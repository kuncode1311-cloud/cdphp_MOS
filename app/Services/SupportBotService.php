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
use Illuminate\Mail\Mailable;
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
    private const ADMIN_SEEN_KEY = 'support_admin_seen_at';
    private const FLOW_MINUTES = 15;
    private const OTP_MINUTES = 10;
    private const OTP_MAX_ATTEMPTS = 5;
    /** Tìm lại tài khoản bằng câu hỏi xác minh: tối đa 3 lần mỗi 30 phút cho mỗi địa chỉ IP */
    private const FIND_MAX_ATTEMPTS = 3;
    private const FIND_LOCK_MINUTES = 30;
    /** Email ảo (do giáo viên tạo tài khoản) không nhận được thư */
    private const NO_MAIL_DOMAINS = ['student.ic3.local', 'ic3.test'];
    /** Số lượt AI tối đa trong một giờ cho mỗi cuộc trò chuyện và mỗi IP */
    private const AI_LIMIT_CONVERSATION = 10;
    private const AI_LIMIT_IP = 60;
    /** Thời gian bot ghi nhớ yêu cầu gặp người thật trong một cuộc trò chuyện */
    private const HANDOFF_HOURS = 12;

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
            return ['text' => 'Bạn đã hỏi khá nhiều trong thời gian ngắn. Mình đã ghi nhận tin nhắn, Ban Quản Trị sẽ trực tiếp trả lời bạn sớm nhất nhé!', 'action' => null];
        }

        $user = $request->user();
        $asksAccount = $this->looksLikeAccountQuestion($text);

        // Hỏi về tài khoản/kết quả nhưng chưa có quyền: hướng dẫn đăng nhập hoặc mua gói, không tra dữ liệu
        if ($asksAccount && ! $user) {
            return ['text' => 'Để mình xem được kết quả và tiến độ học tập, bạn vui lòng đăng nhập tài khoản nhé. Bạn bấm nút bên dưới để đăng nhập.', 'action' => ['label' => 'Đăng nhập', 'url' => route('login')]];
        }
        if ($asksAccount && ! $user->hasAiAssistant()) {
            return ['text' => 'Tra cứu kết quả và tiến độ học tập là tính năng của gói Trợ lý AI. Bạn bấm nút bên dưới để xem gói nhé!', 'action' => ['label' => 'Xem gói Trợ lý AI', 'url' => route('pricing.index')]];
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
            . '|đi[eể]m\s*(c[uủ]a|s[oố])\s*(t[oô]i|con|em|m[iì]nh)|con\s*(t[oô]i|m[iì]nh)|h[oọ]c\s*sinh\s*c[uủ]a|t[eệ]\s*nh[aấ]t|gi[oỏ]i\s*nh[aấ]t/iu',
            $text
        );
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
            'Bạn là Trợ lý AI của IC3 Adventure (hệ thống luyện thi IC3 GS6 & Spark Quest).',
            'Xưng "mình", gọi khách là "bạn". Trả lời đúng trọng tâm, tối đa 5 câu, thân thiện, dễ hiểu với học sinh tiểu học, giáo viên và phụ huynh.',
            'Chỉ dựa vào tài liệu và dữ liệu dưới đây. Nếu không có thông tin, nói thật là chưa rõ và chuyển Ban Quản Trị. Tuyệt đối không bịa.',
            'Trong cuộc trò chuyện, nếu khách đã nói rõ vấn đề, hãy trả lời đúng vấn đề đó, không lặp lại câu chào.',
            'Không bao giờ yêu cầu, tiết lộ hay tạo mật khẩu hoặc mã OTP.',
            'Nếu khách quên mật khẩu, hướng dẫn họ gõ "quên mật khẩu" để bot hỗ trợ từng bước.',
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
            . '|(kh[oô]ng|ko|k)\s*nh[oớ]\s*(pass|pas|m[aậ]t|mk|t[aà]i)'
            . '|m[aấ]t\s*(pass|pas|m[aậ]t\s*kh[aẩ]u)'
            . '|kh[oô]i\s*ph[uụ]c|reset\s*(m[aậ]t\s*kh[aẩ]u|pass|pas)'
            . '|(kh[oô]ng|ko)\s*(th[eể]\s*)?đ[aă]ng\s*nh[aậ]p/iu',
            $text
        );
    }

    private function startRecovery(Request $request, SupportMessage $msg): array
    {
        $this->saveFlow($request, ['step' => 'identify']);

        return $this->finish($msg, [
            "Mình hỗ trợ bạn khôi phục mật khẩu nhé! 🔑\n"
            . "Bạn nhập **email đăng nhập** hoặc **Mã HS** của mình.\n"
            . 'Không nhớ cả hai? Gõ **"không nhớ"** để mình hỏi vài câu xác minh và tìm lại giúp bạn. Gõ "hủy" nếu muốn dừng.',
        ], false, true);
    }

    private function continueRecovery(Request $request, SupportMessage $msg, array $flow, string $text): array
    {
        $input = trim($text);

        if (mb_strtolower($input) === 'hủy' || mb_strtolower($input) === 'huy') {
            return $this->endRecovery($request, $msg, 'Đã dừng khôi phục mật khẩu. Bạn cần gì thêm cứ nhắn mình nhé!');
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
                'Nếu vẫn không được, bạn để lại SĐT/Zalo, Ban Quản Trị sẽ hỗ trợ trực tiếp.',
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
            'Mình hỏi 3 câu để xác minh đúng là bạn nhé. 🕵️',
            'Câu 1: **Họ và tên đầy đủ** của học sinh là gì? (ví dụ: Nguyễn An Nhiên)',
        ], false, true);
    }

    private function stepFindName(Request $request, SupportMessage $msg, string $input): array
    {
        if (mb_strlen(VietText::norm($input)) < 2) {
            return $this->failFlow($request, $msg, ['step' => 'find_name'], 'Bạn nhập họ và tên đầy đủ giúp mình nhé.');
        }
        $this->saveFlow($request, ['step' => 'find_class', 'name' => trim($input)]);

        return $this->finish($msg, [
            "Câu 2: Tài khoản do **thầy/cô tạo** thì nhập **lớp** (ví dụ: 3A1).\n"
            . 'Nếu **tự mua gói** thì nhập **mã đơn hàng** (dạng MOS-202610-ABCDE, có trong nội dung chuyển khoản hoặc biên nhận thanh toán).',
        ], false, true);
    }

    private function stepFindClass(Request $request, SupportMessage $msg, array $flow, string $input): array
    {
        // Tự mua gói: mã đơn hàng chỉ người mua biết, đủ để xác minh cùng họ tên (không cần hỏi giáo viên)
        if (preg_match('/MOS[\s-]*(\d{6})[\s-]*([A-Z0-9]{5,8})/i', $input, $m)) {
            return $this->revealOrFail($request, $msg, $this->findByOrder((string) ($flow['name'] ?? ''), 'MOS-' . $m[1] . '-' . strtoupper($m[2])));
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
        return $this->revealOrFail($request, $msg, $this->findByIdentity((string) ($flow['name'] ?? ''), (string) ($flow['class'] ?? ''), $input));
    }

    /** Xác minh xong: tìm thấy đúng một tài khoản thì cho biết Mã HS + email đã che; không thì báo chung và đếm số lần thử */
    private function revealOrFail(Request $request, SupportMessage $msg, ?User $user): array
    {
        if (! $user) {
            $attempts = $this->countFindAttempt($request);
            if ($attempts >= self::FIND_MAX_ATTEMPTS) {
                return $this->endRecovery($request, $msg, 'Thông tin chưa khớp với tài khoản nào và bạn đã thử quá nhiều lần. ' . $this->handoffNotice());
            }
            $this->saveFlow($request, ['step' => 'find_name']);

            return $this->finish($msg, [
                'Thông tin chưa khớp với tài khoản nào. Bạn kiểm tra lại họ tên (có dấu), lớp và tên thầy/cô hoặc mã đơn hàng rồi thử lại nhé (còn ' . (self::FIND_MAX_ATTEMPTS - $attempts) . ' lần).',
                'Câu 1: **Họ và tên đầy đủ** của học sinh là gì?',
            ], false, true);
        }

        $teacher = $user->teacher?->name;
        if (! $this->hasRealEmail($user)) {
            return $this->endRecovery($request, $msg,
                'Mình tìm thấy tài khoản rồi: ' . ($user->student_code ? "em đăng nhập bằng **Mã HS {$user->student_code}**. " : '')
                . 'Tài khoản này chưa có email nhận thư nên chưa tự đổi mật khẩu được: em nhờ '
                . ($teacher ? "**{$teacher}**" : 'thầy/cô quản lý tài khoản') . ' hoặc Ban Quản Trị đặt lại mật khẩu giúp nhé.');
        }

        $this->saveFlow($request, ['step' => 'confirm', 'user_id' => $user->id]);

        return $this->finish($msg, [
            'Mình tìm thấy tài khoản rồi! 🎉 ' . ($user->student_code ? "Mã HS của em là **{$user->student_code}**, " : '') . 'email đăng ký là **' . $this->maskEmail($user->email) . '**.',
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

                return VietText::norm($u->name) === $name
                    && preg_replace('/^lop\s*/', '', VietText::norm((string) $u->classroom?->name)) === $class
                    && collect($teacherWords)->every(fn ($w) => str_contains($teacherName, " {$w} "));
            });

        return $matches->count() === 1 ? $matches->first() : null;
    }

    /** Người mua gói: mã đơn hàng đúng và họ tên khớp chủ đơn (không nhận tài khoản quản trị) */
    private function findByOrder(string $name, string $code): ?User
    {
        $user = PackageOrder::where('code', $code)->first()?->user;
        if (! $user || $user->isAdmin() || VietText::norm($user->name) !== VietText::norm($name)) {
            return null;
        }

        return $user;
    }

    private function hasRealEmail(User $user): bool
    {
        $domain = mb_strtolower((string) substr(strrchr((string) $user->email, '@'), 1));

        return $domain !== '' && ! in_array($domain, self::NO_MAIL_DOMAINS, true) && ! str_ends_with($domain, '.local') && ! str_ends_with($domain, '.test');
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

            return $this->finish($msg, ['Hiện chưa gửi được email mã OTP. Bạn thử lại sau ít phút hoặc để lại SĐT/Zalo cho Ban Quản Trị nhé.'], false, true);
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

        return $this->finish($msg, [$reply], false, false);
    }

    private function sendOtpMail(User $user, string $otp): bool
    {
        return $this->deliver($user, new PasswordResetOtpMail($otp, $user), 'OTP');
    }

    private function sendChangedAlert(User $user, Request $request): void
    {
        $this->deliver($user, new PasswordChangedAlertMail(
            $user,
            now()->format('H:i:s d/m/Y'),
            (string) $request->ip(),
            (string) $request->userAgent(),
        ), 'cảnh báo đổi mật khẩu');
    }

    /**
     * Gửi email giống các chỗ khác trong hệ thống: ưu tiên Brevo API (máy chủ thường chặn cổng SMTP), lỗi thì dùng SMTP.
     * Chỉ trả true khi thư thật sự được gửi đi; mailer "log"/"array" chỉ ghi lại chứ không gửi nên tính là chưa gửi.
     * Log chỉ ghi loại lỗi, không ghi mã OTP hay địa chỉ email.
     */
    private function deliver(User $user, Mailable $mail, string $kind): bool
    {
        if (BrevoMailService::isConfigured()) {
            try {
                if (BrevoMailService::send($user->email, $user->name, (string) $mail->envelope()->subject, $mail->render())) {
                    return true;
                }
            } catch (\Throwable $e) {
                Log::warning("SupportBot: gửi email {$kind} qua Brevo API thất bại (" . $e::class . "), thử SMTP.");
            }
        }

        $mailer = (string) config('mail.default');
        if (in_array($mailer, ['log', 'array'], true)) {
            Log::error("SupportBot: chưa cấu hình gửi email thật (Brevo API lỗi/thiếu, MAIL_MAILER={$mailer}), không gửi được email {$kind} cho user #{$user->id}");

            return false;
        }

        try {
            Mail::to($user->email)->send($mail);

            return true;
        } catch (\Throwable $e) {
            Log::error("SupportBot: gửi email {$kind} thất bại (" . $e::class . ") cho user #{$user->id}");

            return false;
        }
    }

    // =========================================================================
    // 🧰 TIỆN ÍCH
    // =========================================================================

    private function handoffNotice(): string
    {
        return 'Mình đã chuyển yêu cầu của bạn cho Ban Quản Trị. 🙏 Bạn chuyển sang tab 👨‍💼 Ban Quản Trị, để lại SĐT/Zalo và nhắn tin ở đó để Admin trả lời trực tiếp nhé!';
    }

    private function guestHandoffNotice(): string
    {
        return 'Để gặp Ban Quản Trị, bạn vui lòng đăng nhập tài khoản trước nhé. Khi đã đăng nhập, bạn sẽ có tab 👨‍💼 Ban Quản Trị để nhắn tin và lịch sử được lưu lại để đối chiếu. 🙏';
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

    /** Giới hạn chi phí: tối đa AI_LIMIT_CONVERSATION lượt/giờ mỗi cuộc và AI_LIMIT_IP lượt/giờ mỗi IP. */
    private function aiBudgetAvailable(Request $request, SupportMessage $msg): bool
    {
        [$convKey, $ipKey] = $this->budgetKeys($request, $msg);

        return (int) Cache::get($convKey, 0) < self::AI_LIMIT_CONVERSATION
            && (int) Cache::get($ipKey, 0) < self::AI_LIMIT_IP;
    }

    private function countAiUse(Request $request, SupportMessage $msg): void
    {
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
    }

    /** Che bớt email: ab***@gmail.com */
    private function maskEmail(string $email): string
    {
        [$local, $domain] = explode('@', $email, 2) + [1 => ''];
        $visible = mb_substr($local, 0, min(2, mb_strlen($local)));

        return $visible . str_repeat('*', max(3, mb_strlen($local) - mb_strlen($visible))) . '@' . $domain;
    }
}
