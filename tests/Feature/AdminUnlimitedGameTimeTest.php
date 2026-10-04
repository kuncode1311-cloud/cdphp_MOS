<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Quản trị viên có giờ chơi mini-game không giới hạn để thử nghiệm đầy đủ tính năng; học sinh vẫn bị giới hạn.
 */
class AdminUnlimitedGameTimeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_vao_phong_choi_khong_bi_khoa_va_thay_khong_gioi_han(): void
    {
        $this->seed();
        $admin = User::where('role', 'admin')->firstOrFail();
        $admin->forceFill(['game_time_seconds' => 0])->save();

        $this->actingAs($admin)->get(route('games'))
            ->assertOk()
            ->assertSee('Không giới hạn')
            ->assertSee('VÀO PHÒNG CHƠI NGAY')
            ->assertDontSee('HẾT GIỜ CHƠI');
    }

    public function test_admin_choi_khong_bi_tru_gio_va_doi_sao_khong_ton_sao(): void
    {
        $this->seed();
        $admin = User::where('role', 'admin')->firstOrFail();
        $admin->forceFill(['game_time_seconds' => 0, 'reward_stars' => 10])->save();

        $this->actingAs($admin)->postJson(route('games.consume'), ['seconds' => 30])
            ->assertOk()->assertJson(['success' => true, 'remaining_seconds' => User::UNLIMITED_GAME_SECONDS]);

        $this->actingAs($admin)->postJson(route('games.exchange'), ['package' => 1])
            ->assertOk()->assertJson(['success' => true, 'game_time_seconds' => User::UNLIMITED_GAME_SECONDS]);

        $admin->refresh();
        $this->assertSame(0, (int) $admin->game_time_seconds);
        $this->assertSame(10, (int) $admin->reward_stars);
    }

    public function test_hoc_sinh_het_gio_van_bi_khoa(): void
    {
        $this->seed();
        $student = User::factory()->create(['role' => 'student', 'created_by' => null, 'game_time_seconds' => 0]);

        $this->actingAs($student)->get(route('games'))
            ->assertOk()
            ->assertSee('HẾT GIỜ CHƠI');
    }
}
