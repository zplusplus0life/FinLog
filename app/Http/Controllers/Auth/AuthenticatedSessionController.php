<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        // Biarkan ValidationException bubble untuk Inertia (422),
        // sehingga useForm.onError terpanggil dan menampilkan toast "Data tidak valid".
        $request->authenticate();
        $request->session()->regenerate();

        // Redirect berdasarkan role
        $user = $request->user();
        $role = strtolower((string) ($user->role ?? ''));

        \Log::info('Login successful', [
            'user_id' => $user->id,
            'email' => $user->email,
            'role' => $role,
            'redirect_url' => $role === 'manajer' ? '/manajer/overview' : 'other'
        ]);

        if ($role === 'admin') {
            return redirect('/admin/overview');
        }
        if ($role === 'manajer' || $role === 'manager') {
            return redirect('/manajer/overview');
        }
        if ($role === 'staff') {
            return redirect('/staff/overview');
        }

        // Fallback: ke halaman home jika role tidak dikenali
        return redirect(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        // Force a fresh page load instead of Inertia redirect
        return redirect('/')->withHeaders(['X-Inertia-Location' => url('url'),]);
    }
}