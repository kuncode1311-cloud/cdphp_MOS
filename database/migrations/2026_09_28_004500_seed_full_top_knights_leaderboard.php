<?php

use App\Models\Classroom;
use App\Models\Level;
use App\Models\PracticeTest;
use App\Models\TestAttempt;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;

return new class extends Migration
{
    /**
     * Nạp đầy đủ toàn bộ Top Hiệp Sĩ (Hạng 4 - 10+) cho cả Khối 3, Khối 4 và Khối 5.
     * Đảm bảo hàng ngang "TOP HIỆP SĨ BÁM ĐUỔI" luôn lấp đầy trọn vẹn cả 7 ô (Hạng 4 đến Hạng 10),
     * không bị trống và có đầy đủ học sinh thi đua rực rỡ.
     */
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        $now = Carbon::now();

        // Lớp học
        $class3A1 = Classroom::where('grade', 3)->first();
        $class3A2 = Classroom::where('grade', 3)->skip(1)->first() ?? $class3A1;
        $class4A1 = Classroom::where('grade', 4)->first();
        $class4A2 = Classroom::where('grade', 4)->skip(1)->first() ?? $class4A1;
        $class5A1 = Classroom::where('grade', 5)->first();
        $class5A2 = Classroom::where('grade', 5)->skip(1)->first() ?? $class5A1;

        // Level
        $level1 = Level::where('grade', 3)->first();
        $level2 = Level::where('grade', 4)->first();
        $level3 = Level::where('grade', 5)->first();

        // Bài luyện
        $test3A = PracticeTest::whereHas('topic.level', fn ($q) => $q->where('grade', 3))->first();
        $test3B = PracticeTest::whereHas('topic.level', fn ($q) => $q->where('grade', 3))->skip(1)->first() ?? $test3A;
        $test4A = PracticeTest::whereHas('topic.level', fn ($q) => $q->where('grade', 4))->first();
        $test4B = PracticeTest::whereHas('topic.level', fn ($q) => $q->where('grade', 4))->skip(1)->first() ?? $test4A;
        $test5A = PracticeTest::whereHas('topic.level', fn ($q) => $q->where('grade', 5))->first();
        $test5B = PracticeTest::whereHas('topic.level', fn ($q) => $q->where('grade', 5))->skip(1)->first() ?? $test5A;

        // ==========================================
        // 1. KHỐI 3: TOP HIỆP SĨ ĐẦY ĐỦ TỪ HẠNG 1 ĐẾN HẠNG 14
        // ==========================================
        $grade3Students = [
            // Quán Quân (An Nhiên đã có trong DB)
            ['email' => 'trikun113@gmail.com', 'name' => 'An Nhiên', 'class_id' => $class3A1?->id, 'score' => 3530, 'attempts' => [950, 920, 880, 780]],
            // Á Quân
            ['email' => 'giahan.3a1@student.ic3.local', 'name' => 'Gia Hân', 'class_id' => $class3A1?->id, 'score' => 1650, 'attempts' => [850, 800]],
            // Hạng Ba
            ['email' => 'ducanh.3a1@student.ic3.local', 'name' => 'Đức Anh', 'class_id' => $class3A1?->id, 'score' => 1520, 'attempts' => [780, 740]],
            // Hạng 4 - 10 (LẤP ĐẦY 7 Ô BÁM ĐUỔI TRỰC DIỆN)
            ['email' => 'thaomy.3a2@student.ic3.local', 'name' => 'Thảo My', 'class_id' => $class3A2?->id, 'score' => 1420, 'attempts' => [720, 700]],
            ['email' => 'minhtriet.3a1@student.ic3.local', 'name' => 'Minh Triết', 'class_id' => $class3A1?->id, 'score' => 1360, 'attempts' => [700, 660]],
            ['email' => 'khanhlinh.3a1@student.ic3.local', 'name' => 'Khánh Linh', 'class_id' => $class3A1?->id, 'score' => 1280, 'attempts' => [660, 620]],
            ['email' => 'hoanglong.3a1@student.ic3.local', 'name' => 'Hoàng Long', 'class_id' => $class3A1?->id, 'score' => 1190, 'attempts' => [610, 580]],
            ['email' => 'tuankiet.3a1@student.ic3.local', 'name' => 'Tuấn Kiệt', 'class_id' => $class3A1?->id, 'score' => 1080, 'attempts' => [560, 520]],
            ['email' => 'baongoc.3a1@student.ic3.local', 'name' => 'Bảo Ngọc', 'class_id' => $class3A1?->id, 'score' => 990, 'attempts' => [510, 480]],
            ['email' => 'quynhanh.3a2@student.ic3.local', 'name' => 'Quỳnh Anh', 'class_id' => $class3A2?->id, 'score' => 910, 'attempts' => [470, 440]],
            // Hạng 11 - 14 (Dành cho Xem Tất Cả)
            ['email' => 'quangminh.3a1@student.ic3.local', 'name' => 'Quang Minh', 'class_id' => $class3A1?->id, 'score' => 850, 'attempts' => [850]],
            ['email' => 'phuonganh.3a1@student.ic3.local', 'name' => 'Phương Anh', 'class_id' => $class3A1?->id, 'score' => 780, 'attempts' => [780]],
            ['email' => 'dangkhoa.3a2@student.ic3.local', 'name' => 'Đăng Khoa', 'class_id' => $class3A2?->id, 'score' => 710, 'attempts' => [710]],
            ['email' => 'ngochan.3a2@student.ic3.local', 'name' => 'Ngọc Hân', 'class_id' => $class3A2?->id, 'score' => 650, 'attempts' => [650]],
        ];

        $this->seedRankingsForGrade($grade3Students, $level1, $test3A, $test3B, $now);

        // ==========================================
        // 2. KHỐI 4: TOP HIỆP SĨ ĐẦY ĐỦ TỪ HẠNG 1 ĐẾN HẠNG 12
        // ==========================================
        $grade4Students = [
            ['email' => 'bach.4a1@student.ic3.local', 'name' => 'Hoàng Bách', 'class_id' => $class4A1?->id, 'score' => 1850, 'attempts' => [950, 900]],
            ['email' => 'baonam.4a1@student.ic3.local', 'name' => 'Bảo Nam', 'class_id' => $class4A1?->id, 'score' => 1690, 'attempts' => [860, 830]],
            ['email' => 'diep.4a1@student.ic3.local', 'name' => 'Ngọc Diệp', 'class_id' => $class4A1?->id, 'score' => 1540, 'attempts' => [780, 760]],
            // Hạng 4 - 10
            ['email' => 'nhatnam.4a1@student.ic3.local', 'name' => 'Nhật Nam', 'class_id' => $class4A1?->id, 'score' => 1420, 'attempts' => [720, 700]],
            ['email' => 'linh.4a2@student.ic3.local', 'name' => 'Phương Linh', 'class_id' => $class4A2?->id, 'score' => 1310, 'attempts' => [670, 640]],
            ['email' => 'tri.4a2@student.ic3.local', 'name' => 'Minh Trí', 'class_id' => $class4A2?->id, 'score' => 1220, 'attempts' => [620, 600]],
            ['email' => 'thanhtruc.4a1@student.ic3.local', 'name' => 'Thanh Trúc', 'class_id' => $class4A1?->id, 'score' => 1140, 'attempts' => [580, 560]],
            ['email' => 'quocbao.4a1@student.ic3.local', 'name' => 'Quốc Bảo', 'class_id' => $class4A1?->id, 'score' => 1050, 'attempts' => [530, 520]],
            ['email' => 'minhkhang.4a2@student.ic3.local', 'name' => 'Minh Khang', 'class_id' => $class4A2?->id, 'score' => 980, 'attempts' => [500, 480]],
            ['email' => 'thuychi.4a2@student.ic3.local', 'name' => 'Thùy Chi', 'class_id' => $class4A2?->id, 'score' => 890, 'attempts' => [890]],
            // Hạng 11 - 12
            ['email' => 'giahuy.4a1@student.ic3.local', 'name' => 'Gia Huy', 'class_id' => $class4A1?->id, 'score' => 820, 'attempts' => [820]],
            ['email' => 'tueman.4a2@student.ic3.local', 'name' => 'Tuệ Mẫn', 'class_id' => $class4A2?->id, 'score' => 750, 'attempts' => [750]],
        ];

        $this->seedRankingsForGrade($grade4Students, $level2, $test4A, $test4B, $now);

        // ==========================================
        // 3. KHỐI 5: TOP HIỆP SĨ ĐẦY ĐỦ TỪ HẠNG 1 ĐẾN HẠNG 12
        // ==========================================
        $grade5Students = [
            ['email' => 'khoi.5a1@student.ic3.local', 'name' => 'Minh Khôi', 'class_id' => $class5A1?->id, 'score' => 1890, 'attempts' => [960, 930]],
            ['email' => 'hung.5a1@student.ic3.local', 'name' => 'Tuấn Hưng', 'class_id' => $class5A1?->id, 'score' => 1720, 'attempts' => [880, 840]],
            ['email' => 'maichi.5a2@student.ic3.local', 'name' => 'Mai Chi', 'class_id' => $class5A2?->id, 'score' => 1580, 'attempts' => [800, 780]],
            // Hạng 4 - 10
            ['email' => 'giabao.5a2@student.ic3.local', 'name' => 'Gia Bảo', 'class_id' => $class5A2?->id, 'score' => 1450, 'attempts' => [740, 710]],
            ['email' => 'dung.5a1@student.ic3.local', 'name' => 'Thùy Dung', 'class_id' => $class5A1?->id, 'score' => 1320, 'attempts' => [670, 650]],
            ['email' => 'baoanh.5a1@student.ic3.local', 'name' => 'Bảo Anh', 'class_id' => $class5A1?->id, 'score' => 1210, 'attempts' => [620, 590]],
            ['email' => 'huuphuoc.5a2@student.ic3.local', 'name' => 'Hữu Phước', 'class_id' => $class5A2?->id, 'score' => 1130, 'attempts' => [580, 550]],
            ['email' => 'yennhi.5a2@student.ic3.local', 'name' => 'Yến Nhi', 'class_id' => $class5A2?->id, 'score' => 1040, 'attempts' => [530, 510]],
            ['email' => 'thanhdat.5a1@student.ic3.local', 'name' => 'Thành Đạt', 'class_id' => $class5A1?->id, 'score' => 960, 'attempts' => [490, 470]],
            ['email' => 'phuongthao.5a2@student.ic3.local', 'name' => 'Phương Thảo', 'class_id' => $class5A2?->id, 'score' => 880, 'attempts' => [880]],
            // Hạng 11 - 12
            ['email' => 'namphong.5a1@student.ic3.local', 'name' => 'Nam Phong', 'class_id' => $class5A1?->id, 'score' => 810, 'attempts' => [810]],
            ['email' => 'baochau.5a2@student.ic3.local', 'name' => 'Bảo Châu', 'class_id' => $class5A2?->id, 'score' => 740, 'attempts' => [740]],
        ];

        $this->seedRankingsForGrade($grade5Students, $level3, $test5A, $test5B, $now);

        // Xóa sạch toàn bộ cache bảng vàng để hiển thị ngay tắp lự
        Cache::flush();
    }

    private function seedRankingsForGrade(array $students, ?Level $level, ?PracticeTest $testA, ?PracticeTest $testB, Carbon $now): void
    {
        if (! $testA) {
            return;
        }

        foreach ($students as $index => $item) {
            // Tìm theo email hoặc name
            $user = User::where('email', $item['email'])
                ->orWhere('name', $item['name'])
                ->first();

            if (! $user) {
                $user = User::create([
                    'name' => $item['name'],
                    'email' => $item['email'],
                    'student_code' => 'HS-K' . ($level?->grade ?? 3) . '-' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                    'role' => 'student',
                    'classroom_id' => $item['class_id'],
                    'reward_stars' => rand(1500, 12000),
                    'game_time_seconds' => rand(600, 3600),
                    'password' => bcrypt('12345678'),
                ]);
            } else {
                if ($item['class_id'] && ! $user->classroom_id) {
                    $user->update(['classroom_id' => $item['class_id']]);
                }
            }

            if ($level) {
                $user->accessibleLevels()->syncWithoutDetaching([$level->id]);
            }

            // Nếu là An Nhiên thì giữ nguyên attempts chuẩn đã tạo ở migration trước
            if ($user->email === 'trikun113@gmail.com' || str_contains($user->name, 'An Nhiên')) {
                continue;
            }

            // Xóa attempts trong tuần này của học sinh này để nạp điểm mới
            TestAttempt::where('user_id', $user->id)
                ->where('completed_at', '>=', $now->copy()->subDays(7))
                ->delete();

            // Nạp các lượt làm bài của học sinh này
            foreach ($item['attempts'] as $attIdx => $score) {
                if ($score <= 0) {
                    continue;
                }
                $targetTest = ($attIdx % 2 === 0) ? $testA : ($testB ?? $testA);
                TestAttempt::create([
                    'user_id' => $user->id,
                    'practice_test_id' => $targetTest->id,
                    'score' => $score,
                    'correct_answers' => (int) round($score / 1000 * 14),
                    'total_questions' => 14,
                    'duration_seconds' => rand(380, 680),
                    'completed_at' => $now->copy()->subDays(rand(1, 5))->subHours(rand(1, 10)),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
