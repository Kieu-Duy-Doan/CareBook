<x-layouts.doctor>
    <x-slot name="title">Bảng điều khiển</x-slot>

    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Xin chào, Bác sĩ {{ Auth::user()->full_name }}!</h2>
        <p class="text-gray-500">Dưới đây là tổng quan lịch làm việc của bạn.</p>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <!-- Đang chờ khám -->
        <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100 flex items-center">
            <div class="rounded-full bg-yellow-100 p-3 mr-4">
                <i class="fa-solid fa-hourglass-half text-yellow-600 text-xl w-6 h-6 flex items-center justify-center"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500">Bệnh nhân đang chờ</p>
                <p class="text-xl font-bold text-gray-900">{{ $patientsWaitingOutside }}</p>
            </div>
        </div>

        <!-- Hôm nay -->
        <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100 flex items-center">
            <div class="rounded-full bg-blue-100 p-3 mr-4">
                <i class="fa-solid fa-calendar-day text-blue-600 text-xl w-6 h-6 flex items-center justify-center"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500">Lịch hẹn hôm nay</p>
                <p class="text-xl font-bold text-gray-900">{{ $todayAppointmentsCount }}</p>
            </div>
        </div>

        <!-- Hoàn thành tháng này -->
        <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100 flex items-center">
            <div class="rounded-full bg-green-100 p-3 mr-4">
                <i class="fa-solid fa-check-double text-green-600 text-xl w-6 h-6 flex items-center justify-center"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500">Đã khám (Tháng này)</p>
                <p class="text-xl font-bold text-gray-900">{{ $totalCompletedThisMonth }}</p>
            </div>
        </div>

        <!-- Doanh thu cá nhân -->
        <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100 flex items-center">
            <div class="rounded-full bg-purple-100 p-3 mr-4">
                <i class="fa-solid fa-sack-dollar text-purple-600 text-xl w-6 h-6 flex items-center justify-center"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500">Doanh thu (Tháng này)</p>
                <p class="text-xl font-bold text-gray-900">{{ number_format($revenueThisMonth) }} đ</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Lịch hẹn sắp tới -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden lg:col-span-2">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-900">Lịch hẹn tiếp theo</h3>
                <a href="{{ route('doctor.appointments.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">Xem tất cả</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-500 uppercase bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 font-medium">Thời gian</th>
                            <th class="px-6 py-3 font-medium">Bệnh nhân</th>
                            <th class="px-6 py-3 font-medium">Lý do khám</th>
                            <th class="px-6 py-3 font-medium">Trạng thái</th>
                            <th class="px-6 py-3 font-medium text-right">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($upcomingAppointments as $appointment)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-blue-600">{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') }}</div>
                                <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">{{ $appointment->patientProfile->full_name ?? 'N/A' }}</div>
                                <div class="text-xs text-gray-500">{{ $appointment->patientProfile->phone ?? '' }}</div>
                            </td>
                            <td class="px-6 py-4 text-gray-700">
                                {{ Str::limit($appointment->reason, 40) }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-{{ $appointment->status_color }}-100 text-{{ $appointment->status_color }}-700">
                                    {{ $appointment->status_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('doctor.appointments.show', $appointment->id) }}" class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-medium bg-blue-50 text-blue-600 rounded-md hover:bg-blue-100 transition-colors">
                                    Vào khám <i class="fa-solid fa-arrow-right ml-1"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fa-regular fa-calendar-xmark text-4xl mb-3 text-gray-300"></i>
                                    <p>Không có lịch hẹn tiếp theo</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Biểu đồ mini 7 ngày -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Ca khám 7 ngày qua</h3>
            <div class="relative h-48">
                <canvas id="weeklyChart"></canvas>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const ctx = document.getElementById('weeklyChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: {
                        !!json_encode($miniChartLabels) !!
                    },
                    datasets: [{
                        label: 'Số ca khám',
                        data: {
                            !!json_encode($miniChartData) !!
                        },
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
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
</x-layouts.doctor>