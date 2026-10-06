<?php

namespace App\Services;

use App\Models\PracticeTest;
use App\Models\TestAttempt;
use App\Models\User;
use App\Support\VietText;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Bộ "hàm" AI được tự gọi khi trò chuyện bằng giọng nói (function calling).
 *
 * AI đọc câu hỏi, tự chọn hàm và tham số (chủ đề, khoảng ngày, tên bài...), máy chủ chạy hàm rồi đưa kết quả lại cho AI.
 * An toàn:
 * - AI KHÔNG tự viết truy vấn database; chỉ gọi được các hàm viết sẵn dưới đây.
 * - Mọi hàm chỉ đọc dữ liệu của chính người đang nói (giáo viên chỉ đọc học sinh của mình), không ghi gì.
 * - Tham số lạ hoặc sai kiểu bị bỏ qua; số dòng trả về có giới hạn để tiết kiệm token.
 */
class VoiceToolbox
{
    /** Hàm AI gọi để hiện thẻ thống kê (máy chủ dựng thẻ từ database) */
    public const STATS_CARD = 'hien_the_thong_ke';
    /** Tên các mục trong tài liệu hướng dẫn (dòng "## ..." của resources/support-bot/faq.md) */
    public const FAQ_TITLES = ['Đăng nhập', 'Mua và thuê gói', 'Các khu vực trong hệ thống', 'Khi nào phải chuyển cho Ban Quản Trị'];
    private const MAX_ROWS = 15;
    private const MAX_STUDENT_ROWS = 30;

    public function __construct(private AiAssistantService $assistant, private VoiceQuizService $quiz, private VoiceCardService $cards)
    {
    }

    /**
     * Danh sách hàm theo chuẩn OpenAI "tools" (9Router hiểu được), tùy vai trò người dùng.
     */
    public function definitions(User $user): array
    {
        $tools = [
            $this->tool('thong_tin_tro_ly', 'Thông tin về chính bạn (trợ lý) và hệ thống: tên, ai tạo ra, biệt danh người tạo, làm được gì. Dùng khi được hỏi về bản thân bạn hoặc người tạo ra bạn.', []),
            $this->tool('ket_qua_bai_lam', 'Lấy các lần làm bài của người đang nói kèm thống kê (số lần, điểm trung bình, cao nhất, thấp nhất, số lần đạt). Dùng khi hỏi điểm/kết quả theo chủ đề, theo ngày, theo bài, bài đạt hay chưa đạt.', [
                'chu_de' => ['type' => 'string', 'description' => 'Tên chủ đề, ví dụ "Công dân số"'],
                'khoi' => ['type' => 'integer', 'description' => 'Khối lớp, ví dụ 3'],
                'ten_bai' => ['type' => 'string', 'description' => 'Một phần tên bài'],
                'tu_ngay' => ['type' => 'string', 'description' => 'Ngày bắt đầu, dạng YYYY-MM-DD'],
                'den_ngay' => ['type' => 'string', 'description' => 'Ngày kết thúc, dạng YYYY-MM-DD'],
                'ket_qua' => ['type' => 'string', 'enum' => ['tat_ca', 'dat', 'chua_dat']],
                'sap_xep' => ['type' => 'string', 'enum' => ['moi_nhat', 'diem_cao_nhat', 'diem_thap_nhat']],
                'so_luong' => ['type' => 'integer', 'description' => 'Tối đa 15'],
            ]),
            $this->tool('tien_bo', 'So sánh điểm trung bình của N ngày gần đây với N ngày trước đó để biết có tiến bộ không.', [
                'so_ngay' => ['type' => 'integer', 'description' => 'Số ngày mỗi giai đoạn, mặc định 7'],
            ]),
            $this->tool('danh_sach_bai', 'Tìm bài luyện tập / đề thi thử người đang nói được làm, kèm mã để mở bài (lam_...) hoặc xem kết quả (xem_...).', [
                'chu_de' => ['type' => 'string'],
                'ten_bai' => ['type' => 'string'],
                'trang_thai' => ['type' => 'string', 'enum' => ['chua_lam', 'da_lam', 'tat_ca']],
                'loai' => ['type' => 'string', 'enum' => ['luyen_tap', 'thi_thu', 'tat_ca']],
            ]),
            $this->tool('chu_de_theo_khoi', 'Danh sách chủ đề của các khối người đang nói được học, kèm số câu hỏi luyện được.', [
                'khoi' => ['type' => 'integer'],
            ]),
            $this->tool('huong_dan', 'Đọc tài liệu hướng dẫn dùng hệ thống.', [
                'muc' => ['type' => 'string', 'enum' => self::FAQ_TITLES],
            ], ['muc']),
        ];

        if ($user->isStudent()) {
            $tools[] = $this->tool(self::STATS_CARD, 'Hiện thẻ thống kê kết quả học tập lên màn hình (ô số liệu, biểu đồ điểm các bài gần đây, chủ đề cần ôn) khi người nói muốn xem tổng quan. Trả về tóm tắt để bạn nhận xét.', []);
            $tools[] = $this->tool('cau_sai', 'Lấy các câu em làm sai chưa khắc phục (nội dung câu, số lần sai), có thể lọc theo chủ đề.', [
                'chu_de' => ['type' => 'string'],
                'so_luong' => ['type' => 'integer', 'description' => 'Tối đa 15'],
            ]);
        }
        if ($user->isTeacher()) {
            $tools[] = $this->tool('hoc_sinh', 'Tiến độ học sinh do thầy cô quản lý; có tên thì kèm các bài gần đây của học sinh đó.', [
                'ten' => ['type' => 'string', 'description' => 'Tên hoặc mã học sinh'],
            ]);
        }

        return $tools;
    }

