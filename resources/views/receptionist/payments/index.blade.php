<x-layouts.receptionist>
    <x-slot:title>Quản lý Thanh toán</x-slot:title>

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Thanh toán & Hóa đơn</h2>
            <p class="text-gray-500 mt-1">Quản lý hóa đơn chờ thu và lịch sử thanh toán qua SePay, Tiền mặt & BHYT</p>
        </div>
    </div>

    <!-- Statistics Dashboard Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-6 gap-4 mb-6">
        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-receipt text-sm"></i>
                </div>
                <p class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold">Tổng giao dịch</p>
            </div>
            <p class="text-lg font-bold text-gray-900">{{ number_format($totalTransactions) }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-sack-dollar text-sm"></i>
                </div>
                <p class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold">Doanh thu</p>
            </div>
            <p class="text-lg font-bold text-emerald-600">{{ number_format($totalRevenue, 0, ',', '.') }}đ</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-8 h-8 rounded-lg bg-green-50 text-green-600 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-money-bill-wave text-sm"></i>
                </div>
                <p class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold">Tiền mặt</p>
            </div>
            <p class="text-lg font-bold text-green-600">{{ number_format($totalCash, 0, ',', '.') }}đ</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-8 h-8 rounded-lg bg-cyan-50 text-cyan-600 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-qrcode text-sm"></i>
                </div>
                <p class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold">SePay (QR)</p>
            </div>
            <p class="text-lg font-bold text-cyan-600">{{ number_format($totalSepay, 0, ',', '.') }}đ</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-notes-medical text-sm"></i>
                </div>
                <p class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold">BHYT chi trả</p>
            </div>
            <p class="text-lg font-bold text-indigo-600">{{ number_format($totalInsurance, 0, ',', '.') }}đ</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-8 h-8 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-clock text-sm"></i>
                </div>
                <p class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold">Dư nợ chờ thu</p>
            </div>
            <p class="text-lg font-bold text-orange-600">{{ number_format($totalPending, 0, ',', '.') }}đ</p>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="mb-4 border-b border-gray-200">
        <nav class="flex space-x-8" aria-label="Tabs">
            <a href="{{ route('receptionist.payments.index', ['tab' => 'pending']) }}"
                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ $tab === 'pending' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                <i class="fa-solid fa-file-invoice-dollar mr-2"></i> Chờ thanh toán
            </a>
            <a href="{{ route('receptionist.payments.index', ['tab' => 'history']) }}"
                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ $tab === 'history' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                <i class="fa-solid fa-clock-rotate-left mr-2"></i> Lịch sử giao dịch
            </a>
        </nav>
    </div>

    <!-- Filter Form -->
    <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm mb-6">
        <form action="{{ route('receptionist.payments.index') }}" method="GET" class="flex flex-col gap-4">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Từ ngày</label>
                    <input type="date" name="from" value="{{ request('from', $from->format('Y-m-d')) }}"
                        class="block w-full py-2.5 px-3 border border-gray-200 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 text-sm outline-none bg-gray-50/50">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Đến ngày</label>
                    <input type="date" name="to" value="{{ request('to', $to->format('Y-m-d')) }}"
                        class="block w-full py-2.5 px-3 border border-gray-200 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 text-sm outline-none bg-gray-50/50">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Lọc theo tháng (Ghi đè ngày)</label>
                    <input type="month" name="month" value="{{ request('month') }}"
                        class="block w-full py-2.5 px-3 border border-gray-200 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 text-sm outline-none bg-gray-50/50">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Tìm kiếm</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ $tab === 'pending' ? 'Mã APT / Tên BN...' : 'Mã GD / Mã APT / Tên BN...' }}"
                        class="block w-full py-2.5 px-3 border border-gray-200 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 text-sm outline-none bg-gray-50/50">
                </div>
                
                @if($tab === 'history')
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Phương thức thanh toán</label>
                    <select name="method"
                        class="block w-full py-2.5 px-3 border border-gray-200 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 text-sm outline-none bg-gray-50/50">
                        <option value="">Tất cả</option>
                        <option value="qr" {{ request('method') === 'qr' ? 'selected' : '' }}>QR VietQR</option>
                        <option value="cash" {{ request('method') === 'cash' ? 'selected' : '' }}>Tiền mặt</option>
                        <option value="insurance" {{ request('method') === 'insurance' ? 'selected' : '' }}>BHYT</option>
                        <option value="waived" {{ request('method') === 'waived' ? 'selected' : '' }}>Miễn phí</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Người thu tiền</label>
                    <div x-data="{
                            open: false,
                            search: '',
                            selected: '{{ request('collector_id') }}',
                            options: [
                                { id: '', label: 'Tất cả' },
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
                                return opt ? opt.label : 'Tất cả';
                            }
                        }" class="relative w-full" @click.away="open = false">
                        <input type="hidden" name="collector_id" x-model="selected">
                        <button type="button" @click="open = !open" class="block w-full py-2.5 px-3 border border-gray-200 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 text-sm outline-none bg-gray-50/50 text-left flex items-center justify-between h-[42px]">
                            <span x-text="selectedLabel" class="truncate text-gray-700"></span>
                            <i class="fa-solid fa-chevron-down text-xs text-gray-400"></i>
                        </button>
                        <div x-show="open" x-transition class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg" style="display: none;">
                            <div class="p-2 border-b border-gray-100">
                                <div class="relative">
                                    <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                                    <input type="text" x-model="search" placeholder="Tìm kiếm..." class="w-full pl-7 pr-3 py-1.5 text-sm border border-gray-200 rounded-md focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                                </div>
                            </div>
                            <ul class="max-h-48 overflow-y-auto py-1">
                                <template x-for="option in filteredOptions" :key="option.id">
                                    <li @click="selected = option.id; open = false; search = ''" 
                                        class="px-3 py-2 text-sm cursor-pointer hover:bg-emerald-50 hover:text-emerald-700 transition-colors"
                                        :class="{'bg-emerald-50 text-emerald-700 font-medium': selected === option.id, 'text-gray-700': selected !== option.id}">
                                        <span x-text="option.label"></span>
                                    </li>
                                </template>
                                <li x-show="filteredOptions.length === 0" class="px-3 py-2 text-sm text-gray-500 text-center">Không tìm thấy</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Chuyên khoa</label>
                    <select name="specialty_id"
                        class="block w-full py-2.5 px-3 border border-gray-200 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 text-sm outline-none bg-gray-50/50">
                        <option value="">Tất cả</option>
                        @foreach($specialties as $s)
                            <option value="{{ $s->id }}" {{ request('specialty_id') == $s->id ? 'selected' : '' }}>
                                {{ $s->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>

            <div class="flex gap-2 justify-end mt-2">
                <a href="{{ route('receptionist.payments.index', ['tab' => $tab]) }}"
                    class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-semibold transition-colors">
                    Đặt lại
                </a>
                <button type="submit"
                    class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold transition-colors">
                    Lọc dữ liệu
                </button>
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        @if ($tab === 'pending')
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Mã Lịch Hẹn</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Bệnh nhân</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Phí Dịch vụ (tạm tính)</th>
                            <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Trạng thái</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($appointments as $appointment)
                            @php
                                $totalFee = $appointment->clinicalVisits->where('payment_status', 'pending')->sum('payment_amount');
                                $statusLabel = 'Chờ thanh toán';
                                $statusColor = 'bg-orange-50 text-orange-850 border border-orange-100';
                            @endphp
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap font-mono text-sm font-bold text-gray-700">
                                    {{ $appointment->appointment_code }}
                                    <div class="text-[10px] text-gray-400 font-semibold mt-1">{{ $appointment->created_at->format('H:i d/m/Y') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-950">
                                        {{ $appointment->patientProfile->full_name ?? '—' }}
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1 font-mono">
                                        Mã BN: {{ $appointment->patientProfile->patient_code ?? '—' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600 font-bold">
                                    {{ number_format($totalFee, 0, ',', '.') }}đ
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusColor }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold">
                                    <a href="{{ route('receptionist.payments.create', $appointment->id) }}"
                                        class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm">
                                        <i class="fa-solid fa-qrcode mr-1.5"></i> Thu tiền
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="h-16 w-16 bg-gray-50 rounded-full flex items-center justify-center mb-4 border border-gray-100">
                                            <i class="fa-solid fa-file-invoice-dollar text-2xl text-gray-400"></i>
                                        </div>
                                        <h3 class="text-lg font-bold text-gray-900">Không tìm thấy bản ghi nào</h3>
                                    </div>
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
        @else
            <!-- Lịch sử giao dịch -->
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Mã GD</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Thời gian</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Bệnh nhân</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nội dung</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Phí gốc</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">BN chi trả</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">BHYT chi trả</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">PT TT</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($payments as $payment)
                            @php
                                $methodConfig = [
                                    'qr'        => ['label' => 'QR',      'bg' => 'bg-cyan-100',   'text' => 'text-cyan-700'],
                                    'cash'      => ['label' => 'Tiền mặt','bg' => 'bg-green-100',  'text' => 'text-green-700'],
                                    'insurance' => ['label' => 'BHYT',    'bg' => 'bg-indigo-100', 'text' => 'text-indigo-700'],
                                    'waived'    => ['label' => 'Miễn phí','bg' => 'bg-gray-100',   'text' => 'text-gray-600'],
                                ];
                                $mc = $methodConfig[$payment->method] ?? ['label' => $payment->method, 'bg' => 'bg-gray-100', 'text' => 'text-gray-600'];
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <!-- Mã GD -->
                                <td class="px-4 py-3">
                                    <span class="font-mono text-xs text-gray-700 font-bold bg-gray-100 px-2 py-1 rounded">
                                        {{ Str::limit($payment->transaction_code, 12) }}
                                    </span>
                                </td>
                                <!-- Thời gian -->
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-xs text-gray-900 font-bold">{{ $payment->paid_at?->format('d/m/Y') }}</div>
                                    <div class="text-[11px] font-semibold text-gray-500">{{ $payment->paid_at?->format('H:i') }}</div>
                                </td>
                                <!-- Bệnh nhân -->
                                <td class="px-4 py-3">
                                    <div class="text-sm font-bold text-gray-900 truncate max-w-[140px]">
                                        {{ $payment->appointment?->patientProfile?->full_name ?? '—' }}
                                    </div>
                                    <div class="text-xs text-gray-500 font-mono">
                                        Mã APT: {{ $payment->appointment?->appointment_code ?? '—' }}
                                    </div>
                                </td>
                                <!-- Nội dung -->
                                <td class="px-4 py-3">
                                    <div class="text-xs font-semibold text-gray-700 truncate max-w-[130px]">
                                        {{ $payment->appointment?->specialty?->name ?? '—' }}
                                    </div>
                                    @if($payment->note)
                                        <div class="text-[11px] text-gray-500 truncate max-w-[130px]">{{ $payment->note }}</div>
                                    @endif
                                </td>
                                <!-- Phí gốc -->
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <span class="text-sm font-bold text-gray-600">
                                        {{ $payment->total_fee > 0 ? number_format($payment->total_fee) . 'đ' : '—' }}
                                    </span>
                                </td>
                                <!-- BN chi trả -->
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <div class="text-sm font-bold text-gray-900">{{ number_format($payment->patient_amount) }}đ</div>
                                    <div class="text-xs text-gray-500">{{ $payment->patient_percent }}%</div>
                                </td>
                                <!-- BHYT chi trả -->
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    @if($payment->method === 'insurance' && $payment->insurance_amount > 0)
                                        <div class="text-sm font-bold text-indigo-600">{{ number_format($payment->insurance_amount) }}đ</div>
                                        <div class="text-xs font-semibold text-indigo-400">{{ $payment->insurance_percent }}%</div>
                                    @else
                                        <span class="text-xs text-gray-300 font-bold">—</span>
                                    @endif
                                </td>
                                <!-- PT TT -->
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold uppercase tracking-wider {{ $mc['bg'] }} {{ $mc['text'] }}">
                                        {{ $mc['label'] }}
                                    </span>
                                </td>
                                <!-- Thao tác -->
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <a href="{{ route('receptionist.payments.show', $payment->appointment_id) }}"
                                        class="inline-flex items-center px-3 py-1.5 bg-gray-50 border border-gray-200 text-gray-700 hover:bg-gray-100 rounded-lg text-xs font-semibold transition-all shadow-sm">
                                        <i class="fa-solid fa-eye mr-1.5 text-gray-400"></i> Xem CT
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-16 text-center text-gray-400">
                                    <i class="fa-solid fa-receipt text-4xl mb-3 block text-gray-200"></i>
                                    <p class="text-sm font-semibold">Không tìm thấy giao dịch nào phù hợp.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($payments->hasPages())
                <div class="px-5 py-4 border-t border-gray-100 bg-white">
                    {{ $payments->links() }}
                </div>
            @endif
        @endif
    </div>
</x-layouts.receptionist>
