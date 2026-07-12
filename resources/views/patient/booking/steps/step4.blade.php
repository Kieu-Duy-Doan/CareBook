<x-layouts.patient>
    <div class="max-w-5xl mx-auto px-4 py-6">
        <div class="flex items-center gap-3 mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-800">Xác nhận thông tin đặt lịch</h2>
                <p class="text-sm text-gray-500 mt-1">Vui lòng kiểm tra thông tin và nhập triệu chứng</p>
            </div>
        </div>

        @if($errors->any())
            <div class="p-4 mb-6 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('patient.booking.store') }}" method="POST" id="step4-form">
            @csrf
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                {{-- Cột bên trái: Triệu chứng & Lưu ý --}}
                <div class="space-y-6">
                    {{-- Nhập triệu chứng --}}
                    <div class="bg-white border-2 border-slate-100 rounded-3xl p-6 md:p-8 shadow-sm transition-all hover:border-primary/20">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                                <i class="fa-solid fa-notes-medical text-lg"></i>
                            </div>
                            <h3 class="font-bold text-lg text-slate-800">Lý do khám / Triệu chứng</h3>
                        </div>
                        <div class="relative group">
                            <textarea name="reason" id="reasonTextarea"
                                rows="4"
                                maxlength="500"
                                placeholder="Mô tả triệu chứng, tình trạng sức khoẻ hiện tại để bác sĩ có thể chuẩn bị tốt nhất..."
                                oninput="updateReasonCount(this)"
                                class="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-5 py-4 resize-none text-slate-700 focus:outline-none focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/10 placeholder-slate-400 text-base transition-all">{{ old('reason', '') }}</textarea>
                            
                            <div class="absolute bottom-3 right-4">
                                <span id="reasonCountSpan" class="text-xs font-bold px-2 py-1 rounded-md bg-slate-200 text-slate-500">
                                    {{ mb_strlen(old('reason', '')) }}/500
                                </span>
                            </div>
                        </div>
                        
                        <script>
                            function updateReasonCount(textarea) {
                                const span = document.getElementById('reasonCountSpan');
                                const len = textarea.value.length;
                                span.innerText = len + '/500';
                                if (len > 450) {
                                    span.className = 'text-xs font-bold px-2 py-1 rounded-md bg-orange-100 text-orange-600';
                                } else {
                                    span.className = 'text-xs font-bold px-2 py-1 rounded-md bg-slate-200 text-slate-500';
                                }
                            }
                            window.addEventListener('DOMContentLoaded', () => {
                                const ta = document.getElementById('reasonTextarea');
                                if (ta) updateReasonCount(ta);
                            });
                        </script>
                        <div class="flex items-start gap-2 mt-4 text-sm text-slate-500 bg-blue-50/50 p-3 rounded-xl border border-blue-100/50">
                            <i class="fa-solid fa-lightbulb text-yellow-500 mt-0.5"></i>
                            <p>Thông tin này được bảo mật và chỉ bác sĩ điều trị mới có thể xem.</p>
                        </div>
                    </div>

                    {{-- Lưu ý quan trọng --}}
                    <div class="bg-gradient-to-br from-amber-50 to-orange-50 border-2 border-orange-100 rounded-3xl p-6 md:p-8 shadow-sm">
                        <div class="flex items-center gap-2 mb-4 text-orange-600">
                            <i class="fa-solid fa-triangle-exclamation text-xl"></i>
                            <h3 class="font-bold text-lg">Lưu ý quan trọng</h3>
                        </div>
                        <ul class="text-slate-700 space-y-3 font-medium">
                            <li class="flex items-start gap-3 bg-white/60 p-3 rounded-xl border border-orange-100/50">
                                <div class="w-6 h-6 rounded-full bg-orange-100 text-orange-500 flex items-center justify-center shrink-0 mt-0.5"><i class="fa-solid fa-clock text-xs"></i></div>
                                <span>Vui lòng có mặt trước <strong class="text-orange-700">15 phút</strong> so với giờ hẹn để làm thủ tục.</span>
                            </li>
                            <li class="flex items-start gap-3 bg-white/60 p-3 rounded-xl border border-orange-100/50">
                                <div class="w-6 h-6 rounded-full bg-orange-100 text-orange-500 flex items-center justify-center shrink-0 mt-0.5"><i class="fa-solid fa-id-card text-xs"></i></div>
                                <span>Mang theo CCCD/CMND và thẻ BHYT (nếu có).</span>
                            </li>
                            <li class="flex items-start gap-3 bg-white/60 p-3 rounded-xl border border-orange-100/50">
                                <div class="w-6 h-6 rounded-full bg-orange-100 text-orange-500 flex items-center justify-center shrink-0 mt-0.5"><i class="fa-solid fa-ban text-xs"></i></div>
                                <span>Có thể huỷ lịch tối đa <strong class="text-orange-700">2 tiếng</strong> trước giờ hẹn.</span>
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- Cột bên phải: Tóm tắt thông tin (Ticket Style) --}}
                <div>
                    <div class="bg-white rounded-[2rem] shadow-xl overflow-hidden h-full flex flex-col relative border border-slate-100">
                        {{-- Header --}}
                        <div class="bg-primary px-8 py-6 text-white text-center relative rounded-t-[2rem]">
                            <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-3 backdrop-blur-sm">
                                <i class="fa-solid fa-ticket text-3xl"></i>
                            </div>
                            <h3 class="font-extrabold uppercase text-xl tracking-wide mb-1">Phiếu Đặt Lịch</h3>
                            <p class="text-blue-100 text-sm font-medium">Vui lòng kiểm tra kỹ thông tin</p>
                        </div>

                        {{-- Ticket dashed line --}}
                        <div class="border-b-2 border-dashed border-slate-200 mx-8 relative z-0"></div>

                        {{-- Content --}}
                        <div class="p-6 md:p-8 flex-1 flex flex-col">
                            <div class="space-y-5">
                                {{-- Bệnh nhân --}}
                                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                                    <div class="flex items-center gap-4 mb-3">
                                        <div class="w-12 h-12 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0">
                                            <i class="fa-solid fa-user-injured text-xl"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-0.5">Bệnh nhân</p>
                                            <p class="font-bold text-slate-800 text-lg">{{ $profile->full_name ?? '' }}</p>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2 text-sm">
                                        <p class="text-slate-600"><span class="text-slate-400 mr-1"><i class="fa-solid fa-phone"></i></span> <span class="font-medium">{{ $profile->phone ?? 'Chưa có SĐT' }}</span></p>
                                        <p class="text-slate-600"><span class="text-slate-400 mr-1">CCCD:</span> <span class="font-medium">{{ $profile->id_card ?? 'Không có' }}</span></p>
                                        <p class="text-slate-600 col-span-2"><span class="text-slate-400 mr-1">BHYT:</span> <span class="font-medium">{{ $profile->insurance_code ?? 'Không có' }}</span></p>
                                    </div>
                                </div>

                                {{-- Phương thức đặt --}}
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 rounded-full bg-indigo-50 text-indigo-500 flex items-center justify-center shrink-0">
                                        <i class="fa-solid fa-clipboard-list text-xl"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-0.5">Phương thức</p>
                                        <p class="font-bold text-slate-800 text-base">
                                            @if($booking['booking_method'] === 'suggested')
                                                Bác sĩ gợi ý
                                            @elseif($booking['booking_method'] === 'doctor')
                                                Chỉ định bác sĩ
                                            @else
                                                Đặt lịch cơ bản
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                {{-- Bác sĩ / Chuyên khoa --}}
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center shrink-0">
                                        <i class="fa-solid fa-user-doctor text-xl"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-0.5">{{ $booking['booking_method'] === 'specialty' ? 'Chuyên khoa' : 'Bác sĩ' }}</p>
                                        @if($booking['booking_method'] === 'specialty')
                                            <p class="font-bold text-slate-800 text-base">{{ \App\Models\Specialty::find($booking['specialty_id'])->name ?? '' }}</p>
                                        @else
                                            <p class="font-bold text-slate-800 text-base uppercase">{{ \App\Models\DoctorProfile::find($booking['doctor_id'])->full_title ?? '' }}</p>
                                        @endif
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    {{-- Ngày khám --}}
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-green-50 text-green-500 flex items-center justify-center shrink-0">
                                            <i class="fa-regular fa-calendar text-lg"></i>
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-0.5">Ngày khám</p>
                                            <p class="font-bold text-slate-800">{{ \Carbon\Carbon::parse($booking['date'])->format('d/m/Y') }}</p>
                                        </div>
                                    </div>
                                    
                                    {{-- Giờ khám --}}
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-orange-50 text-orange-500 flex items-center justify-center shrink-0">
                                            <i class="fa-regular fa-clock text-lg"></i>
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-0.5">Giờ khám</p>
                                            <p class="font-bold text-slate-800 text-lg text-primary">{{ $booking['time'] }}</p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Phòng khám --}}
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-purple-50 text-purple-500 flex items-center justify-center shrink-0">
                                        <i class="fa-solid fa-door-open text-lg"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-0.5">Phòng khám</p>
                                        @php
                                            $roomName = 'Được sắp xếp sau';
                                            $doc = \App\Models\DoctorProfile::find($booking['doctor_id'] ?? 0);
                                            if ($doc && $doc->room_name) {
                                                $roomName = $doc->room_name;
                                            }
                                        @endphp
                                        <p class="font-bold text-slate-800">{{ $roomName }}</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Tổng tiền --}}
                            <div class="mt-auto pt-6 border-t border-slate-100 flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-bold text-slate-400 uppercase tracking-wider">Phí khám dự kiến</p>
                                    <p class="text-xs text-slate-500 mt-1">*Thanh toán tại quầy</p>
                                </div>
                                <span class="font-black text-2xl text-primary">{{ isset($totalFee) && $totalFee > 0 ? number_format($totalFee, 0, ',', '.') . ' đ' : 'Liên hệ' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex gap-4 sticky bottom-0 bg-white/90 backdrop-blur-md pt-4 pb-4 md:pb-6 border-t border-slate-100 z-20 mt-8">
                <a href="{{ route('patient.booking.step3') }}" data-loader="true"
                    class="w-1/3 md:w-1/4 py-3 md:py-4 bg-slate-100 text-slate-600 rounded-xl text-center font-bold hover:bg-slate-200 transition-colors active:scale-95 text-sm md:text-base">
                    <i class="fa-solid fa-arrow-left mr-1.5"></i> Quay lại
                </a>
                <button type="submit"
                    class="flex-1 py-3 md:py-4 rounded-xl font-extrabold text-white uppercase tracking-wider transition-all disabled:opacity-70 disabled:cursor-not-allowed shadow-lg shadow-primary/30 active:scale-95 bg-primary hover:bg-primary-dark text-sm md:text-base flex items-center justify-center gap-2">
                    <i class="fa-solid fa-check"></i>
                    <span>XÁC NHẬN ĐẶT LỊCH</span>
                </button>
            </div>
        </form>
    </div>
</x-layouts.patient>