{{-- 
    Component: Admin Sidebar (Menu Thanh Bên Quản Trị Viên Đa Cấp Thông Minh)
    Đồng bộ 100% trên toàn bộ các trang Quản trị: Dashboard, Cài đặt Game, Soạn đề, Đơn hàng, v.v.
--}}
@props([
    'activeTab' => 'tab-results',
    'activeRoute' => '',
    'activeGroup' => 'reports',
])

@php
    $user = auth()->user();
    $isTeacher = $user?->isTeacher();
    $isAdmin = $user?->isAdmin();
    $currentRoute = request()->route()?->getName() ?? $activeRoute;
    $isDashboard = request()->routeIs('admin.dashboard', 'admin.overview');

    // Xác định nhóm nào cần mở sẵn (auto-expanded)
    if ($currentRoute === 'admin.games.settings') {
        $activeGroup = 'games';
    } elseif ($currentRoute === 'admin.questions.studio') {
        $activeGroup = 'exams';
    }

    // Đếm số đơn chờ duyệt và số tin nhắn hỗ trợ nếu chưa có từ controller
    $pendingOrdersCount = $pendingOrdersCount ?? (\App\Models\PackageOrder::where('status', 'pending')->count());
    $pendingSupportCount = $pendingSupportCount ?? (\App\Models\SupportMessage::where('status', 'pending')->count());
@endphp

