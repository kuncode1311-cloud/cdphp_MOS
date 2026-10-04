<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PracticeTest;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Services\GeminiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Controller Soạn Câu Hỏi Bằng AI (AI Question Generator Controller)
 *
 * Hỗ trợ giáo viên tải ảnh/PDF hoặc nhập đề cương để AI tự động trích xuất và soạn thảo câu hỏi.
 */
class AiQuestionController extends Controller
{
    public function __construct(private readonly GeminiService $gemini) {}

    /**
     * Nhận file(s) upload → gọi Gemini → trả về danh sách câu hỏi preview (chưa lưu DB)
     * Hỗ trợ tối đa 10 ảnh / tài liệu PDF cùng lúc.
     */
    public function generate(Request $request): JsonResponse
    {
        $requestStartedAt = microtime(true);
        $request->validate([
            'files' => 'nullable|array|max:10',
            'files.*' => 'file|max:20480|mimes:jpg,jpeg,png,webp,pdf',
            'file' => 'nullable|file|max:20480|mimes:jpg,jpeg,png,webp,pdf',
            'text' => 'nullable|string|max:50000',
            'context' => 'nullable|string|max:50000',
            'generate_images' => 'nullable|boolean',
            'question_count' => 'nullable|integer|min:1|max:30',
        ], [
            'files.max' => 'Thầy/Cô có thể tải lên tối đa 10 tệp cùng lúc.',
            'files.*.max' => 'Mỗi tệp không được vượt quá 20MB.',
            'files.*.mimes' => 'Hệ thống hỗ trợ các định dạng hình ảnh (JPG, PNG, WEBP) hoặc tài liệu PDF.',
            'file.max' => 'Mỗi tệp không được vượt quá 20MB.',
            'file.mimes' => 'Hệ thống hỗ trợ các định dạng hình ảnh (JPG, PNG, WEBP) hoặc tài liệu PDF.',
        ]);

        $promptText = trim((string) $request->input('text', $request->input('context', '')));
        $hasText = ! empty($promptText);
        $hasFiles = $request->hasFile('files') || $request->hasFile('file');
        $generateImages = $request->boolean('generate_images');
        // AI đọc yêu cầu để biết số câu; nội dung có nêu số câu thì ưu tiên hơn ô chọn số câu.
        // Nếu AI không phân tích được thì dùng quy tắc nhận diện cụm "làm 20 câu" làm dự phòng.
        $aiCount = $hasText ? $this->gemini->detectRequestedQuestionCount($promptText) : null;
        $requestedInText = $aiCount === false ? self::detectRequestedCount($promptText) : $aiCount;
        $questionCount = $requestedInText ?? max(1, min(30, $request->integer('question_count', 5)));
        // Mỗi lượt gọi AI tối đa 10 câu để không bị cắt cụt; phần còn lại được bù ở bước sau.
        $batchCount = min($questionCount, 10);

        if (! $hasFiles && ! $hasText) {
            return response()->json([
                'success' => false,
                'message' => 'Thầy/Cô vui lòng tải lên hình ảnh, tài liệu hoặc nhập ý tưởng/yêu cầu vào ô nội dung nhé!',
            ], 422);
        }

        try {
            $uploadedFiles = [];
            if ($request->hasFile('files')) {
                $uploadedFiles = $request->file('files');
            } elseif ($request->hasFile('file')) {
                $uploadedFiles = [$request->file('file')];
            }

            if (! empty($uploadedFiles)) {
                $fileItems = [];
                foreach ($uploadedFiles as $file) {
                    $fileItems[] = [
                        'path' => $file->getRealPath(),
                        'mime_type' => $file->getMimeType() ?: 'image/jpeg',
                        'name' => $file->getClientOriginalName(),
                    ];
                }
                $questions = $this->gemini->generateQuestionsFromFiles($fileItems, $promptText, $generateImages, $batchCount);
            } else {
                // Chế độ giáo viên chỉ nhập ý tưởng / đề cương thuần chữ không kèm ảnh
                $questions = $this->gemini->generateQuestionsFromText($promptText, '', $generateImages, $batchCount);
            }

            // AI hoặc bộ lọc chất lượng có thể bỏ bớt câu; gọi bù để đủ số câu giáo viên đã chọn.
            $questions = $this->topUpQuestions($questions, $questionCount, $uploadedFiles ?? [], $promptText, $generateImages);

            if (empty($questions)) {
                return response()->json([
                    'success' => false,
                    'message' => $hasFiles
                        ? 'Chưa đọc rõ nội dung trong tệp. Thầy/Cô vui lòng kiểm tra tệp có chữ rõ nét hoặc nhập thêm yêu cầu mô tả tài liệu.'
                        : 'Chưa nhận diện được nội dung bài học. Thầy/Cô có thể nhập thêm vài từ khóa gợi ý chi tiết hơn để AI soạn đúng theo ý nhé!',
                    'ai_trace' => $this->gemini->executionTrace(),
                    'total_ms' => (int) round((microtime(true) - $requestStartedAt) * 1000),
                ], 422);
            }

            if ($generateImages) {
                $questions = $this->gemini->prepareIllustrationRequests($questions, true);
            }

            $count = count($questions);

            return response()->json([
                'success' => true,
                'questions' => $questions,
                'count' => $count,
                'message' => "Đã phân tích xong và tìm thấy {$count} câu hỏi chất lượng!".($requestedInText && $count !== $requestedInText ? " (Yêu cầu {$requestedInText} câu, nội dung chỉ đủ dữ kiện cho {$count} câu.)" : ''),
                'requested_count' => $questionCount,
                'ai_trace' => $this->gemini->executionTrace(),
                'total_ms' => (int) round((microtime(true) - $requestStartedAt) * 1000),
                'image_generation_enabled' => $generateImages,
            ]);

        } catch (\RuntimeException $e) {
            Log::error('AiQuestionController@generate: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        } catch (\Throwable $e) {
            Log::error('AiQuestionController@generate unexpected: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Hệ thống đang bận xử lý dữ liệu. Thầy/Cô vui lòng thử lại sau giây lát nhé!',
            ], 500);
        }
    }

