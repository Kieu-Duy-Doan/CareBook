<div class="border-b border-gray-200 mb-6">
    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
        <!-- Lịch hẹn -->
        <a href="{{ route('admin.appointments.index') }}"
            class="{{ request()->routeIs('admin.appointments.*')
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} 
                  whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center">
            <i
                class="fa-solid fa-calendar-check mr-2 {{ request()->routeIs('admin.appointments.*') ? 'text-blue-500' : 'text-gray-400' }}"></i>
            Lịch hẹn
        </a>

        <!-- Giám sát Khám LS -->
        <a href="{{ route('admin.clinical-visits.index') }}"
            class="{{ request()->routeIs('admin.clinical-visits.*')
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} 
                  whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center">
            <i
                class="fa-solid fa-microscope mr-2 {{ request()->routeIs('admin.clinical-visits.*') ? 'text-blue-500' : 'text-gray-400' }}"></i>
            Giám sát Khám LS
        </a>

        <!-- Nhật ký Đối soát -->
        <a href="{{ route('admin.appointment-logs.index') }}"
            class="{{ request()->routeIs('admin.appointment-logs.*')
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} 
                  whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center">
            <i
                class="fa-solid fa-clock-rotate-left mr-2 {{ request()->routeIs('admin.appointment-logs.*') ? 'text-blue-500' : 'text-gray-400' }}"></i>
            Nhật ký Đối soát
        </a>
    </nav>
</div>
