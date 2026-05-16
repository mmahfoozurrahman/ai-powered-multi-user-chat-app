<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_profile_details_and_profile_image(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'password' => 'supersecure',
        ]);

        $response = $this->actingAs($user)->patch('/settings/profile', [
            'name' => 'Updated Developer',
            'email' => 'updated@example.com',
            'profile_image' => UploadedFile::fake()->image('developer-avatar.png'),
        ]);

        $response->assertRedirect('/settings');

        $updatedUser = $user->fresh();

        $this->assertSame('Updated Developer', $updatedUser->name);
        $this->assertSame('updated@example.com', $updatedUser->email);
        $this->assertNotNull($updatedUser->profile_image_path);
        $this->assertMatchesRegularExpression(
            '/^profile-images\/\d{8}_\d{6}_user-'.$updatedUser->id.'_developer-avatar\.(jpg|jpeg|png)$/',
            $updatedUser->profile_image_path
        );

        Storage::disk('public')->assertExists($updatedUser->profile_image_path);
    }

    public function test_user_can_update_password_from_settings(): void
    {
        $user = User::factory()->create([
            'password' => 'supersecure',
        ]);

        $this->actingAs($user)
            ->patch('/settings/password', [
                'current_password' => 'supersecure',
                'password' => 'newersecure',
                'password_confirmation' => 'newersecure',
            ])
            ->assertRedirect('/settings');

        $this->assertTrue(Hash::check('newersecure', $user->fresh()->password));
    }
}
