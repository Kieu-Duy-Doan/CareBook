<x-layouts.admin title="Chi tiết ca làm việc">
    <div class="mb-6">
        <a href="{{ route('admin.work-schedules.index') }}"
            class="text-sm font-medium text-gray-500 hover:text-blue-600 transition-colors mb-2 inline-block">
            <i class="fa-solid fa-arrow-left mr-1"></i> Quay lại Lịch làm việc
        </a>
        <h2 class="text-2xl font-bold text-gray-900 mt-2">Chi tiết ca làm việc</h2>
        <p class="text-gray-500 mt-1">Thông tin ca làm việc và danh sách bệnh nhân đã đặt khám</p>
    </div>

    <!-- Thông tin tổng quan ca trực -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div
                    class="h-16 w-16 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xl">
                    {{ $schedule->doctor->user->avatar_initials }}
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-900">{{ $schedule->doctor->full_title }}</h3>
                    <p class="text-gray-500 text-sm mt-1">
                        <i class="fa-solid fa-phone mr-1"></i> {{ $schedule->doctor->user->phone }} |
                        <i class="fa-solid fa-envelope mr-1"></i>
                        {{ $schedule->doctor->user->email ?? 'Không có email' }}
                    </p>
                </div>
            </div>

            <div class="flex flex-col md:items-end gap-2">
                @if ($schedule->is_active)
                    <span
                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 border border-green-200">
                        <i class="fa-solid fa-check-circle mr-1"></i> Đang hoạt động
                    </span>
                @else
                    <span
                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800 border border-red-200">
                        <i class="fa-solid fa-times-circle mr-1"></i> Tạm ngưng
                    </span>
                @endif
                <form action="{{ route('admin.work-schedules.toggle-active', $schedule->id) }}" method="POST"
                    class="mt-1">
                    @csrf @method('PATCH')
                    <button type="submit" class="text-sm font-medium text-blue-600 hover:text-blue-800 underline">
                        {{ $schedule->is_active ? 'Chuyển sang Tạm ngưng' : 'Kích hoạt lại ca này' }}
                    </button>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-8 pt-6 border-t border-gray-100">
            <div>
                <span class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Thời gian</span>
                <div class="font-semibold text-gray-900">
                    <span class="text-blue-600">{{ $schedule->day_name }}</span><br>
                    <span class="text-lg">{{ substr($schedule->start_time, 0, 5) }} -
                        {{ substr($schedule->end_time, 0, 5) }}</span>
                </div>
            </div>
            <div>
                <span class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Phòng khám</span>
                <div class="font-semibold text-gray-900">
                    {{ $schedule->room->name }}<br>
                    <span class="text-sm font-normal text-gray-500">
                        {{ $schedule->room->room_number ? 'Phòng ' . $schedule->room->room_number : 'Chưa xếp phòng' }}
                    </span>
                </div>
            </div>
            <div>
                <span class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Năng lực khám</span>
                <div class="font-semibold text-gray-900">
                    Tối đa: <span class="text-blue-600">{{ $schedule->max_slots }} slot</span><br>
                    <span class="text-sm font-normal text-gray-500">
                        Dự kiến chia được ~{{ $slotsCount }} slot khám
                    </span>
                </div>
            </div>
            <div>
                <span class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Thời
                    lượng/Slot</span>
                <div class="font-semibold text-gray-900 text-lg">
                    {{ $schedule->slot_duration_minutes }} phút
                </div>
            </div>
        </div>
    </div>

    <!-- Lịch làm việc cả tuần của Bác sĩ -->
    <h3 class="text-lg font-bold text-gray-900 mb-4 mt-8">
        <i class="fa-regular fa-calendar-days text-blue-500 mr-2"></i>
        Lịch làm việc trong tuần
    </h3>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8 overflow-x-auto">
        <div class="min-w-[800px] grid grid-cols-7 gap-3">
            @php
                $days = [
                    2 => 'Thứ 2',
                    3 => 'Thứ 3',
                    4 => 'Thứ 4',
                    5 => 'Thứ 5',
                    6 => 'Thứ 6',
                    7 => 'Thứ 7',
                    1 => 'Chủ Nhật',
                ];
            @endphp

            @foreach ($days as $dayNum => $dayName)
                <div class="flex flex-col">
                    <div
                        class="bg-gray-50 border border-gray-200 text-center py-2 rounded-t-lg text-sm font-bold text-gray-700 {{ $schedule->day_of_week == $dayNum ? 'bg-blue-100 text-blue-800 border-blue-200' : '' }}">
                        {{ $dayName }}
                    </div>
                    <div
                        class="border border-t-0 border-gray-200 rounded-b-lg p-2 flex-1 flex flex-col gap-2 min-h-[120px] {{ $schedule->day_of_week == $dayNum ? 'bg-blue-50/50 border-blue-200' : 'bg-gray-50/30' }}">
                        @if (isset($weeklySchedules[$dayNum]) && count($weeklySchedules[$dayNum]) > 0)
                            @foreach ($weeklySchedules[$dayNum] as $ws)
                                {{-- @php
                                    dump($ws);
                                @endphp --}}
                                @if (!data_get($ws, 'is_override'))
                                    <a href="{{ route('admin.work-schedules.show', data_get($ws, 'id')) }}"
                                        class="block bg-white border p-2.5 rounded shadow-sm hover:shadow-md transition-shadow cursor-pointer">
                                        <div class="font-bold text-gray-900 text-xs flex items-center gap-1">
                                            <i class="fa-regular fa-clock text-gray-400"></i>
                                            {{ substr(data_get($ws, 'start_time'), 0, 5) }} -
                                            {{ substr(data_get($ws, 'end_time'), 0, 5) }}
                                        </div>
                                        <div class="text-gray-500 mt-1.5 text-[11px] truncate flex items-center gap-1"
                                            title="{{ data_get($ws, 'room.name') }}">
                                            <i class="fa-solid fa-door-open text-gray-400"></i>
                                            P.{{ data_get($ws, 'room.room_number') ?? data_get($ws, 'room.name') }}
                                        </div>
                                    </a>
                                @else
                                    {{-- @php
                                        dd($ws);
                                    @endphp --}}
                                    <a href="{{ route('admin.work-schedules.showOverride', data_get($ws, 'id')) }}"
                                        class="block bg-white border p-2.5 rounded shadow-sm hover:shadow-md transition-shadow cursor-pointer ">
                                        <div class="font-bold text-gray-900 text-xs flex items-center gap-1">
                                            <i class="fa-regular fa-clock text-gray-400"></i>
                                            {{ substr(data_get($ws, 'start_time'), 0, 5) }} -
                                            {{ substr(data_get($ws, 'end_time'), 0, 5) }}
                                        </div>
                                        <div class="text-gray-500 mt-1.5 text-[11px] truncate flex items-center gap-1"
                                            title="{{ data_get($ws, 'room.name') }}">
                                            <i class="fa-solid fa-door-open text-gray-400"></i>
                                            P.{{ data_get($ws, 'room.room_number') ?? data_get($ws, 'room.name') }}
                                            đây là override
                                        </div>
                                    </a>
                                @endif
                            @endforeach
                        @else
                            <div class="h-full w-full flex items-center justify-center text-xs text-gray-400 italic">
                                Chờ lịch
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Danh sách lịch hẹn sắp tới thuộc ca trực này -->
    <h3 class="text-lg font-bold text-gray-900 mb-4">
        <i class="fa-regular fa-calendar-check text-blue-500 mr-2"></i>
        Lịch hẹn sắp tới trong ca này
    </h3>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày
                            khám</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Giờ
                            khám</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bệnh
                            nhân</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lý do
                            khám</th>
                        <th scope="col"
                            class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Trạng thái</th>
                        <th scope="col"
                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Thao
                            tác</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($upcomingAppointments as $apt)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-sm text-gray-900">
                                {{ \Carbon\Carbon::parse($apt->appointment_date)->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-bold text-sm text-blue-600">
                                {{ substr($apt->appointment_time, 0, 5) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $apt->patientProfile->full_name }}
                                </div>
                                <div class="text-xs text-gray-500">{{ $apt->patientProfile->phone }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-gray-700 truncate max-w-xs" title="{{ $apt->reason }}">
                                    {{ $apt->reason }}</p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                        'confirmed' => 'bg-blue-100 text-blue-800 border-blue-200',
                                        'checked_in' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                                        'completed' => 'bg-green-100 text-green-800 border-green-200',
                                        'cancelled' => 'bg-red-100 text-red-800 border-red-200',
                                    ];
                                    $statusLabels = [
                                        'pending' => 'Chờ xác nhận',
                                        'confirmed' => 'Đã xác nhận',
                                        'checked_in' => 'Đã đến viện',
                                        'completed' => 'Đã khám xong',
                                        'cancelled' => 'Đã huỷ',
                                    ];
                                    $color = $statusColors[$apt->status] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                                    $label = $statusLabels[$apt->status] ?? $apt->status;
                                @endphp
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $color }}">
                                    {{ $label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('admin.appointments.show', $apt->id) }}"
                                    class="text-blue-600 hover:text-blue-900 transition-colors">Xem phiếu hẹn <i
                                        class="fa-solid fa-arrow-right ml-1"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <div
                                        class="h-12 w-12 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                                        <i class="fa-solid fa-user-doctor text-xl text-gray-400"></i>
                                    </div>
                                    <p class="text-sm font-medium text-gray-900">Ca trực này hiện chưa có bệnh nhân nào
                                        đặt lịch sắp tới.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($upcomingAppointments->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-white">
                {{ $upcomingAppointments->links() }}
            </div>
        @endif
    </div>
</x-layouts.admin>
