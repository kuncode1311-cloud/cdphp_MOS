<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Classroom;
use App\Models\Level;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherStudentAccessControlTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Teacher chỉ được cấp các Khối học mà Teacher đang sở hữu
     */
    public function test_teacher_can_only_grant_levels_they_own(): void
    {
        $this->seed();

        $teacher1 = User::where('email', 'teacher@ic3.test')->firstOrFail(); // Chỉ sở hữu Khối 3
        $student1 = User::where('student_code', 'HS001')->firstOrFail();

        $level3 = Level::where('grade', 3)->firstOrFail();
        $level5 = Level::where('grade', 5)->firstOrFail();

        // ❌ Giáo viên 1 cố cấp Khối 5 (không sở hữu) -> Bị validation lỗi
        $response = $this->actingAs($teacher1)->put(route('admin.users.update', $student1), [
            'name' => $student1->name,
            'email' => $student1->email,
            'student_code' => $student1->student_code,
            'role' => $student1->role,
            'classroom_id' => $student1->classroom_id,
            'level_ids' => [$level3->id, $level5->id],
        ]);

        $response->assertSessionHasErrors('level_ids.1');

        // ✅ Giáo viên 1 cấp Khối 3 (sở hữu) -> Thành công
        $responseSuccess = $this->actingAs($teacher1)->put(route('admin.users.update', $student1), [
            'name' => $student1->name,
            'email' => $student1->email,
            'student_code' => $student1->student_code,
            'role' => $student1->role,
            'classroom_id' => $student1->classroom_id,
            'level_ids' => [$level3->id],
        ]);

        $responseSuccess->assertRedirect();
        $this->assertTrue($student1->fresh()->canAccessLevel($level3));
        $this->assertFalse($student1->fresh()->canAccessLevel($level5));
    }

    /**
     * Test: Teacher A không thể sửa hoặc xóa học sinh của Teacher B
     */
    public function test_teacher_cannot_manage_students_of_another_teacher(): void
    {
        $this->seed();

        $teacher1 = User::where('email', 'teacher@ic3.test')->firstOrFail(); // Quản lý Lớp 3A1, HS001
        $teacher2 = User::where('email', 'teacher4@ic3.test')->firstOrFail(); // Quản lý Lớp 4A1, HS002
        $student2 = User::where('student_code', 'HS002')->firstOrFail();

        // Teacher 1 cố sửa học sinh của Teacher 2 -> 403 Forbidden
        $this->actingAs($teacher1)->put(route('admin.users.update', $student2), [
            'name' => 'Tên bị sửa trái phép',
            'email' => $student2->email,
            'role' => 'student',
        ])->assertForbidden();

        // Teacher 1 cố xóa học sinh của Teacher 2 -> 403 Forbidden
        $this->actingAs($teacher1)->delete(route('admin.users.destroy', $student2))->assertForbidden();
    }

    /**
     * Test: Teacher không thể tạo thêm học sinh khi đã vượt quá số lượng quota cho phép (max_students)
     */
    public function test_teacher_cannot_create_students_when_quota_exceeded(): void
    {
        $this->seed();

        $teacher = User::factory()->create([
            'role' => UserRole::Teacher->value,
            'max_students' => 1,
            'expires_at' => now()->addMonth(),
            'status' => 'active',
        ]);

        $level3 = Level::where('grade', 3)->firstOrFail();
        $teacher->teacherLevels()->sync([$level3->id]);

        $class = Classroom::create([
            'name' => 'Lớp Test Quota',
            'grade' => 3,
            'school_year' => '2026-2027',
            'teacher_id' => $teacher->id,
        ]);

        // Tạo học sinh thứ 1 -> Thành công (đạt 1/1)
        $this->actingAs($teacher)->post(route('admin.users.store'), [
            'name' => 'Học sinh 1',
            'email' => 'hs_quota_1@test.com',
            'student_code' => 'HSQ01',
            'password' => '123456',
            'role' => 'student',
            'classroom_id' => $class->id,
            'level_ids' => [$level3->id],
        ])->assertRedirect();

        $this->assertEquals(1, $teacher->students()->count());

        // Cố tạo học sinh thứ 2 -> Bị chặn 403 (Hết slot)
        $this->actingAs($teacher)->post(route('admin.users.store'), [
            'name' => 'Học sinh 2',
            'email' => 'hs_quota_2@test.com',
            'student_code' => 'HSQ02',
            'password' => '123456',
            'role' => 'student',
            'classroom_id' => $class->id,
            'level_ids' => [$level3->id],
        ])->assertForbidden();
    }

    /**
     * Test: Teacher không thể can thiệp sửa / xóa / thêm Master Content (Chương trình, Khối lớp)
     */
    public function test_teacher_cannot_modify_master_programs_and_levels(): void
    {
        $this->seed();

        $teacher = User::where('email', 'teacher@ic3.test')->firstOrFail();
        $level3 = Level::where('grade', 3)->firstOrFail();
        $program = Program::firstOrFail();

        // Thêm Program -> Chặn 403
        $this->actingAs($teacher)->post(route('admin.programs.store'), [
            'name' => 'Hack Program',
        ])->assertForbidden();

        // Xóa Khối -> Chặn 403
        $this->actingAs($teacher)->delete(route('admin.levels.destroy', $level3))->assertForbidden();
    }

    /**
     * Test: Admin có thể gán Khối và Quota cho Giáo viên
     */
    public function test_admin_can_assign_levels_and_quota_to_teacher(): void
    {
        $this->seed();

        $admin = User::where('role', 'admin')->firstOrFail();
        $teacher = User::where('email', 'teacher@ic3.test')->firstOrFail();
        $level4 = Level::where('grade', 4)->firstOrFail();
        $level5 = Level::where('grade', 5)->firstOrFail();

        $this->actingAs($admin)->put(route('admin.users.update', $teacher), [
            'name' => $teacher->name,
            'email' => $teacher->email,
            'role' => 'teacher',
            'max_students' => 250,
            'expires_at' => '2028-12-31',
            'status' => 'active',
            'teacher_level_ids' => [$level4->id, $level5->id],
        ])->assertRedirect();

        $teacher->refresh();
        $this->assertEquals(250, $teacher->max_students);
        $this->assertEquals('2028-12-31', $teacher->expires_at->format('Y-m-d'));
        $this->assertTrue($teacher->teacherLevels->contains($level4));
        $this->assertTrue($teacher->teacherLevels->contains($level5));
    }
}
