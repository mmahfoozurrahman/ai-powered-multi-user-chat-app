<?php

namespace Tests\Feature\Chat;

use App\Models\AiSetting;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_free_user_is_blocked_when_daily_message_limit_is_exhausted(): void
    {
        $user = User::factory()->create([
            'plan' => 'free',
        ]);

        $conversation = Conversation::factory()->for($user)->create();

        AiSetting::query()->create([
            'active_model' => 'qwen/qwen3-32b',
            'context_window' => 131072,
            'max_output_tokens' => 40960,
            'reserved_completion_tokens' => 2048,
            'provider_reference_rpm' => 60,
            'provider_reference_rpd' => 1000,
            'provider_reference_tpm' => 6000,
            'provider_reference_tpd' => 500000,
            'free_daily_message_limit' => 1,
            'free_daily_token_limit' => 12000,
            'free_monthly_token_limit' => 150000,
        ]);

        Message::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'role' => 'user',
            'tokens_used' => 50,
            'created_at' => now(),
        ]);

        $this->actingAs($user)
            ->post(route('conversations.messages.store', $conversation), [
                'content' => 'One more message please',
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('messages', 1);
    }

    public function test_request_is_blocked_when_estimated_prompt_exceeds_provider_tpm_limit(): void
    {
        $user = User::factory()->create([
            'plan' => 'free',
        ]);

        $conversation = Conversation::factory()->for($user)->create();

        AiSetting::query()->create([
            'active_model' => 'qwen/qwen3-32b',
            'context_window' => 131072,
            'max_output_tokens' => 40960,
            'reserved_completion_tokens' => 2048,
            'provider_reference_rpm' => 60,
            'provider_reference_rpd' => 1000,
            'provider_reference_tpm' => 50,
            'provider_reference_tpd' => 500000,
            'free_daily_message_limit' => 100,
            'free_daily_token_limit' => 12000,
            'free_monthly_token_limit' => 150000,
        ]);

        $this->actingAs($user)
            ->post(route('conversations.messages.store', $conversation), [
                'content' => str_repeat('A', 220),
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('messages', 0);
    }
}
