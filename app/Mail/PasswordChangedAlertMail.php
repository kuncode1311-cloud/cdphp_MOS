<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Mailable Gửi Email Cảnh Báo Khi Mật Khẩu Được Thay Đổi Thành Công
 */
class PasswordChangedAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $changedAt,
        public ?string $ipAddress = null,
        public ?string $userAgent = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🛡️ [Cảnh báo bảo mật] Mật khẩu tài khoản IC3 Adventure vừa được thay đổi',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password-changed',
            with: [
                'user' => $this->user,
                'changedAt' => $this->changedAt,
                'ipAddress' => $this->ipAddress ?? 'Không xác định',
                'userAgent' => $this->userAgent ?? 'Trình duyệt Web',
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
