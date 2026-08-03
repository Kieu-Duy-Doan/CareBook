<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PatientProfile;
use App\Models\Appointment;
use App\Models\ClinicalVisit;
use Illuminate\Support\Facades\Auth;
use App\Services\PaymentService;

class PatientHistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $doctorProfile = $user->doctorProfile;

        if (!$doctorProfile) {
            return redirect()->route('doctor.profile.index')->with('error', 'Vui lòng cập nhật hồ sơ bác sĩ.');
        }

        if ($doctorProfile->doctor_type === 'clinical') {
            $query = Appointment::with(['patientProfile', 'room'])
                ->where('doctor_profile_id', $doctorProfile->id)
                ->where('status', 'completed');
            
            if ($request->filled('appointment_code')) {
                $query->where('appointment_code', 'like', '%' . $request->appointment_code . '%');
            }
            if ($request->filled('patient_name')) {
                $query->whereHas('patientProfile', function($q) use ($request) {
                    $q->where('full_name', 'like', '%' . $request->patient_name . '%');
                });
            }
            if ($request->filled('date_from')) {
                $query->whereDate('appointment_date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('appointment_date', '<=', $request->date_to);
            }

            $items = $query->orderByDesc('appointment_date')->orderByDesc('appointment_time')->paginate(15)->withQueryString();

        } else {
            // Paraclinical
            $query = ClinicalVisit::with(['appointment.patientProfile', 'room'])
                ->where('doctor_profile_id', $doctorProfile->id)
                ->where('status', 'completed');

            if ($request->filled('appointment_code')) {
                $query->whereHas('appointment', function($q) use ($request) {
                    $q->where('appointment_code', 'like', '%' . $request->appointment_code . '%');
                });
            }
            if ($request->filled('patient_name')) {
                $query->whereHas('appointment.patientProfile', function($q) use ($request) {
                    $q->where('full_name', 'like', '%' . $request->patient_name . '%');
                });
            }
            if ($request->filled('date_from')) {
                $query->whereDate('completed_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('completed_at', '<=', $request->date_to);
            }

            $items = $query->orderByDesc('completed_at')->paginate(15)->withQueryString();
        }

        return view('doctor.patient-history.index', compact('items', 'doctorProfile'));
    }

    public function show($id, PaymentService $paymentService)
    {
        $user = Auth::user();
        $doctorProfile = $user->doctorProfile;

        if (!$doctorProfile) {
            return redirect()->route('doctor.profile.index')->with('error', 'Vui lòng cập nhật hồ sơ bác sĩ.');
        }

        if ($doctorProfile->doctor_type === 'clinical') {
            $appointment = Appointment::with([
                'patientProfile',
                'doctorProfile.user',
                'specialty',
                'room',
                'medicalRecord.prescription',
                'clinicalVisits.doctorProfile.user',
                'clinicalVisits.room',
                'clinicalVisits.collectedBy',
                'payments.clinicalVisits.room',
                'payments.prescriptions',
                'logs.changedBy',
            ])
            ->where('doctor_profile_id', $doctorProfile->id)
            ->where('status', 'completed')
            ->findOrFail($id);

            $latestVisit = $appointment->clinicalVisits->sortByDesc('created_at')->first();
            $paymentSummary = $paymentService->calculateSummary($appointment);
            
            $patient = $appointment->patientProfile;

            return view('doctor.patient-history.show', compact('appointment', 'latestVisit', 'paymentSummary', 'doctorProfile', 'patient'));
        } else {
            // Paraclinical
            $visit = ClinicalVisit::with([
                'appointment.patientProfile',
                'room',
                'doctorProfile.user',
                'collectedBy'
            ])
            ->where('doctor_profile_id', $doctorProfile->id)
            ->where('status', 'completed')
            ->findOrFail($id);

            $patient = $visit->appointment->patientProfile;
            return view('doctor.patient-history.show', compact('visit', 'doctorProfile', 'patient'));
        }
    }
}
