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

class ShufflePracticeTestTest extends TestCase
{
    use RefreshDatabase;

    private function fixture(): array
    {
        $program = Program::create(['name' => 'IC3 GS6', 'slug' => 'ic3-gs6-primary']);
        $level = Level::create([
            'program_id' => $program->id,
            'name' => 'Khối 4',
            'slug' => 'khoi-4',
            'grade' => 4,
            'position' => 1,
        ]);
        $topic = Topic::create([
            'level_id' => $level->id,
            'name' => 'Sáng tạo nội dung',
            'slug' => 'sang-tao-noi-dung',
            'position' => 1,
        ]);
        $test = PracticeTest::create([
            'topic_id' => $topic->id,
            'name' => 'Bài luyện 1',
            'slug' => 'k4-cd4-bai-1',
            'difficulty' => 'Cơ bản',
            'duration_minutes' => 20,
            'is_published' => true,
            'shuffle_questions' => true,
            'shuffle_options' => true,
        ]);

        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $student->accessibleLevels()->attach($level);

        return [$admin, $student, $test];
    }

    public function test_admin_can_update_shuffle_settings(): void
    {
        [$admin, $student, $test] = $this->fixture();

        $response = $this->actingAs($admin)->put(route('admin.tests.update', $test), [
            'topic_id' => $test->topic_id,
            'name' => 'Bài luyện 1 Đã Đổi',
            'difficulty' => 'Trung bình',
            'duration_minutes' => 25,
            'shuffle_questions' => 0,
            'shuffle_options' => 1,
            'is_published' => 1,
        ]);

        $response->assertRedirect();
        $test->refresh();
        $this->assertFalse($test->shuffle_questions);
        $this->assertTrue($test->shuffle_options);
    }

    public function test_attempt_scoring_with_shuffled_question_ids(): void
    {
        [$admin, $student, $test] = $this->fixture();

        // Tạo 3 câu hỏi
        $q1 = Question::create([
            'practice_test_id' => $test->id,
            'type' => 'MultipleChoice',
            'title' => 'Câu hỏi 1',
            'position' => 0,
            'points' => 1,
            'is_published' => true,
        ]);
        $q1->options()->createMany([
            ['position' => 0, 'content' => 'A1 Đúng', 'is_correct' => true],
            ['position' => 1, 'content' => 'B1 Sai', 'is_correct' => false],
        ]);

        $q2 = Question::create([
            'practice_test_id' => $test->id,
            'type' => 'MultipleChoice',
            'title' => 'Câu hỏi 2',
            'position' => 1,
            'points' => 1,
            'is_published' => true,
        ]);
        $q2->options()->createMany([
            ['position' => 0, 'content' => 'A2 Sai', 'is_correct' => false],
            ['position' => 1, 'content' => 'B2 Đúng', 'is_correct' => true],
        ]);

        // Giả lập học sinh nhận câu hỏi theo thứ tự đảo ngược: [q2, q1]
        $shuffledIds = [$q2->id, $q1->id];
        // Học sinh trả lời: Câu đầu tiên (q2) chọn đáp án đúng (1), câu thứ hai (q1) chọn đáp án đúng (0)
        $submittedAnswers = [
            0 => [1], // vị trí 0 là q2 -> chọn [1] đúng
            1 => [0], // vị trí 1 là q1 -> chọn [0] đúng
        ];

        $response = $this->actingAs($student)->postJson(route('attempts.store', $test), [
            'answers' => $submittedAnswers,
            'question_ids' => $shuffledIds,
            'duration_seconds' => 60,
        ]);

        $response->assertOk();
        $data = $response->json();
        $this->assertSame(1000, $data['score']);
        $this->assertSame(2, $data['correct_answers']);
        $this->assertTrue($data['results'][0]);
        $this->assertTrue($data['results'][1]);
    }
}
