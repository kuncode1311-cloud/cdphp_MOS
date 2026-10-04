<?php

namespace App\Http\Controllers;

use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TelegramBotController extends Controller
{
    /**
     * Nhận webhook cập nhật từ Telegram Bot Server
     */
    public function handleWebhook(Request $request, TelegramService $telegramService): JsonResponse
    {
        // Chỉ Telegram (biết secret token đã đăng ký khi setWebhook) mới được gọi; chặn kẻ giả mạo lệnh quản trị.
        $secret = (string) config('services.telegram.webhook_secret');
        if ($secret !== '' && ! hash_equals($secret, (string) $request->header('X-Telegram-Bot-Api-Secret-Token', ''))) {
            abort(403);
        }

        // 1. Xử lý Callback Query nếu bấm nút inline
        if ($request->has('callback_query')) {
            $telegramService->handleCallbackQuery($request->input('callback_query'));
            return response()->json(['status' => 'ok']);
        }

        // 2. Xử lý Message thông thường
        $message = $request->input('message');
        if (! $message || empty($message['text'])) {
            return response()->json(['status' => 'ignored']);
        }

        $text = $message['text'];
        $chatId = (string) ($message['chat']['id'] ?? '');

        $telegramService->handleCommand($text, $chatId, $message);

        return response()->json(['status' => 'ok']);
    }
}
