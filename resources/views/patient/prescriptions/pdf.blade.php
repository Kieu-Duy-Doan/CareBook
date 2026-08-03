<!DOCTYPE html>
<html lang="vi">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Đơn Thuốc - {{ $prescription->medicalRecord->appointment->appointment_code }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 13px; line-height: 1.5; color: #333; }
        .container { width: 100%; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { margin: 0; font-size: 20px; text-transform: uppercase; }
        .header p { margin: 5px 0; }
        .title { text-align: center; font-size: 18px; font-weight: bold; margin-bottom: 20px; text-transform: uppercase; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 5px; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th, .items-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .items-table th { background-color: #f9f9f9; text-align: center; }
        .items-table .text-center { text-align: center; }
        .footer { width: 100%; display: table; margin-top: 30px; }
        .footer-col { display: table-cell; width: 50%; text-align: center; }
        .signature { margin-top: 80px; }
        .notes { margin-top: 20px; }
        .notes h4 { margin-bottom: 5px; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>BỆNH VIỆN ĐA KHOA CAREBOOK</h1>
            <p>Địa chỉ: 123 Đường Sức Khỏe, Quận Y Tế, TP. HCM</p>
            <p>Hotline: 1900 1234</p>
        </div>

        <div class="title">ĐƠN THUỐC</div>

        <table class="info-table">
            <tr>
                <td width="20%"><strong>Bệnh nhân:</strong></td>
                <td width="50%">{{ $prescription->medicalRecord->appointment->patientProfile->full_name ?? '—' }}</td>
                <td width="15%"><strong>Mã BN:</strong></td>
                <td width="15%">{{ $prescription->medicalRecord->appointment->patientProfile->patient_code ?? '—' }}</td>
            </tr>
            <tr>
                <td><strong>Chẩn đoán:</strong></td>
                <td colspan="3">{{ $prescription->medicalRecord->diagnosis ?? '—' }}</td>
            </tr>
            <tr>
                <td><strong>Ghi chú chẩn đoán:</strong></td>
                <td colspan="3">{{ $prescription->diagnosis_note ?? '—' }}</td>
            </tr>
            <tr>
                <td><strong>Bác sĩ kê đơn:</strong></td>
                <td>{{ $prescription->medicalRecord->doctorProfile->user->full_name ?? '—' }}</td>
                <td><strong>Ngày kê:</strong></td>
                <td>{{ $prescription->prescribed_date ? \Carbon\Carbon::parse($prescription->prescribed_date)->format('d/m/Y') : now()->format('d/m/Y') }}</td>
            </tr>
        </table>

        <div style="font-weight: bold; margin-bottom: 10px;">Chỉ định dùng thuốc:</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th width="5%" class="text-center">STT</th>
                    <th width="35%">Tên Thuốc</th>
                    <th width="15%" class="text-center">Liều lượng</th>
                    <th width="10%" class="text-center">Số lượng</th>
                    <th width="35%">Cách dùng</th>
                </tr>
            </thead>
            <tbody>
                @if($prescription->items && is_array($prescription->items))
                    @php $stt = 1; @endphp
                    @foreach($prescription->items as $item)
                    <tr>
                        <td class="text-center">{{ $stt++ }}</td>
                        <td>{{ $item['medicine_name'] ?? '—' }}</td>
                        <td class="text-center">{{ $item['dosage'] ?? '—' }}</td>
                        <td class="text-center">{{ $item['quantity'] ?? '—' }}</td>
                        <td>{{ $item['instructions'] ?? '—' }}</td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="5" class="text-center">Không có thuốc</td>
                    </tr>
                @endif
            </tbody>
        </table>

        @if($prescription->general_note)
        <div class="notes">
            <h4>Ghi chú / Dặn dò:</h4>
            <p>{{ $prescription->general_note }}</p>
        </div>
        @endif

        <div class="footer">
            <div class="footer-col">
                <strong>BỆNH NHÂN</strong><br>
                <i>(Ký, ghi rõ họ tên)</i>
                <div class="signature"></div>
                <span>{{ $prescription->medicalRecord->appointment->patientProfile->full_name ?? '—' }}</span>
            </div>
            <div class="footer-col">
                <strong>BÁC SĨ KÊ ĐƠN</strong><br>
                <i>(Ký, ghi rõ họ tên)</i>
                <div class="signature"></div>
                <span>{{ $prescription->medicalRecord->doctorProfile->user->full_name ?? '—' }}</span>
            </div>
        </div>
    </div>
</body>
</html>
