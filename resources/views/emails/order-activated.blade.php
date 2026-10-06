<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn hàng {{ $order->code }} đã kích hoạt - IC3 Adventure</title>
</head>
<body style="margin:0; padding:24px; background:#f0fdf4; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color:#1e293b;">
@php
    $row = 'padding:9px 0; border-bottom:1px solid #f1f5f9; font-size:14px;';
    $label = 'color:#64748b; font-weight:700; width:42%;';
    $value = 'color:#0f172a; font-weight:800; text-align:right;';
    $activated = ($order->activated_at ?? now())->copy()->setTimezone($tz);
    $owner = $user->packageSummary();
@endphp
<div style="max-width:540px; margin:0 auto; background:#ffffff; border-radius:24px; overflow:hidden; border:3px solid #e2e8f0; box-shadow:0 16px 36px rgba(0,0,0,0.08);">
    <div style="background:linear-gradient(135deg, #059669 0%, #10b981 60%, #0ea5e9 100%); padding:30px 24px; text-align:center; color:#ffffff;">
        <div style="font-size:40px; line-height:1;">🎉</div>
        <h1 style="margin:10px 0 4px; font-size:22px; font-weight:900;">Kích hoạt gói thành công!</h1>
        <p style="margin:0; font-size:14px; opacity:0.95;">Cảm ơn {{ $user->name }} đã tin tưởng IC3 Adventure</p>
    </div>

    <div style="padding:24px;">
        <p style="margin:0 0 14px; font-size:15px; line-height:1.6;">Xin chào <b>{{ $user->name }}</b>, đơn hàng của bạn đã được thanh toán và kích hoạt. Bạn có thể bắt đầu học ngay!</p>

        <div style="background:#f8fafc; border:2px solid #e2e8f0; border-radius:16px; padding:6px 16px; margin-bottom:16px;">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
                <tr><td style="{{ $row }} {{ $label }}">Mã đơn hàng</td><td style="{{ $row }} {{ $value }} font-family:monospace; font-size:16px; color:#047857;">{{ $order->code }}</td></tr>
                <tr><td style="{{ $row }} {{ $label }}">Gói dịch vụ</td><td style="{{ $row }} {{ $value }}">{{ $order->package_name }}</td></tr>
                <tr><td style="{{ $row }} {{ $label }}">Số tiền</td><td style="{{ $row }} {{ $value }}">{{ number_format((int) $order->price, 0, ',', '.') }} đ</td></tr>
                <tr><td style="{{ $row }} {{ $label }}">Ngày kích hoạt</td><td style="{{ $row }} {{ $value }}">{{ $activated->format('H:i d/m/Y') }}</td></tr>
                @if($owner['expires_at'])
                    <tr><td style="{{ $row }} {{ $label }}">Học tập đến</td><td style="{{ $row }} {{ $value }}">{{ $owner['expires_at']->format('d/m/Y') }}</td></tr>
                @endif
                @if($user->hasAiAssistant())
                    <tr><td style="{{ $row }} {{ $label }}">Trợ lý AI đến</td><td style="{{ $row }} {{ $value }}">{{ $user->ai_assistant_until->copy()->setTimezone($tz)->format('d/m/Y') }}</td></tr>
                @endif
            </table>
        </div>

        <h2 style="margin:0 0 8px; font-size:15px; font-weight:900; color:#0f172a;">🔑 Thông tin đăng nhập</h2>
        <div style="background:#eff6ff; border:2px solid #bfdbfe; border-radius:16px; padding:6px 16px; margin-bottom:16px;">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
                <tr><td style="{{ $row }} {{ $label }}">Email đăng nhập</td><td style="{{ $row }} {{ $value }}">{{ $user->email }}</td></tr>
                @if($user->student_code)
                    <tr><td style="{{ $row }} {{ $label }}">Mã học sinh</td><td style="{{ $row }} {{ $value }}">{{ $user->student_code }}</td></tr>
                @endif
                @if($user->phone)
                    <tr><td style="{{ $row }} {{ $label }}">Số điện thoại</td><td style="{{ $row }} {{ $value }}">{{ $user->maskedPhone() }}</td></tr>
                @endif
                <tr><td style="{{ $row }} {{ $label }}">Mật khẩu</td><td style="{{ $row }} {{ $value }} font-weight:700; color:#475569;">Mật khẩu bạn đã đặt khi đăng ký</td></tr>
            </table>
        </div>

        <div style="background:#fffbeb; border:2px solid #fde68a; border-radius:16px; padding:14px 16px; font-size:13.5px; line-height:1.6; color:#78350f;">
            <b>💡 Hãy lưu lại email này.</b> Nếu quên mật khẩu hoặc quên tài khoản, bạn mở khung <b>Trợ lý AI</b> ở trang đăng nhập và gõ <b>"quên mật khẩu"</b>.
            Khi không nhớ email, chỉ cần cung cấp <b>họ tên</b> cùng <b>mã đơn hàng {{ $order->code }}</b> hoặc <b>số điện thoại</b> đã đăng ký để xác minh chính chủ.
            IC3 Adventure không bao giờ hỏi mật khẩu của bạn.
        </div>

        <div style="text-align:center; margin-top:20px;">
            <a href="{{ route('login') }}" style="display:inline-block; background:linear-gradient(135deg, #2563eb, #0ea5e9); color:#ffffff; text-decoration:none; font-weight:900; font-size:15px; padding:13px 28px; border-radius:14px;">🚀 Vào học ngay</a>
        </div>
    </div>

    <div style="background:#f8fafc; padding:14px 24px; text-align:center; font-size:12px; color:#94a3b8;">
        Email tự động từ IC3 Adventure. Cần hỗ trợ, bạn nhắn Ban Quản Trị trong khung chat trên website.
    </div>
</div>
</body>
</html>
