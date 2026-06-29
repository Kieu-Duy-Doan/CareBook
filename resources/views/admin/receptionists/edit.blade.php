<x-layouts.admin title="Chỉnh sửa lễ tân — {{ $receptionist->full_name }}">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <nav class="flex text-sm text-gray-500 font-medium">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-gray-900 transition">Dashboard</a>
                <span class="mx-2 text-gray-400">/</span>
                <a href="{{ route('admin.receptionists.index') }}" class="hover:text-gray-900 transition">Lễ tân</a>
                <span class="mx-2 text-gray-400">/</span>
                <a href="{{ route('admin.receptionists.show', $receptionist->id) }}" class="hover:text-gray-900 transition">{{ $receptionist->full_name }}</a>
                <span class="mx-2 text-gray-400">/</span>
                <span class="text-gray-900">Chỉnh sửa</span>
            </nav>
            <a href="{{ route('admin.receptionists.index') }}" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition flex items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i> Quay lại
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
        <div class="mb-4 flex items-center gap-3 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Cột trái: Form (2/3) -->
            <div class="lg:col-span-2">
                <form action="{{ route('admin.receptionists.update', $receptionist->id) }}" method="POST" x-data="{ loading: false }" @submit="loading = true">
                    @csrf
                    @method('PUT')
                    
                    <div class="space-y-6">
                        <!-- Thông tin đăng nhập -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                                <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                                    <i class="fa-solid fa-user-lock text-orange-500"></i> Thông tin đăng nhập
                                </h3>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Họ và tên đầy đủ <span class="text-red-500">*</span></label>
                                        <input type="text" name="full_name" value="{{ old('full_name', $receptionist->full_name) }}" required
                                               class="w-full border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 px-4 py-2 @error('full_name') border-red-500 @enderror">
                                        @error('full_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại <span class="text-red-500">*</span></label>
                                        <input type="text" name="phone" value="{{ old('phone', $receptionist->phone) }}" required
                                               class="w-full border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 px-4 py-2 @error('phone') border-red-500 @enderror">
                                        @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Tên đăng nhập <span class="text-red-500">*</span></label>
                                        <input type="text" name="username" value="{{ old('username', $receptionist->username) }}" required
                                               class="w-full border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 px-4 py-2 @error('username') border-red-500 @enderror">
                                        @error('username') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Số CCCD</label>
                                        <input type="text" name="id_card" value="{{ old('id_card', $receptionist->id_card) }}"
                                               class="w-full border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 px-4 py-2 @error('id_card') border-red-500 @enderror">
                                        @error('id_card') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                        <input type="email" name="email" value="{{ old('email', $receptionist->email) }}"
                                               class="w-full border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 px-4 py-2 @error('email') border-red-500 @enderror">
                                        @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>


                                </div>
                            </div>
                        </div>

                        <!-- Thông tin nhân viên -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                                <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                                    <i class="fa-solid fa-address-card text-orange-500"></i> Thông tin nhân viên
                                </h3>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                     <div>
                                         <label class="block text-sm font-medium text-gray-700 mb-1">Mã nhân viên</label>
                                         <input type="text" disabled readonly value="{{ $receptionist->staffProfile?->employee_code }}"
                                                class="w-full border border-gray-200 bg-gray-50 text-gray-500 rounded-lg px-4 py-2 font-mono cursor-not-allowed">
                                     </div>


                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Phòng ban <span class="text-red-500">*</span></label>
                                        <select name="department" required
                                                class="w-full border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 px-4 py-2 @error('department') border-red-500 @enderror">
                                            <option value="" disabled {{ old('department', $receptionist->staffProfile?->department) ? '' : 'selected' }}>Chọn phòng ban</option>
                                            <option value="Tiếp nhận bệnh nhân" {{ old('department', $receptionist->staffProfile?->department) == 'Tiếp nhận bệnh nhân' ? 'selected' : '' }}>Tiếp nhận bệnh nhân</option>
                                            <option value="Chăm sóc khách hàng" {{ old('department', $receptionist->staffProfile?->department) == 'Chăm sóc khách hàng' ? 'selected' : '' }}>Chăm sóc khách hàng</option>
                                        </select>
                                        @error('department') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">SĐT nội bộ</label>
                                        <input type="text" name="internal_phone" value="{{ old('internal_phone', $receptionist->staffProfile?->internal_phone) }}"
                                               class="w-full border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 px-4 py-2 @error('internal_phone') border-red-500 @enderror">
                                        @error('internal_phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Ngày vào làm</label>
                                        <input type="date" name="start_date" value="{{ old('start_date', $receptionist->staffProfile?->start_date?->format('Y-m-d')) }}" max="{{ date('Y-m-d') }}"
                                               class="w-full border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 px-4 py-2 @error('start_date') border-red-500 @enderror">
                                        @error('start_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Buttons -->
                        <div class="flex items-center justify-end gap-3 pt-2">
                            <a href="{{ route('admin.receptionists.index') }}" class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition">
                                Huỷ bỏ
                            </a>
                            <button type="submit" class="px-6 py-2.5 bg-orange-500 text-white rounded-lg font-medium hover:bg-orange-600 transition flex items-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed" :disabled="loading">
                                <i class="fa-solid fa-spinner fa-spin" x-show="loading" style="display: none;"></i>
                                <i class="fa-solid fa-save" x-show="!loading"></i>
                                <span>Cập nhật</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Cột phải: Thông tin hiện tại (1/3) -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                    <h3 class="font-semibold text-gray-800 mb-4 pb-2 border-b">Thông tin hiện tại</h3>
                    
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-14 h-14 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center text-xl font-bold">
                            {{ $receptionist->avatar_initials ?? mb_substr($receptionist->full_name, 0, 1) }}
                        </div>
                        <div>
                            <p class="font-bold text-gray-900">{{ $receptionist->full_name }}</p>
                            @if ($receptionist->is_active)
                                <span class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">
                                    <i class="fa-solid fa-circle-check"></i> Đang hoạt động
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700">
                                    <i class="fa-solid fa-circle-xmark"></i> Đã khoá
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="space-y-3 pt-4 border-t border-gray-100 text-sm mb-6">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Đăng nhập cuối:</span>
                            <span class="font-medium text-gray-900">{{ $receptionist->last_login_at ? \Carbon\Carbon::parse($receptionist->last_login_at)->format('d/m/Y H:i') : 'Chưa đăng nhập' }}</span>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <form action="{{ route('admin.receptionists.toggle-active', $receptionist->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn {{ $receptionist->is_active ? 'khoá' : 'mở khoá' }} tài khoản lễ tân này?');">
                            @csrf
                            @method('PATCH')
                            @if($receptionist->is_active)
                                <button type="submit" class="w-full px-4 py-2 text-sm font-medium border border-red-200 text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-lock"></i> Khoá tài khoản
                                </button>
                            @else
                                <button type="submit" class="w-full px-4 py-2 text-sm font-medium border border-green-200 text-green-600 bg-green-50 rounded-lg hover:bg-green-100 transition flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-lock-open"></i> Mở khoá tài khoản
                                </button>
                            @endif
                        </form>

                        <a href="{{ route('admin.receptionists.show', $receptionist->id) }}" class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition text-sm font-medium mt-3">
                            <i class="fa-solid fa-eye"></i> Xem hồ sơ chi tiết
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
