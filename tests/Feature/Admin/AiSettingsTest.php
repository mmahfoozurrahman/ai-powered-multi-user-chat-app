<?php

namespace Tests\Feature\Admin;

use App\Models\AiSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_user_cannot_access_admin_ai_settings(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
        ]);

        $this->actingAs($user)
            ->get('/admin/ai-settings')
            ->assertForbidden();
    }

    public function test_admin_user_can_view_and_update_ai_settings(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $this->actingAs($admin)
            ->get('/admin/ai-settings')
            ->assertOk();

        $this->actingAs($admin)
            ->patch('/admin/ai-settings', [
                'active_model' => 'openai/gpt-oss-20b',
                'groq_api_key' => 'groq-secret-key',
                'system_prompt' => 'You are a focused Laravel assistant that always writes safe production-ready code.',
                'context_window' => 8192,
                'max_output_tokens' => 4096,
                'reserved_completion_tokens' => 1024,
                'provider_reference_rpm' => 30,
                'provider_reference_rpd' => 1000,
                'provider_reference_tpm' => 8000,
                'provider_reference_tpd' => 200000,
                'free_daily_message_limit' => 40,
                'free_daily_token_limit' => 6000,
                'free_monthly_token_limit' => 50000,
            ])
            ->assertRedirect('/admin/ai-settings');

        $this->assertDatabaseHas('ai_settings', [
            'active_model' => 'openai/gpt-oss-20b',
            'context_window' => 8192,
            'free_daily_message_limit' => 40,
            'free_daily_token_limit' => 6000,
            'free_monthly_token_limit' => 50000,
        ]);

        $this->assertSame('openai/gpt-oss-20b', AiSetting::query()->first()->active_model);
        $this->assertSame(
            'You are a focused Laravel assistant that always writes safe production-ready code.',
            AiSetting::query()->first()->system_prompt
        );
        $this->assertNotSame('groq-secret-key', AiSetting::query()->first()->getRawOriginal('groq_api_key'));
        $this->assertSame('groq-secret-key', AiSetting::query()->first()->groq_api_key);
    }
}
