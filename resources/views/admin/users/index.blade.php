<x-layouts.admin title="Quản lý tài khoản">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-800">Quản lý tài khoản</h2>
        </div>

        <!-- PHẦN 1: Stats -->
        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">
            <a href="{{ route('admin.users.index') }}" class="block bg-white rounded-lg shadow-sm border-l-4 border-blue-500 p-4 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Tổng tài khoản</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-500">
                        <i class="fa-solid fa-users text-lg"></i>
                    </div>
                </div>
            </a>
            <a href="{{ route('admin.users.index', ['role' => 'patient']) }}" class="block bg-white rounded-lg shadow-sm border-l-4 border-green-500 p-4 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Bệnh nhân</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $stats['patient'] }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-500">
                        <i class="fa-solid fa-user-injured text-lg"></i>
                    </div>
                </div>
            </a>
            <a href="{{ route('admin.users.index', ['role' => 'doctor']) }}" class="block bg-white rounded-lg shadow-sm border-l-4 border-purple-500 p-4 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Bác sĩ</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $stats['doctor'] }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center text-purple-500">
                        <i class="fa-solid fa-user-doctor text-lg"></i>
                    </div>
                </div>
            </a>
            <a href="{{ route('admin.users.index', ['role' => 'receptionist']) }}" class="block bg-white rounded-lg shadow-sm border-l-4 border-orange-500 p-4 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Lễ tân</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $stats['receptionist'] }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center text-orange-500">
                        <i class="fa-solid fa-user-tie text-lg"></i>
                    </div>
                </div>
            </a>
            <a href="{{ route('admin.users.index', ['role' => 'admin']) }}" class="block bg-white rounded-lg shadow-sm border-l-4 border-red-500 p-4 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Quản trị</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $stats['admin'] }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-500">
                        <i class="fa-solid fa-user-shield text-lg"></i>
                    </div>
                </div>
            </a>
            <a href="{{ route('admin.users.index', ['status' => '0']) }}" class="block bg-white rounded-lg shadow-sm border-l-4 border-gray-500 p-4 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Đã khoá</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $stats['locked'] }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500">
                        <i class="fa-solid fa-user-lock text-lg"></i>
                    </div>
                </div>
            </a>
        </div>

        <!-- PHẦN 2: Thêm nhanh -->
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.doctors.create') }}"
               class="flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                <i class="fa-solid fa-user-doctor"></i>
                <span>Thêm bác sĩ</span>
            </a>
            <a href="{{ route('admin.receptionists.create') }}"
               class="flex items-center gap-2 px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition">
                <i class="fa-solid fa-user-tie"></i>
                <span>Thêm lễ tân</span>
            </a>
            <a href="{{ Route::has('admin.patients.create') ? route('admin.patients.create') : '#' }}"
               class="flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                <i class="fa-solid fa-user-injured"></i>
                <span>Thêm bệnh nhân</span>
            </a>
        </div>

        <!-- PHẦN 3: Bộ lọc -->
        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100">
            <form action="{{ route('admin.users.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 items-end">
                <div class="flex-1 w-full">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tìm kiếm</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}"
                               class="pl-10 w-full border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 py-2"
                               placeholder="Tìm theo tên, SĐT, email, username...">
                    </div>
                </div>
                
                <div class="w-full md:w-48">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Vai trò</label>
                    <select name="role" class="w-full border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 py-2">
                        <option value="">Tất cả</option>
                        <option value="patient" {{ request('role') == 'patient' ? 'selected' : '' }}>Bệnh nhân</option>
                        <option value="doctor" {{ request('role') == 'doctor' ? 'selected' : '' }}>Bác sĩ</option>
                        <option value="receptionist" {{ request('role') == 'receptionist' ? 'selected' : '' }}>Lễ tân</option>
                        <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Quản trị viên</option>
                    </select>
                </div>

                <div class="w-full md:w-48">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                    <select name="status" class="w-full border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 py-2">
                        <option value="">Tất cả</option>
                        <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Đang hoạt động</option>
                        <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Đã khoá</option>
                    </select>
                </div>

                <div class="flex gap-2 w-full md:w-auto">
                    <button type="submit" class="flex-1 md:flex-none px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-filter"></i> Lọc
                    </button>
                    @if(request()->anyFilled(['search', 'role', 'status']))
                        <a href="{{ route('admin.users.index') }}" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 flex items-center justify-center">
                            Đặt lại
                        </a>
                    @endif
                </div>
            </form>
            
            <div class="mt-4 text-sm text-gray-500">
                Hiển thị <span class="font-bold text-gray-900">{{ $users->total() }}</span> tài khoản 
                @if(request()->anyFilled(['search', 'role', 'status'])) theo điều kiện lọc @endif
            </div>
        </div>

        <!-- PHẦN 4: Bảng users -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600">
                    <thead class="bg-gray-50 text-gray-700 uppercase text-xs font-semibold border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-center w-12">#</th>
                            <th class="px-4 py-3">Tài khoản</th>
                            <th class="px-4 py-3">SĐT</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3 text-center">Role</th>
                            <th class="px-4 py-3 text-center">Trạng thái</th>
                            <th class="px-4 py-3">Đăng nhập cuối</th>
                            <th class="px-4 py-3 text-center w-24">Chi tiết</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($users as $key => $user)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3 text-center">{{ $users->firstItem() + $key }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        @php
                                            $colorClass = match($user->role) {
                                                'admin' => 'bg-red-100 text-red-600',
                                                'doctor' => 'bg-purple-100 text-purple-600',
                                                'receptionist' => 'bg-orange-100 text-orange-600',
                                                'patient' => 'bg-green-100 text-green-600',
                                                default => 'bg-gray-100 text-gray-600'
                                            };
                                        @endphp
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm {{ $colorClass }}">
                                            {{ $user->avatar_initials ?? mb_substr($user->full_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-900">{{ $user->full_name }}</div>
                                            <div class="text-xs text-gray-500">{{ '@' . $user->username }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-1.5">
                                        <i class="fa-solid fa-phone-alt text-gray-400 text-xs"></i>
                                        <span>{{ $user->phone }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">{{ $user->email ?? '—' }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if ($user->role === 'admin')
                                        <span class="px-2 py-1 rounded-md text-xs font-medium bg-red-100 text-red-700">Quản trị viên</span>
                                    @elseif ($user->role === 'doctor')
                                        <span class="px-2 py-1 rounded-md text-xs font-medium bg-purple-100 text-purple-700">Bác sĩ</span>
                                    @elseif ($user->role === 'receptionist')
                                        <span class="px-2 py-1 rounded-md text-xs font-medium bg-orange-100 text-orange-700">Lễ tân</span>
                                    @elseif ($user->role === 'patient')
                                        <span class="px-2 py-1 rounded-md text-xs font-medium bg-green-100 text-green-700">Bệnh nhân</span>
                                    @else
                                        <span class="px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-700">{{ $user->role }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($user->is_active)
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium bg-green-100 text-green-700">
                                            <i class="fa-solid fa-circle-check"></i> Hoạt động
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium bg-red-100 text-red-700">
                                            <i class="fa-solid fa-circle-xmark"></i> Đã khoá
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->diffForHumans() : '—' }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @php
                                        $showRoute = match($user->role) {
                                            'doctor' => Route::has('admin.doctors.show') ? route('admin.doctors.show', $user->doctorProfile->id ?? 0) : '#',
                                            'receptionist' => Route::has('admin.receptionists.show') ? route('admin.receptionists.show', $user->id) : '#',
                                            'patient' => Route::has('admin.patients.show') ? route('admin.patients.show', $user->id) : '#',
                                            'admin' => Route::has('admin.users.show') ? route('admin.users.show', $user->id) : '#',
                                            default => '#'
                                        };
                                    @endphp
                                    <a href="{{ $showRoute }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 hover:text-blue-700 transition" title="Xem chi tiết">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fa-solid fa-users-slash text-4xl text-gray-300 mb-3"></i>
                                        <p>Không tìm thấy tài khoản nào.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($users->hasPages())
                <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>

