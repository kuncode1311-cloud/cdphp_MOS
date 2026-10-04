<?php

namespace Tests\Feature;

use App\Models\GameSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Giới hạn số phút chơi mini-game mỗi ngày và nhật ký chơi game được gộp theo phiên.
 */
class DailyGameLimitTest extends TestCase
{
    use RefreshDatabase;

    private function student(int $balanceSeconds = 1800): User
    {
        $this->seed();
        GameSetting::set('max_daily_minutes', 20);

        return User::factory()->create(['role' => 'student', 'game_time_seconds' => $balanceSeconds]);
    }

    public function test_gioi_han_ngay_chan_viec_tru_gio_va_bao_nghi_den_mai(): void
    {
        $student = $this->student();
        // Hôm nay đã chơi 1100 giây, chỉ còn 100 giây trong giới hạn 20 phút (1200 giây)
        $student->gameTransactions()->create(['type' => 'play', 'stars_change' => 0, 'time_seconds_change' => -1100, 'description' => 'đã chơi']);

        $this->assertSame(100, $student->effectiveGameTimeSeconds());
        $this->assertSame(40, $student->consumeGameTime(60));   // trừ 60 giây, còn 40 giây trong ngày
        $this->assertSame(0, $student->consumeGameTime(60));    // chỉ trừ nốt 40 giây rồi dừng
        $this->assertSame(1700, (int) $student->fresh()->game_time_seconds); // ví còn lại: 1800 - 60 - 40
        $this->assertTrue($student->fresh()->hasReachedDailyGameLimit());

        $this->actingAs($student->fresh())->get(route('games'))
            ->assertOk()
            ->assertSee('ĐÃ CHƠI ĐỦ 20 PHÚT HÔM NAY');

        $this->actingAs($student->fresh())->getJson(route('games.time'))
            ->assertOk()
            ->assertJson(['game_time_seconds' => 0, 'daily_limit_reached' => true]);
    }

    public function test_hom_qua_khong_tinh_vao_gioi_han_hom_nay(): void
    {
        $student = $this->student();
        $old = $student->gameTransactions()->create(['type' => 'play', 'stars_change' => 0, 'time_seconds_change' => -1200, 'description' => 'hôm qua']);
        $old->forceFill(['created_at' => now()->subDay()->subHour(), 'updated_at' => now()->subDay()->subHour()])->save();

        $this->assertSame(0, $student->dailyGameSecondsUsed());
        $this->assertSame(1200, $student->dailyGameSecondsRemaining());
        $this->assertFalse($student->hasReachedDailyGameLimit());
    }

    public function test_nhat_ky_gop_cac_lan_tru_gio_lien_tuc_thanh_mot_dong(): void
    {
        $student = $this->student();

        foreach (range(1, 6) as $_) {
            $student->consumeGameTime(10);
        }

        $rows = $student->gameTransactions()->where('type', 'play')->get();
        $this->assertCount(1, $rows);
        $this->assertSame(-60, (int) $rows->first()->time_seconds_change);
        $this->assertSame('Chơi mini-game 1 phút', $rows->first()->description);
    }

    public function test_man_hinh_cai_dat_gop_cac_dong_cu_roi_rac_thanh_phien_choi(): void
    {
        $student = $this->student();
        // Dữ liệu cũ: 6 dòng mỗi dòng 10 giây, cách nhau 10 giây
        foreach (range(1, 6) as $i) {
            $tx = $student->gameTransactions()->create(['type' => 'play', 'stars_change' => 0, 'time_seconds_change' => -10, 'description' => 'Chơi mini-game 10 giây']);
            $tx->forceFill(['created_at' => now()->subSeconds(10 * (7 - $i)), 'updated_at' => now()->subSeconds(10 * (7 - $i))])->save();
        }

        $this->actingAs(User::where('role', 'admin')->firstOrFail())
            ->get(route('admin.games.settings'))
            ->assertOk()
            ->assertSee('Chơi mini-game 1 phút')
            ->assertDontSee('Chơi mini-game 10 giây');
    }
}
