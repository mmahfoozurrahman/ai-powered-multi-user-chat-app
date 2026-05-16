<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Admin\AiSettingsController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WorkspaceController;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Inertia::render('Landing');
})->name('landing');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);

    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);

    Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])->name('password.email');

    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'store'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::post('/conversations', [ConversationController::class, 'store'])->name('conversations.store');
    Route::delete('/conversations/{conversation}', [ConversationController::class, 'destroy'])->name('conversations.destroy');
    Route::post('/conversations/{conversation}/messages', [ConversationController::class, 'send'])
        ->middleware('plan.limit')
        ->name('conversations.messages.store');
    Route::post('/conversations/{conversation}/messages/stream', [ConversationController::class, 'stream'])
        ->middleware('plan.limit')
        ->name('conversations.messages.stream');

    Route::get('/chats', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chats/{conversation}', [ChatController::class, 'show'])->name('chat.show');
    Route::get('/settings', [WorkspaceController::class, 'settings'])->name('settings.index');
    Route::patch('/settings/profile', [ProfileController::class, 'update'])->name('settings.profile.update');
    Route::patch('/settings/password', [ProfileController::class, 'updatePassword'])->name('settings.password.update');
    Route::get('/usage', [WorkspaceController::class, 'usage'])->name('usage.index');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/ai-settings', [AiSettingsController::class, 'edit'])->name('ai-settings.edit');
    Route::patch('/ai-settings', [AiSettingsController::class, 'update'])->name('ai-settings.update');
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
    Route::patch('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
});
