<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Services\DashboardService;
use App\Models\Appointment;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::today()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::today()->format('Y-m-d'));

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        $receptionistId = Auth::id();

        $dashboardData = $this->dashboardService->getReceptionistDashboardData($start, $end);

        $totalCheckins = Appointment::whereBetween('appointment_date', [$start, $end])
            ->whereIn('status', ['checked_in', 'examining', 'completed'])
            ->count();

        $payments = Payment::with(['clinicalVisits'])
            ->where('collected_by', $receptionistId)
            ->whereBetween('paid_at', [$start, $end])
            ->where('status', 'completed')
            ->get();

        $totalRevenue = $payments->sum(function($p) {
            $fee = $p->clinicalVisits->sum('payment_amount');
            return max($fee, (float) $p->amount);
        });
        $cashRevenue = $payments->where('method', 'cash')->sum('amount');
        $qrRevenue = $payments->where('method', 'qr')->sum('amount');

        $paymentsDetail = Payment::with(['appointment.patientProfile'])
            ->where('collected_by', $receptionistId)
            ->whereBetween('paid_at', [$start, $end])
            ->where('status', 'completed')
            ->orderBy('paid_at', 'desc')
            ->get();

        $chartFilter = $request->input('chart_filter', '7_days');
        $chartData = $this->dashboardService->getReceptionistRevenueChartData($receptionistId, $chartFilter);
        
        $chartDates = $chartData['chartDates'];
        $revenueCashData = $chartData['revenueCashData'];
        $revenueQrData = $chartData['revenueQrData'];

        return view('receptionist.dashboard', array_merge($dashboardData, compact(
            'startDate', 'endDate', 'totalCheckins', 'totalRevenue', 'cashRevenue', 'qrRevenue',
            'chartDates', 'revenueCashData', 'revenueQrData', 'paymentsDetail', 'chartFilter'
        )));
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

            fputcsv($file, []);
            fputcsv($file, ['Tổng thu', '', '', '', number_format($payments->sum('amount'), 0, ',', '.')]);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
