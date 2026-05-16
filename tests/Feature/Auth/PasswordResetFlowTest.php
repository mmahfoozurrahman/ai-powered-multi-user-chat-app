<?php

namespace Tests\Feature\Auth;

use App\Mail\ResetPasswordLinkMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PasswordResetFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_generates_a_reset_token_and_sends_an_email(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'reset@example.com',
        ]);

        $this->post('/forgot-password', [
            'email' => $user->email,
        ])->assertSessionHas('success');

        $user->refresh();

        $this->assertNotNull($user->reset_token);
        $this->assertNotNull($user->reset_token_expires_at);

        Mail::assertSent(ResetPasswordLinkMail::class);
    }

    public function test_user_can_reset_password_with_a_valid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'reset@example.com',
            'reset_token' => 'valid-token',
            'reset_token_expires_at' => now()->addHour(),
            'password' => 'old-password',
        ]);

        $this->get('/reset-password/valid-token')
            ->assertOk();

        $this->post('/reset-password', [
            'token' => 'valid-token',
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect(route('login'));

        $user->refresh();

        $this->assertTrue(Hash::check('new-password', $user->password));
        $this->assertNull($user->reset_token);
        $this->assertNull($user->reset_token_expires_at);
    }

    public function test_invalid_reset_token_redirects_back_to_forgot_password_page(): void
    {
        $this->get('/reset-password/missing-token')
            ->assertRedirect(route('password.request'));
    }
}
