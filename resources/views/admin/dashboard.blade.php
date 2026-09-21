{{-- Trang quản trị tổng quan; AdminController chuẩn bị số liệu theo vai trò người đang đăng nhập. --}}
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản trị IC3 Quest — Trung tâm điều hành & Kết quả học tập</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --primary-light: #eef2ff;
            --primary-border: #c7d2fe;
            --bg-body: #f8fafc;
            --surface: #ffffff;
            --border: #e2e8f0;
            --border-strong: #cbd5e1;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --text-light: #94a3b8;
            --success: #10b981;
            --success-bg: #ecfdf5;
            --success-border: #a7f3d0;
            --warning: #f59e0b;
            --warning-bg: #fffbeb;
            --warning-border: #fde68a;
            --danger: #ef4444;
            --danger-bg: #fef2f2;
            --danger-border: #fecaca;
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 20px;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 12px -2px rgba(15, 23, 42, 0.08), 0 2px 6px -2px rgba(15, 23, 42, 0.04);
            --shadow-lg: 0 10px 25px -4px rgba(15, 23, 42, 0.1), 0 6px 10px -4px rgba(15, 23, 42, 0.05);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: radial-gradient(circle at 10% 15%, rgba(254, 240, 138, 0.35) 0%, transparent 45%),
                        radial-gradient(circle at 90% 85%, rgba(199, 210, 254, 0.38) 0%, transparent 45%),
                        radial-gradient(circle at 50% 50%, rgba(240, 253, 250, 0.35) 0%, transparent 60%),
                        #f8fafc;
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

        /* 🔄 SIDEBAR TOGGLE BUTTON */
        .btn-sidebar-toggle {
            width: 40px;
            height: 40px;
            border-radius: var(--radius-sm);
            border: 1.5px solid var(--border);
            background: #f8fafc;
            color: #1e293b;
            font-size: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.15s;
            flex-shrink: 0;
            box-shadow: var(--shadow-sm);
        }
        .btn-sidebar-toggle:hover {
            background: #e2e8f0;
            color: #0f172a;
            border-color: var(--border-strong);
            transform: scale(1.05);
        }

        /* ==========================================================================
           🌌 SHELL & SIDEBAR GRID LAYOUT (ĐỒNG BỘ 100% VỚI COMPONENT)
           ========================================================================== */
        .shell.sidebar-collapsed {
            grid-template-columns: 70px minmax(0, 1fr);
        }

        /* ==========================================================================
           💻 MAIN WORKSPACE & TOPBAR
           ========================================================================== */
        .main { min-width: 0; }
        .top {
            padding: 14px 32px;
            background: #ffffff;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 40;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        }
        .top h1 { font-size: 18.5px; font-weight: 900; color: #0f172a; letter-spacing: -0.3px; }
        .top p { margin-top: 2px; color: var(--text-muted); font-size: 12.5px; }
        .top-right { display: flex; align-items: center; gap: 12px; }

        .topbar-user-card {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 5px 12px 5px 7px;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.18s ease;
            box-shadow: 0 1px 2px rgba(0,0,0,0.03);
            text-decoration: none;
        }
        .topbar-user-card:hover {
            background: #ffffff;
            border-color: #cbd5e1;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
            transform: translateY(-1px);
        }
        .topbar-avatar-wrap {
            position: relative;
            flex-shrink: 0;
        }
        .topbar-avatar {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            display: grid;
            place-items: center;
            color: #ffffff;
            font-weight: 900;
            font-size: 12.5px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.12);
        }
        .topbar-online-dot {
            position: absolute;
            bottom: -1px;
            right: -1px;
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: #10b981;
            border: 2px solid #ffffff;
            box-shadow: 0 0 6px #10b981;
        }
        .topbar-user-info {
            text-align: left;
            line-height: 1.25;
        }
        .topbar-user-name {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 13px;
            font-weight: 800;
            color: #0f172a;
            white-space: nowrap;
        }
        .topbar-edit-icon {
            font-size: 10px;
            opacity: 0.6;
            transition: opacity 0.15s;
        }
        .topbar-user-card:hover .topbar-edit-icon {
            opacity: 1;
        }
        .topbar-role-badge {
            font-size: 10.5px;
            font-weight: 800;
            margin-top: 1px;
            display: inline-block;
            white-space: nowrap;
        }
        .topbar-role-badge.role-admin {
            color: #dc2626;
        }
        .topbar-role-badge.role-teacher {
            color: #059669;
        }

        .badge-brand {
            padding: 6px 13px;
            border: 1.5px solid #fde68a;
            border-radius: 999px;
            background: #fffbeb;
            color: #b45309;
            font-weight: 800;
            font-size: 11.5px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            white-space: nowrap;
        }
        .role-pill {
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .role-pill.admin { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
        .role-pill.teacher { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }

        .content {
            padding: 24px 32px 48px;
            max-width: 1560px;
            margin: 0 auto;
        }

        /* Context Alerts */
        .alert-ok {
            padding: 12px 18px;
            background: var(--success-bg);
            border: 1.5px solid var(--success-border);
            color: #065f46;
            border-radius: var(--radius-md);
            margin-bottom: 20px;
            font-weight: 700;
            font-size: 13.5px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: var(--shadow-sm);
        }
        .alert-teacher {
            background: #ffffff;
            border: 1px solid #a7f3d0;
            border-left: 4px solid #10b981;
            color: #065f46;
            padding: 10px 16px;
            border-radius: var(--radius-sm);
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: var(--shadow-sm);
        }
        .alert-teacher-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: #10b981;
            color: #ffffff;
            display: grid;
            place-items: center;
            font-size: 16px;
            flex-shrink: 0;
        }

        /* ==========================================================================
           🌟 TABS NAVIGATION
           ========================================================================== */
        .admin-main-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 20px;
            border-bottom: 1.5px solid var(--border);
            padding-bottom: 10px;
            flex-wrap: wrap;
        }
        .main-tab-btn {
            padding: 8px 16px;
            border-radius: var(--radius-sm);
            border: 1.5px solid transparent;
            background: #ffffff;
            color: var(--text-muted);
            font-size: 13px;
            font-weight: 800;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.15s ease;
            box-shadow: var(--shadow-sm);
            border-color: var(--border);
        }
        .main-tab-btn:hover {
            color: var(--text-main);
            border-color: var(--border-strong);
            transform: translateY(-1px);
        }
        .main-tab-btn.active {
            background: var(--primary);
            color: #ffffff;
            border-color: var(--primary);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.28);
        }

        /* ==========================================================================
           📊 STATS METRIC CARDS (3D Gamified, Rich Gradient Accents, No AI Look)
           ========================================================================== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .stat-card {
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            border-radius: 16px;
            padding: 16px 20px;
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04), 0 1px 3px rgba(15, 23, 42, 0.02);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: #cbd5e1;
            transition: height 0.2s ease;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 26px rgba(15, 23, 42, 0.08), 0 2px 6px rgba(15, 23, 42, 0.04);
            border-color: #cbd5e1;
        }
        .stat-card:hover::before {
            height: 5px;
        }

        /* Từng tone màu chuyên biệt: Gradient góc nhẹ & Accent Bar sắc nét */
        .stat-card.c-purple {
            border-color: #e9d5ff;
            background: radial-gradient(circle at top right, rgba(168, 85, 247, 0.09) 0%, #ffffff 65%);
        }
        .stat-card.c-purple::before {
            background: linear-gradient(90deg, #7c3aed, #a855f7);
        }
        .stat-card.c-blue {
            border-color: #bae6fd;
            background: radial-gradient(circle at top right, rgba(2, 132, 199, 0.09) 0%, #ffffff 65%);
        }
        .stat-card.c-blue::before {
            background: linear-gradient(90deg, #0284c7, #38bdf8);
        }
        .stat-card.c-emerald, .stat-card.c-green {
            border-color: #a7f3d0;
            background: radial-gradient(circle at top right, rgba(16, 185, 129, 0.09) 0%, #ffffff 65%);
        }
        .stat-card.c-emerald::before, .stat-card.c-green::before {
            background: linear-gradient(90deg, #059669, #34d399);
        }
        .stat-card.c-amber {
            border-color: #fde68a;
            background: radial-gradient(circle at top right, rgba(245, 158, 11, 0.09) 0%, #ffffff 65%);
        }
        .stat-card.c-amber::before {
            background: linear-gradient(90deg, #d97706, #fbbf24);
        }

        .stat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .stat-label {
            font-size: 11px;
            font-weight: 850;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }
        .stat-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: grid;
            place-items: center;
            font-size: 18px;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.8);
        }
        .c-purple .stat-icon { background: #f3e8ff; color: #7c3aed; }
        .c-blue .stat-icon { background: #e0f2fe; color: #0284c7; }
        .c-amber .stat-icon { background: #fef3c7; color: #d97706; }
        .c-emerald .stat-icon, .c-green .stat-icon { background: #ecfdf5; color: #059669; }

        .stat-num, .stat-value {
            font-size: 26px;
            font-weight: 950;
            color: #0f172a;
            margin: 4px 0 6px;
            line-height: 1.1;
            letter-spacing: -0.5px;
        }
        .stat-desc, .stat-footer {
            font-size: 12px;
            color: #64748b;
            font-weight: 600;
            line-height: 1.35;
        }


        /* ==========================================================================
           📦 CARDS & PANELS
           ========================================================================== */
        .card {
            background: #ffffff;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            overflow: visible;
            margin-bottom: 24px;
        }
        /* Chỉ các card có bảng cần clip border-radius */
        .card > .table-responsive { border-radius: 0 0 var(--radius-lg) var(--radius-lg); overflow: hidden; }
        .card-header-row {
            padding: 18px 24px;
            background: #ffffff;
            border-bottom: 1.5px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }
        .card-title {
            font-size: 17px;
            font-weight: 800;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .card-subtitle {
            font-size: 12.5px;
            color: var(--text-muted);
            margin-top: 3px;
        }

        /* Action Buttons */
        .btn-primary {
            padding: 9px 18px;
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            color: #ffffff;
            border: none;
            border-radius: var(--radius-sm);
            font-size: 13px;
            font-weight: 800;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
            transition: all 0.15s ease;
        }
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(79, 70, 229, 0.4);
        }
        .btn-secondary {
            padding: 9px 16px;
            background: #f1f5f9;
            color: var(--text-main);
            border: 1.5px solid var(--border-strong);
            border-radius: var(--radius-sm);
            font-size: 13px;
            font-weight: 800;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.15s;
        }
        .btn-secondary:hover {
            background: #e2e8f0;
        }

        /* ==========================================================================
           📝 COLLAPSIBLE FORM DRAWER (Clean & Structured)
           ========================================================================== */
        .form-collapse-panel {
            background: #f8fafc;
            border-bottom: 1.5px solid var(--border);
            padding: 22px 24px;
            display: none;
            animation: slideDown 0.2s ease-out;
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .form-collapse-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
            padding-bottom: 10px;
            border-bottom: 1px dashed var(--border-strong);
        }
        .form-collapse-title {
            font-size: 14px;
            font-weight: 800;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px 20px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .form-group label {
            font-size: 12.5px;
            font-weight: 800;
            color: #334155;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 20px;
            margin: 0;
            line-height: 1.2;
        }
        .form-group label .label-title {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .form-group label .label-hint {
            font-size: 11px;
            color: #64748b;
            font-weight: 600;
        }
        .form-group label span.req { color: var(--danger); font-size: 13px; }
        .form-control {
            width: 100%;
            height: 42px;
            box-sizing: border-box;
            padding: 0 14px;
            border: 1.5px solid var(--border-strong);
            border-radius: var(--radius-sm);
            font-size: 13.5px;
            font-family: inherit;
            background: #ffffff;
            color: var(--text-main);
            outline: none;
            transition: all 0.15s;
        }
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
        }
        .form-level-box {
            grid-column: 1 / -1;
            background: #ffffff;
            border: 1.5px dashed var(--border-strong);
            border-radius: var(--radius-sm);
            padding: 12px 16px;
            margin-top: 4px;
        }
        .form-level-box b {
            font-size: 12px;
            color: #1e293b;
            display: block;
            margin-bottom: 8px;
            font-weight: 800;
        }
        .checkbox-chips {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
        }
        .chip-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            background: #f8fafc;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-sm);
            font-size: 12.5px;
            font-weight: 700;
            color: #334155;
            cursor: pointer;
            transition: all 0.15s;
        }
        .chip-label:hover {
            border-color: var(--primary);
            background: #eef2ff;
        }
        .chip-label input { accent-color: var(--primary); width: 16px; height: 16px; }

        .form-actions {
            grid-column: 1 / -1;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 8px;
            padding-top: 14px;
            border-top: 1px dashed var(--border-strong);
        }

        /* ==========================================================================
           📊 TABLE & SEARCH BAR
           ========================================================================== */
        .search-wrap {
            position: relative;
            display: inline-block;
        }
        .search-input {
            padding: 8px 14px 8px 34px;
            border: 1.5px solid var(--border-strong);
            border-radius: var(--radius-sm);
            font-size: 13px;
            outline: none;
            width: 250px;
            font-family: inherit;
            transition: all 0.15s;
            background: #ffffff;
        }
        .search-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
            width: 280px;
        }
        .search-icon {
            position: absolute;
            left: 11px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            font-size: 13px;
            pointer-events: none;
        }

        .filter-tab-group {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }
        .filter-tab-btn {
            padding: 6px 12px;
            border-radius: var(--radius-sm);
            border: 1.5px solid var(--border);
            background: #ffffff;
            color: var(--text-muted);
            font-size: 12px;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.15s;
        }
        .filter-tab-btn:hover {
            color: var(--text-main);
            border-color: var(--border-strong);
        }
        .filter-tab-btn.active {
            background: var(--primary);
            color: #ffffff;
            border-color: var(--primary);
        }

        /* ==========================================================================
           📊 EXCEL / SPREADSHEET GRID TABLE (Chuẩn lưới Excel đậm nét, rõ ràng Row & Column)
           ========================================================================== */
        .table-responsive,
        .excel-table-wrap {
            overflow-x: auto;
            width: 100%;
            -webkit-overflow-scrolling: touch;
            border: 2px solid #94a3b8; /* Viền ngoài đậm nét rõ ràng */
            border-radius: 12px;
            background: #ffffff;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.08);
        }
        
        table,
        .modal-roster-table,
        .user-table {
            width: 100% !important;
            border-collapse: separate !important;
            border-spacing: 0 !important;
            text-align: left;
            font-size: 12.5px;
            background: #ffffff;
        }
        
        /* THEAD - Chuẩn thanh tiêu đề bảng tính Excel đậm nét */
        thead th,
        .modal-roster-table thead th,
        .user-table thead th {
            padding: 11px 12px !important;
            background: #e2e8f0 !important; /* Nền xám đậm nổi rõ thanh header */
            color: #0f172a !important; /* Chữ đen đậm rõ */
            font-size: 11.5px !important;
            font-weight: 900 !important;
            letter-spacing: 0.6px !important;
            text-transform: uppercase !important;
            border-bottom: 2.5px solid #64748b !important; /* Kẻ ngang dưới header đậm */
            border-right: 1.5px solid #94a3b8 !important; /* Kẻ dọc ngăn cách cột đậm */
            white-space: nowrap !important;
            vertical-align: middle !important;
            position: sticky;
            top: 0;
            z-index: 2;
        }
        thead th:last-child,
        .modal-roster-table thead th:last-child,
        .user-table thead th:last-child {
            border-right: none !important;
        }

        /* TBODY - Từng cell kẻ lưới Excel đậm rõ từng hàng & cột */
        tbody tr,
        .modal-roster-table tbody tr,
        .user-table tbody tr {
            transition: background 0.12s ease-in-out;
            background: #ffffff;
        }
        /* Zebra striping xen kẽ đậm hơn để dễ phân biệt hàng */
        tbody tr:nth-child(even),
        .modal-roster-table tbody tr:nth-child(even),
        .user-table tbody tr:nth-child(even) {
            background: #f1f5f9;
        }
        /* Hover làm nổi bật cả dòng mượt mà */
        tbody tr:hover,
        .modal-roster-table tbody tr:hover,
        .user-table tbody tr:hover {
            background: #e0f2fe !important; /* Xanh sky sáng rõ */
        }

        tbody td,
        .modal-roster-table tbody td,
        .user-table tbody td {
            padding: 10px 12px !important;
            vertical-align: middle !important;
            border-bottom: 1.5px solid #cbd5e1 !important; /* Kẻ ngang đậm rõ */
            border-right: 1.5px solid #cbd5e1 !important; /* Kẻ dọc đậm rõ chuẩn Excel */
            color: #0f172a;
            font-size: 12.5px;
        }
        tbody td:last-child,
        .modal-roster-table tbody td:last-child,
        .user-table tbody td:last-child {
            border-right: none !important;
        }

        /* Cột số thứ tự # - Chuẩn số hàng của Excel (áp dụng cho cột col-stt) */
        tbody td.col-stt,
        thead th.col-stt,
        .modal-roster-table td.col-stt,
        .modal-roster-table th.col-stt {
            text-align: center !important;
            background: #e2e8f0 !important;
            font-weight: 900;
            color: #334155;
            border-right: 2px solid #64748b !important;
        }
        tbody tr:hover td.col-stt {
            background: #bae6fd !important;
            color: #0369a1 !important;
        }

        /* Định dạng riêng cho cột Người dùng: Căn trái tự nhiên, không bị áp đặt style cột số thứ tự */
        .col-user,
        #users-data-table td.col-user,
        #users-data-table th.col-user,
        #users-data-table td:first-child {
            text-align: left !important;
            background: transparent;
            border-right: 1.5px solid #cbd5e1 !important;
        }
        #users-data-table thead th.col-user,
        #users-data-table thead th:first-child {
            background: #e2e8f0 !important;
            border-right: 1.5px solid #94a3b8 !important;
            text-align: left !important;
        }

        /* Căn giữa tiện ích cho các ô */
        .text-center,
        th.text-center,
        td.text-center {
            text-align: center !important;
        }
        .text-right,
        th.text-right,
        td.text-right {
            text-align: right !important;
        }

        /* 🏷️ PILL BADGES */
        .pill-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 9px;
            border-radius: 999px;
            font-size: 11.5px;
            font-weight: 800;
            white-space: nowrap;
            flex-shrink: 0;
            line-height: 1.2;
        }
        .pill-grade { background: #ede9fe; color: #6d28d9; }
        .pill-code { background: #e0f2fe; color: #0369a1; font-family: ui-monospace, monospace; font-size: 11px; }
        .pill-time { background: #f1f5f9; color: #475569; }
        .pill-pass { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
        .pill-perfect { background: #fef9c3; color: #854d0e; border: 1px solid #fde047; font-weight: 900; }
        .pill-fail { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }

        .pill-role-admin { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
        .pill-role-teacher { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
        .pill-role-student { background: #f5f3ff; color: #7c3aed; border: 1px solid #ddd6fe; }

        /* 🟢 ONLINE PULSE ANIMATION & SUSPENDED STYLES */
        .status-dot-online {
            width: 7.5px;
            height: 7.5px;
            background-color: #10b981;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            animation: pulse-dot 1.8s infinite cubic-bezier(0.66, 0, 0, 1);
            flex-shrink: 0;
        }
        @keyframes pulse-dot {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            }
            70% {
                transform: scale(1.1);
                box-shadow: 0 0 0 6px rgba(16, 185, 129, 0);
            }
            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
            }
        }

        .avatar-box-wrap {
            position: relative;
            display: inline-block;
            flex-shrink: 0;
        }
        .avatar-online-badge {
            position: absolute;
            bottom: -2px;
            right: -2px;
            width: 12px;
            height: 12px;
            background: #10b981;
            border: 2px solid #ffffff;
            border-radius: 50%;
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.6);
            animation: pulse-dot 1.8s infinite;
        }
        .avatar-suspended-badge {
            position: absolute;
            bottom: -3px;
            right: -3px;
            width: 15px;
            height: 15px;
            background: #ef4444;
            color: #ffffff;
            border: 2px solid #ffffff;
            border-radius: 50%;
            display: grid;
            place-items: center;
            font-size: 8px;
            font-weight: 900;
        }

        .user-row-suspended {
            background: #fff8f8 !important;
            border-left: 4px solid #ef4444 !important;
        }
        .user-row-suspended:hover {
            background: #fee2e2 !important;
        }

        /* 🛠️ TABLE ACTION BUTTONS VIP — TACTILE, COMPACT & SLEEK */
        .action-btn-group {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            justify-content: center;
            flex-wrap: nowrap;
        }
        .btn-action-edit, .btn-action-grant, .btn-action-view, .btn-action-delete {
            padding: 4px 8px;
            border-radius: 7px;
            font-size: 11px;
            font-weight: 850;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            white-space: nowrap;
            line-height: 1.2;
            transition: all 0.15s ease;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
            text-decoration: none;
            flex-shrink: 0;
            border-width: 1.5px;
            border-style: solid;
        }
        .btn-action-edit {
            background: #ffffff;
            color: #334155;
            border-color: #cbd5e1;
        }
        .btn-action-edit:hover {
            background: #f1f5f9;
            color: #0f172a;
            border-color: #94a3b8;
            transform: translateY(-1px);
        }
        .btn-action-grant {
            background: #eef2ff;
            color: #4338ca;
            border-color: #c7d2fe;
        }
        .btn-action-grant:hover {
            background: #4338ca;
            color: #ffffff;
            border-color: #4338ca;
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(67, 56, 202, 0.25);
        }
        .btn-action-view {
            background: #eef2ff;
            color: #4338ca;
            border-color: #c7d2fe;
        }
        .btn-action-view:hover {
            background: #4338ca;
            color: #ffffff;
            border-color: #4338ca;
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(67, 56, 202, 0.28);
        }
        .btn-action-view:active, .btn-action-edit:active, .btn-action-grant:active, .btn-action-delete:active {
            transform: translateY(1px);
        }
        .btn-action-delete {
            background: #fff1f2;
            color: #e11d48;
            border-color: #fecdd3;
        }
        .btn-action-delete:hover {
            background: #e11d48;
            color: #ffffff;
            border-color: #e11d48;
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(225, 29, 72, 0.25);
        }

        /* Level Hero Cards VIP — Vibrant & High Contrast */
        .level-hero-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 28px rgba(0, 0, 0, 0.08), 0 2px 6px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .level-hero-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 18px 38px rgba(0, 0, 0, 0.13), 0 4px 10px rgba(0, 0, 0, 0.06);
        }

        .level-hero-header {
            padding: 20px 20px 18px;
            color: #ffffff;
            position: relative;
            overflow: hidden;
        }
        .level-hero-header::after {
            content: '';
            position: absolute;
            top: -24px;
            right: -24px;
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 50%;
            pointer-events: none;
        }

        .level-hero-topline {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
            position: relative;
            z-index: 2;
        }
        .level-hero-badge {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(8px);
            border: 1.5px solid rgba(255, 255, 255, 0.5);
            color: #ffffff;
            font-size: 13px;
            font-weight: 900;
            padding: 5px 14px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.25);
        }
        .level-hero-program {
            font-size: 11px;
            font-weight: 850;
            background: rgba(0, 0, 0, 0.28);
            color: #ffffff;
            padding: 4px 10px;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.35);
        }

        .level-hero-title {
            font-size: 17px;
            font-weight: 900;
            color: #ffffff;
            margin: 0;
            line-height: 1.35;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.35);
            position: relative;
            z-index: 2;
        }

        .level-hero-body {
            padding: 18px 20px 20px;
            display: flex;
            flex-direction: column;
            flex: 1;
            justify-content: space-between;
            background: #ffffff;
        }

        .level-metrics-3grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-bottom: 18px;
        }
        .metric-tile {
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            padding: 12px 6px;
            text-align: center;
            transition: all 0.15s ease;
        }
        .metric-tile:hover {
            background: #ffffff;
            border-color: #cbd5e1;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        .metric-icon {
            font-size: 16px;
            display: block;
            margin-bottom: 2px;
        }
        .metric-num {
            font-size: 20px;
            font-weight: 900;
            color: #0f172a;
            display: block;
            line-height: 1.2;
        }
        .metric-lbl {
            font-size: 10px;
            font-weight: 850;
            color: #64748b;
            letter-spacing: 0.5px;
            display: block;
            margin-top: 4px;
        }

        .level-hero-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-level-primary {
            flex: 1.2;
            padding: 9px 12px;
            border-radius: 10px;
            color: #ffffff;
            font-size: 13px;
            font-weight: 900;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transition: all 0.15s ease;
            border: none;
        }
        .btn-level-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.22);
            color: #ffffff;
        }
        .btn-level-preview {
            flex: 1;
            padding: 9px 10px;
            border-radius: 10px;
            background: #f1f5f9;
            color: #334155;
            border: 1.5px solid #cbd5e1;
            font-size: 12.5px;
            font-weight: 850;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .btn-level-preview:hover {
            background: #e2e8f0;
            color: #0f172a;
            border-color: #94a3b8;
            transform: translateY(-2px);
        }
        .btn-level-edit, .btn-level-del {
            padding: 8px 11px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 800;
            border: 1.5px solid #e2e8f0;
            background: #f8fafc;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s ease;
        }
        .btn-level-edit:hover {
            background: #e2e8f0;
            border-color: #cbd5e1;
            transform: translateY(-2px);
        }
        .btn-level-del {
            background: #fff1f2;
            color: #e11d48;
            border-color: #fecdd3;
        }
        .btn-level-del:hover {
            background: #ffe4e6;
            border-color: #fda4af;
            transform: translateY(-2px);
        }

        .badge-current-user {
            padding: 5px 10px;
            border-radius: 7px;
            font-size: 11.5px;
            font-weight: 800;
            background: #ecfdf5;
            color: #059669;
            border: 1.5px solid #a7f3d0;
            display: inline-flex;
            align-items: center;
        }
        .card-toolbar {
            padding: 12px 24px;
            background: #f8fafc;
            border-bottom: 1.5px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
        }

        .btn-inline-save {
            padding: 5px 10px;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 6px;
            font-weight: 800;
            font-size: 11.5px;
            cursor: pointer;
            transition: 0.15s;
        }
        .btn-inline-save:hover { background: var(--primary-hover); }
        .btn-inline-del {
            padding: 5px 8px;
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
            border-radius: 6px;
            font-weight: 800;
            font-size: 13px;
            cursor: pointer;
            transition: 0.15s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
            vertical-align: middle;
        }
        .btn-inline-del:hover { background: #dc2626; color: #fff; border-color: #dc2626; }

        .score-bar-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .score-bar-track {
            flex: 1;
            height: 6px;
            background: #e2e8f0;
            border-radius: 999px;
            overflow: hidden;
            min-width: 50px;
            max-width: 90px;
        }
        .score-bar-fill { height: 100%; border-radius: 999px; }

        /* 🚀 ADVANCED ANALYTICS & REPORTING TOOLBAR */
        .btn-excel {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 16px;
            background: linear-gradient(135deg, #047857 0%, #10b981 100%);
            color: #ffffff !important;
            font-size: 12.5px;
            font-weight: 850;
            border-radius: 9px;
            text-decoration: none;
            border: 1.5px solid #059669;
            cursor: pointer;
            box-shadow: 0 3px 10px rgba(16, 185, 129, 0.3);
            transition: all 0.15s ease;
            line-height: 1.2;
        }
        .btn-excel:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4);
            color: #ffffff !important;
        }
        .btn-excel:active {
            transform: translateY(1px);
        }
        .btn-ghost {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            background: #ffffff;
            color: #334155;
            font-size: 12.5px;
            font-weight: 850;
            border-radius: 9px;
            border: 1.5px solid #cbd5e1;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
            line-height: 1.2;
        }
        .btn-ghost:hover {
            background: #f8fafc;
            color: #0f172a;
            border-color: #94a3b8;
            transform: translateY(-1px);
        }
        .btn-ghost:active {
            transform: translateY(1px);
        }

        /* 📈 TOPIC MASTERY MATRIX (Thẻ đánh giá năng lực đa sắc màu sắc nét) */
        .mastery-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 14px;
            margin-top: 14px;
        }
        .mastery-item {
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            padding: 14px 18px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
        }
        .mastery-item:hover {
            background: #ffffff;
            border-color: #cbd5e1;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
            transform: translateY(-2px);
        }
        .mastery-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        .mastery-title {
            font-size: 13px;
            font-weight: 850;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .mastery-score {
            font-size: 14px;
            font-weight: 950;
            padding: 2px 8px;
            border-radius: 6px;
            background: #f8fafc;
        }
        .mastery-track {
            height: 9px;
            background: #e2e8f0;
            border-radius: 999px;
            overflow: hidden;
            margin-top: 8px;
            border: 1px solid #cbd5e1;
        }
        .mastery-fill {
            height: 100%;
            border-radius: 999px;
            transition: width 0.4s ease;
        }
        .mastery-footer {
            display: flex;
            justify-content: space-between;
            font-size: 11.5px;
            color: #64748b;
            margin-top: 8px;
            font-weight: 750;
        }

        /* 🔍 MULTI-FILTER TOOLBAR */
        .multi-filter-toolbar {
            background: #f8fafc;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-md);
            padding: 14px 18px;
            margin-bottom: 18px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .filter-row-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }
        .filter-row-controls {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 10px;
            align-items: center;
        }
        .filter-select {
            padding: 8px 12px;
            border-radius: var(--radius-sm);
            border: 1.5px solid var(--border-strong);
            background: #ffffff;
            font-size: 12.5px;
            font-weight: 750;
            color: var(--text-main);
            outline: none;
            cursor: pointer;
            transition: all 0.15s;
            width: 100%;
        }
        .filter-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
        }

        @media print {
            .side, .top, .admin-main-tabs, .multi-filter-toolbar, .action-bar-wrap, .btn-action-view, .btn-inline-del, .logout {
                display: none !important;
            }
            .shell { grid-template-columns: 1fr !important; }
            .content { padding: 0 !important; max-width: 100% !important; }
            .card { box-shadow: none !important; border: 1px solid #ccc !important; }
        }

        /* ==========================================================================
           🔑 MODAL DIALOG (CẤP QUYỀN KHỐI HỌC CHO HỌC SINH)
           ========================================================================== */
        .modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            display: none;
            place-items: center;
            z-index: 1000;
            padding: 20px;
            animation: fadeIn 0.15s ease-out;
        }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

        /* 🍞 FLOATING TOAST NOTIFICATION */
        .toast-container {
            position: fixed;
            top: 24px;
            right: 24px;
            z-index: 99999;
            display: flex;
            flex-direction: column;
            gap: 10px;
            pointer-events: none;
        }
        .toast-msg {
            padding: 12px 18px;
            font-size: 13.5px;
            font-weight: 800;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            gap: 10px;
            animation: toastSlideIn 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            pointer-events: auto;
        }
        .toast-success { background: #065f46; border: 1.5px solid #34d399; color: #ecfdf5; }
        .toast-error { background: #991b1b; border: 1.5px solid #f87171; color: #fef2f2; }
        @keyframes toastSlideIn {
            from { opacity: 0; transform: translateX(40px) scale(0.95); }
            to { opacity: 1; transform: translateX(0) scale(1); }
        }
        .modal-box {
            background: #ffffff;
            border-radius: var(--radius-lg);
            width: min(460px, 100%);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            border: 1.5px solid var(--border);
        }
        .modal-header {
            padding: 16px 20px;
            background: #f8fafc;
            border-bottom: 1.5px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .modal-header h3 { font-size: 15px; font-weight: 900; color: #0f172a; display: flex; align-items: center; gap: 8px; }
        .modal-close-btn {
            background: none;
            border: none;
            font-size: 18px;
            color: var(--text-muted);
            cursor: pointer;
            padding: 4px;
            border-radius: 6px;
        }
        .modal-close-btn:hover { background: #e2e8f0; color: #0f172a; }
        .modal-body { padding: 20px; }
        .modal-footer {
            padding: 14px 20px;
            background: #f8fafc;
            border-top: 1.5px solid var(--border);
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        /* 🎨 ENHANCED MODAL FORM CONTROLS & COLOR PICKER PRESETS */
        .color-picker-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 6px;
        }
        .color-preview-box {
            width: 44px;
            height: 38px;
            border-radius: 8px;
            border: 1.5px solid #cbd5e1;
            padding: 2px;
            cursor: pointer;
            flex-shrink: 0;
            background: none;
        }
        .color-preset-list {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }
        .color-preset-btn {
            width: 28px;
            height: 28px;
            border-radius: 7px;
            border: 2px solid #ffffff;
            cursor: pointer;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
            transition: all 0.15s ease;
        }
        .color-preset-btn:hover {
            transform: scale(1.18);
            box-shadow: 0 3px 8px rgba(0,0,0,0.3);
        }
        .modal-field-hint {
            display: block;
            font-size: 11.5px;
            color: #64748b;
            margin-top: 4px;
            line-height: 1.35;
        }
        .modal-section-badge {
            background: #eef2ff;
            color: #4f46e5;
            font-size: 11px;
            font-weight: 850;
            padding: 3px 8px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            margin-bottom: 12px;
        }

        @media (max-width: 1024px) {
            .shell { grid-template-columns: 1fr; }
            .side { display: none; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
            .content { padding: 16px; }
            .top { padding: 16px; }
        }
        @media (max-width: 640px) {
            .stats-grid { grid-template-columns: 1fr; }
            .top { flex-direction: column; align-items: flex-start; gap: 10px; }
        }
    </style>
</head>
<body>
<div class="toast-container" id="toast-container"></div>
<div class="shell">

    <!-- =======================================================================
         🌌 SIDEBAR NAVIGATION
         ======================================================================= -->
    <!-- =======================================================================
         🌌 SIDEBAR NAVIGATION (ĐỒNG BỘ TOÀN BỘ CÁC TRANG ADMIN)
         ======================================================================= -->
    <x-admin-sidebar active-tab="tab-results" active-group="reports" />

    <!-- =======================================================================
         💻 MAIN WORKSPACE
         ======================================================================= -->
    <div class="main">
        <!-- TOPBAR THÔNG MINH TRỰC QUAN -->
        <x-admin-topbar />

        <main class="content">

            @if(session('ok'))
                <div class="alert-ok">
                    <span>✓</span>
                    <div>{{ session('ok') }}</div>
                </div>
            @endif

            @if($isTeacher)
                <div id="teacher-global-banner" style="background: linear-gradient(135deg, #064e3b, #047857); border-radius: 18px; padding: 20px 24px; color: #fff; margin-bottom: 24px; box-shadow: 0 10px 25px rgba(4, 120, 87, 0.25); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <div style="width: 52px; height: 52px; border-radius: 16px; background: rgba(255,255,255,0.2); border: 2px solid rgba(255,255,255,0.4); display: grid; place-items: center; font-size: 26px;">
                            👩‍🏫
                        </div>
                        <div>
                            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                <h2 style="font-size: 18px; font-weight: 900; margin: 0; color: #fff;">Không gian Giảng dạy — {{ auth()->user()->name }}</h2>
                                <span style="background: #a7f3d0; color: #065f46; font-weight: 800; font-size: 11px; padding: 3px 9px; border-radius: 99px;">
                                    {{ $isSubActive ? '● GÓI ĐANG HOẠT ĐỘNG' : '● HẾT HẠN SỬ DỤNG' }}
                                </span>
                            </div>
                            <div style="font-size: 13px; color: #d1fae5; margin-top: 5px; display: flex; gap: 14px; flex-wrap: wrap;">
                                <span>📅 Hạn dùng: <b>{{ $expiresAt ? $expiresAt->format('d/m/Y') : 'Không giới hạn' }}</b></span>
                                <span>🔑 Khối được cấp: 
                                    @forelse($teacherLevels as $tl)
                                        <span style="background: rgba(255,255,255,0.25); color: #fff; padding: 1px 6px; border-radius: 4px; font-weight: 800; font-size: 11.5px;">Khối {{ $tl->grade }}</span>
                                    @empty
                                        <span style="color: #fecaca;">(Chưa được cấp khối nào)</span>
                                    @endforelse
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Quota Bar -->
                    <div style="min-width: 240px; background: rgba(0,0,0,0.15); padding: 12px 18px; border-radius: 14px; border: 1px solid rgba(255,255,255,0.15);">
                        <div style="display: flex; justify-content: space-between; font-size: 12.5px; font-weight: 800; margin-bottom: 6px;">
                            <span>SĨ SỐ HỌC SINH</span>
                            <span style="color: #6ee7b7;">{{ $usedStudents }} / {{ $maxStudents ?: '∞' }} Học sinh</span>
                        </div>
                        <div style="height: 8px; background: rgba(255,255,255,0.2); border-radius: 99px; overflow: hidden;">
                            @php
                                $percent = $maxStudents > 0 ? min(100, round(($usedStudents / $maxStudents) * 100)) : 100;
                            @endphp
                            <div style="height: 100%; width: {{ $percent }}%; background: #34d399; border-radius: 99px; transition: width 0.4s ease;"></div>
                        </div>
                        <div style="font-size: 11px; color: #a7f3d0; margin-top: 4px; text-align: right;">
                            Còn lại: <b>{{ $remainingSlots > 10000 ? 'Không giới hạn' : $remainingSlots . ' suất' }}</b>
                        </div>
                    </div>

                    <!-- Nút Nâng cấp / Gia hạn gói nổi bật cho Giáo viên -->
                    <div style="display: flex; flex-direction: column; gap: 6px; justify-content: center;">
                        <a href="{{ route('pricing.index') }}" style="display: inline-flex; align-items: center; justify-content: center; gap: 7px; padding: 10px 18px; border-radius: 12px; background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; font-weight: 900; font-size: 13px; text-decoration: none; box-shadow: 0 4px 14px rgba(0,0,0,0.3); transition: transform 0.15s; white-space: nowrap;">
                            <span>✨</span> Nâng Cấp Gói
                        </a>
                        <a href="javascript:void(0)" onclick="switchAdminTab('tab-teacher-packages', this)" style="display: inline-flex; align-items: center; justify-content: center; gap: 5px; padding: 5px 12px; border-radius: 10px; background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3); color: #d1fae5; font-weight: 800; font-size: 11.5px; text-decoration: none; white-space: nowrap; cursor: pointer;">
                            <span>📜</span> Đơn của tôi
                        </a>
                    </div>
                </div>
            @endif

            <!-- ========================================================= -->
            <!-- TAB 1: 📊 TRUNG TÂM BÁO CÁO & PHÂN TÍCH ĐIỂM SỐ (TAB-RESULTS) -->
            <!-- ========================================================= -->
            <div id="tab-results" class="admin-tab-pane">
                
                <!-- 🚀 EXECUTIVE HEADER & ACTION TOOLBAR -->
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:14px;">
                    <div>
                        <h2 style="font-size:18.5px; font-weight:900; color:#0f172a; display:flex; align-items:center; gap:8px;">
                            <span>📊</span> {{ $isTeacher ? 'Báo Cáo Kết Quả Luyện Thi Học Sinh' : 'Trung Tâm Phân Tích & Báo Cáo Điểm Số IC3' }}
                        </h2>
                        <p style="font-size:13px; color:var(--text-muted); margin-top:3px;">
                            {{ $isTeacher ? 'Theo dõi năng lực từng chủ đề, điểm số và bài thi của học sinh lớp bạn' : 'Giám sát hoạt động kinh doanh, năng lực tiêu thụ Quota và lịch sử luyện thi toàn sàn' }}
                        </p>
                    </div>
                    <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                        <a href="{{ route('admin.attempts.export') }}" class="btn-excel" title="Tải xuống tệp Excel (CSV) đầy đủ danh sách kết quả">
                            <span>📥</span> Xuất Báo Cáo Excel (CSV)
                        </a>
                        <button type="button" onclick="window.print()" class="btn-ghost" title="In báo cáo / Lưu PDF">
                            <span>🖨️</span> In Bảng Điểm
                        </button>
                        <button type="button" onclick="window.location.reload()" class="btn-ghost" title="Tải lại dữ liệu">
                            <span>🔄</span>
                        </button>
                    </div>
                </div>

                @if(! $isTeacher)
                    <!-- 📊 4 THẺ CHỈ SỐ VÀNG B2B CHO ADMIN -->
                    <div class="stats-grid" style="margin-bottom: 28px;">
                        <div class="stat-card c-purple">
                            <div class="stat-header">
                                <span class="stat-label">TỔNG SỐ GIÁO VIÊN</span>
                                <div class="stat-icon">👩‍🏫</div>
                            </div>
                            <div class="stat-num">{{ $totalTeachersCount }}</div>
                            <div class="stat-desc">
                                <span style="color:#059669; font-weight:800;">🟢 {{ $activeTeachersCount }} Hoạt động</span>
                                @if($suspendedTeachersCount > 0)
                                    · <span style="color:#ef4444; font-weight:700;">🔒 {{ $suspendedTeachersCount }} Tạm khóa</span>
                                @endif
                            </div>
                        </div>

                        <div class="stat-card c-blue">
                            <div class="stat-header">
                                <span class="stat-label">CÔNG SUẤT SỬ DỤNG QUOTA</span>
                                <div class="stat-icon">👥</div>
                            </div>
                            <div class="stat-num">
                                {{ $totalActiveStudents }} <small style="font-size:13px; color:#94a3b8; font-weight:700;">/ {{ $totalQuotaAllocated ?: '∞' }} Quota</small>
                            </div>
                            <div class="stat-desc">
                                <div class="score-bar-track" style="margin-top:6px; height:7px; width:100%;">
                                    <div class="score-bar-fill" style="width: {{ min(100, $quotaUsagePercent) }}%; background: #10b981;"></div>
                                </div>
                                <div style="font-size:11px; color:#64748b; font-weight:700; margin-top:4px;">
                                    Đã kích hoạt {{ $quotaUsagePercent }}% công suất toàn sàn
                                </div>
                            </div>
                        </div>

                        <div class="stat-card c-amber">
                            <div class="stat-header">
                                <span class="stat-label">TÀI KHOẢN CẦN GIA HẠN</span>
                                <div class="stat-icon">⏳</div>
                            </div>
                            <div class="stat-num" style="color: {{ $expiringTeachersCount > 0 ? '#d97706' : '#10b981' }};">
                                {{ $expiringTeachersCount }} <small style="font-size:12px; color:#94a3b8; font-weight:700;">(trong 30 ngày)</small>
                            </div>
                            <div class="stat-desc">
                                @if($expiredTeachersCount > 0)
                                    <span style="color:#ef4444; font-weight:800;">⚠️ {{ $expiredTeachersCount }} tài khoản đã hết hạn</span>
                                @else
                                    <span style="color:#059669; font-weight:700;">✓ Các gói đang vận hành tốt</span>
                                @endif
                            </div>
                        </div>

                        <div class="stat-card c-emerald">
                            <div class="stat-header">
                                <span class="stat-label">LƯỢT THI TOÀN HỆ THỐNG</span>
                                <div class="stat-icon">⚡</div>
                            </div>
                            <div class="stat-num" style="color:#059669;">
                                {{ $totalAttemptsCount }}
                            </div>
                            <div class="stat-desc">
                                ⭐ Điểm TB: <b>{{ $avgScore }}/1000đ</b> · <b>{{ $passedAttemptsCount }}</b> lượt đạt chuẩn
                            </div>
                        </div>
                    </div>
                @else
                    <!-- 📊 4 THẺ CHỈ SỐ HỌC SINH CỦA GIÁO VIÊN -->
                    <div class="stats-grid" style="margin-bottom: 28px;">
                        <div class="stat-card c-purple">
                            <div class="stat-header">
                                <span class="stat-label">SĨ SỐ HỌC SINH</span>
                                <div class="stat-icon">👨‍🎓</div>
                            </div>
                            <div class="stat-num">{{ $usedStudents }} <small style="font-size:13px; color:#94a3b8;">/ {{ $maxStudents ?: '∞' }}</small></div>
                            <div class="stat-desc">
                                <span style="color:#6d28d9; font-weight:800;">Còn lại: {{ $remainingSlots > 10000 ? 'Không giới hạn' : $remainingSlots . ' suất' }}</span>
                            </div>
                        </div>

                        <div class="stat-card c-blue">
                            <div class="stat-header">
                                <span class="stat-label">ĐIỂM TRUNG BÌNH HỌC SINH</span>
                                <div class="stat-icon">⭐</div>
                            </div>
                            <div class="stat-num">
                                {{ $avgScore }} <small style="font-size:13px; color:#94a3b8; font-weight:700;">/ 1000đ</small>
                            </div>
                            <div class="stat-desc">
                                <div class="score-bar-track" style="margin-top:6px; height:7px; width:100%;">
                                    <div class="score-bar-fill" style="width: {{ min(100, $avgScore / 10) }}%; background: {{ $avgScore >= 700 ? '#10b981' : '#f59e0b' }};"></div>
                                </div>
                            </div>
                        </div>

                        <div class="stat-card c-emerald">
                            <div class="stat-header">
                                <span class="stat-label">TỶ LỆ ĐẠT CHUẨN IC3 (≥ 700đ)</span>
                                <div class="stat-icon">🎯</div>
                            </div>
                            <div class="stat-num" style="color:#059669;">
                                {{ $totalAttemptsCount > 0 ? round(($passedAttemptsCount / $totalAttemptsCount) * 100) : 0 }}%
                            </div>
                            <div class="stat-desc">
                                <b>{{ $passedAttemptsCount }}</b> / {{ $totalAttemptsCount }} lượt thi đạt chuẩn
                            </div>
                        </div>

                        <div class="stat-card c-amber">
                            <div class="stat-header">
                                <span class="stat-label">TỔNG LƯỢT THI LUYỆN</span>
                                <div class="stat-icon">📝</div>
                            </div>
                            <div class="stat-num" style="color:#d97706;">
                                {{ $totalAttemptsCount }}
                            </div>
                            <div class="stat-desc">
                                <b>{{ $perfectAttemptsCount }}</b> lượt đạt điểm tuyệt đối (1000đ)
                            </div>
                        </div>
                    </div>

                    <!-- 📈 BẢNG MA TRẬN NĂNG LỰC CÁC CHỦ ĐỀ IC3 (TOPIC MASTERY MATRIX) -->
                    @if(count($topicStats) > 0)
                    <section class="card" style="margin-bottom: 28px; padding: 20px 24px;">
                        <div class="card-header-row" style="margin-bottom: 12px;">
                            <div>
                                <h2 class="card-title" style="font-size: 15.5px;"><span>📈</span> Đánh Giá Năng Lực Theo Chủ Đề IC3 (Topic Mastery)</h2>
                                <p class="card-subtitle">Thống kê điểm trung bình & tỷ lệ đạt chuẩn của học sinh theo từng chủ đề</p>
                            </div>
                            <span class="pill-badge pill-grade" style="font-size:11.5px; padding:4px 10px;">{{ count($topicStats) }} Chủ đề</span>
                        </div>

                        <div class="mastery-container">
                            @foreach($topicStats as $ts)
                                @php
                                    $passRate = $ts['pass_rate'];
                                    $color = $passRate >= 80 ? '#10b981' : ($passRate >= 60 ? '#3b82f6' : '#ef4444');
                                    $gradeLabel = 'Khối ' . ($ts['topic']->level?->grade ?? '3');
                                @endphp
                                <div class="mastery-item" style="padding: 12px 16px;">
                                    <div class="mastery-header">
                                        <div class="mastery-title" title="{{ $ts['topic']->name }}" style="font-size: 13px;">
                                            <b style="color:#4f46e5;">{{ $gradeLabel }}:</b> {{ $ts['topic']->name }}
                                        </div>
                                        <div class="mastery-score" style="color: {{ $color }}; font-size: 13.5px;">
                                            {{ $ts['avg'] }}đ
                                        </div>
                                    </div>
                                    <div class="mastery-track" style="height: 7px; margin-top: 6px;">
                                        <div class="mastery-fill" style="width: {{ $passRate }}%; background: {{ $color }};"></div>
                                    </div>
                                    <div class="mastery-footer" style="margin-top: 6px; font-size: 11.5px;">
                                        <span>📝 {{ $ts['count'] }} lượt nộp</span>
                                        <span style="color: {{ $color }}; font-weight:800;">✓ {{ $passRate }}% Đạt chuẩn</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                    @endif
                @endif

                <!-- =========================================================
                     📝 NHẬT KÝ HOẠT ĐỘNG LUYỆN THI MỚI NHẤT (REALTIME FEED)
                     ========================================================= -->
                <section class="card" style="padding: 24px; overflow: visible;">
                    <div class="card-header-row" style="margin-bottom: 16px;">
                        <div>
                            <h2 class="card-title" style="font-size: 16.5px;">
                                <span>📝</span> {{ $isTeacher ? 'Nhật Ký Luyện Thi Của Học Sinh' : 'Nhật Ký Hoạt Động Luyện Thi Mới Nhất' }}
                            </h2>
                            <p class="card-subtitle">
                                {{ $isTeacher ? 'Theo dõi từng lượt làm bài, điểm số và câu trả lời chi tiết của học sinh' : 'Lịch sử nộp bài luyện thi thời gian thực của toàn bộ học sinh trên hệ thống' }}
                            </p>
                        </div>
                    </div>

                    <!-- 🔍 COMPACT MULTI-FILTER TOOLBAR -->
                    <div class="multi-filter-toolbar" style="padding: 14px 18px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 12px; margin-bottom: 20px;">
                        <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                            <!-- Ô tìm kiếm -->
                            <div class="search-wrap" style="flex: 2; min-width: 220px;">
                                <span class="search-icon">🔍</span>
                                <input type="text" id="filter-keyword" class="search-input" style="width: 100%; box-sizing: border-box; height: 38px;" placeholder="Tìm tên học sinh, mã HS, bài thi..." onkeyup="applyAdvancedResultsFilter()">
                            </div>

                            <!-- Lọc Khối -->
                            <select id="filter-grade" class="filter-select" style="width: auto; min-width: 120px; flex: 1; height: 38px;" onchange="applyAdvancedResultsFilter()">
                                <option value="">🎒 Tất cả Khối</option>
                                @foreach($levels as $lvl)
                                    <option value="{{ $lvl->grade }}">Khối {{ $lvl->grade }}</option>
                                @endforeach
                            </select>

                            <!-- Lọc Giáo viên -->
                            @if(! $isTeacher)
                            <select id="filter-teacher" class="filter-select" style="width: auto; min-width: 140px; flex: 1.2; height: 38px;" onchange="applyAdvancedResultsFilter()">
                                <option value="">👩‍🏫 Tất cả Giáo viên</option>
                                @foreach($teachers as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                                @endforeach
                            </select>
                            @endif

                            <!-- Lọc Chủ đề -->
                            <select id="filter-topic" class="filter-select" style="width: auto; min-width: 150px; flex: 1.3; height: 38px;" onchange="applyAdvancedResultsFilter()">
                                <option value="">📚 Tất cả Chủ đề</option>
                                @foreach($allTopics as $top)
                                    <option value="{{ $top->id }}">K{{ $top->level?->grade ?? 3 }}: {{ $top->name }}</option>
                                @endforeach
                            </select>

                            <!-- Lọc Xếp loại -->
                            <select id="filter-status" class="filter-select" style="width: auto; min-width: 130px; flex: 1; height: 38px;" onchange="applyAdvancedResultsFilter()">
                                <option value="">⭐ Tất cả Xếp loại</option>
                                <option value="perfect">👑 Xuất sắc (1000đ)</option>
                                <option value="pass">✓ Đạt chuẩn (≥ 700đ)</option>
                                <option value="fail">✕ Cần cố gắng (< 700đ)</option>
                            </select>

                            <!-- Nút đặt lại & bộ đếm -->
                            <button type="button" class="btn-ghost" onclick="resetResultsFilter()" title="Đặt lại bộ lọc" style="padding: 8px 12px; font-size: 13px; height: 38px;">
                                🔄
                            </button>
                            <span id="filter-counter-badge" class="pill-badge pill-grade" style="padding: 8px 14px; font-size: 12px;">
                                {{ $attempts->count() }} lượt
                            </span>
                        </div>
                    </div>

                    <!-- DATA TABLE (CHUẨN LƯỚI EXCEL SẮC NÉT, VỪA VẶN 100%) -->
                    <div class="excel-table-wrap" style="overflow-x: hidden;">
                        <table id="attempts-data-table" class="modal-roster-table" style="width: 100% !important; table-layout: fixed;">
                            <colgroup>
                                <col style="width: 4%;">
                                <col style="width: 19%;">
                                <col style="width: 13%;">
                                <col style="width: 18%;">
                                <col style="width: 11%;">
                                <col style="width: 8%;">
                                <col style="width: 12%;">
                                <col style="width: 15%;">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th class="col-stt" style="text-align:center;">#</th>
                                    <th>HỌC SINH</th>
                                    <th style="text-align:center;">{{ $isTeacher ? 'KHỐI HỌC' : 'GIÁO VIÊN / KHỐI' }}</th>
                                    <th>BÀI LUYỆN / CHỦ ĐỀ</th>
                                    <th style="text-align:center;">ĐIỂM SỐ</th>
                                    <th style="text-align:center;">CÂU ĐÚNG</th>
                                    <th style="text-align:center;">THỜI ĐIỂM NỘP</th>
                                    <th style="text-align:center;">CHI TIẾT</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attempts as $idx => $a)
                                    @php
                                        $isPassed = $a->score >= 700;
                                        $isPerfect = $a->score >= 1000;
                                        $mins = floor($a->duration_seconds / 60);
                                        $secs = $a->duration_seconds % 60;
                                        $timeFormatted = sprintf('%02d:%02d', $mins, $secs);
                                        $statusKey = $isPerfect ? 'perfect' : ($isPassed ? 'pass' : 'fail');
                                        $gradeNum = $a->practiceTest?->topic?->level?->grade ?? '3';
                                        $teacherId = $a->user?->created_by ?? '';
                                        $topicId = $a->practiceTest?->topic_id ?? '';
                                    @endphp
                                    <tr class="attempt-row-item" 
                                        data-grade="{{ $gradeNum }}"
                                        data-teacher="{{ $teacherId }}"
                                        data-topic="{{ $topicId }}"
                                        data-status="{{ $statusKey }}"
                                        data-search="{{ mb_strtolower(($a->user?->name ?? '') . ' ' . ($a->user?->student_code ?? '') . ' ' . ($a->practiceTest?->name ?? '') . ' ' . ($a->practiceTest?->topic?->name ?? '')) }}">
                                        
                                        <td class="col-stt" style="color:#94a3b8; font-weight:700; text-align:center;">{{ $idx + 1 }}</td>
                                        <td>
                                            <div style="display:flex; align-items:center; gap:8px; min-width:0;">
                                                <div style="width:30px; height:30px; border-radius:8px; display:grid; place-items:center; font-weight:900; font-size:11.5px; color:#fff; background:linear-gradient(135deg, #6366f1, #8b5cf6); flex-shrink:0;">
                                                    {{ mb_strtoupper(mb_substr($a->user?->name ?? 'H', 0, 1)) }}
                                                </div>
                                                <div style="min-width:0; overflow:hidden;">
                                                    <div style="font-weight:800; color:#0f172a; font-size:12.5px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $a->user?->name ?? 'Học sinh #' . $a->user_id }}</div>
                                                    @if($a->user?->student_code)
                                                        <span class="pill-badge pill-code" style="padding:1px 4px; font-size:9px; margin-top:1px; display:inline-block;">{{ $a->user->student_code }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td style="text-align:center;">
                                            @if(! $isTeacher && $a->user?->teacher)
                                                <div style="font-weight:750; color:#059669; font-size:11.5px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">👩‍🏫 {{ $a->user->teacher->name }}</div>
                                            @endif
                                            <span class="pill-badge pill-grade" style="font-size:9.5px; padding:1.5px 6px; margin-top:2px;">Khối {{ $gradeNum }}</span>
                                        </td>
                                        <td>
                                            <div style="font-weight:750; color:#1e293b; font-size:12px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                                {{ $a->practiceTest?->name ?? 'Bài luyện #' . $a->practice_test_id }}
                                            </div>
                                            <div style="font-size:10.5px; color:#64748b; margin-top:1px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                                📚 {{ $a->practiceTest?->topic?->name }}
                                            </div>
                                        </td>
                                        <td style="text-align:center;">
                                            <div class="score-bar-wrap" style="justify-content:center;">
                                                <b style="font-size:13px; color: {{ $isPassed ? '#15803d' : '#b91c1c' }}; font-weight:900;">
                                                    {{ $a->score }}
                                                </b>
                                                <div class="score-bar-track" style="width: 60px;">
                                                    <div class="score-bar-fill" style="width: {{ min(100, max(0, $a->score / 10)) }}%; background: {{ $isPassed ? '#10b981' : '#ef4444' }};"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td style="text-align:center;">
                                            <b style="color:#0f172a; font-size:12px;">{{ $a->correct_answers }}</b>
                                            <span style="color:#94a3b8; font-size:10.5px;">/{{ $a->total_questions }}</span>
                                        </td>
                                        <td style="text-align:center;">
                                            <div style="font-size:11px; color:#334155;" title="{{ $a->completed_at_vn }}">
                                                {{ $a->completed_date_vn }}
                                            </div>
                                            <div style="font-size:10px; color:#94a3b8; margin-top:1px;">
                                                ⏱️ {{ $timeFormatted }}
                                            </div>
                                        </td>
                                        <td style="text-align:center;">
                                            <button type="button" class="btn-action-view" style="margin: 0 auto;"
                                                data-student="{{ $a->user?->name ?? 'Học sinh #' . $a->user_id }}"
                                                data-code="{{ $a->user?->student_code ?? '' }}"
                                                data-class="{{ $a->user?->classroom?->name ?? 'Tự do' }}"
                                                data-grade="{{ $a->user?->classroom?->grade ?? 3 }}"
                                                data-test="{{ $a->practiceTest?->name ?? 'Bài thi luyện' }}"
                                                data-topic="{{ $a->practiceTest?->topic?->name ?? '' }}"
                                                data-score="{{ $a->score }}"
                                                data-answers="{{ $a->correct_answers }} / {{ $a->total_questions }}"
                                                data-duration="{{ $timeFormatted }}"
                                                data-date="{{ $a->completed_at_vn }}"
                                                data-passed="{{ $isPassed ? '1' : '0' }}"
                                                data-perfect="{{ $isPerfect ? '1' : '0' }}"
                                                onclick="openAttemptDetailModal(this)" 
                                                title="Xem chi tiết phiếu điểm bài thi">
                                                <span>👁️</span> Phiếu điểm
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr id="empty-attempts-row">
                                        <td colspan="8" style="text-align:center; padding:48px 24px; color:#94a3b8;">
                                            <div style="font-size:36px; margin-bottom:8px;">📝</div>
                                            <b style="font-size:14.5px; color:#475569;">Chưa có lượt làm bài nào trong cơ sở dữ liệu.</b>
                                            <p style="font-size:13px; margin-top:4px;">Khi học sinh hoàn thành các bài thi luyện IC3, kết quả sẽ tự động hiển thị thời gian thực tại đây.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>



            <!-- ========================================================= -->
            <!-- TAB: 🔑 QUẢN LÝ KHỐI LỚP & CẤP ĐỘ (TAB-LEVELS)            -->
            <!-- ========================================================= -->
            @if(! $isTeacher)
            <div id="tab-levels" class="admin-tab-pane" style="display:none;">
                <section class="card">
                    <div class="card-header-row">
                        <div>
                            <h2 class="card-title"><span>🔑</span> Quản trị Khung Chương Trình & Khối Lớp</h2>
                            <p class="card-subtitle">Quản lý tập trung các Chương trình đào tạo (IC3, MOS...) và Danh mục Khối lớp (Khối 1 đến Khối 12) trong toàn hệ thống</p>
                        </div>
                        <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                            <span class="pill-badge pill-grade" style="padding:6px 12px; font-size:12px;">{{ $programs->count() }} chương trình · {{ $levels->count() }} khối</span>
                            <button type="button" class="btn-ghost" onclick="openCreateProgramModal()" style="font-size:13px; font-weight:800; border-color:var(--primary); color:var(--primary); background:#eef2ff;">
                                <span>🗂 ＋</span> Thêm chương trình
                            </button>
                            <button type="button" class="btn-primary" onclick="openCreateLevelModal()">
                                <span>🔑 ＋</span> Thêm khối lớp mới
                            </button>
                        </div>
                    </div>

                    @php
                        $gradeThemesAdmin = [
                            1 => [
                                'name' => 'Khởi đầu số',
                                'gradient' => 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)',
                                'color' => '#d97706',
                                'icon' => '🌟',
                                'btn_bg' => 'linear-gradient(135deg, #f59e0b, #d97706)',
                            ],
                            2 => [
                                'name' => 'Khám phá số',
                                'gradient' => 'linear-gradient(135deg, #14b8a6 0%, #0f766e 100%)',
                                'color' => '#0f766e',
                                'icon' => '🌱',
                                'btn_bg' => 'linear-gradient(135deg, #14b8a6, #0f766e)',
                            ],
                            3 => [
                                'name' => 'Spark Level 1',
                                'gradient' => 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
                                'color' => '#059669',
                                'icon' => '📖',
                                'btn_bg' => 'linear-gradient(135deg, #10b981, #059669)',
                            ],
                            4 => [
                                'name' => 'Spark Level 2',
                                'gradient' => 'linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%)',
                                'color' => '#2563eb',
                                'icon' => '🎧',
                                'btn_bg' => 'linear-gradient(135deg, #0ea5e9, #2563eb)',
                            ],
                            5 => [
                                'name' => 'Spark Level 3',
                                'gradient' => 'linear-gradient(135deg, #a855f7 0%, #7c3aed 100%)',
                                'color' => '#7c3aed',
                                'icon' => '✏️',
                                'btn_bg' => 'linear-gradient(135deg, #a855f7, #7c3aed)',
                            ],
                            6 => [
                                'name' => 'Bứt phá số',
                                'gradient' => 'linear-gradient(135deg, #f43f5e 0%, #e11d48 100%)',
                                'color' => '#e11d48',
                                'icon' => '🚀',
                                'btn_bg' => 'linear-gradient(135deg, #f43f5e, #e11d48)',
                            ],
                            7 => [
                                'name' => 'Chinh phục số',
                                'gradient' => 'linear-gradient(135deg, #6366f1 0%, #4338ca 100%)',
                                'color' => '#4338ca',
                                'icon' => '⚡',
                                'btn_bg' => 'linear-gradient(135deg, #6366f1, #4338ca)',
                            ],
                            8 => [
                                'name' => 'Chuyên gia số',
                                'gradient' => 'linear-gradient(135deg, #d946ef 0%, #c026d3 100%)',
                                'color' => '#c026d3',
                                'icon' => '💎',
                                'btn_bg' => 'linear-gradient(135deg, #d946ef, #c026d3)',
                            ],
                            9 => [
                                'name' => 'Làm chủ số',
                                'gradient' => 'linear-gradient(135deg, #f97316 0%, #ea580c 100%)',
                                'color' => '#ea580c',
                                'icon' => '🎯',
                                'btn_bg' => 'linear-gradient(135deg, #f97316, #ea580c)',
                            ],
                        ];
                    @endphp

                    <!-- Programs & Level Groups -->
                    <div style="display: flex; flex-direction: column; gap: 28px; margin-bottom: 32px;">
                        @foreach($programs as $p)
                            @php
                                $progLevels = $p->levels->sortBy('grade');
                                $pClasses = $classes->whereIn('grade', $progLevels->pluck('grade'))->count();
                                $pTopics = $progLevels->sum(fn($l) => $l->topics->count());
                                $pTests = $progLevels->sum(fn($l) => $l->topics->sum(fn($t) => $t->tests->count()));
                            @endphp
                            <div style="background: #ffffff; border: 2px solid #e2e8f0; border-radius: 20px; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.03);">
                                <!-- Program Header Bar -->
                                <div style="background: linear-gradient(135deg, #1e1b4b, #312e81); padding: 16px 20px; color: #ffffff; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div style="width: 42px; height: 42px; border-radius: 12px; background: rgba(255,255,255,0.15); border: 1.5px solid rgba(255,255,255,0.3); display: grid; place-items: center; font-size: 20px;">
                                            🗂️
                                        </div>
                                        <div>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <h3 style="font-size: 16.5px; font-weight: 900; color: #ffffff; margin: 0;">{{ $p->name }}</h3>
                                                <span style="background: rgba(255,255,255,0.2); color: #ffffff; font-size: 11px; font-weight: 800; padding: 2px 7px; border-radius: 6px; font-family: ui-monospace, monospace;">
                                                    slug: {{ $p->slug }}
                                                </span>
                                            </div>
                                            <div style="font-size: 12.5px; color: #cbd5e1; margin-top: 3px; display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
                                                <span>🔑 <b>{{ $progLevels->count() }}</b> Khối lớp</span>
                                                <span>🏫 <b>{{ $pClasses }}</b> Lớp học</span>
                                                <span>📚 <b>{{ $pTopics }}</b> Chủ đề</span>
                                                <span>📝 <b>{{ $pTests }}</b> Đề luyện</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <button type="button" class="btn-action-edit"
                                            data-id="{{ $p->id }}"
                                            data-name="{{ $p->name }}"
                                            data-slug="{{ $p->slug }}"
                                            data-accent="{{ $p->accent ?? '#4f46e5' }}"
                                            data-desc="{{ $p->description ?? '' }}"
                                            data-update-url="{{ route('admin.programs.update', $p) }}"
                                            onclick="openEditProgramModal(this)"
                                            style="background: rgba(255,255,255,0.15); color: #fff; border-color: rgba(255,255,255,0.3);"
                                            title="Sửa thông tin chương trình">
                                            <span>✏️</span> Sửa
                                        </button>
                                        <button type="button" class="btn-action-view" onclick="openCreateLevelModal()" style="background: #10b981; color: #fff; border-color: #059669;" title="Thêm khối mới vào chương trình này">
                                            <span>＋</span> Thêm Khối
                                        </button>
                                        @if($progLevels->count() === 0)
                                            <form method="post" action="{{ route('admin.programs.destroy', $p) }}" onsubmit="return confirm('Bạn có chắc muốn xóa chương trình {{ $p->name }}?')" style="margin:0;">
                                                @csrf
                                                @method('delete')
                                                <button type="submit" class="btn-action-delete" title="Xóa chương trình này">
                                                    <span>🗑️</span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>

                                <!-- Cards for levels under this program -->
                                <div style="padding: 20px; background: #f8fafc;">
                                    @if($progLevels->isNotEmpty())
                                        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(290px, 1fr)); gap: 18px;">
                                            @foreach($progLevels as $lvl)
                                                @php
                                                    $thm = $gradeThemesAdmin[$lvl->grade] ?? $gradeThemesAdmin[(($lvl->grade - 1) % count($gradeThemesAdmin)) + 1] ?? $gradeThemesAdmin[3];
                                                    $classCount = $classes->where('grade', $lvl->grade)->count();
                                                    $topicCount = $lvl->topics->count();
                                                    $testCount = $lvl->topics->sum(fn($t) => $t->tests->count());
                                                @endphp
                                                <div class="level-hero-card" style="border: 2px solid {{ $thm['color'] }}28;">
                                                    <!-- Gradient Banner Header -->
                                                    <div class="level-hero-header" style="background: {{ $thm['gradient'] }};">
                                                        <div class="level-hero-topline">
                                                            <span class="level-hero-badge">
                                                                {{ $thm['icon'] }} Khối {{ $lvl->grade }} · {{ $thm['name'] }}
                                                            </span>
                                                            <span class="level-hero-program">{{ $p->name }}</span>
                                                        </div>
                                                        <h3 class="level-hero-title">{{ $lvl->name }}</h3>
                                                    </div>

                                                    <!-- Body with 3 Key Metric Tiles -->
                                                    <div class="level-hero-body">
                                                        <div class="level-metrics-3grid">
                                                            <div class="metric-tile">
                                                                <span class="metric-icon">🏫</span>
                                                                <b class="metric-num">{{ $classCount }}</b>
                                                                <small class="metric-lbl">LỚP HỌC</small>
                                                            </div>
                                                            <div class="metric-tile">
                                                                <span class="metric-icon">📚</span>
                                                                <b class="metric-num">{{ $topicCount }}</b>
                                                                <small class="metric-lbl">CHỦ ĐỀ</small>
                                                            </div>
                                                            <div class="metric-tile">
                                                                <span class="metric-icon">📝</span>
                                                                <b class="metric-num">{{ $testCount }}</b>
                                                                <small class="metric-lbl">ĐỀ THI</small>
                                                            </div>
                                                        </div>

                                                        <!-- Action Buttons -->
                                                        <div class="level-hero-actions">
                                                            <a href="{{ route('admin.questions.studio') }}?grade={{ $lvl->grade }}" class="btn-level-primary" style="background: {{ $thm['btn_bg'] }};" title="Soạn đề & Quản lý câu hỏi cho Khối {{ $lvl->grade }}">
                                                                <span>📚</span> Soạn đề
                                                            </a>
                                                            <a href="{{ route('levels.show', $lvl) }}" target="_blank" class="btn-level-preview" title="Xem bản đồ học sinh Khối {{ $lvl->grade }}">
                                                                <span>👁️</span> Xem map
                                                            </a>
                                                            <button type="button" class="btn-level-edit"
                                                                data-id="{{ $lvl->id }}"
                                                                data-name="{{ $lvl->name }}"
                                                                data-grade="{{ $lvl->grade }}"
                                                                data-program="{{ $lvl->program_id }}"
                                                                data-position="{{ $lvl->position ?? $lvl->grade }}"
                                                                data-update-url="{{ route('admin.levels.update', $lvl) }}"
                                                                onclick="openEditLevelModal(this)"
                                                                title="Chỉnh sửa thông tin khối">
                                                                <span>✏️</span>
                                                            </button>
                                                            <form method="post" action="{{ route('admin.levels.destroy', $lvl) }}" onsubmit="return confirm('Bạn có chắc muốn xóa khối {{ $lvl->name }}? Lưu ý: Các chủ đề con nếu có cũng sẽ bị xóa!')" style="margin:0;">
                                                                @csrf
                                                                @method('delete')
                                                                <button type="submit" class="btn-level-del" title="Xóa khối này">
                                                                    <span>🗑️</span>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div style="background: #ffffff; border: 1.5px dashed #cbd5e1; border-radius: 12px; padding: 20px; text-align: center; color: #94a3b8;">
                                            Chưa có khối lớp nào thuộc chương trình này.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Level Detailed Management Table -->
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>KHỐI</th>
                                    <th>TÊN CẤP ĐỘ / KHỐI LỚP</th>
                                    <th>CHƯƠNG TRÌNH</th>
                                    <th>THỨ TỰ</th>
                                    <th>THỐNG KÊ</th>
                                    <th style="text-align:right;">THAO TÁC</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($levels as $lvl)
                                    @php
                                        $thm = $gradeThemesAdmin[$lvl->grade] ?? $gradeThemesAdmin[(($lvl->grade - 1) % count($gradeThemesAdmin)) + 1] ?? $gradeThemesAdmin[3];
                                    @endphp
                                    <tr>
                                        <td>
                                            <span style="background: {{ $thm['color'] }}18; color: {{ $thm['color'] }}; font-weight: 850; border-radius: 999px; padding: 4px 10px; font-size: 12px; display: inline-flex; align-items: center; gap: 4px; border: 1px solid {{ $thm['color'] }}40; white-space: nowrap;">
                                                {{ $thm['icon'] }} Khối {{ $lvl->grade }}
                                            </span>
                                        </td>
                                        <td>
                                            <b style="color:#0f172a; font-size:14px;">{{ $lvl->name }}</b>
                                            <div style="font-size:11.5px; color:#64748b; margin-top:2px;">
                                                Mã định danh (slug): <code style="background:#f1f5f9; padding:2px 6px; border-radius:4px; font-size:11px;">{{ $lvl->slug }}</code>
                                            </div>
                                        </td>
                                        <td>
                                            <b style="color:#475569; font-size:13px;">{{ $lvl->program?->name ?? 'IC3 GS6' }}</b>
                                        </td>
                                        <td>
                                            <span style="color:#64748b; font-weight:700;">#{{ $lvl->position ?? $lvl->grade }}</span>
                                        </td>
                                        <td>
                                            <div style="font-size:12px; color:#475569;">
                                                🏫 <b>{{ $classes->where('grade', $lvl->grade)->count() }}</b> lớp · 📚 <b>{{ $lvl->topics->count() }}</b> chủ đề · 📝 <b>{{ $lvl->topics->sum(fn($t) => $t->tests->count()) }}</b> đề
                                            </div>
                                        </td>
                                        <td style="text-align:right;">
                                            <div class="action-btn-group">
                                                <a href="{{ route('levels.show', $lvl) }}" target="_blank" class="btn-action-view" title="Xem bản đồ học sinh Khối {{ $lvl->grade }}">
                                                    <span>👁️</span> Xem map
                                                </a>

                                                <a href="{{ route('admin.questions.studio') }}?grade={{ $lvl->grade }}" class="btn-action-grant" style="text-decoration:none;" title="Mở Studio Soạn Đề & Thêm chủ đề cho khối này">
                                                    <span>📚</span> Soạn đề
                                                </a>

                                                <button type="button" class="btn-action-edit"
                                                    data-id="{{ $lvl->id }}"
                                                    data-name="{{ $lvl->name }}"
                                                    data-grade="{{ $lvl->grade }}"
                                                    data-program="{{ $lvl->program_id }}"
                                                    data-position="{{ $lvl->position ?? $lvl->grade }}"
                                                    data-update-url="{{ route('admin.levels.update', $lvl) }}"
                                                    onclick="openEditLevelModal(this)"
                                                    title="Chỉnh sửa tên khối, cấp độ, số grade">
                                                    <span>✏️</span> Sửa
                                                </button>

                                                <form method="post" action="{{ route('admin.levels.destroy', $lvl) }}" onsubmit="return confirm('Bạn có chắc muốn xóa khối {{ $lvl->name }}? Lưu ý: Các chủ đề con nếu có cũng sẽ bị xóa!')" style="margin:0;">
                                                    @csrf
                                                    @method('delete')
                                                    <button type="submit" class="btn-action-delete" title="Xóa khối lớp này">
                                                        <span>🗑️</span> Xóa
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" style="text-align:center; padding:28px; color:#94a3b8;">Chưa có khối lớp nào.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
            @endif

            <!-- ========================================================= -->
            <!-- TAB 3: 👥 PHÂN QUYỀN & TÀI KHOẢN NGƯỜI DÙNG (TAB-USERS)  -->
            <!-- ========================================================= -->
            <!-- TAB 2: 👥 QUẢN TRỊ ĐẠI LÝ & HỌC SINH (TAB-USERS)           -->
            <!-- ========================================================= -->
            <div id="tab-users" class="admin-tab-pane" style="display:none;">
                <section class="card">
                    <div class="card-header-row">
                        <div>
                            <h2 class="card-title"><span>👥</span> {{ $isTeacher ? 'Danh Sách Học Sinh Của Tôi' : 'Quản Trị Giáo Viên & Học Sinh' }}</h2>
                            <p class="card-subtitle">{{ $isTeacher ? 'Quản lý tài khoản và cấp quyền mở khóa Khối học cho học sinh của bạn' : 'Cấp quyền Khối học & Quota cho Giáo viên và toàn bộ Học sinh trong hệ thống' }}</p>
                        </div>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <span class="pill-badge pill-grade" style="padding:6px 12px; font-size:12px;">Tổng: {{ $allUsers->count() }} tài khoản</span>
                            <button type="button" class="btn-primary" onclick="openCreateUserModal()">
                                <span>＋</span> {{ $isTeacher ? 'Thêm học sinh mới' : 'Thêm tài khoản mới' }}
                            </button>
                        </div>
                    </div>

                    <!-- Modern Toolbar Bar for Filters & Search -->
                    <div class="card-toolbar" style="display:flex; gap:12px; align-items:center; justify-content:space-between; flex-wrap:wrap;">
                    @if($isTeacher)
                        <!-- ================= GIAO DIỆN DÀNH RIÊNG CHO GIÁO VIÊN ================= -->
                        <div class="card-toolbar" style="display:flex; gap:12px; align-items:center; justify-content:space-between; flex-wrap:wrap;">
                            <div style="display:flex; align-items:center; gap:8px;">
                                <span class="pill-badge pill-grade" style="font-size:12.5px; padding:6px 14px;">
                                    👥 Sĩ số: <b>{{ $allUsers->count() }}</b> / {{ auth()->user()->max_students ?: '∞' }} Học sinh
                                </span>
                            </div>

                            <div class="search-wrap" style="flex: 1; max-width: 460px; min-width: 280px;">
                                <span class="search-icon">🔍</span>
                                <input type="text" id="user-search-input" class="search-input" style="width: 100%; box-sizing: border-box; font-size: 13px;" placeholder="Tìm tên, mã HS, email học sinh..." onkeyup="filterUserSearch()">
                            </div>
                        </div>

                        <div class="excel-table-wrap">
                            <table id="users-data-table" class="modal-roster-table" style="width:100% !important; max-width:100% !important; table-layout:fixed; min-width: 760px;">
                                <colgroup>
                                    <col style="width: 4%;">
                                    <col style="width: 36%;">
                                    <col style="width: 13%;">
                                    <col style="width: 14%;">
                                    <col style="width: 10%;">
                                    <col style="width: 23%;">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th class="col-stt" style="text-align:center;">#</th>
                                        <th class="col-user" style="text-align:left; padding-left:14px;">HỌC SINH</th>
                                        <th style="text-align:center;">TRẠNG THÁI</th>
                                        <th style="text-align:center;">KHỐI ĐƯỢC CẤP</th>
                                        <th style="text-align:center;">LƯỢT THI</th>
                                        <th style="text-align:center;">THAO TÁC</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($allUsers as $u)
                                        @php
                                            $uRoleStr = is_object($u->role) ? $u->role->value : (string)$u->role;
                                            $isSuspended = ($u->status === 'suspended');
                                        @endphp
                                        <tr class="user-row-item {{ $isSuspended ? 'user-row-suspended' : '' }}" data-role="{{ $uRoleStr }}" data-user-id="{{ $u->id }}">
                                            <td class="col-stt" style="color:#94a3b8; font-weight:700; text-align:center;">{{ $loop->iteration }}</td>
                                            <td class="col-user" style="text-align:left; padding-left:14px; vertical-align:middle;">
                                                <div style="display:flex; align-items:center; gap:10px; min-width:0;">
                                                    <div class="avatar-box-wrap" 
                                                         style="cursor:pointer; transition:transform 0.15s ease;" 
                                                         onmouseover="this.style.transform='scale(1.08)'" 
                                                         onmouseout="this.style.transform='scale(1)'"
                                                         onclick="openStudentProfileModal(this)"
                                                         data-id="{{ $u->id }}"
                                                         data-name="{{ $u->name }}"
                                                         data-email="{{ $u->email }}"
                                                         data-code="{{ $u->student_code ?? '' }}"
                                                         data-status="{{ $u->status ?? 'active' }}"
                                                         data-created="{{ $u->created_date_vn }}"
                                                         data-attempts="{{ $u->attempts_count ?? $u->attempts()->count() }}"
                                                         data-levels='@json($u->accessibleLevels->map(fn($l) => ["grade" => $l->grade, "name" => $l->name]))'
                                                         data-teacher="{{ $u->teacher?->name ?? (auth()->user()->name ?? 'Giáo viên phụ trách') }}"
                                                         title="Bấm để xem hồ sơ chi tiết của {{ $u->name }}">
                                                        <div style="width:34px; height:34px; border-radius:10px; display:grid; place-items:center; font-weight:900; font-size:13px; color:#fff; background: {{ $isSuspended ? '#94a3b8' : 'linear-gradient(135deg, #6366f1, #8b5cf6)' }}; box-shadow:0 2px 6px rgba(99,102,241,0.25); flex-shrink:0;">
                                                            {{ mb_strtoupper(mb_substr($u->name, 0, 1)) }}
                                                        </div>
                                                        @if($isSuspended)
                                                            <span class="avatar-suspended-badge" title="Tài khoản đang bị tạm khóa">🔒</span>
                                                        @else
                                                            <span class="avatar-online-badge" title="Tài khoản đang hoạt động / Online"></span>
                                                        @endif
                                                    </div>
                                                    <div style="min-width:0; flex:1;">
                                                        <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                                                            <b style="color:{{ $isSuspended ? '#64748b' : '#0f172a' }}; font-size:13.5px; cursor:pointer;" 
                                                               onclick="openStudentProfileModal(this.closest('td').querySelector('.avatar-box-wrap'))" 
                                                               title="Bấm xem hồ sơ {{ $u->name }}">
                                                                {{ $u->name }}
                                                            </b>
                                                            @if($u->student_code)
                                                                <span class="pill-badge pill-code" style="font-size:10px; padding:1px 6px; font-weight:800; border-radius:5px;">{{ $u->student_code }}</span>
                                                            @endif
                                                            @if($isSuspended)
                                                                <span style="font-size:10.5px; color:#ef4444; font-weight:750;">(Đã khóa)</span>
                                                            @endif
                                                        </div>
                                                        <div style="font-size:11px; color:#64748b; margin-top:3px; display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                                                            <span style="color:#475569;" title="{{ $u->email }}">{{ $u->email }}</span>
                                                            <span style="color:#cbd5e1;">•</span>
                                                            <span style="color:#64748b; white-space:nowrap; font-weight:600;">📅 {{ $u->created_date_vn }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td style="text-align:center;" class="user-status-cell">
                                                @if($isSuspended)
                                                    <button type="button" class="pill-badge pill-fail" style="cursor:pointer; padding:4px 10px; font-size:11px; font-weight:800; line-height:1.3; border-radius:8px; border:1.5px solid #fca5a5; white-space:nowrap; display:inline-flex; align-items:center; gap:5px;" onclick="toggleStudentStatusAjax({{ $u->id }}, 'active', '{{ addslashes($u->name) }}')" title="Bấm để mở khóa kích hoạt lại tài khoản">
                                                        <span>🔒</span> Tạm khóa
                                                    </button>
                                                @else
                                                    <button type="button" class="pill-badge pill-pass" style="cursor:pointer; padding:4px 10px; font-size:11px; font-weight:800; line-height:1.3; border-radius:8px; border:1.5px solid #86efac; white-space:nowrap; display:inline-flex; align-items:center; gap:5px;" onclick="toggleStudentStatusAjax({{ $u->id }}, 'suspended', '{{ addslashes($u->name) }}')" title="Bấm để tạm khóa tài khoản này">
                                                        <span class="status-dot-online" style="width:7px; height:7px; background:#10b981; border-radius:50%; display:inline-block; box-shadow:0 0 0 2px rgba(16,185,129,0.2);"></span> Đang học
                                                    </button>
                                                @endif
                                            </td>
                                            <td style="text-align:center;">
                                                <div style="display:flex; gap:3px; flex-wrap:wrap; justify-content:center;">
                                                    @forelse($u->accessibleLevels as $lvl)
                                                        <span class="pill-badge pill-grade" style="font-size:10px; padding:2px 6px;">Khối {{ $lvl->grade }}</span>
                                                    @empty
                                                        <span style="font-size:10.5px; color:#ef4444; font-weight:750;">🔒 Chưa mở</span>
                                                    @endforelse
                                                </div>
                                            </td>
                                            <td style="text-align:center;">
                                                <span class="pill-badge pill-time" style="font-size:10.5px; padding:2px 6px;">📝 {{ $u->attempts_count ?? $u->attempts()->count() }} lượt</span>
                                            </td>
                                            <td style="text-align:center;">
                                                <div class="action-btn-group" style="justify-content:center; gap:4px; flex-wrap:nowrap;">
                                                    <button type="button" class="btn-action-edit"
                                                        data-id="{{ $u->id }}"
                                                        data-name="{{ $u->name }}"
                                                        data-email="{{ $u->email }}"
                                                        data-code="{{ $u->student_code ?? '' }}"
                                                        data-role="{{ $uRoleStr }}"
                                                        data-status="{{ $u->status ?? 'active' }}"
                                                        data-teacher-id="{{ $u->created_by ?? '' }}"
                                                        data-levels='@json($u->accessibleLevels->pluck("id"))'
                                                        data-update-url="{{ route('admin.users.update', $u) }}"
                                                        onclick="openEditUserModal(this)" 
                                                        title="Chỉnh sửa thông tin & Đổi mật khẩu">
                                                        <span>✏️</span> Sửa
                                                    </button>

                                                    <button type="button" class="btn-action-grant" onclick='openGrantModal(@json($u), @json($u->accessibleLevels->pluck("id")))' title="Cấp quyền mở khóa khối học">
                                                        <span>🔑</span> Khối
                                                    </button>

                                                    <button type="button" class="btn-action-delete" onclick="deleteStudentAjax({{ $u->id }}, '{{ addslashes($u->name) }}')" title="Xóa học sinh này">
                                                        <span>🗑️</span> Xóa
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" style="text-align:center; padding:36px; color:#94a3b8;">
                                                <div style="font-size:32px; margin-bottom:6px;">👨‍🎓</div>
                                                <b style="color:#334155; font-size:14px;">Bạn chưa có học sinh nào.</b>
                                                <p style="font-size:13px; margin-top:4px;">Bấm "＋ Thêm học sinh mới" ở trên để tạo tài khoản cho học sinh.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @else
                        <!-- ================= GIAO DIỆN DÀNH CHO ADMIN (QUẢN LÝ GIÁO VIÊN & TẤT CẢ USER) ================= -->
                        <div class="card-toolbar" style="display:flex; gap:12px; align-items:center; justify-content:space-between; flex-wrap:wrap;">
                            <div class="filter-tab-group">
                                <button type="button" class="filter-tab-btn active" id="filter-btn-all" onclick="filterUserRole('all', this)">Tất cả ({{ $allUsers->count() }})</button>
                                <button type="button" class="filter-tab-btn" id="filter-btn-teacher" onclick="filterUserRole('teacher', this)">👩‍🏫 Giáo viên ({{ $allUsers->filter(fn($u) => $u->isTeacher())->count() }})</button>
                                <button type="button" class="filter-tab-btn" id="filter-btn-student" onclick="filterUserRole('student', this)">👨‍🎓 Học sinh ({{ $allUsers->filter(fn($u) => $u->isStudent())->count() }})</button>
                            </div>

                            <div class="search-wrap" style="flex: 1; max-width: 320px;">
                                <span class="search-icon">🔍</span>
                                <input type="text" id="user-search-input" class="search-input" style="width: 100%; box-sizing: border-box;" placeholder="Tìm theo tên, mã HS, email..." onkeyup="filterUserSearch()">
                            </div>
                        </div>

                        <div class="excel-table-wrap">
                            <table id="users-data-table" class="modal-roster-table" style="width:100% !important; max-width:100% !important; table-layout: fixed; min-width: 800px;">
                                <colgroup>
                                    <col style="width: 30%;">
                                    <col style="width: 10%;">
                                    <col style="width: 11%;">
                                    <col style="width: 19%;">
                                    <col style="width: 9%;">
                                    <col style="width: 21%;">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th class="col-user" style="text-align:left; padding-left:16px;">NGƯỜI DÙNG</th>
                                        <th style="text-align:center;">VAI TRÒ</th>
                                        <th style="text-align:center;">TRẠNG THÁI</th>
                                        <th style="text-align:center;">GIÁO VIÊN / GÓI & KHỐI</th>
                                        <th style="text-align:center;">TIẾN ĐỘ</th>
                                        <th style="text-align:center;">THAO TÁC</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($allUsers as $u)
                                        @php
                                            $uRoleStr = is_object($u->role) ? $u->role->value : (string)$u->role;
                                            $isSuspended = ($u->status === 'suspended');
                                            $isPending = ($u->status === 'pending');
                                            $userPendingOrder = $isPending ? $u->packageOrders->firstWhere('status', 'pending') : null;
                                        @endphp
                                        <tr class="user-row-item {{ $isSuspended ? 'user-row-suspended' : '' }}" data-role="{{ $uRoleStr }}" data-user-id="{{ $u->id }}">
                                            <td class="col-user" style="vertical-align:middle; padding:10px 16px; text-align:left;">
                                                <div style="display:flex; align-items:center; gap:10px; min-width:0;">
                                                    <div class="avatar-box-wrap"
                                                         style="cursor:pointer; transition:transform 0.15s ease;"
                                                         onmouseover="this.style.transform='scale(1.08)'"
                                                         onmouseout="this.style.transform='scale(1)'"
                                                         onclick="openStudentProfileModal(this)"
                                                         data-id="{{ $u->id }}"
                                                         data-name="{{ $u->name }}"
                                                         data-email="{{ $u->email }}"
                                                         data-code="{{ $u->student_code ?? '' }}"
                                                         data-status="{{ $u->status ?? 'active' }}"
                                                         data-created="{{ $u->created_date_vn }}"
                                                         data-attempts="{{ $u->attempts_count ?? $u->attempts()->count() }}"
                                                         data-levels='@json($u->accessibleLevels->map(fn($l) => ["grade" => $l->grade, "name" => $l->name]))'
                                                         data-teacher="{{ $u->teacher?->name ?? 'Quản trị viên' }}"
                                                         title="Bấm để xem hồ sơ chi tiết của {{ $u->name }}">
                                                        <div style="width:36px; height:36px; border-radius:9px; display:grid; place-items:center; font-weight:900; font-size:13px; color:#fff; background: {{ $isSuspended ? '#94a3b8' : ($isPending ? 'linear-gradient(135deg, #f59e0b, #d97706)' : ($u->isAdmin() ? 'linear-gradient(135deg, #ef4444, #f59e0b)' : ($u->isTeacher() ? 'linear-gradient(135deg, #10b981, #06b6d4)' : 'linear-gradient(135deg, #6366f1, #8b5cf6)'))) }}; box-shadow:0 2px 6px rgba(99,102,241,0.25); flex-shrink:0;">
                                                            {{ mb_strtoupper(mb_substr($u->name, 0, 1)) }}
                                                        </div>
                                                        @if($isSuspended)
                                                            <span class="avatar-suspended-badge" title="Tài khoản đang bị tạm khóa">🔒</span>
                                                        @elseif($isPending)
                                                            <span class="avatar-suspended-badge" style="background:#f59e0b;" title="Tài khoản đang chờ thanh toán/kích hoạt">⏳</span>
                                                        @else
                                                            <span class="avatar-online-badge" title="Tài khoản đang hoạt động / Online"></span>
                                                        @endif
                                                    </div>
                                                    <div style="min-width:0; flex:1; text-align:left;">
                                                        <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                                                            <b style="color:{{ $isSuspended ? '#64748b' : '#0f172a' }}; font-size:13.5px; font-weight:800; cursor:pointer;"
                                                               onclick="openStudentProfileModal(this.closest('td').querySelector('.avatar-box-wrap'))"
                                                               title="Bấm xem hồ sơ {{ $u->name }}">
                                                                {{ $u->name }}
                                                            </b>
                                                            @if($u->student_code)
                                                                <span class="pill-badge pill-code" style="font-size:10px; padding:1px 6px; font-weight:800; border-radius:5px; background:#e0e7ff; color:#4338ca; border:1px solid #c7d2fe;">{{ $u->student_code }}</span>
                                                            @endif
                                                            @if($isSuspended)
                                                                <span style="font-size:10px; color:#ef4444; font-weight:800; background:#fef2f2; padding:1px 5px; border-radius:4px; border:1px solid #fca5a5;">(Đã khóa)</span>
                                                            @elseif($isPending)
                                                                <span style="font-size:10px; color:#b45309; font-weight:800; background:#fffbeb; padding:1px 5px; border-radius:4px; border:1px solid #fde68a;">(Chờ duyệt)</span>
                                                            @endif
                                                        </div>
                                                        <div style="font-size:11px; color:#64748b; margin-top:3px; display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                                                            <span style="color:#475569; font-weight:500;" title="{{ $u->email }}">{{ $u->email }}</span>
                                                            <span style="color:#cbd5e1;">•</span>
                                                            <span style="color:#64748b; white-space:nowrap; font-weight:600;">📅 {{ $u->created_date_vn }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td style="text-align:center; vertical-align:middle;">
                                                @if($u->isAdmin())
                                                    <span class="pill-badge pill-role-admin" style="font-size:11px; padding:3px 8px; font-weight:800; display:inline-flex; align-items:center; gap:4px;">👑 Admin</span>
                                                @elseif($u->isTeacher())
                                                    <span class="pill-badge pill-role-teacher" style="font-size:11px; padding:3px 8px; font-weight:800; display:inline-flex; align-items:center; gap:4px;">👩‍🏫 Giáo viên</span>
                                                @else
                                                    <span class="pill-badge pill-role-student" style="font-size:11px; padding:3px 8px; font-weight:800; display:inline-flex; align-items:center; gap:4px;">👨‍🎓 Học sinh</span>
                                                @endif
                                            </td>
                                            <td style="text-align:center; vertical-align:middle;" class="user-status-cell">
                                                @if($isSuspended)
                                                    <button type="button" class="pill-badge pill-fail" style="cursor:pointer; padding:4px 10px; font-size:11px; font-weight:800; border-radius:8px; border:1.5px solid #fca5a5; display:inline-flex; align-items:center; gap:4px;" onclick="toggleStudentStatusAjax({{ $u->id }}, 'active', '{{ addslashes($u->name) }}')" title="Bấm để mở khóa kích hoạt lại tài khoản">
                                                        🔒 Tạm khóa
                                                    </button>
                                                @elseif($isPending)
                                                    <span class="pill-badge" style="background:#fef3c7; color:#b45309; border:1.5px solid #fde68a; font-weight:800; padding:4px 10px; font-size:11px; display:inline-flex; align-items:center; gap:4px;" title="Tài khoản mới đăng ký, đang chờ thanh toán đơn hàng">
                                                        ⏳ Chờ kích hoạt
                                                    </span>
                                                @else
                                                    <button type="button" class="pill-badge pill-pass" style="cursor:pointer; padding:4px 10px; font-size:11px; font-weight:800; border-radius:8px; border:1.5px solid #86efac; display:inline-flex; align-items:center; gap:5px;" onclick="toggleStudentStatusAjax({{ $u->id }}, 'suspended', '{{ addslashes($u->name) }}')" title="Bấm để tạm khóa tài khoản này">
                                                        <span class="status-dot-online" style="width:7px; height:7px; background:#10b981; border-radius:50%; display:inline-block; box-shadow:0 0 0 2px rgba(16,185,129,0.2);"></span> {{ $u->isTeacher() ? 'Hoạt động' : 'Đang học' }}
                                                    </button>
                                                @endif
                                            </td>
                                            <td style="text-align:center; vertical-align:middle;">
                                                <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; gap:3px;">
                                                    @if($u->isTeacher())
                                                        @if($isPending)
                                                            @if($userPendingOrder)
                                                                <div style="font-size:11.5px; font-weight:800; color:#b45309;">
                                                                    📦 Đơn: #{{ $userPendingOrder->code }}
                                                                </div>
                                                                <div style="font-size:11px; color:#475569;">
                                                                    Gói: <b>{{ $userPendingOrder->package_name }}</b>
                                                                </div>
                                                                <div style="font-size:10.5px; font-weight:800; color:#d97706;">
                                                                    💰 {{ number_format($userPendingOrder->price) }} đ (Chờ duyệt)
                                                                </div>
                                                            @else
                                                                <span style="font-size:10.5px; color:#b45309; font-weight:700;">Chưa kích hoạt đơn</span>
                                                            @endif
                                                        @else
                                                            @php
                                                                $daysLeft = $u->expires_at ? (int) ceil(now()->diffInDays($u->expires_at, false)) : null;
                                                            @endphp
                                                            <div style="display:flex; gap:3px; flex-wrap:wrap; justify-content:center;">
                                                                @forelse($u->teacherLevels as $tl)
                                                                    <span class="pill-badge pill-grade" style="font-size:9.5px; padding:1.5px 5px; background:#dcfce7; color:#166534;">Khối {{ $tl->grade }}</span>
                                                                @empty
                                                                    <span style="font-size:10px; color:#ef4444; font-weight:750;">🔒 Chưa cấp Khối</span>
                                                                @endforelse
                                                            </div>
                                                            <div style="font-size:11px; color:#1e293b; font-weight:750;">
                                                                👥 Quota: <b>{{ $u->students_count ?? $u->students()->count() }}</b>/{{ $u->max_students ?: '∞' }} HS
                                                            </div>
                                                            @if($u->expires_at)
                                                                <div style="font-size:10.5px; font-weight:750; color: {{ $daysLeft < 0 ? '#dc2626' : ($daysLeft <= 30 ? '#d97706' : '#059669') }};">
                                                                    📅 {{ $u->expires_at->format('d/m/Y') }} ({{ $daysLeft < 0 ? 'Hết hạn ' . abs($daysLeft) . ' ngày' : 'Còn ' . $daysLeft . ' ngày' }})
                                                                </div>
                                                            @else
                                                                <div style="font-size:10.5px; color:#059669; font-weight:700;">
                                                                    ♾️ Vĩnh viễn
                                                                </div>
                                                            @endif
                                                        @endif
                                                    @elseif($u->isStudent())
                                                        @if($u->teacher)
                                                            <div style="font-size:11.5px; font-weight:750; color:#059669; text-align:center;" title="Giáo viên phụ trách">👩‍🏫 {{ $u->teacher->name }}</div>
                                                        @endif
                                                        <div style="display:flex; gap:3px; flex-wrap:wrap; justify-content:center;">
                                                            @forelse($u->accessibleLevels as $lvl)
                                                                <span class="pill-badge pill-grade" style="font-size:10px; padding:2px 6px;">Khối {{ $lvl->grade }}</span>
                                                            @empty
                                                                <span style="font-size:10px; color:#ef4444; font-weight:750;">🔒 Chưa mở</span>
                                                            @endforelse
                                                        </div>
                                                    @else
                                                        <span style="color:#64748b; font-weight:700; font-size:11.5px;">👑 Toàn quyền hệ thống</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td style="text-align:center; vertical-align:middle;">
                                                @if($u->isStudent())
                                                    <span class="pill-badge pill-time" style="font-size:11px; padding:3px 8px; font-weight:750; display:inline-flex; align-items:center; justify-content:center; gap:4px;">
                                                        📝 {{ $u->attempts_count ?? $u->attempts()->count() }} lượt
                                                    </span>
                                                @else
                                                    <span style="color:#94a3b8; font-size:12px; font-weight:600;">—</span>
                                                @endif
                                            </td>
                                            <td style="text-align:center; vertical-align:middle;">
                                                <div class="action-btn-group" style="justify-content:center; align-items:center; gap:4px; flex-wrap:nowrap;">
                                                    @if($u->isTeacher())
                                                        @if($isPending && $userPendingOrder)
                                                            <form method="POST" action="{{ route('admin.orders.activate', $userPendingOrder) }}" onsubmit="return confirm('Duyệt kích hoạt đơn #{{ $userPendingOrder->code }} và mở tài khoản cho giáo viên {{ addslashes($u->name) }}?');" style="display:inline;">
                                                                @csrf
                                                                <button type="submit" class="btn-action-grant" style="background:linear-gradient(135deg, #10b981, #059669); color:#fff; border:none; box-shadow:0 2px 6px rgba(16,185,129,0.3); padding:4px 8px;" title="Duyệt đơn thanh toán và kích hoạt tài khoản Giáo viên này">
                                                                    <span>⚡</span> Duyệt
                                                                </button>
                                                            </form>
                                                        @else
                                                            <button type="button" class="btn-action-view" onclick="openTeacherStudentsModal({{ $u->id }})" title="Mở danh sách học sinh thuộc Giáo viên này">
                                                                <span>👥</span> {{ $u->students_count ?? $u->students()->count() }} HS
                                                            </button>
                                                        @endif
                                                    @endif

                                                    <button type="button" class="btn-action-edit"
                                                        data-id="{{ $u->id }}"
                                                        data-name="{{ $u->name }}"
                                                        data-email="{{ $u->email }}"
                                                        data-code="{{ $u->student_code ?? '' }}"
                                                        data-role="{{ $uRoleStr }}"
                                                        data-status="{{ $u->status ?? 'active' }}"
                                                        data-teacher-id="{{ $u->created_by ?? '' }}"
                                                        data-levels='@json($u->accessibleLevels->pluck("id"))'
                                                        data-update-url="{{ route('admin.users.update', $u) }}"
                                                        onclick="openEditUserModal(this)" 
                                                        title="Chỉnh sửa thông tin & Đổi mật khẩu">
                                                        <span>✏️</span> Sửa
                                                    </button>

                                                    @if($u->isStudent())
                                                        <button type="button" class="btn-action-grant" onclick='openGrantModal(@json($u), @json($u->accessibleLevels->pluck("id")))' title="Cấp quyền mở khóa khối học">
                                                            <span>🔑</span> Khối
                                                        </button>
                                                    @elseif($u->isTeacher() && ! $isPending)
                                                        <button type="button" class="btn-action-grant" style="background:#059669; color:#ffffff;" onclick='openGrantTeacherModal(@json($u), @json($u->teacherLevels->pluck("id")), {{ (int)$u->max_students }}, "{{ $u->expires_at?->format("Y-m-d") ?? "" }}", "{{ $u->status ?? "active" }}")' title="Cấp gói & Phân quyền Khối học cho Giáo viên">
                                                            <span>👑</span> Gói
                                                        </button>
                                                    @endif

                                                    @if(auth()->user()->isAdmin() && ! auth()->user()->is($u))
                                                        <button type="button" class="btn-action-delete" onclick="deleteStudentAjax({{ $u->id }}, '{{ addslashes($u->name) }}')" title="Xóa tài khoản này">
                                                            <span>🗑️</span> Xóa
                                                        </button>
                                                    @elseif(auth()->user()->is($u))
                                                        <span class="badge-current-user" style="font-size:10px; padding:3px 6px;">✓ Đang dùng</span>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" style="text-align:center; padding:28px; color:#94a3b8;">Chưa có người dùng nào.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif
                </section>
            </div>

            @if(! $isTeacher)
            <!-- ========================================================= -->
            <!-- TAB 4: 💎 QUẢN TRỊ GÓI DỊCH VỤ & BẢN QUYỀN (TAB-PACKAGES) -->
            <!-- ========================================================= -->
            <div id="tab-packages" class="admin-tab-pane" style="display:none;">
                <!-- Toolbar Header -->
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:14px;">
                    <div>
                        <h2 style="font-size:18.5px; font-weight:900; color:#0f172a; display:flex; align-items:center; gap:8px;">
                            <span>💎</span> Quản Trị Gói Dịch Vụ & Bản Quyền IC3 GS6
                        </h2>
                        <p style="font-size:13px; color:var(--text-muted); margin-top:3px;">
                            Cấu hình bảng giá gói theo khối lớp & sĩ số học sinh, tự động duyệt đơn nâng cấp từ giáo viên
                        </p>
                    </div>
                    <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                        <a href="{{ route('pricing.index') }}" target="_blank" class="btn-ghost" title="Mở trang bảng giá xem thử giao diện khách hàng">
                            <span>🌐</span> Xem Bảng Giá Khách ➔
                        </a>
                        <button type="button" class="btn-primary" onclick="openCreatePackageModal()" style="display:inline-flex; align-items:center; gap:6px;">
                            <span>＋</span> Thêm Gói Mới
                        </button>
                    </div>
                </div>

                <!-- 📊 4 Thẻ chỉ số kinh doanh Gói & Đơn hàng -->
                <div class="stats-grid" style="margin-bottom: 24px;">
                    <div class="stat-card c-purple">
                        <div class="stat-header">
                            <span class="stat-label">TỔNG SỐ GÓI BẢN QUYỀN</span>
                            <div class="stat-icon">💎</div>
                        </div>
                        <div class="stat-value">{{ $packages->count() }} <small style="font-size:13px; font-weight:700; color:#64748b;">Gói</small></div>
                        <div class="stat-footer">
                            <span style="color:#10b981; font-weight:800;">● {{ $packages->where('is_active', true)->count() }}</span> đang mở bán
                        </div>
                    </div>

                    <div class="stat-card {{ $pendingOrdersCount > 0 ? 'c-amber' : 'c-blue' }}">
                        <div class="stat-header">
                            <span class="stat-label">ĐƠN CHỜ PHÊ DUYỆT</span>
                            <div class="stat-icon">⏳</div>
                        </div>
                        <div class="stat-value" style="{{ $pendingOrdersCount > 0 ? 'color:#d97706;' : '' }}">{{ $pendingOrdersCount }} <small style="font-size:13px; font-weight:700; color:#64748b;">Đơn</small></div>
                        <div class="stat-footer">
                            @if($pendingOrdersCount > 0)
                                <span style="color:#ef4444; font-weight:800;">⚠️ Cần duyệt kích hoạt ngay</span>
                            @else
                                <span style="color:#10b981; font-weight:800;">✓ Đã xử lý toàn bộ</span>
                            @endif
                        </div>
                    </div>

                    <div class="stat-card c-green">
                        <div class="stat-header">
                            <span class="stat-label">ĐƠN ĐÃ KÍCH HOẠT</span>
                            <div class="stat-icon">✅</div>
                        </div>
                        <div class="stat-value">{{ $activeOrdersCount }} <small style="font-size:13px; font-weight:700; color:#64748b;">Đơn</small></div>
                        <div class="stat-footer">
                            <span style="color:#059669; font-weight:800;">{{ $totalOrdersCount }}</span> tổng số đơn đã đặt
                        </div>
                    </div>

                    <div class="stat-card c-blue">
                        <div class="stat-header">
                            <span class="stat-label">DOANH THU BẢN QUYỀN</span>
                            <div class="stat-icon">💰</div>
                        </div>
                        <div class="stat-value" style="font-size: 20px;">{{ number_format($totalRevenue) }} <small style="font-size:13px; font-weight:700; color:#64748b;">đ</small></div>
                        <div class="stat-footer">
                            Doanh thu thực nhận từ đơn kích hoạt
                        </div>
                    </div>
                </div>

                <!-- 🔄 Segmented Sub-view switchers -->
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:18px; border-bottom:1.5px solid #e2e8f0; padding-bottom:12px; gap:12px; flex-wrap:wrap;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <button type="button" id="btn-pkg-subview-list" class="main-tab-btn active" onclick="switchPackageSubView('list', this)">
                            <span>📦</span> Danh Sách Gói Dịch Vụ ({{ $packages->count() }})
                        </button>
                        <button type="button" id="btn-pkg-subview-orders" class="main-tab-btn" onclick="switchPackageSubView('orders', this)">
                            <span>📋</span> Đơn Thuê & Duyệt Bản Quyền ({{ $packageOrders->count() }})
                            @if($pendingOrdersCount > 0)
                                <span style="background:#ef4444; color:#fff; font-size:10px; font-weight:800; padding:1px 6px; border-radius:99px; margin-left:6px;">{{ $pendingOrdersCount }} chờ</span>
                            @endif
                        </button>
                    </div>
                </div>

                <!-- SUB-VIEW 1: DANH SÁCH GÓI DỊCH VỤ -->
                <div id="pkg-subview-list" class="pkg-subview-pane">
                    <div class="table-card" style="border: 1.5px solid #e2e8f0; border-radius: 14px; overflow: hidden; background: #fff; box-shadow: var(--shadow-sm);">
                        <div class="table-responsive">
                            <table class="user-table" style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr>
                                        <th style="width:50px; text-align:center;">#</th>
                                        <th>TÊN GÓI & ĐẶC ĐIỂM</th>
                                        <th>GIÁ BÁN</th>
                                        <th>THỜI HẠN</th>
                                        <th>SĨ SỐ HỌC SINH</th>
                                        <th>KHỐI ĐƯỢC CẤP</th>
                                        <th style="text-align:center;">TRẠNG THÁI</th>
                                        <th style="text-align:right;">THAO TÁC</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($packages as $pkg)
                                        <tr>
                                            <td style="text-align:center;">
                                                <span class="badge-grade" style="background:#f1f5f9; color:#475569; font-weight:800; font-size:11.5px;">#{{ $pkg->position }}</span>
                                            </td>
                                            <td>
                                                <div style="display:flex; align-items:center; gap:8px;">
                                                    <b style="color:#0f172a; font-size:13.5px;">{{ $pkg->name }}</b>
                                                    @if($pkg->badge)
                                                        <span style="background:linear-gradient(135deg, #fef3c7, #fde68a); color:#92400e; font-size:10.5px; font-weight:800; padding:2px 7px; border-radius:6px; border:1px solid #fcd34d;">{{ $pkg->badge }}</span>
                                                    @endif
                                                </div>
                                                @if($pkg->description)
                                                    <p style="font-size:11.5px; color:#64748b; margin:3px 0 0; max-width:320px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $pkg->description }}</p>
                                                @endif
                                            </td>
                                            <td>
                                                <b style="color:#d97706; font-size:14px; font-weight:900;">{{ number_format($pkg->price) }} đ</b>
                                                @if($pkg->original_price && $pkg->original_price > $pkg->price)
                                                    <div style="font-size:11px; color:#94a3b8; text-decoration:line-through;">{{ number_format($pkg->original_price) }} đ</div>
                                                @endif
                                            </td>
                                            <td>
                                                <span style="font-weight:800; color:#334155; font-size:12.5px;">⏰ {{ $pkg->duration_days }} ngày</span>
                                            </td>
                                            <td>
                                                <span class="pill-badge pill-grade" style="background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; font-size:11.5px;">
                                                    👥 Tối đa {{ $pkg->max_students }} HS
                                                </span>
                                            </td>
                                            <td>
                                                <div style="display:flex; gap:4px; flex-wrap:wrap;">
                                                    @forelse($pkg->levels as $lvl)
                                                        <span class="pill-badge" style="background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; font-size:11px; font-weight:800; padding:2px 6px;">
                                                            Khối {{ $lvl->grade }}
                                                        </span>
                                                    @empty
                                                        <span style="color:#94a3b8; font-size:11.5px;">(Toàn bộ)</span>
                                                    @endforelse
                                                </div>
                                            </td>
                                            <td style="text-align:center;">
                                                <form method="POST" action="{{ route('admin.packages.toggle', $pkg) }}" style="display:inline;">
                                                    @csrf
                                                    <button type="submit" class="pill-badge {{ $pkg->is_active ? 'pill-pass' : 'pill-fail' }}" style="cursor:pointer; border:1px solid {{ $pkg->is_active ? '#86efac' : '#fca5a5' }};" title="Bấm để chuyển trạng thái mở bán">
                                                        {{ $pkg->is_active ? '🟢 Mở bán' : '⚪ Tạm ẩn' }}
                                                    </button>
                                                </form>
                                            </td>
                                            <td style="text-align:right;">
                                                <div style="display:inline-flex; align-items:center; gap:6px;">
                                                    <button type="button" class="btn-action-edit" onclick='openEditPackageModal(@json($pkg), @json($pkg->levels->pluck("id")))' title="Chỉnh sửa thông tin gói">
                                                        <span>✏️</span> Sửa
                                                    </button>
                                                    <form method="POST" action="{{ route('admin.packages.destroy', $pkg) }}" onsubmit="return confirm('Bạn có chắc muốn xóa gói \"{{ addslashes($pkg->name) }}\"?');" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn-action-delete" title="Xóa gói dịch vụ">
                                                            <span>🗑️</span> Xóa
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" style="text-align:center; padding:36px; color:#94a3b8;">
                                                Chưa có gói dịch vụ nào. Bấm "+ Thêm Gói Mới" để tạo gói đầu tiên.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- SUB-VIEW 2: ĐƠN THUÊ & PHÊ DUYỆT BẢN QUYỀN (TỐI GIẢN - RÕ RÀNG - ĐẸP MẮT - KHÔNG CUỘN NGANG) -->
                <div id="pkg-subview-orders" class="pkg-subview-pane" style="display:none;">
                    <style>
                        /* =====================================================================
                           💎 GIAO DIỆN QUẢN LÝ ĐƠN HÀNG ADMIN (TỐI GIẢN - HIỆN ĐẠI - ĐẸP MẮT)
                           ===================================================================== */
                        .orders-toolbar-wrap {
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            gap: 12px;
                            margin-bottom: 14px;
                            flex-wrap: wrap;
                        }
                        .orders-search-box {
                            position: relative;
                            flex: 1;
                            min-width: 240px;
                            max-width: 360px;
                        }
                        .orders-search-box input {
                            width: 100%;
                            height: 36px;
                            border: 1.5px solid #cbd5e1;
                            border-radius: 8px;
                            padding: 0 12px 0 34px;
                            font-size: 12.5px;
                            font-family: inherit;
                            color: #0f172a;
                            background: #ffffff;
                            transition: all 0.15s ease;
                            box-sizing: border-box;
                        }
                        .orders-search-box input:focus {
                            outline: none;
                            border-color: #4f46e5;
                            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
                        }
                        .orders-search-icon {
                            position: absolute;
                            left: 10px;
                            top: 50%;
                            transform: translateY(-50%);
                            font-size: 13px;
                            color: #94a3b8;
                            pointer-events: none;
                        }
                        .orders-filter-segmented {
                            display: inline-flex;
                            align-items: center;
                            background: #f1f5f9;
                            padding: 3px;
                            border-radius: 9px;
                            gap: 3px;
                            border: 1px solid #e2e8f0;
                        }
                        .order-filter-pill-tab {
                            border: none;
                            background: transparent;
                            color: #64748b;
                            font-size: 12px;
                            font-weight: 600;
                            padding: 5px 12px;
                            border-radius: 6px;
                            cursor: pointer;
                            transition: all 0.15s ease;
                            display: inline-flex;
                            align-items: center;
                            gap: 6px;
                            user-select: none;
                        }
                        .order-filter-pill-tab:hover {
                            color: #0f172a;
                        }
                        .order-filter-pill-tab.active {
                            background: #ffffff;
                            color: #0f172a;
                            font-weight: 800;
                            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
                        }
                        .filter-count-badge {
                            font-size: 10.5px;
                            font-weight: 800;
                            padding: 1px 6px;
                            border-radius: 999px;
                            background: #e2e8f0;
                            color: #475569;
                        }
                        .order-filter-pill-tab.active .filter-count-badge {
                            background: #0f172a;
                            color: #ffffff;
                        }
                        .filter-count-badge.count-pending {
                            background: #fef3c7;
                            color: #b45309;
                        }
                        .order-filter-pill-tab.active .filter-count-badge.count-pending {
                            background: #d97706;
                            color: #ffffff;
                        }

                        /* List Container - ZERO HORIZONTAL SCROLL & CLEAN CANVAS */
                        .orders-list-container {
                            display: flex;
                            flex-direction: column;
                            gap: 12px;
                            width: 100%;
                            box-sizing: border-box;
                            overflow-x: hidden;
                        }

                        /* Order Row Card (Sạch Sẽ, Tinh Tế, Đẳng Cấp) */
                        .order-card-box {
                            background: #ffffff;
                            border-radius: 10px;
                            transition: all 0.15s ease;
                            position: relative;
                            overflow: hidden;
                            width: 100%;
                            box-sizing: border-box;
                            border: 1px solid #e2e8f0;
                            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
                        }
                        .order-card-box:hover {
                            border-color: #cbd5e1;
                            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
                        }

                        /* Top Meta Row */
                        .order-card-meta-row {
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            gap: 10px;
                            padding: 9px 16px;
                            flex-wrap: wrap;
                            background: #f8fafc;
                            border-bottom: 1px solid #f1f5f9;
                        }
                        .order-meta-left {
                            display: flex;
                            align-items: center;
                            gap: 8px;
                            flex-wrap: wrap;
                        }
                        .ord-status-tag {
                            font-size: 11px;
                            font-weight: 700;
                            padding: 2.5px 8px;
                            border-radius: 5px;
                            display: inline-flex;
                            align-items: center;
                            gap: 4px;
                            letter-spacing: 0.1px;
                        }
                        .ord-status-tag.status-pending {
                            background: #fef3c7;
                            color: #92400e;
                            border: 1px solid #fde68a;
                        }
                        .ord-status-tag.status-active {
                            background: #ecfdf5;
                            color: #065f46;
                            border: 1px solid #a7f3d0;
                        }
                        .ord-status-tag.status-rejected {
                            background: #f1f5f9;
                            color: #64748b;
                            border: 1px solid #e2e8f0;
                        }
                        .ord-code-text {
                            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
                            font-size: 12px;
                            font-weight: 700;
                            color: #334155;
                            background: #ffffff;
                            border: 1px solid #e2e8f0;
                            padding: 2px 7px;
                            border-radius: 5px;
                            cursor: pointer;
                            transition: all 0.12s ease;
                        }
                        .ord-code-text:hover {
                            background: #f1f5f9;
                            border-color: #cbd5e1;
                            color: #0f172a;
                        }
                        .ord-date-text, .ord-paymethod-text {
                            font-size: 11.5px;
                            color: #64748b;
                            font-weight: 500;
                        }
                        .ord-price-pill {
                            font-size: 15px;
                            font-weight: 800;
                            color: #0f172a;
                            letter-spacing: -0.3px;
                        }
                        .ord-price-pill small {
                            font-size: 12px;
                            font-weight: 600;
                            color: #64748b;
                        }

                        /* Main Content Row */
                        .order-card-main {
                            display: grid;
                            grid-template-columns: minmax(0, 1.4fr) minmax(0, 1.1fr) 180px;
                            gap: 16px;
                            padding: 13px 16px;
                            align-items: center;
                            background: #ffffff;
                            box-sizing: border-box;
                        }
                        @media (max-width: 960px) {
                            .order-card-main {
                                grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
                                gap: 12px;
                            }
                            .ord-actions-zone {
                                grid-column: 1 / -1;
                                border-top: 1px solid #f1f5f9;
                                padding-top: 8px;
                            }
                        }
                        @media (max-width: 600px) {
                            .order-card-main {
                                grid-template-columns: 1fr;
                                gap: 10px;
                                padding: 12px 14px;
                            }
                        }

                        /* Customer Zone */
                        .ord-user-primary {
                            display: flex;
                            align-items: center;
                            gap: 9px;
                            margin-bottom: 4px;
                        }
                        .ord-user-avatar {
                            width: 32px;
                            height: 32px;
                            border-radius: 8px;
                            background: #eff6ff;
                            color: #1d4ed8;
                            border: 1px solid #dbeafe;
                            display: grid;
                            place-items: center;
                            font-size: 12px;
                            font-weight: 700;
                            flex-shrink: 0;
                        }
                        .ord-user-name {
                            font-size: 14px;
                            font-weight: 700;
                            color: #0f172a;
                            line-height: 1.2;
                        }
                        .ord-user-contacts {
                            display: flex;
                            align-items: center;
                            gap: 7px;
                            font-size: 12px;
                            color: #64748b;
                            flex-wrap: wrap;
                        }
                        .ord-zalo-link {
                            display: inline-flex;
                            align-items: center;
                            gap: 3px;
                            background: #eff6ff;
                            color: #2563eb;
                            border: 1px solid #bfdbfe;
                            padding: 1.5px 7px;
                            border-radius: 4px;
                            font-size: 11px;
                            font-weight: 700;
                            text-decoration: none;
                            transition: all 0.15s ease;
                        }
                        .ord-zalo-link:hover {
                            background: #2563eb;
                            color: #ffffff;
                            border-color: #2563eb;
                        }
                        .ord-school-badge {
                            font-size: 11px;
                            color: #475569;
                            background: #f8fafc;
                            border: 1px solid #e2e8f0;
                            padding: 1.5px 6px;
                            border-radius: 4px;
                            font-weight: 500;
                        }

                        /* Package Zone */
                        .ord-pkg-name {
                            font-size: 13.5px;
                            font-weight: 700;
                            color: #0f172a;
                            margin-bottom: 2px;
                        }
                        .ord-pkg-meta {
                            font-size: 12px;
                            color: #64748b;
                        }
                        .ord-clean-notes {
                            font-size: 11px;
                            color: #475569;
                            font-style: italic;
                            background: #f8fafc;
                            border-left: 2px solid #cbd5e1;
                            padding: 2px 7px;
                            margin-top: 4px;
                            border-radius: 0 4px 4px 0;
                        }
                        .ord-reject-reason {
                            font-size: 11px;
                            color: #991b1b;
                            background: #fef2f2;
                            border: 1px solid #fecaca;
                            padding: 3px 8px;
                            border-radius: 5px;
                            margin-top: 4px;
                        }
                        .ord-active-time {
                            font-size: 11px;
                            color: #059669;
                            margin-top: 3px;
                            font-weight: 600;
                        }

                        /* Actions Zone */
                        .ord-actions-zone {
                            display: flex;
                            flex-direction: column;
                            gap: 5px;
                            align-items: stretch;
                            justify-content: center;
                        }
                        .ord-btn-quick-activate {
                            width: 100%;
                            height: 34px;
                            border: none;
                            border-radius: 7px;
                            background: #2563eb;
                            color: #ffffff;
                            font-size: 12px;
                            font-weight: 700;
                            cursor: pointer;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            gap: 4px;
                            transition: all 0.15s ease;
                        }
                        .ord-btn-quick-activate:hover {
                            background: #1d4ed8;
                        }
                        .ord-btn-quick-reject {
                            width: 100%;
                            height: 28px;
                            border: 1px solid #e2e8f0;
                            border-radius: 6px;
                            background: #ffffff;
                            color: #64748b;
                            font-size: 11px;
                            font-weight: 600;
                            cursor: pointer;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            gap: 3px;
                            transition: all 0.15s ease;
                        }
                        .ord-btn-quick-reject:hover {
                            background: #fef2f2;
                            color: #dc2626;
                            border-color: #fecaca;
                        }
                        .ord-tag-done {
                            display: inline-flex;
                            align-items: center;
                            gap: 4px;
                            color: #059669;
                            font-weight: 700;
                            font-size: 11.5px;
                            background: #ecfdf5;
                            border: 1px solid #a7f3d0;
                            padding: 5px 9px;
                            border-radius: 6px;
                            justify-content: center;
                        }
                        .ord-tag-closed {
                            display: inline-flex;
                            align-items: center;
                            gap: 4px;
                            color: #64748b;
                            font-weight: 600;
                            font-size: 11.5px;
                            background: #f8fafc;
                            border: 1px solid #e2e8f0;
                            padding: 5px 9px;
                            border-radius: 6px;
                            justify-content: center;
                        }
                    </style>

                    <!-- 🛠️ THANH CÔNG CỤ TÌM KIẾM & BỘ LỌC SEGMENTED -->
                    <div class="orders-toolbar-wrap">
                        <div class="orders-search-box">
                            <span class="orders-search-icon">🔍</span>
                            <input type="text" id="order-search-input" placeholder="Tìm theo mã đơn, tên, email, SĐT, trường..." onkeyup="filterOrdersTable()">
                        </div>
                        <div class="orders-filter-segmented">
                            <button type="button" class="order-filter-pill-tab active" data-status="" onclick="filterOrdersTable('')">
                                Tất cả <span class="filter-count-badge">{{ $packageOrders->count() }}</span>
                            </button>
                            <button type="button" class="order-filter-pill-tab" data-status="pending" onclick="filterOrdersTable('pending')">
                                ⏳ Chờ duyệt <span class="filter-count-badge count-pending">{{ $pendingOrdersCount }}</span>
                            </button>
                            <button type="button" class="order-filter-pill-tab" data-status="active" onclick="filterOrdersTable('active')">
                                ✓ Đã kích hoạt <span class="filter-count-badge">{{ $activeOrdersCount }}</span>
                            </button>
                            <button type="button" class="order-filter-pill-tab" data-status="rejected" onclick="filterOrdersTable('rejected')">
                                ✕ Đã từ chối <span class="filter-count-badge">{{ $packageOrders->where('status', 'rejected')->count() }}</span>
                            </button>
                        </div>
                        <input type="hidden" id="order-status-filter" value="">
                    </div>

                    <!-- 📋 DANH SÁCH CARD ĐƠN HÀNG (TỐI GIẢN, RÕ RÀNG, KHÔNG RỐI MẮT) -->
                    <div class="orders-list-container" id="orders-tbody">
                        @forelse($packageOrders as $ord)
                            @php
                                $rawNotes = $ord->notes ?? '';
                                $userPhone = $ord->user?->phone;
                                if (!$userPhone && preg_match('/(?:SĐT|Điện thoại|Phone)[\s\:\-]+([0-9\+\s]{9,15})/iu', $rawNotes, $m)) {
                                    $userPhone = trim($m[1]);
                                }
                                $userSchool = $ord->user?->school_name;
                                if (!$userSchool && preg_match('/(?:Trường|Đơn vị)[\s\:\-\/]+([^\-\n\r,]+?)(?=\s*[\-\–]\s*|\s*SĐT|\s*Điện thoại|\s*Lý do|$)/iu', $rawNotes, $m)) {
                                    $userSchool = $m[1];
                                }
                                if ($userSchool) {
                                    $userSchool = preg_replace('/^(?:Trường|Đơn vị)[\s\:\-\/]+/iu', '', $userSchool);
                                    $userSchool = preg_replace('/[\s\-\–]*(?:SĐT|Điện thoại|Phone)[\s\:\-]+[0-9\+\s]+/iu', '', $userSchool);
                                    $userSchool = trim($userSchool, " \t\n\r\0\x0B-*:,./");
                                }

                                $rejectionReason = $ord->rejection_reason;
                                if (!$rejectionReason && preg_match('/Lý do từ chối:\s*(.+)$/iu', $rawNotes, $m)) {
                                    $rejectionReason = trim($m[1]);
                                }

                                // Làm sạch ghi chú: chỉ hiển thị nếu là ghi chú thực sự của khách
                                $cleanNotes = $rawNotes;
                                $cleanNotes = preg_replace('/Lý do từ chối:.*$/iu', '', $cleanNotes);
                                $cleanNotes = preg_replace('/[\*]*Trường[\/\s]*Đơn vị[\s\:\-]+[^\-\n\r,]+/iu', '', $cleanNotes);
                                $cleanNotes = preg_replace('/[\*]*(?:SĐT|Điện thoại|Phone)[\s\:\-]+[0-9\+\s]+/iu', '', $cleanNotes);
                                $cleanNotes = trim($cleanNotes, " \t\n\r\0\x0B-*:,./");
                            @endphp
                            <div class="order-row-item order-card-box status-{{ $ord->status }}"
                                 data-code="{{ strtolower($ord->code) }}"
                                 data-user="{{ strtolower($ord->user?->name ?? '') }}"
                                 data-email="{{ strtolower($ord->user?->email ?? '') }}"
                                 data-phone="{{ strtolower($userPhone ?? '') }}"
                                 data-school="{{ strtolower($userSchool ?? '') }}"
                                 data-status="{{ $ord->status }}">
                                
                                <!-- 1. THANH TIÊU ĐỀ: Trạng thái, Mã đơn, Thời gian, PTTT, Giá tiền -->
                                <div class="order-card-meta-row">
                                    <div class="order-meta-left">
                                        @if($ord->status === 'pending')
                                            <span class="ord-status-tag status-pending">⏳ Chờ duyệt</span>
                                        @elseif($ord->status === 'active')
                                            <span class="ord-status-tag status-active">✓ Đã kích hoạt</span>
                                        @elseif($ord->status === 'rejected')
                                            <span class="ord-status-tag status-rejected">✕ Đã từ chối</span>
                                        @else
                                            <span class="ord-status-tag">{{ $ord->status }}</span>
                                        @endif

                                        <span class="ord-code-text" onclick="navigator.clipboard.writeText('{{ $ord->code }}'); alert('Đã sao chép: {{ $ord->code }}');" title="Sao chép mã đơn">
                                            #{{ $ord->code }}
                                        </span>

                                        <span class="ord-date-text">📅 {{ $ord->created_at?->format('d/m/Y H:i') }}</span>
                                        <span class="ord-paymethod-text">· 💳 {{ $ord->payment_method === 'payos' ? 'Chuyển khoản QR' : ($ord->payment_method === 'bank_transfer' ? 'Chuyển khoản ngân hàng' : ($ord->payment_method_label ?? 'Chuyển khoản')) }}</span>
                                    </div>

                                    <div class="ord-price-pill">
                                        {{ number_format($ord->price) }} <small>đ</small>
                                    </div>
                                </div>

                                <!-- 2. NỘI DUNG CHÍNH: KHÁCH HÀNG | GÓI THUÊ | THAO TÁC -->
                                <div class="order-card-main">
                                    <!-- KHÁCH HÀNG -->
                                    <div>
                                        <div class="ord-user-primary">
                                            <div class="ord-user-avatar">
                                                {{ mb_strtoupper(mb_substr($ord->user?->name ?? 'GV', 0, 2)) }}
                                            </div>
                                            <div>
                                                <div class="ord-user-name">{{ $ord->user?->name ?? 'Khách vãng lai' }}</div>
                                            </div>
                                        </div>
                                        <div class="ord-user-contacts">
                                            <span>📧 {{ $ord->user?->email }}</span>
                                            @if($userPhone)
                                                <span>· 📞 <b>{{ $userPhone }}</b></span>
                                                <a href="https://zalo.me/{{ preg_replace('/[^0-9]/', '', $userPhone) }}" target="_blank" class="ord-zalo-link" title="Nhắn Zalo">
                                                    💬 Zalo
                                                </a>
                                            @endif
                                            @if($userSchool)
                                                <span class="ord-school-badge">🏫 {{ $userSchool }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- GÓI THUÊ -->
                                    <div>
                                        <div class="ord-pkg-name">{{ $ord->package_name }}</div>
                                        <div class="ord-pkg-meta">
                                            <span>⏰ <b>{{ $ord->duration_days }}</b> ngày dùng</span>
                                            <span> · 👥 Tối đa <b>{{ $ord->max_students }}</b> HS</span>
                                        </div>

                                        @if($cleanNotes)
                                            <div class="ord-clean-notes">"{{ $cleanNotes }}"</div>
                                        @endif

                                        @if($ord->status === 'rejected' && $rejectionReason)
                                            <div class="ord-reject-reason">❌ <b>Lý do:</b> {{ $rejectionReason }}</div>
                                        @endif

                                        @if($ord->status === 'active' && $ord->activated_at)
                                            <div class="ord-active-time">🛡️ Kích hoạt: {{ \Carbon\Carbon::parse($ord->activated_at)->format('d/m/Y H:i') }}</div>
                                        @endif
                                    </div>

                                    <!-- THAO TÁC -->
                                    <div class="ord-actions-zone">
                                        @if($ord->status === 'pending')
                                            <form method="POST" action="{{ route('admin.orders.activate', $ord) }}" onsubmit="return confirm('Kích hoạt ngay gói {{ addslashes($ord->package_name) }} cho giáo viên {{ addslashes($ord->user?->name) }}?');" style="width:100%;">
                                                @csrf
                                                <button type="submit" class="ord-btn-quick-activate" title="Duyệt đơn và cộng ngày/sĩ số ngay">
                                                    <span>⚡</span> Duyệt Kích Hoạt
                                                </button>
                                            </form>
                                            <button type="button" class="ord-btn-quick-reject" onclick="openRejectOrderModal({{ $ord->id }}, '{{ $ord->code }}', '{{ addslashes($ord->user?->name) }}')" title="Từ chối đơn">
                                                <span>✕</span> Từ chối đơn
                                            </button>
                                        @elseif($ord->status === 'active')
                                            <div class="ord-tag-done">
                                                <span>✓</span> Đã kích hoạt
                                            </div>
                                        @else
                                            <div class="ord-tag-closed">
                                                <span>✕</span> Đã từ chối
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div id="orders-empty-row" style="text-align:center; padding:40px 20px; background:#fff; border:1.5px dashed #cbd5e1; border-radius:12px; color:#94a3b8;">
                                <div style="font-size:32px; margin-bottom:8px;">📦</div>
                                <b style="font-size:14px; color:#475569;">Chưa có đơn thuê gói nào</b>
                                <p style="font-size:12px; margin-top:4px;">Khi giáo viên đặt mua gói từ trang bảng giá, đơn sẽ xuất hiện tại đây.</p>
                            </div>
                        @endforelse

                        <div id="orders-filter-empty" style="display:none; text-align:center; padding:36px 20px; background:#fff; border:1.5px dashed #cbd5e1; border-radius:12px; color:#94a3b8;">
                            <div style="font-size:28px; margin-bottom:6px;">🔍</div>
                            <b style="font-size:13.5px; color:#475569;">Không tìm thấy đơn hàng nào phù hợp</b>
                            <p style="font-size:12px; margin-top:4px;">Vui lòng thử đổi từ khóa tìm kiếm hoặc bấm tab "Tất cả".</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if($isTeacher)
            <!-- ========================================================= -->
            <!-- TAB: 💎 GÓI BẢN QUYỀN & LỊCH SỬ THUÊ GÓI DÀNH CHO GIÁO VIÊN (TAB-TEACHER-PACKAGES) -->
            <!-- ========================================================= -->
            <div id="tab-teacher-packages" class="admin-tab-pane" style="display:none; padding-top: 4px;">
                <!-- Toolbar Header Tối Giản, Gọn Gàng -->
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
                    <div>
                        <h2 style="font-size:20px; font-weight:900; color:#0f172a; display:flex; align-items:center; gap:8px; margin:0; letter-spacing: -0.3px;">
                            <span>💎</span> Gói Bản Quyền & Lịch Sử Thuê Gói
                        </h2>
                    </div>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <button type="button" onclick="openTeacherSupportChat('Kính gửi Ban Quản Trị, tôi muốn gửi yêu cầu cấp thêm số lượng học sinh / mở rộng khối lớp giảng dạy cho tài khoản của mình. Nhờ Ban Quản Trị hỗ trợ giúp tôi với ạ!')" style="font-size:12.5px; font-weight:800; color:#0284c7; border-radius:999px; padding:7px 18px; border:1.5px solid #bae6fd; background:#f0f9ff; box-shadow:0 2px 6px rgba(14,165,233,0.12); cursor:pointer; display:inline-flex; align-items:center; gap:6px; transition:all 0.2s;" onmouseover="this.style.background='#e0f2fe'" onmouseout="this.style.background='#f0f9ff'">
                            <span>💬</span> Yêu Cầu Cấp Thêm
                        </button>
                    </div>
                </div>

                <!-- 🌟 CARD THÔNG TIN BẢN QUYỀN: CHUẨN GAMIFIED 3D CARD (KHÔNG BỌC HỘP THỪA) -->
                <div style="background: #ffffff; border-radius: 24px; border: 3px solid #fbbf24; box-shadow: 0 16px 36px rgba(245, 158, 11, 0.12), 0 0 0 1px rgba(251, 191, 36, 0.2); padding: 28px 28px 24px; margin-bottom: 24px; position: relative;">
                    <!-- Badge Nổi 3D Nóc Thẻ -->
                    <div style="position: absolute; top: -14px; left: 28px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #ffffff; font-weight: 900; font-size: 11.5px; padding: 4px 16px; border-radius: 999px; border: 2.5px solid #ffffff; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.35); display: inline-flex; align-items: center; gap: 6px; letter-spacing: 0.5px;">
                        <span>👑</span> GÓI BẢN QUYỀN ĐANG KÍCH HOẠT
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 20px; padding-bottom: 18px; border-bottom: 1px dashed #fde68a;">
                        <div style="display: flex; align-items: center; gap: 16px;">
                            <!-- Orb Biểu Tượng 3D Vàng Hoàng Gia -->
                            <div style="width: 56px; height: 56px; border-radius: 18px; background: linear-gradient(135deg, #fef3c7, #fde68a); border: 2.5px solid #fbbf24; box-shadow: 0 6px 16px rgba(245, 158, 11, 0.25); display: grid; place-items: center; font-size: 28px; flex-shrink: 0;">
                                👑
                            </div>
                            <div>
                                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                    <h3 style="font-size: 22px; font-weight: 900; color: #1e1b4b; margin: 0; line-height: 1.2;">
                                        {{ $activeTeacherOrder ? $activeTeacherOrder->package_name : 'Gói Tiêu Chuẩn (Standard)' }}
                                    </h3>
                                    <span style="background: #dcfce7; border: 1.5px solid #86efac; color: #15803d; font-weight: 800; font-size: 11.5px; padding: 2.5px 11px; border-radius: 999px; display: inline-flex; align-items: center; gap: 5px; white-space: nowrap;">
                                        <span style="width: 6px; height: 6px; background: #16a34a; border-radius: 50%;"></span>
                                        Đang kích hoạt
                                    </span>
                                    @if($activeTeacherOrder)
                                        <span style="background: #f1f5f9; border: 1px solid #e2e8f0; color: #475569; font-size: 11.5px; font-weight: 800; padding: 2.5px 9px; border-radius: 6px; white-space: nowrap;">
                                            #{{ $activeTeacherOrder->code }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Nút Nâng Cấp Tactile 3D Chuẩn Bảng Giá -->
                        <div>
                            <a href="{{ route('pricing.index') }}" style="display: inline-flex; align-items: center; gap: 8px; background: linear-gradient(180deg, #ffc933 0%, #ff8e1c 100%); color: #4a2700; font-size: 13.5px; font-weight: 1000; padding: 10px 22px; border-radius: 999px; border: 2.5px solid #ffffff; text-decoration: none; box-shadow: 0 4px 0 #b35600, 0 6px 15px rgba(255, 142, 28, 0.4); transition: all 0.15s; white-space: nowrap;">
                                <span>✨</span> Nâng Cấp / Gia Hạn Gói
                            </a>
                        </div>
                    </div>

                    <!-- 3 PODS NĂNG LƯỢNG ĐA SẮC MÀU (BỎ VIỀN QUÁ DÀY, GỌN GÀNG, SANG TRỌNG) -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
                        <!-- Pod 1: Sĩ số học sinh (Emerald) -->
                        <div style="background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%); border: 1.5px solid #a7f3d0; border-radius: 18px; padding: 18px 20px; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.08);">
                            <div style="font-size: 11.5px; font-weight: 900; color: #065f46; text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.3px;">
                                👥 SĨ SỐ HỌC SINH
                            </div>
                            <div style="font-size: 24px; font-weight: 900; color: #059669; margin-bottom: 8px; white-space: nowrap;">
                                {{ $usedStudents }} <span style="font-size: 14px; font-weight: 800; color: #047857;">/ {{ $maxStudents ?: '∞' }} HS</span>
                            </div>
                            @php
                                $quotaPct = $maxStudents > 0 ? min(100, round(($usedStudents / $maxStudents) * 100)) : 100;
                            @endphp
                            <div style="height: 8px; background: #d1fae5; border-radius: 999px; overflow: hidden; margin-bottom: 8px;">
                                <div style="height: 100%; width: {{ $quotaPct }}%; background: linear-gradient(90deg, #10b981, #059669); border-radius: 999px;"></div>
                            </div>
                            <div style="font-size: 12px; color: #047857; font-weight: 700; white-space: nowrap;">
                                Còn trống: <b style="color: #065f46; font-size: 13px;">{{ $remainingSlots > 10000 ? 'Không giới hạn' : $remainingSlots . ' suất' }}</b>
                            </div>
                        </div>

                        <!-- Pod 2: Khối lớp giảng dạy (Sky Blue) -->
                        <div style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border: 1.5px solid #bae6fd; border-radius: 18px; padding: 18px 20px; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.08);">
                            <div style="font-size: 11.5px; font-weight: 900; color: #0369a1; text-transform: uppercase; margin-bottom: 10px; letter-spacing: 0.3px;">
                                🔑 KHỐI ĐƯỢC PHÂN QUYỀN
                            </div>
                            <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 8px;">
                                @forelse($teacherLevels as $tl)
                                    <span style="background: linear-gradient(135deg, #0284c7, #0369a1); color: #ffffff; border: 1.5px solid #ffffff; box-shadow: 0 3px 8px rgba(2, 132, 199, 0.25); border-radius: 8px; padding: 4px 12px; font-weight: 900; font-size: 13px; white-space: nowrap;">
                                        Khối {{ $tl->grade }}
                                    </span>
                                @empty
                                    <span style="color: #64748b; font-size: 12.5px; font-weight: 700;">(Chưa phân khối)</span>
                                @endforelse
                            </div>
                            <div style="font-size: 11.5px; color: #0284c7; font-weight: 700;">
                                Đã mở khóa học sinh & báo cáo
                            </div>
                        </div>

                        <!-- Pod 3: Thời hạn bản quyền (Violet Amber) -->
                        <div style="background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%); border: 1.5px solid #e9d5ff; border-radius: 18px; padding: 18px 20px; box-shadow: 0 4px 14px rgba(124, 58, 237, 0.08);">
                            <div style="font-size: 11.5px; font-weight: 900; color: #6b21a8; text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.3px;">
                                📅 THỜI HẠN BẢN QUYỀN
                            </div>
                            <div style="font-size: 22px; font-weight: 900; color: #581c87; margin-bottom: 8px; white-space: nowrap;">
                                {{ $expiresAt ? $expiresAt->format('d/m/Y') : 'Vĩnh viễn' }}
                            </div>
                            <div>
                                @if($expiresAt)
                                    @if($expiresAt->isPast())
                                        <span style="background: #fef2f2; border: 1.5px solid #fca5a5; color: #b91c1c; font-weight: 900; font-size: 12px; padding: 3px 12px; border-radius: 999px; white-space: nowrap;">
                                            ⚠️ Đã hết hạn
                                        </span>
                                    @else
                                        <span style="background: #ffffff; border: 1.5px solid #c084fc; color: #7e22ce; font-weight: 900; font-size: 12px; padding: 3px 12px; border-radius: 999px; box-shadow: 0 2px 6px rgba(192, 132, 252, 0.2); white-space: nowrap;">
                                            🟢 Còn {{ (int) max(0, ceil(now()->floatDiffInDays($expiresAt, false))) }} ngày
                                        </span>
                                    @endif
                                @else
                                    <span style="background: #ffffff; border: 1.5px solid #c084fc; color: #7e22ce; font-weight: 900; font-size: 12px; padding: 3px 12px; border-radius: 999px; white-space: nowrap;">
                                        🟢 Không giới hạn
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 📜 CARD 2: BẢNG LỊCH SỬ ĐƠN HÀNG (CARD NỔI RIÊNG, KHÔNG BỌC THỪA, TABLE KHÔNG BỊ VỠ) -->
                <div style="border-radius: 22px; border: 1.5px solid #e2e8f0; box-shadow: 0 8px 24px rgba(0,0,0,0.04); padding: 22px 24px; background: #ffffff;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; flex-wrap: wrap; gap: 12px;">
                        <div>
                            <h3 style="font-size: 17px; font-weight: 900; color: #0f172a; display: flex; align-items: center; gap: 8px; margin: 0;">
                                <span>📜</span> Lịch Sử Thuê Gói
                            </h3>
                        </div>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="font-size: 12px; font-weight: 900; color: #1d4ed8; background: linear-gradient(135deg, #eff6ff, #dbeafe); padding: 4px 14px; border-radius: 999px; border: 1px solid #bfdbfe; box-shadow: 0 2px 4px rgba(59, 130, 246, 0.1);">
                                Tổng cộng: {{ $packageOrders->count() }} đơn hàng
                            </span>
                        </div>
                    </div>

                    @if($packageOrders->isEmpty())
                        <div style="text-align: center; padding: 48px 20px; background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 14px;">
                            <div style="font-size: 42px; margin-bottom: 10px;">🛡️</div>
                            <h4 style="font-size: 15px; font-weight: 800; color: #334155; margin-bottom: 6px;">
                                Thầy/Cô đang sử dụng gói bản quyền do Ban Quản Trị cấp trực tiếp
                            </h4>
                            <p style="font-size: 13px; color: #64748b; max-width: 520px; margin: 0 auto 16px;">
                                Chưa có đơn hàng phát sinh trên cổng thanh toán trực tuyến. Thầy/Cô vẫn được sử dụng đầy đủ các tính năng giảng dạy theo hạn mức hiện tại.
                            </p>
                            <a href="{{ route('pricing.index') }}" class="btn-primary" style="display: inline-flex; align-items: center; gap: 6px; text-decoration: none; padding: 8px 18px;">
                                <span>✨</span> Khám Phá Các Gói Bản Quyền IC3 GS6
                            </a>
                        </div>
                    @else
                        @php
                            $pendingOrder = $packageOrders->firstWhere('status', 'pending');
                        @endphp
                        @if($pendingOrder)
                            <div style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border: 1.5px solid #fde68a; border-radius: 12px; padding: 12px 18px; margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <span style="font-size: 20px;">⏳</span>
                                    <div>
                                        <div style="font-weight: 800; font-size: 13px; color: #92400e;">
                                            Đơn hàng #{{ $pendingOrder->code }} ({{ $pendingOrder->package_name }}) đang chờ kích hoạt
                                        </div>
                                        <div style="font-size: 11.5px; color: #b45309;">
                                            Hệ thống đang đối soát ngân hàng. Thầy/Cô có thể bấm "Nhắn Admin Duyệt Ngay" để được kích hoạt trong ít phút!
                                        </div>
                                    </div>
                                </div>
                                <button type="button" onclick="openTeacherSupportChat('Kính gửi Ban Quản Trị, tôi vừa chuyển khoản cho đơn hàng #{{ $pendingOrder->code }}. Nhờ Ban Quản Trị kiểm tra và duyệt kích hoạt giúp tôi với ạ!')" style="padding: 6px 14px; font-size: 12px; font-weight: 800; color: #ffffff; background: #d97706; border: none; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; box-shadow: 0 2px 4px rgba(217, 119, 6, 0.2);">
                                    <span>💬</span> Nhắn Admin Duyệt Ngay
                                </button>
                            </div>
                        @endif

                        <!-- Lưới Table Chuẩn SaaS, Không Bị Ngắt Dòng Rối Mắt, Bỏ Border Dọc Thừa -->
                        <div style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; min-width: 900px;">
                                <thead>
                                    <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                                        <th style="padding: 12px 14px; font-size: 12px; font-weight: 900; color: #475569; text-align: center; width: 45px; white-space: nowrap;">#</th>
                                        <th style="padding: 12px 16px; font-size: 12px; font-weight: 900; color: #475569; text-align: center; white-space: nowrap;">MÃ ĐƠN</th>
                                        <th style="padding: 12px 18px; font-size: 12px; font-weight: 900; color: #475569; text-align: left;">TÊN GÓI BẢN QUYỀN</th>
                                        <th style="padding: 12px 16px; font-size: 12px; font-weight: 900; color: #475569; text-align: center; white-space: nowrap;">SỐ TIỀN</th>
                                        <th style="padding: 12px 14px; font-size: 12px; font-weight: 900; color: #475569; text-align: center; white-space: nowrap;">THỜI HẠN</th>
                                        <th style="padding: 12px 14px; font-size: 12px; font-weight: 900; color: #475569; text-align: center; white-space: nowrap;">SĨ SỐ CẤP</th>
                                        <th style="padding: 12px 14px; font-size: 12px; font-weight: 900; color: #475569; text-align: center; white-space: nowrap;">THANH TOÁN</th>
                                        <th style="padding: 12px 16px; font-size: 12px; font-weight: 900; color: #475569; text-align: center; white-space: nowrap;">TRẠNG THÁI</th>
                                        <th style="padding: 12px 16px; font-size: 12px; font-weight: 900; color: #475569; text-align: center; white-space: nowrap;">NGÀY TẠO</th>
                                        <th style="padding: 12px 14px; font-size: 12px; font-weight: 900; color: #475569; text-align: center; white-space: nowrap;">THAO TÁC</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($packageOrders as $index => $o)
                                        <tr style="border-bottom: 1px solid #f1f5f9; background: {{ $loop->even ? '#fcfcfd' : '#ffffff' }}; transition: background 0.15s;">
                                            <td style="padding: 14px 12px; text-align: center; font-weight: 800; color: #94a3b8; white-space: nowrap;">
                                                {{ $index + 1 }}
                                            </td>
                                            <td style="padding: 14px 16px; text-align: center; white-space: nowrap;">
                                                @if($o->isAdjustment())
                                                    <span style="font-family: 'SF Mono', Consolas, monospace; font-weight: 800; font-size: 12px; color: #b45309; background: #fef3c7; padding: 4px 10px; border-radius: 6px; border: 1px solid #fde68a; display: inline-block; white-space: nowrap;" title="Đơn cấp thêm / điều chỉnh">
                                                        #{{ $o->code }}
                                                    </span>
                                                @else
                                                    <span style="font-family: 'SF Mono', Consolas, monospace; font-weight: 800; font-size: 12px; color: #1e293b; background: #f1f5f9; padding: 4px 10px; border-radius: 6px; border: 1px solid #e2e8f0; display: inline-block; white-space: nowrap;">
                                                        #{{ $o->code }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td style="padding: 14px 18px;">
                                                <div style="font-weight: 800; color: #0f172a; font-size: 13.5px; display: flex; align-items: center; gap: 6px;">
                                                    @if($o->isAdjustment())
                                                        <span>🎁</span>
                                                    @endif
                                                    <span>{{ $o->package_name }}</span>
                                                </div>
                                                <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">
                                                    @if($o->isAdjustment() && $o->parentOrder)
                                                        <span style="color: #0284c7;">Thuộc đơn gốc: <b>#{{ $o->parentOrder->code }}</b></span>
                                                    @elseif(! empty($o->levels_snapshot))
                                                        Khối: {{ $o->levels_snapshot }}
                                                    @elseif($o->package && $o->package->levels && $o->package->levels->isNotEmpty())
                                                        Khối: {{ $o->package->levels->pluck('grade')->map(fn($g) => 'Khối ' . $g)->join(', ') }}
                                                    @else
                                                        Áp dụng toàn bộ khối
                                                    @endif
                                                </div>
                                            </td>
                                            <td style="padding: 14px 16px; text-align: center; white-space: nowrap;">
                                                <span style="font-weight: 900; color: #0284c7; font-size: 14px; white-space: nowrap;">
                                                    {{ $o->formatted_price }}
                                                </span>
                                            </td>
                                            <td style="padding: 14px 14px; text-align: center; font-weight: 700; color: #334155; white-space: nowrap;">
                                                @if($o->isAdjustment())
                                                    <span style="font-size: 11.5px; color: #059669; background: #ecfdf5; padding: 3px 8px; border-radius: 6px; border: 1px solid #a7f3d0;">Kèm gói chính</span>
                                                @else
                                                    {{ $o->duration_days }} ngày
                                                @endif
                                            </td>
                                            <td style="padding: 14px 14px; text-align: center; font-weight: 700; color: #334155; white-space: nowrap;">
                                                @if($o->isAdjustment())
                                                    <span style="color: #0284c7; font-weight: 900;">+{{ $o->max_students }} HS</span>
                                                @else
                                                    {{ $o->max_students > 0 ? $o->max_students . ' HS' : 'Không giới hạn' }}
                                                @endif
                                            </td>
                                            <td style="padding: 14px 14px; text-align: center; white-space: nowrap;">
                                                <span style="font-size: 11.5px; font-weight: 800; color: #475569; background: #f8fafc; padding: 3px 8px; border-radius: 6px; border: 1px solid #e2e8f0; white-space: nowrap;">
                                                    {{ $o->payment_method_label }}
                                                </span>
                                            </td>
                                            <td style="padding: 14px 16px; text-align: center; white-space: nowrap;">
                                                @if($o->isPending())
                                                    <span style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 12px; border-radius: 999px; font-size: 11.5px; font-weight: 800; background: #fef3c7; color: #b45309; border: 1px solid #fde68a; white-space: nowrap;">
                                                        ⏳ Chờ duyệt
                                                    </span>
                                                @elseif($o->isActive())
                                                    <span style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 12px; border-radius: 999px; font-size: 11.5px; font-weight: 800; background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; white-space: nowrap;">
                                                        🟢 Đã kích hoạt
                                                    </span>
                                                @else
                                                    <span style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 12px; border-radius: 999px; font-size: 11.5px; font-weight: 800; background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; white-space: nowrap;">
                                                        🔴 Từ chối
                                                    </span>
                                                @endif
                                            </td>
                                            <td style="padding: 14px 16px; text-align: center; font-size: 12px; color: #64748b; font-weight: 600; white-space: nowrap;">
                                                {{ $o->created_at->format('d/m/Y H:i') }}
                                            </td>
                                            <td style="padding: 14px 14px; text-align: center; white-space: nowrap;">
                                                @if($o->isPending())
                                                    <div style="display: flex; align-items: center; justify-content: center; gap: 6px;">
                                                        <a href="{{ route('pricing.order.checkout', $o) }}" class="btn-excel" style="padding: 5px 10px; font-size: 11.5px; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap;" title="Xem mã QR để thanh toán">
                                                            <span>📱</span> Quét QR
                                                        </a>
                                                        <button type="button" onclick="openTeacherSupportChat('Kính gửi Ban Quản Trị, tôi vừa chuyển khoản cho đơn hàng #{{ $o->code }}. Nhờ Ban Quản Trị kiểm tra và duyệt kích hoạt giúp tôi với ạ!')" style="padding: 5px 10px; font-size: 11.5px; font-weight: 800; color: #d97706; background: #fef3c7; border: 1px solid #fde68a; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap; transition: all 0.2s;" onmouseover="this.style.background='#fde68a'" onmouseout="this.style.background='#fef3c7'" title="Nhắn Ban Quản Trị kích hoạt ngay">
                                                            <span>⚡</span> Nhắn Duyệt
                                                        </button>
                                                    </div>
                                                @elseif($o->isActive())
                                                    <span style="color: #059669; font-weight: 800; font-size: 12px; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap;">
                                                        <span>✓</span> Đang dùng
                                                    </span>
                                                @else
                                                    <span style="color: #94a3b8; font-size: 11.5px;">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
            @endif

            @if(! $isTeacher)
            <!-- ========================================================= -->
            <!-- TAB 5: 💬 TRUNG TÂM LIVE CHAT MESSENGER (CHUẨN MESSENGER WEB THẬT) -->
            <!-- ========================================================= -->
            <div id="tab-chat" class="admin-tab-pane" style="display:none; height: 100%;">
                <style>
                    /* ==========================================================================
                       🎨 TRUNG TÂM LIVE CHAT MESSENGER QUẢN TRỊ (GAMIFIED 3D UI, ĐA SẮC MÀU, CANH CHUẨN)
                       ========================================================================== */
                    body.tab-chat-active .content {
                        max-width: 100% !important;
                        padding: 12px 24px 28px !important;
                    }

                    .ms-desktop-wrap {
                        display: grid;
                        grid-template-columns: 335px minmax(400px, 1fr) 295px;
                        height: calc(100vh - 150px);
                        max-height: calc(100vh - 150px);
                        min-height: 520px;
                        background: #ffffff;
                        border-radius: 18px;
                        border: 2.5px solid #cbd5e1;
                        box-shadow: 0 16px 36px rgba(15, 23, 42, 0.10), inset 0 -2px 0 rgba(0, 0, 0, 0.04);
                        overflow: hidden;
                        font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                        transition: grid-template-columns 0.25s cubic-bezier(0.16, 1, 0.3, 1);
                    }
                    .ms-desktop-wrap.drawer-collapsed {
                        grid-template-columns: 335px 1fr 0px;
                    }

                    /* ===== CỘT 1: DANH SÁCH HỘI THOẠI ===== */
                    .ms-sidebar {
                        background: #f8fafc;
                        border-right: 2px solid #e2e8f0;
                        display: flex;
                        flex-direction: column;
                        height: 100%;
                        max-height: 100%;
                        min-width: 0;
                        overflow: hidden;
                    }
                    .ms-sidebar-header {
                        padding: 14px 14px 12px;
                        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
                        border-bottom: 2px solid #e2e8f0;
                        flex-shrink: 0;
                    }
                    .ms-head-title-row {
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        margin-bottom: 10px;
                    }
                    .ms-head-title-row h2 {
                        font-size: 15px;
                        font-weight: 850;
                        color: #0f172a;
                        margin: 0;
                        display: flex;
                        align-items: center;
                        gap: 7px;
                        letter-spacing: -0.3px;
                    }
                    .ms-head-icons { display: flex; gap: 6px; }
                    .ms-circle-btn {
                        width: 32px;
                        height: 32px;
                        border-radius: 50%;
                        background: #ffffff;
                        border: 1.5px solid #cbd5e1;
                        display: grid;
                        place-items: center;
                        cursor: pointer;
                        color: #475569;
                        font-size: 13px;
                        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
                        transition: all 0.15s;
                    }
                    .ms-circle-btn:hover { background: #f1f5f9; color: #0284c7; border-color: #94a3b8; transform: scale(1.06); }

                    .ms-search-pill {
                        background: #ffffff;
                        border: 2px solid #e2e8f0;
                        border-radius: 12px;
                        padding: 7px 12px 7px 32px;
                        position: relative;
                        display: flex;
                        align-items: center;
                        margin-bottom: 10px;
                        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
                        transition: all 0.2s;
                    }
                    .ms-search-pill:focus-within {
                        border-color: #0284c7;
                        background: #ffffff;
                        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.12);
                    }
                    .ms-search-pill input {
                        background: transparent;
                        border: none;
                        outline: none;
                        font-size: 12.5px;
                        color: #0f172a;
                        width: 100%;
                        font-family: inherit;
                        font-weight: 600;
                    }
                    .ms-search-pill input::placeholder { color: #94a3b8; font-weight: 500; }
                    .ms-search-pill span.icon { position: absolute; left: 10px; color: #94a3b8; font-size: 12px; }

                    .ms-filter-tabs { display: flex; gap: 6px; }
                    .ms-filter-chip {
                        flex: 1;
                        padding: 6px 6px;
                        border-radius: 9px;
                        font-size: 11px;
                        font-weight: 800;
                        border: 1.5px solid #e2e8f0;
                        background: #ffffff;
                        color: #64748b;
                        cursor: pointer;
                        text-align: center;
                        white-space: nowrap;
                        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
                        transition: all 0.15s ease;
                    }
                    .ms-filter-chip:hover:not(.active) { background: #f1f5f9; color: #1e293b; border-color: #cbd5e1; }
                    .ms-filter-chip#chip-filter-all.active {
                        background: #2563eb; color: #ffffff; border-color: #2563eb;
                        box-shadow: 0 3px 10px rgba(37, 99, 235, 0.3);
                    }
                    .ms-filter-chip#chip-filter-pending {
                        background: #fff7ed; border-color: #fed7aa; color: #c2410c;
                    }
                    .ms-filter-chip#chip-filter-pending.active {
                        background: linear-gradient(135deg, #ea580c, #f97316);
                        color: #ffffff; border-color: #ea580c;
                        box-shadow: 0 3px 10px rgba(234, 88, 12, 0.35);
                    }
                    .ms-filter-chip#chip-filter-replied {
                        background: #ecfdf5; border-color: #a7f3d0; color: #059669;
                    }
                    .ms-filter-chip#chip-filter-replied.active {
                        background: #10b981; color: #ffffff; border-color: #10b981;
                        box-shadow: 0 3px 10px rgba(16, 185, 129, 0.3);
                    }

                    .ms-conv-scroll {
                        flex: 1 1 0;
                        min-height: 0;
                        overflow-y: auto;
                        padding: 8px 8px;
                        display: flex;
                        flex-direction: column;
                        gap: 5px;
                        background: #f8fafc;
                    }
                    .ms-conv-scroll::-webkit-scrollbar { width: 4px; }
                    .ms-conv-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

                    /* Thẻ Hội Thoại: Canh Chuẩn 3 Cột Thẳng Tắp */
                    .ms-conv-item {
                        display: flex;
                        align-items: center;
                        gap: 10px;
                        padding: 10px 10px;
                        border-radius: 12px;
                        cursor: pointer;
                        transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1);
                        position: relative;
                        background: #ffffff;
                        border: 1.5px solid #eef2f6;
                        border-left: 4.5px solid transparent;
                        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
                    }
                    .ms-conv-item:hover {
                        background: #f8fafc;
                        border-color: #cbd5e1;
                        transform: translateX(2px);
                    }

                    /* 🔥 THẺ CHƯA REP: NỀN MÀU NỔI BẬT & VIỀN CAM HỔ PHÁCH RỰC RỠ */
                    .ms-conv-item.is-unread {
                        background: #fff8eb !important;
                        border-color: #fed7aa !important;
                        border-left: 4.5px solid #f97316 !important;
                        box-shadow: 0 3px 10px rgba(249, 115, 22, 0.10) !important;
                    }
                    .ms-conv-item.is-unread:hover {
                        background: #ffedd5 !important;
                        border-color: #fdba74 !important;
                    }
                    .ms-conv-item.is-unread .ms-item-name {
                        color: #9a3412 !important;
                        font-weight: 850 !important;
                    }
                    .ms-conv-item.is-unread .ms-item-snippet {
                        color: #c2410c !important;
                        font-weight: 700 !important;
                    }

                    /* 🌟 THẺ ĐANG ĐƯỢC CHỌN (ACTIVE) */
                    .ms-conv-item.active {
                        background: #eff6ff !important;
                        border-color: #93c5fd !important;
                        border-left: 4.5px solid #2563eb !important;
                        box-shadow: 0 4px 14px rgba(37, 99, 235, 0.16) !important;
                    }
                    .ms-conv-item.active .ms-item-name {
                        color: #1e40af !important;
                        font-weight: 850 !important;
                    }

                    /* 1. Khối Avatar bên trái */
                    .ms-item-avatar-wrap {
                        position: relative;
                        width: 42px;
                        height: 42px;
                        flex-shrink: 0;
                    }
                    .ms-item-avatar {
                        width: 42px;
                        height: 42px;
                        border-radius: 50%;
                        display: grid;
                        place-items: center;
                        font-size: 14px;
                        font-weight: 850;
                        color: #ffffff;
                        box-shadow: 0 2px 8px rgba(0,0,0,0.12);
                    }
                    .ms-online-badge {
                        position: absolute;
                        bottom: 0px;
                        right: 0px;
                        width: 11px;
                        height: 11px;
                        border-radius: 50%;
                        background: #10b981;
                        border: 2px solid #ffffff;
                    }

                    /* 2. Khối Giữa: Tên & Snippet */
                    .ms-item-center {
                        flex: 1 1 0;
                        min-width: 0;
                        display: flex;
                        flex-direction: column;
                        justify-content: center;
                        gap: 3px;
                    }
                    .ms-item-name {
                        font-size: 13px;
                        font-weight: 800;
                        color: #0f172a;
                        white-space: nowrap;
                        overflow: hidden;
                        text-overflow: ellipsis;
                        line-height: 1.25;
                    }
                    .ms-item-snippet {
                        font-size: 11.5px;
                        color: #64748b;
                        white-space: nowrap;
                        overflow: hidden;
                        text-overflow: ellipsis;
                        line-height: 1.2;
                    }
                    .snippet-you { color: #0284c7; }
                    .snippet-guest { color: #475569; }

                    /* 3. Khối Meta Phải (CỐ ĐỊNH CHIỀU RỘNG, CANH THẲNG HÀNG 100% THEO CHIỀU DỌC) */
                    .ms-item-meta-right {
                        width: 70px;
                        flex-shrink: 0;
                        display: flex;
                        flex-direction: column;
                        align-items: flex-end;
                        justify-content: space-between;
                        gap: 4px;
                        height: 38px;
                    }
                    .ms-role-cell {
                        display: flex;
                        justify-content: flex-end;
                        width: 100%;
                    }
                    .ms-user-tag {
                        font-size: 9.5px;
                        font-weight: 800;
                        padding: 1.5px 6px;
                        border-radius: 5px;
                        white-space: nowrap;
                        display: inline-block;
                    }
                    .ms-user-tag.tag-guest {
                        background: #f3e8ff; color: #7c3aed; border: 1px solid #d8b4fe;
                    }
                    .ms-user-tag.tag-teacher {
                        background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0;
                    }
                    .ms-user-tag.tag-student {
                        background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd;
                    }

                    .ms-status-cell {
                        display: flex;
                        justify-content: flex-end;
                        align-items: center;
                        width: 100%;
                    }

                    /* 🔴 NÚT ĐỎ NHẤP NHÁY PHÁT SÁNG (PULSING GLOW ANIMATION) CHO TIN CHƯA REP */
                    .ms-pending-pulse-badge {
                        display: inline-flex;
                        align-items: center;
                        gap: 4px;
                        background: #fef2f2;
                        color: #dc2626;
                        border: 1px solid #fca5a5;
                        padding: 1px 6px;
                        border-radius: 999px;
                        font-size: 9.5px;
                        font-weight: 850;
                        position: relative;
                        box-shadow: 0 1px 4px rgba(239, 68, 68, 0.2);
                        white-space: nowrap;
                    }
                    .ms-pending-pulse-badge .pulse-core {
                        width: 6px;
                        height: 6px;
                        border-radius: 50%;
                        background: #ef4444;
                        position: relative;
                        z-index: 2;
                    }
                    .ms-pending-pulse-badge .pulse-ring {
                        position: absolute;
                        left: 6px;
                        top: 50%;
                        width: 6px;
                        height: 6px;
                        border-radius: 50%;
                        background: rgba(239, 68, 68, 0.7);
                        animation: msPulseRing 1.5s cubic-bezier(0.24, 0, 0.38, 1) infinite;
                        z-index: 1;
                    }
                    @keyframes msPulseRing {
                        0% { transform: translateY(-50%) scale(1); opacity: 0.9; }
                        70% { transform: translateY(-50%) scale(3); opacity: 0; }
                        100% { transform: translateY(-50%) scale(3.4); opacity: 0; }
                    }
                    .ms-pending-pulse-badge.is-hidden { display: none !important; }
                    .ms-replied-tag {
                        font-size: 10px;
                        color: #94a3b8;
                        font-weight: 700;
                        white-space: nowrap;
                    }
                    .ms-replied-tag.is-hidden { display: none !important; }

                    /* ===== CỘT 2: KHUNG CHAT CHÍNH ===== */
                    .ms-chat-main {
                        display: flex;
                        flex-direction: column;
                        height: 100%;
                        max-height: 100%;
                        background: #f1f5f9;
                        min-width: 0;
                        overflow: hidden;
                        border-right: 2px solid #e2e8f0;
                    }
                    .ms-chat-header {
                        height: 60px;
                        padding: 0 18px;
                        background: #ffffff;
                        border-bottom: 2px solid #e2e8f0;
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        flex-shrink: 0;
                        z-index: 2;
                        box-shadow: 0 1px 4px rgba(0,0,0,0.03);
                    }
                    .ms-header-user { display: flex; align-items: center; gap: 11px; min-width: 0; }
                    .ms-header-avatar {
                        width: 40px; height: 40px; border-radius: 50%;
                        display: grid; place-items: center;
                        font-size: 14px; font-weight: 850; color: #fff; flex-shrink: 0;
                        border: 2px solid #ffffff;
                        box-shadow: 0 3px 10px rgba(0,0,0,0.14);
                    }
                    .ms-header-user-info { display: flex; flex-direction: column; gap: 1px; min-width: 0; }
                    .ms-header-name-row { display: flex; align-items: center; gap: 6px; }
                    .ms-header-user-info h3 {
                        font-size: 14.5px; font-weight: 850; color: #0f172a;
                        margin: 0; line-height: 1.2;
                        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
                    }
                    .ms-header-user-info small {
                        font-size: 11.5px; color: #64748b; font-weight: 600;
                        display: flex; align-items: center; gap: 6px;
                    }
                    .ms-header-tools { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
                    .ms-tool-btn {
                        padding: 6px 12px; border-radius: 9px;
                        background: #ffffff; color: #475569;
                        border: 1.5px solid #cbd5e1; display: inline-flex; align-items: center; gap: 5px;
                        font-size: 12.5px; font-weight: 800; cursor: pointer; text-decoration: none;
                        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
                        transition: all 0.15s ease;
                    }
                    .ms-tool-btn:hover { transform: translateY(-1px); }
                    .ms-tool-btn.btn-call {
                        background: linear-gradient(135deg, #7c3aed, #8b5cf6);
                        color: #ffffff; border-color: #7c3aed;
                        box-shadow: 0 3px 10px rgba(124, 58, 237, 0.35);
                    }
                    .ms-tool-btn.btn-zalo {
                        background: linear-gradient(135deg, #0084ff, #0066ff);
                        color: #ffffff; border-color: #0084ff;
                        box-shadow: 0 3px 10px rgba(0, 104, 255, 0.35);
                    }
                    .ms-tool-btn.btn-info {
                        padding: 6px 9px;
                    }
                    .ms-tool-btn.btn-info.active {
                        background: #eff6ff; color: #0284c7; border-color: #93c5fd;
                    }

                    /* Chat Stream có Texture 3D nhẹ nhàng chuẩn Telegram/Messenger */
                    .ms-stream-body {
                        flex: 1 1 0;
                        min-height: 0;
                        overflow-y: auto;
                        padding: 18px 24px;
                        background-color: #f1f5f9;
                        background-image: radial-gradient(#cbd5e1 1.2px, transparent 1.2px);
                        background-size: 22px 22px;
                        display: flex;
                        flex-direction: column;
                        gap: 12px;
                    }
                    .ms-stream-body::-webkit-scrollbar { width: 5px; }
                    .ms-stream-body::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

                    .ms-date-divider { text-align: center; margin: 4px 0; }
                    .ms-date-divider span {
                        font-size: 11px; color: #475569; font-weight: 750;
                        background: #ffffff; padding: 4px 14px;
                        border-radius: 999px; border: 1.5px solid #e2e8f0;
                        box-shadow: 0 1px 4px rgba(0,0,0,0.04);
                    }

                    .ms-message-row {
                        display: flex; align-items: flex-end; gap: 9px;
                        max-width: 78%;
                    }
                    .ms-message-row.incoming { align-self: flex-start; }
                    .ms-message-row.outgoing { align-self: flex-end; flex-direction: row-reverse; }
                    .ms-mini-avatar {
                        width: 30px; height: 30px; border-radius: 50%;
                        display: grid; place-items: center;
                        font-size: 11px; font-weight: 850; color: #ffffff;
                        margin-bottom: 2px; flex-shrink: 0;
                        border: 2px solid #ffffff;
                        box-shadow: 0 2px 6px rgba(0,0,0,0.15);
                    }
                    .ms-bubble-text {
                        padding: 11px 15px; font-size: 13.5px;
                        line-height: 1.48; word-break: break-word;
                    }
                    .ms-message-row.incoming .ms-bubble-text {
                        background: #ffffff;
                        color: #0f172a;
                        border-radius: 16px 16px 16px 4px;
                        border: 2px solid #e2e8f0;
                        box-shadow: 0 4px 14px rgba(0,0,0,0.06);
                    }
                    .ms-message-row.outgoing .ms-bubble-text {
                        background: linear-gradient(135deg, #0284c7 0%, #2563eb 100%);
                        color: #ffffff;
                        border-radius: 16px 16px 4px 16px;
                        border: none;
                        box-shadow: 0 6px 20px rgba(37, 99, 235, 0.32);
                        font-weight: 500;
                    }
                    .ms-bubble-meta {
                        font-size: 10.5px; color: #64748b; font-weight: 600;
                        margin-top: 4px; padding: 0 4px;
                    }
                    .ms-message-row.outgoing .ms-bubble-meta {
                        text-align: right; color: #0284c7; font-weight: 750;
                    }

                    /* Quick Emoji Bar */
                    .ms-quick-emoji-bar {
                        display: flex; gap: 10px; padding: 6px 18px;
                        align-items: center; background: #ffffff;
                        border-top: 1.5px solid #e2e8f0;
                        flex-shrink: 0;
                    }
                    .ms-quick-emoji-bar .emoji-label {
                        font-size: 10.5px; font-weight: 850; color: #64748b;
                        text-transform: uppercase; letter-spacing: 0.5px;
                    }
                    .ms-emoji-item {
                        font-size: 17px; cursor: pointer;
                        transition: transform 0.15s ease; line-height: 1;
                    }
                    .ms-emoji-item:hover { transform: scale(1.35); }

                    /* Bottom Composer: CỐ ĐỊNH, KHÔNG BỊ TRÀN */
                    .ms-bottom-composer {
                        padding: 10px 18px;
                        border-top: 2px solid #e2e8f0;
                        display: flex; align-items: center; gap: 9px;
                        background: #ffffff;
                        flex-shrink: 0;
                        box-shadow: 0 -3px 12px rgba(0,0,0,0.03);
                    }
                    .ms-composer-icon-btn {
                        width: 36px; height: 36px;
                        border-radius: 10px; border: 1.5px solid #cbd5e1;
                        background: #f8fafc; color: #475569;
                        font-size: 16px; cursor: pointer;
                        display: grid; place-items: center;
                        transition: all 0.15s;
                        flex-shrink: 0;
                    }
                    .ms-composer-icon-btn:hover { background: #e2e8f0; color: #0f172a; border-color: #94a3b8; }

                    .ms-input-pill-wrap {
                        flex: 1; background: #f8fafc;
                        border: 2px solid #cbd5e1;
                        border-radius: 24px; padding: 7px 14px;
                        display: flex; align-items: center; gap: 8px;
                        transition: all 0.2s;
                    }
                    .ms-input-pill-wrap:focus-within {
                        border-color: #0284c7;
                        background: #ffffff;
                        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.12);
                    }
                    .ms-input-pill-wrap input {
                        background: transparent; border: none; outline: none;
                        font-size: 13px; color: #0f172a; width: 100%;
                        font-family: inherit; font-weight: 600;
                    }
                    .ms-input-pill-wrap input::placeholder { color: #94a3b8; font-weight: 500; }
                    .ms-emoji-btn {
                        border: none; background: transparent;
                        font-size: 17px; cursor: pointer; padding: 0;
                        display: grid; place-items: center;
                        transition: transform 0.15s;
                    }
                    .ms-emoji-btn:hover { transform: scale(1.25); }
                    .ms-send-btn {
                        width: 38px; height: 38px; border-radius: 50%;
                        border: none;
                        background: linear-gradient(135deg, #0284c7 0%, #2563eb 100%);
                        color: #ffffff; font-size: 15px;
                        cursor: pointer; display: grid; place-items: center;
                        box-shadow: 0 4px 14px rgba(37, 99, 235, 0.38);
                        transition: all 0.15s;
                        flex-shrink: 0;
                    }
                    .ms-send-btn:hover { transform: scale(1.08); box-shadow: 0 6px 18px rgba(37, 99, 235, 0.48); }
                    .ms-send-btn:active { transform: translateY(2px); }

                    /* ===== CỘT 3: THÔNG TIN & THAO TÁC (DRAWER) ===== */
                    .ms-info-drawer {
                        background: #f8fafc;
                        overflow-y: auto;
                        padding: 12px;
                        display: flex; flex-direction: column; gap: 10px;
                        min-width: 0;
                        height: 100%;
                        max-height: 100%;
                    }
                    .ms-info-drawer::-webkit-scrollbar { width: 4px; }
                    .ms-info-drawer::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

                    /* Card Khối Từng Section Có Border Đẹp & Tương Phản Xịn */
                    .drawer-section-card {
                        background: #ffffff;
                        border: 2px solid #e2e8f0;
                        border-radius: 14px;
                        padding: 11px;
                        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
                        display: flex;
                        flex-direction: column;
                        gap: 8px;
                        transition: border-color 0.15s;
                    }
                    .drawer-section-card:hover { border-color: #cbd5e1; }

                    .drawer-profile-card {
                        text-align: center; padding: 14px 10px;
                        background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
                        border: 2px solid #e2e8f0;
                        border-radius: 14px;
                        box-shadow: 0 2px 8px rgba(0,0,0,0.03);
                    }
                    .drawer-avatar {
                        width: 52px; height: 52px; border-radius: 50%;
                        margin: 0 auto 8px; display: grid; place-items: center;
                        font-size: 18px; font-weight: 850; color: #fff;
                        border: 3px solid #ffffff;
                        box-shadow: 0 6px 16px rgba(0,0,0,0.14);
                    }
                    .drawer-name {
                        font-size: 14.5px; font-weight: 850; color: #0f172a;
                        margin: 0 0 4px;
                    }
                    .drawer-role-badge {
                        display: inline-block; padding: 3px 12px;
                        border-radius: 999px; font-size: 11px; font-weight: 850;
                    }
                    .drawer-role-badge.badge-guest {
                        background: #f3e8ff; color: #7c3aed; border: 1.5px solid #d8b4fe;
                    }
                    .drawer-role-badge.badge-teacher {
                        background: #ecfdf5; color: #059669; border: 1.5px solid #a7f3d0;
                    }
                    .drawer-role-badge.badge-student {
                        background: #eff6ff; color: #1d4ed8; border: 1.5px solid #93c5fd;
                    }

                    /* Smart Action Box Đa Sắc Màu Nổi Bật */
                    .drawer-guest-box {
                        background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%);
                        border: 2px dashed #c084fc;
                        border-radius: 14px; padding: 11px; text-align: left;
                        box-shadow: 0 3px 12px rgba(192, 132, 252, 0.12);
                    }
                    .drawer-guest-box .title {
                        font-size: 11px; font-weight: 850; color: #7c3aed;
                        display: flex; align-items: center; gap: 5px; margin-bottom: 4px;
                    }
                    .drawer-guest-box .desc {
                        font-size: 11px; color: #64748b; line-height: 1.4; margin: 0 0 8px 0; font-weight: 500;
                    }
                    .btn-create-teacher-from-guest {
                        width: 100%; padding: 8px 10px;
                        background: linear-gradient(135deg, #7c3aed, #9333ea);
                        color: #ffffff; border: none; border-radius: 9px;
                        font-size: 11.5px; font-weight: 850; cursor: pointer;
                        display: flex; align-items: center; justify-content: center; gap: 6px;
                        box-shadow: 0 3px 10px rgba(124, 58, 237, 0.35);
                        transition: all 0.15s;
                    }
                    .btn-create-teacher-from-guest:hover {
                        transform: translateY(-1px);
                        box-shadow: 0 5px 14px rgba(124, 58, 237, 0.45);
                    }

                    .drawer-section-title {
                        font-size: 10.5px; font-weight: 850; color: #475569;
                        text-transform: uppercase; letter-spacing: 0.5px;
                        display: flex; align-items: center; gap: 6px;
                    }
                    .sec-icon {
                        width: 20px; height: 20px; border-radius: 5px;
                        display: inline-grid; place-items: center; font-size: 11px;
                    }
                    .sec-icon-purple { background: #f3e8ff; }
                    .sec-icon-blue   { background: #e0f2fe; }
                    .sec-icon-amber  { background: #fef3c7; }
                    .sec-icon-green  { background: #dcfce7; }

                    .drawer-action-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; }
                    .drawer-btn {
                        padding: 8px 8px; border-radius: 9px;
                        font-size: 11.5px; font-weight: 850; border: none;
                        display: inline-flex; align-items: center;
                        justify-content: center; gap: 5px;
                        cursor: pointer; text-decoration: none;
                        transition: all 0.15s;
                    }
                    .drawer-btn:hover { opacity: 0.95; transform: translateY(-1px); }
                    .drawer-btn-tel {
                        background: linear-gradient(135deg, #7c3aed, #8b5cf6); color: #fff;
                        box-shadow: 0 3px 8px rgba(124, 58, 237, 0.35);
                    }
                    .drawer-btn-zalo {
                        background: linear-gradient(135deg, #0084ff, #0066ff); color: #fff;
                        box-shadow: 0 3px 8px rgba(0, 104, 255, 0.35);
                    }

                    .drawer-info-list { display: flex; flex-direction: column; gap: 5px; font-size: 11.5px; }
                    .drawer-info-row {
                        display: flex; justify-content: space-between;
                        align-items: center; padding: 6px 9px;
                        background: #f8fafc; border-radius: 9px;
                        border: 1.5px solid #eef2f6;
                    }
                    .drawer-info-row .label { color: #64748b; font-weight: 750; display: flex; align-items: center; gap: 4px; }
                    .drawer-info-row .val { color: #0f172a; font-weight: 800; text-align: right; font-size: 11.5px; }

                    .drawer-status-chips { display: flex; gap: 5px; }
                    .drawer-status-chip {
                        flex: 1; padding: 6px 4px; border-radius: 9px;
                        font-size: 11px; font-weight: 800;
                        cursor: pointer; border: 1.5px solid #e2e8f0;
                        transition: all 0.15s; background: #f8fafc; color: #64748b;
                        text-align: center; white-space: nowrap;
                    }
                    .drawer-status-chip:hover { border-color: #cbd5e1; }
                    .drawer-status-chip.active-pending {
                        background: #fef2f2; color: #dc2626; border-color: #fca5a5;
                        box-shadow: 0 2px 8px rgba(220, 38, 38, 0.15);
                    }
                    .drawer-status-chip.active-replied {
                        background: #ecfdf5; color: #059669; border-color: #86efac;
                        box-shadow: 0 2px 8px rgba(5, 150, 105, 0.15);
                    }
                    .drawer-status-chip.active-closed {
                        background: #f1f5f9; color: #475569; border-color: #cbd5e1;
                    }

                    .drawer-canned-list { display: flex; flex-direction: column; gap: 5px; }
                    .drawer-canned-item {
                        padding: 8px 10px; border-radius: 9px;
                        background: #ffffff; border: 1.5px solid #e2e8f0;
                        font-size: 11.5px; color: #334155; line-height: 1.35; font-weight: 600;
                        cursor: pointer; transition: all 0.15s;
                    }
                    .drawer-canned-item:hover {
                        background: #eff6ff; border-color: #93c5fd;
                        color: #1d4ed8; transform: translateX(2px);
                    }
                </style>

                <!-- Hidden file input for photo upload -->
                <input type="file" id="ms-photo-upload" accept="image/*" style="display:none;" onchange="handlePhotoUpload(this)">

                <!-- Chat Workspace: 3 Cột Chuẩn Không Bị Cắt Xén -->
                <div class="ms-desktop-wrap" id="ms-main-wrapper">

                    <!-- ===== CỘT 1: DANH SÁCH HỘI THOẠI ===== -->
                    <div class="ms-sidebar">
                        <div class="ms-sidebar-header">
                            <div class="ms-head-title-row">
                                <h2>💬 Hộp Tin Nhắn</h2>
                                <div class="ms-head-icons">
                                    <button type="button" class="ms-circle-btn" onclick="openBotTeleModal()" title="Cài đặt thông báo Telegram">⚙️</button>
                                </div>
                            </div>

                            <div class="ms-search-pill">
                                <span class="icon">🔍</span>
                                <input type="text" id="chat-search-input" oninput="filterChatConversations(this.value)" placeholder="Tìm theo tên hoặc SĐT...">
                            </div>

                            <div class="ms-filter-tabs">
                                <button type="button" class="ms-filter-chip active" id="chip-filter-all" onclick="filterByStatus('all', this)">Tất cả ({{ $supportMessages->count() }})</button>
                                <button type="button" class="ms-filter-chip" id="chip-filter-pending" onclick="filterByStatus('pending', this)">Chờ ({{ $supportMessages->where('status', 'pending')->count() }})</button>
                                <button type="button" class="ms-filter-chip" id="chip-filter-replied" onclick="filterByStatus('replied', this)">Xong</button>
                            </div>
                        </div>

                        <div class="ms-conv-scroll" id="chat-conversation-list">
                            @php
                                $gradients = [
                                    'linear-gradient(135deg,#0284c7,#0369a1)',
                                    'linear-gradient(135deg,#7c3aed,#a855f7)',
                                    'linear-gradient(135deg,#059669,#10b981)',
                                    'linear-gradient(135deg,#f59e0b,#ef4444)',
                                    'linear-gradient(135deg,#2563eb,#1d4ed8)',
                                ];
                            @endphp

                            @forelse($supportMessages as $index => $msg)
                                @php
                                    $isPending = $msg->status === 'pending';
                                    $gradient = $gradients[$index % count($gradients)];
                                    $initials = mb_strtoupper(mb_substr($msg->name, 0, 2, 'UTF-8'), 'UTF-8');
                                    $timeDiff = $msg->created_at ? $msg->created_at->setTimezone('Asia/Ho_Chi_Minh')->diffForHumans(null, true) : 'Mới';
                                    $userType = $msg->user_type ?? 'guest';
                                    $userTypeLabel = $msg->user_type_label ?? '🌐 Khách Vãng Lai';
                                    $cleanMessage = ($msg->message === 'undefined' || empty(trim($msg->message))) ? 'Khách gửi yêu cầu tư vấn' : $msg->message;
                                @endphp
                                <div class="ms-conv-item {{ $index === 0 ? 'active' : '' }} {{ $isPending ? 'is-unread' : '' }}"
                                     data-id="{{ $msg->id }}"
                                     data-name="{{ $msg->name }}"
                                     data-phone="{{ $msg->phone }}"
                                     data-email="{{ $msg->email }}"
                                     data-message="{{ $cleanMessage }}"
                                     data-admin-reply="{{ $msg->admin_reply }}"
                                     data-replied-at="{{ $msg->replied_at ? \Illuminate\Support\Carbon::parse($msg->replied_at)->setTimezone('Asia/Ho_Chi_Minh')->format('H:i d/m/Y') : '' }}"
                                     data-status="{{ $msg->status }}"
                                     data-initials="{{ $initials }}"
                                     data-gradient="{{ $gradient }}"
                                     data-user-type="{{ $userType }}"
                                     data-user-type-label="{{ $userTypeLabel }}"
                                     data-time="{{ $msg->created_at ? $msg->created_at->setTimezone('Asia/Ho_Chi_Minh')->format('H:i d/m/Y') : '' }}"
                                     onclick="selectChatConversation(this)">

                                    <!-- 1. Cột Avatar -->
                                    <div class="ms-item-avatar-wrap">
                                        <div class="ms-item-avatar" style="background: {{ $gradient }};">
                                            {{ $initials }}
                                        </div>
                                        <span class="ms-online-badge"></span>
                                    </div>

                                    <!-- 2. Cột Thông Tin Giữa (Tên & Tin nhắn) -->
                                    <div class="ms-item-center">
                                        <div class="ms-item-name" title="{{ $msg->name }}">{{ $msg->name }}</div>
                                        <div class="ms-item-snippet" id="snippet-{{ $msg->id }}">
                                            @if($msg->admin_reply)
                                                <span class="snippet-you"><b>Bạn:</b> {{ mb_strimwidth($msg->admin_reply, 0, 18, '...') }}</span>
                                            @else
                                                <span class="snippet-guest">{{ mb_strimwidth($cleanMessage, 0, 20, '...') }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- 3. Cột Meta Cố Định Phải (Role Badge thẳng tắp + Chấm đỏ nhấp nháy phát sáng) -->
                                    <div class="ms-item-meta-right">
                                        <div class="ms-role-cell">
                                            @if($userType === 'teacher')
                                                <span class="ms-user-tag tag-teacher">👨‍🏫 GV</span>
                                            @elseif($userType === 'student')
                                                <span class="ms-user-tag tag-student">🎓 HS</span>
                                            @else
                                                <span class="ms-user-tag tag-guest">🌐 Khách</span>
                                            @endif
                                        </div>
                                        <div class="ms-status-cell">
                                            <span class="ms-pending-pulse-badge {{ $isPending ? '' : 'is-hidden' }}" id="unread-dot-{{ $msg->id }}" title="Chưa phản hồi">
                                                <span class="pulse-ring"></span>
                                                <span class="pulse-core"></span>
                                                <span>Chờ</span>
                                            </span>
                                            <span class="ms-replied-tag {{ $isPending ? 'is-hidden' : '' }}" id="replied-tag-{{ $msg->id }}">
                                                {{ $timeDiff }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div style="text-align:center; padding:40px 16px; color:#94a3b8; font-size:13px;">
                                    Chưa có cuộc trò chuyện nào.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- ===== CỘT 2: KHUNG CHAT CHÍNH ===== -->
                    <div class="ms-chat-main">
                        @if($supportMessages->isNotEmpty())
                            @php
                                $activeMsg = $supportMessages->first();
                                $activeGradient = $gradients[0];
                                $activeInitials = mb_strtoupper(mb_substr($activeMsg->name, 0, 2, 'UTF-8'), 'UTF-8');
                                $activeUserType = $activeMsg->user_type ?? 'guest';
                                $activeUserTypeLabel = $activeMsg->user_type_label ?? '🌐 Khách Vãng Lai';
                                $msgText = ($activeMsg->message === 'undefined' || empty(trim($activeMsg->message))) ? 'Dạ em chào Admin, em cần hỗ trợ tư vấn về tài khoản và gói luyện thi ạ!' : $activeMsg->message;
                                $rawLines = explode("\n", (string) $msgText);
                                $activeLines = array_values(array_filter(array_map('trim', $rawLines), function($l) {
                                    return $l !== '' && $l !== 'undefined';
                                }));
                                if (empty($activeLines)) $activeLines = [$msgText];
                            @endphp

                            <!-- Header -->
                            <div class="ms-chat-header">
                                <div class="ms-header-user">
                                    <div id="chat-detail-avatar" class="ms-header-avatar" style="background: {{ $activeGradient }};">{{ $activeInitials }}</div>
                                    <div class="ms-header-user-info">
                                        <div class="ms-header-name-row">
                                            <h3 id="chat-detail-name">{{ $activeMsg->name }}</h3>
                                            <span id="chat-detail-user-tag" class="ms-user-tag {{ $activeUserType === 'teacher' ? 'tag-teacher' : ($activeUserType === 'student' ? 'tag-student' : 'tag-guest') }}">
                                                {{ $activeUserTypeLabel }}
                                            </span>
                                        </div>
                                        <small>
                                            <span style="color:#10b981; font-weight:800;">● Online</span>
                                            <span>·</span>
                                            <span id="chat-detail-phone">📞 {{ $activeMsg->phone ?: 'Chưa có SĐT' }}</span>
                                        </small>
                                    </div>
                                </div>

                                <div class="ms-header-tools">
                                    <a id="btn-call-phone" href="tel:{{ $activeMsg->phone }}" class="ms-tool-btn btn-call" title="Gọi điện thoại">📞</a>
                                    <a id="btn-zalo" href="https://zalo.me/{{ preg_replace('/[^0-9]/', '', $activeMsg->phone) }}" target="_blank" class="ms-tool-btn btn-zalo" title="Nhắn Zalo">Zalo</a>
                                    <button type="button" class="ms-tool-btn btn-info active" id="btn-toggle-drawer" onclick="toggleChatDrawer()" title="Thông tin người gửi">ℹ️</button>
                                </div>
                            </div>

                            <!-- Messages Body: Cuộn Mượt Mà & Không Bao Giờ Tràn -->
                            <div class="ms-stream-body" id="chat-conversation-body">
                                <div class="ms-date-divider" id="chat-detail-time-stamp">
                                    <span>{{ $activeMsg->created_at ? $activeMsg->created_at->setTimezone('Asia/Ho_Chi_Minh')->format('H:i, d/m/Y') : 'Hôm nay' }}</span>
                                </div>

                                <!-- Dynamic Incoming Message Bubbles Container -->
                                <div id="chat-incoming-bubbles-wrap" style="display:flex; flex-direction:column; gap:6px;">
                                    @foreach($activeLines as $lIdx => $line)
                                        <div class="ms-message-row incoming">
                                            @if($lIdx === count($activeLines) - 1)
                                                <div id="chat-bubble-avatar" class="ms-mini-avatar" style="background: {{ $activeGradient }};">{{ $activeInitials }}</div>
                                            @else
                                                <div style="width: 28px; height: 28px; flex-shrink: 0;"></div>
                                            @endif
                                            <div>
                                                <div class="ms-bubble-text">{{ $line }}</div>
                                                @if($lIdx === count($activeLines) - 1)
                                                    <div class="ms-bubble-meta">📩 Khách gửi · Live Chat</div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <!-- Outgoing Admin Reply -->
                                <div id="chat-admin-reply-container" style="{{ $activeMsg->admin_reply ? 'display:flex;' : 'display:none;' }}" class="ms-message-row outgoing">
                                    <div>
                                        <div class="ms-bubble-text" id="chat-admin-reply-text">{{ $activeMsg->admin_reply }}</div>
                                        <div class="ms-bubble-meta" id="chat-admin-reply-meta">
                                            ✓✓ Đã phản hồi {{ $activeMsg->replied_at ? \Illuminate\Support\Carbon::parse($activeMsg->replied_at)->format('H:i d/m/Y') : '' }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Emoji Bar -->
                            <div class="ms-quick-emoji-bar">
                                <span class="emoji-label">Nhanh:</span>
                                <span class="ms-emoji-item" onclick="insertEmojiToComposer('❤️')" title="Tim">❤️</span>
                                <span class="ms-emoji-item" onclick="insertEmojiToComposer('👍')" title="Thích">👍</span>
                                <span class="ms-emoji-item" onclick="insertEmojiToComposer('😊')" title="Cười">😊</span>
                                <span class="ms-emoji-item" onclick="insertEmojiToComposer('🎉')" title="Chúc mừng">🎉</span>
                                <span class="ms-emoji-item" onclick="insertEmojiToComposer('💡')" title="Gợi ý">💡</span>
                            </div>

                            <!-- Composer: GHIM ĐÁY 100% TRONG TẦM MẮT -->
                            <div class="ms-bottom-composer">
                                <button type="button" class="ms-composer-icon-btn upload-btn" onclick="document.getElementById('ms-photo-upload').click()" title="Gửi ảnh">🖼️</button>

                                <div class="ms-input-pill-wrap">
                                    <input type="text" id="ms-admin-reply-input"
                                           placeholder="Nhập tin nhắn phản hồi tới khách (nhấn Enter để gửi)..."
                                           oninput="toggleSendOrThumbsUp(this.value)"
                                           onkeydown="if(event.key==='Enter') handleAdminSendReply()">
                                    <button type="button" class="ms-emoji-btn" onclick="insertEmojiToComposer('😊')" title="Emoji">😊</button>
                                </div>

                                <button type="button" id="ms-send-action-btn" class="ms-send-btn" onclick="handleSendActionClick()" title="Gửi tin nhắn">
                                    ➤
                                </button>
                            </div>
                        @else
                            <div style="flex:1; display:grid; place-items:center; text-align:center; padding:40px; color:#6b7280;">
                                <div>
                                    <div style="font-size:48px; margin-bottom:12px;">💬</div>
                                    <h3 style="font-size:16px; color:#0f172a; font-weight:800;">Chưa Có Tin Nhắn</h3>
                                    <p style="font-size:12.5px; color:#64748b; max-width:280px; margin:0 auto;">Khi khách hoặc giáo viên gửi câu hỏi từ website, tin nhắn sẽ hiển thị tức thì tại đây.</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- ===== CỘT 3: THÔNG TIN & THAO TÁC (DRAWER) ===== -->
                    @if($supportMessages->isNotEmpty())
                    <div class="ms-info-drawer" id="ms-info-drawer">

                        <!-- Profile -->
                        <div class="drawer-profile-card">
                            <div class="drawer-avatar" id="drawer-avatar" style="background: {{ $activeGradient }};">{{ $activeInitials }}</div>
                            <h4 class="drawer-name" id="drawer-name">{{ $activeMsg->name }}</h4>
                            <span class="drawer-role-badge {{ $activeUserType === 'teacher' ? 'badge-teacher' : 'badge-guest' }}" id="drawer-role-badge">
                                {{ $activeUserTypeLabel }}
                            </span>
                        </div>

                        <!-- Smart Identifier Action Box (Gợi ý thao tác theo đối tượng) -->
                        <div id="drawer-smart-action-box" class="drawer-guest-box">
                            <div class="title" id="smart-box-title">🌐 KHÁCH VÃNG LAI (CHƯA CÓ TÀI KHOẢN)</div>
                            <p class="desc" id="smart-box-desc">Khách gửi tin từ trang ngoài. Bạn có thể tư vấn gói và bấm nút dưới để tạo nhanh tài khoản Giáo viên.</p>
                            <button type="button" id="btn-quick-create-teacher" class="btn-create-teacher-from-guest" onclick="openCreateTeacherFromCurrentChat()">
                                <span>⚡</span> Tạo Tài Khoản Giáo Viên
                            </button>
                        </div>

                        <!-- Quick Contact -->
                        <div class="drawer-section-card">
                            <div class="drawer-section-title">
                                <span class="sec-icon sec-icon-purple">📞</span>
                                <span>Liên hệ nhanh</span>
                            </div>
                            <div class="drawer-action-grid">
                                <a id="drawer-btn-tel" href="tel:{{ $activeMsg->phone }}" class="drawer-btn drawer-btn-tel">
                                    📞 Gọi Ngay
                                </a>
                                <a id="drawer-btn-zalo" href="https://zalo.me/{{ preg_replace('/[^0-9]/', '', $activeMsg->phone) }}" target="_blank" class="drawer-btn drawer-btn-zalo">
                                    💬 Mở Zalo
                                </a>
                            </div>
                        </div>

                        <!-- Contact Info -->
                        <div class="drawer-section-card">
                            <div class="drawer-section-title">
                                <span class="sec-icon sec-icon-blue">📋</span>
                                <span>Thông tin liên hệ</span>
                            </div>
                            <div class="drawer-info-list">
                                <div class="drawer-info-row">
                                    <span class="label">📱 SĐT</span>
                                    <span class="val" id="drawer-phone">{{ $activeMsg->phone ?: 'Chưa có' }}</span>
                                </div>
                                <div class="drawer-info-row">
                                    <span class="label">📧 Email</span>
                                    <span class="val" id="drawer-email">{{ $activeMsg->email ?: 'N/A' }}</span>
                                </div>
                                <div class="drawer-info-row">
                                    <span class="label">🕐 Gửi lúc</span>
                                    <span class="val" id="drawer-time">{{ $activeMsg->created_at ? $activeMsg->created_at->setTimezone('Asia/Ho_Chi_Minh')->format('H:i d/m/Y') : '' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Status Chips -->
                        <div class="drawer-section-card">
                            <div class="drawer-section-title">
                                <span class="sec-icon sec-icon-amber">🏷️</span>
                                <span>Trạng thái xử lý</span>
                            </div>
                            <div class="drawer-status-chips">
                                <button type="button" class="drawer-status-chip {{ $activeMsg->status === 'pending' ? 'active-pending' : '' }}" id="chip-status-pending" onclick="updateCurrentChatStatus('pending')">
                                    🔴 Chờ
                                </button>
                                <button type="button" class="drawer-status-chip {{ $activeMsg->status === 'replied' ? 'active-replied' : '' }}" id="chip-status-replied" onclick="updateCurrentChatStatus('replied')">
                                    🟢 Xong
                                </button>
                                <button type="button" class="drawer-status-chip {{ $activeMsg->status === 'closed' ? 'active-closed' : '' }}" id="chip-status-closed" onclick="updateCurrentChatStatus('closed')">
                                    ⚪ Đóng
                                </button>
                            </div>
                        </div>

                        <!-- Canned Replies -->
                        <div class="drawer-section-card">
                            <div class="drawer-section-title">
                                <span class="sec-icon sec-icon-green">⚡</span>
                                <span>Trả lời mẫu nhanh</span>
                            </div>
                            <div class="drawer-canned-list">
                                <div class="drawer-canned-item" onclick="insertCannedReply('Dạ em chào Thầy/Cô! Em là chuyên viên hỗ trợ IC3 Quest. Em xin gửi thông tin chi tiết gói luyện thi nhé ạ!')">
                                    💬 Chào hỏi & Hỗ trợ
                                </div>
                                <div class="drawer-canned-item" onclick="insertCannedReply('Dạ hệ thống có Gói Tiêu Chuẩn (100 HS, 990.000đ/90 ngày) và Gói Trường Học đầy đủ cả 3 khối 3, 4, 5 ạ.')">
                                    📦 Báo giá gói bản quyền
                                </div>
                                <div class="drawer-canned-item" onclick="insertCannedReply('Dạ em đã gửi lời mời kết bạn qua Zalo rồi ạ. Thầy/Cô kiểm tra tin nhắn chờ giúp em nhé!')">
                                    📱 Đã kết bạn Zalo
                                </div>
                            </div>
                        </div>

                        <!-- Delete Conversation Button -->
                        <div style="margin-top: 4px; padding-top: 8px; border-top: 1.5px dashed #e2e8f0;">
                            <button type="button" onclick="deleteCurrentChatConversation()" style="width:100%; padding:8px 10px; background:#fef2f2; border:1px solid #fecaca; color:#b91c1c; border-radius:8px; font-size:11.5px; font-weight:800; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px; transition:all 0.15s;" onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fef2f2'">
                                <span>🗑️ Xóa Cuộc Trò Chuyện</span>
                            </button>
                        </div>

                    </div>
                    @endif

                </div>
            </div>
            @endif

        </main>
    </div>

</div>

<!-- ===========================================================================
     🤖 MODAL CẤU HÌNH & KÍCH HOẠT TELEGRAM BOT (@trikun_cdphp_bot)
     =========================================================================== -->
<div id="modal-telegram-config" class="modal-backdrop" style="display:none; align-items:center; justify-content:center; position:fixed; inset:0; background:rgba(15,23,42,0.7); backdrop-filter:blur(8px); z-index:99999; padding:16px;">
    <div class="modal-box" style="width:min(680px, 96vw); max-height:90vh; display:flex; flex-direction:column; background:#ffffff; border-radius:20px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.35); overflow:hidden; border:1px solid #e2e8f0;">
        
        <!-- Header -->
        <div style="padding:18px 24px; background:linear-gradient(135deg, #0084ff 0%, #00c6ff 100%); color:#ffffff; display:flex; align-items:center; justify-content:space-between;">
            <div style="display:flex; align-items:center; gap:12px;">
                <div style="width:42px; height:42px; border-radius:12px; background:rgba(255,255,255,0.2); display:grid; place-items:center; font-size:22px; backdrop-filter:blur(4px);">
                    🤖
                </div>
                <div>
                    <h3 style="margin:0; font-size:18px; font-weight:900; letter-spacing:-0.3px; color:#ffffff;">Kích Hoạt Bot Telegram Riêng (@trikun_cdphp_bot)</h3>
                    <p style="margin:2px 0 0; font-size:12.5px; opacity:0.95; color:#f0fdf4;">Nhận thông báo đơn hàng & tin nhắn tư vấn giáo viên theo thời gian thực</p>
                </div>
            </div>
            <button type="button" onclick="closeBotTeleModal()" style="background:rgba(255,255,255,0.2); border:none; color:#ffffff; width:32px; height:32px; border-radius:50%; cursor:pointer; font-size:16px; font-weight:700; display:grid; place-items:center;">✕</button>
        </div>

        <!-- Body -->
        <div style="padding:22px 24px; overflow-y:auto; flex:1; display:flex; flex-direction:column; gap:18px;">
            
            <!-- Hướng dẫn 3 bước trực quan thân thiện -->
            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; padding:16px;">
                <div style="font-size:13.5px; font-weight:800; color:#0f172a; margin-bottom:10px; display:flex; align-items:center; gap:6px;">
                    <span>👉</span> <span>Cách kích hoạt để Bot hiện lên trong Telegram của bạn:</span>
                </div>
                
                <div style="display:flex; flex-direction:column; gap:10px; font-size:13px; color:#334155;">
                    <div style="display:flex; gap:10px; align-items:flex-start;">
                        <span style="background:#0084ff; color:#ffffff; font-weight:900; font-size:11px; width:20px; height:20px; border-radius:50%; display:grid; place-items:center; flex-shrink:0; margin-top:1px;">1</span>
                        <div>
                            <b>Bấm vào liên kết bot:</b> Mở Telegram và bấm <a href="https://t.me/trikun_cdphp_bot" target="_blank" style="color:#0084ff; font-weight:800; text-decoration:underline;">t.me/trikun_cdphp_bot</a> rồi bấm nút <b>START (BẮT ĐẦU)</b>.
                            <div style="margin-top:4px;">
                                <a href="https://t.me/trikun_cdphp_bot" target="_blank" style="display:inline-flex; align-items:center; gap:6px; background:#0084ff; color:#ffffff; padding:5px 14px; border-radius:8px; font-size:12px; font-weight:800; text-decoration:none; margin-top:2px;">
                                    <span>🚀</span> Mở Bot trên Telegram & Nhấn START
                                </a>
                            </div>
                        </div>
                    </div>

                    <div style="display:flex; gap:10px; align-items:flex-start;">
                        <span style="background:#0084ff; color:#ffffff; font-weight:900; font-size:11px; width:20px; height:20px; border-radius:50%; display:grid; place-items:center; flex-shrink:0; margin-top:1px;">2</span>
                        <div>
                            <b>Lấy mã Bot Token:</b> Trong tin nhắn BotFather gửi cho bạn trên Telegram, bạn chỉ cần <b>chạm 1 lần vào dòng mã</b> (Telegram sẽ tự động copy).
                        </div>
                    </div>

                    <div style="display:flex; gap:10px; align-items:flex-start;">
                        <span style="background:#0084ff; color:#ffffff; font-weight:900; font-size:11px; width:20px; height:20px; border-radius:50%; display:grid; place-items:center; flex-shrink:0; margin-top:1px;">3</span>
                        <div>
                            <b>Dán mã Token vào ô bên dưới:</b> Sau đó bấm <b>"Kiểm Tra & Lưu Cấu Hình"</b> là xong!
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Fields -->
            <div style="display:flex; flex-direction:column; gap:14px;">
                <div>
                    <label style="display:block; font-size:12.5px; font-weight:800; color:#334155; margin-bottom:6px;">
                        🔑 Mã Bot Token (Lấy từ BotFather) <span style="color:#ef4444;">*</span>
                    </label>
                    <div style="display:flex; gap:8px;">
                        <input type="text" id="tele-config-token" value="{{ config('services.telegram.bot_token') }}" placeholder="Ví dụ: 8567786883:AAEN..." style="flex:1; padding:10px 14px; border:1px solid #cbd5e1; border-radius:10px; font-size:13.5px; font-family:monospace; background:#ffffff; outline:none; transition:border 0.2s;" onfocus="this.style.borderColor='#0084ff'" onblur="this.style.borderColor='#cbd5e1'">
                        <button type="button" onclick="pasteTokenFromClipboard()" style="padding:0 14px; background:#f1f5f9; border:1px solid #cbd5e1; border-radius:10px; font-size:12.5px; font-weight:700; color:#475569; cursor:pointer; white-space:nowrap;" title="Dán mã từ bộ nhớ tạm">📋 Dán</button>
                    </div>
                </div>

                <div>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                        <label style="font-size:12.5px; font-weight:800; color:#334155;">
                            👤 Chat ID Quản Trị Viên (Tài khoản nhận thông báo)
                        </label>
                        <button type="button" id="btn-tele-detect-id" onclick="detectTelegramChatIdAction()" style="padding:4px 10px; background:#eff6ff; border:1px solid #bfdbfe; color:#1d4ed8; border-radius:8px; font-size:12px; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:4px; transition:all 0.2s;" title="Tự động phát hiện Chat ID khi bạn gửi tin nhắn cho bot">
                            <span>⚡</span> Tự Động Tìm Chat ID
                        </button>
                    </div>
                    <div style="display:flex; gap:8px;">
                        <input type="text" id="tele-config-chat-id" value="{{ config('services.telegram.admin_chat_id', '8732001731') }}" placeholder="8732001731" style="flex:1; padding:10px 14px; border:1px solid #cbd5e1; border-radius:10px; font-size:13.5px; font-family:monospace; background:#ffffff; outline:none; transition:border 0.2s;" onfocus="this.style.borderColor='#0084ff'" onblur="this.style.borderColor='#cbd5e1'">
                    </div>
                    <div style="margin-top:6px; font-size:12px; color:#64748b; line-height:1.5;">
                        💡 <b>Cách lấy Chat ID:</b> Bấm <a href="https://t.me/trikun_cdphp_bot" target="_blank" style="color:#0084ff; font-weight:700;">vào đây</a> gửi tin nhắn <code>/start</code> hoặc <code>alo</code> cho Bot, rồi bấm nút <b>"⚡ Tự Động Tìm Chat ID"</b>. Hoặc bạn có thể xem ID tại bot <a href="https://t.me/userinfobot" target="_blank" style="color:#0084ff; font-weight:700;">@userinfobot</a>.
                    </div>
                </div>
            </div>

            <!-- Trạng thái kết nối Live -->
            <div id="tele-test-result-box" style="display:none; padding:12px 16px; border-radius:10px; font-size:13px; font-weight:600;"></div>

        </div>

        <!-- Footer Actions -->
        <div style="padding:14px 24px; background:#f8fafc; border-top:1px solid #e2e8f0; display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap;">
            <div style="display:flex; gap:8px;">
                <button type="button" id="btn-tele-test-conn" onclick="testTelegramConnectionAction()" style="padding:9px 16px; background:#f1f5f9; color:#0f172a; border:1px solid #cbd5e1; border-radius:10px; font-size:13px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                    <span>🔍</span> Kiểm Tra Kết Nối
                </button>
                <button type="button" id="btn-tele-send-test" onclick="sendTelegramTestAlertAction()" style="padding:9px 16px; background:#e0f2fe; color:#0369a1; border:1px solid #bae6fd; border-radius:10px; font-size:13px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                    <span>🚀</span> Gửi Tin Thử Nghiệm
                </button>
            </div>

            <div style="display:flex; gap:8px;">
                <button type="button" onclick="closeBotTeleModal()" style="padding:9px 16px; background:#ffffff; color:#64748b; border:1px solid #cbd5e1; border-radius:10px; font-size:13px; font-weight:700; cursor:pointer;">
                    Đóng
                </button>
                <button type="button" id="btn-tele-save-conf" onclick="saveTelegramSettingsAction()" style="padding:9px 20px; background:linear-gradient(135deg, #0084ff, #0073e6); color:#ffffff; border:none; border-radius:10px; font-size:13px; font-weight:800; cursor:pointer; box-shadow:0 4px 12px rgba(0,132,255,0.35);">
                    💾 Lưu Cấu Hình
                </button>
            </div>
        </div>

    </div>
</div>

<!-- ===========================================================================
     👤 MODAL THÊM TÀI KHOẢN NGƯỜI DÙNG MỚI
     =========================================================================== -->
<div id="create-user-modal" class="modal-backdrop">
    <div class="modal-box" style="width: min(600px, 100%);">
        <div class="modal-header">
            <h3><span>👤</span> {{ $isTeacher ? 'Thêm học sinh mới' : 'Thêm tài khoản người dùng mới' }}</h3>
            <button type="button" class="modal-close-btn" onclick="closeCreateUserModal()">✕</button>
        </div>
        <form id="create-user-form" method="post" action="{{ route('admin.users.store') }}" onsubmit="handleAjaxUserForm(event, this)">
            @csrf
            <input type="hidden" name="created_by" id="create-user-created-by" value="">
            <div class="modal-body" style="max-height: 75vh; overflow-y: auto;">
                <div class="form-grid-2">
                    <div class="form-group">
                        <label><span class="label-title">👤 Họ và tên <span class="req">*</span></span></label>
                        <input name="name" class="form-control" required placeholder="Ví dụ: Nguyễn Văn An">
                        <small class="modal-field-hint">Họ tên đầy đủ</small>
                    </div>
                    <div class="form-group">
                        <label><span class="label-title">🛡️ Vai trò tài khoản <span class="req">*</span></span></label>
                        <select name="role" id="select-user-role" class="form-control" onchange="toggleStudentClassSelect(this.value)" required style="font-weight:800;">
                            <option value="student">👨‍🎓 Học sinh</option>
                            @if(! $isTeacher)
                            <option value="teacher">👩‍🏫 Giáo viên</option>
                            <option value="admin">👑 Quản trị viên</option>
                            @endif
                        </select>
                        <small class="modal-field-hint">Phân quyền chức năng trong hệ thống</small>
                    </div>
                    <div class="form-group">
                        <label>
                            <span class="label-title">📧 Email đăng nhập</span>
                        </label>
                        <input name="email" type="email" class="form-control" placeholder="user@ic3.test">
                        <small class="modal-field-hint">Bắt buộc đối với Giáo viên & Admin (Học sinh có thể để trống)</small>
                    </div>
                    <div class="form-group" id="group-student-code">
                        <label>
                            <span class="label-title">🏷️ Mã học sinh (Student Code)</span>
                        </label>
                        <input name="student_code" id="input-student-code" class="form-control" placeholder="Ví dụ: HS004, 2026A102...">
                        <small class="modal-field-hint">Cho phép học sinh đăng nhập nhanh bằng mã</small>
                    </div>
                    <div class="form-group">
                        <label><span class="label-title">⚡ Trạng thái tài khoản <span class="req">*</span></span></label>
                        <select name="status" id="create-user-status" class="form-control" style="font-weight:800;">
                            <option value="active" selected>🟢 Đang hoạt động (Active)</option>
                            <option value="suspended">🔒 Tạm khóa (Suspended)</option>
                        </select>
                        <small class="modal-field-hint">Trạng thái quyền truy cập</small>
                    </div>
                    <div class="form-group">
                        <label><span class="label-title">🔑 Mật khẩu khởi tạo <span class="req">*</span></span></label>
                        <input name="password" type="password" class="form-control" value="123456" required placeholder="123456">
                        <small class="modal-field-hint">Mật khẩu ban đầu (mặc định: 123456)</small>
                    </div>

                    @if(! $isTeacher)
                    <!-- Ô chọn Giáo viên phụ trách (Chỉ hiển thị khi Admin tạo Học sinh) -->
                    <div class="form-group" id="group-select-teacher" style="grid-column: 1 / -1; background: #eff6ff; border: 1.5px solid #bfdbfe; border-radius: 10px; padding: 12px 14px;">
                        <label style="margin-bottom: 5px;">
                            <span class="label-title" style="color: #1d4ed8; font-weight: 800;">👩‍🏫 Giáo viên phụ trách:</span>
                        </label>
                        <select name="created_by" id="create-user-teacher-select" class="form-control" style="font-weight:700; background:#fff;">
                            <option value="">-- Học sinh tự do (Không gán Giáo viên nào) --</option>
                            @foreach($teachers as $tc)
                                @php
                                    $tcUsed = $tc->students_count ?? $tc->students()->count();
                                    $tcMax = $tc->max_students ?: '∞';
                                @endphp
                                <option value="{{ $tc->id }}">
                                    👩‍🏫 {{ $tc->name }} (Đang quản lý: {{ $tcUsed }}/{{ $tcMax }} HS · {{ $tc->email }})
                                </option>
                            @endforeach
                        </select>
                        <small class="modal-field-hint" style="color: #2563eb; margin-top: 4px;">
                            Chọn Giáo viên để học sinh này xuất hiện trong danh sách lớp của Giáo viên đó.
                        </small>
                    </div>
                    @endif

                    <!-- Ô chọn Khối cấp quyền cho học sinh -->
                    <div id="student-levels-box" class="form-level-box" style="grid-column: 1 / -1; margin-top: 4px; padding: 14px 16px; background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 12px;">
                        <b style="font-size: 12.5px; color: #1e293b; display: block; margin-bottom: 6px;">🔑 Cấp quyền mở khóa Khối học (Level Access):</b>
                        <small class="modal-field-hint" style="margin-bottom: 10px;">Chọn các khối lớp mà học sinh này được phép vào luyện thi</small>
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
                            @foreach($teacherLevels as $lvl)
                                <label class="chip-label" style="justify-content: center; padding: 9px 12px; border-radius: 8px; background: #ffffff; width: 100%; box-sizing: border-box;">
                                    <input type="checkbox" name="level_ids[]" value="{{ $lvl->id }}" {{ $loop->first ? 'checked' : '' }}>
                                    <span style="color:#0f172a; font-weight:800; font-size:12.5px;">Khối {{ $lvl->grade }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeCreateUserModal()">Hủy</button>
                <button type="submit" class="btn-primary">✓ Lưu & Tạo Tài Khoản</button>
            </div>
        </form>
    </div>
</div>

<!-- ===========================================================================
     ✏️ MODAL CHỈNH SỬA TÀI KHOẢN & ĐỔI MẬT KHẨU
     =========================================================================== -->
<div id="edit-user-modal" class="modal-backdrop">
    <div class="modal-box" style="width: min(600px, 100%);">
        <div class="modal-header">
            <h3 id="edit-user-modal-title"><span>✏️</span> Chỉnh sửa thông tin & Đổi mật khẩu</h3>
            <button type="button" class="modal-close-btn" onclick="closeEditUserModal()">✕</button>
        </div>
        <form id="edit-user-form" method="post" action="" onsubmit="handleAjaxUserForm(event, this)">
            @csrf
            @method('put')
            <div class="modal-body" style="max-height: 75vh; overflow-y: auto;">
                <div class="form-grid-2">
                    <div class="form-group">
                        <label><span class="label-title">👤 Họ và tên <span class="req">*</span></span></label>
                        <input name="name" id="edit-user-name" class="form-control" required placeholder="Ví dụ: Nguyễn Văn An">
                        <small class="modal-field-hint">Tên đầy đủ của người dùng</small>
                    </div>
                    <div class="form-group" id="edit-group-role">
                        <label><span class="label-title">🛡️ Vai trò tài khoản <span class="req">*</span></span></label>
                        <select name="role" id="edit-user-role" class="form-control" onchange="toggleEditStudentClassSelect(this.value)" required style="font-weight:800;">
                            <option value="student">👨‍🎓 Học sinh</option>
                            <option value="teacher">👩‍🏫 Giáo viên</option>
                            @if(! $isTeacher)
                            <option value="admin">👑 Quản trị viên</option>
                            @endif
                        </select>
                        <small class="modal-field-hint" id="edit-user-role-hint">Phân quyền chức năng</small>
                    </div>
                    <div class="form-group">
                        <label>
                            <span class="label-title">📧 Email đăng nhập <span class="req">*</span></span>
                        </label>
                        <input name="email" id="edit-user-email" type="email" class="form-control" required placeholder="user@ic3.test">
                        <small class="modal-field-hint">Địa chỉ email</small>
                    </div>
                    <div class="form-group" id="edit-group-student-code">
                        <label>
                            <span class="label-title">🏷️ Mã học sinh (Student Code)</span>
                        </label>
                        <input name="student_code" id="edit-user-student-code" class="form-control" placeholder="Ví dụ: HS004">
                        <small class="modal-field-hint">Mã đăng nhập nhanh của học sinh</small>
                    </div>
                    <div class="form-group" id="edit-group-status" style="grid-column: 1 / -1;">
                        <label><span class="label-title">⚡ Trạng thái hoạt động <span class="req">*</span></span></label>
                        <select name="status" id="edit-user-status" class="form-control" style="font-weight:800;">
                            <option value="active">🟢 Đang hoạt động / Được phép học (Active)</option>
                            <option value="suspended">🔒 Tạm khóa quyền truy cập (Suspended)</option>
                        </select>
                        <small class="modal-field-hint" id="edit-user-status-hint">Khi bị khóa, học sinh sẽ không thể đăng nhập hoặc làm bài thi</small>
                    </div>
                    <div class="form-group" style="grid-column: 1 / -1; background:#fffbeb; border:1.5px dashed #fde68a; border-radius:10px; padding:12px 14px;">
                        <label style="margin-bottom:4px;">
                            <span class="label-title" style="color:#b45309;">🔑 Đổi mật khẩu mới</span>
                        </label>
                        <input name="password" id="edit-user-password" type="password" class="form-control" placeholder="Nhập mật khẩu mới (Để trống nếu muốn giữ nguyên mật khẩu cũ)..." autocomplete="new-password">
                        <small class="modal-field-hint" style="color:#92400e;">Chỉ nhập khi cần thay đổi mật khẩu đăng nhập</small>
                    </div>

                    @if(! $isTeacher)
                    <!-- Ô chọn/đổi Giáo viên phụ trách (Chỉ hiển thị khi Admin sửa Học sinh) -->
                    <div class="form-group" id="edit-group-select-teacher" style="grid-column: 1 / -1; background: #eff6ff; border: 1.5px solid #bfdbfe; border-radius: 10px; padding: 12px 14px;">
                        <label style="margin-bottom: 5px;">
                            <span class="label-title" style="color: #1d4ed8; font-weight: 800;">👩‍🏫 Giáo viên phụ trách:</span>
                        </label>
                        <select name="created_by" id="edit-user-teacher-select" class="form-control" style="font-weight:700; background:#fff;">
                            <option value="">-- Học sinh tự do (Không gán Giáo viên nào) --</option>
                            @foreach($teachers as $tc)
                                @php
                                    $tcUsed = $tc->students_count ?? $tc->students()->count();
                                    $tcMax = $tc->max_students ?: '∞';
                                @endphp
                                <option value="{{ $tc->id }}">
                                    👩‍🏫 {{ $tc->name }} (Đang quản lý: {{ $tcUsed }}/{{ $tcMax }} HS · {{ $tc->email }})
                                </option>
                            @endforeach
                        </select>
                        <small class="modal-field-hint" style="color: #2563eb; margin-top: 4px;">
                            Chuyển học sinh sang Giáo viên khác hoặc để tự do.
                        </small>
                    </div>
                    @endif

                    <!-- Ô chọn Khối cấp quyền cho học sinh -->
                    <div id="edit-student-levels-box" class="form-level-box" style="grid-column: 1 / -1; margin-top: 4px; padding: 14px 16px; background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 12px;">
                        <b style="font-size: 12.5px; color: #1e293b; display: block; margin-bottom: 6px;">🔑 Cấp quyền truy cập Khối học (Level Access):</b>
                        <small class="modal-field-hint" style="margin-bottom: 10px;">Đánh dấu vào các khối học sinh này được phép truy cập</small>
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
                            @foreach($teacherLevels as $lvl)
                                <label class="chip-label" style="justify-content: center; padding: 9px 12px; border-radius: 8px; background: #ffffff; width: 100%; box-sizing: border-box;">
                                    <input type="checkbox" name="level_ids[]" value="{{ $lvl->id }}" class="edit-level-chk">
                                    <span style="color:#0f172a; font-weight:800; font-size:12.5px;">Khối {{ $lvl->grade }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeEditUserModal()">Hủy</button>
                <button type="submit" class="btn-primary">✓ Lưu Thay Đổi</button>
            </div>
        </form>
    </div>
</div>

<!-- ===========================================================================
     🔑 MODAL THÊM KHỐI LỚP / CẤP ĐỘ MỚI (LEVEL CREATION)
     =========================================================================== -->
<div id="create-level-modal" class="modal-backdrop">
    <div class="modal-box" style="width: min(540px, 100%);">
        <div class="modal-header">
            <h3><span>🔑</span> Thêm Khối lớp / Cấp độ mới</h3>
            <button type="button" class="modal-close-btn" onclick="closeCreateLevelModal()">✕</button>
        </div>
        <form method="post" action="{{ route('admin.levels.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-grid-2">
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label><span class="label-title">🗂️ Thuộc Chương trình đào tạo <span class="req">*</span></span></label>
                        <select name="program_id" class="form-control" required style="font-weight:750;">
                            @foreach($programs as $prog)
                                <option value="{{ $prog->id }}">{{ $prog->name }}</option>
                            @endforeach
                        </select>
                        <small class="modal-field-hint">Chương trình cấp cha quản lý khối lớp này</small>
                    </div>
                    <div class="form-group">
                        <label><span class="label-title">🎯 Chọn Khối lớp (Grade) <span class="req">*</span></span></label>
                        <select name="grade" id="create-level-grade" class="form-control" onchange="autoFillLevelName(this.value)" required style="font-weight:800;">
                            @for($g = 1; $g <= 12; $g++)
                                <option value="{{ $g }}" {{ $g == 1 ? 'selected' : '' }}>Khối {{ $g }}</option>
                            @endfor
                        </select>
                        <small class="modal-field-hint">Cấp lớp tương ứng trong trường học</small>
                    </div>
                    <div class="form-group">
                        <label><span class="label-title">🔢 Thứ tự hiển thị</span></label>
                        <input name="position" type="number" min="0" class="form-control" value="{{ $levels->count() + 1 }}" placeholder="1, 2, 3...">
                        <small class="modal-field-hint">Thứ tự ưu tiên sắp xếp</small>
                    </div>
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label><span class="label-title">🏷️ Tên Khối lớp / Cấp độ <span class="req">*</span></span></label>
                        <input name="name" id="create-level-name" class="form-control" value="IC3 GS6 Spark Level 1 — Khối 1" required placeholder="Ví dụ: IC3 GS6 Spark Level 1 — Khối 1">
                        <small class="modal-field-hint">Tên hiển thị trên bản đồ bài học và chứng chỉ của học sinh</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeCreateLevelModal()">Hủy</button>
                <button type="submit" class="btn-primary">✓ Lưu & Tạo Khối Lớp</button>
            </div>
        </form>
    </div>
</div>

<!-- ===========================================================================
     ✏️ MODAL CHỈNH SỬA KHỐI LỚP / CẤP ĐỘ (LEVEL EDIT)
     =========================================================================== -->
<div id="edit-level-modal" class="modal-backdrop">
    <div class="modal-box" style="width: min(540px, 100%);">
        <div class="modal-header">
            <h3><span>✏️</span> Chỉnh sửa Khối lớp / Cấp độ</h3>
            <button type="button" class="modal-close-btn" onclick="closeEditLevelModal()">✕</button>
        </div>
        <form id="edit-level-form" method="post" action="">
            @csrf
            @method('put')
            <div class="modal-body">
                <div class="form-grid-2">
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label><span class="label-title">🗂️ Thuộc Chương trình đào tạo <span class="req">*</span></span></label>
                        <select name="program_id" id="edit-level-program" class="form-control" required style="font-weight:750;">
                            @foreach($programs as $prog)
                                <option value="{{ $prog->id }}">{{ $prog->name }}</option>
                            @endforeach
                        </select>
                        <small class="modal-field-hint">Chương trình cấp cha quản lý khối lớp này</small>
                    </div>
                    <div class="form-group">
                        <label><span class="label-title">🎯 Chọn Khối lớp (Grade) <span class="req">*</span></span></label>
                        <select name="grade" id="edit-level-grade" class="form-control" required style="font-weight:800;">
                            @for($g = 1; $g <= 12; $g++)
                                <option value="{{ $g }}">Khối {{ $g }}</option>
                            @endfor
                        </select>
                        <small class="modal-field-hint">Cấp lớp tương ứng</small>
                    </div>
                    <div class="form-group">
                        <label><span class="label-title">🔢 Thứ tự hiển thị</span></label>
                        <input name="position" id="edit-level-position" type="number" min="0" class="form-control" placeholder="1, 2, 3...">
                        <small class="modal-field-hint">Thứ tự ưu tiên sắp xếp</small>
                    </div>
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label><span class="label-title">🏷️ Tên Khối lớp / Cấp độ <span class="req">*</span></span></label>
                        <input name="name" id="edit-level-name" class="form-control" required placeholder="Ví dụ: IC3 GS6 Spark Level 1 — Khối 1">
                        <small class="modal-field-hint">Tên hiển thị công khai trên giao diện học tập</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeEditLevelModal()">Hủy</button>
                <button type="submit" class="btn-primary">✓ Lưu Thay Đổi</button>
            </div>
        </form>
    </div>
</div>

<!-- ===========================================================================
     🗂 MODAL THÊM CHƯƠNG TRÌNH ĐÀO TẠO MỚI (PROGRAM CREATION)
     =========================================================================== -->
<div id="create-program-modal" class="modal-backdrop">
    <div class="modal-box" style="width: min(520px, 100%);">
        <div class="modal-header">
            <h3><span>🗂</span> Thêm Chương trình đào tạo mới</h3>
            <button type="button" class="modal-close-btn" onclick="closeCreateProgramModal()">✕</button>
        </div>
        <form method="post" action="{{ route('admin.programs.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-group" style="margin-bottom:14px;">
                    <label><span class="label-title">🔤 Tên chương trình đào tạo <span class="req">*</span></span></label>
                    <input name="name" class="form-control" required placeholder="Ví dụ: IC3 GS6 Tiểu học, Tin học MOS 2019...">
                    <small class="modal-field-hint">Tên chương trình hiển thị công khai trên cổng luyện thi và báo cáo</small>
                </div>
                <div class="form-group" style="margin-bottom:14px;">
                    <label><span class="label-title">🔗 Mã định danh / Slug URL (Tùy chọn)</span></label>
                    <input name="slug" class="form-control" placeholder="Tự động tạo nếu để trống (ví dụ: ic3-gs6-primary)">
                    <small class="modal-field-hint">Dùng trên thanh địa chỉ URL. Hệ thống sẽ tự động tạo từ tên nếu để trống.</small>
                </div>
                <div class="form-group" style="margin-bottom:14px;">
                    <label><span class="label-title">🎨 Màu sắc nhận diện (Theme Brand Color)</span></label>
                    <div class="color-picker-group">
                        <input type="color" id="create-program-color-picker" class="color-preview-box" value="#4f46e5" onchange="syncProgramColor(this.value, 'create')">
                        <input name="accent" id="create-program-accent" class="form-control" style="font-family:ui-monospace, monospace; font-weight:800; max-width:130px;" value="#4f46e5" placeholder="#4f46e5" oninput="syncProgramColor(this.value, 'create')">
                        <div class="color-preset-list">
                            <button type="button" class="color-preset-btn" style="background:#4f46e5" onclick="setProgramPreset('#4f46e5', 'create')" title="Xanh Indigo (#4f46e5)"></button>
                            <button type="button" class="color-preset-btn" style="background:#10b981" onclick="setProgramPreset('#10b981', 'create')" title="Xanh Ngọc (#10b981)"></button>
                            <button type="button" class="color-preset-btn" style="background:#f59e0b" onclick="setProgramPreset('#f59e0b', 'create')" title="Vàng Amber (#f59e0b)"></button>
                            <button type="button" class="color-preset-btn" style="background:#8b5cf6" onclick="setProgramPreset('#8b5cf6', 'create')" title="Tím Hoàng gia (#8b5cf6)"></button>
                            <button type="button" class="color-preset-btn" style="background:#f43f5e" onclick="setProgramPreset('#f43f5e', 'create')" title="Hồng Đỏ (#f43f5e)"></button>
                            <button type="button" class="color-preset-btn" style="background:#0284c7" onclick="setProgramPreset('#0284c7', 'create')" title="Xanh Biển (#0284c7)"></button>
                        </div>
                    </div>
                    <small class="modal-field-hint">Bấm vào ô màu hoặc chọn nhanh màu mẫu bên cạnh để làm màu chủ đạo cho chương trình</small>
                </div>
                <div class="form-group">
                    <label><span class="label-title">📝 Mô tả tóm tắt mục tiêu (Tùy chọn)</span></label>
                    <textarea name="description" class="form-control" style="min-height:70px;" placeholder="Ví dụ: Hành trình trang bị kỹ năng tin học và tư duy số cho học sinh tiểu học..."></textarea>
                    <small class="modal-field-hint">Mô tả ngắn gọn về chương trình đào tạo để học sinh dễ nắm bắt</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeCreateProgramModal()">Hủy</button>
                <button type="submit" class="btn-primary">✓ Lưu & Tạo Chương Trình</button>
            </div>
        </form>
    </div>
</div>

<!-- ===========================================================================
     🗂 MODAL CHỈNH SỬA CHƯƠNG TRÌNH ĐÀO TẠO (PROGRAM EDIT)
     =========================================================================== -->
<div id="edit-program-modal" class="modal-backdrop">
    <div class="modal-box" style="width: min(520px, 100%);">
        <div class="modal-header">
            <h3><span>🗂</span> Chỉnh sửa Chương trình đào tạo</h3>
            <button type="button" class="modal-close-btn" onclick="closeEditProgramModal()">✕</button>
        </div>
        <form id="edit-program-form" method="post" action="">
            @csrf
            @method('put')
            <div class="modal-body">
                <div class="form-group" style="margin-bottom:14px;">
                    <label><span class="label-title">🔤 Tên chương trình đào tạo <span class="req">*</span></span></label>
                    <input name="name" id="edit-program-name" class="form-control" required placeholder="Ví dụ: IC3 GS6 Tiểu học">
                    <small class="modal-field-hint">Tên chương trình hiển thị công khai trên cổng luyện thi và báo cáo</small>
                </div>
                <div class="form-group" style="margin-bottom:14px;">
                    <label><span class="label-title">🔗 Mã định danh / Đường dẫn web (Slug URL) <span class="req">*</span></span></label>
                    <input name="slug" id="edit-program-slug" class="form-control" required placeholder="ic3-gs6-primary">
                    <small class="modal-field-hint">Mã định danh tiếng Việt không dấu trên thanh địa chỉ URL (VD: ic3-gs6-primary)</small>
                </div>
                <div class="form-group" style="margin-bottom:14px;">
                    <label><span class="label-title">🎨 Màu sắc nhận diện (Theme Brand Color)</span></label>
                    <div class="color-picker-group">
                        <input type="color" id="edit-program-color-picker" class="color-preview-box" value="#4f46e5" onchange="syncProgramColor(this.value, 'edit')">
                        <input name="accent" id="edit-program-accent" class="form-control" style="font-family:ui-monospace, monospace; font-weight:800; max-width:130px;" value="#4f46e5" placeholder="#4f46e5" oninput="syncProgramColor(this.value, 'edit')">
                        <div class="color-preset-list">
                            <button type="button" class="color-preset-btn" style="background:#4f46e5" onclick="setProgramPreset('#4f46e5', 'edit')" title="Xanh Indigo (#4f46e5)"></button>
                            <button type="button" class="color-preset-btn" style="background:#10b981" onclick="setProgramPreset('#10b981', 'edit')" title="Xanh Ngọc (#10b981)"></button>
                            <button type="button" class="color-preset-btn" style="background:#f59e0b" onclick="setProgramPreset('#f59e0b', 'edit')" title="Vàng Amber (#f59e0b)"></button>
                            <button type="button" class="color-preset-btn" style="background:#8b5cf6" onclick="setProgramPreset('#8b5cf6', 'edit')" title="Tím Hoàng gia (#8b5cf6)"></button>
                            <button type="button" class="color-preset-btn" style="background:#f43f5e" onclick="setProgramPreset('#f43f5e', 'edit')" title="Hồng Đỏ (#f43f5e)"></button>
                            <button type="button" class="color-preset-btn" style="background:#0284c7" onclick="setProgramPreset('#0284c7', 'edit')" title="Xanh Biển (#0284c7)"></button>
                        </div>
                    </div>
                    <small class="modal-field-hint">Bấm vào ô màu hoặc chọn nhanh màu mẫu bên cạnh để làm màu chủ đạo cho chương trình</small>
                </div>
                <div class="form-group">
                    <label><span class="label-title">📝 Mô tả tóm tắt mục tiêu (Tùy chọn)</span></label>
                    <textarea name="description" id="edit-program-desc" class="form-control" style="min-height:70px;" placeholder="Mô tả chương trình đào tạo"></textarea>
                    <small class="modal-field-hint">Mô tả ngắn gọn về chương trình đào tạo để học sinh dễ nắm bắt</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeEditProgramModal()">Hủy</button>
                <button type="submit" class="btn-primary">✓ Lưu Thay Đổi</button>
            </div>
        </form>
    </div>
</div>

<!-- ===========================================================================
     🔑 MODAL CẤP KHỐI HỌC CHO HỌC SINH (Không bao giờ bị tràn/cắt chân bảng)
     =========================================================================== -->
<div id="grant-level-modal" class="modal-backdrop">
    <div class="modal-box">
        <div class="modal-header">
            <h3><span>🔑</span> Cấp quyền Khối học (Level Access)</h3>
            <button type="button" class="modal-close-btn" onclick="closeGrantModal()">✕</button>
        </div>
        <form id="grant-level-form" method="post" action="" onsubmit="handleAjaxUserForm(event, this)">
            @csrf
            @method('put')
            <input type="hidden" name="name" id="modal-user-name">
            <input type="hidden" name="email" id="modal-user-email">
            <input type="hidden" name="student_code" id="modal-user-code">
            <input type="hidden" name="role" id="modal-user-role">
            <input type="hidden" name="classroom_id" id="modal-user-class">

            <div class="modal-body">
                <p style="font-size:13.5px; color:#334155; margin-bottom:14px;">
                    Chọn các Khối lớp học sinh <b id="modal-display-student-name" style="color:#0f172a;"></b> được phép truy cập và làm bài thi luyện:
                </p>

                <div style="display:flex; flex-direction:column; gap:10px;">
                    @foreach($teacherLevels as $lvl)
                        <label class="chip-label" style="padding:10px 14px; border-radius:10px; width:100%; justify-content:flex-start;">
                            <input type="checkbox" name="level_ids[]" value="{{ $lvl->id }}" id="modal-lvl-{{ $lvl->id }}">
                            <div>
                                <b style="color:#0f172a;">Khối {{ $lvl->grade }}</b> — <span>{{ $lvl->name }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeGrantModal()">Hủy</button>
                <button type="submit" class="btn-primary">✓ Lưu Cấp Quyền</button>
            </div>
        </form>
    </div>
</div>

@if(! $isTeacher)
<!-- ===========================================================================
     👑 MODAL CẤP GÓI & QUẢN LÝ GIÁO VIÊN (DÀNH CHO ADMIN)
     =========================================================================== -->
<div id="grant-teacher-modal" class="modal-backdrop">
    <div class="modal-box" style="width: min(580px, 100%);">
        <div class="modal-header">
            <h3><span>👑</span> Cấp Gói & Phân Quyền Giáo Viên</h3>
            <button type="button" class="modal-close-btn" onclick="closeGrantTeacherModal()">✕</button>
        </div>
        <form id="grant-teacher-form" method="post" action="" onsubmit="handleAjaxUserForm(event, this)">
            @csrf
            @method('put')
            <input type="hidden" name="name" id="teacher-modal-name">
            <input type="hidden" name="email" id="teacher-modal-email">
            <input type="hidden" name="role" value="teacher">

            <div class="modal-body" style="max-height: 75vh; overflow-y: auto;">
                <p style="font-size:13.5px; color:#334155; margin-bottom:14px;">
                    Cấu hình gói đăng ký, số lượng học sinh tối đa và phân quyền Khối học cho Giáo viên <b id="display-teacher-name" style="color:#0f172a;"></b>:
                </p>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label><span class="label-title">👥 Sĩ số Học sinh tối đa (Max Students)</span></label>
                        <input name="max_students" id="teacher-modal-max-students" type="number" min="0" class="form-control" placeholder="100">
                        <small class="modal-field-hint">Số lượng học sinh tối đa giáo viên được phép tạo</small>
                    </div>

                    <div class="form-group">
                        <label><span class="label-title">📅 Ngày hết hạn gói (Expires At)</span></label>
                        <input name="expires_at" id="teacher-modal-expires-at" type="date" class="form-control">
                        <small class="modal-field-hint">Hạn sử dụng dịch vụ của tài khoản giáo viên</small>
                    </div>

                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label><span class="label-title">⚡ Trạng thái hoạt động (Status)</span></label>
                        <select name="status" id="teacher-modal-status" class="form-control" style="font-weight:750;">
                            <option value="active">🟢 Đang hoạt động (Active)</option>
                            <option value="suspended">🟡 Tạm dừng (Suspended)</option>
                            <option value="expired">🔴 Hết hạn (Expired)</option>
                        </select>
                    </div>

                    <!-- Danh sách Khối cấp cho Teacher -->
                    <div class="form-level-box" style="grid-column: 1 / -1; margin-top: 4px; padding: 14px 16px; background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 12px;">
                        <b style="font-size: 12.5px; color: #1e293b; display: block; margin-bottom: 6px;">🔑 Khối học được phép sử dụng (Teacher Level Quota):</b>
                        <small class="modal-field-hint" style="margin-bottom: 10px;">Đánh dấu vào các khối mà Giáo viên này được quyền truy cập và cấp cho học sinh của họ</small>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            @foreach($levels as $lvl)
                                <label class="chip-label" style="padding:9px 12px; border-radius:8px; width:100%; justify-content:flex-start;">
                                    <input type="checkbox" name="teacher_level_ids[]" value="{{ $lvl->id }}" class="teacher-lvl-chk" id="teacher-lvl-{{ $lvl->id }}">
                                    <div>
                                        <b style="color:#0f172a;">Khối {{ $lvl->grade }}</b> — <span>{{ $lvl->name }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeGrantTeacherModal()">Hủy</button>
                <button type="submit" class="btn-primary">✓ Lưu Cấu Hình Gói</button>
            </div>
        </form>
    </div>
</div>
@endif

@if(! $isTeacher)
<!-- ===========================================================================
     👥 MODAL XEM DANH SÁCH HỌC SINH CỦA ĐẠI LÝ (TEACHER STUDENTS MODAL)
     =========================================================================== -->
<div id="teacher-students-modal" class="modal-backdrop">
    <div class="modal-box" style="width: min(1000px, 95vw); border-radius: 18px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
        <div class="modal-header" style="padding: 18px 24px; border-bottom: 1.5px solid #f1f5f9;">
            <div>
                <h3 style="display:flex; align-items:center; gap:8px; margin:0; font-size:18px; font-weight:900;">
                    <span>👥</span> Danh Sách Học Sinh — <b id="ts-modal-teacher-name" style="color:var(--brand);"></b>
                </h3>
                <div style="font-size:13px; color:#64748b; margin-top:4px; display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                    <span>📧 <span id="ts-modal-teacher-email"></span></span>
                    <span>·</span>
                    <span>👥 Sĩ số: <b id="ts-modal-quota" style="color:#059669; font-weight:800;"></b></span>
                </div>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeTeacherStudentsModal()">✕</button>
        </div>

        <div class="modal-body" style="padding: 20px 24px; max-height: 72vh; overflow-y: auto; overflow-x: hidden;">
            <!-- Modal Mini Toolbar -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; gap:12px; flex-wrap:wrap;">
                <div class="search-wrap" style="flex:1; max-width:360px;">
                    <span class="search-icon">🔍</span>
                    <input type="text" id="ts-modal-search" class="search-input" style="width:100%; box-sizing:border-box; height:38px;" placeholder="Tìm tên, mã HS, email học sinh..." onkeyup="filterTeacherModalStudents()">
                </div>
                <div style="display:flex; align-items:center; gap:10px;">
                    <span id="ts-modal-count-badge" class="pill-badge pill-grade" style="font-size:12.5px; padding:6px 14px;"></span>
                    <button type="button" class="btn-primary" style="padding:7px 14px; font-size:12.5px; border-radius:8px; display:inline-flex; align-items:center; gap:6px;" onclick="openCreateStudentForTeacherModal()">
                        <span>＋</span> Thêm học sinh
                    </button>
                </div>
            </div>

            <!-- Table Container: No horizontal scroll, perfect vertical scroll -->
            <div style="max-height: 52vh; overflow-y: auto; overflow-x: hidden; border: 1.5px solid #e2e8f0; border-radius: 12px; background: #fff;">
                <table class="modal-roster-table">
                    <colgroup>
                        <col style="width: 4%;">
                        <col style="width: 25%;">
                        <col style="width: 12%;">
                        <col style="width: 17%;">
                        <col style="width: 11%;">
                        <col style="width: 11%;">
                        <col style="width: 20%;">
                    </colgroup>
                    <thead style="position: sticky; top: 0; background: #f8fafc; z-index: 2;">
                        <tr>
                            <th style="text-align:center;">#</th>
                            <th>HỌC SINH</th>
                            <th style="text-align:center;">TRẠNG THÁI</th>
                            <th>KHỐI ĐƯỢC CẤP</th>
                            <th>LƯỢT THI</th>
                            <th>NGÀY TẠO</th>
                            <th style="text-align:right;">THAO TÁC</th>
                        </tr>
                    </thead>
                    <tbody id="ts-modal-tbody">
                        <!-- Rendered by JS -->
                    </tbody>
                </table>
            </div>

            <div id="ts-modal-empty" style="display:none; text-align:center; padding: 48px 20px; color:#64748b;">
                <div style="font-size: 40px; margin-bottom: 8px;">👨‍🎓</div>
                <div style="font-size: 15px; font-weight:800; color:#1e293b;">Giáo viên này chưa có học sinh nào</div>
                <p style="font-size: 13px; margin-top: 4px; color:#64748b;">Bạn có thể bấm "＋ Thêm học sinh" ở trên để tạo ngay học sinh cho giáo viên này.</p>
            </div>
        </div>

        <div class="modal-footer" style="padding: 14px 24px; border-top: 1.5px solid #f1f5f9;">
            <button type="button" class="btn-primary" onclick="closeTeacherStudentsModal()">✓ Đóng Cửa Sổ</button>
        </div>
    </div>
</div>
@endif

<!-- ===========================================================================
     ⚠️ MODAL XÁC NHẬN HÀNH ĐỘNG HIỆN ĐẠI (CUSTOM CONFIRM DIALOG)
     =========================================================================== -->
<div id="confirm-dialog-modal" class="modal-backdrop" style="z-index: 12000;">
    <div class="modal-box" style="width: min(440px, 95vw); border-radius: 20px; text-align: center; padding: 26px; box-shadow: 0 25px 60px -15px rgba(15, 23, 42, 0.4); border: 1.5px solid #e2e8f0;">
        <div id="confirm-dialog-icon-box" style="width: 60px; height: 60px; margin: 0 auto 16px; border-radius: 50%; display: grid; place-items: center; font-size: 28px; background: #fee2e2; color: #dc2626;">
            🗑️
        </div>
        <h3 id="confirm-dialog-title" style="font-size: 17.5px; font-weight: 900; color: #0f172a; margin-bottom: 8px;">Xác nhận xóa</h3>
        <p id="confirm-dialog-message" style="font-size: 13.5px; color: #64748b; line-height: 1.5; margin-bottom: 22px;">
            Bạn có chắc chắn muốn thực hiện hành động này không?
        </p>
        <div style="display: flex; gap: 10px; justify-content: center;">
            <button type="button" class="btn-secondary" style="padding: 10px 20px; font-size: 13.5px; border-radius: 10px;" onclick="closeConfirmDialog()">Hủy bỏ</button>
            <button type="button" id="confirm-dialog-submit-btn" class="btn-primary" style="padding: 10px 22px; font-size: 13.5px; border-radius: 10px; background: linear-gradient(135deg, #ef4444, #dc2626); box-shadow: 0 4px 12px rgba(220, 38, 38, 0.35);">
                ✓ Xác nhận
            </button>
        </div>
    </div>
</div>

<!-- ===========================================================================
     📋 MODAL CHI TIẾT LƯỢT THI HỌC SINH (ATTEMPT DETAIL MODAL)
     =========================================================================== -->
<div id="attempt-detail-modal" class="modal-backdrop">
    <div class="modal-box" style="width: min(560px, 100%);">
        <div class="modal-header">
            <h3><span>📋</span> Chi Tiết Lượt Thi Học Sinh</h3>
            <button type="button" class="modal-close-btn" onclick="closeAttemptDetailModal()">✕</button>
        </div>
        <div class="modal-body" style="padding: 24px;">
            <!-- Header Info -->
            <div style="display:flex; align-items:center; gap:14px; padding-bottom:16px; border-bottom:1px solid #e2e8f0; margin-bottom:18px;">
                <div id="dtl-avatar" style="width:48px; height:48px; border-radius:12px; display:grid; place-items:center; font-weight:900; font-size:18px; color:#fff; background:linear-gradient(135deg, #6366f1, #8b5cf6); flex-shrink:0;">
                    HS
                </div>
                <div>
                    <h4 id="dtl-student-name" style="font-size:16px; font-weight:900; color:#0f172a; margin:0;">Nguyễn Văn An</h4>
                    <div style="font-size:12.5px; color:#64748b; margin-top:3px; display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                        <span id="dtl-student-code" class="pill-badge pill-code">HS001</span>
                        <span id="dtl-student-class" class="pill-badge pill-grade">Lớp 3A1</span>
                    </div>
                </div>
            </div>

            <!-- Test Title & Topic -->
            <div style="background:#f8fafc; border:1.5px solid #e2e8f0; border-radius:12px; padding:12px 16px; margin-bottom:18px;">
                <small style="font-size:11px; font-weight:800; color:#64748b; text-transform:uppercase; letter-spacing:0.5px;">BÀI LUYỆN TẬP IC3</small>
                <div id="dtl-test-name" style="font-size:15px; font-weight:900; color:#0f172a; margin-top:2px;">Bài luyện 1</div>
                <div id="dtl-topic-name" style="font-size:12.5px; color:#475569; margin-top:2px;">📚 Căn bản về công nghệ</div>
            </div>

            <!-- Score Big Display -->
            <div style="text-align:center; padding:16px; background:linear-gradient(135deg, #f8fafc, #f1f5f9); border-radius:16px; border:1.5px solid #cbd5e1; margin-bottom:18px;">
                <small style="font-size:11.5px; font-weight:800; color:#64748b; text-transform:uppercase;">KẾT QUẢ ĐIỂM SỐ</small>
                <div style="display:flex; align-items:center; justify-content:center; gap:8px; margin:6px 0;">
                    <span id="dtl-score-num" style="font-size:38px; font-weight:1000; color:#15803d; font-family:'Fredoka', sans-serif;">1000</span>
                    <span style="font-size:16px; color:#94a3b8; font-weight:700;">/ 1000đ</span>
                </div>
                <div id="dtl-status-badge" style="display:inline-block;">
                    <span class="pill-badge pill-perfect" style="font-size:12px; padding:4px 12px;">👑 Xuất sắc</span>
                </div>
            </div>

            <!-- Key Metric 3-Grid -->
            <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:10px; margin-bottom:18px; text-align:center;">
                <div style="background:#ffffff; border:1.5px solid #e2e8f0; border-radius:10px; padding:10px 8px;">
                    <small style="display:block; font-size:10.5px; color:#64748b; font-weight:800;">SỐ CÂU ĐÚNG</small>
                    <b id="dtl-answers-count" style="font-size:15px; color:#0f172a; margin-top:2px; display:block;">14 / 14</b>
                </div>
                <div style="background:#ffffff; border:1.5px solid #e2e8f0; border-radius:10px; padding:10px 8px;">
                    <small style="display:block; font-size:10.5px; color:#64748b; font-weight:800;">THỜI GIAN</small>
                    <b id="dtl-duration" style="font-size:15px; color:#0f172a; margin-top:2px; display:block;">⏱️ 02:45</b>
                </div>
                <div style="background:#ffffff; border:1.5px solid #e2e8f0; border-radius:10px; padding:10px 8px;">
                    <small style="display:block; font-size:10.5px; color:#64748b; font-weight:800;">THỜI ĐIỂM NỘP</small>
                    <b id="dtl-completed-at" style="font-size:12px; color:#0f172a; margin-top:4px; display:block;">29/08 15:30</b>
                </div>
            </div>

            <!-- Pedagogical Verdict -->
            <div id="dtl-verdict-box" style="padding:12px 14px; background:#ecfdf5; border:1.5px solid #a7f3d0; border-radius:10px; font-size:12.5px; color:#065f46; font-weight:700; line-height:1.4;">
                💡 <b>Đánh giá:</b> <span id="dtl-verdict-text">Học sinh hoàn thành xuất sắc bài thi với điểm số tối đa.</span>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn-ghost" onclick="window.print()">🖨️ In Phiếu Điểm</button>
            <button type="button" class="btn-primary" onclick="closeAttemptDetailModal()">Đóng</button>
        </div>
    </div>
</div>

<!-- ===========================================================================
     🎓 MODAL XEM HỒ SƠ CHI TIẾT HỌC SINH (STUDENT PROFILE MODAL)
     =========================================================================== -->
<div id="student-profile-modal" class="modal-backdrop" style="display:none; z-index: 10500;">
    <div class="modal-box" style="width: min(560px, 95vw); border-radius: 20px; overflow: hidden; box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.35); border: 2.5px solid #ffffff;">
        <!-- Header Banner Gamified -->
        <div style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #2563eb 100%); padding: 24px; color: #ffffff; position: relative;">
            <button type="button" class="modal-close-btn" onclick="closeStudentProfileModal()" style="position: absolute; right: 16px; top: 16px; color: #ffffff; background: rgba(255,255,255,0.2); border: none; border-radius: 50%; width: 32px; height: 32px; cursor: pointer; display: grid; place-items: center; font-size: 14px;">✕</button>
            <div style="display: flex; align-items: center; gap: 16px;">
                <div id="sp-avatar-wrap" style="position: relative;">
                    <div id="sp-avatar" style="width: 60px; height: 60px; border-radius: 16px; background: #ffffff; color: #4f46e5; font-size: 26px; font-weight: 900; display: grid; place-items: center; box-shadow: 0 4px 14px rgba(0,0,0,0.2); border: 3px solid #ffffff;">
                        Q
                    </div>
                    <span id="sp-status-dot" style="position: absolute; bottom: -2px; right: -2px; width: 14px; height: 14px; border-radius: 50%; background: #10b981; border: 2.5px solid #ffffff;"></span>
                </div>
                <div style="min-width: 0; flex: 1;">
                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <h3 id="sp-name" style="margin: 0; font-size: 20px; font-weight: 900; color: #ffffff; text-shadow: 0 1px 3px rgba(0,0,0,0.2);">
                            Quỳnh Anh
                        </h3>
                        <span id="sp-code-badge" class="pill-badge pill-code" style="font-size: 11px; padding: 2px 8px; font-weight: 800; background: rgba(255,255,255,0.25); color: #ffffff; border: 1px solid rgba(255,255,255,0.4);">
                            HS013
                        </span>
                        <span id="sp-status-badge" style="font-size: 11px; padding: 2px 8px; border-radius: 999px; font-weight: 800; background: #dcfce7; color: #15803d;">
                            🟢 Đang học
                        </span>
                    </div>
                    <div id="sp-email" style="font-size: 12.5px; color: #e0e7ff; margin-top: 4px; font-weight: 600;">
                        quynhanh.3a1@student.ic3.local
                    </div>
                </div>
            </div>
        </div>

        <!-- Body: 4 Thẻ Pods Thông Số Năng Lượng -->
        <div class="modal-body" style="padding: 20px 24px; background: #f8fafc;">
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 18px;">
                <div style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 12px 14px;">
                    <span style="font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; display: block;">🔑 KHỐI ĐƯỢC CẤP</span>
                    <div id="sp-levels" style="margin-top: 6px; display: flex; flex-wrap: wrap; gap: 4px;">
                        <span class="pill-badge pill-grade" style="font-size: 11px; padding: 2px 7px;">Khối 3</span>
                    </div>
                </div>

                <div style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 12px 14px;">
                    <span style="font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; display: block;">📝 LƯỢT LUYỆN THI</span>
                    <div id="sp-attempts" style="margin-top: 6px; font-size: 16px; font-weight: 900; color: #0f172a;">
                        2 lượt thi
                    </div>
                </div>

                <div style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 12px 14px;">
                    <span style="font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; display: block;">👩‍🏫 GIÁO VIÊN PHỤ TRÁCH</span>
                    <div id="sp-teacher" style="margin-top: 6px; font-size: 13.5px; font-weight: 800; color: #0f172a;">
                        Cô Mai Linh
                    </div>
                </div>

                <div style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 12px 14px;">
                    <span style="font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; display: block;">📅 NGÀY TẠO TÀI KHOẢN</span>
                    <div id="sp-created" style="margin-top: 6px; font-size: 13px; font-weight: 700; color: #475569;">
                        07/09/2026
                    </div>
                </div>
            </div>

            <!-- Ghi chú nhanh -->
            <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; padding: 10px 14px; font-size: 12px; color: #1e40af; display: flex; align-items: center; gap: 8px;">
                <span>💡</span>
                <span>Thầy/Cô có thể cấp thêm khối luyện thi hoặc chỉnh sửa thông tin/mật khẩu cho học sinh bất kỳ lúc nào.</span>
            </div>
        </div>

        <!-- Footer Actions -->
        <div class="modal-footer" style="padding: 14px 24px; background: #ffffff; border-top: 1.5px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div style="display: flex; gap: 8px;">
                <button type="button" id="sp-btn-edit" class="btn-primary" style="padding: 7px 14px; font-size: 12.5px; display: inline-flex; align-items: center; gap: 5px;">
                    <span>✏️</span> Sửa thông tin
                </button>
                <button type="button" id="sp-btn-grant" class="btn-ghost" style="padding: 7px 14px; font-size: 12.5px; display: inline-flex; align-items: center; gap: 5px;">
                    <span>🔑</span> Cấp khối
                </button>
            </div>
            <button type="button" class="btn-ghost" onclick="closeStudentProfileModal()" style="padding: 7px 16px; font-size: 12.5px;">
                Đóng
            </button>
        </div>
    </div>
</div>

<!-- ===========================================================================
     👩‍🏫 MODAL HỒ SƠ GIÁO VIÊN & TÀI KHOẢN CÁ NHÂN (MODERN PROFILE MODAL)
     =========================================================================== -->
<div id="teacher-profile-modal" class="modal-backdrop" style="display:none; z-index: 10500;">
    <div class="modal-box" style="width: min(580px, 95vw); border-radius: 22px; overflow: hidden; background: #ffffff; border: 3px solid #ffffff; box-shadow: 0 25px 60px -15px rgba(15, 23, 42, 0.3), 0 0 0 1px rgba(226, 232, 240, 0.8);">
        
        <!-- 1. Cover Banner Nền Sống Động Hiện Đại (Modern Profile Cover) -->
        <div style="height: 95px; background: linear-gradient(135deg, #1e40af 0%, #0284c7 50%, #0ea5e9 100%); position: relative; overflow: hidden;">
            <!-- Họa tiết sóng ánh sáng trang trí -->
            <div style="position: absolute; inset: 0; background: radial-gradient(circle at 80% 20%, rgba(255,255,255,0.25) 0%, transparent 60%);"></div>
            <div style="position: absolute; bottom: 0; left: 0; right: 0; height: 30px; background: linear-gradient(to top, rgba(0,0,0,0.08), transparent);"></div>
            
            <button type="button" class="modal-close-btn" onclick="closeTeacherProfileModal()" style="position: absolute; right: 14px; top: 14px; color: #ffffff; background: rgba(0, 0, 0, 0.25); backdrop-filter: blur(8px); border: 1.5px solid rgba(255,255,255,0.3); border-radius: 50%; width: 32px; height: 32px; cursor: pointer; display: grid; place-items: center; font-size: 13px; font-weight: 900; transition: all 0.2s;" onmouseover="this.style.background='rgba(239, 68, 68, 0.85)'; this.style.borderColor='#fca5a5';" onmouseout="this.style.background='rgba(0, 0, 0, 0.25)'; this.style.borderColor='rgba(255,255,255,0.3)';">✕</button>
        </div>

        <!-- 2. Avatar Overlap & Thông Tin Cá Nhân Sắc Nét -->
        <div style="padding: 0 24px 16px; position: relative;">
            <div style="display: flex; align-items: flex-end; justify-content: space-between; margin-top: -42px; margin-bottom: 12px;">
                <!-- Avatar 3D nổi bật có chấm online -->
                <div style="position: relative; width: 76px; height: 76px; flex-shrink: 0;">
                    <div style="width: 100%; height: 100%; border-radius: 20px; background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%); color: #ffffff; font-size: 30px; font-weight: 900; display: grid; place-items: center; box-shadow: 0 10px 25px rgba(2, 132, 199, 0.35); border: 4px solid #ffffff;">
                        {{ $isTeacher ? 'GV' : 'AD' }}
                    </div>
                    <div style="position: absolute; bottom: 2px; right: 2px; width: 16px; height: 16px; background: #22c55e; border: 3px solid #ffffff; border-radius: 50%; box-shadow: 0 2px 5px rgba(0,0,0,0.15);" title="Đang hoạt động"></div>
                </div>
                <!-- Badge Vai Trò Hiện Đại -->
                <span style="font-size: 12px; padding: 5px 12px; border-radius: 999px; font-weight: 850; background: #ecfdf5; color: #047857; border: 1.5px solid #a7f3d0; box-shadow: 0 2px 6px rgba(16, 185, 129, 0.12); display: inline-flex; align-items: center; gap: 5px;">
                    {{ $isTeacher ? '👩‍🏫 Giáo Viên Phụ Trách' : '👑 Quản Trị Viên' }}
                </span>
            </div>

            <!-- Tên & Liên hệ rõ chữ, phân tách chip thẻ -->
            <div>
                <h3 style="margin: 0; font-size: 21px; font-weight: 900; color: #0f172a; letter-spacing: -0.4px;">
                    {{ auth()->user()->name }}
                </h3>
                
                <div style="display: flex; align-items: center; gap: 8px; margin-top: 8px; flex-wrap: wrap;">
                    <span style="display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 12px; color: #334155; font-weight: 700;">
                        <span>✉️</span> {{ auth()->user()->email }}
                    </span>
                    @if(auth()->user()->phone)
                        <span style="display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 12px; color: #334155; font-weight: 700;">
                            <span>📞</span> {{ auth()->user()->phone }}
                        </span>
                    @endif
                    @if(auth()->user()->school_name)
                        <span style="display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 12px; color: #334155; font-weight: 700;">
                            <span>🏫</span> {{ auth()->user()->school_name }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- 3. Thẻ Bản Quyền & Năng Lực (VIP Subscription Card) -->
        <div class="modal-body" style="padding: 0 24px 20px; background: #ffffff;">
            @if($isTeacher)
                @php
                    $quotaPct = ($maxStudents > 0) ? min(100, round(($usedStudents / $maxStudents) * 100)) : 0;
                    $remainSlots = max(0, ($maxStudents ?: 0) - $usedStudents);
                    $daysLeft = $expiresAt ? (int) max(0, ceil(now()->floatDiffInDays($expiresAt, false))) : null;
                @endphp

                <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 18px; overflow: hidden; margin-bottom: 16px; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.07);">
                    
                    <!-- Header Gói Thanh Lịch -->
                    <div style="background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; padding: 12px 16px; display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 32px; height: 32px; border-radius: 10px; background: #fef3c7; border: 1.5px solid #fde68a; display: grid; place-items: center; font-size: 16px; flex-shrink: 0;">
                                👑
                            </div>
                            <div>
                                <small style="font-size: 10px; font-weight: 850; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; display: block;">GÓI BẢN QUYỀN ĐANG DÙNG</small>
                                <b style="font-size: 15px; color: #0f172a; font-weight: 900; display: block;">
                                    {{ $activeTeacherOrder ? $activeTeacherOrder->package_name : 'Gói Tiêu Chuẩn (Standard)' }}
                                </b>
                            </div>
                        </div>
                        <span style="font-size: 11.5px; font-weight: 850; color: #15803d; background: #dcfce7; border: 1.5px solid #86efac; padding: 3px 10px; border-radius: 999px; display: inline-flex; align-items: center; gap: 5px;">
                            <span style="width: 7px; height: 7px; border-radius: 50%; background: #16a34a;"></span> Đang hoạt động
                        </span>
                    </div>

                    <!-- Thân Thẻ: 2 Cột Thống Kê Sĩ Số & Hạn Dùng -->
                    <div style="padding: 14px 16px; display: flex; flex-direction: column; gap: 12px;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                            
                            <!-- Cột Sĩ Số -->
                            <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 14px; padding: 12px 14px;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="font-size: 10.5px; font-weight: 850; color: #64748b; text-transform: uppercase;">👥 SĨ SỐ HỌC SINH</span>
                                    <span style="font-size: 11px; font-weight: 800; color: #0284c7; background: #e0f2fe; padding: 2px 7px; border-radius: 6px;">{{ $quotaPct }}%</span>
                                </div>
                                <div style="font-size: 20px; font-weight: 900; color: #0f172a; margin-top: 4px; line-height: 1.2;">
                                    <span style="color: #0284c7;">{{ $usedStudents }}</span> <span style="font-size: 13px; font-weight: 700; color: #64748b;">/ {{ $maxStudents ?: '∞' }} HS</span>
                                </div>
                                <!-- Thanh tiến độ mini -->
                                <div style="height: 6px; background: #e2e8f0; border-radius: 999px; margin-top: 8px; overflow: hidden;">
                                    <div style="height: 100%; width: {{ $quotaPct }}%; background: linear-gradient(90deg, #0284c7, #38bdf8); border-radius: 999px;"></div>
                                </div>
                                <small style="font-size: 11px; color: #475569; font-weight: 700; margin-top: 6px; display: block;">
                                    Còn trống <b style="color: #0284c7;">{{ $remainSlots }}</b> suất
                                </small>
                            </div>

                            <!-- Cột Hạn Dùng (Đảm bảo số ngày luôn là số nguyên sạch đẹp) -->
                            <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 14px; padding: 12px 14px;">
                                <div style="font-size: 10.5px; font-weight: 850; color: #64748b; text-transform: uppercase;">
                                    ⏰ THỜI HẠN BẢN QUYỀN
                                </div>
                                <div style="font-size: 19px; font-weight: 900; color: #0f172a; margin-top: 4px; line-height: 1.2;">
                                    {{ $expiresAt ? $expiresAt->format('d/m/Y') : 'Vĩnh viễn' }}
                                </div>
                                <div style="margin-top: 8px;">
                                    @if($daysLeft !== null)
                                        <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 11.5px; font-weight: 850; color: #047857; background: #ecfdf5; padding: 3px 9px; border-radius: 6px; border: 1px solid #a7f3d0;">
                                            <span>⏳</span> Còn <b>{{ $daysLeft }}</b> ngày
                                        </span>
                                    @else
                                        <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 11.5px; font-weight: 850; color: #047857; background: #ecfdf5; padding: 3px 9px; border-radius: 6px; border: 1px solid #a7f3d0;">
                                            <span>♾️</span> Sử dụng trọn đời
                                        </span>
                                    @endif
                                </div>
                            </div>

                        </div>

                        <!-- Khối Lớp Giảng Dạy -->
                        <div style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 10px 14px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                            <span style="font-size: 11.5px; font-weight: 850; color: #475569; display: flex; align-items: center; gap: 6px;">
                                <span>🎒</span> KHỐI GIẢNG DẠY ĐƯỢC CẤP:
                            </span>
                            <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                                @forelse($teacherLevels as $tl)
                                    <span style="background: #fef08a; color: #854d0e; font-weight: 900; font-size: 12px; padding: 3px 10px; border-radius: 6px; border: 1px solid #fde047;">
                                        Khối {{ $tl->grade }}
                                    </span>
                                @empty
                                    <span style="color: #64748b; font-weight: 700; font-size: 12px;">Chưa cấp khối nào</span>
                                @endforelse
                            </div>
                        </div>
                    </div>

                </div>
            @endif

            <!-- 4. Danh Sách Thao Tác Nhanh (Modern Action Cards) -->
            <div style="display: flex; flex-direction: column; gap: 10px;">
                @if($isTeacher)
                    <button type="button" onclick="closeTeacherProfileModal(); switchAdminTab('tab-teacher-packages');" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; border-radius: 14px; border: 1.5px solid #e2e8f0; background: #ffffff; text-align: left; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor='#0284c7'; this.style.background='#f8fafc'; this.style.transform='translateY(-2px)';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='#ffffff'; this.style.transform='none';">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 38px; height: 38px; border-radius: 12px; background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%); border: 1px solid #7dd3fc; color: #0284c7; display: grid; place-items: center; font-size: 18px; flex-shrink: 0;">
                                💎
                            </div>
                            <div>
                                <b style="font-size: 13.5px; color: #0f172a; font-weight: 850; display: block;">Xem Gói Bản Quyền & Lịch Sử Thuê Gói</b>
                                <span style="font-size: 11.5px; color: #64748b; display: block;">Quản lý mã đơn, hạn mức sĩ số và yêu cầu nâng cấp gói</span>
                            </div>
                        </div>
                        <span style="font-size: 12px; font-weight: 850; color: #0284c7; background: #e0f2fe; padding: 4px 10px; border-radius: 8px;">Chi tiết ➔</span>
                    </button>
                @endif

                <button type="button" onclick="closeTeacherProfileModal(); openEditUserModal(null, true);" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; border-radius: 14px; border: 1.5px solid #e2e8f0; background: #ffffff; text-align: left; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor='#d97706'; this.style.background='#fffbeb'; this.style.transform='translateY(-2px)';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='#ffffff'; this.style.transform='none';">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 38px; height: 38px; border-radius: 12px; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border: 1px solid #fcd34d; color: #d97706; display: grid; place-items: center; font-size: 18px; flex-shrink: 0;">
                            ✏️
                        </div>
                        <div>
                            <b style="font-size: 13.5px; color: #0f172a; font-weight: 850; display: block;">Chỉnh Sửa Thông Tin & Đổi Mật Khẩu</b>
                            <span style="font-size: 11.5px; color: #64748b; display: block;">Cập nhật họ tên, số điện thoại hoặc đổi mật khẩu mới</span>
                        </div>
                    </div>
                    <span style="font-size: 12px; font-weight: 850; color: #d97706; background: #fef3c7; padding: 4px 10px; border-radius: 8px;">Cập nhật ➔</span>
                </button>
            </div>
        </div>

        <!-- 5. Footer Hiện Đại -->
        <div class="modal-footer" style="padding: 14px 24px; background: #f8fafc; border-top: 1.5px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 12px; color: #64748b; font-weight: 700; display: flex; align-items: center; gap: 6px;">
                <span>🛡️</span> Hệ thống Quản trị IC3 Quest
            </span>
            <button type="button" onclick="closeTeacherProfileModal()" style="padding: 9px 24px; font-size: 13.5px; font-weight: 850; border-radius: 12px; background: #0f172a; color: #ffffff; border: 1.5px solid #0f172a; cursor: pointer; transition: all 0.15s ease; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.18);" onmouseover="this.style.background='#1e293b'; this.style.borderColor='#1e293b'; this.style.transform='translateY(-1px)';" onmouseout="this.style.background='#0f172a'; this.style.borderColor='#0f172a'; this.style.transform='none';">
                Đóng
            </button>
        </div>
    </div>
</div>

@if(! $isTeacher)
<!-- ===========================================================================
     💎 MODAL THÊM MỚI GÓI DỊCH VỤ (CREATE PACKAGE MODAL)
     =========================================================================== -->
<div id="create-package-modal" class="modal-backdrop">
    <div class="modal-box" style="width: min(720px, 95vw); border-radius: 18px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
        <div class="modal-header">
            <h3 style="display:flex; align-items:center; gap:8px; margin:0; font-size:18px; font-weight:900;">
                <span>💎</span> Thêm Mới Gói Dịch Vụ & Bản Quyền IC3 GS6
            </h3>
            <button type="button" class="modal-close-btn" onclick="closeCreatePackageModal()">✕</button>
        </div>
        <form method="POST" action="{{ route('admin.packages.store') }}">
            @csrf
            <div class="modal-body" style="max-height: 75vh; overflow-y: auto;">
                <div class="form-grid-2">
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label><span class="label-title">📦 Tên gói dịch vụ <span class="req">*</span></span></label>
                        <input name="name" class="form-control" required placeholder="Ví dụ: Gói Tiêu Chuẩn (Standard)" oninput="autoGenPackageSlug(this.value, 'create-package-slug')">
                        <small class="modal-field-hint">Tên hiển thị rõ ràng trên bảng giá</small>
                    </div>

                    <div class="form-group">
                        <label><span class="label-title">🏷️ Mã định danh (Slug)</span></label>
                        <input name="slug" id="create-package-slug" class="form-control" placeholder="tu-dong-theo-ten">
                        <small class="modal-field-hint">Định danh URL (để trống sẽ tự tạo)</small>
                    </div>

                    <div class="form-group">
                        <label><span class="label-title">✨ Huy hiệu nổi bật (Badge)</span></label>
                        <input name="badge" class="form-control" placeholder="Ví dụ: Phổ biến nhất ⭐, Tiết kiệm 🔥">
                        <small class="modal-field-hint">Nhãn tag nhỏ gây ấn tượng trên bảng giá</small>
                    </div>

                    <div class="form-group">
                        <label><span class="label-title">💰 Giá bán thực tế (VND) <span class="req">*</span></span></label>
                        <input name="price" type="number" step="1000" class="form-control" required placeholder="Ví dụ: 990000" min="0">
                        <small class="modal-field-hint">Giá giáo viên sẽ thanh toán</small>
                    </div>

                    <div class="form-group">
                        <label><span class="label-title">🏷️ Giá gốc niêm yết (VND)</span></label>
                        <input name="original_price" type="number" step="1000" class="form-control" placeholder="Ví dụ: 1490000 (hiển thị gạch ngang)" min="0">
                        <small class="modal-field-hint">Giá gạch ngang để làm nổi bật giảm giá</small>
                    </div>

                    <div class="form-group">
                        <label><span class="label-title">⏰ Thời hạn sử dụng (Ngày) <span class="req">*</span></span></label>
                        <input name="duration_days" id="create-pkg-duration" type="number" class="form-control" required placeholder="Ví dụ: 90" min="1">
                        <div style="display:flex; gap:6px; margin-top:6px; flex-wrap:wrap;">
                            <button type="button" class="btn-ghost" style="padding:2px 7px; font-size:11px;" onclick="document.getElementById('create-pkg-duration').value=30">1 tháng (30 ngày)</button>
                            <button type="button" class="btn-ghost" style="padding:2px 7px; font-size:11px;" onclick="document.getElementById('create-pkg-duration').value=90">3 tháng (90 ngày)</button>
                            <button type="button" class="btn-ghost" style="padding:2px 7px; font-size:11px;" onclick="document.getElementById('create-pkg-duration').value=180">6 tháng (180 ngày)</button>
                            <button type="button" class="btn-ghost" style="padding:2px 7px; font-size:11px;" onclick="document.getElementById('create-pkg-duration').value=365">1 năm (365 ngày)</button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><span class="label-title">👥 Sĩ số học sinh tối đa <span class="req">*</span></span></label>
                        <input name="max_students" type="number" class="form-control" required placeholder="Ví dụ: 100" min="1">
                        <small class="modal-field-hint">Hạn mức số lượng học sinh giáo viên được tạo</small>
                    </div>

                    <div class="form-group">
                        <label><span class="label-title">🔢 Thứ tự hiển thị</span></label>
                        <input name="position" type="number" class="form-control" value="1" min="0">
                        <small class="modal-field-hint">Số nhỏ đứng trước</small>
                    </div>

                    <div class="form-group">
                        <label><span class="label-title">⚡ Trạng thái mở bán</span></label>
                        <select name="is_active" class="form-control" style="font-weight:700;">
                            <option value="1" selected>🟢 Mở bán ngay</option>
                            <option value="0">⚪ Tạm ẩn</option>
                        </select>
                    </div>

                    <!-- Phân quyền khối lớp -->
                    <div class="form-group" style="grid-column: 1 / -1; background:#f8fafc; border:1.5px dashed #cbd5e1; border-radius:12px; padding:12px 16px;">
                        <label><span class="label-title" style="color:#0f172a; font-weight:800;">🔑 Khối lớp được cấp quyền trong gói:</span></label>
                        <small class="modal-field-hint" style="margin-bottom:8px;">Giáo viên mua gói này sẽ được truy cập và cấp bài thi của các khối đã chọn</small>
                        <div style="display:flex; gap:12px; flex-wrap:wrap;">
                            @foreach($levels as $lvl)
                                <label class="chip-label" style="padding:7px 12px; border-radius:8px;">
                                    <input type="checkbox" name="level_ids[]" value="{{ $lvl->id }}" checked>
                                    <b style="color:#0f172a;">Khối {{ $lvl->grade }}</b> — {{ $lvl->name }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label><span class="label-title">📝 Mô tả ngắn tóm tắt gói</span></label>
                        <input name="description" class="form-control" placeholder="Ví dụ: Phù hợp cho giáo viên chủ nhiệm nhiều lớp...">
                    </div>

                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label><span class="label-title">📋 Danh sách tính năng (Mỗi dòng 1 tính năng)</span></label>
                        <textarea name="features_text" class="form-control" rows="4" placeholder="Toàn bộ ngân hàng đề thi IC3 Spark GS6&#10;Báo cáo phân tích điểm số tự động&#10;Hỗ trợ kỹ thuật 24/7"></textarea>
                        <small class="modal-field-hint">Mỗi dòng là một gạch đầu dòng có icon xanh trên bảng giá</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeCreatePackageModal()">Hủy</button>
                <button type="submit" class="btn-primary">✓ Lưu Gói Dịch Vụ</button>
            </div>
        </form>
    </div>
</div>

<!-- ===========================================================================
     ✏️ MODAL CHỈNH SỬA GÓI DỊCH VỤ (EDIT PACKAGE MODAL)
     =========================================================================== -->
<div id="edit-package-modal" class="modal-backdrop">
    <div class="modal-box" style="width: min(720px, 95vw); border-radius: 18px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
        <div class="modal-header">
            <h3 style="display:flex; align-items:center; gap:8px; margin:0; font-size:18px; font-weight:900;">
                <span>✏️</span> Chỉnh Sửa Gói Dịch Vụ — <b id="edit-pkg-title-name" style="color:var(--brand);"></b>
            </h3>
            <button type="button" class="modal-close-btn" onclick="closeEditPackageModal()">✕</button>
        </div>
        <form id="edit-package-form" method="POST" action="">
            @csrf
            @method('PUT')
            <div class="modal-body" style="max-height: 75vh; overflow-y: auto;">
                <div class="form-grid-2">
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label><span class="label-title">📦 Tên gói dịch vụ <span class="req">*</span></span></label>
                        <input name="name" id="edit-pkg-name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label><span class="label-title">🏷️ Mã định danh (Slug)</span></label>
                        <input name="slug" id="edit-pkg-slug" class="form-control">
                    </div>

                    <div class="form-group">
                        <label><span class="label-title">✨ Huy hiệu nổi bật (Badge)</span></label>
                        <input name="badge" id="edit-pkg-badge" class="form-control">
                    </div>

                    <div class="form-group">
                        <label><span class="label-title">💰 Giá bán thực tế (VND) <span class="req">*</span></span></label>
                        <input name="price" id="edit-pkg-price" type="number" step="1000" class="form-control" required min="0">
                    </div>

                    <div class="form-group">
                        <label><span class="label-title">🏷️ Giá gốc niêm yết (VND)</span></label>
                        <input name="original_price" id="edit-pkg-original-price" type="number" step="1000" class="form-control" min="0">
                    </div>

                    <div class="form-group">
                        <label><span class="label-title">⏰ Thời hạn sử dụng (Ngày) <span class="req">*</span></span></label>
                        <input name="duration_days" id="edit-pkg-duration" type="number" class="form-control" required min="1">
                    </div>

                    <div class="form-group">
                        <label><span class="label-title">👥 Sĩ số học sinh tối đa <span class="req">*</span></span></label>
                        <input name="max_students" id="edit-pkg-max-students" type="number" class="form-control" required min="1">
                    </div>

                    <div class="form-group">
                        <label><span class="label-title">🔢 Thứ tự hiển thị</span></label>
                        <input name="position" id="edit-pkg-position" type="number" class="form-control" min="0">
                    </div>

                    <div class="form-group">
                        <label><span class="label-title">⚡ Trạng thái mở bán</span></label>
                        <select name="is_active" id="edit-pkg-is-active" class="form-control" style="font-weight:700;">
                            <option value="1">🟢 Mở bán ngay</option>
                            <option value="0">⚪ Tạm ẩn</option>
                        </select>
                    </div>

                    <!-- Phân quyền khối lớp -->
                    <div class="form-group" style="grid-column: 1 / -1; background:#f8fafc; border:1.5px dashed #cbd5e1; border-radius:12px; padding:12px 16px;">
                        <label><span class="label-title" style="color:#0f172a; font-weight:800;">🔑 Khối lớp được cấp quyền trong gói:</span></label>
                        <div style="display:flex; gap:12px; flex-wrap:wrap; margin-top:8px;">
                            @foreach($levels as $lvl)
                                <label class="chip-label" style="padding:7px 12px; border-radius:8px;">
                                    <input type="checkbox" name="level_ids[]" value="{{ $lvl->id }}" id="edit-pkg-lvl-{{ $lvl->id }}" class="edit-pkg-lvl-chk">
                                    <b style="color:#0f172a;">Khối {{ $lvl->grade }}</b> — {{ $lvl->name }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label><span class="label-title">📝 Mô tả ngắn tóm tắt gói</span></label>
                        <input name="description" id="edit-pkg-description" class="form-control">
                    </div>

                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label><span class="label-title">📋 Danh sách tính năng (Mỗi dòng 1 tính năng)</span></label>
                        <textarea name="features_text" id="edit-pkg-features-text" class="form-control" rows="4"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeEditPackageModal()">Hủy</button>
                <button type="submit" class="btn-primary">✓ Cập Nhật Gói</button>
            </div>
        </form>
    </div>
</div>

<!-- ===========================================================================
     ✕ MODAL TỪ CHỐI ĐƠN HÀNG (REJECT ORDER MODAL)
     =========================================================================== -->
<div id="reject-order-modal" class="modal-backdrop">
    <div class="modal-box" style="width: min(500px, 95vw); border-radius: 18px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
        <div class="modal-header">
            <h3 style="display:flex; align-items:center; gap:8px; margin:0; font-size:18px; font-weight:900; color:#ef4444;">
                <span>✕</span> Từ Chối Đơn Hàng #<span id="reject-order-code"></span>
            </h3>
            <button type="button" class="modal-close-btn" onclick="closeRejectOrderModal()">✕</button>
        </div>
        <form id="reject-order-form" method="POST" action="">
            @csrf
            <div class="modal-body">
                <p style="font-size:13px; color:#64748b; margin-top:0;">
                    Bạn đang chuẩn bị từ chối đơn hàng của giáo viên: <b id="reject-order-teacher" style="color:#0f172a;"></b>.
                </p>
                <div class="form-group">
                    <label><span class="label-title">Lý do từ chối (Ghi chú phản hồi):</span></label>
                    <textarea name="reason" id="reject-order-reason" class="form-control" rows="3" placeholder="Ví dụ: Chưa nhận được giao dịch chuyển khoản qua tài khoản ngân hàng..."></textarea>
                    <div style="display:flex; gap:6px; margin-top:6px; flex-wrap:wrap;">
                        <button type="button" class="btn-ghost" style="padding:2px 7px; font-size:11px;" onclick="document.getElementById('reject-order-reason').value='Chưa nhận được giao dịch chuyển khoản qua ngân hàng.'">Chưa nhận tiền</button>
                        <button type="button" class="btn-ghost" style="padding:2px 7px; font-size:11px;" onclick="document.getElementById('reject-order-reason').value='Thông tin chuyển khoản không trùng khớp mã đơn hàng.'">Sai mã chuyển khoản</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeRejectOrderModal()">Quay lại</button>
                <button type="submit" class="btn-primary" style="background:#ef4444; border-color:#ef4444;">✕ Xác Nhận Từ Chối</button>
            </div>
        </form>
    </div>
</div>
@endif

<script>
    const tabMeta = {
        'tab-results': {
            title: 'Trung Tâm Phân Tích & Báo Cáo Điểm Số',
            breadcrumb: '📊 Báo cáo & Luyện thi'
        },
        'tab-users': {
            title: 'Quản Trị Giáo Viên & Danh Sách Học Sinh',
            breadcrumb: '👥 Giáo viên & Học sinh'
        },
        'tab-levels': {
            title: 'Quản Trị Khung Chương Trình & Khối Lớp',
            breadcrumb: '🔑 Khung Chương trình & Khối lớp'
        },
        'tab-packages': {
            title: 'Quản Trị Gói Dịch Vụ & Bản Quyền IC3 GS6',
            breadcrumb: '💎 Gói & Bản quyền'
        },
        'tab-orders': {
            title: 'Quản Lý Đơn Hàng & Kích Hoạt Bản Quyền',
            breadcrumb: '📋 Đơn hàng bản quyền'
        },
        'tab-chat': {
            title: 'Trung Tâm Tin Nhắn Live Chat & Tư Vấn Giáo Viên',
            breadcrumb: '💬 Tư Vấn & Live Chat'
        },
        'tab-classes': {
            title: 'Quản Trị Giáo Viên & Danh Sách Học Sinh',
            breadcrumb: '👥 Giáo viên & Học sinh'
        },
        'tab-teacher-packages': {
            title: 'Quản Lý Bản Quyền & Lịch Sử Thuê Gói',
            breadcrumb: '💎 Gói Bản Quyền'
        }
    };
    window.tabMeta = tabMeta;

    let currentChatMsgId = '{{ $supportMessages->isNotEmpty() ? $supportMessages->first()->id : "" }}';

    function selectChatConversation(card) {
        document.querySelectorAll('.ms-conv-item').forEach(c => c.classList.remove('active'));
        card.classList.add('active');

        const id = card.getAttribute('data-id');
        currentChatMsgId = id;

        const name = card.getAttribute('data-name') || '';
        const phone = card.getAttribute('data-phone') || '';
        const email = card.getAttribute('data-email') || '';
        const message = card.getAttribute('data-message') || '';
        const adminReply = card.getAttribute('data-admin-reply') || '';
        const repliedAt = card.getAttribute('data-replied-at') || '';
        const status = card.getAttribute('data-status') || 'pending';
        const initials = card.getAttribute('data-initials') || 'KH';
        const gradient = card.getAttribute('data-gradient') || 'linear-gradient(135deg, #0084ff, #00c6ff)';
        const ip = card.getAttribute('data-ip') || '127.0.0.1';
        const time = card.getAttribute('data-time') || '';
        const userType = card.getAttribute('data-user-type') || 'guest';
        const userTypeLabel = card.getAttribute('data-user-type-label') || '🌐 Khách Vãng Lai';

        // 1. Cập nhật Header giữa
        const nameEl = document.getElementById('chat-detail-name');
        if (nameEl) nameEl.innerText = name;

        const phoneEl = document.getElementById('chat-detail-phone');
        if (phoneEl) phoneEl.innerHTML = '📞 ' + (phone || 'Chưa có SĐT');

        const tagEl = document.getElementById('chat-detail-user-tag');
        if (tagEl) {
            tagEl.innerText = userTypeLabel;
            tagEl.className = 'ms-user-tag ' + (userType === 'teacher' ? 'tag-teacher' : (userType === 'student' ? 'tag-student' : 'tag-guest'));
        }

        const avatarHeader = document.getElementById('chat-detail-avatar');
        if (avatarHeader) {
            avatarHeader.style.background = gradient;
            avatarHeader.innerText = initials;
        }

        // 2. Cập nhật Messages Body (Xử lý sạch không hiển thị chữ undefined)
        const timeStampEl = document.getElementById('chat-detail-time-stamp');
        if (timeStampEl) timeStampEl.innerHTML = `<span>${time || 'Hôm nay'}</span>`;

        const bubblesWrap = document.getElementById('chat-incoming-bubbles-wrap');
        if (bubblesWrap) {
            let safeMsg = (message || '').trim();
            if (!safeMsg || safeMsg === 'undefined') {
                safeMsg = 'Dạ em chào Admin, em cần hỗ trợ tư vấn về tài khoản và gói luyện thi ạ!';
            }
            const rawLines = safeMsg.split('\n').map(l => l.trim()).filter(l => l.length > 0 && l !== 'undefined');
            const lines = rawLines.length > 0 ? rawLines : [safeMsg];
            let html = '';
            lines.forEach((line, idx) => {
                const isLast = idx === lines.length - 1;
                html += `
                    <div class="ms-message-row incoming">
                        ${isLast ? `<div class="ms-mini-avatar" style="background: ${gradient};">${initials}</div>` : `<div style="width:28px; height:28px; flex-shrink:0;"></div>`}
                        <div>
                            <div class="ms-bubble-text">${line}</div>
                            ${isLast ? `<div class="ms-bubble-meta">📩 Khách gửi · Live Chat</div>` : ''}
                        </div>
                    </div>
                `;
            });
            bubblesWrap.innerHTML = html;
        }

        // Outgoing Admin Reply row
        const replyContainer = document.getElementById('chat-admin-reply-container');
        const replyText = document.getElementById('chat-admin-reply-text');
        const replyMeta = document.getElementById('chat-admin-reply-meta');
        if (replyContainer && replyText) {
            if (adminReply) {
                replyText.innerText = adminReply;
                if (replyMeta) replyMeta.innerText = '✓✓ Đã phản hồi ' + (repliedAt || '');
                replyContainer.style.display = 'flex';
            } else {
                replyContainer.style.display = 'none';
            }
        }

        // Action links
        const cleanPhone = (phone || '').replace(/[^0-9]/g, '');
        const callBtn = document.getElementById('btn-call-phone');
        if (callBtn) callBtn.href = 'tel:' + cleanPhone;

        const zaloBtn = document.getElementById('btn-zalo');
        if (zaloBtn) zaloBtn.href = 'https://zalo.me/' + cleanPhone;

        // 3. Cập nhật Right Drawer
        const drawerAvatar = document.getElementById('drawer-avatar');
        if (drawerAvatar) {
            drawerAvatar.style.background = gradient;
            drawerAvatar.innerText = initials;
        }

        const drawerName = document.getElementById('drawer-name');
        if (drawerName) drawerName.innerText = name;

        const drawerRoleBadge = document.getElementById('drawer-role-badge');
        if (drawerRoleBadge) {
            drawerRoleBadge.innerText = userTypeLabel;
            drawerRoleBadge.className = 'drawer-role-badge ' + (userType === 'teacher' ? 'badge-teacher' : (userType === 'student' ? 'badge-student' : 'badge-guest'));
        }

        const drawerPhone = document.getElementById('drawer-phone');
        if (drawerPhone) drawerPhone.innerText = phone || 'Chưa cập nhật';

        const drawerEmail = document.getElementById('drawer-email');
        if (drawerEmail) drawerEmail.innerText = email || 'N/A';

        const drawerTime = document.getElementById('drawer-time');
        if (drawerTime) drawerTime.innerText = time;

        const drawerIp = document.getElementById('drawer-ip');
        if (drawerIp) drawerIp.innerText = ip;

        const drawerTelBtn = document.getElementById('drawer-btn-tel');
        if (drawerTelBtn) drawerTelBtn.href = 'tel:' + cleanPhone;

        const drawerZaloBtn = document.getElementById('drawer-btn-zalo');
        if (drawerZaloBtn) drawerZaloBtn.href = 'https://zalo.me/' + cleanPhone;

        // Cập nhật Smart Action Box theo phân loại người gửi
        const smartBox = document.getElementById('drawer-smart-action-box');
        const smartTitle = document.getElementById('smart-box-title');
        const smartDesc = document.getElementById('smart-box-desc');
        const quickCreateBtn = document.getElementById('btn-quick-create-teacher');

        if (smartBox) {
            if (userType === 'guest') {
                smartBox.style.background = '#faf5ff';
                smartBox.style.borderColor = '#c084fc';
                if (smartTitle) {
                    smartTitle.innerText = '🌐 KHÁCH VÃNG LAI (CHƯA CÓ TÀI KHOẢN)';
                    smartTitle.style.color = '#7c3aed';
                }
                if (smartDesc) smartDesc.innerText = 'Khách gửi tin từ website ngoài. Bạn có thể tư vấn gói và bấm nút dưới để tạo nhanh tài khoản Giáo viên.';
                if (quickCreateBtn) {
                    quickCreateBtn.style.display = 'flex';
                    quickCreateBtn.innerHTML = '<span>⚡</span> Tạo Tài Khoản Giáo Viên';
                    quickCreateBtn.onclick = openCreateTeacherFromCurrentChat;
                }
            } else if (userType === 'teacher') {
                smartBox.style.background = '#f0fdf4';
                smartBox.style.borderColor = '#86efac';
                if (smartTitle) {
                    smartTitle.innerText = '👨‍🏫 GIÁO VIÊN HỆ THỐNG';
                    smartTitle.style.color = '#15803d';
                }
                if (smartDesc) smartDesc.innerText = 'Thầy/Cô đã có tài khoản trên hệ thống MOS IC3. Bấm để chuyển nhanh sang Quản lý lớp & Học sinh.';
                if (quickCreateBtn) {
                    quickCreateBtn.style.display = 'flex';
                    quickCreateBtn.innerHTML = '<span>👥</span> Xem Quản Trị Giáo Viên';
                    quickCreateBtn.onclick = () => {
                        if (typeof switchAdminTab === 'function') {
                            switchAdminTab('tab-users', document.querySelector('[data-tab="tab-users"]'));
                        }
                    };
                }
            } else {
                smartBox.style.background = '#eff6ff';
                smartBox.style.borderColor = '#93c5fd';
                if (smartTitle) {
                    smartTitle.innerText = '🎓 HỌC SINH HỆ THỐNG';
                    smartTitle.style.color = '#1d4ed8';
                }
                if (smartDesc) smartDesc.innerText = 'Em học sinh đã có tài khoản trên MOS IC3. Bấm để tra cứu kết quả thi luyện của học sinh.';
                if (quickCreateBtn) {
                    quickCreateBtn.style.display = 'flex';
                    quickCreateBtn.innerHTML = '<span>📊</span> Tra Cứu Điểm Luyện Thi';
                    quickCreateBtn.onclick = () => {
                        if (typeof filterResultsByStudent === 'function') {
                            filterResultsByStudent(name);
                        }
                    };
                }
            }
        }

        // Cập nhật trạng thái Drawer Chips
        document.querySelectorAll('.drawer-status-chip').forEach(c => {
            c.classList.remove('active-pending', 'active-replied', 'active-closed');
        });
        const activeChip = document.getElementById('chip-status-' + status);
        if (activeChip) {
            activeChip.classList.add('active-' + status);
        }

        // Tự động cuộn xuống cuối đoạn chat
        const stream = document.getElementById('chat-conversation-body');
        if (stream) stream.scrollTop = stream.scrollHeight;
    }

    // ⚡ TỰ ĐỘNG MỞ MODAL TẠO TÀI KHOẢN GIÁO VIÊN TỪ THÔNG TIN KHÁCH VÃNG LAI
    function openCreateTeacherFromCurrentChat() {
        const activeCard = document.querySelector(`.ms-conv-item[data-id="${currentChatMsgId}"]`) || document.querySelector('.ms-conv-item.active');
        const name = activeCard ? activeCard.getAttribute('data-name') : (document.getElementById('drawer-name')?.innerText || '');
        const phone = activeCard ? activeCard.getAttribute('data-phone') : (document.getElementById('drawer-phone')?.innerText || '');
        const email = activeCard ? activeCard.getAttribute('data-email') : (document.getElementById('drawer-email')?.innerText || '');

        openCreateUserModal();

        const form = document.getElementById('create-user-form');
        if (form) {
            const nameInput = form.querySelector('input[name="name"]');
            if (nameInput) nameInput.value = name;

            const roleSelect = document.getElementById('select-user-role');
            if (roleSelect) {
                roleSelect.value = 'teacher';
                toggleStudentClassSelect('teacher');
            }

            const emailInput = form.querySelector('input[name="email"]');
            if (emailInput) {
                const cleanPhone = (phone || '').replace(/[^0-9]/g, '');
                if (email && email !== 'N/A' && email.includes('@')) {
                    emailInput.value = email;
                } else if (cleanPhone) {
                    emailInput.value = `gv_${cleanPhone}@mosic3.edu.vn`;
                } else {
                    emailInput.value = '';
                }
            }

            const passwordInput = form.querySelector('input[name="password"]');
            if (passwordInput) passwordInput.value = '123456';
        }

        showAdminToast('💡 Đã điền sẵn thông tin khách vãng lai, vui lòng kiểm tra và bấm lưu!', 'info');
    }

    function showAdminToast(msg, type = 'success') {
        let toast = document.getElementById('admin-chat-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'admin-chat-toast';
            Object.assign(toast.style, {
                position: 'fixed', bottom: '24px', right: '24px',
                padding: '10px 18px', borderRadius: '10px',
                fontWeight: '700', fontSize: '13.5px',
                zIndex: '999999', transition: 'all 0.3s ease',
                boxShadow: '0 4px 16px rgba(0,0,0,0.15)',
                display: 'flex', alignItems: 'center', gap: '8px',
                maxWidth: '340px'
            });
            document.body.appendChild(toast);
        }
        const colors = {
            success: { bg: '#dcfce7', color: '#166534', border: '#86efac' },
            error:   { bg: '#fef2f2', color: '#991b1b', border: '#fca5a5' },
            info:    { bg: '#dbeafe', color: '#1e40af', border: '#93c5fd' }
        };
        const c = colors[type] || colors.info;
        toast.style.background = c.bg;
        toast.style.color = c.color;
        toast.style.border = `1.5px solid ${c.border}`;
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
        toast.textContent = msg;
        clearTimeout(toast._timeout);
        toast._timeout = setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(8px)';
        }, 3200);
    }

    function toggleSendOrThumbsUp(val) {
        const btn = document.getElementById('ms-send-action-btn');
        if (!btn) return;
        if ((val || '').trim().length > 0) {
            btn.innerHTML = '➤';
            btn.title = 'Gửi tin nhắn phản hồi';
        } else {
            btn.innerHTML = '👍';
            btn.title = 'Gửi biểu tượng Thích';
        }
    }

    function handleSendActionClick() {
        const input = document.getElementById('ms-admin-reply-input');
        const text = (input ? input.value : '').trim();
        if (text.length > 0) {
            handleAdminSendReply();
        } else {
            sendThumbsUp();
        }
    }

    function sendThumbsUp() {
        submitAdminReply('👍');
    }

    function handleAdminSendReply() {
        const input = document.getElementById('ms-admin-reply-input');
        if (!input) return;
        const text = input.value.trim();
        if (!text) return;
        submitAdminReply(text);
        input.value = '';
        toggleSendOrThumbsUp('');
    }

    function submitAdminReply(text) {
        if (!currentChatMsgId) return;

        const replyContainer = document.getElementById('chat-admin-reply-container');
        const replyText = document.getElementById('chat-admin-reply-text');
        const replyMeta = document.getElementById('chat-admin-reply-meta');

        if (replyContainer && replyText) {
            replyText.innerText = text;
            if (replyMeta) replyMeta.innerText = '✓✓ Vừa gửi phản hồi';
            replyContainer.style.display = 'flex';
        }

        const stream = document.getElementById('chat-conversation-body');
        if (stream) stream.scrollTop = stream.scrollHeight;

        // Cập nhật thẻ hội thoại bên cột trái
        const activeCard = document.querySelector(`.ms-conv-item[data-id="${currentChatMsgId}"]`);
        if (activeCard) {
            activeCard.setAttribute('data-admin-reply', text);
            activeCard.setAttribute('data-status', 'replied');
            activeCard.classList.remove('is-unread');
            activeCard.classList.add('is-replied');

            const unreadDot = document.getElementById('unread-dot-' + currentChatMsgId);
            if (unreadDot) unreadDot.classList.add('is-hidden');

            const repliedTag = document.getElementById('replied-tag-' + currentChatMsgId);
            if (repliedTag) {
                repliedTag.classList.remove('is-hidden');
                repliedTag.innerText = 'Vừa xong';
            }

            const snippet = document.getElementById('snippet-' + currentChatMsgId);
            if (snippet) {
                snippet.innerHTML = `<span class="snippet-you"><b>Bạn:</b> ${escapeSupportHtml(text.substring(0, 18))}...</span>`;
            }
        }

        // Cập nhật Drawer status
        document.querySelectorAll('.drawer-status-chip').forEach(c => {
            c.classList.remove('active-pending', 'active-replied', 'active-closed');
        });
        document.getElementById('chip-status-replied')?.classList.add('active-replied');

        // Gửi AJAX lưu vào Database
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value || '';
        fetch(`/quan-tri/tin-nhan/${currentChatMsgId}/trang-thai`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `_token=${encodeURIComponent(csrfToken)}&_method=PATCH&status=replied&admin_reply=${encodeURIComponent(text)}`
        })
        .then(res => res.json())
        .then(data => {
            showAdminToast('✓ Đã lưu và gửi phản hồi thành công!', 'success');
            if (typeof pollAdminChat === 'function') {
                pollAdminChat();
            }
        })
        .catch(err => {
            console.log('Error saving reply:', err);
        });
    }

    function insertCannedReply(text) {
        const input = document.getElementById('ms-admin-reply-input');
        if (!input) return;
        input.value = text;
        toggleSendOrThumbsUp(text);
        input.focus();
    }

    function insertEmojiToComposer(emoji) {
        const input = document.getElementById('ms-admin-reply-input');
        if (!input) return;
        input.value += emoji;
        toggleSendOrThumbsUp(input.value);
        input.focus();
    }

    function handlePhotoUpload(input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];
        const reader = new FileReader();
        reader.onload = function(e) {
            const body = document.getElementById('chat-conversation-body');
            if (!body) return;
            const row = document.createElement('div');
            row.className = 'ms-message-row outgoing';
            row.style.display = 'flex';
            row.innerHTML = `<div>
                <img src="${e.target.result}" class="ms-bubble-img" onclick="window.open(this.src)" title="Click để xem ảnh">
                <div class="ms-bubble-meta">📷 Ảnh vừa gửi</div>
            </div>`;
            body.appendChild(row);
            body.scrollTop = body.scrollHeight;
            // Reset input
            input.value = '';
        };
        reader.readAsDataURL(file);
    }

    function updateCurrentChatStatus(newStatus) {
        if (!currentChatMsgId) return;

        document.querySelectorAll('.drawer-status-chip').forEach(c => {
            c.classList.remove('active-pending', 'active-replied', 'active-closed');
        });
        const targetChip = document.getElementById('chip-status-' + newStatus);
        if (targetChip) targetChip.classList.add('active-' + newStatus);

        const activeCard = document.querySelector(`.ms-conv-item[data-id="${currentChatMsgId}"]`);
        if (activeCard) {
            activeCard.setAttribute('data-status', newStatus);
            const unreadDot = document.getElementById('unread-dot-' + currentChatMsgId);
            const repliedTag = document.getElementById('replied-tag-' + currentChatMsgId);
            if (newStatus === 'replied' || newStatus === 'closed') {
                activeCard.classList.remove('is-unread');
                activeCard.classList.add('is-replied');
                if (unreadDot) unreadDot.classList.add('is-hidden');
                if (repliedTag) repliedTag.classList.remove('is-hidden');
            } else if (newStatus === 'pending') {
                activeCard.classList.add('is-unread');
                activeCard.classList.remove('is-replied');
                if (unreadDot) unreadDot.classList.remove('is-hidden');
                if (repliedTag) repliedTag.classList.add('is-hidden');
            }
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value || '';
        fetch(`/quan-tri/tin-nhan/${currentChatMsgId}/trang-thai`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `_token=${encodeURIComponent(csrfToken)}&_method=PATCH&status=${encodeURIComponent(newStatus)}`
        })
        .then(res => res.json())
        .then(data => {
            showAdminToast('✓ Đã cập nhật trạng thái tin nhắn!', 'success');
            if (typeof pollAdminChat === 'function') {
                pollAdminChat();
            }
        })
        .catch(err => console.log(err));
    }

    function filterByStatus(status, btn) {
        document.querySelectorAll('.ms-filter-chip').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');

        document.querySelectorAll('.ms-conv-item').forEach(card => {
            const cardStatus = card.getAttribute('data-status');
            if (status === 'all') {
                card.style.display = 'flex';
            } else if (status === 'pending') {
                card.style.display = cardStatus === 'pending' ? 'flex' : 'none';
            } else if (status === 'replied') {
                card.style.display = (cardStatus === 'replied' || cardStatus === 'closed') ? 'flex' : 'none';
            }
        });
    }

    function filterChatConversations(query) {
        const q = (query || '').toLowerCase().trim();
        document.querySelectorAll('.ms-conv-item').forEach(card => {
            const name = (card.getAttribute('data-name') || '').toLowerCase();
            const phone = (card.getAttribute('data-phone') || '').toLowerCase();
            const message = (card.getAttribute('data-message') || '').toLowerCase();

            if (!q || name.includes(q) || phone.includes(q) || message.includes(q)) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }

    function toggleChatDrawer() {
        const wrap = document.getElementById('ms-main-wrapper');
        const btn = document.getElementById('btn-toggle-drawer');
        if (!wrap) return;
        wrap.classList.toggle('drawer-collapsed');
        if (btn) btn.classList.toggle('active');
    }

    function focusChatComposer() {
        const input = document.getElementById('ms-admin-reply-input');
        if (input) input.focus();
    }

    // 🗑️ Xóa cuộc trò chuyện hiện tại
    function deleteCurrentChatConversation() {
        if (!currentChatMsgId) return;
        if (!confirm('Bạn có chắc chắn muốn xóa cuộc trò chuyện này không? Thao tác không thể hoàn tác.')) return;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value || '';
        fetch(`/quan-tri/tin-nhan/${currentChatMsgId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `_token=${encodeURIComponent(csrfToken)}&_method=DELETE`
        })
        .then(res => res.json())
        .then(data => {
            showAdminToast('✓ ' + (data.message || 'Đã xóa cuộc trò chuyện!'), 'success');
            const card = document.querySelector(`.ms-conv-item[data-id="${currentChatMsgId}"]`);
            if (card) card.remove();

            const nextCard = document.querySelector('.ms-conv-item');
            if (nextCard) {
                selectChatConversation(nextCard);
            } else {
                location.reload();
            }
        })
        .catch(err => {
            alert('Có lỗi xảy ra khi xóa cuộc trò chuyện!');
        });
    }

    // ⚡ REAL-TIME POLLING CHO ADMIN CHAT MESSENGER
    let adminChatPollingTimer = null;
    let lastPolledMsgId = 0;
    document.querySelectorAll('.ms-conv-item').forEach(card => {
        const cid = parseInt(card.getAttribute('data-id') || '0', 10);
        if (cid > lastPolledMsgId) lastPolledMsgId = cid;
    });
    if (!lastPolledMsgId) {
        lastPolledMsgId = parseInt('{{ $supportMessages->isNotEmpty() ? $supportMessages->max("id") : 0 }}') || 0;
    }

    function pollAdminChat() {
        if (document.hidden) return;
        fetch(`/quan-tri/tin-nhan/realtime-poll?last_id=${lastPolledMsgId}&active_id=${currentChatMsgId || 0}`)
            .then(res => res.json())
            .then(data => {
                if (!data.ok) return;

                if (typeof updateGlobalSidebarBadges === 'function') {
                    updateGlobalSidebarBadges(data);
                }

                // Cập nhật các bộ đếm số lượng tin chờ
                if (typeof data.pending_count !== 'undefined') {
                    const chipPending = document.getElementById('chip-filter-pending');
                    if (chipPending) chipPending.innerText = `Chờ (${data.pending_count})`;
                }
                if (typeof data.total_count !== 'undefined') {
                    const chipAll = document.getElementById('chip-filter-all');
                    if (chipAll) chipAll.innerText = `Tất cả (${data.total_count})`;
                }

                // Nếu có tin nhắn mới
                if (data.new_messages && data.new_messages.length > 0) {
                    const list = document.getElementById('chat-conversation-list');
                    let hasReallyNewMessage = false;

                    data.new_messages.forEach(msg => {
                        if (msg.id > lastPolledMsgId) lastPolledMsgId = msg.id;

                        let existingCard = document.querySelector(`.ms-conv-item[data-id="${msg.id}"]`);
                        if (!existingCard && list) {
                            hasReallyNewMessage = true;
                            const initials = (msg.name || 'KH').substring(0, 2).toUpperCase();
                            const uType = msg.user_type || 'guest';
                            const uLabel = msg.user_type_label || '🌐 Khách Vãng Lai';
                            const tagBadge = uType === 'teacher' 
                                ? '<span class="ms-user-tag tag-teacher">👨‍🏫 GV</span>' 
                                : (uType === 'student' ? '<span class="ms-user-tag tag-student">🎓 HS</span>' : '<span class="ms-user-tag tag-guest">🌐 Khách</span>');
                            let safeMsg = (msg.message || '').trim();
                            if (!safeMsg || safeMsg === 'undefined') safeMsg = 'Khách gửi yêu cầu tư vấn';
                            const snippetText = safeMsg.length > 20 ? safeMsg.substring(0, 20) + '...' : safeMsg;

                            const cardHtml = `
                                <div class="ms-conv-item is-unread"
                                     data-id="${msg.id}"
                                     data-name="${escapeSupportHtml(msg.name)}"
                                     data-phone="${escapeSupportHtml(msg.phone || '')}"
                                     data-email="${escapeSupportHtml(msg.email || '')}"
                                     data-message="${escapeSupportHtml(safeMsg)}"
                                     data-admin-reply=""
                                     data-replied-at=""
                                     data-status="${msg.status}"
                                     data-initials="${initials}"
                                     data-gradient="linear-gradient(135deg, #0084ff, #00c6ff)"
                                     data-user-type="${uType}"
                                     data-user-type-label="${escapeSupportHtml(uLabel)}"
                                     data-time="${msg.created_at || 'Vừa xong'}"
                                     onclick="selectChatConversation(this)">
                                    <div class="ms-item-avatar-wrap">
                                        <div class="ms-item-avatar" style="background: linear-gradient(135deg, #0084ff, #00c6ff);">${initials}</div>
                                        <span class="ms-online-badge"></span>
                                    </div>
                                    <div class="ms-item-center">
                                        <div class="ms-item-name" title="${escapeSupportHtml(msg.name)}">${escapeSupportHtml(msg.name)}</div>
                                        <div class="ms-item-snippet" id="snippet-${msg.id}">
                                            <span class="snippet-guest">${escapeSupportHtml(snippetText)}</span>
                                        </div>
                                    </div>
                                    <div class="ms-item-meta-right">
                                        <div class="ms-role-cell">
                                            ${tagBadge}
                                        </div>
                                        <div class="ms-status-cell">
                                            <span class="ms-pending-pulse-badge" id="unread-dot-${msg.id}" title="Chưa phản hồi">
                                                <span class="pulse-ring"></span>
                                                <span class="pulse-core"></span>
                                                <span>Chờ</span>
                                            </span>
                                            <span class="ms-replied-tag is-hidden" id="replied-tag-${msg.id}">
                                                Vừa xong
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            `;
                            list.insertAdjacentHTML('afterbegin', cardHtml);

                            if (!currentChatMsgId) {
                                const newEl = list.firstElementChild;
                                if (newEl) selectChatConversation(newEl);
                            }
                        }
                    });

                    // CHỈ phát chuông và hiện thông báo khi có tin nhắn thực sự mới
                    if (hasReallyNewMessage) {
                        playAdminChime();
                        showAdminToast(`🔔 Có tin nhắn tư vấn mới!`, 'info');
                    }
                }

                // Nếu cuộc trò chuyện đang mở có tin nhắn mới hoặc có cập nhật nội dung
                if (data.active_message && data.active_message.id == currentChatMsgId) {
                    const activeCard = document.querySelector(`.ms-conv-item[data-id="${currentChatMsgId}"]`);
                    if (activeCard) {
                        const oldMsg = activeCard.getAttribute('data-message') || '';
                        // CHỈ cập nhật và phát chuông nếu message là chuỗi hợp lệ và khác với tin nhắn cũ
                        if (typeof data.active_message.message === 'string' && data.active_message.message.trim().length > 0 && oldMsg !== data.active_message.message) {
                            activeCard.setAttribute('data-message', data.active_message.message);
                            selectChatConversation(activeCard);
                            playAdminChime();
                        }
                        if (data.active_message.status && activeCard.getAttribute('data-status') !== data.active_message.status) {
                            activeCard.setAttribute('data-status', data.active_message.status);
                        }
                        if (data.active_message.admin_reply && activeCard.getAttribute('data-admin-reply') !== data.active_message.admin_reply) {
                            activeCard.setAttribute('data-admin-reply', data.active_message.admin_reply);
                            activeCard.classList.remove('is-unread');
                            activeCard.classList.add('is-replied');
                            document.getElementById('unread-dot-' + currentChatMsgId)?.classList.add('is-hidden');
                            const repTag = document.getElementById('replied-tag-' + currentChatMsgId);
                            if (repTag) {
                                repTag.classList.remove('is-hidden');
                                repTag.innerText = 'Vừa xong';
                            }

                            const replyContainer = document.getElementById('chat-admin-reply-container');
                            const replyText = document.getElementById('chat-admin-reply-text');
                            const replyMeta = document.getElementById('chat-admin-reply-meta');
                            if (replyContainer && replyText) {
                                replyText.innerText = data.active_message.admin_reply;
                                if (replyMeta) replyMeta.innerText = '✓✓ Đã phản hồi ' + (data.active_message.replied_at || '');
                                replyContainer.style.display = 'flex';
                            }
                        }
                    }
                }
            })
            .catch(err => {});
    }

    function playAdminChime() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(587.33, ctx.currentTime);
            osc.frequency.setValueAtTime(880, ctx.currentTime + 0.1);
            gain.gain.setValueAtTime(0.2, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.4);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start(ctx.currentTime);
            osc.stop(ctx.currentTime + 0.4);
        } catch(e) {}
    }

    if (!adminChatPollingTimer) {
        adminChatPollingTimer = setInterval(pollAdminChat, 5000);
    }

    // =========================================================================
    // 🤖 TELEGRAM BOT (@trikun_cdphp_bot) MODAL HANDLERS
    // =========================================================================
    function openBotTeleModal() {
        const modal = document.getElementById('modal-telegram-config');
        if (modal) {
            modal.style.display = 'flex';
            const box = document.getElementById('tele-test-result-box');
            if (box) box.style.display = 'none';
        }
    }

    function closeBotTeleModal() {
        const modal = document.getElementById('modal-telegram-config');
        if (modal) modal.style.display = 'none';
    }

    async function pasteTokenFromClipboard() {
        try {
            const text = await navigator.clipboard.readText();
            if (text) {
                const input = document.getElementById('tele-config-token');
                if (input) {
                    input.value = text.trim();
                    showAdminToast('✓ Đã dán mã Token từ bộ nhớ tạm!', 'success');
                }
            }
        } catch (e) {
            showAdminToast('Vui lòng nhấn phím Ctrl+V để dán mã vào ô.', 'info');
        }
    }

    function testTelegramConnectionAction() {
        const token = (document.getElementById('tele-config-token')?.value || '').trim();
        const chatId = (document.getElementById('tele-config-chat-id')?.value || '').trim();
        const box = document.getElementById('tele-test-result-box');
        const btn = document.getElementById('btn-tele-test-conn');

        if (!token) {
            alert('Vui lòng nhập mã Bot Token lấy từ BotFather.');
            return;
        }

        if (btn) btn.disabled = true;
        if (box) {
            box.style.display = 'block';
            box.style.background = '#f1f5f9';
            box.style.color = '#334155';
            box.style.border = '1px solid #cbd5e1';
            box.innerHTML = '⏳ Đang kết nối máy chủ Telegram để kiểm tra Bot @trikun_cdphp_bot...';
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        fetch('/quan-tri/cai-dat-telegram', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                bot_token: token,
                admin_chat_id: chatId
            })
        })
        .then(res => res.json())
        .then(data => {
            if (btn) btn.disabled = false;
            if (data.ok) {
                box.style.background = '#ecfdf5';
                box.style.color = '#065f46';
                box.style.border = '1px solid #a7f3d0';
                box.innerHTML = `🎉 <b>KẾT NỐI THÀNH CÔNG!</b><br>Đã nhận diện Bot: <b>${data.bot?.first_name || ''}</b> (<code>@${data.bot?.username || 'trikun_cdphp_bot'}</code>). Cấu hình đã được lưu và sẵn sàng nhận thông báo!`;
                showAdminToast('✓ Kết nối Bot Telegram thành công!', 'success');
            } else {
                box.style.background = '#fef2f2';
                box.style.color = '#991b1b';
                box.style.border = '1px solid #fecaca';
                box.innerHTML = `❌ <b>Chưa kết nối được:</b> ${data.message || 'Mã Token chưa chính xác.'}<br><small style="margin-top:4px; display:block;">Vui lòng kiểm tra lại mã Token bạn đã copy từ BotFather.</small>`;
            }
        })
        .catch(err => {
            if (btn) btn.disabled = false;
            if (box) {
                box.style.background = '#fef2f2';
                box.style.color = '#991b1b';
                box.innerHTML = '❌ Lỗi kết nối máy chủ. Vui lòng kiểm tra lại đường truyền.';
            }
        });
    }

    function sendTelegramTestAlertAction() {
        const btn = document.getElementById('btn-tele-send-test');
        const box = document.getElementById('tele-test-result-box');
        if (btn) btn.disabled = true;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        fetch('/quan-tri/gui-thu-telegram', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                chat_id: (document.getElementById('tele-config-chat-id')?.value || '').trim()
            })
        })
        .then(res => res.json())
        .then(data => {
            if (btn) btn.disabled = false;
            if (box) {
                box.style.display = 'block';
                if (data.ok) {
                    box.style.background = '#ecfdf5';
                    box.style.color = '#065f46';
                    box.style.border = '1px solid #a7f3d0';
                    box.innerHTML = '🚀 <b>Đã gửi tin nhắn thử nghiệm thành công!</b> Vui lòng kiểm tra thông báo trên Telegram của bạn.';
                    showAdminToast('✓ Tin nhắn thử nghiệm đã được gửi tới Telegram!', 'success');
                } else {
                    box.style.background = '#fffbeb';
                    box.style.color = '#92400e';
                    box.style.border = '1px solid #fde68a';
                    box.innerHTML = `⚠️ <b>Thông báo:</b> ${data.message}`;
                }
            }
        })
        .catch(err => {
            if (btn) btn.disabled = false;
        });
    }

    function detectTelegramChatIdAction() {
        const btn = document.getElementById('btn-tele-detect-id');
        const box = document.getElementById('tele-test-result-box');
        const token = (document.getElementById('tele-config-token')?.value || '').trim();
        const inputChatId = document.getElementById('tele-config-chat-id');

        if (btn) btn.disabled = true;
        if (box) {
            box.style.display = 'block';
            box.style.background = '#f1f5f9';
            box.style.color = '#334155';
            box.style.border = '1px solid #cbd5e1';
            box.innerHTML = '🔍 Đang quét tin nhắn gửi tới bot @trikun_cdphp_bot...';
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        fetch('/quan-tri/telegram/lay-chat-id', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ bot_token: token })
        })
        .then(res => res.json())
        .then(data => {
            if (btn) btn.disabled = false;
            if (box) {
                if (data.ok) {
                    if (inputChatId) inputChatId.value = data.chat_id;
                    box.style.background = '#ecfdf5';
                    box.style.color = '#065f46';
                    box.style.border = '1px solid #a7f3d0';
                    box.innerHTML = `🎉 <b>${data.message}</b><br><small style="display:block; margin-top:4px;">Hệ thống đã tự động lưu Chat ID <code>${data.chat_id}</code> và gửi tin nhắn chào mừng tới Telegram của bạn!</small>`;
                    showAdminToast('✓ Đã tự động kết nối Chat ID Telegram!', 'success');
                } else if (data.empty) {
                    box.style.background = '#fffbeb';
                    box.style.color = '#92400e';
                    box.style.border = '1px solid #fde68a';
                    box.innerHTML = `⚠️ <b>${data.message}</b><br><div style="margin-top:6px;"><a href="https://t.me/trikun_cdphp_bot" target="_blank" style="color:#0084ff; font-weight:800; text-decoration:underline;">👉 Bấm vào đây để mở Bot và gửi tin nhắn ngay</a></div>`;
                } else {
                    box.style.background = '#fef2f2';
                    box.style.color = '#991b1b';
                    box.style.border = '1px solid #fecaca';
                    box.innerHTML = `❌ ${data.message}`;
                }
            }
        })
        .catch(err => {
            if (btn) btn.disabled = false;
            if (box) {
                box.style.background = '#fef2f2';
                box.style.color = '#991b1b';
                box.innerHTML = '❌ Lỗi kết nối máy chủ.';
            }
        });
    }

    function saveTelegramSettingsAction() {
        testTelegramConnectionAction();
    }

    // =========================================================================
    // 🔔 ÂM THANH CHUÔNG THÔNG BÁO REAL-TIME (WEB AUDIO API SYNTHESIZER)
    // =========================================================================
    function playNotificationChime() {
        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (!AudioContext) return;
            const ctx = new AudioContext();
            
            // Nốt thứ nhất (G5 - 783.99 Hz)
            const osc1 = ctx.createOscillator();
            const gain1 = ctx.createGain();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(783.99, ctx.currentTime);
            gain1.gain.setValueAtTime(0.12, ctx.currentTime);
            gain1.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.35);
            osc1.connect(gain1);
            gain1.connect(ctx.destination);
            osc1.start(ctx.currentTime);
            osc1.stop(ctx.currentTime + 0.35);

            // Nốt thứ hai cao hơn (C6 - 1046.50 Hz)
            const osc2 = ctx.createOscillator();
            const gain2 = ctx.createGain();
            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(1046.50, ctx.currentTime + 0.12);
            gain2.gain.setValueAtTime(0.15, ctx.currentTime + 0.12);
            gain2.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.55);
            osc2.connect(gain2);
            gain2.connect(ctx.destination);
            osc2.start(ctx.currentTime + 0.12);
            osc2.stop(ctx.currentTime + 0.55);
        } catch (e) {
            console.log('Audio chime error:', e);
        }
    }

    // =========================================================================
    // ⚡ REAL-TIME POLLING ENGINE (ĐỒNG BỘ TIN NHẮN TỰ ĐỘNG)
    // =========================================================================
    function pollRealtimeSupportChat() {
        if (typeof pollAdminChat === 'function') {
            pollAdminChat();
        }
    }

    function escapeSupportHtml(str) {
        if (!str) return '';
        return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    // ⚡ ĐIỀU HƯỚNG CHUYỂN TAB QUẢN TRỊ ĐỒNG BỘ TOÀN HỆ THỐNG
    function switchAdminTab(tabId, btn, filterRole = null) {
        let actualPaneId = tabId;
        let subViewToSwitch = null;

        if (tabId === 'tab-orders') {
            actualPaneId = 'tab-packages';
            subViewToSwitch = 'orders';
        } else if (tabId === 'tab-packages') {
            subViewToSwitch = 'list';
        } else if (tabId === 'tab-classes') {
            actualPaneId = 'tab-users';
        }

        // ⚡ Mở rộng 100% chiều rộng màn hình khi mở Tab Chat Messenger
        document.body.classList.toggle('tab-chat-active', actualPaneId === 'tab-chat');

        // ⚡ Tự động ẩn Banner Giáo viên toàn cục khi vào Tab Quản lý Bản quyền để tránh trùng lặp thông tin
        const teacherGlobalBanner = document.getElementById('teacher-global-banner');
        if (teacherGlobalBanner) {
            teacherGlobalBanner.style.display = (actualPaneId === 'tab-teacher-packages') ? 'none' : 'flex';
        }

        document.querySelectorAll('.admin-tab-pane').forEach(p => p.style.display = 'none');
        document.querySelectorAll('.main-tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.nav-sub-item, .nav a').forEach(a => a.classList.remove('on'));
        
        const target = document.getElementById(actualPaneId);
        if (target) {
            target.style.display = 'block';
        } else {
            const fallback = document.getElementById('tab-results');
            if (fallback) fallback.style.display = 'block';
        }
        
        const mainTabBtn = btn || document.getElementById('btn-' + tabId);
        if (mainTabBtn) mainTabBtn.classList.add('active');
        
        const navLink = document.querySelector(`.nav-sub-item[data-tab="${tabId}"], .nav a[data-tab="${tabId}"]`);
        if (navLink) {
            navLink.classList.add('on');
            const parentGroup = navLink.closest('.nav-group');
            if (parentGroup) parentGroup.classList.add('open');
        }

        if (subViewToSwitch && typeof switchPackageSubView === 'function') {
            switchPackageSubView(subViewToSwitch);
        }

        if (filterRole && typeof filterUserRole === 'function') {
            filterUserRole(filterRole, document.getElementById('filter-btn-' + filterRole));
        }

        // ⚡ Cập nhật Breadcrumbs & Tiêu đề TopBar đồng bộ với Tab đang mở
        if (typeof tabMeta !== 'undefined' && tabMeta[tabId]) {
            const titleEl = document.getElementById('topbar-title');
            const bcEl = document.getElementById('topbar-breadcrumb-tab');
            if (titleEl) titleEl.innerText = tabMeta[tabId].title;
            if (bcEl) bcEl.innerText = tabMeta[tabId].breadcrumb;
        }

        // 💾 Lưu tab đang chọn vào localStorage & sync URL hash để F5 không bị quay về trang đầu!
        try {
            localStorage.setItem('admin_active_tab', tabId);
            if (window.location.hash !== '#' + tabId) {
                history.replaceState(null, null, '#' + tabId);
            }
        } catch (e) {}
    }

    function toggleNavGroup(groupId) {
        const group = document.getElementById(groupId);
        if (group) {
            group.classList.toggle('open');
        }
    }

    // Gán trực tiếp ra window toàn cục để đảm bảo các thẻ HTML inline luôn gọi được 100%
    window.switchAdminTab = switchAdminTab;
    window.toggleNavGroup = toggleNavGroup;

    // Modal Helpers
    function openCreateUserModal() {
        const modal = document.getElementById('create-user-modal');
        const form = document.getElementById('create-user-form');
        if (form) {
            form.reset();
            const teacherSelect = document.getElementById('create-user-teacher-select');
            if (teacherSelect) teacherSelect.value = '';
            const createdByHidden = document.getElementById('create-user-created-by');
            if (createdByHidden) createdByHidden.value = '';
            toggleStudentClassSelect('student');
        }
        if (modal) modal.style.display = 'grid';
    }

    function closeCreateUserModal() {
        const modal = document.getElementById('create-user-modal');
        if (modal) modal.style.display = 'none';
    }

    function openCreateLevelModal() {
        const modal = document.getElementById('create-level-modal');
        if (modal) modal.style.display = 'grid';
    }

    function closeCreateLevelModal() {
        const modal = document.getElementById('create-level-modal');
        if (modal) modal.style.display = 'none';
    }

    function openCreateProgramModal() {
        const modal = document.getElementById('create-program-modal');
        if (modal) modal.style.display = 'grid';
    }

    function closeCreateProgramModal() {
        const modal = document.getElementById('create-program-modal');
        if (modal) modal.style.display = 'none';
    }

    // 🔍 REAL-TIME ADVANCED MULTI-CRITERIA FILTER
    function applyAdvancedResultsFilter() {
        const keyword = (document.getElementById('filter-keyword')?.value || '').toLowerCase().trim();
        const grade = document.getElementById('filter-grade')?.value || '';
        const teacher = document.getElementById('filter-teacher')?.value || '';
        const topic = document.getElementById('filter-topic')?.value || '';
        const status = document.getElementById('filter-status')?.value || '';

        const rows = document.querySelectorAll('.attempt-row-item');
        let visibleCount = 0;

        rows.forEach(row => {
            const rowSearch = row.getAttribute('data-search') || '';
            const rowGrade = row.getAttribute('data-grade') || '';
            const rowTeacher = row.getAttribute('data-teacher') || '';
            const rowTopic = row.getAttribute('data-topic') || '';
            const rowStatus = row.getAttribute('data-status') || '';

            const matchKeyword = !keyword || rowSearch.includes(keyword);
            const matchGrade = !grade || rowGrade === grade;
            const matchTeacher = !teacher || rowTeacher === teacher;
            const matchTopic = !topic || rowTopic === topic;
            const matchStatus = !status || rowStatus === status;

            if (matchKeyword && matchGrade && matchTeacher && matchTopic && matchStatus) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const counter = document.getElementById('filter-counter-badge');
        if (counter) {
            counter.innerText = `Hiển thị: ${visibleCount} / ${rows.length} lượt`;
        }
    }

    function resetResultsFilter() {
        if (document.getElementById('filter-keyword')) document.getElementById('filter-keyword').value = '';
        if (document.getElementById('filter-grade')) document.getElementById('filter-grade').value = '';
        if (document.getElementById('filter-teacher')) document.getElementById('filter-teacher').value = '';
        if (document.getElementById('filter-topic')) document.getElementById('filter-topic').value = '';
        if (document.getElementById('filter-status')) document.getElementById('filter-status').value = '';
        applyAdvancedResultsFilter();
    }

    // 📋 ATTEMPT DETAIL MODAL
    function openAttemptDetailModal(btn) {
        const modal = document.getElementById('attempt-detail-modal');
        if (!modal || !btn) return;

        const data = {
            student_name: btn.dataset.student || 'Học sinh',
            student_code: btn.dataset.code || '',
            classroom: btn.dataset.class || '',
            grade: btn.dataset.grade || '3',
            test_name: btn.dataset.test || 'Bài thi luyện',
            topic_name: btn.dataset.topic || '',
            score: parseInt(btn.dataset.score || '0'),
            answers: btn.dataset.answers || '0 / 0',
            duration: btn.dataset.duration || '00:00',
            completed_at: btn.dataset.date || '',
            is_passed: btn.dataset.passed === '1',
            is_perfect: btn.dataset.perfect === '1'
        };

        document.getElementById('dtl-student-name').innerText = data.student_name;
        document.getElementById('dtl-avatar').innerText = (data.student_name || 'H').substring(0, 1).toUpperCase();
        document.getElementById('dtl-student-code').innerText = data.student_code || 'Tự do';
        document.getElementById('dtl-student-class').innerText = data.classroom ? `${data.classroom} (K${data.grade})` : 'Tự do';
        document.getElementById('dtl-test-name').innerText = data.test_name;
        document.getElementById('dtl-topic-name').innerText = data.topic_name ? `📚 ${data.topic_name}` : 'Bộ đề IC3';
        document.getElementById('dtl-score-num').innerText = data.score;
        document.getElementById('dtl-score-num').style.color = data.is_passed ? '#15803d' : '#b91c1c';
        document.getElementById('dtl-answers-count').innerText = data.answers;
        document.getElementById('dtl-duration').innerText = `⏱️ ${data.duration}`;
        document.getElementById('dtl-completed-at').innerText = data.completed_at;

        // Status badge & verdict
        const statusBox = document.getElementById('dtl-status-badge');
        const verdictBox = document.getElementById('dtl-verdict-box');
        const verdictText = document.getElementById('dtl-verdict-text');

        if (data.is_perfect) {
            statusBox.innerHTML = '<span class="pill-badge pill-perfect" style="font-size:12px; padding:4px 12px;">👑 Xuất sắc (1000đ)</span>';
            verdictBox.style.background = '#fefce8';
            verdictBox.style.borderColor = '#fef08a';
            verdictBox.style.color = '#854d0e';
            verdictText.innerText = 'Học sinh đạt kết quả tuyệt đối, kiến thức chuẩn xác và vững vàng toàn diện theo chuẩn Certiport IC3 GS6!';
        } else if (data.is_passed) {
            statusBox.innerHTML = '<span class="pill-badge pill-pass" style="font-size:12px; padding:4px 12px;">✓ Đạt chuẩn IC3</span>';
            verdictBox.style.background = '#ecfdf5';
            verdictBox.style.borderColor = '#a7f3d0';
            verdictBox.style.color = '#065f46';
            verdictText.innerText = 'Học sinh đạt chuẩn đầu ra Certiport (≥ 700 điểm), nắm tốt các kỹ năng cốt lõi của chủ đề.';
        } else {
            statusBox.innerHTML = '<span class="pill-badge pill-fail" style="font-size:12px; padding:4px 12px;">✕ Cần cố gắng</span>';
            verdictBox.style.background = '#fef2f2';
            verdictBox.style.borderColor = '#fecaca';
            verdictBox.style.color = '#b91c1c';
            verdictText.innerText = 'Học sinh chưa đạt mốc 700 điểm. Giáo viên nên hướng dẫn học sinh làm lại để củng cố các dạng câu hỏi còn sai.';
        }

        modal.style.display = 'grid';
    }

    function closeAttemptDetailModal() {
        const modal = document.getElementById('attempt-detail-modal');
        if (modal) modal.style.display = 'none';
    }

    let currentRoleFilter = 'all';

    function filterUserRole(role, btn) {
        currentRoleFilter = role;
        document.querySelectorAll('.filter-tab-group .filter-tab-btn').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');
        applyUserFilters();
    }

    function filterUserSearch() {
        applyUserFilters();
    }

    function applyUserFilters() {
        const query = (document.getElementById('user-search-input')?.value || '').toLowerCase().trim();
        const rows = document.querySelectorAll('.user-row-item');
        rows.forEach(row => {
            const role = row.getAttribute('data-role');
            const text = row.innerText.toLowerCase();

            const matchesRole = (currentRoleFilter === 'all' || role === currentRoleFilter);
            const matchesQuery = !query || text.includes(query);

            row.style.display = (matchesRole && matchesQuery) ? '' : 'none';
        });
    }

    // 🍞 TOAST NOTIFICATION REALTIME HELPER
    function showToast(message, type = 'success') {
        const container = document.getElementById('toast-container');
        if (!container) return;
        const toast = document.createElement('div');
        toast.className = `toast-msg ${type === 'success' ? 'toast-success' : 'toast-error'}`;
        toast.innerHTML = `<span>${type === 'success' ? '✓' : '✕'}</span> <span>${message}</span>`;
        container.appendChild(toast);
        setTimeout(() => {
            toast.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(40px)';
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    }

    // 👥 QUẢN LÝ POPUP MODAL XEM HỌC SINH CỦA ĐẠI LÝ (REALTIME SPA)
    const teachersData = @json($teachers);
    let activeTeacherId = null;

    function openTeacherStudentsModal(teacherId) {
        activeTeacherId = teacherId;
        const teacher = teachersData.find(t => t.id == teacherId);
        if (!teacher) return;

        document.getElementById('ts-modal-teacher-name').innerText = teacher.name;
        document.getElementById('ts-modal-teacher-email').innerText = teacher.email;
        const maxStudents = teacher.max_students || 'Không giới hạn';
        const students = teacher.students || [];
        document.getElementById('ts-modal-quota').innerText = `${students.length} / ${maxStudents} HS`;
        if (document.getElementById('ts-modal-search')) {
            document.getElementById('ts-modal-search').value = '';
        }

        renderTeacherStudentsTable(students);

        const modal = document.getElementById('teacher-students-modal');
        if (modal) {
            modal.style.zIndex = '1000';
            modal.style.display = 'grid';
        }
    }

    // ⚠️ CUSTOM MODERN CONFIRM DIALOG
    function showConfirmDialog({ title, message, icon = '🗑️', iconBg = '#fee2e2', iconColor = '#dc2626', btnText = '✓ Xác nhận', btnBg = 'linear-gradient(135deg, #ef4444, #dc2626)', onConfirm }) {
        document.getElementById('confirm-dialog-title').innerText = title || 'Xác nhận';
        document.getElementById('confirm-dialog-message').innerText = message || '';
        const iconBox = document.getElementById('confirm-dialog-icon-box');
        if (iconBox) {
            iconBox.innerHTML = icon;
            iconBox.style.background = iconBg;
            iconBox.style.color = iconColor;
        }
        const btn = document.getElementById('confirm-dialog-submit-btn');
        if (btn) {
            btn.innerText = btnText;
            btn.style.background = btnBg;
            btn.onclick = () => {
                closeConfirmDialog();
                if (typeof onConfirm === 'function') onConfirm();
            };
        }
        const modal = document.getElementById('confirm-dialog-modal');
        if (modal) modal.style.display = 'grid';
    }

    function closeConfirmDialog() {
        const modal = document.getElementById('confirm-dialog-modal');
        if (modal) modal.style.display = 'none';
    }

    function renderTeacherStudentsTable(students) {
        const tbody = document.getElementById('ts-modal-tbody');
        const emptyBox = document.getElementById('ts-modal-empty');
        const countBadge = document.getElementById('ts-modal-count-badge');
        
        if (countBadge) countBadge.innerText = `${students ? students.length : 0} Học sinh`;
        if (!tbody) return;

        if (!students || students.length === 0) {
            tbody.innerHTML = '';
            if (emptyBox) emptyBox.style.display = 'block';
            return;
        }

        if (emptyBox) emptyBox.style.display = 'none';

        let html = '';
        students.forEach((s, idx) => {
            const levels = s.accessible_levels || [];
            let levelPills = levels.map(l => `<span class="pill-badge pill-grade" style="font-size:10.5px; padding:2px 7px; margin-right:3px;">Khối ${l.grade}</span>`).join('');
            if (!levelPills) levelPills = '<span style="font-size:11px; color:#ef4444; font-weight:700;">🔒 Chưa mở khối nào</span>';

            const attemptsCount = s.attempts_count ?? (s.attempts ? s.attempts.length : 0);
            const createdDate = s.created_at ? new Date(s.created_at).toLocaleDateString('vi-VN') : '—';
            const levelIdsJson = JSON.stringify(levels.map(l => l.id));
            const studentJson = JSON.stringify({
                id: s.id,
                name: s.name,
                email: s.email,
                student_code: s.student_code,
                role: 'student',
                status: s.status || 'active'
            });

            const escapedName = (s.name || '').replace(/'/g, "\\'");
            const isSuspended = (s.status === 'suspended');

            html += `
                <tr class="ts-modal-row" id="ts-row-${s.id}" data-search="${((s.name || '') + ' ' + (s.student_code || '') + ' ' + (s.email || '')).toLowerCase()}">
                    <td style="color:#94a3b8; font-weight:700; text-align:center;">${idx + 1}</td>
                    <td>
                        <div style="display:flex; align-items:center; gap:8px; min-width:0;">
                            <div style="width:32px; height:32px; border-radius:8px; display:grid; place-items:center; font-weight:900; font-size:12px; color:#fff; background:${isSuspended ? '#94a3b8' : 'linear-gradient(135deg, #6366f1, #8b5cf6)'}; flex-shrink:0;">
                                ${(s.name || 'H').substring(0, 1).toUpperCase()}
                            </div>
                            <div style="min-width:0; overflow:hidden;">
                                <div style="font-weight:800; color:${isSuspended ? '#64748b' : '#0f172a'}; font-size:13px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                    ${s.name} ${isSuspended ? '<span style="font-size:11px; color:#ef4444; font-weight:700;">(Đã khóa)</span>' : ''}
                                </div>
                                <div style="font-size:11.5px; color:#64748b; margin-top:2px;">
                                    ${s.email}
                                    ${s.student_code ? `· <span class="pill-badge pill-code" style="font-size:9.5px; padding:1px 4px;">${s.student_code}</span>` : ''}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td style="text-align:center;">
                        ${isSuspended 
                            ? `<button type="button" class="pill-badge pill-fail" style="cursor:pointer; font-size:11px; padding:3px 9px; border-radius:8px; border:1.5px solid #fca5a5;" onclick="toggleStudentStatusAjax(${s.id}, 'active', '${escapedName}')" title="Bấm để kích hoạt mở khóa lại cho học sinh">🔒 Tạm khóa</button>`
                            : `<button type="button" class="pill-badge pill-pass" style="cursor:pointer; font-size:11px; padding:3px 9px; border-radius:8px; border:1.5px solid #86efac;" onclick="toggleStudentStatusAjax(${s.id}, 'suspended', '${escapedName}')" title="Bấm để tạm khóa quyền đăng nhập/làm bài">🟢 Đang học</button>`
                        }
                    </td>
                    <td><div style="display:flex; gap:3px; flex-wrap:wrap;">${levelPills}</div></td>
                    <td><span class="pill-badge pill-time" style="font-size:11px; padding:2px 7px;">📝 ${attemptsCount} lượt thi</span></td>
                    <td><span style="color:#64748b; font-size:12px; font-weight:600;">📅 ${createdDate}</span></td>
                    <td style="text-align:right;">
                        <div class="action-btn-group" style="justify-content:flex-end; gap:5px; flex-wrap:nowrap;">
                            <button type="button" class="btn-action-edit" style="padding:4px 8px; font-size:11.5px;"
                                data-id="${s.id}"
                                data-name="${s.name}"
                                data-email="${s.email}"
                                data-code="${s.student_code || ''}"
                                data-role="student"
                                data-status="${s.status || 'active'}"
                                data-teacher-id="${s.created_by || activeTeacherId || ''}"
                                data-levels='${levelIdsJson}'
                                data-update-url="/quan-tri/users/${s.id}"
                                onclick="openEditUserModal(this)"
                                title="Sửa thông tin & Mật khẩu">
                                <span>✏️</span> Sửa
                            </button>
                            <button type="button" class="btn-action-grant" style="padding:4px 8px; font-size:11.5px;"
                                onclick='openGrantModal(${studentJson}, ${levelIdsJson})'
                                title="Cấp quyền Khối học">
                                <span>🔑</span> Khối
                            </button>
                            <button type="button" class="btn-action-delete" style="padding:4px 8px; font-size:11.5px; background:#fff1f2; color:#e11d48; border:1px solid #fecdd3; display:inline-flex; align-items:center; gap:3px;"
                                onclick="deleteStudentAjax(${s.id}, '${escapedName}')"
                                title="Xóa học sinh này">
                                <span>🗑️</span> Xóa
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
    }

    function filterTeacherModalStudents() {
        const q = (document.getElementById('ts-modal-search')?.value || '').toLowerCase().trim();
        const rows = document.querySelectorAll('.ts-modal-row');
        let visible = 0;
        rows.forEach(r => {
            const text = r.getAttribute('data-search') || '';
            if (!q || text.includes(q)) {
                r.style.display = '';
                visible++;
            } else {
                r.style.display = 'none';
            }
        });
        const badge = document.getElementById('ts-modal-count-badge');
        if (badge) badge.innerText = `Hiển thị: ${visible} / ${rows.length} HS`;
    }

    function closeTeacherStudentsModal() {
        activeTeacherId = null;
        const modal = document.getElementById('teacher-students-modal');
        if (modal) modal.style.display = 'none';
    }

    function openCreateStudentForTeacherModal() {
        if (!activeTeacherId) return;
        const teacher = teachersData.find(t => t.id == activeTeacherId);
        
        const modal = document.getElementById('create-user-modal');
        const form = document.getElementById('create-user-form');
        if (!modal || !form) return;

        form.reset();

        // Gán created_by
        const teacherSelect = document.getElementById('create-user-teacher-select');
        if (teacherSelect) teacherSelect.value = activeTeacherId;
        const createdByInput = document.getElementById('create-user-created-by');
        if (createdByInput) createdByInput.value = activeTeacherId;

        // Chọn vai trò student
        const roleSelect = document.getElementById('select-user-role');
        if (roleSelect) {
            roleSelect.value = 'student';
            toggleStudentClassSelect('student');
        }

        const statusSelect = document.getElementById('create-user-status');
        if (statusSelect) statusSelect.value = 'active';

        // Tự động tạo email gợi ý nếu cần
        const emailInput = form.querySelector('input[name="email"]');
        if (emailInput && !emailInput.value) {
            emailInput.value = `hs${Math.floor(1000 + Math.random() * 9000)}@student.ic3.local`;
        }

        modal.style.zIndex = '1100';
        modal.style.display = 'grid';
    }

    // ⚡ AJAX SUBMIT HANDLER CHO TẤT CẢ USER / GRANT FORMS
    function handleAjaxUserForm(e, form) {
        e.preventDefault();
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn ? submitBtn.innerText : '';
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerText = '⏳ Đang lưu...';
        }

        const formData = new FormData(form);

        fetch(form.action, {
            method: form.method || 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(async res => {
            const data = await res.json();
            if (!res.ok) {
                let errorMsg = data.message || 'Dữ liệu không hợp lệ.';
                if (data.errors) {
                    const firstErr = Object.values(data.errors)[0];
                    if (firstErr) errorMsg = Array.isArray(firstErr) ? firstErr[0] : firstErr;
                }
                throw new Error(errorMsg);
            }
            return data;
        })
        .then(data => {
            showToast(data.message || 'Thao tác thành công!', 'success');
            
            // Đóng sub-modal
            const parentModal = form.closest('.modal-backdrop');
            if (parentModal) parentModal.style.display = 'none';

            // Cập nhật Realtime danh sách học sinh nếu đang ở trong Popup của Đại lý
            if (activeTeacherId && data.user) {
                const u = data.user;
                const teacher = teachersData.find(t => t.id == activeTeacherId);
                if (teacher) {
                    if (!teacher.students) teacher.students = [];
                    const existingIdx = teacher.students.findIndex(s => s.id == u.id);
                    if (existingIdx >= 0) {
                        teacher.students[existingIdx] = { ...teacher.students[existingIdx], ...u };
                    } else {
                        teacher.students.unshift(u);
                    }
                    renderTeacherStudentsTable(teacher.students);
                    const maxStudents = teacher.max_students || 'Không giới hạn';
                    document.getElementById('ts-modal-quota').innerText = `${teacher.students.length} / ${maxStudents} HS`;
                }
            } else {
                // Nếu thao tác ngoài bảng chính: Tự động cập nhật mượt mà sau 500ms
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            }
        })
        .catch(err => {
            console.error(err);
            showToast(err.message || 'Có lỗi xảy ra, vui lòng thử lại.', 'error');
        })
        .finally(() => {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerText = originalText;
            }
        });
    }

    // 🔒 AJAX BẬT / TẮT KHÓA HỌC SINH REALTIME
    function toggleStudentStatusAjax(studentId, newStatus, studentName) {
        const isLocking = newStatus === 'suspended';
        showConfirmDialog({
            title: isLocking ? 'Tạm khóa tài khoản học sinh' : 'Kích hoạt mở khóa học sinh',
            message: isLocking 
                ? `Bạn có chắc muốn tạm khóa tài khoản của học sinh "${studentName}"? Học sinh này sẽ không thể đăng nhập làm bài thi cho đến khi được mở khóa lại.`
                : `Kích hoạt mở khóa cho học sinh "${studentName}" làm bài thi ngay bây giờ?`,
            icon: isLocking ? '🔒' : '🔓',
            iconBg: isLocking ? '#fffbeb' : '#ecfdf5',
            iconColor: isLocking ? '#b45309' : '#059669',
            btnText: isLocking ? '🔒 Tạm khóa ngay' : '🔓 Kích hoạt ngay',
            btnBg: isLocking ? 'linear-gradient(135deg, #f59e0b, #d97706)' : 'linear-gradient(135deg, #10b981, #059669)',
            onConfirm: () => {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                              document.querySelector('input[name="_token"]')?.value;

                const formData = new FormData();
                formData.append('_method', 'PUT');
                formData.append('_token', token);
                formData.append('status', newStatus);

                fetch(`/quan-tri/users/${studentId}`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(async res => {
                    const data = await res.json();
                    if (!res.ok) throw new Error(data.message || 'Không thể cập nhật trạng thái.');
                    return data;
                })
                .then(data => {
                    showToast(isLocking ? `🔒 Đã tạm khóa "${studentName}"` : `🟢 Đã kích hoạt "${studentName}"`, 'success');
                    
                    // 1. Cập nhật trong Popup Modal nếu đang mở
                    if (activeTeacherId) {
                        const teacher = teachersData.find(t => t.id == activeTeacherId);
                        if (teacher && teacher.students) {
                            const targetStudent = teacher.students.find(s => s.id == studentId);
                            if (targetStudent) {
                                targetStudent.status = newStatus;
                                renderTeacherStudentsTable(teacher.students);
                            }
                        }
                    }

                    // 2. Cập nhật trên Bảng chính (Main Table)
                    const mainRow = document.querySelector(`.user-row-item[data-user-id="${studentId}"]`) ||
                                    document.querySelector(`.user-row-item button[data-id="${studentId}"]`)?.closest('tr');
                    if (mainRow) {
                        if (isLocking) {
                            mainRow.classList.add('user-row-suspended');
                        } else {
                            mainRow.classList.remove('user-row-suspended');
                        }
                        const uRole = mainRow.getAttribute('data-role') || 'student';
                        const statusCell = mainRow.querySelector('.user-status-cell');
                        const escapedParam = studentName.replace(/'/g, "\\'");
                        if (statusCell) {
                            statusCell.innerHTML = isLocking
                                ? `<button type="button" class="pill-badge pill-fail" style="cursor:pointer; padding:3.5px 9px; font-size:11.5px; border-radius:8px; border:1.5px solid #fca5a5;" onclick="toggleStudentStatusAjax(${studentId}, 'active', '${escapedParam}')" title="Bấm để mở khóa kích hoạt lại tài khoản">🔒 Tạm khóa</button>`
                                : `<button type="button" class="pill-badge pill-pass" style="cursor:pointer; padding:3.5px 9px; font-size:11.5px; border-radius:8px; border:1.5px solid #86efac;" onclick="toggleStudentStatusAjax(${studentId}, 'suspended', '${escapedParam}')" title="Bấm để tạm khóa tài khoản này"><span class="status-dot-online"></span> ${uRole === 'teacher' ? 'Hoạt động' : 'Đang học'}</button>`;
                        }
                        const avatarWrap = mainRow.querySelector('.avatar-box-wrap');
                        if (avatarWrap) {
                            const badge = avatarWrap.querySelector('.avatar-online-badge, .avatar-suspended-badge');
                            if (badge) {
                                badge.className = isLocking ? 'avatar-suspended-badge' : 'avatar-online-badge';
                                badge.innerHTML = isLocking ? '🔒' : '';
                                badge.title = isLocking ? 'Tài khoản đang bị tạm khóa' : 'Tài khoản đang hoạt động / Online';
                            }
                        }
                        const nameEl = mainRow.querySelector('td b');
                        if (nameEl) {
                            nameEl.style.color = isLocking ? '#64748b' : '#0f172a';
                        }
                        const editBtn = mainRow.querySelector('.btn-action-edit');
                        if (editBtn) editBtn.setAttribute('data-status', newStatus);
                    }
                })
                .catch(err => {
                    console.error(err);
                    showToast(err.message || 'Có lỗi xảy ra khi đổi trạng thái.', 'error');
                });
            }
        });
    }

    // 🗑️ AJAX XÓA HỌC SINH REALTIME VỚI DIALOG SANG TRỌNG
    function deleteStudentAjax(studentId, studentName) {
        showConfirmDialog({
            title: 'Xác nhận xóa tài khoản',
            message: `Bạn có chắc chắn muốn xóa vĩnh viễn tài khoản "${studentName}" khỏi hệ thống không? Toàn bộ lịch sử làm bài thi luyện của người dùng này sẽ bị xóa.`,
            icon: '🗑️',
            iconBg: '#fee2e2',
            iconColor: '#dc2626',
            btnText: '🗑️ Xóa vĩnh viễn',
            btnBg: 'linear-gradient(135deg, #ef4444, #dc2626)',
            onConfirm: () => {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                              document.querySelector('input[name="_token"]')?.value;

                const formData = new FormData();
                formData.append('_method', 'DELETE');
                formData.append('_token', token);

                fetch(`/quan-tri/users/${studentId}`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(async res => {
                    const data = await res.json();
                    if (!res.ok) throw new Error(data.message || 'Không thể xóa tài khoản.');
                    return data;
                })
                .then(data => {
                    showToast(data.message || 'Đã xóa tài khoản thành công!', 'success');
                    if (activeTeacherId) {
                        const teacher = teachersData.find(t => t.id == activeTeacherId);
                        if (teacher && teacher.students) {
                            teacher.students = teacher.students.filter(s => s.id != studentId);
                            renderTeacherStudentsTable(teacher.students);
                            const maxStudents = teacher.max_students || 'Không giới hạn';
                            document.getElementById('ts-modal-quota').innerText = `${teacher.students.length} / ${maxStudents} HS`;
                        }
                    }
                    const mainRow = document.querySelector(`.user-row-item[data-user-id="${studentId}"]`) ||
                                    document.querySelector(`.user-row-item button[data-id="${studentId}"]`)?.closest('tr');
                    if (mainRow) {
                        mainRow.style.transition = 'all 0.3s ease';
                        mainRow.style.opacity = '0';
                        mainRow.style.transform = 'scale(0.95)';
                        setTimeout(() => mainRow.remove(), 300);
                    }
                })
                .catch(err => {
                    console.error(err);
                    showToast(err.message || 'Có lỗi xảy ra khi xóa tài khoản.', 'error');
                });
            }
        });
    }

    function toggleStudentClassSelect(role) {
        const codeGroup = document.getElementById('group-student-code');
        const levelsBox = document.getElementById('student-levels-box');
        if (levelsBox) levelsBox.style.display = (role === 'student') ? 'block' : 'none';
        if (codeGroup) codeGroup.style.display = (role === 'student') ? 'flex' : 'none';
    }

    function filterResultsByStudent(studentName) {
        switchAdminTab('tab-results', document.querySelector('[data-tab="tab-results"]'));
        const searchInput = document.getElementById('filter-keyword');
        if (searchInput) {
            searchInput.value = studentName;
            applyAdvancedResultsFilter();
        }
    }

    function openEditUserModal(btn, isSelf = false, customData = null) {
        const modal = document.getElementById('edit-user-modal');
        const form = document.getElementById('edit-user-form');
        if (!modal || !form) return;

        let d = {};
        if (isSelf) {
            d = {
                id: {{ auth()->id() }},
                name: "{{ addslashes(auth()->user()->name) }}",
                email: "{{ addslashes(auth()->user()->email) }}",
                code: "",
                role: "{{ is_object(auth()->user()->role) ? auth()->user()->role->value : auth()->user()->role }}",
                status: "{{ auth()->user()->status ?? 'active' }}",
                updateUrl: `/quan-tri/users/{{ auth()->id() }}`
            };
        } else if (customData) {
            d = customData;
        } else if (btn) {
            d = btn.dataset;
        }

        form.action = d.updateUrl || `/quan-tri/users/${d.id}`;
        document.getElementById('edit-user-name').value = d.name || '';
        document.getElementById('edit-user-email').value = d.email || '';
        document.getElementById('edit-user-student-code').value = d.studentCode || d.code || '';
        document.getElementById('edit-user-password').value = '';

        const roleSelect = document.getElementById('edit-user-role');
        const roleHint = document.getElementById('edit-user-role-hint');
        const statusSelect = document.getElementById('edit-user-status');
        const statusHint = document.getElementById('edit-user-status-hint');
        const titleEl = document.getElementById('edit-user-modal-title');
        const isCurrentTeacher = {{ $isTeacher ? 'true' : 'false' }};

        if (roleSelect) {
            roleSelect.value = d.role || 'student';
            if (isSelf) {
                // Tự sửa chính mình: Cố định vai trò, không cho phép tự sửa thành học sinh
                roleSelect.disabled = true;
                if (roleHint) roleHint.innerHTML = '<span style="color:#0284c7; font-weight:800;">🔒 Vai trò cố định (Không thể tự thay đổi)</span>';
            } else if (isCurrentTeacher) {
                // Giáo viên sửa học sinh: Cố định vai trò học sinh
                roleSelect.value = 'student';
                roleSelect.disabled = true;
                if (roleHint) roleHint.innerHTML = '<span style="color:#64748b; font-weight:700;">Giáo viên chỉ quản lý tài khoản Học sinh</span>';
            } else {
                // Quản trị viên sửa người dùng
                roleSelect.disabled = false;
                if (roleHint) roleHint.innerHTML = 'Phân quyền chức năng';
            }
        }

        if (statusSelect) {
            statusSelect.value = d.status || 'active';
            if (isSelf) {
                // Tự sửa chính mình: Cố định trạng thái hoạt động, không tự khóa mình
                statusSelect.disabled = true;
                if (statusHint) statusHint.innerHTML = '<span style="color:#059669; font-weight:800;">🟢 Tài khoản của bạn luôn ở trạng thái hoạt động</span>';
            } else {
                statusSelect.disabled = false;
                if (statusHint) statusHint.innerHTML = 'Khi bị khóa, học sinh sẽ không thể đăng nhập hoặc làm bài thi';
            }
        }

        if (titleEl) {
            titleEl.innerHTML = isSelf 
                ? '<span>✏️</span> Chỉnh sửa thông tin cá nhân & Đổi mật khẩu'
                : '<span>✏️</span> Chỉnh sửa tài khoản người dùng';
        }

        const teacherSelect = document.getElementById('edit-user-teacher-select');
        if (teacherSelect) {
            teacherSelect.value = d.teacherId || '';
        }

        // Ẩn/hiện các ô đặc thù của học sinh
        if (isSelf) {
            // Đang tự sửa chính mình (Giáo viên hoặc Admin): Ẩn toàn bộ ô dành riêng cho học sinh
            const codeGroup = document.getElementById('edit-group-student-code');
            const levelsBox = document.getElementById('edit-student-levels-box');
            const teacherGroup = document.getElementById('edit-group-select-teacher');
            if (codeGroup) codeGroup.style.display = 'none';
            if (levelsBox) levelsBox.style.display = 'none';
            if (teacherGroup) teacherGroup.style.display = 'none';
        } else {
            toggleEditStudentClassSelect(d.role || 'student');
        }

        let levelIds = [];
        try {
            levelIds = typeof d.levels === 'string' ? JSON.parse(d.levels) : (Array.isArray(d.levels) ? d.levels : []);
        } catch (e) {
            levelIds = [];
        }

        document.querySelectorAll('#edit-user-modal .edit-level-chk').forEach(cb => {
            cb.checked = levelIds.includes(parseInt(cb.value));
        });

        modal.style.zIndex = isSelf ? '12000' : '1000';
        modal.style.display = 'grid';
    }

    function openStudentProfileModal(el) {
        if (!el) return;
        const modal = document.getElementById('student-profile-modal');
        if (!modal) return;

        const id = el.dataset.id || '';
        const name = el.dataset.name || '';
        const email = el.dataset.email || '';
        const code = el.dataset.code || '';
        const status = el.dataset.status || 'active';
        const created = el.dataset.created || '';
        const attempts = el.dataset.attempts || '0';
        const teacher = el.dataset.teacher || 'Giáo viên phụ trách';
        let levels = [];
        try { levels = JSON.parse(el.dataset.levels || '[]'); } catch (e) {}
        let editBtnData = null;
        try { editBtnData = JSON.parse(el.dataset.editBtn || '{}'); } catch (e) {}

        const firstLetter = name ? name.trim().charAt(0).toUpperCase() : 'H';
        const isSuspended = (status === 'suspended');

        const avtEl = document.getElementById('sp-avatar');
        if (avtEl) {
            avtEl.innerText = firstLetter;
            avtEl.style.color = isSuspended ? '#64748b' : '#4f46e5';
        }

        const dotEl = document.getElementById('sp-status-dot');
        if (dotEl) {
            dotEl.style.background = isSuspended ? '#ef4444' : '#10b981';
        }

        const nameEl = document.getElementById('sp-name');
        if (nameEl) nameEl.innerText = name;

        const emailEl = document.getElementById('sp-email');
        if (emailEl) emailEl.innerText = email;

        const codeBadge = document.getElementById('sp-code-badge');
        if (codeBadge) {
            if (code) {
                codeBadge.style.display = 'inline-block';
                codeBadge.innerText = code;
            } else {
                codeBadge.style.display = 'none';
            }
        }

        const statusBadge = document.getElementById('sp-status-badge');
        if (statusBadge) {
            if (isSuspended) {
                statusBadge.style.background = '#fee2e2';
                statusBadge.style.color = '#b91c1c';
                statusBadge.innerText = '🔒 Tạm khóa';
            } else {
                statusBadge.style.background = '#dcfce7';
                statusBadge.style.color = '#15803d';
                statusBadge.innerText = '🟢 Đang học';
            }
        }

        const levelsEl = document.getElementById('sp-levels');
        if (levelsEl) {
            if (levels.length > 0) {
                levelsEl.innerHTML = levels.map(l => `<span class="pill-badge pill-grade" style="font-size:11px; padding:2px 7px;">Khối ${l.grade || l}</span>`).join(' ');
            } else {
                levelsEl.innerHTML = '<span style="font-size:11px; color:#ef4444; font-weight:700;">Chưa mở khối nào</span>';
            }
        }

        const attemptsEl = document.getElementById('sp-attempts');
        if (attemptsEl) attemptsEl.innerText = attempts + ' lượt thi';

        const teacherEl = document.getElementById('sp-teacher');
        if (teacherEl) teacherEl.innerText = teacher;

        const createdEl = document.getElementById('sp-created');
        if (createdEl) createdEl.innerText = created || 'Mới tham gia';

        const btnEdit = document.getElementById('sp-btn-edit');
        if (btnEdit) {
            btnEdit.onclick = function() {
                closeStudentProfileModal();
                const rowEditBtn = document.querySelector(`.user-row-item[data-user-id="${id}"] .btn-action-edit`);
                if (rowEditBtn && typeof openEditUserModal === 'function') {
                    openEditUserModal(rowEditBtn);
                }
            };
        }

        const btnGrant = document.getElementById('sp-btn-grant');
        if (btnGrant) {
            btnGrant.onclick = function() {
                closeStudentProfileModal();
                const rowGrantBtn = document.querySelector(`.user-row-item[data-user-id="${id}"] .btn-action-grant`);
                if (rowGrantBtn) {
                    rowGrantBtn.click();
                }
            };
        }

        modal.style.display = 'grid';
    }

    function closeStudentProfileModal() {
        const modal = document.getElementById('student-profile-modal');
        if (modal) modal.style.display = 'none';
    }

    function openTeacherProfileModal() {
        const modal = document.getElementById('teacher-profile-modal');
        if (modal) modal.style.display = 'grid';
    }

    function closeTeacherProfileModal() {
        const modal = document.getElementById('teacher-profile-modal');
        if (modal) modal.style.display = 'none';
    }

    function openMyProfileModal() {
        openTeacherProfileModal();
    }

    function closeEditUserModal() {
        const modal = document.getElementById('edit-user-modal');
        if (modal) modal.style.display = 'none';
    }

    function toggleStudentClassSelect(role) {
        const isStudent = (role === 'student');
        const levelsBox = document.getElementById('student-levels-box');
        const codeGroup = document.getElementById('group-student-code');
        const teacherGroup = document.getElementById('group-select-teacher');
        if (levelsBox) levelsBox.style.display = isStudent ? 'block' : 'none';
        if (codeGroup) codeGroup.style.display = isStudent ? 'block' : 'none';
        if (teacherGroup) teacherGroup.style.display = isStudent ? 'block' : 'none';
    }

    function toggleEditStudentClassSelect(role) {
        const isStudent = (role === 'student');
        const levelsBox = document.getElementById('edit-student-levels-box');
        const codeGroup = document.getElementById('edit-group-student-code');
        const teacherGroup = document.getElementById('edit-group-select-teacher');
        if (levelsBox) levelsBox.style.display = isStudent ? 'block' : 'none';
        if (codeGroup) codeGroup.style.display = isStudent ? 'block' : 'none';
        if (teacherGroup) teacherGroup.style.display = isStudent ? 'block' : 'none';
    }

    function autoFillLevelName(grade) {
        const nameInput = document.getElementById('create-level-name');
        if (nameInput) {
            const g = parseInt(grade) || 1;
            const levelNum = g <= 3 ? g : Math.min(g, 3);
            nameInput.value = `IC3 GS6 Spark Level ${levelNum} — Khối ${g}`;
        }
    }

    function openEditLevelModal(btn) {
        const modal = document.getElementById('edit-level-modal');
        const form = document.getElementById('edit-level-form');
        if (!modal || !form) return;

        const d = btn.dataset;
        form.action = d.updateUrl || `/quan-tri/levels/${d.id}`;
        document.getElementById('edit-level-name').value = d.name || '';
        document.getElementById('edit-level-grade').value = d.grade || '1';
        document.getElementById('edit-level-program').value = d.program || '';
        document.getElementById('edit-level-position').value = d.position || d.grade || '1';

        modal.style.display = 'grid';
    }

    function closeEditLevelModal() {
        const modal = document.getElementById('edit-level-modal');
        if (modal) modal.style.display = 'none';
    }

    function openGrantModal(user, levelIds) {
        const modal = document.getElementById('grant-level-modal');
        const form = document.getElementById('grant-level-form');
        if (!modal || !form) return;

        form.action = `/quan-tri/users/${user.id}`;
        document.getElementById('modal-user-name').value = user.name || '';
        document.getElementById('modal-user-email').value = user.email || '';
        document.getElementById('modal-user-code').value = user.student_code || '';
        document.getElementById('modal-user-role').value = user.role || 'student';
        document.getElementById('modal-user-class').value = user.classroom_id || '';
        document.getElementById('modal-display-student-name').innerText = user.name || '';

        // Reset and check assigned levels
        document.querySelectorAll('#grant-level-modal input[type="checkbox"]').forEach(cb => {
            cb.checked = levelIds.includes(parseInt(cb.value));
        });

        if (activeTeacherId) {
            modal.style.zIndex = '1100';
        } else {
            modal.style.zIndex = '1000';
        }
        modal.style.display = 'grid';
    }

    function closeGrantModal() {
        const modal = document.getElementById('grant-level-modal');
        if (modal) modal.style.display = 'none';
    }

    function openCreateProgramModal() {
        const modal = document.getElementById('create-program-modal');
        if (modal) modal.style.display = 'grid';
    }

    function closeCreateProgramModal() {
        const modal = document.getElementById('create-program-modal');
        if (modal) modal.style.display = 'none';
    }

    function syncProgramColor(val, mode) {
        if (!val) return;
        if (mode === 'create') {
            const picker = document.getElementById('create-program-color-picker');
            const txt = document.getElementById('create-program-accent');
            if (picker && val.startsWith('#') && val.length === 7) picker.value = val;
            if (txt) txt.value = val;
        } else {
            const picker = document.getElementById('edit-program-color-picker');
            const txt = document.getElementById('edit-program-accent');
            if (picker && val.startsWith('#') && val.length === 7) picker.value = val;
            if (txt) txt.value = val;
        }
    }

    function setProgramPreset(color, mode) {
        syncProgramColor(color, mode);
    }

    function openEditProgramModal(btn) {
        const modal = document.getElementById('edit-program-modal');
        const form = document.getElementById('edit-program-form');
        if (!modal || !form) return;

        const d = btn.dataset;
        form.action = d.updateUrl || `/quan-tri/programs/${d.id}`;
        document.getElementById('edit-program-name').value = d.name || '';
        document.getElementById('edit-program-slug').value = d.slug || '';
        const accent = d.accent || '#4f46e5';
        document.getElementById('edit-program-accent').value = accent;
        const picker = document.getElementById('edit-program-color-picker');
        if (picker && accent.startsWith('#') && accent.length === 7) picker.value = accent;
        document.getElementById('edit-program-desc').value = d.desc || '';

        modal.style.display = 'grid';
    }

    function closeEditProgramModal() {
        const modal = document.getElementById('edit-program-modal');
        if (modal) modal.style.display = 'none';
    }

    function openGrantTeacherModal(user, levelIds, maxStudents, expiresAt, status) {
        const modal = document.getElementById('grant-teacher-modal');
        const form = document.getElementById('grant-teacher-form');
        if (!modal || !form) return;

        form.action = `/quan-tri/users/${user.id}`;
        document.getElementById('teacher-modal-name').value = user.name || '';
        document.getElementById('teacher-modal-email').value = user.email || '';
        document.getElementById('teacher-modal-max-students').value = maxStudents || '';
        document.getElementById('teacher-modal-expires-at').value = expiresAt || '';
        document.getElementById('teacher-modal-status').value = status || 'active';
        document.getElementById('display-teacher-name').innerText = user.name || '';

        // Reset and check assigned teacher levels
        document.querySelectorAll('#grant-teacher-modal .teacher-lvl-chk').forEach(cb => {
            cb.checked = levelIds.includes(parseInt(cb.value));
        });

        modal.style.display = 'grid';
    }

    function closeGrantTeacherModal() {
        const modal = document.getElementById('grant-teacher-modal');
        if (modal) modal.style.display = 'none';
    }

    window.addEventListener('click', (e) => {
        const grantModal = document.getElementById('grant-level-modal');
        const grantTeacherModal = document.getElementById('grant-teacher-modal');
        const userModal = document.getElementById('create-user-modal');
        const editUserModal = document.getElementById('edit-user-modal');
        const classModal = document.getElementById('create-class-modal');
        const editClassModal = document.getElementById('edit-class-modal');
        const levelModal = document.getElementById('create-level-modal');
        const editLevelModal = document.getElementById('edit-level-modal');
        const programModal = document.getElementById('create-program-modal');
        const editProgramModal = document.getElementById('edit-program-modal');
        const detailModal = document.getElementById('attempt-detail-modal');
        const teacherStudentsModal = document.getElementById('teacher-students-modal');
        const createPkgModal = document.getElementById('create-package-modal');
        const editPkgModal = document.getElementById('edit-package-modal');
        const rejectOrderModal = document.getElementById('reject-order-modal');
        if (e.target === confirmModal) closeConfirmDialog();
        if (e.target === grantModal) closeGrantModal();
        if (e.target === grantTeacherModal) closeGrantTeacherModal();
        if (e.target === teacherStudentsModal) closeTeacherStudentsModal();
        if (e.target === userModal) closeCreateUserModal();
        if (e.target === editUserModal) closeEditUserModal();
        if (e.target === levelModal) closeCreateLevelModal();
        if (e.target === editLevelModal) closeEditLevelModal();
        if (e.target === programModal) closeCreateProgramModal();
        if (e.target === editProgramModal) closeEditProgramModal();
        if (e.target === detailModal) closeAttemptDetailModal();
        if (e.target === createPkgModal) closeCreatePackageModal();
        if (e.target === editPkgModal) closeEditPackageModal();
        if (e.target === rejectOrderModal) closeRejectOrderModal();
    });

    // 💎 GÓI DỊCH VỤ & ĐƠN THUÊ BẢN QUYỀN HELPERS
    function switchPackageSubView(view, btn) {
        document.querySelectorAll('.pkg-subview-pane').forEach(p => p.style.display = 'none');
        document.getElementById('btn-pkg-subview-list')?.classList.remove('active');
        document.getElementById('btn-pkg-subview-orders')?.classList.remove('active');

        if (view === 'list') {
            const listPane = document.getElementById('pkg-subview-list');
            if (listPane) listPane.style.display = 'block';
            document.getElementById('btn-pkg-subview-list')?.classList.add('active');
        } else {
            const ordersPane = document.getElementById('pkg-subview-orders');
            if (ordersPane) ordersPane.style.display = 'block';
            document.getElementById('btn-pkg-subview-orders')?.classList.add('active');
        }
    }

    function openCreatePackageModal() {
        const modal = document.getElementById('create-package-modal');
        if (modal) modal.style.display = 'grid';
    }

    function closeCreatePackageModal() {
        const modal = document.getElementById('create-package-modal');
        if (modal) modal.style.display = 'none';
    }

    function autoGenPackageSlug(text, targetId) {
        const slug = text.toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .replace(/đ/g, 'd')
            .replace(/[^a-z0-9\s-]/g, '')
            .trim()
            .replace(/\s+/g, '-');
        const target = document.getElementById(targetId);
        if (target && (!target.value || target.dataset.auto !== 'false')) {
            target.value = slug;
        }
    }

    function openEditPackageModal(pkg, levelIds) {
        const modal = document.getElementById('edit-package-modal');
        const form = document.getElementById('edit-package-form');
        if (!modal || !form) return;

        form.action = `/quan-tri/packages/${pkg.id}`;
        document.getElementById('edit-pkg-title-name').innerText = pkg.name;
        document.getElementById('edit-pkg-name').value = pkg.name || '';
        document.getElementById('edit-pkg-slug').value = pkg.slug || '';
        document.getElementById('edit-pkg-badge').value = pkg.badge || '';
        document.getElementById('edit-pkg-price').value = pkg.price || 0;
        document.getElementById('edit-pkg-original-price').value = pkg.original_price || '';
        document.getElementById('edit-pkg-duration').value = pkg.duration_days || 30;
        document.getElementById('edit-pkg-max-students').value = pkg.max_students || 35;
        document.getElementById('edit-pkg-position').value = pkg.position || 1;
        document.getElementById('edit-pkg-is-active').value = pkg.is_active ? '1' : '0';
        document.getElementById('edit-pkg-description').value = pkg.description || '';

        const features = Array.isArray(pkg.features) ? pkg.features.join("\n") : '';
        document.getElementById('edit-pkg-features-text').value = features;

        document.querySelectorAll('.edit-pkg-lvl-chk').forEach(chk => {
            chk.checked = Array.isArray(levelIds) && levelIds.includes(parseInt(chk.value));
        });

        modal.style.display = 'grid';
    }

    function closeEditPackageModal() {
        const modal = document.getElementById('edit-package-modal');
        if (modal) modal.style.display = 'none';
    }

    function openRejectOrderModal(orderId, orderCode, teacherName) {
        const modal = document.getElementById('reject-order-modal');
        const form = document.getElementById('reject-order-form');
        if (!modal || !form) return;

        form.action = `/quan-tri/orders/${orderId}/reject`;
        document.getElementById('reject-order-code').innerText = orderCode;
        document.getElementById('reject-order-teacher').innerText = teacherName;
        document.getElementById('reject-order-reason').value = '';

        modal.style.display = 'grid';
    }

    function closeRejectOrderModal() {
        const modal = document.getElementById('reject-order-modal');
        if (modal) modal.style.display = 'none';
    }

    function filterOrdersTable(statusOverride) {
        const searchInput = document.getElementById('order-search-input');
        const statusInput = document.getElementById('order-status-filter');

        if (statusOverride !== undefined) {
            if (statusInput) statusInput.value = statusOverride;
            // Highlight active filter pill
            document.querySelectorAll('.order-filter-pill-tab').forEach(tab => {
                if (tab.getAttribute('data-status') === statusOverride) {
                    tab.classList.add('active');
                } else {
                    tab.classList.remove('active');
                }
            });
        }

        const query = (searchInput?.value || '').toLowerCase().trim();
        const status = statusInput?.value || '';

        const rows = document.querySelectorAll('.order-row-item');
        let visibleCount = 0;

        rows.forEach(row => {
            const code = (row.getAttribute('data-code') || '').toLowerCase();
            const user = (row.getAttribute('data-user') || '').toLowerCase();
            const email = (row.getAttribute('data-email') || '').toLowerCase();
            const phone = (row.getAttribute('data-phone') || '').toLowerCase();
            const school = (row.getAttribute('data-school') || '').toLowerCase();
            const rowStatus = row.getAttribute('data-status') || '';

            const matchQuery = !query || code.includes(query) || user.includes(query) || email.includes(query) || phone.includes(query) || school.includes(query);
            const matchStatus = !status || rowStatus === status;

            if (matchQuery && matchStatus) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const emptyFilter = document.getElementById('orders-filter-empty');
        if (emptyFilter) {
            emptyFilter.style.display = (visibleCount === 0 && rows.length > 0) ? 'block' : 'none';
        }
    }

    // 🔄 SIDEBAR TOGGLE & LOCALSTORAGE PERSISTENCE
    function toggleAdminSidebar() {
        const shell = document.querySelector('.shell');
        if (!shell) return;
        shell.classList.toggle('sidebar-collapsed');
        const isCollapsed = shell.classList.contains('sidebar-collapsed');
        localStorage.setItem('admin_sidebar_collapsed', isCollapsed ? '1' : '0');
    }

    // Auto restore sidebar state
    if (localStorage.getItem('admin_sidebar_collapsed') === '1') {
        document.querySelector('.shell')?.classList.add('sidebar-collapsed');
    }

    // 🔄 TỰ ĐỘNG KHÔI PHỤC TAB ĐANG ĐỨNG KHI F5 HOẶC TRUY CẬP LẠI
    function restoreAdminActiveTab() {
        const hash = window.location.hash;
        const savedTab = localStorage.getItem('admin_active_tab');
        
        let tabToOpen = null;
        if (hash) {
            const cleanHash = hash.replace('#', '');
            if (cleanHash === 'tab_chat' || cleanHash === 'tab-chat') {
                tabToOpen = 'tab-chat';
            } else if (document.getElementById(cleanHash)) {
                tabToOpen = cleanHash;
            } else if (hash === '#lop-hoc' || hash === '#classes') {
                tabToOpen = 'tab-classes';
            } else if (hash === '#khoi-hoc' || hash === '#levels' || hash === '#cau-truc' || hash === '#programs') {
                tabToOpen = 'tab-levels';
            } else if (hash === '#nguoi-dung' || hash === '#hoc-sinh' || hash === '#dai-ly') {
                tabToOpen = 'tab-users';
            } else if (hash === '#ket-qua' || hash === '#bao-cao') {
                tabToOpen = 'tab-results';
            } else if (hash === '#goi-dich-vu' || hash === '#packages' || hash === '#tab-packages') {
                tabToOpen = 'tab-packages';
            } else if (hash === '#don-hang' || hash === '#orders' || hash === '#tab-orders') {
                tabToOpen = 'tab-orders';
            } else if (hash === '#lich-su-thue-goi' || hash === '#tab-teacher-packages' || hash === '#don-thue-goi' || hash === '#teacher-packages') {
                tabToOpen = 'tab-teacher-packages';
            } else if (hash === '#chat' || hash === '#tin-nhan' || hash === '#tab-chat' || hash === '#tab_chat' || hash === '#messenger') {
                tabToOpen = 'tab-chat';
            }
        }
        
        if (!tabToOpen && savedTab && (document.getElementById(savedTab) || savedTab === 'tab-orders')) {
            tabToOpen = savedTab;
        }

        if (tabToOpen) {
            switchAdminTab(tabToOpen, null);
        }
    }

    window.addEventListener('DOMContentLoaded', restoreAdminActiveTab);
    window.addEventListener('hashchange', restoreAdminActiveTab);
    restoreAdminActiveTab();
</script>

@if($isTeacher)
<!-- ========================================================= -->
<!-- 💬 FLOATING LIVE CHAT WIDGET DÀNH CHO GIÁO VIÊN TRÊN TRANG QUẢN TRỊ -->
<!-- ========================================================= -->
<style>
    /* Nút nổi tròn gọn gàng kích hoạt Chat ở góc phải dưới (chuẩn chatbot) */
    .teacher-chat-toggle-btn {
        position: fixed;
        bottom: 24px;
        right: 24px;
        z-index: 99999;
        width: 54px;
        height: 54px;
        border-radius: 50%;
        background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
        color: #ffffff;
        border: 3px solid #ffffff;
        box-shadow: 0 10px 25px rgba(2, 132, 199, 0.4), inset 0 -3px 0 rgba(0, 0, 0, 0.2);
        cursor: pointer;
        display: grid;
        place-items: center;
        font-size: 24px;
        transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
        outline: none;
        padding: 0;
    }
    .teacher-chat-toggle-btn:hover {
        transform: translateY(-3px) scale(1.08);
        box-shadow: 0 14px 30px rgba(2, 132, 199, 0.5), inset 0 -3px 0 rgba(0, 0, 0, 0.2);
        background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
    }
    .teacher-chat-toggle-btn:active {
        transform: translateY(1px) scale(0.95);
        box-shadow: 0 6px 14px rgba(2, 132, 199, 0.3);
    }
    .teacher-chat-toggle-btn .chat-pulse-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #22c55e;
        border: 2px solid #ffffff;
        position: absolute;
        top: -1px;
        right: -1px;
        animation: chatPulse 2s infinite;
    }
    @keyframes chatPulse {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(34, 197, 94, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
    }

    /* Khung Chat Gamified 3D Box */
    .teacher-chat-box {
        position: fixed;
        bottom: 84px;
        right: 24px;
        width: 380px;
        max-width: calc(100vw - 32px);
        height: 520px;
        max-height: calc(100vh - 120px);
        background: #ffffff;
        border-radius: 20px;
        border: 3px solid #e0f2fe;
        box-shadow: 0 20px 45px rgba(15, 23, 42, 0.18), 0 4px 12px rgba(2, 132, 199, 0.12);
        z-index: 99999;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        animation: chatSlideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        font-family: inherit;
    }
    @keyframes chatSlideUp {
        from { opacity: 0; transform: translateY(20px) scale(0.96); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    /* Header */
    .teacher-chat-header {
        background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
        color: #ffffff;
        padding: 14px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 2px solid rgba(255, 255, 255, 0.15);
    }
    .teacher-chat-admin-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .teacher-chat-admin-avatar {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        background: #ffffff;
        border: 2px solid #bae6fd;
        display: grid;
        place-items: center;
        font-size: 20px;
        position: relative;
    }
    .teacher-chat-admin-avatar .online-badge {
        position: absolute;
        bottom: -2px;
        right: -2px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #22c55e;
        border: 2px solid #ffffff;
    }
    .teacher-chat-close-btn {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.2);
        color: #ffffff;
        border: none;
        font-size: 13px;
        font-weight: 800;
        cursor: pointer;
        display: grid;
        place-items: center;
        transition: all 0.2s;
    }
    .teacher-chat-close-btn:hover {
        background: rgba(255, 255, 255, 0.35);
        transform: scale(1.05);
    }

    /* Badge Thông tin Giáo viên đã xác thực */
    .teacher-chat-auth-badge {
        background: #f8fafc;
        padding: 8px 14px;
        border-bottom: 1px solid #e2e8f0;
        font-size: 11.5px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }

    /* Khung nội dung tin nhắn */
    .teacher-chat-body {
        flex: 1;
        overflow-y: auto;
        padding: 14px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        background: rgba(240, 249, 255, 0.3);
    }
    .teacher-chat-msg {
        max-width: 85%;
        padding: 10px 14px;
        border-radius: 16px;
        font-size: 12.5px;
        line-height: 1.5;
        position: relative;
        word-break: break-word;
    }
    .teacher-chat-msg-bot {
        align-self: flex-start;
        background: #ffffff;
        color: #1e293b;
        border: 1.5px solid #e2e8f0;
        border-bottom-left-radius: 4px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
    }
    .teacher-chat-msg-admin {
        align-self: flex-start;
        background: #f0fdf4;
        color: #166534;
        border: 1.5px solid #bbf7d0;
        border-bottom-left-radius: 4px;
        box-shadow: 0 2px 6px rgba(34, 197, 94, 0.08);
    }
    .teacher-chat-msg-user {
        align-self: flex-end;
        background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
        color: #ffffff;
        border-bottom-right-radius: 4px;
        box-shadow: 0 4px 10px rgba(2, 132, 199, 0.25);
    }
    .teacher-chat-msg-time {
        font-size: 10px;
        opacity: 0.75;
        margin-top: 4px;
        text-align: right;
    }

    /* Gợi ý nhanh Quick Tags */
    .teacher-chat-quick-tags {
        padding: 6px 12px 2px;
        display: flex;
        gap: 6px;
        overflow-x: auto;
        white-space: nowrap;
        background: #ffffff;
        scrollbar-width: none;
    }
    .teacher-chat-quick-tags::-webkit-scrollbar { display: none; }
    .teacher-quick-tag {
        font-size: 11px;
        font-weight: 700;
        color: #0284c7;
        background: #f0f9ff;
        border: 1px solid #bae6fd;
        border-radius: 999px;
        padding: 4px 10px;
        cursor: pointer;
        transition: all 0.15s;
    }
    .teacher-quick-tag:hover {
        background: #0284c7;
        color: #ffffff;
        border-color: #0284c7;
    }

    /* Footer Nhập Tin Nhắn */
    .teacher-chat-footer {
        padding: 10px 12px;
        background: #ffffff;
        border-top: 1.5px solid #e2e8f0;
    }
    .teacher-chat-input-row {
        display: flex;
        gap: 8px;
        align-items: flex-end;
    }
    .teacher-chat-textarea {
        flex: 1;
        border: 1.5px solid #cbd5e1;
        border-radius: 12px;
        padding: 8px 12px;
        font-size: 12.5px;
        font-family: inherit;
        outline: none;
        resize: none;
        max-height: 80px;
        transition: border-color 0.2s;
        line-height: 1.4;
    }
    .teacher-chat-textarea:focus {
        border-color: #0284c7;
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
    }
    .teacher-chat-send-btn {
        padding: 9px 14px;
        background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
        color: #ffffff;
        border: none;
        border-radius: 12px;
        font-weight: 800;
        font-size: 12.5px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.2s;
        box-shadow: 0 4px 10px rgba(2, 132, 199, 0.25);
        flex-shrink: 0;
    }
    .teacher-chat-send-btn:hover {
        background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
        transform: translateY(-1px);
    }
    .teacher-chat-send-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
</style>

<div id="teacher-live-chat-wrapper">
    <!-- Nút Nổi Tròn Kích Hoạt Chatbot Gọn Gàng -->
    <button type="button" id="teacher-chat-toggle-btn" class="teacher-chat-toggle-btn" onclick="toggleTeacherLiveChat()" title="Chat trực tiếp với Ban Quản Trị">
        <span class="chat-pulse-dot"></span>
        <span>💬</span>
    </button>

    <!-- Cửa Sổ Live Chat -->
    <div id="teacher-chat-box" class="teacher-chat-box" style="display: none;">
        <!-- Header -->
        <div class="teacher-chat-header">
            <div class="teacher-chat-admin-info">
                <div class="teacher-chat-admin-avatar">
                    👑
                    <span class="online-badge"></span>
                </div>
                <div>
                    <div style="font-weight: 900; font-size: 13.5px; line-height: 1.2;">Hỗ Trợ Giáo Viên</div>
                    <div style="font-size: 10.5px; opacity: 0.9; display: flex; align-items: center; gap: 4px;">
                        <span>🟢 Ban Quản Trị trực tuyến</span>
                    </div>
                </div>
            </div>
            <button type="button" class="teacher-chat-close-btn" onclick="toggleTeacherLiveChat()" title="Thu nhỏ">✕</button>
        </div>

        <!-- Thông tin giáo viên đã xác thực -->
        <div class="teacher-chat-auth-badge">
            <div style="display: flex; align-items: center; gap: 6px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                <span style="font-size: 13px;">👨‍🏫</span>
                <strong style="color: #0f172a; font-size: 12px;">{{ auth()->user()->name }}</strong>
                <span style="background: #e0f2fe; color: #0284c7; padding: 1px 7px; border-radius: 999px; font-weight: 800; font-size: 10px;">Giáo viên</span>
            </div>
            <span style="color: #64748b; font-size: 11px; flex-shrink: 0;">{{ auth()->user()->phone ?? auth()->user()->email }}</span>
        </div>

        <!-- Khung danh sách tin nhắn -->
        <div class="teacher-chat-body" id="teacher-chat-messages-body">
            <div class="teacher-chat-msg teacher-chat-msg-bot">
                👋 Xin chào Thầy/Cô <b>{{ auth()->user()->name }}</b>! Ban Quản Trị luôn sẵn sàng hỗ trợ Thầy/Cô về việc cấp thêm số lượng học sinh, mở khối lớp hoặc giải đáp thắc mắc giảng dạy. Thầy/Cô hãy gửi yêu cầu bên dưới nhé!
            </div>
        </div>

        <!-- Quick Tags -->
        <div class="teacher-chat-quick-tags">
            <button type="button" class="teacher-quick-tag" onclick="insertTeacherQuickMsg('Kính gửi BQT, tôi muốn gửi yêu cầu cấp thêm số lượng học sinh cho lớp.')">➕ Cấp thêm sĩ số</button>
            <button type="button" class="teacher-quick-tag" onclick="insertTeacherQuickMsg('Kính gửi BQT, tôi muốn đăng ký mở thêm khối lớp mới.')">📚 Mở khối lớp</button>
            <button type="button" class="teacher-quick-tag" onclick="insertTeacherQuickMsg('Kính gửi BQT, nhờ hỗ trợ kiểm tra gói bản quyền của tôi.')">🔑 Kiểm tra gói</button>
        </div>

        <!-- Footer Gửi Tin Nhắn -->
        <form class="teacher-chat-footer" onsubmit="submitTeacherChat(event)">
            <div class="teacher-chat-input-row">
                <textarea id="teacher-chat-input" class="teacher-chat-textarea" rows="2" placeholder="Nhập yêu cầu hoặc câu hỏi gửi Admin..." required></textarea>
                <button type="submit" id="teacher-chat-send-btn" class="teacher-chat-send-btn" title="Gửi tin nhắn">
                    <span>🚀</span> Gửi
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // =====================================================================
    // 💬 LOGIC LIVE CHAT DÀNH CHO GIÁO VIÊN TRONG DASHBOARD (REAL-TIME CHAT)
    // =====================================================================
    let teacherChatActiveId = localStorage.getItem('mos_teacher_support_id') || null;
    let teacherRenderedReplies = new Set();
    let teacherPollInterval = null;

    function toggleTeacherLiveChat() {
        const box = document.getElementById('teacher-chat-box');
        if (!box) return;
        const isHidden = box.style.display === 'none';
        box.style.display = isHidden ? 'flex' : 'none';
        if (isHidden) {
            const input = document.getElementById('teacher-chat-input');
            if (input) setTimeout(() => input.focus(), 150);
            scrollTeacherChatToBottom();
            startTeacherChatPolling();
        } else {
            stopTeacherChatPolling();
        }
    }

    function openTeacherSupportChat(initialText = '') {
        const box = document.getElementById('teacher-chat-box');
        if (box) box.style.display = 'flex';
        const input = document.getElementById('teacher-chat-input');
        if (input) {
            if (initialText) {
                input.value = initialText;
            }
            setTimeout(() => input.focus(), 150);
        }
        scrollTeacherChatToBottom();
        startTeacherChatPolling();
    }

    function startTeacherChatPolling() {
        if (!teacherPollInterval && teacherChatActiveId) {
            // Polling siêu tốc 1.2s khi khung chat đang mở để đạt trải nghiệm Realtime
            teacherPollInterval = setInterval(pollTeacherSupportReply, 1200);
            pollTeacherSupportReply();
        }
    }

    function stopTeacherChatPolling() {
        if (teacherPollInterval) {
            clearInterval(teacherPollInterval);
            teacherPollInterval = null;
        }
    }

    function insertTeacherQuickMsg(text) {
        const input = document.getElementById('teacher-chat-input');
        if (input) {
            input.value = text;
            input.focus();
        }
    }

    function scrollTeacherChatToBottom() {
        const body = document.getElementById('teacher-chat-messages-body');
        if (body) {
            body.scrollTop = body.scrollHeight;
        }
    }

    function playTeacherNotificationSound() {
        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (!AudioContext) return;
            const ctx = new AudioContext();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, ctx.currentTime);
            gain.gain.setValueAtTime(0.2, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.35);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start(ctx.currentTime);
            osc.stop(ctx.currentTime + 0.35);
        } catch(e) {}
    }

    // Gửi tin nhắn siêu tốc (Optimistic UI - Hiện tức thì 0ms, không chờ server)
    async function submitTeacherChat(e) {
        if (e && e.preventDefault) e.preventDefault();
        const input = document.getElementById('teacher-chat-input');
        const message = (input ? input.value : '').trim();
        if (!message) return;

        // 1. Xóa trắng ô nhập và giữ focus ngay lập tức để giáo viên gõ tiếp câu sau
        input.value = '';
        input.focus();

        // 2. Hiển thị ngay lập tức lên màn hình (0ms - Không lag, không chờ đợi)
        const body = document.getElementById('teacher-chat-messages-body');
        const tempMsgId = 'tmsg_' + Date.now();
        const nowTime = new Date().toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });

        if (body) {
            const userMsgDiv = document.createElement('div');
            userMsgDiv.id = tempMsgId;
            userMsgDiv.className = 'teacher-chat-msg teacher-chat-msg-user';
            userMsgDiv.innerHTML = `<div>${escapeHtml(message)}</div><div class="teacher-chat-msg-time" style="display:flex;align-items:center;justify-content:flex-end;gap:4px;"><span>${nowTime}</span> <span class="teacher-msg-status" style="opacity:0.75;font-size:10px;">⏳ Đang gửi...</span></div>`;
            body.appendChild(userMsgDiv);
            scrollTeacherChatToBottom();
        }

        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

        // 3. Gửi ngầm tới server
        try {
            const res = await fetch('/ho-tro/gui-tin-nhan', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    name: '{{ addslashes(auth()->user()->name) }}',
                    contact: '{{ auth()->user()->phone ?? auth()->user()->email }}',
                    email: '{{ auth()->user()->email }}',
                    phone: '{{ auth()->user()->phone }}',
                    message: message,
                    parent_id: teacherChatActiveId || 0
                })
            });

            const data = await res.json();
            const statusEl = document.querySelector(`#${tempMsgId} .teacher-msg-status`);

            if (data.ok) {
                // Cập nhật trạng thái đã gửi thành công
                if (statusEl) {
                    statusEl.innerHTML = '✓ Đã gửi';
                    statusEl.style.opacity = '0.9';
                }

                if (data.message_id) {
                    teacherChatActiveId = data.message_id;
                    localStorage.setItem('mos_teacher_support_id', teacherChatActiveId);
                    startTeacherChatPolling();
                    // Kích hoạt check phản hồi ngay sau 400ms
                    setTimeout(pollTeacherSupportReply, 400);
                }
            } else {
                if (statusEl) {
                    statusEl.innerHTML = '⚠️ Lỗi';
                    statusEl.style.color = '#f87171';
                }
            }
        } catch (err) {
            const statusEl = document.querySelector(`#${tempMsgId} .teacher-msg-status`);
            if (statusEl) {
                statusEl.innerHTML = '⚠️ Lỗi mạng';
                statusEl.style.color = '#f87171';
            }
        }
    }

    // Polling nhận tin nhắn phản hồi từ Admin theo thời gian thực (Real-time)
    async function pollTeacherSupportReply() {
        if (!teacherChatActiveId) return;
        try {
            const res = await fetch(`/ho-tro/tin-nhan/kiem-tra?id=${teacherChatActiveId}&t=${Date.now()}`);
            if (!res.ok) return;
            const data = await res.json();

            if (data.ok && data.admin_reply) {
                // Tách các câu trả lời theo từng dòng để render thành từng bong bóng tin nhắn riêng
                const rawReplies = data.admin_reply.split('\n').map(s => s.trim()).filter(Boolean);
                let hasNewReply = false;

                const body = document.getElementById('teacher-chat-messages-body');
                if (body) {
                    rawReplies.forEach(replyLine => {
                        const replyKey = `${teacherChatActiveId}_${replyLine}`;
                        if (!teacherRenderedReplies.has(replyKey)) {
                            teacherRenderedReplies.add(replyKey);
                            hasNewReply = true;

                            const adminDiv = document.createElement('div');
                            adminDiv.className = 'teacher-chat-msg teacher-chat-msg-admin';
                            adminDiv.innerHTML = `<div><b>👑 Ban Quản Trị:</b> ${escapeHtml(replyLine)}</div><div class="teacher-chat-msg-time">${data.replied_at || 'Vừa xong'}</div>`;
                            body.appendChild(adminDiv);
                        }
                    });

                    if (hasNewReply) {
                        playTeacherNotificationSound();
                        scrollTeacherChatToBottom();

                        // Tự động mở khung chat nếu đang đóng để giáo viên đọc được ngay
                        const box = document.getElementById('teacher-chat-box');
                        if (box && box.style.display === 'none') {
                            box.style.display = 'flex';
                        }
                    }
                }
            }
        } catch (e) {}
    }

    function escapeHtml(str) {
        return str.replace(/[&<>"']/g, function(m) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[m];
        });
    }

    // Lắng nghe sự kiện gõ phím Enter để gửi nhanh (Shift + Enter để xuống dòng)
    document.addEventListener('DOMContentLoaded', function() {
        const chatInput = document.getElementById('teacher-chat-input');
        if (chatInput) {
            chatInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    submitTeacherChat(e);
                }
            });
        }

        // Tự động kích hoạt polling nếu đã có phiên chat
        if (teacherChatActiveId) {
            startTeacherChatPolling();
        }
    });
</script>
@endif

</body>
</html>
