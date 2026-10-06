<?php

namespace App\Services;

use App\Models\Question;
use Illuminate\Support\Collection;

/**
 * Chấm câu hỏi theo cơ sở dữ liệu cho phần luyện tập bằng giọng nói.
 * Cùng quy tắc chấm với phòng làm bài (AttemptController) cho các dạng: chọn đáp án, chọn nhiều đáp án,
 * phân loại khái niệm (MultipleChoiceText) và ghép nối (Matching). Sequence/Hotspot chưa hỗ trợ ở đây.
 */
class QuestionGrader
{
    /** Các dạng câu hỏi mà phần giọng nói hỗ trợ */
    public const SUPPORTED = ['MultipleChoice', 'MultipleResponse', 'MultipleChoiceText', 'Matching'];

    /**
     * @param  mixed  $answer  Chọn đáp án: mảng vị trí đã chọn | Phân loại: mảng chỉ số lựa chọn theo từng dòng | Ghép nối: mảng id vế phải theo từng vế trái
     */
    public function isCorrect(Question $question, mixed $answer): bool
    {
        $options = $question->relationLoaded('options') ? $question->options : $question->options()->get();

        return match ($question->type) {
            'MultipleChoice', 'MultipleResponse' => $this->choice($options, $answer),
            'MultipleChoiceText' => $this->classify($options, $answer),
            'Matching' => $this->matching($options, $answer),
            default => false,
        };
    }

    private function choice(Collection $options, mixed $answer): bool
    {
        $correct = $options->filter(fn ($o) => (bool) $o->is_correct)->pluck('position')->map(fn ($p) => (int) $p)->sort()->values()->all();
        $selected = is_array($answer) ? array_map('intval', $answer) : (is_numeric($answer) ? [(int) $answer] : []);
        sort($selected);

        return $correct !== [] && $correct === array_values($selected);
    }

    private function classify(Collection $options, mixed $answer): bool
    {
        if (! is_array($answer) || $options->isEmpty()) {
            return false;
        }
        foreach ($options->values() as $index => $option) {
            if ((int) ($answer[$index] ?? -1) !== (int) ($option->metadata['correct_index'] ?? -2)) {
                return false;
            }
        }

        return true;
    }

    private function matching(Collection $options, mixed $answer): bool
    {
        if (! is_array($answer) || $options->isEmpty() || count($answer) !== $options->count()) {
            return false;
        }
        foreach ($options->pluck('position')->all() as $index) {
            if ((int) ($answer[$index] ?? -1) !== (int) $index) {
                return false;
            }
        }

        return true;
    }
}
