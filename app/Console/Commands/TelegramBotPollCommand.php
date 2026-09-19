<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class TelegramBotPollCommand extends Command
{
    /**
     * Tên và cú pháp lệnh artisan
     */
    protected $signature = 'telegram:poll {--once : Chỉ lấy cập nhật 1 lần rồi thoát}';

    /**
     * Mô tả lệnh
     */
    protected $description = 'Lắng nghe và tự động phản hồi các tương tác nút bấm, callback và lệnh từ Telegram Bot';

    public function handle(TelegramService $telegramService): int
    {
        $botToken = config('services.telegram.bot_token');
        if (empty($botToken)) {
            $this->error('Chưa cấu hình TELEGRAM_BOT_TOKEN trong file .env');
            return 1;
        }

        $this->info("🤖 Đang khởi động Telegram Bot Polling (Token: " . substr($botToken, 0, 8) . "...)...");

        // Đăng ký menu commands chính thức với Telegram API
        $registered = $telegramService->registerBotCommands();
        if ($registered) {
            $this->info("✅ Đã đồng bộ danh sách Bot Commands lên Telegram API.");
        }

        $this->info("⚡ Đã kích hoạt hệ thống Nút bấm tương tác 1 chạm (Interactive Buttons & Mini-App)");
        $this->info("Nhấn Ctrl+C để dừng.\n");

        // 1. Kiểm tra chống chạy song song nhiều tiến trình (Single Instance Guard)
        $myPid = getmypid();
        $activePid = Cache::get('telegram_poll_active_pid');
        if ($activePid && $activePid !== $myPid) {
            $isAlive = false;
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $check = @shell_exec("tasklist /FI \"PID eq {$activePid}\" /NH");
                if ($check && str_contains($check, 'php.exe')) {
                    $isAlive = true;
                }
            }
            if ($isAlive) {
                $this->warn("⚠️ Đang có tiến trình bot khác (PID: {$activePid}) đang chạy. Tự động thoát để tránh trùng lặp tin nhắn!");
                return 0;
            }
        }
        Cache::put('telegram_poll_active_pid', $myPid, 30);

        $offset = 0;
        $isOnce = $this->option('once');

        while (true) {
            try {
                // Heartbeat để báo cho Web AJAX biết daemon đang hoạt động, không cần tranh chấp getUpdates
                Cache::put('telegram_poll_daemon_active', true, 15);

                $response = Http::timeout(10)->get("https://api.telegram.org/bot{$botToken}/getUpdates", [
                    'offset' => $offset,
                    'timeout' => 5,
                ]);

                if ($response->successful()) {
                    $updates = $response->json('result', []);
                    foreach ($updates as $update) {
                        $updateId = (int) ($update['update_id'] ?? 0);
                        $offset = max($offset, $updateId + 1);

                        // Khóa chống xử lý trùng lặp update_id (Deduplication Lock)
                        if ($updateId > 0) {
                            if (! Cache::add("tele_up_done_{$updateId}", true, 60)) {
                                continue;
                            }
                        }

                        // 1. Xử lý khi người dùng BẤM NÚT INLINE (Callback Query)
                        if (! empty($update['callback_query'])) {
                            $cb = $update['callback_query'];
                            $fromUser = $cb['from']['first_name'] ?? 'User';
                            $actionData = $cb['data'] ?? '';
                            $this->line("<fg=yellow>[Nút bấm Inline từ {$fromUser}]:</> <fg=magenta>{$actionData}</>");

                            $telegramService->handleCallbackQuery($cb);
                            continue;
                        }

                        // 2. Xử lý khi người dùng GỬI TIN NHẮN hoặc BẤM NÚT BÀN PHÍM DƯỚI ĐÁY
                        $message = $update['message'] ?? null;
                        if (! $message || empty($message['text'])) {
                            continue;
                        }

                        $text = trim($message['text']);
                        $chatId = (string) ($message['chat']['id'] ?? '');
                        $fromName = $message['from']['first_name'] ?? 'User';

                        $this->line("<fg=cyan>[Tin nhắn từ {$fromName} ({$chatId})]:</> {$text}");

                        // Kiểm tra nếu admin đang Reply một tin nhắn cụ thể trên Telegram
                        $replyTo = $message['reply_to_message'] ?? null;
                        $isExplicitCustomerReply = false;
                        $replyContent = $text;

                        if (! empty($replyTo)) {
                            // Admin dùng tính năng Swipe / Right-click Reply trên Telegram
                            $isExplicitCustomerReply = true;
                        } elseif (preg_match('/^(?:rep|tl|khach|chat)\s*[:\-]\s*(.+)$/ui', $text, $matches)) {
                            // Admin gõ cú pháp "rep: Chào thầy, ..."
                            $isExplicitCustomerReply = true;
                            $replyContent = trim($matches[1]);
                        }

                        if ($isExplicitCustomerReply) {
                            $supportMsg = \App\Models\SupportMessage::where('status', 'pending')->latest('id')->first();
                            if ($supportMsg) {
                                if (empty($supportMsg->admin_reply)) {
                                    $supportMsg->admin_reply = $replyContent;
                                } else {
                                    $supportMsg->admin_reply .= "\n" . $replyContent;
                                }
                                $supportMsg->status = 'responded';
                                $supportMsg->replied_at = now();
                                $supportMsg->save();

                                $confirm = "✅ <b>ĐÃ CHUYỂN TIẾP TỚI KHÁCH TRÊN WEBSITE!</b>\n";
                                $confirm .= "━━━━━━━━━━━━━━━━━━━━\n";
                                $confirm .= "👤 <b>Khách hàng:</b> " . htmlspecialchars($supportMsg->name) . "\n";
                                $confirm .= "💬 <b>Nội dung gửi:</b> <i>" . htmlspecialchars($replyContent) . "</i>";
                                $telegramService->sendMessage($confirm, null, $chatId);
                                $this->line("<fg=green>[Đã chuyển tiếp tới khách]:</> {$supportMsg->name}: {$replyContent}\n");
                            } else {
                                $telegramService->sendMessage("ℹ️ Hiện không có câu hỏi tư vấn nào đang chờ phản hồi từ khách.", null, $chatId);
                            }
                        } else {
                            // Mọi tin nhắn lệnh, bấm nút menu, tra cứu -> chuyển tới handleCommand
                            $telegramService->handleCommand($text, $chatId);
                            $this->line("<fg=green>[Bot phản hồi lệnh/menu]:</> {$text}\n");
                        }
                    }
                }
            } catch (\Throwable $e) {
                $this->warn("Lỗi kết nối Telegram: " . $e->getMessage());
                sleep(2);
            }

            if ($isOnce) {
                break;
            }

            usleep(500000); // 0.5s
        }

        return 0;
    }
}
