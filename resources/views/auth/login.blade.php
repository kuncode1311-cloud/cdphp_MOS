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
        }

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
        <video autoplay muted loop playsinline preload="auto" poster="{{ asset('images/ic3-login-hero.jpg') }}" class="video-bg">
            <source src="{{ asset('images/bg-hero.mp4') }}" type="video/mp4">
        </video>
        <div class="video-overlay"></div>
    </div>

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
    <div id="liveChatPopover" class="chat-popover">
        <!-- Zalo Header -->
        <div class="zalo-header">
            <div class="zalo-header-left">
                <div class="zalo-avatar">💬</div>
                <div class="zalo-header-info">
                    <h4>Hỗ Trợ Ban Quản Trị</h4>
                    <span><span class="online-dot" style="width:7px; height:7px;"></span> Đang hoạt động</span>
                </div>
            </div>
            <div class="zalo-header-actions">
                <a href="tel:0345151438" class="zalo-btn-action" title="Gọi Hotline (0345.151.438)">📞 Gọi</a>
                <button type="button" class="zalo-btn-action" onclick="toggleLiveChat()" style="width:28px; padding:0;" title="Đóng">✕</button>
            </div>
        </div>

        <!-- Khung Cuộc Trò Chuyện (Zalo Chat Body) -->
        <div class="zalo-chat-body" id="chatMessagesBody">
            <div class="zalo-date-badge">Hôm nay</div>
            <!-- Nội dung tin nhắn sẽ được render tự động từ localStorage -->
        </div>

        <!-- Thanh Nhập Thông Tin Người Gửi (Khách vãng lai) -->
        <div class="zalo-sender-bar">
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

        function toggleLiveChat() {
            const popover = document.getElementById('liveChatPopover');
            popover.classList.toggle('open');
            if (popover.classList.contains('open')) {
                syncSenderUI();
                renderChatHistory();
                const msgInput = document.getElementById('chatSenderMsg');
                if (msgInput) msgInput.focus();
                scrollChatToBottom();
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

        // Quản lý thông tin người gửi
        function syncSenderUI() {
            const savedName = localStorage.getItem('mos_chat_name') || '';
            const savedContact = localStorage.getItem('mos_chat_contact') || '';

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
        // 💾 QUẢN LÝ LỊCH SỬ CHAT CỦA PHIÊN (LOCALSTORAGE)
        // =========================================================================
        function getChatHistory() {
            try {
                return JSON.parse(localStorage.getItem('mos_chat_messages')) || [];
            } catch (e) {
                return [];
            }
        }

        function saveMessageToHistory(sender, text, time) {
            const history = getChatHistory();
            history.push({ sender, text, time });
            localStorage.setItem('mos_chat_messages', JSON.stringify(history));
        }

        function renderChatHistory() {
            const body = document.getElementById('chatMessagesBody');
            if (!body) return;

            const history = getChatHistory();
            
            // Xóa cũ và vẽ lại
            body.innerHTML = `
                <div class="zalo-date-badge">Hôm nay</div>
                <div class="zalo-bubble zalo-bubble-left">
                    Xin chào Thầy/Cô và các bạn! 👋<br>
                    Bạn cần hỗ trợ về <b>tài khoản đăng nhập, thuê gói bản quyền hay tài liệu thi IC3</b>?<br>
                    Hãy để lại lời nhắn kèm SĐT/Zalo bên dưới, Ban Quản Trị sẽ phản hồi bạn ngay tại đây nhé!
                    <div class="bubble-time">Vừa xong</div>
                </div>
            `;

            history.forEach(item => {
                const rawText = String(item.text || '').trim();
                if (!rawText || rawText === 'undefined') return;
                const safeText = rawText.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");

                const bubble = document.createElement('div');
                if (item.sender === 'user') {
                    bubble.className = 'zalo-bubble zalo-bubble-right';
                    bubble.innerHTML = `
                        <div>${safeText}</div>
                        <div class="bubble-time">
                            <span>${item.time || ''}</span>
                            <span>✓✓ Đã gửi</span>
                        </div>
                    `;
                } else {
                    bubble.className = 'zalo-bubble zalo-bubble-left';
                    bubble.innerHTML = `
                        <div style="font-weight:800; font-size:12px; color:#0068ff; margin-bottom:3px;">
                            👨‍💼 Ban Quản Trị (Admin)
                        </div>
                        <div style="white-space:pre-line;">${safeText}</div>
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
        // 🚀 GỬI TIN NHẮN (GỘP PHIÊN CHAT KHÔNG BỊ TÁCH RỜI)
        // =========================================================================
        let activeChatSupportId = localStorage.getItem('mos_active_support_id') || null;
        let adminPollingTimer = null;

        function sendChatMsg() {
            const nameInput = document.getElementById('chatSenderName');
            const contactInput = document.getElementById('chatSenderContact');
            const msgInput = document.getElementById('chatSenderMsg');
            const btn = document.getElementById('btnSendChat');
            const body = document.getElementById('chatMessagesBody');

            const name = nameInput.value.trim() || 'Khách vãng lai';
            const contact = contactInput.value.trim();
            const message = msgInput.value.trim();

            if (!message) {
                msgInput.focus();
                return;
            }

            // Lưu thông tin người gửi
            if (nameInput.value.trim()) localStorage.setItem('mos_chat_name', nameInput.value.trim());
            if (contactInput.value.trim()) localStorage.setItem('mos_chat_contact', contactInput.value.trim());
            syncSenderUI();

            // Hiển thị tin nhắn người dùng ngay lập tức
            const now = new Date();
            const timeStr = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;
            
            saveMessageToHistory('user', message, timeStr);

            const userBubble = document.createElement('div');
            userBubble.className = 'zalo-bubble zalo-bubble-right';
            userBubble.innerHTML = `
                <div>${message.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")}</div>
                <div class="bubble-time">
                    <span>${timeStr}</span>
                    <span>✓✓ Đã gửi</span>
                </div>
            `;
            body.appendChild(userBubble);
            scrollChatToBottom();

            msgInput.value = '';
            btn.disabled = true;
            btn.textContent = '⏳';

            fetch('{{ route("support.message.send") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    name: name,
                    contact: contact || '',
                    phone: contact || null,
                    message: message,
                    parent_id: activeChatSupportId || null
                })
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.textContent = '➤';

                if (data.message_id) {
                    activeChatSupportId = data.message_id;
                    localStorage.setItem('mos_active_support_id', activeChatSupportId);
                    if (!adminPollingTimer) {
                        adminPollingTimer = setInterval(pollAdminReply, 2500);
                    }
                }

                msgInput.focus();
            })
            .catch(err => {
                btn.disabled = false;
                btn.textContent = '➤';
            });
        }

        // =========================================================================
        // ⚡ REAL-TIME POLLING: NHẬN PHẢN HỒI TỪ ADMIN
        // =========================================================================
        let lastReceivedAdminReply = null;

        async function pollAdminReply() {
            if (!activeChatSupportId) return;
            try {
                const res = await fetch(`/ho-tro/tin-nhan/kiem-tra?id=${activeChatSupportId}`);
                if (!res.ok) return;
                const data = await res.json();
                if (data.ok && data.admin_reply && data.admin_reply !== lastReceivedAdminReply) {
                    lastReceivedAdminReply = data.admin_reply;
                    
                    const replyTime = data.replied_at || 'Vừa xong';
                    saveMessageToHistory('admin', data.admin_reply, replyTime);

                    const body = document.getElementById('chatMessagesBody');
                    const adminBubble = document.createElement('div');
                    adminBubble.className = 'zalo-bubble zalo-bubble-left';
                    adminBubble.innerHTML = `
                        <div style="font-weight:800; font-size:12px; color:#0068ff; margin-bottom:3px;">
                            👨‍💼 Ban Quản Trị (Admin)
                        </div>
                        <div style="white-space:pre-line;">${data.admin_reply.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")}</div>
                        <div class="bubble-time">${replyTime}</div>
                    `;
                    body.appendChild(adminBubble);
                    scrollChatToBottom();
                }
            } catch(e) {}
        }

        if (activeChatSupportId) {
            adminPollingTimer = setInterval(pollAdminReply, 2500);
        }

        // Khởi tạo
        syncSenderUI();
        renderChatHistory();
    </script>
</body>
</html>
