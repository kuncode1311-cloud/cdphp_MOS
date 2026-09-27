<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cảnh Báo Đổi Mật Khẩu Thành Công - IC3 Adventure</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f8fafc;
            margin: 0;
            padding: 24px;
            color: #1e293b;
        }
        .email-container {
            max-width: 560px;
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
        .info-card {
            background: linear-gradient(180deg, #f0fdf4 0%, #dcfce7 100%);
            border: 2px solid #86efac;
            border-radius: 18px;
            padding: 20px 24px;
            margin-bottom: 24px;
        }
        .info-title {
            font-size: 13px;
            font-weight: 800;
            color: #15803d;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 7px 0;
            border-bottom: 1px dashed #bbf7d0;
            font-size: 14px;
        }
        .info-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
        .info-label {
            color: #4b5563;
            font-weight: 600;
        }
        .info-value {
            color: #0f172a;
            font-weight: 700;
            text-align: right;
        }
        .alert-box {
            background: linear-gradient(180deg, #fef2f2 0%, #fee2e2 100%);
            border-left: 5px solid #ef4444;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 24px;
        }
        .alert-title {
            font-size: 15px;
            font-weight: 800;
            color: #b91c1c;
            margin-bottom: 8px;
        }
        .alert-text {
            font-size: 13.5px;
            color: #7f1d1d;
            line-height: 1.6;
            margin: 0;
        }
        .contact-btn-group {
            margin-top: 14px;
            padding-top: 12px;
            border-top: 1px dashed #fca5a5;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .contact-pill {
            display: inline-block;
            background: #ffffff;
            color: #b91c1c;
            border: 1.5px solid #ef4444;
            border-radius: 999px;
            padding: 6px 14px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
        }
        .safe-note {
            background: #f8fafc;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 13px;
            color: #64748b;
            line-height: 1.5;
            margin-bottom: 8px;
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
        <!-- Header -->
        <div class="email-header">
            <div style="font-size: 40px;">🛡️</div>
            <h1>IC3 DIGITAL ADVENTURE</h1>
            <p>Học vui · Chơi giỏi · Lớn khôn</p>
        </div>

        <!-- Body -->
        <div class="email-body">
            <div class="greeting">
                Xin chào {{ $user->name }}! 👋
            </div>

            <p class="message-text">
                Hệ thống <b>IC3 Adventure</b> xin thông báo: Mật khẩu đăng nhập cho tài khoản của bạn (<b>{{ $user->email }}</b>) vừa được <b>thay đổi thành công</b>.
            </p>

            <!-- Card Chi Tiết Thao Tác -->
            <div class="info-card">
                <div class="info-title">
                    ✅ CHI TIẾT THAY ĐỔI MẬT KHẨU
                </div>
                <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                    <tr style="border-bottom: 1px dashed #bbf7d0;">
                        <td style="padding: 6px 0; color: #4b5563; font-weight: 600;">🕒 Thời gian:</td>
                        <td style="padding: 6px 0; color: #0f172a; font-weight: 700; text-align: right;">{{ $changedAt }}</td>
                    </tr>
                    <tr style="border-bottom: 1px dashed #bbf7d0;">
                        <td style="padding: 6px 0; color: #4b5563; font-weight: 600;">🌐 Địa chỉ IP:</td>
                        <td style="padding: 6px 0; color: #0f172a; font-weight: 700; text-align: right;">{{ $ipAddress }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #4b5563; font-weight: 600;">🔒 Trạng thái:</td>
                        <td style="padding: 6px 0; color: #16a34a; font-weight: 800; text-align: right;">Đã cập nhật an toàn</td>
                    </tr>
                </table>
            </div>

            <!-- Cảnh Báo An Ninh Khẩn Cấp -->
            <div class="alert-box">
                <div class="alert-title">
                    🚨 BẠN KHÔNG PHẢI NGƯỜI THỰC HIỆN THAO TÁC NÀY?
                </div>
                <p class="alert-text">
                    Nếu bạn <b>KHÔNG</b> yêu cầu đổi mật khẩu, rất có thể tài khoản của bạn đang có người khác xâm nhập trái phép. 
                    Vui lòng <b>LIÊN HỆ NGAY VỚI BAN QUẢN TRỊ HOẶC THẦY CÔ GIÁO</b> để được hỗ trợ khóa tài khoản và khôi phục an toàn kịp thời!
                </p>
                <div class="contact-btn-group">
                    <a href="tel:0345151438" class="contact-pill">📞 Hotline / Zalo: 0345.151.438</a>
                    <a href="mailto:{{ config('mail.from.address', 'kun.code.1311@gmail.com') }}" class="contact-pill">✉️ Email Admin</a>
                </div>
            </div>

            <!-- Lời nhắc nếu chính chủ đổi -->
            <div class="safe-note">
                💡 <i>Nếu chính bạn vừa thực hiện đổi mật khẩu này thì tài khoản đã được bảo vệ tối ưu. Bạn có thể an tâm bỏ qua email này.</i>
            </div>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            © {{ date('Y') }} IC3 Digital Adventure. Hệ thống ôn luyện & thi thử chuẩn quốc tế.
        </div>
    </div>
</body>
</html>
