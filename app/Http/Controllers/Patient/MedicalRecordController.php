<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\MedicalRecord;
use Illuminate\Http\Request;

class MedicalRecordController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        // Get medical records for any patient profile owned by this user
        $records = MedicalRecord::with([
            'appointment.patientProfile', 
            'doctorProfile.user',
            'appointment.specialty'
        ])
        ->whereHas('appointment.patientProfile', function ($query) use ($userId) {
            $query->where('owner_id', $userId);
        })
        ->orderByDesc('created_at')
        ->paginate(10);

        return view('patient.dashboard.records', compact('records'));
    }

    public function show(MedicalRecord $record)
    {
        // Check authorization: Does this record belong to a profile owned by the current user?
        if ($record->appointment->patientProfile->owner_id !== auth()->id()) {
            abort(403, 'Unauthorized access to medical record.');
        }

        $record->load(['appointment.patientProfile', 'doctorProfile.user', 'appointment.specialty', 'prescription']);

        return view('patient.dashboard.record-detail', compact('record'));
    }
}
