<x-layouts.patient>
    @php
    $method = request('method', old('booking_method', $booking['booking_method'] ?? 'specialty'));
    @endphp
    <div class="max-w-5xl mx-auto px-4 py-6">
        <x-stepper step="2" />

        <div class="flex items-center gap-3 mb-8">
            <i class="fa-solid fa-stethoscope text-3xl text-primary"></i>
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Chọn phương thức đặt lịch</h2>
                <p class="text-base text-gray-500 mt-1">
                    Vui lòng chọn cách bạn muốn đặt lịch khám
                </p>
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

        <form action="{{ route('patient.booking.postStep2') }}" method="POST" id="step2-form">
            @csrf
            <input type="hidden" name="draft_id" value="{{ $draftId ?? '' }}">

            {{-- Các lựa chọn --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">

                @if((isset($booking['suggested_doctors']) && count($booking['suggested_doctors']) > 0) || !empty($booking['is_fast_track']))
                {{-- Theo bác sĩ gợi ý --}}
                <a href="{{ route('patient.booking.step2', ['method' => 'suggested', 'draft_id' => $draftId ?? '']) }}" data-loader="true"
                    class="group relative flex flex-col p-5 bg-white border-2 rounded-2xl cursor-pointer transition-all duration-300 hover:shadow-lg hover:shadow-primary/5 hover:-translate-y-1 {{ $method === 'suggested' ? 'border-primary ring-4 ring-primary/10 bg-primary/5' : 'border-slate-100' }}">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center flex-shrink-0 transition-colors mb-4 {{ $method === 'suggested' ? 'bg-primary text-white shadow-md' : 'bg-blue-50 text-blue-500 group-hover:bg-primary/10 group-hover:text-primary' }}">
                        <i class="fa-solid fa-star text-2xl"></i>
                    </div>
                    <div>
                        <p class="font-bold text-slate-800 text-xl transition-colors group-hover:text-primary mb-1">Bác sĩ gợi ý</p>
                        <p class="text-sm text-slate-500 leading-relaxed">Tiếp tục với bác sĩ gợi ý từ lịch hẹn đã bị huỷ trước đó.</p>
                    </div>
                </a>
                @endif

                {{-- Theo chuyên khoa --}}
                <a href="{{ route('patient.booking.step2', ['method' => 'specialty', 'draft_id' => $draftId ?? '']) }}" data-loader="true"
                    class="group relative flex flex-col p-5 bg-white border-2 rounded-2xl cursor-pointer transition-all duration-300 hover:shadow-lg hover:shadow-primary/5 hover:-translate-y-1 {{ $method === 'specialty' ? 'border-primary ring-4 ring-primary/10 bg-primary/5' : 'border-slate-100' }}">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center flex-shrink-0 transition-colors mb-4 {{ $method === 'specialty' ? 'bg-primary text-white shadow-md' : 'bg-blue-50 text-blue-500 group-hover:bg-primary/10 group-hover:text-primary' }}">
                        <i class="fa-solid fa-briefcase-medical text-2xl"></i>
                    </div>
                    <div>
                        <p class="font-bold text-slate-800 text-xl transition-colors group-hover:text-primary mb-1">Đặt lịch cơ bản</p>
                        <p class="text-sm text-slate-500 leading-relaxed">Chọn chuyên khoa và học vị, hệ thống sẽ tự động sắp xếp bác sĩ phù hợp.</p>
                    </div>
                </a>

                {{-- Theo bác sĩ --}}
                <a href="{{ route('patient.booking.step2', ['method' => 'doctor', 'draft_id' => $draftId ?? '']) }}" data-loader="true"
                    class="group relative flex flex-col p-5 bg-white border-2 rounded-2xl cursor-pointer transition-all duration-300 hover:shadow-lg hover:shadow-primary/5 hover:-translate-y-1 {{ $method === 'doctor' ? 'border-primary ring-4 ring-primary/10 bg-primary/5' : 'border-slate-100' }}">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center flex-shrink-0 transition-colors mb-4 {{ $method === 'doctor' ? 'bg-primary text-white shadow-md' : 'bg-blue-50 text-blue-500 group-hover:bg-primary/10 group-hover:text-primary' }}">
                        <i class="fa-solid fa-user-doctor text-2xl"></i>
                    </div>
                    <div>
                        <p class="font-bold text-slate-800 text-xl transition-colors group-hover:text-primary mb-1">Chỉ định bác sĩ</p>
                        <p class="text-sm text-slate-500 leading-relaxed">Chủ động lựa chọn bác sĩ quen hoặc bác sĩ bạn tin tưởng.</p>
                    </div>
                </a>
            </div>

            <input type="hidden" name="booking_method" value="{{ $method }}">

            {{-- Chọn chuyên khoa --}}
            @if($method === 'specialty' || $method === 'doctor')
            <div class="mb-8">
                <h3 class="text-lg font-bold text-slate-800 mb-4">1. Chọn Chuyên khoa:</h3>
                <div class="bg-white border-2 border-slate-100 rounded-2xl overflow-hidden shadow-sm">
                    <div class="overflow-y-auto max-h-[60vh] md:max-h-[400px]">
                        @php
                        $selectedSpecialty = old('specialty_id', $booking['specialty_id'] ?? '');
                        @endphp
                        @foreach($specialties as $s)
                        <label class="flex items-center gap-4 px-5 py-4 border-b hover:bg-primary/5 cursor-pointer transition-colors has-[:checked]:bg-primary/5 has-[:checked]:border-primary/20 border-slate-50">
                            <input type="radio" name="specialty_id" value="{{ $s->id }}" class="sr-only" {{ $selectedSpecialty == $s->id ? 'checked' : '' }}>
                            <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 transition-colors border-slate-300 custom-radio-circle">
                                <div class="w-2.5 h-2.5 rounded-full bg-primary opacity-0 transition-opacity custom-radio-dot"></div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-lg text-slate-800 custom-radio-text">{{ $s->name }}</p>
                                <p class="text-sm text-slate-500 mt-1 line-clamp-2">{{ $s->description }}</p>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Chọn Học vị --}}
                @if($method === 'specialty')
                <div class="mt-8">
                    <h3 class="text-lg font-bold text-slate-800 mb-4">2. Chọn Học vị bác sĩ mong muốn:</h3>
                    <div class="flex flex-wrap gap-4">
                        @php
                        $selectedLevel = old('level', $booking['level'] ?? '');
                        @endphp
                        @foreach($fees as $fee)
                        <label class="fee-label relative block cursor-pointer w-[calc(50%-0.5rem)] md:w-auto md:min-w-[160px]" data-level="{{ $fee->level }}">
                            <input type="radio" name="level" value="{{ $fee->level }}" class="peer sr-only" {{ $selectedLevel == $fee->level ? 'checked' : '' }}>
                            <div class="flex flex-col items-center justify-center p-4 bg-white border-2 rounded-2xl transition-all hover:border-primary/50 hover:shadow-md hover:-translate-y-0.5 peer-checked:border-primary peer-checked:ring-4 peer-checked:ring-primary/10 peer-checked:bg-primary/5 border-slate-100 h-full w-full">
                                <span class="font-bold text-slate-800 text-lg mb-1">{{ $fee->level }}</span>
                                <span class="text-sm text-primary font-bold bg-white px-3 py-1 rounded-full border border-primary/20 shadow-sm">{{ number_format($fee->base_price, 0, ',', '.') }} đ</span>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            @endif

            {{-- Chọn bác sĩ --}}
            @if($method === 'doctor')
            <div class="mb-8">
                <h3 class="text-lg font-bold text-slate-800 mb-4">2. Chọn Bác sĩ:</h3>
                <div class="bg-white border-2 border-slate-100 rounded-2xl overflow-hidden shadow-sm">
                    <div class="overflow-y-auto max-h-[60vh] md:max-h-[400px]">
                        @php
                        $selectedDoctor = old('doctor_id', $booking['doctor_id'] ?? '');
                        @endphp
                        @foreach($doctors as $doc)
                        @php
                        $docSpecialties = $doc->specialties->pluck('id')->join(',');
                        @endphp
                        <label class="doctor-label flex items-center gap-4 px-5 py-4 border-b hover:bg-primary/5 cursor-pointer transition-colors has-[:checked]:bg-primary/5 has-[:checked]:border-primary/20 border-slate-50" data-specialties="{{ $docSpecialties }}">
                            <input type="radio" name="doctor_id" value="{{ $doc->id }}" class="sr-only" {{ $selectedDoctor == $doc->id ? 'checked' : '' }}>
                            <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 transition-colors border-slate-300 custom-radio-circle">
                                <div class="w-2.5 h-2.5 rounded-full bg-primary opacity-0 transition-opacity custom-radio-dot"></div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-lg text-slate-800 custom-radio-text">{{ $doc->full_title }}</p>
                                <div class="flex items-center gap-3 mt-1">
                                    <p class="text-sm text-slate-500 line-clamp-1">Mã BS: <strong class="text-slate-700">{{ $doc->doctor_code }}</strong></p>
                                    @php
                                        $feeForDoc = $fees->firstWhere('level', $doc->level);
                                        $price = $feeForDoc ? $feeForDoc->specific_price : 0;
                                    @endphp
                                    <span class="text-xs text-primary font-bold bg-primary/10 px-2.5 py-1 rounded-md border border-primary/20">
                                        {{ number_format($price, 0, ',', '.') }} đ
                                    </span>
                                </div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- Thông tin Bác sĩ gợi ý --}}
            @if($method === 'suggested')
            <div class="mb-8">
                <h3 class="text-lg font-bold text-slate-800 mb-4">2. Bác sĩ gợi ý đã chọn:</h3>
                <div class="bg-white border-2 border-primary/20 bg-primary/5 rounded-2xl p-5 shadow-sm">
                    @php
                        $suggestedDoctor = \App\Models\DoctorProfile::with('user', 'specialties')->find($booking['doctor_id'] ?? null);
                    @endphp
                    @if($suggestedDoctor)
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-full bg-primary/10 flex items-center justify-center text-primary text-xl">
                                <i class="fa-solid fa-user-doctor"></i>
                            </div>
                            <div>
                                <p class="font-bold text-lg text-slate-800">{{ $suggestedDoctor->full_title }}</p>
                                <p class="text-sm text-slate-500">Chuyên khoa: {{ $suggestedDoctor->specialties->pluck('name')->join(', ') }}</p>
                            </div>
                        </div>
                        <input type="hidden" name="doctor_id" value="{{ $suggestedDoctor->id }}">
                    @else
                        <div class="text-center py-4">
                            <p class="text-red-500 font-bold mb-2">Không tìm thấy thông tin bác sĩ gợi ý.</p>
                            <p class="text-sm text-slate-500">Vui lòng quay lại bước 1 hoặc chọn phương thức đặt lịch khác.</p>
                        </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Navigation --}}
            <div class="flex gap-4 sticky bottom-0 bg-white/90 backdrop-blur-md pt-4 pb-4 md:pb-6 border-t border-slate-100 z-20">
                <a href="{{ route('patient.booking.step1', ['draft_id' => $draftId ?? '']) }}" data-loader="true"
                    class="w-1/3 md:w-1/4 py-3 md:py-4 bg-slate-100 text-slate-600 rounded-xl text-center font-bold hover:bg-slate-200 transition-colors active:scale-95 text-sm md:text-base">
                    <i class="fa-solid fa-arrow-left mr-1.5"></i> Quay lại
                </a>
                <button type="submit" class="flex-1 py-3 md:py-4 rounded-xl font-extrabold text-white transition-all shadow-lg shadow-primary/30 bg-primary hover:bg-primary-dark active:scale-95 text-sm md:text-base">
                    Tiếp tục <i class="fa-solid fa-arrow-right ml-1.5"></i>
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function syncRadioUI() {
                ['specialty_id', 'doctor_id'].forEach(name => {
                    document.querySelectorAll('input[name="' + name + '"]').forEach(radio => {
                        const label = radio.closest('label');
                        const circle = label.querySelector('.custom-radio-circle');
                        const dot = label.querySelector('.custom-radio-dot');
                        const text = label.querySelector('.custom-radio-text');

                        if (circle && dot && text) {
                            if (radio.checked) {
                                circle.classList.add('border-primary');
                                circle.classList.remove('border-slate-300');
                                dot.classList.remove('opacity-0');
                                text.classList.add('text-primary');
                                text.classList.remove('text-slate-800');
                            } else {
                                circle.classList.remove('border-primary');
                                circle.classList.add('border-slate-300');
                                dot.classList.add('opacity-0');
                                text.classList.remove('text-primary');
                                text.classList.add('text-slate-800');
                            }
                        }
                    });
                });
            }

            document.querySelectorAll('input[name="specialty_id"], input[name="doctor_id"]').forEach(radio => {
                radio.addEventListener('change', syncRadioUI);
            });
            syncRadioUI(); // Initial run on page load

            // Dynamic filtering based on specialty
            const specialtyLevels = @json($specialtyLevels ?? []);

            function filterOptions() {
                const selectedSpecialtyRadio = document.querySelector('input[name="specialty_id"]:checked');

                // 1. Filter Levels (for specialty method)
                const feeLabels = document.querySelectorAll('.fee-label');
                if (feeLabels.length > 0) {
                    if (!selectedSpecialtyRadio) {
                        feeLabels.forEach(label => {
                            label.style.display = 'none';
                            label.querySelector('input').checked = false;
                        });
                    } else {
                        const availableLevels = specialtyLevels[selectedSpecialtyRadio.value] || [];
                        feeLabels.forEach(label => {
                            const level = label.getAttribute('data-level');
                            const input = label.querySelector('input');
                            if (availableLevels.includes(level)) {
                                label.style.display = 'block';
                            } else {
                                label.style.display = 'none';
                                input.checked = false;
                            }
                        });
                    }
                }

                // 2. Filter Doctors (for doctor method)
                const doctorLabels = document.querySelectorAll('.doctor-label');
                if (doctorLabels.length > 0) {
                    if (!selectedSpecialtyRadio) {
                        doctorLabels.forEach(label => {
                            label.style.display = 'none';
                            label.querySelector('input').checked = false;
                        });
                    } else {
                        const selectedSpecialtyId = selectedSpecialtyRadio.value;
                        doctorLabels.forEach(label => {
                            const doctorSpecialties = label.getAttribute('data-specialties').split(',');
                            const input = label.querySelector('input');
                            if (doctorSpecialties.includes(selectedSpecialtyId)) {
                                label.style.display = 'flex';
                            } else {
                                label.style.display = 'none';
                                input.checked = false;
                            }
                        });
                    }
                }
            }

            document.querySelectorAll('input[name="specialty_id"]').forEach(radio => {
                radio.addEventListener('change', filterOptions);
            });
            filterOptions(); // Initial run
        });
    </script>
</x-layouts.patient>