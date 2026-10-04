<?php

namespace Tests\Feature;

use App\Models\PracticeTest;
use App\Models\TestAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Bảng Vàng không được trống khi vòng thi đua mới chỉ có ít học sinh làm bài.
 */
class LeaderboardSparseRoundTest extends TestCase
{
    use RefreshDatabase;

    private function attempt(User $user, int $score, $when): void
    {
        $test = PracticeTest::firstOrFail();
        TestAttempt::create([
            'user_id' => $user->id,
            'practice_test_id' => $test->id,
            'score' => $score,
            'correct_answers' => 9,
            'total_questions' => 10,
            'duration_seconds' => 120,
            'completed_at' => $when,
        ]);
    }

    public function test_vong_moi_chi_co_mot_hoc_sinh_thi_mo_rong_de_hien_thi_cac_ban_xuat_sac_truoc_do(): void
    {
        $this->seed();
        Cache::flush();

        $names = ['Bạn Hạng Nhất', 'Bạn Hạng Nhì', 'Bạn Hạng Ba'];
        foreach ($names as $i => $name) {
            $old = User::factory()->create(['role' => 'student', 'name' => $name]);
            $this->attempt($old, 950 - $i * 50, now()->subDays(20));
        }
        $current = User::factory()->create(['role' => 'student', 'name' => 'Bạn Mới Vào']);
        $this->attempt($current, 800, now());

        $this->actingAs($current)->get(route('achievements', ['grade' => 'all']))
            ->assertOk()
            ->assertSee('Bạn Hạng Nhất')
            ->assertSee('Bạn Hạng Nhì')
            ->assertSee('Bạn Hạng Ba');
    }

    public function test_vong_hien_tai_du_hoc_sinh_thi_khong_keo_diem_cu(): void
    {
        $this->seed();
        Cache::flush();

        $old = User::factory()->create(['role' => 'student', 'name' => 'Bạn Cũ Quá Lâu']);
        $this->attempt($old, 990, now()->subDays(20));

        $active = [];
        foreach (['Bạn A', 'Bạn B', 'Bạn C'] as $i => $name) {
            $user = User::factory()->create(['role' => 'student', 'name' => $name]);
            $this->attempt($user, 900 - $i * 10, now());
            $active[] = $user;
        }

        $this->actingAs($active[0])->get(route('achievements', ['grade' => 'all']))
            ->assertOk()
            ->assertSee('Bạn B')
            ->assertDontSee('Bạn Cũ Quá Lâu');
    }
}
