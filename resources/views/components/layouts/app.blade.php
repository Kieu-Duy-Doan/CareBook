<!DOCTYPE html>
<html lang="vi" class="antialiased">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $metaDescription ?? 'CareBook - Bệnh viện hạng đặc biệt đầu tiên của cả nước.' }}">
    <title>{{ $title ?? 'CareBook' }} - CareBook Hospital</title>
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
            --secondary-dark: #1d4ed8;
            --surface: #ffffff;
            --bg: #f8fafc;
            --border: #e2e8f0;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Be Vietnam Pro', sans-serif;
            background-color: var(--surface);
            color: #1e293b;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        [x-cloak] { display: none !important; }

        .text-primary { color: var(--primary); }
        .bg-primary { background-color: var(--primary); }
        .bg-secondary { background-color: var(--secondary); }
        .text-secondary { color: var(--secondary); }
    </style>
    @stack('styles')
</head>

<body class="min-h-screen flex flex-col">

    <!-- Top Contact Bar (Bach Mai Style) -->
    <div class="bg-secondary text-white py-2 hidden md:block">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-end items-center gap-6 text-sm font-medium">
            <span class="flex items-center gap-2 border border-white/30 rounded-full px-4 py-1 hover:bg-white/10 transition-colors">
                <i class="fa-solid fa-phone-volume"></i>
                Gọi tổng đài 1900.888.866
            </span>
            <a href="{{ route('patient.booking.index') }}" class="flex items-center gap-2 border border-white/30 rounded-full px-4 py-1 hover:bg-white/10 transition-colors">
                <i class="fa-regular fa-calendar-check"></i>
                Đặt lịch khám
            </a>
            <div class="flex items-center gap-2">
                <span class="fi fi-vn rounded-sm shadow-sm"></span>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <header class="bg-white sticky top-0 z-50 shadow-sm border-b border-slate-100 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Logo & Brand -->
                <a href="{{ route('home') }}" class="flex items-center gap-4 group shrink-0">
                    <div class="w-12 h-12 rounded-full bg-secondary text-white flex items-center justify-center text-xl shadow-lg shadow-secondary/20 group-hover:scale-105 transition-transform duration-300">
                        <i class="fa-solid fa-hospital"></i>
                    </div>
                    <div>
                        <h1 class="font-bold text-xl md:text-2xl text-secondary uppercase tracking-tight group-hover:text-secondary-dark transition-colors">Bệnh Viện CareBook</h1>
                        <p class="text-[10px] md:text-xs text-secondary/70 font-semibold uppercase tracking-widest">CareBook Hospital</p>
                    </div>
                </a>

                <!-- Desktop Navigation -->
                <nav class="hidden lg:flex items-center gap-6 xl:gap-8 font-bold text-slate-700 text-[15px]">
                    <a href="{{ route('home') }}" class="text-secondary border-b-2 border-secondary py-1">Trang chủ</a>
                    <a href="{{ route('doctors.directory') }}" class="hover:text-secondary transition-colors py-1">Đội ngũ bác sĩ</a>
                    <a href="#" class="hover:text-secondary transition-colors py-1">Tin tức</a>
                    
                    @auth
                        <!-- User Menu -->
                        <div x-data="{ open: false }" class="relative ml-4">
                            <button @click="open = !open" @click.outside="open = false"
                                    class="flex items-center gap-2 px-3 py-2 rounded-xl border border-slate-200 hover:border-secondary hover:text-secondary transition-colors bg-slate-50">
                                <div class="w-6 h-6 rounded-full bg-secondary/10 flex items-center justify-center text-xs text-secondary shrink-0">
                                    {{ substr(auth()->user()->full_name ?? 'U', 0, 1) }}
                                </div>
                                <span>{{ auth()->user()->full_name ?? 'Tài khoản' }}</span>
                                <i class="fa-solid fa-chevron-down text-[10px] transition-transform duration-300" :class="open ? 'rotate-180' : ''"></i>
                            </button>

                            <div x-show="open" x-cloak 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                                 class="absolute right-0 mt-3 w-56 rounded-2xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.1)] bg-white border border-slate-100 overflow-hidden z-50">
                                
                                <div class="px-5 py-3.5 bg-slate-50/50 border-b border-slate-100">
                                    <p class="text-sm font-bold text-slate-800 truncate">{{ auth()->user()->full_name ?? '' }}</p>
                                    <p class="text-xs font-medium text-slate-500 truncate mt-0.5">{{ auth()->user()->phone ?? auth()->user()->email ?? '' }}</p>
                                </div>
                                
                                <div class="p-2 space-y-1">
                                    <a href="{{ route('patient.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-slate-700 rounded-xl hover:bg-slate-50 hover:text-secondary transition-colors">
                                        <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500 group-hover:text-secondary"><i class="fa-solid fa-house-medical"></i></div>
                                        Trang cá nhân
                                    </a>
                                </div>
                                <div class="p-2 border-t border-slate-100">
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-rose-600 rounded-xl hover:bg-rose-50 transition-colors">
                                            <div class="w-8 h-8 rounded-lg bg-rose-50 flex items-center justify-center text-rose-500"><i class="fa-solid fa-arrow-right-from-bracket"></i></div>
                                            Đăng xuất
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('patient.login') }}" class="ml-4 flex items-center gap-2 px-5 py-2.5 rounded-full bg-secondary text-white hover:bg-secondary-dark transition-colors shadow-lg shadow-secondary/20">
                            <i class="fa-regular fa-user text-sm"></i>
                            Đăng nhập
                        </a>
                    @endauth
                </nav>

                <!-- Mobile Menu Button -->
                <div class="lg:hidden flex items-center gap-4">
                    <button class="text-secondary hover:bg-slate-100 p-2 rounded-lg transition-colors">
                        <i class="fa-solid fa-bars text-2xl"></i>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 w-full">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="bg-secondary text-white mt-auto pt-16 pb-8 border-t-[8px] border-amber-400">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 mb-12">
                <!-- Branding -->
                <div class="col-span-1 lg:col-span-2">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-14 h-14 rounded-full bg-white text-secondary flex items-center justify-center text-2xl shadow-lg">
                            <i class="fa-solid fa-hospital"></i>
                        </div>
                        <div>
                            <h2 class="font-bold text-2xl uppercase tracking-tight">Bệnh Viện CareBook</h2>
                            <p class="text-sm text-white/80 font-semibold uppercase tracking-widest">CareBook Hospital</p>
                        </div>
                    </div>
                    <ul class="space-y-4 text-white/90">
                        <li class="flex items-start gap-3">
                            <i class="fa-solid fa-location-dot mt-1 text-amber-400"></i>
                            <span>Địa chỉ: 78 Đường Giải Phóng, Phường Phương Mai, Quận Đống Đa, Hà Nội</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="fa-solid fa-phone-volume text-amber-400"></i>
                            <span>Tổng đài: <strong>1900.888.866</strong></span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="fa-solid fa-envelope text-amber-400"></i>
                            <span>Email: contact@carebook.vn</span>
                        </li>
                    </ul>
                </div>

                <!-- Lịch làm việc -->
                <div>
                    <h3 class="font-bold text-lg mb-6 text-amber-400">Lịch làm việc</h3>
                    <div class="space-y-4 text-sm text-white/90">
                        <p class="font-bold">Khoa Khám bệnh theo yêu cầu:</p>
                        <ul class="list-disc list-inside space-y-1 ml-1">
                            <li>Thứ 2 - Thứ 6: 06:00 - 20:00</li>
                            <li>Thứ 7 - Chủ nhật: 06:30 - 16:30</li>
                        </ul>
                        <p class="font-bold mt-4">Khoa Khám bệnh:</p>
                        <ul class="list-disc list-inside space-y-1 ml-1">
                            <li>Thứ 2 - Thứ 6</li>
                            <li>Sáng: 07:00 - 12:00</li>
                            <li>Chiều: 13:30 - 16:30</li>
                        </ul>
                    </div>
                </div>

                <!-- Links -->
                <div>
                    <h3 class="font-bold text-lg mb-6 text-amber-400">Liên kết nhanh</h3>
                    <ul class="space-y-3 text-white/90 font-medium">
                        <li><a href="#" class="hover:text-white hover:underline transition-all flex items-center gap-2"><i class="fa-solid fa-angle-right text-xs"></i> Về CareBook</a></li>
                        <li><a href="#" class="hover:text-white hover:underline transition-all flex items-center gap-2"><i class="fa-solid fa-angle-right text-xs"></i> Đơn vị chuyên khoa</a></li>
                        <li><a href="#" class="hover:text-white hover:underline transition-all flex items-center gap-2"><i class="fa-solid fa-angle-right text-xs"></i> Tin hoạt động bệnh viện</a></li>
                        <li><a href="#" class="hover:text-white hover:underline transition-all flex items-center gap-2"><i class="fa-solid fa-angle-right text-xs"></i> Cơ hội nghề nghiệp</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-white/20 pt-8 text-sm text-white/60 flex flex-col md:flex-row justify-between items-center gap-4 text-center md:text-left">
                <p>&copy; {{ date('Y') }} Bệnh Viện CareBook. Đã đăng ký bản quyền.</p>
                <div class="flex items-center gap-4">
                    <a href="#" class="hover:text-white transition-colors"><i class="fa-brands fa-facebook text-xl"></i></a>
                    <a href="#" class="hover:text-white transition-colors"><i class="fa-brands fa-youtube text-xl"></i></a>
                </div>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>

</html>
