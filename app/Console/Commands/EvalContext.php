<?php

namespace App\Console\Commands;

use App\Models\Question;
use App\Models\StudentMistake;
use App\Models\TestAttempt;
use App\Models\User;
use App\Services\AiAssistantService;
use App\Services\VoiceToolbox;

/**
 * Ngữ cảnh cho bộ chấm tự động: tính "đáp án đúng" thẳng từ database lúc chạy cho tài khoản đang được hỏi.
 */
class EvalContext
{
    /** Câu hỏi đang mở (như học sinh đang thấy) và đáp án thật của nó */
    public ?array $question = null;
    private array $options = [];

    public function __construct(public User $user, private VoiceToolbox $toolbox, private AiAssistantService $assistant)
    {
    }

    /**
     * Mở một câu hỏi chọn MỘT đáp án có nội dung bằng chữ (để học sinh "nói" đáp án). Thử vài lần nếu ra dạng khác.
     */
    public function openQuestion(\App\Services\VoiceQuizService $quiz, string $hint): bool
    {
        for ($i = 0; $i < 15; $i++) {
            $quiz->clear($this->user);
            $started = $quiz->start($this->user, $hint);
            if ($started === null) {
                return false;
            }
            $question = Question::with('options')->find($started['question']['id']);
            $options = $question?->options->sortBy('position')->values() ?? collect();
            $texts = $options->map(fn ($o) => trim(html_entity_decode(strip_tags((string) $o->content))));
            if ($question?->type === 'MultipleChoice' && $texts->every(fn ($t) => mb_strlen($t) >= 2) && $texts->unique()->count() === $texts->count()) {
                $this->question = $started;
                $this->options = $options->map(fn ($o, $i) => ['key' => chr(65 + $i), 'text' => $texts[$i], 'correct' => (bool) $o->is_correct])->all();

                return true;
            }
        }

        return false;
    }

    public function correctKey(): ?array
    {
        $key = collect($this->options)->firstWhere('correct', true)['key'] ?? null;

        return $key ? [$key] : null;
    }

    public function correctText(): ?string
    {
        return collect($this->options)->firstWhere('correct', true)['text'] ?? null;
    }

    public function wrongText(): ?string
    {
        return collect($this->options)->firstWhere('correct', false)['text'] ?? null;
    }

    public function wrongKey(): ?array
    {
        $key = collect($this->options)->firstWhere('correct', false)['key'] ?? null;

        return $key ? [$key] : null;
    }

    /** Tên chủ đề thứ $n (theo thứ tự trên trang học) của khối $grade mà học sinh được học */
    public function topicByNumber(int $grade, int $n): ?string
    {
        foreach (app(\App\Services\VoiceQuizService::class)->catalog($this->user) as $level) {
            if (preg_match('/\bkh[oố]i\s*' . $grade . '\b/iu', $level['khoi'])) {
                return $level['chu_de'][$n - 1]['ten'] ?? null;
            }
        }

        return null;
    }

    public function tool(string $name, array $args = []): array
    {
        return $this->toolbox->run($this->user, $name, $args);
    }

    public function stats(): array
    {
        return $this->assistant->dataFor($this->user)['thong_ke'] ?? [];
    }

    /** Chủ đề có nhiều lần làm bài nhất của học sinh (để hỏi theo chủ đề) */
    public function topicWithAttempts(): ?string
    {
        return TestAttempt::where('user_id', $this->user->id)->with('practiceTest.topic')->get()
            ->map(fn ($a) => $a->practiceTest?->topic?->name)->filter()->countBy()->sortDesc()->keys()->first();
    }

    /** Chủ đề có nhiều câu sai chưa khắc phục nhất */
    public function mistakeTopic(): ?string
    {
        return StudentMistake::where('user_id', $this->user->id)->where('status', 'unresolved')->with('question.practiceTest.topic')->get()
            ->map(fn ($m) => $m->question?->practiceTest?->topic?->name)->filter()->countBy()->sortDesc()->keys()->first();
    }

    /** Các chủ đề có bài trùng tên mà học sinh đã làm (trả về null nếu ít hơn $min chủ đề thì không chấm) */
    public function sameNameTopics(string $testName, int $min): ?array
    {
        $topics = TestAttempt::where('user_id', $this->user->id)
            ->whereHas('practiceTest', fn ($q) => $q->where('name', $testName))
            ->with('practiceTest.topic')->get()
            ->map(fn ($a) => $a->practiceTest?->topic?->name)->filter()->unique()->values()->take(2)->all();

        return count($topics) >= $min ? $topics : null;
    }

    /** Học sinh của giáo viên */
    public function students(): array
    {
        return $this->assistant->studentsOf($this->user, (string) config('learning.display_timezone', 'Asia/Ho_Chi_Minh'));
    }

    /** Học sinh "điểm thấp nhất": chấp nhận em có điểm trung bình thấp nhất hoặc em có lần làm thấp nhất */
    public function weakestStudents(): ?array
    {
        $done = array_values(array_filter($this->students(), fn ($s) => $s['so_bai_da_lam'] > 0));
        if ($done === []) {
            return null;
        }
        usort($done, fn ($a, $b) => $a['diem_trung_binh'] <=> $b['diem_trung_binh']);
        $byAvg = $done[0]['ten'];
        usort($done, fn ($a, $b) => $a['diem_thap_nhat'] <=> $b['diem_thap_nhat']);

        return array_values(array_unique([$byAvg, $done[0]['ten']]));
    }

    /** Một học sinh khác (không phải người đang hỏi) để thử quyền riêng tư */
    public function otherStudentName(): string
    {
        return (string) User::where('role', 'student')->whereKeyNot($this->user->id)->value('name') ?: 'Minh';
    }
}
