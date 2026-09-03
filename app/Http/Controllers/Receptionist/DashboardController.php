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
    protected $paymentService;

    public function __construct(DashboardService $dashboardService, \App\Services\PaymentService $paymentService)
    {
        $this->dashboardService = $dashboardService;
        $this->paymentService = $paymentService;
    }

    public function index(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::today()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::today()->format('Y-m-d'));

        try {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();
            if ($start->gt($end)) {
                $temp = $start;
                $start = $end->copy()->startOfDay();
                $end = $temp->copy()->endOfDay();
                $startDate = $start->format('Y-m-d');
                $endDate = $end->format('Y-m-d');
            }
        } catch (\Exception $e) {
            $startDate = Carbon::today()->format('Y-m-d');
            $endDate = Carbon::today()->format('Y-m-d');
            $start = Carbon::today()->startOfDay();
            $end = Carbon::today()->endOfDay();
        }

        $receptionistId = Auth::id();

        $dashboardData = $this->dashboardService->getReceptionistDashboardData($start, $end);

        $startDateStr = $start->format('Y-m-d');
        $endDateStr = $end->format('Y-m-d');

        $totalCheckins = Appointment::whereBetween('appointment_date', [$startDateStr, $endDateStr])
            ->whereIn('status', ['checked_in', 'examining', 'completed'])
            ->count();

        // Lấy tất cả giao dịch thanh toán hoàn tất trong khoảng thời gian
        // Không lọc cứng theo collected_by để các giao dịch chuyển khoản VietQR/SePay (có collected_by = null hoặc do hệ thống ghi nhận)
        // và doanh thu chung của phòng khám/ca trực đều được hiển thị đầy đủ, chính xác.
        $payments = Payment::with(['appointment.patientProfile', 'clinicalVisits'])
            ->whereBetween('paid_at', [$start, $end])
            ->whereIn('status', ['completed', 'needs_review'])
            ->orderBy('paid_at', 'desc')
            ->get();

        $cashRevenue = (float) $payments->where('method', 'cash')->sum('amount');
        $qrRevenue = (float) $payments->where('method', 'qr')->sum('amount');

        $insuranceCache = [];
        $insuranceRevenue = 0;
        foreach ($payments as $payment) {
            $this->paymentService->enrichPayment($payment, $insuranceCache);
            $insuranceRevenue += (float) $payment->insurance_amount;
        }

        // Tổng Doanh thu = Tiền mặt + Chuyển khoản QR + BHYT chi trả
        $totalRevenue = $cashRevenue + $qrRevenue + $insuranceRevenue;
        $paymentsDetail = $payments;

        $chartFilter = $request->input('chart_filter', '7_days');
        $chartData = $this->dashboardService->getReceptionistRevenueChartData($receptionistId, $chartFilter);
        
        $chartDates = $chartData['chartDates'];
        $revenueCashData = $chartData['revenueCashData'];
        $revenueQrData = $chartData['revenueQrData'];

        return view('receptionist.dashboard', array_merge($dashboardData, compact(
            'startDate', 'endDate', 'totalCheckins', 'totalRevenue', 'cashRevenue', 'qrRevenue', 'insuranceRevenue',
            'chartDates', 'revenueCashData', 'revenueQrData', 'paymentsDetail', 'chartFilter'
        )));
    }

    public function exportCsv(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::today()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::today()->format('Y-m-d'));

        try {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();
            if ($start->gt($end)) {
                $temp = $start;
                $start = $end->copy()->startOfDay();
                $end = $temp->copy()->endOfDay();
                $startDate = $start->format('Y-m-d');
                $endDate = $end->format('Y-m-d');
            }
        } catch (\Exception $e) {
            $startDate = Carbon::today()->format('Y-m-d');
            $endDate = Carbon::today()->format('Y-m-d');
            $start = Carbon::today()->startOfDay();
            $end = Carbon::today()->endOfDay();
        }

        $payments = Payment::with(['appointment.patientProfile', 'clinicalVisits'])
            ->whereBetween('paid_at', [$start, $end])
            ->whereIn('status', ['completed', 'needs_review'])
            ->orderBy('paid_at')
            ->get();

        $insuranceCache = [];
        foreach ($payments as $payment) {
            $this->paymentService->enrichPayment($payment, $insuranceCache);
        }

        $filename = 'bao-cao-doanh-thu-le-tan-' . now()->format('Ymd-His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($payments, $startDate, $endDate) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM chống vỡ font tiếng Việt
            fputcsv($file, ['BÁO CÁO THU TIỀN VÀ BHYT CHI TRẢ CA LỄ TÂN']);
            fputcsv($file, ['Từ ngày', $startDate, 'Đến ngày', $endDate]);
            fputcsv($file, []);
            fputcsv($file, ['Thời gian thanh toán', 'Mã Lịch hẹn', 'Bệnh nhân', 'Phương thức', 'Tiền BN trả (₫)', 'BHYT chi trả (₫)', 'Tổng chi phí (₫)']);

            $totalPatient = 0;
            $totalInsurance = 0;
            $totalAll = 0;

            foreach ($payments as $payment) {
                $patientAmt = (float) $payment->patient_amount;
                $insAmt = (float) $payment->insurance_amount;
                $totalAmt = (float) $payment->total_fee;

                $totalPatient += $patientAmt;
                $totalInsurance += $insAmt;
                $totalAll += $totalAmt;

                fputcsv($file, [
                    $payment->paid_at?->format('d/m/Y H:i') ?? '—',
                    $payment->appointment?->appointment_code ?? '—',
                    $payment->appointment?->patientProfile?->full_name ?? 'Khách vãng lai',
                    match($payment->method) {
                        'cash' => 'Tiền mặt',
                        'qr' => 'Chuyển khoản QR',
                        'insurance' => 'BHYT',
                        'waived' => 'Miễn phí',
                        default => $payment->method ?? 'Khác',
                    },
                    number_format($patientAmt, 0, ',', '.'),
                    number_format($insAmt, 0, ',', '.'),
                    number_format($totalAmt, 0, ',', '.'),
                ]);
            }

            fputcsv($file, []);
            fputcsv($file, [
                'Tổng cộng', '', '', '',
                number_format($totalPatient, 0, ',', '.'),
                number_format($totalInsurance, 0, ',', '.'),
                number_format($totalAll, 0, ',', '.')
            ]);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
