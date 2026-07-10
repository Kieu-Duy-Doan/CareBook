<x-layouts.doctor title="Giám sát lâm sàng">
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Giám sát lâm sàng & Thanh toán</h2>
            <p class="text-gray-500 mt-1">Danh sách bệnh nhân đang trong quá trình khám lâm sàng</p>
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

    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6">
        <form action="{{ route('doctor.clinical-visits.index') }}" method="GET" class="flex gap-4 items-end">
            <div class="flex-1">
                <label class="block text-xs font-medium text-gray-500 mb-1">Tìm kiếm bệnh nhân hoặc mã LH</label>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Mã LH, Tên bệnh nhân..."
                    class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">
            </div>
            <div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors">
                    Tìm kiếm
                </button>
                <a href="{{ route('doctor.clinical-visits.index') }}" class="ml-2 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    Đặt lại
                </a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mã LH / Bệnh nhân</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ngày khám</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Số lần khám</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Trạng thái thanh toán</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($appointments as $appt)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-mono text-sm text-blue-600 font-medium">{{ $appt->appointment_code }}</div>
                                <div class="font-bold text-gray-900 mt-1">{{ $appt->patientProfile->full_name ?? '—' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                {{ $appt->appointment_date ? $appt->appointment_date->format('d/m/Y') : '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ $appt->clinicalVisits->count() }} phòng khám</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @php
                                    $totalAmount = $appt->clinicalVisits->sum('payment_amount');
                                    $paidAmount = $appt->payments->sum('amount');
                                @endphp
                                @if ($totalAmount > 0 && $paidAmount >= $totalAmount)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                        Đã thanh toán
                                    </span>
                                @elseif($totalAmount == 0)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200">
                                        Miễn phí
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 border border-yellow-200">
                                        Chưa thanh toán
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('doctor.clinical-visits.show', $appt->id) }}" class="text-blue-600 hover:text-blue-800 bg-blue-50 px-3 py-1.5 rounded transition">
                                    Xem chi tiết <i class="fa-solid fa-arrow-right ml-1"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                <div class="h-16 w-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fa-solid fa-microscope text-2xl text-gray-400"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900">Không có dữ liệu</h3>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($appointments->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-white">
                {{ $appointments->links() }}
            </div>
        @endif
    </div>
</x-layouts.doctor>
