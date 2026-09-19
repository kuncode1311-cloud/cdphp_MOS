<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Tệp Tin Đa Phương Tiện Câu Hỏi (QuestionAsset)
 * 
 * Đại diện cho Bảng Cơ Sở Dữ Liệu: `question_assets`
 * 
 * Danh sách các cột trong CSDL:
 * - `id` (int): Khóa chính, mã định danh của tệp tin.
 * - `question_id` (int): Khóa ngoại liên kết bảng `questions` (Câu hỏi đính kèm file này).
 * - `kind` (string): Loại tệp tin ('image' = Hình ảnh, 'audio' = Âm thanh, 'video' = Video, 'document' = Tài liệu).
 * - `path` (string): Đường dẫn lưu trữ file trên máy chủ (Ví dụ: "/storage/question-assets/abc.png").
 * - `original_name` (string): Tên gốc của tệp tin khi người dùng tải lên từ máy tính.
 * - `mime_type` (string): Định dạng tệp tin MIME (Ví dụ: "image/png", "image/jpeg").
 * - `metadata` (array/json): Kích thước và thông số bổ sung.
 */
class QuestionAsset extends Model
{
    protected $fillable = ['question_id', 'kind', 'path', 'original_name', 'mime_type', 'metadata'];

    protected $casts = ['metadata' => 'array'];

    /**
     * Mối quan hệ: Tệp tin đính kèm này thuộc về 1 Câu hỏi (Question)
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
