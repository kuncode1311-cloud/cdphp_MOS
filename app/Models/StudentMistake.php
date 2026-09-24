<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Sổ Tay Câu Sai (StudentMistake)
 *
 * Đại diện cho Bảng: `student_mistakes`
 *
 * Danh sách cột:
 * - `id` (int): Khóa chính
 * - `user_id` (int): Khóa ngoại học sinh
 * - `question_id` (int): Khóa ngoại câu hỏi làm sai
 * - `practice_test_id` (int|null): Khóa ngoại đề thi làm sai gần nhất
 * - `wrong_count` (int): Số lần làm sai lũy kế
 * - `correct_count` (int): Số lần làm đúng lại trong chế độ phục thù
 * - `last_student_answer` (array|json): Đáp án học sinh đã chọn lần sai gần nhất
 * - `status` (string): 'unresolved' (chưa khắc phục) | 'resolved' (đã phục thù thành công)
 * - `last_wrong_at` (datetime): Thời điểm làm sai gần nhất
 * - `last_resolved_at` (datetime|null): Thời điểm làm đúng lại gần nhất
 */
class StudentMistake extends Model
{
    protected $fillable = [
        'user_id',
        'question_id',
        'practice_test_id',
        'wrong_count',
        'correct_count',
        'last_student_answer',
        'status',
        'last_wrong_at',
        'last_resolved_at',
    ];

    protected $casts = [
        'last_student_answer' => 'array',
        'wrong_count' => 'integer',
        'correct_count' => 'integer',
        'last_wrong_at' => 'datetime',
        'last_resolved_at' => 'datetime',
    ];

    /**
     * Thuộc về 1 Học sinh (User)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Thuộc về 1 Câu hỏi (Question)
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Thuộc về 1 Bài luyện / Đề thi (PracticeTest)
     */
    public function practiceTest(): BelongsTo
    {
        return $this->belongsTo(PracticeTest::class);
    }

    /**
     * Scope: Lọc các câu chưa sửa được
     */
    public function scopeUnresolved($query)
    {
        return $query->where('status', 'unresolved');
    }

    /**
     * Scope: Lọc các câu đã phục thù thành công
     */
    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    /**
     * Scope: Lọc các câu sai nhiều lần (nguy hiểm >= 2 lần)
     */
    public function scopeHighRisk($query, int $threshold = 2)
    {
        return $query->where('wrong_count', '>=', $threshold);
    }
}
