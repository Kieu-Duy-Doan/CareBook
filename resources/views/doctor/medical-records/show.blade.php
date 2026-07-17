<x-layouts.doctor title="Chi tiết Hồ sơ bệnh án">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center text-sm text-gray-500 mb-2">
                <a href="{{ route('doctor.appointments.index') }}" class="hover:text-blue-600 transition-colors">Lịch hẹn</a>
                <i class="fa-solid fa-chevron-right text-[10px] mx-2"></i>
                <a href="{{ route('doctor.appointments.show', $medical_record->appointment_id) }}" class="hover:text-blue-600 transition-colors">Chi tiết LH: {{ $medical_record->appointment->appointment_code }}</a>
                <i class="fa-solid fa-chevron-right text-[10px] mx-2"></i>
                <span class="text-gray-800 font-medium">Hồ sơ bệnh án</span>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Hồ sơ bệnh án #{{ $medical_record->id }}</h2>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('doctor.appointments.show', $medical_record->appointment_id) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i> Về lịch hẹn
            </a>
            <a href="{{ route('doctor.medical-records.edit', $medical_record->id) }}" class="bg-blue-50 text-blue-700 hover:bg-blue-100 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                <i class="fa-solid fa-pen mr-2"></i> Sửa hồ sơ
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="bg-green-50 text-green-800 p-4 rounded-lg mb-6 flex items-center gap-3 border border-green-200">
            <i class="fa-solid fa-circle-check text-green-500"></i>{{ session('success') }}
        </div>
    @endif
    
    @if (session('error'))
        <div class="bg-red-50 text-red-800 p-4 rounded-lg mb-6 flex items-center gap-3 border border-red-200">
            <i class="fa-solid fa-circle-xmark text-red-500"></i>{{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <!-- Medical Record Details -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-900">Thông tin chẩn đoán</h3>
                    <span class="px-3 py-1 bg-gray-200 text-gray-800 rounded-full text-xs font-semibold">
                        {{ match($medical_record->treatment_result) {
                            'outpatient' => 'Ngoại trú',
                            'admitted' => 'Nhập viện',
                            'monitoring' => 'Theo dõi thêm',
                            default => $medical_record->treatment_result
                        } }}
                    </span>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-6">
                        <div class="md:col-span-2">
                            <span class="block text-sm text-gray-500 mb-1">Chẩn đoán:</span>
                            <div class="text-gray-900 font-medium whitespace-pre-line">{{ $medical_record->diagnosis }}</div>
                        </div>

                        @if($medical_record->icd10_code)
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Mã ICD-10:</span>
                            <div class="text-gray-900 font-medium">{{ $medical_record->icd10_code }}</div>
                        </div>
                        @endif

                        @if($medical_record->followup_date)
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Ngày tái khám:</span>
                            <div class="text-gray-900 font-medium">{{ $medical_record->followup_date->format('d/m/Y') }}</div>
                        </div>
                        @endif

                        @if($medical_record->conclusion)
                        <div class="md:col-span-2 pt-4 border-t border-gray-100">
                            <span class="block text-sm text-gray-500 mb-1">Kết luận:</span>
                            <div class="text-gray-900 whitespace-pre-line">{{ $medical_record->conclusion }}</div>
                        </div>
                        @endif

                        @if($medical_record->advice)
                        <div class="md:col-span-2 pt-4 border-t border-gray-100">
                            <span class="block text-sm text-gray-500 mb-1">Lời khuyên:</span>
                            <div class="text-gray-900 whitespace-pre-line">{{ $medical_record->advice }}</div>
                        </div>
                        @endif

                        @if($medical_record->result_files)
                        <div class="md:col-span-2 pt-4 border-t border-gray-100">
                            <span class="block text-sm text-gray-500 mb-2">Kết quả cận lâm sàng / Tệp đính kèm:</span>
                            <div class="flex flex-wrap gap-3">
                                @foreach($medical_record->result_files as $file)
                                    <a href="{{ Storage::url($file['path']) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 hover:text-blue-600 transition-colors shadow-sm">
                                        <i class="fa-solid fa-file-pdf text-red-500"></i>
                                        {{ $file['name'] }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Prescription Details -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-900">Đơn thuốc (Prescription)</h3>
                    @if($medical_record->prescription)
                        <a href="{{ route('doctor.prescriptions.edit', $medical_record->prescription->id) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            <i class="fa-solid fa-pen"></i> Sửa đơn thuốc
                        </a>
                    @else
                        <a href="{{ route('doctor.prescriptions.create', $medical_record->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                            <i class="fa-solid fa-plus mr-1"></i> Kê đơn thuốc
                        </a>
                    @endif
                </div>
                
                <div class="p-6">
                    @if($medical_record->prescription)
                        @if($medical_record->prescription->diagnosis_note)
                        <div class="mb-4">
                            <span class="block text-sm text-gray-500 mb-1">Ghi chú chẩn đoán:</span>
                            <div class="text-gray-900">{{ $medical_record->prescription->diagnosis_note }}</div>
                        </div>
                        @endif

                        <div class="mb-4">
                            <h4 class="text-md font-semibold text-gray-800 mb-3">Danh sách thuốc:</h4>
                            <div class="border rounded-lg overflow-hidden">
                                <table class="w-full text-left text-sm">
                                    <thead class="bg-gray-50 text-gray-600 border-b">
                                        <tr>
                                            <th class="px-4 py-3">Tên thuốc</th>
                                            <th class="px-4 py-3 w-24 text-center">Số lượng</th>
                                            <th class="px-4 py-3">Liều dùng / HD</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($medical_record->prescription->items as $item)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 font-medium text-gray-900">{{ $item['medicine_name'] ?? '' }}</td>
                                            <td class="px-4 py-3 text-center">{{ $item['quantity'] ?? '' }}</td>
                                            <td class="px-4 py-3">
                                                <div class="text-gray-800">{{ $item['dosage'] ?? '' }}</div>
                                                @if(!empty($item['instructions']))
                                                    <div class="text-xs text-gray-500 mt-1">{{ $item['instructions'] }}</div>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        @if($medical_record->prescription->general_note)
                        <div class="pt-4 border-t border-gray-100">
                            <span class="block text-sm text-gray-500 mb-1">Ghi chú chung:</span>
                            <div class="text-gray-900 whitespace-pre-line">{{ $medical_record->prescription->general_note }}</div>
                        </div>
                        @endif

                        <!-- Form Xóa đơn thuốc -->
                        <div class="mt-6 pt-4 border-t border-gray-100 text-right">
                            <form action="{{ route('doctor.prescriptions.destroy', $medical_record->prescription->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa đơn thuốc này không?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                    <i class="fa-solid fa-trash mr-1"></i> Xóa đơn thuốc
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500">
                            <div class="mb-3 text-4xl text-gray-300"><i class="fa-solid fa-pills"></i></div>
                            <p>Chưa có đơn thuốc nào được kê cho hồ sơ này.</p>
                        </div>
                    @endif
                </div>
            </div>
            
            @if(isset($unpaidAmount) && $unpaidAmount > 0)
            <!-- Nút Thanh Toán -->
            <div class="bg-amber-50 rounded-xl shadow-sm border border-amber-200 overflow-hidden mt-6">
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <i class="fa-solid fa-money-bill-wave text-amber-500 text-2xl"></i>
                        <div>
                            <h3 class="font-bold text-amber-800">Cần thanh toán phí dịch vụ / Thuốc</h3>
                            <p class="text-amber-700 text-sm">Còn nợ: <strong class="text-red-600 text-lg">{{ number_format($unpaidAmount, 0, ',', '.') }}đ</strong></p>
                        </div>
                    </div>
                    
                    <a href="{{ route('doctor.payments.checkout', $medical_record->appointment_id) }}" class="w-full inline-flex justify-center items-center bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg text-sm font-medium transition-colors shadow-sm mb-3">
                        <i class="fa-solid fa-qrcode mr-2 text-lg"></i> Tiến hành Thanh toán QR
                    </a>
                    
                    <div class="text-center text-sm text-amber-700">
                        hoặc <span class="font-semibold">hướng dẫn bệnh nhân ra quầy lễ tân</span> để thanh toán bằng tiền mặt.
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="space-y-6">
            <!-- Patient Info -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-900 border-b border-gray-100 pb-4 mb-4">Bệnh nhân</h3>
                @php $patient = $medical_record->appointment->patientProfile; @endphp
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xl font-bold">
                        {{ mb_substr($patient->full_name, 0, 1) }}
                    </div>
                    <div>
                        <div class="font-bold text-gray-900">{{ $patient->full_name }}</div>
                        <div class="text-sm text-gray-500">{{ $patient->phone }}</div>
                    </div>
                </div>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between border-b border-gray-50 pb-2">
                        <span class="text-gray-500">Mã BN:</span>
                        <span class="font-medium text-gray-900">{{ $patient->patient_code }}</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-50 pb-2">
                        <span class="text-gray-500">Giới tính:</span>
                        <span class="font-medium text-gray-900">{{ $patient->gender_label }}</span>
                    </div>
                    <div class="flex justify-between pb-2">
                        <span class="text-gray-500">Ngày sinh:</span>
                        <span class="font-medium text-gray-900">{{ $patient->dob ? $patient->dob->format('d/m/Y') : '—' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.doctor>
