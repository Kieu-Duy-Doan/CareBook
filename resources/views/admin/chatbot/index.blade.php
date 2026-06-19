<x-layouts.admin title="Cấu hình Chatbot">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Trợ lý Ảo (Chatbot)</h2>
            <p class="text-gray-500 mt-1">Đào tạo (Train) AI bằng cách định nghĩa các Ý định và Câu trả lời mẫu.</p>
        </div>
        <div class="flex items-center gap-4 text-sm">
            <div
                class="bg-blue-50 text-blue-700 px-4 py-2 rounded-lg border border-blue-100 flex items-center gap-2 font-medium">
                <i class="fa-solid fa-comments"></i>
                {{ number_format($totalSessions) }} Phiên Chat
            </div>
            <div
                class="bg-green-50 text-green-700 px-4 py-2 rounded-lg border border-green-100 flex items-center gap-2 font-medium">
                <i class="fa-solid fa-message"></i>
                {{ number_format($totalMessages) }} Tin nhắn
            </div>
        </div>
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

    <div x-data="{
        activeTab: 'intents',
        showIntentModal: false,
        showResponseModal: false,
        editIntent: null,
        editResponse: null,
    
        // Test Chatbot State
        chatMessages: [],
        chatInput: '',
        isTyping: false,
    
        async sendTestMessage() {
            if (!this.chatInput.trim() || this.isTyping) return;
    
            const userText = this.chatInput;
            this.chatMessages.push({ role: 'user', text: userText });
            this.chatInput = '';
            this.isTyping = true;
    
            // Scroll to bottom
            this.$nextTick(() => { this.$refs.chatBox.scrollTop = this.$refs.chatBox.scrollHeight; });
    
            try {
                const res = await fetch('{{ route('admin.chatbot.test') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ message: userText })
                });
                const data = await res.json();
    
                this.chatMessages.push({ role: 'bot', text: data.reply || data.error });
            } catch (e) {
                this.chatMessages.push({ role: 'bot', text: 'Lỗi hệ thống: Không thể kết nối đến AI.' });
            }
    
            this.isTyping = false;
            this.$nextTick(() => { this.$refs.chatBox.scrollTop = this.$refs.chatBox.scrollHeight; });
        }
    }" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">

        <!-- Tabs Navigation -->
        <div class="flex border-b border-gray-100 overflow-x-auto hide-scrollbar bg-gray-50/50">
            <button @click="activeTab = 'intents'"
                class="px-6 py-4 text-sm font-medium transition-colors border-b-2 whitespace-nowrap"
                :class="activeTab === 'intents' ? 'border-blue-600 text-blue-600 bg-white' :
                    'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-100'">
                <i class="fa-solid fa-brain mr-2"></i> 1. Danh sách Ý định (Intents)
            </button>
            <button @click="activeTab = 'responses'"
                class="px-6 py-4 text-sm font-medium transition-colors border-b-2 whitespace-nowrap"
                :class="activeTab === 'responses' ? 'border-blue-600 text-blue-600 bg-white' :
                    'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-100'">
                <i class="fa-solid fa-reply-all mr-2"></i> 2. Kịch bản Phản hồi
            </button>
            <button @click="activeTab = 'test'"
                class="px-6 py-4 text-sm font-medium transition-colors border-b-2 whitespace-nowrap"
                :class="activeTab === 'test' ? 'border-purple-600 text-purple-600 bg-white' :
                    'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-100'">
                <i class="fa-solid fa-robot mr-2 text-purple-500"></i> Test AI Chatbot
            </button>
        </div>

        <!-- TAB 1: INTENTS -->
        <div x-show="activeTab === 'intents'" style="display: none;">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-800">Các Ý định (Intents) đã học</h3>
                    <button @click="editIntent = null; showIntentModal = true"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        <i class="fa-solid fa-plus mr-1"></i> Dạy Ý định mới
                    </button>
                </div>

                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3">Ý định</th>
                                <th class="px-4 py-3 w-1/3">Mẫu câu hỏi (Keywords)</th>
                                <th class="px-4 py-3 text-center">Hành động AI</th>
                                <th class="px-4 py-3 text-center">Trạng thái</th>
                                <th class="px-4 py-3 text-right">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($intents as $intent)
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900">{{ $intent->intent_name }}</div>
                                        <div class="text-xs text-gray-500 mt-1">{{ $intent->description }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="text-gray-600 line-clamp-2 text-xs bg-gray-100 p-2 rounded">
                                            {{ $intent->example_phrases ?: 'Chưa có mẫu' }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if ($intent->action === 'faq_lookup')
                                            <span
                                                class="bg-blue-100 text-blue-700 text-xs font-semibold px-2.5 py-1 rounded">Tra
                                                cứu FAQ</span>
                                        @elseif($intent->action === 'guide_booking')
                                            <span
                                                class="bg-green-100 text-green-700 text-xs font-semibold px-2.5 py-1 rounded">Hướng
                                                dẫn đặt lịch</span>
                                        @elseif($intent->action === 'transfer_staff')
                                            <span
                                                class="bg-red-100 text-red-700 text-xs font-semibold px-2.5 py-1 rounded">Gặp
                                                nhân viên</span>
                                        @else
                                            <span
                                                class="bg-gray-100 text-gray-700 text-xs font-semibold px-2.5 py-1 rounded">{{ $intent->action }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <form action="{{ route('admin.chatbot.intents.toggle-active', $intent->id) }}"
                                            method="POST">
                                            @csrf @method('PATCH')
                                            <button type="submit"
                                                class="relative inline-flex items-center h-5 rounded-full w-9 focus:outline-none {{ $intent->is_active ? 'bg-green-500' : 'bg-gray-300' }}">
                                                <span
                                                    class="inline-block w-3.5 h-3.5 transform bg-white rounded-full transition {{ $intent->is_active ? 'translate-x-4.5' : 'translate-x-1' }}"></span>
                                            </button>
                                        </form>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button
                                                @click="editIntent = {{ json_encode($intent) }}; showIntentModal = true"
                                                class="text-blue-600 hover:text-blue-900 p-1">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <form action="{{ route('admin.chatbot.intents.destroy', $intent->id) }}"
                                                method="POST" onsubmit="return confirm('Xoá ý định này?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 p-1"><i
                                                        class="fa-solid fa-trash-can"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            @if ($intents->isEmpty())
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">Chưa có Ý định nào
                                        được cấu hình.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 2: RESPONSES -->
        <div x-show="activeTab === 'responses'" style="display: none;">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-800">Kịch bản Phản hồi</h3>
                    <button @click="editResponse = null; showResponseModal = true"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        <i class="fa-solid fa-plus mr-1"></i> Thêm Phản hồi
                    </button>
                </div>

                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3">Thuộc Ý định</th>
                                <th class="px-4 py-3 w-1/2">Nội dung trả lời</th>
                                <th class="px-4 py-3 text-center">Độ ưu tiên</th>
                                <th class="px-4 py-3 text-center">Trạng thái</th>
                                <th class="px-4 py-3 text-right">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($responses as $response)
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium text-gray-900">
                                        {{ $response->intent->intent_name ?? 'N/A' }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 whitespace-pre-wrap">{{ $response->content }}
                                    </td>
                                    <td class="px-4 py-3 text-center font-bold text-blue-600">{{ $response->priority }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <form
                                            action="{{ route('admin.chatbot.responses.toggle-active', $response->id) }}"
                                            method="POST">
                                            @csrf @method('PATCH')
                                            <button type="submit"
                                                class="relative inline-flex items-center h-5 rounded-full w-9 focus:outline-none {{ $response->is_active ? 'bg-green-500' : 'bg-gray-300' }}">
                                                <span
                                                    class="inline-block w-3.5 h-3.5 transform bg-white rounded-full transition {{ $response->is_active ? 'translate-x-4.5' : 'translate-x-1' }}"></span>
                                            </button>
                                        </form>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button
                                                @click="editResponse = {{ json_encode($response) }}; showResponseModal = true"
                                                class="text-blue-600 hover:text-blue-900 p-1">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <form
                                                action="{{ route('admin.chatbot.responses.destroy', $response->id) }}"
                                                method="POST" onsubmit="return confirm('Xoá câu trả lời này?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 p-1"><i
                                                        class="fa-solid fa-trash-can"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            @if ($responses->isEmpty())
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">Chưa có Câu trả lời
                                        nào được cấu hình.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $responses->links() }}
                </div>
            </div>
        </div>

        <!-- TAB 3: TEST CHATBOT -->
        <div x-show="activeTab === 'test'" style="display: none;" class="bg-gray-50 p-6 min-h-[500px]">
            <div
                class="max-w-2xl mx-auto bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden flex flex-col h-[600px]">
                <!-- Header -->
                <div
                    class="bg-gradient-to-r from-blue-600 to-indigo-600 p-4 text-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm">
                            <i class="fa-solid fa-robot text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold">Carebook AI Bot</h3>
                            <p class="text-xs text-blue-100 flex items-center gap-1"><span
                                    class="w-2 h-2 rounded-full bg-green-400"></span> Trực tuyến (Gemini 1.5)</p>
                        </div>
                    </div>
                    <button @click="chatMessages = []" class="text-white/80 hover:text-white text-sm"
                        title="Xóa lịch sử chat"><i class="fa-solid fa-rotate-right"></i> Làm mới</button>
                </div>

                <!-- Chat Window -->
                <div x-ref="chatBox" class="flex-1 p-4 overflow-y-auto bg-gray-50 space-y-4">
                    <div class="text-center text-xs text-gray-400 mb-4">Môi trường giả lập (Sandbox)</div>

                    <template x-for="(msg, i) in chatMessages" :key="i">
                        <div class="flex gap-3" :class="msg.role === 'user' ? 'flex-row-reverse' : ''">
                            <!-- Avatar -->
                            <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0"
                                :class="msg.role === 'user' ? 'bg-blue-100 text-blue-600' :
                                    'bg-gradient-to-br from-indigo-500 to-purple-600 text-white'">
                                <i class="fa-solid" :class="msg.role === 'user' ? 'fa-user' : 'fa-robot'"></i>
                            </div>
                            <!-- Bubble -->
                            <div class="max-w-[75%] rounded-2xl px-4 py-2.5 text-sm whitespace-pre-wrap"
                                :class="msg.role === 'user' ? 'bg-blue-600 text-white rounded-tr-sm' :
                                    'bg-white border border-gray-200 text-gray-800 rounded-tl-sm'">
                                <span x-text="msg.text"></span>
                            </div>
                        </div>
                    </template>

                    <!-- Typing Indicator -->
                    <div x-show="isTyping" class="flex gap-3">
                        <div
                            class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 bg-gradient-to-br from-indigo-500 to-purple-600 text-white">
                            <i class="fa-solid fa-robot"></i>
                        </div>
                        <div
                            class="bg-white border border-gray-200 rounded-2xl rounded-tl-sm px-4 py-3 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce"></span>
                            <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce"
                                style="animation-delay: 0.1s"></span>
                            <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce"
                                style="animation-delay: 0.2s"></span>
                        </div>
                    </div>
                </div>

                <!-- Input Area -->
                <div class="p-3 bg-white border-t border-gray-200">
                    <form @submit.prevent="sendTestMessage" class="flex gap-2 relative">
                        <input x-model="chatInput" type="text" placeholder="Hỏi Chatbot..."
                            class="flex-1 bg-gray-100 border-transparent focus:bg-white focus:ring-2 focus:ring-blue-500 rounded-full px-4 py-2.5 text-sm"
                            :disabled="isTyping">
                        <button type="submit"
                            class="w-10 h-10 bg-blue-600 hover:bg-blue-700 text-white rounded-full flex items-center justify-center transition-colors flex-shrink-0"
                            :disabled="isTyping || !chatInput.trim()">
                            <i class="fa-solid fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- MODAL: INTENT -->
        <div x-show="showIntentModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
            style="display: none;">
            <div @click.away="showIntentModal = false" class="bg-white rounded-xl shadow-lg w-full max-w-lg mx-4">
                <div class="p-5 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-lg font-bold" x-text="editIntent ? 'Sửa Ý định' : 'Thêm Ý định mới'"></h3>
                    <button @click="showIntentModal = false" class="text-gray-400 hover:text-gray-600"><i
                            class="fa-solid fa-xmark text-xl"></i></button>
                </div>
                <form
                    :action="editIntent ? `/admin/chatbot/intents/${editIntent.id}` :
                        '{{ route('admin.chatbot.intents.store') }}'"
                    method="POST" class="p-5">
                    @csrf
                    <template x-if="editIntent"><input type="hidden" name="_method" value="PUT"></template>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mã Ý định (Intent Name) <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="intent_name" :value="editIntent?.intent_name || ''" required
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500"
                                placeholder="VD: hoi_gia_kham">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mô tả <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="description" :value="editIntent?.description || ''" required
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Hỏi chi phí dịch vụ...">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Các mẫu câu hỏi (Ngăn cách bởi
                                dấu phẩy)</label>
                            <textarea name="example_phrases" rows="3"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Khám nhi bao nhiêu tiền?, Bảng giá khám..."><template x-if="editIntent" x-text="editIntent.example_phrases"></template></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Hành động AI <span
                                    class="text-red-500">*</span></label>
                            <select name="action"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="faq_lookup" :selected="editIntent?.action === 'faq_lookup'">Tra cứu tự
                                    động (FAQ)</option>
                                <option value="guide_booking" :selected="editIntent?.action === 'guide_booking'">
                                    Hướng dẫn đặt lịch</option>
                                <option value="introduce_specialty"
                                    :selected="editIntent?.action === 'introduce_specialty'">Giới thiệu chuyên khoa
                                </option>
                                <option value="transfer_staff" :selected="editIntent?.action === 'transfer_staff'">
                                    Chuyển NV Hỗ trợ thật</option>
                            </select>
                        </div>
                        <div class="flex items-center mt-2">
                            <input type="checkbox" name="is_active" id="intent_active" value="1"
                                :checked="!editIntent || editIntent.is_active"
                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                            <label for="intent_active" class="ml-2 text-sm font-medium text-gray-900">Kích hoạt
                                ngay</label>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="showIntentModal = false"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Hủy</button>
                        <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">Lưu
                            Ý định</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL: RESPONSE -->
        <div x-show="showResponseModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
            style="display: none;">
            <div @click.away="showResponseModal = false" class="bg-white rounded-xl shadow-lg w-full max-w-lg mx-4">
                <div class="p-5 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-lg font-bold" x-text="editResponse ? 'Sửa Phản hồi' : 'Thêm Phản hồi mới'"></h3>
                    <button @click="showResponseModal = false" class="text-gray-400 hover:text-gray-600"><i
                            class="fa-solid fa-xmark text-xl"></i></button>
                </div>
                <form
                    :action="editResponse ? `/admin/chatbot/responses/${editResponse.id}` :
                        '{{ route('admin.chatbot.responses.store') }}'"
                    method="POST" class="p-5">
                    @csrf
                    <template x-if="editResponse"><input type="hidden" name="_method" value="PUT"></template>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Thuộc Ý định <span
                                    class="text-red-500">*</span></label>
                            <select name="intent_id" required
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                                @foreach ($intents as $intent)
                                    <option value="{{ $intent->id }}"
                                        :selected="editResponse?.intent_id == {{ $intent->id }}">
                                        {{ $intent->intent_name }} - {{ $intent->description }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nội dung trả lời <span
                                    class="text-red-500">*</span></label>
                            <textarea name="content" required rows="5"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Dạ, phòng khám Carebook có chi phí khám lâm sàng là..."><template x-if="editResponse" x-text="editResponse.content"></template></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Độ ưu tiên</label>
                            <input type="number" name="priority" :value="editResponse?.priority || 1"
                                min="1" max="10"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div class="flex items-center mt-2">
                            <input type="checkbox" name="is_active" id="response_active" value="1"
                                :checked="!editResponse || editResponse.is_active"
                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                            <label for="response_active" class="ml-2 text-sm font-medium text-gray-900">Kích
                                hoạt</label>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="showResponseModal = false"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Hủy</button>
                        <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">Lưu
                            Phản hồi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.admin>
