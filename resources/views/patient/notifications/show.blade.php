<x-layouts.patient-dashboard title="Chi tiết thông báo" active-menu="notifications">
    <div>
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 pb-6 border-b border-slate-100 gap-4">
            <h2 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                <div class="w-10 h-10 rounded-full bg-primary/10 text-primary flex items-center justify-center">
                    <i class="fa-solid fa-bell text-lg"></i>
                </div>
                Chi tiết thông báo
            </h2>
            <a href="{{ route('patient.notifications.page') }}" class="inline-flex items-center gap-2 text-sm font-bold text-slate-500 hover:text-primary bg-slate-50 hover:bg-primary/5 px-4 py-2.5 rounded-xl transition-colors border border-slate-100 w-fit">
                <i class="fa-solid fa-arrow-left"></i> Trở về Hộp thư
            </a>
        </div>

        <div class="bg-white rounded-3xl p-6 md:p-10 shadow-[0_2px_20px_-5px_rgba(0,0,0,0.05)] border border-slate-100">
            <div class="flex items-start gap-5 mb-8 pb-8 border-b border-slate-100">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center shrink-0 shadow-sm {{ in_array($notification->type, ['cancellation', 'system_cancellation']) ? 'bg-red-50 text-red-500 border border-red-100' : ($notification->type === 'patient_cancellation' ? 'bg-slate-50 text-slate-500 border border-slate-200' : 'bg-blue-50 text-blue-500 border border-blue-100') }}">
                    @if(in_array($notification->type, ['cancellation', 'system_cancellation']))
                        <i class="fa-solid fa-triangle-exclamation text-2xl"></i>
                    @elseif($notification->type === 'patient_cancellation')
                        <i class="fa-solid fa-calendar-xmark text-2xl"></i>
                    @else
                        <i class="fa-solid fa-envelope-open-text text-2xl"></i>
                    @endif
                </div>
                <div class="flex-1">
                    <h3 class="text-xl md:text-2xl font-extrabold mb-2 leading-tight {{ in_array($notification->type, ['cancellation', 'system_cancellation']) ? 'text-red-600' : ($notification->type === 'patient_cancellation' ? 'text-slate-600' : 'text-slate-800') }}">
                        {{ $notification->title }}
                    </h3>
                    <div class="flex items-center gap-3 text-sm font-medium">
                        <span class="text-slate-500 bg-slate-50 px-3 py-1 rounded-full border border-slate-100">
                            <i class="fa-regular fa-clock mr-1"></i> {{ $notification->created_at->format('H:i - d/m/Y') }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="prose max-w-none text-slate-700 mb-10 text-base md:text-lg leading-relaxed bg-slate-50/50 p-6 rounded-2xl border border-slate-100">
                {!! nl2br(e($notification->content)) !!}
            </div>

            @if($appointment)
                <div class="bg-white rounded-2xl p-6 border-2 {{ in_array($notification->type, ['cancellation', 'system_cancellation']) ? 'border-red-100' : ($notification->type === 'patient_cancellation' ? 'border-slate-200' : 'border-primary/20') }} mb-10 shadow-sm relative overflow-hidden">
                    {{-- Decor --}}
                    <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br {{ in_array($notification->type, ['cancellation', 'system_cancellation']) ? 'from-red-50 to-red-100/50' : ($notification->type === 'patient_cancellation' ? 'from-slate-50 to-slate-100/50' : 'from-primary/5 to-primary/10') }} rounded-bl-[100px] -z-10"></div>
                    
                    <h3 class="font-extrabold text-slate-800 mb-5 text-lg flex items-center gap-2">
                        <i class="fa-solid fa-file-medical text-slate-400"></i> Thông tin lịch hẹn gốc:
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 text-sm md:text-base">
                        <div class="flex flex-col border-b border-slate-100 pb-3 md:border-0 md:pb-0">
                            <span class="text-slate-400 font-bold uppercase tracking-wider text-xs mb-1">Mã lịch hẹn</span> 
                            <strong class="text-slate-800 font-mono bg-slate-100 px-2 py-1 rounded w-fit">{{ $appointment->appointment_code }}</strong>
                        </div>
                        <div class="flex flex-col border-b border-slate-100 pb-3 md:border-0 md:pb-0">
                            <span class="text-slate-400 font-bold uppercase tracking-wider text-xs mb-1">Bác sĩ</span> 
                            <strong class="text-slate-800">{{ $appointment->doctorProfile->full_title ?? 'Chưa xác định' }}</strong>
                        </div>
                        <div class="flex flex-col border-b border-slate-100 pb-3 md:border-0 md:pb-0">
                            <span class="text-slate-400 font-bold uppercase tracking-wider text-xs mb-1">Thời gian</span> 
                            <strong class="text-slate-800 text-lg text-primary">{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') }} <span class="text-slate-500 text-base font-medium">- {{ $appointment->appointment_date->format('d/m/Y') }}</span></strong>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-slate-400 font-bold uppercase tracking-wider text-xs mb-1">Trạng thái</span> 
                            @if($appointment->status === 'cancelled')
                                <span class="inline-flex items-center gap-1.5 bg-red-50 text-red-600 font-bold px-3 py-1.5 rounded-lg w-fit border border-red-100">
                                    <i class="fa-solid fa-ban text-sm"></i> Đã huỷ
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 bg-blue-50 text-blue-600 font-bold px-3 py-1.5 rounded-lg w-fit border border-blue-100">
                                    <i class="fa-solid fa-check-circle text-sm"></i> Đã xác nhận
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            @if(in_array($notification->type, ['cancellation', 'system_cancellation']))
                <div class="border-t-2 border-dashed border-slate-200 pt-8 mt-8">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 rounded-full bg-orange-100 text-orange-500 flex items-center justify-center">
                            <i class="fa-solid fa-lightbulb text-lg"></i>
                        </div>
                        <h3 class="text-xl font-extrabold text-slate-800">Gợi ý lịch khám thay thế</h3>
                    </div>
                    
                    @if(!empty($notification->data['alternatives']) && count($notification->data['alternatives']) > 0)
                        <p class="text-slate-600 mb-6 font-medium text-base">Để không làm gián đoạn việc thăm khám, chúng tôi xin gợi ý một số Bác sĩ chuyên khoa <span class="font-bold text-primary bg-primary/10 px-2 py-0.5 rounded">{{ $appointment->specialty->name ?? '' }}</span> có lịch trống gần nhất:</p>
                        
                        <div class="bg-gradient-to-br from-blue-50 to-indigo-50/30 border border-blue-100 rounded-3xl p-6 md:p-8 mb-8">
                            <div class="flex items-center gap-4 mb-6">
                                <div class="w-14 h-14 rounded-2xl bg-white shadow-sm flex items-center justify-center text-blue-500 border border-blue-100">
                                    <i class="fa-solid fa-user-doctor text-2xl"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-800 text-lg md:text-xl">Đã tìm thấy <span class="text-blue-600">{{ collect($notification->data['alternatives'])->pluck('id')->unique()->count() }}</span> Bác sĩ phù hợp</h4>
                                    <p class="text-sm text-slate-500 font-medium mt-1">Dựa trên lịch hẹn bị huỷ của bạn</p>
                                </div>
                            </div>
                            
                            {{-- Danh sách bác sĩ gợi ý --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                                @foreach($notification->data['alternatives'] as $doc)
                                    <div class="bg-white/80 backdrop-blur-md p-5 rounded-2xl border border-white shadow-sm flex flex-col gap-4 hover:shadow-md hover:border-blue-200 transition-all">
                                        <div class="flex items-center gap-4">
                                            @if(!empty($doc['avatar_url']))
                                                <img src="{{ Storage::url($doc['avatar_url']) }}" alt="Avatar" class="w-14 h-14 rounded-full object-cover flex-shrink-0 border-2 border-white shadow-sm">
                                            @else
                                                <div class="w-14 h-14 rounded-full bg-gradient-to-br from-blue-100 to-indigo-100 flex items-center justify-center flex-shrink-0 text-blue-600 border-2 border-white shadow-sm">
                                                    <span class="font-black text-lg">{{ mb_strtoupper(mb_substr(collect(explode(' ', $doc['full_title']))->last(), 0, 2)) }}</span>
                                                </div>
                                            @endif
                                            <div class="flex-1 min-w-0">
                                                <h5 class="font-bold text-slate-800 text-base md:text-lg truncate" title="{{ $doc['full_title'] ?? 'Bác sĩ' }}">{{ $doc['full_title'] ?? 'Bác sĩ' }}</h5>
                                                @if(!empty($doc['experience_years']))
                                                    <p class="text-sm text-primary font-bold mt-0.5"><i class="fa-solid fa-star text-yellow-400 text-xs"></i> {{ $doc['experience_years'] }} năm kinh nghiệm</p>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-sm text-slate-600 bg-slate-50 p-3 rounded-xl border border-slate-100 space-y-2">
                                            @if(!empty($doc['expertise']))
                                                <p class="line-clamp-1 flex items-start gap-2"><i class="fa-solid fa-stethoscope text-slate-400 mt-1"></i> <span>{{ $doc['expertise'] }}</span></p>
                                            @endif
                                            <p class="flex items-center gap-2"><i class="fa-regular fa-calendar-check text-green-500"></i> Lịch trống gần nhất: <strong class="text-slate-800 bg-green-100 text-green-700 px-2 py-0.5 rounded">{{ \Carbon\Carbon::parse($doc['alternative_date'])->format('d/m/Y') }}</strong></p>
                                        </div>
                                        <div class="mt-2">
                                            <a href="{{ route('patient.booking.fastTrack', ['notification' => $notification->id, 'doctor_id' => $doc['id']]) }}" 
                                               class="w-full px-4 py-2.5 bg-primary hover:bg-primary-dark text-white font-bold rounded-xl transition-all shadow-md shadow-primary/20 active:scale-95 flex items-center justify-center gap-2 text-sm uppercase tracking-wider">
                                                <i class="fa-solid fa-bolt"></i> Đặt lịch BS này
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="flex justify-center mt-6">
                                <a href="{{ route('patient.booking.step1', ['specialty_id' => $appointment->specialty_id]) }}" 
                                   class="px-8 py-3 bg-white hover:bg-slate-50 text-slate-600 font-bold rounded-xl transition-all active:scale-95 text-center text-base border border-slate-200">
                                    Tìm Bác sĩ khác chuyên khoa {{ $appointment->specialty->name ?? '' }}
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="bg-orange-50 border border-orange-100 rounded-2xl p-8 text-center max-w-2xl mx-auto">
                            <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm border border-orange-100">
                                <i class="fa-solid fa-calendar-xmark text-3xl text-orange-400 block"></i>
                            </div>
                            <h4 class="font-bold text-orange-700 text-lg mb-2">Không có lịch trống</h4>
                            <p class="text-orange-600/80 mb-6 font-medium">Hiện tại chúng tôi không tìm thấy Bác sĩ cùng chuyên khoa có lịch rảnh trong 3 ngày tới. Rất mong bạn thông cảm.</p>
                            <a href="{{ route('patient.booking.step1', ['specialty_id' => $appointment->specialty_id]) }}" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-white border border-orange-200 text-orange-600 font-bold rounded-xl hover:bg-orange-100 transition-colors shadow-sm active:scale-95">
                                <i class="fa-solid fa-search"></i> Tự tìm lịch khám
                            </a>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-layouts.patient-dashboard>
