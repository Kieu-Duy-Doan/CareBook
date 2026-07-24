<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClinicalVisit;
use App\Models\Appointment;
use App\Models\Payment;
use App\Models\WorkSchedule;
use App\Services\AppointmentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClinicalVisitController extends Controller
{
    /**
     * Danh sách lịch hẹn có clinical visit của bác sĩ (gốc hoặc được chỉ định).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $doctorProfile = $user->doctorProfile;

        if (!$doctorProfile) {
            return redirect()->route('doctor.profile.index')->with('error', 'Vui lòng cập nhật hồ sơ bác sĩ.');
        }

        $query = Appointment::with([
            'patientProfile',
            'clinicalVisits' => function ($q) use ($doctorProfile) {
                $q->where('doctor_profile_id', $doctorProfile->id)->orderBy('visit_order');
            },
            'clinicalVisits.room',
            'payments',
            'medicalRecord',
        ])
            ->whereHas('clinicalVisits', function ($q) use ($doctorProfile) {
                $q->where('doctor_profile_id', $doctorProfile->id);
            })
            ->latest('appointment_date')
            ->latest('appointment_time');

        if ($request->filled('search')) {
            $search = AppointmentService::escapeLikeWildcards($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('appointment_code', 'like', '%' . $search . '%')
                    ->orWhereHas('patientProfile', function ($pq) use ($search) {
                        $pq->where('full_name', 'like', '%' . $search . '%');
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $appointments = $query->paginate(15)->withQueryString();

        return view('doctor.clinical-visits.index', compact('appointments'));
    }

    /**
     * Chi tiết lịch hẹn: load TOÀN BỘ visits (của mọi bác sĩ) để xem toàn luồng.
     */
    public function show($appointment_id, \App\Services\PaymentService $paymentService)
    {
        $user = Auth::user();
        $doctorProfile = $user->doctorProfile;

        $appointment = Appointment::with([
            'patientProfile',
            'specialty',
            'room',
            'clinicalVisits' => function ($q) {
                $q->orderBy('is_origin', 'desc')->orderBy('visit_order');
            },
            'clinicalVisits.room',
            'clinicalVisits.doctorProfile.user',
            'medicalRecord.prescription',
            'payments',
        ])
            ->whereHas('clinicalVisits', function ($q) use ($doctorProfile) {
                $q->where('doctor_profile_id', $doctorProfile->id);
            })
            ->findOrFail($appointment_id);

        $allVisits       = $appointment->clinicalVisits;
        $originVisit     = $allVisits->firstWhere('is_origin', true);
        $subVisits       = $allVisits->where('is_origin', false)->values();
        $totalVisits     = $subVisits->count();
        $completedVisits = $subVisits->whereIn('status', ['completed', 'refused'])->count();
        $allSubCompleted = $totalVisits > 0 && $completedVisits === $totalVisits;

        $summary = $paymentService->calculateSummary($appointment);
        $totalAmount  = $summary['total_amount'] ?? $allVisits->sum('payment_amount'); // Hiển thị nguyên giá
        $paidAmount   = $summary['amount_paid'] ?? 0;
        $unpaidAmount = $summary['remaining_to_pay'] ?? 0;

        // Bác sĩ hiện tại có phải bác sĩ gốc không?
        $isOriginDoctor = $originVisit && $originVisit->doctor_profile_id === $doctorProfile->id;

        $rooms = \App\Models\Room::where('is_active', true)
            ->where('room_type', 'diagnostic')
            ->get();

        // Danh sách room_id đã được chỉ định (trừ visit bị từ chối) để vô hiệu hoá trong form
        $assignedRoomIds = $subVisits
            ->whereNotIn('status', ['refused', 'redirected'])
            ->pluck('room_id')
            ->unique()
            ->values()
            ->toArray();

        return view('doctor.clinical-visits.show', compact(
            'appointment',
            'originVisit',
            'subVisits',
            'totalVisits',
            'completedVisits',
            'allSubCompleted',
            'totalAmount',
            'paidAmount',
            'unpaidAmount',
            'rooms',
            'isOriginDoctor',
            'assignedRoomIds'
        ));
    }

    /**
     * Bác sĩ ban đầu chỉ định phòng cận lâm sàng.
     * Tự động tìm bác sĩ trực tại phòng đó theo WorkSchedule.
     */
    public function storeVisit(Request $request, $appointment_id)
    {
        $request->validate([
            'room_id'        => 'required|exists:rooms,id',
            'findings'       => 'nullable|string',
            'payment_amount' => 'nullable|numeric|min:0',
        ]);

        $user          = Auth::user();
        $doctorProfile = $user->doctorProfile;

        // Chỉ bác sĩ có visit gốc mới được chỉ định phòng
        $appointment = Appointment::whereHas('clinicalVisits', function ($q) use ($doctorProfile) {
            $q->where('doctor_profile_id', $doctorProfile->id)->where('is_origin', true);
        })->findOrFail($appointment_id);

        // Kiểm tra trùng phòng: không cho chỉ định phòng đã có visit đang hoạt động
        $isDuplicate = ClinicalVisit::where('appointment_id', $appointment->id)
            ->where('room_id', $request->room_id)
            ->where('is_origin', false)
            ->whereNotIn('status', ['refused', 'redirected'])
            ->exists();

        if ($isDuplicate) {
            $roomName = \App\Models\Room::find($request->room_id)?->name ?? 'Phòng đã chọn';
            return back()->with('error', "Bệnh nhân đã được chỉ định đến \"$roomName\" trước đó. Vui lòng chọn phòng khác hoặc xóa chỉ định cũ trước.");
        }

        $originVisit = ClinicalVisit::where('appointment_id', $appointment->id)
            ->where('is_origin', true)
            ->firstOrFail();

        // Xác định ngày & giờ theo LỊCH HẸN (appointment_time) — không dùng now()
        // để gán đúng bác sĩ có ca trùng với khung giờ bệnh nhân đã đặt.
        $appointmentDayOfWeek = \Carbon\Carbon::parse($appointment->appointment_date)->dayOfWeek;
        $appointmentTime      = $appointment->appointment_time; // HH:MM:SS

        // 1. Ưu tiên cao nhất: Bác sĩ có ca bao phủ giờ hẹn, đúng ngày trong tuần
        $assignedDoctorProfileId = WorkSchedule::where('room_id', $request->room_id)
            ->where('day_of_week', $appointmentDayOfWeek)
            ->where('start_time', '<=', $appointmentTime)
            ->where('end_time',   '>=', $appointmentTime)
            ->where('is_active', true)
            ->value('doctor_profile_id');

        // 2. Fallback 1: Nếu không khớp chính xác giờ, lấy bác sĩ có ca sáng/chiều
        //    dựa vào giờ hẹn — sáng nếu < 12:00, chiều nếu >= 12:00
        if (!$assignedDoctorProfileId) {
            $shiftLabel = \Carbon\Carbon::parse($appointmentTime)->hour < 12 ? 'morning' : 'afternoon';
            $assignedDoctorProfileId = WorkSchedule::where('room_id', $request->room_id)
                ->where('day_of_week', $appointmentDayOfWeek)
                ->where('shift_label', $shiftLabel)
                ->where('is_active', true)
                ->value('doctor_profile_id');
        }

        // 3. Fallback 2: Tìm bất kỳ bác sĩ nào có lịch tại phòng trong ngày đó
        if (!$assignedDoctorProfileId) {
            $assignedDoctorProfileId = WorkSchedule::where('room_id', $request->room_id)
                ->where('day_of_week', $appointmentDayOfWeek)
                ->where('is_active', true)
                ->value('doctor_profile_id');
        }

        // 4. Fallback 3: Lấy bất kỳ bác sĩ nào từng được phân công tại phòng đó
        if (!$assignedDoctorProfileId) {
            $assignedDoctorProfileId = WorkSchedule::where('room_id', $request->room_id)
                ->where('is_active', true)
                ->value('doctor_profile_id');
        }

        // 4. Nếu phòng này thực sự chưa được cấu hình bác sĩ nào, báo lỗi
        if (!$assignedDoctorProfileId) {
            return back()->with('error', 'Không thể chỉ định: Phòng này hiện chưa có bác sĩ nào được phân công làm việc trong hệ thống.');
        }

        $maxOrder  = ClinicalVisit::where('appointment_id', $appointment->id)->max('visit_order');
        $nextOrder = $maxOrder ? $maxOrder + 1 : 2;

        $visit = ClinicalVisit::create([
            'appointment_id'    => $appointment->id,
            'parent_visit_id'   => $originVisit->id,
            'doctor_profile_id' => $assignedDoctorProfileId,
            'room_id'           => $request->room_id,
            'visit_order'       => $nextOrder,
            'is_origin'         => false,
            'status'            => 'waiting',
            'payment_status'    => 'pending',
            'payment_amount'    => $request->payment_amount ?? 0,
            'findings'          => $request->findings,
        ]);

        $roomName = \App\Models\Room::find($request->room_id)?->name ?? 'Phòng không xác định';
        $assignedDoctor = \App\Models\DoctorProfile::with('user')->find($assignedDoctorProfileId)?->user->full_name ?? 'Bác sĩ không xác định';

        \App\Models\AppointmentLog::create([
            'appointment_id' => $appointment->id,
            'action'         => 'CLINICAL_VISIT_CREATED',
            'old_status'     => null,
            'new_status'     => $appointment->status,
            'changed_by'     => Auth::id(),
            'reason'         => "Chỉ định bệnh nhân thực hiện dịch vụ tại phòng $roomName do bác sĩ $assignedDoctor phụ trách."
        ]);

        return back()->with('success', 'Đã chỉ định bệnh nhân đến phòng khám chuyên sâu.');
    }

    /**
     * Xóa chỉ định phòng (chỉ được xóa khi chưa bắt đầu, bởi bác sĩ gốc).
     */
    public function destroyVisit($visit_id)
    {
        $user          = Auth::user();
        $doctorProfile = $user->doctorProfile;

        $visit = ClinicalVisit::where('is_origin', false)
            ->where('status', 'waiting')
            ->whereHas('appointment.clinicalVisits', function ($q) use ($doctorProfile) {
                $q->where('doctor_profile_id', $doctorProfile->id)->where('is_origin', true);
            })
            ->findOrFail($visit_id);

        $roomName = $visit->room->name ?? 'phòng không xác định';
        $appointmentId = $visit->appointment_id;
        $appointmentStatus = $visit->appointment->status;

        $visit->delete();

        \App\Models\AppointmentLog::create([
            'appointment_id' => $appointmentId,
            'action'         => \App\Models\AppointmentLog::ACTION_CLINICAL_VISIT_DELETED,
            'old_status'     => null,
            'new_status'     => $appointmentStatus,
            'changed_by'     => Auth::id(),
            'reason'         => "Hủy chỉ định thực hiện dịch vụ tại $roomName."
        ]);

        return back()->with('success', 'Đã xóa chỉ định khám.');
    }

    /**
     * Cập nhật kết quả khám (chỉ bác sĩ được giao visit đó mới được cập nhật).
     */
    public function updateVisit(Request $request, $visit_id)
    {
        $request->validate([
            'findings'       => 'nullable|string',
            'status'         => 'required|in:waiting,in_progress,completed,refused,redirected',
            'payment_amount' => 'nullable|numeric|min:0',
            'result_files.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'refusal_reason' => 'nullable|string|max:500',
        ]);

        $user          = Auth::user();
        $doctorProfile = $user->doctorProfile;

        $visit = ClinicalVisit::where('doctor_profile_id', $doctorProfile->id)->findOrFail($visit_id);

        $visit->findings       = $request->findings;
        $visit->status         = $request->status;
        $visit->payment_amount = $request->payment_amount ?? $visit->payment_amount;

        if ($request->filled('refusal_reason')) {
            $visit->refusal_reason = $request->refusal_reason;
        }

        if ($request->hasFile('result_files')) {
            $files = $visit->result_files ?? [];
            foreach ($request->file('result_files') as $file) {
                $path   = $file->store('clinical_results', 'public');
                $files[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                ];
            }
            $visit->result_files = $files;
        }

        // Guard: CLS phải thanh toán xong mới được thực hiện (bắt đầu hoặc hoàn thành)
        if (in_array($request->status, ['in_progress', 'completed']) && $visit->payment_status !== 'paid' && $visit->payment_amount > 0) {
            return back()->with('error', 'Không thể thực hiện: Bệnh nhân chưa thanh toán dịch vụ cận lâm sàng này. Vui lòng yêu cầu thanh toán trước.');
        }

        if ($request->status === 'in_progress' && is_null($visit->started_at)) {
            $visit->started_at = now();
        }

        if (in_array($request->status, ['completed', 'refused']) && is_null($visit->completed_at)) {
            $visit->completed_at = now();
        }

        $visit->save();

        $roomName = $visit->room->name ?? 'phòng không xác định';
        $reason = "Đã cập nhật trạng thái thực hiện dịch vụ tại $roomName.";
        if ($request->status === 'in_progress') {
            $reason = "Bệnh nhân bắt đầu thực hiện dịch vụ tại $roomName.";
        } elseif ($request->status === 'completed') {
            $reason = "Bệnh nhân đã thực hiện xong dịch vụ tại $roomName.";
        } elseif ($request->status === 'refused') {
            $reason = "Bệnh nhân từ chối thực hiện dịch vụ tại $roomName với lý do: " . ($request->refusal_reason ?? 'Không có');
        }

        \App\Models\AppointmentLog::create([
            'appointment_id' => $visit->appointment_id,
            'action'         => 'CLINICAL_VISIT_UPDATED',
            'old_status'     => null,
            'new_status'     => $visit->appointment->status,
            'changed_by'     => Auth::id(),
            'reason'         => $reason
        ]);

        return back()->with('success', 'Đã cập nhật kết quả khám lâm sàng.');
    }
}
