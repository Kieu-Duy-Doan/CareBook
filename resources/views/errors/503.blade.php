<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống đang bảo trì</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4">
    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl p-10 text-center border border-gray-100">
        <div class="w-24 h-24 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fa-solid fa-person-digging text-4xl"></i>
        </div>

        <h1 class="text-3xl font-bold text-gray-900 mb-3">Đang Bảo Trì</h1>

        <p class="text-gray-600 mb-8 text-lg leading-relaxed">
            {{ $exception->getMessage() ?: 'Hệ thống đang được bảo trì nâng cấp. Vui lòng quay lại sau ít phút.' }}
        </p>

        <div class="pt-6 border-t border-gray-100">
            <p class="text-sm font-medium text-gray-400">
                CareBook Hospital &copy; {{ date('Y') }}
            </p>
        </div>
    </div>
</body>

</html>