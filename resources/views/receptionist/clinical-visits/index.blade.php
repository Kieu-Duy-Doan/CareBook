<x-layouts.receptionist>
    <x-slot:title>Giám sát Lâm sàng</x-slot:title>

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Giám sát Lâm sàng</h2>
            <p class="text-gray-500 mt-1">Theo dõi tiến độ khám chữa bệnh và trạng thái thanh toán của bệnh nhân hôm nay</p>
        </div>
    </div>

    <!-- FILTER FORM -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 mb-6">
        <form action="{{ route('receptionist.clinical-visits.index') }}" method="GET" class="flex flex-col lg:flex-row gap-4 items-end">
            <div class="w-full lg:flex-1">
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Ngày khám</label>
                <input type="date" name="date" value="{{ request('date', date('Y-m-d')) }}"
                    class="block w-full py-2.5 px-3 border border-gray-200 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 text-sm outline-none bg-gray-50/50">
            </div>
            <div class="w-full lg:flex-1">
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Trạng thái khám</label>
                <select name="status"
                    class="block w-full py-2.5 px-3 border border-gray-200 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 text-sm outline-none bg-white">
                    <option value="">Tất cả trạng thái</option>
                    <option value="waiting" {{ request('status') === 'waiting' ? 'selected' : '' }}>Đang chờ</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>Đang khám</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Đã hoàn thành</option>
                    <option value="refused" {{ request('status') === 'refused' ? 'selected' : '' }}>Từ chối</option>
                    <option value="redirected" {{ request('status') === 'redirected' ? 'selected' : '' }}>Chuyển viện/khoa</option>
                </select>
            </div>
            <div class="w-full lg:flex-1">
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Thanh toán</label>
                <select name="payment_status"
                    class="block w-full py-2.5 px-3 border border-gray-200 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 text-sm outline-none bg-white">
                    <option value="">Tất cả</option>
                    <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>Chưa thanh toán</option>
                    <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Đã thanh toán</option>
                    <option value="waived" {{ request('payment_status') === 'waived' ? 'selected' : '' }}>Miễn phí</option>
                </select>
            </div>
            <div class="w-full lg:flex-1">
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Bác sĩ</label>
                <select name="doctor_profile_id"
                    class="block w-full py-2.5 px-3 border border-gray-200 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 text-sm outline-none bg-white">
                    <option value="">Tất cả Bác sĩ</option>
                    @foreach ($doctors as $doc)
                        <option value="{{ $doc->id }}"
                            {{ request('doctor_profile_id') == $doc->id ? 'selected' : '' }}>
                            {{ $doc->full_title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full lg:flex-1">
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Phòng khám</label>
                <select name="room_id"
                    class="block w-full py-2.5 px-3 border border-gray-200 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 text-sm outline-none bg-white">
                    <option value="">Tất cả Phòng</option>
                    @foreach ($rooms as $room)
                        <option value="{{ $room->id }}"
                            {{ request('room_id') == $room->id ? 'selected' : '' }}>{{ $room->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2 shrink-0 w-full lg:w-auto">
                <a href="{{ route('receptionist.clinical-visits.index') }}"
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
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col"
                            class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Mã LH / STT</th>
                        <th scope="col"
                            class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Bệnh nhân</th>
                        <th scope="col"
                            class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Bác sĩ & Phòng</th>
                        <th scope="col"
                            class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Tiến trình</th>
                        <th scope="col"
                            class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Thanh toán</th>
                        <th scope="col"
                            class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($visits as $visit)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('receptionist.appointments.show', $visit->appointment_id) }}"
                                    class="font-mono text-sm font-bold text-emerald-600 hover:text-emerald-700">
                                    {{ $visit->appointment->appointment_code ?? '#' . $visit->appointment_id }}
                                </a>
                                <div class="text-xs text-gray-500 mt-1">STT: <span
                                        class="font-semibold text-gray-900">{{ $visit->visit_order }}</span></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">
                                    {{ $visit->appointment->patientProfile->full_name ?? '—' }}</div>
                                <div class="text-xs text-gray-500 mt-0.5">
                                    Khởi tạo: {{ $visit->created_at->format('H:i d/m/Y') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $visit->doctorProfile->user->name ?? '—' }}</div>
                                <div class="text-xs font-semibold text-purple-600 mt-0.5">{{ $visit->room->name ?? '—' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if ($visit->status === 'waiting')
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-50 text-yellow-800 border border-yellow-100">Đang chờ</span>
                                @elseif($visit->status === 'in_progress')
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-800 border border-blue-100">Đang khám</span>
                                @elseif($visit->status === 'completed')
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-800 border border-green-100">Hoàn thành</span>
                                @elseif($visit->status === 'refused')
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-50 text-red-800 border border-red-100">Từ chối</span>
                                @elseif($visit->status === 'redirected')
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-50 text-gray-800 border border-gray-100">Chuyển viện</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if ($visit->payment_status === 'pending')
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-orange-50 text-orange-800 border border-orange-100">Chưa TT</span>
                                @elseif($visit->payment_status === 'paid')
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-800 border border-green-100">Đã thu</span>
                                @elseif($visit->payment_status === 'waived')
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-50 text-gray-800 border border-gray-100">Miễn phí</span>
                                @endif
                                <div class="text-[10px] text-gray-500 font-semibold mt-1">
                                    {{ number_format($visit->payment_amount, 0, ',', '.') }}đ</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-3">
                                    @if ($visit->payment_status === 'pending')
                                        <a href="{{ route('receptionist.payments.create', $visit->id) }}"
                                            class="inline-flex items-center px-3 py-1 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 rounded-md text-xs font-semibold transition-colors">
                                            <i class="fa-solid fa-credit-card mr-1"></i> Thu tiền
                                        </a>
                                    @endif
                                    <a href="{{ route('receptionist.clinical-visits.show', $visit->id) }}"
                                        class="text-emerald-600 hover:text-emerald-900 transition-colors inline-flex items-center">
                                        Xem bệnh án <i class="fa-solid fa-arrow-right ml-1"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <div
                                        class="h-16 w-16 bg-gray-50 rounded-full flex items-center justify-center mb-4 border border-gray-100">
                                        <i class="fa-solid fa-hospital-user text-2xl text-gray-400"></i>
                                    </div>
                                    <h3 class="text-lg font-bold text-gray-900">Không có lượt khám nào</h3>
                                    <p class="text-sm mt-1 text-gray-500">Chưa có dữ liệu khám bệnh trong ngày này hoặc với bộ lọc hiện tại.</p>
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
