<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service Gửi Email Qua Brevo (Sendinblue) Transactional API v3
 * 
 * Ưu điểm vượt trội:
 * - Gửi qua giao thức HTTPS (Cổng 443) -> Không bao giờ bị Railway, AWS hay hosting chặn port SMTP 587/25.
 * - Hỗ trợ gửi ngay lập tức tới mọi hộp thư (Gmail, Outlook, Yahoo...) với tỷ lệ vào Inbox cao.
 * - Miễn phí 300 email / ngày.
 */
class BrevoMailService
{
    /**
     * Kiểm tra xem Brevo API Key đã được cấu hình hay chưa
     */
    public static function isConfigured(): bool
    {
        return ! empty(self::getApiKey());
    }

    public static function getApiKey(): ?string
    {
        $key = config('services.brevo.key');
        if (! empty($key)) {
            return (string) $key;
        }

        $envKey = env('BREVO_API_KEY');
        return ! empty($envKey) ? (string) $envKey : null;
    }

    /**
     * Gửi email giao dịch (Transactional Email) qua Brevo API
     */
    public static function send(string $toEmail, string $toName, string $subject, string $htmlContent): bool
    {
        $apiKey = self::getApiKey();
        if (empty($apiKey)) {
            Log::warning('Không tìm thấy BREVO_API_KEY trong cấu hình môi trường.');
            return false;
        }

        $senderEmail = config('services.brevo.sender_email') ?: env('BREVO_SENDER_EMAIL', env('MAIL_FROM_ADDRESS', 'kun.code.1311@gmail.com'));
        $senderName = config('services.brevo.sender_name') ?: env('MAIL_FROM_NAME', 'IC3 Adventure');

        try {
            $response = Http::withHeaders([
                'api-key' => $apiKey,
                'Content-Type' => 'application/json',
                'accept' => 'application/json',
            ])->timeout(12)->post('https://api.brevo.com/v3/smtp/email', [
                'sender' => [
                    'name' => $senderName,
                    'email' => $senderEmail,
                ],
                'to' => [
                    [
                        'email' => trim($toEmail),
                        'name' => trim($toName),
                    ]
                ],
                'subject' => $subject,
                'htmlContent' => $htmlContent,
            ]);

            if ($response->successful()) {
                $messageId = $response->json('messageId') ?? 'ok';
                Log::info("✅ [Brevo API] Đã gửi email thành công tới [{$toEmail}] - MessageId: {$messageId}");
                return true;
            }

            Log::warning("⚠️ [Brevo API] Không thể gửi email tới [{$toEmail}]: " . $response->body());
            return false;
        } catch (\Throwable $e) {
            Log::error("❌ [Brevo API] Lỗi kết nối tới Brevo API: " . $e->getMessage());
            return false;
        }
    }
}
