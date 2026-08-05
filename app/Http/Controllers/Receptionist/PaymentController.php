<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Payment;
use App\Models\ClinicalVisit;
use App\Services\SePayService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PaymentController extends Controller
{
    protected SePayService $sepayService;
    protected PaymentService $paymentService;

    public function __construct(SePayService $sepayService, PaymentService $paymentService)
    {
        $this->sepayService = $sepayService;
        $this->paymentService = $paymentService;
    }

    /**
     * Tab 1 & 2: Danh sách Hóa đơn & Lịch sử thanh toán
     */
    public function index(Request $request)
    {
        // Xóa hiển thị màn hình phụ khi lễ tân quay về danh sách
        \Illuminate\Support\Facades\Cache::forget('receptionist_active_checkout_' . \Illuminate\Support\Facades\Auth::id());

        $tab = $request->input('tab', 'pending'); // 'pending' or 'history'

        // Define date range
        $from = $request->filled('from') ? Carbon::parse($request->from)->startOfDay() : Carbon::today()->startOfDay();
        $to = $request->filled('to') ? Carbon::parse($request->to)->endOfDay() : Carbon::today()->endOfDay();
        if ($request->filled('month')) {
            $monthDate = Carbon::parse($request->month . '-01');
            $from = $monthDate->copy()->startOfMonth();
            $to = $monthDate->copy()->endOfMonth();
        }

        $appointments = collect();
        $payments = collect();

        if ($tab === 'pending') {
            $query = Appointment::with([
                'patientProfile',
                'doctorProfile.user',
                'specialty',
                'clinicalVisits',
                'payments'
            ]);

            $query->whereHas('clinicalVisits', function ($q) {
                $q->where('payment_status', 'pending');
            });

            // Filter date for pending appts based on created_at or appointment_date
            if ($request->filled('from')) {
                $query->whereDate('appointment_date', '>=', $from);
            }
            if ($request->filled('to')) {
                $query->whereDate('appointment_date', '<=', $to);
            }

            if ($request->filled('search')) {
                $search = \App\Services\AppointmentService::escapeLikeWildcards($request->input('search'));
                $query->where(function ($q) use ($search) {
                    $q->where('appointment_code', 'like', "%{$search}%")
                        ->orWhereHas('patientProfile', function ($q2) use ($search) {
                            $q2->where('full_name', 'like', "%{$search}%")
                                ->orWhere('patient_code', 'like', "%{$search}%");
                        });
                });
            }

            $appointments = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        } else {
            // Lịch sử giao dịch (History)
            $query = Payment::with([
                'appointment.patientProfile',
                'appointment.specialty',
                'collectedBy',
                'clinicalVisits',
                'prescriptions'
            ]);

            $query->whereBetween('paid_at', [$from, $to]);

            if ($request->filled('method')) {
                $query->where('method', $request->input('method'));
            }
            if ($request->filled('collector_id')) {
                $query->where('collected_by', $request->input('collector_id'));
            }
            if ($request->filled('specialty_id')) {
                $query->whereHas('appointment', function($q) use ($request) {
                    $q->where('specialty_id', $request->input('specialty_id'));
                });
            }

            if ($request->filled('search')) {
                $search = \App\Services\AppointmentService::escapeLikeWildcards($request->input('search'));
                $query->where(function ($q) use ($search) {
                    $q->where('transaction_code', 'like', "%{$search}%")
                        ->orWhereHas('appointment', function ($q2) use ($search) {
                            $q2->where('appointment_code', 'like', "%{$search}%")
                                ->orWhereHas('patientProfile', function ($q3) use ($search) {
                                    $q3->where('full_name', 'like', "%{$search}%")
                                        ->orWhere('patient_code', 'like', "%{$search}%");
                                });
                        });
                });
            }

            $payments = $query->orderBy('paid_at', 'desc')->paginate(20)->withQueryString();

            // Enrich each payment for history view
            $insuranceCache = [];
            $payments->getCollection()->transform(function ($payment) use (&$insuranceCache) {
                return $this->enrichPayment($payment, $insuranceCache);
            });
        }

        // --- Calculate Statistics based on $from and $to ---
        $basePaymentQuery = Payment::whereBetween('paid_at', [$from, $to])->where('status', 'completed');

        if ($request->filled('method')) {
            $basePaymentQuery->where('method', $request->input('method'));
        }
        if ($request->filled('collector_id')) {
            $basePaymentQuery->where('collected_by', $request->input('collector_id'));
        }
        if ($request->filled('specialty_id')) {
            $basePaymentQuery->whereHas('appointment', function($q) use ($request) {
                $q->where('specialty_id', $request->input('specialty_id'));
            });
        }
        
        $totalTransactions = (clone $basePaymentQuery)->count();
        $totalCash = (clone $basePaymentQuery)->where('method', 'cash')->where('amount', '>', 0)->sum('amount');
        $totalSepay = (clone $basePaymentQuery)->where('method', 'qr')->where('amount', '>', 0)->sum('amount');
        
        $allCompletedPayments = (clone $basePaymentQuery)->with(['appointment.patientProfile', 'clinicalVisits', 'prescriptions'])->get();
        $insuranceCacheForSummary = [];
        $totalInsurance = 0;
        foreach ($allCompletedPayments as $p) {
            $enriched = $this->enrichPayment($p, $insuranceCacheForSummary);
            $totalInsurance += $enriched->insurance_amount;
        }

        // Doanh thu = Tiền mặt + Sepay + BHYT chi trả
        $totalRevenue = $totalCash + $totalSepay + $totalInsurance;
        
        // Dư nợ chờ thu
        $pendingBaseQuery = ClinicalVisit::whereBetween('created_at', [$from, $to])
            ->where('payment_status', 'pending');
        if ($request->filled('specialty_id')) {
            $pendingBaseQuery->whereHas('appointment', function($q) use ($request) {
                $q->where('specialty_id', $request->input('specialty_id'));
            });
        }
        $totalPending = $pendingBaseQuery->sum('payment_amount');

        // Phục vụ filter dropdowns
        $collectors = \App\Models\User::whereIn('role', ['admin', 'receptionist', 'doctor'])->get();
        $specialties = \App\Models\Specialty::all();

        return view('receptionist.payments.index', compact(
            'appointments',
            'payments',
            'tab',
            'from',
            'to',
            'totalTransactions',
            'totalRevenue',
            'totalCash',
            'totalSepay',
            'totalInsurance',
            'totalPending',
            'collectors',
            'specialties'
        ));
    }

    /**
     * Tính toán thông tin BHYT/bệnh nhân chi trả cho một payment.
     */
    private function enrichPayment(Payment $payment, array &$insuranceCache): Payment
    {
        $totalFee = 0;
        if ($payment->relationLoaded('clinicalVisits')) {
            $totalFee += $payment->clinicalVisits->sum('payment_amount');
        }
        if ($payment->relationLoaded('prescriptions')) {
            $totalFee += $payment->prescriptions->sum('payment_amount');
        }

        if ($totalFee == 0) {
            $totalFee = (float) ($payment->amount ?? 0);
        }

        if ($payment->method === 'insurance' || $payment->method === 'waived') {
            // Trường hợp bảo hiểm chi trả 100% hoặc miễn phí hoàn toàn
            $insuranceAmount = $totalFee;
            $patientAmount   = 0;
            $patientPercent  = 0;
            $insurancePercent= 100;
        } else {
            // Trường hợp có thanh toán Tiền mặt hoặc QR
            $patientAmount   = (float) $payment->amount;
            // BHYT chi trả = Số tiền gốc - Số tiền BN chi trả
            $insuranceAmount = max(0, $totalFee - $patientAmount);
            
            if ($totalFee > 0) {
                // Tính phần trăm tương ứng
                $patientPercent = round(($patientAmount / $totalFee) * 100);
                $insurancePercent = 100 - $patientPercent;
            } else {
                $patientPercent = 100;
                $insurancePercent = 0;
            }
        }

        $payment->total_fee         = $totalFee;
        $payment->insurance_percent = $insurancePercent;
        $payment->insurance_amount  = $insuranceAmount;
        $payment->patient_percent   = $patientPercent;
        $payment->patient_amount    = $patientAmount;

        return $payment;
    }

    /**
     * Lịch sử thanh toán chi tiết (show)
     */
    public function show(string $id)
    {
        // Xóa hiển thị màn hình phụ khi lễ tân quay về xem chi tiết
        \Illuminate\Support\Facades\Cache::forget('receptionist_active_checkout_' . \Illuminate\Support\Facades\Auth::id());

        $appointment = Appointment::with([
            'patientProfile',
            'payments.collectedBy',
            'clinicalVisits'
        ])->findOrFail($id);

        $summary = $this->paymentService->calculateSummary($appointment);

        return view('receptionist.payments.show', compact('appointment', 'summary'));
    }

    /**
     * Màn hình chuẩn bị thanh toán (Popup quét mã QR hoặc Thanh toán tiền mặt)
     */
    public function create(Request $request, string $id)
    {
        $appointment = Appointment::with([
            'patientProfile',
            'doctorProfile.user',
            'clinicalVisits'
        ])->findOrFail($id);

        $summary = $this->paymentService->calculateSummary($appointment);

        $receptionistId = \Illuminate\Support\Facades\Auth::id();
        $timeCacheKey = 'receptionist_active_checkout_time_' . $receptionistId;
        $appointmentCacheKey = 'receptionist_active_checkout_' . $receptionistId;
        $intentCacheKeySession = 'receptionist_active_checkout_intent_' . $receptionistId;

        $startTime = \Illuminate\Support\Facades\Cache::get($timeCacheKey);

        // Nếu chuyển sang bệnh nhân khác hoặc có request renew = 1, thì reset lại timer và sinh mã mới
        $currentCachedAppointment = \Illuminate\Support\Facades\Cache::get($appointmentCacheKey);
        
        if (!$startTime || $request->has('renew') || $currentCachedAppointment != $id) {
            $startTime = time();
            \Illuminate\Support\Facades\Cache::put($timeCacheKey, $startTime, now()->addMinutes(60));

            // Sinh mã Intent Code mới (dùng một lần) - không dùng dấu gạch ngang vì ngân hàng có thể cắt mất
            $intentCode = 'APT' . $appointment->id . strtoupper(\Illuminate\Support\Str::random(5));
            \Illuminate\Support\Facades\Cache::put($intentCacheKeySession, $intentCode, now()->addMinutes(60));

            // Lưu vào global cache cho Webhook - TTL 10 phút
            \Illuminate\Support\Facades\Cache::put('qr_intent_' . $intentCode, $appointment->id, now()->addMinutes(10));
            \Illuminate\Support\Facades\Cache::put('qr_intent_' . $intentCode . '_user', Auth::id(), now()->addMinutes(10));
        } else {
            // Lấy lại mã intent đang dùng dở
            $intentCode = \Illuminate\Support\Facades\Cache::get($intentCacheKeySession);

            if (!$intentCode) {
                // Phòng hờ: intent session cache bị mất, sinh mã mới
                $intentCode = 'APT' . $appointment->id . strtoupper(\Illuminate\Support\Str::random(5));
                \Illuminate\Support\Facades\Cache::put($intentCacheKeySession, $intentCode, now()->addMinutes(60));
            }

            // CRITICAL FIX: Luôn refresh TTL của qr_intent_ mỗi khi trang được load
            // để webhook không bị miss nếu bệnh nhân chuyển khoản trễ vài phút.
            \Illuminate\Support\Facades\Cache::put('qr_intent_' . $intentCode, $appointment->id, now()->addMinutes(10));
            \Illuminate\Support\Facades\Cache::put('qr_intent_' . $intentCode . '_user', Auth::id(), now()->addMinutes(10));
        }

        $qrUrl = null;
        if ($summary['remaining_to_pay'] > 0) {
            $qrUrl = $this->sepayService->generateVietQrUrl($appointment, $summary['remaining_to_pay'], $intentCode);
        }

        // Kích hoạt hiển thị lên Màn hình phụ (Customer Display) cho lễ tân hiện tại
        \Illuminate\Support\Facades\Cache::put($appointmentCacheKey, $id, now()->addMinutes(60));

        return view('receptionist.payments.checkout', compact('appointment', 'summary', 'qrUrl', 'startTime'));
    }

    /**
     * Xử lý thanh toán thủ công (Tiền mặt)
     */
    public function storeManual(Request $request, string $id)
    {
        $appointment = Appointment::findOrFail($id);

        $summary = $this->paymentService->calculateSummary($appointment);

        if ($summary['patient_pays'] <= 0) {
            $this->paymentService->createZeroFeePayment($appointment, Auth::user());
            return redirect()->route('receptionist.appointments.show', $appointment->id)
                ->with('success', 'Đã ghi nhận thanh toán hoàn tất (BHYT chi trả 100% / Miễn phí).')
                ->with('active_tab', 'payments');
        }

        $this->paymentService->createCashPayment($appointment, Auth::user());

        return redirect()->route('receptionist.appointments.show', $appointment->id)
            ->with('success', 'Đã ghi nhận thanh toán tiền mặt thành công.')
            ->with('active_tab', 'payments');
    }

    /**
     * Tạo yêu cầu hoàn tiền thừa cho bệnh nhân
     */
    public function confirmRefund(Request $request, string $id)
    {
        $appointment = Appointment::findOrFail($id);
        $summary = $this->paymentService->calculateSummary($appointment);

        if ($summary['overpaid_amount'] > 0) {
            $lastPayment = Payment::where('appointment_id', $appointment->id)
                ->where('status', 'completed')
                ->latest('paid_at')
                ->first();

            if (!$lastPayment) {
                return redirect()->back()->with('error', 'Không tìm thấy giao dịch gốc để hoàn tiền.');
            }

            \App\Models\RefundRequest::create([
                'appointment_id' => $appointment->id,
                'payment_id' => $lastPayment->id,
                'amount' => $summary['overpaid_amount'],
                'reason' => 'Hoàn tiền thừa ' . number_format($summary['overpaid_amount'], 0, ',', '.') . 'đ cho bệnh nhân',
                'status' => 'pending',
                'refund_method' => 'cash',
                'requested_by' => Auth::id(),
            ]);

            \App\Models\PaymentLog::record(
                'refund_requested',
                "Lễ tân yêu cầu hoàn tiền thừa " . number_format($summary['overpaid_amount']) . "đ cho bệnh nhân",
                'info',
                ['appointment_id' => $appointment->id]
            );

            \App\Models\AppointmentLog::create([
                'appointment_id' => $appointment->id,
                'action'         => 'REFUND_REQUESTED',
                'old_status'     => null,
                'new_status'     => $appointment->status,
                'changed_by'     => Auth::id(),
                'reason'         => "Yêu cầu hoàn tiền thừa " . number_format($summary['overpaid_amount']) . "đ cho bệnh nhân."
            ]);

            return redirect()->back()->with('success', 'Đã tạo yêu cầu hoàn tiền thừa thành công. Vui lòng báo quản lý duyệt.');
        }

        return redirect()->back()->with('error', 'Không có khoản tiền thừa nào cần hoàn trả.');
    }

    /**
     * In Hóa đơn VAT
     */
    public function printVat(string $id)
    {
        $appointment = Appointment::with([
            'patientProfile',
            'payments.collectedBy',
            'clinicalVisits'
        ])->findOrFail($id);

        $summary = $this->paymentService->calculateSummary($appointment);

        return view('receptionist.payments.invoice-vat', compact('appointment', 'summary'));
    }

    /**
     * In Phiếu Tạm Ứng
     */
    public function printDeposit(string $id)
    {
        $appointment = Appointment::with([
            'patientProfile',
            'payments.collectedBy'
        ])->findOrFail($id);

        $summary = $this->paymentService->calculateSummary($appointment);

        return view('receptionist.payments.invoice-deposit', compact('appointment', 'summary'));
    }
}
