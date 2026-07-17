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
                'pending_visits' => collect(),
            ];
        }

        $calc = $this->healthInsuranceService->calculate($patient, $allVisits);

        $calc['pending_visits'] = $allVisits->filter(function ($visit) {
            return $visit->payment_status === 'pending';
        })->values();

        $payments = collect();
        foreach ($allVisits as $visit) {
            foreach ($visit->payments as $payment) {
                $payments->push($payment);
            }
        }
        $payments = $payments->unique('id')->where('status', 'completed');

        $amountPaid = $payments->sum('amount');

        $calc['amount_paid'] = $amountPaid;
        $calc['remaining_to_pay'] = max(0, $calc['patient_pays'] - $amountPaid);

        // --- Include Prescriptions ---
        $prescription = $appointment->medicalRecord?->prescription;
        if ($prescription) {
            $prescriptionAmount = $prescription->payment_amount ?? 0;
            $calc['total_amount'] += $prescriptionAmount;
            $calc['patient_pays'] += $prescriptionAmount; // Giả sử thuốc không áp dụng BHYT
            
            $calc['remaining_to_pay'] += max(0, $prescriptionAmount - $prescription->payments()->sum('payment_prescription.amount_allocated'));
            
            if ($prescription->payment_status === 'pending') {
                $calc['pending_visits']->push($prescription); // Gộp chung vào pending để dễ xử lý vòng lặp thu tiền
            }
        }

        $calc['overpaid_amount'] = max(0, $amountPaid - $calc['patient_pays']);
        $calc['all_visits'] = $allVisits;
        $calc['pending_visits'] = $calc['pending_visits']->values();

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
                if (isset($visit->items)) { // Phân biệt Prescription
                    $visitPatientPays = $visit->payment_amount;
                    $payment->prescriptions()->attach($visit->id, [
                        'amount_allocated' => $visitPatientPays
                    ]);
                } else {
                    $visitPatientPays = round($visit->payment_amount * (1 - $insuranceRate));
                    $payment->clinicalVisits()->attach($visit->id, [
                        'amount_allocated' => $visitPatientPays
                    ]);
                }

                $visit->update([
                    'payment_status' => 'paid',
                    'payment_method' => 'cash',
                    'collected_by' => $receptionist->id,
                    'paid_at' => now(),
                ]);
            }

            \App\Models\PaymentLog::record(
                'cash_payment_created',
                "Thu tiền mặt " . number_format($amountToPay) . "đ cho lịch hẹn {$appointment->appointment_code}",
                'success',
                ['appointment_id' => $appointment->id, 'payment_id' => $payment->id]
            );

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
                if (isset($visit->items)) { // Phân biệt Prescription
                    $payment->prescriptions()->attach($visit->id, [
                        'amount_allocated' => 0
                    ]);
                } else {
                    $payment->clinicalVisits()->attach($visit->id, [
                        'amount_allocated' => 0
                    ]);
                }

                $visit->update([
                    'payment_status' => 'paid',
                    'payment_method' => 'insurance',
                    'collected_by' => $receptionist->id,
                    'paid_at' => now(),
                ]);
            }

            \App\Models\PaymentLog::record(
                'insurance_payment_created',
                "Xác nhận BHYT 0đ cho lịch hẹn {$appointment->appointment_code}",
                'success',
                ['appointment_id' => $appointment->id, 'payment_id' => $payment->id]
            );

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

        \App\Models\PaymentLog::record(
            'sepay_webhook_received',
            "Nhận webhook từ SePay — Mã GD: " . ($payload['referenceCode'] ?? 'N/A') . " — " . number_format($payload['transferAmount'] ?? 0) . "đ",
            'info',
            ['payload' => $payload]
        );

        if (!$transactionCode) {
            return;
        }

        // Trích xuất Mã Appointment hoặc Intent Code từ nội dung chuyển khoản
        // VD: APT123A8F2 hoặc APT123
        preg_match('/APT[-\s]*[A-Z0-9]+/i', strtoupper($transferContent), $matches);
        $rawCode = $matches[0] ?? null;

        if (!$rawCode) {
            Log::warning('SePay Webhook: Không tìm thấy mã APT', ['content' => $transferContent]);
            return;
        }

        $normalizedCode = str_replace([' ', '-'], '', $rawCode); // VD: APT123A8F2

        // Lock appointment row để tránh race condition (Fix #6)
        DB::transaction(function () use ($normalizedCode, $rawCode, $transactionCode, $transferAmount, $transactionDate, $transferContent) {

            // 1. Kiểm tra mã intent trong Cache
            $appointmentIdFromCache = \Illuminate\Support\Facades\Cache::get('qr_intent_' . $normalizedCode);
            $isValidIntent = false;

            if ($appointmentIdFromCache) {
                $appointment = Appointment::where('id', $appointmentIdFromCache)->lockForUpdate()->first();
                $isValidIntent = true;
                // Xoá cache ngay để tránh lạm dụng QR này thêm lần nữa
                \Illuminate\Support\Facades\Cache::forget('qr_intent_' . $normalizedCode);
            } else {
                // 2. Không có cache, thử phân tích xem đây là IntentCode hay AppointmentCode cũ
                // Intent code mới có dạng: APT{id}{5 ký tự random}.
                if (preg_match('/^APT(\d+)[A-Z0-9]{5}$/', $normalizedCode, $parts)) {
                    $possibleId = $parts[1];
                    $appointment = Appointment::where('id', $possibleId)->lockForUpdate()->first();
                } else {
                    $appointment = null;
                }

                if (!$appointment) {
                    // Cố gắng tìm bằng appointment_code nguyên bản nếu người dùng gõ tay
                    $appointment = Appointment::whereRaw("REPLACE(appointment_code, '-', '') = ?", [$normalizedCode])
                        ->lockForUpdate()
                        ->first();
                }
            }

            if (!$appointment) {
                Log::warning('SePay Webhook: Không tìm thấy Appointment', ['code' => $normalizedCode]);
                return;
            }

            // Idempotency check — webhook bắn 2 lần chỉ xử lý 1 lần
            if (Payment::where('transaction_code', $transactionCode)->exists()) {
                return;
            }

            $summary = $this->calculateSummary($appointment);
            $requiredAmount = $summary['remaining_to_pay'];
            $pendingVisits = $summary['pending_visits']; // Use from summary to include prescriptions
            $insuranceRate = $summary['insurance_rate'];
            $patientName = $appointment->patientProfile->full_name ?? 'N/A';

            // --- XỬ LÝ QR HẾT HẠN HOẶC KHÔNG HỢP LỆ ---
            // !isValidIntent có nghĩa là cache đã hết hạn, nhưng ta vẫn tìm được appointment
            // qua pattern APT{id}{5chars}. Trong trường hợp đó, vẫn xử lý bình thường.
            // Chỉ cần log warning để biết là dùng fallback.
            if (!$isValidIntent) {
                Log::warning('SePay Webhook: Cache intent hết hạn, dùng pattern fallback', [
                    'code' => $normalizedCode,
                    'appointment_id' => $appointment->id,
                ]);
            }

            // --- XỬ LÝ QR HỢP LỆ ---
            if ($pendingVisits->isEmpty() || $requiredAmount <= 0) {
                Log::info('SePay Webhook: Không có khoản phí chờ thu', ['code' => $appointment->appointment_code]);
                Payment::create([
                    'appointment_id' => $appointment->id,
                    'transaction_code' => $transactionCode,
                    'intent_code' => $rawCode,
                    'amount' => $transferAmount,
                    'method' => 'qr',
                    'status' => 'needs_review',
                    'sepay_reference' => $transactionCode,
                    'paid_at' => $transactionDate,
                    'note' => 'Không có khoản phí chờ thu, nhưng vẫn nhận được tiền.',
                ]);
                $this->notifyReceptionists($appointment, $transferAmount, $requiredAmount, $patientName, true, "Đã nhận {$transferAmount}đ nhưng lịch hẹn không có khoản phí chờ thu.");
                return;
            }

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
                'intent_code' => $rawCode,
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

                // Số tiền bệnh nhân cần trả cho visit này
                $visitPatientPays = isset($visit->items) 
                    ? $visit->payment_amount 
                    : round($visit->payment_amount * (1 - $insuranceRate));
                
                $alreadyPaid = isset($visit->items)
                    ? $visit->payments()->sum('payment_prescription.amount_allocated')
                    : $visit->payments()->sum('payment_clinical_visit.amount_allocated');
                
                $visitRemaining = max(0, $visitPatientPays - $alreadyPaid);

                if ($visitRemaining <= 0) continue;

                $allocated = min($visitRemaining, $remainingAmount);
                $remainingAmount -= $allocated;

                if (isset($visit->items)) {
                    $payment->prescriptions()->attach($visit->id, [
                        'amount_allocated' => $allocated
                    ]);
                } else {
                    $payment->clinicalVisits()->attach($visit->id, [
                        'amount_allocated' => $allocated
                    ]);
                }

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
            $this->notifyReceptionists($appointment, $transferAmount, $requiredAmount, $patientName, false);
        });
    }

    /**
     * Gửi notification cho lễ tân khi có thanh toán QR
     */
    private function notifyReceptionists(Appointment $appointment, float $transferAmount, float $requiredAmount, string $patientName, bool $isExpired = false, string $customMessage = null): void
    {
        $receptionists = User::where('role', 'receptionist')->get();

        if ($receptionists->isEmpty()) return;

        if ($customMessage) {
            $message = $customMessage;
        } elseif ($isExpired) {
            $message = "CẢNH BÁO: Bệnh nhân {$patientName} chuyển " . number_format($transferAmount) . "đ vào mã QR ĐÃ HẾT HẠN hoặc sai cấu trúc. Vui lòng kiểm tra và xử lý thủ công.";
        } else {
            if ($transferAmount < $requiredAmount) {
                $shortfall = $requiredAmount - $transferAmount;
                $message = "Bệnh nhân {$patientName} thanh toán thiếu " . number_format($shortfall) . "đ. Còn " . number_format($shortfall) . "đ cần thu thêm.";
            } elseif ($transferAmount > $requiredAmount) {
                $surplus = $transferAmount - $requiredAmount;
                $message = "Bệnh nhân {$patientName} chuyển dư " . number_format($surplus) . "đ. Vui lòng hoàn trả.";
            } else {
                $message = "Bệnh nhân {$patientName} đã thanh toán đủ " . number_format($transferAmount) . "đ qua QR.";
            }
        }

        // Lưu notification vào DB cho từng lễ tân
        foreach ($receptionists as $receptionist) {
            \App\Models\Notification::create([
                'user_id' => $receptionist->id,
                'title' => $isExpired ? 'Cảnh báo QR Hết Hạn' : 'Thanh toán QR',
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
