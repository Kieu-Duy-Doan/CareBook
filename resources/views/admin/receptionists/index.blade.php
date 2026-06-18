<x-layouts.admin title="Quản lý Lễ tân">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-800">Quản lý Lễ tân</h2>
            <a href="{{ route('admin.receptionists.create') }}" class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition flex items-center gap-2">
                <i class="fa-solid fa-plus"></i> Thêm lễ tân mới
            </a>
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

        <!-- Stat cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-lg shadow-sm border-l-4 border-orange-500 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Tổng lễ tân</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center text-orange-500">
                        <i class="fa-solid fa-user-tie text-lg"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border-l-4 border-green-500 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Đang hoạt động</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $stats['active'] }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-500">
                        <i class="fa-solid fa-circle-check text-lg"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border-l-4 border-red-500 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Đã khoá</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $stats['locked'] }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-500">
                        <i class="fa-solid fa-circle-xmark text-lg"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bộ lọc -->
        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100">
            <form action="{{ route('admin.receptionists.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 items-end">
                <div class="flex-1 w-full">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tìm kiếm</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}"
                               class="pl-10 w-full border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 py-2"
                               placeholder="Tìm theo tên, SĐT hoặc mã NV...">
                    </div>
                </div>
                
                <div class="w-full md:w-64">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phòng ban</label>
                    <select name="department" class="w-full border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 py-2">
                        <option value="">Tất cả phòng ban</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>
                                {{ $dept }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="w-full md:w-48">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                    <select name="status" class="w-full border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 py-2">
                        <option value="">Tất cả</option>
                        <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Đang hoạt động</option>
                        <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Đã khoá</option>
                    </select>
                </div>

                <div class="flex gap-2 w-full md:w-auto">
                    <button type="submit" class="flex-1 md:flex-none px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 flex items-center justify-center gap-2 transition">
                        <i class="fa-solid fa-filter"></i> Lọc
                    </button>
                    @if(request()->anyFilled(['search', 'department', 'status']))
                        <a href="{{ route('admin.receptionists.index') }}" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 flex items-center justify-center transition">
                            Đặt lại
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Bảng lễ tân -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600">
                    <thead class="bg-gray-50 text-gray-700 uppercase text-xs font-semibold border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-center w-12">#</th>
                            <th class="px-4 py-3">Lễ tân</th>
                            <th class="px-4 py-3">Mã NV</th>
                            <th class="px-4 py-3">Chức vụ</th>
                            <th class="px-4 py-3">Phòng ban</th>
                            <th class="px-4 py-3 text-center">SĐT nội bộ</th>
                            <th class="px-4 py-3 text-center">Ngày vào làm</th>
                            <th class="px-4 py-3 text-center">Trạng thái</th>
                            <th class="px-4 py-3 text-center w-32">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($receptionists as $key => $receptionist)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3 text-center">{{ $receptionists->firstItem() + $key }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm bg-orange-100 text-orange-600 shrink-0">
                                            {{ $receptionist->avatar_initials ?? mb_substr($receptionist->full_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-900">{{ $receptionist->full_name }}</div>
                                            <div class="text-xs text-gray-500" title="SĐT đăng nhập"><i class="fa-solid fa-phone mr-1"></i>{{ $receptionist->phone }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 font-mono text-gray-800">
                                    {{ $receptionist->staffProfile?->employee_code ?? '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ $receptionist->staffProfile?->position ?? '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ $receptionist->staffProfile?->department ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    {{ $receptionist->staffProfile?->internal_phone ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    {{ $receptionist->staffProfile?->start_date ? \Carbon\Carbon::parse($receptionist->staffProfile->start_date)->format('d/m/Y') : '—' }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($receptionist->is_active)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-medium bg-green-100 text-green-700">
                                            Hoạt động
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-medium bg-red-100 text-red-700">
                                            Đã khoá
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('admin.receptionists.show', $receptionist->id) }}" class="w-8 h-8 rounded bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-100 transition" title="Xem chi tiết">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.receptionists.edit', $receptionist->id) }}" class="w-8 h-8 rounded bg-yellow-50 text-yellow-600 flex items-center justify-center hover:bg-yellow-100 transition" title="Chỉnh sửa">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <form action="{{ route('admin.receptionists.toggle-active', $receptionist->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn thay đổi trạng thái tài khoản lễ tân này?');" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            @if($receptionist->is_active)
                                                <button type="submit" class="w-8 h-8 rounded bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-100 transition" title="Khoá tài khoản">
                                                    <i class="fa-solid fa-lock"></i>
                                                </button>
                                            @else
                                                <button type="submit" class="w-8 h-8 rounded bg-green-50 text-green-600 flex items-center justify-center hover:bg-green-100 transition" title="Mở khoá tài khoản">
                                                    <i class="fa-solid fa-lock-open"></i>
                                                </button>
                                            @endif
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fa-solid fa-user-slash text-5xl text-gray-300 mb-4"></i>
                                        <p class="text-lg font-medium text-gray-600">Chưa có lễ tân nào trong hệ thống.</p>
                                        <p class="text-sm text-gray-400 mt-1">Bấm vào "Thêm lễ tân mới" để bắt đầu.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($receptionists->hasPages())
                <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
                    {{ $receptionists->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>
