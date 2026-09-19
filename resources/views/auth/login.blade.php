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
            --primary-light: #6366f1;
            --accent: #06b6d4;
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
            background-color: #0b1329;
            background-image: 
                radial-gradient(at 0% 0%, rgba(59, 130, 246, 0.28) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(14, 165, 233, 0.22) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(139, 92, 246, 0.25) 0px, transparent 50%),
                radial-gradient(at 0% 100%, rgba(16, 185, 129, 0.18) 0px, transparent 50%),
                radial-gradient(at 50% 50%, rgba(99, 102, 241, 0.15) 0px, transparent 65%);
            color: var(--text-main);
            padding: 16px;
            position: relative;
        }

        /* Ambient glowing circles */
        .ambient-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            z-index: 0;
            pointer-events: none;
            opacity: 0.55;
            animation: floatOrb 12s ease-in-out infinite alternate;
        }
        .orb-1 {
            width: 380px;
            height: 380px;
            background: linear-gradient(135deg, #38bdf8, #6366f1);
            top: -50px;
            left: -100px;
        }
        .orb-2 {
            width: 420px;
            height: 420px;
            background: linear-gradient(135deg, #a855f7, #ec4899);
            bottom: -60px;
            right: -80px;
            animation-duration: 16s;
            animation-delay: -3s;
        }

        @keyframes floatOrb {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(30px, 20px) scale(1.06); }
        }

        /* Main Container Card - Compact 1 Screen Fit */
        .box {
            position: relative;
            z-index: 1;
            width: min(990px, 95vw);
            max-height: calc(100vh - 32px);
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            background: rgba(255, 255, 255, 0.98);
            border: 1px solid rgba(255, 255, 255, 0.85);
            border-radius: 26px;
            overflow: hidden;
            box-shadow: 
                0 20px 60px -10px rgba(15, 23, 42, 0.45),
                0 0 0 1px rgba(255, 255, 255, 0.6) inset;
            backdrop-filter: blur(20px);
            transition: all 0.3s ease;
        }

        /* Left Hero Banner - Clear Vibrant Image Showcase */
        .art {
            position: relative;
            padding: 28px 28px;
            color: #ffffff;
            background: 
                linear-gradient(180deg, 
                    rgba(15, 23, 42, 0.7) 0%, 
                    rgba(15, 23, 42, 0.06) 24%, 
                    rgba(15, 23, 42, 0.08) 55%, 
                    rgba(15, 23, 42, 0.85) 100%),
                url('/images/ic3-hero-vertical.jpg') center 25% / cover no-repeat;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
        }

        .art-top {
            position: relative;
            z-index: 2;
        }

        .art-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 6px 14px;
            background: rgba(255, 255, 255, 0.22);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.3px;
            color: #ffffff;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.4);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            margin-bottom: 10px;
        }

        .art-badge .dot {
            width: 7px;
            height: 7px;
            background: #4ade80;
            border-radius: 50%;
            box-shadow: 0 0 8px #4ade80;
        }

        .art h1 {
            font-size: 32px;
            font-weight: 900;
            letter-spacing: -0.5px;
            color: #ffffff;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.6);
            margin-bottom: 4px;
            line-height: 1.1;
        }

        .art h1 span {
            background: linear-gradient(135deg, #fef08a 0%, #facc15 60%, #fb923c 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: none;
            filter: drop-shadow(0 2px 6px rgba(0, 0, 0, 0.4));
        }

        .art p.tagline {
            font-size: 13.5px;
            line-height: 1.45;
            color: #f1f5f9;
            text-shadow: 0 1px 5px rgba(0, 0, 0, 0.65);
            font-weight: 500;
        }

        /* Glass Feature Cards at bottom of banner */
        .art-bottom {
            position: relative;
            z-index: 2;
        }

        .art-features {
            display: grid;
            gap: 8px;
            margin-top: 14px;
        }

        .art-feat-item {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(15, 23, 42, 0.52);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border: 1px solid rgba(255, 255, 255, 0.22);
            padding: 8px 13px;
            border-radius: 12px;
            font-size: 12.5px;
            font-weight: 600;
            color: #ffffff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            transition: transform 0.15s ease, background 0.15s ease;
        }

        .art-feat-item:hover {
            transform: translateX(3px);
            background: rgba(15, 23, 42, 0.65);
        }

        .art-feat-icon {
            font-size: 15px;
            display: grid;
            place-items: center;
            width: 26px;
            height: 26px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 8px;
            flex-shrink: 0;
        }

        /* Right Column Form */
        .form-side {
            padding: 28px 32px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #ffffff;
        }

        .form-header {
            margin-bottom: 12px;
        }

        .form-header h2 {
            font-size: 24px;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: -0.4px;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 3px;
        }

        .form-header p {
            color: var(--text-muted);
            font-size: 13px;
            font-weight: 500;
        }

        /* Error box */
        .error {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 9px 13px;
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
            border-radius: 10px;
            font-size: 12.5px;
            font-weight: 600;
            margin-bottom: 12px;
            animation: shake 0.4s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-4px); }
            40%, 80% { transform: translateX(4px); }
        }

        /* Quick Account Switcher */
        .quick-accounts {
            background: #f8fafc;
            border: 1.5px dashed #cbd5e1;
            border-radius: 14px;
            padding: 10px 12px;
            margin-bottom: 14px;
            transition: border-color 0.2s ease;
        }

        .quick-accounts:hover {
            border-color: #94a3b8;
        }

        .quick-title {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #475569;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .quick-pass-hint {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 10.5px;
            font-weight: 700;
            color: #059669;
            background: #ecfdf5;
            padding: 2px 7px;
            border-radius: 99px;
        }

        .quick-chips {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .quick-chip {
            border: 1.5px solid #e2e8f0;
            background: #ffffff;
            padding: 5px 10px;
            border-radius: 8px;
            font-size: 11.5px;
            font-weight: 700;
            color: #334155;
            cursor: pointer;
            transition: all 0.15s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
            font-family: inherit;
        }

        .quick-chip:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08);
        }

        .chip-admin {
            border-color: #fef08a;
            background: #fffbeb;
            color: #b45309;
        }
        .chip-admin:hover, .chip-admin.active {
            border-color: #f59e0b;
            background: #fef3c7;
            color: #92400e;
        }

        .chip-teacher {
            border-color: #bbf7d0;
            background: #f0fdf4;
            color: #15803d;
        }
        .chip-teacher:hover, .chip-teacher.active {
            border-color: #22c55e;
            background: #dcfce7;
            color: #166534;
        }

        .chip-student {
            border-color: #bfdbfe;
            background: #eff6ff;
            color: #1d4ed8;
        }
        .chip-student:hover, .chip-student.active {
            border-color: #3b82f6;
            background: #dbeafe;
            color: #1e40af;
        }

        /* Form Inputs */
        .form-group {
            margin-bottom: 11px;
            position: relative;
        }

        .form-group label {
            display: block;
            font-weight: 700;
            font-size: 12.5px;
            color: #334155;
            margin-bottom: 5px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 12px;
            color: #94a3b8;
            font-size: 15px;
            pointer-events: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .form-input {
            width: 100%;
            padding: 10px 14px 10px 38px;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            font-size: 13.5px;
            font-family: inherit;
            font-weight: 600;
            color: #0f172a;
            outline: none;
            background: #ffffff;
            transition: all 0.2s ease;
        }

        .form-input::placeholder {
            color: #94a3b8;
            font-weight: 500;
        }

        .form-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
        }

        .password-toggle {
            position: absolute;
            right: 10px;
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 4px;
            font-size: 16px;
            display: flex;
            align-items: center;
            border-radius: 6px;
            transition: color 0.15s ease;
        }

        .password-toggle:hover {
            color: #475569;
        }

        /* Submit Button */
        .btn-submit {
            width: 100%;
            margin-top: 8px;
            padding: 11px 18px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 50%, #06b6d4 100%);
            background-size: 200% auto;
            color: #ffffff;
            font-weight: 800;
            font-size: 14px;
            letter-spacing: 0.3px;
            cursor: pointer;
            box-shadow: 0 6px 16px -3px rgba(79, 70, 229, 0.4);
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-family: inherit;
        }

        .btn-submit:hover {
            background-position: right center;
            transform: translateY(-1px);
            box-shadow: 0 8px 20px -3px rgba(79, 70, 229, 0.5);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .form-footer {
            margin-top: 12px;
            text-align: center;
            font-size: 11.5px;
            color: #94a3b8;
            font-weight: 500;
        }

        /* Banner Đăng ký nhận tài khoản Giáo viên mới */
        .teacher-register-card {
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
            border: 1.5px solid #fde68a;
            border-radius: 14px;
            padding: 10px 14px;
            margin-bottom: 12px;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.12);
        }
        .btn-upgrade-teacher-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 12px;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border: 1.5px solid #fef08a;
            border-radius: 10px;
            color: #ffffff !important;
            font-size: 12px;
            font-weight: 900;
            text-decoration: none !important;
            box-shadow: 0 3px 8px rgba(217, 119, 6, 0.3);
            transition: transform 0.15s, box-shadow 0.15s;
        }
        .btn-upgrade-teacher-link:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 12px rgba(217, 119, 6, 0.45);
            background: linear-gradient(135deg, #fbbf24 0%, #d97706 100%);
        }

        @media (max-width: 860px) {
            html, body {
                height: auto;
                overflow-y: auto;
            }
            .box {
                grid-template-columns: 1fr;
                border-radius: 20px;
                max-height: none;
            }
            .art {
                min-height: 220px;
                padding: 24px 20px;
            }
            .art h1 {
                font-size: 26px;
            }
            .art-features {
                display: none;
            }
            .form-side {
                padding: 24px 20px;
            }
        }
    </style>
