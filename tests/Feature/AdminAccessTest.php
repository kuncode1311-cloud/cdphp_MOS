<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_always_opens_management_dashboard(): void
    {
        $this->seed();
        $this->withSession(['url.intended' => '/'])->post('/dang-nhap', ['login' => 'admin', 'password' => '123456'])->assertRedirect(route('admin.dashboard'));
        $this->post('/dang-xuat');
        $this->post('/dang-nhap', ['login' => 'teacher', 'password' => '123456'])->assertRedirect(route('admin.dashboard'));
        $this->post('/dang-xuat');
        $this->post('/dang-nhap', ['login' => 'hs001', 'password' => '123456'])->assertRedirect(route('home'));
        $this->actingAs(User::where('role', 'admin')->first())->get('/')->assertRedirect(route('admin.dashboard'));
        $this->get('/quan-tri')->assertOk()->assertSeeText('Soạn đề thi IC3')->assertSeeText('Giáo viên & Học sinh');
        $this->get('/quan-tri/quan-ly')->assertRedirect(route('admin.dashboard') . '#cau-truc');
    }

    public function test_teacher_can_access_admin_dashboard_and_only_sees_their_assigned_classroom(): void
    {
        $this->seed();
        $teacher = User::where('email', 'teacher@ic3.test')->firstOrFail();
        
        $this->actingAs($teacher)
            ->get('/quan-tri')
            ->assertOk()
            ->assertSeeText('Chế độ Giáo viên')
            ->assertSeeText('An Nhiên'); // Student created by teacher
    }

    public function test_student_cannot_access_admin_area(): void
    {
        $this->seed();
        $student = User::where('role', 'student')->firstOrFail();

        $this->actingAs($student)
            ->get('/quan-tri')
            ->assertForbidden();
    }
}
