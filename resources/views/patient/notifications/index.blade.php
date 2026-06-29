<x-layouts.patient title="Hộp thư thông báo">
    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-slate-800"><i class="fa-solid fa-bell text-secondary mr-2"></i> Hộp thư thông báo</h1>
            <a href="{{ route('patient.dashboard') }}" class="text-sm font-semibold text-slate-500 hover:text-secondary transition-colors"><i class="fa-solid fa-arrow-left"></i> Trở về trang cá nhân</a>
        </div>

        @if(session('success'))
            <div class="bg-green-50 text-green-700 p-4 rounded-xl mb-6 shadow-sm flex items-start gap-3">
                <i class="fa-solid fa-circle-check mt-0.5"></i>
                <p class="font-medium">{{ session('success') }}</p>
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            @if($notifications->isEmpty())
                <div class="p-12 text-center text-slate-500 flex flex-col items-center">
                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                        <i class="fa-solid fa-envelope-open text-2xl text-slate-300"></i>
                    </div>
                    <p class="font-medium">Bạn chưa có thông báo nào.</p>
                </div>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach($notifications as $notif)
                        <div class="p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 transition-colors hover:bg-slate-50 {{ !$notif->is_read ? 'bg-blue-50/30' : '' }}">
                            <div class="flex items-start gap-4 flex-1">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 {{ $notif->type === 'cancellation' ? 'bg-red-100 text-red-600' : 'bg-blue-100 text-blue-600' }}">
                                    @if($notif->type === 'cancellation')
                                        <i class="fa-solid fa-triangle-exclamation"></i>
                                    @else
                                        <i class="fa-solid fa-bell"></i>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <h3 class="font-bold text-base {{ $notif->type === 'cancellation' ? 'text-red-600' : 'text-slate-800' }}">
                                            {{ $notif->title }}
                                        </h3>
                                        @if(!$notif->is_read)
                                            <span class="px-2 py-0.5 bg-blue-500 text-white text-[10px] font-bold rounded-full uppercase">Mới</span>
                                        @endif
                                    </div>
                                    <p class="text-slate-600 text-sm mb-2 leading-relaxed">{{ $notif->content }}</p>
                                    <div class="flex items-center gap-3 text-xs text-slate-400 font-medium">
                                        <span title="{{ $notif->created_at->format('d/m/Y H:i') }}"><i class="fa-regular fa-clock"></i> {{ $notif->created_at->diffForHumans() }}</span>
                                        @if($notif->ref_type === 'appointment' && $notif->ref_id)
                                            <span>&bull;</span>
                                            @if($notif->type === 'cancellation')
                                                @php $apt = \App\Models\Appointment::find($notif->ref_id); @endphp
                                                @if($apt)
                                                    <a href="{{ url('/dat-lich?fast_track=1&patient_profile_id='.$apt->patient_profile_id.'&specialty_id='.$apt->specialty_id.'&doctor_id='.$apt->doctor_profile_id.'&booking_method='.($apt->booking_method ?? 'doctor').'&reason='.urlencode($apt->reason ?? '')) }}" class="text-secondary hover:underline font-bold"><i class="fa-regular fa-calendar-check"></i> Chọn lịch thay thế</a>
                                                @endif
                                            @else
                                                <a href="{{ route('patient.appointments.show', $notif->ref_id) }}" class="text-secondary hover:underline font-bold"><i class="fa-solid fa-link"></i> Xem chi tiết lịch</a>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 sm:ml-4 self-end sm:self-auto shrink-0">
                                <form action="{{ route('patient.notifications.destroy', $notif->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xoá thông báo này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Xoá thông báo">
                                        <i class="fa-regular fa-trash-can"></i> <span class="text-sm font-semibold sm:hidden ml-1">Xoá</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="p-4 bg-slate-50 border-t border-slate-100">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.patient>
