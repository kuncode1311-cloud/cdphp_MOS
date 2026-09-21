<?php

namespace App\Http\Controllers;

use App\Models\GameSetting;
use App\Models\Level;
use App\Models\PracticeTest;
use App\Models\Program;
use App\Models\User;
use App\Services\ParentLearningAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller Không Gian Học Tập & Thi Luyện Của Học Sinh (Learning Controller)
 *
 * Chức năng: Điều hướng toàn bộ giao diện phía học sinh bao gồm:
 * 1. Trang chủ luyện thi (Home) & Danh sách chương trình (Programs).
 * 2. Lựa chọn khối lớp (Khối 3, 4, 5) và các chủ đề bài học.
 * 3. Màn hình chuẩn bị bài thi (Test overview) và Phòng thi trực tuyến sống động (Launch arena).
 * 4. Bảng thành tích học tập cá nhân (Achievements).
 */
class LearningController extends Controller
{
    /**
     * Helper lấy cấu hình 2 gói đổi giờ chơi
     */
    private function getGamePackages(): array
    {
        return [
            1 => [
                'package_id' => 1,
                'stars' => (int) GameSetting::get('pkg1_stars', 500),
                'minutes' => (int) GameSetting::get('pkg1_minutes', 3),
                'title' => (string) GameSetting::get('pkg1_title', 'Gói Khởi Động (3 Phút)'),
                'badge' => 'Tiết kiệm',
            ],
            2 => [
                'package_id' => 2,
                'stars' => (int) GameSetting::get('pkg2_stars', 1000),
                'minutes' => (int) GameSetting::get('pkg2_minutes', 7),
                'title' => (string) GameSetting::get('pkg2_title', 'Gói Siêu Hiệp Sĩ (7 Phút)'),
                'badge' => '🔥 Tặng thêm 1 phút',
            ],
        ];
    }

