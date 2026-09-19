<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản Trị Gói Dịch Vụ & Đơn Thuê Bản Quyền — IC3 Quest</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@600;700;800&family=Nunito:wght@600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
</head>
<body>

    <!-- Topbar -->
    <header class="admin-pkg-topbar">
        <div style="display: flex; align-items: center; gap: 14px;">
            <a href="{{ route('admin.dashboard') }}" style="display: grid; place-items: center; width: 40px; height: 40px; border-radius: 12px; background: #f1f5f9; color: #475569; text-decoration: none; font-size: 18px; font-weight: 900;" title="Quay lại Trung tâm Quản trị">
                ←
            </a>
            <div>
                <h1 style="font-size: 18px; font-weight: 900; margin: 0; color: #0f172a;">
                    💎 Quản Trị Gói Dịch Vụ & Bản Quyền IC3 GS6
                </h1>
                <small style="color: #64748b; font-weight: 700; font-size: 12px;">Thiết lập giá gói, phân quyền khối lớp và duyệt đơn thuê tự động</small>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 12px;">
            <a href="{{ route('pricing.index') }}" target="_blank" class="action-btn" style="background: #f1f5f9; color: #334155;">
                <span>🌐</span> Xem Trang Bảng Giá Khách
            </a>
            <button type="button" class="action-btn btn-primary-pkg" onclick="openPackageModal()">
                <span>+</span> Thêm Gói Mới
            </button>
        </div>
    </header>

    <div class="admin-pkg-container">
        <!-- Thông báo flash -->
        @if(session('ok'))
            <div style="background: #f0fdf4; border: 2px solid #86efac; border-radius: 16px; padding: 14px 18px; color: #15803d; font-weight: 800; margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
                <span>✓</span>
                <div>{{ session('ok') }}</div>
            </div>
        @endif

        @if(session('err'))
            <div style="background: #fef2f2; border: 2px solid #fca5a5; border-radius: 16px; padding: 14px 18px; color: #b91c1c; font-weight: 800; margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
                <span>⚠️</span>
                <div>{{ session('err') }}</div>
            </div>
        @endif

        <!-- 4 Thẻ Thống kê -->
        <div class="metrics-grid">
            <div class="metric-card">
                <i style="background: #eff6ff; color: #1d4ed8;">💎</i>
                <div>
                    <small style="display:block; font-size:11.5px; font-weight:850; color:#64748b; text-transform:uppercase;">TỔNG SỐ GÓI</small>
                    <b style="font-size:22px; font-weight:1000; color:#0f172a;">{{ $packages->count() }} Gói</b>
                </div>
            </div>
            <div class="metric-card">
                <i style="background: #fef3c7; color: #b45309;">⏳</i>
                <div>
                    <small style="display:block; font-size:11.5px; font-weight:850; color:#64748b; text-transform:uppercase;">ĐƠN CHỜ DUYỆT</small>
                    <b style="font-size:22px; font-weight:1000; color:#b45309;">{{ $pendingOrdersCount }} Đơn</b>
                </div>
            </div>
            <div class="metric-card">
                <i style="background: #dcfce7; color: #15803d;">✓</i>
                <div>
                    <small style="display:block; font-size:11.5px; font-weight:850; color:#64748b; text-transform:uppercase;">ĐƠN ĐÃ KÍCH HOẠT</small>
                    <b style="font-size:22px; font-weight:1000; color:#15803d;">{{ $activeOrdersCount }} Đơn</b>
                </div>
            </div>
            <div class="metric-card">
                <i style="background: #f3e8ff; color: #7e22ce;">💰</i>
                <div>
                    <small style="display:block; font-size:11.5px; font-weight:850; color:#64748b; text-transform:uppercase;">DOANH THU KÍCH HOẠT</small>
                    <b style="font-size:22px; font-weight:1000; color:#7e22ce; font-family:'Fredoka', cursive;">{{ number_format($totalRevenue, 0, ',', '.') }} đ</b>
                </div>
            </div>
        </div>

        <!-- Tab Điều Hướng -->
        <div class="tab-nav">
            <a href="{{ route('admin.packages.index', ['tab' => 'packages']) }}" class="tab-btn {{ $tab === 'packages' ? 'active' : '' }}">
                <span>📦</span> Danh Mục Gói Dịch Vụ <span class="badge-count">{{ $packages->count() }}</span>
            </a>
            <a href="{{ route('admin.packages.index', ['tab' => 'orders']) }}" class="tab-btn {{ $tab === 'orders' ? 'active' : '' }}">
                <span>📜</span> Đơn Thuê Gói & Duyệt Bản Quyền 
                @if($pendingOrdersCount > 0)
                    <span class="badge-count" style="background:#ef4444; color:#fff;">{{ $pendingOrdersCount }}</span>
                @else
                    <span class="badge-count">{{ $totalOrdersCount }}</span>
                @endif
            </a>
        </div>

        @if($tab === 'packages')
            <!-- TAB 1: DANH SÁCH GÓI DỊCH VỤ -->
            <div class="table-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px;">
                    <h3 style="margin:0; font-size:17px; font-weight:900; color:#0f172a;">Danh Sách Gói Bản Quyền Đang Cấu Hình</h3>
                    <button type="button" class="action-btn btn-primary-pkg" onclick="openPackageModal()">
                        <span>+</span> Thêm Gói Mới
                    </button>
                </div>

                <div style="overflow-x:auto;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Thứ tự</th>
                                <th>Tên gói</th>
                                <th>Giá bán</th>
                                <th>Thời hạn</th>
                                <th>Sĩ số HS</th>
                                <th>Khối được cấp</th>
                                <th>Đơn đặt</th>
                                <th>Trạng thái</th>
                                <th style="text-align:right;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($packages as $pkg)
                                <tr>
                                    <td><b>#{{ $pkg->sort_order }}</b></td>
                                    <td>
                                        <b style="color:#0f172a; font-size:14.5px;">{{ $pkg->name }}</b>
                                        @if($pkg->badge)
                                            <span style="background:#fef3c7; color:#b45309; font-size:11px; padding:2px 7px; border-radius:6px; font-weight:850; margin-left:6px;">
                                                {{ $pkg->badge }}
                                            </span>
                                        @endif
                                        <small style="display:block; color:#64748b; font-size:12px; margin-top:2px;">{{ Str::limit($pkg->description, 60) }}</small>
                                    </td>
                                    <td>
                                        <b style="color:#b45309; font-family:'Fredoka', cursive; font-size:15px;">{{ $pkg->formatted_price }}</b>
                                        @if($pkg->original_price)
                                            <small style="display:block; text-decoration:line-through; color:#94a3b8; font-size:11.5px;">{{ $pkg->formatted_original_price }}</small>
                                        @endif
                                    </td>
                                    <td><b>{{ $pkg->duration_text }}</b></td>
                                    <td>
                                        <span style="color:#0369a1; font-weight:850;">👥 {{ $pkg->max_students_text }}</span>
                                    </td>
                                    <td>
                                        <span style="background:#dcfce7; color:#15803d; padding:3px 8px; border-radius:8px; font-size:12px; font-weight:850;">
                                            {{ $pkg->levels_list_text }}
                                        </span>
                                    </td>
                                    <td><b>{{ $pkg->orders_count }} đơn</b></td>
                                    <td>
                                        <form method="POST" action="{{ route('admin.packages.toggle', $pkg) }}">
                                            @csrf
                                            <button type="submit" class="pkg-status-pill" style="border:none; cursor:pointer; background: {{ $pkg->is_active ? '#dcfce7' : '#f1f5f9' }}; color: {{ $pkg->is_active ? '#15803d' : '#64748b' }};" title="Bấm để chuyển trạng thái">
                                                {{ $pkg->is_active ? '● Mở bán' : '○ Tạm ẩn' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td style="text-align:right;">
                                        <div style="display:inline-flex; gap:6px;">
                                            <button type="button" class="action-btn" style="background:#eff6ff; color:#1d4ed8;" onclick='openEditPackageModal(@json($pkg), @json($pkg->levels->pluck("id")), @json($pkg->features))'>
                                                Sửa
                                            </button>
                                            <form method="POST" action="{{ route('admin.packages.destroy', $pkg) }}" onsubmit="return confirm('Bạn có chắc chắn muốn xóa gói này không?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="action-btn btn-danger-pkg">
                                                    Xóa
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" style="text-align:center; padding:40px; color:#64748b;">
                                        Chưa có gói dịch vụ nào. Hãy bấm <b>"+ Thêm Gói Mới"</b> để bắt đầu.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <!-- TAB 2: ĐƠN THUÊ GÓI CỦA GIÁO VIÊN -->
            <div class="table-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; flex-wrap:wrap; gap:12px;">
                    <h3 style="margin:0; font-size:17px; font-weight:900; color:#0f172a;">Lịch Sử Đơn Đăng Ký Thuê Gói</h3>
                    
                    <div style="display:flex; gap:8px;">
                        <a href="{{ route('admin.packages.index', ['tab' => 'orders']) }}" class="action-btn" style="background: {{ !request('status') ? '#1072ba' : '#f1f5f9' }}; color: {{ !request('status') ? '#fff' : '#475569' }};">
                            Tất cả ({{ $totalOrdersCount }})
                        </a>
                        <a href="{{ route('admin.packages.index', ['tab' => 'orders', 'status' => 'pending']) }}" class="action-btn" style="background: {{ request('status') === 'pending' ? '#f59e0b' : '#f1f5f9' }}; color: {{ request('status') === 'pending' ? '#fff' : '#475569' }};">
                            Chờ duyệt ({{ $pendingOrdersCount }})
                        </a>
                        <a href="{{ route('admin.packages.index', ['tab' => 'orders', 'status' => 'active']) }}" class="action-btn" style="background: {{ request('status') === 'active' ? '#10b981' : '#f1f5f9' }}; color: {{ request('status') === 'active' ? '#fff' : '#475569' }};">
                            Đã kích hoạt ({{ $activeOrdersCount }})
                        </a>
                    </div>
                </div>

                <div style="overflow-x:auto;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Mã đơn</th>
                                <th>Giáo viên đăng ký</th>
                                <th>Gói thuê</th>
                                <th>Số tiền</th>
                                <th>Thời hạn</th>
                                <th>Trạng thái</th>
                                <th>Ngày đặt</th>
                                <th>Ghi chú</th>
                                <th style="text-align:right;">Duyệt kích hoạt</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                                <tr>
                                    <td>
                                        <b style="color:#0284c7;">#{{ $order->code }}</b>
                                    </td>
                                    <td>
                                        <b style="color:#0f172a;">{{ $order->user?->name ?? 'N/A' }}</b>
                                        <small style="display:block; color:#64748b; font-size:11.5px;">{{ $order->user?->email }}</small>
                                    </td>
                                    <td>
                                        <b>{{ $order->package_name }}</b>
                                        <small style="display:block; color:#15803d; font-size:11.5px; font-weight:800;">
                                            👥 Max {{ $order->max_students ?: '∞' }} HS · {{ $order->package?->levels_list_text }}
                                        </small>
                                    </td>
                                    <td>
                                        <b style="color:#b45309; font-family:'Fredoka', cursive; font-size:15px;">{{ $order->formatted_price }}</b>
                                    </td>
                                    <td>
                                        <span>{{ $order->duration_days }} ngày</span>
                                    </td>
                                    <td>
                                        @if($order->isPending())
                                            <span style="background:#fef3c7; color:#b45309; padding:3px 9px; border-radius:99px; font-size:11.5px; font-weight:850; border:1px solid #fde68a;">
                                                ⏳ Chờ duyệt
                                            </span>
                                        @elseif($order->isActive())
                                            <span style="background:#dcfce7; color:#15803d; padding:3px 9px; border-radius:99px; font-size:11.5px; font-weight:850; border:1px solid #86efac;">
                                                ✓ Đã kích hoạt
                                            </span>
                                        @else
                                            <span style="background:#fee2e2; color:#b91c1c; padding:3px 9px; border-radius:99px; font-size:11.5px; font-weight:850; border:1px solid #fca5a5;">
                                                ✕ Từ chối
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span style="font-size:12px; color:#64748b;">{{ $order->created_vn }}</span>
                                    </td>
                                    <td>
                                        <small style="color:#475569; font-size:12px;">{{ $order->notes ?? '-' }}</small>
                                    </td>
                                    <td style="text-align:right;">
                                        @if($order->isPending())
                                            <div style="display:inline-flex; gap:6px;">
                                                <form method="POST" action="{{ route('admin.orders.activate', $order) }}" onsubmit="return confirm('Xác nhận kích hoạt đơn này? Hệ thống sẽ tự động cộng hạn và gán khối lớp cho Giáo viên {{ $order->user?->name }}.');">
                                                    @csrf
                                                    <button type="submit" class="action-btn btn-success-pkg" title="Kích hoạt tự động ngay lập tức">
                                                        <span>✓</span> Duyệt Kích Hoạt
                                                    </button>
                                                </form>

                                                <form method="POST" action="{{ route('admin.orders.reject', $order) }}" onsubmit="return confirm('Từ chối đơn hàng này?');">
                                                    @csrf
                                                    <button type="submit" class="action-btn btn-danger-pkg" title="Từ chối đơn hàng">
                                                        ✕
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <span style="font-size:12px; color:#94a3b8; font-weight:700;">
                                                {{ $order->activated_at ? 'Kích hoạt: ' . $order->activated_at->format('d/m/Y H:i') : 'Đã xử lý' }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" style="text-align:center; padding:40px; color:#64748b;">
                                        Không có đơn hàng nào trong danh sách.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div style="margin-top:20px;">
                    {{ $orders->links() }}
                </div>
            </div>
        @endif
    </div>

    <!-- Modal Thêm / Sửa Gói Dịch Vụ -->
    <div id="pkg-modal-backdrop" class="modal-backdrop" aria-hidden="true">
        <div class="modal-content-box">
            <button type="button" onclick="closePackageModal()" style="position:absolute; top:18px; right:18px; width:34px; height:34px; border-radius:50%; border:2px solid #cbd5e1; background:#f8fafc; font-size:16px; font-weight:900; cursor:pointer;">✕</button>

            <h3 id="modal-pkg-title" style="margin:0 0 18px; font-size:20px; font-weight:900; color:#0f172a;">
                Thêm Gói Bản Quyền Mới
            </h3>

            <form id="package-form" method="POST" action="{{ route('admin.packages.store') }}">
                @csrf
                <div id="method-container"></div>

                <div class="form-group">
                    <label>Tên gói dịch vụ (*):</label>
                    <input type="text" name="name" id="input-name" class="form-control-custom" placeholder="Ví dụ: Gói Lớp Học Tiêu Chuẩn" required>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                    <div class="form-group">
                        <label>Huy hiệu nổi bật (Badge):</label>
                        <input type="text" name="badge" id="input-badge" class="form-control-custom" placeholder="Ví dụ: Phổ biến nhất ⭐">
                    </div>
                    <div class="form-group">
                        <label>Thứ tự hiển thị:</label>
                        <input type="number" name="sort_order" id="input-sort-order" class="form-control-custom" value="1" min="0">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                    <div class="form-group">
                        <label>Giá bán thực tế (VNĐ) (*):</label>
                        <input type="number" name="price" id="input-price" class="form-control-custom" placeholder="990000" min="0" required>
                    </div>
                    <div class="form-group">
                        <label>Giá gốc trước giảm (VNĐ):</label>
                        <input type="number" name="original_price" id="input-original-price" class="form-control-custom" placeholder="1490000" min="0">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                    <div class="form-group">
                        <label>Thời hạn sử dụng (Số ngày) (*):</label>
                        <input type="number" name="duration_days" id="input-duration" class="form-control-custom" placeholder="90" min="1" required>
                    </div>
                    <div class="form-group">
                        <label>Sĩ số HS tối đa (0 = Không giới hạn):</label>
                        <input type="number" name="max_students" id="input-max-students" class="form-control-custom" placeholder="100" min="0" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Khối lớp được cấp quyền:</label>
                    <div style="display:flex; flex-wrap:wrap; gap:12px; background:#f8fafc; padding:12px; border-radius:12px; border:1px solid #e2e8f0;">
                        @foreach($levels as $lv)
                            <label style="display:flex; align-items:center; gap:6px; font-size:13.5px; font-weight:800; color:#1e293b; cursor:pointer;">
                                <input type="checkbox" name="level_ids[]" value="{{ $lv->id }}" class="pkg-level-checkbox" style="accent-color:#0284c7; width:17px; height:17px;">
                                <span>Khối {{ $lv->grade }} ({{ $lv->name }})</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="form-group">
                    <label>Mô tả ngắn:</label>
                    <textarea name="description" id="input-desc" rows="2" class="form-control-custom" placeholder="Mô tả tóm tắt giá trị của gói..."></textarea>
                </div>

                <div class="form-group">
                    <label>Danh sách tính năng đặc quyền (Mỗi tính năng 1 dòng):</label>
                    <textarea name="features_text" id="input-features" rows="4" class="form-control-custom" placeholder="Quản lý tối đa 100 học sinh&#10;Toàn bộ ngân hàng câu hỏi IC3 GS6&#10;Hệ thống tự động chấm điểm xếp loại"></textarea>
                </div>

                <div class="form-group" style="display:flex; align-items:center; gap:8px;">
                    <input type="checkbox" name="is_active" id="input-active" value="1" checked style="accent-color:#0284c7; width:18px; height:18px;">
                    <label for="input-active" style="margin:0; font-size:13.5px; font-weight:850; cursor:pointer;">
                        Mở bán hiển thị gói này ngay trên bảng giá
                    </label>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                    <button type="button" class="action-btn" style="background:#f1f5f9; color:#475569;" onclick="closePackageModal()">Hủy</button>
                    <button type="submit" class="action-btn btn-primary-pkg" id="modal-submit-btn">Lưu Gói Dịch Vụ</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openPackageModal() {
            document.getElementById('modal-pkg-title').innerText = 'Thêm Gói Bản Quyền Mới';
            document.getElementById('modal-submit-btn').innerText = 'Tạo Gói Mới';
            const form = document.getElementById('package-form');
            form.action = '{{ route("admin.packages.store") }}';
            document.getElementById('method-container').innerHTML = '';

            document.getElementById('input-name').value = '';
            document.getElementById('input-badge').value = '';
            document.getElementById('input-price').value = '';
            document.getElementById('input-original-price').value = '';
            document.getElementById('input-duration').value = '30';
            document.getElementById('input-max-students').value = '50';
            document.getElementById('input-sort-order').value = '1';
            document.getElementById('input-desc').value = '';
            document.getElementById('input-features').value = '';
            document.getElementById('input-active').checked = true;

            document.querySelectorAll('.pkg-level-checkbox').forEach(cb => cb.checked = false);

            document.getElementById('pkg-modal-backdrop').classList.add('open');
        }

        function openEditPackageModal(pkg, levelIds, features) {
            document.getElementById('modal-pkg-title').innerText = 'Cập Nhật Gói: ' + pkg.name;
            document.getElementById('modal-submit-btn').innerText = 'Lưu Thay Đổi';
            const form = document.getElementById('package-form');
            form.action = '/quan-tri/packages/' + pkg.id;
            document.getElementById('method-container').innerHTML = '<input type="hidden" name="_method" value="PUT">';

            document.getElementById('input-name').value = pkg.name || '';
            document.getElementById('input-badge').value = pkg.badge || '';
            document.getElementById('input-price').value = pkg.price || 0;
            document.getElementById('input-original-price').value = pkg.original_price || '';
            document.getElementById('input-duration').value = pkg.duration_days || 30;
            document.getElementById('input-max-students').value = pkg.max_students || 0;
            document.getElementById('input-sort-order').value = pkg.sort_order || 0;
            document.getElementById('input-desc').value = pkg.description || '';
            
            if (Array.isArray(features)) {
                document.getElementById('input-features').value = features.join('\n');
            } else {
                document.getElementById('input-features').value = '';
            }

            document.getElementById('input-active').checked = pkg.is_active;

            document.querySelectorAll('.pkg-level-checkbox').forEach(cb => {
                cb.checked = levelIds.includes(parseInt(cb.value));
            });

            document.getElementById('pkg-modal-backdrop').classList.add('open');
        }

        function closePackageModal() {
            document.getElementById('pkg-modal-backdrop').classList.remove('open');
        }

        document.getElementById('pkg-modal-backdrop').addEventListener('click', function(e) {
            if (e.target === this) {
                closePackageModal();
            }
        });
    </script>
</body>
</html>
