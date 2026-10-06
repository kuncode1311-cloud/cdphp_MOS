<?php

namespace App\Services;

use App\Models\Level;
use App\Models\PracticeTest;
use App\Models\TestAttempt;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Trợ lý AI cho tài khoản có gói Trợ lý AI còn hạn.
 *
 * - Chỉ đọc dữ liệu của chính người đang chat (học sinh), học sinh của mình (giáo viên),
 *   hoặc số liệu tổng quan (quản trị viên). Dữ liệu được tổng hợp sẵn ở máy chủ, AI không viết SQL.
 * - AI chỉ được chọn hành động (mở trang, làm bài) từ danh sách máy chủ đưa ra, không tự tạo đường dẫn.
 * - Không ghi dữ liệu: không đổi mật khẩu, không mua gói, không nộp bài thay học sinh.
 */
class AiAssistantService
{
    private const RECENT_ATTEMPTS = 15;
    private const PENDING_TESTS = 25;
    private const STUDENTS = 50;

    public function __construct(private AiChatClient $client)
    {
    }

    /**
     * Trả lời một tin nhắn của thành viên có gói Trợ lý AI.
     *
     * @param  array<int, array{role: string, text: string}>  $turns  Hội thoại gần nhất, lượt cuối là của khách
     * @return array{text: string, action: ?array{label: string, url: string, auto?: bool}}|null  null nếu AI không phản hồi
     */
    public function answer(User $user, array $turns, string $faq): ?array
    {
        $actions = $this->actionCatalog($user);

        $raw = $this->client->complete($this->systemPrompt($user, $faq, $actions), $turns, 700);
        if ($raw === null) {
            return null;
        }

        return $this->parseReply($raw, $actions, (string) end($turns)['text']);
    }

    /** Khách nói rõ muốn mở/vào/chuyển trang (kể cả gõ sai như "mo tang"): tự mở ngay, không chờ bấm nút. */
    public function asksToOpen(string $text): bool
    {
        return (bool) preg_match('/\b(m[oở]|mwor|mowr|mor|mơ|vào|vao|chuyển|chuyen|sang|đưa|dua|tới|toi|open|làm\s*bài|lam\s*bai)\b/iu', $text);
    }

    /**
     * Dữ liệu học tập theo đúng vai trò của người dùng. Đây là toàn bộ những gì AI được biết về tài khoản.
     *
     * @return array<string, mixed>
     */
    public function dataFor(User $user): array
    {
        $tz = config('learning.display_timezone', 'Asia/Ho_Chi_Minh');

        $data = [
            'hom_nay' => now()->setTimezone($tz)->format('H:i d/m/Y'),
            'tai_khoan' => [
                'ten' => $user->name,
                'vai_tro' => $this->roleLabel($user),
                'ma_hoc_sinh' => $user->student_code,
                'email' => $this->maskEmail($user->email),
                'so_dien_thoai' => $user->maskedPhone(),
                'trang_thai' => $user->status ?? 'active',
            ],
            'goi_dang_dung' => $this->accountPackage($user, $tz),
            'quyen_truy_cap' => [
                'goi_he_thong_con_hieu_luc' => $user->isSubscriptionActive(),
                'ly_do_bi_chan' => $user->subscriptionBlockedMessage(),
                'tro_ly_ai_con_han' => $user->hasAiAssistant(),
                'het_han_tro_ly_ai' => $user->ai_assistant_until?->setTimezone($tz)->format('d/m/Y'),
            ],
        ];

        if ($user->isAdmin()) {
            $data['tong_quan_he_thong'] = [
                'so_hoc_sinh' => User::where('role', 'student')->count(),
                'so_giao_vien' => User::where('role', 'teacher')->count(),
                'so_luot_lam_bai' => TestAttempt::count(),
            ];
        }

        $data['ket_qua_gan_day'] = $this->recentAttempts($user)
            ->map(fn (TestAttempt $a) => $this->attemptRow($a, $tz))
            ->values()
            ->all();
        $data['thong_ke'] = $this->stats($user);
        $data['bai_chua_lam'] = $this->pendingTests($user)
            ->map(fn (PracticeTest $t) => ['ma_bai' => $t->id, 'ten_bai' => $t->name, 'khoi' => $this->levelOf($t)?->name, 'diem_dat' => $t->pass_score])
            ->values()
            ->all();

        if ($user->isTeacher()) {
            $data['quan_ly_hoc_sinh'] = [
                'so_hoc_sinh_hien_co' => $user->students()->count(),
                'han_muc_hoc_sinh' => (int) $user->max_students,
                'con_tao_duoc_them' => $user->max_students ? $user->remainingStudentSlots() : 'khong_gioi_han',
                'cac_lop_dang_phu_trach' => $user->teachingClassrooms()->orderBy('name')->pluck('name')->values()->all(),
                'cac_khoi_duoc_cap' => $user->teacherLevels()->orderBy('position')->pluck('name')->values()->all(),
            ];
            $data['hoc_sinh_cua_toi'] = $this->studentsOf($user, $tz);
        }

        return $data;
    }

