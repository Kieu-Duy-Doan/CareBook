<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\ClinicalVisit;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        // Thống kê cơ bản
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

        // Danh sách bệnh nhân sắp đến hôm nay (pending)
        $upcomingPatients = Appointment::with(['patientProfile', 'doctorProfile.user'])
            ->whereDate('appointment_date', $today)
            ->where('status', 'pending')
            ->orderBy('appointment_time')
            ->take(10)
            ->get();

        // Biểu đồ phân bổ ca theo giờ (từ 7h đến 19h) hôm nay
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

        return view('receptionist.dashboard', compact('stats', 'upcomingPatients', 'chartLabels', 'chartData'));
    }
}