    /**
     * Chạy một hàm AI yêu cầu. Luôn trả về mảng (lỗi cũng trả về dạng {"loi": "..."} để AI tự xử lý).
     */
    public function run(User $user, string $name, array $args): array
    {
        try {
            return match ($name) {
                'ket_qua_bai_lam' => $this->attempts($user, $args),
                'tien_bo' => $this->progress($user, $args),
                'danh_sach_bai' => $this->tests($user, $args),
                'chu_de_theo_khoi' => $this->topics($user, $args),
                'thong_tin_tro_ly' => (array) config('voice.about'),
                'huong_dan' => $this->guide($args),
                self::STATS_CARD => $this->statsSummary($user),
                'cau_sai' => $user->isStudent() ? $this->mistakes($user, $args) : ['loi' => 'Chỉ học sinh mới có sổ tay câu sai'],
                'hoc_sinh' => $user->isTeacher() ? $this->students($user, $args) : ['loi' => 'Chỉ giáo viên mới xem được học sinh'],
                default => ['loi' => 'Không có hàm này'],
            };
        } catch (\Throwable $e) {
            report($e);

            return ['loi' => 'Chưa lấy được dữ liệu'];
        }
    }

    // =========================================================================
    // 🔧 CÁC HÀM
    // =========================================================================

