<?php

namespace Tests\Feature\Chat;

use App\Models\Conversation;
use App\Models\User;
use App\Services\GroqService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ConversationStreamingTest extends TestCase
{
    use RefreshDatabase;

    public function test_stream_endpoint_returns_chunk_and_complete_events(): void
    {
        $user = User::factory()->create([
            'tokens_used_this_month' => 5,
        ]);

        $conversation = Conversation::factory()->for($user)->create([
            'title' => 'New Chat',
        ]);

        $groqService = Mockery::mock(GroqService::class);
        $groqService
            ->shouldReceive('stream')
            ->once()
            ->andReturnUsing(function (array $messages, callable $onChunk) {
                $onChunk("**Laravel** ");
                $onChunk("is expressive.");

                return [
                    'content' => "<think>hidden</think>**Laravel** is expressive.\n[FOLLOWUP_1]: Want a Laravel example?\n[FOLLOWUP_2]: Should I stream code next?",
                    'prompt_tokens' => 0,
                    'completion_tokens' => 0,
                    'total_tokens' => 0,
                ];
            });

        $this->app->instance(GroqService::class, $groqService);

        $response = $this->withoutHeaders(['X-Inertia', 'X-Inertia-Version', 'X-Requested-With'])
            ->actingAs($user)
            ->post(
            route('conversations.messages.stream', $conversation),
            ['content' => 'Why do developers like Laravel?'],
            ['Accept' => 'text/event-stream']
        );

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/event-stream; charset=UTF-8');

        $stream = $response->streamedContent();

        $this->assertStringContainsString('event: chunk', $stream);
        $this->assertStringContainsString('event: complete', $stream);
        $this->assertStringNotContainsString('<think>', $stream);
        $this->assertStringContainsString('"follow_ups":["Want a Laravel example?","Should I stream code next?"]', $stream);

        $this->assertDatabaseCount('messages', 2);
    }
}
