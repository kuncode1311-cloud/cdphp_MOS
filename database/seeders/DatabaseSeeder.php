<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\Level;
use App\Models\PracticeTest;
use App\Models\Program;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    // Tạo dữ liệu ban đầu và gọi lệnh nhập câu hỏi từ gói IC3 cũ.
    public function run(): void
    {
        $admin = User::firstOrCreate(['email' => 'admin@ic3.test'], ['name' => 'Quản trị viên (Tổng)', 'password' => '123456', 'role' => 'admin']);
        $teacher1 = User::firstOrCreate(['email' => 'teacher@ic3.test'], ['name' => 'Cô Mai Linh (GV 3A1)', 'password' => '123456', 'role' => 'teacher', 'max_students' => 100, 'expires_at' => '2027-08-31', 'status' => 'active']);
        $teacher2 = User::firstOrCreate(['email' => 'teacher4@ic3.test'], ['name' => 'Thầy Tuấn (GV 4A1)', 'password' => '123456', 'role' => 'teacher', 'max_students' => 100, 'expires_at' => '2027-08-31', 'status' => 'active']);
        $teacher3 = User::firstOrCreate(['email' => 'teacher5@ic3.test'], ['name' => 'Cô Lan Hương (GV 5A1)', 'password' => '123456', 'role' => 'teacher', 'max_students' => 100, 'expires_at' => '2027-08-31', 'status' => 'active']);

        $class1 = Classroom::firstOrCreate(['name' => 'Lớp 3A1', 'school_year' => '2026-2027'], ['grade' => 3, 'teacher_id' => $teacher1->id]);
        $class2 = Classroom::firstOrCreate(['name' => 'Lớp 4A1', 'school_year' => '2026-2027'], ['grade' => 4, 'teacher_id' => $teacher2->id]);
        $class3 = Classroom::firstOrCreate(['name' => 'Lớp 5A1', 'school_year' => '2026-2027'], ['grade' => 5, 'teacher_id' => $teacher3->id]);

        $student1 = User::firstOrCreate(['student_code' => 'HS001'], ['name' => 'An Nhiên', 'email' => 'hs001@student.ic3.local', 'password' => '123456', 'role' => 'student', 'classroom_id' => $class1->id, 'created_by' => $teacher1->id]);
        $student2 = User::firstOrCreate(['student_code' => 'HS002'], ['name' => 'Bảo Nam', 'email' => 'hs002@student.ic3.local', 'password' => '123456', 'role' => 'student', 'classroom_id' => $class2->id, 'created_by' => $teacher2->id]);
        $student3 = User::firstOrCreate(['student_code' => 'HS003'], ['name' => 'Minh Khôi', 'email' => 'hs003@student.ic3.local', 'password' => '123456', 'role' => 'student', 'classroom_id' => $class3->id, 'created_by' => $teacher3->id]);
        $program = Program::firstOrCreate(
            ['slug' => 'ic3-gs6-primary'],
            [
                'name' => 'IC3 GS6 Tiểu học',
                'description' => 'Hành trình xây dựng năng lực số dành cho học sinh tiểu học.',
                'accent' => '#635bff',
            ]
        );

        $levelData = [3 => 'Spark Level 1', 4 => 'Spark Level 2', 5 => 'Spark Level 3'];
        $createdLevels = [];
        $baseTopics = [
            ['Căn bản về công nghệ', 'Hiểu thiết bị số, phần cứng, phần mềm và cách sử dụng công nghệ đúng mục đích.', 'monitor'],
            ['Công dân số', 'Xây dựng thói quen giao tiếp văn minh và có trách nhiệm trong môi trường số.', 'users'],
            ['Quản lý thông tin', 'Tìm kiếm, đánh giá, lưu trữ và tổ chức thông tin hiệu quả.', 'folder'],
            ['Sáng tạo nội dung', 'Thực hành tạo tài liệu, bài trình bày và nội dung số hấp dẫn.', 'palette'],
            ['Truyền thông', 'Kết nối, cộng tác và chia sẻ thông tin an toàn.', 'message'],
            ['An toàn và bảo mật', 'Bảo vệ tài khoản, dữ liệu cá nhân và nhận diện rủi ro trực tuyến.', 'shield'],
            ['Khám phá mở rộng', 'Tổng hợp kiến thức qua các tình huống thực tế thú vị.', 'rocket'],
        ];
        $topicNames = [
            3 => ['Căn bản về công nghệ', 'Công dân số', 'Quản lý thông tin', 'Sáng tạo nội dung', 'Truyền thông', 'An toàn và bảo mật', 'Chủ đề mở rộng'],
            4 => ['Căn bản về công nghệ', 'Công dân số', 'Quản lý thông tin', 'Sáng tạo nội dung', 'Truyền thông số', 'Cộng tác', 'An toàn và bảo mật'],
            5 => ['Căn bản về công nghệ', 'Công dân số', 'Quản lý thông tin', 'Sáng tạo nội dung', 'Truyền thông số', 'Cộng tác', 'An toàn và bảo mật'],
        ];
        foreach ($levelData as $grade => $name) {
            $level = Level::firstOrCreate(
                ['program_id' => $program->id, 'grade' => $grade],
                ['name' => "IC3 GS6 {$name} — Khối {$grade}", 'slug' => "khoi-{$grade}-".str($name)->slug(), 'position' => $grade]
            );
            $createdLevels[$grade] = $level;
            foreach ($baseTopics as $index => [$unusedName, $description, $icon]) {
                $topicName = $topicNames[$grade][$index];
                $topic = Topic::firstOrCreate(
                    ['level_id' => $level->id, 'slug' => 'chu-de-'.($index + 1)],
                    ['name' => $topicName, 'description' => $description, 'icon' => $icon, 'position' => $index + 1]
                );
                $countsByGrade = [
                    3 => [2, 1, 2, 1, 2, 2, 1],
                    4 => [2, 1, 2, 3, 2, 1, 2],
                    5 => [2, 2, 1, 1, 2, 1, 2],
                ];
                $testCount = $countsByGrade[$grade][$index];
                for ($testNo = 1; $testNo <= $testCount; $testNo++) {
                    $passwords = [
                        3 => [25103011, 25103010, 25103009, 25103008, 25103007, 25103006, 25103005, 25103004, 25103003, 25103002, 25103001],
                        4 => [41025013, 41025012, 41025011, 41025010, 41025009, 41025008, 41025007, 41025006, 41025005, 41025004, 41025003, 41025002, 41025001],
                        5 => [68759341, 68759342, 68759343, 68759344, 68759345, 68759346, 68759347, 68759348, 68759349, 68759350, 68759351],
                    ];
                    $passwordOffset = array_sum(array_slice($countsByGrade[$grade], 0, $index)) + ($testNo - 1);
                    PracticeTest::firstOrCreate(
                        ['slug' => "k{$grade}-cd".($index + 1)."-bai-{$testNo}"],
                        [
                            'topic_id' => $topic->id,
                            'name' => "Bài luyện {$testNo}",
                            'access_code' => (string) $passwords[$grade][$passwordOffset],
                            'duration_minutes' => 0,
                            'question_count' => 0,
                            'pass_score' => 1000,
                            'max_score' => 1000,
                            'difficulty' => $testNo === 1 ? 'Cơ bản' : 'Trung bình',
                            'position' => $testNo,
                        ]
                    );
                }
            }
        }

        // Cấp quyền mẫu cho Giáo viên: Cô Mai Linh (Khối 3), Thầy Tuấn (Khối 3 & 4), Cô Lan Hương (Khối 5)
        if (isset($createdLevels[3])) {
            $teacher1->teacherLevels()->sync([$createdLevels[3]->id]);
        }
        if (isset($createdLevels[3], $createdLevels[4])) {
            $teacher2->teacherLevels()->sync([$createdLevels[3]->id, $createdLevels[4]->id]);
        }
        if (isset($createdLevels[5])) {
            $teacher3->teacherLevels()->sync([$createdLevels[5]->id]);
        }

        // Cấp quyền mẫu: An Nhiên học Khối 3, Bảo Nam học Khối 4, Minh Khôi học Khối 5
        if (isset($createdLevels[3])) {
            $student1->accessibleLevels()->sync([$createdLevels[3]->id]);
        }
        if (isset($createdLevels[4])) {
            $student2->accessibleLevels()->sync([$createdLevels[4]->id]);
        }
        if (isset($createdLevels[5])) {
            $student3->accessibleLevels()->sync([$createdLevels[5]->id]);
        }

        // Khởi tạo danh mục các Gói dịch vụ & Bản quyền phần mềm IC3
        $this->call(PackageSeeder::class);

        // Khởi tạo dữ liệu 509 câu hỏi IC3
        $this->call(QuestionDataSeeder::class);

        // Tự động nạp dữ liệu thi đua, điểm số & đổi thưởng minigame (bỏ qua khi chạy PHPUnit test)
        if (! app()->runningUnitTests()) {
            $this->call(DemoDataSeeder::class);
        }

    }
}
