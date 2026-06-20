<?php

namespace App\Services;

use App\Models\User;
use App\Models\Appointment;
use App\Models\PatientProfile;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Lớp Dịch vụ (Service Layer) chuyên xử lý logic thống kê cho Bảng điều khiển (Dashboard)
 * Giúp bóc tách logic tính toán phức tạp ra khỏi Controller.
 */
class DashboardService
{
    /**
     * Tính toán các chỉ số thống kê tổng quan (KPIs)
     * So sánh dữ liệu ngày hiện tại với ngày hôm qua, tháng này với tháng trước để tính phần trăm tăng trưởng.
     * 
     * @param Carbon $today Ngày hôm nay
     * @param Carbon $startOfMonth Ngày đầu tiên của tháng hiện tại
     * @return array Các chỉ số KPI
     */
    public function getKpiData(Carbon $today, Carbon $startOfMonth): array
    {
        // Khởi tạo các mốc thời gian dùng để so sánh (Hôm qua, tháng trước)
        $yesterday = Carbon::yesterday();
        $startOfLastMonth = Carbon::now()->subMonth()->startOfMonth();
        $endOfLastMonth = Carbon::now()->subMonth()->endOfMonth();

        // 1. Thống kê Lịch khám
        $todayApptCount = Appointment::whereDate('appointment_date', $today)->count();
        $yesterdayApptCount = Appointment::whereDate('appointment_date', $yesterday)->count();
        // Công thức tính % tăng trưởng: ((Hôm nay - Hôm qua) / Hôm qua) * 100
        $apptGrowth = $yesterdayApptCount > 0 ? (($todayApptCount - $yesterdayApptCount) / $yesterdayApptCount) * 100 : ($todayApptCount > 0 ? 100 : 0);

        // 2. Thống kê Bệnh nhân mới
        $totalPatients = PatientProfile::count();
        $newPatientsThisMonth = PatientProfile::where('created_at', '>=', $startOfMonth)->count();
        $newPatientsLastMonth = PatientProfile::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count();
        $patientGrowth = $newPatientsLastMonth > 0 ? (($newPatientsThisMonth - $newPatientsLastMonth) / $newPatientsLastMonth) * 100 : ($newPatientsThisMonth > 0 ? 100 : 0);

        // 3. Tỷ lệ hoạt động & Mức độ hoàn thành công việc
        // Chỉ đếm các bác sĩ có quyền 'doctor' và đang ở trạng thái kích hoạt (is_active = true)
        $activeDoctorsCount = User::where('role', 'doctor')->where('is_active', true)->count();
        
        // Đếm số ca đã khám xong (status = completed) trong ngày hôm nay
        $completedToday = Appointment::whereDate('appointment_date', $today)->where('status', 'completed')->count();
        $completionRate = $todayApptCount > 0 ? round(($completedToday / $todayApptCount) * 100) : 0;

        return compact(
            'todayApptCount', 'apptGrowth', 'totalPatients', 'newPatientsThisMonth', 
            'patientGrowth', 'activeDoctorsCount', 'completedToday', 'completionRate'
        );
    }

