<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\GameSetting;
use App\Models\Level;
use App\Models\PracticeTest;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    /**
     * Sinh dữ liệu demo phong phú, có chiều sâu lịch sử:
     *   - Mỗi học sinh có 10-18 bài làm trải rộng 3 tuần
     *   - Điểm số theo hướng cải thiện dần (mô phỏng tiến độ thực)
     *   - Sao thưởng, giao dịch, thời gian chơi game đầy đủ
     *   - Bảng xếp hạng có dữ liệu rõ ràng Top 1, 2, 3
     */
    public function run(): void
    {
        $tz  = config('learning.display_timezone', 'Asia/Ho_Chi_Minh');
        $now = now($tz);

        // --- Giáo viên ---
        $teacher1 = User::where('email', 'teacher@ic3.test')->first()  ?? User::where('role', 'teacher')->first();
        $teacher2 = User::where('email', 'teacher4@ic3.test')->first() ?? $teacher1;
        $teacher3 = User::where('email', 'teacher5@ic3.test')->first() ?? $teacher1;

        // --- Khối lớp ---
        $level3 = Level::where('grade', 3)->first();
        $level4 = Level::where('grade', 4)->first();
        $level5 = Level::where('grade', 5)->first();

        // --- Lớp học (firstOrCreate để idempotent) ---
        $class3A1 = Classroom::firstOrCreate(['name' => 'Lớp 3A1'], ['grade' => 3, 'school_year' => '2026-2027', 'teacher_id' => $teacher1?->id]);
        $class3A2 = Classroom::firstOrCreate(['name' => 'Lớp 3A2'], ['grade' => 3, 'school_year' => '2026-2027', 'teacher_id' => $teacher1?->id]);
        $class4A1 = Classroom::firstOrCreate(['name' => 'Lớp 4A1'], ['grade' => 4, 'school_year' => '2026-2027', 'teacher_id' => $teacher2?->id]);
        $class4A2 = Classroom::firstOrCreate(['name' => 'Lớp 4A2'], ['grade' => 4, 'school_year' => '2026-2027', 'teacher_id' => $teacher2?->id]);
        $class5A1 = Classroom::firstOrCreate(['name' => 'Lớp 5A1'], ['grade' => 5, 'school_year' => '2026-2027', 'teacher_id' => $teacher3?->id]);
        $class5A2 = Classroom::firstOrCreate(['name' => 'Lớp 5A2'], ['grade' => 5, 'school_year' => '2026-2027', 'teacher_id' => $teacher3?->id]);

        // --- Học sinh bổ sung cho đủ 3 khối ---
        $extraStudents = [
            // Lớp 3A1
            ['name' => 'Lê Thảo My',     'email' => 'thaomy.3a1@student.ic3.local',   'code' => 'HS004', 'class' => $class3A1, 'level' => $level3, 'teacher' => $teacher1],
            ['name' => 'Phạm Tuấn Kiệt', 'email' => 'tuankiet.3a1@student.ic3.local', 'code' => 'HS005', 'class' => $class3A1, 'level' => $level3, 'teacher' => $teacher1],
            ['name' => 'Vũ Quỳnh Anh',   'email' => 'quynhanh.3a1@student.ic3.local', 'code' => 'HS006', 'class' => $class3A1, 'level' => $level3, 'teacher' => $teacher1],
            ['name' => 'Đỗ Hoàng Long',  'email' => 'hoanglong.3a1@student.ic3.local','code' => 'HS007', 'class' => $class3A1, 'level' => $level3, 'teacher' => $teacher1],
            ['name' => 'Bùi Khánh Linh', 'email' => 'khanhlinh.3a1@student.ic3.local','code' => 'HS008', 'class' => $class3A1, 'level' => $level3, 'teacher' => $teacher1],
            ['name' => 'Hoàng Quang Minh','email' => 'quangminh.3a1@student.ic3.local','code' => 'HS009','class' => $class3A1, 'level' => $level3, 'teacher' => $teacher1],
            ['name' => 'Ngô Bảo Ngọc',   'email' => 'baongoc.3a1@student.ic3.local',  'code' => 'HS010', 'class' => $class3A1, 'level' => $level3, 'teacher' => $teacher1],
            ['name' => 'Đinh Minh Triết', 'email' => 'minhtriet.3a1@student.ic3.local','code' => 'HS011', 'class' => $class3A1, 'level' => $level3, 'teacher' => $teacher1],
            ['name' => 'Phan Phương Anh', 'email' => 'phuonganh.3a1@student.ic3.local','code' => 'HS012', 'class' => $class3A1, 'level' => $level3, 'teacher' => $teacher1],
            // Lớp 3A2
            ['name' => 'Quỳnh Anh',      'email' => 'quynhanh.3a2@student.ic3.local', 'code' => 'HS013', 'class' => $class3A2, 'level' => $level3, 'teacher' => $teacher1],
            // Lớp 4A1
            ['name' => 'Võ Nhật Nam',    'email' => 'nhatnam.4a1@student.ic3.local',  'code' => 'HS020', 'class' => $class4A1, 'level' => $level4, 'teacher' => $teacher2],
            ['name' => 'Lý Thanh Trúc',  'email' => 'thanhtruc.4a1@student.ic3.local','code' => 'HS021', 'class' => $class4A1, 'level' => $level4, 'teacher' => $teacher2],
            ['name' => 'Dương Quốc Bảo', 'email' => 'quocbao.4a1@student.ic3.local',  'code' => 'HS022', 'class' => $class4A1, 'level' => $level4, 'teacher' => $teacher2],
            ['name' => 'Hoàng Bách',     'email' => 'bach.4a1@student.ic3.local',     'code' => 'HS030', 'class' => $class4A1, 'level' => $level4, 'teacher' => $teacher2],
            ['name' => 'Ngọc Diệp',      'email' => 'diep.4a1@student.ic3.local',     'code' => 'HS031', 'class' => $class4A1, 'level' => $level4, 'teacher' => $teacher2],
            // Lớp 4A2
            ['name' => 'Phương Linh',    'email' => 'linh.4a2@student.ic3.local',     'code' => 'HS032', 'class' => $class4A2, 'level' => $level4, 'teacher' => $teacher2],
            ['name' => 'Minh Trí',       'email' => 'tri.4a2@student.ic3.local',      'code' => 'HS033', 'class' => $class4A2, 'level' => $level4, 'teacher' => $teacher2],
            // Lớp 5A1
            ['name' => 'Thùy Dung',     'email' => 'dung.5a1@student.ic3.local',     'code' => 'HS040', 'class' => $class5A1, 'level' => $level5, 'teacher' => $teacher3],
            ['name' => 'Tuấn Hưng',     'email' => 'hung.5a1@student.ic3.local',     'code' => 'HS041', 'class' => $class5A1, 'level' => $level5, 'teacher' => $teacher3],
            // Lớp 5A2
            ['name' => 'Mai Chi',       'email' => 'maichi.5a2@student.ic3.local',   'code' => 'HS042', 'class' => $class5A2, 'level' => $level5, 'teacher' => $teacher3],
            ['name' => 'Gia Bảo',       'email' => 'giabao.5a2@student.ic3.local',   'code' => 'HS043', 'class' => $class5A2, 'level' => $level5, 'teacher' => $teacher3],
        ];

        foreach ($extraStudents as $ns) {
            $user = User::firstOrCreate(
                ['student_code' => $ns['code']],
                [
                    'name'              => $ns['name'],
                    'email'             => $ns['email'],
                    'password'          => '123456',
                    'role'              => 'student',
                    'classroom_id'      => $ns['class']?->id,
                    'created_by'        => $ns['teacher']?->id,
                    'reward_stars'      => 0,
                    'game_time_seconds' => 0,
                ]
            );
            if ($ns['level']) {
                $user->accessibleLevels()->syncWithoutDetaching([$ns['level']->id]);
            }
        }

        // --- Chuẩn hoá: gắn lớp & level cho TẤT CẢ học sinh chưa có ---
        $allStudents = User::where('role', 'student')->with('classroom')->get();
        foreach ($allStudents as $idx => $st) {
            if (!$st->classroom_id) {
                $st->classroom_id = match ($idx % 3) {
                    0 => $class3A1->id,
                    1 => $class4A1->id,
                    default => $class5A1->id,
                };
                $st->save();
            }
            $grade = $st->classroom?->grade ?? 3;
            $level = match ($grade) { 3 => $level3, 4 => $level4, default => $level5 };
            if ($level) {
                $st->accessibleLevels()->syncWithoutDetaching([$level->id]);
            }
        }

        // --- Lấy bộ đề theo từng khối ---
        $testsByGrade = [];
        if ($level3) $testsByGrade[3] = PracticeTest::whereHas('topic', fn ($q) => $q->where('level_id', $level3->id))->with('topic')->get();
        if ($level4) $testsByGrade[4] = PracticeTest::whereHas('topic', fn ($q) => $q->where('level_id', $level4->id))->with('topic')->get();
        if ($level5) $testsByGrade[5] = PracticeTest::whereHas('topic', fn ($q) => $q->where('level_id', $level5->id))->with('topic')->get();

        // --- Pool điểm số có tính "cải thiện theo thời gian" ---
        // Index 0 = bài đầu tiên (xa nhất), cuối = bài gần nhất (cao hơn)
        $scoreProgression = [
            // Học sinh xuất sắc (stIdx % 5 == 0): điểm cao ổn định
            'excellent' => [800, 850, 900, 850, 950, 900, 1000, 950, 1000, 950, 1000, 900, 1000, 950, 1000, 1000],
            // Học sinh tiến bộ (stIdx % 5 == 1): điểm tăng dần
            'improving' => [700, 750, 700, 800, 750, 850, 800, 850, 900, 850, 900, 950, 900, 950, 1000],
            // Học sinh ổn định (stIdx % 5 == 2)
            'steady'    => [800, 850, 800, 900, 850, 800, 900, 850, 900, 800, 850, 900, 950, 850, 900],
            // Học sinh trung bình (stIdx % 5 == 3)
            'average'   => [600, 700, 650, 700, 750, 700, 800, 750, 700, 800, 850, 800, 900],
            // Học sinh cần cố gắng (stIdx % 5 == 4): có 1 bài thất bại (71đ ~ nhấp nhầm nộp sớm)
            'struggling'=> [71, 700, 650, 750, 700, 800, 750, 800, 850, 800, 900, 850],
        ];

        $leaderboardStart = GameSetting::getLeaderboardResetStart();

        $allStudents = $allStudents->sortBy('id')->values(); // đảm bảo thứ tự ổn định

        foreach ($allStudents as $stIdx => $st) {
            $grade = $st->classroom?->grade ?? 3;
            $tests = $testsByGrade[$grade] ?? collect();
            if ($tests->isEmpty()) continue;

            // Xóa toàn bộ attempts cũ để seed lại sạch sẽ
            $st->attempts()->delete();
            $st->gameTransactions()->delete();

            // Chọn "profile điểm số" theo chỉ số học sinh
            $profileKey = match ($stIdx % 5) {
                0 => 'excellent',
                1 => 'improving',
                2 => 'steady',
                3 => 'average',
                default => 'struggling',
            };
            $scores = $scoreProgression[$profileKey];

            // Số bài làm: 10-16 bài, trải 3 tuần (21 ngày)
            $totalAttempts = count($scores);
            $totalStarsEarned = 0;

            foreach ($scores as $i => $score) {
                $test = $tests->get($i % $tests->count());

                // Phân bổ thời gian: bài cũ nhất = 21 ngày trước, bài mới nhất = 0-2 ngày
                $daysAgo = (int) round(($totalAttempts - 1 - $i) / ($totalAttempts - 1) * 21);
                $completedAt = $now->copy()
                    ->subDays($daysAgo)
                    ->subHours(rand(0, 8))
                    ->subMinutes(rand(5, 58));

                // Số câu đúng tỷ lệ theo điểm (14 câu với bài 71đ, 10 câu với bài bình thường)
                $totalQ   = ($score <= 100) ? 14 : 10;
                $correctQ = ($score <= 100)
                    ? (int) round($score / 1000 * $totalQ)
                    : (int) round($score / 1000 * $totalQ);
                $duration = rand(150, 520);

                $st->attempts()->create([
                    'practice_test_id' => $test->id,
                    'score'            => $score,
                    'correct_answers'  => $correctQ,
                    'total_questions'  => $totalQ,
                    'duration_seconds' => $duration,
                    'completed_at'     => $completedAt->setTimezone('UTC'),
                ]);

                // Chỉ tính sao cho bài đạt mốc 700
                if ($score >= 700) {
                    $totalStarsEarned += $score;
                }
            }

            // Số bài trong vòng xếp hạng hiện tại (cho Leaderboard)
            $attemptsThisPeriod = $st->attempts()
                ->where('completed_at', '>=', $leaderboardStart)
                ->count();

            // Reward stars = tổng sao kiếm được + bonus ngẫu nhiên nhỏ
            $bonusStars = ($stIdx % 3 === 0) ? rand(800, 1500) : rand(200, 800);
            $st->reward_stars      = $totalStarsEarned + $bonusStars;
            $st->game_time_seconds = rand(1, 6) * 60; // 1-6 phút game time còn lại
            $st->save();

            // Giao dịch sao kiếm được
            $st->gameTransactions()->create([
                'type'               => 'earn',
                'stars_change'       => $totalStarsEarned,
                'time_seconds_change'=> 0,
                'description'        => "Tích lũy từ {$totalAttempts} bài luyện thi IC3 ({$attemptsThisPeriod} bài trong vòng này)",
                'created_at'         => $now->copy()->subDays(1),
            ]);

            // Một số học sinh đã đổi sao lấy giờ chơi game
            if ($stIdx % 2 === 0 && $st->reward_stars >= 500) {
                $st->gameTransactions()->create([
                    'type'               => 'exchange',
                    'stars_change'       => -500,
                    'time_seconds_change'=> 180,
                    'description'        => 'Đổi Gói Khởi Động (500 ⭐ ➔ 3 phút chơi)',
                    'created_at'         => $now->copy()->subHours(rand(3, 18)),
                ]);
            }

            // Học sinh xuất sắc đổi gói cao hơn
            if ($profileKey === 'excellent' && $st->reward_stars >= 1000) {
                $st->gameTransactions()->create([
                    'type'               => 'exchange',
                    'stars_change'       => -1000,
                    'time_seconds_change'=> 420,
                    'description'        => 'Đổi Gói Siêu Hiệp Sĩ (1000 ⭐ ➔ 7 phút chơi)',
                    'created_at'         => $now->copy()->subHours(rand(1, 6)),
                ]);
            }
        }
    }
}
