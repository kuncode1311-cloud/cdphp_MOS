<!doctype html>
<html lang="vi">
<head>
    @include('partials.page-gate')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản Trị Trợ Lý AI — IC3 Quest</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@600;700;800&family=Nunito:wght@600;700;800;900&display=swap" rel="stylesheet">
    <script>
        if (localStorage.getItem('admin_sidebar_collapsed') === '1') {
            document.documentElement.classList.add('admin-sidebar-collapsed-init');
        }
    </script>
    {{-- Bộ style dùng chung của khu Gói & Bản quyền (thẻ, bảng, nút) --}}
    @include('partials.admin-pkg-styles')
    @include('partials.admin-ai-styles')
    <style>
        /* Cấp Trợ lý AI: danh sách gợi ý tài khoản + thẻ xác nhận đúng người */
        .ai-grant-field { position: relative; }
        .ai-grant-list { position: absolute; top: 100%; left: 0; right: 0; z-index: 30; margin-top: 4px; background: #fff; border: 2px solid #93c5fd; border-radius: 12px; box-shadow: 0 14px 30px rgba(15, 23, 42, 0.16); max-height: 320px; overflow-y: auto; }
        .ai-grant-item { display: flex; justify-content: space-between; align-items: center; gap: 10px; width: 100%; padding: 9px 12px; border: none; border-bottom: 1px solid #f1f5f9; background: #fff; font-family: inherit; text-align: left; cursor: pointer; }
        .ai-grant-item:hover, .ai-grant-item.on { background: #eff6ff; }
        .ai-grant-item b { display: block; font-size: 13.5px; color: #0f172a; }
        .ai-grant-item small { display: block; font-size: 12px; font-weight: 700; color: #64748b; }
        .ai-grant-empty { padding: 12px; font-size: 13px; font-weight: 700; color: #64748b; }
        .ai-grant-confirm { margin-top: 12px; display: flex; flex-wrap: wrap; align-items: center; gap: 10px; background: #f0fdf4; border: 2px solid #86efac; border-radius: 12px; padding: 10px 14px; font-size: 13.5px; font-weight: 700; color: #14532d; }
        .ai-grant-confirm b { font-size: 14.5px; }
        .ai-grant-confirm .ai-pill { margin-left: 2px; }
    </style>
</head>
<body>

<div class="shell" id="admin-shell">
    <x-admin-sidebar active-route="admin.ai-assistant.index" active-group="packages" />

    <div class="main-workspace">
        <x-admin-topbar title="Quản Trị Trợ Lý AI" breadcrumb="💎 Gói & Bản quyền > Trợ Lý AI" />

        <main class="main-content">
            @if(session('ok'))
                <div class="ai-flash">✓ {{ session('ok') }}</div>
            @endif
            @if($errors->any())
                <div class="ai-flash ai-flash-err">⚠️ {{ $errors->first() }}</div>
            @endif

            <!-- Tổng quan -->
            <div class="ai-stats">
                <div class="ai-stat blue">
                    <span class="ai-stat-ico">💎</span>
                    <div><small>Gói đang mở bán</small><b>{{ $stats['goi_dang_ban'] }} / {{ $stats['goi_tong'] }}</b></div>
                </div>
                <div class="ai-stat violet">
                    <span class="ai-stat-ico">🤖</span>
                    <div><small>Tài khoản đang dùng AI</small><b>{{ $stats['dang_dung'] }}</b></div>
                </div>
                <div class="ai-stat amber">
                    <span class="ai-stat-ico">⏳</span>
                    <div><small>Sắp hết hạn ({{ $expiringDays }} ngày)</small><b>{{ $stats['sap_het_han'] }}</b></div>
                </div>
                <div class="ai-stat green">
                    <span class="ai-stat-ico">💰</span>
                    <div><small>Doanh thu gói AI</small><b>{{ number_format($stats['doanh_thu'], 0, ',', '.') }} đ</b><em>{{ $stats['so_don'] }} đơn đã kích hoạt</em></div>
                </div>
            </div>

            <!-- Thanh tab: chuyển ngay, không tải lại trang -->
            <div class="ai-tabs" role="tablist">
                <button type="button" class="ai-tab" data-tab="goi" onclick="showAiTab('goi')">📦 Gói bán <span class="ai-tab-count">{{ $stats['goi_tong'] }}</span></button>
                <button type="button" class="ai-tab" data-tab="tai-khoan" onclick="showAiTab('tai-khoan')">👥 Tài khoản đang dùng <span class="ai-tab-count">{{ $stats['dang_dung'] }}</span></button>
                <button type="button" class="ai-tab" data-tab="lich-su" onclick="showAiTab('lich-su')">🧾 Lịch sử mua <span class="ai-tab-count">{{ $stats['tong_don'] }}</span></button>
            </div>

            {{-- ============ TAB GÓI BÁN ============ --}}
            <section data-section="goi">
                <div class="ai-card">
                    <h3>Tạo gói Trợ lý AI mới</h3>
                    <p class="hint">Gói học sinh cấp cho 1 học sinh. Gói giáo viên không giới hạn số học sinh.</p>
                    <form method="POST" action="{{ route('admin.ai-assistant.packages.store') }}" class="ai-form">
                        @csrf
                        <div class="ai-field" style="flex:1; min-width:220px;">
                            <label>Tên gói</label>
                            <input type="text" name="name" placeholder="VD: Trợ lý AI - Học sinh" required maxlength="120">
                        </div>
                        <div class="ai-field">
                            <label>Đối tượng</label>
                            <select name="target_audience">
                                <option value="student">Học sinh</option>
                                <option value="teacher">Giáo viên</option>
                            </select>
                        </div>
                        <div class="ai-field">
                            <label>Giá (đ)</label>
                            <input type="number" name="price" min="0" step="1000" value="79000" required style="width:120px;">
                        </div>
                        <div class="ai-field">
                            <label>Số ngày</label>
                            <input type="number" name="duration_days" min="1" max="365" value="30" required style="width:90px;">
                        </div>
                        <label class="ai-check"><input type="checkbox" name="is_active" value="1" checked> Mở bán ngay</label>
                        <button type="submit" class="ai-btn ai-btn-blue">+ Tạo gói</button>
                    </form>
                </div>

                <div class="ai-card">
                    <h3>Các gói Trợ lý AI</h3>
                    <p class="hint">Sửa rồi bấm "Lưu" ở cuối dòng. Gói tạm ẩn không hiện trên bảng giá.</p>
                    @if($packages->isEmpty())
                        <div class="ai-empty">Chưa có gói Trợ lý AI nào. Dùng form phía trên để tạo gói đầu tiên.</div>
                    @else
                        <table>
                            <thead>
                                <tr>
                                    <th>Tên gói</th>
                                    <th>Đối tượng</th>
                                    <th>Giá (đ)</th>
                                    <th>Số ngày</th>
                                    <th>Đã bán</th>
                                    <th>Mở bán</th>
                                    <th style="text-align:right;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($packages as $pkg)
                                    <tr>
                                        <td>
                                            <input form="pkg-{{ $pkg->id }}" type="text" name="name" value="{{ $pkg->name }}" maxlength="120" required style="width:100%; min-width:200px;">
                                        </td>
                                        <td>
                                            <span class="ai-pill ai-pill-aud {{ $pkg->target_audience === 'teacher' ? 'teacher' : '' }}">{{ $pkg->target_audience === 'student' ? 'Học sinh' : 'Giáo viên' }}</span>
                                        </td>
                                        <td><input form="pkg-{{ $pkg->id }}" type="number" name="price" min="0" step="1000" value="{{ $pkg->price }}" required style="width:110px;"></td>
                                        <td><input form="pkg-{{ $pkg->id }}" type="number" name="duration_days" min="1" max="365" value="{{ $pkg->duration_days }}" required style="width:80px;"></td>
                                        <td>{{ $pkg->orders()->where('status', \App\Models\PackageOrder::STATUS_ACTIVE)->count() }} đơn</td>
                                        <td>
                                            <label class="ai-check">
                                                <input form="pkg-{{ $pkg->id }}" type="checkbox" name="is_active" value="1" {{ $pkg->is_active ? 'checked' : '' }}>
                                                {{ $pkg->is_active ? 'Đang bán' : 'Tạm ẩn' }}
                                            </label>
                                        </td>
                                        <td style="text-align:right;">
                                            <form id="pkg-{{ $pkg->id }}" method="POST" action="{{ route('admin.ai-assistant.packages.update', $pkg) }}" style="margin:0;">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="ai-btn ai-btn-green">Lưu</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </section>

            {{-- ============ TAB TÀI KHOẢN ============ --}}
            <section data-section="tai-khoan">
                <div class="ai-card">
                    <h3>🎁 Cấp Trợ lý AI cho tài khoản</h3>
                    <p class="hint">Dùng để tặng, cho dùng thử hoặc hỗ trợ khách. Gõ tên, mã học sinh hoặc email rồi <b>chọn đúng người</b> trong danh sách hiện ra. Tài khoản đang còn hạn thì được cộng thêm ngày.</p>
                    <form method="POST" action="{{ route('admin.ai-assistant.grant') }}" class="ai-form" id="ai-grant-form">
                        @csrf
                        <input type="hidden" name="user_id" id="ai-grant-user" value="">
                        <div class="ai-field ai-grant-field" style="flex:1 1 280px;">
                            <label for="ai-grant-account">Tài khoản</label>
                            <input id="ai-grant-account" type="text" maxlength="120" autocomplete="off" placeholder="Gõ tên, mã HS hoặc email (ít nhất 2 ký tự)..." style="width:100%;box-sizing:border-box;" aria-controls="ai-grant-list" aria-expanded="false">
                            <div class="ai-grant-list" id="ai-grant-list" role="listbox" hidden></div>
                        </div>
                        <div class="ai-field">
                            <label for="ai-grant-days">Số ngày</label>
                            <input id="ai-grant-days" type="number" name="days" min="1" max="365" value="{{ old('days', 30) }}" style="width:90px;">
                        </div>
                        <button class="ai-btn ai-btn-green" type="submit" id="ai-grant-submit" disabled>🎁 Cấp quyền</button>
                    </form>
                    <div class="ai-grant-confirm" id="ai-grant-confirm" hidden></div>
                </div>

                <div class="ai-card" id="ai-members-card">
                    <h3>Tài khoản có quyền dùng Trợ lý AI</h3>
                    <p class="hint">Sắp hết hạn được xếp lên đầu. Thu hồi sẽ tắt quyền dùng AI ngay, hạn học tập không đổi.</p>

                    <div class="ai-filters">
                        @foreach(['tat-ca' => 'Tất cả', 'con-han' => 'Đang dùng', 'het-han' => 'Đã hết hạn'] as $key => $label)
                            <a href="{{ route('admin.ai-assistant.index', ['tab' => 'tai-khoan', 'trang_thai' => $key, 'q' => $search]) }}"
                               class="ai-chip {{ $status === $key ? 'on' : '' }}">{{ $label }}</a>
                        @endforeach
                    </div>

                    <form method="GET" action="{{ route('admin.ai-assistant.index') }}" class="ai-filters">
                        <input type="hidden" name="tab" value="tai-khoan">
                        <input type="hidden" name="trang_thai" value="{{ $status }}">
                        <input type="text" name="q" value="{{ $search }}" class="ai-search" placeholder="Tìm theo tên, email hoặc mã HS...">
                        <button type="submit" class="ai-btn ai-btn-blue">Tìm</button>
                    </form>

                    @if($members->isEmpty())
                        <div class="ai-empty">{{ $search !== '' ? 'Không có tài khoản nào khớp với từ khóa.' : 'Chưa có tài khoản nào trong nhóm này.' }}</div>
                    @else
                        <table>
                            <thead>
                                <tr>
                                    <th>Tài khoản</th>
                                    <th>Vai trò</th>
                                    <th>Hết hạn</th>
                                    <th>Tình trạng</th>
                                    <th style="text-align:right;">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($members as $member)
                                    @php
                                        $left = (int) ceil(now()->diffInSeconds($member->ai_assistant_until, false) / 86400);
                                        $active = $member->hasAiAssistant();
                                        $expiring = $active && $left <= $expiringDays;
                                    @endphp
                                    <tr class="{{ $expiring ? 'ai-row-warn' : '' }}">
                                        <td>
                                            <b>{{ $member->name }}</b><br>
                                            <small style="color:#64748b; font-weight:700;">{{ $member->student_code ?: $member->email }}</small>
                                        </td>
                                        <td>{{ $member->isTeacher() ? 'Giáo viên' : ($member->isAdmin() ? 'Quản trị' : 'Học sinh') }}</td>
                                        <td>
                                            {{ $member->ai_assistant_until->format('d/m/Y') }}
                                            @if($active)<span class="ai-remain">còn {{ $left }} ngày</span>@endif
                                        </td>
                                        <td>
                                            @if(! $active)
                                                <span class="ai-pill ai-pill-off">Đã hết hạn</span>
                                            @elseif($expiring)
                                                <span class="ai-pill ai-pill-warn">Sắp hết hạn</span>
                                            @else
                                                <span class="ai-pill ai-pill-on">Đang dùng</span>
                                            @endif
                                        </td>
                                        <td style="text-align:right;">
                                            <form method="POST" action="{{ route('admin.ai-assistant.extend', $member) }}" class="ai-inline">
                                                @csrf
                                                <input type="number" name="days" value="30" min="1" max="365" aria-label="Số ngày gia hạn">
                                                <button type="submit" class="ai-btn ai-btn-green">+ Gia hạn</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.ai-assistant.revoke', $member) }}" class="ai-inline" style="margin-top:6px;"
                                                  onsubmit="return confirm('Thu hồi quyền dùng Trợ lý AI của {{ $member->name }}?')">
                                                @csrf
                                                <button type="submit" class="ai-btn ai-btn-red">Thu hồi</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div style="margin-top:12px;">{{ $members->links() }}</div>
                    @endif
                </div>
            </section>

            {{-- ============ TAB LỊCH SỬ MUA ============ --}}
            <section data-section="lich-su">
                <div class="ai-card" id="ai-orders-card">
                    <h3>Lịch sử mua gói Trợ lý AI</h3>
                    <p class="hint">Tất cả đơn gói AI, mới nhất trước. Chỉ đơn đã kích hoạt mới được tính vào doanh thu.</p>

                    <form method="GET" action="{{ route('admin.ai-assistant.index') }}" class="ai-filters ai-orders-bar">
                        <input type="hidden" name="tab" value="lich-su">
                        <div class="ai-field grow">
                            <label for="don-q">Tìm kiếm</label>
                            <input type="text" id="don-q" name="don_q" value="{{ $ordersSearch }}" placeholder="Mã đơn, tên, email, mã HS, tên gói...">
                        </div>
                        <div class="ai-field">
                            <label for="don-status">Trạng thái</label>
                            <select id="don-status" name="don_trang_thai" onchange="this.form.requestSubmit()">
                                @foreach(['tat-ca' => 'Tất cả trạng thái', 'cho-thanh-toan' => 'Chờ thanh toán', 'da-kich-hoat' => 'Đã kích hoạt', 'da-huy' => 'Đã hủy / hết hạn'] as $key => $label)
                                    <option value="{{ $key }}" @selected($ordersStatus === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="ai-field">
                            <label for="don-time">Thời gian</label>
                            <select id="don-time" name="don_thoi_gian" onchange="this.form.requestSubmit()">
                                @foreach(['tat-ca' => 'Mọi thời gian', '7' => '7 ngày qua', '30' => '30 ngày qua', '90' => '90 ngày qua'] as $key => $label)
                                    <option value="{{ $key }}" @selected($ordersRange === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="ai-btn ai-btn-blue ai-orders-submit">🔍 Lọc</button>
                    </form>

                    @if($orders->isEmpty())
                        <div class="ai-empty">{{ ($ordersSearch !== '' || $ordersStatus !== 'tat-ca' || $ordersRange !== 'tat-ca') ? 'Không có đơn nào khớp bộ lọc.' : 'Chưa có đơn mua gói Trợ lý AI nào.' }}</div>
                    @else
                        <table>
                            <thead>
                                <tr>
                                    <th>Mã đơn</th>
                                    <th>Ngày kích hoạt</th>
                                    <th>Tài khoản</th>
                                    <th>Gói</th>
                                    <th>Số tiền</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                                    <tr>
                                        <td style="font-family:monospace; font-weight:800;">{{ $order->code }}</td>
                                        <td>{{ optional($order->activated_at ?? $order->created_at)->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <b>{{ $order->user?->name ?? '—' }}</b><br>
                                            <small style="color:#64748b; font-weight:700;">{{ $order->user?->student_code ?: $order->user?->email }}</small>
                                        </td>
                                        <td>{{ $order->package?->name ?? $order->package_name }}</td>
                                        <td style="font-weight:800; color:#b45309;">{{ number_format($order->price, 0, ',', '.') }} đ</td>
                                        <td>
                                            @php
                                                $pillClass = match ($order->status) {
                                                    \App\Models\PackageOrder::STATUS_ACTIVE => 'ai-pill-on',
                                                    \App\Models\PackageOrder::STATUS_PENDING => 'ai-pill-warn',
                                                    default => 'ai-pill-off',
                                                };
                                            @endphp
                                            <span class="ai-pill {{ $pillClass }}">{{ $order->status_label }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div style="margin-top:12px;">{{ $orders->links() }}</div>
                    @endif
                </div>
            </section>
        </main>
    </div>
</div>

<script>
    // Cấp Trợ lý AI: gõ để tìm, chọn đúng người trong danh sách gợi ý, xác nhận rồi mới cấp
    (() => {
        const input = document.getElementById('ai-grant-account');
        if (!input) return;
        const list = document.getElementById('ai-grant-list');
        const hidden = document.getElementById('ai-grant-user');
        const submit = document.getElementById('ai-grant-submit');
        const confirmBox = document.getElementById('ai-grant-confirm');
        const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
        let timer = null, seq = 0, items = [], active = -1;

        const describe = u => [u.code, u.role + (u.classroom ? ' · ' + u.classroom : ''), u.email].filter(Boolean).map(esc).join(' · ');
        const aiPill = u => u.ai_active
            ? '<span class="ai-pill ai-pill-on">AI còn hạn đến ' + esc(u.ai_until) + '</span>'
            : (u.ai_until ? '<span class="ai-pill ai-pill-warn">AI hết hạn ' + esc(u.ai_until) + '</span>' : '<span class="ai-pill ai-pill-off">Chưa có AI</span>');

        function close() { list.hidden = true; input.setAttribute('aria-expanded', 'false'); active = -1; }
        function clearChoice() { hidden.value = ''; submit.disabled = true; confirmBox.hidden = true; }
        function choose(u) {
            hidden.value = u.id;
            input.value = u.name;
            submit.disabled = false;
            confirmBox.innerHTML = '✅ Cấp cho: <b>' + esc(u.name) + '</b> · ' + describe(u) + ' ' + aiPill(u)
                + (u.ai_active ? ' <span>→ sẽ được <b>cộng thêm</b> số ngày.</span>' : '');
            confirmBox.hidden = false;
            close();
        }
        function render() {
            list.innerHTML = items.length
                ? items.map((u, i) => '<button type="button" role="option" class="ai-grant-item' + (i === active ? ' on' : '') + '" data-i="' + i + '"><span><b>' + esc(u.name) + '</b><small>' + describe(u) + '</small></span>' + aiPill(u) + '</button>').join('')
                : '<div class="ai-grant-empty">Không tìm thấy tài khoản nào. Thử gõ mã học sinh hoặc email.</div>';
            list.hidden = false;
            input.setAttribute('aria-expanded', 'true');
        }

        input.addEventListener('input', () => {
            clearChoice();
            clearTimeout(timer);
            const q = input.value.trim();
            if (q.length < 2) { close(); return; }
            timer = setTimeout(async () => {
                const my = ++seq;
                try {
                    const res = await fetch(@json(route('admin.ai-assistant.lookup')) + '?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' } });
                    const data = await res.json();
                    if (my !== seq) return; // đã gõ tiếp, bỏ kết quả cũ
                    items = data.users || [];
                    active = items.length ? 0 : -1;
                    render();
                } catch (e) { close(); }
            }, 250);
        });
        input.addEventListener('keydown', e => {
            if (list.hidden || !items.length) return;
            if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                e.preventDefault();
                active = (active + (e.key === 'ArrowDown' ? 1 : items.length - 1)) % items.length;
                render();
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (items[active]) choose(items[active]);
            } else if (e.key === 'Escape') { close(); }
        });
        list.addEventListener('mousedown', e => {
            const btn = e.target.closest('.ai-grant-item');
            if (btn) { e.preventDefault(); choose(items[Number(btn.dataset.i)]); }
        });
        input.addEventListener('blur', () => setTimeout(close, 150));
        document.getElementById('ai-grant-form').addEventListener('submit', e => {
            if (!hidden.value) { e.preventDefault(); input.focus(); }
        });
    })();

    // Chuyển tab ngay trên trang, chỉ đổi vùng nội dung. Đổi địa chỉ để tải lại vẫn ở đúng tab.
    function showAiTab(tab) {
        document.querySelectorAll('[data-section]').forEach(section => {
            section.hidden = section.dataset.section !== tab;
        });
        document.querySelectorAll('.ai-tab').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.tab === tab);
        });
        const url = new URL(window.location.href);
        url.searchParams.set('tab', tab);
        history.replaceState(null, '', url);
    }

    // Lọc, tìm kiếm và chuyển trang của danh sách tài khoản: chỉ làm mới khung này, không tải lại cả trang
    const AI_LIST_CARDS = ['ai-members-card', 'ai-orders-card'];

    async function reloadListCard(cardId, url) {
        try {
            const res = await fetch(url, { headers: { 'X-Requested-With': 'fetch' } });
            const doc = new DOMParser().parseFromString(await res.text(), 'text/html');
            const fresh = doc.getElementById(cardId);
            const current = document.getElementById(cardId);
            if (!fresh || !current) {
                window.location.href = url;
                return;
            }
            current.replaceWith(fresh);
            history.replaceState(null, '', url);
        } catch (e) {
            window.location.href = url;
        }
    }

    // Lọc, tìm kiếm, chuyển trang: chỉ làm mới đúng khung đang thao tác, không tải lại cả trang
    document.addEventListener('click', e => {
        const link = e.target.closest(AI_LIST_CARDS.map(id => `#${id} a.ai-chip, #${id} nav a`).join(', '));
        if (!link) return;
        e.preventDefault();
        reloadListCard(link.closest('[id]').id, link.href);
    });

    document.addEventListener('submit', e => {
        const form = e.target.closest(AI_LIST_CARDS.map(id => `#${id} form.ai-filters`).join(', '));
        if (!form) return;
        e.preventDefault();
        const url = new URL(form.action);
        new FormData(form).forEach((value, key) => url.searchParams.set(key, value));
        reloadListCard(form.closest('[id]').id, url.toString());
    });

    document.addEventListener('DOMContentLoaded', () => {
        const tab = new URLSearchParams(window.location.search).get('tab');
        showAiTab(['goi', 'tai-khoan', 'lich-su'].includes(tab) ? tab : 'tai-khoan');
    });
</script>
</body>
</html>
