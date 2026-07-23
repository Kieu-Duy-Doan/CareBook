<x-layouts.patient-dashboard title="Chi tiết kết quả khám" activeMenu="records">
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('patient.records.index') }}" class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-700 transition">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight">Chi tiết Kết quả khám</h1>
                    <p class="text-slate-500 mt-1 text-sm">Mã lịch hẹn: {{ $appointment->appointment_code }}</p>
                </div>
            </div>
            
            <a href="{{ route('patient.appointments.show', $appointment->id) }}" class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition shadow-sm">
                <i class="fa-solid fa-calendar-check text-blue-600"></i> Xem chi tiết lịch khám
            </a>
        </div>

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="text-xs uppercase tracking-[0.25em] text-slate-400">Lịch khám</div>
                    <h2 class="mt-2 text-xl font-semibold text-slate-900">{{ $appointment->appointment_date?->format('d/m/Y') ?? '—' }}@if($appointment->appointment_time) lúc {{ substr($appointment->appointment_time, 0, 5) }}@endif</h2>
                    <div class="mt-2 text-sm text-slate-600">Bác sĩ: {{ $appointment->doctorProfile->full_title ?? 'Chưa có' }} · Phòng: {{ $appointment->room->name ?? 'Chưa có' }}</div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full px-4 py-1.5 text-sm font-semibold {{ $appointment->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : ($appointment->status === 'cancelled' ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-700') }}">{{ $appointment->status_label }}</span>
                </div>
            </div>

            <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_0.9fr]">
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                    <h3 class="text-lg font-semibold text-slate-900">Kết quả khám</h3>
                    <div class="mt-4 space-y-4 text-sm text-slate-700">
                        <div>
                            <div class="text-xs uppercase tracking-[0.2em] text-slate-500">Chẩn đoán (ICD-10)</div>
                            <div class="mt-1 font-semibold text-slate-900">{{ $appointment->medicalRecord->icd10_code ? $appointment->medicalRecord->icd10_code . ' · ' : '' }}{{ $appointment->medicalRecord->diagnosis ?? 'Chưa có' }}</div>
                        </div>
                        <div>
                            <div class="text-xs uppercase tracking-[0.2em] text-slate-500">Kết luận</div>
                            <div class="mt-1 text-slate-700">{{ $appointment->medicalRecord->conclusion ?? 'Chưa có' }}</div>
                        </div>
                        <div>
                            <div class="text-xs uppercase tracking-[0.2em] text-slate-500">Lời khuyên / Dặn dò</div>
                            <div class="mt-1 italic text-slate-700">{{ $appointment->medicalRecord->advice ?? 'Chưa có' }}</div>
                        </div>
                        @if ($appointment->medicalRecord->followup_date)
                            <div class="rounded-3xl bg-white border border-slate-200 p-4 text-sm text-slate-700">
                                <span class="font-semibold text-slate-900">Hẹn tái khám:</span> {{ $appointment->medicalRecord->followup_date->format('d/m/Y') }}
                            </div>
                        @endif

                        @if ($appointment->medicalRecord->result_files && is_array($appointment->medicalRecord->result_files) && count($appointment->medicalRecord->result_files) > 0)
                            <div class="rounded-3xl border border-slate-200 bg-white p-4">
                                <div class="text-xs uppercase tracking-[0.2em] text-slate-500 mb-3">File đính kèm kết quả</div>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($appointment->medicalRecord->result_files as $file)
                                        @php $mrFilePath = is_array($file) ? ($file['path'] ?? '') : $file; @endphp
                                        <a href="{{ Storage::url($mrFilePath) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition">
                                            @if(Str::endsWith($mrFilePath, ['.pdf']))
                                                <i class="fa-solid fa-file-pdf text-red-500"></i>
                                            @elseif(Str::endsWith($mrFilePath, ['.doc', '.docx']))
                                                <i class="fa-solid fa-file-word text-blue-600"></i>
                                            @elseif(Str::endsWith($mrFilePath, ['.png', '.jpg', '.jpeg']))
                                                <i class="fa-regular fa-image text-slate-500"></i>
                                            @else
                                                <i class="fa-solid fa-file text-slate-400"></i>
                                            @endif
                                            Tệp đính kèm {{ $loop->iteration }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                    <div class="flex items-center justify-between gap-3">
                        <h3 class="text-lg font-semibold text-slate-900">Đơn thuốc</h3>
                        @if (! $appointment->medicalRecord->prescription)
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Chưa có</span>
                        @endif
                    </div>

                    @if ($appointment->medicalRecord->prescription)
                        <div class="mt-4 space-y-4 text-sm text-slate-700">
                            @if ($appointment->medicalRecord->prescription->items && is_array($appointment->medicalRecord->prescription->items) && count($appointment->medicalRecord->prescription->items) > 0)
                                <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white text-sm">
                                    <table class="w-full text-left text-slate-700">
                                        <thead class="bg-slate-100 text-xs uppercase tracking-[0.2em] text-slate-500">
                                            <tr>
                                                <th class="px-4 py-3">Thuốc</th>
                                                <th class="px-4 py-3 text-center">Liều lượng</th>
                                                <th class="px-4 py-3 text-center">Số lượng</th>
                                                <th class="px-4 py-3">Cách dùng</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-200">
                                            @foreach ($appointment->medicalRecord->prescription->items as $item)
                                                <tr>
                                                    <td class="px-4 py-3 font-semibold text-slate-800">{{ $item['medicine_name'] ?? '—' }}</td>
                                                    <td class="px-4 py-3 text-center">{{ $item['dosage'] ?? '—' }}</td>
                                                    <td class="px-4 py-3 text-center">{{ $item['quantity'] ?? '—' }}</td>
                                                    <td class="px-4 py-3">{{ $item['instructions'] ?? '—' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="rounded-3xl border border-slate-200 bg-white p-4 text-slate-700">Chưa có danh sách thuốc.</div>
                            @endif

                            @if ($appointment->medicalRecord->prescription->general_note)
                                <div class="rounded-3xl border border-slate-200 bg-white p-4 text-slate-700">
                                    <div class="text-xs uppercase tracking-[0.2em] text-slate-500 mb-2">Ghi chú đơn thuốc</div>
                                    <div class="text-slate-800">{{ $appointment->medicalRecord->prescription->general_note }}</div>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="mt-4 rounded-3xl border border-slate-200 bg-white p-6 text-center text-slate-600">
                            Chưa có đơn thuốc được kê cho lượt khám này.
                        </div>
                    @endif
                </div>
            </div>

            @php
                $completedVisits = $appointment->clinicalVisits->where('status', 'completed');
            @endphp
            @if ($completedVisits->isNotEmpty())
                <div class="mt-6 rounded-3xl border border-slate-200 bg-slate-50 p-5">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Chi tiết khám lâm sàng & cận lâm sàng</h3>
                    <div class="grid gap-4 md:grid-cols-2">
                        @foreach ($completedVisits as $visit)
                            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                <div class="flex items-center justify-between mb-1 gap-2">
                                    <div class="font-semibold text-slate-900 text-sm">{{ $visit->is_origin ? 'Khám Bệnh Ban Đầu' : 'Khám Cận Lâm Sàng' }} - {{ $visit->room->name ?? 'Không rõ phòng' }}</div>
                                    <div class="text-xs text-slate-400 whitespace-nowrap">{{ $visit->completed_at ? $visit->completed_at->format('H:i d/m/Y') : '' }}</div>
                                </div>
                                <div class="text-xs text-slate-500 mb-3">Bác sĩ: <span class="font-medium text-slate-700">{{ $visit->doctorProfile->user->full_name ?? '—' }}</span></div>
                                
                                <div class="text-sm text-slate-700 whitespace-pre-wrap mb-3">{{ $visit->findings ?: 'Chưa có kết luận/ghi chú.' }}</div>

                                @if($visit->result_files && is_array($visit->result_files) && count($visit->result_files) > 0)
                                    <div class="pt-3 border-t border-slate-100 flex flex-wrap gap-2">
                                        @foreach($visit->result_files as $index => $file)
                                            @php $filePath = is_array($file) ? ($file['path'] ?? '') : $file; @endphp
                                            <a href="{{ \Illuminate\Support\Facades\Storage::url($filePath) }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-100 transition">
                                                <i class="fa-regular fa-file-pdf text-rose-500"></i> Xem kết quả {{ $index + 1 }}
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>
    </div>
</x-layouts.patient-dashboard>