    /**
     * Tóm tắt gói/hạn dùng theo dữ liệu tài khoản, để AI trả lời được câu hỏi "gói của tôi",
     * "hạn dùng", "quản lý được bao nhiêu học sinh" mà không phải đoán.
     *
     * @return array<string, mixed>
     */
    private function accountPackage(User $user, string $tz): array
    {
        $snapshot = $user->accountSnapshot();

        return [
            'ten_goi' => $snapshot['package'],
            'het_han' => $user->expires_at?->setTimezone($tz)->format('d/m/Y') ?? $snapshot['expires'],
            'ke_thua_tu_giao_vien' => (bool) ($snapshot['inherited'] ?? false),
            'giao_vien_quan_ly' => $snapshot['teacher'] ?? null,
            'don_dang_cho_thanh_toan' => $snapshot['pending_package'] ?? null,
            'so_hoc_sinh_quan_ly' => $snapshot['students'] ?? null,
            'han_muc_hoc_sinh' => $snapshot['max_students'] ?? null,
        ];
    }

    private function maskEmail(?string $email): ?string
    {
        $email = trim((string) $email);
        if ($email === '' || ! str_contains($email, '@')) {
            return null;
        }

        [$name, $domain] = explode('@', $email, 2);
        $prefix = mb_substr($name, 0, 2);

        return $prefix . str_repeat('•', max(3, mb_strlen($name) - 2)) . '@' . $domain;
    }

