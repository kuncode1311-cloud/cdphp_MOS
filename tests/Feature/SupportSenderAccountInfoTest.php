<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\SupportMessage;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Hộp gợi ý trong Tin nhắn tư vấn: khách chưa có tài khoản thì tạo tài khoản, đã có thì hiện gói của họ.
 */
class SupportSenderAccountInfoTest extends TestCase
{
    use RefreshDatabase;

    private function activate(User $user, string $name, string $audience): void
    {
        $package = Package::create([
            'name' => $name, 'slug' => str($name)->slug()->toString(), 'target_audience' => $audience,
            'price' => 69000, 'duration_days' => 30, 'max_students' => $audience === 'teacher' ? 50 : 1, 'is_active' => true,
        ]);
        $service = app(SubscriptionService::class);
        $service->activateOrder($service->createOrder($user, $package));
    }

    public function test_khach_chua_co_tai_khoan_khong_co_thong_tin_goi(): void
    {
        $this->seed();
        $guest = SupportMessage::create(['name' => 'Khách A', 'message' => 'Xin chào']);

        $this->assertNull($guest->resolveSender()['account']);
    }

    public function test_hoc_sinh_mua_le_hien_goi_dang_dung_va_han_dung(): void
    {
        $this->seed();
        $student = User::factory()->create(['role' => 'student', 'created_by' => null]);
        $this->activate($student, 'Gói Bứt Phá Thử', 'student');
        $msg = SupportMessage::create(['user_id' => $student->id, 'name' => $student->name, 'message' => 'Em cần mua gói thêm']);

        $account = $msg->resolveSender()['account'];
        $this->assertTrue($account['independent']);
        $this->assertSame('Gói Bứt Phá Thử', $account['package']);
        $this->assertNotNull($account['expires']);
        $this->assertNull($account['pending_package']);
    }

    public function test_hoc_sinh_do_giao_vien_quan_ly_hien_goi_cua_giao_vien(): void
    {
        $this->seed();
        $teacher = User::factory()->create(['role' => 'teacher']);
        $this->activate($teacher, 'Gói Giáo Viên Thử', 'teacher');
        $student = User::factory()->create(['role' => 'student', 'created_by' => $teacher->id]);
        $msg = SupportMessage::create(['user_id' => $student->id, 'name' => $student->name, 'message' => 'Hỏi bài']);

        $account = $msg->resolveSender()['account'];
        $this->assertFalse($account['independent']);
        $this->assertTrue($account['inherited']);
        $this->assertSame($teacher->name, $account['teacher']);
        $this->assertSame('Gói Giáo Viên Thử', $account['package']);
    }

    public function test_don_cho_thanh_toan_duoc_bao_cho_admin(): void
    {
        $this->seed();
        $student = User::factory()->create(['role' => 'student', 'created_by' => null]);
        $package = Package::create([
            'name' => 'Gói Chờ Thanh Toán', 'slug' => 'goi-cho-thanh-toan', 'target_audience' => 'student',
            'price' => 149000, 'duration_days' => 90, 'max_students' => 1, 'is_active' => true,
        ]);
        app(SubscriptionService::class)->createOrder($student, $package); // đơn mới ở trạng thái chờ

        $account = $student->accountSnapshot();
        $this->assertNull($account['package']);
        $this->assertSame('Gói Chờ Thanh Toán', $account['pending_package']);
    }

    public function test_trang_tu_van_gan_thong_tin_goi_vao_the_hoi_thoai(): void
    {
        $this->seed();
        $student = User::factory()->create(['role' => 'student', 'created_by' => null]);
        $this->activate($student, 'Gói Hiển Thị Thử', 'student');
        SupportMessage::create(['user_id' => $student->id, 'name' => $student->name, 'message' => 'Chào admin']);

        $this->actingAs(User::where('role', 'admin')->firstOrFail())
            ->get('/quan-tri')
            ->assertOk()
            ->assertSee('data-account=', false)
            ->assertSee('Gói Hiển Thị Thử');
    }
}
