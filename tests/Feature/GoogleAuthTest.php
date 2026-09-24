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

    public function test_google_callback_rejects_unregistered_email_and_does_not_create_user(): void
    {
        $abstractUser = Mockery::mock(SocialiteUser::class);
        $abstractUser->shouldReceive('getId')->andReturn('google-unique-id-999');
        $abstractUser->shouldReceive('getEmail')->andReturn('google_unregistered@gmail.com');
        $abstractUser->shouldReceive('getName')->andReturn('Người Lạ Google');
        $abstractUser->shouldReceive('getNickname')->andReturn('stranger');
        $abstractUser->shouldReceive('getAvatar')->andReturn('https://google.com/avatar.jpg');

        $provider = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
        $provider->shouldReceive('user')->andReturn($abstractUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['login']);

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'google_unregistered@gmail.com']);
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
