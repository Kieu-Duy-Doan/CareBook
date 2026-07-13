<x-layouts.patient>
    <div class="max-w-5xl mx-auto px-4 py-6">
        <x-stepper step="3" />

        <div class="flex items-center gap-3 mb-6">
            <i class="fa-solid fa-calendar-days text-3xl text-primary"></i>
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Chọn ngày và giờ khám</h2>
                <p class="text-base text-gray-500 mt-1">
                    Hồ sơ: <strong>{{ \App\Models\PatientProfile::find($booking['patient_profile_id'])->full_name ?? '' }}</strong>
                </p>
                @if($booking['booking_method'] === 'doctor' || $booking['booking_method'] === 'suggested')
                    <p class="text-base text-gray-500">
                        Bác sĩ: <strong>{{ \App\Models\DoctorProfile::find($booking['doctor_id'])->user->full_name ?? '' }}</strong>
                    </p>
                @elseif($booking['booking_method'] === 'specialty')
                    <p class="text-base text-gray-500">
                        Chuyên khoa: <strong>{{ \App\Models\Specialty::find($booking['specialty_id'])->name ?? '' }}</strong>
                    </p>
                @endif
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

        @if(session('success'))
            <div class="p-4 mb-6 text-sm text-green-800 rounded-lg bg-green-50" role="alert">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('patient.booking.postStep3') }}" method="POST" id="step3-form">
            @csrf
            <input type="hidden" name="draft_id" value="{{ $draftId ?? '' }}">
            
            {{-- Chọn ngày --}}
            <div class="mb-8">
                <div class="flex items-center gap-2 mb-4">
                    <i class="fa-regular fa-calendar-check text-primary text-xl"></i>
                    <h3 class="text-lg font-bold text-slate-800">Chọn ngày khám:</h3>
                </div>

                {{-- Lưới ngày tĩnh --}}
                <div class="grid grid-cols-4 md:grid-cols-7 gap-3">
                    @foreach($availableDates as $dateObj)
                        <a href="{{ route('patient.booking.step3', ['date' => $dateObj['date'], 'draft_id' => $draftId ?? '']) }}" data-loader="true"
                            class="flex flex-col items-center justify-center py-3 rounded-2xl border-2 transition-all duration-200 hover:-translate-y-1 {{ $selectedDate === $dateObj['date'] ? 'border-primary bg-primary text-white shadow-md shadow-primary/30' : 'border-slate-100 bg-white text-slate-700 hover:border-primary/50 hover:bg-primary/5' }}">
                            <span class="text-[11px] font-bold uppercase tracking-wider mb-1 {{ $selectedDate === $dateObj['date'] ? 'text-primary-100' : 'text-slate-400' }}">{{ $dateObj['day_name'] }}</span>
                            <span class="text-xl font-extrabold">{{ $dateObj['display'] }}</span>
                        </a>
                    @endforeach
                </div>

                @if(empty($availableDates))
                    <div class="w-full text-center py-10 bg-red-50 border border-red-100 rounded-2xl mt-2">
                        <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm border border-red-100">
                            <i class="fa-solid fa-calendar-xmark text-2xl text-red-400 block"></i>
                        </div>
                        <p class="text-red-700 font-bold text-lg mb-1">Không tìm thấy lịch khám</p>
                        <p class="text-sm text-red-500">Hiện không có bác sĩ nào thuộc học vị/chuyên khoa này rảnh lịch. Vui lòng quay lại bước trước để chọn lại.</p>
                    </div>
                @endif
            </div>

            {{-- Giờ khám (hiện sau khi chọn ngày) --}}
            @if($selectedDate)
                <input type="hidden" name="date" value="{{ $selectedDate }}">
                <div class="bg-white border-2 border-slate-100 rounded-2xl p-5 md:p-6 mb-8 shadow-sm">
                    {{-- Header phòng --}}
                    <div class="rounded-xl p-3 mb-5 bg-primary/5 border border-primary/10">
                        <div class="flex items-center gap-2 font-bold text-primary">
                            <i class="fa-solid fa-location-dot"></i>
                            @if($booking['booking_method'] === 'doctor' || $booking['booking_method'] === 'suggested')
                                <span>Phòng khám của bác sĩ</span>
                            @else
                                <span>{{ \App\Models\Specialty::find($booking['specialty_id'])->name ?? 'Phòng khám' }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- Chọn giờ khám (Inline) --}}
                    <div class="mt-2">
                        @php
                            $selectedTime = old('time', $booking['time'] ?? '');
                        @endphp
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2 text-slate-800 font-bold text-lg">
                                <i class="fa-regular fa-clock text-primary"></i>
                                <span>Danh sách Giờ khám</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div id="selected-time-display" class="hidden flex items-center gap-1.5 text-sm font-bold text-white bg-green-500 px-3 py-1 rounded-full shadow-sm transition-all">
                                    <i class="fa-regular fa-circle-check"></i> Đã chọn: <span></span>
                                </div>
                                <div class="text-sm font-bold text-primary bg-primary/10 px-3 py-1 rounded-full">
                                    Ngày {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}
                                </div>
                            </div>
                        </div>

                        {{-- Slots --}}
                        <div>
                            @php
                                $morningSlots = array_filter($slots, fn($s) => (int)explode(':', $s['time'])[0] < 12);
                                $afternoonSlots = array_filter($slots, fn($s) => (int)explode(':', $s['time'])[0] >= 12);
                            @endphp

                            @if(count($morningSlots) > 0)
                                {{-- Buổi sáng --}}
                                <div class="mb-6">
                                    <div class="flex items-center gap-2 text-orange-500 font-bold mb-3 text-sm uppercase tracking-wide">
                                        <i class="fa-solid fa-sun"></i>
                                        <span>Buổi sáng</span>
                                    </div>
                                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3">
                                        @foreach($morningSlots as $slot)
                                            <label class="relative block cursor-pointer">
                                                <input type="radio" name="time" value="{{ $slot['time'] }}" data-doctor-id="{{ $slot['doctor_id'] ?? '' }}" class="peer sr-only" {{ $selectedTime == $slot['time'] ? 'checked' : '' }} @disabled(!$slot['available'])>
                                                <div class="flex justify-center items-center gap-1.5 px-2 py-3 rounded-xl border-2 font-bold transition-all duration-200 
                                                    peer-checked:border-primary/50 peer-checked:bg-slate-100 peer-checked:text-primary peer-checked:opacity-60 peer-checked:shadow-inner
                                                    {{ !$slot['available'] ? 'border-slate-100 text-slate-300 cursor-not-allowed bg-slate-50 opacity-60' : 'border-slate-200 text-slate-700 hover:border-primary/50 hover:bg-primary/5 hover:text-primary' }}">
                                                    <i class="fa-regular fa-clock text-sm"></i>
                                                    <span class="text-sm md:text-base">{{ $slot['time'] }}</span>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if(count($afternoonSlots) > 0)
                                {{-- Buổi chiều --}}
                                <div>
                                    <div class="flex items-center gap-2 text-blue-500 font-bold mb-3 text-sm uppercase tracking-wide">
                                        <i class="fa-solid fa-cloud-sun"></i>
                                        <span>Buổi chiều</span>
                                    </div>
                                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3">
                                        @foreach($afternoonSlots as $slot)
                                            <label class="relative block cursor-pointer">
                                                <input type="radio" name="time" value="{{ $slot['time'] }}" data-doctor-id="{{ $slot['doctor_id'] ?? '' }}" class="peer sr-only" {{ $selectedTime == $slot['time'] ? 'checked' : '' }} @disabled(!$slot['available'])>
                                                <div class="flex justify-center items-center gap-1.5 px-2 py-3 rounded-xl border-2 font-bold transition-all duration-200 
                                                    peer-checked:border-primary/50 peer-checked:bg-slate-100 peer-checked:text-primary peer-checked:opacity-60 peer-checked:shadow-inner
                                                    {{ !$slot['available'] ? 'border-slate-100 text-slate-300 cursor-not-allowed bg-slate-50 opacity-60' : 'border-slate-200 text-slate-700 hover:border-primary/50 hover:bg-primary/5 hover:text-primary' }}">
                                                    <i class="fa-regular fa-clock text-sm"></i>
                                                    <span class="text-sm md:text-base">{{ $slot['time'] }}</span>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if(empty($slots))
                                {{-- Không có slot --}}
                                <div class="text-center py-12 text-slate-400 bg-slate-50 rounded-2xl border border-slate-100">
                                    <div class="w-16 h-16 rounded-full bg-white flex items-center justify-center mx-auto mb-3 shadow-sm border border-slate-100">
                                        <i class="fa-solid fa-calendar-xmark text-2xl text-slate-300 block"></i>
                                    </div>
                                    <p class="font-bold text-slate-600">Không có lịch khám vào ngày này</p>
                                    <p class="text-sm mt-1">Vui lòng chọn ngày khác ở phần trên</p>
                                </div>
                            @endif
                            <input type="hidden" name="doctor_id" id="selected_doctor_id" value="{{ old('doctor_id', $booking['doctor_id'] ?? '') }}">
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const timeInputs = document.querySelectorAll('input[name="time"]');
                                    const docInput = document.getElementById('selected_doctor_id');
                                    const timeDisplay = document.getElementById('selected-time-display');
                                    const timeDisplaySpan = timeDisplay ? timeDisplay.querySelector('span') : null;
                                    
                                    function updateTimeDisplay() {
                                        const checked = document.querySelector('input[name="time"]:checked');
                                        if (checked) {
                                            if (docInput && checked.dataset.doctorId) {
                                                docInput.value = checked.dataset.doctorId;
                                            }
                                            if (timeDisplay && timeDisplaySpan) {
                                                timeDisplaySpan.textContent = checked.value;
                                                timeDisplay.classList.remove('hidden');
                                            }
                                        } else {
                                            if (timeDisplay) {
                                                timeDisplay.classList.add('hidden');
                                            }
                                        }
                                    }

                                    // Initialize
                                    updateTimeDisplay();

                                    // Run on change
                                    timeInputs.forEach(input => {
                                        input.addEventListener('change', updateTimeDisplay);
                                    });
                                });
                            </script>
                        </div>
                    </div>
                </div>
            @else
                {{-- Empty state --}}
                @if(count($availableDates) > 0)
                    <div class="bg-slate-50 rounded-2xl p-10 text-center text-slate-400 mb-8 border-2 border-slate-100 border-dashed">
                        <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm">
                            <i class="fa-solid fa-calendar-day text-4xl text-slate-300"></i>
                        </div>
                        <p class="text-lg font-bold text-slate-600 mb-1">Chưa chọn ngày khám</p>
                        <p class="text-sm">Vui lòng chọn một ngày trong danh sách phía trên để xem các khung giờ trống.</p>
                    </div>
                @endif
            @endif

            {{-- Navigation --}}
            <div class="flex gap-4 sticky bottom-0 bg-white/90 backdrop-blur-md pt-4 pb-4 md:pb-6 border-t border-slate-100 z-20">
                <a href="{{ route('patient.booking.step2', ['draft_id' => $draftId ?? '']) }}" data-loader="true"
                    class="w-1/3 md:w-1/4 py-3 md:py-4 bg-slate-100 text-slate-600 rounded-xl text-center font-bold hover:bg-slate-200 transition-colors active:scale-95 text-sm md:text-base">
                    <i class="fa-solid fa-arrow-left mr-1.5"></i> Quay lại
                </a>
                <button type="submit" @if(!$selectedDate || empty($slots)) disabled @endif
                    class="flex-1 py-3 md:py-4 rounded-xl font-extrabold text-white transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-lg shadow-primary/30 bg-primary hover:bg-primary-dark active:scale-95 text-sm md:text-base">
                    Tiếp tục <i class="fa-solid fa-arrow-right ml-1.5"></i>
                </button>
            </div>
        </form>
</x-layouts.patient>