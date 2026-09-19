<?php

namespace App\Http\Controllers;

use App\Models\PracticeTest;
use App\Models\Question;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Controller Chấm Điểm & Lưu Kết Quả Làm Bài (Attempt Controller)
 *
 * Chức năng: Tiếp nhận câu trả lời của học sinh khi hoàn thành bài thi luyện tập,
 * thực hiện kiểm tra tính đúng/sai của từng dạng câu hỏi (Trắc nghiệm, Ghép nối, Phân loại, Hotspot),
 * tính điểm thang 1000 và lưu lại lịch sử làm bài vào bảng `test_attempts`.
 */
class AttemptController extends Controller
{
    /**
     * Nhận bài nộp, chấm điểm tự động và lưu kết quả vào cơ sở dữ liệu
     *
     * @param  Request  $request  Chứa mảng answers (câu trả lời) và duration_seconds (thời gian làm)
     * @param  PracticeTest  $practiceTest  Bộ đề thi học sinh vừa hoàn thành
     * @return JsonResponse Kết quả chấm điểm chi tiết từng câu, tổng điểm và trạng thái hoàn thành
     */
    public function store(Request $request, PracticeTest $practiceTest): JsonResponse
    {
        $user = $request->user();

        if (! $user->isAdmin()) {
            abort_unless($practiceTest->is_published, 404);
            $practiceTest->loadMissing('topic.level');
            abort_unless(
                $user->canAccessLevel($practiceTest->topic->level_id),
                403,
                'Bạn chưa được cấp quyền truy cập bài luyện của Khối học này.',
            );
        }

        // 1. Xác thực dữ liệu nộp bài
        $data = $request->validate([
            'answers' => 'nullable|array',
            'question_ids' => 'nullable|array',
            'duration_seconds' => 'nullable|integer|min:0',
        ]);
        $submittedAnswers = $data['answers'] ?? [];

        // 2. Lấy danh sách câu hỏi đang phát hành của bộ đề
        $questions = $practiceTest->questions()->where('is_published', true)->with('options')->get();
        abort_if($questions->isEmpty(), 422, 'Bộ đề chưa có câu hỏi.');

        // Nếu client gửi kèm thứ tự question_ids (do đề thi xáo trộn câu hỏi)
        if (! empty($data['question_ids']) && is_array($data['question_ids'])) {
            $idOrder = array_flip(array_values($data['question_ids']));
            $questions = $questions->sortBy(fn ($q) => $idOrder[$q->id] ?? 9999)->values();
        }

        // 3. Chấm điểm bằng options/configuration nội bộ của MOS.
        $results = [];
        foreach ($questions as $index => $question) {
            $results[$index] = $this->isCorrect($question, $submittedAnswers[$index] ?? null);
        }

        // 4. Tính toán số câu đúng và quy đổi điểm theo thang 1000
        $correct = count(array_filter($results));
        $total = $questions->count();
        $score = (int) round($correct / $total * 1000);
        // Vượt màn cần đúng hết; báo cáo học tập dùng ngưỡng riêng trong config/learning.php.
        $isCompleted = $correct === $total;

        // 5. Lưu lượt làm bài vào bảng test_attempts (chỉ lưu với Học sinh, Giáo viên làm thử/thị phạm sẽ không lưu)
        $attemptId = null;
        if ($user->isStudent()) {
            $attempt = $user->attempts()->create([
                'practice_test_id' => $practiceTest->id,
                'score' => $score,
                'correct_answers' => $correct,
                'total_questions' => $total,
                'duration_seconds' => $data['duration_seconds'] ?? 0,
                'completed_at' => now(), // Ghi nhận thời điểm nộp bài
            ]);
            $attemptId = $attempt->id;

            // Tích lũy Sao thưởng từ bài thi vào tài khoản học sinh
            if ($score > 0) {
                $user->addRewardStars($score, "Hoàn thành {$practiceTest->name} ({$score}/1000đ)");
            }
        }

        // 6. Tổng hợp đáp án chuẩn phục vụ chế độ xem lại bài thi (Review Quiz Mode)
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

        // 7. Phản hồi JSON cho màn hình tổng kết điểm học sinh
        return response()->json([
            'saved' => (bool) $attemptId,
            'is_completed' => $isCompleted,
            'attempt_id' => $attemptId,
            'score' => $score,
            'earned_stars' => $user->isStudent() ? $score : 0,
            'current_stars' => $user->isStudent() ? (int) $user->fresh()->reward_stars : 0,
            'correct_answers' => $correct,
            'total_questions' => $total,
            'results' => $results,
            'correct_answers_data' => $correctAnswersData,
            'pass_score' => 1000,
            'max_score' => 1000,
        ]);
    }

    /**
     * Hàm phán đoán câu trả lời của học sinh là Đúng hay Sai theo từng dạng câu hỏi
     *
     * @param  Question  $question  Câu hỏi đã chuẩn hóa trong database MOS
     * @param  mixed  $answer  Dữ liệu câu trả lời học sinh đã chọn
     * @return bool True nếu trả lời chính xác, False nếu sai
     */
    private function isCorrect(Question $question, mixed $answer): bool
    {
        $options = $question->relationLoaded('options') ? $question->options : $question->options()->get();
        if (in_array($question->type, ['MultipleChoice', 'MultipleResponse'], true)) {
            $correct = $options->filter->is_correct->pluck('position')->sort()->values()->all();
            $selected = is_array($answer) ? array_map('intval', $answer) : [];
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

    /**
     * Chấm điểm dạng Ghép nối (Matching)
     * Kiểm tra từng vị trí nối cặp của vế trái sang vế phải
     */
    private function correctMatching(Collection $options, mixed $answer): bool
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

    /**
     * Chấm điểm dạng Phân loại / Dropdown văn bản (MultipleChoiceText)
     */
    private function correctMultipleChoiceText(Collection $options, mixed $answer): bool
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

    private function correctSequence(Collection $options, mixed $answer): bool
    {
        if (! is_array($answer) || count($answer) !== $options->count()) {
            return false;
        }

        return array_values(array_map('intval', $answer)) === $options->sortBy('position')->pluck('position')->values()->all();
    }
}
