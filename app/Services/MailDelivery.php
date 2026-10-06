<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Gửi email giao dịch dùng chung: ưu tiên Brevo API (máy chủ thường chặn cổng SMTP), lỗi thì dùng SMTP.
 * Chỉ trả true khi thư thật sự được gửi đi; mailer "log"/"array" chỉ ghi lại nên tính là chưa gửi.
 * Log chỉ ghi loại lỗi và mã tài khoản, không ghi nội dung thư hay địa chỉ email.
 */
class MailDelivery
{
    public static function send(User $user, Mailable $mail, string $kind): bool
    {
        if (! $user->hasDeliverableEmail()) {
            Log::info("MailDelivery: tài khoản #{$user->id} không có email nhận thư, bỏ qua email {$kind}.");

            return false;
        }

        if (BrevoMailService::isConfigured()) {
            try {
                if (BrevoMailService::send($user->email, $user->name, (string) $mail->envelope()->subject, $mail->render())) {
                    return true;
                }
            } catch (\Throwable $e) {
                Log::warning("MailDelivery: gửi email {$kind} qua Brevo API thất bại (" . $e::class . '), thử SMTP.');
            }
        }

        $mailer = (string) config('mail.default');
        if (in_array($mailer, ['log', 'array'], true)) {
            Log::error("MailDelivery: chưa cấu hình gửi email thật (Brevo API lỗi/thiếu, MAIL_MAILER={$mailer}), không gửi được email {$kind} cho tài khoản #{$user->id}");

            return false;
        }

        try {
            Mail::to($user->email)->send($mail);

            return true;
        } catch (\Throwable $e) {
            Log::error("MailDelivery: gửi email {$kind} thất bại (" . $e::class . ") cho tài khoản #{$user->id}");

            return false;
        }
    }
}
