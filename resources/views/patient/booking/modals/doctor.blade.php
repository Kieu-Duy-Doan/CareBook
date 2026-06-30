{{-- ===== MODAL CHỌN BÁC SĨ ===== --}}
    <div x-show="showDoctorModal" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/50 flex items-end md:items-start justify-center p-0 md:p-6 md:pt-32"
         style="z-index: 9999;"
         @click.self="showDoctorModal = false">
        <div class="w-full md:max-w-3xl bg-white rounded-t-3xl md:rounded-3xl flex flex-col shadow-2xl overflow-hidden"
             style="max-height: calc(100vh - 150px);"
             @click.stop>
            <div class="flex items-center justify-between px-5 py-4 border-b" style="border-color:#e2e8f0;">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-user-doctor" style="color:var(--primary);"></i>
                    <span class="font-bold text-lg text-gray-800">Chọn bác sĩ</span>
                </div>
                <button @click="showDoctorModal = false"
                        class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center hover:bg-gray-200 transition-colors">
                    <i class="fa-solid fa-xmark text-gray-500"></i>
                </button>
            </div>

            <div class="px-4 py-3 border-b" style="border-color:#e2e8f0;">
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2" style="color:var(--primary);"></i>
                    <input x-model="doctorSearch" type="search"
                           placeholder='Tìm kiếm... (VD: "Nguyễn")'
                           class="w-full pl-10 pr-4 py-3 border-2 rounded-xl text-sm focus:outline-none"
                           style="border-color:var(--primary);">
                </div>
            </div>

            <div class="overflow-y-auto flex-1 min-h-0">
                <template x-for="doctor in filteredDoctors" :key="doctor.id">
                    <div @click="selectDoctor(doctor)"
                         class="flex items-center gap-4 px-5 py-4 border-b hover:bg-blue-50 cursor-pointer transition-colors"
                         style="border-color:#f8fafc;">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0"
                             style="background-color:rgba(29,111,164,0.10);">
                            <span class="font-bold" style="color:var(--primary);"
                                  x-text="doctor.full_title.split(' ').slice(-2).map(w=>w[0]).join('').slice(0,2).toUpperCase()"></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-gray-800 uppercase truncate" x-text="doctor.full_title"></p>
                            <p class="text-sm text-gray-400" x-text="doctor.level_label"></p>
                            <p class="text-sm font-medium" style="color:var(--primary);" x-text="doctor.primary_specialty"></p>
                        </div>
                        <i class="fa-solid fa-chevron-right text-gray-300 flex-shrink-0"></i>
                    </div>
                </template>

                <div x-show="filteredDoctors.length === 0" class="text-center py-12 text-gray-400">
                    <i class="fa-solid fa-user-doctor text-3xl mb-3 block"></i>
                    <p>Không tìm thấy bác sĩ nào</p>
                </div>
            </div>
        </div>
    </div>
    {{-- END MODALS BƯỚC 2 --}}