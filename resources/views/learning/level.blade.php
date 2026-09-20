{{-- Các chủ đề và bài luyện của khối đang chọn. Tiến độ ở đây đếm bài đạt 1000 điểm. --}}
@extends('layouts.app')
@section('title', $level->name.' — IC3 Adventure')

@section('content')
<div class="adventure-world-wrapper">
    <div class="page-wrap" style="width: min(1200px, 100%);">
        
        @php
            $levelTestIds = $level->topics->flatMap->tests->pluck('id');
            $totalLevelTests = $levelTestIds->count();
            $completedLevelTests = auth()->check() ? auth()->user()->attempts()->whereIn('practice_test_id', $levelTestIds)->where('score', '>=', 1000)->distinct('practice_test_id')->count('practice_test_id') : 0;
            $levelProgressPercent = $totalLevelTests > 0 ? round(($completedLevelTests / $totalLevelTests) * 100) : 0;
        @endphp

        <!-- Header bản đồ cấp độ với tên lửa 3D -->
        <section class="level-hero-banner" style="margin-bottom: 25px;">
            <div class="hero-content">
                <div class="hero-nav-row">
                    <a class="back-link-btn" href="{{ route('programs') }}">← Kho bài học</a>
                    <span class="eyebrow-badge">⚡ IC3 GS6 · KHỐI {{ $level->grade }} · SPARK LEVEL {{ $level->grade - 2 }}</span>
                </div>
                <h1>{{ str($level->name)->before('—') }}</h1>
                <p>Khám phá toàn bộ 7 chủ đề kỹ năng số chuẩn quốc tế IC3 Spark GS6 dành cho học sinh Khối {{ $level->grade }}!</p>
            </div>
            <div class="level-rocket-stat">
                <div class="rocket-img-wrap">
                    <span class="rocket-orb">🚀</span>
                </div>
                <div class="stat-progress-box">
                    <div class="stat-progress-head">
                        <span>Tiến độ cấp độ</span>
                        <b>{{ $levelProgressPercent }}%</b>
                    </div>
                    <div class="progress-track"><i style="width: {{ $levelProgressPercent }}%;"></i></div>
                    <small>Đã hoàn thành {{ $completedLevelTests }}/{{ $totalLevelTests }} bài luyện</small>
                </div>
            </div>
        </section>

        <!-- Khu vực danh sách 7 chủ đề - Thu gọn mặc định -->
        <section class="topic-map-section">
            <div class="topic-toolbar">
                <div class="toolbar-title">
                    <span>🗺️</span>
                    <div>
                        <h2>Bản Đồ 7 Chủ Đề Học Tập</h2>
                        <p>Bấm vào từng chủ đề bên dưới để mở danh sách các bài ôn luyện nhé!</p>
                    </div>
                </div>
                <div class="toolbar-actions">
                    <button class="btn-toggle-all btn-expand-all" onclick="toggleAllTopics(false)" title="Mở toàn bộ 7 chủ đề">
                        <span>⚡</span> Mở tất cả
                    </button>
                    <button class="btn-toggle-all btn-collapse-all" onclick="toggleAllTopics(true)" title="Thu gọn toàn bộ">
                        <span>🔒</span> Thu gọn
                    </button>
                    <label class="topic-search-box">
                        <span>🔍</span>
                        <input id="topic-search-input" oninput="filterTopics(this.value)" type="search" placeholder="Tìm chủ đề hoặc bài luyện...">
                    </label>
                </div>
            </div>

            @php
                $topicGradients = [
                    0 => ['color' => 'topic-green', 'icon' => '📗', 'bg' => 'linear-gradient(135deg, #10b981, #059669)', 'accent' => '#34d399'],
                    1 => ['color' => 'topic-blue', 'icon' => '💡', 'bg' => 'linear-gradient(135deg, #0ea5e9, #0284c7)', 'accent' => '#38bdf8'],
                    2 => ['color' => 'topic-purple', 'icon' => '📂', 'bg' => 'linear-gradient(135deg, #8b5cf6, #6d28d9)', 'accent' => '#a78bfa'],
                    3 => ['color' => 'topic-orange', 'icon' => '🎨', 'bg' => 'linear-gradient(135deg, #f97316, #c2410c)', 'accent' => '#fb923c'],
                    4 => ['color' => 'topic-pink', 'icon' => '✉️', 'bg' => 'linear-gradient(135deg, #ec4899, #be185d)', 'accent' => '#f472b6'],
                    5 => ['color' => 'topic-teal', 'icon' => '🛡️', 'bg' => 'linear-gradient(135deg, #14b8a6, #0f766e)', 'accent' => '#2dd4bf'],
                    6 => ['color' => 'topic-amber', 'icon' => '🚀', 'bg' => 'linear-gradient(135deg, #f59e0b, #b45309)', 'accent' => '#fbbf24'],
                ];
            @endphp

            <div class="topic-grid">
                @foreach($level->topics as $topic)
                @php
                    $theme = $topicGradients[$loop->index % 7];
                    $testCount = $topic->tests->count();
                @endphp
                <article class="topic-box {{ $theme['color'] }} collapsed" data-topic-card data-search="{{ str($topic->name.' '.$topic->tests->pluck('name')->join(' '))->lower() }}">
                    
                    <!-- Header của Chủ Đề (Bấm vào để Mở / Đóng) -->
                    <div class="topic-box-header" onclick="toggleTopic(this)" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();toggleTopic(this);}" role="button" tabindex="0" title="Bấm để xem danh sách bài luyện">
                        <div class="topic-icon-circle">
                            <span>{{ $theme['icon'] }}</span>
                        </div>

                        <div class="topic-header-text">
                            <span class="topic-kicker">CHỦ ĐỀ 0{{ $loop->iteration }}</span>
                            <h3 class="topic-title">{{ $topic->name }}</h3>
                            <p class="topic-desc">{{ $topic->description }}</p>
                        </div>

                        <div class="topic-toggle-area">
                            <span class="test-count-pill">
                                📚 {{ $testCount }} Bài luyện
                            </span>
                            <div class="topic-toggle-btn" aria-hidden="true">
                                <span class="arrow-icon">▾</span>
                            </div>
                        </div>
                    </div>

                    <!-- Phần thân chứa danh sách bài luyện (Ẩn khi collapsed, Hiện khi mở) -->
                    <div class="topic-box-body">
                        <div class="tests-list">
                            @foreach($topic->tests as $test)
                            <a class="test-action-card" href="{{ route('tests.show', $test) }}">
                                <div class="test-play-icon">
                                    <span>▶</span>
                                </div>
                                <div class="test-info-block">
                                    <b class="test-name">{{ $test->name }}</b>
                                    <div class="test-meta-row">
                                        <span>📝 {{ $test->question_count }} câu hỏi</span>
                                        <span>⏱️ {{ $test->duration_minutes ? $test->duration_minutes.' phút' : 'Không giới hạn' }}</span>
                                        <span class="diff-badge diff-{{ $test->difficulty }}">{{ $test->difficulty }}</span>
                                    </div>
                                </div>
                                <div class="test-start-btn">
                                    <span>Bắt đầu</span> <b>→</b>
                                </div>
                            </a>
                            @endforeach
                        </div>
                    </div>
                </article>
                @endforeach
            </div>
        </section>
    </div>
