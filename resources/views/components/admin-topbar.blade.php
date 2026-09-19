{{-- 
    Component: Admin Topbar (Thanh Điều Hướng Trên Cùng Quản Trị Viên)
    Trực quan, thông minh, tách biệt rõ ràng với body và đồng bộ trên mọi trang Admin.
--}}
@props([
    'title' => 'Trung Tâm Phân Tích & Báo Cáo Điểm Số',
    'breadcrumb' => '📊 Báo cáo & Luyện thi',
    'showSwitchPortal' => true,
])

@php
    $user = auth()->user();
    $isTeacher = $user?->isTeacher();
    $pendingSupportCount = $pendingSupportCount ?? (\App\Models\SupportMessage::where('status', 'pending')->count());
    $isDashboard = request()->routeIs('admin.dashboard', 'admin.overview');
@endphp

<header class="admin-unified-topbar" id="admin-unified-topbar">
    <!-- Cụm bên trái: Nút Toggle Sidebar & Tiêu đề trang thoáng đãng -->
    <div class="topbar-left">
        <button type="button" class="btn-topbar-sidebar-toggle" onclick="toggleAdminSidebar()" title="Đóng / Mở thanh menu bên trái (Ctrl+B)" aria-label="Đóng mở thanh bên">
            <span>☰</span>
        </button>
        <div class="topbar-heading-group">
            <div class="topbar-breadcrumbs">
                <span class="bc-dot">●</span>
                <span id="topbar-breadcrumb-tab" class="bc-active">{{ $breadcrumb }}</span>
            </div>
            <h1 id="topbar-title" class="topbar-main-title">
                {{ $title }}
            </h1>
        </div>
    </div>

    <!-- Cụm bên phải: Tinh giản tối đa, chỉ giữ các thao tác thiết yếu -->
    <div class="topbar-right">
        @if($isTeacher)
            <a href="{{ route('pricing.index') }}" class="btn-topbar-action btn-upgrade-gold" title="Xem bảng giá và nâng cấp gói bản quyền IC3 GS6">
                <span>✨</span> <span>Nâng Cấp Gói</span>
            </a>
        @endif

        @if($showSwitchPortal)
            <a href="{{ route('home') }}" class="btn-topbar-action btn-student-portal" title="Chuyển nhanh sang Cổng Học Sinh để kiểm tra bài luyện">
                <span>🚀</span> <span>Cổng Học Sinh</span>
            </a>
        @endif

        @if(! $isTeacher)
            @if($isDashboard)
                <button type="button" class="btn-topbar-chat" onclick="typeof switchAdminTab === 'function' ? switchAdminTab('tab-chat') : (window.location.hash = '#tab-chat', window.location.reload())" title="Mở Trung Tâm Live Chat Tư Vấn">
                    <span>💬</span> <span>Chat Tư Vấn</span>
                    @if($pendingSupportCount > 0)
                        <span class="chat-badge-pulse" id="topbar-chat-count">{{ $pendingSupportCount }}</span>
                    @endif
                </button>
            @else
                <a href="{{ route('admin.dashboard') }}#tab-chat" class="btn-topbar-chat" title="Mở Trung Tâm Live Chat Tư Vấn">
                    <span>💬</span> <span>Chat Tư Vấn</span>
                    @if($pendingSupportCount > 0)
                        <span class="chat-badge-pulse">{{ $pendingSupportCount }}</span>
                    @endif
                </a>
            @endif
        @endif

        <!-- Avatar Người Dùng Tinh Gọn (Click mở Modal Profile / Đổi mật khẩu) -->
        <button type="button" class="topbar-avatar-btn" onclick="typeof openMyProfileModal === 'function' ? openMyProfileModal() : (window.location.href='{{ route('admin.dashboard') }}#profile')" title="Tài khoản: {{ $user?->name ?? 'Admin' }} ({{ $isTeacher ? 'Giáo Viên' : 'Quản Trị Viên' }}) — Bấm để đổi mật khẩu / xem hồ sơ">
            <div class="pill-avatar" style="background: {{ $isTeacher ? 'linear-gradient(135deg, #10b981, #06b6d4)' : 'linear-gradient(135deg, #ef4444, #f59e0b)' }};">
                {{ $isTeacher ? 'GV' : 'AD' }}
            </div>
            <span class="online-indicator" title="Tài khoản đang hoạt động trực tuyến"></span>
        </button>
    </div>
</header>

