@props(['title' => 'Trang cá nhân', 'activeMenu' => 'profiles'])

<x-layouts.patient :title="$title">
    <div class="w-full bg-slate-50 min-h-[calc(100vh-80px)]">
        <div class="w-full px-4 md:px-8 xl:px-12 py-8 md:py-10">
            <!-- Dashboard Header -->
            <div class="flex items-center gap-5 mb-8 md:mb-10 pb-6 border-b border-slate-200">
                <div
                    class="w-16 h-16 rounded-3xl bg-white shadow-sm border border-slate-100 flex items-center justify-center p-1 relative overflow-hidden">
                    <div
                        class="w-full h-full bg-primary/10 rounded-2xl text-primary flex items-center justify-center text-2xl font-black">
                        {{ substr(auth()->user()->full_name ?? 'U', 0, 1) }}
                    </div>
                </div>
                <div>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight">{{ $title }}
                    </h1>
                    <p class="text-slate-500 font-medium mt-1">Xin chào, <span
                            class="text-slate-700 font-bold">{{ auth()->user()->full_name ?? 'Bệnh nhân' }}</span></p>
                </div>
            </div>

            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Sidebar Navigation -->
                <aside class="lg:w-64 shrink-0">
                    <div
                        class="sticky top-28 bg-white lg:bg-transparent rounded-3xl p-3 lg:p-0 shadow-sm border border-slate-100 lg:border-none lg:shadow-none">
                        @php
                            $baseClass =
                                'flex items-center flex-col lg:flex-row gap-2 lg:gap-3 px-3 py-3 lg:px-4 lg:py-3.5 rounded-2xl font-semibold transition-all duration-300 text-center lg:text-left text-[11px] sm:text-xs md:text-[15px] group relative overflow-hidden';
                            $activeClass = 'bg-primary-light/50 text-primary shadow-sm ring-1 ring-primary/10';
                            $inactiveClass = 'text-slate-600 hover:bg-slate-100/80 hover:text-primary';
                        @endphp

                        <nav class="grid grid-cols-3 lg:flex lg:flex-col gap-2">
                            <a href="{{ route('patient.profiles.index') }}"
                                class="{{ $baseClass }} {{ $activeMenu === 'profiles' ? $activeClass : $inactiveClass }}">
                                <i
                                    class="fa-solid fa-address-card w-6 text-center text-lg {{ $activeMenu === 'profiles' ? 'text-primary' : 'text-slate-400 group-hover:text-primary/70' }}"></i>
                                <span>Thông tin cá nhân</span>
                            </a>

                            <a href="{{ route('patient.family.index') }}"
                                class="{{ $baseClass }} {{ $activeMenu === 'family' ? $activeClass : $inactiveClass }}">
                                <i
                                    class="fa-solid fa-users w-6 text-center text-lg {{ $activeMenu === 'family' ? 'text-primary' : 'text-slate-400 group-hover:text-primary/70' }}"></i>
                                <span>Quản lý gia đình</span>
                            </a>

                            <a href="{{ route('patient.appointments.index') }}"
                                class="{{ $baseClass }} {{ $activeMenu === 'appointments' ? $activeClass : $inactiveClass }}">
                                <i
                                    class="fa-regular fa-calendar-check w-6 text-center text-lg {{ $activeMenu === 'appointments' ? 'text-primary' : 'text-slate-400 group-hover:text-primary/70' }}"></i>
                                <span>Lịch sử đặt lịch</span>
                            </a>

                            <a href="{{ route('patient.records.index') }}"
                                class="{{ $baseClass }} {{ $activeMenu === 'records' ? $activeClass : $inactiveClass }}">
                                <i
                                    class="fa-solid fa-file-medical w-6 text-center text-lg {{ $activeMenu === 'records' ? 'text-primary' : 'text-slate-400 group-hover:text-primary/70' }}"></i>
                                <span>Kết quả khám</span>
                            </a>

                            <a href="{{ route('patient.prescriptions.index') }}"
                                class="{{ $baseClass }} {{ $activeMenu === 'prescriptions' ? $activeClass : $inactiveClass }}">
                                <i
                                    class="fa-solid fa-pills w-6 text-center text-lg {{ $activeMenu === 'prescriptions' ? 'text-primary' : 'text-slate-400 group-hover:text-primary/70' }}"></i>
                                <span>Đơn thuốc</span>
                            </a>

                            <a href="{{ route('patient.notifications.page') }}"
                                class="{{ $baseClass }} {{ $activeMenu === 'notifications' ? $activeClass : $inactiveClass }}">
                                <i
                                    class="fa-solid fa-bell w-6 text-center text-lg {{ $activeMenu === 'notifications' ? 'text-primary' : 'text-slate-400 group-hover:text-primary/70' }}"></i>
                                <span>Thông báo</span>
                            </a>
                        </nav>
                    </div>
                </aside>

                <!-- Main Content Area -->
                <main
                    class="flex-1 bg-white rounded-3xl shadow-[0_5px_30px_-10px_rgba(0,0,0,0.03)] border border-slate-100 p-6 md:p-8 min-h-[500px]">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </div>
</x-layouts.patient>
