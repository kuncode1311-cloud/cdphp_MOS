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
        'user_id',
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
     * Khi xóa đoạn chat thì xóa luôn các ảnh đã gửi để không để lại file rác trên máy chủ.
     */
    protected static function booted(): void
    {
        static::deleting(function (SupportMessage $message) {
            SupportImage::where('support_message_id', $message->id)->delete();
            foreach ((array) $message->conversation_history as $turn) {
                $image = $turn['image'] ?? null;
                if (is_string($image) && str_starts_with($image, '/storage/support-chat/') && ! str_contains($image, '..')) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete(substr($image, strlen('/storage/')));
                }
            }
        });
    }

    /**
     * Chuẩn hóa số điện thoại di động Việt Nam về dạng 0901234567; không hợp lệ thì trả về null.
     */
    public static function normalizePhone(?string $raw): ?string
    {
        $digits = preg_replace('/[\s.\-()]/', '', (string) $raw);
        if (preg_match('/^(?:\+?84|0)(\d{9})$/', $digits, $m)) {
            return '0' . $m[1];
        }

        return null;
    }
    /**
     * Tài khoản đã đăng nhập khi gửi tin (nếu có)
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Xác định người gửi là khách, giáo viên hay học sinh.
     * Chỉ tin hai nguồn: tài khoản đã đăng nhập lúc gửi (user_id), hoặc email trùng khớp chính xác với một tài khoản.
     * Tuyệt đối không đoán theo tên hay số điện thoại để tránh gắn nhầm huy hiệu.
     *
     * @return array{user: ?User, type: string, label: string, account: ?array}
     */
    public function resolveSender(): array
    {
        $user = $this->user;
        if (! $user && filled($this->email)) {
            $user = User::whereRaw('LOWER(email) = ?', [mb_strtolower(trim($this->email))])->first();
        }

        if (! $user) {
            return ['user' => null, 'type' => 'guest', 'label' => '🌐 Khách Vãng Lai', 'account' => null];
        }

        $account = $user->accountSnapshot();

        return match (true) {
            $user->isTeacher() => ['user' => $user, 'type' => 'teacher', 'label' => '👨‍🏫 Giáo Viên', 'account' => $account],
            $user->isStudent() => [
                'user' => $user,
                'type' => 'student',
                'label' => $user->isIndependentStudent() ? '🎓 Học Sinh (Mua Lẻ)' : '🎓 Học Sinh',
                'account' => $account,
            ],
            default => ['user' => $user, 'type' => 'user', 'label' => '👤 Thành Viên', 'account' => $account],
        };
    }
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
    public function appendConversationTurn(string $sender, string $text, ?string $image = null, ?string $channel = null, ?array $action = null): void
    {
        $history = is_array($this->conversation_history) ? $this->conversation_history : [];
        $tz = config('learning.display_timezone', 'Asia/Ho_Chi_Minh');
        $timeStr = now()->setTimezone($tz)->format('H:i');

        // Kênh: 'ai' (Trợ lý AI) hoặc 'admin' (Ban Quản Trị). Lượt của bot luôn thuộc kênh AI.
        $channel ??= $sender === 'bot' ? 'ai' : 'admin';

        $history[] = [
            'sender' => $sender, // 'user', 'admin' hoặc 'bot'
            'channel' => $channel,
            'text' => trim($text),
            'time' => $timeStr,
            'timestamp' => now()->timestamp,
        ];
        if ($action) {
            // Nút hành động của Trợ lý AI (mở trang, làm bài): chỉ lưu nhãn và đường dẫn do máy chủ tạo
            $history[array_key_last($history)]['action'] = [
                'label' => (string) $action['label'],
                'url' => (string) $action['url'],
                'auto' => (bool) ($action['auto'] ?? false),
            ];
        }
        if ($image) {
            $history[array_key_last($history)]['image'] = $image; // Đường dẫn ảnh đã lưu trên máy chủ
        }

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
