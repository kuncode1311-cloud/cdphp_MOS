{{-- Trang soạn đề chính: chọn khối, chủ đề, bài luyện rồi sửa câu hỏi. Phần JavaScript xử lý form ở cuối file. --}}
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>IC3 GS6 Studio — Quản trị Bộ đề & Câu hỏi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy-side: #18215f;
            --navy-top: #2a3d8f;
            --navy-deep: #0f172a;
            --blue-primary: #4338ca;
            --blue-gradient: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%);
            --bg-body: #f0f4f9;
            --border-color: #dbe4f0;
            --border-focus: #4f46e5;
            --muted-text: #64748b;
            
            /* Option colors */
            --opt-a: #2563eb;
            --opt-b: #059669;
            --opt-c: #d97706;
            --opt-d: #7c3aed;

            --radius-md: 10px;
            --radius-lg: 14px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
            --shadow-card: 0 4px 16px rgba(15,23,42,0.06);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, sans-serif;
            background: var(--bg-body);
            color: var(--navy-deep);
            height: 100vh;
            overflow: hidden;
            display: flex;
        }

        :root {
            --sidebar-width: 320px;
        }

        .app-container {
            display: grid;
            grid-template-columns: var(--sidebar-width, 320px) minmax(0, 1fr);
            width: 100vw;
            height: 100vh;
            overflow: hidden;
            transition: grid-template-columns 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .app-container.is-resizing {
            transition: none !important;
        }
        .app-container.is-resizing * {
            user-select: none !important;
            cursor: col-resize !important;
        }

        .app-container.sidebar-collapsed {
            grid-template-columns: 0px minmax(0, 1fr) !important;
        }

        /* 1. SIDEBAR */
        .sidebar {
            width: 100%;
            min-width: 0;
            position: relative;
            background: linear-gradient(180deg, var(--navy-top) 0%, var(--navy-side) 100%);
            color: #ffffff;
            height: 100vh;
            display: flex;
            flex-direction: column;
            border-right: 1px solid rgba(255,255,255,0.1);
            overflow: hidden;
            transition: opacity 0.2s ease, visibility 0.2s ease;
            white-space: nowrap;
        }

        /* 📐 VS Code Draggable Sidebar Resizer Handle */
        .sidebar-resizer {
            position: absolute;
            top: 0;
            right: 0;
            width: 6px;
            height: 100%;
            cursor: col-resize;
            z-index: 100;
            background: transparent;
            transition: background 0.15s ease, box-shadow 0.15s ease;
        }
        .sidebar-resizer:hover,
        .app-container.is-resizing .sidebar-resizer {
            background: #38bdf8;
            box-shadow: 0 0 10px rgba(56, 189, 248, 0.8), 0 0 2px #fff;
        }

        .app-container.sidebar-collapsed .sidebar {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        .btn-toggle-sidebar {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            color: #475569;
            display: grid;
            place-items: center;
            font-size: 13px;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.15s;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            flex-shrink: 0;
        }
        .btn-toggle-sidebar:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
            color: #1e293b;
            transform: scale(1.05);
        }
        .app-container.sidebar-collapsed .btn-toggle-sidebar {
            background: #eef2ff;
            color: #4f46e5;
            border-color: #c7d2fe;
            box-shadow: 0 2px 6px rgba(79,70,229,0.15);
        }

        .btn-sidebar-collapse-mini {
            width: 26px;
            height: 26px;
            border-radius: 6px;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.15);
            color: #ffffff;
            display: grid;
            place-items: center;
            font-size: 11px;
            font-weight: 900;
            cursor: pointer;
            transition: 0.15s;
        }
        .btn-sidebar-collapse-mini:hover {
            background: rgba(255,255,255,0.25);
        }

        .sidebar-header {
            padding: 16px 18px 12px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .brand-logo {
            color: #ffffff;
            text-decoration: none;
            font-size: 15.5px;
            font-weight: 900;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .brand-logo span {
            background: #ffcc00;
            color: #1e1b4b;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 900;
        }

        .sidebar-grade-section {
            padding: 12px 14px 8px;
        }
        .grade-section-title {
            font-size: 10px;
            font-weight: 800;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        .grade-pill-group {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
        }
        .grade-btn {
            flex: 1 1 calc(33.33% - 4px);
            min-width: 58px;
            padding: 7px 4px;
            text-align: center;
            border-radius: 7px;
            text-decoration: none;
            font-size: 11.5px;
            font-weight: 800;
            color: #cbd5e1;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
            transition: 0.15s;
            white-space: nowrap;
        }
        .grade-btn:hover { background: rgba(255,255,255,0.16); color: #fff; }
        .grade-btn.active {
            background: #ffffff;
            color: var(--navy-side);
            font-weight: 900;
            border-color: #ffffff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        .btn-add-grade-mini {
            background: rgba(56, 189, 248, 0.15);
            border: 1px dashed rgba(56, 189, 248, 0.5);
            color: #38bdf8;
            border-radius: 6px;
            padding: 2px 8px;
            font-size: 10.5px;
            font-weight: 900;
            cursor: pointer;
            transition: all 0.15s;
            display: inline-flex;
            align-items: center;
            gap: 3px;
        }
        .btn-add-grade-mini:hover {
            background: #38bdf8;
            color: #0f172a;
            border-color: #38bdf8;
        }

        .sidebar-topic-topbar {
            padding: 10px 14px 6px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }
        .topic-topbar-title {
            font-size: 11px;
            font-weight: 850;
            color: #94a3b8;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .btn-add-topic-hero {
            padding: 5.5px 10px;
            background: linear-gradient(135deg, #10b981, #059669);
            border: 1px solid #34d399;
            border-radius: 6px;
            color: #ffffff;
            font-size: 11.5px;
            font-weight: 850;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            box-shadow: 0 2px 6px rgba(16, 185, 129, 0.35);
            transition: all 0.15s ease;
            white-space: nowrap;
        }
        .btn-add-topic-hero:hover {
            background: linear-gradient(135deg, #059669, #047857);
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.45);
        }

        /* 🌲 VS Code Style Tree Questions Subbranch */
        .tree-test-block {
            display: flex;
            flex-direction: column;
            gap: 2px;
            margin-bottom: 2px;
        }
        .tree-test-row {
            display: flex;
            align-items: center;
            gap: 4px;
            width: 100%;
        }
        .tree-test-item {
            flex: 1;
            min-width: 0;
            padding: 5px 8px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 11.5px;
            font-weight: 750;
            color: #cbd5e1;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.15s ease;
        }
        .tree-test-item:hover { background: rgba(255,255,255,0.1); color: #fff; }
        .tree-test-item.is-active {
            background: linear-gradient(135deg, #0284c7, #0369a1);
            color: #ffffff;
            font-weight: 900;
            box-shadow: 0 2px 6px rgba(0,0,0,0.25);
        }
        .tree-test-toggle-icon {
            font-size: 10px;
            color: #38bdf8;
            font-weight: 900;
            width: 10px;
            text-align: center;
            flex-shrink: 0;
        }
        .tree-test-name-txt {
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .badge-count-sm {
            font-size: 10px;
            padding: 1px 5px;
            border-radius: 4px;
            background: rgba(0, 0, 0, 0.25);
            color: #93c5fd;
            font-weight: 800;
            flex-shrink: 0;
        }
        .btn-tree-add-q-mini {
            width: 20px;
            height: 20px;
            border-radius: 4px;
            border: none;
            background: rgba(16, 185, 129, 0.2);
            color: #34d399;
            font-size: 12px;
            font-weight: 900;
            cursor: pointer;
            display: grid;
            place-items: center;
            transition: all 0.15s ease;
            flex-shrink: 0;
        }
        .btn-tree-add-q-mini:hover {
            background: #10b981;
            color: #ffffff;
        }

        .tree-question-subbranch {
            display: flex;
            flex-direction: column;
            gap: 2px;
            padding: 4px 0 6px 8px;
            margin-left: 10px;
            border-left: 1.5px solid rgba(56, 189, 248, 0.25);
            min-width: 0;
        }
        .tree-q-item,
        .q-nav-card {
            padding: 4px 8px !important;
            border-radius: 6px !important;
            text-decoration: none !important;
            font-size: 11.5px !important;
            font-weight: 700 !important;
            color: #cbd5e1 !important;
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            justify-content: flex-start !important;
            gap: 6px !important;
            cursor: pointer !important;
            transition: all 0.12s ease !important;
            background: transparent !important;
            border: 1px solid transparent !important;
            user-select: none !important;
            box-shadow: none !important;
            transform: none !important;
            min-height: 28px !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            box-sizing: border-box !important;
            width: 100% !important;
        }
        .tree-q-item:hover,
        .q-nav-card:hover {
            background: rgba(255, 255, 255, 0.08) !important;
            color: #ffffff !important;
            border-color: rgba(255, 255, 255, 0.12) !important;
            transform: none !important;
        }
        .tree-q-item.active,
        .q-nav-card.active {
            background: linear-gradient(135deg, rgba(2, 132, 199, 0.35), rgba(3, 105, 161, 0.5)) !important;
            border-color: #38bdf8 !important;
            color: #ffffff !important;
            font-weight: 850 !important;
            box-shadow: 0 0 10px rgba(56, 189, 248, 0.3) !important;
            transform: none !important;
        }
        .tree-q-badge {
            font-size: 10px;
            font-weight: 900;
            padding: 1px 5px;
            border-radius: 4px;
            background: rgba(255, 255, 255, 0.12);
            color: #cbd5e1;
            flex-shrink: 0;
            line-height: 1.2;
        }
        .tree-q-item.active .tree-q-badge,
        .q-nav-card.active .tree-q-badge {
            background: #fbbf24 !important;
            color: #0f172a !important;
        }
        .tree-q-icon {
            font-size: 11.5px;
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
        }
        .tree-q-text {
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.3;
            min-width: 0;
        }
        .tree-q-item.active .tree-q-text,
        .q-nav-card.active .tree-q-text {
            color: #ffffff !important;
            font-weight: 750 !important;
        }
        .tree-btn-add-q {
            padding: 5px 8px;
            margin-top: 3px;
            border-radius: 6px;
            border: 1px dashed rgba(16, 185, 129, 0.45);
            background: rgba(16, 185, 129, 0.08);
            color: #34d399;
            font-size: 11px;
            font-weight: 800;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            transition: all 0.15s ease;
            width: 100%;
        }
        .tree-btn-add-q:hover {
            background: rgba(16, 185, 129, 0.22);
            border-color: #10b981;
            color: #ffffff;
        }

        .sidebar-tree-scroll {
            flex: 1;
            overflow-y: auto;
            padding: 6px 12px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            min-height: 0;
        }
        .sidebar-tree-scroll::-webkit-scrollbar { width: 4px; }
        .sidebar-tree-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.25); border-radius: 4px; }

        .tree-topic-block {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 8px;
            transition: 0.15s;
        }
        .tree-topic-block.is-open {
            background: rgba(255,255,255,0.09);
            border-color: rgba(255,255,255,0.2);
        }
        .tree-topic-head {
            padding: 8px 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            user-select: none;
            gap: 6px;
        }
        .tree-topic-head:hover { background: rgba(255,255,255,0.06); border-radius: 7px; }
        .tree-topic-name {
            font-size: 12px;
            font-weight: 800;
            color: #f1f5f9;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: flex;
            align-items: center;
            gap: 6px;
            min-width: 0;
        }
        .tree-toggle-badge {
            width: 16px;
            height: 16px;
            border-radius: 3px;
            background: rgba(255,255,255,0.15);
            color: #fff;
            font-size: 11px;
            font-weight: 900;
            display: grid;
            place-items: center;
            flex-shrink: 0;
        }
        .tree-topic-block.is-open .tree-toggle-badge { background: #ffcc00; color: #1e1b4b; }
        
        .tree-topic-tools {
            display: flex;
            align-items: center;
            gap: 4px;
            flex-shrink: 0;
        }
        .badge-count {
            font-size: 10px;
            padding: 1px 5px;
            border-radius: 99px;
            background: rgba(255,255,255,0.15);
            color: #e2e8f0;
            font-weight: 700;
        }
        .btn-tree-add {
            width: 18px;
            height: 18px;
            border-radius: 3px;
            border: none;
            background: rgba(255,255,255,0.15);
            color: #fff;
            font-size: 12px;
            font-weight: 900;
            cursor: pointer;
            display: grid;
            place-items: center;
        }
        .btn-tree-add:hover { background: #10b981; }

        .tree-test-branch {
            display: none;
            flex-direction: column;
            gap: 2px;
            padding: 4px 6px 8px 14px;
            border-top: 1px dashed rgba(255,255,255,0.1);
        }
        .tree-topic-block.is-open .tree-test-branch { display: flex; }


        .sidebar-bottom-panel {
            padding: 10px 14px;
            border-top: 1px solid rgba(255,255,255,0.1);
            display: flex;
            flex-direction: column;
            gap: 6px;
            background: rgba(0,0,0,0.15);
        }
        .sidebar-link-btn {
            padding: 7px 10px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 800;
            color: #e2e8f0;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
        }
        .sidebar-link-btn:hover { background: rgba(255,255,255,0.14); }

        /* 2. MAIN WORKSPACE */
        .workspace {
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
            background: var(--bg-body);
        }

        /* Top Navbar */
        .workspace-navbar {
            height: 52px;
            min-height: 52px;
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);
            border-bottom: 2px solid #4338ca;
            padding: 0 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: nowrap;
            flex-shrink: 0;
            z-index: 20;
            box-shadow: 0 2px 10px rgba(0,0,0,0.15);
            overflow: hidden;
        }
        .btn-toggle-sidebar {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.25);
            color: #ffffff;
            display: grid;
            place-items: center;
            font-size: 13px;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.15s;
            flex-shrink: 0;
        }
        .btn-toggle-sidebar:hover {
            background: rgba(255,255,255,0.28);
            transform: scale(1.05);
        }
        .nav-breadcrumbs-info {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12.5px;
            font-weight: 700;
            color: #cbd5e1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            min-width: 0;
            flex-shrink: 1;
        }
        .nav-breadcrumbs-info span,
        .nav-breadcrumbs-info strong {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .nav-breadcrumbs-info strong { color: #ffffff; font-weight: 900; }
        .nav-q-active-crumb {
            color: #fbbf24 !important;
            font-weight: 900 !important;
            background: rgba(251, 191, 36, 0.16);
            padding: 2px 9px;
            border-radius: 6px;
            border: 1.5px solid rgba(251, 191, 36, 0.4);
            display: inline-flex;
            align-items: center;
            gap: 4px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.2);
            transition: all 0.2s ease;
        }
        .nav-q-active-crumb.is-creating {
            color: #34d399 !important;
            background: rgba(16, 185, 129, 0.2);
            border-color: #34d399 !important;
            box-shadow: 0 0 8px rgba(16, 185, 129, 0.35);
        }
        
        .nav-quick-badges {
            display: flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
        }
        .nav-badge-pill {
            padding: 3px 10px;
            border-radius: 99px;
            font-size: 11.5px;
            font-weight: 800;
            background: rgba(255,255,255,0.12);
            color: #38bdf8;
            border: 1px solid rgba(255,255,255,0.2);
        }

        .nav-action-buttons {
            display: flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }
        .btn-nav-primary {
            padding: 7px 14px;
            border-radius: 7px;
            font-size: 12.5px;
            font-weight: 800;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
            border: none;
            transition: 0.15s;
            white-space: nowrap;
        }
        .btn-nav-student {
            background: #10b981;
            color: #ffffff;
            font-weight: 800;
            box-shadow: 0 2px 8px rgba(16,185,129,0.3);
        }
        .btn-nav-student:hover { background: #059669; }
        .btn-nav-preview {
            background: #0284c7;
            color: #ffffff;
            font-weight: 800;
            box-shadow: 0 2px 8px rgba(2,132,199,0.3);
        }
        .btn-nav-preview:hover { background: #0369a1; }
        .btn-nav-save {
            background: linear-gradient(135deg, #10b981, #059669);
            color: #ffffff;
            font-weight: 900;
            box-shadow: 0 2px 10px rgba(16, 185, 129, 0.4);
            transition: all 0.2s ease;
        }
        .btn-nav-save:hover { transform: translateY(-1px); }
        .btn-nav-save.is-dirty {
            background: linear-gradient(135deg, #f59e0b, #ea580c) !important;
            color: #ffffff !important;
            box-shadow: 0 0 18px rgba(245, 158, 11, 0.75) !important;
            animation: pulseSaveDirty 1.5s infinite alternate ease-in-out !important;
        }
        @keyframes pulseSaveDirty {
            0% { transform: scale(1); box-shadow: 0 0 8px rgba(245, 158, 11, 0.5); }
            100% { transform: scale(1.03); box-shadow: 0 0 22px rgba(234, 88, 12, 0.95); }
        }
        @keyframes pulseWarn {
            0% { opacity: 0.85; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.05); }
            100% { opacity: 0.85; transform: scale(1); }
        }

        .dirty-badge-count {
            background: #ef4444;
            color: #ffffff;
            font-size: 11px;
            font-weight: 900;
            padding: 2px 7px;
            border-radius: 999px;
            margin-left: 6px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.35);
            display: inline-block;
            vertical-align: middle;
            line-height: 1.2;
            animation: pulseBadge 1.2s infinite ease-in-out;
            border: 1px solid #fee2e2;
        }
        @keyframes pulseBadge {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.18); }
        }

        .is-dirty-field {
            border: 2.5px solid #f59e0b !important;
            box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.25), 0 2px 8px rgba(245, 158, 11, 0.2) !important;
            animation: pulseDirtyField 2s infinite ease-in-out !important;
            background-color: #fffbeb !important;
        }
        @keyframes pulseDirtyField {
            0%, 100% { box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.25); }
            50% { box-shadow: 0 0 0 6px rgba(245, 158, 11, 0.45); }
        }

        /* Split Master-Detail Layout */
        .workspace-split-view {
            display: block;
            flex: 1;
            height: calc(100vh - 52px);
            overflow: hidden;
        }

        /* Right Column: Question Editor Workspace */
        .question-editor-pane {
            height: 100%;
            overflow-y: auto;
            padding: 20px 24px 100px;
            background: #f1f5f9;
            position: relative;
        }
        .question-editor-pane::-webkit-scrollbar { width: 6px; }
        .question-editor-pane::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 6px; }

        .editor-form-wrapper {
            max-width: 1000px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        /* Section Cards */
        .section-box {
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            padding: 20px 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            transition: transform 0.15s, box-shadow 0.15s;
        }
        .section-box.box-prompt { border-top: 4px solid #6366f1; border-color: #e0e7ff; box-shadow: 0 4px 20px rgba(99, 102, 241, 0.04); }
        .section-box.box-options { border-top: 4px solid #10b981; border-color: #d1fae5; box-shadow: 0 4px 20px rgba(16, 185, 129, 0.04); }
        .section-box.box-media { border-top: 4px solid #8b5cf6; border-color: #ede9fe; box-shadow: 0 4px 20px rgba(139, 92, 246, 0.04); }

        .section-header-line {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid #f1f5f9;
        }
        .section-title {
            font-size: 13px;
            font-weight: 900;
            color: var(--navy-deep);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        /* Smart Adaptive Type Guide Bar */
        .type-explanation-banner {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e40af;
            padding: 9px 14px;
            border-radius: 8px;
            font-size: 12.5px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 14px;
        }

        .field-grid-3col {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 12px;
            margin-top: 12px;
        }
        .field-unit {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .field-label {
            font-size: 11.5px;
            font-weight: 800;
            color: var(--slate-dark, #334155);
            text-transform: uppercase;
        }
        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 9px 12px;
            border: 1.5px solid var(--border-color);
            border-radius: 8px;
            font-size: 13.5px;
            font-family: inherit;
            font-weight: 600;
            color: var(--navy-deep);
            background: #ffffff;
            outline: none;
            transition: 0.15s;
        }
        .form-input:focus, .form-select:focus, .form-textarea:focus {
            border-color: var(--border-focus);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
        }
        .form-textarea {
            min-height: 80px;
            line-height: 1.45;
            resize: vertical;
        }

        /* Option Cards Stack */
        .options-stack {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        /* 1. Standard Choice Card (A/B/C/D) */
        .option-row-card {
            display: grid;
            grid-template-columns: 42px minmax(0, 1fr) auto 120px 36px;
            gap: 12px;
            align-items: center;
            padding: 12px 16px;
            border-radius: 12px;
            border: 2px solid #e2e8f0;
            background: #ffffff;
            transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        }
        .option-row-card:hover { border-color: #a7f3d0; background: #fafafa; transform: translateY(-1px); }
        
        .opt-letter-badge {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            font-weight: 900;
            font-size: 15px;
            color: #ffffff;
            display: grid;
            place-items: center;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        .opt-letter-A { background: linear-gradient(135deg, #2563eb, #1d4ed8); }
        .opt-letter-B { background: linear-gradient(135deg, #10b981, #059669); }
        .opt-letter-C { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .opt-letter-D { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
        .opt-letter-E { background: linear-gradient(135deg, #ec4899, #db2777); }
        .opt-letter-F { background: linear-gradient(135deg, #06b6d4, #0891b2); }

        .option-row-card.is-correct {
            background: linear-gradient(135deg, #dcfce7, #bbf7d0) !important;
            border: 3px solid #16a34a !important;
            box-shadow: 0 6px 20px rgba(22, 163, 74, 0.25) !important;
        }
        .option-row-card.is-correct .btn-correct-chk {
            background: #15803d !important;
            color: #ffffff !important;
            border-color: #14532d !important;
            font-weight: 900 !important;
            box-shadow: 0 3px 10px rgba(21, 128, 61, 0.4) !important;
        }
        .option-row-card.is-correct .opt-inp-txt {
            background: #ffffff !important;
            border-color: #16a34a !important;
            color: #14532d !important;
            font-weight: 700 !important;
        }

        /* 2. MATCHING PAIR CARD (Cặp Ghép Nối Vế Trái ──🔗──> Vế Phải) */
        .matching-pair-card {
            display: grid;
            grid-template-columns: 75px minmax(0, 1fr) 30px minmax(0, 1fr) 32px;
            gap: 10px;
            align-items: center;
            padding: 12px 14px;
            border-radius: 10px;
            border: 2px solid #a7f3d0;
            background: #f0fdf4;
            transition: all 0.15s;
        }
        .matching-pair-card:hover { border-color: #10b981; box-shadow: 0 3px 8px rgba(16,185,129,0.12); }
        .matching-pair-badge {
            background: #059669;
            color: #ffffff;
            font-size: 11.5px;
            font-weight: 900;
            padding: 6px 8px;
            border-radius: 6px;
            text-align: center;
            white-space: nowrap;
        }
        .matching-link-icon {
            color: #059669;
            font-size: 16px;
            font-weight: 900;
            text-align: center;
            display: grid;
            place-items: center;
        }

        /* 3. SEQUENCE STEP CARD (Bước 1, Bước 2...) */
        .sequence-step-card {
            display: grid;
            grid-template-columns: 80px minmax(0, 1fr) 32px;
            gap: 10px;
            align-items: center;
            padding: 10px 14px;
            border-radius: 10px;
            border: 2px solid #fed7aa;
            background: #fff7ed;
            transition: all 0.15s;
        }
        .sequence-step-badge {
            background: #ea580c;
            color: #ffffff;
            font-size: 11.5px;
            font-weight: 900;
            padding: 6px 8px;
            border-radius: 6px;
            text-align: center;
        }

        /* Smart Image Control Slot in Option */
        .option-image-control-box {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .opt-thumb-preview {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            object-fit: cover;
            border: 1.5px solid #cbd5e1;
            background: #ffffff;
            cursor: zoom-in;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
            transition: transform 0.15s, border-color 0.15s, box-shadow 0.15s;
        }
        .opt-thumb-preview:hover {
            transform: scale(1.12);
            border-color: #6366f1;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .btn-opt-action-img, .btn-opt-img-action {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 11.5px;
            font-weight: 800;
            cursor: pointer;
            border: none;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
            transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn-opt-change {
            background: #eff6ff;
            color: #1d4ed8;
            border: 1.5px solid #bfdbfe;
        }
        .btn-opt-change:hover {
            background: #2563eb;
            color: #ffffff;
            border-color: #2563eb;
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(37, 99, 235, 0.35);
        }
        .btn-opt-clear {
            background: #fef2f2;
            color: #dc2626;
            border: 1.5px solid #fecaca;
        }
        .btn-opt-clear:hover {
            background: #dc2626;
            color: #ffffff;
            border-color: #dc2626;
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(220, 38, 38, 0.35);
        }
        .btn-opt-add-new {
            background: #faf5ff;
            color: #7c3aed;
            border: 1.5px dashed #c084fc;
            font-weight: 800;
        }
        .btn-opt-add-new:hover {
            background: #7c3aed;
            border-color: #7c3aed;
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 3px 10px rgba(124, 58, 237, 0.35);
        }

        .btn-correct-chk {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 8px;
            border: 1.5px solid #cbd5e1;
            background: #f8fafc;
            color: #334155;
            font-size: 12.5px;
            font-weight: 800;
            cursor: pointer;
            user-select: none;
            transition: all 0.15s;
        }
        .btn-correct-chk:hover {
            border-color: #10b981;
            background: #f0fdf4;
            color: #047857;
        }
        .btn-correct-chk input {
            cursor: pointer;
            width: 16px;
            height: 16px;
            accent-color: #16a34a;
        }
        .btn-del-opt {
            width: 32px;
            height: 32px;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            color: #94a3b8;
            font-size: 13px;
            font-weight: 900;
            cursor: pointer;
            border-radius: 8px;
            display: grid;
            place-items: center;
            transition: all 0.15s;
        }
        .btn-del-opt:hover {
            color: #dc2626;
            background: #fee2e2;
            border-color: #fca5a5;
            transform: scale(1.1);
            box-shadow: 0 2px 8px rgba(220, 38, 38, 0.25);
        }

        .options-bottom-action-bar {
            margin-top: 14px;
        }
        .btn-add-choice-bottom {
            width: 100%;
            padding: 11px 20px;
            background: #f0fdf4;
            border: 2px dashed #86efac;
            border-radius: 12px;
            color: #15803d;
            font-size: 13px;
            font-weight: 800;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        }
        .btn-add-choice-bottom:hover {
            background: #dcfce7;
            border-color: #22c55e;
            color: #14532d;
            transform: translateY(-2px);
            box-shadow: 0 4px 14px rgba(34, 197, 94, 0.22);
        }
        .btn-add-icon-circle {
            width: 24px;
            height: 24px;
            background: #16a34a;
            color: #ffffff;
            border-radius: 50%;
            display: grid;
            place-items: center;
            font-size: 14px;
            font-weight: 900;
            box-shadow: 0 2px 6px rgba(22, 163, 74, 0.35);
            transition: transform 0.2s ease-in-out;
        }
        .btn-add-choice-bottom:hover .btn-add-icon-circle {
            transform: scale(1.15) rotate(90deg);
        }

        /* Media Assets Gallery (Khối 3) */
        .media-assets-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 14px;
        }
        .media-asset-card {
            width: 175px;
            background: #ffffff;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .media-asset-card:hover {
            border-color: #a855f7;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(168, 85, 247, 0.2);
        }
        .media-asset-thumb-wrap {
            width: 100%;
            height: 110px;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border-bottom: 1.5px solid #f1f5f9;
        }
        .media-asset-thumb-wrap img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            cursor: zoom-in;
            transition: transform 0.2s;
        }
        .media-asset-thumb-wrap img:hover {
            transform: scale(1.08);
        }
        .media-asset-info {
            padding: 8px 10px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .media-file-name {
            font-size: 11px;
            font-weight: 700;
            color: #334155;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: block;
            text-align: center;
        }
        .media-action-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 6px;
        }
        .btn-media-tool {
            flex: 1;
            padding: 5px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 800;
            cursor: pointer;
            border: none;
            transition: all 0.15s;
            text-align: center;
        }
        .btn-media-view {
            background: #eff6ff;
            color: #2563eb;
            border: 1px solid #bfdbfe;
        }
        .btn-media-view:hover {
            background: #2563eb;
            color: #ffffff;
        }
        .btn-media-del {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
        .btn-media-del:hover {
            background: #dc2626;
            color: #ffffff;
        }

        /* Fixed Action Footer Dock at bottom of viewport */
        .editor-bottom-bar {
            position: fixed;
            bottom: 0;
            right: 0;
            left: var(--sidebar-width, 320px);
            background: #0f172a;
            border-top: 2px solid #1e293b;
            padding: 12px 28px;
            z-index: 50;
            box-shadow: 0 -8px 25px rgba(0, 0, 0, 0.35);
            transition: left 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .app-container.is-resizing .editor-bottom-bar {
            transition: none !important;
        }
        .app-container.sidebar-collapsed .editor-bottom-bar {
            left: 0px !important;
        }
        .editor-bottom-bar-inner {
            width: 100%;
            max-width: 1000px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
        }
        .btn-delete-q {
            background: #dc2626;
            color: #ffffff;
            border: none;
            padding: 9px 18px;
            border-radius: 9px;
            font-size: 13px;
            font-weight: 900;
            cursor: pointer;
            transition: 0.15s;
            box-shadow: 0 2px 8px rgba(220, 38, 38, 0.35);
        }
        .btn-delete-q:hover { background: #b91c1c; transform: scale(1.03); }

        /* Toast Notification */
        .toast-notification {
            position: fixed;
            top: 65px;
            right: 25px;
            background: #0f172a;
            color: #ffffff;
            padding: 12px 20px;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.25);
            z-index: 99999;
            transform: translateY(-20px);
            opacity: 0;
            pointer-events: none;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .toast-notification.show {
            transform: translateY(0);
            opacity: 1;
            pointer-events: auto;
        }
        .toast-notification.toast-success { background: #15803d; border: 1px solid #22c55e; }
        .toast-notification.toast-error { background: #b91c1c; border: 1px solid #ef4444; }

        /* Generic Confirmation Modal */
        .confirm-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(3px);
            display: none;
            place-items: center;
            z-index: 10000;
            padding: 20px;
        }
        .confirm-modal-overlay.active { display: grid; }
        .confirm-modal-card {
            background: #ffffff;
            border-radius: 14px;
            width: min(420px, 100%);
            padding: 22px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            text-align: center;
            display: flex;
            flex-direction: column;
            gap: 14px;
            animation: confirmPop 0.18s ease-out;
        }
        @keyframes confirmPop { from { transform: scale(0.92); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        .confirm-icon-badge {
            width: 48px;
            height: 48px;
            border-radius: 99px;
            background: #fee2e2;
            color: #dc2626;
            font-size: 22px;
            display: grid;
            place-items: center;
            margin: 0 auto;
        }
        .confirm-modal-title { font-size: 16px; font-weight: 900; color: #0f172a; }
        .confirm-modal-desc { font-size: 13px; color: #64748b; line-height: 1.4; }
        .confirm-modal-btns { display: flex; gap: 8px; justify-content: center; margin-top: 6px; }
        .btn-confirm-cancel { padding: 9px 16px; border-radius: 8px; font-weight: 800; font-size: 13px; border: 1px solid #cbd5e1; background: #f8fafc; color: #334155; cursor: pointer; }
        .btn-confirm-ok { padding: 9px 18px; border-radius: 8px; font-weight: 800; font-size: 13px; border: none; background: #dc2626; color: #fff; cursor: pointer; }

        /* Student Preview Simulator Modal */
        .preview-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(4px);
            display: none;
            place-items: center;
            z-index: 9999;
            padding: 20px;
        }
        .preview-modal-overlay.active { display: grid; }
        /* 🌌 IC3 QUEST ARENA REAL SIMULATOR MODAL (100% Student Exam Match) */
        .preview-simulator-box {
            background: #061021;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(0, 242, 254, 0.22) 0%, transparent 45%),
                radial-gradient(circle at 90% 80%, rgba(161, 140, 209, 0.25) 0%, transparent 50%),
                radial-gradient(circle at 50% 50%, rgba(24, 76, 144, 0.3) 0%, transparent 70%);
            border-radius: 22px;
            width: min(1060px, 96vw);
            box-shadow: 0 0 50px rgba(0, 242, 254, 0.35), 0 25px 60px rgba(0,0,0,0.8);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            border: 2.5px solid #00f2fe;
            animation: simFade 0.2s ease-out;
            color: #ffffff;
        }
        @keyframes simFade { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        
        .sim-arena-topbar {
            background: linear-gradient(180deg, rgba(8, 25, 52, 0.98), rgba(6, 18, 38, 0.95));
            border-bottom: 2px solid #00f2fe;
            padding: 10px 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .sim-arena-brand {
            font-size: 18px;
            font-weight: 900;
            color: #ffe658;
            text-shadow: 0 2px 0 #7e4200, 0 0 15px rgba(255, 230, 88, 0.6);
            display: flex;
            align-items: center;
            gap: 8px;
            letter-spacing: 0.8px;
        }
        .sim-stage-pill {
            background: rgba(0, 242, 254, 0.15);
            border: 1.5px solid rgba(0, 242, 254, 0.4);
            border-radius: 12px;
            padding: 4px 12px;
            font-size: 12px;
            font-weight: 800;
            color: #e0f7ff;
        }
        .btn-exit {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            background: rgba(255, 255, 255, 0.1);
            border: 1.5px solid rgba(255, 255, 255, 0.25);
            border-radius: 999px;
            color: #fff;
            font-weight: 900;
            font-size: 12px;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-exit:hover {
            background: linear-gradient(180deg, #ff4757, #c0392b);
            border-color: #ff6b81;
            color: #fff;
        }
        .btn-ctrl {
            width: 32px;
            height: 32px;
            border-radius: 9px;
            border: 1.5px solid rgba(255, 255, 255, 0.25);
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            font-size: 14px;
            display: grid;
            place-items: center;
            cursor: pointer;
            transition: all 0.15s;
        }
        .btn-ctrl:hover {
            background: rgba(255, 255, 255, 0.25);
            border-color: #00f2fe;
        }

        .sim-arena-checkpoint-bar {
            background: rgba(10, 30, 60, 0.85);
            border: 1.5px solid rgba(0, 242, 254, 0.35);
            border-radius: 12px;
            padding: 6px 14px;
            display: flex;
            align-items: center;
            gap: 6px;
            overflow-x: auto;
            margin: 12px 20px 0;
        }
        .sim-cp-node {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.08);
            border: 1.5px solid rgba(255, 255, 255, 0.25);
            color: #b8d5f8;
            font-size: 12px;
            font-weight: 900;
            display: grid;
            place-items: center;
        }
        .sim-cp-node.active {
            background: linear-gradient(180deg, #ffc048, #ff9f1a);
            border-color: #ffeaa7;
            color: #4a2700;
            box-shadow: 0 0 12px rgba(255, 159, 26, 0.8);
            transform: scale(1.1);
        }

        .sim-body {
            padding: 16px 20px;
            display: flex;
            flex-direction: column;
            gap: 14px;
            max-height: 72vh;
            overflow-y: auto;
        }
        
        .sim-quest-card {
            background: rgba(8, 28, 58, 0.95);
            border: 2px solid #00f2fe;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5), inset 0 0 20px rgba(0, 242, 254, 0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .sim-quest-head {
            background: linear-gradient(180deg, rgba(0, 242, 254, 0.12), transparent);
            border-bottom: 1.5px solid rgba(0, 242, 254, 0.25);
            padding: 12px 18px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .sim-meta-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .sim-type-pill {
            background: linear-gradient(135deg, rgba(0, 242, 254, 0.2), rgba(79, 172, 254, 0.25));
            border: 1px solid #00f2fe;
            border-radius: 8px;
            padding: 3px 10px;
            font-size: 11.5px;
            font-weight: 900;
            color: #00f2fe;
            text-transform: uppercase;
        }
        .sim-q-num-txt {
            font-size: 13px;
            font-weight: 900;
            color: #94a3b8;
        }
        .sim-q-num-txt b { color: #00f2fe; }
        .sim-q-title-text {
            font-size: 16px;
            font-weight: 800;
            color: #ffffff;
            line-height: 1.5;
        }

        .sim-quest-content {
            padding: 16px 20px 20px;
            display: flex;
            flex-direction: column;
            gap: 14px;
            align-items: center;
        }

        /* Arena Choice Buttons (100% Khớp Giao diện Học sinh launch.blade.php) */
        .sim-arena-choices-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 14px;
            width: 100%;
            margin: auto 0;
            padding: 4px 0 10px;
        }
        .sim-arena-choice-btn {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 10px 18px;
            background: #ffffff;
            border: 2.5px solid #e2e8f0;
            border-radius: 16px;
            color: #0f172a;
            font-family: inherit;
            font-size: 15px;
            font-weight: 850;
            text-align: left;
            cursor: pointer;
            transition: all 0.15s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 0 #cbd5e1, 0 6px 14px rgba(0, 0, 0, 0.12);
            min-height: 56px;
        }
        .sim-arena-choice-btn:hover {
            background: #f8fafc;
            border-color: #38bdf8;
            transform: translateY(-2px);
            box-shadow: 0 6px 0 #0284c7, 0 8px 18px rgba(2, 132, 199, 0.25);
        }
        .sim-arena-choice-btn.selected {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;
            border-color: #ffeaa7 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 0 #1e3a8a, 0 0 20px rgba(37, 99, 235, 0.7) !important;
            transform: translateY(-2px);
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }
        .sim-arena-choice-btn.selected .sim-choice-text {
            color: #ffffff !important;
        }
        .sim-arena-choice-btn.correct-reveal {
            background: linear-gradient(180deg, #f0fdf4 0%, #dcfce7 100%) !important;
            border-color: #16a34a !important;
            color: #14532d !important;
            box-shadow: 0 4px 0 #15803d, 0 0 18px rgba(34, 197, 94, 0.45) !important;
        }
        .sim-arena-choice-btn.correct-reveal .sim-choice-text {
            color: #14532d !important;
        }
        .sim-arena-choice-btn.wrong-reveal {
            background: linear-gradient(180deg, #fef2f2 0%, #fee2e2 100%) !important;
            border-color: #dc2626 !important;
            color: #991b1b !important;
            box-shadow: 0 4px 0 #b91c1c, 0 0 18px rgba(239, 68, 68, 0.45) !important;
        }
        .sim-arena-choice-btn.wrong-reveal .sim-choice-text {
            color: #991b1b !important;
        }

        .sim-choice-key {
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
        .sim-arena-choice-btn:nth-child(1) .sim-choice-key { background: linear-gradient(135deg, #0ea5e9, #0284c7); box-shadow: 0 3px 0 #0369a1; }
        .sim-arena-choice-btn:nth-child(2) .sim-choice-key { background: linear-gradient(135deg, #8b5cf6, #6d28d9); box-shadow: 0 3px 0 #5b21b6; }
        .sim-arena-choice-btn:nth-child(3) .sim-choice-key { background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 3px 0 #047857; }
        .sim-arena-choice-btn:nth-child(4) .sim-choice-key { background: linear-gradient(135deg, #f97316, #c2410c); box-shadow: 0 3px 0 #9a3412; }
        .sim-arena-choice-btn:nth-child(5) .sim-choice-key { background: linear-gradient(135deg, #ec4899, #be185d); box-shadow: 0 3px 0 #9d174d; }
        .sim-arena-choice-btn:nth-child(6) .sim-choice-key { background: linear-gradient(135deg, #06b6d4, #0e7490); box-shadow: 0 3px 0 #155e75; }
        .sim-arena-choice-btn:nth-child(7) .sim-choice-key { background: linear-gradient(135deg, #eab308, #a16207); box-shadow: 0 3px 0 #854d0e; }
        .sim-arena-choice-btn:nth-child(8) .sim-choice-key { background: linear-gradient(135deg, #6366f1, #4338ca); box-shadow: 0 3px 0 #3730a3; }

        .sim-arena-choice-btn.selected .sim-choice-key {
            background: #ffeaa7 !important;
            color: #4a2700 !important;
            border-color: #ffffff !important;
            box-shadow: 0 0 14px #facc15 !important;
        }

        .sim-choice-content {
            flex: 1;
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: flex-start;
            gap: 12px;
            width: 100%;
            min-width: 0;
        }
        .sim-choice-img {
            max-width: 140px;
            max-height: 80px;
            width: auto;
            height: auto;
            object-fit: contain;
            border-radius: 8px;
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            padding: 4px;
            flex-shrink: 0;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }
        .sim-choice-text {
            line-height: 1.35;
            text-align: left;
            font-size: 15px;
            font-weight: 800;
            color: inherit;
            word-break: break-word;
        }

        /* Arena Footer & Feedback */
        .sim-arena-foot {
            background: rgba(8, 25, 52, 0.98);
            border-top: 1.5px solid rgba(0, 242, 254, 0.3);
            padding: 12px 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
        }
        .btn-arena-sub {
            padding: 10px 24px;
            border-radius: 999px;
            border: 2px solid #00f2fe;
            background: linear-gradient(180deg, #00f2fe, #0284c7);
            color: #061021;
            font-size: 14px;
            font-weight: 900;
            cursor: pointer;
            box-shadow: 0 0 15px rgba(0, 242, 254, 0.5);
            transition: all 0.15s;
        }
        .btn-arena-sub:hover {
            transform: scale(1.04);
            box-shadow: 0 0 25px rgba(0, 242, 254, 0.8);
        }
        .sim-feedback {
            padding: 12px 18px;
            border-radius: 12px;
            font-weight: 900;
            font-size: 14px;
            display: none;
            width: 100%;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }

        /* ⚡ 100% IDENTICAL USER PLAYER (LAUNCH.BLADE.PHP) STYLES FOR SIMULATOR */
        .neon-wire-container {
            position: relative;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            width: 100%;
            max-width: 920px;
            margin: 0 auto;
            user-select: none;
            padding: 4px 0 16px;
        }
        .wire-column {
            display: flex;
            flex-direction: column;
            gap: 8px;
            z-index: 5;
        }
        .wire-card {
            background: #ffffff;
            border: 2.5px solid #cbd5e1;
            border-radius: 14px;
            padding: 8px 14px;
            color: #0f172a;
            font-size: 13.5px;
            font-weight: 800;
            line-height: 1.3;
            min-height: 46px;
            display: flex;
            align-items: center;
            gap: 10px;
            position: relative;
            box-shadow: 0 3px 0 #cbd5e1, 0 4px 12px rgba(0, 0, 0, 0.06);
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
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #94a3b8;
            border: 3px solid #ffffff;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.3);
            transition: all 0.2s ease;
            z-index: 10;
            display: grid;
            place-items: center;
            font-size: 10px;
            font-weight: 900;
            color: #fff;
        }
        .wire-left-card .wire-port { right: -10px; }
        .wire-right-card .wire-port { left: -10px; }

        .wire-card.pair-theme-0 { border-color: #00f2fe; background: #f0fdff; box-shadow: 0 4px 0 #0284c7, 0 0 16px rgba(0, 242, 254, 0.35); }
        .wire-card.pair-theme-0 .wire-port { background: #00f2fe; box-shadow: 0 0 14px #00f2fe; border-color: #0284c7; }
        .wire-card.pair-theme-0 .wire-idx-badge { background: #00f2fe; color: #082f49; border-color: #0284c7; }

        .wire-card.pair-theme-1 { border-color: #f43f5e; background: #fff1f2; box-shadow: 0 4px 0 #be185d, 0 0 16px rgba(244, 63, 94, 0.35); }
        .wire-card.pair-theme-1 .wire-port { background: #f43f5e; box-shadow: 0 0 14px #f43f5e; border-color: #be185d; }
        .wire-card.pair-theme-1 .wire-idx-badge { background: #f43f5e; color: #ffffff; border-color: #be185d; }

        .wire-card.pair-theme-2 { border-color: #10b981; background: #f0fdf4; box-shadow: 0 4px 0 #047857, 0 0 16px rgba(16, 185, 129, 0.35); }
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

        /* 📋 Classify (MultipleChoiceText) Styles */
        .classify-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
            width: 100%;
            max-width: 940px;
            margin: 0 auto;
        }
        .classify-item {
            display: grid;
            grid-template-columns: 1.15fr 1.25fr;
            align-items: center;
            gap: 16px;
            padding: 10px 18px;
            background: #ffffff;
            border: 2.5px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 3px 0 #cbd5e1, 0 4px 12px rgba(0, 0, 0, 0.06);
            transition: all 0.2s ease;
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
            box-shadow: 0 0 10px rgba(16, 185, 129, 0.45);
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

        /* 🎯 Clean & Smart Hotspot Arena (100% Đồng bộ launch.blade.php) */
        .hotspot-outer-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 14px;
            width: 100%;
            max-width: 860px;
            margin: 0 auto;
            padding: 10px 0;
        }
        .hotspot-instruction-hint {
            font-size: 13.5px;
            font-weight: 850;
            color: #38bdf8;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .hotspot-wrapper {
            position: relative;
            display: block;
            width: 100%;
            max-width: 860px;
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
            width: 100%;
            max-width: 100%;
            height: auto;
            object-fit: contain;
            pointer-events: none;
        }
        .hotspot-target-marker {
            position: absolute;
            transform: translate(-50%, -50%);
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(239, 68, 68, 0.2);
            border: 3px solid #ef4444;
            display: grid;
            place-items: center;
            box-shadow: 0 0 15px #ef4444, inset 0 0 8px #ef4444;
            pointer-events: none;
            z-index: 20;
            animation: targetPulse 1.2s infinite alternate ease-in-out;
        }
        .hotspot-target-marker::after {
            content: '';
            width: 8px;
            height: 8px;
            background: #ef4444;
            border-radius: 50%;
            box-shadow: 0 0 6px #fff;
        }
        @keyframes targetPulse {
            from { transform: translate(-50%, -50%) scale(0.9); }
            to { transform: translate(-50%, -50%) scale(1.15); box-shadow: 0 0 25px #ef4444; }
        }
        .hotspot-review-zone {
            position: absolute;
            border-radius: 8px;
            pointer-events: none;
            z-index: 15;
            display: flex;
            align-items: flex-start;
            padding: 4px 6px;
        }
        .hotspot-review-zone.correct-zone {
            border: 3px solid #22c55e;
            background: rgba(34, 197, 94, 0.25);
            box-shadow: 0 0 15px rgba(34, 197, 94, 0.6);
        }
        .hotspot-review-zone.wrong-zone {
            border: 3px solid #ef4444;
            background: rgba(239, 68, 68, 0.25);
            box-shadow: 0 0 15px rgba(239, 68, 68, 0.6);
        }

        /* Lightbox Image Zoom Modal */
        #modal-img-zoom {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(5px);
            display: none;
            place-items: center;
            z-index: 10000;
            padding: 24px;
        }
        #modal-img-zoom.active { display: grid; }
        .zoom-content-box {
            background: #ffffff;
            border-radius: 14px;
            padding: 16px;
            max-width: 90vw;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
        }
        .zoom-preview-img {
            max-width: 80vw;
            max-height: 75vh;
            object-fit: contain;
            border-radius: 8px;
            background: #f8fafc;
        }

        /* Generic Modals */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(3px);
            display: none;
            place-items: center;
            z-index: 999;
            padding: 16px;
        }
        .modal-overlay.active { display: grid; }
        .modal-card {
            background: #ffffff;
            border-radius: var(--radius-lg);
            width: min(460px, 100%);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            overflow: hidden;
        }
        .modal-head {
            padding: 14px 18px;
            background: #f8fafc;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-head h3 { font-size: 14px; font-weight: 900; }
        .modal-body { padding: 16px 18px; display: flex; flex-direction: column; gap: 10px; }
        .modal-foot { padding: 12px 18px; background: #f8fafc; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; gap: 8px; }

        @media (max-width: 1024px) {
            .app-container { grid-template-columns: 1fr; }
            .sidebar { height: auto; display: none; }
            .workspace-split-view { grid-template-columns: 1fr; }
            .question-list-sidebar { display: none; }
            .editor-bottom-bar { left: 0; }
        }
    </style>
</head>
<body>

    <!-- Toast Notification -->
    <div id="toast-msg" class="toast-notification">
        <span id="toast-icon">✓</span>
        <span id="toast-text">Thông báo</span>
    </div>

    <div class="app-container" id="app-container">
        @php 
            $allQuestions = $selectedTest ? $selectedTest->questions->sortBy('position') : collect();
            $isCreating = request('action') === 'new' || !$selectedQuestion;
        @endphp

        <!-- 1. SIDEBAR -->
        <aside class="sidebar" id="studio-sidebar">
            <div class="sidebar-header">
                <a href="{{ route('admin.dashboard') }}" class="brand-logo">
                    <span>IC3 GS6</span> STUDIO ADMIN
                </a>
                <button type="button" class="btn-sidebar-collapse-mini" onclick="toggleStudioSidebar()" title="Thu gọn Sidebar (Ctrl + B)">
                    ◀
                </button>
            </div>

            <!-- Grade Switcher -->
            <div class="sidebar-grade-section">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <div class="grade-section-title" style="margin-bottom: 0;">Chọn Khối Quản trị</div>
                    <button type="button" class="btn-add-grade-mini" onclick="openModal('modal-add-level')" title="＋ Thêm Khối lớp / Cấp độ mới">
                        <span>＋ Khối</span>
                    </button>
                </div>
                <div class="grade-pill-group">
                    @foreach($levels as $lvl)
                        <a href="{{ route('admin.questions.studio', ['grade' => $lvl->grade]) }}" 
                           class="grade-btn {{ $selectedGrade === $lvl->grade ? 'active' : '' }}"
                           title="{{ $lvl->name }}">
                            Khối {{ $lvl->grade }}
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Topic Management Topbar with Add Button -->
            <div class="sidebar-topic-topbar">
                <div class="topic-topbar-title">Chủ đề Khối {{ $selectedGrade }}</div>
                <button type="button" class="btn-add-topic-hero" onclick="openModal('modal-add-topic')" title="＋ Thêm chủ đề mới vào Khối {{ $selectedGrade }}">
                    <span>＋ Thêm chủ đề</span>
                </button>
            </div>

            <!-- Topic & Test Tree -->
            <div class="sidebar-tree-scroll">
                @forelse($topics as $tIdx => $t)
                    @php $isCurrentTopic = ($selectedTopic?->id === $t->id); @endphp
                    <div class="tree-topic-block {{ $isCurrentTopic ? 'is-open' : '' }}" id="topic-node-{{ $t->id }}">
                        <div class="tree-topic-head" onclick="toggleTopicNode('topic-node-{{ $t->id }}')">
                            <div class="tree-topic-name">
                                <span class="tree-toggle-badge">{{ $isCurrentTopic ? '−' : '+' }}</span>
                                <span>📁 {{ $t->name }}</span>
                            </div>
                            <div class="tree-topic-tools">
                                <span class="badge-count">{{ $t->tests->count() }}</span>
                                <button type="button" class="btn-tree-add" title="＋ Thêm bài test vào chủ đề này" onclick="event.stopPropagation(); openAddTestForTopic({{ $t->id }}, '{{ addslashes($t->name) }}')">＋</button>
                            </div>
                        </div>

                        <!-- Sub Tests & Questions Tree -->
                        <div class="tree-test-branch">
                            @forelse($t->tests as $testItem)
                                @php 
                                    $isCurrentTest = ($selectedTest?->id === $testItem->id); 
                                    $qCount = $testItem->question_count ?? ($isCurrentTest && isset($allQuestions) ? $allQuestions->count() : 0);
                                @endphp
                                <div class="tree-test-block" id="tree-test-{{ $testItem->id }}">
                                    <div class="tree-test-row">
                                        <a href="{{ route('admin.questions.studio', ['grade' => $selectedGrade, 'topic' => $t->id, 'test' => $testItem->id]) }}" 
                                           class="tree-test-item {{ $isCurrentTest ? 'is-active' : '' }}"
                                           title="{{ $testItem->name }}">
                                            <span class="tree-test-toggle-icon">{{ $isCurrentTest ? '▾' : '▸' }}</span>
                                            <span class="tree-test-name-txt">📝 {{ $testItem->name }}</span>
                                            <span class="badge-count-sm">{{ $qCount }}c</span>
                                        </a>
                                        @if($isCurrentTest)
                                            <button type="button" class="btn-tree-add-q-mini" title="＋ Thêm câu hỏi vào bài test này" onclick="event.stopPropagation(); switchToNewQuestionMode()">＋</button>
                                        @endif
                                    </div>

                                    @if($isCurrentTest && isset($allQuestions))
                                        <div class="tree-question-subbranch" id="q-items-scroll-list">
                                            <!-- New item placeholder -->
                                            <div class="tree-q-item q-nav-card" id="q-card-new-slot" style="display: {{ $isCreating ? 'flex' : 'none' }}; border: 1.5px dashed #10b981; background: rgba(16, 185, 129, 0.15);">
                                                <span class="tree-q-badge" style="background:#10b981; color:#fff;" id="q-num-new-label">Mới</span>
                                                <span class="tree-q-text" style="color:#6ee7b7; font-weight:800;">(Đang soạn câu mới...)</span>
                                            </div>

                                            @foreach($allQuestions as $idx => $qItem)
                                                @php $isActive = (!$isCreating && $selectedQuestion?->id === $qItem->id); @endphp
                                                <div class="tree-q-item q-nav-card {{ $isActive ? 'active' : '' }}" 
                                                     id="q-nav-item-{{ $qItem->id }}" 
                                                     onclick="loadQuestionRealtime({{ $qItem->id }})"
                                                     title="Câu {{ $loop->iteration }}: {{ $qItem->title }}">
                                                    <span class="tree-q-badge">#{{ $loop->iteration }}</span>
                                                    <span class="tree-q-icon">
                                                        @if($qItem->type === 'MultipleChoice') 🔘
                                                        @elseif($qItem->type === 'MultipleResponse') ☑️
                                                        @elseif($qItem->type === 'Matching') ⚡
                                                        @elseif($qItem->type === 'MultipleChoiceText') 📝
                                                        @elseif($qItem->type === 'Hotspot') 🎯
                                                        @elseif($qItem->type === 'Sequence') 🔢
                                                        @else ❓
                                                        @endif
                                                    </span>
                                                    <span class="tree-q-text" id="q-preview-text-{{ $qItem->id }}">
                                                        {{ $qItem->title ?: '(Chưa có nội dung đề bài)' }}
                                                    </span>
                                                </div>
                                            @endforeach

                                            <button type="button" class="tree-btn-add-q" onclick="switchToNewQuestionMode()">
                                                <span>＋</span> Thêm câu hỏi
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div style="font-size:11px; color:#cbd5e1; padding:2px 4px;">(Chưa có bài test)</div>
                            @endforelse
                        </div>
                    </div>
                @empty
                    <div style="padding: 18px 12px; text-align: center; color: #cbd5e1; font-size: 12.5px;">
                        <div>Chưa có chủ đề trong Khối {{ $selectedGrade }}.</div>
                        <button type="button" class="btn-add-topic-hero" style="margin-top: 10px;" onclick="openModal('modal-add-topic')">
                            <span>＋ Tạo chủ đề đầu tiên</span>
                        </button>
                    </div>
                @endforelse
            </div>
            <!-- Sidebar Footer -->
            <div class="sidebar-bottom-panel">
                <a href="{{ route('admin.dashboard') }}" class="sidebar-link-btn">
                    <span>🏠 Về Dashboard Quản trị</span>
                    <span>→</span>
                </a>
            </div>

            <!-- 📐 VS Code Draggable Sidebar Resizer Handle -->
            <div class="sidebar-resizer" id="sidebar-resizer" title="Kéo chuột để chỉnh độ rộng Sidebar (Nhấp đúp để về 320px mặc định)"></div>
        </aside>

        <!-- 2. MAIN WORKSPACE -->
        <main class="workspace">
            @php 
                $isCreating = request('action') === 'new' || !$selectedQuestion;
            @endphp

            <!-- Topbar -->
            <header class="workspace-navbar">
                <div style="display:flex; align-items:center; gap:10px; min-width:0; flex:1; overflow:hidden;">
                    <button type="button" class="btn-toggle-sidebar" id="btn-toggle-sidebar" onclick="toggleStudioSidebar()" title="Thu gọn / Mở rộng Sidebar (Ctrl + B)">
                        <span id="sidebar-toggle-icon">◀</span>
                    </button>
                    <div class="nav-breadcrumbs-info">
                        <span title="Khối {{ $selectedGrade }}">Khối {{ $selectedGrade }}</span>
                        <span>›</span>
                        <span title="{{ $selectedTopic?->name ?? 'Chủ đề' }}">{{ $selectedTopic?->name ?? 'Chủ đề' }}</span>
                        <span>›</span>
                        <strong title="{{ $selectedTest?->name ?? 'Bộ đề' }}" id="topbar-test-name">{{ $selectedTest?->name ?? 'Bộ đề' }}</strong>
                        <span>›</span>
                        <span id="topbar-q-crumb" class="nav-q-active-crumb {{ $isCreating ? 'is-creating' : '' }}">
                            {{ $isCreating ? '✨ Soạn câu mới' : 'Câu #' . (($selectedQuestion->position ?? 0) + 1) . ' / ' . ($selectedTest->questions->count() ?? 1) }}
                        </span>
                    </div>
                </div>

                @if($selectedTest)
                    <div class="nav-action-buttons" style="display:flex; align-items:center; gap:8px;">
                        <button type="button" class="btn-nav-primary" onclick="switchToNewQuestionMode()" style="background:linear-gradient(135deg, #10b981, #059669); border:1px solid #34d399; color:#fff; padding:6px 14px; border-radius:8px; font-weight:900; font-size:12px; cursor:pointer; display:flex; align-items:center; gap:5px; box-shadow:0 2px 6px rgba(16,185,129,0.35);" title="Thêm câu hỏi mới vào bài luyện">
                            <span>＋</span> Thêm câu hỏi
                        </button>
                        <button type="button" class="btn-nav-primary" onclick="openModal('modal-edit-test')" style="background:linear-gradient(135deg, #6366f1, #4f46e5); border:1px solid #818cf8; color:#fff; padding:6px 14px; border-radius:8px; font-weight:900; font-size:12px; cursor:pointer; display:flex; align-items:center; gap:5px; box-shadow:0 2px 6px rgba(99,102,241,0.35);" title="Cài đặt bộ đề (Xáo trộn câu hỏi, đáp án, thời gian...)">
                            <span>⚙️</span> Cài đặt đề
                        </button>
                        <a href="{{ route('tests.launch', $selectedTest->slug) }}" target="_blank" class="btn-nav-primary btn-nav-student" title="Thi thử toàn bộ đề thi trong tab mới">
                            <span>🚀</span> Thi thử cả đề
                        </a>
                    </div>
                @endif
            </header>

            @if($selectedTest)
                @php 
                    $allQuestions = $selectedTest->questions->sortBy('position');
                    $isCreating = request('action') === 'new' || !$selectedQuestion;
                @endphp

                <!-- Split Master-Detail -->
                <div class="workspace-split-view">

                    <!-- Right: Question Editor Workspace -->
                    <div class="question-editor-pane">
                        <div class="editor-form-wrapper">

                            <form id="studio-form" method="post" action="{{ $isCreating ? route('admin.questions.store') : route('admin.questions.update', $selectedQuestion) }}" enctype="multipart/form-data" onsubmit="event.preventDefault(); triggerSaveQuestion();">
                                @csrf
                                <input type="hidden" name="_method" id="form-method" value="{{ $isCreating ? 'POST' : 'PUT' }}">
                                <input type="hidden" name="practice_test_id" value="{{ $selectedTest->id }}">
                                <input type="hidden" id="current-q-id" value="{{ $selectedQuestion?->id ?? '' }}">

                                <!-- Block 1: Đề bài & Cấu hình (Indigo Royal) -->
                                <div class="section-box box-prompt" style="background: #ffffff; border-radius: 16px; border: 3px solid #6366f1; padding: 20px 24px; box-shadow: 0 8px 24px rgba(99, 102, 241, 0.15); overflow: hidden;">
                                    <div class="section-header-line" style="background: linear-gradient(135deg, #3730a3, #4f46e5); color: #ffffff; padding: 14px 20px; margin: -20px -24px 18px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; border-bottom: none;">
                                        <div style="display:flex; align-items:center; gap:10px;">
                                            <span style="width:34px; height:34px; border-radius:10px; background:rgba(255,255,255,0.2); color:#fff; display:grid; place-items:center; font-size:17px; box-shadow:0 2px 8px rgba(0,0,0,0.15);">
                                                📝
                                            </span>
                                            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                                                <strong id="editor-block-title" style="font-size:16px; font-weight:900; color:#ffffff; letter-spacing:0.3px;">
                                                    {{ $isCreating ? 'SOẠN THẢO CÂU HỎI MỚI' : 'KHỐI 1: CHỈNH SỬA CÂU #' . (($selectedQuestion->position ?? 0) + 1) }}
                                                </strong>
                                                <span id="editor-type-tag" style="background:#fbbf24; color:#1e1b4b; font-size:12px; font-weight:900; padding:3px 10px; border-radius:6px; box-shadow:0 2px 6px rgba(0,0,0,0.15);">
                                                    🎯 {{ $typeNames[$selectedQuestion?->type ?? 'MultipleChoice'] ?? 'Trắc nghiệm' }}
                                                </span>
                                                <span id="q-change-status-badge" style="display:none; padding:3px 10px; border-radius:6px; background:#ea580c; color:#ffffff; font-weight:900; font-size:11.5px; border:1px solid #f97316; box-shadow:0 2px 8px rgba(234,88,12,0.5); animation:pulseWarn 1.5s infinite ease-in-out;">
                                                    ⚠️ CÓ THAY ĐỔI CHƯA LƯU
                                                </span>
                                            </div>
                                        </div>

                                        <label style="display:inline-flex; align-items:center; gap:8px; cursor:pointer; background:rgba(255,255,255,0.18); border:1.5px solid rgba(255,255,255,0.35); padding:6px 16px; border-radius:999px; font-size:12.5px; font-weight:800; color:#ffffff; transition:0.15s;" title="Bật/Tắt hiển thị câu hỏi này cho học sinh">
                                            <input type="checkbox" name="is_published" id="form-inp-published" value="1" style="width:16px; height:16px; accent-color:#10b981; cursor:pointer;" 
                                                   @checked(old('is_published', $selectedQuestion?->is_published ?? true))>
                                            <span>🟢 Kích hoạt câu hỏi này</span>
                                        </label>
                                    </div>

                                    <div class="field-unit" style="display:flex; flex-direction:column; gap:6px;">
                                        <label class="field-label" style="display:flex; align-items:center; gap:6px; color:#312e81; font-weight:900; font-size:12.5px; letter-spacing:0.3px;">
                                            <span>💬</span> NỘI DUNG ĐỀ BÀI (HIỂN THỊ CHO HỌC SINH) <span style="color:#ef4444; font-weight:900;">*</span>
                                        </label>
                                        <textarea name="title" id="form-inp-title" class="form-textarea" style="background:#f8fafc; border:2.5px solid #818cf8; border-radius:12px; font-size:14.5px; line-height:1.5; color:#0f172a; padding:14px; font-weight:600; min-height:85px; box-shadow:inset 0 1px 3px rgba(0,0,0,0.02);" placeholder="Nhập đề bài chi tiết hiển thị cho học sinh..." required>{{ old('title', $selectedQuestion?->title) }}</textarea>
                                    </div>

                                    <div class="field-grid-3col" style="display:grid; grid-template-columns: 2fr 1fr 1fr; gap:14px; margin-top:16px;">
                                        <div class="field-unit" style="background:#ede9fe; border:2.5px solid #8b5cf6; border-radius:12px; padding:10px 14px; display:flex; flex-direction:column; gap:6px;">
                                            <label class="field-label" style="display:flex; align-items:center; gap:5px; color:#312e81; font-weight:900; font-size:11.5px;">
                                                <span>🎮</span> DẠNG TƯƠNG TÁC IC3 <span style="color:#ef4444; font-weight:900;">*</span>
                                            </label>
                                            <select name="type" id="sel-type" class="form-select" style="border:1.5px solid #818cf8; background:#ffffff; color:#312e81; font-weight:800; border-radius:8px; padding:9px 12px; cursor:pointer;" onchange="onQuestionTypeChange(this.value)">
                                                @foreach($typeNames as $tKey => $tLabel)
                                                    <option value="{{ $tKey }}" @selected(old('type', $selectedQuestion?->type ?? 'MultipleChoice') === $tKey)>
                                                        {{ $tLabel }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="field-unit" style="background:#fef08a; border:2.5px solid #eab308; border-radius:12px; padding:10px 14px; display:flex; flex-direction:column; gap:6px;">
                                            <div style="display:flex; align-items:center; justify-content:space-between;">
                                                <label class="field-label" style="display:flex; align-items:center; gap:5px; color:#854d0e; font-weight:900; font-size:11.5px;">
                                                    <span>⭐</span> HỆ SỐ ĐIỂM
                                                </label>
                                                <span style="font-size:10px; font-weight:900; color:#854d0e; background:#fde047; padding:1px 6px; border-radius:4px;" title="Điểm câu này trong tổng 1000 điểm">
                                                    ≈ {{ round(1000 / max(1, $selectedTest->questions->count())) }}đ
                                                </span>
                                            </div>
                                            <div style="display:flex; align-items:center; gap:6px; background:#ffffff; border:1.5px solid #ca8a04; border-radius:8px; padding:2px 10px;">
                                                <span style="color:#ca8a04; font-weight:900; font-size:14px;">★</span>
                                                <input type="number" step="0.5" name="points" id="form-inp-points" class="form-input" style="border:none; padding:7px 0; font-weight:900; font-size:14px; color:#854d0e; width:100%;" value="{{ old('points', $selectedQuestion?->points ?? 1) }}" required>
                                            </div>
                                        </div>
                                        <div class="field-unit" style="background:#e0f2fe; border:2.5px solid #0284c7; border-radius:12px; padding:10px 14px; display:flex; flex-direction:column; gap:6px;">
                                            <label class="field-label" style="display:flex; align-items:center; gap:5px; color:#0369a1; font-weight:900; font-size:11.5px;">
                                                <span>🔢</span> THỨ TỰ CÂU
                                            </label>
                                            <div style="display:flex; align-items:center; gap:6px; background:#ffffff; border:1.5px solid #0284c7; border-radius:8px; padding:2px 10px;">
                                                <span style="color:#0284c7; font-weight:900; font-size:14px;">#</span>
                                                <input type="number" name="position" id="form-inp-position" class="form-input" style="border:none; padding:7px 0; font-weight:900; font-size:14px; color:#0369a1; width:100%;" value="{{ old('position', $selectedQuestion?->position ?? 0) }}" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Block 2: Đáp án & Tương tác -->
                                <div class="section-box box-answers" style="background: #ffffff; border-radius: 16px; border: 3px solid #10b981; padding: 20px 24px; box-shadow: 0 8px 24px rgba(16, 185, 129, 0.15); margin-top: 20px; overflow: hidden;">
                                    <div class="section-header-line" style="background: linear-gradient(135deg, #065f46, #059669); color: #ffffff; padding: 14px 20px; margin: -20px -24px 18px; display: flex; align-items: center; justify-content: space-between; border-bottom: none;">
                                        <div style="display:flex; align-items:center; gap:10px;">
                                            <span style="width:34px; height:34px; border-radius:10px; background:rgba(255,255,255,0.2); color:#fff; display:grid; place-items:center; font-size:17px; box-shadow:0 2px 8px rgba(0,0,0,0.15);">
                                                🎯
                                            </span>
                                            <strong id="block-options-header-title" style="font-size:16px; font-weight:900; color:#ffffff; letter-spacing:0.3px;">
                                                KHỐI 2: CÁC ĐÁP ÁN LỰA CHỌN & THIẾT LẬP ĐÁP ÁN ĐÚNG
                                            </strong>
                                        </div>
                                        <div style="display:flex; align-items:center; gap:10px;">
                                            <button type="button" class="btn-tool-sm" id="btn-add-option-trigger" onclick="addOptionRow()" style="background:#ffffff; color:#065f46; font-weight:900; border:none; padding:7px 16px; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.15); cursor:pointer;">
                                                ＋ <span id="btn-add-option-text">Thêm đáp án lựa chọn mới</span>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="type-guide-banner" id="type-guide-box" style="background:#ecfdf5; border:1.5px dashed #10b981; border-radius:10px; padding:10px 16px; margin-bottom:16px; color:#065f46; font-size:13px;">
                                        <span id="type-guide-text">
                                            💡 <b>Hướng dẫn:</b> Nhập các phương án lựa chọn và tích chọn vào ô <b>[Đúng]</b> cho phương án chính xác.
                                        </span>
                                    </div>

                                    <!-- Dynamic Options Container -->
                                    <div class="options-container" id="options-box">
                                        <!-- Rendered via Javascript renderOptionsByType -->
                                    </div>

                                    <!-- Bottom Action Row: Add New Option -->
                                    <div class="options-bottom-action-bar" id="options-bottom-action-bar">
                                        <button type="button" class="btn-add-choice-bottom" id="btn-add-choice-bottom" onclick="addOptionRow()">
                                            <span class="btn-add-icon-circle">＋</span>
                                            <span id="btn-add-choice-bottom-text">Thêm phương án lựa chọn mới</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Block 3: Media File Đính kèm (Hình ảnh / Audio) -->
                                <div class="section-box box-media" style="background: #ffffff; border-radius: 16px; border: 3px solid #a855f7; padding: 20px 24px; box-shadow: 0 8px 24px rgba(168, 85, 247, 0.15); margin-top: 20px; overflow: hidden;">
                                    <div class="section-header-line" style="background: linear-gradient(135deg, #581c87, #7c3aed); color: #ffffff; padding: 14px 20px; margin: -20px -24px 18px; display: flex; align-items: center; justify-content: space-between; border-bottom: none;">
                                        <div style="display:flex; align-items:center; gap:10px;">
                                            <span style="width:34px; height:34px; border-radius:10px; background:rgba(255,255,255,0.2); color:#fff; display:grid; place-items:center; font-size:17px; box-shadow:0 2px 8px rgba(0,0,0,0.15);">
                                                🖼️
                                            </span>
                                            <strong style="font-size:16px; font-weight:900; color:#ffffff; letter-spacing:0.3px;">
                                                KHỐI 3: FILE MINH HỌA ĐỀ BÀI (HÌNH ẢNH / AUDIO / TÀI LIỆU)
                                            </strong>
                                        </div>
                                        <span style="font-size:11.5px; font-weight:800; background:rgba(255,255,255,0.2); color:#ffffff; padding:4px 10px; border-radius:99px;">
                                            Tùy chọn (Không bắt buộc)
                                        </span>
                                    </div>

                                    <!-- Media upload dropzone -->
                                    <div class="upload-dropzone" style="background:#faf5ff; border:2px dashed #a855f7; border-radius:12px; padding:16px 20px; display:flex; align-items:center; justify-content:space-between; gap:16px;">
                                        <div style="display:flex; align-items:center; gap:14px;">
                                            <span style="font-size:32px;">📁</span>
                                            <div>
                                                <strong style="font-size:13.5px; color:#581c87; display:block;">Đính kèm hình ảnh hoặc tệp âm thanh nghe cho đề bài câu hỏi này</strong>
                                                <span style="font-size:11.5px; color:#7e22ce;">Hỗ trợ: PNG, JPG, GIF, WEBP, MP3, WAV, PDF (Hiển thị trực tiếp cho học sinh xem dưới đề bài)</span>
                                            </div>
                                        </div>
                                        <div>
                                            <input type="file" name="assets[]" id="file-media-upload" multiple accept="image/*,audio/*,application/pdf" style="display:none;" onchange="previewMediaFiles(this)">
                                            <button type="button" class="btn-upload-trigger" onclick="document.getElementById('file-media-upload').click()" style="background:#7c3aed; color:#ffffff; font-weight:800; border:none; padding:9px 18px; border-radius:8px; cursor:pointer; box-shadow:0 2px 8px rgba(124,58,237,0.35);">
                                                ＋ Chọn tệp từ máy tính
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Uploaded Media Gallery -->
                                    <div class="media-assets-grid" id="media-assets-gallery">
                                        @if($selectedQuestion && $selectedQuestion->assets->count())
                                            @foreach($selectedQuestion->assets as $asset)
                                                @php
                                                    $rawPath = $asset->path ?: $asset->file_path;
                                                    $assetSrc = $rawPath ? (str_starts_with($rawPath, 'http') || str_starts_with($rawPath, '/') ? $rawPath : asset('storage/' . $rawPath)) : '';
                                                    $assetName = $asset->original_name ?: $asset->file_name ?: basename($rawPath ?: 'file');
                                                    $isImg = ($asset->kind === 'image') || preg_match('/\.(png|jpe?g|gif|webp|svg)$/i', $rawPath ?: $assetName);
                                                    $isAud = ($asset->kind === 'audio') || preg_match('/\.(mp3|wav|ogg|m4a|aac)$/i', $rawPath ?: $assetName);
                                                @endphp
                                                <div class="media-asset-card" id="asset-card-{{ $asset->id }}">
                                                    <div class="media-asset-thumb-wrap">
                                                        @if($isImg)
                                                            <img src="{{ $assetSrc }}" alt="{{ $assetName }}" onclick="zoomImage(this.src)" title="Bấm để xem phóng to">
                                                        @elseif($isAud)
                                                            <div style="padding:15px 10px; background:#312e81; color:#fff; text-align:center; width:100%;">
                                                                <span style="font-size:24px;">🎵</span>
                                                                <audio controls src="{{ $assetSrc }}" style="width:100%; height:30px; margin-top:5px;"></audio>
                                                            </div>
                                                        @else
                                                            <div style="padding:20px; background:#f1f5f9; text-align:center; font-size:24px;">
                                                                📄
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="media-asset-info">
                                                        <span class="media-file-name" title="{{ $assetName }}">{{ $assetName }}</span>
                                                        <div class="media-action-row">
                                                            @if($isImg)
                                                                <button type="button" class="btn-media-tool btn-media-view" onclick="zoomImage('{{ $assetSrc }}')">👁️ Xem</button>
                                                            @endif
                                                            <button type="button" class="btn-media-tool btn-media-del" onclick="askDeleteAsset({{ $asset->id }})">✕ Gỡ file</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>

                                <!-- Floating Sticky Bottom Bar -->
                                <div class="editor-bottom-bar">
                                    <div class="editor-bottom-bar-inner">
                                        <div>
                                            <button type="button" class="btn-delete-q" id="btn-delete-q-trigger" style="display: {{ $isCreating ? 'none' : 'block' }};" onclick="askDeleteCurrentQuestion()">
                                                🗑️ Xóa câu hỏi này
                                            </button>
                                        </div>
                                        <div style="display:flex; gap:10px; align-items:center;">
                                            <button type="button" id="btn-revert-changes" onclick="askRevertQuestionChanges()" style="display:none; padding:8px 16px; border-radius:8px; background:#334155; color:#f1f5f9; border:1.5px solid #64748b; font-weight:800; font-size:12.5px; cursor:pointer;" title="Khôi phục lại nội dung ban đầu khi chưa sửa">
                                                <span>↺</span> Hoàn tác về gốc
                                            </button>
                                            <button type="button" class="btn-nav-primary btn-nav-preview" onclick="openStudentSimulator()">
                                                <span>👁️</span> Xem thử học sinh
                                            </button>
                                            <button type="button" class="btn-nav-primary btn-nav-save" id="btn-bottom-save" onclick="triggerSaveQuestion()" style="padding:10px 24px; font-size:13.5px;">
                                                <span>💾</span> <span id="btn-save-text">{{ $isCreating ? 'TẠO CÂU HỎI MỚI (Ctrl + S)' : 'LƯU THAY ĐỔI CÂU #' . (($selectedQuestion->position ?? 0) + 1) . ' (Ctrl + S)' }}</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            @else
                <div style="padding:60px 20px; text-align:center;">
                    <h2 style="font-size:18px; font-weight:900; margin-bottom:8px">Chưa có bài luyện nào trong Khối {{ $selectedGrade }}</h2>
                    <p style="color:var(--muted-text); font-size:13px; margin-bottom:16px">Hãy tạo chủ đề hoặc bài test để bắt đầu.</p>
                    <button type="button" class="btn-nav-primary btn-nav-save" onclick="openModal('modal-add-topic')">＋ Tạo chủ đề mới</button>
                </div>
            @endif

        </main>

    </div>

    <!-- 3. STUDENT PREVIEW MODAL (100% GIAO DIỆN HỌC SINH THẬT - TÁI SỬ DỤNG VIEW USER) -->
    <div id="modal-student-sim" class="preview-modal-overlay">
        <div class="preview-simulator-box" style="width: min(1200px, 98vw); height: 95vh; max-height: 95vh; display: flex; flex-direction: column; padding: 0; border-radius: 20px; overflow: hidden; background: #061021; border: 3px solid #00f2fe; box-shadow: 0 0 45px rgba(0, 242, 254, 0.45); position: relative;">
            <!-- Top Control Strip for Admin Preview -->
            <div style="background: linear-gradient(90deg, #091a33, #0f274a); padding: 9px 20px; border-bottom: 2px solid #00f2fe; display: flex; align-items: center; justify-content: space-between; z-index: 10; flex-shrink: 0; flex-wrap: wrap; gap: 10px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-family: 'Fredoka', cursive, sans-serif; font-size: 16px; font-weight: 700; color: #ffe658; display: flex; align-items: center; gap: 6px; letter-spacing: 0.5px;">
                        <span>⭐</span> IC3 QUEST ARENA
                    </span>
                    <span style="color: #94a3b8; font-size: 13px; font-weight: 800;">
                        · Khối {{ $selectedGrade }} · {{ $selectedTopic?->name }}
                    </span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <button type="button" onclick="triggerIframeAutofill()" style="padding: 6px 14px; border-radius: 8px; background: rgba(255,255,255,0.12); border: 1.5px solid rgba(255,255,255,0.3); color: #ffeaa7; font-size: 12.5px; font-weight: 900; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: 0.15s;" title="Tự động điền đáp án đúng đang cài đặt trong hệ thống">
                        <span>💡</span> Điền đáp án đúng
                    </button>
                    <button type="button" onclick="triggerIframeCheck()" style="padding: 6px 16px; border-radius: 8px; background: linear-gradient(135deg, #00f2fe, #0284c7); border: 1.5px solid #38bdf8; color: #061021; font-size: 12.5px; font-weight: 1000; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 0 10px rgba(0,242,254,0.35); transition: 0.15s;" title="Kiểm tra đáp án đang chọn">
                        <span>🏁</span> KIỂM TRA ĐÁP ÁN
                    </button>
                    <button type="button" onclick="triggerIframeReset()" style="padding: 6px 12px; border-radius: 8px; background: rgba(255,255,255,0.08); border: 1.5px solid rgba(255,255,255,0.22); color: #fff; font-size: 12px; font-weight: 800; cursor: pointer;" title="Làm lại câu này">
                        <span>↺</span> Thử lại
                    </button>
                    <button type="button" onclick="openStudentViewInNewTab()" style="padding: 6px 14px; border-radius: 8px; background: rgba(0,242,254,0.15); border: 1.5px solid #00f2fe; color: #00f2fe; font-size: 12px; font-weight: 900; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: 0.15s;" title="Mở toàn màn hình trong tab mới">
                        <span>🚀</span> Mở Tab Mới
                    </button>
                    <button type="button" onclick="closeModal('modal-student-sim')" style="width: 32px; height: 32px; border-radius: 8px; background: rgba(239, 68, 68, 0.2); border: 1.5px solid #ef4444; color: #ef4444; font-size: 16px; font-weight: 900; cursor: pointer; display: grid; place-items: center; transition: 0.15s;" title="Đóng xem thử">
                        ✕
                    </button>
                </div>
            </div>

            <!-- The Real Live User View Iframe -->
            <iframe id="sim-user-view-iframe" src="" style="flex: 1; width: 100%; height: 100%; border: none; background: #061021;"></iframe>
        </div>
    </div>

    <!-- 4. LIGHTBOX IMAGE ZOOM MODAL -->
    <div id="modal-img-zoom" onclick="closeImageZoom(event)">
        <div class="zoom-content-box" onclick="event.stopPropagation()">
            <div style="width:100%; display:flex; justify-content:space-between; align-items:center;">
                <b style="font-size:14px;">🖼️ Xem ảnh kích thước đầy đủ</b>
                <button type="button" style="background:none; border:none; font-size:20px; cursor:pointer;" onclick="document.getElementById('modal-img-zoom').classList.remove('active')">✕</button>
            </div>
            <img src="" id="zoom-img-target" class="zoom-preview-img" alt="Zoom Preview">
            <button type="button" class="sidebar-link-btn" style="color:#0f172a;" onclick="document.getElementById('modal-img-zoom').classList.remove('active')">Đóng</button>
        </div>
    </div>

    <!-- 5. CONFIRMATION MODAL -->
    <div id="modal-confirm" class="confirm-modal-overlay">
        <div class="confirm-modal-card">
            <div class="confirm-icon-badge" id="confirm-icon">⚠️</div>
            <h4 class="confirm-modal-title" id="confirm-title">Xác nhận thao tác</h4>
            <p class="confirm-modal-desc" id="confirm-desc">Bạn có chắc chắn muốn thực hiện hành động này?</p>
            <div class="confirm-modal-btns">
                <button type="button" class="btn-confirm-cancel" onclick="closeModal('modal-confirm')">Hủy bỏ</button>
                <button type="button" class="btn-confirm-ok" id="btn-confirm-accept">Xác nhận</button>
            </div>
        </div>
    </div>

    <!-- 6. SLEEK CUSTOM INPUT PROMPT MODAL -->
    <div id="modal-prompt-input" class="confirm-modal-overlay">
        <div class="confirm-modal-card" style="max-width: 450px; text-align: left; background: #ffffff; border-radius: 16px; box-shadow: 0 20px 45px rgba(0, 0, 0, 0.25); border: 2px solid #86efac; padding: 22px;">
            <div style="display:flex; align-items:center; gap:12px; margin-bottom:12px;">
                <div id="prompt-modal-icon" style="width:42px; height:42px; border-radius:12px; background:linear-gradient(135deg, #10b981, #059669); color:#fff; display:grid; place-items:center; font-size:22px; box-shadow:0 4px 12px rgba(16,185,129,0.35); flex-shrink:0;">
                    🏷️
                </div>
                <div>
                    <h4 id="prompt-modal-title" style="margin:0; font-size:16px; font-weight:900; color:#0f172a; letter-spacing:0.2px;">Thêm lựa chọn mới</h4>
                    <p id="prompt-modal-desc" style="margin:3px 0 0; font-size:12.5px; color:#64748b; font-weight:600;">Nhập tên lựa chọn mới muốn thêm vào danh sách Dropdown</p>
                </div>
            </div>

            <div style="margin:16px 0;">
                <input type="text" id="prompt-modal-input" class="form-input" style="font-size:14.5px; padding:11px 15px; border:2px solid #86efac; background:#f0fdf4; color:#15803d; font-weight:800; border-radius:10px; box-shadow:inset 0 1px 3px rgba(0,0,0,0.03);" placeholder="Ví dụ: Thiết bị mạng, Bộ nhớ ngoài...">
            </div>

            <div class="confirm-modal-btns" style="display:flex; justify-content:flex-end; gap:10px; margin-top:14px;">
                <button type="button" class="btn-confirm-cancel" onclick="closeModal('modal-prompt-input')" style="padding:9px 18px; border-radius:10px; font-weight:800; font-size:13px; cursor:pointer;">Hủy bỏ</button>
                <button type="button" class="btn-confirm-ok" id="btn-prompt-modal-submit" style="padding:9px 20px; border-radius:10px; font-weight:900; font-size:13px; background:linear-gradient(180deg, #16a34a, #15803d); border:1.5px solid #166534; box-shadow:0 3px 8px rgba(22,163,74,0.35); cursor:pointer;">Xác nhận thêm ➔</button>
            </div>
        </div>
    </div>

    <!-- Modal Add Level (Khối lớp mới) -->
    <!-- Modal Add Level -->
    <div id="modal-add-level" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-head">
                <h3><span>🔑</span> Thêm Khối lớp / Cấp độ mới</h3>
                <button type="button" style="background:none;border:none;font-size:18px;cursor:pointer" onclick="closeModal('modal-add-level')">✕</button>
            </div>
            <form method="post" action="{{ route('admin.levels.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="field-unit">
                        <label class="field-label">🗂️ Thuộc Chương trình đào tạo *</label>
                        <select name="program_id" class="form-select" required>
                            @foreach($programs as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                        <small style="font-size:11px; color:#64748b; margin-top:3px; display:block;">Chương trình cấp cha quản lý khối học này</small>
                    </div>
                    <div class="field-unit">
                        <label class="field-label">🏷️ Tên khối lớp / Cấp độ *</label>
                        <input type="text" name="name" class="form-input" placeholder="Ví dụ: IC3 GS6 Spark Level 1 — Khối 1" required>
                        <small style="font-size:11px; color:#64748b; margin-top:3px; display:block;">Tên hiển thị công khai trên lộ trình luyện thi</small>
                    </div>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                        <div class="field-unit">
                            <label class="field-label">🎯 Khối lớp (Grade) *</label>
                            <input type="number" name="grade" min="1" max="12" class="form-input" value="{{ ($levels->max('grade') ?? 5) + 1 }}" required placeholder="1 - 12">
                        </div>
                        <div class="field-unit">
                            <label class="field-label">🔢 Thứ tự hiển thị</label>
                            <input type="number" name="position" min="0" class="form-input" value="{{ $levels->count() + 1 }}">
                        </div>
                    </div>
                </div>
                <div class="modal-foot">
                    <button type="button" class="sidebar-link-btn" style="color:#0f172a" onclick="closeModal('modal-add-level')">Hủy</button>
                    <button type="submit" class="btn-nav-primary btn-nav-save">✓ Lưu & Tạo Khối Lớp</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Add Topic -->
    <div id="modal-add-topic" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-head">
                <h3><span>📁</span> Thêm Chủ đề mới (Khối {{ $selectedGrade }})</h3>
                <button type="button" style="background:none;border:none;font-size:18px;cursor:pointer" onclick="closeModal('modal-add-topic')">✕</button>
            </div>
            <form method="post" action="{{ route('admin.topics.store') }}">
                @csrf
                <input type="hidden" name="level_id" value="{{ $selectedLevel?->id }}">
                <div class="modal-body">
                    <div class="field-unit">
                        <label class="field-label">📁 Tên chủ đề bài học *</label>
                        <input type="text" name="name" class="form-input" placeholder="Ví dụ: Căn bản về máy tính, An toàn trực tuyến, Soạn thảo văn bản..." required>
                        <small style="font-size:11px; color:#64748b; margin-top:3px; display:block;">Tên chủ đề / bài học lớn</small>
                    </div>
                    <div class="field-unit">
                        <label class="field-label">📝 Mô tả nội dung bài học (Tùy chọn)</label>
                        <input type="text" name="description" class="form-input" placeholder="Ví dụ: Làm quen các bộ phận máy tính, bàn phím và chuột...">
                        <small style="font-size:11px; color:#64748b; margin-top:3px; display:block;">Hiển thị tóm tắt cho học sinh trên bản đồ học tập</small>
                    </div>
                </div>
                <div class="modal-foot">
                    <button type="button" class="sidebar-link-btn" style="color:#0f172a" onclick="closeModal('modal-add-topic')">Hủy</button>
                    <button type="submit" class="btn-nav-primary btn-nav-save">✓ Thêm chủ đề</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Add Test -->
    <div id="modal-add-test" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-head">
                <h3 id="modal-test-title"><span>📝</span> Thêm Bài luyện / Đề thi mới</h3>
                <button type="button" style="background:none;border:none;font-size:18px;cursor:pointer" onclick="closeModal('modal-add-test')">✕</button>
            </div>
            <form method="post" action="{{ route('admin.tests.store') }}">
                @csrf
                <input type="hidden" name="topic_id" id="modal-test-topic-id" value="{{ $selectedTopic?->id }}">
                <div class="modal-body">
                    <div class="field-unit">
                        <label class="field-label">📝 Tên bài luyện / Đề thi *</label>
                        <input type="text" name="name" class="form-input" placeholder="Ví dụ: Bài luyện 1, Đề thi thử cuối kỳ..." required>
                        <small style="font-size:11px; color:#64748b; margin-top:3px; display:block;">Tên đề luyện thi để học sinh chọn làm bài</small>
                    </div>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                        <div class="field-unit">
                            <label class="field-label">🎯 Mức độ</label>
                            <select name="difficulty" class="form-select">
                                <option value="Cơ bản">🟢 Cơ bản</option>
                                <option value="Trung bình">🟡 Trung bình</option>
                                <option value="Nâng cao">🔴 Nâng cao</option>
                            </select>
                        </div>
                        <div class="field-unit">
                            <label class="field-label">⏱️ Thời gian (Phút)</label>
                            <input type="number" name="duration_minutes" class="form-input" value="20">
                        </div>
                    </div>
                    <div style="margin-top:12px; padding:10px 12px; background:#f8fafc; border:1px solid #cbd5e1; border-radius:8px; display:flex; flex-direction:column; gap:8px;">
                        <label style="display:flex; align-items:center; gap:8px; font-size:12px; font-weight:700; color:#0f172a; cursor:pointer;">
                            <input type="checkbox" name="shuffle_questions" value="1" style="width:16px; height:16px;">
                            <span>🔀 Xáo trộn thứ tự câu hỏi khi học sinh làm bài</span>
                        </label>
                        <label style="display:flex; align-items:center; gap:8px; font-size:12px; font-weight:700; color:#0f172a; cursor:pointer;">
                            <input type="checkbox" name="shuffle_options" value="1" style="width:16px; height:16px;">
                            <span>🔀 Xáo trộn thứ tự đáp án (A, B, C, D)</span>
                        </label>
                    </div>
                </div>
                <div class="modal-foot">
                    <button type="button" class="sidebar-link-btn" style="color:#0f172a" onclick="closeModal('modal-add-test')">Hủy</button>
                    <button type="submit" class="btn-nav-primary btn-nav-save">✓ Thêm bài luyện</button>
                </div>
            </form>
        </div>
    </div>

    @if($selectedTest)
    <!-- Modal Edit Test Settings -->
    <div id="modal-edit-test" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-head">
                <h3><span>⚙️</span> Cài đặt bài luyện: {{ $selectedTest->name }}</h3>
                <button type="button" style="background:none;border:none;font-size:18px;cursor:pointer" onclick="closeModal('modal-edit-test')">✕</button>
            </div>
            <form method="post" action="{{ route('admin.tests.update', $selectedTest) }}">
                @csrf
                @method('put')
                <input type="hidden" name="topic_id" value="{{ $selectedTest->topic_id }}">
                <div class="modal-body">
                    <div class="field-unit">
                        <label class="field-label">📝 Tên bài luyện / Đề thi *</label>
                        <input type="text" name="name" class="form-input" value="{{ $selectedTest->name }}" required>
                    </div>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                        <div class="field-unit">
                            <label class="field-label">🎯 Mức độ</label>
                            <select name="difficulty" class="form-select">
                                <option value="Cơ bản" {{ $selectedTest->difficulty === 'Cơ bản' ? 'selected' : '' }}>🟢 Cơ bản</option>
                                <option value="Trung bình" {{ $selectedTest->difficulty === 'Trung bình' ? 'selected' : '' }}>🟡 Trung bình</option>
                                <option value="Nâng cao" {{ $selectedTest->difficulty === 'Nâng cao' ? 'selected' : '' }}>🔴 Nâng cao</option>
                            </select>
                        </div>
                        <div class="field-unit">
                            <label class="field-label">⏱️ Thời gian (Phút)</label>
                            <input type="number" name="duration_minutes" class="form-input" value="{{ $selectedTest->duration_minutes ?? 20 }}">
                        </div>
                    </div>
                    
                    <div style="margin-top:14px; padding:12px 14px; background:#f8fafc; border:1px solid #cbd5e1; border-radius:10px; display:flex; flex-direction:column; gap:10px;">
                        <div style="font-size:12px; font-weight:800; color:#334155; text-transform:uppercase; letter-spacing:0.5px;">🔀 Cấu hình Xáo trộn ngẫu nhiên (Randomize)</div>
                        <label style="display:flex; align-items:center; gap:8px; font-size:13px; font-weight:700; color:#0f172a; cursor:pointer;">
                            <input type="checkbox" name="shuffle_questions" value="1" {{ $selectedTest->shuffle_questions ? 'checked' : '' }} style="width:17px; height:17px; cursor:pointer;">
                            <span>🔀 Xáo trộn thứ tự các câu hỏi khi học sinh làm bài</span>
                        </label>
                        <label style="display:flex; align-items:center; gap:8px; font-size:13px; font-weight:700; color:#0f172a; cursor:pointer;">
                            <input type="checkbox" name="shuffle_options" value="1" {{ $selectedTest->shuffle_options ? 'checked' : '' }} style="width:17px; height:17px; cursor:pointer;">
                            <span>🔀 Xáo trộn thứ tự đáp án (A, B, C, D)</span>
                        </label>
                        <label style="display:flex; align-items:center; gap:8px; font-size:13px; font-weight:700; color:#0f172a; cursor:pointer; margin-top:4px; padding-top:8px; border-top:1px dashed #cbd5e1;">
                            <input type="checkbox" name="is_published" value="1" {{ $selectedTest->is_published ? 'checked' : '' }} style="width:17px; height:17px; cursor:pointer;">
                            <span>🌐 Đang phát hành cho học sinh làm bài</span>
                        </label>
                    </div>
                </div>
                <div class="modal-foot">
                    <button type="button" class="sidebar-link-btn" style="color:#0f172a" onclick="closeModal('modal-edit-test')">Hủy</button>
                    <button type="submit" class="btn-nav-primary btn-nav-save">✓ Lưu cài đặt đề thi</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Initial Data for Current Loaded Question -->
    @php
        $initialOptions = old('options', $selectedQuestion?->parsed_options ?? [
            ['content' => '', 'is_correct' => true, 'image_path' => ''],
            ['content' => '', 'is_correct' => false, 'image_path' => ''],
            ['content' => '', 'is_correct' => false, 'image_path' => ''],
            ['content' => '', 'is_correct' => false, 'image_path' => '']
        ]);
    @endphp
    <script>
        // Giữ các lựa chọn đang soạn ở trình duyệt; thao tác lưu mới gửi chúng về server.
        let currentOptionsState = @json($initialOptions);
    </script>

    <script>
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const lettersList = ['A', 'B', 'C', 'D', 'E', 'F'];
        const STORE_URL = "{{ route('admin.questions.store') }}";

        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast-msg');
            const icon = document.getElementById('toast-icon');
            const text = document.getElementById('toast-text');
            if (!toast) return;

            toast.className = `toast-notification toast-${type} show`;
            icon.textContent = (type === 'success') ? '✓' : '⚠️';
            text.textContent = message;

            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        /* 📱 SIDEBAR COLLAPSE / EXPAND TOGGLE */
        function toggleStudioSidebar() {
            const container = document.getElementById('app-container');
            const toggleIcon = document.getElementById('sidebar-toggle-icon');
            if (!container) return;

            const isCollapsed = container.classList.toggle('sidebar-collapsed');
            localStorage.setItem('studio_sidebar_collapsed', isCollapsed ? '1' : '0');

            if (toggleIcon) {
                toggleIcon.textContent = isCollapsed ? '▶' : '◀';
            }
            showToast(isCollapsed ? 'Đã thu gọn Sidebar để mở rộng không gian!' : 'Đã mở rộng Sidebar', 'info');
        }

        // 📐 VS Code Style Draggable Sidebar Resizer
        (function initSidebarResizer() {
            const resizer = document.getElementById('sidebar-resizer');
            const container = document.getElementById('app-container');
            if (!resizer || !container) return;

            // Khôi phục độ rộng Sidebar đã lưu từ localStorage
            const savedWidth = localStorage.getItem('mos_studio_sidebar_width');
            if (savedWidth) {
                const w = parseInt(savedWidth, 10);
                if (!isNaN(w) && w >= 220 && w <= 750) {
                    document.documentElement.style.setProperty('--sidebar-width', w + 'px');
                }
            }

            let isDragging = false;
            let startX = 0;
            let startW = 320;

            resizer.addEventListener('mousedown', function(e) {
                if (container.classList.contains('sidebar-collapsed')) return;
                isDragging = true;
                startX = e.clientX;
                const currentStyle = getComputedStyle(document.documentElement).getPropertyValue('--sidebar-width');
                startW = parseInt(currentStyle, 10) || 320;

                container.classList.add('is-resizing');
                document.body.classList.add('is-resizing');
                e.preventDefault();
            });

            window.addEventListener('mousemove', function(e) {
                if (!isDragging) return;
                const deltaX = e.clientX - startX;
                let newWidth = startW + deltaX;

                // Giới hạn độ rộng: tối thiểu 220px, tối đa 55% màn hình (hoặc 750px)
                const minW = 220;
                const maxW = Math.min(750, Math.floor(window.innerWidth * 0.55));
                if (newWidth < minW) newWidth = minW;
                if (newWidth > maxW) newWidth = maxW;

                document.documentElement.style.setProperty('--sidebar-width', newWidth + 'px');
            });

            window.addEventListener('mouseup', function() {
                if (!isDragging) return;
                isDragging = false;
                container.classList.remove('is-resizing');
                document.body.classList.remove('is-resizing');

                const currentStyle = getComputedStyle(document.documentElement).getPropertyValue('--sidebar-width');
                const finalW = parseInt(currentStyle, 10);
                if (finalW) {
                    localStorage.setItem('mos_studio_sidebar_width', finalW);
                }
            });

            // Nhấp đúp vào thanh resizer để đặt lại độ rộng chuẩn 320px
            resizer.addEventListener('dblclick', function() {
                document.documentElement.style.setProperty('--sidebar-width', '320px');
                localStorage.setItem('mos_studio_sidebar_width', '320');
                showToast('Đã đặt lại độ rộng Sidebar về mặc định (320px)', 'info');
            });
        })();

        // Khôi phục trạng thái thu gọn Sidebar khi tải trang
        document.addEventListener('DOMContentLoaded', () => {
            if (localStorage.getItem('studio_sidebar_collapsed') === '1') {
                const container = document.getElementById('app-container');
                const toggleIcon = document.getElementById('sidebar-toggle-icon');
                container?.classList.add('sidebar-collapsed');
                if (toggleIcon) toggleIcon.textContent = '▶';
            }
        });

        // Phím tắt Ctrl + B để ẩn/hiện Sidebar nhanh
        window.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'b') {
                e.preventDefault();
                toggleStudioSidebar();
            }
        });

        function openModal(id) { document.getElementById(id)?.classList.add('active'); }
        function closeModal(id) { document.getElementById(id)?.classList.remove('active'); }
        window.addEventListener('click', e => { 
            if (e.target.classList.contains('modal-overlay') || e.target.classList.contains('preview-modal-overlay') || e.target.classList.contains('confirm-modal-overlay')) {
                e.target.classList.remove('active'); 
            }
        });

        function showConfirmModal({ title, desc, icon = '⚠️', confirmBtnText = 'Xác nhận', onConfirm }) {
            document.getElementById('confirm-title').textContent = title;
            document.getElementById('confirm-desc').textContent = desc;
            document.getElementById('confirm-icon').textContent = icon;
            const okBtn = document.getElementById('btn-confirm-accept');
            okBtn.textContent = confirmBtnText;
            okBtn.onclick = () => {
                closeModal('modal-confirm');
                if (typeof onConfirm === 'function') onConfirm();
            };
            openModal('modal-confirm');
        }

        function showCustomPromptModal({ title, desc, icon = '🏷️', placeholder = '', defaultValue = '', onConfirm }) {
            const modalTitle = document.getElementById('prompt-modal-title');
            const modalDesc = document.getElementById('prompt-modal-desc');
            const modalIcon = document.getElementById('prompt-modal-icon');
            const modalInput = document.getElementById('prompt-modal-input');
            const okBtn = document.getElementById('btn-prompt-modal-submit');

            if (modalTitle) modalTitle.textContent = title;
            if (modalDesc) modalDesc.textContent = desc;
            if (modalIcon) modalIcon.textContent = icon;
            if (modalInput) {
                modalInput.value = defaultValue;
                modalInput.placeholder = placeholder;
            }

            const handleSubmit = () => {
                const val = modalInput.value.trim();
                if (!val) {
                    modalInput.focus();
                    return;
                }
                closeModal('modal-prompt-input');
                if (typeof onConfirm === 'function') onConfirm(val);
            };

            okBtn.onclick = handleSubmit;
            modalInput.onkeydown = (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    handleSubmit();
                } else if (e.key === 'Escape') {
                    closeModal('modal-prompt-input');
                }
            };

            openModal('modal-prompt-input');
            setTimeout(() => modalInput?.focus(), 100);
        }

        function zoomImage(src) {
            const modal = document.getElementById('modal-img-zoom');
            const img = document.getElementById('zoom-img-target');
            if (modal && img) {
                img.src = src;
                modal.classList.add('active');
            }
        }
        function closeImageZoom(e) {
            document.getElementById('modal-img-zoom')?.classList.remove('active');
        }

        /* 🖼️ OPTION IMAGE MANAGEMENT */
        function triggerOptionFileUpload(btn) {
            const card = btn.closest('.option-row-card');
            const fileInput = card?.querySelector('.opt-hidden-file-input');
            fileInput?.click();
        }

        function onOptionFileSelected(fileInput) {
            if (!fileInput.files || !fileInput.files[0]) return;
            const file = fileInput.files[0];
            const previewUrl = URL.createObjectURL(file);
            const card = fileInput.closest('.option-row-card');
            const idx = card.getAttribute('data-idx');
            const box = card.querySelector('.option-image-control-box');

            box.innerHTML = `
                <div class="opt-img-has" style="display:flex; align-items:center; gap:5px;">
                    <img src="${previewUrl}" class="opt-thumb-preview" onclick="zoomImage('${previewUrl}')" title="Bấm để xem phóng to" alt="Thumb">
                    <input type="hidden" name="options[${idx}][image_path]" class="opt-inp-img-path" value="">
                    <button type="button" class="btn-opt-action-img btn-opt-change" onclick="triggerOptionFileUpload(this)" title="Đổi ảnh khác">
                        🔄 Đổi
                    </button>
                    <button type="button" class="btn-opt-action-img btn-opt-clear" onclick="askClearOptionImage(this)" title="Gỡ ảnh này">
                        ✕ Gỡ
                    </button>
                </div>
                <input type="file" name="options[${idx}][image_file]" class="opt-hidden-file-input" accept="image/*" style="display:none;" onchange="onOptionFileSelected(this)">
            `;
            const newFileInput = box.querySelector('.opt-hidden-file-input');
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            newFileInput.files = dataTransfer.files;
            showToast('Đã gắn ảnh vào lựa chọn (Chưa lưu CSDL)', 'success');
        }

        function askClearOptionImage(btn) {
            showConfirmModal({
                title: 'Xác nhận gỡ hình ảnh',
                desc: 'Bạn có chắc chắn muốn gỡ bỏ hình ảnh của đáp án lựa chọn này không?',
                confirmBtnText: 'Gỡ ảnh ngay',
                onConfirm: () => {
                    const card = btn.closest('.option-row-card');
                    const idx = card.getAttribute('data-idx');
                    const box = card.querySelector('.option-image-control-box');

                    box.innerHTML = `
                        <div class="opt-img-none" style="display:flex; align-items:center; gap:5px;">
                            <button type="button" class="btn-opt-action-img btn-opt-add-new" onclick="triggerOptionFileUpload(this)">
                                <span>🖼️</span> Thêm ảnh
                            </button>
                            <input type="hidden" name="options[${idx}][image_path]" class="opt-inp-img-path" value="">
                        </div>
                        <input type="file" name="options[${idx}][image_file]" class="opt-hidden-file-input" accept="image/*" style="display:none;" onchange="onOptionFileSelected(this)">
                    `;
                    showToast('Đã gỡ ảnh khỏi đáp án', 'success');
                }
            });
        }

        function askDeleteOptionRow(btn) {
            const container = document.getElementById('options-box');
            if (container.children.length <= 2) {
                alert('Câu hỏi cần có tối thiểu 2 đáp án hoặc cặp ghép nối!');
                return;
            }
            showConfirmModal({
                title: 'Xác nhận xóa dòng này',
                desc: 'Bạn có chắc muốn xóa vĩnh viễn dòng cấu hình này?',
                confirmBtnText: 'Xóa ngay',
                onConfirm: () => {
                    btn.closest('.option-row-card, .matching-pair-card, .sequence-step-card')?.remove();
                    refreshOptionLabels();
                    showToast('Đã xóa dòng thành công', 'success');
                }
            });
        }

        function askDeleteAsset(assetId) {
            showConfirmModal({
                title: 'Xác nhận xóa tệp đính kèm',
                desc: 'File này sẽ bị xóa hoàn toàn khỏi hệ thống lưu trữ. Thao tác không thể hoàn tác!',
                confirmBtnText: 'Xóa file',
                onConfirm: () => {
                    fetch(`/quan-tri/assets/${assetId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': CSRF_TOKEN,
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        document.getElementById(`asset-card-${assetId}`)?.remove();
                        showToast('Đã xóa file đính kèm thành công!', 'success');
                    })
                    .catch(err => alert('Lỗi khi xóa file: ' + err.message));
                }
            });
        }

        function askDeleteCurrentQuestion() {
            const currentQId = document.getElementById('current-q-id')?.value;
            if (!currentQId) return;

            showConfirmModal({
                title: 'Xác nhận XÓA CÂU HỎI',
                desc: 'Bạn có chắc chắn muốn xóa vĩnh viễn câu hỏi này khỏi bộ đề thi?',
                confirmBtnText: 'Xóa vĩnh viễn',
                onConfirm: () => {
                    fetch(`/quan-tri/questions/${currentQId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': CSRF_TOKEN,
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        showToast('Đã xóa câu hỏi thành công!', 'success');
                        location.reload();
                    })
                    .catch(err => location.reload());
                }
            });
        }

        function escapeHtml(str) {
            if (str === null || str === undefined) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
        }

        /* ⚡ DIRTY FORM & CHANGE TRACKING ENGINE */
        /* ⚡ DIRTY FORM & CHANGE TRACKING ENGINE */
        let initialFieldsBaseline = {
            title: '',
            type: '',
            points: 1,
            position: 0,
            published: true,
            optionsSnapshot: ''
        };
        let currentQuestionLoadedData = null;
        let isFormDirty = false;

        function serializeOptionsFromDOM() {
            const container = document.getElementById('options-box');
            if (!container) return '';
            const items = [];
            const inputs = container.querySelectorAll('input, select, textarea');
            inputs.forEach(inp => {
                if (inp.type === 'file') return;
                if (inp.type === 'checkbox' || inp.type === 'radio') {
                    items.push(`${inp.name}=${inp.checked ? '1' : '0'}`);
                } else {
                    items.push(`${inp.name}=${inp.value.trim()}`);
                }
            });
            return items.join('&');
        }

        function captureInitialSnapshot(questionData = null) {
            if (questionData) {
                currentQuestionLoadedData = JSON.parse(JSON.stringify(questionData));
            }
            const titleEl = document.getElementById('form-inp-title');
            const typeEl = document.getElementById('sel-type');
            const pointsEl = document.getElementById('form-inp-points');
            const posEl = document.getElementById('form-inp-position');
            const pubEl = document.getElementById('form-inp-published');

            initialFieldsBaseline = {
                title: (titleEl ? titleEl.value : '').trim(),
                type: typeEl ? typeEl.value : 'MultipleChoice',
                points: pointsEl ? parseFloat(pointsEl.value) : 1,
                position: posEl ? parseInt(posEl.value) : 0,
                published: pubEl ? pubEl.checked : true,
                optionsSnapshot: serializeOptionsFromDOM()
            };

            setDirtyState(0, []);
        }

        function setDirtyState(diffCount, diffNames = []) {
            isFormDirty = (diffCount > 0);
            const badge = document.getElementById('q-change-status-badge');
            const revertBtn = document.getElementById('btn-revert-changes');
            const btnTop = document.getElementById('btn-top-save');
            const btnBottom = document.getElementById('btn-bottom-save');
            const currentQId = document.getElementById('current-q-id')?.value;
            const posInput = document.getElementById('form-inp-position')?.value;
            const qNum = posInput !== '' ? (parseInt(posInput) + 1) : 'Mới';

            if (diffCount > 0) {
                if (badge) {
                    badge.style.display = 'inline-block';
                    badge.innerHTML = `⚠️ CÓ ${diffCount} THAY ĐỔI CHƯA LƯU (${diffNames.join(', ')})`;
                }
                if (revertBtn) {
                    revertBtn.style.display = 'inline-flex';
                    revertBtn.innerHTML = `<span>↺</span> Hoàn tác (${diffCount})`;
                }
                if (btnTop) {
                    btnTop.classList.add('is-dirty');
                    const txtEl = document.getElementById('btn-top-save-text');
                    if (txtEl) {
                        txtEl.innerHTML = `Lưu Câu #${qNum} <span class="dirty-badge-count" title="${diffNames.join(', ')}">${diffCount}</span>`;
                    }
                }
                if (btnBottom) {
                    btnBottom.classList.add('is-dirty');
                    const txtEl = document.getElementById('btn-save-text');
                    if (txtEl) {
                        txtEl.innerHTML = `LƯU CÂU #${qNum} <span class="dirty-badge-count">${diffCount} THAY ĐỔI CHƯA LƯU</span>`;
                    }
                }
            } else {
                if (badge) badge.style.display = 'none';
                if (revertBtn) revertBtn.style.display = 'none';
                if (btnTop) {
                    btnTop.classList.remove('is-dirty');
                    const txtEl = document.getElementById('btn-top-save-text');
                    if (txtEl) txtEl.textContent = currentQId ? `Lưu Câu #${qNum} (Ctrl+S)` : `Tạo Câu Mới (Ctrl+S)`;
                }
                if (btnBottom) {
                    btnBottom.classList.remove('is-dirty');
                    const txtEl = document.getElementById('btn-save-text');
                    if (txtEl) txtEl.textContent = currentQId ? `LƯU THAY ĐỔI CÂU #${qNum} (Ctrl + S)` : `TẠO CÂU HỎI MỚI (Ctrl + S)`;
                }
                // Clear all field dirty highlights
                document.querySelectorAll('.is-dirty-field').forEach(el => el.classList.remove('is-dirty-field'));
            }
        }

        function checkFormDirty() {
            let diffCount = 0;
            const diffNames = [];

            // 1. Check Title
            const titleEl = document.getElementById('form-inp-title');
            const currentTitle = (titleEl ? titleEl.value : '').trim();
            if (currentTitle !== initialFieldsBaseline.title) {
                titleEl?.classList.add('is-dirty-field');
                diffCount++;
                diffNames.push('Đề bài');
            } else {
                titleEl?.classList.remove('is-dirty-field');
            }

            // 2. Check Type
            const typeEl = document.getElementById('sel-type');
            const currentType = typeEl ? typeEl.value : 'MultipleChoice';
            if (currentType !== initialFieldsBaseline.type) {
                typeEl?.classList.add('is-dirty-field');
                typeEl?.parentElement?.classList.add('is-dirty-field');
                diffCount++;
                diffNames.push('Dạng câu hỏi');
            } else {
                typeEl?.classList.remove('is-dirty-field');
                typeEl?.parentElement?.classList.remove('is-dirty-field');
            }

            // 3. Check Points
            const pointsEl = document.getElementById('form-inp-points');
            const currentPoints = pointsEl ? parseFloat(pointsEl.value) : 1;
            if (currentPoints !== initialFieldsBaseline.points) {
                pointsEl?.parentElement?.classList.add('is-dirty-field');
                diffCount++;
                diffNames.push('Điểm');
            } else {
                pointsEl?.parentElement?.classList.remove('is-dirty-field');
            }

            // 4. Check Position
            const posEl = document.getElementById('form-inp-position');
            const currentPos = posEl ? parseInt(posEl.value) : 0;
            if (currentPos !== initialFieldsBaseline.position) {
                posEl?.parentElement?.classList.add('is-dirty-field');
                diffCount++;
                diffNames.push('Thứ tự');
            } else {
                posEl?.parentElement?.classList.remove('is-dirty-field');
            }

            // 5. Check Published
            const pubEl = document.getElementById('form-inp-published');
            const currentPub = pubEl ? pubEl.checked : true;
            if (currentPub !== initialFieldsBaseline.published) {
                diffCount++;
                diffNames.push('Trạng thái');
            }

            // 6. Check Options (DOM-to-DOM exact baseline comparison)
            const currentOptsStr = serializeOptionsFromDOM();
            const optionsBox = document.getElementById('options-box');
            if (currentOptsStr !== initialFieldsBaseline.optionsSnapshot) {
                optionsBox?.classList.add('is-dirty-field');
                diffCount++;
                diffNames.push('Đáp án/Lựa chọn');
            } else {
                optionsBox?.classList.remove('is-dirty-field');
            }

            // 7. Check Media Upload
            const fileUpload = document.getElementById('file-media-upload');
            if (fileUpload && fileUpload.files && fileUpload.files.length > 0) {
                diffCount += fileUpload.files.length;
                diffNames.push(`Đính kèm ${fileUpload.files.length} tệp`);
            }

            setDirtyState(diffCount, diffNames);
        }

        function askRevertQuestionChanges() {
            if (!currentQuestionLoadedData) {
                location.reload();
                return;
            }
            showConfirmModal({
                title: 'Khôi phục dữ liệu gốc?',
                desc: 'Tất cả các thay đổi vừa chỉnh sửa chưa lưu sẽ bị hủy bỏ và khôi phục lại dữ liệu gốc của câu hỏi này.',
                confirmBtnText: 'Khôi phục ngay',
                onConfirm: () => {
                    const q = currentQuestionLoadedData;
                    document.getElementById('form-inp-title').value = q.title || '';
                    document.getElementById('sel-type').value = q.type || 'MultipleChoice';
                    document.getElementById('form-inp-points').value = q.points || 1;
                    document.getElementById('form-inp-position').value = q.position ?? 0;
                    document.getElementById('form-inp-published').checked = !!q.is_published;

                    currentOptionsState = q.parsed_options || q.options || [];
                    renderOptionsByType(q.type || 'MultipleChoice', currentOptionsState);
                    renderAssetsGallery(q.assets || []);

                    captureInitialSnapshot(q);
                    showToast('Đã khôi phục về dữ liệu gốc!', 'success');
                }
            });
        }

        /* ⚡ REALTIME QUESTION SWITCHER */
        function loadQuestionRealtime(questionId) {
            const currentQId = document.getElementById('current-q-id')?.value;
            if (currentQId && parseInt(currentQId) === parseInt(questionId)) return;

            if (isFormDirty) {
                const posInput = document.getElementById('form-inp-position')?.value;
                const qNum = posInput !== '' ? (parseInt(posInput) + 1) : 'hiện tại';
                showConfirmModal({
                    title: `⚠️ Câu #${qNum} chưa được lưu!`,
                    desc: `Bạn vừa sửa đổi nội dung của Câu #${qNum} nhưng chưa bấm Lưu. Nếu chuyển câu ngay, các thay đổi vừa sửa sẽ bị mất.`,
                    confirmBtnText: 'Bỏ thay đổi & Chuyển câu',
                    onConfirm: () => {
                        setDirtyState(false);
                        doLoadQuestionRealtime(questionId);
                    }
                });
                return;
            }
            doLoadQuestionRealtime(questionId);
        }

        /* 🎯 ĐỒNG BỘ TIÊU ĐỀ CÂU HỎI LÊN THANH TOPBAR */
        function updateTopbarQuestionInfo(qNum, type, points, isPublished, isCreating = false) {
            const crumb = document.getElementById('topbar-q-crumb');
            const allNavCards = document.querySelectorAll('.q-nav-card:not(#q-card-new-slot)');
            const totalQuestions = allNavCards.length;

            if (crumb) {
                if (isCreating) {
                    crumb.textContent = '✨ Soạn câu mới';
                    crumb.className = 'nav-q-active-crumb is-creating';
                } else {
                    crumb.textContent = `Câu #${qNum} / ${totalQuestions || 1}`;
                    crumb.className = 'nav-q-active-crumb';
                }
            }
        }

        function doLoadQuestionRealtime(questionId) {
            document.querySelectorAll('.q-nav-card').forEach(card => card.classList.remove('active'));
            const activeCard = document.getElementById(`q-nav-item-${questionId}`);
            if (activeCard) activeCard.classList.add('active');
            document.getElementById('q-card-new-slot').style.display = 'none';

            return fetch(`/quan-tri/questions/${questionId}`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(data => {
                if (!data.success || !data.question) return;
                const q = data.question;
                const qNum = (q.position ?? 0) + 1;

                document.getElementById('current-q-id').value = q.id;
                document.getElementById('form-method').value = 'PUT';
                document.getElementById('studio-form').action = `/quan-tri/questions/${q.id}`;
                document.getElementById('editor-block-title').textContent = `KHỐI 1: CHỈNH SỬA CÂU #${qNum}`;
                const typeTag = document.getElementById('editor-type-tag');
                if (typeTag) {
                    const opt = document.querySelector(`#sel-type option[value="${q.type}"]`);
                    typeTag.textContent = `🎯 ${opt ? opt.textContent.trim() : (q.type || 'Trắc nghiệm')}`;
                }
                document.getElementById('form-inp-title').value = q.title || '';
                document.getElementById('sel-type').value = q.type || 'MultipleChoice';
                document.getElementById('form-inp-points').value = q.points || 1;
                document.getElementById('form-inp-position').value = q.position ?? 0;
                document.getElementById('form-inp-published').checked = !!q.is_published;
                document.getElementById('btn-delete-q-trigger').style.display = 'block';

                const topSaveTxt = document.getElementById('btn-top-save-text');
                if (topSaveTxt) topSaveTxt.textContent = `Lưu Câu #${qNum} (Ctrl+S)`;
                document.getElementById('btn-save-text').textContent = `LƯU THAY ĐỔI CÂU #${qNum} (Ctrl + S)`;

                currentOptionsState = q.parsed_options || q.options || [];
                renderOptionsByType(q.type || 'MultipleChoice', currentOptionsState);
                renderAssetsGallery(q.assets || []);

                // Đồng bộ topbar ngay lập tức
                updateTopbarQuestionInfo(qNum, q.type, q.points, !!q.is_published, false);

                const newUrl = new URL(window.location.href);
                newUrl.searchParams.set('q', q.id);
                newUrl.searchParams.delete('action');
                window.history.pushState({}, '', newUrl.toString());

                captureInitialSnapshot(q);
                showToast(`Đã tải Câu #${qNum}`, 'success');

                // Nếu modal simulator đang mở, tự động cập nhật lại nội dung và nốt checkpoint
                const simModal = document.getElementById('modal-student-sim');
                if (simModal && simModal.classList.contains('active')) {
                    renderSimulatorNavigation();
                    renderSimulatorContent();
                }

                return q;
            })
            .catch(err => {
                console.error('Lỗi nạp câu hỏi:', err);
                showToast('Lỗi khi nạp câu hỏi: ' + err.message, 'error');
            });
        }

        function switchToNewQuestionMode() {
            if (isFormDirty) {
                const posInput = document.getElementById('form-inp-position')?.value;
                const qNum = posInput !== '' ? (parseInt(posInput) + 1) : 'hiện tại';
                showConfirmModal({
                    title: `⚠️ Câu #${qNum} chưa được lưu!`,
                    desc: `Bạn vừa sửa đổi nội dung của Câu #${qNum} nhưng chưa bấm Lưu. Nếu tạo câu mới ngay, các thay đổi vừa sửa sẽ bị mất.`,
                    confirmBtnText: 'Bỏ thay đổi & Tạo câu mới',
                    onConfirm: () => {
                        setDirtyState(false);
                        doSwitchToNewQuestionMode();
                    }
                });
                return;
            }
            doSwitchToNewQuestionMode();
        }

        function doSwitchToNewQuestionMode() {
            document.querySelectorAll('.q-nav-card').forEach(card => card.classList.remove('active'));
            const newSlot = document.getElementById('q-card-new-slot');
            if (newSlot) {
                newSlot.style.display = 'flex';
                newSlot.classList.add('active');
            }

            document.getElementById('current-q-id').value = '';
            document.getElementById('form-method').value = 'POST';
            document.getElementById('studio-form').action = STORE_URL;
            document.getElementById('editor-block-title').textContent = 'SOẠN THẢO CÂU HỎI MỚI';
            document.getElementById('form-inp-title').value = '';
            document.getElementById('sel-type').value = 'MultipleChoice';
            document.getElementById('form-inp-points').value = 1;
            const newPos = document.querySelectorAll('.q-nav-card:not(#q-card-new-slot)').length;
            document.getElementById('form-inp-position').value = newPos;
            document.getElementById('form-inp-published').checked = true;
            document.getElementById('btn-delete-q-trigger').style.display = 'none';
            const topSaveTxt = document.getElementById('btn-top-save-text');
            if (topSaveTxt) topSaveTxt.textContent = 'Lưu Câu Mới';
            document.getElementById('btn-save-text').textContent = 'TẠO CÂU HỎI MỚI (Ctrl + S)';

            currentOptionsState = [
                { content: '', is_correct: true, image_path: '' },
                { content: '', is_correct: false, image_path: '' },
                { content: '', is_correct: false, image_path: '' },
                { content: '', is_correct: false, image_path: '' }
            ];
            renderOptionsByType('MultipleChoice', currentOptionsState);
            document.getElementById('media-assets-gallery').innerHTML = '';

            // Đồng bộ topbar sang chế độ câu mới
            updateTopbarQuestionInfo(newPos + 1, 'MultipleChoice', 1, true, true);

            const newUrl = new URL(window.location.href);
            newUrl.searchParams.set('action', 'new');
            newUrl.searchParams.delete('q');
            window.history.pushState({}, '', newUrl.toString());

            captureInitialSnapshot({
                title: '',
                type: 'MultipleChoice',
                points: 1,
                position: newPos,
                is_published: true,
                parsed_options: currentOptionsState,
                assets: []
            });
            showToast('Đã mở Form Soạn thảo câu hỏi mới', 'success');
        }

        /* 🎯 RENDER OPTIONS BY TYPE (Matching, Sequence, MultipleChoiceText, Hotspot, MultipleChoice) */
        function renderOptionsByType(type, optionsArray) {
            const container = document.getElementById('options-box');
            const headerTitle = document.getElementById('block-options-header-title');
            const bannerText = document.getElementById('type-guide-text');
            const addBtnText = document.getElementById('btn-add-option-text');
            const addBtn = document.getElementById('btn-add-option-trigger') || document.getElementById('btn-add-option-row');
            if (!container) return;
            container.innerHTML = '';

            if (type === 'Matching') {
                // 🔗 GHÉP NỐI (MATCHING)
                if (headerTitle) headerTitle.textContent = 'KHỐI 2: CẤU HÌNH CÁC CẶP GHÉP NỐI (VẾ TRÁI ──🔗──> VẾ PHẢI)';
                if (bannerText) bannerText.innerHTML = '🔗 <b>Dạng Ghép Nối 2 Vế (Matching):</b> Điền các cặp đối tượng tương ứng vào 2 cột (Ví dụ: <i>Thuật ngữ ➔ Định nghĩa</i>). Khi thi, hệ thống sẽ tự động ghép nối vế trái với vế phải và tự động xáo trộn vị trí để học sinh nối cặp.';
                if (addBtn) addBtn.style.display = 'inline-flex';
                if (addBtnText) addBtnText.textContent = 'Thêm cặp ghép nối mới';

                const pairs = (optionsArray && optionsArray.length) ? optionsArray : [
                    { left: '', right: '' },
                    { left: '', right: '' },
                    { left: '', right: '' },
                    { left: '', right: '' }
                ];

                pairs.forEach((item, idx) => {
                    let leftVal = item.left || item.metadata?.left || '';
                    let rightVal = item.right || item.metadata?.right || '';
                    if (!leftVal && !rightVal && item.content) {
                        if (item.content.includes(':::')) {
                            const parts = item.content.split(':::');
                            leftVal = parts[0].trim();
                            rightVal = parts[1].trim();
                        } else {
                            leftVal = item.content;
                        }
                    }

                    const div = document.createElement('div');
                    div.className = 'matching-pair-card';
                    div.setAttribute('data-idx', idx);
                    div.innerHTML = `
                        <div class="matching-pair-badge">🔗 Cặp ${idx + 1}</div>
                        <input type="text" name="options[${idx}][left]" class="form-input" value="${escapeHtml(leftVal)}" placeholder="Vế Trái (Ví dụ: RAM, CPU...)" required>
                        <div class="matching-link-icon">➔</div>
                        <input type="text" name="options[${idx}][right]" class="form-input" value="${escapeHtml(rightVal)}" placeholder="Vế Phải (Ví dụ: Bộ nhớ tạm...)" required>
                        <button type="button" class="btn-del-opt" onclick="askDeleteOptionRow(this)" title="Xóa cặp này">✕</button>
                    `;
                    container.appendChild(div);
                });

            } else if (type === 'Sequence') {
                // 🔢 SẮP XẾP THỨ TỰ (SEQUENCE)
                if (headerTitle) headerTitle.textContent = 'KHỐI 2: CẤU HÌNH TRÌNH TỰ CÁC BƯỚC (BƯỚC 1 ➔ BƯỚC 2 ➔ BƯỚC 3)';
                if (bannerText) bannerText.innerHTML = '🔢 <b>Dạng Sắp Xếp Thứ Tự (Sequence):</b> Nhập các bước hành động theo đúng trình tự chuẩn từ trên xuống dưới (Bước 1 ➔ Bước 2...). Khi thi, hệ thống sẽ tự động xáo trộn để học sinh kéo thả sắp xếp lại theo đúng quy trình.';
                if (addBtn) addBtn.style.display = 'inline-flex';
                if (addBtnText) addBtnText.textContent = 'Thêm bước tiếp theo';

                const steps = (optionsArray && optionsArray.length) ? optionsArray : [
                    { content: '' }, { content: '' }, { content: '' }
                ];

                steps.forEach((item, idx) => {
                    const div = document.createElement('div');
                    div.className = 'sequence-step-card';
                    div.setAttribute('data-idx', idx);
                    div.innerHTML = `
                        <div class="sequence-step-badge">Bước ${idx + 1}</div>
                        <input type="text" name="options[${idx}][content]" class="form-input" value="${escapeHtml(item.content || '')}" placeholder="Mô tả hành động của bước ${idx + 1}..." required>
                        <button type="button" class="btn-del-opt" onclick="askDeleteOptionRow(this)" title="Xóa bước này">✕</button>
                    `;
                    container.appendChild(div);
                });

            } else if (type === 'MultipleChoiceText') {
                // 📝 TRẮC NGHIỆM VĂN BẢN (MULTIPLE CHOICE TEXT)
                if (headerTitle) headerTitle.textContent = 'KHỐI 2: CÁC MỤC VĂN BẢN & ĐÁP ÁN CHỌN TƯƠNG ỨNG (DROPDOWN)';
                if (bannerText) bannerText.innerHTML = '📋 <b>Dạng Phân Loại / Dropdown (MultipleChoiceText):</b> Dành cho câu hỏi phân loại từng thuật ngữ (Ví dụ: <i>Máy in ➔ Phần cứng</i>, <i>Cơ sở dữ liệu ➔ Phần mềm</i>). Bạn có thể thêm các nhãn lựa chọn vào Dropdown rồi chọn đáp án đúng cho từng dòng mục bên dưới.';
                if (addBtn) addBtn.style.display = 'inline-flex';
                if (addBtnText) addBtnText.textContent = 'Thêm mục mới';

                // Thu thập tất cả các lựa chọn khả dĩ trong câu hỏi này (ví dụ: Phần cứng / Phần mềm)
                const candidateChoices = new Set();
                (optionsArray || []).forEach(o => {
                    if (o.right && o.right.trim()) candidateChoices.add(o.right.trim());
                    if (o.metadata?.right && o.metadata.right.trim()) candidateChoices.add(o.metadata.right.trim());
                    if (Array.isArray(o.available_options)) o.available_options.forEach(v => candidateChoices.add(v.trim()));
                    if (o.content && o.content.includes(':::')) {
                        const parts = o.content.split(':::');
                        if (parts[1] && parts[1].trim()) candidateChoices.add(parts[1].trim());
                    }
                });
                const distinctChoices = Array.from(candidateChoices);

                // Thanh Ribbon Quản Lý / Thêm Lựa Chọn Dropdown
                const ribbonDiv = document.createElement('div');
                ribbonDiv.style.cssText = 'display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; background:#f0fdf4; border:1.5px solid #86efac; border-radius:10px; padding:10px 14px; margin-bottom:12px;';
                ribbonDiv.innerHTML = `
                    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                        <span style="font-size:12.5px; font-weight:800; color:#166534;">🏷️ Các lựa chọn có trong Dropdown:</span>
                        <div style="display:flex; gap:6px; flex-wrap:wrap;">
                            ${distinctChoices.map(c => `
                                <span style="display:inline-flex; align-items:center; gap:5px; background:#ffffff; color:#15803d; border:1.5px solid #86efac; font-size:12px; font-weight:800; padding:3px 10px; border-radius:999px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                                    ${escapeHtml(c)}
                                    <b onclick="removeMctCandidateChoice('${escapeHtml(c)}')" style="cursor:pointer; color:#ef4444; font-size:13px; font-weight:900; margin-left:2px;" title="Xóa lựa chọn này">✕</b>
                                </span>
                            `).join('')}
                        </div>
                    </div>
                    <button type="button" onclick="promptAddMctChoice()" style="background:#16a34a; color:#fff; border:none; padding:6px 14px; border-radius:7px; font-size:12px; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:5px; box-shadow:0 2px 6px rgba(22,163,74,0.35);">
                        <span>➕</span> Thêm lựa chọn Dropdown
                    </button>
                `;
                container.appendChild(ribbonDiv);

                (optionsArray || []).forEach((item, idx) => {
                    let leftVal = item.left || item.metadata?.left || '';
                    let rightVal = item.right || item.metadata?.right || '';
                    if (!leftVal && !rightVal && item.content) {
                        if (item.content.includes(':::')) {
                            const parts = item.content.split(':::');
                            leftVal = parts[0].trim();
                            rightVal = parts[1].trim();
                        } else {
                            leftVal = item.content;
                        }
                    }

                    const itemChoices = (item.available_options && item.available_options.length) ? item.available_options : distinctChoices;

                    const div = document.createElement('div');
                    div.className = 'matching-pair-card';
                    div.style.background = '#f8fafc';
                    div.style.borderColor = '#cbd5e1';
                    div.setAttribute('data-idx', idx);

                    let rightControlHtml = '';
                    if (itemChoices.length > 0) {
                        let optionsHtml = '';
                        itemChoices.forEach(choice => {
                            const isSel = (choice.trim() === rightVal.trim());
                            optionsHtml += `<option value="${escapeHtml(choice)}" ${isSel ? 'selected' : ''}>${escapeHtml(choice)}</option>`;
                        });
                        rightControlHtml = `
                            <select name="options[${idx}][right]" class="form-select" onchange="onMctSelectChange(this, ${idx})" style="font-weight:700; color:#1e40af; background:#eff6ff; border-color:#93c5fd;" required>
                                ${optionsHtml}
                                <option value="__ADD_NEW__" style="color:#16a34a; font-weight:800; background:#f0fdf4;">➕ Thêm lựa chọn mới vào danh sách...</option>
                            </select>
                        `;
                    } else {
                        rightControlHtml = `
                            <input type="text" name="options[${idx}][right]" class="form-input" value="${escapeHtml(rightVal)}" placeholder="Đáp án đúng tương ứng" required>
                        `;
                    }

                    div.innerHTML = `
                        <div class="matching-pair-badge" style="background:#475569;">Mục ${idx + 1}</div>
                        <input type="text" name="options[${idx}][left]" class="form-input" value="${escapeHtml(leftVal)}" placeholder="Thuật ngữ / Đối tượng (Ví dụ: Máy in...)" required>
                        <div class="matching-link-icon" style="color:#475569;">➔</div>
                        ${rightControlHtml}
                        <button type="button" class="btn-del-opt" onclick="askDeleteOptionRow(this)" title="Xóa mục này">✕</button>
                    `;
                    container.appendChild(div);
                });

            } else if (type === 'Hotspot') {
                // 🎯 NHẤP VÀO HÌNH (HOTSPOT)
                if (headerTitle) headerTitle.textContent = 'KHỐI 2: HÌNH ẢNH MINH HỌA & VÙNG TƯƠNG TÁC (HOTSPOT)';
                if (bannerText) bannerText.innerHTML = '🎯 <b>Dạng Nhấp Vào Vị Trí Trên Ảnh (Hotspot):</b> Tải ảnh đề bài lên và dùng chuột kéo thả/co giãn các ô vuông tương tác. Nhấp 1-Click vào ô hoặc nút bên dưới để chọn vùng làm đáp án đúng.';
                if (addBtn) addBtn.style.display = 'none';

                let hotspotImg = '';
                if (optionsArray && optionsArray.length) {
                    for (const opt of optionsArray) {
                        if (opt.image_path) { hotspotImg = opt.image_path; break; }
                    }
                }
                if (!hotspotImg) {
                    const galleryImg = document.querySelector('#media-assets-gallery img');
                    if (galleryImg) hotspotImg = galleryImg.src;
                }
                
                let boxesHtml = '';
                let zonesListHtml = '';

                (optionsArray || []).forEach((area, idx) => {
                    const isCorrect = !!area.is_correct;
                    const rect = area.rect || { x: 0, y: 0, w: 1000, h: 1000 };
                    const leftPct = (rect.x / 100).toFixed(2);
                    const topPct = (rect.y / 100).toFixed(2);
                    const widthPct = (rect.w / 100).toFixed(2);
                    const heightPct = (rect.h / 100).toFixed(2);

                    boxesHtml += `
                        <div id="hotspot-box-${idx}" 
                             onmousedown="startHotspotDrag(event, ${idx})"
                             onclick="selectHotspotZone(${idx})"
                             style="position:absolute; left:${leftPct}%; top:${topPct}%; width:${widthPct}%; height:${heightPct}%; 
                                    border:${isCorrect ? '3px solid #16a34a' : '2px dashed #3b82f6'}; 
                                    background:${isCorrect ? 'rgba(34, 197, 94, 0.4)' : 'rgba(59, 130, 246, 0.15)'}; 
                                    border-radius:4px; z-index:10; cursor:move; user-select:none;" 
                                    title="🎯 Kéo chuột để di chuyển ô này đến vị trí bất kỳ trên ảnh!">
                            <span style="position:absolute; top:-10px; left:2px; font-size:10px; font-weight:900; background:${isCorrect ? '#16a34a' : '#2563eb'}; color:#fff; padding:1px 5px; border-radius:4px; pointer-events:none;">
                                ${idx + 1}
                            </span>
                            <!-- Chấm tròn Resize góc dưới phải -->
                            <div class="hs-resize-handle" 
                                 onmousedown="startHotspotResize(event, ${idx})" 
                                 style="position:absolute; right:-5px; bottom:-5px; width:12px; height:12px; background:#ffffff; border:2px solid ${isCorrect ? '#16a34a' : '#2563eb'}; border-radius:3px; cursor:se-resize; z-index:20;" 
                                 title="⤡ Kéo chấm tròn này để thu nhỏ / phóng to khung bao quanh"></div>
                        </div>
                    `;

                    zonesListHtml += `
                        <div class="hotspot-pill-item ${isCorrect ? 'pill-correct' : ''}" 
                             id="hotspot-card-${idx}" 
                             onclick="setHotspotCorrectZone(${idx})"
                             style="display:inline-flex; align-items:center; gap:6px; padding:6px 14px; border-radius:999px; cursor:pointer; 
                                    background:${isCorrect ? '#dcfce7' : '#f8fafc'}; 
                                    border:2px solid ${isCorrect ? '#16a34a' : '#cbd5e1'}; 
                                    color:${isCorrect ? '#15803d' : '#334155'}; 
                                    box-shadow:${isCorrect ? '0 0 12px rgba(34, 197, 94, 0.4)' : 'none'};
                                    font-size:12.5px; font-weight:800; transition:all 0.15s;" 
                                    title="Bấm để chọn Vùng #${idx + 1} làm Đáp án đúng">
                            
                            <span style="width:20px; height:20px; border-radius:50%; background:${isCorrect ? '#16a34a' : '#64748b'}; color:#fff; font-size:11px; font-weight:900; display:grid; place-items:center;">
                                ${idx + 1}
                            </span>
                            
                            <span>${escapeHtml(area.content || `Vùng ${idx + 1}`)}</span>
                            
                            ${isCorrect ? `<span style="background:#16a34a; color:#fff; font-size:10px; padding:2px 7px; border-radius:999px; font-weight:900; letter-spacing:0.3px;">✓ ĐÁP ÁN ĐÚNG</span>` : ''}
                            
                            <button type="button" onclick="event.stopPropagation(); deleteHotspotZone(${idx})" style="background:none; border:none; color:#94a3b8; font-size:13px; font-weight:900; cursor:pointer; padding:0 2px; margin-left:2px;" title="Xóa vùng này">✕</button>

                            <input type="hidden" name="options[${idx}][content]" value="${escapeHtml(area.content || `Vùng ${idx + 1}`)}">
                            <input type="hidden" name="options[${idx}][rect][x]" value="${rect.x}">
                            <input type="hidden" name="options[${idx}][rect][y]" value="${rect.y}">
                            <input type="hidden" name="options[${idx}][rect][w]" value="${rect.w}">
                            <input type="hidden" name="options[${idx}][rect][h]" value="${rect.h}">
                            <input type="hidden" name="options[${idx}][is_correct]" id="hotspot-chk-${idx}" value="${isCorrect ? '1' : '0'}">
                        </div>
                    `;
                });

                const div = document.createElement('div');
                div.style.cssText = 'background:#ffffff; border:1.5px solid #cbd5e1; border-radius:10px; padding:16px; display:flex; flex-direction:column; gap:14px;';
                div.innerHTML = `
                    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
                        <div style="font-size:13px; font-weight:800; color:#1e293b;">🖼️ Bức ảnh tương tác gốc & Các vùng bấm:</div>
                        <div style="display:flex; gap:8px;">
                            <label class="btn-opt-action-img" style="background:#4f46e5; color:#fff; padding:6px 12px; border-radius:6px; font-size:12px; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:5px;">
                                <span>🔄</span> Đổi ảnh Hotspot mới
                                <input type="file" name="asset_file" id="inp-hotspot-upload" accept="image/*" style="display:none;" onchange="onHotspotImageUploaded(this)">
                            </label>
                            <button type="button" class="btn-opt-action-img" onclick="addNewHotspotZone()" style="background:#0f172a; color:#fff; padding:6px 12px; border-radius:6px; font-size:12px; font-weight:800; cursor:pointer;">
                                <span>➕</span> Thêm vùng bấm mới
                            </button>
                        </div>
                    </div>

                    ${hotspotImg ? `
                        <div id="hotspot-admin-canvas-box" style="position:relative; width:100%; border-radius:8px; overflow:hidden; border:2px solid #cbd5e1; background:#0f172a; line-height:0;">
                            <img id="hotspot-admin-base-img" src="${hotspotImg}" style="display:block; width:100%; height:auto;" alt="Hotspot Image">
                            ${boxesHtml}
                        </div>
                    ` : `
                        <div style="padding:30px; text-align:center; color:#64748b; font-size:13px;">(Chưa có ảnh Hotspot. Vui lòng bấm "Đổi ảnh Hotspot mới" ở trên để tải ảnh)</div>
                    `}

                    <div style="display:flex; flex-direction:column; gap:8px; margin-top:2px;">
                        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:6px;">
                            <div style="font-size:12px; font-weight:800; color:#475569; text-transform:uppercase;">🎯 Chọn Vùng làm Đáp án đúng (1-Click):</div>
                            <small style="color:#64748b; font-size:11.5px;">(Bấm vào nút hoặc nhấp trực tiếp ô trên ảnh)</small>
                        </div>
                        <div style="display:flex; flex-wrap:wrap; gap:8px; align-items:center;">
                            ${zonesListHtml}
                        </div>
                    </div>
                `;
                container.appendChild(div);
            } else {
                // 🔘 MULTIPLE CHOICE / MULTIPLE RESPONSE
                if (headerTitle) headerTitle.textContent = (type === 'MultipleChoice') ? 'KHỐI 2: CÁC LỰA CHỌN & THIẾT LẬP 1 ĐÁP ÁN ĐÚNG (RADIO)' : 'KHỐI 2: CÁC LỰA CHỌN & THIẾT LẬP NHIỀU ĐÁP ÁN ĐÚNG (CHECKBOX)';
                if (addBtn) addBtn.style.display = 'inline-flex';
                if (addBtnText) addBtnText.textContent = 'Thêm đáp án lựa chọn mới';

                const isSingle = (type === 'MultipleChoice');
                if (bannerText) {
                    if (type === 'MultipleChoice') {
                        bannerText.innerHTML = '🔘 <b>Dạng Chọn 1 Đáp Án Đúng (Single Choice):</b> Nhập nội dung các phương án A, B, C, D (có thể đính kèm ảnh) và tích chọn <b>[✓ Đúng]</b> cho duy nhất 1 đáp án chính xác (Radio).';
                    } else {
                        bannerText.innerHTML = '☑️ <b>Dạng Chọn Nhiều Đáp Án Đúng (Multiple Response):</b> Nhập các phương án A, B, C, D... và tích chọn <b>[✓ Đúng]</b> cho các đáp án chính xác (cho phép chọn từ 2 đáp án trở lên - Checkbox).';
                    }
                }

                const choices = (optionsArray && optionsArray.length) ? optionsArray : [
                    { content: '', is_correct: true, image_path: '' },
                    { content: '', is_correct: false, image_path: '' },
                    { content: '', is_correct: false, image_path: '' },
                    { content: '', is_correct: false, image_path: '' }
                ];

                choices.forEach((opt, idx) => {
                    const letter = lettersList[idx % lettersList.length];
                    const isCorrect = !!opt.is_correct;
                    const imgPath = opt.image_path || '';

                    const div = document.createElement('div');
                    div.className = `option-row-card ${isCorrect ? 'is-correct' : ''}`;
                    div.setAttribute('data-idx', idx);
                    div.innerHTML = `
                        <span class="opt-letter-badge opt-letter-${letter}">${letter}</span>
                        <input type="text" name="options[${idx}][content]" class="form-input opt-inp-txt" value="${escapeHtml(opt.content || '')}" placeholder="Nội dung đáp án ${letter}">
                        <div class="option-image-control-box">
                            ${imgPath ? `
                                <div class="opt-img-has" style="display:flex; align-items:center; gap:5px;">
                                    <img src="${imgPath}" class="opt-thumb-preview" onclick="zoomImage('${imgPath}')" title="Bấm để xem phóng to" alt="Thumb">
                                    <input type="hidden" name="options[${idx}][image_path]" class="opt-inp-img-path" value="${escapeHtml(imgPath)}">
                                    <button type="button" class="btn-opt-action-img btn-opt-change" onclick="triggerOptionFileUpload(this)" title="Đổi ảnh khác">🔄 Đổi</button>
                                    <button type="button" class="btn-opt-action-img btn-opt-clear" onclick="askClearOptionImage(this)" title="Gỡ ảnh">✕ Gỡ</button>
                                </div>
                            ` : `
                                <div class="opt-img-none" style="display:flex; align-items:center; gap:5px;">
                                    <button type="button" class="btn-opt-action-img btn-opt-add-new" onclick="triggerOptionFileUpload(this)"><span>🖼️</span> Thêm ảnh</button>
                                    <input type="hidden" name="options[${idx}][image_path]" class="opt-inp-img-path" value="">
                                </div>
                            `}
                            <input type="file" name="options[${idx}][image_file]" class="opt-hidden-file-input" accept="image/*" style="display:none;" onchange="onOptionFileSelected(this)">
                        </div>
                        <label class="btn-correct-chk">
                            <input type="${isSingle ? 'radio' : 'checkbox'}" name="options[${idx}][is_correct]" value="1" class="opt-inp-chk" ${isCorrect ? 'checked' : ''} onchange="onCorrectChange(this)">
                            <span>${isCorrect ? '✓ Đúng' : 'Đúng'}</span>
                        </label>
                        <button type="button" class="btn-del-opt" onclick="askDeleteOptionRow(this)" title="Xóa đáp án">✕</button>
                    `;
                    container.appendChild(div);
                });
            }

            const bottomBar = document.getElementById('options-bottom-action-bar');
            const bottomBtnText = document.getElementById('btn-add-choice-bottom-text');
            if (bottomBar) {
                bottomBar.style.display = (type === 'Hotspot') ? 'none' : 'block';
                if (bottomBtnText) {
                    if (type === 'Matching') bottomBtnText.textContent = 'Thêm cặp ghép nối mới (Vế Trái ➔ Vế Phải)';
                    else if (type === 'Sequence') bottomBtnText.textContent = 'Thêm bước thực hiện tiếp theo';
                    else if (type === 'MultipleChoiceText') bottomBtnText.textContent = 'Thêm dòng mục thuật ngữ mới';
                    else bottomBtnText.textContent = 'Thêm phương án lựa chọn mới';
                }
            }

            const mediaBox = document.getElementById('block-media-container');
            if (mediaBox) {
                mediaBox.style.display = (type === 'Hotspot') ? 'none' : 'block';
            }
        }

        function onQuestionTypeChange(type) {
            let existingImg = '';
            if (currentOptionsState && currentOptionsState.length) {
                for (const opt of currentOptionsState) {
                    if (opt.image_path) { existingImg = opt.image_path; break; }
                }
            }
            if (!existingImg) {
                const galleryImg = document.querySelector('#media-assets-gallery img');
                if (galleryImg) existingImg = galleryImg.src;
            }

            if (type === 'Hotspot') {
                if (!currentOptionsState || !currentOptionsState.length || !currentOptionsState[0].rect) {
                    currentOptionsState = [
                        { content: 'Vùng 1', is_correct: true, rect: { x: 1000, y: 1000, w: 2500, h: 2500 }, image_path: existingImg },
                        { content: 'Vùng 2', is_correct: false, rect: { x: 4500, y: 1000, w: 2500, h: 2500 }, image_path: existingImg }
                    ];
                } else {
                    currentOptionsState.forEach(o => { if (!o.image_path && existingImg) o.image_path = existingImg; });
                }
            } else if (type === 'Matching') {
                if (!currentOptionsState || !currentOptionsState.length || (!currentOptionsState[0].left && !currentOptionsState[0].right)) {
                    currentOptionsState = [
                        { left: 'Mục 1', right: 'Định nghĩa 1' },
                        { left: 'Mục 2', right: 'Định nghĩa 2' },
                        { left: 'Mục 3', right: 'Định nghĩa 3' }
                    ];
                }
            } else if (type === 'Sequence') {
                if (!currentOptionsState || !currentOptionsState.length) {
                    currentOptionsState = [
                        { content: 'Bước 1: Bật máy tính' },
                        { content: 'Bước 2: Mở trình duyệt' },
                        { content: 'Bước 3: Đăng nhập tài khoản' }
                    ];
                }
            } else if (type === 'MultipleChoiceText') {
                if (!currentOptionsState || !currentOptionsState.length) {
                    currentOptionsState = [
                        { left: 'Máy in', right: 'Phần cứng (Hardware)', available_options: ['Phần cứng (Hardware)', 'Phần mềm (Software)'] },
                        { left: 'Cơ sở dữ liệu', right: 'Phần mềm (Software)', available_options: ['Phần cứng (Hardware)', 'Phần mềm (Software)'] }
                    ];
                }
            } else {
                if (!currentOptionsState || !currentOptionsState.length || currentOptionsState[0].rect || currentOptionsState[0].left) {
                    currentOptionsState = [
                        { content: 'Lựa chọn A', is_correct: true, image_path: '' },
                        { content: 'Lựa chọn B', is_correct: false, image_path: '' },
                        { content: 'Lựa chọn C', is_correct: false, image_path: '' },
                        { content: 'Lựa chọn D', is_correct: false, image_path: '' }
                    ];
                }
            }
            const typeTag = document.getElementById('editor-type-tag');
            if (typeTag) {
                const opt = document.querySelector(`#sel-type option[value="${type}"]`);
                typeTag.textContent = `🎯 ${opt ? opt.textContent.trim() : type}`;
            }
            renderOptionsByType(type, currentOptionsState);
        }

        function addOptionRow() {
            const type = document.getElementById('sel-type').value;
            const container = document.getElementById('options-box');
            const count = container.children.length;

            if (type === 'Matching') {
                const div = document.createElement('div');
                div.className = 'matching-pair-card';
                div.setAttribute('data-idx', count);
                div.innerHTML = `
                    <div class="matching-pair-badge">🔗 Cặp ${count + 1}</div>
                    <input type="text" name="options[${count}][left]" class="form-input" placeholder="Vế Trái" required>
                    <div class="matching-link-icon">➔</div>
                    <input type="text" name="options[${count}][right]" class="form-input" placeholder="Vế Phải" required>
                    <button type="button" class="btn-del-opt" onclick="askDeleteOptionRow(this)" title="Xóa cặp này">✕</button>
                `;
                container.appendChild(div);
            } else if (type === 'Sequence') {
                const div = document.createElement('div');
                div.className = 'sequence-step-card';
                div.setAttribute('data-idx', count);
                div.innerHTML = `
                    <div class="sequence-step-badge">Bước ${count + 1}</div>
                    <input type="text" name="options[${count}][content]" class="form-input" placeholder="Mô tả hành động của bước ${count + 1}..." required>
                    <button type="button" class="btn-del-opt" onclick="askDeleteOptionRow(this)" title="Xóa bước này">✕</button>
                `;
                container.appendChild(div);
            } else if (type === 'MultipleChoiceText') {
                const candidateChoices = new Set();
                container.querySelectorAll('select[name*="[right]"]').forEach(sel => {
                    Array.from(sel.options).forEach(opt => {
                        if (opt.value && opt.value !== '__ADD_NEW__') candidateChoices.add(opt.value.trim());
                    });
                });
                currentOptionsState.forEach(o => {
                    if (o.right) candidateChoices.add(o.right.trim());
                    if (Array.isArray(o.available_options)) o.available_options.forEach(v => candidateChoices.add(v.trim()));
                });
                const distinctChoices = Array.from(candidateChoices);

                let rightControlHtml = '';
                if (distinctChoices.length > 0) {
                    let optionsHtml = '';
                    distinctChoices.forEach(choice => {
                        optionsHtml += `<option value="${escapeHtml(choice)}">${escapeHtml(choice)}</option>`;
                    });
                    rightControlHtml = `
                        <select name="options[${count}][right]" class="form-select" onchange="onMctSelectChange(this, ${count})" style="font-weight:700; color:#1e40af; background:#eff6ff; border-color:#93c5fd;" required>
                            ${optionsHtml}
                            <option value="__ADD_NEW__" style="color:#16a34a; font-weight:800; background:#f0fdf4;">➕ Thêm lựa chọn mới vào danh sách...</option>
                        </select>
                    `;
                } else {
                    rightControlHtml = `<input type="text" name="options[${count}][right]" class="form-input" placeholder="Đáp án đúng tương ứng" required>`;
                }

                const div = document.createElement('div');
                div.className = 'matching-pair-card';
                div.style.background = '#f8fafc';
                div.style.borderColor = '#cbd5e1';
                div.setAttribute('data-idx', count);
                div.innerHTML = `
                    <div class="matching-pair-badge" style="background:#475569;">Mục ${count + 1}</div>
                    <input type="text" name="options[${count}][left]" class="form-input" placeholder="Thuật ngữ / Đối tượng (Ví dụ: Máy in...)" required>
                    <div class="matching-link-icon" style="color:#475569;">➔</div>
                    ${rightControlHtml}
                    <button type="button" class="btn-del-opt" onclick="askDeleteOptionRow(this)" title="Xóa mục này">✕</button>
                `;
                container.appendChild(div);
            } else {
                const letter = lettersList[count % lettersList.length];
                const isSingle = (type === 'MultipleChoice');
                const div = document.createElement('div');
                div.className = 'option-row-card';
                div.setAttribute('data-idx', count);
                div.innerHTML = `
                    <span class="opt-letter-badge opt-letter-${letter}">${letter}</span>
                    <input type="text" name="options[${count}][content]" class="form-input opt-inp-txt" placeholder="Nội dung đáp án ${letter}">
                    <div class="option-image-control-box">
                        <div class="opt-img-none" style="display:flex; align-items:center; gap:5px;">
                            <button type="button" class="btn-opt-action-img btn-opt-add-new" onclick="triggerOptionFileUpload(this)"><span>🖼️</span> Thêm ảnh</button>
                            <input type="hidden" name="options[${count}][image_path]" class="opt-inp-img-path" value="">
                        </div>
                        <input type="file" name="options[${count}][image_file]" class="opt-hidden-file-input" accept="image/*" style="display:none;" onchange="onOptionFileSelected(this)">
                    </div>
                    <label class="btn-correct-chk">
                        <input type="${isSingle ? 'radio' : 'checkbox'}" name="options[${count}][is_correct]" value="1" class="opt-inp-chk" onchange="onCorrectChange(this)">
                        <span>Đúng</span>
                    </label>
                    <button type="button" class="btn-del-opt" onclick="askDeleteOptionRow(this)" title="Xóa đáp án">✕</button>
                `;
                container.appendChild(div);
            }
            refreshOptionLabels();
        }

        function refreshOptionLabels() {
            const type = document.getElementById('sel-type').value;
            const container = document.getElementById('options-box');
            if (!container) return;

            if (type === 'Matching') {
                container.querySelectorAll('.matching-pair-card').forEach((card, idx) => {
                    card.setAttribute('data-idx', idx);
                    const badge = card.querySelector('.matching-pair-badge');
                    if (badge) badge.textContent = `🔗 Cặp ${idx + 1}`;
                    const leftInp = card.querySelector('input[name*="[left]"]');
                    const rightInp = card.querySelector('input[name*="[right]"]');
                    if (leftInp) leftInp.name = `options[${idx}][left]`;
                    if (rightInp) rightInp.name = `options[${idx}][right]`;
                });
            } else if (type === 'Sequence') {
                container.querySelectorAll('.sequence-step-card').forEach((card, idx) => {
                    card.setAttribute('data-idx', idx);
                    const badge = card.querySelector('.sequence-step-badge');
                    if (badge) badge.textContent = `Bước ${idx + 1}`;
                    const contentInp = card.querySelector('input[name*="[content]"]');
                    if (contentInp) contentInp.name = `options[${idx}][content]`;
                });
            } else if (type === 'MultipleChoiceText') {
                container.querySelectorAll('.matching-pair-card').forEach((card, idx) => {
                    card.setAttribute('data-idx', idx);
                    const badge = card.querySelector('.matching-pair-badge');
                    if (badge) badge.textContent = `Mục ${idx + 1}`;
                    const leftInp = card.querySelector('input[name*="[left]"]');
                    const rightInp = card.querySelector('input[name*="[right]"], select[name*="[right]"]');
                    if (leftInp) leftInp.name = `options[${idx}][left]`;
                    if (rightInp) rightInp.name = `options[${idx}][right]`;
                });
            } else {
                const isSingle = (type === 'MultipleChoice');
                container.querySelectorAll('.option-row-card').forEach((row, idx) => {
                    const letter = lettersList[idx % lettersList.length];
                    row.setAttribute('data-idx', idx);
                    const badge = row.querySelector('.opt-letter-badge');
                    if (badge) {
                        badge.textContent = letter;
                        badge.className = `opt-letter-badge opt-letter-${letter}`;
                    }
                    const txt = row.querySelector('.opt-inp-txt');
                    if (txt) {
                        txt.name = `options[${idx}][content]`;
                        txt.placeholder = `Nội dung đáp án ${letter}`;
                    }
                    const chk = row.querySelector('.opt-inp-chk');
                    if (chk) {
                        chk.name = `options[${idx}][is_correct]`;
                        chk.type = isSingle ? 'radio' : 'checkbox';
                    }
                });
            }
        }

        /* 🏷️ MULTIPLE CHOICE TEXT (DROPDOWN MANAGER) CONTROLLERS */
        function onMctSelectChange(selectEl, idx) {
            if (selectEl.value === '__ADD_NEW__') {
                promptAddMctChoice(idx);
            } else {
                if (currentOptionsState[idx]) {
                    currentOptionsState[idx].right = selectEl.value;
                }
            }
        }

        function promptAddMctChoice(targetIdx = null) {
            showCustomPromptModal({
                title: '➕ Thêm Lựa Chọn Dropdown',
                desc: 'Nhập tên lựa chọn phân loại mới (Ví dụ: Thiết bị mạng, Bộ nhớ ngoài, Phần cứng...)',
                icon: '🏷️',
                placeholder: 'Ví dụ: Thiết bị mạng, Bộ nhớ ngoài...',
                onConfirm: (val) => {
                    const allChoices = new Set();
                    currentOptionsState.forEach(o => {
                        if (o.right) allChoices.add(o.right.trim());
                        if (Array.isArray(o.available_options)) o.available_options.forEach(v => allChoices.add(v.trim()));
                    });
                    allChoices.add(val);
                    const updatedChoices = Array.from(allChoices);

                    currentOptionsState.forEach((o, i) => {
                        o.available_options = updatedChoices;
                        if (targetIdx !== null && i === targetIdx) {
                            o.right = val;
                        }
                    });

                    renderOptionsByType('MultipleChoiceText', currentOptionsState);
                    showToast(`🎉 Đã thêm lựa chọn "${val}" vào danh sách Dropdown!`, 'success');
                }
            });
        }

        function removeMctCandidateChoice(choiceToRemove) {
            showConfirmModal({
                title: 'Xóa lựa chọn này khỏi Dropdown?',
                desc: `Bạn có chắc muốn xóa "${choiceToRemove}" khỏi danh sách các lựa chọn trong Dropdown?`,
                confirmBtnText: 'Xóa lựa chọn',
                onConfirm: () => {
                    currentOptionsState.forEach(o => {
                        if (Array.isArray(o.available_options)) {
                            o.available_options = o.available_options.filter(c => c !== choiceToRemove);
                        }
                        if (o.right === choiceToRemove) {
                            o.right = (o.available_options && o.available_options.length) ? o.available_options[0] : '';
                        }
                    });
                    renderOptionsByType('MultipleChoiceText', currentOptionsState);
                    showToast(`Đã xóa lựa chọn "${choiceToRemove}"`, 'success');
                }
            });
        }

        /* 🎯 HOTSPOT INTERACTIVE ZONE CONTROLLERS */
        function setHotspotCorrectZone(targetIdx) {
            currentOptionsState.forEach((area, i) => {
                area.is_correct = (i === targetIdx);
            });
            renderOptionsByType('Hotspot', currentOptionsState);
            showToast(`Đã chọn Vùng #${targetIdx + 1} làm Đáp án đúng`, 'success');
        }

        function deleteHotspotZone(idx) {
            showConfirmModal({
                title: 'Xóa vùng Hotspot này?',
                desc: `Bạn có chắc chắn muốn xóa Vùng #${idx + 1}?`,
                confirmBtnText: 'Xác nhận xóa',
                onConfirm: () => {
                    currentOptionsState.splice(idx, 1);
                    renderOptionsByType('Hotspot', currentOptionsState);
                    showToast('Đã xóa vùng Hotspot', 'success');
                }
            });
        }

        function addNewHotspotZone() {
            const count = currentOptionsState.length;
            currentOptionsState.push({
                content: `Vùng ${count + 1}`,
                is_correct: count === 0,
                image_path: (currentOptionsState[0]?.image_path) || '',
                rect: { x: 1000 + (count * 500) % 7000, y: 3000, w: 1200, h: 4000 }
            });
            renderOptionsByType('Hotspot', currentOptionsState);
            showToast(`Đã thêm Vùng #${count + 1}`, 'success');
        }

        function selectHotspotZone(idx) {
            document.querySelectorAll('.hotspot-pill-item').forEach((c, i) => {
                if (i === idx) {
                    c.style.transform = 'scale(1.06)';
                    c.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                } else {
                    c.style.transform = 'scale(1)';
                }
            });
        }

        function onHotspotImageUploaded(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const reader = new FileReader();
                reader.onload = function(e) {
                    const newImgUrl = e.target.result;
                    currentOptionsState.forEach(area => {
                        area.image_path = newImgUrl;
                    });
                    const imgEl = document.getElementById('hotspot-admin-base-img');
                    if (imgEl) imgEl.src = newImgUrl;
                    showToast('Đã tải ảnh mới thành công (Hãy bấm "Lưu thay đổi" để lưu vào hệ thống)', 'success');
                };
                reader.readAsDataURL(file);
            }
        }

        /* 🎯 HOTSPOT DRAG & RESIZE ENGINE */
        let activeHotspotDrag = null;
        let activeHotspotResize = null;

        function startHotspotDrag(e, idx) {
            if (e.target.classList.contains('hs-resize-handle')) return;
            e.preventDefault();
            e.stopPropagation();
            selectHotspotZone(idx);

            const canvasBox = document.getElementById('hotspot-admin-canvas-box');
            if (!canvasBox) return;
            const rect = canvasBox.getBoundingClientRect();
            const area = currentOptionsState[idx];
            if (!area.rect) area.rect = { x: 0, y: 0, w: 1000, h: 1000 };

            activeHotspotDrag = {
                idx,
                startX: e.clientX,
                startY: e.clientY,
                boxW: rect.width,
                boxH: rect.height,
                initX: area.rect.x,
                initY: area.rect.y,
                w: area.rect.w,
                h: area.rect.h
            };
        }

        function startHotspotResize(e, idx) {
            e.preventDefault();
            e.stopPropagation();
            selectHotspotZone(idx);

            const canvasBox = document.getElementById('hotspot-admin-canvas-box');
            if (!canvasBox) return;
            const rect = canvasBox.getBoundingClientRect();
            const area = currentOptionsState[idx];
            if (!area.rect) area.rect = { x: 0, y: 0, w: 1000, h: 1000 };

            activeHotspotResize = {
                idx,
                startX: e.clientX,
                startY: e.clientY,
                boxW: rect.width,
                boxH: rect.height,
                initW: area.rect.w,
                initH: area.rect.h
            };
        }

        window.addEventListener('mousemove', (e) => {
            if (activeHotspotDrag) {
                const { idx, startX, startY, boxW, boxH, initX, initY, w, h } = activeHotspotDrag;
                const deltaX_permille = ((e.clientX - startX) / boxW) * 10000;
                const deltaY_permille = ((e.clientY - startY) / boxH) * 10000;

                let newX = Math.max(0, Math.min(10000 - w, initX + deltaX_permille));
                let newY = Math.max(0, Math.min(10000 - h, initY + deltaY_permille));

                currentOptionsState[idx].rect.x = newX;
                currentOptionsState[idx].rect.y = newY;

                const boxEl = document.getElementById(`hotspot-box-${idx}`);
                if (boxEl) {
                    boxEl.style.left = `${(newX / 100).toFixed(2)}%`;
                    boxEl.style.top = `${(newY / 100).toFixed(2)}%`;
                }

                const card = document.getElementById(`hotspot-card-${idx}`);
                if (card) {
                    const inpx = card.querySelector(`input[name="options[${idx}][rect][x]"]`);
                    const inpy = card.querySelector(`input[name="options[${idx}][rect][y]"]`);
                    if (inpx) inpx.value = newX;
                    if (inpy) inpy.value = newY;
                }
            } else if (activeHotspotResize) {
                const { idx, startX, startY, boxW, boxH, initW, initH } = activeHotspotResize;
                const deltaW_permille = ((e.clientX - startX) / boxW) * 10000;
                const deltaH_permille = ((e.clientY - startY) / boxH) * 10000;

                let newW = Math.max(200, Math.min(10000, initW + deltaW_permille));
                let newH = Math.max(200, Math.min(10000, initH + deltaH_permille));

                currentOptionsState[idx].rect.w = newW;
                currentOptionsState[idx].rect.h = newH;

                const boxEl = document.getElementById(`hotspot-box-${idx}`);
                if (boxEl) {
                    boxEl.style.width = `${(newW / 100).toFixed(2)}%`;
                    boxEl.style.height = `${(newH / 100).toFixed(2)}%`;
                }

                const card = document.getElementById(`hotspot-card-${idx}`);
                if (card) {
                    const inpw = card.querySelector(`input[name="options[${idx}][rect][w]"]`);
                    const inph = card.querySelector(`input[name="options[${idx}][rect][h]"]`);
                    if (inpw) inpw.value = newW;
                    if (inph) inph.value = newH;
                }
            }
        });

        window.addEventListener('mouseup', () => {
            if (activeHotspotDrag || activeHotspotResize) {
                activeHotspotDrag = null;
                activeHotspotResize = null;
            }
        });

        function onCorrectChange(input) {
            const type = document.getElementById('sel-type').value;
            const isSingle = (type === 'MultipleChoice');

            if (isSingle && input.checked) {
                document.querySelectorAll('.option-row-card').forEach(card => {
                    const cardInput = card.querySelector('.opt-inp-chk');
                    const span = card.querySelector('.btn-correct-chk span');
                    if (cardInput !== input) {
                        cardInput.checked = false;
                        card.classList.remove('is-correct');
                        if (span) span.textContent = 'Đúng';
                    } else {
                        card.classList.add('is-correct');
                        if (span) span.textContent = '✓ Đúng';
                    }
                });
            } else {
                const parent = input.closest('.option-row-card');
                const span = parent?.querySelector('.btn-correct-chk span');
                if (parent) {
                    if (input.checked) {
                        parent.classList.add('is-correct');
                        if (span) span.textContent = '✓ Đúng';
                    } else {
                        parent.classList.remove('is-correct');
                        if (span) span.textContent = 'Đúng';
                    }
                }
            }
        }

        function renderAssetsGallery(assetsArray) {
            const container = document.getElementById('media-assets-gallery');
            if (!container) return;
            container.innerHTML = '';
            (assetsArray || []).forEach(asset => {
                const assetPath = asset.path || (asset.file_path ? `/storage/${asset.file_path}` : '');
                const assetName = asset.original_name || asset.file_name || (assetPath ? assetPath.split('/').pop() : 'file');
                const isImg = (asset.kind === 'image') || /\.(png|jpe?g|gif|webp|svg)$/i.test(assetPath || assetName);
                const isAud = (asset.kind === 'audio') || /\.(mp3|wav|ogg|m4a|aac)$/i.test(assetPath || assetName);

                const div = document.createElement('div');
                div.className = 'media-asset-card';
                div.id = `asset-card-${asset.id}`;

                div.innerHTML = `
                    <div class="media-asset-thumb-wrap">
                        ${isImg ? 
                            `<img src="${assetPath}" onclick="zoomImage('${assetPath}')" title="Bấm để xem phóng to" alt="${escapeHtml(assetName)}">` : 
                            isAud ?
                            `<div style="padding:15px 10px; background:#312e81; color:#fff; text-align:center; width:100%;">
                                <span style="font-size:24px;">🎵</span>
                                <audio controls src="${assetPath}" style="width:100%; height:30px; margin-top:5px;"></audio>
                             </div>` :
                            `<div style="padding:20px; background:#f1f5f9; text-align:center; font-size:24px;">
                                📄
                             </div>`
                        }
                    </div>
                    <div class="media-asset-info">
                        <span class="media-file-name" title="${escapeHtml(assetName)}">${escapeHtml(assetName)}</span>
                        <div class="media-action-row">
                            ${isImg ? `<button type="button" class="btn-media-tool btn-media-view" onclick="zoomImage('${assetPath}')">👁️ Xem</button>` : ''}
                            <button type="button" class="btn-media-tool btn-media-del" onclick="askDeleteAsset(${asset.id})">✕ Gỡ file</button>
                        </div>
                    </div>
                `;
                container.appendChild(div);
            });
        }

        function previewMediaFiles(input) {
            onAssetFilePicked(input);
        }

        function onAssetFilePicked(input) {
            if (!input.files || !input.files[0]) return;
            const fileName = input.files[0].name.toLowerCase();
            const kindSelect = document.getElementById('form-asset-kind');
            if (kindSelect) {
                if (/\.(png|jpe?g|gif|webp|svg)$/i.test(fileName)) {
                    kindSelect.value = 'image';
                } else if (/\.(mp3|wav|ogg|m4a|aac)$/i.test(fileName)) {
                    kindSelect.value = 'audio';
                } else if (/\.(mp4|webm|mov|avi)$/i.test(fileName)) {
                    kindSelect.value = 'video';
                } else {
                    kindSelect.value = 'document';
                }
            }
            showToast(`Đã chọn tệp: ${input.files[0].name} (Bấm Lưu Ctrl+S để tải lên)`, 'info');
        }

        /* 💾 REALTIME SAVE VIA AJAX */
        function triggerSaveQuestion() {
            const form = document.getElementById('studio-form');
            if (!form) return;

            const titleInput = document.getElementById('form-inp-title');
            if (!titleInput || !titleInput.value.trim()) {
                alert('Vui lòng nhập nội dung câu hỏi trước khi lưu!');
                titleInput?.focus();
                return;
            }

            const formData = new FormData(form);
            const isCreating = (document.getElementById('form-method').value === 'POST');
            const saveUrl = form.action;

            const btnTop = document.getElementById('btn-top-save');
            const btnBottom = document.getElementById('btn-bottom-save');
            if (btnTop) btnTop.disabled = true;
            if (btnBottom) btnBottom.disabled = true;

            showToast('Đang lưu câu hỏi vào CSDL...', 'success');

            fetch(saveUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (btnTop) btnTop.disabled = false;
                if (btnBottom) btnBottom.disabled = false;

                if (data.success && data.question) {
                    showToast('🎉 Đã lưu câu hỏi thành công!', 'success');

                    if (isCreating) {
                        window.location.href = `?grade={{ $selectedGrade }}&topic={{ $selectedTopic?->id }}&test={{ $selectedTest?->id }}&q=${data.question.id}`;
                    } else {
                        const previewEl = document.getElementById(`q-preview-text-${data.question.id}`);
                        if (previewEl) previewEl.textContent = data.question.title;
                        captureInitialSnapshot(data.question);
                    }
                } else {
                    alert('Lỗi: ' + (data.message || 'Không thể lưu câu hỏi.'));
                }
            })
            .catch(err => {
                if (btnTop) btnTop.disabled = false;
                if (btnBottom) btnBottom.disabled = false;
                form.submit();
            });
        }

        function toggleTopicNode(id) {
            const node = document.getElementById(id);
            if (!node) return;
            const isOpen = node.classList.contains('is-open');
            node.classList.toggle('is-open');
            const badge = node.querySelector('.tree-toggle-badge');
            if (badge) badge.textContent = isOpen ? '+' : '−';
        }

        function openAddTestForTopic(topicId, topicName) {
            const input = document.getElementById('modal-test-topic-id');
            const title = document.getElementById('modal-test-title');
            if (input) input.value = topicId;
            if (title) title.textContent = `＋ Thêm Bài luyện (${topicName})`;
            openModal('modal-add-test');
        }

        window.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                triggerSaveQuestion();
            }
        });
        /* 🔊 WEB AUDIO SYNTHESIZER FOR LIVE GAME SIMULATOR */
        let simAudioCtx = null;
        function playSimulatorSound(type) {
            try {
                if (!simAudioCtx) simAudioCtx = new (window.AudioContext || window.webkitAudioContext)();
                if (simAudioCtx.state === 'suspended') simAudioCtx.resume();
                const now = simAudioCtx.currentTime;

                if (type === 'click') {
                    const osc = simAudioCtx.createOscillator();
                    const gain = simAudioCtx.createGain();
                    osc.type = 'triangle';
                    osc.frequency.setValueAtTime(600, now);
                    osc.frequency.exponentialRampToValueAtTime(1100, now + 0.07);
                    gain.gain.setValueAtTime(0.75, now);
                    gain.gain.exponentialRampToValueAtTime(0.01, now + 0.07);
                    osc.connect(gain);
                    gain.connect(simAudioCtx.destination);
                    osc.start(now);
                    osc.stop(now + 0.07);
                } else if (type === 'victory') {
                    const notes = [523.25, 659.25, 783.99, 1046.5, 1318.5, 1567.98, 2093.0];
                    notes.forEach((freq, i) => {
                        const osc = simAudioCtx.createOscillator();
                        const gain = simAudioCtx.createGain();
                        osc.type = 'triangle';
                        osc.frequency.setValueAtTime(freq, now + i * 0.09);
                        gain.gain.setValueAtTime(0.85, now + i * 0.09);
                        gain.gain.exponentialRampToValueAtTime(0.01, now + i * 0.09 + 0.45);
                        osc.connect(gain);
                        gain.connect(simAudioCtx.destination);
                        osc.start(now + i * 0.09);
                        osc.stop(now + i * 0.09 + 0.45);
                    });
                } else if (type === 'fail') {
                    [260, 200, 160].forEach((freq, i) => {
                        const osc = simAudioCtx.createOscillator();
                        const gain = simAudioCtx.createGain();
                        osc.type = 'sawtooth';
                        osc.frequency.setValueAtTime(freq, now + i * 0.13);
                        gain.gain.setValueAtTime(0.75, now + i * 0.13);
                        gain.gain.exponentialRampToValueAtTime(0.01, now + i * 0.13 + 0.28);
                        osc.connect(gain);
                        gain.connect(simAudioCtx.destination);
                        osc.start(now + i * 0.13);
                        osc.stop(now + i * 0.13 + 0.28);
                    });
                }
            } catch (e) {}
        }

        /* 🎮 LIVE STUDENT SIMULATOR ENGINE (100% IC3 QUEST ARENA EXACT MATCH) */
        let simCorrectIndices = [];
        let simMatchingPairs = [];
        let simUserPairMatches = {}; // leftIdx => rightIdx
        let simSelectedLeft = null;
        let simMctSelections = {};
        let simMctCorrectMap = {};
        let simHotspotUserClick = null;
        let simSequenceItems = [];
        let simIsChecked = false;

        function getCurrentQuestionIndex() {
            const allNavCards = document.querySelectorAll('.q-nav-card:not(#q-card-new-slot)');
            const activeCard = document.querySelector('.q-nav-card.active');
            if (activeCard) {
                const idx = Array.from(allNavCards).indexOf(activeCard);
                if (idx >= 0) return idx;
            }
            const pos = parseInt(document.getElementById('form-inp-position')?.value);
            return (!isNaN(pos) && pos >= 0) ? pos : 0;
        }

        function renderSimulatorNavigation() {
            const allNavCards = document.querySelectorAll('.q-nav-card:not(#q-card-new-slot)');
            const totalQ = allNavCards.length || {{ $selectedTest && $selectedTest->questions ? $selectedTest->questions->count() : 14 }};
            const currentQIdx = getCurrentQuestionIndex();

            const cpRow = document.getElementById('sim-cp-row');
            if (cpRow) {
                cpRow.innerHTML = '';
                for (let i = 0; i < totalQ; i++) {
                    const node = document.createElement('div');
                    node.className = `sim-cp-node ${i === currentQIdx ? 'active' : ''}`;
                    node.textContent = i + 1;
                    node.style.cursor = 'pointer';
                    node.title = `Chuyển sang Câu #${i + 1}`;
                    node.onclick = () => {
                        if (i === getCurrentQuestionIndex()) return;
                        switchSimulatorToQuestionIndex(i);
                    };
                    cpRow.appendChild(node);
                }
            }
            const curNumEl = document.getElementById('sim-q-current-num');
            if (curNumEl) curNumEl.textContent = currentQIdx + 1;
            const totNumEl = document.getElementById('sim-q-total-num');
            if (totNumEl) totNumEl.textContent = totalQ;
        }

        function switchSimulatorToQuestionIndex(targetIndex) {
            const allNavCards = document.querySelectorAll('.q-nav-card:not(#q-card-new-slot)');
            const targetCard = allNavCards[targetIndex];
            if (!targetCard) return;

            const match = targetCard.id.match(/q-nav-item-(\d+)/);
            if (!match) return;
            const questionId = match[1];

            playSimulatorSound('click');

            // Cập nhật highlight active trên thanh điều hướng ngay lập tức
            const cpNodes = document.querySelectorAll('#sim-cp-row .sim-cp-node');
            cpNodes.forEach((node, idx) => {
                node.className = `sim-cp-node ${idx === targetIndex ? 'active' : ''}`;
            });
            const curNumEl = document.getElementById('sim-q-current-num');
            if (curNumEl) curNumEl.textContent = targetIndex + 1;

            // Nạp câu hỏi và render nội dung mới vào simulator hoàn toàn không đóng modal, không reload trang
            doLoadQuestionRealtime(questionId).then(() => {
                renderSimulatorContent();
            });
        }

        function openStudentSimulator() {
            playSimulatorSound('click');
            const currentQIdx = getCurrentQuestionIndex();
            const qNum = currentQIdx + 1;

            @if($selectedTest)
                const testSlug = "{{ $selectedTest->slug }}";
                const iframe = document.getElementById('sim-user-view-iframe');
                const targetPath = `/bai-luyen/${testSlug}/lam-bai?preview=1&q=${qNum}`;

                if (iframe) {
                    // Nếu iframe đã tải sẵn bộ đề này, chuyển câu trực tiếp trong bộ nhớ (0ms, không reload, không nhấp nháy!)
                    if (iframe.contentWindow && iframe.contentWindow.rawQuestions) {
                        try {
                            iframe.contentWindow.currentIndex = currentQIdx;
                            iframe.contentWindow.isReviewMode = false;
                            const fb = iframe.contentWindow.document.getElementById('feedback-banner');
                            if (fb) fb.className = 'feedback-banner';
                            iframe.contentWindow.renderQuestion();
                            openModal('modal-student-sim');
                            return;
                        } catch (e) {}
                    }

                    // Nếu chưa tải hoặc đổi bộ đề khác thì mới nạp URL
                    if (!iframe.src || !iframe.src.includes(`/bai-luyen/${testSlug}/lam-bai`)) {
                        iframe.src = targetPath;
                    }
                }
                openModal('modal-student-sim');
            @else
                showToast('Chưa chọn bài luyện để xem thử!', 'error');
            @endif
        }

        function openStudentViewInNewTab() {
            const iframe = document.getElementById('sim-user-view-iframe');
            if (iframe && iframe.src) {
                window.open(iframe.src, '_blank');
            }
        }

        function triggerIframeAutofill() {
            const iframe = document.getElementById('sim-user-view-iframe');
            try {
                iframe?.contentWindow?.postMessage({ action: 'admin-autofill' }, '*');
                if (iframe?.contentWindow?.adminAutofillAnswer) {
                    iframe.contentWindow.adminAutofillAnswer();
                }
            } catch (e) { console.error(e); }
        }

        function triggerIframeCheck() {
            const iframe = document.getElementById('sim-user-view-iframe');
            try {
                iframe?.contentWindow?.postMessage({ action: 'admin-check' }, '*');
                if (iframe?.contentWindow?.adminCheckAnswer) {
                    iframe.contentWindow.adminCheckAnswer();
                }
            } catch (e) { console.error(e); }
        }

        function triggerIframeReset() {
            const iframe = document.getElementById('sim-user-view-iframe');
            try {
                iframe?.contentWindow?.postMessage({ action: 'admin-reset' }, '*');
                if (iframe?.contentWindow?.adminResetAnswer) {
                    iframe.contentWindow.adminResetAnswer();
                }
            } catch (e) { console.error(e); }
        }

        window.addEventListener('message', (event) => {
            if (event.data?.action === 'close-sim-modal') {
                closeModal('modal-student-sim');
            }
        });

        function renderSimulatorContent() {
            simIsChecked = false;
            const promptText = document.getElementById('form-inp-title')?.value || '(Chưa nhập nội dung đề bài)';
            const type = document.getElementById('sel-type').value;

            document.getElementById('sim-prompt-text').textContent = promptText;

            // 2. Set Type Pill
            const typePill = document.getElementById('sim-badge-type');
            if (type === 'Hotspot') typePill.textContent = '🎯 CHỌN ĐIỂM CHẠM TRÊN ẢNH';
            else if (type === 'Matching') typePill.textContent = '🔗 GHÉP NỐI CÁC CẶP TƯƠNG ỨNG';
            else if (type === 'MultipleChoiceText') typePill.textContent = '📝 PHÂN LOẠI KHÁI NIỆM';
            else if (type === 'MultipleResponse') typePill.textContent = '🎯 CHỌN NHIỀU ĐÁP ÁN ĐÚNG';
            else if (type === 'Sequence') typePill.textContent = '🔢 SẮP XẾP THỨ TỰ (SEQUENCE)';
            else typePill.textContent = '🎯 NHIỆM VỤ TRẮC NGHIỆM';

            const container = document.getElementById('sim-dynamic-container');
            container.innerHTML = '';
            simCorrectIndices = [];
            simMatchingPairs = [];
            simUserPairMatches = {};
            simSelectedLeft = null;
            simMctSelections = {};
            simMctCorrectMap = {};
            simHotspotUserClick = null;
            simSequenceItems = [];

            // Render Prompt Media Image or Audio (Khối 3) inside Simulator if present
            const promptMediaImg = document.querySelector('#media-assets-gallery img');
            if (promptMediaImg && promptMediaImg.src && type !== 'Hotspot') {
                const imgBox = document.createElement('div');
                imgBox.style.cssText = 'width:100%; text-align:center; margin-bottom:16px; background:rgba(0,0,0,0.25); padding:10px; border-radius:12px; border:1px solid rgba(255,255,255,0.15);';
                imgBox.innerHTML = `<img src="${promptMediaImg.src}" style="max-height:220px; max-width:100%; object-fit:contain; border-radius:8px; box-shadow:0 4px 14px rgba(0,0,0,0.4);" alt="Minh họa đề bài">`;
                container.appendChild(imgBox);
            }
            const promptAudio = document.querySelector('#media-assets-gallery audio');
            if (promptAudio && promptAudio.src && type !== 'Hotspot') {
                const audBox = document.createElement('div');
                audBox.style.cssText = 'width:100%; text-align:center; margin-bottom:16px; background:rgba(0,0,0,0.25); padding:10px; border-radius:12px; border:1px solid rgba(255,255,255,0.15);';
                audBox.innerHTML = `<audio controls src="${promptAudio.src}" style="width:100%; max-width:450px;"></audio>`;
                container.appendChild(audBox);
            }

            if (type === 'Matching') {
                // ⚡ 100% Giao diện Nối Dây Laser Học sinh (Matching - launch.blade.php)
                const pairCards = document.querySelectorAll('.matching-pair-card');
                const leftList = [];
                const rightList = [];

                if (pairCards.length > 0) {
                    pairCards.forEach((card, idx) => {
                        const left = card.querySelector('input[name*="[left]"]')?.value || `Mục ${idx + 1}`;
                        const right = card.querySelector('input[name*="[right]"]')?.value || `Định nghĩa ${idx + 1}`;
                        leftList.push({ id: idx, text: left });
                        rightList.push({ id: idx, text: right });
                        simMatchingPairs.push({ left, right, idx });
                    });
                } else if (currentOptionsState && currentOptionsState.length) {
                    currentOptionsState.forEach((item, idx) => {
                        let leftVal = item.left || item.metadata?.left || item.content || `Mục ${idx + 1}`;
                        let rightVal = item.right || item.metadata?.right || `Định nghĩa ${idx + 1}`;
                        if (item.content && item.content.includes(':::')) {
                            const parts = item.content.split(':::');
                            leftVal = parts[0].trim();
                            rightVal = parts[1].trim();
                        }
                        leftList.push({ id: idx, text: leftVal });
                        rightList.push({ id: idx, text: rightVal });
                        simMatchingPairs.push({ left: leftVal, right: rightVal, idx });
                    });
                }

                // Shuffled right list
                const shuffledRight = [...rightList];
                if (shuffledRight.length > 1) {
                    const first = shuffledRight.shift();
                    shuffledRight.push(first);
                }

                const wireBoard = document.createElement('div');
                wireBoard.className = 'neon-wire-container';
                wireBoard.id = 'sim-wire-board';

                const leftCol = document.createElement('div');
                leftCol.className = 'wire-column wire-left-col';

                const rightCol = document.createElement('div');
                rightCol.className = 'wire-column wire-right-col';

                const svgCanvas = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                svgCanvas.setAttribute('class', 'wire-svg-canvas');
                svgCanvas.id = 'sim-wire-svg';

                const colors = ['#00f2fe', '#f43f5e', '#10b981', '#f59e0b', '#a855f7'];
                let activeSelection = null; // { side: 'left' | 'right', id: number }

                // 1. Render Left Cards
                leftList.forEach((leftItem, leftIdx) => {
                    const card = document.createElement('div');
                    card.className = 'wire-card wire-left-card';
                    card.dataset.leftId = leftIdx;

                    card.innerHTML = `
                        <div class="wire-idx-badge">${leftIdx + 1}</div>
                        <div style="flex:1;text-align:left;line-height:1.35;">${escapeHtml(leftItem.text)}</div>
                        <div class="wire-port"></div>
                    `;

                    card.onclick = () => {
                        playSimulatorSound('click');
                        if (activeSelection?.side === 'left' && activeSelection.id === leftIdx) {
                            activeSelection = null;
                        } else if (activeSelection?.side === 'right') {
                            const rightId = activeSelection.id;
                            Object.keys(simUserPairMatches).forEach(k => {
                                if (simUserPairMatches[k] === rightId) delete simUserPairMatches[k];
                            });
                            simUserPairMatches[leftIdx] = rightId;
                            activeSelection = null;
                        } else {
                            // If clicking already connected left, disconnect it
                            if (simUserPairMatches[leftIdx] !== undefined) {
                                delete simUserPairMatches[leftIdx];
                            }
                            activeSelection = { side: 'left', id: leftIdx };
                        }
                        updateWireSelectionUI();
                    };

                    leftCol.appendChild(card);
                });

                // 2. Render Right Cards
                const rightLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
                shuffledRight.forEach((rightItem, rIdx) => {
                    const card = document.createElement('div');
                    card.className = 'wire-card wire-right-card';
                    card.dataset.rightId = rightItem.id;

                    card.innerHTML = `
                        <div class="wire-port"></div>
                        <div class="wire-idx-badge">${rightLetters[rIdx % rightLetters.length]}</div>
                        <div style="flex:1;text-align:left;line-height:1.35;">${escapeHtml(rightItem.text)}</div>
                    `;

                    card.onclick = () => {
                        playSimulatorSound('click');
                        if (activeSelection?.side === 'right' && activeSelection.id === rightItem.id) {
                            activeSelection = null;
                        } else if (activeSelection?.side === 'left') {
                            const leftIdx = activeSelection.id;
                            Object.keys(simUserPairMatches).forEach(k => {
                                if (simUserPairMatches[k] === rightItem.id) delete simUserPairMatches[k];
                            });
                            simUserPairMatches[leftIdx] = rightItem.id;
                            activeSelection = null;
                        } else {
                            // If clicking already connected right, disconnect it
                            const connL = Object.keys(simUserPairMatches).find(lIdx => simUserPairMatches[lIdx] === rightItem.id);
                            if (connL !== undefined) {
                                delete simUserPairMatches[connL];
                            }
                            activeSelection = { side: 'right', id: rightItem.id };
                        }
                        updateWireSelectionUI();
                    };

                    rightCol.appendChild(card);
                });

                wireBoard.appendChild(leftCol);
                wireBoard.appendChild(svgCanvas);
                wireBoard.appendChild(rightCol);
                container.appendChild(wireBoard);

                window.updateSimWires = function() {
                    updateWireSelectionUI();
                };

                function updateWireSelectionUI() {
                    // Update Left cards
                    leftCol.querySelectorAll('.wire-left-card').forEach(c => {
                        const lId = +c.dataset.leftId;
                        c.classList.remove('selected', 'pair-theme-0', 'pair-theme-1', 'pair-theme-2', 'pair-theme-3', 'pair-theme-4');
                        if (activeSelection?.side === 'left' && activeSelection.id === lId) {
                            c.classList.add('selected');
                        }
                        const rId = simUserPairMatches[lId];
                        if (rId !== undefined && rId !== null) {
                            c.classList.add(`pair-theme-${lId % colors.length}`);
                        }
                    });

                    // Update Right cards
                    rightCol.querySelectorAll('.wire-right-card').forEach(c => {
                        const rId = +c.dataset.rightId;
                        c.classList.remove('selected', 'pair-theme-0', 'pair-theme-1', 'pair-theme-2', 'pair-theme-3', 'pair-theme-4');
                        if (activeSelection?.side === 'right' && activeSelection.id === rId) {
                            c.classList.add('selected');
                        }
                        const connL = Object.keys(simUserPairMatches).find(lIdx => simUserPairMatches[lIdx] === rId);
                        if (connL !== undefined) {
                            c.classList.add(`pair-theme-${connL % colors.length}`);
                        }
                    });

                    drawWires();
                }

                function drawWires() {
                    svgCanvas.innerHTML = '';
                    const boardRect = wireBoard.getBoundingClientRect();
                    if (boardRect.width === 0) return;

                    Object.keys(simUserPairMatches).forEach(lIdx => {
                        const rId = simUserPairMatches[lIdx];
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

                        // Wide Neon Glow background path
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

                        // Flowing Electric Animation dashes
                        const animPath = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                        animPath.setAttribute('d', d);
                        animPath.setAttribute('class', 'wire-laser-line');
                        animPath.setAttribute('stroke', color);
                        animPath.setAttribute('stroke-width', '4');
                        animPath.setAttribute('stroke-dasharray', '8 12');
                        svgCanvas.appendChild(animPath);
                    });

                    // 2. In Checked Mode: Draw green dashed guidelines to correct targets for any wrong/missing pairs (100% launch.blade.php)
                    if (simIsChecked) {
                        leftList.forEach((leftItem, lIdx) => {
                            const userConnectedRightId = simUserPairMatches[lIdx];
                            if (userConnectedRightId !== lIdx) {
                                // Missing or wrong -> draw green dashed guide line to actual correct right target
                                const leftEl = leftCol.querySelector(`[data-left-id="${lIdx}"] .wire-port`);
                                const rightEl = rightCol.querySelector(`[data-right-id="${lIdx}"] .wire-port`);

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
                                    guidePath.setAttribute('stroke-linecap', 'round');
                                    guidePath.style.filter = 'drop-shadow(0 0 8px rgba(34,197,94,0.85))';
                                    svgCanvas.appendChild(guidePath);
                                }
                            }
                        });
                    }
                }

                setTimeout(updateWireSelectionUI, 80);
                setTimeout(drawWires, 160);
                window.addEventListener('resize', drawWires, { passive: true });

            } else if (type === 'Sequence') {
                // 🔢 SẮP XẾP THỨ TỰ (SEQUENCE SIMULATOR)
                const stepCards = document.querySelectorAll('.sequence-step-card');
                const steps = [];
                if (stepCards.length > 0) {
                    stepCards.forEach((card, idx) => {
                        const txt = card.querySelector('input[name*="[content]"]')?.value || `Bước ${idx + 1}`;
                        steps.push({ correctIdx: idx, text: txt });
                    });
                } else if (currentOptionsState && currentOptionsState.length) {
                    currentOptionsState.forEach((item, idx) => {
                        steps.push({ correctIdx: idx, text: item.content || `Bước ${idx + 1}` });
                    });
                }

                simSequenceItems = [...steps].sort(() => Math.random() - 0.5);

                const seqWrap = document.createElement('div');
                seqWrap.style.cssText = 'display:flex; flex-direction:column; gap:10px; width:100%;';
                seqWrap.innerHTML = `
                    <div style="font-size:13.5px; font-weight:800; color:#ffe658; margin-bottom:4px; text-shadow:0 0 10px rgba(255,230,88,0.4);">
                        🔢 Hãy dùng các nút [▲ Lên] [▼ Xuống] để sắp xếp lại các bước theo đúng thứ tự:
                    </div>
                    <div id="sim-sequence-list" style="display:flex; flex-direction:column; gap:8px;"></div>
                `;
                container.appendChild(seqWrap);

                renderSimSequenceRows();

            } else if (type === 'Hotspot') {
                // 🎯 100% Giao diện Học sinh Hotspot Đích Thực (Đồng bộ launch.blade.php)
                simHotspotUserClick = null;
                let hotspotImg = '';
                for (const opt of currentOptionsState || []) {
                    if (opt.image_path) { hotspotImg = opt.image_path; break; }
                }
                if (!hotspotImg) {
                    const galleryImg = document.querySelector('#media-assets-gallery img');
                    if (galleryImg) hotspotImg = galleryImg.src;
                }

                const outer = document.createElement('div');
                outer.className = 'hotspot-outer-container';

                const hint = document.createElement('div');
                hint.className = 'hotspot-instruction-hint';
                hint.innerHTML = `<span>🎯</span> <i>Hãy nhấp chuột trực tiếp vào biểu tượng hoặc vị trí trên hình ảnh để chọn!</i>`;
                outer.appendChild(hint);

                const wrapper = document.createElement('div');
                wrapper.className = 'hotspot-wrapper';
                wrapper.id = 'sim-hotspot-wrapper';
                wrapper.innerHTML = `<img id="sim-hotspot-img" src="${hotspotImg}" alt="Hotspot">`;

                wrapper.onclick = (e) => {
                    playSimulatorSound('click');
                    const rect = wrapper.getBoundingClientRect();
                    const ptX = ((e.clientX - rect.left) / rect.width) * 10000;
                    const ptY = ((e.clientY - rect.top) / rect.height) * 10000;

                    let matchIdx = -1;
                    (currentOptionsState || []).forEach((area, idx) => {
                        if (area.rect) {
                            const rx = parseFloat(area.rect.x || 0);
                            const ry = parseFloat(area.rect.y || 0);
                            const rw = parseFloat(area.rect.w || 0);
                            const rh = parseFloat(area.rect.h || 0);
                            if (ptX >= rx && ptX <= (rx + rw) && ptY >= ry && ptY <= (ry + rh)) {
                                matchIdx = idx;
                            }
                        }
                    });

                    if (matchIdx === -1 && (currentOptionsState || []).length > 0) {
                        let minDist = Infinity;
                        (currentOptionsState || []).forEach((area, idx) => {
                            if (area.rect) {
                                const rx = parseFloat(area.rect.x || 0);
                                const ry = parseFloat(area.rect.y || 0);
                                const rw = parseFloat(area.rect.w || 0);
                                const rh = parseFloat(area.rect.h || 0);
                                const centerX = rx + rw / 2;
                                const centerY = ry + rh / 2;
                                const dist = Math.hypot(ptX - centerX, ptY - centerY);
                                if (dist < minDist) {
                                    minDist = dist;
                                    matchIdx = idx;
                                }
                            }
                        });
                    }

                    if (matchIdx !== -1) {
                        const targetArea = currentOptionsState[matchIdx];
                        const rx = parseFloat(targetArea.rect.x || 0);
                        const ry = parseFloat(targetArea.rect.y || 0);
                        const rw = parseFloat(targetArea.rect.w || 0);
                        const rh = parseFloat(targetArea.rect.h || 0);
                        const centerX = (rx + rw / 2) / 100;
                        const centerY = (ry + rh / 2) / 100;

                        let marker = wrapper.querySelector('.hotspot-target-marker');
                        if (!marker) {
                            marker = document.createElement('div');
                            marker.className = 'hotspot-target-marker';
                            wrapper.appendChild(marker);
                        }
                        marker.style.left = centerX + '%';
                        marker.style.top = centerY + '%';
                        marker.style.display = 'grid';

                        // Remove old review highlights on new click
                        wrapper.querySelectorAll('.hotspot-review-zone').forEach(el => el.remove());

                        simHotspotUserClick = {
                            idx: matchIdx,
                            isCorrect: !!targetArea.is_correct,
                            area: targetArea
                        };
                    }
                };

                outer.appendChild(wrapper);
                container.appendChild(outer);

            } else if (type === 'MultipleChoiceText') {
                // 📋 100% Giao diện Học sinh Phân loại Đích Thực (launch.blade.php)
                const candidateChoices = new Set();
                currentOptionsState.forEach(o => {
                    if (o.right && o.right.trim()) candidateChoices.add(o.right.trim());
                    if (Array.isArray(o.available_options)) o.available_options.forEach(v => candidateChoices.add(v.trim()));
                });
                const distinctChoices = Array.from(candidateChoices);

                const classifyList = document.createElement('div');
                classifyList.className = 'classify-list';

                currentOptionsState.forEach((item, idx) => {
                    let leftVal = item.left || item.content || `Mục ${idx + 1}`;
                    let rightVal = item.right || '';
                    if (item.content && item.content.includes(':::')) {
                        const p = item.content.split(':::');
                        leftVal = p[0].trim();
                        rightVal = p[1].trim();
                    }

                    simMctCorrectMap[idx] = rightVal.trim();
                    const itemChoices = (item.available_options && item.available_options.length) ? item.available_options : distinctChoices;

                    const row = document.createElement('div');
                    row.className = `classify-item row-theme-${idx % 5}`;
                    row.id = `sim-mct-row-${idx}`;

                    const labelDiv = document.createElement('div');
                    labelDiv.className = 'classify-label';
                    labelDiv.innerHTML = `
                        <span class="classify-idx-badge">${idx + 1}</span>
                        <span>${escapeHtml(leftVal)}</span>
                    `;

                    const btnGroup = document.createElement('div');
                    btnGroup.className = 'classify-buttons-group';

                    itemChoices.forEach((choice, choiceIdx) => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = `classify-btn theme-${choiceIdx % 4} sim-mct-btn-${idx}`;
                        btn.setAttribute('data-choice', choice);
                        btn.textContent = choice;
                        btn.onclick = () => {
                            playSimulatorSound('click');
                            btnGroup.querySelectorAll('.classify-btn').forEach(b => b.classList.remove('selected'));
                            btn.classList.add('selected');
                            simMctSelections[idx] = choice.trim();
                        };
                        btnGroup.appendChild(btn);
                    });

                    row.appendChild(labelDiv);
                    row.appendChild(btnGroup);
                    classifyList.appendChild(row);
                });

                container.appendChild(classifyList);

            } else {
                // Choice Simulator Grid (MultipleChoice / MultipleResponse)
                const grid = document.createElement('div');
                grid.className = 'sim-arena-choices-grid';

                const optionCards = document.querySelectorAll('.option-row-card');
                const choices = [];

                if (optionCards.length > 0) {
                    optionCards.forEach((card, idx) => {
                        const letter = lettersList[idx % lettersList.length];
                        const content = card.querySelector('.opt-inp-txt')?.value || `(Lựa chọn ${letter})`;
                        const imgPath = card.querySelector('.opt-thumb-preview')?.src || '';
                        const isCorrect = card.querySelector('.opt-inp-chk')?.checked || false;
                        choices.push({ letter, content, imgPath, isCorrect, idx });
                    });
                } else if (currentOptionsState && currentOptionsState.length > 0) {
                    currentOptionsState.forEach((opt, idx) => {
                        const letter = lettersList[idx % lettersList.length];
                        choices.push({
                            letter,
                            content: opt.content || `(Lựa chọn ${letter})`,
                            imgPath: opt.image_path || '',
                            isCorrect: !!opt.is_correct,
                            idx
                        });
                    });
                }

                choices.forEach((c) => {
                    if (c.isCorrect) simCorrectIndices.push(c.idx);

                    const btn = document.createElement('div');
                    btn.className = 'sim-arena-choice-btn';
                    btn.setAttribute('data-sim-idx', c.idx);
                    btn.onclick = () => toggleSimSelect(btn);

                    let imgHtml = '';
                    if (c.imgPath) {
                        imgHtml = `<img src="${c.imgPath}" class="sim-choice-img" alt="Đáp án ${c.letter}">`;
                    }

                    const hasRealText = c.content && !c.content.startsWith('(Lựa chọn ');
                    const textHtml = hasRealText ? `<span class="sim-choice-text">${escapeHtml(c.content)}</span>` : '';

                    btn.innerHTML = `
                        <div class="sim-choice-key">${c.letter}</div>
                        <div class="sim-choice-content">
                            ${imgHtml}
                            ${textHtml}
                        </div>
                    `;
                    grid.appendChild(btn);
                });
                container.appendChild(grid);
            }

            // Clear previous feedback
            const fb = document.getElementById('sim-feedback-box');
            if (fb) {
                fb.style.display = 'none';
                fb.className = 'sim-feedback-banner';
            }
        }

        function renderSimSequenceRows() {
            const listEl = document.getElementById('sim-sequence-list');
            if (!listEl) return;
            listEl.innerHTML = '';

            simSequenceItems.forEach((item, curIdx) => {
                const row = document.createElement('div');
                row.style.cssText = 'display:flex; align-items:center; justify-content:space-between; gap:12px; padding:12px 18px; background:rgba(255,255,255,0.08); border:1.5px solid rgba(0,242,254,0.35); border-radius:12px; transition:0.2s;';
                row.innerHTML = `
                    <div style="display:flex; align-items:center; gap:12px;">
                        <span style="width:28px; height:28px; border-radius:8px; background:linear-gradient(135deg, #00f2fe, #0284c7); color:#061021; font-weight:900; font-size:13px; display:grid; place-items:center; box-shadow:0 2px 6px rgba(0,242,254,0.4);">
                            ${curIdx + 1}
                        </span>
                        <span style="font-size:14.5px; font-weight:800; color:#ffffff;">
                            ${escapeHtml(item.text)}
                        </span>
                    </div>
                    <div style="display:flex; gap:6px;">
                        <button type="button" onclick="moveSimSequenceStep(${curIdx}, -1)" style="padding:6px 12px; border-radius:8px; border:1px solid rgba(255,255,255,0.25); background:rgba(255,255,255,0.12); color:#fff; font-weight:900; cursor:pointer; font-size:12px;" ${curIdx === 0 ? 'disabled style="opacity:0.3; cursor:not-allowed;"' : ''}>
                            ▲ Lên
                        </button>
                        <button type="button" onclick="moveSimSequenceStep(${curIdx}, 1)" style="padding:6px 12px; border-radius:8px; border:1px solid rgba(255,255,255,0.25); background:rgba(255,255,255,0.12); color:#fff; font-weight:900; cursor:pointer; font-size:12px;" ${curIdx === simSequenceItems.length - 1 ? 'disabled style="opacity:0.3; cursor:not-allowed;"' : ''}>
                            ▼ Xuống
                        </button>
                    </div>
                `;
                listEl.appendChild(row);
            });
        }

        function moveSimSequenceStep(index, direction) {
            playSimulatorSound('click');
            const targetIdx = index + direction;
            if (targetIdx < 0 || targetIdx >= simSequenceItems.length) return;
            const temp = simSequenceItems[index];
            simSequenceItems[index] = simSequenceItems[targetIdx];
            simSequenceItems[targetIdx] = temp;
            renderSimSequenceRows();
        }

        // 💡 AUTO-FILL ALL CORRECT ANSWERS FOR 1-CLICK TEACHER PREVIEW
        function autoFillSimulatorCorrectAnswer() {
            playSimulatorSound('victory');
            const type = document.getElementById('sel-type').value;

            if (type === 'Matching') {
                simUserPairMatches = {};
                simMatchingPairs.forEach(p => {
                    simUserPairMatches[p.idx] = p.idx;
                });
                if (window.updateSimWires) window.updateSimWires();
                showToast('💡 Đã tự động nối sẵn toàn bộ dây laser đáp án đúng!', 'success');
            } else if (type === 'MultipleChoiceText') {
                Object.keys(simMctCorrectMap).forEach(idx => {
                    const expected = simMctCorrectMap[idx];
                    simMctSelections[idx] = expected;
                    const row = document.getElementById(`sim-mct-row-${idx}`);
                    if (row) {
                        row.querySelectorAll('.classify-btn').forEach(btn => {
                            btn.classList.remove('selected');
                            if (btn.getAttribute('data-choice') === expected) {
                                btn.classList.add('selected');
                            }
                        });
                    }
                });
                showToast('💡 Đã tự động chọn đúng tất cả mục phân loại!', 'success');
            } else if (type === 'Sequence') {
                simSequenceItems.sort((a, b) => a.correctIdx - b.correctIdx);
                renderSimSequenceRows();
                showToast('💡 Đã tự động sắp xếp lại theo đúng thứ tự chuẩn!', 'success');
            } else if (type === 'Hotspot') {
                const wrapper = document.getElementById('sim-hotspot-wrapper');
                let correctIdx = (currentOptionsState || []).findIndex(a => a.is_correct && a.rect);
                if (correctIdx !== -1 && wrapper) {
                    const correctArea = currentOptionsState[correctIdx];
                    const rx = parseFloat(correctArea.rect.x || 0);
                    const ry = parseFloat(correctArea.rect.y || 0);
                    const rw = parseFloat(correctArea.rect.w || 0);
                    const rh = parseFloat(correctArea.rect.h || 0);
                    const centerX = (rx + rw / 2) / 100;
                    const centerY = (ry + rh / 2) / 100;

                    let marker = wrapper.querySelector('.hotspot-target-marker');
                    if (!marker) {
                        marker = document.createElement('div');
                        marker.className = 'hotspot-target-marker';
                        wrapper.appendChild(marker);
                    }
                    marker.style.left = centerX + '%';
                    marker.style.top = centerY + '%';
                    marker.style.display = 'grid';

                    simHotspotUserClick = {
                        idx: correctIdx,
                        isCorrect: true,
                        area: correctArea
                    };
                    showToast('💡 Đã tự động chọn đúng vị trí Hotspot!', 'success');
                }
            } else {
                document.querySelectorAll('.sim-arena-choice-btn').forEach(btn => {
                    const idx = parseInt(btn.getAttribute('data-sim-idx'));
                    if (simCorrectIndices.includes(idx)) {
                        btn.classList.add('selected');
                    } else {
                        btn.classList.remove('selected');
                    }
                });
                showToast('💡 Đã tự động đánh dấu các đáp án đúng!', 'success');
            }
        }

        function toggleSimSelect(element) {
            playSimulatorSound('click');
            const type = document.getElementById('sel-type').value;
            const isSingle = (type === 'MultipleChoice');

            if (isSingle) {
                document.querySelectorAll('.sim-arena-choice-btn').forEach(el => el.classList.remove('selected'));
                element.classList.add('selected');
            } else {
                element.classList.toggle('selected');
            }
        }

        function checkSimulatorAnswer() {
            const type = document.getElementById('sel-type').value;
            const fb = document.getElementById('sim-feedback-box');

            if (type === 'Matching') {
                simIsChecked = true;
                if (window.updateSimWires) window.updateSimWires();

                const totalPairs = simMatchingPairs.length;
                let correctCount = 0;

                simMatchingPairs.forEach(p => {
                    if (simUserPairMatches[p.idx] === p.idx) {
                        correctCount++;
                    }
                });

                fb.style.display = 'block';
                if (correctCount === totalPairs && totalPairs > 0) {
                    playSimulatorSound('victory');
                    fb.style.background = 'rgba(34, 197, 94, 0.25)';
                    fb.style.color = '#86efac';
                    fb.style.border = '1.5px solid #22c55e';
                    fb.textContent = `🎉 CHÍNH XÁC! Bạn đã ghép đúng toàn bộ ${totalPairs}/${totalPairs} cặp tương ứng!`;
                } else {
                    playSimulatorSound('fail');
                    fb.style.background = 'rgba(239, 68, 68, 0.25)';
                    fb.style.color = '#fca5a5';
                    fb.style.border = '1.5px solid #ef4444';
                    fb.textContent = `❌ Bạn đã ghép đúng ${correctCount}/${totalPairs} cặp. Đường nét đứt màu xanh lá cây đã hiển thị vị trí ghép đúng!`;
                }
                return;
            }

            if (type === 'Sequence') {
                const totalSteps = simSequenceItems.length;
                let isCorrect = true;
                simSequenceItems.forEach((item, idx) => {
                    if (item.correctIdx !== idx) {
                        isCorrect = false;
                    }
                });

                fb.style.display = 'block';
                if (isCorrect && totalSteps > 0) {
                    playSimulatorSound('victory');
                    fb.style.background = 'rgba(34, 197, 94, 0.25)';
                    fb.style.color = '#86efac';
                    fb.style.border = '1.5px solid #22c55e';
                    fb.textContent = `🎉 CHÍNH XÁC! Bạn đã sắp xếp đúng toàn bộ ${totalSteps} bước theo trình tự chuẩn!`;
                } else {
                    playSimulatorSound('fail');
                    fb.style.background = 'rgba(239, 68, 68, 0.25)';
                    fb.style.color = '#fca5a5';
                    fb.style.border = '1.5px solid #ef4444';
                    fb.textContent = `❌ Thứ tự các bước chưa chính xác. Hãy dùng các nút ▲ Lên / ▼ Xuống để điều chỉnh lại!`;
                }
                return;
            }

            if (type === 'MultipleChoiceText') {
                const totalItems = Object.keys(simMctCorrectMap).length;
                let correctCount = 0;

                Object.keys(simMctCorrectMap).forEach(idx => {
                    const expected = simMctCorrectMap[idx];
                    const selected = simMctSelections[idx];
                    const row = document.getElementById(`sim-mct-row-${idx}`);

                    if (row) {
                        row.querySelectorAll('.classify-btn').forEach(btn => {
                            const choice = btn.getAttribute('data-choice');
                            btn.classList.remove('selected');
                            if (choice === expected) {
                                btn.classList.add('correct');
                            } else if (choice === selected && selected !== expected) {
                                btn.classList.add('wrong');
                            }
                        });
                    }

                    if (selected === expected) {
                        correctCount++;
                    }
                });

                fb.style.display = 'block';
                if (correctCount === totalItems && totalItems > 0) {
                    playSimulatorSound('victory');
                    fb.style.background = 'rgba(34, 197, 94, 0.25)';
                    fb.style.color = '#86efac';
                    fb.style.border = '1.5px solid #22c55e';
                    fb.textContent = `🎉 CHÍNH XÁC! Bạn đã chọn đúng toàn bộ ${totalItems}/${totalItems} mục phân loại!`;
                } else {
                    playSimulatorSound('fail');
                    fb.style.background = 'rgba(239, 68, 68, 0.25)';
                    fb.style.color = '#fca5a5';
                    fb.style.border = '1.5px solid #ef4444';
                    fb.textContent = `❌ Bạn đã chọn đúng ${correctCount}/${totalItems} mục. Hệ thống đã hiển thị đáp án đúng (Màu xanh) cho từng mục.`;
                }
                return;
            }

            if (type === 'Hotspot') {
                const wrapper = document.getElementById('sim-hotspot-wrapper');
                fb.style.display = 'block';

                if (!simHotspotUserClick) {
                    playSimulatorSound('fail');
                    fb.style.background = 'rgba(245, 158, 11, 0.25)';
                    fb.style.color = '#fde68a';
                    fb.style.border = '1.5px solid #f59e0b';
                    fb.textContent = '⚠️ Hãy nhấp chuột vào một vị trí trên hình để kiểm tra!';
                    return;
                }

                if (wrapper) {
                    wrapper.querySelectorAll('.hotspot-review-zone').forEach(el => el.remove());

                    (currentOptionsState || []).forEach((area, idx) => {
                        if (!area.rect) return;
                        const rx = parseFloat(area.rect.x || 0);
                        const ry = parseFloat(area.rect.y || 0);
                        const rw = parseFloat(area.rect.w || 0);
                        const rh = parseFloat(area.rect.h || 0);

                        if (area.is_correct) {
                            const correctEl = document.createElement('div');
                            correctEl.className = 'hotspot-review-zone correct-zone';
                            correctEl.style.left = (rx / 100) + '%';
                            correctEl.style.top = (ry / 100) + '%';
                            correctEl.style.width = (rw / 100) + '%';
                            correctEl.style.height = (rh / 100) + '%';
                            correctEl.innerHTML = `<span style="background:#16a34a;color:#fff;font-size:11px;font-weight:900;padding:2px 6px;border-radius:4px;box-shadow:0 2px 6px rgba(0,0,0,0.3);">✓ Đáp án đúng</span>`;
                            wrapper.appendChild(correctEl);
                        } else if (simHotspotUserClick && simHotspotUserClick.idx === idx) {
                            const wrongEl = document.createElement('div');
                            wrongEl.className = 'hotspot-review-zone wrong-zone';
                            wrongEl.style.left = (rx / 100) + '%';
                            wrongEl.style.top = (ry / 100) + '%';
                            wrongEl.style.width = (rw / 100) + '%';
                            wrongEl.style.height = (rh / 100) + '%';
                            wrongEl.innerHTML = `<span style="background:#dc2626;color:#fff;font-size:11px;font-weight:900;padding:2px 6px;border-radius:4px;box-shadow:0 2px 6px rgba(0,0,0,0.3);">✗ Bạn đã chọn</span>`;
                            wrapper.appendChild(wrongEl);
                        }
                    });
                }

                if (simHotspotUserClick.isCorrect) {
                    playSimulatorSound('victory');
                    fb.style.background = 'rgba(34, 197, 94, 0.25)';
                    fb.style.color = '#86efac';
                    fb.style.border = '1.5px solid #22c55e';
                    fb.textContent = '🎉 CHÍNH XÁC! Bạn đã nhấp đúng vào vị trí đáp án của câu hỏi!';
                } else {
                    playSimulatorSound('fail');
                    fb.style.background = 'rgba(239, 68, 68, 0.25)';
                    fb.style.color = '#fca5a5';
                    fb.style.border = '1.5px solid #ef4444';
                    fb.textContent = '❌ Vị trí bạn nhấp chưa chính xác. Khung màu xanh lá cây đã hiển thị vị trí đáp án đúng!';
                }
                return;
            }
            const selectedItems = document.querySelectorAll('.sim-arena-choice-btn.selected');
            const selectedIndices = Array.from(selectedItems).map(el => parseInt(el.getAttribute('data-sim-idx')));

            if (selectedIndices.length === 0) {
                playSimulatorSound('fail');
                fb.style.display = 'block';
                fb.style.background = 'rgba(245, 158, 11, 0.25)';
                fb.style.color = '#fde68a';
                fb.style.border = '1.5px solid #f59e0b';
                fb.textContent = '⚠️ Hãy bấm chọn 1 đáp án để kiểm tra kết quả!';
                return;
            }

            document.querySelectorAll('.sim-arena-choice-btn').forEach((el, idx) => {
                const isItemCorrect = simCorrectIndices.includes(idx);
                const isItemSelected = selectedIndices.includes(idx);

                el.classList.remove('correct-reveal', 'wrong-reveal');
                if (isItemCorrect) el.classList.add('correct-reveal');
                else if (isItemSelected && !isItemCorrect) el.classList.add('wrong-reveal');
            });

            const isAllCorrect = simCorrectIndices.length === selectedIndices.length &&
                simCorrectIndices.every(val => selectedIndices.includes(val));

            fb.style.display = 'block';
            if (isAllCorrect) {
                playSimulatorSound('victory');
                fb.style.background = 'rgba(34, 197, 94, 0.25)';
                fb.style.color = '#86efac';
                fb.style.border = '1.5px solid #22c55e';
                fb.textContent = '🎉 CHÍNH XÁC! Bạn đã trả lời đúng câu hỏi này theo cấu hình đáp án.';
            } else {
                playSimulatorSound('fail');
                fb.style.background = 'rgba(239, 68, 68, 0.25)';
                fb.style.color = '#fca5a5';
                fb.style.border = '1.5px solid #ef4444';
                fb.textContent = '❌ CHƯA CHÍNH XÁC! Hệ thống đã hiển thị đáp án đúng (Màu xanh) cho bạn đối chiếu.';
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const currentType = document.getElementById('sel-type')?.value || 'MultipleChoice';
            renderOptionsByType(currentType, currentOptionsState);

            const form = document.getElementById('studio-form');
            if (form) {
                form.addEventListener('input', checkFormDirty);
                form.addEventListener('change', checkFormDirty);
            }

            const initialQData = {!! $selectedQuestion ? json_encode([
                'id' => $selectedQuestion->id,
                'title' => $selectedQuestion->title,
                'type' => $selectedQuestion->type,
                'points' => $selectedQuestion->points ?? 1,
                'position' => $selectedQuestion->position ?? 0,
                'is_published' => (bool)$selectedQuestion->is_published,
                'parsed_options' => $selectedQuestion->parsed_options,
                'assets' => $selectedQuestion->assets
            ]) : 'null' !!};
            captureInitialSnapshot(initialQData);
        });
    </script>
</body>
</html>