    /**
     * Trang chủ học tập của học sinh
     */
    public function home(): View|RedirectResponse
    {
        // Nếu là Admin thì chuyển hướng sang trang Quản trị
        if (request()->user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        $program = Program::with(['levels.topics.tests'])->firstOrFail();

        return view('learning.home', compact('program'));
    }

    /**
     * Danh sách toàn bộ chương trình luyện thi IC3
     */
    public function programs(): View
    {
        $program = Program::with(['levels.topics.tests'])->firstOrFail();

        return view('learning.programs', compact('program'));
    }

    /**
     * Màn hình Bảng thành tích học tập & Lịch sử điểm số của học sinh hiện tại
     */
    public function achievements(Request $request): View|JsonResponse
    {
        $user = $request->user() ?? auth()->user();
        $tz = config('learning.display_timezone', 'Asia/Ho_Chi_Minh');

        // Lấy mốc thời gian bắt đầu vòng thi đua & cấu hình quy tắc tính điểm từ GameSetting
        $startOfLeaderboard = GameSetting::getLeaderboardResetStart();
        $resetPeriod = GameSetting::getLeaderboardResetPeriod();
        $scoreMode = GameSetting::getLeaderboardScoreMode();
        $minPassScore = GameSetting::getLeaderboardMinPassScore();
        $nextReset = GameSetting::getLeaderboardNextReset();
        $nextResetTimestamp = $nextReset ? $nextReset->timestamp : null;

        // Tránh tình trạng đầu tuần mới (sáng Thứ Hai) chưa kịp có học sinh làm bài khiến Bảng Vàng bị trống Á Quân & Hạng Ba:
        // Nếu số bài làm trong vòng thi đua hiện tại còn quá ít (< 5 bài), tự động mở rộng mốc tính về 7 ngày gần nhất (rolling 7 days)
        // để luôn vinh danh những học sinh xuất sắc nhất.
        $recentAttemptsCount = \App\Models\TestAttempt::where('completed_at', '>=', $startOfLeaderboard)->count();
        if ($recentAttemptsCount < 5) {
            $rollingFallback = now($tz)->subDays(7)->setTimezone('UTC');
            if ($startOfLeaderboard->greaterThan($rollingFallback)) {
                $startOfLeaderboard = $rollingFallback;
            }
        }

        // Lấy bài làm trong vòng thi đua hiện tại
        $weeklyAttempts = $user->attempts()
            ->with('practiceTest.topic.level')
            ->where('completed_at', '>=', $startOfLeaderboard)
            ->get();

        $allAttempts = $user->attempts()->with('practiceTest.topic.level')->get();

        // Điểm thi đua của học sinh hiện tại dựa trên quy tắc tính điểm (chỉ tính bài đạt chuẩn hoặc tính tất cả)
        $userCountedWeekly = $scoreMode === 'passed_only'
            ? $weeklyAttempts->filter(fn ($a) => $a->score >= $minPassScore)
            : $weeklyAttempts;
        $weeklyScore = (int) $userCountedWeekly->sum('score');
        $weeklyPassed = (int) $weeklyAttempts->filter(fn ($a) => $a->score >= $minPassScore)->count();

        // Điểm Sao thưởng tích lũy & Thời gian chơi game
        $rewardStars = (int) ($user->reward_stars ?? 0);
        $gameTimeSeconds = (int) ($user->game_time_seconds ?? 0);
        $packages = $this->getGamePackages();

        // Top 3 bài thi cao điểm nhất đợt này của riêng bé
        $topWeeklyAttempts = $weeklyAttempts->sortByDesc('score')->take(3);

        // Khối lớp của học sinh hiện tại và bộ lọc khối được chọn
        $studentGrade = (int) ($user->classroom?->grade ?? $user->accessibleLevels->first()?->grade ?? 4);
        $selectedGrade = (string) $request->get('grade', (string) $studentGrade);

        // Bảng xếp hạng thi đua đua top vòng này
        $studentsQuery = User::where('role', 'student')->with(['classroom', 'accessibleLevels']);
        if ($selectedGrade !== 'all') {
            $gradeInt = (int) $selectedGrade;
            $studentsQuery->where(function ($q) use ($gradeInt) {
                $q->whereHas('classroom', fn ($c) => $c->where('grade', $gradeInt))
                  ->orWhereHas('accessibleLevels', fn ($l) => $l->where('grade', $gradeInt));
            });
        }

        $leaderboardStudents = $studentsQuery->with(['attempts' => function ($q) use ($startOfLeaderboard) {
            $q->where('completed_at', '>=', $startOfLeaderboard);
        }])->get();

        $leaderboard = $leaderboardStudents->map(function ($s) use ($user, $scoreMode, $minPassScore) {
            $weeklyAtts = $s->attempts;
            $passedAttempts = $weeklyAtts->filter(fn ($a) => $a->score >= $minPassScore);
            $countedAttempts = $scoreMode === 'passed_only' ? $passedAttempts : $weeklyAtts;

            $totalScore = (int) $countedAttempts->sum('score');
            $passedCount = (int) $passedAttempts->count();
            $testsCount = (int) $weeklyAtts->count();
            $bestScore = (int) ($weeklyAtts->max('score') ?? 0);

            return [
                'id' => $s->id,
                'name' => $s->name,
                'student_code' => $s->student_code,
                'classroom_name' => $s->classroom?->name ?? ('Khối ' . ($s->accessibleLevels->first()?->grade ?? 4)),
                'grade' => (int) ($s->classroom?->grade ?? $s->accessibleLevels->first()?->grade ?? 4),
                'weekly_score' => $totalScore,
                'passed_count' => $passedCount,
                'tests_count' => $testsCount,
                'best_score' => $bestScore,
                'reward_stars' => (int) ($s->reward_stars ?? 0),
                'is_me' => $s->id === $user->id,
            ];
        })->filter(fn ($item) => $item['tests_count'] > 0 || $item['is_me'])
          ->sortByDesc(fn ($item) => [
              $item['weekly_score'],
              $item['passed_count'],
              $item['tests_count'],
              $item['best_score'],
          ])->values();

        $myRank = null;
        $leaderboard = $leaderboard->map(function ($item, $index) use (&$myRank) {
            $item['rank'] = $index + 1;
            if ($item['is_me']) {
                $myRank = $item;
            }
            return $item;
        });

        $podiumStudents = $leaderboard->take(3);
        $rankingList = $leaderboard->slice(3, 7);

        // ⚡ NẾU LÀ YÊU CẦU AJAX ĐỔI TAB KHỐI LỚP ➔ TRẢ VỀ JSON RENDER MƯỢT MÀ KHÔNG RELOAD TRANG
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'selectedGrade' => $selectedGrade,
                'myRank' => $myRank,
                'nextResetTimestamp' => $nextResetTimestamp,
                'html' => view('learning.partials.leaderboard-content', compact(
                    'studentGrade',
                    'selectedGrade',
                    'leaderboard',
                    'podiumStudents',
                    'rankingList',
                    'myRank',
                    'weeklyScore',
                    'weeklyAttempts',
                    'resetPeriod',
                    'scoreMode',
                    'minPassScore',
                    'nextResetTimestamp'
                ))->render(),
            ]);
        }

        return view('learning.achievements', compact(
            'weeklyAttempts',
            'topWeeklyAttempts',
            'weeklyScore',
            'weeklyPassed',
            'rewardStars',
            'gameTimeSeconds',
            'packages',
            'allAttempts',
            'studentGrade',
            'selectedGrade',
            'leaderboard',
            'podiumStudents',
            'rankingList',
            'myRank',
            'resetPeriod',
            'scoreMode',
            'minPassScore',
            'nextResetTimestamp'
        ));
    }

    /**
     * Góc mini-game vừa chơi vừa học (Khu Trò Chơi)
     */
    public function games(): View
    {
        $user = request()->user() ?? auth()->user();
        $rewardStars = (int) ($user->reward_stars ?? 0);
        $gameTimeSeconds = (int) ($user->game_time_seconds ?? 0);
        $packages = $this->getGamePackages();

        return view('learning.games', compact('user', 'rewardStars', 'gameTimeSeconds', 'packages'));
    }

    /**
     * AJAX: Học sinh đổi Sao thưởng lấy thời gian chơi mini-game
     */
    public function exchangeGamePackage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'package' => 'required|integer|in:1,2',
        ]);

        $user = $request->user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập để đổi gói.'], 401);
        }

        $gameEnabled = (bool) GameSetting::get('game_enabled', true);
        if (! $gameEnabled && ! $user->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Khu trò chơi hiện đang tạm khóa để bảo trì.'], 403);
        }

        $result = $user->exchangeGamePackage((int) $validated['package']);

        return response()->json($result);
    }

    /**
     * AJAX: Lấy số giây chơi game còn lại của học sinh
     */
    public function getGameTime(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'game_enabled' => (bool) GameSetting::get('game_enabled', true),
            'game_time_seconds' => (int) ($user?->game_time_seconds ?? 0),
            'reward_stars' => (int) ($user?->reward_stars ?? 0),
            'user_name' => $user?->name ?? 'Hiệp sĩ IC3',
        ]);
    }

    /**
     * AJAX: Tiêu hao số giây chơi game khi đang chơi trong bao-ve-em-be.html
     */
    public function consumeGameTime(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'seconds' => 'required|integer|min:1|max:600',
        ]);

        $user = $request->user();
        if (! $user) {
            return response()->json(['success' => false, 'remaining_seconds' => 0], 401);
        }

        $remaining = $user->consumeGameTime((int) $validated['seconds']);

        return response()->json([
            'success' => true,
            'remaining_seconds' => $remaining,
        ]);
    }

    /**
     * Màn hình chi tiết của một Khối lớp (Hiển thị các Chủ đề và Danh sách bài luyện)
     */
    public function level(Level $level): View
    {
        abort_unless(auth()->user()->canAccessLevel($level), 403, 'Bạn chưa được cấp quyền truy cập Khối học này. Vui lòng liên hệ Thầy/Cô để mở khóa nhé!');

        $level->load(['program', 'topics.tests' => fn ($query) => $query->where('is_published', true)]);
        abort_unless($level->program->slug === 'ic3-gs6-primary', 404);

        return view('learning.level', compact('level'));
    }

    /**
     * Màn hình giới thiệu thông tin trước khi bắt đầu làm bài thi (Thời lượng, số câu, điểm chuẩn)
     */
    public function test(PracticeTest $practiceTest): View
    {
        abort_unless($practiceTest->is_published, 404);
        $practiceTest->load('topic.level.program');

        abort_unless(auth()->user()->canAccessLevel($practiceTest->topic->level_id), 403, 'Bạn chưa được cấp quyền truy cập bài luyện của Khối học này.');

        return view('learning.test', compact('practiceTest'));
    }

    /**
     * Phòng thi trực tuyến — Nạp câu hỏi và khởi động giao diện làm bài tương tác chuẩn IIG/IC3 Spark
     *
     * - Nạp danh sách câu hỏi đang phát hành từ CSDL.
     * - Chuyển câu hỏi thành DTO nội bộ an toàn cho giao diện làm bài.
     */
    public function launch(PracticeTest $practiceTest): View|\Illuminate\Http\RedirectResponse
    {
        $isAdmin = auth()->user()?->isAdmin();
        $adminAnswerKeys = [];
        if ($isAdmin) {
            $practiceTest->load(['topic.level', 'questions.options', 'questions.assets']);
        } else {
            abort_unless($practiceTest->is_published, 404);
            $practiceTest->load(['topic.level', 'questions' => fn ($query) => $query->where('is_published', true)->with(['options', 'assets'])]);
            abort_unless(auth()->user()->canAccessLevel($practiceTest->topic->level_id), 403, 'Bạn chưa được cấp quyền truy cập bài luyện của Khối học này.');
        }

        // Tự động kích hoạt nạp bộ câu hỏi nếu cơ sở dữ liệu trên máy chủ mới chưa có dữ liệu
        if ($practiceTest->questions->isEmpty()) {
            if (\App\Models\Question::count() === 0 && file_exists(database_path('data/ic3_questions_seed.json'))) {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
                $practiceTest->refresh();
                if ($isAdmin) {
                    $practiceTest->load(['topic.level', 'questions.options', 'questions.assets']);
                } else {
                    $practiceTest->load(['topic.level', 'questions' => fn ($query) => $query->where('is_published', true)->with(['options', 'assets'])]);
                }
            }
        }

        abort_if($practiceTest->questions->isEmpty(), 404, 'Bộ đề chưa có câu hỏi trong cơ sở dữ liệu.');


        $questionsCollection = $practiceTest->questions;
        if ($practiceTest->shuffle_questions) {
            $questionsCollection = $questionsCollection->shuffle();
        }

        if ($isAdmin) {
            foreach ($questionsCollection->values() as $index => $q) {
                $options = $q->options;
                if (in_array($q->type, ['MultipleChoice', 'MultipleResponse'], true)) {
                    $adminAnswerKeys[$index] = $options->filter(fn ($o) => (bool) $o->is_correct)->pluck('position')->sort()->values()->all();
                } elseif ($q->type === 'Matching') {
                    $adminAnswerKeys[$index] = $options->pluck('position', 'position')->all();
                } elseif ($q->type === 'MultipleChoiceText') {
                    $map = [];
                    foreach ($options as $optIdx => $option) {
                        $map[$optIdx] = (int) ($option->metadata['correct_index'] ?? -1);
                    }
                    $adminAnswerKeys[$index] = $map;
                } elseif ($q->type === 'Hotspot') {
                    $adminAnswerKeys[$index] = $options->firstWhere('is_correct', true)?->position ?? 0;
                } elseif ($q->type === 'Sequence') {
                    $adminAnswerKeys[$index] = $options->sortBy('position')->pluck('position')->values()->all();
                }
            }
        }

        $questions = $questionsCollection->map(function ($q) use ($practiceTest) {
            $data = $q->runtimeData();
            if ($practiceTest->shuffle_options && in_array($q->type, ['MultipleChoice', 'MultipleResponse'], true)) {
                $data['options'] = collect($data['options'])->shuffle()->values()->all();
            }

            return $data;
        })->values();

        return view('learning.launch', compact('practiceTest', 'questions', 'adminAnswerKeys'));
    }

    /**
     * Góc Phụ Huynh — Bảng điều khiển phân tích & theo dõi tiến độ học tập chi tiết của con
     *
     * - Dữ liệu thực tế 100% từ bảng test_attempts.
     * - Hệ thống biểu đồ đa dạng: Line (tiến độ theo thời gian), Radar (năng lực 7 chủ đề),
     *   Doughnut (tỷ lệ đạt), Bar (so sánh thời gian & điểm số).
     * - Bộ lọc thông minh theo mốc thời gian (7 ngày, 30 ngày, Tất cả) và Khối học.
     * - Cảnh báo thông minh: phát hiện bài luyện/chủ đề làm sai nhiều lần, điểm yếu cần bổ trợ.
     */
    public function parentDashboard(Request $request, ParentLearningAnalyticsService $analyticsService): View|JsonResponse
    {
        $student = $request->user();
        $availableStudents = collect();

        // Lấy danh sách Khối lớp cho bộ lọc
        $levels = Level::orderBy('grade')->get();

        // Xử lý bộ lọc
        $timeRange = $request->get('time_range', 'all');
        $selectedLevelId = $request->get('level_id', 'all');

        // Phân tích dữ liệu học tập qua Service
        $analytics = $analyticsService->getAnalytics($student, $timeRange, $selectedLevelId);

        // Nếu là yêu cầu AJAX (Realtime filter không load lại trang)
        if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'kpis' => $analytics['kpis'],
                'overviewInsight' => $analytics['overviewInsight'],
                'strengthsAndWeaknesses' => $analytics['strengthsAndWeaknesses'],
                'charts' => $analytics['charts'],
                'calendar' => $analytics['calendar'] ?? [],
                'careItemsHtml' => view('learning.partials.parent-alerts', [
                    'careItems' => $analytics['careItems'],
                    'timingObservations' => $analytics['timingObservations'],
                    'strengthsAndWeaknesses' => $analytics['strengthsAndWeaknesses'],
                ])->render(),
                'tableHtml' => view('learning.partials.parent-table', [
                    'attempts' => $analytics['attempts'],
                ])->render(),
                'attemptsCount' => $analytics['attempts']->count(),
            ]);
        }

        return view('learning.parent-dashboard', array_merge([
            'student' => $student,
            'availableStudents' => $availableStudents,
            'levels' => $levels,
        ], $analytics));
    }
}
