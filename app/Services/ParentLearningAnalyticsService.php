<?php

namespace App\Services;

use App\Models\PracticeTest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

/**
 * Dịch vụ Phân Tích Học Tập Dành Cho Phụ Huynh (Parent Learning Analytics Service)
 *
 * SOURCE OF TRUTH cho scoring: config/learning.php
 *
 * Hai khái niệm điểm số:
 *  - Game Rule (DB pass_score = max_score = 1000): học sinh phải đạt 1000/1000 để "vượt màn"
 *  - Learning Milestone (config learning.pass_score = 700): chuẩn IIG IC3 GS6 dùng cho báo cáo phụ huynh
 *
 * Timezone: App = UTC, người dùng = GMT+7 (Asia/Ho_Chi_Minh).
 * Mọi tính toán ngày/streak phải dùng config('learning.display_timezone').
 */
class ParentLearningAnalyticsService
{
    /**
     * Class constants — giữ để các view/blade đang dùng không bị lỗi.
     * Giá trị thực tế được điều phối bởi config/learning.php.
     */
    public const DEFAULT_MAX_SCORE = 1000;
    public const DEFAULT_PASS_SCORE = 700;
    public const EXCELLENT_SCORE = 900;

    /**
     * Phân tích toàn diện dữ liệu học tập của học sinh dành cho Phụ huynh
     */
    public function getAnalytics(User $student, string $timeRange = 'all', string $selectedLevelId = 'all'): array
    {
        // 1. Lọc và nạp các lượt làm bài với eager loading chống N+1
        // with() lấy sẵn bài luyện, chủ đề và khối, tránh mỗi lượt làm lại phải truy vấn riêng.
        $attemptsQuery = $student->attempts()
            ->with(['practiceTest.topic.level'])
            ->latest('completed_at');

        $timeRangeLabel = match ($timeRange) {
            '7days'      => '7 ngày qua',
            '30days'     => '30 ngày qua',
            'this_month' => 'tháng này',
            default      => 'toàn bộ thời gian',
        };

        $tz = config('learning.display_timezone', 'Asia/Ho_Chi_Minh');

        if ($timeRange === '7days') {
            $attemptsQuery->where('completed_at', '>=', Carbon::now($tz)->subDays(7)->utc());
        } elseif ($timeRange === '30days') {
            $attemptsQuery->where('completed_at', '>=', Carbon::now($tz)->subDays(30)->utc());
        } elseif ($timeRange === 'this_month') {
            $attemptsQuery->where('completed_at', '>=', Carbon::now($tz)->startOfMonth()->utc());
        }

        if ($selectedLevelId !== 'all' && is_numeric($selectedLevelId)) {
            $attemptsQuery->whereHas('practiceTest.topic', function ($q) use ($selectedLevelId) {
                $q->where('level_id', (int) $selectedLevelId);
            });
        }

        $attempts = $attemptsQuery->get();

        // 2. Tính toán 4 chỉ số KPI cốt lõi
        $kpis = $this->calculateKPIs($student, $attempts, $timeRange);

        // 3. Thống kê theo Chủ đề
        $topicStats = $this->calculateTopicStats($attempts);

        // 4. Đánh giá Điểm mạnh & Điểm cần bổ trợ
        $strengthsAndWeaknesses = $this->evaluateStrengthsAndWeaknesses($topicStats, $attempts->count());

        // 5. Danh sách Việc cần quan tâm (Smart Severity & Guidance)
        $alertsData = $this->generateCareItems($student, $attempts);

        // 6. Tình hình của con (Tầng 2: Hiểu trong 15 giây)
        $overviewInsight = $this->buildOverviewInsight($kpis, $strengthsAndWeaknesses, $timeRangeLabel);

        // 7. Chuẩn bị dữ liệu biểu đồ thích ứng
        $charts = $this->buildAdaptiveChartsData($attempts, $topicStats);

        // 8. Calendar heatmap 30 ngày (không phụ thuộc filter, luôn tính toàn bộ)
        $calendar = $this->buildCalendarData($student);

        return [
            'timeRange'             => $timeRange,
            'timeRangeLabel'        => $timeRangeLabel,
            'selectedLevelId'       => $selectedLevelId,
            'attempts'              => $attempts,
            'kpis'                  => $kpis,
            'topicStats'            => $topicStats,
            'strengthsAndWeaknesses' => $strengthsAndWeaknesses,
            'careItems'             => $alertsData['careItems'],
            'timingObservations'    => $alertsData['timingObservations'],
            'overviewInsight'       => $overviewInsight,
            'charts'                => $charts,
            'calendar'              => $calendar,
        ];
    }

