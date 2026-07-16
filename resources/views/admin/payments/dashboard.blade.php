<x-layouts.admin title="Quản lý Thanh toán">
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Bảng điều khiển</h2>
                <p class="text-gray-500 mt-1">Tổng quan tài chính và báo cáo doanh thu</p>
            </div>
            <div class="flex gap-2 flex-wrap">
                {{-- Filter khoảng ngày --}}
                <form method="GET" class="flex gap-2 items-center flex-wrap">
                    <input type="date" name="from" value="{{ $from->format('Y-m-d') }}"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <span class="text-gray-400 text-sm">→</span>
                    <input type="date" name="to" value="{{ $to->format('Y-m-d') }}"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                        Lọc
                    </button>
                </form>
                <a href="{{ route('admin.payments.export-csv', ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]) }}"
                   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm flex items-center gap-2 transition-colors">
                    <i class="fa-solid fa-file-csv"></i> Xuất CSV
                </a>
                <a href="{{ route('admin.payments.print-report', ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]) }}"
                   target="_blank"
                   class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm flex items-center gap-2 transition-colors">
                    <i class="fa-solid fa-file-pdf"></i> In báo cáo (PDF)
                </a>
            </div>
        </div>

        <div class="mt-6 border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
                <a href="{{ route('admin.dashboard') }}"
                   class="{{ request()->routeIs('admin.dashboard') ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm">
                    <i class="fa-solid fa-chart-pie mr-2"></i> Tổng quan
                </a>
                <a href="{{ route('admin.payments.dashboard') }}"
                   class="{{ request()->routeIs('admin.payments.dashboard') ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm">
                    <i class="fa-solid fa-money-bill-wave mr-2"></i> Tài chính & Thanh toán
                </a>
            </nav>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
    @endif

    {{-- ── Thẻ Thống kê ─────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Doanh thu</p>
            <p class="text-2xl font-bold text-green-600">{{ number_format($totalRevenue) }}đ</p>
            <p class="text-xs text-gray-400 mt-1">{{ $from->format('d/m') }} — {{ $to->format('d/m/Y') }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Đã hoàn trả</p>
            <p class="text-2xl font-bold text-red-500">{{ number_format(abs($totalRefunded)) }}đ</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Chờ thu</p>
            <p class="text-2xl font-bold text-yellow-600">{{ number_format($totalPending) }}đ</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Đối soát SePay</p>
            <p class="text-2xl font-bold text-blue-600">{{ $reconciliationRate }}%</p>
            <p class="text-xs text-gray-400 mt-1">{{ $matchedTxns }}/{{ $totalSepayTxns }} giao dịch khớp</p>
        </div>
    </div>

    {{-- ── Hàng 2: Biểu đồ + Phương thức ──────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- Biểu đồ doanh thu theo ngày --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <h3 class="font-semibold text-gray-800 mb-4">Doanh thu theo ngày</h3>
            <canvas id="revenueChart" height="120"></canvas>
        </div>

        {{-- Phương thức thanh toán --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <h3 class="font-semibold text-gray-800 mb-4">Theo phương thức</h3>
            @php
                $methodLabels = ['qr' => 'QR VietQR', 'cash' => 'Tiền mặt', 'insurance' => 'BHYT', 'waived' => 'Miễn phí'];
                $methodColors = ['qr' => 'blue', 'cash' => 'green', 'insurance' => 'purple', 'waived' => 'gray'];
            @endphp
            <div class="space-y-3">
                @forelse($byMethod as $method => $total)
                    @php $color = $methodColors[$method] ?? 'gray'; @endphp
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">{{ $methodLabels[$method] ?? $method }}</span>
                            <span class="font-semibold">{{ number_format($total) }}đ</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2">
                            @php $pct = $totalRevenue > 0 ? min(100, round($total / $totalRevenue * 100)) : 0; @endphp
                            <div class="bg-{{ $color }}-500 h-2 rounded-full" style="width: {{ $pct }}%;"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-400 text-sm">Chưa có dữ liệu.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ── Hàng 3: Cần xử lý + Hoàn tiền chờ duyệt ──────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Needs Review --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800">
                    Giao dịch cần xử lý
                    @if($needsReviewCount > 0)
                        <span class="ml-2 bg-red-100 text-red-700 text-xs font-bold px-2 py-0.5 rounded-full">{{ $needsReviewCount }}</span>
                    @endif
                </h3>
                <a href="{{ route('admin.payments.needs-review') }}" class="text-blue-600 text-sm hover:underline">Xem tất cả →</a>
            </div>
            <ul class="divide-y divide-gray-100">
                @forelse($needsReviewPayments as $p)
                <li class="px-5 py-3 flex items-center justify-between gap-3">
                    <div>
                        <div class="text-sm font-medium text-gray-900">{{ $p->appointment?->patientProfile?->full_name ?? 'N/A' }}</div>
                        <div class="text-xs text-gray-500">{{ $p->appointment?->appointment_code }} — {{ $p->paid_at?->format('d/m/Y H:i') }}</div>
                        <div class="text-xs text-gray-400 mt-0.5 truncate max-w-xs">{{ $p->note }}</div>
                    </div>
                    <span class="text-sm font-bold text-orange-600 shrink-0">{{ number_format($p->amount) }}đ</span>
                </li>
                @empty
                <li class="px-5 py-6 text-center text-gray-400 text-sm">
                    <i class="fa-solid fa-check-circle text-2xl mb-2 block text-green-300"></i>Không có giao dịch nào cần xử lý.
                </li>
                @endforelse
            </ul>
        </div>

        {{-- Hoàn tiền chờ duyệt --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800">
                    Yêu cầu hoàn tiền
                    @if($pendingRefundCount > 0)
                        <span class="ml-2 bg-yellow-100 text-yellow-700 text-xs font-bold px-2 py-0.5 rounded-full">{{ $pendingRefundCount }}</span>
                    @endif
                </h3>
                <a href="{{ route('admin.payments.refunds') }}" class="text-blue-600 text-sm hover:underline">Xem tất cả →</a>
            </div>
            <ul class="divide-y divide-gray-100">
                @forelse($pendingRefunds as $r)
                <li class="px-5 py-3 flex items-center justify-between gap-3">
                    <div>
                        <div class="text-sm font-medium text-gray-900">{{ $r->appointment?->patientProfile?->full_name ?? 'N/A' }}</div>
                        <div class="text-xs text-gray-500">Yêu cầu bởi: {{ $r->requestedBy?->name }} — {{ $r->created_at->format('d/m/Y') }}</div>
                        <div class="text-xs text-gray-400 mt-0.5">{{ $r->reason }}</div>
                    </div>
                    <span class="text-sm font-bold text-red-600 shrink-0">{{ number_format($r->amount) }}đ</span>
                </li>
                @empty
                <li class="px-5 py-6 text-center text-gray-400 text-sm">
                    <i class="fa-solid fa-check-circle text-2xl mb-2 block text-green-300"></i>Không có yêu cầu hoàn tiền nào.
                </li>
                @endforelse
            </ul>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        const ctx = document.getElementById('revenueChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($chartLabels),
                datasets: [{
                    label: 'Doanh thu (đ)',
                    data: @json($chartData),
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59,130,246,0.08)',
                    borderWidth: 2,
                    pointBackgroundColor: '#3b82f6',
                    pointRadius: 4,
                    tension: 0.4,
                    fill: true,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => new Intl.NumberFormat('vi-VN').format(ctx.raw) + 'đ'
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: v => new Intl.NumberFormat('vi-VN', {notation:'compact'}).format(v) + 'đ'
                        }
                    }
                }
            }
        });
    </script>
    @endpush
</x-layouts.admin>
