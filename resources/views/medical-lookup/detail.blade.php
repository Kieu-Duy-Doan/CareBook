<x-layouts.app title="Chi tiết Hồ sơ Bệnh án {{ $appointment->appointment_code }}" metaDescription="Chi tiết kết quả khám bệnh, chẩn đoán, đơn thuốc và cận lâm sàng tại Bệnh viện CareBook.">
    <div class="bg-gradient-to-b from-slate-50 via-white to-slate-50 py-8 md:py-12">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            <!-- Breadcrumb & Actions (Hidden when printing) -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 print:hidden">
                <nav class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
                    <a href="{{ route('home') }}" class="hover:text-primary transition-colors">Trang chủ</a>
                    <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
                    <a href="{{ route('medical-lookup.index') }}" class="hover:text-primary transition-colors">Tra cứu bệnh án</a>
                    <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
                    <span class="text-slate-800 font-semibold truncate max-w-[200px]">{{ $appointment->appointment_code }}</span>
                </nav>

                <div class="flex items-center gap-2 sm:gap-3 w-full sm:w-auto">
                    <a href="{{ route('medical-lookup.index') }}" class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-full border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 text-sm font-semibold transition shadow-sm">
                        <i class="fa-solid fa-arrow-left"></i> Quay lại
                    </a>
                    <button onclick="window.print()" class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-full bg-blue-600 text-white hover:bg-blue-700 text-sm font-semibold transition shadow-sm hover:shadow cursor-pointer">
                        <i class="fa-solid fa-print"></i> In bệnh án
                    </button>
                </div>
            </div>

            <!-- Expiry Warning Banner (Hidden when printing) -->
            <div class="rounded-2xl bg-amber-50 border border-amber-200/80 p-4 text-amber-800 text-sm flex items-start gap-3 shadow-sm print:hidden">
                <i class="fa-solid fa-clock text-amber-600 text-base mt-0.5 shrink-0"></i>
                <div class="leading-relaxed">
                    <strong>Thông báo bảo mật:</strong> Đường dẫn này được mã hóa an toàn và có hiệu lực trong vòng <strong>45 phút</strong> kể từ khi tra cứu. Để lưu lại kết quả lâu dài, quý khách có thể bấm nút <strong>"In bệnh án"</strong> (hoặc chọn <em>Save as PDF</em> trên trình duyệt).
                </div>
            </div>

            <!-- PRINT HEADER (Only visible when printing) -->
            <div class="hidden print:block border-b-2 border-slate-900 pb-4 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-extrabold uppercase text-slate-900 tracking-tight">BỆNH VIỆN CAREBOOK</h2>
                        <p class="text-xs text-slate-600 mt-0.5">Địa chỉ: 123 Đường Sức Khỏe, Quận 1, TP. Hồ Chí Minh · Hotline: 1900.888.866</p>
                    </div>
                    <div class="text-right">
                        <div class="text-base font-extrabold text-slate-900">PHIẾU KHÁM BỆNH & BỆNH ÁN</div>
                        <div class="text-xs text-slate-600">Mã ca khám: {{ $appointment->appointment_code }}</div>
                    </div>
                </div>
            </div>

            <!-- 1. Header ca khám -->
            <div class="bg-white rounded-3xl border border-slate-200 p-5 sm:p-6 md:p-8 shadow-sm">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-6 border-b border-slate-100">
                    <div>
                        <div class="flex flex-wrap items-center gap-2 mb-2">
                            <span class="text-xs font-extrabold uppercase px-3 py-1 rounded-lg bg-blue-50 text-blue-700 tracking-wider">
                                Mã lịch hẹn: {{ $appointment->appointment_code }}
                            </span>
                            <span class="text-xs font-bold px-3 py-1 rounded-lg bg-emerald-50 text-emerald-700">
                                {{ $appointment->status_label }}
                            </span>
                        </div>
                        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">
                            {{ $appointment->specialty->name ?? 'Khám chuyên khoa' }}
                        </h1>
                        <p class="text-sm text-slate-500 mt-1">
                            <i class="fa-regular fa-calendar text-slate-400 mr-1"></i>
                            Ngày khám: <strong class="text-slate-700">{{ $appointment->appointment_date?->format('d/m/Y') }}</strong>
                            @if($appointment->appointment_time)
                                lúc {{ substr($appointment->appointment_time, 0, 5) }}
                            @endif
                            <span class="mx-2 text-slate-300">·</span>
                            Phòng khám: <strong class="text-slate-700">{{ $appointment->room->name ?? 'Phòng khám bệnh' }}</strong>
                        </p>
                    </div>

                    <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100 w-full lg:w-auto lg:min-w-[240px]">
                        <span class="text-xs uppercase text-slate-400 font-bold tracking-wider block">Bác sĩ khám chính</span>
                        <div class="text-base font-bold text-slate-900 mt-1">
                            {{ $appointment->doctorProfile->full_title ?? 'Chưa chỉ định' }}
                        </div>
                        @if($appointment->medicalRecord?->assistant)
                            <div class="text-xs text-slate-500 mt-1">
                                Trợ lý/Điều dưỡng: {{ $appointment->medicalRecord->assistant->full_name }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Thông tin bệnh nhân -->
                <div class="mt-6">
                    <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-400 mb-3">Thông tin bệnh nhân</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 text-sm bg-slate-50/70 p-4 rounded-2xl border border-slate-100">
                        <div>
                            <span class="text-xs text-slate-500 block">Họ và tên</span>
                            <strong class="text-slate-900 font-bold block mt-0.5">{{ $appointment->patientProfile->full_name ?? '—' }}</strong>
                        </div>
                        <div>
                            <span class="text-xs text-slate-500 block">Ngày sinh / Giới tính</span>
                            <span class="text-slate-800 font-semibold block mt-0.5">
                                {{ $appointment->patientProfile->date_of_birth?->format('d/m/Y') ?? '—' }} 
                                ({{ $appointment->patientProfile->gender_label }})
                            </span>
                        </div>
                        <div>
                            <span class="text-xs text-slate-500 block">Số điện thoại</span>
                            @php
                                $rawP = $appointment->patientProfile->phone ?? '';
                                $maskedP = strlen($rawP) >= 7 ? substr($rawP, 0, 3) . '****' . substr($rawP, -3) : $rawP;
                            @endphp
                            <span class="text-slate-800 font-semibold block mt-0.5">{{ $maskedP ?: '—' }}</span>
                        </div>
                        <div>
                            <span class="text-xs text-slate-500 block">Mã bệnh nhân</span>
                            <span class="text-blue-700 font-bold block mt-0.5">{{ $appointment->patientProfile->patient_code ?? 'BN' . $appointment->patientProfile->id }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Chỉ số sinh hiệu (Nếu có đo) -->
            @if($appointment->vital_pulse || $appointment->vital_systolic_bp || $appointment->vital_temperature || $appointment->vital_spo2 || $appointment->vital_weight_kg)
                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
                    <h3 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-heart-pulse text-rose-500"></i> Chỉ số sinh hiệu & Thể trạng
                    </h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3 text-center">
                        @if($appointment->vital_pulse)
                            <div class="p-3 rounded-2xl bg-rose-50/60 border border-rose-100">
                                <span class="text-xs text-slate-500 block">Mạch</span>
                                <span class="text-lg font-bold text-rose-700">{{ $appointment->vital_pulse }}</span>
                                <span class="text-[11px] text-slate-400 block">lần/phút</span>
                            </div>
                        @endif
                        @if($appointment->vital_systolic_bp && $appointment->vital_diastolic_bp)
                            <div class="p-3 rounded-2xl bg-blue-50/60 border border-blue-100">
                                <span class="text-xs text-slate-500 block">Huyết áp</span>
                                <span class="text-lg font-bold text-blue-700">{{ $appointment->vital_systolic_bp }}/{{ $appointment->vital_diastolic_bp }}</span>
                                <span class="text-[11px] text-slate-400 block">mmHg</span>
                            </div>
                        @endif
                        @if($appointment->vital_temperature)
                            <div class="p-3 rounded-2xl bg-amber-50/60 border border-amber-100">
                                <span class="text-xs text-slate-500 block">Nhiệt độ</span>
                                <span class="text-lg font-bold text-amber-700">{{ $appointment->vital_temperature }}</span>
                                <span class="text-[11px] text-slate-400 block">°C</span>
                            </div>
                        @endif
                        @if($appointment->vital_spo2)
                            <div class="p-3 rounded-2xl bg-cyan-50/60 border border-cyan-100">
                                <span class="text-xs text-slate-500 block">SpO2</span>
                                <span class="text-lg font-bold text-cyan-700">{{ $appointment->vital_spo2 }}</span>
                                <span class="text-[11px] text-slate-400 block">%</span>
                            </div>
                        @endif
                        @if($appointment->vital_weight_kg)
                            <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                                <span class="text-xs text-slate-500 block">Cân nặng</span>
                                <span class="text-lg font-bold text-slate-800">{{ $appointment->vital_weight_kg }}</span>
                                <span class="text-[11px] text-slate-400 block">kg</span>
                            </div>
                        @endif
                        @if($appointment->vital_bmi)
                            <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                                <span class="text-xs text-slate-500 block">BMI</span>
                                <span class="text-lg font-bold text-slate-800">{{ $appointment->vital_bmi }}</span>
                                <span class="text-[11px] text-slate-400 block">kg/m²</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- 3. Kết quả khám & Đơn thuốc -->
            <div class="grid grid-cols-1 lg:grid-cols-[1fr_1fr] gap-6">
                <!-- Kết quả khám & Chẩn đoán -->
                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm flex flex-col justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 pb-4 border-b border-slate-100 flex items-center gap-2">
                            <i class="fa-solid fa-clipboard-check text-blue-600"></i> Chẩn đoán & Kết luận
                        </h3>

                        <div class="mt-4 space-y-4 text-sm">
                            <div>
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block">Chẩn đoán (ICD-10)</span>
                                <div class="mt-1 text-base font-bold text-slate-900">
                                    @if($appointment->medicalRecord->icd10_code)
                                        <span class="inline-block px-2 py-0.5 rounded bg-blue-100 text-blue-800 text-xs font-mono mr-1">
                                            {{ $appointment->medicalRecord->icd10_code }}
                                        </span>
                                    @endif
                                    {{ $appointment->medicalRecord->diagnosis ?? 'Chưa ghi nhận' }}
                                </div>
                            </div>

                            <div>
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block">Kết luận của bác sĩ</span>
                                <p class="mt-1 text-slate-800 leading-relaxed bg-slate-50 p-3.5 rounded-2xl border border-slate-100">
                                    {{ $appointment->medicalRecord->conclusion ?? 'Đã hoàn tất quá trình khám bệnh.' }}
                                </p>
                            </div>

                            @if($appointment->medicalRecord->advice)
                                <div>
                                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block">Lời khuyên & Dặn dò</span>
                                    <p class="mt-1 text-slate-700 italic leading-relaxed bg-amber-50/50 p-3.5 rounded-2xl border border-amber-100/60">
                                        "{{ $appointment->medicalRecord->advice }}"
                                    </p>
                                </div>
                            @endif

                            @if($appointment->medicalRecord->followup_date)
                                <div class="p-3.5 rounded-2xl bg-blue-50 border border-blue-100 flex items-center gap-3">
                                    <i class="fa-regular fa-calendar-plus text-blue-600 text-xl"></i>
                                    <div>
                                        <span class="text-xs text-blue-800 font-semibold block">Hẹn tái khám vào ngày:</span>
                                        <strong class="text-base text-blue-900">{{ $appointment->medicalRecord->followup_date->format('d/m/Y') }}</strong>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Tệp đính kèm kết luận -->
                    @if($appointment->medicalRecord->result_files && is_array($appointment->medicalRecord->result_files) && count($appointment->medicalRecord->result_files) > 0)
                        <div class="mt-6 pt-4 border-t border-slate-100">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block mb-2">Tệp kết quả đính kèm</span>
                            <div class="flex flex-wrap gap-2">
                                @foreach($appointment->medicalRecord->result_files as $file)
                                    @php $fPath = is_array($file) ? ($file['path'] ?? '') : $file; @endphp
                                    <a href="{{ Storage::url($fPath) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 text-xs font-semibold transition">
                                        <i class="fa-regular fa-file-pdf text-rose-600"></i> Tệp kết quả {{ $loop->iteration }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Đơn thuốc -->
                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm flex flex-col justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 pb-4 border-b border-slate-100 flex items-center gap-2">
                            <i class="fa-solid fa-pills text-emerald-600"></i> Đơn thuốc điều trị
                        </h3>

                        @if($appointment->medicalRecord->prescription && $appointment->medicalRecord->prescription->items && is_array($appointment->medicalRecord->prescription->items) && count($appointment->medicalRecord->prescription->items) > 0)
                            <div class="mt-4 overflow-x-auto rounded-2xl border border-slate-200">
                                <table class="w-full min-w-[320px] text-left text-sm text-slate-700">
                                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500 font-bold border-b border-slate-200">
                                        <tr>
                                            <th class="px-3.5 py-2.5">Tên thuốc</th>
                                            <th class="px-2 py-2.5 text-center">SL</th>
                                            <th class="px-3 py-2.5">Hướng dẫn</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach($appointment->medicalRecord->prescription->items as $item)
                                            <tr class="hover:bg-slate-50/50">
                                                <td class="px-3.5 py-3">
                                                    <div class="font-bold text-slate-900">{{ $item['medicine_name'] ?? '—' }}</div>
                                                    @if(isset($item['dosage']) && $item['dosage'])
                                                        <div class="text-[11px] text-slate-500">{{ $item['dosage'] }}</div>
                                                    @endif
                                                </td>
                                                <td class="px-2 py-3 text-center font-bold text-slate-800">
                                                    {{ $item['quantity'] ?? '—' }}
                                                </td>
                                                <td class="px-3 py-3 text-xs text-slate-600">
                                                    {{ $item['instructions'] ?? 'Theo chỉ định' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @if($appointment->medicalRecord->prescription->general_note)
                                <div class="mt-4 p-3 rounded-2xl bg-slate-50 text-xs text-slate-600 border border-slate-100">
                                    <strong class="text-slate-800">Lưu ý khi dùng thuốc:</strong>
                                    {{ $appointment->medicalRecord->prescription->general_note }}
                                </div>
                            @endif
                        @else
                            <div class="mt-12 py-8 text-center text-slate-400">
                                <i class="fa-solid fa-capsules text-4xl mb-3 text-slate-300"></i>
                                <p class="text-sm font-medium">Lượt khám này không có đơn thuốc kèm theo.</p>
                            </div>
                        @endif
                    </div>

                    <div class="mt-6 pt-3 text-[11px] text-slate-400 italic text-center">
                        * Bệnh nhân tuân thủ nghiêm ngặt chỉ định dùng thuốc của bác sĩ.
                    </div>
                </div>
            </div>

            <!-- 4. Chi tiết Cận lâm sàng & Xét nghiệm (Nếu có) -->
            @php
                $completedVisits = $appointment->clinicalVisits->where('status', 'completed');
            @endphp
            @if($completedVisits->isNotEmpty())
                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
                    <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-microscope text-purple-600"></i> Kết quả Khám Cận lâm sàng & Xét nghiệm
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($completedVisits as $visit)
                            <div class="p-4 rounded-2xl bg-slate-50/80 border border-slate-200/80 space-y-3">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <h4 class="font-bold text-slate-900 text-sm">
                                            {{ $visit->is_origin ? 'Khám Ban Đầu' : 'Cận Lâm Sàng' }} - {{ $visit->room->name ?? 'Phòng chức năng' }}
                                        </h4>
                                        <p class="text-xs text-slate-500 mt-0.5">
                                            Bác sĩ: <span class="font-semibold text-slate-700">{{ $visit->doctorProfile->user->full_name ?? '—' }}</span>
                                        </p>
                                    </div>
                                    @if($visit->completed_at)
                                        <span class="text-[11px] font-medium text-slate-400 whitespace-nowrap">
                                            {{ $visit->completed_at->format('H:i d/m/Y') }}
                                        </span>
                                    @endif
                                </div>

                                <div class="text-xs text-slate-700 bg-white p-3 rounded-xl border border-slate-100 whitespace-pre-line leading-relaxed">
                                    {{ $visit->findings ?: 'Đã hoàn tất kết luận chuyên môn.' }}
                                </div>

                                @if($visit->result_files && is_array($visit->result_files) && count($visit->result_files) > 0)
                                    <div class="flex flex-wrap gap-2 pt-1">
                                        @foreach($visit->result_files as $index => $file)
                                            @php $vPath = is_array($file) ? ($file['path'] ?? '') : $file; @endphp
                                            <a href="{{ Storage::url($vPath) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-purple-50 text-purple-700 hover:bg-purple-100 text-xs font-semibold transition border border-purple-100">
                                                <i class="fa-regular fa-image text-purple-600"></i> Kết quả CLS {{ $index + 1 }}
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- PRINT SIGNATURE SECTION (Only visible when printing) -->
            <div class="hidden print:block pt-8 mt-8 border-t border-slate-200">
                <div class="grid grid-cols-2 text-center text-sm">
                    <div>
                        <p class="font-bold text-slate-800">BỆNH NHÂN / NGƯỜI NHÀ</p>
                        <p class="text-xs text-slate-400 italic mt-1">(Ký và ghi rõ họ tên)</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 mb-1">Ngày ..... tháng ..... năm 20....</p>
                        <p class="font-bold text-slate-800">BÁC SĨ KHÁM BỆNH</p>
                        <p class="text-xs text-slate-400 italic mt-1">(Ký và ghi rõ họ tên)</p>
                        <div class="h-16"></div>
                        <p class="font-bold text-slate-900">{{ $appointment->doctorProfile->full_title ?? '' }}</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-layouts.app>
