<x-layouts.patient-dashboard title="Lịch sử đặt lịch khám" activeMenu="appointments">
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Lịch sử đặt lịch khám</h1>
                <p class="text-slate-500 mt-2">Xem và quản lý các lịch hẹn đã đặt, bao gồm trạng thái và chi tiết.</p>
            </div>
            <a href="{{ route('patient.booking.index') }}" class="inline-flex items-center justify-center gap-2 rounded-full bg-secondary px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-secondary/20 hover:bg-secondary-dark transition">
                <i class="fa-solid fa-calendar-plus"></i> Đặt lịch khám mới
            </a>
        </div>

        @if (session('success'))
            <div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-800 shadow-sm">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="rounded-3xl border border-rose-200 bg-rose-50 p-4 text-rose-800 shadow-sm">
                {{ session('error') }}
            </div>
        @endif

        @if ($appointments->isEmpty())
            <div class="rounded-3xl border border-slate-200 bg-white p-8 text-center shadow-sm">
                <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-3xl bg-primary/5 text-primary">
                    <i class="fa-regular fa-calendar-xmark text-3xl"></i>
                </div>
                <h2 class="text-lg font-semibold text-slate-900">Chưa có lịch hẹn nào</h2>
                <p class="mt-2 text-sm text-slate-500">Bạn chưa đặt lịch khám nào. Hãy đặt lịch ngay để quản lý thông tin và kết quả khám dễ dàng.</p>
                <a href="{{ route('patient.booking.index') }}" class="mt-6 inline-flex items-center justify-center rounded-full bg-secondary px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-secondary/20 hover:bg-secondary-dark transition">Đặt lịch khám</a>
            </div>
        @else
            <div class="grid gap-4">
                <div class="space-y-4 lg:hidden">
                    @foreach ($appointments as $appointment)
                        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <div class="text-xs uppercase tracking-[0.25em] text-slate-400">{{ $appointment->appointment_date?->format('d/m/Y') ?? '—' }} - {{ $appointment->appointment_time ? substr($appointment->appointment_time, 0, 5) : '—' }}</div>
                                    <h2 class="mt-2 text-lg font-semibold text-slate-900">{{ $appointment->appointment_code }}</h2>
                                    <div class="mt-2 text-sm text-slate-500">Bác sĩ: {{ $appointment->doctorProfile->full_title ?? 'Chưa chỉ định' }}</div>
                                </div>
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $appointment->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : ($appointment->status === 'cancelled' ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-700') }}">{{ $appointment->status_label }}</span>
                            </div>
                            <div class="mt-4 flex flex-wrap gap-2 text-sm">
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-slate-600">{{ $appointment->source_label }}</span>
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-slate-600">{{ $appointment->specialty->name ?? 'Không có chuyên khoa' }}</span>
                            </div>
                            <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <a href="{{ route('patient.appointments.show', $appointment->id) }}" class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 transition">Xem chi tiết</a>
                                @if ($appointment->status === 'pending')
                                    <form action="{{ route('patient.appointments.cancel', $appointment->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center justify-center rounded-full bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700 transition">Huỷ</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="hidden lg:block rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-6 py-4 text-left font-semibold text-slate-500 uppercase tracking-[0.15em]">Mã lịch hẹn</th>
                                    <th class="px-6 py-4 text-left font-semibold text-slate-500 uppercase tracking-[0.15em]">Ngày giờ</th>
                                    <th class="px-6 py-4 text-left font-semibold text-slate-500 uppercase tracking-[0.15em]">Bác sĩ / chuyên khoa</th>
                                    <th class="px-6 py-4 text-left font-semibold text-slate-500 uppercase tracking-[0.15em]">Nguồn</th>
                                    <th class="px-6 py-4 text-left font-semibold text-slate-500 uppercase tracking-[0.15em]">Trạng thái</th>
                                    <th class="px-6 py-4 text-right font-semibold text-slate-500 uppercase tracking-[0.15em]">Hành động</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                                @foreach ($appointments as $appointment)
                                    <tr>
                                        <td class="px-6 py-4 font-medium text-slate-900">{{ $appointment->appointment_code }}</td>
                                        <td class="px-6 py-4 text-slate-600">{{ $appointment->appointment_date?->format('d/m/Y') ?? '—' }}<br>{{ $appointment->appointment_time ? substr($appointment->appointment_time, 0, 5) : '—' }}</td>
                                        <td class="px-6 py-4 text-slate-600">{{ $appointment->doctorProfile->full_title ?? 'Chưa có' }}<br><span class="text-slate-400">{{ $appointment->specialty->name ?? '—' }}</span></td>
                                        <td class="px-6 py-4 text-slate-600">{{ $appointment->source_label }}</td>
                                        <td class="px-6 py-4"><span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $appointment->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : ($appointment->status === 'cancelled' ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-700') }}">{{ $appointment->status_label }}</span></td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex flex-wrap justify-end gap-2">
                                                <a href="{{ route('patient.appointments.show', $appointment->id) }}" class="rounded-full border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition">Chi tiết</a>
                                                @if ($appointment->status === 'pending')
                                                    <form action="{{ route('patient.appointments.cancel', $appointment->id) }}" method="POST" class="inline">@csrf<button type="submit" class="rounded-full bg-rose-600 px-3 py-2 text-xs font-semibold text-white hover:bg-rose-700 transition">Huỷ</button></form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-4 text-sm text-slate-500 shadow-sm">
                    {{ $appointments->links() }}
                </div>
            </div>
        @endif
    </div>
</x-layouts.patient-dashboard>
