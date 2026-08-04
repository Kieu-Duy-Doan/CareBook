<x-layouts.receptionist>
    <x-slot:title>Báo cáo Thống kê</x-slot:title>

    <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Báo cáo & Thống kê</h2>
            <p class="text-gray-500 text-sm mt-1">Tổng hợp số lượng check-in và doanh thu đã thu</p>
        </div>
        
        <!-- Filter Form -->
        <div class="flex flex-col sm:flex-row gap-3">
            <form action="{{ route('receptionist.reports.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3 bg-white p-3 rounded-lg shadow-sm border border-gray-100">
                <div class="flex items-center gap-2">
                    <label class="text-sm text-gray-600 font-medium">Từ ngày:</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="text-sm border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-sm text-gray-600 font-medium">Đến ngày:</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="text-sm border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                    <i class="fa-solid fa-filter mr-1"></i> Lọc
                </button>
            </form>
            <a href="{{ route('receptionist.reports.export-csv', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
                class="inline-flex items-center gap-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg shadow-sm text-sm font-medium transition-colors">
                <i class="fa-solid fa-file-csv text-emerald-600"></i>
                Xuất CSV
            </a>
        </div>
    </div>

    <!-- KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center">
            <div class="h-12 w-12 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xl mr-4">
                <i class="fa-solid fa-user-check"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Số Check-in</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($totalCheckins) }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center">
            <div class="h-12 w-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl mr-4">
                <i class="fa-solid fa-money-bill-trend-up"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Tổng Doanh thu</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($totalRevenue) }} ₫</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center">
            <div class="h-12 w-12 rounded-full bg-orange-50 text-orange-600 flex items-center justify-center text-xl mr-4">
                <i class="fa-solid fa-money-bill-wave"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Tiền mặt</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($cashRevenue) }} ₫</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center">
            <div class="h-12 w-12 rounded-full bg-purple-50 text-purple-600 flex items-center justify-center text-xl mr-4">
                <i class="fa-solid fa-qrcode"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Chuyển khoản QR</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($qrRevenue) }} ₫</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Biểu đồ -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 lg:col-span-2">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Biểu đồ Tổng thu theo ngày</h3>
            <div class="relative h-72">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- Bảng danh sách chi tiết rút gọn -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col h-[400px]">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-900">Chi tiết thanh toán gần đây</h3>
            </div>
            <div class="flex-1 overflow-y-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-500 uppercase bg-gray-50 sticky top-0">
                        <tr>
                            <th class="px-4 py-3 font-medium">Khách hàng</th>
                            <th class="px-4 py-3 font-medium text-right">Số tiền</th>
                            <th class="px-4 py-3 font-medium text-center">PT</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($paymentsDetail->take(15) as $payment)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900 truncate max-w-[120px]" title="{{ $payment->appointment->patientProfile->full_name ?? 'N/A' }}">
                                    {{ $payment->appointment->patientProfile->full_name ?? 'N/A' }}
                                </div>
                                <div class="text-xs text-gray-500">{{ $payment->paid_at->format('d/m H:i') }}</div>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-emerald-600">
                                {{ number_format($payment->amount) }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($payment->method == 'cash')
                                    <span class="inline-flex items-center justify-center px-2 py-1 text-[10px] font-bold bg-orange-100 text-orange-700 rounded">
                                        TM
                                    </span>
                                @else
                                    <span class="inline-flex items-center justify-center px-2 py-1 text-[10px] font-bold bg-purple-100 text-purple-700 rounded">
                                        QR
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center text-gray-500">
                                Không có giao dịch nào trong khoảng thời gian này.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($paymentsDetail->count() > 15)
            <div class="px-4 py-3 border-t border-gray-100 bg-gray-50 text-center text-xs text-gray-500">
                Hiển thị 15/{{ $paymentsDetail->count() }} giao dịch mới nhất.
            </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('revenueChart').getContext('2d');
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($dates) !!},
                    datasets: [
                        {
                            label: 'Tiền mặt',
                            data: {!! json_encode($revenueCashData) !!},
                            backgroundColor: 'rgba(249, 115, 22, 0.8)', // orange-500
                            borderColor: 'rgba(249, 115, 22, 1)',
                            borderWidth: 1,
                            borderRadius: 4
                        },
                        {
                            label: 'Chuyển khoản QR',
                            data: {!! json_encode($revenueQrData) !!},
                            backgroundColor: 'rgba(168, 85, 247, 0.8)', // purple-500
                            borderColor: 'rgba(168, 85, 247, 1)',
                            borderWidth: 1,
                            borderRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                boxWidth: 8
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += new Intl.NumberFormat('vi-VN').format(context.parsed.y) + ' ₫';
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            stacked: true,
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return new Intl.NumberFormat('vi-VN', {
                                        notation: "compact",
                                        compactDisplay: "short"
                                    }).format(value);
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
    @endpush
</x-layouts.receptionist>
