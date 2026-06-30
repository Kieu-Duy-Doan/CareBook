{{-- ===== BƯỚC 1: CHỌN THÀNH VIÊN ===== --}}
<div x-show="step === 1" x-cloak class="max-w-5xl mx-auto px-4 py-6">
    <div class="flex items-center gap-3 mb-8">
        <i class="fa-solid fa-users text-3xl" style="color:var(--primary);"></i>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Chọn người cần khám</h1>
            <p class="text-base text-gray-500 mt-1">Vui lòng chọn thành viên gia đình cần đặt lịch khám</p>
        </div>
    </div>

    {{-- Danh sách hồ sơ --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        <template x-for="profile in profiles" :key="profile.id">
            <div @click="selectProfile(profile)"
                class="group relative flex items-center gap-3 p-4 bg-white border rounded-2xl cursor-pointer transition-colors hover:border-primary hover:bg-primary/5"
                :class="selectedProfile?.id === profile.id ? 'border-primary ring-1 ring-primary/20 bg-primary/5' :
                    'border-slate-200'">

                {{-- Active Decor --}}
                <div class="absolute inset-y-0 left-0 w-1.5 transition-colors duration-300"
                    :class="selectedProfile?.id === profile.id ? 'bg-primary' : 'bg-transparent group-hover:bg-primary/20'">
                </div>

                {{-- Avatar placeholder --}}
                <div
                    class="flex-shrink-0 w-10 h-10 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center ml-2 border border-blue-100">
                    <i class="fa-solid fa-user"></i>
                </div>

                {{-- Info --}}
                <div class="flex-1 min-w-0 relative z-10 ml-2">
                    <div class="flex items-center gap-2 flex-wrap mb-1">
                        <p class="font-bold text-slate-800 text-lg transition-colors group-hover:text-primary"
                            x-text="profile.full_name"></p>
                        <span x-show="profile.is_self"
                            class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase tracking-wider bg-primary/10 text-primary border border-primary/20">
                            Chủ tài khoản
                        </span>
                        <span x-show="!profile.is_self && profile.relationship"
                            class="text-xs px-3 py-1 rounded-full font-bold uppercase tracking-wider bg-emerald-100 text-emerald-700 border border-emerald-200"
                            x-text="{ child: 'Con', spouse: 'Vợ/Chồng', parent: 'Bố/Mẹ', other: 'Khác' }[profile.relationship] || profile.relationship">
                        </span>
                    </div>
                    <div class="flex items-center gap-4 text-sm font-medium text-slate-500 flex-wrap">
                        <span x-show="profile.phone" class="flex items-center gap-1.5">
                            <i class="fa-solid fa-phone opacity-70"></i>
                            <span x-text="profile.phone"></span>
                        </span>
                        <span x-show="profile.date_of_birth" class="flex items-center gap-1.5">
                            <i class="fa-solid fa-cake-candles opacity-70"></i>
                            <span
                                x-text="profile.date_of_birth ? new Date(profile.date_of_birth).toLocaleDateString('vi-VN') : ''"></span>
                        </span>
                    </div>
                </div>
            </div>
        </template>

        {{-- Thêm thành viên --}}
        <a href="{{ route('patient.profiles.create') }}?redirect=booking"
            class="flex items-center justify-center gap-3 p-4 border-2 border-dashed border-slate-300 rounded-2xl transition-all duration-300 group hover:border-primary hover:bg-primary/5 bg-slate-50/50 min-h-[100px]">
            <div class="text-center">
                <p class="font-bold text-slate-600 group-hover:text-primary transition-colors text-lg"><i
                        class="fa-solid fa-plus-circle mr-1"></i> Thêm người mới</p>
            </div>
        </a>
    </div>

    {{-- Cảnh báo không có hồ sơ --}}
    <div x-show="profiles.length === 0"
        class="p-4 bg-yellow-50 border border-yellow-200 rounded-xl text-sm text-yellow-700 mb-4">
        <i class="fa-solid fa-triangle-exclamation mr-2"></i>
        Bạn chưa có hồ sơ bệnh nhân.
        <a href="{{ route('patient.profiles.create') }}" class="underline font-medium ml-1">Tạo hồ sơ ngay</a>
    </div>

    {{-- Navigation --}}
    <div class="flex gap-4 sticky bottom-0 bg-white pt-4 pb-3 border-t border-slate-100 z-20">
        <a href="{{ route('home') }}"
            class="w-1/3 md:w-1/4 py-3 border-2 border-slate-200 text-slate-500 rounded-xl text-center font-bold hover:bg-slate-50 hover:text-slate-700 transition-colors active:scale-95 text-base">
            Thoát
        </a>
        <button @click="goStep2()" :disabled="!selectedProfile"
            class="flex-1 py-3 rounded-xl font-extrabold text-white transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-md bg-primary hover:bg-primary-dark text-base">
            Tiếp tục <i class="fa-solid fa-arrow-right ml-1.5"></i>
        </button>
    </div>
</div>
{{-- END BƯỚC 1 --}}
