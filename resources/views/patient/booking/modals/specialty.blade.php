{{-- ===== MODAL CHỌN CHUYÊN KHOA ===== --}}
    <div x-show="showSpecialtyModal" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/50 flex items-end md:items-start justify-center p-0 md:p-6 md:pt-32"
         style="z-index: 9999;"
         @click.self="showSpecialtyModal = false">
        <div class="w-full md:max-w-3xl bg-white rounded-t-3xl md:rounded-3xl flex flex-col shadow-2xl overflow-hidden"
             style="max-height: calc(100vh - 150px);"
             @click.stop>
            <div class="flex items-center justify-between px-5 py-4 border-b" style="border-color:#e2e8f0;">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-briefcase-medical" style="color:var(--primary);"></i>
                    <span class="font-bold text-lg text-gray-800">Chọn chuyên khoa</span>
                </div>
                <button @click="showSpecialtyModal = false"
                        class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center hover:bg-gray-200 transition-colors">
                    <i class="fa-solid fa-xmark text-gray-500"></i>
                </button>
            </div>

            <div class="px-4 py-3 border-b" style="border-color:#e2e8f0;">
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2" style="color:var(--primary);"></i>
                    <input x-model="specialtySearch" type="search"
                           placeholder='Tìm kiếm... (VD: "Tim mạch")'
                           class="w-full pl-10 pr-4 py-3 border-2 rounded-xl text-sm focus:outline-none"
                           style="border-color:var(--primary);">
                </div>
            </div>

            <div class="overflow-y-auto flex-1 min-h-0">
                <template x-for="specialty in filteredSpecialties" :key="specialty.id">
                    <div @click="selectSpecialty(specialty)"
                         class="flex items-center gap-4 px-5 py-4 border-b hover:bg-blue-50 cursor-pointer transition-colors"
                         style="border-color:#f8fafc;">
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-gray-800" x-text="specialty.name"></p>
                            <p class="text-sm text-gray-400 mt-0.5 line-clamp-2" x-text="specialty.description"></p>
                        </div>
                        <i class="fa-solid fa-chevron-right text-gray-300 flex-shrink-0"></i>
                    </div>
                </template>

                <div x-show="filteredSpecialties.length === 0" class="text-center py-12 text-gray-400">
                    <i class="fa-solid fa-magnifying-glass text-3xl mb-3 block"></i>
                    <p>Không tìm thấy chuyên khoa nào</p>
                </div>
            </div>
        </div>
    </div>
