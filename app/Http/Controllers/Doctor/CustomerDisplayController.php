<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\Appointment;
use App\Services\PaymentService;
use App\Services\SePayService;

class CustomerDisplayController extends Controller
{
    protected PaymentService $paymentService;
    protected SePayService $sepayService;

    public function __construct(PaymentService $paymentService, SePayService $sepayService)
    {
        $this->paymentService = $paymentService;
        $this->sepayService = $sepayService;
    }

    /**
     * Hiển thị giao diện Màn hình phụ cho Bác sĩ
     */
    public function index()
    {
        return view('doctor.customer-display.index');
    }

    /**
     * API Polling để lấy trạng thái hiển thị hiện tại cho màn hình phụ
     */
    public function status(Request $request)
    {
        $doctorId = Auth::id();
        $activeAppointmentId = Cache::get('doctor_active_checkout_appointment_' . $doctorId);

        if (!$activeAppointmentId) {
            return response()->json([
                'status' => 'idle',
                'message' => 'Xin chào, vui lòng chờ bác sĩ xử lý thông tin...'
            ]);
        }

        $appointment = Appointment::with(['patientProfile', 'clinicalVisits'])->find($activeAppointmentId);

        if (!$appointment) {
            return response()->json(['status' => 'idle']);
        }

        $summary = $this->paymentService->calculateSummary($appointment);

        $intentCacheKeySession = 'doctor_active_checkout_intent_' . $doctorId;
        $intentCode = Cache::get($intentCacheKeySession);

        $qrUrl = null;
        if ($summary['remaining_to_pay'] > 0) {
            $qrUrl = $this->sepayService->generateVietQrUrl($appointment, $summary['remaining_to_pay'], $intentCode);
        }

        return response()->json([
            'status' => 'checkout',
            'appointment_code' => $appointment->appointment_code,
            'patient_name' => $appointment->patientProfile->full_name,
            'total_amount' => $summary['total_amount'],
            'insurance_covers' => $summary['insurance_covers'],
            'remaining_to_pay' => $summary['remaining_to_pay'],
            'overpaid_amount' => $summary['overpaid_amount'] ?? 0,
            'amount_paid' => $summary['amount_paid'] ?? 0,
            'is_paid' => $summary['remaining_to_pay'] <= 0,
            'qr_url' => $qrUrl,
            'checkout_start_time' => Cache::get('doctor_active_checkout_time_' . $doctorId, time())
        ]);
    }
}
