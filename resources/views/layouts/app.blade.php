{{-- Khung dùng chung: menu, thanh trên và tài nguyên CSS/JS. Nội dung từng trang được chèn ở yield. --}}
<!doctype html>
<html lang="vi" translate="no" class="notranslate">
<head>
    @include('partials.page-gate')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="google" content="notranslate">
    <meta name="theme-color" content="#1070b8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'IC3 Adventure — Game Khám Phá Kỹ Năng Số')</title>
    <meta name="description" content="Nền tảng luyện tập kỹ năng số IC3 GS6 dành cho học sinh tiểu học.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@600;700;800&family=Nunito:wght@600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/css/game-theme.css', 'resources/js/app.js'])
    <style>
        #magic-canvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            pointer-events: none;
            z-index: 99999;
        }
        /* Exact Pixel-Perfect Adventure Topbar */
        .vip-topbar {
            position: sticky;
            top: 0;
            z-index: 100;
            height: 86px;
            padding: 0 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            background: linear-gradient(180deg, #1072ba 0%, #0d5c96 100%);
            border-bottom: 4px solid #48c3f7;
            box-shadow: inset 0 -4px 0 #073a61, 0 10px 25px rgba(6, 38, 68, 0.45);
            color: #fff;
            box-sizing: border-box;
        }

        .vip-player-pill {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 6px 14px 6px 8px;
            background: #ffffff;
            border: 3px solid #e0f2fe;
            border-radius: 20px;
            color: #12375e;
            box-shadow: 0 5px 0 #094775, 0 8px 15px rgba(0,0,0,0.15);
            flex-shrink: 0;
            text-decoration: none;
            cursor: pointer;
            outline: none;
            font-family: inherit;
            transition: all 0.15s;
        }
        .vip-player-pill:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 0 #094775, 0 10px 20px rgba(0,0,0,0.2);
        }
        .vip-player-pill:active {
            transform: translateY(2px);
            box-shadow: 0 3px 0 #094775;
        }
        .vip-player-pill * {
            pointer-events: none;
        }
        .player-avatar-img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2.5px solid #38bdf8;
            box-shadow: 0 3px 6px rgba(0,0,0,0.15);
        }
        .player-details { display: flex; flex-direction: column; }
        .player-details b {
            font-size: 14px;
            font-weight: 1000;
            color: #0f2d4e;
            line-height: 1.2;
        }
        .player-details small {
            font-size: 11px;
            font-weight: 800;
            color: #64748b;
            margin-top: 3px;
        }
        .player-level-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            background: linear-gradient(135deg, #fef08a, #facc15);
            border: 1.5px solid #eab308;
            border-radius: 999px;
            color: #713f12;
            font-size: 10px;
            font-weight: 1000;
        }

        .vip-adventure-branding {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
            text-align: center;
            flex: 1;
        }
        .brand-star {
            font-size: 28px;
            filter: drop-shadow(0 3px 0 #8c4c00);
            animation: starBounce 2s infinite ease-in-out;
        }
        @keyframes starBounce {
            0%, 100% { transform: scale(1) rotate(0deg); }
            50% { transform: scale(1.15) rotate(10deg); }
        }
        .branding-text h1 {
            margin: 0;
            color: #ffe658;
            font-size: 32px;
            font-family: 'Fredoka', cursive, sans-serif;
            line-height: 1;
            text-shadow: 0 4px 0 #7e4200, 2px 0 #7e4200, -2px 0 #7e4200, 0 0 15px rgba(255, 230, 88, 0.5);
            font-weight: 1000;
            letter-spacing: 0.5px;
        }
        .branding-text small {
            display: block;
            color: #ffffff;
            font-size: 12px;
            letter-spacing: 1px;
            margin-top: 4px;
            font-weight: 900;
            text-shadow: 0 2px 0 #074775;
        }

        .vip-top-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }
        .star-wallet-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 6px 16px;
            background: linear-gradient(180deg, #094775, #063152);
            border: 2.5px solid #6cd5ff;
            border-radius: 16px;
            color: #fff;
            box-shadow: inset 0 -3px 0 rgba(0,0,0,0.3), 0 4px 10px rgba(0,0,0,0.2);
        }
        .star-wallet-card small {
            font-size: 9px;
            font-weight: 900;
            color: #ffe885;
            letter-spacing: 1px;
        }
        .star-wallet-card b {
            font-size: 17px;
            font-weight: 1000;
            color: #ffe658;
            text-shadow: 0 2px 4px rgba(0,0,0,0.4);
            transition: all 0.2s ease;
        }
        .star-wallet-card.star-wallet-pulse {
            animation: starWalletPulse 0.5s ease-in-out;
        }
        @keyframes starWalletPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.12); filter: drop-shadow(0 0 10px #fde047); }
            100% { transform: scale(1); }
        }

        .vip-icon-btn {
            width: 44px;
            height: 44px;
            display: inline-grid;
            place-items: center;
            border: 2.5px solid #9ee6ff;
            border-radius: 50%;
            background: #0866a7;
            color: #fff;
            font-size: 18px;
            box-shadow: inset 0 -4px 0 #064875, 0 4px 10px rgba(0,0,0,0.2);
            cursor: pointer;
            text-decoration: none;
            transition: transform 0.15s, background 0.15s;
        }
        .vip-icon-btn:hover {
            transform: translateY(-2px) scale(1.05);
            background: #0b7cc9;
            box-shadow: 0 0 15px rgba(72, 195, 247, 0.5);
        }
        .vip-icon-logout {
            background: #c0392b;
            border-color: #ff7675;
            box-shadow: inset 0 -4px 0 #851e13, 0 4px 10px rgba(0,0,0,0.2);
        }
        .vip-icon-logout:hover {
            background: #e74c3c;
        }

        /* 💎 Nút Nâng Cấp Gói phong cách Gemini / SaaS Pro */
        .btn-upgrade-topbar {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 16px;
            background: linear-gradient(135deg, #f59e0b 0%, #ea580c 50%, #d97706 100%);
            border: 2px solid #fef08a;
            border-radius: 999px;
            color: #ffffff !important;
            font-size: 13px;
            font-weight: 1000;
            text-decoration: none !important;
            box-shadow: 0 4px 14px rgba(245, 158, 11, 0.45), inset 0 1px 0 rgba(255,255,255,0.4);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            animation: upgradeGlowPulse 2.5s infinite;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .btn-upgrade-topbar:hover {
            transform: translateY(-2px) scale(1.04);
            box-shadow: 0 6px 20px rgba(245, 158, 11, 0.7), inset 0 1px 0 rgba(255,255,255,0.6);
            background: linear-gradient(135deg, #fbbf24 0%, #f97316 50%, #d97706 100%);
        }
        .btn-upgrade-spark {
            font-size: 15px;
            animation: sparkSpin 3s infinite ease-in-out;
            display: inline-block;
        }
        @keyframes upgradeGlowPulse {
            0%, 100% { box-shadow: 0 4px 14px rgba(245, 158, 11, 0.45); }
            50% { box-shadow: 0 4px 22px rgba(245, 158, 11, 0.85); }
        }
        @keyframes sparkSpin {
            0%, 100% { transform: scale(1) rotate(0deg); }
            50% { transform: scale(1.2) rotate(15deg); }
        }

        @media (max-width: 1050px) {
            .vip-topbar { padding: 0 15px; height: 78px; }
            .branding-text h1 { font-size: 24px; }
            .branding-text small { font-size: 10px; }
        }
        @media (max-width: 760px) {
            .vip-topbar { height: 70px; padding: 0 12px; }
            .vip-player-pill { display: none; }
            .branding-text h1 { font-size: 19px; }
            .branding-text small { display: none; }
        }

        .star-wallet-card, .player-level-badge {
            cursor: pointer;
            transition: transform 0.15s, box-shadow 0.15s;
        }
        .star-wallet-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(250, 204, 21, 0.4);
            border-color: #fde047;
        }
        .player-level-badge:hover {
            transform: scale(1.05);
            box-shadow: 0 2px 8px rgba(234, 179, 8, 0.4);
        }

        /* Star Guide Modal Styles */
        .star-guide-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(10, 25, 47, 0.75);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            z-index: 100000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.25s ease;
        }
        .star-guide-backdrop.open {
            opacity: 1;
            pointer-events: auto;
        }
        .star-guide-box {
            background: #ffffff;
            border-radius: 28px;
            border: 4px solid #60a5fa;
            width: min(520px, 100%);
            padding: 30px 24px;
            position: relative;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.4), inset 0 0 25px rgba(191, 219, 254, 0.4);
            transform: scale(0.92);
            transition: transform 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            text-align: center;
        }
        .star-guide-backdrop.open .star-guide-box {
            transform: scale(1);
        }
        .star-guide-close {
            position: absolute;
            top: 14px;
            right: 14px;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #f1f5f9;
            border: 2px solid #cbd5e1;
            color: #64748b;
            font-size: 16px;
            font-weight: 900;
            display: grid;
            place-items: center;
            cursor: pointer;
            transition: all 0.15s;
        }
        .star-guide-close:hover {
            background: #fee2e2;
            border-color: #ef4444;
            color: #b91c1c;
        }
        .star-guide-orb {
            font-size: 48px;
            display: inline-block;
            filter: drop-shadow(0 4px 10px rgba(234, 179, 8, 0.5));
            animation: orbBounce 2s infinite alternate ease-in-out;
        }
        @keyframes orbBounce {
            from { transform: translateY(0); }
            to { transform: translateY(-6px); }
        }
        .star-guide-header h2 {
            font-family: 'Fredoka', cursive, sans-serif;
            font-size: 24px;
            color: #1e3a8a;
            margin: 8px 0 4px;
        }
        .star-guide-header p {
            color: #475569;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 18px;
        }
        .star-guide-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 20px;
        }
        .star-stat-item {
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 18px;
            padding: 12px;
        }
        .star-stat-item small {
            display: block;
            font-size: 10px;
            font-weight: 900;
            color: #64748b;
            letter-spacing: 0.5px;
        }
        .star-stat-item b {
            font-family: 'Fredoka', cursive, sans-serif;
            font-size: 18px;
            color: #b45309;
        }
        .star-guide-grid {
            display: flex;
            flex-direction: column;
            gap: 10px;
            text-align: left;
            margin-bottom: 22px;
        }
        .star-guide-card {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            background: #f0fdf4;
            border: 2px solid #bbf7d0;
            border-radius: 16px;
            padding: 12px 14px;
        }
        .star-guide-card:nth-child(2) {
            background: #eff6ff;
            border-color: #bfdbfe;
        }
        .star-guide-card:nth-child(3) {
            background: #fefce8;
            border-color: #fef08a;
        }
        .star-guide-card i {
            font-style: normal;
            font-size: 22px;
            flex-shrink: 0;
            margin-top: 2px;
        }
        .star-guide-card b {
            display: block;
            font-size: 13px;
            color: #0f172a;
            font-weight: 900;
        }
        .star-guide-card span {
            font-size: 12px;
            color: #475569;
            font-weight: 700;
            line-height: 1.4;
        }
        .star-guide-btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 12px 20px;
            border-radius: 16px;
            background: linear-gradient(180deg, #3b82f6, #1d4ed8);
            border: 2px solid #ffffff;
            color: #ffffff;
            font-size: 14px;
            font-weight: 1000;
            text-decoration: none;
            box-shadow: 0 5px 0 #1e3a8a, 0 8px 18px rgba(37, 99, 235, 0.35);
            transition: all 0.15s;
        }
        .star-guide-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 0 #1e3a8a, 0 10px 22px rgba(37, 99, 235, 0.45);
        }

        /* 🔄 SIDEBAR TOGGLE & COLLAPSED STYLING (USER PORTAL) */
        .btn-sidebar-toggle-vip {
            width: 44px;
            height: 44px;
            display: inline-grid;
            place-items: center;
            border: 2.5px solid #9ee6ff;
            border-radius: 14px;
            background: #0866a7;
            color: #fff;
            font-size: 20px;
            box-shadow: inset 0 -4px 0 #064875, 0 4px 10px rgba(0,0,0,0.2);
            cursor: pointer;
            text-decoration: none;
            transition: transform 0.15s, background 0.15s;
            flex-shrink: 0;
        }
        .btn-sidebar-toggle-vip:hover {
            transform: translateY(-2px) scale(1.05);
            background: #0b7cc9;
            box-shadow: 0 0 15px rgba(72, 195, 247, 0.5);
        }

        /* User Profile & Password Modal Styles */
        .user-profile-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(10, 25, 47, 0.85);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            z-index: 9999999 !important;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }
        .user-profile-backdrop.open {
            display: flex !important;
            opacity: 1 !important;
            visibility: visible !important;
            pointer-events: auto !important;
        }
        .user-profile-box {
            background: #ffffff;
            border-radius: 24px;
            border: 3.5px solid #ffffff;
            width: min(480px, 94vw);
            max-height: 94vh;
            display: flex;
            flex-direction: column;
            position: relative;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.45), 0 0 0 1px rgba(255, 255, 255, 0.2);
            transform: scale(0.94);
            transition: transform 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            overflow: hidden;
        }
        .user-profile-backdrop.open .user-profile-box {
            transform: scale(1);
        }

        /* Nút đóng góc phải */
        .user-profile-close-btn {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            border: 1.5px solid rgba(255, 255, 255, 0.35);
            color: #ffffff;
            font-size: 14px;
            font-weight: 900;
            display: grid;
            place-items: center;
            cursor: pointer;
            transition: all 0.15s;
            z-index: 10;
            box-shadow: 0 2px 6px rgba(0,0,0,0.25);
        }
        .user-profile-close-btn:hover {
            background: #ef4444;
            border-color: #f87171;
            color: #ffffff;
            transform: scale(1.08);
        }

        /* Hero Header: Căn giữa dọc chuẩn tỉ lệ Dũng Sĩ */
        .user-profile-hero {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 55%, #3b82f6 100%);
            padding: 16px 20px 12px;
            text-align: center;
            color: #ffffff;
            position: relative;
            border-bottom: 3.5px solid #f59e0b;
        }
        .user-profile-avatar-wrap {
            width: 64px;
            height: 64px;
            margin: 0 auto 6px;
            position: relative;
        }
        .user-profile-avatar-img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #fbbf24;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            background: #ffffff;
            display: grid;
            place-items: center;
        }
        .user-profile-role-badge {
            position: absolute;
            bottom: -3px;
            right: -4px;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #ffffff;
            font-size: 10.5px;
            font-weight: 1000;
            padding: 2px 7px;
            border-radius: 999px;
            border: 1.5px solid #ffffff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.25);
            white-space: nowrap;
        }
        .user-profile-name {
            font-family: 'Fredoka', cursive, sans-serif;
            font-size: 19px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 4px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        .user-profile-meta {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 11.5px;
            font-weight: 800;
            color: #bae6fd;
            flex-wrap: wrap;
        }
        .user-profile-code-tag {
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.28);
            padding: 1px 8px;
            border-radius: 999px;
            color: #ffffff;
            white-space: nowrap;
        }
        .user-profile-email-tag {
            background: rgba(255, 255, 255, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.32);
            padding: 1px 8px;
            border-radius: 999px;
            color: #ffffff;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .btn-edit-email-mini {
            background: rgba(255, 255, 255, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.4);
            color: #ffffff;
            border-radius: 999px;
            padding: 0 5px;
            font-size: 10px;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.15s;
            line-height: 1.3;
        }
        .btn-edit-email-mini:hover {
            background: #f59e0b;
            border-color: #fde68a;
            color: #ffffff;
            transform: scale(1.08);
        }

        /* Thanh Chuyển Tab Rực Rỡ */
        .user-profile-tabs {
            display: flex;
            background: #e2e8f0;
            border-bottom: 2px solid #cbd5e1;
            padding: 4px 10px 0;
            gap: 6px;
        }
        .user-profile-tab-btn {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 8px 12px;
            font-family: inherit;
            font-size: 13.5px;
            font-weight: 800;
            color: #475569;
            background: transparent;
            border: none;
            cursor: pointer;
            transition: all 0.15s;
            border-radius: 10px 10px 0 0;
        }
        .user-profile-tab-btn:hover {
            color: #0f172a;
            background: rgba(255, 255, 255, 0.5);
        }
        .user-profile-tab-btn.active {
            color: #0284c7;
            background: #ffffff;
            font-weight: 900;
            border-top: 3px solid #0284c7;
            box-shadow: 0 -2px 6px rgba(0, 0, 0, 0.05);
        }
        .user-profile-tab-btn.tab-password.active {
            color: #d97706;
            border-top-color: #f59e0b;
        }

        /* Phần Nội Dung Modal */
        .user-profile-body {
            padding: 14px 18px;
            color: #0f172a;
            overflow: hidden;
        }
        .user-tab-panel {
            display: none;
        }
        .user-tab-panel.active {
            display: block;
            animation: fadeInTab 0.18s ease-in-out;
        }
        @keyframes fadeInTab {
            from { opacity: 0; transform: translateY(4px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Tab 1: Stats & Info Pods (2x2 grid) */
        .profile-stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-bottom: 10px;
        }
        .profile-stat-pod {
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            padding: 7px 10px;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 0 #cbd5e1, 0 2px 5px rgba(0,0,0,0.03);
        }
        .profile-stat-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: grid;
            place-items: center;
            font-size: 16px;
            flex-shrink: 0;
        }
        .profile-stat-info small {
            display: block;
            font-size: 9.5px;
            font-weight: 800;
            color: #64748b;
            text-transform: uppercase;
            line-height: 1.1;
        }
        .profile-stat-info b {
            font-family: 'Fredoka', cursive, sans-serif;
            font-size: 14.5px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.2;
        }
        .profile-info-list {
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            padding: 8px 12px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .profile-info-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12.5px;
            padding-bottom: 4px;
            border-bottom: 1px dashed #e2e8f0;
        }
        .profile-info-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
        .profile-info-row span {
            color: #475569;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .profile-info-row b {
            color: #0f172a;
            font-weight: 900;
        }

        /* Tab 2: Thông Báo Bảo Mật Tinh Gọn */
        .profile-security-hint {
            background: linear-gradient(135deg, #eff6ff 0%, #f0fdf4 100%);
            border: 1.5px solid #93c5fd;
            border-radius: 10px;
            padding: 7px 12px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: #1e3a8a;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .profile-security-hint b {
            color: #0369a1;
        }
        .profile-security-hint u {
            text-decoration-color: #38bdf8;
            font-weight: 800;
        }

        /* Thông Báo Trạng Thái Alert */
        .profile-alert {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12.5px;
            font-weight: 800;
            margin-bottom: 8px;
            display: none;
        }
        .profile-alert.success {
            display: block;
            background: #dcfce7;
            border: 1.5px solid #86efac;
            color: #166534;
        }
        .profile-alert.error {
            display: block;
            background: #fee2e2;
            border: 1.5px solid #fca5a5;
            color: #991b1b;
        }

        /* Form Đổi Mật Khẩu */
        .profile-form-2cols {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-bottom: 6px;
        }
        .profile-field-compact {
            margin-bottom: 6px;
            display: flex;
            flex-direction: column;
        }
        .profile-field-compact label {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 12px;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 3px;
        }
        .profile-field-compact label .req-star {
            color: #ef4444;
            font-weight: 900;
        }
        .profile-input-wrap {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
        }
        .profile-input {
            width: 100%;
            height: 36px;
            padding: 0 32px 0 10px;
            border-radius: 9px;
            border: 1.5px solid #cbd5e1;
            font-family: inherit;
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
            background: #f8fafc;
            transition: all 0.15s;
            outline: none;
            box-sizing: border-box;
        }
        .profile-input:focus {
            background: #ffffff;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.18);
        }
        .btn-toggle-pwd {
            position: absolute;
            right: 8px;
            background: transparent;
            border: none;
            color: #64748b;
            cursor: pointer;
            font-size: 14px;
            padding: 2px;
            line-height: 1;
        }
        .btn-toggle-pwd:hover {
            color: #0f172a;
            transform: scale(1.1);
        }

        /* Hàng Nhập OTP + Nút Nhận OTP */
        .otp-input-action-row {
            display: flex;
            gap: 6px;
            width: 100%;
        }
        .profile-input.otp-field {
            height: 36px;
            padding: 0 8px;
            font-weight: 900;
            letter-spacing: 3px;
            text-align: center;
            font-size: 15px;
            color: #0284c7;
            background: #ffffff;
            border: 2px dashed #0284c7;
            flex: 1;
            min-width: 0;
        }
        .btn-request-otp {
            height: 36px;
            padding: 0 12px;
            background: linear-gradient(180deg, #0284c7 0%, #0369a1 100%);
            color: #ffffff;
            border: 1.5px solid #38bdf8;
            border-radius: 9px;
            font-size: 12px;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 3px 0 #075985;
            transition: all 0.15s;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 4px;
            font-family: inherit;
            flex-shrink: 0;
        }
        .btn-request-otp:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 4px 0 #075985;
            filter: brightness(1.08);
        }
        .btn-request-otp:active:not(:disabled) {
            transform: translateY(2px);
            box-shadow: 0 1px 0 #075985;
        }
        .btn-request-otp:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            box-shadow: none;
            transform: none;
        }
        .otp-timer-badge {
            display: block;
            color: #0284c7;
            font-size: 11px;
            font-weight: 800;
            margin-top: 2px;
        }

        /* Nút Xác Nhận Đổi Mật Khẩu 3D Xúc Giác Nổi Bật */
        .btn-save-password {
            width: 100%;
            height: 38px;
            padding: 0 16px;
            background: linear-gradient(180deg, #10b981 0%, #059669 100%);
            border: 2px solid #34d399;
            border-radius: 11px;
            color: #ffffff;
            font-family: inherit;
            font-size: 13.5px;
            font-weight: 900;
            cursor: pointer;
            box-shadow: 0 4px 0 #047857, 0 6px 14px rgba(16, 185, 129, 0.35);
            transition: all 0.15s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            letter-spacing: 0.5px;
            margin-top: 4px;
        }
        .btn-save-password:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 0 #047857, 0 8px 18px rgba(16, 185, 129, 0.45);
        }
        .btn-save-password:active {
            transform: translateY(2px);
            box-shadow: 0 2px 0 #047857;
        }

        /* ====== Modal Đổi Email Inline ====== */
        .email-modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(10, 25, 47, 0.82);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            z-index: 99999999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }
        .email-modal-backdrop.open {
            display: flex !important;
        }
        .email-modal-box {
            background: #ffffff;
            border-radius: 20px;
            width: min(420px, 94vw);
            padding: 0;
            box-shadow: 0 28px 60px rgba(0,0,0,0.42), 0 0 0 1.5px rgba(255,255,255,0.2);
            overflow: hidden;
            transform: scale(0.92) translateY(16px);
            transition: transform 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .email-modal-backdrop.open .email-modal-box {
            transform: scale(1) translateY(0);
        }
        .email-modal-header {
            background: linear-gradient(135deg, #0369a1 0%, #0284c7 60%, #38bdf8 100%);
            padding: 18px 20px 14px;
            text-align: center;
            color: #fff;
            position: relative;
        }
        .email-modal-header h3 {
            font-size: 17px;
            font-weight: 900;
            margin: 0 0 4px;
            text-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }
        .email-modal-header p {
            font-size: 12px;
            color: #bae6fd;
            font-weight: 600;
            margin: 0;
        }
        .email-modal-header .em-icon {
            font-size: 32px;
            margin-bottom: 6px;
            display: block;
        }
        .email-modal-close {
            position: absolute;
            top: 10px;
            right: 12px;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            border: 1.5px solid rgba(255,255,255,0.4);
            color: #fff;
            font-size: 14px;
            font-weight: 900;
            cursor: pointer;
            display: grid;
            place-items: center;
            transition: all 0.15s;
        }
        .email-modal-close:hover {
            background: #ef4444;
            border-color: #f87171;
        }
        .email-modal-body {
            padding: 18px 20px 20px;
        }
        .email-modal-label {
            display: block;
            font-size: 12px;
            font-weight: 800;
            color: #0369a1;
            margin-bottom: 6px;
            letter-spacing: 0.2px;
        }
        .email-modal-input {
            width: 100%;
            height: 42px;
            padding: 0 12px;
            border-radius: 11px;
            border: 2px solid #bae6fd;
            font-family: inherit;
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
            background: #f0f9ff;
            outline: none;
            transition: all 0.18s;
            box-sizing: border-box;
        }
        .email-modal-input:focus {
            border-color: #0284c7;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.2);
        }
        .email-modal-hint {
            font-size: 11px;
            color: #64748b;
            margin-top: 6px;
            font-weight: 600;
        }
        .email-modal-alert {
            margin-top: 8px;
            padding: 7px 10px;
            border-radius: 8px;
            font-size: 12.5px;
            font-weight: 800;
            display: none;
        }
        .email-modal-alert.error {
            display: block;
            background: #fee2e2;
            border: 1.5px solid #fca5a5;
            color: #991b1b;
        }
        .email-modal-alert.success {
            display: block;
            background: #dcfce7;
            border: 1.5px solid #86efac;
            color: #166534;
        }
        .email-modal-actions {
            display: flex;
            gap: 10px;
            margin-top: 16px;
        }
        .email-modal-btn-cancel {
            flex: 1;
            height: 40px;
            border-radius: 10px;
            border: 2px solid #e2e8f0;
            background: #f8fafc;
            color: #475569;
            font-family: inherit;
            font-size: 13.5px;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.15s;
        }
        .email-modal-btn-cancel:hover {
            background: #e2e8f0;
        }
        .email-modal-btn-save {
            flex: 2;
            height: 40px;
            border-radius: 10px;
            border: 2px solid #38bdf8;
            background: linear-gradient(180deg, #0284c7 0%, #0369a1 100%);
            color: #fff;
            font-family: inherit;
            font-size: 13.5px;
            font-weight: 900;
            cursor: pointer;
            box-shadow: 0 4px 0 #075985;
            transition: all 0.15s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .email-modal-btn-save:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 0 #075985;
        }
        .email-modal-btn-save:active:not(:disabled) {
            transform: translateY(2px);
            box-shadow: 0 2px 0 #075985;
        }
        .email-modal-btn-save:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* ====== Toast Thành Công Đổi Mật Khẩu ====== */
        .pwd-success-toast {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.8);
            z-index: 999999999;
            background: #ffffff;
            border-radius: 24px;
            padding: 0;
            width: min(380px, 90vw);
            box-shadow: 0 32px 80px rgba(0,0,0,0.45), 0 0 0 1.5px rgba(16,185,129,0.3);
            overflow: hidden;
            opacity: 0;
            pointer-events: none;
            transition: all 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .pwd-success-toast.show {
            opacity: 1;
            pointer-events: auto;
            transform: translate(-50%, -50%) scale(1);
        }
        .pwd-toast-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.55);
            backdrop-filter: blur(6px);
            z-index: 999999998;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }
        .pwd-toast-backdrop.show {
            opacity: 1;
            pointer-events: auto;
        }
        .pwd-toast-header {
            background: linear-gradient(135deg, #059669 0%, #10b981 60%, #34d399 100%);
            padding: 28px 20px 20px;
            text-align: center;
            position: relative;
        }
        .pwd-toast-icon {
            width: 68px;
            height: 68px;
            background: rgba(255,255,255,0.2);
            border: 3px solid rgba(255,255,255,0.5);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin: 0 auto 10px;
            animation: toastIconPop 0.5s cubic-bezier(0.175,0.885,0.32,1.275) 0.2s both;
        }
        @keyframes toastIconPop {
            from { transform: scale(0); opacity: 0; }
            to   { transform: scale(1); opacity: 1; }
        }
        .pwd-toast-title {
            font-size: 20px;
            font-weight: 900;
            color: #ffffff;
            margin: 0 0 4px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.15);
        }
        .pwd-toast-subtitle {
            font-size: 13px;
            color: rgba(255,255,255,0.85);
            font-weight: 600;
            margin: 0;
        }
        .pwd-toast-body {
            padding: 20px 24px 24px;
            text-align: center;
        }
        .pwd-toast-msg {
            font-size: 13.5px;
            color: #334155;
            font-weight: 600;
            line-height: 1.6;
            margin-bottom: 16px;
        }
        .pwd-toast-countdown {
            font-size: 12px;
            color: #64748b;
            font-weight: 700;
            margin-bottom: 14px;
        }
        .pwd-toast-progress {
            height: 4px;
            background: #e2e8f0;
            border-radius: 99px;
            overflow: hidden;
            margin-bottom: 16px;
        }
        .pwd-toast-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #059669, #10b981);
            border-radius: 99px;
            width: 100%;
            transition: width linear;
        }
        .pwd-toast-btn-home {
            width: 100%;
            height: 44px;
            border-radius: 12px;
            border: 2px solid #34d399;
            background: linear-gradient(180deg, #10b981 0%, #059669 100%);
            color: #fff;
            font-family: inherit;
            font-size: 14.5px;
            font-weight: 900;
            cursor: pointer;
            box-shadow: 0 4px 0 #047857;
            transition: all 0.15s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .pwd-toast-btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 0 #047857;
        }
        .pwd-toast-btn-home:active {
            transform: translateY(2px);
            box-shadow: 0 2px 0 #047857;
        }

        .app-sidebar {
            transition: width 0.22s cubic-bezier(0.4, 0, 0.2, 1), transform 0.25s ease, padding 0.22s ease !important;
        }
        .app-main {
            transition: margin-left 0.22s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }

        /* Khi đóng sidebar ở giao diện học sinh */
        body.sidebar-collapsed .app-sidebar {
            width: 76px !important;
            padding: 20px 8px !important;
        }
        body.sidebar-collapsed .app-main {
            margin-left: 76px !important;
        }
        body.sidebar-collapsed .app-sidebar .brand {
            justify-content: center !important;
            margin: 0 0 24px !important;
        }
        .app-sidebar .sidebar-ai-upgrade {
            display: block; width: 100%; margin-top: 25px; padding: 16px; text-align: left; cursor: pointer;
            border: 3.5px solid #ffffff; border-radius: 20px; color: #fff; font-family: inherit;
            background: linear-gradient(145deg, #f59e0b 0%, #ec4899 55%, #7c3aed 100%);
            box-shadow: 0 16px 36px rgba(0,0,0,0.18), inset 0 -6px 0 rgba(0,0,0,0.15);
            transition: transform .15s, box-shadow .15s;
        }
        .app-sidebar .sidebar-ai-upgrade:hover { transform: translateY(-2px); box-shadow: 0 20px 40px rgba(0,0,0,0.22), inset 0 -6px 0 rgba(0,0,0,0.15); }
        .app-sidebar .sidebar-ai-upgrade:active { transform: translateY(4px); box-shadow: 0 6px 14px rgba(0,0,0,0.2), inset 0 -2px 0 rgba(0,0,0,0.15); }
        .app-sidebar .sidebar-voice-btn {
            display: flex; align-items: center; gap: 10px; width: 100%; margin-top: 18px; padding: 12px 14px; cursor: pointer; flex-shrink: 0;
            border: 3.5px solid #ffffff; border-radius: 18px; color: #fff; font-family: inherit; font-size: 14px; font-weight: 900;
            background: linear-gradient(135deg, #10b981, #0ea5e9);
            box-shadow: 0 16px 36px rgba(0,0,0,0.18), inset 0 -6px 0 rgba(0,0,0,0.15);
            transition: transform .15s;
        }
        .app-sidebar .sidebar-voice-btn:hover { transform: translateY(-2px); }
        .app-sidebar .sidebar-voice-btn:active { transform: translateY(4px); }
        .app-sidebar .sidebar-voice-btn + .sidebar-ai-upgrade { margin-top: 12px; }
        body.sidebar-collapsed .app-sidebar .sidebar-voice-btn b { display: none; }
        .sidebar-ai-upgrade .sau-icon { display: block; font-size: 30px; line-height: 1; }
        .sidebar-ai-upgrade b { display: block; margin-top: 8px; font-size: 15px; font-weight: 900; }
        .sidebar-ai-upgrade small { display: block; margin: 5px 0 12px; font-size: 12px; font-weight: 700; line-height: 1.4; color: #fff7ed; }
        .sidebar-ai-upgrade em { display: inline-block; padding: 7px 14px; border-radius: 12px; background: #ffffff; color: #7c2d12; font-style: normal; font-size: 12.5px; font-weight: 900; box-shadow: 0 3px 0 rgba(0,0,0,0.18); }

        /* Thanh bên cuộn được để không che mục dưới cùng (Nâng cấp AI, Đăng xuất) khi màn hình thấp */
        .app-sidebar {
            overflow-y: auto !important;
            overflow-x: hidden !important;
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.28) transparent;
            overscroll-behavior: contain;
        }
        .app-sidebar::-webkit-scrollbar { width: 6px; }
        .app-sidebar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.28); border-radius: 6px; }
        .app-sidebar .sidebar-bottom { flex-shrink: 0; }
        .app-sidebar .sidebar-ai-upgrade { flex-shrink: 0; }

        /* Màn hình thấp: thu gọn khoảng cách và khung nâng cấp để thấy hết mà ít phải cuộn */
        @media (max-height: 900px) {
            body:not(.sidebar-collapsed) .app-sidebar { padding-top: 18px !important; padding-bottom: 14px !important; }
            body:not(.sidebar-collapsed) .app-sidebar .brand { margin-bottom: 14px !important; }
            body:not(.sidebar-collapsed) .app-sidebar .mini-profile { margin-bottom: 12px !important; }
            body:not(.sidebar-collapsed) .app-sidebar .side-nav { gap: 4px !important; }
            body:not(.sidebar-collapsed) .app-sidebar .side-nav a { padding-top: 8px !important; padding-bottom: 8px !important; }
            .app-sidebar .sidebar-ai-upgrade { margin-top: 14px; padding: 12px 14px; }
            .sidebar-ai-upgrade .sau-icon { display: none; }
            .sidebar-ai-upgrade b { margin-top: 0; font-size: 14px; }
            .sidebar-ai-upgrade small { margin: 3px 0 8px; font-size: 11px; }
            .sidebar-ai-upgrade em { padding: 5px 12px; font-size: 12px; }
        }

        body.sidebar-collapsed .app-sidebar .brand span:last-child,
        body.sidebar-collapsed .app-sidebar .side-nav a span,
        body.sidebar-collapsed .app-sidebar .side-nav a b,
        body.sidebar-collapsed .app-sidebar .sidebar-ai-upgrade,
        body.sidebar-collapsed .app-sidebar .sidebar-quest,
        body.sidebar-collapsed .app-sidebar .sidebar-bottom span {
            display: none !important;
        }
        body.sidebar-collapsed .app-sidebar .side-nav a {
            justify-content: center !important;
            padding: 12px 0 !important;
        }
        body.sidebar-collapsed .app-sidebar .side-nav a i {
            width: 38px !important;
            height: 38px !important;
            font-size: 20px !important;
            margin: 0 !important;
        }
        body.sidebar-collapsed .app-sidebar .sidebar-bottom button {
            justify-content: center !important;
            padding: 10px 0 !important;
        }
    </style>
    <script>
        window.openUserProfileModal = function() {
            var modal = document.getElementById('user-profile-modal');
            if (modal) {
                modal.style.setProperty('display', 'flex', 'important');
                modal.style.setProperty('opacity', '1', 'important');
                modal.style.setProperty('visibility', 'visible', 'important');
                modal.style.setProperty('pointer-events', 'auto', 'important');
                modal.classList.add('open');
                modal.setAttribute('aria-hidden', 'false');
            }
        };
        window.closeUserProfileModal = function() {
            var modal = document.getElementById('user-profile-modal');
            if (modal) {
                modal.classList.remove('open');
                modal.setAttribute('aria-hidden', 'true');
                modal.style.setProperty('display', 'none', 'important');
                modal.style.setProperty('opacity', '0', 'important');
                modal.style.setProperty('visibility', 'hidden', 'important');
                modal.style.setProperty('pointer-events', 'none', 'important');
            }
            var alertBox = document.getElementById('pwd-alert');
            if (alertBox) {
                alertBox.className = 'profile-alert';
                alertBox.textContent = '';
            }
        };
    </script>
</head>
<body class="app-body">
    <canvas id="magic-canvas" aria-hidden="true"></canvas>

    <!-- Sidebar bên trái gọn gàng -->
    <aside class="app-sidebar" data-sidebar>
        <a class="brand" href="{{ route('home') }}" aria-label="IC3 Adventure - Trang chủ">
            <span class="brand-mark"><i></i><i></i><i></i></span>
            <span><b>IC3</b><small>ADVENTURE</small></span>
        </a>

        <nav class="side-nav" aria-label="Điều hướng chính">
            <a class="{{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}" title="Trang của em">
                <i>🏠</i><span>Trang của em</span><b>›</b>
            </a>
            <a class="{{ request()->routeIs('programs','levels.*','tests.*') ? 'active' : '' }}" href="{{ route('programs') }}" title="Học và luyện IC3">
                <i>📚</i><span>Học và luyện IC3</span><b>›</b>
            </a>
            <a class="{{ request()->routeIs('achievements') ? 'active' : '' }}" href="{{ route('achievements') }}" title="Điểm & thành tích">
                <i>🏆</i><span>Điểm & thành tích</span><b>›</b>
            </a>
            <a class="{{ request()->routeIs('games') ? 'active' : '' }}" href="{{ route('games') }}" title="Chơi nhận thưởng">
                <i>🎮</i><span>Chơi nhận thưởng</span><b>›</b>
            </a>
            <a class="{{ request()->routeIs('mistakes.*') ? 'active' : '' }}" href="{{ route('mistakes.index') }}" title="Sổ tay câu sai & Phục thù">
                <i>📕</i><span>Sổ tay câu sai</span>
                @if(auth()->check() && auth()->user()->isStudent())
                    @php $badgeCount = auth()->user()->unresolvedMistakesCount(); @endphp
                    @if($badgeCount > 0)
                        <span style="background: #ef4444; color: #fff; font-size: 11px; font-weight: 1000; padding: 1px 7px; border-radius: 999px; margin-left: auto; margin-right: 4px;">{{ $badgeCount }}</span>
                    @endif
                @endif
                <b>›</b>
            </a>
            @if(auth()->user()?->canAccessAdmin())
                <a class="{{ request()->routeIs('admin.mock-tests.*') ? 'active' : '' }}" href="{{ route('admin.mock-tests.index') }}" title="Quản trị Bộ đề thi thử">
                    <i>📝</i><span>Đề thi thử IC3</span><b>›</b>
                </a>
                <a class="{{ request()->routeIs('pricing.*') ? 'active' : '' }}" href="{{ route('pricing.index') }}" title="Bảng giá các gói bản quyền IC3">
                    <i>💎</i><span>Gói bản quyền</span><b>›</b>
                </a>
            @endif
            @if(auth()->user()?->isStudent())
                <a class="{{ request()->routeIs('parent.dashboard') ? 'active' : '' }}" href="{{ route('parent.dashboard') }}" title="Góc Phụ Huynh">
                    <i>👨‍👩‍👧‍👦</i><span>Góc Phụ Huynh</span><b>›</b>
                </a>
            @endif
        </nav>

        @if(! auth()->user()?->canAccessAdmin())
        @php
            $sidebarHasAi = auth()->user()?->hasAiAssistant();
        @endphp
        @if($sidebarHasAi)
        <button type="button" class="sidebar-voice-btn" onclick="openVoiceChat()" title="Trò chuyện bằng giọng nói với Trợ lý AI">
            <span>🎙️</span><b>Trò chuyện với AI</b>
        </button>
        @endif
        <button type="button" class="sidebar-ai-upgrade" onclick="openAiUpgrade()" title="Mở bảng nâng cấp Trợ lý AI">
            <span class="sau-icon">🤖</span>
            <b>{{ $sidebarHasAi ? 'Trợ lý AI đang bật' : 'Mở khóa Trợ lý AI' }}</b>
            <small>
                @if($sidebarHasAi)
                    Còn {{ max(0, (int) ceil(now()->diffInDays(auth()->user()->ai_assistant_until, false))) }} ngày. Gia hạn để không bị gián đoạn.
                @else
                    Hiểu điểm yếu của em và gợi ý bài luyện riêng.
                @endif
            </small>
            <em>{{ $sidebarHasAi ? '🔄 Gia hạn' : '✨ Nâng cấp ngay' }}</em>
        </button>
        @endif

        <div class="sidebar-bottom">
            @auth
                <form method="post" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Đăng xuất">
                        <i>↪</i><span>Đăng xuất</span>
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" style="display:flex; align-items:center; gap:8px; padding:10px 14px; color:#fff; text-decoration:none; font-weight:800; font-size:13px;">
                    <i>🔑</i><span>Đăng nhập</span>
                </a>
            @endauth
        </div>
    </aside>

    <!-- Khu vực nội dung chính -->
    <div class="app-main">
        <!-- VIP Topbar chuẩn 100% phong cách game Adventure -->
        <header class="vip-topbar" id="vip-topbar">
            <!-- Nút đóng mở sidebar (Bung/Đóng sidebar nhanh) -->
            <button type="button" class="btn-sidebar-toggle-vip" onclick="toggleUserSidebar()" title="Đóng / Mở menu thanh bên" aria-label="Đóng mở thanh bên">
                <span>☰</span>
            </button>

            <!-- Thẻ người chơi / Giáo viên / Khách góc trái -->
            @auth
                @if(auth()->user()->isTeacher())
                    <button type="button" class="vip-player-pill" onclick="window.openUserProfileModal();" data-open-profile-modal title="Bấm để xem hồ sơ và đổi mật khẩu" style="border-color: #6ee7b7; background: #ffffff;">
                        <div style="width: 44px; height: 44px; border-radius: 50%; background: linear-gradient(135deg, #10b981, #059669); display: grid; place-items: center; font-size: 20px; color: #fff; box-shadow: 0 3px 6px rgba(0,0,0,0.15); flex-shrink: 0;">
                            👩‍🏫
                        </div>
                        <div class="player-details" style="text-align: left;">
                            <b style="color: #065f46; font-size: 13.5px;">{{ auth()->user()->name }} ⚙️</b>
                            <small>
                                <span class="player-level-badge" style="background: #ecfdf5; border-color: #a7f3d0; color: #047857; font-size: 10.5px; font-weight: 800;">👩‍🏫 Chế độ Giảng dạy</span>
                            </small>
                        </div>
                    </button>
                @elseif(auth()->user()->isAdmin())
                    <button type="button" class="vip-player-pill" onclick="window.openUserProfileModal();" data-open-profile-modal title="Bấm để xem hồ sơ và đổi mật khẩu" style="border-color: #fca5a5; background: #ffffff;">
                        <div style="width: 44px; height: 44px; border-radius: 50%; background: linear-gradient(135deg, #ef4444, #dc2626); display: grid; place-items: center; font-size: 20px; color: #fff; box-shadow: 0 3px 6px rgba(0,0,0,0.15); flex-shrink: 0;">
                            👑
                        </div>
                        <div class="player-details" style="text-align: left;">
                            <b style="color: #991b1b; font-size: 13.5px;">{{ auth()->user()->name }} ⚙️</b>
                            <small>
                                <span class="player-level-badge" style="background: #fef2f2; border-color: #fecaca; color: #b91c1c; font-size: 10px; font-weight: 800;">👑 Quản trị viên</span>
                            </small>
                        </div>
                    </button>
                @else
                    <button type="button" class="vip-player-pill" onclick="window.openUserProfileModal();" data-open-profile-modal title="Bấm để xem hồ sơ cá nhân và đổi mật khẩu">
                        <img src="{{ asset('images/student-avatar.jpg') }}" alt="Avatar" class="player-avatar-img">
                        <div class="player-details" style="text-align: left;">
                            <b>Explorer {{ auth()->user()->name }} ⚙️</b>
                            <small>
                                <span>{{ auth()->user()->classroom?->name ?? 'Học sinh' }}</span> · 
                                <span class="player-level-badge" title="Tổng điểm bài thi tích lũy">🏆 {{ number_format(auth()->user()->attempts()->sum('score') ?? 0) }} Điểm</span>
                            </small>
                        </div>
                    </button>
                @endif
            @else
                <a class="vip-player-pill" href="{{ route('login') }}" style="text-decoration:none;">
                    <div style="width: 44px; height: 44px; border-radius: 50%; background: linear-gradient(135deg, #38bdf8, #0284c7); display: grid; place-items: center; font-size: 20px; color: #fff;">
                        👋
                    </div>
                    <div class="player-details">
                        <b>Chào Khách Quý</b>
                        <small style="color:#0284c7; font-weight:800;">Bấm để Đăng nhập</small>
                    </div>
                </a>
            @endauth

            <!-- Logo game chính giữa 3D vàng cam rực rỡ -->
            <div class="vip-adventure-branding">
                <span class="brand-star">⭐</span>
                <div class="branding-text">
                    <h1>IC3 DIGITAL ADVENTURE</h1>
                    <small>Học vui · Chơi giỏi · Lớn khôn!</small>
                </div>
                <span class="brand-star">⭐</span>
            </div>

            <!-- Bộ nút điều khiển góc phải: Nâng cấp gói, Stars, Kho bài học, Logout -->
            <div class="vip-top-actions">
                @if(auth()->user()?->canAccessAdmin())
                    <!-- Nút Nâng cấp gói phong cách Gemini / SaaS Pro -->
                    <a href="{{ route('pricing.index') }}" class="btn-upgrade-topbar" title="Xem bảng giá và nâng cấp gói bản quyền IC3 GS6">
                        <span class="btn-upgrade-spark">✨</span>
                        <span>Nâng cấp gói</span>
                    </a>
                @else
                    @if(auth()->user()?->hasAiAssistant())
                        <button type="button" class="btn-upgrade-topbar" onclick="openVoiceChat()" title="Trò chuyện bằng giọng nói với Trợ lý AI" style="border:0; cursor:pointer;">
                            <span class="btn-upgrade-spark">🎙️</span>
                            <span>Trò chuyện AI</span>
                        </button>
                    @else
                        <button type="button" class="btn-upgrade-topbar" onclick="openAiUpgrade()" title="Nâng cấp Trợ lý AI" style="border:0; cursor:pointer;">
                            <span class="btn-upgrade-spark">✨</span>
                            <span>Nâng cấp AI</span>
                        </button>
                    @endif
                @endif

                @auth
                    @if(auth()->user()->canAccessAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="vip-player-pill" style="background: linear-gradient(135deg, #1e1b4b, #312e81); border-color: #818cf8; color: #ffffff; text-decoration: none; padding: 8px 14px; gap: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.2);">
                            <span style="font-size:16px;">🏫</span>
                            <b style="color: #ffffff; font-size: 13px;">Về Quản trị</b>
                        </a>
                    @else
                        <div class="star-wallet-card" data-open-star-modal title="Bấm để xem hướng dẫn Ví Sao Thưởng">
                            <small>Ví Sao Thưởng</small>
                            <b id="topbar-reward-stars">⭐ {{ number_format(auth()->user()->reward_stars ?? 0) }}</b>
                        </div>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="vip-player-pill" style="background: linear-gradient(135deg, #0284c7, #0369a1); border-color: #7dd3fc; color: #ffffff; text-decoration: none; padding: 8px 14px; gap: 8px;">
                        <span>🔑</span>
                        <b style="color: #ffffff; font-size: 13px;">Đăng Nhập</b>
                    </a>
                @endauth

                <a class="vip-icon-btn" href="{{ route('programs') }}" title="Kho bài học">
                    <span>📚</span>
                </a>

                @auth
                <form method="post" action="{{ route('logout') }}" style="display:inline;margin:0;">
                    @csrf
                    <button type="submit" class="vip-icon-btn vip-icon-logout" title="Đăng xuất">
                        <span>↪</span>
                    </button>
                </form>
                @endauth
            </div>
        </header>

        <main class="main-content-flow">
            @yield('content')
        </main>
    </div>

    <!-- Star Guide Modal Popup -->
    <div id="star-guide-modal" class="star-guide-backdrop" aria-hidden="true">
        <div class="star-guide-box">
            <button class="star-guide-close" data-close-star-modal aria-label="Đóng">✕</button>
            <div class="star-guide-header">
                <span class="star-guide-orb">⭐</span>
                <h2>HƯỚNG DẪN ĐIỂM SAO VÀNG</h2>
                <p>Khám phá cách tích lũy Sao Vàng & thăng cấp Hiệp sĩ IC3!</p>
            </div>

            <div class="star-guide-stats">
                <div class="star-stat-item">
                    <small>SAO HIỆN CÓ</small>
                    <b>⭐ {{ auth()->user() ? auth()->user()->attempts()->sum('score') : 0 }}</b>
                </div>
                <div class="star-stat-item">
                    <small>DANH HIỆU</small>
                    <b>Hiệp sĩ Cấp {{ auth()->user() ? min(10, max(1, floor(auth()->user()->attempts()->count() / 3) + 1)) : 1 }}</b>
                </div>
            </div>

            <div class="star-guide-grid">
                <div class="star-guide-card">
                    <i>🎯</i>
                    <div>
                        <b>1. Kiếm sao từ bài luyện tập</b>
                        <span>Mỗi bài ôn luyện IC3 hoàn thành đạt 1000 điểm chuẩn IIG sẽ thưởng ngay điểm Sao Vàng vào kho.</span>
                    </div>
                </div>

                <div class="star-guide-card">
                    <i>🏆</i>
                    <div>
                        <b>2. Thăng cấp danh hiệu Hiệp sĩ</b>
                        <span>Tích lũy càng nhiều sao để nâng cấp từ Tập sự lên Nhà thám hiểm & Đại Hiệp Sĩ IC3 danh giá!</span>
                    </div>
                </div>

                <div class="star-guide-card">
                    <i>🎮</i>
                    <div>
                        <b>3. Nhận vé chơi Game Hiệp sĩ</b>
                        <span>Sao vàng và thành tích giúp bé mở khóa các màn chơi game giải trí Hiệp sĩ Song Kiếm AI.</span>
                    </div>
                </div>
            </div>

            <div class="star-guide-actions">
                <a href="{{ route('programs') }}" class="star-guide-btn-primary">
                    🚀 Bắt đầu làm bài để kiếm Sao ngay <b>→</b>
                </a>
            </div>
        </div>
    </div>

    @auth
    <!-- Modal Hồ Sơ Cá Nhân & Đổi Mật Khẩu Chuẩn 3D Gamified (Không cuộn, vừa khít 1 màn hình) -->
    <div id="user-profile-modal" class="user-profile-backdrop" aria-hidden="true">
        <div class="user-profile-box">
            <!-- Nút đóng góc phải -->
            <button type="button" class="user-profile-close-btn" onclick="window.closeUserProfileModal();" data-close-profile-modal aria-label="Đóng" title="Đóng cửa sổ">✕</button>

            <!-- Hero Header: Căn giữa dọc chuẩn tỉ lệ Dũng Sĩ -->
            <div class="user-profile-hero">
                <div class="user-profile-avatar-wrap">
                    @if(auth()->user()->isStudent())
                        <img src="{{ asset('images/student-avatar.jpg') }}" alt="Avatar" class="user-profile-avatar-img">
                        <span class="user-profile-role-badge">⭐ Hiệp Sĩ</span>
                    @elseif(auth()->user()->isTeacher())
                        <div class="user-profile-avatar-img" style="display:grid; place-items:center; font-size:30px; background:linear-gradient(135deg, #10b981, #059669); color:#fff; border-color:#86efac;">
                            👩‍🏫
                        </div>
                        <span class="user-profile-role-badge" style="background:#059669;">Giáo viên</span>
                    @else
                        <div class="user-profile-avatar-img" style="display:grid; place-items:center; font-size:30px; background:linear-gradient(135deg, #ef4444, #dc2626); color:#fff; border-color:#fca5a5;">
                            👑
                        </div>
                        <span class="user-profile-role-badge" style="background:#dc2626;">Quản trị</span>
                    @endif
                </div>

                <h2 class="user-profile-name">{{ auth()->user()->name }}</h2>

                <div class="user-profile-meta">
                    @if(auth()->user()->student_code)
                        <span class="user-profile-code-tag">Mã HS: <b>{{ auth()->user()->student_code }}</b></span>
                    @endif
                    <span class="user-profile-email-tag" title="Địa chỉ email nhận mã OTP">
                        📧 <b id="user-email-text">{{ auth()->user()->email }}</b>
                        <button type="button" class="btn-edit-email-mini" onclick="promptUpdateEmail()" title="Bấm để đổi sang Email thật nhận mã OTP">✏️ Đổi</button>
                    </span>
                    <span class="user-profile-email-tag" title="Số điện thoại / Zalo dùng để liên hệ và xác minh khi quên tài khoản">
                        📱 <b id="user-phone-text">{{ auth()->user()->phone ?: 'Chưa có SĐT' }}</b>
                        <button type="button" class="btn-edit-email-mini" onclick="openPhoneModal()" title="Thêm hoặc đổi số điện thoại">✏️ Đổi</button>
                    </span>
                </div>
            </div>

            <!-- Tab Buttons -->
            <div class="user-profile-tabs">
                <button type="button" class="user-profile-tab-btn active" onclick="switchProfileTab('stats', this)">
                    <span>🌟</span> Hồ Sơ Dũng Sĩ
                </button>
                <button type="button" class="user-profile-tab-btn tab-password" onclick="switchProfileTab('password', this)">
                    <span>🔑</span> Đổi Mật Khẩu
                </button>
            </div>

            <!-- Body Panels -->
            <div class="user-profile-body">
                <!-- Panel 1: Stats & Info -->
                <div id="panel-profile-stats" class="user-tab-panel active">
                    <div class="profile-stats-grid">
                        <div class="profile-stat-pod">
                            <div class="profile-stat-icon" style="background:#eff6ff; color:#2563eb;">🏆</div>
                            <div class="profile-stat-info">
                                <small>TỔNG ĐIỂM THI</small>
                                <b>{{ number_format(auth()->user()->attempts()->sum('score') ?? 0) }}</b>
                            </div>
                        </div>

                        <div class="profile-stat-pod">
                            <div class="profile-stat-icon" style="background:#fefce8; color:#ca8a04;">⭐</div>
                            <div class="profile-stat-info">
                                <small>VÍ SAO THƯỞNG</small>
                                <b style="color:#d97706;">{{ number_format(auth()->user()->reward_stars ?? 0) }}</b>
                            </div>
                        </div>

                        <div class="profile-stat-pod">
                            <div class="profile-stat-icon" style="background:#f0fdf4; color:#16a34a;">🎮</div>
                            <div class="profile-stat-info">
                                <small>GIỜ GAME HIỆP SĨ</small>
                                <b style="color:#15803d;">{{ auth()->user()->isAdmin() ? 'Không giới hạn' : floor((auth()->user()->game_time_seconds ?? 0) / 60).' Phút' }}</b>
                            </div>
                        </div>

                        <div class="profile-stat-pod">
                            <div class="profile-stat-icon" style="background:#fdf2f8; color:#db2777;">🎯</div>
                            <div class="profile-stat-info">
                                <small>ĐÃ PHỤC THÙ</small>
                                <b style="color:#be185d;">{{ auth()->user()->mistakes()->where('status', 'resolved')->count() }} Câu</b>
                            </div>
                        </div>
                    </div>

                    <div class="profile-info-list">
                        <div class="profile-info-row">
                            <span>🏫 Lớp học / Nhóm:</span>
                            <b>{{ auth()->user()->classroom?->name ?? 'Tự do / Chưa phân lớp' }}</b>
                        </div>
                        <div class="profile-info-row">
                            <span>📚 Khối lớp IC3:</span>
                            <b>{{ auth()->user()->classroom?->level ? 'Khối ' . auth()->user()->classroom->level->grade : 'Mặc định' }}</b>
                        </div>
                        @if(! auth()->user()->isAdmin())
                            @php
                                $pkg = auth()->user()->packageSummary();
                            @endphp
                            @if($pkg['inherited'])
                                <div class="profile-info-row">
                                    <span>👩‍🏫 Giáo viên quản lý:</span>
                                    <b>{{ $pkg['teacher'] }}</b>
                                </div>
                            @endif
                            <div class="profile-info-row">
                                <span>💎 Gói đang dùng:</span>
                                @if($pkg['package'])
                                    <b>{{ $pkg['package'] }}{{ $pkg['inherited'] ? ' (theo giáo viên)' : '' }}@unless($pkg['inherited']) · <a href="{{ route('pricing.history') }}" style="color:#7c3aed; font-weight:900;">Lịch sử đơn</a>@endunless</b>
                                @elseif($pkg['inherited'])
                                    <b>Theo gói của giáo viên</b>
                                @else
                                    <a href="{{ route('pricing.index') }}" style="color:#7c3aed; font-weight:900;">Chưa có gói - Xem bảng giá</a>
                                @endif
                            </div>
                            <div class="profile-info-row">
                                <span>⏳ Hạn sử dụng:</span>
                                <b>{{ $pkg['expires_at'] ? $pkg['expires_at']->format('d/m/Y') : 'Không giới hạn' }}</b>
                            </div>
                        @endif
                        <div class="profile-info-row">
                            <span>🛡️ Vai trò:</span>
                            <b>{{ auth()->user()->isStudent() ? 'Học sinh' : (auth()->user()->isTeacher() ? 'Giáo viên' : 'Quản trị viên') }}</b>
                        </div>
                        <div class="profile-info-row">
                            <span>📅 Ngày tham gia:</span>
                            <b>{{ auth()->user()->created_at?->format('d/m/Y') ?? 'Hôm nay' }}</b>
                        </div>
                        <div class="profile-info-row">
                            <span>⚡ Trạng thái:</span>
                            <span style="color:#16a34a; font-weight:1000; display:inline-flex; align-items:center; gap:5px;">
                                <i style="font-size:10px;">●</i> Hoạt động bình thường
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Panel 2: Change Password -->
                <div id="panel-profile-password" class="user-tab-panel">
                    <!-- Dòng nhắc bảo mật OTP tinh gọn -->
                    <div class="profile-security-hint">
                        <span style="font-size: 16px; flex-shrink: 0;">🛡️</span>
                        <div>
                            <b>Bảo mật 2 lớp:</b> Mã OTP 6 số sẽ gửi đến <u id="security-hint-email">{{ auth()->user()->email }}</u> để xác minh.
                        </div>
                    </div>

                    <div id="pwd-alert" class="profile-alert"></div>

                    <form id="form-change-password" onsubmit="handlePasswordSubmit(event)">
                        @csrf
                        <!-- 1. Mật khẩu hiện tại -->
                        <div class="profile-field-compact">
                            <label for="input-current-password">
                                <span>🔑</span> Mật khẩu hiện tại <span class="req-star">*</span>
                            </label>
                            <div class="profile-input-wrap">
                                <input type="password" id="input-current-password" name="current_password" class="profile-input" placeholder="Nhập mật khẩu đang dùng" required autocomplete="current-password">
                                <button type="button" class="btn-toggle-pwd" onclick="togglePasswordVisibility('input-current-password', this)" title="Hiện/Ẩn mật khẩu">👁️</button>
                            </div>
                        </div>

                        <!-- 2. Mật khẩu mới & Xác nhận (2 cột song song gọn gàng) -->
                        <div class="profile-form-2cols">
                            <div class="profile-field-compact">
                                <label for="input-new-password">
                                    <span>✨</span> Mật khẩu mới <span class="req-star">*</span>
                                </label>
                                <div class="profile-input-wrap">
                                    <input type="password" id="input-new-password" name="password" class="profile-input" placeholder="Tối thiểu 6 ký tự" minlength="6" required autocomplete="new-password">
                                    <button type="button" class="btn-toggle-pwd" onclick="togglePasswordVisibility('input-new-password', this)" title="Hiện/Ẩn mật khẩu">👁️</button>
                                </div>
                            </div>

                            <div class="profile-field-compact">
                                <label for="input-password-confirmation">
                                    <span>🔁</span> Nhập lại mật khẩu <span class="req-star">*</span>
                                </label>
                                <div class="profile-input-wrap">
                                    <input type="password" id="input-password-confirmation" name="password_confirmation" class="profile-input" placeholder="Gõ lại mật khẩu" minlength="6" required autocomplete="new-password">
                                    <button type="button" class="btn-toggle-pwd" onclick="togglePasswordVisibility('input-password-confirmation', this)" title="Hiện/Ẩn mật khẩu">👁️</button>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Mã OTP & Nút Nhận OTP -->
                        <div class="profile-field-compact">
                            <label for="input-otp">
                                <span>📨</span> Mã xác thực Email OTP (6 số) <span class="req-star">*</span>
                            </label>
                            <div class="otp-input-action-row">
                                <input type="text" id="input-otp" name="otp" class="profile-input otp-field" placeholder="Nhập 6 số OTP" maxlength="6" pattern="[0-9]{6}" required autocomplete="one-time-code">
                                <button type="button" id="btn-request-otp" onclick="requestPasswordOtp()" class="btn-request-otp" title="Bấm để nhận mã OTP gửi về Email">
                                    <span>📨</span> Nhận OTP
                                </button>
                            </div>
                            <small id="otp-timer-text" class="otp-timer-badge" style="display:none;"></small>
                        </div>

                        <!-- 4. Nút Xác Nhận Đổi Mật Khẩu -->
                        <button type="submit" id="btn-submit-password" class="btn-save-password">
                            <span>🔒</span> XÁC NHẬN ĐỔI MẬT KHẨU
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endauth

    @auth
    <!-- Toast Thành Công Đổi Mật Khẩu -->
    <div id="pwd-success-backdrop" class="pwd-toast-backdrop"></div>
    <div id="pwd-success-toast" class="pwd-success-toast" role="dialog" aria-modal="true">
        <div class="pwd-toast-header">
            <div class="pwd-toast-icon">✅</div>
            <h3 class="pwd-toast-title">Mật Khẩu Đổi Thành Công!</h3>
            <p class="pwd-toast-subtitle">Tài khoản của bạn đã được bảo mật cập nhật</p>
        </div>
        <div class="pwd-toast-body">
            <p class="pwd-toast-msg">🎉 Chúc mừng! Mật khẩu mới đã được lưu thành công.<br>Bạn sẽ được chuyển về <b>Trang chủ</b> ngay.</p>
            <div class="pwd-toast-countdown" id="pwd-toast-countdown">Chuyển trang sau <b>3</b> giây...</div>
            <div class="pwd-toast-progress">
                <div class="pwd-toast-progress-bar" id="pwd-toast-bar"></div>
            </div>
            <button type="button" class="pwd-toast-btn-home" onclick="window.location.href='{{ route('home') }}'">
                🏠 Về Trang Chủ Ngay
            </button>
        </div>
    </div>
    @endauth

    @auth
    <!-- Modal Đổi Số điện thoại (dùng chung giao diện với modal đổi email) -->
    <div id="phone-update-modal" class="email-modal-backdrop" aria-hidden="true">
        <div class="email-modal-box">
            <div class="email-modal-header">
                <button type="button" class="email-modal-close" onclick="closePhoneModal()" title="Đóng">✕</button>
                <span class="em-icon">📱</span>
                <h3>Cập nhật Số điện thoại</h3>
                <p>Dùng để liên hệ hỗ trợ và xác minh chính chủ khi quên tài khoản</p>
            </div>
            <div class="email-modal-body">
                <label for="phone-modal-input-field" class="email-modal-label">📞 Số điện thoại / Zalo (học sinh nhỏ: SĐT phụ huynh):</label>
                <input type="tel" id="phone-modal-input-field" class="email-modal-input" inputmode="tel" placeholder="vd: 0912345678" autocomplete="tel">
                <label for="phone-modal-password-field" class="email-modal-label">🔐 Mật khẩu hiện tại (để xác nhận):</label>
                <input type="password" id="phone-modal-password-field" class="email-modal-input" placeholder="Nhập mật khẩu đang dùng" autocomplete="current-password">
                <div id="phone-modal-alert" class="email-modal-alert"></div>
                <div class="email-modal-actions">
                    <button type="button" class="email-modal-btn-cancel" onclick="closePhoneModal()">Hủy bỏ</button>
                    <button type="button" id="phone-modal-btn-save" class="email-modal-btn-save" onclick="submitPhoneUpdate()"><span>✅</span> Lưu số điện thoại</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Đổi Email Inline Đẹp (Thay thế prompt() xấu) -->
    <div id="email-update-modal" class="email-modal-backdrop" aria-hidden="true">
        <div class="email-modal-box">
            <div class="email-modal-header">
                <button type="button" class="email-modal-close" onclick="closeEmailModal()" title="Đóng">✕</button>
                <span class="em-icon">📧</span>
                <h3>Cập nhật Email nhận OTP</h3>
                <p>Email này sẽ được dùng để xác minh bảo mật khi đổi mật khẩu</p>
            </div>
            <div class="email-modal-body">
                <label for="email-modal-input-field" class="email-modal-label">📮 Địa chỉ Email mới (Gmail / Outlook):</label>
                <input type="email" id="email-modal-input-field" class="email-modal-input"
                    placeholder="vd: name@gmail.com"
                    autocomplete="email"
                    spellcheck="false">
                <label for="email-modal-password-field" class="email-modal-label">🔐 Mật khẩu hiện tại (để xác nhận):</label>
                <input type="password" id="email-modal-password-field" class="email-modal-input"
                    placeholder="Nhập mật khẩu đang dùng"
                    autocomplete="current-password">
                <div class="email-modal-hint">💡 Nhập email thật để nhận mã OTP xác thực khi bạn muốn đổi mật khẩu.</div>
                <div id="email-modal-alert" class="email-modal-alert"></div>
                <div class="email-modal-actions">
                    <button type="button" class="email-modal-btn-cancel" onclick="closeEmailModal()">Hủy bỏ</button>
                    <button type="button" id="email-modal-btn-save" class="email-modal-btn-save" onclick="submitEmailUpdate()">
                        <span>✅</span> Lưu Email Mới
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endauth

    <script>
        function toggleUserSidebar() {
            if (window.innerWidth <= 760) {
                const sidebar = document.querySelector('[data-sidebar]');
                if (sidebar) sidebar.classList.toggle('open');
            } else {
                document.body.classList.toggle('sidebar-collapsed');
                const isCollapsed = document.body.classList.contains('sidebar-collapsed');
                localStorage.setItem('user_sidebar_collapsed', isCollapsed ? '1' : '0');
            }
        }

        // Hàm toàn cục cập nhật tức thì Ví Sao Thưởng trên Topbar kèm hiệu ứng rung lắc
        window.updateGlobalStarWallet = function(stars) {
            const el = document.getElementById('topbar-reward-stars');
            if (el) {
                el.innerText = '⭐ ' + Number(stars).toLocaleString('vi-VN');
                const card = el.closest('.star-wallet-card');
                if (card) {
                    card.classList.remove('star-wallet-pulse');
                    void card.offsetWidth; // trigger reflow
                    card.classList.add('star-wallet-pulse');
                }
            }
        };

        // --- XỬ LÝ MODAL HỒ SƠ & ĐỔI MẬT KHẨU ---
        window.openUserProfileModal = function() {
            var modal = document.getElementById('user-profile-modal');
            if (modal) {
                modal.style.setProperty('display', 'flex', 'important');
                modal.style.setProperty('opacity', '1', 'important');
                modal.style.setProperty('visibility', 'visible', 'important');
                modal.style.setProperty('pointer-events', 'auto', 'important');
                modal.classList.add('open');
                modal.setAttribute('aria-hidden', 'false');
            }
        };

        window.closeUserProfileModal = function() {
            var modal = document.getElementById('user-profile-modal');
            if (modal) {
                modal.classList.remove('open');
                modal.setAttribute('aria-hidden', 'true');
                modal.style.setProperty('display', 'none', 'important');
                modal.style.setProperty('opacity', '0', 'important');
                modal.style.setProperty('visibility', 'hidden', 'important');
                modal.style.setProperty('pointer-events', 'none', 'important');
            }
            var alertBox = document.getElementById('pwd-alert');
            if (alertBox) {
                alertBox.className = 'profile-alert';
                alertBox.textContent = '';
            }
        };

        document.querySelectorAll('[data-open-profile-modal]').forEach(el => {
            el.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                window.openUserProfileModal();
            });
        });

        document.querySelectorAll('[data-close-profile-modal]').forEach(el => {
            el.addEventListener('click', window.closeUserProfileModal);
        });

        const profileModalEl = document.getElementById('user-profile-modal');
        profileModalEl?.addEventListener('click', (e) => {
            if (e.target === profileModalEl) window.closeUserProfileModal();
        });

        // Chuyển đổi giữa 2 tab: Hồ sơ & Đổi mật khẩu
        window.switchProfileTab = function(tabName, btnEl) {
            document.querySelectorAll('.user-profile-tab-btn').forEach(b => b.classList.remove('active'));
            if (btnEl) btnEl.classList.add('active');

            document.querySelectorAll('.user-tab-panel').forEach(p => p.classList.remove('active'));
            const targetPanel = document.getElementById('panel-profile-' + tabName);
            if (targetPanel) targetPanel.classList.add('active');
        };

        // Bật / tắt ẩn hiện mật khẩu
        window.togglePasswordVisibility = function(inputId, btnEl) {
            const input = document.getElementById(inputId);
            if (!input) return;
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            btnEl.textContent = isPassword ? '🙈' : '👁️';
        };

        // Gửi mã OTP qua Email
        let otpCountdownTimer = null;
        window.requestPasswordOtp = function() {
            const btn = document.getElementById('btn-request-otp');
            const timerText = document.getElementById('otp-timer-text');
            const alertBox = document.getElementById('pwd-alert');
            if (!btn || !alertBox) return;

            btn.disabled = true;
            const originalBtnHtml = btn.innerHTML;
            btn.innerHTML = '<span>⏳</span> Đang gửi...';

            alertBox.className = 'profile-alert';
            alertBox.textContent = '';

            fetch("{{ route('profile.send-otp') }}", {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
                }
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok) {
                    throw new Error(data.message || 'Không thể gửi mã OTP. Vui lòng thử lại sau.');
                }
                return data;
            })
            .then(data => {
                alertBox.className = 'profile-alert success';
                alertBox.textContent = '✓ ' + (data.message || 'Mã OTP đã được gửi về email!');

                // Đếm ngược 60 giây
                let countdown = 60;
                if (timerText) {
                    timerText.style.display = 'block';
                    timerText.textContent = `⏱️ Gửi lại mã sau ${countdown} giây`;
                }
                if (otpCountdownTimer) clearInterval(otpCountdownTimer);
                otpCountdownTimer = setInterval(() => {
                    countdown--;
                    if (countdown <= 0) {
                        clearInterval(otpCountdownTimer);
                        btn.disabled = false;
                        btn.innerHTML = originalBtnHtml;
                        if (timerText) timerText.style.display = 'none';
                    } else {
                        btn.innerHTML = `<span>⏳</span> ${countdown}s`;
                        if (timerText) timerText.textContent = `⏱️ Gửi lại mã sau ${countdown} giây`;
                    }
                }, 1000);
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = originalBtnHtml;
                alertBox.className = 'profile-alert error';
                alertBox.textContent = '✕ ' + err.message;
            });
        };

        // Xử lý gửi form Đổi mật khẩu qua AJAX
        window.handlePasswordSubmit = function(e) {
            e.preventDefault();
            const form = document.getElementById('form-change-password');
            const btn = document.getElementById('btn-submit-password');
            const alertBox = document.getElementById('pwd-alert');
            if (!form || !btn || !alertBox) return;

            const pwd = document.getElementById('input-new-password')?.value;
            const pwdConf = document.getElementById('input-password-confirmation')?.value;
            const otp = document.getElementById('input-otp')?.value;

            if (!otp || otp.trim().length !== 6) {
                alertBox.className = 'profile-alert error';
                alertBox.textContent = '✕ Vui lòng bấm "Nhận OTP" và điền mã xác thực 6 số gửi về email.';
                return;
            }

            if (pwd !== pwdConf) {
                alertBox.className = 'profile-alert error';
                alertBox.textContent = '✕ Xác nhận mật khẩu mới không trùng khớp. Vui lòng kiểm tra lại.';
                return;
            }

            const formData = new FormData(form);
            const originalBtnHtml = btn.innerHTML;
            btn.disabled = true;
            btn.style.opacity = '0.7';
            btn.innerHTML = '<span>⏳</span> Đang xác thực & cập nhật...';

            alertBox.className = 'profile-alert';
            alertBox.textContent = '';

            fetch("{{ route('profile.password') }}", {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || formData.get('_token')
                },
                body: formData
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok) {
                    throw new Error(data.message || (data.errors ? Object.values(data.errors).flat()[0] : 'Có lỗi xảy ra khi đổi mật khẩu.'));
                }
                return data;
            })
            .then(data => {
                // Đóng modal profile
                window.closeUserProfileModal && window.closeUserProfileModal();
                if (otpCountdownTimer) clearInterval(otpCountdownTimer);

                // Hiện Toast thành công đẹp
                const toast = document.getElementById('pwd-success-toast');
                const toastBd = document.getElementById('pwd-success-backdrop');
                const countdownEl = document.getElementById('pwd-toast-countdown');
                const barEl = document.getElementById('pwd-toast-bar');

                if (toast && toastBd) {
                    toast.classList.add('show');
                    toastBd.classList.add('show');

                    // Đếm ngược 3 giây với thanh tiến độ
                    let remaining = 3;
                    if (countdownEl) countdownEl.innerHTML = 'Chuyển trang sau <b>' + remaining + '</b> giây...';
                    if (barEl) { barEl.style.transition = 'none'; barEl.style.width = '100%'; }
                    setTimeout(() => { if (barEl) { barEl.style.transition = 'width 3s linear'; barEl.style.width = '0%'; } }, 50);

                    const tick = setInterval(() => {
                        remaining--;
                        if (remaining > 0) {
                            if (countdownEl) countdownEl.innerHTML = 'Chuyển trang sau <b>' + remaining + '</b> giây...';
                        } else {
                            clearInterval(tick);
                            window.location.href = '{{ route('home') }}';
                        }
                    }, 1000);
                } else {
                    // Fallback nếu không có toast
                    setTimeout(() => { window.location.href = '{{ route('home') }}'; }, 1200);
                }
            })
            .catch(err => {
                alertBox.className = 'profile-alert error';
                alertBox.textContent = '✕ ' + err.message;
                btn.disabled = false;
                btn.style.opacity = '1';
                btn.innerHTML = originalBtnHtml;
            });
        };

        // Cập nhật địa chỉ email thật của người dùng
        // ======= Modal Đổi Email Inline (Không dùng prompt() xấu) =======
        window.promptUpdateEmail = function() {
            const currentEmail = document.getElementById('user-email-text')?.textContent.trim() || '';
            const inputEl = document.getElementById('email-modal-input-field');
            const alertEl = document.getElementById('email-modal-alert');
            if (inputEl) inputEl.value = currentEmail;
            if (alertEl) { alertEl.className = 'email-modal-alert'; alertEl.textContent = ''; }

            const modal = document.getElementById('email-update-modal');
            if (modal) {
                modal.classList.add('open');
                modal.removeAttribute('aria-hidden');
                setTimeout(() => inputEl && inputEl.focus(), 180);
            }
        };

        window.closeEmailModal = function() {
            const modal = document.getElementById('email-update-modal');
            if (modal) {
                modal.classList.remove('open');
                modal.setAttribute('aria-hidden', 'true');
            }
        };

        window.submitEmailUpdate = function() {
            const inputEl = document.getElementById('email-modal-input-field');
            const alertEl = document.getElementById('email-modal-alert');
            const btnSave = document.getElementById('email-modal-btn-save');
            const currentEmail = document.getElementById('user-email-text')?.textContent.trim() || '';

            const newEmail = inputEl ? inputEl.value.trim() : '';
            const currentPassword = document.getElementById('email-modal-password-field')?.value || '';

            // Kiểm tra cơ bản
            if (!currentPassword) {
                alertEl.className = 'email-modal-alert error';
                alertEl.textContent = '⚠️ Vui lòng nhập mật khẩu hiện tại để xác nhận.';
                document.getElementById('email-modal-password-field')?.focus();
                return;
            }
            if (!newEmail) {
                alertEl.className = 'email-modal-alert error';
                alertEl.textContent = '⚠️ Vui lòng nhập địa chỉ email.';
                inputEl && inputEl.focus();
                return;
            }
            if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(newEmail)) {
                alertEl.className = 'email-modal-alert error';
                alertEl.textContent = '⚠️ Địa chỉ email không đúng định dạng (ví dụ: name@gmail.com).';
                inputEl && inputEl.focus();
                return;
            }
            if (newEmail === currentEmail) {
                alertEl.className = 'email-modal-alert error';
                alertEl.textContent = '⚠️ Email mới trùng với email hiện tại, không cần cập nhật.';
                return;
            }

            // Gửi lên server
            btnSave.disabled = true;
            btnSave.innerHTML = '<span>⏳</span> Đang lưu...';

            fetch("{{ route('profile.update-email') }}", {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
                },
                body: JSON.stringify({ email: newEmail, current_password: currentPassword })
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok) {
                    throw new Error(data.message || (data.errors ? Object.values(data.errors).flat()[0] : 'Không thể cập nhật email.'));
                }
                return data;
            })
            .then(data => {
                const updated = data.email;
                // Cập nhật UI ngay tức thì
                const el1 = document.getElementById('user-email-text');
                if (el1) el1.textContent = updated;
                const el2 = document.getElementById('security-hint-email');
                if (el2) el2.textContent = updated;

                // Hiện thông báo thành công trong modal rồi tự đóng
                alertEl.className = 'email-modal-alert success';
                alertEl.textContent = '✓ ' + data.message;
                btnSave.innerHTML = '<span>✅</span> Đã lưu!';

                setTimeout(() => window.closeEmailModal(), 1800);
            })
            .catch(err => {
                alertEl.className = 'email-modal-alert error';
                alertEl.textContent = '✕ ' + err.message;
                btnSave.disabled = false;
                btnSave.innerHTML = '<span>✅</span> Lưu Email Mới';
            });
        };

        // ----- Đổi số điện thoại (cần mật khẩu hiện tại) -----
        window.openPhoneModal = function() {
            const modal = document.getElementById('phone-update-modal');
            const current = document.getElementById('user-phone-text')?.textContent.trim() || '';
            const input = document.getElementById('phone-modal-input-field');
            if (!modal || !input) return;
            input.value = /^0\d{9}$/.test(current) ? current : '';
            document.getElementById('phone-modal-password-field').value = '';
            const alertEl = document.getElementById('phone-modal-alert');
            alertEl.className = 'email-modal-alert';
            alertEl.textContent = '';
            modal.classList.add('open');
            modal.removeAttribute('aria-hidden');
            setTimeout(() => input.focus(), 180);
        };
        window.closePhoneModal = function() {
            const modal = document.getElementById('phone-update-modal');
            if (modal) { modal.classList.remove('open'); modal.setAttribute('aria-hidden', 'true'); }
        };
        window.submitPhoneUpdate = function() {
            const input = document.getElementById('phone-modal-input-field');
            const password = document.getElementById('phone-modal-password-field')?.value || '';
            const alertEl = document.getElementById('phone-modal-alert');
            const btn = document.getElementById('phone-modal-btn-save');
            const phone = (input?.value || '').replace(/[\s.\-()]/g, '');
            const fail = (msg, el) => { alertEl.className = 'email-modal-alert error'; alertEl.textContent = '⚠️ ' + msg; el && el.focus(); };
            if (!/^(0|\+?84)\d{9}$/.test(phone)) return fail('Số điện thoại gồm 10 chữ số, ví dụ 0912345678.', input);
            if (!password) return fail('Vui lòng nhập mật khẩu hiện tại để xác nhận.', document.getElementById('phone-modal-password-field'));

            btn.disabled = true;
            btn.innerHTML = '<span>⏳</span> Đang lưu...';
            fetch("{{ route('profile.update-phone') }}", {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}' },
                body: JSON.stringify({ phone, current_password: password })
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || (data.errors ? Object.values(data.errors).flat()[0] : 'Không thể cập nhật số điện thoại.'));
                return data;
            })
            .then(data => {
                const el = document.getElementById('user-phone-text');
                if (el) el.textContent = data.phone;
                alertEl.className = 'email-modal-alert success';
                alertEl.textContent = '✓ ' + data.message;
                btn.innerHTML = '<span>✅</span> Đã lưu!';
                setTimeout(() => { window.closePhoneModal(); btn.disabled = false; btn.innerHTML = '<span>✅</span> Lưu số điện thoại'; }, 1600);
            })
            .catch(err => {
                alertEl.className = 'email-modal-alert error';
                alertEl.textContent = '✕ ' + err.message;
                btn.disabled = false;
                btn.innerHTML = '<span>✅</span> Lưu số điện thoại';
            });
        };
        document.getElementById('phone-update-modal')?.addEventListener('click', function(e) {
            if (e.target === this) window.closePhoneModal();
        });

        // Đóng modal đổi email khi bấm ra ngoài
        document.getElementById('email-update-modal')?.addEventListener('click', function(e) {
            if (e.target === this) window.closeEmailModal();
        });

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') {
                window.closeUserProfileModal();
            }
        });
    </script>

    <x-ai-upgrade-modal />
    <x-voice-chat />
    <x-support-chat-widget />
    <x-support-ai-widget />
</body>
</html>
