<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Soạn câu hỏi bằng API riêng, tự động dùng Gemini dự phòng khi cần.
 *
 * Hỗ trợ:
 * - Ảnh JPG/PNG/WEBP: gửi trực tiếp qua inlineData (base64)
 * - Nhiều ảnh cùng lúc trong 1 yêu cầu để AI nắm toàn bộ đề thi nhiều trang
 * - PDF: gửi qua Gemini File API
 * - Text prompt thuần: biên soạn đề cương theo yêu cầu
 *
 * Kiểm tra nghiêm ngặt tính xác thực của tài liệu: KHÔNG tự bịa dữ liệu nếu ảnh không liên quan.
 */
class GeminiService
{
    /** Danh sách API keys xoay vòng */
    private array $apiKeys;

    /** Danh sách các mô hình AI ưu tiên */
    private array $candidateModels = ['gemini-2.5-flash', 'gemini-flash-latest'];

    /** Nhật ký kỹ thuật an toàn cho DevTools, tuyệt đối không chứa API key. */
    private array $executionTrace = [];

    /** Endpoint REST của Gemini */
    private string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta';

    public function __construct()
    {
        $rawKeys = (string) config('services.gemini.api_keys', '');
        $this->apiKeys = array_values(array_filter(array_map('trim', explode(',', $rawKeys))));
    }

    public function executionTrace(): array
    {
        return $this->executionTrace;
    }

    /**
     * Chuẩn bị danh sách câu cần ảnh mà chưa gọi API tạo ảnh.
     */
    public function prepareIllustrationRequests(array $questions, bool $ensureAtLeastOne = false): array
    {
        $eligibleCount = 0;
        foreach ($questions as $index => $question) {
            $needsImage = (bool) ($question['needs_image'] ?? false);
            if ($needsImage && trim((string) ($question['image_prompt'] ?? '')) === '') {
                $questions[$index]['image_prompt'] = $this->buildFallbackImagePrompt($question);
            }

            if ((bool) ($questions[$index]['needs_image'] ?? false)
                && trim((string) ($questions[$index]['image_prompt'] ?? '')) !== '') {
                $eligibleCount++;
            }
        }

        if ($ensureAtLeastOne && $eligibleCount === 0 && ! empty($questions)) {
            $questions[0]['needs_image'] = true;
            $questions[0]['image_prompt'] = $this->buildFallbackImagePrompt($questions[0]);
        }

        return $questions;
    }

    /**
     * Chỉ tạo ảnh cho câu hỏi mà AI xác định thật sự cần dữ kiện trực quan.
     * Lỗi tạo ảnh không được làm mất danh sách câu hỏi đã soạn thành công.
     */
    public function generateRequiredIllustrations(array $questions, bool $ensureAtLeastOne = false): array
    {
        $questions = $this->prepareIllustrationRequests($questions, $ensureAtLeastOne);

        $generatedCount = 0;

        foreach ($questions as &$question) {
            $needsImage = (bool) ($question['needs_image'] ?? false);
            $imagePrompt = trim((string) ($question['image_prompt'] ?? ''));
            if (! $needsImage || $imagePrompt === '' || $generatedCount >= 3) {
                $question['illustration_path'] = null;
                continue;
            }

            try {
                $question['illustration_path'] = $this->generateIllustration($imagePrompt, (string) ($question['title'] ?? ''), $question);
                if ($question['illustration_path']) {
                    $generatedCount++;
                }
            } catch (\Throwable $e) {
                $question['illustration_path'] = null;
                Log::warning('AI soạn đề: Không tạo được ảnh minh họa, vẫn giữ câu hỏi chữ.', [
                    'error_type' => $e::class,
                ]);
            }
        }
        unset($question);

        return $questions;
    }

    private function buildFallbackImagePrompt(array $question): string
    {
        $title = trim((string) ($question['title'] ?? ''));

        // Chỉ lấy tiêu đề câu hỏi, tuyệt đối KHÔNG nối danh sách options (A, B, C, D) vào prompt
        // để tránh việc AI hiểu lầm và vẽ nguyên bảng trắc nghiệm, ô chọn radio lên tranh.
        return trim("Minh họa trực quan bối cảnh công nghệ sinh động cho câu hỏi: {$title}. "
            .'Bức tranh 3D hoàn chỉnh bố cục banner ngang 16:9, tuyệt đối không có chữ, không có đề thi hay phương án lựa chọn.');
    }

    /**
     * Tạo một prompt phổ quát duy nhất đúng với mọi trường hợp câu hỏi ngẫu nhiên.
     * Cung cấp câu hỏi và chỉ dẫn sư phạm dạng banner 16:9 widescreen hoàn chỉnh,
     * yêu cầu toàn bộ khung cảnh nằm trọn vẹn trong khung hình, không bị cắt xén biên,
     * và nghiêm cấm AI vẽ đề thi, bảng trắc nghiệm hay chữ số lên tranh.
     */
    public function buildOptimizedImagePrompt(string $description, string $questionTitle, array $question = []): string
    {
        $targetQuestion = trim($questionTitle !== '' ? $questionTitle : $description);
        $contextHint = ($description !== '' && $description !== $questionTitle) ? " Context details: {$description}." : '';

        return "Task: Generate a high quality cute 3D educational illustration for this school test question: "
            ."\"{$targetQuestion}\".{$contextHint} "
            ."Scene instruction: A delightful, vibrant 3D digital scene (Pixar/Disney 3D animation style) visually explaining the core computer science concept, digital tool, device, or practical situation in the question. "
            ."Banner framing & composition: Complete 16:9 widescreen landscape banner composition. All characters, digital devices, and objects must fit comfortably and entirely within the horizontal 16:9 banner frame with generous safe margins on all sides. Strictly NO objects or characters cut off at top, bottom, or edges. "
            ."Art style: Cute gamified 3D aesthetic, rich cheerful colors (amber orange, royal gold, emerald green, sky blue, vivid purple), smooth glossy 3D clay and plastic surfaces, warm welcoming studio lighting, playful and intuitive for elementary school kids. "
            ."Pedagogical rule: DO NOT reveal or highlight the correct answer. Strictly NO text, NO letters, NO words, NO numbers, NO labels, NO watermark, NO diagrams, NO schematics, NO test questionnaires, NO worksheets, NO multiple choice buttons or checkboxes. Pure pictorial 3D scene.";
    }

