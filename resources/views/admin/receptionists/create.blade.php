<x-layouts.admin title="Thêm lễ tân mới">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <nav class="flex text-sm text-gray-500 font-medium">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-gray-900 transition">Dashboard</a>
                <span class="mx-2 text-gray-400">/</span>
                <a href="{{ route('admin.receptionists.index') }}" class="hover:text-gray-900 transition">Lễ tân</a>
                <span class="mx-2 text-gray-400">/</span>
                <span class="text-gray-900">Thêm mới</span>
            </nav>
            <a href="{{ route('admin.receptionists.index') }}" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition flex items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i> Quay lại
            </a>
        </div>

        <!-- Session Alerts -->
        @if(session('error'))
        <div class="mb-4 flex items-center gap-3 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        <form action="{{ route('admin.receptionists.store') }}" method="POST" x-data="{ loading: false }" @submit="loading = true">
            @csrf
            
            <div class="grid grid-cols-1 gap-6">
                <!-- Thông tin đăng nhập -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                            <i class="fa-solid fa-user-lock text-orange-500"></i> Thông tin đăng nhập
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Họ và tên -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Họ và tên đầy đủ <span class="text-red-500">*</span></label>
                                <input type="text" name="full_name" value="{{ old('full_name') }}" required
                                       class="w-full border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 px-4 py-2 @error('full_name') border-red-500 @enderror"
                                       placeholder="VD: Nguyễn Thị B">
                                @error('full_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <!-- SĐT -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại <span class="text-red-500">*</span> <span class="text-xs font-normal text-gray-500">(dùng để đăng nhập)</span></label>
                                <input type="text" name="phone" value="{{ old('phone') }}" required
                                       class="w-full border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 px-4 py-2 @error('phone') border-red-500 @enderror"
                                       placeholder="VD: 0901234567">
                                @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <!-- Username -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tên đăng nhập <span class="text-red-500">*</span></label>
                                <input type="text" name="username" value="{{ old('username') }}" required
                                       class="w-full border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 px-4 py-2 @error('username') border-red-500 @enderror"
                                       placeholder="VD: lt_nguyenthib">
                                @error('username') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <!-- CCCD -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Số CCCD</label>
                                <input type="text" name="id_card" value="{{ old('id_card') }}"
                                       class="w-full border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 px-4 py-2 @error('id_card') border-red-500 @enderror"
                                       placeholder="VD: 079123456789">
                                @error('id_card') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" name="email" value="{{ old('email') }}"
                                       class="w-full border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 px-4 py-2 @error('email') border-red-500 @enderror"
                                       placeholder="VD: lt_nguyenthib@example.com">
                                @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <!-- Password -->
                            <div x-data="{ showPass: false }">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <input :type="showPass ? 'text' : 'password'" name="password" required
                                           class="w-full border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 px-4 py-2 pr-10 @error('password') border-red-500 @enderror">
                                    <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                        <i class="fa-solid" :class="showPass ? 'fa-eye-slash' : 'fa-eye'"></i>
                                    </button>
                                </div>
                                @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <!-- Confirm Password -->
                            <div x-data="{ showPass2: false }">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Xác nhận mật khẩu <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <input :type="showPass2 ? 'text' : 'password'" name="password_confirmation" required
                                           class="w-full border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 px-4 py-2 pr-10">
                                    <button type="button" @click="showPass2 = !showPass2" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                        <i class="fa-solid" :class="showPass2 ? 'fa-eye-slash' : 'fa-eye'"></i>
                                    </button>
                                </div>
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


                            <!-- Phòng ban -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phòng ban <span class="text-red-500">*</span></label>
                                <select name="department" required
                                        class="w-full border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 px-4 py-2 @error('department') border-red-500 @enderror">
                                    <option value="" disabled {{ old('department') ? '' : 'selected' }}>Chọn phòng ban</option>
                                    <option value="Tiếp nhận bệnh nhân" {{ old('department') == 'Tiếp nhận bệnh nhân' ? 'selected' : '' }}>Tiếp nhận bệnh nhân</option>
                                    <option value="Chăm sóc khách hàng" {{ old('department') == 'Chăm sóc khách hàng' ? 'selected' : '' }}>Chăm sóc khách hàng</option>
                                </select>
                                @error('department') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <!-- SĐT nội bộ -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">SĐT nội bộ</label>
                                <input type="text" name="internal_phone" value="{{ old('internal_phone') }}"
                                       class="w-full border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 px-4 py-2 @error('internal_phone') border-red-500 @enderror"
                                       placeholder="VD: 101, 102">
                                @error('internal_phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <!-- Ngày vào làm -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ngày vào làm</label>
                                <input type="date" name="start_date" value="{{ old('start_date') }}" max="{{ date('Y-m-d') }}"
                                       class="w-full border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 px-4 py-2 @error('start_date') border-red-500 @enderror">
                                @error('start_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Buttons -->
                <div class="flex items-center justify-end gap-3 mt-4">
                    <a href="{{ route('admin.receptionists.index') }}" class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition">
                        Huỷ bỏ
                    </a>
                    <button type="submit" class="px-6 py-2.5 bg-orange-500 text-white rounded-lg font-medium hover:bg-orange-600 transition flex items-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed" :disabled="loading">
                        <i class="fa-solid fa-spinner fa-spin" x-show="loading" style="display: none;"></i>
                        <i class="fa-solid fa-save" x-show="!loading"></i>
                        <span>Lưu lễ tân</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-layouts.admin>
