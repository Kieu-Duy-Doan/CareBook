<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class PaymentService
{
    protected HealthInsuranceService $healthInsuranceService;

    public function __construct(HealthInsuranceService $healthInsuranceService)
    {
        $this->healthInsuranceService = $healthInsuranceService;
    }

    /**
     * Lấy danh sách các clinical_visits đang chờ thanh toán và tính BHYT
     */
    public function calculateSummary(Appointment $appointment): array
    {
        $allVisits = $appointment->clinicalVisits;
        $patient = $appointment->patientProfile;

        if ($allVisits->isEmpty()) {
            return [
                'total_amount' => 0,
                'insurance_rate' => 0,
                'insurance_covers' => 0,
                'patient_pays' => 0,
                'is_expired' => false,
                'warning_message' => null,
                'amount_paid' => 0,
                'remaining_to_pay' => 0,
                'overpaid_amount' => 0,
                'all_visits' => collect(),
            ];
        }

        $calc = $this->healthInsuranceService->calculate($patient, $allVisits);
        
        $payments = collect();
        foreach ($allVisits as $visit) {
            foreach ($visit->payments as $payment) {
                $payments->push($payment);
            }
        }
        $payments = $payments->unique('id');

        $amountPaid = $payments->sum('amount');

        $calc['amount_paid'] = $amountPaid;
        $calc['remaining_to_pay'] = max(0, $calc['patient_pays'] - $amountPaid);
        $calc['overpaid_amount'] = max(0, $amountPaid - $calc['patient_pays']);
        $calc['all_visits'] = $allVisits;

        return $calc;
    }

    /**
     * Xử lý thu tiền mặt tại quầy
     */
    public function createCashPayment(Appointment $appointment, User $receptionist): Payment
    {
        $summary = $this->calculateSummary($appointment);
        $amountToPay = $summary['remaining_to_pay'];
        $pendingVisits = $summary['pending_visits'];
        $insuranceRate = $summary['insurance_rate'];

        return DB::transaction(function () use ($appointment, $receptionist, $amountToPay, $pendingVisits, $insuranceRate) {
            $payment = Payment::create([
                'appointment_id' => $appointment->id,
                'transaction_code' => 'CASH-' . date('YmdHis') . '-' . strtoupper(Str::random(4)),
                'amount' => $amountToPay,
                'method' => 'cash',
                'status' => 'completed',
                'collected_by' => $receptionist->id,
                'paid_at' => now(),
                'note' => 'Thu tiền mặt tại quầy',
            ]);

            foreach ($pendingVisits as $visit) {
                // Phân bổ theo số tiền sau BHYT
                $visitPatientPays = round($visit->payment_amount * (1 - $insuranceRate));

                $payment->clinicalVisits()->attach($visit->id, [
                    'amount_allocated' => $visitPatientPays
                ]);

                $visit->update([
                    'payment_status' => 'paid',
                    'payment_method' => 'cash',
                    'collected_by' => $receptionist->id,
                    'paid_at' => now(),
                ]);
            }

            return $payment;
        });
    }

    /**
     * Xử lý xác nhận thanh toán 0 đồng (BHYT 100% hoặc Miễn phí)
     */
    public function createZeroFeePayment(Appointment $appointment, User $receptionist): Payment
    {
        $summary = $this->calculateSummary($appointment);
        $pendingVisits = $summary['pending_visits'];

        return DB::transaction(function () use ($appointment, $receptionist, $pendingVisits) {
            $payment = Payment::create([
                'appointment_id' => $appointment->id,
                'transaction_code' => 'BHYT-' . date('YmdHis') . '-' . strtoupper(Str::random(4)),
                'amount' => 0,
                'method' => 'insurance',
                'status' => 'completed',
                'collected_by' => $receptionist->id,
                'paid_at' => now(),
                'note' => 'BHYT chi trả 100% / Miễn phí',
            ]);

            foreach ($pendingVisits as $visit) {
                $payment->clinicalVisits()->attach($visit->id, [
                    'amount_allocated' => 0
                ]);

                $visit->update([
                    'payment_status' => 'paid',
                    'payment_method' => 'insurance',
                    'collected_by' => $receptionist->id,
                    'paid_at' => now(),
                ]);
            }

            return $payment;
        });
    }

    /**
     * Xử lý Webhook từ SePay (chuyển khoản VietQR)
     */
    public function processSePayWebhook(array $payload): void
    {
        $transactionCode = $payload['referenceCode'] ?? null;
        $transferAmount = $payload['transferAmount'] ?? 0;
        $transferContent = $payload['content'] ?? '';
        $transactionDate = $payload['transactionDate'] ?? now();

        if (!$transactionCode) {
            return;
        }

        // Trích xuất Mã Appointment từ nội dung chuyển khoản
        preg_match('/APT[-\s]*[A-Z0-9]+/i', strtoupper($transferContent), $matches);
        $rawCode = $matches[0] ?? null;

        if (!$rawCode) {
            Log::warning('SePay Webhook: Không tìm thấy mã APT', ['content' => $transferContent]);
            return;
        }

        $rawCode = str_replace(' ', '', $rawCode);
        $cleanCode = str_replace('-', '', $rawCode);

        // Lock appointment row để tránh race condition (Fix #6)
        DB::transaction(function () use ($rawCode, $cleanCode, $transactionCode, $transferAmount, $transactionDate) {
            // Thử match chính xác trước
            $appointment = Appointment::where('appointment_code', $rawCode)
                ->lockForUpdate()
                ->first();

            // Nếu không thấy, thử match bỏ qua dấu gạch ngang
            if (!$appointment) {
                $appointment = Appointment::whereRaw("REPLACE(appointment_code, '-', '') = ?", [$cleanCode])
                    ->lockForUpdate()
                    ->first();
            }

            if (!$appointment) {
                Log::warning('SePay Webhook: Không tìm thấy Appointment', ['code' => $rawCode]);
                return;
            }

            // Idempotency check — webhook bắn 2 lần chỉ xử lý 1 lần
            if (Payment::where('transaction_code', $transactionCode)->exists()) {
                return;
            }

            $summary = $this->calculateSummary($appointment);
            $requiredAmount = $summary['remaining_to_pay'];
            $pendingVisits = $appointment->clinicalVisits()->where('payment_status', 'pending')->get();
            $insuranceRate = $summary['insurance_rate'];

            if ($pendingVisits->isEmpty() || $requiredAmount <= 0) {
                Log::info('SePay Webhook: Không có khoản phí chờ thu', ['code' => $appointmentCode]);
                return;
            }

            $patientName = $appointment->patientProfile->full_name ?? 'N/A';
            $note = 'Thanh toán qua SePay VietQR';

            if ($transferAmount < $requiredAmount) {
                $shortfall = $requiredAmount - $transferAmount;
                $note = "Chuyển thiếu " . number_format($shortfall) . "đ so với tổng phí " . number_format($requiredAmount) . "đ.";
            } elseif ($transferAmount > $requiredAmount) {
                $surplus = $transferAmount - $requiredAmount;
                $note = "Chuyển dư " . number_format($surplus) . "đ so với tổng phí " . number_format($requiredAmount) . "đ. Cần hoàn trả cho bệnh nhân.";
            }

            $payment = Payment::create([
                'appointment_id' => $appointment->id,
                'transaction_code' => $transactionCode,
                'amount' => $transferAmount,
                'method' => 'qr',
                'status' => 'completed',
                'sepay_reference' => $transactionCode,
                'paid_at' => $transactionDate,
                'note' => $note,
            ]);

            // Phân bổ tiền cho các clinical visits (dùng giá sau BHYT)
            $remainingAmount = $transferAmount;

            // Sắp xếp theo số tiền nhỏ trước để ưu tiên thanh toán (spec Task 5)
            $sortedVisits = $pendingVisits->sortBy('payment_amount');

            foreach ($sortedVisits as $visit) {
                if ($remainingAmount <= 0) break;

                // Số tiền bệnh nhân cần trả cho visit này (đã trừ BHYT)
                $visitPatientPays = round($visit->payment_amount * (1 - $insuranceRate));
                $alreadyPaid = $visit->payments()->sum('payment_clinical_visit.amount_allocated');
                $visitRemaining = max(0, $visitPatientPays - $alreadyPaid);
                
                if ($visitRemaining <= 0) continue;

                $allocated = min($visitRemaining, $remainingAmount);
                $remainingAmount -= $allocated;

                $payment->clinicalVisits()->attach($visit->id, [
                    'amount_allocated' => $allocated
                ]);

                // Nếu phân bổ đủ tiền cho visit này
                if ($allocated >= $visitRemaining) {
                    $visit->update([
                        'payment_status' => 'paid',
                        'payment_method' => 'qr',
                        'paid_at' => $transactionDate,
                    ]);
                }
            }

            // Gửi notification cho lễ tân (Fix #7)
            $this->notifyReceptionists($appointment, $transferAmount, $requiredAmount, $patientName);
        });
    }

    /**
     * Gửi notification cho lễ tân khi có thanh toán QR
     */
    private function notifyReceptionists(Appointment $appointment, float $transferAmount, float $requiredAmount, string $patientName): void
    {
        $receptionists = User::where('role', 'receptionist')->get();

        if ($receptionists->isEmpty()) return;

        if ($transferAmount < $requiredAmount) {
            $shortfall = $requiredAmount - $transferAmount;
            $message = "Bệnh nhân {$patientName} thanh toán thiếu " . number_format($shortfall) . "đ. Còn " . number_format($shortfall) . "đ cần thu thêm.";
        } elseif ($transferAmount > $requiredAmount) {
            $surplus = $transferAmount - $requiredAmount;
            $message = "Bệnh nhân {$patientName} chuyển dư " . number_format($surplus) . "đ. Vui lòng hoàn trả.";
        } else {
            $message = "Bệnh nhân {$patientName} đã thanh toán đủ " . number_format($transferAmount) . "đ qua QR.";
        }

        // Lưu notification vào DB cho từng lễ tân
        foreach ($receptionists as $receptionist) {
            \App\Models\Notification::create([
                'user_id' => $receptionist->id,
                'title' => 'Thanh toán QR',
                'content' => $message,
                'type' => 'system',
                'channel' => 'in_web',
                'is_sent' => true,
                'is_read' => false,
                'ref_type' => 'appointment',
                'ref_id' => $appointment->id,
                'data' => [
                    'appointment_code' => $appointment->appointment_code,
                ],
                'created_at' => now()
            ]);
        }
    }
}
