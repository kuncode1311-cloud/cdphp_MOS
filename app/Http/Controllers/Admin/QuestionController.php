<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveQuestionRequest;
use App\Models\Level;
use App\Models\PracticeTest;
use App\Models\Program;
use App\Models\Question;
use App\Models\Topic;
use App\Services\QuestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Class QuestionController
 *
 * Quản lý Studio Soạn Thảo & Quản Trị Bộ Câu Hỏi IC3.
 * Tuân thủ nghiêm ngặt mô hình MVC:
 * - Controller chỉ tiếp nhận request, gọi Service xử lý nghiệp vụ, trả về View hoặc JSON.
 * - Toàn bộ logic Database / Transaction được thực hiện qua QuestionService.
 */
class QuestionController extends Controller
{
    protected QuestionService $questionService;

    public function __construct(QuestionService $questionService)
    {
        $this->questionService = $questionService;
    }

    /**
     * Hiển thị giao diện Studio Quản trị Bộ đề & Câu hỏi
     * Hỗ trợ lọc theo Khối (grade), Chủ đề (topic), Bộ đề (test), và Câu hỏi đang chọn (question).
     */
    public function index(Request $request): View
    {
        abort_unless($request->user()->isAdmin(), 403, 'Khu vực Studio Soạn đề chỉ dành riêng cho Quản trị viên Trung tâm.');

        // 1. Tải danh sách các Khối (Level) kèm đếm chủ đề và bộ đề
        $levels = Level::with(['program', 'topics.tests'])->orderBy('grade')->get();

        // 2. Xác định Khối đang chọn (mặc định lấy Khối 3 hoặc khối đầu tiên)
        $selectedGrade = $request->integer('grade', $levels->first()?->grade ?? 3);
        $selectedLevel = $levels->firstWhere('grade', $selectedGrade) ?? $levels->first();

        // 3. Xác định Chủ đề đang chọn trong Khối
        $topics = $selectedLevel ? $selectedLevel->topics : collect();
        $selectedTopicId = $request->input('topic');
        $selectedTopic = $selectedTopicId
            ? $topics->firstWhere('id', $selectedTopicId)
            : $topics->first();

        // 4. Xác định Bộ đề (PracticeTest) đang chọn trong Chủ đề
        $tests = $selectedTopic ? $selectedTopic->tests : collect();
        $selectedTestId = $request->input('test');
        $selectedTest = $selectedTestId
            ? PracticeTest::with(['questions.options', 'questions.assets', 'topic.level'])->find($selectedTestId)
            : ($tests->first() ? PracticeTest::with(['questions.options', 'questions.assets', 'topic.level'])->find($tests->first()->id) : null);

        // 5. Xác định Câu hỏi đang mở biên tập (nếu action=new thì trả về null để mở form rỗng)
        $selectedQuestion = null;
        $isCreatingNew = ($request->input('action') === 'new');

        if (! $isCreatingNew && $selectedTest && $selectedTest->questions->isNotEmpty()) {
            $questionId = $request->input('q');
            $selectedQuestion = $questionId
                ? $selectedTest->questions->firstWhere('id', $questionId)
                : $selectedTest->questions->sortBy('position')->first();

            if ($selectedQuestion) {
                $selectedQuestion->load(['options', 'assets']);
            }
        }

        // Bảng tên hiển thị và mô tả trực quan cho từng dạng câu hỏi IC3 GS6
        $typeNames = [
            'MultipleChoice' => '🔘 Chọn 1 đáp án đúng (Radio - A, B, C, D)',
            'MultipleResponse' => '☑️ Chọn nhiều đáp án đúng (Checkbox - Tích nhiều ô)',
            'Matching' => '🔗 Ghép nối 2 vế (Nối thuật ngữ ➔ Định nghĩa)',
            'MultipleChoiceText' => '📋 Phân loại / Dropdown (Mỗi dòng chọn 1 đáp án)',
            'Hotspot' => '🎯 Nhấp vào vị trí trên ảnh (Click chọn vùng ảnh)',
            'Sequence' => '🔢 Sắp xếp thứ tự (Kéo thả Bước 1 ➔ Bước 2 ➔ Bước 3)',
        ];

        $programs = Program::orderBy('name')->get();

        return view('admin.bank.studio', compact(
            'levels',
            'programs',
            'selectedGrade',
            'selectedLevel',
            'topics',
            'selectedTopic',
            'tests',
            'selectedTest',
            'selectedQuestion',
            'typeNames'
        ));
    }

    /**
     * API JSON lấy chi tiết câu hỏi (Dùng cho chuyển câu tức thời không reload trang)
     */
    public function show(Question $question): JsonResponse
    {
        $question->load(['options', 'assets', 'practiceTest.topic.level']);

        return response()->json([
            'success' => true,
            'question' => $question->studioData(),
        ]);
    }

    /**
     * Lưu câu hỏi mới (Nội dung, loại câu, các đáp án, file đính kèm)
     */
    public function store(SaveQuestionRequest $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();
        $test = PracticeTest::findOrFail($validated['practice_test_id']);

        $options = $request->all()['options'] ?? [];
        $assetFile = $request->file('asset_file');
        $assetKind = $request->input('asset_kind', 'image');

        $question = $this->questionService->createQuestion(
            $test,
            $validated,
            $options,
            $assetFile,
            $assetKind
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã thêm câu hỏi thành công!',
                'question' => $question->studioData(),
            ]);
        }

        return redirect()->route('admin.questions.studio', [
            'grade' => $test->topic->level->grade,
            'topic' => $test->topic_id,
            'test' => $test->id,
            'q' => $question->id,
        ])->with('ok', 'Đã thêm câu hỏi mới thành công!');
    }

    /**
     * Cập nhật trọn gói câu hỏi (Tiêu đề, loại, điểm số, các đáp án A/B/C/D, file) trong 1 request duy nhất
     */
    public function update(SaveQuestionRequest $request, Question $question): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();
        $options = $request->all()['options'] ?? [];
        $assetFile = $request->file('asset_file');
        $assetKind = $request->input('asset_kind', 'image');

        $updatedQuestion = $this->questionService->updateQuestion(
            $question,
            $validated,
            $options,
            $assetFile,
            $assetKind
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã cập nhật câu hỏi thành công!',
                'question' => $updatedQuestion->studioData(),
            ]);
        }

        $test = $question->practiceTest;

        return redirect()->route('admin.questions.studio', [
            'grade' => $test->topic->level->grade,
            'topic' => $test->topic_id,
            'test' => $test->id,
            'q' => $question->id,
        ])->with('ok', "Đã cập nhật thành công câu hỏi số #{$question->position}!");
    }

    /**
     * Xóa câu hỏi khỏi bộ đề
     */
    public function destroy(Question $question): RedirectResponse
    {
        $test = $question->practiceTest;
        $this->questionService->deleteQuestion($question);

        return redirect()->route('admin.questions.studio', [
            'grade' => $test->topic->level->grade,
            'topic' => $test->topic_id,
            'test' => $test->id,
        ])->with('ok', 'Đã xóa câu hỏi thành công.');
    }
}
