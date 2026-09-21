{{-- Hiển thị thành tích và phần thưởng; các số liệu được tính trong LearningController::achievements(). --}}
@extends('layouts.app')
@section('title', 'Bảng vàng Thành tích & Điểm thưởng — IC3 Digital Adventure')

@section('content')
<div class="adventure-world-wrapper">
    <div class="achievements-page-wrap">
        @php
            $getKidAvatar = function($id) {
                $num = (($id ?? 1) % 8) + 1;
                return asset("images/leaderboard/avatars/kid-{$num}.png");
            };
        @endphp

        <!-- ===================================================================
             👑 BẢNG VÀNG ĐẤU TRƯỜNG KỲ ẢO 3D (IC3 DIGITAL ADVENTURE LEADERBOARD)
             =================================================================== -->
        <div id="leaderboard-ajax-wrapper" class="leaderboard-ajax-section-wrapper" style="position: relative; min-height: 500px; margin-bottom: 24px;">
            @include('learning.partials.leaderboard-content')
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
                    <small class="pod-hint">Dùng đổi vé chơi game tại Cửa Hàng bên dưới</small>
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
                    <span class="pod-label">BÀI ĐẠT CHUẨN IC3 (≥ {{ $minPassScore ?? 700 }}Đ)</span>
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


    <!-- Modal Xem Tất Cả Bảng Vàng (Toàn Bộ Hiệp Sĩ Trong Khối) -->
    <div class="modal fade" id="modalAllLeaderboard" tabindex="-1" aria-labelledby="modalAllLeaderboardLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content modal-leaderboard-theme">
                <div class="modal-header border-0">
                    <div class="d-flex align-items-center gap-2">
                        <span style="font-size: 24px;">🏆</span>
                        <h5 class="modal-title font-fredoka fw-bold text-white mb-0" id="modalAllLeaderboardLabel">
                            BẢNG VÀNG THI ĐUA — {{ (string)$selectedGrade === 'all' ? 'TOÀN TRƯỜNG' : 'KHỐI ' . $selectedGrade }}
                        </h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle mb-0 text-center" style="font-size: 13.5px;">
                            <thead style="background: rgba(15, 23, 42, 0.8); color: #7dd3fc; text-transform: uppercase; font-size: 11.5px;">
                                <tr>
                                    <th style="width: 70px;">Hạng</th>
                                    <th class="text-start" style="padding-left: 20px;">Hiệp sĩ</th>
                                    <th>Lớp</th>
                                    <th>Số bài làm</th>
                                    <th>Điểm thi đua</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($leaderboard as $item)
                                    <tr class="{{ $item['is_me'] ? 'table-warning fw-bold text-dark' : '' }}">
                                        <td>
                                            @if($item['rank'] == 1)
                                                <span class="badge bg-warning text-dark px-2 py-1 fs-6">🥇 #1</span>
                                            @elseif($item['rank'] == 2)
                                                <span class="badge bg-secondary text-white px-2 py-1 fs-6">🥈 #2</span>
                                            @elseif($item['rank'] == 3)
                                                <span class="badge bg-danger text-white px-2 py-1 fs-6">🥉 #3</span>
                                            @else
                                                <span class="text-muted fw-bold">#{{ $item['rank'] }}</span>
                                            @endif
                                        </td>
                                        <td class="text-start" style="padding-left: 20px;">
                                            <div class="d-flex align-items-center gap-2">
                                                <img src="{{ $getKidAvatar($item['id']) }}" alt="{{ $item['name'] }}" style="width: 32px; height: 32px; border-radius: 50%; border: 1.5px solid #fff;">
                                                <span>{{ $item['name'] }}</span>
                                                @if($item['is_me'])
                                                    <span class="badge bg-primary ms-1" style="font-size: 10px;">BẠN</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>{{ $item['classroom_name'] }}</td>
                                        <td>{{ $item['tests_count'] }} bài</td>
                                        <td class="text-warning fw-bold fs-6">
                                            🪙 {{ number_format($item['weekly_score']) }} đ
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-0 justify-content-between" style="background: rgba(15, 23, 42, 0.9);">
                    <small class="text-info">💡 Hoàn thành bài thi đạt chuẩn (≥ {{ $minPassScore ?? 700 }}đ) để cộng điểm vào Bảng Vàng</small>
                    <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

