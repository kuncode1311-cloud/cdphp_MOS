{{-- Hiển thị thành tích và phần thưởng; các số liệu được tính trong LearningController::achievements(). --}}
@extends('layouts.app')
@section('title', 'Bảng vàng Thành tích & Điểm thưởng — IC3 Digital Adventure')

@section('content')
<div class="adventure-world-wrapper">
    <div class="achievements-page-wrap">
        
        <!-- Header Capsule chuẩn phong cách IC3 Digital Adventure -->
        <div class="mission-chooser-container" style="margin-bottom: 24px; text-align: center;">
            <div class="choose-mission-capsule">
                <span>🏆</span> BẢNG VÀNG THÀNH TÍCH & ĐUA TOP HIỆP SĨ
            </div>
            <p class="mission-subtitle">
                Chăm chỉ làm bài luyện thi IC3 để tích lũy Sao thưởng, thắp sáng Huy hiệu và đổi lấy <b>Vé Chơi Game VIP</b>!
            </p>
        </div>

        <!-- ===================================================================
             3 THẺ NĂNG LƯỢNG & VÉ GAME 3D (TOP STAT PODS)
             =================================================================== -->
        <div class="stat-pods-grid">
            
            <!-- Thẻ 1: Sao Thưởng Tích Lũy -->
            <div class="stat-pod stat-pod-stars">
                <div class="pod-icon-orb pod-orb-gold">
                    <span class="pod-icon-emoji">⭐</span>
                </div>
                <div class="pod-content">
                    <span class="pod-label">SAO THƯỞNG CỦA BÉ</span>
                    <div class="pod-value">
                        <span id="header-reward-stars">{{ number_format($rewardStars) }}</span>
                        <span class="pod-unit">Sao</span>
                    </div>
                    <small class="pod-hint">Tích lũy từ mỗi bài luyện IC3</small>
                </div>
            </div>

            <!-- Thẻ 2: Thời Gian Chơi Game -->
            <div class="stat-pod stat-pod-game">
                <div class="pod-icon-orb pod-orb-blue">
                    <span class="pod-icon-emoji">⏳</span>
                </div>
                <div class="pod-content" style="flex: 1;">
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                        <div>
                            <span class="pod-label">THỜI GIAN CHƠI GAME</span>
                            <div class="pod-value" style="color: #1e3a8a;">
                                <span id="header-game-time">{{ floor($gameTimeSeconds / 60) }}p {{ $gameTimeSeconds % 60 }}s</span>
                            </div>
                        </div>
                        <a href="{{ route('games') }}" class="btn-quick-play">
                            <span>🎮</span> Chơi ngay
                        </a>
                    </div>
                    <small class="pod-hint">Đổi Sao thưởng lấy phút giải trí cực đã</small>
                </div>
            </div>

            <!-- Thẻ 3: Bài Đạt Chuẩn IC3 -->
            <div class="stat-pod stat-pod-passed">
                <div class="pod-icon-orb pod-orb-green">
                    <span class="pod-icon-emoji">🎯</span>
                </div>
                <div class="pod-content">
                    <span class="pod-label">BÀI ĐẠT CHUẨN IC3</span>
                    <div class="pod-value" style="color: #064e3b;">
                        {{ $weeklyPassed }} <span class="pod-unit" style="color: #059669;">/ {{ $weeklyAttempts->count() }} bài</span>
                    </div>
                    <small class="pod-hint">Chuẩn quốc tế IC3 Spark (≥ 700 điểm)</small>
                </div>
            </div>

        </div>

        <!-- ===================================================================
             🏪 CỬA HÀNG ĐỔI GIỜ CHƠI MINI-GAME (GAME TIME EXCHANGE SHOP)
             =================================================================== -->
        <div class="game-container-card shop-card-section">
            <div class="section-top-header">
                <div class="section-title-wrap">
                    <div class="section-icon-badge" style="background: linear-gradient(135deg, #f97316, #ea580c);">
                        <span>🏪</span>
                    </div>
                    <div>
                        <h3 class="section-title">CỬA HÀNG ĐỔI GIỜ CHƠI GAME HIỆP SĨ</h3>
                        <p class="section-subtitle">Dùng số Sao tích lũy được từ bài luyện thi để đổi lấy thời gian chơi mini-game thỏa thích!</p>
                    </div>
                </div>
                <div class="wallet-badge-pill">
                    <span class="wallet-icon">⭐</span>
                    <span>Ví Sao của bé: <b id="shop-wallet-stars">{{ number_format($rewardStars) }}</b></span>
                </div>
            </div>

            <div class="packages-grid">
                @foreach($packages as $pkgId => $pkg)
                    <div class="package-card {{ $pkgId == 2 ? 'package-card-highlight' : '' }}">
                        @if($pkgId == 2)
                            <div class="ribbon-hot">
                                <span>🔥 {{ $pkg['badge'] ?? 'HOT NHẤT' }}</span>
                            </div>
                        @endif

                        <div class="package-header">
                            <div class="package-orb {{ $pkgId == 2 ? 'orb-fire' : 'orb-lightning' }}">
                                <span>{{ $pkgId == 2 ? '🚀' : '⚡' }}</span>
                            </div>
                            <div>
                                <h4 class="package-title">{{ $pkg['title'] }}</h4>
                                <span class="package-reward">Nhận ngay <b>+{{ $pkg['minutes'] }} phút</b> chơi game</span>
                            </div>
                        </div>

                        <div class="package-pricing-box">
                            <span class="price-label">Giá quy đổi:</span>
                            <div class="price-val">
                                <b>{{ number_format($pkg['stars']) }}</b>
                                <span style="font-size: 18px; margin-left: 3px;">⭐</span>
                            </div>
                        </div>

                        <button type="button" 
                            onclick="exchangePackage({{ $pkgId }}, {{ $pkg['stars'] }}, {{ $pkg['minutes'] }})"
                            class="btn-exchange-action {{ $pkgId == 2 ? 'btn-exchange-fire' : 'btn-exchange-amber' }}"
                            id="btn-pkg-{{ $pkgId }}">
                            <span>✨</span> Đổi Gói Ngay
                        </button>
                    </div>
                @endforeach
            </div>

            <!-- Feedback thông báo kết quả đổi Sao -->
            <div id="exchange-feedback" class="exchange-feedback-box" style="display: none;"></div>
        </div>

        <!-- ===================================================================
             👑 BẢNG XẾP HẠNG HIỆP SĨ NHÍ — ĐUA TOP TOÀN KHỐI TUẦN NÀY
             =================================================================== -->
        <div class="game-container-card leaderboard-card-section" id="leaderboard-card-section">
            <div class="section-top-header">
                <div class="section-title-wrap">
                    <div class="section-icon-badge" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8);">
                        <span>👑</span>
                    </div>
                    <div>
                        <h3 class="section-title">BẢNG VÀNG THI ĐUA — ĐUA TOP TUẦN NÀY</h3>
                        <p class="section-subtitle">
                            Bảng xếp hạng cập nhật điểm thi đua theo từng khối lớp
                            @if(($resetPeriod ?? 'weekly') === 'monthly')
                                (Reset vào 00:00 Ngày 01 hàng tháng)
                            @elseif(($resetPeriod ?? 'weekly') === 'manual')
                                (Chu kỳ thi đua theo quyết định của Ban Quản Trị)
                            @else
                                (Reset tự động vào 00:00 Thứ Hai hàng tuần)
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Khung bao bọc nội dung Bảng vàng thi đua hỗ trợ AJAX Realtime -->
            <div id="leaderboard-ajax-wrapper" style="position: relative; min-height: 280px;">
                @include('learning.partials.leaderboard-content')
            </div>
        </div>

        <!-- ===================================================================
             🎖️ BỘ SƯU TẬP 6 HUY HIỆU HIỆP SĨ TUẦN NÀY (TROPHY CASE)
             =================================================================== -->
        <div class="game-container-card trophy-card-section">
            <div class="section-top-header">
                <div class="section-title-wrap">
                    <div class="section-icon-badge" style="background: linear-gradient(135deg, #eab308, #ca8a04);">
                        <span>🎖️</span>
                    </div>
                    <div>
                        <h3 class="section-title">BỘ SƯU TẬP HUY HIỆU HIỆP SĨ TUẦN NÀY</h3>
                        <p class="section-subtitle">Chinh phục các thử thách trong tuần để mở khóa toàn bộ huy hiệu danh dự (Reset vào 00:00 Thứ Hai)</p>
                    </div>
                </div>
            </div>

            @php
                $unlockedP1 = $weeklyAttempts->filter(fn ($a) => ($a->practiceTest?->topic?->position == 1) && $a->score >= 700)->count() > 0;
                $unlockedP4 = $weeklyAttempts->filter(fn ($a) => ($a->practiceTest?->topic?->position == 4) && $a->score >= 700)->count() > 0;
                $unlockedP6 = $weeklyAttempts->filter(fn ($a) => in_array($a->practiceTest?->topic?->position, [6, 7]) && $a->score >= 700)->count() > 0;
                $unlockedDiligent = $weeklyAttempts->count() >= 3;
                $unlockedPassed = $weeklyPassed >= 1;
                $unlockedMaster = $weeklyAttempts->where('score', '>=', 900)->count() > 0;

                $badges = [
                    [
                        'icon' => '🏆',
                        'title' => 'Chiến Binh IC3',
                        'desc' => 'Đạt mốc chuẩn ≥ 700đ tuần này',
                        'unlocked' => $unlockedPassed,
                        'tag' => $unlockedPassed ? 'ĐÃ MỞ KHÓA' : 'Chưa mở'
                    ],
                    [
                        'icon' => '👑',
                        'title' => 'Bậc Thầy Điểm 9+',
                        'desc' => 'Đạt điểm ≥ 900 điểm tuần này',
                        'unlocked' => $unlockedMaster,
                        'tag' => $unlockedMaster ? 'ĐÃ MỞ KHÓA' : 'Chưa mở'
                    ],
                    [
                        'icon' => '🔥',
                        'title' => 'Ngọn Lửa Chăm Chỉ',
                        'desc' => 'Luyện tập từ 3 bài trở lên tuần này',
                        'unlocked' => $unlockedDiligent,
                        'tag' => $unlockedDiligent ? 'ĐÃ MỞ KHÓA' : 'Chưa mở'
                    ],
                    [
                        'icon' => '🎨',
                        'title' => 'Chuyên Gia Sáng Tạo',
                        'desc' => 'Đạt ≥ 700đ bài Chủ đề 4 tuần này',
                        'unlocked' => $unlockedP4,
                        'tag' => $unlockedP4 ? 'ĐÃ MỞ KHÓA' : 'Chưa mở'
                    ],
                    [
                        'icon' => '💻',
                        'title' => 'Hiệp Sĩ Công Nghệ',
                        'desc' => 'Đạt ≥ 700đ bài Chủ đề 1 tuần này',
                        'unlocked' => $unlockedP1,
                        'tag' => $unlockedP1 ? 'ĐÃ MỞ KHÓA' : 'Chưa mở'
                    ],
                    [
                        'icon' => '🛡️',
                        'title' => 'Vệ Binh An Toàn',
                        'desc' => 'Đạt ≥ 700đ bài Chủ đề 6 tuần này',
                        'unlocked' => $unlockedP6,
                        'tag' => $unlockedP6 ? 'ĐÃ MỞ KHÓA' : 'Chưa mở'
                    ],
                ];
            @endphp

            <div class="trophy-badges-grid">
                @foreach($badges as $b)
                    <div class="trophy-badge-card {{ $b['unlocked'] ? 'badge-unlocked' : 'badge-locked' }}">
                        <div class="trophy-icon-orb {{ $b['unlocked'] ? 'orb-unlocked' : 'orb-locked' }}">
                            <span class="trophy-emoji">{{ $b['icon'] }}</span>
                            @if(!$b['unlocked'])
                                <span class="lock-indicator">🔒</span>
                            @endif
                        </div>
                        <b class="trophy-title">{{ $b['title'] }}</b>
                        <small class="trophy-desc">{{ $b['desc'] }}</small>
                        <div class="trophy-status-tag {{ $b['unlocked'] ? 'tag-unlocked' : 'tag-locked' }}">
                            {{ $b['tag'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- ===================================================================
             💡 BÍ KÍP CÀY SAO & LỐI TẮT LUYỆN TẬP
             =================================================================== -->
        <div class="adventure-cta-banner">
            <div class="cta-banner-content">
                <div class="cta-icon-orb">💡</div>
                <div>
                    <h4 class="cta-title">Bí kíp cày Sao & leo Top thần tốc:</h4>
                    <p class="cta-desc">
                        Mỗi bài thi hoàn thành sẽ tự động tích Sao thưởng vào ví. Hãy tích đủ <b>500 Sao</b> hoặc <b>1.000 Sao</b> để đổi lấy giờ chơi Mini-game giải trí cực đã nhé!
                    </p>
                </div>
            </div>
            <a href="{{ route('programs') }}" class="btn-cta-action">
                <span>🚀</span> Luyện thi nhận Sao ngay <b>➔</b>
            </a>
        </div>

    </div>
</div>

<style>
    /* =========================================================================
       IC3 DIGITAL ADVENTURE THEME — ACHIEVEMENTS & GAMIFICATION REDESIGN
       ========================================================================= */
    .adventure-world-wrapper {
        min-height: calc(100vh - 86px);
        padding: 30px 20px 80px;
        background: url('/images/adventure-world-bg.jpg') center/cover no-repeat fixed;
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
    }
    .adventure-world-wrapper:before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at 50% 25%, rgba(255, 255, 255, 0.12) 0%, rgba(6, 28, 56, 0.28) 100%);
        pointer-events: none;
    }
    .adventure-world-wrapper > * {
        position: relative;
        z-index: 2;
    }

    .achievements-page-wrap {
        width: min(1120px, 100%);
        margin: 0 auto;
    }

    /* Top Capsule Header */
    .choose-mission-capsule {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        padding: 12px 36px;
        background: linear-gradient(180deg, #1b4777 0%, #102e52 100%);
        border: 3.5px solid #6ed7ff;
        border-radius: 999px;
        color: #ffffff;
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 22px;
        font-weight: 700;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.35), inset 0 -3px 0 #07192e;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
    }
    .choose-mission-capsule span { font-size: 24px; }
    .mission-subtitle {
        margin: 8px 0 0;
        font-size: 14px;
        color: #ffffff;
        font-weight: 700;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.6);
    }

    /* 3D Stat Pods Grid */
    .stat-pods-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(290px, 1fr));
        gap: 18px;
        margin-bottom: 26px;
    }
    .stat-pod {
        background: rgba(255, 255, 255, 0.94);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 4px solid #ffffff;
        border-radius: 24px;
        padding: 18px 22px;
        display: flex;
        align-items: center;
        gap: 18px;
        box-shadow: 0 10px 24px rgba(10, 30, 60, 0.15), 0 2px 6px rgba(0, 0, 0, 0.05);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .stat-pod:hover {
        transform: translateY(-3px);
        box-shadow: 0 14px 30px rgba(10, 30, 60, 0.22);
    }

    .pod-icon-orb {
        width: 62px;
        height: 62px;
        border-radius: 20px;
        display: grid;
        place-items: center;
        font-size: 32px;
        flex-shrink: 0;
        border: 3px solid #ffffff;
        box-shadow: 0 6px 14px rgba(0,0,0,0.12), inset 0 -3px 0 rgba(0,0,0,0.15);
    }
    .pod-orb-gold { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .pod-orb-blue { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
    .pod-orb-green { background: linear-gradient(135deg, #10b981, #047857); }

    .pod-content { display: flex; flex-direction: column; }
    .pod-label {
        font-size: 11.5px;
        font-weight: 850;
        color: #64748b;
        letter-spacing: 0.6px;
        text-transform: uppercase;
    }
    .pod-value {
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 30px;
        font-weight: 700;
        line-height: 1.15;
        margin: 2px 0;
    }
    .stat-pod-stars .pod-value { color: #92400e; }
    .stat-pod-passed .pod-value { color: #064e3b; }
    .pod-unit {
        font-size: 15px;
        font-weight: 700;
        color: #b45309;
        margin-left: 2px;
    }
    .pod-hint {
        font-size: 12px;
        color: #475569;
        font-weight: 650;
    }

    .btn-quick-play {
        background: linear-gradient(180deg, #3b82f6 0%, #1d4ed8 100%);
        border: 2px solid #93c5fd;
        color: #ffffff;
        padding: 8px 16px;
        border-radius: 14px;
        font-size: 12px;
        font-weight: 850;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 4px 0 #1e40af, 0 6px 12px rgba(37,99,235,0.25);
        transition: transform 0.15s, box-shadow 0.15s;
        white-space: nowrap;
    }
    .btn-quick-play:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 0 #1e40af, 0 8px 16px rgba(37,99,235,0.35);
    }
    .btn-quick-play:active {
        transform: translateY(2px);
        box-shadow: 0 2px 0 #1e40af;
    }

    /* Container Card chung phong cách Glassmorphism Game */
    .game-container-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        border: 4.5px solid #ffffff;
        border-radius: 28px;
        padding: 26px 30px;
        box-shadow: 0 16px 36px rgba(10, 30, 60, 0.18), 0 4px 12px rgba(0, 0, 0, 0.06);
        margin-bottom: 26px;
    }

    /* Header của từng Box */
    .section-top-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 16px;
    }
    .section-title-wrap {
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .section-icon-badge {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        display: grid;
        place-items: center;
        font-size: 22px;
        border: 2.5px solid #ffffff;
        box-shadow: 0 4px 10px rgba(0,0,0,0.12);
        color: #ffffff;
        flex-shrink: 0;
    }
    .section-title {
        margin: 0;
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 20px;
        font-weight: 700;
        color: #0f2d4e;
        letter-spacing: 0.3px;
    }
    .section-subtitle {
        margin: 2px 0 0;
        font-size: 13px;
        color: #64748b;
        font-weight: 650;
    }

    /* Wallet Badge */
    .wallet-badge-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: linear-gradient(180deg, #fffbeb, #fef3c7);
        border: 2.5px solid #fde68a;
        padding: 7px 18px;
        border-radius: 999px;
        font-size: 13.5px;
        font-weight: 800;
        color: #92400e;
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.15);
    }
    .wallet-icon { font-size: 17px; }

    /* Packages Grid */
    .packages-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
    }
    .package-card {
        background: #ffffff;
        border: 3px solid #fed7aa;
        border-radius: 22px;
        padding: 22px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.05);
        position: relative;
        overflow: hidden;
        transition: transform 0.2s ease, border-color 0.2s ease;
    }
    .package-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 22px rgba(0, 0, 0, 0.08);
    }
    .package-card-highlight {
        border-color: #f97316;
        background: linear-gradient(180deg, #ffffff 0%, #fff7ed 100%);
    }

    .ribbon-hot {
        position: absolute;
        top: 12px;
        right: 12px;
        background: linear-gradient(135deg, #ea580c, #dc2626);
        color: #ffffff;
        font-size: 11px;
        font-weight: 900;
        padding: 4px 12px;
        border-radius: 999px;
        box-shadow: 0 2px 8px rgba(220, 38, 38, 0.35);
    }

    .package-header {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 16px;
    }
    .package-orb {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        display: grid;
        place-items: center;
        font-size: 26px;
        color: #ffffff;
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        flex-shrink: 0;
    }
    .orb-lightning { background: linear-gradient(135deg, #f59e0b, #fbbf24); }
    .orb-fire { background: linear-gradient(135deg, #ea580c, #f97316); }

    .package-title {
        margin: 0;
        font-size: 17px;
        font-weight: 900;
        color: #0f172a;
    }
    .package-reward {
        font-size: 13px;
        color: #64748b;
        font-weight: 650;
        display: block;
        margin-top: 2px;
    }
    .package-pricing-box {
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 14px;
        padding: 12px 16px;
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .price-label {
        font-size: 13px;
        font-weight: 750;
        color: #475569;
    }
    .price-val {
        display: flex;
        align-items: center;
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 20px;
        font-weight: 700;
        color: #ea580c;
    }

    /* 3D Action Buttons */
    .btn-exchange-action {
        width: 100%;
        border: none;
        padding: 14px 20px;
        border-radius: 16px;
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 16px;
        font-weight: 700;
        color: #ffffff;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .btn-exchange-amber {
        background: linear-gradient(180deg, #f59e0b 0%, #d97706 100%);
        border: 2px solid #fef3c7;
        box-shadow: 0 5px 0 #b45309, 0 8px 16px rgba(217, 119, 6, 0.3);
    }
    .btn-exchange-fire {
        background: linear-gradient(180deg, #ea580c 0%, #c2410c 100%);
        border: 2px solid #ffedd5;
        box-shadow: 0 5px 0 #9a3412, 0 8px 16px rgba(194, 65, 12, 0.35);
    }
    .btn-exchange-action:hover {
        transform: translateY(-2px);
    }
    .btn-exchange-action:active {
        transform: translateY(3px);
    }
    .btn-exchange-amber:active { box-shadow: 0 2px 0 #b45309; }
    .btn-exchange-fire:active { box-shadow: 0 2px 0 #9a3412; }

    .exchange-feedback-box {
        margin-top: 16px;
        padding: 14px 18px;
        border-radius: 14px;
        font-size: 13.5px;
        font-weight: 750;
        text-align: center;
    }

    /* Leaderboard Controls & Switcher */
    .leaderboard-controls-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    /* Grade Switcher Tabs 3D */
    .grade-filter-tabs {
        display: inline-flex;
        background: #f1f5f9;
        padding: 5px;
        border-radius: 18px;
        gap: 6px;
        border: 2.5px solid #e2e8f0;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    .grade-filter-pill {
        padding: 8px 18px;
        border-radius: 13px;
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 13.5px;
        font-weight: 700;
        text-decoration: none;
        color: #475569;
        background: transparent;
        border: 2px solid transparent;
        cursor: pointer;
        outline: none;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all 0.15s ease;
    }
    .grade-filter-pill:hover {
        color: #1e3a8a;
        background: rgba(255, 255, 255, 0.7);
    }
    .grade-filter-pill:active {
        transform: translateY(2px);
    }
    .grade-filter-pill.pill-active {
        background: linear-gradient(180deg, #3b82f6 0%, #1d4ed8 100%);
        color: #ffffff;
        border-color: #60a5fa;
        box-shadow: 0 4px 0 #1e40af, 0 6px 14px rgba(37, 99, 235, 0.3);
        transform: translateY(-1px);
    }

    /* Capsule đếm ngược kết thúc vòng đua */
    .leaderboard-timer-capsule {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 8px 16px;
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
        border: 2px solid #fde68a;
        border-radius: 999px;
        font-size: 12.5px;
        box-shadow: 0 3px 8px rgba(245, 158, 11, 0.15);
    }
    .leaderboard-timer-capsule .timer-icon {
        font-size: 15px;
        animation: pulseClock 2s infinite ease-in-out;
    }
    @keyframes pulseClock {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.18); }
    }
    .leaderboard-timer-capsule .timer-label {
        color: #92400e;
        font-weight: 700;
    }
    .leaderboard-timer-capsule .timer-countdown {
        color: #b45309;
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 13.5px;
        letter-spacing: 0.5px;
    }

    /* Animation mượt mà khi load AJAX */
    @keyframes leaderboardFadeIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .leaderboard-dynamic-fade-in {
        animation: leaderboardFadeIn 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .rank-top3-glow {
        box-shadow: 0 0 14px rgba(245, 158, 11, 0.6) !important;
        border-color: #fbbf24 !important;
    }

    /* My Rank Banner */
    .my-rank-banner {
        background: linear-gradient(135deg, #eff6ff 0%, #f0fdf4 100%);
        border: 2.5px solid #93c5fd;
        border-radius: 20px;
        padding: 14px 20px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 14px;
        box-shadow: 0 4px 14px rgba(37, 99, 235, 0.08);
    }
    .my-rank-left {
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .my-rank-circle {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: #ffffff;
        display: grid;
        place-items: center;
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 19px;
        font-weight: 700;
        border: 2.5px solid #ffffff;
        box-shadow: 0 3px 8px rgba(37, 99, 235, 0.35);
        flex-shrink: 0;
    }
    .my-rank-label {
        font-size: 11px;
        font-weight: 850;
        color: #1e40af;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        display: block;
    }
    .my-rank-name {
        font-size: 15px;
        color: #0f172a;
        margin-top: 1px;
    }
    .my-rank-stats {
        font-size: 13px;
        font-weight: 700;
        color: #059669;
        margin-left: 4px;
    }
    .my-rank-motivation {
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 14.5px;
        font-weight: 700;
        color: #1d4ed8;
    }

    /* =========================================================================
       3D OLYMPIC PODIUM SYSTEM
       ========================================================================= */
    .olympic-podium-wrap {
        display: grid;
        grid-template-columns: 1fr 1.15fr 1fr;
        gap: 18px;
        align-items: flex-end;
        margin-bottom: 28px;
        padding-top: 20px;
    }
    @media (max-width: 860px) {
        .olympic-podium-wrap {
            grid-template-columns: 1fr;
            gap: 20px;
        }
    }

    .podium-col {
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
    }
    .col-me .podium-card-top {
        box-shadow: 0 0 0 4px #2563eb, 0 12px 28px rgba(37, 99, 235, 0.25);
    }

    .podium-card-top {
        border-radius: 24px 24px 0 0;
        padding: 22px 18px 16px;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        border: 3px solid;
        border-bottom: none;
        position: relative;
    }
    .card-gold {
        background: linear-gradient(180deg, #fffbeb 0%, #fef08a 100%);
        border-color: #facc15;
        box-shadow: 0 10px 25px rgba(234, 179, 8, 0.2);
    }
    .card-silver {
        background: linear-gradient(180deg, #f8fafc 0%, #e2e8f0 100%);
        border-color: #cbd5e1;
        box-shadow: 0 8px 20px rgba(100, 116, 139, 0.15);
    }
    .card-bronze {
        background: linear-gradient(180deg, #fff7ed 0%, #ffedd5 100%);
        border-color: #fdba74;
        box-shadow: 0 8px 20px rgba(234, 88, 12, 0.15);
    }
    .card-placeholder {
        background: rgba(248, 250, 252, 0.6);
        border-color: #e2e8f0;
        border-style: dashed;
        min-height: 180px;
        justify-content: center;
    }
    .placeholder-icon { font-size: 32px; margin-bottom: 6px; }
    .placeholder-text { font-size: 13.5px; color: #64748b; font-weight: 800; }
    .placeholder-hint { font-size: 11.5px; color: #94a3b8; font-weight: 600; margin-top: 3px; }

    .crown-orb {
        position: absolute;
        top: -24px;
        font-size: 32px;
        filter: drop-shadow(0 4px 0 #b45309);
        animation: crownFloat 2s infinite ease-in-out;
    }
    @keyframes crownFloat {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-6px); }
    }

    .rank-badge-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 16px;
        border-radius: 999px;
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 13px;
        font-weight: 700;
        border: 2px solid;
        margin-bottom: 12px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
    }
    .pill-gold { background: #ffffff; color: #854d0e; border-color: #eab308; }
    .pill-silver { background: #ffffff; color: #334155; border-color: #94a3b8; }
    .pill-bronze { background: #ffffff; color: #9a3412; border-color: #f97316; }

    .podium-avatar {
        width: 58px;
        height: 58px;
        border-radius: 50%;
        color: #ffffff;
        display: grid;
        place-items: center;
        font-size: 24px;
        font-weight: 900;
        border: 3.5px solid #ffffff;
        box-shadow: 0 6px 14px rgba(0,0,0,0.15);
        margin-bottom: 8px;
    }
    .avatar-gold { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .avatar-silver { background: linear-gradient(135deg, #64748b, #475569); }
    .avatar-bronze { background: linear-gradient(135deg, #ea580c, #c2410c); }

    .podium-name {
        font-size: 15px;
        color: #0f172a;
        line-height: 1.25;
        display: block;
    }
    .tag-me {
        background: #2563eb;
        color: #ffffff;
        font-size: 10px;
        font-weight: 900;
        padding: 2px 7px;
        border-radius: 999px;
        margin-left: 4px;
        display: inline-block;
        vertical-align: middle;
    }
    .podium-class {
        font-size: 12px;
        color: #64748b;
        font-weight: 750;
        display: block;
        margin-top: 2px;
    }

    .podium-score {
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 26px;
        font-weight: 700;
        line-height: 1.15;
        margin: 8px 0 6px;
    }
    .score-gold { color: #854d0e; }
    .score-silver { color: #334155; }
    .score-bronze { color: #9a3412; }
    .podium-score small { font-size: 14px; font-weight: 650; }

    .podium-footer-meta {
        background: rgba(255, 255, 255, 0.8);
        border-radius: 12px;
        padding: 6px 12px;
        font-size: 11.5px;
        font-weight: 700;
        color: #475569;
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 4px;
        border: 1px solid rgba(0,0,0,0.06);
    }

    /* 3D Podium Step Blocks */
    .podium-step-block {
        border-radius: 0 0 20px 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 10px 20px rgba(0,0,0,0.12);
        position: relative;
    }
    .col-second .podium-step-block { height: 75px; }
    .col-first .podium-step-block { height: 105px; }
    .col-third .podium-step-block { height: 55px; }

    .step-gold {
        background: linear-gradient(180deg, #eab308 0%, #ca8a04 100%);
        border: 3px solid #fde047;
        border-top: none;
    }
    .step-silver {
        background: linear-gradient(180deg, #94a3b8 0%, #64748b 100%);
        border: 3px solid #cbd5e1;
        border-top: none;
    }
    .step-bronze {
        background: linear-gradient(180deg, #f97316 0%, #ea580c 100%);
        border: 3px solid #fdba74;
        border-top: none;
    }

    .step-face-front {
        text-align: center;
        color: #ffffff;
    }
    .step-num {
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 34px;
        font-weight: 800;
        line-height: 1;
        text-shadow: 0 2px 4px rgba(0,0,0,0.4);
    }
    .col-first .step-num { font-size: 42px; }
    .step-title {
        display: block;
        font-size: 10px;
        font-weight: 900;
        letter-spacing: 1px;
        opacity: 0.9;
        text-shadow: 0 1px 2px rgba(0,0,0,0.4);
    }

    /* =========================================================================
       TOP TIẾP THEO (HẠNG 4 – 10)
       ========================================================================= */
    .next-ranks-section {
        border-top: 2px dashed #e2e8f0;
        padding-top: 20px;
    }
    .next-ranks-header {
        font-size: 12.5px;
        font-weight: 850;
        color: #64748b;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        margin-bottom: 12px;
    }
    .next-ranks-grid {
        display: grid;
        gap: 10px;
    }
    .rank-row-item {
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 16px;
        padding: 12px 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        transition: transform 0.15s ease, background-color 0.15s ease, border-color 0.15s ease;
    }
    .rank-row-item:hover {
        transform: translateX(4px);
        background: #ffffff;
        border-color: #cbd5e1;
    }
    .rank-row-item.row-is-me {
        background: #eff6ff;
        border-color: #93c5fd;
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.12);
    }

    .rank-row-left {
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .rank-badge-num {
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 16px;
        font-weight: 700;
        color: #64748b;
        width: 32px;
        text-align: center;
    }
    .rank-row-avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: #e2e8f0;
        color: #334155;
        display: grid;
        place-items: center;
        font-size: 15px;
        font-weight: 800;
        flex-shrink: 0;
    }
    .row-is-me .rank-row-avatar {
        background: #2563eb;
        color: #ffffff;
    }

    .rank-row-title {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 14.5px;
        color: #0f172a;
    }
    .class-chip {
        background: #f1f5f9;
        color: #475569;
        font-size: 11.5px;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 999px;
        margin-left: 2px;
    }
    .rank-row-sub {
        font-size: 12px;
        color: #64748b;
        font-weight: 650;
        display: flex;
        align-items: center;
        gap: 6px;
        margin-top: 2px;
    }

    .rank-row-score {
        text-align: right;
    }
    .rank-row-score b {
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 18px;
        color: #0f172a;
        display: block;
        line-height: 1.1;
    }
    .rank-row-score small {
        font-size: 12px;
        color: #64748b;
        font-weight: 650;
    }

    /* =========================================================================
       TROPHY BADGES SHOWCASE (6 HUY HIỆU)
       ========================================================================= */
    .trophy-badges-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 16px;
    }
    .trophy-badge-card {
        border-radius: 20px;
        padding: 20px 14px;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: space-between;
        border: 2.5px solid;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .trophy-badge-card:hover {
        transform: translateY(-3px);
    }
    .badge-unlocked {
        background: linear-gradient(180deg, #fffbeb 0%, #fef3c7 100%);
        border-color: #fde68a;
        box-shadow: 0 6px 16px rgba(245, 158, 11, 0.12);
    }
    .badge-locked {
        background: rgba(248, 250, 252, 0.7);
        border-color: #e2e8f0;
        opacity: 0.68;
    }

    .trophy-icon-orb {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: grid;
        place-items: center;
        font-size: 30px;
        margin-bottom: 10px;
        position: relative;
    }
    .orb-unlocked {
        background: linear-gradient(135deg, #fef08a, #fde047);
        border: 3px solid #facc15;
        box-shadow: 0 4px 12px rgba(234, 179, 8, 0.3);
    }
    .orb-locked {
        background: #e2e8f0;
        border: 3px solid #cbd5e1;
    }
    .lock-indicator {
        position: absolute;
        bottom: -2px;
        right: -2px;
        font-size: 14px;
        background: #ffffff;
        border-radius: 50%;
        width: 22px;
        height: 22px;
        display: grid;
        place-items: center;
        box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    }

    .trophy-title {
        font-size: 13.5px;
        color: #0f172a;
        display: block;
        line-height: 1.25;
    }
    .trophy-desc {
        font-size: 11.5px;
        color: #64748b;
        font-weight: 650;
        display: block;
        margin-top: 4px;
        line-height: 1.35;
    }

    .trophy-status-tag {
        margin-top: 12px;
        font-size: 10.5px;
        font-weight: 900;
        padding: 3px 10px;
        border-radius: 999px;
        letter-spacing: 0.3px;
        border: 1.5px solid;
    }
    .tag-unlocked {
        background: #f0fdf4;
        color: #16a34a;
        border-color: #bbf7d0;
    }
    .tag-locked {
        background: #f1f5f9;
        color: #94a3b8;
        border-color: #e2e8f0;
    }

    /* =========================================================================
       CTA BANNER (BÍ KÍP CÀY SAO)
       ========================================================================= */
    .adventure-cta-banner {
        background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
        border: 3.5px solid #7dd3fc;
        border-radius: 24px;
        padding: 20px 26px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 18px;
        box-shadow: 0 12px 28px rgba(2, 132, 199, 0.3);
        color: #ffffff;
    }
    .cta-banner-content {
        display: flex;
        align-items: center;
        gap: 16px;
        flex: 1;
        min-width: 280px;
    }
    .cta-icon-orb {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        background: rgba(255, 255, 255, 0.2);
        border: 2px solid rgba(255, 255, 255, 0.35);
        display: grid;
        place-items: center;
        font-size: 26px;
        flex-shrink: 0;
    }
    .cta-title {
        margin: 0;
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 18px;
        font-weight: 700;
        color: #fef08a;
    }
    .cta-desc {
        margin: 3px 0 0;
        font-size: 13px;
        color: #e0f2fe;
        font-weight: 650;
        line-height: 1.4;
    }
    .btn-cta-action {
        background: #ffffff;
        color: #0369a1;
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 15px;
        font-weight: 700;
        padding: 12px 24px;
        border-radius: 16px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 5px 0 #0284c7, 0 8px 16px rgba(0,0,0,0.15);
        white-space: nowrap;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .btn-cta-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 7px 0 #0284c7, 0 10px 20px rgba(0,0,0,0.2);
    }
    .btn-cta-action:active {
        transform: translateY(3px);
        box-shadow: 0 2px 0 #0284c7;
    }
</style>

<script>
    let currentStars = {{ (int) $rewardStars }};
    let currentGameTime = {{ (int) $gameTimeSeconds }};

    function formatTime(seconds) {
        const m = Math.floor(seconds / 60);
        const s = seconds % 60;
        return `${m}p ${s}s`;
    }

    async function exchangePackage(packageId, stars, minutes) {
        const btn = document.getElementById(`btn-pkg-${packageId}`);
        const feedback = document.getElementById('exchange-feedback');

        if (currentStars < stars) {
            feedback.style.display = 'block';
            feedback.style.background = '#fef2f2';
            feedback.style.color = '#b91c1c';
            feedback.style.border = '2px solid #fecaca';
            feedback.innerHTML = `⚠️ Bé chưa đủ Sao thưởng (Hiện có ${currentStars.toLocaleString('vi-VN')} ⭐ / Cần ${stars.toLocaleString('vi-VN')} ⭐). Hãy hoàn thành thêm bài thi để nhận thêm Sao nhé!`;
            return;
        }

        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span>⏳</span> Đang quy đổi...';
        feedback.style.display = 'none';

        try {
            const res = await fetch('{{ route('games.exchange') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ package: packageId })
            });

            const data = await res.json();

            if (data.success) {
                currentStars = data.remaining_stars;
                currentGameTime = data.game_time_seconds;

                // Update UI elements
                const headerStarsEl = document.getElementById('header-reward-stars');
                const shopStarsEl = document.getElementById('shop-wallet-stars');
                const gameTimeEl = document.getElementById('header-game-time');

                if (headerStarsEl) headerStarsEl.innerText = currentStars.toLocaleString('vi-VN');
                if (shopStarsEl) shopStarsEl.innerText = currentStars.toLocaleString('vi-VN');
                if (gameTimeEl) gameTimeEl.innerText = formatTime(currentGameTime);

                feedback.style.display = 'block';
                feedback.style.background = '#f0fdf4';
                feedback.style.color = '#15803d';
                feedback.style.border = '2px solid #bbf7d0';
                feedback.innerHTML = `🎉 Tuyệt vời! ${data.message} (Thời gian chơi hiện có: <b>${formatTime(currentGameTime)}</b>). <a href="{{ route('games') }}" style="color: #15803d; text-decoration: underline; font-weight: 800; margin-left: 6px;">Vào chơi ngay ➔</a>`;
            } else {
                feedback.style.display = 'block';
                feedback.style.background = '#fef2f2';
                feedback.style.color = '#b91c1c';
                feedback.style.border = '2px solid #fecaca';
                feedback.innerHTML = `⚠️ ${data.message || 'Không thể đổi gói. Vui lòng thử lại!'}`;
            }
        } catch (err) {
            feedback.style.display = 'block';
            feedback.style.background = '#fef2f2';
            feedback.style.color = '#b91c1c';
            feedback.style.border = '2px solid #fecaca';
            feedback.innerHTML = '⚠️ Lỗi kết nối máy chủ. Vui lòng kiểm tra lại mạng.';
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    }

    // =========================================================================
    // ⚡ CHUYỂN ĐỔI TAB KHỐI LỚP BẢNG VÀNG REAL-TIME AJAX (KHÔNG RELOAD TRANG)
    // =========================================================================
    let isLeaderboardLoading = false;
    let leaderboardCountdownInterval = null;

    async function switchLeaderboardGrade(grade) {
        if (isLeaderboardLoading) return;
        const wrapper = document.getElementById('leaderboard-ajax-wrapper');
        if (!wrapper) return;

        isLeaderboardLoading = true;

        // Cập nhật giao diện tab ngay lập tức (instant visual feedback)
        document.querySelectorAll('.grade-filter-pill').forEach(pill => {
            const isActive = pill.getAttribute('data-grade') === String(grade);
            pill.classList.toggle('pill-active', isActive);
        });

        // Hiệu ứng mờ nhẹ khi tải
        const container = document.getElementById('leaderboard-dynamic-container');
        if (container) {
            container.style.transition = 'opacity 0.15s ease';
            container.style.opacity = '0.45';
        }

        try {
            const url = `{{ route('achievements') }}?grade=${encodeURIComponent(grade)}`;
            const res = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (!res.ok) throw new Error('Không thể tải bảng xếp hạng');
            const data = await res.json();

            if (data.success && data.html) {
                wrapper.innerHTML = data.html;

                // Cập nhật URL trên thanh địa chỉ mà không reload trang
                const cleanUrl = new URL(window.location.href);
                cleanUrl.searchParams.set('grade', grade);
                window.history.replaceState({ grade: grade }, '', cleanUrl.toString());

                // Khởi động lại đồng hồ đếm ngược
                initLeaderboardTimer();
            }
        } catch (err) {
            console.error('Lỗi tải bảng xếp hạng:', err);
            if (container) container.style.opacity = '1';
        } finally {
            isLeaderboardLoading = false;
        }
    }

    // =========================================================================
    // ⏱️ ĐỒNG HỒ ĐẾM NGƯỢC THỜI GIAN KẾT THÚC VÒNG THI ĐUA
    // =========================================================================
    function initLeaderboardTimer() {
        if (leaderboardCountdownInterval) {
            clearInterval(leaderboardCountdownInterval);
            leaderboardCountdownInterval = null;
        }

        const capsule = document.getElementById('leaderboard-timer-capsule');
        const textEl = document.getElementById('live-countdown-text');
        if (!capsule || !textEl) return;

        const targetTs = parseInt(capsule.getAttribute('data-next-reset'), 10);
        if (!targetTs || isNaN(targetTs)) return;

        function updateCountdown() {
            const nowSec = Math.floor(Date.now() / 1000);
            let diff = targetTs - nowSec;

            if (diff <= 0) {
                textEl.innerText = 'Đang reset vòng mới...';
                return;
            }

            const days = Math.floor(diff / 86400);
            diff %= 86400;
            const hours = Math.floor(diff / 3600);
            diff %= 3600;
            const minutes = Math.floor(diff / 60);
            const seconds = diff % 60;

            if (days > 0) {
                textEl.innerText = `${days} ngày ${hours}h ${minutes}p`;
            } else if (hours > 0) {
                textEl.innerText = `${hours} giờ ${minutes}p ${seconds}s`;
            } else {
                textEl.innerText = `${minutes} phút ${seconds}s`;
            }
        }

        updateCountdown();
        leaderboardCountdownInterval = setInterval(updateCountdown, 1000);
    }

    document.addEventListener('DOMContentLoaded', () => {
        initLeaderboardTimer();
    });
</script>
@endsection
