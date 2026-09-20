{{-- Hiển thị thành tích và phần thưởng; các số liệu được tính trong LearningController::achievements(). --}}
@extends('layouts.app')
@section('title', 'Bảng vàng Thành tích & Điểm thưởng — IC3 Digital Adventure')

@section('content')
<div class="adventure-world-wrapper">
    <div class="achievements-page-wrap">
        
        <!-- Header Capsule chuẩn phong cách IC3 Digital Adventure -->
        <div class="mission-chooser-container">
            <div class="choose-mission-capsule">
                <span class="capsule-trophy-icon">🏆</span>
                <span>BẢNG VÀNG THÀNH TÍCH & ĐUA TOP HIỆP SĨ</span>
            </div>
            <p class="mission-subtitle">
                Chăm chỉ làm bài luyện thi IC3 để tích lũy <b>Sao thưởng</b>, thắp sáng <b>Huy hiệu</b> và đổi lấy <b>Vé Chơi Game VIP</b>!
            </p>
        </div>

        <!-- ===================================================================
             3 VIÊN TINH THỂ NĂNG LƯỢNG 3D (TOP ENERGY PODS)
             =================================================================== -->
        <div class="stat-pods-grid">
            
            <!-- Pod 1: Ví Sao Thưởng Tích Lũy -->
            <div class="stat-pod stat-pod-stars">
                <div class="pod-icon-orb orb-gold-sparkle">
                    <span class="pod-icon-emoji">⭐</span>
                </div>
                <div class="pod-content">
                    <span class="pod-label">VÍ SAO THƯỞNG CỦA BÉ</span>
                    <div class="pod-value value-gold">
                        <span id="header-reward-stars">{{ number_format($rewardStars) }}</span>
                        <span class="pod-unit">Sao</span>
                    </div>
                    <small class="pod-hint">Dùng đổi vé chơi game tại Cửa Hàng</small>
                </div>
            </div>

            <!-- Pod 2: Thời Gian Chơi Game -->
            <div class="stat-pod stat-pod-game">
                <div class="pod-icon-orb orb-blue-game">
                    <span class="pod-icon-emoji">⏳</span>
                </div>
                <div class="pod-content" style="flex: 1;">
                    <div class="pod-row-split">
                        <div>
                            <span class="pod-label">THỜI GIAN CHƠI GAME</span>
                            <div class="pod-value value-blue">
                                <span id="header-game-time">{{ floor($gameTimeSeconds / 60) }}p {{ $gameTimeSeconds % 60 }}s</span>
                            </div>
                        </div>
                        <a href="{{ route('games') }}" class="btn-3d-play-quick">
                            <span>🎮</span> Chơi Ngay
                        </a>
                    </div>
                    <small class="pod-hint">Thời gian trải nghiệm các mini-game giải trí</small>
                </div>
            </div>

            <!-- Pod 3: Bài Đạt Chuẩn IC3 -->
            @php
                $totalWeekly = $weeklyAttempts->count();
                $passRate = $totalWeekly > 0 ? round(($weeklyPassed / $totalWeekly) * 100) : 0;
            @endphp
            <div class="stat-pod stat-pod-passed">
                <div class="pod-icon-orb orb-green-target">
                    <span class="pod-icon-emoji">🎯</span>
                </div>
                <div class="pod-content">
                    <span class="pod-label">BÀI ĐẠT CHUẨN IC3 (≥ 700Đ)</span>
                    <div class="pod-value value-green">
                        {{ $weeklyPassed }} <span class="pod-unit">/ {{ $totalWeekly }} bài</span>
                    </div>
                    <div class="pod-mini-progress">
                        <div class="mini-bar-track">
                            <div class="mini-bar-fill" style="width: {{ $passRate }}%;"></div>
                        </div>
                        <span class="mini-bar-label">Tỷ lệ đạt chuẩn: <b>{{ $passRate }}%</b></span>
                    </div>
                </div>
            </div>

        </div>

        <!-- ===================================================================
             🏪 CỬA HÀNG ĐỔI GIỜ CHƠI MINI-GAME (GAME TIME EXCHANGE SHOP)
             =================================================================== -->
        <div class="game-container-card shop-card-section">
            <div class="section-top-header">
                <div class="section-title-wrap">
                    <div class="section-icon-badge badge-orange-shop">
                        <span>🏪</span>
                    </div>
                    <div>
                        <h3 class="section-title">CỬA HÀNG ĐỔI GIỜ CHƠI GAME HIỆP SĨ</h3>
                        <p class="section-subtitle">Đổi số Sao tích lũy được từ bài luyện thi lấy thời gian chơi game thỏa thích!</p>
                    </div>
                </div>
                <div class="wallet-badge-pill">
                    <span class="wallet-icon">⭐</span>
                    <span>Ví Sao hiện có: <b id="shop-wallet-stars">{{ number_format($rewardStars) }}</b> Sao</span>
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
                    <div class="section-icon-badge badge-blue-crown">
                        <span>👑</span>
                    </div>
                    <div>
                        <h3 class="section-title">BẢNG VÀNG THI ĐUA — ĐUA TOP TUẦN NÀY</h3>
                        <p class="section-subtitle">
                            Bảng xếp hạng cập nhật điểm thi đua theo từng khối lớp
                            @if(($resetPeriod ?? 'weekly') === 'monthly')
                                (Chu kỳ Tháng — Reset vào 00:00 Ngày 01 hàng tháng)
                            @elseif(($resetPeriod ?? 'weekly') === 'manual')
                                (Chu kỳ thi đua mở bởi Ban Quản Trị)
                            @else
                                (Chu kỳ Tuần — Reset tự động vào 00:00 Thứ Hai hàng tuần)
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
                    <div class="section-icon-badge badge-gold-medal">
                        <span>🎖️</span>
                    </div>
                    <div>
                        <h3 class="section-title">BỘ SƯU TẬP HUY HIỆU HIỆP SĨ TUẦN NÀY</h3>
                        <p class="section-subtitle">Chinh phục thử thách tuần để mở khóa Huân chương danh dự và nhận thêm Sao thưởng!</p>
                    </div>
                </div>
            </div>

            <!-- 💡 Hướng dẫn & Ý nghĩa của Huy hiệu (Badge Guide Capsule) -->
            <div class="badge-guide-capsule">
                <div class="guide-icon-orb">💡</div>
                <div class="guide-text-box">
                    <b class="guide-title">Huy Hiệu Danh Dự Là Gì & Thưởng Như Thế Nào?</b>
                    <p class="guide-desc">
                        Mỗi tuần, hệ thống trao tặng <b>6 Huân chương thử thách</b> cho các hiệp sĩ nhí. 
                        Mỗi khi mở khóa thành công 1 Huy hiệu, bé sẽ được <b>thưởng nóng từ +150 ⭐ đến +250 ⭐ Sao vào Ví</b> (dùng đổi vé chơi game thỏa thích), 
                        đồng thời Huân chương sẽ được gắn sáng lấp lánh trên Hồ sơ cá nhân của bé! Bấm vào từng Huy hiệu để xem chi tiết nhé!
                    </p>
                </div>
            </div>

            <!-- Lưới 6 Thẻ Huy Hiệu 3D Kim Loại Đẳng Cấp -->
            <div class="trophy-badges-grid">
                @foreach($badges as $b)
                    <div class="trophy-badge-card {{ $b['unlocked'] ? 'badge-card-unlocked' : 'badge-card-locked' }}"
                         onclick="openBadgeModal('{{ $b['id'] }}')">
                        
                        <!-- Dải ruy-băng / Tag phân loại huy hiệu -->
                        <div class="badge-type-pill {{ $b['unlocked'] ? 'type-pill-unlocked' : 'type-pill-locked' }}">
                            {{ $b['badge_type'] }}
                        </div>

                        <!-- Vòng tròn Huân Chương 3D -->
                        <div class="badge-orb-3d {{ $b['unlocked'] ? 'orb-3d-unlocked' : 'orb-3d-locked' }}">
                            <span class="badge-emoji">{{ $b['icon'] }}</span>
                            @if(!$b['unlocked'])
                                <span class="badge-lock-tag" title="Chưa mở khóa">🔒</span>
                            @else
                                <span class="badge-check-tag" title="Đã đạt huân chương">✓</span>
                            @endif
                        </div>

                        <!-- Tên & Thể lệ -->
                        <b class="badge-item-title">{{ $b['badge_name'] }}</b>
                        <small class="badge-item-rule">{{ $b['rule'] }}</small>

                        <!-- Hộp thưởng Sao -->
                        <div class="badge-reward-pill {{ $b['unlocked'] ? 'reward-pill-received' : 'reward-pill-pending' }}">
                            @if($b['unlocked'])
                                <span>✨ Đã nhận +{{ $b['reward_stars'] }} ⭐</span>
                            @else
                                <span>🎁 Thưởng +{{ $b['reward_stars'] }} ⭐</span>
                            @endif
                        </div>

                        <!-- Thanh tiến độ mini -->
                        <div class="badge-progress-box">
                            <div class="badge-progress-track">
                                <div class="badge-progress-fill {{ $b['unlocked'] ? 'fill-complete' : 'fill-pending' }}" style="width: {{ $b['percent'] }}%;"></div>
                            </div>
                            <span class="badge-progress-label">{{ $b['progress_text'] }}</span>
                        </div>

                        <!-- Nút bấm hành động / xem chi tiết -->
                        <button type="button" class="btn-badge-trigger {{ $b['unlocked'] ? 'btn-trigger-unlocked' : 'btn-trigger-locked' }}">
                            @if($b['unlocked'])
                                <span>✨</span> Xem Huân Chương
                            @else
                                <span>🔍</span> Thể Lệ & Chinh Phục
                            @endif
                        </button>
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
                        Mỗi bài thi hoàn thành sẽ tự động tích lũy điểm và Sao thưởng vào ví. Hãy tích đủ <b>500 Sao</b> hoặc <b>1.000 Sao</b> để đổi lấy giờ chơi Mini-game giải trí cực đã nhé!
                    </p>
                </div>
            </div>
            <a href="{{ route('programs') }}" class="btn-cta-action">
                <span>🚀</span> Luyện thi nhận Sao ngay <b>➔</b>
            </a>
        </div>

    </div>