    private function attempts(User $user, array $args): array
    {
        $topic = $this->topicFilter($user, $args['chu_de'] ?? null);
        if (isset($topic['loi'])) {
            return $topic;
        }
        $from = $this->date($args['tu_ngay'] ?? null)?->startOfDay();
        $to = $this->date($args['den_ngay'] ?? null)?->endOfDay();
        $tz = $this->tz();

        $rows = TestAttempt::where('user_id', $user->id)
            ->with(['practiceTest.level', 'practiceTest.topic.level'])
            ->when($topic['ids'] !== null, fn ($q) => $q->whereHas('practiceTest', fn ($t) => $t->whereIn('topic_id', $topic['ids'])))
            ->when($from, fn ($q) => $q->where('completed_at', '>=', $from->copy()->setTimezone(config('app.timezone'))))
            ->when($to, fn ($q) => $q->where('completed_at', '<=', $to->copy()->setTimezone(config('app.timezone'))))
            ->when(is_string($args['ten_bai'] ?? null) && trim($args['ten_bai']) !== '', fn ($q) => $q->whereHas('practiceTest', fn ($t) => $t->where('name', 'like', '%' . trim($args['ten_bai']) . '%')))
            ->get()
            ->filter(fn (TestAttempt $a) => isset($args['khoi']) ? (int) $this->assistant->levelOf($a->practiceTest)?->grade === (int) $args['khoi'] : true);

        $rows = match ($args['ket_qua'] ?? 'tat_ca') {
            'dat' => $rows->filter(fn ($a) => $this->assistant->passed($a)),
            'chua_dat' => $rows->reject(fn ($a) => $this->assistant->passed($a)),
            default => $rows,
        };

        $sorted = match ($args['sap_xep'] ?? 'moi_nhat') {
            'diem_cao_nhat' => $rows->sortByDesc('score'),
            'diem_thap_nhat' => $rows->sortBy('score'),
            default => $rows->sortByDesc('completed_at'),
        };

        // Nhiều chủ đề có bài trùng tên ("Bài luyện 2"): báo rõ là các bài KHÁC NHAU để AI không gộp làm một
        $distinct = $rows->groupBy('practice_test_id');
        $sameName = $distinct->count() > 1 && is_string($args['ten_bai'] ?? null)
            ? $distinct->map(fn ($g) => [
                'chu_de' => $g->first()->practiceTest?->topic?->name ?? 'Đề thi thử',
                'khoi' => $this->assistant->levelOf($g->first()->practiceTest)?->name,
                'so_lan' => $g->count(),
                'diem_cao_nhat' => $g->max('score'),
                'diem_gan_nhat' => $g->sortByDesc('completed_at')->first()->score,
            ])->values()->all()
            : null;

        return [
            'dieu_kien' => array_filter([
                'chu_de' => $topic['names'] ?? null,
                'tu_ngay' => $from?->format('d/m/Y'),
                'den_ngay' => $to?->format('d/m/Y'),
            ]),
            'luu_y' => $sameName ? 'Có ' . count($sameName) . ' bài KHÁC NHAU cùng tên ở các chủ đề khác nhau. Không gộp chung: nêu theo từng chủ đề hoặc hỏi em muốn xem chủ đề nào.' : null,
            'theo_tung_bai' => $sameName,
            'tong_so_lan' => $rows->count(),
            'so_lan_dat' => $rows->filter(fn ($a) => $this->assistant->passed($a))->count(),
            'diem_trung_binh' => $rows->isEmpty() ? null : (int) round($rows->avg('score')),
            'diem_cao_nhat' => $rows->max('score'),
            'diem_thap_nhat' => $rows->min('score'),
            'danh_sach' => $sorted->take($this->limit($args))->map(fn (TestAttempt $a) => $this->assistant->attemptRow($a, $tz) + [
                'chu_de' => $a->practiceTest?->topic?->name ?? ($a->practiceTest?->is_mock ? 'Đề thi thử' : null),
            ])->values()->all(),
        ];
    }

    /** Tóm tắt thẻ thống kê để AI nhận xét (thẻ đầy đủ do máy chủ gửi thẳng ra màn hình) */
    private function statsSummary(User $user): array
    {
        $card = $this->cards->statsCard($user);
        if ($card === null) {
            return ['loi' => 'Chỉ học sinh mới có thẻ thống kê'];
        }

        return [
            'the_da_hien_tren_man_hinh' => true,
            'ghi_chu' => 'Người nói đang nhìn thấy các con số trên thẻ: chỉ nhận xét 1 đến 2 câu, không đọc lại các con số.',
            'so_lieu' => collect($card['tiles'])->pluck('value', 'label')->all(),
            'chu_de_can_on' => array_column($card['weak'] ?? [], 'label'),
        ];
    }

