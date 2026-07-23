<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Payment;
use App\Services\PaymentService;
use App\Services\SePayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;
    protected SePayService $sePayService;

    public function __construct(PaymentService $paymentService, SePayService $sePayService)
    {
        $this->paymentService = $paymentService;
        $this->sePayService = $sePayService;
    }

    /**
     * Danh sách hóa đơn cần thu & lịch sử thanh toán của bác sĩ
     */
    public function index(Request $request)
    {
        $doctorProfileId = Auth::user()->doctorProfile->id ?? null;

        // Xóa hiển thị màn hình phụ khi bác sĩ quay về danh sách
        Cache::forget('doctor_active_checkout_appointment_' . Auth::id());

        $tab = $request->input('tab', 'pending');

        $query = Appointment::with([
            'patientProfile',
            'clinicalVisits',
            'payments',
            'medicalRecord.prescription',
        ])->where(function ($query) use ($doctorProfileId) {
            $query->where('doctor_profile_id', $doctorProfileId)
                  ->orWhereHas('clinicalVisits', function ($q) use ($doctorProfileId) {
                      $q->where('doctor_profile_id', $doctorProfileId);
                  });
        });

        if ($tab === 'pending') {
            $query->whereHas('clinicalVisits', function ($q) use ($doctorProfileId) {
                $q->where('doctor_profile_id', $doctorProfileId)
                  ->where('payment_status', 'pending');
            });
        } else {
            $query->has('payments');
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('appointment_code', 'like', "%{$search}%")
                  ->orWhereHas('patientProfile', fn($q2) =>
                      $q2->where('full_name', 'like', "%{$search}%")
                         ->orWhere('patient_code', 'like', "%{$search}%")
                  );
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('appointment_date', $request->input('date'));
        }

        $appointments = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        $today = Carbon::today();
        $totalCollectedToday = Payment::whereDate('paid_at', $today)->where('status', 'completed')->sum('amount');
        $qrCollectedToday = Payment::whereDate('paid_at', $today)->where('status', 'completed')->where('method', 'qr')->sum('amount');

        return view('doctor.payments.index', compact('appointments', 'tab', 'totalCollectedToday', 'qrCollectedToday'));
    }

    /**
     * Chi tiết thanh toán & In ấn
     */
    public function show(string $id)
    {
        $doctorProfileId = Auth::user()->doctorProfile->id ?? null;

        // Xóa hiển thị màn hình phụ
        Cache::forget('doctor_active_checkout_appointment_' . Auth::id());

        $appointment = Appointment::with([
            'patientProfile',
            'clinicalVisits.room',
            'clinicalVisits.doctorProfile.user',
            'payments.collectedBy',
            'medicalRecord.prescription',
        ])->where(function ($query) use ($doctorProfileId) {
            $query->where('doctor_profile_id', $doctorProfileId)
                  ->orWhereHas('clinicalVisits', function ($q) use ($doctorProfileId) {
                      $q->where('doctor_profile_id', $doctorProfileId);
                  });
        })->findOrFail($id);

        $summary = $this->paymentService->calculateSummary($appointment);

        return view('doctor.payments.show', compact('appointment', 'summary'));
    }

    /**
     * Hiển thị QR Code thanh toán tại phòng bác sĩ
     */
    public function checkout(Request $request, string $id)
    {
        $doctorProfileId = Auth::user()->doctorProfile->id ?? null;

        $appointment = Appointment::with([
            'patientProfile',
            'medicalRecord.prescription'
        ])
        ->where('id', $id)
        ->where(function ($query) use ($doctorProfileId) {
            $query->where('doctor_profile_id', $doctorProfileId)
                  ->orWhereHas('clinicalVisits', function ($q) use ($doctorProfileId) {
                      $q->where('doctor_profile_id', $doctorProfileId);
                  });
        })
        ->firstOrFail();

        $summary = $this->paymentService->calculateSummary($appointment);

        // Mượn logic cache intent của lễ tân nhưng dùng key riêng cho bác sĩ
        $doctorId = Auth::id();
        $timeCacheKey = 'doctor_active_checkout_time_' . $doctorId;
        $intentCacheKeySession = 'doctor_active_checkout_intent_' . $doctorId;
        $appointmentCacheKey = 'doctor_active_checkout_appointment_' . $doctorId;

        $startTime = Cache::get($timeCacheKey);

        // Nếu chuyển sang bệnh nhân khác hoặc có request renew = 1, thì reset lại timer và sinh mã mới
        $currentCachedAppointment = Cache::get($appointmentCacheKey);

        if (!$startTime || $request->has('renew') || $currentCachedAppointment != $id) {
            $startTime = time();
            Cache::put($timeCacheKey, $startTime, now()->addMinutes(60));

            // Sinh mã Intent Code mới (dùng một lần)
            $intentCode = 'APT' . $appointment->id . strtoupper(\Illuminate\Support\Str::random(5));
            Cache::put($intentCacheKeySession, $intentCode, now()->addMinutes(60));

            // Lưu vào global cache cho Webhook - TTL 10 phút
            Cache::put('qr_intent_' . $intentCode, $appointment->id, now()->addMinutes(10));
        } else {
            // Lấy lại mã intent đang dùng dở
            $intentCode = Cache::get($intentCacheKeySession);

            if (!$intentCode) {
                // Phòng hờ: intent session cache bị mất, sinh mã mới
                $intentCode = 'APT' . $appointment->id . strtoupper(\Illuminate\Support\Str::random(5));
                Cache::put($intentCacheKeySession, $intentCode, now()->addMinutes(60));
            }

            // Refresh TTL
            Cache::put('qr_intent_' . $intentCode, $appointment->id, now()->addMinutes(10));
        }

        $qrUrl = null;
        if ($summary['remaining_to_pay'] > 0) {
            $qrUrl = $this->sePayService->generateVietQrUrl($appointment, $summary['remaining_to_pay'], $intentCode);
        }

        // Kích hoạt hiển thị cho lịch hẹn này
        Cache::put($appointmentCacheKey, $id, now()->addMinutes(60));

        return view('doctor.payments.checkout', compact('appointment', 'summary', 'qrUrl', 'startTime'));
    }

    /**
     * In Phiếu Chỉ Định Cận lâm sàng (Nhiệt 80mm)
     * Bệnh nhân cầm đi khám các phòng khác
     */
    public function printReferralSlip(string $id)
    {
        $doctorProfileId = Auth::user()->doctorProfile->id ?? null;

        $appointment = Appointment::with([
            'patientProfile',
            'clinicalVisits' => fn($q) => $q->orderBy('visit_order'),
            'clinicalVisits.room',
            'clinicalVisits.doctorProfile.user',
            'medicalRecord',
        ])->where(function ($query) use ($doctorProfileId) {
            $query->where('doctor_profile_id', $doctorProfileId)
                  ->orWhereHas('clinicalVisits', function ($q) use ($doctorProfileId) {
                      $q->where('doctor_profile_id', $doctorProfileId);
                  });
        })->findOrFail($id);

        $summary = $this->paymentService->calculateSummary($appointment);

        $subVisits = $appointment->clinicalVisits->where('is_origin', false)->values();
        $originVisit = $appointment->clinicalVisits->firstWhere('is_origin', true);

        $allPaid = $subVisits->every(fn($v) => $v->payment_status === 'paid');

        return view('doctor.payments.referral-slip', compact('appointment', 'summary', 'subVisits', 'originVisit', 'allPaid'));
    }

    /**
     * In Đơn Thuốc / Kết quả (A4)
     * Bệnh nhân xuống quầy thuốc lấy thuốc
     */
    public function printPrescription(string $id)
    {
        $doctorProfileId = Auth::user()->doctorProfile->id ?? null;

        $appointment = Appointment::with([
            'patientProfile',
            'clinicalVisits.room',
            'medicalRecord.prescription',
            'payments',
        ])->where(function ($query) use ($doctorProfileId) {
            $query->where('doctor_profile_id', $doctorProfileId)
                  ->orWhereHas('clinicalVisits', function ($q) use ($doctorProfileId) {
                      $q->where('doctor_profile_id', $doctorProfileId);
                  });
        })->findOrFail($id);

        $summary = $this->paymentService->calculateSummary($appointment);
        $prescription = $appointment->medicalRecord?->prescription;

        return view('doctor.payments.prescription-print', compact('appointment', 'summary', 'prescription'));
    }
}

