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

        $telegramService->handleCommand($text, $chatId);

        return response()->json(['status' => 'ok']);
    }
}
