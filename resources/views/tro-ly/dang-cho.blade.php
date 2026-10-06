<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Trợ lý IC3 đang chờ</title>
    <style>
        :root { --bg-1: #6d28d9; --bg-2: #2563eb; --card: #ffffff; --ink: #1e1b4b; --muted: #475569; }
        @media (prefers-color-scheme: dark) {
            :root:not([data-theme="light"]) { --card: #1e1b4b; --ink: #f8fafc; --muted: #cbd5e1; }
        }
        :root[data-theme="dark"] { --card: #1e1b4b; --ink: #f8fafc; --muted: #cbd5e1; }
        html, body { margin: 0; min-height: 100%; }
        body {
            background: linear-gradient(135deg, var(--bg-1), var(--bg-2));
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            display: flex; align-items: center; justify-content: center; padding: 16px; box-sizing: border-box; min-height: 100vh;
        }
        .card {
            background: var(--card); color: var(--ink);
            border: 3.5px solid #ffffff; border-radius: 22px; padding: 32px 28px; max-width: 460px; text-align: center;
            box-shadow: 0 16px 36px rgba(0,0,0,0.18), inset 0 -6px 0 rgba(0,0,0,0.15);
        }
        .icon { font-size: 56px; line-height: 1; margin-bottom: 12px; }
        h1 { font-size: 22px; margin: 0 0 10px; }
        p { color: var(--muted); font-size: 16px; line-height: 1.55; margin: 0; }
    </style>
</head>
<body>
    <main class="card">
        <div class="icon" aria-hidden="true">🤖</div>
        <h1>Trợ lý IC3 đang chờ bạn</h1>
        <p>Bạn cứ quay lại cửa sổ trò chuyện nhé. Khi bạn nhờ mở trang nào, trợ lý sẽ chuyển ngay tới trang đó ở tab này.</p>
    </main>
</body>
</html>
