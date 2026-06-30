<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Appointment;

class MedicalRecordController extends Controller
{
    public function index()
    {
        $appointments = Appointment::with([
            'doctorProfile',
            'specialty',
            'room',
            'medicalRecord.prescription',
        ])
        ->where('booked_by_user_id', auth()->id())
        ->whereHas('medicalRecord')
        ->orderByDesc('appointment_date')
        ->orderByDesc('appointment_time')
        ->paginate(12);

        return view('patient.medical-records.index', compact('appointments'));
    }

    public function show($id)
    {
        return redirect()->route('patient.records.index');
    }
}
