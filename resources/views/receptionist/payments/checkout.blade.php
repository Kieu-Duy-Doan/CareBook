<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cổng thanh toán PayOS - Giả lập</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;755;800&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- AlpineJS -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen flex items-center justify-center p-4 sm:p-6" x-data="{
    timeLeft: 900,
    formatTime() {
        let minutes = Math.floor(this.timeLeft / 60);
        let seconds = this.timeLeft % 60;
        return (minutes < 10 ? '0' : '') + minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
    },
    init() {
        setInterval(() => {
            if (this.timeLeft > 0) this.timeLeft--;
        }, 1000);
    }
}">

    <div class="w-full max-w-4xl bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden flex flex-col md:flex-row">
        <!-- Sidebar - Hoá đơn info -->
        <div class="w-full md:w-5/12 bg-slate-900 text-white p-8 flex flex-col justify-between border-b md:border-b-0 md:border-r border-slate-800">
            <div>
                <div class="flex items-center gap-2 mb-8">
                    <div class="h-8 w-8 rounded-lg bg-emerald-500 flex items-center justify-center text-slate-950 font-black text-sm">CB</div>
                    <span class="font-extrabold text-lg tracking-tight">CareBook</span>
                    <span class="bg-emerald-500/20 text-emerald-400 text-[10px] px-2 py-0.5 rounded-full font-bold uppercase tracking-wider">PayOS Gateway</span>
                </div>

                <div class="space-y-6">
                    <div>
                        <span class="text-xs text-slate-400 font-bold uppercase tracking-wider block mb-1">Bệnh nhân</span>
                        <p class="text-base font-bold text-white">{{ $visit->appointment->patientProfile->full_name ?? '—' }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">Mã BN: {{ $visit->appointment->patientProfile->patient_code ?? '—' }}</p>
                    </div>

                    <div>
                        <span class="text-xs text-slate-400 font-bold uppercase tracking-wider block mb-1">Dịch vụ</span>
                        <p class="text-sm font-semibold text-slate-250">Khám {{ $visit->appointment->specialty->name ?? 'Tổng quát' }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">Phòng khám: {{ $visit->room->name ?? '—' }}</p>
                    </div>

                    <div>
                        <span class="text-xs text-slate-400 font-bold uppercase tracking-wider block mb-1">Mã đơn hàng / Giao dịch</span>
                        <p class="text-sm font-mono font-bold text-white">{{ $transactionRef }}</p>
                    </div>
                </div>
            </div>

            <div class="mt-12 pt-6 border-t border-slate-800">
                <span class="text-xs text-slate-400 font-bold uppercase tracking-wider block mb-1">Số tiền thanh toán</span>
                <p class="text-3xl font-black text-emerald-400">{{ number_format($visit->payment_amount, 0, ',', '.') }}đ</p>
            </div>
        </div>

        <!-- Main Panel - QR Mock Code -->
        <div class="flex-1 p-8 flex flex-col justify-between items-center text-center">
            <!-- Header status -->
            <div class="w-full flex items-center justify-between mb-6 pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2 text-left">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                    </span>
                    <span class="text-xs font-semibold text-slate-500">Chờ quét mã thanh toán...</span>
                </div>
                <div class="text-xs font-semibold text-slate-700 bg-slate-105 px-3 py-1 rounded-full flex items-center gap-1.5">
                    <i class="fa-solid fa-clock text-slate-400"></i> Hết hạn sau <span class="font-bold text-red-500" x-text="formatTime()">15:00</span>
                </div>
            </div>

            <!-- VietQR Container -->
            <div class="my-auto space-y-5">
                <div class="relative bg-slate-50 p-6 rounded-2xl border border-slate-200 inline-block shadow-inner">
                    <!-- VietQR Logo top left of QR container -->
                    <div class="absolute top-2 left-2 text-[10px] font-bold text-indigo-700 tracking-wider bg-white border px-1.5 py-0.5 rounded shadow-sm">
                        Viet<span class="text-red-500">QR</span>
                    </div>
                    <!-- Generates beautiful VietQR-like QR code dynamically using free service -->
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=https://vietqr.co/api/generate/MBBank/190367289901/{{ $visit->payment_amount }}/{{ $transactionRef }}?accountName=PHONG%20KHAM%20CAREBOOK" 
                         alt="Mã QR Chuyển khoản"
                         class="w-56 h-56 object-contain rounded-lg">
                </div>

                <div class="max-w-sm mx-auto text-sm space-y-2.5 bg-slate-50 p-4 rounded-2xl border border-slate-100 text-left font-medium">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Ngân hàng thụ hưởng:</span>
                        <span class="text-slate-900 font-bold">MB Bank</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Số tài khoản:</span>
                        <span class="text-slate-900 font-mono font-bold">1903 6728 9901</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Tên tài khoản:</span>
                        <span class="text-slate-900 font-bold uppercase">PHONG KHAM CAREBOOK</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Nội dung chuyển khoản:</span>
                        <span class="text-slate-900 font-mono font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded">{{ $transactionRef }}</span>
                    </div>
                </div>
            </div>

            <!-- Footer actions / Simulator control panel -->
            <div class="w-full mt-8 pt-6 border-t border-slate-100 flex flex-col gap-3">
                <form action="{{ route('receptionist.payments.update', $visit->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <button type="submit" 
                        class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-6 rounded-2xl transition-all shadow-md flex items-center justify-center gap-2">
                        <i class="fa-solid fa-bolt"></i> Giả lập thanh toán thành công
                    </button>
                </form>

                <a href="{{ route('receptionist.payments.create', $visit->id) }}" 
                    class="text-xs font-semibold text-slate-500 hover:text-slate-800 transition-colors">
                    <i class="fa-solid fa-ban"></i> Huỷ giao dịch và quay lại quầy
                </a>
            </div>
        </div>
    </div>

</body>
</html>