</div>

<!-- ===================================================================
     MODAL POPUP 3D CHI TIẾT HUY HIỆU DANH DỰ
     =================================================================== -->
<div id="badge-detail-modal" class="badge-modal-overlay" style="display: none;" onclick="if(event.target === this) closeBadgeModal()">
    <div class="badge-modal-box">
        <button type="button" class="modal-close-btn" onclick="closeBadgeModal()">✕</button>

        <div class="modal-hero-orb" id="modal-badge-orb">
            <span id="modal-badge-icon">🏆</span>
        </div>

        <div class="modal-header-info">
            <span class="modal-badge-type" id="modal-badge-type">HOÀNG GIA</span>
            <h3 class="modal-badge-title" id="modal-badge-title">Chiến Binh IC3</h3>
            <p class="modal-badge-meaning" id="modal-badge-meaning">Vinh danh tinh thần học tập chuẩn quốc tế IC3.</p>
        </div>

        <div class="modal-stats-card">
            <div class="modal-stat-row">
                <span class="stat-row-label">🎯 Thể lệ thử thách:</span>
                <b class="stat-row-val" id="modal-badge-rule">Đạt mốc chuẩn ≥ 700 điểm tuần này</b>
            </div>

            <div class="modal-stat-row">
                <span class="stat-row-label">⭐ Phần thưởng mở khóa:</span>
                <b class="stat-row-val val-reward" id="modal-badge-reward">+200 Sao thưởng vào ví</b>
            </div>

            <div class="modal-stat-row" style="border-bottom: none; padding-bottom: 0;">
                <span class="stat-row-label">📊 Tiến độ của bé:</span>
                <b class="stat-row-val" id="modal-badge-progress">1 / 1 bài (Đã hoàn thành)</b>
            </div>

            <div class="modal-progress-track">
                <div class="modal-progress-fill" id="modal-progress-fill" style="width: 100%;"></div>
            </div>
        </div>

        <div class="modal-action-footer">
            <a href="{{ route('programs') }}" id="modal-action-btn" class="btn-modal-action">
                <span>🚀</span> Chinh Phục Ngay
            </a>
            <button type="button" class="btn-modal-close" onclick="closeBadgeModal()">Đóng</button>
        </div>
    </div>
