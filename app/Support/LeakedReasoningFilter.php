<?php

namespace App\Support;

/**
 * Lọc phần "suy nghĩ nội bộ" mà một số model (qua 9Router) lỡ ghi vào câu trả lời, thường là đoạn tiếng Anh
 * như: User said "..." In context: ... Rule: ... Natural tone. Phần này không bao giờ được hiện hay đọc to cho học sinh.
 *
 * Hai lớp: (1) lấy nội dung trong thẻ <noi>...</noi> nếu AI tuân thủ; (2) không có thẻ thì bỏ các câu tiếng Anh ở đầu,
 * chỉ giữ phần tiếng Việt đứng sau câu tiếng Anh cuối cùng.
 */
class LeakedReasoningFilter
{
    private const VIET = '/[ăâđêôơưáàảãạấầẩẫậắằẳẵặéèẻẽẹếềểễệíìỉĩịóòỏõọốồổỗộớờởỡợúùủũụứừửữựýỳỷỹỵ]/iu';
    private const ENGLISH = '/\b(the|is|this|that|user|said|rule|context|short|natural|tone|sentences?|friendly|should|will|and|of|in|with|answer|reply|spoken|teacher|vague|unclear|let me|I\'ll|need to)\b/i';

    public static function clean(string $text): string
    {
        $text = trim($text);

        // Lớp 1: AI đặt câu trả lời cuối trong thẻ <noi>
        if (preg_match_all('/<noi>(.*?)<\/noi>/su', $text, $m) && $m[1] !== []) {
            return trim(implode(' ', array_map('trim', $m[1])));
        }
        // Thẻ mở mà thiếu thẻ đóng (bị cắt giữa chừng): lấy phần sau thẻ mở
        if (preg_match('/<noi>(.*)$/su', $text, $m)) {
            return trim($m[1]);
        }

        // Lớp 2: không có thẻ. Nếu có câu tiếng Anh thì chỉ giữ phần sau câu tiếng Anh cuối cùng.
        $sentences = preg_split('/(?<=[.!?])\s*(?=\S)/u', $text) ?: [$text];
        $lastEnglish = -1;
        foreach ($sentences as $i => $sentence) {
            if (self::looksEnglish($sentence)) {
                $lastEnglish = $i;
            }
        }
        if ($lastEnglish < 0) {
            return $text;
        }

        return trim(implode(' ', array_slice($sentences, $lastEnglish + 1)));
    }

    private static function looksEnglish(string $sentence): bool
    {
        $s = trim($sentence);
        if (mb_strlen($s) < 12 || preg_match(self::VIET, $s)) {
            return false; // có dấu tiếng Việt thì không phải câu tiếng Anh
        }

        return preg_match_all(self::ENGLISH, $s) >= 2;
    }
}
