<x-layouts.admin title="Bảng điều khiển">
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
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

    <!-- Hàng 1: KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Card 1: Lịch khám hôm nay -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 relative overflow-hidden group">
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
                @if ($apptGrowth > 0)
                    <span class="text-green-600 font-medium flex items-center bg-green-50 px-2 py-0.5 rounded-full"><i
                            class="fa-solid fa-arrow-trend-up mr-1 text-xs"></i> +{{ round($apptGrowth, 1) }}%</span>
                @elseif($apptGrowth < 0)
                    <span class="text-red-600 font-medium flex items-center bg-red-50 px-2 py-0.5 rounded-full"><i
                            class="fa-solid fa-arrow-trend-down mr-1 text-xs"></i> {{ round($apptGrowth, 1) }}%</span>
                @else
                    <span class="text-gray-500 font-medium flex items-center bg-gray-50 px-2 py-0.5 rounded-full"><i
                            class="fa-solid fa-minus mr-1 text-xs"></i> 0%</span>
                @endif
                <span class="text-gray-400 ml-2">so với hôm qua</span>
            </div>
        </div>

        <!-- Card 2: Bệnh nhân mới -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 relative overflow-hidden group">
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
                    <span class="text-green-600 font-medium flex items-center bg-green-50 px-2 py-0.5 rounded-full"><i
                            class="fa-solid fa-arrow-trend-up mr-1 text-xs"></i> +{{ round($patientGrowth, 1) }}%</span>
                @elseif($patientGrowth < 0)
                    <span class="text-red-600 font-medium flex items-center bg-red-50 px-2 py-0.5 rounded-full"><i
                            class="fa-solid fa-arrow-trend-down mr-1 text-xs"></i>
                        {{ round($patientGrowth, 1) }}%</span>
                @else
                    <span class="text-gray-500 font-medium flex items-center bg-gray-50 px-2 py-0.5 rounded-full"><i
                            class="fa-solid fa-minus mr-1 text-xs"></i> 0%</span>
                @endif
                <span class="text-gray-400 ml-2">so với tháng trước</span>
            </div>
        </div>

        <!-- Card 3: Tỷ lệ hoàn thành khám -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <i class="fa-solid fa-check-double text-6xl text-emerald-600"></i>
            </div>
            <div class="flex justify-between items-start mb-4 relative z-10">
                <div>
                    <p class="text-sm font-medium text-gray-500">Tỷ lệ hoàn thành hôm nay</p>
                    <h3 class="text-3xl font-bold text-gray-900 mt-1">{{ $completionRate }}%</h3>
                </div>
                <div class="w-10 h-10 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600">
                    <i class="fa-solid fa-check-to-slot"></i>
                </div>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-1.5 mt-2">
                <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $completionRate }}%"></div>
            </div>
            <p class="text-xs text-gray-400 mt-2">{{ $completedToday }} / {{ $todayApptCount }} ca đã khám</p>
        </div>

        <!-- Card 4: Bác sĩ trực -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <i class="fa-solid fa-user-doctor text-6xl text-indigo-600"></i>
            </div>
            <div class="flex justify-between items-start mb-4 relative z-10">
                <div>
                    <p class="text-sm font-medium text-gray-500">Bác sĩ đang hoạt động</p>
                    <h3 class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($activeDoctorsCount) }}</h3>
                </div>
                <div class="w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600">
                    <i class="fa-solid fa-stethoscope"></i>
                </div>
            </div>
            <div class="flex items-center text-sm relative z-10">
                <span class="text-indigo-600 font-medium flex items-center bg-indigo-50 px-2 py-0.5 rounded-full"><i
                        class="fa-solid fa-circle text-[8px] mr-1.5 text-indigo-500"></i> Sẵn sàng phục vụ</span>
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
                <div class="flex bg-gray-50 p-1 rounded-lg border border-gray-200">
                    <a href="{{ route('admin.dashboard', ['trend' => 'day']) }}"
                        class="px-4 py-1.5 text-sm font-medium rounded-md transition-all {{ $trendFilter === 'day' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">7
                        Ngày</a>
                    <a href="{{ route('admin.dashboard', ['trend' => 'month']) }}"
                        class="px-4 py-1.5 text-sm font-medium rounded-md transition-all {{ $trendFilter === 'month' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">Năm
                        nay</a>
                    <a href="{{ route('admin.dashboard', ['trend' => 'year']) }}"
                        class="px-4 py-1.5 text-sm font-medium rounded-md transition-all {{ $trendFilter === 'year' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">5
                        Năm</a>
                </div>
            </div>
            <div class="flex-1 min-h-[300px] w-full relative">
                <canvas id="trendChart" class="absolute inset-0 w-full h-full"></canvas>
            </div>
        </div>

        <!-- Donut Chart -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col">
            <h3 class="text-lg font-bold text-gray-900 mb-6">Cơ cấu Chuyên khoa</h3>
            <div class="flex-1 flex flex-col items-center justify-center min-h-[300px]">
                <div class="relative w-48 h-48 mb-6">
                    <canvas id="specialtyChart" class="w-full h-full"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                        <span class="text-3xl font-bold text-gray-900">{{ array_sum($pieData) }}</span>
                        <span class="text-xs text-gray-500">Lịch tháng này</span>
                    </div>
                </div>

                <!-- Legend -->
                <div class="w-full grid grid-cols-2 gap-2 mt-auto">
                    @foreach ($pieLabels as $index => $label)
                        <div class="flex items-center text-xs text-gray-600">
                            <span class="w-3 h-3 rounded-full mr-2 flex-shrink-0"
                                id="legend-color-{{ $index }}"></span>
                            <span class="truncate" title="{{ $label }}">{{ $label }}</span>
                            <span class="ml-auto font-medium text-gray-900">{{ $pieData[$index] }}</span>
                        </div>
                    @endforeach
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
                                        {{ $appt->patientProfile->full_name ?? 'Khách lẻ' }}</div>
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
                                    {{ $item->doctorProfile->user->name ?? 'Unknown' }}</p>
                                <p class="text-xs text-gray-500 truncate">
                                    {{ $item->doctorProfile->specialty->name ?? 'Khoa khám bệnh' }}</p>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const brandColors = ['#0ea5e9', '#14b8a6', '#6366f1', '#f59e0b', '#ec4899', '#8b5cf6', '#10b981',
                '#f43f5e'
            ];
            const trendLabels = @json($trendLabels);
            const trendData = @json($trendData);
            const pieLabels = @json($pieLabels);
            const pieData = @json($pieData);

            // Gán màu cho legend
            pieLabels.forEach((label, i) => {
                const legendDot = document.getElementById('legend-color-' + i);
                if (legendDot) {
                    legendDot.style.backgroundColor = brandColors[i % brandColors.length];
                }
            });

            function drawLineChart(canvasId, labels, data) {
                const canvas = document.getElementById(canvasId);
                if (!canvas) return;
                const parent = canvas.parentElement;
                const dpr = window.devicePixelRatio || 1;
                const rect = parent.getBoundingClientRect();

                canvas.width = rect.width * dpr;
                canvas.height = rect.height * dpr;

                const ctx = canvas.getContext('2d');
                ctx.scale(dpr, dpr);

                const width = rect.width;
                const height = rect.height;
                const padding = {
                    top: 30,
                    right: 30,
                    bottom: 40,
                    left: 40
                };

                const chartWidth = width - padding.left - padding.right;
                const chartHeight = height - padding.top - padding.bottom;

                const maxVal = Math.max(...data, 5);
                const minVal = 0;

                ctx.clearRect(0, 0, width, height);
                ctx.beginPath();
                ctx.strokeStyle = '#f3f4f6';
                ctx.lineWidth = 1;
                const gridRows = 5;
                for (let i = 0; i <= gridRows; i++) {
                    const y = padding.top + chartHeight - (i * chartHeight / gridRows);
                    ctx.moveTo(padding.left, y);
                    ctx.lineTo(width - padding.right, y);
                    ctx.fillStyle = '#9ca3af';
                    ctx.font = '10px sans-serif';
                    ctx.textAlign = 'right';
                    ctx.textBaseline = 'middle';
                    const labelVal = Math.round(minVal + (i * (maxVal - minVal) / gridRows));
                    ctx.fillText(labelVal, padding.left - 10, y);
                }
                ctx.stroke();
                if (data.length === 0) return;

                const stepX = chartWidth / (data.length > 1 ? data.length - 1 : 1);
                const gradient = ctx.createLinearGradient(0, padding.top, 0, height - padding.bottom);
                gradient.addColorStop(0, 'rgba(14, 165, 233, 0.2)');
                gradient.addColorStop(1, 'rgba(14, 165, 233, 0)');

                ctx.beginPath();
                ctx.moveTo(padding.left, padding.top + chartHeight);
                for (let i = 0; i < data.length; i++) {
                    const x = padding.left + i * stepX;
                    const y = padding.top + chartHeight - ((data[i] - minVal) / (maxVal - minVal) * chartHeight);
                    ctx.lineTo(x, y);
                }
                ctx.lineTo(padding.left + (data.length - 1) * stepX, padding.top + chartHeight);
                ctx.fillStyle = gradient;
                ctx.fill();
                ctx.beginPath();
                ctx.strokeStyle = '#0ea5e9';
                ctx.lineWidth = 3;
                ctx.lineJoin = 'round';
                ctx.lineCap = 'round';
                for (let i = 0; i < data.length; i++) {
                    const x = padding.left + i * stepX;
                    const y = padding.top + chartHeight - ((data[i] - minVal) / (maxVal - minVal) * chartHeight);
                    if (i === 0) ctx.moveTo(x, y);
                    else ctx.lineTo(x, y);
                }
                ctx.stroke();
                for (let i = 0; i < data.length; i++) {
                    const x = padding.left + i * stepX;
                    const y = padding.top + chartHeight - ((data[i] - minVal) / (maxVal - minVal) * chartHeight);
                    ctx.beginPath();
                    ctx.fillStyle = '#ffffff';
                    ctx.strokeStyle = '#0ea5e9';
                    ctx.lineWidth = 2;
                    ctx.arc(x, y, 4, 0, Math.PI * 2);
                    ctx.fill();
                    ctx.stroke();
                    ctx.fillStyle = '#6b7280';
                    ctx.font = '11px sans-serif';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'top';
                    ctx.fillText(labels[i], x, padding.top + chartHeight + 10);
                    if (data[i] > 0) {
                        ctx.fillStyle = '#1f2937';
                        ctx.font = 'bold 10px sans-serif';
                        ctx.fillText(data[i], x, y - 15);
                    }
                }
            }

            function drawDonutChart(canvasId, data, colors) {
                const canvas = document.getElementById(canvasId);
                if (!canvas) return;

                const dpr = window.devicePixelRatio || 1;
                const rect = canvas.parentElement.getBoundingClientRect();

                canvas.width = rect.width * dpr;
                canvas.height = rect.height * dpr;

                const ctx = canvas.getContext('2d');
                ctx.scale(dpr, dpr);

                const centerX = rect.width / 2;
                const centerY = rect.height / 2;
                const radius = Math.min(centerX, centerY) - 10;
                const thickness = 20;

                const total = data.reduce((sum, val) => sum + val, 0);
                if (total === 0) return;

                let startAngle = -Math.PI / 2;

                for (let i = 0; i < data.length; i++) {
                    const sliceAngle = (data[i] / total) * 2 * Math.PI;
                    const endAngle = startAngle + sliceAngle;

                    ctx.beginPath();
                    ctx.arc(centerX, centerY, radius, startAngle, endAngle);
                    ctx.lineWidth = thickness;
                    ctx.strokeStyle = colors[i % colors.length];
                    ctx.stroke();
                    startAngle = endAngle;
                }
            }

            // Gọi hàm vẽ
            drawLineChart('trendChart', trendLabels, trendData);
            drawDonutChart('specialtyChart', pieData, brandColors);

            // Xử lý Resize Window để vẽ lại biểu đồ cho Responsive
            window.addEventListener('resize', () => {
                drawLineChart('trendChart', trendLabels, trendData);
                drawDonutChart('specialtyChart', pieData, brandColors);
            });
        });
    </script>
</x-layouts.admin>
