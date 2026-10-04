<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Đá người dùng ra khỏi hệ thống ngay khi gói/hạn dùng hết hiệu lực, kể cả khi họ đang đăng nhập sẵn.
 */
class EnsureSubscriptionActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ($message = $user->subscriptionBlockedMessage())) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['login' => $message]);
        }

        return $next($request);
    }
}
