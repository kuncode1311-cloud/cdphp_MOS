<?php

namespace App\Services;

use App\Mail\PasswordChangedAlertMail;
use App\Mail\PasswordResetOtpMail;
use App\Models\Package;
use App\Models\PackageOrder;
use App\Models\SupportMessage;
use App\Models\User;
use App\Support\VietText;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Trợ lý AI trong khung chat hỗ trợ.
 *
 * - Trả lời câu hỏi chung theo tài liệu và gói bản quyền (mọi khách).
 * - Thành viên có gói Trợ lý AI còn hạn: tra cứu kết quả và tiến độ của chính mình, mở trang, mở bài thi.
 * - Hướng dẫn khôi phục mật khẩu: xác nhận đúng email đã đăng ký, gửi OTP tới email đó, rồi cho đặt mật khẩu mới.
 *
 * Bảo mật:
 * - Trạng thái khôi phục lưu trong session (cookie) của đúng trình duyệt đã bắt đầu.
 * - Tên hoặc số điện thoại không đủ để đặt lại mật khẩu. Chỉ OTP gửi tới email mới chứng minh quyền sở hữu.
 * - Mã OTP và mật khẩu mới không được lưu vào lịch sử chat, không gửi Telegram, và không ghi log.
 */
class SupportBotService
{
    private const SESSION_KEY = 'support_bot_flow';
    private const RECENT_RECOVERY_KEY = 'support_bot_recent_recovery';
    private const ADMIN_SEEN_KEY = 'support_admin_seen_at';
    private const FLOW_MINUTES = 15;
    private const OTP_MINUTES = 10;
    private const OTP_MAX_ATTEMPTS = 5;
    /** Tìm lại tài khoản bằng câu hỏi xác minh: đủ thoải mái để sửa thông tin, vẫn chống dò tài khoản hàng loạt. */
    private const FIND_MAX_ATTEMPTS = 3;
    private const FIND_LOCK_MINUTES = 10;
    /** Bật/tắt giới hạn lượt AI. Đang tắt để chat thoải mái (không giới hạn); đặt true để bật lại. */
    private const AI_LIMIT_ENABLED = false;
    /** Số lượt AI tối đa trong một giờ cho mỗi cuộc trò chuyện và mỗi IP (chỉ có hiệu lực khi AI_LIMIT_ENABLED = true). */
    private const AI_LIMIT_CONVERSATION = 50;
    private const AI_LIMIT_IP = 300;
    /** Thời gian bot ghi nhớ yêu cầu gặp người thật trong một cuộc trò chuyện */
    private const HANDOFF_HOURS = 12;
    private const SUPPORT_ADMIN_NICKNAME = 'Admin Trí Kun đẹp zai cute phô mai que';
    private const SUPPORT_ZALO_PHONE = '0345151438';

    public function __construct(
        private AiChatClient $client,
        private AiAssistantService $assistant,
    ) {
    }

    /** Ghi nhận Admin đang online. Gọi từ endpoint poll của trang quản trị. */
    public static function markAdminActive(): void
    {
        Cache::put(self::ADMIN_SEEN_KEY, now()->timestamp, now()->addSeconds(60));
    }

    public static function adminOnline(): bool
    {
        return Cache::has(self::ADMIN_SEEN_KEY);
    }

    /**
     * Tin nhắn này có phải đang khôi phục mật khẩu (hoặc đang trong luồng đó) không.
     */
    public function wantsRecovery(Request $request, string $text): bool
    {
        return $this->activeFlow($request) !== null || $this->looksLikeRecovery($text);
    }

    /** Bước hiện tại yêu cầu nhập thông tin nhạy cảm (OTP, mật khẩu mới): không được lưu nguyên văn. */
    public function isSecretStep(Request $request): bool
    {
        $flow = $this->activeFlow($request);

        return $flow !== null && in_array($flow['step'], ['otp', 'password'], true);
    }

    /**
     * Xử lý một tin nhắn ở kênh Trợ lý AI (tin đã được ghi vào hội thoại nếu là thành viên).
     * Thứ tự: bước khôi phục đang dở → bắt đầu khôi phục → yêu cầu gặp người → trả lời bằng AI.
     *
     * @return array{replies: string[], secret_next: bool, flow_active: bool, handoff?: bool, action?: ?array}
     */
    public function handle(Request $request, SupportMessage $msg, string $text): array
    {
        $flow = $this->activeFlow($request);

        if ($flow !== null) {
            return $this->continueRecovery($request, $msg, $flow, $text);
        }

        if ($this->looksLikeRecovery($text)) {
            return $this->startRecovery($request, $msg);
        }

        // Sau khi người dùng đã hủy/thoát luồng, các câu như "làm lại đi" hoặc nhập email tiếp
        // vẫn phải quay về flow xác minh chính thức, không để AI suy diễn theo lịch sử chat cũ.
        if ($this->looksLikeRecoveryContinuation($request, $msg, $text)) {
            return $this->startRecovery($request, $msg);
        }

        // Khách muốn gặp người thật: chỉ thành viên đã đăng nhập mới có kênh Ban Quản Trị (khách vãng lai không lưu chat)
        if ($this->looksLikeHandoff($text)) {
            if (! $msg->exists) {
                return $this->finish($msg, [$this->guestHandoffNotice()], false, false);
            }

            Cache::put($this->handoffKey($msg), true, now()->addHours(self::HANDOFF_HOURS));

            $result = $this->finish($msg, [$this->handoffNotice()], false, false);
            $result['handoff'] = true;

            return $result;
        }

        if ($msg->exists && $this->handoffActive($msg)) {
            return $this->finish($msg, [$this->handoffNotice()], false, false);
        }

        $answer = $this->answerWithAi($request, $msg, $text);
        if ($answer === null) {
            return $this->finish($msg, ['Xin lỗi, lúc này mình chưa trả lời được. Bạn thử hỏi lại sau ít phút hoặc chuyển sang tab 👨‍💼 Ban Quản Trị nhé.'], false, false);
        }

        return $this->finish($msg, [$answer['text']], false, false, $answer['action']);
    }

    // =========================================================================
    // 🤖 TRẢ LỜI BẰNG AI
    // =========================================================================

    /**
     * Chọn cách trả lời: Trợ lý AI đầy đủ (thành viên có gói), hoặc hướng dẫn chung (mọi khách).
     *
     * @return array{text: string, action: ?array}|null
     */
    private function answerWithAi(Request $request, SupportMessage $msg, string $text): ?array
    {
        if (! $this->aiBudgetAvailable($request, $msg)) {
            return ['text' => 'Bạn đã hỏi khá nhiều trong thời gian ngắn. Mình đã ghi nhận tin nhắn, ' . self::SUPPORT_ADMIN_NICKNAME . ' sẽ trực tiếp trả lời bạn sớm nhất nhé!', 'action' => null];
        }

        $user = $request->user();
        $asksAccount = $this->looksLikeAccountQuestion($text);

        if ($this->looksLikePricingQuestion($text)) {
            $reply = $this->agentReplyForGate($text, 'Khách đang quan tâm mua hoặc xem gói bản quyền. Tư vấn ngắn gọn: PayOS là QR thanh toán online và tự kích hoạt gói sau khi thanh toán thành công; chuyển khoản thủ công thì chờ Ban Quản Trị duyệt. Mời bấm nút bên dưới để xem bảng giá, không viết URL thô.');

            return ['text' => $reply, 'action' => ['label' => 'Xem bảng giá', 'url' => route('pricing.index'), 'auto' => $this->wantsAutoOpen($text)]];
        }

        // Hỏi về tài khoản/kết quả nhưng chưa có quyền: hướng dẫn đăng nhập hoặc mua gói, không tra dữ liệu
        if ($asksAccount && ! $user) {
            $reply = $this->agentReplyForGate($text, 'Khách chưa đăng nhập nên chưa thể tra cứu dữ liệu cá nhân. Hãy mời khách đăng nhập để mình xem kết quả và tiến độ học tập.', true);

            return ['text' => $reply, 'action' => ['label' => 'Đăng nhập', 'url' => route('login'), 'auto' => $this->wantsAutoOpen($text)]];
        }
        if ($asksAccount && ! $user->hasAiAssistant()) {
            $reply = $this->agentReplyForGate($text, 'Tài khoản chưa có gói Trợ lý AI còn hạn nên chưa thể tra cứu dữ liệu học tập tự động. Hãy giải thích ngắn gọn và mời xem gói.', true);

            return ['text' => $reply, 'action' => ['label' => 'Xem gói Trợ lý AI', 'url' => route('pricing.index'), 'auto' => $this->wantsAutoOpen($text)]];
        }

        $turns = $this->turnsFrom($msg);
        if ($turns === []) {
            return null;
        }

        if ($user && $user->hasAiAssistant()) {
            $result = $this->assistant->answer($user, $turns, $this->faq());
            if ($result !== null) {
                $this->countAiUse($request, $msg);

                return $result;
            }
        }

        $answer = $this->client->complete($this->systemPrompt(), $turns);
        if ($answer === null) {
            return null;
        }

        $this->countAiUse($request, $msg);

        return ['text' => $answer, 'action' => null];
    }

