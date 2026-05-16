<?php

namespace App\Http\Controllers;

use App\Services\AiSettingsService;
use Inertia\Inertia;
use Inertia\Response;

class WorkspaceController extends Controller
{
    public function __construct(
        private readonly AiSettingsService $aiSettingsService,
    ) {
    }

    public function settings(): Response
    {
        return Inertia::render('Settings/Index', [
            'aiSettings' => $this->aiSettingsService->publicSettingsPayload(),
            'hasApiKey' => filled($this->aiSettingsService->apiKey()),
        ]);
    }

    public function usage(): Response
    {
        return Inertia::render('Usage/Index', [
            'aiSettings' => $this->aiSettingsService->publicSettingsPayload(),
        ]);
    }
}