    private function generateIllustration(string $description, string $questionTitle, array $question = []): ?string
    {
        $baseUrl = rtrim(trim((string) config('services.question_ai.base_url')), '/');
        $model = trim((string) config('services.question_ai.image_model'));
        $prompt = $this->buildOptimizedImagePrompt($description, $questionTitle, $question);

        if ($baseUrl !== '' && $model !== '') {
            $privateImage = $this->generatePrivateIllustration($baseUrl, $model, $prompt);
            if ($privateImage) {
                return $privateImage;
            }
        }

        return $this->generateNativeGeminiIllustration($prompt);
    }

    private function generatePrivateIllustration(string $baseUrl, string $model, string $prompt): ?string
    {

        $startedAt = microtime(true);
        $request = Http::acceptJson()
            ->connectTimeout(max(1, (int) config('services.question_ai.connect_timeout', 10)))
            ->timeout(max(30, (int) config('services.question_ai.timeout', 60)));
        $apiKey = trim((string) config('services.question_ai.api_key'));
        if ($apiKey !== '') {
            $request = $request->withToken($apiKey);
        }

        $response = $request->post($baseUrl.'/chat/completions', [
            'model' => $model,
            'messages' => [['role' => 'user', 'content' => $prompt]],
            'modalities' => ['text', 'image'],
            'size' => '1280x720',
            'aspect_ratio' => '16:9',
            'image_config' => ['aspect_ratio' => '16:9'],
            'stream' => false,
        ]);

        if (! $response->successful()) {
            $this->executionTrace[] = $this->traceRow('9Router Image', $model, 'HTTP '.$response->status(), $startedAt, 'Bỏ qua ảnh');
            return null;
        }

        $dataUrl = $this->findImageDataUrl($response->json());
        if (! $dataUrl) {
            $this->executionTrace[] = $this->traceRow('9Router Image', $model, '200', $startedAt, 'Không có dữ liệu ảnh');
            return null;
        }

        $path = $this->storeGeneratedImage($dataUrl);
        $this->executionTrace[] = $this->traceRow('9Router Image', $model, '200', $startedAt, $path ? 'Đã tạo ảnh' : 'Ảnh không hợp lệ');

        return $path;
    }

    private function generateNativeGeminiIllustration(string $prompt): ?string
    {
        if ($this->apiKeys === []) {
            return null;
        }

        $model = trim((string) config('services.gemini.image_model', 'gemini-3.1-flash-image'));
        foreach ($this->apiKeys as $index => $key) {
            $startedAt = microtime(true);
            try {
                $response = Http::connectTimeout(10)->timeout(120)
                    ->withHeaders(['x-goog-api-key' => $key])
                    ->post("{$this->baseUrl}/models/{$model}:generateContent", [
                        'contents' => [['parts' => [['text' => $prompt]]]],
                        'generationConfig' => [
                            'responseModalities' => ['TEXT', 'IMAGE'],
                            'imageConfig' => [
                                'aspectRatio' => '16:9',
                            ],
                        ],
                    ]);
                if (! $response->successful()) {
                    $this->executionTrace[] = $this->traceRow('Google Gemini Image', $model, 'HTTP '.$response->status(), $startedAt, 'Thử khóa tiếp theo');
                    continue;
                }

                foreach ($response->json('candidates.0.content.parts', []) as $part) {
                    $inline = $part['inlineData'] ?? $part['inline_data'] ?? null;
                    if (! is_array($inline) || empty($inline['data'])) {
                        continue;
                    }
                    $mime = $inline['mimeType'] ?? $inline['mime_type'] ?? 'image/png';
                    $path = $this->storeGeneratedImage('data:'.$mime.';base64,'.$inline['data']);
                    $this->executionTrace[] = $this->traceRow('Google Gemini Image', $model, '200', $startedAt, $path ? 'Đã tạo ảnh' : 'Ảnh không hợp lệ');
                    return $path;
                }
            } catch (ConnectionException $e) {
                Log::warning("Gemini Image: Khóa #{$index} không kết nối được.", ['error_type' => $e::class]);
            }
        }

        return null;
    }

    private function storeGeneratedImage(string $dataUrl): ?string
    {
        if (! preg_match('/^data:(image\/(?:png|jpeg|webp));base64,(.+)$/s', $dataUrl, $matches)) {
            return null;
        }

        $binary = base64_decode($matches[2], true);
        if ($binary === false || strlen($binary) < 100) {
            return null;
        }

        // Tự động cắt theo tỉ lệ vàng 16:9 trung tâm chuẩn HD (1280x720),
        // loại bỏ hoàn toàn dải viền thừa và các chữ rác in ở mép đáy
        $cropped = $this->cropToLandscape16x9($binary);
        if ($cropped !== $binary) {
            $binary = $cropped;
            $extension = 'png';
        } else {
            $extension = match ($matches[1]) {
                'image/png' => 'png',
                'image/webp' => 'webp',
                default => 'jpg',
            };
        }

        $path = 'question-assets/ai-'.Str::uuid().'.'.$extension;
        Storage::disk('public')->put($path, $binary);

        return Storage::url($path);
    }

