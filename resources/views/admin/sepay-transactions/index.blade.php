<x-layouts.admin title="Giao dịch SePay">
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Giao dịch SePay</h2>
            <p class="text-gray-500 mt-1">Danh sách giao dịch và đối soát từ SePay API</p>
        </div>
        <div class="flex gap-2">
            <form action="{{ route('admin.sepay-transactions.reconcile') }}" method="POST">
                @csrf
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-link"></i> Đối soát lại
                </button>
            </form>
            <form action="{{ route('admin.sepay-transactions.sync') }}" method="POST">
                @csrf
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-cloud-arrow-down"></i> Đồng bộ ngay
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
            {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            {{ session('error') }}
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Tổng tiền vào</p>
            <p class="text-xl font-bold text-green-600">+{{ number_format($stats['total_in']) }}đ</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Đã khớp (Matched)</p>
            <p class="text-xl font-bold text-blue-600">{{ $stats['matched'] }} GD</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Sai số tiền</p>
            <p class="text-xl font-bold text-yellow-600">{{ $stats['mismatch'] }} GD</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Chưa khớp (Unmatched)</p>
            <p class="text-xl font-bold text-red-600">{{ $stats['unmatched'] }} GD</p>
        </div>
    </div>

    {{-- Filter Form --}}
    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm mb-6">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-gray-700 mb-1">Tìm kiếm</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Mã GD, nội dung..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Trạng thái đối soát</label>
                <select name="reconciliation" class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-40">
                    <option value="">Tất cả</option>
                    <option value="matched" {{ request('reconciliation') === 'matched' ? 'selected' : '' }}>Đã khớp</option>
                    <option value="unmatched" {{ request('reconciliation') === 'unmatched' ? 'selected' : '' }}>Chưa khớp</option>
                    <option value="amount_mismatch" {{ request('reconciliation') === 'amount_mismatch' ? 'selected' : '' }}>Sai số tiền</option>
                    <option value="manual" {{ request('reconciliation') === 'manual' ? 'selected' : '' }}>Thủ công</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Từ ngày</label>
                <input type="date" name="from" value="{{ request('from') }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Đến ngày</label>
                <input type="date" name="to" value="{{ request('to') }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded-lg text-sm hover:bg-gray-900 transition-colors">
                    Lọc
                </button>
                <a href="{{ route('admin.sepay-transactions.index') }}" class="ml-2 text-sm text-gray-600 hover:text-gray-900">Xóa lọc</a>
            </div>
        </form>
    </div>

    {{-- Tabs --}}
    <div x-data="{ activeTab: 'transactions' }">
        {{-- Tab Buttons --}}
        <div class="flex gap-1 border-b border-gray-200 mb-4">
            <button
                @click="activeTab = 'transactions'"
                :class="activeTab === 'transactions' ? 'border-b-2 border-blue-600 text-blue-600 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                class="px-5 py-3 text-sm transition-colors flex items-center gap-2">
                <i class="fa-solid fa-list-check"></i> Giao dịch SePay
            </button>
            <button
                @click="activeTab = 'logs'"
                :class="activeTab === 'logs' ? 'border-b-2 border-blue-600 text-blue-600 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                class="px-5 py-3 text-sm transition-colors flex items-center gap-2">
                <i class="fa-solid fa-scroll"></i> Nhật ký Hệ thống
            </button>
        </div>

        {{-- Tab 1: Giao dịch SePay --}}
        <div x-show="activeTab === 'transactions'" x-transition>
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã GD / Ngày</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Số tiền In/Out</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/3">Nội dung chuyển khoản</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Đối soát</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($transactions as $txn)
                            @php $badge = $txn->reconciliation_badge; @endphp
                            <tr class="hover:bg-gray-50 transition-colors" x-data="{ openMatch: false }">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-mono font-medium text-gray-900">{{ $txn->transaction_id }}</div>
                                    <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($txn->transaction_date)->format('d/m/Y H:i:s') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($txn->amount_in > 0)
                                    <div class="text-sm text-green-600 font-bold">+{{ number_format($txn->amount_in) }}đ</div>
                                    @endif
                                    @if($txn->amount_out > 0)
                                    <div class="text-sm text-red-600 font-bold">-{{ number_format($txn->amount_out) }}đ</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 break-words">{{ $txn->transaction_content }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $badge['color'] === 'green' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $badge['color'] === 'yellow' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $badge['color'] === 'blue' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $badge['color'] === 'gray' ? 'bg-gray-100 text-gray-800' : '' }}">
                                        {{ $badge['label'] }}
                                    </span>
                                    @if($txn->matchedPayment)
                                        <div class="text-xs text-gray-500 mt-1">Payment #{{ $txn->matchedPayment->id }}</div>
                                        <div class="text-xs text-gray-400 max-w-[200px] truncate" title="{{ $txn->reconciliation_note }}">{{ $txn->reconciliation_note }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($txn->reconciliation_status !== 'matched')
                                        <button @click="openMatch = !openMatch" class="text-blue-600 hover:text-blue-900 text-xs font-medium px-2 py-1 bg-blue-50 hover:bg-blue-100 rounded">
                                            Khớp thủ công
                                        </button>
                                        <div x-show="openMatch" x-transition class="mt-2 p-3 bg-gray-50 border border-gray-200 rounded min-w-[250px] z-10 absolute right-10 shadow-lg">
                                            <form action="{{ route('admin.sepay-transactions.manual-match', $txn->id) }}" method="POST">
                                                @csrf
                                                <div class="mb-2">
                                                    <label class="block text-xs text-gray-600 mb-1">ID Payment</label>
                                                    <input type="number" name="payment_id" required class="w-full text-sm border-gray-300 rounded px-2 py-1">
                                                </div>
                                                <div class="mb-2">
                                                    <label class="block text-xs text-gray-600 mb-1">Ghi chú</label>
                                                    <input type="text" name="note" class="w-full text-sm border-gray-300 rounded px-2 py-1">
                                                </div>
                                                <div class="flex gap-2">
                                                    <button type="submit" class="bg-blue-600 text-white text-xs px-3 py-1.5 rounded">Khớp</button>
                                                    <button type="button" @click="openMatch = false" class="bg-gray-200 text-gray-700 text-xs px-3 py-1.5 rounded">Hủy</button>
                                                </div>
                                            </form>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                    <i class="fa-solid fa-inbox text-3xl mb-2 block text-gray-300"></i>
                                    Chưa có giao dịch nào phù hợp.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $transactions->links() }}
                </div>
            </div>
        </div>

        {{-- Tab 2: Nhật ký hệ thống --}}
        <div x-show="activeTab === 'logs'" x-transition>
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
                <ul class="divide-y divide-gray-200">
                    @forelse($logs as $log)
                    <li class="px-6 py-4 flex gap-4 hover:bg-gray-50 transition-colors">
                        <div class="text-gray-500 text-sm w-36 shrink-0">
                            {{ $log->created_at->format('d/m/Y H:i') }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-gray-900 text-sm">
                                {{ $log->action_label }}
                                @if($log->user)
                                    <span class="ml-2 font-normal text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded">{{ $log->user->name }}</span>
                                @endif
                            </div>
                            <div class="text-sm text-gray-600">{{ $log->message }}</div>
                            @if($log->ip_address)
                                <div class="text-xs text-gray-400 mt-1"><i class="fa-solid fa-network-wired mr-1"></i> {{ $log->ip_address }}</div>
                            @endif
                        </div>
                        <div class="ml-auto shrink-0">
                            @if($log->status == 'success')
                                <span class="text-green-600"><i class="fa-solid fa-check-circle fa-lg"></i></span>
                            @elseif($log->status == 'error')
                                <span class="text-red-600"><i class="fa-solid fa-circle-xmark fa-lg"></i></span>
                            @elseif($log->status == 'warning')
                                <span class="text-yellow-600"><i class="fa-solid fa-triangle-exclamation fa-lg"></i></span>
                            @else
                                <span class="text-blue-600"><i class="fa-solid fa-circle-info fa-lg"></i></span>
                            @endif
                        </div>
                    </li>
                    @empty
                    <li class="px-6 py-12 text-center text-gray-500">
                        <i class="fa-solid fa-clipboard-list text-3xl mb-2 block text-gray-300"></i>
                        Không có log nào.
                    </li>
                    @endforelse
                </ul>
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
