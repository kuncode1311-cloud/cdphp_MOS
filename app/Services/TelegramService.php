<?php

namespace App\Services;

use App\Models\Classroom;
use App\Models\Package;
use App\Models\PackageOrder;
use App\Models\Question;
use App\Models\SupportMessage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service tích hợp Telegram Bot thông minh (Interactive Telegram Mini-App Service)
 * 
 * Tính năng hiện đại:
 * 1. Persistent Menu Keyboard: Các nút bấm vĩnh viễn ở thanh công cụ dưới đáy chat.
 * 2. Inline Interactive Keyboards: Các nút bấm phản hồi tức thời dưới từng tin nhắn.
 * 3. Callback Query Handlers: Xử lý duyệt đơn ngay lập tức, chuyển tab doanh thu, phản hồi nhanh.
 * 4. Đồng bộ 2 chiều: Nhận tin nhắn chat, đẩy tin nhắn tư vấn từ web về Telegram và ngược lại.
 * 5. Tự động đăng ký Bot Commands chính thức lên Telegram API.
 */
class TelegramService
{
    protected string $botToken;
    protected string $adminChatId;

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token', '');
        $this->adminChatId = config('services.telegram.admin_chat_id', '');
    }

    /**
     * Kiểm tra cấu hình Telegram Bot đã sẵn sàng
     */
    public function isConfigured(): bool
    {
        return ! empty($this->botToken) && ! empty($this->adminChatId);
    }

    /**
     * Danh sách Chat ID được cấp quyền Quản trị viên (Chỉ lấy đúng Chat ID được cấu hình)
     */
    public function getAdminChatIds(): array
    {
        $raw = (string) $this->adminChatId;
        if (empty($raw)) {
            return [];
        }

        $ids = array_filter(array_map('trim', explode(',', $raw)));
        return array_values(array_unique($ids));
    }

    /**
     * Kiểm tra một Chat ID có quyền Quản trị viên hay không
     */
    public function isAdminChat(?string $chatId): bool
    {
        if (empty($chatId)) {
            return false;
        }

        $adminIds = $this->getAdminChatIds();
        if (empty($adminIds)) {
            return false;
        }

        return in_array((string) $chatId, $adminIds, true);
    }

    /**
     * Menu lời chào công khai dành cho khách hoặc người dùng thông thường
     */
    public function buildPublicWelcomeMessage(): array
    {
        $text = "👋 <b>Chào mừng bạn đến với Hệ thống Luyện thi IC3 Quest!</b>\n";
        $text .= "━━━━━━━━━━━━━━━━━━━━\n";
        $text .= "Nền tảng học tập, rèn luyện kỹ năng số và ôn luyện chứng chỉ tin học quốc tế IC3 GS6 dành cho học sinh tiểu học.\n\n";
        $text .= "🌐 <b>Website chính thức:</b> https://mos.app\n";
        $text .= "💎 <b>Gói dịch vụ & Bảng giá:</b> Hỗ trợ đầy đủ các khối lớp 3, 4, 5\n";
        $text .= "📞 <b>Hotline / Zalo tư vấn:</b> <code>0345151438</code>\n";
        $text .= "━━━━━━━━━━━━━━━━━━━━\n";
        $text .= "<i>💡 Nếu bạn là Quản trị viên, vui lòng sử dụng tài khoản Telegram được cấp quyền để truy cập Trung tâm điều hành.</i>";

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '🌐 Khám Phá Website IC3 Quest', 'url' => config('app.url', 'https://mos.app')],
                    ['text' => '💎 Xem Bảng Giá Các Gói', 'callback_data' => 'cmd_banggia']
                ],
                [
                    ['text' => '💬 Nhắn Tin Tư Vấn Zalo', 'url' => 'https://zalo.me/0345151438']
                ]
            ]
        ];

        return [
            'text' => $text,
            'keyboard' => $keyboard
        ];
    }

    /**
     * Bàn phím bấm cố định (Persistent Reply Keyboard) ở đáy màn hình Telegram
     */
    public function getMainPersistentKeyboard(): array
    {
        return [
            'keyboard' => [
                [
                    ['text' => '📊 Báo Cáo Doanh Thu'],
                    ['text' => '⏳ Đơn Chờ Duyệt']
                ],
                [
                    ['text' => '💬 Hộp Thư Tư Vấn'],
                    ['text' => '👥 Thống Kê Học Sinh']
                ],
                [
                    ['text' => '💎 Bảng Giá Các Gói'],
                    ['text' => '⚡ Mã QR Thanh Toán']
                ],
                [
                    ['text' => '❓ Menu & Hướng Dẫn']
                ]
            ],
            'resize_keyboard' => true,
            'is_persistent' => true
        ];
    }

    /**
     * Đăng ký danh sách Commands chính thức lên Telegram (để nút Menu [/] hiển thị chuẩn)
     */
    public function registerBotCommands(): bool
    {
        if (empty($this->botToken)) return false;

        $commands = [
            ['command' => 'start', 'description' => '🚀 Mở menu điều hành IC3 Quest'],
            ['command' => 'doanhthu', 'description' => '📊 Báo cáo doanh thu & đơn thành công'],
            ['command' => 'choduyet', 'description' => '⏳ Danh sách đơn hàng chờ duyệt'],
            ['command' => 'danhsachchat', 'description' => '💬 Tin nhắn tư vấn khách hàng'],
            ['command' => 'thongke', 'description' => '👥 Thống kê học sinh, GV, lớp học'],
            ['command' => 'banggia', 'description' => '💎 Bảng giá các gói bản quyền'],
            ['command' => 'qrtest', 'description' => '⚡ Thông tin chuyển khoản & mã QR'],
            ['command' => 'trogiup', 'description' => '❓ Xem hướng dẫn sử dụng bot'],
        ];

        try {
            $response = Http::timeout(6)->post("https://api.telegram.org/bot{$this->botToken}/setMyCommands", [
                'commands' => json_encode($commands)
            ]);
            return $response->successful() && $response->json('ok');
        } catch (\Throwable $e) {
            Log::warning('Telegram setMyCommands error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Phản hồi Callback Query (hiển thị popup toast trên điện thoại/máy tính admin)
     */
    public function answerCallbackQuery(string $callbackQueryId, string $text = '', bool $showAlert = false): bool
    {
        if (empty($this->botToken)) return false;

        try {
            $response = Http::timeout(5)->post("https://api.telegram.org/bot{$this->botToken}/answerCallbackQuery", [
                'callback_query_id' => $callbackQueryId,
                'text' => $text,
                'show_alert' => $showAlert
            ]);
            return $response->successful();
        } catch (\Throwable $e) {
            Log::warning('Telegram answerCallbackQuery error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Cập nhật nội dung tin nhắn đã gửi (Edit Message Text in-place)
     */
    public function editMessageText(string $chatId, int $messageId, string $text, ?array $replyMarkup = null): bool
    {
        if (empty($this->botToken)) return false;

        try {
            $payload = [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => $text,
                'parse_mode' => 'HTML',
            ];

            if ($replyMarkup !== null) {
                $payload['reply_markup'] = json_encode($replyMarkup);
            }

            $response = Http::timeout(6)->post("https://api.telegram.org/bot{$this->botToken}/editMessageText", $payload);
            return $response->successful();
        } catch (\Throwable $e) {
            Log::warning('Telegram editMessageText error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Gửi tin nhắn thông báo có Đơn Thuê Gói Mới (Kèm nút bấm inline thao tác ngay)
     */
    public function sendOrderNotification(PackageOrder $order): bool
    {
        if (! $this->isConfigured()) return false;

        $user = $order->user;
        $priceFormatted = number_format($order->price) . ' đ';
        $timeStr = $order->created_at ? $order->created_at->format('d/m/Y H:i:s') : date('d/m/Y H:i:s');
        $payMethod = $order->payment_method === 'payos' ? 'Quét mã VietQR tự động' : 'Chuyển khoản trực tiếp (MB Bank)';

        // Trích xuất SĐT và Trường học (từ user hoặc từ notes)
        $phone = $user?->phone;
        if (! $phone && $order->notes && preg_match('/(?:SĐT|Điện thoại):\s*([0-9\+\s]+)/iu', $order->notes, $mP)) {
            $phone = trim($mP[1]);
        }

        $school = $user?->school_name;
        if (! $school && $order->notes && preg_match('/(?:Trường|Đơn vị|Trường\/Đơn vị):\s*([^·\n]+)/iu', $order->notes, $mS)) {
            $school = trim($mS[1]);
        }

        $accountStatus = 'Tài khoản đang hoạt động';
        if ($user?->status === 'pending') {
            $accountStatus = '🆕 Đăng ký mới (Chờ kích hoạt)';
        } elseif (! $user) {
            $accountStatus = 'Khách vãng lai';
        }

        $currentStudents = $user?->max_students ?: 0;
        $currentExpiry = $user?->expires_at ? $user->expires_at->format('d/m/Y') : 'Chưa kích hoạt';

        $text = "💎 <b>[IC3 QUEST] CÓ ĐƠN THUÊ GÓI BẢN QUYỀN MỚI!</b>\n";
        $text .= "━━━━━━━━━━━━━━━━━━━━\n";
        $text .= "🏷️ <b>Mã đơn hàng:</b> <code>#{$order->code}</code> (ID: {$order->id})\n";
        $text .= "👩‍🏫 <b>Giáo viên:</b> <b>" . htmlspecialchars($user?->name ?? 'Khách') . "</b>\n";
        $text .= "📧 <b>Email:</b> <code>" . htmlspecialchars($user?->email ?? 'N/A') . "</code>\n";
        if ($phone) {
            $text .= "📱 <b>Số điện thoại (Zalo):</b> <code>" . htmlspecialchars($phone) . "</code>\n";
        }
        if ($school) {
            $text .= "🏫 <b>Trường học / Đơn vị:</b> <b>" . htmlspecialchars($school) . "</b>\n";
        }
        $text .= "👤 <b>Hồ sơ tài khoản:</b> {$accountStatus}\n";
        $text .= "   • <i>Sĩ số hiện tại:</i> {$currentStudents} HS\n";
        $text .= "   • <i>Hạn dùng hiện tại:</i> {$currentExpiry}\n";
        $text .= "━━━━━━━━━━━━━━━━━━━━\n";
        $text .= "📦 <b>Gói đăng ký:</b> <b>" . htmlspecialchars($order->package_name) . "</b>\n";
        $text .= "💰 <b>Số tiền thanh toán:</b> <b>{$priceFormatted}</b>\n";
        $text .= "⏰ <b>Thời hạn gói:</b> +{$order->duration_days} ngày\n";
        $text .= "👥 <b>Sĩ số quản lý:</b> Tối đa {$order->max_students} học sinh\n";
        $text .= "💳 <b>Phương thức:</b> {$payMethod}\n";
        $text .= "⏱️ <b>Thời gian tạo:</b> {$timeStr}\n";
        if ($order->notes) {
            $text .= "📝 <b>Ghi chú:</b> <i>" . htmlspecialchars($order->notes) . "</i>\n";
        }
        $text .= "━━━━━━━━━━━━━━━━━━━━\n";
        $text .= "⚡ <i>Bấm nút bên dưới để Duyệt kích hoạt ngay hoặc liên hệ Thầy/Cô:</i>";

        $cleanPhone = $phone ? preg_replace('/[^0-9]/', '', $phone) : null;
        $inlineButtons = [
            [
                ['text' => "⚡ Duyệt Kích Hoạt #{$order->id}", 'callback_data' => "act_ord_{$order->id}"],
                ['text' => "❌ Hủy Đơn", 'callback_data' => "rej_ord_{$order->id}"]
            ]
        ];

        if ($cleanPhone) {
            $inlineButtons[] = [
                ['text' => "💬 Nhắn Zalo ({$cleanPhone})", 'url' => "https://zalo.me/{$cleanPhone}"],
                ['text' => "📞 Gọi Khách", 'url' => "tel:{$cleanPhone}"]
            ];
        }

        $inlineButtons[] = [
            ['text' => "🔍 Chi Tiết Đơn", 'callback_data' => "view_ord_{$order->id}"],
            ['text' => '🌐 Mở Web Quản Trị', 'url' => url('/quan-tri#tab-packages')]
        ];

        return $this->sendMessage($text, ['inline_keyboard' => $inlineButtons]);
    }

    /**
     * Gửi tin nhắn thông báo Đơn Hàng Đã Kích Hoạt Thành Công
     */
    public function sendPaymentSuccessNotification(PackageOrder $order): bool
    {
        if (! $this->isConfigured()) return false;

        $user = $order->user;
        $priceFormatted = number_format($order->price) . ' đ';

        $text = "✅ <b>[IC3 QUEST] ĐÃ KÍCH HOẠT BẢN QUYỀN THÀNH CÔNG!</b>\n";
        $text .= "━━━━━━━━━━━━━━━━━━━━\n";
        $text .= "🏷️ <b>Mã đơn:</b> <code>#{$order->code}</code> (ID: {$order->id})\n";
        $text .= "👩‍🏫 <b>Giáo viên:</b> <b>" . htmlspecialchars($user?->name ?? 'N/A') . "</b>\n";
        $text .= "📦 <b>Gói dịch vụ:</b> " . htmlspecialchars($order->package_name) . "\n";
        $text .= "💰 <b>Số tiền thanh toán:</b> <b>{$priceFormatted}</b>\n";
        $text .= "🔑 <b>Trạng thái tài khoản:</b> Đã gia hạn thành công (Quản lý {$order->max_students} học sinh)\n";
        $text .= "━━━━━━━━━━━━━━━━━━━━\n";
        $text .= "🚀 <i>Hệ thống đã tự động mở khóa các khối lớp học cho giáo viên!</i>";

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '📊 Xem Doanh Thu', 'callback_data' => 'cmd_doanhthu'],
                    ['text' => '🌐 Mở Trang Quản Trị', 'url' => url('/quan-tri#tab-packages')]
                ]
            ]
        ];

        return $this->sendMessage($text, $keyboard);
    }

    /**
     * Gửi tin nhắn tư vấn từ Live Chat Messenger Widget (Kèm nút Zalo & Đánh dấu xử lý)
     */
    public function sendSupportMessageNotification(string $name, ?string $phone, ?string $email, string $message, ?int $msgId = null): bool
    {
        if (! $this->isConfigured()) return false;

        $text = "💬 <b>[IC3 QUEST] CÓ TIN NHẮN TƯ VẤN MỚI TỪ WEBSITE!</b>\n";
        $text .= "━━━━━━━━━━━━━━━━━━━━\n";
        $text .= "👤 <b>Họ tên:</b> <b>" . htmlspecialchars($name) . "</b>\n";
        if ($phone) {
            $text .= "📞 <b>Số điện thoại:</b> <code>" . htmlspecialchars($phone) . "</code>\n";
        }
        if ($email) {
            $text .= "📧 <b>Email:</b> " . htmlspecialchars($email) . "\n";
        }
        $text .= "📝 <b>Nội dung câu hỏi:</b>\n<i>" . htmlspecialchars($message) . "</i>\n";
        $text .= "━━━━━━━━━━━━━━━━━━━━\n";
        $text .= "⚡ <i>Bấm phản hồi nhanh hoặc gõ trực tiếp tin nhắn ở đây để bot chuyển tiếp về web cho khách!</i>";

        $cleanPhone = $phone ? preg_replace('/[^0-9]/', '', $phone) : null;
        $inlineButtons = [];

        if ($cleanPhone) {
            $inlineButtons[] = [
                ['text' => "💬 Mở Chat Zalo ({$cleanPhone})", 'url' => "https://zalo.me/{$cleanPhone}"]
            ];
        }

        $rowActions = [];
        if ($msgId) {
            $rowActions[] = ['text' => '✅ Đã Tư Vấn Xong', 'callback_data' => "done_chat_{$msgId}"];
        }
        $rowActions[] = ['text' => '💬 Danh Sách Chat', 'callback_data' => 'cmd_danhsachchat'];
        $inlineButtons[] = $rowActions;

        $inlineButtons[] = [
            ['text' => '🌐 Mở Live Chat Quản Trị', 'url' => url('/quan-tri#tab-messenger')]
        ];

        return $this->sendMessage($text, ['inline_keyboard' => $inlineButtons]);
    }

    /**
     * Xử lý khi Admin bấm nút Inline Keyboard (Callback Query)
     */
    public function handleCallbackQuery(array $callbackQuery): void
    {
        $callbackId = (string) ($callbackQuery['id'] ?? '');
        $data = (string) ($callbackQuery['data'] ?? '');
        $chatId = (string) ($callbackQuery['message']['chat']['id'] ?? '');
        $fromId = (string) ($callbackQuery['from']['id'] ?? $chatId);
        $messageId = (int) ($callbackQuery['message']['message_id'] ?? 0);

        if (empty($callbackId) || empty($data)) {
            return;
        }

        // Nút xem bảng giá công khai: Cho phép tất cả mọi người bấm xem
        if ($data === 'cmd_banggia') {
            $this->answerCallbackQuery($callbackId, "💎 Đang tải Bảng giá...");
            $res = $this->buildPricingMessage();
            if ($messageId > 0 && ! empty($chatId)) {
                $this->editMessageText($chatId, $messageId, $res['text'], $res['keyboard']);
            } elseif (! empty($chatId)) {
                $this->sendMessage($res['text'], $res['keyboard'], $chatId);
            }
            return;
        }

        // BẢO VỆ BẢO MẬT: Kiểm tra quyền Admin cho TẤT CẢ các thao tác quản trị khác
        if (! $this->isAdminChat($fromId) && ! $this->isAdminChat($chatId)) {
            $this->answerCallbackQuery($callbackId, "⛔ Bạn không có quyền thực hiện thao tác quản trị này!", true);
            return;
        }

        // 1. Duyệt đơn hàng: act_ord_{id}
        if (str_starts_with($data, 'act_ord_')) {
            $orderId = (int) substr($data, 8);
            $order = PackageOrder::find($orderId);

            if (! $order) {
                $this->answerCallbackQuery($callbackId, "❌ Không tìm thấy đơn hàng ID: #{$orderId}", true);
                return;
            }

            if ($order->isActive()) {
                $this->answerCallbackQuery($callbackId, "ℹ️ Đơn hàng #{$order->code} đã được kích hoạt trước đó rồi!", true);
                return;
            }

            $subscriptionService = app(SubscriptionService::class);
            $success = $subscriptionService->activateOrder($order);

            if ($success) {
                $this->answerCallbackQuery($callbackId, "🎉 Kích hoạt thành công đơn #{$order->code} cho giáo viên!", true);

                // Cập nhật thông điệp xác nhận
                $confirmedText = "✅ <b>ĐÃ KÍCH HOẠT THÀNH CÔNG ĐƠN HÀNG #{$order->code} (ID: {$order->id})!</b>\n";
                $confirmedText .= "━━━━━━━━━━━━━━━━━━━━\n";
                $confirmedText .= "👩‍🏫 <b>Giáo viên:</b> " . htmlspecialchars($order->user?->name ?? 'N/A') . "\n";
                $confirmedText .= "📦 <b>Gói dịch vụ:</b> " . htmlspecialchars($order->package_name) . "\n";
                $confirmedText .= "💰 <b>Số tiền:</b> " . number_format($order->price) . " đ\n";
                $confirmedText .= "⏰ <b>Thời hạn:</b> {$order->duration_days} ngày\n";
                $confirmedText .= "👥 <b>Sĩ số:</b> Tối đa {$order->max_students} học sinh\n";
                $confirmedText .= "━━━━━━━━━━━━━━━━━━━━\n";
                $confirmedText .= "⚡ <i>Tài khoản giáo viên đã được nâng cấp và mở khóa đầy đủ trên hệ thống.</i>";

                $keyboard = [
                    'inline_keyboard' => [
                        [
                            ['text' => '📊 Xem Doanh Thu', 'callback_data' => 'cmd_doanhthu'],
                            ['text' => '⏳ Đơn Chờ Khác', 'callback_data' => 'cmd_choduyet']
                        ],
                        [
                            ['text' => '🌐 Mở Trang Quản Trị', 'url' => url('/quan-tri#tab-packages')]
                        ]
                    ]
                ];

                if ($messageId > 0) {
                    $this->editMessageText($chatId, $messageId, $confirmedText, $keyboard);
                } else {
                    $this->sendMessage($confirmedText, $keyboard, $chatId);
                }
            } else {
                $this->answerCallbackQuery($callbackId, "❌ Kích hoạt đơn thất bại. Vui lòng kiểm tra lại!", true);
            }
            return;
        }

        // 2. Từ chối đơn hàng: rej_ord_{id}
        if (str_starts_with($data, 'rej_ord_')) {
            $orderId = (int) substr($data, 8);
            $order = PackageOrder::find($orderId);

            if (! $order) {
                $this->answerCallbackQuery($callbackId, "❌ Không tìm thấy đơn hàng ID: #{$orderId}", true);
                return;
            }

            $subscriptionService = app(SubscriptionService::class);
            $subscriptionService->rejectOrder($order, 'Admin từ chối qua Telegram Bot');

            $this->answerCallbackQuery($callbackId, "⚠️ Đã từ chối đơn hàng #{$order->code}!", true);

            $rejectText = "❌ <b>ĐÃ TỪ CHỐI ĐƠN HÀNG #{$order->code} (ID: {$order->id})</b>\n";
            $rejectText .= "━━━━━━━━━━━━━━━━━━━━\n";
            $rejectText .= "👩‍🏫 <b>Giáo viên:</b> " . htmlspecialchars($order->user?->name ?? 'N/A') . "\n";
            $rejectText .= "📦 <b>Gói dịch vụ:</b> " . htmlspecialchars($order->package_name) . "\n";
            $rejectText .= "📌 <b>Trạng thái:</b> Đã hủy / từ chối\n";
            $rejectText .= "━━━━━━━━━━━━━━━━━━━━\n";
            $rejectText .= "👉 Bấm vào nút bên dưới để xem các đơn khác:";

            $keyboard = [
                'inline_keyboard' => [
                    [
                        ['text' => '⏳ Xem Đơn Chờ Duyệt', 'callback_data' => 'cmd_choduyet'],
                        ['text' => '📊 Xem Doanh Thu', 'callback_data' => 'cmd_doanhthu']
                    ]
                ]
            ];

            if ($messageId > 0) {
                $this->editMessageText($chatId, $messageId, $rejectText, $keyboard);
            }
            return;
        }

        // 3. Xem chi tiết đơn hàng: view_ord_{id}
        if (str_starts_with($data, 'view_ord_')) {
            $orderId = (int) substr($data, 9);
            $this->answerCallbackQuery($callbackId, "🔍 Đang mở chi tiết đơn #{$orderId}...");
            $res = $this->buildOrderDetailMessage($orderId);
            if ($messageId > 0) {
                $this->editMessageText($chatId, $messageId, $res['text'], $res['keyboard']);
            } else {
                $this->sendMessage($res['text'], $res['keyboard'], $chatId);
            }
            return;
        }

        // 4. Đánh dấu tin nhắn tư vấn là Đã Hỗ Trợ: done_chat_{id}
        if (str_starts_with($data, 'done_chat_')) {
            $msgId = (int) substr($data, 10);
            $msg = SupportMessage::find($msgId);

            if ($msg) {
                $msg->status = 'responded';
                $msg->replied_at = now();
                $msg->save();
                $this->answerCallbackQuery($callbackId, "✅ Đã đánh dấu câu hỏi của '{$msg->name}' là ĐÃ HỖ TRỢ!", false);
            } else {
                $this->answerCallbackQuery($callbackId, "ℹ️ Không tìm thấy câu hỏi ID: {$msgId}", true);
            }

            $res = $this->buildSupportMessagesMessage();
            if ($messageId > 0) {
                $this->editMessageText($chatId, $messageId, $res['text'], $res['keyboard']);
            }
            return;
        }

        // 5. Chuyển đổi tab Doanh thu: rev_today, rev_7days, rev_month, rev_refresh
        if (in_array($data, ['rev_today', 'rev_7days', 'rev_month', 'rev_refresh'])) {
            $range = match ($data) {
                'rev_7days' => '7days',
                'rev_month' => 'month',
                default => 'today',
            };
            $this->answerCallbackQuery($callbackId, "📊 Đang cập nhật báo cáo...");
            $res = $this->buildRevenueMessage($range);
            if ($messageId > 0) {
                $this->editMessageText($chatId, $messageId, $res['text'], $res['keyboard']);
            }
            return;
        }

        // 6. Các nút lệnh chuyển menu nhanh
        switch ($data) {
            case 'cmd_doanhthu':
                $this->answerCallbackQuery($callbackId, "📊 Đang tải Doanh thu...");
                $res = $this->buildRevenueMessage('today');
                if ($messageId > 0) {
                    $this->editMessageText($chatId, $messageId, $res['text'], $res['keyboard']);
                } else {
                    $this->sendMessage($res['text'], $res['keyboard'], $chatId);
                }
                break;

            case 'cmd_choduyet':
                $this->answerCallbackQuery($callbackId, "⏳ Đang tải Đơn chờ duyệt...");
                $res = $this->buildPendingOrdersMessage();
                if ($messageId > 0) {
                    $this->editMessageText($chatId, $messageId, $res['text'], $res['keyboard']);
                } else {
                    $this->sendMessage($res['text'], $res['keyboard'], $chatId);
                }
                break;

            case 'cmd_danhsachchat':
                $this->answerCallbackQuery($callbackId, "💬 Đang tải Live Chat...");
                $res = $this->buildSupportMessagesMessage();
                if ($messageId > 0) {
                    $this->editMessageText($chatId, $messageId, $res['text'], $res['keyboard']);
                } else {
                    $this->sendMessage($res['text'], $res['keyboard'], $chatId);
                }
                break;

            case 'cmd_thongke':
                $this->answerCallbackQuery($callbackId, "👥 Đang tải Thống kê hệ thống...");
                $res = $this->buildSystemStatsMessage();
                if ($messageId > 0) {
                    $this->editMessageText($chatId, $messageId, $res['text'], $res['keyboard']);
                } else {
                    $this->sendMessage($res['text'], $res['keyboard'], $chatId);
                }
                break;

            case 'cmd_banggia':
                $this->answerCallbackQuery($callbackId, "💎 Đang tải Bảng giá...");
                $res = $this->buildPricingMessage();
                if ($messageId > 0) {
                    $this->editMessageText($chatId, $messageId, $res['text'], $res['keyboard']);
                } else {
                    $this->sendMessage($res['text'], $res['keyboard'], $chatId);
                }
                break;

            case 'cmd_qrtest':
                $this->answerCallbackQuery($callbackId, "⚡ Đang tạo thông tin QR...");
                $res = $this->buildQrTestMessage();
                if ($messageId > 0) {
                    $this->editMessageText($chatId, $messageId, $res['text'], $res['keyboard']);
                } else {
                    $this->sendMessage($res['text'], $res['keyboard'], $chatId);
                }
                break;

            case 'cmd_trogiup':
                $this->answerCallbackQuery($callbackId, "❓ Đang mở Trợ giúp...");
                $res = $this->buildHelpMessage();
                if ($messageId > 0) {
                    $this->editMessageText($chatId, $messageId, $res['text'], $res['keyboard']);
                } else {
                    $this->sendMessage($res['text'], $res['keyboard'], $chatId);
                }
                break;

            default:
                $this->answerCallbackQuery($callbackId, "✨ Đã tiếp nhận yêu cầu!");
                break;
        }
    }

    /**
     * Xử lý tin nhắn văn bản hoặc bấm nút Persistent Keyboard
     */
    public function handleCommand(string $rawText, ?string $chatId = null): string
    {
        $targetChat = $chatId ?: $this->adminChatId;
        $text = trim($rawText);

        // Chống trùng lặp tin nhắn nếu nhận lại cùng lệnh trong vòng 2 giây
        $dedupKey = "tele_cmd_dedup_" . md5($targetChat . '_' . $text);
        if (! Cache::add($dedupKey, true, 2)) {
            return '';
        }

        // Chuẩn hóa tên lệnh
        $lower = mb_strtolower($text);

        // Bỏ hậu tố @bot_name nếu có
        if (str_contains($lower, '@')) {
            $lower = explode('@', $lower)[0];
        }

        $isStart = in_array($lower, ['/start', 'start', 'bắt đầu']);

        // BẢO VỆ BẢO MẬT: Nếu KHÔNG PHẢI là Admin Chat ID
        if (! $this->isAdminChat($targetChat)) {
            // 1. Cho phép xem bảng giá công khai
            if (str_contains($lower, 'bảng giá') || str_contains($lower, 'các gói') || $lower === '/banggia') {
                $res = $this->buildPricingMessage();
                $this->sendMessage($res['text'], $res['keyboard'], $targetChat);
                return $res['text'];
            }

            // 2. Chặn các lệnh quản trị nhạy cảm (doanh thu, đơn chờ, chat, thống kê, qr)
            $isAdminAction = str_contains($lower, 'doanh thu') || $lower === '/doanhthu'
                || str_contains($lower, 'chờ duyệt') || str_contains($lower, 'đơn chờ') || $lower === '/choduyet'
                || str_contains($lower, 'live chat') || str_contains($lower, 'tin nhắn') || str_contains($lower, 'hộp thư') || $lower === '/danhsachchat'
                || str_contains($lower, 'thống kê') || str_contains($lower, 'học sinh') || $lower === '/thongke'
                || str_contains($lower, 'tạo qr') || str_contains($lower, 'vietqr') || str_contains($lower, 'mã qr') || str_contains($lower, 'thanh toán') || $lower === '/qrtest'
                || str_starts_with($lower, '/chitiet');

            if ($isAdminAction) {
                $reply = "⛔ <b>TỪ CHỐI TRUY CẬP!</b>\n";
                $reply .= "━━━━━━━━━━━━━━━━━━━━\n";
                $reply .= "Bạn không có quyền truy cập dữ liệu quản trị của IC3 Quest.\n";
                $reply .= "Mã Chat ID của bạn: <code>{$targetChat}</code>\n";
                $reply .= "Nếu bạn là Quản trị viên, vui lòng cấu hình Chat ID này vào hệ thống.";

                $this->sendMessage($reply, null, $targetChat);
                return $reply;
            }

            // 3. Với các lệnh còn lại hoặc /start: Hiển thị lời chào công khai
            $res = $this->buildPublicWelcomeMessage();
            $this->sendMessage($res['text'], $res['keyboard'], $targetChat);
            return $res['text'];
        }

        // ==================== CÁC LỆNH DÀNH RIÊNG CHO QUẢN TRỊ VIÊN ====================

        // Tự động kích hoạt bàn phím cố định đáy chat nếu admin gõ /start hoặc menu
        if ($isStart || str_contains($lower, 'hướng dẫn') || str_contains($lower, 'menu') || in_array($lower, ['/help', '/trogiup'])) {
            $res = $this->buildHelpMessage();
            // Gửi kèm Persistent Keyboard để ghim chặt vào đáy màn hình admin
            $this->sendMessage($res['text'], $this->getMainPersistentKeyboard(), $targetChat);
            return $res['text'];
        }

        if (str_contains($lower, 'doanh thu') || $lower === '/doanhthu') {
            $res = $this->buildRevenueMessage('today');
            $this->sendMessage($res['text'], $res['keyboard'], $targetChat);
            return $res['text'];
        }

        if (str_contains($lower, 'chờ duyệt') || str_contains($lower, 'đơn chờ') || $lower === '/choduyet') {
            $res = $this->buildPendingOrdersMessage();
            $this->sendMessage($res['text'], $res['keyboard'], $targetChat);
            return $res['text'];
        }

        if (str_contains($lower, 'live chat') || str_contains($lower, 'tin nhắn') || str_contains($lower, 'hộp thư') || $lower === '/danhsachchat') {
            $res = $this->buildSupportMessagesMessage();
            $this->sendMessage($res['text'], $res['keyboard'], $targetChat);
            return $res['text'];
        }

        if (str_contains($lower, 'thống kê') || str_contains($lower, 'học sinh') || $lower === '/thongke') {
            $res = $this->buildSystemStatsMessage();
            $this->sendMessage($res['text'], $res['keyboard'], $targetChat);
            return $res['text'];
        }

        if (str_contains($lower, 'bảng giá') || str_contains($lower, 'các gói') || $lower === '/banggia') {
            $res = $this->buildPricingMessage();
            $this->sendMessage($res['text'], $res['keyboard'], $targetChat);
            return $res['text'];
        }

        if (str_contains($lower, 'tạo qr') || str_contains($lower, 'vietqr') || str_contains($lower, 'mã qr') || str_contains($lower, 'thanh toán') || $lower === '/qrtest') {
            $res = $this->buildQrTestMessage();
            $this->sendMessage($res['text'], $res['keyboard'], $targetChat);
            return $res['text'];
        }

        if (str_starts_with($lower, '/chitiet')) {
            $parts = preg_split('/\s+/', $text);
            $arg = $parts[1] ?? null;
            if (! $arg) {
                $reply = "⚠️ <b>Thiếu thông tin:</b> Vui lòng nhập ID hoặc Mã đơn hàng. Ví dụ: <code>/chitiet 1</code>";
                $this->sendMessage($reply, null, $targetChat);
                return $reply;
            }
            $res = $this->buildOrderDetailMessage($arg);
            $this->sendMessage($res['text'], $res['keyboard'], $targetChat);
            return $res['text'];
        }

        // Lệnh mặc định nếu không khớp
        $reply = "❓ <b>Không nhận diện được thao tác!</b>\n";
        $reply .= "━━━━━━━━━━━━━━━━━━━━\n";
        $reply .= "👉 Thầy/Cô vui lòng bấm chọn trực tiếp vào các nút ở menu bên dưới hoặc gõ <b>/start</b> để mở Trung tâm điều khiển.";

        $this->sendMessageWithPersistentKeyboard($reply, null, $targetChat);
        return $reply;
    }

    /**
     * Buidler: Báo cáo doanh thu với các Tab thời gian linh hoạt
     */
    public function buildRevenueMessage(string $range = 'today'): array
    {
        $query = PackageOrder::where('status', PackageOrder::STATUS_ACTIVE);
        $titleRange = 'HÔM NAY';

        if ($range === '7days') {
            $query->where('activated_at', '>=', now()->subDays(7)->startOfDay());
            $titleRange = '7 NGÀY QUA';
        } elseif ($range === 'month') {
            $query->whereMonth('activated_at', now()->month)
                  ->whereYear('activated_at', now()->year);
            $titleRange = 'THÁNG ' . now()->format('m/Y');
        } else {
            $query->whereDate('activated_at', now()->format('Y-m-d'));
            $titleRange = 'HÔM NAY (' . now()->format('d/m/Y') . ')';
        }

        $orders = $query->latest('activated_at')->get();
        $totalAmount = $orders->sum('price');
        $totalCount = $orders->count();
        $formattedAmount = number_format($totalAmount) . ' đ';

        $text = "📊 <b>BÁO CÁO DOANH THU & KÍCH HOẠT: {$titleRange}</b>\n";
        $text .= "━━━━━━━━━━━━━━━━━━━━\n";
        $text .= "✅ <b>Số đơn hoàn tất:</b> <b>{$totalCount}</b> đơn\n";
        $text .= "💰 <b>Tổng thu nhập:</b> <tg-spoiler><b>{$formattedAmount}</b></tg-spoiler>\n\n";

        if ($totalCount > 0) {
            $text .= "<b>Danh sách đơn đã thanh toán:</b>\n";
            foreach ($orders->take(6) as $idx => $ord) {
                $idxNum = $idx + 1;
                $amt = number_format($ord->price) . ' đ';
                $user = $ord->user?->name ?? 'GV Khách';
                $time = $ord->activated_at ? $ord->activated_at->format('H:i d/m') : '';
                $text .= "{$idxNum}. <code>#{$ord->code}</code> ({$amt})\n";
                $text .= "   • GV: <b>{$user}</b> ({$time})\n";
                $text .= "   • Gói: <i>{$ord->package_name}</i>\n";
            }
            if ($totalCount > 6) {
                $remain = $totalCount - 6;
                $text .= "<i>...và còn {$remain} đơn khác trên trang quản trị.</i>\n";
            }
        } else {
            $text .= "<i>Chưa ghi nhận đơn kích hoạt nào trong khoảng thời gian này.</i>\n";
        }

        $text .= "━━━━━━━━━━━━━━━━━━━━\n";
        $text .= "👉 <i>Bấm chuyển mốc thời gian để xem chi tiết doanh thu:</i>";

        // Tạo nút chọn Tab với badge đánh dấu Tab đang chọn
        $btnToday = ($range === 'today') ? '🟢 Hôm Nay' : '📅 Hôm Nay';
        $btn7Days = ($range === '7days') ? '🟢 7 Ngày' : '📆 7 Ngày';
        $btnMonth = ($range === 'month') ? '🟢 Tháng Này' : '📈 Tháng Này';

        $inlineKeyboard = [
            'inline_keyboard' => [
                [
                    ['text' => $btnToday, 'callback_data' => 'rev_today'],
                    ['text' => $btn7Days, 'callback_data' => 'rev_7days'],
                    ['text' => $btnMonth, 'callback_data' => 'rev_month']
                ],
                [
                    ['text' => '⏳ Xem Đơn Chờ Duyệt', 'callback_data' => 'cmd_choduyet'],
                    ['text' => '🔄 Làm Mới', 'callback_data' => 'rev_refresh']
                ],
                [
                    ['text' => '🌐 Mở Báo Cáo Chi Tiết', 'url' => url('/quan-tri#tab-packages')]
                ]
            ]
        ];

        return [
            'text' => $text,
            'keyboard' => $inlineKeyboard
        ];
    }

    /**
     * Builder: Danh sách đơn hàng chờ duyệt kèm nút Duyệt ngay
     */
    public function buildPendingOrdersMessage(): array
    {
        $pendingOrders = PackageOrder::with('user')
            ->where('status', PackageOrder::STATUS_PENDING)
            ->latest('id')
            ->take(6)
            ->get();

        $totalPending = PackageOrder::where('status', PackageOrder::STATUS_PENDING)->count();

        $text = "⏳ <b>DANH SÁCH ĐƠN HÀNG CHỜ DUYỆT ({$totalPending} đơn)</b>\n";
        $text .= "━━━━━━━━━━━━━━━━━━━━\n";

        $actionButtons = [];

        if ($pendingOrders->isEmpty()) {
            $text .= "🎉 <b>Tuyệt vời!</b> Hiện tại không có đơn hàng nào đang chờ duyệt.\n";
            $text .= "Tất cả đơn đã được thanh toán tự động hoặc đã kích hoạt thành công!\n";
        } else {
            foreach ($pendingOrders as $idx => $ord) {
                $idxNum = $idx + 1;
                $amt = number_format($ord->price) . ' đ';
                $teacherName = $ord->user?->name ?? 'Khách';
                $phone = $ord->user?->phone;
                $time = $ord->created_at ? $ord->created_at->format('H:i d/m') : '';
                $payMethod = $ord->payment_method === 'payos' ? 'Quét mã QR' : 'Chuyển khoản MB';

                $text .= "{$idxNum}. <b>ID: {$ord->id}</b> — <code>#{$ord->code}</code> ({$time})\n";
                $text .= "   • GV: <b>{$teacherName}</b>" . ($phone ? " (<code>{$phone}</code>)" : "") . "\n";
                $text .= "   • Gói: <b>{$ord->package_name}</b> | <b>{$amt}</b> ({$payMethod})\n\n";

                // Hàng nút cho từng đơn hàng
                $actionButtons[] = [
                    ['text' => "⚡ Duyệt #{$ord->id}", 'callback_data' => "act_ord_{$ord->id}"],
                    ['text' => "🔍 Chi Tiết #{$ord->id}", 'callback_data' => "view_ord_{$ord->id}"],
                    ['text' => "❌ Hủy", 'callback_data' => "rej_ord_{$ord->id}"]
                ];
            }
        }

        $text .= "━━━━━━━━━━━━━━━━━━━━\n";
        $text .= "👉 <i>Bấm nút <b>⚡ Duyệt #ID</b> để lập tức mở quyền và cộng hạn cho giáo viên!</i>";

        $actionButtons[] = [
            ['text' => '🔄 Cập Nhật Danh Sách', 'callback_data' => 'cmd_choduyet'],
            ['text' => '📊 Xem Doanh Thu', 'callback_data' => 'cmd_doanhthu']
        ];
        $actionButtons[] = [
            ['text' => '🌐 Mở Trang Quản Trị', 'url' => url('/quan-tri#tab-packages')]
        ];

        return [
            'text' => $text,
            'keyboard' => ['inline_keyboard' => $actionButtons]
        ];
    }

    /**
     * Builder: Chi tiết một đơn hàng cụ thể
     */
    public function buildOrderDetailMessage(int|string $orderId): array
    {
        $order = PackageOrder::with(['user', 'package'])->where('id', $orderId)
            ->orWhere('code', $orderId)
            ->first();

        if (! $order) {
            return [
                'text' => "❌ Không tìm thấy đơn hàng với mã hoặc ID: <code>{$orderId}</code>",
                'keyboard' => [
                    'inline_keyboard' => [
                        [['text' => '⏳ Quay lại danh sách chờ', 'callback_data' => 'cmd_choduyet']]
                    ]
                ]
            ];
        }

        $amt = number_format($order->price) . ' đ';
        $user = $order->user;
        $statusText = match ($order->status) {
            'active' => '✅ Đã kích hoạt bản quyền',
            'rejected' => '❌ Đã hủy / từ chối',
            default => '⏳ Đang chờ thanh toán & duyệt',
        };

        $phone = $user?->phone;
        if (! $phone && $order->notes && preg_match('/(?:SĐT|Điện thoại):\s*([0-9\+\s]+)/iu', $order->notes, $mP)) {
            $phone = trim($mP[1]);
        }

        $school = $user?->school_name;
        if (! $school && $order->notes && preg_match('/(?:Trường|Đơn vị|Trường\/Đơn vị):\s*([^·\n]+)/iu', $order->notes, $mS)) {
            $school = trim($mS[1]);
        }

        $text = "🧾 <b>THÔNG TIN CHI TIẾT ĐƠN HÀNG #{$order->code}</b>\n";
        $text .= "━━━━━━━━━━━━━━━━━━━━\n";
        $text .= "🆔 <b>ID Đơn:</b> <code>{$order->id}</code>\n";
        $text .= "📌 <b>Trạng thái:</b> <b>{$statusText}</b>\n";
        $text .= "👩‍🏫 <b>Giáo viên:</b> <b>" . htmlspecialchars($user?->name ?? 'N/A') . "</b>\n";
        $text .= "📧 <b>Email:</b> <code>" . htmlspecialchars($user?->email ?? 'N/A') . "</code>\n";
        if ($phone) {
            $text .= "📱 <b>Số điện thoại (Zalo):</b> <code>" . htmlspecialchars($phone) . "</code>\n";
        }
        if ($school) {
            $text .= "🏫 <b>Trường học / Đơn vị:</b> <b>" . htmlspecialchars($school) . "</b>\n";
        }
        if ($user) {
            $text .= "👥 <b>Sĩ số hiện tại:</b> " . ($user->max_students ?: 0) . " HS\n";
            $text .= "📅 <b>Hạn dùng hiện tại:</b> " . ($user->expires_at ? $user->expires_at->format('d/m/Y') : 'Chưa kích hoạt') . "\n";
        }
        $text .= "━━━━━━━━━━━━━━━━━━━━\n";
        $text .= "📦 <b>Gói dịch vụ:</b> <b>" . htmlspecialchars($order->package_name) . "</b>\n";
        $text .= "💰 <b>Số tiền thanh toán:</b> <b>{$amt}</b>\n";
        $text .= "⏰ <b>Thời hạn sử dụng:</b> {$order->duration_days} ngày\n";
        $text .= "👥 <b>Hạn mức học sinh:</b> Tối đa {$order->max_students} em\n";
        $payMethodText = $order->payment_method === 'payos' ? 'Quét mã VietQR tự động' : 'Chuyển khoản trực tiếp (MB Bank)';
        $text .= "💳 <b>Phương thức:</b> {$payMethodText}\n";
        $text .= "⏱️ <b>Thời gian tạo:</b> " . ($order->created_at ? $order->created_at->format('d/m/Y H:i') : 'N/A') . "\n";
        if ($order->activated_at) {
            $text .= "⚡ <b>Kích hoạt lúc:</b> " . $order->activated_at->format('d/m/Y H:i') . "\n";
        }
        if ($order->notes) {
            $text .= "📝 <b>Ghi chú:</b> <i>" . htmlspecialchars($order->notes) . "</i>\n";
        }
        $text .= "━━━━━━━━━━━━━━━━━━━━\n";

        $buttons = [];
        if ($order->isPending()) {
            $buttons[] = [
                ['text' => "⚡ Duyệt Kích Hoạt Đơn Này", 'callback_data' => "act_ord_{$order->id}"],
                ['text' => "❌ Từ Chối Đơn", 'callback_data' => "rej_ord_{$order->id}"]
            ];
        }

        $cleanPhone = $phone ? preg_replace('/[^0-9]/', '', $phone) : null;
        if ($cleanPhone) {
            $buttons[] = [
                ['text' => "💬 Nhắn Zalo ({$cleanPhone})", 'url' => "https://zalo.me/{$cleanPhone}"],
                ['text' => "📞 Gọi Khách", 'url' => "tel:{$cleanPhone}"]
            ];
        }

        $buttons[] = [
            ['text' => '⏳ Danh Sách Chờ Duyệt', 'callback_data' => 'cmd_choduyet'],
            ['text' => '📊 Doanh Thu', 'callback_data' => 'cmd_doanhthu']
        ];
        $buttons[] = [
            ['text' => '🌐 Xem Trên Trang Quản Trị', 'url' => url('/quan-tri#tab-packages')]
        ];

        return [
            'text' => $text,
            'keyboard' => ['inline_keyboard' => $buttons]
        ];
    }

    /**
     * Builder: Tin nhắn tư vấn Live Chat từ khách
     */
    public function buildSupportMessagesMessage(): array
    {
        $messages = SupportMessage::latest('id')->take(5)->get();

        $text = "💬 <b>DANH SÁCH TIN NHẮN TƯ VẤN (LIVE CHAT WEB)</b>\n";
        $text .= "━━━━━━━━━━━━━━━━━━━━\n";

        $actionButtons = [];

        if ($messages->isEmpty()) {
            $text .= "<i>Chưa có tin nhắn tư vấn nào từ website.</i>\n";
        } else {
            foreach ($messages as $idx => $msg) {
                $idxNum = $idx + 1;
                $contact = $msg->phone ?: ($msg->email ?: 'N/A');
                $time = $msg->created_at ? $msg->created_at->format('H:i d/m') : '';
                $isPending = ($msg->status === 'pending');
                $statusBadge = $isPending ? '⏳ Chờ liên hệ' : '✅ Đã hỗ trợ';

                $text .= "{$idxNum}. <b>" . htmlspecialchars($msg->name) . "</b> ({$statusBadge})\n";
                $text .= "   • Liên hệ: <code>" . htmlspecialchars($contact) . "</code> ({$time})\n";
                $text .= "   • Câu hỏi: <i>\"" . htmlspecialchars(mb_strimwidth($msg->message, 0, 90, '...')) . "\"</i>\n";

                if ($msg->admin_reply) {
                    $text .= "   • Đã phản hồi: <i>\"" . htmlspecialchars(mb_strimwidth($msg->admin_reply, 0, 70, '...')) . "\"</i>\n";
                }
                $text .= "\n";

                // Hàng nút cho từng tin nhắn đang chờ
                if ($isPending) {
                    $row = [
                        ['text' => "✅ Đã Tư Vấn #{$msg->id}", 'callback_data' => "done_chat_{$msg->id}"]
                    ];
                    if ($msg->phone) {
                        $clean = preg_replace('/[^0-9]/', '', $msg->phone);
                        $row[] = ['text' => "💬 Zalo", 'url' => "https://zalo.me/{$clean}"];
                    }
                    $actionButtons[] = $row;
                }
            }
        }

        $text .= "━━━━━━━━━━━━━━━━━━━━\n";
        $text .= "💡 <i>Gõ trực tiếp tin nhắn vào chat này để bot tự động chuyển tiếp trả lời đến khách trên web!</i>";

        $actionButtons[] = [
            ['text' => '🔄 Làm Mới Live Chat', 'callback_data' => 'cmd_danhsachchat'],
            ['text' => '🌐 Mở Hộp Thư Tư Vấn', 'url' => url('/quan-tri#tab-messenger')]
        ];

        return [
            'text' => $text,
            'keyboard' => ['inline_keyboard' => $actionButtons]
        ];
    }

    /**
     * Builder: Thống kê hệ thống toàn diện
     */
    public function buildSystemStatsMessage(): array
    {
        $teacherCount = User::where('role', 'teacher')->count();
        $studentCount = User::where('role', 'student')->count();
        $adminCount = User::where('role', 'admin')->count();
        $classCount = class_exists(Classroom::class) ? Classroom::count() : 0;
        $questionCount = class_exists(Question::class) ? Question::count() : 0;
        $orderCount = PackageOrder::count();
        $activeOrders = PackageOrder::where('status', PackageOrder::STATUS_ACTIVE)->count();
        $pendingOrders = PackageOrder::where('status', PackageOrder::STATUS_PENDING)->count();
        $totalRevenue = PackageOrder::where('status', PackageOrder::STATUS_ACTIVE)->sum('price');

        $text = "👥 <b>THỐNG KÊ TỔNG QUAN HỆ THỐNG IC3 QUEST</b>\n";
        $text .= "━━━━━━━━━━━━━━━━━━━━\n";
        $text .= "👩‍🏫 <b>Giáo viên quản lý:</b> <b>{$teacherCount}</b> thầy/cô\n";
        $text .= "🎒 <b>Học sinh ôn luyện:</b> <b>{$studentCount}</b> em\n";
        $text .= "📚 <b>Lớp học đang mở:</b> <b>{$classCount}</b> lớp\n";
        $text .= "❓ <b>Ngân hàng câu hỏi:</b> <b>{$questionCount}</b> câu hỏi chuẩn IC3\n";
        $text .= "━━━━━━━━━━━━━━━━━━━━\n";
        $text .= "📦 <b>Tình hình đơn thuê gói:</b>\n";
        $text .= "   • Tổng số đơn: <b>{$orderCount}</b>\n";
        $text .= "   • Đã kích hoạt: <b>{$activeOrders}</b> đơn\n";
        $text .= "   • Đang chờ duyệt: <b>{$pendingOrders}</b> đơn\n";
        $text .= "💰 <b>Tổng tích lũy doanh thu:</b> <b>" . number_format($totalRevenue) . " đ</b>\n";
        $text .= "━━━━━━━━━━━━━━━━━━━━\n";
        $text .= "🟢 <i>Hệ thống học và thi trực tuyến hoạt động ổn định!</i>";

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '📊 Xem Doanh Thu', 'callback_data' => 'cmd_doanhthu'],
                    ['text' => '⏳ Xem Đơn Chờ', 'callback_data' => 'cmd_choduyet']
                ],
                [
                    ['text' => '💬 Live Chat Khách', 'callback_data' => 'cmd_danhsachchat'],
                    ['text' => '🔄 Làm Mới Thống Kê', 'callback_data' => 'cmd_thongke']
                ],
                [
                    ['text' => '🌐 Mở Trang Quản Trị Hệ Thống', 'url' => url('/quan-tri')]
                ]
            ]
        ];

        return [
            'text' => $text,
            'keyboard' => $keyboard
        ];
    }

    /**
     * Builder: Bảng giá các gói dịch vụ
     */
    public function buildPricingMessage(): array
    {
        $packages = Package::all();

        $text = "💎 <b>BẢNG GIÁ CÁC GÓI BẢN QUYỀN IC3 GS6 QUEST</b>\n";
        $text .= "━━━━━━━━━━━━━━━━━━━━\n";

        if ($packages->isEmpty()) {
            $text .= "1. <b>Gói Cơ Bản (3 Tháng)</b>: 490.000 đ — Quản lý 100 học sinh\n";
            $text .= "2. <b>Gói Tiêu Chuẩn (1 Năm)</b>: 990.000 đ — Quản lý 300 học sinh\n";
            $text .= "3. <b>Gói VIP Trường Học (1 Năm)</b>: 2.490.000 đ — Không giới hạn học sinh\n";
        } else {
            foreach ($packages as $pkg) {
                $price = number_format($pkg->price) . ' đ';
                $students = $pkg->max_students > 0 ? "Tối đa {$pkg->max_students} học sinh" : "Không giới hạn học sinh";
                $cleanName = preg_replace('/\s*\((Starter|Standard|Pro School)\)\s*/i', '', $pkg->name);
                $text .= "🏷️ <b>" . htmlspecialchars($cleanName) . "</b>: <b>{$price}</b>\n";
                $text .= "   • Thời hạn: {$pkg->duration_days} ngày\n";
                $text .= "   • Quy mô: {$students}\n\n";
            }
        }

        $text .= "━━━━━━━━━━━━━━━━━━━━\n";
        $text .= "✨ <i>Hỗ trợ thanh toán: Quét mã QR tự động & Chuyển khoản MB Bank.</i>";

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '⚡ Xem Mã QR Thanh Toán', 'callback_data' => 'cmd_qrtest'],
                    ['text' => '🌐 Xem Bảng Giá Trên Web', 'url' => url('/bang-gia')]
                ]
            ]
        ];

        return [
            'text' => $text,
            'keyboard' => $keyboard
        ];
    }

    /**
     * Builder: Kiểm tra QR VietQR MB Bank
     */
    public function buildQrTestMessage(): array
    {
        $bankId = config('payment.bank_id', 'MB');
        $bankAccount = config('payment.bank_account', '0345151438');
        $bankName = config('payment.bank_account_name', 'LE MINH TRI');

        $text = "⚡ <b>THÔNG TIN TÀI KHOẢN CHUYỂN KHOẢN THỰC TẾ</b>\n";
        $text .= "━━━━━━━━━━━━━━━━━━━━\n";
        $text .= "🏦 <b>Ngân hàng:</b> <b>{$bankId} (Ngân Hàng MB Bank)</b>\n";
        $text .= "💳 <b>Số tài khoản:</b> <code>{$bankAccount}</code>\n";
        $text .= "👤 <b>Chủ tài khoản:</b> <b>{$bankName}</b>\n";
        $text .= "━━━━━━━━━━━━━━━━━━━━\n";
        $text .= "📌 Mã QR được tạo tự động kèm đúng số tiền và nội dung khi Thầy/Cô đăng ký gói.\n";
        $text .= "👉 Sau khi Thầy/Cô chuyển khoản xong, hệ thống sẽ gửi thông báo để duyệt và kích hoạt ngay!";

        $qrUrl = "https://img.vietqr.io/image/{$bankId}-{$bankAccount}-compact2.png?amount=490000&addInfo=TEST%20IC3&accountName=" . urlencode($bankName);

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '🖼️ Mở Ảnh QR Chuyển Khoản Mẫu', 'url' => $qrUrl]
                ],
                [
                    ['text' => '⏳ Xem Đơn Chờ Duyệt', 'callback_data' => 'cmd_choduyet'],
                    ['text' => '🔙 Về Menu Chính', 'callback_data' => 'cmd_trogiup']
                ]
            ]
        ];

        return [
            'text' => $text,
            'keyboard' => $keyboard
        ];
    }

    /**
     * Builder: Menu trung tâm điều hành & Trợ giúp
     */
    public function buildHelpMessage(): array
    {
        $text = "🚀 <b>TRUNG TÂM ĐIỀU HÀNH IC3 QUEST</b>\n";
        $text .= "━━━━━━━━━━━━━━━━━━━━\n";
        $text .= "Xin chào Quản trị viên! Hệ thống hỗ trợ tra cứu nhanh chóng và thuận tiện.\n\n";
        $text .= "✨ <b>CÁC CHỨC NĂNG CHÍNH:</b>\n";
        $text .= "📊 <b>Báo Cáo Doanh Thu:</b> Xem thu nhập Hôm nay, 7 ngày, Tháng\n";
        $text .= "⏳ <b>Đơn Chờ Duyệt:</b> Xem và bấm nút [⚡ Duyệt] kích hoạt ngay\n";
        $text .= "💬 <b>Hộp Thư Tư Vấn:</b> Xem câu hỏi và nhắn Zalo hỗ trợ Thầy/Cô\n";
        $text .= "👥 <b>Thống Kê Học Sinh:</b> Tổng học sinh, giáo viên, lớp, câu hỏi\n";
        $text .= "💎 <b>Bảng Giá Gói:</b> Thông tin các gói bản quyền IC3\n";
        $text .= "⚡ <b>Mã QR Thanh Toán:</b> Kiểm tra thông tin chuyển khoản MB Bank\n";
        $text .= "━━━━━━━━━━━━━━━━━━━━\n";
        $text .= "👇 <i>Thầy/Cô có thể bấm vào các nút bên dưới:</i>";

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '📊 Xem Doanh Thu', 'callback_data' => 'cmd_doanhthu'],
                    ['text' => '⏳ Đơn Chờ Duyệt', 'callback_data' => 'cmd_choduyet']
                ],
                [
                    ['text' => '💬 Hộp Thư Tư Vấn', 'callback_data' => 'cmd_danhsachchat'],
                    ['text' => '👥 Thống Kê Hệ Thống', 'callback_data' => 'cmd_thongke']
                ],
                [
                    ['text' => '💎 Bảng Giá Gói', 'callback_data' => 'cmd_banggia'],
                    ['text' => '⚡ Xem QR Thanh Toán', 'callback_data' => 'cmd_qrtest']
                ],
                [
                    ['text' => '🌐 Mở Trang Quản Trị IC3 Quest', 'url' => url('/quan-tri')]
                ]
            ]
        ];

        return [
            'text' => $text,
            'keyboard' => $keyboard
        ];
    }

    /**
     * Gửi tin nhắn và đính kèm Bàn phím bấm cố định (Persistent Reply Keyboard) ở đáy màn hình
     */
    public function sendMessageWithPersistentKeyboard(string $text, ?array $inlineKeyboard = null, ?string $customChatId = null): bool
    {
        $chatId = $customChatId ?: $this->adminChatId;
        if (empty($chatId) || empty($this->botToken)) return false;

        $persistentMarkup = $this->getMainPersistentKeyboard();
        return $this->sendMessage($text, $persistentMarkup, $chatId);
    }

    /**
     * Gửi tin nhắn qua Telegram Bot API (có hỗ trợ chỉ định chatId đích và replyMarkup)
     */
    public function sendMessage(string $text, ?array $replyMarkup = null, ?string $customChatId = null): bool
    {
        try {
            $chatId = $customChatId ?: $this->adminChatId;
            if (empty($chatId) || empty($this->botToken)) return false;

            $payload = [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
            ];

            if ($replyMarkup) {
                $payload['reply_markup'] = json_encode($replyMarkup);
            }

            $response = Http::timeout(8)
                ->post("https://api.telegram.org/bot{$this->botToken}/sendMessage", $payload);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('Telegram Send Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Kiểm tra trạng thái kết nối của Telegram Bot qua API getMe
     */
    public function testConnection(?string $token = null): array
    {
        $useToken = $token ?: $this->botToken;
        if (empty($useToken)) {
            return [
                'ok' => false,
                'error' => 'Chưa cung cấp mã Bot Token.',
            ];
        }

        try {
            $response = Http::timeout(6)
                ->get("https://api.telegram.org/bot{$useToken}/getMe");

            if ($response->successful() && $response->json('ok')) {
                return [
                    'ok' => true,
                    'bot' => $response->json('result'),
                ];
            }

            return [
                'ok' => false,
                'error' => $response->json('description') ?? 'Mã Token chưa chính xác hoặc bot bị vô hiệu hóa.',
            ];
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'error' => 'Lỗi kết nối máy chủ: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Tự động đồng bộ các tin nhắn phản hồi từ Telegram Admin về Website
     */
    public function syncIncomingTelegramReplies(?int $specificSupportId = null): ?string
    {
        // Toàn bộ việc nhận tin nhắn từ Telegram được xử lý an toàn và độc quyền bởi daemon telegram:poll
        // Không gọi getUpdates ở đây để triệt tiêu vĩnh viễn tình trạng tranh chấp offset và lặp tin nhắn
        return null;
    }
}
