<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\AppointmentLog;
use App\Models\ClinicalVisit;
use App\Services\AppointmentService;
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
        $user = Auth::user();
        $doctorProfile = $user->doctorProfile;

        if (!$doctorProfile) {
            return redirect()->route('doctor.profile.index')->with('error', 'Vui lòng cập nhật hồ sơ bác sĩ.');
        }

        $query = Appointment::with(['patientProfile', 'specialty', 'room', 'bookedByUser'])
            ->where('doctor_profile_id', $doctorProfile->id);

        // Nếu không có bất kỳ tham số lọc nào trên URL (vào trang lần đầu hoặc bấm Đặt lại)
        if (!$request->hasAny(['date_from', 'date_to', 'status', 'search'])) {
            $query->whereDate('appointment_date', today())
                  ->where('status', '!=', 'pending')
                  ->orderByRaw("CASE WHEN status = 'checked_in' THEN 1 ELSE 2 END ASC")
                  ->orderBy('appointment_time', 'asc');
        } else {
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
                $search = AppointmentService::escapeLikeWildcards($request->search);
                $query->where(function ($q) use ($search) {
                    $q->where('appointment_code', 'like', '%' . $search . '%')
                        ->orWhereHas('patientProfile', function($pq) use ($search) {
                            $pq->where('full_name', 'like', '%' . $search . '%');
                        });
                });
            }
            // Sắp xếp mặc định khi có tìm kiếm/lọc
            $query->latest('appointment_date')
                  ->latest('appointment_time');
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
            'payments.collectedBy',
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
        ->paginate(5)
        ->appends(['tab' => 'history']);

        return view('doctor.appointments.show', compact('appointment', 'pastAppointments'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,checked_in,examining,completed,cancelled,absent,late',
            'reason' => 'nullable|string|max:500'
        ]);

        $user = Auth::user();
        $doctorProfile = $user->doctorProfile;

        $appointment = Appointment::where('doctor_profile_id', $doctorProfile->id)
            ->with(['clinicalVisits', 'medicalRecord.prescription'])
            ->findOrFail($id);
        
        $oldStatus = $appointment->status;
        $newStatus = $request->status;

        // Guard: kiểm tra điều kiện hoàn thành
        if ($newStatus === 'completed') {
            // Phải có MedicalRecord
            if (!$appointment->medicalRecord) {
                return back()->with('error', 'Vui lòng ghi kết luận bệnh án trước khi hoàn thành.');
            }

            // Tất cả ClinicalVisit phải đã hoàn thành
            $pendingVisits = $appointment->clinicalVisits
                ->whereNotIn('status', ['completed', 'refused'])
                ->count();

            if ($pendingVisits > 0) {
                return back()->with('error', "Còn {$pendingVisits} phòng khám chưa hoàn thành. Vui lòng đợi kết quả từ tất cả phòng được chỉ định.");
            }
        }

        if ($oldStatus !== $newStatus) {
            DB::transaction(function () use ($appointment, $request, $oldStatus, $newStatus) {
                $appointment->status = $newStatus;

                if ($newStatus === 'checked_in' && is_null($appointment->checked_in_at)) {
                    $appointment->checked_in_at = now();
                }
                if ($newStatus === 'completed' && is_null($appointment->completed_at)) {
                    $appointment->completed_at = now();
                }

                $appointment->save();

                if (in_array($newStatus, ['checked_in', 'examining'])) {
                    $this->appointmentService->createClinicalVisitIfNotExists($appointment, withPayment: true);
                }

                // Cập nhật started_at cho ClinicalVisit gốc khi bắt đầu khám
                if ($newStatus === 'examining') {
                    ClinicalVisit::where('appointment_id', $appointment->id)
                        ->where('is_origin', true)
                        ->whereNull('started_at')
                        ->update(['started_at' => now(), 'status' => 'in_progress']);
                }

                AppointmentLog::create([
                    'appointment_id' => $appointment->id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'action' => AppointmentLog::ACTION_DOCTOR_STATUS_CHANGE,
                    'changed_by' => Auth::id(),
                    'reason' => $request->reason,
                ]);
                
                if ($newStatus === 'cancelled') {
                    \App\Jobs\ProcessAppointmentNotificationJob::dispatch($appointment, 'cancellation');
                }
            });
        }

        return back()->with('success', 'Cập nhật trạng thái lịch hẹn thành công.');
    }


}
