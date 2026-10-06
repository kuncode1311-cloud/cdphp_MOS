<?php

namespace App\Http\Controllers;

use App\Mail\PasswordChangedAlertMail;
use App\Mail\PasswordResetOtpMail;
use App\Models\User;
use App\Services\MailDelivery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Controller Xác Thực & Phân Quyền Đăng Nhập (Auth Controller)
 * 
 * Chức năng: Xử lý đăng nhập linh hoạt bằng Email (Quản trị viên) hoặc Mã học sinh (Student Code),
 * tự động điều hướng đúng giao diện theo vai trò (Admin -> Dashboard, Học sinh -> Trang chủ luyện thi),
 * và xử lý đăng xuất an toàn.
 */
class AuthController extends Controller
{
    private const FORGOT_SESSION_KEY = 'forgot_password_user_id';
    private const FORGOT_CONFIRM_SESSION_KEY = 'forgot_password_confirm_user_id';
    private const FORGOT_VERIFIED_SESSION_KEY = 'forgot_password_verified_user_id';
    private const FORGOT_OTP_MINUTES = 10;
    private const FORGOT_OTP_MAX_ATTEMPTS = 5;

    /**
     * Hiển thị giao diện Đăng nhập
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Xử lý xác thực thông tin đăng nhập
     * 
     * - Tìm theo email, mã học sinh hoặc tên ngắn ghép với các đuôi email bên dưới.
     * - Tái tạo session an toàn chống tấn công Session Fixation.
     * - Phân luồng điều hướng: Admin vào trang Quản trị, Học sinh vào màn hình luyện thi.
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Xác thực dữ liệu form đăng nhập
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $loginInput = trim($credentials['login']);

        // 2. Tìm tài khoản linh hoạt: Email, Mã học sinh, hoặc tên bí danh (admin, teacher, hs001...)
        $user = \App\Models\User::where('email', $loginInput)
            ->orWhere('student_code', $loginInput)
            ->orWhere('email', $loginInput.'@ic3.test')
            ->orWhere('email', $loginInput.'@student.ic3.local')
            ->first();

        // 3. Thực hiện kiểm tra mật khẩu
        if (! $user || ! \Illuminate\Support\Facades\Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors(['login' => 'Tài khoản hoặc mật khẩu chưa đúng.'])->onlyInput('login');
        }

        // 3.1. Kiểm tra trạng thái tài khoản (Chờ duyệt / Khóa / Hết hạn)
        if ($user->status === 'pending') {
            $latestOrder = $user->latestPackageOrder;
            $msg = 'Tài khoản của Thầy/Cô đang chờ xác nhận thanh toán';
            if ($latestOrder) {
                $msg .= " cho đơn hàng #{$latestOrder->code}";
            }
            $msg .= '. Vui lòng hoàn tất chuyển khoản hoặc liên hệ Quản trị viên để được kích hoạt ngay.';
            return back()->withErrors(['login' => $msg])->onlyInput('login');
        }

        if ($user->status === 'suspended') {
            return back()->withErrors(['login' => 'Tài khoản của bạn đang bị tạm khóa. Vui lòng liên hệ quản trị viên hoặc giáo viên.'])->onlyInput('login');
        }

        // Hết hạn/khóa theo gói: với học sinh của giáo viên, xét gói của giáo viên phụ trách
        if ($message = $user->subscriptionBlockedMessage()) {
            return back()->withErrors(['login' => $message])->onlyInput('login');
        }

        Auth::login($user, $request->boolean('remember'));

        // 4. Khởi tạo lại Session
        // Đổi ID phiên sau khi đăng nhập để không tiếp tục dùng ID phiên cũ.
        $request->session()->regenerate();

        // 5. Điều hướng theo vai trò người dùng (Admin/GV vào Quản trị, Học sinh vào Luyện tập)
        return $request->user()->canAccessAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->intended(route('home'));
    }

    /**
     * Hiển thị màn hình quên mật khẩu cho khách chưa đăng nhập.
     */
    public function forgotPassword(): View
    {
        $userId = (int) session(self::FORGOT_SESSION_KEY, 0);
        $user = $userId > 0 ? User::find($userId) : null;
        $confirmUserId = (int) session(self::FORGOT_CONFIRM_SESSION_KEY, 0);
        $confirmUser = $confirmUserId > 0 ? User::find($confirmUserId) : null;
        $verifiedUserId = (int) session(self::FORGOT_VERIFIED_SESSION_KEY, 0);
        $verifiedUser = $verifiedUserId > 0 ? User::find($verifiedUserId) : null;

        return view('auth.forgot-password', [
            'hasOtpSession' => $user !== null,
            'hasConfirmSession' => $confirmUser !== null,
            'hasVerifiedSession' => $verifiedUser !== null,
            'maskedEmail' => $user?->email ? $this->maskEmail($user->email) : null,
            'verifiedMaskedEmail' => $verifiedUser?->email ? $this->maskEmail($verifiedUser->email) : null,
            'confirmMaskedEmail' => $confirmUser?->email ? $this->maskEmail($confirmUser->email) : null,
            'confirmMaskedPhone' => $confirmUser?->maskedPhone(),
        ]);
    }

