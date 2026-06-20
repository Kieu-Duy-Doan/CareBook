<x-layouts.admin title="Thêm bệnh nhân mới">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <nav class="flex text-sm text-gray-500 font-medium">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-gray-900 transition">Dashboard</a>
                <span class="mx-2 text-gray-400">/</span>
                <a href="{{ route('admin.patients.index') }}" class="hover:text-gray-900 transition">Bệnh nhân</a>
                <span class="mx-2 text-gray-400">/</span>
                <span class="text-gray-900">Thêm mới</span>
            </nav>
            <a href="{{ route('admin.patients.index') }}"
                class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition flex items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i> Quay lại
            </a>
        </div>

        <!-- Thông báo hướng dẫn -->
        <div class="bg-blue-50 border border-blue-200 text-blue-800 rounded-lg p-4 flex items-start gap-3">
            <i class="fa-solid fa-circle-info text-blue-500 mt-0.5"></i>
            <div>
                <p class="font-medium mb-1">Hướng dẫn thêm bệnh nhân</p>
                <p class="text-sm text-blue-700">Chỉ <strong>Họ tên</strong>, <strong>Số điện thoại</strong>,
                    <strong>Mật khẩu</strong>, <strong>Ngày sinh</strong> và <strong>Giới tính</strong> là bắt buộc để
                    tạo tài khoản. Các thông tin còn lại trong hồ sơ y tế có thể để trống — bệnh nhân sẽ tự bổ sung chi
                    tiết sau khi đăng nhập vào ứng dụng.</p>
            </div>
        </div>

        <!-- Session Alerts -->
        @if (session('error'))
            <div class="mb-4 flex items-center gap-3 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <form action="{{ route('admin.patients.store') }}" method="POST" x-data="{ loading: false }"
            @submit="loading = true">
            @csrf

            <div class="grid grid-cols-1 gap-6">
                <!-- Thông tin đăng nhập -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                            <i class="fa-solid fa-user-lock text-green-600"></i> Thông tin đăng nhập <span
                                class="text-red-500">*</span>
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Họ và tên -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Họ và tên đầy đủ <span
                                        class="text-red-500">*</span></label>
                                <input type="text" name="full_name" value="{{ old('full_name') }}" required
                                    class="w-full border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 px-4 py-2 @error('full_name') border-red-500 @enderror"
                                    placeholder="VD: Nguyễn Văn A">
                                @error('full_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- SĐT -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại <span
                                        class="text-red-500">*</span> <span
                                        class="text-xs font-normal text-gray-500">(dùng để đăng nhập)</span></label>
                                <input type="text" name="phone" value="{{ old('phone') }}" required
                                    class="w-full border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 px-4 py-2 @error('phone') border-red-500 @enderror"
                                    placeholder="VD: 0901234567">
                                @error('phone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Username -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tên đăng nhập</label>
                                <input type="text" name="username" value="{{ old('username') }}"
                                    class="w-full border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 px-4 py-2 @error('username') border-red-500 @enderror"
                                    placeholder="Để trống hệ thống sẽ tự dùng SĐT">
                                @error('username')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Password -->
                            <div x-data="{ showPass: false }">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu <span
                                        class="text-red-500">*</span></label>
                                <div class="relative">
                                    <input :type="showPass ? 'text' : 'password'" name="password" required
                                        class="w-full border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 px-4 py-2 pr-10 @error('password') border-red-500 @enderror">
                                    <button type="button" @click="showPass = !showPass"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                        <i class="fa-solid" :class="showPass ? 'fa-eye-slash' : 'fa-eye'"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Confirm Password -->
                            <div x-data="{ showPass2: false }">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Xác nhận mật khẩu <span
                                        class="text-red-500">*</span></label>
                                <div class="relative">
                                    <input :type="showPass2 ? 'text' : 'password'" name="password_confirmation" required
                                        class="w-full border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 px-4 py-2 pr-10">
                                    <button type="button" @click="showPass2 = !showPass2"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                        <i class="fa-solid" :class="showPass2 ? 'fa-eye-slash' : 'fa-eye'"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- CCCD -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Số CCCD / CMND <span
                                        class="text-red-500">*</span></label>
                                <input type="text" name="id_card" value="{{ old('id_card') }}" required
                                    class="w-full border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 px-4 py-2 @error('id_card') border-red-500 @enderror"
                                    placeholder="Nhập 9 hoặc 12 số">
                                @error('id_card')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" name="email" value="{{ old('email') }}"
                                    class="w-full border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 px-4 py-2 @error('email') border-red-500 @enderror"
                                    placeholder="VD: email@example.com">
                                @error('email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hồ sơ y tế bản thân -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                        <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                            <i class="fa-solid fa-notes-medical text-green-600"></i> Hồ sơ y tế bản thân
                        </h3>
                        <span
                            class="px-2.5 py-1 bg-yellow-50 text-yellow-700 text-xs font-medium rounded border border-yellow-200">
                            Bắt buộc Ngày sinh & Giới tính
                        </span>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Họ tên trong hồ sơ -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Họ tên trong hồ sơ</label>
                                <input type="text" name="profile_full_name"
                                    value="{{ old('profile_full_name') }}"
                                    class="w-full border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 px-4 py-2 @error('profile_full_name') border-red-500 @enderror"
                                    placeholder="Mặc định lấy từ họ tên tài khoản">
                                @error('profile_full_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- SĐT hồ sơ -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">SĐT liên hệ riêng</label>
                                <input type="text" name="profile_phone" value="{{ old('profile_phone') }}"
                                    class="w-full border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 px-4 py-2 @error('profile_phone') border-red-500 @enderror"
                                    placeholder="Mặc định lấy từ SĐT tài khoản">
                                @error('profile_phone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Ngày sinh -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ngày sinh <span
                                        class="text-red-500">*</span></label>
                                <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}"
                                    max="{{ date('Y-m-d') }}" required
                                    class="w-full border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 px-4 py-2 @error('date_of_birth') border-red-500 @enderror">
                                @error('date_of_birth')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Giới tính -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Giới tính <span
                                        class="text-red-500">*</span></label>
                                <select name="gender" required
                                    class="w-full border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 px-4 py-2 @error('gender') border-red-500 @enderror">
                                    <option value="">-- Chọn giới tính --</option>
                                    <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Nam
                                    </option>
                                    <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Nữ
                                    </option>
                                    <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Khác
                                    </option>
                                </select>
                                @error('gender')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Dân tộc -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Dân tộc</label>
                                <input type="text" name="ethnicity" value="{{ old('ethnicity') }}"
                                    class="w-full border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 px-4 py-2 @error('ethnicity') border-red-500 @enderror">
                                @error('ethnicity')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Nghề nghiệp -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nghề nghiệp</label>
                                <input type="text" name="occupation" value="{{ old('occupation') }}"
                                    class="w-full border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 px-4 py-2 @error('occupation') border-red-500 @enderror">
                                @error('occupation')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Địa chỉ -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Địa chỉ liên hệ</label>
                                <textarea name="address" rows="2"
                                    class="w-full border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 px-4 py-2 @error('address') border-red-500 @enderror">{{ old('address') }}</textarea>
                                @error('address')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Thông tin bảo hiểm y tế -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                        <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                            <i class="fa-solid fa-id-card-clip text-green-600"></i> Thông tin bảo hiểm y tế
                        </h3>
                        <span
                            class="px-2.5 py-1 bg-gray-200 text-gray-600 text-xs font-medium rounded border border-gray-300">
                            Tùy chọn
                        </span>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Mã thẻ BHYT</label>
                                <input type="text" name="insurance_code" value="{{ old('insurance_code') }}"
                                    class="w-full border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 px-4 py-2 font-mono @error('insurance_code') border-red-500 @enderror">
                                @error('insurance_code')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ngày hết hạn thẻ</label>
                                <input type="date" name="insurance_expiry" value="{{ old('insurance_expiry') }}"
                                    class="w-full border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 px-4 py-2 @error('insurance_expiry') border-red-500 @enderror">
                                @error('insurance_expiry')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nơi đăng ký KCB ban
                                    đầu</label>
                                <input type="text" name="insurance_place" value="{{ old('insurance_place') }}"
                                    class="w-full border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 px-4 py-2 @error('insurance_place') border-red-500 @enderror"
                                    placeholder="VD: Bệnh viện Đa khoa Tỉnh...">
                                @error('insurance_place')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ghi chú y tế -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                        <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                            <i class="fa-solid fa-file-medical text-green-600"></i> Ghi chú y tế
                        </h3>
                        <span
                            class="px-2.5 py-1 bg-gray-200 text-gray-600 text-xs font-medium rounded border border-gray-300">
                            Tùy chọn
                        </span>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ghi chú triệu chứng, dị
                                    ứng, thông tin khác...</label>
                                <textarea name="symptom_notes" rows="4"
                                    class="w-full border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 px-4 py-2 @error('symptom_notes') border-red-500 @enderror">{{ old('symptom_notes') }}</textarea>
                                @error('symptom_notes')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Buttons -->
                <div class="flex items-center justify-end gap-3 mt-4">
                    <a href="{{ route('admin.patients.index') }}"
                        class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition">
                        Huỷ bỏ
                    </a>
                    <button type="submit"
                        class="px-6 py-2.5 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition flex items-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed"
                        :disabled="loading">
                        <i class="fa-solid fa-spinner fa-spin" x-show="loading" style="display: none;"></i>
                        <i class="fa-solid fa-save" x-show="!loading"></i>
                        <span>Lưu bệnh nhân</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-layouts.admin>
