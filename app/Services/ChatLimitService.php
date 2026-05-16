<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Support\TokenEstimator;
use RuntimeException;

class ChatLimitService
{
    public function __construct(
        private readonly AiSettingsService $aiSettingsService,
        private readonly UserUsageService $userUsageService,
    ) {
    }

    public function guard(User $user, Conversation $conversation, string $content): array
    {
        $settings = $this->aiSettingsService->current();
        $aiPayload = $this->aiSettingsService->frontendPayload();
        $usage = $this->userUsageService->snapshot($user, $aiPayload);

        $historyMessages = $conversation->messages()
            ->orderBy('created_at')
            ->get(['content']);

        $systemPromptTokens = TokenEstimator::estimateString($this->aiSettingsService->systemPrompt());
        $historyTokens = TokenEstimator::estimateMessages($historyMessages);
        $inputTokens = TokenEstimator::estimateString($content);
        $estimatedPromptTokens = $systemPromptTokens + $historyTokens + $inputTokens;
        $providerTpmLimit = (int) ($settings->provider_reference_tpm ?? 0);

        if ($estimatedPromptTokens > ($settings->context_window - $settings->reserved_completion_tokens)) {
            throw new RuntimeException('This conversation is getting too large for the active model. Start a new chat or shorten the prompt to continue.');
        }

        if ($providerTpmLimit > 0 && $estimatedPromptTokens > $providerTpmLimit) {
            throw new RuntimeException("This prompt is larger than the configured TPM safety limit of {$providerTpmLimit} tokens for the active model.");
        }

        if ($user->plan === 'free') {
            if ($usage['daily_messages_remaining'] <= 0) {
                throw new RuntimeException('The free plan daily message limit has been reached for today.');
            }

            if ($usage['daily_tokens_remaining'] <= 0) {
                throw new RuntimeException('The free plan daily token allowance has been fully used for today.');
            }

            if ($usage['monthly_tokens_remaining'] <= 0) {
                throw new RuntimeException('The free plan monthly token allowance has been fully used.');
            }

            if ($estimatedPromptTokens > $usage['daily_tokens_remaining']) {
                throw new RuntimeException('This prompt would exceed the remaining free-plan token allowance available for today.');
            }

            if ($estimatedPromptTokens > $usage['monthly_tokens_remaining']) {
                throw new RuntimeException('This prompt would exceed the remaining free-plan token allowance available this month.');
            }
        }

        return [
            'system_prompt_tokens' => $systemPromptTokens,
            'history_tokens' => $historyTokens,
            'input_tokens' => $inputTokens,
            'estimated_prompt_tokens' => $estimatedPromptTokens,
            'usage' => $usage,
            'settings' => $aiPayload,
        ];
    }
}