    /**
     * Tìm tài khoản, gửi OTP qua email đã đăng ký và lưu trạng thái vào session.
     */
    public function sendForgotPasswordOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'login' => ['required', 'string', 'max:255'],
        ], [
            'login.required' => 'Vui lòng nhập email, Mã HS hoặc tên đăng nhập.',
        ]);

        $user = $this->findLoginUser(trim($validated['login']));
        if (! $user || ! $user->hasDeliverableEmail()) {
            return back()
                ->withErrors(['login' => 'Chưa tìm thấy tài khoản có email nhận OTP thật. Vui lòng kiểm tra lại hoặc nhắn Ban Quản Trị hỗ trợ.'])
                ->onlyInput('login');
        }

        if (! filter_var($validated['login'], FILTER_VALIDATE_EMAIL)) {
            session()->forget(self::FORGOT_SESSION_KEY);
            session([self::FORGOT_CONFIRM_SESSION_KEY => $user->id]);

            return redirect()->route('password.forgot')
                ->with('ok', 'Mình đã tìm thấy tài khoản. Bạn nhập đúng email hoặc SĐT đã đăng ký để nhận OTP nhé.');
        }

        return $this->sendResetOtp($user);
    }

    /**
     * Xác minh email hoặc SĐT đã đăng ký trước khi gửi OTP cho tài khoản tìm bằng Mã HS.
     */
    public function confirmForgotPasswordContact(Request $request): RedirectResponse
    {
        $user = User::find((int) session(self::FORGOT_CONFIRM_SESSION_KEY, 0));
        if (! $user) {
            return redirect()->route('password.forgot')
                ->withErrors(['login' => 'Phiên xác minh đã hết hạn. Bạn nhập lại tài khoản hoặc Mã HS nhé.']);
        }

        $validated = $request->validate([
            'contact' => ['required', 'string', 'max:255'],
        ], [
            'contact.required' => 'Vui lòng nhập đúng email hoặc SĐT đã đăng ký.',
        ]);

        $contact = trim($validated['contact']);
        $normalizedPhone = \App\Models\SupportMessage::normalizePhone($contact);
        $emailMatched = mb_strtolower($contact) === mb_strtolower((string) $user->email);
        $phoneMatched = $normalizedPhone !== null && $normalizedPhone === (string) $user->phone;

        if (! $emailMatched && ! $phoneMatched) {
            return back()
                ->withErrors(['contact' => 'Thông tin xác minh chưa khớp. Bạn nhập đúng email hoặc SĐT đã đăng ký nhé.'])
                ->onlyInput('contact');
        }

        session()->forget(self::FORGOT_CONFIRM_SESSION_KEY);

        return $this->sendResetOtp($user);
    }

    /**
     * Tạo, lưu và gửi OTP đặt lại mật khẩu.
     */
    private function sendResetOtp(User $user): RedirectResponse
    {
        $rateKey = 'forgot_pwd_otp_time_' . $user->id;
        if (Cache::has($rateKey)) {
            session()->forget(self::FORGOT_CONFIRM_SESSION_KEY);
            session([self::FORGOT_SESSION_KEY => $user->id]);

            return redirect()->route('password.forgot')
                ->with('ok', 'Mã OTP đã được gửi gần đây. Bạn kiểm tra Hộp thư đến hoặc Spam, rồi bấm tiếp tục để nhập mã.');
        }

        $otp = (string) random_int(100000, 999999);
        Cache::put('forgot_pwd_otp_' . $user->id, hash('sha256', $otp), now()->addMinutes(self::FORGOT_OTP_MINUTES));
        Cache::forget('forgot_pwd_otp_fail_' . $user->id);
        Cache::put($rateKey, true, now()->addMinute());

        if (! MailDelivery::send($user, new PasswordResetOtpMail($otp, $user), 'OTP quên mật khẩu')) {
            Cache::forget('forgot_pwd_otp_' . $user->id);
            Cache::forget($rateKey);

            return back()
                ->withErrors(['login' => 'Hiện chưa gửi được email OTP. Vui lòng thử lại sau ít phút hoặc nhắn Ban Quản Trị.'])
                ->onlyInput('login');
        }

        session([self::FORGOT_SESSION_KEY => $user->id]);

        return redirect()->route('password.forgot')->with('ok', 'Mã OTP 6 số đã được gửi tới email ' . $this->maskEmail($user->email) . '.');
    }

    /**
     * Kiểm tra OTP trước khi mở bước đặt mật khẩu mới.
     */
    public function verifyForgotPasswordOtp(Request $request): RedirectResponse
    {
        $user = User::find((int) session(self::FORGOT_SESSION_KEY, 0));
        if (! $user) {
            return redirect()->route('password.forgot')
                ->withErrors(['login' => 'Phiên khôi phục đã hết hạn. Bạn nhập lại tài khoản để nhận OTP mới nhé.']);
        }

        $validated = $request->validate([
            'otp' => ['required', 'digits:6'],
        ], [
            'otp.required' => 'Vui lòng nhập mã OTP 6 số.',
            'otp.digits' => 'Mã OTP phải gồm đúng 6 chữ số.',
        ]);

        $storedOtp = Cache::get('forgot_pwd_otp_' . $user->id);
        if (! $storedOtp || ! hash_equals((string) $storedOtp, hash('sha256', $validated['otp']))) {
            $failKey = 'forgot_pwd_otp_fail_' . $user->id;
            Cache::put($failKey, (int) Cache::get($failKey, 0) + 1, now()->addMinutes(self::FORGOT_OTP_MINUTES));
            if ((int) Cache::get($failKey) >= self::FORGOT_OTP_MAX_ATTEMPTS) {
                Cache::forget('forgot_pwd_otp_' . $user->id);
                Cache::forget($failKey);
            }

            return back()->withErrors(['otp' => 'Mã OTP không đúng hoặc đã hết hạn. Bạn kiểm tra email và thử lại nhé.']);
        }

        Cache::forget('forgot_pwd_otp_' . $user->id);
        Cache::forget('forgot_pwd_otp_fail_' . $user->id);
        Cache::forget('forgot_pwd_otp_time_' . $user->id);
        session()->forget(self::FORGOT_SESSION_KEY);
        session([self::FORGOT_VERIFIED_SESSION_KEY => $user->id]);

        return redirect()->route('password.forgot')->with('ok', 'Xác minh OTP thành công. Bạn đặt mật khẩu mới ở bước cuối nhé.');
    }

    /**
     * Đặt mật khẩu mới sau khi OTP đã được xác minh.
     */
    public function resetForgotPassword(Request $request): RedirectResponse
    {
        $user = User::find((int) session(self::FORGOT_VERIFIED_SESSION_KEY, 0));
        if (! $user) {
            return redirect()->route('password.forgot')
                ->withErrors(['login' => 'Bạn cần xác minh OTP trước khi đặt mật khẩu mới.']);
        }

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.min' => 'Mật khẩu mới cần ít nhất 6 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu mới chưa trùng khớp.',
        ]);

        $user->forceFill(['password' => Hash::make($validated['password'])])->save();
        session()->forget(self::FORGOT_VERIFIED_SESSION_KEY);

        MailDelivery::send($user, new PasswordChangedAlertMail(
            $user,
            now()->format('H:i:s d/m/Y'),
            (string) $request->ip(),
            (string) $request->userAgent(),
        ), 'cảnh báo đổi mật khẩu');

        return redirect()->route('login')->with('ok', 'Đổi mật khẩu thành công! Bạn đăng nhập bằng mật khẩu mới nhé.');
    }

    /**
     * Xóa phiên quên mật khẩu để người dùng nhập lại tài khoản từ đầu.
     */
    public function restartForgotPassword(): RedirectResponse
    {
        session()->forget([
            self::FORGOT_SESSION_KEY,
            self::FORGOT_CONFIRM_SESSION_KEY,
            self::FORGOT_VERIFIED_SESSION_KEY,
        ]);

        return redirect()->route('password.forgot')
            ->with('ok', 'Bạn có thể nhập lại tài khoản, Mã HS hoặc email từ đầu.');
    }

    /**
     * Đăng xuất tài khoản và xóa phiên làm việc
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Tìm tài khoản giống logic đăng nhập: email, Mã HS hoặc tên ngắn.
     */
    private function findLoginUser(string $loginInput): ?User
    {
        $lower = mb_strtolower($loginInput);

        return User::query()
            ->whereRaw('LOWER(email) = ?', [$lower])
            ->orWhere('student_code', $loginInput)
            ->orWhereRaw('LOWER(email) = ?', [$lower . '@ic3.test'])
            ->orWhereRaw('LOWER(email) = ?', [$lower . '@student.ic3.local'])
            ->first();
    }

    /**
     * Che email khi hiển thị để bảo vệ thông tin tài khoản.
     */
    private function maskEmail(string $email): string
    {
        [$local, $domain] = explode('@', $email, 2) + [1 => ''];
        $visible = mb_substr($local, 0, min(2, mb_strlen($local)));

        return $visible . str_repeat('•', max(3, mb_strlen($local) - mb_strlen($visible))) . '@' . $domain;
    }
}
