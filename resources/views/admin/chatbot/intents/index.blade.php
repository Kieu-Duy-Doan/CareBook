<x-layouts.admin title="Quản lý kịch bản chatbot">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Kịch bản và phản hồi</h2>
            <p class="text-gray-500 mt-1">Cấu hình các kịch bản để Chatbot nhận diện ý định của người dùng.</p>
        </div>
        <button x-data @click="$dispatch('open-modal', 'add-intent')"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            <i class="fa-solid fa-plus mr-2"></i> Thêm kịch bản
        </button>
    </div>

    @if (session('success'))
        <div class="mb-6 bg-green-50 text-green-800 rounded-xl p-4 flex items-center border border-green-200 shadow-sm" x-data="{ show: true }" x-show="show">
            <i class="fa-solid fa-circle-check text-green-500 mr-3 text-lg"></i>
            <span class="flex-1 text-sm font-medium">{{ session('success') }}</span>
            <button @click="show = false" class="text-green-600 hover:text-green-900 transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 bg-red-50 text-red-800 rounded-xl p-4 flex items-center border border-red-200 shadow-sm" x-data="{ show: true }" x-show="show">
            <i class="fa-solid fa-circle-xmark text-red-500 mr-3 text-lg"></i>
            <span class="flex-1 text-sm font-medium">{{ session('error') }}</span>
            <button @click="show = false" class="text-red-600 hover:text-red-900 transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-gray-50/80 text-gray-700 font-medium border-b border-gray-100 uppercase text-xs">
                <tr>
                    <th class="px-6 py-4">Tên kịch bản (Intent)</th>
                    <th class="px-6 py-4">Hành động (Action)</th>
                    <th class="px-6 py-4">Trạng thái</th>
                    <th class="px-6 py-4 text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($intents as $intent)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900">{{ $intent->intent_name }}</div>
                            <div class="text-xs text-gray-500 mt-0.5">{{ $intent->description }}</div>
                        </td>
                        <td class="px-6 py-4">
                            @if ($intent->action == 'faq_lookup')
                                <span
                                    class="bg-purple-50 text-purple-700 px-2.5 py-1 rounded-md text-xs font-medium border border-purple-100">Tra
                                    cứu FAQ</span>
                            @elseif($intent->action == 'guide_booking')
                                <span
                                    class="bg-blue-50 text-blue-700 px-2.5 py-1 rounded-md text-xs font-medium border border-blue-100">Hướng
                                    dẫn Đặt khám</span>
                            @elseif($intent->action == 'introduce_specialty')
                                <span
                                    class="bg-teal-50 text-teal-700 px-2.5 py-1 rounded-md text-xs font-medium border border-teal-100">Giới
                                    thiệu Khoa</span>
                            @else
                                <span
                                    class="bg-orange-50 text-orange-700 px-2.5 py-1 rounded-md text-xs font-medium border border-orange-100">Chuyển
                                    nhân viên</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <form action="{{ route('admin.chatbot.intents.toggle-active', $intent->id) }}"
                                method="POST">
                                @csrf @method('PATCH')
                                <button type="submit"
                                    class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 {{ $intent->is_active ? 'bg-blue-600' : 'bg-gray-200' }}">
                                    <span
                                        class="inline-block h-3 w-3 transform rounded-full bg-white transition-transform {{ $intent->is_active ? 'translate-x-5' : 'translate-x-1' }}"></span>
                                </button>
                            </form>
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ route('admin.chatbot.intents.show', $intent->id) }}"
                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-blue-600 bg-blue-50 hover:bg-blue-100 transition-colors"
                                title="Chi tiết & Phản hồi">
                                <i class="fa-solid fa-list-check"></i>
                            </a>
                            <form action="{{ route('admin.chatbot.intents.destroy', $intent->id) }}" method="POST"
                                class="inline-block" onsubmit="return confirm('Xác nhận xoá kịch bản này?');">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-600 bg-red-50 hover:bg-red-100 transition-colors"
                                    title="Xoá">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-center text-gray-500">
                            <i class="fa-solid fa-robot text-4xl text-gray-300 mb-3 block"></i>
                            Chưa có kịch bản nào được định nghĩa.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Thêm Kịch bản (alpineJS simple modal) -->
    <div x-data="{ open: false }" @open-modal.window="if ($event.detail === 'add-intent') open = true"
        class="relative z-50" x-show="open" style="display: none;">
        <div x-show="open" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div x-show="open" @click.away="open = false"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-gray-100">
                    
                    <div class="absolute right-0 top-0 pr-5 pt-5">
                        <button type="button" @click="open = false" class="rounded-full bg-gray-50 p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            <span class="sr-only">Close</span>
                            <i class="fa-solid fa-xmark text-lg w-5 h-5 flex items-center justify-center"></i>
                        </button>
                    </div>

                    <form action="{{ route('admin.chatbot.intents.store') }}" method="POST">
                        @csrf
                        <div class="bg-white px-6 pb-6 pt-8 sm:p-8">
                            <div class="mb-8 flex items-start gap-4">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-blue-50 sm:h-14 sm:w-14">
                                    <i class="fa-solid fa-robot text-xl text-blue-600"></i>
                                </div>
                                <div>
                                    <h3 class="text-2xl font-bold text-gray-900">Thêm kịch bản mới</h3>
                                    <p class="text-sm text-gray-500 mt-1">Định nghĩa một ý định để AI nhận diện và phản hồi chính xác.</p>
                                </div>
                            </div>
                            
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Tên kịch bản (Mã Intent) <span class="text-red-500">*</span></label>
                                    <input type="text" name="intent_name" required pattern="[a-z0-9_]+"
                                        placeholder="vd: ask_price"
                                        class="w-full rounded-xl border-gray-200 py-3 px-4 text-sm shadow-sm transition-colors focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                                    <p class="text-[13px] text-gray-500 mt-2 flex items-center"><i class="fa-solid fa-circle-info mr-1.5 text-gray-400"></i> Chỉ dùng chữ thường, số và dấu gạch dưới.</p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Mô tả ngắn <span class="text-red-500">*</span></label>
                                    <input type="text" name="description" required placeholder="Nhập tóm tắt kịch bản..."
                                        class="w-full rounded-xl border-gray-200 py-3 px-4 text-sm shadow-sm transition-colors focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Hành động (Action) <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                            <i class="fa-solid fa-bolt text-gray-400"></i>
                                        </div>
                                        <select name="action" required
                                            class="w-full rounded-xl border-gray-200 py-3 pl-11 pr-10 text-sm shadow-sm transition-colors focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 appearance-none bg-white">
                                            <option value="faq_lookup">Tra cứu FAQ</option>
                                            <option value="guide_booking">Hướng dẫn Đặt khám</option>
                                            <option value="introduce_specialty">Giới thiệu Chuyên khoa</option>
                                            <option value="transfer_staff">Chuyển nhân viên (Live chat)</option>
                                        </select>
                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4">
                                            <i class="fa-solid fa-chevron-down text-gray-400 text-xs"></i>
                                        </div>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Ví dụ câu hỏi của khách</label>
                                    <textarea name="example_phrases" rows="4" placeholder="Giá khám là bao nhiêu | Khám tốn bao tiền"
                                        class="w-full rounded-xl border-gray-200 py-3 px-4 text-sm shadow-sm transition-colors focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 resize-none"></textarea>
                                    <div class="bg-blue-50/50 rounded-lg p-3.5 mt-3 border border-blue-100 flex items-start">
                                        <i class="fa-solid fa-lightbulb text-blue-500 mt-0.5 mr-2.5"></i>
                                        <p class="text-[13px] text-blue-800 leading-relaxed font-medium">Cung cấp nhiều mẫu câu khác nhau giúp AI nhận diện tốt hơn. Phân cách các câu bằng ký tự <code class="bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded mx-0.5">|</code> hoặc <code class="bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded mx-0.5">│</code>.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50/80 px-6 py-5 border-t border-gray-100 sm:flex sm:flex-row-reverse sm:px-8">
                            <button type="submit"
                                class="inline-flex w-full justify-center items-center rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-700 hover:shadow-md transition-all sm:ml-3 sm:w-auto">
                                <i class="fa-solid fa-floppy-disk mr-2"></i> Lưu kịch bản
                            </button>
                            <button type="button" @click="open = false"
                                class="mt-3 inline-flex w-full justify-center items-center rounded-xl bg-white px-6 py-2.5 text-sm font-bold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition-all sm:mt-0 sm:w-auto">
                                Hủy bỏ
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
