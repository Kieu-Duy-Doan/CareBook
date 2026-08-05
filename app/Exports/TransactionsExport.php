<?php

namespace App\Exports;

use App\Models\Payment;
use App\Models\InsuranceType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TransactionsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected Request $request;

    private array $methodLabels = [
        'qr'        => 'QR VietQR',
        'cash'      => 'Tiền mặt',
        'insurance' => 'BHYT',
        'waived'    => 'Miễn phí',
    ];

    private array $statusLabels = [
        'completed'    => 'Hoàn thành',
        'pending'      => 'Chờ xử lý',
        'refunded'     => 'Đã hoàn trả',
        'needs_review' => 'Cần xem xét',
    ];

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function title(): string
    {
        return 'Lịch sử giao dịch';
    }

    public function collection()
    {
        [$from, $to] = $this->resolveDateRange();

        $query = Payment::with([
            'appointment.patientProfile',
            'appointment.doctorProfile.user',
            'appointment.specialty',
            'collectedBy',
        ])->whereBetween('paid_at', [$from, $to]);

        if ($this->request->filled('method'))       $query->where('method', $this->request->method);
        if ($this->request->filled('status'))       $query->where('status', $this->request->status);
        if ($this->request->filled('collector_id')) $query->where('collected_by', $this->request->collector_id);
        if ($this->request->filled('specialty_id')) {
            $query->whereHas('appointment', fn($q) =>
                $q->where('specialty_id', $this->request->specialty_id)
            );
        }
        if ($this->request->filled('min_amount'))   $query->where('amount', '>=', (float) $this->request->min_amount);
        if ($this->request->filled('max_amount'))   $query->where('amount', '<=', (float) $this->request->max_amount);
        if ($this->request->filled('search')) {
            $search = $this->request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_code', 'like', "%{$search}%")
                  ->orWhere('note', 'like', "%{$search}%")
                  ->orWhereHas('appointment', fn($q2) =>
                      $q2->where('appointment_code', 'like', "%{$search}%")
                         ->orWhereHas('patientProfile', fn($q3) =>
                             $q3->where('full_name', 'like', "%{$search}%")
                         )
                  );
            });
        }

        return $query->orderByDesc('paid_at')->get();
    }

    public function headings(): array
    {
        return [
            'Mã Giao Dịch',
            'Ngày Thanh Toán',
            'Mã Lịch Hẹn',
            'Bệnh Nhân',
            'Mã BHYT',
            'Chuyên Khoa',
            'Bác Sĩ',
            'Phí Gốc (đ)',
            'Số Tiền GD (đ)',
            'BHYT Chi Trả (%)',
            'BHYT Chi Trả (đ)',
            'Bệnh Nhân Chi Trả (%)',
            'Bệnh Nhân Chi Trả (đ)',
            'Phương Thức',
            'Trạng Thái',
            'Người Thu',
            'Ghi Chú',
        ];
    }

    public function map($payment): array
    {
        $totalFee       = (float) ($payment->appointment?->total_fee ?? $payment->amount ?? 0);
        $insuranceCode  = $payment->appointment?->patientProfile?->insurance_code;
        $coveragePercent = 0;

        if ($payment->method === 'insurance' && $insuranceCode && strlen($insuranceCode) >= 2) {
            $prefix = strtoupper(substr($insuranceCode, 0, 2));
            $coveragePercent = InsuranceType::where('prefix', $prefix)
                ->where('is_active', true)
                ->value('coverage_percent') ?? 0;
        }

        $insuranceAmount = $totalFee > 0 ? round($totalFee * $coveragePercent / 100, 2) : 0;
        $patientAmount   = max(0, $totalFee - $insuranceAmount);
        $patientPercent  = 100 - $coveragePercent;

        return [
            $payment->transaction_code,
            $payment->paid_at?->format('d/m/Y H:i'),
            $payment->appointment?->appointment_code,
            $payment->appointment?->patientProfile?->full_name,
            $insuranceCode,
            $payment->appointment?->specialty?->name,
            $payment->appointment?->doctorProfile?->user?->full_name,
            $totalFee,
            $payment->amount,
            $coveragePercent . '%',
            $insuranceAmount,
            $patientPercent . '%',
            $patientAmount,
            $this->methodLabels[$payment->method] ?? $payment->method,
            $this->statusLabels[$payment->status] ?? $payment->status,
            $payment->collectedBy?->full_name,
            $payment->note,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    private function resolveDateRange(): array
    {
        if ($this->request->filled('month')) {
            $month = Carbon::createFromFormat('Y-m', $this->request->month);
            return [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()];
        }

        $from = $this->request->filled('from')
            ? Carbon::parse($this->request->from)->startOfDay()
            : Carbon::now()->startOfMonth();

        $to = $this->request->filled('to')
            ? Carbon::parse($this->request->to)->endOfDay()
            : Carbon::now()->endOfDay();

        return [$from, $to];
    }
}

