<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Lễ tân' }} - CareBook</title>
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
        <div class="flex items-center gap-2 px-6 py-5 border-b border-gray-200 text-emerald-600">
            <i class="fa-solid fa-hospital-user text-2xl"></i>
            <span class="text-xl font-bold">CareBook</span>
            <span class="text-xs bg-emerald-100 text-emerald-700 px-2 py-1 rounded-full font-medium ml-2">Lễ tân</span>
        </div>
        <!-- Navigation -->
        <div class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
            <a href="{{ route('receptionist.dashboard') }}"
                class="{{ request()->routeIs('receptionist.dashboard') ? 'bg-emerald-50 text-emerald-600' : 'text-gray-700 hover:bg-gray-100' }} group flex items-center px-3 py-2 text-sm font-medium rounded-md">
                <i class="fa-solid fa-house w-6 text-center mr-2 {{ request()->routeIs('receptionist.dashboard') ? 'text-emerald-600' : 'text-gray-400 group-hover:text-gray-500' }}"></i>
                Bảng điều khiển
            </a>

            <a href="{{ route('receptionist.appointments.index') }}"
                class="{{ request()->routeIs('receptionist.appointments.*') ? 'bg-emerald-50 text-emerald-600' : 'text-gray-700 hover:bg-gray-100' }} group flex items-center px-3 py-2 text-sm font-medium rounded-md mt-1">
                <i class="fa-solid fa-calendar-check w-5 text-center mr-3 {{ request()->routeIs('receptionist.appointments.*') ? 'text-emerald-600' : 'text-gray-400 group-hover:text-gray-500' }}"></i>
                Quản lý lịch hẹn
            </a>

            <div x-data="{ openUsers: {{ request()->routeIs('receptionist.patients.*', 'receptionist.customers.*') ? 'true' : 'false' }} }">
                <button @click="openUsers = !openUsers"
                    class="w-full flex items-center justify-between px-3 py-2 rounded-md text-sm font-medium mt-1
                               {{ request()->routeIs('receptionist.patients.*', 'receptionist.customers.*') ? 'bg-emerald-50 text-emerald-600' : 'text-gray-700 hover:bg-gray-100' }}">
                    <span class="flex items-center">
                        <i class="fa-solid fa-users w-5 text-center mr-3 {{ request()->routeIs('receptionist.patients.*', 'receptionist.customers.*') ? 'text-emerald-600' : 'text-gray-400 group-hover:text-gray-500' }}"></i>
                        Khách hàng & Bệnh nhân
                    </span>
                    <i class="fa-solid fa-chevron-down text-xs transition-transform"
                        :class="openUsers ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openUsers" x-transition class="pl-8 mt-1 space-y-1">
                    <a href="{{ route('receptionist.patients.index') }}"
                        class="flex items-center px-3 py-2 rounded-md text-sm {{ request()->routeIs('receptionist.patients.*') ? 'bg-emerald-50 text-emerald-600 font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
                        <i class="fa-solid fa-bed-pulse w-4 mr-2 text-gray-400"></i> Bệnh nhân
                    </a>
                    <a href="{{ route('receptionist.customers.index') }}"
                        class="flex items-center px-3 py-2 rounded-md text-sm {{ request()->routeIs('receptionist.customers.*') ? 'bg-emerald-50 text-emerald-600 font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
                        <i class="fa-solid fa-user w-4 mr-2 text-gray-400"></i> Khách hàng
                    </a>
                </div>
            </div>

            <a href="{{ route('receptionist.clinical-visits.index') }}"
                class="{{ request()->routeIs('receptionist.clinical-visits.*') ? 'bg-emerald-50 text-emerald-600' : 'text-gray-700 hover:bg-gray-100' }} group flex items-center px-3 py-2 text-sm font-medium rounded-md mt-1">
                <i class="fa-solid fa-microscope w-5 text-center mr-3 {{ request()->routeIs('receptionist.clinical-visits.*') ? 'text-emerald-600' : 'text-gray-400 group-hover:text-gray-500' }}"></i>
                Giám sát lâm sàng
            </a>

            <a href="{{ route('receptionist.payments.index') }}"
                class="{{ request()->routeIs('receptionist.payments.*') ? 'bg-emerald-50 text-emerald-600' : 'text-gray-700 hover:bg-gray-100' }} group flex items-center px-3 py-2 text-sm font-medium rounded-md mt-1">
                <i class="fa-solid fa-file-invoice-dollar w-5 text-center mr-3 {{ request()->routeIs('receptionist.payments.*') ? 'text-emerald-600' : 'text-gray-400 group-hover:text-gray-500' }}"></i>
                Lịch sử thanh toán
            </a>
            
            <a href="{{ route('receptionist.profile.index') }}"
                class="{{ request()->routeIs('receptionist.profile.*') ? 'bg-emerald-50 text-emerald-600' : 'text-gray-700 hover:bg-gray-100' }} group flex items-center px-3 py-2 text-sm font-medium rounded-md mt-1">
                <i class="fa-solid fa-user-circle w-5 text-center mr-3 {{ request()->routeIs('receptionist.profile.*') ? 'text-emerald-600' : 'text-gray-400 group-hover:text-gray-500' }}"></i>
                Thông tin cá nhân
            </a>
        </div>

        <!-- User bottom -->
        <div class="border-t border-gray-200 p-4 flex items-center">
            <div class="flex-shrink-0">
                <div class="h-9 w-9 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-sm">
                    {{ Auth::user()->avatar_initials }}
                </div>
            </div>
            <div class="ml-3 flex-1 overflow-hidden">
                <p class="text-sm font-medium text-gray-900 truncate">{{ Auth::user()->full_name }}</p>
                <p class="text-xs text-gray-500 truncate">{{ Auth::user()->display_role }}</p>
            </div>
            <form action="{{ route('receptionist.logout') }}" method="POST" class="ml-2">
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
                <h1 class="text-lg font-semibold text-gray-900">{{ $title ?? 'Bảng điều khiển Lễ tân' }}</h1>
            </div>

            <div class="flex items-center gap-4">
                <div x-data="{ userMenuOpen: false }" class="relative">
                    <button @click="userMenuOpen = !userMenuOpen" @click.outside="userMenuOpen = false"
                        class="flex items-center gap-2 text-sm text-gray-700 hover:text-gray-900 focus:outline-none">
                        <div class="h-8 w-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-xs">
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
                        <a href="{{ route('receptionist.profile.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Hồ sơ cá nhân</a>
                        <form action="{{ route('receptionist.logout') }}" method="POST">
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
