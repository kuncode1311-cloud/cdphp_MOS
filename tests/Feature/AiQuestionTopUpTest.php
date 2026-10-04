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

    public function test_nhan_dien_so_cau_giao_vien_ghi_trong_noi_dung(): void
    {
        $detect = \App\Http\Controllers\Admin\AiQuestionController::class . '::detectRequestedCount';

        $this->assertSame(20, $detect("Câu 1. A?\nCâu 20. Hành động nào an toàn?\nD. Tải tệp email làm 20 câu nha"));
        $this->assertSame(15, $detect('Hãy tạo 15 câu hỏi về mạng'));
        $this->assertSame(8, $detect('làm 5 câu, à không, soạn đúng 8 câu'));
        $this->assertNull($detect("Câu 20. Hành động nào an toàn?\nCâu 19. Gì đó?"));
        $this->assertNull($detect('làm 99 câu'));
        $this->assertNull($detect('Soạn câu hỏi về bàn phím'));
    }

    public function test_noi_dung_ghi_20_cau_thi_soan_20_cau_theo_tung_lot_toi_da_10_cau(): void
    {
        $this->seed();
        $admin = User::where('role', 'admin')->firstOrFail();
        $calls = [];

        $this->mock(GeminiService::class, function (MockInterface $mock) use (&$calls) {
            $mock->shouldReceive('generateQuestionsFromText')->andReturnUsing(function (string $text, string $ctx, bool $img, int $count) use (&$calls) {
                $calls[] = $count;
                $base = count($calls) * 100;

                return array_map(fn ($i) => $this->q('Cau '.($base + $i)), range(1, $count));
            });
            $mock->shouldReceive('executionTrace')->andReturn([]);
        });

        $res = $this->actingAs($admin)->postJson('/quan-tri/ai/tao-cau-hoi', [
            'text' => "Câu 1. Thiết bị nhập là gì?\nlàm 20 câu nha",
            'question_count' => 5,
        ])->assertOk();

        $this->assertSame(20, $res->json('count'));
        $this->assertSame(20, $res->json('requested_count'));
        $this->assertSame(10, $calls[0]);
        $this->assertLessThanOrEqual(10, max($calls));
    }
}
