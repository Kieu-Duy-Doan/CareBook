<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\ClinicalVisit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PaymentController extends Controller
{
    /**
     * Danh sách lịch sử thanh toán & hoá đơn
     */
    public function index(Request $request)
    {
        $query = ClinicalVisit::with([
            'appointment.patientProfile',
            'doctorProfile.user',
            'room',
            'collectedBy'
        ]);

        // Filter by Date
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->input('date'));
        }

        // Filter by Payment Status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->input('payment_status'));
        }

        // Search by Patient Name or Code
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('appointment.patientProfile', function ($q) use ($search) {
                $q->where('full_name', 'like', '%' . $search . '%')
                  ->orWhere('patient_code', 'like', '%' . $search . '%');
            });
        }

        $visits = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        // Calculate statistics for today
        $today = Carbon::today();
        
        $totalCollectedToday = ClinicalVisit::whereDate('paid_at', $today)
            ->where('payment_status', 'paid')
            ->sum('payment_amount');

        $cashCollectedToday = ClinicalVisit::whereDate('paid_at', $today)
            ->where('payment_status', 'paid')
            ->where('payment_method', 'cash')
            ->sum('payment_amount');

        $qrCollectedToday = ClinicalVisit::whereDate('paid_at', $today)
            ->where('payment_status', 'paid')
            ->where('payment_method', 'qr')
            ->sum('payment_amount');

        $pendingAmountToday = ClinicalVisit::whereDate('created_at', $today)
            ->where('payment_status', 'pending')
            ->sum('payment_amount');

        return view('receptionist.payments.index', compact(
            'visits', 
            'totalCollectedToday', 
            'cashCollectedToday', 
            'qrCollectedToday', 
            'pendingAmountToday'
        ));
    }

    /**
     * Màn hình chuẩn bị thanh toán
     */
    public function create($id)
    {
        $visit = ClinicalVisit::with([
            'appointment.patientProfile',
            'doctorProfile.user',
            'room'
        ])->findOrFail($id);

        if ($visit->payment_status !== 'pending') {
            return redirect()->route('receptionist.payments.index')
                ->with('error', 'Lượt khám này đã được xử lý thanh toán.');
        }

        return view('receptionist.payments.create', compact('visit'));
    }

    /**
     * Xử lý thanh toán thủ công (Tiền mặt, Bảo hiểm, Miễn phí)
     */
    public function storeManual(Request $request, $id)
    {
        $request->validate([
            'payment_method' => 'required|in:cash,insurance,waived',
        ], [
            'payment_method.required' => 'Vui lòng chọn hình thức thanh toán.',
            'payment_method.in' => 'Hình thức thanh toán không hợp lệ.',
        ]);

        $visit = ClinicalVisit::findOrFail($id);

        if ($visit->payment_status !== 'pending') {
            return redirect()->route('receptionist.payments.index')
                ->with('error', 'Lượt khám này đã được xử lý thanh toán.');
        }

        $method = $request->input('payment_method');
        
        $visit->payment_status = $method === 'waived' ? 'waived' : 'paid';
        $visit->payment_method = $method;
        $visit->collected_by = Auth::id();
        $visit->paid_at = now();
        $visit->save();

        return redirect()->route('receptionist.payments.index')
            ->with('success', 'Đã ghi nhận thanh toán thành công cho bệnh nhân.');
    }

    /**
     * Khởi tạo cổng thanh toán giả lập PayOS QR
     */
    public function createPayOS($id)
    {
        $visit = ClinicalVisit::with([
            'appointment.patientProfile',
            'doctorProfile.user',
            'room'
        ])->findOrFail($id);

        if ($visit->payment_status !== 'pending') {
            return redirect()->route('receptionist.payments.index')
                ->with('error', 'Lượt khám này đã được xử lý thanh toán.');
        }

        // Tạo nội dung chuyển khoản mã hoá
        $transactionRef = 'CB' . str_pad($visit->id, 6, '0', STR_PAD_LEFT);

        return view('receptionist.payments.checkout', compact('visit', 'transactionRef'));
    }

    /**
     * Cập nhật trạng thái thanh toán khi giả lập PayOS thành công
     */
    public function update(Request $request, $id)
    {
        $visit = ClinicalVisit::findOrFail($id);

        if ($visit->payment_status !== 'pending') {
            return redirect()->route('receptionist.payments.index')
                ->with('error', 'Lượt khám này đã được xử lý thanh toán.');
        }

        $visit->payment_status = 'paid';
        $visit->payment_method = 'qr';
        $visit->collected_by = Auth::id();
        $visit->paid_at = now();
        $visit->save();

        return redirect()->route('receptionist.payments.index')
            ->with('success', 'Thanh toán qua PayOS QR đã được xử lý và xác nhận thành công.');
    }
}
