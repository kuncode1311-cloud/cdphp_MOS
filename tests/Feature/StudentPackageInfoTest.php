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
}
