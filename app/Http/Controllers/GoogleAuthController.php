<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

/**
 * Controller Xử Lý Đăng Nhập Bằng Tài Khoản Google (Google OAuth 2.0)
 * 
 * Luồng hoạt động:
 * 1. Chuyển hướng người dùng sang trang cấp quyền của Google (redirectToGoogle).
 * 2. Tiếp nhận callback từ Google, lấy thông tin hồ sơ (handleGoogleCallback).
 * 3. Tự động liên kết tài khoản nếu email đã có, hoặc tạo tài khoản mới cho học sinh.
 * 4. Đăng nhập và điều hướng thông minh theo phân quyền (Admin / Giáo viên / Học sinh).
 */
class GoogleAuthController extends Controller
{
    /**
     * Chuyển hướng người dùng sang Google OAuth 2.0 consent screen
     */
    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Nhận phản hồi callback từ Google sau khi người dùng xác thực
     */
    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        try {
            /** @var \Laravel\Socialite\Two\User $googleUser */
            $googleUser = Socialite::driver('google')->user();
        } catch (Exception $e) {
            Log::warning('Lỗi hoặc hủy xác thực Google OAuth: ' . $e->getMessage());
            return redirect()->route('login')->withErrors([
                'login' => 'Đăng nhập Google không thành công hoặc bạn đã hủy xác thực. Vui lòng thử lại!',
            ]);
        }

        $googleId = $googleUser->getId();
        $email = $googleUser->getEmail();
        $name = $googleUser->getName() ?? $googleUser->getNickname() ?? 'Học viên Google';
        $avatar = $googleUser->getAvatar();

        if (empty($email)) {
            return redirect()->route('login')->withErrors([
                'login' => 'Không thể lấy thông tin email từ tài khoản Google của bạn.',
            ]);
        }

        // 1. Tìm tài khoản theo google_id
        $user = User::where('google_id', $googleId)->first();

        // 2. Nếu chưa có google_id, tìm theo email để liên kết
        if (! $user) {
            $user = User::where('email', $email)->first();

            if ($user) {
                // Tài khoản đã có sẵn trong hệ thống -> liên kết thêm google_id & avatar
                $user->update([
                    'google_id' => $googleId,
                    'avatar' => $user->avatar ?: $avatar,
                ]);
            }
        }

        // 3. Kiểm tra bảo mật: Nếu email chưa từng được cấp tài khoản -> Chặn truy cập
        if (! $user) {
            Log::warning("Đăng nhập Google bị từ chối do email chưa có trong hệ thống: {$email}");
            return redirect()->route('login')->withErrors([
                'login' => "Email ({$email}) chưa có trong hệ thống hoặc chưa được kích hoạt. Vui lòng liên hệ Thầy/Cô hoặc đăng ký gói để được cấp tài khoản!",
            ]);
        }

        // Cập nhật avatar từ Google nếu tài khoản chưa có ảnh đại diện
        if (empty($user->avatar) && ! empty($avatar)) {
            $user->update(['avatar' => $avatar]);
        }

        // 4. Kiểm tra trạng thái tài khoản (chờ duyệt, tạm khóa, hết hạn)
        if ($user->status === 'pending') {
            return redirect()->route('login')->withErrors([
                'login' => 'Tài khoản của Thầy/Cô đang chờ duyệt kích hoạt. Vui lòng liên hệ Quản trị viên.',
            ]);
        }

        if ($user->status === 'suspended') {
            return redirect()->route('login')->withErrors([
                'login' => 'Tài khoản của bạn đang bị tạm khóa. Vui lòng liên hệ quản trị viên.',
            ]);
        }

        if ($user->status === 'expired' || ($user->expires_at && $user->expires_at->isPast())) {
            return redirect()->route('login')->withErrors([
                'login' => 'Tài khoản của bạn đã hết hạn sử dụng. Vui lòng liên hệ để gia hạn gói.',
            ]);
        }

        // 5. Đăng nhập người dùng vào hệ thống
        Auth::login($user, true);

        // 6. Tái tạo Session ID chống Session Fixation
        $request->session()->regenerate();

        // 7. Điều hướng thông minh theo vai trò
        return $user->canAccessAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->intended(route('home'));
    }
}
