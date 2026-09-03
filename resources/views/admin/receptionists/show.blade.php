<x-layouts.admin title="Hồ sơ lễ tân — {{ $receptionist->full_name }}">
    <div class="space-y-6">
        <!-- Breadcrumbs & Actions -->
        <div class="flex items-center justify-between">
            <nav class="flex text-sm text-gray-500 font-medium">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-gray-900 transition">Dashboard</a>
                <span class="mx-2 text-gray-400">/</span>
                <a href="{{ route('admin.receptionists.index') }}" class="hover:text-gray-900 transition">Lễ tân</a>
                <span class="mx-2 text-gray-400">/</span>
                <span class="text-gray-900">{{ $receptionist->full_name }}</span>
            </nav>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.receptionists.index') }}" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition flex items-center gap-2">
                    <i class="fa-solid fa-arrow-left"></i> Quay lại
                </a>
                <a href="{{ route('admin.receptionists.edit', $receptionist->id) }}" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition flex items-center gap-2">
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
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" class="mb-4 flex items-center gap-3 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span>{{ session('error') }}</span>
            <button @click="show=false" class="ml-auto"><i class="fa-solid fa-xmark"></i></button>
        </div>
        @endif

        <!-- Header Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 flex items-start gap-6">
            <div class="w-20 h-20 shrink-0 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center text-3xl font-bold shadow-sm">
                {{ $receptionist->avatar_initials ?? mb_substr($receptionist->full_name, 0, 1) }}
            </div>
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                    <h2 class="text-2xl font-bold text-gray-900">{{ $receptionist->full_name }}</h2>
                    <span class="px-2.5 py-1 rounded-md text-xs font-medium bg-orange-100 text-orange-700 border border-orange-200">
                        Lễ tân
                    </span>
                    @if ($receptionist->is_active)
                        <span class="px-2.5 py-1 rounded-md text-xs font-medium bg-green-100 text-green-700 border border-green-200">
                            Đang hoạt động
                        </span>
                    @else
                        <span class="px-2.5 py-1 rounded-md text-xs font-medium bg-red-100 text-red-700 border border-red-200">
                            Đã khoá
                        </span>
                    @endif
                </div>
                
                <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-gray-600">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-id-badge text-gray-400"></i>
                        <span class="font-mono">{{ $receptionist->staffProfile?->employee_code ?? '—' }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-briefcase text-gray-400"></i>
                        <span>{{ $receptionist->staffProfile?->position ?? '—' }}</span>
                    </div>
                    @if($receptionist->staffProfile?->department)
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-building text-gray-400"></i>
                            <span>{{ $receptionist->staffProfile->department }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Appointment Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-lg shadow-sm border-l-4 border-blue-500 p-4 flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium mb-1">Check-in hôm nay</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $checkInStats['today'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border-l-4 border-purple-500 p-4 flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium mb-1">Check-in tháng này</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $checkInStats['month'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-purple-50 text-purple-500 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-calendar-days"></i>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border-l-4 border-orange-500 p-4 flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium mb-1">Tổng check-in</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $checkInStats['total'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-orange-50 text-orange-500 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-clipboard-check"></i>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Cột trái (2/3) -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Thông tin tài khoản -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="font-semibold text-gray-800"><i class="fa-solid fa-user-lock mr-2 text-orange-500"></i>Thông tin tài khoản</h3>
                    </div>
                    <div class="p-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 text-sm">
                            <div class="flex justify-between border-b border-gray-50 pb-2">
                                <span class="text-gray-500">SĐT đăng nhập</span>
                                <span class="font-medium text-gray-900">{{ $receptionist->phone }}</span>
                            </div>
                            <div class="flex justify-between border-b border-gray-50 pb-2">
                                <span class="text-gray-500">Username</span>
                                <span class="font-medium text-gray-900">{{ $receptionist->username }}</span>
                            </div>
                            <div class="flex justify-between border-b border-gray-50 pb-2">
                                <span class="text-gray-500">CCCD/CMND</span>
                                <span class="font-medium text-gray-900">{{ $receptionist->id_card ?? '—' }}</span>
                            </div>
                            <div class="flex justify-between border-b border-gray-50 pb-2">
                                <span class="text-gray-500">Email</span>
                                <span class="font-medium text-gray-900">{{ $receptionist->email ?? '—' }}</span>
                            </div>
                            <div class="flex justify-between border-b border-gray-50 pb-2">
                                <span class="text-gray-500">Ngày tạo</span>
                                <span class="font-medium text-gray-900">{{ $receptionist->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <div class="flex justify-between border-b border-gray-50 pb-2">
                                <span class="text-gray-500">Đăng nhập cuối</span>
                                <span class="font-medium text-gray-900">
                                    {{ $receptionist->last_login_at ? \Carbon\Carbon::parse($receptionist->last_login_at)->format('d/m/Y H:i') : '—' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Thông tin nhân viên -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="font-semibold text-gray-800"><i class="fa-solid fa-address-card mr-2 text-orange-500"></i>Thông tin nhân viên</h3>
                    </div>
                    <div class="p-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 text-sm">
                            <div class="flex justify-between border-b border-gray-50 pb-2">
                                <span class="text-gray-500">Mã NV</span>
                                <span class="font-mono text-gray-900">{{ $receptionist->staffProfile?->employee_code ?? '—' }}</span>
                            </div>
                            <div class="flex justify-between border-b border-gray-50 pb-2">
                                <span class="text-gray-500">Chức vụ</span>
                                <span class="font-medium text-gray-900">{{ $receptionist->staffProfile?->position ?? '—' }}</span>
                            </div>
                            <div class="flex justify-between border-b border-gray-50 pb-2">
                                <span class="text-gray-500">Phòng ban</span>
                                <span class="font-medium text-gray-900">{{ $receptionist->staffProfile?->department ?? '—' }}</span>
                            </div>
                            <div class="flex justify-between border-b border-gray-50 pb-2">
                                <span class="text-gray-500">SĐT nội bộ</span>
                                <span class="font-medium text-gray-900">{{ $receptionist->staffProfile?->internal_phone ?? '—' }}</span>
                            </div>
                            <div class="flex justify-between border-b border-gray-50 pb-2">
                                <span class="text-gray-500">Ngày vào làm</span>
                                <span class="font-medium text-gray-900">
                                    {{ $receptionist->staffProfile?->start_date ? \Carbon\Carbon::parse($receptionist->staffProfile->start_date)->format('d/m/Y') : '—' }}
                                </span>
                            </div>
                            <div class="flex justify-between border-b border-gray-50 pb-2">
                                <span class="text-gray-500">Trạng thái nhân viên</span>
                                @if($receptionist->staffProfile?->is_active)
                                    <span class="font-medium text-green-600">Đang công tác</span>
                                @else
                                    <span class="font-medium text-red-600">Đã nghỉ việc / Tạm hoãn</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hoạt động gần đây -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="font-semibold text-gray-800"><i class="fa-solid fa-clock-rotate-left mr-2 text-orange-500"></i>Hoạt động gần đây</h3>
                    </div>
                    <div class="p-5">
                        @if($activityLogs->isEmpty())
                            <div class="py-8 text-center text-gray-500">
                                <i class="fa-regular fa-clock text-4xl text-gray-300 mb-3"></i>
                                <p>Chưa có hoạt động nào.</p>
                            </div>
                        @else
                            <div class="relative border-l-2 border-gray-100 ml-3 space-y-6">
                                @foreach($activityLogs as $log)
                                    <div class="relative pl-6">
                                        <div class="absolute -left-[9px] top-1 w-4 h-4 rounded-full bg-white border-2 border-orange-400 flex items-center justify-center">
                                            <div class="w-1.5 h-1.5 rounded-full bg-orange-400"></div>
                                        </div>
                                        <p class="text-sm font-medium text-gray-900">{{ $log->description ?? $log->action_label }}</p>
                                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1 text-xs text-gray-500">
                                            <span>{{ $log->created_at->diffForHumans() }}</span>
                                            <span class="text-gray-300">•</span>
                                            <span>{{ $log->created_at->format('H:i d/m/Y') }}</span>
                                            @if($log->user)
                                                <span class="text-gray-300">•</span>
                                                <span>Bởi: <strong class="text-gray-700 font-medium">{{ $log->user->full_name }}</strong> ({{ $log->user->role === 'admin' ? 'Quản trị viên' : ucfirst($log->user->role) }})</span>
                                            @endif
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
                        <a href="{{ route('admin.receptionists.edit', $receptionist->id) }}" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                            <i class="fa-solid fa-pen"></i> Chỉnh sửa thông tin
                        </a>
                        
                        <form action="{{ route('admin.receptionists.toggle-active', $receptionist->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn {{ $receptionist->is_active ? 'khoá' : 'mở khoá' }} tài khoản lễ tân này?');">
                            @csrf
                            @method('PATCH')
                            @if($receptionist->is_active)
                                <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 border border-red-200 text-red-600 bg-red-50 rounded-lg hover:bg-red-100 hover:border-red-300 transition font-medium">
                                    <i class="fa-solid fa-lock"></i> Khoá tài khoản
                                </button>
                            @else
                                <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 border border-green-200 text-green-600 bg-green-50 rounded-lg hover:bg-green-100 hover:border-green-300 transition font-medium">
                                    <i class="fa-solid fa-lock-open"></i> Mở khoá tài khoản
                                </button>
                            @endif
                        </form>
                    </div>

                    <div class="mt-6 pt-4 border-t border-gray-100">
                        <p class="text-sm text-gray-500 mb-2">Trạng thái hiện tại:</p>
                        @if ($receptionist->is_active)
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
                    
                    @if($changeLogs->isEmpty())
                        <div class="py-6 text-center text-gray-500">
                            <i class="fa-solid fa-clipboard-list text-2xl text-gray-300 mb-2"></i>
                            <p class="text-sm">Không có dữ liệu.</p>
                        </div>
                    @else
                        <div class="relative border-l border-gray-200 ml-3 space-y-6">
                            @foreach($changeLogs as $log)
                                <div class="relative pl-6">
                                    <span class="absolute -left-1.5 top-1 w-3 h-3 rounded-full bg-blue-400 ring-4 ring-white"></span>
                                    <p class="text-sm font-medium text-gray-900">{{ $log->description ?? $log->action_label }}</p>
                                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1 text-xs text-gray-500">
                                        <span><i class="fa-regular fa-clock mr-1"></i> {{ $log->created_at->diffForHumans() }}</span>
                                        @if($log->user)
                                            <span class="text-gray-300">•</span>
                                            <span>Bởi: <strong class="text-gray-700 font-medium">{{ $log->user->full_name }}</strong> ({{ $log->user->role === 'admin' ? 'Quản trị viên' : ($log->user->role === 'receptionist' ? 'Lễ tân' : ucfirst($log->user->role)) }})</span>
                                        @else
                                            <span class="text-gray-300">•</span>
                                            <span>Bởi: <span class="italic text-gray-400">Hệ thống</span></span>
                                        @endif
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
