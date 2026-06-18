<x-layouts.admin title="Chỉnh sửa bệnh nhân — {{ $patient->full_name }}">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <nav class="flex text-sm text-gray-500 font-medium">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-gray-900 transition">Dashboard</a>
                <span class="mx-2 text-gray-400">/</span>
                <a href="{{ route('admin.patients.index') }}" class="hover:text-gray-900 transition">Bệnh nhân</a>
                <span class="mx-2 text-gray-400">/</span>
                <a href="{{ route('admin.patients.show', $patient->id) }}" class="hover:text-gray-900 transition">{{ $patient->full_name }}</a>
                <span class="mx-2 text-gray-400">/</span>
                <span class="text-gray-900">Chỉnh sửa</span>
            </nav>
            <a href="{{ route('admin.patients.index') }}" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition flex items-center gap-2">
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
                <form action="{{ route('admin.patients.update', $patient->id) }}" method="POST" x-data="{ loading: false }" @submit="loading = true">
                    @csrf
                    @method('PUT')
                    
                    <div class="space-y-6">
                        <!-- Thông tin đăng nhập -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                                <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                                    <i class="fa-solid fa-user-lock text-green-600"></i> Thông tin đăng nhập <span class="text-red-500">*</span>
                                </h3>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Họ và tên đầy đủ <span class="text-red-500">*</span></label>
                                        <input type="text" name="full_name" value="{{ old('full_name', $patient->full_name) }}" required
                                               class="w-full border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 px-4 py-2 @error('full_name') border-red-500 @enderror">
                                        @error('full_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại <span class="text-red-500">*</span></label>
                                        <input type="text" name="phone" value="{{ old('phone', $patient->phone) }}" required
                                               class="w-full border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 px-4 py-2 @error('phone') border-red-500 @enderror">
                                        @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Tên đăng nhập</label>
                                        <input type="text" name="username" value="{{ old('username', $patient->username) }}"
                                               class="w-full border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 px-4 py-2 @error('username') border-red-500 @enderror">
                                        @error('username') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Số CCCD / CMND <span class="text-red-500">*</span></label>
                                        <input type="text" name="id_card" value="{{ old('id_card', $patient->id_card) }}" required
                                               class="w-full border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 px-4 py-2 @error('id_card') border-red-500 @enderror">
                                        @error('id_card') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                        <input type="email" name="email" value="{{ old('email', $patient->email) }}"
                                               class="w-full border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 px-4 py-2 @error('email') border-red-500 @enderror">
                                        @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
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
                                <span class="px-2.5 py-1 bg-yellow-50 text-yellow-700 text-xs font-medium rounded border border-yellow-200">
                                    Bắt buộc Ngày sinh & Giới tính
                                </span>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Họ tên trong hồ sơ</label>
                                        <input type="text" name="profile_full_name" value="{{ old('profile_full_name', $selfProfile?->full_name) }}"
                                               class="w-full border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 px-4 py-2 @error('profile_full_name') border-red-500 @enderror">
                                        @error('profile_full_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">SĐT liên hệ riêng</label>
                                        <input type="text" name="profile_phone" value="{{ old('profile_phone', $selfProfile?->phone) }}"
                                               class="w-full border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 px-4 py-2 @error('profile_phone') border-red-500 @enderror">
                                        @error('profile_phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Ngày sinh <span class="text-red-500">*</span></label>
                                        <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $selfProfile?->date_of_birth?->format('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required
                                               class="w-full border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 px-4 py-2 @error('date_of_birth') border-red-500 @enderror">
                                        @error('date_of_birth') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Giới tính <span class="text-red-500">*</span></label>
                                        <select name="gender" required class="w-full border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 px-4 py-2 @error('gender') border-red-500 @enderror">
                                            <option value="">-- Chọn giới tính --</option>
                                            <option value="male" {{ old('gender', $selfProfile?->gender) == 'male' ? 'selected' : '' }}>Nam</option>
                                            <option value="female" {{ old('gender', $selfProfile?->gender) == 'female' ? 'selected' : '' }}>Nữ</option>
                                            <option value="other" {{ old('gender', $selfProfile?->gender) == 'other' ? 'selected' : '' }}>Khác</option>
                                        </select>
                                        @error('gender') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <!-- Dân tộc -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Dân tộc</label>
                                        <input type="text" name="ethnicity" value="{{ old('ethnicity', $selfProfile?->ethnicity) }}"
                                               class="w-full border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 px-4 py-2 @error('ethnicity') border-red-500 @enderror">
                                        @error('ethnicity') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Nghề nghiệp</label>
                                        <input type="text" name="occupation" value="{{ old('occupation', $selfProfile?->occupation) }}"
                                               class="w-full border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 px-4 py-2 @error('occupation') border-red-500 @enderror">
                                        @error('occupation') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Địa chỉ liên hệ</label>
                                        <textarea name="address" rows="2"
                                                  class="w-full border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 px-4 py-2 @error('address') border-red-500 @enderror">{{ old('address', $selfProfile?->address) }}</textarea>
                                        @error('address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Thông tin bảo hiểm y tế -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                                <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                                    <i class="fa-solid fa-id-card-clip text-green-600"></i> Thông tin bảo hiểm y tế
                                </h3>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Mã thẻ BHYT</label>
                                        <input type="text" name="insurance_code" value="{{ old('insurance_code', $selfProfile?->insurance_code) }}"
                                               class="w-full border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 px-4 py-2 font-mono @error('insurance_code') border-red-500 @enderror">
                                        @error('insurance_code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Ngày hết hạn thẻ</label>
                                        <input type="date" name="insurance_expiry" value="{{ old('insurance_expiry', $selfProfile?->insurance_expiry?->format('Y-m-d')) }}"
                                               class="w-full border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 px-4 py-2 @error('insurance_expiry') border-red-500 @enderror">
                                        @error('insurance_expiry') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Nơi đăng ký KCB ban đầu</label>
                                        <input type="text" name="insurance_place" value="{{ old('insurance_place', $selfProfile?->insurance_place) }}"
                                               class="w-full border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 px-4 py-2 @error('insurance_place') border-red-500 @enderror">
                                        @error('insurance_place') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Ghi chú y tế -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                                <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                                    <i class="fa-solid fa-file-medical text-green-600"></i> Ghi chú y tế
                                </h3>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Ghi chú triệu chứng, dị ứng, thông tin khác...</label>
                                        <textarea name="symptom_notes" rows="4"
                                                  class="w-full border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 px-4 py-2 @error('symptom_notes') border-red-500 @enderror">{{ old('symptom_notes', $selfProfile?->symptom_notes) }}</textarea>
                                        @error('symptom_notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Buttons -->
                        <div class="flex items-center justify-end gap-3 pt-2">
                            <a href="{{ route('admin.patients.index') }}" class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition">
                                Huỷ bỏ
                            </a>
                            <button type="submit" class="px-6 py-2.5 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition flex items-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed" :disabled="loading">
                                <i class="fa-solid fa-spinner fa-spin" x-show="loading" style="display: none;"></i>
                                <i class="fa-solid fa-save" x-show="!loading"></i>
                                <span>Cập nhật thông tin</span>
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
                        <div class="w-14 h-14 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xl font-bold">
                            {{ $patient->avatar_initials ?? mb_substr($patient->full_name, 0, 1) }}
                        </div>
                        <div>
                            <p class="font-bold text-gray-900">{{ $patient->full_name }}</p>
                            @if ($patient->is_active)
                                <span class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700 border border-green-200">
                                    <i class="fa-solid fa-circle-check"></i> Đang hoạt động
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700 border border-red-200">
                                    <i class="fa-solid fa-circle-xmark"></i> Đã khoá
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="space-y-3 pt-4 border-t border-gray-100 text-sm mb-6">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Đăng nhập cuối:</span>
                            <span class="font-medium text-gray-900">{{ $patient->last_login_at ? \Carbon\Carbon::parse($patient->last_login_at)->format('d/m/Y H:i') : 'Chưa đăng nhập' }}</span>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <form action="{{ route('admin.patients.toggle-active', $patient->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn {{ $patient->is_active ? 'khoá' : 'mở khoá' }} tài khoản bệnh nhân này?');">
                            @csrf
                            @method('PATCH')
                            @if($patient->is_active)
                                <button type="submit" class="w-full px-4 py-2 text-sm font-medium border border-red-200 text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-lock"></i> Khoá tài khoản
                                </button>
                            @else
                                <button type="submit" class="w-full px-4 py-2 text-sm font-medium border border-green-200 text-green-600 bg-green-50 rounded-lg hover:bg-green-100 transition flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-lock-open"></i> Mở khoá tài khoản
                                </button>
                            @endif
                        </form>

                        <a href="{{ route('admin.patients.show', $patient->id) }}" class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-green-50 text-green-700 rounded-lg hover:bg-green-100 transition text-sm font-medium mt-3 border border-green-200">
                            <i class="fa-solid fa-eye"></i> Xem hồ sơ chi tiết
                        </a>
                    </div>
                </div>

                @php
                    $relativeCount = $patient->patientProfiles->where('is_self', 0)->count();
                @endphp
                @if($relativeCount > 0)
                <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 flex items-start gap-3">
                    <i class="fa-solid fa-users text-blue-500 mt-1"></i>
                    <div>
                        <p class="font-medium text-blue-900 text-sm">Hồ sơ người thân</p>
                        <p class="text-sm text-blue-700 mt-1">Bệnh nhân này có <strong>{{ $relativeCount }} hồ sơ người thân</strong>. Bạn có thể xem và quản lý tại trang chi tiết.</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.admin>
