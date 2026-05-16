<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\UpdatePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->safe()->only(['name', 'email']);

        if ($request->hasFile('profile_image')) {
            $file = $request->file('profile_image');
            $baseName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'profile-image';
            $filename = now()->format('Ymd_His')."_user-{$user->id}_{$baseName}";
            $extension = $file->getClientOriginalExtension();
            $path = $file->storeAs('profile-images', "{$filename}.{$extension}", 'public');

            if ($user->profile_image_path) {
                Storage::disk('public')->delete($user->profile_image_path);
            }

            $validated['profile_image_path'] = $path;
        }

        $user->update($validated);

        return redirect()
            ->route('settings.index')
            ->with('success', 'Your profile details were updated successfully.');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        return redirect()
            ->route('settings.index')
            ->with('success', 'Your password was updated successfully.');
    }
}
