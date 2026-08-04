<x-layouts.receptionist>
    <x-slot:title>Bảng điều khiển & Thống kê</x-slot:title>

    <div class="mb-6 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Bảng điều khiển & Thống kê</h2>
            <p class="text-gray-500 text-sm mt-1">Xin chào, {{ Auth::user()->full_name }}! Tổng hợp số liệu theo thời gian tùy chọn.</p>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-3">
            <form action="{{ route('receptionist.dashboard') }}" method="GET" class="flex flex-col sm:flex-row gap-3 bg-white p-2 rounded-lg shadow-sm border border-gray-100">
                <div class="flex items-center gap-2">
                    <label class="text-sm text-gray-600 font-medium">Từ ngày:</label>
                    <input type="date" name="start_date" value="{{ $startDate ?? '' }}" class="text-sm border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-sm text-gray-600 font-medium">Đến ngày:</label>
                    <input type="date" name="end_date" value="{{ $endDate ?? '' }}" class="text-sm border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 rounded-md text-sm font-medium transition-colors">
                    <i class="fa-solid fa-filter mr-1"></i> Lọc
                </button>
            </form>
            <a href="{{ route('receptionist.reports.export-csv', ['start_date' => $startDate ?? '', 'end_date' => $endDate ?? '']) }}"
                class="inline-flex items-center gap-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg shadow-sm text-sm font-medium transition-colors">
                <i class="fa-solid fa-file-csv text-emerald-600"></i>
                Xuất CSV
            </a>
        </div>
    </div>

    <!-- Thống kê Lịch hẹn -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <!-- Tổng lịch hẹn hôm nay -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center">
            <div class="h-10 w-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-lg mr-3">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 mb-0.5">Tổng lịch hẹn</p>
                <p class="text-xl font-bold text-gray-900">{{ $stats['total_appointments_today'] }}</p>
            </div>
        </div>

        <!-- Chờ duyệt (Pending) -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center">
            <div class="h-10 w-10 rounded-full bg-yellow-50 text-yellow-600 flex items-center justify-center text-lg mr-3">
                <i class="fa-solid fa-clock"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 mb-0.5">Chờ tiếp nhận</p>
                <p class="text-xl font-bold text-gray-900">{{ $stats['pending_appointments'] }}</p>
            </div>
        </div>

        <!-- Đã Check-in -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center">
            <div class="h-10 w-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg mr-3">
                <i class="fa-solid fa-check-double"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 mb-0.5">Đã Check-in</p>
                <p class="text-xl font-bold text-gray-900">{{ $stats['checked_in_today'] }}</p>
            </div>
        </div>

        <!-- Đến muộn -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center">
            <div class="h-10 w-10 rounded-full bg-orange-50 text-orange-600 flex items-center justify-center text-lg mr-3">
                <i class="fa-solid fa-user-clock"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 mb-0.5">Đến muộn</p>
                <p class="text-xl font-bold text-gray-900">{{ $stats['late_today'] }}</p>
            </div>
        </div>

        <!-- Đã Hủy -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center">
            <div class="h-10 w-10 rounded-full bg-red-50 text-red-600 flex items-center justify-center text-lg mr-3">
                <i class="fa-solid fa-ban"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 mb-0.5">Đã hủy</p>
                <p class="text-xl font-bold text-gray-900">{{ $stats['cancelled_today'] }}</p>
            </div>
        </div>
    </div>

    <!-- Thống kê CLS & Thanh toán -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center">
            <div class="h-10 w-10 rounded-full bg-purple-50 text-purple-600 flex items-center justify-center text-lg mr-3">
                <i class="fa-solid fa-users"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 mb-0.5">Đang chờ khám</p>
                <p class="text-xl font-bold text-gray-900">{{ $stats['visits_waiting'] }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center">
            <div class="h-10 w-10 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg mr-3">
                <i class="fa-solid fa-stethoscope"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 mb-0.5">Đang khám</p>
                <p class="text-xl font-bold text-gray-900">{{ $stats['visits_in_progress'] }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center">
            <div class="h-10 w-10 rounded-full bg-green-50 text-green-600 flex items-center justify-center text-lg mr-3">
                <i class="fa-solid fa-file-invoice-dollar"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 mb-0.5">Hóa đơn chờ thu</p>
                <p class="text-xl font-bold text-gray-900">{{ $stats['pending_payments'] }}</p>
            </div>
        </div>
    </div>

    <!-- KPIs Báo cáo -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center">
            <div class="h-12 w-12 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xl mr-4">
                <i class="fa-solid fa-user-check"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Số Check-in</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($totalCheckins ?? 0) }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center">
            <div class="h-12 w-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl mr-4">
                <i class="fa-solid fa-money-bill-trend-up"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Tổng Doanh thu</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($totalRevenue ?? 0) }} ₫</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center">
            <div class="h-12 w-12 rounded-full bg-orange-50 text-orange-600 flex items-center justify-center text-xl mr-4">
                <i class="fa-solid fa-money-bill-wave"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Tiền mặt</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($cashRevenue ?? 0) }} ₫</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center">
            <div class="h-12 w-12 rounded-full bg-purple-50 text-purple-600 flex items-center justify-center text-xl mr-4">
                <i class="fa-solid fa-qrcode"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Chuyển khoản QR</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($qrRevenue ?? 0) }} ₫</p>
            </div>
        </div>
    </div>

    <!-- Biểu đồ và Chi tiết thanh toán -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Biểu đồ -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 lg:col-span-2 flex flex-col">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-900">Biểu đồ Tổng thu</h3>
                <select onchange="updateChartFilter(this.value)" class="text-sm border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500 py-1.5 pl-3 pr-8">
                    <option value="today" {{ $chartFilter == 'today' ? 'selected' : '' }}>Hôm nay</option>
                    <option value="7_days" {{ $chartFilter == '7_days' ? 'selected' : '' }}>7 ngày qua</option>
                    <option value="30_days" {{ $chartFilter == '30_days' ? 'selected' : '' }}>30 ngày qua</option>
                    <option value="this_month" {{ $chartFilter == 'this_month' ? 'selected' : '' }}>Tháng này</option>
                    <option value="last_month" {{ $chartFilter == 'last_month' ? 'selected' : '' }}>Tháng trước</option>
                    <option value="this_year" {{ $chartFilter == 'this_year' ? 'selected' : '' }}>Năm nay</option>
                    <option value="last_year" {{ $chartFilter == 'last_year' ? 'selected' : '' }}>Năm trước</option>
                </select>
            </div>
            <div class="relative flex-1 min-h-[280px]">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- Bảng danh sách chi tiết rút gọn -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col h-[400px]">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-900">Chi tiết thanh toán</h3>
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
                        @if(isset($paymentsDetail))
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
                                    Không có giao dịch nào.
                                </td>
                            </tr>
                            @endforelse
                        @endif
                    </tbody>
                </table>
            </div>
            @if(isset($paymentsDetail) && $paymentsDetail->count() > 15)
            <div class="px-4 py-3 border-t border-gray-100 bg-gray-50 text-center text-xs text-gray-500">
                Hiển thị 15/{{ $paymentsDetail->count() }} giao dịch mới nhất.
            </div>
            @endif
        </div>
    </div>

    <!-- Bệnh nhân chưa Check-in & Phân bổ ca -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Danh sách bệnh nhân -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 lg:col-span-2 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-900">Bệnh nhân chưa Check-in</h3>
                <a href="{{ route('receptionist.appointments.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">Xem tất cả</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-500 uppercase bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 font-medium">Giờ hẹn</th>
                            <th class="px-6 py-3 font-medium">Bệnh nhân</th>
                            <th class="px-6 py-3 font-medium">Bác sĩ</th>
                            <th class="px-6 py-3 font-medium text-right">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($upcomingPatients as $appointment)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-blue-600">{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') }}</div>
                                <div class="text-gray-500 text-xs">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m/Y') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">{{ $appointment->patientProfile->full_name ?? 'N/A' }}</div>
                                <div class="text-gray-500 text-xs">{{ $appointment->patientProfile->phone ?? '' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-700">{{ $appointment->doctorProfile->user->full_name ?? 'Chưa xếp' }}</div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('receptionist.appointments.show', $appointment->id) }}" class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-medium bg-blue-50 text-blue-600 rounded-md hover:bg-blue-100 transition-colors">
                                    Chi tiết
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                Không có lịch hẹn chờ tiếp nhận.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Biểu đồ phân bổ ca theo giờ -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Phân bổ ca theo giờ</h3>
            <div class="relative h-64">
                <canvas id="hourlyChart"></canvas>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function updateChartFilter(value) {
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('chart_filter', value);
            window.location.search = urlParams.toString();
        }

        document.addEventListener("DOMContentLoaded", function() {
            // Biểu đồ tổng thu (từ Báo cáo)
            const revenueCtx = document.getElementById('revenueChart');
            if (revenueCtx) {
                new Chart(revenueCtx.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($chartDates ?? []) !!},
                        datasets: [
                            {
                                label: 'Tiền mặt',
                                data: {!! json_encode($revenueCashData ?? []) !!},
                                backgroundColor: 'rgba(249, 115, 22, 0.1)',
                                borderColor: 'rgba(249, 115, 22, 1)',
                                borderWidth: 2,
                                tension: 0.4,
                                fill: true,
                                pointBackgroundColor: '#fff',
                                pointBorderColor: 'rgba(249, 115, 22, 1)',
                                pointBorderWidth: 2,
                                pointRadius: 3,
                                pointHoverRadius: 5
                            },
                            {
                                label: 'Chuyển khoản QR',
                                data: {!! json_encode($revenueQrData ?? []) !!},
                                backgroundColor: 'rgba(168, 85, 247, 0.1)',
                                borderColor: 'rgba(168, 85, 247, 1)',
                                borderWidth: 2,
                                tension: 0.4,
                                fill: true,
                                pointBackgroundColor: '#fff',
                                pointBorderColor: 'rgba(168, 85, 247, 1)',
                                pointBorderWidth: 2,
                                pointRadius: 3,
                                pointHoverRadius: 5
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
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.parsed.y !== null) {
                                            label += new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(context.parsed.y);
                                        }
                                        return label;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value, index, values) {
                                        if (value === 0) return '0 ₫';
                                        return new Intl.NumberFormat('vi-VN').format(value) + ' ₫';
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Biểu đồ theo giờ (Dashboard cũ)
            const ctx = document.getElementById('hourlyChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($chartLabels ?? []) !!},
                    datasets: [{
                        label: 'Số ca khám',
                        data: {!! json_encode($chartData ?? []) !!},
                        backgroundColor: '#3b82f6',
                        borderRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        });
    </script>
    @endpush
</x-layouts.receptionist>