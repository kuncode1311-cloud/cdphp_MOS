{{--
    Bảng nâng cấp Trợ lý AI (kiểu SaaS hiện đại): chọn gói -> quét mã PayOS ngay trong bảng -> tự mở khóa khi thanh toán xong.
    Mở bằng window.openAiUpgrade(). Gói lấy từ cơ sở dữ liệu (packages.grants_ai_assistant); mã PayOS sống 10 phút, có đếm ngược.
--}}
@php
    $aiUser = auth()->user();
@endphp
@if($aiUser && ! $aiUser->canAccessAdmin())
<style>
    .aiup-overlay { position: fixed; inset: 0; z-index: 100000; display: none; align-items: center; justify-content: center; padding: 16px; background: rgba(15, 12, 48, 0.72); backdrop-filter: blur(6px); }
    .aiup-overlay.open { display: flex; }
    .aiup-card { position: relative; width: min(880px, 100%); max-height: calc(100vh - 32px); overflow: auto; border-radius: 28px; border: 3.5px solid #ffffff; background: linear-gradient(160deg, #3b1f8f 0%, #5b2fc9 55%, #7c3aed 100%); color: #fff; box-shadow: 0 16px 36px rgba(0,0,0,0.18), inset 0 -6px 0 rgba(0,0,0,0.15); padding: 28px; font-family: 'Nunito', 'Segoe UI', sans-serif; }
    .aiup-close { position: absolute; top: 14px; right: 14px; width: 38px; height: 38px; border-radius: 12px; border: 0; background: rgba(255,255,255,0.18); color: #fff; font-size: 18px; font-weight: 900; cursor: pointer; }
    .aiup-close:hover { background: rgba(255,255,255,0.3); }
    .aiup-head { text-align: center; margin-bottom: 18px; }
    .aiup-head .aiup-icon { font-size: 44px; line-height: 1; }
    .aiup-head h2 { margin: 6px 0 4px; font-family: 'Fredoka', 'Nunito', sans-serif; font-size: 28px; font-weight: 800; }
    .aiup-head p { margin: 0 auto; max-width: 560px; font-size: 14px; font-weight: 700; color: #ddd6fe; line-height: 1.5; }
    .aiup-perks { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 10px; margin: 0 0 20px; padding: 0; list-style: none; }
    .aiup-perks li { background: rgba(255,255,255,0.12); border: 2px solid rgba(255,255,255,0.22); border-radius: 14px; padding: 10px 12px; font-size: 13px; font-weight: 800; }
    .aiup-status { display: inline-block; margin-top: 10px; padding: 6px 14px; border-radius: 999px; background: #fde68a; color: #78350f; font-size: 12.5px; font-weight: 900; }
    .aiup-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 16px; }
    .aiup-plan { position: relative; display: flex; flex-direction: column; gap: 8px; padding: 20px 18px; border-radius: 22px; border: 3.5px solid #ffffff; background: #ffffff; color: #1e1b4b; box-shadow: 0 16px 36px rgba(0,0,0,0.18), inset 0 -6px 0 rgba(0,0,0,0.15); }
    .aiup-plan.is-best { background: linear-gradient(180deg, #fffbeb, #ffffff); border-color: #fcd34d; }
    .aiup-best { position: absolute; top: -13px; left: 50%; transform: translateX(-50%); background: linear-gradient(135deg, #f59e0b, #f97316); color: #fff; font-size: 11px; font-weight: 900; padding: 4px 12px; border-radius: 999px; white-space: nowrap; }
    .aiup-plan h3 { margin: 0; font-size: 18px; font-weight: 900; }
    .aiup-plan .aiup-desc { margin: 0; font-size: 13px; font-weight: 700; color: #6b7280; min-height: 36px; }
    .aiup-price { font-family: 'Fredoka', 'Nunito', sans-serif; font-size: 30px; font-weight: 800; color: #6d28d9; }
    .aiup-price small { font-size: 13px; font-weight: 800; color: #6b7280; }
    .aiup-old { font-size: 13px; font-weight: 800; color: #9ca3af; text-decoration: line-through; }
    .aiup-days { font-size: 13px; font-weight: 800; color: #4c1d95; }
    .aiup-btn { margin-top: auto; border: 0; border-radius: 14px; padding: 12px 14px; font-size: 15px; font-weight: 900; color: #fff; cursor: pointer; background: linear-gradient(135deg, #7c3aed, #5b21b6); box-shadow: 0 5px 0 #3b0f8a; transition: transform .12s, box-shadow .12s; }
    .aiup-btn:hover { transform: translateY(-2px); box-shadow: 0 7px 0 #3b0f8a; }
    .aiup-btn:active { transform: translateY(4px); box-shadow: 0 1px 0 #3b0f8a; }
    .aiup-btn[disabled] { opacity: .65; cursor: wait; transform: none; }
    .aiup-empty { text-align: center; padding: 26px 16px; border-radius: 20px; background: rgba(255,255,255,0.12); border: 2px dashed rgba(255,255,255,0.35); font-weight: 800; line-height: 1.6; }
    .aiup-error { display: none; margin-top: 14px; padding: 10px 14px; border-radius: 12px; background: #fee2e2; color: #991b1b; font-size: 13.5px; font-weight: 800; }
    .aiup-pay { display: none; grid-template-columns: 250px 1fr; gap: 22px; align-items: center; }
    .aiup-pay .aiup-qr { background: #fff; border-radius: 20px; padding: 12px; text-align: center; box-shadow: 0 10px 24px rgba(0,0,0,0.25); }
    .aiup-pay .aiup-qr img { width: 100%; height: auto; display: block; border-radius: 12px; transition: opacity .3s; }
    .aiup-pay h3 { margin: 0 0 6px; font-size: 22px; font-weight: 900; }
    .aiup-pay .aiup-row { margin: 6px 0; font-size: 14px; font-weight: 800; color: #ede9fe; }
    .aiup-pay .aiup-row b { color: #fff; }
    .aiup-count { display: inline-block; margin: 10px 0; padding: 7px 14px; border-radius: 999px; background: #fff7ed; border: 2px solid #fdba74; color: #c2410c; font-size: 13px; font-weight: 900; }
    .aiup-count.is-urgent { background: #fef2f2; border-color: #fca5a5; color: #b91c1c; }
    .aiup-count span { font-family: monospace; font-size: 17px; letter-spacing: .05em; }
    .aiup-back { margin-top: 10px; border: 2px solid rgba(255,255,255,0.4); background: transparent; color: #fff; border-radius: 12px; padding: 8px 14px; font-weight: 900; cursor: pointer; }
    .aiup-done { display: none; text-align: center; padding: 30px 10px; }
    .aiup-done .aiup-icon { font-size: 64px; }
    .aiup-done h3 { margin: 8px 0 4px; font-size: 26px; font-weight: 900; }
    @media (max-width: 720px) { .aiup-card { padding: 20px 16px; } .aiup-pay { grid-template-columns: 1fr; } .aiup-head h2 { font-size: 23px; } }
</style>

<div class="aiup-overlay" id="aiup-overlay" role="dialog" aria-modal="true" aria-labelledby="aiup-title" aria-hidden="true">
    <div class="aiup-card">
        <button type="button" class="aiup-close" onclick="closeAiUpgrade()" aria-label="Đóng">✕</button>

        {{-- Bước 1: chọn gói --}}
        <div id="aiup-step-plans">
            <div class="aiup-head">
                <div class="aiup-icon">🤖✨</div>
                <h2 id="aiup-title">Nâng cấp Trợ lý AI</h2>
                <p>Trợ lý riêng biết điểm mạnh, điểm yếu của em và gợi ý bài luyện phù hợp ngay trong khung chat.</p>
                @if($aiUser->hasAiAssistant())
                    <span class="aiup-status">✅ Đang bật đến {{ $aiUser->ai_assistant_until->timezone(config('learning.display_timezone', 'Asia/Ho_Chi_Minh'))->format('d/m/Y') }}. Mua thêm sẽ được cộng dồn.</span>
                @endif
            </div>

            <ul class="aiup-perks">
                <li>📊 Hiểu điểm số và câu hay sai của em</li>
                <li>🎯 Gợi ý bài luyện nên làm tiếp</li>
                <li>🧭 Mở nhanh đúng trang, đúng bài</li>
                <li>💬 Hỏi đáp ngay trong khung chat</li>
            </ul>

            @if($packages->isEmpty())
                <div class="aiup-empty">Gói Trợ lý AI sắp mở bán. 💜<br>Em hãy nhắn cho Thầy/Cô qua khung chat tư vấn để được hỗ trợ nhé!</div>
            @else
                <div class="aiup-grid">
                    @foreach($packages as $pkg)
                        <div class="aiup-plan {{ $loop->count > 1 && $loop->last ? 'is-best' : '' }}">
                            @if($loop->count > 1 && $loop->last)<span class="aiup-best">⭐ Tiết kiệm nhất</span>@endif
                            <h3>{{ $pkg->name }}</h3>
                            <p class="aiup-desc">{{ $pkg->description }}</p>
                            @if($pkg->original_price && $pkg->original_price > $pkg->price)
                                <div class="aiup-old">{{ $pkg->formatted_original_price }}</div>
                            @endif
                            <div class="aiup-price">{{ number_format($pkg->price, 0, ',', '.') }} <small>đ</small></div>
                            <div class="aiup-days">⏳ Dùng trong {{ $pkg->duration_days }} ngày</div>
                            <button type="button" class="aiup-btn" data-slug="{{ $pkg->slug }}" onclick="startAiUpgradePayment(this)">
                                {{ $aiUser->hasAiAssistant() ? '🔄 Gia hạn gói này' : '🚀 Chọn gói này' }}
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif
            <div class="aiup-error" id="aiup-error" role="alert"></div>
        </div>

        {{-- Bước 2: thanh toán PayOS (VietQR) --}}
        <div class="aiup-pay" id="aiup-step-pay">
            <div class="aiup-qr">
                <img id="aiup-qr-img" src="" alt="Mã QR thanh toán">
            </div>
            <div>
                <h3 id="aiup-pay-name">Thanh toán</h3>
                <div class="aiup-row">Mã đơn: <b id="aiup-pay-code">—</b></div>
                <div class="aiup-row">Số tiền: <b id="aiup-pay-amount">—</b></div>
                <div class="aiup-row">Mở app ngân hàng, <b>quét mã QR</b> để thanh toán. Xong là Trợ lý AI tự mở khóa.</div>
                <div class="aiup-count" id="aiup-count">⏳ Mã hết hạn sau <span id="aiup-count-time">--:--</span></div>
                <div class="aiup-row" id="aiup-pay-status">⚡ Đang chờ thanh toán...</div>
                <button type="button" class="aiup-back" onclick="backToAiPlans()">← Chọn gói khác</button>
            </div>
        </div>

        {{-- Bước 3: thành công --}}
        <div class="aiup-done" id="aiup-step-done">
            <div class="aiup-icon">🎉</div>
            <h3>Đã mở khóa Trợ lý AI!</h3>
            <p style="font-weight:800; color:#ddd6fe;">Cảm ơn em. Trang sẽ tự tải lại để bắt đầu chat với Trợ lý AI nhé.</p>
        </div>
    </div>
</div>

<script>
    (function () {
        var overlay = document.getElementById('aiup-overlay');
        if (!overlay) { return; }
        var ORDER_URL = @json(route('pricing.order', ['package' => '__SLUG__']));
        var STATUS_URL = @json(route('pricing.order.status', ['order' => '__CODE__']));
        var CSRF = @json(csrf_token());
        var pollTimer = null, countTimer = null, activeBtn = null;

        function $(id) { return document.getElementById(id); }
        function show(step) {
            $('aiup-step-plans').style.display = step === 'plans' ? 'block' : 'none';
            $('aiup-step-pay').style.display = step === 'pay' ? 'grid' : 'none';
            $('aiup-step-done').style.display = step === 'done' ? 'block' : 'none';
        }
        function stopTimers() {
            if (pollTimer) { clearInterval(pollTimer); pollTimer = null; }
            if (countTimer) { clearInterval(countTimer); countTimer = null; }
        }
        function showError(msg) {
            var box = $('aiup-error');
            box.textContent = msg;
            box.style.display = msg ? 'block' : 'none';
        }

        window.openAiUpgrade = function () {
            showError('');
            show('plans');
            overlay.classList.add('open');
            overlay.setAttribute('aria-hidden', 'false');
        };
        window.closeAiUpgrade = function () {
            stopTimers();
            overlay.classList.remove('open');
            overlay.setAttribute('aria-hidden', 'true');
        };
        window.backToAiPlans = function () {
            stopTimers();
            $('aiup-qr-img').style.opacity = '1';
            showError('');
            show('plans');
        };

        function startCountdown(seconds) {
            var box = $('aiup-count'), timeEl = $('aiup-count-time');
            if (!seconds || seconds <= 0) { box.style.display = 'none'; return; }
            box.style.display = 'inline-block';
            box.classList.remove('is-urgent');
            box.innerHTML = '⏳ Mã hết hạn sau <span id="aiup-count-time">--:--</span>';
            var deadline = Date.now() + seconds * 1000;
            function tick() {
                var left = Math.max(0, Math.round((deadline - Date.now()) / 1000));
                var t = $('aiup-count-time');
                if (t) { t.textContent = String(Math.floor(left / 60)).padStart(2, '0') + ':' + String(left % 60).padStart(2, '0'); }
                box.classList.toggle('is-urgent', left <= 60);
                if (left <= 0) { expired(); }
            }
            countTimer = setInterval(tick, 1000);
            tick();
        }
        function expired() {
            stopTimers();
            $('aiup-count').textContent = '⌛ Mã đã hết hạn. Em bấm "Chọn gói khác" để tạo mã mới nhé.';
            $('aiup-qr-img').style.opacity = '0.15';
            $('aiup-pay-status').textContent = 'Đơn đã hết hạn thanh toán.';
        }
        function poll(code) {
            fetch(STATUS_URL.replace('__CODE__', encodeURIComponent(code)), { headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    if (d.is_active) {
                        stopTimers();
                        show('done');
                        setTimeout(function () { window.location.reload(); }, 2200);
                    } else if (d.is_expired) {
                        expired();
                    }
                })
                .catch(function () { /* mất mạng tạm thời: lần hỏi sau sẽ thử lại */ });
        }

        window.startAiUpgradePayment = function (btn) {
            if (btn.disabled) { return; }
            showError('');
            var original = btn.innerHTML;
            btn.disabled = true;
            btn.textContent = '⏳ Đang tạo mã thanh toán...';
            activeBtn = btn;

            var body = new FormData();
            body.append('_token', CSRF);
            body.append('payment_method', 'payos');

            fetch(ORDER_URL.replace('__SLUG__', encodeURIComponent(btn.getAttribute('data-slug'))), {
                method: 'POST',
                body: body,
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
            })
                .then(function (r) { return r.json().then(function (data) { return { ok: r.ok, data: data }; }); })
                .then(function (res) {
                    btn.disabled = false;
                    btn.innerHTML = original;
                    if (!res.ok || !res.data.ok) {
                        showError(res.data.message || 'Chưa tạo được mã thanh toán. Em thử lại sau ít phút nhé!');
                        return;
                    }
                    var d = res.data;
                    $('aiup-qr-img').src = d.qr_url;
                    $('aiup-qr-img').style.opacity = '1';
                    $('aiup-pay-name').textContent = d.order.package_name;
                    $('aiup-pay-code').textContent = '#' + d.order.code;
                    $('aiup-pay-amount').textContent = d.order.price_formatted;
                    $('aiup-pay-status').textContent = '⚡ Đang chờ thanh toán...';
                    show('pay');
                    stopTimers();
                    startCountdown(d.expires_in_seconds);
                    pollTimer = setInterval(function () { poll(d.order.code); }, 2500);
                })
                .catch(function () {
                    btn.disabled = false;
                    btn.innerHTML = original;
                    showError('Có lỗi kết nối. Em kiểm tra mạng rồi thử lại nhé!');
                });
        };

        overlay.addEventListener('click', function (e) { if (e.target === overlay) { window.closeAiUpgrade(); } });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && overlay.classList.contains('open')) { window.closeAiUpgrade(); } });
    })();
</script>
@endif
