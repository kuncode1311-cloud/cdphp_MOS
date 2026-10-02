{{-- Giao diện Swagger UI cho tài liệu API; dữ liệu lấy từ /docs/api.json do Scramble sinh ra. --}}
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Swagger - Tài liệu API IC3 Quest</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui.css">
    <style>body { margin: 0; background: #fafafa; }</style>
</head>
<body>
<div id="swagger-ui"></div>
<script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
<script>
    window.ui = SwaggerUIBundle({
        url: @json(url('/docs/api.json')),
        dom_id: '#swagger-ui',
        deepLinking: true,
        tryItOutEnabled: false,
        persistAuthorization: false,
        // Gửi kèm cookie đăng nhập và mã CSRF khi bấm "Try it out"
        requestInterceptor: (req) => {
            req.credentials = 'include';
            req.headers['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;
            req.headers['X-Requested-With'] = 'XMLHttpRequest';
            return req;
        },
    });
</script>
</body>
</html>
