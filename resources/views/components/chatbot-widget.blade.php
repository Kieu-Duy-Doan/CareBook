<div x-data="chatbotWidget()" class="fixed bottom-6 right-6 z-50 font-sans" x-init="initWidget()">

    <!-- Chat Button -->
    <button @click="toggleChat" x-show="!isOpen" x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="opacity-0 translate-y-4 scale-90"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200 transform"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 scale-90"
        class="bg-blue-600 hover:bg-blue-700 text-white p-4 rounded-full shadow-lg shadow-blue-500/30 flex items-center justify-center focus:outline-none focus:ring-4 focus:ring-blue-300 transition-all group relative">
        <i class="fa-solid fa-robot text-2xl group-hover:scale-110 transition-transform"></i>
        <!-- Notification dot -->
        <span x-show="unreadCount > 0"
            class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold w-5 h-5 flex items-center justify-center rounded-full border-2 border-white"
            x-text="unreadCount"></span>
    </button>

    <!-- Chat Window -->
    <div x-show="isOpen" style="display: none;" x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="opacity-0 translate-y-8 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200 transform"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-8 scale-95"
        class="bg-white w-[350px] max-w-[calc(100vw-32px)] sm:w-[380px] h-[500px] max-h-[calc(100vh-100px)] flex flex-col rounded-2xl shadow-2xl border border-gray-100 overflow-hidden absolute bottom-0 right-0 origin-bottom-right">

        <!-- Header -->
        <div
            class="bg-gradient-to-r from-blue-600 to-indigo-600 p-4 text-white flex items-center justify-between shadow-sm relative z-10">
            <div class="flex items-center gap-3">
                <div class="relative">
                    <div
                        class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm border border-white/30">
                        <i class="fa-solid fa-robot text-xl"></i>
                    </div>
                    <span
                        class="absolute bottom-0 right-0 w-3 h-3 bg-green-400 border-2 border-indigo-600 rounded-full"></span>
                </div>
                <div>
                    <h3 class="font-bold leading-tight">Carebook AI Bot</h3>
                    <p class="text-xs text-blue-100 font-medium">Trợ lý y tế thông minh</p>
                </div>
            </div>
            <div class="flex items-center gap-1">
                <button @click="clearHistory"
                    class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-white/20 transition-colors text-white/80 hover:text-white"
                    title="Xóa lịch sử">
                    <i class="fa-solid fa-rotate-right text-sm"></i>
                </button>
                <button @click="toggleChat"
                    class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-white/20 transition-colors text-white/80 hover:text-white">
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
            </div>
        </div>

        <!-- Chat Area -->
        <div x-ref="chatMessages" class="flex-1 p-4 overflow-y-auto bg-gray-50/50 space-y-4">

            <!-- Greeting Message (Always there) -->
            <div class="flex gap-3">
                <div
                    class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 bg-gradient-to-br from-indigo-500 to-purple-600 text-white shadow-sm mt-1">
                    <i class="fa-solid fa-robot text-xs"></i>
                </div>
                <div
                    class="bg-white border border-gray-100 shadow-sm rounded-2xl rounded-tl-sm px-4 py-3 text-[14px] text-gray-700 max-w-[85%]">
                    Xin chào 👋 Tôi là trợ lý ảo của Carebook. Tôi có thể giúp gì cho bạn hôm nay?
                </div>
            </div>

            <!-- Dynamic Messages -->
            <template x-for="(msg, index) in messages" :key="index">
                <div class="flex gap-3" :class="msg.role === 'user' ? 'flex-row-reverse' : ''">
                    <!-- Avatar -->
                    <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 shadow-sm mt-1"
                        :class="msg.role === 'user' ? 'bg-blue-100 text-blue-600' :
                            'bg-gradient-to-br from-indigo-500 to-purple-600 text-white'">
                        <i class="fa-solid"
                            :class="msg.role === 'user' ? 'fa-user text-xs' : 'fa-robot text-xs'"></i>
                    </div>
                    <!-- Bubble -->
                    <div class="max-w-[85%] rounded-2xl px-4 py-3 shadow-sm flex flex-col"
                        :class="msg.role === 'user' ? 'bg-blue-600 text-white rounded-tr-sm' :
                            'bg-white border border-gray-100 text-gray-700 rounded-tl-sm'">
                        <span class="text-[14px] whitespace-pre-wrap" x-html="msg.text"></span>

                        <!-- Metadata Cards -->
                        <template x-if="(msg.role === 'assistant' || msg.role === 'bot') && msg.metadata">
                            <div class="mt-2 pt-2 border-t border-gray-100 space-y-2">
                                <template x-for="(val, key) in msg.metadata" :key="key">
                                    <div>
                                        <!-- Nếu là Action thì render Nút bấm điều hướng -->
                                        <template x-if="key === 'action'">
                                            <template x-if="val === 'guide_booking'">
                                                <a href="/booking"
                                                    class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg px-4 py-2 mt-2 transition-colors text-xs">
                                                    <i class="fa-regular fa-calendar-check mr-1"></i> Đặt lịch khám ngay
                                                </a>
                                            </template>
                                            <template x-if="val === 'transfer_staff'">
                                                <button
                                                    class="block w-full text-center bg-orange-100 hover:bg-orange-200 text-orange-700 font-medium rounded-lg px-4 py-2 mt-2 transition-colors text-xs">
                                                    <i class="fa-solid fa-headset mr-1"></i> Gặp nhân viên tư vấn
                                                </button>
                                            </template>
                                            <!-- Bỏ qua faq_lookup action vì không cần nút bấm -->
                                        </template>

                                        <!-- Nếu là dữ liệu thông thường -->
                                        <template x-if="key !== 'action'">
                                            <div
                                                class="bg-indigo-50 border border-indigo-100 rounded p-2 text-xs text-indigo-800 flex items-start gap-2">
                                                <i class="fa-solid fa-info-circle mt-0.5 opacity-70"></i>
                                                <div>
                                                    <span class="font-semibold" x-text="key + ': '"></span>
                                                    <span x-text="val"></span>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            <!-- Typing Indicator -->
            <div x-show="isTyping" class="flex gap-3">
                <div
                    class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 bg-gradient-to-br from-indigo-500 to-purple-600 text-white shadow-sm mt-1">
                    <i class="fa-solid fa-robot text-xs"></i>
                </div>
                <div
                    class="bg-white border border-gray-100 shadow-sm rounded-2xl rounded-tl-sm px-4 py-3 flex items-center gap-1.5 h-11">
                    <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce"></span>
                    <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce"
                        style="animation-delay: 0.1s"></span>
                    <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce"
                        style="animation-delay: 0.2s"></span>
                </div>
            </div>
        </div>

        <!-- Input Area -->
        <div class="p-3 bg-white border-t border-gray-100 relative z-10">
            <form @submit.prevent="sendMessage" class="flex gap-2">
                <input x-model="inputMessage" type="text" placeholder="Nhập câu hỏi..."
                    class="flex-1 bg-gray-100 border-transparent focus:bg-white focus:border-blue-300 focus:ring-2 focus:ring-blue-100 rounded-full px-4 py-2.5 text-[14px] transition-all outline-none"
                    :disabled="isTyping" x-ref="chatInput">
                <button type="submit"
                    class="w-11 h-11 bg-blue-600 hover:bg-blue-700 text-white rounded-full flex items-center justify-center transition-colors flex-shrink-0 shadow-sm"
                    :disabled="isTyping || !inputMessage.trim()"
                    :class="(isTyping || !inputMessage.trim()) ? 'opacity-50 cursor-not-allowed' : ''">
                    <i class="fa-solid fa-paper-plane mr-0.5"></i>
                </button>
            </form>
            <div class="text-center mt-2">
                <span class="text-[10px] text-gray-400 font-medium tracking-wide uppercase">Powered by CareBook
                    NLP</span>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('chatbotWidget', () => ({
            isOpen: false,
            messages: [],
            inputMessage: '',
            isTyping: false,
            sessionToken: null,
            unreadCount: 0,

            initWidget() {
                // Load history from localStorage
                const savedData = localStorage.getItem('carebook_chatbot_session');
                if (savedData) {
                    try {
                        const parsed = JSON.parse(savedData);
                        this.messages = parsed.messages || [];
                        this.sessionToken = parsed.sessionToken || null;

                        // Show dot if there are messages and chat is closed
                        if (this.messages.length > 0 && !this.isOpen) {
                            this.unreadCount = 1;
                        }
                    } catch (e) {
                        console.error('Lỗi đọc lịch sử chat', e);
                    }
                }
            },

            toggleChat() {
                this.isOpen = !this.isOpen;
                if (this.isOpen) {
                    this.unreadCount = 0;
                    this.scrollToBottom();
                    setTimeout(() => this.$refs.chatInput && this.$refs.chatInput.focus(), 300);
                }
            },

            async sendMessage() {
                if (!this.inputMessage.trim() || this.isTyping) return;

                const userText = this.inputMessage.trim();
                this.messages.push({
                    role: 'user',
                    text: userText
                });
                this.inputMessage = '';
                this.isTyping = true;
                this.saveState();
                this.scrollToBottom();

                try {
                    // Ensure route exists or fallback to static URL if route fails
                    const apiUrl = '/api/chatbot/message';

                    const response = await fetch(apiUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector(
                                'meta[name="csrf-token"]')?.getAttribute(
                                'content') || ''
                        },
                        body: JSON.stringify({
                            message: userText,
                            session_token: this.sessionToken
                        })
                    });

                    if (!response.ok) throw new Error('Network response was not ok');

                    const data = await response.json();

                    // Update session token if server provided one
                    if (data.session_token && !this.sessionToken) {
                        this.sessionToken = data.session_token;
                    }

                    this.messages.push({
                        role: 'assistant',
                        text: data.reply,
                        metadata: data.metadata
                    });
                } catch (error) {
                    console.error('Error sending message:', error);
                    this.messages.push({
                        role: 'assistant',
                        text: 'Xin lỗi, tôi đang gặp sự cố kết nối mạng. Vui lòng thử lại sau.'
                    });
                } finally {
                    this.isTyping = false;
                    this.saveState();
                    this.scrollToBottom();
                }
            },

            clearHistory() {
                if (confirm('Bạn có chắc chắn muốn xóa toàn bộ lịch sử trò chuyện này?')) {
                    this.messages = [];
                    this.sessionToken = null;
                    localStorage.removeItem('carebook_chatbot_session');
                    this.isOpen = false;
                }
            },

            saveState() {
                localStorage.setItem('carebook_chatbot_session', JSON.stringify({
                    messages: this.messages,
                    sessionToken: this.sessionToken
                }));
            },

            scrollToBottom() {
                this.$nextTick(() => {
                    if (this.$refs.chatMessages) {
                        this.$refs.chatMessages.scrollTop = this.$refs.chatMessages
                            .scrollHeight;
                    }
                });
            }
        }));
    });
</script>
