<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Classroom;
use App\Models\Level;
use App\Models\Package;
use App\Models\PackageOrder;
use App\Models\TestAttempt;
use App\Models\User;
use Illuminate\View\View;

/**
 * Controller Quản trị Trung tâm (Admin Dashboard)
 * 
 * Chức năng: Điều phối giao diện điều hành tổng quan cho Quản trị viên/Giáo viên,
 * thống kê sĩ số học sinh, danh sách lớp học và lịch sử kết quả thi luyện.
 */
class AdminController extends Controller
{
    /**
     * Hiển thị Bảng điều khiển Quản trị & Trung tâm Báo cáo Thống kê
     */
    public function dashboard(): View
    {
        $user = auth()->user();
        $isTeacher = $user?->isTeacher() ?? false;
        $levels = Level::query()->with(['topics', 'program'])->withCount(['topics', 'students'])->orderBy('grade')->get();

        if ($isTeacher) {
            // 👩‍🏫 Giáo viên / Đại lý: Quản lý trực tiếp danh sách học sinh do mình tạo
            $students = User::query()
                ->where('role', UserRole::Student->value)
                ->where('created_by', $user->id)
                ->with(['accessibleLevels'])
                ->withCount('attempts')
                ->latest('id')
                ->get();

            $studentIds = $students->pluck('id')->all();

            $attemptsQuery = TestAttempt::query()
                ->whereIn('user_id', $studentIds)
                ->with(['user', 'practiceTest.topic.level']);

            $attempts = (clone $attemptsQuery)->latest('id')->limit(100)->get();
            $totalAttemptsCount = (clone $attemptsQuery)->count();
            $passedAttemptsCount = (clone $attemptsQuery)->where('score', '>=', 700)->count();
            $perfectAttemptsCount = (clone $attemptsQuery)->where('score', '>=', 1000)->count();
            $avgScore = $totalAttemptsCount > 0 ? (int) round((clone $attemptsQuery)->avg('score')) : 0;

            // Khối học được Admin cấp quyền cho Giáo viên này
            $teacherLevels = $user->teacherLevels()->with('program')->orderBy('grade')->get();
            $maxStudents = $user->max_students;
            $usedStudents = $students->count();
            $remainingSlots = $user->remainingStudentSlots();
            $expiresAt = $user->expires_at;
            $isSubActive = $user->isSubscriptionActive();
        } else {
            // 👨‍💼 Quản trị viên (Admin): Toàn quyền quản lý các Đại lý / Giáo viên & Học sinh
            $students = User::query()
                ->where('role', UserRole::Student->value)
                ->with(['accessibleLevels', 'teacher'])
                ->withCount('attempts')
                ->latest('id')
                ->get();

            $attemptsQuery = TestAttempt::query()
                ->with(['user.teacher', 'practiceTest.topic.level']);

            $attempts = (clone $attemptsQuery)->latest('id')->limit(100)->get();
            $totalAttemptsCount = (clone $attemptsQuery)->count();
            $passedAttemptsCount = (clone $attemptsQuery)->where('score', '>=', 700)->count();
            $perfectAttemptsCount = (clone $attemptsQuery)->where('score', '>=', 1000)->count();
            $avgScore = $totalAttemptsCount > 0 ? (int) round((clone $attemptsQuery)->avg('score')) : 0;

            $teacherLevels = $levels;
            $maxStudents = 0;
            $usedStudents = $students->count();
            $remainingSlots = 999999;
            $expiresAt = null;
            $isSubActive = true;
        }

        $classes = collect(); // Tối giản: Không dùng lớp học

        $teachers = User::query()
            ->where('role', UserRole::Teacher->value)
            ->with([
                'teacherLevels',
                'students' => fn($q) => $q->with('accessibleLevels')->withCount('attempts')->latest('id')
            ])
            ->withCount('students')
            ->orderBy('name')
            ->get();

        // 📊 B2B Business & Operational Metrics cho Quản trị viên Tổng (Admin)
        $totalTeachersCount = $teachers->count();
        $activeTeachersCount = $teachers->where('status', '!=', 'suspended')->count();
        $suspendedTeachersCount = $teachers->where('status', 'suspended')->count();
        $totalQuotaAllocated = (int) $teachers->sum('max_students');
        $totalActiveStudents = $students->count();
        $quotaUsagePercent = $totalQuotaAllocated > 0 ? round(($totalActiveStudents / $totalQuotaAllocated) * 100) : 0;
        
        $expiringTeachersCount = $teachers->filter(function ($t) {
            if (! $t->expires_at) return false;
            $days = (int) ceil(now()->diffInDays($t->expires_at, false));
            return $days >= 0 && $days <= 30;
        })->count();

        $expiredTeachersCount = $teachers->filter(function ($t) {
            if (! $t->expires_at) return false;
            $days = (int) ceil(now()->diffInDays($t->expires_at, false));
            return $days < 0;
        })->count();

        if ($isTeacher) {
            $allUsers = User::query()
                ->where('role', UserRole::Student->value)
                ->where('created_by', $user->id)
                ->with(['accessibleLevels'])
                ->withCount('attempts')
                ->latest('id')
                ->get();
        } else {
            $allUsers = User::query()
                ->where('role', '!=', UserRole::Admin->value)
                ->with(['accessibleLevels', 'teacherLevels', 'students', 'teacher', 'packageOrders', 'latestPackageOrder'])
                ->withCount('attempts')
                ->latest('id')
                ->get();
        }

        // 📈 Thống kê tỷ lệ hoàn thành theo từng Chủ đề (Topic Mastery Matrix)
        $allTopics = \App\Models\Topic::with('level')->orderBy('level_id')->orderBy('position')->get();
        $topicStats = [];
        foreach ($allTopics as $t) {
            $topicAttempts = $attempts->filter(fn ($a) => $a->practiceTest?->topic_id === $t->id);
            $cnt = $topicAttempts->count();
            $topicAvg = $cnt > 0 ? (int) round($topicAttempts->avg('score')) : null;
            $topicPass = $cnt > 0 ? round(($topicAttempts->where('score', '>=', 700)->count() / $cnt) * 100) : 0;

            if ($cnt > 0) {
                $topicStats[] = [
                    'topic' => $t,
                    'count' => $cnt,
                    'avg' => $topicAvg,
                    'pass_rate' => $topicPass,
                ];
            }
        }

        // 👑 Top học sinh xuất sắc nhất
        $topStudents = $students->filter(fn ($s) => $s->attempts_count > 0)
            ->sortByDesc(fn ($s) => $attempts->where('user_id', $s->id)->max('score') ?? 0)
            ->take(5);

        $programs = \App\Models\Program::orderBy('name')->get();

        // 💎 Quản trị Gói dịch vụ & Đơn thuê bản quyền (dành cho Admin)
        if (! $isTeacher) {
            $packages = Package::query()
                ->with(['levels.program'])
                ->withCount('orders')
                ->ordered()
                ->get();

            $packageOrders = PackageOrder::query()
                ->with(['user', 'package.levels'])
                ->latest('id')
                ->limit(50)
                ->get();

            $totalOrdersCount = PackageOrder::count();
            $pendingOrdersCount = PackageOrder::where('status', PackageOrder::STATUS_PENDING)->count();
            $activeOrdersCount = PackageOrder::where('status', PackageOrder::STATUS_ACTIVE)->count();
            $totalRevenue = (int) PackageOrder::where('status', PackageOrder::STATUS_ACTIVE)->sum('price');

            $supportMessages = \App\Models\SupportMessage::latest('id')->limit(50)->get();

            // 🧠 Cơ chế Nhận diện Thông minh: Khách vãng lai hay Giáo viên / Học sinh hệ thống
            foreach ($supportMessages as $msg) {
                $matchedUser = null;
                if (! empty($msg->email)) {
                    $matchedUser = User::where('email', $msg->email)->first();
                }
                if (! $matchedUser && ! empty($msg->phone)) {
                    $cleanPhone = preg_replace('/[^0-9]/', '', $msg->phone);
                    if (strlen($cleanPhone) >= 9) {
                        $matchedUser = User::where('email', 'LIKE', "%{$cleanPhone}%")
                            ->orWhere('name', 'LIKE', "%{$msg->name}%")
                            ->first();
                        if (! $matchedUser) {
                            $order = PackageOrder::where('notes', 'LIKE', "%{$cleanPhone}%")->first();
                            if ($order && $order->user) {
                                $matchedUser = $order->user;
                            }
                        }
                    }
                }

                if ($matchedUser) {
                    $msg->is_guest = false;
                    if ($matchedUser->role === 'teacher') {
                        $msg->user_type = 'teacher';
                        $msg->user_type_label = '👨‍🏫 Giáo Viên';
                        $msg->user_role_badge = 'badge-teacher';
                    } elseif ($matchedUser->role === 'student') {
                        $msg->user_type = 'student';
                        $msg->user_type_label = '🎓 Học Sinh';
                        $msg->user_role_badge = 'badge-student';
                    } else {
                        $msg->user_type = 'user';
                        $msg->user_type_label = '👤 Thành Viên';
                        $msg->user_role_badge = 'badge-user';
                    }
                    $msg->matched_user_name = $matchedUser->name;
                    $msg->matched_user_id = $matchedUser->id;
                } else {
                    $msg->is_guest = true;
                    $msg->user_type = 'guest';
                    $msg->user_type_label = '🌐 Khách Vãng Lai';
                    $msg->user_role_badge = 'badge-guest';
                    $msg->matched_user_name = null;
                    $msg->matched_user_id = null;
                }
            }

            $pendingSupportCount = \App\Models\SupportMessage::where('status', 'pending')->count();
        } else {
            $packages = collect();
            $packageOrders = collect();
            $totalOrdersCount = 0;
            $pendingOrdersCount = 0;
            $activeOrdersCount = 0;
            $totalRevenue = 0;
            $supportMessages = collect();
            $pendingSupportCount = 0;
        }

        return view('admin.dashboard', compact(
            'classes',
            'students',
            'allUsers',
            'teachers',
            'levels',
            'programs',
            'attempts',
            'totalAttemptsCount',
            'passedAttemptsCount',
            'perfectAttemptsCount',
            'avgScore',
            'topicStats',
            'topStudents',
            'allTopics',
            'isTeacher',
            'teacherLevels',
            'maxStudents',
            'usedStudents',
            'remainingSlots',
            'expiresAt',
            'isSubActive',
            'totalTeachersCount',
            'activeTeachersCount',
            'suspendedTeachersCount',
            'totalQuotaAllocated',
            'totalActiveStudents',
            'quotaUsagePercent',
            'expiringTeachersCount',
            'expiredTeachersCount',
            'packages',
            'packageOrders',
            'totalOrdersCount',
            'pendingOrdersCount',
            'activeOrdersCount',
            'totalRevenue',
            'supportMessages',
            'pendingSupportCount'
        ));
    }

