<x-layouts.doctor title="Chi tiết lịch sử khám: {{ $patient->full_name }}">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center text-sm text-gray-500 mb-2">
                <a href="{{ route('doctor.patient-history.index') }}" class="hover:text-blue-600 transition-colors">Bệnh án & Lịch sử khám</a>
                <i class="fa-solid fa-chevron-right text-[10px] mx-2"></i>
                <span class="text-gray-800 font-medium">{{ $patient->full_name }}</span>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Chi tiết Bệnh án</h2>
        </div>
        <a href="{{ route('doctor.patient-history.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
            <i class="fa-solid fa-arrow-left"></i> Quay lại
        </a>
    </div>

    <!-- Thông tin bệnh nhân -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
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

    <h3 class="text-lg font-bold text-gray-900 mb-4">Lịch sử các đợt khám ({{ $appointments->count() }})</h3>
    <div class="space-y-6">
        @forelse ($appointments as $appt)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <i class="fa-regular fa-calendar-check text-blue-500 text-xl"></i>
                        <div>
                            <h4 class="font-bold text-gray-900">Ngày khám: {{ $appt->appointment_date ? $appt->appointment_date->format('d/m/Y') : '—' }}</h4>
                            <div class="text-xs text-gray-500">Mã LH: {{ $appt->appointment_code }}</div>
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('doctor.appointments.show', $appt->id) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Chi tiết lịch hẹn <i class="fa-solid fa-arrow-right ml-1"></i></a>
                    </div>
                </div>
                <div class="p-6">
                    <div class="mb-4">
                        <div class="text-sm font-medium text-gray-700 mb-1">Lý do khám:</div>
                        <p class="text-sm text-gray-900">{{ $appt->reason }}</p>
                    </div>

                    @if ($appt->medicalRecord)
                        <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 mb-4">
                            <h5 class="font-bold text-blue-800 mb-2 border-b border-blue-200 pb-2">Kết quả khám & Chẩn đoán</h5>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="font-medium text-gray-700 block mb-1">Chẩn đoán (ICD-10: {{ $appt->medicalRecord->icd10_code ?? '—' }}):</span>
                                    <p class="text-gray-900">{{ $appt->medicalRecord->diagnosis ?? '—' }}</p>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700 block mb-1">Hướng điều trị:</span>
                                    <p class="text-gray-900">
                                        @if($appt->medicalRecord->treatment_result == 'outpatient') Ngoại trú
                                        @elseif($appt->medicalRecord->treatment_result == 'admitted') Nhập viện
                                        @elseif($appt->medicalRecord->treatment_result == 'monitoring') Theo dõi thêm
                                        @else {{ $appt->medicalRecord->treatment_result }} @endif
                                    </p>
                                </div>
                                <div class="md:col-span-2">
                                    <span class="font-medium text-gray-700 block mb-1">Dặn dò / Lời khuyên:</span>
                                    <p class="text-gray-900">{{ $appt->medicalRecord->advice ?? '—' }}</p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-sm text-gray-500 italic mb-4">Chưa có kết quả chẩn đoán cuối cùng.</div>
                    @endif

                    @if ($appt->clinicalVisits->isNotEmpty())
                        <div>
                            <h5 class="font-bold text-gray-900 mb-2 text-sm">Các phòng đã khám:</h5>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach($appt->clinicalVisits as $visit)
                                    <div class="border border-gray-100 rounded p-3 text-sm bg-gray-50">
                                        <div class="font-medium text-gray-900">{{ $visit->room->name ?? 'Phòng khám' }}</div>
                                        <div class="text-gray-500 mt-1 line-clamp-2">{{ $visit->findings ?: 'Không có ghi chú' }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-12 text-gray-500 bg-white rounded-xl shadow-sm border border-gray-100">
                <p>Bệnh nhân chưa có lịch sử khám hoàn thành nào.</p>
            </div>
        @endforelse
    </div>
</x-layouts.doctor>