    /**
     * Danh sách hành động AI được phép chọn. Mã hành động là khóa, giá trị là nhãn và đường dẫn do máy chủ tạo.
     *
     * @return array<string, array{label: string, url: string}>
     */
    public function actionCatalog(User $user): array
    {
        $actions = [
            'trang_hoc' => ['label' => 'Học và luyện IC3', 'url' => route('programs')],
            'thanh_tich' => ['label' => 'Điểm và thành tích', 'url' => route('achievements')],
            'so_tay' => ['label' => 'Sổ tay câu sai', 'url' => route('mistakes.index')],
            'tro_choi' => ['label' => 'Chơi nhận thưởng', 'url' => route('games')],
            'bang_gia' => ['label' => 'Gói bản quyền', 'url' => route('pricing.index')],
        ];

        if ($user->isStudent()) {
            $actions['phu_huynh'] = ['label' => 'Góc Phụ Huynh', 'url' => route('parent.dashboard')];
        }
        if ($user->canAccessAdmin()) {
            $actions['quan_tri'] = ['label' => 'Trang quản trị', 'url' => route('admin.dashboard')];
        }

        $actions['trang_chu'] = ['label' => 'Trang của em', 'url' => route('home')];
        $actions['lich_su_goi'] = ['label' => 'Lịch sử thuê gói', 'url' => route('pricing.history')];
        $actions['lam_lai_cau_sai'] = ['label' => 'Làm lại các câu đã sai', 'url' => route('mistakes.launch')];

        // Mỗi khối lớp là một trang riêng (ví dụ "khối 3"), lấy từ CSDL
        foreach (Level::orderBy('grade')->orderBy('position')->limit(20)->get() as $level) {
            $actions['khoi_' . $level->id] = ['label' => "Khối {$level->grade} «{$level->name}»", 'url' => route('levels.show', $level)];
        }

        // Các bài luyện thi trong CSDL, để khách nhờ mở đúng bài theo tên
        foreach (PracticeTest::orderBy('id')->limit(30)->get() as $test) {
            $actions['bai_' . $test->id] = ['label' => "Bài luyện «{$test->name}»", 'url' => route('tests.show', $test)];
        }

        foreach ($this->pendingTests($user) as $test) {
            $actions['lam_' . $test->id] = ['label' => "Làm bài «{$test->name}»", 'url' => route('tests.launch', $test)];
        }
        foreach ($this->recentAttempts($user)->unique('practice_test_id') as $attempt) {
            $test = $attempt->practiceTest;
            if ($test) {
                $actions['xem_' . $test->id] = ['label' => "Xem kết quả «{$test->name}»", 'url' => route('tests.show', $test)];
            }
        }

        return $actions;
    }

    // =========================================================================
    // 🧩 DỮ LIỆU THEO VAI TRÒ
    // =========================================================================

    private function roleLabel(User $user): string
    {
        return match (true) {
            $user->isAdmin() => 'Quản trị viên',
            $user->isTeacher() => 'Giáo viên',
            default => 'Học sinh',
        };
    }

    /** Khối lớp người dùng được học/dạy; null nghĩa là tất cả (quản trị viên) */
    private function accessibleLevelIds(User $user): ?array
    {
        if ($user->isAdmin()) {
            return null;
        }
        if ($user->isTeacher()) {
            return $user->teacherLevels()->pluck('levels.id')->all();
        }

        return $user->accessibleLevels()->pluck('levels.id')->all();
    }

    /**
     * Bài đã xuất bản mà người dùng được phép làm.
     * Đề thi thử thuộc khối qua level_id; bài luyện thuộc khối qua chủ đề (giống màn học tập).
     */
    public function accessibleTests(User $user): Builder
    {
        $query = PracticeTest::query()->with(['level', 'topic.level'])->where('is_published', true)->orderBy('position');
        $levelIds = $this->accessibleLevelIds($user);

        if ($levelIds === null) {
            return $query;
        }

        $levelIds = $levelIds ?: [0];

        return $query->where(function (Builder $q) use ($levelIds) {
            $q->where(fn (Builder $mock) => $mock->where('is_mock', true)->whereIn('level_id', $levelIds))
                ->orWhere(fn (Builder $practice) => $practice->where('is_mock', false)
                    ->whereHas('topic', fn (Builder $topic) => $topic->whereIn('level_id', $levelIds)));
        });
    }

    /** Khối lớp của một bài: đề thi thử dùng level_id, bài luyện dùng khối của chủ đề */
    public function levelOf(?PracticeTest $test): ?Level
    {
        if (! $test) {
            return null;
        }

        return $test->is_mock ? $test->level : $test->topic?->level;
    }

    /** @return Collection<int, TestAttempt> */
    private function recentAttempts(User $user): Collection
    {
        return TestAttempt::where('user_id', $user->id)
            ->with(['practiceTest.level', 'practiceTest.topic.level'])
            ->latest('completed_at')
            ->limit(self::RECENT_ATTEMPTS)
            ->get();
    }

    /** @return Collection<int, PracticeTest> */
    private function pendingTests(User $user): Collection
    {
        $done = TestAttempt::where('user_id', $user->id)->pluck('practice_test_id');

        return $this->accessibleTests($user)
            ->whereNotIn('id', $done)
            ->limit(self::PENDING_TESTS)
            ->get();
    }

