<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * Ảnh gửi trong chat hỗ trợ, lưu trong cơ sở dữ liệu (không phụ thuộc ổ đĩa máy chủ).
 */
class SupportImage extends Model
{
    protected $fillable = ['support_message_id', 'token', 'mime', 'size', 'data'];

    protected $hidden = ['data'];

    public const URL_PREFIX = '/ho-tro/anh/';

    public static function storeUpload(UploadedFile $file, ?int $supportMessageId): self
    {
        $bytes = file_get_contents($file->getRealPath());

        return self::create([
            'support_message_id' => $supportMessageId,
            'token' => Str::random(48),
            'mime' => $file->getMimeType() ?: 'image/png',
            'size' => strlen($bytes),
            'data' => $bytes,
        ]);
    }

    public function url(): string
    {
        return self::URL_PREFIX.$this->token;
    }
}
