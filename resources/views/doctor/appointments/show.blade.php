<x-layouts.doctor title="Chi tiết lịch hẹn #{{ $appointment->appointment_code }}">
    <!-- Breadcrumb & Actions -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center text-sm text-gray-500 mb-2">
                <a href="{{ route('doctor.dashboard') }}" class="hover:text-blue-600 transition-colors">Bảng điều khiển</a>
                <i class="fa-solid fa-chevron-right text-[10px] mx-2"></i>
                <a href="{{ route('doctor.appointments.index') }}" class="hover:text-blue-600 transition-colors">Lịch hẹn</a>
                <i class="fa-solid fa-chevron-right text-[10px] mx-2"></i>
                <span class="text-gray-800 font-medium">{{ $appointment->appointment_code }}</span>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Chi tiết lịch hẹn <span class="text-blue-600">#{{ $appointment->appointment_code }}</span></h2>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('doctor.appointments.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" style="display: none;"
            class="bg-green-50 text-green-800 p-4 rounded-lg mb-6 flex items-center justify-between border border-green-200">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-green-500"></i>
                {{ session('success') }}
            </div>
            <button @click="show=false" class="text-green-500 hover:text-green-700"><i class="fa-solid fa-xmark"></i></button>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 mb-6">
        <!-- Thông tin bệnh nhân -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-bold text-gray-900 border-b border-gray-100 pb-4 mb-4">Thông tin bệnh nhân</h3>
            <div class="flex items-start gap-4 mb-6">
                <div class="h-16 w-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-bold text-xl flex-shrink-0">
                    {{ substr($appointment->patientProfile->full_name, 0, 1) }}
                </div>
                <div>
                    <h4 class="text-lg font-bold text-gray-900">{{ $appointment->patientProfile->full_name }}</h4>
                    <div class="flex flex-wrap gap-4 mt-2 text-sm text-gray-600">
                        @if ($appointment->patientProfile->date_of_birth)
                            <div class="flex items-center gap-1.5"><i class="fa-regular fa-calendar"></i> {{ \Carbon\Carbon::parse($appointment->patientProfile->date_of_birth)->format('d/m/Y') }} ({{ \Carbon\Carbon::parse($appointment->patientProfile->date_of_birth)->age }} tuổi)</div>
                        @endif
                        <div class="flex items-center gap-1.5"><i class="fa-solid fa-venus-mars"></i> {{ $appointment->patientProfile->gender === 'male' ? 'Nam' : ($appointment->patientProfile->gender === 'female' ? 'Nữ' : 'Khác') }}</div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div><span class="text-gray-500 mr-2">SĐT:</span> <span class="font-medium">{{ $appointment->patientProfile->phone ?? '—' }}</span></div>
                <div><span class="text-gray-500 mr-2">BHYT:</span> <span class="font-medium">{{ $appointment->patientProfile->health_insurance_number ?? '—' }}</span></div>
                <div class="sm:col-span-2"><span class="text-gray-500 mr-2">Lý do khám:</span> <span class="font-medium">{{ $appointment->reason }}</span></div>
                @if ($appointment->receptionist_note)
                    <div class="sm:col-span-2 bg-yellow-50 p-3 rounded-lg border border-yellow-100 text-yellow-800">
                        <span class="font-semibold mr-1">Ghi chú từ lễ tân:</span> {{ $appointment->receptionist_note }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Tiền sử bệnh & Triệu chứng -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-bold text-gray-900 border-b border-gray-100 pb-4 mb-4">Thông tin bệnh án & Tiền sử</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Triệu chứng -->
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-2">Triệu chứng/Ghi chú của bệnh nhân:</h4>
                    @if($appointment->patientProfile->symptom_notes)
                        <div class="bg-gray-50 p-4 rounded-lg text-sm text-gray-700 border border-gray-200">
                            {{ $appointment->patientProfile->symptom_notes }}
                        </div>
                    @else
                        <div class="text-sm text-gray-500 italic">Không có ghi chú triệu chứng.</div>
                    @endif
                </div>

                <!-- Lịch sử bệnh án (Files) -->
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-2">Hồ sơ bệnh án đính kèm:</h4>
                    @if($appointment->patientProfile->medical_history && is_array($appointment->patientProfile->medical_history) && count($appointment->patientProfile->medical_history) > 0)
                        <ul class="space-y-2">
                            @foreach($appointment->patientProfile->medical_history as $file)
                                <li class="flex items-center justify-between p-3 bg-gray-50 border border-gray-200 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <i class="fa-solid fa-file-medical text-blue-500 text-lg"></i>
                                        <span class="text-sm font-medium text-gray-700 truncate max-w-[200px]" title="{{ is_array($file) ? ($file['name'] ?? 'File') : basename($file) }}">
                                            {{ is_array($file) ? ($file['name'] ?? 'File đính kèm') : basename($file) }}
                                        </span>
                                    </div>
                                    <a href="{{ is_array($file) ? asset('storage/' . ($file['path'] ?? '')) : asset('storage/' . $file) }}" target="_blank" class="text-xs bg-blue-100 text-blue-700 px-3 py-1.5 rounded hover:bg-blue-200 transition-colors">
                                        Xem trước
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-sm text-gray-500 italic">Không có hồ sơ đính kèm.</div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Lịch sử các ca khám đã hoàn thành -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-bold text-gray-900 border-b border-gray-100 pb-4 mb-4">Lịch sử khám bệnh (Đã hoàn thành)</h3>
            
            @if($pastAppointments->count() > 0)
                <div class="space-y-4">
                    @foreach($pastAppointments as $pastApt)
                        <div x-data="{ expanded: false }" class="border border-gray-200 rounded-lg overflow-hidden">
                            <!-- Header (Clickable to expand) -->
                            <div @click="expanded = !expanded" class="bg-gray-50 px-4 py-3 flex items-center justify-between cursor-pointer hover:bg-gray-100 transition-colors">
                                <div class="flex items-center gap-4">
                                    <div class="bg-green-100 text-green-600 p-2 rounded-full">
                                        <i class="fa-solid fa-check-circle"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-900">
                                            Ngày khám: {{ \Carbon\Carbon::parse($pastApt->appointment_date)->format('d/m/Y') }} lúc {{ substr($pastApt->appointment_time, 0, 5) }}
                                        </div>
                                        <div class="text-xs text-gray-500 mt-0.5">
                                            Bác sĩ: <span class="font-medium text-gray-700">{{ $pastApt->doctor->user->full_name ?? 'N/A' }}</span> - Chuyên khoa: {{ $pastApt->specialty->name ?? 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-gray-400">
                                    <i class="fa-solid fa-chevron-down transition-transform duration-200" :class="expanded ? 'rotate-180' : ''"></i>
                                </div>
                            </div>
                            
                            <!-- Expanded Content -->
                            <div x-show="expanded" x-collapse x-cloak class="p-4 bg-white border-t border-gray-200" style="display: none;">
                                @if($pastApt->medicalRecord)
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <!-- Kết quả khám -->
                                        <div>
                                            <h5 class="text-sm font-bold text-gray-800 mb-3 flex items-center gap-2">
                                                <i class="fa-solid fa-notes-medical text-blue-500"></i> Hồ sơ khám bệnh
                                            </h5>
                                            <div class="space-y-3 text-sm">
                                                <div><span class="text-gray-500 block mb-1">Chẩn đoán:</span> <span class="font-medium text-gray-900 bg-gray-50 p-2 rounded block">{{ $pastApt->medicalRecord->diagnosis ?? 'Không có thông tin' }}</span></div>
                                                <div><span class="text-gray-500 block mb-1">Kế hoạch điều trị:</span> <div class="text-gray-800 bg-gray-50 p-2 rounded min-h-[60px]">{{ $pastApt->medicalRecord->treatment_plan ?? 'Không có thông tin' }}</div></div>
                                                <div><span class="text-gray-500 block mb-1">Lời khuyên của bác sĩ:</span> <div class="text-gray-800 bg-gray-50 p-2 rounded min-h-[60px]">{{ $pastApt->medicalRecord->doctor_advice ?? 'Không có thông tin' }}</div></div>
                                            </div>
                                        </div>
                                        
                                        <!-- Đơn thuốc -->
                                        <div>
                                            <h5 class="text-sm font-bold text-gray-800 mb-3 flex items-center gap-2">
                                                <i class="fa-solid fa-pills text-purple-500"></i> Đơn thuốc
                                            </h5>
                                            @if($pastApt->medicalRecord->prescription && $pastApt->medicalRecord->prescription->details->count() > 0)
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
                                                            @foreach($pastApt->medicalRecord->prescription->details as $detail)
                                                                <tr>
                                                                    <td class="px-3 py-2 text-gray-900 font-medium">{{ $detail->medication->name ?? $detail->medication_name }}</td>
                                                                    <td class="px-3 py-2 text-gray-700">{{ $detail->quantity }}</td>
                                                                    <td class="px-3 py-2 text-gray-500 text-xs">{{ $detail->dosage }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <div class="text-sm text-gray-500 italic bg-gray-50 p-3 rounded text-center border border-dashed border-gray-300">
                                                    Không có đơn thuốc cho ca khám này.
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <div class="text-center p-6 text-gray-500">
                                        <i class="fa-solid fa-folder-open text-3xl mb-2 text-gray-300"></i>
                                        <p>Chưa có dữ liệu hồ sơ khám bệnh cho lịch hẹn này.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center p-8 bg-gray-50 rounded-lg border border-dashed border-gray-200">
                    <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fa-solid fa-clock-rotate-left text-gray-400 text-xl"></i>
                    </div>
                    <h3 class="text-sm font-medium text-gray-900">Không có lịch sử</h3>
                    <p class="text-sm text-gray-500 mt-1">Bệnh nhân này chưa có ca khám nào hoàn thành trước đây.</p>
                </div>
            @endif
        </div>

        <!-- Cập nhật trạng thái -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-bold text-gray-900 border-b border-gray-100 pb-4 mb-4">Trạng thái lịch hẹn</h3>
            
            <div class="mb-4">
                <span class="block text-sm text-gray-500 mb-1">Trạng thái hiện tại:</span>
                @php $color = $appointment->status_color; @endphp
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-{{ $color }}-100 text-{{ $color }}-800 border border-{{ $color }}-200">
                    {{ $appointment->status_label }}
                </span>
            </div>

            <form action="{{ route('doctor.appointments.update-status', $appointment->id) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm text-gray-500 mb-1">Cập nhật thành:</label>
                    <select name="status" class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">
                        <option value="pending" {{ $appointment->status === 'pending' ? 'selected' : '' }}>Đã tiếp nhận</option>
                        <option value="checked_in" {{ $appointment->status === 'checked_in' ? 'selected' : '' }}>Đã check-in</option>
                        <option value="examining" {{ $appointment->status === 'examining' ? 'selected' : '' }}>Đang khám</option>
                        <option value="completed" {{ $appointment->status === 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                        <option value="absent" {{ $appointment->status === 'absent' ? 'selected' : '' }}>Vắng mặt</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm text-gray-500 mb-1">Ghi chú (Tuỳ chọn):</label>
                    <textarea name="reason" rows="2" class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none"></textarea>
                </div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg text-sm font-medium transition-colors">
                    Lưu trạng thái
                </button>
            </form>
        </div>
    </div>

    <!-- Chỉ số sinh tồn -->
    @if ($appointment->vital_pulse || $appointment->vital_systolic_bp || $appointment->vital_temperature || $appointment->vital_weight_kg)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <h3 class="text-lg font-bold text-gray-900 border-b border-gray-100 pb-4 mb-4">Chỉ số sinh tồn</h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 text-center">
                <div class="text-gray-400 mb-1"><i class="fa-solid fa-heart-pulse"></i></div>
                <div class="text-xs text-gray-500 uppercase font-medium">Mạch</div>
                <div class="text-lg font-bold text-gray-900">{{ $appointment->vital_pulse ?? '—' }} <span class="text-xs font-normal text-gray-500">l/p</span></div>
            </div>
            <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 text-center">
                <div class="text-gray-400 mb-1"><i class="fa-solid fa-droplet"></i></div>
                <div class="text-xs text-gray-500 uppercase font-medium">Huyết áp</div>
                <div class="text-lg font-bold text-gray-900">{{ $appointment->vital_systolic_bp ?? '—' }}/{{ $appointment->vital_diastolic_bp ?? '—' }} <span class="text-xs font-normal text-gray-500">mmHg</span></div>
            </div>
            <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 text-center">
                <div class="text-gray-400 mb-1"><i class="fa-solid fa-temperature-half"></i></div>
                <div class="text-xs text-gray-500 uppercase font-medium">Nhiệt độ</div>
                <div class="text-lg font-bold text-gray-900">{{ $appointment->vital_temperature ?? '—' }} <span class="text-xs font-normal text-gray-500">°C</span></div>
            </div>
            <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 text-center">
                <div class="text-gray-400 mb-1"><i class="fa-solid fa-weight-scale"></i></div>
                <div class="text-xs text-gray-500 uppercase font-medium">Cân nặng</div>
                <div class="text-lg font-bold text-gray-900">{{ $appointment->vital_weight_kg ?? '—' }} <span class="text-xs font-normal text-gray-500">kg</span></div>
            </div>
        </div>
    </div>
    @endif
    
    <div class="text-center flex justify-center gap-4 flex-wrap">
        @if ($appointment->status === 'examining' || $appointment->status === 'completed')
            <a href="{{ route('doctor.clinical-visits.show', $appointment->id) }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg text-sm font-medium transition-colors shadow-sm">
                <i class="fa-solid fa-stethoscope mr-2"></i> Chuyển đến Giám sát lâm sàng
            </a>
        @endif

        @if ($appointment->status === 'completed')
            @if ($appointment->medicalRecord)
                <a href="{{ route('doctor.medical-records.show', $appointment->medicalRecord->id) }}" class="inline-block bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg text-sm font-medium transition-colors shadow-sm">
                    <i class="fa-solid fa-file-medical mr-2"></i> Xem hồ sơ bệnh án
                </a>
            @else
                <a href="{{ route('doctor.medical-records.create', $appointment->id) }}" class="inline-block bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg text-sm font-medium transition-colors shadow-sm">
                    <i class="fa-solid fa-file-medical mr-2"></i> Thêm hồ sơ bệnh án
                </a>
            @endif
        @endif
    </div>

</x-layouts.doctor>
