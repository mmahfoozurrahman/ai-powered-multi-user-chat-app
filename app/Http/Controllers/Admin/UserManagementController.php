<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminUserRequest;
use App\Http\Requests\Admin\UpdateAdminUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class UserManagementController extends Controller
{
    public function index(): Response
    {
        $users = User::query()
            ->latest()
            ->get()
            ->map(fn (User $user) => $this->transformUser($user))
            ->values();

        return Inertia::render('Admin/Users', [
            'users' => $users,
            'summary' => [
                'total_users' => $users->count(),
                'admin_users' => $users->where('is_admin', true)->count(),
                'pro_users' => $users->where('plan', 'pro')->count(),
                'free_users' => $users->where('plan', 'free')->count(),
            ],
        ]);
    }

    public function store(StoreAdminUserRequest $request): RedirectResponse
    {
        $user = User::create($request->validated());

        return redirect()
            ->route('admin.users.index')
            ->with('success', "{$user->name} was created successfully.");
    }

    public function update(UpdateAdminUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();
        $this->guardAdminIntegrity($user, (bool) $validated['is_admin']);

        if (blank($validated['password'] ?? null)) {
            unset($validated['password']);
        }

        unset($validated['password_confirmation']);

        $user->update($validated);

        return redirect()
            ->route('admin.users.index')
            ->with('success', "{$user->name} was updated successfully.");
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->is(auth()->user())) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'You cannot delete the account that is currently signed in.');
        }

        if ($user->is_admin && User::query()->where('is_admin', true)->count() <= 1) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'At least one admin account must remain available.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', "{$name} was removed successfully.");
    }

    private function guardAdminIntegrity(User $user, bool $willRemainAdmin): void
    {
        if ($user->is_admin && ! $willRemainAdmin && User::query()->where('is_admin', true)->count() <= 1) {
            throw ValidationException::withMessages([
                'is_admin' => 'At least one admin account must remain available.',
            ]);
        }
    }

    private function transformUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'plan' => $user->plan,
            'is_admin' => (bool) $user->is_admin,
            'tokens_used_this_month' => $user->tokens_used_this_month,
            'profile_image_url' => $user->profileImageUrl(),
            'created_at' => $user->created_at?->toDateString(),
        ];
    }
}
