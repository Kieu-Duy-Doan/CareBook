<x-layouts.doctor title="Thông báo">
    <div class="space-y-6">

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Thông báo</h1>
                <p class="text-sm text-gray-500 mt-1">Danh sách thông báo của bạn</p>
            </div>

            @if ($notifications->count() > 0)
            <form action="{{ route('doctor.notifications.destroy-read') }}" method="POST"
                onsubmit="return confirm('Bạn có chắc chắn muốn dọn dẹp tất cả thông báo đã đọc?');">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="text-sm px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition-colors flex items-center gap-2">
                    <i class="fa-solid fa-broom"></i> Xoá thông báo đã đọc
                </button>
            </form>
            @endif
        </div>

        @if ($notifications->isEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-16 text-center">
            <div class="w-16 h-16 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-bell-slash text-2xl"></i>
            </div>
            <p class="text-gray-900 font-bold mb-1">Chưa có thông báo nào</p>
            <p class="text-gray-500 text-sm">Bạn sẽ nhận được thông báo khi có lịch hẹn mới hoặc thay đổi.</p>
        </div>
        @else
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden divide-y divide-gray-100">
            @foreach ($notifications as $notif)
            <div class="p-4 md:p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 transition-colors hover:bg-gray-50 {{ !$notif->is_read ? 'bg-blue-50/30' : '' }}">
                <div class="flex items-start gap-4 flex-1">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 {{ in_array($notif->type, ['cancellation', 'system_cancellation']) ? 'bg-red-100 text-red-600' : ($notif->type === 'patient_cancellation' ? 'bg-gray-100 text-gray-600' : 'bg-blue-100 text-blue-600') }}">
                        @if (in_array($notif->type, ['cancellation', 'system_cancellation']))
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        @elseif ($notif->type === 'patient_cancellation')
                        <i class="fa-solid fa-ban"></i>
                        @elseif (in_array($notif->type, ['appointment', 'patient_booking', 'system_booking']))
                        <i class="fa-solid fa-calendar-check"></i>
                        @else
                        <i class="fa-solid fa-bell"></i>
                        @endif
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="font-bold text-base {{ in_array($notif->type, ['cancellation', 'system_cancellation']) ? 'text-red-600' : 'text-gray-800' }}">
                                {{ $notif->title }}
                            </h3>
                            @if (!$notif->is_read)
                            <span class="px-2 py-0.5 bg-blue-500 text-white text-[10px] font-bold rounded-full uppercase">Mới</span>
                            @endif
                        </div>
                        <p class="text-gray-600 text-sm mb-2 leading-relaxed line-clamp-2">{{ $notif->content }}</p>
                        <div class="flex items-center gap-3 text-xs text-gray-400 font-medium">
                            <span title="{{ $notif->created_at->format('d/m/Y H:i') }}">
                                <i class="fa-regular fa-clock"></i> {{ $notif->created_at->diffForHumans() }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-1 sm:ml-4 self-end sm:self-auto shrink-0">
                    @if ($notif->ref_type === 'appointment' && $notif->ref_id)
                    <a href="{{ route('doctor.notifications.show', $notif->id) }}"
                        class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors border border-transparent hover:border-blue-100"
                        title="Xem chi tiết">
                        <span>Xem chi tiết</span> <i class="fa-solid fa-chevron-right text-[10px]"></i>
                    </a>
                    @endif
                    <form action="{{ route('doctor.notifications.destroy', $notif->id) }}" method="POST"
                        onsubmit="return confirm('Bạn có chắc chắn muốn xoá thông báo này?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                            title="Xoá">
                            <i class="fa-regular fa-trash-can text-lg"></i>
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>

        @if ($notifications->hasPages())
        <div class="mt-4">
            {{ $notifications->links() }}
        </div>
        @endif
        @endif
    </div>
</x-layouts.doctor>