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
        $request->validate([
            'files' => 'nullable|array|max:10',
            'files.*' => 'file|max:20480|mimes:jpg,jpeg,png,webp,pdf',
            'file' => 'nullable|file|max:20480|mimes:jpg,jpeg,png,webp,pdf',
            'text' => 'nullable|string|max:8000',
            'context' => 'nullable|string|max:8000',
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
                $questions = $this->gemini->generateQuestionsFromFiles($fileItems, $promptText);
            } else {
                // Chế độ giáo viên chỉ nhập ý tưởng / đề cương thuần chữ không kèm ảnh
                $questions = $this->gemini->generateQuestionsFromText($promptText);
            }

            if (empty($questions)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chưa nhận diện được nội dung bài học. Thầy/Cô có thể nhập thêm vài từ khóa gợi ý chi tiết hơn để AI soạn đúng theo ý nhé!',
                ], 422);
            }

            $count = count($questions);

            return response()->json([
                'success' => true,
                'questions' => $questions,
                'count' => $count,
                'message' => "Đã phân tích xong và tìm thấy {$count} câu hỏi chất lượng!",
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
}
