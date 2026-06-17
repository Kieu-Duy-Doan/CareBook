<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - CareBook</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900">
    <div class="min-h-screen flex">
        <!-- Left Column: Branding -->
        <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-blue-600 to-teal-500 text-white p-12 flex-col justify-between">
            <div>
                <div class="flex items-center gap-3 text-3xl font-bold mb-6">
                    <i class="fa-solid fa-hospital-user text-4xl"></i>
                    CareBook
                </div>
                <h1 class="text-4xl font-extrabold leading-tight mb-4">
                    Hệ thống đặt lịch<br>khám bệnh thông minh
                </h1>
                <p class="text-blue-100 text-lg">Giải pháp toàn diện quản lý phòng khám và bệnh viện.</p>
            </div>

            <div class="space-y-6">
                <div class="flex items-start gap-4">
                    <div class="bg-white/20 p-3 rounded-lg"><i class="fa-solid fa-calendar-check text-2xl"></i></div>
                    <div>
                        <h3 class="font-semibold text-lg">Đặt lịch nhanh chóng</h3>
                        <p class="text-blue-100 text-sm">Chủ động thời gian, giảm thiểu chờ đợi.</p>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <div class="bg-white/20 p-3 rounded-lg"><i class="fa-solid fa-file-medical text-2xl"></i></div>
                    <div>
                        <h3 class="font-semibold text-lg">Hồ sơ điện tử</h3>
                        <p class="text-blue-100 text-sm">Lưu trữ an toàn, tra cứu dễ dàng.</p>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <div class="bg-white/20 p-3 rounded-lg"><i class="fa-solid fa-user-doctor text-2xl"></i></div>
                    <div>
                        <h3 class="font-semibold text-lg">Đội ngũ chuyên gia</h3>
                        <p class="text-blue-100 text-sm">Kết nối với các bác sĩ hàng đầu.</p>
                    </div>
                </div>
            </div>
            
            <div class="text-sm text-blue-200">
                &copy; {{ date('Y') }} CareBook. All rights reserved.
            </div>
        </div>

        <!-- Right Column: Login Form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8 sm:p-12">
            <div class="w-full max-w-md">
                <!-- Mobile Logo -->
                <div class="lg:hidden flex items-center justify-center gap-2 text-2xl font-bold text-blue-600 mb-8">
                    <i class="fa-solid fa-hospital-user text-3xl"></i>
                    CareBook
                </div>

                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900">Đăng nhập</h2>
                    <p class="text-gray-500 mt-2">Vui lòng đăng nhập để tiếp tục</p>
                </div>

                @if (session('error'))
                    <div class="bg-red-50 text-red-600 p-4 rounded-lg mb-6 flex items-start gap-3">
                        <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @if (session('success'))
                    <div class="bg-green-50 text-green-600 p-4 rounded-lg mb-6 flex items-start gap-3">
                        <i class="fa-solid fa-circle-check mt-0.5"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                <form action="{{ route('login.post') }}" method="POST" class="space-y-6" x-data="{ loading: false }" @submit="loading = true">
                    @csrf
                    @if(request()->has('redirect'))
                        <input type="hidden" name="redirect" value="{{ request('redirect') }}">
                    @endif
                    
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <i class="fa-solid fa-phone"></i>
                            </div>
                            <input type="text" id="phone" name="phone" value="{{ old('phone') }}" required autofocus
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors"
                                placeholder="Nhập số điện thoại">
                        </div>
                        @error('phone')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div x-data="{ showPassword: false }">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <i class="fa-solid fa-lock"></i>
                            </div>
                            <input :type="showPassword ? 'text' : 'password'" id="password" name="password" required
                                class="block w-full pl-10 pr-10 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors"
                                placeholder="Nhập mật khẩu">
                            <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                <i class="fa-solid" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember" name="remember" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded cursor-pointer">
                            <label for="remember" class="ml-2 block text-sm text-gray-700 cursor-pointer">
                                Ghi nhớ đăng nhập
                            </label>
                        </div>
                        <div class="text-sm">
                            <a href="#" class="font-medium text-blue-600 hover:text-blue-500">Quên mật khẩu?</a>
                        </div>
                    </div>

                    <button type="submit" :disabled="loading"
                        class="w-full flex justify-center items-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-70 disabled:cursor-not-allowed transition-all">
                        <span x-show="!loading">Đăng nhập</span>
                        <span x-show="loading" class="flex items-center gap-2" style="display: none;">
                            <i class="fa-solid fa-spinner fa-spin"></i> Đang xử lý...
                        </span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
