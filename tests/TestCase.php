<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        // Chặn hoàn toàn các cuộc gọi ra ngoài tới Telegram API khi chạy test tự động
        Http::fake([
            'https://api.telegram.org/*' => Http::response([
                'ok' => true,
                'result' => [
                    'message_id' => 99999,
                    'chat' => ['id' => 8732001731],
                    'text' => 'Mocked test message',
                ],
            ], 200),
        ]);
    }
}
