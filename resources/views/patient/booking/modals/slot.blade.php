{{-- ===== MODAL CHỌN GIỜ KHÁM ===== --}}
    <div x-show="showSlotModal" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/50 flex items-end md:items-start justify-center p-0 md:p-6 md:pt-32"
         style="z-index: 9999;"
         @click.self="showSlotModal = false">
        <div class="w-full md:max-w-3xl bg-white rounded-t-3xl md:rounded-3xl flex flex-col shadow-2xl overflow-hidden"
             style="max-height: calc(100vh - 150px);"
             @click.stop>
            {{-- Header modal --}}
            <div class="flex items-center justify-between px-5 py-4 rounded-t-3xl" style="background-color:var(--primary);">
                <div class="flex items-center gap-2 text-white">
                    <i class="fa-solid fa-clock"></i>
                    <span class="font-bold">Chọn giờ khám</span>
                </div>
                <div class="text-white text-sm opacity-80"
                     x-text="selectedDate ? 'Ngày ' + new Date(selectedDate.date).toLocaleDateString('vi-VN') : ''"></div>
                <button @click="showSlotModal = false">
                    <i class="fa-solid fa-xmark text-white text-xl"></i>
                </button>
            </div>

            {{-- Loading --}}
            <div x-show="loadingSlots" class="flex items-center justify-center py-12">
                <i class="fa-solid fa-spinner fa-spin text-3xl" style="color:var(--primary);"></i>
            </div>

            {{-- Slots --}}
            <div x-show="!loadingSlots" class="overflow-y-auto flex-1 min-h-0 px-5 py-4">

                {{-- Phòng khám --}}
                <div x-show="selectedDoctor?.room_name"
                     class="flex items-center gap-2 font-semibold mb-4 p-3 rounded-xl"
                     style="color:var(--primary);background-color:var(--primary-light);">
                    <i class="fa-solid fa-location-dot"></i>
                    <span x-text="selectedDoctor?.room_name"></span>
                </div>

                {{-- Buổi sáng --}}
                <div x-show="slots.filter(s => parseInt(s.time.split(':')[0]) < 12).length > 0" class="mb-6">
                    <div class="flex items-center gap-2 text-orange-500 font-semibold mb-3">
                        <i class="fa-solid fa-sun"></i>
                        <span>Buổi sáng</span>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <template x-for="slot in slots.filter(s => parseInt(s.time.split(':')[0]) < 12)" :key="slot.time">
                            <button @click="selectSlot(slot)"
                                    :disabled="!slot.available"
                                    class="flex items-center gap-2 px-4 py-3 rounded-xl border-2 text-sm font-medium transition-all"
                                    :class="{
                                        'border-gray-100 text-gray-300 cursor-not-allowed line-through bg-gray-50': !slot.available
                                    }"
                                    :style="slot.available
                                        ? (selectedSlot?.time === slot.time
                                            ? 'background-color:var(--primary);border-color:var(--primary);color:#ffffff;'
                                            : 'border-color:#e2e8f0;color:#374151;')
                                        : ''">
                                <i class="fa-regular fa-clock text-xs"></i>
                                <span x-text="slot.time"></span>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Buổi chiều --}}
                <div x-show="slots.filter(s => parseInt(s.time.split(':')[0]) >= 12).length > 0">
                    <div class="flex items-center gap-2 text-blue-500 font-semibold mb-3">
                        <i class="fa-solid fa-cloud-sun"></i>
                        <span>Buổi chiều</span>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <template x-for="slot in slots.filter(s => parseInt(s.time.split(':')[0]) >= 12)" :key="slot.time">
                            <button @click="selectSlot(slot)"
                                    :disabled="!slot.available"
                                    class="flex items-center gap-2 px-4 py-3 rounded-xl border-2 text-sm font-medium transition-all"
                                    :class="{
                                        'border-gray-100 text-gray-300 cursor-not-allowed line-through bg-gray-50': !slot.available
                                    }"
                                    :style="slot.available
                                        ? (selectedSlot?.time === slot.time
                                            ? 'background-color:var(--primary);border-color:var(--primary);color:#ffffff;'
                                            : 'border-color:#e2e8f0;color:#374151;')
                                        : ''">
                                <i class="fa-regular fa-clock text-xs"></i>
                                <span x-text="slot.time"></span>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Không có slot --}}
                <div x-show="slots.length === 0 && !loadingSlots"
                     class="text-center py-8 text-gray-400">
                    <i class="fa-solid fa-calendar-xmark text-4xl mb-3 block"></i>
                    <p>Không có lịch khám vào ngày này</p>
                    <p class="text-sm mt-1">Vui lòng chọn ngày khác</p>
                </div>
            </div>
        </div>
    </div>
    {{-- END MODAL SLOT --}}