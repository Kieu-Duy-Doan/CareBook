<x-layouts.admin title="Hồ sơ bác sĩ — {{ $doctor->full_title }}">
    <div class="space-y-6">
        <!-- Breadcrumbs & Actions -->
        <div class="flex items-center justify-between">
            <nav class="flex text-sm text-gray-500 font-medium">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-gray-900 transition">Dashboard</a>
                <span class="mx-2 text-gray-400">/</span>
                <a href="{{ route('admin.doctors.index') }}" class="hover:text-gray-900 transition">Bác sĩ</a>
                <span class="mx-2 text-gray-400">/</span>
                <span class="text-gray-900">{{ $doctor->full_title }}</span>
            </nav>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.doctors.index') }}" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition flex items-center gap-2">
                    <i class="fa-solid fa-arrow-left"></i> Quay lại
                </a>
                <a href="{{ route('admin.doctors.edit', $doctor->id) }}" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition flex items-center gap-2">
                    <i class="fa-solid fa-pen"></i> Chỉnh sửa
                </a>
            </div>
        </div>

        <!-- Session Alerts -->
        @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" class="mb-4 flex items-center gap-3 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('success') }}</span>
            <button @click="show=false" class="ml-auto"><i class="fa-solid fa-xmark"></i></button>
        </div>
        @endif
        @if(session('error'))
        <div class="mb-4 flex items-center gap-3 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        <!-- Header Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 flex items-start gap-6">
            <div class="w-20 h-20 shrink-0 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center text-3xl font-bold shadow-sm">
                {{ $doctor->user->avatar_initials ?? mb_substr($doctor->user->full_name, 0, 1) }}
            </div>
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                    <h2 class="text-2xl font-bold text-gray-900">{{ $doctor->full_title }}</h2>
                    <span class="px-2.5 py-1 rounded-md text-xs font-medium bg-purple-100 text-purple-700 border border-purple-200">
                        {{ $doctor->level }}
                    </span>
                    @if ($doctor->user->is_active)
                        <span class="px-2.5 py-1 rounded-md text-xs font-medium bg-green-100 text-green-700 border border-green-200">
                            Đang hoạt động
                        </span>
                    @else
                        <span class="px-2.5 py-1 rounded-md text-xs font-medium bg-red-100 text-red-700 border border-red-200">
                            Đã khoá
                        </span>
                    @endif
                </div>
                
                <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-gray-600 mb-4">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-id-card text-gray-400"></i>
                        <span class="font-mono">{{ $doctor->doctor_code }}</span>
                    </div>
                    @if($doctor->license_number)
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-file-signature text-gray-400"></i>
                            <span>CCHN: {{ $doctor->license_number }}</span>
                        </div>
                    @endif
                    @if($doctor->experience_years)
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-briefcase-medical text-gray-400"></i>
                            <span>{{ $doctor->experience_years }} năm kinh nghiệm</span>
                        </div>
                    @endif
                </div>
                
                <div class="flex flex-wrap gap-2">
                    @foreach($doctor->specialties as $specialty)
                        <span class="px-3 py-1 rounded-full text-xs {{ $specialty->pivot->is_primary ? 'bg-green-100 text-green-800 font-medium border border-green-300' : 'bg-gray-100 text-gray-700 border border-gray-200' }}">
                            {{ $specialty->name }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Appointment Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
                <p class="text-sm text-gray-500 font-medium mb-1">Tổng lịch hẹn</p>
                <p class="text-2xl font-bold text-gray-900">{{ $appointmentStats['total'] }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-blue-100 bg-blue-50/50 p-4">
                <p class="text-sm text-blue-600 font-medium mb-1">Hôm nay</p>
                <p class="text-2xl font-bold text-blue-700">{{ $appointmentStats['today'] }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-yellow-100 bg-yellow-50/50 p-4">
                <p class="text-sm text-yellow-600 font-medium mb-1">Đang chờ khám</p>
                <p class="text-2xl font-bold text-yellow-700">{{ $appointmentStats['pending'] }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-green-100 bg-green-50/50 p-4">
                <p class="text-sm text-green-600 font-medium mb-1">Hoàn thành</p>
                <p class="text-2xl font-bold text-green-700">{{ $appointmentStats['completed'] }}</p>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Cột trái (2/3) -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Thông tin tài khoản -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="font-semibold text-gray-800">Thông tin tài khoản</h3>
                    </div>
                    <div class="p-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 text-sm">
                            <div class="flex justify-between border-b border-gray-50 pb-2">
                                <span class="text-gray-500">SĐT đăng nhập</span>
                                <span class="font-medium text-gray-900">{{ $doctor->user->phone }}</span>
                            </div>
                            <div class="flex justify-between border-b border-gray-50 pb-2">
                                <span class="text-gray-500">Username</span>
                                <span class="font-medium text-gray-900">{{ $doctor->user->username }}</span>
                            </div>
                            <div class="flex justify-between border-b border-gray-50 pb-2">
                                <span class="text-gray-500">Email</span>
                                <span class="font-medium text-gray-900">{{ $doctor->user->email ?? '—' }}</span>
                            </div>
                            <div class="flex justify-between border-b border-gray-50 pb-2">
                                <span class="text-gray-500">CCCD/CMND</span>
                                <span class="font-medium text-gray-900">{{ $doctor->user->id_card ?? '—' }}</span>
                            </div>
                            <div class="flex justify-between border-b border-gray-50 pb-2">
                                <span class="text-gray-500">Ngày tạo</span>
                                <span class="font-medium text-gray-900">{{ $doctor->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <div class="flex justify-between border-b border-gray-50 pb-2">
                                <span class="text-gray-500">Đăng nhập cuối</span>
                                <span class="font-medium text-gray-900">
                                    {{ $doctor->user->last_login_at ? \Carbon\Carbon::parse($doctor->user->last_login_at)->format('d/m/Y H:i') : '—' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Thông tin chuyên môn -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="font-semibold text-gray-800">Thông tin chuyên môn</h3>
                    </div>
                    <div class="p-5 space-y-5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                                <span class="block text-xs text-gray-500 mb-1">Học hàm/Học vị</span>
                                <span class="block font-medium text-gray-900">{{ $doctor->academic_title ?? '—' }}</span>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                                <span class="block text-xs text-gray-500 mb-1">Cấp độ</span>
                                <span class="block font-medium text-gray-900">{{ $doctor->level }}</span>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                                <span class="block text-xs text-gray-500 mb-1">Kinh nghiệm</span>
                                <span class="block font-medium text-gray-900">{{ $doctor->experience_years ? $doctor->experience_years . ' năm' : '—' }}</span>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                                <span class="block text-xs text-gray-500 mb-1">Số CCHN</span>
                                <span class="block font-medium text-gray-900">{{ $doctor->license_number ?? '—' }}</span>
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Chuyên khoa phụ trách</h4>
                            <div class="flex flex-wrap gap-2">
                                @foreach($doctor->specialties as $specialty)
                                    <div class="px-3 py-1.5 rounded-lg text-sm flex items-center gap-2 {{ $specialty->pivot->is_primary ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-gray-50 border border-gray-200 text-gray-700' }}">
                                        @if($specialty->pivot->is_primary)
                                            <i class="fa-solid fa-star text-green-500 text-xs"></i>
                                        @endif
                                        {{ $specialty->name }}
                                        @if($specialty->pivot->is_primary)
                                            <span class="text-[10px] uppercase font-bold text-green-600 bg-green-200 px-1.5 py-0.5 rounded ml-1">Chính</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Lĩnh vực chuyên môn</h4>
                            <div class="p-4 bg-gray-50 rounded-lg text-sm text-gray-700 whitespace-pre-wrap border border-gray-100">{{ $doctor->expertise ?: 'Chưa cập nhật' }}</div>
                        </div>

                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Giới thiệu bản thân</h4>
                            <div class="p-4 bg-gray-50 rounded-lg text-sm text-gray-700 whitespace-pre-wrap border border-gray-100">{{ $doctor->bio ?: 'Chưa cập nhật' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Lịch làm việc -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                        <h3 class="font-semibold text-gray-800">Lịch làm việc cố định</h3>
                        <a href="{{ route('admin.work-schedules.index', ['doctor_id' => $doctor->id]) }}" class="text-sm text-blue-600 hover:text-blue-700 hover:underline">Xem chi tiết</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-600">
                            <thead class="bg-gray-50 text-gray-700 uppercase text-xs font-semibold border-b border-gray-100">
                                <tr>
                                    <th class="px-5 py-3">Thứ</th>
                                    <th class="px-5 py-3">Thời gian</th>
                                    <th class="px-5 py-3">Phòng khám</th>
                                    <th class="px-5 py-3 text-center">Số slot</th>
                                    <th class="px-5 py-3 text-center">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($doctor->workSchedules as $schedule)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-5 py-3 font-medium text-gray-900">
                                            @php
                                                $days = [
                                                    1 => 'Thứ 2', 2 => 'Thứ 3', 3 => 'Thứ 4', 
                                                    4 => 'Thứ 5', 5 => 'Thứ 6', 6 => 'Thứ 7', 0 => 'Chủ nhật'
                                                ];
                                            @endphp
                                            {{ $days[$schedule->day_of_week] ?? '' }}
                                        </td>
                                        <td class="px-5 py-3">
                                            {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                        </td>
                                        <td class="px-5 py-3">
                                            <div class="flex items-center gap-2">
                                                <i class="fa-solid fa-door-open text-gray-400"></i>
                                                {{ $schedule->room->name ?? '—' }}
                                            </div>
                                        </td>
                                        <td class="px-5 py-3 text-center font-mono">
                                            {{ $schedule->max_slots }}
                                        </td>
                                        <td class="px-5 py-3 text-center">
                                            @if ($schedule->is_active)
                                                <span class="text-xs font-medium text-green-600 bg-green-50 px-2 py-1 rounded">Hoạt động</span>
                                            @else
                                                <span class="text-xs font-medium text-red-600 bg-red-50 px-2 py-1 rounded">Vô hiệu</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-5 py-8 text-center text-gray-500">
                                            Chưa có lịch làm việc.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Lịch hẹn gần nhất -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="font-semibold text-gray-800">Lịch hẹn gần nhất</h3>
                    </div>
                    <div class="p-5">
                        @if($recentAppointments->isEmpty())
                            <p class="text-gray-500 text-center py-4">Chưa có lịch hẹn nào.</p>
                        @else
                            <div class="space-y-4">
                                @foreach($recentAppointments as $appointment)
                                    <div class="flex items-center justify-between p-3 border border-gray-100 rounded-lg hover:bg-gray-50 transition">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm">
                                                {{ mb_substr($appointment->patientProfile->user->full_name ?? 'BN', 0, 1) }}
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900">{{ $appointment->patientProfile->user->full_name ?? 'Bệnh nhân' }}</p>
                                                <p class="text-xs text-gray-500">
                                                    <i class="fa-regular fa-calendar mr-1"></i> {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m/Y') }}
                                                </p>
                                            </div>
                                        </div>
                                        <div>
                                            @php
                                                $statusClass = match($appointment->status) {
                                                    'pending' => 'bg-yellow-100 text-yellow-700',
                                                    'confirmed' => 'bg-blue-100 text-blue-700',
                                                    'completed' => 'bg-green-100 text-green-700',
                                                    'cancelled' => 'bg-red-100 text-red-700',
                                                    default => 'bg-gray-100 text-gray-700'
                                                };
                                                $statusText = match($appointment->status) {
                                                    'pending' => 'Đã tiếp nhận',
                                                    'confirmed' => 'Đã xác nhận',
                                                    'completed' => 'Hoàn thành',
                                                    'cancelled' => 'Đã huỷ',
                                                    default => $appointment->status
                                                };
                                            @endphp
                                            <span class="px-2.5 py-1 rounded-md text-xs font-medium {{ $statusClass }}">
                                                {{ $statusText }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

            </div>

            <!-- Cột phải (1/3) -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Card Thao tác -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
                    <h3 class="font-semibold text-gray-800 mb-4 pb-2 border-b">Thao tác</h3>
                    
                    <div class="space-y-3">
                        <a href="{{ route('admin.doctors.edit', $doctor->id) }}" class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                            <i class="fa-solid fa-pen"></i> Chỉnh sửa thông tin
                        </a>
                        
                        <form action="{{ route('admin.doctors.toggle-active', $doctor->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn thay đổi trạng thái bác sĩ này?');">
                            @csrf
                            @method('PATCH')
                            @if($doctor->user->is_active)
                                <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2 border border-red-200 text-red-600 bg-red-50 rounded-lg hover:bg-red-100 hover:border-red-300 transition">
                                    <i class="fa-solid fa-lock"></i> Khoá tài khoản
                                </button>
                            @else
                                <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2 border border-green-200 text-green-600 bg-green-50 rounded-lg hover:bg-green-100 hover:border-green-300 transition">
                                    <i class="fa-solid fa-lock-open"></i> Mở khoá tài khoản
                                </button>
                            @endif
                        </form>
                    </div>

                    <div class="mt-6 pt-4 border-t border-gray-100">
                        <p class="text-sm text-gray-500 mb-2">Trạng thái hiện tại:</p>
                        @if ($doctor->user->is_active)
                            <div class="flex items-center gap-2 text-green-600 bg-green-50 p-3 rounded-lg border border-green-100">
                                <i class="fa-solid fa-circle-check text-lg"></i>
                                <span class="font-medium">Tài khoản đang hoạt động bình thường.</span>
                            </div>
                        @else
                            <div class="flex items-center gap-2 text-red-600 bg-red-50 p-3 rounded-lg border border-red-100">
                                <i class="fa-solid fa-circle-xmark text-lg"></i>
                                <span class="font-medium">Tài khoản đang bị khoá.</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Lịch sử thay đổi -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
                    <h3 class="font-semibold text-gray-800 mb-4 pb-2 border-b">Lịch sử thay đổi</h3>
                    
                    @if($logs->isEmpty())
                        <div class="py-6 text-center text-gray-500">
                            <i class="fa-solid fa-clipboard-list text-2xl text-gray-300 mb-2"></i>
                            <p class="text-sm">Không có dữ liệu.</p>
                        </div>
                    @else
                        <div class="relative border-l border-gray-200 ml-3 space-y-6">
                            @foreach($logs as $log)
                                <div class="relative pl-6">
                                    <span class="absolute -left-1.5 top-1 w-3 h-3 rounded-full bg-blue-400 ring-4 ring-white"></span>
                                    <p class="text-sm font-medium text-gray-900">{{ $log->description ?? $log->action }}</p>
                                    <div class="flex items-center gap-3 mt-1 text-xs text-gray-500">
                                        <span><i class="fa-regular fa-clock mr-1"></i> {{ $log->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