    /**
     * Tự động cắt ảnh về tỉ lệ 16:9 widescreen chuẩn HD (1280x720) lấy trọng tâm,
     * loại bỏ hoàn toàn các viền thừa trắng/đen và chữ rác in ở mép đáy.
     */
    private function cropToLandscape16x9(string $binary): string
    {
        if (! extension_loaded('gd')) {
            if (extension_loaded('imagick')) {
                return $this->cropWithImagick16x9($binary);
            }
            Log::warning('GeminiService: PHP GD/Imagick extension chưa được kích hoạt, ảnh minh họa sẽ giữ nguyên tỉ lệ gốc của AI.');
            return $binary;
        }

        $srcImage = @imagecreatefromstring($binary);
        if (! $srcImage) {
            return $binary;
        }

        $origW = imagesx($srcImage);
        $origH = imagesy($srcImage);

        if ($origW <= 0 || $origH <= 0) {
            imagedestroy($srcImage);
            return $binary;
        }

        $currentRatio = $origW / $origH;
        $targetRatio = 16 / 9;

        // Nếu ảnh đã đúng chuẩn 16:9 (sai lệch < 3%)
        if (abs($currentRatio - $targetRatio) < 0.03) {
            if ($origW === 1280 && $origH === 720) {
                imagedestroy($srcImage);
                return $binary;
            }
            // Giữ nguyên vẹn 100% khung hình banner 16:9 từ AI, không cắt xén biên
            $cropW = $origW;
            $cropH = $origH;
            $cropX = 0;
            $cropY = 0;
        } elseif ($currentRatio < $targetRatio) {
            // Nếu ảnh hẹp hơn 16:9 (ví dụ ảnh vuông 1:1 hoặc ảnh dọc):
            // Giữ nguyên chiều rộng $origW, cắt chiều cao ở giữa: $cropH = round($origW * 9 / 16)
            $cropW = $origW;
            $cropH = (int) round($origW * 9 / 16);
            $cropX = 0;
            $cropY = (int) max(0, round(($origH - $cropH) / 2));
        } else {
            // Nếu ảnh bè ngang hơn 16:9:
            // Giữ nguyên chiều cao $origH, cắt chiều rộng ở giữa: $cropW = round($origH * 16 / 9)
            $cropH = $origH;
            $cropW = (int) round($origH * 16 / 9);
            $cropX = (int) max(0, round(($origW - $cropW) / 2));
            $cropY = 0;
        }

        $targetW = 1280;
        $targetH = 720;
        $dstImage = imagecreatetruecolor($targetW, $targetH);
        if (! $dstImage) {
            imagedestroy($srcImage);
            return $binary;
        }

        // Giữ độ trong suốt nếu có
        imagealphablending($dstImage, false);
        imagesavealpha($dstImage, true);
        $transparent = imagecolorallocatealpha($dstImage, 255, 255, 255, 127);
        imagefilledrectangle($dstImage, 0, 0, $targetW, $targetH, $transparent);

        imagecopyresampled(
            $dstImage,
            $srcImage,
            0, 0,
            $cropX, $cropY,
            $targetW, $targetH,
            $cropW, $cropH
        );

        ob_start();
        imagepng($dstImage, null, 7);
        $result = ob_get_clean();

        imagedestroy($srcImage);
        imagedestroy($dstImage);

        return (! empty($result) && strlen($result) > 100) ? $result : $binary;
    }

    private function cropWithImagick16x9(string $binary): string
    {
        try {
            $im = new \Imagick();
            $im->readImageBlob($binary);
            $origW = $im->getImageWidth();
            $origH = $im->getImageHeight();
            if ($origW <= 0 || $origH <= 0) {
                $im->clear();
                $im->destroy();
                return $binary;
            }

            $targetRatio = 16 / 9;
            $currentRatio = $origW / $origH;
            if (abs($currentRatio - $targetRatio) < 0.03) {
                if ($origW === 1280 && $origH === 720) {
                    $im->clear();
                    $im->destroy();
                    return $binary;
                }
                $cropW = $origW;
                $cropH = $origH;
                $cropX = 0;
                $cropY = 0;
            } elseif ($currentRatio < $targetRatio) {
                $cropW = $origW;
                $cropH = (int) round($origW * 9 / 16);
                $cropX = 0;
                $cropY = (int) max(0, round(($origH - $cropH) / 2));
            } else {
                $cropH = $origH;
                $cropW = (int) round($origH * 16 / 9);
                $cropX = (int) max(0, round(($origW - $cropW) / 2));
                $cropY = 0;
            }

            $im->cropImage($cropW, $cropH, $cropX, $cropY);
            $im->setImagePage(0, 0, 0, 0);
            $im->resizeImage(1280, 720, \Imagick::FILTER_LANCZOS, 1);
            $im->setImageFormat('png');
            $result = $im->getImageBlob();
            $im->clear();
            $im->destroy();

            return (! empty($result) && strlen($result) > 100) ? $result : $binary;
        } catch (\Throwable $e) {
            Log::warning('GeminiService: Cắt ảnh 16:9 bằng Imagick thất bại: '.$e->getMessage());
            return $binary;
        }
    }

    private function findImageDataUrl(mixed $value): ?string
    {
        if (is_string($value)) {
            if (str_starts_with($value, 'data:image/')) {
                return $value;
            }
            // 9Router có thể bọc data URL trong Markdown: ![image](data:image/...;base64,...)
            if (preg_match('#(data:image/(?:png|jpeg|webp);base64,[A-Za-z0-9+/=]+)#', $value, $matches)) {
                return $matches[1];
            }
        }
        if (! is_array($value)) {
            return null;
        }
        foreach ($value as $item) {
            $found = $this->findImageDataUrl($item);
            if ($found) {
                return $found;
            }
        }

        return null;
    }

    /**
     * Tạo danh sách câu hỏi từ nhiều tệp (ảnh hoặc PDF) cùng lúc
     * Đọc chính xác nội dung chữ trong tệp, không bịa đặt nội dung không có.
     *
     * @param  array  $fileItems  Mảng các phần tử: ['path' => string, 'mime_type' => string, 'name' => string]
     * @param  string  $context  Ngữ cảnh bổ sung từ giáo viên
     * @return array Danh sách câu hỏi đã chuẩn hóa
     */
    public function generateQuestionsFromFiles(array $fileItems, string $context = '', bool $generateImages = false, int $questionCount = 5): array
    {
        $this->executionTrace = [];
        $questionCount = max(1, min(10, $questionCount));
        // Trích chữ từ PDF ngay trên máy chủ để AI luôn có nội dung thật, kể cả khi dịch vụ không đọc được PDF đính kèm.
        $extracted = $this->extractPdfText($fileItems);
        $filePrompt = $this->buildFilePrompt($context, $generateImages, $questionCount, $extracted['text']);
        // PDF đã có chữ thì chỉ gửi chữ cho API riêng (ổn định hơn đính kèm tệp); ảnh và PDF dạng quét vẫn đính kèm.
        $privateFiles = array_values(array_filter($fileItems, fn (array $file) => ! in_array($file['path'], $extracted['covered'], true)));
        // Chỉ tải PDF lên Google sau khi API riêng thất bại hoặc chưa được cấu hình.
        $questions = $this->tryPrivateApi($filePrompt, $privateFiles);
        // Mô hình đôi khi bỏ qua nội dung tệp và soạn câu chung chung; khi đó bỏ kết quả để chuyển sang Gemini.
        if ($questions !== null && $questions !== [] && $extracted['text'] !== '' && ! $this->isGroundedInSource($questions, $extracted['text'])) {
            $this->executionTrace[] = $this->traceRow('9Router', (string) config('services.question_ai.model'), '200', microtime(true), 'Không bám sát nội dung tệp, chuyển Gemini');
            Log::info('AI soạn đề: câu hỏi từ API riêng không bám nội dung tệp, chuyển sang Gemini.');
            $questions = null;
        }
        if ($questions !== null && $questions !== []) {
            $questions = $this->filterStandaloneQuestions($questions);
            if ($questions !== []) {
                return $questions;
            }
        }

        if ($questions === []) {
            Log::info('AI soạn đề: API riêng không đọc được tệp, chuyển sang bộ đọc tài liệu Gemini.');
        }

        $this->ensureGeminiConfigured();
        $parts = [];

        // Nạp tất cả tệp ảnh/PDF vào payload trước
        foreach ($fileItems as $file) {
            $path = $file['path'];
            $mimeType = $file['mime_type'] ?? (mime_content_type($path) ?: 'image/jpeg');

            if (str_contains($mimeType, 'pdf')) {
                $fileUri = $this->uploadFileToGemini($path, 'application/pdf');
                $parts[] = [
                    'fileData' => [
                        'mimeType' => 'application/pdf',
                        'fileUri' => $fileUri,
                    ],
                ];
            } else {
                $base64 = base64_encode(file_get_contents($path));
                $parts[] = [
                    'inlineData' => [
                        'mimeType' => $mimeType,
                        'data' => $base64,
                    ],
                ];
            }
        }

        // Đưa chỉ dẫn xử lý nghiêm ngặt vào cuối sau khi đã xem toàn bộ tệp
        $parts[] = ['text' => $filePrompt];

        $payload = [
            'contents' => [['parts' => $parts]],
            'generationConfig' => $this->generationConfig(),
        ];

        $questions = $this->normalizeRequestedQuestionType($this->callWithKeyRotation($payload), $filePrompt);

        return $this->filterStandaloneQuestions($questions);
    }

