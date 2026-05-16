<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class ResetPasswordController extends Controller
{
    public function create(string $token): RedirectResponse|Response
    {
        $user = User::query()
            ->where('reset_token', $token)
            ->where('reset_token_expires_at', '>', now())
            ->first();

        if (! $user) {
            return redirect()
                ->route('password.request')
                ->with('error', 'This reset link is invalid or has expired.');
        }

        return Inertia::render('Auth/ResetPassword', [
            'token' => $token,
            'email' => $user->email,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::query()
            ->where('email', $validated['email'])
            ->where('reset_token', $validated['token'])
            ->where('reset_token_expires_at', '>', now())
            ->first();

        if (! $user) {
            return back()->withErrors([
                'email' => 'This reset link is invalid or has expired.',
            ]);
        }

        $user->forceFill([
            'password' => $validated['password'],
            'reset_token' => null,
            'reset_token_expires_at' => null,
        ])->save();

        return redirect()
            ->route('login')
            ->with('success', 'Password updated successfully. Please sign in.');
    }
}
