<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Quản trị viên/Giáo viên xem trang học sinh sẽ thấy lời giải thích vì bài làm thử của họ không được lưu.
 */
class StaffPreviewNoticeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_thay_thong_bao_bai_lam_thu_khong_duoc_luu_o_so_tay_cau_sai(): void
    {
        $this->seed();
        $this->actingAs(User::where('role', 'admin')->firstOrFail())
            ->get(route('mistakes.index'))
            ->assertOk()
            ->assertSee('Bạn đang xem với tư cách Quản trị viên')
            ->assertSee('không được lưu');
    }

    public function test_hoc_sinh_khong_thay_thong_bao_nay(): void
    {
        $this->seed();
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)->get(route('mistakes.index'))
            ->assertOk()
            ->assertDontSee('Bạn đang xem với tư cách');
    }
}
