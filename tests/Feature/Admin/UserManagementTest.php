<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_user_cannot_access_admin_users_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin/users')
            ->assertForbidden();
    }

    public function test_admin_user_can_create_update_and_delete_users(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $this->actingAs($admin)
            ->post('/admin/users', [
                'name' => 'Workspace Member',
                'email' => 'member@example.com',
                'plan' => 'pro',
                'is_admin' => false,
                'password' => 'supersecure',
                'password_confirmation' => 'supersecure',
            ])
            ->assertRedirect('/admin/users');

        $user = User::query()->where('email', 'member@example.com')->first();

        $this->assertNotNull($user);

        $this->actingAs($admin)
            ->patch("/admin/users/{$user->id}", [
                'name' => 'Updated Member',
                'email' => 'member@example.com',
                'plan' => 'free',
                'is_admin' => false,
                'password' => '',
                'password_confirmation' => '',
            ])
            ->assertRedirect('/admin/users');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Member',
            'plan' => 'free',
        ]);

        $this->actingAs($admin)
            ->delete("/admin/users/{$user->id}")
            ->assertRedirect('/admin/users');

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    public function test_last_admin_cannot_be_demoted(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $this->actingAs($admin)
            ->patch("/admin/users/{$admin->id}", [
                'name' => $admin->name,
                'email' => $admin->email,
                'plan' => 'free',
                'is_admin' => false,
                'password' => '',
                'password_confirmation' => '',
            ])
            ->assertSessionHasErrors('is_admin');
    }
}
