<?php

namespace App\Services;

use App\Models\User;
use App\Support\LeakedReasoningFilter;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * "Bộ não" của trò chuyện bằng giọng nói. MỌI câu nói đều do AI phân tích, không nhận dạng bằng từ khóa:
 *
 * 1. Máy chủ đọc database, gửi AI "hồ sơ nhanh" của người đang nói (+ câu hỏi đang làm nếu có).
 * 2. AI hiểu ý, thiếu dữ liệu thì TỰ GỌI HÀM tra cứu trong VoiceToolbox (lọc theo chủ đề, ngày, bài...).
 *    Hàm viết sẵn, chỉ đọc dữ liệu của chính người nói; AI không tự truy vấn database.
 * 3. AI trả lời kèm DẤU HIỆU điều khiển, máy chủ kiểm tra rồi mới làm:
 *    [[LUYEN: chủ đề]] ra câu hỏi · [[CHON: B]] em chọn đáp án (máy chủ tự chấm theo database)
 *    [[DOC_LAI]] / [[DOI_CAU]] / [[DUNG]] điều khiển câu hỏi · [[MO: mã]] nút mở trang · [[MO_NGAY: mã]] tự mở tab
 * 4. SOÁT câu trả lời (VoiceAnswerGuard): bỏ câu có số không có trong dữ liệu, chủ đề không có thật, lộ đáp án.
 */
class VoiceAssistantService
{
    /** Số lượt hội thoại gần nhất AI được nhớ (8 lần hỏi-đáp) */
    private const HISTORY_TURNS = 16;
    /** Mỗi lượt cũ chỉ gửi tối đa chừng này ký tự (đủ ngữ cảnh, đỡ tốn token) */
    private const HISTORY_CHARS = 300;
    private const MAX_TOKENS = 400;
    /** AI được gọi hàm tối đa 2 vòng (vd: tìm bài → xem kết quả bài đó), vòng sau bắt buộc trả lời */
    private const MAX_TOOL_ROUNDS = 2;
    private const MAX_CALLS_PER_ROUND = 3;
    /** Các lệnh điều khiển câu hỏi AI được dùng khi em đang làm câu hỏi */
    private const CONTROLS = ['DOC_LAI' => 'doc_lai', 'DOI_CAU' => 'doi_cau', 'DUNG' => 'dung'];

    public function __construct(
        private AiChatClient $client,
        private AiAssistantService $assistant,
        private VoiceQuizService $quiz,
        private VoiceCardService $cards,
        private VoiceAnswerGuard $guard,
        private VoiceToolbox $toolbox,
    ) {
    }