    /**
     * Xác định mốc đạt (pass_score) thực tế của một bài luyện.
     *
     * Logic:
     *  - Nếu DB lưu pass_score < max_score (ví dụ 700/1000): dùng giá trị DB đó.
     *  - Nếu DB lưu pass_score = max_score (1000/1000 — game rule): dùng Learning Milestone từ config.
     *
     * Điều này đảm bảo báo cáo phụ huynh luôn dùng chuẩn IC3 (700) dù seeder đã set 1000/1000.
     */
    public static function resolvePassScore(?PracticeTest $test): int
    {
        $defaultPass = config('learning.pass_score', self::DEFAULT_PASS_SCORE);
        $defaultMax  = config('learning.max_score', self::DEFAULT_MAX_SCORE);

        if (! $test) {
            return $defaultPass;
        }

        $max = $test->max_score ?: $defaultMax;
        if ($test->pass_score && $test->pass_score < $max) {
            return (int) $test->pass_score;
        }

        // DB: pass_score = max_score (game rule) → trả về learning milestone
        return $defaultPass;
    }

    /**
     * Xác định điểm tối đa của một bài luyện
     */
    public static function resolveMaxScore(?PracticeTest $test): int
    {
        return $test?->max_score ?: config('learning.max_score', self::DEFAULT_MAX_SCORE);
    }

    /**
     * Tính toán 4 chỉ số KPI nhìn 5 giây
     * FIX V4: Timezone đúng (Asia/Ho_Chi_Minh) thay vì UTC
     */
    private function calculateKPIs(User $student, Collection $attempts, string $timeRange): array
    {
        $tz           = config('learning.display_timezone', 'Asia/Ho_Chi_Minh');
        $passScore    = config('learning.pass_score', self::DEFAULT_PASS_SCORE);
        $excellentScore = config('learning.excellent_score', self::EXCELLENT_SCORE);

        $totalAttempts = $attempts->count();
        $passedAttempts = $attempts->filter(function ($a) {
            $pass = self::resolvePassScore($a->practiceTest);
            return $a->score >= $pass;
        })->count();

        $passRate  = $totalAttempts > 0 ? (int) round(($passedAttempts / $totalAttempts) * 100) : 0;
        $avgScore  = $totalAttempts > 0 ? (int) round($attempts->avg('score')) : 0;
        $highestScore = $totalAttempts > 0 ? $attempts->max('score') : 0;

        $totalSeconds       = $attempts->sum('duration_seconds');
        $totalMinutes       = (int) round($totalSeconds / 60);
        $avgMinutesPerTest  = $totalAttempts > 0 ? round(($totalSeconds / $totalAttempts) / 60, 1) : 0;

        // ============================================================
        // STREAK — sử dụng timezone GMT+7 để tránh lệch ngày
        // ============================================================
        $datesWithAttempts = $student->attempts()
            ->whereNotNull('completed_at')
            ->get()
            ->map(fn ($a) => $a->completed_at->setTimezone($tz)->format('Y-m-d'))
            ->unique()
            ->values()
            ->toArray();
        rsort($datesWithAttempts);

        $streakDays = 0;
        $today     = Carbon::now($tz)->format('Y-m-d');
        $yesterday = Carbon::now($tz)->subDay()->format('Y-m-d');

        if (in_array($today, $datesWithAttempts)) {
            $cursor = Carbon::now($tz);
        } elseif (in_array($yesterday, $datesWithAttempts)) {
            $cursor = Carbon::now($tz)->subDay();
        } else {
            $cursor = null;
        }

        if ($cursor) {
            // subDay() là mutable: cursor dịch chuyển lùi mỗi vòng lặp
            while (in_array($cursor->format('Y-m-d'), $datesWithAttempts)) {
                $streakDays++;
                $cursor->subDay();
            }
        }

        // "Học gần nhất" — so sánh theo timezone GMT+7
        $lastPracticeDate       = count($datesWithAttempts) > 0 ? Carbon::parse($datesWithAttempts[0], $tz) : null;
        $daysSinceLastPractice  = $lastPracticeDate ? (int) $lastPracticeDate->diffInDays(Carbon::now($tz)) : null;

        $lastPracticeText = 'Chưa có lượt học';
        if ($daysSinceLastPractice === 0) {
            $lastPracticeText = 'Hôm nay con đã học';
        } elseif ($daysSinceLastPractice === 1) {
            $lastPracticeText = 'Học gần nhất hôm qua';
        } elseif ($daysSinceLastPractice !== null) {
            $lastPracticeText = 'Học cách đây ' . $daysSinceLastPractice . ' ngày';
        }

        // So sánh tiến độ với kỳ trước (CHỈ khi kỳ trước có dữ liệu thật)
        $scoreComparison = $this->calculatePeriodComparison($student, $timeRange, $avgScore);

        // Xếp loại học lực theo Learning Milestone (700)
        $academicRank = match (true) {
            $totalAttempts === 0    => ['title' => 'Chưa xếp loại', 'badge' => 'Chưa có điểm',      'color' => '#64748b', 'bg' => '#f1f5f9'],
            $avgScore >= $excellentScore => ['title' => 'Xuất sắc',   'badge' => '🏆 Xuất sắc',     'color' => '#15803d', 'bg' => '#dcfce7'],
            $avgScore >= $passScore => ['title' => 'Đạt chuẩn',       'badge' => '⭐ Đạt chuẩn',    'color' => '#1d4ed8', 'bg' => '#dbeafe'],
            $avgScore >= 500        => ['title' => 'Cần cố gắng',     'badge' => '⚡ Cần cố gắng',  'color' => '#b45309', 'bg' => '#fef3c7'],
            default                 => ['title' => 'Cần hỗ trợ',      'badge' => '🌱 Cần hỗ trợ',   'color' => '#b91c1c', 'bg' => '#fee2e2'],
        };

        return [
            'totalAttempts'         => $totalAttempts,
            'passedAttempts'        => $passedAttempts,
            'passRate'              => $passRate,
            'avgScore'              => $avgScore,
            'highestScore'          => $highestScore,
            'totalMinutes'          => $totalMinutes,
            'avgMinutesPerTest'     => $avgMinutesPerTest,
            'streakDays'            => $streakDays,
            'daysSinceLastPractice' => $daysSinceLastPractice,
            'lastPracticeText'      => $lastPracticeText,
            'scoreComparison'       => $scoreComparison,
            'academicRank'          => $academicRank,
        ];
    }

