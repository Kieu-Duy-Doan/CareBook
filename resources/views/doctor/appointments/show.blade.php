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
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('doctor.appointments.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i> Quay lại
            </a>
            
            <!-- Quick Actions cho Bác sĩ -->
            @if ($appointment->status === 'checked_in')
                <form action="{{ route('doctor.appointments.update-status', $appointment->id) }}" method="POST" class="inline-block">
                    @csrf
                    <input type="hidden" name="status" value="examining">
                    <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                        <i class="fa-solid fa-stethoscope"></i> Bắt đầu khám
                    </button>
                </form>
            @endif

            @if ($appointment->status === 'examining')
                <form action="{{ route('doctor.appointments.update-status', $appointment->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Xác nhận hoàn thành buổi khám?');">
                    @csrf
                    <input type="hidden" name="status" value="completed">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                        <i class="fa-solid fa-check-double"></i> Hoàn thành khám
                    </button>
                </form>
            @endif

            @if (in_array($appointment->status, ['examining', 'completed']))
                <a href="{{ route('doctor.clinical-visits.show', $appointment->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                    <i class="fa-solid fa-file-prescription"></i> Kê đơn / Dịch vụ
                </a>
            @endif
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
                                            @if($pastApt->medicalRecord->prescription && is_array($pastApt->medicalRecord->prescription->items) && count($pastApt->medicalRecord->prescription->items) > 0)
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
                                                            @foreach($pastApt->medicalRecord->prescription->items as $detail)
                                                                <tr>
                                                                    <td class="px-3 py-2 text-gray-900 font-medium">{{ $detail['medication_name'] ?? ($detail['name'] ?? '—') }}</td>
                                                                    <td class="px-3 py-2 text-gray-700">{{ $detail['quantity'] ?? '—' }}</td>
                                                                    <td class="px-3 py-2 text-gray-500 text-xs">{{ $detail['dosage'] ?? '—' }}</td>
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

        <!-- Panel hành động theo trạng thái -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-bold text-gray-900 border-b border-gray-100 pb-4 mb-4">Trạng thái lịch hẹn</h3>

            <div class="mb-4">
                <span class="block text-sm text-gray-500 mb-1">Trạng thái hiện tại:</span>
                @php $color = $appointment->status_color; @endphp
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-{{ $color }}-100 text-{{ $color }}-800 border border-{{ $color }}-200">
                    {{ $appointment->status_label }}
                </span>
            </div>

            {{-- ===== LUỒNG HÀNH ĐỘNG THEO TRẠNG THÁI ===== --}}

            @if ($appointment->status === 'checked_in')
                {{-- Bệnh nhân đã check-in → Bác sĩ bắt đầu khám --}}
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <p class="text-sm text-blue-700 mb-3"><i class="fa-solid fa-circle-info mr-1"></i> Bệnh nhân đã check-in và đang chờ khám.</p>
                    <form action="{{ route('doctor.appointments.update-status', $appointment->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="examining">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-lg text-sm font-semibold transition-colors flex items-center justify-center gap-2">
                            <i class="fa-solid fa-stethoscope"></i> Bắt đầu khám
                        </button>
                    </form>
                </div>

            @elseif ($appointment->status === 'examining')
                {{-- Đang khám → 2 nút: Giám sát CLS + Ghi kết luận --}}
                @php
                    $clinicalVisits    = $appointment->clinicalVisits ?? collect();
                    $subVisits         = $clinicalVisits->where('is_origin', false);
                    $totalSubs         = $subVisits->count();
                    $completedSubs     = $subVisits->whereIn('status', ['completed', 'refused'])->count();
                    $allSubsDone       = $totalSubs > 0 && $completedSubs === $totalSubs;
                    $hasRecord         = $appointment->medicalRecord !== null;
                    $hasPrescription   = $hasRecord && $appointment->medicalRecord->prescription !== null;
                    $canComplete       = $hasRecord && ($totalSubs === 0 || $allSubsDone);
                @endphp

                {{-- Tiến trình phòng cận lâm sàng (nếu có chỉ định) --}}
                @if ($totalSubs > 0)
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-semibold text-gray-700">Tiến trình cận lâm sàng</span>
                            <span class="text-xs font-bold {{ $allSubsDone ? 'text-green-600' : 'text-amber-600' }}">
                                {{ $completedSubs }}/{{ $totalSubs }} phòng hoàn thành
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 mb-3">
                            <div class="h-2 rounded-full {{ $allSubsDone ? 'bg-green-500' : 'bg-blue-500' }} transition-all"
                                 style="width: {{ $totalSubs > 0 ? round(($completedSubs / $totalSubs) * 100) : 0 }}%"></div>
                        </div>
                        @if ($allSubsDone)
                            <p class="text-xs text-green-700 font-medium"><i class="fa-solid fa-circle-check mr-1"></i>Tất cả phòng khám đã hoàn tất. Sẵn sàng kết luận!</p>
                        @else
                            <p class="text-xs text-amber-700"><i class="fa-solid fa-clock mr-1"></i>Đang chờ kết quả từ các phòng cận lâm sàng...</p>
                        @endif
                    </div>
                @endif

                <div class="space-y-2">
                    <a href="{{ route('doctor.clinical-visits.show', $appointment->id) }}"
                       class="w-full flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 rounded-lg text-sm font-semibold transition-colors">
                        <i class="fa-solid fa-microscope"></i> Giám sát lâm sàng & Chỉ định
                    </a>

                    @if ($hasRecord)
                        <a href="{{ route('doctor.medical-records.show', $appointment->medicalRecord->id) }}"
                           class="w-full flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white py-2.5 rounded-lg text-sm font-semibold transition-colors">
                            <i class="fa-solid fa-file-medical"></i> Xem / Sửa hồ sơ bệnh án
                        </a>
                    @else
                        <a href="{{ route('doctor.medical-records.create', $appointment->id) }}"
                           class="w-full flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white py-2.5 rounded-lg text-sm font-semibold transition-colors">
                            <i class="fa-solid fa-file-circle-plus"></i> Ghi kết luận bệnh án
                        </a>
                    @endif

                    @if ($canComplete)
                        <form action="{{ route('doctor.appointments.update-status', $appointment->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="completed">
                            <button type="submit"
                                onclick="return confirm('Bạn xác nhận hoàn thành buổi khám này?')"
                                class="w-full flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white py-2.5 rounded-lg text-sm font-semibold transition-colors">
                                <i class="fa-solid fa-circle-check"></i> Hoàn thành khám
                            </button>
                        </form>
                    @else
                        <div class="text-xs text-center text-gray-400 italic pt-1">
                            @if (!$hasRecord)
                                Cần ghi kết luận bệnh án trước khi hoàn thành.
                            @elseif (!$allSubsDone && $totalSubs > 0)
                                Còn {{ $totalSubs - $completedSubs }} phòng cận lâm sàng chưa xong.
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Huỷ/Vắng --}}
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <form action="{{ route('doctor.appointments.update-status', $appointment->id) }}" method="POST" class="flex gap-2">
                        @csrf
                        <select name="status" class="flex-1 text-xs border border-gray-300 rounded-lg px-2 py-1.5 outline-none bg-white">
                            <option value="cancelled">Huỷ lịch hẹn</option>
                            <option value="absent">Vắng mặt</option>
                        </select>
                        <textarea name="reason" rows="1" placeholder="Lý do..." class="flex-1 text-xs border border-gray-300 rounded-lg px-2 py-1.5 outline-none resize-none"></textarea>
                        <button type="submit" class="text-xs bg-red-100 hover:bg-red-200 text-red-700 px-3 py-1.5 rounded-lg transition-colors">Xác nhận</button>
                    </form>
                </div>

            @elseif ($appointment->status === 'completed')
                {{-- Đã hoàn thành → Xem kết quả --}}
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                    <p class="text-sm text-green-700 font-medium"><i class="fa-solid fa-circle-check mr-1"></i>Buổi khám đã hoàn thành.</p>
                </div>
                <div class="space-y-2">
                    <a href="{{ route('doctor.clinical-visits.show', $appointment->id) }}"
                       class="w-full flex items-center justify-center gap-2 bg-indigo-100 hover:bg-indigo-200 text-indigo-700 py-2 rounded-lg text-sm font-medium transition-colors">
                        <i class="fa-solid fa-microscope"></i> Xem giám sát lâm sàng
                    </a>
                    @if ($appointment->medicalRecord)
                        <a href="{{ route('doctor.medical-records.show', $appointment->medicalRecord->id) }}"
                           class="w-full flex items-center justify-center gap-2 bg-emerald-100 hover:bg-emerald-200 text-emerald-700 py-2 rounded-lg text-sm font-medium transition-colors">
                            <i class="fa-solid fa-file-medical"></i> Xem hồ sơ bệnh án
                        </a>
                    @endif
                </div>

            @else
                {{-- Trạng thái pending / cancelled / absent → dropdown generic --}}
                <form action="{{ route('doctor.appointments.update-status', $appointment->id) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm text-gray-500 mb-1">Cập nhật thành:</label>
                        <select name="status" class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">
                            <option value="pending" {{ $appointment->status === 'pending' ? 'selected' : '' }}>Đã tiếp nhận</option>
                            <option value="checked_in" {{ $appointment->status === 'checked_in' ? 'selected' : '' }}>Đã check-in</option>
                            <option value="cancelled" {{ $appointment->status === 'cancelled' ? 'selected' : '' }}>Đã huỷ</option>
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
            @endif
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
    
    <!-- Lịch sử Thanh toán -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
            <i class="fa-solid fa-receipt text-green-500"></i>
            <h3 class="text-lg font-bold text-gray-900">Lịch sử thanh toán</h3>
        </div>
        <div class="p-6">
            @if ($appointment->payments->isEmpty())
                <div class="text-center py-8 text-gray-500">
                    <div class="mb-3"><i class="fa-solid fa-receipt text-4xl text-gray-300"></i></div>
                    <p>Chưa có giao dịch thanh toán nào.</p>
                </div>
            @else
                @php
                    $totalPaid = $appointment->payments->where('status', 'paid')->sum('amount');
                    $totalPending = $appointment->payments->where('status', 'pending')->sum('amount');
                @endphp
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="bg-green-50 rounded-lg p-4 text-center border border-green-100">
                        <div class="text-xs text-green-600 font-medium uppercase mb-1">Đã thanh toán</div>
                        <div class="text-lg font-bold text-green-700">{{ number_format($totalPaid, 0, ',', '.') }}đ</div>
                    </div>
                    <div class="bg-yellow-50 rounded-lg p-4 text-center border border-yellow-100">
                        <div class="text-xs text-yellow-600 font-medium uppercase mb-1">Chờ thanh toán</div>
                        <div class="text-lg font-bold text-yellow-700">{{ number_format($totalPending, 0, ',', '.') }}đ</div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Mã GD</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-500">Số tiền</th>
                                <th class="px-4 py-3 text-center font-medium text-gray-500">Phương thức</th>
                                <th class="px-4 py-3 text-center font-medium text-gray-500">Trạng thái</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Thời gian</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($appointment->payments->sortByDesc('created_at') as $payment)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <span class="font-mono text-xs text-gray-700">{{ $payment->transaction_code ?? '—' }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-900">
                                        {{ number_format($payment->amount, 0, ',', '.') }}đ
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @php
                                            $methodLabel = match($payment->method) {
                                                'cash' => ['Tiền mặt', 'bg-emerald-100 text-emerald-700'],
                                                'bank_transfer' => ['Chuyển khoản', 'bg-blue-100 text-blue-700'],
                                                'qr_code' => ['QR Code', 'bg-purple-100 text-purple-700'],
                                                default => [$payment->method, 'bg-gray-100 text-gray-700'],
                                            };
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $methodLabel[1] }}">{{ $methodLabel[0] }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @php
                                            $statusLabel = match($payment->status) {
                                                'paid' => ['Đã thanh toán', 'bg-green-100 text-green-700'],
                                                'pending' => ['Chờ thanh toán', 'bg-yellow-100 text-yellow-700'],
                                                'refunded' => ['Hoàn tiền', 'bg-red-100 text-red-700'],
                                                'failed' => ['Thất bại', 'bg-red-100 text-red-700'],
                                                default => [$payment->status, 'bg-gray-100 text-gray-700'],
                                            };
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusLabel[1] }}">{{ $statusLabel[0] }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-500 text-xs">
                                        {{ $payment->paid_at ? $payment->paid_at->format('d/m/Y H:i') : ($payment->created_at ? $payment->created_at->format('d/m/Y H:i') : '—') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
    
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
