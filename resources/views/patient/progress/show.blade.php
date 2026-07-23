<x-layouts.patient-dashboard title="Tiến trình khám" activeMenu="progress">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('patient.progress.index') }}" class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-700 transition">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Tiến trình khám</h1>
                    <p class="text-slate-500 text-sm mt-0.5">Mã: {{ $appointment->appointment_code }}</p>
                </div>
            </div>
            <a href="{{ route('patient.appointments.show', $appointment->id) }}" class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition shadow-sm">
                <i class="fa-solid fa-calendar-check text-blue-600"></i> Chi tiết lịch khám
            </a>
        </div>

        <!-- Info Card -->
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="grid gap-3 sm:grid-cols-3 text-sm">
                <div class="rounded-2xl bg-slate-50 p-3.5">
                    <div class="text-xs uppercase tracking-[0.2em] text-slate-400 mb-1">Ngày khám</div>
                    <div class="font-semibold text-slate-900">{{ $appointment->appointment_date?->format('d/m/Y') }} · {{ substr($appointment->appointment_time, 0, 5) }}</div>
                </div>
                <div class="rounded-2xl bg-slate-50 p-3.5">
                    <div class="text-xs uppercase tracking-[0.2em] text-slate-400 mb-1">Bác sĩ</div>
                    <div class="font-semibold text-slate-900">{{ $appointment->doctorProfile->full_title ?? '—' }}</div>
                </div>
                <div class="rounded-2xl bg-slate-50 p-3.5">
                    <div class="text-xs uppercase tracking-[0.2em] text-slate-400 mb-1">Check-in lúc</div>
                    <div class="font-semibold text-slate-900">{{ $appointment->checked_in_at?->format('H:i') ?? '—' }}</div>
                </div>
            </div>
        </div>

        <!-- Progress Summary -->
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            @php
                $percent = $totalCount > 0 ? round(($completedCount / $totalCount) * 100) : 0;
                $inProgress = $visits->where('status', 'in_progress')->first();
            @endphp
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-lg font-semibold text-slate-900">Tổng quan</h2>
                <span class="rounded-full px-3 py-1 text-sm font-semibold {{ $percent === 100 ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700' }}">
                    {{ $completedCount }}/{{ $totalCount }} phòng hoàn thành
                </span>
            </div>
            <div class="h-3 w-full rounded-full bg-slate-100 overflow-hidden">
                <div class="h-full rounded-full transition-all duration-700 {{ $percent === 100 ? 'bg-emerald-500' : 'bg-blue-500' }}" style="width: {{ $percent }}%"></div>
            </div>
            @if ($inProgress)
                <div class="mt-3 flex items-center gap-2 text-sm text-blue-700 bg-blue-50 rounded-xl px-4 py-2.5">
                    <span class="relative flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-blue-500"></span>
                    </span>
                    <span>Đang khám tại: <strong>{{ $inProgress->room->name ?? '—' }}</strong></span>
                </div>
            @elseif ($percent === 100)
                <div class="mt-3 flex items-center gap-2 text-sm text-emerald-700 bg-emerald-50 rounded-xl px-4 py-2.5">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Đã hoàn thành tất cả các phòng khám!</span>
                </div>
            @endif
        </div>

        <!-- Vertical Stepper -->
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900 mb-6">Chi tiết các phòng khám</h2>

            <div class="relative">
                {{-- Check-in Step --}}
                <div class="flex gap-4 mb-0">
                    <div class="flex flex-col items-center">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center bg-emerald-500 text-white shadow-md shadow-emerald-200">
                            <i class="fa-solid fa-right-to-bracket text-sm"></i>
                        </div>
                        @if ($visits->isNotEmpty())
                            <div class="w-0.5 flex-1 bg-emerald-300 my-1"></div>
                        @endif
                    </div>
                    <div class="pb-8 flex-1">
                        <div class="font-semibold text-slate-900">Check-in</div>
                        <div class="text-sm text-slate-500 mt-0.5">
                            @if ($appointment->checked_in_at)
                                <i class="fa-regular fa-clock mr-1"></i> {{ $appointment->checked_in_at->format('H:i, d/m/Y') }}
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Visit Steps --}}
                @foreach ($visits as $index => $visit)
                    @php
                        $isCompleted = $visit->status === 'completed';
                        $isInProgress = $visit->status === 'in_progress';
                        $isWaiting = $visit->status === 'waiting';
                        $isRefused = $visit->status === 'refused';
                        $isLast = $loop->last;
                    @endphp
                    <div class="flex gap-4 mb-0">
                        <div class="flex flex-col items-center">
                            {{-- Circle indicator --}}
                            @if ($isCompleted)
                                <div class="w-10 h-10 rounded-full flex items-center justify-center bg-emerald-500 text-white shadow-md shadow-emerald-200">
                                    <i class="fa-solid fa-check text-sm"></i>
                                </div>
                            @elseif ($isInProgress)
                                <div class="w-10 h-10 rounded-full flex items-center justify-center bg-blue-500 text-white shadow-md shadow-blue-200 relative">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-30"></span>
                                    <i class="fa-solid fa-stethoscope text-sm relative z-10"></i>
                                </div>
                            @elseif ($isRefused)
                                <div class="w-10 h-10 rounded-full flex items-center justify-center bg-rose-500 text-white shadow-md shadow-rose-200">
                                    <i class="fa-solid fa-xmark text-sm"></i>
                                </div>
                            @else
                                <div class="w-10 h-10 rounded-full flex items-center justify-center bg-slate-200 text-slate-400">
                                    <span class="text-sm font-bold">{{ $visit->visit_order }}</span>
                                </div>
                            @endif

                            {{-- Connector line --}}
                            @if (!$isLast)
                                <div class="w-0.5 flex-1 my-1 {{ $isCompleted ? 'bg-emerald-300' : ($isInProgress ? 'bg-blue-200' : 'bg-slate-200') }}"></div>
                            @endif
                        </div>

                        <div class="{{ !$isLast ? 'pb-8' : '' }} flex-1">
                            <div class="rounded-2xl border p-4 {{ $isInProgress ? 'border-blue-200 bg-blue-50/50 ring-1 ring-blue-100' : ($isCompleted ? 'border-emerald-200 bg-emerald-50/30' : ($isRefused ? 'border-rose-200 bg-rose-50/30' : 'border-slate-200 bg-slate-50/50')) }}">
                                <div class="flex items-start justify-between gap-2 mb-1">
                                    <div class="font-semibold text-slate-900 text-sm">
                                        {{ $visit->is_origin ? 'Khám Bệnh Ban Đầu' : 'Khám Cận Lâm Sàng' }}
                                    </div>
                                    @if ($isCompleted)
                                        <span class="rounded-full bg-emerald-100 text-emerald-700 px-2 py-0.5 text-[11px] font-semibold whitespace-nowrap">Hoàn thành</span>
                                    @elseif ($isInProgress)
                                        <span class="rounded-full bg-blue-100 text-blue-700 px-2 py-0.5 text-[11px] font-semibold whitespace-nowrap">Đang khám</span>
                                    @elseif ($isRefused)
                                        <span class="rounded-full bg-rose-100 text-rose-700 px-2 py-0.5 text-[11px] font-semibold whitespace-nowrap">Từ chối</span>
                                    @else
                                        <span class="rounded-full bg-slate-100 text-slate-500 px-2 py-0.5 text-[11px] font-semibold whitespace-nowrap">Chờ khám</span>
                                    @endif
                                </div>

                                <div class="flex items-center gap-1.5 text-sm text-slate-600 mb-1">
                                    <i class="fa-solid fa-door-open text-xs text-slate-400"></i>
                                    {{ $visit->room->name ?? 'Không rõ phòng' }}
                                </div>
                                @if ($visit->room)
                                    <div class="flex flex-wrap gap-x-3 gap-y-0.5 text-xs text-slate-400 mb-2">
                                        @if ($visit->room->room_number)
                                            <span><i class="fa-solid fa-hashtag mr-0.5"></i>Phòng {{ $visit->room->room_number }}</span>
                                        @endif
                                        @if ($visit->room->floor)
                                            <span><i class="fa-solid fa-stairs mr-0.5"></i>Tầng {{ $visit->room->floor }}</span>
                                        @endif
                                        @if ($visit->room->building)
                                            <span><i class="fa-solid fa-building mr-0.5"></i>{{ $visit->room->building }}</span>
                                        @endif
                                    </div>
                                @endif

                                <div class="text-xs text-slate-500">
                                    <i class="fa-solid fa-user-doctor mr-1"></i>
                                    BS: {{ $visit->doctorProfile->user->full_name ?? '—' }}
                                </div>

                                @if ($visit->started_at || $visit->completed_at)
                                    <div class="mt-2 pt-2 border-t {{ $isInProgress ? 'border-blue-200' : ($isCompleted ? 'border-emerald-200' : 'border-slate-200') }} text-xs text-slate-500 flex flex-wrap gap-x-4 gap-y-1">
                                        @if ($visit->started_at)
                                            <span><i class="fa-regular fa-clock mr-1"></i> Bắt đầu: {{ $visit->started_at->format('H:i') }}</span>
                                        @endif
                                        @if ($visit->completed_at)
                                            <span><i class="fa-solid fa-flag-checkered mr-1"></i> Kết thúc: {{ $visit->completed_at->format('H:i') }}</span>
                                        @endif
                                    </div>
                                @endif

                                @if ($isRefused && $visit->refusal_reason)
                                    <div class="mt-2 pt-2 border-t border-rose-200 text-xs text-rose-600">
                                        <i class="fa-solid fa-triangle-exclamation mr-1"></i> {{ $visit->refusal_reason }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-layouts.patient-dashboard>
