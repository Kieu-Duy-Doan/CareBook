<x-layouts.admin title="Lịch sử Phiên Chat">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Lịch sử Phiên Chat</h2>
            <p class="text-gray-500 mt-1">Giám sát các cuộc hội thoại giữa Chatbot và người dùng.</p>
        </div>
    </div>
    <!-- Bộ lọc -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6">
        <form action="{{ route('admin.chatbot.sessions.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Trạng thái</label>
                <select name="status"
                    class="rounded-md border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Tất cả</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Đang diễn ra</option>
                    <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Đã kết thúc</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Tin nhắn cần lưu ý</label>
                <label class="flex items-center text-sm text-gray-700 mt-2">
                    <input type="checkbox" name="is_flagged" value="1" {{ request('is_flagged') ? 'checked' : '' }}
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-2">
                    Chỉ hiện phiên có Flag
                </label>
            </div>
            <div>
                <button type="submit"
                    class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    Lọc
                </button>
                @if (request()->anyFilled(['status', 'is_flagged']))
                    <a href="{{ route('admin.chatbot.sessions.index') }}"
                        class="ml-2 text-sm text-blue-600 hover:underline">Xoá lọc</a>
                @endif
            </div>
        </form>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-gray-50/80 text-gray-700 font-medium border-b border-gray-100 uppercase text-xs">
                <tr>
                    <th class="px-6 py-4">Mã Phiên (Session ID)</th>
                    <th class="px-6 py-4">Người dùng</th>
                    <th class="px-6 py-4">Trạng thái</th>
                    <th class="px-6 py-4 text-center">Số tin nhắn</th>
                    <th class="px-6 py-4">Thời gian tạo</th>
                    <th class="px-6 py-4 text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($sessions as $session)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4 font-mono text-xs text-gray-500">
                            #{{ $session->id }}<br>
                            <span
                                class="text-gray-400 text-[10px]">{{ substr($session->session_token, 0, 8) }}...</span>
                        </td>
                        <td class="px-6 py-4">
                            @if ($session->user)
                                <div class="font-medium text-blue-600 flex items-center">
                                    <i class="fa-solid fa-user-check text-blue-400 mr-2"></i>
                                    {{ $session->user->full_name }}
                                </div>
                            @else
                                <div class="font-medium text-gray-500 flex items-center">
                                    <i class="fa-solid fa-user-secret text-gray-400 mr-2"></i> Khách vãng lai
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if ($session->status == 'active')
                                <span
                                    class="bg-green-50 text-green-700 px-2.5 py-1 rounded-md text-xs font-medium border border-green-100">Đang
                                    chat</span>
                            @else
                                <span
                                    class="bg-gray-100 text-gray-700 px-2.5 py-1 rounded-md text-xs font-medium border border-gray-200">Đã
                                    kết thúc</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center font-medium">
                            {{ $session->messages_count }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-gray-900">{{ $session->created_at->format('d/m/Y') }}</div>
                            <div class="text-xs text-gray-500">{{ $session->created_at->format('H:i') }}</div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('admin.chatbot.sessions.show', $session->id) }}"
                                class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-blue-600 bg-blue-50 hover:bg-blue-100 transition-colors text-xs font-medium"
                                title="Xem chi tiết">
                                Xem chi tiết <i class="fa-solid fa-arrow-right ml-1"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                            Chưa có phiên chat nào được ghi nhận.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $sessions->links() }}
    </div>
</x-layouts.admin>
