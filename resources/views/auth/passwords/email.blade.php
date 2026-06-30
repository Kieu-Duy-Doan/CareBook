<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu - CareBook</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Be Vietnam Pro', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="antialiased bg-slate-50 text-slate-800 min-h-screen flex flex-col">
    <header class="bg-blue-600 py-3 px-6 flex items-center justify-between shadow-md relative z-20">
        <a href="{{ route('home') }}" class="flex items-center gap-3 group">
            <div class="w-10 h-10 rounded-full bg-white text-blue-600 flex items-center justify-center text-xl shadow-lg group-hover:scale-105 transition-transform duration-300">
                <i class="fa-solid fa-hospital"></i>
            </div>
            <div>
                <h1 class="font-bold text-xl text-white tracking-tight drop-shadow-md">CareBook</h1>
                <p class="text-[9px] text-blue-100 font-semibold uppercase tracking-widest drop-shadow-md">Hospital</p>
            </div>
        </a>
    </header>

    <main class="flex-1 relative flex flex-col items-center justify-center p-4 overflow-hidden">
        <div class="absolute inset-0 z-0 opacity-40 pointer-events-none" style="background-image: radial-gradient(#3b82f6 1px, transparent 1px); background-size: 30px 30px;"></div>

        <div class="w-full max-w-md bg-white rounded-[2rem] shadow-[0_20px_60px_-15px_rgba(0,0,0,0.1)] border border-slate-100 p-8 sm:p-10 relative z-10">
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 text-2xl mb-3 shadow-inner">
                    <i class="fa-solid fa-key"></i>
                </div>
                <h2 class="text-2xl font-extrabold text-slate-800">Quên mật khẩu</h2>
                <p class="text-sm text-slate-500 mt-2 font-medium">Nhập số điện thoại hoặc email đã đăng ký</p>
            </div>

            @if(session('error'))
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4 rounded-r-xl text-sm text-red-700 flex items-start gap-3">
                    <i class="fa-solid fa-circle-exclamation mt-0.5 text-red-500"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-4 rounded-r-xl text-sm text-green-700 flex items-start gap-3">
                    <i class="fa-solid fa-circle-check mt-0.5 text-green-500"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4 rounded-r-xl text-sm text-red-700">
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('password.email') }}" method="POST" class="space-y-4"
                x-data="{ loading: false }" @submit="loading = true">
                @csrf
                <input type="hidden" name="login_type" value="{{ $login_type ?? 'patient' }}">

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Số điện thoại hoặc Email</label>
                    <input name="identifier" type="text" value="{{ old('identifier') }}" required
                        placeholder="VD: 0901234567 hoặc email@gmail.com"
                        class="block w-full pl-4 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:bg-white focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 outline-none">
                    <p class="text-xs text-slate-500 mt-1">Chúng tôi sẽ gửi liên kết đặt lại mật khẩu tới email đã đăng ký</p>
                    @error('identifier') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <button type="submit" :disabled="loading"
                    class="w-full flex justify-center items-center py-4 px-4 rounded-xl shadow-lg text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-600/20 disabled:opacity-70 transition-all">
                    <span x-show="!loading">Gửi liên kết đặt lại</span>
                    <span x-show="loading" x-cloak class="flex items-center gap-2">
                        <i class="fa-solid fa-spinner fa-spin"></i> Đang gửi...
                    </span>
                </button>

                <div class="text-center text-sm text-slate-500">
                    <a href="{{ ($login_type ?? 'patient') === 'admin' ? route('login') : route('patient.login') }}"
                        class="text-blue-600 font-bold hover:text-blue-700">
                        <i class="fa-solid fa-arrow-left mr-1"></i> Quay lại đăng nhập
                    </a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
