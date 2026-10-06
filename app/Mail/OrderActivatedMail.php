<?php

namespace App\Mail;

use App\Models\PackageOrder;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email xác nhận đơn hàng đã kích hoạt thành công: mã đơn, gói, số tiền, hạn dùng và thông tin đăng nhập.
 * Không gửi mật khẩu (hệ thống chỉ lưu mật khẩu đã mã hóa); hướng dẫn dùng mã đơn để lấy lại tài khoản khi cần.
 */
class OrderActivatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public PackageOrder $order, public User $user)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '✅ [IC3 Adventure] Đơn hàng ' . $this->order->code . ' đã kích hoạt thành công',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-activated',
            with: [
                'order' => $this->order,
                'user' => $this->user,
                'tz' => (string) config('learning.display_timezone', 'Asia/Ho_Chi_Minh'),
            ],
        );
    }
}
