<?php

use App\Models\GameSetting;
use App\Models\PracticeTest;
use App\Models\TestAttempt;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;

return new class extends Migration
{
    /**
     * Nạp bài làm mẫu cho tháng hiện tại để Bảng Vàng theo tháng có đủ Quán quân, Á quân, Hạng Ba và Top 4-10
     * ở cả Khối 3, 4, 5. Chỉ dùng tài khoản học sinh mẫu (@student.ic3.local), chạy lặp lại không nạp trùng.
     */
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        $tz = config('learning.display_timezone', 'Asia/Ho_Chi_Minh');
        $now = now($tz);
        $monthStart = $now->copy()->startOfMonth();
        $marker = 'seed_month_attempts_'.$now->format('Ym');

        if (GameSetting::get($marker)) {
            return;
        }

        mt_srand((int) $now->format('Ym')); // Dữ liệu ổn định giữa các môi trường

        foreach ([3, 4, 5] as $grade) {
            $tests = PracticeTest::whereHas('topic.level', fn ($q) => $q->where('grade', $grade))->orderBy('id')->take(4)->get();
            if ($tests->isEmpty()) {
                continue;
            }

            $students = User::where('role', 'student')
                ->where('email', 'like', '%@student.ic3.local')
                ->where(function ($q) use ($grade) {
                    $q->whereHas('classroom', fn ($c) => $c->where('grade', $grade))
                        ->orWhereHas('accessibleLevels', fn ($l) => $l->where('grade', $grade));
                })
                ->orderBy('id')
                ->take(12)
                ->get();

            foreach ($students as $index => $student) {
                // Hạng càng cao điểm càng lớn: từ khoảng 980 giảm dần, luôn trên mức đạt chuẩn 700
                $bestScore = max(720, 980 - $index * 22);
                $attemptCount = $index < 3 ? 3 : 2;

                for ($i = 0; $i < $attemptCount; $i++) {
                    $test = $tests[($index + $i) % $tests->count()];
                    $score = max(700, $bestScore - $i * mt_rand(20, 60));
                    $total = max(1, (int) ($test->question_count ?: 10));
                    // Rải bài làm đều từ đầu tháng đến hiện tại
                    $completedAt = $monthStart->copy()->addSeconds(mt_rand(3600, max(3601, $monthStart->diffInSeconds($now) - 600)));

                    if ($completedAt->greaterThan($now)) {
                        $completedAt = $now->copy()->subMinutes(5); // Không để bài làm rơi vào tương lai
                    }

                    TestAttempt::create([
                        'user_id' => $student->id,
                        'practice_test_id' => $test->id,
                        'score' => $score,
                        'correct_answers' => (int) round($score / 1000 * $total),
                        'total_questions' => $total,
                        'duration_seconds' => mt_rand(240, 900),
                        'completed_at' => $completedAt->setTimezone('UTC'),
                    ]);
                }
            }
        }

        GameSetting::set($marker, '1', 'Đã nạp bài làm mẫu cho Bảng Vàng tháng '.$now->format('m/Y'));
        Cache::flush();
    }

    public function down(): void
    {
        // Không xóa dữ liệu thi đua khi rollback
    }
};
