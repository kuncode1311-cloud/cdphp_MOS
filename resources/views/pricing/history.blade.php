<!doctype html>
<html lang="vi">
<head>
    @include('partials.page-gate')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lịch Sử Thuê Gói Dịch Vụ — IC3 Quest</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Fredoka:wght@600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: radial-gradient(circle at 10% 0%, #dbeafe 0, transparent 40%), radial-gradient(circle at 95% 10%, #fce7f3 0, transparent 35%), #f1f5f9;
            color: #0f172a;
            line-height: 1.5;
            min-height: 100vh;
        }

        /* Thanh trên cùng */
        .hist-nav {
            position: sticky; top: 0; z-index: 100;
            background: linear-gradient(180deg, #1072ba 0%, #0d5c96 100%);
            border-bottom: 4px solid #48c3f7;
            box-shadow: inset 0 -4px 0 #073a61, 0 8px 20px rgba(6, 38, 68, 0.3);
            padding: 0 28px; height: 68px;
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
        }
        .nav-brand { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .brand-badge {
            width: 40px; height: 40px; border-radius: 12px;
            background: linear-gradient(135deg, #facc15, #f59e0b); color: #7c2d12;
            display: grid; place-items: center; font-weight: 900; font-size: 15px;
            border: 2.5px solid #fff; box-shadow: inset 0 -3px 0 rgba(0, 0, 0, 0.15);
        }
        .brand-name { font-family: 'Fredoka', sans-serif; font-size: 20px; color: #ffe658; text-shadow: 0 2px 0 #7e4200; }
        .nav-actions { display: flex; gap: 8px; }
        .nav-pill {
            display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px;
            border-radius: 999px; font-size: 13px; font-weight: 800; text-decoration: none;
            color: #fff; background: rgba(255, 255, 255, 0.14); border: 2px solid rgba(255, 255, 255, 0.35);
            transition: transform 0.15s, background 0.15s;
        }
        .nav-pill:hover { transform: translateY(-2px); background: rgba(255, 255, 255, 0.25); }

        .wrap { max-width: 1040px; margin: 30px auto 60px; padding: 0 18px; }

        /* Phần đầu trang */
        .hero {
            display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 55%, #db2777 100%);
            border: 3.5px solid #fff; border-radius: 26px; padding: 24px 28px; color: #fff;
            box-shadow: 0 16px 36px rgba(79, 70, 229, 0.28), inset 0 -6px 0 rgba(0, 0, 0, 0.15);
        }
        .hero h1 { font-family: 'Fredoka', sans-serif; font-size: 28px; line-height: 1.2; }
        .hero p { opacity: 0.9; font-size: 14px; margin-top: 4px; }
        .btn-3d {
            display: inline-flex; align-items: center; gap: 8px; padding: 12px 20px;
            border-radius: 16px; font-weight: 800; font-size: 14px; text-decoration: none; border: 0; cursor: pointer;
            transition: transform 0.12s, box-shadow 0.12s;
        }
        .btn-3d:hover { transform: translateY(-2px); }
        .btn-3d:active { transform: translateY(4px); box-shadow: none; }
        .btn-gold { background: linear-gradient(180deg, #fde047, #f59e0b); color: #7c2d12; box-shadow: 0 6px 0 #b45309; }
        .btn-blue { background: linear-gradient(180deg, #38bdf8, #2563eb); color: #fff; box-shadow: 0 5px 0 #1e40af; }

        /* 3 thẻ số liệu */
        .stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin: 20px 0 26px; }
        .stat {
            background: #fff; border: 3.5px solid #fff; border-radius: 22px; padding: 16px 18px;
            display: flex; align-items: center; gap: 14px;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08), inset 0 -5px 0 var(--c);
        }
        .stat-ic { width: 52px; height: 52px; border-radius: 16px; display: grid; place-items: center; font-size: 26px; background: var(--bg); flex-shrink: 0; }
        .stat small { display: block; font-size: 11.5px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.4px; }
        .stat b { font-size: 20px; font-weight: 800; color: #0f172a; }
        .stat span.sub { display: block; font-size: 12px; color: #64748b; font-weight: 600; }

        .section-title { font-size: 16px; font-weight: 800; color: #334155; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; }

        /* Thẻ đơn hàng */
        .orders { display: flex; flex-direction: column; gap: 14px; }
        .order {
            position: relative; background: #fff; border: 3px solid #fff; border-radius: 22px;
            padding: 18px 20px 18px 26px; overflow: hidden;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08), inset 0 -4px 0 rgba(0, 0, 0, 0.06);
            display: grid; grid-template-columns: 1.6fr 2fr auto; gap: 18px; align-items: center;
        }
        .order::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 8px; background: var(--c); }
        .order.is-active { --c: #10b981; }
        .order.is-pending { --c: #f59e0b; }
        .order.is-rejected { --c: #ef4444; }

        .o-code { font-family: Consolas, monospace; font-size: 13px; font-weight: 700; color: #475569; }
        .o-name { font-size: 17px; font-weight: 800; color: #0f172a; margin: 2px 0 4px; }
        .o-levels { font-size: 12px; color: #64748b; }

        .o-meta { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
        .meta { background: #f8fafc; border-radius: 14px; padding: 8px 12px; }
        .meta small { display: block; font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; }
        .meta b { font-size: 14px; color: #0f172a; }
        .meta b.price { color: #2563eb; }

        .o-side { display: flex; flex-direction: column; align-items: flex-end; gap: 10px; min-width: 150px; }
        .pill { display: inline-flex; align-items: center; gap: 5px; padding: 5px 12px; border-radius: 999px; font-size: 12.5px; font-weight: 800; white-space: nowrap; }
        .pill-active { background: #dcfce7; color: #15803d; }
        .pill-pending { background: #fef3c7; color: #b45309; }
        .pill-rejected { background: #fee2e2; color: #b91c1c; }
        .o-date { font-size: 12px; color: #64748b; font-weight: 600; }
        .o-note { font-size: 12px; color: #b91c1c; font-weight: 600; text-align: right; }

        .empty { background: #fff; border: 3.5px solid #fff; border-radius: 26px; padding: 46px 20px; text-align: center; box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08); }
        .empty .big { font-size: 54px; }
        .empty p { color: #475569; font-weight: 600; margin: 8px 0 18px; }

        .pager { margin-top: 18px; }

        @media (max-width: 820px) {
            .order { grid-template-columns: 1fr; gap: 12px; }
            .o-side { flex-direction: row; justify-content: space-between; align-items: center; min-width: 0; flex-wrap: wrap; }
            .o-note { text-align: left; }
        }
        @media (max-width: 640px) {
            .hist-nav { padding: 0 12px; height: 60px; }
            .brand-name { display: none; }
            .nav-pill { padding: 7px 11px; font-size: 12px; }
            .hero { padding: 20px; border-radius: 22px; }
            .hero h1 { font-size: 22px; }
            .stats { grid-template-columns: 1fr; gap: 10px; }
            .o-meta { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>
    @php
        $me = auth()->user();
        $isStudent = $me->isStudent();
        $summary = $stats['summary'];
    @endphp

    <nav class="hist-nav">
        <a href="{{ route('pricing.index') }}" class="nav-brand">
            <div class="brand-badge">IC3</div>
            <div class="brand-name">IC3 Quest</div>
        </a>
        <div class="nav-actions">
            <a href="{{ route('pricing.index') }}" class="nav-pill">💎 Bảng giá</a>
            <a href="{{ route('home') }}" class="nav-pill">🏠 Trang chủ</a>
        </div>
    </nav>

    <div class="wrap">
        <section class="hero">
            <div>
                <h1>📜 {{ $isStudent ? 'Lịch sử đơn mua gói của em' : 'Lịch sử đơn thuê gói' }}</h1>
                <p>Tài khoản: <b>{{ $me->name }}</b>{{ $me->email ? ' · ' . $me->email : '' }}</p>
            </div>
            <a href="{{ route('pricing.index') }}" class="btn-3d btn-gold">✨ {{ $isStudent ? 'Mua gói mới' : 'Thuê gói mới' }}</a>
        </section>

        <div class="stats">
            <div class="stat" style="--c:#10b981; --bg:#dcfce7;">
                <div class="stat-ic">💎</div>
                <div>
                    <small>Gói đang dùng</small>
                    <b>{{ $summary['package'] ?? 'Chưa có gói' }}</b>
                    @if($summary['expires_at'])
                        <span class="sub">Hạn dùng đến {{ \Illuminate\Support\Carbon::parse($summary['expires_at'])->format('d/m/Y') }}</span>
                    @endif
                </div>
            </div>
            <div class="stat" style="--c:#3b82f6; --bg:#dbeafe;">
                <div class="stat-ic">🧾</div>
                <div>
                    <small>Tổng số đơn</small>
                    <b>{{ $stats['total'] }} đơn</b>
                    <span class="sub">{{ $stats['active'] }} đơn đã kích hoạt</span>
                </div>
            </div>
            <div class="stat" style="--c:#f59e0b; --bg:#fef3c7;">
                <div class="stat-ic">💰</div>
                <div>
                    <small>Đã thanh toán</small>
                    <b>{{ number_format($stats['spent'], 0, ',', '.') }} đ</b>
                    <span class="sub">Chỉ tính đơn đã kích hoạt</span>
                </div>
            </div>
        </div>

        @if($orders->isEmpty())
            <div class="empty">
                <div class="big">🛒</div>
                <p>{{ $isStudent ? 'Em chưa có đơn mua gói nào.' : 'Thầy/Cô chưa có đơn thuê gói nào.' }}</p>
                <a href="{{ route('pricing.index') }}" class="btn-3d btn-blue">Khám phá các gói</a>
            </div>
        @else
            <div class="section-title">🗂️ Các đơn gần đây</div>
            <div class="orders">
                @foreach($orders as $o)
                    <div class="order {{ $o->isActive() ? 'is-active' : ($o->isPending() ? 'is-pending' : 'is-rejected') }}">
                        <div>
                            <div class="o-code">#{{ $o->code }}</div>
                            <div class="o-name">{{ $o->package_name }}</div>
                            @if($o->package?->levels_list_text)
                                <div class="o-levels">📚 {{ $o->package->levels_list_text }}</div>
                            @endif
                        </div>

                        <div class="o-meta">
                            <div class="meta"><small>Số tiền</small><b class="price">{{ $o->formatted_price }}</b></div>
                            <div class="meta"><small>Thời hạn</small><b>{{ $o->duration_days }} ngày</b></div>
                            <div class="meta"><small>Sĩ số</small><b>{{ $o->max_students > 0 ? $o->max_students . ' HS' : 'Không giới hạn' }}</b></div>
                        </div>

                        <div class="o-side">
                            @if($o->isActive())
                                <span class="pill pill-active">✅ Đã kích hoạt</span>
                            @elseif($o->isPending())
                                <span class="pill pill-pending">⏳ Chờ thanh toán</span>
                            @else
                                <span class="pill pill-rejected">✖ {{ $o->isExpiredByTimeout() ? 'Hết hạn thanh toán' : 'Đã hủy' }}</span>
                            @endif
                            <span class="o-date">🗓️ {{ $o->created_vn }}</span>
                            @if($o->isPending())
                                <a href="{{ route('pricing.order.checkout', $o) }}" class="btn-3d btn-blue" style="padding:9px 14px; font-size:13px;">📱 Thanh toán ngay</a>
                            @elseif($o->isRejected() && $o->isExpiredByTimeout())
                                <span class="o-note">Quá 10 phút chưa thanh toán</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="pager">{{ $orders->links() }}</div>
        @endif
    </div>
</body>
</html>
