<x-layouts.receptionist title="Bác sĩ trực hôm nay">
    <div class="space-y-5">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Bác sĩ trực hôm nay</h2>
                <p class="text-gray-500 mt-1 text-sm">
                    <i class="fa-regular fa-calendar-days mr-1"></i>
                    {{ $today->isoFormat('dddd, DD/MM/YYYY') }}
                </p>
            </div>
            <span class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full text-sm font-semibold">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                {{ $schedules->count() }} ca trực
            </span>
        </div>

        @if($byRoom->isEmpty())
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-12 text-center text-gray-400">
                <i class="fa-solid fa-calendar-xmark text-4xl mb-3 block"></i>
                <p class="font-medium">Không có lịch trực nào hôm nay.</p>
                <p class="text-sm mt-1">Hôm nay là {{ $today->isoFormat('dddd') }}, không có ca trực được cấu hình.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                @foreach($byRoom as $roomId => $roomSchedules)
                    @php $room = $roomSchedules->first()->room; @endphp
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                        <!-- Room header -->
                        <div class="px-5 py-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-indigo-50 flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                                <i class="fa-solid fa-door-open text-blue-600 text-sm"></i>
                            </div>
                            <div>
                                <div class="font-bold text-gray-900 text-sm">{{ $room->name ?? 'Phòng #'.$roomId }}</div>
                                @if($room && $room->location)
                                    <div class="text-xs text-gray-500">{{ $room->location }}</div>
                                @endif
                            </div>
                        </div>

                        <!-- Doctors list -->
                        <div class="divide-y divide-gray-50">
                            @foreach($roomSchedules as $schedule)
                                @php
                                    $doctor = $schedule->doctor;
                                    $now = \Carbon\Carbon::now()->format('H:i:s');
                                    $isActive = $now >= $schedule->start_time && $now <= $schedule->end_time;
                                @endphp
                                <div class="px-5 py-4 flex items-center gap-3">
                                    <!-- Shift badge -->
                                    <div class="flex-shrink-0 w-16 text-center">
                                        {!! $schedule->shift_badge !!}
                                    </div>

                                    <!-- Doctor info -->
                                    <div class="flex-1 min-w-0">
                                        <div class="font-semibold text-gray-900 text-sm truncate">
                                            {{ $doctor->full_title ?? 'Bác sĩ không xác định' }}
                                        </div>
                                        <div class="text-xs text-gray-500 mt-0.5">
                                            <i class="fa-regular fa-clock mr-1"></i>
                                            {{ $schedule->time_range }}
                                        </div>
                                    </div>

                                    <!-- Status -->
                                    @if($isActive)
                                        <span class="flex-shrink-0 inline-flex items-center gap-1 text-xs font-medium text-emerald-700 bg-emerald-50 px-2 py-1 rounded-full border border-emerald-200">
                                            <span class="relative flex h-1.5 w-1.5">
                                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                                <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-500"></span>
                                            </span>
                                            Đang trực
                                        </span>
                                    @else
                                        <span class="flex-shrink-0 inline-flex items-center text-xs font-medium text-gray-400 bg-gray-50 px-2 py-1 rounded-full border border-gray-200">
                                            Chờ ca
                                        </span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.receptionist>
