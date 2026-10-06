<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Mỗi giới hạn tốc độ có bộ đếm riêng: hỏi trạng thái đơn / chat liên tục không được làm đăng nhập bị chặn 429.
 */
class ThrottleIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_polling_order_status_does_not_block_login(): void
    {
        $this->seed();
        $student = User::factory()->create(['role' => 'student', 'created_by' => null, 'status' => 'pending']);
        $package = Package::create([
            'name' => 'Gói Thử Giới Hạn', 'slug' => 'goi-thu-gioi-han', 'target_audience' => 'student',
            'price' => 149000, 'duration_days' => 30, 'max_students' => 1, 'is_active' => true,
        ]);
        $order = app(SubscriptionService::class)->createOrder($student, $package, ['payment_method' => 'payos']);

        // Hỏi trạng thái đơn 30 lần (nhiều hơn hạn mức đăng nhập 10 lần/phút)
        for ($i = 0; $i < 30; $i++) {
            $this->getJson(route('pricing.order.status', $order))->assertOk();
        }

        // Đăng nhập vẫn không bị 429
        $this->post('/dang-nhap', ['login' => 'admin', 'password' => '123456'])->assertStatus(302);
    }

    public function test_login_is_still_limited_to_10_attempts_per_minute(): void
    {
        $this->seed();

        for ($i = 0; $i < 10; $i++) {
            $this->post('/dang-nhap', ['login' => 'khong-ton-tai', 'password' => 'sai'])->assertStatus(302);
        }

        $this->post('/dang-nhap', ['login' => 'khong-ton-tai', 'password' => 'sai'])->assertStatus(429);
    }
}
