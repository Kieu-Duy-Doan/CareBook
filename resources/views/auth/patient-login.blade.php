<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập Bệnh nhân - CareBook</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body {
            font-family: 'Be Vietnam Pro', sans-serif;
        }
    </style>
</head>

<body class="antialiased bg-slate-50 text-slate-800 min-h-screen flex flex-col">
    <!-- Top Header -->
    <header class="bg-blue-600 py-3 px-6 flex items-center justify-between shadow-md relative z-20">
        <a href="{{ route('home') }}" class="flex items-center gap-3 group">
            <div
                class="w-10 h-10 rounded-full bg-white text-blue-600 flex items-center justify-center text-xl shadow-lg group-hover:scale-105 transition-transform duration-300">
                <i class="fa-solid fa-hospital"></i>
            </div>
            <div>
                <h1 class="font-bold text-xl text-white tracking-tight drop-shadow-md">CareBook</h1>
                <p class="text-[9px] text-blue-100 font-semibold uppercase tracking-widest drop-shadow-md">Hospital</p>
            </div>
        </a>
        <div class="flex items-center gap-4 text-white font-medium text-sm hidden md:flex">
            <span class="flex items-center gap-2"><i class="fa-solid fa-phone-volume"></i> 1900.888.866</span>
            <span class="bg-white/20 px-3 py-1 rounded-full border border-white/30 backdrop-blur-sm">VN</span>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 relative flex flex-col items-center justify-center p-4 overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 z-0 opacity-40 pointer-events-none"
            style="background-image: radial-gradient(#3b82f6 1px, transparent 1px); background-size: 30px 30px;"></div>

        <div
            class="w-full max-w-md bg-white rounded-[2rem] shadow-[0_20px_60px_-15px_rgba(0,0,0,0.1)] border border-slate-100 p-8 sm:p-10 relative z-10">

            <!-- Header section of the form -->
            <div class="text-center mb-8">
                <div
                    class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-blue-50 text-blue-600 text-2xl mb-4 shadow-inner">
                    <i class="fa-solid fa-user-injured"></i>
                </div>
                <h2 class="text-2xl font-extrabold text-slate-800">Cổng thông tin Bệnh nhân</h2>
                <p class="text-sm text-slate-500 mt-2 font-medium">Đăng nhập để đặt lịch và tra cứu hồ sơ</p>
            </div>

            <!-- Alerts -->
            @if (session('error'))
                <div
                    class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-xl text-sm text-red-700 flex items-start gap-3">
                    <i class="fa-solid fa-circle-exclamation mt-0.5 text-red-500"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if (session('success'))
                <div
                    class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-r-xl text-sm text-green-700 flex items-start gap-3">
                    <i class="fa-solid fa-circle-check mt-0.5 text-green-500"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-5" x-data="{ loading: false, showPassword: false }"
                @submit="loading = true">
                @csrf
                @if (request()->has('redirect'))
                    <input type="hidden" name="redirect" value="{{ request('redirect') }}">
                @endif
                <input type="hidden" name="login_type" value="patient">

                <div>
                    <label for="phone" class="block text-sm font-bold text-slate-700 mb-1.5">Số điện thoại</label>
                    <div class="relative group">
                        <div
                            class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-blue-600 transition-colors">
                            <i class="fa-solid fa-phone"></i>
                        </div>
                        <input type="text" id="phone" name="phone" value="{{ old('phone') }}" required
                            autofocus
                            class="block w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:bg-white focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 outline-none transition-all placeholder:text-slate-400"
                            placeholder="Nhập số điện thoại của bạn">
                    </div>
                    @error('phone')
                        <p class="text-red-500 text-xs font-semibold mt-1.5 ml-1"><i
                                class="fa-solid fa-triangle-exclamation mr-1"></i> {{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-sm font-bold text-slate-700">Mật khẩu</label>
                        <a href="#"
                            class="text-xs font-bold text-blue-600 hover:text-blue-700 transition-colors">Quên mật khẩu?</a>
                    </div>
                    <div class="relative group">
                        <div
                            class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-blue-600 transition-colors">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <input :type="showPassword ? 'text' : 'password'" id="password" name="password" required
                            class="block w-full pl-11 pr-12 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:bg-white focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 outline-none transition-all placeholder:text-slate-400"
                            placeholder="Nhập mật khẩu">
                        <button type="button" @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-blue-600 transition-colors">
                            <i class="fa-solid" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-red-500 text-xs font-semibold mt-1.5 ml-1"><i
                                class="fa-solid fa-triangle-exclamation mr-1"></i> {{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2">
                    <button type="submit" :disabled="loading"
                        class="w-full flex justify-center items-center py-4 px-4 rounded-xl shadow-lg shadow-blue-600/20 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-600/20 active:scale-[0.98] disabled:opacity-70 disabled:cursor-not-allowed transition-all">
                        <span x-show="!loading" class="tracking-wide uppercase">Đăng nhập ngay</span>
                        <span x-show="loading" class="flex items-center gap-2" style="display: none;">
                            <i class="fa-solid fa-spinner fa-spin text-lg"></i> Đang xử lý...
                        </span>
                    </button>
                </div>
            </form>

            <div class="mt-8 pt-6 border-t border-slate-100 text-center">
                <p class="text-sm font-medium text-slate-500">
                    Bạn chưa có tài khoản?
                    <a href="#" class="text-blue-600 hover:text-blue-700 font-bold ml-1 transition-colors">Đăng ký tại đây</a>
                </p>
            </div>
        </div>

        <p class="mt-8 text-sm text-slate-400 font-medium z-10 text-center">
            &copy; {{ date('Y') }} CareBook Hospital. Bảo mật thông tin y tế.
        </p>
    </main>
</body>

</html>
