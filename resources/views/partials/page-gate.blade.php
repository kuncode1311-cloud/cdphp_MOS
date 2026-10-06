{{--
    Cổng chờ tải trang: ẩn trang và hiện vòng quay cho tới khi giao diện đã sẵn sàng (CSS, phông chữ, nội dung),
    để người dùng không thấy cảnh nội dung "thô" rồi nhảy sang giao diện đẹp.
    Đặt ngay đầu <head> của mọi trang đầy đủ. Có chốt an toàn: tối đa vài giây là hiện trang, không bao giờ kẹt màn hình trống.
--}}
<style id="page-gate-css">
    html.pg-wait { background: #0d5c96; }
    html.pg-wait body { visibility: hidden !important; }
    html.pg-wait::before {
        content: "";
        position: fixed;
        inset: 0;
        z-index: 2147483646;
        background: radial-gradient(circle at 50% 38%, #1a86d1 0%, #0d5c96 55%, #073a61 100%);
    }
    html.pg-wait::after {
        content: "";
        position: fixed;
        top: 50%;
        left: 50%;
        width: 54px;
        height: 54px;
        margin: -27px 0 0 -27px;
        z-index: 2147483647;
        border-radius: 50%;
        border: 6px solid rgba(255, 255, 255, 0.28);
        border-top-color: #ffd23f;
        animation: pgSpin 0.8s linear infinite;
    }
    html.pg-ready body { animation: pgFadeIn 0.25s ease-out; }
    @keyframes pgSpin { to { transform: rotate(360deg); } }
    @keyframes pgFadeIn { from { opacity: 0; } to { opacity: 1; } }
    @media (prefers-reduced-motion: reduce) {
        html.pg-wait::after { animation-duration: 2s; }
        html.pg-ready body { animation: none; }
    }
</style>
<script>
    (function () {
        var root = document.documentElement;
        root.classList.add('pg-wait');

        var started = Date.now();
        var shown = false;
        var domReady = document.readyState !== 'loading';
        var fontsReady = false;
        var fullyLoaded = document.readyState === 'complete';

        function reveal() {
            if (shown) { return; }
            shown = true;
            // Không dùng requestAnimationFrame: tab chạy nền sẽ tạm dừng nó và trang bị kẹt ở trạng thái ẩn
            root.classList.remove('pg-wait');
            root.classList.add('pg-ready');
        }

        // Hiện trang khi: nội dung + phông chữ đã sẵn sàng VÀ (tải xong hẳn, hoặc đã chờ tối đa 2,5 giây)
        function check() {
            if (shown || !domReady || !fontsReady) { return; }
            if (fullyLoaded || Date.now() - started >= 2500) { reveal(); }
        }

        document.addEventListener('DOMContentLoaded', function () { domReady = true; check(); });
        window.addEventListener('load', function () { fullyLoaded = true; check(); });
        window.addEventListener('pageshow', function (e) { if (e.persisted) { reveal(); } });

        // Phông chữ: chờ tối đa 1,5 giây, quá hạn thì cứ hiện trang
        var fontsPromise = (document.fonts && document.fonts.ready) ? document.fonts.ready : Promise.resolve();
        var fontsTimeout = new Promise(function (resolve) { setTimeout(resolve, 1500); });
        Promise.race([fontsPromise, fontsTimeout]).then(function () { fontsReady = true; check(); });

        // Kiểm tra lại sau 2,5 giây (đủ thời gian chờ ảnh) và chốt an toàn 4 giây: chắc chắn hiện trang
        setTimeout(check, 2500);
        setTimeout(reveal, 4000);
    })();
</script>
<noscript><style>html.pg-wait body { visibility: visible !important; } html.pg-wait::before, html.pg-wait::after { display: none; }</style></noscript>
