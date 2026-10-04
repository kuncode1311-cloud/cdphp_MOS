<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Đăng ký webhook Telegram kèm secret token để chỉ Telegram mới gọi được /api/telegram/webhook.
 */
class TelegramSetWebhook extends Command
{
    protected $signature = 'telegram:set-webhook';

    protected $description = 'Đăng ký webhook Telegram với secret token (TELEGRAM_WEBHOOK_SECRET)';

    public function handle(): int
    {
        $token = (string) config('services.telegram.bot_token');
        $secret = (string) config('services.telegram.webhook_secret');
        if ($token === '' || $secret === '') {
            $this->error('Thiếu TELEGRAM_BOT_TOKEN hoặc TELEGRAM_WEBHOOK_SECRET trong .env.');

            return self::FAILURE;
        }

        $url = rtrim((string) config('app.url'), '/') . '/api/telegram/webhook';
        $res = Http::asForm()->post("https://api.telegram.org/bot{$token}/setWebhook", [
            'url' => $url,
            'secret_token' => $secret,
        ]);

        $this->line($res->json('description') ?? $res->body());

        return $res->json('ok') ? self::SUCCESS : self::FAILURE;
    }
}