{{-- Trang chủ học sinh, nhận chương trình học từ LearningController::home(). --}}
@extends('layouts.app')
@section('title', 'IC3 Adventure — Khám phá kỹ năng số')

@section('content')
<div class="adventure-world-wrapper">
    <div class="page-wrap" style="width: min(1200px, 100%);">
        <!-- Hero Game Banner với Background ảnh 2 học sinh sắc nét 100% -->
        <section class="hero game-hero" style="margin-bottom: 25px;">
            <!-- Lớp background ảnh học sinh sắc nét -->
            <div class="hero-bg-art" aria-hidden="true"></div>

            <div class="hero-copy">
                <span class="eyebrow"><i>⚡</i> NHIỆM VỤ TIẾP THEO · +100 SAO VÀNG</span>
                <h1>Học một bài mới, <span>mở khóa siêu năng lực!</span></h1>
                <p>Chọn đúng khối lớp, hoàn thành bài luyện IC3 1000 điểm rồi nhận vé vào Đấu trường Game Hiệp sĩ!</p>
                <div class="hero-actions">
                    <a class="button primary hero-btn-main" href="#chuong-trinh"><span>🚀</span> Bắt đầu học ngay <b>→</b></a>
                    <a class="button game-button hero-btn-game" href="{{ asset('games/bao-ve-em-be.html') }}"><span>🎮</span> Chơi Hiệp sĩ Song Kiếm</a>
                </div>
                <div class="hero-benefits">
                    <span>✓ Tự động lưu tiến độ</span>
                    <span>✓ Chuẩn 1000 điểm</span>
                    <span>✓ Vé chơi game</span>
                </div>
                @php
                    $tz = config('learning.display_timezone', 'Asia/Ho_Chi_Minh');
                    $todayStartUtc = now($tz)->startOfDay()->utc();
                    $todayEndUtc = now($tz)->endOfDay()->utc();
                    $todayCompleted = auth()->user()->attempts()->whereBetween('created_at', [$todayStartUtc, $todayEndUtc])->where('score', '>=', 1000)->distinct('practice_test_id')->count('practice_test_id');
                    $dailyPercent = min(100, round(($todayCompleted / 3) * 100));
                    $todayScore = auth()->user()->attempts()->whereBetween('created_at', [$todayStartUtc, $todayEndUtc])->sum('score') ?: 0;
                    $totalAllTests = $program->levels->flatMap->topics->flatMap->tests->count();
                @endphp
                <div class="daily-progress">
                    <div><b>Tiến độ hôm nay</b><small>{{ $todayCompleted }}/3 nhiệm vụ</small></div>
                    <div class="progress"><i style="width: {{ $dailyPercent }}%;"></i></div>
                    <span style="font-weight: 1000; color: #2563eb;">{{ $dailyPercent }}%</span>
                </div>
            </div>

            <!-- Thẻ bay thành tích nhỏ gọn tinh tế -->
            <div class="float-card card-score">
                <i>⭐</i>
                <span><b>+{{ $todayScore }}</b> <small>điểm hôm nay</small></span>
            </div>
            <div class="float-card card-streak">
                <span>🔥</span>
                <b>{{ max(1, auth()->user()->attempts()->selectRaw('DATE(created_at)')->distinct()->count()) }} ngày <small>luyện tập</small></b>
            </div>
        </section>

        <!-- 4 Action Hub Cards lối tắt chức năng VIP -->
        <section class="action-hub" aria-label="Lối tắt chức năng" style="margin-bottom: 35px;">
            <a class="action-card action-learn" href="#chuong-trinh">
                <i>📚</i>
                <div>
                    <small>HỌC NGAY</small>
                    <b>Chọn chương trình</b>
                    <span>Xem {{ $program->levels->count() }} khối lớp và {{ $totalAllTests }} bài luyện →</span>
                </div>
            </a>
            <a class="action-card action-continue" href="{{ $program->levels->first() ? route('levels.show', $program->levels->first()) : '#chuong-trinh' }}">
                <i>⚡</i>
                <div>
                    <small>TIẾP TỤC</small>
                    <b>Bài đang học</b>
                    <span>Quay lại hành trình gần nhất →</span>
                </div>
            </a>
            <a class="action-card action-game" href="{{ asset('games/bao-ve-em-be.html') }}">
                <i>🎮</i>
                <div>
                    <small>PHẦN THƯỞNG</small>
                    <b>Khu trò chơi</b>
                    <span>Dùng camera điều khiển song kiếm →</span>
                </div>
            </a>
            <a class="action-card action-award" href="{{ route('achievements') }}">
                <i>🏆</i>
                <div>
                    <small>THÀNH TÍCH</small>
                    <b>{{ auth()->user()->attempts()->sum('score') ?? 0 }} điểm sao</b>
                    <span>Xem huy hiệu và bảng vàng →</span>
                </div>
            </a>
        </section>

        @if(auth()->user()->isStudent())
        <!-- Parent Portal Quick Banner -->
        <div style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%); border-radius: 24px; padding: 18px 24px; margin-bottom: 30px; display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap; border: 3px solid #818cf8; box-shadow: 0 10px 25px rgba(49, 46, 129, 0.25);">
            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="width: 52px; height: 52px; border-radius: 18px; background: rgba(255,255,255,0.15); display: grid; place-items: center; font-size: 26px; border: 2px solid rgba(255,255,255,0.25); flex-shrink: 0;">
                    👨‍👩‍👧‍👦
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-family: 'Fredoka', cursive, sans-serif; font-size: 19px; color: #fde047; font-weight: 700;">Góc Theo Dõi Dành Cho Phụ Huynh</span>
                        <span style="background: #ec4899; color: #ffffff; font-size: 10px; font-weight: 900; padding: 2px 8px; border-radius: 999px; text-transform: uppercase;">Mới & Trực quan</span>
                    </div>
                    <p style="margin: 3px 0 0; color: #e0e7ff; font-size: 13px; font-weight: 700;">
                        Xem thống kê chi tiết, biểu đồ năng lực 7 chủ đề, thời lượng học và cảnh báo thông minh các bài con làm sai nhiều lần.
                    </p>
                </div>
            </div>
            <a href="{{ route('parent.dashboard') }}" style="display: inline-flex; align-items: center; gap: 8px; background: linear-gradient(180deg, #facc15, #eab308); color: #713f12; font-size: 13.5px; font-weight: 900; padding: 10px 20px; border-radius: 16px; text-decoration: none; border: 2px solid #ffffff; box-shadow: 0 4px 12px rgba(234, 179, 8, 0.35); transition: transform 0.15s; white-space: nowrap;">
                <span>📊 Xem Báo Cáo Của Con</span>
                <b>→</b>
            </a>
        </div>
        @endif

        <!-- Center Capsule Header: Choose a Mission -->
        <div class="mission-chooser-container" id="chuong-trinh" style="text-align: center; margin-bottom: 25px;">
            <div class="choose-mission-capsule">
                <span>🎯</span> Choose a Mission · Chọn Nhiệm Vụ Khám Phá
            </div>
        </div>

        <!-- 3 Grand Colorful Adventure Cards per Row -->
        <div class="adventure-cards-grid">
            @php
                $gradeThemes = [
                    1 => ['gradient' => 'linear-gradient(180deg, #f59e0b 0%, #d97706 100%)', 'text_color' => '#b45309', 'icon' => '🌟', 'label' => 'Khởi đầu số', 'desc' => 'Làm quen máy tính, phần mềm & công dân số cơ bản.'],
                    2 => ['gradient' => 'linear-gradient(180deg, #14b8a6 0%, #0f766e 100%)', 'text_color' => '#0f766e', 'icon' => '🌱', 'label' => 'Khám phá số', 'desc' => 'Khám phá thông tin, kỹ năng tìm kiếm & ứng dụng số.'],
                    3 => ['gradient' => 'linear-gradient(180deg, #78da78 0%, #48be48 100%)', 'text_color' => '#1e701e', 'icon' => '📖', 'label' => 'Spark Level 1', 'desc' => 'Làm quen máy tính, phần mềm & công dân số cơ bản.'],
                    4 => ['gradient' => 'linear-gradient(180deg, #6ec6ff 0%, #309df5 100%)', 'text_color' => '#12579b', 'icon' => '🎧', 'label' => 'Spark Level 2', 'desc' => 'Tìm kiếm thông tin, bảng tính & sáng tạo nội dung.'],
                    5 => ['gradient' => 'linear-gradient(180deg, #c780fa 0%, #9b4ced 100%)', 'text_color' => '#5a1999', 'icon' => '✏️', 'label' => 'Spark Level 3', 'desc' => 'Truyền thông mạng, an toàn dữ liệu & bảo mật số.'],
                    6 => ['gradient' => 'linear-gradient(180deg, #f43f5e 0%, #e11d48 100%)', 'text_color' => '#be123c', 'icon' => '🚀', 'label' => 'Bứt phá số', 'desc' => 'Bứt phá kỹ năng công nghệ & tư duy logic số.'],
                    7 => ['gradient' => 'linear-gradient(180deg, #6366f1 0%, #4338ca 100%)', 'text_color' => '#3730a3', 'icon' => '⚡', 'label' => 'Chinh phục số', 'desc' => 'Chinh phục kiến thức tin học chuẩn quốc tế.'],
                    8 => ['gradient' => 'linear-gradient(180deg, #d946ef 0%, #c026d3 100%)', 'text_color' => '#a21caf', 'icon' => '💎', 'label' => 'Chuyên gia số', 'desc' => 'Chuyên gia ứng dụng số & kỹ năng nâng cao.'],
                    9 => ['gradient' => 'linear-gradient(180deg, #f97316 0%, #ea580c 100%)', 'text_color' => '#c2410c', 'icon' => '🎯', 'label' => 'Làm chủ số', 'desc' => 'Làm chủ nền tảng số & công nghệ tương lai.'],
                ];
            @endphp

            @foreach($program->levels->sortBy('grade') as $level)
                @php
                    $theme = $gradeThemes[$level->grade] ?? $gradeThemes[(($level->grade - 1) % count($gradeThemes)) + 1] ?? $gradeThemes[3];
                    $hasAccess = auth()->user()->canAccessLevel($level);
                    $topicCount = $level->topics->count();
                    $testCount = $level->topics->sum(fn($t) => $t->tests->count());
                    $desc = $topicCount > 0 ? ($theme['desc'] ?? "{$topicCount} chủ đề · {$testCount} bài luyện") : "Đang cập nhật nội dung bài học";
                    $stars = $testCount > 0 ? "{$testCount} Nhiệm vụ" : "Sắp ra mắt";
                @endphp
                <div class="adventure-card" style="background: {{ $theme['gradient'] }}; color: #fff; {{ $hasAccess ? '' : 'filter: grayscale(0.55); opacity: 0.88; position: relative;' }}">
                    @if(! $hasAccess)
                        <div style="position: absolute; top: 14px; right: 14px; background: rgba(15, 23, 42, 0.82); color: #f8fafc; padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 850; display: flex; align-items: center; gap: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">
                            <span>🔒</span> Chưa mở khóa
                        </div>
                    @endif

                    <h3 class="adv-card-title">Khối {{ $level->grade }} · {{ $theme['label'] }}</h3>

                    <div class="adv-icon-orb">
                        <span class="adv-icon-emoji">{{ $hasAccess ? $theme['icon'] : '🔒' }}</span>
                    </div>

                    <p class="adv-card-desc">{{ $desc }}</p>

                    <div class="adv-card-stars">
                        @if($hasAccess)
                            <span>⭐</span> <b>{{ $stars }}</b>
                        @else
                            <span style="color: #ffffff; font-size: 12px; font-weight: 750; background: rgba(0,0,0,0.2); padding: 3px 10px; border-radius: 999px;">Chưa được cấp quyền học</span>
                        @endif
                    </div>

                    @if($hasAccess)
                        <a href="{{ route('levels.show', $level) }}" class="adv-card-btn" style="color: {{ $theme['text_color'] }}; background: #ffffff;">
                            Bắt đầu (Start) <b>→</b>
                        </a>
                    @else
                        <button type="button" class="adv-card-btn" style="background: rgba(15,23,42,0.7); color: #ffffff; border-color: rgba(255,255,255,0.3); cursor: not-allowed; opacity: 0.9;" onclick="alert('Khối học này đang bị khóa. Hãy liên hệ Giáo viên phụ trách để được cấp quyền mở khóa nhé!')">
                            🔒 Đang khóa (Liên hệ GV)
                        </button>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- Bottom Hub: Daily Challenge & Shop -->
        <div class="adventure-bottom-hub" style="margin-bottom: 35px;">
            <div class="hub-card daily-challenge-card">
                <div class="hub-icon gift-icon">🎁</div>
                <div class="hub-content">
                    <b>Thử thách hàng ngày (Daily Challenge)</b>
                    <span>Hoàn thành bất kỳ 1 bài luyện 1000 điểm để nhận 20 Sao Vàng thưởng!</span>
                    <div class="hub-progress-bar">
                        <div class="hub-progress-fill" style="width: 33%;"></div>
                    </div>
                </div>
                <div class="hub-counter">1 / 3</div>
            </div>

            <a href="{{ asset('games/bao-ve-em-be.html') }}" class="hub-card shop-card">
                <div class="hub-icon shop-icon">🏪</div>
                <div class="hub-content">
                    <b>Khu Trò Chơi (Shop & Rewards)</b>
                    <span>Đổi sao nhận quà & mở khóa game Song Kiếm!</span>
                </div>
                <div class="shop-badge">VÀO CHƠI →</div>
            </a>
        </div>

        <!-- Gamified Journey 3-Step Quest Section -->
        <section class="game-journey-container" id="hanh-trinh">
            <div class="journey-header">
                <span class="journey-kicker">✨ HÀNH TRÌNH 3 BƯỚC THÀNH CÔNG</span>
                <h2>Mỗi ngày một bước tiến lớn 🚀</h2>
                <p>Ba bước đơn giản để bé biến kỹ năng công nghệ thành siêu năng lực số!</p>
            </div>

            <div class="journey-steps-grid">
                <!-- Step 1 -->
                <div class="journey-step-card step-cyan">
                    <div class="step-badge">BƯỚC 01</div>
                    <div class="step-icon-orb">
                        <span>🧭</span>
                    </div>
                    <h3>1. Chọn Chủ Đề</h3>
                    <p>Khám phá các khối lớp & chủ đề IC3 GS6 phù hợp với em.</p>
                    <div class="step-feature-tag">✓ Chuẩn kiến thức IIG</div>
                </div>

                <div class="step-connector-arrow">➔</div>

                <!-- Step 2 -->
                <div class="journey-step-card step-gold">
                    <div class="step-badge">BƯỚC 02</div>
                    <div class="step-icon-orb">
                        <span>🎯</span>
                    </div>
                    <h3>2. Đạt 1000 Điểm</h3>
                    <p>Luyện tập thỏa thích, không giới hạn để đạt điểm tối đa.</p>
                    <div class="step-feature-tag">✓ Tự động lưu tiến độ</div>
                </div>

                <div class="step-connector-arrow">➔</div>

                <!-- Step 3 -->
                <div class="journey-step-card step-mint">
                    <div class="step-badge">BƯỚC 03</div>
                    <div class="step-icon-orb">
                        <span>🎮</span>
                    </div>
                    <h3>3. Chơi Nhận Thưởng</h3>
                    <p>Mở khóa huy hiệu hiệp sĩ & vé vào game Song Kiếm AI.</p>
                    <div class="step-feature-tag">✓ Vé game & Sao vàng</div>
                </div>
            </div>
        </section>
    </div>
