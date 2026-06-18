<x-layouts.admin title="Trung tâm Khám bệnh">
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Trung tâm Khám bệnh</h2>
            <p class="text-gray-500 mt-1">Danh sách lượt khám và đặt chỗ</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.appointments.calendar') }}"
                class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2 shadow-sm">
                <i class="fa-solid fa-calendar-days"></i> Dạng lịch
            </a>
            <a href="{{ route('admin.appointments.export-csv') }}?{{ http_build_query(request()->query()) }}"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2 shadow-sm">
                <i class="fa-solid fa-file-csv"></i> Xuất CSV
            </a>
            <a href="{{ route('admin.appointments.create') }}"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2 shadow-sm">
                <i class="fa-solid fa-plus"></i> Thêm lịch hẹn
            </a>
        </div>
    </div>

    <x-admin.appointment-tabs />

    <!-- Alert -->
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

    <!-- FILTER FORM -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6">
        <form action="{{ route('admin.appointments.index') }}" method="GET" class="space-y-4">
            <!-- Hàng 1 -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Tìm mã LH hoặc tên bệnh nhân..."
                        class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">
                </div>
                <div>
                    <select name="doctor_id"
                        class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none bg-white">
                        <option value="">Tất cả bác sĩ</option>
                        @foreach ($doctors as $doc)
                            <option value="{{ $doc->id }}"
                                {{ request('doctor_id') == $doc->id ? 'selected' : '' }}>{{ $doc->full_title }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select name="specialty_id"
                        class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none bg-white">
                        <option value="">Tất cả chuyên khoa</option>
                        @foreach ($specialties as $sp)
                            <option value="{{ $sp->id }}"
                                {{ request('specialty_id') == $sp->id ? 'selected' : '' }}>{{ $sp->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select name="status"
                        class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none bg-white">
                        <option value="">Tất cả trạng thái</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Chờ khám
                        </option>
                        <option value="checked_in" {{ request('status') === 'checked_in' ? 'selected' : '' }}>Đã tiếp
                            nhận</option>
                        <option value="examining" {{ request('status') === 'examining' ? 'selected' : '' }}>Đang khám
                        </option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Hoàn thành
                        </option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Đã huỷ
                        </option>
                        <option value="absent" {{ request('status') === 'absent' ? 'selected' : '' }}>Vắng mặt</option>
                    </select>
                </div>
            </div>

            <!-- Hàng 2 -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
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
                    <label class="block text-xs font-medium text-gray-500 mb-1">Nguồn đặt</label>
                    <select name="source"
                        class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none bg-white">
                        <option value="">Tất cả nguồn</option>
                        <option value="web" {{ request('source') === 'web' ? 'selected' : '' }}>Web</option>
                        <option value="counter" {{ request('source') === 'counter' ? 'selected' : '' }}>Quầy lễ tân
                        </option>
                        <option value="chatbot" {{ request('source') === 'chatbot' ? 'selected' : '' }}>Chatbot
                        </option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit"
                        class="flex-1 bg-gray-900 hover:bg-gray-800 text-white py-2 rounded-lg text-sm font-medium transition-colors">
                        Lọc
                    </button>
                    <a href="{{ route('admin.appointments.index') }}"
                        class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded-lg text-sm font-medium transition-colors">
                        Đặt lại
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Stats Bar -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-4">
        <div class="text-sm text-gray-600 font-medium">
            Tìm thấy <span class="text-gray-900 font-bold">{{ $totalCount }}</span> lịch hẹn
        </div>
        <div class="flex flex-wrap items-center gap-2 text-xs">
            <span
                class="inline-flex items-center px-2 py-1 rounded bg-yellow-100 text-yellow-800 border border-yellow-200">
                Chờ khám: {{ $statusCounts['pending'] ?? 0 }}
            </span>
            <span class="inline-flex items-center px-2 py-1 rounded bg-blue-100 text-blue-800 border border-blue-200">
                Đã tiếp nhận: {{ $statusCounts['checked_in'] ?? 0 }}
            </span>
            <span
                class="inline-flex items-center px-2 py-1 rounded bg-purple-100 text-purple-800 border border-purple-200">
                Đang khám: {{ $statusCounts['examining'] ?? 0 }}
            </span>
            <span
                class="inline-flex items-center px-2 py-1 rounded bg-green-100 text-green-800 border border-green-200">
                Hoàn thành: {{ $statusCounts['completed'] ?? 0 }}
            </span>
            <span class="inline-flex items-center px-2 py-1 rounded bg-red-100 text-red-800 border border-red-200">
                Đã huỷ: {{ $statusCounts['cancelled'] ?? 0 }}
            </span>
            <span class="inline-flex items-center px-2 py-1 rounded bg-gray-100 text-gray-800 border border-gray-200">
                Vắng mặt: {{ $statusCounts['absent'] ?? 0 }}
            </span>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã LH
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bệnh
                            nhân</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bác
                            sĩ</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Chuyên khoa</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày
                            - Giờ</th>
                        <th scope="col"
                            class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Trạng thái</th>
                        <th scope="col"
                            class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Nguồn</th>
                        <th scope="col"
                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Thao tác</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($appointments as $appt)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('admin.appointments.show', $appt->id) }}"
                                    class="font-mono text-sm font-medium text-blue-600 hover:text-blue-800 hover:underline">
                                    {{ $appt->appointment_code }}
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">
                                    {{ $appt->patientProfile->full_name ?? '—' }}</div>
                                @if ($appt->patientProfile && $appt->patientProfile->date_of_birth)
                                    <div class="text-xs text-gray-500 mt-0.5">
                                        {{ \Carbon\Carbon::parse($appt->patientProfile->date_of_birth)->age }} tuổi
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $appt->doctor->full_title ?? '—' }}
                                </div>
                                @if ($appt->doctor && current($appt->doctor->specialties->where('pivot.is_primary', true)->toArray()))
                                    @php
                                        $primary = $appt->doctor->specialties->where('pivot.is_primary', true)->first();
                                    @endphp
                                    <div class="text-[10px] text-gray-500 mt-0.5">{{ $primary ? $primary->name : '' }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                {{ $appt->specialty->name ?? '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <div class="font-medium text-gray-900">
                                    {{ $appt->appointment_date ? $appt->appointment_date->format('d/m/Y') : '—' }}
                                </div>
                                <div class="text-gray-500 text-xs mt-0.5"><i
                                        class="fa-regular fa-clock mr-1"></i>{{ $appt->appointment_time ? substr($appt->appointment_time, 0, 5) : '—' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @php
                                    $color = $appt->status_color;
                                @endphp
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800 border border-{{ $color }}-200">
                                    {{ $appt->status_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if ($appt->source === 'web')
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-blue-50 text-blue-600 border border-blue-100">Web</span>
                                @elseif($appt->source === 'counter')
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-orange-50 text-orange-600 border border-orange-100">Quầy</span>
                                @elseif($appt->source === 'chatbot')
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-purple-50 text-purple-600 border border-purple-100">Chatbot</span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-gray-50 text-gray-600 border border-gray-100">Khác</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('admin.appointments.show', $appt->id) }}"
                                        class="text-gray-400 hover:text-blue-600 transition-colors"
                                        title="Xem chi tiết">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.appointments.edit', $appt->id) }}"
                                        class="text-gray-400 hover:text-yellow-600 transition-colors"
                                        title="Chỉnh sửa">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form action="{{ route('admin.appointments.destroy', $appt->id) }}"
                                        method="POST" class="inline-block"
                                        onsubmit="return confirm('Bạn có chắc chắn muốn xoá lịch hẹn này?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="text-gray-400 hover:text-red-600 transition-colors" title="Xoá">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <div
                                        class="h-16 w-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                        <i class="fa-solid fa-calendar-xmark text-2xl text-gray-400"></i>
                                    </div>
                                    <h3 class="text-lg font-medium text-gray-900">Không tìm thấy lịch hẹn nào</h3>
                                    <p class="text-sm mt-1 text-gray-500">Thử thay đổi bộ lọc tìm kiếm.</p>
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
</x-layouts.admin>