    /**
     * So sánh điểm số với kỳ trước (nếu có dữ liệu)
     */
    private function calculatePeriodComparison(User $student, string $timeRange, int $currentAvg): ?array
    {
        $previousAttemptsQuery = $student->attempts();

        if ($timeRange === '7days') {
            $previousAttemptsQuery->whereBetween('completed_at', [now()->subDays(14), now()->subDays(7)]);
            $label = 'so với 7 ngày trước';
        } elseif ($timeRange === '30days') {
            $previousAttemptsQuery->whereBetween('completed_at', [now()->subDays(60), now()->subDays(30)]);
            $label = 'so với 30 ngày trước';
        } elseif ($timeRange === 'this_month') {
            $previousAttemptsQuery->whereBetween('completed_at', [
                now()->subMonth()->startOfMonth(),
                now()->subMonth()->endOfMonth(),
            ]);
            $label = 'so với tháng trước';
        } else {
            return null;
        }

        $prevAttempts = $previousAttemptsQuery->get();
        if ($prevAttempts->isEmpty() || $currentAvg === 0) {
            return null;
        }

        $prevAvg = (int) round($prevAttempts->avg('score'));
        $diff    = $currentAvg - $prevAvg;

        if ($diff === 0) {
            return null;
        }

        return [
            'diff'       => $diff,
            'isIncrease' => $diff > 0,
            'text'       => ($diff > 0 ? '↑ ' : '↓ ') . abs($diff) . ' điểm ' . $label,
        ];
    }

    /**
     * Tính toán thống kê theo từng Chủ đề
     */
    private function calculateTopicStats(Collection $attempts): SupportCollection
    {
        $grouped = $attempts->groupBy(function ($a) {
            $t = $a->practiceTest?->topic;
            if ($t && $t->position) {
                return 'Chủ đề ' . $t->position . ': ' . $t->name;
            }
            return $t?->name ?? 'Tổng hợp';
        });

        return $grouped->map(function ($items, $topicName) {
            $count          = $items->count();
            $avgScore       = (int) round($items->avg('score'));
            $maxScore       = $items->max('score');
            $minScore       = $items->min('score');
            $totalCorrect   = $items->sum('correct_answers');
            $totalQuestions = $items->sum('total_questions');
            $accuracyRate   = $totalQuestions > 0 ? round(($totalCorrect / $totalQuestions) * 100, 1) : 0;
            $topic          = $items->first()->practiceTest?->topic;

            return [
                'name'           => $topicName,
                'topic'          => $topic,
                'count'          => $count,
                'avgScore'       => $avgScore,
                'maxScore'       => $maxScore,
                'minScore'       => $minScore,
                'totalCorrect'   => $totalCorrect,
                'totalQuestions' => $totalQuestions,
                'accuracyRate'   => $accuracyRate,
            ];
        })->sortByDesc('avgScore');
    }

    /**
     * Đánh giá Điểm mạnh & Điểm cần bổ trợ
     */
    private function evaluateStrengthsAndWeaknesses(SupportCollection $topicStats, int $totalAttempts): array
    {
        if ($topicStats->isEmpty()) {
            return [
                'strength'      => null,
                'weakness'      => null,
                'strengthLabel' => 'Kết quả tốt nhất hiện tại',
                'weaknessLabel' => 'Nội dung nên ôn thêm',
            ];
        }

        $passScore        = config('learning.pass_score', self::DEFAULT_PASS_SCORE);
        $minForStrength   = config('learning.min_attempts_for_strength', 3);

        $strength = $topicStats->first();
        $weakness = $topicStats->count() > 1 ? $topicStats->last() : null;

        // "Chủ đề thế mạnh" chỉ xác nhận khi đủ dữ liệu và điểm thực sự tốt
        $isTrueStrength = $totalAttempts >= $minForStrength && ($strength['avgScore'] ?? 0) >= $passScore;
        $strengthLabel  = $isTrueStrength ? 'Chủ đề thế mạnh của con' : 'Kết quả tốt nhất hiện tại';
        $weaknessLabel  = $totalAttempts >= $minForStrength ? 'Chủ đề con thường gặp khó' : 'Nội dung nên ôn thêm';

        return [
            'strength'      => $strength,
            'weakness'      => $weakness,
            'strengthLabel' => $strengthLabel,
            'weaknessLabel' => $weaknessLabel,
        ];
    }

