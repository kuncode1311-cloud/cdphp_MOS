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
 * Khung "Nâng cấp Trợ lý AI" thay cho "Thử thách tuần": hiện gói AI từ cơ sở dữ liệu và thanh toán PayOS thật.
 */
class AiUpgradeModalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.payos.client_id' => 'cid', 'services.payos.api_key' => 'key', 'services.payos.checksum_key' => 'ck']);
    }

    private function aiPackage(string $audience = 'student', int $price = 49000, string $name = 'Trợ lý AI 1 Tháng'): Package
    {
        return Package::create([
            'name' => $name, 'slug' => 'ai-' . uniqid(), 'target_audience' => $audience, 'price' => $price,
            'duration_days' => 30, 'max_students' => 0, 'is_active' => true, 'grants_ai_assistant' => true,
        ]);
    }

    private function student(): User
    {
        $this->seed();

        return User::factory()->create([
            'role' => 'student', 'created_by' => null, 'status' => 'active', 'expires_at' => now()->addDays(30), 'max_students' => 1,
        ]);
    }

    public function test_student_sees_ai_upgrade_card_instead_of_weekly_challenge(): void
    {
        $student = $this->student();
        $this->aiPackage();

        $this->actingAs($student)->get(route('home'))
            ->assertOk()
            ->assertSee('Mở khóa Trợ lý AI')
            ->assertSee('Nâng cấp Trợ lý AI')
            ->assertSee('Trợ lý AI 1 Tháng')
            ->assertSee('49.000')
            ->assertDontSee('Thử thách tuần')
            ->assertDontSee('1/3 nhiệm vụ');
    }

    public function test_student_without_ai_packages_sees_coming_soon_message(): void
    {
        $student = $this->student();

        $this->actingAs($student)->get(route('home'))
            ->assertOk()
            ->assertSee('Gói Trợ lý AI sắp mở bán');
    }

    public function test_student_only_sees_student_ai_packages(): void
    {
        $student = $this->student();
        $this->aiPackage('student', 49000, 'AI Cho Hoc Sinh');
        $this->aiPackage('teacher', 99000, 'AI Cho Giao Vien');

        $this->actingAs($student)->get(route('home'))
            ->assertSee('AI Cho Hoc Sinh')
            ->assertDontSee('AI Cho Giao Vien');
    }

    public function test_student_with_active_ai_sees_renew_state(): void
    {
        $student = $this->student();
        $student->forceFill(['ai_assistant_until' => now()->addDays(10)])->save();
        $this->aiPackage();

        $this->actingAs($student)->get(route('home'))
            ->assertSee('Trợ lý AI đang bật')
            ->assertSee('Gia hạn');
    }

    public function test_admin_does_not_get_the_student_upgrade_modal(): void
    {
        $this->seed();
        $this->aiPackage();
        $admin = User::where('role', 'admin')->firstOrFail();

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($admin)->get('/?xem=hoc-sinh')->assertOk()->assertDontSee('aiup-overlay', false);
    }

    public function test_buying_ai_package_creates_payos_order_with_qr_and_countdown_then_grants_ai(): void
    {
        $student = $this->student();
        $package = $this->aiPackage();
        Http::fake(['*' => Http::response(['code' => '00', 'data' => ['checkoutUrl' => 'https://pay.test/x', 'qrCode' => 'qr']])]);

        $res = $this->actingAs($student)->postJson(route('pricing.order', $package), ['payment_method' => 'payos'])
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertEqualsWithDelta(600, $res->json('expires_in_seconds'), 5);
        $order = PackageOrder::where('user_id', $student->id)->firstOrFail();
        $this->assertEquals('payos', $order->payment_method);
        $this->assertFalse($student->fresh()->hasAiAssistant());

        // Thanh toán xong (webhook/Admin kích hoạt) thì mở khóa AI
        app(SubscriptionService::class)->activateOrder($order);
        $this->assertTrue($student->fresh()->hasAiAssistant());
    }
}
