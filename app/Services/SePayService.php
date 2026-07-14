<?php

namespace App\Services;

use App\Models\Appointment;

class SePayService
{
    /**
     * Generate VietQR URL via SePay endpoint.
     * 
     * @param Appointment $appointment
     * @param float $amount
     * @return string
     */
    public function generateVietQrUrl(Appointment $appointment, float $amount, string $intentCode = null): string
    {
        $bankAcc = config('services.sepay.bank_acc');
        $bankName = config('services.sepay.bank_name');
        $template = config('services.sepay.template', 'compact');

        // Nội dung thanh toán: Sử dụng intentCode nếu có, ngược lại dùng appointment_code
        $description = 'Thanh toan ' . ($intentCode ?? $appointment->appointment_code);

        $baseUrl = 'https://qr.sepay.vn/img';

        $params = [
            'acc' => $bankAcc,
            'bank' => $bankName,
            'amount' => $amount,
            'des' => $description,
            'template' => $template,
        ];

        return $baseUrl . '?' . http_build_query($params);
    }
}
