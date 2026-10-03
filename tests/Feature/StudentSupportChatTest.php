<?php

namespace Tests\Feature;

use App\Models\SupportMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Học sinh mua lẻ được chat với Ban Quản Trị; học sinh do giáo viên quản lý chỉ thấy lời nhắc hỏi giáo viên.
 */
class StudentSupportChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_hoc_sinh_mua_le_thay_khung_chat_ho_tro(): void
    {
        $this->seed();
        $student = User::factory()->create(['role' => 'student', 'created_by' => null, 'name' => 'Bé Mua Lẻ']);

        $this->assertTrue($student->isIndependentStudent());
        $this->actingAs($student)->get(route('home'))
            ->assertOk()
            ->assertSee('Chat hỗ trợ')
            ->assertDontSee('Hỏi giáo viên');
    }

    public function test_hoc_sinh_do_giao_vien_quan_ly_chi_thay_loi_nhac_hoi_giao_vien(): void
    {
        $this->seed();
        $teacher = User::where('role', 'teacher')->firstOrFail();
        $student = User::factory()->create(['role' => 'student', 'created_by' => $teacher->id]);

        $this->assertFalse($student->isIndependentStudent());
        $this->actingAs($student)->get(route('home'))
            ->assertOk()
            ->assertSee('Hỏi giáo viên')
            ->assertSee($teacher->name)
            ->assertDontSee('Chat hỗ trợ');
    }

    public function test_tin_nhan_cua_hoc_sinh_mua_le_hien_o_messenger_voi_huy_hieu_mua_le(): void
    {
        $this->seed();
        $student = User::factory()->create(['role' => 'student', 'created_by' => null, 'name' => 'Bé Mua Lẻ']);

        $this->actingAs($student)->postJson(route('support.message.send'), [
            'name' => $student->name,
            'email' => $student->email,
            'message' => 'Em muốn hỏi về gói luyện thi',
        ])->assertOk()->assertJson(['ok' => true]);

        $this->assertDatabaseHas('support_messages', ['email' => $student->email, 'status' => 'pending']);

        $admin = User::where('role', 'admin')->firstOrFail();
        $this->actingAs($admin)->get('/quan-tri')->assertOk()->assertSee('Học Sinh (Mua Lẻ)');
    }

    public function test_giao_vien_va_admin_khong_thay_khung_chat_hoc_sinh(): void
    {
        $this->seed();
        $this->actingAs(User::where('role', 'admin')->firstOrFail())->get(route('home'))->assertDontSee('sc-fab', false);
    }
}
