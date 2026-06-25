<?php

namespace App\Services;

use App\Models\AiSetting;
use App\Support\AssistantPrompt;
use App\Support\TokenEstimator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;

class AiSettingsService
{
    public function current(): AiSetting
    {
        if (! Schema::hasTable('ai_settings')) {
            return new AiSetting($this->defaults());
        }

        return AiSetting::query()->firstOrCreate([], $this->defaults());
    }

    public function defaults(): array
    {
        return [
            'active_model' => config('services.groq.model', 'qwen/qwen3-32b'),
            'reasoning_effort' => 'medium',
            'groq_api_key' => config('services.groq.api_key'),
            'system_prompt' => AssistantPrompt::CONTENT,
            'context_window' => 131072,
            'max_output_tokens' => 40960,
            'reserved_completion_tokens' => 2048,
            'provider_reference_rpm' => 60,
            'provider_reference_rpd' => 1000,
            'provider_reference_tpm' => 6000,
            'provider_reference_tpd' => 500000,
            'free_daily_message_limit' => 100,
            'free_daily_token_limit' => 12000,
            'free_monthly_token_limit' => 150000,
        ];
    }

    public function frontendPayload(): array
    {
        $settings = $this->current();
        $systemPrompt = $this->systemPrompt();

        return [
            'model' => [
                'id' => $settings->active_model,
                'context_window' => $settings->context_window,
                'max_output_tokens' => $settings->max_output_tokens,
                'reserved_completion_tokens' => $settings->reserved_completion_tokens,
                'system_prompt_tokens' => TokenEstimator::estimateString($systemPrompt),
                'max_prompt_tokens' => max(0, $settings->context_window - $settings->reserved_completion_tokens),
            ],
            'provider_limits' => [
                'rpm' => $settings->provider_reference_rpm,
                'rpd' => $settings->provider_reference_rpd,
                'tpm' => $settings->provider_reference_tpm,
                'tpd' => $settings->provider_reference_tpd,
            ],
            'plan_limits' => [
                'free' => [
                    'daily_message_limit' => $settings->free_daily_message_limit,
                    'daily_token_limit' => $settings->free_daily_token_limit,
                    'monthly_token_limit' => $settings->free_monthly_token_limit,
                ],
            ],
        ];
    }

    public function adminSettingsPayload(): array
    {
        return Arr::except($this->current()->toArray(), ['groq_api_key']);
    }

    public function publicSettingsPayload(): array
    {
        return Arr::except($this->current()->toArray(), ['groq_api_key', 'system_prompt']);
    }

    public function apiKey(): ?string
    {
        return $this->current()->groq_api_key ?: config('services.groq.api_key');
    }

    public function systemPrompt(): string
    {
        return $this->current()->system_prompt ?: AssistantPrompt::CONTENT;
    }
}
