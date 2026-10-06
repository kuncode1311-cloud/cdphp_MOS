{{--
    Khung Trợ lý AI cho học sinh đã đăng nhập.
    - Có gói Trợ lý AI: hỏi AI về kết quả, bài đã làm, bài nên làm tiếp; AI có thể hiện nút mở trang hoặc mở bài thi.
    - Chưa có gói: hiện nút xem gói để mua. Hội thoại được lưu cho tài khoản này.
--}}
@auth
@php
    $aiUser = auth()->user();
    $aiEntitled = $aiUser->hasAiAssistant();
@endphp
@if($aiUser->isStudent())
    <button type="button" class="sc-fab" id="ai-fab" aria-label="Trợ lý AI" title="Trợ lý AI"
            style="bottom: 84px; background: linear-gradient(135deg, #7c3aed, #ec4899);">🤖</button>

    @include('partials.floating-panel')

    <div class="sc-panel" id="ai-panel" role="dialog" aria-label="Trợ lý AI" style="bottom: 150px;">
        <div class="sc-grip" aria-hidden="true"></div>
        <div class="sc-head">
            <span class="sc-avatar">🤖</span>
            <div class="sc-head-text">
                <b>Trợ lý AI IC3</b>
                <small>{{ $aiEntitled ? 'Hỏi về kết quả và bài nên làm tiếp' : 'Cần gói Trợ lý AI để dùng' }}</small>
            </div>
            <button type="button" class="sc-close" id="ai-close" aria-label="Đóng">✕</button>
        </div>

        @if($aiEntitled)
            <div class="sc-body" id="ai-body">
                <div class="sc-msg admin">
                    Chào {{ $aiUser->name }}! Em hỏi mình nhé, ví dụ:<br>
                    • "Hôm nay em làm bài nào rồi?"<br>
                    • "Bài nào em nên làm tiếp?"<br>
                    • "Mở bài thi cho em"
                </div>
            </div>
            <form class="sc-foot" id="ai-form" autocomplete="off">
                <div class="sc-row">
                    <input type="text" class="sc-input" id="ai-text" placeholder="Hỏi Trợ lý AI..." maxlength="1000" required>
                    <button type="submit" class="sc-send" id="ai-send" aria-label="Gửi">➤</button>
                </div>
            </form>
        @else
            <div class="sc-body">
                <div class="sc-msg admin">
                    Tra cứu kết quả và tiến độ học tập là tính năng của <b>gói Trợ lý AI</b>. Em nhờ phụ huynh xem gói và đăng ký nhé!
                </div>
            </div>
            <div class="sc-foot" style="text-align:center;">
                <a href="{{ route('pricing.index') }}" class="sc-send" style="width:auto; padding:0 16px; text-decoration:none; display:inline-flex; align-items:center;">Xem gói Trợ lý AI</a>
            </div>
        @endif
    </div>

    <script>
        // Kéo khung theo thanh tiêu đề, kéo góc dưới để đổi kích thước; nhớ vị trí trên trình duyệt này
        makeFloatingPanel(document.getElementById('ai-panel'), { head: '.sc-head', grip: '.sc-grip', key: 'ic3_ai_panel_layout', minW: 300, minH: 340 });
    </script>

    @if($aiEntitled)
        <script>
            (function () {
                const user = @json(['id' => $aiUser->id, 'name' => $aiUser->name]);
                const urls = { send: @json(route('support.message.send')), check: @json(route('support.message.check')) };
                const storeKey = 'ic3_ai_chat_' + user.id;
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
                const $ = (id) => document.getElementById(id);
                const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
                const fmt = (s) => esc(s).replace(/\*\*(.+?)\*\*/g, '<b>$1</b>').replace(/\n/g, '<br>');

                let state = { id: null };
                try { state = Object.assign(state, JSON.parse(localStorage.getItem(storeKey) || '{}')); } catch (e) {}
                const save = () => { try { localStorage.setItem(storeKey, JSON.stringify(state)); } catch (e) {} };

                // Nút mở trang/làm bài: chỉ nhận đường dẫn cùng trang web
                function actionHtml(action) {
                    if (!action || !action.url || !action.label) return '';
                    try {
                        const url = new URL(action.url, window.location.origin);
                        if (url.origin !== window.location.origin) return '';
                        return `<a href="${esc(url.pathname + url.search)}" class="sc-action" style="display:inline-block;margin-top:6px;padding:6px 12px;border-radius:10px;background:#7c3aed;color:#fff;font-weight:800;text-decoration:none;font-size:13px;">${esc(action.label)} →</a>`;
                    } catch (e) { return ''; }
                }

                function render(history) {
                    const body = $('ai-body');
                    const first = body.firstElementChild;
                    body.innerHTML = '';
                    if (first) body.appendChild(first);
                    (history || []).forEach((t) => {
                        if (t.sender !== 'user' && t.sender !== 'bot') return;
                        const div = document.createElement('div');
                        div.className = 'sc-msg ' + (t.sender === 'user' ? 'me' : 'admin');
                        div.innerHTML = `<div>${fmt(t.text || '')}</div>` + (t.sender === 'bot' ? actionHtml(t.action) : '');
                        body.appendChild(div);
                    });
                    body.scrollTop = body.scrollHeight;
                }

                async function loadHistory() {
                    if (!state.id) return;
                    try {
                        const res = await fetch(urls.check + '?id=' + encodeURIComponent(state.id), { headers: { 'Accept': 'application/json' } });
                        const data = await res.json();
                        if (data.ok) render(data.conversation_history || []);
                    } catch (e) {}
                }

                $('ai-fab').addEventListener('click', () => {
                    const open = !$('ai-panel').classList.contains('open');
                    $('ai-panel').classList.toggle('open', open);
                    if (open) { loadHistory(); setTimeout(() => $('ai-text').focus(), 50); }
                });
                $('ai-close').addEventListener('click', () => $('ai-panel').classList.remove('open'));

                $('ai-form').addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const text = $('ai-text').value.trim();
                    if (!text) return;
                    $('ai-send').disabled = true;
                    try {
                        const res = await fetch(urls.send, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                            body: JSON.stringify({ name: user.name, message: text, channel: 'ai', parent_id: state.id }),
                        });
                        const data = await res.json();
                        if (!res.ok || !data.ok) throw new Error(data.message || 'Chưa gửi được, em thử lại nhé.');
                        state.id = data.message_id;
                        save();
                        $('ai-text').value = '';
                        render(data.conversation_history || []);
                    } catch (err) {
                        alert(err.message);
                    } finally {
                        $('ai-send').disabled = false;
                    }
                });
            })();
        </script>
    @endif
@endif
@endauth
