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
                $question['illustration_path'] = $this->generateIllustration($imagePrompt, (string) ($question['title'] ?? ''));
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

        return trim("Minh họa trực quan cho câu hỏi Tin học/IC3: {$title}. "
            .'Ảnh nên thể hiện đúng tình huống hoặc thao tác số trong câu hỏi, rõ ràng, dễ quan sát, không ghi đáp án, không làm nổi bật phương án đúng.');
    }

    private function generateIllustration(string $description, string $questionTitle): ?string
    {
        $baseUrl = rtrim(trim((string) config('services.question_ai.base_url')), '/');
        $model = trim((string) config('services.question_ai.image_model'));
        $prompt = "Tạo một ảnh minh họa giáo dục rõ ràng, thân thiện với học sinh tiểu học. "
            ."Bắt buộc xuất ảnh ngang đúng kích thước 1280x720 pixel, tỉ lệ 16:9, bố cục landscape. "
            ."Không tạo ảnh dọc, không tạo ảnh vuông, không đặt ảnh dọc vào giữa nền mờ, không letterbox/pillarbox. "
            ."Ảnh phải cung cấp dữ kiện trực quan cần thiết để trả lời câu hỏi, không ghi đáp án, không chèn chữ thừa. "
            ."Câu hỏi: {$questionTitle}. Nội dung ảnh: {$description}";

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
                        'generationConfig' => ['responseModalities' => ['TEXT', 'IMAGE']],
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

        $binary = $this->normalizeImageToLandscape($binary) ?? $binary;
        $path = 'question-assets/ai-'.Str::uuid().'.jpg';
        Storage::disk('public')->put($path, $binary);

        return Storage::url($path);
    }

    private function normalizeImageToLandscape(string $binary): ?string
    {
        $source = @imagecreatefromstring($binary);
        if (! $source) {
            return null;
        }

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        if ($sourceWidth <= 0 || $sourceHeight <= 0) {
            imagedestroy($source);
            return null;
        }

        $targetWidth = 1280;
        $targetHeight = 720;
        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);

        $coverScale = max($targetWidth / $sourceWidth, $targetHeight / $sourceHeight);
        $coverWidth = (int) ceil($sourceWidth * $coverScale);
        $coverHeight = (int) ceil($sourceHeight * $coverScale);
        $coverX = (int) floor(($targetWidth - $coverWidth) / 2);
        $coverY = (int) floor(($targetHeight - $coverHeight) / 2);
        imagecopyresampled($canvas, $source, $coverX, $coverY, 0, 0, $coverWidth, $coverHeight, $sourceWidth, $sourceHeight);

        ob_start();
        imagejpeg($canvas, null, 88);
        $normalized = ob_get_clean();

        imagedestroy($source);
        imagedestroy($canvas);

        return is_string($normalized) && $normalized !== '' ? $normalized : null;
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
        // Chỉ tải PDF lên Google sau khi API riêng thất bại hoặc chưa được cấu hình.
        $questions = $this->tryPrivateApi($this->buildFilePrompt($context, $generateImages, $questionCount), $fileItems);
        if ($questions !== null && $questions !== []) {
            $questions = $this->filterStandaloneQuestions($this->filterComputerScienceQuestions($questions));
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
        $parts[] = ['text' => $this->buildFilePrompt($context, $generateImages, $questionCount)];

        $payload = [
            'contents' => [['parts' => $parts]],
            'generationConfig' => $this->generationConfig(),
        ];

        $questions = $this->normalizeRequestedQuestionType($this->callWithKeyRotation($payload), $this->buildFilePrompt($context, $generateImages, $questionCount));

        return $this->filterStandaloneQuestions($this->filterComputerScienceQuestions($questions));
    }

    /**
     * Chặn câu hỏi lệch khỏi Tin học/IC3 ngay cả khi mô hình không tuân thủ prompt.
     */
    private function filterComputerScienceQuestions(array $questions): array
    {
        $keywords = [
            'tin học', 'máy tính', 'phần mềm', 'phần cứng', 'internet', 'mạng máy tính',
            'kỹ năng số', 'an toàn số', 'thiết bị số', 'dữ liệu', 'tệp', 'thư mục',
            'bàn phím', 'chuột', 'màn hình', 'cpu', 'bộ nhớ', 'máy in', 'trình duyệt',
            'email', 'mật khẩu', 'địa chỉ ip', 'word', 'excel', 'powerpoint', 'paint',
            'lập trình', 'thuật toán', 'robot', 'ic3',
        ];

        return array_values(array_filter($questions, function (array $question) use ($keywords): bool {
            $parts = [$question['title'] ?? ''];
            foreach ($question['options'] ?? [] as $option) {
                $parts[] = $option['content'] ?? '';
                $parts[] = $option['left'] ?? '';
                $parts[] = $option['right'] ?? '';
            }
            $haystack = mb_strtolower(implode(' ', $parts), 'UTF-8');

            return collect($keywords)->contains(fn (string $keyword): bool => str_contains($haystack, $keyword));
        }));
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
    private function buildFilePrompt(string $context = '', bool $generateImages = false, int $questionCount = 5): string
    {
        $questionCount = max(1, min(10, $questionCount));
        $contextLine = $context ? "YÊU CẦU ĐẶC BIỆT TỪ GIÁO VIÊN: {$context}\n" : '';
        $imageInstructions = $generateImages
            ? <<<'IMAGE'
- Chế độ tạo ảnh đang BẬT. Chỉ đặt `needs_image: true` khi học sinh thực sự cần quan sát biểu tượng, vị trí, bố cục, hình dạng hoặc thao tác trực quan mới trả lời được.
- Khi `needs_image` là true, viết `image_prompt` mô tả chính xác ảnh cần tạo; ảnh không được tiết lộ đáp án. Nội dung câu hỏi phải nhắc học sinh quan sát hình minh họa.
- Nếu tệp nguồn là ảnh hoặc câu hỏi dùng dữ kiện trực quan, hãy chọn 1 đến 3 câu phù hợp nhất để có ảnh minh họa.
IMAGE
            : <<<'NO_IMAGE'
- Chế độ tạo ảnh đang TẮT. Không đề xuất ảnh minh họa; luôn đặt `needs_image: false` và `image_prompt: null`. Mọi câu hỏi phải đủ dữ kiện bằng chữ.
NO_IMAGE;

        return <<<PROMPT
{$contextLine}Bạn là chuyên gia sư phạm Tin học/IC3 tiểu học. Hãy tự phân tích thông minh mọi ảnh hoặc PDF được cung cấp và tạo câu hỏi phù hợp.

MỤC TIÊU BẮT BUỘC:
- Tạo đúng {$questionCount} câu hỏi nếu nguồn có đủ dữ kiện. Nếu nguồn không đủ, tạo ít hơn nhưng không bịa thêm.
- Mọi câu hỏi đầu ra phải thuộc Tin học/IC3 hoặc kỹ năng số.
- Luôn bám sát yêu cầu của giáo viên và nội dung thật trong tệp; nếu có cả hai thì phải kết hợp cả hai.
- Không bịa dữ kiện cụ thể không nhìn thấy hoặc không đọc được từ nguồn.
- Mỗi câu hỏi phải thể hiện rõ ít nhất một chi tiết cụ thể lấy từ nguồn: đối tượng, hành động, chữ, số liệu, bố cục hoặc tình huống nhìn thấy. Không được chỉ dùng nguồn làm cảm hứng rồi tạo câu hỏi chung chung không còn liên quan.

QUY TRÌNH SUY LUẬN:
1. Nhận diện loại tài liệu, mục đích, cấu trúc và các dữ kiện có thể đọc chắc chắn.
2. Xác định ý định của giáo viên: số lượng câu, dạng câu, mức độ, chủ đề và phần cần tập trung. Yêu cầu rõ ràng của giáo viên được ưu tiên, nhưng không được trái dữ kiện trong tệp.
3. Nếu nguồn đã có nội dung Tin học hoặc câu hỏi Tin học, trích xuất và biên soạn sát nội dung đó.
4. Nếu nguồn không nói trực tiếp về Tin học, giữ nguyên đối tượng/tình huống thật của nguồn rồi đặt nó trong một nhiệm vụ số tự nhiên. Nội dung câu hỏi phải mô tả lại đủ chi tiết nguồn để học sinh hiểu mà không cần xem ảnh/PDF. Có thể hỏi cách lưu, đặt tên, tìm kiếm, cắt/chỉnh sửa, sắp xếp hoặc chia sẻ chính nội dung đó.
5. Chọn dạng câu phù hợp nhất với nội dung: một đáp án, nhiều đáp án hoặc ghép nối. Không ép mọi câu về cùng một dạng nếu giáo viên không yêu cầu.
6. Tạo phương án nhiễu hợp lý, rõ nghĩa, không mơ hồ; đáp án đúng phải kiểm chứng được từ nguồn hoặc từ kiến thức Tin học phổ thông chắc chắn.
7. Bỏ qua phần chữ mờ, thiếu hoặc không chắc chắn. Chỉ trả về [] khi không thể đọc được dữ liệu hữu ích và yêu cầu chữ cũng không đủ để soạn câu.

KIỂM TRA BÁM NGUỒN TRƯỚC KHI TRẢ KẾT QUẢ — LOẠI BỎ CÂU NẾU CÓ MỘT TRONG CÁC LỖI SAU:
- Câu hỏi có thể được tạo y hệt dù không hề xem tệp nguồn.
- Câu hỏi chỉ nói chung về máy tính, sức khỏe, mật khẩu, Internet hoặc an toàn số nhưng không sử dụng chi tiết cụ thể nào từ nguồn.
- Câu hỏi thêm người, đồ vật, hành động, phần mềm hoặc hoàn cảnh không xuất hiện trong tệp và cũng không được giáo viên yêu cầu.
- Câu hỏi chỉ bám prompt chữ nhưng bỏ qua tệp, hoặc chỉ bám tệp nhưng bỏ qua một yêu cầu rõ ràng trong prompt chữ.

RÀNG BUỘC CHẤT LƯỢNG:
- Ngôn ngữ tiếng Việt trong sáng, ngắn gọn, phù hợp học sinh tiểu học.
- Không tạo câu hỏi kiến thức đời sống thuần túy nếu không có thao tác Tin học/kỹ năng số.
- Không dùng tên phần mềm, thiết bị hoặc tính năng không liên quan chỉ để làm câu hỏi có vẻ thuộc Tin học.
- Không lặp lại cùng một ý dưới nhiều cách hỏi.
- Mỗi câu hỏi phải tự đủ dữ kiện; riêng câu có `needs_image: true` thì dữ kiện trực quan phải nằm đầy đủ trong ảnh mới do AI tạo và được lưu kèm câu hỏi.
- Không viết “theo hình trên”, “trong tài liệu này”, “dựa vào thời khóa biểu”, “ở bảng dưới đây” hoặc hỏi một dữ kiện chỉ tồn tại trong tệp nhưng không được nêu trong câu hỏi.
- Nếu muốn dùng dữ liệu nguồn, phải đưa đầy đủ phần dữ liệu cần thiết vào ngay nội dung câu hỏi; nếu không thể trình bày ngắn gọn và rõ ràng thì bỏ câu đó.
- Với ảnh minh họa, hãy mô tả ngắn gọn đúng đối tượng/hành động nhìn thấy ngay trong câu hỏi rồi hỏi thao tác số trên chính nội dung đó. Không chuyển sang một chủ đề Tin học chung khác.
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
     * Chỉ dẫn biên soạn câu hỏi khi giáo viên nhập văn bản đề cương
     */
    private function buildTextPrompt(string $context = '', bool $generateImages = false, int $questionCount = 5): string
    {
        $questionCount = max(1, min(10, $questionCount));
        $contextLine = $context ? "Ghi chú từ giáo viên: {$context}\n" : '';
        $imageInstructions = $generateImages
            ? '- Chế độ tạo ảnh đang BẬT: chọn 1 đến 3 câu phù hợp để có ảnh minh họa khi hình ảnh giúp học sinh quan sát rõ hơn; viết `image_prompt` cụ thể và không để ảnh lộ đáp án.'
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
