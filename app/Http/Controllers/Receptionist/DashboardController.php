<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\ClinicalVisit;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        
        $stats = [
            'total_appointments_today' => Appointment::whereDate('appointment_date', $today)->count(),
            'pending_appointments' => Appointment::whereDate('appointment_date', $today)->where('status', 'pending')->count(),
            'visits_in_progress' => ClinicalVisit::whereDate('created_at', $today)->where('status', 'in_progress')->count(),
            'visits_waiting' => ClinicalVisit::whereDate('created_at', $today)->where('status', 'waiting')->count(),
        ];

        return view('receptionist.dashboard', compact('stats'));
    }
}
