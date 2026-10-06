<?php

namespace App\Services;

use App\Models\Level;
use App\Models\Question;
use App\Models\StudentMistake;
use App\Models\Topic;
use App\Models\User;
use App\Support\LeakedReasoningFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Làm câu hỏi ngay trong cuộc trò chuyện bằng giọng nói.
 *
 * - Câu hỏi lấy thật từ ngân hàng đề, đúng khối em được học. Hỗ trợ: chọn đáp án (kể cả có hình), chọn nhiều đáp án,
 *   phân loại khái niệm (chọn trong ô) và ghép nối. Dạng chọn đáp án làm được bằng cách nói; các dạng còn lại em làm trên màn hình.
 * - Đáp án đúng KHÔNG gửi ra trình duyệt; máy chủ nhớ câu đang hỏi và tự chấm theo cơ sở dữ liệu.
 * - Hiểu cách em nói đáp án ("đáp án B", "số hai", "A và C", hoặc nói nội dung đáp án); chưa chắc thì nhờ AI hiểu giúp.
 * - Chỉ luyện tập: không cộng sao, không ghi vào kết quả hay sổ tay câu sai.
 */
class VoiceQuizService
{
    private const CHOICE_TYPES = ['MultipleChoice', 'MultipleResponse'];
    private const SCREEN_TYPES = ['MultipleChoiceText', 'Matching'];
    private const MAX_OPTIONS = 6;
    private const MAX_ITEMS = 8;
    private const PENDING_MINUTES = 30;
    private const SEEN_MAX = 40;

    public function __construct(private AiChatClient $client, private QuestionGrader $grader)
    {
    }

    // =========================================================================
    // 📚 DANH MỤC & SỔ TAY CÂU SAI (cho AI biết để trò chuyện thông minh)
    // =========================================================================

    /**
     * Khối và chủ đề người dùng được học, kèm số câu hỏi có thể làm bằng giọng nói.
     *
     * @return array<int, array{khoi: string, chu_de: array<int, array{ten: string, so_cau: int}>}>
     */
    public function catalog(User $user): array
    {
        $levels = $this->accessibleLevels($user);
        $topics = $this->publishedTopics()->whereIn('level_id', $levels->pluck('id'))->get(['id', 'level_id', 'name']);

        return $levels->map(function (Level $level) use ($topics, $user) {
            return [
                'khoi' => $level->name,
                'chu_de' => $topics->where('level_id', $level->id)->map(fn (Topic $t) => [
                    'ten' => $t->name,
                    'so_cau' => $this->base($user, collect([$level->id]))
                        ->whereHas('practiceTest', fn ($q) => $q->where('topic_id', $t->id))
                        ->count(),
                ])->values()->all(),
            ];
        })->values()->all();
    }

    /**
     * Tên các chủ đề thật người dùng được học (dùng để soát câu trả lời của AI).
     *
     * @return array<int, string>
     */
    public function topicNames(): array
    {
        // Đối chiếu với mọi chủ đề có thật trong hệ thống (kể cả chưa có bài) để không bỏ nhầm câu đúng
        return Topic::query()->pluck('name')->unique()->values()->all();
    }

    /** Tên chủ đề thật khớp với lời em nói (ví dụ "ra câu hỏi về công dân số" → "Công dân số"), hoặc null */
    public function topicNameFromHint(User $user, string $hint): ?string
    {
        $ids = $this->matchTopics($this->accessibleLevels($user)->pluck('id'), $this->norm($hint));

        return $ids->isEmpty() ? null : Topic::whereIn('id', $ids)->value('name');
    }

    /** Tên các chủ đề thuộc khối người dùng được học */
    public function topicNamesFor(User $user): array
    {
        return Topic::whereIn('level_id', $this->accessibleLevels($user)->pluck('id'))->orderBy('position')->pluck('name')->unique()->values()->all();
    }

    /** Mã các chủ đề (thuộc khối người dùng được học) khớp với tên AI/em nói, ví dụ "công dân số" */
    public function topicIdsFor(User $user, string $name): array
    {
        return $this->matchTopics($this->accessibleLevels($user)->pluck('id'), $this->norm($name))->all();
    }

