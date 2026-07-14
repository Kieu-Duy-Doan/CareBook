<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Phiếu Tạm Ứng - {{ $appointment->appointment_code }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 13px; line-height: 1.5; color: #333; }
        .container { width: 100%; margin: 0 auto; max-width: 800px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 1px solid #ddd; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 18px; text-transform: uppercase; }
        .title { text-align: center; font-size: 16px; font-weight: bold; margin-bottom: 20px; text-transform: uppercase; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 5px; }
        .amount-box { border: 1px solid #000; padding: 15px; text-align: center; margin-bottom: 20px; background-color: #f9f9f9; }
        .amount-box .amount { font-size: 24px; font-weight: bold; }
        .footer { width: 100%; display: table; margin-top: 30px; }
        .footer-col { display: table-cell; width: 50%; text-align: center; }
        .signature { margin-top: 60px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>BỆNH VIỆN ĐA KHOA CAREBOOK</h1>
            <p>123 Đường Sức Khỏe, Quận Y Tế, TP. HCM | Tel: 1900 1234</p>
        </div>

        <div class="title">PHIẾU BIÊN NHẬN THU TIỀN</div>

        <table class="info-table">
            <tr>
                <td width="30%"><strong>Họ tên người nộp:</strong></td>
                <td width="70%">{{ $appointment->patientProfile->full_name }}</td>
            </tr>
            <tr>
                <td><strong>Mã Bệnh nhân:</strong></td>
                <td>{{ $appointment->patientProfile->patient_code }}</td>
            </tr>
            <tr>
                <td><strong>Lý do nộp:</strong></td>
                <td>Thanh toán chi phí dịch vụ khám bệnh (Mã lịch: {{ $appointment->appointment_code }})</td>
            </tr>
            <tr>
                <td><strong>Ngày nộp:</strong></td>
                <td>{{ now()->format('d/m/Y H:i') }}</td>
            </tr>
        </table>

        <div class="amount-box">
            Số tiền: <span class="amount">{{ number_format($summary['amount_paid'], 0, ',', '.') }} đ</span>
        </div>

        <div class="footer">
            <div class="footer-col">
                <strong>Người Nộp Tiền</strong><br>
                <i>(Ký, ghi rõ họ tên)</i>
                <div class="signature"></div>
                <span>{{ $appointment->patientProfile->full_name }}</span>
            </div>
            <div class="footer-col">
                <strong>Người Thu Tiền</strong><br>
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
