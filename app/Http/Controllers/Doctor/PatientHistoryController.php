<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PatientProfile;
use App\Models\Appointment;
use Illuminate\Support\Facades\Auth;

class PatientHistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $doctorProfile = $user->doctorProfile;

        if (!$doctorProfile) {
            return redirect()->route('doctor.profile.index')->with('error', 'Vui lòng cập nhật hồ sơ bác sĩ.');
        }

        // Lấy danh sách bệnh nhân đã từng khám với bác sĩ này
        $query = PatientProfile::whereHas('appointments.clinicalVisits', function($q) use ($doctorProfile) {
            $q->where('doctor_profile_id', $doctorProfile->id);
        });

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('full_name', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        $patients = $query->paginate(15)->withQueryString();

        return view('doctor.patient-history.index', compact('patients'));
    }

    public function show($patient_id)
    {
        $user = Auth::user();
        $doctorProfile = $user->doctorProfile;

        $patient = PatientProfile::findOrFail($patient_id);

        // Lấy tất cả lịch sử khám của bệnh nhân này
        $appointments = Appointment::with(['medicalRecord', 'clinicalVisits' => function($q) {
            $q->orderBy('visit_order');
        }])
        ->where('patient_profile_id', $patient->id)
        ->where('status', 'completed')
        ->orderBy('appointment_date', 'desc')
        ->get();

        return view('doctor.patient-history.show', compact('patient', 'appointments'));
    }
}
