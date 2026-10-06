<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Chuẩn hóa chữ tiếng Việt để so sánh: chữ thường, bỏ dấu, chỉ giữ chữ và số.
 * Chỉ dùng để SO KHỚP dữ liệu (tên chủ đề, tên học sinh, số liệu); việc hiểu ý câu nói do AI đảm nhận.
 */
class VietText
{
    public static function norm(string $text): string
    {
        $ascii = Str::ascii(mb_strtolower($text));

        return trim(preg_replace('/\s+/', ' ', preg_replace('/[^a-z0-9 ]+/', ' ', $ascii)));
    }
}
