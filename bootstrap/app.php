<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\StudentMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    // Đặt tên middleware để dùng trong routes/web.php và nơi chuyển khách tới đăng nhập.
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'student' => StudentMiddleware::class,
        ]);
        $middleware->redirectGuestsTo('/dang-nhap');
        // Bỏ kiểm tra CSRF cho API heartbeat tiêu hao giờ chơi từ game offline, webhook PayOS, và bỏ hết trong môi trường test
        $csrfExcept = ['tro-choi/tieu-hao-thoi-gian', 'bang-gia/payos-webhook', 'api/*'];
        if (($_ENV['APP_ENV'] ?? '') === 'testing' || ($_SERVER['APP_ENV'] ?? '') === 'testing') {
            $csrfExcept = ['*'];
        }
        $middleware->validateCsrfTokens(except: $csrfExcept);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
