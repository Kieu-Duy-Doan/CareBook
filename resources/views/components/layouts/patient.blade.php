<!DOCTYPE html>
<html lang="vi" class="antialiased">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

        * { box-sizing: border-box; }

        body {
            font-family: 'Be Vietnam Pro', sans-serif;
            background-color: var(--bg);
            color: #1e293b;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        [x-cloak] { display: none !important; }

        /* Animation Utilities */
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in-down {
            animation: fadeInDown 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .text-primary { color: var(--primary); }
        .bg-primary { background-color: var(--primary); }
        .border-primary { border-color: var(--primary); }
        .hover\:bg-primary-dark:hover { background-color: var(--primary-dark); }
        .bg-primary\/5 { background-color: rgba(37,99,235,0.05); }
        .bg-primary\/10 { background-color: rgba(37,99,235,0.10); }
        .border-primary\/10 { border-color: rgba(37,99,235,0.10); }
        .text-primary\/80 { color: rgba(37,99,235,0.80); }
        .ring-primary { --tw-ring-color: var(--primary); }
        .focus\:ring-primary:focus { --tw-ring-color: var(--primary); }
        .focus\:border-primary:focus { border-color: var(--primary); }
        
        .text-secondary { color: var(--secondary); }
        .bg-secondary { background-color: var(--secondary); }
        .hover\:text-secondary:hover { color: var(--secondary); }
        
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
    @stack('styles')
</head>

<body class="min-h-screen bg-slate-50 flex flex-col">

    <!-- Premium Patient Top Nav -->
    <header id="patient-header" class="sticky top-0 z-50 bg-white shadow-sm transition-all duration-300">
        <!-- Top Bar (White) -->
        <div class="w-full px-4 md:px-6 py-2 md:h-20 flex items-center justify-between gap-2 md:gap-4 relative">
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
                <div class="hidden md:block w-px h-4 bg-slate-300"></div>
                <div class="hidden md:flex items-center gap-1.5 text-slate-600">
                    <span>Xin chào, <strong class="text-slate-900">{{ auth()->user()->full_name ?? 'Bệnh nhân' }}</strong></span>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="flex items-center">
                    @csrf
                    <button type="submit" class="flex items-center gap-1.5 hover:text-rose-600 transition-colors text-slate-600">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i> <span class="hidden sm:inline">Đăng xuất</span>
                    </button>
                </form>
                <div class="px-1.5 py-0.5 border border-slate-300 rounded text-slate-600 font-bold bg-white text-[10px]">VN</div>
                @else
                <div class="hidden md:block w-px h-4 bg-slate-300"></div>
                <a href="{{ route('login') }}" class="transition-colors font-bold text-secondary hover:opacity-80"><i class="fa-solid fa-user"></i> Đăng nhập</a>
                @endauth
            </div>
        </div>

        <!-- Bottom Bar (Green Navigation) -->
        <div class="bg-secondary text-white shadow-md">
            <div class="w-full px-4 md:px-6">
                <!-- Removed overflow-x-auto to prevent dropdown clipping -->
                <nav class="flex flex-wrap items-center justify-center gap-x-4 gap-y-2 md:gap-8 text-xs md:text-sm font-bold uppercase py-3 md:py-3.5">
                    <a href="{{ route('home') }}" class="whitespace-nowrap hover:text-amber-300 transition-colors">Cổng thông tin</a>
                    <a href="#" class="whitespace-nowrap hover:text-amber-300 transition-colors hidden sm:block">Đội ngũ bác sĩ</a>
                    <a href="{{ route('patient.booking.index') }}" class="whitespace-nowrap hover:text-amber-300 transition-colors">Đặt lịch khám</a>
                    
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
</body>

</html>