    /**
     * Nguồn phân tích không tự động đi theo câu hỏi đã lưu, vì vậy câu hỏi phải tự đủ dữ kiện.
     */
    private function filterStandaloneQuestions(array $questions): array
    {
        $dependentReferences = '/(?:trong|theo|dựa\s+vào)\s+(?:hình|ảnh|tệp|file|pdf|tài\s+liệu|bảng|biểu\s+đồ|sơ\s+đồ|thời\s+khóa\s+biểu)(?:\s+(?:này|trên|dưới))?|(?:hình|ảnh|tệp|tài\s+liệu|bảng|biểu\s+đồ|sơ\s+đồ)\s+(?:trên|dưới|đính\s+kèm)|ở\s+(?:trên|dưới)\s+(?:đây)?/ui';
        $hiddenScheduleData = '/(?:lớp\s*[0-9]+[a-z0-9]*).*(?:khi\s+nào|thời\s+gian\s+nào|buổi\s+nào|thứ\s+mấy|tiết\s+(?:nào|thứ\s+mấy)|giáo\s+viên\s+nào)/ui';

        return array_values(array_filter($questions, function (array $question) use ($dependentReferences, $hiddenScheduleData): bool {
            $title = trim((string) ($question['title'] ?? ''));
            $hasGeneratedIllustration = (bool) ($question['needs_image'] ?? false)
                && trim((string) ($question['image_prompt'] ?? '')) !== '';

            return ($hasGeneratedIllustration || ! preg_match($dependentReferences, $title))
                && ! preg_match($hiddenScheduleData, $title);
        }));
    }

    /**
     * Tạo danh sách câu hỏi từ một hình ảnh đơn lẻ
     */
    public function generateQuestionsFromImage(string $filePath, string $context = '', int $questionCount = 5): array
    {
        return $this->generateQuestionsFromFiles([[
            'path' => $filePath,
            'mime_type' => mime_content_type($filePath) ?: 'image/jpeg',
            'name' => basename($filePath),
        ]], $context, false, $questionCount);
    }

    /**
     * Tạo danh sách câu hỏi từ một file PDF
     */
    public function generateQuestionsFromPdf(string $filePath, string $context = '', int $questionCount = 5): array
    {
        return $this->generateQuestionsFromFiles([[
            'path' => $filePath,
            'mime_type' => 'application/pdf',
            'name' => basename($filePath),
        ]], $context, false, $questionCount);
    }

    /**
     * Tạo câu hỏi từ nội dung văn bản thuần (khi giáo viên nhập mô tả / đề cương)
     */
    public function generateQuestionsFromText(string $textContent, string $context = '', bool $generateImages = false, int $questionCount = 5): array
    {
        $this->executionTrace = [];
        $questionCount = max(1, min(10, $questionCount));
        $prompt = $this->buildTextPrompt($context, $generateImages, $questionCount)."\n\n---\nNỘI DUNG YÊU CẦU SOẠN BÀI:\n".$textContent;
        $questions = $this->tryPrivateApi($prompt);
        if ($questions !== null) {
            return $questions;
        }

        $this->ensureGeminiConfigured();

        $payload = [
            'contents' => [[
                'parts' => [
                    [
                        'text' => $prompt,
                    ],
                ],
            ]],
            'generationConfig' => $this->generationConfig(),
        ];

        return $this->normalizeRequestedQuestionType($this->callWithKeyRotation($payload), $prompt);
    }

    // =========================================================================
    // CÁC HÀM HỖ TRỢ NỘI BỘ
    // =========================================================================

