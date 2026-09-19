<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Kết Quả Bài Làm Của Học Sinh (TestAttempt)
 * 
 * Đại diện cho Bảng Cơ Sở Dữ Liệu: `test_attempts`
 * 
 * Danh sách các cột trong CSDL:
 * - `id` (int): Khóa chính, mã định danh của lượt làm bài.
 * - `user_id` (int): Khóa ngoại liên kết với bảng `users` (Học sinh thực hiện bài thi).
 * - `practice_test_id` (int): Khóa ngoại liên kết với bảng `practice_tests` (Bộ đề thi được làm).
 * - `score` (int): Điểm số đạt được (quy đổi theo thang điểm chuẩn 1000).
 * - `correct_answers` (int): Số lượng câu hỏi trả lời chính xác (Ví dụ: 12).
 * - `total_questions` (int): Tổng số câu hỏi của bộ đề (Ví dụ: 14).
 * - `duration_seconds` (int): Tổng thời gian làm bài thực tế tính bằng giây (Ví dụ: 689 giây).
 * - `completed_at` (datetime): Thời điểm học sinh bấm nộp bài và hoàn thành.
 * - `created_at` / `updated_at`: Ngày giờ hệ thống tự động ghi nhận.
 */
class TestAttempt extends Model
{
    protected $fillable = [
        'user_id',
        'practice_test_id',
        'score',
        'correct_answers',
        'total_questions',
        'duration_seconds',
        'completed_at'
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'correct_answers' => 'integer',
            'total_questions' => 'integer',
            'duration_seconds' => 'integer',
            'completed_at' => 'datetime'
        ];
    }

    /**
     * Mối quan hệ: Lượt thi này thuộc về 1 Học sinh (User)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mối quan hệ: Lượt thi này thuộc về 1 Bộ đề thi luyện tập (PracticeTest)
     */
    public function practiceTest(): BelongsTo
    {
        return $this->belongsTo(PracticeTest::class);
    }

    /**
     * Accessor: Lấy thời gian hoàn thành theo múi giờ Việt Nam (d/m/Y H:i)
     */
    public function getCompletedAtVnAttribute(): string
    {
        $tz = config('learning.display_timezone', 'Asia/Ho_Chi_Minh');
        $date = $this->completed_at ?? $this->created_at;

        return $date ? $date->setTimezone($tz)->format('d/m/Y H:i') : '';
    }

    /**
     * Accessor: Lấy ngày hoàn thành bài thi theo múi giờ Việt Nam (d/m/Y)
     */
    public function getCompletedDateVnAttribute(): string
    {
        $tz = config('learning.display_timezone', 'Asia/Ho_Chi_Minh');
        $date = $this->completed_at ?? $this->created_at;

        return $date ? $date->setTimezone($tz)->format('d/m/Y') : '';
    }

    /**
     * Accessor: Lấy thời gian hoàn thành đầy đủ theo múi giờ Việt Nam (d/m/Y H:i:s)
     */
    public function getCompletedAtFullVnAttribute(): string
    {
        $tz = config('learning.display_timezone', 'Asia/Ho_Chi_Minh');
        $date = $this->completed_at ?? $this->created_at;

        return $date ? $date->setTimezone($tz)->format('d/m/Y H:i:s') : '';
    }
}
