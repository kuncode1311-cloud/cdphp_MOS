<?php

namespace Tests\Feature;

use App\Console\Commands\VoiceEval;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Lệnh chấm tự động Trợ lý AI giọng nói (php artisan voice:eval): đáp án đúng tính từ database lúc chạy.
 */
class VoiceEvalCommandTest extends TestCase
{
    use RefreshDatabase;

    private User $student;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        Storage::fake('local');
        config(['services.question_ai.base_url' => 'https://router.test/v1', 'services.question_ai.model' => 'm', 'services.question_ai.api_key' => 'k']);
        $this->student = User::factory()->create([
            'role' => 'student', 'created_by' => null, 'status' => 'active', 'expires_at' => now()->addDays(30),
            'max_students' => 1, 'ai_assistant_until' => now()->addDays(10), 'reward_stars' => 123,
        ]);
    }

    private function report(): array
    {
        return json_decode(Storage::disk('local')->get(VoiceEval::REPORT), true);
    }

    public function test_case_passes_and_report_is_saved(): void
    {
        Http::fake(['router.test/*' => Http::response(['choices' => [['message' => ['content' => 'Chào em! Cô nghe đây.']]]])]);

        $this->artisan('voice:eval', ['--student' => $this->student->id, '--only' => 'Chào hỏi'])->assertExitCode(0);

        $this->assertSame(['total' => 1, 'passed' => 1], array_intersect_key($this->report()['summary'], ['total' => 0, 'passed' => 0]));
        $this->assertSame('ai', $this->report()['cases'][0]['route']);
    }

    public function test_numbers_are_checked_against_the_database(): void
    {
        Http::fake(['router.test/*' => Http::response(['choices' => [['message' => ['content' => 'Em đang có 123 sao thưởng nhé.']]]])]);

        // Đáp án đúng (123 sao) lấy từ database lúc chạy, câu trả lời phải chứa đúng số đó
        $this->artisan('voice:eval', ['--student' => $this->student->id, '--only' => 'Số sao'])->assertExitCode(0);
        $this->assertTrue($this->report()['cases'][0]['passed']);
    }

    public function test_wrong_ai_answer_fails_with_a_reason(): void
    {
        // AI bịa chuyện cười rồi tự ra câu hỏi: tình huống "Ngoài lề" yêu cầu không ra câu hỏi
        Http::fake(['router.test/*' => Http::response(['choices' => [['message' => ['content' => "Chuyện cười nè.\n[[LUYEN: bat ky]]"]]]])]);

        $this->artisan('voice:eval', ['--student' => $this->student->id, '--only' => 'Ngoài lề'])->assertExitCode(1);

        $case = $this->report()['cases'][0];
        $this->assertFalse($case['passed']);
        $this->assertContains('không được có câu hỏi luyện tập', $case['failures']);
    }
}
