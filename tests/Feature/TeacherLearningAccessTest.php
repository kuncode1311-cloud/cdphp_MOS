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

class TeacherLearningAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_dashboard_is_only_available_to_students(): void
    {
        [$teacher, $student] = $this->learningFixture();
        $admin = User::factory()->create(['role' => 'admin']);
        $otherStudent = User::factory()->create(['role' => 'student']);

        $this->actingAs($teacher)
            ->get(route('parent.dashboard'))
            ->assertForbidden();

        $this->actingAs($teacher)
            ->get(route('home'))
            ->assertOk()
            ->assertDontSee('Góc Phụ Huynh');

        $this->actingAs($admin)
            ->get(route('parent.dashboard'))
            ->assertForbidden();

        $this->actingAs($student)
            ->get(route('parent.dashboard', ['student_id' => $otherStudent->id]))
            ->assertOk()
            ->assertViewHas('student', fn (User $viewedStudent) => $viewedStudent->is($student));
    }

    public function test_teacher_can_try_an_authorized_test_without_saving_an_attempt(): void
    {
        [$teacher, , $test] = $this->learningFixture();

        $this->actingAs($teacher)
            ->get(route('tests.show', $test))
            ->assertOk();

        $this->actingAs($teacher)
            ->get(route('tests.launch', $test))
            ->assertOk();

        $this->actingAs($teacher)
            ->postJson(route('attempts.store', $test), [
                'answers' => [[0]],
                'duration_seconds' => 10,
            ])
            ->assertOk()
            ->assertJson([
                'saved' => false,
                'attempt_id' => null,
                'score' => 1000,
            ]);

        $this->assertDatabaseCount('test_attempts', 0);
    }

    public function test_teacher_cannot_submit_a_test_outside_assigned_levels(): void
    {
        [$teacher, , , $otherTest] = $this->learningFixture();

        $this->actingAs($teacher)
            ->get(route('tests.show', $otherTest))
            ->assertForbidden();

        $this->actingAs($teacher)
            ->get(route('tests.launch', $otherTest))
            ->assertForbidden();

        $this->actingAs($teacher)
            ->postJson(route('attempts.store', $otherTest), ['answers' => [[0]]])
            ->assertForbidden();

        $this->assertDatabaseCount('test_attempts', 0);
    }

    public function test_student_attempt_is_saved_and_cannot_submit_outside_granted_levels(): void
    {
        [, $student, $test, $otherTest] = $this->learningFixture();

        $this->actingAs($student)
            ->postJson(route('attempts.store', $test), [
                'answers' => [[0]],
                'duration_seconds' => 10,
            ])
            ->assertOk()
            ->assertJson([
                'saved' => true,
                'score' => 1000,
            ]);

        $this->assertDatabaseHas('test_attempts', [
            'user_id' => $student->id,
            'practice_test_id' => $test->id,
            'score' => 1000,
        ]);

        $this->actingAs($student)
            ->postJson(route('attempts.store', $otherTest), ['answers' => [[0]]])
            ->assertForbidden();

        $this->assertDatabaseCount('test_attempts', 1);
    }

    public function test_admin_can_preview_and_submit_without_saving_an_attempt(): void
    {
        [, , $test] = $this->learningFixture();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('tests.launch', $test))
            ->assertOk();

        $this->actingAs($admin)
            ->postJson(route('attempts.store', $test), ['answers' => [[0]]])
            ->assertOk()
            ->assertJson([
                'saved' => false,
                'attempt_id' => null,
                'score' => 1000,
            ]);

        $this->assertDatabaseCount('test_attempts', 0);
    }

    public function test_guest_cannot_open_parent_dashboard_or_submit_attempts(): void
    {
        [, , $test] = $this->learningFixture();

        $this->get(route('parent.dashboard'))
            ->assertRedirect(route('login'));

        $this->postJson(route('attempts.store', $test), ['answers' => [[0]]])
            ->assertUnauthorized();

        $this->assertDatabaseCount('test_attempts', 0);
    }

    private function learningFixture(): array
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);
        $program = Program::create(['name' => 'MOS', 'slug' => 'ic3-gs6-primary']);
        $level = Level::create(['program_id' => $program->id, 'name' => 'Khối 3', 'slug' => 'khoi-3', 'grade' => 3, 'position' => 1]);
        $otherLevel = Level::create(['program_id' => $program->id, 'name' => 'Khối 4', 'slug' => 'khoi-4', 'grade' => 4, 'position' => 2]);
        $teacher->teacherLevels()->attach($level);
        $student->accessibleLevels()->attach($level);

        $test = $this->createTestWithQuestion($level, 'bai-duoc-cap-quyen');
        $otherTest = $this->createTestWithQuestion($otherLevel, 'bai-ngoai-quyen');

        return [$teacher, $student, $test, $otherTest];
    }

    private function createTestWithQuestion(Level $level, string $slug): PracticeTest
    {
        $topic = Topic::create([
            'level_id' => $level->id,
            'name' => "Chủ đề {$level->grade}",
            'slug' => "chu-de-{$level->grade}",
            'position' => 1,
        ]);
        $test = PracticeTest::create([
            'topic_id' => $topic->id,
            'name' => "Bài kiểm thử {$level->grade}",
            'slug' => $slug,
            'duration_minutes' => 20,
            'pass_score' => 700,
            'max_score' => 1000,
            'difficulty' => 'Cơ bản',
            'is_published' => true,
            'position' => 1,
        ]);
        $question = Question::create([
            'practice_test_id' => $test->id,
            'type' => 'MultipleChoice',
            'title' => 'Chọn đáp án đúng',
            'configuration' => [],
            'position' => 0,
            'points' => 1,
            'is_published' => true,
        ]);
        $question->options()->createMany([
            ['content' => 'Đúng', 'metadata' => [], 'is_correct' => true, 'position' => 0],
            ['content' => 'Sai', 'metadata' => [], 'is_correct' => false, 'position' => 1],
        ]);

        return $test;
    }
}
