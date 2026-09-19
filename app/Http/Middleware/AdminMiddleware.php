<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Tên là admin nhưng giáo viên cũng được qua nếu canAccessAdmin() trả về true.
        abort_unless($request->user()?->canAccessAdmin(), 403);

        return $next($request);
    }
}