    /**
     * Trả lời một câu nói. Trả về null nếu AI không phản hồi.
     *
     * @param  array<int, array{role: string, text: string}>  $history  Lượt hội thoại trước đó (không gồm câu hiện tại)
     * @return array{text: string, display: string, quiz_hint: ?string, choice: ?array, control: ?string, action: ?array, card: ?array, route: string, tools: array<int, string>}|null
     */
    public function reply(User $user, array $history, string $text): ?array
    {
        $pending = $this->quiz->hasPending($user);

        $turns = [];
        foreach (array_slice($history, -self::HISTORY_TURNS) as $turn) {
            $turns[] = ['role' => $turn['role'] === 'user' ? 'user' : 'assistant', 'text' => mb_substr((string) $turn['text'], 0, self::HISTORY_CHARS)];
        }
        $turns[] = ['role' => 'user', 'text' => $text];

        [$system, $facts] = $this->buildPrompt($user, $pending);
        [$raw, $toolData, $toolNames, $card] = $this->askWithTools($user, $system, $turns);
        if ($raw === null) {
            return null;
        }
        // Số liệu AI lấy thêm qua hàm cũng là dữ liệu thật: cho phép bộ soát chấp nhận
        if ($toolData !== '') {
            preg_match_all('/\d+/', $toolData, $m);
            $facts['numbers'] = array_values(array_unique(array_merge($facts['numbers'], array_map('intval', $m[0]))));
        }

        // Một số model lỡ ghi cả phần suy nghĩ nội bộ bằng tiếng Anh: bỏ đi trước khi hiện/đọc to
        $raw = LeakedReasoningFilter::clean($raw);
        $base = ['route' => 'ai', 'tools' => $toolNames, 'choice' => null, 'control' => null];
        if ($raw === '') {
            return $this->result($card ? 'Em xem kết quả học tập trong thẻ bên dưới nhé!' : 'Em nói rõ hơn một chút cho cô nghe với nhé!', null, null, $card) + $base;
        }

        [$body, $markerHint] = $this->extractQuizMarker($raw);
        [$body, $action] = $this->extractAction($body, $user);
        // Khách nói rõ "mở/chuyển trang" thì tự mở ngay, không chờ bấm nút (kể cả khi AI chỉ gắn [[MO:]])
        if ($action !== null && $this->assistant->asksToOpen($text)) {
            $action['auto'] = true;
        }
        [$body, $choice, $control] = $this->extractQuizControls($body, $pending);

        // Ra câu hỏi khi AI gắn dấu hiệu, trừ khi AI mới chỉ RỦ (câu cuối là câu hỏi) hoặc đang có câu chờ trả lời
        $hint = null;
        if ($markerHint !== null && ! $pending && $card === null && ! ($action['auto'] ?? false) && ! $this->endsWithQuestion($body)) {
            $hint = $markerHint;
        }

        // Soát câu trả lời
        $checked = $this->guard->check($body, $facts['numbers'], $facts['topics'], $pending);
        $body = $checked['text'] !== ''
            ? $checked['text']
            : ($choice || $control ? '' : 'Cô chưa chắc chắn về điều này nên không muốn nói sai. Em hỏi lại theo cách khác giúp cô nhé!');

        return $this->result($body, $hint, $action, $card) + ['choice' => $choice, 'control' => $control] + $base;
    }

    /** Câu cuối là câu hỏi ("Em có muốn thử không?") nghĩa là AI mới rủ, chờ em đồng ý */
    private function endsWithQuestion(string $body): bool
    {
        $lines = preg_split('/\n+/u', trim($body));

        return str_ends_with(rtrim((string) end($lines), " *\t"), '?');
    }

