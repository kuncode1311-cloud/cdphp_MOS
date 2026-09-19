<?php

namespace App\Observers;

use App\Models\Question;

/**
 * Class QuestionObserver
 *
 * Model Observer lắng nghe các sự kiện Eloquent của Model Question.
 * Tự động cập nhật số lượng câu hỏi (question_count) trong bảng practice_tests khi có thay đổi.
 * Tách biệt hoàn toàn side-effect ra khỏi Controller theo chuẩn kiến trúc MVC/OOP.
 */
class QuestionObserver
{
    /**
     * Xử lý khi một câu hỏi mới được tạo thành công
     */
    public function created(Question $question): void
    {
        $this->updateTestQuestionCount($question);
    }

    /**
     * Xử lý khi một câu hỏi bị xóa
     */
    public function deleted(Question $question): void
    {
        $this->updateTestQuestionCount($question);
    }

    /**
     * Cập nhật lại số lượng câu hỏi trong bộ đề
     */
    protected function updateTestQuestionCount(Question $question): void
    {
        if ($test = $question->practiceTest) {
            $count = $test->questions()->count();
            // Lưu số câu mà không phát thêm sự kiện cập nhật model.
            $test->updateQuietly(['question_count' => $count]);
        }
    }
}
