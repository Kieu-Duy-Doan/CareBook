<x-layouts.admin title="Quản lý Hoàn tiền">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Yêu cầu Hoàn tiền</h2>
            <p class="text-gray-500 mt-1">Duyệt và quản lý các yêu cầu hoàn tiền bệnh nhân</p>
        </div>
        <a href="{{ route('admin.payments.dashboard') }}" class="text-blue-600 text-sm hover:underline flex items-center gap-1">
            <i class="fa-solid fa-arrow-left"></i> Quay lại Dashboard
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
    @endif

    {{-- Filter theo trạng thái --}}
    <div class="flex gap-2 mb-4 flex-wrap">
        @foreach(['all' => 'Tất cả', 'pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'rejected' => 'Từ chối', 'completed' => 'Đã hoàn'] as $val => $label)
            <a href="{{ route('admin.payments.refunds', $val !== 'all' ? ['status' => $val] : []) }}"
               class="px-3 py-1.5 text-sm rounded-full border transition-colors {{ request('status') === $val || ($val === 'all' && !request('status')) ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 border-gray-300 hover:border-blue-400' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bệnh nhân</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Số tiền</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lý do</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Yêu cầu bởi</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Trạng thái</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Thao tác</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($refunds as $r)
                @php $badge = $r->status_badge; @endphp
                <tr class="hover:bg-gray-50" x-data="{ open: false }">
                    <td class="px-6 py-4">
                        <div class="font-medium text-sm text-gray-900">{{ $r->appointment?->patientProfile?->full_name ?? 'N/A' }}</div>
                        <div class="text-xs text-gray-500 font-mono">{{ $r->appointment?->appointment_code }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-red-600 font-bold text-sm">{{ number_format($r->amount) }}đ</span>
                    </td>
                    <td class="px-6 py-4 max-w-xs">
                        <div class="text-sm text-gray-700 truncate" title="{{ $r->reason }}">{{ $r->reason ?: '—' }}</div>
                        <div class="text-xs text-gray-400">{{ $r->created_at->format('d/m/Y H:i') }}</div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-700">{{ $r->requestedBy?->name }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 rounded-full text-xs font-semibold
                            {{ $badge['color'] === 'green' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $badge['color'] === 'yellow' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $badge['color'] === 'red' ? 'bg-red-100 text-red-800' : '' }}
                            {{ $badge['color'] === 'blue' ? 'bg-blue-100 text-blue-800' : '' }}">
                            {{ $badge['label'] }}
                        </span>
                        @if($r->reviewedBy)
                            <div class="text-xs text-gray-400 mt-0.5">{{ $r->reviewedBy->name }} — {{ $r->reviewed_at?->format('d/m') }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($r->status === 'pending')
                            <button @click="open = !open" class="bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-medium px-3 py-1.5 rounded-lg transition-colors">
                                Duyệt / Từ chối
                            </button>
                            <div x-show="open" x-transition class="mt-2 p-3 border border-gray-200 rounded-lg bg-gray-50 min-w-64">
                                <form method="POST" action="{{ route('admin.payments.refunds.review', $r->id) }}">
                                    @csrf
                                    <div class="mb-2">
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Phương thức hoàn</label>
                                        <select name="refund_method" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm">
                                            <option value="cash">Tiền mặt</option>
                                            <option value="bank_transfer">Chuyển khoản</option>
                                        </select>
                                    </div>
                                    <div class="mb-2">
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Ghi chú</label>
                                        <input type="text" name="review_note" placeholder="Lý do..."
                                            class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm">
                                    </div>
                                    <div class="flex gap-2">
                                        <button type="submit" name="action" value="approve"
                                            class="flex-1 bg-green-600 hover:bg-green-700 text-white text-xs font-medium py-1.5 rounded transition-colors">
                                            <i class="fa-solid fa-check"></i> Duyệt
                                        </button>
                                        <button type="submit" name="action" value="reject"
                                            class="flex-1 bg-red-100 hover:bg-red-200 text-red-700 text-xs font-medium py-1.5 rounded transition-colors">
                                            <i class="fa-solid fa-xmark"></i> Từ chối
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @else
                            <span class="text-xs text-gray-400 italic">Đã xử lý</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                        <i class="fa-solid fa-inbox text-4xl mb-3 block text-gray-200"></i>
                        Không có yêu cầu hoàn tiền nào.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t border-gray-100">{{ $refunds->links() }}</div>
    </div>
</x-layouts.admin>
