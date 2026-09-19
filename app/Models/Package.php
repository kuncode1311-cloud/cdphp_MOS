<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Model Gói Dịch Vụ / Bản Quyền IC3 (Package)
 *
 * Đại diện cho bảng `packages` trong CSDL.
 *
 * Danh sách cột:
 * - `id` (int): Khóa chính
 * - `name` (string): Tên gói dịch vụ
 * - `slug` (string): Định danh URL duy nhất
 * - `badge` (string|null): Nhãn nổi bật (Phổ biến nhất, Tiết kiệm...)
 * - `description` (string|null): Mô tả ngắn
 * - `price` (int): Giá bán thực tế (VNĐ)
 * - `original_price` (int|null): Giá gốc chưa giảm
 * - `duration_days` (int): Thời hạn gói (ngày)
 * - `max_students` (int): Hạn mức học sinh quản lý (0 = không giới hạn)
 * - `features` (array|null): Danh sách tính năng nổi bật
 * - `is_active` (bool): Đang mở bán hay tạm ẩn
 * - `sort_order` (int): Thứ tự ưu tiên hiển thị
 */
class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'badge',
        'description',
        'price',
        'original_price',
        'duration_days',
        'max_students',
        'features',
        'is_active',
        'sort_order',
    ];

    /**
     * Ép kiểu tự động của Eloquent
     */
    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'original_price' => 'integer',
            'duration_days' => 'integer',
            'max_students' => 'integer',
            'features' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Tự động sinh slug nếu chưa có khi tạo gói
     */
    protected static function booted(): void
    {
        static::creating(function (Package $package) {
            if (empty($package->slug)) {
                $baseSlug = Str::slug($package->name);
                $slug = $baseSlug;
                $counter = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = "{$baseSlug}-{$counter}";
                    $counter++;
                }
                $package->slug = $slug;
            }
        });
    }

    /**
     * Mối quan hệ: Gói cấp quyền cho những Khối lớp nào (Levels)
     */
    public function levels(): BelongsToMany
    {
        return $this->belongsToMany(Level::class, 'package_level', 'package_id', 'level_id')->withTimestamps();
    }

    /**
     * Mối quan hệ: Danh sách đơn hàng đã mua gói này
     */
    public function orders(): HasMany
    {
        return $this->hasMany(PackageOrder::class);
    }

    /**
     * Scope: Lấy các gói đang mở bán
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Sắp xếp theo thứ tự hiển thị
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('price');
    }

    /**
     * Accessor: Giá định dạng tiền tệ VNĐ (vd: "990.000 đ")
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 0, ',', '.') . ' đ';
    }

    /**
     * Accessor: Giá gốc định dạng VNĐ (nếu có)
     */
    public function getFormattedOriginalPriceAttribute(): ?string
    {
        return $this->original_price ? number_format($this->original_price, 0, ',', '.') . ' đ' : null;
    }

    /**
     * Accessor: Hiển thị thời hạn theo tiếng Việt dễ hiểu
     */
    public function getDurationTextAttribute(): string
    {
        if ($this->duration_days >= 365) {
            $years = round($this->duration_days / 365, 1);
            return $years == 1 ? '1 năm học' : "{$years} năm";
        }

        if ($this->duration_days >= 30) {
            $months = round($this->duration_days / 30);
            return "{$months} tháng ({$this->duration_days} ngày)";
        }

        return "{$this->duration_days} ngày";
    }

    /**
     * Accessor: Hiển thị số lượng học sinh được cấp
     */
    public function getMaxStudentsTextAttribute(): string
    {
        return $this->max_students > 0 ? "Tối đa {$this->max_students} học sinh" : 'Không giới hạn học sinh';
    }

    /**
     * Accessor: Danh sách tên các khối lớp áp dụng (vd: "Khối 3, Khối 4")
     */
    public function getLevelsListTextAttribute(): string
    {
        if ($this->relationLoaded('levels')) {
            if ($this->levels->isEmpty()) {
                return 'Tất cả các khối lớp';
            }
            return $this->levels->pluck('name')->join(', ');
        }

        $levels = $this->levels()->get();
        return $levels->isEmpty() ? 'Tất cả các khối lớp' : $levels->pluck('name')->join(', ');
    }

    /**
     * Accessor: Tên khối lớp ngắn gọn, thân thiện và đồng bộ giao diện bảng giá
     */
    public function getShortLevelsTextAttribute(): string
    {
        $levels = $this->relationLoaded('levels') ? $this->levels : $this->levels()->get();
        if ($levels->isEmpty()) {
            return 'Toàn bộ Khối 3, 4, 5';
        }

        $grades = $levels->pluck('grade')->filter()->sort()->values();
        if ($grades->count() >= 3 || $grades->all() === [3, 4, 5]) {
            return 'Toàn bộ Khối 3, 4, 5';
        }

        if ($grades->count() === 2) {
            return "Khối {$grades[0]} & {$grades[1]}";
        }

        if ($grades->count() === 1) {
            $g = $grades[0];
            $lvl = $g == 3 ? 1 : ($g == 4 ? 2 : ($g == 5 ? 3 : $g));
            return "Khối {$g} (Level {$lvl})";
        }

        return $levels->pluck('name')->join(', ');
    }
}
