<!DOCTYPE html>
<html lang="vi" class="antialiased">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ $metaDescription ?? 'CareBook - Đặt lịch khám bệnh trực tuyến nhanh chóng, tiện lợi.' }}">
    <title>{{ $title ?? 'CareBook' }} - CareBook</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-light: #eff6ff;
            --secondary: #2563eb;
            --surface: #ffffff;
            --bg: #f8fafc;
            --border: #e2e8f0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Be Vietnam Pro', sans-serif;
            background-color: var(--bg);
            color: #1e293b;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        [x-cloak] {
            display: none !important;
        }

        /* Animation Utilities */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-down {
            animation: fadeInDown 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .text-primary {
            color: var(--primary);
        }

        .bg-primary {
            background-color: var(--primary);
        }

        .border-primary {
            border-color: var(--primary);
        }

        .hover\:bg-primary-dark:hover {
            background-color: var(--primary-dark);
        }

        .bg-primary\/5 {
            background-color: rgba(37, 99, 235, 0.05);
        }

        .bg-primary\/10 {
            background-color: rgba(37, 99, 235, 0.10);
        }

        .border-primary\/10 {
            border-color: rgba(37, 99, 235, 0.10);
        }

        .text-primary\/80 {
            color: rgba(37, 99, 235, 0.80);
        }

        .ring-primary {
            --tw-ring-color: var(--primary);
        }

        .focus\:ring-primary:focus {
            --tw-ring-color: var(--primary);
        }

        .focus\:border-primary:focus {
            border-color: var(--primary);
        }

        .text-secondary {
            color: var(--secondary);
        }

        .bg-secondary {
            background-color: var(--secondary);
        }

        .hover\:text-secondary:hover {
            color: var(--secondary);
        }

        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }

        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
    @stack('styles')
</head>

