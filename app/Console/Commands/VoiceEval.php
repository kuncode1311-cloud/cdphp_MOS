<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\AiAssistantService;
use App\Services\VoiceAssistantService;
use App\Services\VoiceQuizService;
use App\Services\VoiceToolbox;
use App\Support\VietText;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Chấm tự động Trợ lý AI giọng nói với AI thật.
 *
 * Chạy các tình huống trong resources/voice-eval/cases.php, so câu trả lời với đáp án tính từ database lúc chạy,
 * đo thời gian, in kết quả và lưu báo cáo JSON vào storage/app/voice-eval/last.json.
 * Không ghi gì vào dữ liệu học tập; câu hỏi luyện tập (nếu AI ra) được hủy ngay sau mỗi tình huống.
 */
class VoiceEval extends Command
{
    protected $signature = 'voice:eval
        {--student= : Mã tài khoản học sinh dùng để hỏi (mặc định: học sinh làm nhiều bài nhất)}
        {--teacher= : Mã tài khoản giáo viên dùng để hỏi (mặc định: giáo viên có nhiều học sinh nhất)}
        {--only= : Chỉ chạy tình huống có tên chứa chữ này}';

    protected $description = 'Chấm tự động Trợ lý AI giọng nói với AI thật (đáp án đúng tính từ database)';

    private const DEFAULT_MAX_SECONDS = 15;
    /** Báo cáo lần chấm gần nhất: storage/app/voice-eval/last.json */
    public const REPORT = 'voice-eval/last.json';

