<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f9fafb; }
        .container { max-width: 600px; margin: 30px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { background-color: #0ea5e9; color: white; padding: 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 20px; }
        .content { padding: 30px; }
        .content p { margin-top: 0; margin-bottom: 20px; white-space: pre-wrap; }
        .footer { background-color: #f3f4f6; color: #6b7280; text-align: center; padding: 15px; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Carebook Clinic</h1>
        </div>
        <div class="content">
            <h2>{{ $title }}</h2>
            <div style="margin-bottom: 20px;">{!! nl2br(e($content)) !!}</div>

            <p>Trân trọng,<br>Đội ngũ Carebook</p>
        </div>
        <div class="footer">
            Đây là email tự động từ hệ thống Carebook. Vui lòng không trả lời email này.
        </div>
    </div>
</body>
</html>
