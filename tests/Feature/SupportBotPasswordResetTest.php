<?php

namespace Tests\Feature;

use App\Mail\PasswordChangedAlertMail;
use App\Mail\PasswordResetOtpMail;
use App\Models\SupportMessage;
use App\Models\User;
use App\Services\SupportBotService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Kiểm thử đầy đủ luồng đổi mật khẩu qua Trợ lý AI trong khung chat.
 * Mỗi test là một tình huống thật mà khách có thể gặp.
 */
class SupportBotPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private const EMAIL = 'hocsinh@gmail.com';
    private const OLD_PASSWORD = 'mat-khau-cu-123';
    private const NEW_PASSWORD = 'mat-khau-moi-456';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ValidateCsrfToken::class);
        config(['services.gemini.api_keys' => 'test-key']);
        config(['services.question_ai.base_url' => '', 'services.question_ai.model' => '']);
        Cache::flush();
        // Giả lập server đã cấu hình SMTP (thư vẫn bị Mail::fake chặn, không gửi thật)
        config(['mail.default' => 'smtp']);
        Mail::fake();
        Http::fake();
        // Trong test, admin mặc định không online để bot xử lý
        Cache::forget('support_admin_seen_at');
    }

    private function user(array $extra = []): User
    {
        return User::factory()->create(array_merge([
            'name' => 'Học Sinh Thử',
            'email' => self::EMAIL,
            'password' => Hash::make(self::OLD_PASSWORD),
        ], $extra));
    }

    /** Khách vãng lai luôn có SĐT để admin liên hệ (bắt buộc khi không đang khôi phục mật khẩu) */
    private function say(string $message): array
    {
        return $this->postJson(route('support.message.send'), [
            'name' => 'Khách',
            'contact' => '0912345678',
            'message' => $message,
            'channel' => 'ai',
        ])->assertOk()->json();
    }

    /** Lấy mã OTP từ email vừa gửi (test không đọc được hash trong cache) */
    private function lastOtp(): string
    {
        $otp = null;
        Mail::assertSent(PasswordResetOtpMail::class, function (PasswordResetOtpMail $mail) use (&$otp) {
            $otp = $mail->otp;

            return true;
        });

        return (string) $otp;
    }

    /**
     * Chuyển sang một trình duyệt khác: cookie phiên mới (đã mã hóa đúng định dạng) trỏ tới phiên chưa có dữ liệu.
     * Luồng khôi phục của trình duyệt cũ không thể đọc được từ đây.
     */
    private function newBrowser(): void
    {
        // Trong test, session store là singleton của cùng app nên phải xóa dữ liệu nhớ trong bộ nhớ
        // (trên máy chủ thật mỗi request là một tiến trình riêng nên không cần bước này)
        app('session.store')->flush();
        $sessionId = \Illuminate\Support\Str::random(40);
        $this->withCookie(config('session.cookie'), app('encrypter')->encrypt($sessionId, false));
    }

    private function startAndConfirm(string $identifier = self::EMAIL): void
    {
        $this->say('quên mật khẩu');
        $this->say($identifier);
        $this->say(self::EMAIL);
    }

    private function login(string $login, string $password)
    {
        return $this->post(route('login.store'), ['login' => $login, 'password' => $password]);
    }

    // =========================================================================
    // ✅ LUỒNG THÀNH CÔNG
    // =========================================================================

    public function test_doi_mat_khau_thanh_cong_va_dang_nhap_bang_mat_khau_moi(): void
    {
        $user = $this->user();

        $this->startAndConfirm();
        $otp = $this->lastOtp();
        $this->say($otp);
        $data = $this->say(self::NEW_PASSWORD);

        $this->assertFalse($data['flow_active']);
        $this->assertFalse($data['secret_next']);

        // Đăng nhập thật bằng mật khẩu mới qua form đăng nhập
        $this->login(self::EMAIL, self::NEW_PASSWORD)->assertRedirect();
        $this->assertAuthenticated();
        $this->post(route('logout'));

        // Mật khẩu cũ không còn dùng được
        $this->login(self::EMAIL, self::OLD_PASSWORD);
        $this->assertGuest();
    }

    public function test_sau_khi_doi_xong_gui_email_canh_bao_bao_mat(): void
    {
        $this->user();
        $this->startAndConfirm();
        $this->say($this->lastOtp());
        $this->say(self::NEW_PASSWORD);

        Mail::assertSent(PasswordChangedAlertMail::class, fn ($mail) => $mail->hasTo(self::EMAIL));
    }

    public function test_nhan_dien_bang_ma_hs_va_bang_biet_danh_dang_nhap(): void
    {
        $this->user(['student_code' => 'HS001', 'email' => 'hs001@ic3.test']);

        // Mã HS
        $this->say('quên mật khẩu');
        $data = $this->say('HS001');
        $this->assertStringContainsString('hs0••01@ic3.test', $data['bot_replies'][0]);

        // Biệt danh "hs001" (trang đăng nhập tự ghép @ic3.test), bắt đầu từ trình duyệt mới
        $this->newBrowser();
        $this->say('quên mật khẩu');
        $data = $this->say('hs001');
        $this->assertStringContainsString('hs', $data['bot_replies'][0]);
        $this->assertTrue($data['flow_active']);
    }

    // =========================================================================
    // 🚫 TRƯỜNG HỢP NHẬP SAI / KHÔNG HỢP LỆ
    // =========================================================================

    public function test_tai_khoan_khong_ton_tai_tra_loi_chung_va_khong_gui_mail(): void
    {
        $this->say('quên mật khẩu');
        $data = $this->say('khongco@gmail.com');

        $this->assertStringContainsString('chưa tìm thấy', $data['bot_replies'][0]);
        Mail::assertNothingSent();
    }

    public function test_dang_o_buoc_nhap_tai_khoan_ma_hoi_cach_nhap_thi_duoc_huong_dan_tu_nhien(): void
    {
        $this->say('quên pas');
        $data = $this->say('nhập sao?');
        $text = implode("\n", $data['bot_replies']);

        $this->assertTrue($data['flow_active']);
        $this->assertStringContainsString('email', $text);
        $this->assertStringContainsString('Mã HS', $text);
        $this->assertStringContainsString('không nhớ', $text);
        $this->assertStringNotContainsString('chưa tìm thấy tài khoản', $text);
        Mail::assertNothingSent();
    }

    public function test_co_brevo_thi_otp_gui_qua_brevo_api_giong_trang_ho_so(): void
    {
        // Máy chủ thật: cổng SMTP bị chặn, gửi bằng Brevo API
        Http::fake(['https://api.brevo.com/v3/smtp/email' => Http::response(['messageId' => '<id>'], 201), '*' => Http::response([], 200)]);
        config(['services.brevo.key' => 'test-fake-key', 'mail.default' => 'log']);
        $this->user();

        $this->say('quên mật khẩu');
        $this->say(self::EMAIL);
        $data = $this->say(self::EMAIL);

        $this->assertStringContainsString('đã gửi mã OTP', implode(' ', $data['bot_replies']));
        Http::assertSent(fn ($r) => $r->url() === 'https://api.brevo.com/v3/smtp/email'
            && ($r['to'][0]['email'] ?? null) === self::EMAIL
            && str_contains((string) ($r['subject'] ?? ''), 'Mã OTP'));
        Mail::assertNothingSent();
    }

    public function test_server_chua_cau_hinh_gui_mail_thi_noi_that_khong_bao_da_gui(): void
    {
        // MAIL_MAILER=log chỉ ghi thư vào file log, không gửi thật
        config(['mail.default' => 'log']);
        $this->user();

        $this->startAndConfirm();
        $data = $this->say('123456');

        $this->assertNull(Cache::get('support_bot_otp_' . User::where('email', self::EMAIL)->value('id')));
        Mail::assertNothingSent();
        $this->assertStringNotContainsString('đã gửi mã OTP', implode(' ', $data['bot_replies']));
    }

    public function test_nhap_sai_email_xac_nhan_khong_gui_otp(): void
    {
        $this->user();
        $this->say('quên mật khẩu');
        $this->say(self::EMAIL);
        $data = $this->say('nguoilade@gmail.com');

        $this->assertStringContainsString('chưa khớp', $data['bot_replies'][0]);
        Mail::assertNothingSent();
        $this->assertTrue($data['flow_active']);
    }

    public function test_email_xac_nhan_viet_hoa_thuong_van_duoc_chap_nhan(): void
    {
        $this->user();
        $this->say('quên mật khẩu');
        $this->say(self::EMAIL);
        $this->say('HocSinh@GMAIL.com');

        Mail::assertSent(PasswordResetOtpMail::class);
    }

    public function test_otp_sai_nhieu_lan_bi_huy_va_ma_cu_khong_dung_duoc_nua(): void
    {
        $user = $this->user();
        $this->startAndConfirm();
        $real = $this->lastOtp();
        $wrong = $real === '000000' ? '111111' : '000000';

        for ($i = 0; $i < 4; $i++) {
            $data = $this->say($wrong);
            $this->assertTrue($data['flow_active'], "Lần sai thứ {$i} vẫn phải còn cơ hội");
        }

        // Lần thứ 5 bị hủy
        $data = $this->say($wrong);
        $this->assertFalse($data['flow_active']);
        $this->assertStringContainsString('hủy', $data['bot_replies'][0]);

        // Mã đúng cũng không dùng được nữa (đã bị hủy)
        $this->say($real);
        $this->say(self::NEW_PASSWORD);
        $this->assertTrue(Hash::check(self::OLD_PASSWORD, $user->fresh()->password));
    }

    public function test_otp_sai_roi_dung_van_thanh_cong_neu_chua_du_so_lan(): void
    {
        $user = $this->user();
        $this->startAndConfirm();
        $real = $this->lastOtp();
        $wrong = $real === '000000' ? '111111' : '000000';

        $this->say($wrong);
        $this->say($wrong);
        $this->say($real);
        $this->say(self::NEW_PASSWORD);

        $this->assertTrue(Hash::check(self::NEW_PASSWORD, $user->fresh()->password));
    }

    public function test_otp_het_han_khong_dung_duoc(): void
    {
        $user = $this->user();
        $this->startAndConfirm();
        $real = $this->lastOtp();

        // Hết 10 phút: mã bị xóa khỏi bộ nhớ đệm
        Cache::forget('support_bot_otp_' . $user->id);
        $this->say($real);
        $this->say(self::NEW_PASSWORD);

        $this->assertTrue(Hash::check(self::OLD_PASSWORD, $user->fresh()->password));
    }

    public function test_otp_khong_du_6_so_bao_loi_va_giu_buoc(): void
    {
        $this->user();
        $this->startAndConfirm();

        foreach (['12345', 'abcdef', '1234567'] as $bad) {
            $data = $this->say($bad);
            $this->assertTrue($data['flow_active']);
            $this->assertTrue($data['secret_next']);
        }
    }

    public function test_mat_khau_moi_qua_ngan_bao_loi_va_giu_mat_khau_cu(): void
    {
        $user = $this->user();
        $this->startAndConfirm();
        $this->say($this->lastOtp());

        $data = $this->say('12345');

        $this->assertStringContainsString('6 ký tự', $data['bot_replies'][0]);
        $this->assertTrue($data['secret_next']);
        $this->assertTrue(Hash::check(self::OLD_PASSWORD, $user->fresh()->password));
    }

    // =========================================================================
    // 🔁 GỬI LẠI, HỦY, HẾT HẠN
    // =========================================================================

    public function test_khong_gui_otp_lien_tiep_trong_1_phut(): void
    {
        $this->user();
        $this->startAndConfirm();
        $this->say(self::EMAIL); // yêu cầu lại ngay lập tức

        Mail::assertSent(PasswordResetOtpMail::class, 1);
    }

    public function test_go_huy_dung_luong_ngay_o_moi_buoc(): void
    {
        $user = $this->user();

        $this->say('quên mật khẩu');
        $data = $this->say('hủy');
        $this->assertFalse($data['flow_active']);
        $this->assertFalse($data['secret_next']);

        // Hủy ở bước OTP
        $this->startAndConfirm();
        $data = $this->say('hủy');
        $this->assertFalse($data['secret_next']);
        $this->assertFalse($data['flow_active']);

        $this->assertTrue(Hash::check(self::OLD_PASSWORD, $user->fresh()->password));
    }

    public function test_luong_het_han_sau_15_phut(): void
    {
        $this->user();
        $this->startAndConfirm();
        $this->travel(16)->minutes();

        $data = $this->say('123456');

        $this->assertFalse($data['secret_next']);
        $this->assertFalse($data['flow_active']);
    }

    // =========================================================================
    // 🔒 BẢO MẬT
    // =========================================================================

    public function test_otp_va_mat_khau_moi_khong_duoc_luu_trong_hoi_thoai(): void
    {
        $this->user();
        $this->startAndConfirm();
        $otp = $this->lastOtp();
        $data = $this->say($otp);
        $this->say(self::NEW_PASSWORD);

        // Khách vãng lai không được lưu hội thoại, nên không có bản ghi nào chứa OTP hay mật khẩu
        $this->assertSame(0, SupportMessage::count());
        $stored = json_encode($data);

        $this->assertStringNotContainsString($otp, $stored);
        $this->assertStringNotContainsString(self::NEW_PASSWORD, $stored);
        $this->assertStringNotContainsString(self::OLD_PASSWORD, $stored);
    }

    public function test_trinh_duyet_khac_khong_tiep_tuc_duoc_luong_doi_mat_khau(): void
    {
        $user = $this->user();
        $this->startAndConfirm();
        $otp = $this->lastOtp();

        // Trình duyệt khác: phiên mới, không có luồng khôi phục
        $this->newBrowser();
        $data = $this->say($otp);
        $this->say(self::NEW_PASSWORD);

        $this->assertFalse($data['flow_active']);
        $this->assertTrue(Hash::check(self::OLD_PASSWORD, $user->fresh()->password));
    }

    public function test_admin_online_van_xu_ly_luong_khoi_phuc(): void
    {
        $user = $this->user();
        SupportBotService::markAdminActive();

        $this->startAndConfirm();
        $this->say($this->lastOtp());
        $this->say(self::NEW_PASSWORD);

        $this->assertTrue(Hash::check(self::NEW_PASSWORD, $user->fresh()->password));
    }

    public function test_otp_go_nham_o_kenh_ban_quan_tri_bi_tu_choi_va_khong_luu(): void
    {
        $user = $this->user();
        $this->startAndConfirm();
        $otp = $this->lastOtp();

        // Thành viên chuyển sang tab Ban Quản Trị rồi gõ OTP: phải bị từ chối, không lưu và không báo Telegram
        $member = User::factory()->create(['role' => 'student']);
        $response = $this->actingAs($member)->postJson(route('support.message.send'), [
            'name' => 'Khách', 'contact' => '0912345678', 'message' => $otp, 'channel' => 'admin',
        ]);

        $response->assertStatus(422);
        $this->assertStringContainsString('tab 🤖 Trợ lý AI', $response->json('message'));
        $this->assertSame(0, SupportMessage::where('message', 'like', '%' . $otp . '%')->count());
        $this->assertTrue(Hash::check(self::OLD_PASSWORD, $user->fresh()->password));
    }

    public function test_ma_otp_chi_dung_mot_lan(): void
    {
        $user = $this->user();
        $this->startAndConfirm();
        $otp = $this->lastOtp();
        $this->say($otp);
        $this->say(self::NEW_PASSWORD);

        // Luồng đã kết thúc: gửi lại mã cũ không thể đổi mật khẩu lần nữa
        $this->say($otp);
        $this->say('mat-khau-lan-3-789');

        $this->assertTrue(Hash::check(self::NEW_PASSWORD, $user->fresh()->password));
        $this->assertFalse(Hash::check('mat-khau-lan-3-789', $user->fresh()->password));
    }

    public function test_email_khong_lo_day_du_trong_tin_nhan_bot(): void
    {
        $this->user();
        $this->say('quên mật khẩu');
        $data = $this->say(self::EMAIL);

        $this->assertStringNotContainsString(self::EMAIL, $data['bot_replies'][0]);
        $this->assertStringContainsString('@gmail.com', $data['bot_replies'][0]);
    }

    public function test_sau_khi_huy_lam_lai_khong_duoc_ai_tu_gui_otp_theo_lich_su_cu(): void
    {
        $this->user();

        $this->say('quên mật khẩu');
        $this->say(self::EMAIL);
        $this->say('hủy');

        $restart = $this->say('z làm lại đi');
        $this->assertTrue($restart['flow_active']);
        $this->assertStringContainsString('email đăng nhập', implode(' ', $restart['bot_replies']));
        Mail::assertNotSent(PasswordResetOtpMail::class);

        $identify = $this->say(self::EMAIL);
        $this->assertTrue($identify['flow_active']);
        $this->assertStringContainsString('nhập **đầy đủ** địa chỉ email', implode(' ', $identify['bot_replies']));
        Mail::assertNotSent(PasswordResetOtpMail::class);

        $confirm = $this->say(self::EMAIL);
        $this->assertTrue($confirm['secret_next']);
        Mail::assertSent(PasswordResetOtpMail::class);
    }
}
