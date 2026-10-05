<?php

namespace Tests\Feature;

use App\Models\SupportMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Nhận diện người gửi trong Messenger chỉ theo tài khoản đăng nhập hoặc email khớp chính xác,
 * số điện thoại được chuẩn hóa và ảnh được xóa cùng đoạn chat.
 */
class SupportSenderIdentityTest extends TestCase
{
    use RefreshDatabase;

    public function test_khach_ten_giong_hoc_sinh_van_la_khach_vang_lai(): void
    {
        $this->seed();
        User::factory()->create(['role' => 'student', 'name' => 'Tài', 'email' => 'tai@hs.test']);

        $guest = SupportMessage::create(['name' => 'tài', 'phone' => '0901234567', 'message' => 'Xin chào']);

        $this->assertSame('guest', $guest->resolveSender()['type']);
    }

    public function test_email_khop_chinh_xac_khong_phan_biet_hoa_thuong_moi_duoc_nhan_dien(): void
    {
        $this->seed();
        User::factory()->create(['role' => 'student', 'created_by' => null, 'email' => 'Mua.Le@hs.test']);

        $exact = SupportMessage::create(['name' => 'A', 'email' => 'mua.le@HS.test', 'message' => 'x']);
        $partial = SupportMessage::create(['name' => 'B', 'email' => 'le@hs.test', 'message' => 'x']);

        $this->assertSame('🎓 Học Sinh (Mua Lẻ)', $exact->resolveSender()['label']);
        $this->assertSame('guest', $partial->resolveSender()['type']);
    }

    public function test_tin_nhan_luu_tai_khoan_dang_nhap_va_nhan_dien_dung_ke_ca_khi_email_khac(): void
    {
        $this->seed();
        $teacher = User::where('role', 'teacher')->firstOrFail();

        $this->actingAs($teacher)->postJson(route('support.message.send'), [
            'name' => 'Cô Giáo', 'email' => 'khac@mail.test', 'message' => 'Em cần hỗ trợ',
        ])->assertOk();

        $msg = SupportMessage::firstOrFail();
        $this->assertSame($teacher->id, $msg->user_id);
        $this->assertSame('teacher', $msg->resolveSender()['type']);
    }

    public function test_khach_phai_nhap_so_dien_thoai_hop_le_nguoi_dang_nhap_thi_tuy_chon(): void
    {
        $this->seed();

        // Số hợp lệ được chuẩn hóa
        $this->postJson(route('support.message.send'), ['name' => 'A', 'contact' => '+84 901 234 567', 'message' => 'm1'])->assertOk();
        $this->assertSame('0901234567', SupportMessage::latest('id')->first()->phone);

        // Khách nhập chuỗi linh tinh hoặc bỏ trống thì bị từ chối kèm thông báo rõ ràng, không tạo tin nhắn
        $before = SupportMessage::count();
        $this->postJson(route('support.message.send'), ['name' => 'B', 'contact' => '222', 'message' => 'm2'])
            ->assertStatus(422)->assertJsonFragment(['message' => 'Vui lòng nhập đúng số điện thoại hoặc Zalo (10 số, ví dụ 0912345678).']);
        $this->postJson(route('support.message.send'), ['name' => 'C', 'message' => 'm3'])->assertStatus(422);
        $this->assertSame($before, SupportMessage::count());

        // Người đã đăng nhập không bắt buộc có số điện thoại
        $student = User::factory()->create(['role' => 'student']);
        $this->actingAs($student)->postJson(route('support.message.send'), ['name' => $student->name, 'email' => $student->email, 'message' => 'm4'])->assertOk();
        $this->assertNull(SupportMessage::latest('id')->first()->phone);
    }
    public function test_xoa_doan_chat_xoa_luon_anh_da_gui(): void
    {
        Storage::fake('public');
        $this->seed();
        $admin = User::where('role', 'admin')->firstOrFail();
        $msg = SupportMessage::create(['name' => 'Khách', 'message' => 'Xin chào']);

        $url = $this->actingAs($admin)->postJson("/quan-tri/tin-nhan/{$msg->id}/anh", [
            'image' => UploadedFile::fake()->image('a.png'),
        ])->assertOk()->json('image');
        $this->assertDatabaseCount('support_images', 1);

        $this->actingAs($admin)->deleteJson("/quan-tri/tin-nhan/{$msg->id}")->assertOk();

        $this->assertDatabaseCount('support_images', 0);
        $this->assertDatabaseMissing('support_messages', ['id' => $msg->id]);
    }
}
