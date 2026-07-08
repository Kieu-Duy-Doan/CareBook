<x-layouts.receptionist title="Chi tiết Bệnh án">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Chi tiết Bệnh án #{{ $visit->id }}</h2>
            <p class="text-gray-500 mt-1">Giám sát thông tin khám, chẩn đoán và đơn thuốc (Chỉ xem)</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('receptionist.clinical-visits.index') }}"
                class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                <i class="fa-solid fa-arrow-left mr-2"></i> Quay lại danh sách
            </a>
            <button onclick="window.print()"
                class="bg-gray-900 hover:bg-gray-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                <i class="fa-solid fa-print mr-2"></i> In hồ sơ
            </button>
        </div>
    </div>

    <!-- Alert for Payment Status -->
    @if ($visit->payment_status === 'pending')
        <div class="bg-orange-50 text-orange-800 p-4 rounded-lg mb-6 border border-orange-200 flex items-start gap-3">
            <i class="fa-solid fa-circle-exclamation text-orange-500 mt-0.5"></i>
            <div>
                <h4 class="font-bold text-sm">Chưa thanh toán</h4>
                <p class="text-sm mt-1">Lượt khám này đang ghi nhận khoản phí
                    <strong>{{ number_format($visit->payment_amount, 0, ',', '.') }}đ</strong> nhưng chưa được thanh
                    toán tại quầy.
                </p>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Cột Trái: Thông tin hành chính & Lượt khám -->
        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="border-b border-gray-100 bg-gray-50 px-6 py-4 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900"><i
                            class="fa-solid fa-circle-info text-blue-500 mr-2"></i>Thông tin Lượt khám</h3>
                    @if ($visit->status === 'waiting')
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-yellow-100 text-yellow-800">Đang
                            chờ</span>
                    @elseif($visit->status === 'in_progress')
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-blue-100 text-blue-800">Đang
                            khám</span>
                    @elseif($visit->status === 'completed')
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-green-100 text-green-800">Hoàn
                            thành</span>
                    @endif
                </div>
                <div class="p-6 space-y-4 text-sm">
                    <div>
                        <span class="text-gray-500 block mb-1">Mã Lịch Hẹn</span>
                        <a href="{{ route('receptionist.appointments.show', $visit->appointment_id) }}"
                            class="font-bold text-blue-600 hover:underline">{{ $visit->appointment->appointment_code ?? '#' . $visit->appointment_id }}</a>
                    </div>
                    <div>
                        <span class="text-gray-500 block mb-1">Bệnh nhân</span>
                        <span
                            class="font-medium text-gray-900">{{ $visit->appointment->patientProfile->full_name ?? '—' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block mb-1">Bác sĩ phụ trách</span>
                        <span class="font-medium text-gray-900">{{ $visit->doctorProfile->user->name ?? '—' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block mb-1">Phòng khám</span>
                        <span class="font-medium text-gray-900">{{ $visit->room->name ?? '—' }}</span>
                    </div>
                    <hr class="border-gray-100">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-gray-500 block mb-1 text-xs">Bắt đầu khám</span>
                            <span
                                class="text-gray-900">{{ $visit->started_at ? $visit->started_at->format('H:i - d/m/Y') : '—' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 block mb-1 text-xs">Kết thúc khám</span>
                            <span
                                class="text-gray-900">{{ $visit->completed_at ? $visit->completed_at->format('H:i - d/m/Y') : '—' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thanh toán -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="border-b border-gray-100 bg-gray-50 px-6 py-4">
                    <h3 class="font-semibold text-gray-900"><i
                            class="fa-solid fa-credit-card text-green-500 mr-2"></i>Thanh toán</h3>
                </div>
                <div class="p-6 space-y-4 text-sm">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Chi phí khám</span>
                        <span
                            class="font-bold text-lg text-gray-900">{{ number_format($visit->payment_amount, 0, ',', '.') }}đ</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Trạng thái</span>
                        @if ($visit->payment_status === 'pending')
                            <span class="font-medium text-orange-600">Chưa thanh toán</span>
                        @elseif($visit->payment_status === 'paid')
                            <span class="font-medium text-green-600">Đã thanh toán</span>
                        @elseif($visit->payment_status === 'waived')
                            <span class="font-medium text-gray-600">Miễn phí</span>
                        @endif
                    </div>
                    @if ($visit->payment_status === 'paid')
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Hình thức</span>
                            <span
                                class="font-medium text-gray-900 text-transform: uppercase">{{ $visit->payment_method ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Thu ngân</span>
                            <span class="font-medium text-gray-900">{{ $visit->collectedBy->name ?? '—' }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Cột Phải: Chuyên môn y tế -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Ghi nhận lâm sàng -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="border-b border-gray-100 bg-gray-50 px-6 py-4">
                    <h3 class="font-semibold text-gray-900"><i
                            class="fa-solid fa-stethoscope text-purple-500 mr-2"></i>Ghi nhận Lâm sàng</h3>
                </div>
                <div class="p-6">
                    @if ($visit->findings)
                        <div class="prose prose-sm max-w-none text-gray-800 whitespace-pre-wrap">{{ $visit->findings }}
                        </div>
                    @else
                        <p class="text-gray-400 italic text-sm">Chưa có ghi nhận lâm sàng.</p>
                    @endif
                </div>
            </div>

            <!-- Hồ sơ bệnh án (Chẩn đoán) -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="border-b border-gray-100 bg-gray-50 px-6 py-4 flex justify-between items-center">
                    <h3 class="font-semibold text-gray-900"><i class="fa-solid fa-file-medical text-red-500 mr-2"></i>Hồ
                        sơ Bệnh án & Chẩn đoán</h3>
                    @if ($medicalRecord)
                        <span class="text-xs text-gray-500">ID: #{{ $medicalRecord->id }}</span>
                    @endif
                </div>
                <div class="p-6">
                    @if ($medicalRecord)
                        <div class="space-y-4">
                            <div>
                                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Chẩn đoán</h4>
                                <p class="text-gray-900 bg-gray-50 p-4 rounded-lg text-sm border border-gray-100">
                                    {{ $medicalRecord->diagnosis }}</p>
                            </div>

                            @if ($medicalRecord->icd10_code)
                                <div>
                                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Mã ICD-10
                                    </h4>
                                    <span
                                        class="px-2 py-1 bg-blue-50 text-blue-700 rounded text-sm font-mono border border-blue-100">{{ $medicalRecord->icd10_code }}</span>
                                </div>
                            @endif

                            @if ($medicalRecord->advice)
                                <div>
                                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Lời khuyên
                                        Bác sĩ</h4>
                                    <p class="text-gray-800 text-sm italic whitespace-pre-wrap">
                                        {{ $medicalRecord->advice }}</p>
                                </div>
                            @endif

                            <div class="grid grid-cols-2 gap-4 mt-4 bg-gray-50 p-4 rounded-lg border border-gray-100">
                                <div>
                                    <span class="block text-xs text-gray-500 mb-1">Hướng điều trị</span>
                                    <span class="font-medium text-gray-900 text-sm">
                                        {{ $medicalRecord->treatment_result === 'outpatient' ? 'Ngoại trú' : ($medicalRecord->treatment_result === 'admitted' ? 'Nhập viện' : 'Theo dõi') }}
                                    </span>
                                </div>
                                <div>
                                    <span class="block text-xs text-gray-500 mb-1">Ngày tái khám</span>
                                    <span
                                        class="font-medium text-gray-900 text-sm">{{ $medicalRecord->followup_date ? $medicalRecord->followup_date->format('d/m/Y') : 'Không hẹn' }}</span>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fa-regular fa-folder-open text-4xl text-gray-300 mb-3"></i>
                            <p class="text-gray-500 text-sm">Chưa có Hồ sơ bệnh án được lập cho lượt khám này.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Đơn thuốc -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="border-b border-gray-100 bg-gray-50 px-6 py-4">
                    <h3 class="font-semibold text-gray-900"><i class="fa-solid fa-pills text-teal-500 mr-2"></i>Đơn
                        thuốc</h3>
                </div>
                <div class="p-0">
                    @if ($prescription)
                        @php
                            $items = is_string($prescription->items)
                                ? json_decode($prescription->items, true)
                                : $prescription->items;
                        @endphp

                        @if (!empty($items) && is_array($items))
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-12">
                                            STT</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Tên thuốc</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Số lượng</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Cách dùng</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200 text-sm">
                                    @foreach ($items as $index => $item)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ $index + 1 }}
                                            </td>
                                            <td class="px-6 py-4 font-medium text-gray-900">{{ $item['name'] ?? '—' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-gray-700">
                                                {{ $item['quantity'] ?? '0' }} {{ $item['unit'] ?? 'viên' }}
                                            </td>
                                            <td class="px-6 py-4 text-gray-700 text-xs">{{ $item['usage'] ?? '—' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="p-6 text-center text-gray-500 text-sm">Đơn thuốc trống.</div>
                        @endif

                        @if ($prescription->general_note)
                            <div class="p-6 bg-yellow-50 border-t border-yellow-100">
                                <h4 class="text-xs font-bold text-yellow-800 uppercase tracking-wider mb-1"><i
                                        class="fa-solid fa-triangle-exclamation mr-1"></i>Lưu ý khi dùng thuốc</h4>
                                <p class="text-yellow-900 text-sm">{{ $prescription->general_note }}</p>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-8">
                            <i class="fa-solid fa-prescription text-4xl text-gray-300 mb-3"></i>
                            <p class="text-gray-500 text-sm">Chưa có Đơn thuốc được kê.</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-layouts.receptionist>
