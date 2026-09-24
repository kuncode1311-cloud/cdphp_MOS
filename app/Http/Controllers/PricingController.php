<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderPackageRequest;
use App\Models\Package;
use App\Models\PackageOrder;
use App\Models\SupportMessage;
use App\Services\PayosService;
use App\Services\SubscriptionService;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Controller Xem Bảng Giá & Thuê Gói Dịch Vụ (Pricing & Packages)
 *
 * Chức năng:
 * 1. Hiển thị bảng giá các gói bản quyền IC3 GS6 cho giáo viên & người dùng tham khảo.
 * 2. Đặt mua / gửi yêu cầu thuê gói dịch vụ.
 * 3. Hướng dẫn chuyển khoản ngân hàng qua mã VietQR tự động & Thanh toán online PayOS.
 * 4. Xem lịch sử các gói đã thuê của tài khoản.
 * 5. Nhận tin nhắn tư vấn từ Live Chat Widget và bắn Telegram Admin.
 */
class PricingController extends Controller
{
    /**
     * Hiển thị bảng giá các gói dịch vụ
     */
    public function index(): View
    {
        // 🌟 Lấy danh sách gói Giáo viên & Nhà trường
        $teacherPackages = Package::active()
            ->forTeachers()
            ->ordered()
            ->take(6)
            ->with('levels')
            ->get();

        // 🌟 Lấy danh sách gói Học sinh & Khách lẻ tự luyện
        $studentPackages = Package::active()
            ->forStudents()
            ->ordered()
            ->take(6)
            ->with('levels')
            ->get();

        // Biến $packages giữ danh sách gói mặc định để tương thích
        $packages = $teacherPackages;

        $user = auth()->user();
        $latestOrder = $user ? $user->packageOrders()->with('package')->first() : null;

        return view('pricing.index', compact('packages', 'teacherPackages', 'studentPackages', 'latestOrder'));
    }

