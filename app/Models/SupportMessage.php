<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model Tin nhắn Tư vấn & Hỗ trợ Khách hàng (SupportMessage)
 * 
 * Đại diện cho các tin nhắn được gửi từ Widget Chat trực tiếp trên website,
 * được tự động chuyển tiếp thông báo tới Telegram Bot của Ban Quản Trị.
 */
class SupportMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'message',
        'conversation_history',
        'admin_reply',
        'replied_at',
        'status',
        'ip_address',
    ];

    /**
     * Thuộc tính tự động ép kiểu
     */
    protected $casts = [
        'conversation_history' => 'array',
        'replied_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Các cuộc gọi tư vấn (có ghi âm) đã thực hiện cho đoạn chat này
     */
    public function calls(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SupportCall::class);
    }

    /**
     * Thêm một lượt tin nhắn vào luồng hội thoại đa chiều
     */
    public function appendConversationTurn(string $sender, string $text): void
    {
        $history = is_array($this->conversation_history) ? $this->conversation_history : [];
        $tz = config('learning.display_timezone', 'Asia/Ho_Chi_Minh');
        $timeStr = now()->setTimezone($tz)->format('H:i');

        $history[] = [
            'sender' => $sender, // 'user' hoặc 'admin'
            'text' => trim($text),
            'time' => $timeStr,
            'timestamp' => now()->timestamp,
        ];

        $this->conversation_history = $history;
    }

    /**
     * Scope lấy tin nhắn chờ xử lý
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
