<?php

namespace Tests\Feature;

use App\Models\Level;
use App\Models\PracticeTest;
use App\Models\Program;
use App\Models\Question;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Bài luyện chưa có câu hỏi không được dẫn học sinh tới trang lỗi 404.
 */
class EmptyPracticeTestForStudentTest extends TestCase
{
    use RefreshDatabase;

    private function setupData(): array
    {
        $program = Program::create(['name' => 'IC3 GS6 Tiểu học', 'slug' => 'ic3-gs6-primary']);
        $level = Level::create(['program_id' => $program->id, 'name' => 'Khối 3', 'slug' => 'khoi-3-thu', 'grade' => 3, 'position' => 1]);
        $topic = Topic::create(['level_id' => $level->id, 'name' => 'Chủ đề thử', 'slug' => 'chu-de-thu', 'position' => 1]);
        $empty = PracticeTest::create(['topic_id' => $topic->id, 'name' => 'Bài chưa có câu', 'slug' => 'bai-chua-co-cau', 'duration_minutes' => 10, 'pass_score' => 700, 'max_score' => 1000, 'is_published' => true, 'question_count' => 0]);
        $filled = PracticeTest::create(['topic_id' => $topic->id, 'name' => 'Bài đã có câu', 'slug' => 'bai-da-co-cau', 'duration_minutes' => 10, 'pass_score' => 700, 'max_score' => 1000, 'is_published' => true, 'question_count' => 1]);
        Question::create(['practice_test_id' => $filled->id, 'title' => 'Câu hỏi thử?', 'type' => 'MultipleChoice', 'points' => 1, 'is_published' => true, 'position' => 1]);

        $student = User::factory()->create(['role' => 'student', 'created_by' => null]);
        $student->accessibleLevels()->sync([$level->id]);

        return [$student, $level, $empty, $filled];
    }

    public function test_ban_do_khoa_bai_luyen_chua_co_cau_hoi_va_mo_bai_da_co_cau(): void
    {
        [$student, $level, $empty, $filled] = $this->setupData();

        $html = $this->actingAs($student)->get(route('levels.show', $level))->assertOk()->getContent();

        $this->assertStringContainsString('Thầy cô đang chuẩn bị câu hỏi', $html);
        $this->assertStringNotContainsString(route('tests.show', $empty), $html);
        $this->assertStringContainsString(route('tests.show', $filled), $html);
    }

    public function test_vao_thang_phong_thi_cua_bai_rong_duoc_dua_ve_ban_do_kem_thong_bao(): void
    {
        [$student, $level, $empty] = $this->setupData();

        $this->actingAs($student)->get(route('tests.launch', $empty))
            ->assertRedirect(route('levels.show', $level))
            ->assertSessionHas('info');

        $this->actingAs($student)->followingRedirects()->get(route('tests.launch', $empty))
            ->assertOk()
            ->assertSee('đang được thầy cô chuẩn bị câu hỏi');
    }
}