    /**
     * Câu sai chưa khắc phục của học sinh, có thể lọc theo chủ đề.
     *
     * @param  array<int, int>|null  $topicIds
     * @return array<int, array{chu_de: ?string, bai: ?string, cau_hoi: string, so_lan_sai: int, so_lan_dung_lai: int}>
     */
    public function mistakesIn(User $user, ?array $topicIds, int $limit): array
    {
        return StudentMistake::query()
            ->where('user_id', $user->id)
            ->where('status', 'unresolved')
            ->when($topicIds !== null, fn ($q) => $q->whereHas('question.practiceTest', fn ($t) => $t->whereIn('topic_id', $topicIds)))
            ->with('question.practiceTest.topic')
            ->orderByDesc('wrong_count')
            ->limit($limit)
            ->get()
            ->map(fn ($m) => [
                'chu_de' => $m->question?->practiceTest?->topic?->name,
                'bai' => $m->question?->practiceTest?->name,
                'cau_hoi' => Str::limit($this->plain((string) $m->question?->title), 140),
                'so_lan_sai' => (int) $m->wrong_count,
                'so_lan_dung_lai' => (int) $m->correct_count,
            ])->values()->all();
    }

    /** Chủ đề có ít nhất một bài luyện đã xuất bản (bỏ chủ đề thử, chủ đề rỗng) theo đúng thứ tự hiển thị */
    private function publishedTopics()
    {
        return Topic::query()
            ->whereHas('tests', fn ($q) => $q->where('is_published', true)->where('is_mock', false))
            ->orderBy('position')->orderBy('id');
    }

    /**
     * Tóm tắt câu sai chưa khắc phục của học sinh: theo chủ đề và những câu sai nhiều nhất.
     *
     * @return array{tong_so_cau_chua_khac_phuc: int, theo_chu_de: array<int, array{chu_de: string, so_cau: int}>, sai_nhieu_nhat: array<int, array{chu_de: ?string, cau_hoi: string, so_lan_sai: int}>}
     */
    public function mistakeSummary(User $user): array
    {
        if (! $user->isStudent()) {
            return ['tong_so_cau_chua_khac_phuc' => 0, 'theo_chu_de' => [], 'sai_nhieu_nhat' => []];
        }

        $rows = StudentMistake::query()
            ->where('user_id', $user->id)
            ->where('status', 'unresolved')
            ->with('question.practiceTest.topic')
            ->orderByDesc('wrong_count')
            ->limit(60)
            ->get();

        return [
            'tong_so_cau_chua_khac_phuc' => StudentMistake::where('user_id', $user->id)->where('status', 'unresolved')->count(),
            'theo_chu_de' => $rows->groupBy(fn ($m) => $m->question?->practiceTest?->topic?->name ?? 'Khác')
                ->map(fn ($g, $name) => ['chu_de' => (string) $name, 'so_cau' => $g->count()])
                ->sortByDesc('so_cau')->values()->take(8)->all(),
            'sai_nhieu_nhat' => $rows->take(6)->map(fn ($m) => [
                'chu_de' => $m->question?->practiceTest?->topic?->name,
                'cau_hoi' => Str::limit($this->plain((string) $m->question?->title), 110),
                'so_lan_sai' => (int) $m->wrong_count,
            ])->values()->all(),
        ];
    }

    // =========================================================================
    // ❓ RA CÂU HỎI
    // =========================================================================

