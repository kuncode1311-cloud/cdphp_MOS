<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Level;
use App\Models\PracticeTest;
use App\Models\Question;
use App\Models\Topic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Controller Quản Trị & Soạn Bộ Đề Thi Thử (Admin Mock Test Controller)
 *
 * Chức năng:
 * 1. Quản lý danh sách các bộ đề thi thử chuẩn IC3 GS6 theo từng Khối lớp.
 * 2. Studio Soạn đề thi thử: Chọn lọc câu hỏi từ 7 chủ đề trong Khối đưa vào đề thi thử.
 * 3. Hỗ trợ thuật toán bốc đề ngẫu nhiên theo ma trận số lượng câu hỏi từ mỗi chủ đề.
 */
class MockTestController extends Controller
{
    /**
     * Danh sách các bộ đề thi thử
     */
    public function index(Request $request): View
    {
        $levels = Level::orderBy('grade')->get();
        $selectedGrade = $request->integer('grade', $levels->first()?->grade ?? 3);
        $selectedLevel = $levels->firstWhere('grade', $selectedGrade) ?? $levels->first();

        $mockTests = PracticeTest::where('is_mock', true)
            ->when($selectedLevel, fn ($q) => $q->where('level_id', $selectedLevel->id))
            ->withCount('mockQuestions')
            ->orderBy('id', 'desc')
            ->get();

        $totalMockTestsAll = PracticeTest::where('is_mock', true)->count();
        $totalQuestionsInGrade = $selectedLevel 
            ? Question::whereHas('practiceTest.topic', fn ($q) => $q->where('level_id', $selectedLevel->id))->where('is_published', true)->distinct()->count() 
            : 0;

        return view('admin.mock-tests.index', compact(
            'levels',
            'selectedGrade',
            'selectedLevel',
            'mockTests',
            'totalMockTestsAll',
            'totalQuestionsInGrade'
        ));
    }

    /**
     * Giao diện soạn bộ đề thi thử mới
     */
    public function create(Request $request): View
    {
        $levels = Level::with(['topics.tests.questions' => function ($q) {
            $q->where('is_published', true)->with(['options', 'assets']);
        }])->orderBy('grade')->get();

        $selectedGrade = $request->integer('grade', $levels->first()?->grade ?? 3);
        $selectedLevel = $levels->firstWhere('grade', $selectedGrade) ?? $levels->first();

        return view('admin.mock-tests.builder', [
            'mode' => 'create',
            'mockTest' => new PracticeTest([
                'duration_minutes' => 45,
                'pass_score' => 700,
                'max_score' => 1000,
                'is_published' => true,
                'shuffle_questions' => true,
                'shuffle_options' => true,
            ]),
            'levels' => $levels,
            'selectedGrade' => $selectedGrade,
            'selectedLevel' => $selectedLevel,
            'selectedQuestionIds' => [],
        ]);
    }

    /**
     * Lưu bộ đề thi thử mới
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'level_id' => 'required|exists:levels,id',
            'name' => 'required|string|max:255',
            'duration_minutes' => 'required|integer|min:5|max:180',
            'pass_score' => 'required|integer|min:100|max:1000',
            'is_published' => 'nullable|boolean',
            'shuffle_questions' => 'nullable|boolean',
            'shuffle_options' => 'nullable|boolean',
            'question_ids' => 'required|array|min:1',
            'question_ids.*' => 'exists:questions,id',
        ], [
            'question_ids.required' => 'Vui lòng chọn ít nhất 1 câu hỏi để đưa vào bộ đề thi thử.',
            'question_ids.min' => 'Vui lòng chọn ít nhất 1 câu hỏi để đưa vào bộ đề thi thử.',
        ]);

        try {
            $level = Level::findOrFail($validated['level_id']);

            // Sinh slug chuẩn không dấu và duy nhất
            $baseSlug = Str::slug($validated['name']) ?: ('de-thi-thu-k'.$level->grade);
            $slug = $baseSlug;
            $counter = 1;
            while (PracticeTest::where('slug', $slug)->exists()) {
                $counter++;
                $slug = "{$baseSlug}-{$counter}";
            }

            $mockTest = PracticeTest::create([
                'level_id' => $level->id,
                'topic_id' => null, // Đề thi thử tổng hợp cấp Khối không thuộc riêng 1 chủ đề
                'is_mock' => true,
                'name' => $validated['name'],
                'slug' => $slug,
                'duration_minutes' => $validated['duration_minutes'],
                'question_count' => count($validated['question_ids']),
                'pass_score' => $validated['pass_score'],
                'max_score' => 1000,
                'position' => 0,
                'is_published' => $request->boolean('is_published', true),
                'shuffle_questions' => $request->boolean('shuffle_questions', true),
                'shuffle_options' => $request->boolean('shuffle_options', true),
            ]);

            // Gắn danh sách câu hỏi vào bảng pivot
            $syncData = [];
            foreach ($validated['question_ids'] as $position => $questionId) {
                $syncData[$questionId] = ['position' => $position + 1];
            }
            $mockTest->mockQuestions()->sync($syncData);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Đã tạo bộ đề thi thử '{$mockTest->name}' thành công!",
                    'redirect' => route('admin.mock-tests.index', ['grade' => $level->grade]),
                ]);
            }

            return redirect()->route('admin.mock-tests.index', ['grade' => $level->grade])
                ->with('ok', "Đã tạo bộ đề thi thử '{$mockTest->name}' với {$mockTest->question_count} câu hỏi thành công!");
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Lỗi khi tạo bộ đề thi thử: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lỗi khi tạo bộ đề thi thử: ' . $e->getMessage(),
                ], 500);
            }

            return back()->withInput()->with('error', 'Có lỗi khi tạo bộ đề thi thử: ' . $e->getMessage());
        }
    }

    /**
     * Giao diện chỉnh sửa bộ đề thi thử
     */
    public function edit(PracticeTest $mockTest): View
    {
        abort_unless($mockTest->is_mock, 404, 'Bộ đề này không phải đề thi thử tổng hợp.');

        $levels = Level::with(['topics.tests.questions' => function ($q) {
            $q->where('is_published', true)->with(['options', 'assets']);
        }])->orderBy('grade')->get();

        $selectedLevel = $mockTest->level ?? $levels->first();
        $selectedGrade = $selectedLevel?->grade ?? 3;

        $selectedQuestionIds = $mockTest->mockQuestions()->pluck('questions.id')->all();

        return view('admin.mock-tests.builder', [
            'mode' => 'edit',
            'mockTest' => $mockTest,
            'levels' => $levels,
            'selectedGrade' => $selectedGrade,
            'selectedLevel' => $selectedLevel,
            'selectedQuestionIds' => $selectedQuestionIds,
        ]);
    }