    /**
     * Hỏi AI và cho AI TỰ GỌI HÀM lấy thêm dữ liệu (function calling):
     * AI đọc câu nói + hồ sơ nhanh → thấy thiếu thì gọi hàm (vd ket_qua_bai_lam(chu_de="Công dân số", tu_ngay=...))
     * → máy chủ chạy hàm (chỉ dữ liệu của chính người nói) → đưa kết quả lại → AI trả lời. Tối đa MAX_TOOL_ROUNDS vòng.
     * 9Router lỗi thì quay về cách trả lời thường (có dự phòng Gemini) để trò chuyện không bị gián đoạn.
     *
     * @return array{0: ?string, 1: string, 2: array<int, string>, 3: ?array}  [câu trả lời thô, dữ liệu các hàm đã trả về, tên các hàm đã gọi, thẻ thống kê nếu AI yêu cầu]
     */
    private function askWithTools(User $user, string $system, array $turns): array
    {
        $tools = $this->toolbox->definitions($user);
        $messages = [['role' => 'system', 'content' => $system]];
        foreach ($turns as $turn) {
            $messages[] = ['role' => $turn['role'], 'content' => $turn['text']];
        }

        $toolData = '';
        $names = [];
        $card = null;
        for ($round = 0; $round <= self::MAX_TOOL_ROUNDS; $round++) {
            $msg = $this->client->chatWithTools($messages, $tools, self::MAX_TOKENS, $round < self::MAX_TOOL_ROUNDS);
            if ($msg === null) {
                break;
            }
            if ($msg['tool_calls'] === [] || $round === self::MAX_TOOL_ROUNDS) {
                if ($msg['content'] !== '') {
                    return [$msg['content'], $toolData, $names, $card];
                }
                break;
            }

            $calls = array_slice($msg['tool_calls'], 0, self::MAX_CALLS_PER_ROUND);
            $messages[] = ['role' => 'assistant', 'content' => $msg['content'], 'tool_calls' => array_map(fn ($c) => [
                'id' => $c['id'], 'type' => 'function',
                'function' => ['name' => $c['name'], 'arguments' => json_encode((object) $c['arguments'], JSON_UNESCAPED_UNICODE)],
            ], $calls)];
            foreach ($calls as $call) {
                $result = json_encode($this->toolbox->run($user, $call['name'], $call['arguments']), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $toolData .= $result . "\n";
                $names[] = $call['name'];
                if ($call['name'] === VoiceToolbox::STATS_CARD) {
                    $card = $this->cards->statsCard($user, $text);
                }
                $messages[] = ['role' => 'tool', 'tool_call_id' => $call['id'], 'name' => $call['name'], 'content' => $result];
                Log::info('VoiceAssistant: AI gọi hàm', ['user' => $user->id, 'ham' => $call['name'], 'tham_so' => $call['arguments']]);
            }
        }

        // Dự phòng: trả lời kiểu thường, kèm dữ liệu các hàm đã lấy được (nếu có)
        $fallbackSystem = $toolData === '' ? $system : $system . "\n\n=== DỮ LIỆU VỪA TRA CỨU ===\n" . $toolData;

        return [$this->client->complete($fallbackSystem, $turns, self::MAX_TOKENS), $toolData, $names, $card];
    }

    /** Đóng gói kết quả: chữ hiện (giữ định dạng) và chữ đọc to (đã làm sạch) */
    private function result(string $display, ?string $quizHint = null, ?array $action = null, ?array $card = null): array
    {
        $display = $this->sanitizeDisplay($display);

        return ['text' => $this->cleanForSpeech($display), 'display' => $display, 'quiz_hint' => $quizHint, 'action' => $action, 'card' => $card];
    }

    // =========================================================================
    // 🧩 PROMPT
    // =========================================================================

    /**
     * @return array{0: string, 1: array{numbers: array<int, int>, topics: array<int, string>}}  [prompt hệ thống, dữ kiện để soát]
     */
    private function buildPrompt(User $user, bool $pending): array
    {
        $isStudent = $user->isStudent();
        $data = $this->assistant->dataFor($user);
        $stats = $data['thong_ke'] ?? [];
        $catalog = $isStudent ? $this->quiz->catalog($user) : [];
        $mistakes = $isStudent ? $this->quiz->mistakeSummary($user) : null;

        // HỒ SƠ NHANH: số liệu chính để trả lời ngay phần lớn câu hỏi; chi tiết hơn thì AI tự gọi hàm
        $profile = array_filter([
            'ten' => $data['tai_khoan']['ten'] ?? $user->name,
            'vai_tro' => $data['tai_khoan']['vai_tro'] ?? null,
            'hom_nay' => $data['hom_nay'] ?? null,
            'thong_ke' => $stats ?: null,
            'ba_bai_gan_nhat' => collect($data['ket_qua_gan_day'] ?? [])->take(3)
                ->map(fn ($r) => array_intersect_key($r, array_flip(['bai', 'chu_de', 'ngay', 'diem', 'dat'])))->all() ?: null,
            'so_bai_chua_lam' => count($data['bai_chua_lam'] ?? []),
            'sao_gio_choi_han_dung' => $isStudent ? $this->rewardsOf($user) : null,
            'cau_sai_chua_khac_phuc' => $mistakes ? ['tong' => $mistakes['tong_so_cau_chua_khac_phuc'], 'theo_chu_de' => $mistakes['theo_chu_de']] : null,
            // Chủ đề đánh số theo từng khối, đúng thứ tự trên trang học: để hiểu "chủ đề 1", "chủ đề thứ hai của khối 4"...
            'chu_de_theo_khoi' => collect($catalog)->mapWithKeys(fn ($k) => [
                $k['khoi'] => collect($k['chu_de'])->values()->map(fn ($t, $i) => ($i + 1) . '. ' . $t['ten'])->all(),
            ])->filter()->all() ?: null,
            'so_hoc_sinh' => $user->isTeacher() ? count($data['hoc_sinh_cua_toi'] ?? []) : null,
        ], fn ($v) => $v !== null);

        $pages = collect($this->assistant->actionCatalog($user))
            ->reject(fn ($a, $code) => (bool) preg_match('/^(lam|xem)_\d+$/', $code))
            ->map(fn ($a, $code) => "{$code}: {$a['label']}");

        $persona = $isStudent
            ? 'Bạn là cô giáo trợ lý AI kiêm agent tư vấn của IC3 Adventure, đang trò chuyện bằng giọng nói với một học sinh tiểu học. Xưng "cô", gọi học sinh là "em", nói ấm áp như giáo viên thật.'
            : 'Bạn là agent tư vấn AI thân thiện của IC3 Adventure, đang trò chuyện bằng giọng nói với một giáo viên. Xưng "mình", gọi người nói là "Thầy/Cô", hỗ trợ như nhân viên thật.';
        $now = now()->setTimezone((string) config('learning.display_timezone', 'Asia/Ho_Chi_Minh'));
        $weekdays = ['Chủ nhật', 'Thứ hai', 'Thứ ba', 'Thứ tư', 'Thứ năm', 'Thứ sáu', 'Thứ bảy'];

        $lines = [
            $persona,
            'ĐỊNH DẠNG BẮT BUỘC: chỉ viết câu trả lời cuối cùng bằng tiếng Việt, đặt trong cặp thẻ <noi> và </noi>. Không viết suy nghĩ, phân tích hay tiếng Anh ở ngoài thẻ.',
            '',
            'NGUYÊN TẮC CHUNG:',
            '1. Hiểu ý như agent: mọi câu nói đều phải đọc trong ngữ cảnh cả cuộc trò chuyện (người nói có thể nói tắt, nói vòng vo, nói cảm xúc, hỏi chen ngang hoặc bị nhận giọng sai chính tả), rồi tự quyết định: trò chuyện, tư vấn, tra cứu thêm, hay thực hiện một việc bằng DẤU HIỆU. Người nói đã yêu cầu rõ một việc thì làm luôn, không hỏi xác nhận lại; chỉ khi thật sự mơ hồ mới hỏi lại đúng 1 câu ngắn.',
            '2. Đúng dữ liệu: mọi thông tin về người dùng, việc học, hệ thống và chính bạn chỉ lấy từ HỒ SƠ hoặc kết quả HÀM. HỒ SƠ chỉ là bản TÓM TẮT (ví dụ chỉ có vài bài gần nhất): hỏi về một bài, chủ đề, khoảng thời gian hay danh sách cụ thể thì tra cứu đầy đủ bằng HÀM, không suy ra từ phần tóm tắt. Thiếu thì GỌI HÀM phù hợp, đừng đoán; vẫn không có thì nói thật là chưa có thông tin. Con số viết bằng chữ số, không tự cộng trừ ra số mới.',
            '3. Đúng tên: tên chủ đề, bài, khối, trang... dùng đúng như dữ liệu, không tự đặt. Danh sách có đánh số thì người nói có thể gọi theo SỐ THỨ TỰ. Hàm báo không tìm thấy thì chọn tên đúng trong danh sách nó gợi ý. Được hỏi một danh sách thì liệt kê ĐỦ và ĐÚNG tên, mỗi mục một dòng "- ".',
            '4. Thời gian: hôm nay là ' . $weekdays[$now->dayOfWeek] . ' ' . $now->format('Y-m-d') . '. Tự quy đổi các mốc như "hôm qua", "tuần trước", "tháng này" ra ngày dạng YYYY-MM-DD khi gọi hàm.',
            '5. Kết quả hàm có "ghi_chu" thì làm theo ghi chú đó.',
            '6. An toàn: chỉ nói về dữ liệu của chính người đang nói (giáo viên được nói về học sinh của mình). Không hỏi, đọc hay tạo mật khẩu, OTP. Chủ đề không hợp trẻ em thì nhẹ nhàng đưa về việc học.',
            '7. Trình bày (câu trả lời vừa được đọc to vừa hiện trên màn hình): như đang nhắn/đối thoại với người thật, tự nhiên, ấm áp, khích lệ, 1 đến 3 câu ngắn; khi giải thích thì 2 đến 4 ý, mỗi ý một dòng "- ", in đậm **từ khóa**. Không viết đoạn dài, tiêu đề, bảng, biểu tượng, đường dẫn hay tên miền.',
            '8. Chỉ rủ làm câu hỏi luyện tập khi hợp ngữ cảnh, không rủ hai lượt liền nhau, không lặp lại lời chào.',
            '',
            'DẤU HIỆU (đặt ở dòng cuối, hệ thống tự làm, không giải thích dấu hiệu):',
            '- [[MO: mã]] hiện nút mở trang khi có ích; [[MO_NGAY: mã]] khi người nói nhờ mở hoặc chuyển sang trang đó (tự mở tab mới). Mã lấy trong mục TRANG hoặc từ kết quả hàm.',
            '- QUAN TRỌNG: khi người nói muốn LÀM câu hỏi, bài tập hay luyện của một khối/chủ đề (ví dụ "làm câu hỏi khối 3 chủ đề 2") thì KHÔNG mở trang: dùng [[LUYEN: chủ đề]] (nếu chưa có câu hỏi đang hiện), hoặc hỏi lại đúng 1 câu nếu chưa rõ chủ đề. Chỉ mở trang khi người nói rõ muốn ĐI TỚI trang đó.',
        ];
        if ($pending) {
            $lines[] = '- Người nói ĐANG làm câu hỏi bên dưới. Hiểu họ muốn gì rồi dùng đúng MỘT dấu hiệu nếu cần:';
            $lines[] = '  [[CHON: chữ cái]] (câu chọn nhiều thì [[CHON: A,C]]) khi họ đã CHỌN đáp án, dù diễn đạt kiểu gì. Khi đó chỉ viết một câu ngắn, KHÔNG nói đúng hay sai: hệ thống tự chấm theo đáp án thật.';
            $lines[] = '  [[DOC_LAI]] muốn nghe lại câu hỏi · [[DOI_CAU]] muốn bỏ qua hoặc đổi câu khác · [[DUNG]] muốn dừng làm câu hỏi.';
            $lines[] = '  Những câu khác (hỏi gợi ý, hỏi nghĩa, nói chuyện) thì trả lời bình thường, không gắn dấu hiệu, KHÔNG đoán hay lộ đáp án.';
        } else {
            $lines[] = '- [[LUYEN: chủ đề | nội dung]] khi người nói muốn làm, ôn hoặc luyện câu hỏi NGAY (tự yêu cầu, hoặc vừa đồng ý lời rủ). Gắn dấu hiệu là câu hỏi hiện ngay trên màn hình, nên lời dẫn chỉ cần một câu ngắn, không hỏi "sẵn sàng chưa". Phần chủ đề: tên đúng trong dữ liệu, hoặc "bat ky", hoặc "cau sai" (ôn câu từng làm sai). '
                . 'Phần sau dấu | là tùy chọn: các cách gọi của nội dung cụ thể muốn hỏi, cách nhau dấu phẩy. '
                . 'Chỉ đang RỦ thì kết thúc bằng câu hỏi và KHÔNG gắn. Người nói đang hỏi kết quả, thống kê thì KHÔNG gắn. Không tự bịa câu hỏi hay đáp án: câu hỏi do hệ thống lấy từ ngân hàng đề.';
            $lines[] = '- [[DUNG]] khi người nói muốn thôi làm câu hỏi.';
        }
        $lines[] = '';

        $activeQuestion = $pending ? $this->quiz->pendingContext($user) : null;
        if ($activeQuestion !== null) {
            $lines[] = '=== CÂU HỎI EM ĐANG LÀM (bạn KHÔNG biết đáp án đúng) ===';
            $lines[] = $activeQuestion;
            $lines[] = '';
        }

        $profileJson = json_encode($profile, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $lines[] = '=== HỒ SƠ NGƯỜI ĐANG NÓI ===';
        $lines[] = $profileJson;
        $lines[] = '';
        $lines[] = '=== TRANG (mã: tên trang) ===';
        $lines[] = $pages->implode("\n");

        preg_match_all('/\d+/', $profileJson . ' ' . $activeQuestion, $m);

        return [implode("\n", $lines), [
            'numbers' => array_values(array_unique(array_map('intval', $m[0]))),
            'topics' => $this->quiz->topicNames(),
        ]];
    }

    /** Sao thưởng, giờ chơi game và hạn dùng của chính người đang nói */
    private function rewardsOf(User $user): array
    {
        $tz = config('learning.display_timezone', 'Asia/Ho_Chi_Minh');
        // Học sinh do giáo viên quản lý dùng gói và hạn của giáo viên (giống trang hồ sơ)
        $package = $user->packageSummary();

        return array_filter([
            'so_sao_thuong_hien_co' => (int) ($user->reward_stars ?? 0),
            'gio_choi_game_con_lai_phut' => (int) floor(((int) ($user->game_time_seconds ?? 0)) / 60),
            'goi_dang_dung' => $package['package'],
            'goi_cua_giao_vien' => $package['teacher'],
            'han_hoc_tap_den_ngay' => $package['expires_at']?->format('d/m/Y') ?? 'không giới hạn',
            'han_tro_ly_ai_den_ngay' => $user->ai_assistant_until?->setTimezone($tz)->format('d/m/Y'),
        ], fn ($v) => $v !== null);
    }

    // =========================================================================
    // 🔧 DẤU HIỆU AI GẮN VÀO CÂU TRẢ LỜI
    // =========================================================================

    /**
     * Tách dấu hiệu mở trang. [[MO: mã]] = hiện nút; [[MO_NGAY: mã]] = tự mở tab mới (AI hiểu em nhờ mở).
     * Chỉ nhận mã nằm trong danh sách do máy chủ đưa ra (hoặc bài em được phép làm); đường dẫn luôn do máy chủ tạo.
     *
     * @return array{0: string, 1: ?array{label: string, url: string, auto: bool}}
     */
    public function extractAction(string $raw, User $user): array
    {
        $action = null;
        if (preg_match('/\[\[\s*(MO_NGAY|MO)\s*:\s*([a-z0-9_]+)\s*\]\]/iu', $raw, $m)) {
            $catalog = $this->assistant->actionCatalog($user);
            $code = strtolower($m[2]);
            // Bài AI tìm qua hàm danh_sach_bai có thể nằm ngoài danh sách ngắn: chỉ nhận nếu em được phép làm bài đó
            if (! isset($catalog[$code]) && preg_match('/^(lam|xem)_(\d+)$/', $code, $cm)
                && ($test = $this->assistant->accessibleTests($user)->whereKey((int) $cm[2])->first())) {
                $catalog[$code] = $cm[1] === 'lam'
                    ? ['label' => "Làm bài «{$test->name}»", 'url' => route('tests.launch', $test)]
                    : ['label' => "Xem kết quả «{$test->name}»", 'url' => route('tests.show', $test)];
            }
            if (isset($catalog[$code])) {
                $action = ['label' => $catalog[$code]['label'], 'url' => $catalog[$code]['url'], 'auto' => strtoupper($m[1]) === 'MO_NGAY'];
            }
        }

        return [trim(preg_replace('/\[\[\s*MO(_NGAY)?\s*:.*?\]\]/isu', '', $raw)), $action];
    }

    /**
     * Tách dấu hiệu [[LUYEN: ...]] mà AI thêm khi người nói muốn làm câu hỏi.
     *
     * @return array{0: string, 1: ?string}  [nội dung đã bỏ dấu hiệu, gợi ý chủ đề hoặc null]
     */
    public function extractQuizMarker(string $raw): array
    {
        $hint = null;
        if (preg_match('/\[\[\s*LUYEN\s*:\s*(.*?)\s*\]\]/iu', $raw, $m)) {
            $hint = trim($m[1]);
        }

        return [trim(preg_replace('/\[\[\s*LUYEN\s*:.*?\]\]/isu', '', $raw)), $hint];
    }

    /**
     * Tách dấu hiệu điều khiển câu hỏi: [[CHON: A,C]] (chỉ nhận khi đang có câu chờ trả lời), [[DOC_LAI]], [[DOI_CAU]], [[DUNG]].
     *
     * @return array{0: string, 1: ?array<int, string>, 2: ?string}  [nội dung đã bỏ dấu hiệu, các chữ cái em chọn, lệnh điều khiển]
     */
    public function extractQuizControls(string $raw, bool $pending): array
    {
        $choice = null;
        if ($pending && preg_match('/\[\[\s*CHON\s*:\s*([A-Fa-f ,;và]+?)\s*\]\]/u', $raw, $m)) {
            preg_match_all('/[A-Fa-f]/', $m[1], $keys);
            $choice = array_values(array_unique(array_map('strtoupper', $keys[0]))) ?: null;
        }

        $control = null;
        foreach (self::CONTROLS as $marker => $name) {
            if (preg_match('/\[\[\s*' . $marker . '\s*\]\]/u', $raw)) {
                $control = $name;
                break;
            }
        }
        // Không có câu đang chờ thì "đọc lại / đổi câu" không có ý nghĩa
        if (! $pending && $control !== 'dung') {
            $control = null;
        }

        $clean = preg_replace('/\[\[\s*(CHON\s*:.*?|DOC_LAI|DOI_CAU|DUNG)\s*\]\]/isu', '', $raw);

        return [trim($clean), $choice, $choice ? null : $control];
    }

    /**
     * Làm sạch chữ hiện trên màn hình nhưng GIỮ cách trình bày đơn giản: đoạn văn, dòng gạch đầu dòng "- ", in đậm **...**.
     * Bỏ mọi thứ có thể gây hại hoặc rối mắt: thẻ HTML, mã, đường dẫn, dấu hiệu điều khiển, tiêu đề #.
     */
    public function sanitizeDisplay(string $text): string
    {
        $text = preg_replace('/\[\[.*?\]\]/su', '', $text);
        $text = preg_replace('/```.*?```/su', '', $text);
        $text = preg_replace('/\[([^\]]+)\]\([^)]+\)/u', '$1', $text);
        $text = preg_replace('~https?://\S+~u', '', $text);
        $text = preg_replace('~\b[\w-]+(\.[\w-]+)*\.(app|com|vn|net|org|io)(/\S*)?~iu', '', $text);
        $text = strip_tags($text);
        $text = preg_replace('/^[ \t]*[•*+–—][ \t]+/mu', '- ', $text);
        $text = preg_replace('/^#{1,6}\s*/mu', '', $text);
        $text = preg_replace('/`/u', '', $text);
        $text = preg_replace("/[ \t]+\n/u", "\n", $text);
        $text = preg_replace("/\n{3,}/u", "\n\n", $text);

        return trim($text);
    }

    /**
     * Làm sạch câu trả lời để trình duyệt đọc to tự nhiên: bỏ định dạng markdown, biểu tượng cảm xúc, đường dẫn.
     */
    public function cleanForSpeech(string $text): string
    {
        $text = preg_replace('/\[\[.*?\]\]/su', ' ', $text);
        $text = preg_replace('/```.*?```/su', ' ', $text);
        $text = preg_replace('/\[([^\]]+)\]\([^)]+\)/u', '$1', $text);
        $text = preg_replace('~https?://\S+~u', ' ', $text);
        // Địa chỉ không có http (mos.app/bang-gia) và đường dẫn dạng /bang-gia: không đọc to
        $text = preg_replace('~\b[\w-]+(\.[\w-]+)*\.(app|com|vn|net|org|io)(/\S*)?~iu', ' ', $text);
        $text = preg_replace('~(^|\s)/[\w\-/]{3,}~u', ' ', $text);
        $text = preg_replace('/[*_#`>|~]+/u', '', $text);
        $text = preg_replace('/^\s*[-•]\s+/mu', '', $text);
        $text = preg_replace('/[\x{1F000}-\x{1FFFF}\x{2600}-\x{27BF}\x{FE0F}\x{200D}]/u', '', $text);
        // Xuống dòng sau dấu kết câu thì chỉ là khoảng trắng; xuống dòng giữa câu thì thành dấu chấm để đọc ngắt nghỉ
        $text = preg_replace('/([.!?…])\s*\n+\s*/u', '$1 ', $text);
        $text = preg_replace('/\s*\n+\s*/u', '. ', $text);
        $text = preg_replace('/\.\s*\.+/u', '.', $text);
        $text = preg_replace('/\s{2,}/u', ' ', $text);

        return trim($text);
    }

    /**
     * Chép lời nói trong đoạn âm thanh WAV thành chữ tiếng Việt. Trả về null nếu không nghe được.
     */
    public function transcribe(string $wavBinary): ?string
    {
        $baseUrl = rtrim(trim((string) config('services.question_ai.base_url')), '/');
        $model = trim((string) config('services.question_ai.model'));
        if ($baseUrl === '' || $model === '') {
            return null;
        }

        try {
            $request = Http::acceptJson()
                ->connectTimeout(max(1, (int) config('services.question_ai.connect_timeout', 10)))
                ->timeout(25)
                ->withoutRedirecting();
            $apiKey = trim((string) config('services.question_ai.api_key'));
            if ($apiKey !== '') {
                $request = $request->withToken($apiKey);
            }

            $response = $request->post($baseUrl . '/chat/completions', [
                'model' => $model,
                'stream' => false,
                'temperature' => 0,
                'max_tokens' => 300,
                'messages' => [[
                    'role' => 'user',
                    'content' => [
                        ['type' => 'text', 'text' => 'Chép lại CHÍNH XÁC lời nói trong đoạn âm thanh bằng tiếng Việt. Chỉ trả về nội dung lời nói, không giải thích. Nếu không có lời nói rõ ràng thì trả về đúng một chữ: KHONG'],
                        ['type' => 'input_audio', 'input_audio' => ['data' => base64_encode($wavBinary), 'format' => 'wav']],
                    ],
                ]],
            ]);

            if (! $response->successful()) {
                Log::warning("VoiceAssistant: 9Router nghe giọng nói trả về HTTP {$response->status()}.");

                return null;
            }

            $text = $this->readCompletionText($response->body());
            if ($text === null || mb_strtoupper(trim($text, " .!\n")) === 'KHONG') {
                return null;
            }

            return $text;
        } catch (\Throwable $e) {
            Log::warning('VoiceAssistant: không nghe được giọng nói (' . $e::class . ').');

            return null;
        }
    }

    /** 9Router có nơi trả JSON thường, có nơi trả luồng SSE dù không yêu cầu stream */
    private function readCompletionText(string $body): ?string
    {
        $json = json_decode($body, true);
        if (is_array($json)) {
            $text = trim((string) data_get($json, 'choices.0.message.content', ''));

            return $text !== '' ? $text : null;
        }

        $text = '';
        foreach (preg_split('/\r?\n/', $body) as $line) {
            if (! str_starts_with($line, 'data:')) {
                continue;
            }
            $payload = trim(substr($line, 5));
            if ($payload === '' || $payload === '[DONE]') {
                continue;
            }
            $text .= (string) data_get(json_decode($payload, true), 'choices.0.delta.content', '');
        }
        $text = trim($text);

        return $text !== '' ? $text : null;
    }
}
