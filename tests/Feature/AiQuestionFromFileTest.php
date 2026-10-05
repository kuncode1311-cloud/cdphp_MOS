<?php

namespace Tests\Feature;

use App\Services\GeminiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Soạn câu hỏi từ tệp: nền tảng là môn Tin học (nguồn ngoài Tin học chỉ làm bối cảnh), nguồn Tin học thì bám sát/trích nguyên văn,
 * và luôn gửi kèm chữ trích từ PDF.
 */
class AiQuestionFromFileTest extends TestCase
{
    use RefreshDatabase;

    private function pdfWithText(string $text): string
    {
        $stream = "BT /F1 18 Tf 50 700 Td ({$text}) Tj ET";
        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
            '<< /Length '.strlen($stream)." >>\nstream\n{$stream}\nendstream",
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];
        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $i => $body) {
            $offsets[] = strlen($pdf);
            $pdf .= ($i + 1)." 0 obj\n{$body}\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";
        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        return $pdf."trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";
    }

    public function test_chu_trich_tu_pdf_duoc_gui_cho_ai_va_chi_dan_tin_hoc_cho_nguon_ngoai_tin_hoc(): void
    {
        Http::preventStrayRequests();
        config([
            'services.question_ai.base_url' => 'https://ai.example.test/v1/',
            'services.question_ai.api_key' => 'khoa',
            'services.question_ai.model' => 'model-thu-nghiem',
        ]);
        $question = [
            'title' => 'Ha Noi la thu do cua Viet Nam dung khong',
            'type' => 'MultipleChoice',
            'needs_image' => false,
            'image_prompt' => null,
            'options' => [
                ['content' => 'Hà Nội', 'is_correct' => true],
                ['content' => 'Huế', 'is_correct' => false],
                ['content' => 'Đà Nẵng', 'is_correct' => false],
                ['content' => 'Cần Thơ', 'is_correct' => false],
            ],
        ];
        Http::fake(['ai.example.test/*' => Http::response([
            'choices' => [['message' => ['content' => json_encode([$question])], 'finish_reason' => 'stop']],
        ])]);

        $path = tempnam(sys_get_temp_dir(), 'mos-pdf-');
        try {
            file_put_contents($path, $this->pdfWithText('Ha Noi la thu do cua Viet Nam. Day la mot doan van de kiem tra viec trich chu tu tep PDF cua giao vien. '.str_repeat('Noi dung bai hoc. ', 10)));

            $questions = app(GeminiService::class)->generateQuestionsFromFiles([
                ['path' => $path, 'mime_type' => 'application/pdf', 'name' => 'dia-ly.pdf'],
            ], '', false, 1);

            // Nguồn chữ không phải Tin học: không áp bộ lọc "bám nguồn" nên câu AI trả về vẫn được giữ lại
            $this->assertCount(1, $questions);
            $this->assertSame('Ha Noi la thu do cua Viet Nam dung khong', $questions[0]['title']);

            Http::assertSent(function (Request $request) {
                $content = $request["messages"][0]["content"]; $prompt = end($content)["text"];

                return str_contains($prompt, 'Ha Noi la thu do cua Viet Nam')
                    && str_contains($prompt, 'PHẠM VI BẮT BUỘC')
                    && str_contains($prompt, 'MỌI câu hỏi phải thuộc môn Tin học')
                    && str_contains($prompt, 'Loại B')
                    && str_contains($prompt, 'KHÔNG hỏi chi tiết của chính tệp')
                    && str_contains($prompt, 'TRÍCH NGUYÊN VĂN');
            });
        } finally {
            unlink($path);
        }
    }
}
