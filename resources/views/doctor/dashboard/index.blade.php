<x-layouts.doctor>
    <x-slot name="title">Bảng điều khiển</x-slot>

    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Xin chào, Bác sĩ {{ Auth::user()->full_name }}!</h2>
        <p class="text-gray-500">Dưới đây là tổng quan lịch làm việc của bạn hôm nay.</p>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex items-center">
            <div class="rounded-full bg-blue-100 p-3 mr-4">
                <i class="fa-solid fa-calendar-day text-blue-600 text-xl w-6 h-6 flex items-center justify-center"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Lịch hẹn hôm nay</p>
                <p class="text-2xl font-bold text-gray-900">{{ $todayAppointmentsCount }}</p>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex items-center">
            <div class="rounded-full bg-green-100 p-3 mr-4">
                <i class="fa-solid fa-check-double text-green-600 text-xl w-6 h-6 flex items-center justify-center"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Đã hoàn thành</p>
                <p class="text-2xl font-bold text-gray-900">{{ $completedTodayCount }}</p>
            </div>
        </div>
    </div>

    <!-- Lịch hẹn sắp tới -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-900">Lịch hẹn sắp tới</h3>
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
                        <th class="px-6 py-3 font-medium">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($upcomingAppointments as $appointment)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m/Y') }}</div>
                                <div class="text-gray-500">{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">{{ $appointment->patientProfile->full_name ?? 'N/A' }}</div>
                                <div class="text-gray-500">{{ $appointment->patientProfile->phone ?? '' }}</div>
                            </td>
                            <td class="px-6 py-4 text-gray-700">
                                {{ Str::limit($appointment->reason, 50) }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-{{ $appointment->status_color }}-100 text-{{ $appointment->status_color }}-700">
                                    {{ $appointment->status_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('doctor.appointments.show', $appointment->id) }}" class="text-blue-600 hover:text-blue-800 font-medium text-sm">
                                    Chi tiết <i class="fa-solid fa-arrow-right ml-1"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fa-regular fa-calendar-xmark text-4xl mb-3 text-gray-300"></i>
                                    <p>Không có lịch hẹn sắp tới</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.doctor>
