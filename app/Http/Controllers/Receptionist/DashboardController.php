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

        $payments = Payment::where('collected_by', $receptionistId)
            ->whereBetween('paid_at', [$start, $end])
            ->where('status', 'completed')
            ->get();

        $totalRevenue = $payments->sum('amount');
        $cashRevenue = $payments->where('method', 'cash')->sum('amount');
        $qrRevenue = $payments->where('method', 'qr')->sum('amount');

        $paymentsDetail = Payment::with(['appointment.patientProfile'])
            ->where('collected_by', $receptionistId)
            ->whereBetween('paid_at', [$start, $end])
            ->where('status', 'completed')
            ->orderBy('paid_at', 'desc')
            ->get();

        $chartFilter = $request->input('chart_filter', '7_days');
        $chartEnd = Carbon::today()->endOfDay();
        
        $isYearly = false;
        $isHourly = false;
        
        if ($chartFilter === 'today') {
            $chartStart = Carbon::today()->startOfDay();
            $isHourly = true;
        } elseif ($chartFilter === 'this_month') {
            $chartStart = Carbon::today()->startOfMonth();
            $chartEnd = Carbon::today()->endOfMonth();
        } elseif ($chartFilter === 'last_month') {
            $chartStart = Carbon::today()->subMonth()->startOfMonth();
            $chartEnd = Carbon::today()->subMonth()->endOfMonth();
        } elseif ($chartFilter === 'this_year') {
            $chartStart = Carbon::today()->startOfYear();
            $chartEnd = Carbon::today()->endOfYear();
            $isYearly = true;
        } elseif ($chartFilter === 'last_year') {
            $chartStart = Carbon::today()->subYear()->startOfYear();
            $chartEnd = Carbon::today()->subYear()->endOfYear();
            $isYearly = true;
        } elseif ($chartFilter === '30_days') {
            $chartStart = Carbon::today()->subDays(29)->startOfDay();
        } else { // 7_days
            $chartStart = Carbon::today()->subDays(6)->startOfDay();
        }

        $chartDates = [];
        $revenueCashData = [];
        $revenueQrData = [];

        if ($isYearly) {
            $chartDataObj = Payment::select(DB::raw('MONTH(paid_at) as month'), 'method', DB::raw('SUM(amount) as total'))
                ->where('collected_by', $receptionistId)
                ->whereBetween('paid_at', [$chartStart, $chartEnd])
                ->where('status', 'completed')
                ->groupBy('month', 'method')
                ->get();
                
            for ($m = 1; $m <= 12; $m++) {
                $chartDates[] = 'Tháng ' . $m;
                
                $cashForMonth = $chartDataObj->where('month', $m)->where('method', 'cash')->first();
                $qrForMonth = $chartDataObj->where('month', $m)->where('method', 'qr')->first();
                
                $revenueCashData[] = $cashForMonth ? (float)$cashForMonth->total : 0;
                $revenueQrData[] = $qrForMonth ? (float)$qrForMonth->total : 0;
            }
        } elseif ($isHourly) {
            $chartDataObj = Payment::select(DB::raw('HOUR(paid_at) as hour'), 'method', DB::raw('SUM(amount) as total'))
                ->where('collected_by', $receptionistId)
                ->whereBetween('paid_at', [$chartStart, $chartEnd])
                ->where('status', 'completed')
                ->groupBy('hour', 'method')
                ->get();
                
            for ($h = 7; $h <= 20; $h++) { // 7h - 20h
                $chartDates[] = $h . ':00';
                
                $cashForHour = $chartDataObj->where('hour', $h)->where('method', 'cash')->first();
                $qrForHour = $chartDataObj->where('hour', $h)->where('method', 'qr')->first();
                
                $revenueCashData[] = $cashForHour ? (float)$cashForHour->total : 0;
                $revenueQrData[] = $qrForHour ? (float)$qrForHour->total : 0;
            }
        } else {
            $chartDataObj = Payment::select(DB::raw('DATE(paid_at) as date'), 'method', DB::raw('SUM(amount) as total'))
                ->where('collected_by', $receptionistId)
                ->whereBetween('paid_at', [$chartStart, $chartEnd])
                ->where('status', 'completed')
                ->groupBy('date', 'method')
                ->get();
                
            $currentDate = $chartStart->copy();
            while ($currentDate <= $chartEnd) {
                $dateStr = $currentDate->format('Y-m-d');
                $chartDates[] = $currentDate->format('d/m');
                
                $cashForDate = $chartDataObj->where('date', $dateStr)->where('method', 'cash')->first();
                $qrForDate = $chartDataObj->where('date', $dateStr)->where('method', 'qr')->first();
                
                $revenueCashData[] = $cashForDate ? (float)$cashForDate->total : 0;
                $revenueQrData[] = $qrForDate ? (float)$qrForDate->total : 0;
                
                $currentDate->addDay();
            }
        }

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
