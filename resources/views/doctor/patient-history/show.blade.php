<x-layouts.doctor title="Chi tiết lượt khám: {{ $patient->full_name }}">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center text-sm text-gray-500 mb-2">
                <a href="{{ route('doctor.patient-history.index') }}" class="hover:text-blue-600 transition-colors">Lịch sử khám</a>
                <i class="fa-solid fa-chevron-right text-[10px] mx-2"></i>
                <span class="text-gray-800 font-medium">{{ $patient->full_name }}</span>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Chi tiết lượt khám</h2>
        </div>
        <a href="{{ route('doctor.patient-history.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
            <i class="fa-solid fa-arrow-left"></i> Quay lại
        </a>
    </div>

    <!-- Thông tin bệnh nhân -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Thông tin bệnh nhân</h3>
        <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
            <div class="h-20 w-20 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-bold text-3xl flex-shrink-0">
                {{ substr($patient->full_name, 0, 1) }}
            </div>
            <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 w-full">
                <div>
                    <div class="text-xs text-gray-500 font-medium mb-1">Họ và tên</div>
                    <div class="font-bold text-gray-900 text-lg">{{ $patient->full_name }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 font-medium mb-1">Ngày sinh & Giới tính</div>
                    <div class="font-medium text-gray-900">
                        @if($patient->date_of_birth) {{ \Carbon\Carbon::parse($patient->date_of_birth)->format('d/m/Y') }} @else — @endif 
                        • {{ $patient->gender === 'male' ? 'Nam' : ($patient->gender === 'female' ? 'Nữ' : 'Khác') }}
                    </div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 font-medium mb-1">Số điện thoại</div>
                    <div class="font-medium text-gray-900">{{ $patient->phone ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 font-medium mb-1">BHYT</div>
                    <div class="font-medium text-blue-600">{{ $patient->health_insurance_number ?? '—' }}</div>
                </div>
            </div>
        </div>
        @if ($patient->medical_history)
            <div class="mt-6 p-4 bg-red-50 border border-red-100 rounded-lg">
                <div class="text-xs font-bold text-red-700 uppercase mb-1"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Lưu ý y tế / Tiền sử bệnh</div>
                <p class="text-sm text-red-800">{{ is_string($patient->medical_history) ? $patient->medical_history : json_encode($patient->medical_history, JSON_UNESCAPED_UNICODE) }}</p>
            </div>
        @endif
    </div>

    @if($doctorProfile->doctor_type === 'clinical')
        <div class="grid gap-6 xl:grid-cols-[1.5fr_0.9fr]">
            <div class="space-y-6">
                <!-- Thông tin lịch hẹn chung -->
                <section class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="text-xs font-medium text-gray-500">Mã lịch hẹn</div>
                            <h2 class="mt-1 text-2xl font-bold text-gray-900">{{ $appointment->appointment_code }}</h2>
                        </div>
                        <span class="rounded-full px-4 py-2 text-sm font-semibold {{ $appointment->status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">{{ $appointment->status_label }}</span>
                    </div>

                    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="rounded-xl bg-gray-50 p-4 text-sm text-gray-700 border border-gray-100">
                            <div class="text-xs font-medium text-gray-500">Ngày khám</div>
                            <div class="mt-1 text-lg font-bold text-gray-900">{{ $appointment->appointment_date?->format('d/m/Y') ?? '—' }}</div>
                        </div>
                        <div class="rounded-xl bg-gray-50 p-4 text-sm text-gray-700 border border-gray-100">
                            <div class="text-xs font-medium text-gray-500">Giờ khám</div>
                            <div class="mt-1 text-lg font-bold text-gray-900">{{ $appointment->appointment_time ? substr($appointment->appointment_time, 0, 5) : '—' }}</div>
                        </div>
                        <div class="rounded-xl bg-gray-50 p-4 text-sm text-gray-700 border border-gray-100">
                            <div class="text-xs font-medium text-gray-500">Phòng khám</div>
                            <div class="mt-1 text-lg font-bold text-gray-900">{{ $appointment->room->name ?? '—' }}</div>
                        </div>
                    </div>

                    @if ($appointment->reason)
                        <div class="mt-6 rounded-xl bg-blue-50 p-4 text-sm text-blue-900 border border-blue-100">
                            <div class="font-bold text-blue-900">Lý do khám</div>
                            <p class="mt-2">{{ $appointment->reason }}</p>
                        </div>
                    @endif
                </section>

                <!-- Kết quả khám & Chẩn đoán -->
                @if ($appointment->medicalRecord)
                    <section class="rounded-xl border border-blue-100 bg-white p-6 shadow-sm overflow-hidden">
                        <h3 class="text-lg font-bold text-blue-800 border-b border-blue-100 pb-3 mb-4">Kết quả khám & Chẩn đoán</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="font-medium text-gray-500 block mb-1">Chẩn đoán (ICD-10: {{ $appointment->medicalRecord->icd10_code ?? '—' }}):</span>
                                <p class="text-gray-900 font-medium">{{ $appointment->medicalRecord->diagnosis ?? '—' }}</p>
                            </div>
                            <div>
                                <span class="font-medium text-gray-500 block mb-1">Hướng điều trị:</span>
                                <p class="text-gray-900 font-medium">
                                    @if($appointment->medicalRecord->treatment_result == 'outpatient') Ngoại trú
                                    @elseif($appointment->medicalRecord->treatment_result == 'admitted') Nhập viện
                                    @elseif($appointment->medicalRecord->treatment_result == 'monitoring') Theo dõi thêm
                                    @else {{ $appointment->medicalRecord->treatment_result }} @endif
                                </p>
                            </div>
                            
                            @if($appointment->medicalRecord->conclusion)
                            <div class="md:col-span-2">
                                <span class="font-medium text-gray-500 block mb-1">Kết luận:</span>
                                <p class="text-gray-900 font-medium">{{ $appointment->medicalRecord->conclusion }}</p>
                            </div>
                            @endif

                            @if($appointment->medicalRecord->followup_date)
                            <div>
                                <span class="font-medium text-gray-500 block mb-1">Ngày tái khám:</span>
                                <p class="text-gray-900 font-medium text-blue-600"><i class="fa-regular fa-calendar text-blue-500 mr-1"></i> {{ \Carbon\Carbon::parse($appointment->medicalRecord->followup_date)->format('d/m/Y') }}</p>
                            </div>
                            @endif

                            <div class="md:col-span-2 mt-2">
                                <span class="font-medium text-gray-500 block mb-1">Dặn dò / Lời khuyên:</span>
                                <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                                    <p class="text-gray-900 whitespace-pre-line">{{ $appointment->medicalRecord->advice ?? '—' }}</p>
                                </div>
                            </div>
                            
                            @if($appointment->medicalRecord->result_files)
                                <div class="md:col-span-2 mt-2">
                                    <span class="text-xs text-gray-500 block mb-2"><i class="fa-solid fa-paperclip"></i> File đính kèm:</span>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach(is_array($appointment->medicalRecord->result_files) ? $appointment->medicalRecord->result_files : json_decode($appointment->medicalRecord->result_files, true) as $file)
                                            @php $filePath = is_array($file) ? ($file['path'] ?? '') : $file; $fileName = is_array($file) ? ($file['name'] ?? 'Xem file') : 'Xem file'; @endphp
                                            <a href="{{ Storage::url($filePath) }}" target="_blank" class="inline-flex items-center gap-2 px-3 py-1.5 bg-blue-50 text-blue-600 rounded-lg text-xs font-medium hover:bg-blue-100 border border-blue-200">
                                                <i class="fa-solid fa-file"></i> {{ $fileName }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Đơn thuốc -->
                        <div class="mt-6 pt-4 border-t border-gray-100">
                            <h4 class="text-base font-bold text-gray-900 mb-3 flex items-center gap-2">
                                <i class="fa-solid fa-pills text-purple-500"></i> Đơn thuốc
                            </h4>
                            @if($appointment->medicalRecord->prescription && is_array($appointment->medicalRecord->prescription->items) && count($appointment->medicalRecord->prescription->items) > 0)
                            <div class="border border-gray-200 rounded-lg overflow-hidden">
                                <table class="min-w-full divide-y divide-gray-200 text-sm">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tên thuốc</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">SL</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Liều dùng</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                        @foreach($appointment->medicalRecord->prescription->items as $detail)
                                        <tr>
                                            <td class="px-3 py-2 text-gray-900 font-medium">{{ $detail['medicine_name'] ?? ($detail['medication_name'] ?? ($detail['name'] ?? '—')) }}</td>
                                            <td class="px-3 py-2 text-gray-700">{{ $detail['quantity'] ?? '—' }}</td>
                                            <td class="px-3 py-2 text-gray-500 text-xs">{{ $detail['dosage'] ?? '—' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @if($appointment->medicalRecord->prescription->general_note)
                            <div class="mt-3 text-sm">
                                <span class="font-medium text-gray-500">Ghi chú đơn thuốc:</span>
                                <span class="text-gray-900 ml-1">{{ $appointment->medicalRecord->prescription->general_note }}</span>
                            </div>
                            @endif
                            @else
                            <div class="text-sm text-gray-500 italic bg-gray-50 p-3 rounded text-center border border-dashed border-gray-300">
                                Không có đơn thuốc cho ca khám này.
                            </div>
                            @endif
                        </div>
                    </section>
                @endif
                
                @if ($appointment->clinicalVisits->isNotEmpty())
                    <section class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Chi tiết các phòng đã khám</h3>
                        <div class="grid grid-cols-1 gap-4">
                            @foreach($appointment->clinicalVisits as $visit)
                                <div class="border border-gray-200 rounded-lg p-4 text-sm bg-white shadow-sm hover:shadow-md transition-shadow">
                                    <div class="flex justify-between items-start mb-2 border-b border-gray-100 pb-2">
                                        <div class="font-bold text-blue-700 text-base">{{ $visit->room->name ?? 'Phòng khám/Dịch vụ' }}</div>
                                        <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-xs font-medium">{{ $visit->status == 'completed' ? 'Đã hoàn thành' : 'Chưa hoàn thành' }}</span>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-3">
                                        <div>
                                            <span class="text-xs text-gray-500 block">Bác sĩ thực hiện:</span>
                                            <span class="font-medium text-gray-900">{{ $visit->doctorProfile->full_title ?? '—' }}</span>
                                        </div>
                                        <div>
                                            <span class="text-xs text-gray-500 block">Kỹ thuật viên / Y tá:</span>
                                            <span class="font-medium text-gray-900">{{ $visit->collectedBy->full_name ?? '—' }}</span>
                                        </div>
                                        <div class="sm:col-span-2">
                                            <span class="text-xs text-gray-500 block mb-1">Kết quả / Ghi chú:</span>
                                            <div class="p-3 bg-gray-50 rounded border border-gray-100 text-gray-800">
                                                {{ $visit->findings ?: 'Chưa có ghi chú' }}
                                            </div>
                                        </div>
                                        @if($visit->result_files)
                                            <div class="sm:col-span-2 mt-2">
                                                <span class="text-xs text-gray-500 block mb-2"><i class="fa-solid fa-paperclip"></i> File đính kèm:</span>
                                                <div class="flex flex-wrap gap-2">
                                                    @foreach(is_array($visit->result_files) ? $visit->result_files : json_decode($visit->result_files, true) as $file)
                                                        @php $filePath = is_array($file) ? ($file['path'] ?? '') : $file; $fileName = is_array($file) ? ($file['name'] ?? 'Xem file') : 'Xem file'; @endphp
                                                        <a href="{{ Storage::url($filePath) }}" target="_blank" class="inline-flex items-center gap-2 px-3 py-1.5 bg-blue-50 text-blue-600 rounded-lg text-xs font-medium hover:bg-blue-100 border border-blue-200">
                                                            <i class="fa-solid fa-file"></i> {{ $fileName }}
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
                
                <!-- Log lịch sử -->
                @if ($appointment->logs->isNotEmpty())
                    <section class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-bold text-gray-900 mb-6">Lịch sử thay đổi trạng thái</h3>
                        <div class="relative border-l-2 border-gray-200 ml-3 space-y-6">
                            @foreach ($appointment->logs as $log)
                            <div class="relative pl-6">
                                <span class="absolute -left-[9px] top-1 h-4 w-4 rounded-full bg-gray-300 ring-4 ring-white"></span>
                                <div class="text-sm font-bold text-gray-900">{{ $log->action_label }}</div>
                                @if($log->old_status && $log->new_status)
                                    <div class="mt-1 text-sm text-gray-600">
                                        Trạng thái: <span class="font-medium">{{ $log->old_status_label }}</span> &rarr; <span class="font-bold text-gray-900">{{ $log->new_status_label }}</span>
                                    </div>
                                @elseif($log->new_status)
                                    <div class="mt-1 text-sm text-gray-600">
                                        Trạng thái: <span class="font-bold text-gray-900">{{ $log->new_status_label }}</span>
                                    </div>
                                @endif
                                @if($log->reason)
                                    <div class="mt-1 text-sm text-gray-500 italic">Ghi chú: {{ $log->reason }}</div>
                                @endif
                                <div class="mt-2 text-xs text-gray-400">
                                    <i class="fa-regular fa-clock"></i> {{ $log->created_at->format('H:i d/m/Y') }} 
                                    @if($log->changedBy)
                                        • Người đổi: <span class="font-medium text-gray-600">{{ $log->changedBy->full_name }}</span>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>

            <aside class="space-y-6">
                <!-- Tóm tắt chi phí -->
                <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-3 mb-4 border-b border-gray-100 pb-3">
                        <h3 class="text-lg font-bold text-gray-900">Chi phí dịch vụ</h3>
                    </div>

                    @if(isset($paymentSummary) && $paymentSummary['total_amount'] > 0)
                        <div class="space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Tổng phí dịch vụ:</span>
                                <span class="font-bold text-gray-900">{{ number_format($paymentSummary['total_amount'], 0, ',', '.') }}đ</span>
                            </div>
                            @if($paymentSummary['insurance_rate'] > 0)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">BHYT chi trả ({{ $paymentSummary['insurance_rate'] * 100 }}%):</span>
                                <span class="font-bold text-green-600">-{{ number_format($paymentSummary['insurance_covers'], 0, ',', '.') }}đ</span>
                            </div>
                            @endif
                            <div class="flex justify-between text-sm border-t border-gray-200 pt-3 mt-3">
                                <span class="font-bold text-gray-900">Người bệnh trả ({{ (1 - $paymentSummary['insurance_rate']) * 100 }}%):</span>
                                <span class="font-bold text-red-600 text-lg">{{ number_format($paymentSummary['patient_pays'], 0, ',', '.') }}đ</span>
                            </div>

                            <div class="mt-4 space-y-3 border-t border-gray-200 pt-4">
                                <h4 class="text-sm font-bold text-gray-900 mb-2">Chi tiết từng dịch vụ</h4>
                                
                                @foreach($paymentSummary['all_visits'] as $v)
                                    @php
                                        $visitName = $v->is_origin ? 'Phí Khám Bệnh' : ($v->room ? 'Khám ' . $v->room->name : 'Dịch vụ Cận lâm sàng / Khác');
                                        $baseFee = $v->payment_amount;
                                        $insCovers = round($baseFee * $paymentSummary['insurance_rate']);
                                        $patPays = $baseFee - $insCovers;
                                    @endphp
                                    <div class="rounded-lg bg-gray-50 p-3 border border-gray-100 text-sm">
                                        <div class="font-bold text-gray-800">{{ $visitName }}</div>
                                        <div class="mt-2 space-y-1">
                                            <div class="flex justify-between text-gray-600">
                                                <span>Phí ban đầu:</span>
                                                <span>{{ number_format($baseFee, 0, ',', '.') }}đ</span>
                                            </div>
                                            <div class="flex justify-between text-gray-700 font-bold border-t border-gray-100 pt-1 mt-1">
                                                <span>BN trả:</span>
                                                <span class="text-red-600">{{ number_format($patPays, 0, ',', '.') }}đ</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="rounded-lg bg-gray-50 p-4 text-sm text-gray-600 text-center border border-gray-100">
                            Chưa phát sinh chi phí hoặc chưa cập nhật.
                        </div>
                    @endif
                </div>
            </aside>
        </div>
    @else
        <!-- View dành cho bác sĩ Paraclinical -->
        <div class="grid gap-6">
            <section class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-6">
                    <div>
                        <div class="text-xs font-medium text-gray-500">Mã lượt khám cận lâm sàng</div>
                        <h2 class="mt-1 text-2xl font-bold text-gray-900">#{{ $visit->id }} (Gốc: {{ $visit->appointment->appointment_code }})</h2>
                    </div>
                    <span class="rounded-full px-4 py-2 text-sm font-semibold {{ $visit->status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">{{ $visit->status == 'completed' ? 'Đã hoàn thành' : 'Chưa hoàn thành' }}</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                            <h4 class="font-bold text-gray-900 mb-2 border-b border-gray-200 pb-2">Thông tin dịch vụ</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Phòng thực hiện:</span>
                                    <span class="font-bold text-gray-900">{{ $visit->room->name ?? '—' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Người thực hiện:</span>
                                    <span class="font-medium text-gray-900">{{ $visit->doctorProfile->full_title ?? $visit->collectedBy->full_name ?? '—' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Thời gian bắt đầu:</span>
                                    <span class="font-medium text-gray-900">{{ $visit->started_at ? $visit->started_at->format('H:i d/m/Y') : '—' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Thời gian hoàn thành:</span>
                                    <span class="font-medium text-gray-900">{{ $visit->completed_at ? $visit->completed_at->format('H:i d/m/Y') : '—' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="bg-blue-50 p-4 rounded-xl border border-blue-100 h-full">
                            <h4 class="font-bold text-blue-800 mb-2 border-b border-blue-200 pb-2">Kết quả cận lâm sàng</h4>
                            <div class="text-sm">
                                <span class="text-gray-600 block mb-1">Ghi chú / Kết luận:</span>
                                <div class="bg-white p-3 rounded border border-blue-100 text-gray-900 whitespace-pre-line min-h-[100px]">
                                    {{ $visit->findings ?: 'Không có ghi chú' }}
                                </div>

                                @if($visit->result_files)
                                    <div class="mt-4">
                                        <span class="text-gray-600 block mb-2"><i class="fa-solid fa-paperclip"></i> File đính kèm:</span>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach(is_array($visit->result_files) ? $visit->result_files : json_decode($visit->result_files, true) as $file)
                                                @php $filePath = is_array($file) ? ($file['path'] ?? '') : $file; $fileName = is_array($file) ? ($file['name'] ?? 'Tải / Xem file') : 'Tải / Xem file'; @endphp
                                                <a href="{{ Storage::url($filePath) }}" target="_blank" class="inline-flex items-center gap-2 px-3 py-2 bg-white text-blue-600 rounded-lg text-sm font-medium hover:bg-blue-50 border border-blue-200 shadow-sm transition-colors">
                                                    <i class="fa-solid fa-file"></i> {{ $fileName }}
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    @endif
</x-layouts.doctor>
