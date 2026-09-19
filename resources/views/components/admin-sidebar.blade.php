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
                <div class="group-right-wrap">
                    <svg class="chevron-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </div>
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
                <div class="group-right-wrap">
                    <svg class="chevron-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </div>
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
                <div class="group-right-wrap">
                    <svg class="chevron-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </div>
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
                    <a href="{{ route('programs') }}" class="nav-sub-item" style="background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.4);">
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
                <div class="group-right-wrap">
                    <svg class="chevron-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </div>
            </button>
            <div class="nav-submenu">
                <a href="{{ route('admin.games.settings') }}" class="nav-sub-item {{ $currentRoute === 'admin.games.settings' ? 'on' : '' }}" title="Cài đặt quy đổi Sao & Giờ chơi mini-game">
                    <span class="sub-icon">⚙️</span> <span class="nav-text">Cài đặt Khu Trò Chơi</span>
                </a>
                <a href="{{ asset('games/bao-ve-em-be.html') }}" target="_blank" class="nav-sub-item" style="color: #7dd3fc;" title="Mở phòng chơi game điều khiển bằng Camera chuyển động">
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
                <div class="group-right-wrap">
                    <span class="group-counter red" id="group-badge-orders" style="{{ $pendingOrdersCount > 0 ? '' : 'display:none;' }}">{{ $pendingOrdersCount }}</span>
                    <svg class="chevron-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </div>
            </button>
            <div class="nav-submenu">
                @if($isDashboard)
                    <a href="javascript:void(0)" class="nav-sub-item {{ $activeTab === 'tab-orders' ? 'on' : '' }}" data-tab="tab-orders" onclick="switchAdminTab('tab-orders', this)" title="Đối chiếu thanh toán và phê duyệt đơn thuê gói">
                        <span class="sub-icon">📋</span> <span class="nav-text">Quản Lý Đơn Hàng</span>
                        <span class="badge-counter red" id="sub-badge-orders" style="{{ $pendingOrdersCount > 0 ? '' : 'display:none;' }}" title="Có {{ $pendingOrdersCount }} đơn hàng đang chờ duyệt">{{ $pendingOrdersCount }} chờ</span>
                    </a>
                    <a href="javascript:void(0)" class="nav-sub-item {{ $activeTab === 'tab-packages' ? 'on' : '' }}" data-tab="tab-packages" onclick="switchAdminTab('tab-packages', this)" title="Cấu hình danh mục gói dịch vụ bản quyền">
                        <span class="sub-icon">📦</span> <span class="nav-text">Danh Mục Gói Dịch Vụ</span>
                    </a>
                @else
                    <a href="{{ route('admin.dashboard') }}#tab-orders" class="nav-sub-item" title="Đối chiếu thanh toán và phê duyệt đơn thuê gói">
                        <span class="sub-icon">📋</span> <span class="nav-text">Quản Lý Đơn Hàng</span>
                        <span class="badge-counter red" id="sub-badge-orders" style="{{ $pendingOrdersCount > 0 ? '' : 'display:none;' }}" title="Có {{ $pendingOrdersCount }} đơn hàng đang chờ duyệt">{{ $pendingOrdersCount }} chờ</span>
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
                <div class="group-right-wrap">
                    <span class="group-counter amber" id="group-badge-chat" style="{{ $pendingSupportCount > 0 ? '' : 'display:none;' }}">{{ $pendingSupportCount }}</span>
                    <svg class="chevron-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </div>
            </button>
            <div class="nav-submenu">
                @if($isDashboard)
                    <a href="javascript:void(0)" class="nav-sub-item {{ $activeTab === 'tab-chat' ? 'on' : '' }}" data-tab="tab-chat" onclick="switchAdminTab('tab-chat', this)" title="Trung tâm Chat Messenger & Hỗ trợ Giáo viên">
                        <span class="sub-icon">⚡</span> <span class="nav-text">Tin Nhắn Messenger</span>
                        <span class="badge-counter amber" id="sub-badge-chat" style="{{ $pendingSupportCount > 0 ? '' : 'display:none;' }}" title="Có {{ $pendingSupportCount }} tin nhắn mới">{{ $pendingSupportCount }}</span>
                    </a>
                @else
                    <a href="{{ route('admin.dashboard') }}#tab-chat" class="nav-sub-item" title="Trung tâm Chat Messenger & Hỗ trợ Giáo viên">
                        <span class="sub-icon">⚡</span> <span class="nav-text">Tin Nhắn Messenger</span>
                        <span class="badge-counter amber" id="sub-badge-chat" style="{{ $pendingSupportCount > 0 ? '' : 'display:none;' }}" title="Có {{ $pendingSupportCount }} tin nhắn mới">{{ $pendingSupportCount }}</span>
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
                <div class="group-right-wrap">
                    <svg class="chevron-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </div>
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
       🌊 VIVID ROYAL BLUE ADMIN SIDEBAR (HIGH CONTRAST - CỰC KỲ DỄ ĐỌC)
       ========================================================================= */
    .admin-unified-sidebar {
        position: sticky;
        top: 0;
        height: 100vh;
        max-height: 100vh;
        padding: 16px 12px 14px;
        /* Nền Xanh Dương Hoàng Gia - Đồng bộ hoàn toàn với Topbar */
        background: linear-gradient(180deg, #0284c7 0%, #0369a1 45%, #075985 100%);
        color: #ffffff;
        display: flex;
        flex-direction: column;
        border-right: 1.5px solid rgba(255, 255, 255, 0.18);
        box-shadow: 4px 0 20px rgba(7, 89, 133, 0.25);
        z-index: 50;
        transition: width 0.22s cubic-bezier(0.4, 0, 0.2, 1), padding 0.22s ease;
        overflow: hidden;
        box-sizing: border-box;
    }

    /* 1. Brand & Logo */
    .brand {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0 2px 14px;
        padding-bottom: 12px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        text-decoration: none;
    }
    .brand-logo-img {
        width: 40px;
        height: 40px;
        border-radius: 11px;
        object-fit: cover;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.25);
        border: 2px solid rgba(255, 255, 255, 0.4);
        flex-shrink: 0;
    }
    .brand-text {
        font-size: 18px;
        font-weight: 900;
        letter-spacing: -0.3px;
        color: #ffffff !important;
        line-height: 1.15;
    }
    .brand-text span {
        color: #67e8f9 !important;
    }
    .brand-text small {
        display: block;
        font-size: 9.5px;
        font-weight: 800;
        color: #dbeafe !important;
        letter-spacing: 1.2px;
        margin-top: 2px;
    }

    /* 2. Admin Badge / Profile Box */
    .admin-badge-box {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 10px;
        background: rgba(255, 255, 255, 0.14);
        border: 1.5px solid rgba(255, 255, 255, 0.25);
        border-radius: 11px;
        margin-bottom: 14px;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: background-color 0.15s ease, border-color 0.15s ease;
    }
    .admin-badge-box:hover {
        background: rgba(255, 255, 255, 0.22);
        border-color: rgba(255, 255, 255, 0.45);
    }
    .avatar {
        width: 34px;
        height: 34px;
        border-radius: 9px;
        display: grid;
        place-items: center;
        font-size: 13px;
        font-weight: 900;
        color: #ffffff;
        border: 1.5px solid rgba(255, 255, 255, 0.5);
        flex-shrink: 0;
    }
    .admin-info b {
        display: block;
        font-size: 13px;
        color: #ffffff !important;
        font-weight: 850;
    }
    .admin-info small {
        display: block;
        font-size: 11px;
        color: #dbeafe !important;
        font-weight: 750;
        margin-top: 1px;
    }

    /* 3. Accordion Nav - Visual Hierarchy & High Contrast */
    .sidebar-accordion {
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
        padding-right: 2px;
        margin-bottom: 10px;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .sidebar-accordion::-webkit-scrollbar {
        width: 4px;
    }
    .sidebar-accordion::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.3);
        border-radius: 999px;
    }

    .nav-group {
        border-radius: 9px;
        margin-bottom: 3px;
        transition: all 0.2s ease;
    }

    /* TIÊU ĐỀ NHÓM: DẠNG HEADER SECTION RÕ NÉT, TÁCH BIỆT TRANG CON */
    .nav-group-header {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 7.5px 9px;
        background: rgba(15, 23, 42, 0.25) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        border-radius: 8px;
        color: #bae6fd !important;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.6px;
        cursor: pointer;
        transition: all 0.15s ease;
        text-align: left;
    }
    .nav-group-header .group-title {
        color: inherit !important;
        font-weight: 800;
        font-size: 11px;
    }
    .nav-group-header:hover {
        background: rgba(15, 23, 42, 0.4) !important;
        color: #ffffff !important;
        border-color: rgba(255, 255, 255, 0.25) !important;
    }
    .nav-group.open .nav-group-header {
        background: rgba(15, 23, 42, 0.5) !important;
        color: #ffffff !important;
        border-left: 3.5px solid #38bdf8 !important;
        border-color: rgba(56, 189, 248, 0.4) !important;
    }
    .group-title-wrap {
        display: flex;
        align-items: center;
        gap: 7px;
        min-width: 0;
    }
    .group-right-wrap {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-shrink: 0;
    }
    .group-icon {
        font-size: 13px;
        width: 22px;
        height: 22px;
        display: inline-grid;
        place-items: center;
        background: rgba(255, 255, 255, 0.12);
        border-radius: 6px;
        flex-shrink: 0;
    }
    .nav-group.open .group-icon {
        background: rgba(56, 189, 248, 0.3);
    }
    .chevron-svg {
        transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        color: #7dd3fc;
        flex-shrink: 0;
    }
    .nav-group.open .chevron-svg {
        transform: rotate(90deg);
        color: #ffffff;
    }

    /* BADGE ĐẾM SỐ TRÊN HEADER NHÓM */
    .group-counter {
        font-size: 10px;
        font-weight: 900;
        padding: 1px 6px;
        border-radius: 999px;
        line-height: 1.3;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25);
    }
    .group-counter.red {
        background: #ef4444;
        color: #ffffff;
        border: 1px solid #fca5a5;
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.5);
    }
    .group-counter.amber {
        background: #f59e0b;
        color: #ffffff;
        border: 1px solid #fde68a;
        box-shadow: 0 2px 8px rgba(245, 158, 11, 0.5);
    }

    /* SUBMENU ITEMS: DANH SÁCH NHÁNH CÂY THỤT LỀ, CÓ VIỀN RIÊNG */
    .nav-submenu {
        display: none;
        flex-direction: column;
        gap: 3px;
        padding: 4px 0 5px 12px;
        margin-left: 12px;
        border-left: 2px dashed rgba(56, 189, 248, 0.4);
    }
    .nav-group.open .nav-submenu {
        display: flex;
    }
    .nav-sub-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 7px 10px;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.12);
        color: #f8fafc !important;
        font-size: 12.5px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.15s ease;
        position: relative;
    }
    .nav-sub-item .nav-text {
        color: #f8fafc !important;
        font-weight: 600;
    }
    /* RÊ CHUỘT: TĂNG ĐỘ SÁNG MỜ ÊM ÁI */
    .nav-sub-item:hover {
        background: rgba(255, 255, 255, 0.22) !important;
        border-color: rgba(255, 255, 255, 0.35) !important;
        color: #ffffff !important;
    }
    .nav-sub-item:hover .nav-text {
        color: #ffffff !important;
    }
    /* KHI MỤC ĐƯỢC CHỌN (ACTIVE / ON): NỔI KHỐI 3D TRẮNG CHỮ XANH ĐẬM TƯƠNG PHẢN TUYỆT ĐỐI */
    .nav-sub-item.on, .nav-sub-item.active {
        background: #ffffff !important;
        color: #0284c7 !important;
        font-weight: 900 !important;
        border-radius: 8px;
        border-color: #ffffff !important;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.25), inset 0 -2px 0 rgba(2, 132, 199, 0.2);
    }
    .nav-sub-item.on .nav-text, .nav-sub-item.active .nav-text {
        color: #0284c7 !important;
        font-weight: 900 !important;
    }
    .nav-sub-item.on .sub-icon, .nav-sub-item.active .sub-icon {
        color: #0284c7 !important;
    }
    .nav-sub-item .sub-icon {
        font-size: 13.5px;
        flex-shrink: 0;
    }

    /* BADGE TRONG TRANG CON */
    .badge-counter {
        font-size: 10px;
        font-weight: 900;
        padding: 2px 7px;
        border-radius: 999px;
        margin-left: auto;
        flex-shrink: 0;
        letter-spacing: 0.2px;
    }
    .badge-counter.red {
        background: #ef4444;
        color: #ffffff;
        border: 1px solid #fca5a5;
        box-shadow: 0 2px 6px rgba(239, 68, 68, 0.4);
    }
    .badge-counter.amber {
        background: #f59e0b;
        color: #ffffff;
        border: 1px solid #fde68a;
        box-shadow: 0 2px 6px rgba(245, 158, 11, 0.4);
    }
    .badge-counter.blue {
        background: #0284c7;
        color: #ffffff;
        border: 1px solid #7dd3fc;
    }

    /* 4. Chân Sidebar: Tinh gọn & Phẳng */
    .sidebar-footer {
        padding-top: 10px;
        border-top: 1px solid rgba(255, 255, 255, 0.2);
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .btn-sidebar-student-switch {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 7.5px 10px;
        border-radius: 8px;
        background: #059669;
        color: #ffffff !important;
        font-size: 12px;
        font-weight: 800;
        text-decoration: none;
        border: 1px solid #34d399;
        transition: background-color 0.15s ease;
    }
    .btn-sidebar-student-switch:hover {
        background: #047857;
    }
    .btn-sidebar-logout {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 7px 10px;
        border-radius: 8px;
        background: rgba(239, 68, 68, 0.25);
        border: 1px solid rgba(239, 68, 68, 0.5);
        color: #ffffff !important;
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
        transition: background-color 0.15s ease;
    }
    .btn-sidebar-logout:hover {
        background: #ef4444;
        border-color: #ef4444;
    }

    /* 🔄 COLLAPSED SIDEBAR */
    .shell.sidebar-collapsed .admin-unified-sidebar {
        width: 70px !important;
        padding: 14px 6px !important;
    }
    .shell.sidebar-collapsed .brand-text,
    .shell.sidebar-collapsed .admin-info,
    .shell.sidebar-collapsed .group-title,
    .shell.sidebar-collapsed .group-right-wrap,
    .shell.sidebar-collapsed .chevron-svg,
    .shell.sidebar-collapsed .nav-text,
    .shell.sidebar-collapsed .badge-counter,
    .shell.sidebar-collapsed .btn-sidebar-student-switch span:last-child,
    .shell.sidebar-collapsed .btn-sidebar-logout span:last-child {
        display: none !important;
    }
    .shell.sidebar-collapsed .brand {
        justify-content: center;
        margin-bottom: 12px;
        padding-bottom: 8px;
    }
    .shell.sidebar-collapsed .admin-badge-box {
        justify-content: center;
        padding: 5px;
    }
    .shell.sidebar-collapsed .nav-group-header {
        justify-content: center;
        padding: 8px 0;
    }
    .shell.sidebar-collapsed .nav-submenu {
        padding: 2px 0;
        margin-left: 0;
        border-left: none;
    }
    .shell.sidebar-collapsed .nav-sub-item {
        justify-content: center;
        padding: 8px 0;
    }
    .shell.sidebar-collapsed .btn-sidebar-student-switch,
    .shell.sidebar-collapsed .btn-sidebar-logout {
        padding: 8px 0;
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

    // ⚡ REAL-TIME SIDEBAR BADGE COUNTERS (Cập nhật số đơn & tin nhắn tức thì qua Fetch)
    function updateGlobalSidebarBadges(data) {
        if (!data) return;
        const orders = parseInt(data.pending_orders_count) || 0;
        const msgs = parseInt(data.pending_count) || 0;

        // 1. Badge Đơn Hàng Mới
        const grpOrders = document.getElementById('group-badge-orders');
        if (grpOrders) {
            grpOrders.textContent = orders;
            grpOrders.style.display = orders > 0 ? 'inline-flex' : 'none';
        }
        const subOrders = document.getElementById('sub-badge-orders');
        if (subOrders) {
            subOrders.textContent = orders + ' chờ';
            subOrders.style.display = orders > 0 ? 'inline-block' : 'none';
        }

        // 2. Badge Tin Nhắn Chờ Tư Vấn
        const grpChat = document.getElementById('group-badge-chat');
        if (grpChat) {
            grpChat.textContent = msgs;
            grpChat.style.display = msgs > 0 ? 'inline-flex' : 'none';
        }
        const subChat = document.getElementById('sub-badge-chat');
        if (subChat) {
            subChat.textContent = msgs;
            subChat.style.display = msgs > 0 ? 'inline-block' : 'none';
        }

        // 3. Topbar Chat Button
        const topbarChat = document.getElementById('topbar-chat-count');
        if (topbarChat) {
            topbarChat.textContent = msgs;
            topbarChat.style.display = msgs > 0 ? 'inline-flex' : 'none';
        }

        // 4. Chat Tab Filter Chip (nếu đang ở trang quản trị)
        const chipPending = document.getElementById('chip-filter-pending');
        if (chipPending) {
            chipPending.textContent = 'Chờ (' + msgs + ')';
        }
    }

    // Polling định kỳ mỗi 3.5s để đồng bộ số lượng tức thì
    if (!window._adminBadgePollTimer) {
        window._adminBadgePollTimer = setInterval(function() {
            fetch('/quan-tri/tin-nhan/realtime-poll?last_id=0&active_id=0')
                .then(res => res.json())
                .then(data => {
                    if (data && data.ok) {
                        updateGlobalSidebarBadges(data);
                    }
                })
                .catch(() => {});
        }, 3500);
    }
</script>
