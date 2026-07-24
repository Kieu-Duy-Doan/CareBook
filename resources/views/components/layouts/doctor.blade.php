<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Bác sĩ' }} - CareBook</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    {!! $styles ?? '' !!}
    @stack('styles')
</head>

<body class="font-sans antialiased bg-gray-50 text-gray-900" x-data="{ sidebarOpen: false }">
    <!-- Mobile sidebar backdrop -->
    <div x-show="sidebarOpen" class="fixed inset-0 z-40 bg-gray-900/80 lg:hidden" x-transition.opacity
        @click="sidebarOpen = false" style="display: none;"></div>
    <!-- Sidebar -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 transition-transform duration-300 lg:translate-x-0 flex flex-col">
        <!-- Logo -->
        <div class="flex items-center gap-2 px-6 py-5 border-b border-gray-200 text-blue-600">
            <i class="fa-solid fa-user-doctor text-2xl"></i>
            <span class="text-xl font-bold">CareBook</span>
            <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded-full font-medium ml-2">Bác sĩ</span>
        </div>
        <!-- Navigation -->
        <div class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
            <a href="{{ route('doctor.dashboard') }}"
                class="{{ request()->routeIs('doctor.dashboard') ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }} group flex items-center px-3 py-2 text-sm font-medium rounded-md">
                <i class="fa-solid fa-house w-6 text-center mr-2 {{ request()->routeIs('doctor.dashboard') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-500' }}"></i>
                Bảng điều khiển
            </a>

            <a href="{{ route('doctor.appointments.index') }}"
                class="{{ request()->routeIs('doctor.appointments.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }} group flex items-center px-3 py-2 text-sm font-medium rounded-md mt-1">
                <i class="fa-solid fa-calendar-check w-5 text-center mr-3 {{ request()->routeIs('doctor.appointments.*') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-500' }}"></i>
                Quản lý lịch hẹn
            </a>

            <a href="{{ route('doctor.clinical-visits.index') }}"
                class="{{ request()->routeIs('doctor.clinical-visits.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }} group flex items-center px-3 py-2 text-sm font-medium rounded-md mt-1">
                <i class="fa-solid fa-microscope w-5 text-center mr-3 {{ request()->routeIs('doctor.clinical-visits.*') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-500' }}"></i>
                Giám sát lâm sàng
            </a>

            <a href="{{ route('doctor.payments.index') }}"
                class="{{ request()->routeIs('doctor.payments.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }} group flex items-center px-3 py-2 text-sm font-medium rounded-md mt-1">
                <i class="fa-solid fa-file-invoice-dollar w-5 text-center mr-3 {{ request()->routeIs('doctor.payments.*') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-500' }}"></i>
                Thanh toán
            </a>

            <a href="{{ route('doctor.work-schedules.index') }}"
                class="{{ request()->routeIs('doctor.work-schedules.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }} group flex items-center px-3 py-2 text-sm font-medium rounded-md mt-1">
                <i class="fa-solid fa-calendar-days w-5 text-center mr-3 {{ request()->routeIs('doctor.work-schedules.*') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-500' }}"></i>
                Lịch làm việc
            </a>

            <a href="{{ route('doctor.patient-history.index') }}"
                class="{{ request()->routeIs('doctor.patient-history.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }} group flex items-center px-3 py-2 text-sm font-medium rounded-md mt-1">
                <i class="fa-solid fa-notes-medical w-5 text-center mr-3 {{ request()->routeIs('doctor.patient-history.*') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-500' }}"></i>
                Lịch sử bệnh nhân
            </a>

            <a href="{{ route('doctor.notifications.page') }}"
                class="{{ request()->routeIs('doctor.notifications.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }} group flex items-center px-3 py-2 text-sm font-medium rounded-md mt-1">
                <i class="fa-solid fa-bell w-5 text-center mr-3 {{ request()->routeIs('doctor.notifications.*') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-500' }}"></i>
                Thông báo
                @if(isset($unreadCount) && $unreadCount > 0)
                <span class="ml-auto inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold text-white bg-red-500 rounded-full">{{ $unreadCount }}</span>
                @endif
            </a>

            <a href="{{ route('doctor.profile.index') }}"
                class="{{ request()->routeIs('doctor.profile.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }} group flex items-center px-3 py-2 text-sm font-medium rounded-md mt-1">
                <i class="fa-solid fa-user-circle w-5 text-center mr-3 {{ request()->routeIs('doctor.profile.*') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-500' }}"></i>
                Thông tin cá nhân
            </a>
        </div>

        <!-- User bottom -->
        <div class="border-t border-gray-200 p-4 flex items-center">
            <div class="flex-shrink-0">
                <div class="h-9 w-9 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm">
                    {{ Auth::user()->avatar_initials }}
                </div>
            </div>
            <div class="ml-3 flex-1 overflow-hidden">
                <p class="text-sm font-medium text-gray-900 truncate">{{ Auth::user()->full_name }}</p>
                <p class="text-xs text-gray-500 truncate">{{ Auth::user()->display_role }}</p>
            </div>
            <form action="{{ route('doctor.logout') }}" method="POST" class="ml-2">
                @csrf
                <button type="submit" class="text-gray-400 hover:text-red-500 transition-colors" title="Đăng xuất">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="lg:pl-64 flex flex-col min-h-screen">
        <!-- Topbar -->
        <header class="sticky top-0 z-30 bg-white border-b border-gray-200 h-16 flex items-center justify-between px-4 sm:px-6">
            <div class="flex items-center">
                <button @click="sidebarOpen = true" class="lg:hidden text-gray-500 hover:text-gray-700 mr-4">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                <h1 class="text-lg font-semibold text-gray-900">{{ $title ?? 'Bảng điều khiển Bác sĩ' }}</h1>
            </div>

            <div class="flex items-center gap-4">
                <a href="{{ route('doctor.customer-display.index') }}" target="_blank"
                    class="hidden md:flex items-center gap-2 bg-blue-50 text-blue-600 hover:bg-blue-100 px-3 py-1.5 rounded-lg text-sm font-semibold border border-blue-200 transition-colors">
                    <i class="fa-solid fa-display"></i> Mở Màn hình Phụ
                </a>

                {{-- Notification Bell Dropdown --}}
                <div x-data="{ notifOpen: false }" class="relative">
                    <button @click="notifOpen = !notifOpen" class="relative p-2 text-gray-400 hover:text-gray-600 transition-colors" title="Thông báo">
                        <i class="fa-regular fa-bell text-xl"></i>
                        @if(isset($unreadCount) && $unreadCount > 0)
                        <span class="absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-[9px] font-bold leading-none text-white transform translate-x-1/4 -translate-y-1/4 bg-red-500 rounded-full">{{ $unreadCount }}</span>
                        @endif
                    </button>

                    <div x-show="notifOpen" @click.away="notifOpen = false" x-cloak style="display: none;" class="absolute right-0 top-full mt-2 w-[340px] max-w-[90vw] bg-white border border-gray-200 shadow-xl rounded-lg py-2 z-50 overflow-hidden flex flex-col max-h-[80vh]">
                        <div class="px-4 py-2 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                            <h3 class="font-bold text-gray-800 text-sm">Thông báo</h3>
                            @if(isset($unreadCount) && $unreadCount > 0)
                            <form action="{{ route('doctor.notifications.read') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-xs text-blue-600 hover:text-blue-800 font-semibold">Đánh dấu đã đọc</button>
                            </form>
                            @endif
                        </div>
                        <div class="overflow-y-auto flex-1 p-2 space-y-1">
                            @if(isset($notifications) && count($notifications) == 0)
                            <p class="text-center text-gray-500 py-4 text-xs italic">Chưa có thông báo nào</p>
                            @endif
                            @if(isset($notifications))
                            @foreach($notifications as $notif)
                            <a href="{{ route('doctor.notifications.show', $notif->id) }}" class="block p-3 rounded-md hover:bg-gray-50 transition-colors cursor-pointer border border-transparent hover:border-gray-100 flex flex-col gap-1 {{ $notif->is_read ? 'opacity-70' : 'bg-blue-50/50' }}">
                                <div class="flex justify-between items-start">
                                    <h4 class="font-bold text-sm {{ in_array($notif->type, ['cancellation', 'system_cancellation']) ? 'text-red-600' : 'text-gray-800' }}">{{ $notif->title }}</h4>
                                    @if(!$notif->is_read)
                                    <span class="w-2 h-2 rounded-full bg-blue-500 flex-shrink-0 mt-1"></span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-600 leading-relaxed line-clamp-2">{{ $notif->content }}</p>
                                <span class="text-[10px] text-gray-400 mt-1">{{ $notif->created_at->diffForHumans() }}</span>
                            </a>
                            @endforeach
                            @endif
                        </div>
                        <div class="px-4 py-2 border-t border-gray-100 bg-gray-50 text-center">
                            <a href="{{ route('doctor.notifications.page') }}" class="text-xs font-bold text-blue-600 hover:underline">Xem tất cả thông báo</a>
                        </div>
                    </div>
                </div>

                <div x-data="{ userMenuOpen: false }" class="relative">
                    <button @click="userMenuOpen = !userMenuOpen" @click.outside="userMenuOpen = false"
                        class="flex items-center gap-2 text-sm text-gray-700 hover:text-gray-900 focus:outline-none">
                        <div class="h-8 w-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs">
                            {{ Auth::user()->avatar_initials }}
                        </div>
                        <i class="fa-solid fa-chevron-down text-xs text-gray-400"></i>
                    </button>

                    <div x-show="userMenuOpen" style="display: none;"
                        class="absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none"
                        x-transition>
                        <div class="px-4 py-2 border-b border-gray-100">
                            <p class="text-sm text-gray-900 font-medium">{{ Auth::user()->full_name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email ?? Auth::user()->phone }}</p>
                        </div>
                        <a href="{{ route('doctor.profile.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Hồ sơ cá nhân</a>
                        <form action="{{ route('doctor.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">Đăng xuất</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- Content -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto">
            @if (session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-md">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fa-solid fa-check-circle text-green-500"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
            @endif

            @if (session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-md">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fa-solid fa-exclamation-circle text-red-500"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
            @endif

            {{ $slot }}
        </main>
    </div>

    {!! $scripts ?? '' !!}
    @stack('scripts')
</body>

</html>