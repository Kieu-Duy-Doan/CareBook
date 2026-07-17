<x-layouts.admin title="Trung tâm Khám bệnh">
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Trung tâm Khám bệnh</h2>
            <p class="text-gray-500 mt-1">Quản lý lịch sử thay đổi trạng thái và thông tin lịch hẹn</p>
        </div>
    </div>

    <x-admin.appointment-tabs />

    <!-- FILTER FORM -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6">
        <form action="{{ route('admin.appointment-logs.index') }}" method="GET" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Mã lịch hẹn</label>
                    <input type="text" name="appointment_id" value="{{ request('appointment_id') }}"
                        placeholder="VD: 12345"
                        class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Người thao tác</label>
                    <select name="changed_by"
                        class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none bg-white">
                        <option value="">Tất cả nhân sự</option>
                        @foreach ($users as $user)
                        <option value="{{ $user->id }}"
                            {{ request('changed_by') == $user->id ? 'selected' : '' }}>{{ $user->name }}
                            ({{ $user->email }})
                        </option>
                        @endforeach
                    </select>
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
                <div class="flex gap-2">
                    <button type="submit"
                        class="flex-1 bg-gray-900 hover:bg-gray-800 text-white py-2 rounded-lg text-sm font-medium transition-colors">
                        Lọc
                    </button>
                    <a href="{{ route('admin.appointment-logs.index') }}"
                        class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded-lg text-sm font-medium transition-colors">
                        Đặt lại
                    </a>
                </div>
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
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-16">
                            ID Log</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã
                            Lịch Hẹn</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Người
                            thao tác</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hành
                            động</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng
                            thái (Từ &rarr; Mới)</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lý do
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thời
                            gian</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($logs as $log)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            #{{ $log->id }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if ($log->appointment)
                            <a href="{{ route('admin.appointments.show', $log->appointment_id) }}"
                                class="font-mono text-sm font-medium text-blue-600 hover:text-blue-800 hover:underline">
                                {{ $log->appointment->appointment_code }}
                            </a>
                            @else
                            <span class="text-sm font-mono text-gray-500">ID: {{ $log->appointment_id }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $log->changedBy->name ?? 'Hệ thống / Vô danh' }}
                            </div>
                            @if ($log->changedBy)
                            <div class="text-xs text-gray-500">{{ $log->changedBy->email }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php $actionLower = strtolower($log->action); @endphp
                            @if (str_contains($actionLower, 'create') || str_contains($actionLower, 'tạo') || str_contains($actionLower, 'kê'))
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">{{ $log->action_label }}</span>
                            @elseif(str_contains($actionLower, 'update') || str_contains($actionLower, 'cập nhật') || str_contains($actionLower, 'change'))
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200">{{ $log->action_label }}</span>
                            @elseif(str_contains($actionLower, 'cancel') || str_contains($actionLower, 'hủy') || str_contains($actionLower, 'fail'))
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200">{{ $log->action_label }}</span>
                            @else
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200">{{ $log->action_label }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            @if ($log->old_status || $log->new_status)
                            <div class="flex items-center gap-2">
                                <span
                                    class="px-2 py-0.5 rounded bg-gray-100 text-gray-600 text-xs border border-gray-200">{{ $log->old_status_label ?? 'N/A' }}</span>
                                <i class="fa-solid fa-arrow-right text-gray-400 text-xs"></i>
                                <span
                                    class="px-2 py-0.5 rounded bg-blue-50 text-blue-700 text-xs border border-blue-200">{{ $log->new_status_label ?? 'N/A' }}</span>
                            </div>
                            @else
                            <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700 max-w-xs truncate" title="{{ $log->reason }}">
                            {{ $log->reason ?? '—' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            <div class="font-medium text-gray-900">{{ $log->created_at->format('d/m/Y') }}</div>
                            <div class="text-gray-500 text-xs mt-0.5"><i
                                    class="fa-regular fa-clock mr-1"></i>{{ $log->created_at->format('H:i') }}
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <div
                                    class="h-16 w-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                    <i class="fa-solid fa-clock-rotate-left text-2xl text-gray-400"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900">Không tìm thấy nhật ký nào</h3>
                                <p class="text-sm mt-1 text-gray-500">Thử thay đổi bộ lọc tìm kiếm hoặc hệ thống
                                    chưa có dữ liệu log.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-white">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</x-layouts.admin>