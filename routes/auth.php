<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\User;

// Public routes
Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store'])
        ->name('register.store');



    
    
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::post('login', [AuthenticatedSessionController::class, 'store'])
    ->name('login.store');

// Public email pre-check endpoint (not behind guest) for login
Route::post('login/verify', function (Request $request) {
    $email = (string) $request->input('email');

    if (! $email) {
        return response()->json(['ok' => false, 'message' => 'Email wajib diisi.'], 422);
    }

    $user = User::where('email', $email)->first();

    if (! $user) {
        return response()->json(['ok' => false, 'message' => 'Data tidak valid.'], 422);
    }

    if (array_key_exists('status', $user->getAttributes()) && ! (bool) ($user->status ?? false)) {
        return response()->json(['ok' => false, 'message' => 'Akun Anda tidak aktif. Hubungi administrator.'], 422);
    }

    return response()->json(['ok' => true, 'message' => 'OK']);
})->middleware('web')->name('login.verify');

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});

// Standalone logout route
Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout.standalone');