    /**
     * Tầng 2: TÌNH HÌNH CỦA CON (Hiểu trong 15 giây)
     */
    private function buildOverviewInsight(array $kpis, array $strengthsAndWeaknesses, string $timeRangeLabel): array
    {
        $passScore = config('learning.pass_score', self::DEFAULT_PASS_SCORE);
        $total     = $kpis['totalAttempts'];

        if ($total === 0) {
            return [
                'activityLine' => "Chưa có lượt học nào trong {$timeRangeLabel}",
                'bestLine'     => 'Chưa có dữ liệu bài làm',
                'needCareLine' => 'Chưa có dữ liệu bài làm',
                'conclusion'   => "Con chưa làm bài nào trong {$timeRangeLabel}. Phụ huynh hãy nhắc con làm 1 bài luyện để bắt đầu nhé!",
            ];
        }

        $passed       = $kpis['passedAttempts'];
        $activityLine = "Con đã làm {$total} bài trong {$timeRangeLabel} ({$passed}/{$total} bài đạt mốc {$passScore})";

        $strength = $strengthsAndWeaknesses['strength'];
        $weakness = $strengthsAndWeaknesses['weakness'];

        $bestLine = $strength
            ? "{$strength['name']} — {$strength['avgScore']}/1000 điểm ({$strength['totalCorrect']}/{$strength['totalQuestions']} câu đúng)"
            : 'Chưa đủ dữ liệu';

        $needCareLine = ($weakness && $weakness['name'] !== ($strength['name'] ?? ''))
            ? "{$weakness['name']} — {$weakness['avgScore']}/1000 điểm ({$weakness['totalCorrect']}/{$weakness['totalQuestions']} câu đúng)"
            : ($strength && $strength['avgScore'] < $passScore
                ? "Cần tăng tốc để đạt mốc {$passScore} điểm"
                : 'Chưa có phần nào đáng lo ngại');

        if ($passed === $total) {
            $conclusion = "Rất tuyệt vời! Con đã vượt mốc {$passScore} điểm ở tất cả bài đã làm. Phụ huynh hãy khen ngợi con nhé.";
        } elseif ($passed > 0) {
            $conclusion = "Con đã có bài đạt mốc {$passScore} điểm. Hãy cùng con duy trì rèn luyện thêm ở các bài chưa đạt.";
        } else {
            $targetTopic = $weakness ? $weakness['name'] : ($strength ? $strength['name'] : 'bài luyện');
            $conclusion  = "Hiện con chưa có bài nào đạt mốc {$passScore} điểm. Nên ưu tiên cùng con ôn lại chủ đề {$targetTopic}.";
        }

        return [
            'activityLine' => $activityLine,
            'bestLine'     => $bestLine,
            'needCareLine' => $needCareLine,
            'conclusion'   => $conclusion,
        ];
    }

