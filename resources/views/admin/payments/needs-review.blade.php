<x-layouts.admin title="Giao dịch cần xử lý">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Giao dịch cần xử lý</h2>
            <p class="text-gray-500 mt-1">Các giao dịch QR bất thường (thiếu/dư tiền, mã không rõ)</p>
        </div>
        <a href="{{ route('admin.payments.dashboard') }}" class="text-blue-600 text-sm hover:underline flex items-center gap-1">
            <i class="fa-solid fa-arrow-left"></i> Quay lại Dashboard
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
    @endif

    {{-- Filter --}}
    <form method="GET" class="flex gap-2 mb-4">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm mã lịch hẹn, tên bệnh nhân..."
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm flex-1 max-w-sm">
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 transition-colors">
            <i class="fa-solid fa-search"></i> Tìm
        </button>
    </form>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bệnh nhân / Lịch hẹn</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Số tiền GD</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ghi chú</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ngày</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Thao tác</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($payments as $p)
                <tr class="hover:bg-gray-50" x-data="{ open: false }">
                    <td class="px-6 py-4">
                        <div class="font-medium text-sm text-gray-900">{{ $p->appointment?->patientProfile?->full_name ?? 'N/A' }}</div>
                        <div class="text-xs text-gray-500 font-mono">{{ $p->appointment?->appointment_code }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-orange-600 font-bold text-sm">{{ number_format($p->amount) }}đ</span>
                        <div class="text-xs text-gray-500">{{ $p->method === 'qr' ? 'QR VietQR' : $p->method }}</div>
                    </td>
                    <td class="px-6 py-4 max-w-xs">
                        <div class="text-sm text-gray-700 truncate" title="{{ $p->note }}">{{ $p->note }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $p->paid_at?->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <button @click="open = !open" class="bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-medium px-3 py-1.5 rounded-lg transition-colors">
                            Xử lý <i class="fa-solid fa-chevron-down ml-1 text-xs"></i>
                        </button>

                        <div x-show="open" x-transition class="mt-2 p-3 border border-gray-200 rounded-lg bg-gray-50 min-w-64">
                            <form method="POST" action="{{ route('admin.payments.resolve-review', $p->id) }}">
                                @csrf
                                <label class="block text-xs font-medium text-gray-600 mb-1">Ghi chú xử lý</label>
                                <input type="text" name="note" placeholder="Lý do..."
                                    class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm mb-2">
                                <div class="flex gap-2">
                                    <button type="submit" name="action" value="approve"
                                        class="flex-1 bg-green-600 hover:bg-green-700 text-white text-xs font-medium py-1.5 rounded transition-colors">
                                        <i class="fa-solid fa-check"></i> Xác nhận
                                    </button>
                                    <button type="submit" name="action" value="create_refund"
                                        class="flex-1 bg-red-100 hover:bg-red-200 text-red-700 text-xs font-medium py-1.5 rounded transition-colors">
                                        <i class="fa-solid fa-rotate-left"></i> Tạo hoàn tiền
                                    </button>
                                </div>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                        <i class="fa-solid fa-circle-check text-4xl mb-3 block text-green-300"></i>
                        Không có giao dịch nào cần xử lý!
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t border-gray-100">{{ $payments->links() }}</div>
    </div>
</x-layouts.admin>
