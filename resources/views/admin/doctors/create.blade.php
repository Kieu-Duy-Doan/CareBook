<x-layouts.admin title="Thêm bác sĩ mới">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <nav class="flex text-sm text-gray-500 font-medium">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-gray-900 transition">Dashboard</a>
                <span class="mx-2 text-gray-400">/</span>
                <a href="{{ route('admin.doctors.index') }}" class="hover:text-gray-900 transition">Bác sĩ</a>
                <span class="mx-2 text-gray-400">/</span>
                <span class="text-gray-900">Thêm mới</span>
            </nav>
            <a href="{{ route('admin.doctors.index') }}" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition flex items-center gap-2">
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

        <form action="{{ route('admin.doctors.store') }}" method="POST" x-data="{ loading: false }" @submit="loading = true">
            @csrf
            
            <div class="grid grid-cols-1 gap-6">
                <!-- Thông tin đăng nhập -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                            <i class="fa-solid fa-user-lock text-purple-600"></i> Thông tin đăng nhập
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Họ và tên -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Họ và tên đầy đủ <span class="text-red-500">*</span></label>
                                <input type="text" name="full_name" value="{{ old('full_name') }}" required
                                       class="w-full border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500 px-4 py-2 @error('full_name') border-red-500 @enderror"
                                       placeholder="VD: Nguyễn Văn A">
                                @error('full_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <!-- SĐT -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại <span class="text-red-500">*</span> <span class="text-xs font-normal text-gray-500">(dùng để đăng nhập)</span></label>
                                <input type="tel" name="phone" value="{{ old('phone') }}" required
                                       class="w-full border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500 px-4 py-2 @error('phone') border-red-500 @enderror"
                                       placeholder="VD: 0901234567">
                                @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <!-- Username -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tên đăng nhập <span class="text-red-500">*</span></label>
                                <input type="text" name="username" value="{{ old('username') }}" required
                                       class="w-full border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500 px-4 py-2 @error('username') border-red-500 @enderror"
                                       placeholder="VD: bs_nguyenvana">
                                @error('username') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" name="email" value="{{ old('email') }}"
                                       class="w-full border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500 px-4 py-2 @error('email') border-red-500 @enderror"
                                       placeholder="VD: bs_nguyenvana@example.com">
                                @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            
                            <div class="hidden md:block"></div>

                            <!-- Password -->
                            <div x-data="{ showPass: false }">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <input :type="showPass ? 'text' : 'password'" name="password" required minlength="8"
                                           class="w-full border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500 px-4 py-2 pr-10 @error('password') border-red-500 @enderror">
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
                                           class="w-full border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500 px-4 py-2 pr-10">
                                    <button type="button" @click="showPass2 = !showPass2" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                        <i class="fa-solid" :class="showPass2 ? 'fa-eye-slash' : 'fa-eye'"></i>
                                    </button>
                                </div>
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
                            <!-- Cấp độ -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Cấp độ chuyên môn <span class="text-red-500">*</span></label>
                                <input type="text" name="level" value="{{ old('level') }}" required
                                       class="w-full border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500 px-4 py-2 @error('level') border-red-500 @enderror"
                                       placeholder="VD: Bác sĩ, Bác sĩ chính">
                                @error('level') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <!-- Học hàm/Học vị -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Học hàm học vị</label>
                                <select name="academic_title" class="w-full border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500 px-4 py-2 @error('academic_title') border-red-500 @enderror">
                                    <option value="">-- Chọn học hàm học vị --</option>
                                    <option value="BS." {{ old('academic_title') == 'BS.' ? 'selected' : '' }}>Bác sĩ (BS.)</option>
                                    <option value="BSCK1." {{ old('academic_title') == 'BSCK1.' ? 'selected' : '' }}>Bác sĩ Chuyên khoa 1 (BSCK1.)</option>
                                    <option value="BSCK2." {{ old('academic_title') == 'BSCK2.' ? 'selected' : '' }}>Bác sĩ Chuyên khoa 2 (BSCK2.)</option>
                                    <option value="ThS." {{ old('academic_title') == 'ThS.' ? 'selected' : '' }}>Thạc sĩ (ThS.)</option>
                                    <option value="TS." {{ old('academic_title') == 'TS.' ? 'selected' : '' }}>Tiến sĩ (TS.)</option>
                                    <option value="PGS.TS." {{ old('academic_title') == 'PGS.TS.' ? 'selected' : '' }}>Phó Giáo sư Tiến sĩ (PGS.TS.)</option>
                                    <option value="GS.TS." {{ old('academic_title') == 'GS.TS.' ? 'selected' : '' }}>Giáo sư Tiến sĩ (GS.TS.)</option>
                                </select>
                                @error('academic_title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <!-- Số năm kinh nghiệm -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Số năm kinh nghiệm</label>
                                <input type="number" name="experience_years" value="{{ old('experience_years') }}" min="0" max="60"
                                       class="w-full border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500 px-4 py-2 @error('experience_years') border-red-500 @enderror"
                                       placeholder="VD: 10">
                                @error('experience_years') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <!-- Số chứng chỉ hành nghề -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Số chứng chỉ hành nghề <span class="text-red-500">*</span></label>
                                <input type="text" name="license_number" value="{{ old('license_number') }}" required
                                       class="w-full border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500 px-4 py-2 @error('license_number') border-red-500 @enderror"
                                       placeholder="VD: 12345/BYT-CCHN">
                                @error('license_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <!-- Lĩnh vực chuyên trị -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Lĩnh vực chuyên trị</label>
                                <textarea name="expertise" rows="3" maxlength="2000"
                                          class="w-full border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500 px-4 py-2 @error('expertise') border-red-500 @enderror"
                                          placeholder="Nhập các lĩnh vực chuyên môn sâu...">{{ old('expertise') }}</textarea>
                                @error('expertise') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <!-- Giới thiệu bản thân -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Giới thiệu bản thân</label>
                                <textarea name="bio" rows="4" maxlength="2000"
                                          class="w-full border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500 px-4 py-2 @error('bio') border-red-500 @enderror"
                                          placeholder="Nhập tóm tắt tiểu sử, quá trình công tác...">{{ old('bio') }}</textarea>
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
                        
                        <div x-data="{ selectedIds: {{ json_encode(old('specialty_ids', [])) }}, primaryId: {{ old('primary_specialty_id', 'null') }} }"
                             x-init="$watch('selectedIds', value => {
                                 if (value.length > 0 && !value.includes(primaryId?.toString()) && !value.includes(Number(primaryId))) {
                                     primaryId = value[0];
                                 } else if (value.length === 0) {
                                     primaryId = null;
                                 }
                             })">
                            <!-- Checkbox group -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach($specialties as $specialty)
                                <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer transition select-none"
                                       :class="selectedIds.includes('{{ $specialty->id }}') ? 'border-purple-500 bg-purple-50 text-purple-800' : 'border-gray-200 text-gray-700 hover:bg-gray-50'">
                                    <input type="checkbox" name="specialty_ids[]" value="{{ $specialty->id }}"
                                           x-model="selectedIds" class="hidden">
                                    <i class="fa-solid fa-check text-purple-600" x-show="selectedIds.includes('{{ $specialty->id }}')"></i>
                                    <i class="fa-regular fa-square text-gray-300" x-show="!selectedIds.includes('{{ $specialty->id }}')"></i>
                                    {{ $specialty->name }}
                                </label>
                                @endforeach
                            </div>

                            <!-- Chọn chuyên khoa chính -->
                            <div class="mt-6 pt-4 border-t border-gray-100" x-show="selectedIds.length > 0" x-transition>
                                <label class="font-medium text-gray-800">Chọn chuyên khoa chính <span class="text-red-500">*</span></label>
                                <p class="text-xs text-gray-500 mt-1 mb-3">Chuyên khoa chính sẽ được hiển thị nổi bật trên hồ sơ bác sĩ.</p>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                    @foreach($specialties as $specialty)
                                    <label x-show="selectedIds.includes('{{ $specialty->id }}')"
                                           class="flex items-center gap-2 p-3 border rounded-lg cursor-pointer transition"
                                           :class="primaryId == '{{ $specialty->id }}' ? 'border-green-500 bg-green-50 text-green-800' : 'border-gray-200 hover:bg-gray-50'">
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
                <div class="flex items-center justify-end gap-3 mt-4">
                    <a href="{{ route('admin.doctors.index') }}" class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition">
                        Huỷ bỏ
                    </a>
                    <button type="submit" class="px-6 py-2.5 bg-purple-600 text-white rounded-lg font-medium hover:bg-purple-700 transition flex items-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed" :disabled="loading">
                        <i class="fa-solid fa-spinner fa-spin" x-show="loading" style="display: none;"></i>
                        <i class="fa-solid fa-save" x-show="!loading"></i>
                        <span>Lưu bác sĩ</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-layouts.admin>
