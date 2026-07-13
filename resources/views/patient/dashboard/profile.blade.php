<x-layouts.patient-dashboard title="Thông tin cá nhân" activeMenu="profiles">
    <div x-data="{ lightboxOpen: false, lightboxImg: '' }">
        <!-- Top Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Chi tiết hồ sơ</h2>
                <p class="text-slate-500 text-sm mt-1">Các thông tin quan trọng trong hồ sơ y tế của bạn</p>
            </div>
            <a href="{{ $profile ? route('patient.profiles.edit', $profile->id) : route('patient.profiles.create', ['is_self' => 1]) }}"
                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-primary text-white font-semibold rounded-xl transition-colors hover:bg-primary-dark shrink-0 whitespace-nowrap self-start sm:self-auto">
                <i class="fa-regular fa-pen-to-square"></i>
                <span>Cập nhật thông tin</span>
            </a>
        </div>

        <div class="flex flex-col md:flex-row gap-8">
            <!-- Left Column: Avatar & Status -->
            <div class="w-full md:w-64 shrink-0 flex flex-col items-center">
                <div
                    class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 w-full flex flex-col items-center relative overflow-hidden">
                    <div class="absolute top-0 inset-x-0 h-24 bg-gradient-to-b from-primary/10 to-transparent"></div>

                    <div
                        class="w-28 h-28 rounded-3xl bg-white shadow-md border border-slate-50 flex items-center justify-center mb-5 overflow-hidden relative z-10">
                        @if ($user->avatar_url)
                            <img src="{{ asset('storage/' . $user->avatar_url) }}" alt="Avatar"
                                class="w-full h-full object-cover">
                        @else
                            <span class="text-4xl font-black text-primary/40">{{ $user->avatar_initials }}</span>
                        @endif
                    </div>

                    <h3 class="font-bold text-lg text-slate-800 mb-1 text-center relative z-10">{{ $user->full_name }}
                    </h3>
                    <div
                        class="bg-emerald-50 text-emerald-600 px-3 py-1.5 rounded-lg text-xs font-bold flex items-center gap-1.5 shadow-sm border border-emerald-100 relative z-10">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Đang hoạt động
                    </div>
                </div>
            </div>

            <!-- Right Column: Info Grids -->
            <div class="flex-1 space-y-6">
                <!-- Thông tin tài khoản -->
                <div class="bg-white rounded-3xl p-6 md:p-8 shadow-sm border border-slate-100">
                    <h3
                        class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-4 mb-6 flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                            <i class="fa-solid fa-user-shield"></i>
                        </div>
                        Thông tin tài khoản
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-8">
                        <div class="group">
                            <p class="text-xs text-slate-500 font-medium uppercase tracking-wider mb-1.5">Họ và tên</p>
                            <p class="font-semibold text-slate-800 text-[15px]">{{ $user->full_name }}</p>
                        </div>

                        <div class="group">
                            <p class="text-xs text-slate-500 font-medium uppercase tracking-wider mb-1.5">Số điện thoại
                            </p>
                            <p class="font-semibold text-slate-800 text-[15px]">{{ $user->phone ?? 'Chưa cập nhật' }}
                            </p>
                        </div>

                        <div class="group">
                            <p class="text-xs text-slate-500 font-medium uppercase tracking-wider mb-1.5">Email</p>
                            <p class="font-semibold text-slate-800 text-[15px]">{{ $user->email ?? 'Chưa cập nhật' }}
                            </p>
                        </div>

                        <div class="group">
                            <p class="text-xs text-slate-500 font-medium uppercase tracking-wider mb-1.5">Căn cước công
                                dân</p>
                            <p class="font-semibold text-slate-800 text-[15px]">{{ $user->id_card ?? 'Chưa cập nhật' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Thông tin y tế -->
                <div class="bg-white rounded-3xl p-6 md:p-8 shadow-sm border border-slate-100">
                    <h3
                        class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-4 mb-6 flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center">
                            <i class="fa-solid fa-notes-medical"></i>
                        </div>
                        Thông tin y tế cá nhân
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-8">
                        <div class="group">
                            <p class="text-xs text-slate-500 font-medium uppercase tracking-wider mb-1.5">Ngày sinh</p>
                            <p class="font-semibold text-slate-800 text-[15px]">
                                {{ $profile && $profile->date_of_birth ? \Carbon\Carbon::parse($profile->date_of_birth)->format('d/m/Y') : 'Chưa cập nhật' }}
                            </p>
                        </div>

                        <div class="group">
                            <p class="text-xs text-slate-500 font-medium uppercase tracking-wider mb-1.5">Giới tính</p>
                            <p class="font-semibold text-slate-800 text-[15px]">
                                @if ($profile && $profile->gender == 'male')
                                    Nam
                                @elseif($profile && $profile->gender == 'female')
                                    Nữ
                                @elseif($profile && $profile->gender == 'other')
                                    Khác
                                @else
                                    Chưa cập nhật
                                @endif
                            </p>
                        </div>

                        <div class="group sm:col-span-2">
                            <p class="text-xs text-slate-500 font-medium uppercase tracking-wider mb-1.5">Địa chỉ</p>
                            <p class="font-semibold text-slate-800 text-[15px]">
                                {{ $profile->address ?? 'Chưa cập nhật' }}</p>
                        </div>

                        <div class="group">
                            <p class="text-xs text-slate-500 font-medium uppercase tracking-wider mb-1.5">Dân tộc</p>
                            <p class="font-semibold text-slate-800 text-[15px]">
                                {{ $profile->ethnicity ?? 'Chưa cập nhật' }}</p>
                        </div>

                        <div class="group">
                            <p class="text-xs text-slate-500 font-medium uppercase tracking-wider mb-1.5">Nghề nghiệp
                            </p>
                            <p class="font-semibold text-slate-800 text-[15px]">
                                {{ $profile->occupation ?? 'Chưa cập nhật' }}</p>
                        </div>

                        <div class="group border-t border-slate-50 pt-4 mt-2">
                            <p class="text-xs text-slate-500 font-medium uppercase tracking-wider mb-1.5">Mã BHYT</p>
                            <p class="font-bold text-primary text-[15px]">
                                {{ $profile->insurance_code ?? 'Chưa cập nhật' }}</p>
                        </div>

                        <div class="group border-t border-slate-50 pt-4 mt-2">
                            <p class="text-xs text-slate-500 font-medium uppercase tracking-wider mb-1.5">Hạn thẻ BHYT
                            </p>
                            <p class="font-semibold text-slate-800 text-[15px]">
                                {{ $profile && $profile->insurance_expiry ? \Carbon\Carbon::parse($profile->insurance_expiry)->format('d/m/Y') : 'Chưa cập nhật' }}
                            </p>
                        </div>
                    </div>

                    <!-- Tiền sử y tế (Fixed Bug) -->
                    <div class="mt-8 pt-6 border-t border-slate-100">
                        <h4 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                            <div
                                class="w-6 h-6 rounded border border-rose-100 bg-rose-50 text-rose-500 flex items-center justify-center text-xs">
                                <i class="fa-solid fa-file-waveform"></i>
                            </div>
                            Tiền sử y tế / Hồ sơ đính kèm
                        </h4>

                        <div>
                            @if ($profile && $profile->medical_history)
                                @php
                                    $historyArray = is_string($profile->medical_history)
                                        ? json_decode($profile->medical_history, true)
                                        : $profile->medical_history;
                                @endphp

                                @if (is_array($historyArray) && count($historyArray) > 0)
                                    <div class="flex flex-wrap gap-4 mt-2">
                                        @foreach ($historyArray as $history)
                                            @if (str_contains(strtolower($history), 'http') &&
                                                    (str_contains(strtolower($history), 'cloudinary') ||
                                                        str_contains(strtolower($history), '.jpg') ||
                                                        str_contains(strtolower($history), '.png')))
                                                <!-- Image Thumbnail -->
                                                <div @click="lightboxOpen = true; lightboxImg = '{{ $history }}'"
                                                    class="w-24 h-24 rounded-xl overflow-hidden cursor-pointer border-2 border-slate-100 hover:border-primary transition-colors group relative">
                                                    <img src="{{ $history }}" class="w-full h-full object-cover"
                                                        alt="Tiền sử y tế">
                                                    <div
                                                        class="absolute inset-0 bg-black/20 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                                        <i
                                                            class="fa-solid fa-magnifying-glass-plus text-white text-xl"></i>
                                                    </div>
                                                </div>
                                            @else
                                                <!-- Text Badge (Fallback) -->
                                                <span
                                                    class="px-4 py-2 bg-slate-50 text-slate-700 rounded-xl text-sm font-medium border border-slate-200 shadow-sm">{{ $history }}</span>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-sm font-medium text-slate-500 italic">Không có hồ sơ đính kèm</p>
                                @endif
                            @else
                                <p class="text-sm font-medium text-slate-500 italic">Chưa cập nhật</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lightbox Modal -->
        <div x-show="lightboxOpen" x-cloak
            class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/90 backdrop-blur-sm p-4 sm:p-8"
            @keydown.escape.window="lightboxOpen = false">
            <div x-show="lightboxOpen" @click.outside="lightboxOpen = false"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                class="relative max-w-4xl w-full max-h-[90vh] flex flex-col">

                <!-- Close Button -->
                <button @click="lightboxOpen = false"
                    class="absolute -top-12 right-0 w-10 h-10 bg-white/10 hover:bg-white/20 text-white rounded-full flex items-center justify-center transition-colors">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>

                <!-- Image -->
                <img :src="lightboxImg" class="w-full h-full object-contain rounded-2xl shadow-2xl" alt="Phóng to">
            </div>
        </div>
    </div>
</x-layouts.patient-dashboard>
