<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu Bác sĩ - CareBook</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900">
    <div class="min-h-screen flex items-center justify-center p-8">
        <div class="w-full max-w-md bg-white rounded-lg shadow-md p-8">
            <div class="flex items-center justify-center gap-2 text-2xl font-bold text-blue-600 mb-6">
                <i class="fa-solid fa-user-doctor text-3xl"></i>
                CareBook
            </div>

            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-900">Khôi phục mật khẩu</h2>
                <p class="text-gray-500 mt-2 text-sm">Vui lòng liên hệ với Quản trị viên hệ thống để được cấp lại mật khẩu Bác sĩ.</p>
            </div>

            <div class="mt-6 text-center">
                <a href="{{ route('doctor.login') }}" class="text-sm font-medium text-blue-600 hover:text-blue-500">
                    <i class="fa-solid fa-arrow-left mr-1"></i> Quay lại trang đăng nhập
                </a>
            </div>
        </div>
    </div>
</body>
</html>
