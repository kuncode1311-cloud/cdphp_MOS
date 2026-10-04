<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Chỉ cho Quản trị viên tổng đi qua.
 * Giáo viên vẫn vào được Khu quản trị nhưng không được dùng các chức năng trung tâm (gói, đơn, chat, Telegram, AI, Studio...).
 */
class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->isAdmin(), 403, 'Chức năng này chỉ dành cho Quản trị viên tổng.');

        return $next($request);
    }
}
