<?php

namespace App\Services;

use App\Models\Question;
use App\Models\QuestionAsset;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Class QuestionAssetService
 *
 * Tầng Service quản lý vòng đời tệp tin đa phương tiện (Ảnh minh họa, âm thanh, v.v.) của câu hỏi.
 * Đảm bảo lưu trữ an toàn, sinh đường dẫn URL chuẩn và tự động dọn dẹp file rác khi xóa.
 */
class QuestionAssetService
{
    /**
     * Tải lên và liên kết tệp tin mới với câu hỏi
     *
     * @param  string  $kind  Loại tệp: image, audio, video, document
     */
    public function uploadAsset(Question $question, UploadedFile $file, string $kind = 'image'): QuestionAsset
    {
        // Tự động nhận diện loại tệp chính xác theo định dạng file
        $ext = strtolower($file->getClientOriginalExtension());
        if (in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'])) {
            $kind = 'image';
        } elseif (in_array($ext, ['mp3', 'wav', 'ogg', 'm4a', 'aac'])) {
            $kind = 'audio';
        } elseif (in_array($ext, ['mp4', 'webm', 'mov', 'avi'])) {
            $kind = 'video';
        } elseif (in_array($ext, ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt'])) {
            $kind = 'document';
        }

        // 1. Lưu file vào thư mục public disk (storage/app/public/question-assets)
        $path = $file->store('question-assets', 'public');

        // 2. Tạo bản ghi trong bảng question_assets
        return $question->assets()->create([
            'kind' => $kind,
            'path' => Storage::url($path),
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'metadata' => [
                'size' => $file->getSize(),
                'extension' => $file->getClientOriginalExtension(),
            ],
        ]);
    }

    /**
     * Xóa bản ghi tài nguyên và dọn dẹp file vật lý trên đĩa cứng
     */
    public function deleteAsset(QuestionAsset $asset): bool
    {
        // Kiểm tra và xóa file thực tế trên disk
        // Chỉ đường dẫn /storage/ được xử lý xóa file ở đây; tài nguyên legacy không vào nhánh này.
        if (Str::startsWith($asset->path, '/storage/')) {
            $relativeDiskPath = Str::after($asset->path, '/storage/');
            if (Storage::disk('public')->exists($relativeDiskPath)) {
                Storage::disk('public')->delete($relativeDiskPath);
            }
        }

        // Xóa bản ghi trong database
        return (bool) $asset->delete();
    }
}
