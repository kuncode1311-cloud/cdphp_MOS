{{-- Khung chat nổi có thể kéo theo thanh tiêu đề và đổi kích thước bằng góc dưới bên phải. Vị trí và kích thước được nhớ trên trình duyệt. --}}
<style>
    .sc-grip {
        position: absolute; right: 3px; bottom: 3px; width: 16px; height: 16px;
        cursor: nwse-resize; touch-action: none; opacity: 0.55;
        border-right: 3px solid #7c3aed; border-bottom: 3px solid #7c3aed; border-radius: 0 0 5px 0;
    }
    .sc-grip:hover { opacity: 1; }
    .sc-panel.open, .chat-popover.open { display: flex; flex-direction: column; }
    .sc-panel .sc-body, .chat-popover .zalo-chat-body { flex: 1 1 auto; min-height: 0; }
</style>
<script>
    if (!window.makeFloatingPanel) {
        window.makeFloatingPanel = function (panel, opts) {
            if (!panel || panel.dataset.floating === '1') return;
            panel.dataset.floating = '1';

            const head = panel.querySelector(opts.head);
            const grip = panel.querySelector(opts.grip);
            if (!head || !grip) return;

            const MIN_W = opts.minW || 280;
            const MIN_H = opts.minH || 320;
            const clamp = (v, lo, hi) => Math.min(Math.max(v, lo), Math.max(lo, hi));
            const read = () => { try { return JSON.parse(localStorage.getItem(opts.key) || 'null'); } catch (e) { return null; } };
            const write = (l) => { try { localStorage.setItem(opts.key, JSON.stringify(l)); } catch (e) {} };

            function place(l) {
                if (!l) return;
                const w = clamp(l.w, MIN_W, window.innerWidth - 16);
                const h = clamp(l.h, MIN_H, window.innerHeight - 16);
                const x = clamp(l.x, 8, window.innerWidth - w - 8);
                const y = clamp(l.y, 8, window.innerHeight - h - 8);
                Object.assign(panel.style, { position: 'fixed', width: w + 'px', height: h + 'px', left: x + 'px', top: y + 'px', right: 'auto', bottom: 'auto' });
            }

            function snapshot() {
                const r = panel.getBoundingClientRect();
                return { x: r.left, y: r.top, w: r.width, h: r.height };
            }

            place(read());
            window.addEventListener('resize', () => place(read()));

            let mode = null;
            let offset = null;
            head.style.touchAction = 'none';
            head.style.cursor = 'move';
            head.addEventListener('pointerdown', (e) => {
                if (e.target.closest('button, a, input, textarea')) return;
                const r = panel.getBoundingClientRect();
                mode = 'move';
                offset = { dx: e.clientX - r.left, dy: e.clientY - r.top };
                head.setPointerCapture(e.pointerId);
                e.preventDefault();
            });

            grip.addEventListener('pointerdown', (e) => {
                mode = 'resize';
                grip.setPointerCapture(e.pointerId);
                e.preventDefault();
                e.stopPropagation();
            });

            function onMove(e) {
                if (!mode) return;
                const r = panel.getBoundingClientRect();
                if (mode === 'move') {
                    const x = clamp(e.clientX - offset.dx, 0, window.innerWidth - r.width);
                    const y = clamp(e.clientY - offset.dy, 0, window.innerHeight - r.height);
                    Object.assign(panel.style, { position: 'fixed', left: x + 'px', top: y + 'px', right: 'auto', bottom: 'auto' });
                } else {
                    const w = clamp(e.clientX - r.left, MIN_W, window.innerWidth - r.left - 8);
                    const h = clamp(e.clientY - r.top, MIN_H, window.innerHeight - r.top - 8);
                    Object.assign(panel.style, { width: w + 'px', height: h + 'px' });
                }
            }

            function onEnd() {
                if (!mode) return;
                mode = null;
                write(snapshot());
            }

            [head, grip].forEach((el) => {
                el.addEventListener('pointermove', onMove);
                el.addEventListener('pointerup', onEnd);
                el.addEventListener('pointercancel', onEnd);
            });
        };
    }
</script>
