<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Thanh Toán Chuyển Khoản #{{ $order->code }} — IC3 Quest</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-light: #eff6ff;
            --emerald: #059669;
            --emerald-light: #ecfdf5;
            --amber: #d97706;
            --amber-light: #fffbeb;
            --slate-900: #0f172a;
            --slate-800: #1e293b;
            --slate-700: #334155;
            --slate-600: #475569;
            --slate-500: #64748b;
            --slate-200: #e2e8f0;
            --slate-100: #f1f5f9;
            --slate-50: #f8fafc;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 16px;
            color: var(--slate-900);
        }

        /* Ambient backdrop glow */
        .ambient-glow {
            position: fixed;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.2) 0%, rgba(16, 185, 129, 0.08) 60%, transparent 70%);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            pointer-events: none;
            z-index: 0;
        }

        /* Main Modal Container */
        .pay-modal {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 680px;
            background: #ffffff;
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.45), 0 0 0 1px rgba(255, 255, 255, 0.1);
            overflow: hidden;
            animation: modalFadeUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes modalFadeUp {
            from {
                opacity: 0;
                transform: translateY(18px) scale(0.98);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Modal Header */
        .modal-head {
            padding: 20px 24px 16px;
            background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
            border-bottom: 1px solid var(--slate-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .modal-head-brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-logo-chip {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, var(--primary) 0%, #3b82f6 100%);
            color: #ffffff;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 800;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
            flex-shrink: 0;
        }

        .modal-head-text h1 {
            font-size: 17px;
            font-weight: 800;
            color: var(--slate-900);
            letter-spacing: -0.01em;
            line-height: 1.3;
        }

        .modal-head-text .meta-sub {
            font-size: 12.5px;
            color: var(--slate-500);
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
            margin-top: 2px;
        }

        .badge-order-chip {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            background: var(--slate-100);
            border: 1px solid var(--slate-200);
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            color: var(--slate-700);
            letter-spacing: 0.02em;
        }

        .live-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            background: var(--emerald-light);
            border: 1px solid #bbf7d0;
            border-radius: 999px;
            font-size: 11.5px;
            font-weight: 700;
            color: var(--emerald);
            white-space: nowrap;
            flex-shrink: 0;
        }

        .live-status-pill .pulse-dot {
            width: 8px;
            height: 8px;
            background: var(--emerald);
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 0 0 rgba(5, 150, 105, 0.7);
            animation: pulseWave 1.8s infinite cubic-bezier(0.66, 0, 0, 1);
        }

        @keyframes pulseWave {
            0% {
                box-shadow: 0 0 0 0 rgba(5, 150, 105, 0.7);
            }
            70% {
                box-shadow: 0 0 0 8px rgba(5, 150, 105, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(5, 150, 105, 0);
            }
        }

        /* Banner Guideline */
        .guide-banner {
            background: #eff6ff;
            border-bottom: 1px solid #dbeafe;
            padding: 10px 24px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12.5px;
            color: #1e40af;
            line-height: 1.4;
        }

        .guide-banner .tip-icon {
            font-size: 16px;
            flex-shrink: 0;
        }

        /* Modal Body */
        .modal-content-grid {
            padding: 20px 24px;
            display: grid;
            grid-template-columns: 240px 1fr;
            gap: 20px;
            align-items: start;
        }

        @media (max-width: 620px) {
            .modal-content-grid {
                grid-template-columns: 1fr;
                gap: 16px;
                padding: 16px;
            }
            .modal-head {
                padding: 16px;
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
            .live-status-pill {
                align-self: flex-start;
            }
        }

        /* Left: QR Card */
        .qr-display-box {
            background: #ffffff;
            border: 1.5px solid var(--slate-200);
            border-radius: 18px;
            padding: 14px;
            text-align: center;
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05);
            transition: border-color 0.2s;
        }

        .qr-display-box:hover {
            border-color: #93c5fd;
        }

        .qr-image-wrapper {
            position: relative;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            display: inline-block;
            margin-bottom: 8px;
            border: 1px solid #f1f5f9;
        }

        .qr-image-wrapper img {
            width: 100%;
            max-width: 210px;
            height: auto;
            aspect-ratio: 1/1;
            display: block;
            border-radius: 10px;
        }

        .qr-card-badges {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 8px;
        }

        .qr-badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 7px;
            background: #f8fafc;
            border: 1px solid var(--slate-200);
            border-radius: 5px;
            font-size: 10.5px;
            font-weight: 700;
            color: var(--slate-600);
        }

        .qr-scan-hint {
            font-size: 11px;
            color: var(--slate-500);
            line-height: 1.4;
            font-weight: 500;
        }

        /* Right: Transfer Detail Rows */
        .transfer-details-box {
            display: flex;
            flex-direction: column;
            gap: 8.5px;
        }

        .detail-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 8px 12px;
            background: #ffffff;
            border: 1px solid var(--slate-200);
            border-radius: 11px;
            transition: all 0.15s;
        }

        .detail-row:hover {
            background: var(--slate-50);
            border-color: #cbd5e1;
        }

        .detail-meta-label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 600;
            color: var(--slate-500);
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .detail-value-text {
            font-size: 13.5px;
            font-weight: 700;
            color: var(--slate-900);
            word-break: break-all;
            margin-top: 1px;
        }

        /* Highlighted Amounts & Memo */
        .detail-row-amount {
            background: var(--primary-light) !important;
            border-color: #bfdbfe !important;
        }

        .detail-row-amount .detail-meta-label {
            color: #1d4ed8;
        }

        .detail-row-amount .detail-value-text {
            font-size: 16.5px;
            font-weight: 800;
            color: #1d4ed8;
        }

        .detail-row-memo {
            background: var(--amber-light) !important;
            border-color: #fde68a !important;
        }

        .detail-row-memo .detail-meta-label {
            color: #b45309;
        }

        .detail-row-memo .detail-value-text {
            font-size: 14.5px;
            font-weight: 800;
            color: #b45309;
            letter-spacing: 0.02em;
        }

        /* 1-Click Copy Chip Button */
        .copy-chip-btn {
            background: #ffffff;
            color: var(--primary);
            border: 1px solid #bfdbfe;
            border-radius: 7px;
            padding: 4px 9px;
            font-size: 11.5px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: all 0.15s;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .copy-chip-btn:hover {
            background: var(--primary);
            color: #ffffff;
            border-color: var(--primary);
        }

        .copy-chip-btn.copied {
            background: var(--emerald);
            color: #ffffff;
            border-color: var(--emerald);
        }

        /* Important Note Box */
        .transfer-note-box {
            background: #fffbeb;
            border: 1px solid #fef3c7;
            border-radius: 10px;
            padding: 8px 12px;
            font-size: 11.5px;
            color: #92400e;
            line-height: 1.45;
            display: flex;
            align-items: flex-start;
            gap: 6px;
        }

        .transfer-note-box b {
            color: #78350f;
        }

        /* Modal Footer / Actions */
        .modal-foot {
            padding: 14px 24px 18px;
            background: #f8fafc;
            border-top: 1px solid var(--slate-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        @media (max-width: 620px) {
            .modal-foot {
                padding: 14px 16px;
                flex-direction: column-reverse;
            }
            .modal-foot button, .modal-foot a {
                width: 100%;
                justify-content: center;
            }
        }

        .btn-foot-secondary {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 18px;
            background: #ffffff;
            border: 1px solid var(--slate-300);
            border-radius: 999px;
            color: var(--slate-700);
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.15s;
        }

        .btn-foot-secondary:hover {
            background: var(--slate-100);
            border-color: var(--slate-400);
        }

        .btn-foot-confirm {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 10px 22px;
            background: linear-gradient(135deg, var(--emerald) 0%, #10b981 100%);
            border: none;
            border-radius: 999px;
            color: #ffffff;
            font-size: 13.5px;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
            transition: all 0.15s;
        }

        .btn-foot-confirm:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(16, 185, 129, 0.45);
        }

        .btn-foot-confirm:disabled {
            opacity: 0.8;
            cursor: default;
        }

        /* Success Active State View */
        .success-active-view {
            padding: 40px 24px;
            text-align: center;
            display: none;
        }

        .success-icon-badge {
            width: 72px;
            height: 72px;
            background: var(--emerald-light);
            color: var(--emerald);
            border: 2px solid #a7f3d0;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            margin-bottom: 16px;
            animation: bounceIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        @keyframes bounceIn {
            0% { transform: scale(0.3); opacity: 0; }
            50% { transform: scale(1.1); opacity: 1; }
            100% { transform: scale(1); }
        }

        .success-active-view h2 {
            font-size: 22px;
            font-weight: 800;
            color: var(--slate-900);
            margin-bottom: 8px;
        }

        .success-active-view p {
            font-size: 14px;
            color: var(--slate-600);
            max-width: 460px;
            margin: 0 auto 20px;
            line-height: 1.5;
        }

        .countdown-bar-wrap {
            width: 100%;
            max-width: 320px;
            height: 6px;
            background: var(--slate-200);
            border-radius: 999px;
            margin: 0 auto;
            overflow: hidden;
        }

        .countdown-bar {
            height: 100%;
            width: 0%;
            background: var(--emerald);
            transition: width 2.5s linear;
        }
    </style>
</head>
<body>

    <div class="ambient-glow"></div>

    <div class="pay-modal" id="pay-modal-card">

        <!-- SUCCESS VIEW (Appears when order is active) -->
        <div class="success-active-view" id="success-view" @if($order->isActive()) style="display: block;" @endif>
            <div class="success-icon-badge">🎉</div>
            <h2>Thanh Toán & Kích Hoạt Thành Công!</h2>
            <p>
                Gói bản quyền <b>{{ $cleanPkgName }}</b> của Thầy/Cô đã được kích hoạt thành công. Thầy/Cô có thể tạo lớp và phân quyền cho học sinh ngay bây giờ!
            </p>
            <div class="countdown-bar-wrap">
                <div class="countdown-bar" id="redirect-bar"></div>
            </div>
            <div style="margin-top: 24px;">
                @if(auth()->user()?->canAccessAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="btn-foot-confirm" style="text-decoration: none;">
                        🏫 Đến Trang Quản Trị Ngay
                    </a>
                @else
                    <a href="{{ route('programs') }}" class="btn-foot-confirm" style="text-decoration: none;">
                        🚀 Bắt Đầu Luyện Thi Ngay
                    </a>
                @endif
            </div>
        </div>

        <!-- MAIN PAYMENT DETAILS VIEW -->
        <div id="payment-view" @if($order->isActive()) style="display: none;" @endif>
            <!-- Modal Header -->
            <div class="modal-head">
                <div class="modal-head-brand">
                    <div class="brand-logo-chip">IC3</div>
                    <div class="modal-head-text">
                        <h1>Thanh Toán Chuyển Khoản</h1>
                        <div class="meta-sub">
                            <span class="badge-order-chip">#{{ $order->code }}</span>
                            <span>· Gói: <b>{{ $cleanPkgName }}</b></span>
                        </div>
                    </div>
                </div>

                <div class="live-status-pill">
                    <span class="pulse-dot"></span>
                    <span id="live-status-text">Đang chờ giao dịch...</span>
                </div>
            </div>

            <!-- Guideline Banner -->
            <div class="guide-banner">
                <span class="tip-icon">💡</span>
                <span>Mở App Ngân hàng bất kỳ để <b>quét mã VietQR</b> hoặc <b>chuyển khoản</b> chính xác số tiền & nội dung bên dưới.</span>
            </div>

            <!-- Content Grid: Left QR & Right Bank Details -->
            <div class="modal-content-grid">
                <!-- Left: QR Code Card -->
                <div class="qr-display-box">
                    <div class="qr-image-wrapper">
                        <img src="{{ $vietQrUrl }}" alt="Mã VietQR Chuyển Khoản" id="vietqr-img">
                    </div>

                    <div class="qr-card-badges">
                        <span class="qr-badge-pill">⚡ Napas 247</span>
                        <span class="qr-badge-pill">🛡️ VietQR PRO</span>
                    </div>

                    <p class="qr-scan-hint">
                        Quét bằng App Ngân hàng bất kỳ hoặc Ví MoMo, ZaloPay, Viettel Money
                    </p>
                </div>

                <!-- Right: Bank Information Details with 1-Click Copy -->
                <div class="transfer-details-box">
                    <!-- Bank Name -->
                    <div class="detail-row">
                        <div>
                            <div class="detail-meta-label">🏦 Ngân hàng thụ hưởng</div>
                            <div class="detail-value-text">{{ $bankConfig['bank_name'] }}</div>
                        </div>
                    </div>

                    <!-- Account Name -->
                    <div class="detail-row">
                        <div>
                            <div class="detail-meta-label">👤 Chủ tài khoản</div>
                            <div class="detail-value-text" id="val-acc-name">{{ $bankConfig['account_name'] }}</div>
                        </div>
                    </div>

                    <!-- Account Number -->
                    <div class="detail-row">
                        <div>
                            <div class="detail-meta-label">🔢 Số tài khoản</div>
                            <div class="detail-value-text" id="val-acc-no" style="font-family: monospace; font-size: 15px; letter-spacing: 0.05em;">{{ $bankConfig['account_no'] }}</div>
                        </div>
                        <button type="button" class="copy-chip-btn" onclick="copyToClipboard('val-acc-no', this)">
                            <span>📋</span> <span>Sao chép</span>
                        </button>
                    </div>

                    <!-- Amount -->
                    <div class="detail-row detail-row-amount">
                        <div>
                            <div class="detail-meta-label">💰 Số tiền thanh toán</div>
                            <div class="detail-value-text" id="val-amount">{{ $order->formatted_price }}</div>
                        </div>
                        <button type="button" class="copy-chip-btn" onclick="copyValue('{{ $order->price }}', this)">
                            <span>📋</span> <span>Sao chép</span>
                        </button>
                    </div>

                    <!-- Transfer Memo -->
                    <div class="detail-row detail-row-memo">
                        <div>
                            <div class="detail-meta-label">✍️ Nội dung chuyển khoản (Bắt buộc)</div>
                            <div class="detail-value-text" id="val-memo">{{ $transferContent }}</div>
                        </div>
                        <button type="button" class="copy-chip-btn" onclick="copyToClipboard('val-memo', this)">
                            <span>📋</span> <span>Sao chép</span>
                        </button>
                    </div>

                    <!-- Notice Note -->
                    <div class="transfer-note-box">
                        <span>⚠️</span>
                        <span>Lưu ý: Vui lòng nhập <b>chính xác số tiền</b> và <b>nội dung chuyển khoản</b> để hệ thống tự động kích hoạt tài khoản ngay.</span>
                    </div>
                </div>
            </div>

            <!-- Modal Foot / Actions -->
            <div class="modal-foot">
                <a href="{{ route('pricing.index') }}" class="btn-foot-secondary">
                    ✕ Đóng / Quay Lại
                </a>

                <button type="button" class="btn-foot-confirm" id="btn-confirm-transferred" onclick="confirmPaymentTransferred()">
                    <span>⚡ Tôi Đã Chuyển Khoản Xong</span>
                </button>
            </div>
        </div>
    </div>

    <script>
        const ORDER_CODE = "{{ $order->code }}";
        let isPolling = true;
        let pollTimer = null;

        // 📋 1-Click Copy function
        function copyToClipboard(elementId, btn) {
            const el = document.getElementById(elementId);
            if (!el) return;
            const text = el.innerText.trim();
            executeCopy(text, btn);
        }

        function copyValue(val, btn) {
            executeCopy(val, btn);
        }

        function executeCopy(text, btn) {
            navigator.clipboard.writeText(text).then(() => {
                const originalHtml = btn.innerHTML;
                btn.classList.add('copied');
                btn.innerHTML = '<span>✓</span> <span>Đã chép</span>';
                setTimeout(() => {
                    btn.classList.remove('copied');
                    btn.innerHTML = originalHtml;
                }, 1800);
            }).catch(() => {
                // Fallback prompt
                prompt("Nhấn Ctrl+C để sao chép:", text);
            });
        }

        // ⚡ Nút "Tôi Đã Chuyển Khoản"
        function confirmPaymentTransferred() {
            const btn = document.getElementById('btn-confirm-transferred');
            if (!btn) return;
            btn.disabled = true;
            btn.innerHTML = '<span>⏳ Đang gửi thông báo tới Admin...</span>';

            fetch(`/bang-gia/don-hang/${ORDER_CODE}/da-chuyen-khoan`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                btn.innerHTML = '<span>✓ Đã báo Admin kiểm tra!</span>';
                document.getElementById('live-status-text').innerText = 'Đang đợi kiểm tra và kích hoạt...';
            })
            .catch(err => {
                btn.innerHTML = '<span>✓ Đã ghi nhận chuyển khoản</span>';
            });
        }

        // 🔄 Real-time Polling: Kiểm tra trạng thái đơn hàng mỗi 2.5s
        function checkStatus() {
            if (!isPolling) return;

            fetch(`/bang-gia/don-hang/${ORDER_CODE}/trang-thai`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.is_active) {
                    isPolling = false;
                    clearInterval(pollTimer);
                    showActivationSuccess(data.redirect_url);
                }
            })
            .catch(err => console.error('Poll error:', err));
        }

        function showActivationSuccess(redirectUrl) {
            document.getElementById('payment-view').style.display = 'none';
            const successView = document.getElementById('success-view');
            successView.style.display = 'block';

            const bar = document.getElementById('redirect-bar');
            setTimeout(() => {
                if (bar) bar.style.width = '100%';
            }, 100);

            setTimeout(() => {
                window.location.href = redirectUrl || '/chuong-trinh';
            }, 2600);
        }

        @if(! $order->isActive())
            pollTimer = setInterval(checkStatus, 2500);
        @else
            showActivationSuccess("{{ auth()->user()?->canAccessAdmin() ? route('admin.dashboard') : route('programs') }}");
        @endif
    </script>
</body>
</html>