    /**
     * Cập nhật trạng thái tin nhắn tư vấn Live Chat (Đã phản hồi / Đóng)
     */
    public function updateSupportMessageStatus(\Illuminate\Http\Request $request, \App\Models\SupportMessage $supportMessage): \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $status = $request->input('status');
        $reply = $request->input('admin_reply');

        $updateData = [];
        if ($status && in_array($status, ['pending', 'replied', 'closed'])) {
            $updateData['status'] = $status;
        }

        if ($reply) {
            $updateData['admin_reply'] = $reply;
            $updateData['replied_at'] = now();
            $updateData['status'] = 'replied';
        }

        if (! empty($updateData)) {
            $supportMessage->update($updateData);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'status' => $supportMessage->status,
                'admin_reply' => $supportMessage->admin_reply,
                'replied_at' => $supportMessage->replied_at ? $supportMessage->replied_at->format('H:i d/m/Y') : null,
                'message' => 'Đã lưu phản hồi thành công.'
            ]);
        }

        return back()->with('ok', 'Đã cập nhật trạng thái tin nhắn tư vấn.');
    }

    /**
     * Xóa đoạn chat / cuộc hội thoại hỗ trợ
     */
    public function deleteSupportMessage(\App\Models\SupportMessage $supportMessage): \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $supportMessage->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa đoạn chat thành công!',
                'pending_count' => \App\Models\SupportMessage::where('status', 'pending')->count(),
                'total_count' => \App\Models\SupportMessage::count(),
            ]);
        }

        return back()->with('ok', 'Đã xóa đoạn chat thành công.');
    }

    /**
     * Endpoint Polling Real-time cho Giao diện Messenger Quản trị
     */
    public function pollSupportMessages(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        $lastId = (int) $request->query('last_id', 0);
        $activeId = (int) $request->query('active_id', 0);

        $newMessages = \App\Models\SupportMessage::where('id', '>', $lastId)
            ->latest('id')
            ->get()
            ->map(function ($m) {
                $userType = 'guest';
                $userTypeLabel = '🌐 Khách Vãng Lai';
                if (! empty($m->email)) {
                    $u = User::where('email', $m->email)->first();
                    if ($u) {
                        $userType = $u->role === 'teacher' ? 'teacher' : ($u->role === 'student' ? 'student' : 'user');
                        $userTypeLabel = $u->role === 'teacher' ? '👨‍🏫 Giáo Viên' : ($u->role === 'student' ? '🎓 Học Sinh' : '👤 Thành Viên');
                    }
                }
                return [
                    'id' => $m->id,
                    'name' => $m->name,
                    'phone' => $m->phone,
                    'email' => $m->email,
                    'message' => $m->message,
                    'admin_reply' => $m->admin_reply,
                    'status' => $m->status,
                    'user_type' => $userType,
                    'user_type_label' => $userTypeLabel,
                    'replied_at' => $m->replied_at ? \Illuminate\Support\Carbon::parse($m->replied_at)->setTimezone('Asia/Ho_Chi_Minh')->format('H:i d/m/Y') : null,
                    'created_at' => $m->created_at ? $m->created_at->setTimezone('Asia/Ho_Chi_Minh')->format('H:i d/m/Y') : null,
                    'time_diff' => $m->created_at ? $m->created_at->setTimezone('Asia/Ho_Chi_Minh')->diffForHumans(null, true) : 'Vừa xong',
                ];
            });

        $activeMessage = null;
        if ($activeId > 0) {
            $active = \App\Models\SupportMessage::find($activeId);
            if ($active) {
                $activeMessage = [
                    'id' => $active->id,
                    'status' => $active->status,
                    'message' => $active->message,
                    'admin_reply' => $active->admin_reply,
                    'replied_at' => $active->replied_at ? \Illuminate\Support\Carbon::parse($active->replied_at)->setTimezone('Asia/Ho_Chi_Minh')->format('H:i d/m/Y') : null,
                    'created_at' => $active->created_at ? $active->created_at->setTimezone('Asia/Ho_Chi_Minh')->format('H:i d/m/Y') : null,
                    'updated_at' => $active->updated_at ? $active->updated_at->timestamp : 0,
                ];
            }
        }

        $pendingCount = \App\Models\SupportMessage::where('status', 'pending')->count();
        $pendingOrdersCount = \App\Models\PackageOrder::where('status', 'pending')->count();
        $totalCount = \App\Models\SupportMessage::count();

        return response()->json([
            'ok' => true,
            'new_messages' => $newMessages,
            'active_message' => $activeMessage,
            'pending_count' => $pendingCount,
            'pending_orders_count' => $pendingOrdersCount,
            'total_count' => $totalCount,
        ]);
    }

    /**
     * Kiểm tra và Lưu cấu hình Telegram Bot từ giao diện Quản Trị
     */
    public function saveTelegramConfig(\Illuminate\Http\Request $request, \App\Services\TelegramService $telegramService): \Illuminate\Http\JsonResponse
    {
        $botToken = trim($request->input('bot_token', ''));
        $adminChatId = trim($request->input('admin_chat_id', ''));

        if (empty($botToken)) {
            return response()->json([
                'ok' => false,
                'message' => 'Vui lòng nhập mã Bot Token lấy từ BotFather.',
            ], 422);
        }

        // Kiểm tra kết nối trước
        $testRes = $telegramService->testConnection($botToken);
        if (! $testRes['ok']) {
            return response()->json([
                'ok' => false,
                'message' => 'Mã Bot Token chưa đúng hoặc chưa kích hoạt: ' . ($testRes['error'] ?? 'Không kết nối được.'),
            ], 422);
        }

        $botInfo = $testRes['bot'] ?? [];
        $botUsername = $botInfo['username'] ?? 'trikun_cdphp_bot';

        // Cập nhật file .env
        $envPath = base_path('.env');
        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);

            // Replace or append TELEGRAM_BOT_TOKEN
            if (preg_match('/^TELEGRAM_BOT_TOKEN=.*$/m', $envContent)) {
                $envContent = preg_replace('/^TELEGRAM_BOT_TOKEN=.*$/m', 'TELEGRAM_BOT_TOKEN=' . $botToken, $envContent);
            } else {
                $envContent .= "\nTELEGRAM_BOT_TOKEN=" . $botToken;
            }

            // Replace or append TELEGRAM_ADMIN_CHAT_ID
            if (! empty($adminChatId)) {
                if (preg_match('/^TELEGRAM_ADMIN_CHAT_ID=.*$/m', $envContent)) {
                    $envContent = preg_replace('/^TELEGRAM_ADMIN_CHAT_ID=.*$/m', 'TELEGRAM_ADMIN_CHAT_ID=' . $adminChatId, $envContent);
                } else {
                    $envContent .= "\nTELEGRAM_ADMIN_CHAT_ID=" . $adminChatId;
                }
            }

            file_put_contents($envPath, $envContent);
        }

        // Gửi thử tin nhắn chào mừng về Telegram nếu có chatId
        $chatIdToSend = ! empty($adminChatId) ? $adminChatId : config('services.telegram.admin_chat_id');
        if (! empty($chatIdToSend)) {
            $helloText = "🎉 <b>[IC3 QUEST] KẾT NỐI BOT TELEGRAM THÀNH CÔNG!</b>\n";
            $helloText .= "━━━━━━━━━━━━━━━━━━━━\n";
            $helloText .= "🤖 <b>Bot quản trị:</b> @{$botUsername}\n";
            $helloText .= "⏱️ <b>Thời gian:</b> " . now()->format('d/m/Y H:i:s') . "\n";
            $helloText .= "✅ <b>Trạng thái:</b> Sẵn sàng nhận thông báo đơn hàng & tin nhắn giáo viên real-time!";
            $telegramService->sendMessage($helloText, null, $chatIdToSend);
        }

        return response()->json([
            'ok' => true,
            'message' => "Kết nối thành công tới Bot @{$botUsername}! Cấu hình đã được lưu an toàn.",
            'bot' => $botInfo,
        ]);
    }

    /**
     * Bắn tin nhắn thử nghiệm tới Telegram Admin
     */
    public function testTelegramNotification(\Illuminate\Http\Request $request, \App\Services\TelegramService $telegramService): \Illuminate\Http\JsonResponse
    {
        $chatId = $request->input('chat_id') ?: config('services.telegram.admin_chat_id');
        $msg = "🔔 <b>[IC3 QUEST] THỬ NGHIỆM KẾT NỐI THÀNH CÔNG!</b>\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "🤖 <b>Tên Bot:</b> @trikun_cdphp_bot\n";
        $msg .= "⏱️ <b>Thời gian gửi:</b> " . now()->format('d/m/Y H:i:s') . "\n";
        $msg .= "⚡ <i>Hệ thống thông báo Real-time và Live Chat đã sẵn sàng 100%!</i>";

        $sent = $telegramService->sendMessage($msg, null, $chatId);

        if ($sent) {
            return response()->json([
                'ok' => true,
                'message' => 'Đã gửi tin nhắn thử nghiệm thành công! Vui lòng kiểm tra Telegram của bạn.',
            ]);
        }

        return response()->json([
            'ok' => false,
            'message' => 'Chưa thể gửi tin nhắn. Vui lòng mở Telegram, bấm START vào bot @trikun_cdphp_bot rồi thử lại.',
        ], 422);
    }

    /**
     * Tự động lấy Chat ID của người dùng từ Telegram getUpdates
     */
    public function getTelegramChatId(\Illuminate\Http\Request $request, \App\Services\TelegramService $telegramService): \Illuminate\Http\JsonResponse
    {
        $token = trim($request->input('bot_token', '')) ?: config('services.telegram.bot_token');
        if (empty($token)) {
            return response()->json([
                'ok' => false,
                'message' => 'Chưa có Bot Token để kiểm tra. Vui lòng nhập Token trước.',
            ], 422);
        }

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(8)->get("https://api.telegram.org/bot{$token}/getUpdates", [
                'limit' => 20,
            ]);

            if (! $response->successful() || ! $response->json('ok')) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Lỗi kết nối Telegram API: ' . ($response->json('description') ?? 'Không rõ'),
                ], 422);
            }

            $updates = $response->json('result', []);
            if (empty($updates)) {
                return response()->json([
                    'ok' => false,
                    'empty' => true,
                    'message' => 'Chưa thấy tin nhắn mới nào gửi tới Bot. Bạn hãy mở Telegram, tìm @trikun_cdphp_bot và gửi 1 tin nhắn bất kỳ (ví dụ: "alo" hoặc bấm /start) rồi bấm lại nút này nhé!',
                ]);
            }

            // Lấy update mới nhất có message
            $targetChatId = null;
            $targetName = 'Quản trị viên';
            $targetUsername = '';

            for ($i = count($updates) - 1; $i >= 0; $i--) {
                $up = $updates[$i];
                $msg = $up['message'] ?? $up['channel_post'] ?? null;
                if ($msg && ! empty($msg['chat']['id'])) {
                    $targetChatId = (string) $msg['chat']['id'];
                    $targetName = trim(($msg['from']['first_name'] ?? '') . ' ' . ($msg['from']['last_name'] ?? ''));
                    $targetUsername = $msg['from']['username'] ?? '';
                    break;
                }
            }

            if (! $targetChatId) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Không tìm thấy thông tin người gửi trong danh sách tin nhắn.',
                ], 422);
            }

            // Cập nhật file .env
            $envPath = base_path('.env');
            if (file_exists($envPath)) {
                $envContent = file_get_contents($envPath);
                if (preg_match('/^TELEGRAM_ADMIN_CHAT_ID=.*$/m', $envContent)) {
                    $envContent = preg_replace('/^TELEGRAM_ADMIN_CHAT_ID=.*$/m', 'TELEGRAM_ADMIN_CHAT_ID=' . $targetChatId, $envContent);
                } else {
                    $envContent .= "\nTELEGRAM_ADMIN_CHAT_ID=" . $targetChatId;
                }
                file_put_contents($envPath, $envContent);
            }

            // Gửi luôn 1 tin xác nhận tới Telegram
            $helloText = "🎉 <b>[IC3 QUEST] TÌM THẤY BẠN THÀNH CÔNG!</b>\n";
            $helloText .= "━━━━━━━━━━━━━━━━━━━━\n";
            $helloText .= "👋 Chào <b>" . htmlspecialchars($targetName ?: 'Admin') . "</b>!\n";
            $helloText .= "🆔 <b>Chat ID của bạn:</b> <code>{$targetChatId}</code>\n";
            $helloText .= "✅ Hệ thống đã tự động kết nối Bot và sẵn sàng thông báo đơn hàng & tin nhắn trực tiếp tới bạn!";
            $telegramService->sendMessage($helloText, null, $targetChatId);

            return response()->json([
                'ok' => true,
                'chat_id' => $targetChatId,
                'name' => $targetName,
                'username' => $targetUsername,
                'message' => "Đã nhận diện thành công! Chat ID: {$targetChatId} (" . ($targetUsername ? "@{$targetUsername}" : $targetName) . "). Đã gửi tin nhắn chào mừng đến Telegram của bạn!",
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Lỗi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Gửi phản hồi của admin tới tin nhắn tư vấn từ giao diện Admin
     */
    public function sendAdminReplyViaTelegram(\Illuminate\Http\Request $request, \App\Services\TelegramService $telegramService): \Illuminate\Http\JsonResponse
    {
        $messageId = $request->input('message_id');
        $replyText = trim($request->input('reply_text', ''));

        if (empty($replyText)) {
            return response()->json(['ok' => false, 'message' => 'Nội dung phản hồi không được để trống.'], 422);
        }

        $supportMessage = \App\Models\SupportMessage::find($messageId);
        if ($supportMessage) {
            $supportMessage->status = 'responded';
            $supportMessage->admin_reply = $replyText;
            $supportMessage->replied_at = now();
            $supportMessage->save();
        }

        // Bắn thông báo về Telegram admin để lưu nhật ký
        $teleText = "✍️ <b>[ADMIN PHẢN HỒI TIN NHẮN TƯ VẤN]</b>\n";
        $teleText .= "━━━━━━━━━━━━━━━━━━━━\n";
        if ($supportMessage) {
            $teleText .= "👤 <b>Khách hàng:</b> " . htmlspecialchars($supportMessage->name) . "\n";
            $contact = $supportMessage->phone ?: ($supportMessage->email ?: 'N/A');
            $teleText .= "📞 <b>Liên hệ:</b> <code>" . htmlspecialchars($contact) . "</code>\n";
            $teleText .= "💬 <b>Câu hỏi của khách:</b> <i>" . htmlspecialchars($supportMessage->message) . "</i>\n";
            $teleText .= "━━━━━━━━━━━━━━━━━━━━\n";
        }
        $teleText .= "💬 <b>Nội dung Admin phản hồi:</b>\n" . htmlspecialchars($replyText);

        $telegramService->sendMessage($teleText);

        return response()->json([
            'ok' => true,
            'message' => 'Đã lưu phản hồi và gửi thông báo tới Telegram!',
            'reply' => $replyText,
        ]);
    }

    /**
     * Xuất danh sách kết quả bài thi ra tệp CSV (Excel-ready)
     */
    public function exportCsv(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $user = auth()->user();
        $isTeacher = $user?->isTeacher() ?? false;

        $query = TestAttempt::query()->with(['user.classroom', 'practiceTest.topic.level'])->latest('id');

        if ($isTeacher) {
            $classIds = Classroom::where('teacher_id', $user->id)->pluck('id')->all();
            $query->whereHas('user', function ($q) use ($user, $classIds) {
                $q->where('created_by', $user->id)
                  ->orWhereIn('classroom_id', $classIds);
            });
        }

        $attempts = $query->get();
        $filename = 'bang_diem_ic3_' . date('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($attempts) {
            $handle = fopen('php://output', 'w');
            // Ghi UTF-8 BOM để Excel hiển thị tiếng Việt không bị lỗi font
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'STT',
                'Mã Học Sinh',
                'Họ và Tên',
                'Email',
                'Lớp Học',
                'Khối',
                'Chủ Đề IC3',
                'Bài Luyện',
                'Điểm Số (Thang 1000)',
                'Số Câu Đúng',
                'Tổng Số Câu',
                'Thời Gian Làm (giây)',
                'Thời Điểm Nộp',
                'Xếp Loại'
            ]);

            foreach ($attempts as $idx => $a) {
                $isPassed = $a->score >= 700;
                $isPerfect = $a->score >= 1000;
                $status = $isPerfect ? 'Xuất sắc' : ($isPassed ? 'Đạt chuẩn' : 'Chưa đạt');

                fputcsv($handle, [
                    $idx + 1,
                    $a->user?->student_code ?? '',
                    $a->user?->name ?? 'Học sinh #' . $a->user_id,
                    $a->user?->email ?? '',
                    $a->user?->classroom?->name ?? '',
                    $a->user?->classroom ? 'Khối ' . $a->user->classroom->grade : '',
                    $a->practiceTest?->topic?->name ?? '',
                    $a->practiceTest?->name ?? '',
                    $a->score,
                    $a->correct_answers,
                    $a->total_questions,
                    $a->duration_seconds,
                    $a->completed_at_full_vn,
                    $status
                ]);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Xóa một lượt thi (dọn dẹp dữ liệu kiểm thử)
     */
    public function destroyAttempt(TestAttempt $attempt): \Illuminate\Http\RedirectResponse
    {
        $user = auth()->user();
        if ($user->isTeacher()) {
            $classIds = Classroom::where('teacher_id', $user->id)->pluck('id')->all();
            abort_unless(in_array($attempt->user?->classroom_id, $classIds), 403, 'Bạn không có quyền xóa kết quả của học sinh lớp khác.');
        }

        $attempt->delete();

        return redirect()->back()->with('ok', 'Đã xóa bản ghi lượt thi thành công.');
    }
}
