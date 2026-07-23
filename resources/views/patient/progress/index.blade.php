<x-layouts.patient-dashboard title="Tiến trình khám" activeMenu="progress">
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Tiến trình khám hiện tại</h1>
            <p class="text-slate-500 mt-2">Theo dõi các bước khám bệnh đang diễn ra của bạn.</p>
        </div>

        @if ($appointments->isEmpty())
            <div class="rounded-3xl border border-slate-200 bg-white p-8 text-center shadow-sm">
                <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-3xl bg-blue-50 text-blue-500">
                    <i class="fa-solid fa-route text-3xl"></i>
                </div>
                <h2 class="text-lg font-semibold text-slate-900">Không có lịch khám nào đang diễn ra</h2>
                <p class="mt-2 text-sm text-slate-500">Hiện tại bạn không có lịch hẹn nào đang trong quá trình khám (đã check-in). Khi bạn check-in tại bệnh viện, tiến trình khám sẽ hiển thị ở đây.</p>
                <a href="{{ route('patient.appointments.index') }}" class="mt-6 inline-flex items-center justify-center rounded-full bg-slate-100 px-6 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-200 transition">
                    <i class="fa-regular fa-calendar-check mr-2"></i> Xem lịch sử đặt lịch
                </a>
            </div>
        @else
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach ($appointments as $appointment)
                    @php
                        $visits = $appointment->clinicalVisits->sortBy('visit_order');
                        $completed = $visits->where('status', 'completed')->count();
                        $total = $visits->count();
                        $percent = $total > 0 ? round(($completed / $total) * 100) : 0;
                        $currentVisit = $visits->whereIn('status', ['in_progress'])->first() ?? $visits->where('status', 'waiting')->first();
                    @endphp
                    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md transition">
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div>
                                <div class="text-xs uppercase tracking-[0.2em] text-slate-400 mb-1">{{ $appointment->appointment_code }}</div>
                                <div class="font-semibold text-slate-900">{{ $appointment->appointment_date?->format('d/m/Y') }} lúc {{ substr($appointment->appointment_time, 0, 5) }}</div>
                                <div class="text-sm text-slate-500 mt-1">BS: {{ $appointment->doctorProfile->full_title ?? '—' }}</div>
                            </div>
                            <span class="rounded-full px-3 py-1 text-xs font-semibold bg-blue-100 text-blue-700 whitespace-nowrap">
                                {{ $appointment->status_label }}
                            </span>
                        </div>

                        <!-- Progress bar -->
                        <div class="mb-3">
                            <div class="flex items-center justify-between text-xs text-slate-500 mb-1.5">
                                <span>Tiến trình</span>
                                <span class="font-semibold text-slate-700">{{ $completed }}/{{ $total }} phòng</span>
                            </div>
                            <div class="h-2.5 w-full rounded-full bg-slate-100 overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-500 {{ $percent === 100 ? 'bg-emerald-500' : 'bg-blue-500' }}" style="width: {{ $percent }}%"></div>
                            </div>
                        </div>

                        @if ($currentVisit)
                            <div class="text-xs text-slate-500">
                                {{ $currentVisit->status === 'in_progress' ? 'Đang khám tại' : 'Chờ khám tại' }}:
                                <span class="font-semibold text-slate-700">{{ $currentVisit->room->name ?? '—' }}</span>
                                @if ($currentVisit->room)
                                    <span class="text-slate-400 ml-1">
                                        @if ($currentVisit->room->room_number)P.{{ $currentVisit->room->room_number }}@endif
                                        @if ($currentVisit->room->floor) · Tầng {{ $currentVisit->room->floor }}@endif
                                        @if ($currentVisit->room->building) · {{ $currentVisit->room->building }}@endif
                                    </span>
                                @endif
                            </div>
                        @endif

                        <a href="{{ route('patient.progress.show', $appointment->id) }}" class="mt-4 w-full inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-500 transition">
                            <i class="fa-solid fa-route"></i> Xem tiến trình
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.patient-dashboard>
