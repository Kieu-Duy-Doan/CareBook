<x-layouts.patient-dashboard title="Sửa hồ sơ bệnh nhân" activeMenu="profiles">
    <div>
        <div class="flex items-center gap-4 mb-8">
            <a href="{{ route('patient.profiles.index') }}" class="w-12 h-12 rounded-full bg-white shadow-sm border border-slate-100 flex items-center justify-center text-slate-500 hover:text-primary hover:bg-slate-50 transition-all active:scale-95 group">
                <i class="fa-solid fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
            </a>
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight">Cập nhật hồ sơ</h1>
                <p class="text-sm md:text-base text-slate-500 mt-1">Hồ sơ: <span class="font-bold text-slate-700">{{ $profile->full_name }}</span></p>
            </div>
        </div>

        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100 relative overflow-hidden">
            <!-- Subtle background decor -->
            <div class="absolute top-0 right-0 w-64 h-64 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-primary/5 to-transparent rounded-full -translate-y-1/2 translate-x-1/2 pointer-events-none"></div>

            <form action="{{ route('patient.profiles.update', $profile->id) }}" method="POST" class="relative z-10">
                @csrf
                @method('PUT')
                
                <div class="space-y-5">
                    <!-- Họ và tên -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5 ml-1">Họ và tên <span class="text-rose-500">*</span></label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fa-regular fa-user text-slate-400 group-focus-within:text-primary transition-colors"></i>
                            </div>
                            <input type="text" name="full_name" value="{{ old('full_name', $profile->full_name) }}" required placeholder="Ví dụ: Nguyễn Văn A"
                                   class="pl-11 w-full rounded-2xl border-slate-200 bg-slate-50 shadow-sm focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all duration-300 @error('full_name') border-rose-500 bg-rose-50 @enderror py-3">
                        </div>
                        @error('full_name') <p class="mt-1.5 ml-1 text-xs font-medium text-rose-500"><i class="fa-solid fa-circle-exclamation mr-1"></i> {{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- Ngày sinh -->
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1.5 ml-1">Ngày sinh <span class="text-rose-500">*</span></label>
                            <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $profile->date_of_birth ? \Carbon\Carbon::parse($profile->date_of_birth)->format('Y-m-d') : '') }}" required
                                   class="w-full rounded-2xl border-slate-200 bg-slate-50 shadow-sm focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all duration-300 @error('date_of_birth') border-rose-500 bg-rose-50 @enderror py-3 px-4">
                            @error('date_of_birth') <p class="mt-1.5 ml-1 text-xs font-medium text-rose-500"><i class="fa-solid fa-circle-exclamation mr-1"></i> {{ $message }}</p> @enderror
                        </div>

                        <!-- Giới tính -->
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1.5 ml-1">Giới tính <span class="text-rose-500">*</span></label>
                            @php
                                $selectedGender = old('gender', $profile->gender);
                                if ($selectedGender === 'M') {
                                    $selectedGender = 'male';
                                } elseif ($selectedGender === 'F') {
                                    $selectedGender = 'female';
                                } elseif ($selectedGender === 'O') {
                                    $selectedGender = 'other';
                                }
                            @endphp
                            <select name="gender" required class="w-full rounded-2xl border-slate-200 bg-slate-50 shadow-sm focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all duration-300 @error('gender') border-rose-500 bg-rose-50 @enderror py-3 px-4 appearance-none">
                                <option value="" disabled>Chọn giới tính</option>
                                <option value="male" {{ $selectedGender == 'male' ? 'selected' : '' }}>Nam</option>
                                <option value="female" {{ $selectedGender == 'female' ? 'selected' : '' }}>Nữ</option>
                                <option value="other" {{ $selectedGender == 'other' ? 'selected' : '' }}>Khác</option>
                            </select>
                            @error('gender') <p class="mt-1.5 ml-1 text-xs font-medium text-rose-500"><i class="fa-solid fa-circle-exclamation mr-1"></i> {{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Số điện thoại & CCCD -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1.5 ml-1">Số điện thoại</label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i class="fa-solid fa-phone text-slate-400 group-focus-within:text-primary transition-colors"></i>
                                </div>
                                <input type="text" name="phone" value="{{ old('phone', $profile->phone) }}" placeholder="09xxxx..."
                                       class="pl-11 w-full rounded-2xl border-slate-200 bg-slate-50 shadow-sm focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all duration-300 py-3">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1.5 ml-1">CMND / CCCD</label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i class="fa-regular fa-id-card text-slate-400 group-focus-within:text-primary transition-colors"></i>
                                </div>
                                <input type="text" name="id_card" value="{{ old('id_card', $profile->id_card) }}" placeholder="Số CCCD..."
                                       class="pl-11 w-full rounded-2xl border-slate-200 bg-slate-50 shadow-sm focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all duration-300 py-3">
                            </div>
                        </div>
                    </div>

                    <!-- Địa chỉ -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5 ml-1">Địa chỉ liên hệ</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fa-solid fa-location-dot text-slate-400 group-focus-within:text-primary transition-colors"></i>
                            </div>
                            <input type="text" name="address" value="{{ old('address', $profile->address) }}" placeholder="Số nhà, đường, phường/xã..."
                                   class="pl-11 w-full rounded-2xl border-slate-200 bg-slate-50 shadow-sm focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all duration-300 py-3">
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-slate-100">
                    <button type="submit" class="w-full bg-primary hover:bg-primary-dark text-white font-extrabold py-4 px-6 rounded-2xl shadow-[0_8px_20px_-8px_rgba(29,111,164,0.5)] hover:shadow-[0_12px_25px_-8px_rgba(29,111,164,0.6)] transition-all active:scale-[0.98] text-lg flex justify-center items-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i> Lưu thay đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.patient>
