<x-layouts.admin title="Chi tiết Kịch bản: {{ $intent->intent_name }}">
    <div class="mb-6 flex justify-between items-center">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.chatbot.intents.index') }}"
                class="w-10 h-10 bg-white border border-gray-200 rounded-lg flex items-center justify-center text-gray-600 hover:bg-gray-50 transition-colors">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ $intent->intent_name }}</h2>
                <p class="text-gray-500 mt-1">Cấu hình câu trả lời cho kịch bản này.</p>
            </div>
        </div>
        <button x-data @click="$dispatch('open-modal', 'edit-intent')"
            class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            <i class="fa-solid fa-pen mr-2"></i> Sửa kịch bản
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

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Thông tin Kịch bản -->
        <div class="col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Thông tin Kịch bản</h3>
                <div class="space-y-4 text-sm">
                    <div>
                        <span class="block text-gray-500 text-xs mb-1">Mô tả</span>
                        <div class="font-medium text-gray-900">{{ $intent->description }}</div>
                    </div>
                    <div>
                        <span class="block text-gray-500 text-xs mb-1">Hành động</span>
                        <div class="font-medium text-blue-600">{{ $intent->action }}</div>
                    </div>
                    <div>
                        <span class="block text-gray-500 text-xs mb-1">Trạng thái</span>
                        {!! $intent->is_active
                            ? '<span class="text-green-600 font-medium">Đang hoạt động</span>'
                            : '<span class="text-red-600 font-medium">Đã tắt</span>' !!}
                    </div>
                    <div>
                        <span class="block text-gray-500 text-xs mb-1">Ví dụ khách hỏi</span>
                        <div
                            class="bg-gray-50 p-3 rounded border border-gray-100 whitespace-pre-wrap font-mono text-xs">
                            {{ str_replace('│', "\n", $intent->example_phrases) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Danh sách Câu trả lời -->
        <div class="col-span-1 md:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="flex justify-between items-center p-6 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-900">Danh sách Phản hồi (Responses)</h3>
                    <button x-data @click="$dispatch('open-modal', 'add-response')"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg text-sm font-medium transition-colors">
                        <i class="fa-solid fa-plus mr-1"></i> Thêm mới
                    </button>
                </div>
                <div class="p-0">
                    @forelse($intent->responses as $res)
                        <div class="p-6 border-b border-gray-100 last:border-0 hover:bg-gray-50 transition-colors">
                            <div class="flex justify-between items-start gap-4">
                                <div class="flex-1">
                                    <div
                                        class="bg-blue-50/50 p-4 rounded-xl border border-blue-100 relative text-gray-800 text-sm mb-3">
                                        {!! nl2br(e($res->content)) !!}
                                    </div>
                                    <div class="flex items-center gap-4 text-xs text-gray-500">
                                        <span><i class="fa-solid fa-arrow-up-1-9 mr-1"></i> Ưu tiên:
                                            {{ $res->priority }}</span>
                                        <span><i class="fa-solid fa-fire mr-1"></i> Lượt dùng:
                                            {{ number_format($res->use_count) }}</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <form
                                        action="{{ route('admin.chatbot.intents.responses.toggle-active', ['intent_id' => $intent->id, 'id' => $res->id]) }}"
                                        method="POST">
                                        @csrf @method('PATCH')
                                        <button type="submit"
                                            class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors focus:outline-none {{ $res->is_active ? 'bg-green-500' : 'bg-gray-200' }}"
                                            title="Bật/Tắt">
                                            <span
                                                class="inline-block h-3 w-3 transform rounded-full bg-white transition-transform {{ $res->is_active ? 'translate-x-5' : 'translate-x-1' }}"></span>
                                        </button>
                                    </form>
                                    <form
                                        action="{{ route('admin.chatbot.intents.responses.destroy', ['intent_id' => $intent->id, 'id' => $res->id]) }}"
                                        method="POST" onsubmit="return confirm('Xoá câu trả lời này?');">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="w-8 h-8 rounded-lg text-red-600 bg-red-50 hover:bg-red-100 transition-colors"
                                            title="Xoá">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-10 text-center text-gray-500">
                            Chưa có câu trả lời nào. Chatbot sẽ không thể phản hồi ý định này.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Add Response -->
    <div x-data="{ open: false }" @open-modal.window="if ($event.detail === 'add-response') open = true"
        class="relative z-50" x-show="open" style="display: none;">
        <div x-show="open" class="fixed inset-0 bg-gray-900/50 transition-opacity"></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div x-show="open" @click.away="open = false"
                    class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    <form action="{{ route('admin.chatbot.intents.responses.store', $intent->id) }}" method="POST">
                        @csrf
                        <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">Thêm Câu trả lời</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nội dung trả lời
                                        *</label>
                                    <textarea name="content" required rows="5"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                                    <p class="text-xs text-gray-500 mt-1">Hỗ trợ placeholders:
                                        <code>@{{ ten_nguoi_dung }}</code>, <code>@{{ ten_chuyen_khoa }}</code>
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Độ ưu tiên (Priority)
                                        *</label>
                                    <input type="number" name="priority" value="1" min="1" required
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                            <button type="submit"
                                class="inline-flex w-full justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 sm:ml-3 sm:w-auto">Lưu
                                lại</button>
                            <button type="button" @click="open = false"
                                class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">Hủy</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Intent -->
    <div x-data="{ open: false }" @open-modal.window="if ($event.detail === 'edit-intent') open = true"
        class="relative z-50" x-show="open" style="display: none;">
        <div x-show="open" class="fixed inset-0 bg-gray-900/50 transition-opacity"></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div x-show="open" @click.away="open = false"
                    class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    <form action="{{ route('admin.chatbot.intents.update', $intent->id) }}" method="POST">
                        @csrf @method('PUT')
                        <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">Sửa Kịch bản</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tên Kịch bản (Mã
                                        Intent) *</label>
                                    <input type="text" name="intent_name" value="{{ $intent->intent_name }}"
                                        required pattern="[a-z0-9_]+"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Mô tả *</label>
                                    <input type="text" name="description" value="{{ $intent->description }}"
                                        required
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Hành động (Action)
                                        *</label>
                                    <select name="action" required
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="faq_lookup"
                                            {{ $intent->action == 'faq_lookup' ? 'selected' : '' }}>Tra cứu FAQ
                                        </option>
                                        <option value="guide_booking"
                                            {{ $intent->action == 'guide_booking' ? 'selected' : '' }}>Hướng dẫn Đặt
                                            khám</option>
                                        <option value="introduce_specialty"
                                            {{ $intent->action == 'introduce_specialty' ? 'selected' : '' }}>Giới thiệu
                                            Chuyên khoa</option>
                                        <option value="transfer_staff"
                                            {{ $intent->action == 'transfer_staff' ? 'selected' : '' }}>Chuyển nhân
                                            viên</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Ví dụ khách hỏi</label>
                                    <textarea name="example_phrases" rows="3"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ $intent->example_phrases }}</textarea>
                                </div>
                                <div>
                                    <label class="flex items-center text-sm font-medium text-gray-700">
                                        <input type="checkbox" name="is_active" value="1"
                                            {{ $intent->is_active ? 'checked' : '' }}
                                            class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        Hoạt động
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                            <button type="submit"
                                class="inline-flex w-full justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 sm:ml-3 sm:w-auto">Lưu
                                thay đổi</button>
                            <button type="button" @click="open = false"
                                class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">Hủy</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