    /**
     * Gọi API riêng theo định dạng Chat Completions; null yêu cầu dùng dịch vụ dự phòng.
     * Mảng rỗng hợp lệ có nghĩa tài liệu không có câu hỏi, không phải lỗi kết nối.
     */
    private function tryPrivateApi(string $prompt, array $fileItems = []): ?array
    {
        $baseUrl = rtrim(trim((string) config('services.question_ai.base_url')), '/');
        $model = trim((string) config('services.question_ai.model'));
        if ($baseUrl === '' || $model === '') {
            if ($baseUrl !== '' || $model !== '') {
                Log::warning('AI soạn đề: Cấu hình API riêng thiếu URL hoặc tên mô hình, sử dụng Gemini dự phòng.');
            }

            return null;
        }

        $content = [];
        foreach ($fileItems as $file) {
            $mimeType = $file['mime_type'] ?? (mime_content_type($file['path']) ?: 'image/jpeg');
            $dataUrl = 'data:'.$mimeType.';base64,'.base64_encode(file_get_contents($file['path']));
            $content[] = $mimeType === 'application/pdf'
                ? ['type' => 'file', 'file' => ['filename' => $file['name'] ?? basename($file['path']), 'file_data' => $dataUrl]]
                : ['type' => 'image_url', 'image_url' => ['url' => $dataUrl]];
        }
        $content[] = ['type' => 'text', 'text' => $prompt];

        $startedAt = microtime(true);
        try {
            $request = Http::acceptJson()
                ->connectTimeout(max(1, (int) config('services.question_ai.connect_timeout', 10)))
                ->timeout(max(1, (int) config('services.question_ai.timeout', 60)))
                ->withoutRedirecting();
            $apiKey = trim((string) config('services.question_ai.api_key'));
            if ($apiKey !== '') {
                $request = $request->withToken($apiKey);
            }

            $response = $request->post($baseUrl.'/chat/completions', [
                'model' => $model,
                'messages' => [['role' => 'user', 'content' => $content]],
                'stream' => false,
            ]);

            if (! $response->successful()) {
                $this->executionTrace[] = $this->traceRow('9Router', $model, 'HTTP '.$response->status(), $startedAt, 'Chuyển dự phòng');
                Log::warning('AI soạn đề: API riêng gặp lỗi, chuyển sang Gemini.', ['status' => $response->status()]);

                return null;
            }

            $rawText = $response->json('choices.0.message.content');
            if (! is_string($rawText) || trim($rawText) === '' || $response->json('choices.0.finish_reason') === 'length') {
                throw new \RuntimeException('API riêng trả về nội dung thiếu hoặc chưa hoàn tất.');
            }

            $questions = $this->normalizeRequestedQuestionType($this->parseQuestionsJson($rawText), $prompt);
            $this->executionTrace[] = $this->traceRow('9Router', $model, '200', $startedAt, $questions === [] ? 'Không đọc được nội dung' : 'Thành công');

            return $questions;
        } catch (ConnectionException|\RuntimeException $e) {
            $this->executionTrace[] = $this->traceRow('9Router', $model, 'Lỗi', $startedAt, 'Chuyển dự phòng');
            // Không ghi nội dung phản hồi hay thông tin xác thực vào nhật ký.
            Log::warning('AI soạn đề: API riêng không trả về câu hỏi hợp lệ, chuyển sang Gemini.', ['error_type' => $e::class]);

            return null;
        }
    }

    private function traceRow(string $provider, string $model, string $status, float $startedAt, string $result): array
    {
        return [
            'Nhà cung cấp' => $provider,
            'Model' => $model,
            'Trạng thái' => $status,
            'Kết quả' => $result,
            'Thời gian (ms)' => (int) round((microtime(true) - $startedAt) * 1000),
        ];
    }

    private function ensureGeminiConfigured(): void
    {
        if (empty($this->apiKeys)) {
            throw new \RuntimeException('Chưa thể soạn đề: API riêng chưa sẵn sàng và chưa cấu hình khóa Gemini dự phòng. Thầy/Cô vui lòng liên hệ quản trị viên.');
        }
    }

    /**
     * Chuẩn hóa cứng dạng ghép nối khi giáo viên đã yêu cầu rõ trong prompt.
     * Một số model đôi khi trả MultipleResponse dù nội dung đã là các cặp trái/phải.
     */
    private function normalizeRequestedQuestionType(array $questions, string $prompt): array
    {
        if (! preg_match('/ghép\s*nối|nối\s*cặp|nối\s*hai\s*cột|matching/ui', $prompt)) {
            return $questions;
        }

        return array_map(function (array $question): array {
            $normalized = array_map(function (array $option): array {
                $left = trim((string) ($option['left'] ?? ''));
                $right = trim((string) ($option['right'] ?? ''));
                $content = trim((string) ($option['content'] ?? ''));

                if (($left === '' || $right === '') && $content !== '') {
                    $parts = preg_split('/\s+[–—-]\s+/u', $content, 2);
                    if (count($parts) === 2) {
                        [$left, $right] = array_map('trim', $parts);
                    }
                }

                return [
                    'content' => $content !== '' ? $content : ($left.' – '.$right),
                    'is_correct' => true,
                    'position' => $option['position'] ?? 0,
                    'left' => $left,
                    'right' => $right,
                ];
            }, $question['options'] ?? []);

            // Chỉ ép sang ghép nối khi AI thực sự trả đủ hai vế; tránh hiển thị cột phải rỗng.
            $hasPairs = count($normalized) >= 2 && collect($normalized)->every(fn (array $option) => $option['left'] !== '' && $option['right'] !== '');
            if ($hasPairs) {
                $question['type'] = 'Matching';
                $question['options'] = $normalized;
            }

            return $question;
        }, $questions);
    }

