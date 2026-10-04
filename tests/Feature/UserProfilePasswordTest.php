<?php

namespace Tests\Feature;

use App\Mail\PasswordChangedAlertMail;
use App\Mail\PasswordResetOtpMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class UserProfilePasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_send_otp_and_change_password_safely(): void
    {
        config(['services.brevo.key' => '']);
        putenv('BREVO_API_KEY=');
        $_ENV['BREVO_API_KEY'] = '';

        Mail::fake();
        $this->seed();
        $user = User::where('student_code', 'HS001')->firstOrFail();

        $this->actingAs($user);

        // 1. Gửi mã OTP xác thực
        $otpResponse = $this->postJson(route('profile.send-otp'));
        $otpResponse->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        Mail::assertSent(PasswordResetOtpMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });

        $cachedOtp = Cache::get('pwd_otp_' . $user->id);
        $this->assertNotNull($cachedOtp);
        $this->assertEquals(6, strlen($cachedOtp));

        // 2. Thử đổi mật khẩu với OTP sai
        $wrongOtpResponse = $this->postJson(route('profile.password'), [
            'current_password' => '123456',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
            'otp' => '000000',
        ]);
        $wrongOtpResponse->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);

        // 3. Thử đổi mật khẩu với mật khẩu hiện tại sai
        $wrongPassResponse = $this->postJson(route('profile.password'), [
            'current_password' => 'sai_mat_khau',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
            'otp' => $cachedOtp,
        ]);
        $wrongPassResponse->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Mật khẩu hiện tại chưa chính xác.',
            ]);

        // 4. Thử đổi mật khẩu với xác nhận không trùng khớp
        $mismatchResponse = $this->postJson(route('profile.password'), [
            'current_password' => '123456',
            'password' => 'newpassword123',
            'password_confirmation' => 'khac_nhau',
            'otp' => $cachedOtp,
        ]);
        $mismatchResponse->assertStatus(422);

        // 5. Đổi mật khẩu thành công với thông tin chuẩn và đúng OTP
        $successResponse = $this->postJson(route('profile.password'), [
            'current_password' => '123456',
            'password' => 'matkhau_moi_999',
            'password_confirmation' => 'matkhau_moi_999',
            'otp' => $cachedOtp,
        ]);
        $successResponse->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        // 6. Xác nhận mật khẩu mới trong DB khớp
        $this->assertTrue(Hash::check('matkhau_moi_999', $user->fresh()->password));

        // 7. Xác nhận email cảnh báo đổi mật khẩu đã được gửi đến hộp thư người dùng
        Mail::assertSent(PasswordChangedAlertMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });

        // 8. Xác nhận OTP đã bị xóa sau khi sử dụng
        $this->assertNull(Cache::get('pwd_otp_' . $user->id));
    }

    public function test_user_can_update_email_address(): void
    {
        $this->seed();
        $user = User::where('student_code', 'HS001')->firstOrFail();
        $this->actingAs($user);

        $res = $this->postJson(route('profile.update-email'), [
            'email' => 'annhien.real@gmail.com',
            'current_password' => '123456',
        ]);

        $res->assertOk()
            ->assertJson([
                'success' => true,
                'email' => 'annhien.real@gmail.com',
            ]);

        $this->assertEquals('annhien.real@gmail.com', $user->fresh()->email);
    }

    public function test_user_can_send_otp_via_brevo_api(): void
    {
        \Illuminate\Support\Facades\Http::fake([
            'https://api.brevo.com/v3/smtp/email' => \Illuminate\Support\Facades\Http::response(['messageId' => '<mocked-brevo-id-123>'], 200),
        ]);

        config(['services.brevo.key' => 'test-fake-key']);
        putenv('BREVO_API_KEY=test-fake-key');
        $_ENV['BREVO_API_KEY'] = 'test-fake-key';

        $this->seed();
        $user = User::where('student_code', 'HS001')->firstOrFail();
        $this->actingAs($user);

        $otpResponse = $this->postJson(route('profile.send-otp'));
        $otpResponse->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        \Illuminate\Support\Facades\Http::assertSent(function ($request) use ($user) {
            return $request->url() === 'https://api.brevo.com/v3/smtp/email'
                && $request->hasHeader('api-key', 'test-fake-key')
                && str_contains($request->body(), $user->email);
        });

        config(['services.brevo.key' => '']);
        putenv('BREVO_API_KEY');
        unset($_ENV['BREVO_API_KEY']);
    }

    public function test_user_receives_password_changed_alert_via_brevo_api(): void
    {
        \Illuminate\Support\Facades\Http::fake([
            'https://api.brevo.com/v3/smtp/email' => \Illuminate\Support\Facades\Http::response(['messageId' => '<mocked-brevo-alert-123>'], 200),
        ]);

        config(['services.brevo.key' => 'test-fake-key']);
        putenv('BREVO_API_KEY=test-fake-key');
        $_ENV['BREVO_API_KEY'] = 'test-fake-key';

        $this->seed();
        $user = User::where('student_code', 'HS001')->firstOrFail();
        $this->actingAs($user);

        // Chuẩn bị OTP
        Cache::put('pwd_otp_' . $user->id, '123456', now()->addMinutes(10));

        $response = $this->postJson(route('profile.password'), [
            'current_password' => '123456',
            'password' => 'new_pass_brevo_123',
            'password_confirmation' => 'new_pass_brevo_123',
            'otp' => '123456',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        \Illuminate\Support\Facades\Http::assertSent(function ($request) use ($user) {
            return $request->url() === 'https://api.brevo.com/v3/smtp/email'
                && $request->hasHeader('api-key', 'test-fake-key')
                && str_contains($request->body(), $user->email);
        });

        config(['services.brevo.key' => '']);
        putenv('BREVO_API_KEY');
        unset($_ENV['BREVO_API_KEY']);
    }
}
