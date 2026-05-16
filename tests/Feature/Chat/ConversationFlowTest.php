<?php

namespace Tests\Feature\Chat;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ConversationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_a_conversation(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('conversations.store'));

        $conversation = Conversation::first();

        $response->assertRedirect(route('chat.show', $conversation));
        $this->assertNotNull($conversation);
        $this->assertSame($user->id, $conversation->user_id);
        $this->assertSame('New Chat', $conversation->title);
    }

    public function test_authenticated_user_can_delete_their_own_conversation(): void
    {
        $user = User::factory()->create();
        $conversation = Conversation::factory()->for($user)->create();

        $this->actingAs($user)
            ->delete(route('conversations.destroy', $conversation))
            ->assertRedirect(route('chat.index'));

        $this->assertDatabaseMissing('conversations', [
            'id' => $conversation->id,
        ]);
    }

    public function test_user_cannot_delete_another_users_conversation(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $conversation = Conversation::factory()->for($otherUser)->create();

        $this->actingAs($user)
            ->deleteJson(route('conversations.destroy', $conversation))
            ->assertNotFound();
    }

    public function test_message_send_stores_user_and_assistant_messages_and_updates_usage(): void
    {
        config()->set('services.groq.api_key', 'test-key');

        Http::fake([
            'https://api.groq.com/openai/v1/chat/completions' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => "This explains how to wire the chat flow.\n[FOLLOWUP_1]: How should we persist follow-up suggestions?\n[FOLLOWUP_2]: Can we add streaming next?",
                    ],
                ]],
                'usage' => [
                    'prompt_tokens' => 25,
                    'completion_tokens' => 35,
                    'total_tokens' => 60,
                ],
            ]),
        ]);

        $user = User::factory()->create([
            'tokens_used_this_month' => 10,
        ]);

        $conversation = Conversation::factory()->for($user)->create([
            'title' => 'New Chat',
        ]);

        $this->actingAs($user)
            ->post(route('conversations.messages.store', $conversation), [
                'content' => 'How do I connect Laravel to Groq?',
            ])
            ->assertRedirect(route('chat.show', $conversation));

        $this->assertDatabaseCount('messages', 2);
        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => 'How do I connect Laravel to Groq?',
        ]);
        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
        ]);

        $conversation->refresh();
        $user->refresh();

        $this->assertNotSame('New Chat', $conversation->title);
        $this->assertSame(70, $user->tokens_used_this_month);
    }

    public function test_missing_groq_key_returns_error_without_storing_failed_message(): void
    {
        config()->set('services.groq.api_key', null);

        $user = User::factory()->create();
        $conversation = Conversation::factory()->for($user)->create();

        $this->actingAs($user)
            ->post(route('conversations.messages.store', $conversation), [
                'content' => 'Hello there',
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('messages', 0);
    }
}
