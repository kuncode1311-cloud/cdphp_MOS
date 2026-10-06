{{-- Form gửi sang AuthController; lỗi nhập liệu được Laravel trả về trong biến errors. --}}
<!doctype html>
<html lang="vi">
<head>
    @include('partials.page-gate')
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
            --primary-glow: #ec4899;
            --accent-purple: #8b5cf6;
            --card-bg: rgba(15, 10, 25, 0.72);
            --card-border: rgba(255, 255, 255, 0.16);
            --input-bg: rgba(255, 255, 255, 0.08);
            --input-border: rgba(255, 255, 255, 0.18);
            --text-main: #ffffff;
            --text-sub: #cbd5e1;
            --text-dim: #94a3b8;
            --zalo-blue: #0068ff;
            --zalo-blue-hover: #0056d6;
            --zalo-light-bubble: #e5efff;
        }

        html, body {
            height: 100%;
            min-height: 100%;
            overflow-x: hidden;
        }

        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #05020a;
            color: var(--text-main);
            padding: 16px;
            position: relative;
        }

        /* ====== CINEMATIC VIDEO BACKGROUND ====== */
        .video-bg-container {
            position: fixed;
            inset: 0;
            width: 100%;
            height: 100%;
            background: #05020a;
            overflow: hidden;
            z-index: 0;
            pointer-events: none;
        }

        /* Video giãn kín màn hình theo cả chiều ngang và dọc: giữ đủ toàn bộ nội dung, chấp nhận méo tỉ lệ */
        .video-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: fill;
            filter: brightness(1.12) contrast(1.05) saturate(1.12);
            /* Ẩn video cho tới khi phát được: nền tối đứng yên, không hiện ảnh tĩnh khác video rồi mới nhảy */
            opacity: 0;
            transition: opacity 0.8s ease;
        }
        .video-bg.is-ready { opacity: 1; }
        /* Chỉ khi video không tải được mới dùng ảnh nền thay thế */
        .video-bg-container.video-failed { background: #05020a url('{{ asset('images/ic3-login-hero.jpg') }}') center / cover no-repeat; }

        .video-overlay {
            position: absolute;
            inset: 0;
            background: 
                radial-gradient(ellipse at center, rgba(10, 5, 20, 0.12) 0%, rgba(5, 2, 10, 0.42) 100%),
                linear-gradient(180deg, rgba(5, 2, 10, 0.05) 0%, rgba(5, 2, 10, 0.38) 100%);
        }

        /* ====== ULTRA-PREMIUM GOLDEN-RATIO GLASS CARD ====== */
        .login-wrapper {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 495px;
            margin: auto;
            animation: cardFadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes cardFadeUp {
            from {
                opacity: 0;
                transform: translateY(20px) scale(0.97);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .glass-card {
            background: var(--card-bg);
            backdrop-filter: blur(28px) saturate(190%);
            -webkit-backdrop-filter: blur(28px) saturate(190%);
            border: 1.5px solid var(--card-border);
            border-radius: 28px;
            padding: clamp(26px, 4vh, 38px) clamp(28px, 4.5vw, 42px) clamp(22px, 3.2vh, 30px) clamp(28px, 4.5vw, 42px);
            box-shadow: 
                0 24px 70px -10px rgba(0, 0, 0, 0.85),
                0 0 45px -10px rgba(236, 72, 153, 0.25),
                inset 0 1px 1px rgba(255, 255, 255, 0.25);
            position: relative;
            overflow: hidden;
        }

        /* Ambient Top Glow Line */
        .glass-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 15%;
            right: 15%;
            height: 2.5px;
            background: linear-gradient(90deg, transparent, #ec4899, #8b5cf6, transparent);
            filter: drop-shadow(0 0 8px #ec4899);
        }

        /* ====== HEADER SECTION ====== */
        .card-header {
            text-align: center;
            margin-bottom: clamp(14px, 2.4vh, 22px);
        }

        .badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: clamp(4.5px, 0.8vh, 6px) clamp(13px, 1.8vw, 16px);
            background: rgba(244, 114, 182, 0.12);
            border: 1px solid rgba(244, 114, 182, 0.3);
            border-radius: 999px;
            font-size: clamp(11.5px, 1.4vh, 12.5px);
            font-weight: 750;
            color: #fbcfe8;
            letter-spacing: 0.35px;
            margin-bottom: clamp(7px, 1.2vh, 11px);
            box-shadow: 0 0 15px rgba(244, 114, 182, 0.15);
        }

        .badge-pill .sparkle {
            animation: pulseGlow 2s infinite ease-in-out;
        }

        @keyframes pulseGlow {
            0%, 100% { transform: scale(1); opacity: 0.9; }
            50% { transform: scale(1.2); opacity: 1; }
        }

        .brand-name {
            font-size: clamp(30px, 4.2vh, 36px);
            font-weight: 900;
            letter-spacing: -0.5px;
            line-height: 1.15;
            color: #ffffff;
            margin-bottom: clamp(4px, 0.8vh, 7px);
        }

        .brand-name span {
            background: linear-gradient(135deg, #fbcfe8 0%, #f472b6 45%, #c084fc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
            filter: drop-shadow(0 2px 12px rgba(236, 72, 153, 0.35));
        }

        .brand-desc {
            font-size: clamp(12.5px, 1.6vh, 13.8px);
            color: var(--text-sub);
            font-weight: 500;
        }

        /* Error Notice */
        .alert-error {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 9px 14px;
            background: rgba(239, 68, 68, 0.18);
            border: 1.2px solid rgba(248, 113, 113, 0.45);
            border-radius: 12px;
            color: #fca5a5;
            font-size: 12.5px;
            font-weight: 650;
            margin-bottom: 12px;
            backdrop-filter: blur(8px);
            animation: shake 0.4s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-5px); }
            40%, 80% { transform: translateX(5px); }
        }

        /* ====== FORM INPUTS ====== */
        .form-group {
            margin-bottom: clamp(12px, 2vh, 18px);
        }

        .form-label {
            display: block;
            font-size: clamp(12px, 1.5vh, 13px);
            font-weight: 750;
            color: #e2e8f0;
            margin-bottom: clamp(5px, 0.8vh, 7px);
            letter-spacing: 0.15px;
        }

        .input-box {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            color: #94a3b8;
            display: flex;
            align-items: center;
            pointer-events: none;
            transition: color 0.2s ease;
        }

        .input-control {
            width: 100%;
            padding: clamp(11.5px, 1.6vh, 14px) 16px clamp(11.5px, 1.6vh, 14px) 44px;
            background: var(--input-bg);
            border: 1.5px solid var(--input-border);
            border-radius: 14px;
            font-size: clamp(13.8px, 1.7vh, 14.8px);
            font-family: inherit;
            font-weight: 600;
            color: #ffffff;
            outline: none;
            transition: all 0.25s ease;
        }

        .input-control::placeholder {
            color: rgba(255, 255, 255, 0.35);
            font-weight: 450;
        }

        .input-control:focus {
            background: rgba(255, 255, 255, 0.12);
            border-color: #f472b6;
            box-shadow: 0 0 0 3.5px rgba(244, 114, 182, 0.22), 0 0 18px rgba(244, 114, 182, 0.15);
        }

        .input-control:focus + .input-icon,
        .input-box:focus-within .input-icon {
            color: #f472b6;
        }

        .btn-toggle-eye {
            position: absolute;
            right: 14px;
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 5px;
            font-size: 17px;
            display: flex;
            align-items: center;
            border-radius: 6px;
            transition: color 0.15s ease;
        }

        .btn-toggle-eye:hover {
            color: #ffffff;
        }

        /* ====== SUBMIT BUTTON ====== */
        .btn-submit {
            width: 100%;
            margin-top: clamp(4px, 0.8vh, 8px);
            padding: clamp(12px, 1.8vh, 14.5px) 20px;
            border: none;
            border-radius: 14px;
            background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 50%, #6366f1 100%);
            background-size: 200% auto;
            color: #ffffff;
            font-weight: 850;
            font-size: clamp(14px, 1.8vh, 15.5px);
            letter-spacing: 0.35px;
            cursor: pointer;
            box-shadow: 0 7px 22px -2px rgba(236, 72, 153, 0.48), inset 0 1px 1px rgba(255, 255, 255, 0.3);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-family: inherit;
        }

        .btn-submit:hover {
            background-position: right center;
            transform: translateY(-2px);
            box-shadow: 0 11px 28px -2px rgba(236, 72, 153, 0.62), inset 0 1px 1px rgba(255, 255, 255, 0.4);
        }

        .btn-submit:active {
            transform: translateY(1px);
        }

        /* ====== NÚT ĐĂNG NHẬP GOOGLE 3D TACTILE ====== */
        .social-divider {
            display: flex;
            align-items: center;
            margin: clamp(12px, 2vh, 18px) 0 clamp(10px, 1.5vh, 14px) 0;
            color: rgba(255, 255, 255, 0.42);
            font-size: clamp(10.5px, 1.3vh, 11px);
            font-weight: 750;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }

        .social-divider::before,
        .social-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.18), transparent);
        }

        .social-divider span {
            padding: 0 9px;
        }

        .btn-google-login {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: clamp(11px, 1.6vh, 13.5px) 18px;
            border-radius: 14px;
            background: #ffffff;
            color: #1e293b;
            font-weight: 800;
            font-size: clamp(13.8px, 1.7vh, 14.8px);
            text-decoration: none;
            box-shadow: 0 5px 18px rgba(0, 0, 0, 0.24), inset 0 -2px 0 rgba(0, 0, 0, 0.1);
            border: 1.5px solid rgba(255, 255, 255, 0.85);
            transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
            font-family: inherit;
        }

        .btn-google-login:hover {
            background: #f8fafc;
            color: #0f172a;
            transform: translateY(-2px);
            box-shadow: 0 9px 25px rgba(0, 0, 0, 0.32), 0 0 18px rgba(66, 133, 244, 0.3), inset 0 -2px 0 rgba(0, 0, 0, 0.12);
        }

        .btn-google-login:active {
            transform: translateY(1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2), inset 0 -1px 0 rgba(0, 0, 0, 0.1);
        }

        .google-icon {
            flex-shrink: 0;
            width: clamp(19px, 2.4vh, 21px);
            height: clamp(19px, 2.4vh, 21px);
        }

        /* ====== DIVIDER ====== */
        .card-divider {
            display: flex;
            align-items: center;
            margin: clamp(12px, 2vh, 18px) 0 clamp(10px, 1.5vh, 14px) 0;
            color: rgba(255, 255, 255, 0.35);
            font-size: clamp(10.5px, 1.3vh, 11px);
            font-weight: 700;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }

        .card-divider::before,
        .card-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
        }

        .card-divider span {
            padding: 0 9px;
        }

        /* ====== 💎 SIÊU NÚT XEM GÓI & MUA BẢN QUYỀN ====== */
        .btn-pricing-cta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: clamp(10px, 1.5vh, 13px) clamp(14px, 2vw, 18px);
            border-radius: 14px;
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.22) 0%, rgba(217, 119, 6, 0.16) 100%);
            border: 1.3px solid rgba(251, 191, 36, 0.48);
            text-decoration: none;
            color: #ffffff;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(245, 158, 11, 0.14);
        }

        .btn-pricing-cta::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 60%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transform: skewX(-25deg);
            transition: 0.75s;
        }

        .btn-pricing-cta:hover::before {
            left: 140%;
        }

        .btn-pricing-cta:hover {
            transform: translateY(-2px);
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.32) 0%, rgba(217, 119, 6, 0.24) 100%);
            border-color: #facc15;
            box-shadow: 0 7px 22px rgba(245, 158, 11, 0.28), 0 0 14px rgba(250, 204, 21, 0.2);
        }

        .pricing-cta-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .pricing-cta-icon {
            width: clamp(32px, 4vh, 38px);
            height: clamp(32px, 4vh, 38px);
            border-radius: 10px;
            background: linear-gradient(135deg, #fbbf24 0%, #d97706 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: clamp(16px, 2vh, 18px);
            box-shadow: 0 3px 10px rgba(245, 158, 11, 0.38);
            flex-shrink: 0;
        }

        .pricing-cta-text {
            display: flex;
            flex-direction: column;
        }

        .pricing-cta-title {
            font-size: clamp(12.5px, 1.6vh, 13.8px);
            font-weight: 850;
            color: #fef08a;
            letter-spacing: 0.15px;
        }

        .pricing-cta-desc {
            font-size: clamp(11px, 1.3vh, 12px);
            color: #cbd5e1;
            font-weight: 500;
        }

        .pricing-cta-arrow {
            font-size: 15px;
            color: #fde047;
            font-weight: 800;
            transition: transform 0.2s ease;
        }

        .btn-pricing-cta:hover .pricing-cta-arrow {
            transform: translateX(4px);
        }

        /* ====== FOOTER LINKS ====== */
        .card-footer {
            margin-top: clamp(12px, 1.8vh, 18px);
            text-align: center;
            display: flex;
            flex-direction: column;
            gap: clamp(4px, 0.8vh, 6px);
        }

        .contact-admin-box {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: clamp(11.8px, 1.5vh, 12.8px);
            color: var(--text-dim);
            font-weight: 550;
        }

        .btn-contact-trigger {
            background: none;
            border: none;
            color: #f472b6;
            font-weight: 750;
            font-size: clamp(11.8px, 1.5vh, 12.8px);
            font-family: inherit;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 3px;
            transition: all 0.15s ease;
            padding: 1px 4px;
        }

        .btn-contact-trigger:hover {
            color: #fbcfe8;
            text-decoration: underline;
            text-shadow: 0 0 8px rgba(244, 114, 182, 0.5);
        }

        .copyright-text {
            font-size: clamp(10.5px, 1.3vh, 11.5px);
            color: rgba(255, 255, 255, 0.3);
            font-weight: 500;
        }

        /* ====== 💬 NÚT NỔI CHAT ZALO STYLE ====== */
        .floating-chat-btn {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 999;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            border-radius: 999px;
            background: linear-gradient(135deg, #0068ff 0%, #0084ff 100%);
            border: 2px solid rgba(255, 255, 255, 0.4);
            color: #ffffff;
            font-weight: 800;
            font-size: 13.5px;
            font-family: inherit;
            cursor: pointer;
            box-shadow: 0 8px 25px rgba(0, 104, 255, 0.45);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .floating-chat-btn:hover {
            transform: translateY(-3px) scale(1.03);
            background: linear-gradient(135deg, #0056d6 0%, #0073e6 100%);
            box-shadow: 0 12px 32px rgba(0, 104, 255, 0.6);
        }

        .online-dot {
            width: 9px;
            height: 9px;
            background: #22c55e;
            border: 2px solid #ffffff;
            border-radius: 50%;
            animation: pulseDot 2s infinite;
        }

        @keyframes pulseDot {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(34, 197, 94, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
        }

        /* ====== 💬 HỘP THOẠI CHAT ZALO CHUẨN ĐẸP (KHÔNG VỠ GIAO DIỆN) ====== */
        .chat-popover {
            position: fixed;
            bottom: 86px;
            right: 24px;
            width: 380px;
            max-width: calc(100vw - 32px);
            height: 520px;
            max-height: calc(100vh - 110px);
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.45);
            border: 1px solid #cbd5e1;
            z-index: 1000;
            display: none;
            flex-direction: column;
            overflow: hidden;
            animation: chatSlideUp 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .chat-popover.open {
            display: flex;
        }

        @keyframes chatSlideUp {
            from {
                opacity: 0;
                transform: translateY(16px) scale(0.96);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Header Zalo Style */
        .zalo-header {
            background: linear-gradient(135deg, #0068ff 0%, #007aff 100%);
            color: #ffffff;
            padding: 12px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(0, 104, 255, 0.2);
        }

        .zalo-header-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .zalo-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #ffffff;
            color: #0068ff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 900;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        }

        .zalo-header-info h4 {
            font-size: 14px;
            font-weight: 800;
            margin: 0;
            color: #ffffff;
            letter-spacing: -0.2px;
        }

        .zalo-header-info span {
            font-size: 11px;
            color: #dbeafe;
            display: flex;
            align-items: center;
            gap: 4px;
            font-weight: 500;
        }

        .zalo-header-actions {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .zalo-btn-action {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: #ffffff;
            height: 28px;
            padding: 0 8px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            transition: background 0.15s;
        }

        .zalo-btn-action:hover {
            background: rgba(255, 255, 255, 0.35);
        }

        /* Body Chat - Nền xám Zalo */
        .zalo-chat-body {
            flex: 1;
            min-height: 0;
            background: #f4f5f7;
            padding: 12px 14px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 10px;
            scroll-behavior: smooth;
        }

        .zalo-chat-body::-webkit-scrollbar {
            width: 5px;
        }

        .zalo-chat-body::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 999px;
        }

        /* Date Badge Zalo */
        .zalo-date-badge {
            align-self: center;
            background: rgba(0, 0, 0, 0.08);
            color: #64748b;
            font-size: 11px;
            font-weight: 600;
            padding: 2px 10px;
            border-radius: 999px;
            margin: 2px 0;
        }

        /* Tin nhắn dạng bong bóng Zalo */
        .zalo-bubble {
            max-width: 82%;
            padding: 9px 13px;
            font-size: 13px;
            line-height: 1.45;
            word-break: break-word;
            animation: bubbleFade 0.2s ease;
        }

        @keyframes bubbleFade {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Tin nhắn nhận (Admin / Bot) - Bên trái */
        .zalo-bubble-left {
            align-self: flex-start;
            background: #ffffff;
            color: #0f172a;
            border-radius: 14px 14px 14px 4px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
        }

        /* Tin nhắn gửi (Người dùng) - Bên phải */
        .zalo-bubble-right {
            align-self: flex-end;
            background: var(--zalo-light-bubble);
            color: #0040a8;
            border-radius: 14px 14px 4px 14px;
            border: 1px solid #bfdbfe;
            box-shadow: 0 1px 3px rgba(0, 104, 255, 0.08);
        }

        .bubble-time {
            font-size: 10px;
            opacity: 0.65;
            margin-top: 3px;
            text-align: right;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 4px;
        }

        /* Thông tin người gửi (Khách vãng lai) */
        .zalo-sender-bar {
            background: #ffffff;
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            padding: 7px 12px;
            flex-shrink: 0;
        }

        .sender-form-box {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .sender-form-header {
            font-size: 11px;
            color: #64748b;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .sender-inputs-row {
            display: flex;
            gap: 6px;
        }

        .zalo-field-input {
            flex: 1;
            padding: 6px 9px;
            border: 1.2px solid #cbd5e1;
            border-radius: 8px;
            font-size: 12px;
            font-family: inherit;
            outline: none;
            transition: border-color 0.15s;
        }

        .zalo-field-input:focus {
            border-color: #0068ff;
        }

        .sender-saved-badge {
            display: none;
            align-items: center;
            justify-content: space-between;
            font-size: 11.5px;
            color: #334155;
            font-weight: 600;
        }

        .btn-change-sender {
            background: none;
            border: none;
            color: #0068ff;
            font-size: 11.5px;
            font-weight: 750;
            cursor: pointer;
            padding: 0;
            text-decoration: underline;
        }

        /* Vùng nhập tin nhắn Zalo */
        .zalo-input-area {
            background: #ffffff;
            padding: 9px 12px;
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .zalo-textarea {
            flex: 1;
            padding: 9px 12px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            border-radius: 12px;
            font-size: 13.5px;
            font-family: inherit;
            color: #0f172a;
            outline: none;
            resize: none;
            height: 40px;
            line-height: 1.4;
            transition: border-color 0.15s;
        }

        .zalo-textarea:focus {
            border-color: #0068ff;
            background: #ffffff;
        }

        .zalo-btn-send {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #0068ff;
            border: none;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.15s, transform 0.1s;
            flex-shrink: 0;
        }

        .zalo-btn-send:hover {
            background: #0056d6;
            transform: scale(1.05);
        }

        .zalo-btn-send:active {
            transform: scale(0.95);
        }

        /* ====== TỐI ƯU RESPONSIVE & KHÔNG CUỘN TRANG ====== */
        @media (max-height: 720px) {
            body {
                padding: 6px 10px;
            }
            .glass-card {
                padding: 16px 22px 14px 22px;
                border-radius: 18px;
            }
            .badge-pill {
                display: none;
            }
            .card-header {
                margin-bottom: 8px;
            }
            .brand-name {
                font-size: 23px;
                margin-bottom: 2px;
            }
            .brand-desc {
                font-size: 11px;
            }
            .form-group {
                margin-bottom: 7px;
            }
            .form-label {
                font-size: 11.5px;
                margin-bottom: 3px;
            }
            .input-control {
                padding: 7px 12px 7px 34px;
                font-size: 13px;
                border-radius: 10px;
            }
            .btn-submit {
                padding: 8px 16px;
                font-size: 13px;
                border-radius: 10px;
            }
            .social-divider, .card-divider {
                margin: 7px 0 6px 0;
            }
            .btn-google-login {
                padding: 7.5px 14px;
                font-size: 12.5px;
                border-radius: 10px;
            }
            .btn-pricing-cta {
                padding: 6px 10px;
                border-radius: 10px;
            }
            .pricing-cta-icon {
                width: 26px;
                height: 26px;
                font-size: 13px;
            }
            .card-footer {
                margin-top: 6px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 12px 10px;
                overflow-y: auto;
            }
            .login-wrapper {
                max-width: 100%;
            }
            .glass-card {
                padding: 18px 18px 14px 18px;
                border-radius: 20px;
            }
            .brand-name {
                font-size: 24px;
            }
            .floating-chat-btn {
                bottom: 12px;
                right: 12px;
                padding: 8px 14px;
                font-size: 12px;
            }
            .chat-popover {
                bottom: 64px;
                right: 8px;
                left: 8px;
                width: auto;
                height: 440px;
            }
        }
    </style>
</head>
<body>

    <!-- Cinematic Video Background (bg-hero.mp4) -->
    <div class="video-bg-container">
        <video id="loginBgVideo" autoplay muted loop playsinline preload="auto" class="video-bg">
            <source src="{{ asset('images/bg-hero.mp4') }}" type="video/mp4">
        </video>
        <div class="video-overlay"></div>
    </div>
    <script>
        // Đợi video phát được rồi mới hiện; lỗi tải thì mới dùng ảnh nền dự phòng
        (function () {
            const video = document.getElementById('loginBgVideo');
            if (!video) return;
            const box = video.parentElement;
            const show = () => video.classList.add('is-ready');
            video.addEventListener('playing', show, { once: true });
            video.addEventListener('error', () => box.classList.add('video-failed'), true);
            const p = video.play();
            if (p && p.catch) p.catch(() => box.classList.add('video-failed'));
            // Mạng quá chậm (hơn 12 giây vẫn chưa phát): hiện ảnh dự phòng để khỏi trống
            setTimeout(() => { if (!video.classList.contains('is-ready')) box.classList.add('video-failed'); }, 12000);
            video.addEventListener('playing', () => box.classList.remove('video-failed'));
        })();
    </script>

    <!-- Ultra-Clean Glassmorphism Card -->
    <div class="login-wrapper">
        <main class="glass-card">
            
            <!-- Header -->
            <div class="card-header">
                <div class="badge-pill">
                    <span class="sparkle">🌸</span>
                    <span>IC3 GS6 & Spark Quest</span>
                </div>
                <h1 class="brand-name">IC3 <span>QUEST</span></h1>
                <p class="brand-desc">Đăng nhập hệ thống học tập & luyện thi trực tuyến ✨</p>
            </div>

            @error('login')
                <div class="alert-error">
                    <span>⚠️</span>
                    <span>{{ $message }}</span>
                </div>
            @enderror

            <!-- Login Form -->
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
                        <input id="loginInput" class="input-control" name="login" value="{{ old('login') }}" required autofocus placeholder="Nhập tên đăng nhập hoặc email">
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
                        <input id="passwordInput" class="input-control" name="password" type="password" required placeholder="Nhập mật khẩu của bạn">
                        <button type="button" class="btn-toggle-eye" onclick="togglePassword()" title="Hiện/ẩn mật khẩu">
                            <span id="eyeIcon">👁️</span>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <span>ĐĂNG NHẬP NGAY</span>
                    <span>🚀</span>
                </button>
            </form>

            <div class="social-divider">
                <span>HOẶC TIẾP TỤC VỚI</span>
            </div>

            <!-- 🔴 Nút Đăng Nhập Bằng Google 3D Tactile -->
            <a href="{{ route('auth.google') }}" class="btn-google-login" id="btnGoogleLogin" title="Đăng nhập an toàn & nhanh chóng với tài khoản Google">
                <svg class="google-icon" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.66-5.17 3.66-9.17z"/>
                    <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.25v3.15C3.26 21.36 7.34 24 12 24z"/>
                    <path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.25C.45 8.18 0 9.99 0 12s.45 3.82 1.25 5.42l4.03-3.15z"/>
                    <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.34 0 3.26 2.64 1.25 6.58l4.03 3.15c.95-2.83 3.6-4.98 6.72-4.98z"/>
                </svg>
                <span>Đăng nhập bằng tài khoản Google</span>
            </a>

            <div class="card-divider">
                <span>Dành cho Giáo viên & Trường học</span>
            </div>

            <!-- 💎 Siêu Nút Xem Gói & Mua Bản Quyền -->
            <a href="{{ route('pricing.index') }}" class="btn-pricing-cta">
                <div class="pricing-cta-left">
                    <div class="pricing-cta-icon">💎</div>
                    <div class="pricing-cta-text">
                        <span class="pricing-cta-title">Xem Các Gói & Thuê Bản Quyền</span>
                        <span class="pricing-cta-desc">Cấp lớp học, đề thi & chấm điểm tự động</span>
                    </div>
                </div>
                <span class="pricing-cta-arrow">→</span>
            </a>

            <!-- Footer -->
            <div class="card-footer">
                <div class="contact-admin-box">
                    <span>Cần hỗ trợ hoặc chưa có tài khoản?</span>
                    <button type="button" class="btn-contact-trigger" onclick="toggleLiveChat()">
                        <span>Nhắn Admin ngay 💬</span>
                    </button>
                </div>
                <div class="copyright-text">
                    Luyện thi IC3 GS6 & Quản trị MOS © 2026
                </div>
            </div>
        </main>
    </div>

    <!-- 💬 NÚT NỔI CHAT ZALO STYLE -->
    <button type="button" class="floating-chat-btn" onclick="toggleLiveChat()" title="Nhắn tin với Ban Quản Trị">
        <span class="online-dot"></span>
        <span>💬 Hỗ Trợ Zalo / Chat</span>
    </button>

    <!-- 💬 CỬA SỔ CHAT GIAO DIỆN CHUẨN ZALO (ĐÃ LƯU LỊCH SỬ & KHÔNG VỠ) -->
    @include('partials.floating-panel')
    <div id="liveChatPopover" class="chat-popover">
        <div class="sc-grip" aria-hidden="true"></div>
        <!-- Zalo Header -->
        <div class="zalo-header">
            <div class="zalo-header-left">
                <div class="zalo-avatar">💬</div>
                <div class="zalo-header-info">
                    <h4 id="chatHeaderTitle">Hỗ Trợ IC3</h4>
                    <span><span class="online-dot" style="width:7px; height:7px;"></span> Đang hoạt động</span>
                </div>
            </div>
            <div class="zalo-header-actions">
                <a href="tel:0345151438" class="zalo-btn-action" title="Gọi Hotline (0345.151.438)">📞 Gọi</a>
                <button type="button" class="zalo-btn-action" onclick="toggleLiveChat()" style="width:28px; padding:0;" title="Đóng">✕</button>
            </div>
        </div>

        <!-- Hai kênh tách biệt: Trợ lý AI tự động và Ban Quản Trị trả lời trực tiếp -->
        <div class="chat-tabs" id="chatTabs" role="tablist">
            <button type="button" class="chat-tab ai" id="chatTabAi" role="tab" onclick="switchChatTab('ai')">🤖 Trợ lý AI</button>
            <button type="button" class="chat-tab" id="chatTabAdmin" role="tab" onclick="switchChatTab('admin')">👨‍💼 Ban Quản Trị</button>
        </div>

        <!-- Khung Cuộc Trò Chuyện (Zalo Chat Body) -->
        <div class="zalo-chat-body" id="chatMessagesBody">
            <div class="zalo-date-badge">Hôm nay</div>
            <!-- Nội dung tin nhắn sẽ được render tự động từ localStorage -->
        </div>

        <!-- Thanh Nhập Thông Tin Người Gửi (Khách vãng lai) -->
        <div class="zalo-sender-bar" id="chatSenderBar">
            <div class="sender-form-box" id="senderFormBox">
                <div class="sender-form-header">
                    <span>💡 Điền thông tin để Admin liên hệ lại qua Zalo/SĐT:</span>
                </div>
                <div class="sender-inputs-row">
                    <input type="text" id="chatSenderName" class="zalo-field-input" placeholder="Tên bạn (Thầy Hùng, Cô Mai...)" style="flex: 1.1;">
                    <input type="text" id="chatSenderContact" class="zalo-field-input" placeholder="Số Zalo hoặc SĐT..." style="flex: 1;">
                </div>
            </div>

            <div class="sender-saved-badge" id="senderSavedBadge">
                <span>👤 Người gửi: <b id="displaySenderText" style="color:#0068ff;">Khách</b></span>
                <button type="button" class="btn-change-sender" onclick="editSenderInfo()">Đổi thông tin</button>
            </div>
        </div>

        <!-- Thanh Nhập Tin Nhắn & Nút Gửi Zalo -->
        <div class="zalo-input-area">
            <textarea id="chatSenderMsg" class="zalo-textarea" placeholder="Nhập tin nhắn tới Admin..." rows="1" onkeydown="handleChatKey(event)"></textarea>
            <button type="button" id="btnSendChat" class="zalo-btn-send" onclick="sendChatMsg()" title="Gửi tin nhắn (Enter)">
                ➤
            </button>
        </div>
    </div>

    <script>
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

        // Ô nhập ở bước OTP/mật khẩu được che bằng dấu chấm (không lộ trên màn hình)
        (function () {
            const style = document.createElement('style');
            style.textContent = `
                .bot-secret { -webkit-text-security: disc; }
                .chat-tabs { display: flex; gap: 6px; padding: 8px 12px; background: #fff; border-bottom: 1px solid #e5e7eb; }
                .chat-tab { flex: 1; border: none; border-radius: 10px; padding: 8px 6px; font-family: inherit; font-weight: 800; font-size: 12.5px; cursor: pointer; background: #f1f5f9; color: #475569; }
                .chat-tab.active { background: linear-gradient(135deg, #0068ff, #38bdf8); color: #fff; box-shadow: 0 3px 0 #0557c7; }
                .chat-tab.active.ai { background: linear-gradient(135deg, #7c3aed, #a855f7); box-shadow: 0 3px 0 #5b21b6; }
                .zalo-notice { align-self: center; max-width: 90%; font-size: 12px; color: #64748b; background: #e2e8f0; border-radius: 10px; padding: 6px 10px; text-align: center; margin: 6px auto; }
                .chat-action { display: inline-block; margin-top: 8px; padding: 7px 12px; border-radius: 10px; font-size: 12.5px; font-weight: 800; text-decoration: none; color: #fff; background: linear-gradient(135deg, #7c3aed, #a855f7); box-shadow: 0 3px 0 #5b21b6; }
                .chat-action:hover { transform: translateY(-1px); }
            `;
            document.head.appendChild(style);
        })();

        // Hai kênh tách biệt: 'ai' (Trợ lý AI tự động) và 'admin' (Ban Quản Trị trả lời trực tiếp).
        // Hội thoại dùng chung một bản ghi trên máy chủ, giao diện chỉ lọc hiển thị theo kênh.
        // Khách vãng lai chỉ chat với Trợ lý AI và không được lưu hội thoại trên máy chủ.
        // Thành viên đã đăng nhập có thêm kênh Ban Quản Trị, lịch sử được lưu để đối chiếu.
        const CHAT_IS_MEMBER = @json(auth()->check());
        let chatTab = CHAT_IS_MEMBER && localStorage.getItem('mos_chat_tab') === 'admin' ? 'admin' : 'ai';
        // Bước khôi phục mật khẩu đang yêu cầu OTP hoặc mật khẩu mới (máy chủ quyết định, trình duyệt chỉ ghi nhớ)
        let botSecretMode = localStorage.getItem('mos_bot_secret') === '1';
        let activeChatSupportId = localStorage.getItem('mos_active_support_id') || null;
        let adminPollingTimer = null;

        const AI_GREETING = 'Xin chào! 👋 Mình là <b>Trợ lý AI</b> của IC3 Adventure.<br>Mình trả lời về <b>đăng nhập, gói bản quyền và tài liệu thi IC3</b>.<br>Bạn gõ <b>"quên mật khẩu"</b> nếu cần lấy lại mật khẩu nhé!'
            + (CHAT_IS_MEMBER ? '' : '<br><small>💡 Chat với Ban Quản Trị cần đăng nhập tài khoản. Nội dung chat với Trợ lý AI không được lưu lại.</small>');
        const ADMIN_GREETING = 'Xin chào Thầy/Cô và các bạn! 👋<br>Bạn để lại lời nhắn kèm SĐT/Zalo bên dưới, Ban Quản Trị sẽ phản hồi bạn ngay tại đây nhé!<br><small>🔒 Lịch sử chat được lưu 12 tháng để đối chiếu khi cần.</small>';

        function toggleLiveChat() {
            const popover = document.getElementById('liveChatPopover');
            popover.classList.toggle('open');
            if (popover.classList.contains('open')) {
                updateTabUI();
                syncSenderUI();
                renderChatHistory();
                const msgInput = document.getElementById('chatSenderMsg');
                if (msgInput) msgInput.focus();
                scrollChatToBottom();
            }
        }

        function switchChatTab(tab) {
            chatTab = tab === 'admin' && CHAT_IS_MEMBER ? 'admin' : 'ai';
            localStorage.setItem('mos_chat_tab', chatTab);
            updateTabUI();
            renderChatHistory();
        }

        // Cập nhật nút tab, tiêu đề, gợi ý nhập liệu. Chỉ kênh Ban Quản Trị cần thông tin SĐT người gửi.
        function updateTabUI() {
            const aiBtn = document.getElementById('chatTabAi');
            const adminBtn = document.getElementById('chatTabAdmin');
            const msgInput = document.getElementById('chatSenderMsg');
            const senderBar = document.getElementById('chatSenderBar');
            const title = document.getElementById('chatHeaderTitle');

            if (aiBtn) aiBtn.classList.toggle('active', chatTab === 'ai');
            // Khách vãng lai chỉ có một kênh (Trợ lý AI) nên ẩn thanh chọn tab
            const tabsRow = document.getElementById('chatTabs');
            if (tabsRow) tabsRow.style.display = CHAT_IS_MEMBER ? '' : 'none';
            if (adminBtn) {
                adminBtn.style.display = CHAT_IS_MEMBER ? '' : 'none';
                adminBtn.classList.toggle('active', chatTab === 'admin');
            }
            if (title) title.textContent = chatTab === 'ai' ? 'Trợ lý IC3 (AI)' : 'Hỗ Trợ Ban Quản Trị';
            if (senderBar) senderBar.style.display = chatTab === 'admin' ? 'flex' : 'none';
            if (msgInput) {
                msgInput.placeholder = chatTab === 'ai' ? 'Hỏi Trợ lý AI về đăng nhập, gói, tài liệu...' : 'Nhắn tin tới Ban Quản Trị...';
                msgInput.classList.toggle('bot-secret', chatTab === 'ai' && botSecretMode);
            }
        }

        function scrollChatToBottom() {
            const body = document.getElementById('chatMessagesBody');
            if (body) {
                setTimeout(() => { body.scrollTop = body.scrollHeight; }, 50);
            }
        }

        function handleChatKey(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendChatMsg();
            }
        }

        // Số hợp lệ: 10 chữ số, bắt đầu bằng 0 hoặc +84 (khớp với kiểm tra phía máy chủ)
        function isValidPhone(value) {
            const digits = String(value || '').replace(/[\s.\-()]/g, '');
            return /^(?:\+?84|0)\d{9}$/.test(digits);
        }

        // Quản lý thông tin người gửi (chỉ dùng cho kênh Ban Quản Trị)
        function syncSenderUI() {
            const savedName = localStorage.getItem('mos_chat_name') || '';
            let savedContact = localStorage.getItem('mos_chat_contact') || '';
            // Xóa số đã lưu nhưng không hợp lệ (ví dụ "222") để khách nhập lại
            if (savedContact && !isValidPhone(savedContact)) {
                localStorage.removeItem('mos_chat_contact');
                savedContact = '';
            }

            const nameInput = document.getElementById('chatSenderName');
            const contactInput = document.getElementById('chatSenderContact');
            const formBox = document.getElementById('senderFormBox');
            const badgeBox = document.getElementById('senderSavedBadge');
            const displayText = document.getElementById('displaySenderText');

            if (savedName || savedContact) {
                nameInput.value = savedName;
                contactInput.value = savedContact;
                displayText.textContent = `${savedName || 'Khách'} ${savedContact ? '• ' + savedContact : ''}`;
                formBox.style.display = 'none';
                badgeBox.style.display = 'flex';
            } else {
                formBox.style.display = 'flex';
                badgeBox.style.display = 'none';
            }
        }

        function editSenderInfo() {
            document.getElementById('senderFormBox').style.display = 'flex';
            document.getElementById('senderSavedBadge').style.display = 'none';
            document.getElementById('chatSenderName').focus();
        }

        // =========================================================================
        // 💾 LỊCH SỬ CHAT TRÊN TRÌNH DUYỆT (LOCALSTORAGE)
        // =========================================================================
        function getChatHistory() {
            try {
                return JSON.parse(localStorage.getItem('mos_chat_messages')) || [];
            } catch (e) {
                return [];
            }
        }

        // Chỉ nhận đường dẫn ảnh do máy chủ lưu (/storage/...) để tránh chèn địa chỉ lạ vào thẻ ảnh
        function safeImageUrl(url) {
            return typeof url === 'string' && url.startsWith('/storage/') && !url.includes('..') ? url : '';
        }

        // Kênh của một tin: tin cũ không có kênh thì bot thuộc kênh AI, còn lại thuộc kênh Ban Quản Trị
        function channelOf(item) {
            if (item.channel) return item.channel;
            return item.sender === 'bot' ? 'ai' : 'admin';
        }

        function escapeHtml(text) {
            return String(text).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }

        // Chữ đậm **...** từ AI, và xuống dòng giữ nguyên
        function formatText(text) {
            return escapeHtml(text).replace(/\*\*(.+?)\*\*/g, '<b>$1</b>');
        }

        // Nút mở trang/làm bài do máy chủ tạo. Chỉ nhận đường dẫn cùng trang web để tránh chuyển sang trang lạ.
        function actionButton(action) {
            if (!action || typeof action.url !== 'string' || typeof action.label !== 'string') return '';
            try {
                const url = new URL(action.url, window.location.origin);
                if (url.origin !== window.location.origin) return '';
                return `<a class="chat-action" href="${url.pathname}${url.search}${url.hash}">${escapeHtml(action.label)} →</a>`;
            } catch (e) {
                return '';
            }
        }

        function saveMessageToHistory(sender, text, time, image, channel, action) {
            const history = getChatHistory();
            const entry = { sender, text, time, channel: channel || (sender === 'bot' ? 'ai' : 'admin') };
            if (safeImageUrl(image)) entry.image = image;
            if (action && action.label && action.url) entry.action = { label: action.label, url: action.url };
            history.push(entry);
            localStorage.setItem('mos_chat_messages', JSON.stringify(history));
        }

        // Vẽ lại khung chat, chỉ hiện tin thuộc kênh đang chọn
        function renderChatHistory() {
            const body = document.getElementById('chatMessagesBody');
            if (!body) return;

            const isAi = chatTab === 'ai';
            const label = isAi ? '🤖 Trợ lý IC3 (AI)' : '👨‍💼 Ban Quản Trị (Admin)';
            const greeting = isAi ? AI_GREETING : ADMIN_GREETING;
            body.innerHTML = `
                <div class="zalo-date-badge">Hôm nay</div>
                <div class="zalo-bubble zalo-bubble-left">
                    <div style="font-weight:800; font-size:12px; color:${isAi ? '#7c3aed' : '#0068ff'}; margin-bottom:3px;">${label}</div>
                    ${greeting}
                    <div class="bubble-time">Vừa xong</div>
                </div>
            `;

            getChatHistory().filter(item => channelOf(item) === chatTab).forEach(item => {
                const rawText = String(item.text || '').trim();
                const imageUrl = safeImageUrl(item.image);
                if ((!rawText || rawText === 'undefined') && !imageUrl) return;

                const bubble = document.createElement('div');
                if (item.sender === 'notice') {
                    bubble.className = 'zalo-notice';
                    bubble.textContent = rawText;
                } else if (item.sender === 'user') {
                    bubble.className = 'zalo-bubble zalo-bubble-right';
                    bubble.innerHTML = `
                        <div>${escapeHtml(rawText)}</div>
                        <div class="bubble-time">
                            <span>${item.time || ''}</span>
                            <span>✓✓ Đã gửi</span>
                        </div>
                    `;
                } else {
                    const isBot = item.sender === 'bot';
                    const imageHtml = imageUrl ? `<a href="${imageUrl}" target="_blank"><img src="${imageUrl}" alt="Ảnh từ Ban Quản Trị" style="max-width:100%; border-radius:10px; display:block; margin:4px 0;"></a>` : '';
                    bubble.className = 'zalo-bubble zalo-bubble-left';
                    bubble.innerHTML = `
                        <div style="font-weight:800; font-size:12px; color:${isBot ? '#7c3aed' : '#0068ff'}; margin-bottom:3px;">
                            ${isBot ? '🤖 Trợ lý IC3 (AI)' : '👨‍💼 Ban Quản Trị (Admin)'}
                        </div>
                        ${imageHtml}
                        ${rawText ? `<div style="white-space:pre-line;">${formatText(rawText)}</div>` : ''}
                        ${actionButton(item.action)}
                        <div class="bubble-time">${item.time || ''}</div>
                    `;
                }
                body.appendChild(bubble);
            });

            scrollChatToBottom();
        }

        function clearChatHistory() {
            if (!confirm('Bạn có chắc chắn muốn xóa toàn bộ lịch sử đoạn chat này để làm mới không?')) return;
            localStorage.removeItem('mos_chat_messages');
            localStorage.removeItem('mos_active_support_id');
            activeChatSupportId = null;
            renderChatHistory();
        }

        // =========================================================================
        // 🚀 GỬI TIN NHẮN (MỖI KÊNH CÓ LUỒNG RIÊNG, CHUNG MỘT HỘI THOẠI)
        // =========================================================================
        function sendChatMsg() {
            const nameInput = document.getElementById('chatSenderName');
            const contactInput = document.getElementById('chatSenderContact');
            const msgInput = document.getElementById('chatSenderMsg');
            const btn = document.getElementById('btnSendChat');

            const name = nameInput.value.trim() || 'Khách vãng lai';
            const contact = contactInput.value.trim();
            const message = msgInput.value.trim();
            const sentChannel = chatTab;

            if (!message) {
                msgInput.focus();
                return;
            }

            // Kênh Ban Quản Trị cần SĐT/Zalo hợp lệ để Admin liên hệ lại. Kênh AI không cần.
            if (sentChannel === 'admin' && !isValidPhone(contact)) {
                editSenderInfo();
                contactInput.focus();
                alert('Vui lòng nhập đúng số điện thoại hoặc Zalo (10 số, ví dụ 0912345678).');
                return;
            }

            if (sentChannel === 'admin') {
                if (nameInput.value.trim()) localStorage.setItem('mos_chat_name', nameInput.value.trim());
                if (contactInput.value.trim()) localStorage.setItem('mos_chat_contact', contactInput.value.trim());
                syncSenderUI();
            }

            const now = new Date();
            const timeStr = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;

            // Khách vãng lai: gửi kèm lịch sử AI đang có trong trình duyệt (máy chủ không lưu), bỏ các đoạn đã ẩn
            const aiHistory = getChatHistory()
                .filter(item => channelOf(item) === 'ai' && (item.sender === 'user' || item.sender === 'bot'))
                .filter(item => !String(item.text || '').startsWith('🔒'))
                .slice(-20)
                .map(item => ({ sender: item.sender, text: String(item.text || '') }));

            // Bước OTP/mật khẩu: không lưu nội dung thật vào trình duyệt
            const shownText = sentChannel === 'ai' && botSecretMode ? '🔒 Đã ẩn để bảo mật' : message;
            saveMessageToHistory('user', shownText, timeStr, null, sentChannel);
            renderChatHistory();

            msgInput.value = '';
            btn.disabled = true;
            btn.textContent = '⏳';

            const payload = {
                name: name,
                contact: contact || '',
                phone: contact || null,
                message: message,
                parent_id: activeChatSupportId || null,
                channel: sentChannel
            };
            if (!CHAT_IS_MEMBER) {
                payload.history = aiHistory;
            }

            fetch('{{ route("support.message.send") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            })
            .then(async res => ({ ok: res.ok, data: await res.json() }))
            .then(({ ok, data }) => {
                btn.disabled = false;
                btn.textContent = '➤';

                if (!ok) {
                    // Lỗi nhập liệu (ví dụ thiếu SĐT, đang khôi phục ở tab AI): hiện thông báo trong kênh vừa gửi
                    saveMessageToHistory('notice', data.message || 'Có lỗi xảy ra, bạn thử lại nhé.', timeStr, null, sentChannel);
                    renderChatHistory();
                    if (/điện thoại/i.test(data.message || '')) editSenderInfo();
                    msgInput.focus();
                    return;
                }

                if (data.message_id) {
                    activeChatSupportId = data.message_id;
                    localStorage.setItem('mos_active_support_id', activeChatSupportId);
                    if (!adminPollingTimer) {
                        adminPollingTimer = setInterval(pollAdminReply, 2500);
                    }
                }

                botSecretMode = !!data.secret_next;
                localStorage.setItem('mos_bot_secret', botSecretMode ? '1' : '0');
                localStorage.setItem('mos_bot_flow', data.flow_active ? '1' : '0');

                // Kênh Ban Quản Trị: nếu Admin chưa online thì báo ngay, và gợi ý kênh AI
                if (sentChannel === 'admin' && data.admin_online === false) {
                    saveMessageToHistory('notice', 'Ban Quản Trị hiện chưa trực tuyến. Tin nhắn đã được ghi nhận, Admin sẽ trả lời sớm nhất có thể. Bạn cũng có thể hỏi nhanh ở tab 🤖 Trợ lý AI.', data.time || timeStr, null, 'admin');
                }

                // Kênh AI: câu trả lời của trợ lý
                const botReplies = Array.isArray(data.bot_replies) ? data.bot_replies : [];
                botReplies.forEach((text, index) => {
                    const isLast = index === botReplies.length - 1;
                    saveMessageToHistory('bot', text, data.time || timeStr, null, 'ai', isLast ? data.bot_action : null);
                });

                updateTabUI();
                renderChatHistory();
                msgInput.focus();
            })
            .catch(() => {
                btn.disabled = false;
                btn.textContent = '➤';
            });
        }

        // =========================================================================
        // ⚡ NHẬN PHẢN HỒI TỪ ADMIN (KÊNH BAN QUẢN TRỊ) VÀ TRỢ LÝ AI
        // =========================================================================
        async function pollAdminReply() {
            if (!activeChatSupportId) return;
            try {
                const res = await fetch(`/ho-tro/tin-nhan/kiem-tra?id=${activeChatSupportId}`);
                if (!res.ok) return;
                const data = await res.json();
                if (!data.ok || !Array.isArray(data.conversation_history)) return;

                // Chỉ đếm tin của Admin và của bot (tin của khách và thông báo tạm không tính)
                const isReply = turn => turn.sender === 'admin' || turn.sender === 'bot';
                const serverReplies = data.conversation_history.filter(isReply);
                const knownCount = getChatHistory().filter(isReply).length;

                if (serverReplies.length > knownCount) {
                    serverReplies.slice(knownCount).forEach(turn => {
                        saveMessageToHistory(
                            turn.sender,
                            String(turn.text || '').trim(),
                            turn.time || data.replied_at || 'Vừa xong',
                            turn.image,
                            turn.channel || (turn.sender === 'bot' ? 'ai' : 'admin'),
                            turn.action
                        );
                    });
                    renderChatHistory();
                }
            } catch (e) {}
        }

        if (activeChatSupportId) {
            adminPollingTimer = setInterval(pollAdminReply, 2500);
        }

        // Kéo và đổi kích thước khung chat (nhớ vị trí trên trình duyệt này)
        makeFloatingPanel(document.getElementById('liveChatPopover'), { head: '.zalo-header', grip: '.sc-grip', key: 'mos_chat_popover_layout', minW: 300, minH: 360 });

        // Khởi tạo
        syncSenderUI();
        updateTabUI();
        renderChatHistory();
    </script>
</body>
</html>
