<!doctype html>
<html lang="vi" translate="no" class="notranslate">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="google" content="notranslate">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Đấu Trường Phục Thù — Luyện Lại Các Câu Sai — IC3 Adventure</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700;800&family=Nunito:wght@600;700;800;900;1000&display=swap" rel="stylesheet">
    <style>
        :root {
            --grade-accent: #f87171;
            --grade-glow: #ef4444;
            --gold: #ffe135;
            --gold-glow: #ff9f1a;
            --correct: #16a34a;
            --wrong: #dc2626;
            --text-main: #ffffff;
            --text-muted: #b8d5f8;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; user-select: none; }

        body {
            font-family: 'Nunito', 'Segoe UI', sans-serif;
            background: #0f172a;
            color: var(--text-main);
            height: 100vh;
            max-height: 100vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(239, 68, 68, 0.22) 0%, transparent 45%),
                radial-gradient(circle at 90% 80%, rgba(249, 115, 22, 0.22) 0%, transparent 50%),
                radial-gradient(circle at 50% 50%, rgba(30, 41, 59, 0.6) 0%, transparent 70%);
            background-attachment: fixed;
        }

        .arena-topbar {
            flex-shrink: 0;
            z-index: 100;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 8px 24px;
            background: linear-gradient(180deg, rgba(30, 41, 59, 0.95), rgba(15, 23, 42, 0.92));
            backdrop-filter: blur(18px);
            border-bottom: 3px solid #ef4444;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.5);
        }

        .bar-left, .bar-right { display: flex; align-items: center; gap: 12px; z-index: 2; }

        .btn-exit {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 7px 16px;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.25);
            border-radius: 999px;
            color: #fff;
            font-weight: 900;
            font-size: 13px;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-exit:hover {
            background: linear-gradient(180deg, #ff4757, #c0392b);
            border-color: #ff6b81;
            color: #fff;
            transform: translateX(-2px);
        }

        .stage-pill-info {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(249, 115, 22, 0.15));
            border: 1.5px solid rgba(239, 68, 68, 0.5);
            border-radius: 12px;
            font-size: 13px;
            font-weight: 900;
            color: #fee2e2;
        }

        .bar-center {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1;
            pointer-events: none;
        }
        .arena-brand {
            font-family: 'Fredoka', cursive, sans-serif;
            font-size: 20px;
            font-weight: 700;
            color: #fde047;
            text-shadow: 0 2px 0 #7e4200;
            display: flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }

        @media (max-width: 992px) {
            .stage-pill-info { display: none; }
        }
        @media (max-width: 680px) {
            .bar-center {
                position: static;
                transform: none;
                flex: 1;
            }
            .arena-brand {
                font-size: 16px;
            }
        }

        .bar-right { justify-content: flex-end; }
        .hud-timer-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            background: rgba(0, 0, 0, 0.4);
            border: 2px solid #10b981;
            border-radius: 12px;
            color: #6ee7b7;
            font-size: 13px;
            font-weight: 900;
        }

        .arena-container {
            flex: 1;
            min-height: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            padding: 12px 24px 18px;
            position: relative;
            z-index: 10;
        }

        .checkpoint-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 10px;
            max-width: 900px;
        }
        .chk-node {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
            font-size: 13px;
            font-weight: 900;
            display: grid;
            place-items: center;
            cursor: pointer;
            transition: all 0.15s;
        }
        .chk-node:hover { transform: scale(1.1); border-color: #ef4444; }
        .chk-node.active {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            border-color: #ffffff;
            box-shadow: 0 0 12px rgba(239, 68, 68, 0.8);
            transform: scale(1.15);
        }
        .chk-node.answered {
            background: rgba(249, 115, 22, 0.35);
            border-color: #f97316;
        }
        .chk-node.review-correct {
            background: #16a34a !important;
            border-color: #86efac !important;
            box-shadow: 0 0 10px rgba(34, 197, 94, 0.6);
        }
        .chk-node.review-wrong {
            background: #dc2626 !important;
            border-color: #fca5a5 !important;
            box-shadow: 0 0 10px rgba(239, 68, 68, 0.6);
        }

        .quest-card {
            width: min(940px, 100%);
            flex: 1;
            min-height: 0;
            background: rgba(15, 23, 42, 0.95);
            border: 3.5px solid rgba(255, 255, 255, 0.9);
            border-radius: 26px;
            box-shadow: 0 16px 45px rgba(0, 0, 0, 0.6), inset 0 -6px 0 rgba(0,0,0,0.4);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .quest-header {
            padding: 16px 24px 12px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
        }
        .quest-meta-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .type-pill {
            padding: 3px 12px;
            border-radius: 999px;
            background: #fee2e2;
            color: #dc2626;
            font-size: 11.5px;
            font-weight: 1000;
            text-transform: uppercase;
            box-shadow: 0 2px 6px rgba(239,68,68,0.25);
        }
        .quest-title {
            font-size: 16.5px;
            font-weight: 800;
            line-height: 1.45;
            color: #ffffff;
            white-space: pre-line;
        }

        .quest-body {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            padding: 16px 24px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        /* Ảnh minh họa đề bài */
        .quest-image-box {
            text-align: center;
            width: min(100%, 760px);
            aspect-ratio: 16 / 9;
            max-height: clamp(180px, 32vh, 360px);
            margin: 0 auto 16px;
            padding: 10px;
            border-radius: 18px;
            border: 2px solid rgba(56, 189, 248, 0.42);
            background: linear-gradient(180deg, rgba(8, 30, 55, 0.78), rgba(5, 18, 36, 0.72));
            box-shadow: 0 14px 34px rgba(0, 0, 0, 0.24), inset 0 0 0 1px rgba(255, 255, 255, 0.06);
            position: relative;
            overflow: hidden;
            display: grid;
            place-items: center;
        }
        .quest-image-box::before {
            content: "";
            position: absolute;
            inset: 10px;
            border-radius: 12px;
            background-image: var(--quest-image-bg);
            background-size: cover;
            background-position: center;
            filter: blur(18px) saturate(1.15);
            opacity: .32;
            transform: scale(1.08);
        }
        .quest-image-box img {
            display: block;
            position: relative;
            z-index: 1;
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
            object-fit: contain;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.08);
            cursor: zoom-in;
            transition: transform 0.18s;
        }
        .quest-image-box img:hover { transform: scale(1.02); }

        /* 1. Multiple Choice / Response */
        .options-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 14px;
            width: 100%;
        }
        @media (max-width: 640px) {
            .options-grid { grid-template-columns: 1fr; }
        }
        .opt-card {
            background: rgba(255, 255, 255, 0.08);
            border: 2.5px solid rgba(255, 255, 255, 0.22);
            border-radius: 18px;
            padding: 12px 18px;
            display: flex;
            align-items: center;
            gap: 14px;
            cursor: pointer;
            transition: all 0.15s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            min-height: 72px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        .opt-card:hover {
            background: rgba(255, 255, 255, 0.14);
            border-color: rgba(255, 255, 255, 0.5);
            transform: translateY(-2px);
        }
        .opt-card.selected {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.5), rgba(220, 38, 38, 0.4));
            border-color: #ef4444;
            box-shadow: 0 0 16px rgba(239, 68, 68, 0.6);
        }
        .opt-card.review-correct-answer {
            background: rgba(22, 163, 74, 0.5) !important;
            border-color: #22c55e !important;
            box-shadow: 0 0 16px rgba(34, 197, 94, 0.6) !important;
        }
        .opt-card.review-wrong-answer {
            background: rgba(220, 38, 38, 0.5) !important;
            border-color: #ef4444 !important;
            box-shadow: 0 0 16px rgba(239, 68, 68, 0.6) !important;
        }
        .opt-tag {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.18);
            color: #ffffff;
            display: grid;
            place-items: center;
            font-weight: 1000;
            font-size: 15px;
            flex-shrink: 0;
            border: 1.5px solid rgba(255, 255, 255, 0.35);
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        }
        .opt-content {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 15px;
            font-weight: 800;
            line-height: 1.4;
            color: #ffffff;
            min-width: 0;
        }
        .opt-text {
            word-break: break-word;
        }
        .choice-img {
            max-width: 130px;
            max-height: 80px;
            width: auto;
            height: auto;
            object-fit: contain;
            border-radius: 10px;
            background: #ffffff;
            border: 1.5px solid #cbd5e1;
            padding: 4px;
            flex-shrink: 0;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
            cursor: zoom-in;
            transition: transform 0.15s;
        }
        .choice-img:hover {
            transform: scale(1.05);
        }

        /* 2. MultipleChoiceText (Phân loại / Dropdown choices) */
        .classify-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            width: 100%;
            margin: auto 0;
        }
        .classify-item {
            display: grid;
            grid-template-columns: 1.15fr 1.25fr;
            align-items: center;
            gap: 16px;
            padding: 12px 18px;
            background: rgba(255, 255, 255, 0.08);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 18px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            transition: all 0.2s;
        }
        @media (max-width: 768px) {
            .classify-item { grid-template-columns: 1fr; gap: 10px; }
        }
        .classify-label {
            font-size: 14.5px;
            font-weight: 800;
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 10px;
            line-height: 1.4;
        }
        .classify-idx-badge {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #ffffff;
            display: grid;
            place-items: center;
            font-size: 12px;
            font-weight: 900;
            flex-shrink: 0;
            border: 1.5px solid rgba(255,255,255,0.4);
        }
        .classify-buttons-group {
            display: flex;
            gap: 8px;
            align-items: center;
            width: 100%;
            flex-wrap: wrap;
        }
        .classify-btn {
            flex: 1;
            min-width: 110px;
            min-height: 42px;
            padding: 8px 14px;
            border-radius: 12px;
            border: 2px solid rgba(255, 255, 255, 0.25);
            background: rgba(255, 255, 255, 0.08);
            color: #e2e8f0;
            font-size: 13.5px;
            font-weight: 850;
            cursor: pointer;
            transition: all 0.15s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .classify-btn:hover {
            background: rgba(255, 255, 255, 0.18);
            border-color: #ffffff;
            color: #ffffff;
            transform: translateY(-2px);
        }
        .classify-btn.selected {
            background: linear-gradient(135deg, #ef4444, #dc2626) !important;
            border-color: #ffffff !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(239, 68, 68, 0.6) !important;
            transform: scale(1.02);
        }
        .classify-btn.rev-correct {
            background: #16a34a !important;
            border-color: #86efac !important;
            color: #ffffff !important;
            box-shadow: 0 0 12px rgba(34, 197, 94, 0.6) !important;
        }
        .classify-btn.rev-wrong {
            background: #dc2626 !important;
            border-color: #fca5a5 !important;
            color: #ffffff !important;
            box-shadow: 0 0 12px rgba(239, 68, 68, 0.6) !important;
        }

        /* 3. Sequence (Sắp xếp thứ tự) */
        .sequence-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
            width: 100%;
        }
        .sequence-item {
            background: rgba(255, 255, 255, 0.06);
            border: 2px solid rgba(255, 255, 255, 0.18);
            border-radius: 14px;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            color: #fff;
            font-size: 14px;
            font-weight: 800;
        }
        .sequence-item.rev-correct {
            background: rgba(22, 163, 74, 0.25);
            border-color: #22c55e;
        }
        .sequence-item.rev-wrong {
            background: rgba(220, 38, 38, 0.25);
            border-color: #ef4444;
        }
        .btn-seq-move {
            padding: 6px 12px;
            border-radius: 8px;
            border: 1.5px solid rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            font-weight: 800;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.15s;
        }
        .btn-seq-move:hover:not(:disabled) {
            background: #ef4444;
            border-color: #ef4444;
        }

        /* 4. Matching (Ghép nối) */
        .matching-board {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
            width: 100%;
        }
        .matching-card {
            background: rgba(255, 255, 255, 0.06);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 14px;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            transition: all 0.15s;
            color: #fff;
            font-size: 13.5px;
            font-weight: 800;
            min-height: 52px;
        }
        .matching-card:hover {
            background: rgba(255, 255, 255, 0.12);
            border-color: #38bdf8;
            transform: translateY(-2px);
        }
        .matching-card.selected {
            background: rgba(239, 68, 68, 0.35) !important;
            border-color: #ef4444 !important;
            box-shadow: 0 0 14px rgba(239, 68, 68, 0.6) !important;
        }
        .matching-card.matched {
            border-color: #f59e0b;
            background: rgba(245, 158, 11, 0.18);
        }
        .matching-card.rev-correct {
            background: rgba(22, 163, 74, 0.35) !important;
            border-color: #22c55e !important;
            box-shadow: 0 0 12px rgba(34, 197, 94, 0.5) !important;
        }
        .matching-card.rev-wrong {
            background: rgba(220, 38, 38, 0.35) !important;
            border-color: #ef4444 !important;
            box-shadow: 0 0 12px rgba(239, 68, 68, 0.5) !important;
        }

        /* 5. Hotspot (Nhấp trên hình) */
        .hotspot-outer-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            width: 100%;
        }
        .hotspot-wrapper {
            position: relative;
            display: inline-block;
            max-width: 100%;
            border-radius: 14px;
            overflow: hidden;
            border: 2.5px solid rgba(255, 255, 255, 0.3);
            background: #ffffff;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
            cursor: crosshair;
        }
        .hotspot-wrapper img {
            max-width: 100%;
            max-height: 330px;
            display: block;
            object-fit: contain;
        }
        .hotspot-target-marker {
            position: absolute;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #ef4444;
            border: 2.5px solid #ffffff;
            box-shadow: 0 0 14px #ef4444, inset 0 0 4px rgba(0,0,0,0.5);
            transform: translate(-50%, -50%);
            display: none;
            place-items: center;
            font-size: 13px;
            font-weight: 900;
            color: #fff;
            pointer-events: none;
            z-index: 10;
        }
        .hotspot-target-marker.rev-hit {
            background: #16a34a !important;
            box-shadow: 0 0 14px #22c55e !important;
        }
        .hotspot-target-marker.rev-miss {
            background: #dc2626 !important;
            box-shadow: 0 0 14px #ef4444 !important;
        }
        .hotspot-correct-box {
            position: absolute;
            border: 3px dashed #22c55e;
            background: rgba(34, 197, 94, 0.3);
            box-shadow: 0 0 16px rgba(34, 197, 94, 0.7);
            border-radius: 8px;
            pointer-events: none;
            z-index: 9;
        }
        .hotspot-correct-badge {
            position: absolute;
            top: -24px;
            left: 0;
            background: #16a34a;
            color: #ffffff;
            font-size: 10.5px;
            font-weight: 1000;
            padding: 2px 8px;
            border-radius: 6px;
            white-space: nowrap;
            box-shadow: 0 2px 6px rgba(0,0,0,0.3);
        }

        .quest-footer {
            padding: 12px 24px;
            border-top: 2px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            background: rgba(0, 0, 0, 0.25);
        }
        .btn-act {
            padding: 10px 20px;
            border-radius: 14px;
            border: 2px solid transparent;
            font-family: inherit;
            font-size: 14px;
            font-weight: 900;
            cursor: pointer;
            transition: all 0.15s;
        }
        .btn-prev { background: rgba(255, 255, 255, 0.1); color: #fff; border-color: rgba(255,255,255,0.2); }
        .btn-prev:hover { background: rgba(255, 255, 255, 0.2); }
        .btn-next { background: #2563eb; color: #fff; }
        .btn-next:hover { background: #1d4ed8; }
        .btn-submit {
            background: linear-gradient(135deg, #10b981, #059669);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35);
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(16, 185, 129, 0.5); }

        /* Score Summary Modal */
        .score-modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(8px);
            z-index: 1000;
            place-items: center;
            padding: 20px;
        }
        .score-modal.show { display: grid; }
        .score-box {
            background: #ffffff;
            color: #0f172a;
            border-radius: 28px;
            padding: 36px 32px;
            width: min(540px, 100%);
            text-align: center;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.5);
            border: 4px solid #ffffff;
            animation: popUp 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        @keyframes popUp {
            from { transform: scale(0.85); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        .score-icon { font-size: 64px; margin-bottom: 12px; }
        .score-heading { font-family: 'Fredoka', cursive; font-size: 26px; margin-bottom: 6px; }
        .score-sub { font-size: 14px; color: #64748b; margin-bottom: 20px; font-weight: 700; }
        .score-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            background: #f8fafc;
            border-radius: 18px;
            padding: 16px;
            margin-bottom: 24px;
            border: 1.5px solid #e2e8f0;
        }
        .stat-blk small { display: block; font-size: 11px; font-weight: 900; color: #64748b; }
        .stat-blk b { font-size: 24px; font-weight: 1000; font-family: 'Fredoka', cursive; }

        /* Modal Zoom Ảnh */
        #img-zoom-modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.88);
            backdrop-filter: blur(8px);
            z-index: 2000;
            place-items: center;
            padding: 24px;
            cursor: zoom-out;
        }
        #img-zoom-modal.show { display: grid; }
        #zoom-img-target {
            max-width: 90vw;
            max-height: 85vh;
            border-radius: 16px;
            box-shadow: 0 16px 48px rgba(0,0,0,0.8);
            border: 3px solid #ffffff;
            background: #ffffff;
            object-fit: contain;
        }
    </style>
</head>
<body>

    <header class="arena-topbar">
        <div class="bar-left">
            <a class="btn-exit" href="{{ route('mistakes.index') }}">
                ◀ Về Sổ Tay
            </a>
            <div class="stage-pill-info">
                <span>🔥</span> ĐẤU TRƯỜNG PHỤC THÙ CÂU SAI
            </div>
        </div>

        <div class="bar-center">
            <div class="arena-brand">
                <span>⚡</span> PHÒNG ÔN TẬP PHỤC THÙ
            </div>
        </div>

        <div class="bar-right">
            <div class="hud-timer-badge">
                <span>⏱️</span> Thong thả suy nghĩ
            </div>
        </div>
    </header>

    <main class="arena-container">
        <!-- Checkpoints -->
        <nav class="checkpoint-container" id="checkpoint-container"></nav>

        <!-- Quest Card -->
        <article class="quest-card" id="quest-card">
            <div class="quest-header">
                <div class="quest-meta-row">
                    <span class="type-pill" id="type-badge">NHIỆM VỤ PHỤC THÙ</span>
                    <span style="font-size: 13px; font-weight: 900; color: #94a3b8;">
                        Câu <b id="q-curr-num" style="color:#fff;">1</b> / <span id="q-total-num">1</span>
                    </span>
                </div>
                <div id="wrong-alert-banner" style="background: rgba(239,68,68,0.2); border: 1.5px solid rgba(239,68,68,0.4); border-radius: 10px; padding: 6px 12px; font-size: 12px; font-weight: 800; color: #fca5a5; margin-bottom: 8px;">
                    ⚠️ Em đã làm sai câu này <b id="q-wrong-times">1</b> lần. Cùng làm thật cẩn thận để phục thù thành công nhé!
                </div>
                <h1 class="quest-title" id="quest-title">Nội dung câu hỏi...</h1>
            </div>

            <div class="quest-body" id="quest-body">
                <div id="dynamic-content-area" style="width: 100%; flex: 1; display: flex; flex-direction: column; justify-content: center;"></div>
            </div>

            <div class="quest-footer">
                <button class="btn-act btn-prev" id="btn-prev" style="display: none;">◀ Câu trước</button>
                <div style="flex: 1; text-align: center;" id="feedback-banner"></div>
                <div style="display: flex; gap: 8px;">
                    <button class="btn-act btn-next" id="btn-next">Câu tiếp ▶</button>
                    <button class="btn-act btn-submit" id="btn-submit">🏁 NỘP BÀI PHỤC THÙ</button>
                </div>
            </div>
        </article>
    </main>

    <!-- Score Modal -->
    <div class="score-modal" id="score-modal">
        <div class="score-box">
            <div class="score-icon" id="res-icon">🎉</div>
            <h2 class="score-heading" id="res-title">Phục Thù Thành Công!</h2>
            <p class="score-sub" id="res-sub">Em đã giải quyết được các câu hỏi bị sai!</p>

            <div class="score-stats">
                <div class="stat-blk">
                    <small>ĐÃ KHẮC PHỤC</small>
                    <b style="color: #16a34a;" id="stat-resolved">0</b>
                </div>
                <div class="stat-blk">
                    <small>SAO VÀNG NHẬN ĐƯỢC</small>
                    <b style="color: #eab308;" id="stat-stars">+0 ⭐</b>
                </div>
            </div>

            <div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
                <button type="button" class="btn-act" onclick="document.getElementById('score-modal').classList.remove('show'); renderQuestion();" style="background: #f1f5f9; color: #1e293b; border: 1.5px solid #cbd5e1;">
                    👁️ Xem Lại Đáp Án
                </button>
                <a class="btn-act" href="{{ route('mistakes.index') }}" style="background: #2563eb; color: #fff; text-decoration: none;">
                    📕 Về Sổ Tay Câu Sai
                </a>
                <button type="button" class="btn-act" onclick="location.reload()" style="background: #10b981; color: #fff;">
                    🔄 Làm Lại
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Zoom Ảnh -->
    <div id="img-zoom-modal" onclick="closeImageZoom()">
        <img id="zoom-img-target" src="" alt="Phóng to">
    </div>

    <script>
        const questions = @json($questions);
        const submitUrl = "{{ route('mistakes.submit') }}";
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        let currentIndex = 0;
        let userSelections = {};
        let hotspotClickPoints = {};
        let isSubmitted = false;
        let serverResults = [];
        let serverCorrectAnswers = {};

        // Audio synthesizer
        const AudioCtxClass = window.AudioContext || window.webkitAudioContext;
        let audioCtx = null;
        function initAudio() {
            if (!audioCtx) audioCtx = new AudioCtxClass();
            if (audioCtx.state === 'suspended') audioCtx.resume();
        }
        function playSound(type) {
            try {
                initAudio();
                const now = audioCtx.currentTime;
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.connect(gain);
                gain.connect(audioCtx.destination);

                if (type === 'click') {
                    osc.type = 'triangle';
                    osc.frequency.setValueAtTime(600, now);
                    osc.frequency.exponentialRampToValueAtTime(300, now + 0.06);
                    gain.gain.setValueAtTime(0.2, now);
                    gain.gain.linearRampToValueAtTime(0.01, now + 0.06);
                    osc.start(now);
                    osc.stop(now + 0.06);
                } else if (type === 'select') {
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(523.25, now);
                    osc.frequency.setValueAtTime(659.25, now + 0.05);
                    gain.gain.setValueAtTime(0.25, now);
                    gain.gain.linearRampToValueAtTime(0.01, now + 0.12);
                    osc.start(now);
                    osc.stop(now + 0.12);
                } else if (type === 'victory') {
                    [523.25, 659.25, 783.99, 1046.50].forEach((freq, idx) => {
                        const o = audioCtx.createOscillator();
                        const g = audioCtx.createGain();
                        o.connect(g);
                        g.connect(audioCtx.destination);
                        o.type = 'triangle';
                        o.frequency.setValueAtTime(freq, now + idx * 0.1);
                        g.gain.setValueAtTime(0.3, now + idx * 0.1);
                        g.gain.linearRampToValueAtTime(0.01, now + idx * 0.1 + 0.25);
                        o.start(now + idx * 0.1);
                        o.stop(now + idx * 0.1 + 0.25);
                    });
                } else if (type === 'fail') {
                    osc.type = 'sawtooth';
                    osc.frequency.setValueAtTime(350, now);
                    osc.frequency.linearRampToValueAtTime(150, now + 0.25);
                    gain.gain.setValueAtTime(0.25, now);
                    gain.gain.linearRampToValueAtTime(0.01, now + 0.25);
                    osc.start(now);
                    osc.stop(now + 0.25);
                }
            } catch (e) {}
        }

        // Image Zoom modal helpers
        function zoomImage(src) {
            const modal = document.getElementById('img-zoom-modal');
            const target = document.getElementById('zoom-img-target');
            if (!modal || !target || !src) return;
            target.src = src;
            modal.classList.add('show');
        }
        function closeImageZoom() {
            const modal = document.getElementById('img-zoom-modal');
            if (modal) modal.classList.remove('show');
        }

        // Helper: Tách ảnh minh họa của câu hỏi (đảm bảo không nhầm với ảnh của phương án)
        function getPromptImagePath(q) {
            const optImgPaths = (q.options || []).map(o => o.image_path).filter(Boolean);
            let promptImgPath = q.configuration?.image_path || null;

            if (!promptImgPath && q.assets && q.assets.length > 0) {
                const candidate = q.assets.find(a => a.kind === 'question_image' || (a.kind === 'image' && !optImgPaths.includes(a.path)));
                if (candidate) promptImgPath = candidate.path;
            }
            return promptImgPath;
        }

        // Helper: Chuẩn hóa tọa độ vùng chọn Hotspot (Hỗ trợ cả thang 0-100% lẫn 0-10000)
        function getNormRect(rect) {
            if (!rect) return null;
            let x = parseFloat(rect.x || 0);
            let y = parseFloat(rect.y || 0);
            let w = parseFloat(rect.w || 0);
            let h = parseFloat(rect.h || 0);
            if (x > 100 || y > 100 || w > 100 || h > 100) {
                x = x / 100;
                y = y / 100;
                w = w / 100;
                h = h / 100;
            }
            return { x, y, w, h };
        }

        function initCheckpoints() {
            const container = document.getElementById('checkpoint-container');
            container.innerHTML = '';
            questions.forEach((q, idx) => {
                const node = document.createElement('div');
                node.className = 'chk-node' + (idx === 0 ? ' active' : '');
                node.textContent = idx + 1;
                node.onclick = () => {
                    playSound('click');
                    currentIndex = idx;
                    renderQuestion();
                };
                container.appendChild(node);
            });
            document.getElementById('q-total-num').textContent = questions.length;
        }

        function renderQuestion() {
            if (questions.length === 0) return;
            const q = questions[currentIndex];

            document.getElementById('q-curr-num').textContent = currentIndex + 1;
            document.getElementById('quest-title').textContent = q.title || '(Câu hỏi thực hành)';
            document.getElementById('q-wrong-times').textContent = q.wrong_count || 1;

            const typeNames = {
                'MultipleChoice': 'CHỌN 1 ĐÁP ÁN',
                'MultipleResponse': 'CHỌN NHIỀU ĐÁP ÁN',
                'MultipleChoiceText': 'CHỌN TỪ THẢ XUỐNG / PHÂN LOẠI',
                'Matching': 'GHÉP NỐI KHÁI NIỆM',
                'Sequence': 'SẮP XẾP THỨ TỰ',
                'Hotspot': 'CHỌN VỊ TRÍ TRÊN HÌNH'
            };
            document.getElementById('type-badge').textContent = typeNames[q.type] || 'NHIỆM VỤ PHỤC THÙ';

            // Highlight checkpoint node
            document.querySelectorAll('.chk-node').forEach((n, idx) => {
                n.classList.toggle('active', idx === currentIndex);
                if (userSelections[idx] !== undefined && userSelections[idx] !== null) {
                    n.classList.add('answered');
                }
            });

            // Feedback Banner
            const fb = document.getElementById('feedback-banner');
            if (isSubmitted && serverResults[currentIndex] !== undefined) {
                const isCorrect = serverResults[currentIndex];
                fb.innerHTML = isCorrect 
                    ? '<span style="color:#4ade80; font-weight:900; font-size:14px;">✓ CHÍNH XÁC! Em đã phục thù thành công câu này.</span>'
                    : '<span style="color:#f87171; font-weight:900; font-size:14px;">✕ CHƯA ĐÚNG! Em hãy ghi nhớ đáp án màu xanh nhé.</span>';
            } else {
                fb.innerHTML = '';
            }

            const container = document.getElementById('dynamic-content-area');
            container.innerHTML = '';

            // Nếu câu hỏi có ảnh minh họa đề bài (và không phải Hotspot)
            const promptImg = getPromptImagePath(q);
            if (promptImg && q.type !== 'Hotspot') {
                const imgBox = document.createElement('div');
                imgBox.className = 'quest-image-box';
                imgBox.style.setProperty('--quest-image-bg', `url("${promptImg}")`);
                imgBox.innerHTML = `<img src="${promptImg}" alt="Minh họa đề bài" onclick="zoomImage('${promptImg}')" title="Nhấp để xem ảnh lớn">`;
                container.appendChild(imgBox);
            }

            if (q.type === 'MultipleChoiceText') {
                renderChoiceText(q, container);
            } else if (q.type === 'MultipleResponse') {
                renderMultipleResponse(q, container);
            } else if (q.type === 'Sequence') {
                renderSequence(q, container);
            } else if (q.type === 'Matching') {
                renderMatching(q, container);
            } else if (q.type === 'Hotspot') {
                renderHotspot(q, container);
            } else {
                renderMultipleChoice(q, container);
            }

            // Cập nhật nút Next / Prev / Submit
            const isLast = currentIndex === questions.length - 1;
            document.getElementById('btn-prev').style.display = currentIndex > 0 ? 'inline-block' : 'none';
            document.getElementById('btn-next').style.display = isLast ? 'none' : 'inline-block';
            document.getElementById('btn-submit').style.display = (isLast && !isSubmitted) ? 'inline-block' : 'none';
        }

        // 1. Multiple Choice (1 đáp án - Hỗ trợ cả Chữ lẫn Hình Ảnh)
        function renderMultipleChoice(q, container) {
            const optGrid = document.createElement('div');
            optGrid.className = 'options-grid';

            const rawCorrect = serverCorrectAnswers[currentIndex] || [];
            const correctList = Array.isArray(rawCorrect) ? rawCorrect.map(Number) : [Number(rawCorrect)];

            (q.options || []).forEach((opt, optIdx) => {
                const card = document.createElement('div');
                const posVal = opt.position !== undefined ? opt.position : optIdx;
                const isSelected = userSelections[currentIndex] === posVal;
                card.className = 'opt-card' + (isSelected ? ' selected' : '');

                if (isSubmitted) {
                    if (correctList.includes(posVal) || opt.is_correct) {
                        card.classList.add('review-correct-answer');
                    } else if (isSelected) {
                        card.classList.add('review-wrong-answer');
                    }
                }

                const letters = ['A', 'B', 'C', 'D', 'E', 'F'];
                const letter = letters[optIdx] || (optIdx + 1);

                // Render hình ảnh của phương án (nếu có)
                const imgHtml = opt.image_path ? `<img class="choice-img" src="${opt.image_path}" alt="Hình ${letter}" onclick="event.stopPropagation(); zoomImage('${opt.image_path}')" title="Nhấp để phóng to">` : '';
                const cleanContent = (opt.content && opt.content.trim() !== '' && opt.content.trim() !== '​') ? opt.content.trim() : '';

                card.innerHTML = `
                    <span class="opt-tag">${letter}</span>
                    <div class="opt-content">
                        ${imgHtml}
                        ${cleanContent ? `<span class="opt-text">${cleanContent}</span>` : ''}
                    </div>
                `;

                if (!isSubmitted) {
                    card.onclick = () => {
                        playSound('select');
                        userSelections[currentIndex] = posVal;
                        renderQuestion();
                    };
                }

                optGrid.appendChild(card);
            });

            container.appendChild(optGrid);
        }

        // 2. Multiple Response (Nhiều đáp án - Hỗ trợ cả Chữ lẫn Hình Ảnh)
        function renderMultipleResponse(q, container) {
            const optGrid = document.createElement('div');
            optGrid.className = 'options-grid';

            let cur = userSelections[currentIndex];
            if (!Array.isArray(cur)) cur = [];

            const rawCorrect = serverCorrectAnswers[currentIndex] || [];
            const correctList = Array.isArray(rawCorrect) ? rawCorrect.map(Number) : [Number(rawCorrect)];

            (q.options || []).forEach((opt, optIdx) => {
                const card = document.createElement('div');
                const posVal = opt.position !== undefined ? opt.position : optIdx;
                const isSelected = cur.includes(posVal);
                card.className = 'opt-card' + (isSelected ? ' selected' : '');

                if (isSubmitted) {
                    if (correctList.includes(posVal) || opt.is_correct) {
                        card.classList.add('review-correct-answer');
                    } else if (isSelected) {
                        card.classList.add('review-wrong-answer');
                    }
                }

                const letters = ['A', 'B', 'C', 'D', 'E', 'F'];
                const letter = letters[optIdx] || (optIdx + 1);

                const imgHtml = opt.image_path ? `<img class="choice-img" src="${opt.image_path}" alt="Hình ${letter}" onclick="event.stopPropagation(); zoomImage('${opt.image_path}')" title="Nhấp để phóng to">` : '';
                const cleanContent = (opt.content && opt.content.trim() !== '' && opt.content.trim() !== '​') ? opt.content.trim() : '';

                card.innerHTML = `
                    <span class="opt-tag">${letter}</span>
                    <div class="opt-content">
                        ${imgHtml}
                        ${cleanContent ? `<span class="opt-text">${cleanContent}</span>` : ''}
                    </div>
                `;

                if (!isSubmitted) {
                    card.onclick = () => {
                        playSound('select');
                        let selectedArr = userSelections[currentIndex] || [];
                        if (!Array.isArray(selectedArr)) selectedArr = [];
                        if (selectedArr.includes(posVal)) {
                            selectedArr = selectedArr.filter(p => p !== posVal);
                        } else {
                            selectedArr.push(posVal);
                        }
                        userSelections[currentIndex] = selectedArr;
                        renderQuestion();
                    };
                }

                optGrid.appendChild(card);
            });

            container.appendChild(optGrid);
        }

        // 3. Multiple Choice Text (Chọn từ thả xuống / Phân loại khái niệm)
        function renderChoiceText(q, container) {
            const candidateChoices = new Set();
            (q.options || []).forEach(o => {
                if (o.metadata?.right && o.metadata.right.trim()) candidateChoices.add(o.metadata.right.trim());
                if (Array.isArray(o.metadata?.available_options)) o.metadata.available_options.forEach(v => candidateChoices.add(v.trim()));
            });
            const distinctChoices = Array.from(candidateChoices);

            const classifyList = document.createElement('div');
            classifyList.className = 'classify-list';

            const userMap = (typeof userSelections[currentIndex] === 'object' && !Array.isArray(userSelections[currentIndex])) 
                ? userSelections[currentIndex] : {};

            const correctMap = serverCorrectAnswers[currentIndex] || {};

            (q.options || []).forEach((opt, idx) => {
                let leftVal = opt.metadata?.left || opt.content || `Mục ${idx + 1}`;
                if (opt.content && opt.content.includes(':::')) {
                    leftVal = opt.content.split(':::')[0].trim();
                }

                const itemChoices = (opt.metadata?.available_options && opt.metadata.available_options.length) 
                    ? opt.metadata.available_options : distinctChoices;

                const row = document.createElement('div');
                row.className = 'classify-item';

                const labelDiv = document.createElement('div');
                labelDiv.className = 'classify-label';
                labelDiv.innerHTML = `
                    <span class="classify-idx-badge">${idx + 1}</span>
                    <span>${leftVal}</span>
                `;

                const btnGroup = document.createElement('div');
                btnGroup.className = 'classify-buttons-group';

                const curSelected = userMap[idx];
                const correctChoiceIdx = correctMap[idx];

                itemChoices.forEach((choice, choiceIdx) => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'classify-btn';
                    btn.textContent = choice;

                    const isUserPick = (curSelected !== undefined && (curSelected === choice || curSelected === choiceIdx));
                    if (isUserPick) btn.classList.add('selected');

                    if (isSubmitted) {
                        if (correctChoiceIdx !== undefined && correctChoiceIdx === choiceIdx) {
                            btn.classList.add('rev-correct');
                        } else if (isUserPick) {
                            btn.classList.add('rev-wrong');
                        }
                    }

                    if (!isSubmitted) {
                        btn.onclick = () => {
                            playSound('select');
                            btnGroup.querySelectorAll('.classify-btn').forEach(b => b.classList.remove('selected'));
                            btn.classList.add('selected');
                            if (!userSelections[currentIndex] || Array.isArray(userSelections[currentIndex])) {
                                userSelections[currentIndex] = {};
                            }
                            userSelections[currentIndex][idx] = choiceIdx;

                            const chk = document.querySelectorAll('.chk-node')[currentIndex];
                            if (chk) chk.classList.add('answered');
                        };
                    }

                    btnGroup.appendChild(btn);
                });

                row.appendChild(labelDiv);
                row.appendChild(btnGroup);
                classifyList.appendChild(row);
            });

            container.appendChild(classifyList);
        }

        // 4. Sequence (Sắp xếp thứ tự)
        function renderSequence(q, container) {
            const list = document.createElement('div');
            list.className = 'sequence-list';

            let currentOrder = userSelections[currentIndex];
            if (!Array.isArray(currentOrder) || currentOrder.length !== q.options.length) {
                currentOrder = q.options.map((_, idx) => idx);
                userSelections[currentIndex] = currentOrder;
            }

            const rawCorrect = serverCorrectAnswers[currentIndex] || [];
            const correctOrder = Array.isArray(rawCorrect) ? rawCorrect.map(Number) : q.options.map((_, idx) => idx);

            currentOrder.forEach((optIdx, pos) => {
                const opt = q.options[optIdx];
                const item = document.createElement('div');
                item.className = 'sequence-item';

                const imgHtml = opt.image_path ? `<img class="choice-img" src="${opt.image_path}" alt="Hình ${pos + 1}" onclick="zoomImage('${opt.image_path}')">` : '';

                if (isSubmitted) {
                    const isStepCorrect = (Number(opt.position) === pos);
                    item.classList.add(isStepCorrect ? 'rev-correct' : 'rev-wrong');

                    item.innerHTML = `
                        <div style="display:flex; align-items:center; gap:12px;">
                            <span class="opt-tag" style="background:${isStepCorrect ? '#16a34a' : '#dc2626'}; color:#fff;">${pos + 1}</span>
                            ${imgHtml}
                            <span>${opt.content || ''}</span>
                        </div>
                        <div>
                            <span style="font-size:12px; font-weight:900; padding:4px 10px; border-radius:8px; background:${isStepCorrect ? 'rgba(34,197,94,0.3)' : 'rgba(239,68,68,0.3)'}; color:${isStepCorrect ? '#4ade80' : '#fca5a5'};">
                                ${isStepCorrect ? '✓ Đúng vị trí' : `✕ Vị trí đúng: Bước ${(opt.position !== undefined ? opt.position : 0) + 1}`}
                            </span>
                        </div>
                    `;
                } else {
                    item.innerHTML = `
                        <div style="display:flex; align-items:center; gap:12px;">
                            <span class="opt-tag" style="background:#ef4444; color:#fff;">${pos + 1}</span>
                            ${imgHtml}
                            <span>${opt.content || ''}</span>
                        </div>
                        <div style="display:flex; gap:6px;">
                            <button type="button" class="btn-seq-move" onclick="moveSequence(${pos}, -1)" ${pos === 0 ? 'disabled style="opacity:0.3; cursor:not-allowed;"' : ''}>▲ Lên</button>
                            <button type="button" class="btn-seq-move" onclick="moveSequence(${pos}, 1)" ${pos === currentOrder.length - 1 ? 'disabled style="opacity:0.3; cursor:not-allowed;"' : ''}>▼ Xuống</button>
                        </div>
                    `;
                }
                list.appendChild(item);
            });

            container.appendChild(list);
        }

        window.moveSequence = (pos, dir) => {
            if (isSubmitted) return;
            const arr = userSelections[currentIndex];
            const targetPos = pos + dir;
            if (targetPos < 0 || targetPos >= arr.length) return;
            playSound('select');
            const temp = arr[pos];
            arr[pos] = arr[targetPos];
            arr[targetPos] = temp;
            renderQuestion();
        };

        // 5. Matching (Ghép nối khái niệm)
        let activeLeft = null;
        function renderMatching(q, container) {
            const board = document.createElement('div');
            board.className = 'matching-board';

            const userMatches = (typeof userSelections[currentIndex] === 'object' && !Array.isArray(userSelections[currentIndex])) 
                ? userSelections[currentIndex] : {};

            const leftCol = document.createElement('div');
            leftCol.style.display = 'grid';
            leftCol.style.gap = '10px';

            const rightCol = document.createElement('div');
            rightCol.style.display = 'grid';
            rightCol.style.gap = '10px';

            const leftList = [];
            const rightList = [];

            (q.options || []).forEach((opt, idx) => {
                let leftVal = opt.metadata?.left || opt.content || `Mục ${idx + 1}`;
                let rightVal = opt.metadata?.right || `Định nghĩa ${idx + 1}`;
                if (opt.content && opt.content.includes(':::')) {
                    const parts = opt.content.split(':::');
                    leftVal = parts[0].trim();
                    rightVal = parts[1].trim();
                }
                leftList.push({ id: idx, text: leftVal, img: opt.image_path });
                rightList.push({ id: idx, text: rightVal });
            });

            // Xáo trộn vị trí cột phải để tạo thử thách thực sự
            const shuffledRight = [...rightList];
            if (shuffledRight.length > 1) {
                const first = shuffledRight.shift();
                shuffledRight.push(first);
            }

            leftList.forEach((item, lIdx) => {
                const lCard = document.createElement('div');
                lCard.className = 'matching-card';

                const matchedRightId = userMatches[item.id];
                const isMatched = matchedRightId !== undefined && matchedRightId !== null;

                if (isMatched) lCard.classList.add('matched');
                if (activeLeft === item.id) lCard.classList.add('selected');

                if (isSubmitted) {
                    if (Number(matchedRightId) === Number(item.id)) {
                        lCard.classList.add('rev-correct');
                    } else {
                        lCard.classList.add('rev-wrong');
                    }
                }

                const lImg = item.img ? `<img class="choice-img" src="${item.img}" alt="Hình" onclick="event.stopPropagation(); zoomImage('${item.img}')">` : '';
                lCard.innerHTML = `
                    <span class="opt-tag">${item.id + 1}</span> 
                    ${lImg} 
                    <span style="flex:1;">${item.text}</span>
                    ${isMatched ? `<span style="font-size:11px; font-weight:900; background:#f59e0b; color:#1e1e1e; padding:2px 8px; border-radius:6px;">→ ${String.fromCharCode(65 + Number(matchedRightId))}</span>` : ''}
                `;

                if (!isSubmitted) {
                    lCard.onclick = () => {
                        playSound('click');
                        activeLeft = (activeLeft === item.id) ? null : item.id;
                        renderQuestion();
                    };
                }
                leftCol.appendChild(lCard);
            });

            shuffledRight.forEach((item, rIdx) => {
                const rCard = document.createElement('div');
                rCard.className = 'matching-card';
                const isMatchedToAny = Object.values(userMatches).some(val => Number(val) === Number(item.id));
                if (isMatchedToAny) rCard.classList.add('matched');

                rCard.innerHTML = `<span class="opt-tag">${String.fromCharCode(65 + item.id)}</span> <span style="flex:1;">${item.text}</span>`;

                if (!isSubmitted) {
                    rCard.onclick = () => {
                        if (activeLeft !== null) {
                            playSound('select');
                            if (!userSelections[currentIndex] || Array.isArray(userSelections[currentIndex])) {
                                userSelections[currentIndex] = {};
                            }
                            userSelections[currentIndex][activeLeft] = item.id;
                            activeLeft = null;
                            renderQuestion();
                        }
                    };
                }
                rightCol.appendChild(rCard);
            });

            board.appendChild(leftCol);
            board.appendChild(rightCol);
            container.appendChild(board);
        }

        // 6. Hotspot (Nhấp trên hình - Hỗ trợ chuẩn hóa tọa độ và Review Mode trực quan)
        function renderHotspot(q, container) {
            const imgPath = q.configuration?.image_path || (q.assets && q.assets.length ? q.assets[0].path : null);
            if (!imgPath) {
                container.innerHTML = '<div style="color:#f87171; text-align:center; padding:20px;">Không tìm thấy hình ảnh Hotspot.</div>';
                return;
            }

            const outer = document.createElement('div');
            outer.className = 'hotspot-outer-container';

            const hint = document.createElement('div');
            hint.style.color = '#e2e8f0';
            hint.style.fontSize = '13.5px';
            hint.style.fontWeight = '800';
            hint.innerHTML = isSubmitted 
                ? '<span>🎯</span> <i>Xem lại vị trí đáp án đúng (khung xanh) và vị trí em đã chọn:</i>'
                : '<span>🎯</span> <i>Nhấp chuột trực tiếp vào biểu tượng hoặc vị trí đúng trên hình ảnh:</i>';
            outer.appendChild(hint);

            const wrap = document.createElement('div');
            wrap.className = 'hotspot-wrapper';

            const img = document.createElement('img');
            img.src = imgPath;
            img.alt = 'Hotspot';
            img.id = 'hotspot-img';
            wrap.appendChild(img);

            const selectedIdx = userSelections[currentIndex];
            const rawCorrect = serverCorrectAnswers[currentIndex];
            const correctPos = (rawCorrect !== undefined && rawCorrect !== null) ? Number(rawCorrect) : 0;

            if (isSubmitted) {
                // Khung đáp án đúng
                let correctOpt = (q.options || []).find(o => Number(o.position) === correctPos || o.is_correct);
                if (!correctOpt && q.options && q.options.length > 0) {
                    correctOpt = q.options[correctPos] || q.options[0];
                }
                if (correctOpt) {
                    const cRect = getNormRect(correctOpt.metadata?.rect || correctOpt.rect);
                    if (cRect) {
                        const correctBox = document.createElement('div');
                        correctBox.className = 'hotspot-correct-box';
                        correctBox.style.left = cRect.x + '%';
                        correctBox.style.top = cRect.y + '%';
                        correctBox.style.width = cRect.w + '%';
                        correctBox.style.height = cRect.h + '%';
                        correctBox.innerHTML = `<div class="hotspot-correct-badge">✓ ĐÁP ÁN ĐÚNG</div>`;
                        wrap.appendChild(correctBox);
                    }
                }

                // Điểm học sinh đã nhấp
                const selectedOption = (q.options || []).find(option => Number(option.position) === Number(selectedIdx));
                if (selectedIdx !== undefined && selectedIdx !== null && selectedOption) {
                    const uRect = getNormRect(selectedOption.metadata?.rect || selectedOption.rect);
                    if (uRect) {
                        const selectedPoint = hotspotClickPoints[currentIndex];
                        const userMarker = document.createElement('div');
                        const isHit = (Number(selectedIdx) === correctPos || !!selectedOption.is_correct);
                        userMarker.className = isHit ? 'hotspot-target-marker rev-hit' : 'hotspot-target-marker rev-miss';
                        userMarker.style.left = (selectedPoint?.x ?? (uRect.x + uRect.w / 2)) + '%';
                        userMarker.style.top = (selectedPoint?.y ?? (uRect.y + uRect.h / 2)) + '%';
                        userMarker.style.display = 'grid';
                        userMarker.innerHTML = isHit ? '✓' : '✕';
                        wrap.appendChild(userMarker);
                    }
                }
            } else {
                const marker = document.createElement('div');
                marker.className = 'hotspot-target-marker';
                wrap.appendChild(marker);

                const selectedOption = (q.options || []).find(option => Number(option.position) === Number(selectedIdx));
                if (selectedIdx !== undefined && selectedIdx !== null && selectedOption) {
                    const rect = getNormRect(selectedOption.metadata?.rect || selectedOption.rect);
                    if (rect) {
                        const selectedPoint = hotspotClickPoints[currentIndex];
                        marker.style.left = (selectedPoint?.x ?? (rect.x + rect.w / 2)) + '%';
                        marker.style.top = (selectedPoint?.y ?? (rect.y + rect.h / 2)) + '%';
                        marker.style.display = 'grid';
                    }
                }

                wrap.onclick = (e) => {
                    playSound('click');
                    const rect = img.getBoundingClientRect();
                    const ptX = ((e.clientX - rect.left) / rect.width) * 100;
                    const ptY = ((e.clientY - rect.top) / rect.height) * 100;

                    let matchIdx = -1;
                    (q.options || []).forEach((opt, idx) => {
                        const r = getNormRect(opt.metadata?.rect || opt.rect);
                        if (r) {
                            if (ptX >= r.x && ptX <= (r.x + r.w) && ptY >= r.y && ptY <= (r.y + r.h)) {
                                matchIdx = idx;
                            }
                        }
                    });

                    if (matchIdx !== -1) {
                        const targetArea = q.options[matchIdx];
                        userSelections[currentIndex] = Number(targetArea.position !== undefined ? targetArea.position : matchIdx);
                        hotspotClickPoints[currentIndex] = { x: ptX, y: ptY };
                        marker.style.left = ptX + '%';
                        marker.style.top = ptY + '%';
                        marker.style.display = 'grid';

                        const chk = document.querySelectorAll('.chk-node')[currentIndex];
                        if (chk) chk.classList.add('answered');
                    }
                };
            }

            outer.appendChild(wrap);
            container.appendChild(outer);
        }

        document.getElementById('btn-prev').onclick = () => {
            if (currentIndex > 0) { 
                playSound('click');
                currentIndex--; 
                renderQuestion(); 
            }
        };
        document.getElementById('btn-next').onclick = () => {
            if (currentIndex < questions.length - 1) { 
                playSound('click');
                currentIndex++; 
                renderQuestion(); 
            }
        };

        document.getElementById('btn-submit').onclick = () => {
            if (isSubmitted) return;
            isSubmitted = true;

            fetch(submitUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    answers: userSelections,
                    question_ids: questions.map(q => q.id)
                })
            })
            .then(res => res.json())
            .then(data => {
                serverResults = data.results || [];
                serverCorrectAnswers = data.correct_answers_data || {};

                document.getElementById('stat-resolved').textContent = `${data.resolved_count} / ${data.total_questions}`;
                document.getElementById('stat-stars').textContent = `+${data.earned_stars} ⭐`;

                if (data.resolved_count > 0) {
                    playSound('victory');
                    document.getElementById('res-icon').textContent = '👑';
                    document.getElementById('res-title').textContent = 'Xuất Sắc! Phục Thù Thành Công!';
                    document.getElementById('res-sub').textContent = `Em đã khắc phục được ${data.resolved_count} câu hỏi bị sai và nhận thêm ${data.earned_stars} Sao Vàng!`;
                } else {
                    playSound('fail');
                    document.getElementById('res-icon').textContent = '💪';
                    document.getElementById('res-title').textContent = 'Cố Gắng Lên Em Nhé!';
                    document.getElementById('res-sub').textContent = 'Hãy xem lại đáp án và luyện tập thêm một lần nữa nhé!';
                }

                // Đổi màu checkpoint node theo kết quả
                const chkNodes = document.querySelectorAll('.chk-node');
                (data.results || []).forEach((isCorrect, idx) => {
                    if (chkNodes[idx]) {
                        chkNodes[idx].classList.add(isCorrect ? 'review-correct' : 'review-wrong');
                    }
                });

                document.getElementById('score-modal').classList.add('show');
            })
            .catch(err => {
                console.error(err);
                alert('Có lỗi xảy ra khi nộp bài.');
            });
        };

        document.addEventListener('DOMContentLoaded', () => {
            initCheckpoints();
            renderQuestion();
        });
    </script>
</body>
</html>
