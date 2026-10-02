{{-- Bảng gọi điện tư vấn qua Stringee (có ghi âm) cho trang Live Chat. Chỉ Quản trị viên tổng mới dùng. --}}
@if(auth()->user()?->isAdmin())
<style>
    .call-overlay { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.55); z-index: 10050; display: none; align-items: center; justify-content: center; padding: 16px; }
    .call-overlay.open { display: flex; }
    .call-card { width: min(460px, 100%); max-height: 92vh; overflow-y: auto; background: #fff; border: 3.5px solid #fff; border-radius: 22px; box-shadow: 0 16px 36px rgba(0,0,0,0.18), inset 0 -6px 0 rgba(0,0,0,0.08); }
    .call-head { background: linear-gradient(135deg, #7c3aed, #0ea5e9); color: #fff; padding: 18px 20px; border-radius: 18px 18px 0 0; display: flex; justify-content: space-between; align-items: center; }
    .call-head h3 { margin: 0; font-size: 17px; font-weight: 900; }
    .call-close { background: rgba(255,255,255,0.2); border: 0; color: #fff; width: 30px; height: 30px; border-radius: 50%; font-size: 16px; cursor: pointer; }
    .call-body { padding: 18px 20px; display: flex; flex-direction: column; gap: 12px; }
    .call-who { text-align: center; }
    .call-who .name { font-size: 18px; font-weight: 900; color: #0f172a; }
    .call-status { text-align: center; font-weight: 800; color: #7c3aed; font-size: 14px; }
    .call-timer { text-align: center; font-size: 30px; font-weight: 900; color: #0f172a; font-variant-numeric: tabular-nums; }
    .call-input { width: 100%; padding: 10px 12px; border: 2px solid #cbd5e1; border-radius: 12px; font-size: 15px; font-weight: 700; box-sizing: border-box; }
    .call-note { width: 100%; padding: 10px 12px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 13px; box-sizing: border-box; resize: vertical; min-height: 60px; }
    .call-notice { background: #fef3c7; border: 2px dashed #f59e0b; color: #92400e; border-radius: 12px; padding: 10px 12px; font-size: 12.5px; font-weight: 700; line-height: 1.5; }
    .call-actions { display: flex; gap: 10px; justify-content: center; }
    .call-btn { border: 0; border-radius: 14px; padding: 11px 18px; font-size: 14px; font-weight: 900; color: #fff; cursor: pointer; box-shadow: 0 4px 0 rgba(0,0,0,0.2); transition: transform .12s; }
    .call-btn:hover { transform: translateY(-2px); }
    .call-btn:active { transform: translateY(3px); box-shadow: none; }
    .call-btn[disabled] { opacity: .5; cursor: not-allowed; }
    .call-btn.go { background: linear-gradient(135deg, #10b981, #059669); }
    .call-btn.stop { background: linear-gradient(135deg, #ef4444, #dc2626); }
    .call-btn.mute { background: linear-gradient(135deg, #64748b, #475569); }
    .call-history-title { font-size: 13px; font-weight: 900; color: #475569; margin-top: 4px; }
    .call-history-item { background: #f8fafc; border: 2px solid #e2e8f0; border-radius: 12px; padding: 9px 11px; font-size: 12.5px; color: #334155; display: flex; flex-direction: column; gap: 6px; }
    .call-history-item audio { width: 100%; height: 34px; }
    .call-history-empty { color: #94a3b8; font-size: 12.5px; text-align: center; padding: 6px; }
</style>

<div class="call-overlay" id="call-overlay" aria-hidden="true">
    <div class="call-card" role="dialog" aria-labelledby="call-title">
        <div class="call-head">
            <h3 id="call-title">📞 Gọi điện tư vấn</h3>
            <button type="button" class="call-close" onclick="closeCallPanel()" title="Đóng">✕</button>
        </div>
        <div class="call-body">
            <div class="call-who">
                <div class="name" id="call-name">Khách hàng</div>
            </div>
            <input type="tel" id="call-phone-input" class="call-input" placeholder="Số điện thoại khách, ví dụ 0901234567" inputmode="tel">
            <div class="call-notice">
                🎙️ Cuộc gọi được ghi âm để minh bạch chất lượng hỗ trợ. Hãy thông báo cho khách trước khi trao đổi nhé!
            </div>
            <div class="call-status" id="call-status">Sẵn sàng gọi</div>
            <div class="call-timer" id="call-timer" style="display:none;">00:00</div>
            <textarea id="call-note" class="call-note" placeholder="Ghi chú nội dung cuộc gọi (lưu khi kết thúc)..."></textarea>
            <div class="call-actions">
                <button type="button" class="call-btn go" id="call-btn-start" onclick="startSupportCall()">📞 Bắt đầu gọi</button>
                <button type="button" class="call-btn mute" id="call-btn-mute" onclick="toggleCallMute()" style="display:none;">🎤 Tắt mic</button>
                <button type="button" class="call-btn stop" id="call-btn-hangup" onclick="hangupSupportCall()" style="display:none;">📵 Kết thúc</button>
            </div>
            <div class="call-history-title">🗂️ Lịch sử cuộc gọi &amp; ghi âm</div>
            <div id="call-history"><div class="call-history-empty">Chưa có cuộc gọi nào.</div></div>
        </div>
    </div>
</div>
<audio id="call-remote-audio" autoplay></audio>

<script src="https://cdn.stringee.com/sdk/web/latest/stringee-web-sdk.min.js" defer></script>
<script>
(function () {
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
    const URLS = {
        token: @json(route('admin.calls.token')),
        start: @json(route('admin.calls.start')),
        update: @json(route('admin.calls.update', ['call' => '__ID__'])),
        history: @json(route('admin.calls.index', ['supportMessage' => '__ID__'])),
    };
    let client = null, activeCall = null, dbCallId = null, timerId = null, answeredAt = null, muted = false, ended = false;

    const $ = (id) => document.getElementById(id);
    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const api = (url, method, body) => fetch(url, {
        method,
        headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF},
        body: body ? JSON.stringify(body) : undefined,
    }).then(async (r) => {
        const data = await r.json().catch(() => ({}));
        if (!r.ok) throw new Error(data.message || 'Có lỗi xảy ra, vui lòng thử lại.');
        return data;
    });
    const setStatus = (t) => { $('call-status').textContent = t; };
    const fmt = (s) => String(Math.floor(s / 60)).padStart(2, '0') + ':' + String(s % 60).padStart(2, '0');

    function currentCard() {
        return document.querySelector(`.ms-conv-item[data-id="${currentChatMsgId}"]`) || document.querySelector('.ms-conv-item.active');
    }

    async function loadHistory() {
        const box = $('call-history');
        if (!currentChatMsgId) return;
        try {
            const {calls} = await api(URLS.history.replace('__ID__', currentChatMsgId), 'GET');
            box.innerHTML = calls.length ? calls.map((c) => `
                <div class="call-history-item">
                    <div><b>${esc(c.status_label)}</b> · ${esc(c.started_at)} · ${fmt(c.duration || 0)}${c.admin ? ' · ' + esc(c.admin) : ''}</div>
                    ${c.note ? `<div>📝 ${esc(c.note)}</div>` : ''}
                    ${c.has_recording ? `<audio controls preload="none" src="${esc(c.recording_url)}"></audio>` : (c.status === 'ended' ? '<div style="color:#94a3b8;">Ghi âm đang được xử lý, mở lại sau ít phút.</div>' : '')}
                </div>`).join('') : '<div class="call-history-empty">Chưa có cuộc gọi nào.</div>';
        } catch (e) { /* bỏ qua lỗi tải lịch sử */ }
    }

    // Gọi từ nút 📞 trên header chat và nút "Gọi Ngay" ở khung thông tin; trả về false để chặn tel: khi mở bảng.
    window.openCallPanel = function (ev) {
        ev?.preventDefault();
        const card = currentCard();
        $('call-name').textContent = card?.dataset.name || 'Khách hàng';
        $('call-phone-input').value = card?.dataset.phone || '';
        resetUi();
        $('call-overlay').classList.add('open');
        loadHistory();
        return false;
    };

    window.closeCallPanel = function () {
        if (activeCall && !ended && !confirm('Cuộc gọi đang diễn ra. Bạn có muốn kết thúc và đóng bảng không?')) return;
        if (activeCall && !ended) hangupSupportCall();
        $('call-overlay').classList.remove('open');
    };

    function resetUi() {
        if (activeCall && !ended) return;
        setStatus('Sẵn sàng gọi');
        $('call-timer').style.display = 'none';
        $('call-btn-start').style.display = '';
        $('call-btn-start').disabled = false;
        $('call-btn-mute').style.display = 'none';
        $('call-btn-hangup').style.display = 'none';
        $('call-note').value = '';
        $('call-phone-input').disabled = false;
    }

    function ensureClient(token) {
        return new Promise((resolve, reject) => {
            if (client && client.hasConnected) return resolve(client);
            if (typeof StringeeClient === 'undefined') return reject(new Error('Chưa tải được thư viện Stringee. Vui lòng kiểm tra mạng rồi thử lại.'));
            client = new StringeeClient();
            client.on('authen', (res) => res.r === 0 ? resolve(client) : reject(new Error('Stringee từ chối đăng nhập (' + res.message + '). Kiểm tra lại khóa API.')));
            client.on('disconnect', () => { client = null; });
            client.connect(token);
        });
    }

    window.startSupportCall = async function () {
        const phone = $('call-phone-input').value.trim();
        if (!phone) return setStatus('Vui lòng nhập số điện thoại khách.');
        $('call-btn-start').disabled = true;
        setStatus('Đang kết nối tổng đài...');
        try {
            const cfg = await api(URLS.token, 'GET');
            if (!cfg.configured) {
                setStatus(cfg.message);
                $('call-btn-start').disabled = false;
                return;
            }
            const created = await api(URLS.start, 'POST', {support_message_id: currentChatMsgId || null, phone});
            dbCallId = created.call.id;
            ended = false; muted = false; answeredAt = null;
            const c = await ensureClient(cfg.token);

            const call = new StringeeCall2(c, cfg.from_number, created.to_number, false);
            call.customData = String(dbCallId);
            activeCall = call;
            bindCallEvents(call);
            call.makeCall((res) => {
                if (res.r !== 0) return finishCall('failed', 'Không thể gọi: ' + res.message);
                api(URLS.update.replace('__ID__', dbCallId), 'PATCH', {stringee_call_id: call.callId}).catch(() => {});
            });

            setStatus('Đang gọi...');
            $('call-phone-input').disabled = true;
            $('call-btn-start').style.display = 'none';
            $('call-btn-hangup').style.display = '';
            $('call-btn-mute').style.display = '';
            $('call-btn-mute').textContent = '🎤 Tắt mic';
        } catch (e) {
            setStatus(e.message);
            $('call-btn-start').disabled = false;
            if (dbCallId && !activeCall) api(URLS.update.replace('__ID__', dbCallId), 'PATCH', {status: 'failed', end_reason: e.message.slice(0, 100)}).catch(() => {});
        }
    };

    function bindCallEvents(call) {
        call.on('addlocalstream', () => {});
        call.on('addremotestream', (stream) => { const a = $('call-remote-audio'); a.srcObject = null; a.srcObject = stream; });
        call.on('signalingstate', (state) => {
            // 1: đang gọi, 2: đổ chuông, 3: đã bắt máy, 4: máy bận, 5: kết thúc
            if (state.code === 2) setStatus('Đang đổ chuông...');
            if (state.code === 3) onAnswered();
            if (state.code === 4) finishCall('missed', 'Máy bận hoặc khách từ chối.');
            if (state.code === 5) finishCall('ended', 'Cuộc gọi đã kết thúc.');
        });
        call.on('mediastate', (state) => { if (state.code === 1 && !answeredAt) onAnswered(); });
        call.on('error', (info) => finishCall('failed', 'Lỗi cuộc gọi: ' + (info.message || '')));
    }

    function onAnswered() {
        if (answeredAt) return;
        answeredAt = Date.now();
        setStatus('Đang đàm thoại (có ghi âm)');
        $('call-timer').style.display = '';
        timerId = setInterval(() => { $('call-timer').textContent = fmt(Math.floor((Date.now() - answeredAt) / 1000)); }, 500);
        api(URLS.update.replace('__ID__', dbCallId), 'PATCH', {status: 'answered'}).catch(() => {});
    }

    function finishCall(status, message) {
        if (ended) return;
        ended = true;
        clearInterval(timerId);
        setStatus(answeredAt ? message : (status === 'ended' ? 'Khách không nghe máy.' : message));
        $('call-btn-hangup').style.display = 'none';
        $('call-btn-mute').style.display = 'none';
        $('call-btn-start').style.display = '';
        $('call-btn-start').disabled = false;
        $('call-btn-start').textContent = '📞 Gọi lại';
        $('call-phone-input').disabled = false;
        const id = dbCallId;
        api(URLS.update.replace('__ID__', id), 'PATCH', {status, end_reason: message.slice(0, 100), note: $('call-note').value.trim() || null})
            .then(() => setTimeout(loadHistory, 1500)).catch(() => {});
        activeCall = null;
    }

    window.hangupSupportCall = function () {
        if (!activeCall) return;
        try { activeCall.hangup(() => {}); } catch (e) {}
        finishCall('ended', 'Đã kết thúc cuộc gọi.');
    };

    window.toggleCallMute = function () {
        if (!activeCall) return;
        muted = !muted;
        activeCall.mute(muted);
        $('call-btn-mute').textContent = muted ? '🔇 Bật mic' : '🎤 Tắt mic';
    };
})();
</script>
@endif
