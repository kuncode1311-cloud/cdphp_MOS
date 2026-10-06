<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quên mật khẩu - IC3 Quest</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=be-vietnam-pro:400,600,700,800,900&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Be Vietnam Pro', system-ui, sans-serif;
            color: #ffffff;
            background: #05020a url('{{ asset('images/ic3-login-hero.jpg') }}') center / cover no-repeat fixed;
            display: grid;
            place-items: center;
            padding: 14px;
            overflow-x: hidden;
        }
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: radial-gradient(circle at 20% 25%, rgba(14, 165, 233, 0.34), transparent 28%),
                radial-gradient(circle at 78% 18%, rgba(236, 72, 153, 0.34), transparent 30%),
                linear-gradient(180deg, rgba(5, 2, 10, 0.44), rgba(5, 2, 10, 0.78));
            pointer-events: none;
        }
        .auth-shell {
            position: relative;
            width: min(500px, 100%);
            border: 3.5px solid rgba(255, 255, 255, 0.45);
            border-radius: 24px;
            background: rgba(16, 10, 28, 0.76);
            box-shadow: 0 22px 55px rgba(0, 0, 0, 0.35), inset 0 -7px 0 rgba(0, 0, 0, 0.18);
            backdrop-filter: blur(18px);
            padding: clamp(18px, 3.2vw, 28px);
        }
        .badge-pill {
            width: fit-content;
            margin: 0 auto 9px;
            padding: 7px 15px;
            border-radius: 999px;
            border: 1.5px solid rgba(244, 114, 182, 0.55);
            background: rgba(236, 72, 153, 0.16);
            font-weight: 900;
            font-size: 12px;
        }
        h1 {
            margin: 0;
            text-align: center;
            font-size: clamp(28px, 6vw, 38px);
            font-weight: 1000;
            letter-spacing: 0;
            line-height: 1.08;
        }
        h1 span { color: #f472b6; }
        .lead {
            margin: 8px auto 16px;
            max-width: 400px;
            text-align: center;
            color: rgba(255, 255, 255, 0.78);
            font-weight: 700;
            font-size: 13.5px;
            line-height: 1.42;
        }
        .notice, .error {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            padding: 10px 12px;
            border-radius: 14px;
            margin-bottom: 12px;
            font-size: 12.5px;
            font-weight: 800;
            line-height: 1.38;
        }
        .notice { background: rgba(16, 185, 129, 0.18); border: 1.5px solid rgba(52, 211, 153, 0.55); color: #bbf7d0; }
        .error { background: rgba(239, 68, 68, 0.18); border: 1.5px solid rgba(248, 113, 113, 0.55); color: #fecaca; }
        .form-group { margin-bottom: 11px; }
        label {
            display: block;
            margin-bottom: 6px;
            color: rgba(255, 255, 255, 0.88);
            font-size: 12.5px;
            font-weight: 900;
        }
        input {
            width: 100%;
            border: 2px solid rgba(255, 255, 255, 0.62);
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.9);
            color: #172033;
            padding: 12px 14px;
            font: inherit;
            font-size: 14px;
            font-weight: 750;
            outline: none;
            box-shadow: inset 0 -3px 0 rgba(15, 23, 42, 0.12);
        }
        input:focus {
            border-color: #f472b6;
            box-shadow: 0 0 0 4px rgba(244, 114, 182, 0.2), inset 0 -3px 0 rgba(15, 23, 42, 0.12);
        }
        .btn-main, .btn-back {
            width: 100%;
            min-height: 47px;
            border-radius: 15px;
            font: inherit;
            font-size: 14px;
            font-weight: 1000;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            text-decoration: none;
            transition: transform 0.16s ease, box-shadow 0.16s ease;
        }
        .btn-main {
            border: 3px solid rgba(255, 255, 255, 0.88);
            color: #ffffff;
            background: linear-gradient(135deg, #ec4899, #8b5cf6 54%, #0ea5e9);
            box-shadow: 0 8px 0 rgba(0, 0, 0, 0.24), 0 18px 30px rgba(139, 92, 246, 0.28);
        }
        .btn-back {
            margin-top: 10px;
            border: 2px solid rgba(255, 255, 255, 0.28);
            color: rgba(255, 255, 255, 0.88);
            background: rgba(255, 255, 255, 0.1);
        }
        .btn-main:hover, .btn-back:hover { transform: translateY(-2px); }
        .btn-main:active, .btn-back:active { transform: translateY(3px); }
        .btn-soft {
            width: 100%;
            min-height: 40px;
            margin-top: 9px;
            border: 2px solid rgba(255, 255, 255, 0.24);
            border-radius: 14px;
            color: rgba(255, 255, 255, 0.88);
            background: rgba(255, 255, 255, 0.08);
            font: inherit;
            font-size: 13px;
            font-weight: 900;
            cursor: pointer;
            transition: transform 0.16s ease, background 0.16s ease;
        }
        .btn-soft:hover {
            transform: translateY(-2px);
            background: rgba(255, 255, 255, 0.14);
        }
        .otp-panel {
            margin-top: 12px;
            padding-top: 14px;
            border-top: 1px solid rgba(255, 255, 255, 0.16);
        }
        .hint {
            margin: 0 0 11px;
            color: rgba(255, 255, 255, 0.72);
            font-size: 12.5px;
            font-weight: 700;
            line-height: 1.42;
        }

        @media (max-height: 720px) {
            body { align-items: start; }
            .auth-shell { margin-top: 10px; }
            .badge-pill { display: none; }
            .lead { margin-bottom: 12px; }
        }
    </style>
</head>
<body>
    <main class="auth-shell">
        <div class="badge-pill">🔐 Khôi phục an toàn bằng OTP</div>
        <h1>QUÊN <span>MẬT KHẨU</span></h1>
        <p class="lead">Nhập tài khoản, Mã HS hoặc email. Hệ thống sẽ gửi mã OTP 6 số tới email thật đã đăng ký.</p>

        @if (session('ok'))
            <div class="notice"><span>✅</span><span>{{ session('ok') }}</span></div>
        @endif

        @if ($errors->any())
            <div class="error"><span>⚠️</span><span>{{ $errors->first() }}</span></div>
        @endif

        @if ($hasVerifiedSession)
            <section class="otp-panel">
                <p class="hint">Bước 4/4: OTP đã đúng cho email {{ $verifiedMaskedEmail }}. Bạn đặt mật khẩu mới để hoàn tất nhé.</p>
                <form method="post" action="{{ route('password.forgot.reset') }}">
                    @csrf
                    <div class="form-group">
                        <label for="passwordInput">Mật khẩu mới</label>
                        <input id="passwordInput" name="password" type="password" minlength="6" placeholder="Tối thiểu 6 ký tự" autocomplete="new-password" required autofocus>
                    </div>
                    <div class="form-group">
                        <label for="passwordConfirmInput">Nhập lại mật khẩu mới</label>
                        <input id="passwordConfirmInput" name="password_confirmation" type="password" minlength="6" placeholder="Nhập lại cho chắc chắn" autocomplete="new-password" required>
                    </div>
                    <button type="submit" class="btn-main">
                        <span>ĐẶT LẠI MẬT KHẨU</span>
                        <span>🚀</span>
                    </button>
                </form>
                <form method="post" action="{{ route('password.forgot.restart') }}">
                    @csrf
                    <button type="submit" class="btn-soft">Nhập lại tài khoản khác</button>
                </form>
            </section>
        @elseif ($hasOtpSession)
            <section class="otp-panel">
                <p class="hint">Bước 3/4: OTP đã gửi tới email {{ $maskedEmail }}. Nhập đúng mã trước, rồi hệ thống mới mở bước đặt mật khẩu mới.</p>
                <form method="post" action="{{ route('password.forgot.verify-otp') }}">
                    @csrf
                    <div class="form-group">
                        <label for="otpInput">Mã OTP 6 số</label>
                        <input id="otpInput" name="otp" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" placeholder="Nhập 6 chữ số" autocomplete="one-time-code" required autofocus>
                    </div>
                    <button type="submit" class="btn-main">
                        <span>XÁC MINH OTP</span>
                        <span>✅</span>
                    </button>
                </form>
                <form method="post" action="{{ route('password.forgot.restart') }}">
                    @csrf
                    <button type="submit" class="btn-soft">Nhập lại tài khoản khác</button>
                </form>
            </section>
        @elseif ($hasConfirmSession)
            <section class="otp-panel">
                <p class="hint">
                    Bước 2/4: Tài khoản này có email <b>{{ $confirmMaskedEmail }}</b>
                    @if ($confirmMaskedPhone)
                        và SĐT <b>{{ $confirmMaskedPhone }}</b>
                    @endif
                    . Bạn nhập đúng một trong hai thông tin này để nhận OTP.
                </p>
                <form method="post" action="{{ route('password.forgot.confirm') }}">
                    @csrf
                    <div class="form-group">
                        <label for="contactInput">Email hoặc SĐT đã đăng ký</label>
                        <input id="contactInput" name="contact" value="{{ old('contact') }}" placeholder="Nhập đầy đủ email hoặc SĐT" autocomplete="email" required autofocus>
                    </div>
                    <button type="submit" class="btn-main">
                        <span>XÁC MINH & GỬI OTP</span>
                        <span>✉️</span>
                    </button>
                </form>
                <form method="post" action="{{ route('password.forgot.restart') }}">
                    @csrf
                    <button type="submit" class="btn-soft">Nhập lại tài khoản khác</button>
                </form>
            </section>
        @else
            <form method="post" action="{{ route('password.forgot.send-otp') }}">
                @csrf
                <p class="hint">Bước 1/4: Nếu nhập email đăng ký, hệ thống gửi OTP ngay. Nếu nhập Mã HS hoặc tên đăng nhập, bạn sẽ cần xác minh email/SĐT trước.</p>
                <div class="form-group">
                    <label for="loginInput">Tài khoản / Mã HS / Email</label>
                    <input id="loginInput" name="login" value="{{ old('login') }}" placeholder="Ví dụ: HS001 hoặc email của bạn" autocomplete="username" required autofocus>
                </div>
                <button type="submit" class="btn-main">
                    <span>TIẾP TỤC</span>
                    <span>➡️</span>
                </button>
            </form>
        @endif

        <a href="{{ route('login') }}" class="btn-back">← Quay lại đăng nhập</a>
    </main>
</body>
</html>
