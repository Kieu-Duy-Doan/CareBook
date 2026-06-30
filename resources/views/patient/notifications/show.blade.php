<x-layouts.patient-dashboard title="Chi tiết thông báo" active-menu="notifications">
    <div>
        <div class="flex items-center justify-between mb-6 pb-6 border-b border-slate-100">
            <h2 class="text-xl font-bold text-slate-800"><i class="fa-solid fa-bell text-secondary mr-2"></i> Chi tiết thông báo</h2>
            <a href="{{ route('patient.notifications.page') }}" class="text-sm font-semibold text-slate-500 hover:text-secondary transition-colors"><i class="fa-solid fa-arrow-left"></i> Trở về Hộp thư</a>
        </div>

        <div>
            <div class="flex items-start gap-4 mb-6">
                <div class="w-12 h-12 rounded-full flex items-center justify-center shrink-0 {{ $notification->type === 'cancellation' ? 'bg-red-100 text-red-600' : 'bg-blue-100 text-blue-600' }}">
                    @if($notification->type === 'cancellation')
                        <i class="fa-solid fa-triangle-exclamation text-xl"></i>
                    @else
                        <i class="fa-solid fa-bell text-xl"></i>
                    @endif
                </div>
                <div>
                    <h3 class="text-xl font-bold {{ $notification->type === 'cancellation' ? 'text-red-600' : 'text-slate-800' }}">
                        {{ $notification->title }}
                    </h3>
                    <p class="text-sm text-slate-500 mt-1"><i class="fa-regular fa-clock"></i> {{ $notification->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>

            <div class="prose max-w-none text-slate-700 mb-8">
                <p class="text-base leading-relaxed">{{ $notification->content }}</p>
            </div>

            @if($appointment)
                <div class="bg-slate-50 rounded-xl p-5 border-l-4 {{ $notification->type === 'cancellation' ? 'border-red-500' : 'border-blue-500' }} mb-8">
                    <h3 class="font-bold text-slate-800 mb-4">Thông tin lịch hẹn:</h3>
                    <ul class="space-y-2 text-sm">
                        <li><span class="text-slate-500 inline-block w-32">Mã lịch hẹn:</span> <strong class="text-slate-800">{{ $appointment->appointment_code }}</strong></li>
                        <li><span class="text-slate-500 inline-block w-32">Bác sĩ:</span> <strong class="text-slate-800">{{ $appointment->doctorProfile->full_title ?? 'Chưa xác định' }}</strong></li>
                        <li><span class="text-slate-500 inline-block w-32">Thời gian:</span> <strong class="text-slate-800">{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') }} ngày {{ $appointment->appointment_date->format('d/m/Y') }}</strong></li>
                        <li><span class="text-slate-500 inline-block w-32">Trạng thái:</span> 
                            @if($appointment->status === 'cancelled')
                                <span class="text-red-600 font-bold">Đã huỷ</span>
                            @else
                                <span class="text-blue-600 font-bold">Đã xác nhận</span>
                            @endif
                        </li>
                    </ul>
                </div>
            @endif

            @if($notification->type === 'cancellation')
                <div class="border-t border-slate-200 pt-8">
                    <h3 class="text-lg font-bold text-slate-800 mb-4">Gợi ý lịch khám thay thế</h3>
                    
                    @if(!empty($notification->data['alternatives']) && count($notification->data['alternatives']) > 0)
                        <p class="text-slate-600 mb-6">Để không làm gián đoạn việc thăm khám của bạn, chúng tôi xin gợi ý một số Bác sĩ chuyên khoa <strong class="text-slate-800">{{ $appointment->specialty->name ?? '' }}</strong> có lịch trống vào các ngày sắp tới.</p>
                        
                        <div class="bg-blue-50 border border-blue-100 rounded-xl p-5 mb-8 text-center">
                            <i class="fa-solid fa-user-doctor text-blue-500 text-3xl mb-3"></i>
                            <h4 class="font-bold text-slate-800 text-lg mb-2">Đã tìm thấy {{ collect($notification->data['alternatives'])->pluck('id')->unique()->count() }} Bác sĩ phù hợp</h4>
                            <p class="text-sm text-slate-600 mb-5">Dưới đây là các bác sĩ có lịch trống trong những ngày tới. Vui lòng xem xét và chọn "Đặt lịch với Bác sĩ gợi ý" nếu bạn đồng ý.</p>
                            
                            {{-- Danh sách bác sĩ gợi ý --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 mb-6 text-left">
                                @foreach($notification->data['alternatives'] as $doc)
                                    <div class="bg-white p-4 rounded-xl border border-blue-200 shadow-sm flex flex-col gap-3">
                                        <div class="flex items-center gap-3">
                                            @if(!empty($doc['avatar_url']))
                                                <img src="{{ Storage::url($doc['avatar_url']) }}" alt="Avatar" class="w-12 h-12 rounded-full object-cover flex-shrink-0 border border-blue-200">
                                            @else
                                                <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0 text-primary border border-blue-200">
                                                    <span class="font-bold text-sm">{{ mb_strtoupper(mb_substr(collect(explode(' ', $doc['full_title']))->last(), 0, 2)) }}</span>
                                                </div>
                                            @endif
                                            <div class="flex-1 min-w-0">
                                                <h5 class="font-bold text-slate-800 text-sm truncate">{{ $doc['full_title'] ?? 'Bác sĩ' }}</h5>
                                                @if(!empty($doc['experience_years']))
                                                    <p class="text-xs text-primary mt-0.5 font-medium">{{ $doc['experience_years'] }} năm kinh nghiệm</p>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-xs text-slate-600 bg-slate-50 p-2 rounded-lg border border-slate-100">
                                            @if(!empty($doc['expertise']))
                                                <p class="mb-1 line-clamp-1"><i class="fa-solid fa-stethoscope w-4 text-slate-400"></i> {{ $doc['expertise'] }}</p>
                                            @endif
                                            <p><i class="fa-regular fa-calendar w-4 text-slate-400"></i> Lịch trống gần nhất: <strong class="text-slate-700">{{ \Carbon\Carbon::parse($doc['alternative_date'])->format('d/m/Y') }}</strong></p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <a href="{{ url('/dat-lich?notification_id='.$notification->id.'&booking_method=suggested&cancelled_doctor_id='.$appointment->doctor_profile_id) }}" class="inline-block px-8 py-3 bg-primary hover:bg-primary-dark text-white font-bold rounded-lg transition-colors shadow-[0_8px_20px_-8px_rgba(37,99,235,0.5)] hover:shadow-[0_12px_25px_-8px_rgba(37,99,235,0.6)] active:scale-95">
                                Đặt lịch với Bác sĩ gợi ý
                            </a>
                        </div>
                        
                        <div class="text-center">
                            <p class="text-slate-600 text-sm mb-3">Hoặc bạn có thể tự xem toàn bộ danh sách để chọn lịch phù hợp nhất:</p>
                            <a href="{{ url('/dat-lich?specialty_id=' . $appointment->specialty_id) }}" class="inline-block px-6 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition-colors">
                                Xem Tất Cả Bác Sĩ
                            </a>
                        </div>
                    @else
                        <div class="bg-orange-50 border border-orange-100 rounded-xl p-5 text-center">
                            <p class="text-orange-700">Hiện tại chúng tôi không tìm thấy Bác sĩ cùng chuyên khoa có lịch rảnh trong 3 ngày tới. Rất mong bạn thông cảm và vui lòng chọn một ngày khác.</p>
                            <a href="{{ url('/dat-lich?specialty_id=' . $appointment->specialty_id) }}" class="inline-block mt-4 px-6 py-2 bg-white border border-orange-200 text-orange-600 font-bold rounded-lg hover:bg-orange-100 transition-colors">
                                Đặt lịch ngày khác
                            </a>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-layouts.patient-dashboard>
