<x-layouts.receptionist title="Quản lý Khách hàng">
    <div class="space-y-6">
        <!-- Action Bar -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <h2 class="text-2xl font-bold text-gray-800">Quản lý Khách hàng</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('receptionist.customers.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i> Thêm khách hàng
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

        <!-- Stat cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg shadow-sm border-l-4 border-green-600 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Tổng khách hàng</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600">
                        <i class="fa-solid fa-users text-lg"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border-l-4 border-blue-500 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Đang hoạt động</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $stats['active'] }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-500">
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
            <div class="bg-white rounded-lg shadow-sm border-l-4 border-purple-500 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Tổng hồ sơ</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $stats['profiles'] }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center text-purple-500">
                        <i class="fa-solid fa-folder-open text-lg"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bộ lọc -->
        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100">
            <form action="{{ route('receptionist.customers.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 items-end">
                <div class="flex-1 w-full">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tìm kiếm</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}"
                               class="pl-10 w-full border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 py-2"
                               placeholder="Tìm theo tên, SĐT, email, CCCD hoặc mã BHYT...">
                    </div>
                </div>
                
                <div class="w-full md:w-48">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bảo hiểm y tế</label>
                    <select name="has_insurance" class="w-full border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 py-2">
                        <option value="">Tất cả</option>
                        <option value="1" {{ request('has_insurance') == '1' ? 'selected' : '' }}>Có BHYT</option>
                        <option value="0" {{ request('has_insurance') == '0' ? 'selected' : '' }}>Không có BHYT</option>
                    </select>
                </div>

                <div class="w-full md:w-48">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                    <select name="status" class="w-full border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 py-2">
                        <option value="">Tất cả</option>
                        <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Đang hoạt động</option>
                        <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Đã khoá</option>
                    </select>
                </div>

                <div class="flex gap-2 w-full md:w-auto">
                    <button type="submit" class="flex-1 md:flex-none px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center justify-center gap-2 transition">
                        <i class="fa-solid fa-filter"></i> Lọc
                    </button>
                    @if(request()->anyFilled(['search', 'has_insurance', 'status']))
                        <a href="{{ route('receptionist.customers.index') }}" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 flex items-center justify-center transition">
                            Đặt lại
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Bảng khách hàng -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600">
                    <thead class="bg-gray-50 text-gray-700 uppercase text-xs font-semibold border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-center w-12">#</th>
                            <th class="px-4 py-3">Khách hàng</th>
                            <th class="px-4 py-3">SĐT</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3 text-center">Số hồ sơ</th>
                            <th class="px-4 py-3">BHYT</th>
                            <th class="px-4 py-3 text-center">Trạng thái</th>
                            <th class="px-4 py-3 text-center w-32">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($customers as $key => $customer)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3 text-center">{{ $customers->firstItem() + $key }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm bg-green-100 text-green-600 shrink-0">
                                            {{ $customer->avatar_initials ?? mb_substr($customer->full_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-900">{{ $customer->full_name }}</div>
                                            <div class="text-xs text-gray-500 mt-0.5">
                                                <i class="fa-regular fa-id-card mr-1"></i>{{ $customer->id_card ?? '—' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 font-medium">
                                    <i class="fa-solid fa-phone text-gray-400 mr-1.5 text-xs"></i>{{ $customer->phone }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ $customer->email ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-medium bg-purple-50 text-purple-700 border border-purple-200 rounded">
                                        {{ $customer->patientProfiles->count() }} hồ sơ
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $selfProfile = $customer->patientProfiles->where('is_self', 1)->first();
                                    @endphp
                                    @if($selfProfile && $selfProfile->insurance_code)
                                        @if($selfProfile->insurance_expiry && \Carbon\Carbon::parse($selfProfile->insurance_expiry)->isPast())
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-red-50 text-red-700 border border-red-200 mb-1">
                                                Hết hạn
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-green-50 text-green-700 border border-green-200 mb-1">
                                                Có BHYT
                                            </span>
                                        @endif
                                        <div class="text-xs text-gray-500 font-mono">{{ $selfProfile->insurance_code }}</div>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200">
                                            Không có
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($customer->is_active)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-700">
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
                                        <a href="{{ route('receptionist.customers.show', $customer->id) }}" class="w-8 h-8 rounded bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-100 transition" title="Xem chi tiết">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="{{ route('receptionist.customers.edit', $customer->id) }}" class="w-8 h-8 rounded bg-yellow-50 text-yellow-600 flex items-center justify-center hover:bg-yellow-100 transition" title="Chỉnh sửa">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <form action="{{ route('receptionist.customers.destroy', $customer->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xoá khách hàng này? Tất cả hồ sơ và dữ liệu liên quan sẽ bị xoá vĩnh viễn!');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-8 h-8 rounded bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-100 transition" title="Xoá khách hàng">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fa-solid fa-user-slash text-5xl text-gray-300 mb-4"></i>
                                        <p class="text-lg font-medium text-gray-600">Chưa có khách hàng nào.</p>
                                        <p class="text-sm text-gray-400 mt-1">Bấm vào "Thêm khách hàng" để tạo hồ sơ mới.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($customers->hasPages())
                <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
                    {{ $customers->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.receptionist>
