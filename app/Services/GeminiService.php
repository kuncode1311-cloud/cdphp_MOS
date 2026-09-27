<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

    /** Endpoint REST của Gemini */
    private string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta';

    public function __construct()
    {
        $rawKeys = (string) config('services.gemini.api_keys', '');
        $this->apiKeys = array_values(array_filter(array_map('trim', explode(',', $rawKeys))));
    }

    /**
     * Tạo danh sách câu hỏi từ nhiều tệp (ảnh hoặc PDF) cùng lúc
     * Đọc chính xác nội dung chữ trong tệp, không bịa đặt nội dung không có.
     *
     * @param  array  $fileItems  Mảng các phần tử: ['path' => string, 'mime_type' => string, 'name' => string]
     * @param  string  $context  Ngữ cảnh bổ sung từ giáo viên
     * @return array Danh sách câu hỏi đã chuẩn hóa
     */
    public function generateQuestionsFromFiles(array $fileItems, string $context = ''): array
    {
        // Chỉ tải PDF lên Google sau khi API riêng thất bại hoặc chưa được cấu hình.
        $questions = $this->tryPrivateApi($this->buildFilePrompt($context), $fileItems);
        if ($questions !== null) {
            return $questions;
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
        $parts[] = ['text' => $this->buildFilePrompt($context)];

        $payload = [
            'contents' => [['parts' => $parts]],
            'generationConfig' => $this->generationConfig(),
        ];

        return $this->callWithKeyRotation($payload);
    }

    /**
     * Tạo danh sách câu hỏi từ một hình ảnh đơn lẻ
     */
    public function generateQuestionsFromImage(string $filePath, string $context = ''): array
    {
        return $this->generateQuestionsFromFiles([[
            'path' => $filePath,
            'mime_type' => mime_content_type($filePath) ?: 'image/jpeg',
            'name' => basename($filePath),
        ]], $context);
    }

    /**
     * Tạo danh sách câu hỏi từ một file PDF
     */
    public function generateQuestionsFromPdf(string $filePath, string $context = ''): array
    {
        return $this->generateQuestionsFromFiles([[
            'path' => $filePath,
            'mime_type' => 'application/pdf',
            'name' => basename($filePath),
        ]], $context);
    }

    /**
     * Tạo câu hỏi từ nội dung văn bản thuần (khi giáo viên nhập mô tả / đề cương)
     */
    public function generateQuestionsFromText(string $textContent, string $context = ''): array
    {
        $prompt = $this->buildTextPrompt($context)."\n\n---\nNỘI DUNG YÊU CẦU SOẠN BÀI:\n".$textContent;
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

        return $this->callWithKeyRotation($payload);
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
                Log::warning('AI soạn đề: API riêng gặp lỗi, chuyển sang Gemini.', ['status' => $response->status()]);

                return null;
            }

            $rawText = $response->json('choices.0.message.content');
            if (! is_string($rawText) || trim($rawText) === '' || $response->json('choices.0.finish_reason') === 'length') {
                throw new \RuntimeException('API riêng trả về nội dung thiếu hoặc chưa hoàn tất.');
            }

            return $this->normalizeRequestedQuestionType($this->parseQuestionsJson($rawText), $prompt);
        } catch (ConnectionException|\RuntimeException $e) {
            // Không ghi nội dung phản hồi hay thông tin xác thực vào nhật ký.
            Log::warning('AI soạn đề: API riêng không trả về câu hỏi hợp lệ, chuyển sang Gemini.', ['error_type' => $e::class]);

            return null;
        }
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
            $question['type'] = 'Matching';
            $question['options'] = array_map(function (array $option): array {
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

            return $question;
        }, $questions);
    }

    /**
     * Chỉ dẫn phân tích tệp tài liệu: CƠ CHẾ LINH HOẠT THÔNG MINH
     * - Ưu tiên 1: Thực hiện theo ghi chú yêu cầu của giáo viên (nếu có).
     * - Ưu tiên 2: Trích xuất chính xác nếu ảnh là trang đề thi / bài tập chữ.
     * - Ưu tiên 3: Nhìn hình minh họa/đồ vật để sáng tạo câu hỏi phù hợp lứa tuổi học sinh tiểu học.
     * - Chỉ từ chối khi ảnh hoàn toàn vô nghĩa và không có gợi ý từ giáo viên.
     */
    private function buildFilePrompt(string $context = ''): string
    {
        $contextLine = $context ? "YÊU CẦU ĐẶC BIỆT TỪ GIÁO VIÊN: {$context}\n" : '';

        return <<<PROMPT
{$contextLine}Bạn là chuyên gia sư phạm tin học tiểu học, chuyên hỗ trợ giáo viên trích xuất và biên soạn các câu hỏi trắc nghiệm chuẩn IC3 GS6 (IC3 Spark) cho học sinh lớp 3, lớp 4, lớp 5.

PHẠM VI BẮT BUỘC: Câu hỏi đầu ra luôn phải thuộc môn Tin học/IC3 (máy tính, phần cứng, phần mềm, hệ điều hành, Internet, an toàn số, kỹ năng số và thiết bị công nghệ). Hãy linh hoạt chuyển mọi hình ảnh thành ngữ cảnh Tin học: ảnh sản phẩm có thể dùng để hỏi cách tìm kiếm/đọc/lưu thông tin bằng máy tính; ảnh phong cảnh có thể dùng để hỏi thao tác xem, chỉnh sửa, sắp xếp hoặc chia sẻ ảnh; ảnh đời sống có thể dùng để hỏi kỹ năng số an toàn. Không hỏi trực tiếp kiến thức ngoài Tin học như giá bán, kích thước hay đặc điểm vật lý nếu không gắn với thao tác số. Chỉ trả về [] khi ảnh hoàn toàn không thể liên hệ với kỹ năng Tin học.

HÃY XỬ LÝ THEO NGUYÊN TẮC LINH HOẠT VÀ THÔNG MINH NHƯ SAU:

1. ƯU TIÊN HÀNG ĐẦU - NẾU CÓ YÊU CẦU TỪ GIÁO VIÊN:
   - Luôn bám sát yêu cầu cụ thể của giáo viên trong mục "YÊU CẦU ĐẶC BIỆT TỪ GIÁO VIÊN" ở trên, kết hợp với các hình ảnh/tài liệu được cung cấp để tạo câu hỏi đúng chủ đề mong muốn.

2. TRƯỜNG HỢP ẢNH CHỨA ĐỀ BÀI / CÂU HỎI CÓ SẴN (Đề thi giấy, sách bài tập, văn bản):
   - Đọc và TRÍCH XUẤT CHÍNH XÁC nội dung các câu hỏi từ trong ảnh ra.
   - Giữ nguyên câu từ tiếng Việt gốc của đề bài.
   - Xác định và đánh dấu đáp án đúng chuẩn xác (`is_correct: true`).
   - Đảm bảo mỗi câu có đủ 4 phương án lựa chọn A, B, C, D rõ ràng, dễ hiểu.

3. TRƯỜNG HỢP ẢNH LÀ TRANH MINH HỌA, ĐỒ VẬT HOẶC SƠ ĐỒ (Không có sẵn câu hỏi chữ):
   - Tự động NHẬN DIỆN đồ vật, thiết bị hoặc chủ đề trong ảnh (ví dụ: máy tính bàn, laptop, chuột, bàn phím, màn hình, robot, dây cáp, cổng USB, thao tác người dùng, sơ đồ...).
   - TỰ ĐỘNG BIÊN SOẠN các câu hỏi trắc nghiệm hay, thiết thực xoay quanh kiến thức về thiết bị/chủ đề đó phù hợp với học sinh tiểu học.

4. TRƯỜNG HỢP ẢNH HOÀN TOÀN KHÔNG CHỨA NỘI DUNG GIÁO DỤC (Ảnh tối đen, ảnh mờ không thấy gì, ảnh rác...) VÀ GIÁO VIÊN CŨNG KHÔNG GHI CHÚ GÌ:
   - Trả về mảng rỗng: []

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
    private function buildTextPrompt(string $context = ''): string
    {
        $contextLine = $context ? "Ghi chú từ giáo viên: {$context}\n" : '';

        return <<<PROMPT
{$contextLine}Bạn là chuyên gia sư phạm tin học tiểu học, chuyên soạn câu hỏi và bài luyện tập chuẩn IC3 GS6 (IC3 Spark) cho học sinh lớp 3, lớp 4, lớp 5.

PHẠM VI BẮT BUỘC: Chỉ soạn nội dung thuộc môn Tin học/IC3. Không chuyển yêu cầu sang môn học hoặc kiến thức ngoài Tin học.

Dựa trên yêu cầu và nội dung đề cương được cung cấp:
- Hãy biên soạn các câu hỏi trắc nghiệm hay, bám sát kiến thức tin học tiểu học, ngôn từ trong sáng, dễ hiểu.
- Mỗi câu hỏi gồm đủ phương án phù hợp, xác định chính xác đáp án đúng.
- Được phép chọn `MultipleChoice`, `MultipleResponse` hoặc `Matching` tùy nội dung; với `Matching`, dùng `left`, `right`, `content`, `is_correct: true` cho từng cặp.
- Nếu yêu cầu có “ghép nối”, “nối cặp”, “matching” hoặc “nối hai cột”, bắt buộc trả `type: "Matching"` và các cặp `left`–`right`, không trả `MultipleResponse`.
- Trả về DUY NHẤT một chuỗi JSON array hợp lệ (không kèm lời dẫn):
  [
    {
      "title": "Nội dung câu hỏi?",
      "type": "MultipleChoice",
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
                try {
                    $response = Http::connectTimeout(10)->timeout(90)
                        ->withHeaders(['x-goog-api-key' => $key])
                        ->post("{$this->baseUrl}/models/{$model}:generateContent", $payload);

                    if ($response->status() === 429) {
                        Log::warning("GeminiService: Khóa #{$index} chạm giới hạn tần suất, chuyển khóa tiếp theo.");

                        continue;
                    }

                    if (! $response->successful()) {
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

                    return $this->parseQuestionsJson($rawText);

                } catch (ConnectionException|\RuntimeException $e) {
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
