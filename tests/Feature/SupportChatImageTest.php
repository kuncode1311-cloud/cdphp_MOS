<?php

namespace Tests\Feature;

use App\Models\SupportMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Kiểm tra admin gửi ảnh trong Live Chat: ảnh được lưu thật và ghi vào lịch sử hội thoại.
 */
class SupportChatImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_gui_anh_luu_vao_may_chu_va_lich_su_chat(): void
    {
        Storage::fake('public');
        $this->seed();
        $admin = User::where('role', 'admin')->firstOrFail();
        $msg = SupportMessage::create(['name' => 'Khách A', 'phone' => '0901234567', 'message' => 'Xin chào']);

        $res = $this->actingAs($admin)->postJson("/quan-tri/tin-nhan/{$msg->id}/anh", [
            'image' => UploadedFile::fake()->image('anh.png', 300, 300),
        ])->assertOk()->assertJson(['success' => true]);

        $url = $res->json('image');
        $this->assertStringStartsWith('/storage/support-chat/', $url);
        Storage::disk('public')->assertExists(str_replace('/storage/', '', $url));

        $turn = $msg->fresh()->conversation_history[0];
        $this->assertSame('admin', $turn['sender']);
        $this->assertSame($url, $turn['image']);
        $this->assertSame('replied', $msg->fresh()->status);

        // Trang quản trị phải hiển thị ảnh trong lịch sử chat sau khi tải lại
        $this->actingAs($admin)->get('/quan-tri')->assertOk()->assertSee($url, false);
    }

    public function test_tu_choi_tep_khong_phai_anh_va_giao_vien_khong_duoc_gui(): void
    {
        Storage::fake('public');
        $this->seed();
        $admin = User::where('role', 'admin')->firstOrFail();
        $teacher = User::where('role', 'teacher')->firstOrFail();
        $msg = SupportMessage::create(['name' => 'Khách A', 'phone' => '0901234567', 'message' => 'Xin chào']);

        $this->actingAs($admin)->postJson("/quan-tri/tin-nhan/{$msg->id}/anh", [
            'image' => UploadedFile::fake()->create('virus.php', 10, 'application/x-php'),
        ])->assertStatus(422);

        $this->actingAs($teacher)->postJson("/quan-tri/tin-nhan/{$msg->id}/anh", [
            'image' => UploadedFile::fake()->image('anh.png'),
        ])->assertForbidden();
    }
}