    /** Thống kê trên toàn bộ lịch sử làm bài của người dùng */
    private function stats(User $user): array
    {
        $attempts = TestAttempt::where('user_id', $user->id)->with('practiceTest:id,name,pass_score')->get();
        if ($attempts->isEmpty()) {
            return ['so_bai_da_lam' => 0];
        }

        $best = $attempts->sortByDesc('score')->first();
        $worst = $attempts->sortBy('score')->first();

        return [
            'so_bai_da_lam' => $attempts->count(),
            'so_bai_dat' => $attempts->filter(fn (TestAttempt $a) => $this->passed($a))->count(),
            'diem_trung_binh' => (int) round($attempts->avg('score')),
            'bai_tot_nhat' => ['ten_bai' => $best->practiceTest?->name, 'diem' => $best->score],
            'bai_thap_nhat' => ['ten_bai' => $worst->practiceTest?->name, 'diem' => $worst->score],
        ];
    }

    /** Tổng hợp tiến độ của từng học sinh do giáo viên quản lý (chỉ học sinh của chính giáo viên đó) */
    public function studentsOf(User $teacher, string $tz): array
    {
        $students = User::where('created_by', $teacher->id)->where('role', 'student')->orderBy('name')->limit(self::STUDENTS)->get();
        $lastAttempts = TestAttempt::whereIn('user_id', $students->pluck('id'))
            ->with('practiceTest:id,name,pass_score')
            ->latest('completed_at')
            ->get()
            ->groupBy('user_id');

        return $students->map(function (User $student) use ($lastAttempts, $tz) {
            $attempts = $lastAttempts->get($student->id, collect());
            $last = $attempts->first();

            return [
                'ten' => $student->name,
                'ma_hoc_sinh' => $student->student_code,
                'so_bai_da_lam' => $attempts->count(),
                'so_bai_dat' => $attempts->filter(fn (TestAttempt $a) => $this->passed($a))->count(),
                'diem_trung_binh' => $attempts->isEmpty() ? null : (int) round($attempts->avg('score')),
                'diem_cao_nhat' => $attempts->max('score'),
                'diem_thap_nhat' => $attempts->min('score'),
                'bai_gan_nhat' => $last?->practiceTest?->name,
                'diem_gan_nhat' => $last?->score,
                'ngay_gan_nhat' => $last?->completed_at?->setTimezone($tz)->format('d/m/Y H:i'),
            ];
        })->values()->all();
    }

    public function attemptRow(TestAttempt $attempt, string $tz): array
    {
        return [
            'bai' => $attempt->practiceTest?->name,
            // Nhiều chủ đề có bài trùng tên ("Bài luyện 1"): luôn kèm chủ đề để không lẫn
            'chu_de' => $attempt->practiceTest?->topic?->name ?? ($attempt->practiceTest?->is_mock ? 'Đề thi thử' : null),
            'khoi' => $this->levelOf($attempt->practiceTest)?->name,
            'ngay' => $attempt->completed_at?->setTimezone($tz)->format('d/m/Y H:i'),
            'diem' => $attempt->score,
            'so_cau_dung' => $attempt->correct_answers,
            'tong_so_cau' => $attempt->total_questions,
            'dat' => $this->passed($attempt),
        ];
    }

    /** Đạt khi điểm đạt điểm chuẩn của bài (thang 1000) */
    public function passed(TestAttempt $attempt): bool
    {
        $passScore = (int) ($attempt->practiceTest?->pass_score ?? 700);

        return (int) $attempt->score >= $passScore;
    }

    // =========================================================================
    // 🤖 PROMPT VÀ PHÂN TÍCH TRẢ LỜI
    // =========================================================================

