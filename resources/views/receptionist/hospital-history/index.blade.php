<x-layouts.receptionist title="Lịch sử khám bệnh">
    <div class="space-y-5">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Lịch sử khám bệnh</h2>
                <p class="text-gray-500 mt-1 text-sm">Tra cứu các lịch khám đã hoàn thành</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <form action="{{ route('receptionist.hospital-history.index') }}" method="GET" class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Tìm kiếm</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Mã LH, tên bệnh nhân..."
                        class="w-full py-2 px-3 border border-gray-300 rounded-lg text-sm outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Từ ngày</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                        class="py-2 px-3 border border-gray-300 rounded-lg text-sm outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Đến ngày</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                        class="py-2 px-3 border border-gray-300 rounded-lg text-sm outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Bác sĩ</label>
                    <select name="doctor_id" class="py-2 px-3 border border-gray-300 rounded-lg text-sm outline-none focus:ring-blue-500 focus:border-blue-500 bg-white">
                        <option value="">Tất cả bác sĩ</option>
                        @foreach($doctors as $doc)
                            <option value="{{ $doc->id }}" {{ request('doctor_id') == $doc->id ? 'selected' : '' }}>
                                {{ $doc->full_title }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Chuyên khoa</label>
                    <select name="specialty_id" class="py-2 px-3 border border-gray-300 rounded-lg text-sm outline-none focus:ring-blue-500 focus:border-blue-500 bg-white">
                        <option value="">Tất cả chuyên khoa</option>
                        @foreach($specialties as $spec)
                            <option value="{{ $spec->id }}" {{ request('specialty_id') == $spec->id ? 'selected' : '' }}>
                                {{ $spec->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition">
                        <i class="fa-solid fa-magnifying-glass mr-1"></i> Lọc
                    </button>
                    <a href="{{ route('receptionist.hospital-history.index', ['clear_filter' => 1]) }}"
                        class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition">
                        Xóa lọc
                    </a>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Mã LH</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Bệnh nhân</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Bác sĩ</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Chuyên khoa</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ngày khám</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Tổng thu</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($appointments as $apt)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3">
                                <span class="font-mono text-xs text-gray-500">{{ $apt->appointment_code }}</span>
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-900">
                                {{ $apt->patientProfile->full_name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-gray-700">
                                {{ $apt->doctor->full_title ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ $apt->specialty->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ $apt->appointment_date?->format('d/m/Y') }}
                                <span class="text-gray-400 text-xs">{{ substr($apt->appointment_time ?? '', 0, 5) }}</span>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-gray-900">
                                {{ number_format($apt->payments->sum('amount'), 0, ',', '.') }}₫
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('receptionist.appointments.show', $apt->id) }}"
                                    class="text-blue-600 hover:text-blue-800 text-sm font-medium transition">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-gray-400">
                                <i class="fa-solid fa-folder-open text-2xl mb-2 block"></i>
                                Không có dữ liệu lịch sử khám trong khoảng thời gian này.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($appointments->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $appointments->links() }}
            </div>
            @endif
        </div>
    </div>
</x-layouts.receptionist>
