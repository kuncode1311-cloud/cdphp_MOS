<?php

namespace Tests\Feature;

use App\Services\GeminiService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AiQuestionProviderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
        config([
            'services.question_ai.base_url' => 'https://ai.example.test/v1/',
            'services.question_ai.api_key' => 'khoa-api-rieng-thu-nghiem',
            'services.question_ai.model' => 'model-thu-nghiem',
            'services.gemini.api_keys' => 'khoa-gemini-thu-nghiem',
        ]);
    }

    public function test_api_rieng_duoc_goi_truoc_va_khong_can_khoa_gemini(): void
    {
        config(['services.gemini.api_keys' => '']);
        Http::fake(['ai.example.test/*' => Http::response($this->privateResponse())]);

        $questions = app(GeminiService::class)->generateQuestionsFromText('Soạn câu hỏi về bàn phím.');

        $this->assertCount(1, $questions);
        $this->assertSame(0, $questions[0]['options'][0]['position']);
        Http::assertSentCount(1);
        Http::assertSent(fn (Request $request) => $request->url() === 'https://ai.example.test/v1/chat/completions'
            && $request->hasHeader('Authorization', 'Bearer khoa-api-rieng-thu-nghiem')
            && $request['model'] === 'model-thu-nghiem'
            && $request['stream'] === false
            && str_contains($request['messages'][0]['content'][0]['text'], 'Soạn câu hỏi về bàn phím.'));
    }

    #[DataProvider('privateFailures')]
    public function test_chuyen_sang_gemini_khi_api_rieng_loi(string $failure): void
    {
        $privateResponse = match ($failure) {
            'mang' => Http::failedConnection(),
            'http' => Http::response(['error' => 'Dịch vụ đang bận.'], 503),
            'xac_thuc' => Http::response([], 401),
            'gioi_han' => Http::response([], 429),
            'json' => Http::response($this->privateResponse('Không phải JSON')),
            'cau_truc' => Http::response($this->privateResponse('[42]')),
            'doi_tuong_rong' => Http::response($this->privateResponse('{}')),
            'phuong_an' => Http::response($this->privateResponse('[{"title":"Câu hỏi?","options":[1,2]}]')),
            'thieu_dap_an' => Http::response($this->privateResponse(str_replace('true', 'false', $this->questionsJson()))),
            'bi_cat' => Http::response(['choices' => [['message' => ['content' => $this->questionsJson()], 'finish_reason' => 'length']]]),
            default => Http::response(['choices' => []]),
        };
        Http::fake([
            'ai.example.test/*' => $privateResponse,
            'generativelanguage.googleapis.com/*' => Http::response($this->geminiResponse()),
        ]);

        $this->assertCount(1, app(GeminiService::class)->generateQuestionsFromText('Thiết bị máy tính'));

        $requests = Http::recorded()->pluck(0);
        // Lỗi kết nối không tạo phản hồi để Laravel lưu vào danh sách đã ghi nhận.
        if ($failure !== 'mang') {
            $this->assertSame('ai.example.test', parse_url($requests[0]->url(), PHP_URL_HOST));
            Http::assertSentCount(2);
        }
        $this->assertSame('generativelanguage.googleapis.com', parse_url($requests->last()->url(), PHP_URL_HOST));
        Http::assertSent(fn (Request $request) => $request->hasHeader('x-goog-api-key', 'khoa-gemini-thu-nghiem')
            && ! $request->hasHeader('Authorization'));
    }

    public static function privateFailures(): array
    {
        return array_map(fn ($failure) => [$failure], [
            'mang', 'http', 'xac_thuc', 'gioi_han', 'json', 'cau_truc',
            'doi_tuong_rong', 'phuong_an', 'thieu_dap_an', 'bi_cat', 'rong',
        ]);
    }

    public function test_anh_va_pdf_den_api_rieng_truoc_khi_tai_len_google(): void
    {
        Http::fake(['ai.example.test/*' => Http::response($this->privateResponse())]);
        $path = tempnam(sys_get_temp_dir(), 'mos-ai-');

        try {
            file_put_contents($path, 'noi-dung-tep-thu-nghiem');
            $questions = app(GeminiService::class)->generateQuestionsFromFiles([
                ['path' => $path, 'mime_type' => 'image/png', 'name' => 'anh.png'],
                ['path' => $path, 'mime_type' => 'application/pdf', 'name' => 'de-thi.pdf'],
            ], 'Soạn câu hỏi lớp 3');

            $this->assertCount(1, $questions);
            Http::assertSentCount(1);
            Http::assertSent(function (Request $request) {
                $content = $request['messages'][0]['content'];

                return $content[0]['image_url']['url'] === 'data:image/png;base64,'.base64_encode('noi-dung-tep-thu-nghiem')
                    && $content[1]['file']['file_data'] === 'data:application/pdf;base64,'.base64_encode('noi-dung-tep-thu-nghiem')
                    && $content[1]['file']['filename'] === 'de-thi.pdf'
                    && str_contains($content[2]['text'], 'Soạn câu hỏi lớp 3');
            });
        } finally {
            unlink($path);
        }
    }

    public function test_pdf_chi_tai_len_google_sau_khi_api_rieng_that_bai(): void
    {
        Http::fake([
            'ai.example.test/*' => Http::response([], 503),
            'generativelanguage.googleapis.com/upload/v1beta/files' => Http::response([], 200, [
                'X-Goog-Upload-URL' => 'https://generativelanguage.googleapis.com/upload/session-test',
            ]),
            'generativelanguage.googleapis.com/upload/session-test' => Http::response([
                'file' => ['uri' => 'https://generativelanguage.googleapis.com/v1beta/files/test'],
            ]),
            'generativelanguage.googleapis.com/v1beta/models/*' => Http::response($this->geminiResponse()),
        ]);
        $path = tempnam(sys_get_temp_dir(), 'mos-ai-');

        try {
            file_put_contents($path, '%PDF-noi-dung-thu-nghiem');
            $this->assertCount(1, app(GeminiService::class)->generateQuestionsFromPdf($path));

            $urls = Http::recorded()->map(fn ($pair) => $pair[0]->url())->all();
            $this->assertSame([
                'https://ai.example.test/v1/chat/completions',
                'https://generativelanguage.googleapis.com/upload/v1beta/files',
                'https://generativelanguage.googleapis.com/upload/session-test',
                'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent',
            ], $urls);
            Http::assertSent(fn (Request $request) => str_contains($request->url(), ':generateContent')
                && $request['contents'][0]['parts'][0]['fileData']['mimeType'] === 'application/pdf');
        } finally {
            unlink($path);
        }
    }

    public function test_pdf_chuyen_sang_gemini_khi_api_rieng_tra_mang_rong(): void
    {
        Http::fake([
            'ai.example.test/*' => Http::response($this->privateResponse('[]')),
            'generativelanguage.googleapis.com/upload/v1beta/files' => Http::response([], 200, [
                'X-Goog-Upload-URL' => 'https://generativelanguage.googleapis.com/upload/session-empty-test',
            ]),
            'generativelanguage.googleapis.com/upload/session-empty-test' => Http::response([
                'file' => ['uri' => 'https://generativelanguage.googleapis.com/v1beta/files/empty-test'],
            ]),
            'generativelanguage.googleapis.com/v1beta/models/*' => Http::response($this->geminiResponse()),
        ]);
        $path = tempnam(sys_get_temp_dir(), 'mos-ai-empty-');

        try {
            file_put_contents($path, '%PDF-thoi-khoa-bieu');
            $questions = app(GeminiService::class)->generateQuestionsFromPdf($path);

            $this->assertCount(1, $questions);
            Http::assertSentCount(4);
        } finally {
            unlink($path);
        }
    }

    public function test_tai_lieu_loai_bo_cau_hoi_cong_nghe_khong_phai_tin_hoc(): void
    {
        $wrongQuestions = json_encode([[
            'title' => 'Môn Công nghệ của lớp 6B1 học vào buổi nào?',
            'type' => 'MultipleChoice',
            'options' => [
                ['content' => 'Buổi sáng thứ Hai', 'is_correct' => true],
                ['content' => 'Buổi chiều thứ Hai', 'is_correct' => false],
            ],
        ]], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        Http::fake([
            'ai.example.test/*' => Http::response($this->privateResponse($wrongQuestions)),
            'generativelanguage.googleapis.com/*' => Http::response($this->geminiResponse($wrongQuestions)),
        ]);
        $path = tempnam(sys_get_temp_dir(), 'mos-ai-filter-');

        try {
            file_put_contents($path, 'anh-thoi-khoa-bieu');
            $questions = app(GeminiService::class)->generateQuestionsFromImage($path);

            $this->assertSame([], $questions);
            Http::assertSentCount(2);
        } finally {
            unlink($path);
        }
    }

    public function test_tai_lieu_loai_bo_cau_hoi_phu_thuoc_anh_nguon_khong_dinh_kem(): void
    {
        $dependentQuestions = json_encode([[
            'title' => 'Môn Tin học của lớp 6B1 được học vào thời gian nào trong tuần?',
            'type' => 'MultipleChoice',
            'options' => [
                ['content' => 'Buổi sáng thứ Hai', 'is_correct' => true],
                ['content' => 'Buổi chiều thứ Ba', 'is_correct' => false],
            ],
        ]], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        Http::fake([
            'ai.example.test/*' => Http::response($this->privateResponse($dependentQuestions)),
            'generativelanguage.googleapis.com/*' => Http::response($this->geminiResponse($dependentQuestions)),
        ]);
        $path = tempnam(sys_get_temp_dir(), 'mos-ai-context-');

        try {
            file_put_contents($path, 'anh-thoi-khoa-bieu');
            $this->assertSame([], app(GeminiService::class)->generateQuestionsFromImage($path));
            Http::assertSentCount(2);
        } finally {
            unlink($path);
        }
    }

    public function test_ket_qua_rong_hop_le_khong_goi_them_gemini(): void
    {
        Http::fake(['ai.example.test/*' => Http::response($this->privateResponse('[]'))]);

        $this->assertSame([], app(GeminiService::class)->generateQuestionsFromText('Không có nội dung'));
        Http::assertSentCount(1);
    }

    public function test_chua_cau_hinh_api_rieng_van_dung_gemini(): void
    {
        config(['services.question_ai.base_url' => '', 'services.question_ai.model' => '']);
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response($this->geminiResponse())]);

        $this->assertCount(1, app(GeminiService::class)->generateQuestionsFromText('Bàn phím'));
        Http::assertSentCount(1);
    }

    public function test_xoay_khoa_gemini_sau_khi_api_rieng_that_bai(): void
    {
        config(['services.gemini.api_keys' => 'khoa-1, khoa-2']);
        Http::fake([
            'ai.example.test/*' => Http::response([], 502),
            'generativelanguage.googleapis.com/*' => Http::sequence()
                ->push([], 429)
                ->push($this->geminiResponse()),
        ]);

        $this->assertCount(1, app(GeminiService::class)->generateQuestionsFromText('Bàn phím'));
        Http::assertSentCount(3);
        Http::assertSent(fn (Request $request) => $request->hasHeader('x-goog-api-key', 'khoa-2'));
    }

    public function test_loi_ro_rang_khi_api_rieng_hong_va_khong_co_gemini(): void
    {
        config(['services.gemini.api_keys' => '']);
        Http::fake(['ai.example.test/*' => Http::response([], 502)]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('chưa cấu hình khóa Gemini dự phòng');

        app(GeminiService::class)->generateQuestionsFromText('Bàn phím');
    }

    public function test_chi_tao_va_luu_anh_khi_cau_hoi_thuc_su_can_minh_hoa(): void
    {
        Storage::fake('public');
        config(['services.question_ai.image_model' => 'model-tao-anh']);
        $questionJson = json_encode([[
            'title' => 'Quan sát hình minh họa, biểu tượng nào dùng để lưu tệp?',
            'type' => 'MultipleChoice',
            'needs_image' => true,
            'image_prompt' => 'Thanh công cụ có biểu tượng đĩa mềm và ba biểu tượng gây nhiễu.',
            'options' => [
                ['content' => 'Biểu tượng đĩa mềm', 'is_correct' => true],
                ['content' => 'Biểu tượng thùng rác', 'is_correct' => false],
            ],
        ]], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $imageData = $this->fakePngDataUrl(420, 640);
        Http::fakeSequence('ai.example.test/*')
            ->push($this->privateResponse($questionJson))
            ->push(['choices' => [['message' => ['images' => [['image_url' => ['url' => $imageData]]]]]]]);

        $service = app(GeminiService::class);
        $questions = $service->generateRequiredIllustrations($service->generateQuestionsFromText('Soạn câu hỏi về nút lưu tệp.'));

        $this->assertNotEmpty($questions[0]['illustration_path']);
        $this->assertStringStartsWith('/storage/question-assets/ai-', $questions[0]['illustration_path']);
        Storage::disk('public')->assertExists(preg_replace('#^/storage/#', '', $questions[0]['illustration_path']));
        $imageSize = getimagesize(Storage::disk('public')->path(preg_replace('#^/storage/#', '', $questions[0]['illustration_path'])));
        $this->assertSame([1280, 720], [$imageSize[0], $imageSize[1]]);
        Http::assertSentCount(2);
        Http::assertSent(fn (Request $request) => $request['model'] === 'model-tao-anh'
            && $request['modalities'] === ['text', 'image']
            && $request['size'] === '1280x720'
            && $request['aspect_ratio'] === '16:9');
    }

    public function test_bat_tao_anh_thi_van_co_anh_khi_ai_quen_danh_dau(): void
    {
        Storage::fake('public');
        config(['services.question_ai.image_model' => 'model-tao-anh']);
        $questionJson = json_encode([[
            'title' => 'Quan sát ảnh chân dung trong tệp, nên đặt tên tệp nào để dễ nhận biết?',
            'type' => 'MultipleChoice',
            'needs_image' => false,
            'image_prompt' => null,
            'options' => [
                ['content' => 'anh_chan_dung_deo_khau_trang.jpg', 'is_correct' => true],
                ['content' => 'tai_lieu_hoc_toan.docx', 'is_correct' => false],
            ],
        ]], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $imageData = $this->fakePngDataUrl(420, 640);
        Http::fakeSequence('ai.example.test/*')
            ->push($this->privateResponse($questionJson))
            ->push(['choices' => [['message' => ['images' => [['image_url' => ['url' => $imageData]]]]]]]);

        $service = app(GeminiService::class);
        $questions = $service->generateRequiredIllustrations(
            $service->generateQuestionsFromText('Soạn câu hỏi từ ảnh chân dung.', '', true),
            true
        );

        $this->assertTrue($questions[0]['needs_image']);
        $this->assertNotEmpty($questions[0]['illustration_path']);
        $this->assertStringStartsWith('/storage/question-assets/ai-', $questions[0]['illustration_path']);
    }

    public function test_prompt_chi_yeu_cau_anh_khi_giao_vien_bat_tuy_chon(): void
    {
        Http::fake(['ai.example.test/*' => Http::response($this->privateResponse())]);

        app(GeminiService::class)->generateQuestionsFromText('Soạn câu hỏi về biểu tượng lưu.', '', true);

        Http::assertSent(fn (Request $request) => str_contains(
            $request['messages'][0]['content'][0]['text'],
            'Chế độ tạo ảnh đang BẬT'
        ));
    }

    public function test_prompt_nhan_so_cau_giao_vien_muon_tao(): void
    {
        Http::fake(['ai.example.test/*' => Http::response($this->privateResponse())]);

        app(GeminiService::class)->generateQuestionsFromText('Soạn câu hỏi về bàn phím.', '', false, 7);

        Http::assertSent(fn (Request $request) => str_contains(
            $request['messages'][0]['content'][0]['text'],
            'Tạo đúng 7 câu hỏi'
        ));
    }

    private function questionsJson(): string
    {
        return json_encode([[
            'title' => 'Thiết bị nào dùng để nhập chữ?',
            'type' => 'MultipleChoice',
            'options' => [
                ['content' => 'Bàn phím', 'is_correct' => true],
                ['content' => 'Màn hình', 'is_correct' => false],
                ['content' => 'Loa', 'is_correct' => false],
                ['content' => 'Máy in', 'is_correct' => false],
            ],
        ]], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    private function privateResponse(?string $content = null): array
    {
        return ['choices' => [['message' => ['content' => $content ?? $this->questionsJson()], 'finish_reason' => 'stop']]];
    }

    private function geminiResponse(?string $content = null): array
    {
        return ['candidates' => [['content' => ['parts' => [['text' => $content ?? $this->questionsJson()]]]]]];
    }

    private function fakePngDataUrl(int $width = 420, int $height = 640): string
    {
        $image = imagecreatetruecolor($width, $height);
        $bg = imagecolorallocate($image, 219, 234, 254);
        $fg = imagecolorallocate($image, 37, 99, 235);
        imagefilledrectangle($image, 0, 0, $width, $height, $bg);
        imagefilledellipse($image, (int) ($width / 2), (int) ($height / 2), 160, 160, $fg);

        ob_start();
        imagepng($image);
        $binary = ob_get_clean();
        imagedestroy($image);

        return 'data:image/png;base64,'.base64_encode($binary);
    }
}
