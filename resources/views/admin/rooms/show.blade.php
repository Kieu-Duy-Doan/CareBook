
<x-layouts.admin title="Chi tiết Phòng khám">
    <div class="mb-6">
        <a href="{{ route('admin.rooms.index') }}" class="text-sm font-medium text-gray-500 hover:text-blue-600 transition-colors mb-2 inline-block">
            <i class="fa-solid fa-arrow-left mr-1"></i> Quay lại Danh sách Phòng
        </a>
        <h2 class="text-2xl font-bold text-gray-900 mt-2">Chi tiết Phòng khám</h2>
    </div>

    <!-- Thông tin tổng quan Phòng -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8 flex flex-col md:flex-row gap-6 items-start">
        <div class="flex-shrink-0">
            <div class="h-24 w-24 rounded-xl bg-purple-50 text-purple-500 flex flex-col items-center justify-center border border-purple-100">
                @if($room->room_type === 'examination')
                    <i class="fa-solid fa-stethoscope text-4xl mb-2"></i>
                @elseif($room->room_type === 'diagnostic')
                    <i class="fa-solid fa-microscope text-4xl mb-2"></i>
                @elseif($room->room_type === 'surgery')
                    <i class="fa-solid fa-scalpel text-4xl mb-2"></i>
                @else
                    <i class="fa-solid fa-door-open text-4xl mb-2"></i>
                @endif
            </div>
        </div>
        
        <div class="flex-1 w-full">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-2xl font-bold text-gray-900">{{ $room->name }}</h3>
                    <div class="text-sm text-gray-500 mt-1">
                        Mã phòng: <span class="font-bold text-gray-700">{{ $room->room_number ?? 'N/A' }}</span>
                    </div>
                </div>
                @if($room->is_active)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 border border-green-200">
                        <i class="fa-solid fa-check-circle mr-1"></i> Đang hoạt động
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800 border border-red-200">
                        <i class="fa-solid fa-times-circle mr-1"></i> Đã đóng cửa
                    </span>
                @endif
            </div>
            
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                    <div class="text-xs text-gray-500 font-medium uppercase tracking-wider mb-1">Loại phòng</div>
                    <div class="font-bold text-gray-900">
                        @if($room->room_type === 'examination') Khám bệnh
                        @elseif($room->room_type === 'diagnostic') Chẩn đoán/Xét nghiệm
                        @elseif($room->room_type === 'surgery') Phẫu thuật
                        @else Khác @endif
                    </div>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                    <div class="text-xs text-gray-500 font-medium uppercase tracking-wider mb-1">Vị trí</div>
                    <div class="font-bold text-gray-900">
                        Tầng {{ $room->floor ?? '--' }}, Tòa {{ $room->building ?? '--' }}
                    </div>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                    <div class="text-xs text-gray-500 font-medium uppercase tracking-wider mb-1">Sức chứa</div>
                    <div class="font-bold text-gray-900">{{ $room->capacity ?? '--' }} người</div>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg border border-purple-100">
                    <div class="text-xs text-purple-600 font-medium uppercase tracking-wider mb-1">Chuyên khoa</div>
                    <div class="font-bold text-purple-900">{{ $room->specialties->count() }} chuyên khoa</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Phân vùng Danh sách Chuyên khoa & Lịch làm việc -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Chuyên khoa sử dụng phòng -->
        <div class="lg:col-span-1">
            <h3 class="text-lg font-bold text-gray-900 mb-4">
                <i class="fa-solid fa-stethoscope text-blue-500 mr-2"></i> 
                Chuyên khoa phục vụ
            </h3>
            
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                @if($room->specialties->count() > 0)
                    <div class="flex flex-wrap gap-2">
                        @foreach($room->specialties as $specialty)
                            <a href="{{ route('admin.specialties.show', $specialty->id) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-100 transition-colors">
                                {{ $specialty->name }}
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6 text-gray-500 text-sm">
                        Phòng này hiện chưa được gán chuyên khoa cụ thể nào (phục vụ chung).
                    </div>
                @endif
            </div>
        </div>

        <!-- Lịch làm việc định kỳ -->
        <div class="lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900">
                    <i class="fa-regular fa-calendar-days text-indigo-500 mr-2"></i> 
                    Lịch trực định kỳ tại phòng
                </h3>
                <a href="{{ route('admin.work-schedules.index') }}?room_id={{ $room->id }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Quản lý Lịch trực <i class="fa-solid fa-arrow-right ml-1"></i></a>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thứ</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thời gian</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bác sĩ phụ trách</th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php
                            $daysMap = [
                                2 => 'Thứ Hai', 3 => 'Thứ Ba', 4 => 'Thứ Tư', 5 => 'Thứ Năm', 
                                6 => 'Thứ Sáu', 7 => 'Thứ Bảy', 1 => 'Chủ Nhật'
                            ];
                        @endphp
                        @forelse($room->workSchedules->sortBy('day_of_week') as $ws)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 whitespace-nowrap font-bold text-sm text-gray-900">
                                {{ $daysMap[$ws->day_of_week] ?? '' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-blue-600 font-medium">
                                {{ substr($ws->start_time, 0, 5) }} - {{ substr($ws->end_time, 0, 5) }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center">
                                    <div class="h-6 w-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-[10px] mr-2">
                                        {{ $ws->doctor->user->avatar_initials }}
                                    </div>
                                    <div class="text-sm font-medium text-gray-900">{{ $ws->doctor->full_title }}</div>
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('admin.work-schedules.show', $ws->id) }}" class="text-blue-600 hover:text-blue-900"><i class="fa-solid fa-eye"></i></a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-500 text-sm">
                                Chưa có lịch trực nào được phân bổ tại phòng này.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Bệnh nhân có lịch hẹn hôm nay -->
    <div>
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900">
                <i class="fa-regular fa-calendar-check text-green-500 mr-2"></i> 
                Lịch hẹn hôm nay tại phòng ({{ \Carbon\Carbon::today()->format('d/m/Y') }})
            </h3>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                {{ $todayAppointments->count() }} ca khám
            </span>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Giờ khám</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bệnh nhân</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bác sĩ</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($todayAppointments as $apt)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap font-bold text-sm text-blue-600">
                                {{ substr($apt->appointment_time, 0, 5) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $apt->patientProfile->full_name }}</div>
                                <div class="text-xs text-gray-500">{{ $apt->patientProfile->phone }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $apt->doctorProfile->full_title }}</div>
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
                                        'completed' => 'Khám xong',
                                        'cancelled' => 'Đã huỷ',
                                    ];
                                    $color = $statusColors[$apt->status] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                                    $label = $statusLabels[$apt->status] ?? $apt->status;
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $color }}">
                                    {{ $label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('admin.appointments.show', $apt->id) }}" class="text-blue-600 hover:text-blue-900 transition-colors">Xem phiếu hẹn</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="h-12 w-12 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                                        <i class="fa-regular fa-calendar-check text-xl text-gray-400"></i>
                                    </div>
                                    <p class="text-sm font-medium text-gray-900">Không có lịch khám nào trong phòng này vào hôm nay.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.admin>
