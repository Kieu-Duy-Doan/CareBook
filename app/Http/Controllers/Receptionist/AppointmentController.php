<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\AppointmentLog;
use App\Models\ClinicalVisit;
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

        // Aggregate counts by status based on the exact same filters (excluding status filter itself)
        $statusCounts = DB::table('appointments')
            ->select('status', DB::raw('count(*) as count'))
            ->when($request->filled('date_from'), fn($q) => $q->whereDate('appointment_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn($q) => $q->whereDate('appointment_date', '<=', $request->date_to))
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

        return view('receptionist.appointments.index', compact('appointments', 'doctors', 'specialties', 'totalCount', 'statusCounts'));
    }

    public function calendar(Request $request)
    {
        $doctors = DoctorProfile::with('user')->whereHas('user', fn($q) => $q->where('is_active', true))->get();
        $specialties = Specialty::where('is_active', true)->orderBy('name')->get();

        // Get appointments for the calendar (filtered by range if provided)
        $query = Appointment::with(['patientProfile', 'doctor.user'])
            ->whereNotIn('status', ['cancelled']);

        if ($request->filled('start') && $request->filled('end')) {
            $query->whereBetween('appointment_date', [
                substr($request->start, 0, 10),
                substr($request->end, 0, 10)
            ]);
        } else {
            $query->whereMonth('appointment_date', now()->month)
                  ->whereYear('appointment_date', now()->year);
        }

        $appointments = $query->get();

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
                'url'   => route('receptionist.appointments.show', $apt->id),
                'backgroundColor' => $color,
                'borderColor' => $color,
            ];
        });

        return view('receptionist.appointments.calendar', compact('doctors', 'specialties', 'events'));
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

        return view('receptionist.appointments.show', compact('appointment'));
    }

    public function create()
    {
        $patients = PatientProfile::orderBy('full_name')->get();
        $specialties = Specialty::where('is_active', true)->orderBy('name')->get();
        $doctors = DoctorProfile::with('user')->whereHas('user', fn($q) => $q->where('is_active', true))->get();
        $rooms = Room::where('is_active', true)->orderBy('name')->get();
        $users = User::where('is_active', true)->orderBy('full_name')->get();

        return view('receptionist.appointments.create', compact('patients', 'specialties', 'doctors', 'rooms', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'patient_profile_id' => 'required|exists:patient_profiles,id',
            'specialty_id'       => 'required|exists:specialties,id',
            'doctor_profile_id'  => 'required|exists:doctor_profiles,id',
            'room_id'            => 'required|exists:rooms,id,is_active,1',
            'appointment_date'   => 'required|date|after_or_equal:today',
            'appointment_time'   => 'required',
            'status'             => 'required|in:pending,checked_in,examining,completed,cancelled,absent',
            'source'             => 'required|in:web,counter,chatbot',
            'reason'             => 'required|string',

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

        // Xác thực bác sĩ thuộc chuyên khoa được chọn
        $doctorBelongsToSpecialty = DB::table('doctor_specialties')
            ->where('doctor_profile_id', $request->doctor_profile_id)
            ->where('specialty_id', $request->specialty_id)
            ->exists();

        if (!$doctorBelongsToSpecialty) {
            return back()->withErrors(['doctor_profile_id' => 'Bác sĩ được chọn không thuộc chuyên khoa đã chỉ định.'])->withInput();
        }

        // Kiểm tra trùng lịch hẹn (Chống trùng lịch bác sĩ và trùng lịch bệnh nhân)
        if ($request->status !== 'cancelled') {
            // 1. Kiểm tra bác sĩ trùng lịch
            $doctorConflict = Appointment::where('doctor_profile_id', $request->doctor_profile_id)
                ->whereDate('appointment_date', $request->appointment_date)
                ->whereTime('appointment_time', $request->appointment_time)
                ->where('status', '!=', 'cancelled')
                ->exists();

            if ($doctorConflict) {
                return back()->withErrors(['appointment_time' => 'Bác sĩ này đã có lịch hẹn khác vào khung giờ này. Vui lòng chọn giờ khác.'])->withInput();
            }

            // 2. Kiểm tra bệnh nhân trùng lịch
            $patientConflict = Appointment::where('patient_profile_id', $request->patient_profile_id)
                ->whereDate('appointment_date', $request->appointment_date)
                ->whereTime('appointment_time', $request->appointment_time)
                ->where('status', '!=', 'cancelled')
                ->exists();

            if ($patientConflict) {
                return back()->withErrors(['appointment_time' => 'Bệnh nhân này đã có lịch hẹn khác vào cùng khung giờ này.'])->withInput();
            }
        }

        $patient = PatientProfile::findOrFail($request->patient_profile_id);
        $doctor = DoctorProfile::findOrFail($request->doctor_profile_id);

        $appointmentCode = 'APT' . strtoupper(substr(uniqid(), -8));

        $checkedInAt = in_array($request->status, ['checked_in', 'examining', 'completed']) ? now() : null;
        $completedAt = $request->status === 'completed' ? now() : null;

        $totalFee = 0;
        if ($doctor->level) {
            $fee = \App\Models\DoctorLevelFee::where('level', $doctor->level)->first();
            $totalFee = $fee ? $fee->specific_price : 0;
        }

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
            'total_fee'          => $totalFee,
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

        // Tạo lượt khám lâm sàng nếu trạng thái hợp lệ
        if (in_array($appointment->status, ['checked_in', 'examining', 'completed'])) {
            $this->createClinicalVisitIfNotExists($appointment);
        }

        AppointmentLog::create([
            'appointment_id' => $appointment->id,
            'old_status'     => null,
            'new_status'     => $appointment->status,
            'action'         => 'ADMIN_CREATE',
            'changed_by'     => Auth::id(),
            'reason'         => 'Khởi tạo lịch hẹn bởi Quản trị viên',
        ]);

        return redirect()->route('receptionist.appointments.index')->with('success', 'Tạo lịch hẹn mới thành công.');
    }

    public function edit($id)
    {
        $appointment = Appointment::findOrFail($id);
        $patients = PatientProfile::orderBy('full_name')->get();
        $specialties = Specialty::where('is_active', true)->orderBy('name')->get();

        $currentSpecialtyId = old('specialty_id', $appointment->specialty_id);

        $doctors = DoctorProfile::with('user')
            ->whereHas('user', fn($q) => $q->where('is_active', true))
            ->whereHas('specialties', fn($q) => $q->where('specialties.id', $currentSpecialtyId))
            ->get();

        $rooms = Room::where('is_active', true)->orderBy('name')->get();
        $users = User::where('is_active', true)->orderBy('full_name')->get();

        return view('receptionist.appointments.edit', compact('appointment', 'patients', 'specialties', 'doctors', 'rooms', 'users'));
    }

    public function update(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);

        // Khóa chỉnh sửa đối với lịch đã khám hoặc đang khám
        $isLocked = in_array($appointment->status, ['examining', 'completed']);

        if ($isLocked) {
            $restrictedFields = [
                'patient_profile_id', 'specialty_id', 'doctor_profile_id', 'room_id',
                'appointment_date', 'appointment_time', 'source', 'reason',
                'vital_pulse', 'vital_systolic_bp', 'vital_diastolic_bp', 'vital_temperature',
                'vital_respiratory', 'vital_spo2', 'vital_weight_kg', 'vital_height_cm',
                'vital_bmi', 'vital_note', 'measured_by'
            ];

            $changedRestricted = [];
            foreach ($restrictedFields as $field) {
                if ($request->has($field)) {
                    $reqValue = $request->input($field);
                    $dbValue = $appointment->getAttribute($field);

                    if ($field === 'appointment_date' && $dbValue instanceof \Carbon\Carbon) {
                        $dbValue = $dbValue->format('Y-m-d');
                    }

                    if ($reqValue !== null && $reqValue !== '') {
                        if ($dbValue != $reqValue) {
                            $changedRestricted[] = $field;
                        }
                    } else {
                        if ($dbValue !== null && $dbValue !== '') {
                            $changedRestricted[] = $field;
                        }
                    }
                }
            }

            if (!empty($changedRestricted)) {
                return back()->withErrors([
                    'status' => 'Lịch hẹn đang khám hoặc đã hoàn thành. Lễ tân chỉ được cập nhật ghi chú và trạng thái của lịch hẹn.'
                ])->withInput();
            }
        }

        $request->validate([
            'patient_profile_id' => 'required|exists:patient_profiles,id',
            'specialty_id'       => 'required|exists:specialties,id',
            'doctor_profile_id'  => 'required|exists:doctor_profiles,id',
            'room_id'            => 'required|exists:rooms,id,is_active,1',
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

        // Xác thực bác sĩ thuộc chuyên khoa được chọn
        $doctorBelongsToSpecialty = DB::table('doctor_specialties')
            ->where('doctor_profile_id', $request->doctor_profile_id)
            ->where('specialty_id', $request->specialty_id)
            ->exists();

        if (!$doctorBelongsToSpecialty) {
            return back()->withErrors(['doctor_profile_id' => 'Bác sĩ được chọn không thuộc chuyên khoa đã chỉ định.'])->withInput();
        }

        // Kiểm tra trùng lịch hẹn (Chống trùng lịch bác sĩ và trùng lịch bệnh nhân, loại trừ chính lịch này)
        if ($request->status !== 'cancelled') {
            // 1. Kiểm tra bác sĩ trùng lịch
            $doctorConflict = Appointment::where('doctor_profile_id', $request->doctor_profile_id)
                ->whereDate('appointment_date', $request->appointment_date)
                ->whereTime('appointment_time', $request->appointment_time)
                ->where('status', '!=', 'cancelled')
                ->where('id', '!=', $id)
                ->exists();

            if ($doctorConflict) {
                return back()->withErrors(['appointment_time' => 'Bác sĩ này đã có lịch hẹn khác vào khung giờ này. Vui lòng chọn giờ khác.'])->withInput();
            }

            // 2. Kiểm tra bệnh nhân trùng lịch
            $patientConflict = Appointment::where('patient_profile_id', $request->patient_profile_id)
                ->whereDate('appointment_date', $request->appointment_date)
                ->whereTime('appointment_time', $request->appointment_time)
                ->where('status', '!=', 'cancelled')
                ->where('id', '!=', $id)
                ->exists();

            if ($patientConflict) {
                return back()->withErrors(['appointment_time' => 'Bệnh nhân này đã có lịch hẹn khác vào cùng khung giờ này.'])->withInput();
            }
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

        $totalFee = 0;
        if ($doctor->level) {
            $fee = \App\Models\DoctorLevelFee::where('level', $doctor->level)->first();
            $totalFee = $fee ? $fee->specific_price : 0;
        }
        $appointment->total_fee = $totalFee;

        if (in_array($newStatus, ['checked_in', 'examining', 'completed']) && is_null($appointment->checked_in_at)) {
            $appointment->checked_in_at = now();
        }
        if ($newStatus === 'completed' && is_null($appointment->completed_at)) {
            $appointment->completed_at = now();
        }

        $appointment->save();

        // Đồng bộ lượt khám lâm sàng
        if (in_array($newStatus, ['checked_in', 'examining', 'completed'])) {
            $this->createClinicalVisitIfNotExists($appointment);
        }

        // Tự động dọn dẹp lượt khám chưa khám nếu hủy lịch/đổi về chờ khám
        if (in_array($newStatus, ['cancelled', 'pending'])) {
            $visit = ClinicalVisit::where('appointment_id', $appointment->id)->first();
            if ($visit && $visit->status === 'waiting') {
                $visit->delete();
            }
        }

        if ($oldStatus !== $newStatus) {
            AppointmentLog::create([
                'appointment_id' => $appointment->id,
                'old_status'     => $oldStatus,
                'new_status'     => $newStatus,
                'action'         => 'ADMIN_UPDATE',
                'changed_by'     => Auth::id(),
                'reason'         => 'Cập nhật lịch hẹn và trạng thái bởi Quản trị viên',
            ]);

            if ($newStatus === 'cancelled') {
                \App\Jobs\ProcessAppointmentNotificationJob::dispatch($appointment, 'cancellation');
            }
        }

        return redirect()->route('receptionist.appointments.index')->with('success', 'Cập nhật lịch hẹn thành công.');
    }


    public function destroy($id)
    {
        $appointment = Appointment::findOrFail($id);

        try {
            DB::transaction(function () use ($appointment) {
                // Xoá lượt khám lâm sàng liên quan trước
                ClinicalVisit::where('appointment_id', $appointment->id)->delete();
                // Xoá logs liên quan trước (do có ràng buộc restrictOnDelete)
                $appointment->logs()->delete();
                $appointment->delete();
            });

            return redirect()->route('receptionist.appointments.index')->with('success', 'Xoá lịch hẹn thành công.');
        } catch (\Exception $e) {
            return redirect()->route('receptionist.appointments.index')->with('error', 'Không thể xoá lịch hẹn này. Lịch hẹn có thể đang liên kết với dữ liệu khác trong hệ thống.');
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,checked_in,examining,completed,cancelled,absent',
            'reason' => 'nullable|string|max:500'
        ]);

        $appointment = Appointment::findOrFail($id);
        $oldStatus = $appointment->status;
        $newStatus = $request->status;

        // Chặn chuyển đổi trạng thái của lịch hẹn đã khám hoàn thành
        if ($oldStatus === 'completed' && $newStatus !== 'completed') {
            return back()->with('error', 'Không thể thay đổi trạng thái của lịch hẹn đã hoàn thành.');
        }

        if ($oldStatus !== $newStatus) {
            $appointment->status = $newStatus;

            if (in_array($newStatus, ['checked_in', 'examining', 'completed']) && is_null($appointment->checked_in_at)) {
                $appointment->checked_in_at = now();
            }
            if ($newStatus === 'completed' && is_null($appointment->completed_at)) {
                $appointment->completed_at = now();
            }

            $appointment->save();

            // Đồng bộ lượt khám lâm sàng
            if (in_array($newStatus, ['checked_in', 'examining', 'completed'])) {
                $this->createClinicalVisitIfNotExists($appointment);
            }

            // Tự động dọn dẹp lượt khám chưa khám nếu hủy lịch/đổi về chờ khám
            if (in_array($newStatus, ['cancelled', 'pending'])) {
                $visit = ClinicalVisit::where('appointment_id', $appointment->id)->first();
                if ($visit && $visit->status === 'waiting') {
                    $visit->delete();
                }
            }

            AppointmentLog::create([
                'appointment_id' => $appointment->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'action' => 'ADMIN_STATUS_CHANGE',
                'changed_by' => Auth::id(),
                'reason' => $request->reason,
            ]);

            if ($newStatus === 'cancelled') {
                \App\Jobs\ProcessAppointmentNotificationJob::dispatch($appointment, 'cancellation');
            }
        }

        return back()->with('success', 'Đã cập nhật trạng thái lịch hẹn thành công.');
    }

    public function exportCsv(Request $request)
    {
        // Áp dụng cùng filter như index nhưng không paginate
        $query = Appointment::with(['patientProfile', 'doctor.user', 'specialty', 'room'])->latest('appointment_date')->latest('appointment_time');

        if ($request->filled('date_from')) {
            $query->whereDate('appointment_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('appointment_date', '<=', $request->date_to);
        }
        if ($request->filled('doctor_id')) {
            $query->where('doctor_profile_id', $request->doctor_id);
        }
        if ($request->filled('specialty_id')) {
            $query->where('specialty_id', $request->specialty_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }
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

        $appointments = $query->get();

        $filename = 'lich-hen-' . now()->format('Ymd-His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($appointments) {
            $file = fopen('php://output', 'w');
            // BOM để Excel đọc UTF-8 đúng
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            // Header row
            fputcsv($file, ['Mã LH', 'Bệnh nhân', 'Bác sĩ', 'Chuyên khoa', 'Phòng', 'Ngày khám', 'Giờ khám', 'Trạng thái', 'Nguồn', 'Ngày đặt']);
            foreach ($appointments as $a) {
                fputcsv($file, [
                    $a->appointment_code,
                    $a->patientProfile->full_name ?? '',
                    $a->doctor->full_title ?? '',
                    $a->specialty->name ?? '',
                    $a->room->name ?? '',
                    $a->appointment_date ? $a->appointment_date->format('d/m/Y') : '',
                    $a->appointment_time ? substr($a->appointment_time, 0, 5) : '',
                    $a->status_label ?? $a->status,
                    $a->source_label ?? $a->source,
                    $a->created_at->format('d/m/Y H:i'),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function createClinicalVisitIfNotExists(Appointment $appointment)
    {
        // Check if visit already exists
        if (ClinicalVisit::where('appointment_id', $appointment->id)->exists()) {
            return;
        }

        // Calculate visit order
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
            'payment_amount' => $appointment->total_fee ?? 0,
            'payment_status' => ($appointment->total_fee ?? 0) > 0 ? 'pending' : 'paid',
        ]);
    }
}