    public function handle(VoiceAssistantService $voice, VoiceQuizService $quiz, VoiceToolbox $toolbox, AiAssistantService $assistant): int
    {
        $users = ['student' => $this->pickStudent(), 'teacher' => $this->pickTeacher()];
        $cases = require resource_path('voice-eval/cases.php');
        $only = $this->option('only') ? VietText::norm((string) $this->option('only')) : null;

        $results = [];
        foreach ($cases as $case) {
            if ($only !== null && ! str_contains(VietText::norm($case['name']), $only)) {
                continue;
            }
            $user = $users[$case['role']] ?? null;
            if (! $user) {
                $this->line("⏭  {$case['name']}: không có tài khoản " . ($case['role'] === 'teacher' ? 'giáo viên' : 'học sinh') . ' để chấm');
                continue;
            }

            $ctx = new EvalContext($user, $toolbox, $assistant);
            $quiz->clear($user);
            $history = [];
            if (isset($case['start_quiz']) && ! $ctx->openQuestion($quiz, $case['start_quiz'])) {
                $this->line("⏭  {$case['name']}: không có câu hỏi chọn một đáp án bằng chữ để thử");
                continue;
            }
            if ($ctx->question) {
                $history[] = ['role' => 'assistant', 'text' => (string) $ctx->question['spoken']];
            }
            $turns = $this->fillPlaceholders($case['turns'], $ctx);
            if ($turns === null) {
                $this->line("⏭  {$case['name']}: dữ liệu chưa đủ để hỏi");
                continue;
            }

            $result = null;
            $seconds = 0.0;
            foreach ($turns as $text) {
                $started = microtime(true);
                $result = $voice->reply($user, $history, $text);
                $seconds = round(microtime(true) - $started, 1);
                if ($result === null) {
                    break;
                }
                $history[] = ['role' => 'user', 'text' => $text];
                $history[] = ['role' => 'assistant', 'text' => $result['text']];
            }
            $quiz->clear($user);

            [$failures, $skipped] = $this->check($case['expect'], $result, $seconds, $ctx);
            if ($skipped) {
                $this->line("⏭  {$case['name']}: dữ liệu chưa đủ để chấm");
                continue;
            }

            $passed = $failures === [];
            $results[] = [
                'name' => $case['name'],
                'user' => $user->name,
                'turns' => $turns,
                'answer' => $result['display'] ?? '(AI không trả lời)',
                'route' => $result['route'] ?? null,
                'tools' => $result['tools'] ?? [],
                'seconds' => $seconds,
                'passed' => $passed,
                'failures' => $failures,
            ];
            $this->line(($passed ? '✅' : '❌') . " {$case['name']} ({$seconds}s" . (($result['tools'] ?? []) ? ' · ' . implode(', ', $result['tools']) : '') . ')' . ($passed ? '' : ' → ' . implode('; ', $failures)));
            if (! $passed && $this->output->isVerbose()) {
                $this->line('    Trả lời: ' . str_replace("\n", ' / ', (string) ($result['display'] ?? '')));
            }
        }

        if ($results === []) {
            $this->warn('Không có tình huống nào được chấm.');

            return self::FAILURE;
        }

        $passed = count(array_filter($results, fn ($r) => $r['passed']));
        $times = array_column($results, 'seconds');
        $summary = [
            'total' => count($results),
            'passed' => $passed,
            'percent' => (int) round($passed * 100 / count($results)),
            'avg_seconds' => round(array_sum($times) / count($times), 1),
            'max_seconds' => max($times),
            'ran_at' => now()->setTimezone((string) config('learning.display_timezone', 'Asia/Ho_Chi_Minh'))->format('H:i d/m/Y'),
        ];
        Storage::disk('local')->put(self::REPORT, json_encode(['summary' => $summary, 'cases' => $results], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        $this->newLine();
        $this->info("Kết quả: {$passed}/{$summary['total']} đúng ({$summary['percent']}%) · trung bình {$summary['avg_seconds']}s · chậm nhất {$summary['max_seconds']}s");
        $this->line('Báo cáo chi tiết: storage/app/' . self::REPORT . ' (thêm -v để in câu trả lời của các câu sai).');

        return $passed === $summary['total'] ? self::SUCCESS : self::FAILURE;
    }

    /**
     * @return array{0: array<int, string>, 1: bool}  [các lỗi, có bỏ qua vì thiếu dữ liệu không]
     */
    private function check(array $expect, ?array $result, float $seconds, EvalContext $ctx): array
    {
        if ($result === null) {
            return [['AI không trả lời (lỗi kết nối)'], false];
        }

        $fail = [];
        $answer = $this->normalize($result['display'] ?? '');

        if (isset($expect['route']) && $result['route'] !== $expect['route']) {
            $fail[] = 'đúng ra phải ' . ($expect['route'] === 'nhanh' ? 'trả lời nhanh' : 'gọi AI');
        }
        foreach (['quiz' => 'quiz_hint', 'card' => 'card', 'action' => 'action'] as $key => $field) {
            if (isset($expect[$key]) && ($result[$field] !== null) !== $expect[$key]) {
                $fail[] = ($expect[$key] ? 'thiếu ' : 'không được có ') . ['quiz' => 'câu hỏi luyện tập', 'card' => 'thẻ thống kê', 'action' => 'nút mở trang'][$key];
            }
        }
        if (array_key_exists('choice', $expect)) {
            $want = is_callable($expect['choice']) ? ($expect['choice'])($ctx) : $expect['choice'];
            if (($result['choice'] ?? null) !== $want) {
                $fail[] = $want === null
                    ? 'không được tự chấm (AI hiểu nhầm là đã chọn ' . implode(',', (array) $result['choice']) . ')'
                    : 'hiểu sai đáp án em chọn: cần ' . implode(',', $want) . ', AI hiểu ' . ($result['choice'] ? implode(',', $result['choice']) : 'chưa chọn');
            }
        }
        if (array_key_exists('control', $expect) && ($result['control'] ?? null) !== $expect['control']) {
            $fail[] = 'lệnh điều khiển: cần ' . ($expect['control'] ?? 'không có') . ', AI ra ' . ($result['control'] ?? 'không có');
        }
        if (isset($expect['hint_contains'])) {
            $want = is_callable($expect['hint_contains']) ? ($expect['hint_contains'])($ctx) : $expect['hint_contains'];
            if ($want === null) {
                return [[], true];
            }
            if (! $this->containsAny($this->normalize((string) ($result['quiz_hint'] ?? '')), (array) $want)) {
                $fail[] = 'ra câu hỏi sai phạm vi: cần «' . implode(' / ', (array) $want) . '», AI ghi «' . ($result['quiz_hint'] ?? '') . '»';
            }
        }
        if (isset($expect['contains_any']) && ! $this->containsAny($answer, (array) $expect['contains_any'])) {
            $fail[] = 'thiếu ý: ' . implode(' / ', (array) $expect['contains_any']);
        }
        if (isset($expect['contains_all'])) {
            $all = is_callable($expect['contains_all']) ? ($expect['contains_all'])($ctx) : $expect['contains_all'];
            if ($all === null) {
                return [[], true];
            }
            foreach ($all as $needle) {
                if (! $this->containsAny($answer, [$needle])) {
                    $fail[] = "thiếu «{$needle}»";
                }
            }
        }
        foreach ((array) ($expect['not_contains'] ?? []) as $needle) {
            if ($this->containsAny($answer, [$needle])) {
                $fail[] = "không được nói «{$needle}»";
            }
        }
        if (isset($expect['value'])) {
            $value = ($expect['value'])($ctx);
            if ($value === null) {
                return [[], true];
            }
            if (! $this->containsAny($answer, (array) $value)) {
                $fail[] = 'sai/thiếu số liệu đúng: ' . implode(' hoặc ', array_map('strval', (array) $value));
            }
        }
        $max = $expect['max_seconds'] ?? self::DEFAULT_MAX_SECONDS;
        if ($seconds > $max) {
            $fail[] = "chậm ({$seconds}s > {$max}s)";
        }

        return [$fail, false];
    }

    /** Không dấu, chữ thường; bỏ dấu chấm ngăn cách hàng nghìn và số 0 đứng đầu để so số/ngày dễ dàng */
    private function normalize(string $text): string
    {
        $text = preg_replace('/(?<=\d)[.,](?=\d{3}\b)/u', '', $text);
        $norm = VietText::norm($text);

        return preg_replace('/\b0+(\d)/', '$1', $norm);
    }

    private function containsAny(string $answer, array $needles): bool
    {
        foreach ($needles as $needle) {
            $n = $this->normalize((string) $needle);
            if ($n !== '' && preg_match('/\b' . preg_quote($n, '/') . '\b/', $answer)) {
                return true;
            }
        }

        return false;
    }

    private function fillPlaceholders(array $turns, EvalContext $ctx): ?array
    {
        $out = [];
        foreach ($turns as $text) {
            if (str_contains($text, '{chu_de_da_lam}')) {
                $topic = $ctx->topicWithAttempts();
                if ($topic === null) {
                    return null;
                }
                $text = str_replace('{chu_de_da_lam}', mb_strtolower($topic), $text);
            }
            $text = str_replace(['{dap_an_dung}', '{dap_an_sai}'], [(string) $ctx->correctText(), (string) $ctx->wrongText()], $text);
            $out[] = str_replace('{ban_khac}', $ctx->otherStudentName(), $text);
        }

        return $out;
    }

    private function pickStudent(): ?User
    {
        if ($id = $this->option('student')) {
            return User::where('role', 'student')->find((int) $id);
        }

        return User::where('role', 'student')->where('status', 'active')
            ->orderByDesc(\App\Models\StudentMistake::selectRaw('count(*)')->whereColumn('student_mistakes.user_id', 'users.id')->where('status', 'unresolved'))
            ->orderByDesc(\App\Models\TestAttempt::selectRaw('count(*)')->whereColumn('test_attempts.user_id', 'users.id'))
            ->first();
    }

    private function pickTeacher(): ?User
    {
        if ($id = $this->option('teacher')) {
            return User::where('role', 'teacher')->find((int) $id);
        }

        return User::where('role', 'teacher')->where('status', 'active')->withCount('students')->orderByDesc('students_count')->first();
    }
}
