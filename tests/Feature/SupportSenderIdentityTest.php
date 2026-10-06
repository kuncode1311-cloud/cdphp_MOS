<?php

namespace Tests\Feature;

use App\Models\SupportMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
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

    public function test_khach_vang_lai_khong_duoc_luu_chat_va_phai_dang_nhap_de_chat_admin(): void
    {
        Http::fake();
        $this->seed();
        $before = SupportMessage::count();

        // Khách vãng lai không có kênh Ban Quản Trị, và không tạo bản ghi nào trong CSDL
        $this->postJson(route('support.message.send'), ['name' => 'A', 'contact' => '0901234567', 'message' => 'm1', 'channel' => 'admin'])
            ->assertStatus(422)
            ->assertJsonFragment(['message' => 'Để chat với Ban Quản Trị, bạn vui lòng đăng nhập tài khoản nhé. Trợ lý AI vẫn sẵn sàng trả lời bạn ở đây!']);

        $this->postJson(route('support.message.send'), ['name' => 'B', 'message' => 'Gói giá bao nhiêu?', 'channel' => 'ai'])->assertOk();

        $this->assertSame($before, SupportMessage::count());
    }

    public function test_thanh_vien_chat_admin_luu_so_dien_thoai_da_chuan_hoa(): void
    {
        $this->seed();
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)->postJson(route('support.message.send'), [
            'name' => $student->name, 'contact' => '+84 901 234 567', 'message' => 'm1', 'channel' => 'admin',
        ])->assertOk();

        $this->assertSame('0901234567', SupportMessage::latest('id')->first()->phone);
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
