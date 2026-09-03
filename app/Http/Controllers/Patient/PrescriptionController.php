<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use Barryvdh\DomPDF\Facade\Pdf;

class PrescriptionController extends Controller
{
    public function index()
    {
        return redirect()->route('patient.records.index');
    }

    public function show($id)
    {
        return redirect()->route('patient.records.index');
    }

    public function download($id)
    {
        $prescription = Prescription::with(['medicalRecord.appointment.patientProfile', 'medicalRecord.doctorProfile.user'])
            ->findOrFail($id);

        // Optional: Check if the prescription belongs to the logged-in user
        if ($prescription->medicalRecord->appointment->patientProfile->owner_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $pdf = Pdf::loadView('patient.prescriptions.pdf', compact('prescription'));

        return $pdf->stream('Don_Thuoc_' . $prescription->id . '.pdf');
    }
}
