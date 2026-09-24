<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\StudentMistake;
use App\Models\User;
use Illuminate\Database\Seeder;

class StudentMistakeSeeder extends Seeder
{
    /**
     * Khởi tạo dữ liệu mẫu Sổ tay câu sai & Phục thù cho học sinh
     */
    public function run(): void
    {
        $student = User::where('name', 'like', '%An Nhiên%')->first();
        if (! $student) {
            $student = User::where('role', 'student')->first();
        }

        if (! $student) {
            return;
        }

        // Lấy danh sách câu hỏi theo từng Khối lớp
        $grade3Questions = Question::with('practiceTest.topic.level')
            ->whereHas('practiceTest.topic.level', fn ($q) => $q->where('grade', 3))
            ->take(10)
            ->get();

        $grade4Questions = Question::with('practiceTest.topic.level')
            ->whereHas('practiceTest.topic.level', fn ($q) => $q->where('grade', 4))
            ->take(5)
            ->get();

        $grade5Questions = Question::with('practiceTest.topic.level')
            ->whereHas('practiceTest.topic.level', fn ($q) => $q->where('grade', 5))
            ->take(5)
            ->get();

        // 1. Tạo câu sai cho học sinh An Nhiên (Khối 3)
        // Câu 1: Báo động đỏ (sai 2 lần) - Đang sai
        if (isset($grade3Questions[0])) {
            StudentMistake::updateOrCreate(
                ['user_id' => $student->id, 'question_id' => $grade3Questions[0]->id],
                [
                    'practice_test_id' => $grade3Questions[0]->practice_test_id,
                    'wrong_count' => 2,
                    'correct_count' => 0,
                    'last_student_answer' => [0],
                    'status' => 'unresolved',
                    'last_wrong_at' => now()->subMinutes(25),
                ]
            );
        }

        // Câu 2: Báo động đỏ nguy cấp (sai 3 lần) - Đang sai
        if (isset($grade3Questions[1])) {
            StudentMistake::updateOrCreate(
                ['user_id' => $student->id, 'question_id' => $grade3Questions[1]->id],
                [
                    'practice_test_id' => $grade3Questions[1]->practice_test_id,
                    'wrong_count' => 3,
                    'correct_count' => 0,
                    'last_student_answer' => [1],
                    'status' => 'unresolved',
                    'last_wrong_at' => now()->subHours(2),
                ]
            );
        }

        // Câu 3: Sai 1 lần - Đang sai
        if (isset($grade3Questions[2])) {
            StudentMistake::updateOrCreate(
                ['user_id' => $student->id, 'question_id' => $grade3Questions[2]->id],
                [
                    'practice_test_id' => $grade3Questions[2]->practice_test_id,
                    'wrong_count' => 1,
                    'correct_count' => 0,
                    'last_student_answer' => [2],
                    'status' => 'unresolved',
                    'last_wrong_at' => now()->subHours(5),
                ]
            );
        }

        // Câu 4: Sai 1 lần - Đang sai
        if (isset($grade3Questions[3])) {
            StudentMistake::updateOrCreate(
                ['user_id' => $student->id, 'question_id' => $grade3Questions[3]->id],
                [
                    'practice_test_id' => $grade3Questions[3]->practice_test_id,
                    'wrong_count' => 1,
                    'correct_count' => 0,
                    'last_student_answer' => [0],
                    'status' => 'unresolved',
                    'last_wrong_at' => now()->subDay(),
                ]
            );
        }

        // Câu 5: Báo động đỏ (sai 2 lần) - Đang sai
        if (isset($grade3Questions[4])) {
            StudentMistake::updateOrCreate(
                ['user_id' => $student->id, 'question_id' => $grade3Questions[4]->id],
                [
                    'practice_test_id' => $grade3Questions[4]->practice_test_id,
                    'wrong_count' => 2,
                    'correct_count' => 0,
                    'last_student_answer' => [3],
                    'status' => 'unresolved',
                    'last_wrong_at' => now()->subDays(2),
                ]
            );
        }

        // Câu 6: Đã phục thù thành công (làm đúng lại 2 lần)
        if (isset($grade3Questions[5])) {
            StudentMistake::updateOrCreate(
                ['user_id' => $student->id, 'question_id' => $grade3Questions[5]->id],
                [
                    'practice_test_id' => $grade3Questions[5]->practice_test_id,
                    'wrong_count' => 1,
                    'correct_count' => 2,
                    'last_student_answer' => [1],
                    'status' => 'resolved',
                    'last_wrong_at' => now()->subDays(3),
                    'last_resolved_at' => now()->subHours(1),
                ]
            );
        }

        // Câu 7: Đã phục thù thành công (làm đúng lại 1 lần)
        if (isset($grade3Questions[6])) {
            StudentMistake::updateOrCreate(
                ['user_id' => $student->id, 'question_id' => $grade3Questions[6]->id],
                [
                    'practice_test_id' => $grade3Questions[6]->practice_test_id,
                    'wrong_count' => 2,
                    'correct_count' => 1,
                    'last_student_answer' => [0],
                    'status' => 'resolved',
                    'last_wrong_at' => now()->subDays(4),
                    'last_resolved_at' => now()->subDays(1),
                ]
            );
        }

        // Câu 8: Đã phục thù thành công (làm đúng lại 1 lần)
        if (isset($grade3Questions[7])) {
            StudentMistake::updateOrCreate(
                ['user_id' => $student->id, 'question_id' => $grade3Questions[7]->id],
                [
                    'practice_test_id' => $grade3Questions[7]->practice_test_id,
                    'wrong_count' => 1,
                    'correct_count' => 1,
                    'last_student_answer' => [2],
                    'status' => 'resolved',
                    'last_wrong_at' => now()->subDays(5),
                    'last_resolved_at' => now()->subHours(3),
                ]
            );
        }

        // 2. Tạo câu sai ở Khối 4 (để test bộ lọc Khối 4)
        if (isset($grade4Questions[0])) {
            StudentMistake::updateOrCreate(
                ['user_id' => $student->id, 'question_id' => $grade4Questions[0]->id],
                [
                    'practice_test_id' => $grade4Questions[0]->practice_test_id,
                    'wrong_count' => 1,
                    'correct_count' => 0,
                    'last_student_answer' => [1],
                    'status' => 'unresolved',
                    'last_wrong_at' => now()->subHours(6),
                ]
            );
        }

        if (isset($grade4Questions[1])) {
            StudentMistake::updateOrCreate(
                ['user_id' => $student->id, 'question_id' => $grade4Questions[1]->id],
                [
                    'practice_test_id' => $grade4Questions[1]->practice_test_id,
                    'wrong_count' => 1,
                    'correct_count' => 1,
                    'last_student_answer' => [0],
                    'status' => 'resolved',
                    'last_wrong_at' => now()->subHours(10),
                    'last_resolved_at' => now()->subHours(2),
                ]
            );
        }

        // 3. Tạo câu sai ở Khối 5 (để test bộ lọc Khối 5)
        if (isset($grade5Questions[0])) {
            StudentMistake::updateOrCreate(
                ['user_id' => $student->id, 'question_id' => $grade5Questions[0]->id],
                [
                    'practice_test_id' => $grade5Questions[0]->practice_test_id,
                    'wrong_count' => 2,
                    'correct_count' => 0,
                    'last_student_answer' => [2],
                    'status' => 'unresolved',
                    'last_wrong_at' => now()->subHours(8),
                ]
            );
        }

        if (isset($grade5Questions[1])) {
            StudentMistake::updateOrCreate(
                ['user_id' => $student->id, 'question_id' => $grade5Questions[1]->id],
                [
                    'practice_test_id' => $grade5Questions[1]->practice_test_id,
                    'wrong_count' => 1,
                    'correct_count' => 1,
                    'last_student_answer' => [1],
                    'status' => 'resolved',
                    'last_wrong_at' => now()->subDay(),
                    'last_resolved_at' => now()->subHours(4),
                ]
            );
        }
    }
}
