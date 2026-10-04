<?php

namespace Tests\Feature;

use App\Models\Level;
use App\Models\Package;
use App\Models\PackageOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Admin cấp gói, gia hạn và mở khối thủ công cho học sinh mua lẻ ở trang Quản lý người dùng.
 */
class AdminGrantStudentPackageTest extends TestCase
{
    use RefreshDatabase;

    private function studentPackage(): Package
    {
        $package = Package::create([
            'name' => 'Gói Cấp Tay Thử', 'slug' => 'goi-cap-tay-thu', 'target_audience' => 'student',
            'price' => 149000, 'duration_days' => 90, 'max_students' => 1, 'is_active' => true,
        ]);
        $package->levels()->sync(Level::pluck('id')->take(2)->all());

        return $package;
    }

    public function test_admin_cap_goi_thu_cong_cho_hoc_sinh_mua_le(): void
    {
        $this->seed();
        $admin = User::where('role', 'admin')->firstOrFail();
        $student = User::factory()->create(['role' => 'student', 'created_by' => null, 'status' => 'active']);
        $package = $this->studentPackage();
        $levelIds = $package->levels->pluck('id')->all();

        $this->actingAs($admin)->putJson("/quan-tri/users/{$student->id}", [
            'name' => $student->name, 'email' => $student->email, 'role' => 'student',
            'status' => 'active', 'expires_at' => '2027-03-01', 'level_ids' => $levelIds,
            'grant_package_id' => $package->id, 'is_grant_level_form' => 1,
        ])->assertOk();

        $student->refresh();
        $this->assertSame('2027-03-01', $student->expires_at->format('Y-m-d'));
        $this->assertEqualsCanonicalizing($levelIds, $student->accessibleLevels->pluck('id')->all());

        $order = PackageOrder::where('user_id', $student->id)->firstOrFail();
        $this->assertSame('Gói Cấp Tay Thử', $order->package_name);
        $this->assertSame('Ban Quản Trị cấp', $order->payment_method);
        $this->assertSame(0, (int) $order->price);
        $this->assertTrue($order->isActive());

        // Hồ sơ phải hiện gói vừa cấp
        $this->assertSame('Gói Cấp Tay Thử', $student->packageSummary()['package']);
    }

    public function test_khong_chon_goi_thi_chi_gia_han_va_khong_tao_don(): void
    {
        $this->seed();
        $admin = User::where('role', 'admin')->firstOrFail();
        $student = User::factory()->create(['role' => 'student', 'created_by' => null]);

        $this->actingAs($admin)->putJson("/quan-tri/users/{$student->id}", [
            'name' => $student->name, 'email' => $student->email, 'role' => 'student',
            'status' => 'active', 'expires_at' => '2027-01-01', 'is_grant_level_form' => 1,
        ])->assertOk();

        $this->assertSame('2027-01-01', $student->fresh()->expires_at->format('Y-m-d'));
        $this->assertSame(0, PackageOrder::where('user_id', $student->id)->count());
        $this->assertCount(0, $student->fresh()->accessibleLevels);
    }

    public function test_nut_goi_va_khoi_chi_hien_cho_hoc_sinh_mua_le(): void
    {
        $this->seed();
        $admin = User::where('role', 'admin')->firstOrFail();
        $teacher = User::where('role', 'teacher')->firstOrFail();
        User::factory()->create(['role' => 'student', 'created_by' => null, 'name' => 'Bé Mua Lẻ Thử']);
        User::factory()->create(['role' => 'student', 'created_by' => $teacher->id, 'name' => 'Bé Của Cô Giáo']);

        $html = $this->actingAs($admin)->get('/quan-tri')->assertOk()->getContent();

        $this->assertStringContainsString('openGrantStudentPackageModal', $html);
        $this->assertStringContainsString('grant-student-package-modal', $html);
        $this->assertStringContainsString('Cấp Gói & Mở Khối Cho Học Sinh Mua Lẻ', $html);
    }

    public function test_giao_vien_khong_the_tu_cap_goi_cho_hoc_sinh(): void
    {
        $this->seed();
        $teacher = User::where('role', 'teacher')->firstOrFail();
        $student = User::factory()->create(['role' => 'student', 'created_by' => $teacher->id]);
        $package = $this->studentPackage();

        $this->actingAs($teacher)->putJson("/quan-tri/users/{$student->id}", [
            'name' => $student->name, 'email' => $student->email, 'role' => 'student',
            'status' => 'active', 'grant_package_id' => $package->id,
        ])->assertOk();

        $this->assertSame(0, PackageOrder::where('user_id', $student->id)->count());
    }
}