    /**
     * Chọn một câu hỏi thật phù hợp và nhớ lại để chấm. Trả về null nếu không có câu nào phù hợp.
     *
     * @param  string  $hint  "Tên chủ đề", "cau sai" (ôn câu từng sai), rỗng/"bat ky", kèm nội dung cần tìm sau dấu "|":
     *                       ví dụ "Căn bản về công nghệ | laptop, máy tính xách tay" (chỉ lấy câu có nhắc tới laptop)
     * @return array{question: array<string, mixed>, spoken: string, source: string, hint: string, matched: ?bool, keywords: string}|null
     */
    public function start(User $user, string $hint): ?array
    {
        $levelIds = $this->accessibleLevels($user)->pluck('id');
        if ($levelIds->isEmpty()) {
            return null;
        }

        // "chủ đề | nội dung cần tìm"
        [$topicPart, $keywordPart] = array_pad(array_map('trim', explode('|', $hint, 2)), 2, '');
        $normHint = $this->norm($topicPart);
        $keywords = array_values(array_filter(array_map(fn ($k) => $this->norm($k), preg_split('/[,;]/', $keywordPart))));
        $fromMistakes = $user->isStudent() && (bool) preg_match('/\b(cau sai|sai|on lai|on tap cau|phuc thu)\b/', $normHint);

        $query = $this->base($user, $levelIds);
        $source = 'ngan_hang';

        if ($fromMistakes) {
            $ids = StudentMistake::where('user_id', $user->id)->where('status', 'unresolved')->pluck('question_id');
            $query->whereIn('id', $ids);
            $source = 'cau_sai';
        } else {
            $topicIds = $this->matchTopics($levelIds, $normHint);
            if ($topicIds->isNotEmpty()) {
                $query->whereHas('practiceTest', fn ($q) => $q->whereIn('topic_id', $topicIds));
            } elseif ($normHint !== '' && ! preg_match('/^(bat ky|ngau nhien|any|tat ca)$/', $normHint)) {
                // Không phải tên chủ đề (vd "máy tính xách tay"): coi là nội dung cần tìm
                $keywords[] = $normHint;
            }
        }

        // Chỉ lấy câu có nhắc tới nội dung em muốn (trong đề hoặc đáp án); không có thì ra câu cùng phạm vi và báo cho em biết
        $matched = null;
        if ($keywords !== []) {
            $hits = (clone $query)->with('options')->limit(500)->get()
                ->filter(fn (Question $q) => $this->mentionsAny($q, $keywords))
                ->pluck('id');
            $matched = $hits->isNotEmpty();
            if ($matched) {
                $query->whereIn('id', $hits);
            }
        }

        $seen = Cache::get($this->seenKey($user), []);
        $candidates = (clone $query)->whereNotIn('id', $seen)->inRandomOrder()->limit(40)->with(['options', 'assets', 'practiceTest.topic.level'])->get();
        if ($candidates->isEmpty()) {
            // Đã hỏi hết các câu phù hợp thì cho hỏi lại từ đầu
            $candidates = $query->inRandomOrder()->limit(40)->with(['options', 'assets', 'practiceTest.topic.level'])->get();
            $seen = [];
        }

        foreach ($candidates as $question) {
            $described = $this->describe($question);
            if ($described === null) {
                continue;
            }

            Cache::put($this->pendingKey($user), ['qid' => $question->id, 'tries' => 0], now()->addMinutes(self::PENDING_MINUTES));
            Cache::put($this->seenKey($user), array_slice(array_merge($seen, [$question->id]), -self::SEEN_MAX), now()->addHours(6));

            $topic = $question->practiceTest?->topic;
            $described['question']['topic'] = $topic?->name;
            $described['question']['level'] = $topic?->level?->name;

            return $described + [
                'source' => $source,
                'hint' => $hint,
                'matched' => $matched,
                'keywords' => trim($keywordPart) !== '' ? trim($keywordPart) : ($matched === null ? '' : $topicPart),
            ];
        }

        return null;
    }

    /** Câu hỏi có nhắc tới một trong các nội dung cần tìm không (so không dấu, theo nguyên cụm từ) */
    private function mentionsAny(Question $question, array $keywords): bool
    {
        $text = ' ' . $this->norm($this->plain((string) $question->title) . ' ' . $question->options->map(fn ($o) => $this->plain((string) $o->content))->implode(' ')) . ' ';
        foreach ($keywords as $keyword) {
            if ($keyword !== '' && str_contains($text, ' ' . $keyword . ' ')) {
                return true;
            }
        }

        return false;
    }

    public function hasPending(User $user): bool
    {
        return Cache::has($this->pendingKey($user));
    }

    public function clear(User $user): void
    {
        Cache::forget($this->pendingKey($user));
    }

    /**
     * Mô tả câu hỏi em đang làm (KHÔNG kèm đáp án đúng) để AI trò chuyện quanh câu hỏi: gợi ý, giải thích khái niệm.
     */
    public function pendingContext(User $user): ?string
    {
        $state = Cache::get($this->pendingKey($user));
        $question = $state ? Question::with(['options', 'assets'])->find($state['qid']) : null;
        $described = $question ? $this->describe($question) : null;
        if ($described === null) {
            return null;
        }

        $q = $described['question'];
        $lines = [
            'Dạng câu: ' . match (true) {
                in_array($question->type, self::SCREEN_TYPES, true) => 'em làm trên màn hình (chọn trong từng ô / ghép nối) rồi bấm nộp, KHÔNG trả lời bằng lời',
                $question->type === 'MultipleResponse' => 'chọn NHIỀU đáp án đúng',
                default => 'chọn MỘT đáp án',
            },
            'Câu hỏi: ' . $q['text'],
        ];
        foreach ($q['options'] ?? [] as $o) {
            $lines[] = "- Đáp án {$o['key']}: " . ($o['text'] !== '' ? $o['text'] : '(hình ảnh)');
        }
        foreach ($q['items'] ?? [] as $it) {
            $lines[] = '- ' . $it['text'] . ' (chọn: ' . implode(' / ', $it['choices']) . ')';
        }
        if (! empty($q['left'])) {
            $lines[] = 'Các vế bên trái: ' . implode('; ', array_column($q['left'], 'text'));
            $lines[] = 'Các vế bên phải: ' . implode('; ', array_column($q['right'], 'text'));
        }
        if (! empty($q['images'])) {
            $lines[] = '(Câu hỏi có hình ảnh trên màn hình mà bạn không nhìn thấy.)';
        }

        return implode("\n", $lines);
    }