    /**
     * Tầng 3: VIỆC CẦN QUAN TÂM
     * Phân loại 5 cấp độ dựa trên tín hiệu đa chiều.
     */
    private function generateCareItems(User $student, Collection $attempts): array
    {
        $careItems          = [];
        $timingObservations = [];
        $passScore          = config('learning.pass_score', self::DEFAULT_PASS_SCORE);
        $excellentScore     = config('learning.excellent_score', self::EXCELLENT_SCORE);
        $criticalThreshold  = config('learning.critical_score_threshold', 400);
        $minForTrend        = config('learning.min_attempts_for_trend', 2);
        $fastThreshold      = config('learning.fast_attempt_threshold_seconds', 60);
        $minAccuracy        = config('learning.min_accuracy_for_fast_attempt', 0.4);

        $groupedByTest = $attempts->groupBy('practice_test_id');

        foreach ($groupedByTest as $testId => $testAttempts) {
            $test = $testAttempts->first()->practiceTest;
            if (! $test) {
                continue;
            }

            $resolvedPassScore = self::resolvePassScore($test);
            $maxScore          = self::resolveMaxScore($test);
            $attemptCount      = $testAttempts->count();
            $latestAttempt     = $testAttempts->sortByDesc('completed_at')->first();
            $latestScore       = $latestAttempt->score;
            $bestScore         = $testAttempts->max('score');
            $failCount         = $testAttempts->filter(fn ($a) => $a->score < $resolvedPassScore)->count();
            $totalQuestions    = $latestAttempt->total_questions ?? 0;
            $correctAnswers    = $latestAttempt->correct_answers ?? 0;
            $accuracy          = $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100, 1) : 0;
            $missingPoints     = max(0, $resolvedPassScore - $latestScore);

            $firstAttemptScore = $testAttempts->sortBy('completed_at')->first()->score;
            $isImproving       = $attemptCount >= $minForTrend && $latestScore > $firstAttemptScore;

            // Phân loại 5 cấp độ
            if ($latestScore >= $excellentScore) {
                $level      = 5;
                $levelBadge = '🟢 Làm tốt';
                $themeColor = '#10b981';
                $what       = "{$test->name} ({$test->topic?->name})";
                $why        = "Con đạt kết quả rất tốt: {$latestScore}/{$maxScore} điểm ({$correctAnswers}/{$totalQuestions} câu đúng).";
                $actionText = 'Duy trì phong độ';
                $actionUrl  = null;
            } elseif ($latestScore >= $resolvedPassScore) {
                $level      = 5;
                $levelBadge = '🟢 Đã đạt mốc';
                $themeColor = '#10b981';
                $what       = "{$test->name} ({$test->topic?->name})";
                $why        = "Con đã vượt mốc đạt {$resolvedPassScore} điểm ({$latestScore}/{$maxScore} điểm).";
                $actionText = 'Luyện thêm để đạt điểm tối đa';
                $actionUrl  = route('tests.show', $test->slug);
            } elseif ($isImproving) {
                $level      = 4;
                $levelBadge = '🔵 Đang tiến bộ';
                $themeColor = '#3b82f6';
                $what       = "{$test->name} ({$test->topic?->name})";
                $why        = "Điểm của con đã tăng từ {$firstAttemptScore} lên {$latestScore} điểm. Còn thiếu {$missingPoints} điểm để chạm mốc {$resolvedPassScore}.";
                $actionText = 'Làm thêm 1 lần để vượt mốc';
                $actionUrl  = route('tests.show', $test->slug);
            } elseif ($attemptCount >= 3 || ($failCount >= 2 && $latestScore < $criticalThreshold)) {
                $level      = 3;
                $levelBadge = '🔴 Cần ưu tiên';
                $themeColor = '#ef4444';
                $what       = "{$test->name} ({$test->topic?->name})";
                $why        = "Con đã làm {$attemptCount} lần nhưng chưa đạt mốc {$resolvedPassScore}. Điểm gần nhất là {$latestScore}/{$maxScore} (thiếu {$missingPoints} điểm).";
                $actionText = 'Xem lại bài học & làm lại';
                $actionUrl  = route('tests.show', $test->slug);
            } elseif ($attemptCount >= 2 || $latestScore < 500) {
                $level      = 2;
                $levelBadge = '🟠 Cần ôn thêm';
                $themeColor = '#f97316';
                $what       = "{$test->name} ({$test->topic?->name})";
                $why        = "Con trả lời đúng {$correctAnswers}/{$totalQuestions} câu ({$accuracy}%). Còn thiếu {$missingPoints} điểm để đạt mốc {$resolvedPassScore}.";
                $actionText = 'Cùng con ôn lại bài này';
                $actionUrl  = route('tests.show', $test->slug);
            } else {
                $level      = 1;
                $levelBadge = '🟡 Cần theo dõi';
                $themeColor = '#eab308';
                $what       = "{$test->name} ({$test->topic?->name})";
                $why        = "Lần làm đầu tiên đạt {$latestScore}/{$maxScore}, thiếu {$missingPoints} điểm để đạt mốc {$resolvedPassScore}.";
                $actionText = 'Khuyến khích con làm lại';
                $actionUrl  = route('tests.show', $test->slug);
            }

            if ($latestScore < $resolvedPassScore) {
                $careItems[] = [
                    'level'          => $level,
                    'badge'          => $levelBadge,
                    'color'          => $themeColor,
                    'testName'       => $test->name,
                    'topicName'      => $test->topic?->name ?? 'Tổng hợp',
                    'levelName'      => $test->topic?->level?->name ?? '',
                    'what'           => $what,
                    'why'            => $why,
                    'latestScore'    => $latestScore,
                    'maxScore'       => $maxScore,
                    'passScore'      => $resolvedPassScore,
                    'missingPoints'  => $missingPoints,
                    'correctAnswers' => $correctAnswers,
                    'totalQuestions' => $totalQuestions,
                    'accuracy'       => $accuracy,
                    'attemptCount'   => $attemptCount,
                    'actionText'     => $actionText,
                    'actionUrl'      => $actionUrl,
                ];
            }

            // Ghi nhận nhận xét về thời gian làm bài (dữ liệu khách quan)
            foreach ($testAttempts as $att) {
                if ($att->duration_seconds < $fastThreshold
                    && $att->total_questions > 0
                    && ($att->correct_answers / $att->total_questions) < $minAccuracy) {
                    $timingObservations[] = [
                        'testName'     => $test->name,
                        'topicName'    => $test->topic?->name ?? 'Tổng hợp',
                        'durationText' => $att->duration_seconds . ' giây',
                        'accuracyText' => round(($att->correct_answers / $att->total_questions) * 100) . '%',
                        'dateText'     => $att->completed_at ? $att->completed_at->setTimezone(config('learning.display_timezone', 'Asia/Ho_Chi_Minh'))->format('d/m/Y H:i') : '',
                        'note'         => "Lượt làm bài này có thời gian khá ngắn ({$att->duration_seconds} giây) và tỷ lệ trả lời đúng thấp. Phụ huynh có thể nhắc con đọc kỹ từng câu ở lần làm tiếp theo nhé.",
                    ];
                }
            }
        }

