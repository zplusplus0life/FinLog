<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            // Jika pengguna belum login, arahkan ke halaman login.
            return redirect('login');
        }

        $user = Auth::user();

        // Periksa apakah peran pengguna ada di dalam daftar peran yang diizinkan.
        $userRole = strtolower($user->role ?? '');
        foreach ($roles as $role) {
            if ($userRole == strtolower($role)) {
                return $next($request);
            }
        }

        // Jika peran tidak sesuai, lempar error 403 (Forbidden).
        abort(403, 'ANDA TIDAK MEMILIKI AKSES KE HALAMAN INI.');
    }
}