<body class="min-h-screen bg-slate-50 flex flex-col relative">

    <!-- Global Loading Overlay -->
    <div id="global-loader" class="fixed inset-0 z-[9999] bg-white/80 backdrop-blur-sm flex flex-col items-center justify-center hidden opacity-0 transition-opacity duration-300">
        <div class="relative w-20 h-20">
            <div class="absolute inset-0 border-4 border-primary/20 rounded-full"></div>
            <div class="absolute inset-0 border-4 border-primary border-t-transparent rounded-full animate-spin"></div>
        </div>
        <p class="mt-4 text-primary font-bold text-lg animate-pulse">Đang xử lý...</p>
    </div>

    <!-- Premium Patient Top Nav -->
    <header id="patient-header" class="sticky top-0 z-50 bg-white shadow-sm transition-all duration-300">
        <!-- Top Bar (White) -->
        <div class="w-full px-4 md:px-8 xl:px-12 py-2 md:h-20 flex items-center justify-between gap-2 md:gap-4 relative">
            <!-- Left Side: Logo -->
            <a href="{{ route('home') }}" class="flex items-center gap-3 group shrink-0 relative z-20">
                <div class="w-10 h-10 md:w-12 md:h-12 rounded-full bg-secondary text-white flex items-center justify-center text-xl shadow-lg">
                    <i class="fa-solid fa-hospital"></i>
                </div>
                <div class="hidden sm:block">
                    <h1 class="font-bold text-lg md:text-xl uppercase tracking-tight text-secondary">Bệnh Viện CareBook</h1>
                    <p class="text-[9px] md:text-[11px] font-semibold uppercase tracking-widest text-secondary opacity-80">CareBook Hospital</p>
                </div>
            </a>

            <!-- Slogan (Centered) -->
            <div class="hidden lg:flex flex-1 justify-center absolute inset-0 items-center pointer-events-none z-10">
                <span class="text-[1.7rem] font-[cursive] italic drop-shadow-sm text-amber-500">Vì sức khoẻ nhân dân</span>
            </div>

            <!-- Right Side: User actions -->
            <div class="flex items-center gap-3 md:gap-4 text-xs md:text-sm font-medium text-slate-700 shrink-0 relative z-20">
                <div class="hidden md:flex items-center gap-1.5 font-bold text-slate-800">
                    <i class="fa-solid fa-phone-volume"></i>
                    <span>1900.888.866</span>
                </div>

                @auth
                <!-- Tích hợp Thông báo Bệnh nhân (Bell & Popup) -->
                <div x-data="{ openDropdown: false }" class="relative flex items-center">
                    <!-- Nút Chuông -->
                    <button @click="openDropdown = !openDropdown" class="relative p-2 text-slate-600 hover:text-secondary transition-colors" title="Thông báo">
                        <i class="fa-solid fa-bell text-lg"></i>
                        @if(isset($unreadCount) && $unreadCount > 0)
                        <span class="absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-[9px] font-bold leading-none text-white transform translate-x-1/4 -translate-y-1/4 bg-red-500 rounded-full">{{ $unreadCount }}</span>
                        @endif
                    </button>

                    <!-- Dropdown danh sách -->
                    <div x-show="openDropdown" @click.away="openDropdown = false" x-cloak style="display: none;" class="absolute right-0 top-full mt-2 w-80 bg-white border border-slate-200 shadow-2xl rounded-lg py-2 z-50 overflow-hidden flex flex-col max-h-[80vh]">
                        <div class="px-4 py-2 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                            <h3 class="font-bold text-slate-800 text-sm">Thông báo của bạn</h3>
                            @if(isset($unreadCount) && $unreadCount > 0)
                            <form action="{{ route('patient.notifications.read') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" onclick="showGlobalLoader()" class="text-xs text-blue-600 hover:text-blue-800 font-semibold">Đánh dấu đã đọc</button>
                            </form>
                            @endif
                        </div>
                        <div class="overflow-y-auto flex-1 p-2 space-y-1">
                            @if(isset($notifications) && count($notifications) == 0)
                            <p class="text-center text-slate-500 py-4 text-xs italic">Chưa có thông báo nào</p>
                            @endif
                            @if(isset($notifications))
                            @foreach($notifications as $notif)
                            <a href="{{ route('patient.notifications.show', $notif->id) }}" data-loader="true" class="block p-3 rounded-md hover:bg-slate-50 transition-colors cursor-pointer border border-transparent hover:border-slate-100 flex flex-col gap-1 {{ $notif->is_read ? 'opacity-70' : 'bg-blue-50/50' }}">
                                <div class="flex justify-between items-start">
                                    <h4 class="font-bold text-sm {{ in_array($notif->type, ['cancellation', 'system_cancellation']) ? 'text-red-600' : ($notif->type === 'patient_cancellation' ? 'text-slate-600' : 'text-slate-800') }}">{{ $notif->title }}</h4>
                                    @if(!$notif->is_read)
                                    <span class="w-2 h-2 rounded-full bg-blue-500 flex-shrink-0 mt-1"></span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-600 leading-relaxed">{{ $notif->content }}</p>
                            </a>
                            @endforeach
                            @endif
                        </div>
                        <div class="px-4 py-2 border-t border-slate-100 bg-slate-50 text-center">
                            <a href="{{ route('patient.notifications.page') }}" data-loader="true" class="text-xs font-bold text-secondary hover:underline">Xem tất cả thông báo</a>
                        </div>
                    </div>
                </div>

                <div class="hidden md:block w-px h-4 bg-slate-300"></div>
                <div class="hidden md:flex items-center gap-1.5 text-slate-600">
                    <span>Xin chào, <strong class="text-slate-900">{{ auth()->user()->full_name ?? 'Bệnh nhân' }}</strong></span>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="flex items-center">
                    @csrf
                    <button type="submit" class="flex items-center gap-1.5 hover:text-rose-600 transition-colors text-slate-600">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i> <span>Đăng xuất</span>
                    </button>
                </form>
                <div class="px-1.5 py-0.5 border border-slate-300 rounded text-slate-600 font-bold bg-white text-[10px]">VN</div>
                @else
                <div class="hidden md:block w-px h-4 bg-slate-300"></div>
                <a href="{{ route('patient.login') }}" class="transition-colors font-bold text-secondary hover:opacity-80"><i class="fa-solid fa-user"></i> Đăng nhập</a>
                @endauth
            </div>
        </div>

        <!-- Bottom Bar (Green Navigation) -->
        <div class="bg-secondary text-white shadow-md">
            <div class="w-full px-4 md:px-8 xl:px-12">
                <!-- Removed overflow-x-auto to prevent dropdown clipping -->
                <nav class="flex flex-wrap items-center justify-center gap-x-4 gap-y-2 md:gap-8 text-xs md:text-sm font-bold uppercase py-3 md:py-3.5">
                    <a href="{{ route('home') }}" class="whitespace-nowrap hover:text-amber-300 transition-colors">Cổng thông tin</a>
                    <a href="#" class="whitespace-nowrap hover:text-amber-300 transition-colors hidden sm:block">Đội ngũ bác sĩ</a>
                    <a href="{{ route('patient.booking.step1') }}" class="whitespace-nowrap hover:text-amber-300 transition-colors">Đặt lịch khám</a>

                    @auth
                    <!-- Dropdown Cá nhân -->
                    <div x-data="{ open: false }" class="relative" @mouseleave="open = false" @mouseenter="open = true">
                        <button @click="open = !open" class="whitespace-nowrap flex items-center gap-1.5 hover:text-amber-300 transition-colors outline-none pb-1 md:pb-0 uppercase">
                            Cá nhân <i class="fa-solid fa-chevron-down text-[10px]"></i>
                        </button>

                        <div x-show="open" x-cloak
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="opacity-0 translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-1"
                            class="absolute top-full left-0 md:left-auto md:right-0 mt-0 w-60 bg-white border border-slate-200 shadow-xl rounded-lg py-2 z-50 text-slate-700 normal-case">

                            <a href="{{ route('patient.dashboard') }}" class="block px-4 py-2 hover:bg-slate-50 text-sm font-semibold transition-colors uppercase hover:text-secondary">Thông tin cá nhân</a>
                            <a href="{{ route('patient.family.index') }}" class="block px-4 py-2 hover:bg-slate-50 text-sm font-semibold transition-colors uppercase hover:text-secondary">Quản lý gia đình</a>
                            <a href="{{ route('patient.records.index') }}" class="block px-4 py-2 hover:bg-slate-50 text-sm font-semibold transition-colors uppercase hover:text-secondary">Kết quả khám bệnh</a>
                            <a href="{{ route('patient.appointments.index') }}" class="block px-4 py-2 hover:bg-slate-50 text-sm font-semibold transition-colors uppercase hover:text-secondary">Lịch sử đặt lịch khám</a>
                            <a href="#" class="block px-4 py-2 hover:bg-slate-50 text-sm font-semibold transition-colors uppercase hover:text-secondary">Thay đổi mật khẩu</a>

                            <div class="border-t border-slate-100 mt-1 pt-1">
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 hover:bg-rose-50 hover:text-rose-600 text-sm font-semibold transition-colors uppercase">
                                        Đăng xuất
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endauth
                </nav>
            </div>
        </div>
    </header>

    <!-- Main slot -->
    <main class="flex-1 w-full">
        {{ $slot }}
    </main>

    @stack('scripts')
    <script>
        // Hiển thị Global Loader
        function showGlobalLoader() {
            const loader = document.getElementById('global-loader');
            if (loader) {
                loader.classList.remove('hidden');
                // Nhường một chút thời gian để trình duyệt render class hidden trước khi thay đổi opacity
                setTimeout(() => loader.classList.remove('opacity-0'), 10);
            }
        }

        // Tự động gắn sự kiện hiển thị Loader
        document.addEventListener('DOMContentLoaded', () => {
            // 1. Khi submit bất kỳ form nào
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', () => {
                    showGlobalLoader();
                });
            });

            // 2. Khi click vào thẻ a có data-loader (ví dụ: chuyển bước đặt lịch)
            document.querySelectorAll('a[data-loader="true"]').forEach(link => {
                link.addEventListener('click', (e) => {
                    // Tránh loader nếu link mở tab mới hoặc ngăn chặn mặc định
                    if (link.target !== '_blank' && !e.defaultPrevented) {
                        showGlobalLoader();
                    }
                });
            });
        });

        // Ẩn loader khi người dùng quay lại bằng nút Back của trình duyệt (BFCache)
        window.addEventListener('pageshow', (e) => {
            if (e.persisted) {
                const loader = document.getElementById('global-loader');
                if (loader) {
                    loader.classList.add('hidden', 'opacity-0');
                }
            }
        });
    </script>
</body>

</html>