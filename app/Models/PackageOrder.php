<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Đơn Thuê Gói Bản Quyền (PackageOrder)
 *
 * Đại diện cho bảng `package_orders` trong CSDL.
 *
 * Danh sách cột:
 * - `id` (int): Khóa chính
 * - `code` (string): Mã đơn hàng định danh (MOS-YYYYMM-XXXX)
 * - `user_id` (int): Khóa ngoại liên kết tới bảng `users` (Giáo viên đặt mua)
 * - `package_id` (int): Khóa ngoại liên kết tới bảng `packages`
 * - `package_name` (string): Tên gói tại thời điểm mua
 * - `price` (int): Giá tiền thanh toán
 * - `duration_days` (int): Số ngày sử dụng của gói
 * - `max_students` (int): Hạn mức học sinh của gói
 * - `status` (string): Trạng thái (pending, active, rejected)
 * - `payment_method` (string): Phương thức thanh toán (bank_transfer, manual...)
 * - `notes` (string|null): Ghi chú
 * - `activated_at` (datetime|null): Thời điểm kích hoạt gói
 */
class PackageOrder extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_REJECTED = 'rejected';

    public const TYPE_SUBSCRIPTION = 'subscription';
    public const TYPE_QUOTA_ADD = 'quota_add';

    protected $fillable = [
        'code',
        'user_id',
        'package_id',
        'parent_id',
        'package_name',
        'price',
        'duration_days',
        'max_students',
        'levels_snapshot',
        'order_type',
        'status',
        'payment_method',
        'notes',
        'activated_at',
    ];

    /**
     * Ép kiểu tự động của Eloquent
     */
    protected function casts(): array
    {
        return [
            'parent_id' => 'integer',
            'price' => 'integer',
            'duration_days' => 'integer',
            'max_students' => 'integer',
            'activated_at' => 'datetime',
        ];
    }

    /**
     * Lấy danh sách khối lớp hiển thị (Ưu tiên ảnh chụp lúc mua, fallback theo gói hiện tại)
     */
    public function getDisplayLevelsAttribute(): string
    {
        if (! empty($this->levels_snapshot)) {
            return $this->levels_snapshot;
        }

        if ($this->package && $this->package->relationLoaded('levels')) {
            $names = $this->package->levels->pluck('name')->all();
            return ! empty($names) ? implode(', ', $names) : 'Toàn bộ khối';
        }

        return $this->package?->levels()->pluck('name')->join(', ') ?: 'Toàn bộ khối';
    }

    /**
     * Mối quan hệ: Đơn hàng gốc của đơn điều chỉnh này (Đơn Cha)
     */
    public function parentOrder(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Mối quan hệ: Các đơn điều chỉnh / cấp bù thuộc về đơn hàng này (Các Đơn Con)
     */
    public function adjustments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Kiểm tra xem đơn hàng này có phải là đơn điều chỉnh / cấp thêm không
     */
    public function isAdjustment(): bool
    {
        return $this->order_type === self::TYPE_QUOTA_ADD || ! empty($this->parent_id);
    }

    /**
     * Mối quan hệ: Người dùng / Giáo viên đặt đơn
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mối quan hệ: Gói dịch vụ được đặt
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Scope: Lọc theo trạng thái
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Kiểm tra trạng thái
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Accessor: Giá định dạng tiền tệ VNĐ
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 0, ',', '.') . ' đ';
    }

    /**
     * Accessor: Nhãn trạng thái tiếng Việt
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'Đã kích hoạt',
            self::STATUS_REJECTED => 'Đã từ chối',
            default => 'Chờ duyệt',
        };
    }

    /**
     * Accessor: Badge màu hiển thị trạng thái
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'badge-success',
            self::STATUS_REJECTED => 'badge-danger',
            default => 'badge-warning',
        };
    }

    /**
     * Accessor: Lý do từ chối đơn hàng (nếu có trong notes)
     */
    public function getRejectionReasonAttribute(): ?string
    {
        if (preg_match('/Lý do từ chối:\s*(.*)/u', $this->notes ?? '', $matches)) {
            return trim($matches[1]);
        }
        return null;
    }

    /**
     * Accessor: Nhãn phương thức thanh toán tiếng Việt
     */
    public function getPaymentMethodLabelAttribute(): string
    {
        return match (strtolower((string) $this->payment_method)) {
            'payos' => 'Chuyển khoản QR',
            'bank_transfer', 'bank' => 'Chuyển khoản ngân hàng',
            'manual', 'admin' => 'Quản trị viên cấp',
            'momo' => 'Ví MoMo',
            'vnpay' => 'Ví VNPAY',
            default => !empty($this->payment_method) ? 'Chuyển khoản' : 'Chuyển khoản',
        };
    }

    /**
     * Accessor: Ngày tạo theo định dạng d/m/Y H:i
     */
    public function getCreatedVnAttribute(): string
    {
        $tz = config('learning.display_timezone', 'Asia/Ho_Chi_Minh');
        return $this->created_at ? $this->created_at->setTimezone($tz)->format('d/m/Y H:i') : '';
    }
}
