<x-layouts.admin :title="'Báo cáo thống kê'">
    <div class="space-y-6">
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Bảng điều khiển</h2>
                <p class="text-gray-500 mt-1">Báo cáo & Thống kê chi tiết theo bộ lọc</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-500">Cập nhật lúc: <span
                        class="font-medium text-gray-900">{{ now()->format('H:i d/m/Y') }}</span></span>
                <button onclick="window.location.reload()"
                    class="p-2 text-gray-500 hover:text-blue-600 bg-white rounded-lg border border-gray-200 shadow-sm transition-colors"
                    title="Làm mới dữ liệu">
                    <i class="fa-solid fa-rotate-right"></i>
                </button>
            </div>
        </div>

        <div class="mt-6 border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
                <a href="{{ route('admin.dashboard') }}"
                    class="{{ request()->routeIs('admin.dashboard') ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm">
                    <i class="fa-solid fa-chart-pie mr-2"></i> Tổng quan
                </a>
                <a href="{{ route('admin.payments.dashboard') }}"
                    class="{{ request()->routeIs('admin.payments.dashboard') ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm">
                    <i class="fa-solid fa-money-bill-wave mr-2"></i> Tài chính & Thanh toán
                </a>
                <a href="{{ route('admin.reports.index') }}"
                    class="{{ request()->routeIs('admin.reports.index') ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm">
                    <i class="fa-solid fa-chart-line mr-2"></i> Báo cáo chi tiết
                </a>
            </nav>
        </div>
    </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
            <form action="{{ route('admin.reports.index') }}" method="GET" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Từ ngày</label>
                        <input type="date" name="date_from" value="{{ $dateFrom->toDateString() }}"
                            class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Đến ngày</label>
                        <input type="date" name="date_to" value="{{ $dateTo->toDateString() }}"
                            class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Bác sĩ</label>
                        <select name="doctor_id"
                            class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none bg-white">
                            <option value="">Tất cả bác sĩ</option>
                            @foreach ($doctors as $doc)
                            <option value="{{ $doc->id }}"
                                {{ $doctorId == $doc->id ? 'selected' : '' }}>{{ $doc->full_title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Chuyên khoa</label>
                        <select name="specialty_id"
                            class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none bg-white">
                            <option value="">Tất cả chuyên khoa</option>
                            @foreach ($specialties as $sp)
                            <option value="{{ $sp->id }}"
                                {{ $specialtyId == $sp->id ? 'selected' : '' }}>{{ $sp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mt-5">
                        <div class="flex gap-2">
                            <button type="submit"
                                class="flex-1 bg-gray-900 hover:bg-gray-800 text-white py-2 rounded-lg text-sm font-medium transition-colors">
                                Xem báo cáo
                            </button>
                            <a href="{{ route('admin.reports.index') }}"
                                class="flex-1 flex items-center justify-center bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded-lg text-sm font-medium transition-colors">
                                Đặt lại
                            </a>
                        </div>
                        @if($doctorId || $specialtyId)
                        <a href="{{ route('admin.reports.export-csv', request()->only(['date_from','date_to','doctor_id','specialty_id'])) }}"
                            class="flex items-center justify-center gap-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 py-2 rounded-lg text-sm font-medium transition-colors mt-2">
                            <i class="fa-solid fa-file-csv"></i> Xuất CSV
                        </a>
                        @endif
                    </div>
                </div>
                <div class="text-xs text-gray-500">
                    <i class="fa-regular fa-calendar mr-1"></i>
                    Đang xem: <strong>{{ $dateFrom->format('d/m/Y') }}</strong> — <strong>{{ $dateTo->format('d/m/Y') }}</strong>
                    ({{ $dateFrom->diffInDays($dateTo) + 1 }} ngày)
                </div>
            </form>
        </div>

        {{-- KPI CARDS: Thống kê lượt khám --}}
        <div>
            <h2 class="text-lg font-semibold text-gray-800 mb-3">
                <i class="fa-solid fa-clipboard-list text-blue-500 mr-2"></i>Thống kê lượt khám
            </h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                    <div class="text-xs text-gray-500 font-medium mb-1">Tổng lượt khám</div>
                    <div class="text-2xl font-bold text-gray-900">{{ number_format($appointmentStats['total']) }}</div>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                    <div class="text-xs text-gray-500 font-medium mb-1">Hoàn thành</div>
                    <div class="text-2xl font-bold text-green-600">{{ number_format($appointmentStats['completed']) }}</div>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                    <div class="text-xs text-gray-500 font-medium mb-1">Đã huỷ</div>
                    <div class="text-2xl font-bold text-red-600">{{ number_format($appointmentStats['cancelled']) }}</div>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                    <div class="text-xs text-gray-500 font-medium mb-1">Vắng mặt</div>
                    <div class="text-2xl font-bold text-gray-500">{{ number_format($appointmentStats['absent']) }}</div>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                    <div class="text-xs text-gray-500 font-medium mb-1">Ca sáng</div>
                    <div class="text-2xl font-bold text-amber-600">{{ number_format($shiftStats['morning']) }}</div>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                    <div class="text-xs text-gray-500 font-medium mb-1">Ca chiều</div>
                    <div class="text-2xl font-bold text-indigo-600">{{ number_format($shiftStats['afternoon']) }}</div>
                </div>
            </div>
        </div>

        {{-- KPI CARDS: Doanh thu --}}
        <div>
            <h2 class="text-lg font-semibold text-gray-800 mb-3">
                <i class="fa-solid fa-coins text-yellow-500 mr-2"></i>Thống kê doanh thu
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl border border-blue-200 p-5">
                    <div class="text-xs text-blue-600 font-medium mb-1">Tổng doanh thu</div>
                    <div class="text-2xl font-bold text-blue-900">{{ number_format($revenueStats['total_revenue']) }}đ</div>
                    <div class="text-xs text-blue-500 mt-1">Đã thanh toán hoàn tất</div>
                </div>
                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl border border-green-200 p-5">
                    <div class="text-xs text-green-600 font-medium mb-1">Thực thu từ bệnh nhân</div>
                    <div class="text-2xl font-bold text-green-900">{{ number_format($revenueStats['patient_revenue']) }}đ</div>
                    <div class="text-xs text-green-500 mt-1">
                        Tiền mặt: {{ number_format($revenueStats['cash_revenue']) }}đ
                        &bull; QR: {{ number_format($revenueStats['qr_revenue']) }}đ
                    </div>
                </div>
                <div class="bg-gradient-to-br from-teal-50 to-teal-100 rounded-xl border border-teal-200 p-5">
                    <div class="text-xs text-teal-600 font-medium mb-1">BHYT chi trả</div>
                    <div class="text-2xl font-bold text-teal-900">{{ number_format($revenueStats['insurance_revenue']) }}đ</div>
                    <div class="text-xs text-teal-500 mt-1">Phần BHYT đã ghi nhận</div>
                </div>
                <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl border border-yellow-200 p-5">
                    <div class="text-xs text-yellow-600 font-medium mb-1">Chờ quyết toán / Chờ thu</div>
                    <div class="text-2xl font-bold text-yellow-900">{{ number_format($revenueStats['pending_revenue']) }}đ</div>
                    <div class="text-xs text-yellow-500 mt-1">Các khoản chưa hoàn tất</div>
                </div>
            </div>
        </div>

        {{-- BẢNG THỐNG KÊ THEO BÁC SĨ --}}
        <div>
            <h2 class="text-lg font-semibold text-gray-800 mb-3">
                <i class="fa-solid fa-user-doctor text-purple-500 mr-2"></i>Thống kê theo bác sĩ
            </h2>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">STT</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Bác sĩ</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Chuyên khoa</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Tổng lượt</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Hoàn thành</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Huỷ</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Vắng</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($doctorStats as $index => $stat)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 text-sm text-gray-500">{{ $index + 1 }}</td>
                                <td class="px-6 py-3 text-sm font-medium text-gray-900">{{ $stat['doctor_title'] }}</td>
                                <td class="px-6 py-3 text-sm text-gray-500">{{ $stat['specialty'] }}</td>
                                <td class="px-6 py-3 text-sm text-center font-semibold text-gray-900">{{ $stat['total'] }}</td>
                                <td class="px-6 py-3 text-sm text-center text-green-600 font-semibold">{{ $stat['completed'] }}</td>
                                <td class="px-6 py-3 text-sm text-center text-red-600">{{ $stat['cancelled'] }}</td>
                                <td class="px-6 py-3 text-sm text-center text-gray-500">{{ $stat['absent'] }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500 text-sm">
                                    Không có dữ liệu trong khoảng thời gian này.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- BẢNG THỐNG KÊ THEO CHUYÊN KHOA --}}
        <div>
            <h2 class="text-lg font-semibold text-gray-800 mb-3">
                <i class="fa-solid fa-stethoscope text-teal-500 mr-2"></i>Thống kê theo chuyên khoa
            </h2>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">STT</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Chuyên khoa</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Tổng lượt khám</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Hoàn thành</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Tỷ lệ hoàn thành</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($specialtyStats as $index => $stat)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 text-sm text-gray-500">{{ $index + 1 }}</td>
                                <td class="px-6 py-3 text-sm font-medium text-gray-900">{{ $stat['name'] }}</td>
                                <td class="px-6 py-3 text-sm text-center font-semibold text-gray-900">{{ $stat['total'] }}</td>
                                <td class="px-6 py-3 text-sm text-center text-green-600 font-semibold">{{ $stat['completed'] }}</td>
                                <td class="px-6 py-3 text-sm text-center">
                                    @php $rate = $stat['total'] > 0 ? round(($stat['completed'] / $stat['total']) * 100) : 0; @endphp
                                    <div class="flex items-center justify-center gap-2">
                                        <div class="w-20 bg-gray-200 rounded-full h-2">
                                            <div class="bg-green-500 h-2 rounded-full" style="width: {{ $rate }}%"></div>
                                        </div>
                                        <span class="text-xs font-medium text-gray-600">{{ $rate }}%</span>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500 text-sm">
                                    Không có dữ liệu trong khoảng thời gian này.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @if($detailRows && $detailRows->isNotEmpty())
    {{-- DRILL-DOWN TABLE --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-gray-900">
                    <i class="fa-solid fa-table-list text-blue-500 mr-2"></i>
                    Chi tiết lịch khám — {{ $detailRows->count() }} bản ghi
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">
                    Từ {{ $dateFrom->format('d/m/Y') }} đến {{ $dateTo->format('d/m/Y') }}
                    @if($doctorId) &bull; Bộ lọc: bác sĩ đã chọn @endif
                    @if($specialtyId) &bull; Bộ lọc: chuyên khoa đã chọn @endif
                </p>
            </div>
            <a href="{{ route('admin.reports.export-csv', request()->only(['date_from','date_to','doctor_id','specialty_id'])) }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 rounded-lg text-sm font-semibold transition">
                <i class="fa-solid fa-file-csv"></i> Xuất CSV
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Mã LH</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Bệnh nhân</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Bác sĩ</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Chuyên khoa</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Ngày khám</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Trạng thái</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Tổng thu</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($detailRows as $row)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3">
                            <span class="font-mono text-xs text-gray-500">{{ $row->appointment_code }}</span>
                        </td>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $row->patientProfile->full_name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-700 text-xs">{{ $row->doctor->full_title ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600 text-xs">{{ $row->specialty->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600 text-xs">
                            {{ $row->appointment_date?->format('d/m/Y') }}
                            <span class="text-gray-400">{{ substr($row->appointment_time ?? '', 0, 5) }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                {{ $row->status === 'completed' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $row->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900 text-sm">
                            {{ number_format($row->payments->sum('amount'), 0, ',', '.') }}₫
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('admin.hospital-history.show', $row->id) }}"
                                class="text-blue-500 hover:text-blue-700 text-xs">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</x-layouts.admin>
