<?php

namespace App\Http\Controllers;

use App\Mail\PasswordChangedAlertMail;
use App\Mail\PasswordResetOtpMail;
use App\Services\BrevoMailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Controller Hồ Sơ & Tài Khoản Cá Nhân (Profile Controller)
 * 
 * Quản lý thông tin tài khoản và đổi mật khẩu an toàn với xác thực Email OTP 2 lớp.
 */
class ProfileController extends Controller
{
    /**
     * Gửi mã OTP xác thực đổi mật khẩu qua Email
     */
    public function sendOtp(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user, 401);

        if (! $user->email) {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản chưa có địa chỉ Email để nhận mã OTP.',
            ], 422);
        }

        // Chống spam: Giới hạn 60 giây gửi 1 lần
        $rateLimitKey = 'pwd_otp_time_' . $user->id;
        if (Cache::has($rateLimitKey)) {
            $remaining = Cache::get($rateLimitKey . '_expire_at', 60);
            return response()->json([
                'success' => false,
                'message' => 'Bạn thao tác quá nhanh. Vui lòng đợi thêm một lát trước khi yêu cầu mã mới.',
            ], 429);
        }

        // Tạo mã OTP ngẫu nhiên 6 chữ số
        $otp = (string) random_int(100000, 999999);

        // Lưu vào Cache: Hiệu lực 10 phút
        Cache::put('pwd_otp_' . $user->id, $otp, now()->addMinutes(10));
        Cache::put($rateLimitKey, true, now()->addSeconds(60));

        // Gửi email xác thực OTP qua hệ thống Mailer (Ưu tiên Brevo API v3, Fallback sang SMTP)
        $sent = false;
        if (BrevoMailService::isConfigured()) {
            try {
                $htmlContent = view('emails.password-otp', [
                    'otp' => $otp,
                    'user' => $user,
                ])->render();

                $sent = BrevoMailService::send(
                    toEmail: $user->email,
                    toName: $user->name,
                    subject: '🛡️ [IC3 Adventure] Mã OTP Xác Thực Đổi Mật Khẩu: ' . $otp,
                    htmlContent: $htmlContent
                );
            } catch (\Throwable $errBrevo) {
                Log::warning("Gửi OTP qua Brevo API không thành công: " . $errBrevo->getMessage());
            }
        }

        // Nếu chưa gửi được qua Brevo API -> fallback sang Laravel Mailer (SMTP)
        if (! $sent) {
            try {
                Mail::to($user->email)->send(new PasswordResetOtpMail($otp, $user));
                Log::info("Đã gửi OTP đổi mật khẩu cho user ID {$user->id} ({$user->email}) thành công qua SMTP.");
            } catch (\Throwable $e) {
                Log::error("Không thể gửi email OTP cho {$user->email}: " . $e->getMessage());
                Cache::forget($rateLimitKey); // Mở lại giới hạn để người dùng có thể gửi lại
                return response()->json([
                    'success' => false,
                    'message' => "Không thể gửi email OTP tới hộp thư [{$user->email}]. Chi tiết: " . $e->getMessage() . ". Hãy kiểm tra lại cấu hình Brevo/SMTP hoặc đổi sang email thật.",
                ], 500);
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Mã OTP gồm 6 chữ số đã được gửi đến email [{$user->email}]. Vui lòng mở Hộp thư đến (hoặc hòm thư Spam) để lấy mã xác thực!",
        ]);
    }

    /**
     * Cập nhật địa chỉ Email của tài khoản người dùng
     */
    public function updateEmail(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user, 401);

        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
        ], [
            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email' => 'Địa chỉ email không đúng định dạng.',
            'email.unique' => 'Địa chỉ email này đã được sử dụng bởi một tài khoản khác.',
        ]);

        $newEmail = strtolower(trim($validated['email']));
        $user->email = $newEmail;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => "Đã cập nhật email nhận thông báo thành công: {$newEmail}",
            'email' => $newEmail,
        ]);
    }

    /**
     * Cập nhật mật khẩu tài khoản người dùng kèm xác thực mã OTP
     */
    public function updatePassword(Request $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 401);

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'otp' => ['required', 'string', 'size:6'],
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.min' => 'Mật khẩu mới phải có ít nhất 6 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu mới không trùng khớp.',
            'otp.required' => 'Vui lòng nhập mã OTP 6 số đã được gửi qua email.',
            'otp.size' => 'Mã OTP phải có đúng 6 chữ số.',
        ]);

        // 1. Kiểm tra mã OTP từ Cache
        $cachedOtp = Cache::get('pwd_otp_' . $user->id);
        if (! $cachedOtp || ! hash_equals((string) $cachedOtp, trim($validated['otp']))) {
            // Chặn dò mã OTP: nhập sai 5 lần thì hủy mã, phải bấm "Nhận OTP" để lấy mã mới
            $failKey = 'pwd_otp_fail_' . $user->id;
            Cache::put($failKey, (int) Cache::get($failKey, 0) + 1, now()->addMinutes(10));
            if ((int) Cache::get($failKey) >= 5) {
                Cache::forget('pwd_otp_' . $user->id);
                Cache::forget($failKey);
            }

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mã OTP không chính xác hoặc đã hết hạn (10 phút). Vui lòng bấm "Nhận OTP" để lấy mã mới.',
                    'errors' => ['otp' => ['Mã OTP không đúng hoặc đã hết hiệu lực.']],
                ], 422);
            }

            return back()->withErrors(['otp' => 'Mã OTP không chính xác hoặc đã hết hạn.']);
        }

        // 2. Kiểm tra mật khẩu hiện tại
        if (! Hash::check($validated['current_password'], $user->password)) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mật khẩu hiện tại chưa chính xác.',
                    'errors' => ['current_password' => ['Mật khẩu hiện tại chưa chính xác.']],
                ], 422);
            }

            return back()->withErrors(['current_password' => 'Mật khẩu hiện tại chưa chính xác.']);
        }

        // 3. Cập nhật mật khẩu mới và hủy mã OTP đã dùng
        $user->forceFill([
            'password' => Hash::make($validated['password']),
        ])->save();

        Cache::forget('pwd_otp_' . $user->id);
        Cache::forget('pwd_otp_time_' . $user->id);

        // 4. Gửi email cảnh báo bảo mật đổi mật khẩu thành công (Ưu tiên Brevo API v3, Fallback sang SMTP)
        $changedAt = now()->format('H:i:s d/m/Y');
        $ipAddress = (string) ($request->ip() ?? 'Không xác định');
        $userAgent = (string) ($request->userAgent() ?? 'Trình duyệt Web');

        $alertSent = false;
        if (BrevoMailService::isConfigured()) {
            try {
                $htmlContent = view('emails.password-changed', [
                    'user' => $user,
                    'changedAt' => $changedAt,
                    'ipAddress' => $ipAddress,
                    'userAgent' => $userAgent,
                ])->render();

                $alertSent = BrevoMailService::send(
                    toEmail: $user->email,
                    toName: $user->name,
                    subject: '🛡️ [Cảnh báo bảo mật] Mật khẩu tài khoản IC3 Adventure vừa được thay đổi',
                    htmlContent: $htmlContent
                );
            } catch (\Throwable $errBrevo) {
                Log::warning("Gửi email cảnh báo đổi mật khẩu qua Brevo API không thành công: " . $errBrevo->getMessage());
            }
        }

        if (! $alertSent) {
            try {
                Mail::to($user->email)->send(new PasswordChangedAlertMail($user, $changedAt, $ipAddress, $userAgent));
                Log::info("Đã gửi email cảnh báo đổi mật khẩu cho user ID {$user->id} ({$user->email}) thành công qua SMTP.");
            } catch (\Throwable $e) {
                Log::error("Không thể gửi email cảnh báo đổi mật khẩu cho {$user->email}: " . $e->getMessage());
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Chúc mừng bạn! Mật khẩu đã được đổi thành công và an toàn tuyệt đối.',
            ]);
        }

        return back()->with('ok', 'Đổi mật khẩu thành công.');
    }
}
