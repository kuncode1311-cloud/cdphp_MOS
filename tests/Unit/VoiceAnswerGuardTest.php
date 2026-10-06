<?php

namespace Tests\Unit;

use App\Services\VoiceAnswerGuard;
use PHPUnit\Framework\TestCase;

/**
 * Bộ soát câu trả lời của AI giọng nói: chỉ bỏ câu sai lệch, giữ nguyên cách trình bày của câu đúng.
 */
class VoiceAnswerGuardTest extends TestCase
{
    public function test_keeps_small_numbers_and_formatting(): void
    {
        $out = (new VoiceAnswerGuard)->check("**Có 3 bước:**\n- Bước 1 mở máy.\n- Bước 2 đăng nhập.", [], [], false);

        $this->assertSame(0, $out['removed']);
        $this->assertSame("**Có 3 bước:**\n- Bước 1 mở máy.\n- Bước 2 đăng nhập.", $out['text']);
    }

    public function test_accepts_numbers_with_thousand_separator_when_in_data(): void
    {
        $out = (new VoiceAnswerGuard)->check('Em có 16.354 sao thưởng.', [16354], [], false);

        $this->assertSame(0, $out['removed']);
    }
}
