<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordLinkMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ForgotPasswordController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if ($user) {
            $token = Str::random(64);

            $user->forceFill([
                'reset_token' => $token,
                'reset_token_expires_at' => now()->addHour(),
            ])->save();

            $resetUrl = route('password.reset', $token);

            Mail::to($user->email)->send(new ResetPasswordLinkMail($user->name, $resetUrl));
        }

        return back()->with(
            'success',
            'If we found an account for that email, a reset link has been sent.'
        );
    }
}
