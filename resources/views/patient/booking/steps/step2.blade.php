{{-- ===== BƯỚC 2: CHỌN PHƯƠNG THỨC ===== --}}
    <div x-show="step === 2" class="max-w-5xl mx-auto px-4 py-8">
        <div class="flex items-center gap-3 mb-8">
            <i class="fa-solid fa-stethoscope text-3xl" style="color:var(--primary);"></i>
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Chọn phương thức đặt lịch</h2>
                <p class="text-base text-gray-500 mt-1">
                    Đặt lịch cho: <strong x-text="selectedProfile?.full_name"></strong>
                </p>
            </div>
        </div>

        {{-- 2 lựa chọn --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-8">
            {{-- Theo chuyên khoa --}}
            <div @click="selectMethod('specialty')"
                 class="group relative flex items-center gap-4 p-6 bg-white border rounded-3xl cursor-pointer transition-all duration-300 overflow-hidden shadow-sm hover:shadow-md hover:-translate-y-0.5"
                 :class="bookingMethod === 'specialty' ? 'border-primary ring-2 ring-primary/20 bg-primary/5' : 'border-slate-200'">
                 
                {{-- Active Decor --}}
                <div class="absolute inset-y-0 left-0 w-2 transition-colors duration-300"
                     :class="bookingMethod === 'specialty' ? 'bg-primary' : 'bg-transparent group-hover:bg-primary/20'"></div>

                <div class="flex-shrink-0 relative z-10">
                    <div class="w-8 h-8 rounded-full border-2 flex items-center justify-center transition-all duration-300"
                         :class="bookingMethod === 'specialty' ? 'bg-primary border-primary shadow-sm shadow-primary/30' : 'border-slate-300 group-hover:border-primary/50 bg-white'">
                        <i x-show="bookingMethod === 'specialty'" class="fa-solid fa-check text-white text-sm"></i>
                    </div>
                </div>
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center flex-shrink-0 transition-colors relative z-10"
                     :class="bookingMethod === 'specialty' ? 'bg-primary/10' : 'bg-slate-50 group-hover:bg-primary/5'">
                    <i class="fa-solid fa-briefcase-medical text-3xl text-primary"></i>
                </div>
                <div class="relative z-10 ml-2">
                    <p class="font-bold text-slate-800 text-xl transition-colors group-hover:text-primary">Theo chuyên khoa</p>
                    <p class="text-base font-medium text-slate-500 mt-1">Hệ thống gợi ý bác sĩ phù hợp nhất</p>
                </div>
            </div>

            {{-- Theo bác sĩ --}}
            <div @click="selectMethod('doctor')"
                 class="group relative flex items-center gap-4 p-6 bg-white border rounded-3xl cursor-pointer transition-all duration-300 overflow-hidden shadow-sm hover:shadow-md hover:-translate-y-0.5"
                 :class="bookingMethod === 'doctor' ? 'border-primary ring-2 ring-primary/20 bg-primary/5' : 'border-slate-200'">
                 
                {{-- Active Decor --}}
                <div class="absolute inset-y-0 left-0 w-2 transition-colors duration-300"
                     :class="bookingMethod === 'doctor' ? 'bg-primary' : 'bg-transparent group-hover:bg-primary/20'"></div>

                <div class="flex-shrink-0 relative z-10">
                    <div class="w-8 h-8 rounded-full border-2 flex items-center justify-center transition-all duration-300"
                         :class="bookingMethod === 'doctor' ? 'bg-primary border-primary shadow-sm shadow-primary/30' : 'border-slate-300 group-hover:border-primary/50 bg-white'">
                        <i x-show="bookingMethod === 'doctor'" class="fa-solid fa-check text-white text-sm"></i>
                    </div>
                </div>
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center flex-shrink-0 transition-colors relative z-10"
                     :class="bookingMethod === 'doctor' ? 'bg-primary/10' : 'bg-slate-50 group-hover:bg-primary/5'">
                    <i class="fa-solid fa-user-doctor text-3xl text-primary"></i>
                </div>
                <div class="relative z-10 ml-2">
                    <p class="font-bold text-slate-800 text-xl transition-colors group-hover:text-primary">Theo bác sĩ</p>
                    <p class="text-base font-medium text-slate-500 mt-1">Chọn trực tiếp bác sĩ mong muốn khám</p>
                </div>
            </div>
        </div>

        {{-- Chọn chuyên khoa --}}
        <div x-show="bookingMethod === 'specialty'" class="mb-8">
            <button @click="openSpecialtyModal()"
                    class="w-full flex items-center gap-4 p-5 bg-white border-2 rounded-2xl text-left transition-all hover:bg-slate-50"
                    :style="selectedSpecialty ? 'border-color:var(--primary);' : 'border-color:#cbd5e1;'">
                <i class="fa-solid fa-briefcase-medical text-2xl" style="color:var(--primary);"></i>
                <div class="flex-1 min-w-0">
                    <p class="text-base text-gray-500" x-show="!selectedSpecialty">Chưa chọn chuyên khoa — Nhấn để chọn</p>
                    <p class="font-bold text-lg text-gray-800" x-show="selectedSpecialty" x-text="selectedSpecialty?.name"></p>
                    <p class="text-sm text-gray-500 truncate mt-1" x-show="selectedSpecialty"
                       x-text="selectedSpecialty.description ? (selectedSpecialty.description.length > 80 ? selectedSpecialty.description.substring(0,80) + '...' : selectedSpecialty.description) : 'Hệ thống sẽ tự động xếp bác sĩ phù hợp'"></p>
                </div>
                <i class="fa-solid fa-chevron-right text-gray-400 text-xl flex-shrink-0"></i>
            </button>
        </div>

        {{-- Chọn bác sĩ --}}
        <div x-show="bookingMethod === 'doctor'" class="mb-8">
            <button @click="openDoctorModal()"
                    class="w-full flex items-center gap-4 p-5 bg-white border-2 rounded-2xl text-left transition-all hover:bg-slate-50"
                    :style="selectedDoctor ? 'border-color:var(--primary);' : 'border-color:#cbd5e1;'">
                <div class="w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0 border"
                     :style="selectedDoctor ? 'background-color:rgba(29,111,164,0.10); border-color:transparent;' : 'border-color:#e2e8f0;'">
                    <i x-show="!selectedDoctor" class="fa-solid fa-user text-gray-400 text-xl"></i>
                    <span x-show="selectedDoctor"
                          class="font-bold text-lg" style="color:var(--primary);"
                          x-text="selectedDoctor?.full_title?.split(' ').slice(-2).map(w=>w[0]).join('').slice(0,2).toUpperCase()">
                    </span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-gray-500 text-base" x-show="!selectedDoctor">Chưa chọn bác sĩ — Nhấn để chọn</p>
                    <p class="font-bold text-lg text-gray-800 truncate" x-show="selectedDoctor" x-text="selectedDoctor?.full_title"></p>
                    <p class="text-base font-medium truncate mt-1" x-show="selectedDoctor"
                       style="color:var(--primary);" x-text="selectedDoctor?.primary_specialty"></p>
                </div>
                <i class="fa-solid fa-chevron-right text-gray-400 text-xl flex-shrink-0"></i>
            </button>
        </div>

        {{-- Navigation --}}
        <div class="flex gap-4 sticky bottom-0 bg-white pt-6 pb-4 border-t border-slate-100 z-20">
            <button @click="step = 1"
                    class="flex-1 py-4 border-2 border-primary/20 text-primary rounded-2xl font-bold hover:bg-primary/5 transition-colors active:scale-95 text-lg">
                <i class="fa-solid fa-arrow-left mr-1.5"></i> Quay lại
            </button>
            <button @click="goStep3()"
                    :disabled="!canGoStep3"
                    class="py-4 rounded-2xl font-extrabold text-white transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:active:scale-100 shadow-lg shadow-primary/30 hover:shadow-primary/40 active:scale-95 bg-primary hover:bg-primary-dark text-lg"
                    style="flex:2;">
                Tiếp tục <i class="fa-solid fa-arrow-right ml-1.5"></i>
            </button>
        </div>
    </div>
    {{-- END BƯỚC 2 --}}