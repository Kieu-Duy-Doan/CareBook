<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký Bệnh nhân - CareBook</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>body { font-family: 'Be Vietnam Pro', sans-serif; }</style>
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
        <div class="flex items-center gap-4 text-white font-medium text-sm hidden md:flex">
            <span class="flex items-center gap-2"><i class="fa-solid fa-phone-volume"></i> 1900.888.866</span>
            <span class="bg-white/20 px-3 py-1 rounded-full border border-white/30 backdrop-blur-sm">VN</span>
        </div>
    </header>

    <main class="flex-1 relative flex flex-col items-center justify-center p-4 overflow-hidden">
        <div class="absolute inset-0 z-0 opacity-40 pointer-events-none" style="background-image: radial-gradient(#3b82f6 1px, transparent 1px); background-size: 30px 30px;"></div>

        <div class="w-full max-w-2xl lg:max-w-3xl bg-white rounded-[2rem] shadow-[0_20px_60px_-15px_rgba(0,0,0,0.1)] border border-slate-100 p-8 sm:p-10 relative z-10">

            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 text-2xl mb-3 shadow-inner">
                    <i class="fa-solid fa-user-injured"></i>
                </div>
                <h2 class="text-2xl font-extrabold text-slate-800">Đăng ký bệnh nhân</h2>
                <p class="text-sm text-slate-500 mt-2 font-medium">Tạo tài khoản để đặt lịch và quản lý hồ sơ</p>
            </div>

            @if(session('error'))
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4 rounded-r-xl text-sm text-red-700 flex items-start gap-3">
                    <i class="fa-solid fa-circle-exclamation mt-0.5 text-red-500"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('register.post') }}" class="space-y-4" x-data="{loading:false, showPassword:false}">
                @csrf

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Họ và tên</label>
                    <input type="text" name="full_name" value="{{ old('full_name') }}" required
                        class="block w-full pl-4 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:bg-white focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 outline-none">
                    @error('full_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Số điện thoại</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" required
                        class="block w-full pl-4 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:bg-white focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 outline-none">
                    @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Mật khẩu</label>
                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'" name="password" required
                                class="block w-full pl-4 pr-12 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:bg-white focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 outline-none">
                            <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400">
                                <i class="fa-solid" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                        @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Xác nhận mật khẩu</label>
                        <input type="password" name="password_confirmation" required
                            class="block w-full pl-4 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:bg-white focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Ngày sinh</label>
                        <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" required
                            class="block w-full pl-4 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium">
                        @error('date_of_birth') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Giới tính</label>
                        <select name="gender" required class="block w-full pl-4 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium">
                            <option value="">--Chọn--</option>
                            <option value="male">Nam</option>
                            <option value="female">Nữ</option>
                            <option value="other">Khác</option>
                        </select>
                        @error('gender') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">CCCD/CMND</label>
                        <input type="text" name="id_card" value="{{ old('id_card') }}" class="block w-full pl-4 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium">
                        @error('id_card') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Địa chỉ</label>
                        <input type="text" name="address" value="{{ old('address') }}" class="block w-full pl-4 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Nghề nghiệp</label>
                        <input type="text" name="occupation" value="{{ old('occupation') }}" class="block w-full pl-4 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Dân tộc</label>
                        <input type="text" name="ethnicity" value="{{ old('ethnicity') }}" class="block w-full pl-4 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Mã BHYT</label>
                        <input type="text" name="insurance_code" value="{{ old('insurance_code') }}" class="block w-full pl-4 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Hạn thẻ BHYT</label>
                        <input type="date" name="insurance_expiry" value="{{ old('insurance_expiry') }}" class="block w-full pl-4 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" :disabled="loading" @click="loading = true"
                        class="w-full flex justify-center items-center py-4 px-4 rounded-xl shadow-lg text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-600/20 active:scale-[0.98] disabled:opacity-70 disabled:cursor-not-allowed transition-all">
                        <span x-show="!loading" class="tracking-wide uppercase">Đăng ký</span>
                        <span x-show="loading" class="flex items-center gap-2" style="display:none;"><i class="fa-solid fa-spinner fa-spin mr-2"></i> Đang xử lý...</span>
                    </button>
                </div>

                <div class="text-center text-sm text-slate-500">
                    Bạn đã có tài khoản? <a href="{{ route('patient.login') }}" class="text-blue-600 font-bold">Đăng nhập</a>
                </div>
            </form>
        </div>
        
        <p class="mt-8 text-sm text-slate-400 font-medium z-10 text-center">&copy; {{ date('Y') }} CareBook Hospital. Bảo mật thông tin y tế.</p>
    </main>

</body>
</html>
