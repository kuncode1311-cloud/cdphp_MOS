<?php

namespace Tests\Feature;

use App\Models\PracticeTest;
use App\Models\Question;
use App\Models\StudentMistake;
use App\Models\User;
use Database\Seeders\StudentMistakeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentMistakeNotebookTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_view_mistake_notebook_with_db_data(): void
    {
        $this->seed();
        $student = User::where('student_code', 'HS001')->firstOrFail();

        // Tạo câu hỏi mẫu cho bài test
        $test = PracticeTest::where('slug', 'k3-cd1-bai-1')->firstOrFail();
        $q1 = $test->questions()->create([
            'type' => 'MultipleChoice',
            'title' => 'Câu hỏi mẫu đang bị sai',
            'configuration' => [],
            'position' => 0,
            'points' => 1,
            'is_published' => true,
        ]);
        $q1->options()->create([
            'content' => 'Đáp án A',
            'is_correct' => true,
            'position' => 0,
        ]);

        $q2 = $test->questions()->create([
            'type' => 'MultipleChoice',
            'title' => 'Câu hỏi mẫu đã phục thù',
            'configuration' => [],
            'position' => 1,
            'points' => 1,
            'is_published' => true,
        ]);
        $q2->options()->create([
            'content' => 'Đáp án B',
            'is_correct' => true,
            'position' => 0,
        ]);

        // Tạo 1 lỗi chưa sửa (báo động đỏ: sai 3 lần)
        StudentMistake::create([
            'user_id' => $student->id,
            'question_id' => $q1->id,
            'practice_test_id' => $test->id,
            'wrong_count' => 3,
            'correct_count' => 0,
            'last_student_answer' => [1],
            'status' => 'unresolved',
            'last_wrong_at' => now()->subHour(),
        ]);

        // Tạo 1 lỗi đã sửa xong
        StudentMistake::create([
            'user_id' => $student->id,
            'question_id' => $q2->id,
            'practice_test_id' => $test->id,
            'wrong_count' => 1,
            'correct_count' => 2,
            'last_student_answer' => [0],
            'status' => 'resolved',
            'last_wrong_at' => now()->subDays(2),
            'last_resolved_at' => now()->subDay(),
        ]);

        $this->actingAs($student);

        // 1. Kiểm tra màn hình danh sách sổ tay câu sai
        $response = $this->get(route('mistakes.index'));
        $response->assertOk();
        $response->assertSee('Sổ Tay Câu Hỏi Cần Phục Thù');
        $response->assertSee('CẦN PHỤC THÙ');
        $response->assertSee('BÁO ĐỘNG ĐỎ');
        $response->assertSee('ĐÃ VƯỢT QUA');
        $response->assertSee('Câu hỏi mẫu đang bị sai');

        // 2. Kiểm tra bộ lọc trạng thái đã sửa
        $responseResolved = $this->get(route('mistakes.index', ['status' => 'resolved']));
        $responseResolved->assertOk();
        $responseResolved->assertSee('Câu hỏi mẫu đã phục thù');

        // 3. Kiểm tra bộ lọc báo động đỏ
        $responseRisk = $this->get(route('mistakes.index', ['risk' => 'high_risk']));
        $responseRisk->assertOk();
        $responseRisk->assertSee('Câu hỏi mẫu đang bị sai');

        // 4. Kiểm tra vào phòng thi phục thù câu hỏi cụ thể
        $launchResponse = $this->get(route('mistakes.launch', ['question_id' => $q1->id]));
        $launchResponse->assertOk();
        $launchResponse->assertSee('Đấu Trường Phục Thù');
    }
}