</div>

<style>
    /* =========================================================================
       IC3 DIGITAL ADVENTURE THEME — ACHIEVEMENTS & GAMIFICATION 3D REDESIGN
       ========================================================================= */
    .adventure-world-wrapper {
        min-height: calc(100vh - 86px);
        padding: 26px 18px 80px;
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
        background: radial-gradient(circle at 50% 20%, rgba(255, 255, 255, 0.15) 0%, rgba(6, 28, 56, 0.32) 100%);
        pointer-events: none;
    }
    .adventure-world-wrapper > * {
        position: relative;
        z-index: 2;
    }

    .achievements-page-wrap {
        width: min(1140px, 100%);
        margin: 0 auto;
    }

    /* Top Capsule Header */
    .mission-chooser-container {
        margin-bottom: 22px;
        text-align: center;
    }
    .choose-mission-capsule {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        padding: 10px 32px;
        background: linear-gradient(180deg, #1b4777 0%, #102e52 100%);
        border: 3.5px solid #6ed7ff;
        border-radius: 999px;
        color: #ffffff;
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 21px;
        font-weight: 700;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35), inset 0 -3px 0 #07192e;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
    }
    .capsule-trophy-icon { font-size: 24px; }
    .mission-subtitle {
        margin: 8px 0 0;
        font-size: 13.5px;
        color: #ffffff;
        font-weight: 700;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.6);
    }

    /* 3D Stat Pods Grid */
    .stat-pods-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(290px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    .stat-pod {
        background: rgba(255, 255, 255, 0.96);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        border: 3.5px solid #ffffff;
        border-radius: 22px;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: 0 10px 24px rgba(10, 30, 60, 0.14), inset 0 -3px 0 rgba(0,0,0,0.06);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .stat-pod:hover {
        transform: translateY(-3px);
        box-shadow: 0 14px 28px rgba(10, 30, 60, 0.2);
    }

    .pod-icon-orb {
        width: 58px;
        height: 58px;
        border-radius: 18px;
        display: grid;
        place-items: center;
        font-size: 28px;
        flex-shrink: 0;
        border: 2.5px solid #ffffff;
        box-shadow: 0 6px 14px rgba(0,0,0,0.12), inset 0 -3px 0 rgba(0,0,0,0.15);
    }
    .orb-gold-sparkle { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .orb-blue-game { background: linear-gradient(135deg, #38bdf8, #0284c7); }
    .orb-green-target { background: linear-gradient(135deg, #10b981, #059669); }

    .pod-content { display: flex; flex-direction: column; flex: 1; }
    .pod-label {
        font-size: 11px;
        font-weight: 850;
        color: #64748b;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }
    .pod-value {
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 27px;
        font-weight: 700;
        line-height: 1.15;
        margin: 2px 0;
    }
    .value-gold { color: #b45309; }
    .value-blue { color: #0369a1; }
    .value-green { color: #065f46; }
    .pod-unit {
        font-size: 14.5px;
        font-weight: 700;
        color: #64748b;
        margin-left: 2px;
    }
    .pod-hint {
        font-size: 11.5px;
        color: #64748b;
        font-weight: 650;
    }

    .pod-row-split {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }
    .btn-3d-play-quick {
        background: linear-gradient(180deg, #0284c7 0%, #0369a1 100%);
        border: 2px solid #7dd3fc;
        color: #ffffff;
        padding: 7px 14px;
        border-radius: 12px;
        font-size: 11.5px;
        font-weight: 850;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        box-shadow: 0 3px 0 #024e75, 0 5px 10px rgba(2, 132, 199, 0.25);
        transition: transform 0.15s, box-shadow 0.15s;
        white-space: nowrap;
    }
    .btn-3d-play-quick:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 0 #024e75, 0 8px 14px rgba(2, 132, 199, 0.35);
    }
    .btn-3d-play-quick:active {
        transform: translateY(2px);
        box-shadow: 0 1px 0 #024e75;
    }

    .pod-mini-progress {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 3px;
    }
    .mini-bar-track {
        flex: 1;
        height: 6px;
        background: #e2e8f0;
        border-radius: 999px;
        overflow: hidden;
    }
    .mini-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, #10b981, #059669);
        border-radius: 999px;
        transition: width 0.3s ease;
    }
    .mini-bar-label {
        font-size: 11px;
        color: #047857;
        font-weight: 700;
        white-space: nowrap;
    }

    /* Container Card chung */
    .game-container-card {
        background: rgba(255, 255, 255, 0.96);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        border: 4px solid #ffffff;
        border-radius: 26px;
        padding: 24px 26px;
        box-shadow: 0 14px 34px rgba(10, 30, 60, 0.15), 0 3px 10px rgba(0, 0, 0, 0.05);
        margin-bottom: 24px;
    }

    .section-top-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 18px;
        flex-wrap: wrap;
        gap: 14px;
    }
    .section-title-wrap {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .section-icon-badge {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        display: grid;
        place-items: center;
        font-size: 22px;
        border: 2px solid #ffffff;
        box-shadow: 0 4px 10px rgba(0,0,0,0.12);
        color: #ffffff;
        flex-shrink: 0;
    }
    .badge-orange-shop { background: linear-gradient(135deg, #f97316, #ea580c); }
    .badge-blue-crown { background: linear-gradient(135deg, #0284c7, #1d4ed8); }
    .badge-gold-medal { background: linear-gradient(135deg, #eab308, #ca8a04); }

    .section-title {
        margin: 0;
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 19px;
        font-weight: 700;
        color: #0f2d4e;
        letter-spacing: 0.3px;
    }
    .section-subtitle {
        margin: 2px 0 0;
        font-size: 12.5px;
        color: #64748b;
        font-weight: 650;
    }

    /* Wallet Badge */
    .wallet-badge-pill {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        background: linear-gradient(180deg, #fffbeb, #fef3c7);
        border: 2px solid #fde68a;
        padding: 6px 16px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 800;
        color: #92400e;
        box-shadow: 0 3px 8px rgba(245, 158, 11, 0.15);
    }
    .wallet-icon { font-size: 16px; }

    /* Packages Grid */
    .packages-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 18px;
    }
    .package-card {
        background: #ffffff;
        border: 2.5px solid #fed7aa;
        border-radius: 20px;
        padding: 20px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
        position: relative;
        overflow: hidden;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .package-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
    }
    .package-card-highlight {
        border-color: #f97316;
        background: linear-gradient(180deg, #ffffff 0%, #fff7ed 100%);
    }

    .ribbon-hot {
        position: absolute;
        top: 10px;
        right: 10px;
        background: linear-gradient(135deg, #ea580c, #dc2626);
        color: #ffffff;
        font-size: 10.5px;
        font-weight: 900;
        padding: 3px 10px;
        border-radius: 999px;
        box-shadow: 0 2px 6px rgba(220, 38, 38, 0.35);
    }
    .package-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 14px;
    }
    .package-orb {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: grid;
        place-items: center;
        font-size: 24px;
        color: #ffffff;
        box-shadow: 0 3px 8px rgba(0,0,0,0.12);
        flex-shrink: 0;
    }
    .orb-lightning { background: linear-gradient(135deg, #f59e0b, #fbbf24); }
    .orb-fire { background: linear-gradient(135deg, #ea580c, #f97316); }

    .package-title {
        margin: 0;
        font-size: 16px;
        font-weight: 900;
        color: #0f172a;
    }
    .package-reward {
        font-size: 12.5px;
        color: #64748b;
        font-weight: 650;
        display: block;
        margin-top: 2px;
    }
    .package-pricing-box {
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        padding: 10px 14px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .price-label {
        font-size: 12.5px;
        font-weight: 750;
        color: #475569;
    }
    .price-val {
        display: flex;
        align-items: center;
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 19px;
        font-weight: 700;
        color: #ea580c;
    }

    .btn-exchange-action {
        width: 100%;
        border: none;
        padding: 12px 18px;
        border-radius: 14px;
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 15px;
        font-weight: 700;
        color: #ffffff;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .btn-exchange-amber {
        background: linear-gradient(180deg, #f59e0b 0%, #d97706 100%);
        border: 2px solid #fef3c7;
        box-shadow: 0 4px 0 #b45309, 0 6px 12px rgba(217, 119, 6, 0.25);
    }
    .btn-exchange-fire {
        background: linear-gradient(180deg, #ea580c 0%, #c2410c 100%);
        border: 2px solid #ffedd5;
        box-shadow: 0 4px 0 #9a3412, 0 6px 12px rgba(194, 65, 12, 0.3);
    }
    .btn-exchange-action:hover { transform: translateY(-2px); }
    .btn-exchange-action:active { transform: translateY(2px); }
    .btn-exchange-amber:active { box-shadow: 0 1px 0 #b45309; }
    .btn-exchange-fire:active { box-shadow: 0 1px 0 #9a3412; }

    .exchange-feedback-box {
        margin-top: 14px;
        padding: 12px 16px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 750;
        text-align: center;
    }

    /* =========================================================================
       BẢNG VÀNG THI ĐUA CONTROLS & OLYMPIC STAGE STYLES
       ========================================================================= */
    .leaderboard-controls-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 18px;
        flex-wrap: wrap;
    }

    .grade-filter-tabs {
        display: inline-flex;
        background: #f1f5f9;
        padding: 4px;
        border-radius: 16px;
        gap: 6px;
        border: 2px solid #e2e8f0;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    .grade-filter-pill {
        padding: 7px 16px;
        border-radius: 12px;
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 13px;
        font-weight: 700;
        color: #475569;
        background: transparent;
        border: 2px solid transparent;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all 0.15s ease;
    }
    .grade-filter-pill:hover {
        color: #0f172a;
        background: rgba(255, 255, 255, 0.8);
    }
    .grade-filter-pill.pill-active {
        color: #ffffff;
        box-shadow: 0 4px 0 rgba(0,0,0,0.2), 0 5px 12px rgba(0,0,0,0.15);
        transform: translateY(-1px);
    }
    .tab-royal.pill-active {
        background: linear-gradient(180deg, #2563eb 0%, #1d4ed8 100%);
        border-color: #60a5fa;
    }
    .tab-emerald.pill-active {
        background: linear-gradient(180deg, #10b981 0%, #059669 100%);
        border-color: #6ee7b7;
    }
    .tab-sky.pill-active {
        background: linear-gradient(180deg, #0284c7 0%, #0369a1 100%);
        border-color: #7dd3fc;
    }
    .tab-purple.pill-active {
        background: linear-gradient(180deg, #8b5cf6 0%, #6d28d9 100%);
        border-color: #c4b5fd;
    }

    /* Timer Capsule */
    .leaderboard-timer-capsule {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 14px;
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
        border: 2px solid #fde68a;
        border-radius: 999px;
        box-shadow: 0 3px 8px rgba(245, 158, 11, 0.15);
    }
    .timer-orb {
        font-size: 16px;
        animation: pulseClock 2s infinite ease-in-out;
    }
    @keyframes pulseClock {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.15); }
    }
    .timer-text-wrap {
        display: flex;
        flex-direction: column;
        line-height: 1.15;
    }
    .timer-label {
        font-size: 9.5px;
        font-weight: 850;
        color: #92400e;
        letter-spacing: 0.5px;
    }
    .timer-countdown {
        color: #b45309;
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 13px;
        letter-spacing: 0.3px;
    }

    /* My Rank Energy Bar (High-Contrast & Sleek) */
    .my-rank-energy-bar {
        background: linear-gradient(90deg, #0f172a 0%, #1e3a8a 55%, #0369a1 100%);
        border: 2.5px solid #38bdf8;
        border-radius: 18px;
        padding: 12px 18px;
        margin-bottom: 22px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        box-shadow: 0 6px 20px rgba(2, 132, 199, 0.28);
        color: #ffffff;
    }
    .rank-bar-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .my-rank-orb {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: linear-gradient(135deg, #38bdf8, #0284c7);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2.5px solid #ffffff;
        box-shadow: 0 0 12px rgba(56, 189, 248, 0.5);
        flex-shrink: 0;
    }
    .rank-orb-top3 {
        background: linear-gradient(135deg, #fbbf24, #d97706);
        box-shadow: 0 0 16px rgba(245, 158, 11, 0.8) !important;
    }
    .rank-hash { font-size: 14px; font-weight: 900; opacity: 0.8; }
    .rank-number {
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 20px;
        font-weight: 700;
        margin-left: 1px;
    }
    .rank-context-label {
        font-size: 10.5px;
        font-weight: 850;
        color: #93c5fd;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }
    .rank-user-name-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 1px;
        flex-wrap: wrap;
    }
    .user-display-name {
        font-size: 15px;
        color: #ffffff;
    }
    .user-class-chip {
        background: rgba(255, 255, 255, 0.18);
        color: #e0f2fe;
        font-size: 11px;
        font-weight: 750;
        padding: 2px 8px;
        border-radius: 999px;
    }
    .user-score-badge {
        color: #fef08a;
        font-size: 12.5px;
        font-weight: 700;
    }

    .rank-bar-right {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .rank-motivation-badge {
        font-size: 13px;
        color: #e0f2fe;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .btn-boost-rank {
        background: linear-gradient(180deg, #fbbf24 0%, #d97706 100%);
        border: 2px solid #fef08a;
        color: #78350f;
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 12.5px;
        font-weight: 700;
        padding: 7px 16px;
        border-radius: 12px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        box-shadow: 0 3px 0 #92400e, 0 4px 10px rgba(0,0,0,0.2);
        white-space: nowrap;
        transition: transform 0.15s;
    }
    .btn-boost-rank:hover { transform: translateY(-2px); }
    .btn-boost-rank:active { transform: translateY(2px); box-shadow: 0 1px 0 #92400e; }

    /* =========================================================================
       3D OLYMPIC CHAMPIONS STAGE SYSTEM
       ========================================================================= */
    .olympic-stage-container {
        display: grid;
        grid-template-columns: 1fr 1.15fr 1fr;
        gap: 16px;
        align-items: flex-end;
        margin-bottom: 0;
        padding-top: 30px;
        position: relative;
    }
    @media (max-width: 840px) {
        .olympic-stage-container {
            grid-template-columns: 1fr;
            gap: 22px;
        }
    }

    .podium-pillar {
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        position: relative;
    }
    .pillar-is-me .avatar-ring {
        box-shadow: 0 0 0 4px #0284c7, 0 0 20px rgba(2, 132, 199, 0.7) !important;
    }

    /* Golden Sunburst behind Champion */
    .golden-sunburst-halo {
        position: absolute;
        top: -20px;
        left: 50%;
        transform: translateX(-50%);
        width: 240px;
        height: 240px;
        background: radial-gradient(circle, rgba(251, 191, 36, 0.35) 0%, rgba(245, 158, 11, 0.12) 50%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
        z-index: 1;
    }

    .pillar-avatar-group {
        position: relative;
        z-index: 2;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        margin-bottom: 12px;
    }

    /* Floating Crown */
    .floating-crown-orb {
        font-size: 32px;
        line-height: 1;
        margin-bottom: -10px;
        filter: drop-shadow(0 4px 6px rgba(180, 83, 9, 0.5));
        animation: crownFloat 2s infinite ease-in-out;
    }
    @keyframes crownFloat {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-6px); }
    }

    /* Medal Ribbons */
    .pillar-medal-ribbon {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 14px;
        border-radius: 999px;
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 12px;
        font-weight: 700;
        margin-bottom: 10px;
        border: 2px solid;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    }
    .ribbon-gold {
        background: linear-gradient(180deg, #fef08a 0%, #fde047 100%);
        color: #854d0e;
        border-color: #eab308;
    }
    .ribbon-silver {
        background: linear-gradient(180deg, #f1f5f9 0%, #e2e8f0 100%);
        color: #334155;
        border-color: #94a3b8;
    }
    .ribbon-bronze {
        background: linear-gradient(180deg, #ffedd5 0%, #fed7aa 100%);
        color: #9a3412;
        border-color: #f97316;
    }

    /* 3D Metallic Avatar Rings */
    .avatar-ring {
        width: 66px;
        height: 66px;
        border-radius: 50%;
        display: grid;
        place-items: center;
        position: relative;
        margin-bottom: 8px;
        border: 4px solid #ffffff;
        box-shadow: 0 8px 18px rgba(0,0,0,0.18);
        transition: transform 0.2s ease;
    }
    .pillar-gold .avatar-ring {
        width: 78px;
        height: 78px;
        border-width: 4.5px;
    }
    .ring-gold {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        border-color: #fef08a !important;
        box-shadow: 0 0 16px rgba(245, 158, 11, 0.5), 0 8px 18px rgba(0,0,0,0.2) !important;
    }
    .ring-silver {
        background: linear-gradient(135deg, #94a3b8, #64748b);
        border-color: #f8fafc !important;
    }
    .ring-bronze {
        background: linear-gradient(135deg, #ea580c, #c2410c);
        border-color: #ffedd5 !important;
    }
    .avatar-inner {
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 28px;
        font-weight: 800;
        color: #ffffff;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.35);
    }
    .pillar-gold .avatar-inner { font-size: 34px; }

    .badge-me-tag {
        position: absolute;
        bottom: -6px;
        background: #0284c7;
        color: #ffffff;
        font-size: 9.5px;
        font-weight: 900;
        padding: 2px 8px;
        border-radius: 999px;
        border: 2px solid #ffffff;
        box-shadow: 0 2px 6px rgba(0,0,0,0.2);
        white-space: nowrap;
    }
    .tag-champion {
        background: #dc2626;
        color: #ffffff;
    }

    .podium-player-name {
        font-size: 15px;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.25;
        max-width: 190px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .name-gold {
        font-size: 16.5px;
        color: #0f172a;
    }
    .podium-player-class {
        font-size: 11.5px;
        color: #64748b;
        font-weight: 700;
        margin-top: 1px;
    }

    /* Score Pills */
    .podium-score-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 12px;
        border-radius: 12px;
        margin-top: 6px;
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 18px;
        font-weight: 700;
        border: 1.5px solid;
    }
    .pill-gold-score {
        background: linear-gradient(180deg, #fefce8, #fef08a);
        color: #854d0e;
        border-color: #fde047;
    }
    .pill-silver-score {
        background: #f8fafc;
        color: #334155;
        border-color: #cbd5e1;
    }
    .pill-bronze-score {
        background: #fff7ed;
        color: #9a3412;
        border-color: #fed7aa;
    }
    .podium-score-pill small {
        font-size: 11px;
        font-weight: 700;
        opacity: 0.85;
    }

    /* =========================================================================
       3D PEDESTAL BLOCKS (MẶT TRÊN & MẶT TRƯỚC NỔI KHỐI)
       ========================================================================= */
    .pedestal-block {
        display: flex;
        flex-direction: column;
        border-radius: 16px 16px 0 0;
        position: relative;
        box-shadow: 0 12px 24px rgba(0,0,0,0.16);
    }
    .block-gold { height: 165px; }
    .block-silver { height: 120px; }
    .block-bronze { height: 90px; }

    /* Top surface */
    .pedestal-top-surface {
        height: 18px;
        border-radius: 16px 16px 0 0;
        border: 2.5px solid;
        border-bottom: none;
    }
    .surface-gold {
        background: linear-gradient(90deg, #fef08a 0%, #fde047 100%);
        border-color: #fef9c3;
    }
    .surface-silver {
        background: linear-gradient(90deg, #f1f5f9 0%, #e2e8f0 100%);
        border-color: #ffffff;
    }
    .surface-bronze {
        background: linear-gradient(90deg, #fed7aa 0%, #fdba74 100%);
        border-color: #ffedd5;
    }

    /* Front face with huge 3D embossed numbers */
    .pedestal-front-face {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 10px 14px;
        border: 2.5px solid;
        border-top: none;
        color: #ffffff;
        position: relative;
    }
    .face-gold {
        background: linear-gradient(180deg, #f59e0b 0%, #d97706 65%, #b45309 100%);
        border-color: #fbbf24;
    }
    .face-silver {
        background: linear-gradient(180deg, #94a3b8 0%, #64748b 65%, #475569 100%);
        border-color: #cbd5e1;
    }
    .face-bronze {
        background: linear-gradient(180deg, #ea580c 0%, #c2410c 65%, #9a3412 100%);
        border-color: #fb923c;
    }

    .pedestal-3d-number {
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 48px;
        font-weight: 800;
        line-height: 1;
        text-shadow: 0 3px 0 rgba(0,0,0,0.35), 0 5px 12px rgba(0,0,0,0.25);
    }
    .num-gold {
        font-size: 58px;
        color: #fef08a;
        text-shadow: 0 4px 0 #92400e, 0 6px 14px rgba(0,0,0,0.3);
    }
    .num-silver {
        color: #f8fafc;
        text-shadow: 0 3px 0 #334155, 0 5px 10px rgba(0,0,0,0.25);
    }
    .num-bronze {
        color: #ffedd5;
        text-shadow: 0 3px 0 #7c2d12, 0 5px 10px rgba(0,0,0,0.25);
    }

    .pedestal-subtext {
        font-size: 11px;
        font-weight: 900;
        letter-spacing: 1px;
        opacity: 0.95;
        margin-top: 2px;
        text-shadow: 0 1px 2px rgba(0,0,0,0.4);
    }
    .subtext-gold { color: #fefce8; font-size: 12px; }

    .pedestal-meta-stat {
        background: rgba(0, 0, 0, 0.18);
        border-radius: 999px;
        padding: 2px 10px;
        font-size: 10.5px;
        font-weight: 750;
        margin-top: 4px;
        color: rgba(255, 255, 255, 0.9);
    }

    /* Placeholders */
    .placeholder-group {
        padding: 20px 10px;
    }
    .placeholder-orb { font-size: 32px; margin-bottom: 4px; }
    .placeholder-title { font-size: 13.5px; font-weight: 800; color: #64748b; }
    .placeholder-sub { font-size: 11px; color: #94a3b8; font-weight: 600; margin-top: 2px; }

    /* Unified 3D Stage Base Footer */
    .stage-footer-base {
        background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
        border: 2.5px solid #38bdf8;
        border-radius: 0 0 18px 18px;
        padding: 8px 16px;
        text-align: center;
        box-shadow: 0 8px 20px rgba(0,0,0,0.25), inset 0 2px 0 rgba(255,255,255,0.1);
        margin-bottom: 24px;
    }
    .stage-base-label {
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 12.5px;
        font-weight: 700;
        color: #38bdf8;
        letter-spacing: 1px;
        text-shadow: 0 0 8px rgba(56, 189, 248, 0.5);
    }

    /* =========================================================================
       TOP TIẾP THEO (HẠNG 4 – 10) GỌN GÀNG, SẮC NÉT
       ========================================================================= */
    .next-ranks-section {
        border-top: 2px dashed #e2e8f0;
        padding-top: 18px;
    }
    .next-ranks-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }
    .header-left {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .header-bolt { font-size: 16px; color: #f59e0b; }
    .header-title {
        font-size: 12.5px;
        font-weight: 850;
        color: #475569;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }
    .header-badge-hint {
        font-size: 11.5px;
        color: #64748b;
        font-weight: 700;
    }

    .next-ranks-grid {
        display: grid;
        gap: 8px;
    }
    .rank-row-item {
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 14px;
        padding: 10px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
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
        gap: 12px;
    }
    .rank-badge-num {
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 15px;
        font-weight: 700;
        color: #64748b;
        width: 28px;
        text-align: center;
    }
    .rank-row-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #e2e8f0;
        color: #334155;
        display: grid;
        place-items: center;
        font-size: 14px;
        font-weight: 800;
        flex-shrink: 0;
    }
    .row-is-me .rank-row-avatar {
        background: #0284c7;
        color: #ffffff;
    }

    .rank-row-title {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 14px;
        color: #0f172a;
    }
    .tag-me-pill {
        background: #0284c7;
        color: #ffffff;
        font-size: 9.5px;
        font-weight: 900;
        padding: 1px 6px;
        border-radius: 999px;
    }
    .class-chip {
        background: #f1f5f9;
        color: #475569;
        font-size: 11px;
        font-weight: 700;
        padding: 1px 7px;
        border-radius: 999px;
    }
    .rank-row-sub {
        font-size: 11.5px;
        color: #64748b;
        font-weight: 650;
        display: flex;
        align-items: center;
        gap: 6px;
        margin-top: 1px;
    }
    .rank-row-score {
        text-align: right;
    }
    .rank-row-score b {
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 17px;
        color: #0f172a;
        display: block;
        line-height: 1.1;
    }
    .rank-row-score small {
        font-size: 11px;
        color: #64748b;
        font-weight: 650;
    }

    /* =========================================================================
       BỘ SƯU TẬP HUY HIỆU (TROPHY CASE & 3D MEDALS)
       ========================================================================= */
    .badge-guide-capsule {
        background: linear-gradient(135deg, #eff6ff 0%, #f0fdf4 100%);
        border: 2px solid #bfdbfe;
        border-radius: 18px;
        padding: 14px 18px;
        margin-bottom: 20px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        box-shadow: 0 4px 12px rgba(2, 132, 199, 0.08);
    }
    .guide-icon-orb {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        background: linear-gradient(135deg, #38bdf8, #0284c7);
        display: grid;
        place-items: center;
        font-size: 20px;
        color: #ffffff;
        flex-shrink: 0;
        box-shadow: 0 3px 6px rgba(2, 132, 199, 0.25);
    }
    .guide-title {
        font-size: 13.5px;
        font-weight: 850;
        color: #1e3a8a;
        display: block;
    }
    .guide-desc {
        margin: 3px 0 0;
        font-size: 12.5px;
        color: #334155;
        line-height: 1.4;
    }

    .trophy-badges-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 14px;
    }
    .trophy-badge-card {
        border-radius: 20px;
        padding: 16px 12px;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: space-between;
        border: 2.5px solid;
        cursor: pointer;
        position: relative;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .trophy-badge-card:hover {
        transform: translateY(-4px);
    }

    .badge-card-unlocked {
        background: linear-gradient(180deg, #fffbeb 0%, #fef3c7 45%, #fde68a 100%);
        border-color: #fbbf24;
        box-shadow: 0 6px 16px rgba(245, 158, 11, 0.18), inset 0 -3px 0 rgba(217, 119, 6, 0.15);
    }
    .badge-card-locked {
        background: rgba(248, 250, 252, 0.85);
        border-color: #e2e8f0;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.04);
        opacity: 0.85;
    }

    .badge-type-pill {
        font-size: 9.5px;
        font-weight: 900;
        padding: 2px 8px;
        border-radius: 999px;
        margin-bottom: 8px;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }
    .type-pill-unlocked {
        background: rgba(180, 83, 9, 0.12);
        color: #92400e;
    }
    .type-pill-locked {
        background: #e2e8f0;
        color: #64748b;
    }

    .badge-orb-3d {
        width: 58px;
        height: 58px;
        border-radius: 50%;
        display: grid;
        place-items: center;
        font-size: 28px;
        margin-bottom: 8px;
        position: relative;
        border: 3px solid;
    }
    .orb-3d-unlocked {
        background: linear-gradient(135deg, #fef08a, #fde047);
        border-color: #facc15;
        box-shadow: 0 0 14px rgba(234, 179, 8, 0.4), 0 4px 10px rgba(0,0,0,0.12);
    }
    .orb-3d-locked {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }

    .badge-lock-tag, .badge-check-tag {
        position: absolute;
        bottom: -2px;
        right: -2px;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        display: grid;
        place-items: center;
        font-size: 11px;
        border: 2px solid #ffffff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.15);
    }
    .badge-lock-tag { background: #64748b; color: #fff; }
    .badge-check-tag { background: #10b981; color: #fff; font-weight: 900; }

    .badge-item-title {
        font-size: 13px;
        font-weight: 850;
        color: #0f172a;
        line-height: 1.2;
    }
    .badge-item-rule {
        font-size: 11px;
        color: #64748b;
        font-weight: 650;
        display: block;
        margin: 3px 0 6px;
        line-height: 1.3;
        min-height: 28px;
    }

    .badge-reward-pill {
        font-size: 11px;
        font-weight: 800;
        padding: 3px 10px;
        border-radius: 999px;
        margin-bottom: 8px;
    }
    .reward-pill-received {
        background: #f0fdf4;
        color: #15803d;
        border: 1px solid #bbf7d0;
    }
    .reward-pill-pending {
        background: #fff7ed;
        color: #c2410c;
        border: 1px solid #fed7aa;
    }

    .badge-progress-box {
        width: 100%;
        margin-bottom: 8px;
    }
    .badge-progress-track {
        height: 6px;
        background: #e2e8f0;
        border-radius: 999px;
        overflow: hidden;
    }
    .badge-progress-fill {
        height: 100%;
        border-radius: 999px;
        transition: width 0.3s ease;
    }
    .fill-complete { background: linear-gradient(90deg, #10b981, #059669); }
    .fill-pending { background: linear-gradient(90deg, #f59e0b, #ea580c); }
    .badge-progress-label {
        font-size: 10px;
        color: #64748b;
        font-weight: 750;
        display: block;
        margin-top: 2px;
    }

    .btn-badge-trigger {
        width: 100%;
        border: none;
        padding: 6px 10px;
        border-radius: 10px;
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 11.5px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        transition: background-color 0.15s ease;
    }
    .btn-trigger-unlocked {
        background: #10b981;
        color: #ffffff;
    }
    .btn-trigger-locked {
        background: #e2e8f0;
        color: #475569;
    }

    /* =========================================================================
       CTA BANNER (BÍ KÍP CÀY SAO)
       ========================================================================= */
    .adventure-cta-banner {
        background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
        border: 3.5px solid #7dd3fc;
        border-radius: 22px;
        padding: 18px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        box-shadow: 0 10px 24px rgba(2, 132, 199, 0.28);
        color: #ffffff;
    }
    .cta-banner-content {
        display: flex;
        align-items: center;
        gap: 14px;
        flex: 1;
        min-width: 260px;
    }
    .cta-icon-orb {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.2);
        border: 2px solid rgba(255, 255, 255, 0.35);
        display: grid;
        place-items: center;
        font-size: 24px;
        flex-shrink: 0;
    }
    .cta-title {
        margin: 0;
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 17px;
        font-weight: 700;
        color: #fef08a;
    }
    .cta-desc {
        margin: 3px 0 0;
        font-size: 12.5px;
        color: #e0f2fe;
        font-weight: 650;
        line-height: 1.4;
    }
    .btn-cta-action {
        background: #ffffff;
        color: #0369a1;
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 14px;
        font-weight: 700;
        padding: 10px 22px;
        border-radius: 14px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 4px 0 #0284c7, 0 6px 14px rgba(0,0,0,0.15);
        white-space: nowrap;
        transition: transform 0.15s ease;
    }
    .btn-cta-action:hover { transform: translateY(-2px); }

    /* =========================================================================
       MODAL 3D POPUP CHI TIẾT HUY HIỆU
       ========================================================================= */
    .badge-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.7);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .badge-modal-box {
        background: #ffffff;
        border: 4px solid #38bdf8;
        border-radius: 26px;
        width: min(460px, 100%);
        padding: 28px 24px;
        text-align: center;
        position: relative;
        box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        animation: modalScale 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }
    @keyframes modalScale {
        from { opacity: 0; transform: scale(0.9); }
        to { opacity: 1; transform: scale(1); }
    }
    .modal-close-btn {
        position: absolute;
        top: 14px;
        right: 14px;
        background: #f1f5f9;
        border: none;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        font-size: 15px;
        cursor: pointer;
        color: #64748b;
        font-weight: 800;
        display: grid;
        place-items: center;
    }
    .modal-close-btn:hover { background: #e2e8f0; color: #0f172a; }

    .modal-hero-orb {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        margin: 0 auto 12px;
        display: grid;
        place-items: center;
        font-size: 42px;
        background: linear-gradient(135deg, #fef08a, #fde047);
        border: 4px solid #facc15;
        box-shadow: 0 0 20px rgba(234, 179, 8, 0.5);
    }
    .modal-badge-type {
        font-size: 11px;
        font-weight: 900;
        color: #b45309;
        letter-spacing: 0.8px;
        text-transform: uppercase;
        background: #fef3c7;
        padding: 2px 10px;
        border-radius: 999px;
    }
    .modal-badge-title {
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 22px;
        font-weight: 700;
        color: #0f172a;
        margin: 6px 0 4px;
    }
    .modal-badge-meaning {
        font-size: 13px;
        color: #64748b;
        line-height: 1.4;
        margin: 0 0 16px;
    }

    .modal-stats-card {
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 16px;
        padding: 14px 16px;
        text-align: left;
        margin-bottom: 20px;
    }
    .modal-stat-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-bottom: 8px;
        margin-bottom: 8px;
        border-bottom: 1px dashed #e2e8f0;
        gap: 10px;
    }
    .stat-row-label {
        font-size: 12px;
        color: #64748b;
        font-weight: 700;
    }
    .stat-row-val {
        font-size: 12.5px;
        color: #0f172a;
        font-weight: 800;
        text-align: right;
    }
    .val-reward { color: #ea580c; }

    .modal-progress-track {
        height: 8px;
        background: #e2e8f0;
        border-radius: 999px;
        overflow: hidden;
        margin-top: 10px;
    }
    .modal-progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #10b981, #059669);
        border-radius: 999px;
        transition: width 0.3s ease;
    }

    .modal-action-footer {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
    }
    .btn-modal-action {
        flex: 1;
        background: linear-gradient(180deg, #0284c7 0%, #0369a1 100%);
        border: 2px solid #7dd3fc;
        color: #ffffff;
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 14.5px;
        font-weight: 700;
        padding: 11px 18px;
        border-radius: 14px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        box-shadow: 0 4px 0 #024e75;
    }
    .btn-modal-close {
        background: #f1f5f9;
        border: 1.5px solid #cbd5e1;
        color: #475569;
        font-size: 13.5px;
        font-weight: 750;
        padding: 11px 18px;
        border-radius: 14px;
        cursor: pointer;
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

    // =========================================================================
    // 🎖️ MODAL 3D CHI TIẾT HUY HIỆU DANH DỰ
    // =========================================================================
    const badgesData = @json($badges);

    function openBadgeModal(badgeId) {
        const badge = badgesData.find(b => b.id === badgeId);
        if (!badge) return;

        const modal = document.getElementById('badge-detail-modal');
        const iconEl = document.getElementById('modal-badge-icon');
        const typeEl = document.getElementById('modal-badge-type');
        const titleEl = document.getElementById('modal-badge-title');
        const meaningEl = document.getElementById('modal-badge-meaning');
        const ruleEl = document.getElementById('modal-badge-rule');
        const rewardEl = document.getElementById('modal-badge-reward');
        const progressEl = document.getElementById('modal-badge-progress');
        const fillEl = document.getElementById('modal-progress-fill');
        const actionBtn = document.getElementById('modal-action-btn');

        if (iconEl) iconEl.innerText = badge.icon;
        if (typeEl) typeEl.innerText = badge.badge_type;
        if (titleEl) titleEl.innerText = badge.badge_name;
        if (meaningEl) meaningEl.innerText = badge.desc;
        if (ruleEl) ruleEl.innerText = badge.rule;
        if (rewardEl) rewardEl.innerText = `+${badge.reward_stars} ⭐ Sao thưởng vào ví`;
        if (progressEl) progressEl.innerText = `${badge.progress_text} (${badge.percent}%)`;
        if (fillEl) fillEl.style.width = `${badge.percent}%`;

        if (actionBtn) {
            if (badge.unlocked) {
                actionBtn.innerHTML = '<span>🎉</span> Bé Đã Hoàn Thành!';
                actionBtn.style.background = 'linear-gradient(180deg, #10b981 0%, #059669 100%)';
                actionBtn.style.borderColor = '#6ee7b7';
                actionBtn.href = 'javascript:void(0)';
                actionBtn.onclick = closeBadgeModal;
            } else {
                actionBtn.innerHTML = '<span>🚀</span> Làm Bài Ngay (+ ' + badge.reward_stars + ' ⭐)';
                actionBtn.style.background = 'linear-gradient(180deg, #0284c7 0%, #0369a1 100%)';
                actionBtn.style.borderColor = '#7dd3fc';
                actionBtn.href = '{{ route('programs') }}';
                actionBtn.onclick = null;
            }
        }

        if (modal) modal.style.display = 'flex';
    }

    function closeBadgeModal() {
        const modal = document.getElementById('badge-detail-modal');
        if (modal) modal.style.display = 'none';
    }

    document.addEventListener('DOMContentLoaded', () => {
        initLeaderboardTimer();
    });
</script>
@endsection