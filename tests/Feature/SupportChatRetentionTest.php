<?php

namespace Tests\Feature;

use App\Models\SupportMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Lịch sử chat thành viên chỉ giữ 12 tháng, đúng với thông báo trong khung chat.
 */
class SupportChatRetentionTest extends TestCase
{
    use RefreshDatabase;

    public function test_xoa_doan_chat_qua_12_thang_va_giu_doan_chat_moi(): void
    {
        $old = SupportMessage::create(['name' => 'Cũ', 'message' => 'cũ', 'status' => 'pending']);
        $recent = SupportMessage::create(['name' => 'Mới', 'message' => 'mới', 'status' => 'pending']);

        // Đẩy thời gian cập nhật của đoạn chat cũ về 13 tháng trước
        SupportMessage::whereKey($old->id)->update(['updated_at' => now()->subMonths(13)]);

        $this->artisan('support:prune-chats')->assertSuccessful();

        $this->assertNull(SupportMessage::find($old->id));
        $this->assertNotNull(SupportMessage::find($recent->id));
    }
}
