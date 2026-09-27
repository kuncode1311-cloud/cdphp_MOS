<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $mode === 'create' ? 'Soạn Bộ Đề Thi Thử Mới' : 'Biên Tập: ' . $mockTest->name }} — MOS Studio</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --primary-light: #eef2ff;
            --bg-body: #edf2f7;
            --surface: #ffffff;
            --border: #e2e8f0;
            --border-strong: #cbd5e1;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --success: #10b981;
            --success-dark: #059669;
            --success-light: #f0fdf4;
            --warning: #f59e0b;
            --danger: #ef4444;
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            /* Nền đa sắc nhẹ nhàng giúp các card màu trắng nổi khối rõ rệt, không bị tệp màu */
            background: #edf2f7;
            background: radial-gradient(circle at 10% 12%, rgba(224, 231, 255, 0.6) 0%, transparent 45%),
                        radial-gradient(circle at 90% 88%, rgba(254, 240, 138, 0.35) 0%, transparent 40%),
                        #edf2f7;
            background-attachment: fixed;
            color: var(--text-main);
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            -webkit-font-smoothing: antialiased;
            min-height: 100vh;
        }

        .shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 270px minmax(0, 1fr);
            transition: grid-template-columns 0.22s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .shell.sidebar-collapsed {
            grid-template-columns: 76px minmax(0, 1fr);
        }

        .main-workspace {
            display: flex;
            flex-direction: column;
            min-width: 0;
            min-height: 100vh;
        }
        .main-content {
            padding: 16px 24px 44px;
            flex: 1;
            min-width: 0;
            max-width: 1580px;
            margin: 0 auto;
            width: 100%;
        }

        /* ==========================================================================
           1. SUB-HEADER BAR (TINH GỌN, NỔI BẬT KHỐI 3D)
           ========================================================================== */
        .sub-header-bar {
            background: #ffffff;
            border-radius: var(--radius-lg);
            border: 2px solid #ffffff;
            padding: 10px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 16px;
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.06);
            flex-wrap: wrap;
        }
        .sub-header-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .btn-sub-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border-radius: 9px;
            background: #f1f5f9;
            border: 1.5px solid #cbd5e1;
            color: #334155;
            font-size: 12.5px;
            font-weight: 800;
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .btn-sub-back:hover {
            background: #e2e8f0;
            color: #0f172a;
            transform: translateX(-2px);
        }
        .sub-badge-grade {
            font-size: 12px;
            font-weight: 900;
            padding: 4px 12px;
            border-radius: 8px;
            background: #e0e7ff;
            color: #3730a3;
            border: 1.5px solid #c7d2fe;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .sub-header-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .counter-pill {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            padding: 5px 14px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 800;
            color: #475569;
        }
        .counter-pill b {
            font-size: 16px;
            font-weight: 900;
            color: #2563eb;
        }
        .counter-bar-track {
            width: 90px;
            height: 8px;
            background: #e2e8f0;
            border-radius: 999px;
            overflow: hidden;
        }
        .counter-bar-fill {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, #f59e0b, #10b981);
            border-radius: 999px;
            transition: width 0.25s ease;
        }
        .btn-top-save-clean {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 18px;
            border-radius: 10px;
            background: linear-gradient(135deg, #10b981, #059669);
            color: #ffffff;
            font-weight: 900;
            font-size: 13px;
            border: 2px solid #ffffff;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35), inset 0 -2px 0 rgba(0,0,0,0.15);
            transition: all 0.15s ease;
        }
        .btn-top-save-clean:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.45);
        }
        .btn-top-save-clean:active {
            transform: translateY(1px);
        }

        /* ==========================================================================
           2. 2-COLUMN STUDIO GRID
           ========================================================================== */
        .studio-grid {
            display: grid;
            grid-template-columns: 330px minmax(0, 1fr);
            gap: 18px;
            align-items: start;
        }
        @media (max-width: 1100px) {
            .studio-grid { grid-template-columns: 1fr; }
        }

        /* ==========================================================================
           3. CỘT TRÁI: SIDEBAR CẤU HÌNH TỐI GIẢN & SẮC NÉT
           ========================================================================== */
        .left-col-sticky {
            position: sticky;
            top: 16px;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        @media (max-width: 1100px) {
            .left-col-sticky { position: static; }
        }

        .config-card-clean {
            background: #ffffff;
            border-radius: var(--radius-lg);
            border: 2px solid #ffffff;
            border-top: 4.5px solid #4f46e5;
            padding: 18px;
            display: flex;
            flex-direction: column;
            gap: 14px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        }
        .config-card-title {
            font-size: 14.5px;
            font-weight: 900;
            color: #0f172a;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 8px;
            border-bottom: 1.5px solid #f1f5f9;
        }
        .config-card-title small {
            font-size: 11px;
            font-weight: 800;
            color: #4f46e5;
            background: #eef2ff;
            padding: 2px 8px;
            border-radius: 999px;
            border: 1px solid #c7d2fe;
        }

        .clean-field {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .clean-field label {
            font-size: 12px;
            font-weight: 800;
            color: #334155;
        }
        .clean-input {
            width: 100%;
            padding: 8px 12px;
            border-radius: 8px;
            border: 1.5px solid #cbd5e1;
            font-family: inherit;
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
            background: #ffffff;
            transition: all 0.15s ease;
        }
        .clean-input:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
        }

        /* Bộ thông số 2 hàng cân đối */
        .params-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        /* Checkbox Switch tinh tế */
        .switches-group-clean {
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding: 10px 12px;
            background: #f8fafc;
            border-radius: 10px;
            border: 1.5px solid #e2e8f0;
        }
        .switch-clean-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            font-weight: 800;
            color: #334155;
            cursor: pointer;
            user-select: none;
        }
        .switch-clean-label input {
            width: 16px;
            height: 16px;
            accent-color: #4f46e5;
            cursor: pointer;
        }

        /* Bảng Phân Bổ Chủ Đề (Thanh lịch, không vỡ chữ) */
        .matrix-clean-section {
            display: flex;
            flex-direction: column;
            gap: 6px;
            border-top: 1.5px solid #f1f5f9;
            padding-top: 10px;
        }
        .matrix-clean-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 11.5px;
            font-weight: 900;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .matrix-clean-header small {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: none;
        }
        .topic-clean-list {
            display: flex;
            flex-direction: column;
            gap: 4px;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            padding: 6px;
        }
        .topic-clean-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 6px 9px;
            border-radius: 7px;
            cursor: pointer;
            transition: all 0.12s ease;
            gap: 8px;
        }
        .topic-clean-row:hover {
            background: #e2e8f0;
        }
        .topic-clean-row.is-selected {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
        }
        .topic-row-left {
            display: flex;
            align-items: center;
            gap: 7px;
            min-width: 0;
            flex: 1;
        }
        .matrix-topic-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #cbd5e1;
            flex-shrink: 0;
        }
        .matrix-topic-dot.active {
            background: #10b981;
            box-shadow: 0 0 5px #10b981;
        }
        .topic-row-name {
            font-size: 12px;
            font-weight: 750;
            color: #334155;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .topic-clean-row.is-selected .topic-row-name {
            color: #1e40af;
            font-weight: 850;
        }
        .matrix-topic-badge {
            font-size: 11px;
            font-weight: 850;
            padding: 1px 7px;
            border-radius: 999px;
            background: #e2e8f0;
            color: #64748b;
            flex-shrink: 0;
        }
        .matrix-topic-badge.active {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
        }

        .btn-submit-main {
            width: 100%;
            padding: 11px;
            border-radius: 12px;
            background: linear-gradient(135deg, #10b981, #059669);
            color: #ffffff;
            font-weight: 900;
            font-size: 14px;
            border: 2px solid #ffffff;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35), inset 0 -2px 0 rgba(0,0,0,0.15);
            transition: all 0.15s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .btn-submit-main:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(16, 185, 129, 0.45);
        }
        .btn-submit-main:active {
            transform: translateY(1px);
        }

        /* ==========================================================================
           4. CỘT PHẢI: SMART 1-ROW TOOLBAR & QUESTION STREAM RỰC RỠ
           ========================================================================== */
        .right-col {
            display: flex;
            flex-direction: column;
            gap: 12px;
            min-width: 0;
        }

        /* Smart Action Bar (1 HÀNG DUY NHẤT: Gọn gàng, sạch sẽ, không nút thừa) */
        .smart-action-bar {
            background: #ffffff;
            border-radius: var(--radius-lg);
            border: 2px solid #ffffff;
            padding: 10px 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05);
            flex-wrap: wrap;
        }

        .search-field-wrap {
            position: relative;
            flex: 1;
            min-width: 220px;
        }
        .search-field-wrap input {
            width: 100%;
            padding: 8px 12px 8px 34px;
            border-radius: 9px;
            border: 1.5px solid #cbd5e1;
            font-size: 12.5px;
            font-weight: 700;
            color: #0f172a;
            outline: none;
            background: #f8fafc;
            transition: all 0.15s ease;
        }
        .search-field-wrap input:focus {
            background: #ffffff;
            border-color: #4f46e5;
            box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.12);
        }
        .search-icon-fixed {
            position: absolute;
            left: 11px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 14px;
            color: #94a3b8;
            pointer-events: none;
        }

        /* 📁 Dropdown Select Bộ Lọc Chủ Đề Siêu Gọn */
        .topic-select-wrap {
            position: relative;
        }
        .clean-select-filter {
            padding: 8px 12px;
            border-radius: 9px;
            border: 1.5px solid #cbd5e1;
            font-size: 12.5px;
            font-weight: 800;
            color: #1e293b;
            background: #f8fafc;
            cursor: pointer;
            outline: none;
            transition: all 0.15s;
            max-width: 240px;
        }
        .clean-select-filter:focus {
            border-color: #4f46e5;
            background: #ffffff;
            box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.12);
        }

        .bar-actions-group {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }
        .btn-action-clean {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            padding: 7px 12px;
            border-radius: 9px;
            font-size: 12px;
            font-weight: 850;
            cursor: pointer;
            border: 1.5px solid #cbd5e1;
            background: #ffffff;
            color: #334155;
            transition: all 0.15s ease;
            white-space: nowrap;
        }
        .btn-action-clean:hover {
            background: #f1f5f9;
            color: #0f172a;
            border-color: #94a3b8;
        }
        .btn-action-pick {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
            border: 1.5px solid #d97706 !important;
            color: #ffffff !important;
            box-shadow: 0 2px 8px rgba(217, 119, 6, 0.3) !important;
            transition: all 0.15s ease !important;
        }
        .btn-action-pick:hover {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%) !important;
            border-color: #b45309 !important;
            color: #ffffff !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 5px 14px rgba(217, 119, 6, 0.45) !important;
        }
        .btn-action-pick:active {
            transform: translateY(1px) !important;
            box-shadow: 0 2px 4px rgba(217, 119, 6, 0.3) !important;
        }
        .btn-action-blue {
            background: #eff6ff !important;
            border-color: #bfdbfe !important;
            color: #1d4ed8 !important;
        }
        .btn-action-blue:hover {
            background: #dbeafe !important;
            border-color: #93c5fd !important;
            color: #1e40af !important;
        }
        .btn-action-danger {
            color: #dc2626 !important;
            border-color: #fecaca !important;
            background: #fef2f2 !important;
        }
        .btn-action-danger:hover {
            background: #fee2e2 !important;
            border-color: #fca5a5 !important;
            color: #b91c1c !important;
        }
        /* ==========================================================================
           5. QUESTION CARDS - TƯƠNG PHẢN RÕ NÉT, NỔI KHỐI 3D TRÊN NỀN
           ========================================================================== */
        .questions-flow {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .q-card {
            background: #ffffff;
            border-radius: var(--radius-md);
            border: 1.5px solid var(--border-strong);
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
            transition: all 0.16s cubic-bezier(0.16, 1, 0.3, 1);
            overflow: hidden;
        }
        .q-card:hover {
            border-color: #94a3b8;
            box-shadow: 0 6px 14px rgba(15, 23, 42, 0.07);
            transform: translateY(-1px);
        }
        /* State: Câu hỏi ĐƯỢC CHỌN (Nổi bật, rực rỡ và rõ ràng) */
        .q-card.is-checked {
            border-color: #10b981;
            background: #f0fdf4;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.16), inset 0 -2px 0 rgba(16, 185, 129, 0.1);
        }

        .q-card-header {
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            user-select: none;
        }
        .q-check-wrap {
            display: flex;
            align-items: center;
            flex-shrink: 0;
        }
        .q-checkbox-input {
            width: 19px;
            height: 19px;
            border-radius: 5px;
            accent-color: #10b981;
            cursor: pointer;
        }

        .q-card-main {
            flex: 1;
            min-width: 0;
        }
        .q-meta-line {
            display: flex;
            align-items: center;
            gap: 7px;
            margin-bottom: 4px;
            font-size: 11.5px;
            font-weight: 800;
            color: #64748b;
        }
        .badge-q-num {
            font-size: 11px;
            font-weight: 900;
            padding: 1px 7px;
            border-radius: 6px;
            background: #e0e7ff;
            color: #4338ca;
            border: 1px solid #c7d2fe;
        }
        .q-card.is-checked .badge-q-num {
            background: #10b981;
            color: #ffffff;
            border-color: #059669;
        }
        .badge-type-tag {
            background: #f1f5f9;
            color: #475569;
            padding: 1px 6px;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
        }
        .badge-topic-tag {
            color: #2563eb;
            font-weight: 850;
        }
        .badge-image-tag {
            background: #fef3c7;
            color: #b45309;
            padding: 1px 6px;
            border-radius: 4px;
            border: 1px solid #fde68a;
            font-size: 11px;
            font-weight: 800;
        }
        .q-title-txt {
            font-size: 14px;
            font-weight: 750;
            color: #0f172a;
            line-height: 1.45;
        }

        /* 👁️ Nút Xem Thử Popup Trực Quan */
        .q-card-actions {
            flex-shrink: 0;
            display: flex;
            align-items: center;
        }
        .btn-q-preview {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 12px;
            border-radius: 8px;
            background: linear-gradient(135deg, #f0fdf4, #dcfce7);
            border: 1.5px solid #86efac;
            color: #15803d;
            font-size: 12px;
            font-weight: 850;
            cursor: pointer;
            transition: all 0.15s ease;
            box-shadow: 0 1px 3px rgba(16, 185, 129, 0.15);
            white-space: nowrap;
        }
        .btn-q-preview:hover {
            background: linear-gradient(135deg, #10b981, #059669);
            border-color: #059669;
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35);
        }
        .btn-q-preview:active {
            transform: translateY(1px);
        }

        /* 🌌 IC3 QUEST ARENA REAL SIMULATOR MODAL (100% Giao diện phòng thi thật) */
        .preview-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(5px);
            display: none;
            place-items: center;
            z-index: 9999;
            padding: 16px;
        }
        .preview-modal-overlay.active {
            display: grid;
        }
        .preview-simulator-box {
            width: min(1200px, 98vw);
            height: 94vh;
            max-height: 94vh;
            display: flex;
            flex-direction: column;
            padding: 0;
            border-radius: 18px;
            overflow: hidden;
            background: #061021;
            border: 2.5px solid #00f2fe;
            box-shadow: 0 0 45px rgba(0, 242, 254, 0.45);
            position: relative;
        }

        /* 🎛️ SIMULATOR TOPBAR CÂN ĐỐI 3 PHẦN - KHÔNG DỒN LỆCH */
        .sim-topbar {
            background: linear-gradient(90deg, #091a33, #0f274a);
            padding: 10px 20px;
            border-bottom: 2px solid #00f2fe;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 10;
            flex-shrink: 0;
            gap: 16px;
        }
        .sim-topbar-left {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }
        .sim-brand {
            font-family: 'Fredoka', cursive, sans-serif;
            font-size: 15px;
            font-weight: 700;
            color: #ffe658;
            display: flex;
            align-items: center;
            gap: 6px;
            letter-spacing: 0.5px;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .sim-mode-badge {
            color: #94a3b8;
            font-size: 12px;
            font-weight: 800;
            white-space: nowrap;
            background: rgba(255, 255, 255, 0.08);
            padding: 3px 10px;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }
        .sim-topbar-center {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            flex-shrink: 0;
        }
        .btn-sim-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 16px;
            border-radius: 9px;
            font-size: 12.5px;
            font-weight: 900;
            cursor: pointer;
            transition: all 0.15s ease;
            white-space: nowrap;
        }
        .btn-sim-autofill {
            background: rgba(245, 158, 11, 0.15);
            border: 1.5px solid #f59e0b;
            color: #fde68a;
        }
        .btn-sim-autofill:hover {
            background: #f59e0b;
            color: #1e1b4b;
            box-shadow: 0 0 14px rgba(245, 158, 11, 0.45);
            transform: translateY(-1px);
        }
        .btn-sim-check {
            background: linear-gradient(135deg, #00f2fe, #0284c7);
            border: 1.5px solid #38bdf8;
            color: #061021;
            box-shadow: 0 0 10px rgba(0, 242, 254, 0.35);
        }
        .btn-sim-check:hover {
            transform: translateY(-1px);
            box-shadow: 0 0 18px rgba(0, 242, 254, 0.6);
        }
        .sim-topbar-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            min-width: 0;
        }
        .btn-sim-close {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 9px;
            background: rgba(239, 68, 68, 0.15);
            border: 1.5px solid rgba(239, 68, 68, 0.4);
            color: #fca5a5;
            font-size: 12.5px;
            font-weight: 850;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .btn-sim-close:hover {
            background: #ef4444;
            color: #ffffff;
            border-color: #dc2626;
            box-shadow: 0 0 12px rgba(239, 68, 68, 0.4);
        }
        @keyframes spinLoader {
            to { transform: rotate(360deg); }
        }

        .no-questions-found {
            background: #ffffff;
            border-radius: var(--radius-md);
            border: 2px dashed #cbd5e1;
            padding: 40px 16px;
            text-align: center;
            color: #64748b;
            font-size: 13.5px;
            font-weight: 750;
        }

        /* ==========================================================================
           🍞 TOAST NOTIFICATION 3D GAMIFIED VIP (ĐỒNG BỘ CHUẨN ADMIN)
           ========================================================================== */
        .toast-container {
            position: fixed;
            top: 24px;
            right: 28px;
            z-index: 999999;
            display: flex;
            flex-direction: column;
            gap: 12px;
            pointer-events: none;
        }
        .toast-msg {
            padding: 13px 18px;
            font-size: 13.5px;
            font-weight: 800;
            border-radius: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: toastSlideIn 0.32s cubic-bezier(0.16, 1, 0.3, 1);
            pointer-events: auto;
            min-width: 320px;
            max-width: 490px;
            border: 2.5px solid #ffffff;
            box-shadow: 0 16px 36px rgba(0, 0, 0, 0.2), inset 0 -3px 0 rgba(0, 0, 0, 0.14);
            transition: all 0.25s ease;
        }
        .toast-msg.toast-success {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: #ffffff;
            border-color: #6ee7b7;
            box-shadow: 0 14px 32px rgba(4, 120, 87, 0.4), inset 0 -3px 0 rgba(0, 0, 0, 0.15);
        }
        .toast-msg.toast-error {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: #ffffff;
            border-color: #fca5a5;
            box-shadow: 0 14px 32px rgba(220, 38, 38, 0.4), inset 0 -3px 0 rgba(0, 0, 0, 0.15);
        }
        .toast-msg.toast-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #ffffff;
            border-color: #fde68a;
            box-shadow: 0 14px 32px rgba(217, 119, 6, 0.4), inset 0 -3px 0 rgba(0, 0, 0, 0.15);
        }
        .toast-msg.toast-info {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: #ffffff;
            border-color: #bae6fd;
            box-shadow: 0 14px 32px rgba(2, 132, 199, 0.4), inset 0 -3px 0 rgba(0, 0, 0, 0.15);
        }
        .toast-icon-wrap {
            width: 28px;
            height: 28px;
            border-radius: 9px;
            background: rgba(255, 255, 255, 0.22);
            border: 1.5px solid rgba(255, 255, 255, 0.45);
            display: grid;
            place-items: center;
            font-size: 13.5px;
            font-weight: 900;
            flex-shrink: 0;
            color: #ffffff;
        }
        .toast-close-btn {
            margin-left: auto;
            background: transparent;
            border: none;
            color: rgba(255, 255, 255, 0.85);
            font-size: 16px;
            cursor: pointer;
            padding: 0 4px;
            line-height: 1;
            transition: color 0.15s ease, transform 0.15s ease;
        }
        .toast-close-btn:hover {
            color: #ffffff;
            transform: scale(1.2);
        }
        @keyframes toastSlideIn {
            from { opacity: 0; transform: translateX(50px) scale(0.92); }
            to { opacity: 1; transform: translateX(0) scale(1); }
        }
    </style>
</head>
<body>

<div class="shell" id="admin-shell">
    <!-- Sidebar Admin Đồng Bộ 100% -->
    <x-admin-sidebar active-route="admin.mock-tests.index" active-group="exams" />

    <!-- Main Workspace -->
    <div class="main-workspace">
        <x-admin-topbar title="{{ $mode === 'create' ? 'Soạn Bộ Đề Thi Thử Mới' : 'Biên Tập: ' . $mockTest->name }}" breadcrumb="🏆 Chuyên Môn Đề Thi > Soạn Đề Thi Thử" />

        <main class="main-content">
            <!-- 🎛️ SUB-HEADER TINH GỌN (CHỈ CHỨA TRẠNG THÁI & NÚT LƯU, KHÔNG LẶP TIÊU ĐỀ) -->
            <div class="sub-header-bar">
                <div class="sub-header-left">
                    <a href="{{ route('admin.mock-tests.index', ['grade' => $selectedGrade]) }}" class="btn-sub-back">
                        ← Quay lại danh sách
                    </a>
                    <span class="sub-badge-grade">Khối {{ $selectedGrade }} • Spark Level {{ max(1, $selectedGrade - 2) }}</span>
                </div>

                <div class="sub-header-right">
                    <div class="counter-pill">
                        <span>Đã chọn:</span>
                        <b id="top-count-badge">0</b> / <span id="top-total-available">{{ $selectedLevel?->topics->flatMap->tests->flatMap->questions->count() ?? 0 }}</span> câu
                        <div class="counter-bar-track">
                            <div class="counter-bar-fill" id="top-progress-fill"></div>
                        </div>
                    </div>

                    <button type="button" class="btn-top-save-clean" id="btn-top-save" onclick="submitMockTestForm()">
                        💾 Lưu Bộ Đề
                    </button>
                </div>
            </div>

            <!-- Form Soạn Đề -->
            <form id="mock-test-form" method="post" action="{{ $mode === 'create' ? route('admin.mock-tests.store') : route('admin.mock-tests.update', $mockTest) }}">
                @csrf
                @if($mode === 'edit')
                    @method('put')
                @endif

                <div class="studio-grid">
                    <!-- =================================================================
                         LEFT COLUMN: SIDEBAR CẤU HÌNH TỐI GIẢN & SẮC NÉT
                         ================================================================= -->
                    <aside class="left-col-sticky">
                        <div class="config-card-clean">
                            <div class="config-card-title">
                                <span>⚙️ Thiết Lập Đề Thi</span>
                                <small>{{ $mode === 'create' ? 'Tạo mới' : 'Biên tập' }}</small>
                            </div>

                            <!-- Khối lớp -->
                            <div class="clean-field">
                                <label>Khối lớp áp dụng</label>
                                <select class="clean-input" name="level_id" id="level_select" onchange="switchLevel(this.value)">
                                    @foreach($levels as $lvl)
                                        <option value="{{ $lvl->id }}" data-grade="{{ $lvl->grade }}" {{ $selectedLevel?->id === $lvl->id ? 'selected' : '' }}>
                                            Khối {{ $lvl->grade }} (Spark Level {{ max(1, $lvl->grade - 2) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Tên đề thi -->
                            <div class="clean-field">
                                <label>Tên bộ đề thi <span style="color:#ef4444">*</span></label>
                                <input class="clean-input" type="text" name="name" value="{{ old('name', $mockTest->name ?? 'Đề Thi Thử IC3 Spark Level ' . max(1, $selectedGrade - 2) . ' — Đề 01') }}" required placeholder="Ví dụ: Đề Thi Thử Khối {{ $selectedGrade }} — Đề 01">
                            </div>

                            <!-- Thông số: Thời gian & Điểm đạt -->
                            <div class="params-row">
                                <div class="clean-field">
                                    <label>Thời lượng (phút)</label>
                                    <input class="clean-input" type="number" name="duration_minutes" value="{{ old('duration_minutes', $mockTest->duration_minutes ?? 45) }}" min="5" max="180" required>
                                </div>
                                <div class="clean-field">
                                    <label>Điểm chuẩn đạt</label>
                                    <input class="clean-input" type="number" name="pass_score" value="{{ old('pass_score', $mockTest->pass_score ?? 700) }}" min="100" max="1000" step="50" required>
                                </div>
                            </div>

                            <!-- 3 Tùy chọn Checkbox Thanh Lịch -->
                            <div class="switches-group-clean">
                                <label class="switch-clean-label">
                                    <input type="checkbox" name="shuffle_questions" value="1" {{ old('shuffle_questions', $mockTest->shuffle_questions ?? true) ? 'checked' : '' }}>
                                    <span>Xáo trộn câu hỏi khi thi</span>
                                </label>
                                <label class="switch-clean-label">
                                    <input type="checkbox" name="shuffle_options" value="1" {{ old('shuffle_options', $mockTest->shuffle_options ?? true) ? 'checked' : '' }}>
                                    <span>Xáo trộn đáp án A, B, C, D</span>
                                </label>
                                <label class="switch-clean-label">
                                    <input type="checkbox" name="is_published" value="1" {{ old('is_published', $mockTest->is_published ?? true) ? 'checked' : '' }}>
                                    <span>Mở phát hành cho học sinh thi</span>
                                </label>
                            </div>

                            <!-- Phân Bổ 7 Chủ Đề Tối Giản (Dạng Danh Sách Thanh Mảnh) -->
                            <div class="matrix-clean-section">
                                <div class="matrix-clean-header">
                                    <span>Phân bổ 7 chủ đề</span>
                                    <small>(Bấm để lọc)</small>
                                </div>
                                <div class="topic-clean-list">
                                    @foreach($selectedLevel->topics as $topic)
                                        <div class="topic-clean-row" id="matrix-item-{{ $topic->id }}" onclick="filterByTopic({{ $topic->id }})">
                                            <div class="topic-row-left">
                                                <span class="matrix-topic-dot" id="matrix-dot-{{ $topic->id }}"></span>
                                                <span class="topic-row-name" title="{{ $topic->name }}">CĐ {{ $topic->position }}: {{ $topic->name }}</span>
                                            </div>
                                            <span class="matrix-topic-badge" id="matrix-badge-{{ $topic->id }}">0 câu</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Nút Lưu & Cập Nhật -->
                            <button class="btn-submit-main" type="submit" id="btn-save-submit">
                                💾 Lưu & Phát Hành Bộ Đề
                            </button>
                        </div>
                    </aside>

                    <!-- =================================================================
                         RIGHT COLUMN: SMART 1-ROW TOOLBAR & QUESTION STREAM
                         ================================================================= -->
                    <section class="right-col">
                        @php
                            $allTopicsQuestionsCount = 0;
                            foreach($selectedLevel->topics as $t) {
                                $allTopicsQuestionsCount += $t->tests->flatMap->questions->where('is_published', true)->count();
                            }
                        @endphp

                        <!-- 🎛️ SMART ACTION BAR (1 HÀNG DUY NHẤT: Gộp Search + Select Chủ Đề + Actions) -->
                        <div class="smart-action-bar">
                            <!-- 🔍 Ô Tìm kiếm -->
                            <div class="search-field-wrap">
                                <span class="search-icon-fixed">🔍</span>
                                <input type="text" id="question-search-input" placeholder="Tìm kiếm câu hỏi theo từ khóa, nội dung..." oninput="handleSearch()">
                            </div>

                            <!-- 📁 SELECT OPTION BỘ LỌC CHỦ ĐỀ GỌN GÀNG (Thay thế cho dải buttons dài) -->
                            <div class="topic-select-wrap">
                                <select id="topic-select-filter" class="clean-select-filter" onchange="filterByTopic(this.value)">
                                    <option value="all" id="opt-topic-all">⚡ Tất cả 7 chủ đề (0/{{ $allTopicsQuestionsCount }})</option>
                                    @foreach($selectedLevel->topics as $topic)
                                        @php
                                            $tCount = $topic->tests->flatMap->questions->where('is_published', true)->count();
                                        @endphp
                                        <option value="{{ $topic->id }}" id="opt-topic-{{ $topic->id }}" data-pos="{{ $topic->position }}" data-name="{{ $topic->name }}" data-total="{{ $tCount }}">
                                            CĐ {{ $topic->position }}: {{ Str::limit($topic->name, 24) }} (0/{{ $tCount }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Cụm nút hành động tinh gọn -->
                            <div class="bar-actions-group">
                                <!-- Bốc Đề: Input số + Nút bốc ngẫu nhiên -->
                                <div style="display:flex;align-items:center;gap:4px;">
                                    <input type="number" id="pick-count-input" value="30" min="1"
                                           style="width:56px;padding:5px 8px;border-radius:8px;border:1.5px solid #cbd5e1;font-size:13px;font-weight:700;text-align:center;color:#1e293b;background:#fff;outline:none;"
                                           title="Nhập số câu muốn bốc"
                                           onkeydown="if(event.key==='Enter'){autoPickQuestions(parseInt(this.value)||30);}">
                                    <button type="button" class="btn-action-clean btn-action-pick" id="btn-quick-pick"
                                            onclick="autoPickQuestions(parseInt(document.getElementById('pick-count-input').value)||30)"
                                            title="Tự động bốc ngẫu nhiên đều từ 7 chủ đề">
                                        ⚡ Bốc Đề
                                    </button>
                                </div>

                                <!-- Nút Chọn hết CĐ -->
                                <button type="button" class="btn-action-clean" onclick="toggleCurrentTopicAll()" id="btn-select-topic-all">
                                    ✓ Chọn hết
                                </button>
                                <!-- Nút Bỏ chọn -->
                                <button type="button" class="btn-action-clean btn-action-danger" onclick="clearAllSelections()" title="Bỏ chọn tất cả câu hỏi">
                                    ✕
                                </button>
                            </div>
                        </div>

                        <!-- 📋 Danh Sách Câu Hỏi Trực Quan Sắc Nét & Tinh Gọn -->
                        @php
                            $typeLabels = [
                                'MultipleChoice' => 'Trắc nghiệm',
                                'MultipleResponse' => 'Nhiều đáp án',
                                'Matching' => 'Ghép nối',
                                'MultipleChoiceText' => 'Phân loại',
                                'Hotspot' => 'Hotspot',
                                'Sequence' => 'Sắp xếp',
                            ];
                            $globalQIdx = 0;
                        @endphp

                        <div class="questions-flow" id="questions-flow-container">
                            @foreach($selectedLevel->topics as $topic)
                                @foreach($topic->tests as $test)
                                    @foreach($test->questions->where('is_published', true) as $q)
                                        @php
                                            $globalQIdx++;
                                            $isChecked = in_array($q->id, $selectedQuestionIds);
                                            $hasImage = $q->assets->where('kind', 'image')->isNotEmpty();
                                        @endphp

                                        <article class="q-card {{ $isChecked ? 'is-checked' : '' }}" 
                                                 id="q-card-{{ $q->id }}" 
                                                 data-question-id="{{ $q->id }}" 
                                                 data-topic-id="{{ $topic->id }}" 
                                                 data-title="{{ Str::lower($q->title) }}"
                                                 data-options="{{ Str::lower($q->options->pluck('content')->implode(' ')) }}">
                                            
                                            <div class="q-card-header" onclick="toggleCardCheck({{ $q->id }}, event)">
                                                <div class="q-check-wrap">
                                                    <input type="checkbox" 
                                                           name="question_ids[]" 
                                                           value="{{ $q->id }}" 
                                                           class="q-checkbox-input" 
                                                           id="cb-q-{{ $q->id }}"
                                                           data-topic="{{ $topic->id }}" 
                                                           {{ $isChecked ? 'checked' : '' }} 
                                                           onchange="handleCheckboxChange(event)">
                                                </div>

                                                <div class="q-card-main">
                                                    <div class="q-meta-line">
                                                        <span class="badge-q-num">#{{ $globalQIdx }}</span>
                                                        <span>•</span>
                                                        <span class="badge-type-tag">{{ $typeLabels[$q->type] ?? $q->type }}</span>
                                                        <span>•</span>
                                                        <span class="badge-topic-tag">CĐ {{ $topic->position }}: {{ $topic->name }}</span>
                                                        @if($hasImage)
                                                            <span>•</span>
                                                            <span class="badge-image-tag" title="Câu hỏi có hình ảnh minh họa">🖼️ Có ảnh</span>
                                                        @endif
                                                    </div>
                                                    <div class="q-title-txt">
                                                        {{ $q->title ?: '(Câu hỏi thực hành tương tác)' }}
                                                    </div>
                                                </div>

                                                <div class="q-card-actions" onclick="event.stopPropagation()">
                                                    <button type="button" 
                                                            class="btn-q-preview" 
                                                            onclick="openQuestionPreview('{{ $test->slug }}', {{ $q->id }}, {{ $globalQIdx }})"
                                                            title="Xem trực quan câu hỏi này trong phòng thi">
                                                        <span>👁️</span> <span>Xem thử</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </article>
                                    @endforeach
                                @endforeach
                            @endforeach
                        </div>

                        <div id="no-results-box" class="no-questions-found" style="display: none;">
                            🔍 Không tìm thấy câu hỏi nào phù hợp với từ khóa tìm kiếm.
                        </div>
                    </section>
                </div>
            </form>
        </main>
    </div>
</div>

<!-- 🌌 POPUP MODAL XEM THỬ PHÒNG THI TƯƠNG TÁC (IC3 ARENA SIMULATOR) -->
<div id="modal-student-sim" class="preview-modal-overlay">
    <div class="preview-simulator-box">
        <!-- Top Control Strip Cân Đối 3 Phần - Tuyệt Đối Không Dồn Lệch -->
        <div class="sim-topbar">
            <!-- Left: Brand & Khối lớp -->
            <div class="sim-topbar-left">
                <span class="sim-brand">
                    <span>⭐</span> IC3 QUEST ARENA
                </span>
                <span class="sim-mode-badge" id="sim-preview-title">
                    Khối {{ $selectedGrade }} · Xem thử câu hỏi
                </span>
            </div>

            <!-- Center: Nút hành động kiểm thử căn đều chính giữa -->
            <div class="sim-topbar-center">
                <button type="button" class="btn-sim-action btn-sim-autofill" onclick="triggerIframeAutofill()" title="Tự động điền đáp án đúng theo hệ thống">
                    <span>💡</span> Điền đáp án đúng
                </button>
                <button type="button" class="btn-sim-action btn-sim-check" onclick="triggerIframeCheck()" title="Kiểm tra đáp án đang chọn">
                    <span>🏁</span> KIỂM TRA ĐÁP ÁN
                </button>
            </div>

            <!-- Right: Nút Đóng nằm sát bên phải -->
            <div class="sim-topbar-right">
                <button type="button" class="btn-sim-close" onclick="closeStudentSimModal()" title="Đóng xem thử (Phím ESC)">
                    <span>✕</span> <span>Đóng</span>
                </button>
            </div>
        </div>

        <!-- Vùng Hiển Thị Câu Hỏi & Loading Layer Chống Giật -->
        <div style="flex: 1; position: relative; width: 100%; height: 100%; overflow: hidden; background: #061021;">
            <div id="sim-loading-layer" style="position: absolute; inset: 0; display: none; place-items: center; background: #061021; z-index: 5; transition: opacity 0.15s ease;">
                <div style="display: flex; flex-direction: column; align-items: center; gap: 10px;">
                    <div style="width: 32px; height: 32px; border: 3px solid rgba(0,242,254,0.2); border-top-color: #00f2fe; border-radius: 50%; animation: spinLoader 0.7s linear infinite;"></div>
                    <span style="font-size: 13px; font-weight: 800; color: #94a3b8;">Đang nạp câu hỏi...</span>
                </div>
            </div>
            <!-- Iframe Phòng Thi Trực Quan Thật -->
            <iframe id="sim-user-view-iframe" src="" style="width: 100%; height: 100%; border: none; background: #061021; opacity: 0; transition: opacity 0.18s ease;"></iframe>
        </div>
    </div>
</div>

<script>
    let currentTopicFilter = 'all';

    // ⚡ CẬP NHẬT TIẾN ĐỘ & BẢNG MA TRẬN 7 CHỦ ĐỀ
    function updateCounters() {
        const allCheckboxes = document.querySelectorAll('.q-checkbox-input');
        const checkedBoxes = document.querySelectorAll('.q-checkbox-input:checked');
        const totalChecked = checkedBoxes.length;

        // 1. Cập nhật top badge & progress bar
        const badge = document.getElementById('top-count-badge');
        if (badge) badge.textContent = totalChecked;

        // Progress bar tính theo tổng câu available thực tế
        const totalAvailableEl = document.getElementById('top-total-available');
        const totalAvailable = totalAvailableEl ? parseInt(totalAvailableEl.textContent) || 1 : 1;
        const percent = Math.min(100, Math.round((totalChecked / totalAvailable) * 100));
        const fillBar = document.getElementById('top-progress-fill');
        if (fillBar) {
            fillBar.style.width = percent + '%';
            if (totalChecked >= Math.floor(totalAvailable * 0.8)) {
                fillBar.style.background = '#10b981';
            } else {
                fillBar.style.background = '#f59e0b';
            }
        }

        // 2. Cập nhật từng chủ đề
        const topics = @json($selectedLevel->topics->pluck('id'));
        let allCheckedCount = 0;
        let allTotalCount = allCheckboxes.length;

        topics.forEach(tId => {
            const topicBoxes = document.querySelectorAll(`.q-checkbox-input[data-topic="${tId}"]`);
            const topicChecked = document.querySelectorAll(`.q-checkbox-input[data-topic="${tId}"]:checked`).length;
            const topicTotal = topicBoxes.length;

            allCheckedCount += topicChecked;

            // Cập nhật text trong Select Option lọc chủ đề
            const optTopic = document.getElementById(`opt-topic-${tId}`);
            if (optTopic) {
                const pos = optTopic.dataset.pos;
                const name = optTopic.dataset.name;
                const shortName = name.length > 24 ? name.substring(0, 24) + '...' : name;
                optTopic.textContent = `CĐ ${pos}: ${shortName} (${topicChecked}/${topicTotal})`;
            }

            // Matrix badge & dot ở cột trái
            const matrixBadge = document.getElementById(`matrix-badge-${tId}`);
            const matrixDot = document.getElementById(`matrix-dot-${tId}`);
            const matrixItem = document.getElementById(`matrix-item-${tId}`);

            if (matrixBadge) {
                matrixBadge.textContent = `${topicChecked} câu`;
                if (topicChecked > 0) {
                    matrixBadge.classList.add('active');
                } else {
                    matrixBadge.classList.remove('active');
                }
            }
            if (matrixDot) {
                if (topicChecked > 0) {
                    matrixDot.classList.add('active');
                } else {
                    matrixDot.classList.remove('active');
                }
            }
            if (matrixItem) {
                if (currentTopicFilter === String(tId)) {
                    matrixItem.classList.add('is-selected');
                } else {
                    matrixItem.classList.remove('is-selected');
                }
            }
        });

        // Cập nhật option Tất cả
        const optAll = document.getElementById('opt-topic-all');
        if (optAll) {
            optAll.textContent = `⚡ Tất cả 7 chủ đề (${totalChecked}/${allTotalCount})`;
        }
    }

    // 🔘 Click Card để tick chọn câu hỏi
    function toggleCardCheck(qId, event) {
        if (event.target.closest('.btn-q-preview') || event.target.closest('.q-card-actions')) {
            return;
        }

        const cb = document.getElementById(`cb-q-${qId}`);
        if (cb && event.target !== cb) {
            cb.checked = !cb.checked;
        }

        const card = document.getElementById(`q-card-${qId}`);
        if (card && cb) {
            if (cb.checked) {
                card.classList.add('is-checked');
            } else {
                card.classList.remove('is-checked');
            }
        }

        updateCounters();
    }

    function handleCheckboxChange(event) {
        event.stopPropagation();
        const cb = event.target;
        const qId = cb.value;
        const card = document.getElementById(`q-card-${qId}`);
        if (card) {
            if (cb.checked) {
                card.classList.add('is-checked');
            } else {
                card.classList.remove('is-checked');
            }
        }
        updateCounters();
    }

    // 🌌 SIMULATOR PREVIEW: XEM THỬ CÂU HỎI TRONG PHÒNG THI THẬT
    let currentPreviewSlug = null;
    let currentPreviewQId = null;

    function openQuestionPreview(testSlug, questionId, qNum) {
        const modal = document.getElementById('modal-student-sim');
        const iframe = document.getElementById('sim-user-view-iframe');
        const titleEl = document.getElementById('sim-preview-title');
        const loader = document.getElementById('sim-loading-layer');

        if (titleEl) {
            titleEl.textContent = qNum ? `Khối {{ $selectedGrade }} · Câu #${qNum}` : 'Khối {{ $selectedGrade }} · Xem thử câu hỏi';
        }

        if (!modal || !iframe) return;

        // Bật loading layer và làm mờ iframe để không bao giờ thấy nội dung cũ bị chớp giật
        if (loader) {
            loader.style.display = 'grid';
            loader.style.opacity = '1';
        }
        iframe.style.opacity = '0';

        iframe.onload = () => {
            iframe.style.opacity = '1';
            if (loader) {
                loader.style.opacity = '0';
                setTimeout(() => { loader.style.display = 'none'; }, 150);
            }
        };

        // Nạp sạch câu hỏi mới (chỉ render duy nhất câu hỏi này)
        currentPreviewSlug = testSlug;
        currentPreviewQId = questionId;
        iframe.src = `/bai-luyen/${testSlug}/lam-bai?preview=1&qId=${questionId}&single=1`;

        modal.classList.add('active');
    }

    function triggerIframeAutofill() {
        const iframe = document.getElementById('sim-user-view-iframe');
        try {
            iframe?.contentWindow?.postMessage({ action: 'admin-autofill' }, '*');
            if (iframe?.contentWindow?.adminAutofillAnswer) {
                iframe.contentWindow.adminAutofillAnswer();
            }
            // Tự động kiểm tra luôn để học sinh / giáo viên thấy ngay đáp án đúng và lời giải
            setTimeout(() => {
                triggerIframeCheck();
            }, 120);
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

    function closeStudentSimModal() {
        const modal = document.getElementById('modal-student-sim');
        const iframe = document.getElementById('sim-user-view-iframe');
        const loader = document.getElementById('sim-loading-layer');

        if (modal) modal.classList.remove('active');

        // CLEAR SẠCH HOÀN TOÀN IFRAME & CACHE KHI THOÁT POPUP
        if (iframe) {
            iframe.onload = null;
            iframe.src = 'about:blank';
            iframe.style.opacity = '0';
        }
        if (loader) {
            loader.style.display = 'none';
        }
        currentPreviewSlug = null;
        currentPreviewQId = null;
    }

    // Đóng modal khi bấm ESC hoặc click ra ngoài nền mờ
    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeStudentSimModal();
        }
    });

    window.addEventListener('click', (e) => {
        if (e.target.id === 'modal-student-sim' || e.target.classList.contains('preview-modal-overlay')) {
            closeStudentSimModal();
        }
    });

    window.addEventListener('message', (event) => {
        if (event.data?.action === 'close-sim-modal') {
            closeStudentSimModal();
        }
    });

    // 📑 Lọc theo Chủ Đề (Đồng bộ cả Select Option và Cột Trái)
    function filterByTopic(topicId) {
        currentTopicFilter = String(topicId);

        // Đồng bộ giá trị vào Select dropdown
        const select = document.getElementById('topic-select-filter');
        if (select && select.value !== currentTopicFilter) {
            select.value = currentTopicFilter;
        }

        // Cập nhật highlight ở danh sách chủ đề cột trái
        const topics = @json($selectedLevel->topics->pluck('id'));
        topics.forEach(tId => {
            const matrixItem = document.getElementById(`matrix-item-${tId}`);
            if (matrixItem) {
                if (currentTopicFilter === String(tId)) {
                    matrixItem.classList.add('is-selected');
                } else {
                    matrixItem.classList.remove('is-selected');
                }
            }
        });

        handleSearch();
    }

    // 🔍 Tìm Kiếm & Kết Hợp Lọc Chủ Đề
    function handleSearch() {
        const keyword = document.getElementById('question-search-input').value.toLowerCase().trim();
        const cards = document.querySelectorAll('.q-card');
        let visibleCount = 0;

        cards.forEach(card => {
            const cardTopic = card.dataset.topicId;
            const cardTitle = card.dataset.title;
            const cardOptions = card.dataset.options;

            const matchTopic = (currentTopicFilter === 'all' || cardTopic === String(currentTopicFilter));
            const matchKeyword = (keyword === '' || cardTitle.includes(keyword) || cardOptions.includes(keyword));

            if (matchTopic && matchKeyword) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        const noRes = document.getElementById('no-results-box');
        if (noRes) {
            noRes.style.display = visibleCount === 0 ? 'block' : 'none';
        }
    }

    // 🔘 Chọn hết / Bỏ chọn hết chủ đề đang xem
    function toggleCurrentTopicAll() {
        let targetSelector = '.q-card';
        if (currentTopicFilter !== 'all') {
            targetSelector = `.q-card[data-topic-id="${currentTopicFilter}"]`;
        }

        const visibleCards = Array.from(document.querySelectorAll(targetSelector)).filter(c => c.style.display !== 'none');
        const checkboxes = visibleCards.map(c => c.querySelector('.q-checkbox-input')).filter(Boolean);

        const allChecked = checkboxes.every(cb => cb.checked);
        checkboxes.forEach(cb => {
            cb.checked = !allChecked;
            const card = document.getElementById(`q-card-${cb.value}`);
            if (card) {
                if (!allChecked) {
                    card.classList.add('is-checked');
                } else {
                    card.classList.remove('is-checked');
                }
            }
        });

        updateCounters();
    }

    // 🎲 Bốc Đề Nhanh Với Toast & Loading State Chống Đơ
    function autoPickQuestions(targetCount) {
        const levelId = document.getElementById('level_select').value;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const pickBtn = document.getElementById('btn-quick-pick') || document.querySelector('.btn-action-pick');

        if (pickBtn) {
            pickBtn.disabled = true;
            pickBtn.style.opacity = '0.7';
            pickBtn.innerHTML = '⚡ Đang bốc...';
        }

        fetch('{{ route("admin.mock-tests.quick-random") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                level_id: levelId,
                total_count: targetCount
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.question_ids) {
                const idSet = new Set(data.question_ids);
                document.querySelectorAll('.q-checkbox-input').forEach(cb => {
                    const shouldCheck = idSet.has(parseInt(cb.value, 10));
                    cb.checked = shouldCheck;
                    const card = document.getElementById(`q-card-${cb.value}`);
                    if (card) {
                        if (shouldCheck) card.classList.add('is-checked');
                        else card.classList.remove('is-checked');
                    }
                });
                updateCounters();
                showToast(`🎉 Đã tự động bốc thành công ${data.count} câu hỏi phân bổ đều từ 7 chủ đề trong khối!`, 'success');
            } else {
                showToast(data.message || 'Không thể bốc câu hỏi tự động.', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            showToast('Có lỗi xảy ra khi kết nối máy chủ.', 'error');
        })
        .finally(() => {
            if (pickBtn) {
                pickBtn.disabled = false;
                pickBtn.style.opacity = '1';
                pickBtn.innerHTML = '⚡ Bốc Đề';
            }
        });
    }

    // 🗑️ Bỏ chọn toàn bộ câu hỏi
    function clearAllSelections() {
        const checkedBoxes = document.querySelectorAll('.q-checkbox-input:checked');
        if (checkedBoxes.length === 0) {
            showToast('Hiện tại chưa có câu hỏi nào được chọn.', 'info');
            return;
        }

        if (!confirm('Bạn có chắc muốn bỏ chọn tất cả câu hỏi đã chọn?')) return;
        
        checkedBoxes.forEach(cb => {
            cb.checked = false;
            const card = document.getElementById(`q-card-${cb.value}`);
            if (card) card.classList.remove('is-checked');
        });
        updateCounters();
        showToast('✓ Đã bỏ chọn tất cả câu hỏi!', 'info');
    }

    // 💾 Kiểm tra dữ liệu trước khi nộp Form Lưu Bộ Đề
    function submitMockTestForm() {
        const nameInput = document.querySelector('input[name="name"]');
        if (!nameInput || !nameInput.value.trim()) {
            showToast('⚠️ Vui lòng nhập tên cho bộ đề thi thử!', 'warning');
            nameInput?.focus();
            return;
        }

        const checkedBoxes = document.querySelectorAll('.q-checkbox-input:checked');
        if (checkedBoxes.length === 0) {
            showToast('⚠️ Vui lòng chọn ít nhất 1 câu hỏi để đưa vào bộ đề thi thử!', 'warning');
            return;
        }

        const saveBtn = document.getElementById('btn-top-save');
        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.innerHTML = '⏳ Đang lưu...';
        }

        document.getElementById('mock-test-form').submit();
    }

    // 🍞 Hàm Hiển Thị Toast Thông Báo Chuẩn Gamified 3D VIP
    function showToast(message, type = 'success') {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = `toast-msg toast-${type}`;

        let icon = '✓';
        if (type === 'error') icon = '✕';
        else if (type === 'warning') icon = '⚠️';
        else if (type === 'info') icon = 'ℹ️';

        toast.innerHTML = `
            <div class="toast-icon-wrap">${icon}</div>
            <div style="flex:1; line-height:1.4; font-size:13px; font-weight:800;">${message}</div>
            <button type="button" class="toast-close-btn" onclick="this.closest('.toast-msg').remove()" title="Đóng">✕</button>
        `;

        container.appendChild(toast);

        setTimeout(() => {
            if (toast.parentElement) {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(50px) scale(0.92)';
                setTimeout(() => toast.remove(), 280);
            }
        }, 4200);
    }

    function switchLevel(levelId) {
        const opt = document.querySelector(`#level_select option[value="${levelId}"]`);
        const grade = opt ? opt.dataset.grade : 3;
        window.location.href = `{{ route('admin.mock-tests.create') }}?grade=${grade}`;
    }

    // Khởi động trang & Lắng nghe Session Messages
    document.addEventListener('DOMContentLoaded', () => {
        updateCounters();

        @if(session('ok'))
            showToast(@json(session('ok')), 'success');
        @endif
        @if(session('error'))
            showToast(@json(session('error')), 'error');
        @endif
        @if($errors->any())
            @foreach($errors->all() as $error)
                showToast(@json($error), 'error');
            @endforeach
        @endif
    });
</script>

<!-- Toast Container 3D Gamified -->
<div id="toast-container" class="toast-container"></div>
</body>
</html>
