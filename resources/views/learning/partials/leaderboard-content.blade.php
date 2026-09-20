{{-- Partial Bảng Vàng Thi Đua — IC3 Digital Adventure (v4) --}}
@php
    $first  = $podiumStudents->firstWhere('rank', 1);
    $second = $podiumStudents->firstWhere('rank', 2);
    $third  = $podiumStudents->firstWhere('rank', 3);
    $getKidAvatar = function($id) {
        $num = (($id ?? 1) % 8) + 1;
        return asset("images/leaderboard/avatars/kid-{$num}.png");
    };
    $gradeLabel = (string)$selectedGrade === 'all'
        ? 'BẢNG VÀNG TOÀN TRƯỜNG'
        : 'BẢNG VÀNG KHỐI ' . $selectedGrade;
@endphp

<div id="leaderboard-dynamic-container" class="leaderboard-fantasy-hero leaderboard-dynamic-fade-in">

    <div class="hero-stage-viewport">

        {{-- HUD: BỘ LỌC KHỐI + TIMER (BỎ NÚT 3 GẠCH) --}}
        <div class="sky-floating-hud">
            <div class="sky-hud-left">
                <div class="sky-grade-pill-group" id="grade-filter-tabs">
                    @php
                        $gradeTabs = [
                            'all' => ['label' => 'Toàn trường', 'icon' => '🎓'],
                            3     => ['label' => 'Khối 3',      'icon' => '🌟'],
                            4     => ['label' => 'Khối 4',      'icon' => '⭐'],
                            5     => ['label' => 'Khối 5',      'icon' => '🏆'],
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
            <div class="sky-hud-right">
                @if(!empty($nextResetTimestamp))
                    <div class="sky-timer-capsule" id="leaderboard-timer-capsule" data-next-reset="{{ $nextResetTimestamp }}">
                        <div class="timer-clock-badge"><span>⏱️</span></div>
                        <div class="timer-text-group">
                            <span class="timer-header-label">KẾT THÚC VÒNG ĐUA SAU:</span>
                            <b class="timer-countdown-val" id="live-countdown-text">Đang tính...</b>
                        </div>
                    </div>
                @else
                    <div class="sky-timer-capsule">
                        <div class="timer-clock-badge"><span>🏆</span></div>
                        <div class="timer-text-group">
                            <span class="timer-header-label">VÒNG ĐUA HIỆN TẠI:</span>
                            <b class="timer-countdown-val">Đang diễn ra</b>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- SCENERY SIGNBOARDS --}}
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
                <span class="sign-bot">tri thức!</span>
            </div>
        </div>

        {{-- TITLE CREST --}}
        <div class="hero-title-crest">
            <img src="{{ asset('images/leaderboard/crest-banner-3d.png') }}" class="crest-wings-image" alt="IC3 Wings" loading="eager">
            <div class="crest-headings-overlay">
                <h2 class="crest-title-ic3">IC3 DIGITAL ADVENTURE</h2>
                <div class="crest-gold-ribbon"><span>{{ $gradeLabel }}</span></div>
                <div class="crest-motto-capsule"><span>Học vui • Chơi giỏi • Lớn khôn!</span></div>
            </div>
        </div>

        {{-- TOP 3 PODIUMS --}}
        <div class="podiums-arena-trio">

            {{-- RANK 2: Á QUÂN --}}
            <div class="podium-pillar pillar-silver {{ $second && $second['is_me'] ? 'pillar-is-me' : '' }}">
                @if($second)
                    <div class="pillar-hero-figure">
                        <div class="sprite-glow-wrap sprite-glow-silver">
                            <img src="{{ asset('images/leaderboard/hero-runnerup.png') }}" alt="Á Quân" class="hero-avatar-sprite sprite-silver" loading="eager">
                        </div>
                    </div>
                    <div class="pedestal-shield-badge badge-silver">
                        <span class="pedestal-wing">🪽</span><span class="pedestal-num">2</span><span class="pedestal-wing">🪽</span>
                    </div>
                    <div class="pedestal-altar altar-silver">
                        <div class="altar-badge badge-silver-text">Á QUÂN</div>
                        <div class="altar-card">
                            <div class="altar-profile">
                                <div class="avatar-ring ring-silver">
                                    <img src="{{ $getKidAvatar($second['id']) }}" alt="{{ $second['name'] }}" class="avatar-img" loading="lazy">
                                </div>
                                <div class="profile-info">
                                    <b class="profile-name" title="{{ $second['name'] }}">{{ $second['name'] }}</b>
                                    <span class="profile-class">👤 {{ $second['classroom_name'] }}</span>
                                    @if($second['is_me'])<span class="my-rank-chip">🎉 Bé đây!</span>@endif
                                </div>
                            </div>
                            <div class="altar-score-pill pill-silver"><span class="coin-icon">⭐</span><b>{{ number_format($second['weekly_score']) }}</b><span>điểm</span></div>
                        </div>
                    </div>
                @else
                    <div class="pillar-hero-figure"><div class="placeholder-icon">🥈</div></div>
                    <div class="pedestal-shield-badge badge-silver"><span class="pedestal-num">2</span></div>
                    <div class="pedestal-altar altar-silver">
                        <div class="altar-badge badge-silver-text">Á QUÂN</div>
                        <div class="altar-card"><div class="placeholder-text">Đang chờ Á Quân</div></div>
                    </div>
                @endif
            </div>

            {{-- RANK 1: QUÁN QUÂN --}}
            <div class="podium-pillar pillar-gold {{ $first && $first['is_me'] ? 'pillar-is-me' : '' }}">
                @if($first)
                    <div class="pillar-hero-figure champion-hero-figure">
                        <div class="champion-halo-ray"></div>
                        <div class="sprite-glow-wrap sprite-glow-gold">
                            <div class="champion-crown-orbit">
                                <span class="sparkle sp-1">✨</span>
                                <span class="sparkle sp-2">⭐</span>
                                <img src="{{ asset('images/leaderboard/crown-3d.png') }}" class="orbit-crown-img" alt="Crown" loading="eager">
                            </div>
                            <img src="{{ asset('images/leaderboard/hero-champion.png') }}" alt="Quán Quân" class="hero-avatar-sprite sprite-gold" loading="eager">
                        </div>
                    </div>
                    <div class="pedestal-shield-badge badge-gold">
                        <span class="laurel-leaf">🌿</span><span class="pedestal-num num-champ">1</span><span class="laurel-leaf">🌿</span>
                    </div>
                    <div class="pedestal-altar altar-gold">
                        <div class="altar-badge badge-gold-text">QUÁN QUÂN</div>
                        <div class="altar-card card-gold">
                            <div class="altar-profile">
                                {{-- Avatar KHÔNG bị che bởi badge --}}
                                <div class="avatar-ring ring-gold">
                                    <img src="{{ $getKidAvatar($first['id']) }}" alt="{{ $first['name'] }}" class="avatar-img" loading="lazy">
                                </div>
                                <div class="profile-info">
                                    <b class="profile-name name-gold" title="{{ $first['name'] }}">{{ $first['name'] }}</b>
                                    <span class="profile-class">👤 {{ $first['classroom_name'] }}</span>
                                    @if($first['is_me'])<span class="my-rank-chip chip-champ">👑 Bé — Vô Địch!</span>@endif
                                </div>
                            </div>
                            <div class="altar-score-pill pill-gold"><span class="coin-icon">⭐</span><b>{{ number_format($first['weekly_score']) }}</b><span>điểm</span></div>
                        </div>
                        <div class="royal-carpet-runner"></div>
                    </div>
                @else
                    <div class="pillar-hero-figure"><div class="placeholder-icon">👑</div></div>
                    <div class="pedestal-shield-badge badge-gold"><span class="pedestal-num num-champ">1</span></div>
                    <div class="pedestal-altar altar-gold">
                        <div class="altar-badge badge-gold-text">QUÁN QUÂN</div>
                        <div class="altar-card"><div class="placeholder-text">Đang chờ Quán Quân</div></div>
                    </div>
                @endif
            </div>

            {{-- RANK 3: HẠNG BA --}}
            <div class="podium-pillar pillar-bronze {{ $third && $third['is_me'] ? 'pillar-is-me' : '' }}">
                @if($third)
                    <div class="pillar-hero-figure">
                        <div class="sprite-glow-wrap sprite-glow-bronze">
                            <img src="{{ asset('images/leaderboard/hero-thirdplace.png') }}" alt="Hạng Ba" class="hero-avatar-sprite sprite-bronze" loading="eager">
                        </div>
                    </div>
                    <div class="pedestal-shield-badge badge-bronze">
                        <span class="pedestal-wing">🪽</span><span class="pedestal-num">3</span><span class="pedestal-wing">🪽</span>
                    </div>
                    <div class="pedestal-altar altar-bronze">
                        <div class="altar-badge badge-bronze-text">HẠNG BA</div>
                        <div class="altar-card">
                            <div class="altar-profile">
                                <div class="avatar-ring ring-bronze">
                                    <img src="{{ $getKidAvatar($third['id']) }}" alt="{{ $third['name'] }}" class="avatar-img" loading="lazy">
                                </div>
                                <div class="profile-info">
                                    <b class="profile-name" title="{{ $third['name'] }}">{{ $third['name'] }}</b>
                                    <span class="profile-class">👤 {{ $third['classroom_name'] }}</span>
                                    @if($third['is_me'])<span class="my-rank-chip">🎉 Bé đây!</span>@endif
                                </div>
                            </div>
                            <div class="altar-score-pill pill-bronze"><span class="coin-icon">⭐</span><b>{{ number_format($third['weekly_score']) }}</b><span>điểm</span></div>
                        </div>
                    </div>
                @else
                    <div class="pillar-hero-figure"><div class="placeholder-icon">🥉</div></div>
                    <div class="pedestal-shield-badge badge-bronze"><span class="pedestal-num">3</span></div>
                    <div class="pedestal-altar altar-bronze">
                        <div class="altar-badge badge-bronze-text">HẠNG BA</div>
                        <div class="altar-card"><div class="placeholder-text">Đang chờ Hạng Ba</div></div>
                    </div>
                @endif
            </div>

        </div>

        {{-- HONOR PLAQUE --}}
        <div class="stage-honor-dock">
            <div class="honor-torch">🔥</div>
            <div class="honor-stone-slab">
                <span class="star-accent">★</span>
                <span class="honor-slab-text">NHỮNG HIỆP SĨ XUẤT SẮC NHẤT IC3</span>
                <span class="star-accent">★</span>
            </div>
            <div class="honor-torch">🔥</div>
        </div>

    </div>

    {{-- RANK 4-10: 7 CỘT KHÔNG SCROLL --}}
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
                    $rankColors = [4=>'#f59e0b',5=>'#8b5cf6',6=>'#0ea5e9',7=>'#ec4899',8=>'#10b981',9=>'#f97316',10=>'#6366f1'];
                    $rankColor = $rankColors[$st['rank']] ?? '#38bdf8';
                @endphp
                <div class="chaser-item-card {{ $st['is_me'] ? 'card-is-me' : '' }}" style="--rank-color:{{ $rankColor }}">
                    <div class="chaser-rank-badge">{{ $st['rank'] }}</div>
                    <div class="chaser-avatar-wrap">
                        <img src="{{ $getKidAvatar($st['id']) }}" alt="{{ $st['name'] }}" class="chaser-avatar-img" loading="lazy">
                    </div>
                    <div class="chaser-info">
                        <b class="chaser-name" title="{{ $st['name'] }}">{{ $st['name'] }}</b>
                        <span class="chaser-class">{{ $st['classroom_name'] }}</span>
                        <div class="chaser-score">⭐ {{ number_format($st['weekly_score']) }}</div>
                    </div>
                </div>
            @empty
                <div class="chaser-empty-row">Đang cập nhật thêm hiệp sĩ...</div>
            @endforelse
        </div>
    </div>

    {{-- PLAYER FOOTER --}}
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
                <span class="hud-status-text">🎯 <b>Bé đang ở #{{ $myRank['rank'] ?? '?' }}! Luyện ngay để bứt phá Top 10!</b></span>
            @endif
        </div>
        <div class="footer-hud-right">
            <a href="{{ route('programs') }}" class="btn-luyen-ngay-action" title="Làm bài luyện tập ngay">
                <span>✏️</span><span>Luyện Ngay</span><span>➔</span>
            </a>
        </div>
    </div>

</div>