    private function progress(User $user, array $args): array
    {
        $days = max(1, min(90, (int) ($args['so_ngay'] ?? 7)));
        $now = now();
        $period = function (Carbon $from, Carbon $to) use ($user) {
            $scores = TestAttempt::where('user_id', $user->id)->whereBetween('completed_at', [$from, $to])->pluck('score');

            return ['so_lan_lam' => $scores->count(), 'diem_trung_binh' => $scores->isEmpty() ? null : (int) round($scores->avg())];
        };
        $recent = $period($now->copy()->subDays($days), $now);
        $before = $period($now->copy()->subDays(2 * $days), $now->copy()->subDays($days));

        return [
            'so_ngay_moi_giai_doan' => $days,
            'gan_day' => $recent,
            'truoc_do' => $before,
            'nhan_xet' => match (true) {
                $recent['diem_trung_binh'] === null => 'gần đây chưa làm bài',
                $before['diem_trung_binh'] === null => 'giai đoạn trước chưa làm bài nên chưa so sánh được',
                $recent['diem_trung_binh'] > $before['diem_trung_binh'] => 'tiến bộ',
                $recent['diem_trung_binh'] < $before['diem_trung_binh'] => 'giảm',
                default => 'giữ nguyên',
            },
        ];
    }

    private function tests(User $user, array $args): array
    {
        $topic = $this->topicFilter($user, $args['chu_de'] ?? null);
        if (isset($topic['loi'])) {
            return $topic;
        }
        $done = TestAttempt::where('user_id', $user->id)
            ->selectRaw('practice_test_id, MAX(score) as best')
            ->groupBy('practice_test_id')
            ->pluck('best', 'practice_test_id');

        $query = $this->assistant->accessibleTests($user)
            ->when($topic['ids'] !== null, fn ($q) => $q->whereIn('topic_id', $topic['ids']))
            ->when(is_string($args['ten_bai'] ?? null) && trim($args['ten_bai']) !== '', fn ($q) => $q->where('name', 'like', '%' . trim($args['ten_bai']) . '%'))
            ->when(($args['loai'] ?? null) === 'luyen_tap', fn ($q) => $q->where('is_mock', false))
            ->when(($args['loai'] ?? null) === 'thi_thu', fn ($q) => $q->where('is_mock', true));
        match ($args['trang_thai'] ?? 'tat_ca') {
            'chua_lam' => $query->whereNotIn('id', $done->keys()),
            'da_lam' => $query->whereIn('id', $done->keys()),
            default => null,
        };
        $total = (clone $query)->count();

        return [
            'tong_so_bai' => $total,
            'danh_sach' => $query->limit(self::MAX_ROWS)->get()->map(fn (PracticeTest $t) => [
                'ma_mo_bai' => 'lam_' . $t->id,
                'ma_xem_ket_qua' => $done->has($t->id) ? 'xem_' . $t->id : null,
                'ten_bai' => $t->name,
                'loai' => $t->is_mock ? 'Đề thi thử' : 'Luyện tập',
                'chu_de' => $t->topic?->name,
                'khoi' => $this->assistant->levelOf($t)?->name,
                'diem_dat' => $t->pass_score,
                'diem_cao_nhat_cua_em' => $done->get($t->id),
            ])->values()->all(),
        ];
    }

    private function topics(User $user, array $args): array
    {
        $catalog = $this->quiz->catalog($user);
        if (isset($args['khoi'])) {
            $catalog = array_values(array_filter($catalog, fn ($row) => preg_match('/\bkh[oố]i\s*' . (int) $args['khoi'] . '\b/iu', $row['khoi'])));
        }

        return ['khoi' => $catalog];
    }

