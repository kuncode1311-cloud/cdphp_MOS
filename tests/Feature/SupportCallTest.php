<?php

namespace Tests\Feature;

use App\Models\SupportCall;
use App\Models\SupportMessage;
use App\Models\User;
use App\Services\StringeeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Kiểm tra luồng gọi điện tư vấn Stringee: phân quyền, quay số, webhook và kịch bản ghi âm.
 */
class SupportCallTest extends TestCase
{
    use RefreshDatabase;

    private function cauHinhStringee(): StringeeService
    {
        config([
            'services.stringee.key_sid' => 'SK.test',
            'services.stringee.key_secret' => 'bi-mat',
            'services.stringee.from_number' => '842400000000',
            'services.stringee.record' => true,
            'services.stringee.webhook_base' => 'https://app.test',
        ]);

        return app(StringeeService::class);
    }

    private function admin(): User
    {
        $this->seed();

        return User::where('role', 'admin')->firstOrFail();
    }

    public function test_chuan_hoa_so_dien_thoai_viet_nam(): void
    {
        $s = $this->cauHinhStringee();
        $this->assertSame('84901234567', $s->normalizePhone('0901 234 567'));
        $this->assertSame('84901234567', $s->normalizePhone('+84901234567'));
        $this->assertNull($s->normalizePhone('Khách truy cập trang Đăng nhập'));
    }

    public function test_giao_vien_khong_duoc_goi_dien(): void
    {
        $this->cauHinhStringee();
        $this->seed();
        $teacher = User::where('role', 'teacher')->firstOrFail();

        $this->actingAs($teacher)->getJson('/quan-tri/cuoc-goi/token')->assertForbidden();
        $this->actingAs($teacher)->postJson('/quan-tri/cuoc-goi', ['phone' => '0901234567'])->assertForbidden();
    }

    public function test_admin_nhan_token_va_tao_cuoc_goi(): void
    {
        $this->cauHinhStringee();
        $admin = $this->admin();
        $msg = SupportMessage::create(['name' => 'Khách A', 'phone' => '0901234567', 'message' => 'Xin chào']);

        $this->actingAs($admin)->getJson('/quan-tri/cuoc-goi/token')
            ->assertOk()->assertJson(['configured' => true, 'from_number' => '842400000000'])
            ->assertJsonStructure(['token']);

        $this->actingAs($admin)->postJson('/quan-tri/cuoc-goi', ['phone' => 'abc'])->assertStatus(422);

        $this->actingAs($admin)->postJson('/quan-tri/cuoc-goi', ['phone' => '0901234567', 'support_message_id' => $msg->id])
            ->assertOk()->assertJson(['to_number' => '84901234567']);
        $this->assertDatabaseHas('support_calls', ['to_number' => '84901234567', 'status' => 'calling', 'support_message_id' => $msg->id]);
    }

    public function test_chua_cau_hinh_thi_bao_thieu_khoa(): void
    {
        config(['services.stringee.key_sid' => '', 'services.stringee.key_secret' => '', 'services.stringee.from_number' => '']);

        $this->actingAs($this->admin())->getJson('/quan-tri/cuoc-goi/token')->assertOk()->assertJson(['configured' => false]);
    }

    public function test_webhook_answer_tu_choi_khi_sai_chu_ky(): void
    {
        $this->cauHinhStringee();

        $this->postJson('/api/stringee/answer?sig=sai', ['to' => '84901234567'])->assertForbidden();
        $this->postJson('/api/stringee/answer', ['to' => '84901234567'])->assertForbidden();
    }

    public function test_webhook_answer_tra_kich_ban_ghi_am_va_noi_may(): void
    {
        $s = $this->cauHinhStringee();
        $call = SupportCall::create(['to_number' => '84901234567', 'status' => 'calling']);

        $res = $this->postJson('/api/stringee/answer?sig=' . $s->signature('answer'), [
            'to' => '84901234567', 'callId' => 'call-abc', 'customData' => (string) $call->id,
        ])->assertOk();

        $res->assertJsonPath('0.action', 'record')->assertJsonPath('0.format', 'mp3');
        $res->assertJsonPath('1.action', 'connect')->assertJsonPath('1.to.number', '84901234567')
            ->assertJsonPath('1.from.number', '842400000000');
        $this->assertSame('call-abc', $call->fresh()->stringee_call_id);
    }

    public function test_cap_nhat_trang_thai_tinh_thoi_luong(): void
    {
        $this->cauHinhStringee();
        $admin = $this->admin();
        $call = SupportCall::create(['to_number' => '84901234567', 'status' => 'calling', 'admin_id' => $admin->id]);

        $this->actingAs($admin)->patchJson("/quan-tri/cuoc-goi/{$call->id}", ['status' => 'answered'])->assertOk();
        $this->assertNotNull($call->fresh()->answered_at);

        $this->travel(75)->seconds();
        $this->actingAs($admin)->patchJson("/quan-tri/cuoc-goi/{$call->id}", ['status' => 'ended', 'note' => 'Đã tư vấn gói'])
            ->assertOk()->assertJsonPath('call.status', 'ended');
        $this->assertSame(75, $call->fresh()->duration);
    }

    public function test_cuoc_goi_khong_nghe_may_duoc_danh_dau_missed(): void
    {
        $this->cauHinhStringee();
        $admin = $this->admin();
        $call = SupportCall::create(['to_number' => '84901234567', 'status' => 'calling', 'admin_id' => $admin->id]);

        $this->actingAs($admin)->patchJson("/quan-tri/cuoc-goi/{$call->id}", ['status' => 'ended'])
            ->assertOk()->assertJsonPath('call.status', 'missed');
    }
}
