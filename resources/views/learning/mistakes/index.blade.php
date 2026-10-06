{{-- Sổ tay câu sai & Phòng ôn luyện phục thù của học sinh --}}
@extends('layouts.app')
@section('title', 'Sổ Tay Câu Sai & Phục Thù — IC3 Adventure')

@section('content')
<div class="adventure-world-wrapper">
    <div class="page-wrap" style="width: min(1140px, 100%);">
        <x-staff-preview-notice />


        <!-- 1. Hero Game Banner Nổi Bật Chuẩn Phong Cách IC3 Adventure -->
        <section class="mistake-hero-banner" style="margin-bottom: 22px;">
            <div class="mistake-hero-content">
                <div class="hero-tag-row">
                    <a class="back-link-btn" href="{{ route('home') }}">← Trang của em</a>
                    <span class="eyebrow-badge">🔥 PHÒNG ÔN TẬP PHỤC THÙ · IC3 MISTAKE NOTEBOOK</span>
                </div>
                <h1>Sổ Tay Câu Hỏi Cần Phục Thù</h1>
                <p>Mỗi lần làm sai là một cơ hội để trở nên mạnh mẽ hơn! Ôn luyện lại các câu hỏi này để sửa lỗi và nhận thêm thật nhiều Sao Vàng nhé!</p>
            </div>
            <div class="mistake-hero-action">
                @if($totalUnresolved > 0)
                <a class="btn-revenge-now" href="{{ route('mistakes.launch', ['grade' => $selectedGrade !== 'all' ? $selectedGrade : null]) }}">
                    <span>⚡</span> Làm Lại {{ $totalUnresolved }} Câu Sai Ngay <b>→</b>
                </a>
                @else
                <div class="all-clear-pod">
                    <span style="font-size: 28px;">🌟</span>
                    <div>
                        <b style="font-size: 15px; display: block; line-height: 1.2;">Tuyệt vời!</b>
                        <small style="font-size: 12px; color: #047857; font-weight: 700;">Không còn câu sai nào</small>
                    </div>
                </div>
                @endif
            </div>
        </section>

        <!-- 2. 3 Tinh Thể Năng Lượng Đa Sắc (Click Lọc Nhanh Tức Thì - Loại Bỏ Màu Trắng Nhàm Chán) -->
        <section class="stat-pods-grid" style="margin-bottom: 22px;">
            <!-- Pod 1: Cần Phục Thù (Đỏ Hồng Tươi Tắn) -->
            <a class="stat-pod pod-danger {{ $selectedStatus === 'unresolved' && $selectedRisk !== 'high_risk' ? 'is-active-pod' : '' }}" 
               href="{{ route('mistakes.index', array_merge(request()->query(), ['status' => 'unresolved', 'risk' => 'all'])) }}"
               title="Bấm để lọc các câu đang bị sai">
                <div class="pod-icon-orb orb-danger">
                    <span>❌</span>
                </div>
                <div class="pod-content">
                    <span class="pod-label">CẦN PHỤC THÙ</span>
                    <div class="pod-value value-danger">
                        <span>{{ $totalUnresolved }}</span>
                        <small class="pod-unit">câu</small>
                    </div>
                    <small class="pod-hint">Câu hỏi đang chờ em vượt qua</small>
                </div>
            </a>

            <!-- Pod 2: Báo Động Đỏ (Vàng Hổ Phách Cảnh Báo) -->
            <a class="stat-pod pod-warning {{ $selectedRisk === 'high_risk' ? 'is-active-pod' : '' }}" 
               href="{{ route('mistakes.index', array_merge(request()->query(), ['risk' => 'high_risk', 'status' => 'unresolved'])) }}"
               title="Bấm để lọc các câu sai từ 2 lần trở lên">
                <div class="pod-icon-orb orb-warning">
                    <span>🚨</span>
                </div>
                <div class="pod-content">
                    <span class="pod-label">BÁO ĐỘNG ĐỎ</span>
                    <div class="pod-value value-warning">
                        <span>{{ $highRiskCount }}</span>
                        <small class="pod-unit">câu</small>
                    </div>
                    <small class="pod-hint">Sai từ 2 lần trở lên (Cần ôn gấp)</small>
                </div>
            </a>

            <!-- Pod 3: Đã Vượt Qua (Xanh Ngọc Lục Bảo Thành Công) -->
            <a class="stat-pod pod-success {{ $selectedStatus === 'resolved' ? 'is-active-pod' : '' }}" 
               href="{{ route('mistakes.index', array_merge(request()->query(), ['status' => 'resolved', 'risk' => 'all'])) }}"
               title="Bấm để lọc các câu đã phục thù thành công">
                <div class="pod-icon-orb orb-success">
                    <span>✅</span>
                </div>
                <div class="pod-content">
                    <span class="pod-label">ĐÃ VƯỢT QUA</span>
                    <div class="pod-value value-success">
                        <span>{{ $resolvedCount }}</span>
                        <small class="pod-unit">câu</small>
                    </div>
                    <small class="pod-hint">Đã phục thù thành công xuất sắc</small>
                </div>
            </a>
        </section>

        <!-- 3. Thanh Bộ Lọc Tinh Giản, Rõ Ràng & Dễ Thao Tác -->
        <div class="filter-card-bar" style="margin-bottom: 22px;">
            <!-- Nhóm Khối Lớp -->
            <div class="filter-group">
                <span class="filter-group-label">🏫 Khối lớp:</span>
                <div class="filter-pill-cluster">
                    <a class="filter-pill {{ $selectedGrade === 'all' ? 'active' : '' }}" href="{{ route('mistakes.index', array_merge(request()->query(), ['grade' => 'all'])) }}">
                        Tất cả
                    </a>
                    @foreach($levels as $lvl)
                    <a class="filter-pill {{ (string)$selectedGrade === (string)$lvl->grade ? 'active' : '' }}" href="{{ route('mistakes.index', array_merge(request()->query(), ['grade' => $lvl->grade])) }}">
                        Khối {{ $lvl->grade }}
                    </a>
                    @endforeach
                </div>
            </div>

            <div class="filter-divider"></div>

            <!-- Nhóm Chủ đề: chỉ hiện các chủ đề của khối đang chọn -->
            <div class="filter-group">
                <span class="filter-group-label">📚 Chủ đề:</span>
                <select class="topic-select" aria-label="Chọn chủ đề" onchange="if (this.value) window.location.href = this.value;">
                    <option value="{{ route('mistakes.index', array_merge(request()->query(), ['topic' => 'all'])) }}" {{ $selectedTopic === 'all' ? 'selected' : '' }}>
                        Tất cả chủ đề ({{ $topicCounts->sum() }} câu)
                    </option>
                    @foreach($topics->groupBy(fn ($t) => $t->level?->grade) as $grade => $group)
                    <optgroup label="Khối {{ $grade }}">
                        @foreach($group as $topic)
                        <option value="{{ route('mistakes.index', array_merge(request()->query(), ['topic' => $topic->id])) }}" {{ (string)$selectedTopic === (string)$topic->id ? 'selected' : '' }}>
                            {{ $topic->name }} ({{ $topicCounts[$topic->id] ?? 0 }} câu)
                        </option>
                        @endforeach
                    </optgroup>
                    @endforeach
                </select>
            </div>

            <style>
                .topic-select { min-width: 280px; max-width: 100%; padding: 9px 14px; border-radius: 16px; border: 3px solid transparent; background: linear-gradient(#fff, #fff) padding-box, linear-gradient(90deg, #f59e0b, #ec4899, #8b5cf6) border-box; color: #4c1d95; font: inherit; font-weight: 800; font-size: 14px; box-shadow: 0 5px 0 rgba(139,92,246,0.35); cursor: pointer; }
                .topic-select:hover { transform: translateY(-1px); }
                .topic-select:focus { outline: 3px solid #c4b5fd; outline-offset: 2px; }
                .topic-select option { color: #1e1b4b; font-weight: 700; }
                .topic-select optgroup { color: #7c3aed; font-weight: 900; }
            </style>

            <div class="filter-divider"></div>

            <!-- Nhóm Trạng Thái -->
            <div class="filter-group">
                <span class="filter-group-label">🎯 Trạng thái:</span>
                <div class="filter-pill-cluster">
                    <a class="filter-pill {{ $selectedStatus === 'unresolved' && $selectedRisk !== 'high_risk' ? 'active' : '' }}" href="{{ route('mistakes.index', array_merge(request()->query(), ['status' => 'unresolved', 'risk' => 'all'])) }}">
                        Đang sai ({{ $totalUnresolved }})
                    </a>
                    <a class="filter-pill pill-risk {{ $selectedRisk === 'high_risk' ? 'active' : '' }}" href="{{ route('mistakes.index', array_merge(request()->query(), ['risk' => 'high_risk', 'status' => 'unresolved'])) }}">
                        🚨 Báo động đỏ ({{ $highRiskCount }})
                    </a>
                    <a class="filter-pill pill-resolved {{ $selectedStatus === 'resolved' ? 'active' : '' }}" href="{{ route('mistakes.index', array_merge(request()->query(), ['status' => 'resolved', 'risk' => 'all'])) }}">
                        Đã sửa ({{ $resolvedCount }})
                    </a>
                    <a class="filter-pill {{ $selectedStatus === 'all' && $selectedRisk !== 'high_risk' ? 'active' : '' }}" href="{{ route('mistakes.index', array_merge(request()->query(), ['status' => 'all', 'risk' => 'all'])) }}">
                        Tất cả ({{ $totalUnresolved + $resolvedCount }})
                    </a>
                </div>
            </div>
        </div>

        <!-- 4. Danh Sách Câu Hỏi Tinh Gọn - Tập Trung 100% Vào Chữ & Nút Bấm Chính -->
        @if($mistakes->isNotEmpty())
        <div class="mistakes-list">
            @include('learning.mistakes._items')

            @if($mistakes->hasMorePages())
            <div style="margin-top: 24px; text-align: center;">
                <button type="button" id="btn-load-more-mistakes" class="btn-revenge-now" data-next="{{ $mistakes->nextPageUrl() }}" style="display: inline-flex; cursor: pointer; border: none;">
                    <span>⬇️</span> <b>Xem thêm câu sai</b>
                </button>
            </div>
            @endif
        </div>
        @else
        <div class="empty-mistake-card">
            <div style="font-size: 56px; margin-bottom: 12px;">🎉</div>
            <h3 style="font-family: 'Fredoka', cursive; font-size: 22px; color: #0f172a; margin-bottom: 8px;">
                Không Có Câu Hỏi Nào Trong Mục Này!
            </h3>
            <p style="font-size: 14.5px; color: #64748b; max-width: 460px; margin: 0 auto 20px; font-weight: 700;">
                Em đang làm bài rất tốt hoặc đã khắc phục hết tất cả các câu hỏi bị sai rồi. Tiếp tục duy trì phong độ nhé!
            </p>
            <a class="btn-revenge-now" href="{{ route('programs') }}" style="display: inline-flex;">
                <span>🚀</span> Luyện Thêm Bài Học Mới
            </a>
        </div>
        @endif

    </div>
</div>

<script>
    // Bấm "Xem thêm": tải 30 câu sai tiếp theo và nối vào cuối danh sách, không tải lại trang
    (function () {
        const btn = document.getElementById('btn-load-more-mistakes');
        if (!btn) return;
        const list = document.querySelector('.mistakes-list');
        btn.addEventListener('click', async function () {
            const url = btn.dataset.next;
            if (!url || btn.disabled) return;
            btn.disabled = true;
            const label = btn.querySelector('b');
            const original = label.textContent;
            label.textContent = 'Đang tải...';
            try {
                const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const data = await res.json();
                btn.parentElement.insertAdjacentHTML('beforebegin', data.html);
                if (data.next_url) {
                    btn.dataset.next = data.next_url;
                    label.textContent = original;
                    btn.disabled = false;
                } else {
                    btn.parentElement.remove();
                }
            } catch (e) {
                label.textContent = 'Chưa tải được, bấm để thử lại';
                btn.disabled = false;
            }
        });
    })();
</script>

<style>
    /* Nền Bản Đồ Phiêu Lưu Thế Giới Game */
    .adventure-world-wrapper {
        min-height: calc(100vh - 86px);
        padding: 24px 20px 60px;
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
        background: radial-gradient(circle at 50% 30%, rgba(255, 255, 255, 0.12) 0%, rgba(0, 0, 0, 0.18) 100%);
        pointer-events: none;
    }
    .adventure-world-wrapper > * {
        position: relative;
        z-index: 2;
    }

    /* 1. Hero Game Banner */
    .mistake-hero-banner {
        background: #ffffff;
        border: 3.5px solid #ffffff;
        border-radius: 24px;
        padding: 22px 28px;
        box-shadow: 0 14px 34px rgba(0, 0, 0, 0.09), inset 0 -4px 0 rgba(0,0,0,0.04);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        flex-wrap: wrap;
    }
    .hero-tag-row {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 6px;
        flex-wrap: wrap;
    }
    .back-link-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 14px;
        background: #f8fafc;
        border: 1.5px solid #cbd5e1;
        border-radius: 999px;
        color: #1e293b;
        font-size: 12px;
        font-weight: 900;
        text-decoration: none;
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
        padding: 4px 12px;
        background: linear-gradient(135deg, #fef2f2, #fee2e2);
        border: 1.5px solid #fca5a5;
        border-radius: 999px;
        color: #dc2626;
        font-size: 11px;
        font-weight: 1000;
        letter-spacing: 0.5px;
    }
    .mistake-hero-content h1 {
        font-family: 'Fredoka', cursive;
        font-size: 26px;
        color: #0f172a;
        margin: 4px 0 6px;
    }
    .mistake-hero-content p {
        font-size: 14px;
        color: #64748b;
        margin: 0;
        max-width: 600px;
        font-weight: 700;
        line-height: 1.45;
    }

    .btn-revenge-now {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 13px 24px;
        border-radius: 18px;
        background: linear-gradient(135deg, #ef4444, #dc2626);
        border: 2.5px solid #ffffff;
        color: #ffffff;
        font-weight: 1000;
        font-size: 14.5px;
        text-decoration: none;
        box-shadow: 0 6px 18px rgba(239, 68, 68, 0.35);
        transition: all 0.15s;
        cursor: pointer;
    }
    .btn-revenge-now:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 24px rgba(239, 68, 68, 0.45);
    }
    .all-clear-pod {
        display: flex;
        align-items: center;
        gap: 12px;
        background: #ecfdf5;
        border: 2px solid #86efac;
        border-radius: 16px;
        padding: 10px 20px;
        color: #065f46;
    }

    /* 2. 3 Tinh Thể Năng Lượng Đa Sắc Rực Rỡ (Gamified 3D Energy Pods) */
    .stat-pods-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
    }
    @media (max-width: 768px) {
        .stat-pods-grid {
            grid-template-columns: 1fr;
        }
    }
    .stat-pod {
        border-radius: 22px;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        text-decoration: none;
        position: relative;
        transition: all 0.22s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    .stat-pod:hover {
        transform: translateY(-4px) scale(1.015);
    }
    .stat-pod.is-active-pod {
        transform: translateY(-3px) scale(1.02);
        outline: 3px solid #1e293b;
        outline-offset: 2px;
    }

    /* Pod 1: Đỏ Hồng (Cần Phục Thù) */
    .pod-danger {
        background: linear-gradient(135deg, #fff1f2 0%, #ffe4e6 45%, #fecdd3 100%);
        border: 3.5px solid #f43f5e;
        box-shadow: 0 12px 28px rgba(244, 63, 94, 0.22), inset 0 -4px 0 rgba(225, 29, 72, 0.18);
    }
    .pod-danger .pod-label { color: #9f1239; }
    .pod-danger .value-danger { color: #881337; }
    .pod-danger .pod-hint { color: #9f1239; }

    /* Pod 2: Vàng Hổ Phách (Báo Động Đỏ) */
    .pod-warning {
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 45%, #fde68a 100%);
        border: 3.5px solid #f59e0b;
        box-shadow: 0 12px 28px rgba(245, 158, 11, 0.22), inset 0 -4px 0 rgba(217, 119, 6, 0.18);
    }
    .pod-warning .pod-label { color: #92400e; }
    .pod-warning .value-warning { color: #78350f; }
    .pod-warning .pod-hint { color: #92400e; }

    /* Pod 3: Xanh Lục Bảo (Đã Vượt Qua) */
    .pod-success {
        background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 45%, #a7f3d0 100%);
        border: 3.5px solid #10b981;
        box-shadow: 0 12px 28px rgba(16, 185, 129, 0.22), inset 0 -4px 0 rgba(5, 150, 105, 0.18);
    }
    .pod-success .pod-label { color: #065f46; }
    .pod-success .value-success { color: #047857; }
    .pod-success .pod-hint { color: #065f46; }

    .pod-icon-orb {
        width: 52px;
        height: 52px;
        border-radius: 18px;
        display: grid;
        place-items: center;
        font-size: 22px;
        border: 2px solid #ffffff;
        box-shadow: 0 4px 10px rgba(0,0,0,0.12);
        flex-shrink: 0;
    }
    .orb-danger { background: linear-gradient(135deg, #f43f5e, #be123c); }
    .orb-warning { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .orb-success { background: linear-gradient(135deg, #10b981, #059669); }

    .pod-content {
        flex: 1;
    }
    .pod-label {
        display: block;
        font-size: 11px;
        font-weight: 1000;
        letter-spacing: 0.6px;
        margin-bottom: 2px;
    }
    .pod-value {
        font-family: 'Fredoka', cursive;
        font-size: 30px;
        font-weight: 1000;
        line-height: 1;
        display: flex;
        align-items: baseline;
        gap: 4px;
    }
    .pod-unit {
        font-size: 13px;
        font-family: 'Nunito', sans-serif;
        font-weight: 800;
    }
    .pod-hint {
        display: block;
        font-size: 11.5px;
        font-weight: 750;
        margin-top: 3px;
    }

    /* 3. Thanh Bộ Lọc Kính Mờ (Glassmorphism Mịn Màng) */
    .filter-card-bar {
        background: rgba(255, 255, 255, 0.94);
        backdrop-filter: blur(8px);
        border-radius: 18px;
        border: 2.5px solid rgba(255, 255, 255, 0.9);
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.07);
        padding: 12px 18px;
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }
    .filter-group {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .filter-group-label {
        font-size: 12.5px;
        font-weight: 900;
        color: #334155;
        white-space: nowrap;
    }
    .filter-pill-cluster {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }
    .filter-divider {
        width: 1px;
        height: 24px;
        background: #cbd5e1;
    }
    @media (max-width: 768px) {
        .filter-divider { display: none; }
    }
    .filter-pill {
        padding: 6px 13px;
        border-radius: 999px;
        background: #f1f5f9;
        border: 1.5px solid #cbd5e1;
        color: #334155;
        font-size: 12px;
        font-weight: 850;
        text-decoration: none;
        transition: all 0.15s ease;
    }
    .filter-pill:hover {
        background: #e2e8f0;
        color: #0f172a;
    }
    .filter-pill.active {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        border-color: #1d4ed8;
        color: #ffffff;
        box-shadow: 0 3px 8px rgba(37, 99, 235, 0.3);
    }
    .filter-pill.pill-risk.active {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        border-color: #dc2626;
        color: #ffffff;
        box-shadow: 0 3px 8px rgba(239, 68, 68, 0.3);
    }
    .filter-pill.pill-resolved.active {
        background: linear-gradient(135deg, #10b981, #059669);
        border-color: #059669;
        color: #ffffff;
        box-shadow: 0 3px 8px rgba(16, 185, 129, 0.3);
    }

    /* 4. Hàng Câu Hỏi Tinh Gọn - Tối Đa Độ Nổi Bật Của Chữ */
    .mistakes-list {
        display: grid;
        gap: 12px;
    }
    .mistake-item-row {
        background: #ffffff;
        border-radius: 18px;
        border: 2px solid #e2e8f0;
        box-shadow: 0 4px 14px rgba(0,0,0,0.05);
        padding: 16px 22px;
        display: flex;
        align-items: center;
        gap: 20px;
        position: relative;
        transition: all 0.2s ease;
    }
    .mistake-item-row:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.09);
        border-color: #cbd5e1;
    }

    /* Vạch chỉ báo phân cấp màu sắc rõ rệt */
    .mistake-item-row.is-danger {
        border-left: 6px solid #ef4444;
    }
    .mistake-item-row.is-warning {
        border-left: 6px solid #f59e0b;
    }
    .mistake-item-row.is-normal {
        border-left: 6px solid #3b82f6;
    }
    .mistake-item-row.is-resolved {
        border-left: 6px solid #10b981;
        opacity: 0.94;
    }

    /* Cột Nội dung */
    .mistake-content-col {
        flex: 1;
        min-width: 0;
    }
    .mistake-meta-bar {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 6px;
    }
    .meta-pill-grade {
        font-size: 11px;
        font-weight: 900;
        padding: 2px 9px;
        border-radius: 6px;
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
    }
    .meta-topic-text {
        font-size: 12px;
        font-weight: 750;
        color: #475569;
    }
    .risk-tag {
        font-size: 11px;
        font-weight: 850;
        padding: 2px 8px;
        border-radius: 6px;
    }
    .risk-tag-danger {
        background: #fef2f2;
        color: #b91c1c;
        border: 1px solid #fca5a5;
    }
    .risk-tag-warning {
        background: #fffbeb;
        color: #b45309;
        border: 1px solid #fde68a;
    }
    .risk-tag-normal {
        background: #f1f5f9;
        color: #334155;
        border: 1px solid #cbd5e1;
    }
    .risk-tag-resolved {
        background: #ecfdf5;
        color: #047857;
        border: 1px solid #a7f3d0;
    }
    .meta-time-text {
        font-size: 11.5px;
        font-weight: 700;
        color: #94a3b8;
        margin-left: auto;
    }

    /* Tiêu đề câu hỏi: Chữ to, đen tuyền, rõ nét 100% */
    .q-title-text {
        font-size: 15.5px;
        font-weight: 850;
        color: #0f172a;
        line-height: 1.48;
        margin: 0;
        letter-spacing: -0.1px;
    }

    /* Cột Thao tác: Gọn gàng và nổi bật */
    .mistake-action-col {
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: flex-end;
    }
    .btn-revenge-main {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 9px 20px;
        border-radius: 14px;
        background: linear-gradient(135deg, #ef4444, #dc2626);
        border: 2px solid #ffffff;
        color: #ffffff;
        font-weight: 950;
        font-size: 13.5px;
        text-decoration: none;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        transition: all 0.15s ease;
        white-space: nowrap;
    }
    .btn-revenge-main:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(239, 68, 68, 0.42);
    }
    .resolved-action-group {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 3px;
    }
    .badge-resolved-success {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 11.5px;
        font-weight: 900;
        color: #047857;
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        padding: 3px 10px;
        border-radius: 999px;
        white-space: nowrap;
    }
    .btn-replay-link {
        font-size: 11px;
        font-weight: 800;
        color: #64748b;
        text-decoration: underline;
    }
    .btn-replay-link:hover {
        color: #1e293b;
    }

    /* Màn Hình Trống */
    .empty-mistake-card {
        background: #ffffff;
        border-radius: 24px;
        border: 3.5px solid #ffffff;
        box-shadow: 0 14px 32px rgba(0,0,0,0.06);
        padding: 50px 20px;
        text-align: center;
    }

    /* Responsive cho màn hình di động */
    @media (max-width: 768px) {
        .mistake-item-row {
            flex-direction: column;
            align-items: stretch;
            gap: 14px;
            padding: 14px 16px;
        }
        .meta-time-text {
            margin-left: 0;
            width: 100%;
        }
        .mistake-action-col {
            justify-content: stretch;
        }
        .btn-revenge-main {
            width: 100%;
            justify-content: center;
        }
        .resolved-action-group {
            align-items: center;
            width: 100%;
        }
    }
</style>
@endsection
