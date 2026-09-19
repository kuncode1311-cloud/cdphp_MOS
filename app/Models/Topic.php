<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Chủ Đề Bài Học (Topic)
 * 
 * Đại diện cho Bảng Cơ Sở Dữ Liệu: `topics`
 * 
 * Danh sách các cột trong CSDL:
 * - `id` (int): Khóa chính, mã định danh chủ đề.
 * - `level_id` (int): Khóa ngoại liên kết bảng `levels` (Khối lớp chứa chủ đề này).
 * - `name` (string): Tên chủ đề (Ví dụ: "Căn bản về công nghệ", "Ứng dụng then chốt", "Cuộc sống trực tuyến").
 * - `slug` (string): Đường dẫn URL tĩnh (Ví dụ: "chu-de-1").
 * - `description` (text): Mô tả nội dung bài học của chủ đề.
 * - `icon` (string): Mã biểu tượng hiển thị (Ví dụ: "monitor", "sparkles").
 * - `position` (int): Thứ tự hiển thị của chủ đề trong khối lớp.
 */
class Topic extends Model
{
    protected $fillable = ['level_id', 'name', 'slug', 'description', 'icon', 'position'];

    /**
     * Mối quan hệ: Chủ đề này thuộc về 1 Khối lớp (Level)
     */
    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    /**
     * Mối quan hệ: 1 Chủ đề có nhiều Bộ đề thi luyện tập (PracticeTests)
     */
    public function tests(): HasMany
    {
        return $this->hasMany(PracticeTest::class)->orderBy('position');
    }
}
