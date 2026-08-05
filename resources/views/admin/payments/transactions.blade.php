<x-layouts.admin title="Lịch sử giao dịch">

    {{-- ── Header ─────────────────────────────────────────────────────────────── --}}
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Lịch sử giao dịch</h2>
                <p class="text-gray-500 mt-1">Theo dõi toàn bộ giao dịch thanh toán trong hệ thống</p>
            </div>
            <a href="{{ route('admin.payments.transactions.export', request()->query()) }}"
               id="btn-export-excel"
               class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm">
                <i class="fa-solid fa-file-excel"></i>
                Xuất Excel
            </a>
        </div>
    </div>

    {{-- ── Bộ lọc nâng cao ─────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-6"
         x-data="{ open: {{ request()->hasAny(['method','status','collector_id','specialty_id','min_amount','max_amount','search','from','to','month']) ? 'true' : 'false' }} }">
        <button @click="open = !open"
                class="w-full flex items-center justify-between px-5 py-4 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-xl transition-colors">
            <span class="flex items-center gap-2">
                <i class="fa-solid fa-sliders text-blue-500"></i>
                Bộ lọc nâng cao
                @if(request()->hasAny(['method','status','collector_id','specialty_id','min_amount','max_amount','search']))
                    <span class="bg-blue-100 text-blue-700 text-xs font-bold px-2 py-0.5 rounded-full">Đang lọc</span>
                @endif
            </span>
            <i class="fa-solid fa-chevron-down text-xs transition-transform" :class="open ? 'rotate-180' : ''"></i>
        </button>

        <div x-show="open" x-transition class="border-t border-gray-100">
            <form method="GET" action="{{ route('admin.payments.transactions') }}" class="p-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    {{-- Từ ngày / Đến ngày --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Từ ngày</label>
                        <input type="date" name="from" id="filter-from"
                               value="{{ request('from', $from->format('Y-m-d')) }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Đến ngày</label>
                        <input type="date" name="to" id="filter-to"
                               value="{{ request('to', $to->format('Y-m-d')) }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none">
                    </div>

                    {{-- Chọn tháng --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">
                            Chọn tháng <span class="text-gray-400">(ghi đè từ/đến ngày)</span>
                        </label>
                        <input type="month" name="month" id="filter-month"
                               value="{{ request('month') }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none">
                    </div>

                    {{-- Phương thức thanh toán --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Phương thức thanh toán</label>
                        <select name="method"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none bg-white">
                            <option value="">-- Tất cả --</option>
                            <option value="qr"        {{ request('method') === 'qr'        ? 'selected' : '' }}>QR VietQR</option>
                            <option value="cash"      {{ request('method') === 'cash'      ? 'selected' : '' }}>Tiền mặt</option>
                            <option value="insurance" {{ request('method') === 'insurance' ? 'selected' : '' }}>BHYT</option>
                            <option value="waived"    {{ request('method') === 'waived'    ? 'selected' : '' }}>Miễn phí</option>
                        </select>
                    </div>


                    {{-- Người thu tiền (Searchable) --}}
                    <div x-data="{
                            open: false,
                            search: '',
                            selected: '{{ request('collector_id') }}',
                            options: [
                                { id: '', label: '-- Tất cả --' },
                                @foreach($collectors as $c)
                                    { id: '{{ $c->id }}', label: '{{ $c->full_name }} ({{ $c->role === 'admin' ? 'Admin' : ($c->role === 'doctor' ? 'Bác sĩ' : 'Lễ tân') }})' },
                                @endforeach
                            ],
                            get filteredOptions() {
                                if (this.search === '') return this.options;
                                return this.options.filter(i => i.label.toLowerCase().includes(this.search.toLowerCase()));
                            },
                            get selectedLabel() {
                                const opt = this.options.find(i => i.id == this.selected);
                                return opt ? opt.label : '-- Tất cả --';
                            }
                        }" class="relative w-full" @click.away="open = false">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Người thu tiền</label>
                        <input type="hidden" name="collector_id" x-model="selected">
                        <button type="button" @click="open = !open" class="w-full flex items-center justify-between border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none bg-white text-left text-gray-700 h-[38px]">
                            <span x-text="selectedLabel" class="truncate"></span>
                            <i class="fa-solid fa-chevron-down text-xs text-gray-400"></i>
                        </button>
                        <div x-show="open" x-transition class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg" style="display: none;">
                            <div class="p-2 border-b border-gray-100">
                                <div class="relative">
                                    <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                                    <input type="text" x-model="search" placeholder="Tìm kiếm..." class="w-full pl-7 pr-3 py-1.5 text-sm border border-gray-200 rounded-md focus:outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-400">
                                </div>
                            </div>
                            <ul class="max-h-48 overflow-y-auto py-1">
                                <template x-for="option in filteredOptions" :key="option.id">
                                    <li @click="selected = option.id; open = false; search = ''" 
                                        class="px-3 py-2 text-sm cursor-pointer hover:bg-blue-50 hover:text-blue-600 transition-colors"
                                        :class="{'bg-blue-50 text-blue-600 font-medium': selected === option.id, 'text-gray-700': selected !== option.id}">
                                        <span x-text="option.label"></span>
                                    </li>
                                </template>
                                <li x-show="filteredOptions.length === 0" class="px-3 py-2 text-sm text-gray-500 text-center">Không tìm thấy</li>
                            </ul>
                        </div>
                    </div>

                    {{-- Chuyên khoa --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Chuyên khoa</label>
                        <select name="specialty_id"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none bg-white">
                            <option value="">-- Tất cả --</option>
                            @foreach($specialties as $s)
                                <option value="{{ $s->id }}" {{ request('specialty_id') == $s->id ? 'selected' : '' }}>
                                    {{ $s->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Khoảng tiền --}}
                    <div class="sm:col-span-2 lg:col-span-1">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Khoảng số tiền (đ)</label>
                        <div class="flex gap-2">
                            <input type="number" name="min_amount" placeholder="Từ"
                                   value="{{ request('min_amount') }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none">
                            <input type="number" name="max_amount" placeholder="Đến"
                                   value="{{ request('max_amount') }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none">
                        </div>
                    </div>

                    {{-- Tìm kiếm --}}
                    <div class="sm:col-span-2 lg:col-span-1">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tìm kiếm</label>
                        <div class="relative">
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                            <input type="text" name="search" placeholder="Mã giao dịch, tên bệnh nhân, mã lịch hẹn..."
                                   value="{{ request('search') }}"
                                   class="w-full border border-gray-300 rounded-lg pl-9 pr-4 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none">
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 mt-4 pt-4 border-t border-gray-100">
                    <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                        <i class="fa-solid fa-filter"></i> Áp dụng bộ lọc
                    </button>
                    <a href="{{ route('admin.payments.transactions') }}"
                       class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                        <i class="fa-solid fa-xmark"></i> Xóa bộ lọc
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Summary Cards ───────────────────────────────────────────────────────── --}}
    <div x-data="{ showPendingModal: false }">
    <div class="grid grid-cols-2 lg:grid-cols-6 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-receipt text-blue-600 text-sm"></i>
                </div>
                <p class="text-[11px] text-gray-500 uppercase tracking-wider font-medium leading-tight">Tổng GD</p>
            </div>
            <p class="text-xl font-bold text-gray-900">{{ number_format($totalCount) }}</p>
            <p class="text-[10px] text-gray-400 mt-1">{{ $from->format('d/m') }} — {{ $to->format('d/m/Y') }}</p>
        </div>
        
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-9 h-9 rounded-lg bg-green-50 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-circle-check text-green-600 text-sm"></i>
                </div>
                <p class="text-[11px] text-gray-500 uppercase tracking-wider font-medium leading-tight">Doanh thu</p>
            </div>
            <p class="text-xl font-bold text-green-600">{{ number_format($totalRevenue) }}đ</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-9 h-9 rounded-lg bg-emerald-50 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-money-bill-wave text-emerald-600 text-sm"></i>
                </div>
                <p class="text-[11px] text-gray-500 uppercase tracking-wider font-medium leading-tight">Tiền mặt</p>
            </div>
            <p class="text-xl font-bold text-emerald-600">{{ number_format($totalCash) }}đ</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-9 h-9 rounded-lg bg-cyan-50 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-qrcode text-cyan-600 text-sm"></i>
                </div>
                <p class="text-[11px] text-gray-500 uppercase tracking-wider font-medium leading-tight">SePay (QR)</p>
            </div>
            <p class="text-xl font-bold text-cyan-600">{{ number_format($totalSepay) }}đ</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-9 h-9 rounded-lg bg-indigo-50 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-notes-medical text-indigo-600 text-sm"></i>
                </div>
                <p class="text-[11px] text-gray-500 uppercase tracking-wider font-medium leading-tight">BHYT chi trả</p>
            </div>
            <p class="text-xl font-bold text-indigo-600">{{ number_format($totalInsurance) }}đ</p>
        </div>

        <div @click="showPendingModal = true" class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm cursor-pointer hover:border-yellow-300 hover:shadow-md transition-all group relative">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-9 h-9 rounded-lg bg-yellow-50 flex items-center justify-center shrink-0 group-hover:bg-yellow-100 transition-colors">
                    <i class="fa-solid fa-clock text-yellow-600 text-sm"></i>
                </div>
                <p class="text-[11px] text-gray-500 uppercase tracking-wider font-medium leading-tight">Chờ xử lý</p>
            </div>
            <p class="text-xl font-bold text-yellow-600">{{ number_format($totalPending) }}đ</p>
            <p class="text-[10px] text-yellow-500 mt-1 opacity-0 group-hover:opacity-100 transition-opacity">Nhấn để xem chi tiết &rarr;</p>
        </div>

    {{-- Pending Visits Modal --}}
    <div x-show="showPendingModal" class="relative z-[100]" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
        <div x-show="showPendingModal" x-transition.opacity class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div x-show="showPendingModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     @click.away="showPendingModal = false"
                     class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl">
                    
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-lg font-semibold leading-6 text-gray-900" id="modal-title">
                            Chi tiết các dịch vụ Chờ thu ({{ number_format($totalPending) }}đ)
                        </h3>
                        <button @click="showPendingModal = false" class="text-gray-400 hover:text-gray-500">
                            <i class="fa-solid fa-xmark text-xl"></i>
                        </button>
                    </div>

                    <div class="bg-gray-50 p-4 max-h-[60vh] overflow-y-auto">
                        @if(isset($pendingVisits) && $pendingVisits->count() > 0)
                            <table class="min-w-full divide-y divide-gray-200 bg-white rounded-lg shadow-sm border border-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mã Lịch hẹn</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ngày giờ</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bệnh nhân</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dịch vụ (Phòng)</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bác sĩ</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Số tiền</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($pendingVisits as $visit)
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $visit->appointment?->appointment_code }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-500">{{ $visit->created_at?->format('d/m/Y H:i') }}</td>
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $visit->appointment?->patientProfile?->full_name }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-500">{{ $visit->room?->name ?? 'N/A' }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-500">{{ $visit->appointment?->doctorProfile?->user?->full_name }}</td>
                                            <td class="px-4 py-3 text-sm font-semibold text-red-600 text-right">{{ number_format($visit->payment_amount) }}đ</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @if($pendingVisits->count() == 100)
                                <p class="text-xs text-gray-500 text-center mt-3">* Đang hiển thị 100 dịch vụ gần nhất.</p>
                            @endif
                        @else
                            <div class="text-center py-10">
                                <i class="fa-regular fa-folder-open text-4xl text-gray-300 mb-3"></i>
                                <p class="text-gray-500">Không có dịch vụ chờ thu nào.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>

    {{-- ── Bảng giao dịch + Modal ──────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden"
         x-data="transactionTable()">

        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">
                Danh sách giao dịch
                <span class="ml-2 text-sm text-gray-400 font-normal">({{ $payments->total() }} kết quả)</span>
            </h3>
            <p class="text-xs text-gray-400">Trang {{ $payments->currentPage() }}/{{ $payments->lastPage() }}</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Mã GD</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Thời gian</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Bệnh nhân</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nội dung</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Phí gốc</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">BN chi trả</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">BHYT chi trả</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">PT TT</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">TT</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Người thu</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($payments as $payment)
                        @php
                            $methodConfig = [
                                'qr'        => ['label' => 'QR',      'bg' => 'bg-blue-100',   'text' => 'text-blue-700'],
                                'cash'      => ['label' => 'Tiền mặt','bg' => 'bg-green-100',  'text' => 'text-green-700'],
                                'insurance' => ['label' => 'BHYT',    'bg' => 'bg-indigo-100', 'text' => 'text-indigo-700'],
                                'waived'    => ['label' => 'Miễn phí','bg' => 'bg-gray-100',   'text' => 'text-gray-600'],
                            ];
                            $statusConfig = [
                                'completed'    => ['label' => 'Hoàn thành',  'bg' => 'bg-green-100',  'text' => 'text-green-700'],
                                'pending'      => ['label' => 'Chờ xử lý',   'bg' => 'bg-yellow-100', 'text' => 'text-yellow-700'],
                                'refunded'     => ['label' => 'Hoàn trả',    'bg' => 'bg-red-100',    'text' => 'text-red-700'],
                                'needs_review' => ['label' => 'Cần xem xét', 'bg' => 'bg-orange-100', 'text' => 'text-orange-700'],
                            ];
                            $mc = $methodConfig[$payment->method] ?? ['label' => $payment->method, 'bg' => 'bg-gray-100', 'text' => 'text-gray-600'];
                            $sc = $statusConfig[$payment->status] ?? ['label' => $payment->status, 'bg' => 'bg-gray-100', 'text' => 'text-gray-600'];
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            {{-- Mã GD --}}
                            <td class="px-4 py-3">
                                <span class="font-mono text-xs text-gray-700 bg-gray-100 px-2 py-0.5 rounded">
                                    {{ Str::limit($payment->transaction_code, 16) }}
                                </span>
                            </td>

                            {{-- Thời gian --}}
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-xs text-gray-900 font-medium">{{ $payment->paid_at?->format('d/m/Y') }}</div>
                                <div class="text-xs text-gray-400">{{ $payment->paid_at?->format('H:i') }}</div>
                            </td>

                            {{-- Bệnh nhân --}}
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-gray-900 truncate max-w-[140px]">
                                    {{ $payment->appointment?->patientProfile?->full_name ?? '—' }}
                                </div>
                                <div class="text-xs text-gray-400">
                                    {{ $payment->appointment?->appointment_code ?? '—' }}
                                </div>
                            </td>

                            {{-- Nội dung --}}
                            <td class="px-4 py-3">
                                <div class="text-xs text-gray-700 truncate max-w-[130px]">
                                    {{ $payment->appointment?->specialty?->name ?? '—' }}
                                </div>
                                @if($payment->note)
                                    <div class="text-xs text-gray-400 truncate max-w-[130px]">{{ $payment->note }}</div>
                                @endif
                            </td>

                            {{-- Phí gốc --}}
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <span class="text-sm font-medium text-gray-700">
                                    {{ $payment->total_fee > 0 ? number_format($payment->total_fee) . 'đ' : '—' }}
                                </span>
                            </td>

                            {{-- Bệnh nhân chi trả --}}
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">{{ number_format($payment->patient_amount) }}đ</div>
                                <div class="text-xs text-gray-400">{{ $payment->patient_percent }}%</div>
                            </td>

                            {{-- BHYT chi trả --}}
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                @if($payment->insurance_amount > 0)
                                    <div class="text-sm font-semibold text-indigo-600">{{ number_format($payment->insurance_amount) }}đ</div>
                                    <div class="text-xs text-indigo-400">{{ $payment->insurance_percent }}%</div>
                                @else
                                    <span class="text-xs text-gray-300">—</span>
                                @endif
                            </td>

                            {{-- Phương thức --}}
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $mc['bg'] }} {{ $mc['text'] }}">
                                    {{ $mc['label'] }}
                                </span>
                            </td>

                            {{-- Trạng thái --}}
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $sc['bg'] }} {{ $sc['text'] }}">
                                    {{ $sc['label'] }}
                                </span>
                            </td>

                            {{-- Người thu --}}
                            <td class="px-4 py-3">
                                <div class="text-xs text-gray-700 truncate max-w-[100px]">
                                    {{ $payment->collectedBy?->full_name ?? '—' }}
                                </div>
                            </td>

                            {{-- Thao tác --}}
                            <td class="px-4 py-3 text-center">
                                <button @click="openModal({{ $payment->id }})"
                                        class="inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 hover:bg-blue-50 px-2.5 py-1.5 rounded-lg transition-colors font-medium">
                                    <i class="fa-solid fa-eye text-xs"></i> Xem
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-4 py-16 text-center text-gray-400">
                                <i class="fa-solid fa-receipt text-4xl mb-3 block text-gray-200"></i>
                                <p class="text-sm">Không tìm thấy giao dịch nào phù hợp.</p>
                                <a href="{{ route('admin.payments.transactions') }}" class="text-blue-500 text-sm hover:underline mt-1 inline-block">Xóa bộ lọc</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($payments->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $payments->links() }}
            </div>
        @endif

        {{-- ── Modal chi tiết ─────────────────────────────────────────────────── --}}
        <div x-show="showModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60"
             @click.self="showModal = false"
             style="display: none;">

            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-hidden flex flex-col"
                 @click.stop>

                {{-- Modal Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center">
                            <i class="fa-solid fa-receipt text-blue-600"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900" x-text="'Chi tiết giao dịch #' + (detail?.payment?.id ?? '')"></h3>
                            <p class="text-xs text-gray-500 font-mono" x-text="detail?.payment?.transaction_code"></p>
                        </div>
                    </div>
                    <button @click="showModal = false"
                            class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-gray-200 text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                {{-- Loading state --}}
                <div x-show="loading" class="flex items-center justify-center py-20">
                    <div class="flex flex-col items-center gap-3 text-gray-400">
                        <i class="fa-solid fa-spinner fa-spin text-3xl text-blue-400"></i>
                        <p class="text-sm">Đang tải dữ liệu...</p>
                    </div>
                </div>

                {{-- Modal body --}}
                <div x-show="!loading && detail" class="overflow-y-auto flex-1 px-6 py-5 space-y-5" style="display: none;">

                    {{-- Thông tin giao dịch --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                                <i class="fa-solid fa-money-bill-wave text-green-500"></i> Thông tin giao dịch
                            </h4>
                        </div>

                        {{-- Badges hàng ngang --}}
                        <div class="col-span-2 flex flex-wrap gap-2">
                            <template x-if="detail?.payment?.method">
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                    <i class="fa-solid fa-credit-card text-xs"></i>
                                    <span x-text="detail?.methodLabels?.[detail?.payment?.method]?.label ?? detail?.payment?.method"></span>
                                </span>
                            </template>
                            <template x-if="detail?.payment?.status">
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium"
                                      :class="{
                                          'bg-green-100 text-green-700': detail?.payment?.status === 'completed',
                                          'bg-yellow-100 text-yellow-700': detail?.payment?.status === 'pending',
                                          'bg-red-100 text-red-700': detail?.payment?.status === 'refunded',
                                          'bg-orange-100 text-orange-700': detail?.payment?.status === 'needs_review',
                                      }">
                                    <span x-text="detail?.statusLabels?.[detail?.payment?.status]?.label ?? detail?.payment?.status"></span>
                                </span>
                            </template>
                        </div>

                        {{-- Số tiền --}}
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs text-gray-500 mb-1">Phí gốc (lịch hẹn)</p>
                            <p class="text-xl font-bold text-gray-900" x-text="formatMoney(detail?.payment?.total_fee) + 'đ'"></p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs text-gray-500 mb-1">Số tiền giao dịch</p>
                            <p class="text-xl font-bold text-green-700" x-text="formatMoney(detail?.payment?.amount) + 'đ'"></p>
                        </div>

                        {{-- Phân chia BHYT / BN --}}
                        <div class="col-span-2 grid grid-cols-2 gap-3">
                            <div class="border border-indigo-100 bg-indigo-50 rounded-xl p-3">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs text-indigo-600 font-medium">BHYT chi trả</span>
                                    <span class="text-sm font-bold text-indigo-700" x-text="detail?.payment?.insurance_percent + '%'"></span>
                                </div>
                                <p class="text-lg font-bold text-indigo-700" x-text="formatMoney(detail?.payment?.insurance_amount) + 'đ'"></p>
                                <div class="mt-2 h-1.5 bg-indigo-100 rounded-full">
                                    <div class="h-1.5 bg-indigo-500 rounded-full transition-all"
                                         :style="'width: ' + detail?.payment?.insurance_percent + '%'"></div>
                                </div>
                            </div>
                            <div class="border border-green-100 bg-green-50 rounded-xl p-3">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs text-green-600 font-medium">Bệnh nhân chi trả</span>
                                    <span class="text-sm font-bold text-green-700" x-text="detail?.payment?.patient_percent + '%'"></span>
                                </div>
                                <p class="text-lg font-bold text-green-700" x-text="formatMoney(detail?.payment?.patient_amount) + 'đ'"></p>
                                <div class="mt-2 h-1.5 bg-green-100 rounded-full">
                                    <div class="h-1.5 bg-green-500 rounded-full transition-all"
                                         :style="'width: ' + detail?.payment?.patient_percent + '%'"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Thông tin khác --}}
                        <div>
                            <p class="text-xs text-gray-500">Thời gian thanh toán</p>
                            <p class="text-sm font-medium text-gray-800 mt-0.5" x-text="detail?.payment?.paid_at ?? '—'"></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Người thu tiền</p>
                            <p class="text-sm font-medium text-gray-800 mt-0.5" x-text="detail?.payment?.collected_by_name ?? '—'"></p>
                        </div>
                        <div class="col-span-2" x-show="detail?.payment?.note">
                            <p class="text-xs text-gray-500">Ghi chú</p>
                            <p class="text-sm text-gray-700 mt-0.5 bg-yellow-50 px-3 py-2 rounded-lg border border-yellow-100" x-text="detail?.payment?.note"></p>
                        </div>
                    </div>

                    <hr class="border-gray-100">

                    {{-- Thông tin bệnh nhân & lịch hẹn --}}
                    <div>
                        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                            <i class="fa-solid fa-user-injured text-blue-400"></i> Bệnh nhân & Lịch hẹn
                        </h4>
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <p class="text-xs text-gray-500">Bệnh nhân</p>
                                <p class="font-medium text-gray-900 mt-0.5" x-text="detail?.payment?.patient_name ?? '—'"></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Mã bệnh nhân</p>
                                <p class="font-medium text-gray-700 mt-0.5 font-mono text-xs" x-text="detail?.payment?.patient_code ?? '—'"></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Số điện thoại</p>
                                <p class="font-medium text-gray-700 mt-0.5" x-text="detail?.payment?.patient_phone ?? '—'"></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Mã BHYT</p>
                                <p class="font-medium text-indigo-700 mt-0.5 font-mono text-xs" x-text="detail?.payment?.insurance_code ?? 'Không có'"></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Mã lịch hẹn</p>
                                <p class="font-medium text-gray-700 mt-0.5 font-mono text-xs" x-text="detail?.payment?.appointment_code ?? '—'"></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Ngày khám</p>
                                <p class="font-medium text-gray-700 mt-0.5" x-text="detail?.payment?.appointment_date ?? '—'"></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Bác sĩ</p>
                                <p class="font-medium text-gray-700 mt-0.5" x-text="detail?.payment?.doctor_name ?? '—'"></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Chuyên khoa</p>
                                <p class="font-medium text-gray-700 mt-0.5" x-text="detail?.payment?.specialty_name ?? '—'"></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Phòng khám</p>
                                <p class="font-medium text-gray-700 mt-0.5" x-text="detail?.payment?.room_name ?? '—'"></p>
                            </div>
                        </div>
                    </div>

                    {{-- Chi tiết khám (clinical visits) --}}
                    <template x-if="detail?.payment?.clinical_visits?.length > 0">
                        <div>
                            <hr class="border-gray-100 mb-4">
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                                <i class="fa-solid fa-stethoscope text-purple-400"></i> Chi tiết lượt khám
                            </h4>
                            <div class="space-y-2">
                                <template x-for="(v, i) in detail.payment.clinical_visits" :key="i">
                                    <div class="flex items-center justify-between py-2 px-3 bg-gray-50 rounded-lg text-sm">
                                        <div>
                                            <span class="font-medium text-gray-800" x-text="v.doctor ?? '—'"></span>
                                            <span class="text-gray-400 mx-1">·</span>
                                            <span class="text-gray-500 text-xs" x-text="v.room ?? ''"></span>
                                        </div>
                                        <div class="text-right">
                                            <span class="font-semibold text-gray-900" x-text="formatMoney(v.payment_amount) + 'đ'"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- Đơn thuốc --}}
                    <template x-if="detail?.payment?.prescriptions?.length > 0">
                        <div>
                            <hr class="border-gray-100 mb-4">
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                                <i class="fa-solid fa-pills text-green-500"></i> Đơn thuốc
                            </h4>
                            <div class="space-y-2">
                                <template x-for="(p, i) in detail.payment.prescriptions" :key="i">
                                    <div class="flex items-center justify-between py-2 px-3 bg-gray-50 rounded-lg text-sm">
                                        <div>
                                            <span class="text-gray-700" x-text="p.prescribed_date"></span>
                                            <span class="text-gray-400 mx-1">·</span>
                                            <span class="text-gray-500 text-xs" x-text="p.diagnosis_note"></span>
                                        </div>
                                        <div class="text-right">
                                            <span class="font-semibold text-gray-900" x-text="formatMoney(p.payment_amount) + 'đ'"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- Lịch sử log --}}
                    <template x-if="detail?.logs?.length > 0">
                        <div>
                            <hr class="border-gray-100 mb-4">
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                                <i class="fa-solid fa-clock-rotate-left text-orange-400"></i> Lịch sử hoạt động
                            </h4>
                            <div class="space-y-2">
                                <template x-for="(l, i) in detail.logs" :key="i">
                                    <div class="flex gap-3 text-sm">
                                        <div class="w-1.5 h-1.5 rounded-full mt-2 shrink-0"
                                             :class="{
                                                 'bg-green-500': l.status === 'success',
                                                 'bg-yellow-500': l.status === 'warning',
                                                 'bg-red-500': l.status === 'error',
                                                 'bg-blue-400': l.status === 'info',
                                             }"></div>
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="font-medium text-gray-800 text-xs" x-text="l.action"></span>
                                                <span class="text-gray-300">·</span>
                                                <span class="text-gray-400 text-xs" x-text="l.created_at"></span>
                                                <span x-show="l.user" class="text-gray-400 text-xs">bởi <span class="text-gray-600" x-text="l.user"></span></span>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-0.5" x-text="l.message"></p>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    function transactionTable() {
        return {
            showModal: false,
            loading: false,
            detail: null,

            openModal(paymentId) {
                this.showModal = true;
                this.loading = true;
                this.detail = null;

                fetch(`{{ url('admin/payments/transactions') }}/${paymentId}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => res.json())
                .then(data => {
                    this.detail = data;
                    this.loading = false;
                })
                .catch(() => {
                    this.loading = false;
                    alert('Không thể tải chi tiết giao dịch.');
                    this.showModal = false;
                });
            },

            formatMoney(value) {
                if (value === null || value === undefined || value === 0 || value === '0') return '0';
                return new Intl.NumberFormat('vi-VN').format(parseFloat(value));
            }
        }
    }
    </script>
    @endpush

</x-layouts.admin>

