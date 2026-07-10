<x-layouts.doctor title="Lịch làm việc">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Lịch làm việc</h2>
            <p class="text-gray-500 mt-1">Xem các ca trực của bạn theo tuần</p>
        </div>
    </div>

    <!-- Weekly View -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        <div class="p-4 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
            <h3 class="font-bold text-gray-900"><i class="fa-solid fa-calendar-week mr-2"></i>Lịch trình trong tuần</h3>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-7 divide-y md:divide-y-0 md:divide-x divide-gray-200">
            @php
                $days = [
                    2 => 'Thứ 2',
                    3 => 'Thứ 3',
                    4 => 'Thứ 4',
                    5 => 'Thứ 5',
                    6 => 'Thứ 6',
                    7 => 'Thứ 7',
                    1 => 'Chủ nhật',
                ];
                
                // Group schedules by day_of_week
                $groupedSchedules = [];
                foreach ($schedules as $schedule) {
                    $groupedSchedules[$schedule->day_of_week][] = $schedule;
                }
                
                // Group overrides
                $overrideMap = [];
                foreach ($overrides as $override) {
                    $d = \Carbon\Carbon::parse($override->override_date);
                    $iso = $d->dayOfWeekIso + 1;
                    if ($iso == 8) $iso = 1;
                    
                    $overrideMap[$iso][] = $override;
                }
            @endphp
            
            @foreach($days as $dayVal => $dayName)
                <div class="p-3 min-h-[200px] border-r border-gray-100 last:border-r-0">
                    <div class="text-center mb-4">
                        <span class="inline-block px-3 py-1.5 {{ \Carbon\Carbon::now()->dayOfWeekIso + 1 == $dayVal || (\Carbon\Carbon::now()->dayOfWeekIso == 7 && $dayVal == 1) ? 'bg-blue-600 text-white shadow-md' : 'bg-gray-100 text-gray-700' }} text-sm font-bold rounded-lg w-full">{{ $dayName }}</span>
                    </div>
                    
                    <div class="space-y-3">
                        @if(isset($overrideMap[$dayVal]))
                            @foreach($overrideMap[$dayVal] as $ov)
                                <a href="{{ route('doctor.work-schedules.show', ['schedule' => $ov->id, 'type' => 'override']) }}" class="block p-3 rounded-xl border-l-4 text-left hover:bg-yellow-100 transition-all duration-200 bg-yellow-50 border-yellow-500 shadow-sm hover:shadow">
                                    <div class="text-xs font-bold text-yellow-800 mb-1 flex items-center justify-between">
                                        <span><i class="fa-solid fa-clock mr-1"></i> {{ \Carbon\Carbon::parse($ov->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($ov->end_time)->format('H:i') }}</span>
                                    </div>
                                    <div class="text-xs text-yellow-700 font-medium mb-2"><i class="fa-solid fa-door-open mr-1"></i> {{ $ov->room->name ?? 'Phòng' }}</div>
                                    <div class="mt-1">
                                        <span class="px-2 py-1 bg-yellow-200 text-yellow-800 text-[10px] font-bold rounded-md">Lịch ghi đè</span>
                                    </div>
                                </a>
                            @endforeach
                        @endif

                        @if(isset($groupedSchedules[$dayVal]))
                            @foreach($groupedSchedules[$dayVal] as $sch)
                                <a href="{{ route('doctor.work-schedules.show', $sch->id) }}" class="block p-3 rounded-xl border-l-4 text-left transition-all duration-200 shadow-sm hover:shadow {{ $sch->is_active ? 'bg-blue-50 border-blue-500 hover:bg-blue-100' : 'bg-gray-50 border-gray-400 opacity-70 hover:bg-gray-100' }}">
                                    <div class="text-xs font-bold {{ $sch->is_active ? 'text-blue-800' : 'text-gray-600' }} mb-1 flex items-center justify-between">
                                        <span><i class="fa-solid fa-clock mr-1"></i> {{ \Carbon\Carbon::parse($sch->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($sch->end_time)->format('H:i') }}</span>
                                    </div>
                                    <div class="text-xs {{ $sch->is_active ? 'text-blue-700' : 'text-gray-500' }} font-medium"><i class="fa-solid fa-door-open mr-1"></i> {{ $sch->room->name ?? 'Phòng' }}</div>
                                    
                                    @if(!$sch->is_active)
                                    <div class="mt-2">
                                        <span class="px-2 py-1 bg-gray-200 text-gray-700 text-[10px] font-bold rounded-md">Tạm ngưng</span>
                                    </div>
                                    @endif
                                </a>
                            @endforeach
                        @else
                            @if(!isset($overrideMap[$dayVal]))
                                <div class="flex flex-col items-center justify-center h-24 text-gray-300">
                                    <i class="fa-regular fa-calendar-xmark text-2xl mb-1"></i>
                                    <span class="text-xs">Trống</span>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-layouts.doctor>