    // =========================================================================
    // ✅ CHẤM CÂU TRẢ LỜI
    // =========================================================================

    /**
     * Chấm câu trả lời theo đáp án trong database.
     * - Dạng chọn đáp án: nhận chữ cái em chọn ($keys: em bấm nút, hoặc AI trò chuyện đã hiểu em chọn gì);
     *   chỉ có lời nói ($utterance) thì nhờ AI hiểu em chọn đáp án nào.
     * - Dạng chọn trong ô / ghép nối: nhận kết quả em làm trên màn hình ($answers).
     *
     * @param  array<int|string, mixed>|null  $answers  Kết quả làm trên màn hình (dạng phân loại/ghép nối)
     * @param  array<int, string>|null  $keys  Các chữ cái em chọn (A, B, C...)
     * @return array<string, mixed>
     */
    public function answer(User $user, string $utterance, ?array $answers = null, ?array $keys = null): array
    {
        $state = Cache::get($this->pendingKey($user));
        $question = $state ? Question::with(['options', 'assets'])->find($state['qid']) : null;
        if (! $question) {
            return ['status' => 'no_question', 'spoken' => 'Mình chưa có câu hỏi nào đang chờ. Em muốn cô ra câu hỏi không?'];
        }

        if (in_array($question->type, self::SCREEN_TYPES, true)) {
            return $this->answerOnScreen($user, $question, $answers);
        }

        $options = $this->optionsOf($question);
        if ($options === null) {
            $this->clear($user);

            return ['status' => 'no_question', 'spoken' => 'Câu hỏi này đang gặp trục trặc. Em muốn làm câu khác không?'];
        }

        $multi = $question->type === 'MultipleResponse';
        $valid = array_column($options, 'key');
        $picked = $keys !== null
            ? array_values(array_unique(array_filter(array_map(fn ($k) => strtoupper(trim((string) $k)), $keys), fn ($k) => in_array($k, $valid, true))))
            : $this->askAiToMap($utterance, $options, $question);

        if ($picked === null || $picked === [] || (! $multi && count($picked) > 1)) {
            $tries = (int) ($state['tries'] ?? 0) + 1;
            Cache::put($this->pendingKey($user), ['qid' => $question->id, 'tries' => $tries], now()->addMinutes(self::PENDING_MINUTES));

            $keys = implode(', ', array_column($options, 'key'));
            $spoken = $picked !== null && count($picked) > 1 && ! $multi
                ? 'Câu này chỉ chọn một đáp án thôi em nhé. Em chọn đáp án nào?'
                : "Cô chưa nghe rõ em chọn đáp án nào. Em nói lại giúp cô: {$keys}?";

            return ['status' => 'unclear', 'spoken' => $spoken, 'tries' => $tries];
        }

        $correctKeys = array_values(array_map(fn ($o) => $o['key'], array_filter($options, fn ($o) => $o['correct'])));
        sort($picked);
        $isCorrect = $picked === $correctKeys;

        $this->clear($user);
        $describe = fn (array $keys) => implode('; ', array_map(
            fn ($k) => $k . ': ' . ($this->optionText($options, $k) ?: 'hình ' . $k),
            $keys
        ));
        $reason = $this->explainReason($question, $describe($correctKeys), $describe($picked), $isCorrect);
        $displayLines = array_map(fn ($k) => "- **{$k}.** " . ($this->optionText($options, $k) ?: 'hình ' . $k), $correctKeys);
        [$feedback, $display] = $this->composeFeedback($isCorrect, 'Đáp án đúng là ' . $describe($correctKeys) . '.', $displayLines, $reason);

        return [
            'status' => 'graded',
            'type' => $question->type,
            'correct' => $isCorrect,
            'correct_keys' => $correctKeys,
            'picked_keys' => $picked,
            'feedback' => $feedback,
            'feedback_display' => $display,
            'spoken' => $feedback . ' Em làm tiếp một câu nữa nhé?',
        ];
    }