    /**
     * Tạo ảnh minh họa cho một câu hỏi sau khi danh sách câu hỏi đã hiện lên giao diện.
     */
    public function generateIllustration(Request $request): JsonResponse
    {
        @set_time_limit(180);
        @ini_set('max_execution_time', '180');
        ignore_user_abort(true);

        $requestStartedAt = microtime(true);
        $data = $request->validate([
            'question' => 'required|array',
            'question.title' => 'required|string|max:2000',
            'question.type' => 'required|string|in:MultipleChoice,MultipleResponse,Matching',
            'question.needs_image' => 'nullable|boolean',
            'question.image_prompt' => 'nullable|string|max:2000',
            'question.options' => 'required|array|min:2',
            'question.options.*.content' => 'nullable|string|max:1000',
            'question.options.*.left' => 'nullable|string|max:1000',
            'question.options.*.right' => 'nullable|string|max:1000',
            'question.options.*.is_correct' => 'nullable|boolean',
            'question.options.*.position' => 'nullable|integer',
            'question.options.*.metadata' => 'nullable|array',
        ]);

        // Giải phóng khóa session để nhiều request tạo ảnh chạy song song thực sự (đa luồng)
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        $questions = $this->gemini->generateRequiredIllustrations([$data['question']], true);
        $question = $questions[0] ?? [];
        $path = $question['illustration_path'] ?? null;

        return response()->json([
            'success' => is_string($path) && $path !== '',
            'question' => $question,
            'illustration_path' => $path,
            'message' => $path ? 'Đã tạo ảnh minh họa.' : 'Chưa tạo được ảnh minh họa cho câu này.',
            'ai_trace' => $this->gemini->executionTrace(),
            'total_ms' => (int) round((microtime(true) - $requestStartedAt) * 1000),
        ], $path ? 200 : 422);
    }

