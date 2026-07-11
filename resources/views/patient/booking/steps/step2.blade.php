{{-- ===== BƯỚC 2: CHỌN PHƯƠNG THỨC ===== --}}
<div x-show="step === 2" x-transition class="max-w-5xl mx-auto px-4 py-6">
    <div class="flex items-center gap-3 mb-8">
        <i class="fa-solid fa-stethoscope text-3xl" style="color:var(--primary);"></i>
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Chọn phương thức đặt lịch</h2>
            <p class="text-base text-gray-500 mt-1">
                Đặt lịch cho: <strong x-text="selectedProfile?.full_name"></strong>
            </p>
        </div>
    </div>

    {{-- Các lựa chọn --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        <template x-if="suggestedDoctors.length > 0">
            {{-- Theo bác sĩ gợi ý --}}
            <div @click="selectMethod('suggested')"
                class="group relative flex items-center gap-3 p-4 bg-white border rounded-2xl cursor-pointer transition-colors hover:border-primary hover:bg-primary/5"
                :class="bookingMethod === 'suggested' ? 'border-primary ring-1 ring-primary/20 bg-primary/5' : 'border-slate-200'">

                {{-- Active Decor --}}
                <div class="absolute inset-y-0 left-0 w-1.5 transition-colors duration-300"
                    :class="bookingMethod === 'suggested' ? 'bg-primary' : 'bg-transparent group-hover:bg-primary/20'"></div>

                <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 transition-colors relative z-10 ml-2"
                    :class="bookingMethod === 'suggested' ? 'bg-primary text-white shadow-md' : 'bg-blue-50 text-blue-500 group-hover:bg-primary/10 group-hover:text-primary'">
                    <i class="fa-solid fa-star text-2xl"></i>
                </div>
                <div class="relative z-10 ml-1">
                    <p class="font-bold text-slate-800 text-lg transition-colors group-hover:text-primary">Bác sĩ gợi ý</p>
                    <p class="text-[13px] font-medium text-slate-500 mt-0.5">Từ lịch huỷ trước đó</p>
                </div>
            </div>
        </template>

        {{-- Theo chuyên khoa --}}
        <div @click="selectMethod('specialty')"
            class="group relative flex items-center gap-3 p-4 bg-white border rounded-2xl cursor-pointer transition-colors hover:border-primary hover:bg-primary/5"
            :class="bookingMethod === 'specialty' ? 'border-primary ring-1 ring-primary/20 bg-primary/5' : 'border-slate-200'">

            {{-- Active Decor --}}
            <div class="absolute inset-y-0 left-0 w-1.5 transition-colors duration-300"
                :class="bookingMethod === 'specialty' ? 'bg-primary' : 'bg-transparent group-hover:bg-primary/20'"></div>

            <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 transition-colors relative z-10 ml-2"
                :class="bookingMethod === 'specialty' ? 'bg-primary text-white shadow-md' : 'bg-blue-50 text-blue-500 group-hover:bg-primary/10 group-hover:text-primary'">
                <i class="fa-solid fa-briefcase-medical text-2xl"></i>
            </div>
            <div class="relative z-10 ml-1">
                <p class="font-bold text-slate-800 text-lg transition-colors group-hover:text-primary">Đặt lịch cơ bản</p>
                <p class="text-[13px] font-medium text-slate-500 mt-0.5">Hệ thống gợi ý bác sĩ</p>
            </div>
        </div>

        {{-- Theo bác sĩ --}}
        <div @click="selectMethod('doctor')"
            class="group relative flex items-center gap-3 p-4 bg-white border rounded-2xl cursor-pointer transition-colors hover:border-primary hover:bg-primary/5"
            :class="bookingMethod === 'doctor' ? 'border-primary ring-1 ring-primary/20 bg-primary/5' : 'border-slate-200'">

            {{-- Active Decor --}}
            <div class="absolute inset-y-0 left-0 w-1.5 transition-colors duration-300"
                :class="bookingMethod === 'doctor' ? 'bg-primary' : 'bg-transparent group-hover:bg-primary/20'"></div>

            <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 transition-colors relative z-10 ml-2"
                :class="bookingMethod === 'doctor' ? 'bg-primary text-white shadow-md' : 'bg-blue-50 text-blue-500 group-hover:bg-primary/10 group-hover:text-primary'">
                <i class="fa-solid fa-user-doctor text-2xl"></i>
            </div>
            <div class="relative z-10 ml-1">
                <p class="font-bold text-slate-800 text-lg transition-colors group-hover:text-primary">Chỉ định bác sĩ</p>
                <p class="text-[13px] font-medium text-slate-500 mt-0.5">Chọn trực tiếp bác sĩ</p>
            </div>
        </div>
    </div>

    {{-- Chọn chuyên khoa (Dùng chung cho cả 2 luồng) --}}
    <div x-show="bookingMethod === 'specialty' || bookingMethod === 'doctor'" class="mb-8 animate-fade-in">
        <h3 class="text-base font-bold text-slate-800 mb-3" x-text="bookingMethod === 'doctor' ? '1. Chọn Chuyên khoa trước:' : 'Tìm và chọn Chuyên khoa:'"></h3>
        <div class="bg-white border-2 border-slate-200 rounded-2xl overflow-hidden shadow-sm transition-all hover:border-primary/30">
            <div class="px-4 py-3 border-b border-slate-200 bg-slate-50 flex gap-3 items-center">
                <i class="fa-solid fa-magnifying-glass text-slate-400"></i>
                <input x-model="specialtySearch" type="search" placeholder='Tìm kiếm chuyên khoa... (VD: "Tim mạch")' class="w-full bg-transparent outline-none text-slate-700 font-medium placeholder:font-normal text-base">
            </div>
            <div class="overflow-y-auto max-h-[60vh] md:max-h-[350px]">
                <template x-for="specialty in filteredSpecialties" :key="specialty.id">
                    <div @click="selectSpecialty(specialty)"
                        class="flex items-center gap-4 px-4 py-4 md:py-3 border-b hover:bg-primary/5 cursor-pointer transition-colors"
                        :class="selectedSpecialty?.id === specialty.id ? 'bg-primary/5 border-primary/20' : 'border-slate-100'">
                        <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0"
                            :class="selectedSpecialty?.id === specialty.id ? 'border-primary bg-primary' : 'border-slate-300'">
                            <i x-show="selectedSpecialty?.id === specialty.id" class="fa-solid fa-check text-white text-[10px]"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-base text-gray-800" :class="selectedSpecialty?.id === specialty.id ? 'text-primary' : ''" x-text="specialty.name"></p>
                            <p class="text-sm text-gray-500 mt-0.5 line-clamp-2" x-text="specialty.description"></p>
                        </div>
                    </div>
                </template>
                <div x-show="filteredSpecialties.length === 0" class="text-center py-8 text-gray-400">
                    <i class="fa-solid fa-magnifying-glass text-3xl mb-3 block opacity-50"></i>
                    <p class="text-base">Không tìm thấy chuyên khoa nào</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Chọn Học vị (Chỉ cho luồng Đặt lịch cơ bản) --}}
    <div x-show="bookingMethod === 'specialty'" class="mb-8 animate-fade-in">
        <h3 class="text-base font-bold text-slate-800 mb-3">Chọn Học vị bác sĩ mong muốn:</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <template x-for="fee in availableLevels" :key="fee.id">
                <div @click="selectedLevel = fee.level; availableDates = []; slots = []; selectedDate = null; selectedSlot = null;"
                    class="flex flex-col items-center justify-center p-3 bg-white border-2 rounded-2xl cursor-pointer transition-all hover:border-primary/50"
                    :class="selectedLevel === fee.level ? 'border-primary ring-1 ring-primary/20 bg-primary/5' : 'border-slate-200'">
                    <span class="font-bold text-slate-800 text-base" x-text="fee.level"></span>
                    <span class="text-[13px] text-primary font-medium mt-1" x-text="new Intl.NumberFormat('vi-VN', {style: 'currency', currency: 'VND'}).format(fee.base_price)"></span>
                </div>
            </template>
        </div>
        <div x-show="selectedSpecialty && availableLevels.length === 0" class="text-center py-6 bg-red-50 border border-red-100 rounded-xl mt-3">
            <i class="fa-solid fa-user-doctor text-3xl text-red-300 mb-2 block"></i>
            <p class="text-red-600 font-bold">Không có bác sĩ</p>
            <p class="text-sm text-red-500 mt-1">Chuyên khoa này hiện chưa có bác sĩ nào có lịch khám. Vui lòng chọn chuyên khoa khác.</p>
        </div>
    </div>

    {{-- Chọn bác sĩ --}}
    <div x-show="bookingMethod === 'doctor' && selectedSpecialty !== null" class="mb-8 animate-fade-in">
        <h3 class="text-base font-bold text-slate-800 mb-3">2. Chọn Bác sĩ thuộc chuyên khoa <span class="text-primary" x-text="selectedSpecialty.name"></span>:</h3>
        <div class="bg-white border-2 border-slate-200 rounded-2xl overflow-hidden shadow-sm transition-all hover:border-primary/30">
            <div class="px-4 py-3 border-b border-slate-200 bg-slate-50 flex gap-3 items-center">
                <i class="fa-solid fa-magnifying-glass text-slate-400"></i>
                <input x-model="doctorSearch" type="search" placeholder='Tìm kiếm bác sĩ... (VD: "Nguyễn")' class="w-full bg-transparent outline-none text-slate-700 font-medium placeholder:font-normal text-base">
            </div>
            <div class="overflow-y-auto max-h-[60vh] md:max-h-[350px]">
                <template x-for="doc in filteredDoctors" :key="doc.id">
                    <div @click="selectDoctor(doc)"
                        class="flex items-center gap-4 px-4 py-4 md:py-3 border-b hover:bg-primary/5 cursor-pointer transition-colors"
                        :class="selectedDoctor?.id === doc.id ? 'bg-primary/5 border-primary/20' : 'border-slate-100'">
                        <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0"
                            :class="selectedDoctor?.id === doc.id ? 'border-primary bg-primary' : 'border-slate-300'">
                            <i x-show="selectedDoctor?.id === doc.id" class="fa-solid fa-check text-white text-[10px]"></i>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center flex-shrink-0 border border-blue-100 text-primary">
                            <span class="font-bold text-sm" x-text="doc.full_title.split(' ').slice(-2).map(w=>w[0]).join('').slice(0,2).toUpperCase()"></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-base text-gray-800" :class="selectedDoctor?.id === doc.id ? 'text-primary' : ''" x-text="doc.full_title"></p>
                            <p class="text-sm text-gray-500 mt-0.5 line-clamp-1">Mã BS: <strong x-text="doc.doctor_code"></strong></p>
                        </div>
                        <div class="text-right whitespace-nowrap ml-3">
                            <p class="text-sm font-medium text-slate-500">Giá chỉ định</p>
                            <p class="font-bold text-primary text-base" x-text="fees.find(f => f.level === doc.level) ? new Intl.NumberFormat('vi-VN', {style: 'currency', currency: 'VND'}).format(fees.find(f => f.level === doc.level).specific_price) : '0 đ'"></p>
                        </div>
                    </div>
                </template>
                <div x-show="filteredDoctors.length === 0" class="text-center py-8 text-gray-400">
                    <i class="fa-solid fa-magnifying-glass text-3xl mb-3 block opacity-50"></i>
                    <p class="text-base">Không tìm thấy bác sĩ nào</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Chọn bác sĩ gợi ý --}}
    <div x-show="bookingMethod === 'suggested'" class="mb-6 animate-fade-in">
        <h3 class="text-base font-bold text-slate-800 mb-3">Vui lòng chọn một Bác sĩ từ danh sách gợi ý:</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <template x-for="doc in suggestedDoctors" :key="doc.id">
                <div @click="selectSuggestedDoctor(doc)"
                    class="group relative flex items-center gap-3 p-3 bg-white border rounded-2xl cursor-pointer transition-all hover:shadow-md"
                    :class="selectedDoctor && selectedDoctor.id === doc.id ? 'border-primary bg-primary/5 ring-1 ring-primary/20' : 'border-slate-200 hover:border-primary/50'">

                    <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0"
                        :class="selectedDoctor && selectedDoctor.id === doc.id ? 'bg-primary text-white' : 'bg-blue-50 text-blue-600'">
                        <i class="fa-solid fa-user-doctor text-sm"></i>
                    </div>

                    <div class="flex-1 min-w-0">
                        <h4 class="font-bold text-slate-800 text-base truncate" x-text="doc.full_title"></h4>
                        <template x-if="doc.has_same_slot">
                            <p class="text-xs font-bold text-green-600 mt-0.5"><i class="fa-regular fa-clock"></i> Có lịch rảnh đúng giờ cũ</p>
                        </template>
                        <template x-if="!doc.has_same_slot">
                            <p class="text-xs text-orange-500 mt-0.5"><i class="fa-solid fa-triangle-exclamation"></i> Kín giờ cũ, cần chọn giờ khác</p>
                        </template>
                    </div>

                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-colors shrink-0"
                        :class="selectedDoctor && selectedDoctor.id === doc.id ? 'border-primary bg-primary' : 'border-slate-300 group-hover:border-primary/50'">
                        <i x-show="selectedDoctor && selectedDoctor.id === doc.id" class="fa-solid fa-check text-white text-[10px]"></i>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- Navigation --}}
    <div class="flex gap-4 sticky bottom-0 bg-white pt-4 pb-3 border-t border-slate-100 z-20">
        <button @click="step = 1"
            class="w-1/3 md:w-1/4 py-3 border-2 border-primary/20 text-primary rounded-xl font-bold hover:bg-primary/5 transition-colors active:scale-95 text-base">
            Quay lại
        </button>
        <button @click="goStep3()"
            :disabled="!canGoStep3"
            class="flex-1 py-3 rounded-xl font-extrabold text-white transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-md bg-primary hover:bg-primary-dark text-base">
            Tiếp tục <i class="fa-solid fa-arrow-right ml-1.5"></i>
        </button>
    </div>
</div>
{{-- END BƯỚC 2 --}}