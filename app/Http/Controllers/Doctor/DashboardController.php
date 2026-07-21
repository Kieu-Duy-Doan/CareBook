<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $doctorProfile = $user->doctorProfile;

        if (!$doctorProfile) {
            return redirect()->route('doctor.profile.index')->with('error', 'Vui lòng cập nhật hồ sơ bác sĩ của bạn.');
        }

        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        // Thống kê cơ bản hôm nay
        $todayAppointmentsCount = Appointment::where('doctor_profile_id', $doctorProfile->id)
            ->whereDate('appointment_date', $today)
            ->count();

        $completedTodayCount = Appointment::where('doctor_profile_id', $doctorProfile->id)
            ->whereDate('appointment_date', $today)
            ->where('status', 'completed')
            ->count();

        // Thống kê mở rộng
        $patientsWaitingOutside = Appointment::where('doctor_profile_id', $doctorProfile->id)
            ->whereDate('appointment_date', $today)
            ->where('status', 'checked_in')
            ->count();

        $totalCompletedThisMonth = Appointment::where('doctor_profile_id', $doctorProfile->id)
            ->where('appointment_date', '>=', $startOfMonth)
            ->where('status', 'completed')
            ->count();

        // Tính doanh thu cá nhân (từ các thanh toán đã hoàn thành của lịch khám thuộc bác sĩ này)
        $revenueThisMonth = Payment::where('status', 'completed')
            ->whereHas('appointment', function ($query) use ($doctorProfile, $startOfMonth) {
                $query->where('doctor_profile_id', $doctorProfile->id)
                    ->where('appointment_date', '>=', $startOfMonth);
            })
            ->sum('amount');

        // Danh sách sắp tới
        $upcomingAppointments = Appointment::with('patientProfile')
            ->where('doctor_profile_id', $doctorProfile->id)
            ->whereDate('appointment_date', '>=', $today)
            ->whereIn('status', ['pending', 'checked_in', 'examining'])
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->take(5)
            ->get();

        // Biểu đồ mini 7 ngày gần nhất
        $sevenDaysAgo = Carbon::now()->subDays(6)->startOfDay();
        $chartData = Appointment::select(DB::raw('DATE(appointment_date) as date'), DB::raw('count(*) as count'))
            ->where('doctor_profile_id', $doctorProfile->id)
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

        return view('doctor.dashboard.index', compact(
            'doctorProfile',
            'todayAppointmentsCount',
            'completedTodayCount',
            'patientsWaitingOutside',
            'totalCompletedThisMonth',
            'revenueThisMonth',
            'upcomingAppointments',
            'miniChartLabels',
            'miniChartData'
        ));
    }
}
