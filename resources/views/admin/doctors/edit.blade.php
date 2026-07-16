<x-layouts.admin title="Chỉnh sửa — {{ $doctor->full_title }}">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <nav class="flex text-sm text-gray-500 font-medium">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-gray-900 transition">Dashboard</a>
                <span class="mx-2 text-gray-400">/</span>
                <a href="{{ route('admin.doctors.index') }}" class="hover:text-gray-900 transition">Bác sĩ</a>
                <span class="mx-2 text-gray-400">/</span>
                <a href="{{ route('admin.doctors.show', $doctor->id) }}" class="hover:text-gray-900 transition">{{ $doctor->full_title }}</a>
                <span class="mx-2 text-gray-400">/</span>
                <span class="text-gray-900">Chỉnh sửa</span>
            </nav>
            <a href="{{ route('admin.doctors.index') }}" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition flex items-center gap-2">
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
                <form action="{{ route('admin.doctors.update', $doctor->id) }}" method="POST" x-data="{ loading: false }" @submit="loading = true">
                    @csrf
                    @method('PUT')
                    
                    <div class="space-y-6">
                        <!-- Thông tin đăng nhập -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                                <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                                    <i class="fa-solid fa-user-lock text-purple-600"></i> Thông tin đăng nhập
                                </h3>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Họ và tên đầy đủ <span class="text-red-500">*</span></label>
                                        <input type="text" name="full_name" value="{{ old('full_name', $doctor->user->full_name) }}" required
                                               class="w-full border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500 px-4 py-2 @error('full_name') border-red-500 @enderror">
                                        @error('full_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại <span class="text-red-500">*</span></label>
                                        <input type="text" name="phone" value="{{ old('phone', $doctor->user->phone) }}" required
                                               class="w-full border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500 px-4 py-2 @error('phone') border-red-500 @enderror">
                                        @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Tên đăng nhập <span class="text-red-500">*</span></label>
                                        <input type="text" name="username" value="{{ old('username', $doctor->user->username) }}" required
                                               class="w-full border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500 px-4 py-2 @error('username') border-red-500 @enderror">
                                        @error('username') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                        <input type="email" name="email" value="{{ old('email', $doctor->user->email) }}"
                                               class="w-full border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500 px-4 py-2 @error('email') border-red-500 @enderror">
                                        @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                    

                                </div>
                            </div>
                        </div>

                        <!-- Hồ sơ chuyên môn -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                                <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                                    <i class="fa-solid fa-user-doctor text-purple-600"></i> Hồ sơ chuyên môn
                                </h3>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Mã bác sĩ <span class="text-red-500">*</span></label>
                                        <input type="hidden" name="doctor_code" value="{{ old('doctor_code', $doctor->doctor_code) }}">
                                        <input disabled type="text" value="{{ old('doctor_code', $doctor->doctor_code) }}"
                                               class="w-full border border-gray-300 rounded-lg px-4 py-2 font-mono bg-gray-100 text-gray-500 cursor-not-allowed opacity-70">
                                        @error('doctor_code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <!-- Học hàm -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Học hàm <span class="text-red-500">*</span></label>
                                        <select name="academic_rank" required class="w-full border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500 px-4 py-2 @error('academic_rank') border-red-500 @enderror">
                                            <option value="none" {{ old('academic_rank', $doctor->academic_rank) == 'none' ? 'selected' : '' }}>Không có</option>
                                            <option value="PGS" {{ old('academic_rank', $doctor->academic_rank) == 'PGS' ? 'selected' : '' }}>Phó Giáo sư</option>
                                            <option value="GS" {{ old('academic_rank', $doctor->academic_rank) == 'GS' ? 'selected' : '' }}>Giáo sư</option>
                                        </select>
                                        @error('academic_rank') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <!-- Học vị -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Học vị <span class="text-red-500">*</span></label>
                                        <select name="degree" required class="w-full border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500 px-4 py-2 @error('degree') border-red-500 @enderror">
                                            <option value="">-- Chọn học vị --</option>
                                            <option value="BS" {{ old('degree', $doctor->degree) == 'BS' ? 'selected' : '' }}>Bác sĩ</option>
                                            <option value="ThS" {{ old('degree', $doctor->degree) == 'ThS' ? 'selected' : '' }}>Thạc sĩ</option>
                                            <option value="TS" {{ old('degree', $doctor->degree) == 'TS' ? 'selected' : '' }}>Tiến sĩ</option>
                                            <option value="BSCK1" {{ old('degree', $doctor->degree) == 'BSCK1' ? 'selected' : '' }}>Bác sĩ Chuyên khoa 1</option>
                                            <option value="BSCK2" {{ old('degree', $doctor->degree) == 'BSCK2' ? 'selected' : '' }}>Bác sĩ Chuyên khoa 2</option>
                                            <option value="BSNT" {{ old('degree', $doctor->degree) == 'BSNT' ? 'selected' : '' }}>Bác sĩ Nội trú</option>
                                        </select>
                                        @error('degree') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <!-- Cấp độ / Chức vụ Lâm sàng -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Chức vụ lâm sàng <span class="text-red-500">*</span></label>
                                        <select name="current_position" required class="w-full border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500 px-4 py-2 @error('current_position') border-red-500 @enderror">
                                            <option value="">-- Chọn chức vụ lâm sàng --</option>
                                            <option value="INTERN" {{ old('current_position', $doctor->current_position) == 'INTERN' ? 'selected' : '' }}>Bác sĩ Nội trú / Thực hành</option>
                                            <option value="ATTENDING" {{ old('current_position', $doctor->current_position) == 'ATTENDING' ? 'selected' : '' }}>Bác sĩ Điều trị</option>
                                            <option value="CONSULTANT" {{ old('current_position', $doctor->current_position) == 'CONSULTANT' ? 'selected' : '' }}>Bác sĩ Hội chẩn / Ca trưởng</option>
                                            <option value="DEPARTMENT_HEAD" {{ old('current_position', $doctor->current_position) == 'DEPARTMENT_HEAD' ? 'selected' : '' }}>Phó khoa / Trưởng khoa Lâm sàng</option>
                                            <option value="EXPERT" {{ old('current_position', $doctor->current_position) == 'EXPERT' ? 'selected' : '' }}>Giám đốc Chuyên môn / Chuyên gia</option>
                                        </select>
                                        @error('current_position') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Số năm kinh nghiệm</label>
                                        <input type="number" name="experience_years" value="{{ old('experience_years', $doctor->experience_years) }}" min="0" max="60"
                                               class="w-full border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500 px-4 py-2 @error('experience_years') border-red-500 @enderror">
                                        @error('experience_years') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Số chứng chỉ hành nghề</label>
                                        <input type="text" name="license_number" value="{{ old('license_number', $doctor->license_number) }}"
                                               class="w-full border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500 px-4 py-2 @error('license_number') border-red-500 @enderror">
                                        @error('license_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Lĩnh vực chuyên trị</label>
                                        <textarea name="expertise" rows="3"
                                                  class="w-full border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500 px-4 py-2 @error('expertise') border-red-500 @enderror">{{ old('expertise', $doctor->expertise) }}</textarea>
                                        @error('expertise') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Giới thiệu bản thân</label>
                                        <textarea name="bio" rows="4"
                                                  class="w-full border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500 px-4 py-2 @error('bio') border-red-500 @enderror">{{ old('bio', $doctor->bio) }}</textarea>
                                        @error('bio') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Chuyên khoa phụ trách -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                                <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                                    <i class="fa-solid fa-stethoscope text-purple-600"></i> Chuyên khoa phụ trách
                                </h3>
                            </div>
                            <div class="p-6">
                                @error('specialty_ids') <p class="mb-3 text-sm text-red-600">{{ $message }}</p> @enderror
                                @error('primary_specialty_id') <p class="mb-3 text-sm text-red-600">{{ $message }}</p> @enderror
                                
                                <div x-data="{ selectedIds: {{ json_encode(old('specialty_ids', $selectedSpecialtyIds)) }}, primaryId: {{ old('primary_specialty_id', $primarySpecialtyId ?? 'null') }} }">
                                    <!-- Checkbox group -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        @foreach($specialties as $specialty)
                                        <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer transition select-none"
                                               :class="selectedIds.includes({{ $specialty->id }}) || selectedIds.includes('{{ $specialty->id }}') ? 'border-purple-500 bg-purple-50 text-purple-800' : 'border-gray-200 text-gray-700 hover:bg-gray-50'">
                                            <input type="checkbox" name="specialty_ids[]" value="{{ $specialty->id }}"
                                                   x-model="selectedIds" class="hidden">
                                            <i class="fa-solid fa-check text-purple-600" x-show="selectedIds.includes({{ $specialty->id }}) || selectedIds.includes('{{ $specialty->id }}')"></i>
                                            <i class="fa-regular fa-square text-gray-300" x-show="!(selectedIds.includes({{ $specialty->id }}) || selectedIds.includes('{{ $specialty->id }}'))"></i>
                                            {{ $specialty->name }}
                                        </label>
                                        @endforeach
                                    </div>

                                    <!-- Chọn chuyên khoa chính -->
                                    <div class="mt-6 pt-4 border-t border-gray-100" x-show="selectedIds.length > 0" x-transition>
                                        <label class="font-medium text-gray-800">Chọn chuyên khoa chính <span class="text-red-500">*</span></label>
                                        <p class="text-xs text-gray-500 mt-1 mb-3">Chuyên khoa chính sẽ được hiển thị nổi bật trên hồ sơ bác sĩ.</p>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                            @foreach($specialties as $specialty)
                                            <label x-show="selectedIds.includes({{ $specialty->id }}) || selectedIds.includes('{{ $specialty->id }}')"
                                                   class="flex items-center gap-2 p-3 border rounded-lg cursor-pointer transition"
                                                   :class="primaryId == {{ $specialty->id }} ? 'border-green-500 bg-green-50 text-green-800' : 'border-gray-200 hover:bg-gray-50'">
                                                <input type="radio" name="primary_specialty_id" value="{{ $specialty->id }}"
                                                       x-model="primaryId" class="w-4 h-4 text-green-600 border-gray-300 focus:ring-green-500">
                                                {{ $specialty->name }}
                                            </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Buttons -->
                        <div class="flex items-center justify-end gap-3 pt-2">
                            <a href="{{ route('admin.doctors.index') }}" class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition">
                                Huỷ bỏ
                            </a>
                            <button type="submit" class="px-6 py-2.5 bg-purple-600 text-white rounded-lg font-medium hover:bg-purple-700 transition flex items-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed" :disabled="loading">
                                <i class="fa-solid fa-spinner fa-spin" x-show="loading" style="display: none;"></i>
                                <i class="fa-solid fa-save" x-show="!loading"></i>
                                <span>Cập nhật</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Cột phải (1/3) -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                    <h3 class="font-semibold text-gray-800 mb-4 pb-2 border-b">Thông tin hiện tại</h3>
                    
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-14 h-14 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center text-xl font-bold">
                            {{ $doctor->user->avatar_initials ?? mb_substr($doctor->user->full_name, 0, 1) }}
                        </div>
                        <div>
                            <p class="font-bold text-gray-900">{{ $doctor->user->full_name }}</p>
                            @if ($doctor->user->is_active)
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
                            <span class="font-medium text-gray-900">{{ $doctor->user->last_login_at ? \Carbon\Carbon::parse($doctor->user->last_login_at)->format('d/m/Y H:i') : 'Chưa đăng nhập' }}</span>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <form action="{{ route('admin.doctors.toggle-active', $doctor->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn {{ $doctor->user->is_active ? 'khoá' : 'mở khoá' }} tài khoản bác sĩ này?');">
                            @csrf
                            @method('PATCH')
                            @if($doctor->user->is_active)
                                <button type="submit" class="w-full px-4 py-2 text-sm font-medium border border-red-200 text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-lock"></i> Khoá tài khoản
                                </button>
                            @else
                                <button type="submit" class="w-full px-4 py-2 text-sm font-medium border border-green-200 text-green-600 bg-green-50 rounded-lg hover:bg-green-100 transition flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-lock-open"></i> Mở khoá tài khoản
                                </button>
                            @endif
                        </form>

                        <a href="{{ route('admin.doctors.show', $doctor->id) }}" class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition text-sm font-medium mt-3">
                            <i class="fa-solid fa-eye"></i> Xem hồ sơ chi tiết
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
