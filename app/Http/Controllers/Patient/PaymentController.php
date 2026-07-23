<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Payment;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PaymentController extends Controller
{
    /**
     * Tải hóa đơn PDF cho một thanh toán cụ thể.
     */
    public function print($appointment_id, $payment_id)
    {
        // 1. Xác minh quyền sở hữu: Cuộc hẹn này phải thuộc về user đang đăng nhập
        $appointment = Appointment::with(['patientProfile'])
            ->where('booked_by_user_id', auth()->id())
            ->findOrFail($appointment_id);

        // 2. Lấy chi tiết payment cùng với các dịch vụ đã thanh toán trong hóa đơn này
        $payment = Payment::with(['clinicalVisits', 'prescriptions'])
            ->where('appointment_id', $appointment->id)
            ->findOrFail($payment_id);

        // 3. Tính toán lại summary riêng cho hóa đơn này
        // (Trong hóa đơn này có thể bao gồm BHYT hay không, nhưng ta lấy từ số tiền bệnh nhân thực tế đã trả)
        $totalPaymentAmount = $payment->amount;

        // Render PDF
        $pdf = Pdf::loadView('patient.payments.invoice', [
            'appointment' => $appointment,
            'payment' => $payment,
            'totalAmount' => $totalPaymentAmount
        ]);

        return $pdf->stream('Hoa_Don_' . $payment->transaction_code . '.pdf');
    }
}
