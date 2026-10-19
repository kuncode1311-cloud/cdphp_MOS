<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\PackageOrder;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Dự phòng cho webhook PayOS: nếu webhook vì lý do nào đó (cấu hình sai, mạng lỗi...) không gọi
 * được về server, hệ thống phải tự hỏi thẳng PayOS để không bỏ sót đơn khách đã thanh toán thật.
 */
class PayosReconcileFallbackTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.payos.client_id' => 'cid', 'services.payos.api_key' => 'key', 'services.payos.checksum_key' => 'ck',
            'services.payos.link_ttl_minutes' => 10,
        ]);
    }

    private function pendingPayosOrder(int $price = 149000): PackageOrder
    {
        $this->seed();
        $student = User::factory()->create(['role' => 'student', 'created_by' => null, 'status' => 'pending']);
        $package = Package::create([
            'name' => 'Gói Đối Chiếu Thử', 'slug' => 'goi-doi-chieu-thu-' . uniqid(), 'target_audience' => 'student',
            'price' => $price, 'duration_days' => 90, 'max_students' => 1, 'is_active' => true,
        ]);

        $order = app(SubscriptionService::class)->createOrder($student, $package, ['payment_method' => 'payos']);
        $order->update(['payos_expires_at' => now()->addMinutes(8)]);
        Cache::put("order_payos_{$order->id}", ['ok' => true, 'orderCode' => 999888, 'qrCode' => 'qr'], now()->addMinutes(10));
        $this->withSession(['owned_order_ids' => [$order->id]]);

        return $order->fresh();
    }

    public function test_poll_trang_thai_tu_kich_hoat_don_khi_payos_bao_da_tra_tien(): void
    {
        $order = $this->pendingPayosOrder(149000);
        Http::fake(['*/v2/payment-requests/*' => Http::response(['code' => '00', 'data' => ['status' => 'PAID', 'amountPaid' => 149000]])]);

        $this->getJson(route('pricing.order.status', $order))
            ->assertOk()
            ->assertJson(['is_active' => true]);

        $this->assertTrue($order->fresh()->isActive());
    }

    public function test_khong_kich_hoat_khi_payos_van_bao_dang_cho(): void
    {
        $order = $this->pendingPayosOrder(149000);
        Http::fake(['*/v2/payment-requests/*' => Http::response(['code' => '00', 'data' => ['status' => 'PENDING', 'amountPaid' => 0]])]);

        $this->getJson(route('pricing.order.status', $order))->assertOk()->assertJson(['is_active' => false]);

        $this->assertTrue($order->fresh()->isPending());
    }

    public function test_khong_kich_hoat_khi_so_tien_da_tra_chua_du(): void
    {
        $order = $this->pendingPayosOrder(149000);
        // Khách chuyển thiếu tiền: PayOS báo PAID nhưng amountPaid thấp hơn giá đơn
        Http::fake(['*/v2/payment-requests/*' => Http::response(['code' => '00', 'data' => ['status' => 'PAID', 'amountPaid' => 50000]])]);

        $this->getJson(route('pricing.order.status', $order))->assertOk()->assertJson(['is_active' => false]);

        $this->assertTrue($order->fresh()->isPending());
    }

    public function test_khong_dung_den_payos_voi_don_chuyen_khoan_tay(): void
    {
        $this->seed();
        $student = User::factory()->create(['role' => 'student', 'created_by' => null, 'status' => 'pending']);
        $package = Package::create([
            'name' => 'Gói Chuyển Khoản Thử', 'slug' => 'goi-ck-thu-' . uniqid(), 'target_audience' => 'student',
            'price' => 99000, 'duration_days' => 30, 'max_students' => 1, 'is_active' => true,
        ]);
        $order = app(SubscriptionService::class)->createOrder($student, $package, ['payment_method' => 'bank_transfer']);
        $this->withSession(['owned_order_ids' => [$order->id]]);

        Http::fake(['*/v2/payment-requests/*' => Http::response(['code' => '00', 'data' => ['status' => 'PAID', 'amountPaid' => 99000]])]);

        $this->getJson(route('pricing.order.status', $order))->assertOk()->assertJson(['is_active' => false]);
        Http::assertNothingSent();
    }

    public function test_lenh_nen_kich_hoat_duoc_don_ngay_ca_khi_khong_ai_dang_poll(): void
    {
        $order = $this->pendingPayosOrder(149000);
        Http::fake(['*/v2/payment-requests/*' => Http::response(['code' => '00', 'data' => ['status' => 'PAID', 'amountPaid' => 149000]])]);

        $this->artisan('payos:reconcile-pending')->assertSuccessful();

        $this->assertTrue($order->fresh()->isActive());
    }
}
