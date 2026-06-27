<x-layouts.patient title="Đặt lịch thành công">

<div class="max-w-lg mx-auto px-4 py-12 text-center">

    {{-- Icon thành công --}}
    <div class="w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6"
         style="background-color:#e8f4fd;">
        <i class="fa-solid fa-circle-check text-5xl" style="color:#27AE60;"></i>
    </div>

    <h1 class="text-2xl font-bold text-gray-800 mb-2">Đặt lịch thành công!</h1>
    <p class="text-gray-500 mb-8">Vui lòng ghi nhớ mã lịch hẹn của bạn</p>

    {{-- Mã lịch hẹn --}}
    <div class="bg-white border-2 rounded-2xl px-6 py-4 inline-block mb-6"
         style="border-color:var(--primary);">
        <p class="text-xs text-gray-400 mb-1">Mã lịch hẹn</p>
        <p class="text-3xl font-bold tracking-widest" style="color:var(--primary);">
            {{ $appointment->appointment_code }}
        </p>
    </div>

    {{-- Thông tin tóm tắt --}}
    <div class="bg-white border rounded-2xl overflow-hidden mb-6 text-left"
         style="border-color:#e2e8f0;">
        <div class="px-5 py-3 border-b" style="background-color:rgba(29,111,164,0.05);border-color:rgba(29,111,164,0.10);">
            <span class="font-bold text-sm uppercase" style="color:var(--primary);">
                <i class="fa-solid fa-calendar-check mr-2"></i>Chi tiết lịch hẹn
            </span>
        </div>
        <div class="divide-y divide-gray-50">
            <div class="flex items-start px-5 py-3">
                <span class="text-gray-400 text-sm w-28 flex-shrink-0">Bệnh nhân:</span>
                <span class="font-semibold text-gray-800">{{ $appointment->patientProfile?->full_name }}</span>
            </div>
            @if($appointment->doctorProfile)
            <div class="flex items-start px-5 py-3">
                <span class="text-gray-400 text-sm w-28 flex-shrink-0">Bác sĩ:</span>
                <span class="font-semibold text-gray-800 uppercase">{{ $appointment->doctorProfile->full_title }}</span>
            </div>
            @endif
            <div class="flex items-start px-5 py-3">
                <span class="text-gray-400 text-sm w-28 flex-shrink-0">Chuyên khoa:</span>
                <span class="font-semibold text-gray-800">{{ $appointment->specialty?->name }}</span>
            </div>
            <div class="flex items-start px-5 py-3">
                <span class="text-gray-400 text-sm w-28 flex-shrink-0">Ngày khám:</span>
                <span class="font-semibold text-gray-800">
                    {{ $appointment->appointment_date->format('d/m/Y') }}
                </span>
            </div>
            <div class="flex items-start px-5 py-3">
                <span class="text-gray-400 text-sm w-28 flex-shrink-0">Giờ khám:</span>
                <span class="font-bold text-lg" style="color:var(--primary);">
                    {{ substr($appointment->appointment_time, 0, 5) }}
                </span>
            </div>
            @if($appointment->room)
            <div class="flex items-start px-5 py-3">
                <span class="text-gray-400 text-sm w-28 flex-shrink-0">Phòng khám:</span>
                <span class="font-semibold text-gray-800">{{ $appointment->room->name }}</span>
            </div>
            @endif
        </div>
    </div>

    {{-- Lưu ý --}}
    <div class="bg-orange-50 border border-orange-200 rounded-xl p-4 text-left mb-8">
        <p class="font-bold text-orange-700 mb-1 text-sm">
            <i class="fa-solid fa-circle-info mr-1"></i> Nhắc nhở
        </p>
        <ul class="text-sm text-orange-600 space-y-1">
            <li>• Có mặt trước <strong>15 phút</strong> so với giờ hẹn</li>
            <li>• Mang theo <strong>CCCD</strong> và <strong>thẻ BHYT</strong> (nếu có)</li>
            <li>• Có thể huỷ lịch trước 2 tiếng qua ứng dụng</li>
        </ul>
    </div>

    {{-- Actions --}}
    <div class="flex flex-col sm:flex-row gap-3">
        <a href="{{ route('home') }}"
           class="flex-1 py-3 border-2 rounded-xl text-center font-semibold hover:bg-gray-50 transition-colors"
           style="border-color:var(--primary);color:var(--primary);">
            <i class="fa-solid fa-house mr-2"></i>Về trang chủ
        </a>
        <a href="{{ route('patient.booking.index') }}"
           class="flex-1 py-3 rounded-xl text-center font-semibold text-white transition-colors"
           style="background-color:var(--primary);"
           onmouseover="this.style.backgroundColor='#155a85'"
           onmouseout="this.style.backgroundColor='var(--primary)'">
            <i class="fa-solid fa-calendar-plus mr-2"></i>Đặt lịch khác
        </a>
    </div>
</div>

</x-layouts.patient>
