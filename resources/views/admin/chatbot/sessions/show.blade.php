<x-layouts.admin title="Chi tiết Phiên Chat #{{ $session->id }}">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 md:mb-8">
        <div>
            <div class="flex items-center text-sm text-gray-500 mb-2">
                <a href="{{ route('admin.chatbot.sessions.index') }}" class="hover:text-blue-600 transition-colors">Lịch sử phiên chat</a>
                <span class="mx-2 text-gray-300">/</span>
                <span class="font-bold text-gray-900">Chi tiết</span>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Chi tiết Phiên Chat #{{ $session->id }}</h2>
            <p class="text-gray-500 mt-1">
                @if ($session->user)
                    Khách hàng: <span class="font-medium text-blue-600">{{ $session->user->full_name }}</span>
                @else
                    Khách hàng: <span class="font-medium text-gray-600">Khách vãng lai</span>
                @endif
                &bull; Tạo lúc: {{ $session->created_at->format('d/m/Y H:i') }}
            </p>
        </div>
        <div class="flex items-center gap-3">
            @if ($session->status == 'active')
                <span
                    class="bg-green-50 text-green-700 px-3 py-2 rounded-lg text-sm font-medium border border-green-100 flex items-center">
                    <span class="w-2 h-2 rounded-full bg-green-500 mr-2 animate-pulse"></span> Đang diễn ra
                </span>
            @else
                <span
                    class="bg-gray-100 text-gray-700 px-3 py-2 rounded-lg text-sm font-medium border border-gray-200">
                    Đã kết thúc lúc {{ $session->ended_at ? $session->ended_at->format('H:i') : '' }}
                </span>
            @endif
            <a href="{{ route('admin.chatbot.sessions.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-blue-600 transition-colors shadow-sm">
                <i class="fa-solid fa-arrow-left mr-2"></i> Quay lại
            </a>
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
    </div>

    <!-- Chat Bubbles -->
    <div class="bg-[#Fdfdfc] rounded-xl shadow-sm border border-gray-200 p-6 max-w-4xl mx-auto mb-10">
        <div class="space-y-6">
            @forelse($messages as $msg)
                @if ($msg->role == 'user')
                    <!-- User Message -->
                    <div class="flex justify-end group">
                        <div class="max-w-[80%]">
                            <div class="flex items-end justify-end mb-1 gap-2">
                                <div
                                    class="text-[10px] text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity">
                                    {{ $msg->created_at->format('H:i') }}</div>
                                <div class="bg-blue-600 text-white p-3 rounded-2xl rounded-tr-sm shadow-sm relative">
                                    {!! nl2br(e($msg->content)) !!}

                                    <!-- Flag Button for User Message -->
                                    <button x-data="{ flagged: {{ $msg->is_flagged ? 'true' : 'false' }} }"
                                        @click="
                                            fetch('{{ route('admin.chatbot.sessions.messages.flag', $msg->id) }}', {
                                                method: 'PATCH',
                                                headers: {
                                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                    'Accept': 'application/json'
                                                }
                                            })
                                            .then(res => res.json())
                                            .then(data => { if(data.success) flagged = data.is_flagged; })
                                        "
                                        class="absolute top-1/2 -left-8 -translate-y-1/2 w-6 h-6 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors"
                                        :class="flagged ? 'text-red-500' : 'text-gray-300 hover:text-red-400'"
                                        title="Đánh dấu tin nhắn cần xem xét">
                                        <i class="fa-solid fa-flag text-xs"></i>
                                    </button>
                                </div>
                            </div>
                            @if ($msg->intent_detected)
                                <div class="text-right text-[11px] text-gray-500 font-mono mt-1">
                                    <i class="fa-solid fa-microchip text-blue-400 mr-1"></i> Intent:
                                    {{ $msg->intent_detected }}
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <!-- Bot Message -->
                    <div class="flex justify-start">
                        <div class="max-w-[80%] flex items-end gap-2">
                            <div
                                class="w-8 h-8 rounded-full bg-indigo-100 border border-indigo-200 flex items-center justify-center text-indigo-600 flex-shrink-0 self-end mb-1">
                                <i class="fa-solid fa-robot text-sm"></i>
                            </div>
                            <div>
                                <div
                                    class="bg-white text-gray-800 p-3 rounded-2xl rounded-tl-sm shadow-sm border border-gray-100 text-sm">
                                    {!! nl2br(e($msg->content)) !!}

                                    <!-- Hiển thị Metadata dạng Cards nếu có -->
                                    @if ($msg->metadata)
                                        <div class="mt-3 pt-3 border-t border-gray-100">
                                            <div class="text-[10px] uppercase text-gray-400 font-bold mb-2">Dữ liệu kèm
                                                theo (Metadata)</div>
                                            <pre class="bg-gray-50 p-2 rounded text-[11px] font-mono text-gray-600 overflow-x-auto border border-gray-200">{{ json_encode($msg->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        </div>
                                    @endif
                                </div>
                                <div class="text-[10px] text-gray-400 mt-1 ml-1">{{ $msg->created_at->format('H:i') }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @empty
                <div class="text-center text-gray-500 py-10">
                    Không có tin nhắn nào trong phiên này.
                </div>
            @endforelse
        </div>
    </div>
</x-layouts.admin>
