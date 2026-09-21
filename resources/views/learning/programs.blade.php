{{-- Danh sách chương trình và khối học; dữ liệu được chuẩn bị trong LearningController::programs(). --}}
@extends('layouts.app')
@section('title', 'Học và luyện IC3 — IC3 Digital Adventure')

@section('content')
<div class="adventure-world-wrapper">
    <!-- Center Capsule Header -->
    <div class="mission-chooser-container">
        <div class="choose-mission-capsule">
            <span>📚</span> KHO BÀI HỌC VÀ THỬ THÁCH SỐ
        </div>
    </div>

    <!-- 3 Grand Colorful Adventure Cards per Row -->
    <div class="adventure-cards-grid">
        @php
            $gradeThemes = [
                1 => ['gradient' => 'linear-gradient(180deg, #f59e0b 0%, #d97706 100%)', 'text_color' => '#b45309', 'icon' => '🌟', 'label' => 'Khởi đầu số'],
                2 => ['gradient' => 'linear-gradient(180deg, #14b8a6 0%, #0f766e 100%)', 'text_color' => '#0f766e', 'icon' => '🌱', 'label' => 'Khám phá số'],
                3 => ['gradient' => 'linear-gradient(180deg, #78da78 0%, #48be48 100%)', 'text_color' => '#1e701e', 'icon' => '📖', 'label' => 'Spark Level 1'],
                4 => ['gradient' => 'linear-gradient(180deg, #6ec6ff 0%, #309df5 100%)', 'text_color' => '#12579b', 'icon' => '🎧', 'label' => 'Spark Level 2'],
                5 => ['gradient' => 'linear-gradient(180deg, #c780fa 0%, #9b4ced 100%)', 'text_color' => '#5a1999', 'icon' => '✏️', 'label' => 'Spark Level 3'],
                6 => ['gradient' => 'linear-gradient(180deg, #f43f5e 0%, #e11d48 100%)', 'text_color' => '#be123c', 'icon' => '🚀', 'label' => 'Bứt phá số'],
                7 => ['gradient' => 'linear-gradient(180deg, #6366f1 0%, #4338ca 100%)', 'text_color' => '#3730a3', 'icon' => '⚡', 'label' => 'Chinh phục số'],
                8 => ['gradient' => 'linear-gradient(180deg, #d946ef 0%, #c026d3 100%)', 'text_color' => '#a21caf', 'icon' => '💎', 'label' => 'Chuyên gia số'],
                9 => ['gradient' => 'linear-gradient(180deg, #f97316 0%, #ea580c 100%)', 'text_color' => '#c2410c', 'icon' => '🎯', 'label' => 'Làm chủ số'],
            ];
        @endphp

        @foreach($program->levels->sortBy('grade') as $level)
            @php
                $theme = $gradeThemes[$level->grade] ?? $gradeThemes[(($level->grade - 1) % count($gradeThemes)) + 1] ?? $gradeThemes[3];
                $hasAccess = auth()->user()->canAccessLevel($level);
                $topicCount = $level->topics->count();
                $testCount = $level->topics->sum(fn($t) => $t->tests->count());
                $desc = $topicCount > 0 ? "{$topicCount} chủ đề · {$testCount} bài luyện chuẩn IC3 GS6" : "Đang cập nhật nội dung bài học";
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
                        <span>⚡</span> <b>{{ $stars }}</b>
                    @else
                        <span style="color: #ffffff; font-size: 12px; font-weight: 750; background: rgba(0,0,0,0.2); padding: 3px 10px; border-radius: 999px;">Chưa được cấp quyền học</span>
                    @endif
                </div>

                @if($hasAccess)
                    <a href="{{ route('levels.show', $level) }}" class="adv-card-btn" style="color: {{ $theme['text_color'] }}; background: #ffffff;">
                        VÀO BẢN ĐỒ HỌC TẬP <b>→</b>
                    </a>
                @else
                    <button type="button" class="adv-card-btn" style="background: rgba(15,23,42,0.7); color: #ffffff; border-color: rgba(255,255,255,0.3); cursor: not-allowed; opacity: 0.9;" onclick="alert('Khối học này đang bị khóa. Hãy liên hệ Giáo viên phụ trách để được cấp quyền mở khóa nhé!')">
                        🔒 Đang khóa (Liên hệ GV)
                    </button>
                @endif
            </div>
        @endforeach
    </div>
</div>

<style>
    /* Full Page Game Adventure Theme for Programs page */
    .adventure-world-wrapper {
        min-height: calc(100vh - 86px);
        padding: 35px 24px 70px;
        background: transparent;
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

    .mission-chooser-container {
        margin-bottom: 30px;
    }
    .choose-mission-capsule {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        padding: 12px 34px;
        background: linear-gradient(180deg, #1b4777, #102e52);
        border: 3.5px solid #6ed7ff;
        border-radius: 999px;
        color: #ffffff;
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 22px;
        font-weight: 700;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4), inset 0 -3px 0 #07192e;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
    }
    .choose-mission-capsule span { font-size: 24px; }

    /* 3 Cards Per Row Clean Grand Layout */
    .adventure-cards-grid {
        width: min(1180px, 100%);
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 26px;
    }

    .adventure-card {
        border-radius: 30px;
        padding: 30px 24px;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        border: 4.5px solid #ffffff;
        box-shadow: 0 18px 40px rgba(0, 0, 0, 0.25), inset 0 -8px 0 rgba(0, 0, 0, 0.15);
        transition: all 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    .adventure-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.35), inset 0 -8px 0 rgba(0, 0, 0, 0.15);
    }

    .adv-card-title {
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 23px;
        font-weight: 700;
        margin-bottom: 14px;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }
    .adv-icon-orb {
        width: 110px;
        height: 110px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.35);
        border: 4px solid #ffffff;
        display: grid;
        place-items: center;
        margin: 8px 0 18px;
        box-shadow: inset 0 -4px 0 rgba(0, 0, 0, 0.15), 0 8px 18px rgba(0, 0, 0, 0.15);
    }
    .adv-icon-emoji { font-size: 52px; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.2)); }
    .adv-card-desc { font-size: 14px; line-height: 1.5; font-weight: 700; margin-bottom: 18px; color: #fff; text-shadow: 0 1px 2px rgba(0,0,0,0.2); }
    .adv-card-stars { display: inline-flex; align-items: center; gap: 6px; padding: 6px 16px; background: rgba(0, 0, 0, 0.18); border: 1.5px solid rgba(255, 255, 255, 0.4); border-radius: 999px; font-size: 13px; font-weight: 900; margin-bottom: 20px; }
    .adv-card-btn { width: 100%; padding: 14px 20px; background: #ffffff; border: 3px solid #ffffff; border-radius: 18px; font-family: inherit; font-size: 15px; font-weight: 1000; text-decoration: none; box-shadow: 0 6px 0 rgba(0, 0, 0, 0.2), 0 10px 18px rgba(0, 0, 0, 0.15); transition: all 0.18s; display: inline-block; }
    .adv-card-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 0 rgba(0, 0, 0, 0.2), 0 14px 22px rgba(0, 0, 0, 0.2); }

    @media (max-width: 960px) {
        .adventure-cards-grid { grid-template-columns: repeat(2, 1fr); gap: 20px; }
    }
    @media (max-width: 640px) {
        .adventure-cards-grid { grid-template-columns: 1fr; max-width: 480px; }
    }
</style>
@endsection
