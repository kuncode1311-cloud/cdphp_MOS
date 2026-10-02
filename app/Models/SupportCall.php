<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Nhật ký cuộc gọi tư vấn qua Stringee, kèm ghi âm để minh bạch khi hỗ trợ khách hàng.
 */
class SupportCall extends Model
{
    protected $fillable = [
        'support_message_id', 'admin_id', 'stringee_call_id', 'from_number', 'to_number',
        'status', 'end_reason', 'started_at', 'answered_at', 'ended_at', 'duration',
        'recording_url', 'recording_path', 'note',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'answered_at' => 'datetime',
        'ended_at' => 'datetime',
        'duration' => 'integer',
    ];

    public function supportMessage(): BelongsTo
    {
        return $this->belongsTo(SupportMessage::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'calling' => 'Đang gọi',
            'answered' => 'Đang đàm thoại',
            'ended' => 'Đã kết thúc',
            'missed' => 'Khách không nghe máy',
            default => 'Gọi không thành công',
        };
    }

    /**
     * Dữ liệu gọn gàng trả về cho giao diện quản trị.
     */
    public function toPanelArray(): array
    {
        $tz = config('learning.display_timezone', 'Asia/Ho_Chi_Minh');

        return [
            'id' => $this->id,
            'to_number' => $this->to_number,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'duration' => $this->duration,
            'started_at' => $this->started_at?->setTimezone($tz)->format('H:i d/m/Y'),
            'admin' => $this->admin?->name,
            'note' => $this->note,
            'has_recording' => (bool) $this->recording_path,
            'recording_url' => $this->recording_path ? route('admin.calls.recording', $this) : null,
        ];
    }
}
