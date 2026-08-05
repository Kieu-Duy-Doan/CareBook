<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Exports\TransactionsExport;
use App\Models\Payment;
use App\Models\InsuranceType;
use App\Models\PaymentLog;
use App\Models\User;
use App\Models\Specialty;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class TransactionHistoryController extends Controller
{
    /**
     * Trang danh sách lịch sử giao dịch với bộ lọc nâng cao.
     */
    public function index(Request $request)
    {
        [$from, $to] = $this->resolveDateRange($request);

        $query = Payment::with([
            'appointment.patientProfile',
            'appointment.doctorProfile.user',
            'appointment.specialty',
            'collectedBy',
            'clinicalVisits',
            'prescriptions',
        ]);

        // ── Bộ lọc ───────────────────────────────────────────────────────────
        $query->whereBetween('paid_at', [$from, $to]);

        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('collector_id')) {
            $query->where('collected_by', $request->collector_id);
        }

        if ($request->filled('specialty_id')) {
            $query->whereHas('appointment', fn($q) =>
                $q->where('specialty_id', $request->specialty_id)
            );
        }

        if ($request->filled('min_amount')) {
            $query->where('amount', '>=', (float) $request->min_amount);
        }

        if ($request->filled('max_amount')) {
            $query->where('amount', '<=', (float) $request->max_amount);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_code', 'like', "%{$search}%")
                  ->orWhere('note', 'like', "%{$search}%")
                  ->orWhereHas('appointment', function ($q2) use ($search) {
                      $q2->where('appointment_code', 'like', "%{$search}%")
                         ->orWhereHas('patientProfile', fn($q3) =>
                             $q3->where('full_name', 'like', "%{$search}%")
                         );
                  });
            });
        }

        $payments = $query->orderByDesc('paid_at')->paginate(20)->withQueryString();

        // ── Tính toán thêm cho từng payment ──────────────────────────────────
        $insuranceCache = [];
        $payments->getCollection()->transform(function ($payment) use (&$insuranceCache) {
            return $this->enrichPayment($payment, $insuranceCache);
        });

        // ── Summary Stats (theo bộ lọc hiện tại) ─────────────────────────────
        $summaryQuery = Payment::whereBetween('paid_at', [$from, $to]);

        if ($request->filled('method'))       $summaryQuery->where('method', $request->method);
        if ($request->filled('status'))       $summaryQuery->where('status', $request->status);
        if ($request->filled('collector_id')) $summaryQuery->where('collected_by', $request->collector_id);
        if ($request->filled('specialty_id')) {
            $summaryQuery->whereHas('appointment', fn($q) =>
                $q->where('specialty_id', $request->specialty_id)
            );
        }

        $totalCount    = $summaryQuery->count();
        $totalCash     = (clone $summaryQuery)->where('status', 'completed')->where('method', 'cash')->sum('amount');
        $totalSepay    = (clone $summaryQuery)->where('status', 'completed')->where('method', 'qr')->sum('amount');
        
        $pendingQuery = \App\Models\ClinicalVisit::with(['appointment.patientProfile', 'appointment.doctorProfile.user', 'room'])
            ->where('payment_status', 'pending');
        if ($request->filled('specialty_id')) {
            $pendingQuery->whereHas('appointment', fn($q) => $q->where('specialty_id', $request->specialty_id));
        }
        // Lấy danh sách chi tiết (giới hạn 100 để tránh nặng page)
        $pendingVisits = $pendingQuery->latest('created_at')->take(100)->get();
        $totalPending = $pendingQuery->sum('payment_amount');

        $allCompletedPayments = (clone $summaryQuery)->with(['appointment.patientProfile', 'clinicalVisits', 'prescriptions'])->get();
        $insuranceCacheForSummary = [];
        $totalInsurance = 0;
        foreach ($allCompletedPayments as $p) {
            $enriched = $this->enrichPayment($p, $insuranceCacheForSummary);
            $totalInsurance += $enriched->insurance_amount;
        }

        // Doanh thu = Tiền mặt + Sepay + BHYT chi trả
        $totalRevenue = $totalCash + $totalSepay + $totalInsurance;

        // ── Dữ liệu filter dropdowns ──────────────────────────────────────────
        $collectors = User::whereIn('role', ['admin', 'receptionist', 'doctor'])
            ->where('is_active', true)
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'role']);

        $specialties = Specialty::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('admin.payments.transactions', compact(
            'payments',
            'from', 'to',
            'totalCount', 'totalRevenue', 'totalCash', 'totalSepay', 'totalPending', 'totalInsurance',
            'collectors', 'specialties', 'pendingVisits'
        ));
    }

    /**
     * Trả về JSON chi tiết giao dịch cho modal.
     */
    public function show(Payment $payment)
    {
        $payment->load([
            'appointment.patientProfile',
            'appointment.doctorProfile.user',
            'appointment.specialty',
            'appointment.room',
            'collectedBy',
            'clinicalVisits.doctorProfile.user',
            'clinicalVisits.room',
            'prescriptions',
        ]);

        $insuranceCache = [];
        $enriched = $this->enrichPayment($payment, $insuranceCache);

        $logs = PaymentLog::where(function ($q) use ($payment) {
                $q->where('payment_id', $payment->id)
                  ->orWhere('appointment_id', $payment->appointment_id);
            })
            ->orderByDesc('created_at')
            ->with('user')
            ->take(10)
            ->get();

        $methodLabels = [
            'qr'        => ['label' => 'QR VietQR', 'color' => 'blue'],
            'cash'      => ['label' => 'Tiền mặt',  'color' => 'green'],
            'insurance' => ['label' => 'BHYT',       'color' => 'indigo'],
            'waived'    => ['label' => 'Miễn phí',   'color' => 'gray'],
        ];

        $statusLabels = [
            'completed'    => ['label' => 'Hoàn thành',  'color' => 'green'],
            'pending'      => ['label' => 'Chờ xử lý',   'color' => 'yellow'],
            'refunded'     => ['label' => 'Đã hoàn trả', 'color' => 'red'],
            'needs_review' => ['label' => 'Cần xem xét', 'color' => 'orange'],
        ];

        return response()->json([
            'payment' => [
                'id'               => $enriched->id,
                'transaction_code' => $enriched->transaction_code,
                'amount'           => $enriched->amount,
                'total_fee'        => $enriched->total_fee,
                'method'           => $enriched->method,
                'status'           => $enriched->status,
                'note'             => $enriched->note,
                'paid_at'          => $enriched->paid_at?->format('d/m/Y H:i:s'),
                'insurance_percent' => $enriched->insurance_percent,
                'insurance_amount'  => $enriched->insurance_amount,
                'patient_percent'   => $enriched->patient_percent,
                'patient_amount'    => $enriched->patient_amount,
                'collected_by_name' => $enriched->collectedBy?->full_name,
                'appointment_code'  => $enriched->appointment?->appointment_code,
                'appointment_date'  => $enriched->appointment?->appointment_date?->format('d/m/Y'),
                'patient_name'      => $enriched->appointment?->patientProfile?->full_name,
                'patient_code'      => $enriched->appointment?->patientProfile?->patient_code,
                'patient_phone'     => $enriched->appointment?->patientProfile?->phone,
                'insurance_code'    => $enriched->appointment?->patientProfile?->insurance_code,
                'doctor_name'       => $enriched->appointment?->doctorProfile?->user?->full_name,
                'specialty_name'    => $enriched->appointment?->specialty?->name,
                'room_name'         => $enriched->appointment?->room?->name,
                'clinical_visits'   => $enriched->clinicalVisits?->map(fn($v) => [
                    'doctor'         => $v->doctorProfile?->user?->full_name,
                    'room'           => $v->room?->name,
                    'payment_amount' => $v->payment_amount,
                    'payment_method' => $v->payment_method,
                    'payment_status' => $v->payment_status,
                    'started_at'     => $v->started_at?->format('d/m/Y H:i'),
                    'completed_at'   => $v->completed_at?->format('d/m/Y H:i'),
                ]),
                'prescriptions' => $enriched->prescriptions?->map(fn($p) => [
                    'payment_amount'  => $p->payment_amount,
                    'payment_status'  => $p->payment_status,
                    'payment_method'  => $p->payment_method,
                    'prescribed_date' => $p->prescribed_date?->format('d/m/Y'),
                    'diagnosis_note'  => $p->diagnosis_note,
                ]),
            ],
            'logs' => $logs->map(fn($l) => [
                'action'     => $l->action_label,
                'message'    => $l->message,
                'status'     => $l->status,
                'user'       => $l->user?->full_name,
                'created_at' => $l->created_at?->format('d/m/Y H:i'),
            ]),
            'methodLabels' => $methodLabels,
            'statusLabels' => $statusLabels,
        ]);
    }

    /**
     * Xuất Excel theo bộ lọc hiện tại.
     */
    public function exportExcel(Request $request)
    {
        [$from, $to] = $this->resolveDateRange($request);
        $filename = 'giao-dich_' . $from->format('Ymd') . '_' . $to->format('Ymd') . '.xlsx';

        return Excel::download(new TransactionsExport($request), $filename);
    }

    // ── Private Helpers ───────────────────────────────────────────────────────

    /**
     * Resolve khoảng thời gian từ request.
     * Nếu có 'month' (Y-m): override from/to theo tháng đó.
     */
    private function resolveDateRange(Request $request): array
    {
        if ($request->filled('month')) {
            $month = Carbon::createFromFormat('Y-m', $request->month);
            return [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()];
        }

        $from = $request->filled('from')
            ? Carbon::parse($request->from)->startOfDay()
            : Carbon::now()->startOfMonth();

        $to = $request->filled('to')
            ? Carbon::parse($request->to)->endOfDay()
            : Carbon::now()->endOfDay();

        return [$from, $to];
    }

    /**
     * Tính toán thông tin BHYT/bệnh nhân chi trả cho một payment.
     */
    private function enrichPayment(Payment $payment, array &$insuranceCache): Payment
    {
        $totalFee = 0;
        if ($payment->relationLoaded('clinicalVisits')) {
            $totalFee += $payment->clinicalVisits->sum('payment_amount');
        }
        if ($payment->relationLoaded('prescriptions')) {
            $totalFee += $payment->prescriptions->sum('payment_amount');
        }

        if ($totalFee == 0) {
            $totalFee = (float) ($payment->amount ?? 0);
        }

        if ($payment->method === 'insurance' || $payment->method === 'waived') {
            // Trường hợp bảo hiểm chi trả 100% hoặc miễn phí hoàn toàn
            $insuranceAmount = $totalFee;
            $patientAmount   = 0;
            $patientPercent  = 0;
            $insurancePercent= 100;
        } else {
            // Trường hợp có thanh toán Tiền mặt hoặc QR
            $patientAmount   = (float) $payment->amount;
            // BHYT chi trả = Số tiền gốc - Số tiền BN chi trả
            $insuranceAmount = max(0, $totalFee - $patientAmount);
            
            if ($totalFee > 0) {
                // Tính phần trăm tương ứng
                $patientPercent = round(($patientAmount / $totalFee) * 100);
                $insurancePercent = 100 - $patientPercent;
            } else {
                $patientPercent = 100;
                $insurancePercent = 0;
            }
        }
        $payment->total_fee         = $totalFee;
        $payment->insurance_percent = $insurancePercent;
        $payment->insurance_amount  = $insuranceAmount;
        $payment->patient_percent   = $patientPercent;
        $payment->patient_amount    = $patientAmount;

        return $payment;
    }
}

