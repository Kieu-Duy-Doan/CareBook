<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn Thuốc - {{ $appointment->appointment_code }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 13px;
            color: #1a1a1a;
            background: #fff;
            padding: 20mm 15mm;
        }

        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; }
        .header-left h1 { font-size: 16px; font-weight: bold; text-transform: uppercase; }
        .header-left p { font-size: 11px; color: #555; }
        .header-right { text-align: right; }
        .header-right .doc-title { font-size: 18px; font-weight: bold; text-align: center; margin-bottom: 4px; }
        .header-right p { font-size: 11px; color: #555; }

        .divider { border-top: 1.5px solid #1a1a1a; margin: 10px 0; }
        .divider-thin { border-top: 1px dashed #999; margin: 8px 0; }

        .patient-info { display: grid; grid-template-columns: 1fr 1fr; gap: 4px 20px; margin: 10px 0; }
        .patient-info .row { display: flex; gap: 6px; }
        .patient-info .label { color: #555; min-width: 80px; }
        .patient-info .value { font-weight: bold; }

        .diagnosis-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px 14px; margin: 10px 0; }
        .diagnosis-box h3 { font-size: 12px; text-transform: uppercase; color: #64748b; margin-bottom: 6px; letter-spacing: 0.5px; }
        .diagnosis-box p { font-size: 13px; }

        .prescription-title { font-size: 15px; font-weight: bold; text-transform: uppercase; margin: 14px 0 8px; text-align: center; letter-spacing: 1px; }

        table { width: 100%; border-collapse: collapse; }
        table thead th { background: #1e3a5f; color: #fff; padding: 7px 10px; font-size: 12px; text-align: left; }
        table thead th:last-child { text-align: right; }
        table tbody tr:nth-child(even) { background: #f8f9fa; }
        table tbody td { padding: 7px 10px; font-size: 13px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        table tbody td.number { text-align: center; }
        table tbody td.amount { text-align: right; font-weight: bold; }

        .total-section { margin-top: 12px; display: flex; justify-content: flex-end; }
        .total-box { border: 1.5px solid #1a1a1a; padding: 10px 20px; min-width: 200px; }
        .total-box .row { display: flex; justify-content: space-between; gap: 30px; padding: 3px 0; font-size: 13px; }
        .total-box .row.grand { border-top: 1px solid #1a1a1a; margin-top: 4px; padding-top: 6px; font-size: 15px; font-weight: bold; }

        .payment-stamp { margin-top: 14px; text-align: center; }
        .stamp { display: inline-block; border: 2.5px solid; padding: 5px 20px; border-radius: 4px;
                 font-size: 14px; font-weight: bold; text-transform: uppercase; letter-spacing: 2px;
                 transform: rotate(-5deg); }
        .stamp.paid { border-color: #16a34a; color: #16a34a; }
        .stamp.unpaid { border-color: #dc2626; color: #dc2626; }

        .notes-box { margin-top: 10px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; padding: 8px 12px; }
        .notes-box h4 { font-size: 11px; text-transform: uppercase; color: #92400e; margin-bottom: 4px; }

        .signatures { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px; }
        .sig-box { text-align: center; }
        .sig-box p.title { font-size: 12px; font-style: italic; color: #555; }
        .sig-box p.name { font-size: 13px; font-weight: bold; margin-top: 50px; }

        .footer { margin-top: 20px; text-align: center; font-size: 10px; color: #888; }

        @media print {
            body { padding: 10mm 12mm; }
            @page { size: A4; margin: 0; }
        }
    </style>
</head>
<body>

<div class="header">
    <div class="header-left">
        <h1>CareBook Medical</h1>
        <p>Phòng khám đa khoa CareBook</p>
        <p>{{ config('app.url') }}</p>
    </div>
    <div class="header-right">
        <p class="doc-title">ĐƠN THUỐC</p>
        <p>Mã LH: <strong>{{ $appointment->appointment_code }}</strong></p>
        <p>Ngày in: {{ now()->format('H:i d/m/Y') }}</p>
    </div>
</div>

<div class="divider"></div>

<div class="patient-info">
    <div class="row">
        <span class="label">Họ và tên:</span>
        <span class="value">{{ $appointment->patientProfile->full_name }}</span>
    </div>
    <div class="row">
        <span class="label">Mã bệnh nhân:</span>
        <span class="value">{{ $appointment->patientProfile->patient_code }}</span>
    </div>
    <div class="row">
        <span class="label">Ngày sinh:</span>
        <span class="value">{{ $appointment->patientProfile->date_of_birth ? \Carbon\Carbon::parse($appointment->patientProfile->date_of_birth)->format('d/m/Y') : '—' }}</span>
    </div>
    <div class="row">
        <span class="label">Giới tính:</span>
        <span class="value">{{ $appointment->patientProfile->gender === 'male' ? 'Nam' : ($appointment->patientProfile->gender === 'female' ? 'Nữ' : '—') }}</span>
    </div>
    <div class="row">
        <span class="label">Số điện thoại:</span>
        <span class="value">{{ $appointment->patientProfile->phone ?? '—' }}</span>
    </div>
    <div class="row">
        <span class="label">BHYT:</span>
        <span class="value">{{ $appointment->patientProfile->health_insurance_number ?? 'Không có' }}</span>
    </div>
</div>

<div class="divider-thin"></div>

@if($appointment->medicalRecord)
<div class="diagnosis-box">
    <h3>Chẩn đoán / Kết luận</h3>
    <p>{{ $appointment->medicalRecord->diagnosis ?? $appointment->reason ?? '—' }}</p>
</div>
@endif

@if($prescription && count($prescription->items ?? []) > 0)
<p class="prescription-title">Danh Sách Thuốc</p>
<table>
    <thead>
        <tr>
            <th style="width:5%">#</th>
            <th style="width:35%">Tên thuốc</th>
            <th style="width:20%">Liều dùng</th>
            <th style="width:15%">Số lượng</th>
            <th style="width:10%">Đơn vị</th>
            <th style="width:15%">Đơn giá</th>
        </tr>
    </thead>
    <tbody>
        @foreach($prescription->items as $index => $item)
        <tr>
            <td class="number">{{ $index + 1 }}</td>
            <td>
                <strong>{{ $item['name'] ?? '—' }}</strong>
                @if(!empty($item['note']))
                <br><span style="font-size:11px; color:#555; font-style:italic">{{ $item['note'] }}</span>
                @endif
            </td>
            <td>{{ $item['dosage'] ?? '—' }}</td>
            <td class="number">{{ $item['quantity'] ?? '—' }}</td>
            <td>{{ $item['unit'] ?? '—' }}</td>
            <td class="amount">{{ !empty($item['price']) ? number_format($item['price'], 0, ',', '.') . 'đ' : '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="total-section">
    <div class="total-box">
        @if($summary['insurance_covers'] > 0)
        <div class="row">
            <span>Tổng tiền thuốc:</span>
            <span>{{ number_format($prescription->payment_amount ?? 0, 0, ',', '.') }}đ</span>
        </div>
        <div class="row">
            <span>BHYT chi trả ({{ $summary['insurance_rate'] * 100 }}%):</span>
            <span>-{{ number_format($summary['insurance_covers'], 0, ',', '.') }}đ</span>
        </div>
        @endif
        <div class="row grand">
            <span>Bệnh nhân trả:</span>
            <span>{{ number_format($prescription->payment_amount ?? 0, 0, ',', '.') }}đ</span>
        </div>
    </div>
</div>

<div class="payment-stamp">
    @if(($prescription->payment_status ?? 'pending') === 'paid')
        <span class="stamp paid">✓ Đã Thanh Toán</span>
    @else
        <span class="stamp unpaid">Chưa Thanh Toán</span>
    @endif
</div>

@elseif($prescription)
<div class="diagnosis-box" style="margin-top:12px">
    <h3>Đơn Thuốc</h3>
    <p style="font-style:italic; color:#555">Không có thuốc được kê trong đơn này.</p>
</div>
@else
<div class="diagnosis-box" style="margin-top:12px; border-color:#fde68a; background:#fffbeb">
    <h3 style="color:#92400e">Lưu ý</h3>
    <p>Bác sĩ chưa tạo đơn thuốc cho lịch hẹn này.</p>
</div>
@endif

@if($prescription?->general_note)
<div class="notes-box" style="margin-top:14px">
    <h4>Lưu ý của bác sĩ:</h4>
    <p>{{ $prescription->general_note }}</p>
</div>
@endif

<div class="divider-thin" style="margin-top:16px"></div>

<div class="signatures">
    <div class="sig-box">
        <p class="title">Bệnh nhân ký nhận</p>
        <p class="name">{{ $appointment->patientProfile->full_name }}</p>
    </div>
    <div class="sig-box">
        <p class="title">Bác sĩ kê đơn</p>
        <p class="name">{{ $appointment->doctorProfile?->user?->full_name ?? '___________' }}</p>
    </div>
</div>

<div class="footer">
    <div class="divider-thin"></div>
    <p>CareBook · Phiếu này có giá trị cấp phát thuốc tại quầy · In ngày {{ now()->format('d/m/Y H:i') }}</p>
</div>

<script>window.onload = function() { window.print(); }</script>
</body>
</html>
