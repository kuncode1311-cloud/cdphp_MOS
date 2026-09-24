<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mã OTP Đổi Mật Khẩu - IC3 Adventure</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f0fdf4;
            margin: 0;
            padding: 24px;
            color: #1e293b;
        }
        .email-container {
            max-width: 540px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 16px 36px rgba(0,0,0,0.08);
            border: 3px solid #e2e8f0;
        }
        .email-header {
            background: linear-gradient(135deg, #1072ba 0%, #0284c7 60%, #0369a1 100%);
            padding: 32px 24px;
            text-align: center;
            color: #ffffff;
        }
        .email-header h1 {
            margin: 10px 0 0;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: 0.5px;
        }
        .email-header p {
            margin: 6px 0 0;
            font-size: 14px;
            color: #bae6fd;
            font-weight: 600;
        }
        .email-body {
            padding: 32px 28px;
        }
        .greeting {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 12px;
        }
        .message-text {
            font-size: 15px;
            line-height: 1.6;
            color: #475569;
            margin-bottom: 24px;
        }
        .otp-card {
            background: linear-gradient(180deg, #f0f9ff 0%, #e0f2fe 100%);
            border: 2px dashed #0284c7;
            border-radius: 18px;
            padding: 24px;
            text-align: center;
            margin-bottom: 24px;
        }
        .otp-label {
            font-size: 12px;
            font-weight: 800;
            color: #0369a1;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 8px;
        }
        .otp-code {
            font-family: 'Courier New', Courier, monospace;
            font-size: 40px;
            font-weight: 900;
            letter-spacing: 10px;
            color: #0284c7;
            text-shadow: 0 2px 4px rgba(2, 132, 199, 0.15);
            display: inline-block;
            padding-left: 10px;
        }
        .otp-expiry {
            font-size: 13px;
            font-weight: 700;
            color: #ef4444;
            margin-top: 10px;
        }
        .notice-box {
            background: #fffbeb;
            border-left: 4px solid #f59e0b;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 13px;
            color: #92400e;
            line-height: 1.5;
            margin-bottom: 24px;
        }
        .email-footer {
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <div style="font-size: 40px;">⭐</div>
            <h1>IC3 DIGITAL ADVENTURE</h1>
            <p>Học vui · Chơi giỏi · Lớn khôn</p>
        </div>

        <div class="email-body">
            <div class="greeting">
                Xin chào {{ $user->name }}! 👋
            </div>

            <p class="message-text">
                Hệ thống nhận được yêu cầu <b>đổi mật khẩu</b> cho tài khoản của bạn trên nền tảng <b>IC3 Adventure</b>. Dưới đây là mã xác thực OTP bảo mật:
            </p>

            <div class="otp-card">
                <div class="otp-label">MÃ XÁC THỰC BẢO MẬT (OTP)</div>
                <div class="otp-code">{{ $otp }}</div>
                <div class="otp-expiry">⏳ Hiệu lực trong 10 phút</div>
            </div>

            <div class="notice-box">
                🛡️ <b>Lưu ý bảo mật quan trọng:</b><br>
                - Tuyệt đối không chia sẻ mã này cho bất kỳ ai, kể cả khi được yêu cầu.<br>
                - Nếu bạn không yêu cầu đổi mật khẩu, vui lòng liên hệ ngay với giáo viên hoặc ban quản trị để kiểm tra tài khoản.
            </div>

            <p style="font-size: 14px; color: #64748b; margin: 0;">
                Chúc bạn có những giờ học tập và phiêu lưu vui vẻ cùng IC3 Adventure! 🚀
            </p>
        </div>

        <div class="email-footer">
            © {{ date('Y') }} IC3 Digital Adventure. Hệ thống ôn luyện & thi thử chuẩn quốc tế.
        </div>
    </div>
</body>
</html>
