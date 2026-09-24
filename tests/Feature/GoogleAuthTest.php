<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_google_redirect_route_redirects_to_google_oauth(): void
    {
        $response = $this->get(route('auth.google'));

        $response->assertRedirect();
        $targetUrl = $response->getTargetUrl();
        $this->assertStringContainsString('accounts.google.com', $targetUrl);
        $this->assertStringContainsString('client_id=' . config('services.google.client_id'), $targetUrl);
    }

    public function test_google_callback_creates_new_student_user_and_logs_in(): void
    {
        $abstractUser = Mockery::mock(SocialiteUser::class);
        $abstractUser->shouldReceive('getId')->andReturn('google-unique-id-999');
        $abstractUser->shouldReceive('getEmail')->andReturn('google_student@gmail.com');
        $abstractUser->shouldReceive('getName')->andReturn('Em Bé Google');
        $abstractUser->shouldReceive('getNickname')->andReturn('bbe');
        $abstractUser->shouldReceive('getAvatar')->andReturn('https://google.com/avatar.jpg');

        $provider = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
        $provider->shouldReceive('user')->andReturn($abstractUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('home'));

        $this->assertAuthenticated();

        $user = User::where('google_id', 'google-unique-id-999')->first();
        $this->assertNotNull($user);
        $this->assertEquals('Em Bé Google', $user->name);
        $this->assertEquals('google_student@gmail.com', $user->email);
        $this->assertEquals(UserRole::Student->value, $user->role);
        $this->assertEquals(10, $user->reward_stars);
        $this->assertEquals(600, $user->game_time_seconds);
    }

    public function test_google_callback_links_to_existing_user_by_email(): void
    {
        $existingUser = User::factory()->create([
            'email' => 'teacher_existing@gmail.com',
            'role' => UserRole::Teacher->value,
            'google_id' => null,
        ]);

        $abstractUser = Mockery::mock(SocialiteUser::class);
        $abstractUser->shouldReceive('getId')->andReturn('google-teacher-id-123');
        $abstractUser->shouldReceive('getEmail')->andReturn('teacher_existing@gmail.com');
        $abstractUser->shouldReceive('getName')->andReturn('Thầy Giáo Google');
        $abstractUser->shouldReceive('getNickname')->andReturn('thaygiao');
        $abstractUser->shouldReceive('getAvatar')->andReturn('https://google.com/avatar_teacher.jpg');

        $provider = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
        $provider->shouldReceive('user')->andReturn($abstractUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($existingUser);

        $existingUser->refresh();
        $this->assertEquals('google-teacher-id-123', $existingUser->google_id);
    }
}
