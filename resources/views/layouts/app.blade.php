{{-- Khung dùng chung: menu, thanh trên và tài nguyên CSS/JS. Nội dung từng trang được chèn ở yield. --}}
<!doctype html>
<html lang="vi" translate="no" class="notranslate">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="google" content="notranslate">
    <meta name="theme-color" content="#1070b8">
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
        body.sidebar-collapsed .app-sidebar .brand span:last-child,
        body.sidebar-collapsed .app-sidebar .side-nav a span,
        body.sidebar-collapsed .app-sidebar .side-nav a b,
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
            @if(auth()->user()?->canAccessAdmin())
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
        <div class="sidebar-quest">
            <span>🏆</span>
            <b>Thử thách tuần</b>
            <small>Hoàn thành 3 bài học</small>
            <div><i></i></div>
            <em>1/3 nhiệm vụ</em>
        </div>
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
                    <a class="vip-player-pill" href="{{ route('admin.dashboard') }}" style="border-color: #6ee7b7; background: #ffffff; text-decoration: none;">
                        <div style="width: 44px; height: 44px; border-radius: 50%; background: linear-gradient(135deg, #10b981, #059669); display: grid; place-items: center; font-size: 20px; color: #fff; box-shadow: 0 3px 6px rgba(0,0,0,0.15); flex-shrink: 0;">
                            👩‍🏫
                        </div>
                        <div class="player-details">
                            <b style="color: #065f46; font-size: 13.5px;">{{ auth()->user()->name }}</b>
                            <small>
                                <span class="player-level-badge" style="background: #ecfdf5; border-color: #a7f3d0; color: #047857; font-size: 10.5px; font-weight: 800;">👩‍🏫 Chế độ Giảng dạy</span>
                            </small>
                        </div>
                    </a>
                @elseif(auth()->user()->isAdmin())
                    <a class="vip-player-pill" href="{{ route('admin.dashboard') }}" style="border-color: #fca5a5; background: #ffffff; text-decoration: none;">
                        <div style="width: 44px; height: 44px; border-radius: 50%; background: linear-gradient(135deg, #ef4444, #dc2626); display: grid; place-items: center; font-size: 20px; color: #fff; box-shadow: 0 3px 6px rgba(0,0,0,0.15); flex-shrink: 0;">
                            👑
                        </div>
                        <div class="player-details">
                            <b style="color: #991b1b; font-size: 13.5px;">{{ auth()->user()->name }}</b>
                            <small>
                                <span class="player-level-badge" style="background: #fef2f2; border-color: #fecaca; color: #b91c1c; font-size: 10px; font-weight: 800;">👑 Quản trị viên</span>
                            </small>
                        </div>
                    </a>
                @else
                    <a class="vip-player-pill" href="{{ route('home') }}">
                        <img src="{{ asset('images/student-avatar.jpg') }}" alt="Avatar" class="player-avatar-img">
                        <div class="player-details">
                            <b>Explorer {{ auth()->user()->name }}</b>
                            <small>
                                <span>{{ auth()->user()->classroom?->name ?? 'Học sinh' }}</span> · 
                                <span class="player-level-badge" data-open-star-modal title="Tổng điểm bài thi tích lũy">🏆 {{ number_format(auth()->user()->attempts()->sum('score') ?? 0) }} Điểm</span>
                            </small>
                        </div>
                    </a>
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

        // 🚀 CÔNG NGHỆ INSTANT HOVER PREFETCH: Tải trước trang khi di chuột/chạm để chuyển trang tức thì (0ms)
        const prefetchedUrls = new Set();
        function prefetchInternalUrl(url) {
            if (!url || prefetchedUrls.has(url)) return;
            try {
                const u = new URL(url, window.location.origin);
                if (u.origin !== window.location.origin) return;
                if (u.pathname.endsWith('.pdf') || u.pathname.endsWith('.docx') || u.pathname.includes('/logout')) return;
                prefetchedUrls.add(url);

                const link = document.createElement('link');
                link.rel = 'prefetch';
                link.href = url;
                link.as = 'document';
                document.head.appendChild(link);
            } catch (e) {}
        }

        document.addEventListener('mouseover', function(e) {
            const a = e.target.closest('a');
            if (a && a.href && a.href.startsWith(window.location.origin)) {
                prefetchInternalUrl(a.href);
            }
        }, { passive: true });

        document.addEventListener('touchstart', function(e) {
            const a = e.target.closest('a');
            if (a && a.href && a.href.startsWith(window.location.origin)) {
                prefetchInternalUrl(a.href);
            }
        }, { passive: true });

        // 🖼️ TỰ ĐỘNG LƯU CACHE CÁC ẢNH NẶNG TRONG BỘ NHỚ (Chống giật/đơ khi chuyển trang)
        window.addEventListener('load', function() {
            const heavyImages = [
                '{{ asset("images/adventure-world-bg.jpg") }}',
                '{{ asset("images/leaderboard/fantasy-arena-bg.jpg") }}',
                '{{ asset("images/leaderboard/crest-banner-3d.png") }}',
                '{{ asset("images/leaderboard/crown-3d.png") }}',
                '{{ asset("images/leaderboard/hero-champion.png") }}',
                '{{ asset("images/leaderboard/hero-runnerup.png") }}',
                '{{ asset("images/leaderboard/hero-thirdplace.png") }}',
                '{{ asset("images/student-avatar.jpg") }}',
                '{{ asset("images/ic3-quest-logo.png") }}'
            ];
            const preloadImages = () => {
                heavyImages.forEach(src => {
                    const img = new Image();
                    img.src = src;
                });
            };
            if ('requestIdleCallback' in window) {
                requestIdleCallback(preloadImages);
            } else {
                setTimeout(preloadImages, 800);
            }
        });
    </script>
</body>
</html>