    /** Lượt hội thoại gần nhất để AI nhớ ngữ cảnh. Lượt cuối phải là của khách. */
    private function turnsFrom(SupportMessage $msg): array
    {
        $turns = [];
        foreach (array_slice(is_array($msg->conversation_history) ? $msg->conversation_history : [], -20) as $turn) {
            $text = trim((string) ($turn['text'] ?? ''));
            if ($text !== '') {
                $turns[] = ['role' => ($turn['sender'] ?? '') === 'user' ? 'user' : 'assistant', 'text' => $text];
            }
        }

        return $turns !== [] && end($turns)['role'] === 'user' ? $turns : [];
    }

    private function looksLikeAccountQuestion(string $text): bool
    {
        // Chỉ bắt câu hỏi về dữ liệu của người hỏi (kết quả, bài đã làm, tiến độ), không bắt câu hỏi chung như "cách làm bài thi"
        return (bool) preg_match(
            '/k[eêế]t\s*qu[aả]|ti[eế]n\s*độ|(đ[aã]|ch[uư]a|r[oồ]i)\s*l[aà]m\s*(b[aà]i|đ[eề])|l[aà]m\s*(b[aà]i|đ[eề])\s*(n[aà]o|g[iì]|ch[uư]a|r[oồ]i)'
            . '|đi[eể]m\s*(c[uủ]a|s[oố])\s*(t[oô]i|con|em|m[iì]nh)|con\s*(t[oô]i|m[iì]nh)|h[oọ]c\s*sinh\s*c[uủ]a|t[eệ]\s*nh[aấ]t|gi[oỏ]i\s*nh[aấ]t'
            . '|g[oó]i\s*(c[uủ]a|đang\s*d[uù]ng|hi[eệ]n\s*t[aạ]i)|h[aạ]n\s*(d[uù]ng|g[oó]i)|t[aà]i\s*kho[aả]n\s*(c[uủ]a|t[oô]i|m[iì]nh)|qu[aả]n\s*l[yý]|quan\s*ly/iu',
            $text
        );
    }

    private function looksLikePricingQuestion(string $text): bool
    {
        return (bool) preg_match('/mua\s*g[oó]i|thu[eê]\s*g[oó]i|b[aả]ng\s*gi[aá]|gi[aá]\s*(g[oó]i|bao\s*nhi[eê]u)|thanh\s*to[aá]n|payos|vietqr|đăng\s*k[yý]\s*g[oó]i|dang\s*ky\s*goi/iu', $text);
    }

    private function wantsAutoOpen(string $text): bool
    {
        return (bool) preg_match('/m[oơở]\s*|open|v[aà]o\s*|chuy[eể]n\s*|đưa\s*|dua\s*|t[oớ]i\s*|sang\s*|l[aà]m\s*b[aà]i/iu', $text);
    }

    private function agentReplyForGate(string $text, string $policy, bool $deterministic = false): string
    {
        if ($deterministic) {
            // Đây là câu chặn quyền/bảo mật nên trả lời cố định theo chính sách, không phụ thuộc AI.
            return $policy;
        }

        $answer = $this->client->complete(implode("\n", [
            'Bạn là agent tư vấn IC3 Adventure, nói như nhân viên hỗ trợ thật.',
            'Đọc câu người dùng, trả lời thân thiện, nhanh, đúng trọng tâm, tối đa 2 câu.',
            'Không bịa dữ liệu cá nhân, không hỏi mật khẩu/OTP, không hứa làm thay thao tác nhạy cảm.',
            'Chính sách bắt buộc: ' . $policy,
        ]), [[
            'role' => 'user',
            'text' => $text,
        ]], 120);

        return $answer ?: $policy;
    }

    /** Nội dung tài liệu hỗ trợ dùng chung cho cả hai cách trả lời */
    private function faq(): string
    {
        $faqPath = resource_path('support-bot/faq.md');

        return is_file($faqPath) ? trim((string) file_get_contents($faqPath)) : '(Chưa có tài liệu.)';
    }

    /** Prompt cho khách chung: tài liệu FAQ và gói bản quyền lấy trực tiếp từ CSDL */
    private function systemPrompt(): string
    {
        $packages = Package::active()->ordered()->get(['name', 'price', 'duration_days'])
            ->map(fn (Package $p) => sprintf('- %s: %s đ, dùng %d ngày', $p->name, number_format((int) $p->price, 0, ',', '.'), (int) $p->duration_days))
            ->implode("\n");

        return implode("\n", [
            'Bạn là agent tư vấn IC3 Adventure (IC3 GS6 & Spark Quest), thay nhân viên hỗ trợ trả lời khách trên website.',
            'Xưng "mình", gọi khách là "bạn". Đọc TOÀN BỘ ngữ cảnh hội thoại, hiểu ý câu mới nhất rồi trả lời như người thật: ấm áp, nhanh, chủ động, không máy móc.',
            'Tên người hỗ trợ thật khi cần chuyển ca: ' . self::SUPPORT_ADMIN_NICKNAME . '. Nếu khách hỏi admin là ai, trả lời đúng tên này một cách vui vẻ, tự nhiên.',
            'SĐT/Zalo hỗ trợ chính thức của MOS là ' . self::SUPPORT_ZALO_PHONE . '. Khi khách cần gặp người thật, có thể đưa số này để liên hệ Zalo.',
            'Mọi câu khách gửi đều phải được suy nghĩ theo ngữ cảnh: nếu là chào hỏi thì chào ngắn; nếu là câu hỏi thì trả lời; nếu muốn thao tác thì hướng dẫn hoặc đưa bước tiếp theo; nếu mơ hồ thì hỏi lại đúng 1 câu ngắn.',
            'Trả lời gọn: thường 1-3 câu; chỉ dùng bullet khi thật sự cần liệt kê. Không lặp lại lời chào nếu đang trò chuyện dở.',
            'Chỉ dựa vào tài liệu và dữ liệu dưới đây. Nếu không có thông tin, nói thật là chưa rõ và mời khách để lại SĐT/Zalo cho ' . self::SUPPORT_ADMIN_NICKNAME . '. Tuyệt đối không bịa.',
            'An toàn: không bao giờ yêu cầu, tiết lộ hay tạo mật khẩu hoặc mã OTP; không đăng nhập giùm; không nhận thông tin thẻ ngân hàng.',
            'Tuyệt đối không được nói đã xác thực thành công, đã tìm thấy tài khoản, đã gửi OTP, hoặc email đã khớp nếu Laravel chưa trả kết quả trong flow khôi phục mật khẩu.',
            'Nếu khách quên mật khẩu hoặc đang tiếp tục chuyện quên mật khẩu, hướng dẫn họ gõ "quên mật khẩu" để bot xử lý từng bước chính thức.',
            'Không viết URL thô như mos.app/... trong nội dung trả lời. Nếu cần mở trang, hãy nói "bấm nút bên dưới" hoặc nêu tên trang.',
            'Khi tư vấn thanh toán: PayOS là QR thanh toán online, hệ thống tự kích hoạt gói sau khi thanh toán thành công; chuyển khoản thủ công/VietQR thì cần Ban Quản Trị kiểm tra và duyệt.',
            'Khách chưa đăng nhập vẫn được tư vấn và hỗ trợ: xem/chọn gói, đăng ký và thuê gói ngay ở Bảng giá, thanh toán, kiểm tra đơn, quên mật khẩu, đăng nhập, liên hệ hỗ trợ. Các trang học chỉ dành cho tài khoản đã đăng nhập, nên hướng dẫn đăng nhập trước khi vào.',
            '',
            '=== TÀI LIỆU HỖ TRỢ ===',
            $this->faq(),
            '',
            '=== DỮ LIỆU GÓI BẢN QUYỀN (CSDL) ===',
            $packages !== '' ? $packages : '- Hiện chưa có gói nào được mở bán.',
        ]);
    }

    // =========================================================================
    // 🔐 KHÔI PHỤC MẬT KHẨU QUA OTP
    // =========================================================================

    /** Yêu cầu gặp người thật: "nhân viên", "admin", "người thật", "gặp người", "gọi điện"... */
    private function looksLikeHandoff(string $text): bool
    {
        return (bool) preg_match(
            '/nh[aâ]n\s*vi[eê]n|admin|ng[uư][oờ]i\s*th[aậ]t|ng[uư][oờ]i\s*h[oỗ]\s*tr[oợ]|g[aặ]p\s*(ng[uư][oờ]i|ad)|g[oọ]i\s*(đi[eệ]n|cho)|n[oó]i\s*chuy[eệ]n\s*v[oớ]i\s*ng[uư][oờ]i/iu',
            $text
        );
    }

