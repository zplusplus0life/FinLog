<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use Illuminate\Validation\ValidationException;

class FortifyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Ignore Fortify default routes to prevent conflicts with our custom auth routes
        \Laravel\Fortify\Fortify::ignoreRoutes();

        Fortify::loginView(fn () => inertia('login'));

        Fortify::authenticateUsing(function (Request $request) {
            $user = \App\Models\User::where('email', $request->email)->first();

            if (!$user) {
                throw ValidationException::withMessages([
                    'email' => ['Email yang Anda masukkan tidak terdaftar.'],
                ]);
            }

            if (!Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['Email atau password yang Anda masukkan salah.'],
                ]);
            }

            return $user;
        });
    }

    public function register(): void
    {
        //
    }
}
