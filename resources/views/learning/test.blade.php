{{-- Trang giới thiệu bài luyện trước khi mở màn hình làm bài ở learning/launch. --}}
@extends('layouts.app')
@section('title', $practiceTest->name.' — '.$practiceTest->topic->name.' (Khối '.$practiceTest->topic->level->grade.')')

@section('content')
<style>
    .mission-stage-bright-wrap {
        width: min(1200px, 96%);
        margin: 20px auto 40px;
    }

    /* Bright Navigation Bar */
    .bright-nav-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    .bright-back-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: #ffffff;
        border: 3px solid #38bdf8;
        border-radius: 999px;
        color: #0369a1;
        font-size: 13.5px;
        font-weight: 1000;
        text-decoration: none;
        box-shadow: 0 4px 0 #0284c7, 0 8px 16px rgba(2, 132, 199, 0.15);
        transition: all 0.15s;
    }
    .bright-back-btn:hover {
        transform: translateY(-2px);
        background: #f0f9ff;
        box-shadow: 0 6px 0 #0284c7, 0 12px 20px rgba(2, 132, 199, 0.25);
    }
    .bright-breadcrumbs {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 18px;
        background: #ffffff;
        border: 2.5px solid #e2e8f0;
        border-radius: 999px;
        color: #64748b;
        font-size: 12.5px;
        font-weight: 850;
        box-shadow: 0 3px 6px rgba(0, 0, 0, 0.05);
    }
    .bright-breadcrumbs a {
        color: #0284c7;
        text-decoration: none;
        transition: color 0.15s;
    }
    .bright-breadcrumbs a:hover {
        color: #0369a1;
        text-decoration: underline;
    }

    /* Grid Stage Layout */
    .bright-stage-grid {
        display: grid;
        grid-template-columns: 1.35fr 0.85fr;
        gap: 24px;
        align-items: start;
    }

    /* Main Briefing 3D White Card */
    .bright-card {
        background: #ffffff;
        border-radius: 28px;
        box-shadow: 0 16px 35px rgba(0, 0, 0, 0.12), inset 0 -6px 0 rgba(0, 0, 0, 0.06);
        overflow: hidden;
        position: relative;
    }
    .bright-main-card {
        padding: 34px;
        border: 4px solid #38bdf8;
        box-shadow: 0 12px 0 #0284c7, 0 20px 40px rgba(2, 132, 199, 0.15);
    }

    .bright-badge-row {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 18px;
    }
    .bright-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 12px;
        font-size: 11.5px;
        font-weight: 900;
        letter-spacing: 0.4px;
        border: 2px solid #ffffff;
        box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
    }
    .pill-grade { background: linear-gradient(135deg, #ffcf33, #ff9b26); color: #4a2700; }
    .pill-topic { background: linear-gradient(135deg, #38bdf8, #0284c7); color: #ffffff; }
    .pill-diff { background: linear-gradient(135deg, #34d399, #059669); color: #ffffff; }

    .bright-stage-header { margin: 10px 0 16px; }
    .stage-label-chip {
        display: inline-block;
        padding: 4px 12px;
        background: #e0f2fe;
        border: 2px solid #38bdf8;
        border-radius: 10px;
        color: #0369a1;
        font-size: 11.5px;
        font-weight: 1000;
        letter-spacing: 1px;
        margin-bottom: 10px;
    }
    .bright-stage-header h1 {
        margin: 0;
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 34px;
        color: #0f172a;
        font-weight: 700;
        line-height: 1.2;
    }
    .bright-stage-header h2 {
        margin: 6px 0 0;
        font-size: 20px;
        color: #0284c7;
        font-weight: 850;
    }

    .bright-rule-box {
        margin: 14px 0 18px;
        padding: 14px 18px;
        background: #fefce8;
        border: 2.5px solid #fef08a;
        border-left: 5px solid #eab308;
        border-radius: 14px;
        color: #713f12;
        font-size: 13.5px;
        font-weight: 750;
        line-height: 1.5;
    }
    .bright-desc {
        font-size: 14.5px;
        line-height: 1.65;
        color: #475569;
        font-weight: 650;
        margin-bottom: 22px;
    }

    /* 4 Game Stats Cubes */
    .bright-stats-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 14px;
        margin: 22px 0;
    }
    .bright-stat-cube {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 16px 18px;
        border: 3.5px solid #ffffff;
        border-radius: 20px;
        color: #ffffff;
        box-shadow: inset 0 -4px 0 rgba(0, 0, 0, 0.15), 0 8px 18px rgba(0, 0, 0, 0.1);
        transition: transform 0.15s;
    }
    .bright-stat-cube:hover { transform: translateY(-3px); }
    .cube-time { background: linear-gradient(145deg, #38bdf8, #0284c7); }
    .cube-questions { background: linear-gradient(145deg, #34d399, #059669); }
    .cube-target { background: linear-gradient(145deg, #fbbf24, #d97706); }
    .cube-reward { background: linear-gradient(145deg, #c084fc, #7e22ce); }

    .cube-icon {
        width: 48px;
        height: 48px;
        display: grid;
        place-items: center;
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.25);
        border: 2px solid #ffffff;
        font-size: 24px;
        flex-shrink: 0;
    }
    .cube-info small, .cube-info b, .cube-info span { display: block; }
    .cube-info small { font-size: 9.5px; font-weight: 1000; letter-spacing: 0.8px; color: #fef08a; }
    .cube-info b { font-size: 16.5px; font-weight: 1000; margin: 3px 0 2px; }
    .cube-info span { font-size: 11.5px; color: #ffffff; opacity: 0.95; font-weight: 750; }

    /* Action Start Button */
    .bright-action-row {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 26px;
    }
    .bright-start-btn {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 16px 26px;
        border: 4px solid #ffffff;
        border-radius: 22px;
        background: linear-gradient(180deg, #ffc048 0%, #ff9f1a 100%);
        color: #4a2700;
        box-shadow: inset 0 -6px 0 #b35600, 0 8px 0 #0284c7, 0 16px 30px rgba(255, 159, 26, 0.4);
        cursor: pointer;
        transition: all 0.15s;
        width: 100%;
        text-decoration: none;
    }
    .bright-start-btn:hover {
        transform: translateY(-3px);
        background: linear-gradient(180deg, #ffd068 0%, #ffa933 100%);
        box-shadow: inset 0 -6px 0 #b35600, 0 11px 0 #0284c7, 0 20px 35px rgba(255, 159, 26, 0.55);
    }
    .bright-start-btn .btn-icon {
        font-size: 34px;
        animation: rocket-bounce 1.5s infinite ease-in-out;
    }
    .bright-start-btn .btn-text b {
        display: block;
        font-size: 19px;
        font-weight: 1000;
        letter-spacing: 0.5px;
    }
    .bright-start-btn .btn-text small {
        display: block;
        font-size: 12px;
        font-weight: 800;
        color: #6f3300;
        margin-top: 2px;
    }
    .bright-start-btn .btn-arrow {
        width: 40px;
        height: 40px;
        display: grid;
        place-items: center;
        border-radius: 50%;
        background: #ffffff;
        color: #b35600;
        font-size: 22px;
        font-weight: 1000;
        box-shadow: 0 3px 0 #92400e;
    }

    /* Side Briefing 3D White Card */
    .bright-side-card {
        border: 4px solid #a855f7;
        box-shadow: 0 12px 0 #7e22ce, 0 20px 40px rgba(126, 34, 206, 0.15);
    }
    .side-art-box {
        height: 190px;
        position: relative;
        overflow: hidden;
        background: linear-gradient(145deg, #38bdf8, #a855f7);
    }
    .side-hero-pic {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center 30%;
    }
    .side-content-box {
        padding: 24px;
    }
    .side-content-box .kicker {
        font-size: 11.5px;
        font-weight: 900;
        color: #7e22ce;
        letter-spacing: 1px;
        display: block;
    }
    .side-content-box h3 {
        margin: 6px 0 16px;
        font-family: 'Fredoka', cursive, sans-serif;
        font-size: 24px;
        color: #0f172a;
        font-weight: 700;
    }
    .bright-mission-list {
        list-style: none;
        padding: 0;
        margin: 0 0 18px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .bright-mission-list li {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 12px 14px;
        background: #f8fafc;
        border: 2px solid #e2e8f0;
        border-radius: 16px;
    }
    .bright-mission-list li b { display: block; font-size: 13.5px; color: #0f172a; font-weight: 850; }
    .bright-mission-list li small { display: block; font-size: 11.5px; color: #64748b; margin-top: 2px; font-weight: 700; }
    .check-circle {
        width: 26px;
        height: 26px;
        display: grid;
        place-items: center;
        border-radius: 50%;
        background: #22c55e;
        color: #ffffff;
        font-size: 13px;
        font-weight: 1000;
        border: 2px solid #ffffff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
        flex-shrink: 0;
    }

    .bright-tip-box {
        padding: 14px 16px;
        background: #fefce8;
        border: 2px solid #fef08a;
        border-radius: 16px;
        color: #713f12;
    }
    .bright-tip-box b { font-size: 13px; font-weight: 900; }
    .bright-tip-box p { margin: 4px 0 0; font-size: 12.5px; line-height: 1.5; color: #854d0e; font-weight: 650; }

    @media (max-width: 980px) {
        .bright-stage-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 640px) {
        .bright-stats-grid { grid-template-columns: 1fr; }
        .bright-main-card { padding: 20px; }
    }
</style>

<div class="mission-stage-bright-wrap">
    <!-- Đường dẫn bản đồ Tone Sáng -->
    <div class="bright-nav-bar">
        <a class="bright-back-btn" href="{{ route('levels.show', $practiceTest->topic->level) }}">
            <span>←</span> Quay lại Bản đồ Khối {{ $practiceTest->topic->level->grade }}
        </a>
        <div class="bright-breadcrumbs">
            <a href="{{ route('home') }}">Trang chủ</a>
            <span>›</span>
            <a href="{{ route('levels.show', $practiceTest->topic->level) }}">Khối {{ $practiceTest->topic->level->grade }}</a>
            <span>›</span>
            <span>Chủ đề {{ $practiceTest->topic->position }}</span>
        </div>
    </div>

    <!-- Bảng nhiệm vụ chính phong cách Game 3D Tone Sáng -->
    <div class="bright-stage-grid">
        <!-- Khung thông tin thử thách chính -->
        <article class="bright-card bright-main-card">
            <div class="bright-badge-row">
                <span class="bright-pill pill-grade">
                    <i>★</i> KHỐI {{ $practiceTest->topic->level->grade }} · SPARK LEVEL {{ $practiceTest->topic->level->grade - 2 }}
                </span>
                <span class="bright-pill pill-topic">
                    <i>⚡</i> CHỦ ĐỀ {{ $practiceTest->topic->position }}: {{ mb_strtoupper($practiceTest->topic->name) }}
                </span>
                <span class="bright-pill pill-diff">
                    <i>🎯</i> {{ mb_strtoupper($practiceTest->difficulty) }}
                </span>
            </div>

            <div class="bright-stage-header">
                <span class="stage-label-chip">STAGE 0{{ $practiceTest->position }}</span>
                <h1>{{ $practiceTest->name }}</h1>
                <h2>{{ $practiceTest->topic->name }}</h2>
            </div>

            <div class="bright-rule-box">
                💡 <b>Quy định IC3:</b> Với mục đích rèn luyện kĩ năng, các bài Test đều cần đạt <b>điểm tối đa (1000/1000)</b> mới được tính là hoàn thành.
            </div>

            <p class="bright-desc">{{ $practiceTest->topic->description }}</p>

            <!-- 4 Thẻ chỉ số Game Stats Màu Sắc Rực Rỡ -->
            <div class="bright-stats-grid">
                <div class="bright-stat-cube cube-time">
                    <div class="cube-icon">⏱️</div>
                    <div class="cube-info">
                        <small>THỜI GIAN</small>
                        <b>{{ $practiceTest->duration_minutes ? $practiceTest->duration_minutes.' phút' : 'Không giới hạn' }}</b>
                        <span>Thong thả suy nghĩ</span>
                    </div>
                </div>

                <div class="bright-stat-cube cube-questions">
                    <div class="cube-icon">📋</div>
                    <div class="cube-info">
                        <small>SỐ CÂU HỎI</small>
                        <b>{{ $practiceTest->question_count }} câu hỏi</b>
                        <span>Đầy đủ dạng bài</span>
                    </div>
                </div>

                <div class="bright-stat-cube cube-target">
                    <div class="cube-icon">🏆</div>
                    <div class="cube-info">
                        <small>ĐIỂM ĐẠT</small>
                        <b>{{ $practiceTest->pass_score }} / {{ $practiceTest->max_score }}</b>
                        <span>Mục tiêu điểm tối đa</span>
                    </div>
                </div>

                <div class="bright-stat-cube cube-reward">
                    <div class="cube-icon">💎</div>
                    <div class="cube-info">
                        <small>PHẦN THƯỞNG</small>
                        <b>+100 Sao Vàng</b>
                        <span>Vé vào Khu Trò Chơi</span>
                    </div>
                </div>
            </div>

            <!-- Nút hành động Bắt đầu làm bài -->
            <div class="bright-action-row">
                <button class="bright-start-btn" data-start-test>
                    <span class="btn-icon">🚀</span>
                    <span class="btn-text">
                        <b>BẮT ĐẦU VƯỢT THỬ THÁCH</b>
                        <small>Vào làm bài ngay · Tự động mở khóa</small>
                    </span>
                    <span class="btn-arrow">→</span>
                </button>

                <div style="font-size: 12.5px; color: #64748b; font-weight: 750; padding: 4px 6px;">
                    🛡️ Hệ thống tự động mở khóa & lưu kết quả của em
                </div>
            </div>
        </article>

        <!-- Khung hướng dẫn & Nhân vật hiệp sĩ số -->
        <aside class="bright-card bright-side-card">
            <div class="side-art-box">
                <img src="{{ asset('images/ic3-quest-hero.png') }}" alt="IC3 Quest Heroes" class="side-hero-pic">
            </div>

            <div class="side-content-box">
                <span class="kicker">SỨ MỆNH HIỆP SĨ SỐ</span>
                <h3>Chinh phục thử thách</h3>

                <ul class="bright-mission-list">
                    <li>
                        <span class="check-circle">✓</span>
                        <div>
                            <b>Đọc kỹ từng câu hỏi</b>
                            <small>Quan sát hình ảnh và tình huống thật kỹ</small>
                        </div>
                    </li>
                    <li>
                        <span class="check-circle">✓</span>
                        <div>
                            <b>Chọn đáp án phù hợp nhất</b>
                            <small>Bấm chọn thẻ đáp án tương ứng</small>
                        </div>
                    </li>
                    <li>
                        <span class="check-circle">✓</span>
                        <div>
                            <b>Đạt tối thiểu {{ $practiceTest->pass_score }} điểm</b>
                            <small>Nhận huy hiệu và mở khóa trò chơi</small>
                        </div>
                    </li>
                </ul>

                <div class="bright-tip-box">
                    <b>💡 Bí kíp chiến thắng:</b>
                    <p>Bình tĩnh suy nghĩ! Em có thể làm bài nhiều lần để đạt điểm số cao nhất.</p>
                </div>
            </div>
        </aside>
    </div>
</div>

<!-- Modal Chuẩn bị vào phòng thi phong cách Game Arcade Tone Sáng Tuyệt Đối -->
<div class="modal" id="practice-test-modal" data-test-modal aria-hidden="true" style="position:fixed;inset:0;z-index:9999999;display:none;align-items:center;justify-content:center;padding:20px;">
    <div class="modal-backdrop" data-modal-close style="position:fixed;inset:0;background:rgba(4,14,32,0.75);backdrop-filter:blur(12px);z-index:9999998;cursor:pointer;"></div>
    <section class="modal-card arcade-modal" role="dialog" aria-modal="true" aria-labelledby="modal-title" style="position:relative;z-index:9999999;background:#ffffff!important;border:4px solid #ffffff!important;box-shadow:0 30px 85px rgba(0,0,0,0.5),0 0 45px rgba(0,242,254,0.3)!important;color:#0f172a!important;border-radius:32px!important;padding:40px 34px!important;max-width:520px;width:100%;text-align:center;">
        <button class="modal-close-btn" data-modal-close aria-label="Đóng" style="position:absolute;right:18px;top:18px;width:38px;height:38px;display:grid;place-items:center;border:2px solid #e2e8f0;border-radius:50%;background:#f1f5f9;color:#64748b;font-size:16px;font-weight:900;cursor:pointer;">✕</button>
        <div class="modal-glow-ring" style="width:86px;height:86px;margin:0 auto 16px;border-radius:50%;background:linear-gradient(135deg,#fef08a,#facc15);border:3.5px solid #ffffff;display:grid;place-items:center;box-shadow:0 10px 25px rgba(234,179,8,0.35),0 0 25px rgba(250,204,21,0.5);">
            <div class="arcade-badge" style="font-size:42px;">🎮</div>
        </div>
        <span class="arcade-kicker" style="font-size:12px;font-weight:900;color:#d97706;letter-spacing:1.5px;display:block;">SẴN SÀNG CHƯA NÀO?</span>
        <h2 id="modal-title" style="margin:8px 0 14px;font-family:'Fredoka',cursive,sans-serif!important;font-size:28px!important;color:#0f172a!important;font-weight:700!important;text-shadow:none!important;">Bắt Đầu Thử Thách!</h2>
        <div class="modal-test-preview" style="padding:16px;background:#f8fafc;border:2px solid #e2e8f0;border-radius:20px;margin:16px 0;">
            <span class="preview-grade" style="display:inline-block;padding:4px 12px;background:linear-gradient(135deg,#0ea5e9,#0284c7);color:#ffffff;border-radius:999px;font-size:11px;font-weight:900;margin-bottom:6px;">Khối {{ $practiceTest->topic->level->grade }}</span>
            <b style="display:block;font-size:18px;color:#0f172a;font-weight:900;">{{ $practiceTest->topic->name }}</b>
            <small style="display:block;font-size:13px;color:#64748b;margin-top:4px;font-weight:750;">{{ $practiceTest->name }} · {{ $practiceTest->question_count }} câu hỏi chuẩn IIG</small>
        </div>
        <p class="modal-desc" style="font-size:14px;line-height:1.6;color:#475569!important;margin:14px 0 24px!important;font-weight:700;">
            Bé hãy đọc thật kỹ từng câu hỏi và chọn đáp án chính xác nhất nhé. Chúc bé hoàn thành xuất sắc 1000/1000 điểm!
        </p>
        <div class="modal-arcade-actions" style="display:flex;gap:12px;justify-content:center;">
            <button class="button modal-cancel" data-modal-close style="background:#f1f5f9!important;border:2px solid #cbd5e1!important;color:#475569!important;border-radius:16px!important;padding:14px 22px!important;font-size:14px!important;font-weight:850!important;cursor:pointer;">Để sau nhé</button>
            <a class="button modal-launch notranslate" translate="no" href="{{ route('tests.launch', $practiceTest) }}" style="background:linear-gradient(180deg,#ffc048,#ff9f1a)!important;border:3px solid #ffffff!important;color:#4a2700!important;border-radius:16px!important;padding:14px 28px!important;font-size:15px!important;font-weight:1000!important;box-shadow:0 5px 0 #b35600,0 12px 25px rgba(255,159,26,0.45)!important;display:inline-flex;align-items:center;gap:8px;text-decoration:none;">
                <span>🚀</span> BẮT ĐẦU LÀM BÀI
            </a>
        </div>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const testModal = document.getElementById('practice-test-modal');
        const startBtn = document.querySelector('[data-start-test]');
        const closeBtns = document.querySelectorAll('[data-modal-close]');

        function openModal() {
            if (testModal) {
                testModal.style.display = 'flex';
                testModal.setAttribute('aria-hidden', 'false');
            }
        }

        function closeModal() {
            if (testModal) {
                testModal.style.display = 'none';
                testModal.setAttribute('aria-hidden', 'true');
            }
        }

        if (startBtn) {
            startBtn.addEventListener('click', function(e) {
                e.preventDefault();
                openModal();
            });
        }

        closeBtns.forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                closeModal();
            });
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeModal();
        });
    });
</script>
@endsection