    /**
     * Cập nhật bộ đề thi thử
     */
    public function update(Request $request, PracticeTest $mockTest): RedirectResponse|JsonResponse
    {
        abort_unless($mockTest->is_mock, 404);

        $validated = $request->validate([
            'level_id' => 'required|exists:levels,id',
            'name' => 'required|string|max:255',
            'duration_minutes' => 'required|integer|min:5|max:180',
            'pass_score' => 'required|integer|min:100|max:1000',
            'is_published' => 'nullable|boolean',
            'shuffle_questions' => 'nullable|boolean',
            'shuffle_options' => 'nullable|boolean',
            'question_ids' => 'required|array|min:1',
            'question_ids.*' => 'exists:questions,id',
        ]);

        $mockTest->update([
            'level_id' => $validated['level_id'],
            'name' => $validated['name'],
            'duration_minutes' => $validated['duration_minutes'],
            'question_count' => count($validated['question_ids']),
            'pass_score' => $validated['pass_score'],
            'is_published' => $request->boolean('is_published'),
            'shuffle_questions' => $request->boolean('shuffle_questions'),
            'shuffle_options' => $request->boolean('shuffle_options'),
        ]);

        $syncData = [];
        foreach ($validated['question_ids'] as $position => $questionId) {
            $syncData[$questionId] = ['position' => $position + 1];
        }
        $mockTest->mockQuestions()->sync($syncData);

        $grade = $mockTest->level?->grade ?? 3;

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Đã cập nhật bộ đề thi thử '{$mockTest->name}' thành công!",
                'redirect' => route('admin.mock-tests.index', ['grade' => $grade]),
            ]);
        }

        return redirect()->route('admin.mock-tests.index', ['grade' => $grade])
            ->with('ok', "Đã cập nhật bộ đề thi thử '{$mockTest->name}' thành công!");
    }

    /**
     * Xóa bộ đề thi thử
     */
    public function destroy(PracticeTest $mockTest): RedirectResponse
    {
        abort_unless($mockTest->is_mock, 404);

        $grade = $mockTest->level?->grade ?? 3;
        $name = $mockTest->name;

        // Bảng pivot cascadeOnDelete tự động dọn dẹp
        $mockTest->delete();

        return redirect()->route('admin.mock-tests.index', ['grade' => $grade])
            ->with('ok', "Đã xóa bộ đề thi thử '{$name}' thành công.");
    }

    /**
     * API Thông Minh: Bốc ngẫu nhiên câu hỏi theo phân bổ đồng đều từ các chủ đề trong Khối
     */
    public function quickRandom(Request $request): JsonResponse
    {
        $levelId = $request->integer('level_id');
        $level = Level::with(['topics.tests.questions' => fn ($q) => $q->where('is_published', true)])->findOrFail($levelId);

        $totalDesired = $request->integer('total_count', 30);
        $topics = $level->topics;

        if ($topics->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Khối học chưa có chủ đề nào.'], 422);
        }

        $perTopic = (int) max(1, floor($totalDesired / $topics->count()));
        $selectedQuestionIds = [];

        foreach ($topics as $topic) {
            $allTopicQuestionIds = $topic->tests->flatMap->questions->pluck('id')->all();
            if (empty($allTopicQuestionIds)) {
                continue;
            }
            shuffle($allTopicQuestionIds);
            $picked = array_slice($allTopicQuestionIds, 0, $perTopic);
            $selectedQuestionIds = array_merge($selectedQuestionIds, $picked);
        }

        // Nếu còn thiếu so với target thì bốc ngẫu nhiên thêm từ các câu chưa chọn
        if (count($selectedQuestionIds) < $totalDesired) {
            $allLevelQuestionIds = $topics->flatMap->tests->flatMap->questions->pluck('id')->all();
            $remaining = array_values(array_diff($allLevelQuestionIds, $selectedQuestionIds));
            shuffle($remaining);
            $needed = $totalDesired - count($selectedQuestionIds);
            $additional = array_slice($remaining, 0, $needed);
            $selectedQuestionIds = array_merge($selectedQuestionIds, $additional);
        }

        return response()->json([
            'success' => true,
            'question_ids' => array_values(array_unique($selectedQuestionIds)),
            'count' => count($selectedQuestionIds),
        ]);
    }
}