</div>

<style>
    /* Full Page Game Adventure Theme for Topic Map */
    .adventure-world-wrapper {
        min-height: calc(100vh - 86px);
        padding: 30px 20px 60px;
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
        background: radial-gradient(circle at 50% 30%, rgba(255, 255, 255, 0.15) 0%, rgba(0, 0, 0, 0.2) 100%);
        pointer-events: none;
    }
    .adventure-world-wrapper > * {
        position: relative;
        z-index: 2;
    }

    /* Level Hero Banner (Clean & Crisp White Card) */
    .level-hero-banner {
        background: #ffffff;
        border: 4px solid #ffffff;
        border-radius: 28px;
        padding: 28px 32px;
        box-shadow: 0 16px 45px rgba(0, 0, 0, 0.12);
        display: grid;
        grid-template-columns: 1fr 280px;
        gap: 28px;
        align-items: center;
    }
    .hero-nav-row {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 8px;
        flex-wrap: wrap;
    }
    .back-link-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 14px;
        background: #ffffff;
        border: 1.5px solid #cbd5e1;
        border-radius: 999px;
        color: #1e293b;
        font-size: 12px;
        font-weight: 900;
        text-decoration: none;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        transition: all 0.15s;
    }
    .back-link-btn:hover {
        background: #f1f5f9;
        border-color: #94a3b8;
    }
    .eyebrow-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 14px;
        background: linear-gradient(135deg, #fef08a, #facc15);
        border: 1.5px solid #eab308;
        border-radius: 999px;
        color: #713f12;
        font-size: 11.5px;
        font-weight: 1000;
        letter-spacing: 0.6px;
    }
    .hero-content h1 {
        font-family: 'Fredoka', cursive, 'Nunito', sans-serif;
        font-size: 32px;
        font-weight: 700;
        color: #0f172a;
        margin: 4px 0 6px;
        line-height: 1.2;
    }
    .hero-content p {
        font-size: 14.5px;
        color: #475569;
        font-weight: 750;
        margin: 0;
        line-height: 1.5;
    }

    .level-rocket-stat {
        background: #f8fafc;
        border: 2px solid #e2e8f0;
        border-radius: 22px;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        gap: 14px;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.04);
    }
    .rocket-orb { font-size: 34px; }
    .stat-progress-box {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .stat-progress-head {
        display: flex;
        justify-content: space-between;
        font-size: 12.5px;
        font-weight: 900;
        color: #0f172a;
    }
    .stat-progress-head b { color: #2563eb; font-size: 14px; }
    .progress-track {
        height: 8px;
        background: #e2e8f0;
        border-radius: 999px;
        overflow: hidden;
    }
    .progress-track i {
        display: block;
        height: 100%;
        background: linear-gradient(90deg, #3b82f6, #8b5cf6);
        border-radius: 999px;
    }
    .stat-progress-box small {
        font-size: 11px;
        color: #64748b;
        font-weight: 700;
    }

    /* Topic Toolbar (Clean & Crisp White Card) */
    .topic-toolbar {
        background: #ffffff;
        border: 3.5px solid #ffffff;
        border-radius: 24px;
        padding: 18px 24px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.1);
    }
    .toolbar-title {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .toolbar-title span { font-size: 32px; }
    .toolbar-title h2 {
        font-family: 'Fredoka', cursive, 'Nunito', sans-serif;
        font-size: 22px;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
    }
    .toolbar-title p {
        font-size: 13.5px;
        color: #64748b;
        font-weight: 750;
        margin: 2px 0 0;
    }

    .toolbar-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .btn-toggle-all {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        background: #ffffff;
        border: 2px solid #cbd5e1;
        border-radius: 999px;
        color: #1e293b;
        font-size: 13px;
        font-weight: 900;
        cursor: pointer;
        transition: all 0.15s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    .btn-toggle-all:hover {
        background: #f1f5f9;
        border-color: #3b82f6;
        color: #1d4ed8;
        transform: translateY(-2px);
    }

    .topic-search-box {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        background: #f1f5f9;
        border: 2px solid #cbd5e1;
        border-radius: 999px;
        width: 260px;
        max-width: 100%;
    }
    .topic-search-box input {
        border: none;
        background: transparent;
        font-size: 13.5px;
        font-weight: 750;
        color: #0f172a;
        width: 100%;
        outline: none;
    }

    /* 7 Topic Boxes Grid (2 columns) - INDEPENDENT HEIGHT ALIGN-ITEMS START */
    .topic-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 22px;
        align-items: start;
    }

    .topic-box {
        background: #ffffff;
        border: 3.5px solid #ffffff;
        border-radius: 28px;
        box-shadow: 0 14px 35px rgba(0, 0, 0, 0.12);
        overflow: hidden;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        position: relative;
    }
    .topic-box:hover {
        transform: translateY(-3px);
        box-shadow: 0 20px 45px rgba(0, 0, 0, 0.18);
    }

    /* Topic Header (Vibrant 3D Gradient Colors) */
    .topic-box-header {
        padding: 22px 24px;
        display: flex;
        align-items: center;
        gap: 16px;
        cursor: pointer;
        user-select: none;
        color: #ffffff;
        transition: filter 0.15s ease;
    }
    .topic-box-header:hover {
        filter: brightness(1.04);
    }

    .topic-green .topic-box-header { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
    .topic-blue .topic-box-header { background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); }
    .topic-purple .topic-box-header { background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); }
    .topic-orange .topic-box-header { background: linear-gradient(135deg, #f97316 0%, #c2410c 100%); }
    .topic-pink .topic-box-header { background: linear-gradient(135deg, #ec4899 0%, #be185d 100%); }
    .topic-teal .topic-box-header { background: linear-gradient(135deg, #14b8a6 0%, #0f766e 100%); }
    .topic-amber .topic-box-header { background: linear-gradient(135deg, #f59e0b 0%, #b45309 100%); }

    .topic-icon-circle {
        width: 56px;
        height: 56px;
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.25);
        border: 2.5px solid #ffffff;
        display: grid;
        place-items: center;
        font-size: 28px;
        flex-shrink: 0;
        box-shadow: inset 0 -2px 0 rgba(0, 0, 0, 0.15);
    }
    .topic-header-text {
        flex: 1;
    }
    
    .topic-kicker {
        display: block;
        font-size: 11.5px;
        font-weight: 1000;
        letter-spacing: 0.8px;
        color: #ffe658;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.4);
        margin-bottom: 2px;
    }

    .topic-title {
        font-family: 'Fredoka', cursive, 'Nunito', sans-serif;
        font-size: 22px;
        font-weight: 700;
        color: #ffffff;
        margin: 0 0 3px;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.35);
        line-height: 1.25;
    }
    .topic-desc {
        font-size: 13.5px;
        color: #ffffff;
        opacity: 0.98;
        font-weight: 750;
        margin: 0;
        line-height: 1.45;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
    }

    .topic-toggle-area {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
    }
    .test-count-pill {
        padding: 6px 14px;
        background: rgba(255, 255, 255, 0.25);
        border: 1.5px solid #ffffff;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 1000;
        color: #ffffff;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
    }
    .topic-toggle-btn {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: #ffffff;
        border: 2px solid #ffffff;
        display: grid;
        place-items: center;
        color: #0f172a;
        font-size: 18px;
        font-weight: 1000;
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
        transition: transform 0.25s ease;
        pointer-events: none;
    }

    /* Collapsed & Expanded Animation */
    .topic-box.collapsed .topic-box-body {
        display: none;
    }
    .topic-box:not(.collapsed) .topic-toggle-btn {
        transform: rotate(180deg);
    }

    /* Topic Body with Tests */
    .topic-box-body {
        padding: 18px 22px;
        background: #f8fafc;
        border-top: 2px solid #e2e8f0;
    }
    .tests-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .test-action-card {
        padding: 14px 18px;
        background: #ffffff;
        border: 2px solid #e2e8f0;
        border-radius: 18px;
        display: flex;
        align-items: center;
        gap: 14px;
        text-decoration: none;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        transition: transform 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .test-action-card:hover {
        transform: translateY(-2px);
        border-color: #3b82f6;
        box-shadow: 0 8px 20px rgba(37, 99, 235, 0.18);
    }
    .test-play-icon {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        color: #ffffff;
        display: grid;
        place-items: center;
        font-size: 16px;
        flex-shrink: 0;
        box-shadow: 0 3px 6px rgba(37, 99, 235, 0.3);
    }
    .test-info-block {
        flex: 1;
    }
    .test-name {
        display: block;
        font-size: 15.5px;
        font-weight: 900;
        color: #0f172a;
    }
    .test-meta-row {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 12px;
        color: #64748b;
        font-weight: 750;
        margin-top: 3px;
        flex-wrap: wrap;
    }
    .diff-badge {
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 900;
    }
    .diff-Cơ\ bản { background: #dcfce7; color: #166534; }
    .diff-Trung\ bình { background: #fef3c7; color: #b45309; }
    .diff-Nâng\ cao { background: #fee2e2; color: #b91c1c; }

    .test-start-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 16px;
        background: #eff6ff;
        border: 1.5px solid #bfdbfe;
        border-radius: 12px;
        color: #1d4ed8;
        font-size: 13px;
        font-weight: 1000;
        transition: all 0.15s;
    }
    .test-action-card:hover .test-start-btn {
        background: #1d4ed8;
        color: #ffffff;
        border-color: #1d4ed8;
    }

    @media (max-width: 960px) {
        .level-hero-banner {
            grid-template-columns: 1fr;
            gap: 18px;
        }
        .topic-grid {
            grid-template-columns: 1fr;
        }
        .topic-toolbar {
            flex-direction: column;
            align-items: stretch;
        }
        .topic-search-box {
            width: 100%;
        }
    }
</style>

<script>
    window.toggleTopic = function(header) {
        if (!header) return;
        const card = header.closest('.topic-box');
        if (card) {
            card.classList.toggle('collapsed');
        }
    };

    window.toggleAllTopics = function(collapse) {
        document.querySelectorAll('.topic-box').forEach(card => {
            if (collapse) {
                card.classList.add('collapsed');
            } else {
                card.classList.remove('collapsed');
            }
        });
    };

    window.filterTopics = function(query) {
        const q = (query || '').trim().toLocaleLowerCase('vi');
        document.querySelectorAll('.topic-box').forEach(card => {
            const searchData = (card.dataset.search || '').toLocaleLowerCase('vi');
            const isMatched = !q || searchData.includes(q);
            card.style.display = isMatched ? '' : 'none';
            if (q && isMatched) {
                card.classList.remove('collapsed');
            }
        });
    };
</script>
@endsection
