<x-layouts.doctor title="Lịch sử khám bệnh nhân">
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Lịch sử khám</h2>
            <p class="text-gray-500 mt-1">Tra cứu danh sách các lượt khám đã hoàn thành</p>
        </div>
    </div>

    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6">
        <form action="{{ route('doctor.patient-history.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Mã lịch khám</label>
                <input type="text" name="appointment_code" value="{{ request('appointment_code') }}"
                    placeholder="VD: LH12345"
                    class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Tên bệnh nhân</label>
                <input type="text" name="patient_name" value="{{ request('patient_name') }}"
                    placeholder="Nhập tên bệnh nhân..."
                    class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Từ ngày</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">
            </div>
            <div class="flex gap-2 items-end">
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Đến ngày</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                        class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 h-[38px] flex items-center justify-center rounded-lg text-sm font-medium transition-colors">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
                <a href="{{ route('doctor.patient-history.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 h-[38px] flex items-center justify-center rounded-lg text-sm font-medium transition-colors">
                    <i class="fa-solid fa-rotate-right"></i>
                </a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-500">
                <thead class="bg-gray-50 text-xs text-gray-700 uppercase">
                    <tr>
                        <th class="px-6 py-4 font-medium">Mã lịch khám</th>
                        <th class="px-6 py-4 font-medium">Bệnh nhân</th>
                        <th class="px-6 py-4 font-medium">Ngày thực hiện</th>
                        <th class="px-6 py-4 font-medium">Phòng/Dịch vụ</th>
                        @if($doctorProfile->doctor_type === 'paraclinical')
                            <th class="px-6 py-4 font-medium">Kết quả</th>
                        @endif
                        <th class="px-6 py-4 font-medium text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($items as $item)
                        @php
                            if ($doctorProfile->doctor_type === 'clinical') {
                                $patient = $item->patientProfile;
                                $date = $item->appointment_date ? $item->appointment_date->format('d/m/Y') : '—';
                                $code = $item->appointment_code;
                                $room = $item->room ? $item->room->name : 'Phòng khám';
                            } else {
                                $patient = $item->appointment->patientProfile;
                                $date = $item->completed_at ? $item->completed_at->format('d/m/Y H:i') : '—';
                                $code = $item->appointment->appointment_code;
                                $room = $item->room ? $item->room->name : 'Phòng cận lâm sàng';
                            }
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-bold text-gray-900">{{ $code }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-bold text-sm flex-shrink-0">
                                        {{ substr($patient->full_name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-gray-900">{{ $patient->full_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $patient->phone ?? '—' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 font-medium">{{ $date }}</td>
                            <td class="px-6 py-4">{{ $room }}</td>
                            @if($doctorProfile->doctor_type === 'paraclinical')
                                <td class="px-6 py-4">
                                    <span class="line-clamp-1 max-w-[200px]" title="{{ $item->findings }}">{{ $item->findings ?: 'Đã có kết quả' }}</span>
                                </td>
                            @endif
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('doctor.patient-history.show', $item->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg text-sm font-medium transition-colors">
                                    <i class="fa-regular fa-eye"></i> Xem
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $doctorProfile->doctor_type === 'paraclinical' ? 6 : 5 }}" class="px-6 py-12 text-center text-gray-500">
                                <i class="fa-solid fa-folder-open text-4xl text-gray-300 mb-4 block"></i>
                                <h3 class="text-lg font-medium text-gray-900">Không tìm thấy lượt khám nào</h3>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($items->hasPages())
        <div class="mt-6">
            {{ $items->links() }}
        </div>
    @endif
</x-layouts.doctor>
