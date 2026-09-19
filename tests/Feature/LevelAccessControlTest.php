<?php

namespace Tests\Feature;

use App\Models\Level;
use App\Models\PracticeTest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LevelAccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_only_access_granted_levels(): void
    {
        $this->seed();

        $student3 = User::where('student_code', 'HS001')->firstOrFail();
        $level3 = Level::where('grade', 3)->firstOrFail();
        $level4 = Level::where('grade', 4)->firstOrFail();

        // Học sinh Khối 3 được vào Khối 3
        $this->actingAs($student3)
            ->get(route('levels.show', $level3))
            ->assertOk();

        // Học sinh Khối 3 bị chặn 403 khi vào Khối 4
        $this->actingAs($student3)
            ->get(route('levels.show', $level4))
            ->assertForbidden();

        // Học sinh Khối 3 bị chặn 403 khi cố truy cập bài test của Khối 4
        $testInLevel4 = PracticeTest::whereHas('topic', fn ($q) => $q->where('level_id', $level4->id))->firstOrFail();
        $this->actingAs($student3)
            ->get(route('tests.show', $testInLevel4))
            ->assertForbidden();
    }

    public function test_teacher_can_grant_multiple_levels_to_a_student(): void
    {
        $this->seed();

        $teacher = User::where('email', 'teacher4@ic3.test')->firstOrFail(); // Sở hữu Khối 3 & 4
        $student = User::where('student_code', 'HS002')->firstOrFail();
        $level3 = Level::where('grade', 3)->firstOrFail();
        $level4 = Level::where('grade', 4)->firstOrFail();

        // Ban đầu chỉ có Khối 4
        $this->assertTrue($student->canAccessLevel($level4));
        $this->assertFalse($student->canAccessLevel($level3));

        // Giáo viên cấp thêm Khối 3 cho học sinh này (do GV sở hữu cả 3 & 4)
        $this->actingAs($teacher)
            ->put(route('admin.users.update', $student), [
                'name' => $student->name,
                'email' => $student->email,
                'student_code' => $student->student_code,
                'role' => $student->role,
                'classroom_id' => $student->classroom_id,
                'level_ids' => [$level3->id, $level4->id],
            ])
            ->assertRedirect();

        $student->refresh();

        // Học sinh giờ đây vào được cả 2 Khối 3 và 4
        $this->assertTrue($student->canAccessLevel($level3));
        $this->assertTrue($student->canAccessLevel($level4));

        $this->actingAs($student)
            ->get(route('levels.show', $level4))
            ->assertOk();
    }

    public function test_admin_and_teacher_have_full_access_to_all_levels(): void
    {
        $this->seed();

        $admin = User::where('role', 'admin')->firstOrFail();
        $level5 = Level::where('grade', 5)->firstOrFail();

        $this->assertTrue($admin->canAccessLevel($level5));
    }
}
