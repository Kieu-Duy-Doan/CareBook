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
use App\Http\Requests\Doctor\StoreClinicalVisitRequest;
use App\Http\Requests\Doctor\UpdateClinicalVisitRequest;

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
        $insuranceCovers = $summary['insurance_covers'] ?? 0;
        $patientPays  = $summary['patient_pays'] ?? $totalAmount;

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
            'insuranceCovers',
            'patientPays',
            'rooms',
            'isOriginDoctor',
            'assignedRoomIds'
        ));
    }

    /**
     * Bác sĩ ban đầu chỉ định phòng cận lâm sàng.
     * Tự động tìm bác sĩ trực tại phòng đó theo WorkSchedule.
     */
    public function storeVisit(StoreClinicalVisitRequest $request, $appointment_id, \App\Services\ClinicalService $clinicalService)
    {
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

        try {
            $clinicalService->assignClinicalVisit($appointment, $originVisit, $request->validated());
            return back()->with('success', 'Đã chỉ định bệnh nhân đến phòng khám chuyên sâu.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Xóa chỉ định phòng (chỉ được xóa khi chưa bắt đầu, bởi bác sĩ gốc).
     */
    public function destroyVisit($visit_id, \App\Services\ClinicalService $clinicalService)
    {
        $user          = Auth::user();
        $doctorProfile = $user->doctorProfile;

        $visit = ClinicalVisit::where('is_origin', false)
            ->where('status', 'waiting')
            ->whereHas('appointment.clinicalVisits', function ($q) use ($doctorProfile) {
                $q->where('doctor_profile_id', $doctorProfile->id)->where('is_origin', true);
            })
            ->findOrFail($visit_id);

        $clinicalService->deleteClinicalVisit($visit);

        return back()->with('success', 'Đã xóa chỉ định khám.');
    }

    /**
     * Cập nhật kết quả khám (chỉ bác sĩ được giao visit đó mới được cập nhật).
     */
    public function updateVisit(UpdateClinicalVisitRequest $request, $visit_id, \App\Services\ClinicalService $clinicalService)
    {
        $user          = Auth::user();
        $doctorProfile = $user->doctorProfile;

        $visit = ClinicalVisit::where('doctor_profile_id', $doctorProfile->id)->findOrFail($visit_id);

        try {
            $clinicalService->updateClinicalVisit($visit, $request->validated(), $request->file('result_files'));
            return back()->with('success', 'Đã cập nhật kết quả khám lâm sàng.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
