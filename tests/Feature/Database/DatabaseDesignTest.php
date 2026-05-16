<?php

namespace Tests\Feature\Database;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseDesignTest extends TestCase
{
    use RefreshDatabase;

    public function test_conversation_scope_returns_only_the_authenticated_users_records(): void
    {
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();

        $ownedConversation = Conversation::factory()->for($firstUser)->create();
        Conversation::factory()->for($secondUser)->create();

        $result = Conversation::query()
            ->ownedBy($firstUser->id)
            ->pluck('id');

        $this->assertCount(1, $result);
        $this->assertTrue($result->contains($ownedConversation->id));
    }

    public function test_message_scope_only_returns_messages_from_conversations_owned_by_the_user(): void
    {
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();

        $ownedConversation = Conversation::factory()->for($firstUser)->create();
        $otherConversation = Conversation::factory()->for($secondUser)->create();

        $ownedMessage = Message::factory()->forConversation($ownedConversation)->create();

        Message::factory()->forConversation($otherConversation)->create();

        $result = Message::query()
            ->ownedBy($firstUser->id)
            ->pluck('id');

        $this->assertCount(1, $result);
        $this->assertTrue($result->contains($ownedMessage->id));
    }

    public function test_user_relationships_include_conversations_and_messages(): void
    {
        $user = User::factory()->create(['plan' => 'pro']);
        $conversation = Conversation::factory()->for($user)->create();
        $message = Message::factory()->forConversation($conversation)->create();

        $this->assertTrue($user->isPro());
        $this->assertTrue($user->conversations->contains($conversation));
        $this->assertTrue($user->messages->contains($message));
    }
}
