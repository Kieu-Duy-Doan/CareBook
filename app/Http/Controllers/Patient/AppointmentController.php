<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function index()
    {
        $appointments = Appointment::with([
            'patientProfile',
            'doctorProfile.user',
            'specialty',
            'room',
        ])
        ->where('booked_by_user_id', auth()->id())
        ->orderByDesc('appointment_date')
        ->orderByDesc('appointment_time')
        ->paginate(12);

        return view('patient.appointments.index', compact('appointments'));
    }

    public function show($id)
    {
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
        ->where('booked_by_user_id', auth()->id())
        ->findOrFail($id);

        $latestVisit = $appointment->clinicalVisits->sortByDesc('created_at')->first();

        return view('patient.appointments.show', compact('appointment', 'latestVisit'));
    }

    public function cancel(Request $request, $id)
    {
        $appointment = Appointment::where('booked_by_user_id', auth()->id())
            ->findOrFail($id);

        if (!in_array($appointment->status, ['pending'])) {
            return back()->with('error', 'Chỉ có lịch hẹn ở trạng thái "Đã tiếp nhận" mới có thể huỷ.');
        }

        $appointment->update(['status' => 'cancelled']);

        // Check for spam cancellations today
        $threshold = config('booking.spam_threshold', 3);
        $today = \Carbon\Carbon::today();
        
        $cancellationsToday = Appointment::where('booked_by_user_id', auth()->id())
            ->where('status', 'cancelled')
            // Apply only to appointments booked by the patient themselves, usually source = web
            ->where('source', 'web')
            ->whereDate('updated_at', $today)
            ->count();

        if ($cancellationsToday > $threshold) {
            $user = auth()->user();
            $user->update([
                'is_active' => false,
                'locked_reason' => 'spam_cancellation'
            ]);
            
            \Illuminate\Support\Facades\Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('patient.login')
                ->with('error', 'Tài khoản của bạn đã bị khóa do hủy lịch khám quá nhiều lần trong ngày. Vui lòng liên hệ Hotline: ' . config('booking.admin_phone') . ' hoặc Lễ tân để được hỗ trợ mở khóa.');
        }

        // Dispatch cancellation notification (by patient, no suggestions needed)
        \App\Jobs\ProcessAppointmentNotificationJob::dispatch($appointment, 'patient_cancel', 'patient');

        return redirect()->route('patient.appointments.index')
            ->with('success', 'Huỷ lịch hẹn thành công.');
    }
}