    /**
     * Tạo đơn đặt thuê gói dịch vụ (Dành cho tài khoản đã đăng nhập)
     */
    public function order(
        OrderPackageRequest $request,
        Package $package,
        SubscriptionService $subscriptionService,
        TelegramService $telegramService,
        PayosService $payosService
    ): RedirectResponse
    {
        $user = $request->user();

        // Học sinh không thể mua các gói quản lý lớp của giáo viên
        if ($user->isStudent() && $package->isForTeachers()) {
            return back()->with('err', 'Tài khoản học sinh không thể thuê gói giáo viên. Vui lòng chọn các gói tự luyện dành cho Học sinh.');
        }

        $data = $request->validated();
        $extraNotes = [];
        if (! empty($data['school_name'])) {
            $extraNotes[] = "Trường: {$data['school_name']}";
            $user->update(['school_name' => $data['school_name']]);
        }
        if (! empty($data['phone'])) {
            $phonePrefix = $user->isStudent() ? 'SĐT Phụ huynh' : 'SĐT';
            $extraNotes[] = "{$phonePrefix}: {$data['phone']}";
            $user->update(['phone' => $data['phone']]);
        }
        if (! empty($data['name']) && $user->name !== $data['name']) {
            $user->update(['name' => $data['name']]);
        }
        if (! empty($data['notes'])) {
            $extraNotes[] = $data['notes'];
        }

        $orderPayload = [
            'payment_method' => $data['payment_method'] ?? 'bank_transfer',
            'notes' => ! empty($extraNotes) ? implode(' · ', $extraNotes) : null,
        ];

        $order = $subscriptionService->createOrder($user, $package, $orderPayload);

        // Bắn thông báo Telegram cho Ban Quản Trị
        $telegramService->sendOrderNotification($order);

        // Tạo liên kết ngầm PayOS nếu cần webhook tự động, KHÔNG chuyển hướng ra trang ngoài
        if ($order->payment_method === 'payos') {
            try {
                $payosResult = $payosService->createPaymentLink(
                    $order,
                    route('pricing.payos.return', $order),
                    route('pricing.order.checkout', $order)
                );
                if (! empty($payosResult['ok'])) {
                    Cache::put("order_payos_{$order->id}", $payosResult, now()->addDay());
                }
            } catch (\Throwable $e) {
                Log::warning('PayOS link create notice: ' . $e->getMessage());
            }
        }

        $paymentDetails = $this->getPaymentDetails($order);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'order' => [
                    'id' => $order->id,
                    'code' => $order->code,
                    'package_name' => $paymentDetails['cleanPkgName'],
                    'price' => $order->price,
                    'price_formatted' => number_format($order->price) . ' đ',
                    'status' => $order->status,
                ],
                'bank' => $paymentDetails['bankConfig'],
                'qr_url' => $paymentDetails['vietQrUrl'],
                'transfer_content' => $paymentDetails['transferContent'],
                'checkout_url' => route('pricing.order.checkout', $order),
            ]);
        }

        return redirect()->route('pricing.order.checkout', $order)
            ->with('ok', "Đã tạo đơn đăng ký gói \"{$paymentDetails['cleanPkgName']}\".");
    }

    /**
     * Đăng ký tài khoản mới (Giáo viên hoặc Học sinh) VÀ đặt thuê gói ngay lập tức
     */
    public function registerAndOrder(
        \App\Http\Requests\RegisterTeacherPackageRequest $request,
        Package $package,
        SubscriptionService $subscriptionService,
        TelegramService $telegramService,
        PayosService $payosService
    ): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $isStudentPackage = $package->isForStudents();

        // 1. Phân loại vai trò tài khoản dựa trên gói đặt mua
        $role = $isStudentPackage ? \App\Enums\UserRole::Student->value : \App\Enums\UserRole::Teacher->value;
        $maxStudents = $isStudentPackage ? 1 : 0;

        $user = \App\Models\User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'phone' => $data['phone'] ?? null,
            'school_name' => $data['school_name'] ?? null,
            'role' => $role,
            'status' => 'pending', // Chờ thanh toán & kích hoạt đơn hàng
            'max_students' => $maxStudents,
            'expires_at' => null,
        ]);

        // 2. Tạo đơn hàng thuê gói
        $extraNotes = [];
        if (! empty($data['school_name'])) {
            $extraNotes[] = "Trường/Đơn vị: {$data['school_name']}";
        }
        if (! empty($data['phone'])) {
            $phonePrefix = $isStudentPackage ? 'SĐT Phụ huynh' : 'SĐT';
            $extraNotes[] = "{$phonePrefix}: {$data['phone']}";
        }
        if (! empty($data['notes'])) {
            $extraNotes[] = "Ghi chú: {$data['notes']}";
        }

        $order = $subscriptionService->createOrder($user, $package, [
            'payment_method' => $data['payment_method'] ?? 'bank_transfer',
            'notes' => implode(' · ', $extraNotes),
        ]);

        // Bắn thông báo Telegram cho Ban Quản Trị
        $telegramService->sendOrderNotification($order);

        // Tạo liên kết ngầm PayOS nếu cần webhook tự động, KHÔNG chuyển hướng ra trang ngoài
        if ($order->payment_method === 'payos') {
            try {
                $payosResult = $payosService->createPaymentLink(
                    $order,
                    route('pricing.payos.return', $order),
                    route('pricing.order.checkout', $order)
                );
                if (! empty($payosResult['ok'])) {
                    Cache::put("order_payos_{$order->id}", $payosResult, now()->addDay());
                }
            } catch (\Throwable $e) {
                Log::warning('PayOS link create notice: ' . $e->getMessage());
            }
        }

        $paymentDetails = $this->getPaymentDetails($order);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'order' => [
                    'id' => $order->id,
                    'code' => $order->code,
                    'package_name' => $paymentDetails['cleanPkgName'],
                    'price' => $order->price,
                    'price_formatted' => number_format($order->price) . ' đ',
                    'status' => $order->status,
                ],
                'bank' => $paymentDetails['bankConfig'],
                'qr_url' => $paymentDetails['vietQrUrl'],
                'transfer_content' => $paymentDetails['transferContent'],
                'checkout_url' => route('pricing.order.checkout', $order),
            ]);
        }

        $welcomeMsg = $isStudentPackage
            ? "Chào mừng bạn {$user->name}! Tài khoản Học sinh của bạn đã được tạo thành công."
            : "Chào mừng Thầy/Cô {$user->name}! Tài khoản Giáo viên của Thầy/Cô đã được tạo thành công.";

        return redirect()->route('pricing.order.checkout', $order)
            ->with('ok', $welcomeMsg);
    }

    /**
     * Tạo link thanh toán PayOS từ trang checkout nếu khách muốn thanh toán online
     */
    public function createPayosLink(PackageOrder $order, PayosService $payosService): RedirectResponse
    {
        $user = auth()->user();
        if ($user && $order->user_id !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Bạn không có quyền thực hiện thao tác này.');
        }

        $payosResult = $payosService->createPaymentLink(
            $order,
            route('pricing.payos.return', $order),
            route('pricing.order.checkout', $order)
        );

        if ($payosResult['ok'] && ! empty($payosResult['checkoutUrl'])) {
            $order->update(['payment_method' => 'payos']);
            return redirect()->away($payosResult['checkoutUrl']);
        }

        return back()->with('err', $payosResult['message'] ?? 'Không thể kết nối cổng PayOS vào lúc này. Vui lòng chuyển khoản theo mã VietQR.');
    }

    /**
     * Xử lý khi khách hoàn tất thanh toán PayOS và được chuyển hướng về web
     */
    public function payosReturn(Request $request, PackageOrder $order, SubscriptionService $subscriptionService, TelegramService $telegramService): RedirectResponse
    {
        $status = $request->query('status', '');
        $code = $request->query('code', '');

        if ($status === 'PAID' || $code === '00') {
            if ($order->status === PackageOrder::STATUS_PENDING) {
                $subscriptionService->activateOrder($order);
                $telegramService->sendPaymentSuccessNotification($order);
            }
            return redirect()->route('pricing.order.checkout', $order)
                ->with('ok', "Thanh toán PayOS thành công! Gói bản quyền của Thầy/Cô đã được kích hoạt.");
        }

        return redirect()->route('pricing.order.checkout', $order)
            ->with('info', "Giao dịch PayOS đã hoàn thành. Nếu đã trừ tiền tài khoản, gói của Thầy/Cô sẽ được tự động kích hoạt trong giây lát.");
    }

    /**
     * Webhook tự động kích hoạt từ cổng thanh toán PayOS
     */
    public function payosWebhook(Request $request, PayosService $payosService, SubscriptionService $subscriptionService, TelegramService $telegramService): JsonResponse
    {
        Log::info('PayOS Webhook received payload: ', $request->all());

        $data = $payosService->verifyWebhookData($request->all());

        if (! $data) {
            Log::warning('PayOS Webhook verification failed.');
            return response()->json(['success' => false, 'message' => 'Invalid signature'], 400);
        }

        $order = null;
        if (! empty($data['orderCode'])) {
            $codeStr = (string) $data['orderCode'];
            if (strlen($codeStr) > 6) {
                $candidateId = (int) substr($codeStr, 0, -6);
                if ($candidateId > 0) {
                    $order = PackageOrder::find($candidateId);
                }
            }
        }

        // Fallback: Tìm theo description hoặc mã code
        if (! $order && ! empty($data['description'])) {
            $rawDesc = trim((string)$data['description']);
            $cleanLettersOnly = preg_replace('/[^A-Za-z0-9]/', '', $rawDesc);
            $order = PackageOrder::where('status', PackageOrder::STATUS_PENDING)
                ->get()
                ->first(function ($o) use ($cleanLettersOnly) {
                    $orderLetters = preg_replace('/[^A-Za-z0-9]/', '', $o->code);
                    return str_contains($cleanLettersOnly, $orderLetters) || str_contains($orderLetters, $cleanLettersOnly);
                });
        }

        if ($order) {
            if ($order->status === PackageOrder::STATUS_PENDING) {
                $subscriptionService->activateOrder($order);
                $telegramService->sendPaymentSuccessNotification($order);
                Log::info("PayOS Webhook: Kích hoạt thành công đơn hàng #{$order->code} (ID: {$order->id})");
            } else {
                Log::info("PayOS Webhook: Đơn hàng #{$order->code} đã được kích hoạt trước đó.");
            }
        } else {
            Log::warning("PayOS Webhook: Không tìm thấy đơn hàng pending tương ứng.", $data);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Nhận tin nhắn tư vấn từ Live Chat Widget và chuyển tiếp về Telegram Admin
     */
    public function sendSupportMessage(Request $request, TelegramService $telegramService): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'contact' => 'nullable|string|max:120',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:120',
            'message' => 'required|string|max:2000',
        ]);

        if (empty($data['phone']) && ! empty($data['contact'])) {
            $data['phone'] = $data['contact'];
        }

        $data['ip_address'] = $request->ip();

        // 🔄 Cơ chế nhận diện phiên chat thông minh 2 lớp:
        // Lớp 1: Khóa theo mã phiên trình duyệt (parent_id) nếu phiên đó chưa đóng và còn trong 24h
        // Lớp 2: Khóa theo Số điện thoại nếu cùng SĐT tương tác trong vòng 24h gần nhất (tính từ tin nhắn cuối - updated_at)
        $parentId = (int) $request->input('parent_id', 0);
        $supportMsg = null;

        if ($parentId > 0) {
            $supportMsg = SupportMessage::where('id', $parentId)
                ->where('status', '!=', 'closed')
                ->where('updated_at', '>=', now()->subHours(24))
                ->first();
        }

        if (! $supportMsg && ! empty($data['phone'])) {
            $supportMsg = SupportMessage::where('phone', $data['phone'])
                ->where('status', '!=', 'closed')
                ->where('updated_at', '>=', now()->subHours(24))
                ->latest('id')
                ->first();
        }

        $incomingMessage = trim((string) ($data['message'] ?? ''));
        if ($incomingMessage === 'undefined' || empty($incomingMessage)) {
            $incomingMessage = 'Khách gửi yêu cầu tư vấn gói luyện thi IC3.';
        }

        if ($supportMsg) {
            // Nối thêm tin nhắn vào cuộc hội thoại hiện có (lọc bỏ undefined nếu có)
            $existingMsg = trim((string) $supportMsg->message);
            if ($existingMsg === 'undefined' || empty($existingMsg)) {
                $supportMsg->message = $incomingMessage;
            } else {
                $supportMsg->message = $existingMsg . "\n" . $incomingMessage;
            }
            if (! empty($data['name']) && (empty($supportMsg->name) || $supportMsg->name === 'Khách vãng lai')) {
                $supportMsg->name = $data['name'];
            }
            if (! empty($data['phone']) && empty($supportMsg->phone)) {
                $supportMsg->phone = $data['phone'];
            }
            $supportMsg->status = 'pending';
            $supportMsg->appendConversationTurn('user', $incomingMessage);
            $supportMsg->updated_at = now();
            $supportMsg->save();
        } else {
            $supportMsg = new SupportMessage([
                'name' => $data['name'],
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'message' => $incomingMessage,
                'ip_address' => $data['ip_address'],
                'status' => 'pending',
            ]);
            $supportMsg->appendConversationTurn('user', $incomingMessage);
            $supportMsg->save();
        }

        $telegramService->sendSupportMessageNotification(
            $data['name'],
            $data['phone'] ?? null,
            $data['email'] ?? null,
            $data['message'],
            $supportMsg->id
        );

        $tz = config('learning.display_timezone', 'Asia/Ho_Chi_Minh');

        return response()->json([
            'ok' => true,
            'message_id' => $supportMsg->id,
            'time' => now()->setTimezone($tz)->format('H:i'),
            'conversation_history' => $supportMsg->conversation_history ?? [],
            'message' => 'Cảm ơn bạn! Ban Quản Trị đã nhận được tin nhắn và sẽ phản hồi ngay tại đây.',
        ]);
    }

    /**
     * Kiểm tra phản hồi từ Ban Quản Trị theo thời gian thực (Real-time Live Chat Widget)
     */
    public function checkSupportMessageReply(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        $id = (int) $request->query('id', 0);
        if ($id <= 0) {
            return response()->json(['ok' => false, 'message' => 'Thiếu mã tin nhắn.']);
        }

        $msg = SupportMessage::find($id);
        if (! $msg) {
            return response()->json(['ok' => false, 'message' => 'Không tìm thấy cuộc trò chuyện.']);
        }

        return response()->json([
            'ok' => true,
            'id' => $msg->id,
            'status' => $msg->status,
            'sender_name' => $msg->name,
            'contact' => $msg->phone ?? $msg->email ?? '',
            'admin_reply' => $msg->admin_reply,
            'conversation_history' => $msg->conversation_history ?? [],
            'replied_at' => $msg->replied_at ? \Illuminate\Support\Carbon::parse($msg->replied_at)->format('H:i') : null,
        ]);
    }

    /**
     * Lấy toàn bộ thông tin thanh toán VietQR và cấu hình ngân hàng
     */
    public function getPaymentDetails(PackageOrder $order): array
    {
        $cachedPayos = Cache::get("order_payos_{$order->id}");

        if ($order->payment_method === 'payos' && $cachedPayos && ! empty($cachedPayos['ok']) && ! empty($cachedPayos['qrCode'])) {
            $qr = $cachedPayos['qrCode'];
            $bankBin = ! empty($cachedPayos['bin']) ? $cachedPayos['bin'] : '970452';
            $accountNo = ! empty($cachedPayos['accountNumber']) ? $cachedPayos['accountNumber'] : '10142609193448408';
            $bankName = 'Ngân hàng TMCP Kiên Long (KienlongBank)';
            $accountName = ! empty($cachedPayos['accountName']) ? $cachedPayos['accountName'] : config('payment.account_name', 'LE MINH TRI');

            if (preg_match('/0006([0-9]{6})01([0-9]{2})([0-9]+)0208QRIBFTTA/', $qr, $m)) {
                $bankBin = $m[1];
                $accountNo = $m[3];
                if ($bankBin === '970452') {
                    $bankName = 'Ngân hàng TMCP Kiên Long (KienlongBank)';
                }
            }

            $transferContent = 'MOS ' . substr(preg_replace('/[^a-zA-Z0-9]/', '', $order->code), -15);
            if (preg_match('/62[0-9]{2}08[0-9]{2}(MOS[a-zA-Z0-9\s]+)6304/', $qr, $mDesc)) {
                $transferContent = trim($mDesc[1]);
            }

            $bankConfig = [
                'bank_id' => $bankBin,
                'bank_name' => $bankName,
                'account_no' => $accountNo,
                'account_name' => $accountName,
            ];

            $vietQrUrl = sprintf(
                'https://img.vietqr.io/image/%s-%s-compact2.png?amount=%d&addInfo=%s&accountName=%s',
                $bankConfig['bank_id'],
                $bankConfig['account_no'],
                $order->price,
                rawurlencode($transferContent),
                rawurlencode($bankConfig['account_name'])
            );
        } else {
            $bankConfig = [
                'bank_id' => config('payment.bank_id', 'MB'),
                'bank_name' => config('payment.bank_name', 'MB Bank (Ngân Hàng Quân Đội)'),
                'account_no' => config('payment.bank_account', '0345151438'),
                'account_name' => config('payment.account_name', 'LE MINH TRI'),
            ];

            $transferContent = "MOS {$order->code}";
            $vietQrUrl = sprintf(
                'https://img.vietqr.io/image/%s-%s-compact2.png?amount=%d&addInfo=%s&accountName=%s',
                $bankConfig['bank_id'],
                $bankConfig['account_no'],
                $order->price,
                rawurlencode($transferContent),
                rawurlencode($bankConfig['account_name'])
            );
        }

        $cleanPkgName = preg_replace('/\s*\((Starter|Standard|Pro School)\)\s*/i', '', $order->package_name);

        return [
            'bankConfig' => $bankConfig,
            'vietQrUrl' => $vietQrUrl,
            'transferContent' => $transferContent,
            'cleanPkgName' => $cleanPkgName,
        ];
    }

    /**
     * Trang hướng dẫn chuyển khoản & kiểm tra trạng thái đơn hàng (Checkout / VietQR)
     */
    public function checkout(PackageOrder $order): View|RedirectResponse
    {
        $user = auth()->user();
        if ($user && $order->user_id !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Bạn không có quyền xem đơn hàng này.');
        }

        $order->load(['package.levels', 'user']);
        $details = $this->getPaymentDetails($order);

        $bankConfig = $details['bankConfig'];
        $vietQrUrl = $details['vietQrUrl'];
        $transferContent = $details['transferContent'];
        $cleanPkgName = $details['cleanPkgName'];

        return view('pricing.checkout', compact('order', 'bankConfig', 'vietQrUrl', 'transferContent', 'cleanPkgName'));
    }

    /**
     * Kiểm tra trạng thái đơn hàng theo thời gian thực (Real-time Polling cho Popup/Checkout)
     */
    public function checkOrderStatus(PackageOrder $order): JsonResponse
    {
        $isActive = $order->isActive();

        // Tự động đăng nhập cho khách nếu đơn hàng đã được kích hoạt thành công
        if ($isActive && auth()->guest() && $order->user && $order->user->status === 'active') {
            auth()->login($order->user);
        }

        $redirectUrl = route('programs');
        if (auth()->check()) {
            $redirectUrl = auth()->user()->canAccessAdmin() ? route('admin.dashboard') : route('programs');
        } elseif ($order->user) {
            $redirectUrl = route('login') . '?registered=1&email=' . urlencode($order->user->email);
        }

        return response()->json([
            'status' => $order->status,
            'is_active' => $isActive,
            'activated_at' => $order->activated_at ? $order->activated_at->format('H:i d/m/Y') : null,
            'redirect_url' => $redirectUrl,
        ]);
    }

    /**
     * Khách hàng bấm nút "Tôi đã chuyển khoản" để thông báo Admin qua Telegram
     */
    public function confirmTransferred(PackageOrder $order, TelegramService $telegramService): JsonResponse
    {
        if ($order->isPending()) {
            $user = $order->user;
            $amt = number_format($order->price) . ' đ';
            $text = "⚡ <b>[KHÁCH BÁO ĐÃ CHUYỂN KHOẢN XONG]</b>\n";
            $text .= "━━━━━━━━━━━━━━━━━━━━\n";
            $text .= "🏷️ <b>Đơn hàng:</b> <code>#{$order->code}</code> (ID: {$order->id})\n";
            $text .= "👩‍🏫 <b>Giáo viên:</b> <b>" . htmlspecialchars($user?->name ?? 'Khách') . "</b>\n";
            if ($user?->phone) {
                $text .= "📞 <b>Điện thoại:</b> <code>" . htmlspecialchars($user->phone) . "</code>\n";
            }
            $text .= "📦 <b>Gói:</b> " . htmlspecialchars($order->package_name) . "\n";
            $text .= "💰 <b>Số tiền:</b> <b>{$amt}</b>\n";
            $text .= "⏱️ Thầy/Cô vừa bấm xác nhận <b>'Tôi đã chuyển khoản'</b> trên web.\n";
            $text .= "━━━━━━━━━━━━━━━━━━━━\n";
            $text .= "👉 Thầy/Cô vui lòng kiểm tra tài khoản và bấm nút duyệt dưới đây:";

            $cleanPhone = $user?->phone ? preg_replace('/[^0-9]/', '', $user->phone) : null;
            $buttons = [
                [
                    ['text' => "⚡ Duyệt Kích Hoạt #{$order->id}", 'callback_data' => "act_ord_{$order->id}"],
                    ['text' => "🔍 Chi Tiết", 'callback_data' => "view_ord_{$order->id}"]
                ]
            ];
            if ($cleanPhone) {
                $buttons[] = [
                    ['text' => "💬 Chat Zalo GV ({$cleanPhone})", 'url' => "https://zalo.me/{$cleanPhone}"]
                ];
            }

            $telegramService->sendMessage($text, ['inline_keyboard' => $buttons]);
        }

        return response()->json([
            'ok' => true,
            'message' => 'Đã gửi thông báo xác nhận chuyển khoản đến Ban Quản Trị để kích hoạt ngay!',
        ]);
    }

    /**
     * Xem lịch sử đơn thuê gói của tôi
     */
    public function history(): \Illuminate\View\View|\Illuminate\Http\RedirectResponse
    {
        $user = auth()->user();
        if ($user && $user->canAccessAdmin()) {
            return redirect()->to(route('admin.dashboard') . '#tab-teacher-packages');
        }

        $orders = $user->packageOrders()
            ->with('package.levels')
            ->latest('id')
            ->paginate(10);

        return view('pricing.history', compact('orders'));
    }
}
