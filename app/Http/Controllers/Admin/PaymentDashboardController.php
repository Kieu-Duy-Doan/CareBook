<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\ClinicalVisit;
use App\Models\Appointment;
use App\Models\PaymentLog;
use App\Models\SePayTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentDashboardController extends Controller
{
    public function index(Request $request)
    {
        // Khoảng thời gian (mặc định tháng này)
        $from = $request->filled('from')
            ? Carbon::parse($request->from)->startOfDay()
            : Carbon::now()->startOfMonth();

        $to = $request->filled('to')
            ? Carbon::parse($request->to)->endOfDay()
            : Carbon::now()->endOfDay();

        // ── Thống kê tổng quan ──────────────────────────────
        $payments = Payment::with(['clinicalVisits'])
            ->whereBetween('paid_at', [$from, $to])
            ->where('status', 'completed')
            ->get();
            
        $totalRevenue = $payments->sum(function($p) {
            $fee = $p->clinicalVisits->sum('payment_amount');
            return max($fee, (float) $p->amount);
        });

        $totalPending = ClinicalVisit::where('payment_status', 'pending')->sum('payment_amount');

        // ── Doanh thu theo phương thức ──────────────────────
        $byMethod = Payment::whereBetween('paid_at', [$from, $to])
            ->where('status', 'completed')
            ->where('amount', '>', 0)
            ->selectRaw('method, SUM(amount) as total')
            ->groupBy('method')
            ->pluck('total', 'method');

        // ── Biểu đồ theo ngày (7 ngày gần nhất hoặc trong range) ──
        $chartDays = min((int)$from->diffInDays($to) + 1, 30);
        $chartFrom = $to->copy()->subDays($chartDays - 1)->startOfDay();

        $chartPayments = Payment::with(['clinicalVisits'])
            ->whereBetween('paid_at', [$chartFrom, $to])
            ->where('status', 'completed')
            ->get();

        $dailyRevenue = [];
        foreach ($chartPayments as $p) {
            $date = $p->paid_at->format('Y-m-d');
            $fee = $p->clinicalVisits->sum('payment_amount');
            $rev = max($fee, (float) $p->amount);
            $dailyRevenue[$date] = ($dailyRevenue[$date] ?? 0) + $rev;
        }

        // Fill days with 0 for missing dates
        $chartLabels = [];
        $chartData = [];
        for ($i = $chartDays - 1; $i >= 0; $i--) {
            $date = $to->copy()->subDays($i)->format('Y-m-d');
            $chartLabels[] = $to->copy()->subDays($i)->format('d/m');
            $chartData[] = (float)($dailyRevenue[$date] ?? 0);
        }


        // ── Đối soát: % đã khớp ───────────────────────────
        $totalSepayTxns = SePayTransaction::count();
        $matchedTxns = SePayTransaction::where('reconciliation_status', 'matched')->count();
        $reconciliationRate = $totalSepayTxns > 0 ? round($matchedTxns / $totalSepayTxns * 100) : 0;

        return view('admin.payments.dashboard', compact(
            'from', 'to',
            'totalRevenue', 'totalPending',
            'byMethod',
            'chartLabels', 'chartData',
            'reconciliationRate', 'totalSepayTxns', 'matchedTxns'
        ));
    }



    /**
     * Export danh sách thanh toán ra CSV
     */
    public function exportCsv(Request $request)
    {
        $from = $request->filled('from') ? Carbon::parse($request->from)->startOfDay() : Carbon::now()->startOfMonth();
        $to = $request->filled('to') ? Carbon::parse($request->to)->endOfDay() : Carbon::now()->endOfDay();

        $payments = Payment::with(['appointment.patientProfile', 'collectedBy'])
            ->whereBetween('paid_at', [$from, $to])
            ->where('status', '!=', 'pending')
            ->orderBy('paid_at')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="payments_' . $from->format('Ymd') . '_' . $to->format('Ymd') . '.csv"',
        ];

        $callback = function () use ($payments) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM for Excel

            fputcsv($file, ['Mã GD', 'Ngày TT', 'Mã Lịch hẹn', 'Bệnh nhân', 'Số tiền', 'PT Thanh toán', 'Trạng thái', 'Nhân viên thu', 'Ghi chú']);

            foreach ($payments as $p) {
                fputcsv($file, [
                    $p->transaction_code,
                    $p->paid_at?->format('d/m/Y H:i'),
                    $p->appointment?->appointment_code,
                    $p->appointment?->patientProfile?->full_name,
                    number_format($p->amount),
                    $p->method,
                    $p->status,
                    $p->collectedBy?->name,
                    $p->note,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
    /**
     * In báo cáo danh sách thanh toán (PDF/Print)
     */
    public function printReport(Request $request)
    {
        $from = $request->filled('from') ? Carbon::parse($request->from)->startOfDay() : Carbon::now()->startOfMonth();
        $to = $request->filled('to') ? Carbon::parse($request->to)->endOfDay() : Carbon::now()->endOfDay();

        $payments = Payment::with(['appointment.patientProfile', 'collectedBy'])
            ->whereBetween('paid_at', [$from, $to])
            ->where('status', '!=', 'pending')
            ->orderBy('paid_at')
            ->get();

        $totalRevenue = $payments->where('status', 'completed')->where('amount', '>', 0)->sum('amount');

        return view('admin.payments.print-report', compact('payments', 'from', 'to', 'totalRevenue'));
    }
}
