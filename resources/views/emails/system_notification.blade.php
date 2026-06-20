<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        body { margin: 0; padding: 0; background-color: #f3f4f6; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; }
        table { border-spacing: 0; border-collapse: collapse; }
        td { padding: 0; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f3f4f6; padding: 40px 0; }
        .main-container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f3f4f6;">
    <center class="wrapper" style="width: 100%; background-color: #f3f4f6; padding: 40px 0;">
        <div class="main-container" style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
            
            <!-- Header -->
            <table width="100%" style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); background-color: #2563eb;">
                <tr>
                    <td style="padding: 30px 20px; text-align: center;">
                        <span style="color: #ffffff; font-size: 28px; font-weight: 800; letter-spacing: 0.5px; font-family: sans-serif;">
                            CareBook<span style="color: #93c5fd;">Clinic</span>
                        </span>
                    </td>
                </tr>
            </table>
            
            <!-- Body -->
            <table width="100%" style="background-color: #ffffff;">
                <tr>
                    <td style="padding: 40px 30px;">
                        <h2 style="margin: 0 0 24px 0; font-size: 22px; font-weight: 700; color: #111827; border-bottom: 2px solid #eff6ff; padding-bottom: 16px; font-family: sans-serif;">
                            {{ $title }}
                        </h2>
                        
                        <div style="font-size: 16px; color: #4b5563; margin-bottom: 30px; white-space: pre-wrap; line-height: 1.8; font-family: sans-serif;">{!! nl2br(e($content)) !!}</div>
                        
                        <div style="margin-top: 40px; border-top: 1px solid #f3f4f6; padding-top: 24px;">
                            <p style="margin: 0; font-size: 15px; color: #4b5563; font-family: sans-serif;">Trân trọng,</p>
                            <p style="margin: 4px 0 0 0; font-weight: 700; color: #1e40af; font-size: 16px; font-family: sans-serif;">Đội ngũ CareBook Clinic</p>
                        </div>
                    </td>
                </tr>
            </table>
            
            <!-- Footer -->
            <table width="100%" style="background-color: #f9fafb; border-top: 1px solid #e5e7eb;">
                <tr>
                    <td style="padding: 24px 30px; text-align: center;">
                        <p style="margin: 0 0 8px 0; font-size: 13px; color: #6b7280; font-family: sans-serif;">
                            Bạn nhận được email này vì đây là thông báo từ hệ thống CareBook.
                        </p>
                        <p style="margin: 0 0 16px 0; font-size: 13px; color: #6b7280; font-family: sans-serif;">
                            Vui lòng không trả lời trực tiếp email này. Nếu cần hỗ trợ, vui lòng truy cập 
                            <a href="{{ config('app.url') }}" style="color: #3b82f6; text-decoration: none; font-weight: 500;">Website của chúng tôi</a>.
                        </p>
                        <p style="margin: 0; font-size: 12px; color: #9ca3af; font-family: sans-serif;">
                            &copy; {{ date('Y') }} CareBook Clinic. All rights reserved.
                        </p>
                    </td>
                </tr>
            </table>
            
        </div>
    </center>
</body>
</html>
