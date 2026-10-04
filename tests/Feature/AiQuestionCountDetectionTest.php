<?php

namespace Tests\Feature;

use App\Services\GeminiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * AI đọc yêu cầu của giáo viên để biết cần soạn bao nhiêu câu.
 */
class AiQuestionCountDetectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        config([
            'services.question_ai.base_url' => 'https://ai.example.test/v1/',
            'services.question_ai.api_key' => 'khoa',
            'services.question_ai.model' => 'model-thu-nghiem',
            'services.gemini.api_keys' => 'khoa-gemini',
        ]);
    }

    private function aiSays(string $content): void
    {
        Http::fake(['ai.example.test/*' => Http::response([
            'choices' => [['message' => ['content' => $content], 'finish_reason' => 'stop']],
        ])]);
    }

    public function test_ai_tra_ve_so_cau_giao_vien_yeu_cau(): void
    {
        $this->aiSays('{"count": 20}');

        $this->assertSame(20, app(GeminiService::class)->detectRequestedQuestionCount("Câu 1. A?\nCâu 2. B?\nlàm 20 câu nha"));
        Http::assertSent(fn (Request $request) => str_contains($request['messages'][0]['content'], 'làm 20 câu nha'));
    }

    public function test_ai_noi_khong_co_yeu_cau_so_cau_thi_tra_ve_null(): void
    {
        $this->aiSays('```json'."\n".'{"count": null}'."\n".'```');

        $this->assertNull(app(GeminiService::class)->detectRequestedQuestionCount('Đây là đề cương bài học về phần cứng máy tính cho học sinh lớp 3'));
    }

    public function test_so_cau_ngoai_khoang_bi_bo_qua_va_noi_dung_qua_ngan_khong_goi_ai(): void
    {
        $this->aiSays('{"count": 99}');
        $this->assertNull(app(GeminiService::class)->detectRequestedQuestionCount('Yêu cầu soạn thật nhiều câu hỏi cho học sinh'));

        Http::fake();
        $this->assertNull(app(GeminiService::class)->detectRequestedQuestionCount('soạn 5 câu'));
        Http::assertNothingSent();
    }

    public function test_tra_ve_false_khi_ai_khong_phan_tich_duoc(): void
    {
        Http::fake([
            'ai.example.test/*' => Http::response([], 503),
            'generativelanguage.googleapis.com/*' => Http::response([], 500),
        ]);

        $this->assertFalse(app(GeminiService::class)->detectRequestedQuestionCount('Hãy tạo cho tôi 15 câu hỏi về mạng máy tính'));
    }
}