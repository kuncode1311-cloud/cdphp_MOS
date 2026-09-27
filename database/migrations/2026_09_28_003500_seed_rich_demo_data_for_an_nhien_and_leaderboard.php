<?php

use App\Models\Classroom;
use App\Models\Level;
use App\Models\PracticeTest;
use App\Models\Question;
use App\Models\StudentMistake;
use App\Models\TestAttempt;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Nạp dữ liệu phong phú, sống động cho học sinh An Nhiên (Lớp 3A1)
     * và Bảng Vàng Đua Top các Khối lớp để sẵn sàng cho buổi demo đồ án.
     */
    public function up(): void
    {
        // Bỏ qua khi đang chạy test suite tự động với SQLite in-memory
        if (app()->environment('testing') || app()->runningUnitTests() || config('database.default') === 'sqlite') {
            return;
        }

        // 1. Xác định hoặc chuẩn hóa tài khoản học sinh An Nhiên
        $anNhien = User::where('email', 'trikun113@gmail.com')
            ->orWhere('name', 'like', '%An Nhiên%')
            ->first();

        if (! $anNhien) {
            $class3A1 = Classroom::where('grade', 3)->first();
            $anNhien = User::create([
                'name' => 'An Nhiên',
                'email' => 'trikun113@gmail.com',
                'student_code' => 'HS-3A1-001',
                'role' => 'student',
                'classroom_id' => $class3A1?->id ?? 1,
                'reward_stars' => 16354,
                'game_time_seconds' => 1800,
                'password' => bcrypt('12345678'),
            ]);
        } else {
            $class3A1 = Classroom::where('grade', 3)->first();
            $anNhien->update([
                'classroom_id' => $anNhien->classroom_id ?: ($class3A1?->id ?? 1),
                'reward_stars' => max((int) $anNhien->reward_stars, 16354),
                'game_time_seconds' => max((int) $anNhien->game_time_seconds, 1800),
            ]);
        }

        // Cấp quyền mở khóa cả 3 Khối học cho An Nhiên để demo mọi màn hình
        $allLevelIds = Level::pluck('id')->all();
        if (! empty($allLevelIds)) {
            $anNhien->accessibleLevels()->syncWithoutDetaching($allLevelIds);
        }

        // Lấy các bài luyện của Khối 3, 4, 5
        $testsGrade3 = PracticeTest::whereHas('topic.level', fn ($q) => $q->where('grade', 3))->get();
        $testsGrade4 = PracticeTest::whereHas('topic.level', fn ($q) => $q->where('grade', 4))->get();
        $testsGrade5 = PracticeTest::whereHas('topic.level', fn ($q) => $q->where('grade', 5))->get();

        $primaryTest3A = $testsGrade3->first();
        $primaryTest3B = $testsGrade3->skip(1)->first() ?? $primaryTest3A;
        $primaryTest4 = $testsGrade4->first();
        $primaryTest5 = $testsGrade5->first();

        $now = Carbon::now();

        // 2. TẠO SỔ TAY CÂU SAI PHONG PHÚ CHO AN NHIÊN (Đang sai, Báo động đỏ, Đã vượt qua)
        if ($primaryTest3A) {
            $questions3 = Question::where('practice_test_id', $primaryTest3A->id)->take(12)->get();
            if ($questions3->count() >= 8) {
                // Xóa câu sai cũ của An Nhiên nếu có để nạp bộ chuẩn demo đẹp nhất
                StudentMistake::where('user_id', $anNhien->id)->delete();

                // 2.1. CÂU BÁO ĐỘNG ĐỎ (Sai >= 2 lần, cần ôn gấp - 2 câu)
                StudentMistake::create([
                    'user_id' => $anNhien->id,
                    'question_id' => $questions3[4]->id, // Câu 5: Phần cứng / phần mềm
                    'practice_test_id' => $primaryTest3A->id,
                    'wrong_count' => 3,
                    'correct_count' => 0,
                    'last_student_answer' => null,
                    'status' => 'unresolved',
                    'last_wrong_at' => $now->copy()->subHours(2),
                    'last_resolved_at' => null,
                ]);

                StudentMistake::create([
                    'user_id' => $anNhien->id,
                    'question_id' => $questions3[5]->id, // Câu 6: Thao tác thanh tác vụ
                    'practice_test_id' => $primaryTest3A->id,
                    'wrong_count' => 2,
                    'correct_count' => 0,
                    'last_student_answer' => [0],
                    'status' => 'unresolved',
                    'last_wrong_at' => $now->copy()->subHours(6),
                    'last_resolved_at' => null,
                ]);

                // 2.2. CÂU ĐANG SAI THƯỜNG (Cần phục thù - 3 câu)
                StudentMistake::create([
                    'user_id' => $anNhien->id,
                    'question_id' => $questions3[2]->id, // Câu 3: Phần mềm ứng dụng
                    'practice_test_id' => $primaryTest3A->id,
                    'wrong_count' => 1,
                    'correct_count' => 0,
                    'last_student_answer' => [3], // Chọn nhầm Windows
                    'status' => 'unresolved',
                    'last_wrong_at' => $now->copy()->subHours(10),
                    'last_resolved_at' => null,
                ]);

                StudentMistake::create([
                    'user_id' => $anNhien->id,
                    'question_id' => $questions3[3]->id, // Câu 4: Nhận diện Laptop
                    'practice_test_id' => $primaryTest3A->id,
                    'wrong_count' => 1,
                    'correct_count' => 0,
                    'last_student_answer' => [2],
                    'status' => 'unresolved',
                    'last_wrong_at' => $now->copy()->subDay(),
                    'last_resolved_at' => null,
                ]);

                if ($primaryTest3B && $primaryTest3B->questions()->exists()) {
                    $qOther = $primaryTest3B->questions()->first();
                    StudentMistake::create([
                        'user_id' => $anNhien->id,
                        'question_id' => $qOther->id,
                        'practice_test_id' => $primaryTest3B->id,
                        'wrong_count' => 1,
                        'correct_count' => 0,
                        'last_student_answer' => [1],
                        'status' => 'unresolved',
                        'last_wrong_at' => $now->copy()->subDays(2),
                        'last_resolved_at' => null,
                    ]);
                }

                // 2.3. CÂU ĐÃ VƯỢT QUA / ĐÃ SỬA XONG (Đã phục thù thành công - 4 câu)
                StudentMistake::create([
                    'user_id' => $anNhien->id,
                    'question_id' => $questions3[0]->id, // Câu 1: Bàn di chuột tích hợp
                    'practice_test_id' => $primaryTest3A->id,
                    'wrong_count' => 2,
                    'correct_count' => 1,
                    'last_student_answer' => [0],
                    'status' => 'resolved',
                    'last_wrong_at' => $now->copy()->subDays(3),
                    'last_resolved_at' => $now->copy()->subHours(5),
                ]);

                StudentMistake::create([
                    'user_id' => $anNhien->id,
                    'question_id' => $questions3[1]->id, // Câu 2: Thuật ngữ App
                    'practice_test_id' => $primaryTest3A->id,
                    'wrong_count' => 1,
                    'correct_count' => 2,
                    'last_student_answer' => [0],
                    'status' => 'resolved',
                    'last_wrong_at' => $now->copy()->subDays(4),
                    'last_resolved_at' => $now->copy()->subHours(8),
                ]);

                StudentMistake::create([
                    'user_id' => $anNhien->id,
                    'question_id' => $questions3[6]->id, // Câu 7: Sạc pin thư viện
                    'practice_test_id' => $primaryTest3A->id,
                    'wrong_count' => 2,
                    'correct_count' => 1,
                    'last_student_answer' => [0],
                    'status' => 'resolved',
                    'last_wrong_at' => $now->copy()->subDays(3),
                    'last_resolved_at' => $now->copy()->subHours(12),
                ]);

                StudentMistake::create([
                    'user_id' => $anNhien->id,
                    'question_id' => $questions3[7]->id, // Câu 8: Thiết bị ngoại vi
                    'practice_test_id' => $primaryTest3A->id,
                    'wrong_count' => 1,
                    'correct_count' => 1,
                    'last_student_answer' => [0, 1],
                    'status' => 'resolved',
                    'last_wrong_at' => $now->copy()->subDays(5),
                    'last_resolved_at' => $now->copy()->subDays(1),
                ]);
            }
        }

        // 3. TẠO LỊCH SỬ BÀI LÀM GẦN ĐÂY CHO AN NHIÊN ĐỂ DẪN ĐẦU BẢNG VÀNG KHỐI 3 (QUÁN QUÂN 1,870 ĐIỂM)
        if ($primaryTest3A) {
            // Xóa các attempts cũ của An Nhiên trong tuần này để nạp điểm mới
            TestAttempt::where('user_id', $anNhien->id)
                ->where('completed_at', '>=', $now->copy()->subDays(7))
                ->delete();

            // Bài 1: Điểm 950 (Xuất sắc)
            TestAttempt::create([
                'user_id' => $anNhien->id,
                'practice_test_id' => $primaryTest3A->id,
                'score' => 950,
                'correct_answers' => 13,
                'total_questions' => 14,
                'duration_seconds' => 385,
                'completed_at' => $now->copy()->subHours(3),
            ]);

            // Bài 2: Điểm 920 (Giỏi)
            TestAttempt::create([
                'user_id' => $anNhien->id,
                'practice_test_id' => $primaryTest3B ? $primaryTest3B->id : $primaryTest3A->id,
                'score' => 920,
                'correct_answers' => 13,
                'total_questions' => 14,
                'duration_seconds' => 420,
                'completed_at' => $now->copy()->subDay(),
            ]);

            // Bài 3: Điểm 880 (Hoàn thành tốt)
            TestAttempt::create([
                'user_id' => $anNhien->id,
                'practice_test_id' => $primaryTest3A->id,
                'score' => 880,
                'correct_answers' => 12,
                'total_questions' => 14,
                'duration_seconds' => 510,
                'completed_at' => $now->copy()->subDays(3),
            ]);

            // Bài 4: Điểm 780 (Lần làm đầu tiên tiến bộ)
            TestAttempt::create([
                'user_id' => $anNhien->id,
                'practice_test_id' => $primaryTest3A->id,
                'score' => 780,
                'correct_answers' => 11,
                'total_questions' => 14,
                'duration_seconds' => 640,
                'completed_at' => $now->copy()->subDays(5),
            ]);
        }

        // 4. TẠO LƯỢT THI CHO CÁC BẠN KHỐI 3 ĐỂ BẢNG VÀNG CÓ ĐẦY ĐỦ Á QUÂN, HẠNG BA VÀ TOP 4-10
        // Danh sách học sinh Khối 3 (trừ An Nhiên):
        $peersGrade3 = User::where('role', 'student')
            ->where('id', '!=', $anNhien->id)
            ->where(function ($q) {
                $q->whereHas('classroom', fn ($c) => $c->where('grade', 3))
                  ->orWhere('email', 'like', '%.3a%');
            })
            ->get();

        $grade3RankingScores = [
            // Á Quân (#2)
            ['score1' => 840, 'score2' => 810], // Tổng: 1,650 điểm
            // Hạng Ba (#3)
            ['score1' => 770, 'score2' => 750], // Tổng: 1,520 điểm
            // Top 4 - 10
            ['score1' => 720, 'score2' => 690], // #4: 1,410 điểm
            ['score1' => 660, 'score2' => 620], // #5: 1,280 điểm
            ['score1' => 850, 'score2' => 300], // #6: 1,150 điểm
            ['score1' => 980, 'score2' => 0],   // #7: 980 điểm
            ['score1' => 900, 'score2' => 0],   // #8: 900 điểm
            ['score1' => 840, 'score2' => 0],   // #9: 840 điểm
            ['score1' => 760, 'score2' => 0],   // #10: 760 điểm
        ];

        foreach ($peersGrade3->values() as $idx => $peer) {
            if ($idx >= count($grade3RankingScores)) {
                break;
            }
            $config = $grade3RankingScores[$idx];

            // Xóa attempts trong tuần của bạn này
            TestAttempt::where('user_id', $peer->id)
                ->where('completed_at', '>=', $now->copy()->subDays(7))
                ->delete();

            // Tạo bài 1
            if ($config['score1'] > 0 && $primaryTest3A) {
                TestAttempt::create([
                    'user_id' => $peer->id,
                    'practice_test_id' => $primaryTest3A->id,
                    'score' => $config['score1'],
                    'correct_answers' => (int) round($config['score1'] / 1000 * 14),
                    'total_questions' => 14,
                    'duration_seconds' => rand(400, 700),
                    'completed_at' => $now->copy()->subDays(rand(1, 4))->subHours(rand(1, 10)),
                ]);
            }

            // Tạo bài 2
            if ($config['score2'] > 0 && $primaryTest3B) {
                TestAttempt::create([
                    'user_id' => $peer->id,
                    'practice_test_id' => $primaryTest3B->id,
                    'score' => $config['score2'],
                    'correct_answers' => (int) round($config['score2'] / 1000 * 14),
                    'total_questions' => 14,
                    'duration_seconds' => rand(450, 750),
                    'completed_at' => $now->copy()->subDays(rand(2, 6))->subHours(rand(1, 10)),
                ]);
            }
        }

        // 5. TẠO LƯỢT THI CHO CÁC BẠN KHỐI 4 ĐỂ TAB KHỐI 4 CŨNG RỰC RỠ ĐẦY ĐỦ TOP 10
        if ($primaryTest4) {
            $peersGrade4 = User::where('role', 'student')
                ->where(function ($q) {
                    $q->whereHas('classroom', fn ($c) => $c->where('grade', 4))
                      ->orWhere('email', 'like', '%.4a%');
                })
                ->get();

            $grade4Scores = [
                ['score1' => 930, 'score2' => 890], // #1: 1,820
                ['score1' => 860, 'score2' => 830], // #2: 1,690
                ['score1' => 780, 'score2' => 760], // #3: 1,540
                ['score1' => 710, 'score2' => 690], // #4: 1,400
                ['score1' => 650, 'score2' => 630], // #5: 1,280
                ['score1' => 580, 'score2' => 540], // #6: 1,120
                ['score1' => 950, 'score2' => 0],   // #7: 950
                ['score1' => 870, 'score2' => 0],   // #8: 870
            ];

            foreach ($peersGrade4->values() as $idx => $peer4) {
                if ($idx >= count($grade4Scores)) {
                    break;
                }
                $sConfig = $grade4Scores[$idx];
                TestAttempt::where('user_id', $peer4->id)->where('completed_at', '>=', $now->copy()->subDays(7))->delete();

                if ($sConfig['score1'] > 0) {
                    TestAttempt::create([
                        'user_id' => $peer4->id,
                        'practice_test_id' => $primaryTest4->id,
                        'score' => $sConfig['score1'],
                        'correct_answers' => (int) round($sConfig['score1'] / 1000 * 14),
                        'total_questions' => 14,
                        'duration_seconds' => rand(400, 650),
                        'completed_at' => $now->copy()->subDays(rand(1, 3))->subHours(rand(1, 10)),
                    ]);
                }
                if ($sConfig['score2'] > 0) {
                    TestAttempt::create([
                        'user_id' => $peer4->id,
                        'practice_test_id' => $primaryTest4->id,
                        'score' => $sConfig['score2'],
                        'correct_answers' => (int) round($sConfig['score2'] / 1000 * 14),
                        'total_questions' => 14,
                        'duration_seconds' => rand(400, 650),
                        'completed_at' => $now->copy()->subDays(rand(3, 5))->subHours(rand(1, 10)),
                    ]);
                }
            }
        }

        // 6. TẠO LƯỢT THI CHO CÁC BẠN KHỐI 5 ĐỂ TAB KHỐI 5 ĐẦY ĐỦ TOP 10
        if ($primaryTest5) {
            $peersGrade5 = User::where('role', 'student')
                ->where(function ($q) {
                    $q->whereHas('classroom', fn ($c) => $c->where('grade', 5))
                      ->orWhere('email', 'like', '%.5a%');
                })
                ->get();

            $grade5Scores = [
                ['score1' => 960, 'score2' => 930], // #1: 1,890
                ['score1' => 880, 'score2' => 840], // #2: 1,720
                ['score1' => 800, 'score2' => 780], // #3: 1,580
                ['score1' => 740, 'score2' => 710], // #4: 1,450
                ['score1' => 670, 'score2' => 640], // #5: 1,310
            ];

            foreach ($peersGrade5->values() as $idx => $peer5) {
                if ($idx >= count($grade5Scores)) {
                    break;
                }
                $sConfig5 = $grade5Scores[$idx];
                TestAttempt::where('user_id', $peer5->id)->where('completed_at', '>=', $now->copy()->subDays(7))->delete();

                if ($sConfig5['score1'] > 0) {
                    TestAttempt::create([
                        'user_id' => $peer5->id,
                        'practice_test_id' => $primaryTest5->id,
                        'score' => $sConfig5['score1'],
                        'correct_answers' => (int) round($sConfig5['score1'] / 1000 * 14),
                        'total_questions' => 14,
                        'duration_seconds' => rand(380, 600),
                        'completed_at' => $now->copy()->subDays(rand(1, 3))->subHours(rand(1, 10)),
                    ]);
                }
                if ($sConfig5['score2'] > 0) {
                    TestAttempt::create([
                        'user_id' => $peer5->id,
                        'practice_test_id' => $primaryTest5->id,
                        'score' => $sConfig5['score2'],
                        'correct_answers' => (int) round($sConfig5['score2'] / 1000 * 14),
                        'total_questions' => 14,
                        'duration_seconds' => rand(400, 620),
                        'completed_at' => $now->copy()->subDays(rand(3, 5))->subHours(rand(1, 10)),
                    ]);
                }
            }
        }

        // 7. XÓA SẠCH CACHE ĐỂ CẬP NHẬT GIAO DIỆN NGAY LẬP TỨC
        Cache::flush();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Giữ nguyên dữ liệu demo
    }
};
