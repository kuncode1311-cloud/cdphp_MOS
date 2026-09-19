<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Lịch sử Giao dịch Game & Điểm thưởng (GameTransaction)
 *
 * Lưu lại mọi biến động về Sao thưởng và Thời gian chơi của học sinh:
 * - `type`: 'earn' (làm bài thi), 'exchange' (đổi gói), 'play' (chơi game), 'admin_adjust' (quản trị viên cộng/trừ)
 * - `stars_change`: Biến động số sao (+/-)
 * - `time_seconds_change`: Biến động thời gian chơi (+/- giây)
 */
class GameTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'stars_change',
        'time_seconds_change',
        'description',
    ];

    protected $casts = [
        'stars_change' => 'integer',
        'time_seconds_change' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accessor: Lấy thời gian tạo theo múi giờ Việt Nam (Asia/Ho_Chi_Minh) dạng ngắn: HH:mm DD/MM
     * Giúp hiển thị đúng giờ Việt Nam dù CSDL lưu chuẩn UTC.
     */
    public function getCreatedAtVnAttribute(): string
    {
        $tz = config('learning.display_timezone', 'Asia/Ho_Chi_Minh');

        return $this->created_at ? $this->created_at->setTimezone($tz)->format('H:i d/m') : '';
    }

    /**
     * Accessor: Lấy thời gian tạo theo múi giờ Việt Nam đầy đủ: HH:mm:ss DD/MM/YYYY
     */
    public function getCreatedAtFullVnAttribute(): string
    {
        $tz = config('learning.display_timezone', 'Asia/Ho_Chi_Minh');

        return $this->created_at ? $this->created_at->setTimezone($tz)->format('H:i:s d/m/Y') : '';
    }
}
