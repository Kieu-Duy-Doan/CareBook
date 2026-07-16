<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\PaymentLog;
use App\Models\SePayTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReconciliationService
{
    /**
     * Chạy đối soát tự động toàn bộ SePayTransaction chưa khớp.
     * Match bằng cách tìm mã APT... trong transaction_content khớp với Payment.intent_code
     */
    public function reconcileAll(): array
    {
        $stats = ['matched' => 0, 'mismatch' => 0, 'skipped' => 0];

        $unmatchedTxns = SePayTransaction::where('reconciliation_status', 'unmatched')
            ->whereNotNull('transaction_content')
            ->get();

        foreach ($unmatchedTxns as $txn) {
            $result = $this->reconcileTransaction($txn);
            $stats[$result]++;
        }

        Log::info('Reconciliation complete', $stats);
        return $stats;
    }

    /**
     * Đối soát một giao dịch cụ thể
     */
    public function reconcileTransaction(SePayTransaction $txn): string
    {
        // Trích xuất mã APT từ nội dung chuyển khoản
        preg_match('/APT[-\s]*[A-Z0-9]+/i', strtoupper($txn->transaction_content ?? ''), $matches);
        $rawCode = $matches[0] ?? null;

        if (!$rawCode) {
            return 'skipped';
        }

        $normalizedCode = str_replace([' ', '-'], '', strtoupper($rawCode));

        // Tìm Payment khớp với intent_code
        $payment = Payment::where('intent_code', $normalizedCode)
            ->orWhere('intent_code', $rawCode)
            ->first();

        if (!$payment) {
            // Thử tìm theo appointment_code
            $payment = Payment::whereHas('appointment', function ($q) use ($normalizedCode) {
                $q->whereRaw("REPLACE(appointment_code, '-', '') = ?", [$normalizedCode]);
            })->first();
        }

        if (!$payment) {
            return 'skipped';
        }

        // So sánh số tiền
        $paymentAmount = (float) $payment->amount;
        $txnAmount = (float) $txn->amount_in;

        $status = abs($paymentAmount - $txnAmount) < 1 ? 'matched' : 'amount_mismatch';

        DB::transaction(function () use ($txn, $payment, $status) {
            $txn->update([
                'matched_payment_id' => $payment->id,
                'reconciliation_status' => $status,
                'is_synced' => $status === 'matched',
                'reconciliation_note' => $status === 'matched'
                    ? 'Tự động khớp với Payment #' . $payment->id
                    : 'Số tiền không khớp: GD=' . number_format($txn->amount_in) . 'đ, Payment=' . number_format($payment->amount) . 'đ',
            ]);

            PaymentLog::record(
                'reconciliation_' . $status,
                "Đối soát GD #{$txn->transaction_id} với Payment #{$payment->id}: " . ($status === 'matched' ? 'Khớp' : 'Sai số tiền'),
                $status === 'matched' ? 'success' : 'warning',
                ['payment_id' => $payment->id, 'appointment_id' => $payment->appointment_id]
            );
        });

        return $status === 'matched' ? 'matched' : 'mismatch';
    }

    /**
     * Match thủ công: admin chỉ định giao dịch SePay khớp với Payment nào
     */
    public function manualMatch(SePayTransaction $txn, Payment $payment, string $note = ''): void
    {
        DB::transaction(function () use ($txn, $payment, $note) {
            $txn->update([
                'matched_payment_id' => $payment->id,
                'reconciliation_status' => 'manual',
                'is_synced' => true,
                'reconciliation_note' => $note ?: 'Khớp thủ công bởi Admin #' . auth()->id(),
            ]);

            PaymentLog::record(
                'reconciliation_manual',
                "Admin khớp thủ công GD #{$txn->transaction_id} với Payment #{$payment->id}",
                'info',
                ['payment_id' => $payment->id, 'appointment_id' => $payment->appointment_id]
            );
        });
    }
}
