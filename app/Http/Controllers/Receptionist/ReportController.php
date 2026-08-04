<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::today()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::today()->format('Y-m-d'));

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        $receptionistId = Auth::id();

        // 1. Tổng quan Check-in (Số lịch hẹn có status checked_in, examining, completed)
        $totalCheckins = Appointment::whereBetween('appointment_date', [$start, $end])
            ->whereIn('status', ['checked_in', 'examining', 'completed'])
            ->count();

        // 2. Tổng thu của lễ tân này
        $payments = Payment::where('collected_by', $receptionistId)
            ->whereBetween('paid_at', [$start, $end])
            ->where('status', 'completed')
            ->get();

        $totalRevenue = $payments->sum('amount');
        $cashRevenue = $payments->where('method', 'cash')->sum('amount');
        $qrRevenue = $payments->where('method', 'qr')->sum('amount');

        // Lấy chi tiết các thanh toán để hiển thị bảng (nếu cần)
        $paymentsDetail = Payment::with(['appointment.patientProfile'])
            ->where('collected_by', $receptionistId)
            ->whereBetween('paid_at', [$start, $end])
            ->where('status', 'completed')
            ->orderBy('paid_at', 'desc')
            ->get();

        // 3. Dữ liệu biểu đồ doanh thu theo ngày
        $chartData = Payment::select(DB::raw('DATE(paid_at) as date'), 'method', DB::raw('SUM(amount) as total'))
            ->where('collected_by', $receptionistId)
            ->whereBetween('paid_at', [$start, $end])
            ->where('status', 'completed')
            ->groupBy('date', 'method')
            ->get();

        $dates = [];
        $currentDate = $start->copy();
        
        while ($currentDate <= $end) {
            $dates[] = $currentDate->format('d/m/Y');
            $currentDate->addDay();
        }

        $revenueCashData = [];
        $revenueQrData = [];
        
        foreach ($dates as $date) {
            $formattedDate = Carbon::createFromFormat('d/m/Y', $date)->format('Y-m-d');
            $cashForDate = $chartData->where('date', $formattedDate)->where('method', 'cash')->first();
            $qrForDate = $chartData->where('date', $formattedDate)->where('method', 'qr')->first();
            
            $revenueCashData[] = $cashForDate ? (float)$cashForDate->total : 0;
            $revenueQrData[] = $qrForDate ? (float)$qrForDate->total : 0;
        }

        return view('receptionist.reports.index', compact(
            'startDate', 'endDate', 'totalCheckins', 'totalRevenue', 'cashRevenue', 'qrRevenue',
            'dates', 'revenueCashData', 'revenueQrData', 'paymentsDetail'
        ));
    }

    public function exportCsv(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::today()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::today()->format('Y-m-d'));

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        $receptionistId = Auth::id();

        $payments = Payment::with(['appointment.patientProfile'])
            ->where('collected_by', $receptionistId)
            ->whereBetween('paid_at', [$start, $end])
            ->where('status', 'completed')
            ->orderBy('paid_at')
            ->get();

        $filename = 'bao-cao-le-tan-' . now()->format('Ymd-His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($payments, $startDate, $endDate) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM
            fputcsv($file, ['Từ ngày', 'Đến ngày', $startDate, $endDate]);
            fputcsv($file, []);
            fputcsv($file, ['Thời gian thanh toán', 'Mã Lịch hẹn', 'Bệnh nhân', 'Phương thức', 'Số tiền (₫)']);

            foreach ($payments as $payment) {
                fputcsv($file, [
                    $payment->paid_at?->format('d/m/Y H:i'),
                    $payment->appointment->appointment_code ?? '',
                    $payment->appointment->patientProfile->full_name ?? '',
                    $payment->method === 'cash' ? 'Tiền mặt' : 'QR/Chuyển khoản',
                    number_format($payment->amount, 0, ',', '.'),
                ]);
            }

            // Summary row
            fputcsv($file, []);
            fputcsv($file, ['Tổng thu', '', '', '', number_format($payments->sum('amount'), 0, ',', '.')]);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
