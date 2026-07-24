<x-layouts.doctor title="Chi tiết thông báo">
    <div class="space-y-6">

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
                <div class="w-10 h-10 rounded-full {{ in_array($notification->type, ['cancellation', 'system_cancellation']) ? 'bg-red-100 text-red-600' : 'bg-blue-100 text-blue-600' }} flex items-center justify-center">
                    @if(in_array($notification->type, ['cancellation', 'system_cancellation']))
                    <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                    @else
                    <i class="fa-solid fa-bell text-lg"></i>
                    @endif
                </div>
                Chi tiết thông báo
            </h1>
            <a href="{{ route('doctor.notifications.page') }}"
                class="inline-flex items-center gap-2 text-sm font-semibold text-gray-500 hover:text-blue-600 bg-gray-50 hover:bg-blue-50 px-4 py-2.5 rounded-lg transition-colors border border-gray-200 w-fit">
                <i class="fa-solid fa-arrow-left"></i> Trở về danh sách
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            {{-- Header --}}
            <div class="p-6 md:p-8 border-b border-gray-100">
                <h2 class="text-xl md:text-2xl font-bold mb-2 {{ in_array($notification->type, ['cancellation', 'system_cancellation']) ? 'text-red-600' : 'text-gray-800' }}">
                    {{ $notification->title }}
                </h2>
                <div class="flex items-center gap-3 text-sm font-medium text-gray-500">
                    <span class="bg-gray-50 px-3 py-1 rounded-full border border-gray-100">
                        <i class="fa-regular fa-clock mr-1"></i> {{ $notification->created_at->format('H:i - d/m/Y') }}
                    </span>
                    @php
                    $typeLabels = [
                    'system' => 'Hệ thống',
                    'appointment' => 'Lịch khám',
                    'cancellation' => 'Huỷ lịch',
                    'system_cancellation' => 'Huỷ lịch',
                    'patient_cancellation' => 'Huỷ lịch',
                    'patient_booking' => 'Đặt lịch',
                    'system_booking' => 'Đặt lịch'
                    ];
                    $typeLabel = $typeLabels[$notification->type] ?? ucfirst($notification->type);
                    @endphp
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ in_array($notification->type, ['cancellation', 'system_cancellation']) ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800' }}">
                        {{ $typeLabel }}
                    </span>
                </div>
            </div>

            {{-- Content --}}
            <div class="p-6 md:p-8 bg-gray-50/50">
                <div class="prose max-w-none text-gray-700 text-base md:text-lg leading-relaxed">
                    {!! nl2br(e($notification->content)) !!}
                </div>
            </div>

            {{-- Appointment Info --}}
            @if($appointment)
            <div class="p-6 md:p-8 border-t border-gray-100">
                <h3 class="font-bold text-gray-800 mb-5 text-lg flex items-center gap-2">
                    <i class="fa-solid fa-file-medical text-gray-400"></i> Thông tin lịch hẹn
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 text-sm md:text-base">
                    <div class="flex flex-col border-b border-gray-100 pb-3 md:border-0 md:pb-0">
                        <span class="text-gray-400 font-bold uppercase tracking-wider text-xs mb-1">Mã lịch hẹn</span>
                        <strong class="text-gray-800 font-mono bg-gray-100 px-2 py-1 rounded w-fit">{{ $appointment->appointment_code }}</strong>
                    </div>
                    <div class="flex flex-col border-b border-gray-100 pb-3 md:border-0 md:pb-0">
                        <span class="text-gray-400 font-bold uppercase tracking-wider text-xs mb-1">Bệnh nhân</span>
                        <strong class="text-gray-800">{{ $appointment->patientProfile->full_name ?? 'Chưa xác định' }}</strong>
                    </div>
                    <div class="flex flex-col border-b border-gray-100 pb-3 md:border-0 md:pb-0">
                        <span class="text-gray-400 font-bold uppercase tracking-wider text-xs mb-1">Thời gian</span>
                        <strong class="text-gray-800 text-lg text-blue-600">{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') }} <span class="text-gray-500 text-base font-medium">- {{ $appointment->appointment_date->format('d/m/Y') }}</span></strong>
                    </div>
                    <div class="flex flex-col border-b border-gray-100 pb-3 md:border-0 md:pb-0">
                        <span class="text-gray-400 font-bold uppercase tracking-wider text-xs mb-1">Chuyên khoa</span>
                        <strong class="text-gray-800">{{ $appointment->specialty->name ?? 'Chưa xác định' }}</strong>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-gray-400 font-bold uppercase tracking-wider text-xs mb-1">Trạng thái</span>
                        @php
                        $statusLabels = [
                        'pending' => 'Chờ xác nhận',
                        'confirmed' => 'Đã xác nhận',
                        'completed' => 'Hoàn thành',
                        'cancelled' => 'Đã huỷ',
                        'in_progress' => 'Đang khám'
                        ];
                        $statusLabel = $statusLabels[$appointment->status] ?? ucfirst($appointment->status);
                        @endphp
                        @if($appointment->status === 'cancelled')
                        <span class="inline-flex items-center gap-1.5 bg-red-50 text-red-600 font-bold px-3 py-1.5 rounded-lg w-fit border border-red-100">
                            <i class="fa-solid fa-ban text-sm"></i> {{ $statusLabel }}
                        </span>
                        @elseif($appointment->status === 'completed')
                        <span class="inline-flex items-center gap-1.5 bg-green-50 text-green-600 font-bold px-3 py-1.5 rounded-lg w-fit border border-green-100">
                            <i class="fa-solid fa-check-circle text-sm"></i> {{ $statusLabel }}
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1.5 bg-blue-50 text-blue-600 font-bold px-3 py-1.5 rounded-lg w-fit border border-blue-100">
                            <i class="fa-solid fa-check-circle text-sm"></i> {{ $statusLabel }}
                        </span>
                        @endif
                    </div>
                </div>

                {{-- Nút xem lịch hẹn --}}
                <div class="mt-6 pt-6 border-t border-gray-100">
                    <a href="{{ route('doctor.appointments.show', $appointment->id) }}"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                        <i class="fa-solid fa-calendar-check"></i> Xem lịch hẹn
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-layouts.doctor>