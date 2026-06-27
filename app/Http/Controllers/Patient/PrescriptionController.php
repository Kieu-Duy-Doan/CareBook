<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use Illuminate\Http\Request;

class PrescriptionController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        // Get prescriptions for any patient profile owned by this user
        $prescriptions = Prescription::with([
            'medicalRecord.appointment.patientProfile',
            'medicalRecord.doctorProfile.user'
        ])
        ->whereHas('medicalRecord.appointment.patientProfile', function ($query) use ($userId) {
            $query->where('owner_id', $userId);
        })
        ->orderByDesc('prescribed_date')
        ->paginate(10);

        return view('patient.dashboard.prescriptions', compact('prescriptions'));
    }

    public function show(Prescription $prescription)
    {
        // Check authorization: Does this prescription belong to a profile owned by the current user?
        if ($prescription->medicalRecord->appointment->patientProfile->owner_id !== auth()->id()) {
            abort(403, 'Unauthorized access to prescription.');
        }

        $prescription->load(['medicalRecord.appointment.patientProfile', 'medicalRecord.doctorProfile.user']);

        return view('patient.dashboard.prescription-detail', compact('prescription'));
    }
}
