{{--
    Khung Trợ lý AI cho học sinh/giáo viên đã đăng nhập.
    - Có gói Trợ lý AI: hỏi AI về kết quả, bài đã làm, bài nên làm tiếp; AI có thể hiện nút mở trang hoặc mở bài thi.
    - Chưa có gói: hiện nút xem gói để mua. Hội thoại được lưu cho tài khoản này.
--}}
@auth
@php
    $aiUser = auth()->user();
    $aiEntitled = $aiUser->hasAiAssistant();
@endphp
@if($aiUser->isStudent() || $aiUser->isTeacher())
    <button type="button" class="sc-fab" id="ai-fab" aria-label="Trợ lý AI" title="Trợ lý AI"
            style="bottom: 84px; background: linear-gradient(135deg, #7c3aed, #ec4899);">🤖</button>

    @include('partials.floating-panel')

    <div class="sc-panel" id="ai-panel" role="dialog" aria-label="Trợ lý AI" style="bottom: 150px;">
        <div class="sc-grip" aria-hidden="true"></div>
        <div class="sc-head">
            <span class="sc-avatar">🤖</span>
            <div class="sc-head-text">
                <b>Trợ lý AI IC3</b>
                <small>{{ $aiEntitled ? 'Hỏi AI và mở nhanh trang cần thao tác' : 'Cần gói Trợ lý AI để dùng' }}</small>
            </div>
            <button type="button" class="sc-close" id="ai-close" aria-label="Đóng">✕</button>
        </div>

        @if($aiEntitled)
            <div class="sc-body" id="ai-body">
                <div class="sc-msg admin">
                    Chào {{ $aiUser->name }}! Bạn hỏi mình nhé, ví dụ:<br>
                    • "Hôm nay em làm bài nào rồi?"<br>
                    • "Bài nào nên làm tiếp?"<br>
                    • "Mở trang bài học cho mình"
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
                <a href="{{ route('pricing.index') }}" target="_blank" rel="noopener" class="sc-send" style="width:auto; padding:0 16px; text-decoration:none; display:inline-flex; align-items:center;">Xem gói Trợ lý AI</a>
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
                const fmt = (s) => esc(stripInlineLinks(s)).replace(/\*\*(.+?)\*\*/g, '<b>$1</b>').replace(/\n/g, '<br>');
                let aiTyping = false;

                const style = document.createElement('style');
                style.textContent = `
                    #ai-panel .sc-msg.typing { width:auto; min-width:86px; padding:10px 13px; }
                    #ai-panel .ai-typing-label { font-size:12px; font-weight:900; color:#7c3aed; margin-bottom:6px; }
                    #ai-panel .ai-typing-dots { display:inline-flex; align-items:center; gap:4px; height:14px; }
                    #ai-panel .ai-typing-dots span { width:6px; height:6px; border-radius:999px; background:#a855f7; animation: aiTypingPulse .9s infinite ease-in-out; }
                    #ai-panel .ai-typing-dots span:nth-child(2) { animation-delay:.14s; }
                    #ai-panel .ai-typing-dots span:nth-child(3) { animation-delay:.28s; }
                    #ai-panel .sc-action { display:inline-flex; align-items:center; justify-content:center; margin-top:7px; padding:6px 11px; border-radius:10px; background:linear-gradient(135deg,#7c3aed,#ec4899); color:#fff; font-weight:900; text-decoration:none; font-size:12.5px; box-shadow:0 2px 0 #5b21b6; }
                    #ai-panel .sc-action:hover { transform:translateY(-1px); }
                    @keyframes aiTypingPulse { 0%,80%,100% { opacity:.35; transform:translateY(0); } 40% { opacity:1; transform:translateY(-3px); } }
                `;
                document.head.appendChild(style);

                let state = { id: null };
                try { state = Object.assign(state, JSON.parse(localStorage.getItem(storeKey) || '{}')); } catch (e) {}
                const save = () => { try { localStorage.setItem(storeKey, JSON.stringify(state)); } catch (e) {} };

                // Nút mở trang/làm bài: chỉ nhận đường dẫn cùng trang web
                function actionHtml(action) {
                    if (!action || !action.url || !action.label) return '';
                    try {
                        const url = new URL(action.url, window.location.origin);
                        if (url.origin !== window.location.origin) return '';
                        return `<a href="${esc(url.pathname + url.search + url.hash)}" target="_blank" rel="noopener" class="sc-action">${esc(action.label)} →</a>`;
                    } catch (e) { return ''; }
                }

                function looksLikeOpenRequest(text) {
                    return /(mở|mo trang|open|vào|vao|chuyển|chuyen|đưa|dua|tới|toi|sang|làm bài|lam bai)/iu.test(String(text || ''));
                }

                function prepareActionTab(text) {
                    if (!looksLikeOpenRequest(text)) return null;
                    const tab = window.open('', '_blank');
                    if (!tab) return null;
                    try {
                        tab.opener = null;
                        tab.document.write('<!doctype html><title>Trợ lý AI IC3</title><body style="font-family:system-ui;padding:24px">Trợ lý AI đang mở trang phù hợp...</body>');
                    } catch (e) {}

                    return tab;
                }

                function openActionIfNeeded(action, preparedTab = null) {
                    if (!action || !action.auto || !action.url) return;
                    try {
                        const url = new URL(action.url, window.location.origin);
                        if (url.origin !== window.location.origin) return;
                        const path = url.pathname + url.search + url.hash;
                        if (preparedTab && !preparedTab.closed) {
                            preparedTab.location.href = path;
                            return;
                        }
                        window.open(path, '_blank', 'noopener');
                    } catch (e) {}
                }

                function stripInlineLinks(text) {
                    return String(text || '')
                        .replace(/https?:\/\/mos\.app\/[^\s)]+/gi, '')
                        .replace(/\bmos\.app\/[^\s)]+/gi, '')
                        .replace(/\s{2,}/g, ' ')
                        .trim();
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
                    if (aiTyping) {
                        const typing = document.createElement('div');
                        typing.className = 'sc-msg admin typing';
                        typing.innerHTML = `
                            <div class="ai-typing-label">🤖 Trợ lý AI đang nhập...</div>
                            <div class="ai-typing-dots"><span></span><span></span><span></span></div>
                        `;
                        body.appendChild(typing);
                    }
                    body.scrollTop = body.scrollHeight;
                }

                async function loadHistory() {
                    if (!state.id) return;
                    try {
                        const res = await fetch(urls.check + '?id=' + encodeURIComponent(state.id), { headers: { 'Accept': 'application/json' } });
                        const data = await res.json();
                        if (data.ok) {
                            state.lastHistory = data.conversation_history || [];
                            render(state.lastHistory);
                        }
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
                    aiTyping = true;
                    const preparedTab = prepareActionTab(text);
                    const currentHistory = state.lastHistory || [];
                    render(currentHistory.concat([{ sender: 'user', text }]));
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
                        state.lastHistory = data.conversation_history || [];
                        aiTyping = false;
                        render(state.lastHistory);
                        openActionIfNeeded(data.bot_action, preparedTab);
                        if (preparedTab && !preparedTab.closed && !data.bot_action?.auto) {
                            preparedTab.close();
                        }
                    } catch (err) {
                        if (preparedTab && !preparedTab.closed) preparedTab.close();
                        aiTyping = false;
                        render(state.lastHistory || []);
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
