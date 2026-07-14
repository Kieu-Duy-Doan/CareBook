<?php

namespace App\Http\Controllers\Api\Webhook;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SePayWebhookController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Nhận Webhook từ SePay khi có biến động số dư.
     * Signature đã được xác thực bởi VerifySePayWebhook middleware.
     */
    public function handle(Request $request)
    {
        try {
            $this->paymentService->processSePayWebhook($request->all());

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('SePay Webhook Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['success' => false, 'message' => 'Internal error'], 500);
        }
    }
}
