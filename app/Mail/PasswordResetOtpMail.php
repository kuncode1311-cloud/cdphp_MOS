<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Mailable Gửi Mã OTP Xác Thực Đổi Mật Khẩu
 */
class PasswordResetOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $otp,
        public User $user
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🛡️ [IC3 Adventure] Mã OTP Xác Thực Đổi Mật Khẩu: ' . $this->otp,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password-otp',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
