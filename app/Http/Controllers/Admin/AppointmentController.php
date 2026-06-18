<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\AppointmentLog;
use App\Models\DoctorProfile;
use App\Models\Specialty;
use App\Models\PatientProfile;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Appointment::with([
            'patientProfile',
            'doctor.user',
            'specialty',
            'room',
            'bookedByUser'
        ])->latest('appointment_date')->latest('appointment_time');

        // Filter theo ngày từ
        if ($request->filled('date_from')) {
            $query->whereDate('appointment_date', '>=', $request->date_from);
        }
        // Filter theo ngày đến
        if ($request->filled('date_to')) {
            $query->whereDate('appointment_date', '<=', $request->date_to);
        }
        // Filter theo bác sĩ
        if ($request->filled('doctor_id')) {
            $query->where('doctor_profile_id', $request->doctor_id);
        }
        // Filter theo chuyên khoa
        if ($request->filled('specialty_id')) {
            $query->where('specialty_id', $request->specialty_id);
        }
        // Filter theo trạng thái
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        // Filter theo nguồn đặt
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }
        // Search theo mã lịch hẹn hoặc tên bệnh nhân
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('appointment_code', 'like', '%' . $request->search . '%')
                    ->orWhereHas(
                        'patientProfile',
                        fn($pq) =>
                        $pq->where('full_name', 'like', '%' . $request->search . '%')
                    );
            });
        }

        $appointments = $query->paginate(20)->withQueryString();

        // Data cho filter dropdowns
        $doctors = DoctorProfile::with('user')->whereHas('user', fn($q) => $q->where('is_active', true))->get();
        $specialties = Specialty::where('is_active', true)->orderBy('name')->get();

        // Thống kê nhanh theo filter hiện tại (không paginate)
        $totalCount = Appointment::when($request->filled('date_from'), fn($q) => $q->whereDate('appointment_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn($q) => $q->whereDate('appointment_date', '<=', $request->date_to))
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->when($request->filled('doctor_id'), fn($q) => $q->where('doctor_profile_id', $request->doctor_id))
            ->when($request->filled('specialty_id'), fn($q) => $q->where('specialty_id', $request->specialty_id))
            ->when($request->filled('source'), fn($q) => $q->where('source', $request->source))
            ->when($request->filled('search'), fn($q) => $q->where(function ($sq) use ($request) {
                $sq->where('appointment_code', 'like', '%' . $request->search . '%')
                    ->orWhereHas(
                        'patientProfile',
                        fn($pq) =>
                        $pq->where('full_name', 'like', '%' . $request->search . '%')
                    );
            }))
            ->count();

        // Aggregate counts by status based on the exact same filters
        $statusCounts = DB::table('appointments')
            ->select('status', DB::raw('count(*) as count'))
            ->when($request->filled('date_from'), fn($q) => $q->whereDate('appointment_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn($q) => $q->whereDate('appointment_date', '<=', $request->date_to))
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->when($request->filled('doctor_id'), fn($q) => $q->where('doctor_profile_id', $request->doctor_id))
            ->when($request->filled('specialty_id'), fn($q) => $q->where('specialty_id', $request->specialty_id))
            ->when($request->filled('source'), fn($q) => $q->where('source', $request->source))
            ->when($request->filled('search'), fn($q) => $q->where(function ($sq) use ($request) {
                $sq->where('appointment_code', 'like', '%' . $request->search . '%')
                    ->orWhereExists(function ($pq) use ($request) {
                        $pq->select(DB::raw(1))
                            ->from('patient_profiles')
                            ->whereColumn('patient_profiles.id', 'appointments.patient_profile_id')
                            ->where('patient_profiles.full_name', 'like', '%' . $request->search . '%');
                    });
            }))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return view('admin.appointments.index', compact('appointments', 'doctors', 'specialties', 'totalCount', 'statusCounts'));
    }

    public function calendar(Request $request)
    {
        $doctors = DoctorProfile::with('user')->whereHas('user', fn($q) => $q->where('is_active', true))->get();
        $specialties = Specialty::where('is_active', true)->orderBy('name')->get();

        // Get appointments for the calendar (usually current month)
        $appointments = Appointment::with(['patientProfile', 'doctor.user'])
            ->whereNotIn('status', ['cancelled'])
            ->get();

        // Format data for FullCalendar
        $events = $appointments->map(function ($apt) {
            $title = $apt->patientProfile->full_name . ' (' . ($apt->doctor->user->full_name ?? 'N/A') . ')';
            $start = $apt->appointment_date->format('Y-m-d') . 'T' . $apt->appointment_time;

            // Generate a simple end time (assume 30 mins slot)
            $end = \Carbon\Carbon::parse($start)->addMinutes(30)->format('Y-m-d\TH:i:s');

            $color = match ($apt->status) {
                'pending'    => '#eab308', // yellow-500
                'checked_in' => '#3b82f6', // blue-500
                'examining'  => '#a855f7', // purple-500
                'completed'  => '#22c55e', // green-500
                'absent'     => '#6b7280', // gray-500
                default      => '#9ca3af',
            };

            return [
                'id'    => $apt->id,
                'title' => $title,
                'start' => $start,
                'end'   => $end,
                'url'   => route('admin.appointments.show', $apt->id),
                'backgroundColor' => $color,
                'borderColor' => $color,
            ];
        });

        return view('admin.appointments.calendar', compact('doctors', 'specialties', 'events'));
    }

    public function show($id)
    {
        $appointment = Appointment::with([
            'patientProfile',
            'doctor.user',
            'doctor.specialties',
            'specialty',
            'room',
            'bookedByUser',
            'clinicalVisits.doctor.user',
            'clinicalVisits.room',
            'medicalRecord.prescription',
            'logs.changedBy',
        ])->findOrFail($id);

        return view('admin.appointments.show', compact('appointment'));
    }

    public function create()
    {
        $patients = PatientProfile::orderBy('full_name')->get();
        $specialties = Specialty::where('is_active', true)->orderBy('name')->get();
        $doctors = DoctorProfile::with('user')->whereHas('user', fn($q) => $q->where('is_active', true))->get();
        $rooms = Room::where('is_active', true)->orderBy('name')->get();
        $users = User::where('is_active', true)->orderBy('full_name')->get();

        return view('admin.appointments.create', compact('patients', 'specialties', 'doctors', 'rooms', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'patient_profile_id' => 'required|exists:patient_profiles,id',
            'specialty_id'       => 'required|exists:specialties,id',
            'doctor_profile_id'  => 'required|exists:doctor_profiles,id',
            'room_id'            => 'required|exists:rooms,id',
            'appointment_date'   => 'required|date|after_or_equal:today',
            'appointment_time'   => 'required',
            'status'             => 'required|in:pending,checked_in,examining,completed,cancelled,absent',
            'source'             => 'required|in:web,counter,chatbot',
            'reason'             => 'required|string',
            'receptionist_note'  => 'nullable|string',

            // Vitals
            'vital_pulse'        => 'nullable|integer|min:0',
            'vital_systolic_bp'  => 'nullable|integer|min:0',
            'vital_diastolic_bp' => 'nullable|integer|min:0',
            'vital_temperature'  => 'nullable|numeric|min:0',
            'vital_respiratory'  => 'nullable|integer|min:0',
            'vital_spo2'         => 'nullable|numeric|min:0',
            'vital_weight_kg'    => 'nullable|numeric|min:0',
            'vital_height_cm'    => 'nullable|numeric|min:0',
            'vital_bmi'          => 'nullable|numeric|min:0',
            'vital_note'         => 'nullable|string',
            'measured_by'        => 'nullable|exists:users,id',
        ]);

        // Tránh trùng lịch hẹn của cùng 1 bác sĩ tại cùng ngày và giờ
        $exists = Appointment::where('doctor_profile_id', $request->doctor_profile_id)
            ->whereDate('appointment_date', $request->appointment_date)
            ->whereTime('appointment_time', $request->appointment_time)
            ->exists();

        if ($exists) {
            return back()->withErrors(['appointment_time' => 'Bác sĩ đã có lịch hẹn vào ngày và khung giờ này. Vui lòng chọn khung giờ khác.'])->withInput();
        }

        $patient = PatientProfile::findOrFail($request->patient_profile_id);
        $doctor = DoctorProfile::findOrFail($request->doctor_profile_id);

        $appointmentCode = 'APT' . strtoupper(substr(uniqid(), -8));

        $checkedInAt = in_array($request->status, ['checked_in', 'examining', 'completed']) ? now() : null;
        $completedAt = $request->status === 'completed' ? now() : null;

        $appointment = Appointment::create([
            'appointment_code'   => $appointmentCode,
            'patient_profile_id' => $request->patient_profile_id,
            'booked_by_user_id'  => $patient->owner_id ?? Auth::id(),
            'specialty_id'       => $request->specialty_id,
            'doctor_level'       => $doctor->level,
            'room_id'            => $request->room_id,
            'doctor_profile_id'  => $request->doctor_profile_id,
            'appointment_date'   => $request->appointment_date,
            'appointment_time'   => $request->appointment_time,
            'reason'             => $request->reason,
            'status'             => $request->status,
            'source'             => $request->source,
            'receptionist_note'  => $request->receptionist_note,

            // Vitals
            'vital_pulse'        => $request->vital_pulse,
            'vital_systolic_bp'  => $request->vital_systolic_bp,
            'vital_diastolic_bp' => $request->vital_diastolic_bp,
            'vital_temperature'  => $request->vital_temperature,
            'vital_respiratory'  => $request->vital_respiratory,
            'vital_spo2'         => $request->vital_spo2,
            'vital_weight_kg'    => $request->vital_weight_kg,
            'vital_height_cm'    => $request->vital_height_cm,
            'vital_bmi'          => $request->vital_bmi,
            'vital_note'         => $request->vital_note,
            'measured_by'        => $request->measured_by,

            'checked_in_at'      => $checkedInAt,
            'completed_at'       => $completedAt,
        ]);

        AppointmentLog::create([
            'appointment_id' => $appointment->id,
            'old_status'     => null,
            'new_status'     => $appointment->status,
            'action'         => 'ADMIN_CREATE',
            'changed_by'     => Auth::id(),
            'reason'         => 'Khởi tạo lịch hẹn bởi Quản trị viên',
        ]);

        return redirect()->route('admin.appointments.index')->with('success', 'Tạo lịch hẹn mới thành công.');
    }

    public function edit($id)
    {
        $appointment = Appointment::findOrFail($id);
        $patients = PatientProfile::orderBy('full_name')->get();
        $specialties = Specialty::where('is_active', true)->orderBy('name')->get();
        $doctors = DoctorProfile::with('user')->whereHas('user', fn($q) => $q->where('is_active', true))->get();
        $rooms = Room::where('is_active', true)->orderBy('name')->get();
        $users = User::where('is_active', true)->orderBy('full_name')->get();

        return view('admin.appointments.edit', compact('appointment', 'patients', 'specialties', 'doctors', 'rooms', 'users'));
    }

    public function update(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);

        $request->validate([
            'patient_profile_id' => 'required|exists:patient_profiles,id',
            'specialty_id'       => 'required|exists:specialties,id',
            'doctor_profile_id'  => 'required|exists:doctor_profiles,id',
            'room_id'            => 'required|exists:rooms,id',
            'appointment_date'   => 'required|date',
            'appointment_time'   => 'required',
            'status'             => 'required|in:pending,checked_in,examining,completed,cancelled,absent',
            'source'             => 'required|in:web,counter,chatbot',
            'reason'             => 'required|string',
            'receptionist_note'  => 'nullable|string',

            // Vitals
            'vital_pulse'        => 'nullable|integer|min:0',
            'vital_systolic_bp'  => 'nullable|integer|min:0',
            'vital_diastolic_bp' => 'nullable|integer|min:0',
            'vital_temperature'  => 'nullable|numeric|min:0',
            'vital_respiratory'  => 'nullable|integer|min:0',
            'vital_spo2'         => 'nullable|numeric|min:0',
            'vital_weight_kg'    => 'nullable|numeric|min:0',
            'vital_height_cm'    => 'nullable|numeric|min:0',
            'vital_bmi'          => 'nullable|numeric|min:0',
            'vital_note'         => 'nullable|string',
            'measured_by'        => 'nullable|exists:users,id',
        ]);

        $exists = Appointment::where('doctor_profile_id', $request->doctor_profile_id)
            ->whereDate('appointment_date', $request->appointment_date)
            ->whereTime('appointment_time', $request->appointment_time)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['appointment_time' => 'Bác sĩ đã có lịch hẹn vào ngày và khung giờ này. Vui lòng chọn khung giờ khác.'])->withInput();
        }

        $patient = PatientProfile::findOrFail($request->patient_profile_id);
        $doctor = DoctorProfile::findOrFail($request->doctor_profile_id);

        $oldStatus = $appointment->status;
        $newStatus = $request->status;

        $appointment->patient_profile_id = $request->patient_profile_id;
        $appointment->booked_by_user_id = $patient->owner_id ?? Auth::id();
        $appointment->specialty_id = $request->specialty_id;
        $appointment->doctor_level = $doctor->level;
        $appointment->room_id = $request->room_id;
        $appointment->doctor_profile_id = $request->doctor_profile_id;
        $appointment->appointment_date = $request->appointment_date;
        $appointment->appointment_time = $request->appointment_time;
        $appointment->reason = $request->reason;
        $appointment->status = $request->status;
        $appointment->source = $request->source;
        $appointment->receptionist_note = $request->receptionist_note;

        $appointment->vital_pulse = $request->vital_pulse;
        $appointment->vital_systolic_bp = $request->vital_systolic_bp;
        $appointment->vital_diastolic_bp = $request->vital_diastolic_bp;
        $appointment->vital_temperature = $request->vital_temperature;
        $appointment->vital_respiratory = $request->vital_respiratory;
        $appointment->vital_spo2 = $request->vital_spo2;
        $appointment->vital_weight_kg = $request->vital_weight_kg;
        $appointment->vital_height_cm = $request->vital_height_cm;
        $appointment->vital_bmi = $request->vital_bmi;
        $appointment->vital_note = $request->vital_note;
        $appointment->measured_by = $request->measured_by;

        if (in_array($newStatus, ['checked_in', 'examining', 'completed']) && is_null($appointment->checked_in_at)) {
            $appointment->checked_in_at = now();
        }
        if ($newStatus === 'completed' && is_null($appointment->completed_at)) {
            $appointment->completed_at = now();
        }

        $appointment->save();

        if ($oldStatus !== $newStatus) {
            AppointmentLog::create([
                'appointment_id' => $appointment->id,
                'old_status'     => $oldStatus,
                'new_status'     => $newStatus,
                'action'         => 'ADMIN_UPDATE',
                'changed_by'     => Auth::id(),
                'reason'         => 'Cập nhật lịch hẹn và trạng thái bởi Quản trị viên',
            ]);
        }

        return redirect()->route('admin.appointments.index')->with('success', 'Cập nhật lịch hẹn thành công.');
    }


    public function destroy($id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->delete();

        return redirect()->route('admin.appointments.index')->with('success', 'Xoá lịch hẹn thành công.');
    }
}
