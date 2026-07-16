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

        $query = Appointment::with([
            'patientProfile',
            'doctorProfile.user',
            'specialty',
            'clinicalVisits',
            'payments'
        ]);

        // Lọc theo khoảng ngày (dựa trên ngày đặt lịch)
        if ($request->filled('date')) {
            $query->whereDate('appointment_date', $request->input('date'));
        }

        // Lọc theo Tab (Chờ thu tiền vs Lịch sử)
        if ($tab === 'pending') {
            // Lấy các Appointment có ClinicalVisits đang pending
            $query->whereHas('clinicalVisits', function ($q) {
                $q->where('payment_status', 'pending');
            });
        } else {
            // Lấy các Appointment có Payments
            $query->has('payments');

            // Filter theo phương thức thanh toán
            if ($request->filled('method')) {
                $method = $request->input('method');
                $query->whereHas('payments', function ($q) use ($method) {
                    $q->where('method', $method);
                });
            }
        }

        // Tìm kiếm
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('appointment_code', 'like', "%{$search}%")
                    ->orWhereHas('patientProfile', function ($q2) use ($search) {
                        $q2->where('full_name', 'like', "%{$search}%")
                            ->orWhere('patient_code', 'like', "%{$search}%");
                    });
            });
        }

        $appointments = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        // Thống kê nhanh hôm nay
        $today = Carbon::today();

        $totalCollectedToday = Payment::whereDate('paid_at', $today)
            ->where('status', 'completed')
            ->sum('amount');

        // Tổng tiền cần thu: Tổng tiền của tất cả các visits chưa thanh toán tạo hôm nay
        // Note: Để đơn giản, ta tính theo các visits tạo hôm nay
        $pendingAmountToday = ClinicalVisit::whereDate('created_at', $today)
            ->where('payment_status', 'pending')
            ->sum('payment_amount');
        // Wait, patient's insurance could reduce this, but for quick stats, this is an estimate.
        // Actually, let's keep it simple.

        $qrCollectedToday = Payment::whereDate('paid_at', $today)
            ->where('status', 'completed')
            ->where('method', 'qr')
            ->sum('amount');

        return view('receptionist.payments.index', compact(
            'appointments',
            'tab',
            'totalCollectedToday',
            'pendingAmountToday',
            'qrCollectedToday'
        ));
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

            // Lưu vào global cache cho Webhook - TTL 10 phút để có buffer
            \Illuminate\Support\Facades\Cache::put('qr_intent_' . $intentCode, $appointment->id, now()->addMinutes(10));
        } else {
            // Lấy lại mã intent đang dùng dở
            $intentCode = \Illuminate\Support\Facades\Cache::get($intentCacheKeySession);

            if (!$intentCode) {
                // Phòng hờ: intent session cache bị mất, sinh mã mới
                $intentCode = 'APT' . $appointment->id . strtoupper(\Illuminate\Support\Str::random(5));
                \Illuminate\Support\Facades\Cache::put($intentCacheKeySession, $intentCode, now()->addMinutes(60));
            }

            // CRITICAL FIX: Luôn refresh TTL của qr_intent_ mỗi khi trang được load
            // để đảm bảo cache không expire trước khi webhook kịp đến.
            \Illuminate\Support\Facades\Cache::put('qr_intent_' . $intentCode, $appointment->id, now()->addMinutes(10));
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
            return redirect()->route('receptionist.payments.index')
                ->with('success', 'Đã ghi nhận thanh toán hoàn tất (BHYT chi trả 100% / Miễn phí).');
        }

        $this->paymentService->createCashPayment($appointment, Auth::user());

        return redirect()->route('receptionist.payments.index')
            ->with('success', 'Đã ghi nhận thanh toán tiền mặt thành công.');
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
