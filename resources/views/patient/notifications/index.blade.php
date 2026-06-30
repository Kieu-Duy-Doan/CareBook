<x-layouts.patient-dashboard title="Hộp thư thông báo" active-menu="notifications">
    <div>
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-slate-800"><i class="fa-solid fa-bell text-secondary mr-2"></i> Hộp thư
                thông báo</h2>

            @if ($notifications->count() > 0)
                <form action="{{ route('patient.notifications.destroy-read') }}" method="POST"
                    onsubmit="return confirm('Bạn có chắc chắn muốn dọn dẹp tất cả thông báo đã đọc?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="text-sm px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg transition-colors flex items-center gap-2">
                        Xoá thông báo đã đọc
                    </button>
                </form>
            @endif
        </div>

        @if (session('success'))
            <div class="bg-green-50 text-green-700 p-4 rounded-xl mb-6 shadow-sm flex items-start gap-3">
                <i class="fa-solid fa-circle-check mt-0.5"></i>
                <p class="font-medium">{{ session('success') }}</p>
            </div>
        @endif

        <div>
            @if ($notifications->isEmpty())
                <div class="p-12 text-center text-slate-500 flex flex-col items-center">
                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                        <i class="fa-solid fa-envelope-open text-2xl text-slate-300"></i>
                    </div>
                    <p class="font-medium">Bạn chưa có thông báo nào.</p>
                </div>
            @else
                <div class="divide-y divide-slate-100 border border-slate-100 rounded-xl overflow-hidden">
                    @foreach ($notifications as $notif)
                        <div
                            class="p-4 md:p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 transition-colors hover:bg-slate-50 {{ !$notif->is_read ? 'bg-blue-50/30' : '' }}">
                            <div class="flex items-start gap-4 flex-1">
                                <div
                                    class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 {{ $notif->type === 'cancellation' ? 'bg-red-100 text-red-600' : 'bg-blue-100 text-blue-600' }}">
                                    @if ($notif->type === 'cancellation')
                                        <i class="fa-solid fa-triangle-exclamation"></i>
                                    @else
                                        <i class="fa-solid fa-bell"></i>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <h3
                                            class="font-bold text-base {{ $notif->type === 'cancellation' ? 'text-red-600' : 'text-slate-800' }}">
                                            {{ $notif->title }}
                                        </h3>
                                        @if (!$notif->is_read)
                                            <span
                                                class="px-2 py-0.5 bg-blue-500 text-white text-[10px] font-bold rounded-full uppercase">Mới</span>
                                        @endif
                                    </div>
                                    <p class="text-slate-600 text-sm mb-2 leading-relaxed line-clamp-2">
                                        {{ $notif->content }}</p>
                                    <div class="flex items-center gap-3 text-xs text-slate-400 font-medium">
                                        <span title="{{ $notif->created_at->format('d/m/Y H:i') }}"><i
                                                class="fa-regular fa-clock"></i>
                                            {{ $notif->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-1 sm:ml-4 self-end sm:self-auto shrink-0">
                                @if ($notif->ref_type === 'appointment' && $notif->ref_id)
                                    <a href="{{ route('patient.notifications.show', $notif->id) }}" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-500 hover:text-primary hover:bg-blue-50 rounded-lg transition-colors border border-transparent hover:border-blue-100" title="Xem chi tiết">
                                        <span>Xem chi tiết</span> <i class="fa-solid fa-chevron-right text-[10px]"></i>
                                    </a>
                                @endif
                                <form action="{{ route('patient.notifications.destroy', $notif->id) }}" method="POST"
                                    onsubmit="return confirm('Bạn có chắc chắn muốn xoá thông báo này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors"
                                        title="Xoá">
                                        <i class="fa-regular fa-trash-can text-lg"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="pt-6">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.patient-dashboard>
