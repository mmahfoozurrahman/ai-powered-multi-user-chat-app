<?php

namespace App\Services;

use App\Models\Message;
use App\Models\User;
use Carbon\CarbonInterface;

class UserUsageService
{
    public function dailyUserMessageCount(User $user, ?CarbonInterface $date = null): int
    {
        $date = $date ?? now();

        return Message::query()
            ->where('user_id', $user->id)
            ->where('role', 'user')
            ->whereDate('created_at', $date->toDateString())
            ->count();
    }

    public function dailyTokenUsage(User $user, ?CarbonInterface $date = null): int
    {
        $date = $date ?? now();

        return (int) Message::query()
            ->where('user_id', $user->id)
            ->whereDate('created_at', $date->toDateString())
            ->sum('tokens_used');
    }

    public function snapshot(User $user, array $aiSettings): array
    {
        $freeLimits = $aiSettings['plan_limits']['free'] ?? [];
        $dailyMessageLimit = (int) ($freeLimits['daily_message_limit'] ?? 0);
        $dailyTokenLimit = (int) ($freeLimits['daily_token_limit'] ?? 0);
        $monthlyTokenLimit = (int) ($freeLimits['monthly_token_limit'] ?? 0);

        $dailyMessagesUsed = $this->dailyUserMessageCount($user);
        $dailyTokensUsed = $this->dailyTokenUsage($user);
        $monthlyTokensUsed = (int) ($user->tokens_used_this_month ?? 0);

        return [
            'daily_messages_used' => $dailyMessagesUsed,
            'daily_messages_remaining' => max(0, $dailyMessageLimit - $dailyMessagesUsed),
            'daily_message_limit' => $dailyMessageLimit,
            'daily_tokens_used' => $dailyTokensUsed,
            'daily_tokens_remaining' => max(0, $dailyTokenLimit - $dailyTokensUsed),
            'daily_token_limit' => $dailyTokenLimit,
            'monthly_tokens_used' => $monthlyTokensUsed,
            'monthly_tokens_remaining' => max(0, $monthlyTokenLimit - $monthlyTokensUsed),
            'monthly_token_limit' => $monthlyTokenLimit,
        ];
    }
}
