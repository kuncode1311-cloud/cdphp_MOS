{{-- Tab dùng chung cho các trợ lý (chữ và giọng nói): một tab duy nhất, tái sử dụng cho cả phiên.
     Mở sẵn bằng window.troLyTab.prepare() khi người dùng bấm (trình duyệt không chặn), rồi dùng open(url) khi cần chuyển trang. --}}
<script>
    (function () {
        var NAME = 'ic3-tro-ly';
        var PLACEHOLDER = {{ Js::from(route('tro-ly.cho')) }};
        var win = null;

        function alive() {
            return win && !win.closed;
        }

        window.troLyTab = {
            // Gọi khi người dùng bấm (cú bấm cho phép mở tab). Nếu tab đã mở thì dùng lại, không mở thêm.
            // Tab hiện trang chờ thân thiện, không để trang trắng.
            prepare: function () {
                try {
                    win = window.open(PLACEHOLDER, NAME);
                    if (win) { try { win.opener = null; } catch (e) {} }
                } catch (e) { win = null; }
                return !!alive();
            },
            // Chuyển tab riêng tới đường dẫn. Trả về false nếu trình duyệt chặn (khi chưa có tab mở sẵn).
            open: function (url) {
                try {
                    if (alive()) {
                        win.location.href = url;
                    } else {
                        win = window.open(url, NAME);
                    }
                    if (win) { try { win.opener = null; } catch (e) {} }
                    return !!alive();
                } catch (e) { return false; }
            },
            // Đóng tab nếu nó vẫn đang ở trang chờ (chưa chuyển trang), để không để lại tab trống.
            closeIfBlank: function () {
                try {
                    if (alive() && win.location.pathname === new URL(PLACEHOLDER, window.location.origin).pathname) { win.close(); }
                } catch (e) {}
                if (!alive()) { win = null; }
            },
        };
    })();
</script>
