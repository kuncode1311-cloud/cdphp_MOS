{{-- Style dùng chung của các trang Trợ lý AI trong khu quản trị (gói AI, huấn luyện AI giọng nói) --}}
<style>
    /* Khung chung: sidebar bên trái, nội dung bên phải */
    .shell { min-height: 100vh; display: grid; grid-template-columns: 270px minmax(0, 1fr); transition: grid-template-columns 0.22s cubic-bezier(0.4, 0, 0.2, 1); }
    .shell.sidebar-collapsed, html.admin-sidebar-collapsed-init .shell { grid-template-columns: 70px minmax(0, 1fr); }
    html.admin-sidebar-collapsed-init .shell { transition: none !important; }
    .main-workspace { display: flex; flex-direction: column; min-width: 0; min-height: 100vh; }
    .main-content { padding: 24px 32px 48px; flex: 1; }
    @media (max-width: 1100px) { .shell { grid-template-columns: 1fr; } .main-content { padding: 20px 16px 40px; } }

    /* Nền và thẻ: gọn, nhẹ, ít màu */
    body { background: #f1f5f9; }
    .ai-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 12px; margin-bottom: 18px; }
    .ai-stat { background: linear-gradient(135deg, #ffffff 0%, var(--tint) 100%); border: 3px solid var(--border); border-radius: 16px; padding: 11px 13px; position: relative; overflow: hidden; display: flex; align-items: center; gap: 12px; box-shadow: 0 12px 24px rgba(15, 23, 42, 0.10), inset 0 -5px 0 rgba(15, 23, 42, 0.08); }
    .ai-stat::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 5px; background: var(--accent); }
    .ai-stat-ico { width: 40px; height: 40px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; font-size: 18px; background: var(--grad); color: #fff; flex-shrink: 0; box-shadow: 0 5px 10px rgba(15, 23, 42, 0.15); }
    .ai-stat small { display: block; font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: .03em; }
    .ai-stat b { display: block; font-size: 18px; font-weight: 900; color: #0f172a; line-height: 1.2; margin-top: 2px; }
    .ai-stat em { display: block; font-style: normal; font-size: 11.5px; font-weight: 700; color: #64748b; margin-top: 1px; }
    .ai-stat.blue { --accent: #0ea5e9; --tint: #eff6ff; --border: #7dd3fc; --grad: linear-gradient(135deg, #0284c7, #22d3ee); }
    .ai-stat.violet { --accent: #a855f7; --tint: #faf5ff; --border: #c4b5fd; --grad: linear-gradient(135deg, #8b5cf6, #d946ef); }
    .ai-stat.amber { --accent: #f59e0b; --tint: #fff7ed; --border: #fcd34d; --grad: linear-gradient(135deg, #f97316, #facc15); }
    .ai-stat.green { --accent: #10b981; --tint: #ecfdf5; --border: #6ee7b7; --grad: linear-gradient(135deg, #059669, #34d399); }

    .ai-tabs { display: flex; gap: 6px; border-bottom: 1px solid #e2e8f0; margin-bottom: 16px; }
    .ai-tab { background: none; border: none; border-bottom: 2.5px solid transparent; margin-bottom: -1px; padding: 9px 14px; font-family: inherit; font-size: 13.5px; font-weight: 800; color: #64748b; cursor: pointer; display: inline-flex; align-items: center; gap: 7px; }
    .ai-tab:hover { color: #1e40af; }
    .ai-tab.active { color: #1e40af; border-bottom-color: #1e40af; }
    .ai-tab-count { background: #e2e8f0; color: #475569; border-radius: 999px; padding: 1px 8px; font-size: 11.5px; font-weight: 900; }
    .ai-tab.active .ai-tab-count { background: #dbeafe; color: #1e40af; }

    .ai-card { background: #fff; border: 3px solid #93c5fd; border-radius: 16px; padding: 18px 20px; margin-bottom: 16px; box-shadow: 0 12px 24px rgba(15, 23, 42, 0.08), inset 0 -5px 0 rgba(29, 78, 216, 0.08); }
    .ai-card h3 { margin: 0 0 3px; font-size: 16px; font-weight: 900; color: #0f172a; }
    .ai-card p.hint { margin: 0 0 14px; font-size: 13px; font-weight: 600; color: #64748b; }
    .ai-card table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
    .ai-card th { text-align: left; font-size: 11.5px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: .03em; padding: 9px 10px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
    .ai-card td { padding: 10px; border-bottom: 1px solid #f1f5f9; color: #1e293b; font-weight: 600; vertical-align: middle; }
    .ai-card tr:last-child td { border-bottom: none; }
    .ai-card input, .ai-card select { padding: 7px 9px; border: 1px solid #cbd5e1; border-radius: 8px; font-family: inherit; font-weight: 700; font-size: 13px; background: #fff; }
    .ai-card input:focus, .ai-card select:focus { outline: 2px solid #bfdbfe; border-color: #3b82f6; }

    .ai-pill { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 11.5px; font-weight: 800; white-space: nowrap; }
    .ai-pill-on { background: #dcfce7; color: #15803d; }
    .ai-pill-warn { background: #fef3c7; color: #b45309; }
    .ai-pill-off { background: #f1f5f9; color: #64748b; }
    .ai-pill-aud { background: #eff6ff; color: #1d4ed8; }
    .ai-pill-aud.teacher { background: #fff7ed; color: #c2410c; }

    .ai-btn { border: none; border-radius: 9px; padding: 7px 13px; font-family: inherit; font-size: 12.5px; font-weight: 800; cursor: pointer; color: #fff; }
    .ai-btn-blue { background: #1d4ed8; }
    .ai-btn-green { background: #059669; }
    .ai-btn-red { background: #dc2626; }
    .ai-btn-ghost { background: #f1f5f9; color: #334155; }
    .ai-btn:hover { filter: brightness(0.96); }

    .ai-filters { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; margin-bottom: 12px; }
    .ai-chip { border: 1px solid #cbd5e1; background: #fff; color: #334155; border-radius: 999px; padding: 6px 12px; font-size: 12.5px; font-weight: 800; text-decoration: none; }
    .ai-chip.on { background: #1e40af; border-color: #1e40af; color: #fff; }
    .ai-search { flex: 1; min-width: 220px; padding: 9px 12px !important; }
    .ai-filters { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; margin-bottom: 10px; }
    .ai-filter-label { font-size: 12px; font-weight: 800; color: #64748b; margin-right: 4px; min-width: 76px; }

    /* Thanh lọc lịch sử mua: khung nền xanh nhạt, ô nhập và ô chọn viền rõ */
    .ai-orders-bar { display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end; background: #eff6ff; border: 2px solid #bfdbfe; border-radius: 14px; padding: 14px; margin-bottom: 16px; }
    .ai-orders-bar .ai-field { display: flex; flex-direction: column; gap: 5px; min-width: 180px; }
    .ai-orders-bar .ai-field.grow { flex: 1 1 280px; }
    .ai-orders-bar label { font-size: 11px; font-weight: 900; color: #1e40af; text-transform: uppercase; letter-spacing: .04em; }
    .ai-orders-bar input[type=text], .ai-orders-bar select { padding: 10px 12px; border: 2px solid #93c5fd; border-radius: 11px; background: #fff; font-family: inherit; font-weight: 700; font-size: 13.5px; color: #0f172a; width: 100%; box-sizing: border-box; }
    .ai-orders-bar input[type=text]:focus, .ai-orders-bar select:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px #bfdbfe; }
    .ai-orders-bar select { cursor: pointer; }
    .ai-orders-submit { padding: 11px 18px; white-space: nowrap; }

    .ai-form { display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end; }
    .ai-field { display: flex; flex-direction: column; gap: 4px; }
    .ai-field label { font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; }
    .ai-check { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 700; color: #334155; }
    .ai-check input { width: 16px; height: 16px; accent-color: #1d4ed8; }
    .ai-inline { display: inline-flex; gap: 6px; align-items: center; margin: 0; }
    .ai-inline input { width: 64px; }
    .ai-remain { display: block; font-size: 11.5px; font-weight: 700; color: #64748b; }
    .ai-row-warn td { background: #fffbeb; }
    .ai-empty { text-align: center; color: #64748b; font-weight: 700; padding: 22px; font-size: 13.5px; }
    .ai-flash { background: #f0fdf4; border: 1px solid #86efac; border-radius: 12px; padding: 11px 14px; color: #15803d; font-weight: 800; margin-bottom: 16px; font-size: 13.5px; }
    .ai-flash-err { background: #fef2f2; border-color: #fca5a5; color: #b91c1c; }
</style>
