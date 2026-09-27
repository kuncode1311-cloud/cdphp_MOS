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
        $optionsContext = '';
        if (! empty($question['options']) && is_array($question['options'])) {
            $texts = [];
            foreach (array_slice($question['options'], 0, 4) as $opt) {
                $text = trim((string) ($opt['content'] ?? $opt['left'] ?? ''));
                if ($text !== '') {
                    $texts[] = $text;
                }
            }
            if ($texts !== []) {
                $optionsContext = ' Bối cảnh thao tác và khái niệm liên quan: '.implode(', ', $texts).'.';
            }
        }

        return trim("Minh họa trực quan sinh động cho câu hỏi Tin học/IC3: {$title}.{$optionsContext} "
            .'Ảnh nên thể hiện đúng tình huống thực tế hoặc thao tác số trong câu hỏi, rõ ràng, dễ quan sát, tuyệt đối không ghi đáp án, không làm nổi bật phương án đúng.');
    }

    /**
     * Tối ưu hóa prompt tạo ảnh chuyên sâu chuẩn kiến thức Tin học & IC3 Spark tiểu học.
     * Chuyển hóa sang tiếng Anh chuyên ngành UI/Hardware để mô hình tạo ảnh sinh ra hình ảnh sắc nét,
     * đúng giao diện phần mềm thực tế, không vẽ hoạt hình mầm non linh tinh.
     */
    public function buildOptimizedImagePrompt(string $description, string $questionTitle, array $question = []): string
    {
        $combinedText = mb_strtolower($questionTitle.' '.$description);
        $options = $question['options'] ?? [];
        foreach ($options as $opt) {
            $combinedText .= ' '.mb_strtolower(($opt['content'] ?? '').' '.($opt['left'] ?? '').' '.($opt['right'] ?? ''));
        }

        // 1. Soạn thảo văn bản (Word / Thẻ Ribbon: Insert, Home, Layout, Chèn ảnh, Bảng...)
        if (preg_match('/soạn\s*thảo|văn\s*bản|word|thẻ\s*insert|thẻ\s*home|thẻ\s*file|chèn\s*ảnh|chèn\s*hình|chèn\s*bảng|đổi\s*màu\s*chữ|cỡ\s*chữ|font\s*chữ|căn\s*lề|kiểu\s*chữ/ui', $combinedText)) {
            return 'A realistic, clean computer screenshot of a modern word processing software interface (Microsoft Word style) in wide 16:9 landscape aspect ratio. '
                .'The top displays a sharp Ribbon toolbar with clearly labeled tabs: [File] [Home] [Insert] [Draw] [Design] [Layout] [References] [Review] [View]. '
                .'The ribbon displays clean toolbar icons including Pictures, Shapes, Table, and Font formatting tools. '
                .'Below the ribbon is a clean document editing page with a typing cursor on clean white paper. '
                .'Authentic digital literacy educational graphic for elementary students, wide 16:9 widescreen layout, modern flat software UI, sharp text, no cartoon characters, no animal mascots, no gibberish text, no blurry borders.';
        }

        // 2. Trình chiếu (PowerPoint / Slides / Hiệu ứng)
        if (preg_match('/trình\s*chiếu|powerpoint|slide|trang\s*chiếu|bài\s*trình\s*chiếu|transitions|animations/ui', $combinedText)) {
            return 'A realistic, clean computer screenshot of presentation software (Microsoft PowerPoint style) in wide 16:9 landscape aspect ratio. '
                .'Displays the top Ribbon toolbar with tabs [File] [Home] [Insert] [Draw] [Design] [Transitions] [Animations] [Slide Show], '
                .'a left slide thumbnail column, and a central widescreen presentation slide with editable title and subtitle placeholders. '
                .'Sharp modern computer software interface, educational computer science graphic, wide 16:9 widescreen layout, no cartoon characters, no blurry borders.';
        }

        // 3. Bảng tính điện tử (Excel / Ô tính / Hàng / Cột)
        if (preg_match('/bảng\s*tính|excel|hàng|cột|ô\s*tính|công\s*thức|spreadsheet/ui', $combinedText)) {
            return 'A realistic, clean computer screenshot of a spreadsheet software (Microsoft Excel style) in wide 16:9 landscape aspect ratio. '
                .'Displays the top Ribbon menu with [File] [Home] [Insert] [Page Layout] [Formulas] [Data], the Formula Bar (fx), '
                .'and a neat grid with column letters (A, B, C, D, E) and row numbers (1, 2, 3, 4, 5) with sample table data. '
                .'Crisp modern flat UI, educational computer science graphic, wide 16:9 landscape layout, no cartoon characters, no blurry borders.';
        }

        // 4. Vẽ & Đồ họa (MS Paint / Hộp màu / Công cụ vẽ)
        if (preg_match('/paint|vẽ\s*hình|tô\s*màu|bút\s*chì|công\s*cụ\s*vẽ|tẩy|hộp\s*màu/ui', $combinedText)) {
            return 'A realistic, clean computer screenshot of a digital drawing application (MS Paint style) in wide 16:9 landscape aspect ratio. '
                .'Shows a top tool palette with Pencil, Eraser, Fill bucket, Shapes palette (Rectangle, Circle, Star), Color Palette, '
                .'and a clean white drawing canvas. '
                .'Crisp modern software UI for elementary computer class, wide 16:9 widescreen layout, no blurry borders.';
        }

        // 5. Quản lý tệp và thư mục (File Explorer / Folders / Tệp tin)
        if (preg_match('/thư\s*mục|tệp\s*tin|tệp|folder|file\s*explorer|đổi\s*tên\s*tệp|sao\s*chép\s*tệp|phần\s*mở\s*rộng|\.docx|\.xlsx|\.png|\.pdf/ui', $combinedText)) {
            return 'A realistic, clean computer screenshot of Windows File Explorer in wide 16:9 landscape aspect ratio. '
                .'Displays folder tree navigation on the left, and organized folders (Documents, School, Pictures) and clear file icons with recognizable extensions (.docx, .png, .pdf) on the right, with a top toolbar showing New Folder and Organize buttons. '
                .'Crisp modern operating system interface, educational computer literacy graphic, wide 16:9 horizontal layout, no blurry borders.';
        }

        // 6. Căn cước công dân gắn chip / Dữ liệu số / Chip điện tử
        if (preg_match('/căn\s*cước|cccd|chip\s*điện\s*tử|chip|thẻ\s*thông\s*minh|smart\s*card/ui', $combinedText)) {
            return 'A realistic, high-resolution educational product photograph of a modern electronic citizen identity smart card in wide 16:9 landscape aspect ratio. '
                .'The card clearly features a prominent yellow metallic embedded microchip on the front, resting neatly on a modern computer desk next to a sleek laptop and digital tablet. '
                .'Bright studio lighting, sharp focus, clean technology illustration for elementary digital literacy, wide 16:9 widescreen layout, no blurry borders.';
        }

        // 7. Bàn phím máy tính (Keys / Enter / Spacebar)
        if (preg_match('/bàn\s*phím|keyboard|phím\s*enter|phím\s*space|phím\s*cách|phím\s*backspace|phím\s*shift|gõ\s*phím/ui', $combinedText)) {
            return 'A clear, top-down educational photograph of a modern desktop computer keyboard in wide 16:9 landscape aspect ratio. '
                .'Displays clean alphanumeric keys, Spacebar, Enter, Backspace, and arrow keys with bright soft studio lighting on a classroom desk. '
                .'Crisp details, educational computer hardware photo, wide 16:9 horizontal layout, no blurry borders.';
        }

        // 8. Chuột máy tính (Mouse / Nút chuột / Con lăn)
        if (preg_match('/chuột\s*máy\s*tính|chuột|mouse|nút\s*cuộn|nhấp\s*chuột|con\s*trỏ\s*chuột/ui', $combinedText)) {
            return 'A clear, professional educational photograph of a modern optical computer mouse resting on a mousepad on a clean desk in wide 16:9 landscape aspect ratio. '
                .'Shows the left click button, right click button, and center scroll wheel clearly under bright soft studio lighting. '
                .'Educational computer hardware photo, wide 16:9 horizontal layout, no blurry borders.';
        }

        // 9. Phần cứng: Màn hình, Thân máy (CPU), Máy in, Loa, USB
        if (preg_match('/máy\s*in|màn\s*hình|thân\s*máy|cpu|ổ\s*cứng|usb|loa\s*máy\s*tính|tai\s*nghe|thiết\s*bị/ui', $combinedText)) {
            return "A clear, realistic educational photograph of computer hardware and digital devices in wide 16:9 landscape aspect ratio. "
                ."Context: {$questionTitle}. "
                ."Clean modern desktop computer setup on a bright classroom desk, crisp studio lighting, wide 16:9 horizontal presentation, no blurry borders.";
        }

        // 10. Trình duyệt web & Mạng Internet an toàn
        if (preg_match('/trình\s*duyệt|internet|web|google|tìm\s*kiếm|địa\s*chỉ\s*trang\s*web|url|mật\s*khẩu|an\s*toàn\s*số/ui', $combinedText)) {
            return 'A realistic, clean computer screenshot of a modern web browser window in wide 16:9 landscape aspect ratio. '
                .'Displays address URL bar, Back and Forward navigation buttons, and a clean safe educational web portal on the screen. '
                .'Crisp modern flat UI, educational digital literacy graphic, wide 16:9 widescreen layout, no blurry borders.';
        }

        // Mặc định: Xây dựng prompt tiếng Anh chuẩn mô tả tình huống câu hỏi
        return "A professional educational illustration or clean software screenshot in wide 16:9 landscape aspect ratio for an elementary computer science / IC3 Spark test question. "
            ."Topic and Context: {$questionTitle}. "
            ."Details: {$description}. "
            ."Clean modern digital education visual, realistic computer environment, crisp details, 16:9 widescreen presentation, strictly no cartoon animals, no gibberish text, no blurry borders.";
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

        $extension = match ($matches[1]) {
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'jpg',
        };

        $path = 'question-assets/ai-'.Str::uuid().'.'.$extension;
        Storage::disk('public')->put($path, $binary);

        return Storage::url($path);
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
- Tạo đúng {$questionCount} câu hỏi theo yêu cầu của giáo viên. Tuyệt đối không tạo thiếu số lượng câu hỏi.
- Nếu tệp nguồn có ít nội dung hoặc chỉ là một tài liệu đơn lẻ, hãy chủ động khai thác đa dạng các góc nhìn Tin học & kỹ năng số thiết thực xoay quanh tài liệu đó (như: cách đặt tên và lưu trữ tệp khoa học, định dạng đuôi tệp mở rộng .docx/.xlsx/.pdf/.png, phần mềm phù hợp để mở và chỉnh sửa, thiết bị ngoại vi kết nối và in ấn, thao tác sao lưu và bảo mật dữ liệu, quy tắc chia sẻ an toàn qua mạng số) để luôn đảm bảo tạo đủ {$questionCount} câu hỏi phong phú, bổ ích.
- Mọi câu hỏi đầu ra phải thuộc Tin học/IC3 hoặc kỹ năng số.
- Luôn bám sát yêu cầu của giáo viên và nội dung thật trong tệp; nếu có cả hai thì phải kết hợp cả hai.
- Không bịa dữ kiện trái ngược với nguồn. Từng câu hỏi phải tự chứa đủ ngữ cảnh tình huống để học sinh trả lời mà không cần nhìn vào tệp gốc của giáo viên.

QUY TRÌNH SUY LUẬN:
1. Nhận diện loại tài liệu, mục đích, cấu trúc và các dữ kiện có thể đọc chắc chắn.
2. Xác định ý định của giáo viên: số lượng câu ({$questionCount} câu), dạng câu, mức độ, chủ đề và phần cần tập trung.
3. Nếu nguồn đã có nội dung Tin học hoặc câu hỏi Tin học, trích xuất và biên soạn sát nội dung đó.
4. Nếu nguồn là hình ảnh hoặc văn bản về chủ đề khác, hãy đặt tình huống số thực tế xoay quanh chính đối tượng đó (cách xử lý ảnh, lưu trữ tệp, mở phần mềm, in ấn, bảo vệ dữ liệu, tìm kiếm...) để tạo đủ {$questionCount} câu hỏi Tin học thiết thực.
5. Chọn dạng câu phù hợp nhất với nội dung: một đáp án, nhiều đáp án hoặc ghép nối. Không ép mọi câu về cùng một dạng nếu giáo viên không yêu cầu.
6. Tạo phương án nhiễu hợp lý, rõ nghĩa, không mơ hồ; đáp án đúng phải kiểm chứng được từ kiến thức Tin học phổ thông/IC3 chắc chắn.

RÀNG BUỘC CHẤT LƯỢNG:
- Ngôn ngữ tiếng Việt trong sáng, ngắn gọn, phù hợp học sinh tiểu học.
- Đảm bảo tạo đủ {$questionCount} câu hỏi, không lặp lại cùng một ý dưới nhiều cách hỏi.
- Mỗi câu hỏi phải tự đủ dữ kiện; riêng câu có `needs_image: true` thì dữ kiện trực quan phải nằm đầy đủ trong ảnh mới do AI tạo và được lưu kèm câu hỏi.
- Tuyệt đối không viết mơ hồ “theo hình trên”, “trong tài liệu này”, “dựa vào thời khóa biểu”, “ở bảng dưới đây”... Hãy đưa tình huống cụ thể vào ngay câu hỏi (ví dụ: "Khi lưu tệp danh sách học sinh...", "Để in tài liệu báo cáo ra giấy...").
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
