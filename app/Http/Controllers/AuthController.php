<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        if ($user->status === 'expired' || ($user->expires_at && $user->expires_at->isPast())) {
            return back()->withErrors(['login' => 'Tài khoản của bạn đã hết hạn sử dụng. Vui lòng liên hệ để gia hạn gói.'])->onlyInput('login');
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
     * Đăng xuất tài khoản và xóa phiên làm việc
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
