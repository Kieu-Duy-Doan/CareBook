<?php

namespace App\Http\Controllers\Receptionist;

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
     * Hiển thị giao diện Màn hình phụ
     */
    public function index()
    {
        return view('receptionist.customer-display.index');
    }

    /**
     * API Polling để lấy trạng thái hiển thị hiện tại cho màn hình phụ
     */
    public function status(Request $request)
    {
        $receptionistId = Auth::id();
        $activeAppointmentId = Cache::get('receptionist_active_checkout_' . $receptionistId);

        if (!$activeAppointmentId) {
            return response()->json([
                'status' => 'idle',
                'message' => 'Xin chào quý khách, vui lòng chờ trong giây lát...'
            ]);
        }

        $appointment = Appointment::with(['patientProfile', 'clinicalVisits'])->find($activeAppointmentId);

        if (!$appointment) {
            return response()->json(['status' => 'idle']);
        }

        $summary = $this->paymentService->calculateSummary($appointment);

        $intentCacheKeySession = 'receptionist_active_checkout_intent_' . $receptionistId;
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
            'checkout_start_time' => Cache::get('receptionist_active_checkout_time_' . $receptionistId, time())
        ]);
    }
}
