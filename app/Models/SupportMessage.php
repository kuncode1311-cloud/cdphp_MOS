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
        'admin_reply',
        'replied_at',
        'status',
        'ip_address',
    ];

    /**
     * Thuộc tính tự động ép kiểu
     */
    protected $casts = [
        'replied_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope lấy tin nhắn chờ xử lý
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