    /** Câu phân loại / ghép nối: chấm theo kết quả em làm trên màn hình */
    private function answerOnScreen(User $user, Question $question, ?array $answers): array
    {
        $described = $this->describe($question);
        if ($described === null) {
            $this->clear($user);

            return ['status' => 'no_question', 'spoken' => 'Câu hỏi này đang gặp trục trặc. Em muốn làm câu khác không?'];
        }

        $isMatching = $question->type === 'Matching';
        $options = $question->options->sortBy('position')->values();
        $n = $options->count();

        // Kết quả em làm phải đủ từng dòng và hợp lệ, nếu không thì nhắc em làm tiếp
        $valid = is_array($answers) && count($answers) === $n;
        if ($valid) {
            foreach ($options as $i => $option) {
                $v = $answers[$i] ?? null;
                $max = $isMatching ? $n - 1 : count($option->metadata['available_options'] ?? []) - 1;
                if (! is_numeric($v) || (int) $v < 0 || (int) $v > $max) {
                    $valid = false;
                    break;
                }
            }
        }
        if (! $valid) {
            return ['status' => 'unclear', 'spoken' => 'Câu này em làm trực tiếp trên màn hình nhé. Em chọn đủ các dòng rồi bấm Trả lời.'];
        }

        $answers = array_map('intval', array_values($answers));
        $isCorrect = $this->grader->isCorrect($question, $answers);

        $wrong = [];
        $correctLines = [];
        $pickedLines = [];
        $wrongDisplay = [];
        $wrongSpoken = [];
        foreach ($options as $i => $option) {
            if ($isMatching) {
                [$left, $right] = $this->pairOf($option);
                $rightOf = fn (int $id) => $this->pairOf($options[$id] ?? $option)[1];
                $correctLines[] = "{$left} nối với {$right}";
                $pickedLines[] = "{$left} nối với " . $rightOf($answers[$i]);
                if ($answers[$i] !== $i) {
                    $wrong[] = $i;
                    $wrongDisplay[] = "- **{$left}** → {$right}";
                    $wrongSpoken[] = "{$left} nối với {$right}";
                }
            } else {
                $choices = $option->metadata['available_options'] ?? [];
                $correctIdx = (int) ($option->metadata['correct_index'] ?? -1);
                $text = $this->plain((string) $option->content);
                $correctLines[] = "{$text}: " . ($choices[$correctIdx] ?? '');
                $pickedLines[] = "{$text}: " . ($choices[$answers[$i]] ?? '');
                if ($answers[$i] !== $correctIdx) {
                    $wrong[] = $i;
                    $wrongDisplay[] = "- **{$text}** → " . ($choices[$correctIdx] ?? '');
                    $wrongSpoken[] = "{$text} là " . ($choices[$correctIdx] ?? '');
                }
            }
        }

        $this->clear($user);
        $correctText = implode('; ', $correctLines);
        $reason = $this->explainReason($question, $correctText, implode('; ', $pickedLines), $isCorrect);
        // Đọc to tối đa 3 chỗ chưa đúng cho ngắn gọn; đầy đủ thì hiện trên màn hình
        $spokenWrong = array_slice($wrongSpoken, 0, 3);
        $spokenAnswer = 'Những chỗ chưa đúng là: ' . implode('; ', $spokenWrong) . (count($wrongSpoken) > 3 ? '; và một vài dòng khác' : '') . '.';
        [$feedback, $display] = $this->composeFeedback($isCorrect, $spokenAnswer, $wrongDisplay, $reason, 'Chỗ chưa đúng:');

        return [
            'status' => 'graded',
            'type' => $question->type,
            'correct' => $isCorrect,
            'wrong_items' => $wrong,
            'solution' => $isMatching
                ? array_map(fn ($i) => $i, range(0, $n - 1))
                : $options->map(fn ($o) => (int) ($o->metadata['correct_index'] ?? -1))->values()->all(),
            'feedback' => $feedback,
            'feedback_display' => $display,
            'spoken' => $feedback . ' Em làm tiếp một câu nữa nhé?',
        ];
    }

