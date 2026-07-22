<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_user_cannot_log_in(): void
    {
        $user = User::factory()->createOne([
            'email' => 'inactive-login@example.com',
            'status' => 'inactive',
        ]);

        $response = $this->post(route('login.submit'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_inactive_authenticated_session_is_logged_out(): void
    {
        $user = User::factory()->createOne(['status' => 'inactive']);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_is_rate_limited_by_email_and_ip(): void
    {
        $credentials = [
            'email' => 'rate-limited@example.com',
            'password' => 'incorrect-password',
        ];

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('login.submit'), $credentials)->assertRedirect();
        }

        $this->post(route('login.submit'), $credentials)->assertStatus(429);
    }

    public function test_inactive_user_cannot_authorize_a_private_broadcast_channel(): void
    {
        $user = User::factory()->createOne(['status' => 'inactive']);

        $response = $this->actingAs($user)->post('/broadcasting/auth', [
            'channel_name' => "private-users.{$user->id}",
            'socket_id' => '123.456',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
