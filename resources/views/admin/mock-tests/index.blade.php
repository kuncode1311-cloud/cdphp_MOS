<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản Trị Bộ Đề Thi Thử IC3 GS6 — MOS Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script>
        if (localStorage.getItem('admin_sidebar_collapsed') === '1') {
            document.documentElement.classList.add('admin-sidebar-collapsed-init');
        }
    </script>
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --primary-light: #eef2ff;
            --primary-border: #c7d2fe;
            --bg-body: #f8fafc;
            --surface: #ffffff;
            --border: #e2e8f0;
            --border-strong: #cbd5e1;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --radius-sm: 10px;
            --radius-md: 14px;
            --radius-lg: 18px;
            --radius-xl: 24px;
            --shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 14px -2px rgba(15, 23, 42, 0.08);
            --shadow-lg: 0 12px 28px -4px rgba(15, 23, 42, 0.12);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: radial-gradient(circle at 10% 15%, rgba(254, 240, 138, 0.35) 0%, transparent 45%),
                        radial-gradient(circle at 90% 85%, rgba(199, 210, 254, 0.38) 0%, transparent 45%),
                        radial-gradient(circle at 50% 50%, rgba(240, 253, 250, 0.35) 0%, transparent 60%),
                        #f8fafc;
            background-attachment: fixed;
            color: var(--text-main);
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            -webkit-font-smoothing: antialiased;
            min-height: 100vh;
        }

        .shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 270px minmax(0, 1fr);
            transition: grid-template-columns 0.22s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .shell.sidebar-collapsed,
        html.admin-sidebar-collapsed-init .shell {
            grid-template-columns: 70px minmax(0, 1fr);
        }
        html.admin-sidebar-collapsed-init .shell {
            transition: none !important;
        }

        .main-workspace {
            display: flex;
            flex-direction: column;
            min-width: 0;
            min-height: 100vh;
        }
        .main-content {
            padding: 24px 32px 48px;
            flex: 1;
            min-width: 0;
            max-width: 1560px;
            margin: 0 auto;
            width: 100%;
        }

        /* 3D Hero Banner Đồng Bộ Admin */
        .hero-banner {
            background: linear-gradient(135deg, #1d4ed8 0%, #0ea5e9 62%, #14b8a6 100%);
            border-radius: 22px;
            padding: 22px 28px;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            border: 3.5px solid #ffffff;
            box-shadow: 0 18px 38px rgba(14, 116, 144, 0.22), inset 0 -5px 0 rgba(3, 105, 161, 0.34);
            margin-bottom: 24px;
            position: relative;
            overflow: hidden;
        }
        .hero-banner::before {
            content: '';
            position: absolute;
            top: -40px; right: -40px;
            width: 180px; height: 180px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 70%);
            pointer-events: none;
        }
        .hero-content {
            max-width: 820px;
            position: relative;
            z-index: 2;
        }
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(8px);
            font-size: 11px;
            font-weight: 850;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 8px;
            border: 1px solid rgba(255,255,255,0.3);
        }
        .hero-content h1 {
            font-size: 28px;
            font-weight: 900;
            margin: 0 0 7px;
            letter-spacing: -0.3px;
            line-height: 1.25;
        }
        .hero-content p {
            margin: 0;
            font-size: 13.5px;
            color: #e0f2fe;
            font-weight: 600;
            line-height: 1.55;
            opacity: 0.95;
        }
        .hero-action {
            display: flex;
            align-items: center;
            gap: 12px;
            position: relative;
            z-index: 2;
            flex-shrink: 0;
        }

        /* 3D Tactile Buttons */
        .btn-create-3d {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            border-radius: 15px;
            background: linear-gradient(180deg, #fbbf24, #f97316);
            color: #ffffff;
            font-size: 14px;
            font-weight: 950;
            text-decoration: none;
            border: 2.5px solid #ffffff;
            box-shadow: 0 7px 0 #c2410c, 0 14px 24px rgba(217, 119, 6, 0.34);
            transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1);
            white-space: nowrap;
        }
        .btn-create-3d:hover {
            transform: translateY(-2px);
            box-shadow: 0 9px 0 #c2410c, 0 18px 28px rgba(217, 119, 6, 0.42);
        }
        .btn-create-3d:active {
            transform: translateY(2px);
            box-shadow: 0 3px 0 #c2410c, 0 7px 12px rgba(217, 119, 6, 0.28);
        }

        /* 3D Tactile Stat Pods */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .stat-card {
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            border: 2px solid #dbeafe;
            border-radius: 18px;
            padding: 16px 18px;
            box-shadow: 0 10px 22px rgba(15, 23, 42, 0.08), inset 0 -4px 0 rgba(37, 99, 235, 0.06);
            display: flex;
            align-items: center;
            gap: 16px;
            transition: all 0.2s ease;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            border-color: #93c5fd;
            box-shadow: 0 14px 28px rgba(37, 99, 235, 0.12);
        }
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            font-size: 24px;
            flex-shrink: 0;
            border: 2px solid #ffffff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
        }
        .stat-val {
            font-size: 23px;
            font-weight: 900;
            line-height: 1.1;
            margin: 3px 0;
            color: #0f172a;
        }
        .stat-lbl {
            font-size: 11px;
            font-weight: 750;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* Grade Selection Tabs */
        .grade-tabs-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 18px;
            flex-wrap: wrap;
        }
        .grade-tabs {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding-bottom: 2px;
        }
        .grade-tab {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: 14px;
            background: #ffffff;
            border: 2px solid #e2e8f0;
            color: #334155;
            font-weight: 800;
            font-size: 14px;
            text-decoration: none;
            box-shadow: 0 2px 6px rgba(0,0,0,0.03);
            transition: all 0.18s ease;
        }
        .grade-tab:hover {
            border-color: #93c5fd;
            background: #f8fafc;
            transform: translateY(-1px);
        }
        .grade-tab.active {
            background: linear-gradient(135deg, #4f46e5, #4338ca);
            border-color: #4338ca;
            color: #ffffff;
            box-shadow: 0 6px 16px rgba(79, 70, 229, 0.35);
        }

        /* Tests Grid */
        .test-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(330px, 1fr));
            gap: 18px;
        }
        .mock-card {
            background: linear-gradient(180deg, #ffffff 0%, #f4f8ff 100%);
            border-radius: 20px;
            border: 2px solid #bfdbfe;
            box-shadow: 0 12px 26px rgba(30, 64, 175, 0.10), inset 0 -5px 0 rgba(37, 99, 235, 0.08);
            padding: 18px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
        }
        .mock-card::before {
            content: '';
            position: absolute;
            inset: 0 0 auto;
            height: 7px;
            background: linear-gradient(90deg, #38bdf8, #6366f1, #22c55e);
        }
        .mock-card:hover {
            transform: translateY(-3px);
            border-color: #60a5fa;
            box-shadow: 0 18px 34px rgba(37, 99, 235, 0.16), inset 0 -5px 0 rgba(37, 99, 235, 0.10);
        }
        .mock-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin: 6px 0 14px;
        }
        .mock-title {
            font-size: 16px;
            font-weight: 900;
            color: #071630;
            line-height: 1.32;
        }
        .mock-status {
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 10.5px;
            font-weight: 950;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .status-pub {
            background: linear-gradient(180deg, #dcfce7, #bbf7d0);
            color: #047857;
            border: 1.5px solid #4ade80;
        }
        .status-unpub {
            background: linear-gradient(180deg, #f8fafc, #e2e8f0);
            color: #64748b;
            border: 1.5px solid #cbd5e1;
        }

        .mock-meta-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            background: rgba(255, 255, 255, 0.78);
            border-radius: 14px;
            padding: 8px;
            margin-bottom: 16px;
            border: 1.5px solid #dbeafe;
        }
        .meta-stat {
            text-align: center;
            background: #f8fbff;
            border: 1px solid #e0e7ff;
            border-radius: 11px;
            padding: 8px 6px;
        }
        .meta-stat small {
            display: block;
            font-size: 9.5px;
            font-weight: 900;
            color: #64748b;
            margin-bottom: 2px;
            text-transform: uppercase;
        }
        .meta-stat b {
            font-size: 14px;
            font-weight: 900;
            color: #0284c7;
        }

        .mock-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-action {
            flex: 1;
            padding: 10px 12px;
            border-radius: 13px;
            font-size: 12.5px;
            font-weight: 950;
            text-decoration: none;
            text-align: center;
            border: 1.5px solid transparent;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .btn-play {
            background: linear-gradient(180deg, #22c55e, #059669);
            color: #ffffff;
            border-color: #86efac;
            box-shadow: 0 5px 0 #047857, 0 10px 18px rgba(16, 185, 129, 0.26);
        }
        .btn-play:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 0 #047857, 0 14px 24px rgba(16, 185, 129, 0.34);
        }
        .btn-edit {
            background: linear-gradient(180deg, #eef2ff, #c7d2fe);
            color: #3730a3;
            border-color: #a5b4fc;
            box-shadow: 0 5px 0 #a5b4fc, 0 10px 18px rgba(99, 102, 241, 0.16);
        }
        .btn-edit:hover {
            background: linear-gradient(180deg, #e0e7ff, #a5b4fc);
            color: #312e81;
            transform: translateY(-2px);
        }
        .btn-del {
            padding: 10px 12px;
            background: linear-gradient(180deg, #fff1f2, #fecaca);
            color: #dc2626;
            border: 1.5px solid #fca5a5;
            border-radius: 13px;
            cursor: pointer;
            font-weight: 950;
            font-size: 13px;
            transition: all 0.15s;
            box-shadow: 0 5px 0 #fca5a5, 0 10px 18px rgba(239, 68, 68, 0.14);
        }
        .btn-del:hover {
            background: linear-gradient(180deg, #fee2e2, #fca5a5);
            transform: translateY(-2px);
        }

        /* Alert Box */
        .alert-ok {
            padding: 14px 20px;
            border-radius: 14px;
            background: #ecfdf5;
            border: 1.5px solid #a7f3d0;
            color: #065f46;
            font-weight: 800;
            font-size: 13.5px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: var(--shadow-sm);
        }

        /* Empty State */
        .empty-state {
            background: #ffffff;
            border-radius: var(--radius-xl);
            border: 3px dashed #cbd5e1;
            padding: 64px 24px;
            text-align: center;
            margin-top: 10px;
        }
        .empty-icon {
            font-size: 56px;
            margin-bottom: 14px;
        }
        .empty-title {
            font-size: 20px;
            font-weight: 900;
            color: #0f172a;
            margin-bottom: 8px;
        }
        .empty-desc {
            font-size: 14px;
            color: #64748b;
            max-width: 520px;
            margin: 0 auto 24px;
            line-height: 1.6;
            font-weight: 500;
        }

        @media (max-width: 1100px) {
            .shell { grid-template-columns: 1fr; }
            .side { display: none; }
            .main-content { padding: 20px 16px 40px; }
            .hero-banner { flex-direction: column; align-items: flex-start; }
            .hero-action { width: 100%; }
            .btn-create-3d { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>

<div class="shell" id="admin-shell">
    <!-- 1. Sidebar Quản Trị Viên Đồng Bộ 100% -->
    <x-admin-sidebar active-route="admin.mock-tests.index" active-group="exams" />

    <!-- 2. Không Gian Làm Việc Chính -->
    <div class="main-workspace">
        <!-- Topbar Quản Trị Đồng Bộ -->
        <x-admin-topbar title="Quản Trị Bộ Đề Thi Thử IC3 GS6" breadcrumb="🏆 Chuyên Môn Đề Thi > Bộ Đề Thi Thử" />

        <main class="main-content">
            @if(session('ok'))
                <div class="alert-ok">
                    <span>✓</span> {{ session('ok') }}
                </div>
            @endif

            <!-- 3D Hero Banner -->
            <section class="hero-banner">
                <div class="hero-content">
                    @php
                        $topicCount = $selectedLevel ? $selectedLevel->topics()->count() : 0;
                    @endphp
                    <span class="hero-badge">🏆 ĐỀ THI THỬ IC3 GS6</span>
                    <h1>Bộ Đề Thi Thử Tổng Hợp</h1>
                    <p>Chọn lọc câu hỏi từ {{ $topicCount }} chủ đề, cấu hình thời lượng và điểm đạt để học sinh luyện thi trong giao diện như thi thật.</p>
                </div>
                <div class="hero-action">
                    <a class="btn-create-3d" href="{{ route('admin.mock-tests.create', ['grade' => $selectedGrade]) }}">
                        <span>＋</span> Soạn đề mới
                    </a>
                </div>
            </section>

            <!-- 3-Pod Stats Overview -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #eef2ff; color: #4f46e5;">🏆</div>
                    <div>
                        <div class="stat-lbl">Tổng bộ đề</div>
                        <div class="stat-val">{{ number_format($totalMockTestsAll ?? 0) }} <small style="font-size: 13px; font-weight: 700; color: #64748b;">Bộ Đề</small></div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: #f0fdf4; color: #16a34a;">⚡</div>
                    <div>
                        <div class="stat-lbl">Khối đang chọn</div>
                        <div class="stat-val">Khối {{ $selectedGrade }} <small style="font-size: 13px; font-weight: 700; color: #16a34a;">(Spark {{ $selectedGrade - 2 }})</small></div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: #f0f9ff; color: #0284c7;">📚</div>
                    <div>
                        <div class="stat-lbl">Câu hỏi Khối {{ $selectedGrade }}</div>
                        <div class="stat-val" style="color: #0284c7;">{{ number_format($totalQuestionsInGrade ?? 0) }} <small style="font-size: 13px; font-weight: 700; color: #64748b;">Câu Sẵn Sàng</small></div>
                    </div>
                </div>
            </div>

            <!-- Grade Tabs Switcher -->
            <div class="grade-tabs-bar">
                <div class="grade-tabs">
                    @foreach($levels as $lvl)
                        <a class="grade-tab {{ $selectedGrade === $lvl->grade ? 'active' : '' }}" href="{{ route('admin.mock-tests.index', ['grade' => $lvl->grade]) }}">
                            <span>⚡</span> Khối {{ $lvl->grade }} · Spark {{ $lvl->grade - 2 }}
                        </a>
                    @endforeach
                </div>
                <div style="font-size: 13px; font-weight: 750; color: #64748b;">
                    Hiển thị <b>{{ $mockTests->count() }}</b> đề thi thử Khối {{ $selectedGrade }}
                </div>
            </div>

            <!-- Tests Grid -->
            @if($mockTests->isNotEmpty())
                <div class="test-grid">
                    @foreach($mockTests as $test)
                        <article class="mock-card">
                            <div>
                                <div class="mock-header">
                                    <h3 class="mock-title">{{ $test->name }}</h3>
                                    <span class="mock-status {{ $test->is_published ? 'status-pub' : 'status-unpub' }}">
                                        {{ $test->is_published ? 'Đang mở' : 'Đang ẩn' }}
                                    </span>
                                </div>

                                <div class="mock-meta-grid">
                                    <div class="meta-stat">
                                        <small>SỐ CÂU</small>
                                        <b>{{ $test->mock_questions_count ?: $test->question_count }} câu</b>
                                    </div>
                                    <div class="meta-stat">
                                        <small>THỜI LƯỢNG</small>
                                        <b>{{ $test->duration_minutes }} phút</b>
                                    </div>
                                    <div class="meta-stat">
                                        <small>ĐIỂM ĐẠT</small>
                                        <b>{{ $test->pass_score }}/1000</b>
                                    </div>
                                </div>
                            </div>

                            <div class="mock-actions">
                                <a class="btn-action btn-play" href="{{ route('tests.launch', $test->slug) }}?preview=1" target="_blank" title="Mở phòng thi thử giao diện học sinh để kiểm tra đề">
                                    ▶ Làm thử
                                </a>
                                <a class="btn-action btn-edit" href="{{ route('admin.mock-tests.edit', $test) }}" title="Chỉnh sửa câu hỏi hoặc thời lượng đề thi">
                                    ✏️ Sửa
                                </a>
                                <form method="post" action="{{ route('admin.mock-tests.destroy', $test) }}" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bộ đề thi thử \'{{ $test->name }}\'?')">
                                    @csrf
                                    @method('delete')
                                    <button class="btn-del" type="submit" title="Xóa đề thi thử này">🗑️</button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-icon">📝</div>
                    <h3 class="empty-title">Chưa có Bộ Đề Thi Thử nào cho Khối {{ $selectedGrade }}</h3>
                    <p class="empty-desc">Hãy tạo bộ đề thi thử đầu tiên để học sinh Khối {{ $selectedGrade }} được rèn luyện phòng thi chuẩn quốc tế Certiport/IIG nhé!</p>
                    <a class="btn-create-3d" href="{{ route('admin.mock-tests.create', ['grade' => $selectedGrade]) }}">
                        <span>＋</span> Bắt Đầu Soạn Đề Thi Thử Ngay
                    </a>
                </div>
            @endif
        </main>
    </div>
</div>

<script>
    function syncMockTestSidebarStateBeforeNavigate() {
        const shell = document.getElementById('admin-shell');
        if (!shell) return;

        const isCollapsed = localStorage.getItem('admin_sidebar_collapsed') === '1'
            || document.documentElement.classList.contains('admin-sidebar-collapsed-init')
            || shell.classList.contains('sidebar-collapsed');

        shell.classList.toggle('sidebar-collapsed', isCollapsed);
        document.documentElement.classList.toggle('admin-sidebar-collapsed-init', isCollapsed);
    }

    document.querySelectorAll('.grade-tab').forEach(tab => {
        tab.addEventListener('click', syncMockTestSidebarStateBeforeNavigate);
    });

    window.addEventListener('pageshow', syncMockTestSidebarStateBeforeNavigate);
</script>

</body>
</html>