    /**
     * Ghép lời nhận xét: kết quả + đáp án đúng (lấy thẳng từ database) + lý do do AI giải thích.
     * Trả về [chữ để đọc to, chữ hiện trên màn hình có gạch đầu dòng và in đậm].
     *
     * @param  array<int, string>  $displayLines  Các dòng "- ..." liệt kê đáp án/chỗ chưa đúng
     * @return array{0: string, 1: string}
     */
    private function composeFeedback(bool $isCorrect, string $spokenAnswer, array $displayLines, string $reason, string $heading = 'Đáp án đúng:'): array
    {
        $spoken = $isCorrect ? 'Chính xác rồi em!' : 'Chưa đúng rồi em. ' . $spokenAnswer;
        $display = $isCorrect ? '**✅ Chính xác!**' : "**❌ Chưa đúng.**\n**{$heading}**\n" . implode("\n", $displayLines);

        if ($reason !== '') {
            $spoken .= ' ' . $reason;
            $display .= "\n\n**Vì sao:** " . $reason;
        } elseif ($isCorrect) {
            $spoken .= ' Giỏi lắm!';
            $display .= ' Giỏi lắm!';
        } else {
            $spoken .= ' Lần sau mình cố gắng hơn nhé!';
        }

        return [$spoken, $display];
    }

    /**
     * Nhờ AI hiểu em chọn đáp án nào (em nói chữ cái, số thứ tự, nội dung đáp án hay cách diễn đạt khác đều được).
     *
     * @return array<int, string>|null  Các chữ cái đã chọn; [] nếu em chưa nêu rõ; null nếu AI không phản hồi
     */
    private function askAiToMap(string $utterance, array $options, Question $question): ?array
    {
        $list = implode("\n", array_map(fn ($o) => "{$o['key']}. " . ($o['text'] !== '' ? $o['text'] : '(hình ảnh)'), $options));
        $system = 'Bạn giúp xác định học sinh chọn đáp án nào. Chỉ trả về JSON dạng {"chon":["A"]}. '
            . 'Nếu học sinh không nêu rõ đáp án (chỉ hỏi lại, nói linh tinh, hoặc không chắc) thì trả về {"chon":[]}. Không giải thích.';
        $user = "Câu hỏi: " . $this->plain((string) $question->title) . "\nCác đáp án:\n{$list}\nHọc sinh nói: \"{$utterance}\"";

        $raw = $this->client->complete($system, [['role' => 'user', 'text' => $user]], 60);
        if ($raw === null || ! preg_match('/\{.*\}/s', $raw, $m)) {
            return null;
        }

        $chon = json_decode($m[0], true)['chon'] ?? null;
        if (! is_array($chon)) {
            return null;
        }

        $valid = array_column($options, 'key');

        return array_values(array_unique(array_filter(array_map(fn ($k) => strtoupper(trim((string) $k)), $chon), fn ($k) => in_array($k, $valid, true))));
    }

    /**
     * Lý do ngắn gọn vì sao đáp án đúng là như vậy (1-2 câu). Trả về chuỗi rỗng nếu AI không phản hồi.
     * Đáp án đúng/sai do máy chủ tự chấm và tự hiện từ database; AI không được chép lại đáp án để tránh nói sai.
     */
    private function explainReason(Question $question, string $correctText, string $pickedText, bool $isCorrect): string
    {
        $system = 'Bạn là cô giáo trợ lý AI thân thiện, vừa chấm một câu hỏi cho học sinh tiểu học. '
            . 'Hãy giải thích NGẮN GỌN VÌ SAO đáp án đúng là như vậy, tối đa 2 câu ngắn, dễ hiểu, có thể khích lệ nhẹ. '
            . 'TUYỆT ĐỐI không nhắc lại danh sách đáp án, không nói em đúng hay sai, không hỏi thêm câu nào. '
            . 'Xưng "cô", gọi "em". Không dùng ký hiệu, markdown, biểu tượng. Chỉ dựa vào câu hỏi và đáp án đúng đã cho, không bịa thêm kiến thức khác. '
            . 'Chỉ viết lời giải thích cuối cùng bằng tiếng Việt, đặt trong cặp thẻ <noi> và </noi>, không viết suy nghĩ hay tiếng Anh ở ngoài thẻ.';
        $user = 'Câu hỏi: ' . $this->plain((string) $question->title)
            . "\nĐáp án đúng: " . $correctText
            . "\nEm chọn: " . $pickedText
            . "\nKết quả: " . ($isCorrect ? 'ĐÚNG' : 'CHƯA ĐÚNG');

        $raw = $this->client->complete($system, [['role' => 'user', 'text' => $user]], 200);

        return $raw !== null ? $this->plain(LeakedReasoningFilter::clean($raw)) : '';
    }

    // =========================================================================
    // 🔧 MÔ TẢ CÂU HỎI & HỖ TRỢ
    // =========================================================================

