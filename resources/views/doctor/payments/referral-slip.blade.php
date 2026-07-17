<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phiếu Chỉ Định Cận Lâm Sàng - {{ $appointment->appointment_code }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            width: 80mm;
            max-width: 80mm;
            color: #000;
            background: #fff;
        }

        .center { text-align: center; }
        .bold { font-weight: bold; }
        .divider { border-top: 1px dashed #000; margin: 4px 0; }
        .divider-solid { border-top: 1px solid #000; margin: 4px 0; }

        .header { text-align: center; margin-bottom: 6px; }
        .header h1 { font-size: 13px; font-weight: bold; text-transform: uppercase; }
        .header p { font-size: 10px; }

        .section { margin: 5px 0; }
        .section-title { font-weight: bold; font-size: 10px; text-transform: uppercase; margin-bottom: 3px; }

        table { width: 100%; border-collapse: collapse; }
        table td { padding: 2px 0; vertical-align: top; }
        table td.label { color: #444; width: 35%; }
        table td.value { font-weight: bold; }

        .visit-table { margin-top: 4px; }
        .visit-table th { font-size: 9px; text-transform: uppercase; text-align: left; border-bottom: 1px solid #000; padding-bottom: 2px; }
        .visit-table td { font-size: 10px; padding: 3px 0; border-bottom: 1px dashed #ccc; }
        .visit-table td.amount { text-align: right; font-weight: bold; white-space: nowrap; }

        .stamp-paid {
            border: 3px solid #16a34a;
            color: #16a34a;
            font-weight: bold;
            font-size: 14px;
            text-align: center;
            padding: 4px 8px;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 8px auto;
            display: inline-block;
            transform: rotate(-5deg);
        }

        .stamp-unpaid {
            border: 3px solid #dc2626;
            color: #dc2626;
            font-weight: bold;
            font-size: 14px;
            text-align: center;
            padding: 4px 8px;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 8px auto;
            display: inline-block;
            transform: rotate(-5deg);
        }

        .footer { margin-top: 8px; text-align: center; font-size: 9px; color: #666; }
        .signature-area { margin-top: 10px; }
        .signature-area .sig-box { display: inline-block; width: 48%; text-align: center; }

        @media print {
            body { width: 80mm; }
            @page { size: 80mm auto; margin: 3mm; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Phòng Khám CareBook</h1>
        <p>PHIẾU CHỈ ĐỊNH CẬN LÂM SÀNG</p>
        <p>In ngày: {{ now()->format('H:i d/m/Y') }}</p>
    </div>

    <div class="divider-solid"></div>

    <div class="section">
        <table>
            <tr>
                <td class="label">Mã LH:</td>
                <td class="value">{{ $appointment->appointment_code }}</td>
            </tr>
            <tr>
                <td class="label">Bệnh nhân:</td>
                <td class="value">{{ $appointment->patientProfile->full_name }}</td>
            </tr>
            <tr>
                <td class="label">Mã BN:</td>
                <td class="value">{{ $appointment->patientProfile->patient_code }}</td>
            </tr>
            <tr>
                <td class="label">Ngày khám:</td>
                <td class="value">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m/Y') }}</td>
            </tr>
            @if($originVisit)
            <tr>
                <td class="label">Bác sĩ chỉ định:</td>
                <td class="value">{{ $originVisit->doctorProfile->user->full_name ?? '—' }}</td>
            </tr>
            @endif
        </table>
    </div>

    <div class="divider"></div>

    <div class="section">
        <p class="section-title">Danh sách phòng được chỉ định</p>
        <table class="visit-table">
            <thead>
                <tr>
                    <th style="width: 55%">Phòng / Dịch vụ</th>
                    <th style="width: 20%" class="center">TT</th>
                    <th style="width: 25%; text-align:right">Chi phí</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subVisits as $visit)
                <tr>
                    <td>
                        {{ $visit->room->name ?? 'Dịch vụ #' . $visit->id }}
                        @if($visit->findings)
                        <br><span style="font-size:9px; color:#555">{{ Str::limit($visit->findings, 40) }}</span>
                        @endif
                    </td>
                    <td class="center">
                        @if($visit->payment_status === 'paid') ✓
                        @else ○
                        @endif
                    </td>
                    <td class="amount">{{ number_format($visit->payment_amount, 0, ',', '.') }}đ</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" style="text-align:center; padding:8px 0; font-style:italic; color:#888">
                        Không có chỉ định
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="divider"></div>

    <table>
        <tr>
            <td class="label">Tổng chi phí:</td>
            <td class="value" style="text-align:right">{{ number_format($summary['total_amount'], 0, ',', '.') }}đ</td>
        </tr>
        @if($summary['insurance_covers'] > 0)
        <tr>
            <td class="label">BHYT ({{ $summary['insurance_rate'] * 100 }}%):</td>
            <td class="value" style="text-align:right">-{{ number_format($summary['insurance_covers'], 0, ',', '.') }}đ</td>
        </tr>
        @endif
        <tr>
            <td class="label bold">BN cần trả:</td>
            <td class="value bold" style="text-align:right; font-size:13px">{{ number_format($summary['patient_pays'], 0, ',', '.') }}đ</td>
        </tr>
    </table>

    <div class="divider-solid"></div>

    <div class="center" style="margin: 6px 0">
        @if($allPaid)
            <span class="stamp-paid">✓ Đã Thanh Toán</span>
        @else
            <span class="stamp-unpaid">Chưa Thanh Toán</span>
        @endif
    </div>

    @if(!$allPaid)
    <p class="center" style="font-size:9px; color:#dc2626; margin-bottom:4px">
        Bệnh nhân cần thanh toán trước khi đến các phòng khám.
    </p>
    @endif

    <div class="divider"></div>

    <div class="signature-area">
        <span class="sig-box">
            <p style="font-size:9px">Bác sĩ chỉ định</p>
            <p style="font-size:9px; margin-top:20px">{{ $originVisit?->doctorProfile?->user?->full_name ?? '___________' }}</p>
        </span>
        <span class="sig-box">
            <p style="font-size:9px">Thu ngân xác nhận</p>
            <p style="font-size:9px; margin-top:20px">___________</p>
        </span>
    </div>

    <div class="footer">
        <div class="divider"></div>
        <p>CareBook · {{ config('app.url') }}</p>
        <p>Giữ phiếu này để trình tại từng phòng khám</p>
    </div>

    <script>window.onload = function() { window.print(); }</script>
</body>
</html>
