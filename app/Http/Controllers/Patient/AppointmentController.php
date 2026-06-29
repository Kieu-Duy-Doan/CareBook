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
            return back()->with('error', 'Chỉ có lịch hẹn ở trạng thái "Chờ khám" mới có thể huỷ.');
        }

        $appointment->update(['status' => 'cancelled']);

        return redirect()->route('patient.appointments.index')
            ->with('success', 'Huỷ lịch hẹn thành công.');
    }
}