<aside class="side admin-unified-sidebar" id="admin-unified-sidebar">
    <!-- 1. Logo & Thương Hiệu 3D Xịn Sò -->
    <a href="{{ route('admin.dashboard') }}" class="brand" title="IC3 Quest — Trung tâm Điều Hành Quản Trị">
        <img src="{{ asset('images/ic3-quest-logo.png') }}" alt="IC3 Quest Logo" class="brand-logo-img">
        <div class="brand-text">
            <span>IC3</span> QUEST
            <small>HỆ THỐNG QUẢN TRỊ</small>
        </div>
    </a>

    <!-- 2. Thẻ Thông Tin Admin / Profile -->
    <div class="admin-badge-box" onclick="typeof openMyProfileModal === 'function' ? openMyProfileModal() : (window.location.href='{{ route('admin.dashboard') }}#profile')" title="Bấm để xem và sửa thông tin tài khoản">
        <div class="avatar" style="background: {{ $isTeacher ? 'linear-gradient(135deg, #10b981, #06b6d4)' : 'linear-gradient(135deg, #4f46e5, #7c3aed)' }};">
            {{ $isTeacher ? 'GV' : 'AD' }}
        </div>
        <div class="admin-info" style="min-width: 0;">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 4px;">
                <b style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $user?->name ?? 'Quản trị viên' }}</b>
                <span style="font-size: 11px; opacity: 0.8;" title="Chỉnh sửa thông tin">✏️</span>
            </div>
            <small>{{ $isTeacher ? '👩‍🏫 Giáo viên phụ trách' : '👑 Quản trị viên Tổng' }}</small>
        </div>
    </div>

    <!-- 3. Danh Mục Menu Đa Cấp Accordion Thông Minh -->
    <div class="sidebar-accordion" id="admin-sidebar-accordion">

        <!-- NHÓM 1: BÁO CÁO & THEO DÕI -->
        <div class="nav-group {{ $activeGroup === 'reports' ? 'open' : '' }}" id="nav-group-reports">
            <button type="button" class="nav-group-header" onclick="toggleNavGroup('nav-group-reports')">
                <div class="group-title-wrap">
                    <span class="group-icon">📊</span>
                    <span class="group-title">BÁO CÁO & THEO DÕI</span>
                </div>
                <span class="group-chevron">▾</span>
            </button>
            <div class="nav-submenu">
                @if($isDashboard)
                    <a href="javascript:void(0)" class="nav-sub-item {{ $activeTab === 'tab-results' ? 'on' : '' }}" data-tab="tab-results" onclick="switchAdminTab('tab-results', this)">
                        <span class="sub-icon">📈</span> <span class="nav-text">Kết quả & Báo cáo</span>
                    </a>
                @else
                    <a href="{{ route('admin.dashboard') }}#tab-results" class="nav-sub-item">
                        <span class="sub-icon">📈</span> <span class="nav-text">Kết quả & Báo cáo</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- NHÓM 2: QUẢN TRỊ TÀI KHOẢN -->
        <div class="nav-group {{ $activeGroup === 'school' ? 'open' : '' }}" id="nav-group-school">
            <button type="button" class="nav-group-header" onclick="toggleNavGroup('nav-group-school')">
                <div class="group-title-wrap">
                    <span class="group-icon">👥</span>
                    <span class="group-title">QUẢN TRỊ TÀI KHOẢN</span>
                </div>
                <span class="group-chevron">▾</span>
            </button>
            <div class="nav-submenu">
                @if($isTeacher)
                    @if($isDashboard)
                        <a href="javascript:void(0)" class="nav-sub-item {{ $activeTab === 'tab-users' ? 'on' : '' }}" data-tab="tab-users" onclick="switchAdminTab('tab-users', this)">
                            <span class="sub-icon">👨‍🎓</span> <span class="nav-text">Học sinh của tôi</span>
                        </a>
                    @else
                        <a href="{{ route('admin.dashboard') }}#tab-users" class="nav-sub-item">
                            <span class="sub-icon">👨‍🎓</span> <span class="nav-text">Học sinh của tôi</span>
                        </a>
                    @endif
                @else
                    @if($isDashboard)
                        <a href="javascript:void(0)" class="nav-sub-item {{ $activeTab === 'tab-users-teachers' ? 'on' : '' }}" data-tab="tab-users-teachers" onclick="switchAdminTab('tab-users', this, 'teacher')">
                            <span class="sub-icon">👩‍🏫</span> <span class="nav-text">Giáo Viên & Đối Tác</span>
                        </a>
                        <a href="javascript:void(0)" class="nav-sub-item {{ $activeTab === 'tab-users-students' ? 'on' : '' }}" data-tab="tab-users-students" onclick="switchAdminTab('tab-users', this, 'student')">
                            <span class="sub-icon">👨‍🎓</span> <span class="nav-text">Danh Sách Học Sinh</span>
                        </a>
                    @else
                        <a href="{{ route('admin.dashboard') }}#tab-users" class="nav-sub-item">
                            <span class="sub-icon">👩‍🏫</span> <span class="nav-text">Giáo Viên & Đối Tác</span>
                        </a>
                        <a href="{{ route('admin.dashboard') }}#tab-users" class="nav-sub-item">
                            <span class="sub-icon">👨‍🎓</span> <span class="nav-text">Danh Sách Học Sinh</span>
                        </a>
                    @endif
                @endif
            </div>
        </div>

        <!-- NHÓM 3: CHUYÊN MÔN ĐỀ THI -->
        <div class="nav-group {{ $activeGroup === 'exams' ? 'open' : '' }}" id="nav-group-exams">
            <button type="button" class="nav-group-header" onclick="toggleNavGroup('nav-group-exams')">
                <div class="group-title-wrap">
                    <span class="group-icon">📚</span>
                    <span class="group-title">CHUYÊN MÔN ĐỀ THI</span>
                </div>
                <span class="group-chevron">▾</span>
            </button>
            <div class="nav-submenu">
                @if(! $isTeacher)
                    <a href="{{ route('admin.questions.studio') }}" class="nav-sub-item studio-link {{ $currentRoute === 'admin.questions.studio' ? 'on' : '' }}" title="Mở Question Studio soạn đề trọn gói">
                        <span class="sub-icon">✍️</span> <span class="nav-text">Soạn đề thi IC3 ➔</span>
                    </a>
                    @if($isDashboard)
                        <a href="javascript:void(0)" class="nav-sub-item {{ $activeTab === 'tab-levels' ? 'on' : '' }}" data-tab="tab-levels" onclick="switchAdminTab('tab-levels', this)">
                            <span class="sub-icon">🔑</span> <span class="nav-text">Khung Chương trình & Khối</span>
                        </a>
                    @else
                        <a href="{{ route('admin.dashboard') }}#tab-levels" class="nav-sub-item">
                            <span class="sub-icon">🔑</span> <span class="nav-text">Khung Chương trình & Khối</span>
                        </a>
                    @endif
                @else
                    <a href="{{ route('programs') }}" class="nav-sub-item" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;">
                        <span class="sub-icon">🎮</span> <span class="nav-text">Cổng Luyện thi ➔</span>
                    </a>
                @endif
            </div>
        </div>

        @if(! $isTeacher)
        <!-- NHÓM 4: KHU TRÒ CHƠI & THƯỞNG -->
        <div class="nav-group {{ $activeGroup === 'games' ? 'open' : '' }}" id="nav-group-games">
            <button type="button" class="nav-group-header" onclick="toggleNavGroup('nav-group-games')">
                <div class="group-title-wrap">
                    <span class="group-icon">🎮</span>
                    <span class="group-title">KHU TRÒ CHƠI & THƯỞNG</span>
                </div>
                <span class="group-chevron">▾</span>
            </button>
            <div class="nav-submenu">
                <a href="{{ route('admin.games.settings') }}" class="nav-sub-item {{ $currentRoute === 'admin.games.settings' ? 'on' : '' }}" title="Cài đặt quy đổi Sao & Giờ chơi mini-game">
                    <span class="sub-icon">⚙️</span> <span class="nav-text">Cài đặt Khu Trò Chơi</span>
                </a>
                <a href="{{ asset('games/bao-ve-em-be.html') }}" target="_blank" class="nav-sub-item" style="color: #0284c7;" title="Mở phòng chơi game điều khiển bằng Camera chuyển động">
                    <span class="sub-icon">⚔️</span> <span class="nav-text">Chơi thử Song Kiếm ↗</span>
                </a>
            </div>
        </div>
        @endif

        @if(! $isTeacher)
        <!-- NHÓM 5: GÓI & BẢN QUYỀN -->
        <div class="nav-group {{ $activeGroup === 'packages' ? 'open' : '' }}" id="nav-group-packages">
            <button type="button" class="nav-group-header" onclick="toggleNavGroup('nav-group-packages')">
                <div class="group-title-wrap">
                    <span class="group-icon">💎</span>
                    <span class="group-title">GÓI & BẢN QUYỀN</span>
                </div>
                <span class="group-chevron">▾</span>
            </button>
            <div class="nav-submenu">
                @if($isDashboard)
                    <a href="javascript:void(0)" class="nav-sub-item {{ $activeTab === 'tab-orders' ? 'on' : '' }}" data-tab="tab-orders" onclick="switchAdminTab('tab-orders', this)" title="Đối chiếu thanh toán và phê duyệt đơn thuê gói">
                        <span class="sub-icon">📋</span> <span class="nav-text">Quản Lý Đơn Hàng</span>
                        @if($pendingOrdersCount > 0)
                            <span class="badge-counter red" title="Có {{ $pendingOrdersCount }} đơn hàng đang chờ duyệt">{{ $pendingOrdersCount }} chờ</span>
                        @endif
                    </a>
                    <a href="javascript:void(0)" class="nav-sub-item {{ $activeTab === 'tab-packages' ? 'on' : '' }}" data-tab="tab-packages" onclick="switchAdminTab('tab-packages', this)" title="Cấu hình danh mục gói dịch vụ bản quyền">
                        <span class="sub-icon">📦</span> <span class="nav-text">Danh Mục Gói Dịch Vụ</span>
                    </a>
                @else
                    <a href="{{ route('admin.dashboard') }}#tab-orders" class="nav-sub-item" title="Đối chiếu thanh toán và phê duyệt đơn thuê gói">
                        <span class="sub-icon">📋</span> <span class="nav-text">Quản Lý Đơn Hàng</span>
                        @if($pendingOrdersCount > 0)
                            <span class="badge-counter red" title="Có {{ $pendingOrdersCount }} đơn hàng đang chờ duyệt">{{ $pendingOrdersCount }} chờ</span>
                        @endif
                    </a>
                    <a href="{{ route('admin.dashboard') }}#tab-packages" class="nav-sub-item" title="Cấu hình danh mục gói dịch vụ bản quyền">
                        <span class="sub-icon">📦</span> <span class="nav-text">Danh Mục Gói Dịch Vụ</span>
                    </a>
                @endif
                <a href="{{ route('pricing.index') }}" target="_blank" class="nav-sub-item" title="Xem bảng giá công khai của khách hàng">
                    <span class="sub-icon">🌐</span> <span class="nav-text">Xem Bảng Giá Khách ↗</span>
                </a>
            </div>
        </div>

        <!-- NHÓM 6: TƯ VẤN & LIVE CHAT -->
        <div class="nav-group {{ $activeGroup === 'chat' ? 'open' : '' }}" id="nav-group-support-chat">
            <button type="button" class="nav-group-header" onclick="toggleNavGroup('nav-group-support-chat')">
                <div class="group-title-wrap">
                    <span class="group-icon">💬</span>
                    <span class="group-title">TƯ VẤN & LIVE CHAT</span>
                </div>
                <span class="group-chevron">▾</span>
            </button>
            <div class="nav-submenu">
                @if($isDashboard)
                    <a href="javascript:void(0)" class="nav-sub-item {{ $activeTab === 'tab-chat' ? 'on' : '' }}" data-tab="tab-chat" onclick="switchAdminTab('tab-chat', this)" title="Trung tâm Chat Messenger & Hỗ trợ Giáo viên">
                        <span class="sub-icon">⚡</span> <span class="nav-text">Tin Nhắn Messenger</span>
                        @if($pendingSupportCount > 0)
                            <span class="badge-counter blue" title="Có {{ $pendingSupportCount }} tin nhắn mới">{{ $pendingSupportCount }}</span>
                        @endif
                    </a>
                @else
                    <a href="{{ route('admin.dashboard') }}#tab-chat" class="nav-sub-item" title="Trung tâm Chat Messenger & Hỗ trợ Giáo viên">
                        <span class="sub-icon">⚡</span> <span class="nav-text">Tin Nhắn Messenger</span>
                        @if($pendingSupportCount > 0)
                            <span class="badge-counter blue" title="Có {{ $pendingSupportCount }} tin nhắn mới">{{ $pendingSupportCount }}</span>
                        @endif
                    </a>
                @endif
            </div>
        </div>
        @else
        <!-- DÀNH CHO GIÁO VIÊN: GÓI DỊCH VỤ -->
        <div class="nav-group open" id="nav-group-teacher-packages">
            <button type="button" class="nav-group-header" onclick="toggleNavGroup('nav-group-teacher-packages')">
                <div class="group-title-wrap">
                    <span class="group-icon">💎</span>
                    <span class="group-title">GÓI BẢN QUYỀN</span>
                </div>
                <span class="group-chevron">▾</span>
            </button>
            <div class="nav-submenu">
                <a href="{{ route('pricing.index') }}" class="nav-sub-item" title="Bảng giá & Thuê gói bản quyền IC3 GS6">
                    <span class="sub-icon">✨</span> <span class="nav-text">Nâng Cấp Gói Giảng Dạy</span>
                </a>
                <a href="{{ route('pricing.history') }}" class="nav-sub-item" title="Xem lịch sử các đơn hàng đã đặt">
                    <span class="sub-icon">📜</span> <span class="nav-text">Lịch Sử Thuê Gói</span>
                </a>
            </div>
        </div>
        @endif

    </div>

    <!-- 4. Chân Sidebar: Nút Đăng Xuất & Link Cổng Học Sinh -->
    <div class="sidebar-footer">
        <a href="{{ route('home') }}" class="btn-sidebar-student-switch" title="Chuyển sang giao diện người học để kiểm tra bài luyện">
            <span>🚀</span> <span>Về Cổng Học Sinh</span>
        </a>
        <form class="logout" method="post" action="{{ route('logout') }}" style="margin: 0;">
            @csrf
            <button type="submit" class="btn-sidebar-logout" title="Đăng xuất khỏi hệ thống">
                <span>↪</span> <span>Đăng xuất</span>
            </button>
        </form>
    </div>
