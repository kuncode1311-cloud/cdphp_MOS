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

class QuestionStudioTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected PracticeTest $test;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $program = Program::create([
            'name' => 'IC3 GS6',
            'slug' => 'ic3-gs6',
        ]);

        $level = Level::create([
            'program_id' => $program->id,
            'name' => 'Khối 3',
            'slug' => 'khoi-3',
            'grade' => 3,
            'position' => 1,
        ]);

        $topic = Topic::create([
            'level_id' => $level->id,
            'name' => 'Căn bản máy tính',
            'slug' => 'can-ban-may-tinh',
            'position' => 1,
        ]);

        $this->test = PracticeTest::create([
            'topic_id' => $topic->id,
            'name' => 'Bài luyện 1',
            'slug' => 'bai-luyen-1',
            'duration_minutes' => 20,
            'pass_score' => 700,
            'max_score' => 1000,
            'difficulty' => 'Cơ bản',
            'is_published' => true,
        ]);
    }

    public function test_admin_can_access_question_studio(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.questions.studio'));
        $response->assertStatus(200);
        $response->assertSee('IC3 GS6');
        $response->assertSee('STUDIO ADMIN');
    }

    public function test_admin_can_store_question_with_options_atomically(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.questions.store'), [
            'practice_test_id' => $this->test->id,
            'title' => 'Thiết bị nào sau đây là thiết bị đầu vào?',
            'type' => 'MultipleChoice',
            'points' => 1,
            'is_published' => 1,
            'options' => [
                ['content' => 'Bàn phím (Keyboard)', 'is_correct' => 1],
                ['content' => 'Màn hình (Monitor)', 'is_correct' => 0],
                ['content' => 'Máy in (Printer)', 'is_correct' => 0],
                ['content' => 'Loa (Speaker)', 'is_correct' => 0],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('questions', [
            'practice_test_id' => $this->test->id,
            'title' => 'Thiết bị nào sau đây là thiết bị đầu vào?',
            'type' => 'MultipleChoice',
        ]);

        $question = Question::first();
        $this->assertCount(4, $question->options);
        $this->assertTrue($question->options()->where('content', 'Bàn phím (Keyboard)')->first()->is_correct);

        // Câu mới chỉ dùng schema nội bộ; không cần payload nguồn.
        $this->assertNull($question->raw_payload);

        // Check observer updated question_count
        $this->assertEquals(1, $this->test->fresh()->question_count);
    }

    public function test_admin_can_update_question_and_options_in_single_request(): void
    {
        /** @var Question $question */
        $question = $this->test->questions()->create([
            'title' => 'Câu hỏi cũ',
            'type' => 'MultipleChoice',
            'configuration' => [],
            'position' => 0,
            'points' => 1,
            'is_published' => true,
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.questions.update', $question), [
            'title' => 'Câu hỏi đã cập nhật nội dung mới',
            'type' => 'MultipleResponse',
            'points' => 2,
            'is_published' => 1,
            'options' => [
                ['content' => 'Đáp án A đúng', 'is_correct' => 1],
                ['content' => 'Đáp án B đúng', 'is_correct' => 1],
                ['content' => 'Đáp án C sai', 'is_correct' => 0],
            ],
        ]);

        $response->assertRedirect();
        $question->refresh();
        $this->assertEquals('Câu hỏi đã cập nhật nội dung mới', $question->title);
        $this->assertEquals('MultipleResponse', $question->type);
        $this->assertEquals(2, $question->points);
        $this->assertCount(3, $question->options);
        $this->assertEquals(2, $question->options()->where('is_correct', true)->count());
    }

    public function test_admin_can_delete_question_and_recalculates_count(): void
    {
        $question = $this->test->questions()->create([
            'title' => 'Câu cần xóa',
            'type' => 'MultipleChoice',
            'configuration' => [],
            'position' => 0,
            'points' => 1,
        ]);

        $this->assertEquals(1, $this->test->fresh()->question_count);

        $response = $this->actingAs($this->admin)->delete(route('admin.questions.destroy', $question));
        $response->assertRedirect();

        $this->assertDatabaseMissing('questions', ['id' => $question->id]);
        $this->assertEquals(0, $this->test->fresh()->question_count);
    }

    public function test_admin_can_create_level_without_slug(): void
    {
        $program = Program::first();
        $response = $this->actingAs($this->admin)->post(route('admin.levels.store'), [
            'program_id' => $program->id,
            'name' => 'IC3 GS6 Spark Level 1 — Khối 1',
            'grade' => 1,
            'position' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('levels', [
            'name' => 'IC3 GS6 Spark Level 1 — Khối 1',
            'grade' => 1,
        ]);
    }
}
