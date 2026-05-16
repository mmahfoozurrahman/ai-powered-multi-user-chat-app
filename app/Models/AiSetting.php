<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiSetting extends Model
{
    protected $fillable = [
        'active_model',
        'groq_api_key',
        'system_prompt',
        'context_window',
        'max_output_tokens',
        'reserved_completion_tokens',
        'provider_reference_rpm',
        'provider_reference_rpd',
        'provider_reference_tpm',
        'provider_reference_tpd',
        'free_daily_message_limit',
        'free_daily_token_limit',
        'free_monthly_token_limit',
    ];

    protected function casts(): array
    {
        return [
            'groq_api_key' => 'encrypted',
            'context_window' => 'integer',
            'max_output_tokens' => 'integer',
            'reserved_completion_tokens' => 'integer',
            'provider_reference_rpm' => 'integer',
            'provider_reference_rpd' => 'integer',
            'provider_reference_tpm' => 'integer',
            'provider_reference_tpd' => 'integer',
            'free_daily_message_limit' => 'integer',
            'free_daily_token_limit' => 'integer',
            'free_monthly_token_limit' => 'integer',
        ];
    }
}
