<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CareBook Clinic</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
            color: #333333;
            -webkit-font-smoothing: antialiased;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        .header {
            background-color: #0d9488;
            color: #ffffff;
            padding: 24px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 32px 24px;
            font-size: 16px;
            line-height: 1.6;
        }
        .content p {
            margin-bottom: 16px;
        }
        .btn-container {
            text-align: center;
            margin-top: 32px;
            margin-bottom: 16px;
        }
        .btn {
            display: inline-block;
            background-color: #0d9488;
            color: #ffffff;
            text-decoration: none;
            padding: 14px 32px;
            font-size: 18px;
            font-weight: bold;
            border-radius: 8px;
            min-height: 48px;
            line-height: 20px;
        }
        .footer {
            background-color: #f8fafc;
            padding: 24px;
            text-align: center;
            font-size: 14px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
        }
        .footer p {
            margin: 8px 0;
        }
        .highlight {
            font-weight: bold;
            color: #0d9488;
        }
        .box {
            background-color: #f1f5f9;
            padding: 16px;
            border-radius: 6px;
            margin: 24px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Phòng Khám CareBook</h1>
        </div>
        
        <div class="content">
            @yield('content')
        </div>
        
        <div class="footer">
            <p>Đây là email tự động, vui lòng không trả lời.</p>
            <p>Hotline hỗ trợ: <span style="font-weight: bold; font-size: 16px; color: #333;">1900 1234</span></p>
            <p>&copy; {{ date('Y') }} CareBook. Xin trân trọng cảm ơn.</p>
        </div>
    </div>
</body>
</html>
