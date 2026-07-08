<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\ClinicalVisit;
use App\Models\Payment;
use Illuminate\Http\Request;
use PayOS\PayOS;

class PaymentController extends Controller
{
    public function __construct()
    {
        // Removed PayOS injection from constructor so the page doesn't crash
        // if the user hasn't configured the PAYOS_* environment variables yet.
    }

    public function index(Request $request)
    {
        $query = Payment::with(['appointment.patientProfile', 'collectedBy'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('appointment', function($q) use ($search) {
                $q->where('appointment_code', 'like', "%{$search}%")
                  ->orWhereHas('patientProfile', function($q2) use ($search) {
                      $q2->where('full_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('payment_status')) {
            $query->where('status', $request->payment_status);
        }

        $payments = $query->paginate(15)->withQueryString();

        return view('receptionist.payments.index', compact('payments'));
    }

    public function edit($id)
    {
        $payment = Payment::with(['appointment.patientProfile', 'collectedBy'])->findOrFail($id);
        return view('receptionist.payments.edit', compact('payment'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,paid,failed,cancelled',
            'payment_method' => 'nullable|required_if:status,paid|in:cash,payos,insurance'
        ]);

        $payment = Payment::findOrFail($id);
        
        $payment->update([
            'status' => $request->status,
            'payment_method' => $request->status == 'paid' ? $request->payment_method : null,
            'paid_at' => $request->status == 'paid' ? now() : null,
        ]);
        
        // Cập nhật clinical_visits liên quan (nếu có)
        $clinicalVisits = $payment->appointment->clinicalVisits;
        foreach($clinicalVisits as $cv) {
            $cv->update([
                'payment_status' => $request->status == 'paid' ? 'paid' : 'pending',
                'payment_method' => $request->status == 'paid' ? $request->payment_method : null,
                'paid_at' => $request->status == 'paid' ? now() : null,
            ]);
        }

        return redirect()->route('receptionist.payments.index')
            ->with('success', 'Đã cập nhật trạng thái thanh toán thành công!');
    }

    public function create(Request $request, ClinicalVisit $clinical_visit)
    {
        $clinical_visit->load(['appointment.patientProfile', 'appointment.doctorProfile']);

        // Check if returning from PayOS
        if ($request->has('orderCode')) {
            try {
                $payOS = app(PayOS::class);
                $paymentInfo = $payOS->getPaymentLinkInformation($request->orderCode);
                
                if ($paymentInfo['status'] === 'PAID') {
                    $payment = Payment::where('order_code', $request->orderCode)->first();
                    if ($payment && $payment->status !== 'paid') {
                        $payment->update([
                            'status' => 'paid',
                            'paid_at' => now()
                        ]);
                        
                        $clinical_visit->update([
                            'payment_status' => 'paid',
                            'paid_at' => now()
                        ]);
                    }
                    session()->flash('success', 'Giao dịch thanh toán đã thành công!');
                } else if ($request->cancel == 'true' || $paymentInfo['status'] === 'CANCELLED') {
                    session()->flash('error', 'Giao dịch thanh toán đã bị hủy hoặc thất bại!');
                } else {
                    session()->flash('error', 'Giao dịch thanh toán chưa hoàn tất.');
                }
            } catch (\Exception $e) {
                if ($request->cancel == 'true') {
                    session()->flash('error', 'Giao dịch thanh toán đã bị hủy!');
                }
            }
            
            return redirect()->route('receptionist.payments.create', $clinical_visit->id);
        }

        return view('receptionist.payments.checkout', compact('clinical_visit'));
    }

    public function storeManual(Request $request, ClinicalVisit $clinical_visit)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,insurance'
        ]);

        $clinical_visit->update([
            'payment_amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'payment_status' => 'paid',
            'collected_by' => auth()->id(),
            'paid_at' => now(),
        ]);

        Payment::create([
            'appointment_id' => $clinical_visit->appointment_id,
            'order_code' => intval(substr(time(), -6) . rand(1000, 9999)),
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'status' => 'paid',
            'collected_by' => auth()->id(),
            'paid_at' => now(),
        ]);

        return redirect()->route('receptionist.clinical-visits.index')
            ->with('success', 'Đã thanh toán thành công bằng ' . ($request->payment_method == 'cash' ? 'Tiền mặt' : 'Bảo hiểm y tế'));
    }

    public function createPayOS(Request $request, ClinicalVisit $clinical_visit)
    {
        $request->validate([
            'amount' => 'required|numeric|min:2000'
        ]);

        $clinical_visit->update([
            'payment_amount' => $request->amount,
            'payment_method' => 'qr'
        ]);

        $orderCode = intval(substr(time(), -6) . rand(1000, 9999));

        $payment = Payment::create([
            'appointment_id' => $clinical_visit->appointment_id,
            'order_code' => $orderCode,
            'amount' => $request->amount,
            'payment_method' => 'payos',
            'status' => 'pending',
            'collected_by' => auth()->id(),
        ]);

        $data = [
            "orderCode" => $orderCode,
            "amount" => intval($request->amount),
            "description" => "Thanh toan lich kham",
            "returnUrl" => route('receptionist.payments.create', $clinical_visit->id),
            "cancelUrl" => route('receptionist.payments.create', $clinical_visit->id)
        ];

        try {
            $payOS = app(PayOS::class);
            $response = $payOS->createPaymentLink($data);
            return redirect($response['checkoutUrl']);
        } catch (\Exception $e) {
            return back()->with('error', 'Không thể tạo mã QR PayOS: ' . $e->getMessage());
        }
    }
}
