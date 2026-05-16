<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_login_and_register_pages(): void
    {
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();
    }

    public function test_user_can_register_and_is_redirected_to_chat_index(): void
    {
        $response = $this->post('/register', [
            'name' => 'Jane Developer',
            'email' => 'jane@example.com',
            'password' => 'supersecure',
            'password_confirmation' => 'supersecure',
        ]);

        $response->assertRedirect(route('chat.index'));
        $this->assertAuthenticated();

        $user = User::first();

        $this->assertNotNull($user);
        $this->assertSame('free', $user->plan);
        $this->assertTrue(Hash::check('supersecure', $user->password));
    }

    public function test_user_can_login_and_logout_with_custom_auth_flow(): void
    {
        $user = User::factory()->create([
            'email' => 'dev@example.com',
            'password' => 'supersecure',
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'supersecure',
            'remember' => true,
        ])->assertRedirect(route('chat.index'));

        $this->assertAuthenticatedAs($user);

        $this->post('/logout')
            ->assertRedirect(route('landing'));

        $this->assertGuest();
    }

    public function test_guest_is_redirected_to_login_when_accessing_chat_routes(): void
    {
        $this->get('/chats')->assertRedirect('/login');
        $this->get('/settings')->assertRedirect('/login');
        $this->get('/usage')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_workspace_pages(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/settings')
            ->assertOk();

        $this->actingAs($user)
            ->get('/usage')
            ->assertOk();
    }
}
