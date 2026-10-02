<?php

namespace App\Services;

use App\Models\SupportCall;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Tích hợp tổng đài Stringee: cấp token cho Web SDK, tạo kịch bản cuộc gọi (SCCO)
 * có ghi âm và tải file ghi âm về máy chủ để lưu minh bạch.
 */
class StringeeService
{
    public function isConfigured(): bool
    {
        return config('services.stringee.key_sid') !== ''
            && config('services.stringee.key_secret') !== ''
            && config('services.stringee.from_number') !== '';
    }

    public function fromNumber(): string
    {
        return $this->normalizePhone((string) config('services.stringee.from_number')) ?? '';
    }

    /**
     * Chuẩn hóa số điện thoại Việt Nam về dạng 84xxxxxxxxx; trả về null nếu không hợp lệ.
     */
    public function normalizePhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);
        if (str_starts_with($digits, '84')) {
            $digits = substr($digits, 2);
        } elseif (str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        return preg_match('/^[1-9]\d{8,9}$/', $digits) ? '84' . $digits : null;
    }

    /**
     * Token đăng nhập Stringee Web SDK (client) hoặc gọi REST API.
     */
    public function makeToken(string $userId, bool $restApi = false, int $ttl = 3600): string
    {
        $sid = config('services.stringee.key_sid');
        $now = time();
        $header = ['typ' => 'JWT', 'alg' => 'HS256', 'cty' => 'stringee-api;v=1'];
        $payload = [
            'jti' => $sid . '-' . $now . '-' . bin2hex(random_bytes(4)),
            'iss' => $sid,
            'exp' => $now + $ttl,
        ];
        if ($restApi) {
            $payload['rest_api'] = true;
        } else {
            $payload['userId'] = $userId;
        }

        $encode = fn (array $d) => rtrim(strtr(base64_encode(json_encode($d)), '+/', '-_'), '=');
        $unsigned = $encode($header) . '.' . $encode($payload);
        $signature = rtrim(strtr(base64_encode(
            hash_hmac('sha256', $unsigned, config('services.stringee.key_secret'), true)
        ), '+/', '-_'), '=');

        return $unsigned . '.' . $signature;
    }

    /**
     * Chữ ký đính kèm vào URL webhook để chỉ Stringee (nơi biết URL) mới gọi được.
     */
    public function signature(string $purpose): string
    {
        return hash_hmac('sha256', 'stringee-' . $purpose, (string) config('services.stringee.key_secret'));
    }

    public function verifySignature(string $purpose, ?string $sig): bool
    {
        return $this->isConfigured() && is_string($sig) && hash_equals($this->signature($purpose), $sig);
    }

    public function webhookUrl(string $purpose): string
    {
        $base = rtrim((string) config('services.stringee.webhook_base'), '/');

        return "{$base}/api/stringee/{$purpose}?sig=" . $this->signature($purpose);
    }

    /**
     * Kịch bản SCCO: ghi âm (nếu bật) rồi nối máy tới số điện thoại khách.
     */
    public function buildScco(string $toNumber, string $customData = ''): array
    {
        $from = $this->fromNumber();
        $scco = [];

        if (config('services.stringee.record')) {
            $scco[] = [
                'action' => 'record',
                'eventUrl' => $this->webhookUrl('event'),
                'format' => 'mp3',
            ];
        }

        $scco[] = [
            'action' => 'connect',
            'from' => ['type' => 'external', 'number' => $from, 'alias' => $from],
            'to' => ['type' => 'external', 'number' => $toNumber, 'alias' => $toNumber],
            'customData' => $customData,
            'timeout' => 45,
            'maxConnectTime' => -1,
            'peerToPeerCall' => false,
        ];

        return $scco;
    }

    /**
     * Tải file ghi âm từ Stringee về storage riêng tư. Trả về true nếu đã có file.
     */
    public function downloadRecording(SupportCall $call): bool
    {
        if ($call->recording_path && Storage::disk('local')->exists($call->recording_path)) {
            return true;
        }
        if (! $call->stringee_call_id || ! $this->isConfigured()) {
            return false;
        }

        try {
            $base = rtrim(config('services.stringee.api_base'), '/');
            $url = $call->recording_url ?: "{$base}/call/recording/{$call->stringee_call_id}";
            $response = Http::timeout(30)
                ->withHeaders(['X-STRINGEE-AUTH' => $this->makeToken('', true)])
                ->get($url);

            if (! $response->successful() || strlen($response->body()) < 1024) {
                return false;
            }

            $path = 'call-recordings/' . now()->format('Y/m') . "/call-{$call->id}.mp3";
            Storage::disk('local')->put($path, $response->body());
            $call->update(['recording_path' => $path]);

            return true;
        } catch (\Throwable $e) {
            Log::warning('Stringee: không tải được ghi âm', ['call' => $call->id, 'error' => $e->getMessage()]);

            return false;
        }
    }
}