    /**
     * Lấy dữ liệu cho biểu đồ xu hướng (Sử dụng Group By 1 query thay vì vòng lặp N+1)
     * Giải quyết bài toán N+1 Query bằng cách gom nhóm dữ liệu (Group By) trực tiếp từ Database.
     *
     * @param string $filter 'month_days' (từng ngày trong tháng), 'year_months' (từng tháng trong năm)
     * @param string $targetMonth Số tháng (1-12) hoặc 'all'
     * @param string $targetYear Năm cần xem (VD: '2026')
     * @return array Labels (Nhãn trục X) và Data (Số liệu trục Y)
     */
    public function getTrendData(string $filter, string $targetMonth, string $targetYear): array
    {
        $trendLabels = [];
        $trendData = [];

        // Validate Year
        $year = (int) $targetYear;
        if ($year < 2000 || $year > 2100) {
            $year = Carbon::now()->year;
            $targetYear = (string) $year;
        }

        if ($filter === 'month_days') {
            // Xem số lượng khám theo từng ngày trong 1 tháng cụ thể
            $month = (int) $targetMonth;
            if ($month < 1 || $month > 12) {
                $month = Carbon::now()->month;
                $targetMonth = (string) $month;
            }

            // Lấy số ngày trong tháng đó
            $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;

            // Dùng hàm DAY() của CSDL để gom nhóm
            $appointments = Appointment::select(DB::raw('DAY(appointment_date) as day'), DB::raw('count(*) as total'))
                ->whereYear('appointment_date', $year)
                ->whereMonth('appointment_date', $month)
                ->groupBy('day')
                ->pluck('total', 'day')->toArray();

            for ($i = 1; $i <= $daysInMonth; $i++) {
                $trendLabels[] = "$i/$month";
                $trendData[] = $appointments[$i] ?? 0;
            }
        } elseif ($filter === 'year_months') {
            // Xem số lượng khám theo 12 tháng trong 1 năm cụ thể
            $appointments = Appointment::select(DB::raw('MONTH(appointment_date) as month'), DB::raw('count(*) as total'))
                ->whereYear('appointment_date', $year)
                ->groupBy('month')
                ->pluck('total', 'month')->toArray();

            for ($i = 1; $i <= 12; $i++) {
                $trendLabels[] = "Tháng $i";
                $trendData[] = $appointments[$i] ?? 0;
            }
        }

        return [
            'trendFilter' => $filter,
            'targetMonth' => $targetMonth,
            'targetYear' => $targetYear,
            'trendLabels' => $trendLabels,
            'trendData' => $trendData,
        ];
    }

    /**
     * Lấy dữ liệu phân bổ chuyên khoa trong tháng để vẽ Biểu đồ tròn (Donut Chart)
     * Đếm số ca khám chia theo từng khoa (Nội, Ngoại, Nhi...)
     */
    public function getSpecialtyPieData(Carbon $startOfMonth): array
    {
        // Nhóm dữ liệu theo ID chuyên khoa và đếm tổng số ca
        $specialtyData = Appointment::select('specialty_id', DB::raw('count(*) as total'))
            ->with('specialty:id,name') // Tải kèm tên chuyên khoa (Eager Loading) để tránh N+1 Query
            ->where('appointment_date', '>=', $startOfMonth)
            ->whereNotNull('specialty_id') // Bỏ qua các ca chưa phân khoa
            ->groupBy('specialty_id')
            ->get();

        $pieLabels = [];
        $pieData = [];

        // Bóc tách mảng thành mảng nhãn và mảng giá trị cho biểu đồ JS
        foreach ($specialtyData as $item) {
            $pieLabels[] = $item->specialty ? $item->specialty->name : 'Khác';
            $pieData[] = $item->total;
        }

        // Xử lý trường hợp tháng này chưa có ca khám nào (Chống lỗi vẽ biểu đồ mảng rỗng)
        if (empty($pieData)) {
            $pieLabels = ['Chưa có dữ liệu'];
            $pieData = [1];
        }

        return compact('pieLabels', 'pieData');
    }

    /**
     * Lấy danh sách Top bác sĩ tiếp nhận nhiều ca nhất trong tháng
     * Để hiển thị bảng xếp hạng năng suất.
     */
    public function getTopDoctors(Carbon $startOfMonth)
    {
        return Appointment::select('doctor_profile_id', DB::raw('count(*) as total'))
            ->with('doctorProfile.user') // Eager Loading thông tin tài khoản của bác sĩ
            ->where('appointment_date', '>=', $startOfMonth)
            ->whereNotNull('doctor_profile_id')
            ->groupBy('doctor_profile_id')
            ->orderByDesc('total') // Sắp xếp giảm dần theo số lượng ca khám
            ->take(5) // Chỉ lấy Top 5
            ->get();
    }

    /**
     * Lấy danh sách 10 ca khám sớm nhất trong ngày hôm nay
     * Để hiển thị cho Lễ tân theo dõi tại bàn làm việc.
     */
    public function getTodayAppointments(Carbon $today)
    {
        return Appointment::with(['patientProfile', 'doctorProfile.user', 'specialty']) // Load luôn thông tin liên quan để tránh N+1 Query
            ->whereDate('appointment_date', $today)
            ->orderBy('appointment_time') // Sắp xếp lịch khám theo thứ tự thời gian (từ sáng đến chiều)
            ->take(10) // Tối đa hiển thị 10 ca
            ->get();
    }
}
