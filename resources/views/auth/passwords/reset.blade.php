<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu - CareBook</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="antialiased bg-slate-50 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md bg-white rounded-2xl shadow p-8">
        <h1 class="text-2xl font-bold mb-2">Đặt lại mật khẩu</h1>
        <p class="text-sm text-slate-500 mb-6">Vui lòng nhập mật khẩu mới của bạn.</p>

        @if(session('error'))
            <div class="bg-rose-50 text-rose-700 p-3 rounded mb-4">{{ session('error') }}</div>
        @endif

        <form action="{{ route('password.update') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="login_type" value="{{ $login_type ?? 'patient' }}">

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                <input name="email" type="email" value="{{ $email ?? old('email') }}" required
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Email">
                @error('email') <p class="text-rose-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Mật khẩu mới</label>
                <input name="password" type="password" required
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Mật khẩu mới">
                @error('password') <p class="text-rose-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Xác nhận mật khẩu</label>
                <input name="password_confirmation" type="password" required
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Xác nhận mật khẩu">
            </div>

            <div class="flex items-center justify-between">
                <a href="{{ $login_type === 'admin' ? route('login') : route('patient.login') }}" class="text-sm text-slate-600 hover:text-slate-800">Quay lại đăng nhập</a>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg">Đặt lại mật khẩu</button>
            </div>
        </form>
    </div>
</body>
</html>
