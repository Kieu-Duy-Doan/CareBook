<x-layouts.receptionist>
    <x-slot:title>Bảng điều khiển</x-slot:title>

    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Tổng quan hôm nay</h2>
        <p class="text-gray-500 text-sm mt-1">Xin chào, {{ Auth::user()->full_name }}! Chúc bạn một ngày làm việc hiệu quả.</p>
    </div>

    <!-- Stats grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Tổng lịch hẹn hôm nay -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center">
            <div class="h-12 w-12 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xl mr-4">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Lịch hẹn hôm nay</p>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['total_appointments_today'] }}</p>
            </div>
        </div>

        <!-- Lịch hẹn chờ xử lý -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center">
            <div class="h-12 w-12 rounded-full bg-orange-50 text-orange-600 flex items-center justify-center text-xl mr-4">
                <i class="fa-solid fa-clock"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Lịch hẹn chờ duyệt</p>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['pending_appointments'] }}</p>
            </div>
        </div>

        <!-- Đang chờ khám -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center">
            <div class="h-12 w-12 rounded-full bg-yellow-50 text-yellow-600 flex items-center justify-center text-xl mr-4">
                <i class="fa-solid fa-users"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Bệnh nhân đang chờ</p>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['visits_waiting'] }}</p>
            </div>
        </div>

        <!-- Đang khám -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center">
            <div class="h-12 w-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl mr-4">
                <i class="fa-solid fa-stethoscope"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Bệnh nhân đang khám</p>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['visits_in_progress'] }}</p>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <h3 class="text-lg font-bold text-gray-900 mb-4">Thao tác nhanh</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="{{ route('receptionist.appointments.create') }}" class="flex items-center p-4 bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-gray-50 hover:border-emerald-300 transition-colors">
            <div class="h-10 w-10 rounded bg-emerald-100 text-emerald-600 flex items-center justify-center mr-3">
                <i class="fa-solid fa-plus"></i>
            </div>
            <span class="font-medium text-gray-700">Tạo lịch hẹn mới</span>
        </a>
        <a href="{{ route('receptionist.patients.create') }}" class="flex items-center p-4 bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-gray-50 hover:border-blue-300 transition-colors">
            <div class="h-10 w-10 rounded bg-blue-100 text-blue-600 flex items-center justify-center mr-3">
                <i class="fa-solid fa-user-plus"></i>
            </div>
            <span class="font-medium text-gray-700">Thêm bệnh nhân</span>
        </a>
        <a href="{{ route('receptionist.clinical-visits.index') }}" class="flex items-center p-4 bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-gray-50 hover:border-purple-300 transition-colors">
            <div class="h-10 w-10 rounded bg-purple-100 text-purple-600 flex items-center justify-center mr-3">
                <i class="fa-solid fa-desktop"></i>
            </div>
            <span class="font-medium text-gray-700">Màn hình giám sát</span>
        </a>
        <a href="{{ route('receptionist.payments.index') }}" class="flex items-center p-4 bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-gray-50 hover:border-orange-300 transition-colors">
            <div class="h-10 w-10 rounded bg-orange-100 text-orange-600 flex items-center justify-center mr-3">
                <i class="fa-solid fa-file-invoice-dollar"></i>
            </div>
            <span class="font-medium text-gray-700">Thanh toán & Thu ngân</span>
        </a>
    </div>
</x-layouts.receptionist>
