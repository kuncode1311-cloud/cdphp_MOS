<?php

namespace Tests\Feature;

use App\Models\Level;
use App\Models\PracticeTest;
use App\Models\Program;
use App\Models\TestAttempt;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Góc Phụ Huynh: mọi con số (thẻ KPI, nhãn điểm cao nhất, nhận xét) phải khớp dữ liệu thật và cùng đổi theo bộ lọc thời gian.
 */
class ParentDashboardTimeFilterTest extends TestCase
{
    use RefreshDatabase;

    private function student(): User
    {
        $student = User::factory()->create(['role' => 'student']);
        $program = Program::create(['name' => 'MOS', 'slug' => 'ic3']);
        $level = Level::create(['program_id' => $program->id, 'name' => 'Khối 3', 'slug' => 'khoi-3', 'grade' => 3, 'position' => 1]);
        $topic = Topic::create(['level_id' => $level->id, 'name' => 'Căn bản về công nghệ', 'slug' => 'can-ban', 'position' => 1]);
        $test = PracticeTest::create([
            'topic_id' => $topic->id, 'name' => 'Bài 1', 'slug' => 'bai-1', 'duration_minutes' => 20,
            'pass_score' => 700, 'max_score' => 1000, 'is_published' => true, 'position' => 1,
        ]);

        $make = fn (int $score, $when) => TestAttempt::create([
            'user_id' => $student->id, 'practice_test_id' => $test->id, 'score' => $score,
            'correct_answers' => 1, 'total_questions' => 10, 'duration_seconds' => 120, 'completed_at' => $when,
        ]);
        $make(1000, now()->subDays(20));
        $make(214, now()->subDays(2));
        $make(286, now()->subDay());

        return $student;
    }

    public function test_loc_7_ngay_chi_tinh_bai_trong_7_ngay_va_khong_con_so_cu(): void
    {
        $student = $this->student();

        $json = $this->actingAs($student)->getJson(route('parent.dashboard', ['time_range' => '7days']))->assertOk()->json();
        $this->assertSame(2, $json['kpis']['totalAttempts']);
        $this->assertSame(286, (int) $json['kpis']['highestScore']);
        $ticker = $json['charts']['line']['ticker'];
        $this->assertSame(286, (int) $ticker['peakScore']);
        $this->assertSame(414, (int) $ticker['distanceToPass']);
        $this->assertSame('▲ Tăng 72 điểm', $ticker['diffText']);
        $this->assertSame('7 ngày qua', $json['timeRangeLabel']);

        $html = $this->actingAs($student)->get(route('parent.dashboard', ['time_range' => '7days']))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/id="tickerPeakScore"[^>]*>\s*286đ</', $html);
        $this->assertDoesNotMatchRegularExpression('/id="tickerPeakScore"[^>]*>\s*(1000|857)đ</', $html);
    }

    public function test_loc_tat_ca_thay_diem_cao_nhat_that_va_khong_co_bai_thi_hien_chua_co(): void
    {
        $student = $this->student();

        $all = $this->actingAs($student)->getJson(route('parent.dashboard'))->assertOk()->json();
        $this->assertSame(1000, (int) $all['charts']['line']['ticker']['peakScore']);

        $empty = User::factory()->create(['role' => 'student']);
        $html = $this->actingAs($empty)->get(route('parent.dashboard', ['time_range' => '7days']))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/id="tickerPeakScore"[^>]*>\s*Chưa có</', $html);
        $this->assertStringNotContainsString('Tăng 304', $html);
        $this->assertStringNotContainsString('Sáng tạo nội dung', $html);
    }
}
