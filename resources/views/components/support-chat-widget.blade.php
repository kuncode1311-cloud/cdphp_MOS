{{--
    Khung chat hỗ trợ trong cổng học sinh.
    - Học sinh mua lẻ (không thuộc giáo viên nào): chat trực tiếp với Ban Quản Trị, tin nhắn hiện ở trang Tin Nhắn Messenger.
    - Học sinh do giáo viên quản lý: không có ô chat, chỉ có lời nhắc liên hệ giáo viên của mình.
--}}
@auth
@php($scUser = auth()->user())
@if($scUser->isStudent())
    @if($scUser->created_by)
        @php($scTeacher = $scUser->teacher)
        <button type="button" class="sc-fab" id="sc-fab" aria-label="Hỏi giáo viên">👩‍🏫 Hỏi giáo viên</button>
        <div class="sc-panel" id="sc-panel" role="dialog" aria-label="Liên hệ giáo viên">
            <div class="sc-head">
                <div><b>Cần giúp đỡ?</b><small>Giáo viên của em sẽ hỗ trợ</small></div>
                <button type="button" class="sc-close" id="sc-close" aria-label="Đóng">✕</button>
            </div>
            <div class="sc-teacher">
                <span>Em hãy nhờ giáo viên của mình nhé:</span>
                <span class="sc-teacher-name">{{ $scTeacher?->name ?? 'Giáo viên phụ trách' }}</span>
                @if($scTeacher?->email)<span>✉️ {{ $scTeacher->email }}</span>@endif
                <span class="sc-hint">Em hỏi trực tiếp ở lớp hoặc nhắn qua kênh lớp học của thầy cô để được trả lời nhanh nhất.</span>
            </div>
        </div>
        <script>
            (function () {
                const panel = document.getElementById('sc-panel');
                document.getElementById('sc-fab').addEventListener('click', () => panel.classList.toggle('open'));
                document.getElementById('sc-close').addEventListener('click', () => panel.classList.remove('open'));
            })();
        </script>
    @else
        <button type="button" class="sc-fab" id="sc-fab" aria-label="Chat hỗ trợ">💬 Chat hỗ trợ<span class="sc-dot"></span></button>
        <div class="sc-panel" id="sc-panel" role="dialog" aria-label="Chat với Ban Quản Trị">
            <div class="sc-head">
                <div><b>Chat với Ban Quản Trị</b><small>IC3 Quest luôn sẵn sàng giúp em</small></div>
                <button type="button" class="sc-close" id="sc-close" aria-label="Đóng">✕</button>
            </div>
            <div class="sc-body" id="sc-body">
                <div class="sc-msg admin">Chào {{ $scUser->name }}! Em cần hỗ trợ gì về tài khoản hoặc gói luyện thi, cứ nhắn cho mình nhé 😊</div>
            </div>
            <form class="sc-foot" id="sc-form" autocomplete="off">
                <input type="tel" class="sc-input" id="sc-phone" placeholder="Số điện thoại (không bắt buộc, để tư vấn viên gọi lại)" maxlength="30">
                <div class="sc-row">
                    <input type="text" class="sc-input" id="sc-text" placeholder="Nhập tin nhắn..." maxlength="2000" required>
                    <button type="submit" class="sc-send" id="sc-send">Gửi</button>
                </div>
            </form>
        </div>
        <script>
            (function () {
                const user = @json(['id' => $scUser->id, 'name' => $scUser->name, 'email' => $scUser->email]);
                const urls = { send: @json(route('support.message.send')), check: @json(route('support.message.check')) };
                const storeKey = 'ic3_support_chat_' + user.id;
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
                const $ = (id) => document.getElementById(id);
                const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
                let state = { id: null, phone: '', seenAdmin: 0 };
                try { state = Object.assign(state, JSON.parse(localStorage.getItem(storeKey) || '{}')); } catch (e) {}
                const save = () => { try { localStorage.setItem(storeKey, JSON.stringify(state)); } catch (e) {} };
                const isOpen = () => $('sc-panel').classList.contains('open');

                function render(history) {
                    const body = $('sc-body');
                    const first = body.firstElementChild;
                    body.innerHTML = '';
                    if (first) body.appendChild(first);
                    (history || []).forEach((t) => {
                        const div = document.createElement('div');
                        div.className = 'sc-msg ' + (t.sender === 'user' ? 'me' : 'admin');
                        div.innerHTML = (t.image ? `<a href="${esc(t.image)}" target="_blank"><img src="${esc(t.image)}" alt="Ảnh"></a>` : '')
                            + (t.text ? `<div>${esc(t.text)}</div>` : '')
                            + `<div class="sc-time">${esc(t.created_at || t.time || '')}</div>`;
                        body.appendChild(div);
                    });
                    body.scrollTop = body.scrollHeight;
                }

                function applyHistory(history) {
                    const adminCount = (history || []).filter((t) => t.sender === 'admin').length;
                    if (adminCount > state.seenAdmin && !isOpen()) $('sc-fab').classList.add('has-new');
                    if (isOpen()) state.seenAdmin = adminCount;
                    save();
                    render(history);
                }

                async function poll() {
                    if (!state.id) return;
                    try {
                        const res = await fetch(urls.check + '?id=' + encodeURIComponent(state.id), { headers: { 'Accept': 'application/json' } });
                        const data = await res.json();
                        if (data.ok) applyHistory(data.conversation_history || []);
                    } catch (e) { /* mạng chập chờn thì thử lại lần sau */ }
                }

                function toggle(open) {
                    $('sc-panel').classList.toggle('open', open);
                    if (open) {
                        $('sc-fab').classList.remove('has-new');
                        $('sc-phone').value = state.phone || '';
                        poll();
                        setTimeout(() => $('sc-text').focus(), 50);
                    }
                }
                $('sc-fab').addEventListener('click', () => toggle(!isOpen()));
                $('sc-close').addEventListener('click', () => toggle(false));

                $('sc-form').addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const text = $('sc-text').value.trim();
                    if (!text) return;
                    const phone = $('sc-phone').value.trim();
                    const btn = $('sc-send');
                    btn.disabled = true;
                    try {
                        const res = await fetch(urls.send, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                            body: JSON.stringify({ name: user.name, email: user.email, phone: phone || null, message: text, parent_id: state.id }),
                        });
                        const data = await res.json();
                        if (!res.ok || !data.ok) throw new Error(data.message || 'Chưa gửi được tin nhắn, em thử lại nhé.');
                        state.id = data.message_id;
                        state.phone = phone;
                        $('sc-text').value = '';
                        applyHistory(data.conversation_history || []);
                    } catch (err) {
                        alert(err.message);
                    } finally {
                        btn.disabled = false;
                    }
                });

                if (state.id) poll();
                setInterval(poll, 8000);
            })();
        </script>
    @endif
@endif
@endauth
