@props(['title' => 'Trang cá nhân', 'activeMenu' => 'profiles'])

<x-layouts.patient :title="$title">
    <div class="w-full px-4 md:px-6 py-6 md:py-8">
        <!-- Dashboard Header -->
        <div class="flex items-center gap-4 mb-6 md:mb-8 pb-4 md:pb-6 border-b border-slate-200">
            <div class="w-16 h-16 rounded-full bg-primary/10 text-primary flex items-center justify-center text-2xl font-bold shadow-inner">
                {{ substr(auth()->user()->full_name ?? 'U', 0, 1) }}
            </div>
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Thông tin cá nhân</h1>
                <p class="text-slate-500 font-medium mt-1">Xin chào, {{ auth()->user()->full_name ?? 'Bệnh nhân' }}</p>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-6 md:gap-8">
            <!-- Sidebar Navigation -->
            <aside class="lg:w-64 shrink-0 -mx-2 px-2 lg:mx-0 lg:px-0">
                <nav class="grid grid-cols-2 sm:grid-cols-3 lg:flex lg:flex-col gap-2 pb-2 lg:pb-0 lg:space-y-1">
                    <a href="{{ route('patient.profiles.index') }}" 
                       class="flex flex-col lg:flex-row items-center gap-1.5 md:gap-3 px-2 py-3 rounded-xl font-semibold transition-colors text-center lg:text-left text-xs sm:text-sm md:text-base border border-slate-100 lg:border-none {{ $activeMenu === 'profiles' ? 'bg-primary text-white shadow-md shadow-primary/20' : 'text-slate-600 hover:bg-slate-100 hover:text-primary bg-white lg:bg-transparent shadow-sm lg:shadow-none' }}">
                        <i class="fa-solid fa-address-card w-5 text-center {{ $activeMenu === 'profiles' ? 'text-white' : 'text-slate-400' }}"></i>
                        Hồ sơ cá nhân
                    </a>

                    <!-- Quản lý gia đình -->
                    <a href="{{ route('patient.family.index') }}" 
                       class="flex flex-col lg:flex-row items-center gap-1.5 md:gap-3 px-2 py-3 rounded-xl font-semibold transition-colors text-center lg:text-left text-xs sm:text-sm md:text-base border border-slate-100 lg:border-none {{ $activeMenu === 'family' ? 'bg-primary text-white shadow-md shadow-primary/20' : 'text-slate-600 hover:bg-slate-100 hover:text-primary bg-white lg:bg-transparent shadow-sm lg:shadow-none' }}">
                        <i class="fa-solid fa-users w-5 text-center {{ $activeMenu === 'family' ? 'text-white' : 'text-slate-400' }}"></i>
                        Quản lý gia đình
                    </a>

                    <a href="{{ route('patient.appointments.index') }}" 
                       class="flex flex-col lg:flex-row items-center gap-1.5 md:gap-3 px-2 py-3 rounded-xl font-semibold transition-colors text-center lg:text-left text-xs sm:text-sm md:text-base border border-slate-100 lg:border-none {{ $activeMenu === 'appointments' ? 'bg-primary text-white shadow-md shadow-primary/20' : 'text-slate-600 hover:bg-slate-100 hover:text-primary bg-white lg:bg-transparent shadow-sm lg:shadow-none' }}">
                        <i class="fa-regular fa-calendar-check w-5 text-center {{ $activeMenu === 'appointments' ? 'text-white' : 'text-slate-400' }}"></i>
                        Lịch sử đặt lịch
                    </a>

                    <!-- Kết quả khám bệnh -->
                    <a href="{{ route('patient.records.index') }}" 
                       class="flex flex-col lg:flex-row items-center gap-1.5 md:gap-3 px-2 py-3 rounded-xl font-semibold transition-colors text-center lg:text-left text-xs sm:text-sm md:text-base border border-slate-100 lg:border-none {{ $activeMenu === 'records' ? 'bg-primary text-white shadow-md shadow-primary/20' : 'text-slate-600 hover:bg-slate-100 hover:text-primary bg-white lg:bg-transparent shadow-sm lg:shadow-none' }}">
                        <i class="fa-solid fa-file-medical w-5 text-center {{ $activeMenu === 'records' ? 'text-white' : 'text-slate-400' }}"></i>
                        Kết quả khám
                    </a>

                    <!-- Đơn thuốc -->
                    <a href="{{ route('patient.prescriptions.index') }}" 
                       class="flex flex-col lg:flex-row items-center gap-1.5 md:gap-3 px-2 py-3 rounded-xl font-semibold transition-colors text-center lg:text-left text-xs sm:text-sm md:text-base border border-slate-100 lg:border-none {{ $activeMenu === 'prescriptions' ? 'bg-primary text-white shadow-md shadow-primary/20' : 'text-slate-600 hover:bg-slate-100 hover:text-primary bg-white lg:bg-transparent shadow-sm lg:shadow-none' }}">
                        <i class="fa-solid fa-pills w-5 text-center {{ $activeMenu === 'prescriptions' ? 'text-white' : 'text-slate-400' }}"></i>
                        Đơn thuốc
                    </a>
                </nav>
            </aside>

            <!-- Main Content Area -->
            <main class="flex-1 bg-white rounded-2xl md:rounded-3xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.05)] border border-slate-100 p-4 sm:p-6 md:p-8 min-h-[400px]">
                {{ $slot }}
            </main>
        </div>
    </div>
</x-layouts.patient>
