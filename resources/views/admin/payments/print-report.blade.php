<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo cáo Doanh thu - {{ $from->format('d/m/Y') }} đến {{ $to->format('d/m/Y') }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0 0 5px 0;
            font-size: 24px;
        }
        .header p {
            margin: 0;
            color: #666;
        }
        .summary {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            background: #f9f9f9;
            padding: 15px;
            border: 1px solid #ddd;
        }
        .summary div {
            text-align: center;
        }
        .summary div h3 {
            margin: 0 0 5px 0;
            font-size: 14px;
            color: #666;
            text-transform: uppercase;
        }
        .summary div p {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .status-completed { color: green; }
        .status-refunded { color: red; }
        .status-needs_review { color: orange; }

        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer;">
            In Báo Cáo
        </button>
    </div>

    <div class="header">
        <h1>BÁO CÁO GIAO DỊCH THANH TOÁN</h1>
        <p>Từ ngày {{ $from->format('d/m/Y') }} đến {{ $to->format('d/m/Y') }}</p>
    </div>

    <div class="summary">
        <div>
            <h3>Tổng giao dịch</h3>
            <p>{{ $payments->count() }}</p>
        </div>
        <div>
            <h3>Tổng doanh thu</h3>
            <p style="color: green;">+{{ number_format($totalRevenue) }}đ</p>
        </div>
        <div>
            <h3>Đã hoàn trả</h3>
            <p style="color: red;">-{{ number_format($totalRefunded) }}đ</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">STT</th>
                <th width="15%">Mã GD</th>
                <th width="12%">Thời gian</th>
                <th width="12%">Mã lịch hẹn</th>
                <th width="20%">Bệnh nhân</th>
                <th width="12%">PT Thanh toán</th>
                <th width="12%" class="text-right">Số tiền (VNĐ)</th>
                <th width="12%">Trạng thái</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $index => $p)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $p->transaction_code }}</td>
                    <td>{{ $p->paid_at?->format('d/m/Y H:i') }}</td>
                    <td>{{ $p->appointment?->appointment_code }}</td>
                    <td>{{ $p->appointment?->patientProfile?->full_name }}</td>
                    <td>
                        @if($p->method === 'qr') QR Code
                        @elseif($p->method === 'cash') Tiền mặt
                        @elseif($p->method === 'insurance') BHYT
                        @else Khác @endif
                    </td>
                    <td class="text-right">{{ number_format($p->amount) }}</td>
                    <td class="status-{{ $p->status }}">
                        @if($p->status === 'completed') Thành công
                        @elseif($p->status === 'refunded') Hoàn tiền
                        @elseif($p->status === 'needs_review') Cần xử lý
                        @else {{ $p->status }} @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">Không có giao dịch nào trong khoảng thời gian này.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 50px; display: flex; justify-content: flex-end;">
        <div style="text-align: center; width: 200px;">
            <p style="margin-bottom: 60px;">Người lập biểu</p>
            <p><strong>{{ auth()->user()->name }}</strong></p>
        </div>
    </div>
</body>
</html>
