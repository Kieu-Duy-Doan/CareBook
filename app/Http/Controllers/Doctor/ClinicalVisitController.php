<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClinicalVisit;
use App\Models\Appointment;
use App\Models\Payment;
use App\Models\WorkSchedule;
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
                'clinicalVisits' => function($q) use ($doctorProfile) {
                    $q->where('doctor_profile_id', $doctorProfile->id)->orderBy('visit_order');
                },
                'clinicalVisits.room',
                'payments',
                'medicalRecord',
            ])
            ->whereHas('clinicalVisits', function($q) use ($doctorProfile) {
                $q->where('doctor_profile_id', $doctorProfile->id);
            })
            ->latest('appointment_date')
            ->latest('appointment_time');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('appointment_code', 'like', '%' . $request->search . '%')
                  ->orWhereHas('patientProfile', function($pq) use ($request) {
                      $pq->where('full_name', 'like', '%' . $request->search . '%');
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
    public function show($appointment_id)
    {
        $user = Auth::user();
        $doctorProfile = $user->doctorProfile;

        $appointment = Appointment::with([
            'patientProfile',
            'specialty',
            'room',
            'clinicalVisits' => function($q) {
                $q->orderBy('is_origin', 'desc')->orderBy('visit_order');
            },
            'clinicalVisits.room',
            'clinicalVisits.doctorProfile.user',
            'medicalRecord.prescription',
            'payments',
        ])
        ->whereHas('clinicalVisits', function($q) use ($doctorProfile) {
            $q->where('doctor_profile_id', $doctorProfile->id);
        })
        ->findOrFail($appointment_id);

        $allVisits       = $appointment->clinicalVisits;
        $originVisit     = $allVisits->firstWhere('is_origin', true);
        $subVisits       = $allVisits->where('is_origin', false)->values();
        $totalVisits     = $subVisits->count();
        $completedVisits = $subVisits->whereIn('status', ['completed', 'refused'])->count();
        $allSubCompleted = $totalVisits > 0 && $completedVisits === $totalVisits;

        $totalAmount  = $allVisits->sum('payment_amount');
        $paidAmount   = $appointment->payments->sum('amount');
        $unpaidAmount = max(0, $totalAmount - $paidAmount);

        // Bác sĩ hiện tại có phải bác sĩ gốc không?
        $isOriginDoctor = $originVisit && $originVisit->doctor_profile_id === $doctorProfile->id;

        $rooms = \App\Models\Room::where('is_active', true)
            ->where('room_type', 'diagnostic')
            ->get();

        return view('doctor.clinical-visits.show', compact(
            'appointment', 'originVisit', 'subVisits',
            'totalVisits', 'completedVisits', 'allSubCompleted',
            'totalAmount', 'paidAmount', 'unpaidAmount',
            'rooms', 'isOriginDoctor'
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
        $appointment = Appointment::whereHas('clinicalVisits', function($q) use ($doctorProfile) {
            $q->where('doctor_profile_id', $doctorProfile->id)->where('is_origin', true);
        })->findOrFail($appointment_id);

        $originVisit = ClinicalVisit::where('appointment_id', $appointment->id)
            ->where('is_origin', true)
            ->firstOrFail();

        // 1. Ưu tiên cao nhất: Tìm bác sĩ đang trực tại phòng được chỉ định (đúng giờ, đúng ngày)
        $dayOfWeek   = now()->dayOfWeek;
        $currentTime = now()->format('H:i:s');

        $assignedDoctorProfileId = WorkSchedule::where('room_id', $request->room_id)
            ->where('day_of_week', $dayOfWeek)
            ->where('start_time', '<=', $currentTime)
            ->where('end_time', '>=', $currentTime)
            ->where('is_active', true)
            ->value('doctor_profile_id');

        // 2. Fallback 1: Nếu ngoài giờ, tìm bác sĩ có ca trực trong HÔM NAY tại phòng đó
        if (!$assignedDoctorProfileId) {
            $assignedDoctorProfileId = WorkSchedule::where('room_id', $request->room_id)
                ->where('day_of_week', $dayOfWeek)
                ->where('is_active', true)
                ->value('doctor_profile_id');
        }

        // 3. Fallback 2: Nếu hôm nay không ai trực, tìm BẤT KỲ bác sĩ nào từng có lịch tại phòng đó
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

        ClinicalVisit::create([
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
            ->whereHas('appointment.clinicalVisits', function($q) use ($doctorProfile) {
                $q->where('doctor_profile_id', $doctorProfile->id)->where('is_origin', true);
            })
            ->findOrFail($visit_id);

        $visit->delete();

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

        if ($request->status === 'in_progress' && is_null($visit->started_at)) {
            $visit->started_at = now();
        }
        if (in_array($request->status, ['completed', 'refused']) && is_null($visit->completed_at)) {
            $visit->completed_at = now();
        }

        $visit->save();

        return back()->with('success', 'Đã cập nhật kết quả khám lâm sàng.');
    }

    public function processPayment(Request $request, $appointment_id)
    {
        $request->validate([
            'payment_method' => 'required|in:cash,qr,insurance,waived',
            'amount' => 'required|numeric|min:1',
            'notes' => 'nullable|string'
        ]);

        $user = Auth::user();
        $doctorProfile = $user->doctorProfile;

        $appointment = Appointment::whereHas('clinicalVisits', function($q) use ($doctorProfile) {
            $q->where('doctor_profile_id', $doctorProfile->id);
        })->findOrFail($appointment_id);

        DB::beginTransaction();
        try {
            // Cập nhật payment_status của các clinical_visits
            ClinicalVisit::where('appointment_id', $appointment->id)
                ->where('payment_status', 'pending')
                ->update([
                    'payment_status' => 'paid',
                    'paid_at' => now(),
                    'collected_by' => $user->id,
                    'payment_method' => $request->payment_method
                ]);

            // Tạo bản ghi Payment
            Payment::create([
                'appointment_id' => $appointment->id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'collected_by' => $user->id,
                'paid_at' => now(),
                'notes' => $request->notes,
                'transaction_id' => 'TXN' . strtoupper(uniqid())
            ]);

            DB::commit();
            return back()->with('success', 'Đã thanh toán thành công ' . number_format($request->amount) . ' VNĐ.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra khi thanh toán: ' . $e->getMessage());
        }
    }
}