    private function guide(array $args): array
    {
        $title = (string) ($args['muc'] ?? '');
        $path = resource_path('support-bot/faq.md');
        if (! is_file($path)) {
            return ['loi' => 'Chưa có tài liệu hướng dẫn'];
        }
        foreach (preg_split('/^## /mu', (string) file_get_contents($path)) as $part) {
            if ($title !== '' && str_starts_with(trim($part), $title)) {
                return [
                    'noi_dung' => trim($part),
                    'ghi_chu' => 'Tài liệu viết cho khung chat hỗ trợ bằng chữ ở góc màn hình; người nói đang trò chuyện bằng giọng với bạn, nên "khung chat này" nghĩa là khung chat hỗ trợ ở góc màn hình.',
                ];
            }
        }

        return ['loi' => 'Không có mục này', 'cac_muc' => self::FAQ_TITLES];
    }

    private function mistakes(User $user, array $args): array
    {
        $topic = $this->topicFilter($user, $args['chu_de'] ?? null);
        if (isset($topic['loi'])) {
            return $topic;
        }
        $rows = $this->quiz->mistakesIn($user, $topic['ids'], $this->limit($args));

        return ['so_cau' => count($rows), 'danh_sach' => $rows];
    }

    private function students(User $teacher, array $args): array
    {
        $tz = $this->tz();
        $all = collect($this->assistant->studentsOf($teacher, $tz));
        $name = is_string($args['ten'] ?? null) ? VietText::norm($args['ten']) : '';
        if ($name === '') {
            return ['so_hoc_sinh' => $all->count(), 'danh_sach' => $all->take(self::MAX_STUDENT_ROWS)->values()->all()];
        }

        // Chỉ tìm trong học sinh của chính thầy cô
        $student = User::where('created_by', $teacher->id)->where('role', 'student')->get()
            ->first(fn (User $s) => str_contains(VietText::norm((string) $s->name), $name) || VietText::norm((string) $s->student_code) === $name);
        if (! $student) {
            return ['loi' => 'Không tìm thấy học sinh này trong lớp của thầy cô'];
        }

        return [
            'hoc_sinh' => $all->firstWhere('ma_hoc_sinh', $student->student_code) ?? ['ten' => $student->name],
            'bai_gan_day' => TestAttempt::where('user_id', $student->id)->with(['practiceTest.level', 'practiceTest.topic.level'])
                ->latest('completed_at')->limit(8)->get()
                ->map(fn ($a) => $this->assistant->attemptRow($a, $tz))->values()->all(),
            'so_cau_sai_chua_khac_phuc' => $student->isStudent() ? \App\Models\StudentMistake::where('user_id', $student->id)->where('status', 'unresolved')->count() : 0,
        ];
    }

    // =========================================================================
    // 🧰 TIỆN ÍCH
    // =========================================================================

    private function tool(string $name, string $description, array $properties, array $required = []): array
    {
        return ['type' => 'function', 'function' => [
            'name' => $name,
            'description' => $description,
            'parameters' => ['type' => 'object', 'properties' => (object) $properties] + ($required ? ['required' => $required] : []),
        ]];
    }

    /** Lọc chủ đề: null = không lọc; không khớp chủ đề nào thì báo lỗi kèm danh sách thật để AI tự sửa */
    private function topicFilter(User $user, mixed $name): array
    {
        if (! is_string($name) || trim($name) === '') {
            return ['ids' => null];
        }
        $ids = $this->quiz->topicIdsFor($user, $name);
        if ($ids === []) {
            return ['loi' => "Không có chủ đề \"{$name}\"", 'cac_chu_de_that' => $this->quiz->topicNamesFor($user)];
        }

        return ['ids' => $ids, 'names' => \App\Models\Topic::whereIn('id', $ids)->pluck('name')->unique()->implode(', ')];
    }

    private function date(mixed $value): ?Carbon
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }
        try {
            return Carbon::createFromFormat('Y-m-d', $value, $this->tz());
        } catch (\Throwable) {
            return null;
        }
    }

    private function limit(array $args): int
    {
        return max(1, min(self::MAX_ROWS, (int) ($args['so_luong'] ?? 8)));
    }

    private function tz(): string
    {
        return (string) config('learning.display_timezone', 'Asia/Ho_Chi_Minh');
    }
}
