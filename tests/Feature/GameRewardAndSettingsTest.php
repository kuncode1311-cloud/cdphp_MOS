<?php

namespace Tests\Feature;

use App\Models\GameSetting;
use App\Models\PracticeTest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameRewardAndSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_exchange_reward_stars_for_game_time(): void
    {
        $this->seed();

        /** @var User $student */
        $student = User::where('role', 'student')->firstOrFail();
        $student->reward_stars = 1200;
        $student->game_time_seconds = 0;
        $student->save();

        // Kiểm tra trang Thành tích và Khu Trò Chơi tải thành công không lỗi view
        $this->actingAs($student)->get('/thanh-tich')->assertOk()->assertSeeText('CỬA HÀNG ĐỔI GIỜ CHƠI');
        $this->actingAs($student)->get('/tro-choi')->assertOk()->assertSeeText('CỬA HÀNG QUY ĐỔI GIỜ CHƠI');

        // Học sinh đổi Gói 1 (500 Sao = 3 phút = 180 giây)
        $response = $this->actingAs($student)
            ->postJson(route('games.exchange'), ['package' => 1]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'remaining_stars' => 700,
                'game_time_seconds' => 180,
            ]);

        $student->refresh();
        $this->assertEquals(700, $student->reward_stars);
        $this->assertEquals(180, $student->game_time_seconds);

        // Kiểm tra lịch sử giao dịch được ghi nhận
        $this->assertDatabaseHas('game_transactions', [
            'user_id' => $student->id,
            'type' => 'exchange',
            'stars_change' => -500,
            'time_seconds_change' => 180,
        ]);

        // Học sinh đổi tiếp Gói 2 (1000 Sao, nhưng chỉ còn 700 -> thất bại)
        $failResponse = $this->actingAs($student)
            ->postJson(route('games.exchange'), ['package' => 2]);

        $failResponse->assertOk()
            ->assertJson([
                'success' => false,
            ]);

        $student->refresh();
        $this->assertEquals(700, $student->reward_stars);
        $this->assertEquals(180, $student->game_time_seconds);
    }

    public function test_submitting_attempt_awards_reward_stars_to_student(): void
    {
        $this->seed();

        /** @var User $student */
        $student = User::where('role', 'student')->firstOrFail();
        $student->reward_stars = 100;
        $student->save();

        /** @var PracticeTest $test */
        $test = PracticeTest::where('slug', 'k3-cd1-bai-1')->firstOrFail();
        $question = $test->questions()->create([
            'type' => 'MultipleChoice',
            'title' => 'Câu hỏi kiểm tra điểm thưởng',
            'configuration' => [],
            'position' => 0,
            'points' => 1,
            'is_published' => true,
        ]);
        $question->options()->create([
            'content' => 'Đáp án đúng',
            'is_correct' => true,
            'position' => 0,
            'metadata' => [],
        ]);

        $response = $this->actingAs($student)
            ->postJson(route('attempts.store', $test), [
                'duration_seconds' => 60,
                'answers' => [
                    0 => [0],
                ],
            ]);

        $response->assertOk();

        $data = $response->json();
        $this->assertArrayHasKey('earned_stars', $data);
        $this->assertArrayHasKey('current_stars', $data);
        $this->assertEquals(1000, $data['score']);
        $this->assertEquals(1000, $data['earned_stars']);

        $student->refresh();
        $this->assertEquals(1100, $student->reward_stars);

        // Đảm bảo có lịch sử nhận sao từ bài thi
        $this->assertDatabaseHas('game_transactions', [
            'user_id' => $student->id,
            'type' => 'earn',
            'stars_change' => 1000,
        ]);
    }

    public function test_game_time_can_be_retrieved_and_consumed(): void
    {
        $this->seed();

        /** @var User $student */
        $student = User::where('role', 'student')->firstOrFail();
        $student->game_time_seconds = 180;
        $student->reward_stars = 350;
        $student->save();

        // 1. Kiểm tra API lấy thời gian còn lại
        $getResponse = $this->actingAs($student)
            ->getJson(route('games.time'));

        $getResponse->assertOk()
            ->assertJson([
                'game_enabled' => true,
                'game_time_seconds' => 180,
                'reward_stars' => 350,
            ]);

        // 2. Kiểm tra heartbeat tiêu hao 20 giây
        $consumeResponse = $this->actingAs($student)
            ->postJson(route('games.consume'), ['seconds' => 20]);

        $consumeResponse->assertOk()
            ->assertJson([
                'success' => true,
                'remaining_seconds' => 160,
            ]);

        $student->refresh();
        $this->assertEquals(160, $student->game_time_seconds);

        $this->assertDatabaseHas('game_transactions', [
            'user_id' => $student->id,
            'type' => 'play',
            'time_seconds_change' => -20,
        ]);
    }

    public function test_admin_can_manage_game_settings_and_adjust_stars(): void
    {
        $this->seed();

        /** @var User $admin */
        $admin = User::where('role', 'admin')->firstOrFail();
        /** @var User $student */
        $student = User::where('role', 'student')->firstOrFail();

        // Học sinh không được vào trang cài đặt game của admin
        $this->actingAs($student)
            ->get(route('admin.games.settings'))
            ->assertForbidden();

        /** @var User $teacher */
        $teacher = User::where('role', 'teacher')->firstOrFail();

        // Giáo viên cũng không được vào trang cài đặt game và không được sửa cấu hình
        $this->actingAs($teacher)
            ->get(route('admin.games.settings'))
            ->assertForbidden();

        $this->actingAs($teacher)
            ->post(route('admin.games.settings.update'), ['pkg1_stars' => 450])
            ->assertForbidden();

        // Kiểm tra Menu sidebar trên Dashboard: Giáo viên hoàn toàn không thấy mục Cài đặt Khu Trò Chơi
        $this->actingAs($teacher)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSeeText('Cài đặt Khu Trò Chơi')
            ->assertDontSeeText('KHU TRÒ CHƠI & THƯỞNG');

        // Admin truy cập thành công
        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSeeText('Cài đặt Khu Trò Chơi')
            ->assertSeeText('KHU TRÒ CHƠI & THƯỞNG');

        $this->actingAs($admin)
            ->get(route('admin.games.settings'))
            ->assertOk()
            ->assertSeeText('Cài đặt Khu Trò Chơi');

        // Admin cập nhật cài đặt gói đổi thưởng
        $updateResponse = $this->actingAs($admin)
            ->post(route('admin.games.settings.update'), [
                'game_enabled' => '1',
                'pkg1_stars' => 450,
                'pkg1_minutes' => 4,
                'pkg1_title' => 'Gói Tiết Kiệm Mới (4 Phút)',
                'pkg2_stars' => 900,
                'pkg2_minutes' => 9,
                'pkg2_title' => 'Gói Đỉnh Cao Mới (9 Phút)',
                'max_daily_minutes' => 45,
            ]);

        $updateResponse->assertRedirect();
        $this->assertEquals(450, (int) GameSetting::get('pkg1_stars'));
        $this->assertEquals(4, (int) GameSetting::get('pkg1_minutes'));
        $this->assertEquals('Gói Tiết Kiệm Mới (4 Phút)', GameSetting::get('pkg1_title'));

        // Admin thưởng tay 250 sao cho học sinh
        $initialStars = (int) $student->reward_stars;
        $rewardResponse = $this->actingAs($admin)
            ->post(route('admin.games.adjust-stars'), [
                'user_id' => $student->id,
                'amount' => 250,
                'reason' => 'Thưởng đột xuất thi HSG',
            ]);

        $rewardResponse->assertRedirect();

        $student->refresh();
        $this->assertEquals($initialStars + 250, $student->reward_stars);

        $this->assertDatabaseHas('game_transactions', [
            'user_id' => $student->id,
            'type' => 'earn',
            'stars_change' => 250,
        ]);
    }

    /**
     * Kiểm tra thời gian hiển thị lịch sử giao dịch và kết quả thi tuân thủ múi giờ Việt Nam (UTC+7)
     */
    public function test_game_transaction_and_attempt_display_vietnam_timezone(): void
    {
        $this->seed();

        /** @var User $admin */
        $admin = User::where('role', 'admin')->firstOrFail();
        /** @var User $student */
        $student = User::where('role', 'student')->firstOrFail();

        // Tạo 1 giao dịch với mốc giờ UTC đã biết: 2026-09-16 11:01:00 UTC (Tương ứng 18:01:00 giờ Việt Nam)
        $tx = $student->gameTransactions()->create([
            'type' => 'play',
            'stars_change' => 0,
            'time_seconds_change' => -10,
            'description' => 'Chơi mini-game 10 giây',
        ]);
        $tx->created_at = \Illuminate\Support\Carbon::parse('2026-09-16 11:01:00', 'UTC');
        $tx->save();

        // Kiểm tra accessor hiển thị đúng 18:01 16/09
        $this->assertEquals('18:01 16/09', $tx->created_at_vn);
        $this->assertEquals('18:01:00 16/09/2026', $tx->created_at_full_vn);

        // Kiểm tra trang Admin Cài đặt Trò chơi hiển thị đúng giờ Việt Nam
        $response = $this->actingAs($admin)->get(route('admin.games.settings'));
        $response->assertOk();
        $response->assertSeeText('18:01 16/09');
    }
}