    /**
     * Chỉ dẫn phân tích tệp tài liệu: CƠ CHẾ LINH HOẠT THÔNG MINH
     * - Ưu tiên 1: Thực hiện theo ghi chú yêu cầu của giáo viên (nếu có).
     * - Ưu tiên 2: Đọc chính xác dữ kiện thật trong tệp.
     * - Ưu tiên 3: Chuyển dữ kiện sang ngữ cảnh Tin học/kỹ năng số khi nguồn không có bài Tin học trực tiếp.
     * - Chỉ từ chối khi ảnh hoàn toàn vô nghĩa và không có gợi ý từ giáo viên.
     */
    private function buildFilePrompt(string $context = '', bool $generateImages = false, int $questionCount = 5, string $sourceText = ''): string
    {
        $questionCount = max(1, min(10, $questionCount));
        $sourceBlock = $sourceText !== '' ? "NỘI DUNG CHỮ ĐÃ TRÍCH TỪ TỆP (dùng làm nguồn chính để soạn câu hỏi):\n<<<\n{$sourceText}\n>>>\n\n" : '';
        $contextLine = $context ? "YÊU CẦU ĐẶC BIỆT TỪ GIÁO VIÊN: {$context}\n" : '';
        $imageInstructions = $generateImages
            ? <<<'IMAGE'
- Chế độ tạo ảnh đang BẬT. Chọn 1 đến 3 câu phù hợp nhất để có ảnh minh họa khi hình ảnh giúp học sinh quan sát rõ hơn.
- Khi `needs_image` là true: Tự động viết `image_prompt` bằng tiếng Anh mô tả bối cảnh hình ảnh 3D hoạt hình Pixar rực rỡ, dễ thương (Cute 3D gamified digital art, Pixar 3D feel, vibrant colors, smooth glossy clay surfaces, friendly for elementary kids, strictly NO text, NO letters, NO words, NO answer spoilers) bám sát đúng chủ thể/công cụ số của câu hỏi đó. Tuyệt đối không để ảnh làm lộ đáp án.
IMAGE
            : <<<'NO_IMAGE'
- Chế độ tạo ảnh đang TẮT. Không đề xuất ảnh minh họa; luôn đặt `needs_image: false` và `image_prompt: null`. Mọi câu hỏi phải đủ dữ kiện bằng chữ.
NO_IMAGE;

        return <<<PROMPT
{$contextLine}{$sourceBlock}Bạn là chuyên gia sư phạm tiểu học. Hãy đọc kỹ toàn bộ ảnh hoặc PDF được cung cấp rồi soạn câu hỏi BÁM SÁT NỘI DUNG THẬT TRONG TỆP.

NGUYÊN TẮC BẮT BUỘC (ưu tiên cao nhất):
- Mọi câu hỏi phải lấy từ nội dung có trong tệp. Tệp thuộc chủ đề nào thì soạn theo chủ đề đó (Tin học, Toán, Tiếng Việt, tiểu sử, hướng dẫn...). TUYỆT ĐỐI không ép về Tin học và không tự thêm kiến thức chung ngoài tệp.
- Nếu tệp đã có sẵn câu hỏi, bài tập hoặc đề kiểm tra (đặc biệt là nội dung Tin học/IC3): TRÍCH NGUYÊN VĂN câu hỏi, các phương án và đáp án đúng như trong tệp, không diễn đạt lại, không đổi nghĩa. Chỉ chuẩn hóa định dạng JSON.
- Nếu tệp là tài liệu, bài giảng hoặc văn bản thường: soạn câu hỏi kiểm tra hiểu biết từ các dữ kiện, khái niệm, số liệu, tên riêng thật sự xuất hiện trong tệp.
- Cố gắng soạn đúng {$questionCount} câu. Nếu nội dung tệp không đủ dữ kiện cho đủ số câu thì chỉ soạn số câu có căn cứ rõ ràng trong tệp, tuyệt đối KHÔNG bịa hoặc chèn câu hỏi chung chung cho đủ số lượng.
- Nếu không đọc được chữ trong tệp, trả về [] thay vì tự suy đoán.
- Luôn bám sát yêu cầu của giáo viên (nếu có) trong phạm vi nội dung tệp.
- Từng câu hỏi phải tự chứa đủ ngữ cảnh để học sinh trả lời mà không cần nhìn vào tệp gốc; không đổi sự thật so với tệp.

QUY TRÌNH SUY LUẬN:
1. Nhận diện loại tài liệu, mục đích, cấu trúc và các dữ kiện đọc chắc chắn.
2. Xác định ý định của giáo viên: số lượng câu ({$questionCount} câu), dạng câu, mức độ, phần cần tập trung.
3. Nếu tệp có sẵn câu hỏi: trích nguyên văn. Nếu không: chọn các dữ kiện quan trọng nhất trong tệp để hỏi.
4. Chọn dạng câu phù hợp: một đáp án, nhiều đáp án hoặc ghép nối. Không ép mọi câu về cùng một dạng nếu giáo viên không yêu cầu.
5. Tạo phương án nhiễu hợp lý, rõ nghĩa, không mơ hồ; đáp án đúng phải đối chiếu được với nội dung tệp.

RÀNG BUỘC CHẤT LƯỢNG:
- Ngôn ngữ tiếng Việt trong sáng, ngắn gọn, phù hợp học sinh tiểu học.
- Không lặp lại cùng một ý dưới nhiều cách hỏi.
- Mỗi câu hỏi phải tự đủ dữ kiện; riêng câu có `needs_image: true` thì dữ kiện trực quan phải nằm đầy đủ trong ảnh mới do AI tạo và được lưu kèm câu hỏi.
- Tuyệt đối không viết mơ hồ “theo hình trên”, “trong tài liệu này”, “ở bảng dưới đây”... Hãy đưa tình huống hoặc dữ kiện cụ thể vào ngay câu hỏi.
{$imageInstructions}
QUY CÁCH ĐỊNH DẠNG ĐẦU RA BẮT BUỘC:
- Trả về DUY NHẤT một chuỗi JSON array hợp lệ, tuyệt đối KHÔNG có markdown, KHÔNG kèm giải thích bên ngoài.
- Được phép chọn `MultipleChoice` (1 đáp án), `MultipleResponse` (nhiều đáp án) hoặc `Matching` (ghép nối) tùy nội dung.
- Với `Matching`, mỗi phương án phải có thêm `left`, `right` và `content` mô tả cặp ghép; `is_correct` là true.
- Nếu yêu cầu của giáo viên có các cụm “ghép nối”, “nối cặp”, “matching” hoặc “nối hai cột”, bắt buộc dùng `type: "Matching"`; tuyệt đối không dùng `MultipleResponse`.
- Với câu `Matching`, không đánh dấu các lựa chọn A/B/C/D; hãy tạo các cặp `left`–`right` riêng biệt để giao diện hiển thị thành hai cột.
- Cấu trúc:
  [
    {
      "title": "Nội dung câu hỏi?",
      "type": "MultipleChoice",
      "needs_image": false,
      "image_prompt": null,
      "options": [
        {"content": "Phương án 1", "is_correct": false},
        {"content": "Phương án 2", "is_correct": true},
        {"content": "Phương án 3", "is_correct": false},
        {"content": "Phương án 4", "is_correct": false}
      ]
    }
  ]
- `MultipleChoice` phải có đúng 1 đáp án đúng; `MultipleResponse` phải có ít nhất 2 đáp án đúng.
- Hoặc trả về [] nếu không thể khai thác được nội dung từ ảnh.
PROMPT;
    }

    /**
     * Kiểm tra câu hỏi có thật sự lấy từ nội dung tệp: ít nhất một nửa số câu có phần lớn từ khóa nằm trong chữ trích từ tệp.
     *
     * @param  array<int, array<string, mixed>>  $questions
     */
    private function isGroundedInSource(array $questions, string $sourceText): bool
    {
        $source = mb_strtolower($sourceText, 'UTF-8');
        $grounded = 0;

        foreach ($questions as $question) {
            $words = preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower((string) ($question['title'] ?? ''), 'UTF-8'), -1, PREG_SPLIT_NO_EMPTY) ?: [];
            $words = array_values(array_unique(array_filter($words, fn (string $w) => mb_strlen($w) >= 4)));
            if ($words === []) {
                continue;
            }

            $found = count(array_filter($words, fn (string $w) => str_contains($source, $w)));
            if ($found / count($words) >= 0.4) {
                $grounded++;
            }
        }

