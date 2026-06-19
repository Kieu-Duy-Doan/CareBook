<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Appointment;
use App\Models\PatientProfile;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $startOfMonth = Carbon::now()->startOfMonth();
        $startOfLastMonth = Carbon::now()->subMonth()->startOfMonth();
        $endOfLastMonth = Carbon::now()->subMonth()->endOfMonth();

        // 1. KPI Cards
        // Lịch khám hôm nay
        $todayApptCount = Appointment::whereDate('appointment_date', $today)->count();
        $yesterdayApptCount = Appointment::whereDate('appointment_date', $yesterday)->count();
        $apptGrowth = $yesterdayApptCount > 0 ? (($todayApptCount - $yesterdayApptCount) / $yesterdayApptCount) * 100 : ($todayApptCount > 0 ? 100 : 0);

        // Tổng bệnh nhân
        $totalPatients = PatientProfile::count();
        $newPatientsThisMonth = PatientProfile::where('created_at', '>=', $startOfMonth)->count();
        $newPatientsLastMonth = PatientProfile::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count();
        $patientGrowth = $newPatientsLastMonth > 0 ? (($newPatientsThisMonth - $newPatientsLastMonth) / $newPatientsLastMonth) * 100 : ($newPatientsThisMonth > 0 ? 100 : 0);

        // Bác sĩ đang hoạt động
        $activeDoctorsCount = User::where('role', 'doctor')->where('is_active', true)->count();

        // Tỷ lệ hoàn thành hôm nay
        $completedToday = Appointment::whereDate('appointment_date', $today)->where('status', 'completed')->count();
        $completionRate = $todayApptCount > 0 ? round(($completedToday / $todayApptCount) * 100) : 0;

        // 2. Trend Chart (Biểu đồ xu hướng)
        $trendFilter = $request->query('trend', 'day'); // day, month, year
        $trendData = [];
        $trendLabels = [];

        if ($trendFilter === 'day') {
            // 7 ngày qua
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i);
                $trendLabels[] = $date->format('d/m');
                $count = Appointment::whereDate('appointment_date', $date)->count();
                $trendData[] = $count;
            }
        } elseif ($trendFilter === 'month') {
            // 12 tháng trong năm nay
            for ($i = 1; $i <= 12; $i++) {
                $trendLabels[] = "Tháng $i";
                $count = Appointment::whereYear('appointment_date', Carbon::now()->year)
                    ->whereMonth('appointment_date', $i)->count();
                $trendData[] = $count;
            }
        } elseif ($trendFilter === 'year') {
            // 5 năm qua
            for ($i = 4; $i >= 0; $i--) {
                $year = Carbon::now()->subYears($i)->year;
                $trendLabels[] = "Năm $year";
                $count = Appointment::whereYear('appointment_date', $year)->count();
                $trendData[] = $count;
            }
        }

        // 3. Phân bổ theo chuyên khoa (Tháng này)
        $specialtyData = Appointment::select('specialty_id', DB::raw('count(*) as total'))
            ->with('specialty:id,name')
            ->where('appointment_date', '>=', $startOfMonth)
            ->whereNotNull('specialty_id')
            ->groupBy('specialty_id')
            ->get();

        $pieLabels = [];
        $pieData = [];
        foreach ($specialtyData as $item) {
            $pieLabels[] = $item->specialty ? $item->specialty->name : 'Khác';
            $pieData[] = $item->total;
        }

        if (empty($pieData)) {
            $pieLabels = ['Chưa có dữ liệu'];
            $pieData = [1];
        }

        $topDoctors = Appointment::select('doctor_profile_id', DB::raw('count(*) as total'))
            ->with('doctorProfile.user')
            ->where('appointment_date', '>=', $startOfMonth)
            ->whereNotNull('doctor_profile_id')
            ->groupBy('doctor_profile_id')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        // Lịch khám hôm nay
        $todayAppointments = Appointment::with(['patientProfile', 'doctorProfile.user', 'specialty'])
            ->whereDate('appointment_date', $today)
            ->orderBy('appointment_time')
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'todayApptCount',
            'apptGrowth',
            'totalPatients',
            'newPatientsThisMonth',
            'patientGrowth',
            'activeDoctorsCount',
            'completionRate',
            'completedToday',
            'trendFilter',
            'trendLabels',
            'trendData',
            'pieLabels',
            'pieData',
            'topDoctors',
            'todayAppointments'
        ));
    }
}
