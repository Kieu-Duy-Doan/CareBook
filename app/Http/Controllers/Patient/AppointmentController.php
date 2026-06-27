<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentLog;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    /**
     * Danh sách lịch hẹn (Lịch sử & Sắp tới)
     */
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'upcoming'); // upcoming | history

        $query = Appointment::with(['patientProfile', 'doctor.user', 'specialty', 'room'])
            ->where('booked_by_user_id', auth()->id());

        if ($tab === 'history') {
            $query->whereIn('status', ['completed', 'cancelled', 'absent'])
                  ->orderBy('appointment_date', 'desc')
                  ->orderBy('appointment_time', 'desc');
        } else {
            $query->whereNotIn('status', ['completed', 'cancelled', 'absent'])
                  ->orderBy('appointment_date', 'asc')
                  ->orderBy('appointment_time', 'asc');
        }

        $appointments = $query->paginate(10);

        return view('patient.appointments.index', compact('appointments', 'tab'));
    }

    /**
     * Xem chi tiết 1 lịch hẹn
     */
    public function show($id)
    {
        $appointment = Appointment::with([
            'patientProfile', 
            'doctor.user', 
            'specialty', 
            'room',
            'logs' => fn($q) => $q->with('changedBy')
        ])
        ->where('booked_by_user_id', auth()->id())
        ->findOrFail($id);

        return view('patient.appointments.show', compact('appointment'));
    }

    /**
     * Bệnh nhân tự hủy lịch
     */
    public function cancel(Request $request, $id)
    {
        $appointment = Appointment::where('booked_by_user_id', auth()->id())
            ->findOrFail($id);

        if ($appointment->status !== 'pending') {
            return back()->with('error', 'Chỉ có thể hủy lịch khi đang ở trạng thái Chờ khám.');
        }

        $reason = $request->input('reason', 'Bệnh nhân tự hủy lịch qua ứng dụng');

        $appointment->update(['status' => 'cancelled']);

        AppointmentLog::create([
            'appointment_id' => $appointment->id,
            'changed_by'     => auth()->id(),
            'old_status'     => 'pending',
            'new_status'     => 'cancelled',
            'action'         => 'PATIENT_CANCELLED',
            'reason'         => $reason,
        ]);

        return redirect()->route('patient.appointments.show', $appointment->id)
            ->with('success', 'Đã hủy lịch hẹn thành công.');
    }
}
