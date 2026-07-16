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

    /**
     * Get transactions from SePay API
     */
    public function getTransactions(int $limit = 100, int $page = 1, array $extraParams = []): array
    {
        $token = config('services.sepay.api_token');
        if (!$token) {
            \Illuminate\Support\Facades\Log::error('SePay API Token is missing.');
            return ['error' => 'Thiếu cấu hình SEPAY_API_TOKEN'];
        }

        $params = array_merge([
            'limit' => $limit,
            'page' => $page,
        ], $extraParams);

        $response = \Illuminate\Support\Facades\Http::withToken($token)
            ->get('https://my.sepay.vn/userapi/transactions/list', $params);

        if ($response->successful()) {
            return $response->json();
        }

        \Illuminate\Support\Facades\Log::error('SePay API Error', ['response' => $response->body()]);
        return ['error' => 'Không thể kết nối đến SePay API'];
    }

    /**
     * Sync transactions to local database with pagination
     */
    public function syncTransactions(array $filters = []): int
    {
        $syncedCount = 0;
        $page = 1;
        $limit = 100;
        $keepFetching = true;

        while ($keepFetching) {
            $result = $this->getTransactions($limit, $page, $filters);
            if (isset($result['error'])) {
                if ($page === 1) {
                    throw new \Exception($result['error']);
                }
                break;
            }

            $transactions = $result['transactions'] ?? [];
            if (empty($transactions)) {
                break;
            }

            $newOnThisPage = 0;
            foreach ($transactions as $txn) {
                $exists = \App\Models\SePayTransaction::where('transaction_id', $txn['id'])->exists();
                if (!$exists) {
                    \App\Models\SePayTransaction::create([
                        'transaction_id' => $txn['id'],
                        'gateway' => $txn['bank_brand_name'] ?? null,
                        'transaction_date' => $txn['transaction_date'] ?? null,
                        'account_number' => $txn['account_number'] ?? null,
                        'sub_account' => $txn['sub_account'] ?? null,
                        'amount_in' => $txn['amount_in'] ?? 0,
                        'amount_out' => $txn['amount_out'] ?? 0,
                        'accumulated' => $txn['accumulated'] ?? 0,
                        'transaction_content' => $txn['transaction_content'] ?? null,
                        'reference_number' => $txn['reference_number'] ?? null,
                        'code' => $txn['code'] ?? null,
                        'is_synced' => false,
                    ]);
                    $syncedCount++;
                    $newOnThisPage++;
                }
            }

            // Nếu không có GD mới nào trên trang này, khả năng cao các trang sau cũng toàn đồ cũ.
            // Hoặc kéo tối đa 5 trang (500 GD) mỗi lần sync để tránh timeout.
            if ($newOnThisPage === 0 || $page >= 5) {
                $keepFetching = false;
            } else {
                $page++;
            }
        }

        if ($syncedCount > 0) {
            \App\Models\PaymentLog::record(
                'sync_sepay_pages',
                "Đã kéo {$page} trang từ SePay API. Đồng bộ thêm {$syncedCount} giao dịch mới.",
                'info'
            );
        }

        return $syncedCount;
    }
}
