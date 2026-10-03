<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Hồ sơ học sinh hiển thị giáo viên quản lý, gói đang dùng và hạn sử dụng.
 */
class StudentPackageInfoTest extends TestCase
{
    use RefreshDatabase;

    public function test_hoc_sinh_do_giao_vien_quan_ly_thay_ten_giao_vien_va_goi_theo_giao_vien(): void
    {
        $this->seed();
        $teacher = User::where('role', 'teacher')->firstOrFail();
        $student = User::factory()->create(['role' => 'student', 'created_by' => $teacher->id]);

        $summary = $student->packageSummary();
        $this->assertTrue($summary['inherited']);
        $this->assertSame($teacher->name, $summary['teacher']);

        $this->actingAs($student)->get(route('home'))
            ->assertOk()
            ->assertSee('Giáo viên quản lý')
            ->assertSee($teacher->name);
    }

    public function test_hoc_sinh_mua_le_chua_co_goi_duoc_moi_xem_bang_gia(): void
    {
        $this->seed();
        $student = User::factory()->create(['role' => 'student', 'created_by' => null]);

        $this->assertFalse($student->packageSummary()['inherited']);
        $this->actingAs($student)->get(route('home'))
            ->assertOk()
            ->assertDontSee('Giáo viên quản lý')
            ->assertSee('Chưa có gói')
            ->assertSee('Không giới hạn');
    }

    public function test_hoc_sinh_mua_le_co_goi_dang_hoat_dong_thay_ten_goi_va_han_dung(): void
    {
        $this->seed();
        $student = User::factory()->create(['role' => 'student', 'created_by' => null]);
        $package = \App\Models\Package::create([
            'name' => 'Gói Thử Học Sinh', 'slug' => 'goi-thu-hoc-sinh', 'target_audience' => 'student',
            'price' => 69000, 'duration_days' => 30, 'max_students' => 1, 'is_active' => true,
        ]);
        $service = app(\App\Services\SubscriptionService::class);
        $service->activateOrder($service->createOrder($student, $package));

        $summary = $student->fresh()->packageSummary();
        $this->assertSame('Gói Thử Học Sinh', $summary['package']);
        $this->assertNotNull($summary['expires_at']);

        $this->actingAs($student->fresh())->get(route('home'))->assertOk()->assertSee('Gói Thử Học Sinh')->assertSee('Lịch sử đơn');
    }

    public function test_trang_lich_su_don_dung_cau_chu_danh_cho_hoc_sinh(): void
    {
        $this->seed();
        $student = User::factory()->create(['role' => 'student', 'created_by' => null]);

        $this->actingAs($student)->get(route('pricing.history'))
            ->assertOk()
            ->assertSee('Em chưa có đơn mua gói nào')
            ->assertDontSee('Thầy/Cô chưa có đơn');
    }
}
