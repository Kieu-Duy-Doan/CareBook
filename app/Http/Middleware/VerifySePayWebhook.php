<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VerifySePayWebhook
{
    public function handle(Request $request, Closure $next): Response
    {
        $secret = config('services.sepay.webhook_secret');

        if (empty($secret)) {
            Log::error('SePay Webhook: Chưa cấu hình SEPAY_WEBHOOK_SECRET trong .env');
            return response()->json(['success' => false, 'message' => 'Server misconfigured'], 500);
        }

        $signature = $request->header('x-sepay-signature') ?? $request->header('Authorization');

        if (!$signature) {
            Log::warning('SePay Webhook: Thiếu signature header', ['ip' => $request->ip()]);
            return response()->json(['success' => false, 'message' => 'Missing signature'], 401);
        }

        // Phương thức 1: HMAC-SHA256 (ưu tiên — header x-sepay-signature)
        if (str_starts_with($signature, 'sha256=')) {
            $payload = $request->getContent();
            $timestamp = $request->header('x-sepay-timestamp', '');
            $dataToSign = $timestamp . '.' . $payload;
            $expectedSignature = hash_hmac('sha256', $dataToSign, $secret);
            $receivedHash = str_replace('sha256=', '', $signature);

            if (hash_equals($expectedSignature, $receivedHash)) {
                return $next($request);
            }

            Log::warning('SePay Webhook: HMAC signature không khớp', ['ip' => $request->ip()]);
            return response()->json(['success' => false, 'message' => 'Invalid signature'], 401);
        }

        // Phương thức 2: API Key (header Authorization: Apikey xxx)
        if (str_starts_with($signature, 'Apikey ')) {
            $token = str_replace('Apikey ', '', $signature);

            if (hash_equals($secret, $token)) {
                return $next($request);
            }

            Log::warning('SePay Webhook: API key không khớp', ['ip' => $request->ip()]);
            return response()->json(['success' => false, 'message' => 'Invalid API key'], 401);
        }

        Log::warning('SePay Webhook: Format signature không được hỗ trợ', [
            'ip' => $request->ip(),
            'signature_prefix' => substr($signature, 0, 10),
        ]);
        return response()->json(['success' => false, 'message' => 'Unsupported auth format'], 401);
    }
}
