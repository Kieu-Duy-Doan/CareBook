{{-- ===== BƯỚC 3: CHỌN LỊCH KHÁM ===== --}}
<div x-show="step === 3" class="max-w-5xl mx-auto px-4 py-6">
    <div class="flex items-center gap-3 mb-6">
        <i class="fa-solid fa-calendar-days text-3xl" style="color:var(--primary);"></i>
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Chọn ngày và giờ khám</h2>
            <p class="text-base text-gray-500 mt-1">
                Bệnh nhân: <strong x-text="selectedProfile?.full_name"></strong>
            </p>
            <p class="text-base text-gray-500" x-show="bookingMethod === 'doctor'">
                Bác sĩ: <strong x-text="selectedDoctor?.full_title?.toUpperCase()"></strong>
            </p>
            <p class="text-base text-gray-500" x-show="bookingMethod === 'specialty'">
                Chuyên khoa: <strong x-text="selectedSpecialty?.name"></strong>
            </p>
        </div>
    </div>

    {{-- Chọn ngày --}}
    <div class="mb-6">
        <p class="font-bold text-base mb-3" style="color:var(--primary);">Chọn ngày khám:</p>

        {{-- Horizontal scroll dates --}}
        <div class="flex gap-2.5 overflow-x-auto pb-3" style="-ms-overflow-style:none;scrollbar-width:none;">
            <template x-for="dateObj in availableDates" :key="dateObj.date">
                <button @click="selectDate(dateObj)"
                    type="button"
                    class="flex-shrink-0 flex flex-col items-center justify-center w-16 h-20 rounded-xl border-2 transition-all"
                    :class="selectedDate?.date === dateObj.date
                                ? 'border-primary bg-primary text-white shadow-md scale-105'
                                : 'border-gray-200 text-gray-600 hover:border-primary/50 hover:bg-primary/5'">
                    <span class="text-[11px] font-medium uppercase" x-text="dateObj.day_name"></span>
                    <span class="text-xl font-extrabold my-0.5" x-text="dateObj.display"></span>
                </button>
            </template>

            {{-- Loading skeleton --}}
            <template x-if="loadingDates">
                <div class="flex gap-2.5">
                    <template x-for="i in [1,2,3,4,5,6]" :key="i">
                        <div class="flex-shrink-0 w-16 h-20 rounded-xl bg-gray-100 animate-pulse"></div>
                    </template>
                </div>
            </template>

            {{-- Trạng thái không có bác sĩ phù hợp --}}
            <template x-if="!loadingDates && availableDates.length === 0">
                <div class="w-full text-center py-6 bg-red-50 border border-red-100 rounded-xl">
                    <i class="fa-solid fa-user-doctor text-4xl text-red-300 mb-3 block"></i>
                    <p class="text-red-600 font-bold">Không tìm thấy lịch khám</p>
                    <p class="text-sm text-red-500 mt-1">Hiện không có bác sĩ nào thuộc học vị này rảnh lịch. Vui lòng quay lại và chọn học vị khác.</p>
                </div>
            </template>
        </div>
    </div>

    {{-- Giờ khám (hiện sau khi chọn ngày) --}}
    <div x-show="selectedDate" x-transition class="bg-white border rounded-xl p-4 mb-6 shadow-sm" style="border-color:#e2e8f0;">
        {{-- Header phòng --}}
        <div class="rounded-lg p-2.5 mb-3" style="background-color:rgba(29,111,164,0.08);">
            <div class="flex items-center gap-2 font-semibold text-sm" style="color:var(--primary);">
                <i class="fa-solid fa-location-dot"></i>
                <span x-show="bookingMethod === 'doctor'" x-text="selectedDoctor?.room_name ?? 'Phòng khám'"></span>
                <span x-show="bookingMethod === 'specialty'" x-text="selectedSpecialty?.name"></span>
            </div>
        </div>

        {{-- Đã chọn slot --}}
        <div x-show="selectedSlot"
            class="flex items-center gap-3 p-3 border-2 rounded-xl mb-3 shadow-sm"
            style="background-color:var(--primary-light);border-color:var(--primary);">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" style="background-color:var(--primary);">
                <i class="fa-solid fa-clock text-white text-lg"></i>
            </div>
            <div>
                <p class="text-xl font-extrabold" style="color:var(--primary);" x-text="selectedSlot?.time"></p>
                <p class="text-sm font-medium text-gray-600"
                    x-text="selectedDate ? 'Ngày: ' + new Date(selectedDate.date).toLocaleDateString('vi-VN') : ''"></p>
            </div>
            <i class="fa-solid fa-circle-check text-2xl ml-auto" style="color:#27AE60;"></i>
        </div>

        {{-- Chọn giờ khám (Inline) --}}
        <div class="mt-4 bg-white border-2 border-slate-200 rounded-2xl overflow-hidden shadow-sm transition-all hover:border-primary/30">
            <div class="px-5 py-4 border-b bg-slate-50 flex items-center justify-between">
                <div class="flex items-center gap-2 text-slate-800 font-bold">
                    <i class="fa-solid fa-clock text-primary"></i>
                    <span>Danh sách Giờ khám</span>
                </div>
                <div class="text-sm font-medium text-slate-500" x-text="selectedDate ? 'Ngày ' + new Date(selectedDate.date).toLocaleDateString('vi-VN') : ''"></div>
            </div>

            {{-- Loading --}}
            <div x-show="loadingSlots" class="flex items-center justify-center py-12">
                <i class="fa-solid fa-spinner fa-spin text-3xl" style="color:var(--primary);"></i>
            </div>

            {{-- Slots --}}
            <div x-show="!loadingSlots" class="p-5">
                {{-- Buổi sáng --}}
                <div x-show="slots.filter(s => parseInt(s.time.split(':')[0]) < 12).length > 0" class="mb-6">
                    <div class="flex items-center gap-2 text-orange-500 font-semibold mb-3 text-base">
                        <i class="fa-solid fa-sun"></i>
                        <span>Buổi sáng</span>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:flex md:flex-wrap gap-3">
                        <template x-for="slot in slots.filter(s => parseInt(s.time.split(':')[0]) < 12)" :key="slot.time">
                            <button @click="selectSlot(slot)"
                                :disabled="!slot.available"
                                class="flex justify-center md:justify-start items-center gap-2 px-3 py-3 md:px-4 md:py-2.5 rounded-xl border-2 text-base md:text-sm font-medium transition-all"
                                :class="{
                                            'border-gray-100 text-gray-300 cursor-not-allowed line-through bg-gray-50': !slot.available
                                        }"
                                :style="slot.available
                                            ? (selectedSlot?.time === slot.time
                                                ? 'background-color:var(--primary);border-color:var(--primary);color:#ffffff;box-shadow:0 4px 14px 0 rgba(37,99,235,0.39);'
                                                : 'border-color:#e2e8f0;color:#374151;hover:border-primary/50')
                                            : ''">
                                <i class="fa-regular fa-clock text-sm md:text-xs"></i>
                                <span x-text="slot.time"></span>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Buổi chiều --}}
                <div x-show="slots.filter(s => parseInt(s.time.split(':')[0]) >= 12).length > 0">
                    <div class="flex items-center gap-2 text-blue-500 font-semibold mb-3 text-base">
                        <i class="fa-solid fa-cloud-sun"></i>
                        <span>Buổi chiều</span>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:flex md:flex-wrap gap-3">
                        <template x-for="slot in slots.filter(s => parseInt(s.time.split(':')[0]) >= 12)" :key="slot.time">
                            <button @click="selectSlot(slot)"
                                :disabled="!slot.available"
                                class="flex justify-center md:justify-start items-center gap-2 px-3 py-3 md:px-4 md:py-2.5 rounded-xl border-2 text-base md:text-sm font-medium transition-all"
                                :class="{
                                            'border-gray-100 text-gray-300 cursor-not-allowed line-through bg-gray-50': !slot.available
                                        }"
                                :style="slot.available
                                            ? (selectedSlot?.time === slot.time
                                                ? 'background-color:var(--primary);border-color:var(--primary);color:#ffffff;box-shadow:0 4px 14px 0 rgba(37,99,235,0.39);'
                                                : 'border-color:#e2e8f0;color:#374151;hover:border-primary/50')
                                            : ''">
                                <i class="fa-regular fa-clock text-sm md:text-xs"></i>
                                <span x-text="slot.time"></span>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Không có slot --}}
                <div x-show="slots.length === 0 && !loadingSlots"
                    class="text-center py-8 text-gray-400">
                    <i class="fa-solid fa-calendar-xmark text-4xl mb-3 block opacity-50"></i>
                    <p class="font-medium">Không có lịch khám vào ngày này</p>
                    <p class="text-sm mt-1">Vui lòng chọn ngày khác ở bảng trên</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Empty state --}}
    <div x-show="!selectedDate && availableDates.length > 0"
        class="bg-gray-50 rounded-3xl p-10 text-center text-gray-400 mb-8 border border-gray-100">
        <i class="fa-solid fa-calendar text-5xl mb-4 block text-gray-300"></i>
        <p class="text-lg font-medium">Vui lòng chọn ngày khám ở trên</p>
    </div>

    {{-- Navigation --}}
    <div class="flex gap-4 sticky bottom-0 bg-white pt-4 pb-3 border-t border-slate-100 z-20">
        <button @click="step = 2"
            class="w-1/3 md:w-1/4 py-3 border-2 border-primary/20 text-primary rounded-xl font-bold hover:bg-primary/5 transition-colors active:scale-95 text-base">
            Quay lại
        </button>
        <button @click="goStep4()"
            :disabled="!canGoStep4"
            class="flex-1 py-3 rounded-xl font-extrabold text-white transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-md bg-primary hover:bg-primary-dark text-base">
            Tiếp tục <i class="fa-solid fa-arrow-right ml-1.5"></i>
        </button>
    </div>
</div>
{{-- END BƯỚC 3 --}}