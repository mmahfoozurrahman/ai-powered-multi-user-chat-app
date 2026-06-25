<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAiSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'active_model' => ['required', 'string', 'max:255'],
            'reasoning_effort' => ['required', 'string', 'in:low,medium,high'],
            'groq_api_key' => ['nullable', 'string', 'max:5000'],
            'system_prompt' => ['required', 'string', 'min:20'],
            'context_window' => ['required', 'integer', 'min:1024'],
            'max_output_tokens' => ['required', 'integer', 'min:256'],
            'reserved_completion_tokens' => ['required', 'integer', 'min:128'],
            'provider_reference_rpm' => ['nullable', 'integer', 'min:1'],
            'provider_reference_rpd' => ['nullable', 'integer', 'min:1'],
            'provider_reference_tpm' => ['nullable', 'integer', 'min:1'],
            'provider_reference_tpd' => ['nullable', 'integer', 'min:1'],
            'free_daily_message_limit' => ['required', 'integer', 'min:1'],
            'free_daily_token_limit' => ['required', 'integer', 'min:1'],
            'free_monthly_token_limit' => ['required', 'integer', 'min:1'],
        ];
    }
}
