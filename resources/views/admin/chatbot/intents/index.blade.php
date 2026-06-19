<x-layouts.admin title="Quản lý Kịch bản Chatbot">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Kịch bản (Intents) & Phản hồi</h2>
            <p class="text-gray-500 mt-1">Cấu hình các kịch bản để Chatbot nhận diện ý định của người dùng.</p>
        </div>
        <button x-data @click="$dispatch('open-modal', 'add-intent')"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            <i class="fa-solid fa-plus mr-2"></i> Thêm Kịch bản
        </button>
    </div>

    @if (session('success'))
        <div class="mb-6 bg-green-50 text-green-800 rounded-lg p-4 flex items-center border border-green-200">
            <i class="fa-solid fa-circle-check text-green-500 mr-3 text-lg"></i>
            <span class="flex-1 text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="mb-6 bg-red-50 text-red-800 rounded-lg p-4 flex items-center border border-red-200">
            <i class="fa-solid fa-circle-exclamation text-red-500 mr-3 text-lg"></i>
            <span class="flex-1 text-sm font-medium">{{ session('error') }}</span>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-gray-50/80 text-gray-700 font-medium border-b border-gray-100 uppercase text-xs">
                <tr>
                    <th class="px-6 py-4">Tên Kịch bản (Intent)</th>
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

    <!-- Modal Thêm Kịch bản (alpineJS simple modal) -->
    <div x-data="{ open: false }" @open-modal.window="if ($event.detail === 'add-intent') open = true"
        class="relative z-50" x-show="open" style="display: none;">
        <div x-show="open" class="fixed inset-0 bg-gray-900/50 transition-opacity"></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div x-show="open" @click.away="open = false"
                    class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    <form action="{{ route('admin.chatbot.intents.store') }}" method="POST">
                        @csrf
                        <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">Thêm Kịch bản mới</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tên Kịch bản (Mã Intent)
                                        *</label>
                                    <input type="text" name="intent_name" required pattern="[a-z0-9_]+"
                                        placeholder="vd: ask_price"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <p class="text-xs text-gray-500 mt-1">Chỉ dùng chữ thường, số và dấu gạch dưới.</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Mô tả *</label>
                                    <input type="text" name="description" required
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Hành động (Action)
                                        *</label>
                                    <select name="action" required
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="faq_lookup">Tra cứu FAQ</option>
                                        <option value="guide_booking">Hướng dẫn Đặt khám</option>
                                        <option value="introduce_specialty">Giới thiệu Chuyên khoa</option>
                                        <option value="transfer_staff">Chuyển nhân viên (Live chat)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Ví dụ câu hỏi của
                                        khách</label>
                                    <textarea name="example_phrases" rows="3" placeholder="Giá khám là bao nhiêu│Khám tốn bao tiền"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                                    <p class="text-xs text-gray-500 mt-1">Phân cách các câu bằng ký tự │ (Shift + \ trên
                                        đa số bàn phím)</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                            <button type="submit"
                                class="inline-flex w-full justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 sm:ml-3 sm:w-auto">Lưu
                                Kịch bản</button>
                            <button type="button" @click="open = false"
                                class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">Hủy</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
