<x-layouts.doctor>
    <x-slot:title>Thanh toán - Phòng khám</x-slot:title>

    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Quản lý Thanh toán</h2>
        <p class="text-gray-500 mt-1">Theo dõi các khoản phí từ bệnh nhân của bạn</p>
    </div>

    {{-- Thống kê nhanh --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex items-center gap-4">
            <div class="h-12 w-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-qrcode"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider">Thu QR hôm nay</p>
                <p class="text-2xl font-black text-blue-600">{{ number_format($qrCollectedToday, 0, ',', '.') }}đ</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex items-center gap-4">
            <div class="h-12 w-12 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider">Tổng thu hôm nay</p>
                <p class="text-2xl font-black text-emerald-600">{{ number_format($totalCollectedToday, 0, ',', '.') }}đ</p>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="border-b border-gray-100 flex">
            <a href="{{ route('doctor.payments.index', ['tab' => 'pending']) }}"
               class="px-6 py-4 text-sm font-semibold border-b-2 transition-colors {{ $tab === 'pending' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                <i class="fa-solid fa-clock mr-2"></i>Chờ thanh toán
            </a>
            <a href="{{ route('doctor.payments.index', ['tab' => 'history']) }}"
               class="px-6 py-4 text-sm font-semibold border-b-2 transition-colors {{ $tab === 'history' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                <i class="fa-solid fa-history mr-2"></i>Lịch sử
            </a>

            {{-- Search --}}
            <div class="ml-auto flex items-center gap-3 px-4">
                <form method="GET" action="{{ route('doctor.payments.index') }}" class="flex items-center gap-2">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm mã LH, tên BN..."
                           class="px-3 py-1.5 border border-gray-200 rounded-lg text-sm outline-none focus:border-blue-400 w-52">
                    <input type="date" name="date" value="{{ request('date') }}"
                           class="px-3 py-1.5 border border-gray-200 rounded-lg text-sm outline-none focus:border-blue-400">
                    <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </form>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                    <tr>
                        <th class="py-3 px-5">Mã Lịch hẹn</th>
                        <th class="py-3 px-5">Bệnh nhân</th>
                        <th class="py-3 px-5">Ngày khám</th>
                        <th class="py-3 px-5 text-right">Tổng tiền</th>
                        <th class="py-3 px-5 text-right">Đã thu</th>
                        <th class="py-3 px-5 text-right">Còn lại</th>
                        <th class="py-3 px-5 text-center">Trạng thái</th>
                        <th class="py-3 px-5 text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($appointments as $apt)
                    @php
                        $totalAmt = $apt->clinicalVisits->sum('payment_amount');
                        $paidAmt = $apt->payments->where('status', 'completed')->sum('amount');
                        $remaining = max(0, $totalAmt - $paidAmt);
                        $hasPending = $apt->clinicalVisits->where('payment_status', 'pending')->isNotEmpty();
                    @endphp
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="py-3.5 px-5">
                            <span class="font-mono font-bold text-gray-900 text-xs">{{ $apt->appointment_code }}</span>
                        </td>
                        <td class="py-3.5 px-5">
                            <p class="font-semibold text-gray-900">{{ $apt->patientProfile->full_name }}</p>
                            <p class="text-xs text-gray-400 font-mono">{{ $apt->patientProfile->patient_code }}</p>
                        </td>
                        <td class="py-3.5 px-5 text-gray-600">
                            {{ \Carbon\Carbon::parse($apt->appointment_date)->format('d/m/Y') }}
                        </td>
                        <td class="py-3.5 px-5 text-right font-bold text-gray-900">
                            {{ number_format($totalAmt, 0, ',', '.') }}đ
                        </td>
                        <td class="py-3.5 px-5 text-right font-bold text-emerald-600">
                            {{ number_format($paidAmt, 0, ',', '.') }}đ
                        </td>
                        <td class="py-3.5 px-5 text-right font-bold {{ $remaining > 0 ? 'text-red-600' : 'text-gray-400' }}">
                            {{ number_format($remaining, 0, ',', '.') }}đ
                        </td>
                        <td class="py-3.5 px-5 text-center">
                            @if($remaining <= 0)
                                <span class="inline-flex items-center gap-1 text-xs font-bold bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">
                                    <i class="fa-solid fa-check"></i> Đã thu
                                </span>
                            @elseif($hasPending)
                                <span class="inline-flex items-center gap-1 text-xs font-bold bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">
                                    <i class="fa-solid fa-clock"></i> Chờ thu
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-xs font-bold bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">
                                    Xử lý
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-5 text-center">
                            <div class="flex items-center justify-center gap-2">
                                @if($remaining > 0)
                                <a href="{{ route('doctor.payments.checkout', $apt->id) }}"
                                   class="px-3 py-1.5 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-1">
                                    <i class="fa-solid fa-qrcode"></i> QR
                                </a>
                                @endif
                                <a href="{{ route('doctor.payments.show', $apt->id) }}"
                                   class="px-3 py-1.5 bg-white border border-gray-200 text-gray-700 text-xs font-medium rounded-lg hover:bg-gray-50 transition-colors">
                                    Chi tiết
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-16 text-center text-gray-400">
                            <i class="fa-solid fa-file-invoice-dollar text-4xl mb-3 opacity-30"></i>
                            <p class="text-sm">Không có hóa đơn nào trong danh sách này.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($appointments->hasPages())
        <div class="p-4 border-t border-gray-100">
            {{ $appointments->links() }}
        </div>
        @endif
    </div>
</x-layouts.doctor>