        return $grounded * 2 >= count($questions);
    }
    /**
     * Trích văn bản từ các tệp PDF (có chữ) để làm nguồn chính cho AI. PDF dạng ảnh quét sẽ không có chữ nên bỏ qua.
     *
     * @return array{text: string, covered: array<int, string>} text là chữ đã trích, covered là đường dẫn các PDF đã trích được chữ
     */
    private function extractPdfText(array $fileItems): array
    {
        $chunks = [];
        $covered = [];
        $remaining = 40000;

        foreach ($fileItems as $file) {
            if ($remaining <= 0 || ! str_contains((string) ($file['mime_type'] ?? ''), 'pdf')) {
                continue;
            }

            try {
                $text = (new \Smalot\PdfParser\Parser())->parseFile($file['path'])->getText();
            } catch (\Throwable $e) {
                Log::info('AI soạn đề: không trích được chữ từ PDF, dùng bản đính kèm.', ['error_type' => $e::class]);

                continue;
            }

            $text = trim((string) preg_replace('/[ \t]+/u', ' ', (string) preg_replace('/\R{3,}/u', "\n\n", (string) $text)));
            if (mb_strlen($text) < 80) {
                continue;
            }

            $text = mb_substr($text, 0, $remaining);
            $remaining -= mb_strlen($text);
            $chunks[] = '[Tệp: '.($file['name'] ?? basename($file['path']))."]\n".$text;
            $covered[] = $file['path'];
        }

        return ['text' => implode("\n\n", $chunks), 'covered' => $covered];
    }

    /**
     * Chỉ dẫn biên soạn câu hỏi khi giáo viên nhập văn bản đề cương
     */
    private function buildTextPrompt(string $context = '', bool $generateImages = false, int $questionCount = 5): string
    {
        $questionCount = max(1, min(10, $questionCount));
        $contextLine = $context ? "Ghi chú từ giáo viên: {$context}\n" : '';
        $imageInstructions = $generateImages
            ? '- Chế độ tạo ảnh đang BẬT: chọn 1 đến 3 câu phù hợp để có ảnh minh họa khi hình ảnh giúp học sinh quan sát rõ hơn. Với câu có `needs_image: true`, tự động viết `image_prompt` bằng tiếng Anh mô tả bối cảnh 3D hoạt hình Pixar rực rỡ, dễ thương (Cute 3D gamified digital art, Pixar 3D feel, vibrant colors, smooth glossy clay surfaces, friendly for elementary kids, strictly NO text, NO letters, NO words, NO answer spoilers) bám sát đúng chủ thể/công cụ số của câu hỏi đó; tuyệt đối không để ảnh lộ đáp án.'
            : '- Chế độ tạo ảnh đang TẮT: luôn đặt `needs_image: false`, `image_prompt: null` và viết câu hỏi đủ dữ kiện bằng chữ.';

        return <<<PROMPT
{$contextLine}Bạn là chuyên gia sư phạm tin học tiểu học, chuyên soạn câu hỏi và bài luyện tập chuẩn IC3 GS6 (IC3 Spark) cho học sinh lớp 3, lớp 4, lớp 5.

PHẠM VI BẮT BUỘC: Chỉ soạn nội dung thuộc môn Tin học/IC3. Không chuyển yêu cầu sang môn học hoặc kiến thức ngoài Tin học.

Dựa trên yêu cầu và nội dung đề cương được cung cấp:
- Tạo đúng {$questionCount} câu hỏi nếu nội dung đủ dữ kiện; nếu không đủ thì tạo số câu ít hơn và không bịa thêm.
- Nếu giáo viên dán sẵn danh sách câu hỏi/đáp án, hãy ưu tiên chuyển đổi và chuẩn hóa đúng các câu đó sang JSON; không tự thay nội dung khi câu hỏi đã rõ.
- Hãy biên soạn các câu hỏi trắc nghiệm hay, bám sát kiến thức tin học tiểu học, ngôn từ trong sáng, dễ hiểu.
- Mỗi câu hỏi gồm đủ phương án phù hợp, xác định chính xác đáp án đúng.
- Được phép chọn `MultipleChoice`, `MultipleResponse` hoặc `Matching` tùy nội dung; với `Matching`, dùng `left`, `right`, `content`, `is_correct: true` cho từng cặp.
- Nếu yêu cầu có “ghép nối”, “nối cặp”, “matching” hoặc “nối hai cột”, bắt buộc trả `type: "Matching"` và các cặp `left`–`right`, không trả `MultipleResponse`.
{$imageInstructions}
- Trả về DUY NHẤT một chuỗi JSON array hợp lệ (không kèm lời dẫn):
  [
    {
      "title": "Nội dung câu hỏi?",
      "type": "MultipleChoice",
      "needs_image": false,
      "image_prompt": null,
      "options": [
        {"content": "Phương án A", "is_correct": false},
        {"content": "Phương án B", "is_correct": true},
        {"content": "Phương án C", "is_correct": false},
        {"content": "Phương án D", "is_correct": false}
      ]
    }
  ]
PROMPT;
    }

    /**
     * Cấu hình generation tối ưu độ chính xác và định dạng JSON
     */
    private function generationConfig(): array
    {
        return [
            'temperature' => 0.1,
            'topP' => 0.85,
            'responseMimeType' => 'application/json',
        ];
    }

    /**
     * Tải file lên Gemini File API để phân tích tài liệu PDF
     */
    private function uploadFileToGemini(string $filePath, string $mimeType): string
    {
        $key = $this->apiKeys[0];
        $fileName = basename($filePath);
        $fileSize = filesize($filePath);

        $initResponse = Http::connectTimeout(10)->timeout(90)->withHeaders([
            'x-goog-api-key' => $key,
            'X-Goog-Upload-Protocol' => 'resumable',
            'X-Goog-Upload-Command' => 'start',
            'X-Goog-Upload-Header-Content-Length' => $fileSize,
            'X-Goog-Upload-Header-Content-Type' => $mimeType,
            'Content-Type' => 'application/json',
        ])->post('https://generativelanguage.googleapis.com/upload/v1beta/files', [
            'file' => ['display_name' => $fileName],
        ]);

        $uploadUrl = $initResponse->header('X-Goog-Upload-URL');
        if (! $uploadUrl) {
            throw new \RuntimeException('Chưa thể chuẩn bị tệp tài liệu để phân tích. Thầy/Cô vui lòng thử lại.');
        }

        $uploadResponse = Http::connectTimeout(10)->timeout(90)->withHeaders([
            'Content-Length' => $fileSize,
            'X-Goog-Upload-Offset' => 0,
            'X-Goog-Upload-Command' => 'upload, finalize',
        ])->withBody(file_get_contents($filePath), $mimeType)->post($uploadUrl);

        $fileData = $uploadResponse->json();
        $fileUri = $fileData['file']['uri'] ?? null;

        if (! $fileUri) {
            throw new \RuntimeException('Không thể nạp tệp tài liệu lên hệ thống AI. Thầy/Cô vui lòng thử lại.');
        }

        return $fileUri;
    }

    /**
     * Gọi Gemini API xoay vòng mô hình và API key
     */
    private function callWithKeyRotation(array $payload): array
    {
        foreach ($this->candidateModels as $model) {
            foreach ($this->apiKeys as $index => $key) {
                $startedAt = microtime(true);
                try {
                    $response = Http::connectTimeout(10)->timeout(90)
                        ->withHeaders(['x-goog-api-key' => $key])
                        ->post("{$this->baseUrl}/models/{$model}:generateContent", $payload);

                    if ($response->status() === 429) {
                        $this->executionTrace[] = $this->traceRow('Google Gemini', $model, 'HTTP 429', $startedAt, 'Thử khóa/model tiếp theo');
                        Log::warning("GeminiService: Khóa #{$index} chạm giới hạn tần suất, chuyển khóa tiếp theo.");

                        continue;
                    }

                    if (! $response->successful()) {
                        $this->executionTrace[] = $this->traceRow('Google Gemini', $model, 'HTTP '.$response->status(), $startedAt, 'Thử model tiếp theo');
                        Log::error("GeminiService: Lỗi kết nối mô hình {$model} với khóa #{$index}", [
                            'status' => $response->status(),
                        ]);

                        if ($response->status() === 404) {
                            break;
                        }

                        continue;
                    }

                    $data = $response->json();

                    // Bóc tách toàn bộ nội dung văn bản từ các khối (parts)
                    $rawText = '';
                    foreach ($data['candidates'][0]['content']['parts'] ?? [] as $part) {
                        if (! empty($part['text'])) {
                            $rawText .= $part['text'];
                        }
                    }

                    if (empty($rawText)) {
                        continue;
                    }

                    $questions = $this->parseQuestionsJson($rawText);
                    $this->executionTrace[] = $this->traceRow('Google Gemini', $model, '200', $startedAt, 'Thành công');

                    return $questions;

                } catch (ConnectionException|\RuntimeException $e) {
                    $this->executionTrace[] = $this->traceRow('Google Gemini', $model, 'Lỗi', $startedAt, 'Thử khóa/model tiếp theo');
                    Log::warning("GeminiService: Không nhận được câu hỏi hợp lệ với khóa #{$index}.", ['error_type' => $e::class]);

                    continue;
                }
            }
        }

        throw new \RuntimeException('Hệ thống AI hiện đang bận hoặc gặp sự cố kết nối. Thầy/Cô vui lòng thử lại sau giây lát nhé!');
    }

    /**
     * Chuyển đổi chuỗi JSON từ AI thành danh sách câu hỏi hợp lệ
     */
    private function parseQuestionsJson(string $rawText): array
    {
        $cleaned = trim($rawText);
        $cleaned = preg_replace('/^```(?:json)?\s*/i', '', $cleaned);
        $cleaned = preg_replace('/\s*```$/', '', $cleaned);
        $cleaned = trim($cleaned);

        $questions = json_decode($cleaned, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($questions) || ! array_is_list($questions) || ! str_starts_with($cleaned, '[')) {
            throw new \RuntimeException('AI trả về danh sách câu hỏi không đúng định dạng.');
        }

        return array_map(function ($q) {
            if (! is_array($q) || ! is_string($q['title'] ?? null) || trim($q['title']) === ''
                || ! is_array($q['options'] ?? null) || ! array_is_list($q['options']) || count($q['options']) < 2
                || ! in_array($q['type'] ?? 'MultipleChoice', ['MultipleChoice', 'MultipleResponse', 'Matching'], true)) {
                throw new \RuntimeException('AI trả về câu hỏi thiếu nội dung hoặc phương án trả lời.');
            }

            foreach ($q['options'] as $option) {
                if (($q['type'] ?? 'MultipleChoice') === 'Matching'
                    && (! is_string($option['left'] ?? null) || trim($option['left']) === ''
                        || ! is_string($option['right'] ?? null) || trim($option['right']) === '')) {
                    throw new \RuntimeException('AI trả về cặp ghép nối không hợp lệ.');
                }
                if (! is_array($option) || ! is_string($option['content'] ?? null) || trim($option['content']) === ''
                    || ! is_bool($option['is_correct'] ?? null)) {
                    throw new \RuntimeException('AI trả về phương án trả lời không hợp lệ.');
                }
            }

            $correctCount = count(array_filter($q['options'], fn ($option) => $option['is_correct']));
            if ($correctCount === 0 || (($q['type'] ?? 'MultipleChoice') === 'MultipleChoice' && $correctCount !== 1)
                || (($q['type'] ?? 'MultipleChoice') === 'MultipleResponse' && $correctCount < 2)) {
                throw new \RuntimeException('AI chưa xác định đúng đáp án của câu hỏi.');
            }

            return [
                'title' => trim($q['title']),
                'type' => $q['type'] ?? 'MultipleChoice',
                'needs_image' => (bool) ($q['needs_image'] ?? false),
                'image_prompt' => isset($q['image_prompt']) && is_string($q['image_prompt'])
                    ? trim($q['image_prompt'])
                    : null,
                'options' => array_values(array_map(fn ($opt, $i) => [
                    'content' => trim($opt['content'] ?? ''),
                    'is_correct' => (bool) ($opt['is_correct'] ?? false),
                    'position' => $i,
                    'left' => isset($opt['left']) ? trim($opt['left']) : null,
                    'right' => isset($opt['right']) ? trim($opt['right']) : null,
                ], $q['options'], array_keys($q['options']))),
            ];
        }, $questions);
    }
}