</div>

<style>
    /* Full Page Game Adventure Theme */
    .adventure-world-wrapper {
        min-height: calc(100vh - 86px);
        padding: 24px 20px 60px;
        background: transparent;
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
    }
    /* Ultra-Sleek Hero Banner with 100% Crisp Sharp Background Art */
    .game-hero {
        min-height: 400px;
        margin-top: 5px;
        border-radius: 30px;
        overflow: hidden;
        border: 3.5px solid #ffffff;
        box-shadow: 0 16px 40px rgba(0, 0, 0, 0.12);
        position: relative;
        display: flex;
        align-items: center;
        background: #fdfaf2;
    }
    .hero-bg-art {
        position: absolute;
        inset: 0;
        background: url('{{ asset('images/ic3-quest-hero.png') }}') right center / cover no-repeat;
        z-index: 1;
    }
    .hero-copy {
        max-width: 500px;
        padding: 30px 16px 30px 38px;
        background: transparent !important;
        backdrop-filter: none !important;
        position: relative;
        z-index: 5;
    }
    .eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 14px;
        background: linear-gradient(135deg, #fef08a, #facc15);
        border: 1.5px solid #eab308;
        border-radius: 999px;
        color: #713f12;
        font-size: 11px;
        font-weight: 1000;
        letter-spacing: 0.6px;
        box-shadow: 0 3px 8px rgba(234, 179, 8, 0.3);
        margin-bottom: 6px;
    }
    .hero h1 {
        font-family: 'Fredoka', cursive, 'Nunito', sans-serif;
        font-size: 34px;
        line-height: 1.18;
        letter-spacing: -0.5px;
        color: #0f172a;
        margin: 8px 0 6px;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    .hero h1 span {
        color: #1d4ed8;
        font-weight: 700;
    }
    .hero-copy > p {
        font-size: 13.5px;
        line-height: 1.5;
        color: #334155;
        font-weight: 750;
        margin-bottom: 16px;
    }
    .hero-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 14px;
    }
    .hero-btn-main {
        padding: 11px 20px;
        border-radius: 14px;
        font-size: 13.5px;
        font-weight: 1000;
        background: linear-gradient(180deg, #3b82f6, #1d4ed8) !important;
        border: 2.5px solid #ffffff !important;
        color: #ffffff !important;
        box-shadow: 0 5px 0 #1e3a8a, 0 8px 16px rgba(37, 99, 235, 0.35) !important;
        transition: all 0.2s;
    }
    .hero-btn-main:hover {
        transform: translateY(-2px);
        box-shadow: 0 7px 0 #1e3a8a, 0 10px 20px rgba(37, 99, 235, 0.45) !important;
    }
    .hero-btn-game {
        padding: 11px 20px;
        border-radius: 14px;
        font-size: 13.5px;
        font-weight: 1000;
        background: linear-gradient(180deg, #ffc048, #ff9f1a) !important;
        border: 2.5px solid #ffffff !important;
        color: #4a2700 !important;
        box-shadow: 0 5px 0 #b45309, 0 8px 16px rgba(245, 158, 11, 0.35) !important;
        transition: all 0.2s;
    }
    .hero-btn-game:hover {
        transform: translateY(-2px);
        box-shadow: 0 7px 0 #b45309, 0 10px 20px rgba(245, 158, 11, 0.45) !important;
    }
    .hero-benefits {
        display: flex;
        gap: 14px;
        flex-wrap: wrap;
        color: #047857;
        font-size: 12px;
        font-weight: 900;
        margin-bottom: 14px;
    }
    .daily-progress {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 4px;
        max-width: 290px;
        padding: 8px 14px;
        border-radius: 12px;
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    }
    .daily-progress b { font-size: 12px; font-weight: 900; color: #0f172a; }
    .daily-progress small { font-size: 11px; color: #64748b; font-weight: 700; margin-left: 4px; }
    .daily-progress .progress {
        grid-column: 1 / 3;
        height: 6px;
        background: #e2e8f0;
        border-radius: 999px;
        overflow: hidden;
    }
    .daily-progress .progress i {
        display: block;
        height: 100%;
        width: 33%;
        background: linear-gradient(90deg, #3b82f6, #8b5cf6);
        border-radius: 999px;
    }

    /* Sleek Compact Float Cards */
    .float-card {
        position: absolute;
        z-index: 10;
        backdrop-filter: blur(8px);
        border: 2px solid #ffffff;
        border-radius: 999px;
        padding: 6px 14px;
        display: flex;
        align-items: center;
        gap: 6px;
        animation: floatCard 3.5s ease-in-out infinite alternate;
        box-shadow: 0 8px 18px rgba(0, 0, 0, 0.12);
    }
    .card-score {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.96), rgba(254, 240, 138, 0.94));
        left: 440px;
        top: 20px;
    }
    .card-score > i {
        font-style: normal;
        font-size: 18px;
    }
    .card-score b {
        font-family: 'Fredoka', cursive, 'Nunito', sans-serif;
        font-size: 15px;
        font-weight: 700;
        color: #b45309;
    }
    .card-score small {
        font-size: 11px;
        font-weight: 900;
        color: #78350f;
    }

    .card-streak {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.96), rgba(254, 215, 170, 0.94));
        right: 25px;
        bottom: 22px;
        animation-delay: 1.5s;
    }
    .card-streak > span {
        font-size: 18px;
    }
    .card-streak > b {
        font-family: 'Fredoka', cursive, 'Nunito', sans-serif;
        font-size: 14px;
        font-weight: 700;
        color: #c2410c;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .card-streak > b small {
        font-size: 11px;
        font-weight: 900;
        color: #9a3412;
    }

    @keyframes floatCard {
        from { transform: translateY(0); }
        to { transform: translateY(-8px); }
    }

    /* Ultra-Crisp Action Hub Cards - Zero-Flicker Hardware Accelerated */
    .action-hub {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 18px;
    }
    .action-card {
        padding: 20px 18px;
        border-radius: 24px;
        border: 3.5px solid #ffffff;
        display: flex;
        align-items: center;
        gap: 14px;
        text-decoration: none;
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.16), inset 0 -4px 0 rgba(0, 0, 0, 0.15);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        will-change: transform;
        backface-visibility: hidden;
        transform: translateZ(0);
    }
    .action-card:hover {
        transform: translateY(-4px) translateZ(0);
        box-shadow: 0 16px 36px rgba(0, 0, 0, 0.25);
    }
    .action-card > i {
        font-style: normal;
        font-size: 30px;
        width: 56px;
        height: 56px;
        display: grid;
        place-items: center;
        background: rgba(255, 255, 255, 0.3);
        border: 2.5px solid #ffffff;
        border-radius: 18px;
        flex-shrink: 0;
        box-shadow: inset 0 -2px 0 rgba(0, 0, 0, 0.15), 0 6px 12px rgba(0, 0, 0, 0.1);
    }
    .action-card > div {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    .action-card small {
        font-size: 11px;
        font-weight: 1000;
        letter-spacing: 0.8px;
        color: #ffe658;
        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);
    }
    .action-card b {
        font-family: 'Fredoka', cursive, 'Nunito', sans-serif;
        font-size: 20px;
        font-weight: 700;
        color: #ffffff;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.4);
        line-height: 1.2;
    }
    .action-card span {
        font-size: 12px;
        font-weight: 800;
        color: #ffffff;
        opacity: 0.95;
        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.4);
        margin-top: 2px;
    }
    .action-learn { background: linear-gradient(180deg, #4f46e5, #3730a3); }
    .action-continue { background: linear-gradient(180deg, #f97316, #c2410c); }
    .action-game { background: linear-gradient(180deg, #059669, #065f46); }
    .action-award { background: linear-gradient(180deg, #c026d3, #7e22ce); }

    /* Choose a Mission Capsule */
    .choose-mission-capsule {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 14px 38px;
        background: linear-gradient(180deg, #1b4777, #102e52);
        border: 4px solid #6ed7ff;
        border-radius: 999px;
        color: #ffffff;
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 24px;
        font-weight: 700;
        letter-spacing: 0.5px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4), inset 0 -3px 0 #07192e;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
    }
    .choose-mission-capsule span {
        font-size: 24px;
    }

    /* 3 Grand Adventure Cards Grid per Row */
    .adventure-cards-grid {
        width: 100%;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 26px;
        margin-bottom: 32px;
    }

    .adventure-card {
        border-radius: 28px;
        padding: 28px 24px;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        border: 4px solid #ffffff;
        box-shadow: 0 16px 35px rgba(0, 0, 0, 0.25), inset 0 -8px 0 rgba(0, 0, 0, 0.15);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        will-change: transform;
        backface-visibility: hidden;
        transform: translateZ(0);
        position: relative;
    }
    .adventure-card:hover {
        transform: translateY(-6px) translateZ(0);
        box-shadow: 0 24px 45px rgba(0, 0, 0, 0.35), inset 0 -8px 0 rgba(0, 0, 0, 0.15);
    }

    /* Card Themes */
    .card-green {
        background: linear-gradient(180deg, #64cf64 0%, #3cae3c 100%);
        color: #ffffff;
    }
    .card-blue {
        background: linear-gradient(180deg, #57baff 0%, #1f8ee8 100%);
        color: #ffffff;
    }
    .card-purple {
        background: linear-gradient(180deg, #ba6df5 0%, #8934e0 100%);
        color: #ffffff;
    }

    .adv-card-title {
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 14px;
        color: #ffffff;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.4);
    }

    .adv-icon-orb {
        width: 106px;
        height: 106px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.35);
        border: 4px solid #ffffff;
        display: grid;
        place-items: center;
        margin: 6px 0 16px;
        box-shadow: inset 0 -4px 0 rgba(0, 0, 0, 0.15), 0 8px 18px rgba(0, 0, 0, 0.15);
    }
    .adv-icon-emoji {
        font-size: 52px;
        filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.25));
    }

    .adv-card-desc {
        font-size: 14px;
        line-height: 1.5;
        font-weight: 800;
        margin-bottom: 18px;
        min-height: 42px;
        color: #ffffff;
        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.35);
    }

    .adv-card-stars {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 18px;
        background: rgba(0, 0, 0, 0.25);
        border: 2px solid rgba(255, 255, 255, 0.7);
        border-radius: 999px;
        font-size: 14px;
        font-weight: 1000;
        margin-bottom: 20px;
        color: #ffffff;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
    }
    .adv-card-stars span {
        font-size: 16px;
    }

    .adv-card-btn {
        width: 100%;
        padding: 14px 22px;
        background: #ffffff;
        border: 3px solid #ffffff;
        border-radius: 18px;
        font-family: inherit;
        font-size: 16px;
        font-weight: 1000;
        text-decoration: none;
        box-shadow: 0 6px 0 rgba(0, 0, 0, 0.2), 0 8px 18px rgba(0, 0, 0, 0.15);
        transition: all 0.18s;
        display: inline-block;
    }
    .card-green .adv-card-btn { color: #1e701e; }
    .card-blue .adv-card-btn { color: #12579b; }
    .card-purple .adv-card-btn { color: #5a1999; }

    .adv-card-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 0 rgba(0, 0, 0, 0.2), 0 12px 20px rgba(0, 0, 0, 0.2);
    }

    /* Bottom Hub: Daily Challenge & Shop */
    .adventure-bottom-hub {
        width: 100%;
        display: grid;
        grid-template-columns: 1.4fr 1fr;
        gap: 20px;
    }

    .hub-card {
        padding: 18px 24px;
        border-radius: 24px;
        display: flex;
        align-items: center;
        gap: 18px;
        border: 3.5px solid #ffffff;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
        text-decoration: none;
        transition: all 0.2s;
    }
    .hub-card:hover {
        transform: translateY(-4px);
    }

    .daily-challenge-card {
        background: linear-gradient(135deg, #ffffff, #f1f5f9);
        color: #1e293b;
    }
    .hub-icon {
        font-size: 42px;
        flex-shrink: 0;
        filter: drop-shadow(0 3px 6px rgba(0, 0, 0, 0.15));
    }
    .hub-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .hub-content b {
        font-size: 15px;
        font-weight: 1000;
        color: #0f172a;
    }
    .hub-content span {
        font-size: 11px;
        color: #475569;
        font-weight: 700;
    }
    .hub-progress-bar {
        height: 10px;
        background: #e2e8f0;
        border-radius: 999px;
        overflow: hidden;
        margin-top: 6px;
        border: 1px solid #cbd5e1;
    }
    .hub-progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #f59e0b, #fbbf24);
        border-radius: 999px;
    }
    .hub-counter {
        font-size: 15px;
        font-weight: 1000;
        color: #d97706;
        padding: 6px 12px;
        background: #fef3c7;
        border-radius: 12px;
        border: 1.5px solid #fde68a;
    }

    .shop-card {
        background: linear-gradient(135deg, #fcd34d, #f59e0b);
        color: #451a03;
    }
    .shop-card .hub-content b {
        color: #451a03;
    }
    .shop-card .hub-content span {
        color: #78350f;
    }
    .shop-badge {
        padding: 10px 18px;
        background: #78350f;
        border: 2px solid #ffffff;
        border-radius: 14px;
        color: #ffffff;
        font-size: 12px;
        font-weight: 1000;
        letter-spacing: 0.5px;
        box-shadow: 0 4px 0 #451a03;
        flex-shrink: 0;
    }

    /* Gamified Journey 3-Step Section - Frosted Glass Translucent */
    .game-journey-container {
        padding: 38px 30px;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.48), rgba(255, 255, 255, 0.22));
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        border: 3.5px solid rgba(255, 255, 255, 0.9);
        border-radius: 34px;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.16), inset 0 1px 0 rgba(255, 255, 255, 0.95);
        margin-bottom: 25px;
        color: #0f172a;
    }
    .journey-header {
        text-align: center;
        margin-bottom: 30px;
    }
    .journey-kicker {
        display: inline-block;
        padding: 6px 18px;
        background: linear-gradient(135deg, #fef08a, #facc15);
        border: 2px solid #eab308;
        border-radius: 999px;
        color: #713f12;
        font-size: 12px;
        font-weight: 1000;
        letter-spacing: 0.8px;
        margin-bottom: 10px;
        box-shadow: 0 4px 12px rgba(234, 179, 8, 0.35);
    }
    .journey-header h2 {
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 34px;
        color: #0b2f54;
        margin: 4px 0 8px;
        text-shadow: 0 2px 0 #ffffff, 0 0 16px rgba(255, 255, 255, 0.95);
    }
    .journey-header p {
        color: #1e293b;
        font-size: 15px;
        font-weight: 850;
        text-shadow: 0 1px 0 #ffffff, 0 0 12px rgba(255, 255, 255, 0.9);
    }

    .journey-steps-grid {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
    }
    .journey-step-card {
        flex: 1;
        padding: 28px 22px;
        border-radius: 26px;
        border: 4px solid #ffffff;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        box-shadow: 0 16px 35px rgba(0, 0, 0, 0.15), inset 0 -6px 0 rgba(0, 0, 0, 0.15);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        will-change: transform;
        backface-visibility: hidden;
        transform: translateZ(0);
    }
    .journey-step-card:hover {
        transform: translateY(-5px) translateZ(0);
        box-shadow: 0 22px 42px rgba(0, 0, 0, 0.22);
    }

    .step-cyan { background: linear-gradient(180deg, #62c3ff 0%, #2799f2 100%); }
    .step-gold { background: linear-gradient(180deg, #ffc848 0%, #ff9414 100%); }
    .step-mint { background: linear-gradient(180deg, #5ddc7c 0%, #20b545 100%); }

    .step-badge {
        padding: 5px 16px;
        border: 2px solid #ffffff;
        border-radius: 999px;
        color: #ffffff;
        font-size: 12px;
        font-weight: 1000;
        letter-spacing: 0.5px;
        margin-bottom: 14px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
    .step-cyan .step-badge { background: #0b4578; }
    .step-gold .step-badge { background: #78350f; }
    .step-mint .step-badge { background: #0c4d23; }

    .step-icon-orb {
        width: 86px;
        height: 86px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.45);
        border: 4px solid #ffffff;
        display: grid;
        place-items: center;
        font-size: 42px;
        margin-bottom: 16px;
        box-shadow: inset 0 -4px 0 rgba(0, 0, 0, 0.15), 0 8px 18px rgba(0, 0, 0, 0.12);
    }
    .journey-step-card h3 {
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 22px;
        font-weight: 700;
        margin-bottom: 8px;
        color: #ffffff;
    }
    .step-cyan h3 { text-shadow: 0 2px 4px #063966; }
    .step-gold h3 { text-shadow: 0 2px 4px #78350f; }
    .step-mint h3 { text-shadow: 0 2px 4px #0d4a25; }

    .journey-step-card p {
        font-size: 13px;
        line-height: 1.5;
        font-weight: 800;
        margin-bottom: 16px;
        color: #ffffff;
        min-height: 40px;
    }
    .step-cyan p { text-shadow: 0 1px 3px #063966; }
    .step-gold p { text-shadow: 0 1px 3px #78350f; }
    .step-mint p { text-shadow: 0 1px 3px #0d4a25; }

    .step-feature-tag {
        padding: 6px 16px;
        background: #ffffff;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 1000;
        box-shadow: 0 4px 0 rgba(0, 0, 0, 0.15);
    }
    .step-cyan .step-feature-tag { color: #09508c; }
    .step-gold .step-feature-tag { color: #853e00; }
    .step-mint .step-feature-tag { color: #0f5c2a; }

    .step-connector-arrow {
        font-size: 34px;
        font-weight: 1000;
        color: #f59e0b;
        filter: drop-shadow(0 2px 4px rgba(180, 83, 9, 0.4));
        animation: arrowPulse 1.5s infinite ease-in-out;
    }
    @keyframes arrowPulse {
        0%, 100% { transform: scale(1) translateX(0); }
        50% { transform: scale(1.15) translateX(4px); }
    }

    @media (max-width: 1024px) {
        .adventure-cards-grid {
            grid-template-columns: 1fr;
        }
        .adventure-bottom-hub {
            grid-template-columns: 1fr;
        }
        .journey-steps-grid {
            flex-direction: column;
        }
        .step-connector-arrow {
            transform: rotate(90deg);
        }
    }
</style>
@endsection
