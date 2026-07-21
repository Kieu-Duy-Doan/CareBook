<x-layouts.admin title="Bảng điều khiển">
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Bảng điều khiển</h2>
                <p class="text-gray-500 mt-1">Tổng quan tình hình hoạt động của phòng khám CareBook</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-500">Cập nhật lúc: <span
                        class="font-medium text-gray-900">{{ now()->format('H:i d/m/Y') }}</span></span>
                <button onclick="window.location.reload()"
                    class="p-2 text-gray-500 hover:text-blue-600 bg-white rounded-lg border border-gray-200 shadow-sm transition-colors"
                    title="Làm mới dữ liệu">
                    <i class="fa-solid fa-rotate-right"></i>
                </button>
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

    <!-- Hàng 1: KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 gap-6 mb-8">
        <!-- Card 1: Lịch khám hôm nay -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 relative overflow-hidden group hover:shadow-md transition-shadow">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <i class="fa-regular fa-calendar-check text-6xl text-blue-600"></i>
            </div>
            <div class="flex justify-between items-start mb-4 relative z-10">
                <div>
                    <p class="text-sm font-medium text-gray-500">Lịch khám hôm nay</p>
                    <h3 class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($todayApptCount) }}</h3>
                </div>
                <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
                    <i class="fa-solid fa-notes-medical"></i>
                </div>
            </div>
            <div class="flex items-center text-sm relative z-10">
                <span class="text-gray-500 font-medium text-xs">Ca khám mới</span>
            </div>
        </div>

        <!-- Card 2: Lịch bị hủy hôm nay -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 relative overflow-hidden group hover:shadow-md transition-shadow">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <i class="fa-solid fa-calendar-xmark text-6xl text-red-600"></i>
            </div>
            <div class="flex justify-between items-start mb-4 relative z-10">
                <div>
                    <p class="text-sm font-medium text-gray-500">Bị hủy hôm nay</p>
                    <h3 class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($canceledToday ?? 0) }}</h3>
                </div>
                <div class="w-10 h-10 rounded-full bg-red-50 flex items-center justify-center text-red-600">
                    <i class="fa-solid fa-ban"></i>
                </div>
            </div>
            <div class="flex items-center text-sm relative z-10">
                <span class="text-gray-500 font-medium text-xs">Ca bị hủy/từ chối</span>
            </div>
        </div>

        <!-- Card 3: Tỷ lệ hoàn thành khám -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 relative overflow-hidden group hover:shadow-md transition-shadow">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <i class="fa-solid fa-check-double text-6xl text-emerald-600"></i>
            </div>
            <div class="flex justify-between items-start mb-4 relative z-10">
                <div>
                    <p class="text-sm font-medium text-gray-500">Tỷ lệ hoàn thành (Hôm nay)</p>
                    <h3 class="text-3xl font-bold text-gray-900 mt-1">{{ $completionRate }}%</h3>
                </div>
                <div class="w-10 h-10 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600">
                    <i class="fa-solid fa-check-to-slot"></i>
                </div>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-1.5 mt-2 relative z-10">
                <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $completionRate }}%"></div>
            </div>
            <p class="text-xs text-gray-400 mt-2 relative z-10">{{ $completedToday }} / {{ $todayApptCount }} ca đã khám</p>
        </div>

        <!-- Card 4: Doanh thu hôm nay -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 relative overflow-hidden group hover:shadow-md transition-shadow">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <i class="fa-solid fa-hand-holding-dollar text-6xl text-amber-600"></i>
            </div>
            <div class="flex justify-between items-start mb-4 relative z-10">
                <div>
                    <p class="text-sm font-medium text-gray-500">Doanh thu hôm nay</p>
                    <h3 class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($revenueToday ?? 0) }}đ</h3>
                </div>
                <div class="w-10 h-10 rounded-full bg-amber-50 flex items-center justify-center text-amber-600">
                    <i class="fa-solid fa-coins"></i>
                </div>
            </div>
            <div class="flex items-center text-sm relative z-10">
                <span class="text-gray-500 font-medium">Tổng thu trong ngày</span>
            </div>
        </div>

        <!-- Card 5: Doanh thu tháng này -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 relative overflow-hidden group hover:shadow-md transition-shadow">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <i class="fa-solid fa-vault text-6xl text-indigo-600"></i>
            </div>
            <div class="flex justify-between items-start mb-4 relative z-10">
                <div>
                    <p class="text-sm font-medium text-gray-500">Doanh thu tháng này</p>
                    <h3 class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($revenueThisMonth ?? 0) }}đ</h3>
                </div>
                <div class="w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600">
                    <i class="fa-solid fa-sack-dollar"></i>
                </div>
            </div>
            <div class="flex items-center text-sm relative z-10">
                <span class="text-gray-500 font-medium">Tổng thu trong tháng</span>
            </div>
        </div>

        <!-- Card 6: Bệnh nhân mới -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 relative overflow-hidden group hover:shadow-md transition-shadow">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <i class="fa-solid fa-users text-6xl text-teal-600"></i>
            </div>
            <div class="flex justify-between items-start mb-4 relative z-10">
                <div>
                    <p class="text-sm font-medium text-gray-500">Bệnh nhân mới (Tháng)</p>
                    <h3 class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($newPatientsThisMonth) }}</h3>
                </div>
                <div class="w-10 h-10 rounded-full bg-teal-50 flex items-center justify-center text-teal-600">
                    <i class="fa-solid fa-user-plus"></i>
                </div>
            </div>
            <div class="flex items-center text-sm relative z-10">
                @if ($patientGrowth > 0)
                <span class="text-green-600 font-medium flex items-center bg-green-50 px-2 py-0.5 rounded-full">
                    <i class="fa-solid fa-arrow-trend-up mr-1 text-xs"></i> +{{ round($patientGrowth, 1) }}%
                </span>
                @elseif($patientGrowth < 0)
                    <span class="text-red-600 font-medium flex items-center bg-red-50 px-2 py-0.5 rounded-full">
                    <i class="fa-solid fa-arrow-trend-down mr-1 text-xs"></i> {{ round($patientGrowth, 1) }}%
                    </span>
                    @else
                    <span class="text-gray-500 font-medium flex items-center bg-gray-50 px-2 py-0.5 rounded-full">
                        <i class="fa-solid fa-minus mr-1 text-xs"></i> 0%
                    </span>
                    @endif
                    <span class="text-gray-400 ml-2">so với tháng trước</span>
            </div>
        </div>
    </div>

    <!-- Hàng 2: Biểu đồ thống kê (Pure JS) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Line Chart -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col">
            <div class="flex flex-wrap justify-between items-center mb-6 gap-4">
                <h3 class="text-lg font-bold text-gray-900">Xu hướng Lịch khám</h3>

                <!-- Filter Buttons -->
                <div class="flex items-center gap-2">
                    <form id="chartFilterForm" class="flex items-center border border-gray-200 bg-white rounded-lg overflow-hidden transition-colors shadow-sm">

                        <!-- Chọn Tháng -->
                        <select name="target_month" id="targetMonth" class="px-3 py-1.5 text-sm border-0 focus:ring-0 text-gray-700 bg-transparent w-[110px] cursor-pointer border-r border-gray-200">
                            <option value="all" {{ request('target_month') === 'all' ? 'selected' : '' }}>Cả năm</option>
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ request('target_month', now()->month) == $i ? 'selected' : '' }}>Tháng {{ $i }}</option>
                                @endfor
                        </select>

                        <!-- Chọn Năm -->
                        <select name="target_year" id="targetYear" class="px-3 py-1.5 text-sm border-0 focus:ring-0 text-gray-700 bg-transparent w-[90px] cursor-pointer">
                            @php $currentYear = date('Y'); @endphp
                            @for($i = $currentYear; $i >= $currentYear - 5; $i--)
                            <option value="{{ $i }}" {{ request('target_year', $currentYear) == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </form>
                </div>
            </div>
            <div class="flex-1 min-h-[300px] w-full relative">
                <div id="trendLoading" class="absolute inset-0 flex items-center justify-center bg-white/80 z-10 rounded-xl transition-opacity duration-300">
                    <i class="fa-solid fa-circle-notch fa-spin text-3xl text-blue-600"></i>
                </div>
                <canvas id="trendChart" class="absolute inset-0 w-full h-full"></canvas>
            </div>
        </div>

        <!-- Donut Chart -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col relative overflow-hidden">
            <h3 class="text-lg font-bold text-gray-900 mb-6">Cơ cấu Chuyên khoa</h3>
            <div id="specialtyLoading" class="absolute inset-0 flex items-center justify-center bg-white/80 z-10 transition-opacity duration-300">
                <i class="fa-solid fa-circle-notch fa-spin text-3xl text-blue-600"></i>
            </div>
            <div class="flex-1 flex flex-col items-center justify-center min-h-[300px]">
                <div class="relative w-full h-full max-h-[300px]">
                    <canvas id="specialtyChart" class="w-full h-full"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Hàng 3: Biểu đồ bổ sung -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Peak Hours Bar Chart -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col relative overflow-hidden">
            <h3 class="text-lg font-bold text-gray-900 mb-6">Giờ cao điểm (Tháng)</h3>
            <div id="peakHoursLoading" class="absolute inset-0 flex items-center justify-center bg-white/80 z-10 transition-opacity duration-300">
                <i class="fa-solid fa-circle-notch fa-spin text-3xl text-blue-600"></i>
            </div>
            <div class="flex-1 min-h-[300px] w-full relative">
                <canvas id="peakHoursChart" class="absolute inset-0 w-full h-full"></canvas>
            </div>
        </div>

        <!-- Revenue Method Donut Chart -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col relative overflow-hidden">
            <h3 class="text-lg font-bold text-gray-900 mb-6">Doanh thu theo Phương thức</h3>
            <div id="revenueMethodLoading" class="absolute inset-0 flex items-center justify-center bg-white/80 z-10 transition-opacity duration-300">
                <i class="fa-solid fa-circle-notch fa-spin text-3xl text-blue-600"></i>
            </div>
            <div class="flex-1 flex flex-col items-center justify-center min-h-[300px]">
                <div class="relative w-full h-full max-h-[300px]">
                    <canvas id="revenueMethodChart" class="w-full h-full"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Hàng 3: Bảng dữ liệu -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Bảng 1: Bệnh nhân hôm nay (Chiếm 2 cột) -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-900">Lịch khám Hôm nay</h3>
                <a href="{{ route('admin.appointments.index') }}"
                    class="text-sm font-medium text-blue-600 hover:text-blue-800 transition-colors">Xem tất cả <i
                        class="fa-solid fa-arrow-right text-xs ml-1"></i></a>
            </div>
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-xs uppercase text-gray-500 font-semibold border-b border-gray-100">
                            <th class="px-6 py-3">Giờ khám</th>
                            <th class="px-6 py-3">Bệnh nhân</th>
                            <th class="px-6 py-3">Bác sĩ</th>
                            <th class="px-6 py-3 text-center">Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($todayAppointments as $appt)
                        <tr class="hover:bg-gray-50 transition-colors cursor-pointer"
                            onclick="window.location='{{ route('admin.appointments.show', $appt->id) }}'">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="font-bold text-gray-900">{{ \Carbon\Carbon::parse($appt->appointment_time)->format('H:i') }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">
                                    {{ $appt->patientProfile->full_name ?? 'Khách lẻ' }}
                                </div>
                                <div class="text-xs text-gray-500 mt-0.5">{{ $appt->appointment_code }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div
                                        class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">
                                        {{ substr($appt->doctorProfile->user->name ?? 'BS', 0, 1) }}
                                    </div>
                                    <span
                                        class="text-sm text-gray-700 font-medium">{{ $appt->doctorProfile->user->name ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                @if ($appt->status === 'pending')
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">Đang
                                    chờ</span>
                                @elseif($appt->status === 'confirmed')
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Đã
                                    xác nhận</span>
                                @elseif($appt->status === 'completed')
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Đã
                                    khám xong</span>
                                @else
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Đã
                                    hủy</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-500 text-sm">Không có lịch
                                khám nào trong hôm nay.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Bảng 2: Top Bác sĩ -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-900">Top Bác sĩ (Tháng)</h3>
                <p class="text-xs text-gray-500 mt-1">Dựa trên số ca tiếp nhận</p>
            </div>
            <div class="p-0 flex-1">
                <ul class="divide-y divide-gray-100">
                    @forelse($topDoctors as $index => $item)
                    <li class="p-4 hover:bg-gray-50 transition-colors flex items-center gap-4">
                        <div
                            class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center font-bold {{ $index == 0 ? 'text-yellow-500 bg-yellow-50' : ($index == 1 ? 'text-gray-400 bg-gray-50' : ($index == 2 ? 'text-amber-700 bg-amber-50' : 'text-gray-400')) }}">
                            #{{ $index + 1 }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">
                                {{ $item->doctorProfile->user->name ?? 'Unknown' }}
                            </p>
                            <p class="text-xs text-gray-500 truncate">
                                {{ $item->doctorProfile->specialty->name ?? 'Khoa khám bệnh' }}
                            </p>
                        </div>
                        <div class="text-right">
                            <span class="block text-sm font-bold text-blue-600">{{ $item->total }}</span>
                            <span class="block text-[10px] text-gray-400 uppercase">Ca khám</span>
                        </div>
                    </li>
                    @empty
                    <li class="p-6 text-center text-gray-500 text-sm">Chưa có dữ liệu bác sĩ tháng này.</li>
                    @endforelse
                </ul>
            </div>
        </div>

    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const monthSelect = document.getElementById('targetMonth');
            const yearSelect = document.getElementById('targetYear');

            let trendChartInstance = null;
            let specialtyChartInstance = null;
            let peakHoursChartInstance = null;
            let revenueMethodChartInstance = null;

            const brandColors = [
                '#0ea5e9', '#14b8a6', '#6366f1', '#f59e0b',
                '#ec4899', '#8b5cf6', '#10b981', '#f43f5e'
            ];
            const methodColors = ['#10b981', '#3b82f6', '#8b5cf6'];

            function fetchChartData() {
                document.getElementById('trendLoading').style.opacity = '1';
                document.getElementById('trendLoading').style.visibility = 'visible';
                document.getElementById('specialtyLoading').style.opacity = '1';
                document.getElementById('specialtyLoading').style.visibility = 'visible';
                document.getElementById('peakHoursLoading').style.opacity = '1';
                document.getElementById('peakHoursLoading').style.visibility = 'visible';
                document.getElementById('revenueMethodLoading').style.opacity = '1';
                document.getElementById('revenueMethodLoading').style.visibility = 'visible';

                const url = new URL("{{ route('admin.dashboard.data') }}", window.location.origin);
                url.searchParams.append('target_month', monthSelect.value);
                url.searchParams.append('target_year', yearSelect.value);

                fetch(url.toString(), {
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        renderTrendChart(data.trend);
                        renderSpecialtyChart(data.specialty);
                        renderPeakHoursChart(data.peak_hours);
                        renderRevenueMethodChart(data.revenue_method);
                    })
                    .finally(() => {
                        setTimeout(() => {
                            document.getElementById('trendLoading').style.opacity = '0';
                            document.getElementById('trendLoading').style.visibility = 'hidden';
                            document.getElementById('specialtyLoading').style.opacity = '0';
                            document.getElementById('specialtyLoading').style.visibility = 'hidden';
                            document.getElementById('peakHoursLoading').style.opacity = '0';
                            document.getElementById('peakHoursLoading').style.visibility = 'hidden';
                            document.getElementById('revenueMethodLoading').style.opacity = '0';
                            document.getElementById('revenueMethodLoading').style.visibility = 'hidden';
                        }, 300);
                    });
            }

            function renderTrendChart(data) {
                const ctx = document.getElementById('trendChart').getContext('2d');
                if (trendChartInstance) {
                    trendChartInstance.destroy();
                }

                // Create gradient
                const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                gradient.addColorStop(0, 'rgba(14, 165, 233, 0.3)');
                gradient.addColorStop(1, 'rgba(14, 165, 233, 0.0)');

                trendChartInstance = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.trendLabels,
                        datasets: [{
                            label: 'Lịch khám',
                            data: data.trendData,
                            borderColor: '#0ea5e9',
                            backgroundColor: gradient,
                            borderWidth: 3,
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#0ea5e9',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                                padding: 12,
                                backgroundColor: 'rgba(17, 24, 39, 0.9)',
                                titleFont: {
                                    size: 13
                                },
                                bodyFont: {
                                    size: 14,
                                    weight: 'bold'
                                },
                                displayColors: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                border: {
                                    display: false
                                },
                                grid: {
                                    color: '#f3f4f6',
                                    drawBorder: false
                                },
                                ticks: {
                                    stepSize: 1,
                                    color: '#9ca3af',
                                    padding: 10
                                }
                            },
                            x: {
                                border: {
                                    display: false
                                },
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: '#6b7280',
                                    padding: 10
                                }
                            }
                        },
                        interaction: {
                            mode: 'nearest',
                            axis: 'x',
                            intersect: false
                        }
                    }
                });
            }

            function renderSpecialtyChart(data) {
                const ctx = document.getElementById('specialtyChart').getContext('2d');
                if (specialtyChartInstance) {
                    specialtyChartInstance.destroy();
                }

                let labels = data.pieLabels;
                let values = data.pieData;

                if (values.reduce((a, b) => a + b, 0) === 0) {
                    labels = ['Chưa có dữ liệu'];
                    values = [1];
                }

                specialtyChartInstance = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: values,
                            backgroundColor: brandColors,
                            borderWidth: 0,
                            hoverOffset: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '75%',
                        layout: {
                            padding: 20
                        },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    padding: 20,
                                    font: {
                                        size: 12,
                                        family: "'Inter', sans-serif"
                                    }
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(17, 24, 39, 0.9)',
                                padding: 12,
                                bodyFont: {
                                    size: 14
                                },
                                callbacks: {
                                    label: function(context) {
                                        let label = context.label || '';
                                        if (label !== 'Chưa có dữ liệu') {
                                            label += ': ' + context.raw + ' ca';
                                        }
                                        return ' ' + label;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            function renderPeakHoursChart(data) {
                const ctx = document.getElementById('peakHoursChart').getContext('2d');
                if (peakHoursChartInstance) {
                    peakHoursChartInstance.destroy();
                }

                peakHoursChartInstance = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.peakLabels,
                        datasets: [{
                            label: 'Số ca khám',
                            data: data.peakData,
                            backgroundColor: '#f59e0b',
                            borderRadius: 6,
                            barThickness: 'flex'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: 'rgba(17, 24, 39, 0.9)',
                                padding: 12,
                                bodyFont: {
                                    size: 14
                                },
                                callbacks: {
                                    title: function(context) {
                                        return 'Khung giờ ' + context[0].label;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: '#f3f4f6',
                                    drawBorder: false
                                },
                                ticks: {
                                    stepSize: 1,
                                    color: '#9ca3af'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: '#6b7280'
                                }
                            }
                        }
                    }
                });
            }

            function renderRevenueMethodChart(data) {
                const ctx = document.getElementById('revenueMethodChart').getContext('2d');
                if (revenueMethodChartInstance) {
                    revenueMethodChartInstance.destroy();
                }

                let labels = data.revenueMethodLabels;
                let values = data.revenueMethodData;

                revenueMethodChartInstance = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: values,
                            backgroundColor: methodColors,
                            borderWidth: 0,
                            hoverOffset: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        layout: {
                            padding: 20
                        },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    padding: 20,
                                    font: {
                                        size: 12,
                                        family: "'Inter', sans-serif"
                                    }
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(17, 24, 39, 0.9)',
                                padding: 12,
                                callbacks: {
                                    label: function(context) {
                                        let label = context.label || '';
                                        if (label !== 'Chưa có dữ liệu') {
                                            label += ': ' + new Intl.NumberFormat('vi-VN', {
                                                style: 'currency',
                                                currency: 'VND'
                                            }).format(context.raw);
                                        }
                                        return ' ' + label;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Init
            fetchChartData();

            // Lắng nghe sự kiện change
            monthSelect.addEventListener('change', fetchChartData);
            yearSelect.addEventListener('change', fetchChartData);
        });
    </script>
</x-layouts.admin>