<x-layouts.patient-dashboard title="Kết quả khám" activeMenu="records">
    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight">Kết quả khám và Đơn thuốc</h1>
                <p class="text-slate-500 mt-2 text-sm md:text-base">Tra cứu kết quả khám bệnh, đơn thuốc và các file kết quả đính kèm.</p>
            </div>
        </div>

        <!-- Bộ lọc -->
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <form method="GET" action="{{ route('patient.records.index') }}" class="flex flex-col sm:flex-row gap-4 items-end">
                <div class="flex-1 w-full sm:w-auto">
                    <label for="appointment_code" class="block text-sm font-medium text-slate-700 mb-1.5">Mã lịch hẹn</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <i class="fa-solid fa-hashtag text-slate-400"></i>
                        </div>
                        <input type="text" name="appointment_code" id="appointment_code" value="{{ request('appointment_code') }}" class="block w-full rounded-xl border-0 py-2.5 pl-10 ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6" placeholder="Nhập mã lịch hẹn...">
                    </div>
                </div>
                <div class="flex-1 w-full sm:w-auto">
                    <label for="appointment_date" class="block text-sm font-medium text-slate-700 mb-1.5">Ngày khám</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <i class="fa-regular fa-calendar text-slate-400"></i>
                        </div>
                        <input type="date" name="appointment_date" id="appointment_date" value="{{ request('appointment_date') }}" class="block w-full rounded-xl border-0 py-2.5 pl-10 pr-4 ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
                    </div>
                </div>
                <div class="w-full sm:w-auto flex gap-2">
                    <button type="submit" class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 transition">
                        <i class="fa-solid fa-magnifying-glass"></i> Tra cứu
                    </button>
                    <a href="{{ route('patient.records.index') }}" class="inline-flex items-center justify-center rounded-xl bg-slate-100 px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-200 transition" title="Xóa bộ lọc">
                        <i class="fa-solid fa-rotate-right"></i>
                    </a>
                </div>
            </form>
        </div>

        @if ($appointments->isEmpty())
            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-8 text-center text-slate-600 shadow-sm">
                @if(request()->hasAny(['appointment_code', 'appointment_date']))
                    <p class="text-xl font-semibold">Không tìm thấy kết quả phù hợp.</p>
                    <p class="mt-2">Vui lòng thử lại với từ khóa khác.</p>
                @else
                    <p class="text-xl font-semibold">Chưa có kết quả khám hoặc đơn thuốc nào.</p>
                    <p class="mt-2">Quay lại lịch khám để kiểm tra khi có kết quả.</p>
                @endif
            </div>
        @else
            <!-- Danh sách -->
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-slate-50 text-slate-500">
                            <tr>
                                <th class="px-6 py-4 font-medium">Mã lịch hẹn</th>
                                <th class="px-6 py-4 font-medium">Ngày khám</th>
                                <th class="px-6 py-4 font-medium">Bác sĩ phụ trách</th>
                                <th class="px-6 py-4 font-medium">Trạng thái</th>
                                <th class="px-6 py-4 font-medium text-right">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 text-slate-700">
                            @foreach ($appointments as $appointment)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-6 py-4 font-semibold text-slate-900">
                                        {{ $appointment->appointment_code }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-slate-900">{{ $appointment->appointment_date?->format('d/m/Y') }}</div>
                                        <div class="text-xs text-slate-500">lúc {{ substr($appointment->appointment_time, 0, 5) }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-slate-900">{{ $appointment->doctorProfile->full_title ?? '—' }}</div>
                                        <div class="text-xs text-slate-500">{{ $appointment->room->name ?? '—' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $appointment->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : ($appointment->status === 'cancelled' ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-700') }}">
                                            {{ $appointment->status_label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('patient.records.show', $appointment->id) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition shadow-sm">
                                            <i class="fa-solid fa-eye text-blue-600"></i> Xem chi tiết
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6">
                {{ $appointments->links() }}
            </div>
        @endif
    </div>
</x-layouts.patient-dashboard>