    /**
     * Dọn ảnh AI tạm nếu giáo viên không lưu câu hỏi vào bài luyện.
     */
    public function cleanupIllustrations(Request $request): JsonResponse
    {
        $data = $request->validate([
            'paths' => 'required|array|max:20',
            'paths.*' => ['required', 'string', 'regex:#^/storage/question-assets/ai-[a-f0-9-]+\.(?:png|jpg|webp)$#i'],
        ]);

        $deleted = 0;
        foreach ($data['paths'] as $path) {
            $relativePath = ltrim(preg_replace('#^/storage/#', '', $path), '/');
            if (Storage::disk('public')->exists($relativePath)) {
                Storage::disk('public')->delete($relativePath);
                $deleted++;
            }
        }

        return response()->json([
            'success' => true,
            'deleted' => $deleted,
        ]);
    }

    /**
     * Nhận danh sách câu hỏi đã review → lưu vào DB (questions + question_options)
     */
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'practice_test_id' => 'required|exists:practice_tests,id',
            'questions' => 'required|array|min:1',
            'questions.*.title' => 'required|string|max:2000',
            'questions.*.type' => 'required|string|in:MultipleChoice,MultipleResponse,Matching',
            'questions.*.options' => 'required|array|min:2',
            'questions.*.options.*.content' => 'required|string|max:1000',
            'questions.*.options.*.is_correct' => 'required|boolean',
            'questions.*.illustration_path' => ['nullable', 'string', 'regex:#^/storage/question-assets/ai-[a-f0-9-]+\.(?:png|jpg|webp)$#i'],
        ], [
            'practice_test_id.required' => 'Chưa xác định bài luyện cần lưu câu hỏi.',
            'questions.required' => 'Danh sách câu hỏi chọn lưu không được để trống.',
        ]);

        $practiceTest = PracticeTest::findOrFail($request->integer('practice_test_id'));

        // Lấy vị trí thứ tự lớn nhất hiện tại để xếp tiếp nối vào cuối bài luyện
        $maxPosition = $practiceTest->questions()->max('position') ?? 0;
        $importedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($request->input('questions') as $i => $qData) {
                $position = $maxPosition + $i + 1;

                $question = Question::create([
                    'practice_test_id' => $practiceTest->id,
                    'type' => $qData['type'],
                    'title' => $qData['title'],
                    'position' => $position,
                    'points' => 1,
                    'is_published' => true,
                    'configuration' => [],
                ]);

                foreach ($qData['options'] as $j => $optData) {
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'content' => $optData['content'],
                        'is_correct' => (bool) $optData['is_correct'],
                        'position' => $j,
                        'metadata' => $qData['type'] === 'Matching' && isset($optData['left'], $optData['right'])
                            ? ['left' => $optData['left'], 'right' => $optData['right']]
                            : null,
                    ]);
                }

                $illustrationPath = $qData['illustration_path'] ?? null;
                if (is_string($illustrationPath) && $illustrationPath !== '') {
                    $relativePath = ltrim(preg_replace('#^/storage/#', '', $illustrationPath), '/');
                    if (\Illuminate\Support\Facades\Storage::disk('public')->exists($relativePath)) {
                        $question->assets()->create([
                            'kind' => 'question_image',
                            'path' => $illustrationPath,
                            'original_name' => basename($relativePath),
                            'mime_type' => \Illuminate\Support\Facades\Storage::disk('public')->mimeType($relativePath),
                            'metadata' => ['generated_by_ai' => true],
                        ]);
                    }
                }

                $importedCount++;
            }

            DB::commit();

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('AiQuestionController@import: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Lưu câu hỏi chưa thành công. Thầy/Cô vui lòng thử lại sau giây lát nhé!',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'count' => $importedCount,
            'message' => "Đã lưu thành công {$importedCount} câu hỏi vào bài luyện!",
        ]);
    }

    /**
     * Bổ sung câu hỏi khi AI trả thiếu so với số câu đã chọn (mỗi lượt tối đa 10 câu), loại câu trùng và cắt về đúng số lượng.
     *
     * @param  array<int, array<string, mixed>>  $questions
     * @param  array<int, \Illuminate\Http\UploadedFile>  $uploadedFiles
     * @return array<int, array<string, mixed>>
     */
    private function topUpQuestions(array $questions, int $wanted, array $uploadedFiles, string $promptText, bool $generateImages): array
    {
        $signature = fn (array $q): string => mb_strtolower(mb_substr(preg_replace('/\s+/u', ' ', (string) ($q['title'] ?? '')), 0, 80));

        $maxRounds = (int) ceil($wanted / 10) + 1;
        for ($round = 0; $round < $maxRounds && count($questions) < $wanted; $round++) {
            $missing = $wanted - count($questions);
            $existing = implode("\n", array_map(fn (array $q, int $i) => ($i + 1).'. '.($q['title'] ?? ''), $questions, array_keys($questions)));
            $note = trim($promptText."\n\nĐã có các câu hỏi sau, hãy soạn câu MỚI, khác hoàn toàn về nội dung và cách hỏi:\n".$existing);

            try {
                // Xin dư một câu để còn bù cho câu bị bộ lọc loại.
                $extra = ! empty($uploadedFiles)
                    ? $this->gemini->generateQuestionsFromFiles($this->fileItems($uploadedFiles), $note, $generateImages, min(10, $missing + 1))
                    : $this->gemini->generateQuestionsFromText($note, '', $generateImages, min(10, $missing + 1));
            } catch (\Throwable $e) {
                Log::warning('AiQuestionController: bù câu hỏi thất bại - '.$e->getMessage());
                break;
            }

            $seen = array_map($signature, $questions);
            foreach ($extra as $candidate) {
                if (is_array($candidate) && ! in_array($signature($candidate), $seen, true)) {
                    $questions[] = $candidate;
                    $seen[] = $signature($candidate);
                }
            }
        }

        return array_slice(array_values($questions), 0, $wanted);
    }

    /**
     * Chuyển tệp tải lên thành mảng thông tin để gửi cho dịch vụ AI.
     *
     * @param  array<int, \Illuminate\Http\UploadedFile>  $uploadedFiles
     * @return array<int, array{path: string, mime_type: string, name: string}>
     */
    private function fileItems(array $uploadedFiles): array
    {
        return array_map(fn ($file) => [
            'path' => $file->getRealPath(),
            'mime_type' => $file->getMimeType() ?: 'image/jpeg',
            'name' => $file->getClientOriginalName(),
        ], $uploadedFiles);
    }

    /**
     * Tìm số câu giáo viên ghi trong nội dung, ví dụ "làm 20 câu", "tạo 15 câu hỏi". Lấy lần xuất hiện đầu tiên (tổng số thường nói trước, phần chia nhỏ như "gồm 3 trắc nghiệm" nói sau);
     * trả về null nếu không có hoặc ngoài khoảng 1-30. Các tiêu đề "Câu 20." trong đề dán vào không bị nhầm.
     */
    public static function detectRequestedCount(string $text): ?int
    {
        if (! preg_match_all('/(?:làm|tạo|soạn|ra|cho|lấy|xuất|cần|viết|gồm|với|số lượng|tổng cộng|tổng|đúng)\s*:?\s*(?:đúng\s+|khoảng\s+|đủ\s+)?(\d{1,2})\s*câu/iu', $text, $matches)) {
            return null;
        }

        $number = (int) $matches[1][0];

        return $number >= 1 && $number <= 30 ? $number : null;
    }
}
