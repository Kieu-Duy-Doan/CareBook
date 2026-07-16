<x-layouts.doctor title="Giám sát lâm sàng">
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Giám sát lâm sàng & Thanh toán</h2>
            <p class="text-gray-500 mt-1">Danh sách bệnh nhân đang trong quá trình khám lâm sàng</p>
        </div>
    </div>

    @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" style="display: none;"
            class="bg-green-50 text-green-800 p-4 rounded-lg mb-6 flex items-center justify-between border border-green-200">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-green-500"></i>
                {{ session('success') }}
            </div>
            <button @click="show=false" class="text-green-500 hover:text-green-700"><i class="fa-solid fa-xmark"></i></button>
        </div>
    @endif

    {{-- Bộ lọc --}}
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6">
        <form action="{{ route('doctor.clinical-visits.index') }}" method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Tìm kiếm bệnh nhân hoặc mã LH</label>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Mã LH, Tên bệnh nhân..."
                    class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">
            </div>
            <div class="min-w-[160px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Trạng thái lịch hẹn</label>
                <select name="status" class="block w-full py-2 px-3 border border-gray-300 rounded-lg text-sm outline-none bg-white">
                    <option value="">-- Tất cả --</option>
                    <option value="examining" {{ request('status') === 'examining' ? 'selected' : '' }}>Đang khám</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Đã hoàn thành</option>
                    <option value="checked_in" {{ request('status') === 'checked_in' ? 'selected' : '' }}>Đã check-in</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition-colors">
                    <i class="fa-solid fa-magnifying-glass mr-1"></i> Tìm
                </button>
                <a href="{{ route('doctor.clinical-visits.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    Đặt lại
                </a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã LH / Bệnh nhân</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày khám</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái lịch</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tiến trình CLS</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vai trò của bạn</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($appointments as $appt)
                        @php
                            $myVisits        = $appt->clinicalVisits;
                            $isOriginDoc     = $myVisits->contains('is_origin', true);
                            $subVisitsOfMine = $myVisits->where('is_origin', false);
                            $totalMySubs     = $subVisitsOfMine->count();
                            $doneMySubs      = $subVisitsOfMine->whereIn('status', ['completed', 'refused'])->count();
                            $allSubsDone     = $totalMySubs > 0 && $doneMySubs === $totalMySubs;

                            // Cảnh báo: bác sĩ gốc, đang khám, chưa ghi kết luận
                            $needsAttention  = $appt->status === 'examining'
                                && $isOriginDoc
                                && $appt->medicalRecord === null;
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors {{ $needsAttention ? 'bg-amber-50 hover:bg-amber-100' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($needsAttention)
                                    <span class="inline-flex items-center gap-1 text-xs text-amber-600 font-semibold mb-1">
                                        <i class="fa-solid fa-triangle-exclamation"></i> Cần kết luận
                                    </span><br>
                                @endif
                                <div class="font-mono text-sm text-blue-600 font-medium">{{ $appt->appointment_code }}</div>
                                <div class="font-bold text-gray-900 mt-0.5">{{ $appt->patientProfile->full_name ?? '—' }}</div>
                                <div class="text-xs text-gray-400 mt-0.5">{{ $appt->patientProfile->phone ?? '' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <div class="font-medium">{{ $appt->appointment_date ? $appt->appointment_date->format('d/m/Y') : '—' }}</div>
                                <div class="text-xs text-gray-400">{{ $appt->appointment_time ? \Carbon\Carbon::parse($appt->appointment_time)->format('H:i') : '' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @php $color = $appt->status_color; @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800 border border-{{ $color }}-200">
                                    {{ $appt->status_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if ($isOriginDoc)
                                    @if ($totalMySubs === 0)
                                        <span class="text-xs text-gray-400 italic">Chưa có chỉ định</span>
                                    @else
                                        <div class="flex items-center gap-2 mb-1">
                                            <div class="w-24 bg-gray-200 rounded-full h-1.5">
                                                <div class="h-1.5 rounded-full {{ $allSubsDone ? 'bg-green-500' : 'bg-blue-500' }}"
                                                     style="width: {{ round(($doneMySubs / $totalMySubs) * 100) }}%"></div>
                                            </div>
                                            <span class="text-xs font-semibold {{ $allSubsDone ? 'text-green-600' : 'text-amber-600' }}">
                                                {{ $doneMySubs }}/{{ $totalMySubs }}
                                            </span>
                                        </div>
                                        @if ($allSubsDone)
                                            <span class="text-xs text-green-600 font-medium">
                                                <i class="fa-solid fa-circle-check mr-0.5"></i>Tất cả xong
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400">Chờ kết quả...</span>
                                        @endif
                                    @endif
                                @else
                                    @foreach($subVisitsOfMine as $sv)
                                        @if($sv->status === 'waiting')
                                            <span class="inline-flex items-center gap-1 text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full">
                                                <i class="fa-solid fa-clock text-[10px]"></i> Chờ khám
                                            </span>
                                        @elseif($sv->status === 'in_progress')
                                            <span class="inline-flex items-center gap-1 text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">
                                                <i class="fa-solid fa-spinner text-[10px]"></i> Đang khám
                                            </span>
                                        @elseif($sv->status === 'completed')
                                            <span class="inline-flex items-center gap-1 text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">
                                                <i class="fa-solid fa-check text-[10px]"></i> Đã xong
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400">{{ $sv->status }}</span>
                                        @endif
                                        <div class="text-xs text-gray-400 mt-0.5 truncate max-w-[120px]">{{ $sv->room->name ?? '' }}</div>
                                    @endforeach
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($isOriginDoc)
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold bg-blue-100 text-blue-700 px-2.5 py-1 rounded-full">
                                        <i class="fa-solid fa-house-medical text-[10px]"></i> Bác sĩ chính
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold bg-purple-100 text-purple-700 px-2.5 py-1 rounded-full">
                                        <i class="fa-solid fa-microscope text-[10px]"></i> Cận lâm sàng
                                    </span>
                                    <div class="text-xs text-gray-400 mt-0.5">
                                        {{ $myVisits->first()?->room?->name ?? '' }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('doctor.clinical-visits.show', $appt->id) }}"
                                   class="inline-flex items-center gap-1.5 {{ $needsAttention ? 'bg-amber-500 hover:bg-amber-600 text-white' : 'text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100' }} px-3 py-1.5 rounded-lg transition text-xs font-semibold">
                                    {{ $needsAttention ? 'Ghi kết luận' : 'Xem chi tiết' }}
                                    <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <div class="h-16 w-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fa-solid fa-microscope text-2xl text-gray-400"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900">Không có dữ liệu</h3>
                                <p class="text-sm text-gray-500 mt-1">Không có lịch hẹn nào phù hợp với bộ lọc.</p>
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
