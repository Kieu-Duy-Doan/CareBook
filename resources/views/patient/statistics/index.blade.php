<x-layouts.patient-dashboard title="Thống kê cá nhân" activeMenu="statistics">
    <!-- Filter -->
    <form action="{{ route('patient.statistics.index') }}" method="GET" class="mb-8 grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Từ ngày</label>
            <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-4 py-2 rounded-xl border-slate-200 shadow-sm focus:border-primary focus:ring-primary text-sm">
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Đến ngày</label>
            <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-4 py-2 rounded-xl border-slate-200 shadow-sm focus:border-primary focus:ring-primary text-sm">
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Tháng (năm {{ $year }})</label>
            <select name="month" class="w-full px-4 py-2 rounded-xl border-slate-200 shadow-sm focus:border-primary focus:ring-primary text-sm">
                <option value="">Cả năm</option>
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>Tháng {{ $m }}</option>
                @endfor
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="flex-1 bg-primary text-white font-semibold rounded-xl px-4 py-2 hover:bg-primary-dark transition text-sm">Lọc</button>
            <a href="{{ route('patient.statistics.index') }}" class="bg-slate-100 text-slate-700 font-semibold rounded-xl px-4 py-2 hover:bg-slate-200 transition text-sm text-center flex items-center justify-center">Xóa</a>
        </div>
    </form>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm">
            <div class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-2">Tổng lượt khám</div>
            <div class="text-3xl font-extrabold text-slate-800">{{ $totalAppointments }}</div>
            <div class="mt-4 flex gap-4 text-sm font-medium">
                <span class="text-emerald-600"><i class="fa-solid fa-check-circle mr-1"></i> {{ $completedAppointments }} hoàn thành</span>
                <span class="text-rose-600"><i class="fa-solid fa-xmark-circle mr-1"></i> {{ $cancelledAppointments }} đã huỷ</span>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm">
            <div class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-2">Tổng chi tiêu</div>
            <div class="text-3xl font-extrabold text-slate-800">{{ number_format($totalPaid, 0, ',', '.') }}đ</div>
            <div class="mt-4 text-sm font-medium text-emerald-600">
                <i class="fa-solid fa-shield-heart mr-1"></i> BHYT đã trả: {{ number_format($totalBHYT, 0, ',', '.') }}đ
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm">
            <div class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-2">Hình thức thanh toán</div>
            <div class="space-y-3 mt-4">
                <div class="flex justify-between items-center text-sm">
                    <span class="font-medium text-slate-600"><i class="fa-solid fa-money-bill-wave text-emerald-500 mr-2"></i> Tiền mặt</span>
                    <span class="font-bold text-slate-800">{{ number_format($cashPaid, 0, ',', '.') }}đ</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="font-medium text-slate-600"><i class="fa-solid fa-qrcode text-blue-500 mr-2"></i> Quét mã QR</span>
                    <span class="font-bold text-slate-800">{{ number_format($qrPaid, 0, ',', '.') }}đ</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm h-96 flex flex-col">
            <h3 class="text-lg font-bold text-slate-800 mb-4">Biểu đồ Lượt Khám & Chi Tiêu</h3>
            <div class="relative w-full flex-1 min-h-0">
                <canvas id="trendChart"></canvas>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm h-96 flex flex-col">
            <h3 class="text-lg font-bold text-slate-800 mb-4">Tỷ lệ Hình thức thanh toán</h3>
            <div class="relative w-full flex-1 min-h-0 flex justify-center">
                <canvas id="paymentMethodChart"></canvas>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Trend Chart
            const ctxTrend = document.getElementById('trendChart').getContext('2d');
            new Chart(ctxTrend, {
                type: 'line',
                data: {
                    labels: @json($chartLabels),
                    datasets: [
                        {
                            label: 'Lượt khám',
                            data: @json($appointmentData),
                            borderColor: '#3b82f6', // blue-500
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            borderWidth: 2,
                            tension: 0.3,
                            fill: true,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Chi tiêu (đ)',
                            data: @json($revenueData),
                            borderColor: '#10b981', // emerald-500
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            borderWidth: 2,
                            tension: 0.3,
                            fill: false,
                            yAxisID: 'y1'
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
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Lượt khám'
                            },
                            ticks: {
                                stepSize: 1
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Chi tiêu (đ)'
                            },
                            grid: {
                                drawOnChartArea: false,
                            }
                        }
                    }
                }
            });

            // Payment Method Chart
            const ctxMethod = document.getElementById('paymentMethodChart').getContext('2d');
            new Chart(ctxMethod, {
                type: 'doughnut',
                data: {
                    labels: @json($paymentMethodChart['labels']),
                    datasets: [{
                        data: @json($paymentMethodChart['data']),
                        backgroundColor: [
                            '#10b981', // emerald-500
                            '#3b82f6'  // blue-500
                        ],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        });
    </script>
    @endpush
</x-layouts.patient-dashboard>
