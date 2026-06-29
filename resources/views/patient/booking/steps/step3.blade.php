{{-- ===== BƯỚC 3: CHỌN LỊCH KHÁM ===== --}}
    <div x-show="step === 3" class="max-w-5xl mx-auto px-4 py-8">
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
        <div class="mb-8">
            <p class="font-bold text-lg mb-4" style="color:var(--primary);">Chọn ngày khám:</p>

            {{-- Horizontal scroll dates --}}
            <div class="flex gap-4 overflow-x-auto pb-4" style="-ms-overflow-style:none;scrollbar-width:none;">
                <template x-for="dateObj in availableDates" :key="dateObj.date">
                    <button @click="selectDate(dateObj)"
                            type="button"
                            class="flex-shrink-0 flex flex-col items-center justify-center w-24 h-28 rounded-2xl border-2 transition-all"
                            :class="selectedDate?.date === dateObj.date
                                ? 'border-primary bg-primary text-white shadow-lg scale-105'
                                : 'border-gray-200 text-gray-600 hover:border-primary/50 hover:bg-primary/5'">
                        <span class="text-sm font-medium" x-text="dateObj.day_name"></span>
                        <span class="text-3xl font-extrabold my-1" x-text="dateObj.display"></span>
                    </button>
                </template>

                {{-- Loading skeleton --}}
                <template x-if="loadingDates">
                    <div class="flex gap-4">
                        <template x-for="i in [1,2,3,4,5]" :key="i">
                            <div class="flex-shrink-0 w-24 h-28 rounded-2xl bg-gray-100 animate-pulse"></div>
                        </template>
                    </div>
                </template>
            </div>
        </div>

        {{-- Giờ khám (hiện sau khi chọn ngày) --}}
        <div x-show="selectedDate" x-transition class="bg-white border rounded-3xl p-6 mb-8 shadow-sm" style="border-color:#e2e8f0;">
            {{-- Header phòng --}}
            <div class="rounded-xl p-4 mb-6" style="background-color:rgba(29,111,164,0.08);">
                <div class="flex items-center gap-3 font-semibold text-lg" style="color:var(--primary);">
                    <i class="fa-solid fa-location-dot"></i>
                    <span x-show="bookingMethod === 'doctor'" x-text="selectedDoctor?.room_name ?? 'Phòng khám'"></span>
                    <span x-show="bookingMethod === 'specialty'" x-text="selectedSpecialty?.name"></span>
                </div>
            </div>

            {{-- Đã chọn slot --}}
            <div x-show="selectedSlot"
                 class="flex items-center gap-4 p-5 border-2 rounded-2xl mb-6 shadow-sm"
                 style="background-color:var(--primary-light);border-color:var(--primary);">
                <div class="w-14 h-14 rounded-xl flex items-center justify-center flex-shrink-0" style="background-color:var(--primary);">
                    <i class="fa-solid fa-clock text-white text-2xl"></i>
                </div>
                <div>
                    <p class="text-2xl font-extrabold" style="color:var(--primary);" x-text="selectedSlot?.time"></p>
                    <p class="text-base font-medium text-gray-600"
                       x-text="selectedDate ? 'Ngày: ' + new Date(selectedDate.date).toLocaleDateString('vi-VN') : ''"></p>
                </div>
                <i class="fa-solid fa-circle-check text-3xl ml-auto" style="color:#27AE60;"></i>
            </div>

            {{-- Nút đổi/chọn giờ --}}
            <button @click="showSlotModal = true"
                    class="w-full flex items-center justify-center gap-3 py-4 border-2 border-dashed border-gray-300 rounded-2xl text-gray-600 hover:border-primary hover:text-primary hover:bg-primary/5 transition-colors text-lg font-bold">
                <i x-show="!selectedSlot" class="fa-regular fa-clock text-xl"></i>
                <i x-show="selectedSlot" class="fa-solid fa-pen text-lg"></i>
                <span x-text="selectedSlot ? 'Thay đổi giờ khám' : 'Bấm vào đây để chọn giờ khám'"></span>
            </button>
        </div>

        {{-- Empty state --}}
        <div x-show="!selectedDate"
             class="bg-gray-50 rounded-3xl p-10 text-center text-gray-400 mb-8 border border-gray-100">
            <i class="fa-solid fa-calendar text-5xl mb-4 block text-gray-300"></i>
            <p class="text-lg font-medium">Vui lòng chọn ngày khám ở trên</p>
        </div>

        {{-- Navigation --}}
        <div class="flex gap-4 sticky bottom-0 bg-white pt-6 pb-4 border-t border-slate-100 z-20">
            <button @click="step = 2"
                    class="flex-1 py-4 border-2 border-primary/20 text-primary rounded-2xl font-bold hover:bg-primary/5 transition-colors active:scale-95 text-lg">
                <i class="fa-solid fa-arrow-left mr-1.5"></i> Quay lại
            </button>
            <button @click="goStep4()"
                    :disabled="!canGoStep4"
                    class="py-4 rounded-2xl font-extrabold text-white transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:active:scale-100 shadow-lg shadow-primary/30 hover:shadow-primary/40 active:scale-95 bg-primary hover:bg-primary-dark text-lg"
                    style="flex:2;">
                Tiếp tục <i class="fa-solid fa-arrow-right ml-1.5"></i>
            </button>
        </div>
    </div>
    {{-- END BƯỚC 3 --}}