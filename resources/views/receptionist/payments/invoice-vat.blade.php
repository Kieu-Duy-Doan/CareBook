<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Hóa Đơn Dịch Vụ - {{ $appointment->appointment_code }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 13px;
            line-height: 1.5;
            color: #333;
        }

        .container {
            width: 100%;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            margin: 0;
            font-size: 20px;
            text-transform: uppercase;
        }

        .header p {
            margin: 5px 0;
        }

        .title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
            text-transform: uppercase;
        }

        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }

        .info-table td {
            padding: 5px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .items-table th {
            background-color: #f9f9f9;
        }

        .items-table .text-right {
            text-align: right;
        }

        .items-table .text-center {
            text-align: center;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .summary-table td {
            padding: 5px;
        }

        .summary-table .text-right {
            text-align: right;
            font-weight: bold;
        }

        .footer {
            width: 100%;
            display: table;
        }

        .footer-col {
            display: table-cell;
            width: 50%;
            text-align: center;
        }

        .signature {
            margin-top: 80px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>BỆNH VIỆN ĐA KHOA CAREBOOK</h1>
            <p>Địa chỉ: 123 Đường Sức Khỏe, Quận Y Tế, TP. HCM</p>
            <p>Mã số thuế: 0123456789 - Hotline: 1900 1234</p>
        </div>

        <div class="title">HÓA ĐƠN DỊCH VỤ Y TẾ (VAT)</div>

        <table class="info-table">
            <tr>
                <td width="20%"><strong>Khách hàng:</strong></td>
                <td width="50%">{{ $appointment->patientProfile->full_name }}</td>
                <td width="15%"><strong>Mã KH:</strong></td>
                <td width="15%">{{ $appointment->patientProfile->patient_code }}</td>
            </tr>
            <tr>
                <td><strong>Mã lịch hẹn:</strong></td>
                <td>{{ $appointment->appointment_code }}</td>
                <td><strong>Ngày in:</strong></td>
                <td>{{ now()->format('d/m/Y H:i') }}</td>
            </tr>
        </table>

        <table class="items-table">
            <thead>
                <tr>
                    <th width="5%" class="text-center">STT</th>
                    <th width="55%">Tên Dịch Vụ</th>
                    <th width="40%" class="text-right">Thành Tiền</th>
                </tr>
            </thead>
            <tbody>
                @foreach($appointment->clinicalVisits as $index => $visit)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $visit->is_origin ? 'Phí Khám Bệnh' : 'Dịch vụ Cận lâm sàng / Khác' }} (Mã: #{{ $visit->id }})</td>
                    <td class="text-right">{{ number_format($visit->payment_amount, 0, ',', '.') }} đ</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table class="summary-table">
            <tr>
                <td width="60%" class="text-right">Tổng chi phí dịch vụ:</td>
                <td width="40%" class="text-right">{{ number_format($summary['total_amount'], 0, ',', '.') }} đ</td>
            </tr>
            <tr>
                <td class="text-right">BHYT chi trả ({{ $summary['insurance_rate'] * 100 }}%):</td>
                <td class="text-right">- {{ number_format($summary['insurance_covers'], 0, ',', '.') }} đ</td>
            </tr>
            <tr>
                <td class="text-right"><strong>BỆNH NHÂN THANH TOÁN:</strong></td>
                <td class="text-right"><strong>{{ number_format($summary['patient_pays'], 0, ',', '.') }} đ</strong></td>
            </tr>
        </table>

        <p><strong>Lịch sử thanh toán:</strong></p>
        <ul>
            @foreach($appointment->payments->where('status', 'completed') as $payment)
            <li>Đã thu: {{ number_format($payment->amount, 0, ',', '.') }} đ ({{ $payment->method == 'cash' ? 'Tiền mặt' : 'Chuyển khoản' }}) lúc {{ $payment->paid_at->format('H:i d/m/Y') }}</li>
            @endforeach
            @foreach($appointment->payments->where('status', 'refunded') as $payment)
            <li>Hoàn tiền: {{ number_format(abs($payment->amount), 0, ',', '.') }} đ lúc {{ $payment->paid_at->format('H:i d/m/Y') }}</li>
            @endforeach
        </ul>

        <div class="footer">
            <div class="footer-col">
                <strong>KHÁCH HÀNG</strong><br>
                <i>(Ký, ghi rõ họ tên)</i>
                <div class="signature"></div>
                <span>{{ $appointment->patientProfile->full_name }}</span>
            </div>
            <div class="footer-col">
                <strong>NGƯỜI LẬP PHIẾU</strong><br>
                <i>(Ký, ghi rõ họ tên)</i>
                <div class="signature"></div>
                <span>{{ auth()->user()->name }}</span>
            </div>
        </div>
    </div>
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>

</html>