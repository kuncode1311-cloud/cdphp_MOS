<?php

namespace Tests\Feature;

use App\Models\PracticeTest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LearningJourneyTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_browse_the_learning_journey(): void
    {
        $this->seed();
        $test = PracticeTest::where('slug', 'k3-cd1-bai-1')->firstOrFail();
        $question = $test->questions()->create([
            'type' => 'MultipleChoice',
            'title' => 'Câu hỏi hành trình nội bộ',
            'configuration' => [],
            'position' => 0,
            'points' => 1,
            'is_published' => true,
        ]);
        $question->options()->create([
            'content' => 'Đáp án mẫu',
            'is_correct' => true,
            'position' => 0,
            'metadata' => [],
        ]);
        $this->actingAs(User::where('role', 'student')->firstOrFail());
        $this->get('/')->assertOk()->assertSee('mở khóa siêu năng lực')->assertSee('IC3 DIGITAL ADVENTURE');
        $this->get('/chuong-trinh/khoi-3-spark-level-1')->assertOk()->assertSee('Bản Đồ 7 Chủ Đề Học Tập');
        $this->get('/bai-luyen/k3-cd1-bai-1')->assertOk()->assertSee('Bắt đầu làm bài');
        $this->get('/bai-luyen/k3-cd1-bai-1/lam-bai')->assertOk()->assertSee('Đấu trường thử thách số thông minh')->assertSee('const questions');
    }
}
