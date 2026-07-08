<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use PayOS\PayOS;

class PayOSWebhookController extends Controller
{
    public function __construct()
    {
        // Removed PayOS injection from constructor
    }

    public function handleWebhook(Request $request)
    {
        $body = $request->all();

        try {
            $payOS = app(PayOS::class);
            $data = $payOS->verifyPaymentWebhookData($body);

            if ($data['code'] == '00') { // success
                $orderCode = $data['orderCode'];
                
                $payment = Payment::where('order_code', $orderCode)->first();
                if ($payment) {
                    $payment->update([
                        'status' => 'paid',
                        'paid_at' => now()
                    ]);

                    if ($payment->appointment) {
                        $clinicalVisits = $payment->appointment->clinicalVisits;
                        foreach($clinicalVisits as $cv) {
                            $cv->update([
                                'payment_status' => 'paid',
                                'paid_at' => now(),
                            ]);
                        }
                    }
                }
            }

            return response()->json([
                "error" => 0,
                "message" => "Ok",
                "data" => null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "error" => -1,
                "message" => $e->getMessage(),
                "data" => null
            ]);
        }
    }
}
