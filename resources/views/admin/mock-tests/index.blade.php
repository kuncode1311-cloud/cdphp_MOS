<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản Trị Bộ Đề Thi Thử IC3 GS6 — MOS Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
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
        .shell.sidebar-collapsed {
            grid-template-columns: 76px minmax(0, 1fr);
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
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #0284c7 100%);
            border-radius: var(--radius-xl);
            padding: 28px 34px;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            border: 3.5px solid #ffffff;
            box-shadow: 0 16px 36px rgba(30, 64, 175, 0.18), inset 0 -4px 0 rgba(0,0,0,0.15);
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
            max-width: 760px;
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
            font-size: 11.5px;
            font-weight: 850;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 10px;
            border: 1px solid rgba(255,255,255,0.3);
        }
        .hero-content h1 {
            font-size: 26px;
            font-weight: 900;
            margin: 0 0 8px;
            letter-spacing: -0.3px;
            line-height: 1.25;
        }
        .hero-content p {
            margin: 0;
            font-size: 14px;
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
            padding: 13px 24px;
            border-radius: 16px;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #ffffff;
            font-size: 14.5px;
            font-weight: 850;
            text-decoration: none;
            border: 2.5px solid #ffffff;
            box-shadow: 0 8px 20px rgba(217, 119, 6, 0.4), inset 0 -3px 0 rgba(0,0,0,0.2);
            transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1);
            white-space: nowrap;
        }
        .btn-create-3d:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(217, 119, 6, 0.5), inset 0 -3px 0 rgba(0,0,0,0.2);
        }
        .btn-create-3d:active {
            transform: translateY(2px);
            box-shadow: 0 4px 10px rgba(217, 119, 6, 0.3);
        }

        /* 3D Tactile Stat Pods */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .stat-card {
            background: var(--surface);
            border: 2.5px solid #ffffff;
            border-radius: var(--radius-lg);
            padding: 18px 20px;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.05), inset 0 -3px 0 rgba(0, 0, 0, 0.03);
            display: flex;
            align-items: center;
            gap: 16px;
            transition: all 0.2s ease;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 26px rgba(15, 23, 42, 0.08);
        }
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            font-size: 24px;
            flex-shrink: 0;
            border: 2px solid #ffffff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
        }
        .stat-val {
            font-size: 24px;
            font-weight: 900;
            line-height: 1.1;
            margin: 3px 0;
            color: #0f172a;
        }
        .stat-lbl {
            font-size: 11.5px;
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
            margin-bottom: 20px;
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
            padding: 10px 20px;
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
            grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
            gap: 20px;
        }
        .mock-card {
            background: #ffffff;
            border-radius: var(--radius-xl);
            border: 2.5px solid #ffffff;
            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.06), inset 0 -4px 0 rgba(0,0,0,0.04);
            padding: 24px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
        }
        .mock-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 18px 36px rgba(15, 23, 42, 0.1);
        }
        .mock-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
        }
        .mock-title {
            font-size: 17px;
            font-weight: 900;
            color: #0f172a;
            line-height: 1.35;
        }
        .mock-status {
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 850;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .status-pub {
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }
        .status-unpub {
            background: #f1f5f9;
            color: #64748b;
            border: 1px solid #cbd5e1;
        }

        .mock-meta-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            background: #f8fafc;
            border-radius: 16px;
            padding: 12px;
            margin-bottom: 18px;
            border: 1px solid #e2e8f0;
        }
        .meta-stat {
            text-align: center;
        }
        .meta-stat small {
            display: block;
            font-size: 10.5px;
            font-weight: 800;
            color: #64748b;
            margin-bottom: 2px;
            text-transform: uppercase;
        }
        .meta-stat b {
            font-size: 15px;
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
            padding: 10px 14px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 850;
            text-decoration: none;
            text-align: center;
            border: 1.5px solid transparent;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .btn-play {
            background: linear-gradient(135deg, #10b981, #059669);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
        }
        .btn-play:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 14px rgba(16, 185, 129, 0.35);
        }
        .btn-edit {
            background: #e0e7ff;
            color: #4338ca;
            border-color: #c7d2fe;
        }
        .btn-edit:hover {
            background: #c7d2fe;
            color: #312e81;
        }
        .btn-del {
            padding: 10px 14px;
            background: #fef2f2;
            color: #dc2626;
            border: 1.5px solid #fecaca;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 850;
            font-size: 13px;
            transition: all 0.15s;
        }
        .btn-del:hover {
            background: #fee2e2;
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
                    <span class="hero-badge">🏆 CHUYÊN MÔN ĐỀ THI IC3 GS6</span>
                    <h1>Quản Trị Bộ Đề Thi Thử Tổng Hợp</h1>
                    <p>Mỗi bộ đề thi thử tổng hợp câu hỏi chọn lọc từ 7 chủ đề trong khối lớp. Học sinh sẽ làm bài thi với áp lực thời gian thực 40 - 50 phút, đạt chuẩn 700/1000 điểm để nhận huy hiệu và Sao Vàng.</p>
                </div>
                <div class="hero-action">
                    <a class="btn-create-3d" href="{{ route('admin.mock-tests.create', ['grade' => $selectedGrade]) }}">
                        <span>＋</span> Soạn Bộ Đề Thi Thử Mới
                    </a>
                </div>
            </section>

            <!-- 3-Pod Stats Overview -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #eef2ff; color: #4f46e5;">🏆</div>
                    <div>
                        <div class="stat-lbl">Tổng Số Bộ Đề Toàn Trường</div>
                        <div class="stat-val">{{ number_format($totalMockTestsAll ?? 0) }} <small style="font-size: 13px; font-weight: 700; color: #64748b;">Bộ Đề</small></div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: #f0fdf4; color: #16a34a;">⚡</div>
                    <div>
                        <div class="stat-lbl">Khối Lớp Đang Chọn</div>
                        <div class="stat-val">Khối {{ $selectedGrade }} <small style="font-size: 13px; font-weight: 700; color: #16a34a;">(Spark {{ $selectedGrade - 2 }})</small></div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: #f0f9ff; color: #0284c7;">📚</div>
                    <div>
                        <div class="stat-lbl">Ngân Hàng Câu Hỏi Khối {{ $selectedGrade }}</div>
                        <div class="stat-val" style="color: #0284c7;">{{ number_format($totalQuestionsInGrade ?? 0) }} <small style="font-size: 13px; font-weight: 700; color: #64748b;">Câu Sẵn Sàng</small></div>
                    </div>
                </div>
            </div>

            <!-- Grade Tabs Switcher -->
            <div class="grade-tabs-bar">
                <div class="grade-tabs">
                    @foreach($levels as $lvl)
                        <a class="grade-tab {{ $selectedGrade === $lvl->grade ? 'active' : '' }}" href="{{ route('admin.mock-tests.index', ['grade' => $lvl->grade]) }}">
                            <span>⚡</span> Khối {{ $lvl->grade }} (Spark Level {{ $lvl->grade - 2 }})
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
                                    ▶ Làm Thử
                                </a>
                                <a class="btn-action btn-edit" href="{{ route('admin.mock-tests.edit', $test) }}" title="Chỉnh sửa câu hỏi hoặc thời lượng đề thi">
                                    ✏️ Sửa Đề
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

</body>
</html>