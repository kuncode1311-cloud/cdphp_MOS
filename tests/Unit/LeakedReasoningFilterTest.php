<?php

namespace Tests\Unit;

use App\Support\LeakedReasoningFilter;
use PHPUnit\Framework\TestCase;

/**
 * Phần "suy nghĩ nội bộ" bằng tiếng Anh mà model lỡ ghi vào câu trả lời không được hiện hay đọc to cho học sinh.
 */
class LeakedReasoningFilterTest extends TestCase
{
    public function test_real_leaked_reply_is_cleaned(): void
    {
        $leaked = 'User said "lúc đầu" (at first / originally / earlier). In context: this is vague/unclear. '
            . 'Rule: "Nếu người nói chỉ chào hoặc nói chưa rõ ý, hãy hỏi lại một câu ngắn thân thiện.". '
            . '"Không dùng ký hiệu đặc biệt, markdown, gạch đầu dòng, biểu tượng cảm xúc, đường dẫn.". '
            . '1-3 short spoken sentences. Natural tone. Friendly AI teacher.'
            . 'Ý em là lúc đầu thế nào, em nói rõ hơn cho cô nghe với nhé.';

        $clean = LeakedReasoningFilter::clean($leaked);

        $this->assertStringNotContainsString('User said', $clean);
        $this->assertStringNotContainsString('Rule', $clean);
        $this->assertStringNotContainsString('Natural tone', $clean);
        $this->assertStringContainsString('Ý em là lúc đầu thế nào', $clean);
    }

    public function test_answer_inside_noi_tags_is_extracted(): void
    {
        $this->assertSame(
            'Chào em, cô nghe đây.',
            LeakedReasoningFilter::clean('Thinking about the user greeting. <noi>Chào em, cô nghe đây.</noi> trailing junk')
        );
    }

    public function test_unclosed_tag_keeps_text_after_it(): void
    {
        $this->assertSame('Chào em nhé', LeakedReasoningFilter::clean('The user greets. <noi>Chào em nhé'));
    }

    public function test_normal_vietnamese_reply_is_unchanged(): void
    {
        $reply = 'Em đã làm ba bài luyện tập rồi. Điểm cao nhất là chín trăm điểm. Em giỏi lắm!';

        $this->assertSame($reply, LeakedReasoningFilter::clean($reply));
    }

    public function test_vietnamese_sentence_with_a_few_english_terms_is_kept(): void
    {
        $reply = 'Gmail là một dịch vụ email của Google. Em có thể dùng Chrome hoặc Edge để mở nhé.';

        $this->assertSame($reply, LeakedReasoningFilter::clean($reply));
    }

    public function test_only_english_reasoning_leaves_nothing(): void
    {
        $this->assertSame('', LeakedReasoningFilter::clean('The user said hello. This is a short friendly reply with a natural tone.'));
    }
}
