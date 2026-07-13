<x-layouts.receptionist>
    <x-slot:title>Chi tiết Lượt khám</x-slot:title>

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Chi tiết Bệnh án #{{ $visit->id }}</h2>
            <p class="text-gray-500 mt-1">Giám sát thông tin khám, chẩn đoán và đơn thuốc (Chỉ xem)</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('receptionist.clinical-visits.index') }}"
                class="bg-white border border-gray-350 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg text-sm font-semibold transition-colors flex items-center shadow-sm">
                <i class="fa-solid fa-arrow-left mr-2 text-gray-400"></i> Quay lại danh sách
            </a>
            <button onclick="window.print()"
                class="bg-gray-900 hover:bg-gray-800 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors flex items-center shadow-sm">
                <i class="fa-solid fa-print mr-2 text-gray-400"></i> In hồ sơ
            </button>
        </div>
    </div>

    <!-- Alert for Payment Status -->
    @if ($visit->payment_status === 'pending')
        <div class="bg-orange-50 text-orange-850 p-4 rounded-xl mb-6 border border-orange-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-start gap-3">
                <i class="fa-solid fa-circle-exclamation text-orange-500 mt-0.5 text-lg"></i>
                <div>
                    <h4 class="font-bold text-sm text-orange-900">Chưa thanh toán</h4>
                    <p class="text-sm mt-0.5">Lượt khám này đang ghi nhận khoản phí
                        <strong>{{ number_format($visit->payment_amount, 0, ',', '.') }}đ</strong> nhưng chưa được hoàn thành thanh toán.
                    </p>
                </div>
            </div>
            <a href="{{ route('receptionist.payments.create', $visit->id) }}"
                class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-lg text-sm font-semibold transition-colors flex items-center justify-center shrink-0 shadow-sm">
                <i class="fa-solid fa-credit-card mr-2"></i> Thu tiền ngay
            </a>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Cột Trái: Thông tin hành chính & Lượt khám -->
        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 flex items-center justify-between">
                    <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider"><i
                            class="fa-solid fa-circle-info text-emerald-500 mr-2"></i>Thông tin Lượt khám</h3>
                    @if ($visit->status === 'waiting')
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-yellow-50 text-yellow-800 border border-yellow-100">Đang chờ</span>
                    @elseif($visit->status === 'in_progress')
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-800 border border-blue-100">Đang khám</span>
                    @elseif($visit->status === 'completed')
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-50 text-green-800 border border-green-100">Hoàn thành</span>
                    @elseif($visit->status === 'refused')
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-800 border border-red-100">Từ chối</span>
                    @elseif($visit->status === 'redirected')
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-50 text-gray-800 border border-gray-100">Chuyển viện</span>
                    @endif
                </div>
                <div class="p-6 space-y-4 text-sm">
                    <div>
                        <span class="text-gray-500 block mb-1 text-xs font-semibold uppercase tracking-wider">Mã Lịch Hẹn</span>
                        <a href="{{ route('receptionist.appointments.show', $visit->appointment_id) }}"
                            class="font-bold text-emerald-600 hover:underline">{{ $visit->appointment->appointment_code ?? '#' . $visit->appointment_id }}</a>
                    </div>
                    <div>
                        <span class="text-gray-500 block mb-1 text-xs font-semibold uppercase tracking-wider">Bệnh nhân</span>
                        <span
                            class="font-semibold text-gray-900">{{ $visit->appointment->patientProfile->full_name ?? '—' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block mb-1 text-xs font-semibold uppercase tracking-wider">Bác sĩ phụ trách</span>
                        <span class="font-medium text-gray-900">{{ $visit->doctorProfile->user->name ?? '—' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block mb-1 text-xs font-semibold uppercase tracking-wider">Phòng khám</span>
                        <span class="font-medium text-gray-900">{{ $visit->room->name ?? '—' }}</span>
                    </div>
                    <hr class="border-gray-100">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-gray-500 block mb-1 text-xs font-semibold uppercase tracking-wider">Bắt đầu khám</span>
                            <span
                                class="text-gray-900 font-medium">{{ $visit->started_at ? $visit->started_at->format('H:i - d/m/Y') : '—' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 block mb-1 text-xs font-semibold uppercase tracking-wider">Kết thúc khám</span>
                            <span
                                class="text-gray-900 font-medium">{{ $visit->completed_at ? $visit->completed_at->format('H:i - d/m/Y') : '—' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thanh toán -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4">
                    <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider"><i
                            class="fa-solid fa-credit-card text-emerald-500 mr-2"></i>Thanh toán</h3>
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
                            <span class="font-bold text-orange-600">Chưa thanh toán</span>
                        @elseif($visit->payment_status === 'paid')
                            <span class="font-bold text-emerald-600">Đã thanh toán</span>
                        @elseif($visit->payment_status === 'waived')
                            <span class="font-bold text-gray-600">Miễn phí</span>
                        @endif
                    </div>
                    @if ($visit->payment_status === 'paid')
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Hình thức</span>
                            <span
                                class="font-semibold text-gray-900 text-transform: uppercase">{{ $visit->payment_method ?? '—' }}</span>
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
                <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4">
                    <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider"><i
                            class="fa-solid fa-stethoscope text-emerald-500 mr-2"></i>Ghi nhận Lâm sàng</h3>
                </div>
                <div class="p-6">
                    @if ($visit->findings)
                        <div class="prose prose-sm max-w-none text-gray-800 whitespace-pre-wrap font-medium">{{ $visit->findings }}</div>
                    @else
                        <p class="text-gray-400 italic text-sm">Chưa có ghi nhận lâm sàng từ bác sĩ.</p>
                    @endif
                </div>
            </div>

            <!-- Hồ sơ bệnh án (Chẩn đoán) -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 flex justify-between items-center">
                    <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider"><i class="fa-solid fa-file-medical text-red-500 mr-2"></i>Hồ sơ Bệnh án & Chẩn đoán</h3>
                    @if ($medicalRecord)
                        <span class="text-xs text-gray-500 font-mono">ID: #{{ $medicalRecord->id }}</span>
                    @endif
                </div>
                <div class="p-6">
                    @if ($medicalRecord)
                        <div class="space-y-4">
                            <div>
                                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Chẩn đoán</h4>
                                <p class="text-gray-900 bg-gray-50 p-4 rounded-lg text-sm border border-gray-100 font-medium">
                                    {{ $medicalRecord->diagnosis }}</p>
                            </div>

                            @if ($medicalRecord->icd10_code)
                                <div>
                                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Mã ICD-10</h4>
                                    <span
                                        class="px-2.5 py-1 bg-blue-50 text-blue-700 rounded text-sm font-mono border border-blue-100 font-semibold">{{ $medicalRecord->icd10_code }}</span>
                                </div>
                            @endif

                            @if ($medicalRecord->advice)
                                <div>
                                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Lời khuyên Bác sĩ</h4>
                                    <p class="text-gray-800 text-sm italic whitespace-pre-wrap font-medium">
                                        {{ $medicalRecord->advice }}</p>
                                </div>
                            @endif

                            <div class="grid grid-cols-2 gap-4 mt-4 bg-gray-50 p-4 rounded-lg border border-gray-100">
                                <div>
                                    <span class="block text-xs text-gray-500 mb-1">Hướng điều trị</span>
                                    <span class="font-semibold text-gray-900 text-sm">
                                        {{ $medicalRecord->treatment_result === 'outpatient' ? 'Ngoại trú' : ($medicalRecord->treatment_result === 'admitted' ? 'Nhập viện' : 'Theo dõi') }}
                                    </span>
                                </div>
                                <div>
                                    <span class="block text-xs text-gray-500 mb-1">Ngày tái khám</span>
                                    <span
                                        class="font-semibold text-gray-900 text-sm">{{ $medicalRecord->followup_date ? $medicalRecord->followup_date->format('d/m/Y') : 'Không hẹn' }}</span>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fa-regular fa-folder-open text-4xl text-gray-305 mb-3"></i>
                            <p class="text-gray-500 text-sm font-medium">Chưa có Hồ sơ bệnh án được lập cho lượt khám này.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Đơn thuốc -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4">
                    <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider"><i class="fa-solid fa-pills text-purple-500 mr-2"></i>Đơn thuốc</h3>
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
                                            class="px-6 py-3 text-left text-xs font-bold text-gray-500 G uppercase tracking-wider w-12">
                                            STT</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            Tên thuốc</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-24">
                                            Số lượng</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            Cách dùng</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200 text-sm">
                                    @foreach ($items as $index => $item)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-gray-500 font-mono">{{ $index + 1 }}</td>
                                            <td class="px-6 py-4 font-semibold text-gray-900">{{ $item['name'] ?? '—' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-gray-700 font-medium">
                                                {{ $item['quantity'] ?? '0' }} {{ $item['unit'] ?? 'viên' }}
                                            </td>
                                            <td class="px-6 py-4 text-gray-700 text-xs font-medium">{{ $item['usage'] ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="p-6 text-center text-gray-500 text-sm font-medium">Đơn thuốc trống.</div>
                        @endif

                        @if ($prescription->general_note)
                            <div class="p-6 bg-yellow-50/50 border-t border-yellow-100">
                                <h4 class="text-xs font-bold text-yellow-800 uppercase tracking-wider mb-1"><i
                                        class="fa-solid fa-triangle-exclamation mr-1"></i>Lưu ý khi dùng thuốc</h4>
                                <p class="text-yellow-900 text-sm font-medium">{{ $prescription->general_note }}</p>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-8">
                            <i class="fa-solid fa-prescription text-4xl text-gray-305 mb-3"></i>
                            <p class="text-gray-500 text-sm font-medium">Chưa có Đơn thuốc được kê cho bệnh nhân.</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-layouts.receptionist>
