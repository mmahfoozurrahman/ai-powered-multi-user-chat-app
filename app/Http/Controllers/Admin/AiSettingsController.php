<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAiSettingsRequest;
use App\Services\AiSettingsService;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class AiSettingsController extends Controller
{
    public function __construct(
        private readonly AiSettingsService $aiSettingsService,
    ) {
    }

    public function edit(): Response
    {
        return Inertia::render('Admin/AiSettings', [
            'settings' => $this->aiSettingsService->adminSettingsPayload(),
            'hasApiKey' => filled($this->aiSettingsService->apiKey()),
        ]);
    }

    public function update(UpdateAiSettingsRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if ($validated['reserved_completion_tokens'] >= $validated['context_window']) {
            return back()->withErrors([
                'reserved_completion_tokens' => 'Reserved completion tokens must stay below the model context window.',
            ]);
        }

        if ($validated['max_output_tokens'] > $validated['context_window']) {
            return back()->withErrors([
                'max_output_tokens' => 'Max output tokens cannot exceed the model context window.',
            ]);
        }

        $settings = $this->aiSettingsService->current();

        if (blank($validated['groq_api_key'] ?? null)) {
            unset($validated['groq_api_key']);
        }

        $settings->update($validated);

        return redirect()
            ->route('admin.ai-settings.edit')
            ->with('success', 'AI model settings were updated successfully.');
    }
}
