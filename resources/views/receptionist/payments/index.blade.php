<x-layouts.receptionist>
    <x-slot:title>Quản lý Thanh toán</x-slot:title>

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Thanh toán & Hóa đơn</h2>
            <p class="text-gray-500 mt-1">Quản lý hóa đơn chờ thu và lịch sử thanh toán qua SePay & Tiền mặt</p>
        </div>
    </div>

    <!-- Statistics Dashboard Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-6">
        <!-- Tổng thu hôm nay -->
        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex items-center">
            <div class="h-10 w-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg mr-3.5">
                <i class="fa-solid fa-sack-dollar"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-0.5">Tổng thu hôm nay</p>
                <p class="text-xl font-bold text-gray-950">{{ number_format($totalCollectedToday, 0, ',', '.') }}đ</p>
            </div>
        </div>
        <!-- Thu qua QR SePay -->
        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex items-center">
            <div class="h-10 w-10 rounded-full bg-purple-50 text-purple-600 flex items-center justify-center text-lg mr-3.5">
                <i class="fa-solid fa-qrcode"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-0.5">Thu chuyển khoản (SePay)</p>
                <p class="text-xl font-bold text-gray-950">{{ number_format($qrCollectedToday, 0, ',', '.') }}đ</p>
            </div>
        </div>
        <!-- Chờ thu hôm nay -->
        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex items-center">
            <div class="h-10 w-10 rounded-full bg-orange-50 text-orange-600 flex items-center justify-center text-lg mr-3.5">
                <i class="fa-solid fa-clock"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-0.5">Dư nợ chờ thu</p>
                <p class="text-xl font-bold text-gray-950">{{ number_format($pendingAmountToday, 0, ',', '.') }}đ</p>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="mb-4 border-b border-gray-200">
        <nav class="flex space-x-8" aria-label="Tabs">
            <a href="{{ route('receptionist.payments.index', ['tab' => 'pending']) }}"
                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ $tab === 'pending' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                <i class="fa-solid fa-file-invoice-dollar mr-2"></i> Chờ thanh toán
            </a>
            <a href="{{ route('receptionist.payments.index', ['tab' => 'history']) }}"
                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ $tab === 'history' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                <i class="fa-solid fa-clock-rotate-left mr-2"></i> Lịch sử giao dịch
            </a>
        </nav>
    </div>

    <!-- Filter Form -->
    <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm mb-6">
        <form action="{{ route('receptionist.payments.index') }}" method="GET" class="flex flex-col lg:flex-row gap-4 items-end">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div class="w-full lg:w-48">
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Ngày đặt lịch</label>
                <input type="date" name="date" value="{{ request('date') }}"
                    class="block w-full py-2.5 px-3 border border-gray-200 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 text-sm outline-none bg-gray-50/50">
            </div>
            <div class="w-full lg:flex-1">
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Tìm kiếm Lịch hẹn / Bệnh nhân</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nhập mã APT... hoặc tên bệnh nhân..."
                    class="block w-full py-2.5 px-3 border border-gray-200 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 text-sm outline-none bg-gray-50/50">
            </div>
            @if($tab === 'history')
            <div class="w-full lg:w-36">
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Phương thức</label>
                <select name="method"
                    class="block w-full py-2.5 px-3 border border-gray-200 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 text-sm outline-none bg-gray-50/50">
                    <option value="">Tất cả</option>
                    <option value="cash" {{ request('method') === 'cash' ? 'selected' : '' }}>Tiền mặt</option>
                    <option value="qr" {{ request('method') === 'qr' ? 'selected' : '' }}>QR (SePay)</option>
                </select>
            </div>
            @endif
            <div class="flex gap-2 shrink-0 w-full lg:w-auto">
                <a href="{{ route('receptionist.payments.index', ['tab' => $tab]) }}"
                    class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-semibold transition-colors flex-1 lg:flex-none text-center">
                    Đặt lại
                </a>
                <button type="submit"
                    class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold transition-colors flex-1 lg:flex-none">
                    Lọc dữ liệu
                </button>
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Mã Lịch Hẹn</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Bệnh nhân</th>
                        <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Phí Dịch vụ (tạm tính)</th>
                        <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Trạng thái</th>
                        <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($appointments as $appointment)
                        @php
                            if ($tab === 'pending') {
                                $totalFee = $appointment->clinicalVisits->where('payment_status', 'pending')->sum('payment_amount');
                                $statusLabel = 'Chờ thanh toán';
                                $statusColor = 'bg-orange-50 text-orange-850 border border-orange-100';
                            } else {
                                $totalFee = $appointment->payments->where('status', 'completed')->sum('amount');
                                
                                $hasOverpayment = $appointment->payments->where('status', 'completed')->filter(function ($payment) {
                                    return str_contains($payment->note ?? '', 'Chuyển dư');
                                })->isNotEmpty();

                                if ($hasOverpayment) {
                                    $statusLabel = 'Có khoản chuyển dư';
                                    $statusColor = 'bg-amber-50 text-amber-800 border border-amber-200';
                                } else {
                                    $statusLabel = 'Đã thanh toán';
                                    $statusColor = 'bg-emerald-50 text-emerald-850 border border-emerald-100';
                                }
                            }
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <!-- Mã Lịch Hẹn -->
                            <td class="px-6 py-4 whitespace-nowrap font-mono text-sm font-bold text-gray-700">
                                {{ $appointment->appointment_code }}
                                <div class="text-[10px] text-gray-400 font-semibold mt-1">{{ $appointment->created_at->format('H:i d/m/Y') }}</div>
                            </td>
                            <!-- Bệnh nhân -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-950">
                                    {{ $appointment->patientProfile->full_name ?? '—' }}
                                </div>
                                <div class="text-xs text-gray-500 mt-1 font-mono">
                                    Mã BN: {{ $appointment->patientProfile->patient_code ?? '—' }}
                                </div>
                            </td>
                            <!-- Tiền -->
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600 font-bold">
                                {{ number_format($totalFee, 0, ',', '.') }}đ
                            </td>
                            
                            <!-- Trạng thái -->
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusColor }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            
                            <!-- Thao tác -->
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold">
                                @if($tab === 'pending')
                                    <a href="{{ route('receptionist.payments.create', $appointment->id) }}"
                                        class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm">
                                        <i class="fa-solid fa-qrcode mr-1.5"></i> Thu tiền
                                    </a>
                                @else
                                    <a href="{{ route('receptionist.payments.show', $appointment->id) }}"
                                        class="inline-flex items-center px-3 py-2 bg-gray-50 border border-gray-250 text-gray-700 hover:bg-gray-100 rounded-lg text-xs font-semibold transition-all">
                                        <i class="fa-solid fa-eye mr-1.5 text-gray-400"></i> Xem chi tiết
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="h-16 w-16 bg-gray-50 rounded-full flex items-center justify-center mb-4 border border-gray-100">
                                        <i class="fa-solid fa-file-invoice-dollar text-2xl text-gray-400"></i>
                                    </div>
                                    <h3 class="text-lg font-bold text-gray-900">Không tìm thấy bản ghi nào</h3>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($appointments->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-white">
                {{ $appointments->links() }}
            </div>
        @endif
    </div>
</x-layouts.receptionist>
