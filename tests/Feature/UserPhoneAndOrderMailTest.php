<?php

namespace Tests\Feature;

use App\Mail\OrderActivatedMail;
use App\Models\Package;
use App\Models\PackageOrder;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Số điện thoại của tài khoản (lưu, chuẩn hóa, che khi hiển thị) và email xác nhận khi đơn hàng kích hoạt thành công.
 */
class UserPhoneAndOrderMailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Giả lập server có SMTP (Mail::fake chặn lại, không gửi thật); khóa Brevo để trống trong phpunit.xml
        config(['mail.default' => 'smtp']);
        Mail::fake();
        Cache::flush();
    }

    // ---------------- SỐ ĐIỆN THOẠI ----------------

    public function test_phone_is_normalized_and_masked(): void
    {
        $user = User::factory()->create(['phone' => '+84 912.345-678']);

        $this->assertSame('0912345678', $user->fresh()->phone);
        $this->assertSame('09•••••678', $user->maskedPhone());
        $this->assertNull(User::factory()->create(['phone' => 'abc'])->fresh()->phone);
    }

    public function test_admin_saves_phone_when_creating_and_editing_user(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Bé Na', 'email' => 'na@student.ic3.local', 'student_code' => 'HS777', 'phone' => '0912 345 678',
            'password' => '123456', 'role' => 'student', 'status' => 'active',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $kid = User::where('student_code', 'HS777')->firstOrFail();
        $this->assertSame('0912345678', $kid->phone);

        $this->actingAs($admin)->put(route('admin.users.update', $kid), [
            'name' => 'Bé Na', 'email' => 'na@student.ic3.local', 'student_code' => 'HS777', 'phone' => '0987654321', 'role' => 'student', 'status' => 'active',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('0987654321', $kid->fresh()->phone);

        $this->actingAs($admin)->put(route('admin.users.update', $kid), [
            'name' => 'Bé Na', 'email' => 'na@student.ic3.local', 'student_code' => 'HS777', 'phone' => '12345', 'role' => 'student', 'status' => 'active',
        ])->assertSessionHasErrors('phone');
    }

    public function test_user_updates_own_phone_with_current_password(): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $user = User::factory()->create(['password' => Hash::make('mat-khau-cu')]);

        $this->actingAs($user)->postJson(route('profile.update-phone'), ['phone' => '0912345678', 'current_password' => 'sai'])->assertStatus(422);
        $this->assertNull($user->fresh()->phone);

        $this->actingAs($user)->postJson(route('profile.update-phone'), ['phone' => '+84912345678', 'current_password' => 'mat-khau-cu'])
            ->assertOk()->assertJson(['success' => true, 'phone' => '0912345678']);
        $this->assertSame('0912345678', $user->fresh()->phone);
    }

    public function test_phone_given_when_ordering_is_saved_on_the_account(): void
    {
        $this->seed();
        $teacher = User::where('email', 'teacher@ic3.test')->firstOrFail();
        $package = Package::where('slug', 'goi-tieu-chuan-standard')->firstOrFail();

        $this->actingAs($teacher)->post(route('pricing.order', $package), ['payment_method' => 'payos', 'phone' => '0911222333']);

        $this->assertSame('0911222333', $teacher->fresh()->phone);
    }

    // ---------------- EMAIL XÁC NHẬN ĐƠN HÀNG ----------------

    private function order(User $user): PackageOrder
    {
        $package = Package::create(['name' => 'Gói Học Sinh', 'slug' => 'goi-hs-' . $user->id, 'price' => 99000, 'duration_days' => 30]);

        return PackageOrder::create([
            'code' => 'MOS-202610-ZX' . $user->id . 'AB', 'user_id' => $user->id, 'package_id' => $package->id, 'package_name' => 'Gói Học Sinh',
            'price' => 99000, 'duration_days' => 30, 'status' => 'pending', 'payment_method' => 'payos',
        ]);
    }

    public function test_activation_sends_one_confirmation_email_with_order_code_and_login_but_no_password(): void
    {
        $user = User::factory()->create(['role' => 'student', 'email' => 'mua.goi@gmail.com', 'phone' => '0912345678', 'student_code' => 'HS501']);
        $order = $this->order($user);
        $service = app(SubscriptionService::class);

        $this->assertTrue($service->activateOrder($order));
        $this->assertTrue($service->activateOrder($order->fresh()), 'Kích hoạt lặp (webhook gửi 2 lần) vẫn thành công');

        Mail::assertSent(OrderActivatedMail::class, 1);
        Mail::assertSent(OrderActivatedMail::class, function (OrderActivatedMail $mail) use ($order) {
            $html = $mail->render();

            return $mail->hasTo('mua.goi@gmail.com')
                && str_contains($mail->envelope()->subject, $order->code)
                && str_contains($html, $order->code)
                && str_contains($html, 'mua.goi@gmail.com')
                && str_contains($html, 'HS501')
                && str_contains($html, '09•••••678')
                && ! str_contains($html, '0912345678')
                && str_contains($html, 'quên mật khẩu');
        });
    }

    public function test_no_confirmation_email_for_account_without_real_email(): void
    {
        $kid = User::factory()->create(['role' => 'student', 'email' => 'hs502@student.ic3.local']);

        $this->assertTrue(app(SubscriptionService::class)->activateOrder($this->order($kid)));

        Mail::assertNothingSent();
    }

    public function test_confirmation_email_goes_through_brevo_when_configured(): void
    {
        Http::fake(['https://api.brevo.com/v3/smtp/email' => Http::response(['messageId' => '<id>'], 201)]);
        config(['services.brevo.key' => 'test-fake-key']);
        $user = User::factory()->create(['role' => 'student', 'email' => 'brevo.user@gmail.com']);
        $order = $this->order($user);

        app(SubscriptionService::class)->activateOrder($order);

        Http::assertSent(fn ($r) => $r->url() === 'https://api.brevo.com/v3/smtp/email'
            && ($r['to'][0]['email'] ?? null) === 'brevo.user@gmail.com'
            && str_contains((string) $r['subject'], $order->code));
        Mail::assertNothingSent();
    }

    // ---------------- TRỢ LÝ CHAT: XÁC MINH BẰNG SĐT ----------------

    private function say(string $message): array
    {
        return $this->postJson(route('support.message.send'), ['name' => 'Khách', 'contact' => '0900000000', 'message' => $message, 'channel' => 'ai'])->assertOk()->json();
    }

    public function test_bot_verifies_by_name_and_phone_and_shows_masked_email_and_phone(): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);
        config(['services.gemini.api_keys' => 'k', 'services.question_ai.base_url' => '', 'services.question_ai.model' => '']);
        Http::fake();
        User::factory()->create(['role' => 'student', 'name' => 'Trần Bảo Ngọc', 'email' => 'baongoc@gmail.com', 'phone' => '0912345678']);

        $this->say('quên mật khẩu');
        $this->say('không nhớ');
        $this->say('Trần Bảo Ngọc');
        $text = implode("\n", $this->say('0912 345 678')['bot_replies']);

        $this->assertMatchesRegularExpression('/ba(•){3,}@gmail\.com/u', $text);
        // Không dùng dấu * để che (trùng ký hiệu in đậm, khung chat nuốt mất)
        $this->assertStringNotContainsString('*@gmail.com', $text);
        $this->assertStringContainsString('09•••••678', $text);
        $this->assertStringNotContainsString('baongoc@gmail.com', $text);
    }
}