    /**
     * Dữ liệu câu hỏi gửi cho trình duyệt (KHÔNG kèm đáp án đúng) và lời đọc. Null nếu câu không hiển thị/đọc được.
     *
     * @return array{question: array<string, mixed>, spoken: string}|null
     */
    private function describe(Question $question): ?array
    {
        $question->loadMissing(['options', 'assets']);
        $title = $this->plain((string) $question->title);
        if ($title === '') {
            return null;
        }

        $base = [
            'id' => $question->id,
            'type' => $question->type,
            'text' => $title,
            'multi' => $question->type === 'MultipleResponse',
            'images' => $this->promptImages($question),
        ];
        $seeImage = $base['images'] !== [] ? ' Em nhìn hình trên màn hình nhé.' : '';

        if (in_array($question->type, self::CHOICE_TYPES, true)) {
            $options = $this->optionsOf($question);
            if ($options === null) {
                return null;
            }

            return [
                'question' => $base + ['options' => array_map(fn ($o) => ['key' => $o['key'], 'text' => $o['text'], 'image' => $o['image']], $options)],
                'spoken' => $this->spokenChoice($title, $options, $base['multi'], $seeImage),
            ];
        }

        $options = $question->options->sortBy('position')->values();
        if ($options->count() < 2 || $options->count() > self::MAX_ITEMS) {
            return null;
        }

        if ($question->type === 'MultipleChoiceText') {
            $items = [];
            foreach ($options as $i => $option) {
                $choices = array_values(array_filter(array_map(fn ($c) => $this->plain((string) $c), $option->metadata['available_options'] ?? []), fn ($c) => $c !== ''));
                $correct = $option->metadata['correct_index'] ?? null;
                $text = $this->plain((string) $option->content);
                if ($text === '' || count($choices) < 2 || ! is_numeric($correct) || (int) $correct < 0 || (int) $correct >= count($choices)) {
                    return null;
                }
                $items[] = ['index' => $i, 'text' => $text, 'choices' => $choices];
            }

            return [
                'question' => $base + ['items' => $items],
                'spoken' => 'Câu hỏi: ' . rtrim($title, " .?") . '?' . $seeImage . ' Câu này em làm trực tiếp trên màn hình nhé: chọn đáp án đúng ở từng dòng rồi bấm Trả lời. Em cần gợi ý thì cứ hỏi cô.',
            ];
        }

        if ($question->type === 'Matching') {
            $left = [];
            $right = [];
            foreach ($options as $i => $option) {
                [$l, $r] = $this->pairOf($option);
                if ($l === '' || $r === '') {
                    return null;
                }
                $left[] = ['id' => $i, 'text' => $l];
                $right[] = ['id' => $i, 'text' => $r];
            }
            // Xáo vế phải như phòng làm bài: chuyển phần tử đầu xuống cuối để không trùng hàng với vế trái
            $first = array_shift($right);
            $right[] = $first;

            return [
                'question' => $base + ['left' => $left, 'right' => $right],
                'spoken' => 'Câu hỏi: ' . rtrim($title, " .?") . '?' . $seeImage . ' Câu này em làm trực tiếp trên màn hình nhé: ghép từng dòng bên trái với đáp án bên phải rồi bấm Trả lời. Em cần gợi ý thì cứ hỏi cô.',
            ];
        }

        return null;
    }

    /** Vế trái / vế phải của một dòng ghép nối (giống cách phòng làm bài đọc dữ liệu) */
    private function pairOf($option): array
    {
        $left = $this->plain((string) ($option->metadata['left'] ?? ''));
        $right = $this->plain((string) ($option->metadata['right'] ?? ''));
        $content = (string) $option->content;
        if (($left === '' || $right === '') && $content !== '') {
            $parts = str_contains($content, ':::') ? explode(':::', $content) : preg_split('/\s*[–—\-:]+\s*/u', $content);
            $left = $left !== '' ? $left : $this->plain((string) ($parts[0] ?? ''));
            $right = $right !== '' ? $right : $this->plain((string) ($parts[1] ?? ''));
        }

        return [$left, $right];
    }

    /** @return array<int, string> Đường dẫn ảnh đề bài (không gồm ảnh của từng đáp án) */
    private function promptImages(Question $question): array
    {
        $optionImages = $question->options->pluck('image_path')->filter()->all();
        $paths = [];
        if (! empty($question->configuration['image_path'])) {
            $paths[] = (string) $question->configuration['image_path'];
        }
        foreach ($question->assets as $asset) {
            if ($asset->kind === 'image' && ! in_array($asset->path, $optionImages, true)) {
                $paths[] = (string) $asset->path;
            }
        }

        return array_values(array_unique(array_map(fn ($p) => $this->imageUrl($p), array_filter($paths))));
    }