        // Sắp xếp ưu tiên: Level 3 → Level 2 → Level 1
        usort($careItems, fn ($a, $b) => $b['level'] <=> $a['level']);

        return [
            'careItems'          => $careItems,
            'timingObservations' => $timingObservations,
        ];
    }

    /**
     * Chuẩn bị dữ liệu biểu đồ thích ứng (Adaptive Charts)
     * V4: Thêm Radar chart data, giữ Line + Topic bar + Efficiency
     */
        private function buildAdaptiveChartsData(Collection $attempts, SupportCollection $topicStats): array
    {
        $passScore      = config('learning.pass_score', self::DEFAULT_PASS_SCORE);
        $excellentScore = config('learning.excellent_score', self::EXCELLENT_SCORE);
        $maxLine        = config('learning.line_chart_max_attempts', 15);
        $maxEfficiency  = config('learning.efficiency_max_items', 6);
        $tz             = config('learning.display_timezone', 'Asia/Ho_Chi_Minh');

        // Sắp xếp các lượt làm theo thời gian tăng dần
        $chronological = $attempts->sortBy('completed_at')->values()->take(-$maxLine);

        // =========================================================================
        // 1. BIỂU ĐỒ BIẾN ĐỘNG ĐIỂM SỐ KIỂU CHỨNG KHOÁN (STOCK MOMENTUM & TREND)
        // =========================================================================
        $lineLabels        = [];
        $lineScores        = [];
        $linePassThreshold = [];
        $lineTooltipsMeta  = [];
        $pointColors       = [];
        $pointRadii        = [];

        $prevScore = null;
        $totalChron = $chronological->count();

        foreach ($chronological as $index => $att) {
            $dateStr   = $att->completed_at ? $att->completed_at->setTimezone($tz)->format('d/m') : 'N/A';
            $timeStr   = $att->completed_at ? $att->completed_at->setTimezone($tz)->format('H:i') : '';
            $testName  = $att->practiceTest?->name ?? ('Bài ' . ($index + 1));
            $topic = $att->practiceTest?->topic;
            $topicName = $topic?->name ?? 'Tổng hợp';
            $topicLabel = ($topic && $topic->position) ? ('CĐ ' . $topic->position . ' - ' . $topicName) : $topicName;
            $ps        = self::resolvePassScore($att->practiceTest);
            $ms        = self::resolveMaxScore($att->practiceTest);

            $diff = null;
            $diffText = '';
            $trend = 'same';
            if ($prevScore !== null) {
                $diff = $att->score - $prevScore;
                if ($diff > 0) {
                    $diffText = '▲ Tăng ' . $diff . 'đ';
                    $trend = 'up';
                } elseif ($diff < 0) {
                    $diffText = '▼ Giảm ' . abs($diff) . 'đ';
                    $trend = 'down';
                } else {
                    $diffText = '► Không đổi';
                    $trend = 'same';
                }
            } else {
                $diffText = '● Khởi điểm: ' . $att->score . 'đ';
                $trend = 'start';
            }

            // Điểm màu: Xanh lá nếu đạt chuẩn (>= 700), Cam nếu 500-699, Đỏ nếu < 500
            $ptColor = $att->score >= $ps ? '#10b981' : ($att->score >= 500 ? '#f59e0b' : '#ef4444');
            $pointColors[] = $ptColor;
            $pointRadii[]  = 6;

            $lineLabels[]        = [$topicLabel, $dateStr];
            $lineScores[]        = $att->score;
            $linePassThreshold[] = $ps;

            $lineTooltipsMeta[] = [
                'order'     => $index + 1,
                'date'      => $dateStr . ' ' . $timeStr,
                'testName'  => $testName,
                'topicName' => ($topic && $topic->position ? ('Chủ đề ' . $topic->position . ': ' . $topicName) : $topicName),
                'score'     => $att->score,
                'maxScore'  => $ms,
                'passScore' => $ps,
                'diff'      => $diff,
                'diffText'  => $diffText,
                'trend'     => $trend,
                'correct'   => $att->correct_answers,
                'total'     => $att->total_questions,
                'isPassed'  => $att->score >= $ps,
            ];

            $prevScore = $att->score;
        }

        // Ticker biến động gần nhất kiểu bảng điện tử
        $latestAttempt = $chronological->last();
        $secondLatest  = $chronological->count() >= 2 ? $chronological->slice(-2, 1)->first() : null;

        $tickerDiff = 0;
        $tickerDiffText = 'Chưa có biến động';
        $tickerIsUp = null;

        if ($latestAttempt && $secondLatest) {
            $tickerDiff = $latestAttempt->score - $secondLatest->score;
            if ($tickerDiff > 0) {
                $tickerDiffText = '▲ Tăng ' . $tickerDiff . ' điểm';
                $tickerIsUp = true;
            } elseif ($tickerDiff < 0) {
                $tickerDiffText = '▼ Giảm ' . abs($tickerDiff) . ' điểm';
                $tickerIsUp = false;
            } else {
                $tickerDiffText = '► Điểm giữ nguyên';
                $tickerIsUp = null;
            }
        }

        $peakAttempt = $attempts->sortByDesc('score')->first();
        $peakTopicObj = $peakAttempt?->practiceTest?->topic;
        $peakTopicName = $peakTopicObj ? (($peakTopicObj->position ? 'Chủ đề ' . $peakTopicObj->position . ': ' : '') . $peakTopicObj->name) : 'Nổi bật';

        $stockTicker = [
            'latestScore'    => $latestAttempt?->score ?? 0,
            'prevScore'      => $secondLatest?->score,
            'diff'           => $tickerDiff,
            'diffText'       => $tickerDiffText,
            'isUp'           => $tickerIsUp,
            'peakScore'      => $attempts->max('score') ?? 0,
            'peakTopic'      => $peakTopicName,
            'lowestScore'    => $attempts->min('score') ?? 0,
            'passScore'      => $passScore,
            'distanceToPass' => $latestAttempt ? max(0, $passScore - $latestAttempt->score) : $passScore,
        ];

        // =========================================================================
        // 2. BIỂU ĐỒ CỘT ĐA SẮC THEO TỪNG CHỦ ĐỀ (VIBRANT TOPIC COLUMN CHART)
        // =========================================================================
        $topicPalette = [
            '#6366f1', // Indigo hiện đại
            '#06b6d4', // Cyan ngọc
            '#10b981', // Emerald xanh lá
            '#f59e0b', // Amber vàng cam
            '#ec4899', // Pink hồng rực
            '#8b5cf6', // Violet tím
            '#3b82f6', // Sky xanh dương
            '#f97316', // Orange cam rực
        ];

        $topicLabels     = [];
        $topicScores     = [];
        $topicAccuracies = [];
        $topicColors     = [];
        $topicCounts     = [];

        $colorIndex = 0;
        foreach ($topicStats as $stat) {
            $topicLabels[]     = $stat['name'];
            $topicScores[]     = $stat['avgScore'];
            $topicAccuracies[] = $stat['accuracyRate'];
            $topicCounts[]     = $stat['count'];
            $topicColors[]     = $topicPalette[$colorIndex % count($topicPalette)];
            $colorIndex++;
        }

        // =========================================================================
        // 3. ĐỒNG HỒ TỐC ĐỘ NĂNG LỰC BÁN NGUYỆT (SPEEDOMETER / GAUGE METER)
        // =========================================================================
        $avgScore = $attempts->count() > 0 ? (int) round($attempts->avg('score')) : 0;
        $gaugeStatus = match (true) {
            $attempts->isEmpty()       => ['label' => 'Chưa có dữ liệu', 'color' => '#64748b', 'badge' => 'Chưa có điểm'],
            $avgScore >= $excellentScore => ['label' => 'Xuất sắc', 'color' => '#10b981', 'badge' => '🏆 Xuất sắc'],
            $avgScore >= $passScore      => ['label' => 'Đạt chuẩn', 'color' => '#3b82f6', 'badge' => '⭐ Đạt chuẩn'],
            $avgScore >= 500             => ['label' => 'Cần cố gắng', 'color' => '#f59e0b', 'badge' => '⚡ Cần cố gắng'],
            default                      => ['label' => 'Cần hỗ trợ', 'color' => '#ef4444', 'badge' => '🌱 Cần hỗ trợ'],
        };

        // =========================================================================
        // 4. BIỂU ĐỒ PHÂN BỔ CÂU HỎI ĐÚNG / SAI (ACCURACY DONUT)
        // =========================================================================
        $totalCorrect = $attempts->sum('correct_answers');
        $totalQuestions = $attempts->sum('total_questions');
        $totalWrong = max(0, $totalQuestions - $totalCorrect);
        $accuracyRate = $totalQuestions > 0 ? round(($totalCorrect / $totalQuestions) * 100, 1) : 0;

        // =========================================================================
        // 5. RADAR & EFFICIENCY (Giữ lại cho tương thích ngược)
        // =========================================================================
        $radarPassLine = array_fill(0, count($topicLabels), $passScore);
        $useRadar      = count($topicLabels) >= 3; // Chỉ bật radar khi có ít nhất 3 đỉnh tam giác thực thụ

        $efficiencyItems = [];
        foreach ($chronological->take(-$maxEfficiency) as $att) {
            $acc = $att->total_questions > 0
                ? round(($att->correct_answers / $att->total_questions) * 100)
                : 0;
            $durationText = $att->duration_seconds < 60
                ? "{$att->duration_seconds} giây"
                : floor($att->duration_seconds / 60) . ' phút ' . ($att->duration_seconds % 60) . ' giây';

            $efficiencyItems[] = [
                'testName'     => $att->practiceTest?->name ?? 'Bài thi',
                'topicName'    => $att->practiceTest?->topic?->name ?? '',
                'score'        => $att->score,
                'correct'      => $att->correct_answers,
                'total'        => $att->total_questions,
                'accuracy'     => $acc,
                'durationText' => $durationText,
                'dateText'     => $att->completed_at
                    ? $att->completed_at->setTimezone($tz)->format('d/m/Y')
                    : '',
            ];
        }

        // Thống kê xếp loại theo mốc đạt chuẩn 700đ (cho Donut chart)
        $totalAttemptsCount = $attempts->count();
        $passedCount        = $attempts->filter(fn ($a) => $a->score >= self::resolvePassScore($a->practiceTest))->count();
        $failedCount        = max(0, $totalAttemptsCount - $passedCount);
        $passPercentage     = $totalAttemptsCount > 0 ? (int) round(($passedCount / $totalAttemptsCount) * 100) : 0;
        $failPercentage     = $totalAttemptsCount > 0 ? (100 - $passPercentage) : 0;

        return [
            // Biểu đồ lên xuống kiểu chứng khoán
            'line' => [
                'labels'        => $lineLabels,
                'scores'        => $lineScores,
                'passThreshold' => $linePassThreshold,
                'pointColors'   => $pointColors,
                'pointRadii'    => $pointRadii,
                'meta'          => $lineTooltipsMeta,
                'ticker'        => $stockTicker,
            ],
            // Biểu đồ cột đa sắc theo chủ đề
            'topic' => [
                'labels'     => $topicLabels,
                'scores'     => $topicScores,
                'accuracies' => $topicAccuracies,
                'counts'     => $topicCounts,
                'colors'     => $topicColors,
                'passScore'  => $passScore,
            ],
            // Đồng hồ đo năng lực bán nguyệt
            'gauge' => [
                'avgScore'   => $avgScore,
                'passScore'  => $passScore,
                'status'     => $gaugeStatus,
            ],
            // Biểu đồ phân bổ đúng / sai
            'accuracy' => [
                'correct'    => $totalCorrect,
                'wrong'      => $totalWrong,
                'total'      => $totalQuestions,
                'rate'       => $accuracyRate,
            ],
            // Donut tổng quan kết quả xếp loại theo mốc 700đ
            'donut' => [
                'total'          => $totalAttemptsCount,
                'passed'         => $passedCount,
                'failed'         => $failedCount,
                'passPercentage' => $passPercentage,
                'failPercentage' => $failPercentage,
                'passScore'      => $passScore,
            ],
            // Radar tương thích
            'radar' => [
                'labels'    => $topicLabels,
                'scores'    => $topicScores,
                'passLine'  => $radarPassLine,
                'useRadar'  => $useRadar,
                'passScore' => $passScore,
            ],
            'efficiency' => $efficiencyItems,
        ];
    }

    /**
     * Calendar Heatmap 30 ngày (V4 NEW)
     */
    private function buildCalendarData(User $student): array
    {
        $tz   = config('learning.display_timezone', 'Asia/Ho_Chi_Minh');
        $days = config('learning.calendar_heatmap_days', 30);

        $recentAttempts = $student->attempts()
            ->whereNotNull('completed_at')
            ->where('completed_at', '>=', now()->subDays($days + 1))
            ->get();

        // Group theo ngày (GMT+7, không phải UTC)
        $byDate = $recentAttempts->groupBy(
            fn ($a) => $a->completed_at->setTimezone($tz)->format('Y-m-d')
        );

        $calendar = [];
        $todayStr = Carbon::now($tz)->format('Y-m-d');

        for ($i = $days - 1; $i >= 0; $i--) {
            $date     = Carbon::now($tz)->subDays($i)->format('Y-m-d');
            $dayItems = $byDate->get($date, collect());
            $count    = $dayItems->count();
            $avgScore = $count > 0 ? (int) round($dayItems->avg('score')) : 0;

            $calendar[] = [
                'date'       => $date,
                'dayLabel'   => Carbon::parse($date)->format('d'),
                'monthLabel' => Carbon::parse($date)->format('m'),
                'fullLabel'  => Carbon::parse($date, $tz)->locale('vi')->isoFormat('dddd, D/M'),
                'count'      => $count,
                'avgScore'   => $avgScore,
                'isToday'    => $date === $todayStr,
            ];
        }

        return $calendar;
    }
}
