<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\AppointmentLog;
use App\Models\ClinicalVisit;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $doctorProfile = $user->doctorProfile;

        if (!$doctorProfile) {
            return redirect()->route('doctor.profile.index')->with('error', 'Vui lòng cập nhật hồ sơ bác sĩ.');
        }

        $query = Appointment::with(['patientProfile', 'specialty', 'room', 'bookedByUser'])
            ->where('doctor_profile_id', $doctorProfile->id)
            ->latest('appointment_date')
            ->latest('appointment_time');

        if ($request->filled('date_from')) {
            $query->whereDate('appointment_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('appointment_date', '<=', $request->date_to);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('appointment_code', 'like', '%' . $request->search . '%')
                    ->orWhereHas('patientProfile', function($pq) use ($request) {
                        $pq->where('full_name', 'like', '%' . $request->search . '%');
                    });
            });
        }

        $appointments = $query->paginate(15)->withQueryString();

        return view('doctor.appointments.index', compact('appointments'));
    }

    public function show($id)
    {
        $user = Auth::user();
        $doctorProfile = $user->doctorProfile;

        $appointment = Appointment::with([
            'patientProfile',
            'specialty',
            'room',
            'bookedByUser',
            'clinicalVisits.room',
            'medicalRecord.prescription',
            'logs.changedBy',
        ])
        ->where('doctor_profile_id', $doctorProfile->id)
        ->findOrFail($id);

        $pastAppointments = Appointment::with([
            'specialty',
            'doctor.user',
            'medicalRecord.prescription'
        ])
        ->where('patient_profile_id', $appointment->patient_profile_id)
        ->where('status', 'completed')
        ->where('id', '!=', $appointment->id)
        ->orderBy('appointment_date', 'desc')
        ->orderBy('appointment_time', 'desc')
        ->get();

        return view('doctor.appointments.show', compact('appointment', 'pastAppointments'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,checked_in,examining,completed,cancelled,absent',
            'reason' => 'nullable|string|max:500'
        ]);

        $user = Auth::user();
        $doctorProfile = $user->doctorProfile;

        $appointment = Appointment::where('doctor_profile_id', $doctorProfile->id)->findOrFail($id);
        
        $oldStatus = $appointment->status;
        $newStatus = $request->status;

        if ($oldStatus !== $newStatus) {
            $appointment->status = $newStatus;

            if ($newStatus === 'checked_in' && is_null($appointment->checked_in_at)) {
                $appointment->checked_in_at = now();
            }
            if ($newStatus === 'completed' && is_null($appointment->completed_at)) {
                $appointment->completed_at = now();
            }

            $appointment->save();

            if (in_array($newStatus, ['checked_in', 'examining'])) {
                $this->createClinicalVisitIfNotExists($appointment);
            }

            AppointmentLog::create([
                'appointment_id' => $appointment->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'action' => 'DOCTOR_STATUS_CHANGE',
                'changed_by' => Auth::id(),
                'reason' => $request->reason,
            ]);
            
            if ($newStatus === 'cancelled') {
                \App\Jobs\ProcessAppointmentNotificationJob::dispatch($appointment, 'cancellation');
            }
        }

        return back()->with('success', 'Cập nhật trạng thái lịch hẹn thành công.');
    }

    private function createClinicalVisitIfNotExists(Appointment $appointment)
    {
        if (ClinicalVisit::where('appointment_id', $appointment->id)->exists()) {
            return;
        }

        $maxOrder = ClinicalVisit::where('doctor_profile_id', $appointment->doctor_profile_id)
            ->whereDate('created_at', now()->toDateString())
            ->max('visit_order');

        $nextOrder = $maxOrder ? $maxOrder + 1 : 1;

        ClinicalVisit::create([
            'appointment_id' => $appointment->id,
            'doctor_profile_id' => $appointment->doctor_profile_id,
            'room_id' => $appointment->room_id,
            'visit_order' => $nextOrder,
            'is_origin' => true,
            'status' => 'waiting',
        ]);
    }
}
