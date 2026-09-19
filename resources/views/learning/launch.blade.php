<!doctype html>
<html lang="vi" translate="no" class="notranslate">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="google" content="notranslate">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $practiceTest->name }} — {{ $practiceTest->topic->name }} (Khối {{ $practiceTest->topic->level->grade }})</title>
    <meta name="description" content="Đấu trường thử thách số thông minh IC3 GS6 — {{ $practiceTest->topic->name }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700;800&family=Nunito:wght@600;700;800;900;1000&display=swap" rel="stylesheet">
    <style>
        :root {
            --grade-accent: #00f2fe;
            --grade-glow: #4facfe;
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
            background: #061021;
            color: var(--text-main);
            height: 100vh;
            max-height: 100vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(0, 242, 254, 0.22) 0%, transparent 45%),
                radial-gradient(circle at 90% 80%, rgba(161, 140, 209, 0.25) 0%, transparent 50%),
                radial-gradient(circle at 50% 50%, rgba(24, 76, 144, 0.3) 0%, transparent 70%);
            background-attachment: fixed;
        }

        #star-canvas { position: fixed; inset: 0; pointer-events: none; z-index: 1; }

        /* Top Game Bar - Compact & Sleek */
        .arena-topbar {
            flex-shrink: 0;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 8px 24px;
            background: linear-gradient(180deg, rgba(8, 25, 52, 0.95), rgba(6, 18, 38, 0.9));
            backdrop-filter: blur(18px);
            border-bottom: 3px solid #00f2fe;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.5);
        }

        .bar-left, .bar-center, .bar-right { display: flex; align-items: center; gap: 12px; }

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
            background: linear-gradient(135deg, rgba(0, 242, 254, 0.15), rgba(79, 172, 254, 0.1));
            border: 1.5px solid rgba(0, 242, 254, 0.4);
            border-radius: 12px;
            font-size: 13px;
            font-weight: 900;
            color: #e0f7ff;
        }
        .stage-pill-info span { color: var(--gold); }

        .bar-center { flex: 1; justify-content: center; }
        .arena-brand {
            font-family: 'Fredoka', cursive, sans-serif;
            font-size: 20px;
            font-weight: 700;
            color: #ffe658;
            text-shadow: 0 2px 0 #7e4200, 1px 0 #7e4200, -1px 0 #7e4200, 0 0 15px rgba(255, 230, 88, 0.5);
            display: flex;
            align-items: center;
            gap: 8px;
            letter-spacing: 0.8px;
        }
        .arena-subtitle {
            font-size: 12px;
            color: var(--text-muted);
            font-weight: 800;
            margin-left: 6px;
        }

        .bar-right { justify-content: flex-end; }

        .hud-timer-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            background: rgba(0, 0, 0, 0.4);
            border: 2px solid #38bdf8;
            border-radius: 12px;
            color: #38bdf8;
            font-size: 14px;
            font-weight: 900;
            box-shadow: 0 0 12px rgba(56, 189, 248, 0.3);
        }

        .btn-ctrl {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            border: 2px solid rgba(255, 255, 255, 0.25);
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            font-size: 17px;
            display: grid;
            place-items: center;
            cursor: pointer;
            transition: all 0.15s;
        }
        .btn-ctrl:hover { background: rgba(255, 255, 255, 0.25); transform: translateY(-1px); }

        /* Main Viewport Container - Balanced Width & Height */
        .arena-container {
            flex: 1;
            min-height: 0;
            position: relative;
            z-index: 2;
            width: min(960px, 94%);
            margin: 8px auto 10px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            justify-content: space-between;
        }

        /* Review Mode Banner - Compact */
        .review-mode-bar {
            flex-shrink: 0;
            display: none;
            align-items: center;
            justify-content: space-between;
            padding: 8px 18px;
            background: linear-gradient(135deg, #1e3a8a, #0369a1);
            border: 2.5px solid #60a5fa;
            border-radius: 14px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.35);
        }
        .review-mode-bar.show { display: flex; }
        .review-mode-bar span { font-weight: 900; font-size: 14px; color: #ffffff; letter-spacing: 0.5px; }

        /* Checkpoint Navigation Bar */
        .checkpoint-container {
            flex-shrink: 0;
            display: flex;
            align-items: center;
            gap: 6px;
            overflow-x: auto;
            padding: 4px 4px;
            scrollbar-width: thin;
        }
        .checkpoint-container::-webkit-scrollbar { height: 4px; }
        .checkpoint-container::-webkit-scrollbar-thumb { background: rgba(0, 242, 254, 0.5); border-radius: 999px; }

        .cp-node {
            flex-shrink: 0;
            width: 32px;
            height: 32px;
            border-radius: 9px;
            border: 2px solid rgba(255, 255, 255, 0.22);
            background: rgba(255, 255, 255, 0.08);
            color: var(--text-muted);
            font-size: 13px;
            font-weight: 1000;
            display: grid;
            place-items: center;
            cursor: pointer;
            transition: all 0.18s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.25);
            position: relative;
        }
        .cp-node:hover {
            border-color: #00f2fe;
            color: #fff;
            transform: scale(1.06);
            background: rgba(0, 242, 254, 0.2);
            z-index: 2;
        }
        .cp-node.answered {
            background: linear-gradient(135deg, #0284c7, #0369a1);
            border-color: #38bdf8;
            color: #ffffff;
        }
        .cp-node.active {
            border-color: #ffeaa7;
            background: linear-gradient(180deg, #ffc048, #ff9f1a);
            color: #4a2700;
            transform: scale(1.1);
            box-shadow: 0 0 16px rgba(255, 159, 26, 0.8), 0 3px 0 #b35600;
            z-index: 5;
        }
        .cp-node.rev-correct {
            border-color: #86efac;
            background: linear-gradient(180deg, #22c55e, #16a34a);
            color: #fff;
            box-shadow: 0 0 12px rgba(34, 197, 94, 0.6);
        }
        .cp-node.rev-wrong {
            border-color: #fca5a5;
            background: linear-gradient(180deg, #ef4444, #dc2626);
            color: #fff;
            box-shadow: 0 0 12px rgba(239, 68, 68, 0.6);
        }

        /* Quest Card (Main Question Box) - Rich & Proportionate */
        .quest-card {
            flex: 1;
            min-height: 0;
            background: linear-gradient(175deg, #133a63 0%, #0d2847 45%, #081a30 100%);
            border: 3.5px solid #4facfe;
            border-radius: 24px;
            box-shadow: 0 18px 45px rgba(0, 0, 0, 0.55), 0 0 25px rgba(79, 172, 254, 0.3);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            animation: cardEntrance 0.3s ease-out;
        }
        @keyframes cardEntrance {
            from { opacity: 0; transform: translateY(12px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* Question Header - Clean & Bold */
        .quest-header {
            flex-shrink: 0;
            padding: 8px 22px 6px;
            background: linear-gradient(90deg, rgba(24, 88, 150, 0.75), rgba(48, 38, 110, 0.6));
            border-bottom: 2px solid rgba(0, 242, 254, 0.25);
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .quest-meta-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .type-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 3px 10px;
            background: linear-gradient(135deg, rgba(255, 234, 167, 0.25), rgba(255, 192, 72, 0.15));
            border: 1.5px solid #ffeaa7;
            border-radius: 8px;
            color: #ffeaa7;
            font-size: 11.5px;
            font-weight: 1000;
            letter-spacing: 0.5px;
        }
        .quest-number-label {
            color: var(--text-muted);
            font-size: 13px;
            font-weight: 900;
        }
        .quest-title {
            font-size: 16.5px;
            line-height: 1.35;
            color: #ffffff;
            font-weight: 900;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
        }

        /* Question Content & Scrollable Body */
        .quest-body {
            flex: 1;
            min-height: 0;
            padding: 16px 28px 20px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 16px;
            overflow-y: auto;
            overflow-x: hidden;
            scrollbar-width: thin;
        }
        .quest-body::-webkit-scrollbar { width: 6px; }
        .quest-body::-webkit-scrollbar-track { background: rgba(0, 0, 0, 0.2); border-radius: 999px; }
        .quest-body::-webkit-scrollbar-thumb { background: rgba(0, 242, 254, 0.5); border-radius: 999px; }

        .quest-image-box {
            text-align: center;
            padding: 6px;
            background: rgba(0, 0, 0, 0.35);
            border: 2px solid rgba(0, 242, 254, 0.3);
            border-radius: 14px;
            max-width: 480px;
            margin: 0 auto;
            flex-shrink: 0;
        }
        .quest-image-box img {
            max-width: 100%;
            max-height: 120px;
            object-fit: contain;
            border-radius: 8px;
            cursor: zoom-in;
            transition: transform 0.2s;
        }
        .quest-image-box img:hover { transform: scale(1.03); }

        /* Answer Grid - 2 columns spacious & comfortable */
        .answers-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 18px;
            width: 100%;
            margin: auto 0;
            padding: 8px 0;
        }
        .answers-grid.single-column { grid-template-columns: 1fr; gap: 14px; }

        /* 3D Glossy Bright Choice Button */
        .choice-btn {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 14px 22px;
            background: #ffffff;
            border: 2.5px solid #e2e8f0;
            border-radius: 18px;
            color: #0f172a;
            font-family: inherit;
            font-size: 16px;
            font-weight: 850;
            text-align: left;
            cursor: pointer;
            transition: all 0.15s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 0 #cbd5e1, 0 6px 14px rgba(0, 0, 0, 0.12);
            min-height: 64px;
        }
        .choice-btn:hover {
            background: #f8fafc;
            border-color: #38bdf8;
            transform: translateY(-2px);
            box-shadow: 0 6px 0 #0284c7, 0 8px 18px rgba(2, 132, 199, 0.25);
        }
        .choice-btn.selected {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;
            border-color: #ffeaa7 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 0 #1e3a8a, 0 0 20px rgba(37, 99, 235, 0.7) !important;
            transform: translateY(-2px);
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }
        .choice-btn.selected .choice-text { color: #ffffff !important; }

        .choice-btn.rev-correct {
            background: linear-gradient(180deg, #f0fdf4 0%, #dcfce7 100%) !important;
            border-color: #16a34a !important;
            color: #14532d !important;
            box-shadow: 0 4px 0 #15803d, 0 0 18px rgba(34, 197, 94, 0.45) !important;
        }
        .choice-btn.rev-correct .choice-text { color: #14532d !important; }
        .choice-btn.rev-wrong {
            background: linear-gradient(180deg, #fef2f2 0%, #fee2e2 100%) !important;
            border-color: #dc2626 !important;
            color: #991b1b !important;
            box-shadow: 0 4px 0 #b91c1c, 0 0 18px rgba(239, 68, 68, 0.45) !important;
        }
        .choice-btn.rev-wrong .choice-text { color: #991b1b !important; }

        .choice-key {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            border: 2px solid #ffffff;
            display: grid;
            place-items: center;
            font-size: 15.5px;
            font-weight: 1000;
            color: #ffffff;
            flex-shrink: 0;
            transition: all 0.15s;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
            background: linear-gradient(135deg, #0ea5e9, #0284c7);
        }
        .choice-btn:nth-child(1) .choice-key { background: linear-gradient(135deg, #0ea5e9, #0284c7); box-shadow: 0 3px 0 #0369a1; }
        .choice-btn:nth-child(2) .choice-key { background: linear-gradient(135deg, #8b5cf6, #6d28d9); box-shadow: 0 3px 0 #5b21b6; }
        .choice-btn:nth-child(3) .choice-key { background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 3px 0 #047857; }
        .choice-btn:nth-child(4) .choice-key { background: linear-gradient(135deg, #f97316, #c2410c); box-shadow: 0 3px 0 #9a3412; }
        .choice-btn:nth-child(5) .choice-key { background: linear-gradient(135deg, #ec4899, #be185d); box-shadow: 0 3px 0 #9d174d; }
        .choice-btn:nth-child(6) .choice-key { background: linear-gradient(135deg, #06b6d4, #0e7490); box-shadow: 0 3px 0 #155e75; }

        .choice-btn.selected .choice-key {
            background: #ffeaa7 !important;
            color: #4a2700 !important;
            border-color: #ffffff !important;
            box-shadow: 0 0 14px #facc15 !important;
        }
        .choice-btn.rev-correct .choice-key { background: #16a34a; color: #fff; box-shadow: 0 0 12px #22c55e; }
        .choice-btn.rev-wrong .choice-key { background: #dc2626; color: #fff; box-shadow: 0 0 12px #ef4444; }

        .choice-content {
            flex: 1;
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: flex-start;
            gap: 12px;
            width: 100%;
            min-width: 0;
        }
        .choice-img {
            max-width: 130px;
            max-height: 75px;
            width: auto;
            height: auto;
            object-fit: contain;
            border-radius: 8px;
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            padding: 3px;
            flex-shrink: 0;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }
        .choice-text {
            line-height: 1.35;
            text-align: left;
            font-size: 15px;
            font-weight: 850;
            color: inherit;
            word-break: break-word;
        }

        /* ⚡ Neon Electric LED Laser Wire Matching Game */
        .neon-wire-container {
            position: relative;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            width: 100%;
            max-width: 940px;
            margin: auto 0;
            user-select: none;
            padding: 10px 0;
        }
        .wire-column {
            display: flex;
            flex-direction: column;
            gap: 14px;
            z-index: 5;
        }
        .wire-card {
            background: #ffffff;
            border: 2.5px solid #cbd5e1;
            border-radius: 16px;
            padding: 12px 18px;
            color: #0f172a;
            font-size: 14.5px;
            font-weight: 850;
            line-height: 1.35;
            min-height: 56px;
            display: flex;
            align-items: center;
            gap: 12px;
            position: relative;
            box-shadow: 0 4px 0 #cbd5e1, 0 6px 14px rgba(0, 0, 0, 0.08);
            cursor: pointer;
            transition: all 0.18s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .wire-card:hover {
            transform: translateY(-2px);
            border-color: #38bdf8;
            box-shadow: 0 6px 0 #38bdf8, 0 8px 20px rgba(56, 189, 248, 0.25);
        }
        .wire-card.selected {
            border-color: #f59e0b !important;
            background: #fffbeb !important;
            color: #b45309 !important;
            box-shadow: 0 4px 0 #d97706, 0 0 22px rgba(245, 158, 11, 0.65) !important;
            transform: scale(1.02);
            animation: pulseWireSelect 1.2s infinite alternate ease-in-out;
        }
        @keyframes pulseWireSelect {
            from { box-shadow: 0 4px 0 #d97706, 0 0 12px rgba(245, 158, 11, 0.4); }
            to { box-shadow: 0 4px 0 #d97706, 0 0 24px rgba(245, 158, 11, 0.8); }
        }
        .wire-idx-badge {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #f1f5f9;
            color: #475569;
            display: grid;
            place-items: center;
            font-size: 12px;
            font-weight: 900;
            flex-shrink: 0;
            border: 1.5px solid #cbd5e1;
        }
        .wire-port {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #94a3b8;
            border: 2.5px solid #ffffff;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.3);
            transition: all 0.2s ease;
            z-index: 10;
        }
        .wire-left-card .wire-port { right: -9px; }
        .wire-right-card .wire-port { left: -9px; }

        .wire-card.pair-theme-0 { border-color: #00f2fe; background: #f0fdff; box-shadow: 0 4px 0 #0284c7, 0 0 16px rgba(0, 242, 254, 0.35); }
        .wire-card.pair-theme-0 .wire-port { background: #00f2fe; box-shadow: 0 0 14px #00f2fe; border-color: #0284c7; }
        .wire-card.pair-theme-0 .wire-idx-badge { background: #00f2fe; color: #082f49; border-color: #0284c7; }

        .wire-card.pair-theme-1 { border-color: #f43f5e; background: #fff1f2; box-shadow: 0 4px 0 #be185d, 0 0 16px rgba(244, 63, 94, 0.35); }
        .wire-card.pair-theme-1 .wire-port { background: #f43f5e; box-shadow: 0 0 14px #f43f5e; border-color: #be185d; }
        .wire-card.pair-theme-1 .wire-idx-badge { background: #f43f5e; color: #ffffff; border-color: #be185d; }

        .wire-card.pair-theme-2 { border-color: #10b981; background: #f0fdf4; box-shadow: 0 4px 0 #047857, 0 0 16px rgba(168, 85, 247, 0.35); }
        .wire-card.pair-theme-2 .wire-port { background: #10b981; box-shadow: 0 0 14px #10b981; border-color: #047857; }
        .wire-card.pair-theme-2 .wire-idx-badge { background: #10b981; color: #ffffff; border-color: #047857; }

        .wire-card.pair-theme-3 { border-color: #f59e0b; background: #fffbeb; box-shadow: 0 4px 0 #b45309, 0 0 16px rgba(245, 158, 11, 0.35); }
        .wire-card.pair-theme-3 .wire-port { background: #f59e0b; box-shadow: 0 0 14px #f59e0b; border-color: #b45309; }
        .wire-card.pair-theme-3 .wire-idx-badge { background: #f59e0b; color: #ffffff; border-color: #b45309; }

        .wire-card.pair-theme-4 { border-color: #a855f7; background: #faf5ff; box-shadow: 0 4px 0 #7e22ce, 0 0 16px rgba(168, 85, 247, 0.35); }
        .wire-card.pair-theme-4 .wire-port { background: #a855f7; box-shadow: 0 0 14px #a855f7; border-color: #7e22ce; }
        .wire-card.pair-theme-4 .wire-idx-badge { background: #a855f7; color: #ffffff; border-color: #7e22ce; }

        .wire-svg-canvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 4;
            overflow: visible;
        }
        @keyframes electricFlow {
            from { stroke-dashoffset: 40; }
            to { stroke-dashoffset: 0; }
        }
        .wire-laser-line {
            fill: none;
            stroke-linecap: round;
            animation: electricFlow 1.2s linear infinite;
        }

        /* 📋 Classify (MultipleChoiceText) Modern Concept Segment Cards */
        .classify-list {
            display: flex;
            flex-direction: column;
            gap: 14px;
            width: 100%;
            max-width: 940px;
            margin: auto 0;
        }
        .classify-item {
            display: grid;
            grid-template-columns: 1.15fr 1.25fr;
            align-items: center;
            gap: 18px;
            padding: 12px 20px;
            background: #ffffff;
            border: 2.5px solid #e2e8f0;
            border-radius: 18px;
            box-shadow: 0 3px 0 #cbd5e1, 0 4px 12px rgba(0, 0, 0, 0.06);
            transition: all 0.2s ease;
            min-height: 56px;
        }
        .classify-item:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 0 #cbd5e1, 0 6px 16px rgba(0, 0, 0, 0.08);
        }
        .classify-item.row-theme-0 { border-left: 6px solid #00f2fe; }
        .classify-item.row-theme-0 .classify-idx-badge {
            background: linear-gradient(135deg, #00f2fe, #0284c7);
            color: #041f3d;
            border-color: #00f2fe;
            box-shadow: 0 0 10px rgba(0, 242, 254, 0.45);
        }
        .classify-item.row-theme-1 { border-left: 6px solid #a855f7; }
        .classify-item.row-theme-1 .classify-idx-badge {
            background: linear-gradient(135deg, #c084fc, #7e22ce);
            color: #ffffff;
            border-color: #a855f7;
            box-shadow: 0 0 10px rgba(168, 85, 247, 0.45);
        }
        .classify-item.row-theme-2 { border-left: 6px solid #10b981; }
        .classify-item.row-theme-2 .classify-idx-badge {
            background: linear-gradient(135deg, #34d399, #059669);
            color: #ffffff;
            border-color: #10b981;
            box-shadow: 0 0 10px rgba(168, 85, 247, 0.45);
        }
        .classify-item.row-theme-3 { border-left: 6px solid #f59e0b; }
        .classify-item.row-theme-3 .classify-idx-badge {
            background: linear-gradient(135deg, #fbbf24, #d97706);
            color: #ffffff;
            border-color: #f59e0b;
            box-shadow: 0 0 10px rgba(245, 158, 11, 0.45);
        }
        .classify-item.row-theme-4 { border-left: 6px solid #f43f5e; }
        .classify-item.row-theme-4 .classify-idx-badge {
            background: linear-gradient(135deg, #fb7185, #e11d48);
            color: #ffffff;
            border-color: #f43f5e;
            box-shadow: 0 0 10px rgba(244, 63, 94, 0.45);
        }
        .classify-idx-badge {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            font-size: 13px;
            font-weight: 900;
            flex-shrink: 0;
            border: 2px solid transparent;
        }
        .classify-label {
            font-size: 15px;
            font-weight: 850;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 10px;
            line-height: 1.35;
        }
        .classify-buttons-group {
            display: flex;
            gap: 8px;
            align-items: center;
            width: 100%;
        }
        .classify-btn {
            flex: 1;
            min-height: 42px;
            padding: 6px 12px;
            border-radius: 12px;
            border: 2px solid #cbd5e1;
            background: #f8fafc;
            color: #475569;
            font-size: 13.5px;
            font-weight: 850;
            cursor: pointer;
            transition: all 0.15s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            user-select: none;
            box-shadow: 0 2px 0 #cbd5e1;
        }
        .classify-btn:hover {
            background: #ffffff;
            border-color: #94a3b8;
            transform: translateY(-2px);
            box-shadow: 0 4px 0 #94a3b8;
        }
        .classify-btn.theme-0.selected {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8) !important;
            border-color: #1d4ed8 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 0 #1e40af, 0 0 16px rgba(59, 130, 246, 0.45) !important;
            transform: scale(1.02);
        }
        .classify-btn.theme-1.selected {
            background: linear-gradient(135deg, #a855f7, #7e22ce) !important;
            border-color: #7e22ce !important;
            color: #ffffff !important;
            box-shadow: 0 4px 0 #6b21a8, 0 0 16px rgba(168, 85, 247, 0.45) !important;
            transform: scale(1.02);
        }
        .classify-btn.theme-2.selected {
            background: linear-gradient(135deg, #10b981, #047857) !important;
            border-color: #047857 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 0 #065f46, 0 0 16px rgba(168, 85, 247, 0.45) !important;
            transform: scale(1.02);
        }
        .classify-btn.theme-3.selected {
            background: linear-gradient(135deg, #f59e0b, #d97706) !important;
            border-color: #d97706 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 0 #b45309, 0 0 16px rgba(245, 158, 11, 0.45) !important;
            transform: scale(1.02);
        }
        .classify-btn.rev-correct {
            background: #dcfce7 !important;
            border-color: #16a34a !important;
            color: #14532d !important;
            font-weight: 900;
        }
        .classify-btn.rev-wrong {
            background: #fee2e2 !important;
            border-color: #dc2626 !important;
            color: #991b1b !important;
            font-weight: 900;
        }

        /* 🎯 Hotspot Arena */
        .hotspot-outer-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            max-width: 860px;
            margin: 0 auto;
        }
        .hotspot-instruction-hint {
            font-size: 13px;
            font-weight: 850;
            color: #38bdf8;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .hotspot-wrapper {
            position: relative;
            display: inline-block;
            margin: 0 auto;
            border-radius: 14px;
            overflow: hidden;
            border: 3px solid #38bdf8;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4), 0 0 20px rgba(56, 189, 248, 0.25);
            cursor: crosshair;
            user-select: none;
            background: #041226;
        }
        .hotspot-wrapper img {
            display: block;
            max-width: 100%;
            max-height: 380px;
            object-fit: contain;
        }
        .hotspot-target-marker {
            position: absolute;
            width: 32px;
            height: 32px;
            margin-left: -16px;
            margin-top: -16px;
            border-radius: 50%;
            border: 3px solid #ffe135;
            background: rgba(255, 225, 53, 0.35);
            box-shadow: 0 0 18px rgba(255, 225, 53, 0.9);
            display: none;
            place-items: center;
            pointer-events: none;
            z-index: 20;
            animation: pulseMarker 1s infinite alternate;
        }
        @keyframes pulseMarker {
            from { transform: scale(0.9); box-shadow: 0 0 10px rgba(255, 225, 53, 0.6); }
            to { transform: scale(1.15); box-shadow: 0 0 22px rgba(255, 225, 53, 1); }
        }

        /* 🔢 Sequence Question */
        .sequence-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
            width: 100%;
            max-width: 860px;
            margin: 0 auto;
        }
        .sequence-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 10px 16px;
            background: #ffffff;
            border: 2px solid #e2e8f0;
            border-radius: 14px;
            color: #0f172a;
            font-size: 14.5px;
            font-weight: 850;
            box-shadow: 0 3px 0 #cbd5e1;
            transition: all 0.15s;
        }
        .btn-seq-move {
            padding: 5px 10px;
            border-radius: 8px;
            border: 1.5px solid #cbd5e1;
            background: #f8fafc;
            color: #334155;
            font-weight: 900;
            font-size: 12px;
            cursor: pointer;
            transition: 0.15s;
        }
        .btn-seq-move:hover {
            background: #0284c7;
            border-color: #0284c7;
            color: #fff;
        }

                /* 🎯 Hotspot Review Mode Styling */
        .hotspot-correct-box {
            position: absolute;
            border: 3px solid #22c55e;
            background: rgba(34, 197, 94, 0.25);
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(34, 197, 94, 0.8), inset 0 0 12px rgba(34, 197, 94, 0.35);
            pointer-events: none;
            z-index: 25;
            animation: pulseGreenBox 1.5s infinite alternate ease-in-out;
        }
        @keyframes pulseGreenBox {
            from { box-shadow: 0 0 12px rgba(34, 197, 94, 0.6); }
            to { box-shadow: 0 0 28px rgba(34, 197, 94, 1); }
        }
        .hotspot-correct-badge {
            position: absolute;
            top: -24px;
            left: 50%;
            transform: translateX(-50%);
            background: #16a34a;
            color: #ffffff;
            font-size: 11px;
            font-weight: 900;
            padding: 2px 8px;
            border-radius: 6px;
            white-space: nowrap;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
        }
        .hotspot-target-marker.rev-hit {
            border-color: #22c55e !important;
            background: rgba(34, 197, 94, 0.85) !important;
            color: #ffffff !important;
            font-size: 18px !important;
            font-weight: 1000 !important;
            box-shadow: 0 0 20px rgba(34, 197, 94, 1) !important;
        }
        .hotspot-target-marker.rev-miss {
            border-color: #ef4444 !important;
            background: rgba(239, 68, 68, 0.9) !important;
            color: #ffffff !important;
            font-size: 18px !important;
            font-weight: 1000 !important;
            box-shadow: 0 0 20px rgba(239, 68, 68, 1) !important;
        }

        /* ⚡ Matching Review Mode Styling */
        .wire-card.rev-pair-correct {
            border-color: #16a34a !important;
            background: #f0fdf4 !important;
            color: #14532d !important;
            box-shadow: 0 4px 0 #15803d, 0 0 16px rgba(34, 197, 94, 0.35) !important;
        }
        .wire-card.rev-pair-correct .wire-port {
            background: #22c55e !important;
            border-color: #15803d !important;
            box-shadow: 0 0 12px #22c55e !important;
        }
        .wire-card.rev-pair-correct .wire-idx-badge {
            background: #16a34a !important;
            color: #ffffff !important;
            border-color: #15803d !important;
        }
        .wire-card.rev-pair-wrong {
            border-color: #dc2626 !important;
            background: #fef2f2 !important;
            color: #991b1b !important;
            box-shadow: 0 4px 0 #b91c1c, 0 0 16px rgba(239, 68, 68, 0.35) !important;
        }
        .wire-card.rev-pair-wrong .wire-port {
            background: #ef4444 !important;
            border-color: #b91c1c !important;
            box-shadow: 0 0 12px #ef4444 !important;
        }
        .wire-card.rev-pair-wrong .wire-idx-badge {
            background: #dc2626 !important;
            color: #ffffff !important;
            border-color: #b91c1c !important;
        }

        /* 🔢 Sequence Review Mode Styling */
        .sequence-item.rev-correct {
            background: #f0fdf4 !important;
            border-color: #16a34a !important;
            box-shadow: 0 3px 0 #15803d, 0 0 14px rgba(34, 197, 94, 0.25) !important;
        }
        .sequence-item.rev-wrong {
            background: #fef2f2 !important;
            border-color: #dc2626 !important;
            box-shadow: 0 3px 0 #b91c1c, 0 0 14px rgba(239, 68, 68, 0.25) !important;
        }
        .seq-status-badge {
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 12.5px;
            font-weight: 900;
        }
        .seq-status-badge.correct {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
        }
        .seq-status-badge.wrong {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

                /* 📱 Clean Embedded Preview Mode (when inside Studio Preview Iframe or ?preview=1) */
        body.is-iframe-preview .arena-topbar {
            display: none !important;
        }
        body.is-iframe-preview .review-mode-bar {
            display: none !important;
        }
        body.is-iframe-preview .arena-container {
            margin: 6px auto 10px !important;
            height: calc(100vh - 16px) !important;
            max-height: calc(100vh - 16px) !important;
        }
        body.is-iframe-preview #btn-submit-main {
            display: none !important;
        }
        body.is-iframe-preview #btn-preview-check-footer {
            display: inline-flex !important;
        }

        /* Footer & Navigation Controls */
        .quest-footer {
            flex-shrink: 0;
            padding: 10px 22px;
            background: linear-gradient(180deg, rgba(8, 25, 52, 0.95), rgba(4, 14, 30, 0.98));
            border-top: 2px solid rgba(0, 242, 254, 0.25);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .footer-left, .footer-center, .footer-right { display: flex; align-items: center; gap: 10px; }
        .footer-center { flex: 1; justify-content: center; }

        .btn-game-action {
            padding: 9px 20px;
            border-radius: 999px;
            font-family: inherit;
            font-size: 14px;
            font-weight: 900;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.15s ease-out;
            border: none;
        }
        .btn-prev {
            background: #1e293b;
            color: #94a3b8;
            border: 2px solid #334155;
            box-shadow: 0 3px 0 #0f172a;
        }
        .btn-prev:hover { background: #334155; color: #fff; transform: translateY(-2px); }

        .btn-next {
            background: linear-gradient(135deg, #00f2fe, #0284c7);
            color: #061021;
            border: 2px solid #38bdf8;
            box-shadow: 0 4px 0 #0369a1, 0 0 14px rgba(0, 242, 254, 0.4);
        }
        .btn-next:hover { transform: translateY(-2px); box-shadow: 0 6px 0 #0369a1, 0 0 20px rgba(0, 242, 254, 0.6); }

        .btn-submit-main {
            background: linear-gradient(135deg, #10b981, #059669);
            color: #fff;
            border: 2px solid #34d399;
            box-shadow: 0 4px 0 #047857, 0 0 16px rgba(16, 185, 129, 0.4);
            display: none;
        }
        .btn-submit-main:hover { transform: translateY(-2px); box-shadow: 0 6px 0 #047857, 0 0 22px rgba(16, 185, 129, 0.6); }

        .feedback-banner {
            display: none;
            padding: 6px 14px;
            border-radius: 12px;
            font-size: 13.5px;
            font-weight: 900;
            animation: feedbackSlide 0.2s ease-out;
        }
        .feedback-banner.show { display: inline-flex; align-items: center; gap: 8px; }
        .feedback-banner.success {
            background: rgba(22, 163, 74, 0.22);
            border: 2px solid #22c55e;
            color: #4ade80;
            box-shadow: 0 0 16px rgba(34, 197, 94, 0.35);
        }
        .feedback-banner.fail {
            background: rgba(220, 38, 38, 0.22);
            border: 2px solid #ef4444;
            color: #fca5a5;
            box-shadow: 0 0 16px rgba(239, 68, 68, 0.35);
        }
        @keyframes feedbackSlide {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Score Summary Modal */
        .iig-score-card {
            display: none;
            flex-direction: column;
            align-items: center;
            background: linear-gradient(175deg, #0e2744 0%, #081a30 100%);
            border: 3.5px solid #00f2fe;
            border-radius: 24px;
            box-shadow: 0 0 45px rgba(0, 242, 254, 0.4);
            padding: 30px;
            margin: auto;
            max-width: 650px;
            width: 90%;
            text-align: center;
            z-index: 100;
        }
        .iig-score-card.show { display: flex; animation: scoreCardZoom 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        @keyframes scoreCardZoom {
            from { opacity: 0; transform: scale(0.85); }
            to { opacity: 1; transform: scale(1); }
        }

        .iig-status-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            font-size: 36px;
            margin-bottom: 12px;
            box-shadow: 0 0 25px currentColor;
        }
        .status-pass-icon { background: rgba(34, 197, 94, 0.2); border: 3px solid #22c55e; color: #4ade80; }
        .status-fail-icon { background: rgba(239, 68, 68, 0.2); border: 3px solid #ef4444; color: #f87171; }

        .iig-result-title {
            font-family: 'Fredoka', cursive, sans-serif;
            font-size: 26px;
            font-weight: 800;
            margin-bottom: 6px;
        }
        .title-pass { color: var(--gold); text-shadow: 0 0 15px rgba(255, 225, 53, 0.5); }
        .title-fail { color: #f87171; }

        .iig-score-display {
            background: rgba(0, 0, 0, 0.4);
            border: 2px solid rgba(0, 242, 254, 0.4);
            border-radius: 16px;
            padding: 16px 28px;
            margin: 16px 0;
            display: inline-flex;
            align-items: baseline;
            gap: 8px;
        }
        .iig-score-num {
            font-family: 'Fredoka', cursive, sans-serif;
            font-size: 44px;
            font-weight: 800;
            color: #00f2fe;
            text-shadow: 0 0 20px rgba(0, 242, 254, 0.8);
        }
        .iig-score-max { font-size: 18px; color: #94a3b8; font-weight: 800; }

        .iig-stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            width: 100%;
            margin-bottom: 22px;
        }
        .stat-item {
            background: rgba(255, 255, 255, 0.06);
            border: 1.5px solid rgba(255, 255, 255, 0.12);
            border-radius: 12px;
            padding: 12px;
        }
        .stat-val { font-size: 18px; font-weight: 900; color: #fff; margin-bottom: 2px; }
        .stat-lbl { font-size: 12px; color: #94a3b8; font-weight: 700; }

        .iig-actions-row { display: flex; gap: 12px; justify-content: center; width: 100%; }
        .btn-score-act {
            padding: 12px 24px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 900;
            cursor: pointer;
            border: none;
            transition: all 0.15s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-act-retry {
            background: linear-gradient(135deg, #00f2fe, #0284c7);
            color: #061021;
            box-shadow: 0 4px 14px rgba(0, 242, 254, 0.4);
        }
        .btn-act-retry:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0, 242, 254, 0.6); }

        .btn-act-review {
            background: rgba(255, 255, 255, 0.12);
            border: 2px solid rgba(255, 255, 255, 0.25);
            color: #fff;
        }
        .btn-act-review:hover { background: rgba(255, 255, 255, 0.22); color: #fff; transform: translateY(-2px); }

        /* Image Zoom Modal */
        #img-zoom-modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.85);
            z-index: 1000;
            place-items: center;
            cursor: zoom-out;
        }
        #img-zoom-modal.show { display: grid; }
        #zoom-img-target { max-width: 90vw; max-height: 85vh; border-radius: 12px; box-shadow: 0 0 35px rgba(0,0,0,0.8); }
    </style>
</head>
<body>
    <canvas id="star-canvas"></canvas>

    <!-- Top Game Navigation Bar -->
    <header class="arena-topbar">
        <div class="bar-left">
            <a href="{{ route('tests.show', $practiceTest->slug) }}" class="btn-exit" id="btn-exit">
                ◀ Thoát
            </a>
            <div class="stage-pill-info">
                Khối {{ $practiceTest->topic->level->grade }} &bull; {{ $practiceTest->topic->name }}
            </div>
        </div>

        <div class="bar-center">
            <div class="arena-brand">
                <span>⭐</span> IC3 QUEST ARENA
                <span class="arena-subtitle">— Đấu trường thử thách số thông minh</span>
            </div>
        </div>

        <div class="bar-right">
            <div class="hud-timer-badge">
                <span>⏱️</span>
                <span id="timer-display">20:00</span>
            </div>
            <button class="btn-ctrl" id="btn-sound" title="Bật/Tắt Âm thanh">🔊</button>
            <button class="btn-ctrl" id="btn-fullscreen" title="Toàn màn hình">⛶</button>
        </div>
    </header>

    <!-- Khu vực Đấu trường thi đấu chính -->
    <main class="arena-container">
        <!-- Thanh nốt điều hướng câu hỏi (Checkpoints) -->
        <nav class="checkpoint-container" id="checkpoint-container"></nav>

        <!-- Thanh thông báo chế độ Review đáp án -->
        <div class="review-mode-bar" id="review-mode-bar">
            <span>🔍 CHẾ ĐỘ XEM LẠI BÀI THI (REVIEW QUIZ)</span>
            <button type="button" onclick="location.reload()" style="background:#ef4444; color:#fff; border:none; padding:5px 14px; border-radius:8px; font-weight:900; cursor:pointer;">Thoát Review</button>
        </div>

        <!-- Thẻ bài thi tương tác chính (Quest Card) -->
        <article class="quest-card" id="quest-card">
            <div class="quest-header">
                <div class="quest-meta-row">
                    <span class="type-pill" id="type-badge">🎯 NHIỆM VỤ</span>
                    <span class="quest-number-label">Câu <b id="q-curr-num" style="color:#fff;">1</b> / <span id="q-total-num">14</span></span>
                </div>
                <h1 class="quest-title" id="quest-prompt-title">Nội dung câu hỏi...</h1>
            </div>

            <div class="quest-body" id="quest-body">
                <!-- Vùng ảnh đề bài (nếu có) -->
                <div class="quest-image-box" id="prompt-img-box" style="display:none;">
                    <img id="prompt-img" src="" alt="Đề bài" onclick="zoomImage(this.src)">
                </div>

                <!-- Vùng chứa các lựa chọn tương tác đa dạng -->
                <div id="dynamic-content-area" style="width: 100%; flex: 1; display: flex; flex-direction: column; justify-content: center; min-height: 0;"></div>
            </div>

            <div class="quest-footer">
                <div class="footer-left">
                    <button class="btn-game-action btn-prev" id="btn-prev">
                        ◀ Câu trước
                    </button>
                </div>
                <div class="footer-center">
                    <div class="feedback-banner" id="feedback-banner">
                        <span id="fb-icon" style="font-size:16px;">🎉</span>
                        <div id="fb-text">Đúng rồi! Chúc mừng bạn!</div>
                    </div>
                </div>
                <div class="footer-right">
                    <button class="btn-game-action btn-next" id="btn-next">
                        <span>Câu tiếp theo</span> ▶
                    </button>
                    <button class="btn-game-action btn-submit-main" id="btn-submit-main">
                        <span>🏁 NỘP BÀI (SUBMIT ALL)</span>
                    </button>
                </div>
            </div>
        </article>

        <!-- Bảng Tổng Kết Điểm Chuẩn Game Arena -->
        <section class="iig-score-card" id="iig-score-card">
            <div class="iig-status-icon status-pass-icon" id="iig-status-icon">👑</div>
            <h2 class="iig-result-title title-pass" id="iig-result-title">Bạn Quá Xuất Sắc!</h2>
            <p id="iig-result-subtitle" style="color: #94a3b8; font-size: 14px; margin-bottom: 8px;">Chúc mừng bạn đã hoàn thành bài thi!</p>

            <div class="iig-score-display">
                <span class="iig-score-num" id="iig-score-num">1000</span>
                <span class="iig-score-max">/ 1000 Điểm</span>
            </div>

            <div class="iig-stats-grid">
                <div class="stat-item">
                    <div class="stat-val" id="stat-correct-count" style="color:#4ade80;">14 / 14</div>
                    <div class="stat-lbl">Câu chính xác</div>
                </div>
                <div class="stat-item">
                    <div class="stat-val" id="stat-duration-time" style="color:#38bdf8;">02:45</div>
                    <div class="stat-lbl">Thời gian làm</div>
                </div>
                <div class="stat-item">
                    <div class="stat-val" id="stat-stars-earned" style="color:#facc15;">+1000 ⭐</div>
                    <div class="stat-lbl">Sao tích lũy</div>
                </div>
            </div>

            <div class="iig-actions-row">
                <button type="button" class="btn-score-act btn-act-retry" onclick="location.reload()">
                    <span>🔄</span> Làm lại đề thi
                </button>
                <button type="button" class="btn-score-act btn-act-review" id="btn-start-review">
                    <span>🔍</span> Xem lại đáp án
                </button>
            </div>
        </section>
    </main>

    <!-- Modal Zoom Ảnh -->
    <div id="img-zoom-modal" onclick="closeImageZoom()">
        <img id="zoom-img-target" src="" alt="Phóng to">
    </div>

    <!-- Canvas Confetti -->
    <canvas id="confetti-canvas" style="position:fixed; inset:0; pointer-events:none; z-index:999;"></canvas>

    <script>
        // 1. Dữ liệu câu hỏi chuẩn hóa từ Database MOS
        const questions = @json($questions);
        const adminAnswerKeys = @json($adminAnswerKeys ?? []);
        const rawQuestions = questions;
        const submitUrl = "{{ route('attempts.store', $practiceTest->slug) }}";
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        let currentIndex = 0;
        let userSelections = {};
        let hotspotClickPoints = {};
        let answers = userSelections;
        let questionResults = {};
        let reviewResults = questionResults;
        let correctAnswersData = {};
        let isReviewMode = false;
        let isSubmitted = false;
        let soundEnabled = true;
        let startTime = Date.now();
        let timerSeconds = {{ ($practiceTest->duration_minutes ?: 20) * 60 }};
        let timerInterval = null;

        // Phát hiện chế độ Admin Preview
        const isIframePreview = (window.self !== window.top) || new URLSearchParams(window.location.search).has('preview');
        if (isIframePreview) {
            document.body.classList.add('is-iframe-preview');
            if (Object.keys(adminAnswerKeys).length > 0) {
                correctAnswersData = adminAnswerKeys;
            }
        }

        // Đọc tham số câu hỏi từ URL (?q=X)
        const urlParams = new URLSearchParams(window.location.search);
        const paramQ = parseInt(urlParams.get('q'), 10);
        if (!isNaN(paramQ) && paramQ >= 1 && paramQ <= rawQuestions.length) {
            currentIndex = paramQ - 1;
        }

        // Web Audio Synthesizer
        const AudioCtxClass = window.AudioContext || window.webkitAudioContext;
        let audioCtx = null;

        function initAudio() {
            if (!audioCtx) audioCtx = new AudioCtxClass();
            if (audioCtx.state === 'suspended') audioCtx.resume();
        }

        function playSound(type) {
            if (!soundEnabled) return;
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
                    const notes = [523.25, 659.25, 783.99, 1046.50];
                    notes.forEach((freq, idx) => {
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

        // Timer HUD
        function startTimer() {
            if (typeof isIframePreview !== 'undefined' && isIframePreview) return;
            const display = document.getElementById('timer-display');
            timerInterval = setInterval(() => {
                if (isSubmitted) {
                    clearInterval(timerInterval);
                    return;
                }
                timerSeconds--;
                if (timerSeconds <= 0) {
                    clearInterval(timerInterval);
                    timerSeconds = 0;
                    submitExam();
                }
                const m = Math.floor(timerSeconds / 60).toString().padStart(2, '0');
                const s = (timerSeconds % 60).toString().padStart(2, '0');
                if (display) display.textContent = `${m}:${s}`;
            }, 1000);
        }
        startTimer();

        // Checkpoints Render
        function renderCheckpoints() {
            const container = document.getElementById('checkpoint-container');
            if (!container) return;
            container.innerHTML = '';

            rawQuestions.forEach((_, idx) => {
                const node = document.createElement('button');
                node.type = 'button';
                node.className = 'cp-node';
                node.textContent = idx + 1;
                node.title = `Câu ${idx + 1}`;

                if (idx === currentIndex) node.classList.add('active');

                if (isReviewMode) {
                    if (questionResults[idx] === true) node.classList.add('rev-correct');
                    else if (questionResults[idx] === false) node.classList.add('rev-wrong');
                } else {
                    const ans = userSelections[idx];
                    if (ans !== undefined && ans !== null && (!Array.isArray(ans) || ans.length > 0) && (typeof ans !== 'object' || Object.keys(ans).length > 0)) {
                        node.classList.add('answered');
                    }
                }

                node.addEventListener('click', () => {
                    playSound('click');
                    currentIndex = idx;
                    if (typeof isIframePreview !== 'undefined' && isIframePreview) {
                        isReviewMode = false;
                        document.getElementById('feedback-banner').className = 'feedback-banner';
                    }
                    renderQuestion();
                });

                container.appendChild(node);
            });
        }

        // Render Current Question
        function renderQuestion() {
            if (rawQuestions.length === 0) return;
            const q = rawQuestions[currentIndex];

            // 1. Meta & Title
            document.getElementById('q-curr-num').textContent = currentIndex + 1;
            document.getElementById('q-total-num').textContent = rawQuestions.length;
            document.getElementById('quest-prompt-title').textContent = q.title || '(Chưa có tiêu đề câu hỏi)';

            // 2. Type Badge
            const typeBadge = document.getElementById('type-badge');
            if (q.type === 'MultipleResponse') typeBadge.textContent = '🎯 CHỌN NHIỀU ĐÁP ÁN ĐÚNG';
            else if (q.type === 'Matching') typeBadge.textContent = '⚡ NỐI DÂY TƯƠNG ỨNG (MATCHING)';
            else if (q.type === 'MultipleChoiceText') typeBadge.textContent = '📝 PHÂN LOẠI KHÁI NIỆM';
            else if (q.type === 'Hotspot') typeBadge.textContent = '🎯 CHỌN ĐIỂM CHẠM TRÊN ẢNH';
            else if (q.type === 'Sequence') typeBadge.textContent = '🔢 SẮP XẾP THỨ TỰ (SEQUENCE)';
            else typeBadge.textContent = '🎯 NHIỆM VỤ TRẮC NGHIỆM';

            // 3. Prompt Image
            const imgBox = document.getElementById('prompt-img-box');
            const promptImg = document.getElementById('prompt-img');
            
            // Collect all option image paths so we never accidentally show an option image as the prompt image
            const optImgPaths = (q.options || []).map(o => o.image_path).filter(Boolean);
            let promptImgPath = q.configuration?.image_path || null;

            if (!promptImgPath && q.assets && q.assets.length > 0) {
                // Find asset that is specifically a question prompt image (not an option image)
                const candidate = q.assets.find(a => a.kind === 'question_image' || (a.kind === 'image' && !optImgPaths.includes(a.path)));
                if (candidate) promptImgPath = candidate.path;
            }

            if (promptImgPath && q.type !== 'Hotspot') {
                promptImg.src = promptImgPath;
                imgBox.style.display = 'block';
            } else {
                imgBox.style.display = 'none';
            }

            // 4. Dynamic Content by Type
            const contentArea = document.getElementById('dynamic-content-area');
            contentArea.innerHTML = '';

            if (q.type === 'MultipleChoice' || q.type === 'MultipleResponse') {
                renderMultipleChoice(q, contentArea);
            } else if (q.type === 'Matching') {
                renderMatching(q, contentArea);
            } else if (q.type === 'MultipleChoiceText') {
                renderChoiceText(q, contentArea);
            } else if (q.type === 'Hotspot') {
                renderHotspot(q, contentArea);
            } else if (q.type === 'Sequence') {
                renderSequence(q, contentArea);
            } else {
                renderMultipleChoice(q, contentArea);
            }

            // 5. Update Checkpoints & Navigation Buttons
            renderCheckpoints();
            updateNavigation();
        }

        // Render Multiple Choice / Multiple Response
        function renderMultipleChoice(q, container) {
            const grid = document.createElement('div');
            grid.className = 'answers-grid';
            if ((q.options || []).length <= 2) grid.classList.add('single-column');

            const rawUserList = userSelections[currentIndex];
            const userList = Array.isArray(rawUserList) 
                ? rawUserList.map(Number) 
                : (rawUserList !== undefined && rawUserList !== null ? [Number(rawUserList)] : []);
            
            const rawCorrect = correctAnswersData[currentIndex];
            const correctList = Array.isArray(rawCorrect) 
                ? rawCorrect.map(Number) 
                : (rawCorrect !== undefined && rawCorrect !== null ? [Number(rawCorrect)] : []);

            (q.options || []).forEach((opt, idx) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'choice-btn';
                const posVal = Number(opt.position !== undefined ? opt.position : idx);
                btn.dataset.index = posVal;

                const isSelected = userList.includes(posVal);
                const isCorrectChoice = correctList.includes(posVal) || !!opt.is_correct;

                if (isReviewMode) {
                    if (isCorrectChoice) {
                        btn.classList.add('rev-correct');
                    } else if (isSelected) {
                        btn.classList.add('rev-wrong');
                    }
                } else {
                    if (isSelected) btn.classList.add('selected');
                }

                const keyChar = String.fromCharCode(65 + idx);
                let imgHtml = opt.image_path ? `<img class="choice-img" src="${opt.image_path}" alt="Hình ${idx + 1}" onclick="event.stopPropagation(); zoomImage('${opt.image_path}')">` : '';
                let contentText = opt.content && opt.content.trim() !== '​' ? opt.content : '';

                btn.innerHTML = `
                    <div class="choice-key">${keyChar}</div>
                    <div class="choice-content">
                        ${imgHtml}
                        ${contentText ? `<span class="choice-text">${contentText}</span>` : ''}
                    </div>
                `;

                if (!isReviewMode) {
                    btn.addEventListener('click', () => {
                        playSound('select');
                        if (q.type === 'MultipleResponse') {
                            if (!Array.isArray(userSelections[currentIndex])) userSelections[currentIndex] = [];
                            const arr = userSelections[currentIndex];
                            const i = arr.indexOf(posVal);
                            if (i > -1) arr.splice(i, 1);
                            else arr.push(posVal);
                        } else {
                            userSelections[currentIndex] = [posVal];
                        }
                        renderQuestion();
                    });
                }

                grid.appendChild(btn);
            });

            container.appendChild(grid);
        }
        // ⚡ Render Neon Electric LED Laser Wire Matching Game
        function renderMatching(q, container) {
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
                leftList.push({ id: idx, text: leftVal });
                rightList.push({ id: idx, text: rightVal });
            });

            // Shuffled right list for authentic challenge
            const shuffledRight = [...rightList];
            if (shuffledRight.length > 1) {
                const first = shuffledRight.shift();
                shuffledRight.push(first);
            }

            const currentMatches = (typeof userSelections[currentIndex] === 'object' && !Array.isArray(userSelections[currentIndex])) 
                ? userSelections[currentIndex] : {};

            const wireBoard = document.createElement('div');
            wireBoard.className = 'neon-wire-container';
            wireBoard.id = 'wire-board-container';

            const leftCol = document.createElement('div');
            leftCol.className = 'wire-column wire-left-col';

            const rightCol = document.createElement('div');
            rightCol.className = 'wire-column wire-right-col';

            const svgCanvas = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            svgCanvas.setAttribute('class', 'wire-svg-canvas');
            svgCanvas.id = 'wire-svg-canvas';

            const colors = ['#00f2fe', '#f43f5e', '#10b981', '#f59e0b', '#a855f7'];
            let activeSelection = null; // { side: 'left' | 'right', id: number }

            // 1. Left Cards
            leftList.forEach((item, leftIdx) => {
                const card = document.createElement('div');
                card.className = 'wire-card wire-left-card';
                card.dataset.leftId = leftIdx;

                card.innerHTML = `
                    <div class="wire-idx-badge">${leftIdx + 1}</div>
                    <div style="flex:1; line-height:1.35;">${item.text}</div>
                    <div class="wire-port"></div>
                `;

                if (!isReviewMode) {
                    card.onclick = () => {
                        playSound('click');
                        if (activeSelection?.side === 'left' && activeSelection.id === leftIdx) {
                            activeSelection = null;
                        } else if (activeSelection?.side === 'right') {
                            const rightId = activeSelection.id;
                            Object.keys(currentMatches).forEach(k => {
                                if (+currentMatches[k] === rightId) delete currentMatches[k];
                            });
                            currentMatches[leftIdx] = rightId;
                            userSelections[currentIndex] = currentMatches;
                            activeSelection = null;
                        } else {
                            if (currentMatches[leftIdx] !== undefined) {
                                delete currentMatches[leftIdx];
                                userSelections[currentIndex] = currentMatches;
                            }
                            activeSelection = { side: 'left', id: leftIdx };
                        }
                        updateWireUI();
                        renderCheckpoints();
                    };
                }

                leftCol.appendChild(card);
            });

            // 2. Right Cards
            const rightLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
            shuffledRight.forEach((item, rIdx) => {
                const card = document.createElement('div');
                card.className = 'wire-card wire-right-card';
                card.dataset.rightId = item.id;

                card.innerHTML = `
                    <div class="wire-port"></div>
                    <div class="wire-idx-badge">${rightLetters[rIdx % rightLetters.length]}</div>
                    <div style="flex:1; line-height:1.35;">${item.text}</div>
                `;

                if (!isReviewMode) {
                    card.onclick = () => {
                        playSound('click');
                        if (activeSelection?.side === 'right' && activeSelection.id === item.id) {
                            activeSelection = null;
                        } else if (activeSelection?.side === 'left') {
                            const leftIdx = activeSelection.id;
                            Object.keys(currentMatches).forEach(k => {
                                if (+currentMatches[k] === item.id) delete currentMatches[k];
                            });
                            currentMatches[leftIdx] = item.id;
                            userSelections[currentIndex] = currentMatches;
                            activeSelection = null;
                        } else {
                            const connL = Object.keys(currentMatches).find(lIdx => +currentMatches[lIdx] === item.id);
                            if (connL !== undefined) {
                                delete currentMatches[connL];
                                userSelections[currentIndex] = currentMatches;
                            }
                            activeSelection = { side: 'right', id: item.id };
                        }
                        updateWireUI();
                        renderCheckpoints();
                    };
                }

                rightCol.appendChild(card);
            });

            wireBoard.appendChild(leftCol);
            wireBoard.appendChild(svgCanvas);
            wireBoard.appendChild(rightCol);
            container.appendChild(wireBoard);

            function updateWireUI() {
                // Update Left
                leftCol.querySelectorAll('.wire-left-card').forEach(c => {
                    const lId = +c.dataset.leftId;
                    c.classList.remove('selected', 'pair-theme-0', 'pair-theme-1', 'pair-theme-2', 'pair-theme-3', 'pair-theme-4', 'rev-pair-correct', 'rev-pair-wrong');
                    if (!isReviewMode && activeSelection?.side === 'left' && activeSelection.id === lId) {
                        c.classList.add('selected');
                    }
                    const rId = currentMatches[lId];
                    if (rId !== undefined && rId !== null) {
                        if (isReviewMode) {
                            if (Number(rId) === Number(lId)) c.classList.add('rev-pair-correct');
                            else c.classList.add('rev-pair-wrong');
                        } else {
                            c.classList.add(`pair-theme-${lId % colors.length}`);
                        }
                    }
                });

                // Update Right
                rightCol.querySelectorAll('.wire-right-card').forEach(c => {
                    const rId = +c.dataset.rightId;
                    c.classList.remove('selected', 'pair-theme-0', 'pair-theme-1', 'pair-theme-2', 'pair-theme-3', 'pair-theme-4', 'rev-pair-correct', 'rev-pair-wrong');
                    if (!isReviewMode && activeSelection?.side === 'right' && activeSelection.id === rId) {
                        c.classList.add('selected');
                    }
                    const connL = Object.keys(currentMatches).find(lIdx => +currentMatches[lIdx] === rId);
                    if (connL !== undefined) {
                        if (isReviewMode) {
                            if (Number(rId) === Number(connL)) c.classList.add('rev-pair-correct');
                            else c.classList.add('rev-pair-wrong');
                        } else {
                            c.classList.add(`pair-theme-${connL % colors.length}`);
                        }
                    }
                });

                drawWires();
            }

            function drawWires() {
                svgCanvas.innerHTML = '';
                const boardRect = wireBoard.getBoundingClientRect();
                if (boardRect.width === 0) return;

                // 1. Draw User Matches
                Object.keys(currentMatches).forEach(lIdx => {
                    const rId = currentMatches[lIdx];
                    const leftEl = leftCol.querySelector(`[data-left-id="${lIdx}"] .wire-port`);
                    const rightEl = rightCol.querySelector(`[data-right-id="${rId}"] .wire-port`);

                    if (!leftEl || !rightEl) return;

                    const lRect = leftEl.getBoundingClientRect();
                    const rRect = rightEl.getBoundingClientRect();

                    const x1 = lRect.left + lRect.width / 2 - boardRect.left;
                    const y1 = lRect.top + lRect.height / 2 - boardRect.top;
                    const x2 = rRect.left + rRect.width / 2 - boardRect.left;
                    const y2 = rRect.top + rRect.height / 2 - boardRect.top;

                    const dx = Math.abs(x2 - x1) * 0.5;
                    const d = `M ${x1} ${y1} C ${x1 + dx} ${y1}, ${x2 - dx} ${y2}, ${x2} ${y2}`;

                    let color = colors[lIdx % colors.length];
                    if (isReviewMode) {
                        color = (Number(rId) === Number(lIdx)) ? '#10b981' : '#ef4444';
                    }

                    // Glow background path
                    const glowPath = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                    glowPath.setAttribute('d', d);
                    glowPath.setAttribute('stroke', color);
                    glowPath.setAttribute('stroke-width', '10');
                    glowPath.setAttribute('stroke-opacity', '0.45');
                    glowPath.setAttribute('fill', 'none');
                    glowPath.setAttribute('stroke-linecap', 'round');
                    glowPath.style.filter = `drop-shadow(0 0 8px ${color})`;
                    svgCanvas.appendChild(glowPath);

                    // Core Laser path
                    const corePath = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                    corePath.setAttribute('d', d);
                    corePath.setAttribute('stroke', '#ffffff');
                    corePath.setAttribute('stroke-width', '3.5');
                    corePath.setAttribute('fill', 'none');
                    corePath.setAttribute('stroke-linecap', 'round');
                    svgCanvas.appendChild(corePath);

                    // Flowing Electric dashes
                    const animPath = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                    animPath.setAttribute('d', d);
                    animPath.setAttribute('class', 'wire-laser-line');
                    animPath.setAttribute('stroke', color);
                    animPath.setAttribute('stroke-width', '4');
                    animPath.setAttribute('stroke-dasharray', '8 12');
                    svgCanvas.appendChild(animPath);
                });

                // 2. In Review Mode: Draw dashed green guidelines for any wrong or unselected pairs
                if (isReviewMode) {
                    leftList.forEach((_, lIdx) => {
                        const userRight = currentMatches[lIdx];
                        const correctRight = lIdx;
                        if (userRight === undefined || Number(userRight) !== Number(correctRight)) {
                            const leftEl = leftCol.querySelector(`[data-left-id="${lIdx}"] .wire-port`);
                            const rightEl = rightCol.querySelector(`[data-right-id="${correctRight}"] .wire-port`);
                            if (leftEl && rightEl) {
                                const lRect = leftEl.getBoundingClientRect();
                                const rRect = rightEl.getBoundingClientRect();
                                const x1 = lRect.left + lRect.width / 2 - boardRect.left;
                                const y1 = lRect.top + lRect.height / 2 - boardRect.top;
                                const x2 = rRect.left + rRect.width / 2 - boardRect.left;
                                const y2 = rRect.top + rRect.height / 2 - boardRect.top;
                                const dx = Math.abs(x2 - x1) * 0.5;
                                const d = `M ${x1} ${y1} C ${x1 + dx} ${y1}, ${x2 - dx} ${y2}, ${x2} ${y2}`;

                                const guidePath = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                                guidePath.setAttribute('d', d);
                                guidePath.setAttribute('stroke', '#22c55e');
                                guidePath.setAttribute('stroke-width', '3');
                                guidePath.setAttribute('stroke-dasharray', '6 6');
                                guidePath.setAttribute('fill', 'none');
                                guidePath.style.opacity = '0.85';
                                svgCanvas.appendChild(guidePath);
                            }
                        }
                    });
                }
            }

            setTimeout(updateWireUI, 50);
            setTimeout(drawWires, 120);
            window.addEventListener('resize', drawWires, { passive: true });
        }
        // 📋 Render Multiple Choice Text (Classify Segment Buttons)
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

            (q.options || []).forEach((opt, idx) => {
                let leftVal = opt.metadata?.left || opt.content || `Mục ${idx + 1}`;
                if (opt.content && opt.content.includes(':::')) {
                    leftVal = opt.content.split(':::')[0].trim();
                }

                const itemChoices = (opt.metadata?.available_options && opt.metadata.available_options.length) 
                    ? opt.metadata.available_options : distinctChoices;

                const row = document.createElement('div');
                row.className = `classify-item row-theme-${idx % 5}`;

                const labelDiv = document.createElement('div');
                labelDiv.className = 'classify-label';
                labelDiv.innerHTML = `
                    <span class="classify-idx-badge">${idx + 1}</span>
                    <span>${leftVal}</span>
                `;

                const btnGroup = document.createElement('div');
                btnGroup.className = 'classify-buttons-group';

                const curSelected = userMap[idx];
                const correctMap = correctAnswersData[currentIndex] || {};
                const correctChoiceIdx = correctMap[idx];

                itemChoices.forEach((choice, choiceIdx) => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = `classify-btn theme-${choiceIdx % 4}`;
                    btn.textContent = choice;

                    const isUserPick = (curSelected !== undefined && (curSelected === choice || curSelected === choiceIdx));
                    const isRightPick = (correctChoiceIdx !== undefined && correctChoiceIdx === choiceIdx);

                    if (isReviewMode) {
                        if (isRightPick) {
                            btn.classList.add('rev-correct');
                        } else if (isUserPick) {
                            btn.classList.add('rev-wrong');
                        }
                    } else {
                        if (isUserPick) btn.classList.add('selected');
                    }

                    if (!isReviewMode) {
                        btn.onclick = () => {
                            playSound('click');
                            btnGroup.querySelectorAll('.classify-btn').forEach(b => b.classList.remove('selected'));
                            btn.classList.add('selected');
                            if (!userSelections[currentIndex] || Array.isArray(userSelections[currentIndex])) {
                                userSelections[currentIndex] = {};
                            }
                            userSelections[currentIndex][idx] = choiceIdx;
                            renderCheckpoints();
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

        // 🎯 Render Hotspot Question
        function renderHotspot(q, container) {
            const imgPath = q.configuration?.image_path || q.assets?.[0]?.path;
            if (!imgPath) {
                container.innerHTML = '<div style="color:#f87171; text-align:center; padding:20px;">Không tìm thấy hình ảnh đề bài Hotspot.</div>';
                return;
            }

            const outer = document.createElement('div');
            outer.className = 'hotspot-outer-container';

            const hint = document.createElement('div');
            hint.className = 'hotspot-instruction-hint';
            hint.innerHTML = isReviewMode 
                ? `<span>🎯</span> <i>Xem lại vị trí đáp án đúng (khung xanh) và vị trí bạn đã chọn.</i>`
                : `<span>🎯</span> <i>Hãy nhấp chuột trực tiếp vào biểu tượng hoặc vị trí trên hình ảnh để chọn đáp án!</i>`;
            outer.appendChild(hint);

            const wrapper = document.createElement('div');
            wrapper.className = 'hotspot-wrapper';
            wrapper.id = 'hotspot-wrapper';
            wrapper.innerHTML = `<img id="hotspot-img" src="${imgPath}" alt="Hotspot">`;

            const selectedIdx = userSelections[currentIndex];
            const rawCorrect = correctAnswersData[currentIndex];
            const correctPos = (rawCorrect !== undefined && rawCorrect !== null) ? Number(rawCorrect) : 0;

            if (isReviewMode) {
                // Correct target area box
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
                        wrapper.appendChild(correctBox);
                    }
                }

                // User picked marker
                const selectedOption = (q.options || []).find(option => Number(option.position) === Number(selectedIdx));
                if (selectedIdx !== undefined && selectedIdx !== null && selectedOption) {
                    const userOpt = selectedOption;
                    const uRect = getNormRect(userOpt.metadata?.rect || userOpt.rect);
                    if (uRect) {
                        const selectedPoint = hotspotClickPoints[currentIndex];
                        const userMarker = document.createElement('div');
                        const isHit = (Number(selectedIdx) === correctPos || !!userOpt.is_correct);
                        userMarker.className = isHit ? 'hotspot-target-marker rev-hit' : 'hotspot-target-marker rev-miss';
                        userMarker.style.left = (selectedPoint?.x ?? (uRect.x + uRect.w / 2)) + '%';
                        userMarker.style.top = (selectedPoint?.y ?? (uRect.y + uRect.h / 2)) + '%';
                        userMarker.style.display = 'grid';
                        userMarker.innerHTML = isHit ? '✓' : '✕';
                        wrapper.appendChild(userMarker);
                    }
                }
            } else {
                const marker = document.createElement('div');
                marker.className = 'hotspot-target-marker';
                wrapper.appendChild(marker);

                const selectedOption = (q.options || []).find(option => Number(option.position) === Number(selectedIdx));
                if (selectedIdx !== undefined && selectedIdx !== null && selectedOption) {
                    const targetArea = selectedOption;
                    const rect = getNormRect(targetArea.metadata?.rect || targetArea.rect);
                    if (rect) {
                        const selectedPoint = hotspotClickPoints[currentIndex];
                        marker.style.left = (selectedPoint?.x ?? (rect.x + rect.w / 2)) + '%';
                        marker.style.top = (selectedPoint?.y ?? (rect.y + rect.h / 2)) + '%';
                        marker.style.display = 'grid';
                    }
                }

                wrapper.onclick = (e) => {
                    playSound('click');
                    const image = wrapper.querySelector('#hotspot-img');
                    const imageRect = image.getBoundingClientRect();
                    const ptX = ((e.clientX - imageRect.left) / imageRect.width) * 100;
                    const ptY = ((e.clientY - imageRect.top) / imageRect.height) * 100;

                    if (ptX < 0 || ptX > 100 || ptY < 0 || ptY > 100) return;

                    let matchIdx = -1;
                    (q.options || []).forEach((area, idx) => {
                        const rect = getNormRect(area.metadata?.rect || area.rect);
                        if (rect) {
                            if (ptX >= rect.x && ptX <= (rect.x + rect.w) && ptY >= rect.y && ptY <= (rect.y + rect.h)) {
                                matchIdx = idx;
                            }
                        }
                    });

                    if (matchIdx !== -1) {
                        const targetArea = q.options[matchIdx];
                        userSelections[currentIndex] = Number(targetArea.position ?? matchIdx);
                        hotspotClickPoints[currentIndex] = { x: ptX, y: ptY };
                        marker.style.left = ptX + '%';
                        marker.style.top = ptY + '%';
                        marker.style.display = 'grid';
                        renderCheckpoints();
                    }
                };
            }

            outer.appendChild(wrapper);
            container.appendChild(outer);
        }
        // 🔢 Render Sequence Question
        function renderSequence(q, container) {
            const list = document.createElement('div');
            list.className = 'sequence-list';

            let currentOrder = userSelections[currentIndex];
            if (!Array.isArray(currentOrder) || currentOrder.length !== q.options.length) {
                currentOrder = q.options.map((_, idx) => idx);
                userSelections[currentIndex] = currentOrder;
            }

            const rawCorrect = correctAnswersData[currentIndex];
            const correctOrder = Array.isArray(rawCorrect) ? rawCorrect.map(Number) : q.options.map((_, idx) => idx);

            currentOrder.forEach((optIdx, pos) => {
                const opt = q.options[optIdx];
                const item = document.createElement('div');
                item.className = 'sequence-item';

                if (isReviewMode) {
                    const isStepCorrect = (Number(opt.position) === pos);
                    item.classList.add(isStepCorrect ? 'rev-correct' : 'rev-wrong');

                    item.innerHTML = `
                        <div style="display:flex; align-items:center; gap:10px;">
                            <span class="choice-key" style="width:30px; height:30px; font-size:14px; background:${isStepCorrect ? '#16a34a' : '#dc2626'}">${pos + 1}</span>
                            <span>${opt.content}</span>
                        </div>
                        <div>
                            <span class="seq-status-badge ${isStepCorrect ? 'correct' : 'wrong'}">
                                ${isStepCorrect ? '✓ Đúng vị trí' : `✕ Vị trí đúng: Bước ${(opt.position !== undefined ? opt.position : 0) + 1}`}
                            </span>
                        </div>
                    `;
                } else {
                    item.innerHTML = `
                        <div style="display:flex; align-items:center; gap:10px;">
                            <span class="choice-key" style="width:30px; height:30px; font-size:14px;">${pos + 1}</span>
                            <span>${opt.content}</span>
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
            const arr = userSelections[currentIndex];
            const targetPos = pos + dir;
            if (targetPos < 0 || targetPos >= arr.length) return;
            playSound('select');
            const temp = arr[pos];
            arr[pos] = arr[targetPos];
            arr[targetPos] = temp;
            renderQuestion();
        };

        // Navigation Updates
        function updateNavigation() {
            document.getElementById('btn-prev').style.display = currentIndex > 0 ? 'inline-flex' : 'none';

            const btnNext = document.getElementById('btn-next');
            const btnSubmit = document.getElementById('btn-submit-main');

            if (isReviewMode) {
                btnNext.style.display = currentIndex < rawQuestions.length - 1 ? 'inline-flex' : 'none';
                btnSubmit.style.display = 'none';

                // Feedback Banner
                const fb = document.getElementById('feedback-banner');
                const isCorrect = questionResults[currentIndex];
                fb.className = isCorrect ? 'feedback-banner success show' : 'feedback-banner fail show';
                document.getElementById('fb-icon').textContent = isCorrect ? '✓' : '✗';
                document.getElementById('fb-text').innerHTML = isCorrect ? '<b>CHÍNH XÁC!</b> Bạn đã trả lời đúng.' : '<b>CHƯA CHÍNH XÁC!</b> Hãy xem lại đáp án chuẩn.';
            } else {
                if (currentIndex === rawQuestions.length - 1) {
                    btnNext.style.display = 'none';
                    btnSubmit.style.display = 'inline-flex';
                } else {
                    btnNext.style.display = 'inline-flex';
                    btnSubmit.style.display = 'none';
                }
                document.getElementById('feedback-banner').className = 'feedback-banner';
            }
        }

        // Previous / Next Button Events
        document.getElementById('btn-prev').addEventListener('click', () => {
            if (currentIndex > 0) {
                playSound('click');
                currentIndex--;
                if (typeof isIframePreview !== 'undefined' && isIframePreview) {
                    isReviewMode = false;
                    document.getElementById('feedback-banner').className = 'feedback-banner';
                }
                renderQuestion();
            }
        });

        document.getElementById('btn-next').addEventListener('click', () => {
            if (currentIndex < rawQuestions.length - 1) {
                playSound('click');
                currentIndex++;
                if (typeof isIframePreview !== 'undefined' && isIframePreview) {
                    isReviewMode = false;
                    document.getElementById('feedback-banner').className = 'feedback-banner';
                }
                renderQuestion();
            }
        });

        // Submit Exam
        document.getElementById('btn-submit-main').addEventListener('click', () => {
            submitExam();
        });

        function submitExam() {
            if (isSubmitted) return;
            isSubmitted = true;
            playSound('click');

            const durationSec = Math.round((Date.now() - startTime) / 1000);

            fetch(submitUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    answers: userSelections,
                    question_ids: (rawQuestions || []).map(q => q.id),
                    duration_seconds: durationSec
                })
            })
            .then(res => res.json())
            .then(data => {
                questionResults = data.results || {};
                reviewResults = questionResults;
                correctAnswersData = data.correct_answers_data || {};
                reviewResults = questionResults;
                showScoreSummary(data, durationSec);
            })
            .catch(err => {
                console.error(err);
                showScoreSummary({ score: 1000, correct_answers: rawQuestions.length, total_questions: rawQuestions.length }, durationSec);
            });
        }

        function showScoreSummary(data, durationSec) {
            document.getElementById('quest-card').style.display = 'none';
            document.getElementById('checkpoint-container').style.display = 'none';

            const scoreCard = document.getElementById('iig-score-card');
            scoreCard.classList.add('show');

            const score = data.score ?? 0;
            const correct = data.correct_answers ?? 0;
            const total = data.total_questions ?? rawQuestions.length;
            const isPerfect = score >= 700;

            const m = Math.floor(durationSec / 60).toString().padStart(2, '0');
            const s = (durationSec % 60).toString().padStart(2, '0');

            document.getElementById('iig-score-num').textContent = score;
            document.getElementById('stat-correct-count').textContent = `${correct} / ${total}`;
            document.getElementById('stat-duration-time').textContent = `${m}:${s}`;
            document.getElementById('stat-stars-earned').textContent = `+${score}`;

            const icon = document.getElementById('iig-status-icon');
            const title = document.getElementById('iig-result-title');
            const sub = document.getElementById('iig-result-subtitle');

            if (isPerfect) {
                playSound('victory');
                createConfetti(120);
                icon.className = 'iig-status-icon status-pass-icon';
                icon.textContent = '👑';
                title.className = 'iig-result-title title-pass';
                title.textContent = 'Bạn Quá Xuất Sắc!';
                sub.textContent = 'Chúc mừng bạn đã đạt chuẩn xuất sắc IC3 Spark!';
            } else {
                playSound('fail');
                icon.className = 'iig-status-icon status-fail-icon';
                icon.textContent = '✕';
                title.className = 'iig-result-title title-fail';
                title.textContent = 'Cố Gắng Lần Sau Bạn Nhé!';
                sub.textContent = 'Hãy xem lại bài thi để củng cố các câu chưa chính xác nhé.';
            }
        }

        // Start Review Mode
        document.getElementById('btn-start-review').addEventListener('click', () => {
            playSound('click');
            isReviewMode = true;
            document.getElementById('iig-score-card').classList.remove('show');
            document.getElementById('quest-card').style.display = 'flex';
            document.getElementById('checkpoint-container').style.display = 'flex';
            document.getElementById('review-mode-bar').classList.add('show');
            currentIndex = 0;
            renderQuestion();
        });

        // Image Zoom Modal
        window.zoomImage = (src) => {
            document.getElementById('zoom-img-target').src = src;
            document.getElementById('img-zoom-modal').classList.add('show');
        };
        window.closeImageZoom = () => {
            document.getElementById('img-zoom-modal').classList.remove('show');
        };

        // Sound Toggle
        document.getElementById('btn-sound').addEventListener('click', function() {
            soundEnabled = !soundEnabled;
            this.textContent = soundEnabled ? '🔊' : '🔇';
        });

        // Fullscreen Toggle
        document.getElementById('btn-fullscreen').addEventListener('click', () => {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(() => {});
            } else {
                document.exitFullscreen().catch(() => {});
            }
        });

        // Background Starfield Animation
        const starCanvas = document.getElementById('star-canvas');
        const starCtx = starCanvas.getContext('2d');
        let stars = [];

        function resizeStarCanvas() {
            starCanvas.width = window.innerWidth;
            starCanvas.height = window.innerHeight;
            stars = Array.from({ length: 60 }, () => ({
                x: Math.random() * starCanvas.width,
                y: Math.random() * starCanvas.height,
                size: Math.random() * 1.8 + 0.5,
                speed: Math.random() * 0.3 + 0.1,
                alpha: Math.random() * 0.7 + 0.3
            }));
        }
        window.addEventListener('resize', resizeStarCanvas);
        resizeStarCanvas();

        function animateStars() {
            starCtx.clearRect(0, 0, starCanvas.width, starCanvas.height);
            starCtx.fillStyle = '#ffffff';
            stars.forEach(s => {
                starCtx.globalAlpha = s.alpha;
                starCtx.beginPath();
                starCtx.arc(s.x, s.y, s.size, 0, Math.PI * 2);
                starCtx.fill();
                s.y -= s.speed;
                if (s.y < 0) s.y = starCanvas.height;
            });
            requestAnimationFrame(animateStars);
        }
        animateStars();

        // Confetti Generator
        function createConfetti(count) {
            const canvas = document.getElementById('confetti-canvas');
            const ctx = canvas.getContext('2d');
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;

            const pieces = Array.from({ length: count }, () => ({
                x: Math.random() * canvas.width,
                y: Math.random() * canvas.height - canvas.height,
                size: Math.random() * 8 + 4,
                color: ['#00f2fe', '#ffe135', '#ff3366', '#33ff88', '#9933ff'][Math.floor(Math.random() * 5)],
                speed: Math.random() * 4 + 2,
                angle: Math.random() * Math.PI * 2,
                rotationSpeed: (Math.random() - 0.5) * 0.2
            }));

            let frame = 0;
            function draw() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                pieces.forEach(p => {
                    ctx.save();
                    ctx.translate(p.x, p.y);
                    ctx.rotate(p.angle);
                    ctx.fillStyle = p.color;
                    ctx.fillRect(-p.size / 2, -p.size / 2, p.size, p.size);
                    ctx.restore();

                    p.y += p.speed;
                    p.angle += p.rotationSpeed;
                });

                if (++frame < 180) requestAnimationFrame(draw);
                else ctx.clearRect(0, 0, canvas.width, canvas.height);
            }
            draw();
        }

        // Initial Render
        renderQuestion();

        // Admin Preview Mode Helpers & Listener
        window.adminAutofillAnswer = () => {
            playSound('select');
            if (Object.keys(adminAnswerKeys).length > 0) {
                correctAnswersData = adminAnswerKeys;
            }
            const q = rawQuestions[currentIndex];
            const correctKey = correctAnswersData[currentIndex];

            if (correctKey !== undefined) {
                if (q.type === 'MultipleChoice' || q.type === 'MultipleResponse') {
                    userSelections[currentIndex] = Array.isArray(correctKey) ? [...correctKey] : [Number(correctKey)];
                } else if (q.type === 'Matching') {
                    const matchObj = {};
                    (q.options || []).forEach((_, idx) => { matchObj[idx] = idx; });
                    userSelections[currentIndex] = matchObj;
                } else if (q.type === 'MultipleChoiceText') {
                    const textObj = {};
                    (q.options || []).forEach((opt, idx) => {
                        const targetIdx = (typeof correctKey === 'object' && correctKey[idx] !== undefined) 
                            ? Number(correctKey[idx]) 
                            : (opt.metadata?.correct_index ?? 0);
                        textObj[idx] = targetIdx;
                    });
                    userSelections[currentIndex] = textObj;
                } else if (q.type === 'Hotspot') {
                    userSelections[currentIndex] = Number(correctKey);
                } else if (q.type === 'Sequence') {
                    userSelections[currentIndex] = Array.isArray(correctKey) ? [...correctKey] : (q.options || []).map((_, idx) => idx);
                }
            }
            isReviewMode = false;
            document.getElementById('feedback-banner').className = 'feedback-banner';
            renderQuestion();
        };

        window.adminCheckAnswer = () => {
            const curAnswer = userSelections[currentIndex];
            const hasAnswer = curAnswer !== undefined && curAnswer !== null && 
                (!Array.isArray(curAnswer) || curAnswer.length > 0) && 
                (typeof curAnswer !== 'object' || Object.keys(curAnswer).length > 0);

            if (!hasAnswer) {
                playSound('fail');
                const fb = document.getElementById('feedback-banner');
                fb.className = 'feedback-banner fail show';
                document.getElementById('fb-icon').textContent = '⚠️';
                document.getElementById('fb-text').innerHTML = '<b>Vui lòng chọn hoặc điền đáp án trước khi kiểm tra!</b>';
                return;
            }

            if (Object.keys(adminAnswerKeys).length > 0) {
                correctAnswersData = adminAnswerKeys;
            }

            const q = rawQuestions[currentIndex];
            const correctKey = correctAnswersData[currentIndex];
            let isCorrect = false;

            if (q.type === 'MultipleChoice' || q.type === 'MultipleResponse') {
                const userList = Array.isArray(curAnswer) ? curAnswer.map(Number).sort() : [Number(curAnswer)];
                const rightList = Array.isArray(correctKey) ? correctKey.map(Number).sort() : [Number(correctKey)];
                isCorrect = (JSON.stringify(userList) === JSON.stringify(rightList));
            } else if (q.type === 'Matching') {
                const pairs = typeof curAnswer === 'object' ? curAnswer : {};
                const totalPairs = (q.options || []).length;
                let correctCount = 0;
                Object.keys(pairs).forEach(k => {
                    if (Number(pairs[k]) === Number(k)) correctCount++;
                });
                isCorrect = (correctCount === totalPairs && totalPairs > 0);
            } else if (q.type === 'MultipleChoiceText') {
                const userMap = typeof curAnswer === 'object' ? curAnswer : {};
                const targetMap = typeof correctKey === 'object' ? correctKey : {};
                const totalKeys = (q.options || []).length;
                let correctCount = 0;
                (q.options || []).forEach((opt, idx) => {
                    const targetIdx = (typeof targetMap === 'object' && targetMap[idx] !== undefined) 
                        ? Number(targetMap[idx]) 
                        : (opt.metadata?.correct_index ?? -1);
                    if (userMap[idx] !== undefined && Number(userMap[idx]) === Number(targetIdx)) {
                        correctCount++;
                    }
                });
                isCorrect = (correctCount === totalKeys && totalKeys > 0);
            } else if (q.type === 'Hotspot') {
                isCorrect = (Number(curAnswer) === Number(correctKey));
            } else if (q.type === 'Sequence') {
                const userArr = Array.isArray(curAnswer) ? curAnswer.map(Number) : [];
                const targetArr = Array.isArray(correctKey) ? correctKey.map(Number) : (q.options || []).map((_, i) => i);
                isCorrect = (JSON.stringify(userArr) === JSON.stringify(targetArr));
            }

            isReviewMode = true;
            questionResults[currentIndex] = isCorrect;
            renderQuestion();

            const fb = document.getElementById('feedback-banner');
            if (isCorrect) {
                playSound('victory');
                createConfetti(50);
                fb.className = 'feedback-banner success show';
                document.getElementById('fb-icon').textContent = '🎉';
                document.getElementById('fb-text').innerHTML = '<b>CHÍNH XÁC!</b> Đáp án đã chọn hoàn toàn đúng!';
            } else {
                playSound('fail');
                fb.className = 'feedback-banner fail show';
                document.getElementById('fb-icon').textContent = '❌';
                document.getElementById('fb-text').innerHTML = '<b>CHƯA CHÍNH XÁC!</b> Đáp án đúng đã được tô màu xanh lá cây để đối chiếu.';
            }
        };

        window.adminResetAnswer = () => {
            playSound('click');
            delete userSelections[currentIndex];
            delete hotspotClickPoints[currentIndex];
            delete questionResults[currentIndex];
            isReviewMode = false;
            document.getElementById('feedback-banner').className = 'feedback-banner';
            renderQuestion();
        };

        if (window.self !== window.top) {
            window.addEventListener('message', (event) => {
                if (event.data?.action === 'admin-autofill') window.adminAutofillAnswer();
                else if (event.data?.action === 'admin-check') window.adminCheckAnswer();
                else if (event.data?.action === 'admin-reset') window.adminResetAnswer();
            });
        }
    </script>
</body>
</html>
