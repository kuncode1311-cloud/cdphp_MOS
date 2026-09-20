<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cài đặt Khu Trò Chơi & Đổi Giờ Chơi — IC3 Quest</title>
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
            --radius-md: 14px;
            --radius-lg: 18px;
            --shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 14px -2px rgba(15, 23, 42, 0.08);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background-color: var(--bg-body);
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
            background-color: var(--bg-body);
        }
        .main-content {
            padding: 24px 32px 48px;
            flex: 1;
            min-width: 0;
        }

        /* 3D Tactile Stat Cards */
        .stats-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px; margin-bottom: 28px;
        }
        .stat-card {
            background: #ffffff;
            border: 2.5px solid #ffffff;
            border-radius: var(--radius-md);
            padding: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.06), inset 0 -4px 0 rgba(0, 0, 0, 0.04);
            display: flex;
            align-items: center;
            gap: 16px;
            transition: all 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .stat-card:hover {
            transform: translateY(-3px) scale(1.01);
            box-shadow: 0 16px 32px rgba(0, 0, 0, 0.1), inset 0 -4px 0 rgba(0, 0, 0, 0.04);
        }
        .stat-icon {
            width: 54px; height: 54px; border-radius: 16px;
            display: grid; place-items: center; font-size: 26px; flex-shrink: 0;
            border: 2px solid #ffffff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        .stat-val { font-size: 26px; font-weight: 900; line-height: 1.1; margin: 3px 0; }
        .stat-lbl { font-size: 12px; font-weight: 750; color: var(--text-muted); text-transform: uppercase; }

        /* Cards Layout */
        .grid-layout {
            display: grid; grid-template-columns: 1.3fr 1fr;
            gap: 24px; align-items: start;
        }
        .card {
            background: var(--surface);
            border: 2.5px solid #ffffff;
            border-radius: var(--radius-lg);
            padding: 26px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.06), inset 0 -4px 0 rgba(0, 0, 0, 0.03);
            margin-bottom: 24px;
        }
        .card-head {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 18px; padding-bottom: 14px; border-bottom: 1px solid var(--border);
        }
        .card-title { font-size: 17px; font-weight: 850; display: flex; align-items: center; gap: 8px; }

        /* Form elements */
        .form-unit { margin-bottom: 16px; }
        .form-label { display: block; font-size: 13px; font-weight: 750; margin-bottom: 6px; color: #1e293b; }
        .form-input, .form-select {
            width: 100%; padding: 11px 14px; border: 1.5px solid var(--border);
            border-radius: 10px; font-size: 14px; font-family: inherit; font-weight: 600;
            background: #fff; transition: border-color 0.15s;
        }
        .form-input:focus, .form-select:focus {
            outline: none; border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
        }

        .package-box {
            padding: 16px; border-radius: var(--radius-md); border: 2px solid #e0e7ff;
            background: #f8faff; margin-bottom: 16px;
        }
        .package-box.pkg2 { border-color: #fef08a; background: #fffdf2; }

        .btn-save {
            background: linear-gradient(135deg, #4f46e5, #4338ca);
            color: #fff; border: 2px solid #ffffff; padding: 13px 26px; border-radius: 12px;
            font-size: 14.5px; font-weight: 850; cursor: pointer; display: inline-flex;
            align-items: center; gap: 8px; box-shadow: 0 5px 0 #312e81, 0 10px 20px rgba(79, 70, 229, 0.3);
            transition: all 0.15s ease;
        }
        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 0 #312e81, 0 14px 25px rgba(79, 70, 229, 0.4);
        }
        .btn-save:active {
            transform: translateY(3px);
            box-shadow: 0 2px 0 #312e81, 0 4px 8px rgba(79, 70, 229, 0.2);
        }

        .btn-action {
            background: linear-gradient(135deg, #10b981, #059669);
            color: #fff; border: 2px solid #ffffff; padding: 11px 22px; border-radius: 12px;
            font-size: 13.5px; font-weight: 850; cursor: pointer;
            box-shadow: 0 4px 0 #047857, 0 8px 16px rgba(16, 185, 129, 0.25);
            transition: all 0.15s ease;
        }
        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 0 #047857, 0 12px 20px rgba(16, 185, 129, 0.35);
        }
        .btn-action:active {
            transform: translateY(2px);
            box-shadow: 0 2px 0 #047857, 0 4px 8px rgba(16, 185, 129, 0.15);
        }

        /* Toggle switch */
        .switch-label {
            display: flex; align-items: center; justify-content: space-between;
            padding: 14px 18px; border-radius: 12px; background: #f1f5f9;
            cursor: pointer; margin-bottom: 20px;
        }

        /* Table */
        .table-wrap { overflow-x: auto; max-height: 480px; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; text-align: left; }
        th {
            background: #f8fafc; padding: 11px 12px; font-weight: 800;
            color: var(--text-muted); border-bottom: 1.5px solid var(--border);
            position: sticky; top: 0; z-index: 2;
        }
        td { padding: 11px 12px; border-bottom: 1px solid var(--border); }
        tr:hover td { background: #f8fafc; }

        .badge-earn { color: #047857; background: #d1fae5; padding: 4px 8px; border-radius: 6px; font-weight: 800; font-size: 11px; }
        .badge-exchange { color: #1d4ed8; background: #dbeafe; padding: 4px 8px; border-radius: 6px; font-weight: 800; font-size: 11px; }
        .badge-play { color: #b45309; background: #fef3c7; padding: 4px 8px; border-radius: 6px; font-weight: 800; font-size: 11px; }

        .alert-box {
            padding: 14px 18px; border-radius: 12px; font-size: 14px; font-weight: 700;
            margin-bottom: 20px; display: flex; align-items: center; gap: 10px;
        }
        .alert-ok { background: #ecfdf5; color: #065f46; border: 1.5px solid #a7f3d0; }
        .alert-err { background: #fef2f2; color: #991b1b; border: 1.5px solid #fecaca; }

        @media (max-width: 1100px) {
            .grid-layout { grid-template-columns: 1fr; }
            .shell { grid-template-columns: 1fr; }
            .side { display: none; }
        }
    </style>
</head>
<body>

<div class="shell">
    <!-- Sidebar Đồng Bộ 100% Hệ Thống -->
    <x-admin-sidebar active-route="admin.games.settings" active-group="games" />

    <!-- Main Workspace -->
    <div class="main-workspace">
        <!-- Topbar Trực Quan Thông Minh -->
        <x-admin-topbar title="Cài Đặt Khu Trò Chơi & Tỷ Lệ Đổi Giờ" breadcrumb="🎮 Cài Đặt Khu Trò Chơi & Đổi Giờ" />

        <main class="main-content">

        @if(session('ok'))
            <div class="alert-box alert-ok">
                <span>✓</span> {{ session('ok') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert-box alert-err">
                <div>
                    @foreach($errors->all() as $err)
                        <div>• {{ $err }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Stats Overview -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fef3c7; color:#d97706;">⭐</div>
                <div>
                    <div class="stat-lbl">Sao Thưởng Toàn Trường</div>
                    <div class="stat-val" style="color:#b45309;">{{ number_format($totalStarsBalance) }}</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background:#e0e7ff; color:#4f46e5;">⏱️</div>
                <div>
                    <div class="stat-lbl">Tổng Giờ Đã Chơi</div>
                    <div class="stat-val" style="color:#4338ca;">{{ number_format($totalMinutesPlayed) }} <small style="font-size:14px;">Phút</small></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background:#dbeafe; color:#2563eb;">🎟️</div>
                <div>
                    <div class="stat-lbl">Số Lượt Đổi Gói</div>
                    <div class="stat-val" style="color:#1d4ed8;">{{ number_format($totalExchanges) }}</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background:#dcfce7; color:#16a34a;">👨‍🎓</div>
                <div>
                    <div class="stat-lbl">Học Sinh Đang Có Giờ Chơi</div>
                    <div class="stat-val" style="color:#15803d;">{{ number_format($totalStudentsWithTime) }}</div>
                </div>
            </div>
        </div>

        <!-- 2 Column Layout -->
        <div class="grid-layout">
            
            <!-- Cột Trái: Cấu hình quy đổi -->
            <div>
                <div class="card">
                    <div class="card-head">
                        <h2 class="card-title"><span>⚙️</span> Cấu hình Quy đổi Sao ➔ Giờ chơi</h2>
                        <span style="font-size:12px; color:var(--text-muted); font-weight:700;">Áp dụng toàn trường</span>
                    </div>

                    <form method="post" action="{{ route('admin.games.settings.update') }}">
                        @csrf
                        @method('put')

                        <!-- Bật/Tắt mini-game -->
                        <label class="switch-label">
                            <div>
                                <b style="font-size:14.5px; display:block;">Mở Cổng Khu Trò Chơi Mini-Game</b>
                                <small style="color:var(--text-muted);">Tắt tính năng này nếu đang trong giờ kiểm tra tập trung</small>
                            </div>
                            <input type="checkbox" name="game_enabled" value="1" {{ ($settings['game_enabled'] ?? '1') === '1' ? 'checked' : '' }} style="width:22px; height:22px; cursor:pointer;">
                        </label>

                        <!-- Gói 1 -->
                        <div class="package-box">
                            <div style="font-size:13px; font-weight:800; color:#4338ca; margin-bottom:12px; display:flex; align-items:center; gap:6px;">
                                <span>🥉</span> CẤU HÌNH GÓI 1 (GÓI CƠ BẢN)
                            </div>
                            <div class="form-unit">
                                <label class="form-label">Tên hiển thị gói 1</label>
                                <input type="text" name="pkg1_title" class="form-input" value="{{ $settings['pkg1_title'] ?? 'Gói Khởi Động' }}" required>
                            </div>
                            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
                                <div class="form-unit">
                                    <label class="form-label">⭐ Số Sao cần đổi</label>
                                    <input type="number" name="pkg1_stars" class="form-input" value="{{ $settings['pkg1_stars'] ?? 500 }}" min="10" required>
                                </div>
                                <div class="form-unit">
                                    <label class="form-label">⏱️ Phút chơi nhận được</label>
                                    <input type="number" name="pkg1_minutes" class="form-input" value="{{ $settings['pkg1_minutes'] ?? 3 }}" min="1" max="60" required>
                                </div>
                            </div>
                        </div>

                        <!-- Gói 2 -->
                        <div class="package-box pkg2">
                            <div style="font-size:13px; font-weight:800; color:#854d0e; margin-bottom:12px; display:flex; align-items:center; gap:6px;">
                                <span>🥈</span> CẤU HÌNH GÓI 2 (GÓI SIÊU CẤP - KHUYẾN KHÍCH)
                            </div>
                            <div class="form-unit">
                                <label class="form-label">Tên hiển thị gói 2</label>
                                <input type="text" name="pkg2_title" class="form-input" value="{{ $settings['pkg2_title'] ?? 'Gói Siêu Hiệp Sĩ' }}" required>
                            </div>
                            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
                                <div class="form-unit">
                                    <label class="form-label">⭐ Số Sao cần đổi</label>
                                    <input type="number" name="pkg2_stars" class="form-input" value="{{ $settings['pkg2_stars'] ?? 1000 }}" min="10" required>
                                </div>
                                <div class="form-unit">
                                    <label class="form-label">⏱️ Phút chơi nhận được</label>
                                    <input type="number" name="pkg2_minutes" class="form-input" value="{{ $settings['pkg2_minutes'] ?? 7 }}" min="1" max="120" required>
                                </div>
                            </div>
                        </div>

                        <!-- Giới hạn tối đa mỗi ngày -->
                        <div class="form-unit">
                            <label class="form-label">🛡️ Giới hạn tối đa thời gian chơi/ngày (Bảo vệ mắt học sinh)</label>
                            <input type="number" name="max_daily_minutes" class="form-input" value="{{ $settings['max_daily_minutes'] ?? 20 }}" min="5" max="180" required>
                            <small style="color:var(--text-muted); font-size:11.5px; display:block; margin-top:4px;">
                                Mỗi học sinh không được chơi quá số phút này trong cùng một ngày.
                            </small>
                        </div>

                        <div style="text-align:right; margin-top:20px;">
                            <button type="submit" class="btn-save">
                                <span>✓</span> Lưu Cài Đặt Game
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Cấu hình Bảng Xếp Hạng & Chu Kỳ Reset -->
                <div class="card" style="margin-top: 24px; border-top: 4px solid #3b82f6;">
                    <div class="card-head">
                        <h2 class="card-title"><span>👑</span> Quản Trị Bảng Xếp Hạng & Vòng Đua</h2>
                        <span style="font-size:12px; color:var(--text-muted); font-weight:700;">Thi đua toàn trường</span>
                    </div>

                    <!-- 1. Cấu hình Chu kỳ & Chế độ tính điểm Bảng Xếp Hạng -->
                    <form method="post" action="{{ route('admin.games.leaderboard.settings') }}" style="margin-bottom: 18px;">
                        @csrf
                        <div class="form-unit">
                            <label class="form-label">⏱️ Chu kỳ Reset Bảng Xếp Hạng</label>
                            <select name="leaderboard_reset_period" class="form-select">
                                <option value="weekly" {{ ($leaderboardPeriod ?? 'weekly') === 'weekly' ? 'selected' : '' }}>
                                    📅 Hàng tuần (Tự động reset vào 00:00 Thứ Hai)
                                </option>
                                <option value="monthly" {{ ($leaderboardPeriod ?? '') === 'monthly' ? 'selected' : '' }}>
                                    🗓️ Hàng tháng (Tự động reset vào 00:00 Ngày 01 hàng tháng)
                                </option>
                                <option value="manual" {{ ($leaderboardPeriod ?? '') === 'manual' ? 'selected' : '' }}>
                                    ✋ Thủ công (Chỉ reset khi Quản trị viên bấm nút)
                                </option>
                            </select>
                        </div>

                        <!-- Chế độ tính điểm xếp hạng -->
                        <div class="form-unit" style="margin-top: 14px;">
                            <label class="form-label">🎯 Quy Tắc Tính Điểm Đua Top Bảng Vàng</label>
                            <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 6px;">
                                <label style="display: flex; align-items: flex-start; gap: 10px; background: #eff6ff; border: 1.5px solid #bfdbfe; border-radius: 10px; padding: 10px 12px; cursor: pointer;">
                                    <input type="radio" name="leaderboard_score_mode" value="passed_only" {{ ($leaderboardScoreMode ?? 'passed_only') === 'passed_only' ? 'checked' : '' }} style="margin-top: 3px;">
                                    <div>
                                        <b style="color: #1e40af; font-size: 13.5px; display: block;">🌟 Chỉ tính bài thi ĐẠT CHUẨN IC3 (≥ {{ $leaderboardMinPassScore ?? 700 }}đ) — Khuyên dùng</b>
                                        <span style="color: #475569; font-size: 12px; line-height: 1.35; display: block;">Học sinh phải đạt từ điểm chuẩn trở lên mới được tích lũy điểm vào Bảng Vàng thi đua. Khích lệ tinh thần ôn luyện chất lượng cao.</span>
                                    </div>
                                </label>
                                <label style="display: flex; align-items: flex-start; gap: 10px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 10px 12px; cursor: pointer;">
                                    <input type="radio" name="leaderboard_score_mode" value="all_attempts" {{ ($leaderboardScoreMode ?? 'passed_only') === 'all_attempts' ? 'checked' : '' }} style="margin-top: 3px;">
                                    <div>
                                        <b style="color: #334155; font-size: 13.5px; display: block;">📊 Tính TẤT CẢ các bài thi (Kể cả dưới điểm chuẩn)</b>
                                        <span style="color: #64748b; font-size: 12px; line-height: 1.35; display: block;">Cộng dồn toàn bộ điểm của mọi lượt làm bài, bất kể điểm số đạt hay không đạt.</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Điểm chuẩn tối thiểu -->
                        <div class="form-unit" style="margin-top: 14px;">
                            <label class="form-label">🏅 Điểm Chuẩn Tối Thiểu Đạt Chuẩn IC3</label>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <input type="number" name="leaderboard_min_pass_score" class="form-input" value="{{ $leaderboardMinPassScore ?? 700 }}" min="100" max="1000" step="50" style="max-width: 140px;" required>
                                <span style="font-size: 13px; color: var(--text-muted); font-weight: 600;">điểm (Thang điểm 1000 IC3 Spark GS6)</span>
                            </div>
                        </div>

                        <div style="text-align: right; margin-top: 14px;">
                            <button type="submit" class="btn-save" style="padding: 9px 18px; font-size: 13px;">
                                <span>✓</span> Lưu Cài Đặt Bảng Xếp Hạng
                            </button>
                        </div>
                    </form>

                    <!-- Thông số vòng đua hiện tại -->
                    <div style="background:#f8fafc; border:1.5px solid #e2e8f0; border-radius:12px; padding:14px 16px; margin-bottom:18px; display:flex; flex-direction:column; gap:8px; font-size:13px;">
                        <div style="display:flex; justify-content:space-between;">
                            <span style="color:var(--text-muted);">Mốc bắt đầu tính điểm:</span>
                            <b style="color:#0f172a;">{{ $leaderboardStart ? $leaderboardStart->setTimezone(config('learning.display_timezone', 'Asia/Ho_Chi_Minh'))->format('H:i d/m/Y') : 'Chưa thiết lập' }}</b>
                        </div>
                        <div style="display:flex; justify-content:space-between;">
                            <span style="color:var(--text-muted);">Quy tắc tính điểm:</span>
                            @if(($leaderboardScoreMode ?? 'passed_only') === 'passed_only')
                                <b style="color:#059669;">🌟 Chỉ tính bài Đạt chuẩn (≥ {{ $leaderboardMinPassScore ?? 700 }}đ)</b>
                            @else
                                <b style="color:#2563eb;">📊 Tính tất cả bài thi</b>
                            @endif
                        </div>
                        <div style="display:flex; justify-content:space-between;">
                            <span style="color:var(--text-muted);">Lần reset gần nhất:</span>
                            <span style="color:#2563eb; font-weight:700;">{{ $leaderboardLastReset ? \Carbon\Carbon::parse($leaderboardLastReset)->format('H:i d/m/Y') : 'Theo chu kỳ mặc định' }}</span>
                        </div>
                        @if($leaderboardNext)
                            <div style="display:flex; justify-content:space-between;">
                                <span style="color:var(--text-muted);">Đợt reset tự động kế tiếp:</span>
                                <b style="color:#059669;">{{ $leaderboardNext->format('H:i d/m/Y') }}</b>
                            </div>
                        @endif
                    </div>

                    <!-- 2. Nút bấm Reset Ngay -->
                    <form method="post" action="{{ route('admin.games.leaderboard.reset') }}" onsubmit="return confirm('⚡ CẢNH BÁO QUẢN TRỊ:\n\nBạn có chắc chắn muốn BẮT ĐẦU VÒNG THI ĐUA MỚI ngay bây giờ?\n\nMốc tính điểm bài thi của học sinh trên Bảng Vàng sẽ được tính lại từ thời điểm này. Điểm tích lũy và Sao thưởng của học sinh vẫn được bảo toàn an toàn.')">
                        @csrf
                        <button type="submit" class="btn-action" style="width:100%; padding:13px; background:linear-gradient(135deg, #f59e0b, #d97706); border:2px solid #fbbf24; border-radius:12px; color:#fff; font-weight:800; font-size:13.5px; cursor:pointer; box-shadow:0 4px 12px rgba(245, 158, 11, 0.25); display:flex; align-items:center; justify-content:center; gap:8px;">
                            <span>⚡</span> Bắt Đầu Vòng Đua Mới (Reset Bảng Xếp Hạng Ngay)
                        </button>
                    </form>
                </div>
            </div>

            <!-- Cột Phải: Thưởng nóng & Tra cứu lịch sử -->
            <div>
                <!-- Thưởng nóng cho học sinh -->
                <div class="card">
                    <div class="card-head">
                        <h2 class="card-title"><span>🎁</span> Thưởng nóng / Điều chỉnh Sao</h2>
                    </div>
                    <form method="post" action="{{ route('admin.games.adjust-stars') }}">
                        @csrf
                        <div class="form-unit">
                            <label class="form-label">Chọn học sinh</label>
                            <select name="user_id" class="form-select" required>
                                <option value="">-- Chọn học sinh nhận thưởng --</option>
                                @foreach($students as $st)
                                    <option value="{{ $st->id }}">
                                        {{ $st->name }} ({{ $st->classroom?->name ?? 'Chưa xếp lớp' }}) · Hiện có: {{ $st->reward_stars }} ⭐
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div style="display:grid; grid-template-columns: 1fr 1.5fr; gap:12px;">
                            <div class="form-unit">
                                <label class="form-label">Số Sao (+ / -)</label>
                                <input type="number" name="amount" class="form-input" placeholder="+500" required>
                            </div>
                            <div class="form-unit">
                                <label class="form-label">Lý do thưởng</label>
                                <input type="text" name="reason" class="form-input" placeholder="Tiến bộ vượt bậc..." required>
                            </div>
                        </div>

                        <button type="submit" class="btn-action" style="width:100%; padding:12px;">
                            <span>✨</span> Thực Hiện Thưởng Sao
                        </button>
                    </form>
                </div>

                <!-- Lịch sử đổi & chơi gần nhất -->
                <div class="card">
                    <div class="card-head">
                        <h2 class="card-title"><span>📜</span> Nhật ký Giao dịch Gần Nhất</h2>
                        <span style="font-size:12px; color:var(--text-muted);">{{ $recentTransactions->count() }} lượt gần đây</span>
                    </div>

                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Học sinh</th>
                                    <th>Giao dịch</th>
                                    <th>Sao</th>
                                    <th>Thời gian</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTransactions as $tx)
                                    <tr>
                                        <td>
                                            <b>{{ $tx->user?->name ?? 'Học sinh' }}</b>
                                            <small style="display:block; color:var(--text-muted); font-size:11px;" title="{{ $tx->created_at_full_vn }}">
                                                {{ $tx->created_at_vn }}
                                            </small>
                                        </td>
                                        <td>
                                            @if($tx->type === 'earn')
                                                <span class="badge-earn">Làm bài</span>
                                            @elseif($tx->type === 'exchange')
                                                <span class="badge-exchange">Đổi gói</span>
                                            @elseif($tx->type === 'play')
                                                <span class="badge-play">Chơi game</span>
                                            @else
                                                <span class="badge-earn">Admin</span>
                                            @endif
                                            <small style="display:block; color:#475569; font-size:11px; margin-top:2px;">
                                                {{ $tx->description }}
                                            </small>
                                        </td>
                                        <td>
                                            @if($tx->stars_change > 0)
                                                <b style="color:#059669;">+{{ $tx->stars_change }}</b>
                                            @elseif($tx->stars_change < 0)
                                                <b style="color:#dc2626;">{{ $tx->stars_change }}</b>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($tx->time_seconds_change > 0)
                                                <b style="color:#2563eb;">+{{ round($tx->time_seconds_change / 60) }}p</b>
                                            @elseif($tx->time_seconds_change < 0)
                                                <b style="color:#d97706;">{{ $tx->time_seconds_change }}s</b>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" style="text-align:center; padding:24px; color:var(--text-muted);">
                                            Chưa có giao dịch đổi điểm nào.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>
    </main>
    </div>
</div>

</body>
</html>
