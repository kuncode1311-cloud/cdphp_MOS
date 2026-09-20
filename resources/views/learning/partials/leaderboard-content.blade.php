{{-- Partial Bảng Vàng Thi Đua & Bảng Xếp Hạng (Dùng cho cả lần đầu nạp trang và AJAX load động) --}}
<div id="leaderboard-dynamic-container" class="leaderboard-dynamic-fade-in">
    
    <!-- Thanh điều khiển: Tabs chọn Khối lớp 3D & Viên nhộng đếm ngược -->
    <div class="leaderboard-controls-row">
        <!-- Tabs chọn Khối lớp 3D xúc giác đa sắc màu -->
        <div class="grade-filter-tabs" id="grade-filter-tabs">
            @php
                $gradeTabs = [
                    'all' => ['label' => 'Toàn trường', 'icon' => '🌐', 'colorClass' => 'tab-royal'],
                    3 => ['label' => 'Khối 3', 'icon' => '🎒', 'colorClass' => 'tab-emerald'],
                    4 => ['label' => 'Khối 4', 'icon' => '🎒', 'colorClass' => 'tab-sky'],
                    5 => ['label' => 'Khối 5', 'icon' => '🎒', 'colorClass' => 'tab-purple'],
                ];
            @endphp
            @foreach($gradeTabs as $gKey => $gData)
                <button type="button"
                    class="grade-filter-pill {{ $gData['colorClass'] }} {{ (string)$selectedGrade === (string)$gKey ? 'pill-active' : '' }}"
                    data-grade="{{ $gKey }}"
                    onclick="switchLeaderboardGrade('{{ $gKey }}')">
                    <span class="pill-icon">{{ $gData['icon'] }}</span>
                    <span>{{ $gData['label'] }}</span>
                </button>
            @endforeach
        </div>

        <!-- Viên nhộng đếm ngược kết thúc vòng đua sống động -->
        @if(!empty($nextResetTimestamp))
            <div class="leaderboard-timer-capsule" id="leaderboard-timer-capsule" data-next-reset="{{ $nextResetTimestamp }}" title="Thời gian còn lại của vòng thi đua hiện tại">
                <span class="timer-orb">⏱️</span>
                <div class="timer-text-wrap">
                    <span class="timer-label">KẾT THÚC VÒNG ĐUA SAU:</span>
                    <b class="timer-countdown" id="live-countdown-text">Đang tính...</b>
                </div>
            </div>
        @else
            <div class="leaderboard-timer-capsule" title="Vòng đua thi đua liên tục">
                <span class="timer-orb">🏆</span>
                <div class="timer-text-wrap">
                    <span class="timer-label">VÒNG ĐUA HIỆN TẠI:</span>
                    <b class="timer-countdown">Đang diễn ra</b>
                </div>
            </div>
        @endif
    </div>

    <!-- Thanh Năng Lượng Chiến Tích Của Bé (My Rank Energy Bar) -->
    <div class="my-rank-energy-bar">
        <div class="rank-bar-left">
            <div class="my-rank-orb {{ isset($myRank['rank']) && $myRank['rank'] <= 3 ? 'rank-orb-top3' : '' }}">
                <span class="rank-hash">#</span>
                <span class="rank-number">{{ $myRank['rank'] ?? '?' }}</span>
            </div>
            <div class="rank-user-info">
                <div class="rank-context-label">
                    VỊ TRÍ CỦA BẠN TRÊN BẢNG VÀNG — <b>{{ (string)$selectedGrade === 'all' ? 'TOÀN TRƯỜNG' : 'KHỐI ' . $selectedGrade }}</b>
                </div>
                <div class="rank-user-name-row">
                    <b class="user-display-name">{{ auth()->user()->name }}</b>
                    <span class="user-class-chip">🏫 {{ auth()->user()->classroom?->name ?? 'Học sinh IC3' }}</span>
                    <span class="user-score-badge">
                        ⭐ <b>{{ number_format($myRank['weekly_score'] ?? $weeklyScore) }}</b> điểm vòng này ({{ $myRank['tests_count'] ?? $weeklyAttempts->count() }} bài)
                    </span>
                </div>
            </div>
        </div>

        <div class="rank-bar-right">
            <div class="rank-motivation-badge">
                @if(isset($myRank['rank']) && $myRank['rank'] == 1)
                    <span>👑</span> <b>Quán Quân! Bé đang dẫn đầu Bảng Vàng!</b>
                @elseif(isset($myRank['rank']) && $myRank['rank'] <= 3)
                    <span>🔥</span> <b>Tuyệt đỉnh! Bé đang vinh dự đứng trong TOP 3!</b>
                @else
                    <span>⚡</span> <b>Luyện thi thêm để bứt phá vào TOP 3 nhé!</b>
                @endif
            </div>
            <a href="{{ route('programs') }}" class="btn-boost-rank" title="Làm thêm bài thi để cộng điểm thăng hạng">
                <span>🚀</span> Luyện Ngay <b>➔</b>
            </a>
        </div>
    </div>

    <!-- SÂN KHẤU VINH QUANG 3D OLYMPIC (CHAMPIONS STAGE) -->
    @php
        $first = $podiumStudents->firstWhere('rank', 1);
        $second = $podiumStudents->firstWhere('rank', 2);
        $third = $podiumStudents->firstWhere('rank', 3);
    @endphp

    <div class="olympic-stage-container">
        
        <!-- ==================== BẬC 2: Á QUÂN (BÊN TRÁI) ==================== -->
        <div class="podium-pillar pillar-silver {{ $second && $second['is_me'] ? 'pillar-is-me' : '' }}">
            @if($second)
                <!-- Phần Avatar & Thông tin đứng trên đỉnh bục -->
                <div class="pillar-avatar-group">
                    <div class="pillar-medal-ribbon ribbon-silver">
                        <span>🥈 Á QUÂN</span>
                    </div>

                    <div class="avatar-ring ring-silver {{ $second['is_me'] ? 'ring-pulse-me' : '' }}">
                        <div class="avatar-inner">
                            {{ mb_substr($second['name'], 0, 1) }}
                        </div>
                        @if($second['is_me'])
                            <span class="badge-me-tag">BÉ</span>
                        @endif
                    </div>

                    <div class="podium-player-name">{{ $second['name'] }}</div>
                    <div class="podium-player-class">🏫 {{ $second['classroom_name'] }}</div>

                    <div class="podium-score-pill pill-silver-score">
                        <span class="score-icon">🥈</span>
                        <b>{{ number_format($second['weekly_score']) }}</b>
                        <small>điểm</small>
                    </div>
                </div>

                <!-- Bục Đứng 3D Kim Loại Bạc -->
                <div class="pedestal-block block-silver">
                    <div class="pedestal-top-surface surface-silver"></div>
                    <div class="pedestal-front-face face-silver">
                        <div class="pedestal-3d-number num-silver">2</div>
                        <span class="pedestal-subtext">Á QUÂN</span>
                        <div class="pedestal-meta-stat">🎯 {{ $second['passed_count'] }} bài đạt chuẩn</div>
                    </div>
                </div>
            @else
                <div class="pillar-avatar-group placeholder-group">
                    <div class="placeholder-orb">🚀</div>
                    <div class="placeholder-title">Đang chờ Á Quân</div>
                    <div class="placeholder-sub">Bứt phá để chiếm bục 2</div>
                </div>
                <div class="pedestal-block block-silver">
                    <div class="pedestal-top-surface surface-silver"></div>
                    <div class="pedestal-front-face face-silver">
                        <div class="pedestal-3d-number num-silver">2</div>
                    </div>
                </div>
            @endif
        </div>

        <!-- ==================== BẬC 1: QUÁN QUÂN (Ở GIỮA - CAO NHẤT & TỎA SÁNG) ==================== -->
        <div class="podium-pillar pillar-gold {{ $first && $first['is_me'] ? 'pillar-is-me' : '' }}">
            <!-- Hào quang mặt trời tỏa sáng phía sau Quán Quân -->
            <div class="golden-sunburst-halo"></div>

            @if($first)
                <!-- Phần Avatar & Thông tin đứng trên đỉnh bục -->
                <div class="pillar-avatar-group">
                    <!-- Vương miện vàng 3D bay bồng bềnh -->
                    <div class="floating-crown-orb">
                        <span>👑</span>
                    </div>

                    <div class="pillar-medal-ribbon ribbon-gold">
                        <span>🥇 QUÁN QUÂN</span>
                    </div>

                    <div class="avatar-ring ring-gold {{ $first['is_me'] ? 'ring-pulse-me' : '' }}">
                        <div class="avatar-inner">
                            {{ mb_substr($first['name'], 0, 1) }}
                        </div>
                        @if($first['is_me'])
                            <span class="badge-me-tag tag-champion">⭐ BẠN LÀ VÔ ĐỊCH</span>
                        @endif
                    </div>

                    <div class="podium-player-name name-gold">{{ $first['name'] }}</div>
                    <div class="podium-player-class">🏫 {{ $first['classroom_name'] }}</div>

                    <div class="podium-score-pill pill-gold-score">
                        <span class="score-icon">🏆</span>
                        <b>{{ number_format($first['weekly_score']) }}</b>
                        <small>điểm</small>
                    </div>
                </div>

                <!-- Bục Đứng 3D Kim Loại Vàng Hoàng Gia Khổng Lồ -->
                <div class="pedestal-block block-gold">
                    <div class="pedestal-top-surface surface-gold"></div>
                    <div class="pedestal-front-face face-gold">
                        <div class="pedestal-3d-number num-gold">1</div>
                        <span class="pedestal-subtext subtext-gold">QUÁN QUÂN</span>
                        <div class="pedestal-meta-stat stat-gold">🎯 {{ $first['passed_count'] }} bài đạt chuẩn</div>
                    </div>
                </div>
            @else
                <div class="pillar-avatar-group placeholder-group">
                    <div class="placeholder-orb">👑</div>
                    <div class="placeholder-title">Đang chờ Quán Quân</div>
                    <div class="placeholder-sub">Chinh phục ngôi vương</div>
                </div>
                <div class="pedestal-block block-gold">
                    <div class="pedestal-top-surface surface-gold"></div>
                    <div class="pedestal-front-face face-gold">
                        <div class="pedestal-3d-number num-gold">1</div>
                    </div>
                </div>
            @endif
        </div>

        <!-- ==================== BẬC 3: HẠNG BA (BÊN PHẢI) ==================== -->
        <div class="podium-pillar pillar-bronze {{ $third && $third['is_me'] ? 'pillar-is-me' : '' }}">
            @if($third)
                <!-- Phần Avatar & Thông tin đứng trên đỉnh bục -->
                <div class="pillar-avatar-group">
                    <div class="pillar-medal-ribbon ribbon-bronze">
                        <span>🥉 HẠNG BA</span>
                    </div>

                    <div class="avatar-ring ring-bronze {{ $third['is_me'] ? 'ring-pulse-me' : '' }}">
                        <div class="avatar-inner">
                            {{ mb_substr($third['name'], 0, 1) }}
                        </div>
                        @if($third['is_me'])
                            <span class="badge-me-tag">BÉ</span>
                        @endif
                    </div>

                    <div class="podium-player-name">{{ $third['name'] }}</div>
                    <div class="podium-player-class">🏫 {{ $third['classroom_name'] }}</div>

                    <div class="podium-score-pill pill-bronze-score">
                        <span class="score-icon">🥉</span>
                        <b>{{ number_format($third['weekly_score']) }}</b>
                        <small>điểm</small>
                    </div>
                </div>

                <!-- Bục Đứng 3D Kim Loại Đồng Hổ Phách -->
                <div class="pedestal-block block-bronze">
                    <div class="pedestal-top-surface surface-bronze"></div>
                    <div class="pedestal-front-face face-bronze">
                        <div class="pedestal-3d-number num-bronze">3</div>
                        <span class="pedestal-subtext">HẠNG BA</span>
                        <div class="pedestal-meta-stat">🎯 {{ $third['passed_count'] }} bài đạt chuẩn</div>
                    </div>
                </div>
            @else
                <div class="pillar-avatar-group placeholder-group">
                    <div class="placeholder-orb">⚡</div>
                    <div class="placeholder-title">Đang chờ Hạng Ba</div>
                    <div class="placeholder-sub">Luyện thi để lên bục</div>
                </div>
                <div class="pedestal-block block-bronze">
                    <div class="pedestal-top-surface surface-bronze"></div>
                    <div class="pedestal-front-face face-bronze">
                        <div class="pedestal-3d-number num-bronze">3</div>
                    </div>
                </div>
            @endif
        </div>

    </div>

    <!-- Bệ Đỡ Sân Khấu Chung (Unified 3D Stage Footer Base) -->
    <div class="stage-footer-base">
        <div class="stage-base-label">
            <span>✨</span> ĐẤU TRƯỜNG VINH QUANG IC3 DIGITAL ADVENTURE <span>✨</span>
        </div>
    </div>

    <!-- BẢNG TOP HIỆP SĨ BÁM ĐUỔI (HẠNG 4 – 10) GỌN GÀNG, SẮC NÉT -->
    @if($rankingList->isNotEmpty())
        <div class="next-ranks-section">
            <div class="next-ranks-header">
                <div class="header-left">
                    <span class="header-bolt">⚡</span>
                    <span class="header-title">TOP HIỆP SĨ BÁM ĐUỔI (HẠNG 4 – 10)</span>
                </div>
                <span class="header-badge-hint">Cạnh tranh bứt phá từng điểm số</span>
            </div>

            <div class="next-ranks-grid">
                @foreach($rankingList as $st)
                    <div class="rank-row-item {{ $st['is_me'] ? 'row-is-me' : '' }}">
                        <div class="rank-row-left">
                            <div class="rank-badge-num">
                                #{{ $st['rank'] }}
                            </div>
                            <div class="rank-row-avatar">
                                {{ mb_substr($st['name'], 0, 1) }}
                            </div>
                            <div class="rank-row-info">
                                <div class="rank-row-title">
                                    <b class="name-text">{{ $st['name'] }}</b>
                                    @if($st['is_me'])
                                        <span class="tag-me-pill">⭐ BẠN</span>
                                    @endif
                                    <span class="class-chip">🏫 {{ $st['classroom_name'] }}</span>
                                </div>
                                <div class="rank-row-sub">
                                    <span>🎯 Đạt chuẩn: <b>{{ $st['passed_count'] }}/{{ $st['tests_count'] }} bài</b></span>
                                    <span>•</span>
                                    <span>⭐ {{ number_format($st['reward_stars']) }} Sao thưởng</span>
                                </div>
                            </div>
                        </div>

                        <div class="rank-row-right">
                            <div class="rank-row-score">
                                <b>{{ number_format($st['weekly_score']) }}</b>
                                <small>điểm</small>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @elseif($podiumStudents->isEmpty())
        <div class="empty-leaderboard-card">
            <div class="empty-icon">🎮</div>
            <h4>VÒNG THI ĐUA MỚI ĐÃ KHỞI TRANH!</h4>
            <p>Chưa có bạn nào ghi danh điểm số ở khối này trong vòng đua hiện tại. Hãy là người đầu tiên bước lên ngôi vị Quán quân!</p>
            <a href="{{ route('programs') }}" class="btn-boost-rank" style="margin: 0 auto; display: inline-flex;">
                <span>🚀</span> Luyện thi nhận điểm ngay
            </a>
        </div>
    @endif

</div>