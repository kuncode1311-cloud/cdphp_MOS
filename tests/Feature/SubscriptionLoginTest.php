<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Kiểm thử việc chặn đăng nhập và đá phiên khi hết hạn gói, cho cả 3 nhóm:
 * giáo viên, học sinh lẻ (tự có gói), học sinh phụ thuộc gói của giáo viên.
 */
class SubscriptionLoginTest extends TestCase
{
    use RefreshDatabase;

    private function teacher(array $attributes = []): User
    {
        $teacher = User::where('email', 'teacher@ic3.test')->firstOrFail();
        $teacher->forceFill($attributes)->save();

        return $teacher;
    }

    private function createStudent(array $attributes): User
    {
        return User::create($attributes + [
            'name' => 'Học sinh kiểm thử',
            'email' => strtolower($attributes['student_code']) . '@student.ic3.local',
            'password' => '123456',
            'role' => 'student',
            'status' => 'active',
        ]);
    }

    private function login(string $code)
    {
        return $this->post('/dang-nhap', ['login' => $code, 'password' => '123456']);
    }

    public function test_hoc_sinh_le_het_han_bi_chan_dang_nhap(): void
    {
        $this->seed();
        $this->createStudent(['student_code' => 'HSLE1', 'created_by' => null, 'expires_at' => now()->subDays(2)]);

        $this->login('HSLE1')
            ->assertSessionHasErrors(['login' => 'Tài khoản của bạn đã hết hạn sử dụng. Vui lòng liên hệ để gia hạn gói.']);
        $this->assertGuest();
    }

    public function test_hoc_sinh_le_con_han_dang_nhap_duoc(): void
    {
        $this->seed();
        $this->createStudent(['student_code' => 'HSLE2', 'created_by' => null, 'expires_at' => now()->addDays(30)]);

        $this->login('HSLE2')->assertRedirect(route('home'));
        $this->assertAuthenticated();
    }

    public function test_hoc_sinh_phu_thuoc_giao_vien_het_han_bi_chan_du_tai_khoan_con_han(): void
    {
        $this->seed();
        $teacher = $this->teacher(['expires_at' => now()->subDay()]);
        // Học sinh không có hạn riêng, dùng gói của giáo viên
        $this->createStudent(['student_code' => 'HSGV1', 'created_by' => $teacher->id, 'expires_at' => null]);

        $this->login('HSGV1')->assertSessionHasErrors(['login']);
        $this->assertGuest();
    }

    public function test_hoc_sinh_phu_thuoc_giao_vien_con_han_dang_nhap_duoc(): void
    {
        $this->seed();
        $teacher = $this->teacher(['expires_at' => now()->addMonths(6)]);
        $this->createStudent(['student_code' => 'HSGV2', 'created_by' => $teacher->id, 'expires_at' => null]);

        $this->login('HSGV2')->assertRedirect(route('home'));
        $this->assertAuthenticated();
    }

    public function test_hoc_sinh_phu_thuoc_giao_vien_bi_khoa_bi_chan(): void
    {
        $this->seed();
        $teacher = $this->teacher(['status' => 'suspended', 'expires_at' => now()->addMonths(6)]);
        $this->createStudent(['student_code' => 'HSGV3', 'created_by' => $teacher->id]);

        $this->login('HSGV3')->assertSessionHasErrors(['login']);
        $this->assertGuest();
    }

    public function test_giao_vien_het_han_bi_chan_dang_nhap(): void
    {
        $this->seed();
        $this->teacher(['expires_at' => now()->subDay()]);

        $this->login('teacher')->assertSessionHasErrors(['login']);
        $this->assertGuest();
    }

    public function test_het_han_trong_hom_nay_van_con_hieu_luc(): void
    {
        $this->seed();
        $this->teacher(['expires_at' => now()->startOfDay()]);

        $this->login('teacher')->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_phien_dang_mo_bi_da_ra_khi_het_han(): void
    {
        $this->seed();
        $student = $this->createStudent(['student_code' => 'HSLE3', 'created_by' => null, 'expires_at' => now()->subDay()]);

        $this->actingAs($student)->get('/')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_giao_vien_dang_mo_bi_da_ra_khi_het_han_trong_phien(): void
    {
        $this->seed();
        $teacher = $this->teacher(['expires_at' => now()->subDay()]);

        $this->actingAs($teacher)->get('/quan-tri')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_admin_khong_bi_anh_huong_boi_han_dung(): void
    {
        $this->seed();
        $admin = User::where('role', 'admin')->firstOrFail();
        $admin->forceFill(['expires_at' => now()->subYear()])->save();

        $this->actingAs($admin)->get('/quan-tri')->assertOk();
    }
}
