<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentLog;
use App\Models\SePayTransaction;
use App\Services\ReconciliationService;
use App\Services\SePayService;
use Illuminate\Http\Request;

class SePayTransactionController extends Controller
{
    public function __construct(
        protected SePayService $sepayService,
        protected ReconciliationService $reconciliationService,
    ) {}

    public function index(Request $request)
    {
        $query = SePayTransaction::query();

        // Tìm kiếm
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                  ->orWhere('transaction_content', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%");
            });
        }

        // Lọc theo trạng thái đối soát
        if ($request->filled('reconciliation')) {
            $query->where('reconciliation_status', $request->reconciliation);
        }

        // Lọc theo khoảng ngày
        if ($request->filled('from')) {
            $query->whereDate('transaction_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('transaction_date', '<=', $request->to);
        }

        $transactions = $query->with('matchedPayment.appointment.patientProfile')
            ->orderBy('transaction_date', 'desc')
            ->paginate(20)
            ->withQueryString();

        $logs = PaymentLog::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20, ['*'], 'log_page');

        // Thống kê nhanh
        $stats = [
            'total_in'   => SePayTransaction::sum('amount_in'),
            'total_out'  => SePayTransaction::sum('amount_out'),
            'matched'    => SePayTransaction::where('reconciliation_status', 'matched')->count(),
            'unmatched'  => SePayTransaction::where('reconciliation_status', 'unmatched')->count(),
            'mismatch'   => SePayTransaction::where('reconciliation_status', 'amount_mismatch')->count(),
        ];

        return view('admin.sepay-transactions.index', compact('transactions', 'logs', 'stats'));
    }

    public function sync(Request $request)
    {
        try {
            $syncedCount = $this->sepayService->syncTransactions();

            // Chạy đối soát tự động sau khi sync
            $reconStats = $this->reconciliationService->reconcileAll();

            PaymentLog::record(
                'sync_sepay_success',
                "Đồng bộ {$syncedCount} giao dịch mới. Đối soát: {$reconStats['matched']} khớp, {$reconStats['mismatch']} sai tiền.",
                'success'
            );

            return redirect()->back()->with(
                'success',
                "Đã đồng bộ {$syncedCount} giao dịch mới. Đối soát tự động: {$reconStats['matched']} khớp, {$reconStats['mismatch']} sai số tiền."
            );
        } catch (\Exception $e) {
            PaymentLog::record('sync_sepay_error', $e->getMessage(), 'error');
            return redirect()->back()->with('error', 'Đồng bộ thất bại. Vui lòng kiểm tra lại cấu hình API Token.');
        }
    }

    /**
     * Chạy đối soát lại (không sync mới)
     */
    public function reconcile(Request $request)
    {
        $stats = $this->reconciliationService->reconcileAll();

        PaymentLog::record(
            'reconciliation_run',
            "Chạy đối soát thủ công: {$stats['matched']} khớp, {$stats['mismatch']} sai tiền, {$stats['skipped']} bỏ qua.",
            'info'
        );

        return redirect()->back()->with(
            'success',
            "Đối soát xong: {$stats['matched']} khớp, {$stats['mismatch']} sai số tiền, {$stats['skipped']} bỏ qua."
        );
    }

    /**
     * Khớp thủ công một giao dịch với Payment
     */
    public function manualMatch(Request $request, SePayTransaction $transaction)
    {
        $request->validate([
            'payment_id' => 'required|exists:payments,id',
            'note' => 'nullable|string|max:500',
        ], [
            'payment_id.required' => 'Vui lòng nhập hoặc chọn ID thanh toán.',
            'payment_id.exists' => 'Thanh toán này không tồn tại trong hệ thống.',
            'note.string' => 'Ghi chú phải là chuỗi ký tự.',
            'note.max' => 'Ghi chú không được vượt quá 500 ký tự.',
        ]);

        $payment = Payment::findOrFail($request->payment_id);
        $this->reconciliationService->manualMatch($transaction, $payment, $request->note ?? '');

        return redirect()->back()->with('success', "Đã khớp giao dịch #{$transaction->transaction_id} với Payment #{$payment->id}.");
    }
}
