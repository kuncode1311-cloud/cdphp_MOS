<?php

namespace Tests\Feature;

use App\Models\Level;
use App\Models\PracticeTest;
use App\Models\Program;
use App\Models\Question;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class InternalQuestionTypesTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('questionCases')]
    public function test_each_question_type_uses_internal_schema(
        string $type,
        array $options,
        mixed $correctAnswer,
    ): void {
        [$admin, $student, $test] = $this->domainFixture();

        $create = $this->actingAs($admin)->post(route('admin.questions.store'), [
            'practice_test_id' => $test->id,
            'title' => "Câu kiểm thử {$type}",
            'type' => $type,
            'points' => 1,
            'is_published' => 1,
            'options' => $options,
        ]);
        $create->assertRedirect();

        $question = Question::query()->firstOrFail();
        $this->assertNull($question->raw_payload);
        $this->assertNull($question->external_id);
        $this->assertCount(count($options), $question->options);

        $update = $this->actingAs($admin)->put(route('admin.questions.update', $question), [
            'title' => "Đã sửa {$type}",
            'type' => $type,
            'points' => 2,
            'is_published' => 1,
            'options' => $options,
        ]);
        $update->assertRedirect();
        $this->assertSame("Đã sửa {$type}", $question->fresh()->title);

        $render = $this->actingAs($admin)->get(route('tests.launch', $test));
        $render->assertOk()->assertViewHas(
            'questions',
            fn ($questions) => $questions->first()['title'] === "Đã sửa {$type}"
                && $questions->first()['type'] === $type,
        );
        $render->assertDontSee('raw_payload')->assertDontSee('storage://images');
        $render->assertSee('reviewResults');

        $submit = $this->actingAs($student)->postJson(route('attempts.store', $test), [
            'answers' => [0 => $correctAnswer],
            'duration_seconds' => 5,
        ]);
        $submit->assertOk()->assertJson([
            'correct_answers' => 1,
            'total_questions' => 1,
            'score' => 1000,
            'results' => [true],
        ]);
    }

    public static function questionCases(): array
    {
        return [
            'single choice' => ['MultipleChoice', [
                ['content' => 'Đúng', 'is_correct' => 1],
                ['content' => 'Sai', 'is_correct' => 0],
            ], [0]],
            'multiple response' => ['MultipleResponse', [
                ['content' => 'Đúng A', 'is_correct' => 1],
                ['content' => 'Sai', 'is_correct' => 0],
                ['content' => 'Đúng B', 'is_correct' => 1],
            ], [0, 2]],
            'matching' => ['Matching', [
                ['left' => 'CPU', 'right' => 'Bộ xử lý'],
                ['left' => 'RAM', 'right' => 'Bộ nhớ tạm'],
            ], [0 => 0, 1 => 1]],
            'choice text' => ['MultipleChoiceText', [
                ['left' => 'Máy in', 'right' => 'Phần cứng', 'available_options' => ['Phần cứng', 'Phần mềm']],
                ['left' => 'Word', 'right' => 'Phần mềm', 'available_options' => ['Phần cứng', 'Phần mềm']],
            ], [0 => 0, 1 => 1]],
            'hotspot' => ['Hotspot', [
                ['content' => 'Vùng đúng', 'is_correct' => 1, 'rect' => ['x' => 1, 'y' => 2, 'w' => 30, 'h' => 40]],
                ['content' => 'Vùng sai', 'is_correct' => 0, 'rect' => ['x' => 50, 'y' => 60, 'w' => 30, 'h' => 40]],
            ], 0],
            'sequence' => ['Sequence', [
                ['content' => 'Bước một'],
                ['content' => 'Bước hai'],
                ['content' => 'Bước ba'],
            ], [0, 1, 2]],
        ];
    }

    private function domainFixture(): array
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $program = Program::create(['name' => 'MOS', 'slug' => 'ic3-gs6-primary']);
        $level = Level::create(['program_id' => $program->id, 'name' => 'Khối 3', 'slug' => 'khoi-3', 'grade' => 3, 'position' => 1]);
        $student->accessibleLevels()->attach($level);
        $topic = Topic::create(['level_id' => $level->id, 'name' => 'Chủ đề', 'slug' => 'chu-de', 'position' => 1]);
        $test = PracticeTest::create([
            'topic_id' => $topic->id,
            'name' => 'Bài kiểm thử',
            'slug' => 'bai-kiem-thu-noi-bo',
            'duration_minutes' => 20,
            'pass_score' => 700,
            'max_score' => 1000,
            'difficulty' => 'Cơ bản',
            'is_published' => true,
        ]);

        return [$admin, $student, $test];
    }
}
