<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\PackageOrder;
use App\Models\User;
use App\Services\PayosService;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Thanh toán online (PayOS): mã sống 10 phút, có đếm ngược; quá hạn chưa trả thì tự hủy và ẩn khỏi bảng đơn Admin.
 * Đơn chuyển khoản tay (chờ Admin duyệt) thì không hết hạn.
 */
class PayosLinkExpiryTest extends TestCase
{
    use RefreshDatabase;

    private const CHECKSUM = 'khoa-checksum-thu-nghiem';

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.payos.client_id' => 'cid', 'services.payos.api_key' => 'key', 'services.payos.checksum_key' => self::CHECKSUM,
            'services.payos.link_ttl_minutes' => 10,
            'services.telegram.admin_chat_id' => '111222333', 'services.telegram.bot_token' => 'token-thu-nghiem',
        ]);
    }

    private function order(string $method, ?\DateTimeInterface $expiresAt = null, ?\DateTimeInterface $createdAt = null): PackageOrder
    {
        $this->seed();
        $student = User::factory()->create(['role' => 'student', 'created_by' => null, 'status' => 'pending']);
        $package = Package::create([
            'name' => 'Gói Hết Hạn Thử', 'slug' => 'goi-het-han-thu-' . uniqid(), 'target_audience' => 'student',
            'price' => 149000, 'duration_days' => 90, 'max_students' => 1, 'is_active' => true,
        ]);

        $order = app(SubscriptionService::class)->createOrder($student, $package, ['payment_method' => $method]);
        $order->update(['payos_expires_at' => $expiresAt]);
        if ($createdAt) {
            $order->forceFill(['created_at' => $createdAt])->save();
        }
        $this->withSession(['owned_order_ids' => [$order->id]]);

        return $order->fresh();
    }

    public function test_payos_link_request_has_10_minute_expiry(): void
    {
        $order = $this->order('payos');
        Http::fake(['*' => Http::response(['code' => '00', 'data' => ['checkoutUrl' => 'https://pay.test/x', 'qrCode' => 'qr']])]);

        $before = time();
        $result = app(PayosService::class)->createPaymentLink($order, 'https://a.test/return', 'https://a.test/cancel');

        $this->assertTrue($result['ok']);
        $this->assertEqualsWithDelta($before + 600, $result['expiredAt'], 5);
        Http::assertSent(fn ($request) => abs(($request['expiredAt'] ?? 0) - ($before + 600)) <= 5);
    }

    public function test_checkout_shows_countdown_while_online_order_is_valid(): void
    {
        $order = $this->order('payos', now()->addSeconds(540));
        Cache::put("order_payos_{$order->id}", ['ok' => true, 'qrCode' => 'qr', 'expiredAt' => time() + 540], now()->addMinutes(10));

        $this->get(route('pricing.order.checkout', $order))
            ->assertOk()
            ->assertSee('id="qr-countdown-time"', false)
            ->assertSee('Mã thanh toán online hết hạn sau');
    }

    public function test_expired_online_order_is_auto_cancelled_and_checkout_redirects(): void
    {
        $order = $this->order('payos', now()->subSeconds(5));

        $this->get(route('pricing.order.checkout', $order))->assertRedirect(route('pricing.index'));

        $order->refresh();
        $this->assertTrue($order->isRejected());
        $this->assertTrue($order->isExpiredByTimeout());
    }

    public function test_order_status_poll_reports_expired(): void
    {
        $order = $this->order('payos', now()->subSeconds(5));

        $this->getJson(route('pricing.order.status', $order))
            ->assertOk()
            ->assertJson(['is_active' => false, 'is_expired' => true]);
    }

    public function test_manual_bank_transfer_order_never_expires(): void
    {
        $order = $this->order('bank_transfer', null, now()->subDays(3));

        $this->get(route('pricing.order.checkout', $order))
            ->assertOk()
            ->assertDontSee('id="qr-countdown-time"', false);

        $this->assertTrue($order->fresh()->isPending());
    }

    public function test_legacy_online_order_without_expiry_uses_creation_time(): void
    {
        $fresh = $this->order('payos', null, now()->subMinutes(3));
        $stale = $this->order('payos', null, now()->subMinutes(30));

        $this->assertEquals(1, app(SubscriptionService::class)->expireStaleOnlineOrders());
        $this->assertTrue($fresh->fresh()->isPending());
        $this->assertTrue($stale->fresh()->isRejected());
    }

    public function test_admin_order_table_hides_cancelled_and_expired_orders(): void
    {
        $waiting = $this->order('payos', now()->addMinutes(5));
        $expired = $this->order('payos', now()->subMinutes(1));
        $manual = $this->order('bank_transfer');
        $rejected = $this->order('bank_transfer');
        app(SubscriptionService::class)->rejectOrder($rejected, 'Thử nghiệm');
        $paid = $this->order('payos', now()->addMinutes(5));
        app(SubscriptionService::class)->activateOrder($paid);

        $admin = User::where('role', 'admin')->firstOrFail();
        $html = $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString('#' . $waiting->code, $html);
        $this->assertStringContainsString('#' . $manual->code, $html);
        $this->assertStringContainsString('#' . $paid->code, $html);
        $this->assertStringNotContainsString('#' . $expired->code, $html);
        $this->assertStringNotContainsString('#' . $rejected->code, $html);
        $this->assertStringContainsString('Chờ thanh toán', $html);
    }

    public function test_online_link_failure_cancels_order_and_asks_to_retry(): void
    {
        $this->seed();
        $teacher = User::where('email', 'teacher@ic3.test')->firstOrFail();
        $package = Package::where('slug', 'goi-tieu-chuan-standard')->firstOrFail();
        Http::fake(['*' => Http::response(['code' => '99', 'desc' => 'loi'], 400)]);

        $this->actingAs($teacher)->postJson(route('pricing.order', $package), ['payment_method' => 'bank_transfer'])
            ->assertStatus(503)
            ->assertJson(['ok' => false]);

        // Luôn là đơn PayOS (bỏ qua phương thức khách gửi lên); không tạo được mã thì đơn bị hủy, không treo
        $order = PackageOrder::where('user_id', $teacher->id)->firstOrFail();
        $this->assertEquals('payos', $order->payment_method);
        $this->assertTrue($order->isRejected());
    }

    public function test_guest_register_is_rolled_back_when_online_code_fails(): void
    {
        $this->seed();
        $package = Package::where('slug', 'goi-tieu-chuan-standard')->firstOrFail();
        Http::fake(['*' => Http::response(['code' => '99'], 400)]);

        $this->postJson(route('pricing.register_and_order', $package), [
            'name' => 'Co Thu', 'email' => 'thu@example.com', 'password' => 'matkhau123', 'password_confirmation' => 'matkhau123', 'phone' => '0912345678',
        ])->assertStatus(503);

        $this->assertDatabaseMissing('users', ['email' => 'thu@example.com']);
    }

    public function test_successful_online_link_stores_expiry_on_order(): void
    {
        $this->seed();
        $teacher = User::where('email', 'teacher@ic3.test')->firstOrFail();
        $package = Package::where('slug', 'goi-tieu-chuan-standard')->firstOrFail();
        Http::fake(['*' => Http::response(['code' => '00', 'data' => ['checkoutUrl' => 'https://pay.test/x', 'qrCode' => 'qr']])]);

        $this->actingAs($teacher)->post(route('pricing.order', $package), ['payment_method' => 'payos']);

        $order = PackageOrder::where('user_id', $teacher->id)->firstOrFail();
        $this->assertEquals('payos', $order->payment_method);
        $this->assertEqualsWithDelta(time() + 600, $order->payos_expires_at->timestamp, 5);
    }

    public function test_late_webhook_still_activates_order_that_was_just_auto_cancelled(): void
    {
        $order = $this->order('payos', now()->subSeconds(5));
        app(SubscriptionService::class)->expireStaleOnlineOrders();
        $this->assertTrue($order->fresh()->isExpiredByTimeout());

        $data = [
            'orderCode' => (int) ($order->id . '051006'), 'amount' => 149000, 'description' => 'MOS thanh toan',
            'code' => '00', 'desc' => 'success',
        ];
        ksort($data);
        $query = implode('&', array_map(fn ($k, $v) => "{$k}={$v}", array_keys($data), $data));

        $this->postJson(route('pricing.payos.webhook'), [
            'code' => '00', 'desc' => 'success', 'success' => true, 'data' => $data,
            'signature' => hash_hmac('sha256', $query, self::CHECKSUM),
        ])->assertOk();

        $this->assertTrue($order->fresh()->isActive());
    }
}
