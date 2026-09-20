{{-- Partial Bảng Vàng Thi Đua & Bảng Xếp Hạng Đấu Trường Game Kỳ Ảo 3D (IC3 Digital Adventure - Match Ref B 100%) --}}
@php
    $first = $podiumStudents->firstWhere('rank', 1);
    $second = $podiumStudents->firstWhere('rank', 2);
    $third = $podiumStudents->firstWhere('rank', 3);

    $getKidAvatar = function($id) {
        $num = (($id ?? 1) % 8) + 1;
        return asset("images/leaderboard/avatars/kid-{$num}.png");
    };

    $gradeLabel = (string)$selectedGrade === 'all' 
        ? 'BẢNG VÀNG TOÀN TRƯỜNG' 
        : 'BẢNG VÀNG KHỐI ' . $selectedGrade;
@endphp

<div id="leaderboard-dynamic-container" class="leaderboard-fantasy-hero leaderboard-dynamic-fade-in">

    <!-- ===================================================================
         1. FANTASY STAGE (THE ARENA WITH SKY HUD, TITLE CREST & PODIUMS)
         =================================================================== -->
    <div class="hero-stage-viewport">
        
        <!-- A. SKY FLOATING HUD (GRADE SWITCHER & COUNTDOWN CAPSULE) -->
        <div class="sky-floating-hud">
            <!-- Left: Menu + Grade Switcher -->
            <div class="sky-hud-left">
                <button type="button" class="sky-btn-menu" title="Menu bảng vàng" aria-label="Menu">
                    <span>☰</span>
                </button>
                <div class="sky-grade-pill-group" id="grade-filter-tabs">
                    @php
                        $gradeTabs = [
                            'all' => ['label' => 'Toàn trường', 'icon' => '🎓'],
                            3 => ['label' => 'Khối 3', 'icon' => '👶'],
                            4 => ['label' => 'Khối 4', 'icon' => '🎒'],
                            5 => ['label' => 'Khối 5', 'icon' => '🎒'],
                        ];
                    @endphp
                    @foreach($gradeTabs as $gKey => $gData)
                        <button type="button"
                            class="arena-grade-pill {{ (string)$selectedGrade === (string)$gKey ? 'pill-active' : '' }}"
                            data-grade="{{ $gKey }}"
                            onclick="switchLeaderboardGrade('{{ $gKey }}')">
                            <span class="pill-icon">{{ $gData['icon'] }}</span>
                            <span>{{ $gData['label'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Right: Countdown Timer Capsule -->
            <div class="sky-hud-right">
                @if(!empty($nextResetTimestamp))
                    <div class="sky-timer-capsule" id="leaderboard-timer-capsule" data-next-reset="{{ $nextResetTimestamp }}" title="Thời gian còn lại của vòng thi đua hiện tại">
                        <div class="timer-clock-badge">
                            <span>⏱️</span>
                        </div>
                        <div class="timer-text-group">
                            <span class="timer-header-label">KẾT THÚC VÒNG ĐUA SAU:</span>
                            <b class="timer-countdown-val" id="live-countdown-text">Đang tính...</b>
                        </div>
                    </div>
                @else
                    <div class="sky-timer-capsule" title="Vòng đua thi đua liên tục">
                        <div class="timer-clock-badge"><span>🏆</span></div>
                        <div class="timer-text-group">
                            <span class="timer-header-label">VÒNG ĐUA HIỆN TẠI:</span>
                            <b class="timer-countdown-val">Đang diễn ra</b>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- B. SCENERY SIGNBOARDS (LEFT IC3 PILLARS & RIGHT INSPIRATIONAL BOARD) -->
        <div class="scenery-prop prop-left">
            <div class="stone-pillar-tablet">
                <span class="tablet-brand">IC3</span>
                <span class="tablet-point">KIẾN THỨC</span>
                <span class="tablet-point">KỸ NĂNG</span>
                <span class="tablet-point">TƯƠNG LAI</span>
            </div>
        </div>
        <div class="scenery-prop prop-right">
            <div class="rustic-wood-signboard">
                <span class="sign-top">Cùng nhau</span>
                <b class="sign-mid">chinh phục</b>
                <span class="sign-bot">tri thức !</span>
            </div>
        </div>

        <!-- C. CENTER TITLE CREST BANNER -->
        <div class="hero-title-crest">
            <img src="{{ asset('images/leaderboard/crest-banner-3d.png') }}" class="crest-wings-image" alt="Golden Wings & Crown">
            <div class="crest-headings-overlay">
                <h2 class="crest-title-ic3">IC3 DIGITAL ADVENTURE</h2>
                <div class="crest-gold-ribbon">
                    <span>{{ $gradeLabel }}</span>
                </div>
                <div class="crest-motto-capsule">
                    <span>Học vui • Chơi giỏi • Lớn khôn!</span>
                </div>
            </div>
        </div>

        <!-- D. TOP 3 OLYMPIC PODIUMS TRIO -->
        <div class="podiums-arena-trio">

            <!-- RANK 2: Á QUÂN (LEFT) -->
            <div class="podium-pillar pillar-silver {{ $second && $second['is_me'] ? 'pillar-is-me' : '' }}">
                @if($second)
                    <div class="pillar-hero-figure">
                        <img src="{{ asset('images/leaderboard/hero-runnerup.png') }}" alt="Á Quân" class="hero-avatar-sprite sprite-silver">
                    </div>
                    <div class="pedestal-shield-badge badge-silver">
                        <span class="pedestal-wing">🪽</span>
                        <span class="pedestal-num">2</span>
                        <span class="pedestal-wing">🪽</span>
                    </div>
                    <div class="pedestal-altar altar-silver">
                        <div class="altar-badge badge-silver-text">Á QUÂN</div>
                        <div class="altar-card">
                            <div class="altar-profile">
                                <div class="avatar-ring ring-silver">
                                    <img src="{{ $getKidAvatar($second['id']) }}" alt="{{ $second['name'] }}" class="avatar-img">
                                    @if($second['is_me'])
                                        <span class="tag-me-dot">BÉ</span>
                                    @endif
                                </div>
                                <div class="profile-info">
                                    <b class="profile-name" title="{{ $second['name'] }}">{{ $second['name'] }}</b>
                                    <span class="profile-class">👤 {{ $second['classroom_name'] }}</span>
                                </div>
                            </div>
                            <div class="altar-score-pill pill-silver">
                                <span class="coin-icon">⭐</span>
                                <b>{{ number_format($second['weekly_score']) }}</b>
                                <span>điểm</span>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="pillar-hero-figure placeholder-sprite">
                        <div class="placeholder-icon">🥈</div>
                    </div>
                    <div class="pedestal-shield-badge badge-silver">
                        <span class="pedestal-num">2</span>
                    </div>
                    <div class="pedestal-altar altar-silver placeholder-altar">
                        <div class="altar-badge badge-silver-text">Á QUÂN</div>
                        <div class="altar-card">
                            <div class="placeholder-text">Đang chờ Á Quân</div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- RANK 1: QUÁN QUÂN (CENTER - HIGHEST & GRANDEST) -->
            <div class="podium-pillar pillar-gold {{ $first && $first['is_me'] ? 'pillar-is-me' : '' }}">
                @if($first)
                    <div class="pillar-hero-figure champion-hero-figure">
                        <div class="champion-halo-ray"></div>
                        <div class="champion-crown-orbit">
                            <span class="sparkle sp-1">✨</span>
                            <span class="sparkle sp-2">⭐</span>
                            <img src="{{ asset('images/leaderboard/crown-3d.png') }}" class="orbit-crown-img" alt="Crown">
                        </div>
                        <img src="{{ asset('images/leaderboard/hero-champion.png') }}" alt="Quán Quân" class="hero-avatar-sprite sprite-gold">
                    </div>
                    <div class="pedestal-shield-badge badge-gold">
                        <span class="laurel-leaf">🌿</span>
                        <span class="pedestal-num num-champ">1</span>
                        <span class="laurel-leaf">🌿</span>
                    </div>
                    <div class="pedestal-altar altar-gold">
                        <div class="altar-badge badge-gold-text">QUÁN QUÂN</div>
                        <div class="altar-card card-gold">
                            <div class="altar-profile">
                                <div class="avatar-ring ring-gold">
                                    <img src="{{ $getKidAvatar($first['id']) }}" alt="{{ $first['name'] }}" class="avatar-img">
                                    @if($first['is_me'])
                                        <span class="tag-me-dot tag-champ">VÔ ĐỊCH</span>
                                    @endif
                                </div>
                                <div class="profile-info">
                                    <b class="profile-name name-gold" title="{{ $first['name'] }}">{{ $first['name'] }}</b>
                                    <span class="profile-class">👤 {{ $first['classroom_name'] }}</span>
                                </div>
                            </div>
                            <div class="altar-score-pill pill-gold">
                                <span class="coin-icon">⭐</span>
                                <b>{{ number_format($first['weekly_score']) }}</b>
                                <span>điểm</span>
                            </div>
                        </div>
                        <!-- Thảm đỏ hoàng gia -->
                        <div class="royal-carpet-runner"></div>
                    </div>
                @else
                    <div class="pillar-hero-figure placeholder-sprite">
                        <div class="placeholder-icon">👑</div>
                    </div>
                    <div class="pedestal-shield-badge badge-gold">
                        <span class="pedestal-num num-champ">1</span>
                    </div>
                    <div class="pedestal-altar altar-gold placeholder-altar">
                        <div class="altar-badge badge-gold-text">QUÁN QUÂN</div>
                        <div class="altar-card">
                            <div class="placeholder-text">Đang chờ Quán Quân</div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- RANK 3: HẠNG BA (RIGHT) -->
            <div class="podium-pillar pillar-bronze {{ $third && $third['is_me'] ? 'pillar-is-me' : '' }}">
                @if($third)
                    <div class="pillar-hero-figure">
                        <img src="{{ asset('images/leaderboard/hero-thirdplace.png') }}" alt="Hạng Ba" class="hero-avatar-sprite sprite-bronze">
                    </div>
                    <div class="pedestal-shield-badge badge-bronze">
                        <span class="pedestal-wing">🪽</span>
                        <span class="pedestal-num">3</span>
                        <span class="pedestal-wing">🪽</span>
                    </div>
                    <div class="pedestal-altar altar-bronze">
                        <div class="altar-badge badge-bronze-text">HẠNG BA</div>
                        <div class="altar-card">
                            <div class="altar-profile">
                                <div class="avatar-ring ring-bronze">
                                    <img src="{{ $getKidAvatar($third['id']) }}" alt="{{ $third['name'] }}" class="avatar-img">
                                    @if($third['is_me'])
                                        <span class="tag-me-dot">BÉ</span>
                                    @endif
                                </div>
                                <div class="profile-info">
                                    <b class="profile-name" title="{{ $third['name'] }}">{{ $third['name'] }}</b>
                                    <span class="profile-class">👤 {{ $third['classroom_name'] }}</span>
                                </div>
                            </div>
                            <div class="altar-score-pill pill-bronze">
                                <span class="coin-icon">⭐</span>
                                <b>{{ number_format($third['weekly_score']) }}</b>
                                <span>điểm</span>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="pillar-hero-figure placeholder-sprite">
                        <div class="placeholder-icon">🥉</div>
                    </div>
                    <div class="pedestal-shield-badge badge-bronze">
                        <span class="pedestal-num">3</span>
                    </div>
                    <div class="pedestal-altar altar-bronze placeholder-altar">
                        <div class="altar-badge badge-bronze-text">HẠNG BA</div>
                        <div class="altar-card">
                            <div class="placeholder-text">Đang chờ Hạng Ba</div>
                        </div>
                    </div>
                @endif
            </div>

        </div>

        <!-- E. STONE HONOR PLAQUE (NHỮNG HIỆP SĨ XUẤT SẮC NHẤT IC3) -->
        <div class="stage-honor-dock">
            <div class="honor-torch torch-left">🔥</div>
            <div class="honor-stone-slab">
                <span class="star-accent">★</span>
                <span class="honor-slab-text">NHỮNG HIỆP SĨ XUẤT SẮC NHẤT IC3</span>
                <span class="star-accent">★</span>
            </div>
            <div class="honor-torch torch-right">🔥</div>
        </div>

    </div>

    <!-- ===================================================================
         2. RANK 4 - 10 HORIZONTAL STRIP (DẢI THẺ BÀI NẰM NGANG CHUẨN REF B)
         =================================================================== -->
    <div class="hero-chaser-dock">
        <div class="chaser-dock-header">
            <div class="chaser-dock-title">
                <span class="chaser-bolt-glow">⚡</span>
                <span>TOP HIỆP SĨ BÁM ĐUỔI (HẠNG 4 - 10)</span>
            </div>
            <a href="#modalAllLeaderboard" data-bs-toggle="modal" class="chaser-dock-link">
                <span>Xem tất cả</span> <b>➔</b>
            </a>
        </div>

        <div class="chaser-dock-cards-row">
            @forelse($rankingList as $st)
                @php
                    $isEven = $st['rank'] % 2 === 0;
                    $badgeTheme = $isEven ? 'badge-cyan' : 'badge-pink';
                    $ribbonMedal = match($st['rank']) {
                        4, 6, 8, 10 => '🥈',
                        5, 7, 9 => '🎖️',
                        default => '⭐',
                    };
                @endphp
                <div class="chaser-item-pill {{ $st['is_me'] ? 'item-is-me' : '' }}">
                    <div class="item-rank-num {{ $badgeTheme }}">
                        <span>{{ $st['rank'] }}</span>
                    </div>
                    <div class="item-avatar-col">
                        <img src="{{ $getKidAvatar($st['id']) }}" alt="{{ $st['name'] }}" class="item-avatar-img">
                        <span class="item-mini-ribbon">{{ $ribbonMedal }}</span>
                    </div>
                    <div class="item-info-col">
                        <b class="item-name" title="{{ $st['name'] }}">{{ $st['name'] }}</b>
                        <span class="item-class">👤 {{ $st['classroom_name'] }}</span>
                        <div class="item-score">
                            <span class="score-star">⭐</span>
                            <b>{{ number_format($st['weekly_score']) }}</b>
                            <small>điểm</small>
                        </div>
                    </div>
                </div>
            @empty
                <div class="chaser-empty-row">
                    <span>Đang cập nhật thêm hiệp sĩ bám đuổi...</span>
                </div>
            @endforelse
        </div>
    </div>

    <!-- ===================================================================
         3. CURRENT PLAYER STATUS STICKY HUD (BĂNG DÀI VỊ TRÍ CỦA BẠN)
         =================================================================== -->
    <div class="hero-player-footer-hud">
        <div class="footer-hud-left">
            <div class="footer-rank-badge {{ isset($myRank['rank']) && $myRank['rank'] <= 3 ? 'rank-top3' : '' }}">
                <span>{{ $myRank['rank'] ?? '?' }}</span>
            </div>
            <div class="footer-rank-context">
                <span class="context-label">VỊ TRÍ CỦA BẠN TRÊN BẢNG VÀNG — <b>{{ (string)$selectedGrade === 'all' ? 'TOÀN TRƯỜNG' : 'KHỐI ' . $selectedGrade }}</b></span>
            </div>
        </div>

        <div class="footer-hud-center">
            @if(isset($myRank['rank']) && $myRank['rank'] == 1)
                <span class="hud-status-text">👑 <b>Quán Quân! Bé đang dẫn đầu Bảng Vàng!</b></span>
            @elseif(isset($myRank['rank']) && $myRank['rank'] <= 3)
                <span class="hud-status-text">🔥 <b>Tuyệt đỉnh! Bé đang vinh dự đứng trong TOP 3!</b></span>
            @elseif(isset($myRank['rank']) && $myRank['rank'] <= 10)
                <span class="hud-status-text">⚡ <b>Bé đang nằm trong Top 10 Hiệp Sĩ Xuất Sắc!</b></span>
            @else
                <span class="hud-status-text">🎯 <b>Bé đang ở vị trí #{{ $myRank['rank'] ?? '?' }}! Luyện ngay để bứt phá Top 10!</b></span>
            @endif
        </div>

        <div class="footer-hud-right">
            <a href="{{ route('programs') }}" class="btn-luyen-ngay-action" title="Làm bài luyện tập ngay">
                <span class="btn-icon">✏️</span>
                <span>Luyện Ngay</span>
                <span class="btn-arrow">➔</span>
            </a>
        </div>
    </div>

</div>