<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Bộ Đề Thi Luyện Tập (PracticeTest)
 *
 * Đại diện cho Bảng Cơ Sở Dữ Liệu: `practice_tests`
 *
 * Danh sách các cột trong CSDL:
 * - `id` (int): Khóa chính, mã định danh bài luyện.
 * - `topic_id` (int|null): Khóa ngoại liên kết bảng `topics` (Chủ đề chứa bài luyện này, null nếu là Đề thi thử cấp Khối).
 * - `level_id` (int|null): Khóa ngoại liên kết bảng `levels` (Khối lớp trực thuộc khi là Đề thi thử).
 * - `is_mock` (boolean): Đánh dấu Đề thi thử tổng hợp cấp Khối (1 = Đề thi thử, 0 = Bài luyện chủ đề).
 * - `name` (string): Tên bài luyện thi (Ví dụ: "Bài luyện 1", "Đề thi thử 01").
 * - `slug` (string): Đường dẫn URL tĩnh (Ví dụ: "k3-cd1-bai-1").
 * - `launch_path` (string): Đường dẫn tệp bài giảng / phòng thi HTML5 tương tác.
 * - `access_code` (string): Mã bảo mật truy cập bài thi (được mã hóa).
 * - `resource_manifest` (array/json): Danh mục các tệp hình ảnh, âm thanh của bài thi.
 * - `duration_minutes` (int): Thời lượng làm bài thi tính bằng phút (Mặc định: 20 phút, thi thử 45 phút).
 * - `question_count` (int): Tổng số câu hỏi của bộ đề (Ví dụ: 14 câu, thi thử 30 câu).
 * - `pass_score` (int): Điểm chuẩn đạt của bài thi (Mặc định: 700 / 1000).
 * - `max_score` (int): Điểm tối đa có thể đạt được (1000 điểm).
 * - `is_published` (boolean): Trạng thái mở phát hành cho học sinh (1 = Đang mở, 0 = Ẩn).
 * - `position` (int): Thứ tự hiển thị trong chủ đề.
 */
class PracticeTest extends Model
{
    protected $fillable = [
        'topic_id',
        'level_id',
        'is_mock',
        'name',
        'slug',
        'access_code',
        'duration_minutes',
        'question_count',
        'pass_score',
        'max_score',
        'is_published',
        'shuffle_questions',
        'shuffle_options',
        'position',
    ];

    // Ẩn mã khi xuất JSON; cast encrypted bên dưới mã hóa khi lưu và giải mã khi đọc.
    protected $hidden = ['access_code', 'launch_path', 'resource_manifest'];

    protected $casts = [
        'is_mock' => 'boolean',
        'is_published' => 'boolean',
        'shuffle_questions' => 'boolean',
        'shuffle_options' => 'boolean',
        'access_code' => 'encrypted',
    ];

    /**
     * Mối quan hệ: Bộ đề này thuộc về 1 Chủ đề bài học (Topic) (nếu là bài luyện thường)
     */
    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    /**
     * Mối quan hệ: Bộ đề thi thử thuộc về 1 Khối lớp (Level)
     */
    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    /**
     * Mối quan hệ Many-to-Many: Câu hỏi của đề thi thử (qua bảng pivot practice_test_questions)
     */
    public function mockQuestions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'practice_test_questions')
            ->withPivot('position')
            ->orderBy('practice_test_questions.position');
    }

    /**
     * Mối quan hệ: Lấy danh sách câu hỏi của bộ đề.
     * Tự động nhận diện nếu là Đề thi thử (Many-to-Many) hoặc Bài luyện đơn (One-to-Many).
     */
    public function questions(): HasMany|BelongsToMany
    {
        if ($this->is_mock) {
            return $this->mockQuestions();
        }

        return $this->hasMany(Question::class)->orderBy('position');
    }

    /**
     * Scope: Lọc các bộ đề thi thử cấp Khối
     */
    public function scopeMockTests($query)
    {
        return $query->where('is_mock', true);
    }

    /**
     * Scope: Lọc các bài luyện chuẩn theo chủ đề
     */
    public function scopeStandardTests($query)
    {
        return $query->where('is_mock', false);
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
