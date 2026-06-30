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
                        <p class="text-slate-600 mb-6">Để không làm gián đoạn việc thăm khám của bạn, chúng tôi xin gợi ý một số Bác sĩ chuyên khoa <strong class="text-slate-800">{{ $appointment->specialty->name ?? '' }}</strong> có lịch trống vào các ngày sắp tới:</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                            @foreach($notification->data['alternatives'] as $doc)
                                <div class="bg-white border border-slate-200 rounded-xl p-5 hover:border-blue-300 hover:shadow-md transition-all group">
                                    <div class="flex items-start gap-4">
                                        <div class="w-12 h-12 bg-blue-50 rounded-full flex items-center justify-center shrink-0">
                                            <i class="fa-solid fa-user-doctor text-blue-600 text-lg"></i>
                                        </div>
                                        <div class="flex-1">
                                            <h4 class="font-bold text-slate-800 text-lg mb-1">{{ $doc['full_title'] }}</h4>
                                            <p class="text-sm text-slate-500 mb-4"><i class="fa-regular fa-calendar text-slate-400"></i> Có lịch vào ngày: <strong class="text-slate-700">{{ \Carbon\Carbon::parse($doc['alternative_date'])->format('d/m/Y') }}</strong></p>
                                            
                                            <a href="{{ url('/dat-lich?fast_track=1&patient_profile_id='.$appointment->patient_profile_id.'&specialty_id='.$appointment->specialty_id.'&doctor_id='.$doc['id'].'&cancelled_doctor_id='.$appointment->doctor_profile_id.'&booking_method='.($appointment->booking_method ?? 'doctor').'&reason='.urlencode($appointment->reason ?? '')) }}" class="block w-full py-2.5 px-4 bg-primary hover:bg-secondary text-white font-bold text-center rounded-lg transition-colors text-sm">
                                                Đặt lịch với Bác sĩ này
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="text-center">
                            <p class="text-slate-600 text-sm mb-3">Hoặc bạn có thể tự xem toàn bộ danh sách để chọn lịch phù hợp nhất:</p>
                            <a href="{{ url('/dat-lich?specialty_id=' . $appointment->specialty_id) }}" class="inline-block px-6 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition-colors">
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
