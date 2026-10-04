<?php

namespace App\Http\Controllers;

use App\Models\Level;
use App\Models\Question;
use App\Models\StudentMistake;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller Sổ Tay Câu Sai & Phòng Luyện Phục Thù (Student Mistake Controller)
 *
 * Chức năng:
 * 1. Hiển thị sổ tay các câu hỏi học sinh đã làm sai trong quá trình luyện tập và thi thử.
 * 2. Phân loại mức độ nghiêm trọng (sai 1 lần, sai nhiều lần báo động đỏ).
 * 3. Mở phòng luyện tập phục thù: làm lại chính xác các câu đang bị sai.
 * 4. Chấm điểm và cập nhật trạng thái "Đã khắc phục" khi học sinh làm đúng lại.
 */
class MistakeController extends Controller
{
    /**
     * Màn hình Sổ tay câu sai của học sinh
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user, 401);

        $levels = Level::orderBy('grade')->get();

        $selectedGrade = $request->input('grade', 'all');
        $selectedStatus = $request->input('status', 'unresolved');
        $selectedRisk = $request->input('risk', 'all');

        // Thống kê tổng quan
        $allMistakes = StudentMistake::where('user_id', $user->id)->get();
        $totalUnresolved = $allMistakes->where('status', 'unresolved')->count();
        $highRiskCount = $allMistakes->where('status', 'unresolved')->where('wrong_count', '>=', 2)->count();
        $resolvedCount = $allMistakes->where('status', 'resolved')->count();

        // Truy vấn danh sách câu sai theo bộ lọc
        $query = StudentMistake::where('user_id', $user->id)
            ->with(['question.options', 'question.assets', 'question.practiceTest.topic.level', 'practiceTest']);

        if ($selectedStatus !== 'all') {
            $query->where('status', $selectedStatus);
        }

        if ($selectedRisk === 'high_risk') {
            $query->where('wrong_count', '>=', 2);
        }

        if ($selectedGrade !== 'all' && is_numeric($selectedGrade)) {
            $gradeInt = (int) $selectedGrade;
            $query->whereHas('question.practiceTest.topic.level', function ($q) use ($gradeInt) {
                $q->where('grade', $gradeInt);
            });
        }

        $mistakes = $query->orderBy('wrong_count', 'desc')
            ->orderBy('last_wrong_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('learning.mistakes.index', compact(
            'levels',
            'mistakes',
            'totalUnresolved',
            'highRiskCount',
            'resolvedCount',
            'selectedGrade',
            'selectedStatus',
            'selectedRisk'
        ));
    }

    /**
     * Khởi động phòng luyện phục thù: làm lại các câu đang bị sai
     */
    public function launch(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 401);

        $gradeFilter = $request->input('grade');
        $questionId = $request->input('question_id');

        $query = StudentMistake::where('user_id', $user->id)
            ->with(['question.options', 'question.assets', 'question.practiceTest.topic.level']);

        if ($questionId && is_numeric($questionId)) {
            $query->where('question_id', (int) $questionId);
        } else {
            $query->where('status', 'unresolved');
            if ($gradeFilter && is_numeric($gradeFilter)) {
                $gradeInt = (int) $gradeFilter;
                $query->whereHas('question.practiceTest.topic.level', fn ($q) => $q->where('grade', $gradeInt));
            }
        }

        $mistakes = $query->orderBy('wrong_count', 'desc')->get();

        if ($mistakes->isEmpty()) {
            return redirect()->route('mistakes.index')
                ->with('ok', 'Tuyệt vời! Bạn hiện không có câu hỏi nào bị sai cần phải phục thù.');
        }

        // Chuyển đổi câu hỏi sang runtimeData an toàn cho giao diện làm bài
        $questions = $mistakes->map(function ($mistake) {
            $q = $mistake->question;
            $data = $q->runtimeData();
            $data['wrong_count'] = $mistake->wrong_count;
            $data['last_wrong_at'] = $mistake->last_wrong_at?->diffForHumans();
            return $data;
        })->values();

