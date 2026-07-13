<x-layouts.receptionist>
    <x-slot:title>Quản lý Thanh toán & Thu ngân</x-slot:title>

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Quản lý Thanh toán & Thu ngân</h2>
            <p class="text-gray-500 mt-1">Quản lý hoá đơn, doanh thu và thu tiền lượt khám của bệnh nhân</p>
        </div>
    </div>

    <!-- Statistics Dashboard Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
        <!-- Tổng doanh thu hôm nay -->
        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex items-center">
            <div class="h-10 w-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg mr-3.5">
                <i class="fa-solid fa-sack-dollar"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-0.5">Tổng thu hôm nay</p>
                <p class="text-xl font-bold text-gray-950">{{ number_format($totalCollectedToday, 0, ',', '.') }}đ</p>
            </div>
        </div>
        <!-- Thu qua Tiền mặt -->
        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex items-center">
            <div class="h-10 w-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-lg mr-3.5">
                <i class="fa-solid fa-money-bill-wave"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-0.5">Thu Tiền mặt</p>
                <p class="text-xl font-bold text-gray-950">{{ number_format($cashCollectedToday, 0, ',', '.') }}đ</p>
            </div>
        </div>
        <!-- Thu qua QR/PayOS -->
        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex items-center">
            <div class="h-10 w-10 rounded-full bg-purple-50 text-purple-600 flex items-center justify-center text-lg mr-3.5">
                <i class="fa-solid fa-qrcode"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-0.5">Thu PayOS QR</p>
                <p class="text-xl font-bold text-gray-950">{{ number_format($qrCollectedToday, 0, ',', '.') }}đ</p>
            </div>
        </div>
        <!-- Hoá đơn chưa thu hôm nay -->
        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex items-center">
            <div class="h-10 w-10 rounded-full bg-orange-50 text-orange-600 flex items-center justify-center text-lg mr-3.5">
                <i class="fa-solid fa-receipt"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-0.5">Chờ thanh toán hôm nay</p>
                <p class="text-xl font-bold text-gray-950">{{ number_format($pendingAmountToday, 0, ',', '.') }}đ</p>
            </div>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm mb-6">
        <form action="{{ route('receptionist.payments.index') }}" method="GET" class="flex flex-col lg:flex-row gap-4 items-end">
            <div class="w-full lg:w-48">
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Ngày tạo</label>
                <input type="date" name="date" value="{{ request('date') }}"
                    class="block w-full py-2.5 px-3 border border-gray-200 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 text-sm outline-none bg-gray-50/50">
            </div>
            <div class="w-full lg:w-48">
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Trạng thái</label>
                <select name="payment_status"
                    class="block w-full py-2.5 px-3 border border-gray-200 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 text-sm outline-none bg-white">
                    <option value="">Tất cả</option>
                    <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>Chưa thanh toán</option>
                    <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Đã thanh toán</option>
                    <option value="waived" {{ request('payment_status') === 'waived' ? 'selected' : '' }}>Miễn phí</option>
                </select>
            </div>
            <div class="w-full lg:flex-1">
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Tìm kiếm bệnh nhân</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nhập tên bệnh nhân hoặc mã bệnh nhân..."
                    class="block w-full py-2.5 px-3 border border-gray-200 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 text-sm outline-none bg-gray-50/50">
            </div>
            <div class="flex gap-2 shrink-0 w-full lg:w-auto">
                <a href="{{ route('receptionist.payments.index') }}"
                    class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-semibold transition-colors flex-1 lg:flex-none text-center">
                    Đặt lại
                </a>
                <button type="submit"
                    class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold transition-colors flex-1 lg:flex-none">
                    Lọc dữ liệu
                </button>
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Mã Hoá Đơn</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Bệnh nhân</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Dịch vụ & Bác sĩ</th>
                        <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Số tiền</th>
                        <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Trạng thái</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Chi tiết thanh toán</th>
                        <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($visits as $visit)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <!-- Mã Hoá Đơn -->
                            <td class="px-6 py-4 whitespace-nowrap font-mono text-sm font-bold text-gray-700">
                                #INV-{{ str_pad($visit->id, 6, '0', STR_PAD_LEFT) }}
                                <div class="text-[10px] text-gray-400 font-semibold mt-1">Ngày lập: {{ $visit->created_at->format('H:i d/m/Y') }}</div>
                            </td>
                            <!-- Bệnh nhân -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-950">
                                    {{ $visit->appointment->patientProfile->full_name ?? '—' }}
                                </div>
                                <div class="text-xs text-gray-500 mt-1 font-mono">
                                    Mã BN: {{ $visit->appointment->patientProfile->patient_code ?? '—' }}
                                </div>
                            </td>
                            <!-- Dịch vụ & Bác sĩ -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    Khám: {{ $visit->appointment->specialty->name ?? 'Tổng quát' }}
                                </div>
                                <div class="text-xs text-purple-600 font-medium mt-1">
                                    BS: {{ $visit->doctorProfile->user->name ?? '—' }} ({{ $visit->room->name ?? '—' }})
                                </div>
                            </td>
                            <!-- Số tiền -->
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-gray-900">
                                {{ number_format($visit->payment_amount, 0, ',', '.') }}đ
                            </td>
                            <!-- Trạng thái -->
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($visit->payment_status === 'pending')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-orange-50 text-orange-850 border border-orange-100">
                                        Chờ thanh toán
                                    </span>
                                @elseif($visit->payment_status === 'paid')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-850 border border-emerald-100">
                                        Đã thanh toán
                                    </span>
                                @elseif($visit->payment_status === 'waived')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-50 text-gray-850 border border-gray-100">
                                        Miễn phí
                                    </span>
                                @endif
                            </td>
                            <!-- Chi tiết thanh toán -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-650">
                                @if($visit->payment_status === 'paid' || $visit->payment_status === 'waived')
                                    <div class="flex flex-col gap-0.5 text-xs font-medium">
                                        <div>Phương thức: 
                                            <span class="font-bold text-gray-900 uppercase">
                                                @if($visit->payment_method === 'cash') Tiền mặt
                                                @elseif($visit->payment_method === 'qr') PayOS QR
                                                @elseif($visit->payment_method === 'insurance') Bảo hiểm
                                                @elseif($visit->payment_method === 'waived') Miễn phí
                                                @else {{ $visit->payment_method }}
                                                @endif
                                            </span>
                                        </div>
                                        <div>Lúc: <span class="text-gray-900 font-mono">{{ $visit->paid_at ? $visit->paid_at->format('H:i d/m/Y') : '—' }}</span></div>
                                        <div>Thu ngân: <span class="text-gray-900 font-semibold">{{ $visit->collectedBy->name ?? '—' }}</span></div>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400 italic font-medium">Chưa thanh toán</span>
                                @endif
                            </td>
                            <!-- Thao tác -->
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold">
                                @if($visit->payment_status === 'pending')
                                    <a href="{{ route('receptionist.payments.create', $visit->id) }}"
                                        class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm">
                                        <i class="fa-solid fa-credit-card mr-1.5"></i> Thu tiền
                                    </a>
                                @else
                                    <a href="{{ route('receptionist.clinical-visits.show', $visit->id) }}"
                                        class="inline-flex items-center px-3 py-2 bg-gray-50 border border-gray-250 text-gray-700 hover:bg-gray-100 rounded-lg text-xs font-semibold transition-all">
                                        <i class="fa-solid fa-file-lines mr-1.5 text-gray-400"></i> Xem chi tiết
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="h-16 w-16 bg-gray-50 rounded-full flex items-center justify-center mb-4 border border-gray-100">
                                        <i class="fa-solid fa-file-invoice-dollar text-2xl text-gray-400"></i>
                                    </div>
                                    <h3 class="text-lg font-bold text-gray-900">Không tìm thấy hoá đơn nào</h3>
                                    <p class="text-sm mt-1 text-gray-500">Không có dữ liệu hoá đơn thanh toán cho ngày hoặc trạng thái này.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($visits->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-white">
                {{ $visits->links() }}
            </div>
        @endif
    </div>
</x-layouts.receptionist>