    private function systemPrompt(User $user, string $faq, array $actions): string
    {
        $data = json_encode($this->dataFor($user), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $actionLines = collect($actions)->map(fn (array $a, string $id) => "- {$id}: {$a['label']}")->implode("\n");

        return implode("\n", [
            'Bạn là agent tư vấn IC3 Adventure, thay nhân viên hỗ trợ trả lời thành viên có gói Trợ lý AI.',
            'Xưng "mình", gọi khách là "bạn". Đọc toàn bộ ngữ cảnh, hiểu ý câu mới nhất rồi trả lời tự nhiên như người thật: nhanh, thân thiện, chủ động và dễ hiểu với học sinh tiểu học, giáo viên, phụ huynh.',
            'Bạn giúp tra cứu kết quả học tập, bài đã làm, bài nên làm tiếp, hướng dẫn dùng hệ thống và đề xuất thao tác kế tiếp.',
            '',
            'QUY TẮC BẮT BUỘC:',
            '- Chỉ dùng dữ liệu trong mục DỮ LIỆU CỦA NGƯỜI DÙNG. Tuyệt đối không bịa tên bài, điểm, ngày hay số liệu.',
            '- Nếu dữ liệu không có để trả lời, nói thật là chưa có thông tin và gợi ý khách hỏi Ban Quản Trị.',
            '- Điểm tính theo thang 1000. Bài đạt khi điểm lớn hơn hoặc bằng điểm chuẩn của bài.',
            '- Không bao giờ yêu cầu hay tiết lộ mật khẩu hoặc mã OTP.',
            '- Khi khách muốn mở trang hoặc làm bài, chọn một mã trong mục HÀNH ĐỘNG. Nếu không cần thao tác, chọn null.',
            '- Trường auto chỉ được true khi khách nói rõ muốn mở/chuyển/vào trang hoặc làm bài ngay. Nếu chỉ tư vấn/gợi ý thì auto là false.',
            '- Trả lời như đang chat tư vấn: thường 1-3 câu; nếu cần liệt kê thì dùng tối đa 3 gạch đầu dòng. Không lặp lời chào khi đang trong cuộc trò chuyện.',
            '',
            'ĐỊNH DẠNG TRẢ LỜI: chỉ trả về một JSON duy nhất, không có chữ nào khác, theo mẫu:',
            '{"reply": "nội dung trả lời cho khách", "action": "mã hành động hoặc null", "auto": false}',
            '',
            '=== TÀI LIỆU HỖ TRỢ ===',
            $faq,
            '',
            '=== DỮ LIỆU CỦA NGƯỜI DÙNG (JSON) ===',
            (string) $data,
            '',
            '=== HÀNH ĐỘNG ===',
            $actionLines !== '' ? $actionLines : '(không có)',
        ]);
    }

    /**
     * Đọc JSON từ AI. Nếu AI trả về chữ thường hoặc JSON hỏng thì dùng nguyên văn làm câu trả lời, không chọn hành động.
     *
     * @return array{text: string, action: ?array{label: string, url: string, auto?: bool}}
     */
    private function parseReply(string $raw, array $actions, string $userText): array
    {
        $decoded = json_decode(trim(preg_replace('/^```(?:json)?\s*|\s*```$/m', '', $raw)), true);

        if (! is_array($decoded) && preg_match('/\{.*\}/s', $raw, $match)) {
            $decoded = json_decode($match[0], true);
        }

        if (! is_array($decoded) || ! is_string($decoded['reply'] ?? null) || trim($decoded['reply']) === '') {
            return ['text' => trim($raw), 'action' => null];
        }

        $id = $decoded['action'] ?? null;
        $action = is_string($id) && isset($actions[$id]) ? $actions[$id] : null;
        if ($action !== null) {
            // AI quyết định auto, nhưng nếu khách đã nói rõ "mở/vào trang" thì vẫn tự mở
            $action['auto'] = (bool) ($decoded['auto'] ?? false) || $this->asksToOpen($userText);
        }

        return ['text' => trim($decoded['reply']), 'action' => $action];
    }
}
