<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Appointment;

use Illuminate\Http\Request;

class MedicalRecordController extends Controller
{
    public function index(Request $request)
    {
        $query = Appointment::with([
            'doctorProfile',
            'specialty',
            'room',
            'medicalRecord',
        ])
        ->where('booked_by_user_id', auth()->id())
        ->whereHas('medicalRecord');

        if ($request->filled('appointment_code')) {
            $query->where('appointment_code', 'like', '%' . $request->appointment_code . '%');
        }

        if ($request->filled('appointment_date')) {
            $query->whereDate('appointment_date', $request->appointment_date);
        }

        $appointments = $query->orderByDesc('appointment_date')
            ->orderByDesc('appointment_time')
            ->paginate(12)->withQueryString();

        return view('patient.medical-records.index', compact('appointments'));
    }

    public function show($id)
    {
        $appointment = Appointment::with([
            'doctorProfile.user',
            'specialty',
            'room',
            'medicalRecord.prescription',
            'clinicalVisits.doctorProfile.user',
            'clinicalVisits.room',
        ])
        ->where('booked_by_user_id', auth()->id())
        ->whereHas('medicalRecord')
        ->findOrFail($id);

        return view('patient.medical-records.show', compact('appointment'));
    }
}
