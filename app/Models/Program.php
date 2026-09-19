<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Chương Trình Đào Tạo Chuẩn (Program)
 * 
 * Đại diện cho Bảng Cơ Sở Dữ Liệu: `programs`
 * 
 * Danh sách các cột trong CSDL:
 * - `id` (int): Khóa chính, mã định danh chương trình.
 * - `name` (string): Tên chương trình (Ví dụ: "IC3 GS6 Spark Primary").
 * - `slug` (string): Đường dẫn URL tĩnh (Ví dụ: "ic3-gs6-spark").
 * - `description` (text): Mô tả tổng quan về chương trình học và chuẩn đầu ra.
 * - `accent` (string): Mã màu thương hiệu chủ đạo (Ví dụ: "#5b5ce2").
 */
class Program extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'accent'];

    /**
     * Mối quan hệ: 1 Chương trình có nhiều Khối lớp (Levels: Khối 3, Khối 4, Khối 5)
     */
    public function levels(): HasMany
    {
        return $this->hasMany(Level::class)->orderBy('position');
    }
}