        return view('learning.mistakes.launch', compact('mistakes', 'questions'));
    }

    /**
     * Nộp bài luyện phục thù và cập nhật trạng thái câu sai
     */
    public function submit(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user, 401);

        $data = $request->validate([
            'answers' => 'nullable|array',
            'question_ids' => 'required|array|min:1',
            'duration_seconds' => 'nullable|integer|min:0',
        ]);

        $submittedAnswers = $data['answers'] ?? [];
        $questionIds = $data['question_ids'];

        // Chỉ chấm các câu thật sự nằm trong sổ câu sai của học sinh này (không cho lấy đáp án câu tùy ý)
        $ownMistakeIds = StudentMistake::where('user_id', $user->id)
            ->whereIn('question_id', $questionIds)
            ->pluck('question_id')
            ->all();
        $questions = Question::whereIn('id', $ownMistakeIds)->with('options')->get();
        // Giữ đúng thứ tự câu hỏi client gửi lên
        $idOrder = array_flip($questionIds);
        $questions = $questions->sortBy(fn ($q) => $idOrder[$q->id] ?? 9999)->values();

        $results = [];
        $resolvedCount = 0;
        $stillWrongCount = 0;

        foreach ($questions as $index => $question) {
            $isCorrect = $this->isCorrect($question, $submittedAnswers[$index] ?? null);
            $results[$index] = $isCorrect;

            $mistake = StudentMistake::where('user_id', $user->id)
                ->where('question_id', $question->id)
                ->first();

            if ($isCorrect) {
                // Chỉ tính sao khi câu chuyển từ "chưa khắc phục" sang "đã khắc phục"; làm lại câu đã xong thì không được thêm sao
                if ($mistake && $mistake->status === 'unresolved') {
                    $resolvedCount++;
                }
                if ($mistake) {
                    $mistake->update([
                        'correct_count' => $mistake->correct_count + 1,
                        'status' => 'resolved',
                        'last_resolved_at' => now(),
                    ]);
                }
            } else {
                $stillWrongCount++;
                if ($mistake) {
                    $mistake->update([
                        'wrong_count' => $mistake->wrong_count + 1,
                        'last_student_answer' => $submittedAnswers[$index] ?? null,
                        'status' => 'unresolved',
                        'last_wrong_at' => now(),
                    ]);
                }
            }
        }

        // Tặng sao thưởng khích lệ cho các câu đã phục thù thành công (mỗi câu 50 sao)
        $earnedStars = $resolvedCount * 50;
        if ($earnedStars > 0 && $user->isStudent()) {
            $user->addRewardStars($earnedStars, "Phục thù thành công {$resolvedCount} câu hỏi bị sai (+{$earnedStars} sao)");
        }

        // Tổng hợp đáp án chuẩn phục vụ xem lại (Review Mode)
        $correctAnswersData = [];
        foreach ($questions as $index => $question) {
            $options = $question->options;
            if (in_array($question->type, ['MultipleChoice', 'MultipleResponse'], true)) {
                $correctAnswersData[$index] = $options->filter->is_correct->pluck('position')->sort()->values()->all();
            } elseif ($question->type === 'Matching') {
                $correctAnswersData[$index] = $options->pluck('position', 'position')->all();
            } elseif ($question->type === 'MultipleChoiceText') {
                $map = [];
                foreach ($options as $optIdx => $option) {
                    $map[$optIdx] = (int) ($option->metadata['correct_index'] ?? -1);
                }
                $correctAnswersData[$index] = $map;
            } elseif ($question->type === 'Hotspot') {
                $correctAnswersData[$index] = $options->firstWhere('is_correct', true)?->position ?? 0;
            } elseif ($question->type === 'Sequence') {
                $correctAnswersData[$index] = $options->sortBy('position')->pluck('position')->values()->all();
            }
        }

        return response()->json([
            'success' => true,
            'total_questions' => $questions->count(),
            'resolved_count' => $resolvedCount,
            'still_wrong_count' => $stillWrongCount,
            'earned_stars' => $earnedStars,
            'current_stars' => (int) $user->fresh()->reward_stars,
            'results' => $results,
            'correct_answers_data' => $correctAnswersData,
        ]);
    }

    /**
     * Hàm kiểm tra tính đúng/sai của câu hỏi
     */
    private function isCorrect(Question $question, mixed $answer): bool
    {
        $options = $question->options;
        if (in_array($question->type, ['MultipleChoice', 'MultipleResponse'], true)) {
            $correct = $options->filter(fn ($o) => (bool) $o->is_correct)->pluck('position')->sort()->values()->all();
            $selected = is_array($answer) ? array_map('intval', $answer) : (is_numeric($answer) ? [(int) $answer] : []);
            sort($correct);
            sort($selected);

            return $correct === $selected;
        }

        return match ($question->type) {
            'Matching' => $this->correctMatching($options, $answer),
            'MultipleChoiceText' => $this->correctMultipleChoiceText($options, $answer),
            'Hotspot' => (bool) $options->firstWhere('position', (int) $answer)?->is_correct,
            'Sequence' => $this->correctSequence($options, $answer),
            default => false,
        };
    }

    private function correctMatching($options, mixed $answer): bool
    {
        if (! is_array($answer) || $options->isEmpty() || count($answer) !== $options->count()) {
            return false;
        }
        foreach ($options->pluck('position')->all() as $index) {
            if ((int) ($answer[$index] ?? -1) !== $index) {
                return false;
            }
        }

        return true;
    }

    private function correctMultipleChoiceText($options, mixed $answer): bool
    {
        if (! is_array($answer)) {
            return false;
        }
        foreach ($options as $index => $option) {
            if ((int) ($answer[$index] ?? -1) !== (int) ($option->metadata['correct_index'] ?? -2)) {
                return false;
            }
        }

        return $options->isNotEmpty();
    }

    private function correctSequence($options, mixed $answer): bool
    {
        if (! is_array($answer) || count($answer) !== $options->count()) {
            return false;
        }

        return array_values(array_map('intval', $answer)) === $options->sortBy('position')->pluck('position')->values()->all();
    }
}