    private function looksLikeRecovery(string $text): bool
    {
        // Chấp nhận cả cách gõ sai dấu/thiếu chữ như "qên pass", "ko nhớ pas"
        return (bool) preg_match(
            '/q[uư]?[eêé]n\s*(m[aậ]t|pass|pas|mk|t[aà]i|m[aậ]t\s*m[aã])'
            . '|q[uư]?[eêé]n\s*(mail|email)'
            . '|(kh[oô]ng|ko|k)\s*nh[oớ]\s*(pass|pas|m[aậ]t|mk|t[aà]i|mail|email)'
            . '|m[aấ]t\s*(pass|pas|m[aậ]t\s*kh[aẩ]u)'
            . '|kh[oô]i\s*ph[uụ]c|reset\s*(m[aậ]t\s*kh[aẩ]u|pass|pas)'
            . '|(kh[oô]ng|ko)\s*(th[eể]\s*)?đ[aă]ng\s*nh[aậ]p/iu',
            $text
        );
    }

    /**
     * Nhận diện câu tiếp nối sau khi flow quên mật khẩu vừa bị hủy hoặc hết phiên.
     * Mục tiêu là chặn AI trả lời theo ngữ cảnh cũ rồi tạo cảm giác đã xác thực/gửi OTP.
     */
    private function looksLikeRecoveryContinuation(Request $request, SupportMessage $msg, string $text): bool
    {
        $norm = VietText::norm($text);
        $mentionsRecoveryAgain = $this->wantsRecoveryRestart($text)
            || (bool) preg_match('/\b(lam\s*lai|thu\s*lai|tiep\s*tuc|gui\s*otp|ma\s*otp|xac\s*thuc)\b/', $norm)
            || (bool) filter_var(trim($text), FILTER_VALIDATE_EMAIL);

        if (! $mentionsRecoveryAgain) {
            return false;
        }

        if ($request->hasSession() && (bool) $request->session()->get(self::RECENT_RECOVERY_KEY, false)) {
            return true;
        }

        foreach (array_slice(is_array($msg->conversation_history) ? $msg->conversation_history : [], -8) as $turn) {
            $turnText = VietText::norm((string) ($turn['text'] ?? ''));
            if (preg_match('/quen\s*(mat\s*khau|mail|email)|khoi\s*phuc|otp|email\s*dang\s*ky|da\s*dung\s*khoi\s*phuc|xac\s*nhan\s*dung/', $turnText)) {
                return true;
            }
        }

        return false;
    }

    private function startRecovery(Request $request, SupportMessage $msg): array
    {
        $this->saveFlow($request, ['step' => 'identify']);

        return $this->finish($msg, [
            "Mình hỗ trợ bạn khôi phục mật khẩu nhé! 🔑\n"
            . "Cách nhanh nhất là bấm nút bên dưới để mở trang **Quên mật khẩu**: nhập email/Mã HS, xác minh email hoặc SĐT, nhập OTP rồi đặt mật khẩu mới.\n"
            . "Bạn cũng có thể làm ngay trong khung chat này bằng cách nhập **email đăng nhập** hoặc **Mã HS**. Không nhớ cả hai thì gõ **\"không nhớ\"**. Gõ \"hủy\" nếu muốn dừng.",
        ], false, true, ['label' => 'Mở trang quên mật khẩu', 'url' => route('password.forgot')]);
    }

    private function continueRecovery(Request $request, SupportMessage $msg, array $flow, string $text): array
    {
        $input = trim($text);

        if (mb_strtolower($input) === 'hủy' || mb_strtolower($input) === 'huy') {
            return $this->endRecovery($request, $msg, 'Đã dừng khôi phục mật khẩu. Bạn cần gì thêm cứ nhắn mình nhé!');
        }

        if ($aiAction = $this->handleRecoveryIntentByAi($request, $msg, $flow, $input)) {
            return $aiAction;
        }

        return match ($flow['step']) {
            'identify' => $this->stepIdentify($request, $msg, $input),
            'find_name' => $this->stepFindName($request, $msg, $input),
            'find_class' => $this->stepFindClass($request, $msg, $flow, $input),
            'find_teacher' => $this->stepFindTeacher($request, $msg, $flow, $input),
            'confirm' => $this->stepConfirmEmail($request, $msg, $flow, $input),
            'otp' => $this->stepVerifyOtp($request, $msg, $flow, $input),
            'password' => $this->stepSetPassword($request, $msg, $flow, $input),
            default => $this->endRecovery($request, $msg, 'Phiên khôi phục đã hết hạn. Bạn gõ "quên mật khẩu" để bắt đầu lại nhé.'),
        };
    }

