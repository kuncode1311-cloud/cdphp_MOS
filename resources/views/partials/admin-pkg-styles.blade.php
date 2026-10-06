    <style>
        :root {
            --primary: #1072ba;
            --primary-dark: #094775;
            --bg-body: #f8fafc;
            --border-color: #e2e8f0;
        }
        body {
            font-family: 'Nunito', sans-serif;
            background: var(--bg-body);
            color: #1e293b;
            margin: 0;
            padding: 0;
        }
        .admin-pkg-topbar {
            background: #ffffff;
            border-bottom: 2px solid var(--border-color);
            padding: 14px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 99;
        }
        .admin-pkg-container {
            max-width: 1360px;
            margin: 0 auto;
            padding: 28px 20px 80px;
        }
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 26px;
        }
        .metric-card {
            background: #ffffff;
            border-radius: 20px;
            border: 2px solid var(--border-color);
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        }
        .metric-card i {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            font-size: 24px;
            font-style: normal;
        }
        .tab-nav {
            display: flex;
            gap: 10px;
            border-bottom: 2px solid var(--border-color);
            margin-bottom: 24px;
        }
        .tab-btn {
            padding: 12px 22px;
            font-size: 14.5px;
            font-weight: 850;
            color: #64748b;
            text-decoration: none;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.15s;
        }
        .tab-btn:hover {
            color: var(--primary);
        }
        .tab-btn.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }
        .badge-count {
            background: #f1f5f9;
            color: #475569;
            padding: 2px 8px;
            border-radius: 99px;
            font-size: 11.5px;
            font-weight: 900;
        }
        .tab-btn.active .badge-count {
            background: #e0f2fe;
            color: #0369a1;
        }
        .action-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 850;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: transform 0.15s;
        }
        .action-btn:hover {
            transform: translateY(-2px);
        }
        .btn-primary-pkg {
            background: linear-gradient(135deg, #1072ba, #0284c7);
            color: #ffffff;
            box-shadow: 0 4px 10px rgba(16, 114, 186, 0.25);
        }
        .btn-success-pkg {
            background: linear-gradient(135deg, #10b981, #059669);
            color: #ffffff;
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.25);
        }
        .btn-danger-pkg {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fca5a5;
        }
        .table-card {
            background: #ffffff;
            border-radius: 24px;
            border: 2px solid var(--border-color);
            padding: 24px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.03);
        }
        table.admin-table {
            width: 100%;
            border-collapse: collapse;
        }
        table.admin-table th {
            text-align: left;
            padding: 12px 14px;
            font-size: 12px;
            font-weight: 900;
            color: #64748b;
            text-transform: uppercase;
            border-bottom: 2px solid var(--border-color);
        }
        table.admin-table td {
            padding: 14px;
            font-size: 13.5px;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }
        .pkg-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 99px;
            font-size: 11.5px;
            font-weight: 850;
        }
        .modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(4px);
            z-index: 999999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .modal-backdrop.open {
            display: flex;
        }
        .modal-content-box {
            background: #ffffff;
            border-radius: 24px;
            width: min(640px, 100%);
            max-height: 90vh;
            overflow-y: auto;
            padding: 28px;
            position: relative;
        }
        .form-group {
            margin-bottom: 16px;
        }
        .form-group label {
            display: block;
            font-size: 12.5px;
            font-weight: 850;
            color: #334155;
            margin-bottom: 6px;
        }
        .form-control-custom {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid #cbd5e1;
            border-radius: 12px;
            font-size: 13.5px;
            font-family: inherit;
            box-sizing: border-box;
        }
    </style>
