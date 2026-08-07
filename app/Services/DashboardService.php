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
     * Tính toán các chỉ số thống kê tổng quan (KPIs) theo khoảng thời gian
     * So sánh dữ liệu trong khoảng thời gian với khoảng thời gian trước đó tương đương để tính phần trăm tăng trưởng.
     * 
     * @param Carbon $dateFrom Ngày bắt đầu
     * @param Carbon $dateTo Ngày kết thúc
     * @return array Các chỉ số KPI
     */
    public function getKpiData(Carbon $dateFrom, Carbon $dateTo): array
    {
        // Tính số ngày trong khoảng thời gian để lấy khoảng trước đó tương đương
        $daysDiff = clone $dateFrom;
        $daysDiff = $daysDiff->diffInDays($dateTo);
        
        $prevDateTo = (clone $dateFrom)->subDay()->endOfDay();
        $prevDateFrom = (clone $prevDateTo)->subDays($daysDiff)->startOfDay();

        // 1. Thống kê Lịch khám
        $apptCount = Appointment::whereBetween('appointment_date', [$dateFrom, $dateTo])->count();
        $prevApptCount = Appointment::whereBetween('appointment_date', [$prevDateFrom, $prevDateTo])->count();
        $apptGrowth = $prevApptCount > 0 ? (($apptCount - $prevApptCount) / $prevApptCount) * 100 : ($apptCount > 0 ? 100 : 0);

        // 2. Thống kê Bệnh nhân mới
        $totalPatients = PatientProfile::count();
        $newPatients = PatientProfile::whereBetween('created_at', [$dateFrom, $dateTo])->count();
        $prevNewPatients = PatientProfile::whereBetween('created_at', [$prevDateFrom, $prevDateTo])->count();
        $patientGrowth = $prevNewPatients > 0 ? (($newPatients - $prevNewPatients) / $prevNewPatients) * 100 : ($newPatients > 0 ? 100 : 0);

        // 3. Tỷ lệ hoạt động & Mức độ hoàn thành công việc
        $activeDoctorsCount = User::where('role', 'doctor')->where('is_active', true)->count();

        $completedAppts = Appointment::whereBetween('appointment_date', [$dateFrom, $dateTo])->where('status', 'completed')->count();
        $completionRate = $apptCount > 0 ? round(($completedAppts / $apptCount) * 100) : 0;

        // Số lịch khám bị hủy
        $canceledAppts = Appointment::whereBetween('appointment_date', [$dateFrom, $dateTo])
            ->where('status', 'cancelled')
            ->count();

        return [
            'todayApptCount' => $apptCount, // Giữ nguyên tên biến để view không bị lỗi
            'apptGrowth' => $apptGrowth,
            'totalPatients' => $totalPatients,
            'newPatientsThisMonth' => $newPatients, // Giữ nguyên tên biến
            'patientGrowth' => $patientGrowth,
            'activeDoctorsCount' => $activeDoctorsCount,
            'completedToday' => $completedAppts, // Giữ nguyên tên biến
            'completionRate' => $completionRate,
            'canceledToday' => $canceledAppts, // Giữ nguyên tên biến
        ];
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
    public function getTopDoctors(Carbon $dateFrom, Carbon $dateTo)
    {
        return Appointment::select('doctor_profile_id', DB::raw('count(*) as total'))
            ->with('doctorProfile.user') // Eager Loading thông tin tài khoản của bác sĩ
            ->whereBetween('appointment_date', [$dateFrom, $dateTo])
            ->whereNotNull('doctor_profile_id')
            ->groupBy('doctor_profile_id')
            ->orderByDesc('total') // Sắp xếp giảm dần theo số lượng ca khám
            ->take(5) // Chỉ lấy Top 5
            ->get();
    }

    /**
     * Lấy danh sách 10 ca khám mới nhất trong khoảng thời gian
     * Để hiển thị danh sách lịch khám.
     */
    public function getAppointmentsList(Carbon $dateFrom, Carbon $dateTo)
    {
        return Appointment::with(['patientProfile', 'doctorProfile.user', 'specialty']) // Load luôn thông tin liên quan để tránh N+1 Query
            ->whereBetween('appointment_date', [$dateFrom, $dateTo])
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
    public function getReceptionistDashboardData(Carbon $start, Carbon $end): array
    {
        $stats = [
            'total_appointments_today' => Appointment::whereBetween('appointment_date', [$start, $end])->count(),
            'pending_appointments' => Appointment::whereBetween('appointment_date', [$start, $end])->where('status', 'pending')->count(),
            'checked_in_today' => Appointment::whereBetween('appointment_date', [$start, $end])->where('status', 'checked_in')->count(),
            'late_today' => Appointment::whereBetween('appointment_date', [$start, $end])->where('status', 'late')->count(),
            'cancelled_today' => Appointment::whereBetween('appointment_date', [$start, $end])->where('status', 'cancelled')->count(),
            'visits_in_progress' => ClinicalVisit::whereBetween('created_at', [$start, $end])->where('status', 'in_progress')->count(),
            'visits_waiting' => ClinicalVisit::whereBetween('created_at', [$start, $end])->where('status', 'waiting')->count(),
            'pending_payments' => Payment::where('status', 'pending')->count(),
        ];

        $upcomingPatients = Appointment::with(['patientProfile', 'doctorProfile.user'])
            ->whereBetween('appointment_date', [$start, $end])
            ->where('status', 'pending')
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->take(10)
            ->get();

        $hourlyDistribution = Appointment::select(DB::raw('HOUR(appointment_time) as hour'), DB::raw('count(*) as count'))
            ->whereBetween('appointment_date', [$start, $end])
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
     * Lấy dữ liệu biểu đồ doanh thu cho Lễ tân
     */
    public function getReceptionistRevenueChartData($receptionistId, $chartFilter): array
    {
        $chartEnd = Carbon::today()->endOfDay();
        
        $isYearly = false;
        $isHourly = false;
        
        if ($chartFilter === 'today') {
            $chartStart = Carbon::today()->startOfDay();
            $isHourly = true;
        } elseif ($chartFilter === 'this_month') {
            $chartStart = Carbon::today()->startOfMonth();
            $chartEnd = Carbon::today()->endOfMonth();
        } elseif ($chartFilter === 'last_month') {
            $chartStart = Carbon::today()->subMonth()->startOfMonth();
            $chartEnd = Carbon::today()->subMonth()->endOfMonth();
        } elseif ($chartFilter === 'this_year') {
            $chartStart = Carbon::today()->startOfYear();
            $chartEnd = Carbon::today()->endOfYear();
            $isYearly = true;
        } elseif ($chartFilter === 'last_year') {
            $chartStart = Carbon::today()->subYear()->startOfYear();
            $chartEnd = Carbon::today()->subYear()->endOfYear();
            $isYearly = true;
        } elseif ($chartFilter === '30_days') {
            $chartStart = Carbon::today()->subDays(29)->startOfDay();
        } else { // 7_days
            $chartStart = Carbon::today()->subDays(6)->startOfDay();
        }

        $chartDates = [];
        $revenueCashData = [];
        $revenueQrData = [];

        if ($isYearly) {
            $chartDataObj = Payment::select(DB::raw('MONTH(paid_at) as month'), 'method', DB::raw('SUM(amount) as total'))
                ->where('collected_by', $receptionistId)
                ->whereBetween('paid_at', [$chartStart, $chartEnd])
                ->where('status', 'completed')
                ->groupBy('month', 'method')
                ->get();
                
            for ($m = 1; $m <= 12; $m++) {
                $chartDates[] = 'Tháng ' . $m;
                
                $cashForMonth = $chartDataObj->where('month', $m)->where('method', 'cash')->first();
                $qrForMonth = $chartDataObj->where('month', $m)->where('method', 'qr')->first();
                
                $revenueCashData[] = $cashForMonth ? (float)$cashForMonth->total : 0;
                $revenueQrData[] = $qrForMonth ? (float)$qrForMonth->total : 0;
            }
        } elseif ($isHourly) {
            $chartDataObj = Payment::select(DB::raw('HOUR(paid_at) as hour'), 'method', DB::raw('SUM(amount) as total'))
                ->where('collected_by', $receptionistId)
                ->whereBetween('paid_at', [$chartStart, $chartEnd])
                ->where('status', 'completed')
                ->groupBy('hour', 'method')
                ->get();
                
            for ($h = 7; $h <= 20; $h++) { // 7h - 20h
                $chartDates[] = $h . ':00';
                
                $cashForHour = $chartDataObj->where('hour', $h)->where('method', 'cash')->first();
                $qrForHour = $chartDataObj->where('hour', $h)->where('method', 'qr')->first();
                
                $revenueCashData[] = $cashForHour ? (float)$cashForHour->total : 0;
                $revenueQrData[] = $qrForHour ? (float)$qrForHour->total : 0;
            }
        } else {
            $chartDataObj = Payment::select(DB::raw('DATE(paid_at) as date'), 'method', DB::raw('SUM(amount) as total'))
                ->where('collected_by', $receptionistId)
                ->whereBetween('paid_at', [$chartStart, $chartEnd])
                ->where('status', 'completed')
                ->groupBy('date', 'method')
                ->get();
                
            $currentDate = $chartStart->copy();
            while ($currentDate <= $chartEnd) {
                $dateStr = $currentDate->format('Y-m-d');
                $chartDates[] = $currentDate->format('d/m');
                
                $cashForDate = $chartDataObj->where('date', $dateStr)->where('method', 'cash')->first();
                $qrForDate = $chartDataObj->where('date', $dateStr)->where('method', 'qr')->first();
                
                $revenueCashData[] = $cashForDate ? (float)$cashForDate->total : 0;
                $revenueQrData[] = $qrForDate ? (float)$qrForDate->total : 0;
                
                $currentDate->addDay();
            }
        }

        return compact('chartDates', 'revenueCashData', 'revenueQrData');
    }

    /**
     * Lấy dữ liệu thống kê cho Dashboard Bác sĩ
     */
    public function getDoctorDashboardData(Carbon $fromDate, Carbon $toDate, $doctorProfileId): array
    {
        $doctorProfile = \App\Models\DoctorProfile::find($doctorProfileId);
        $doctorType = $doctorProfile ? $doctorProfile->doctor_type : 'clinical';

        $fromDateString = $fromDate->format('Y-m-d');
        $toDateString = $toDate->format('Y-m-d');

        if ($doctorType === 'clinical') {
            $appointmentsCount = Appointment::where('doctor_profile_id', $doctorProfileId)
                ->whereBetween('appointment_date', [$fromDateString, $toDateString])
                ->count();

            $completedCount = Appointment::where('doctor_profile_id', $doctorProfileId)
                ->whereBetween('appointment_date', [$fromDateString, $toDateString])
                ->where('status', 'completed')
                ->count();

            $patientsWaitingOutside = Appointment::where('doctor_profile_id', $doctorProfileId)
                ->whereBetween('appointment_date', [$fromDateString, $toDateString])
                ->where('status', 'checked_in')
                ->count();
                
            $examiningCount = Appointment::where('doctor_profile_id', $doctorProfileId)
                ->whereBetween('appointment_date', [$fromDateString, $toDateString])
                ->where('status', 'examining')
                ->count();
        } else {
            $appointmentsCount = ClinicalVisit::where('doctor_profile_id', $doctorProfileId)
                ->whereBetween('created_at', [$fromDate->startOfDay(), $toDate->endOfDay()])
                ->count();

            $completedCount = ClinicalVisit::where('doctor_profile_id', $doctorProfileId)
                ->whereBetween('created_at', [$fromDate->startOfDay(), $toDate->endOfDay()])
                ->where('status', 'completed')
                ->count();

            $patientsWaitingOutside = ClinicalVisit::where('doctor_profile_id', $doctorProfileId)
                ->whereBetween('created_at', [$fromDate->startOfDay(), $toDate->endOfDay()])
                ->where('status', 'waiting')
                ->count();
                
            $examiningCount = ClinicalVisit::where('doctor_profile_id', $doctorProfileId)
                ->whereBetween('created_at', [$fromDate->startOfDay(), $toDate->endOfDay()])
                ->where('status', 'in_progress')
                ->count();
        }

        $waitingListData = [];
        
        if ($doctorType === 'clinical') {
            $waitingListData['checked_in'] = Appointment::with('patientProfile')
                ->where('doctor_profile_id', $doctorProfileId)
                ->whereBetween('appointment_date', [$fromDateString, $toDateString])
                ->where('status', 'checked_in')
                ->orderBy('is_late', 'asc')
                ->orderByRaw("CASE WHEN is_late = true THEN TIME(checked_in_at) ELSE appointment_time END ASC")
                ->paginate(10, ['*'], 'checked_in_page');
                
            $waitingListData['examining'] = Appointment::with('patientProfile')
                ->where('doctor_profile_id', $doctorProfileId)
                ->whereBetween('appointment_date', [$fromDateString, $toDateString])
                ->where('status', 'examining')
                ->orderBy('appointment_time', 'asc')
                ->paginate(10, ['*'], 'examining_page');
                
            $waitingListData['completed'] = Appointment::with('patientProfile')
                ->where('doctor_profile_id', $doctorProfileId)
                ->whereBetween('appointment_date', [$fromDateString, $toDateString])
                ->where('status', 'completed')
                ->orderBy('completed_at', 'desc')
                ->paginate(10, ['*'], 'completed_page');
                
            $waitingListData['cancelled'] = Appointment::with('patientProfile')
                ->where('doctor_profile_id', $doctorProfileId)
                ->whereBetween('appointment_date', [$fromDateString, $toDateString])
                ->where('status', 'cancelled')
                ->orderBy('appointment_time', 'desc')
                ->paginate(10, ['*'], 'cancelled_page');
        } else {
            $waitingListData['pending'] = ClinicalVisit::with(['appointment.patientProfile'])
                ->where('doctor_profile_id', $doctorProfileId)
                ->whereBetween('created_at', [$fromDate->startOfDay(), $toDate->endOfDay()])
                ->where('status', 'waiting')
                ->orderBy('created_at', 'asc')
                ->paginate(10, ['*'], 'pending_page');
                
            $waitingListData['examining'] = ClinicalVisit::with(['appointment.patientProfile'])
                ->where('doctor_profile_id', $doctorProfileId)
                ->whereBetween('created_at', [$fromDate->startOfDay(), $toDate->endOfDay()])
                ->where('status', 'in_progress')
                ->orderBy('started_at', 'asc')
                ->paginate(10, ['*'], 'examining_page');
                
            $waitingListData['completed'] = ClinicalVisit::with(['appointment.patientProfile'])
                ->where('doctor_profile_id', $doctorProfileId)
                ->whereBetween('created_at', [$fromDate->startOfDay(), $toDate->endOfDay()])
                ->where('status', 'completed')
                ->orderBy('completed_at', 'desc')
                ->paginate(10, ['*'], 'completed_page');
        }

        $sevenDaysAgo = Carbon::now()->subDays(6)->startOfDay();
        if ($doctorType === 'clinical') {
            $chartData = Appointment::select(DB::raw('DATE(appointment_date) as date'), DB::raw('count(*) as count'))
                ->where('doctor_profile_id', $doctorProfileId)
                ->where('appointment_date', '>=', $sevenDaysAgo)
                ->where('appointment_date', '<=', Carbon::today())
                ->groupBy('date')
                ->pluck('count', 'date')->toArray();
        } else {
            $chartData = ClinicalVisit::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                ->where('doctor_profile_id', $doctorProfileId)
                ->where('created_at', '>=', $sevenDaysAgo)
                ->where('created_at', '<=', Carbon::today()->endOfDay())
                ->groupBy('date')
                ->pluck('count', 'date')->toArray();
        }

        $miniChartLabels = [];
        $miniChartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $dateStr = Carbon::now()->subDays($i)->format('Y-m-d');
            $miniChartLabels[] = Carbon::now()->subDays($i)->format('d/m');
            $miniChartData[] = $chartData[$dateStr] ?? 0;
        }

        return compact(
            'appointmentsCount',
            'completedCount',
            'patientsWaitingOutside',
            'examiningCount',
            'waitingListData',
            'doctorType',
            'miniChartLabels',
            'miniChartData'
        );
    }
}
