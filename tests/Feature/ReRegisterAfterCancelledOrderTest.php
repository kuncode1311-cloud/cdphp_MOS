<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\PackageOrder;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Khách đăng ký bỏ dở (đơn bị hủy/hết hạn) phải đăng ký lại được bằng chính email cũ, không bị báo "đã tồn tại".
 */
class ReRegisterAfterCancelledOrderTest extends TestCase
{
    use RefreshDatabase;

    private function package(): Package
    {
        return Package::create([
            'name' => 'Gói Khám Phá', 'slug' => 'goi-kham-pha-thu', 'target_audience' => 'student',
            'price' => 69000, 'duration_days' => 30, 'max_students' => 1, 'is_active' => true,
        ]);
    }

    private function form(array $over = []): array
    {
        return array_merge([
            'name' => 'Trung', 'email' => 'khach@example.test', 'password' => 'matkhau123', 'password_confirmation' => 'matkhau123', 'phone' => '0912345678',
        ], $over);
    }

    private function registerOnce(Package $package): User
    {
        Http::fake(['*' => Http::response(['code' => '00', 'data' => ['checkoutUrl' => 'https://pay.test/x', 'qrCode' => 'qr']])]);
        $this->postJson(route('pricing.register_and_order', $package), $this->form())->assertOk();

        return User::where('email', 'khach@example.test')->firstOrFail();
    }

    public function test_dang_ky_lai_duoc_khi_don_cu_da_bi_huy(): void
    {
        $this->seed();
        $package = $this->package();
        $user = $this->registerOnce($package);
        app(SubscriptionService::class)->rejectOrder($user->packageOrders()->firstOrFail(), 'Khách không chuyển khoản');

        Http::fake(['*' => Http::response(['code' => '00', 'data' => ['checkoutUrl' => 'https://pay.test/y', 'qrCode' => 'qr2']])]);
        $this->postJson(route('pricing.register_and_order', $package), $this->form(['name' => 'Trung Mới', 'password' => 'matkhaumoi1', 'password_confirmation' => 'matkhaumoi1']))
            ->assertOk()->assertJson(['ok' => true]);

        $this->assertSame(1, User::where('email', 'khach@example.test')->count());
        $this->assertSame('Trung Mới', $user->fresh()->name);
        $this->assertTrue(\Hash::check('matkhaumoi1', $user->fresh()->password));
        $this->assertSame(1, $user->packageOrders()->where('status', PackageOrder::STATUS_PENDING)->count());
    }

    public function test_van_chan_khi_don_dang_cho_thanh_toan_con_hieu_luc(): void
    {
        $this->seed();
        $package = $this->package();
        $this->registerOnce($package);

        $this->postJson(route('pricing.register_and_order', $package), $this->form(['name' => 'Người Khác']))
            ->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_van_chan_khi_tai_khoan_da_tung_kich_hoat_goi(): void
    {
        $this->seed();
        $package = $this->package();
        $user = $this->registerOnce($package);
        app(SubscriptionService::class)->activateOrder($user->packageOrders()->firstOrFail());

        $this->postJson(route('pricing.register_and_order', $package), $this->form(['name' => 'Kẻ Giả']))
            ->assertStatus(422)->assertJsonValidationErrors('email');
        $this->assertNotSame('Kẻ Giả', $user->fresh()->name);
    }

    public function test_tai_khoan_hoc_sinh_do_giao_vien_tao_khong_bi_dang_ky_de(): void
    {
        $this->seed();
        $teacher = User::where('role', 'teacher')->firstOrFail();
        User::factory()->create(['role' => 'student', 'created_by' => $teacher->id, 'status' => 'pending', 'email' => 'khach@example.test']);

        $this->postJson(route('pricing.register_and_order', $this->package()), $this->form())
            ->assertStatus(422)->assertJsonValidationErrors('email');
    }
}