<style>
    /* =========================================================================
       ✨ TOPBAR STYLING - XANH DƯƠNG TƯƠI SÁNG (VIVID ROYAL BLUE) TÁCH BIỆT 100% VỚI BODY
       ========================================================================= */
    .admin-unified-topbar {
        position: sticky;
        top: 0;
        z-index: 40;
        height: 66px;
        padding: 0 24px;
        background: linear-gradient(90deg, #0284c7 0%, #0369a1 55%, #075985 100%);
        border-bottom: 2.5px solid #034f78;
        box-shadow: 0 4px 18px rgba(2, 132, 199, 0.22);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        box-sizing: border-box;
    }

    .topbar-left {
        display: flex;
        align-items: center;
        gap: 14px;
        min-width: 0;
        flex: 1;
    }

    .btn-topbar-sidebar-toggle {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        border: 1.5px solid rgba(255, 255, 255, 0.35);
        background: rgba(255, 255, 255, 0.16);
        color: #ffffff;
        font-size: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.15s ease;
        flex-shrink: 0;
        backdrop-filter: blur(4px);
    }
    .btn-topbar-sidebar-toggle:hover {
        background: rgba(255, 255, 255, 0.28);
        border-color: #ffffff;
        color: #ffffff;
    }
    .btn-topbar-sidebar-toggle:active {
        transform: translateY(2px);
    }

    .topbar-heading-group {
        min-width: 0;
        flex: 1;
    }

    .topbar-breadcrumbs {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 11.5px;
        margin-bottom: 2px;
    }
    .bc-dot {
        color: #38bdf8;
        font-size: 8px;
        line-height: 1;
    }
    .bc-active {
        color: #bae6fd;
        font-weight: 750;
        letter-spacing: 0.3px;
    }

    .topbar-main-title {
        font-size: 17.5px;
        font-weight: 850;
        color: #ffffff;
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        letter-spacing: -0.3px;
        line-height: 1.25;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.18);
    }

    /* Topbar Right Actions - Tối giản */
    .topbar-right {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
    }

    .btn-topbar-action {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 14px;
        border-radius: 9px;
        font-size: 12.5px;
        font-weight: 800;
        text-decoration: none;
        transition: background 0.15s ease, transform 0.15s ease;
        cursor: pointer;
    }
    .btn-student-portal {
        background: #059669;
        color: #ffffff !important;
        border: 1.5px solid #34d399;
        box-shadow: 0 2px 8px rgba(5, 150, 105, 0.3);
    }
    .btn-student-portal:hover {
        background: #047857;
        transform: translateY(-1px);
    }
    .btn-student-portal:active {
        transform: translateY(1px);
    }

    .btn-upgrade-gold {
        background: #f59e0b;
        color: #ffffff !important;
        border: 1.5px solid #fde68a;
        box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3);
    }
    .btn-upgrade-gold:hover {
        background: #d97706;
        transform: translateY(-1px);
    }

    .btn-topbar-chat {
        position: relative;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 14px;
        border-radius: 9px;
        font-size: 12.5px;
        font-weight: 800;
        background: rgba(255, 255, 255, 0.18);
        color: #ffffff !important;
        border: 1.5px solid rgba(255, 255, 255, 0.35);
        cursor: pointer;
        text-decoration: none;
        transition: background 0.15s ease, transform 0.15s ease;
        backdrop-filter: blur(4px);
    }
    .btn-topbar-chat:hover {
        background: rgba(255, 255, 255, 0.28);
        border-color: #ffffff;
        transform: translateY(-1px);
    }
    .btn-topbar-chat:active {
        transform: translateY(1px);
    }
    .chat-badge-pulse {
        background: #ef4444;
        color: #ffffff;
        font-size: 10px;
        font-weight: 900;
        padding: 1px 6px;
        border-radius: 999px;
        box-shadow: 0 2px 6px rgba(239, 68, 68, 0.4);
        animation: chatPulse 2s infinite;
    }
    @keyframes chatPulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.15); }
    }

    /* Avatar Button Tinh Gọn */
    .topbar-avatar-btn {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        background: transparent;
        border: none;
        cursor: pointer;
        outline: none;
        border-radius: 50%;
        transition: transform 0.18s cubic-bezier(0.16, 1, 0.3, 1);
        margin-left: 4px;
    }
    .topbar-avatar-btn:hover {
        transform: scale(1.08);
    }
    .topbar-avatar-btn:active {
        transform: scale(0.95);
    }
    .pill-avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: grid;
        place-items: center;
        font-size: 13px;
        font-weight: 900;
        color: #ffffff;
        border: 2px solid #ffffff;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }
    .online-indicator {
        position: absolute;
        bottom: 0;
        right: 0;
        width: 10px;
        height: 10px;
        background: #10b981;
        border: 2px solid #e8f2fe;
        border-radius: 50%;
        box-shadow: 0 0 6px #10b981;
    }

    @media (max-width: 900px) {
        .admin-unified-topbar { padding: 0 16px; height: 62px; }
        .topbar-main-title { font-size: 15.5px; }
    }
    @media (max-width: 680px) {
        .btn-student-portal span:last-child,
        .btn-topbar-chat span:last-child { display: none; }
        .topbar-breadcrumbs { display: none; }
    }
</style>

<script>
    function toggleAdminSidebar() {
        const shell = document.querySelector('.shell');
        if (!shell) return;
        shell.classList.toggle('sidebar-collapsed');
        const isCollapsed = shell.classList.contains('sidebar-collapsed');
        localStorage.setItem('admin_sidebar_collapsed', isCollapsed ? '1' : '0');
    }

    // Auto restore sidebar state
    if (localStorage.getItem('admin_sidebar_collapsed') === '1') {
        document.querySelector('.shell')?.classList.add('sidebar-collapsed');
    }
</script>
