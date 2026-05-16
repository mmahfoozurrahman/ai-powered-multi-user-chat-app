<?php

namespace App\Http\Middleware;

use App\Services\AiSettingsService;
use App\Services\UserUsageService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'csrf_token' => csrf_token(),
            'appName' => config('app.name'),
            'auth' => [
                'user' => fn (): ?array => $this->userData($request->user()),
            ],
            'ai' => fn (): array => app(AiSettingsService::class)->frontendPayload(),
            'flash' => [
                'success' => fn (): ?string => $request->session()->get('success'),
                'error' => fn (): ?string => $request->session()->get('error'),
            ],
        ];
    }

    protected function userData(?Authenticatable $user): ?array
    {
        if (! $user) {
            return null;
        }

        return [
            'id' => $user->getAuthIdentifier(),
            'name' => $user->name,
            'email' => $user->email,
            'profile_image_url' => $user->profileImageUrl(),
            'plan' => $user->plan ?? 'free',
            'is_admin' => (bool) $user->is_admin,
            'tokens_used_this_month' => $user->tokens_used_this_month ?? 0,
            'usage' => app(UserUsageService::class)->snapshot($user, app(AiSettingsService::class)->frontendPayload()),
        ];
    }
}
