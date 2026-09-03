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
use App\Services\AppointmentService;
use App\Http\Requests\Receptionist\StoreAppointmentRequest;
use App\Http\Requests\Receptionist\UpdateAppointmentRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    protected AppointmentService $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }
    public function index(Request $request)
    {
        if (!$request->has('date_from') && !$request->has('date_to') && !$request->has('search') && !$request->has('status')) {
            $request->merge([
                'date_from' => now()->toDateString(),
                'date_to' => now()->toDateString(),
            ]);
        }

        $query = Appointment::with([
            'patientProfile',
            'doctor.user',
            'specialty',
            'room',
            'bookedByUser'
        ])->orderBy('appointment_date', 'asc')->orderBy('appointment_time', 'asc');

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
            $search = AppointmentService::escapeLikeWildcards($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('appointment_code', 'like', '%' . $search . '%')
                    ->orWhereHas(
                        'patientProfile',
                        fn($pq) =>
                        $pq->where('full_name', 'like', '%' . $search . '%')
                           ->orWhere('phone', 'like', '%' . $search . '%')
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
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = AppointmentService::escapeLikeWildcards($request->search);
                $q->where(function ($sq) use ($search) {
                    $sq->where('appointment_code', 'like', '%' . $search . '%')
                        ->orWhereHas(
                            'patientProfile',
                            fn($pq) =>
                            $pq->where('full_name', 'like', '%' . $search . '%')
                               ->orWhere('phone', 'like', '%' . $search . '%')
                        );
                });
            })
            ->count();

        // Aggregate counts by status based on the exact same filters (excluding status filter itself)
        $statusCounts = DB::table('appointments')
            ->select('status', DB::raw('count(*) as count'))
            ->when($request->filled('date_from'), fn($q) => $q->whereDate('appointment_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn($q) => $q->whereDate('appointment_date', '<=', $request->date_to))
            ->when($request->filled('doctor_id'), fn($q) => $q->where('doctor_profile_id', $request->doctor_id))
            ->when($request->filled('specialty_id'), fn($q) => $q->where('specialty_id', $request->specialty_id))
            ->when($request->filled('source'), fn($q) => $q->where('source', $request->source))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = AppointmentService::escapeLikeWildcards($request->search);
                $q->where(function ($sq) use ($search) {
                    $sq->where('appointment_code', 'like', '%' . $search . '%')
                        ->orWhereExists(function ($pq) use ($search) {
                            $pq->select(DB::raw(1))
                                ->from('patient_profiles')
                                ->whereColumn('patient_profiles.id', 'appointments.patient_profile_id')
                                ->where('patient_profiles.full_name', 'like', '%' . $search . '%');
                        });
                });
            })
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
            'clinicalVisits.doctorProfile.user',
            'clinicalVisits.room',
            'clinicalVisits.payments',
            'medicalRecord.prescription',
            'payments.collectedBy',
            'logs' => function ($q) {
                $q->with('changedBy')->orderByDesc('created_at');
            },
        ])->findOrFail($id);

        $summary = app(\App\Services\PaymentService::class)->calculateSummary($appointment);

        return view('receptionist.appointments.show', compact('appointment', 'summary'));
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

    public function store(StoreAppointmentRequest $request)
    {
        $patient = PatientProfile::findOrFail($request->patient_profile_id);
        $doctor = DoctorProfile::findOrFail($request->doctor_profile_id);
        
        $data = $request->validated();
        if (!isset($data['measured_by'])) {
            $hasVitals = $request->filled('vital_pulse') || $request->filled('vital_systolic_bp') || $request->filled('vital_diastolic_bp') || 
                         $request->filled('vital_temperature') || $request->filled('vital_respiratory') || $request->filled('vital_spo2') || 
                         $request->filled('vital_weight_kg') || $request->filled('vital_height_cm');
            $data['measured_by'] = $hasVitals ? Auth::id() : null;
        }

        try {
            $this->appointmentService->storeByReceptionist($data, $doctor, $patient, Auth::id());
            return redirect()->route('receptionist.appointments.index')->with('success', 'Tạo lịch hẹn mới thành công.');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
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

    public function update(UpdateAppointmentRequest $request, $id)
    {
        $appointment = Appointment::findOrFail($id);
        
        if ($appointment->status === 'cancelled' && $request->status !== 'cancelled') {
            return back()->withInput()->with('error', 'Lịch hẹn đã ở trạng thái Đã huỷ, không thể chuyển sang trạng thái khác.');
        }

        if ($appointment->status === 'completed' && $request->status !== 'completed') {
            return back()->withInput()->with('error', 'Lịch hẹn đã ở trạng thái Hoàn thành, không thể chuyển sang trạng thái khác.');
        }

        $data = $request->validated();
        $isLocked = in_array($appointment->status, ['examining', 'completed', 'cancelled']);
        
        $doctor = null;
        $patient = null;
        
        if (!$isLocked) {
            $patient = PatientProfile::findOrFail($request->patient_profile_id);
            $doctor = DoctorProfile::findOrFail($request->doctor_profile_id);
            
            if (!isset($data['measured_by'])) {
                $hasVitals = $request->filled('vital_pulse') || $request->filled('vital_systolic_bp') || $request->filled('vital_diastolic_bp') || 
                             $request->filled('vital_temperature') || $request->filled('vital_respiratory') || $request->filled('vital_spo2') || 
                             $request->filled('vital_weight_kg') || $request->filled('vital_height_cm');
                $data['measured_by'] = $hasVitals ? Auth::id() : null;
            }
        }

        $this->appointmentService->updateByReceptionist($appointment, $data, Auth::id(), $doctor, $patient);

        return redirect()->route('receptionist.appointments.index')->with('success', 'Cập nhật lịch hẹn thành công.');
    }



    public function updateVitals(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);

        $data = $request->validate([
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
        ]);

        $hasVitals = $request->filled('vital_pulse') || $request->filled('vital_systolic_bp') || $request->filled('vital_diastolic_bp') || 
                     $request->filled('vital_temperature') || $request->filled('vital_respiratory') || $request->filled('vital_spo2') || 
                     $request->filled('vital_weight_kg') || $request->filled('vital_height_cm');
                     
        $data['measured_by'] = $hasVitals ? Auth::id() : null;

        $appointment->update($data);

        // Ghi log
        \App\Models\AppointmentLog::create([
            'appointment_id' => $appointment->id,
            'action'         => 'UPDATED',
            'old_status'     => $appointment->status,
            'new_status'     => $appointment->status,
            'changed_by'     => Auth::id(),
            'reason'         => 'Cập nhật chỉ số sinh tồn (Vitals).'
        ]);

        return back()->with('success', 'Cập nhật chỉ số sinh tồn thành công.');
    }

    public function destroy($id)
    {
        $appointment = Appointment::findOrFail($id);

        try {
            $this->appointmentService->destroyAppointment($appointment);

            return redirect()->route('receptionist.appointments.index')->with('success', 'Xoá lịch hẹn thành công.');
        } catch (\Exception $e) {
            return redirect()->route('receptionist.appointments.index')->with('error', 'Không thể xoá lịch hẹn này. Lịch hẹn có thể đang liên kết với dữ liệu khác trong hệ thống.');
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,checked_in,examining,completed,cancelled,absent,late',
            'reason' => 'nullable|string|max:500'
        ], [
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ.',
            'reason.max' => 'Lý do không được vượt quá 500 ký tự.',
        ]);

        $appointment = Appointment::findOrFail($id);
        $oldStatus = $appointment->status;
        $newStatus = $request->status;

        // Chặn chuyển đổi trạng thái của lịch hẹn đã bị huỷ
        if ($oldStatus === 'cancelled' && $newStatus !== 'cancelled') {
            return back()->with('error', 'Lịch hẹn đã ở trạng thái Đã huỷ, không thể chuyển sang trạng thái khác.');
        }

        // Chặn chuyển đổi trạng thái của lịch hẹn đã khám hoàn thành
        if ($oldStatus === 'completed' && $newStatus !== 'completed') {
            return back()->with('error', 'Không thể thay đổi trạng thái của lịch hẹn đã hoàn thành.');
        }

        // Chặn đổi trạng thái của lịch hẹn đang khám sang các trạng thái khác ngoài Hoàn thành
        if ($oldStatus === 'examining' && $newStatus !== 'examining' && $newStatus !== 'completed') {
            return back()->with('error', 'Lịch hẹn đang khám chỉ có thể chuyển sang trạng thái Hoàn thành.');
        }

        if ($oldStatus !== $newStatus) {
            DB::transaction(function () use ($appointment, $oldStatus, $newStatus, $request) {
                $appointment->status = $newStatus;

                // Đảm bảo cập nhật lại phí khám chuẩn xác để tránh lỗi 0đ khi Check-in nhanh
                $doctor = \App\Models\DoctorProfile::find($appointment->doctor_profile_id);
                if ($doctor && $doctor->level) {
                    $fee = \App\Models\DoctorLevelFee::where('level', $doctor->level)->first();
                    if ($fee) {
                        $appointment->total_fee = $fee->specific_price;
                    }
                }

                if (in_array($newStatus, ['checked_in', 'examining', 'completed']) && is_null($appointment->checked_in_at)) {
                    $appointment->checked_in_at = now();

                    // Tính toán đến muộn (>30 phút)
                    $appointmentDatetime = \Carbon\Carbon::parse($appointment->appointment_date->format('Y-m-d') . ' ' . $appointment->appointment_time);
                    if (now()->isAfter($appointmentDatetime->copy()->addMinutes(30))) {
                        $appointment->is_late = true;
                    } else {
                        $appointment->is_late = false;
                    }
                }
                if ($newStatus === 'completed' && is_null($appointment->completed_at)) {
                    $appointment->completed_at = now();
                }

                $appointment->save();

                // Đồng bộ lượt khám lâm sàng
                if (in_array($newStatus, ['checked_in', 'examining', 'completed'])) {
                    $this->appointmentService->createClinicalVisitIfNotExists($appointment, withPayment: true);
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
                    'action' => AppointmentLog::ACTION_RECEPTIONIST_STATUS_CHANGE,
                    'changed_by' => Auth::id(),
                    'reason' => $request->reason,
                ]);

                if ($newStatus === 'cancelled') {
                    \App\Jobs\ProcessAppointmentNotificationJob::dispatch($appointment, 'admin_cancel', 'Lễ tân');
                }
            });
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã cập nhật trạng thái lịch hẹn thành công.',
                'new_status' => $newStatus,
            ]);
        }

        return back()->with('success', 'Đã cập nhật trạng thái lịch hẹn thành công.');
    }

    public function exportCsv(Request $request)
    {
        if (!$request->has('date_from') && !$request->has('date_to') && !$request->has('search') && !$request->has('status')) {
            $request->merge([
                'date_from' => now()->toDateString(),
                'date_to' => now()->toDateString(),
            ]);
        }

        // Áp dụng cùng filter như index nhưng không paginate
        $query = Appointment::with(['patientProfile', 'doctor.user', 'specialty', 'room'])->orderBy('appointment_date', 'asc')->orderBy('appointment_time', 'asc');

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
            $search = AppointmentService::escapeLikeWildcards($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('appointment_code', 'like', '%' . $search . '%')
                    ->orWhereHas(
                        'patientProfile',
                        fn($pq) =>
                        $pq->where('full_name', 'like', '%' . $search . '%')
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


}
