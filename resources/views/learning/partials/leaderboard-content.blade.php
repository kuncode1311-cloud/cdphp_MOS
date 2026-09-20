{{-- Partial Bảng Vàng Thi Đua & Bảng Xếp Hạng (Dùng cho cả lần đầu nạp trang và AJAX load động) --}}
<div id="leaderboard-dynamic-container" class="leaderboard-dynamic-fade-in">
    
    <!-- Thanh chọn Khối lớp và Trạng thái Vòng đua -->
    <div class="leaderboard-controls-row">
        <!-- Tabs chọn Khối lớp thông minh chuẩn game 3D pill -->
        <div class="grade-filter-tabs" id="grade-filter-tabs">
            @foreach([3 => 'Khối 3', 4 => 'Khối 4', 5 => 'Khối 5', 'all' => 'Toàn trường'] as $gKey => $gLabel)
                <button type="button"
                    class="grade-filter-pill {{ (string)$selectedGrade === (string)$gKey ? 'pill-active' : '' }}"
                    data-grade="{{ $gKey }}"
                    onclick="switchLeaderboardGrade('{{ $gKey }}')">
                    @if($gKey === 'all') 🌐 @else 🎒 @endif {{ $gLabel }}
                </button>
            @endforeach
        </div>

        <!-- Huy hiệu đếm ngược kết thúc vòng đua -->
        @if(!empty($nextResetTimestamp))
            <div class="leaderboard-timer-capsule" id="leaderboard-timer-capsule" data-next-reset="{{ $nextResetTimestamp }}" title="Thời gian còn lại của vòng thi đua hiện tại">
                <span class="timer-icon">⏱️</span>
                <span class="timer-label">Kết thúc sau:</span>
                <b class="timer-countdown" id="live-countdown-text">Đang tính...</b>
            </div>
        @else
            <div class="leaderboard-timer-capsule" title="Vòng đua thi đua liên tục">
                <span class="timer-icon">🏆</span>
                <span class="timer-label">Vòng đua:</span>
                <b class="timer-countdown">Đang diễn ra</b>
            </div>
        @endif
    </div>

    <!-- Thanh ghim vị trí thi đua của bé (My Rank Banner) -->
    <div class="my-rank-banner">
        <div class="my-rank-left">
            <div class="my-rank-circle {{ isset($myRank['rank']) && $myRank['rank'] <= 3 ? 'rank-top3-glow' : '' }}">
                <span>#{{ $myRank['rank'] ?? '?' }}</span>
            </div>
            <div>
                <span class="my-rank-label">VỊ TRÍ CỦA BẠN TRÊN BẢNG XẾP HẠNG ({{ (string)$selectedGrade === 'all' ? 'TOÀN TRƯỜNG' : 'KHỐI ' . $selectedGrade }})</span>
                <div class="my-rank-name">
                    <b>{{ auth()->user()->name }}</b>
                    <span class="my-rank-stats">
                        • Đạt <b>{{ number_format($myRank['weekly_score'] ?? $weeklyScore) }}</b> điểm tuần này ({{ $myRank['tests_count'] ?? $weeklyAttempts->count() }} bài làm)
                    </span>
                </div>
            </div>
        </div>
        <div class="my-rank-motivation">
            @if(isset($myRank['rank']) && $myRank['rank'] == 1)
                👑 <b>Xuất sắc!</b> Bé đang giữ ngôi vị <b>Quán quân</b> Bảng vàng! 🎉
            @elseif(isset($myRank['rank']) && $myRank['rank'] <= 3)
                🔥 <b>Tuyệt vời!</b> Bé đang đứng trong <b>TOP 3</b> vinh quang! Tiếp tục bứt phá nhé!
            @else
                ⚡ Luyện thêm bài để thăng hạng vào <b>TOP 3 Hiệp sĩ</b> nhé!
            @endif
        </div>
    </div>

    <!-- BỤC VINH QUANG 3D OLYMPIC PODIUM (Top 1, 2, 3) -->
    @php
        $first = $podiumStudents->firstWhere('rank', 1);
        $second = $podiumStudents->firstWhere('rank', 2);
        $third = $podiumStudents->firstWhere('rank', 3);
    @endphp

    <div class="olympic-podium-wrap">
        
        <!-- BẬC 2: Á QUÂN (BÊN TRÁI) -->
        <div class="podium-col col-second {{ $second && $second['is_me'] ? 'col-me' : '' }}">
            @if($second)
                <div class="podium-card-top card-silver">
                    <div class="rank-badge-pill pill-silver">
                        <span>🥈 Á QUÂN</span>
                    </div>
                    <div class="podium-avatar avatar-silver">
                        {{ mb_substr($second['name'], 0, 1) }}
                    </div>
                    <b class="podium-name">
                        {{ $second['name'] }}
                        @if($second['is_me']) <span class="tag-me">BẠN</span> @endif
                    </b>
                    <span class="podium-class">🏫 {{ $second['classroom_name'] }}</span>
                    <div class="podium-score score-silver">
                        {{ number_format($second['weekly_score']) }} <small>điểm</small>
                    </div>
                    <div class="podium-footer-meta">
                        <span>🎯 {{ $second['passed_count'] }} bài đạt</span>
                        <span>⭐ {{ number_format($second['reward_stars']) }}</span>
                    </div>
                </div>
                <div class="podium-step-block step-silver">
                    <div class="step-face-front">
                        <span class="step-num">2</span>
                        <small class="step-title">Á QUÂN</small>
                    </div>
                </div>
            @else
                <div class="podium-card-top card-placeholder">
                    <div class="placeholder-icon">🚀</div>
                    <b class="placeholder-text">Đang chờ Á Quân!</b>
                    <span class="placeholder-hint">Luyện thi ngay để chiếm bục số 2</span>
                </div>
                <div class="podium-step-block step-silver">
                    <div class="step-face-front">
                        <span class="step-num">2</span>
                    </div>
                </div>
            @endif
        </div>

        <!-- BẬC 1: QUÁN QUÂN (Ở GIỮA - CAO NHẤT & TỎA SÁNG) -->
        <div class="podium-col col-first {{ $first && $first['is_me'] ? 'col-me' : '' }}">
            @if($first)
                <div class="podium-card-top card-gold">
                    <div class="crown-orb">👑</div>
                    <div class="rank-badge-pill pill-gold">
                        <span>🥇 QUÁN QUÂN</span>
                    </div>
                    <div class="podium-avatar avatar-gold">
                        {{ mb_substr($first['name'], 0, 1) }}
                    </div>
                    <b class="podium-name" style="font-size: 16.5px;">
                        {{ $first['name'] }}
                        @if($first['is_me']) <span class="tag-me">BẠN</span> @endif
                    </b>
                    <span class="podium-class">🏫 {{ $first['classroom_name'] }}</span>
                    <div class="podium-score score-gold">
                        {{ number_format($first['weekly_score']) }} <small>điểm</small>
                    </div>
                    <div class="podium-footer-meta meta-gold">
                        <span>🎯 {{ $first['passed_count'] }} bài đạt</span>
                        <span>⭐ {{ number_format($first['reward_stars']) }}</span>
                    </div>
                </div>
                <div class="podium-step-block step-gold">
                    <div class="step-face-front">
                        <span class="step-num">1</span>
                        <small class="step-title">QUÁN QUÂN</small>
                    </div>
                </div>
            @else
                <div class="podium-card-top card-placeholder">
                    <div class="placeholder-icon">👑</div>
                    <b class="placeholder-text">Đang chờ Quán Quân!</b>
                    <span class="placeholder-hint">Chinh phục 1000 điểm để giữ ngôi đầu</span>
                </div>
                <div class="podium-step-block step-gold">
                    <div class="step-face-front">
                        <span class="step-num">1</span>
                    </div>
                </div>
            @endif
        </div>

        <!-- BẬC 3: HẠNG BA (BÊN PHẢI) -->
        <div class="podium-col col-third {{ $third && $third['is_me'] ? 'col-me' : '' }}">
            @if($third)
                <div class="podium-card-top card-bronze">
                    <div class="rank-badge-pill pill-bronze">
                        <span>🥉 HẠNG BA</span>
                    </div>
                    <div class="podium-avatar avatar-bronze">
                        {{ mb_substr($third['name'], 0, 1) }}
                    </div>
                    <b class="podium-name">
                        {{ $third['name'] }}
                        @if($third['is_me']) <span class="tag-me">BẠN</span> @endif
                    </b>
                    <span class="podium-class">🏫 {{ $third['classroom_name'] }}</span>
                    <div class="podium-score score-bronze">
                        {{ number_format($third['weekly_score']) }} <small>điểm</small>
                    </div>
                    <div class="podium-footer-meta">
                        <span>🎯 {{ $third['passed_count'] }} bài đạt</span>
                        <span>⭐ {{ number_format($third['reward_stars']) }}</span>
                    </div>
                </div>
                <div class="podium-step-block step-bronze">
                    <div class="step-face-front">
                        <span class="step-num">3</span>
                        <small class="step-title">HẠNG BA</small>
                    </div>
                </div>
            @else
                <div class="podium-card-top card-placeholder">
                    <div class="placeholder-icon">⚡</div>
                    <b class="placeholder-text">Đang chờ Hạng Ba!</b>
                    <span class="placeholder-hint">Luyện thi để bước lên bục vinh quang</span>
                </div>
                <div class="podium-step-block step-bronze">
                    <div class="step-face-front">
                        <span class="step-num">3</span>
                    </div>
                </div>
            @endif
        </div>

    </div>

    <!-- BẢNG DANH SÁCH TOP TIẾP THEO (HẠNG 4 – 10) GỌN GÀNG, ĐẸP MẮT -->
    @if($rankingList->isNotEmpty())
        <div class="next-ranks-section">
            <div class="next-ranks-header">
                <span>⚡ TOP HIỆP SĨ TIẾP THEO (HẠNG 4 – 10)</span>
                <small style="color: #64748b; font-weight: 700;">Đua tranh từng điểm số</small>
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
                                    <b>{{ $st['name'] }}</b>
                                    @if($st['is_me'])
                                        <span class="tag-me">BẠN</span>
                                    @endif
                                    <span class="class-chip">🏫 {{ $st['classroom_name'] }}</span>
                                </div>
                                <div class="rank-row-sub">
                                    <span>🎯 Đạt chuẩn {{ $st['passed_count'] }}/{{ $st['tests_count'] }} bài</span>
                                    <span>•</span>
                                    <span>⭐ {{ number_format($st['reward_stars']) }} Sao</span>
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
        <div class="empty-leaderboard-card" style="text-align:center; padding: 36px 20px; background: #f8fafc; border-radius: 20px; border: 2px dashed #cbd5e1; margin-top: 16px;">
            <div style="font-size: 42px; margin-bottom: 8px;">🎮</div>
            <h4 style="font-family: 'Fredoka', cursive; font-size: 18px; color: #1e293b; margin-bottom: 6px;">VÒNG THI ĐUA MỚI ĐÃ KHỞI TRANH!</h4>
            <p style="font-size: 13.5px; color: #64748b; margin-bottom: 18px;">Chưa có bạn nào ghi danh điểm số ở khối này tuần này. Hãy là người đầu tiên bứt phá ngôi vị Quán quân!</p>
            <a href="{{ route('programs') }}" class="btn-3d-tactile" style="background: linear-gradient(180deg, #3b82f6, #1d4ed8); color: #fff; text-decoration: none; padding: 10px 24px;">
                🚀 Bắt Đầu Luyện Thi Ngay
            </a>
        </div>
    @endif

</div>
