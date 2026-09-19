<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Phương Án / Đáp Án Lựa Chọn (QuestionOption)
 * 
 * Đại diện cho Bảng Cơ Sở Dữ Liệu: `question_options`
 * 
 * Danh sách các cột trong CSDL:
 * - `id` (int): Khóa chính, mã định danh của phương án.
 * - `question_id` (int): Khóa ngoại liên kết bảng `questions` (Câu hỏi sở hữu phương án này).
 * - `content` (text): Nội dung chữ của đáp án (hoặc chuỗi nối cặp "Vế trái ::: Vế phải").
 * - `image_path` (string): Đường dẫn hình ảnh minh họa cho đáp án này (nếu có).
 * - `is_correct` (boolean): Đánh dấu đáp án Đúng (1 = Đúng, 0 = Sai).
 * - `position` (int): Thứ tự của phương án (0 = A, 1 = B, 2 = C, 3 = D).
 * - `metadata` (array/json): Dữ liệu cấu hình nâng cao (Tọa độ Hotspot rect, vế nối left/right, các lựa chọn phân loại).
 */
class QuestionOption extends Model
{
    protected $fillable = ['question_id', 'content', 'image_path', 'is_correct', 'position', 'metadata'];

    protected $casts = ['is_correct' => 'boolean', 'metadata' => 'array'];

    /**
     * Mối quan hệ: Đáp án này thuộc về 1 Câu hỏi (Question)
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
