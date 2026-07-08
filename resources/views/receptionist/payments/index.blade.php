<x-layouts.receptionist>
    <x-slot:title>Lịch sử thanh toán</x-slot:title>

    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Lịch sử thanh toán</h2>
            <p class="text-gray-500 text-sm mt-1">Danh sách lịch sử các giao dịch thanh toán.</p>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <form action="{{ route('receptionist.payments.index') }}" method="GET" class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Tìm kiếm mã lịch hẹn, tên bệnh nhân..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <div class="w-full sm:w-48">
                <select name="payment_status"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">Tất cả trạng thái</option>
                    <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Chưa thanh toán</option>
                    <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Đã thanh toán</option>
                    <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>Thất bại</option>
                    <option value="cancelled" {{ request('payment_status') == 'cancelled' ? 'selected' : '' }}>Đã huỷ</option>
                </select>
            </div>
            <button type="submit"
                class="px-6 py-2 bg-emerald-600 text-white font-medium rounded-lg hover:bg-emerald-700 focus:ring-4 focus:ring-emerald-200 transition-colors">
                Lọc
            </button>
            @if(request()->anyFilled(['search', 'payment_status']))
                <a href="{{ route('receptionist.payments.index') }}"
                    class="px-6 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-colors text-center">
                    Xóa lọc
                </a>
            @endif
        </form>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 border-b border-gray-200 text-gray-700 uppercase text-xs">
                    <tr>
                        <th class="px-6 py-4 font-semibold">Bệnh nhân</th>
                        <th class="px-6 py-4 font-semibold">Bác sĩ khám</th>
                        <th class="px-6 py-4 font-semibold">Thu ngân</th>
                        <th class="px-6 py-4 font-semibold">Số tiền</th>
                        <th class="px-6 py-4 font-semibold">Trạng thái</th>
                        <th class="px-6 py-4 font-semibold">Ngày thanh toán</th>
                        <th class="px-6 py-4 font-semibold text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($payments as $payment)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-900">{{ $payment->appointment->patientProfile->full_name ?? 'N/A' }}</p>
                                <p class="text-xs text-gray-500">{{ $payment->appointment->patientProfile->patient_code ?? '' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                {{ $payment->appointment->doctorProfile->user->full_name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-gray-700">
                                {{ $payment->collectedBy->full_name ?? '--' }}
                            </td>
                            <td class="px-6 py-4 font-semibold text-gray-900">
                                {{ number_format($payment->amount, 0, ',', '.') }} VNĐ
                            </td>
                            <td class="px-6 py-4">
                                @if($payment->status === 'paid')
                                    <span class="px-2.5 py-1 text-xs font-medium bg-emerald-100 text-emerald-700 rounded-full">
                                        Đã thanh toán ({{ ucfirst($payment->payment_method) }})
                                    </span>
                                @elseif($payment->status === 'failed' || $payment->status === 'cancelled')
                                    <span class="px-2.5 py-1 text-xs font-medium bg-red-100 text-red-700 rounded-full">Thất bại/Huỷ</span>
                                @else
                                    <span class="px-2.5 py-1 text-xs font-medium bg-orange-100 text-orange-700 rounded-full">Chưa thanh toán</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-500">
                                {{ $payment->paid_at ? \Carbon\Carbon::parse($payment->paid_at)->format('d/m/Y H:i') : '--' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('receptionist.payments.edit', $payment->id) }}"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-md transition-colors">
                                    <i class="fa-solid fa-eye"></i> Chi tiết
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                Không có dữ liệu thanh toán nào được tìm thấy.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $payments->links() }}
        </div>
    </div>
</x-layouts.receptionist>
