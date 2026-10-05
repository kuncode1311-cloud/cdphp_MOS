<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Bản sao bền vững (trong cơ sở dữ liệu) của tệp lưu trên disk "public".
 *
 * Ổ đĩa máy chủ Railway bị xóa mỗi lần triển khai, nên mỗi tệp tải lên / ảnh AI tạo đều được lưu thêm vào đây,
 * và được tự phục hồi ra đĩa khi có người truy cập mà tệp đã mất.
 */
class StoredFile extends Model
{
    protected $fillable = ['path', 'mime', 'size', 'data'];

    protected $hidden = ['data'];

    /** Tệp lớn hơn mức này không sao lưu vào CSDL (tránh làm nặng CSDL). */
    public const MAX_BACKUP_BYTES = 25 * 1024 * 1024;

    /** Đường dẫn tương đối trên disk public, đã loại tiền tố /storage/ và URL gốc. */
    public static function relative(string $pathOrUrl): string
    {
        $path = parse_url($pathOrUrl, PHP_URL_PATH) ?: $pathOrUrl;

        return ltrim(preg_replace('#^/?storage/#', '', ltrim($path, '/')) ?? '', '/');
    }

    /** Sao lưu một tệp đã có trên đĩa vào CSDL. Không bao giờ làm hỏng luồng chính nếu sao lưu lỗi. */
    public static function remember(string $pathOrUrl): void
    {
        try {
            $relative = self::relative($pathOrUrl);
            $disk = Storage::disk('public');
            if ($relative === '' || ! $disk->exists($relative)) {
                return;
            }
            $size = $disk->size($relative);
            if ($size > self::MAX_BACKUP_BYTES) {
                return;
            }
            self::updateOrCreate(['path' => $relative], [
                'mime' => $disk->mimeType($relative) ?: null,
                'size' => $size,
                'data' => $disk->get($relative),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /** Xóa bản sao trong CSDL khi tệp bị xóa có chủ đích. */
    public static function forget(string $pathOrUrl): void
    {
        self::where('path', self::relative($pathOrUrl))->delete();
    }

    /**
     * Đảm bảo tệp có trên đĩa: nếu đã mất (do triển khai lại) thì ghi lại từ bản sao trong CSDL.
     */
    public static function ensureOnDisk(string $pathOrUrl): bool
    {
        $relative = self::relative($pathOrUrl);
        if ($relative === '' || str_contains($relative, '..')) {
            return false;
        }
        $disk = Storage::disk('public');
        if ($disk->exists($relative)) {
            return true;
        }
        $row = self::where('path', $relative)->first();
        if (! $row) {
            return false;
        }
        $disk->put($relative, $row->data);

        return true;
    }
}
