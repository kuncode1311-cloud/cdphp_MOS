<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Câu Hỏi Đề Thi (Question)
 *
 * Đại diện cho Bảng Cơ Sở Dữ Liệu: `questions`
 *
 * Danh sách các cột trong CSDL:
 * - `id` (int): Khóa chính, mã định danh câu hỏi.
 * - `practice_test_id` (int): Khóa ngoại liên kết bảng `practice_tests` (Bộ đề thi chứa câu hỏi này).
 * - `external_id` (string): Mã định danh gốc từ file bài giảng iSpring/IC3.
 * - `type` (string): Loại câu hỏi ('MultipleChoice', 'MultipleResponse', 'Matching', 'MultipleChoiceText', 'Hotspot', 'Sequence').
 * - `title` (text): Nội dung câu hỏi / Đề bài hiển thị cho học sinh.
 * - `raw_payload` (array/json): Cấu trúc dữ liệu JSON đầy đủ của câu hỏi (chuẩn tương thích Game Synth).
 * - `position` (int): Thứ tự của câu hỏi trong đề thi (Câu 1, Câu 2, Câu 3...).
 * - `points` (int): Điểm số của câu hỏi.
 * - `is_published` (boolean): Trạng thái mở câu hỏi (1 = Đang mở, 0 = Ẩn).
 */
class Question extends Model
{
    protected $hidden = ['external_id'];

    public function getRawPayloadAttribute(): null
    {
        return null;
    }

    protected $fillable = ['practice_test_id', 'type', 'title', 'configuration', 'position', 'points', 'is_published'];

    protected $casts = ['configuration' => 'array', 'is_published' => 'boolean'];

    /**
     * Mối quan hệ: Câu hỏi này thuộc về 1 Bộ đề thi (PracticeTest)
     */
    public function practiceTest(): BelongsTo
    {
        return $this->belongsTo(PracticeTest::class);
    }

    /**
     * Mối quan hệ: 1 Câu hỏi có nhiều Phương án lựa chọn (QuestionOptions)
     */
    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class)->orderBy('position');
    }

    /**
     * Mối quan hệ: 1 Câu hỏi có nhiều Tệp tin đa phương tiện (QuestionAssets)
     */
    public function assets(): HasMany
    {
        return $this->hasMany(QuestionAsset::class);
    }

    /** Dữ liệu an toàn cho runtime; không mang raw_payload hay đáp án đúng ra trình duyệt. */
    public function runtimeData(): array
    {
        $this->loadMissing('options', 'assets');

        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'points' => $this->points,
            'configuration' => $this->configuration ?? [],
            'assets' => $this->assets->map(fn ($asset) => ['kind' => $asset->kind, 'path' => $asset->path])->values(),
            'options' => $this->options->map(fn ($option) => [
                'position' => $option->position,
                'content' => $option->content,
                'image_path' => $option->image_path,
                'metadata' => collect($option->metadata ?? [])->only([
                    'left', 'right', 'rect', 'available_options', 'correct_position',
                ])->all(),
            ])->values(),
        ];
    }

    /** DTO dành cho Studio, gồm đáp án để quản trị viên chỉnh sửa. */
    public function studioData(): array
    {
        $this->loadMissing('options', 'assets');

        return [
            'id' => $this->id,
            'practice_test_id' => $this->practice_test_id,
            'type' => $this->type,
            'title' => $this->title,
            'configuration' => $this->configuration ?? [],
            'position' => $this->position,
            'points' => $this->points,
            'is_published' => $this->is_published,
            'parsed_options' => $this->parsed_options,
            'assets' => $this->assets->map(fn ($asset) => [
                'id' => $asset->id,
                'kind' => $asset->kind,
                'path' => $asset->path,
                'original_name' => $asset->original_name,
                'mime_type' => $asset->mime_type,
                'metadata' => $asset->metadata,
            ])->values(),
        ];
    }

    /**
     * Tự động trích xuất danh sách options chuẩn hóa cho cả câu hỏi mới lẫn câu legacy IC3
     */
    public function getParsedOptionsAttribute(): array
    {
        if ($this->options->isNotEmpty()) {
            $rawAreas = [];
            $rawImg = ($this->type === 'Hotspot') ? ($this->configuration['image_path'] ?? null) : null;
            $assetImg = ($this->type === 'Hotspot') ? ($this->relationLoaded('assets') ? $this->assets->first()?->path : $this->assets()->first()?->path) : null;

            return $this->options->map(function ($opt, $idx) use ($rawAreas, $rawImg, $assetImg) {
                $left = $opt->metadata['left'] ?? null;
                $right = $opt->metadata['right'] ?? null;
                if ($this->type === 'Matching' && ! $left && ! $right && str_contains($opt->content, ':::')) {
                    [$left, $right] = explode(':::', $opt->content, 2);
                    $left = trim($left);
                    $right = trim($right);
                }

                $rect = $opt->metadata['rect'] ?? ($rawAreas[$idx]['r'] ?? null);
                $imgPath = $opt->image_path ?: ($rawImg ?: $assetImg);

                return [
                    'id' => $opt->id,
                    'content' => $opt->content,
                    'left' => $left,
                    'right' => $right,
                    'is_correct' => (bool) $opt->is_correct,
                    'image_path' => $imgPath,
                    'rect' => $rect,
                    'available_options' => $opt->metadata['available_options'] ?? null,
                ];
            })->toArray();
        }

        return [];
    }

    /**
     * Bóc tách chuỗi văn bản sạch từ cấu trúc JSON lồng nhau của IC3
     */
    protected function extractNodeText(mixed $node): string
    {
        if (is_string($node)) {
            return trim(strip_tags($node));
        }
        if (is_array($node)) {
            if (! empty($node['d'][0])) {
                return trim(strip_tags($node['d'][0]));
            }
            if (! empty($node['t'])) {
                return $this->extractNodeText($node['t']);
            }
            if (! empty($node['a'])) {
                return trim(strip_tags($node['a']));
            }
            if (! empty($node['h'])) {
                return trim(strip_tags($node['h']));
            }
        }

        return '';
    }
}
