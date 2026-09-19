<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Lớp Học (Classroom)
 * 
 * Đại diện cho Bảng Cơ Sở Dữ Liệu: `classrooms`
 * 
 * Danh sách các cột trong CSDL:
 * - `id` (int): Khóa chính, mã định danh lớp học.
 * - `name` (string): Tên lớp học (Ví dụ: "Lớp 3A1", "Lớp 4A2").
 * - `grade` (int): Khối lớp (3, 4, 5).
 * - `school_year` (string): Niên khóa học tập (Ví dụ: "2026-2027").
 * - `teacher_id` (int): Khóa ngoại liên kết bảng `users` (Giáo viên chủ nhiệm/quản lý lớp).
 * - `created_at` / `updated_at`: Thời gian tạo và cập nhật lớp học.
 */
class Classroom extends Model
{
    protected $fillable = ['name', 'grade', 'school_year', 'teacher_id'];

    protected function casts(): array
    {
        return ['grade' => 'integer'];
    }

    /**
     * Mối quan hệ: Lớp học có 1 Giáo viên quản lý (User)
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Mối quan hệ: Lớp học có nhiều Học sinh (Users với role='student')
     */
    public function students(): HasMany
    {
        return $this->hasMany(User::class)->where('role', UserRole::Student->value);
    }

    /**
     * Scope lọc lớp học theo khối (Grade 3, 4, 5)
     */
    public function scopeForGrade(Builder $query, int $grade): Builder
    {
        return $query->where('grade', $grade);
    }
}
