<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Services\ChatLimitService;
use App\Services\GroqService;
use App\Services\AiSettingsService;
use App\Services\UserUsageService;
use App\Support\AssistantMessageFormatter;
use App\Support\TokenEstimator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use RuntimeException;

class ConversationController extends Controller
{
    public function store(): RedirectResponse
    {
        $conversation = Conversation::create([
            'user_id' => auth()->id(),
            'title' => 'New Chat',
        ]);

        return redirect()
            ->route('chat.show', $conversation)
            ->with('success', 'A new conversation is ready.');
    }

    public function destroy(Conversation $conversation): RedirectResponse
    {
        $this->ensureOwnership($conversation);

        $conversation->delete();

        return redirect()
            ->route('chat.index')
            ->with('success', 'Conversation deleted successfully.');
    }

    public function send(Request $request, Conversation $conversation, GroqService $groqService, ChatLimitService $chatLimitService): RedirectResponse
    {
        $this->ensureOwnership($conversation);

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:20000'],
        ]);

        $userMessage = trim($validated['content']);

        try {
            $chatLimitService->guard($request->user(), $conversation, $userMessage);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        $userRecord = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => auth()->id(),
            'role' => 'user',
            'content' => $userMessage,
            'tokens_used' => TokenEstimator::estimateString($userMessage),
        ]);

        if ($conversation->title === 'New Chat') {
            $conversation->update([
                'title' => $this->generateTitle($userMessage),
            ]);
        }

        try {
            $response = $groqService->chat(
                $conversation->messages()
                    ->orderBy('created_at')
                    ->get()
                    ->map(fn (Message $message) => [
                        'role' => $message->role,
                        'content' => $message->content,
                    ])
                    ->all()
            );
        } catch (RuntimeException $exception) {
            $userRecord->delete();

            return back()->with('error', $exception->getMessage());
        }

        Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => auth()->id(),
            'role' => 'assistant',
            'content' => $response['content'],
            'tokens_used' => $response['completion_tokens'] ?: TokenEstimator::estimateString($response['content']),
        ]);

        $conversation->touch();

        auth()->user()?->increment(
            'tokens_used_this_month',
            $response['total_tokens'] ?: ($userRecord->tokens_used + TokenEstimator::estimateString($response['content']))
        );

        return redirect()
            ->route('chat.show', $conversation)
            ->with('success', 'Groq responded successfully.');
    }

    public function stream(
        Request $request,
        Conversation $conversation,
        GroqService $groqService,
        ChatLimitService $chatLimitService,
        AiSettingsService $aiSettingsService,
        UserUsageService $userUsageService,
    ): Response
    {
        $this->ensureOwnership($conversation);

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:20000'],
        ]);

        $user = $request->user();
        $userMessage = trim($validated['content']);

        try {
            $chatLimitService->guard($user, $conversation, $userMessage);
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 429);
        }

        $userRecord = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'role' => 'user',
            'content' => $userMessage,
            'tokens_used' => TokenEstimator::estimateString($userMessage),
        ]);

        if ($conversation->title === 'New Chat') {
            $conversation->update([
                'title' => $this->generateTitle($userMessage),
            ]);
        }

        $messages = $conversation->messages()
            ->orderBy('created_at')
            ->get()
            ->map(fn (Message $message) => [
                'role' => $message->role,
                'content' => $message->content,
            ])
            ->all();

        return response()->stream(function () use (
            $groqService,
            $conversation,
            $messages,
            $user,
            $userRecord,
            $aiSettingsService,
            $userUsageService,
        ) {
            @ini_set('output_buffering', 'off');
            @ini_set('zlib.output_compression', '0');
            @ini_set('implicit_flush', '1');

            $sendEvent = function (string $event, array $data): void {
                echo "event: {$event}\n";
                echo 'data: '.json_encode($data, JSON_UNESCAPED_UNICODE)."\n\n";

                while (ob_get_level() > 0) {
                    ob_flush();
                    flush();
                    break;
                }

                flush();
            };

            echo ': stream-open '.str_repeat(' ', 2048)."\n\n";
            flush();

            try {
                $response = $groqService->stream($messages, function (string $chunk) use ($sendEvent) {
                    $sendEvent('chunk', ['content' => $chunk]);
                });

                $assistant = Message::create([
                    'conversation_id' => $conversation->id,
                    'user_id' => $user->id,
                    'role' => 'assistant',
                    'content' => $response['content'],
                    'tokens_used' => $response['completion_tokens'] ?: TokenEstimator::estimateString($response['content']),
                ]);

                $conversation->touch();

                $user->increment(
                    'tokens_used_this_month',
                    $response['total_tokens'] ?: ($userRecord->tokens_used + TokenEstimator::estimateString($response['content']))
                );

                $sendEvent('complete', [
                    'conversation' => [
                        'id' => $conversation->id,
                        'title' => $conversation->fresh()->title,
                        'updated_at' => $conversation->fresh()->updated_at?->toIso8601String(),
                    ],
                    'message' => $this->transformMessage($assistant->fresh()),
                    'tokens_used_this_month' => $user->fresh()->tokens_used_this_month,
                    'usage' => $userUsageService->snapshot($user->fresh(), $aiSettingsService->frontendPayload()),
                ]);
            } catch (RuntimeException $exception) {
                $userRecord->delete();

                $sendEvent('error', [
                    'message' => $exception->getMessage(),
                ]);
            }
        }, 200, [
            'Cache-Control' => 'no-cache, no-transform',
            'Connection' => 'keep-alive',
            'Content-Type' => 'text/event-stream',
            'Content-Encoding' => 'identity',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    private function ensureOwnership(Conversation $conversation): void
    {
        abort_unless($conversation->user_id === auth()->id(), 404);
    }

    private function generateTitle(string $content): string
    {
        return Str::of($content)
            ->squish()
            ->limit(60, '...')
            ->value();
    }

    private function transformMessage(Message $message): array
    {
        $parsed = AssistantMessageFormatter::parse($message->content);

        return [
            'id' => $message->id,
            'role' => $message->role,
            'content' => $parsed['content'],
            'follow_ups' => $parsed['follow_ups'],
            'created_at' => $message->created_at?->toIso8601String(),
            'tokens_used' => $message->tokens_used,
        ];
    }

}
