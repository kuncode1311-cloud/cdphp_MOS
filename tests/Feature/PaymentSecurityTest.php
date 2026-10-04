<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\PackageOrder;
use App\Models\User;
use App\Services\SubscriptionService;
use App\Services\TelegramService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * An toàn thanh toán: giá lấy từ máy chủ, webhook PayOS xác thực chữ ký + số tiền + trạng thái, kích hoạt không bị lặp,
 * và trình duyệt không có cách nào tự kích hoạt đơn.
 */
class PaymentSecurityTest extends TestCase
{
    use RefreshDatabase;

    private const CHECKSUM = 'khoa-checksum-thu-nghiem';

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.payos.client_id' => 'cid', 'services.payos.api_key' => 'key', 'services.payos.checksum_key' => self::CHECKSUM,
            'services.telegram.admin_chat_id' => '111222333', 'services.telegram.bot_token' => 'token-thu-nghiem',
        ]);
    }

    private function package(bool $active = true): Package
    {
        return Package::create([
            'name' => 'Gói Thanh Toán Thử', 'slug' => 'goi-thanh-toan-thu', 'target_audience' => 'student',
            'price' => 149000, 'duration_days' => 90, 'max_students' => 1, 'is_active' => $active,
        ]);
    }

    private function pendingOrder(): PackageOrder
    {
        $this->seed();
        $student = User::factory()->create(['role' => 'student', 'created_by' => null, 'status' => 'pending']);

        return app(SubscriptionService::class)->createOrder($student, $this->package(), ['payment_method' => 'payos']);
    }

    /** Tạo payload webhook có chữ ký đúng chuẩn PayOS (có cả trường rỗng) hoặc kiểu cũ (bỏ trường rỗng). */
    private function webhook(PackageOrder $order, array $override = [], bool $official = true, array $top = []): array
    {
        $data = array_merge([
            'orderCode' => (int) ($order->id.'051006'),
            'amount' => (int) $order->price,
            'description' => 'MOS thanh toan',
            'code' => '00',
            'desc' => 'success',
            'counterAccountBankId' => '',
            'counterAccountName' => null,
        ], $override);

        ksort($data);
        $parts = [];
        foreach ($data as $k => $v) {
            if ($v === null || $v === '') {
                if ($official) {
                    $parts[] = "{$k}=";
                }

                continue;
            }
            $parts[] = "{$k}={$v}";
        }

        return array_merge([
            'code' => '00', 'desc' => 'success', 'success' => true,
            'data' => $data,
            'signature' => hash_hmac('sha256', implode('&', $parts), self::CHECKSUM),
        ], $top);
    }

    public function test_webhook_dung_chu_ky_va_so_tien_kich_hoat_don_dung_mot_lan(): void
    {
        $order = $this->pendingOrder();
        $payload = $this->webhook($order);

        $this->postJson(route('pricing.payos.webhook'), $payload)->assertOk();
        $this->assertTrue($order->fresh()->isActive());
        $expiry = $order->user->fresh()->expires_at;

        // PayOS gửi lặp cùng một thông báo: không được cộng thêm hạn
        $this->postJson(route('pricing.payos.webhook'), $payload)->assertOk();
        $this->assertTrue($order->user->fresh()->expires_at->equalTo($expiry));
    }

    public function test_webhook_chu_ky_kieu_cu_bo_truong_rong_van_duoc_chap_nhan(): void
    {
        $order = $this->pendingOrder();

        $this->postJson(route('pricing.payos.webhook'), $this->webhook($order, [], false))->assertOk();
        $this->assertTrue($order->fresh()->isActive());
    }

    public function test_webhook_sai_chu_ky_hoac_bi_sua_so_tien_bi_tu_choi(): void
    {
        $order = $this->pendingOrder();

        $bad = $this->webhook($order);
        $bad['signature'] = str_repeat('a', 64);
        $this->postJson(route('pricing.payos.webhook'), $bad)->assertStatus(400);

        // Kẻ gian sửa số tiền nhưng giữ nguyên chữ ký cũ
        $tampered = $this->webhook($order);
        $tampered['data']['amount'] = 1000;
        $this->postJson(route('pricing.payos.webhook'), $tampered)->assertStatus(400);

        // Không có chữ ký
        $this->postJson(route('pricing.payos.webhook'), ['data' => ['orderCode' => (int) ($order->id.'051006'), 'amount' => 149000]])->assertStatus(400);

        $this->assertTrue($order->fresh()->isPending());
    }

    public function test_webhook_so_tien_khong_khop_hoac_giao_dich_that_bai_khong_kich_hoat(): void
    {
        $order = $this->pendingOrder();

        $this->postJson(route('pricing.payos.webhook'), $this->webhook($order, ['amount' => 1000]))->assertStatus(422);
        $this->postJson(route('pricing.payos.webhook'), $this->webhook($order, [], true, ['success' => false]))->assertOk();
        $this->postJson(route('pricing.payos.webhook'), $this->webhook($order, ['code' => '01']))->assertOk();

        $this->assertTrue($order->fresh()->isPending());
    }

    public function test_url_tro_ve_payos_khong_the_tu_kich_hoat_don(): void
    {
        $order = $this->pendingOrder();

        $this->get(route('pricing.payos.return', $order).'?status=PAID&code=00&cancel=false')->assertRedirect();

        $this->assertTrue($order->fresh()->isPending());
    }

    public function test_gia_luon_lay_tu_may_chu_khong_nhan_gia_tu_trinh_duyet(): void
    {
        $this->seed();
        $package = $this->package();
        $student = User::factory()->create(['role' => 'student', 'created_by' => null]);

        $this->actingAs($student)->post(route('pricing.order', $package), [
            'payment_method' => 'bank_transfer', 'price' => 1, 'duration_days' => 9999, 'status' => 'active',
        ])->assertRedirect();

        $order = PackageOrder::where('user_id', $student->id)->firstOrFail();
        $this->assertSame(149000, (int) $order->price);
        $this->assertSame(90, (int) $order->duration_days);
        $this->assertTrue($order->isPending());
    }

    public function test_goi_da_ngung_ban_khong_nhan_don_va_khong_tao_tai_khoan(): void
    {
        $this->seed();
        $package = $this->package(false);
        $student = User::factory()->create(['role' => 'student', 'created_by' => null]);

        $this->actingAs($student)->post(route('pricing.order', $package), ['payment_method' => 'bank_transfer'])->assertNotFound();
        $this->post(route('pricing.register_and_order', $package), [
            'name' => 'Khách', 'email' => 'khach@example.test', 'phone' => '0912345678', 'password' => 'matkhau123', 'password_confirmation' => 'matkhau123',
        ])->assertNotFound();

        $this->assertSame(0, PackageOrder::count());
        $this->assertDatabaseMissing('users', ['email' => 'khach@example.test']);
    }

    public function test_nut_toi_da_chuyen_khoan_chi_danh_cho_chu_don_va_khong_gui_lap(): void
    {
        $order = $this->pendingOrder();
        $this->mock(TelegramService::class, function ($mock) {
            $mock->shouldReceive('sendMessage')->once();
        });

        // Người lạ biết mã đơn nhưng không phải chủ đơn
        $this->postJson(route('pricing.order.confirm_transferred', $order))->assertNotFound();

        // Chủ đơn bấm hai lần liên tiếp: chỉ báo Ban Quản Trị một lần
        $this->actingAs($order->user)->postJson(route('pricing.order.confirm_transferred', $order))->assertOk();
        $this->actingAs($order->user)->postJson(route('pricing.order.confirm_transferred', $order))->assertOk();
    }

    public function test_khong_the_tu_choi_don_da_kich_hoat(): void
    {
        $order = $this->pendingOrder();
        $service = app(SubscriptionService::class);
        $service->activateOrder($order);

        $this->assertFalse($service->rejectOrder($order->fresh(), 'nhầm'));
        $this->assertTrue($order->fresh()->isActive());
    }

    public function test_telegram_duyet_don_chi_hieu_luc_khi_webhook_co_secret_token(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);
        $order = $this->pendingOrder();
        $callback = ['callback_query' => [
            'id' => 'cb1', 'data' => 'act_ord_'.$order->id,
            'from' => ['id' => 111222333],
            'message' => ['message_id' => 5, 'chat' => ['id' => 111222333]],
        ]];

        // Chưa bật secret token: kẻ gian biết ID admin cũng không duyệt được đơn
        $this->postJson('/api/telegram/webhook', $callback)->assertOk();
        $this->assertTrue($order->fresh()->isPending());

        // Đã bật secret token nhưng yêu cầu thiếu/sai token: bị chặn
        config(['services.telegram.webhook_secret' => 'bi-mat-telegram']);
        $this->postJson('/api/telegram/webhook', $callback, ['X-Telegram-Bot-Api-Secret-Token' => 'sai'])->assertForbidden();
        $this->assertTrue($order->fresh()->isPending());

        // Đúng token từ Telegram thì duyệt được
        $this->postJson('/api/telegram/webhook', $callback, ['X-Telegram-Bot-Api-Secret-Token' => 'bi-mat-telegram'])->assertOk();
        $this->assertTrue($order->fresh()->isActive());
    }
}