<style>
    /* =========================================================================
       IC3 DIGITAL ADVENTURE THEME — ACHIEVEMENTS & GAMIFICATION 3D REDESIGN
       ========================================================================= */
    .adventure-world-wrapper {
        min-height: calc(100vh - 86px);
        padding: 14px 14px 60px;
        background: url('{{ asset('images/adventure-world-bg.jpg') }}') center/cover no-repeat fixed;
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
        width: min(1200px, 100%);
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

    /* ===================================================================
       3D STAT PODS GRID — ĐA SẮC MÀU RỰC RỠ PHONG CÁCH GAME
       =================================================================== */
    .stat-pods-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(290px, 1fr));
        gap: 18px;
        margin-bottom: 26px;
    }
    .stat-pod {
        border-radius: 24px;
        padding: 18px 22px;
        display: flex;
        align-items: center;
        gap: 16px;
        transition: all 0.22s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        overflow: hidden;
    }
    .stat-pod:hover {
        transform: translateY(-5px) scale(1.015);
    }

    /* Pod 1: Ví Sao Thưởng (Hổ Phách Vàng Kim Rực Rỡ) */
    .stat-pod-stars {
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 40%, #fde68a 100%);
        border: 3.5px solid #fcd34d;
        box-shadow: 0 12px 28px rgba(245, 158, 11, 0.22), inset 0 -4px 0 rgba(217, 119, 6, 0.18);
    }
    .stat-pod-stars:hover {
        box-shadow: 0 16px 36px rgba(245, 158, 11, 0.32), inset 0 -4px 0 rgba(217, 119, 6, 0.18);
    }
    .stat-pod-stars .pod-label { color: #92400e; }
    .stat-pod-stars .pod-value { color: #78350f; }
    .stat-pod-stars .pod-unit { color: #b45309; }
    .stat-pod-stars .pod-hint { color: #92400e; font-weight: 700; }

    /* Pod 2: Thời Gian Chơi Game (Lam Biển Neon Sky Blue) */
    .stat-pod-game {
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 40%, #bae6fd 100%);
        border: 3.5px solid #7dd3fc;
        box-shadow: 0 12px 28px rgba(2, 132, 199, 0.22), inset 0 -4px 0 rgba(3, 105, 161, 0.18);
    }
    .stat-pod-game:hover {
        box-shadow: 0 16px 36px rgba(2, 132, 199, 0.32), inset 0 -4px 0 rgba(3, 105, 161, 0.18);
    }
    .stat-pod-game .pod-label { color: #0369a1; }
    .stat-pod-game .pod-value { color: #0284c7; }
    .stat-pod-game .pod-hint { color: #0284c7; font-weight: 700; }

    /* Pod 3: Bài Đạt Chuẩn IC3 (Lục Bảo Tươi Sáng Emerald) */
    .stat-pod-passed {
        background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 40%, #a7f3d0 100%);
        border: 3.5px solid #6ee7b7;
        box-shadow: 0 12px 28px rgba(16, 185, 129, 0.22), inset 0 -4px 0 rgba(5, 150, 105, 0.18);
    }
    .stat-pod-passed:hover {
        box-shadow: 0 16px 36px rgba(16, 185, 129, 0.32), inset 0 -4px 0 rgba(5, 150, 105, 0.18);
    }
    .stat-pod-passed .pod-label { color: #065f46; }
    .stat-pod-passed .pod-value { color: #047857; }
    .stat-pod-passed .pod-unit { color: #065f46; }
    .stat-pod-passed .mini-bar-track { background: rgba(5, 150, 105, 0.22); }
    .stat-pod-passed .mini-bar-label { color: #065f46; font-weight: 800; }

    .pod-icon-orb {
        width: 60px;
        height: 60px;
        border-radius: 20px;
        display: grid;
        place-items: center;
        font-size: 30px;
        flex-shrink: 0;
        border: 3px solid #ffffff;
        box-shadow: 0 8px 18px rgba(0,0,0,0.14), inset 0 -3px 0 rgba(0,0,0,0.15);
    }
    .orb-gold-sparkle { background: linear-gradient(135deg, #fbbf24, #d97706); }
    .orb-blue-game { background: linear-gradient(135deg, #38bdf8, #0284c7); }
    .orb-green-target { background: linear-gradient(135deg, #34d399, #059669); }

    .pod-content { display: flex; flex-direction: column; flex: 1; }
    .pod-label {
        font-size: 11px;
        font-weight: 850;
        letter-spacing: 0.6px;
        text-transform: uppercase;
    }
    .pod-value {
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 28px;
        font-weight: 700;
        line-height: 1.15;
        margin: 3px 0;
    }
    .pod-unit {
        font-size: 15px;
        font-weight: 700;
        margin-left: 2px;
    }
    .pod-hint {
        font-size: 11.5px;
        line-height: 1.35;
    }

    .pod-row-split {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }
    .btn-3d-play-quick {
        background: linear-gradient(180deg, #0284c7 0%, #0369a1 100%);
        border: 2.5px solid #ffffff;
        color: #ffffff;
        padding: 8px 16px;
        border-radius: 14px;
        font-size: 12px;
        font-weight: 900;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 4px 0 #024e75, 0 6px 14px rgba(2, 132, 199, 0.3);
        transition: transform 0.15s, box-shadow 0.15s;
        white-space: nowrap;
    }
    .btn-3d-play-quick:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 0 #024e75, 0 10px 18px rgba(2, 132, 199, 0.4);
    }
    .btn-3d-play-quick:active {
        transform: translateY(2px);
        box-shadow: 0 1px 0 #024e75;
    }

    .pod-mini-progress {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 4px;
    }
    .mini-bar-track {
        flex: 1;
        height: 7px;
        border-radius: 999px;
        overflow: hidden;
    }
    .mini-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, #10b981, #047857);
        border-radius: 999px;
        transition: width 0.3s ease;
    }

    /* Container Card chung */
    .game-container-card {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        border: 4px solid #ffffff;
        border-radius: 28px;
        padding: 26px 28px;
        box-shadow: 0 16px 40px rgba(10, 30, 60, 0.15), 0 3px 10px rgba(0, 0, 0, 0.05);
        margin-bottom: 26px;
    }

    /* Thẻ Bảng Vàng Thi Đua mang phong cách Sân Vận Động Arcade Đấu Trường */
    .leaderboard-card-section {
        background: linear-gradient(180deg, #ffffff 0%, #f0f9ff 35%, #e0f2fe 100%);
        border: 4px solid #ffffff;
        border-top: 6px solid #0284c7;
        box-shadow: 0 20px 50px rgba(2, 132, 199, 0.18), 0 4px 12px rgba(0, 0, 0, 0.06);
    }

    .rule-indicator-pill {
        display: inline-flex;
        align-items: center;
    }
    .rule-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 16px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 850;
        letter-spacing: 0.3px;
    }
    .rule-passed {
        background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
        border: 2px solid #6ee7b7;
        color: #065f46;
        box-shadow: 0 3px 10px rgba(16, 185, 129, 0.2);
    }
    .rule-all {
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        border: 2px solid #bfdbfe;
        color: #1e40af;
        box-shadow: 0 3px 10px rgba(59, 130, 246, 0.2);
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
       FANTASY GAME LEADERBOARD HERO (MATCH REFERENCE B - FIXED v3)
       ========================================================================= */
    .leaderboard-ajax-section-wrapper {
        position: relative;
        width: 100%;
    }

    .leaderboard-fantasy-hero {
        position: relative;
        width: 100%;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 20px 50px rgba(2, 10, 32, 0.55);
        display: flex;
        flex-direction: column;
    }

    /* 1. HERO STAGE VIEWPORT */
    .hero-stage-viewport {
        position: relative;
        width: 100%;
        min-height: 500px;
        background: url('{{ asset('images/leaderboard/fantasy-arena-bg.jpg') }}') center 15% / cover no-repeat;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 12px 16px 0;
        overflow: visible;
    }
    .hero-stage-viewport::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(ellipse at 50% 20%, rgba(255,255,255,0.05) 0%, rgba(4,12,35,0.22) 100%);
        pointer-events: none;
        z-index: 1;
    }

    /* 1A. SKY FLOATING HUD */
    .sky-floating-hud {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        position: relative;
        z-index: 25;
        flex-wrap: wrap;
    }
    .sky-hud-left {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .sky-btn-menu {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: rgba(6, 18, 48, 0.82);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 2px solid rgba(56, 189, 248, 0.55);
        color: #ffffff;
        font-size: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 4px 14px rgba(0,0,0,0.4);
        transition: all 0.15s;
        flex-shrink: 0;
    }
    .sky-btn-menu:hover {
        background: rgba(29, 114, 254, 0.85);
        border-color: #60a5fa;
        transform: translateY(-1px);
    }
    .sky-grade-pill-group {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: rgba(5, 15, 40, 0.82);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 2px solid rgba(56, 189, 248, 0.5);
        border-radius: 999px;
        padding: 4px 6px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.35);
    }
    .arena-grade-pill {
        padding: 6px 15px;
        border-radius: 999px;
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 13.5px;
        font-weight: 700;
        color: #94a3b8;
        background: transparent;
        border: 1.5px solid transparent;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all 0.18s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        white-space: nowrap;
    }
    .arena-grade-pill:hover {
        color: #ffffff;
        background: rgba(255, 255, 255, 0.14);
    }
    .arena-grade-pill.pill-active {
        color: #ffffff;
        background: linear-gradient(180deg, #1d72fe 0%, #0c50b8 100%);
        border-color: #60a5fa;
        box-shadow: 0 0 16px rgba(29, 114, 254, 0.85), inset 0 1px 0 rgba(255,255,255,0.4);
        transform: translateY(-1px);
    }
    .arena-grade-pill .pill-icon { font-size: 14px; }

    /* Timer capsule (right) */
    .sky-hud-right { display: flex; align-items: center; }
    .sky-timer-capsule {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        background: rgba(5, 15, 40, 0.88);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 2px solid rgba(56, 189, 248, 0.6);
        border-radius: 999px;
        padding: 6px 18px 6px 8px;
        box-shadow: 0 5px 18px rgba(0,0,0,0.4);
        color: #ffffff;
    }
    .timer-clock-badge {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: rgba(56, 189, 248, 0.25);
        border: 2px solid #38bdf8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        animation: pulseClock 2s infinite ease-in-out;
    }
    @keyframes pulseClock {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.08); }
    }
    .timer-text-group { display: flex; flex-direction: column; line-height: 1.15; }
    .timer-header-label {
        font-size: 10px;
        font-weight: 850;
        color: #7dd3fc;
        letter-spacing: 0.8px;
        text-transform: uppercase;
    }
    .timer-countdown-val {
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 16px;
        font-weight: 800;
        color: #fef08a;
        text-shadow: 0 0 10px rgba(254, 240, 138, 0.8);
        letter-spacing: 0.3px;
    }

    /* 1B. SCENERY PROPS */
    .scenery-prop {
        position: absolute;
        z-index: 10;
        pointer-events: none;
    }
    .prop-left { left: 16px; top: 120px; }
    .stone-pillar-tablet {
        background: rgba(8, 18, 45, 0.72);
        border: 2px solid #38bdf8;
        border-radius: 10px;
        padding: 7px 11px;
        backdrop-filter: blur(6px);
        box-shadow: 0 5px 14px rgba(0,0,0,0.45);
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1px;
    }
    .tablet-brand {
        font-family: 'Fredoka', cursive;
        font-size: 15px;
        font-weight: 900;
        color: #38bdf8;
        letter-spacing: 1px;
    }
    .tablet-point {
        font-family: 'Fredoka', cursive;
        font-size: 9.5px;
        font-weight: 800;
        color: #ffffff;
        letter-spacing: 0.4px;
        line-height: 1.2;
    }

    .prop-right {
        right: 16px;
        top: 125px;
        animation: signGentleBob 3.5s infinite ease-in-out;
    }
    @keyframes signGentleBob {
        0%, 100% { transform: translateY(0) rotate(1.5deg); }
        50% { transform: translateY(-5px) rotate(2.5deg); }
    }
    .rustic-wood-signboard {
        background: linear-gradient(180deg, #b45309 0%, #78350f 100%);
        border: 2.5px solid #fef3c7;
        border-radius: 12px;
        padding: 8px 13px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.42), inset 0 2px 0 rgba(255,255,255,0.25);
        color: #fefce8;
        text-align: center;
        display: flex;
        flex-direction: column;
        line-height: 1.2;
    }
    .rustic-wood-signboard .sign-top, .rustic-wood-signboard .sign-bot {
        font-family: 'Fredoka', cursive;
        font-size: 11px;
        font-weight: 600;
        opacity: 0.9;
    }
    .rustic-wood-signboard .sign-mid {
        font-family: 'Fredoka', cursive;
        font-size: 13px;
        font-weight: 850;
        color: #fde047;
        text-shadow: 0 1px 3px rgba(0,0,0,0.6);
        margin: 1px 0;
    }

    /* 1C. TITLE CREST — nâng lên cao để tận dụng khoảng trống bầu trời, không ép xuống nhân vật */
    .hero-title-crest {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        position: relative;
        z-index: 15;
        margin: -50px auto 0;
        width: min(600px, 100%);
    }
    .crest-wings-image {
        width: 250px;
        max-width: 90%;
        height: auto;
        filter: drop-shadow(0 8px 18px rgba(245, 158, 11, 0.6));
        margin-bottom: -40px;
        pointer-events: none;
        position: relative;
        z-index: 1;
    }
    .crest-headings-overlay {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        z-index: 2;
        width: 100%;
    }
    .crest-title-ic3 {
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: clamp(20px, 2.6vw, 30px);
        font-weight: 900;
        color: #ffffff;
        text-shadow:
            0 2px 0 #92400e,
            0 4px 12px rgba(0, 0, 0, 0.65),
            0 0 24px rgba(251, 191, 36, 0.9);
        letter-spacing: 1px;
        margin: 0;
        line-height: 1.05;
    }
    .crest-gold-ribbon {
        background: linear-gradient(180deg, #fef08a 0%, #fbbf24 40%, #f59e0b 100%);
        border: 2px solid #ffffff;
        border-radius: 999px;
        padding: 3px 26px;
        box-shadow: 0 3px 12px rgba(180, 83, 9, 0.5), inset 0 1px 0 rgba(255,255,255,0.7);
        margin-top: 2px;
    }
    .crest-gold-ribbon span {
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 13px;
        font-weight: 900;
        color: #78350f;
        text-shadow: 0 1px 0 rgba(255,255,255,0.6);
        letter-spacing: 0.8px;
        text-transform: uppercase;
    }
    .crest-motto-capsule {
        background: rgba(8, 24, 60, 0.82);
        border: 1.5px solid rgba(125, 211, 252, 0.5);
        border-radius: 999px;
        padding: 2px 14px;
        font-size: 10px;
        font-weight: 750;
        color: #e0f2fe;
        box-shadow: 0 2px 6px rgba(0,0,0,0.3);
        margin-top: 2px;
    }

    /* 1D. PODIUMS TRIO */
    .podiums-arena-trio {
        display: grid;
        grid-template-columns: 1fr 1.2fr 1fr;
        gap: 10px;
        align-items: flex-end;
        width: 100%;
        max-width: 900px;
        margin: 12px auto 0;
        position: relative;
        z-index: 12;
    }
    .podium-pillar {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-end;
        position: relative;
    }

    /* Characters */
    .pillar-hero-figure {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        z-index: 3;
        /* Floating bob animation cho tất cả nhân vật */
        animation: spriteFloat 3s ease-in-out infinite;
    }
    /* Mỗi cột có delay khác nhau để nhìn sống động */
    .pillar-silver .pillar-hero-figure { animation-delay: 0.4s; }
    .pillar-gold   .pillar-hero-figure { animation-delay: 0s; }
    .pillar-bronze .pillar-hero-figure { animation-delay: 0.8s; }

    @keyframes spriteFloat {
        0%, 100% { transform: translateY(0px); }
        45%      { transform: translateY(-6px); }
        55%      { transform: translateY(-6px); }
    }
    /* Khi hover: dừng float và scale lên */
    .podium-pillar:hover .pillar-hero-figure {
        animation-play-state: paused;
    }

    .hero-avatar-sprite {
        width: auto;
        display: block;
        object-fit: contain;
        image-rendering: -webkit-optimize-contrast;
        image-rendering: crisp-edges;
    }
    /* Kích thước nhân vật cân đối chuẩn tỉ lệ vàng, sắc nét trong vắt */
    .sprite-gold {
        height: 192px;
        filter: drop-shadow(0 14px 22px rgba(0,0,0,0.5));
    }
    .sprite-silver {
        height: 175px;
        filter: drop-shadow(0 12px 18px rgba(0,0,0,0.45));
    }
    .sprite-bronze {
        height: 172px;
        filter: drop-shadow(0 12px 18px rgba(0,0,0,0.45));
    }
    .podium-pillar:hover .hero-avatar-sprite {
        transform: scale(1.05);
        transition: transform 0.2s ease;
    }

    /* Champion orbital crown & halo — vương miện hiển thị trọn vẹn, không bị logo che */
    .champion-hero-figure { position: relative; }
    .champion-crown-orbit {
        position: absolute;
        top: -8px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 20;           /* vương miện sắc nét, hiển thị trọn vẹn 100% */
        pointer-events: none;
        animation: crownFloat 2.5s infinite ease-in-out;
        animation-delay: 0s;
    }
    @keyframes crownFloat {
        0%, 100% { transform: translateX(-50%) translateY(0); }
        50%       { transform: translateX(-50%) translateY(-4px); }
    }
    .orbit-crown-img {
        width: 48px;
        height: auto;
        filter: drop-shadow(0 4px 12px rgba(180, 83, 9, 0.95)) drop-shadow(0 0 16px rgba(251,191,36,0.7));
    }
    .champion-crown-orbit .sparkle {
        position: absolute;
        font-size: 13px;
        animation: sparkleSpin 2s infinite ease-in-out;
    }
    @keyframes sparkleSpin {
        0%, 100% { opacity: 0.5; transform: scale(0.8) rotate(0deg); }
        50%       { opacity: 1;   transform: scale(1.3) rotate(20deg); }
    }
    .sp-1 { top: -4px; left: -9px; }
    .sp-2 { top: 3px; right: -9px; animation-delay: 1s; }

    .champion-halo-ray {
        display: none; /* Ẩn lớp phủ gradient vàng gây mờ đục nhân vật Quán quân */
    }

    /* Number badge above pedestal */
    .pedestal-shield-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 2px;
        font-family: 'Fredoka', cursive, sans-serif;
        font-weight: 900;
        position: relative;
        z-index: 6;
        margin-bottom: -12px;
    }
    .badge-gold {
        font-size: 24px;
        color: #fefce8;
        text-shadow: 0 2px 0 #92400e, 0 3px 10px rgba(0,0,0,0.6);
    }
    .badge-gold .laurel-leaf { font-size: 17px; }
    .badge-gold .num-champ { font-size: 30px; line-height: 1; }
    .badge-silver {
        font-size: 20px;
        color: #ffffff;
        text-shadow: 0 2px 0 #1e293b, 0 3px 7px rgba(0,0,0,0.45);
    }
    .badge-bronze {
        font-size: 20px;
        color: #ffedd5;
        text-shadow: 0 2px 0 #431407, 0 3px 7px rgba(0,0,0,0.45);
    }
    .pedestal-wing { font-size: 14px; }
    .pedestal-num { padding: 0 1px; line-height: 1; }

    /* Pedestal altars (colored steps + info card) */
    .pedestal-altar {
        width: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        z-index: 4;
        padding-top: 16px;
        box-shadow: 0 12px 28px rgba(0,0,0,0.4);
    }
    .altar-gold {
        border-radius: 18px 18px 0 0;
        background: linear-gradient(180deg, #ffe87a 0%, #f59e0b 22%, #d97706 68%, #b45309 100%);
        border: 3px solid #fff9c3;
        border-bottom: none;
    }
    .altar-silver {
        border-radius: 14px 14px 0 0;
        background: linear-gradient(180deg, #ffffff 0%, #d1d5db 22%, #6b7280 68%, #4b5563 100%);
        border: 3px solid #f1f5f9;
        border-bottom: none;
    }
    .altar-bronze {
        border-radius: 14px 14px 0 0;
        background: linear-gradient(180deg, #ffedd5 0%, #fb923c 22%, #ea580c 68%, #9a3412 100%);
        border: 3px solid #ffedd5;
        border-bottom: none;
    }

    /* Tier badge labels */
    .altar-badge {
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 11.5px;
        font-weight: 900;
        letter-spacing: 0.5px;
        padding: 2px 14px;
        border-radius: 999px;
        margin-bottom: 6px;
        display: inline-block;
        box-shadow: 0 2px 6px rgba(0,0,0,0.25);
    }
    .badge-gold-text {
        background: linear-gradient(180deg, #fef08a, #facc15);
        color: #78350f;
        border: 1px solid #eab308;
    }
    .badge-silver-text {
        background: linear-gradient(180deg, #e0e7ff, #c7d2fe);
        color: #1e3a8a;
        border: 1px solid #93c5fd;
    }
    .badge-bronze-text {
        background: linear-gradient(180deg, #ffedd5, #fed7aa);
        color: #7c2d12;
        border: 1px solid #fb923c;
    }

    /* Altar info card */
    .altar-card {
        background: rgba(255, 255, 255, 0.97);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        border: 2px solid #ffffff;
        border-radius: 16px;
        padding: 8px 12px 10px;
        width: 91%;
        margin: 0 auto 10px;
        box-shadow: 0 6px 20px rgba(0,0,0,0.2);
        text-align: center;
    }
    .card-gold { border-color: #fde047; box-shadow: 0 8px 22px rgba(245,158,11,0.35); }
    .altar-profile {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        text-align: left;
        margin-bottom: 5px;
    }
    .avatar-ring {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        overflow: hidden;
        border: 2px solid;
        flex-shrink: 0;
        position: relative;
    }
    .ring-gold { border-color: #f59e0b; width: 40px; height: 40px; }
    .ring-silver { border-color: #60a5fa; }
    .ring-bronze { border-color: #ea580c; }
    .avatar-img { width: 100%; height: 100%; object-fit: cover; }
    .tag-me-dot {
        position: absolute;
        bottom: -2px;
        right: -2px;
        background: #0284c7;
        color: #ffffff;
        font-size: 7.5px;
        font-weight: 900;
        padding: 1px 3px;
        border-radius: 999px;
        border: 1px solid #ffffff;
    }
    .tag-champ { background: #dc2626; }
    .profile-info { display: flex; flex-direction: column; min-width: 0; }
    .profile-name {
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 13px;
        font-weight: 900;
        color: #0f172a;
        line-height: 1.2;
        max-width: 105px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .name-gold { font-size: 14px; color: #78350f; }
    .profile-class {
        font-size: 10px;
        font-weight: 700;
        color: #64748b;
        margin-top: 1px;
    }
    .altar-score-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        padding: 3px 12px;
        border-radius: 999px;
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 13px;
        font-weight: 700;
        border: 1.5px solid;
    }
    .pill-gold { background: linear-gradient(180deg, #fffbeb, #fef08a); color: #78350f; border-color: #facc15; box-shadow: 0 2px 7px rgba(245,158,11,0.3); }
    .pill-silver { background: linear-gradient(180deg, #ffffff, #e0e7ff); color: #1e3a8a; border-color: #93c5fd; }
    .pill-bronze { background: linear-gradient(180deg, #ffffff, #ffedd5); color: #7c2d12; border-color: #fdba74; }
    .altar-score-pill .coin-icon { font-size: 13px; }
    .placeholder-text { font-size: 12px; color: #94a3b8; font-weight: 600; padding: 8px 0; }

    /* Red carpet runner for champion */
    .royal-carpet-runner {
        width: 100px;
        height: 20px;
        background: linear-gradient(180deg, #dc2626 0%, #991b1b 100%);
        border-left: 2px solid #fbbf24;
        border-right: 2px solid #fbbf24;
        box-shadow: 0 4px 10px rgba(0,0,0,0.5);
        margin: 0 auto;
    }

    /* 1E. HONOR PLAQUE */
    .stage-honor-dock {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin: 5px 0 3px;
        position: relative;
        z-index: 10;
    }
    .honor-torch { font-size: 17px; filter: drop-shadow(0 0 8px rgba(251, 146, 60, 0.9)); }
    .honor-stone-slab {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        background: linear-gradient(180deg, #1e2d45 0%, #0e1a28 100%);
        border: 2px solid #d4a017;
        border-radius: 10px;
        padding: 4px 20px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.5), inset 0 1px 0 rgba(255,255,255,0.12);
    }
    .honor-stone-slab .star-accent { color: #f59e0b; font-size: 11px; }
    .honor-slab-text {
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 11.5px;
        font-weight: 800;
        color: #fef08a;
        letter-spacing: 1.2px;
        text-shadow: 0 1px 3px rgba(0,0,0,0.7);
        text-transform: uppercase;
    }

    /* ===== SPRITE GLOW WRAPPER (hiệu ứng ánh sáng xung quanh nhân vật) ===== */
    .sprite-glow-wrap {
        position: relative;
        display: inline-flex;
        align-items: flex-end;
        justify-content: center;
    }
    /* Glow vàng kim — Quán Quân: Tinh tế, sắc nét, trong trẻo, không bị mờ đục hay phủ sương */
    .sprite-glow-gold {
        filter: drop-shadow(0 0 10px rgba(254, 240, 138, 0.75))
                drop-shadow(0 4px 14px rgba(245, 158, 11, 0.4));
        animation: championGlow 2.5s ease-in-out infinite alternate;
    }
    @keyframes championGlow {
        0%   { filter: drop-shadow(0 0 8px rgba(254, 240, 138, 0.65)) drop-shadow(0 4px 10px rgba(245, 158, 11, 0.35)); }
        100% { filter: drop-shadow(0 0 14px rgba(253, 224, 71, 0.9)) drop-shadow(0 6px 16px rgba(245, 158, 11, 0.5)); }
    }
    /* Glow bạc — Á Quân */
    .sprite-glow-silver {
        filter: drop-shadow(0 0 16px rgba(148, 163, 184, 0.9))
                drop-shadow(0 0 32px rgba(203, 213, 225, 0.6));
        animation: silverGlow 2.5s ease-in-out infinite alternate;
    }
    @keyframes silverGlow {
        0%   { filter: drop-shadow(0 0 12px rgba(148,163,184,0.8)) drop-shadow(0 0 24px rgba(203,213,225,0.5)); }
        100% { filter: drop-shadow(0 0 22px rgba(226,232,240,1.0)) drop-shadow(0 0 44px rgba(148,163,184,0.75)); }
    }
    /* Glow đồng — Hạng Ba */
    .sprite-glow-bronze {
        filter: drop-shadow(0 0 16px rgba(251, 146, 60, 0.9))
                drop-shadow(0 0 30px rgba(234, 88, 12, 0.6));
        animation: bronzeGlow 2.8s ease-in-out infinite alternate;
    }
    @keyframes bronzeGlow {
        0%   { filter: drop-shadow(0 0 10px rgba(251,146,60,0.8)) drop-shadow(0 0 22px rgba(234,88,12,0.5)); }
        100% { filter: drop-shadow(0 0 22px rgba(253,186,116,1.0)) drop-shadow(0 0 44px rgba(251,146,60,0.75)); }
    }

    /* === MY-RANK-CHIP: thay tag-me-dot không che avatar === */
    .my-rank-chip {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        font-size: 10px;
        font-weight: 800;
        color: #ffffff;
        background: linear-gradient(90deg, #dc2626, #b91c1c);
        border-radius: 999px;
        padding: 2px 7px;
        border: 1px solid rgba(255,255,255,0.5);
        margin-top: 2px;
        box-shadow: 0 0 8px rgba(220,38,38,0.6);
        line-height: 1.4;
        letter-spacing: 0.2px;
    }
    .my-rank-chip.chip-champ {
        background: linear-gradient(90deg, #d97706, #b45309);
        box-shadow: 0 0 10px rgba(245,158,11,0.8);
        animation: chipPulse 2s ease-in-out infinite alternate;
    }
    @keyframes chipPulse {
        0%   { box-shadow: 0 0 8px rgba(245,158,11,0.7); }
        100% { box-shadow: 0 0 18px rgba(253,224,71,1.0); }
    }

    /* 2. RANK 4-10 STRIP — 7 CỘT, KHÔNG SCROLL */
    .hero-chaser-dock {
        background: linear-gradient(180deg, #081c40 0%, #040f22 100%);
        border-top: 2.5px solid #00d0f5;
        padding: 10px 16px 12px;
        width: 100%;
        position: relative;
        z-index: 20;
        box-shadow: inset 0 2px 14px rgba(0, 210, 245, 0.14);
    }
    .chaser-dock-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    .chaser-dock-title {
        display: flex;
        align-items: center;
        gap: 6px;
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 13.5px;
        font-weight: 800;
        color: #00e5ff;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        text-shadow: 0 0 12px rgba(0, 229, 255, 0.6);
    }
    .chaser-bolt-glow { color: #facc15; font-size: 16px; filter: drop-shadow(0 0 7px #facc15); }
    .chaser-dock-link {
        font-size: 12px;
        font-weight: 700;
        color: #38bdf8;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 5px 12px;
        border-radius: 999px;
        border: 1.5px solid rgba(56, 189, 248, 0.4);
        background: rgba(56, 189, 248, 0.1);
        transition: all 0.15s;
        white-space: nowrap;
    }
    .chaser-dock-link:hover { color: #fff; background: rgba(56,189,248,0.25); border-color: #38bdf8; }

    /* Row 7 cột: không scroll, chia đều */
    .chaser-dock-cards-row {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 8px;
        align-items: stretch;
    }

    /* Thẻ học sinh (class mới: chaser-item-card) */
    .chaser-item-card {
        background: rgba(255, 255, 255, 0.07);
        border: 1.5px solid rgba(255, 255, 255, 0.14);
        border-radius: 14px;
        padding: 10px 8px 10px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
        gap: 6px;
        text-align: center;
        cursor: default;
        transition: transform 0.15s, background 0.15s, border-color 0.15s, box-shadow 0.15s;
        position: relative;
        border-top: 3px solid var(--rank-color, #38bdf8);
    }
    .chaser-item-card:hover {
        transform: translateY(-3px);
        background: rgba(255, 255, 255, 0.13);
        border-color: var(--rank-color, #38bdf8);
        box-shadow: 0 6px 18px rgba(0,0,0,0.3), 0 0 12px rgba(56,189,248,0.2);
    }
    .chaser-item-card.card-is-me {
        background: rgba(245, 158, 11, 0.22) !important;
        border-color: #fbbf24 !important;
        box-shadow: 0 0 16px rgba(245,158,11,0.5) !important;
    }
    .chaser-item-card.card-is-me .chaser-name { color: #fef08a !important; }

    /* Badge số thứ tự — canh giữa, màu theo --rank-color */
    .chaser-rank-badge {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: var(--rank-color, #38bdf8);
        color: #ffffff;
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 13px;
        font-weight: 900;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
        box-shadow: 0 2px 8px rgba(0,0,0,0.4), 0 0 10px color-mix(in srgb, var(--rank-color, #38bdf8) 60%, transparent);
        flex-shrink: 0;
    }
    .chaser-avatar-wrap { position: relative; }
    .chaser-avatar-img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        border: 2.5px solid rgba(255, 255, 255, 0.75);
        object-fit: cover;
        box-shadow: 0 3px 10px rgba(0,0,0,0.45);
        display: block;
    }
    .chaser-info {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1px;
        min-width: 0;
        width: 100%;
    }
    .chaser-name {
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 12.5px;
        font-weight: 800;
        color: #ffffff;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
        line-height: 1.2;
    }
    .chaser-class {
        font-size: 10px;
        font-weight: 600;
        color: #94a3b8;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
    }
    .chaser-score {
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 11.5px;
        font-weight: 700;
        color: #fef08a;
        white-space: nowrap;
    }
    .chaser-empty-row { color: #94a3b8; font-size: 13px; padding: 12px 0; text-align: center; grid-column: 1/-1; }

    /* 3. PLAYER FOOTER HUD */
    .hero-player-footer-hud {
        background: linear-gradient(90deg, #07173a 0%, #0c2860 50%, #07173a 100%);
        border-top: 1.5px solid rgba(56, 189, 248, 0.35);
        padding: 7px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        position: relative;
        z-index: 22;
        color: #ffffff;
    }
    .footer-hud-left { display: flex; align-items: center; gap: 9px; }
    .footer-rank-badge {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: linear-gradient(135deg, #38bdf8, #0284c7);
        border: 2px solid #fef08a;
        color: #ffffff;
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 13px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 0 10px rgba(56,189,248,0.6);
        flex-shrink: 0;
    }
    .rank-top3 {
        background: linear-gradient(135deg, #fbbf24, #d97706);
        box-shadow: 0 0 14px rgba(245,158,11,0.9) !important;
    }
    .context-label {
        font-size: 11px;
        font-weight: 850;
        color: #e0f2fe;
        letter-spacing: 0.3px;
    }
    .footer-hud-center { display: flex; align-items: center; }
    .hud-status-text { font-size: 12.5px; font-weight: 700; color: #e0f2fe; }
    .footer-hud-right { display: flex; align-items: center; }
    .btn-luyen-ngay-action {
        background: linear-gradient(180deg, #fbbf24 0%, #f59e0b 50%, #d97706 100%);
        border: 1.5px solid #fef08a;
        border-radius: 999px;
        padding: 5px 18px;
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 12.5px;
        font-weight: 700;
        color: #78350f;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        box-shadow: 0 3px 0 #92400e, 0 4px 10px rgba(0,0,0,0.2);
        white-space: nowrap;
        transition: transform 0.15s;
    }
    .btn-luyen-ngay-action:hover { transform: translateY(-2px); color: #78350f; }

    /* Modal leaderboard theme */
    .modal-leaderboard-theme {
        background: linear-gradient(180deg, #0f1d38 0%, #071226 100%);
        border: 2px solid #38bdf8;
        border-radius: 20px;
        color: #ffffff;
    }
    .modal-leaderboard-theme .modal-header {
        background: rgba(15, 23, 42, 0.8);
        border-bottom: 1.5px solid rgba(56, 189, 248, 0.3);
    }

    /* 4. RESPONSIVE */
    @media (max-width: 1100px) {
        .chaser-dock-cards-row { gap: 6px; }
        .chaser-item-card { padding: 8px 5px 8px; }
        .chaser-name { font-size: 11px; }
        .chaser-avatar-img { width: 34px; height: 34px; }
    }
    @media (max-width: 992px) {
        .hero-stage-viewport { min-height: 460px; }
        .hero-title-crest { margin: -30px auto 0; }
        .podiums-arena-trio { grid-template-columns: 1fr 1.1fr 1fr; gap: 8px; }
        .sprite-gold   { height: 165px; }
        .sprite-silver { height: 140px; }
        .sprite-bronze { height: 138px; }
        /* Chaser vẫn 7 cột nhưng nhỏ hơn */
        .chaser-dock-cards-row { gap: 5px; }
        .chaser-item-card { padding: 7px 4px 8px; }
        .chaser-name { font-size: 10.5px; }
        .chaser-score { font-size: 10.5px; }
        .chaser-avatar-img { width: 30px; height: 30px; }
        .chaser-rank-badge { width: 22px; height: 22px; font-size: 11px; }
    }
    @media (max-width: 640px) {
        .hero-title-crest { margin: 4px auto 0; }
        .sky-floating-hud { flex-wrap: wrap; gap: 8px; }
        .sky-grade-pill-group { flex-wrap: wrap; }
        .hero-player-footer-hud { flex-direction: column; align-items: stretch; text-align: center; gap: 7px; }
        .footer-hud-left, .footer-hud-center, .footer-hud-right { justify-content: center; }
        .scenery-prop { display: none; }
        .crest-title-ic3 { font-size: 18px; }
        /* Mobile: chaser xuống 2 hàng scroll */
        .chaser-dock-cards-row { grid-template-columns: repeat(4, 1fr); }
        .sprite-gold   { height: 130px; }
        .sprite-silver { height: 110px; }
        .sprite-bronze { height: 108px; }
    }
    @media (prefers-reduced-motion: reduce) {
        .champion-crown-orbit, .champion-halo-ray, .prop-right, .timer-clock-badge,
        .sprite-glow-gold, .sprite-glow-silver, .sprite-glow-bronze,
        .my-rank-chip, .pillar-hero-figure { animation: none !important; }
    }

    /* ===== LOADING OVERLAY IC3 (hiển thị ở giữa khi đổi khối lọc) ===== */
    .lb-loading-overlay {
        position: absolute;
        inset: 0;
        z-index: 999;
        background: rgba(4, 12, 35, 0.72);
        backdrop-filter: blur(3px);
        -webkit-backdrop-filter: blur(3px);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 16px;
        border-radius: inherit;
        animation: overlayFadeIn 0.15s ease;
    }
    @keyframes overlayFadeIn {
        from { opacity: 0; }
        to   { opacity: 1; }
    }
    .lb-loading-ring {
        width: 58px;
        height: 58px;
        border-radius: 50%;
        border: 4px solid rgba(56, 189, 248, 0.25);
        border-top-color: #38bdf8;
        border-right-color: #fbbf24;
        animation: ic3SpinRing 0.85s linear infinite;
        box-shadow: 0 0 24px rgba(56,189,248,0.5);
    }
    @keyframes ic3SpinRing {
        to { transform: rotate(360deg); }
    }
    .lb-loading-text {
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 14px;
        font-weight: 800;
        color: #7dd3fc;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        text-shadow: 0 0 12px rgba(56,189,248,0.8);
        animation: loadTextBlink 1.4s ease-in-out infinite alternate;
    }
    @keyframes loadTextBlink {
        from { opacity: 0.6; }
        to   { opacity: 1.0; }
    }

    /* Fade-in nhẹ khi nội dung mới được inject */
    @keyframes leaderboardSpinAnim {
        to { transform: rotate(360deg); }
    }
    .leaderboard-dynamic-fade-in {
        animation: lbFadeIn 0.3s ease forwards;
    }
    @keyframes lbFadeIn {
        from { opacity: 0.5; transform: translateY(6px); }
        to   { opacity: 1;   transform: translateY(0); }
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
        box-shadow: 0 4px 0 #024e75, 0 6px 14px rgba(0,0,0,0.15);
        white-space: nowrap;
        transition: transform 0.15s ease;
    }
    .btn-cta-action:hover { transform: translateY(-2px); }
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
                if (typeof window.updateGlobalStarWallet === 'function') {
                    window.updateGlobalStarWallet(currentStars);
                }
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
    const gradeHtmlCache = {};

    async function switchLeaderboardGrade(grade) {
        if (isLeaderboardLoading) return;
        const wrapper = document.getElementById('leaderboard-ajax-wrapper');
        if (!wrapper) return;

        // Cập nhật giao diện tab ngay lập tức (instant visual feedback)
        document.querySelectorAll('.arena-grade-pill').forEach(pill => {
            const isActive = pill.getAttribute('data-grade') === String(grade);
            pill.classList.toggle('pill-active', isActive);
        });

        // ⚡ NẾU ĐÃ LƯU CACHE ➔ HIỂN THỊ NGAY TỨC THÌ 0MS (TRẢI NGHIỆM SIÊU MƯỢT)
        if (gradeHtmlCache[grade]) {
            wrapper.innerHTML = gradeHtmlCache[grade];
            const cleanUrl = new URL(window.location.href);
            cleanUrl.searchParams.set('grade', grade);
            window.history.replaceState({ grade: grade }, '', cleanUrl.toString());
            initLeaderboardTimer();
            return;
        }

        isLeaderboardLoading = true;

        // ✨ IC3 LOADING OVERLAY — hiển thị ở giữa màn khi chưa có cache
        const oldOverlay = document.getElementById('lb-loading-overlay');
        if (oldOverlay) oldOverlay.remove();

        const overlay = document.createElement('div');
        overlay.id = 'lb-loading-overlay';
        overlay.className = 'lb-loading-overlay';
        overlay.innerHTML = `
            <div class="lb-loading-ring"></div>
            <span class="lb-loading-text">IC3 Đang tải...</span>
        `;
        if (getComputedStyle(wrapper).position === 'static') {
            wrapper.style.position = 'relative';
        }
        wrapper.appendChild(overlay);

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
                // Lưu vào cache bộ nhớ client
                gradeHtmlCache[grade] = data.html;
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
            const overlayEl = document.getElementById('lb-loading-overlay');
            if (overlayEl) overlayEl.remove();
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

            const secPad = seconds < 10 ? '0' + seconds : seconds;
            const minPad = minutes < 10 ? '0' + minutes : minutes;

            if (days > 0) {
                textEl.innerText = `${days} ngày ${hours}h ${minPad}p ${secPad}s`;
            } else if (hours > 0) {
                textEl.innerText = `${hours} giờ ${minPad}p ${secPad}s`;
            } else {
                textEl.innerText = `${minPad} phút ${secPad}s`;
            }
        }

        updateCountdown();
        leaderboardCountdownInterval = setInterval(updateCountdown, 1000);
    }

    document.addEventListener('DOMContentLoaded', () => {
        const wrapper = document.getElementById('leaderboard-ajax-wrapper');
        if (wrapper) {
            gradeHtmlCache['{{ $selectedGrade }}'] = wrapper.innerHTML;
        }
        initLeaderboardTimer();
    });
</script>
@endsection