</aside>

<style>
    /* =========================================================================
       🌊 UNIFIED SMART ADMIN SIDEBAR STYLING (AQUA OCEAN & ICE PASTEL THEME)
       ========================================================================= */
    .admin-unified-sidebar {
        position: sticky;
        top: 0;
        height: 100vh;
        max-height: 100vh;
        padding: 18px 14px 14px;
        background: linear-gradient(180deg, #ffffff 0%, #f0fdfa 35%, #e0f2fe 100%);
        color: #0f172a;
        display: flex;
        flex-direction: column;
        border-right: 2px solid #bae6fd;
        box-shadow: 4px 0 24px rgba(2, 132, 199, 0.08);
        z-index: 50;
        transition: width 0.22s cubic-bezier(0.4, 0, 0.2, 1), padding 0.22s ease;
        overflow: hidden;
        box-sizing: border-box;
    }

    .brand {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 0 4px 16px;
        padding-bottom: 14px;
        border-bottom: 1.5px solid #e0f2fe;
        text-decoration: none;
    }
    .brand-logo-img {
        width: 44px;
        height: 44px;
        border-radius: 13px;
        object-fit: cover;
        box-shadow: 0 4px 12px rgba(2, 132, 199, 0.16);
        border: 2px solid #bae6fd;
        flex-shrink: 0;
        transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .brand:hover .brand-logo-img {
        transform: scale(1.06) rotate(-2deg);
        border-color: #0284c7;
    }
    .brand-text {
        font-size: 19px;
        font-weight: 1000;
        letter-spacing: 0.5px;
        color: #0f172a;
        line-height: 1.1;
    }
    .brand-text span {
        color: #0284c7;
    }
    .brand-text small {
        display: block;
        font-size: 9.5px;
        font-weight: 800;
        color: #0d9488;
        letter-spacing: 1.5px;
        margin-top: 3px;
    }

    /* Admin Badge / Profile Box */
    .admin-badge-box {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        background: #ffffff;
        border: 1.5px solid #bae6fd;
        border-radius: 14px;
        margin-bottom: 16px;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(2, 132, 199, 0.06);
        transition: all 0.2s ease;
    }
    .admin-badge-box:hover {
        background: #f0f9ff;
        border-color: #38bdf8;
        box-shadow: 0 4px 14px rgba(2, 132, 199, 0.12);
        transform: translateY(-1px);
    }
    .avatar {
        width: 38px;
        height: 38px;
        border-radius: 11px;
        display: grid;
        place-items: center;
        font-size: 14px;
        font-weight: 900;
        color: #ffffff;
        border: 2px solid #bae6fd;
        box-shadow: 0 3px 8px rgba(2, 132, 199, 0.18);
        flex-shrink: 0;
    }
    .admin-info b {
        display: block;
        font-size: 13.5px;
        color: #0f172a;
        font-weight: 850;
    }
    .admin-info small {
        display: block;
        font-size: 11px;
        color: #0284c7;
        font-weight: 750;
        margin-top: 2px;
    }

    /* Accordion Groups */
    .sidebar-accordion {
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
        padding-right: 4px;
        margin-bottom: 12px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .sidebar-accordion::-webkit-scrollbar {
        width: 4px;
    }
    .sidebar-accordion::-webkit-scrollbar-thumb {
        background: #bae6fd;
        border-radius: 999px;
    }
    .sidebar-accordion::-webkit-scrollbar-thumb:hover {
        background: #38bdf8;
    }

    .nav-group {
        border-radius: 12px;
        transition: all 0.2s ease;
    }
    .nav-group.open {
        background: rgba(240, 249, 255, 0.65);
        border: 1px solid #e0f2fe;
    }
    .nav-group-header {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 9px 12px;
        background: rgba(255, 255, 255, 0.75);
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        color: #334155;
        font-size: 11.5px;
        font-weight: 850;
        letter-spacing: 0.5px;
        cursor: pointer;
        transition: all 0.18s ease;
    }
    .nav-group-header:hover {
        color: #0284c7;
        background: #e0f2fe;
        border-color: #7dd3fc;
    }
    .nav-group.open .nav-group-header {
        background: linear-gradient(90deg, #e0f2fe 0%, #f0fdfa 100%);
        border-color: #7dd3fc;
        color: #0369a1;
        font-weight: 900;
    }
    .group-title-wrap {
        display: flex;
        align-items: center;
        gap: 9px;
    }
    .group-icon {
        font-size: 15px;
    }
    .group-chevron {
        font-size: 13px;
        transition: transform 0.25s ease;
        color: #64748b;
    }
    .nav-group.open .group-chevron {
        transform: rotate(180deg);
        color: #0284c7;
    }

    /* Submenu items */
    .nav-submenu {
        display: none;
        flex-direction: column;
        gap: 4px;
        padding: 6px 4px 8px 12px;
        margin-left: 12px;
        border-left: 2px solid #bae6fd;
    }
    .nav-group.open .nav-submenu {
        display: flex;
    }
    .nav-sub-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 12px;
        border-radius: 10px;
        color: #334155;
        font-size: 13px;
        font-weight: 750;
        text-decoration: none;
        transition: all 0.15s ease;
        border: 1px solid transparent;
        position: relative;
    }
    .nav-sub-item:hover {
        background: #e0f2fe;
        color: #0284c7;
        transform: translateX(3px);
    }
    .nav-sub-item.on, .nav-sub-item.active {
        background: linear-gradient(135deg, #0284c7 0%, #0d9488 100%);
        color: #ffffff !important;
        border-color: #38bdf8;
        box-shadow: 0 4px 14px rgba(2, 132, 199, 0.35);
        font-weight: 900;
    }
    .nav-sub-item.on .sub-icon, .nav-sub-item.active .sub-icon {
        color: #ffffff;
    }
    .nav-sub-item .sub-icon {
        font-size: 15px;
        flex-shrink: 0;
    }

    /* Badge Counter */
    .badge-counter {
        font-size: 10px;
        font-weight: 900;
        padding: 2px 7px;
        border-radius: 999px;
        margin-left: auto;
        flex-shrink: 0;
    }
    .badge-counter.red {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: #ffffff;
        box-shadow: 0 2px 6px rgba(239, 68, 68, 0.35);
    }
    .badge-counter.blue {
        background: linear-gradient(135deg, #0284c7, #0369a1);
        color: #ffffff;
        box-shadow: 0 2px 6px rgba(2, 132, 199, 0.35);
    }

    /* Sidebar Footer */
    .sidebar-footer {
        padding-top: 12px;
        border-top: 1.5px solid #e0f2fe;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .btn-sidebar-student-switch {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 9px 12px;
        border-radius: 10px;
        background: linear-gradient(135deg, #0d9488 0%, #059669 100%);
        border: 1px solid #34d399;
        color: #ffffff;
        font-size: 12.5px;
        font-weight: 850;
        text-decoration: none;
        box-shadow: 0 4px 12px rgba(13, 148, 136, 0.25);
        transition: all 0.18s ease;
    }
    .btn-sidebar-student-switch:hover {
        background: linear-gradient(135deg, #0f766e 0%, #047857 100%);
        color: #ffffff;
        box-shadow: 0 6px 16px rgba(13, 148, 136, 0.35);
        transform: translateY(-1px);
    }
    .btn-sidebar-logout {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 8.5px 12px;
        border-radius: 10px;
        background: #fff1f2;
        border: 1.5px solid #fecdd3;
        color: #e11d48;
        font-size: 12.5px;
        font-weight: 850;
        cursor: pointer;
        transition: all 0.18s ease;
    }
    .btn-sidebar-logout:hover {
        background: #e11d48;
        color: #ffffff;
        border-color: #e11d48;
        box-shadow: 0 4px 12px rgba(225, 29, 72, 0.25);
        transform: translateY(-1px);
    }

    /* =========================================================================
       🔄 SIDEBAR COLLAPSED MODE
       ========================================================================= */
    .shell.sidebar-collapsed .admin-unified-sidebar {
        width: 76px !important;
        padding: 16px 8px !important;
    }
    .shell.sidebar-collapsed .brand-text,
    .shell.sidebar-collapsed .admin-info,
    .shell.sidebar-collapsed .group-title,
    .shell.sidebar-collapsed .group-chevron,
    .shell.sidebar-collapsed .nav-text,
    .shell.sidebar-collapsed .badge-counter,
    .shell.sidebar-collapsed .btn-sidebar-student-switch span:last-child,
    .shell.sidebar-collapsed .btn-sidebar-logout span:last-child {
        display: none !important;
    }
    .shell.sidebar-collapsed .brand {
        justify-content: center;
        margin-bottom: 14px;
        padding-bottom: 10px;
    }
    .shell.sidebar-collapsed .admin-badge-box {
        justify-content: center;
        padding: 6px;
    }
    .shell.sidebar-collapsed .nav-group-header {
        justify-content: center;
        padding: 10px 0;
    }
    .shell.sidebar-collapsed .nav-submenu {
        padding: 4px 0;
        margin-left: 0;
        border-left: none;
    }
    .shell.sidebar-collapsed .nav-sub-item {
        justify-content: center;
        padding: 10px 0;
    }
    .shell.sidebar-collapsed .btn-sidebar-student-switch,
    .shell.sidebar-collapsed .btn-sidebar-logout {
        padding: 10px 0;
        justify-content: center;
    }
</style>

<script>
    function toggleNavGroup(groupId) {
        const group = document.getElementById(groupId);
        if (group) {
            group.classList.toggle('open');
        }
    }
</script>
