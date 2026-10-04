<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\GeminiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * Soạn câu hỏi bằng AI phải đủ số câu giáo viên chọn, kể cả khi AI hoặc bộ lọc bỏ bớt câu.
 */
class AiQuestionTopUpTest extends TestCase
{
    use RefreshDatabase;

    private function q(string $title): array
    {
        return ['title' => $title, 'type' => 'single_choice', 'options' => []];
    }

    public function test_bu_them_cau_khi_ai_tra_thieu_va_bo_cau_trung(): void
    {
        $this->seed();
        $admin = User::where('role', 'admin')->firstOrFail();

        $this->mock(GeminiService::class, function (MockInterface $mock) {
            $mock->shouldReceive('generateQuestionsFromText')->twice()->andReturn(
                [$this->q('Câu 1'), $this->q('Câu 2'), $this->q('Câu 3'), $this->q('Câu 4')],
                [$this->q('câu 4'), $this->q('Câu 5 mới'), $this->q('Câu 6 thừa')]
            );
            $mock->shouldReceive('executionTrace')->andReturn([]);
        });

        $res = $this->actingAs($admin)->postJson('/quan-tri/ai/tao-cau-hoi', [
            'text' => 'Soạn câu hỏi về bàn phím', 'question_count' => 5,
        ])->assertOk();

        $this->assertSame(5, $res->json('count'));
        $titles = array_column($res->json('questions'), 'title');
        $this->assertSame(['Câu 1', 'Câu 2', 'Câu 3', 'Câu 4', 'Câu 5 mới'], $titles);
    }

    public function test_cat_bot_khi_ai_tra_thua_va_khong_goi_bu_khi_da_du(): void
    {
        $this->seed();
        $admin = User::where('role', 'admin')->firstOrFail();

        $this->mock(GeminiService::class, function (MockInterface $mock) {
            $mock->shouldReceive('generateQuestionsFromText')->once()->andReturn(
                [$this->q('A'), $this->q('B'), $this->q('C'), $this->q('D')]
            );
            $mock->shouldReceive('executionTrace')->andReturn([]);
        });

        $res = $this->actingAs($admin)->postJson('/quan-tri/ai/tao-cau-hoi', [
            'text' => 'Soạn câu hỏi', 'question_count' => 3,
        ])->assertOk();

        $this->assertSame(3, $res->json('count'));
    }
}
