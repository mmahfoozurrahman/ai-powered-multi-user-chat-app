<?php

namespace App\Http\Middleware;

use App\Services\AiSettingsService;
use App\Services\UserUsageService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPlanLimit
{
    public function __construct(
        private readonly AiSettingsService $aiSettingsService,
        private readonly UserUsageService $userUsageService,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->plan !== 'free') {
            return $next($request);
        }

        $usage = $this->userUsageService->snapshot($user, $this->aiSettingsService->frontendPayload());

        if ($usage['daily_messages_remaining'] <= 0) {
            return $this->deny($request, 'The free plan daily message limit has been reached for today.');
        }

        if ($usage['daily_tokens_remaining'] <= 0) {
            return $this->deny($request, 'The free plan daily token allowance has been fully used for today.');
        }

        if ($usage['monthly_tokens_remaining'] <= 0) {
            return $this->deny($request, 'The free plan monthly token allowance has been fully used.');
        }

        return $next($request);
    }

    private function deny(Request $request, string $message): Response
    {
        if ($request->expectsJson() || $request->is('conversations/*/messages/stream')) {
            return response()->json(['message' => $message], 429);
        }

        return redirect()->back()->with('error', $message);
    }
}
