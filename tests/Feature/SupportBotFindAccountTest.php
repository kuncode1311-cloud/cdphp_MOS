<?php

namespace Tests\Feature;

use App\Mail\PasswordResetOtpMail;
use App\Models\Classroom;
use App\Models\Package;
use App\Models\PackageOrder;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Trợ lý chat: quên cả email lẫn Mã HS thì hỏi câu xác minh để tìm lại tài khoản.
 * - Học sinh do thầy/cô tạo: họ tên + lớp + tên thầy/cô.
 * - Tự mua gói: họ tên + mã đơn hàng.
 * Chỉ cho biết Mã HS và email đã che; đổi mật khẩu vẫn bắt buộc OTP gửi về email.
 */
class SupportBotFindAccountTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ValidateCsrfToken::class);
        config(['services.gemini.api_keys' => 'test-key', 'services.question_ai.base_url' => '', 'services.question_ai.model' => '', 'mail.default' => 'smtp']);
        Cache::flush();
        Mail::fake();
        Http::fake();
        Cache::forget('support_admin_seen_at');

        $this->teacher = User::factory()->create(['role' => 'teacher', 'name' => 'Cô Mai Linh (GV 3A1)']);
    }

    private function student(string $name, string $email, string $class = 'Lớp 3A1', ?User $teacher = null): User
    {
        $teacher ??= $this->teacher;
        $classroom = Classroom::firstOrCreate(['name' => $class], ['grade' => 3, 'teacher_id' => $teacher->id]);

        return User::factory()->create([
            'role' => 'student', 'name' => $name, 'email' => $email, 'student_code' => 'HS' . random_int(100, 999),
            'classroom_id' => $classroom->id, 'created_by' => $teacher->id,
        ]);
    }

    private function say(string $message): array
    {
        return $this->postJson(route('support.message.send'), [
            'name' => 'Khách', 'contact' => '0912345678', 'message' => $message, 'channel' => 'ai',
        ])->assertOk()->json();
    }

    private function replies(array $data): string
    {
        return implode("\n", $data['bot_replies']);
    }

    private function answerQuestions(string $name, string $second, ?string $teacher = null): array
    {
        $this->say('quên mật khẩu');
        $this->say('không nhớ');
        $this->say($name);
        $data = $this->say($second);

        return $teacher === null ? $data : $this->say($teacher);
    }

    public function test_hoc_sinh_cua_thay_co_xac_minh_dung_thi_biet_ma_hs_va_email_da_che_roi_nhan_otp(): void
    {
        $kid = $this->student('Nguyễn An Nhiên', 'annhien.real@gmail.com');

        // Gõ không dấu, "cô" + tên cô vẫn khớp
        $data = $this->answerQuestions('nguyen an nhien', '3a1', 'cô Mai Linh');
        $text = $this->replies($data);

        $this->assertStringContainsString($kid->student_code, $text);
        $this->assertStringContainsString('annh', $text);
        $this->assertStringContainsString('al@gmail.com', $text);
        $this->assertStringNotContainsString('annhien.real@gmail.com', $text, 'Không bao giờ lộ email đầy đủ');

        // Đi tiếp như cũ: nhập đủ email thì mới gửi OTP
        Mail::assertNothingSent();
        $this->say('annhien.real@gmail.com');
        Mail::assertSent(PasswordResetOtpMail::class, fn ($m) => $m->hasTo('annhien.real@gmail.com'));
    }

    public function test_tai_khoan_email_ao_chi_bao_ma_hs_va_nho_thay_co(): void
    {
        $kid = $this->student('Lê Thảo My', 'thaomy.3a1@student.ic3.local');

        $text = $this->replies($this->answerQuestions('Lê Thảo My', '3A1', 'Mai Linh'));

        $this->assertStringContainsString($kid->student_code, $text);
        $this->assertStringContainsString('chưa có email nhận thư', $text);
        $this->assertStringContainsString('Cô Mai Linh', $text);
        Mail::assertNothingSent();
    }

    public function test_hoc_sinh_tu_mua_goi_xac_minh_bang_ma_don_hang(): void
    {
        $buyer = User::factory()->create(['role' => 'student', 'name' => 'Trần Bảo Ngọc', 'email' => 'baongoc@gmail.com', 'student_code' => null]);
        $package = Package::create(['name' => 'Gói Học Sinh', 'slug' => 'goi-hs-test', 'price' => 99000]);
        PackageOrder::create(['code' => 'MOS-202610-K7QXZ', 'user_id' => $buyer->id, 'package_id' => $package->id, 'package_name' => 'Gói Học Sinh', 'price' => 99000, 'status' => 'active']);

        // Gõ mã đơn chữ thường, thiếu gạch vẫn nhận
        $text = $this->replies($this->answerQuestions('Trần Bảo Ngọc', 'mã đơn của em là mos 202610 k7qxz'));

        $this->assertStringContainsString('baon', $text);
        $this->assertStringContainsString('oc@gmail.com', $text);
        $this->assertStringContainsString('nhập **đầy đủ** địa chỉ email', $text);
    }

    public function test_ma_don_dung_co_the_tim_tai_khoan_va_van_bat_xac_nhan_email(): void
    {
        $buyer = User::factory()->create(['role' => 'student', 'name' => 'Trần Bảo Ngọc', 'email' => 'baongoc@gmail.com']);
        $package = Package::create(['name' => 'Gói', 'slug' => 'goi-test-2', 'price' => 1]);
        PackageOrder::create(['code' => 'MOS-202610-AAAAA', 'user_id' => $buyer->id, 'package_id' => $package->id, 'package_name' => 'Gói', 'price' => 1]);

        $text = $this->replies($this->answerQuestions('Người Khác', 'MOS-202610-AAAAA'));

        $this->assertStringContainsString('baon', $text);
        $this->assertStringContainsString('nhập **đầy đủ** địa chỉ email', $text);
        Mail::assertNothingSent();
    }

    public function test_sai_thong_tin_chi_bao_chung_va_khoa_sau_3_lan(): void
    {
        $this->student('Nguyễn An Nhiên', 'annhien.real@gmail.com');

        // Sai lớp: không được nói là sai ở câu nào
        $text = $this->replies($this->answerQuestions('Nguyễn An Nhiên', '4A1', 'Mai Linh'));
        $this->assertStringContainsString('chưa khớp', $text);
        $this->assertStringNotContainsString('annh', $text);

        // Sai thêm 2 lần nữa thì bị khóa, chuyển cho Ban Quản Trị
        foreach (range(1, 2) as $_) {
            $this->say('Nguyễn An Nhiên');
            $this->say('3A1');
            $data = $this->say('Thầy Tuấn');
        }
        $this->assertStringContainsString('quá nhiều lần', $this->replies($data));

        // Bắt đầu lại cũng không thử tiếp được, kể cả nhập đúng
        $this->say('quên mật khẩu');
        $this->assertStringContainsString('quá nhiều lần', $this->replies($this->say('không nhớ')));
    }

    public function test_trung_ten_lop_va_giao_vien_thi_khong_doan_nguoi(): void
    {
        $this->student('Nguyễn Minh', 'minh1@gmail.com');
        $this->student('Nguyễn Minh', 'minh2@gmail.com');

        $text = $this->replies($this->answerQuestions('Nguyễn Minh', '3A1', 'Mai Linh'));

        $this->assertStringContainsString('chưa khớp', $text);
        $this->assertStringNotContainsString('mi***', $text);
    }
}
