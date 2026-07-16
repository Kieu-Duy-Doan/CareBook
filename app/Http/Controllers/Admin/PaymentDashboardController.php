<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\ClinicalVisit;
use App\Models\Appointment;
use App\Models\PaymentLog;
use App\Models\RefundRequest;
use App\Models\SePayTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentDashboardController extends Controller
{
    public function index(Request $request)
    {
        // Khoảng thời gian (mặc định tháng này)
        $from = $request->filled('from')
            ? Carbon::parse($request->from)->startOfDay()
            : Carbon::now()->startOfMonth();

        $to = $request->filled('to')
            ? Carbon::parse($request->to)->endOfDay()
            : Carbon::now()->endOfDay();

        // ── Thống kê tổng quan ──────────────────────────────
        $totalRevenue = Payment::whereBetween('paid_at', [$from, $to])
            ->where('status', 'completed')
            ->where('amount', '>', 0)
            ->sum('amount');

        $totalRefunded = Payment::whereBetween('paid_at', [$from, $to])
            ->where('status', 'refunded')
            ->sum('amount');

        $totalPending = ClinicalVisit::where('payment_status', 'pending')->sum('payment_amount');

        $needsReviewCount = Payment::where('status', 'needs_review')->count();
        $pendingRefundCount = RefundRequest::where('status', 'pending')->count();

        // ── Doanh thu theo phương thức ──────────────────────
        $byMethod = Payment::whereBetween('paid_at', [$from, $to])
            ->where('status', 'completed')
            ->where('amount', '>', 0)
            ->selectRaw('method, SUM(amount) as total')
            ->groupBy('method')
            ->pluck('total', 'method');

        // ── Biểu đồ theo ngày (7 ngày gần nhất hoặc trong range) ──
        $chartDays = min((int)$from->diffInDays($to) + 1, 30);
        $chartFrom = $to->copy()->subDays($chartDays - 1)->startOfDay();

        $dailyRevenue = Payment::whereBetween('paid_at', [$chartFrom, $to])
            ->where('status', 'completed')
            ->where('amount', '>', 0)
            ->selectRaw('DATE(paid_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        // Fill days with 0 for missing dates
        $chartLabels = [];
        $chartData = [];
        for ($i = $chartDays - 1; $i >= 0; $i--) {
            $date = $to->copy()->subDays($i)->format('Y-m-d');
            $chartLabels[] = $to->copy()->subDays($i)->format('d/m');
            $chartData[] = (float)($dailyRevenue[$date] ?? 0);
        }

        // ── Giao dịch gần nhất chờ xử lý ──────────────────
        $needsReviewPayments = Payment::with(['appointment.patientProfile'])
            ->where('status', 'needs_review')
            ->latest('paid_at')
            ->take(5)
            ->get();

        $pendingRefunds = RefundRequest::with(['appointment.patientProfile', 'requestedBy'])
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        // ── Đối soát: % đã khớp ───────────────────────────
        $totalSepayTxns = SePayTransaction::count();
        $matchedTxns = SePayTransaction::where('reconciliation_status', 'matched')->count();
        $reconciliationRate = $totalSepayTxns > 0 ? round($matchedTxns / $totalSepayTxns * 100) : 0;

        return view('admin.payments.dashboard', compact(
            'from', 'to',
            'totalRevenue', 'totalRefunded', 'totalPending',
            'needsReviewCount', 'pendingRefundCount',
            'byMethod',
            'chartLabels', 'chartData',
            'needsReviewPayments', 'pendingRefunds',
            'reconciliationRate', 'totalSepayTxns', 'matchedTxns'
        ));
    }

    /**
     * Danh sách Payment cần xử lý (needs_review)
     */
    public function needsReview(Request $request)
    {
        $payments = Payment::with(['appointment.patientProfile', 'appointment.doctorProfile.user'])
            ->where('status', 'needs_review')
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->whereHas('appointment', function ($q2) use ($request) {
                    $q2->where('appointment_code', 'like', '%' . $request->search . '%')
                        ->orWhereHas('patientProfile', fn($q3) =>
                            $q3->where('full_name', 'like', '%' . $request->search . '%')
                        );
                });
            })
            ->latest('paid_at')
            ->paginate(20);

        return view('admin.payments.needs-review', compact('payments'));
    }

    /**
     * Xác nhận/Xử lý một payment needs_review
     */
    public function resolveReview(Request $request, Payment $payment)
    {
        $request->validate([
            'action' => 'required|in:approve,create_refund',
            'note' => 'nullable|string|max:500',
        ], [
            'action.required' => 'Vui lòng chọn hành động.',
            'action.in' => 'Hành động không hợp lệ.',
            'note.string' => 'Ghi chú phải là chuỗi ký tự.',
            'note.max' => 'Ghi chú không được vượt quá 500 ký tự.',
        ]);

        if ($request->action === 'approve') {
            $payment->update([
                'status' => 'completed',
                'note' => $payment->note . ' | Admin xác nhận: ' . ($request->note ?? ''),
            ]);

            PaymentLog::record(
                'payment_approved',
                "Admin xác nhận Payment #{$payment->id} (trước đó: needs_review). Ghi chú: " . ($request->note ?? 'Không có'),
                'success',
                ['payment_id' => $payment->id, 'appointment_id' => $payment->appointment_id]
            );

            return redirect()->route('admin.payments.needs-review')
                ->with('success', 'Đã xác nhận payment thành công.');
        }

        if ($request->action === 'create_refund') {
            RefundRequest::create([
                'appointment_id' => $payment->appointment_id,
                'payment_id' => $payment->id,
                'amount' => $payment->amount,
                'reason' => $request->note ?? 'Tạo từ trang Needs Review',
                'status' => 'pending',
                'requested_by' => Auth::id(),
            ]);

            $payment->update(['status' => 'completed']);

            PaymentLog::record(
                'refund_request_created',
                "Tạo yêu cầu hoàn tiền từ Payment #{$payment->id}",
                'info',
                ['payment_id' => $payment->id, 'appointment_id' => $payment->appointment_id]
            );

            return redirect()->route('admin.payments.refunds')
                ->with('success', 'Đã tạo yêu cầu hoàn tiền. Vui lòng duyệt tại trang Hoàn tiền.');
        }
    }

    /**
     * Danh sách yêu cầu hoàn tiền
     */
    public function refunds(Request $request)
    {
        $refunds = RefundRequest::with(['appointment.patientProfile', 'payment', 'requestedBy', 'reviewedBy'])
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20);

        return view('admin.payments.refunds', compact('refunds'));
    }

    /**
     * Duyệt/Từ chối yêu cầu hoàn tiền
     */
    public function reviewRefund(Request $request, RefundRequest $refund)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'refund_method' => 'nullable|in:cash,bank_transfer',
            'review_note' => 'nullable|string|max:500',
        ], [
            'action.required' => 'Vui lòng chọn hành động (Duyệt hoặc Từ chối).',
            'action.in' => 'Hành động không hợp lệ.',
            'refund_method.in' => 'Phương thức hoàn tiền không hợp lệ.',
            'review_note.string' => 'Ghi chú phải là chuỗi ký tự.',
            'review_note.max' => 'Ghi chú không được vượt quá 500 ký tự.',
        ]);

        $status = $request->action === 'approve' ? 'approved' : 'rejected';

        $refund->update([
            'status' => $status,
            'refund_method' => $request->refund_method,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'review_note' => $request->review_note,
        ]);

        if ($status === 'approved') {
            Payment::create([
                'appointment_id' => $refund->appointment_id,
                'transaction_code' => 'RFD-' . now()->format('YmdHis') . '-' . $refund->id,
                'amount' => -$refund->amount,
                'method' => 'cash',
                'status' => 'refunded',
                'collected_by' => Auth::id(),
                'paid_at' => now(),
                'note' => 'Hoàn tiền: ' . ($refund->review_note ?? ''),
            ]);
        }

        PaymentLog::record(
            'refund_' . $status,
            "Admin " . ($status === 'approved' ? 'duyệt' : 'từ chối') . " hoàn tiền #" . $refund->id . " — " . number_format($refund->amount) . "đ",
            $status === 'approved' ? 'success' : 'warning',
            ['appointment_id' => $refund->appointment_id]
        );

        return redirect()->route('admin.payments.refunds')
            ->with('success', $status === 'approved' ? 'Đã duyệt hoàn tiền.' : 'Đã từ chối hoàn tiền.');
    }

    /**
     * Export danh sách thanh toán ra CSV
     */
    public function exportCsv(Request $request)
    {
        $from = $request->filled('from') ? Carbon::parse($request->from)->startOfDay() : Carbon::now()->startOfMonth();
        $to = $request->filled('to') ? Carbon::parse($request->to)->endOfDay() : Carbon::now()->endOfDay();

        $payments = Payment::with(['appointment.patientProfile', 'collectedBy'])
            ->whereBetween('paid_at', [$from, $to])
            ->where('status', '!=', 'pending')
            ->orderBy('paid_at')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="payments_' . $from->format('Ymd') . '_' . $to->format('Ymd') . '.csv"',
        ];

        $callback = function () use ($payments) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM for Excel

            fputcsv($file, ['Mã GD', 'Ngày TT', 'Mã Lịch hẹn', 'Bệnh nhân', 'Số tiền', 'PT Thanh toán', 'Trạng thái', 'Nhân viên thu', 'Ghi chú']);

            foreach ($payments as $p) {
                fputcsv($file, [
                    $p->transaction_code,
                    $p->paid_at?->format('d/m/Y H:i'),
                    $p->appointment?->appointment_code,
                    $p->appointment?->patientProfile?->full_name,
                    number_format($p->amount),
                    $p->method,
                    $p->status,
                    $p->collectedBy?->name,
                    $p->note,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
    /**
     * In báo cáo danh sách thanh toán (PDF/Print)
     */
    public function printReport(Request $request)
    {
        $from = $request->filled('from') ? Carbon::parse($request->from)->startOfDay() : Carbon::now()->startOfMonth();
        $to = $request->filled('to') ? Carbon::parse($request->to)->endOfDay() : Carbon::now()->endOfDay();

        $payments = Payment::with(['appointment.patientProfile', 'collectedBy'])
            ->whereBetween('paid_at', [$from, $to])
            ->where('status', '!=', 'pending')
            ->orderBy('paid_at')
            ->get();

        $totalRevenue = $payments->where('status', 'completed')->where('amount', '>', 0)->sum('amount');
        $totalRefunded = abs($payments->where('status', 'refunded')->sum('amount'));

        return view('admin.payments.print-report', compact('payments', 'from', 'to', 'totalRevenue', 'totalRefunded'));
    }
}