</head>
<body>

    <!-- Dynamic background orbs -->
    <div class="ambient-orb orb-1"></div>
    <div class="ambient-orb orb-2"></div>

    <main class="box">
        <!-- Left Hero Art Section -->
        <section class="art">
            <div class="art-top">
                <div class="art-badge">
                    <span class="dot"></span>
                    <span>✨ IC3 GS6 & Spark Quest</span>
                </div>
                <h1>IC3 <span>QUEST</span></h1>
                <p class="tagline">Nền tảng luyện thi & làm chủ kỹ năng số chuẩn quốc tế cho học sinh.</p>

                <!-- Nút Xem các gói bản quyền bên trái -->
                <a href="{{ route('pricing.index') }}" style="display:inline-flex; align-items:center; gap:8px; margin-top:14px; padding:9px 16px; border-radius:12px; background:linear-gradient(135deg, #f59e0b, #d97706); color:#fff; font-weight:900; font-size:12.5px; text-decoration:none; box-shadow:0 4px 14px rgba(245,158,11,0.45); border:1.5px solid #fef08a;">
                    <span>💎</span> <span>Xem Các Gói & Đăng Ký Tài Khoản Mới</span> <b>→</b>
                </a>
            </div>

            <div class="art-bottom">
                <div class="art-features">
                    <div class="art-feat-item">
                        <span class="art-feat-icon">🎯</span>
                        <span>Ngân hàng câu hỏi trắc nghiệm & tương tác 3D</span>
                    </div>
                    <div class="art-feat-item">
                        <span class="art-feat-icon">🤖</span>
                        <span>Trợ lý AI chấm điểm & phản hồi kết quả tức thì</span>
                    </div>
                    <div class="art-feat-item">
                        <span class="art-feat-icon">🏆</span>
                        <span>Bảng vàng thi đua, tích điểm đổi quà & studio đề</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Right Login Form Section -->
        <section class="form-side">
            <div class="form-header">
                <h2>Đăng nhập 👋</h2>
                <p>Chọn tài khoản mẫu bên dưới hoặc nhập thông tin:</p>
            </div>

            @error('login')
                <div class="error">
                    <span>⚠️</span>
                    <span>{{ $message }}</span>
                </div>
            @enderror

            <!-- Quick Account Fill Chips -->
            <div class="quick-accounts">
                <div class="quick-title">
                    <span>Tài khoản mẫu nhanh</span>
                    <span class="quick-pass-hint">🔑 Pass: 123456</span>
                </div>
                <div class="quick-chips">
                    <button type="button" class="quick-chip chip-admin" onclick="quickFill('admin', '123456', this)">
                        👑 <strong>Admin</strong>
                    </button>
                    <button type="button" class="quick-chip chip-teacher" onclick="quickFill('teacher', '123456', this)">
                        👨‍🏫 <strong>GV Khối 3</strong>
                    </button>
                    <button type="button" class="quick-chip chip-student" onclick="quickFill('hs001', '123456', this)">
                        🎓 <strong>Khối 3</strong> (hs001)
                    </button>
                    <button type="button" class="quick-chip chip-student" onclick="quickFill('hs002', '123456', this)">
                        🎓 <strong>Khối 4</strong> (hs002)
                    </button>
                    <button type="button" class="quick-chip chip-student" onclick="quickFill('hs003', '123456', this)">
                        🎓 <strong>Khối 5</strong> (hs003)
                    </button>
                </div>
            </div>

            <!-- Banner Đăng Ký Nhận Tài Khoản Giáo Viên Mới Qua Gói Bản Quyền -->
            <div class="teacher-register-card">
                <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:5px;">
                    <span style="font-size:11px; font-weight:900; color:#b45309; text-transform:uppercase; letter-spacing:0.5px;">
                        ✨ CHƯA CÓ TÀI KHOẢN GIÁO VIÊN?
                    </span>
                    <span style="background:#fde68a; color:#92400e; font-size:10px; font-weight:900; padding:1px 6px; border-radius:99px;">
                        CẤP TỰ ĐỘNG
                    </span>
                </div>
                <div style="font-size:12px; color:#475569; line-height:1.4; margin-bottom:8px;">
                    Đăng ký thuê gói để <b>nhận tài khoản Giáo viên</b> & cấp lớp luyện thi cho học sinh ngay:
                </div>
                <a href="{{ route('pricing.index') }}" class="btn-upgrade-teacher-link">
                    <span>💎 Xem Các Gói & Đăng Ký Tài Khoản</span>
                    <b>→</b>
                </a>
            </div>

            <!-- Login Form -->
            <form method="post" action="{{ route('login.store') }}" id="loginForm">
                @csrf

                <div class="form-group">
                    <label for="loginInput">Tài khoản (Tên / Mã HS / Email)</label>
                    <div class="input-wrapper">
                        <span class="input-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </span>
                        <input id="loginInput" class="form-input" name="login" value="{{ old('login') }}" required autofocus placeholder="admin, teacher, hs001 hoặc email">
                    </div>
                </div>

                <div class="form-group">
                    <label for="passwordInput">Mật khẩu</label>
                    <div class="input-wrapper">
                        <span class="input-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                        </span>
                        <input id="passwordInput" class="form-input" name="password" type="password" required placeholder="Nhập mật khẩu (123456)">
                        <button type="button" class="password-toggle" onclick="togglePassword()" title="Hiện/ẩn mật khẩu">
                            <span id="eyeIcon">👁️</span>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <span>ĐĂNG NHẬP NGAY</span>
                    <span style="font-size: 15px;">🚀</span>
                </button>
            </form>

            <div class="form-footer">
                <span>Luyện thi IC3 GS6 & Quản trị MOS © 2026</span>
            </div>
        </section>
    </main>

    <script>
        function quickFill(user, pass, element) {
            const loginInput = document.getElementById('loginInput');
            const passInput = document.getElementById('passwordInput');
            
            loginInput.value = user;
            passInput.value = pass;
            
            // Highlight active button
            document.querySelectorAll('.quick-chip').forEach(chip => chip.classList.remove('active'));
            if (element) {
                element.classList.add('active');
            }
            
            // Focus and subtle feedback
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
