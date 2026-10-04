<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Giới hạn tần suất các route công khai và xác thực webhook Telegram.
 */
class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_dang_nhap_sai_lien_tuc_bi_chan_429(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->post('/dang-nhap', ['login' => 'khong-co', 'password' => 'sai'])->assertStatus(302);
        }

        $this->post('/dang-nhap', ['login' => 'khong-co', 'password' => 'sai'])->assertStatus(429);
    }

    public function test_gui_chat_qua_nhieu_bi_chan_429(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $this->postJson('/ho-tro/gui-tin-nhan', ['name' => 'A', 'message' => "tin {$i}"])->assertOk();
        }

        $this->postJson('/ho-tro/gui-tin-nhan', ['name' => 'A', 'message' => 'tin 21'])->assertStatus(429);
    }

    public function test_webhook_telegram_can_secret_token_khi_da_cau_hinh(): void
    {
        config(['services.telegram.webhook_secret' => 'bi-mat-telegram']);

        $this->postJson('/api/telegram/webhook', ['message' => ['text' => 'hi', 'chat' => ['id' => 1]]])->assertForbidden();
        $this->postJson('/api/telegram/webhook', ['callback_query' => ['id' => '1', 'data' => 'act_ord_1', 'from' => ['id' => 8952266086]]],
            ['X-Telegram-Bot-Api-Secret-Token' => 'sai'])->assertForbidden();
        $this->postJson('/api/telegram/webhook', ['update_id' => 1], ['X-Telegram-Bot-Api-Secret-Token' => 'bi-mat-telegram'])->assertOk();
    }
}
