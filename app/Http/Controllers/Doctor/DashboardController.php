<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $doctorProfile = $user->doctorProfile;

        if (!$doctorProfile) {
            return redirect()->route('doctor.profile.index')->with('error', 'Vui lòng cập nhật hồ sơ bác sĩ của bạn.');
        }

        // Thống kê cơ bản cho bác sĩ hôm nay
        $todayAppointmentsCount = Appointment::where('doctor_profile_id', $doctorProfile->id)
            ->whereDate('appointment_date', today())
            ->count();

        $completedTodayCount = Appointment::where('doctor_profile_id', $doctorProfile->id)
            ->whereDate('appointment_date', today())
            ->where('status', 'completed')
            ->count();

        $upcomingAppointments = Appointment::where('doctor_profile_id', $doctorProfile->id)
            ->whereDate('appointment_date', '>=', today())
            ->whereIn('status', ['pending', 'checked_in', 'examining'])
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->take(5)
            ->get();

        return view('doctor.dashboard.index', compact('doctorProfile', 'todayAppointmentsCount', 'completedTodayCount', 'upcomingAppointments'));
    }
}
