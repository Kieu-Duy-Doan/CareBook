<?php

namespace App\Http\Controllers\Admin;

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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Admin\StoreAppointmentRequest;
use App\Http\Requests\Admin\UpdateAppointmentRequest;

class AppointmentController extends Controller
{
    protected AppointmentService $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }
    public function index(Request $request)
    {
        $query = Appointment::with([
            'patientProfile',
            'doctor.user',
            'specialty',
            'room',
            'bookedByUser'
        ])->latest('appointment_date')->latest('appointment_time');

        // Nếu là bác sĩ, chỉ cho phép xem lịch hẹn của mình
        if (Auth::user()->isDoctor() && Auth::user()->doctorProfile) {
            $query->where('doctor_profile_id', Auth::user()->doctorProfile->id);
        }

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
            ->when(Auth::user()->isDoctor() && Auth::user()->doctorProfile, fn($q) => $q->where('doctor_profile_id', Auth::user()->doctorProfile->id))
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

        // Aggregate counts by status based on the exact same filters
        $statusCounts = DB::table('appointments')
            ->select('status', DB::raw('count(*) as count'))
            ->when($request->filled('date_from'), fn($q) => $q->whereDate('appointment_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn($q) => $q->whereDate('appointment_date', '<=', $request->date_to))
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->when($request->filled('doctor_id'), fn($q) => $q->where('doctor_profile_id', $request->doctor_id))
            ->when($request->filled('specialty_id'), fn($q) => $q->where('specialty_id', $request->specialty_id))
            ->when($request->filled('source'), fn($q) => $q->where('source', $request->source))
            ->when(Auth::user()->isDoctor() && Auth::user()->doctorProfile, fn($q) => $q->where('doctor_profile_id', Auth::user()->doctorProfile->id))
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

        return view('admin.appointments.index', compact('appointments', 'doctors', 'specialties', 'totalCount', 'statusCounts'));
    }

    public function calendar(Request $request)
    {
        $doctors = DoctorProfile::with('user')->whereHas('user', fn($q) => $q->where('is_active', true))->get();
        $specialties = Specialty::where('is_active', true)->orderBy('name')->get();

        // Get appointments for the calendar — limit to requested month for performance
        $query = Appointment::with(['patientProfile', 'doctor.user'])
            ->whereNotIn('status', ['cancelled']);

        if ($request->filled('month')) {
            $query->whereMonth('appointment_date', substr($request->month, 5, 2))
                  ->whereYear('appointment_date', substr($request->month, 0, 4));
        } else {
            $query->whereMonth('appointment_date', now()->month)
                  ->whereYear('appointment_date', now()->year);
        }

        if (Auth::user()->isDoctor() && Auth::user()->doctorProfile) {
            $query->where('doctor_profile_id', Auth::user()->doctorProfile->id);
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
                'late'       => '#f97316', // orange-500
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
            'clinicalVisits.doctorProfile.user',
            'clinicalVisits.room',
            'medicalRecord.prescription',
            'logs.changedBy',
            'payments'
        ])->findOrFail($id);

        $clinicSettings = \App\Models\SystemSetting::whereIn('key', ['clinic_name', 'clinic_address', 'clinic_phone'])
            ->pluck('value', 'key')->toArray();

        return view('admin.appointments.show', compact('appointment', 'clinicSettings'));
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

    public function store(StoreAppointmentRequest $request)
    {
        $validated = $request->validated();

        $patient = PatientProfile::findOrFail($request->patient_profile_id);
        $doctor = DoctorProfile::findOrFail($request->doctor_profile_id);

        try {
            $this->appointmentService->storeByReceptionist($validated, $doctor, $patient, Auth::id());
            return redirect()->route('admin.appointments.index')->with('success', 'Tạo lịch hẹn mới thành công.');
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

        return view('admin.appointments.edit', compact('appointment', 'patients', 'specialties', 'doctors', 'rooms', 'users'));
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

        $validated = $request->validated();

        $patient = PatientProfile::findOrFail($request->patient_profile_id);
        $doctor = DoctorProfile::findOrFail($request->doctor_profile_id);

        $this->appointmentService->updateByReceptionist($appointment, $validated, Auth::id(), $doctor, $patient);

        return redirect()->route('admin.appointments.index')->with('success', 'Cập nhật lịch hẹn thành công.');
    }


    public function destroy($id)
    {
        $appointment = Appointment::findOrFail($id);

        // Guard: không cho xóa nếu có dữ liệu liên quan
        if ($appointment->payments()->exists()) {
            return redirect()->route('admin.appointments.index')
                ->with('error', 'Không thể xoá lịch hẹn này vì đã có dữ liệu thanh toán. Vui lòng sử dụng SQL để xóa nếu cần.');
        }
        if ($appointment->clinicalVisits()->exists()) {
            return redirect()->route('admin.appointments.index')
                ->with('error', 'Không thể xoá lịch hẹn này vì đã có dữ liệu khám lâm sàng. Vui lòng sử dụng SQL để xóa nếu cần.');
        }
        if ($appointment->medicalRecord) {
            return redirect()->route('admin.appointments.index')
                ->with('error', 'Không thể xoá lịch hẹn này vì đã có hồ sơ bệnh án. Vui lòng sử dụng SQL để xóa nếu cần.');
        }

        try {
            DB::transaction(function () use ($appointment) {
                $appointment->logs()->delete();
                $appointment->delete();
            });

            return redirect()->route('admin.appointments.index')->with('success', 'Xoá lịch hẹn thành công.');
        } catch (\Exception $e) {
            return redirect()->route('admin.appointments.index')->with('error', 'Không thể xoá lịch hẹn này. Lịch hẹn có thể đang liên kết với dữ liệu khác trong hệ thống.');
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

        // Chặn chuyển đổi trạng thái của lịch hẹn đã hoàn thành
        if ($oldStatus === 'completed' && $newStatus !== 'completed') {
            return back()->with('error', 'Không thể thay đổi trạng thái của lịch hẹn đã hoàn thành.');
        }

        if ($oldStatus !== $newStatus) {
            DB::transaction(function () use ($appointment, $request, $oldStatus, $newStatus) {
                $appointment->status = $newStatus;

                // Đảm bảo cập nhật lại phí khám chuẩn xác để tránh lỗi 0đ khi Check-in nhanh
                $doctor = DoctorProfile::find($appointment->doctor_profile_id);
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

                if (in_array($newStatus, ['checked_in', 'examining'])) {
                    $this->appointmentService->createClinicalVisitIfNotExists($appointment, withPayment: true);
                }

                AppointmentLog::create([
                    'appointment_id' => $appointment->id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'action' => AppointmentLog::ACTION_ADMIN_STATUS_CHANGE,
                    'changed_by' => Auth::id(),
                    'reason' => $request->reason,
                ]);

                if ($newStatus === 'cancelled') {
                    \App\Jobs\ProcessAppointmentNotificationJob::dispatch($appointment, 'admin_cancel');
                }
            });
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
        if (Auth::user()->isDoctor() && Auth::user()->doctorProfile) {
            $query->where('doctor_profile_id', Auth::user()->doctorProfile->id);
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
