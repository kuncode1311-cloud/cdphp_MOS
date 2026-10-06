<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Trang quản lý Trợ lý AI trong khu quản trị: quyền truy cập, gia hạn và thu hồi.
 */
class AiAssistantAdminTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $this->seed();

        return User::where('email', 'admin@ic3.test')->firstOrFail();
    }

    public function test_admin_xem_trang_tro_ly_ai(): void
    {
        $admin = $this->admin();
        $member = User::factory()->create(['role' => 'student', 'name' => 'Bé Gia Hạn']);
        $member->forceFill(['ai_assistant_until' => now()->addDays(5)])->save();

        $this->actingAs($admin)->get(route('admin.ai-assistant.index'))
            ->assertOk()
            ->assertSee('Tài khoản có quyền dùng Trợ lý AI')
            ->assertSee('Bé Gia Hạn');
    }

    public function test_nguoi_khong_phai_admin_khong_vao_duoc(): void
    {
        $this->seed();
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)->get(route('admin.ai-assistant.index'))->assertForbidden();
    }

    public function test_gia_han_cong_them_ngay_vao_han_hien_tai(): void
    {
        $admin = $this->admin();
        $member = User::factory()->create(['role' => 'student']);
        $until = now()->addDays(10)->startOfSecond();
        $member->forceFill(['ai_assistant_until' => $until])->save();

        $this->actingAs($admin)->post(route('admin.ai-assistant.extend', $member), ['days' => 30])->assertRedirect();

        $this->assertTrue($member->fresh()->ai_assistant_until->equalTo($until->copy()->addDays(30)));
    }

    public function test_gia_han_tai_khoan_het_han_tinh_tu_hom_nay(): void
    {
        $admin = $this->admin();
        $member = User::factory()->create(['role' => 'student']);
        $member->forceFill(['ai_assistant_until' => now()->subDays(3)])->save();

        $this->actingAs($admin)->post(route('admin.ai-assistant.extend', $member), ['days' => 15])->assertRedirect();

        $this->assertTrue($member->fresh()->hasAiAssistant());
        $this->assertTrue($member->fresh()->ai_assistant_until->between(now()->addDays(14), now()->addDays(16)));
    }

    public function test_thu_hoi_tat_quyen_ai_va_giu_nguyen_han_hoc_tap(): void
    {
        $admin = $this->admin();
        $member = User::factory()->create(['role' => 'student', 'expires_at' => now()->addDays(60)->toDateString()]);
        $member->forceFill(['ai_assistant_until' => now()->addDays(20)])->save();
        $expiry = $member->fresh()->expires_at->toDateString();

        $this->actingAs($admin)->post(route('admin.ai-assistant.revoke', $member))->assertRedirect();

        $fresh = $member->fresh();
        $this->assertNull($fresh->ai_assistant_until);
        $this->assertSame($expiry, $fresh->expires_at->toDateString());
    }

    public function test_tim_kiem_theo_ten(): void
    {
        $admin = $this->admin();
        User::factory()->create(['role' => 'student', 'name' => 'Tìm Được Tôi'])->forceFill(['ai_assistant_until' => now()->addDay()])->save();
        User::factory()->create(['role' => 'student', 'name' => 'Người Khác'])->forceFill(['ai_assistant_until' => now()->addDay()])->save();

        $this->actingAs($admin)->get(route('admin.ai-assistant.index', ['q' => 'Tìm Được']))
            ->assertOk()
            ->assertSee('Tìm Được Tôi')
            ->assertDontSee('Người Khác');
    }

    public function test_tao_goi_tro_ly_ai_moi_va_mo_ban(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.ai-assistant.packages.store'), [
            'name' => 'Gói AI Thử', 'target_audience' => 'student', 'price' => 59000, 'duration_days' => 30, 'is_active' => '1',
        ])->assertRedirect();

        $pkg = \App\Models\Package::where('name', 'Gói AI Thử')->firstOrFail();
        $this->assertTrue($pkg->grants_ai_assistant);
        $this->assertTrue($pkg->is_active);
        $this->assertSame(59000, $pkg->price);
        $this->assertSame(1, $pkg->max_students);
    }

    public function test_sua_gia_va_tat_mo_ban_goi_ai(): void
    {
        $admin = $this->admin();
        $pkg = \App\Models\Package::create([
            'slug' => 'goi-ai-test', 'name' => 'Gói AI Cũ', 'target_audience' => 'student', 'price' => 79000,
            'duration_days' => 30, 'max_students' => 1, 'is_active' => true, 'grants_ai_assistant' => true,
        ]);

        $this->actingAs($admin)->put(route('admin.ai-assistant.packages.update', $pkg), [
            'name' => 'Gói AI Mới', 'price' => 88000, 'duration_days' => 45,
        ])->assertRedirect();

        $fresh = $pkg->fresh();
        $this->assertSame('Gói AI Mới', $fresh->name);
        $this->assertSame(88000, $fresh->price);
        $this->assertSame(45, $fresh->duration_days);
        $this->assertFalse($fresh->is_active, 'Bỏ tick thì phải tạm ẩn');
    }

    public function test_khong_sua_duoc_goi_thuong_qua_trang_goi_ai(): void
    {
        $admin = $this->admin();
        $normal = \App\Models\Package::where('grants_ai_assistant', false)->firstOrFail();

        $this->actingAs($admin)->put(route('admin.ai-assistant.packages.update', $normal), [
            'name' => 'Bị đổi', 'price' => 1, 'duration_days' => 1,
        ])->assertNotFound();
    }

    public function test_goi_ai_khong_hien_trong_danh_muc_goi_dich_vu(): void
    {
        $admin = $this->admin();
        \App\Models\Package::where('grants_ai_assistant', true)->update(['name' => 'Gói AI Ẩn Danh Mục']);

        $this->actingAs($admin)->get(route('admin.dashboard') . '#tab-packages')
            ->assertOk()
            ->assertDontSee('Gói AI Ẩn Danh Mục');
    }

    public function test_ca_ba_tab_deu_mo_duoc(): void
    {
        $admin = $this->admin();

        foreach (['goi', 'tai-khoan', 'lich-su'] as $tab) {
            $this->actingAs($admin)->get(route('admin.ai-assistant.index', ['tab' => $tab]))->assertOk();
        }
    }

    public function test_go_tim_hien_tai_khoan_de_xac_nhan_dung_nguoi(): void
    {
        $admin = $this->admin();
        $student = User::where('student_code', 'HS001')->firstOrFail();

        $res = $this->actingAs($admin)->getJson(route('admin.ai-assistant.lookup', ['q' => 'HS001']))->assertOk();

        $first = $res->json('users.0');
        $this->assertSame($student->id, $first['id']);
        $this->assertSame($student->name, $first['name']);
        $this->assertSame('HS001', $first['code']);
        $this->assertSame('Học sinh', $first['role']);
        $this->assertFalse($first['ai_active']);
        // Gõ quá ngắn thì không tìm; không trả về tài khoản quản trị
        $this->actingAs($admin)->getJson(route('admin.ai-assistant.lookup', ['q' => 'H']))->assertJson(['users' => []]);
        $this->assertNotContains('admin@ic3.test', array_column($this->actingAs($admin)->getJson(route('admin.ai-assistant.lookup', ['q' => 'ic3.test']))->json('users'), 'email'));
    }

    public function test_cap_tro_ly_ai_cho_tai_khoan_da_chon(): void
    {
        $admin = $this->admin();
        $student = User::where('student_code', 'HS001')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.ai-assistant.grant'), ['user_id' => $student->id, 'days' => 30])
            ->assertRedirect()->assertSessionHas('ok', fn ($msg) => str_starts_with($msg, "Đã cấp Trợ lý AI cho {$student->name} 30 ngày"));

        $this->assertTrue($student->fresh()->hasAiAssistant());
        $this->assertTrue($student->fresh()->ai_assistant_until->between(now()->addDays(29), now()->addDays(31)));
        $this->actingAs($admin)->get(route('admin.ai-assistant.index'))->assertSee('Cấp Trợ lý AI cho tài khoản')->assertSee($student->name);

        // Cấp lần nữa khi còn hạn: thông báo là gia hạn
        $this->actingAs($admin)->post(route('admin.ai-assistant.grant'), ['user_id' => $student->id, 'days' => 10])
            ->assertSessionHas('ok', fn ($msg) => str_starts_with($msg, "Đã gia hạn Trợ lý AI cho {$student->name} thêm 10 ngày"));
    }

    public function test_cap_quyen_bat_buoc_chon_tai_khoan_hop_le(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.ai-assistant.grant'), ['days' => 30])->assertSessionHasErrors('user_id');
        $this->actingAs($admin)->post(route('admin.ai-assistant.grant'), ['user_id' => $admin->id])->assertSessionHasErrors('user_id');
        $this->assertNull($admin->fresh()->ai_assistant_until);
    }

    public function test_giao_vien_khong_cap_quyen_duoc(): void
    {
        $this->seed();
        $teacher = User::where('email', 'teacher@ic3.test')->firstOrFail();
        $student = User::where('student_code', 'HS001')->firstOrFail();

        $this->actingAs($teacher)->post(route('admin.ai-assistant.grant'), ['user_id' => $student->id])->assertForbidden();
        $this->actingAs($teacher)->getJson(route('admin.ai-assistant.lookup', ['q' => 'HS001']))->assertForbidden();
    }
}
