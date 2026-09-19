<?php

namespace Tests\Feature;

use App\Models\Classroom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_student_through_database_backed_resource(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $classroom = Classroom::create(['name' => 'Lớp 3A1', 'grade' => 3, 'school_year' => '2026-2027', 'teacher_id' => $admin->id]);

        $this->actingAs($admin)->post(route('admin.students.store'), [
            'name' => 'Học sinh mới', 'student_code' => 'HS100', 'password' => '123456', 'classroom_id' => $classroom->id,
        ])->assertRedirect();

        $student = User::where('student_code', 'HS100')->firstOrFail();
        $this->assertTrue($student->isStudent());
        $this->assertTrue($student->classroom->is($classroom));
        $this->assertNotSame('123456', $student->password);
    }

    public function test_student_cannot_use_admin_user_routes(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $this->actingAs($student)->post(route('admin.users.store'), [])->assertForbidden();
    }

    public function test_admin_cannot_delete_current_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->delete(route('admin.users.destroy', $admin))->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    /**
     * Kiểm tra Admin có thể gán và cập nhật giáo viên phụ trách cho học sinh
     */
    public function test_admin_can_assign_teacher_when_creating_and_updating_student(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher1 = User::factory()->create(['role' => 'teacher', 'name' => 'Cô Mai']);
        $teacher2 = User::factory()->create(['role' => 'teacher', 'name' => 'Thầy Hùng']);

        // 1. Admin tạo học sinh và gán giáo viên Cô Mai
        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Nguyễn Văn An',
            'email' => 'an.nguyen@student.ic3.local',
            'student_code' => 'HS999',
            'password' => '123456',
            'role' => 'student',
            'status' => 'active',
            'created_by' => $teacher1->id,
        ]);

        $response->assertRedirect();

        $student = User::where('student_code', 'HS999')->firstOrFail();
        $this->assertEquals($teacher1->id, $student->created_by);
        $this->assertTrue($student->teacher->is($teacher1));

        // 2. Admin cập nhật chuyển học sinh sang Thầy Hùng
        $updateResponse = $this->actingAs($admin)->put(route('admin.users.update', $student), [
            'name' => 'Nguyễn Văn An (Đổi GV)',
            'email' => 'an.nguyen@student.ic3.local',
            'student_code' => 'HS999',
            'role' => 'student',
            'status' => 'active',
            'created_by' => $teacher2->id,
        ]);

        $updateResponse->assertRedirect();

        $student->refresh();
        $this->assertEquals($teacher2->id, $student->created_by);
        $this->assertTrue($student->teacher->is($teacher2));
    }
}
