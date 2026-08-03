<x-layouts.doctor>
    <x-slot name="title">Bảng điều khiển</x-slot>

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Xin chào, Bác sĩ {{ Auth::user()->full_name }}!</h2>
            <p class="text-gray-500">Dưới đây là tổng quan lịch làm việc của bạn.</p>
        </div>
        
        <form method="GET" action="{{ route('doctor.dashboard') }}" class="flex items-center space-x-3 bg-white p-2 rounded-lg shadow-sm border border-gray-100">
            <div>
                <input type="date" name="from_date" value="{{ $fromDate }}" class="block w-full rounded-md border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
            </div>
            <span class="text-gray-500 font-medium">-</span>
            <div>
                <input type="date" name="to_date" value="{{ $toDate }}" class="block w-full rounded-md border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
            </div>
            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                Lọc
            </button>
        </form>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        @if($doctorType === 'clinical')
            <!-- Đang chờ khám -->
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100 flex items-center">
                <div class="rounded-full bg-yellow-100 p-3 mr-4">
                    <i class="fa-solid fa-hourglass-half text-yellow-600 text-xl w-6 h-6 flex items-center justify-center"></i>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500">Bệnh nhân đang chờ</p>
                    <p class="text-xl font-bold text-gray-900">{{ $patientsWaitingOutside }}</p>
                </div>
            </div>

            <!-- Hôm nay -->
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100 flex items-center">
                <div class="rounded-full bg-blue-100 p-3 mr-4">
                    <i class="fa-solid fa-calendar-day text-blue-600 text-xl w-6 h-6 flex items-center justify-center"></i>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500">Tổng lịch hẹn</p>
                    <p class="text-xl font-bold text-gray-900">{{ $appointmentsCount }}</p>
                </div>
            </div>

            <!-- Hoàn thành -->
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100 flex items-center">
                <div class="rounded-full bg-green-100 p-3 mr-4">
                    <i class="fa-solid fa-check-double text-green-600 text-xl w-6 h-6 flex items-center justify-center"></i>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500">Đã hoàn thành</p>
                    <p class="text-xl font-bold text-gray-900">{{ $completedCount }}</p>
                </div>
            </div>
        @else
            <!-- Bệnh nhân đang chờ (Paraclinical) -->
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100 flex items-center">
                <div class="rounded-full bg-yellow-100 p-3 mr-4">
                    <i class="fa-solid fa-hourglass-half text-yellow-600 text-xl w-6 h-6 flex items-center justify-center"></i>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500">Tổng bệnh nhân đang chờ</p>
                    <p class="text-xl font-bold text-gray-900">{{ $patientsWaitingOutside }}</p>
                </div>
            </div>

            <!-- Đang khám (Paraclinical) -->
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100 flex items-center">
                <div class="rounded-full bg-blue-100 p-3 mr-4">
                    <i class="fa-solid fa-user-doctor text-blue-600 text-xl w-6 h-6 flex items-center justify-center"></i>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500">Tổng bệnh nhân đang khám</p>
                    <p class="text-xl font-bold text-gray-900">{{ $examiningCount }}</p>
                </div>
            </div>

            <!-- Đã hoàn thành (Paraclinical) -->
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100 flex items-center">
                <div class="rounded-full bg-green-100 p-3 mr-4">
                    <i class="fa-solid fa-check-double text-green-600 text-xl w-6 h-6 flex items-center justify-center"></i>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500">Tổng bệnh nhân đã hoàn thành</p>
                    <p class="text-xl font-bold text-gray-900">{{ $completedCount }}</p>
                </div>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Hàng chờ khám bệnh -->
        <div x-data="{ tab: new URLSearchParams(location.search).get('tab') || '{{ $doctorType === 'clinical' ? 'checked_in' : 'pending' }}' }" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden lg:col-span-2 flex flex-col h-full">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-900">Hàng chờ khám bệnh</h3>
                <a href="{{ $doctorType === 'clinical' ? route('doctor.appointments.index') : route('doctor.clinical-visits.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">Xem tất cả</a>
            </div>
            
            @if($doctorType === 'clinical')
            <div class="flex border-b border-gray-100 bg-gray-50/50 overflow-x-auto">
                <button @click="tab = 'checked_in'" :class="tab === 'checked_in' ? 'border-blue-500 text-blue-600 bg-white' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50'" class="px-4 py-3 text-sm font-medium border-b-2 whitespace-nowrap transition-colors flex items-center">
                    Đã check-in <span class="ml-2 bg-gray-100 text-gray-600 py-0.5 px-2 rounded-full text-xs">{{ $waitingListData['checked_in']->total() }}</span>
                </button>
                <button @click="tab = 'examining'" :class="tab === 'examining' ? 'border-blue-500 text-blue-600 bg-white' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50'" class="px-4 py-3 text-sm font-medium border-b-2 whitespace-nowrap transition-colors flex items-center">
                    Đang khám <span class="ml-2 bg-gray-100 text-gray-600 py-0.5 px-2 rounded-full text-xs">{{ $waitingListData['examining']->total() }}</span>
                </button>
                <button @click="tab = 'completed'" :class="tab === 'completed' ? 'border-blue-500 text-blue-600 bg-white' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50'" class="px-4 py-3 text-sm font-medium border-b-2 whitespace-nowrap transition-colors flex items-center">
                    Hoàn thành <span class="ml-2 bg-gray-100 text-gray-600 py-0.5 px-2 rounded-full text-xs">{{ $waitingListData['completed']->total() }}</span>
                </button>
                <button @click="tab = 'cancelled'" :class="tab === 'cancelled' ? 'border-blue-500 text-blue-600 bg-white' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50'" class="px-4 py-3 text-sm font-medium border-b-2 whitespace-nowrap transition-colors flex items-center">
                    Đã hủy <span class="ml-2 bg-gray-100 text-gray-600 py-0.5 px-2 rounded-full text-xs">{{ $waitingListData['cancelled']->total() }}</span>
                </button>
            </div>
            
            <!-- Tab Contents -->
            <div class="flex-1 overflow-x-auto relative">
                @foreach(['checked_in', 'examining', 'completed', 'cancelled'] as $statusKey)
                <div x-show="tab === '{{ $statusKey }}'" style="display: none;" :style="tab === '{{ $statusKey }}' ? 'display: block;' : 'display: none;'">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-500 uppercase bg-gray-50 sticky top-0">
                            <tr>
                                <th class="px-6 py-3 font-medium">Thời gian</th>
                                <th class="px-6 py-3 font-medium">Bệnh nhân</th>
                                <th class="px-6 py-3 font-medium">Lý do khám</th>
                                <th class="px-6 py-3 font-medium text-right">Hành động</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($waitingListData[$statusKey] as $appointment)
                            <tr class="hover:bg-gray-50 transition-colors {{ isset($appointment->is_late) && $appointment->is_late ? 'bg-red-50' : '' }}">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-blue-600">{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') }}</div>
                                    <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m') }}</div>
                                    @if(isset($appointment->is_late) && $appointment->is_late)
                                        <span class="inline-block px-2 py-0.5 mt-1 text-[10px] font-bold rounded-full bg-red-100 text-red-600 border border-red-200">Đến muộn</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">{{ $appointment->patientProfile->full_name ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-500">{{ $appointment->patientProfile->phone ?? '' }}</div>
                                </td>
                                <td class="px-6 py-4 text-gray-700">
                                    {{ Str::limit($appointment->reason, 40) }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('doctor.appointments.show', $appointment->id) }}" class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-medium bg-blue-50 text-blue-600 rounded-md hover:bg-blue-100 transition-colors">
                                        Vào khám <i class="fa-solid fa-arrow-right ml-1"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fa-regular fa-folder-open text-4xl mb-3 text-gray-300"></i>
                                        <p>Không có dữ liệu</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    @if($waitingListData[$statusKey]->hasPages())
                    <div class="px-4 py-3 border-t border-gray-100 bg-white">
                        {{ $waitingListData[$statusKey]->appends(['tab' => $statusKey])->links() }}
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            
            @else
            <!-- Tabs for paraclinical -->
            <div class="flex border-b border-gray-100 bg-gray-50/50 overflow-x-auto">
                <button @click="tab = 'pending'" :class="tab === 'pending' ? 'border-blue-500 text-blue-600 bg-white' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50'" class="px-4 py-3 text-sm font-medium border-b-2 whitespace-nowrap transition-colors flex items-center">
                    Đang chờ <span class="ml-2 bg-gray-100 text-gray-600 py-0.5 px-2 rounded-full text-xs">{{ $waitingListData['pending']->total() }}</span>
                </button>
                <button @click="tab = 'examining'" :class="tab === 'examining' ? 'border-blue-500 text-blue-600 bg-white' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50'" class="px-4 py-3 text-sm font-medium border-b-2 whitespace-nowrap transition-colors flex items-center">
                    Đang thực hiện <span class="ml-2 bg-gray-100 text-gray-600 py-0.5 px-2 rounded-full text-xs">{{ $waitingListData['examining']->total() }}</span>
                </button>
                <button @click="tab = 'completed'" :class="tab === 'completed' ? 'border-blue-500 text-blue-600 bg-white' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50'" class="px-4 py-3 text-sm font-medium border-b-2 whitespace-nowrap transition-colors flex items-center">
                    Hoàn thành <span class="ml-2 bg-gray-100 text-gray-600 py-0.5 px-2 rounded-full text-xs">{{ $waitingListData['completed']->total() }}</span>
                </button>
            </div>
            
            <!-- Tab Contents for Paraclinical -->
            <div class="flex-1 overflow-x-auto relative">
                @foreach(['pending', 'examining', 'completed'] as $statusKey)
                <div x-show="tab === '{{ $statusKey }}'" style="display: none;" :style="tab === '{{ $statusKey }}' ? 'display: block;' : 'display: none;'">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-500 uppercase bg-gray-50 sticky top-0">
                            <tr>
                                <th class="px-6 py-3 font-medium">Thời gian tạo</th>
                                <th class="px-6 py-3 font-medium">Bệnh nhân</th>
                                <th class="px-6 py-3 font-medium text-right">Hành động</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($waitingListData[$statusKey] as $visit)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-blue-600">{{ $visit->created_at->format('H:i') }}</div>
                                    <div class="text-xs text-gray-500">{{ $visit->created_at->format('d/m') }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">{{ $visit->appointment->patientProfile->full_name ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-500">{{ $visit->appointment->patientProfile->phone ?? '' }}</div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('doctor.clinical-visits.show', $visit->appointment_id) }}" class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-medium bg-blue-50 text-blue-600 rounded-md hover:bg-blue-100 transition-colors">
                                        Xem chi tiết <i class="fa-solid fa-arrow-right ml-1"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-6 py-8 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fa-regular fa-folder-open text-4xl mb-3 text-gray-300"></i>
                                        <p>Không có dữ liệu</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    @if($waitingListData[$statusKey]->hasPages())
                    <div class="px-4 py-3 border-t border-gray-100 bg-white">
                        {{ $waitingListData[$statusKey]->appends(['tab' => $statusKey])->links() }}
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
        </div>

        <!-- Biểu đồ mini 7 ngày -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Ca khám 7 ngày qua</h3>
            <div class="relative h-48">
                <canvas id="weeklyChart"></canvas>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const ctx = document.getElementById('weeklyChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: {
                        !!json_encode($miniChartLabels) !!
                    },
                    datasets: [{
                        label: 'Số ca khám',
                        data: {
                            !!json_encode($miniChartData) !!
                        },
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        });
    </script>
    @endpush
</x-layouts.doctor>