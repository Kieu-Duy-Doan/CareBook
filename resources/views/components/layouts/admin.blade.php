<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Admin' }} - CareBook</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
    {!! $styles ?? '' !!}
    @stack('styles')
</head>

<body class="font-sans antialiased bg-gray-50 text-gray-900" x-data="{ sidebarOpen: false }">
    <!-- Mobile sidebar backdrop -->
    <div x-show="sidebarOpen" class="fixed inset-0 z-40 bg-gray-900/80 lg:hidden" x-transition.opacity
        @click="sidebarOpen = false" style="display: none;"></div>
    <!-- Sidebar -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="-translate-x-full lg:translate-x-0 fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 transition-transform duration-300 flex flex-col">
        <!-- Logo -->
        <div class="flex items-center gap-2 px-6 py-5 border-b border-gray-200 text-blue-600">
            <i class="fa-solid fa-hospital-user text-2xl"></i>
            <span class="text-xl font-bold">CareBook</span>
        </div>
        <!-- Nvigation -->
        <div class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
            <a href="{{ route('admin.dashboard') }}"
                class="{{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }} group flex items-center px-3 py-2 text-sm font-medium rounded-md">
                <i
                    class="fa-solid fa-house w-6 text-center mr-2 {{ request()->routeIs('admin.dashboard') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-500' }}"></i>
                Dashboard
            </a>
            <!-- Quản lý tài khoản -->
            <div x-data="{ openUsers: {{ request()->routeIs('admin.users.*', 'admin.doctors.*', 'admin.receptionists.*', 'admin.patients.*') ? 'true' : 'false' }} }">
                <button @click="openUsers = !openUsers"
                    class="w-full flex items-center justify-between px-3 py-2 rounded-md text-sm font-medium
                               {{ request()->routeIs('admin.users.*', 'admin.doctors.*', 'admin.receptionists.*', 'admin.patients.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
                    <span class="flex items-center">
                        <i
                            class="fa-solid fa-users-gear w-5 text-center mr-3 {{ request()->routeIs('admin.users.*', 'admin.doctors.*', 'admin.receptionists.*', 'admin.patients.*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        Quản lý tài khoản
                    </span>
                    <i class="fa-solid fa-chevron-down text-xs transition-transform"
                        :class="openUsers ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openUsers" x-cloak x-transition class="pl-8 mt-1 space-y-1">
                    <a href="{{ route('admin.users.index') }}"
                        class="flex items-center px-3 py-2 rounded-md text-sm {{ request()->routeIs('admin.users.*') ? 'bg-blue-50 text-blue-600 font-medium' : 'text-gray-600 hover:bg-gray-100' }}"><i
                            class="fa-solid fa-list-ul w-4 mr-2 text-gray-400"></i> Tất cả tài khoản</a>
                    <a href="{{ route('admin.doctors.index') }}"
                        class="flex items-center px-3 py-2 rounded-md text-sm {{ request()->routeIs('admin.doctors.*') ? 'bg-purple-50 text-purple-700 font-medium' : 'text-gray-600 hover:bg-gray-100' }}"><i
                            class="fa-solid fa-user-doctor w-4 mr-2 text-purple-400"></i> Bác sĩ</a>
                    <a href="{{ route('admin.receptionists.index') }}"
                        class="flex items-center px-3 py-2 rounded-md text-sm {{ request()->routeIs('admin.receptionists.*') ? 'bg-blue-50 text-blue-600 font-medium' : 'text-gray-600 hover:bg-gray-100' }}"><i
                            class="fa-solid fa-user-nurse w-4 mr-2 text-gray-400"></i> Lễ tân</a>
                    <a href="{{ Route::has('admin.patients.index') ? route('admin.patients.index') : '#' }}"
                        class="flex items-center px-3 py-2 rounded-md text-sm {{ request()->routeIs('admin.patients.*') ? 'bg-blue-50 text-blue-600 font-medium' : 'text-gray-600 hover:bg-gray-100' }}"><i
                            class="fa-solid fa-bed-pulse w-4 mr-2 text-gray-400"></i> Bệnh nhân</a>
                </div>
            </div>

            <!-- Single menus -->
            <a href="{{ route('admin.specialties.index') }}"
                class="{{ request()->routeIs('admin.specialties.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }} group flex items-center px-3 py-2 text-sm font-medium rounded-md mt-1">
                <i
                    class="fa-solid fa-stethoscope w-5 text-center mr-3 {{ request()->routeIs('admin.specialties.*') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-500' }}"></i>
                Chuyên khoa
            </a>
            <a href="{{ route('admin.rooms.index') }}"
                class="{{ request()->routeIs('admin.rooms.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }} group flex items-center px-3 py-2 text-sm font-medium rounded-md mt-1">
                <i
                    class="fa-solid fa-door-open w-5 text-center mr-3 {{ request()->routeIs('admin.rooms.*') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-500' }}"></i>
                Phòng khám
            </a>
            <a href="{{ route('admin.work-schedules.index') }}"
                class="{{ request()->routeIs('admin.work-schedules.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }} group flex items-center px-3 py-2 text-sm font-medium rounded-md mt-1">
                <i
                    class="fa-regular fa-calendar w-5 text-center mr-3 {{ request()->routeIs('admin.work-schedules.*') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-500' }}"></i>
                Lịch làm việc
            </a>
            <a href="{{ route('admin.appointments.index') }}"
                class="{{ request()->routeIs('admin.appointments.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }} group flex items-center px-3 py-2 text-sm font-medium rounded-md mt-1">
                <i
                    class="fa-solid fa-calendar-check w-5 text-center mr-3 {{ request()->routeIs('admin.appointments.*') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-500' }}"></i>
                Lịch hẹn
            </a>
            <a href="{{ Route::has('admin.clinical-visits.index') ? route('admin.clinical-visits.index') : '#' }}"
                class="{{ request()->routeIs('admin.clinical-visits.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }} group flex items-center px-3 py-2 text-sm font-medium rounded-md mt-1">
                <i
                    class="fa-solid fa-microscope w-5 text-center mr-3 {{ request()->routeIs('admin.clinical-visits.*') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-500' }}"></i>
                Giám sát khám lâm sàng
            </a>
            <a href="{{ Route::has('admin.appointment-logs.index') ? route('admin.appointment-logs.index') : '#' }}"
                class="{{ request()->routeIs('admin.appointment-logs.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }} group flex items-center px-3 py-2 text-sm font-medium rounded-md mt-1">
                <i
                    class="fa-solid fa-clock-rotate-left w-5 text-center mr-3 {{ request()->routeIs('admin.appointment-logs.*') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-500' }}"></i>
                Nhật ký lịch hẹn
            </a>


            <a href="{{ route('admin.posts.index') }}"
                class="{{ request()->routeIs('admin.posts.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }} group flex items-center px-3 py-2 text-sm font-medium rounded-md mt-1">
                <i
                    class="fa-regular fa-clipboard w-5 text-center mr-3 {{ request()->routeIs('admin.posts.*') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-500' }}"></i>
                Bài viết
            </a>

            <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider mt-4 mb-1">
                Hệ thống
            </div>

            <!-- QUẢN LÝ CHATBOT -->
            <div x-data="{ openChatbot: {{ request()->routeIs('admin.chatbot.*', 'admin.faqs.*') ? 'true' : 'false' }} }">
                <button @click="openChatbot = !openChatbot"
                    class="w-full flex items-center justify-between px-3 py-2 rounded-md text-sm font-medium mt-1
                               {{ request()->routeIs('admin.chatbot.*', 'admin.faqs.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
                    <span class="flex items-center">
                        <i
                            class="fa-solid fa-robot w-5 text-center mr-3 {{ request()->routeIs('admin.chatbot.*', 'admin.faqs.*') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-500' }}"></i>
                        Quản lý chatbot
                    </span>
                    <i class="fa-solid fa-chevron-down text-xs transition-transform"
                        :class="openChatbot ? 'rotate-180' : ''"></i>
                </button>

                <div x-show="openChatbot" x-cloak x-transition class="pl-8 mt-1 space-y-1">
                    <a href="{{ route('admin.chatbot.intents.index') }}"
                        class="flex items-center px-3 py-2 rounded-md text-sm
                              {{ request()->routeIs('admin.chatbot.intents.*') ? 'bg-blue-50/50 text-blue-600 font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
                        Kịch bản & phản hồi
                    </a>
                    <a href="{{ route('admin.faqs.index') }}"
                        class="flex items-center px-3 py-2 rounded-md text-sm
                              {{ request()->routeIs('admin.faqs.*') ? 'bg-blue-50/50 text-blue-600 font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
                        Câu hỏi thường gặp
                    </a>
                    <a href="{{ route('admin.chatbot.sessions.index') }}"
                        class="flex items-center px-3 py-2 rounded-md text-sm
                              {{ request()->routeIs('admin.chatbot.sessions.*') ? 'bg-blue-50/50 text-blue-600 font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
                        Lịch sử chat
                    </a>
                </div>
            </div>


            <!-- Single System Menus -->
            <a href="{{ route('admin.notifications.index') }}"
                class="{{ request()->routeIs('admin.notifications.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }} group flex items-center px-3 py-2 text-sm font-medium rounded-md mt-1">
                <i
                    class="fa-solid fa-bell w-5 text-center mr-3 {{ request()->routeIs('admin.notifications.*') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-500' }}"></i>
                Thông báo
            </a>
            <a href="{{ route('admin.settings.index') }}"
                class="{{ request()->routeIs('admin.settings.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }} group flex items-center px-3 py-2 text-sm font-medium rounded-md mt-1 mb-4">
                <i
                    class="fa-solid fa-gear w-5 text-center mr-3 {{ request()->routeIs('admin.settings.*') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-500' }}"></i>
                Cài đặt hệ thống
            </a>
        </div>

        <!-- User bottom -->
        <div class="border-t border-gray-200 p-4 flex items-center">
            <div class="flex-shrink-0">
                <div
                    class="h-9 w-9 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm">
                    {{ Auth::user()->avatar_initials }}
                </div>
            </div>
            <div class="ml-3 flex-1 overflow-hidden">
                <p class="text-sm font-medium text-gray-900 truncate">{{ Auth::user()->full_name }}</p>
                <p class="text-xs text-gray-500 truncate">{{ Auth::user()->display_role }}</p>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="ml-2">
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
        <header
            class="sticky top-0 z-30 bg-white border-b border-gray-200 h-16 flex items-center justify-between px-4 sm:px-6">
            <div class="flex items-center">
                <button @click="sidebarOpen = true" class="lg:hidden text-gray-500 hover:text-gray-700 mr-4">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                <h1 class="text-lg font-semibold text-gray-900">{{ $title ?? 'Quản trị hệ thống' }}</h1>
            </div>

            <div class="flex items-center gap-4">
                <a href="{{ route('admin.notifications.index') }}" class="text-gray-400 hover:text-gray-600 relative"
                    title="Quản lý thông báo">
                    <i class="fa-regular fa-bell text-xl"></i>
                    <!-- <span class="absolute top-0 right-0 -mt-1 -mr-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white">3</span> -->
                </a>

                <div x-data="{ userMenuOpen: false }" class="relative">
                    <button @click="userMenuOpen = !userMenuOpen" @click.outside="userMenuOpen = false"
                        class="flex items-center gap-2 text-sm text-gray-700 hover:text-gray-900 focus:outline-none">
                        <div
                            class="h-8 w-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs">
                            {{ Auth::user()->avatar_initials }}
                        </div>
                        <i class="fa-solid fa-chevron-down text-xs text-gray-400"></i>
                    </button>

                    <div x-show="userMenuOpen" style="display: none;"
                        class="absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none"
                        x-transition>
                        <div class="px-4 py-2 border-b border-gray-100">
                            <p class="text-sm text-gray-900 font-medium">{{ Auth::user()->full_name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email ?? Auth::user()->phone }}
                            </p>
                        </div>
                        <a href="{{ route('admin.users.show', Auth::id()) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Hồ sơ cá
                            nhân</a>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">Đăng
                                xuất</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>
        <!-- Content -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto">
            {{ $slot }}
        </main>
    </div>

    {!! $scripts ?? '' !!}
    @stack('scripts')
</body>

</html>
