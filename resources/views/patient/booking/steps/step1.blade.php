{{-- ===== BƯỚC 1: CHỌN THÀNH VIÊN ===== --}}
    <div x-show="step === 1" x-cloak class="max-w-5xl mx-auto px-4 py-8">
        <div class="flex items-center gap-3 mb-8">
            <i class="fa-solid fa-users text-3xl" style="color:var(--primary);"></i>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Chọn người cần khám</h1>
                <p class="text-base text-gray-500 mt-1">Vui lòng chọn thành viên gia đình cần đặt lịch khám</p>
            </div>
        </div>

        {{-- Danh sách hồ sơ --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-8">
            <template x-for="profile in profiles" :key="profile.id">
                <div @click="selectProfile(profile)"
                     class="group relative flex items-center gap-4 p-6 bg-white border rounded-3xl cursor-pointer transition-all duration-300 overflow-hidden shadow-sm hover:shadow-md hover:-translate-y-0.5"
                     :class="selectedProfile?.id === profile.id ? 'border-primary ring-2 ring-primary/20 bg-primary/5' : 'border-slate-200'">
                     
                    {{-- Active Decor --}}
                    <div class="absolute inset-y-0 left-0 w-2 transition-colors duration-300"
                         :class="selectedProfile?.id === profile.id ? 'bg-primary' : 'bg-transparent group-hover:bg-primary/20'"></div>

                    {{-- Radio dot --}}
                    <div class="flex-shrink-0 relative z-10">
                        <div class="w-8 h-8 rounded-full border-2 flex items-center justify-center transition-all duration-300"
                             :class="selectedProfile?.id === profile.id ? 'bg-primary border-primary shadow-sm shadow-primary/30' : 'border-slate-300 group-hover:border-primary/50 bg-white'">
                            <i x-show="selectedProfile?.id === profile.id"
                               class="fa-solid fa-check text-white text-sm"></i>
                        </div>
                    </div>

                    {{-- Info --}}
                    <div class="flex-1 min-w-0 relative z-10 ml-2">
                        <div class="flex items-center gap-2 flex-wrap mb-2">
                            <p class="font-bold text-slate-800 text-xl transition-colors group-hover:text-primary" x-text="profile.full_name"></p>
                            <span x-show="profile.is_self"
                                  class="text-xs px-3 py-1 rounded-full font-bold uppercase tracking-wider bg-primary/10 text-primary border border-primary/20">
                                Chủ tài khoản
                            </span>
                        </div>
                        <div class="flex items-center gap-5 text-base font-medium text-slate-600 flex-wrap">
                            <span x-show="profile.phone" class="flex items-center gap-2">
                                <i class="fa-solid fa-phone text-slate-400"></i>
                                <span x-text="profile.phone"></span>
                            </span>
                            <span x-show="profile.date_of_birth" class="flex items-center gap-2">
                                <i class="fa-solid fa-cake-candles text-slate-400"></i>
                                <span x-text="profile.date_of_birth ? new Date(profile.date_of_birth).toLocaleDateString('vi-VN') : ''"></span>
                            </span>
                        </div>
                    </div>
                </div>
            </template>

            {{-- Thêm thành viên --}}
            <a href="{{ route('patient.profiles.create') }}?redirect=booking"
               class="flex items-center gap-4 p-6 border-2 border-dashed border-slate-300 rounded-3xl transition-all duration-300 group hover:border-primary hover:bg-primary/5 bg-slate-50/50">
                <div class="w-8 h-8"></div>
                <div>
                    <p class="font-bold text-slate-600 group-hover:text-primary transition-colors text-xl flex items-center gap-2"><i class="fa-solid fa-plus-circle"></i> Thêm người mới</p>
                    <p class="text-base font-medium text-slate-500 mt-1">Tạo hồ sơ cho người thân trong gia đình</p>
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
        <div class="flex gap-4 sticky bottom-0 bg-white pt-6 pb-4 border-t border-slate-100 z-20">
            <a href="{{ route('home') }}"
               class="flex-1 py-4 border-2 border-slate-200 text-slate-500 rounded-2xl text-center font-bold hover:bg-slate-50 hover:text-slate-700 transition-colors active:scale-95">
                <i class="fa-solid fa-xmark mr-1.5"></i> Thoát
            </a>
            <button @click="goStep2()"
                    :disabled="!selectedProfile"
                    class="py-4 rounded-2xl font-extrabold text-white transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:active:scale-100 shadow-lg shadow-primary/30 hover:shadow-primary/40 active:scale-95 bg-primary hover:bg-primary-dark"
                    style="flex:2;">
                Tiếp tục <i class="fa-solid fa-arrow-right ml-1.5"></i>
            </button>
        </div>
    </div>
    {{-- END BƯỚC 1 --}}