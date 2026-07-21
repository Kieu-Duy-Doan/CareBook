<?php

namespace App\Services;

use App\Models\User;
use App\Models\Appointment;
use App\Models\PatientProfile;
use App\Models\Payment;
use App\Models\ClinicalVisit;
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

        // 4. Doanh thu (Hôm nay & Tháng này)
        $revenueToday = Payment::whereDate('paid_at', $today)
            ->where('status', 'completed')
            ->sum('amount');

        $revenueThisMonth = Payment::where('paid_at', '>=', $startOfMonth)
            ->where('status', 'completed')
            ->sum('amount');

        // 5. Số lịch khám bị hủy (Hôm nay)
        $canceledToday = Appointment::whereDate('appointment_date', $today)
            ->where('status', 'cancelled')
            ->count();

        return compact(
            'todayApptCount',
            'apptGrowth',
            'totalPatients',
            'newPatientsThisMonth',
            'patientGrowth',
            'activeDoctorsCount',
            'completedToday',
            'completionRate',
            'revenueToday',
            'revenueThisMonth',
            'canceledToday'
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

    /**
     * Lấy dữ liệu Giờ cao điểm trong tháng hiện tại
     * Phân bổ số lượng ca khám theo từng khung giờ (7h - 19h)
     */
    public function getPeakHoursData(Carbon $startOfMonth): array
    {
        $appointments = Appointment::select(DB::raw('HOUR(appointment_time) as hour'), DB::raw('count(*) as count'))
            ->where('appointment_date', '>=', $startOfMonth)
            ->whereNotNull('appointment_time')
            ->groupBy('hour')
            ->pluck('count', 'hour')->toArray();

        $peakLabels = [];
        $peakData = [];
        for ($i = 7; $i <= 19; $i++) {
            $peakLabels[] = $i . ':00';
            $peakData[] = $appointments[$i] ?? 0;
        }

        return compact('peakLabels', 'peakData');
    }

    /**
     * Lấy dữ liệu Doanh thu theo phương thức thanh toán trong tháng
     */
    public function getRevenueByMethodData(Carbon $startOfMonth): array
    {
        $payments = Payment::select('method', DB::raw('sum(amount) as total'))
            ->where('paid_at', '>=', $startOfMonth)
            ->where('status', 'completed')
            ->groupBy('method')
            ->pluck('total', 'method')->toArray();

        $methodNames = [
            'cash' => 'Tiền mặt',
            'qr' => 'Chuyển khoản (QR)',
            'insurance' => 'Bảo hiểm Y tế',
            'waived' => 'Miễn phí / Miễn giảm'
        ];

        $revenueMethodLabels = [];
        $revenueMethodData = [];

        foreach ($methodNames as $key => $name) {
            $revenueMethodLabels[] = $name;
            $revenueMethodData[] = $payments[$key] ?? 0;
        }

        // Nếu chưa có doanh thu
        if (empty(array_filter($revenueMethodData))) {
            $revenueMethodLabels = ['Chưa có dữ liệu'];
            $revenueMethodData = [1];
        }

        return compact('revenueMethodLabels', 'revenueMethodData');
    }

    /**
     * Lấy dữ liệu thống kê cho Dashboard Lễ tân
     */
    public function getReceptionistDashboardData(Carbon $today): array
    {
        $stats = [
            'total_appointments_today' => Appointment::whereDate('appointment_date', $today)->count(),
            'pending_appointments' => Appointment::whereDate('appointment_date', $today)->where('status', 'pending')->count(),
            'checked_in_today' => Appointment::whereDate('appointment_date', $today)->where('status', 'checked_in')->count(),
            'late_today' => Appointment::whereDate('appointment_date', $today)->where('status', 'late')->count(),
            'cancelled_today' => Appointment::whereDate('appointment_date', $today)->where('status', 'cancelled')->count(),
            'visits_in_progress' => ClinicalVisit::whereDate('created_at', $today)->where('status', 'in_progress')->count(),
            'visits_waiting' => ClinicalVisit::whereDate('created_at', $today)->where('status', 'waiting')->count(),
            'pending_payments' => Payment::where('status', 'pending')->count(),
        ];

        $upcomingPatients = Appointment::with(['patientProfile', 'doctorProfile.user'])
            ->whereDate('appointment_date', $today)
            ->where('status', 'pending')
            ->orderBy('appointment_time')
            ->take(10)
            ->get();

        $hourlyDistribution = Appointment::select(DB::raw('HOUR(appointment_time) as hour'), DB::raw('count(*) as count'))
            ->whereDate('appointment_date', $today)
            ->groupBy('hour')
            ->pluck('count', 'hour')->toArray();

        $chartLabels = [];
        $chartData = [];
        for ($i = 7; $i <= 19; $i++) {
            $chartLabels[] = $i . ':00';
            $chartData[] = $hourlyDistribution[$i] ?? 0;
        }

        return compact('stats', 'upcomingPatients', 'chartLabels', 'chartData');
    }

    /**
     * Lấy dữ liệu thống kê cho Dashboard Bác sĩ
     */
    public function getDoctorDashboardData(Carbon $today, Carbon $startOfMonth, $doctorProfileId): array
    {
        $todayAppointmentsCount = Appointment::where('doctor_profile_id', $doctorProfileId)
            ->whereDate('appointment_date', $today)
            ->count();

        $completedTodayCount = Appointment::where('doctor_profile_id', $doctorProfileId)
            ->whereDate('appointment_date', $today)
            ->where('status', 'completed')
            ->count();

        $patientsWaitingOutside = Appointment::where('doctor_profile_id', $doctorProfileId)
            ->whereDate('appointment_date', $today)
            ->where('status', 'checked_in')
            ->count();

        $totalCompletedThisMonth = Appointment::where('doctor_profile_id', $doctorProfileId)
            ->where('appointment_date', '>=', $startOfMonth)
            ->where('status', 'completed')
            ->count();

        $revenueThisMonth = Payment::where('status', 'completed')
            ->whereHas('appointment', function ($query) use ($doctorProfileId, $startOfMonth) {
                $query->where('doctor_profile_id', $doctorProfileId)
                    ->where('appointment_date', '>=', $startOfMonth);
            })
            ->sum('amount');

        $upcomingAppointments = Appointment::with('patientProfile')
            ->where('doctor_profile_id', $doctorProfileId)
            ->whereDate('appointment_date', '>=', $today)
            ->whereIn('status', ['pending', 'checked_in', 'examining'])
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->take(5)
            ->get();

        $sevenDaysAgo = Carbon::now()->subDays(6)->startOfDay();
        $chartData = Appointment::select(DB::raw('DATE(appointment_date) as date'), DB::raw('count(*) as count'))
            ->where('doctor_profile_id', $doctorProfileId)
            ->where('appointment_date', '>=', $sevenDaysAgo)
            ->where('appointment_date', '<=', $today)
            ->groupBy('date')
            ->pluck('count', 'date')->toArray();

        $miniChartLabels = [];
        $miniChartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $dateStr = Carbon::now()->subDays($i)->format('Y-m-d');
            $miniChartLabels[] = Carbon::now()->subDays($i)->format('d/m');
            $miniChartData[] = $chartData[$dateStr] ?? 0;
        }

        return compact(
            'todayAppointmentsCount',
            'completedTodayCount',
            'patientsWaitingOutside',
            'totalCompletedThisMonth',
            'revenueThisMonth',
            'upcomingAppointments',
            'miniChartLabels',
            'miniChartData'
        );
    }
}
