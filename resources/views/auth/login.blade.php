{{-- Form gửi sang AuthController; lỗi nhập liệu được Laravel trả về trong biến errors. --}}
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng nhập · IC3 Quest</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --accent: #06b6d4;
            --amber: #f59e0b;
            --text-main: #0f172a;
            --text-muted: #64748b;
        }

        html, body {
            height: 100%;
            overflow: hidden;
        }

        body {
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #060913;
            color: var(--text-main);
            padding: 20px;
            position: relative;
        }

        /* ====== CINEMATIC VIDEO BACKGROUND ====== */
        .video-bg-container {
            position: fixed;
            inset: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
            pointer-events: none;
        }

        .video-bg {
            position: absolute;
            top: 50%;
            left: 50%;
            min-width: 100%;
            min-height: 100%;
            width: auto;
            height: auto;
            transform: translate(-50%, -50%);
            object-fit: cover;
            filter: brightness(0.65) saturate(1.15) contrast(1.05);
        }

        .video-overlay {
            position: absolute;
            inset: 0;
            background: 
                radial-gradient(circle at 20% 20%, rgba(79, 70, 229, 0.25) 0%, transparent 60%),
                radial-gradient(circle at 80% 80%, rgba(6, 182, 212, 0.2) 0%, transparent 60%),
                linear-gradient(180deg, rgba(6, 9, 19, 0.45) 0%, rgba(6, 9, 19, 0.75) 100%);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
        }

        /* ====== MAIN CARD (PREMIUM GLASSMORPHISM) ====== */
        .login-card {
            position: relative;
            z-index: 10;
            width: min(940px, 95vw);
            max-height: calc(100vh - 32px);
            display: grid;
            grid-template-columns: 1fr 1.05fr;
            background: rgba(255, 255, 255, 0.92);
            border: 1.8px solid rgba(255, 255, 255, 0.8);
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 
                0 30px 80px -15px rgba(0, 0, 0, 0.6),
                0 0 0 1px rgba(255, 255, 255, 0.5) inset;
            backdrop-filter: blur(28px);
            -webkit-backdrop-filter: blur(28px);
            animation: cardAppear 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes cardAppear {
            from {
                opacity: 0;
                transform: translateY(20px) scale(0.98);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* ====== LEFT COLUMN: BRAND & SHOWCASE ====== */
        .card-brand {
            position: relative;
            padding: 36px 32px;
            color: #ffffff;
            background: 
                linear-gradient(145deg, rgba(15, 23, 42, 0.88) 0%, rgba(30, 41, 59, 0.82) 100%);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            border-right: 1px solid rgba(255, 255, 255, 0.15);
        }

        .brand-glow {
            position: absolute;
            width: 280px;
            height: 280px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.35) 0%, transparent 70%);
            top: -60px;
            left: -60px;
            pointer-events: none;
            filter: blur(40px);
        }

        .brand-top {
            position: relative;
            z-index: 2;
        }

        .brand-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 6px 14px;
            background: rgba(255, 255, 255, 0.14);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 999px;
            font-size: 11.5px;
            font-weight: 800;
            color: #e2e8f0;
            margin-bottom: 16px;
            letter-spacing: 0.3px;
        }

        .brand-badge .dot {
            width: 7px;
            height: 7px;
            background: #22c55e;
            border-radius: 50%;
            box-shadow: 0 0 8px #22c55e;
        }

        .brand-title {
            font-size: 34px;
            font-weight: 900;
            line-height: 1.1;
            letter-spacing: -0.5px;
            color: #ffffff;
            margin-bottom: 8px;
        }

        .brand-title span {
            background: linear-gradient(135deg, #fef08a 0%, #facc15 50%, #f59e0b 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
        }

        .brand-tagline {
            font-size: 13.5px;
            line-height: 1.5;
            color: #cbd5e1;
            font-weight: 500;
        }

        /* Feature Pillars (Gọn gàng, tinh tế) */
        .brand-features {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin: 24px 0;
        }

        .feat-pill {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 9px 14px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 14px;
            font-size: 12.5px;
            font-weight: 600;
            color: #f1f5f9;
            transition: all 0.2s ease;
        }

        .feat-pill:hover {
            background: rgba(255, 255, 255, 0.14);
            transform: translateX(4px);
            border-color: rgba(255, 255, 255, 0.28);
        }

        .feat-icon {
            width: 28px;
            height: 28px;
            border-radius: 9px;
            background: rgba(255, 255, 255, 0.12);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
        }

        .brand-bottom {
            position: relative;
            z-index: 2;
        }

        .btn-pricing-link {
            display: inline-flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            padding: 10px 16px;
            border-radius: 14px;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #ffffff;
            text-decoration: none;
            font-size: 13px;
            font-weight: 800;
            box-shadow: 0 4px 16px rgba(245, 158, 11, 0.35);
            border: 1.5px solid rgba(254, 240, 138, 0.6);
            transition: all 0.2s ease;
        }

        .btn-pricing-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(245, 158, 11, 0.5);
            background: linear-gradient(135deg, #fbbf24 0%, #d97706 100%);
        }

        /* ====== RIGHT COLUMN: CLEAN & ELEGANT FORM ====== */
        .card-form {
            padding: 36px 36px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #ffffff;
        }

        .form-title {
            font-size: 26px;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: -0.5px;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-subtitle {
            font-size: 13.5px;
            color: var(--text-muted);
            font-weight: 500;
            margin-bottom: 18px;
        }

        /* Error Banner */
        .form-error {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 10px 14px;
            background: #fef2f2;
            border: 1.5px solid #fecaca;
            border-radius: 12px;
            color: #b91c1c;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 16px;
            animation: shake 0.4s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-4px); }
            40%, 80% { transform: translateX(4px); }
        }

        /* Quick Account Chips (Gọn gàng, sạch sẽ) */
        .quick-section {
            margin-bottom: 18px;
        }

        .quick-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .quick-header span {
            font-size: 11.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
        }

        .pass-hint {
            font-size: 11px;
            font-weight: 800;
            color: #059669;
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            padding: 2px 8px;
            border-radius: 999px;
        }

        .quick-pills {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .chip-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 11px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 750;
            border: 1.5px solid #e2e8f0;
            background: #f8fafc;
            color: #334155;
            cursor: pointer;
            transition: all 0.15s ease;
            font-family: inherit;
        }

        .chip-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
        }

        .chip-btn.active {
            border-color: var(--primary);
            background: #eef2ff;
            color: var(--primary);
        }

        .chip-admin { border-color: #fde68a; background: #fffbeb; color: #b45309; }
        .chip-admin:hover, .chip-admin.active { border-color: #f59e0b; background: #fef3c7; }

        .chip-teacher { border-color: #bbf7d0; background: #f0fdf4; color: #15803d; }
        .chip-teacher:hover, .chip-teacher.active { border-color: #22c55e; background: #dcfce7; }

        .chip-student { border-color: #bfdbfe; background: #eff6ff; color: #1d4ed8; }
        .chip-student:hover, .chip-student.active { border-color: #3b82f6; background: #dbeafe; }

        /* Form Inputs */
        .form-group {
            margin-bottom: 14px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 750;
            color: #334155;
            margin-bottom: 6px;
        }

        .input-box {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 13px;
            color: #94a3b8;
            display: flex;
            align-items: center;
            pointer-events: none;
        }

        .input-control {
            width: 100%;
            padding: 11px 14px 11px 40px;
            border: 1.6px solid #cbd5e1;
            border-radius: 12px;
            font-size: 14px;
            font-family: inherit;
            font-weight: 600;
            color: #0f172a;
            background: #ffffff;
            outline: none;
            transition: all 0.2s ease;
        }

        .input-control::placeholder {
            color: #94a3b8;
            font-weight: 500;
        }

        .input-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3.5px rgba(79, 70, 229, 0.12);
        }

        .btn-toggle-eye {
            position: absolute;
            right: 10px;
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 5px;
            font-size: 16px;
            display: flex;
            align-items: center;
            border-radius: 6px;
            transition: color 0.15s ease;
        }

        .btn-toggle-eye:hover {
            color: #475569;
        }

        /* Submit Button */
        .btn-login-submit {
            width: 100%;
            margin-top: 6px;
            padding: 12px 20px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
            color: #ffffff;
            font-weight: 850;
            font-size: 14.5px;
            letter-spacing: 0.3px;
            cursor: pointer;
            box-shadow: 0 6px 18px -2px rgba(79, 70, 229, 0.4);
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-family: inherit;
        }

        .btn-login-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px -2px rgba(79, 70, 229, 0.55);
            background: linear-gradient(135deg, #4338ca 0%, #2563eb 100%);
        }

        .btn-login-submit:active {
            transform: translateY(1px);
        }

        /* Subtle Bottom Register Link */
        .login-sublink {
            margin-top: 14px;
            text-align: center;
            font-size: 12.5px;
            color: #64748b;
            font-weight: 600;
        }

        .login-sublink a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 800;
            transition: color 0.15s ease;
        }

        .login-sublink a:hover {
            color: var(--primary-hover);
            text-decoration: underline;
        }

        .form-copyright {
            margin-top: 16px;
            text-align: center;
            font-size: 11.5px;
            color: #94a3b8;
            font-weight: 500;
        }

        /* Responsive */
        @media (max-width: 860px) {
            html, body {
                height: auto;
                overflow-y: auto;
            }
            .login-card {
                grid-template-columns: 1fr;
                border-radius: 22px;
                max-height: none;
            }
            .card-brand {
                padding: 24px 22px;
            }
            .brand-title {
                font-size: 28px;
            }
            .brand-features {
                display: none;
            }
            .card-form {
                padding: 24px 22px;
            }
        }
    </style>
</head>
<body>

    <!-- Cinematic Video Background (bg-hero.mp4) -->
    <div class="video-bg-container">
        <video autoplay muted loop playsinline class="video-bg">
            <source src="{{ asset('images/bg-hero.mp4') }}" type="video/mp4">
        </video>
        <div class="video-overlay"></div>
    </div>

    <!-- Main Glassmorphism Login Card -->
    <main class="login-card">
        
        <!-- Left Side: Brand & Showcase -->
        <section class="card-brand">
            <div class="brand-glow"></div>

            <div class="brand-top">
                <div class="brand-badge">
                    <span class="dot"></span>
                    <span>✨ IC3 GS6 & Spark Quest</span>
                </div>
                <h1 class="brand-title">IC3 <span>QUEST</span></h1>
                <p class="brand-tagline">Nền tảng luyện thi & làm chủ kỹ năng số chuẩn quốc tế cho học sinh.</p>
            </div>

            <div class="brand-features">
                <div class="feat-pill">
                    <span class="feat-icon">🎯</span>
                    <span>Ngân hàng câu hỏi trắc nghiệm & tương tác 3D</span>
                </div>
                <div class="feat-pill">
                    <span class="feat-icon">🤖</span>
                    <span>Trợ lý AI chấm điểm & phản hồi kết quả tức thì</span>
                </div>
                <div class="feat-pill">
                    <span class="feat-icon">🏆</span>
                    <span>Bảng vàng thi đua, tích điểm đổi quà & studio đề</span>
                </div>
            </div>

            <div class="brand-bottom">
                <a href="{{ route('pricing.index') }}" class="btn-pricing-link">
                    <span>💎 Xem Các Gói & Đăng Ký Tài Khoản</span>
                    <span style="font-size: 15px;">→</span>
                </a>
            </div>
        </section>

        <!-- Right Side: Clean Login Form -->
        <section class="card-form">
            <div class="form-header-box">
                <h2 class="form-title">Đăng nhập 👋</h2>
                <p class="form-subtitle">Chọn tài khoản mẫu hoặc nhập thông tin đăng nhập:</p>
            </div>

            @error('login')
                <div class="form-error">
                    <span>⚠️</span>
                    <span>{{ $message }}</span>
                </div>
            @enderror

            <!-- Quick Account Switcher (Thanh thoát & Hiện đại) -->
            <div class="quick-section">
                <div class="quick-header">
                    <span>Tài khoản mẫu nhanh</span>
                    <span class="pass-hint">🔑 Pass: 123456</span>
                </div>
                <div class="quick-pills">
                    <button type="button" class="chip-btn chip-admin" onclick="quickFill('admin', '123456', this)">
                        👑 Admin
                    </button>
                    <button type="button" class="chip-btn chip-teacher" onclick="quickFill('teacher', '123456', this)">
                        👨‍🏫 GV Khối 3
                    </button>
                    <button type="button" class="chip-btn chip-student" onclick="quickFill('hs001', '123456', this)">
                        🎓 Khối 3
                    </button>
                    <button type="button" class="chip-btn chip-student" onclick="quickFill('hs002', '123456', this)">
                        🎓 Khối 4
                    </button>
                    <button type="button" class="chip-btn chip-student" onclick="quickFill('hs003', '123456', this)">
                        🎓 Khối 5
                    </button>
                </div>
            </div>

            <!-- Main Login Form -->
            <form method="post" action="{{ route('login.store') }}" id="loginForm">
                @csrf

                <div class="form-group">
                    <label for="loginInput" class="form-label">Tài khoản (Tên / Mã HS / Email)</label>
                    <div class="input-box">
                        <span class="input-icon">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </span>
                        <input id="loginInput" class="input-control" name="login" value="{{ old('login') }}" required autofocus placeholder="admin, teacher, hs001 hoặc email">
                    </div>
                </div>

                <div class="form-group">
                    <label for="passwordInput" class="form-label">Mật khẩu</label>
                    <div class="input-box">
                        <span class="input-icon">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2.2" ry="2.2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                        </span>
                        <input id="passwordInput" class="input-control" name="password" type="password" required placeholder="Nhập mật khẩu (123456)">
                        <button type="button" class="btn-toggle-eye" onclick="togglePassword()" title="Hiện/ẩn mật khẩu">
                            <span id="eyeIcon">👁️</span>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-login-submit">
                    <span>ĐĂNG NHẬP NGAY</span>
                    <span>🚀</span>
                </button>
            </form>

            <div class="login-sublink">
                Chưa có tài khoản Giáo viên? <a href="{{ route('pricing.index') }}">Đăng ký thuê gói để nhận tài khoản ngay →</a>
            </div>

            <div class="form-copyright">
                Luyện thi IC3 GS6 & Quản trị MOS © 2026
            </div>
        </section>
    </main>

    <script>
        function quickFill(user, pass, element) {
            const loginInput = document.getElementById('loginInput');
            const passInput = document.getElementById('passwordInput');
            
            loginInput.value = user;
            passInput.value = pass;
            
            document.querySelectorAll('.chip-btn').forEach(chip => chip.classList.remove('active'));
            if (element) {
                element.classList.add('active');
            }
            
            loginInput.focus();
        }

        function togglePassword() {
            const passwordInput = document.getElementById('passwordInput');
            const eyeIcon = document.getElementById('eyeIcon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.textContent = '🙈';
            } else {
                passwordInput.type = 'password';
                eyeIcon.textContent = '👁️';
            }
        }
    </script>
</body>
</html>
