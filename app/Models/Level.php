<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Khối Lớp / Cấp Độ (Level)
 * 
 * Đại diện cho Bảng Cơ Sở Dữ Liệu: `levels`
 * 
 * Danh sách các cột trong CSDL:
 * - `id` (int): Khóa chính, mã định danh khối lớp.
 * - `program_id` (int): Khóa ngoại liên kết bảng `programs` (Chương trình đào tạo chứa khối này).
 * - `name` (string): Tên đầy đủ của khối (Ví dụ: "IC3 GS6 Spark Level 1 — Khối 3").
 * - `slug` (string): Đường dẫn URL tĩnh (Ví dụ: "khoi-3-spark-level-1").
 * - `grade` (int): Số khối lớp (3, 4, 5).
 * - `position` (int): Thứ tự hiển thị.
 */
class Level extends Model
{
    protected $fillable = ['program_id', 'name', 'slug', 'grade', 'position'];

    /**
     * Mối quan hệ: Khối lớp thuộc về 1 Chương trình (Program)
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Mối quan hệ: 1 Khối lớp có nhiều Chủ đề bài học (Topics)
     */
    public function topics(): HasMany
    {
        return $this->hasMany(Topic::class)->orderBy('position');
    }

    /**
     * Mối quan hệ: Các Học sinh được cấp quyền học Khối lớp này
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'level_user')->withTimestamps();
    }

    /**
     * Mối quan hệ: Các Giáo viên được Admin cấp quyền sử dụng Khối lớp này
     */
    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'teacher_level', 'level_id', 'teacher_id')->withTimestamps();
    }

    /**
     * Mối quan hệ: Các Gói dịch vụ áp dụng cho Khối lớp này
     */
    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(Package::class, 'package_level', 'level_id', 'package_id')->withTimestamps();
    }

    /**
     * Tùy biến truy vấn Route Model Binding để hỗ trợ cả dấu gạch dưới _, dấu cách, viết hoa/thường
     */
    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        $field = $field ?? $this->getRouteKeyName();

        if ($field === 'slug') {
            $cleaned = strtolower(trim((string) $value));
            $normalized = str_replace(['_', ' ', '.'], '-', $cleaned);

            return $query->where(function ($q) use ($value, $cleaned, $normalized) {
                $q->where('slug', $value)
                  ->orWhere('slug', $cleaned)
                  ->orWhere('slug', $normalized);
            });
        }

        return parent::resolveRouteBindingQuery($query, $value, $field);
    }
}
