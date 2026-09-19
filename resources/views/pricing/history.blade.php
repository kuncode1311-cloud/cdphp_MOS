<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lịch Sử Thuê Gói Dịch Vụ — IC3 Quest</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,600&family=Fredoka:wght@600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #f8fafc;
            color: #202124;
            line-height: 1.5;
        }

        .pricing-nav {
            position: sticky;
            top: 0;
            z-index: 100;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid #dadce0;
            padding: 0 32px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: #202124;
        }

        .brand-logo-badge {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: linear-gradient(135deg, #1a73e8, #0d47a1);
            color: #ffffff;
            display: grid;
            place-items: center;
            font-size: 18px;
            font-weight: 900;
        }

        .history-wrapper {
            max-width: 1040px;
            margin: 35px auto 60px;
            padding: 0 20px;
        }

        .history-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .history-header h1 {
            font-size: 22px;
            font-weight: 800;
            color: #202124;
        }

        .history-card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #dadce0;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(60, 64, 67, 0.08);
        }

        table.history-table {
            width: 100%;
            border-collapse: collapse;
        }

        table.history-table th {
            text-align: left;
            padding: 10px 12px;
            font-size: 12px;
            font-weight: 700;
            color: #5f6368;
            text-transform: uppercase;
            border-bottom: 1px solid #dadce0;
        }

        table.history-table td {
            padding: 14px 12px;
            font-size: 13px;
            border-bottom: 1px solid #f1f3f4;
            vertical-align: middle;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 10px;
            border-radius: 999px;
            font-size: 11.5px;
            font-weight: 700;
        }

        .status-pending { background: #fef7e0; color: #b06000; border: 1px solid #fce8b2; }
        .status-active { background: #e6f4ea; color: #137333; border: 1px solid #ceead6; }
        .status-rejected { background: #fce8e6; color: #c5221f; border: 1px solid #fad2cf; }

        .btn-link {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            background: #e8f0fe;
            color: #1a73e8;
        }
        .btn-link:hover {
            background: #1a73e8;
            color: #ffffff;
        }
    </style>
</head>
<body>

    <nav class="pricing-nav">
        <a href="{{ route('pricing.index') }}" class="nav-brand">
            <div class="brand-logo-badge">IC3</div>
            <div style="font-size:16px; font-weight:800; color:#1a73e8;">IC3 QUEST</div>
        </a>

        <div style="display:flex; align-items:center; gap:12px;">
            <a href="{{ route('pricing.index') }}" style="font-size:13px; font-weight:700; color:#1a73e8; text-decoration:none;">
                ← Xem Bảng Giá
            </a>
            @if(auth()->user()?->canAccessAdmin())
                <a href="{{ route('admin.dashboard') }}" style="font-size:13px; font-weight:700; color:#5f6368; text-decoration:none;">
                    🏫 Quản Trị
                </a>
            @else
                <a href="{{ route('home') }}" style="font-size:13px; font-weight:700; color:#5f6368; text-decoration:none;">
                    🏠 Trang Chủ
                </a>
            @endif
        </div>
    </nav>

    <div class="history-wrapper">
        <div class="history-header">
            <div>
                <h1>Lịch Sử Đơn Thuê Gói Dịch Vụ</h1>
                <p style="color:#5f6368; font-size:13px; margin-top:2px;">Tài khoản: <b>{{ auth()->user()->name }} ({{ auth()->user()->email }})</b></p>
            </div>
            <a href="{{ route('pricing.index') }}" class="btn-link" style="padding:8px 16px; font-size:13px;">
                + Thuê Gói Mới
            </a>
        </div>

        <div class="history-card">
            @if($orders->isEmpty())
                <div style="text-align:center; padding:40px; color:#5f6368;">
                    <p style="font-size:14px; margin-bottom:12px;">Thầy/Cô chưa có đơn thuê gói nào.</p>
                    <a href="{{ route('pricing.index') }}" class="btn-link">Khám Phá Các Gói Bản Quyền</a>
                </div>
            @else
                <div style="overflow-x:auto;">
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th>Mã đơn</th>
                                <th>Tên gói</th>
                                <th>Số tiền</th>
                                <th>Thời hạn</th>
                                <th>Sĩ số</th>
                                <th>Trạng thái</th>
                                <th>Ngày đặt</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $o)
                                <tr>
                                    <td><b>#{{ $o->code }}</b></td>
                                    <td>
                                        <b style="color:#202124;">{{ $o->package_name }}</b>
                                        <small style="display:block; color:#5f6368; font-size:11px;">{{ $o->package?->levels_list_text }}</small>
                                    </td>
                                    <td><b style="color:#1a73e8;">{{ $o->formatted_price }}</b></td>
                                    <td>{{ $o->duration_days }} ngày</td>
                                    <td>{{ $o->max_students > 0 ? $o->max_students . ' HS' : 'Không giới hạn' }}</td>
                                    <td>
                                        @if($o->isPending())
                                            <span class="status-pill status-pending">⏳ Chờ duyệt</span>
                                        @elseif($o->isActive())
                                            <span class="status-pill status-active">✓ Đã kích hoạt</span>
                                        @else
                                            <span class="status-pill status-rejected">✕ Từ chối</span>
                                        @endif
                                    </td>
                                    <td><span style="color:#5f6368; font-size:12px;">{{ $o->created_vn }}</span></td>
                                    <td>
                                        @if($o->isPending())
                                            <a href="{{ route('pricing.order.checkout', $o) }}" class="btn-link">
                                                Mã QR
                                            </a>
                                        @else
                                            <span style="color:#9aa0a6; font-size:12px;">Hoàn tất</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div style="margin-top:16px;">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>
</body>
</html>
