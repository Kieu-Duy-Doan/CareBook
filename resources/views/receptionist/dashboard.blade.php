<x-layouts.receptionist>
    <x-slot:title>Bảng điều khiển</x-slot:title>

    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Tổng quan hôm nay</h2>
        <p class="text-gray-500 text-sm mt-1">Xin chào, {{ Auth::user()->full_name }}! Chúc bạn một ngày làm việc hiệu quả.</p>
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Danh sách bệnh nhân sắp đến -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 lg:col-span-2 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-900">Bệnh nhân sắp đến (Chưa Check-in)</h3>
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
                                Không có bệnh nhân nào sắp đến.
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

    <!-- Quick Actions -->
    <h3 class="text-lg font-bold text-gray-900 mb-4">Thao tác nhanh</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="{{ route('receptionist.appointments.create') }}" class="flex items-center p-4 bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-gray-50 hover:border-emerald-300 transition-colors">
            <div class="h-10 w-10 rounded bg-emerald-100 text-emerald-600 flex items-center justify-center mr-3">
                <i class="fa-solid fa-plus"></i>
            </div>
            <span class="font-medium text-gray-700">Tạo lịch hẹn mới</span>
        </a>
        <a href="{{ route('receptionist.patients.create') }}" class="flex items-center p-4 bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-gray-50 hover:border-blue-300 transition-colors">
            <div class="h-10 w-10 rounded bg-blue-100 text-blue-600 flex items-center justify-center mr-3">
                <i class="fa-solid fa-user-plus"></i>
            </div>
            <span class="font-medium text-gray-700">Thêm bệnh nhân</span>
        </a>
        <a href="{{ route('receptionist.clinical-visits.index') }}" class="flex items-center p-4 bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-gray-50 hover:border-purple-300 transition-colors">
            <div class="h-10 w-10 rounded bg-purple-100 text-purple-600 flex items-center justify-center mr-3">
                <i class="fa-solid fa-desktop"></i>
            </div>
            <span class="font-medium text-gray-700">Màn hình giám sát</span>
        </a>
        <a href="{{ route('receptionist.payments.index') }}" class="flex items-center p-4 bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-gray-50 hover:border-orange-300 transition-colors">
            <div class="h-10 w-10 rounded bg-orange-100 text-orange-600 flex items-center justify-center mr-3">
                <i class="fa-solid fa-file-invoice-dollar"></i>
            </div>
            <span class="font-medium text-gray-700">Thanh toán & Thu ngân</span>
        </a>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const ctx = document.getElementById('hourlyChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: {
                        !!json_encode($chartLabels) !!
                    },
                    datasets: [{
                        label: 'Số ca khám',
                        data: {
                            !!json_encode($chartData) !!
                        },
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