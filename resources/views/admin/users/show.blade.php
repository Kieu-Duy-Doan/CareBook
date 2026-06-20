<x-layouts.admin title="Chi tiết quản trị viên">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-full bg-red-100 text-red-600 flex items-center justify-center text-2xl font-bold shadow-sm">
                    {{ $user->avatar_initials ?? mb_substr($user->full_name, 0, 1) }}
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                        {{ $user->full_name }}
                        <span class="px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700 border border-red-200">
                            Quản trị viên
                        </span>
                    </h2>
                    <p class="text-gray-500 mt-1">{{ '@' . $user->username }}</p>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                @if($user->role === 'admin')
                <a href="{{ route('admin.users.edit', $user->id) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center gap-2 shadow-sm text-sm font-medium">
                    <i class="fa-solid fa-pen-to-square"></i> Sửa thông tin
                </a>
                @endif
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition flex items-center gap-2 text-sm font-medium">
                    <i class="fa-solid fa-arrow-left"></i> Quay lại
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Cột trái: Thông tin -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Thông tin tài khoản</h3>
                    <div class="space-y-4 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Số điện thoại</span>
                            <span class="font-medium text-gray-900">{{ $user->phone }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Username</span>
                            <span class="font-medium text-gray-900">{{ $user->username }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Email</span>
                            <span class="font-medium text-gray-900">{{ $user->email ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">CCCD/CMND</span>
                            <span class="font-medium text-gray-900">{{ $user->id_card ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Ngày tạo</span>
                            <span class="font-medium text-gray-900">{{ $user->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Đăng nhập cuối</span>
                            <span class="font-medium text-gray-900">
                                {{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->format('d/m/Y H:i') : '—' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cột phải: Logs -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Hoạt động gần đây</h3>
                    
                    @if($logs->isEmpty())
                        <div class="py-8 text-center text-gray-500 flex flex-col items-center">
                            <i class="fa-solid fa-clipboard-list text-3xl text-gray-300 mb-2"></i>
                            <p>Không tìm thấy hoạt động nào.</p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($logs as $log)
                                <div class="flex gap-4 p-3 rounded-lg border border-gray-50 bg-gray-50/50 hover:bg-gray-50 transition">
                                    <div class="mt-1">
                                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
                                            <i class="fa-solid fa-bolt text-sm"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">{{ $log->description ?? $log->action }}</p>
                                        <div class="flex items-center gap-4 mt-1 text-xs text-gray-500">
                                            <span><i class="fa-regular fa-clock mr-1"></i> {{ $log->created_at->diffForHumans() }}</span>
                                            <span><i class="fa-solid fa-network-wired mr-1"></i> {{ $log->ip_address ?? '—' }}</span>
                                        </div>
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
