<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Quản trị viên bấm "Cổng Học Sinh" phải vào được giao diện học sinh để kiểm tra bài luyện.
 */
class AdminStudentPortalAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_bam_cong_hoc_sinh_vao_duoc_va_quay_lai_quan_tri_thi_het_che_do_xem(): void
    {
        $this->seed();
        $admin = User::where('role', 'admin')->firstOrFail();
        $this->actingAs($admin);

        // Vào thẳng "/" như cũ thì vẫn được đưa về trang Quản trị
        $this->get('/')->assertRedirect(route('admin.dashboard'));

        // Bấm nút "Cổng Học Sinh" thì vào được trang học sinh
        $this->get(route('home', ['xem' => 'hoc-sinh']))->assertOk();

        // Trong cổng học sinh bấm "Trang của em" (về "/") không bị đẩy ngược về Quản trị
        $this->get('/')->assertOk();

        // Quay lại trang Quản trị thì thôi chế độ xem cổng học sinh
        $this->get(route('admin.dashboard'))->assertOk();
        $this->get('/')->assertRedirect(route('admin.dashboard'));
    }

    public function test_nut_cong_hoc_sinh_tren_trang_quan_tri_co_tham_so_xem(): void
    {
        $this->seed();
        $this->actingAs(User::where('role', 'admin')->firstOrFail())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('xem=hoc-sinh', false);
    }
}
