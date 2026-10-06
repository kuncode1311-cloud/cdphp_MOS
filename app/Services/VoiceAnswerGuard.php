<?php

namespace App\Services;

use App\Support\VietText;
use Illuminate\Support\Facades\Log;

/**
 * Soát câu trả lời của AI trước khi hiện và đọc to (chạy bằng code, không tốn lượt AI).
 *
 * Bỏ những câu có dấu hiệu sai:
 * - Có con số KHÔNG xuất hiện trong dữ liệu đã gửi cho AI (AI tự bịa hoặc tự tính sai).
 * - Nhắc "chủ đề X" mà X không có trong danh sách chủ đề thật.
 * - Đang làm câu hỏi mà AI lộ/đoán đáp án.
 * Giữ nguyên cách trình bày (đoạn, gạch đầu dòng, in đậm) của những câu còn lại.
 */
class VoiceAnswerGuard
{
    /** Các số nhỏ và số chuẩn luôn được phép (số thứ tự, thang điểm, mức đạt...) */
    private const ALWAYS_ALLOWED = [100, 700, 900, 1000];
    private const SMALL_NUMBER_MAX = 20;

    /**
     * @param  array<int, int>  $allowedNumbers  Các số có trong dữ liệu đã gửi cho AI
     * @param  array<int, string>  $topicNames  Tên chủ đề thật
     * @return array{text: string, removed: int}
     */
    public function check(string $reply, array $allowedNumbers, array $topicNames, bool $quizPending): array
    {
        $allowed = array_flip(array_merge($allowedNumbers, self::ALWAYS_ALLOWED));
        $topics = array_values(array_filter(array_map([VietText::class, 'norm'], $topicNames)));
        $removed = 0;
        $reasons = [];

        $lines = preg_split('/\n/u', $reply);
        $kept = [];
        foreach ($lines as $line) {
            if (trim($line) === '') {
                $kept[] = '';
                continue;
            }

            $prefix = preg_match('/^(\s*-\s+)/u', $line, $pm) ? $pm[1] : '';
            $body = substr($line, strlen($prefix));
            $sentences = preg_split('/(?<=[.!?…])\s+/u', $body) ?: [$body];

            $good = [];
            foreach ($sentences as $sentence) {
                $why = $this->problem($sentence, $allowed, $topics, $quizPending);
                if ($why === null) {
                    $good[] = $sentence;
                } else {
                    $removed++;
                    $reasons[] = $why;
                }
            }

            if ($good !== []) {
                $kept[] = $prefix . implode(' ', $good);
            }
        }

        $text = trim(preg_replace("/\n{3,}/u", "\n\n", implode("\n", $kept)));

        if ($removed > 0) {
            Log::info('VoiceAnswerGuard: đã bỏ câu trả lời sai lệch', ['so_cau' => $removed, 'ly_do' => array_values(array_unique($reasons))]);
        }

        return ['text' => $text, 'removed' => $removed];
    }

    /** Lý do câu này không được dùng, hoặc null nếu câu ổn */
    private function problem(string $sentence, array $allowed, array $topics, bool $quizPending): ?string
    {
        // 1) Con số phải có trong dữ liệu thật (cho phép số nhỏ như "3 câu", "bài 2")
        if (preg_match_all('/\d{1,3}(?:[.,]\d{3})+|\d+/u', $sentence, $m)) {
            foreach ($m[0] as $raw) {
                $n = (int) preg_replace('/[.,]/', '', $raw);
                if ($n > self::SMALL_NUMBER_MAX && ! isset($allowed[$n])) {
                    return 'so_khong_co_trong_du_lieu';
                }
            }
        }

        // 2) "chủ đề <Tên viết hoa>" phải là chủ đề thật
        if ($topics !== [] && preg_match_all('/chủ đề\s+(?:\*\*|«|")?([A-ZĐÀÁẢÃẠĂẮẰẲẴẶÂẤẦẨẪẬÈÉẺẼẸÊẾỀỂỄỆÌÍỈĨỊÒÓỎÕỌÔỐỒỔỖỘƠỚỜỞỠỢÙÚỦŨỤƯỨỪỬỮỰỲÝỶỸỴ][^,.;:!?\n*"»]{2,60})/u', $sentence, $tm)) {
            foreach ($tm[1] as $name) {
                if (! $this->isRealTopic(VietText::norm($name), $topics)) {
                    return 'chu_de_khong_co_that';
                }
            }
        }

        // 3) Đang làm câu hỏi: không được nói/đoán đáp án
        if ($quizPending && preg_match('/(đáp án đúng|đáp án là|chọn đáp án [A-F]\b|là đáp án [A-F]\b|đáp án [A-F] (là|mới) đúng)/iu', $sentence)) {
            return 'lo_dap_an';
        }

        return null;
    }

    /** Tên AI nói khớp chủ đề thật: trùng, hoặc bắt đầu bằng tên chủ đề (AI hay nói thêm "... nhé", "... của khối 3") */
    private function isRealTopic(string $said, array $topics): bool
    {
        foreach ($topics as $topic) {
            if ($said === $topic || str_starts_with($said, $topic . ' ') || str_starts_with($said . ' ', $topic . ' ')) {
                return true;
            }
        }

        return false;
    }
}
