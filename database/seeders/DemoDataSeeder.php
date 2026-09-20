<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\GameTransaction;
use App\Models\Level;
use App\Models\PracticeTest;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $tz = config('learning.display_timezone', 'Asia/Ho_Chi_Minh');
        $now = now($tz);
        $startOfWeek = now($tz)->startOfWeek();

        $teacher1 = User::where('email', 'teacher@ic3.test')->first() ?? User::where('role', 'teacher')->first();
        $teacher2 = User::where('email', 'teacher4@ic3.test')->first() ?? $teacher1;
        $teacher3 = User::where('email', 'teacher5@ic3.test')->first() ?? $teacher1;

        $level3 = Level::where('grade', 3)->first();
        $level4 = Level::where('grade', 4)->first();
        $level5 = Level::where('grade', 5)->first();

        // 1. Tạo và lấy các lớp học cho Khối 3, 4, 5
        $class3A1 = Classroom::firstOrCreate(['name' => 'Lớp 3A1'], ['grade' => 3, 'school_year' => '2026-2027', 'teacher_id' => $teacher1->id]);
        $class3A2 = Classroom::firstOrCreate(['name' => 'Lớp 3A2'], ['grade' => 3, 'school_year' => '2026-2027', 'teacher_id' => $teacher1->id]);
        $class4A1 = Classroom::firstOrCreate(['name' => 'Lớp 4A1'], ['grade' => 4, 'school_year' => '2026-2027', 'teacher_id' => $teacher2->id]);
        $class4A2 = Classroom::firstOrCreate(['name' => 'Lớp 4A2'], ['grade' => 4, 'school_year' => '2026-2027', 'teacher_id' => $teacher2->id]);
        $class5A1 = Classroom::firstOrCreate(['name' => 'Lớp 5A1'], ['grade' => 5, 'school_year' => '2026-2027', 'teacher_id' => $teacher3->id]);
        $class5A2 = Classroom::firstOrCreate(['name' => 'Lớp 5A2'], ['grade' => 5, 'school_year' => '2026-2027', 'teacher_id' => $teacher3->id]);

        // 2. Danh sách học sinh mẫu cần bổ sung (Khối 3, 4, 5)
        $newStudents = [
            // Khối 3
            ['name' => 'Gia Hân', 'email' => 'giahan.3a1@student.ic3.local', 'code' => 'HS010', 'class' => $class3A1, 'level' => $level3, 'teacher' => $teacher1],
            ['name' => 'Đức Anh', 'email' => 'ducanh.3a1@student.ic3.local', 'code' => 'HS011', 'class' => $class3A1, 'level' => $level3, 'teacher' => $teacher1],
            ['name' => 'Thảo My', 'email' => 'thaomy.3a2@student.ic3.local', 'code' => 'HS012', 'class' => $class3A2, 'level' => $level3, 'teacher' => $teacher1],
            ['name' => 'Quỳnh Anh', 'email' => 'quynhanh.3a2@student.ic3.local', 'code' => 'HS013', 'class' => $class3A2, 'level' => $level3, 'teacher' => $teacher1],
            // Khối 4
            ['name' => 'Hoàng Bách', 'email' => 'bach.4a1@student.ic3.local', 'code' => 'HS030', 'class' => $class4A1, 'level' => $level4, 'teacher' => $teacher2],
            ['name' => 'Ngọc Diệp', 'email' => 'diep.4a1@student.ic3.local', 'code' => 'HS031', 'class' => $class4A1, 'level' => $level4, 'teacher' => $teacher2],
            ['name' => 'Phương Linh', 'email' => 'linh.4a2@student.ic3.local', 'code' => 'HS032', 'class' => $class4A2, 'level' => $level4, 'teacher' => $teacher2],
            ['name' => 'Minh Trí', 'email' => 'tri.4a2@student.ic3.local', 'code' => 'HS033', 'class' => $class4A2, 'level' => $level4, 'teacher' => $teacher2],
            // Khối 5
            ['name' => 'Thùy Dung', 'email' => 'dung.5a1@student.ic3.local', 'code' => 'HS040', 'class' => $class5A1, 'level' => $level5, 'teacher' => $teacher3],
            ['name' => 'Tuấn Hưng', 'email' => 'hung.5a1@student.ic3.local', 'code' => 'HS041', 'class' => $class5A1, 'level' => $level5, 'teacher' => $teacher3],
            ['name' => 'Mai Chi', 'email' => 'maichi.5a2@student.ic3.local', 'code' => 'HS042', 'class' => $class5A2, 'level' => $level5, 'teacher' => $teacher3],
            ['name' => 'Gia Bảo', 'email' => 'giabao.5a2@student.ic3.local', 'code' => 'HS043', 'class' => $class5A2, 'level' => $level5, 'teacher' => $teacher3],
        ];

        foreach ($newStudents as $ns) {
            $user = User::firstOrCreate(
                ['student_code' => $ns['code']],
                [
                    'name' => $ns['name'],
                    'email' => $ns['email'],
                    'password' => '123456',
                    'role' => 'student',
                    'classroom_id' => $ns['class']?->id,
                    'created_by' => $ns['teacher']?->id,
                    'reward_stars' => 0,
                    'game_time_seconds' => 0,
                ]
            );
            if ($ns['level']) {
                $user->accessibleLevels()->syncWithoutDetaching([$ns['level']->id]);
            }
        }

        // 3. Chuẩn hóa tất cả học sinh hiện có: gắn lớp & level tương ứng
        $allStudents = User::where('role', 'student')->get();
        foreach ($allStudents as $idx => $st) {
            if (! $st->classroom_id) {
                $chosenClass = match ($idx % 3) {
                    0 => $class3A1,
                    1 => $class4A1,
                    default => $class5A1,
                };
                $st->classroom_id = $chosenClass->id;
                $st->save();
            }

            $grade = $st->classroom?->grade ?? 4;
            $level = match ($grade) {
                3 => $level3,
                4 => $level4,
                default => $level5,
            };
            if ($level) {
                $st->accessibleLevels()->syncWithoutDetaching([$level->id]);
            }
        }

        // 4. Lấy danh sách đề thi theo từng khối
        $testsByGrade = [];
        if ($level3) $testsByGrade[3] = PracticeTest::whereHas('topic', fn ($q) => $q->where('level_id', $level3->id))->with('topic')->get();
        if ($level4) $testsByGrade[4] = PracticeTest::whereHas('topic', fn ($q) => $q->where('level_id', $level4->id))->with('topic')->get();
        if ($level5) $testsByGrade[5] = PracticeTest::whereHas('topic', fn ($q) => $q->where('level_id', $level5->id))->with('topic')->get();

        // 5. Tạo dữ liệu bài làm thi đua đợt hiện tại cho từng học sinh
        $leaderboardStart = \App\Models\GameSetting::getLeaderboardResetStart();
        $startVn = $leaderboardStart->copy()->setTimezone($tz);
        $daysDiff = max(1, (int) $now->diffInDays($startVn));

        foreach ($allStudents as $stIdx => $st) {
            $grade = $st->classroom?->grade ?? 4;
            $tests = $testsByGrade[$grade] ?? collect();
            if ($tests->isEmpty()) continue;

            // Xóa attempts cũ của vòng hiện tại nếu có để seed lại đều đẹp
            $st->attempts()->where('completed_at', '>=', $leaderboardStart)->delete();

            // Số bài thi trong vòng này: từ 2 đến 5 bài (tạo thứ hạng phong phú cho Top 1, 2, 3 và Top 4-10)
            $attemptCount = match ($stIdx % 6) {
                0 => 4, // Top Quán quân
                1 => 3, // Á quân
                2 => 3, // Hạng ba
                3 => 2,
                4 => 2,
                default => 1,
            };

            $totalEarnedThisPeriod = 0;

            for ($i = 0; $i < $attemptCount; $i++) {
                $test = $tests->get($i % $tests->count());
                // Điểm số ngẫu nhiên phong phú
                $scorePool = [1000, 950, 900, 850, 800, 750, 700];
                $score = $scorePool[($stIdx * 2 + $i) % count($scorePool)];
                $totalQ = 10;
                $correctQ = (int) round($score / 1000 * $totalQ);
                $duration = rand(150, 480);
                $daysAgo = rand(0, min($daysDiff, 3));
                $completedAt = $now->copy()->subDays($daysAgo)->subHours(rand(1, 12))->subMinutes(rand(5, 55));
                if ($completedAt->lessThan($startVn)) {
                    $completedAt = $now->copy()->subHours(rand(1, 6));
                }

                $st->attempts()->create([
                    'practice_test_id' => $test->id,
                    'score' => $score,
                    'correct_answers' => $correctQ,
                    'total_questions' => $totalQ,
                    'duration_seconds' => $duration,
                    'completed_at' => $completedAt->setTimezone('UTC'),
                ]);

                $totalEarnedThisPeriod += $score;
            }

            // Cập nhật reward_stars và game_time_seconds
            $rewardStars = $totalEarnedThisPeriod + rand(300, 1200);
            $gameTime = rand(2, 6) * 60; // 2 - 6 phút

            $st->reward_stars = $rewardStars;
            $st->game_time_seconds = $gameTime;
            $st->save();

            // Ghi nhật ký giao dịch mẫu
            $st->gameTransactions()->create([
                'type' => 'earn',
                'stars_change' => $totalEarnedThisPeriod,
                'time_seconds_change' => 0,
                'description' => "Hoàn thành {$attemptCount} bài luyện thi IC3 vòng này",
                'created_at' => $now->copy()->subHours(2),
            ]);

            if ($stIdx % 2 === 0) {
                $st->gameTransactions()->create([
                    'type' => 'exchange',
                    'stars_change' => -500,
                    'time_seconds_change' => 180,
                    'description' => 'Đổi Gói Khởi Động (500 ⭐ ➔ 3 phút chơi)',
                    'created_at' => $now->copy()->subHours(1),
                ]);
            }
        }
    }
}
