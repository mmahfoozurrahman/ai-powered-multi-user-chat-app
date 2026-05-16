<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Support\AssistantMessageFormatter;
use Inertia\Inertia;
use Inertia\Response;

class ChatController extends Controller
{
    public function index(): Response
    {
        $latestConversation = Conversation::query()
            ->ownedBy(auth()->id())
            ->with(['messages' => fn ($query) => $query->orderBy('created_at')])
            ->latest('updated_at')
            ->first();

        return Inertia::render('Chat/Index', [
            'conversations' => $this->conversationList(),
            'activeConversation' => $latestConversation ? [
                'id' => $latestConversation->id,
                'title' => $latestConversation->title,
                'created_at' => $latestConversation->created_at?->toIso8601String(),
                'updated_at' => $latestConversation->updated_at?->toIso8601String(),
                'messages' => $latestConversation->messages->map(
                    fn (Message $message) => $this->transformMessage($message)
                )->values(),
            ] : null,
        ]);
    }

    public function show(Conversation $conversation): Response
    {
        $conversation = Conversation::query()
            ->ownedBy(auth()->id())
            ->with(['messages' => fn ($query) => $query->orderBy('created_at')])
            ->findOrFail($conversation->id);

        return Inertia::render('Chat/Show', [
            'conversations' => $this->conversationList(),
            'activeConversation' => [
                'id' => $conversation->id,
                'title' => $conversation->title,
                'created_at' => $conversation->created_at?->toIso8601String(),
                'updated_at' => $conversation->updated_at?->toIso8601String(),
                'messages' => $conversation->messages->map(
                    fn (Message $message) => $this->transformMessage($message)
                )->values(),
            ],
        ]);
    }

    private function conversationList()
    {
        return Conversation::query()
            ->ownedBy(auth()->id())
            ->latest('updated_at')
            ->get()
            ->map(fn (Conversation $conversation) => [
                'id' => $conversation->id,
                'title' => $conversation->title,
                'updated_at' => $conversation->updated_at?->toIso8601String(),
            ])
            ->values();
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