    private function stepIdentify(Request $request, SupportMessage $msg, string $input): array
    {
        if ($this->asksHowToAnswerRecovery($input)) {
            return $this->guideRecoveryStep($request, $msg, ['step' => 'identify'], $input);
        }

        // Không nhớ cả email lẫn Mã HS: chuyển sang hỏi câu xác minh để tìm lại tài khoản
        if (preg_match('/^(khong|ko|k|chang|cha)\s*(nho|biet)|^quen\s*(het|luon|ca|roi)/', VietText::norm($input))) {
            return $this->startFind($request, $msg);
        }

        // Nhận đúng các cách đăng nhập như trang đăng nhập (AuthController::store)
        $lower = mb_strtolower($input);
        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$lower])
            ->orWhere('student_code', $input)
            ->orWhereRaw('LOWER(email) = ?', [$lower . '@ic3.test'])
            ->orWhereRaw('LOWER(email) = ?', [$lower . '@student.ic3.local'])
            ->first();

        if (! $user || blank($user->email)) {
            return $this->finish($msg, [
                'Mình chưa tìm thấy tài khoản khớp với thông tin này. Bạn kiểm tra lại email hoặc Mã HS, hoặc gõ **"không nhớ"** để mình hỏi vài câu xác minh nhé.',
                'Nếu vẫn không được, bạn để lại SĐT/Zalo để ' . self::SUPPORT_ADMIN_NICKNAME . ' hỗ trợ trực tiếp.',
            ], false, true);
        }

        $this->saveFlow($request, ['step' => 'confirm', 'user_id' => $user->id]);

        return $this->finish($msg, [
            'Tài khoản này đăng ký email **' . $this->maskEmail($user->email) . '**.',
            'Để xác nhận đúng là bạn, hãy nhập **đầy đủ** địa chỉ email đó.',
        ], false, true);
    }

    // ----- Tìm lại tài khoản khi quên cả email lẫn Mã HS: hỏi họ tên, lớp, giáo viên -----

    private function startFind(Request $request, SupportMessage $msg): array
    {
        if ($this->findLocked($request)) {
            return $this->endRecovery($request, $msg, 'Bạn đã thử xác minh quá nhiều lần. ' . $this->handoffNotice());
        }
        $this->saveFlow($request, ['step' => 'find_name']);

        return $this->finish($msg, [
            'Mình cần xác minh vài thông tin để tìm đúng tài khoản nhé. Bạn có thể trả lời từng bước, mình sẽ giữ lại phần đã nhập đúng hướng.',
            'Câu 1: **Họ và tên đầy đủ** của học sinh là gì? (ví dụ: Nguyễn An Nhiên). Nếu nhớ rõ, bạn cũng có thể nhập kèm lớp hoặc SĐT trong cùng một tin.',
        ], false, true);
    }

    private function stepFindName(Request $request, SupportMessage $msg, string $input): array
    {
        if ($this->wantsRecoveryRestart($input)) {
            return $this->startFind($request, $msg);
        }

        $phone = \App\Models\SupportMessage::normalizePhone(preg_replace('/[^0-9+]/', '', $input));
        if ($phone !== null) {
            $nameOnly = trim(preg_replace('/(?:\+?84|0)[0-9\s.\-()]{8,14}/', '', $input));
            if (mb_strlen(VietText::norm($nameOnly)) >= 2) {
                return $this->revealOrFail($request, $msg, $this->findByPhone($nameOnly, $phone), ['name' => $nameOnly]);
            }

            return $this->revealOrFail($request, $msg, $this->findByPhoneOnly($phone), []);
        }

        if (preg_match('/MOS[\s-]*(\d{6})[\s-]*([A-Z0-9]{5,8})/i', $input, $m)) {
            return $this->revealOrFail($request, $msg, $this->findByOrderOnly('MOS-' . $m[1] . '-' . strtoupper($m[2])), []);
        }

        $nameAndClass = $this->splitNameAndClass($input);
        if ($nameAndClass !== null) {
            if (! empty($nameAndClass['phone'])) {
                return $this->revealOrFail($request, $msg, $this->findByPhone($nameAndClass['name'], $nameAndClass['phone']), ['name' => $nameAndClass['name']]);
            }

            if (! empty($nameAndClass['order_code'])) {
                return $this->revealOrFail($request, $msg, $this->findByOrder($nameAndClass['name'], $nameAndClass['order_code']), ['name' => $nameAndClass['name']]);
            }

            if (! empty($nameAndClass['teacher'])) {
                return $this->revealOrFail(
                    $request,
                    $msg,
                    $this->findByIdentity($nameAndClass['name'], $nameAndClass['class'], $nameAndClass['teacher']),
                    ['name' => $nameAndClass['name'], 'class' => $nameAndClass['class']]
                );
            }

            $this->saveFlow($request, [
                'step' => 'find_teacher',
                'name' => $nameAndClass['name'],
                'class' => $nameAndClass['class'],
            ]);

            return $this->finish($msg, [
                'Mình đã ghi nhận họ tên **' . $nameAndClass['name'] . '** và lớp **' . $nameAndClass['class'] . '**.',
                'Câu cuối: Tên **thầy/cô giáo** quản lý tài khoản của em là gì?',
            ], false, true);
        }

        $nameCandidates = $this->findNameCandidates($input);
        if ($nameCandidates === [] && $this->looksLikeFullStudentName($input)) {
            $this->saveFlow($request, ['step' => 'find_class', 'name' => trim($input)]);

            return $this->finish($msg, [
                'Mình đã ghi nhận họ tên **' . trim($input) . '**.',
                "Bạn cho mình thêm **lớp** (ví dụ: 3A1), hoặc nếu tự mua gói thì nhập **mã đơn hàng** / **SĐT đã đăng ký** để xác minh đúng tài khoản nhé.",
            ], false, true);
        }

        if ($nameCandidates === []) {
            return $this->replyRecoveryProblem(
                $request,
                $msg,
                ['step' => 'find_name'],
                $input,
                'Khách nhập tên "' . $input . '" nhưng hệ thống chưa thấy tài khoản học sinh nào khớp gần đúng. Hãy nói mềm rằng chưa thấy tên này và xin thêm họ tên đầy đủ, lớp hoặc SĐT đăng ký.'
            );
        }

        $this->saveFlow($request, ['step' => 'find_class', 'name' => trim($input)]);

        return $this->finish($msg, [
            'Mình thấy có tài khoản có tên gần giống **' . trim($input) . '** trong hệ thống.',
            'Gợi ý gần nhất: ' . implode('; ', $this->candidateHints($nameCandidates)) . '.',
            "Bạn cho mình thêm **lớp** (ví dụ: 3A1), hoặc nếu tự mua gói thì nhập **mã đơn hàng** / **SĐT đã đăng ký** để xác minh đúng tài khoản nhé.",
        ], false, true);
    }

    private function stepFindClass(Request $request, SupportMessage $msg, array $flow, string $input): array
    {
        if ($this->wantsFindNameAgain($input) || $this->wantsRecoveryRestart($input)) {
            $this->saveFlow($request, ['step' => 'find_name']);

            return $this->finish($msg, ['Được nhé, mình quay lại câu 1. Bạn nhập **họ và tên đầy đủ** của học sinh.'], false, true);
        }

        if ($this->looksLikeFullStudentName($input)) {
            return $this->stepFindName($request, $msg, $input);
        }

        if ($this->looksLikeNonAnswer($input)) {
            return $this->failFlow($request, $msg, $flow, 'Mình đang cần **lớp**, **mã đơn hàng** hoặc **SĐT đã đăng ký** để xác minh nhé.');
        }

        // Số điện thoại đã đăng ký + họ tên khớp đúng một tài khoản
        $phone = \App\Models\SupportMessage::normalizePhone(preg_replace('/[^0-9+]/', '', $input));
        if ($phone !== null) {
            return $this->revealOrFail($request, $msg, $this->findByPhone((string) ($flow['name'] ?? ''), $phone), $flow);
        }
        // Tự mua gói: mã đơn hàng chỉ người mua biết, đủ để xác minh cùng họ tên (không cần hỏi giáo viên)
        if (preg_match('/MOS[\s-]*(\d{6})[\s-]*([A-Z0-9]{5,8})/i', $input, $m)) {
            return $this->revealOrFail($request, $msg, $this->findByOrder((string) ($flow['name'] ?? ''), 'MOS-' . $m[1] . '-' . strtoupper($m[2])), $flow);
        }

        $this->saveFlow($request, ['step' => 'find_teacher', 'name' => $flow['name'] ?? '', 'class' => trim($input)]);

        return $this->finish($msg, ['Câu 3: Tên **thầy/cô giáo** quản lý tài khoản của em là gì? (ví dụ: Cô Mai Linh)'], false, true);
    }

    /**
     * So khớp cả 3 thông tin với đúng MỘT tài khoản học sinh. Sai thì chỉ báo chung (không nói sai ở câu nào) để không dò được dữ liệu.
     * Đúng thì cho biết Mã HS và email đã che; đổi mật khẩu vẫn bắt buộc OTP gửi về email nên không chiếm được tài khoản.
     */
    private function stepFindTeacher(Request $request, SupportMessage $msg, array $flow, string $input): array
    {
        if ($this->wantsFindNameAgain($input)) {
            $this->saveFlow($request, ['step' => 'find_name']);

            return $this->finish($msg, ['Được nhé, mình quay lại câu 1. Bạn nhập **họ và tên đầy đủ** của học sinh.'], false, true);
        }

        if ($this->wantsPreviousRecoveryStep($input) || $this->wantsEditClass($input)) {
            $this->saveFlow($request, ['step' => 'find_class', 'name' => $flow['name'] ?? '']);

            return $this->finish($msg, [
                'Mình quay lại câu 2 nhé.',
                'Bạn nhập **lớp**, hoặc nếu tự mua gói thì nhập **mã đơn hàng** / **SĐT đã đăng ký**.',
            ], false, true);
        }

        if ($this->looksLikeNonAnswer($input)) {
            return $this->failFlow($request, $msg, $flow, 'Mình đang cần tên **thầy/cô giáo quản lý tài khoản**. Nếu muốn sửa lớp, bạn gõ "quay lại".');
        }

        return $this->revealOrFail($request, $msg, $this->findByIdentity((string) ($flow['name'] ?? ''), (string) ($flow['class'] ?? ''), $input), $flow);
    }

    /** Xác minh xong: tìm thấy đúng một tài khoản thì cho biết Mã HS + email đã che; không thì báo chung và đếm số lần thử */
    private function revealOrFail(Request $request, SupportMessage $msg, ?User $user, array $flow = []): array
    {
        if (! $user) {
            $attempts = $this->countFindAttempt($request);
            if ($attempts >= self::FIND_MAX_ATTEMPTS) {
                return $this->endRecovery($request, $msg, 'Thông tin chưa khớp với tài khoản nào và bạn đã thử quá nhiều lần. ' . $this->handoffNotice());
            }
            $name = trim((string) ($flow['name'] ?? ''));
            if ($name !== '') {
                $this->saveFlow($request, ['step' => 'find_class', 'name' => $name]);

                return $this->finish($msg, [
                    'Thông tin xác minh chưa khớp. Mình giữ họ tên **' . $name . '** rồi nhé (còn ' . (self::FIND_MAX_ATTEMPTS - $attempts) . ' lần).',
                    'Bạn nhập lại **lớp**, hoặc nếu tự mua gói thì nhập **mã đơn hàng** / **SĐT đã đăng ký**.',
                ], false, true);
            }

            $this->saveFlow($request, ['step' => 'find_name']);

            return $this->finish($msg, [
                'Thông tin chưa khớp với tài khoản nào. Bạn kiểm tra lại họ tên, lớp, tên thầy/cô, mã đơn hàng hoặc SĐT rồi thử lại nhé (còn ' . (self::FIND_MAX_ATTEMPTS - $attempts) . ' lần).',
                'Câu 1: **Họ và tên đầy đủ** của học sinh là gì?',
            ], false, true);
        }

        $teacher = $user->teacher?->name;
        if (! $user->hasDeliverableEmail()) {
            return $this->endRecovery($request, $msg,
                'Mình tìm thấy tài khoản rồi: ' . ($user->student_code ? "em đăng nhập bằng **Mã HS {$user->student_code}**. " : '')
                . 'Tài khoản này chưa có email nhận thư nên chưa tự đổi mật khẩu được: em nhờ '
                . ($teacher ? "**{$teacher}**" : 'thầy/cô quản lý tài khoản') . ' hoặc ' . self::SUPPORT_ADMIN_NICKNAME . ' đặt lại mật khẩu giúp nhé.');
        }

        $this->saveFlow($request, ['step' => 'confirm', 'user_id' => $user->id]);

        return $this->finish($msg, [
            'Mình tìm thấy tài khoản rồi! 🎉 ' . ($user->student_code ? "Mã HS của em là **{$user->student_code}**, " : '') . 'email đăng ký là **' . $this->maskEmail($user->email) . '**'
                . ($user->phone ? ', số điện thoại **' . $this->maskPhone($user->phone) . '**' : '') . '.',
            'Để xác nhận đúng là bạn, hãy nhập **đầy đủ** địa chỉ email đó để nhận mã OTP.',
        ], false, true);
    }

    /** Học sinh khớp đúng cả họ tên, lớp và giáo viên quản lý; không khớp hoặc khớp nhiều người thì trả null */
    private function findByIdentity(string $name, string $class, string $teacher): ?User
    {
        $name = VietText::norm($name);
        $class = preg_replace('/^lop\s*/', '', VietText::norm($class));
        $teacherWords = array_values(array_diff(explode(' ', VietText::norm($teacher)), ['co', 'thay', 'gv', 'giao', 'vien', 'cua', 'em', 'la', '']));
        if ($name === '' || $class === '' || $teacherWords === []) {
            return null;
        }

        $matches = User::query()
            ->where('role', 'student')
            ->whereNotNull('classroom_id')
            ->whereNotNull('created_by')
            ->with(['classroom:id,name', 'teacher:id,name'])
            ->get(['id', 'name', 'email', 'student_code', 'classroom_id', 'created_by'])
            ->filter(function (User $u) use ($name, $class, $teacherWords) {
                $teacherName = ' ' . VietText::norm((string) $u->teacher?->name) . ' ';

                return (
                    $this->matchesLooseName((string) $u->name, $name)
                    && $this->matchesLooseClass((string) $u->classroom?->name, $class)
                    && collect($teacherWords)->every(fn ($w) => str_contains($teacherName, " {$w} "))
                ) || (
                    $this->matchesLooseName((string) $u->name, $name)
                    && $this->matchesLooseClass((string) $u->classroom?->name, $class)
                    && $this->similarity($teacherName, implode(' ', $teacherWords)) >= 0.72
                );
            });

        return $matches->count() === 1 ? $matches->first() : null;
    }

    /** Họ tên + SĐT đã đăng ký khớp đúng một tài khoản (không nhận tài khoản quản trị) */
    private function findByPhone(string $name, string $phone): ?User
    {
        $matches = User::where('phone', $phone)->where('role', '!=', 'admin')->get()
            ->filter(fn (User $u) => $this->matchesLooseName((string) $u->name, $name));

        return $matches->count() === 1 ? $matches->first() : null;
    }

    /** SĐT đúng duy nhất cũng đủ để nhận diện bước đầu; vẫn bắt nhập email đầy đủ trước khi gửi OTP. */
    private function findByPhoneOnly(string $phone): ?User
    {
        $matches = User::where('phone', $phone)->where('role', '!=', 'admin')->get();

        return $matches->count() === 1 ? $matches->first() : null;
    }

    /** Người mua gói: mã đơn hàng đúng và họ tên khớp chủ đơn (không nhận tài khoản quản trị) */
    private function findByOrder(string $name, string $code): ?User
    {
        $user = PackageOrder::where('code', $code)->first()?->user;
        if (! $user || $user->isAdmin() || ! $this->matchesLooseName((string) $user->name, $name)) {
            return null;
        }

        return $user;
    }

    /** Mã đơn hàng là bí mật phía người mua; đúng mã vẫn chỉ mở bước xác nhận email đầy đủ. */
    private function findByOrderOnly(string $code): ?User
    {
        $user = PackageOrder::where('code', $code)->first()?->user;

        return $user && ! $user->isAdmin() ? $user : null;
    }

    /** Đếm nhanh tài khoản học sinh có tên khớp/gần khớp để phản hồi tự nhiên trước khi hỏi thêm thông tin. */
    private function findNameCandidates(string $name): array
    {
        $needle = VietText::norm($name);
        if ($needle === '' || mb_strlen($needle) < 2) {
            return [];
        }

        $words = array_values(array_filter(explode(' ', $needle), fn ($word) => mb_strlen($word) >= 2));

        return User::query()
            ->where('role', '!=', 'admin')
            ->with('classroom:id,name')
            ->get(['id', 'name', 'classroom_id'])
            ->map(function (User $user) use ($needle, $words) {
                $candidate = VietText::norm((string) $user->name);
                if ($candidate === $needle || str_contains($candidate, $needle)) {
                    $score = 100;
                } elseif ($words !== [] && collect($words)->every(fn ($word) => str_contains($candidate, $word))) {
                    $score = 95;
                } else {
                    $score = (int) round($this->similarity($candidate, $needle) * 100);
                }

                return ['user' => $user, 'score' => $score];
            })
            ->filter(fn (array $row) => $row['score'] >= 70)
            ->sortByDesc('score')
            ->take(3)
            ->pluck('user')
            ->values()
            ->all();
    }

    /**
     * Tách câu nhập tự nhiên thành họ tên và lớp nếu người dùng đã nói chung một tin.
     * Ví dụ: "Lê Minh Trí lớp 3A", "le minh tri 3a", "em tên An Nhiên học lớp 4B".
     *
     * @return array{name: string, class: string, teacher?: string, phone?: string, order_code?: string}|null
     */
    private function splitNameAndClass(string $input): ?array
    {
        $clean = trim(preg_replace('/\s+/', ' ', $input));
        if ($clean === '') {
            return null;
        }

        $phone = \App\Models\SupportMessage::normalizePhone(preg_replace('/[^0-9+]/', '', $clean));
        $orderCode = null;
        if (preg_match('/MOS[\s-]*(\d{6})[\s-]*([A-Z0-9]{5,8})/i', $clean, $m)) {
            $orderCode = 'MOS-' . $m[1] . '-' . strtoupper($m[2]);
        }

        if (preg_match('/^(?:em\s*)?(?:ten\s*)?(.+?)\s+(?:hoc\s*)?(?:lop|lớp)\s*([0-9]{1,2}\s*[a-zA-Z][0-9]?)/iu', $clean, $m)) {
            $result = [
                'name' => trim($m[1]),
                'class' => strtoupper(str_replace(' ', '', trim($m[2]))),
            ];
            if (preg_match('/(?:co|cô|thay|thầy|gv|giao vien|giáo viên)\s+(.+)$/iu', $clean, $teacher)) {
                $result['teacher'] = trim($teacher[1]);
            }
            if ($phone !== null) {
                $result['phone'] = $phone;
            }
            if ($orderCode !== null) {
                $result['order_code'] = $orderCode;
            }

            return $result;
        }

        if (preg_match('/^(.+?)\s+([0-9]{1,2}\s*[a-zA-Z][0-9]?)$/u', $clean, $m)) {
            $name = trim($m[1]);
            if ($this->looksLikeFullStudentName($name)) {
                return [
                    'name' => $name,
                    'class' => strtoupper(str_replace(' ', '', trim($m[2]))),
                ];
            }
        }

        return null;
    }

    /** Tạo gợi ý đã che nhẹ để người dùng nhận ra, nhưng chưa lộ đầy đủ danh tính. */
    private function candidateHints(array $users): array
    {
        return array_map(function (User $user) {
            $class = $user->classroom?->name ? ' - ' . $this->maskClassName($user->classroom->name) : '';

            return $this->maskPersonName((string) $user->name) . $class;
        }, $users);
    }

    private function matchesLooseName(string $candidate, string $input): bool
    {
        $candidate = VietText::norm($candidate);
        $input = VietText::norm($input);
        if ($candidate === '' || $input === '') {
            return false;
        }

        $inputWords = array_values(array_filter(explode(' ', $input), fn ($word) => mb_strlen($word) >= 2));
        if ($candidate === $input || str_contains($candidate, $input)) {
            return true;
        }

        if ($inputWords !== [] && collect($inputWords)->every(fn ($word) => str_contains($candidate, $word) || $this->wordNear($candidate, $word))) {
            return true;
        }

        return $this->similarity($candidate, $input) >= 0.78;
    }

    private function matchesLooseClass(string $candidate, string $input): bool
    {
        $candidate = preg_replace('/^lop\s*/', '', VietText::norm($candidate));
        $input = preg_replace('/^lop\s*/', '', VietText::norm($input));

        return $candidate !== '' && $input !== '' && ($candidate === $input || $this->similarity($candidate, $input) >= 0.82);
    }

    private function wordNear(string $candidate, string $word): bool
    {
        foreach (explode(' ', $candidate) as $candidateWord) {
            if (mb_strlen($candidateWord) >= 2 && levenshtein($candidateWord, $word) <= 1) {
                return true;
            }
        }

        return false;
    }

    private function similarity(string $a, string $b): float
    {
        $a = VietText::norm($a);
        $b = VietText::norm($b);
        if ($a === '' || $b === '') {
            return 0.0;
        }

        similar_text($a, $b, $percent);

        return $percent / 100;
    }

    /**
     * Tên học sinh để tìm tài khoản cần đủ họ tên thật, không nhận nickname.
     * Các chuỗi như "Trí", "Trí Kun" quá mơ hồ, dễ dò sai tài khoản nên phải hỏi lại.
     */
    private function looksLikeFullStudentName(string $input): bool
    {
        $norm = VietText::norm($input);
        $words = array_values(array_filter(explode(' ', $norm), fn ($word) => mb_strlen($word) >= 2));

        return count($words) >= 3;
    }

    private function findKey(Request $request): string
    {
        return 'support_bot_find_' . sha1((string) $request->ip());
    }

    private function findLocked(Request $request): bool
    {
        return (int) Cache::get($this->findKey($request), 0) >= self::FIND_MAX_ATTEMPTS;
    }

    private function countFindAttempt(Request $request): int
    {
        $key = $this->findKey($request);
        Cache::add($key, 0, now()->addMinutes(self::FIND_LOCK_MINUTES));

        return (int) Cache::increment($key);
    }

    /**
     * Người dùng muốn quay lại bước trước, chấp nhận cả gõ thiếu dấu/sai thứ tự phím nhẹ như "qauy lai".
     */
    private function wantsPreviousRecoveryStep(string $input): bool
    {
        $text = VietText::norm($input);

        return (bool) preg_match('/\b(quay|qauy|tro|ve|lai|back)\b/', $text);
    }

    /** Người dùng muốn nhập lại từ câu 1/họ tên. */
    private function wantsFindNameAgain(string $input): bool
    {
        $text = VietText::norm($input);

        return (bool) preg_match('/(cau\s*1|ho\s*ten|ten\s*hoc\s*sinh|nhap\s*lai\s*(tu\s*dau|ho\s*ten)|lam\s*lai\s*(tu\s*dau)?)/', $text);
    }

    /** Người dùng muốn sửa lớp/mã đơn/SĐT ở câu 2. */
    private function wantsEditClass(string $input): bool
    {
        $text = VietText::norm($input);

        return (bool) preg_match('/(sua|doi|nhap\s*lai).*(lop|ma\s*don|sdt|so\s*dien\s*thoai)|cau\s*2/', $text);
    }

    /** Người dùng muốn khởi động lại nhánh tìm tài khoản. */
    private function wantsRecoveryRestart(string $input): bool
    {
        $text = VietText::norm($input);

        return (bool) preg_match('/(lam\s*lai|bat\s*dau\s*lai|nhap\s*lai\s*tu\s*dau|reset\s*lai)/', $text);
    }

    /**
     * Dùng AI như một lớp đọc ý định trong luồng khôi phục.
     * AI chỉ được phân loại thao tác; code vẫn tự thực thi để không lộ dữ liệu hoặc làm sai quyền.
     */
    private function handleRecoveryIntentByAi(Request $request, SupportMessage $msg, array $flow, string $input): ?array
    {
        $step = (string) ($flow['step'] ?? '');
        if ($this->asksHowToAnswerRecovery($input)) {
            return $this->guideRecoveryStep($request, $msg, $flow, $input);
        }

        if (! in_array($step, ['find_name', 'find_class', 'find_teacher'], true)) {
            return null;
        }

        $decision = $this->decideRecoveryAction($step, $flow, $input);
        if ($decision === null) {
            return null;
        }

        return match ($decision['action']) {
            'restart', 'back_to_name' => $this->goToFindName($request, $msg),
            'back_to_class' => $this->goToFindClass($request, $msg, $flow),
            'not_answer' => $this->replyRecoveryLikeStaff($request, $msg, $flow, $input),
            default => null,
        };
    }

    /**
     * AI agent quyết định action tiếp theo bằng tool call.
     *
     * @return array{action: string, extracted?: array<string, string>}|null
     */
    private function decideRecoveryAction(string $step, array $flow, string $input): ?array
    {
        $messages = [
            [
                'role' => 'system',
                'content' => implode("\n", [
                    'Bạn là AI agent điều phối luồng quên mật khẩu IC3 Adventure.',
                    'Nhiệm vụ: đọc mọi câu người dùng nhập, hiểu ý định và gọi đúng tool.',
                    'Không tự xác minh tài khoản, không gửi OTP, không đổi mật khẩu, không hỏi mật khẩu cũ.',
                    'Chỉ chọn action tiếp theo để Laravel thực thi an toàn.',
                    'Nếu người dùng đang thật sự cung cấp thông tin cho bước hiện tại, chọn continue.',
                    'Nếu người dùng nói tự nhiên/gõ sai chính tả nhưng muốn quay lại/sửa thông tin, chọn action tương ứng.',
                    'Nếu chỉ chào hỏi, nói đùa, hoặc chưa cung cấp thông tin cần thiết, chọn not_answer.',
                ]),
            ],
            [
                'role' => 'user',
                'content' => json_encode([
                    'current_step' => $step,
                    'known' => [
                        'name' => (string) ($flow['name'] ?? ''),
                        'class' => (string) ($flow['class'] ?? ''),
                    ],
                    'user_message' => $input,
                    'available_actions' => [
                        'continue' => 'Tiếp tục xử lý câu này theo bước hiện tại.',
                        'not_answer' => 'Câu này không phải thông tin cần cho bước hiện tại.',
                        'back_to_name' => 'Quay lại nhập họ tên/câu 1.',
                        'back_to_class' => 'Quay lại nhập lớp/mã đơn/SĐT/câu 2.',
                        'restart' => 'Làm lại nhánh tìm tài khoản từ đầu.',
                    ],
                ], JSON_UNESCAPED_UNICODE),
            ],
        ];

        $tools = [[
            'type' => 'function',
            'function' => [
                'name' => 'choose_recovery_action',
                'description' => 'Chọn action kế tiếp cho luồng quên mật khẩu sau khi đọc câu người dùng.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'action' => [
                            'type' => 'string',
                            'enum' => ['continue', 'not_answer', 'back_to_name', 'back_to_class', 'restart'],
                        ],
                        'reason' => [
                            'type' => 'string',
                            'description' => 'Lý do ngắn gọn để debug nội bộ, không hiển thị cho người dùng.',
                        ],
                        'extracted' => [
                            'type' => 'object',
                            'properties' => [
                                'name' => ['type' => 'string'],
                                'class' => ['type' => 'string'],
                                'phone' => ['type' => 'string'],
                                'order_code' => ['type' => 'string'],
                                'teacher' => ['type' => 'string'],
                            ],
                        ],
                    ],
                    'required' => ['action'],
                ],
            ],
        ]];

        $result = $this->client->chatWithTools($messages, $tools, 120);
        $call = collect($result['tool_calls'] ?? [])->firstWhere('name', 'choose_recovery_action');
        if (! is_array($call)) {
            return null;
        }

        $args = is_array($call['arguments'] ?? null) ? $call['arguments'] : [];
        $action = (string) ($args['action'] ?? '');
        if (! in_array($action, ['continue', 'not_answer', 'back_to_name', 'back_to_class', 'restart'], true)) {
            return null;
        }

        return ['action' => $action, 'extracted' => is_array($args['extracted'] ?? null) ? $args['extracted'] : []];
    }

    /** Người dùng đang hỏi cách nhập ở bước hiện tại, không phải cung cấp dữ liệu xác minh. */
    private function asksHowToAnswerRecovery(string $input): bool
    {
        $text = VietText::norm($input);

        return (bool) preg_match('/(nhap|ghi|dien|viet|go)\s*(sao|gi|nhu\s*the\s*nao|kieu\s*gi|o\s*dau)|lam\s*sao|lam\s*the\s*nao|huong\s*dan|chi\s*(minh|toi|em)|khong\s*biet\s*(nhap|ghi|dien|viet|go)/', $text);
    }

    private function guideRecoveryStep(Request $request, SupportMessage $msg, array $flow, string $input): array
    {
        $this->saveFlow($request, $flow);

        $step = (string) ($flow['step'] ?? 'identify');
        $reply = match ($step) {
            'identify' => 'Bạn nhập **email đăng nhập** hoặc **Mã HS** vào đây là được nha. Nếu không nhớ cả hai, cứ gõ **"không nhớ"**, mình sẽ hỏi vài thông tin như họ tên, lớp hoặc SĐT để tìm giúp.',
            'find_name' => 'Bạn nhập **họ và tên đầy đủ của học sinh** trước nhé. Nếu nhớ thêm lớp hoặc SĐT thì có thể nhập chung một tin, ví dụ: `Nguyễn An Nhiên lớp 3A1`.',
            'find_class' => 'Ở bước này bạn nhập **lớp** như `3A1`, hoặc nhập **SĐT đã đăng ký** / **mã đơn hàng** nếu tài khoản tự mua gói nha.',
            'find_teacher' => 'Bạn nhập tên **thầy/cô quản lý tài khoản**, ví dụ `Cô Mai Linh`. Nếu muốn sửa lớp hoặc SĐT thì gõ **quay lại** nhé.',
            'confirm' => 'Bạn nhập **đầy đủ email đăng ký** giống phần gợi ý đã che ở trên. Mình chỉ gửi OTP khi email khớp chính xác để bảo vệ tài khoản.',
            'otp' => 'Bạn mở email vừa nhận rồi nhập **6 chữ số OTP** vào đây. Mã này sẽ được ẩn trong lịch sử để bảo mật.',
            'password' => 'Bạn nhập **mật khẩu mới** ít nhất 6 ký tự. Không cần gửi mật khẩu cũ nha.',
            default => 'Bạn nhập tiếp thông tin theo câu mình vừa hỏi nhé. Nếu muốn dừng thì gõ **hủy**.',
        };

        return $this->finish($msg, [$reply], in_array($step, ['otp', 'password'], true), true);
    }

    private function goToFindName(Request $request, SupportMessage $msg): array
    {
        $this->saveFlow($request, ['step' => 'find_name']);

        return $this->finish($msg, ['Được nhé, mình quay lại câu 1. Bạn nhập **họ và tên đầy đủ** của học sinh.'], false, true);
    }

    private function goToFindClass(Request $request, SupportMessage $msg, array $flow): array
    {
        $this->saveFlow($request, ['step' => 'find_class', 'name' => $flow['name'] ?? '']);

        return $this->finish($msg, [
            'Mình quay lại câu 2 nhé.',
            'Bạn nhập **lớp**, hoặc nếu tự mua gói thì nhập **mã đơn hàng** / **SĐT đã đăng ký**.',
        ], false, true);
    }

    private function repeatRecoveryQuestion(Request $request, SupportMessage $msg, array $flow): array
    {
        $this->saveFlow($request, $flow);

        return match ((string) ($flow['step'] ?? '')) {
            'find_name' => $this->finish($msg, ['Mình đang ở bước tìm tài khoản. Bạn nhập **họ tên đầy đủ**, hoặc gõ **hủy** nếu muốn dừng nhé.'], false, true),
            'find_class' => $this->finish($msg, ['Mình đang giữ họ tên rồi. Bạn nhập **lớp**, **mã đơn hàng** hoặc **SĐT đăng ký**; muốn sửa tên thì gõ **quay lại câu 1**.'], false, true),
            'find_teacher' => $this->finish($msg, ['Mình đang cần tên **thầy/cô quản lý tài khoản**. Muốn sửa lớp/SĐT thì gõ **quay lại**; muốn nhập lại tên thì gõ **quay lại câu 1**.'], false, true),
            default => $this->finish($msg, ['Bạn nhập tiếp thông tin giúp mình nhé.'], false, true),
        };
    }

    /**
     * Khi khách nói ngoài lề trong luồng khôi phục, để AI trả lời mềm như nhân viên hỗ trợ,
     * nhưng vẫn neo lại bước bảo mật đang cần làm.
     */
    private function replyRecoveryLikeStaff(Request $request, SupportMessage $msg, array $flow, string $input): array
    {
        $this->saveFlow($request, $flow);

        $step = (string) ($flow['step'] ?? '');
        $need = match ($step) {
            'find_name' => 'họ và tên đầy đủ của học sinh',
            'find_class' => 'lớp, mã đơn hàng hoặc số điện thoại đã đăng ký',
            'find_teacher' => 'tên thầy/cô giáo quản lý tài khoản',
            default => 'thông tin xác minh tiếp theo',
        };

        $answer = $this->client->complete(implode("\n", [
            'Bạn là nhân viên hỗ trợ thân thiện của IC3 Adventure.',
            'Người dùng đang ở giữa luồng quên mật khẩu. Hãy trả lời tự nhiên như người thật, tối đa 2 câu ngắn.',
            'Không yêu cầu mật khẩu cũ, không yêu cầu OTP nếu chưa tới bước OTP, không khẳng định tìm thấy tài khoản.',
            'Nếu người dùng chào/hỏi dấu ?/nói chưa rõ, đáp lại nhẹ nhàng rồi hỏi đúng một thông tin đang cần.',
            'Nói rõ họ có thể gõ "quay lại", "quay lại câu 1" hoặc "hủy" nếu muốn đổi hướng.',
            'Thông tin đang cần: ' . $need,
        ]), [[
            'role' => 'user',
            'text' => $input,
        ]], 160);

        if ($answer !== null) {
            return $this->finish($msg, [$answer], false, true);
        }

        return $this->repeatRecoveryQuestion($request, $msg, $flow);
    }

    /**
     * Khi thông tin khách nhập chưa đạt yêu cầu, AI nói lại thật mềm như nhân viên,
     * còn code vẫn giữ nguyên bước hiện tại.
     */
    private function replyRecoveryProblem(Request $request, SupportMessage $msg, array $flow, string $input, string $problem): array
    {
        $this->saveFlow($request, $flow);

        $answer = $this->client->complete(implode("\n", [
            'Bạn là nhân viên hỗ trợ IC3 Adventure đang chat với khách.',
            'Khách đang trong luồng khôi phục mật khẩu. Trả lời tự nhiên, thân thiện, tối đa 2 câu ngắn.',
            'Không trách khách, không nói như máy. Hãy giải thích nhẹ vì sao cần nhập lại và nói rõ khách nên nhập gì tiếp theo.',
            'Không hỏi mật khẩu, không hỏi OTP, không khẳng định tìm thấy tài khoản.',
            'Tình huống: ' . $problem,
        ]), [[
            'role' => 'user',
            'text' => $input,
        ]], 140);

        return $this->finish($msg, [
            $answer ?: 'Mình hiểu rồi, nhưng thông tin này còn hơi thiếu để tìm đúng tài khoản. Bạn nhập giúp mình **họ và tên đầy đủ** nhé, ví dụ: Nguyễn An Nhiên.',
        ], false, true);
    }

    private function looksLikeNonAnswer(string $input): bool
    {
        $text = VietText::norm($input);

        return (bool) preg_match('/^(hi|hello|helo|alo|ok|oke|uh|ừ|um|test|thu|abc|a|b|c)$/iu', $text);
    }

    private function stepConfirmEmail(Request $request, SupportMessage $msg, array $flow, string $input): array
    {
        $user = User::find($flow['user_id'] ?? 0);
        if (! $user) {
            return $this->endRecovery($request, $msg, 'Có lỗi xảy ra. Bạn gõ "quên mật khẩu" để thử lại nhé.');
        }

        if (mb_strtolower($input) !== mb_strtolower((string) $user->email)) {
            return $this->failFlow($request, $msg, $flow, 'Email chưa khớp với tài khoản. Bạn thử lại hoặc gõ "hủy" để dừng.');
        }

        $rateKey = 'support_bot_otp_time_' . $user->id;
        if (Cache::has($rateKey)) {
            return $this->finish($msg, ['Bạn vừa yêu cầu mã rồi. Vui lòng đợi khoảng 1 phút rồi thử lại nhé.'], false, true);
        }

        $otp = (string) random_int(100000, 999999);
        Cache::put('support_bot_otp_' . $user->id, hash('sha256', $otp), now()->addMinutes(self::OTP_MINUTES));
        Cache::forget('support_bot_otp_fail_' . $user->id);
        Cache::put($rateKey, true, now()->addMinute());

        $sent = $this->sendOtpMail($user, $otp);
        if (! $sent) {
            Cache::forget($rateKey);
            Cache::forget('support_bot_otp_' . $user->id);

            return $this->finish($msg, ['Hiện chưa gửi được email mã OTP. Bạn thử lại sau ít phút hoặc để lại SĐT/Zalo cho ' . self::SUPPORT_ADMIN_NICKNAME . ' nhé.'], false, true);
        }

        $this->saveFlow($request, ['step' => 'otp', 'user_id' => $user->id]);

        return $this->finish($msg, [
            'Mình đã gửi mã OTP 6 số tới email của bạn. Mã có hiệu lực trong 10 phút.',
            'Bạn nhập mã OTP vào ô chat nhé. Mã sẽ được ẩn để bảo mật.',
        ], true, true);
    }

    private function stepVerifyOtp(Request $request, SupportMessage $msg, array $flow, string $input): array
    {
        $userId = (int) ($flow['user_id'] ?? 0);

        if (! preg_match('/^\d{6}$/', $input)) {
            return $this->failFlow($request, $msg, $flow, 'Mã OTP gồm đúng 6 chữ số. Bạn nhập lại nhé.', true);
        }

        $stored = Cache::get('support_bot_otp_' . $userId);
        if (! $stored || ! hash_equals((string) $stored, hash('sha256', $input))) {
            $failKey = 'support_bot_otp_fail_' . $userId;
            Cache::put($failKey, (int) Cache::get($failKey, 0) + 1, now()->addMinutes(self::OTP_MINUTES));

            if ((int) Cache::get($failKey) >= self::OTP_MAX_ATTEMPTS) {
                Cache::forget('support_bot_otp_' . $userId);
                Cache::forget($failKey);

                return $this->endRecovery($request, $msg, 'Bạn nhập sai quá nhiều lần nên mã OTP đã bị hủy. Bạn gõ "quên mật khẩu" để nhận mã mới sau 1 phút nhé.');
            }

            return $this->failFlow($request, $msg, $flow, 'Mã OTP không đúng hoặc đã hết hạn. Bạn kiểm tra email (cả mục Spam) rồi nhập lại nhé.', true);
        }

        Cache::forget('support_bot_otp_' . $userId);
        Cache::forget('support_bot_otp_fail_' . $userId);
        $this->saveFlow($request, ['step' => 'password', 'user_id' => $userId]);

        return $this->finish($msg, [
            'Xác minh thành công! ✅',
            'Bạn nhập **mật khẩu mới** (ít nhất 6 ký tự). Mật khẩu sẽ được ẩn trong khung chat.',
        ], true, true);
    }

    private function stepSetPassword(Request $request, SupportMessage $msg, array $flow, string $input): array
    {
        $user = User::find($flow['user_id'] ?? 0);
        if (! $user) {
            return $this->endRecovery($request, $msg, 'Có lỗi xảy ra. Bạn gõ "quên mật khẩu" để thử lại nhé.');
        }

        if (mb_strlen($input) < 6) {
            return $this->failFlow($request, $msg, $flow, 'Mật khẩu mới cần ít nhất 6 ký tự. Bạn nhập lại nhé.', true);
        }

        $user->forceFill(['password' => Hash::make($input)])->save();
        $this->sendChangedAlert($user, $request);
        session()->forget(self::SESSION_KEY);

        return $this->finish($msg, [
            'Đổi mật khẩu thành công! 🎉',
            'Bạn kéo lên form đăng nhập, nhập tài khoản và mật khẩu mới để vào hệ thống nhé.',
        ], false, false);
    }

    /** Ghi nhận lỗi nhập liệu nhưng vẫn giữ bước hiện tại */
    private function failFlow(Request $request, SupportMessage $msg, array $flow, string $reply, bool $secretNext = false): array
    {
        $this->saveFlow($request, $flow);

        return $this->finish($msg, [$reply], $secretNext, true);
    }

    private function endRecovery(Request $request, SupportMessage $msg, string $reply): array
    {
        session()->forget(self::SESSION_KEY);
        session()->put(self::RECENT_RECOVERY_KEY, true);

        return $this->finish($msg, [$reply], false, false);
    }

    private function sendOtpMail(User $user, string $otp): bool
    {
        return MailDelivery::send($user, new PasswordResetOtpMail($otp, $user), 'OTP');
    }

    private function sendChangedAlert(User $user, Request $request): void
    {
        MailDelivery::send($user, new PasswordChangedAlertMail(
            $user,
            now()->format('H:i:s d/m/Y'),
            (string) $request->ip(),
            (string) $request->userAgent(),
        ), 'cảnh báo đổi mật khẩu');
    }

    // =========================================================================
    // 🧰 TIỆN ÍCH
    // =========================================================================

    private function handoffNotice(): string
    {
        return 'Mình sẽ nhờ ' . self::SUPPORT_ADMIN_NICKNAME . ' hỗ trợ trực tiếp nhé. Bạn có thể nhắn Zalo/SĐT **' . self::SUPPORT_ZALO_PHONE . '**, hoặc chuyển sang tab 👨‍💼 Ban Quản Trị và để lại nội dung cần hỗ trợ nha!';
    }

    private function guestHandoffNotice(): string
    {
        return 'Nếu cần người thật hỗ trợ ngay, bạn nhắn Zalo/SĐT **' . self::SUPPORT_ZALO_PHONE . '** gặp ' . self::SUPPORT_ADMIN_NICKNAME . ' nhé. Nếu đã có tài khoản, bạn đăng nhập để mở tab 👨‍💼 Ban Quản Trị thì admin xem lịch sử và hỗ trợ chuẩn hơn.';
    }

    /** Khóa đếm lượt AI: thành viên theo cuộc trò chuyện đã lưu, khách vãng lai theo phiên trình duyệt. */
    private function budgetKeys(Request $request, SupportMessage $msg): array
    {
        $conversation = $msg->exists
            ? 'msg_' . $msg->id
            : 'sess_' . sha1($request->hasSession() ? $request->session()->getId() : (string) $request->ip());

        return [
            'support_bot_ai_conv_' . $conversation,
            'support_bot_ai_ip_' . md5((string) $msg->ip_address),
        ];
    }

    /** Giới hạn chi phí (khi bật): tối đa AI_LIMIT_CONVERSATION lượt/giờ mỗi cuộc và AI_LIMIT_IP lượt/giờ mỗi IP. */
    private function aiBudgetAvailable(Request $request, SupportMessage $msg): bool
    {
        if (! self::AI_LIMIT_ENABLED) {
            return true;
        }

        [$convKey, $ipKey] = $this->budgetKeys($request, $msg);

        return (int) Cache::get($convKey, 0) < self::AI_LIMIT_CONVERSATION
            && (int) Cache::get($ipKey, 0) < self::AI_LIMIT_IP;
    }

    private function countAiUse(Request $request, SupportMessage $msg): void
    {
        if (! self::AI_LIMIT_ENABLED) {
            return;
        }

        foreach ($this->budgetKeys($request, $msg) as $key) {
            Cache::add($key, 0, now()->addHour());
            Cache::increment($key);
        }
    }

    private function handoffKey(SupportMessage $msg): string
    {
        return 'support_bot_handoff_' . $msg->id;
    }

    private function handoffActive(SupportMessage $msg): bool
    {
        return Cache::has($this->handoffKey($msg));
    }

    /**
     * Ghi các câu trả lời của bot vào hội thoại và trả về kết quả cho giao diện.
     * Khách vãng lai dùng bản ghi chưa lưu nên không ghi vào CSDL.
     */
    private function finish(SupportMessage $msg, array $replies, bool $secretNext, bool $flowActive, ?array $action = null): array
    {
        $history = is_array($msg->conversation_history) ? $msg->conversation_history : [];
        $lastBotText = '';
        for ($i = count($history) - 1; $i >= 0; $i--) {
            if (($history[$i]['sender'] ?? '') === 'bot') {
                $lastBotText = trim((string) ($history[$i]['text'] ?? ''));
                break;
            }
        }

        $replies = array_values(array_filter($replies, function (string $reply) use (&$lastBotText) {
            $text = trim($reply);
            if ($text === '' || $text === $lastBotText) {
                return false;
            }
            $lastBotText = $text;

            return true;
        }));

        $last = count($replies) - 1;
        foreach ($replies as $index => $reply) {
            $msg->appendConversationTurn('bot', $reply, null, 'ai', $index === $last ? $action : null);
        }
        if ($msg->exists) {
            $msg->save();
        }

        return ['replies' => $replies, 'secret_next' => $secretNext, 'flow_active' => $flowActive, 'action' => $action];
    }

    /** Lấy luồng khôi phục còn hiệu lực trong session; hết hạn thì xóa */
    private function activeFlow(Request $request): ?array
    {
        $flow = $request->hasSession() ? $request->session()->get(self::SESSION_KEY) : null;
        if (! is_array($flow)) {
            return null;
        }

        if (($flow['expires_at'] ?? 0) < now()->timestamp) {
            $request->session()->forget(self::SESSION_KEY);

            return null;
        }

        return $flow;
    }

    private function saveFlow(Request $request, array $flow): void
    {
        $flow['expires_at'] = now()->addMinutes(self::FLOW_MINUTES)->timestamp;
        $request->session()->put(self::SESSION_KEY, $flow);
        $request->session()->forget(self::RECENT_RECOVERY_KEY);
    }

    /** Che bớt email nhưng giữ đủ ký tự đầu/cuối để người dùng dễ nhận ra. */
    private function maskEmail(string $email): string
    {
        [$local, $domain] = explode('@', $email, 2) + [1 => ''];
        $length = mb_strlen($local);
        if ($length <= 4) {
            return mb_substr($local, 0, 2) . str_repeat('•', max(1, $length - 2)) . '@' . $domain;
        }

        $head = mb_substr($local, 0, min(4, $length - 2));
        $tail = mb_substr($local, -2);

        return $head . str_repeat('•', max(2, $length - mb_strlen($head) - 2)) . $tail . '@' . $domain;
    }

    /** Che nhẹ SĐT trong luồng xác minh: 0345•••438. */
    private function maskPhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?: '';
        if (strlen($digits) <= 7) {
            return $digits;
        }

        return substr($digits, 0, 4) . str_repeat('•', max(2, strlen($digits) - 7)) . substr($digits, -3);
    }

    /** Che tên gợi ý theo từng từ: Nguyễn A• Nhiên, đủ nhận ra nhưng chưa lộ toàn bộ. */
    private function maskPersonName(string $name): string
    {
        return collect(preg_split('/\s+/', trim($name)) ?: [])
            ->filter()
            ->map(function (string $word, int $index) {
                if ($index === 0 || mb_strlen($word) <= 2) {
                    return $word;
                }

                return mb_substr($word, 0, 1) . str_repeat('•', max(1, mb_strlen($word) - 2)) . mb_substr($word, -1);
            })
            ->implode(' ');
    }

    /** Che nhẹ lớp gợi ý: Lớp 3A•, vẫn đủ để học sinh nhận diện. */
    private function maskClassName(string $className): string
    {
        $class = trim($className);
        if (preg_match('/^(.+?[0-9]{1,2}[A-Za-z])([0-9])$/u', $class, $m)) {
            return $m[1] . '•';
        }

        return $class;
    }
}
