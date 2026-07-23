<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Appointment;

class ExaminationProgressController extends Controller
{
    public function index()
    {
        $appointments = Appointment::with([
            'doctorProfile.user',
            'specialty',
            'room',
            'clinicalVisits.room',
        ])
        ->where('booked_by_user_id', auth()->id())
        ->whereIn('status', ['checked_in', 'examining'])
        ->orderByDesc('appointment_date')
        ->orderByDesc('appointment_time')
        ->get();

        if ($appointments->count() === 1) {
            return redirect()->route('patient.progress.show', $appointments->first()->id);
        }

        return view('patient.progress.index', compact('appointments'));
    }

    public function show($id)
    {
        $appointment = Appointment::with([
            'doctorProfile.user',
            'specialty',
            'room',
            'clinicalVisits.room',
            'clinicalVisits.doctorProfile.user',
        ])
        ->where('booked_by_user_id', auth()->id())
        ->whereIn('status', ['checked_in', 'examining'])
        ->findOrFail($id);

        $visits = $appointment->clinicalVisits->sortBy('visit_order');
        $completedCount = $visits->where('status', 'completed')->count();
        $totalCount = $visits->count();

        return view('patient.progress.show', compact('appointment', 'visits', 'completedCount', 'totalCount'));
    }
}
