<x-layouts.doctor title="Lịch hẹn của tôi">
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Quản lý lịch hẹn</h2>
            <p class="text-gray-500 mt-1">Danh sách các ca khám được phân công cho bạn</p>
        </div>
    </div>

    @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" style="display: none;"
            class="bg-green-50 text-green-800 p-4 rounded-lg mb-6 flex items-center justify-between border border-green-200">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-green-500"></i>
                {{ session('success') }}
            </div>
            <button @click="show=false" class="text-green-500 hover:text-green-700"><i
                    class="fa-solid fa-xmark"></i></button>
        </div>
    @endif

    @if (session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)" style="display: none;"
            class="bg-red-50 text-red-800 p-4 rounded-lg mb-6 flex items-center justify-between border border-red-200">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-circle-exclamation text-red-500"></i>
                {{ session('error') }}
            </div>
            <button @click="show=false" class="text-red-500 hover:text-red-700"><i
                    class="fa-solid fa-xmark"></i></button>
        </div>
    @endif

    <!-- FILTER FORM -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6">
        <form action="{{ route('doctor.appointments.index') }}" method="GET" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Tìm kiếm</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Mã LH hoặc tên bệnh nhân..."
                        class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Từ ngày</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                        class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Đến ngày</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                        class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Trạng thái</label>
                    <select name="status"
                        class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none bg-white">
                        <option value="">Tất cả</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Đã tiếp nhận</option>
                        <option value="checked_in" {{ request('status') === 'checked_in' ? 'selected' : '' }}>Đã checkin</option>
                        <option value="examining" {{ request('status') === 'examining' ? 'selected' : '' }}>Đang khám</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Đã huỷ</option>
                        <option value="absent" {{ request('status') === 'absent' ? 'selected' : '' }}>Vắng mặt</option>
                    </select>
                </div>
            </div>

            <div class="flex gap-2 justify-end">
                <a href="{{ route('doctor.appointments.index') }}"
                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    Đặt lại
                </a>
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
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
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã LH</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bệnh nhân</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lý do khám</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày - Giờ</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($appointments as $appt)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('doctor.appointments.show', $appt->id) }}"
                                    class="font-mono text-sm font-medium text-blue-600 hover:text-blue-800 hover:underline">
                                    {{ $appt->appointment_code }}
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">
                                    {{ $appt->patientProfile->full_name ?? '—' }}
                                </div>
                                @if ($appt->patientProfile && $appt->patientProfile->date_of_birth)
                                    <div class="text-xs text-gray-500 mt-0.5">
                                        {{ \Carbon\Carbon::parse($appt->patientProfile->date_of_birth)->age }} tuổi - {{ $appt->patientProfile->gender === 'male' ? 'Nam' : ($appt->patientProfile->gender === 'female' ? 'Nữ' : 'Khác') }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                {{ Str::limit($appt->reason, 50) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <div class="font-medium text-gray-900">
                                    {{ $appt->appointment_date ? $appt->appointment_date->format('d/m/Y') : '—' }}
                                </div>
                                <div class="text-gray-500 text-xs mt-0.5">
                                    <i class="fa-regular fa-clock mr-1"></i>
                                    {{ $appt->appointment_time ? substr($appt->appointment_time, 0, 5) : '—' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @php
                                    $color = $appt->status_color;
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800 border border-{{ $color }}-200">
                                    {{ $appt->status_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('doctor.appointments.show', $appt->id) }}" class="text-blue-600 hover:text-blue-800">
                                    Chi tiết <i class="fa-solid fa-arrow-right ml-1 text-xs"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="h-16 w-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                        <i class="fa-solid fa-calendar-xmark text-2xl text-gray-400"></i>
                                    </div>
                                    <h3 class="text-lg font-medium text-gray-900">Không tìm thấy lịch hẹn nào</h3>
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
    </div>
</x-layouts.doctor>