    private function imageUrl(string $path): string
    {
        return preg_match('~^(https?:)?//|^/~', $path) ? $path : '/storage/' . ltrim($path, '/');
    }

    /** @return \Illuminate\Support\Collection<int, Level> */
    private function accessibleLevels(User $user)
    {
        return Level::query()->orderBy('grade')->get()->filter(fn (Level $l) => $user->canAccessLevel($l))->values();
    }

    /** Câu hỏi hợp lệ để hỏi trong trò chuyện: đã xuất bản, dạng được hỗ trợ, chỉ có ảnh (không âm thanh/video/tài liệu), thuộc khối được học */
    private function base(User $user, $levelIds): Builder
    {
        return Question::query()
            ->where('is_published', true)
            ->whereIn('type', QuestionGrader::SUPPORTED)
            ->whereDoesntHave('assets', fn ($q) => $q->where('kind', '!=', 'image'))
            ->whereHas('practiceTest', fn ($q) => $q->where('is_mock', false)->where('is_published', true)
                ->whereHas('topic', fn ($t) => $t->whereIn('level_id', $levelIds)));
    }

    private function matchTopics($levelIds, string $normHint)
    {
        if ($normHint === '' || preg_match('/^(bat ky|ngau nhien|any|tat ca)$/', $normHint)) {
            return collect();
        }

        return Topic::query()->whereIn('level_id', $levelIds)->get(['id', 'name'])
            ->filter(function (Topic $t) use ($normHint) {
                $name = $this->norm($t->name);

                return $name !== '' && (str_contains($name, $normHint) || str_contains($normHint, $name));
            })
            ->pluck('id');
    }

    /**
     * Danh sách đáp án theo thứ tự A, B, C... cho dạng chọn đáp án; null nếu câu không dùng được.
     * Đáp án chỉ có hình thì để chữ rỗng và kèm đường dẫn hình.
     *
     * @return array<int, array{key: string, text: string, image: ?string, correct: bool}>|null
     */
    private function optionsOf(Question $question): ?array
    {
        $options = [];
        foreach ($question->options->sortBy('position')->values() as $i => $option) {
            $text = $this->plain((string) $option->content);
            $image = $option->image_path ? $this->imageUrl((string) $option->image_path) : null;
            if ($text === '' && $image === null) {
                return null;
            }
            $options[] = ['key' => chr(65 + $i), 'text' => $text, 'image' => $image, 'correct' => (bool) $option->is_correct];
        }

        if (count($options) < 2 || count($options) > self::MAX_OPTIONS || ! in_array(true, array_column($options, 'correct'), true)) {
            return null;
        }

        return $options;
    }

    private function optionText(array $options, string $key): string
    {
        foreach ($options as $o) {
            if ($o['key'] === $key) {
                return $o['text'];
            }
        }

        return '';
    }

    private function spokenChoice(string $title, array $options, bool $multi, string $seeImage): string
    {
        $parts = ['Câu hỏi: ' . rtrim($title, " .?") . '?' . $seeImage];
        if ($multi) {
            $parts[] = 'Câu này có thể có nhiều đáp án đúng.';
        }
        foreach ($options as $o) {
            $parts[] = $o['text'] !== ''
                ? "Đáp án {$o['key']}: " . rtrim($o['text'], ' .') . '.'
                : "Đáp án {$o['key']}: là hình {$o['key']} trên màn hình.";
        }
        $parts[] = $multi ? 'Em chọn những đáp án nào?' : 'Em chọn đáp án nào?';

        return implode(' ', $parts);
    }

    /** Bỏ thẻ HTML, ký hiệu định dạng, khoảng trắng thừa */
    private function plain(string $text): string
    {
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/[*_#`>|~]+/u', '', $text);

        return trim(preg_replace('/\s+/u', ' ', $text));
    }

    /** Chữ thường, bỏ dấu, chỉ giữ chữ và số: dùng để so khớp lời nói với tên chủ đề/nội dung đáp án */
    private function norm(string $text): string
    {
        $ascii = Str::ascii(mb_strtolower($text));

        return trim(preg_replace('/\s+/', ' ', preg_replace('/[^a-z0-9 ]+/', ' ', $ascii)));
    }

    private function pendingKey(User $user): string
    {
        return 'voice_quiz:' . $user->id;
    }

    private function seenKey(User $user): string
    {
        return 'voice_quiz_seen:' . $user->id;
    }
}
