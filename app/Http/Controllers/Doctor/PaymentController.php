<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Services\PaymentService;
use App\Services\SePayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        ->where('doctor_profile_id', $doctorProfileId)
        ->firstOrFail();

        $summary = $this->paymentService->calculateSummary($appointment);

        // Mượn logic cache intent của lễ tân nhưng dùng key riêng cho bác sĩ
        $doctorId = Auth::id();
        $timeCacheKey = 'doctor_active_checkout_time_' . $doctorId;
        $intentCacheKeySession = 'doctor_active_checkout_intent_' . $doctorId;
        $appointmentCacheKey = 'doctor_active_checkout_appointment_' . $doctorId;

        $startTime = \Illuminate\Support\Facades\Cache::get($timeCacheKey);

        // Nếu chuyển sang bệnh nhân khác hoặc có request renew = 1, thì reset lại timer và sinh mã mới
        $currentCachedAppointment = \Illuminate\Support\Facades\Cache::get($appointmentCacheKey);
        
        if (!$startTime || $request->has('renew') || $currentCachedAppointment != $id) {
            $startTime = time();
            \Illuminate\Support\Facades\Cache::put($timeCacheKey, $startTime, now()->addMinutes(60));

            // Sinh mã Intent Code mới (dùng một lần)
            $intentCode = 'APT' . $appointment->id . strtoupper(\Illuminate\Support\Str::random(5));
            \Illuminate\Support\Facades\Cache::put($intentCacheKeySession, $intentCode, now()->addMinutes(60));

            // Lưu vào global cache cho Webhook - TTL 10 phút
            \Illuminate\Support\Facades\Cache::put('qr_intent_' . $intentCode, $appointment->id, now()->addMinutes(10));
        } else {
            // Lấy lại mã intent đang dùng dở
            $intentCode = \Illuminate\Support\Facades\Cache::get($intentCacheKeySession);

            if (!$intentCode) {
                // Phòng hờ: intent session cache bị mất, sinh mã mới
                $intentCode = 'APT' . $appointment->id . strtoupper(\Illuminate\Support\Str::random(5));
                \Illuminate\Support\Facades\Cache::put($intentCacheKeySession, $intentCode, now()->addMinutes(60));
            }

            // Refresh TTL
            \Illuminate\Support\Facades\Cache::put('qr_intent_' . $intentCode, $appointment->id, now()->addMinutes(10));
        }

        $qrUrl = null;
        if ($summary['remaining_to_pay'] > 0) {
            $qrUrl = $this->sePayService->generateVietQrUrl($appointment, $summary['remaining_to_pay'], $intentCode);
        }

        // Kích hoạt hiển thị cho lịch hẹn này
        \Illuminate\Support\Facades\Cache::put($appointmentCacheKey, $id, now()->addMinutes(60));

        return view('doctor.payments.checkout', compact('appointment', 'summary', 'qrUrl', 'startTime'));
    }


}
