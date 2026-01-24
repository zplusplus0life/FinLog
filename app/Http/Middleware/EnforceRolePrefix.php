<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class EnforceRolePrefix
{
    /**
     * Enforce that the authenticated user's role matches any role prefix in the URL.
     * Example: /admin/* only accessible by role 'admin'.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect('/');
        }

        $user = Auth::user();
        $userRole = strtolower($user->role ?? '');

        // Map url prefixes to roles
        $prefixRoleMap = [
            'admin' => 'admin',
            'manajer' => 'manajer',
            'staff' => 'staff',
            'viewer' => 'viewer',
        ];

        $path = ltrim($request->path(), '/');
        $segments = explode('/', $path);
        $first = $segments[0] ?? '';

        // Enhanced security: Check if user is trying to access role-based paths
        if ($first && array_key_exists($first, $prefixRoleMap)) {
            $requiredRole = $prefixRoleMap[$first];
            if ($userRole !== $requiredRole) {
                // Return 404 to avoid disclosing the existence of other role areas
                // Log attempted access for security monitoring
                \Log::warning('Attempted unauthorized access', [
                    'user_id' => $user->id,
                    'user_role' => $userRole,
                    'attempted_role' => $requiredRole,
                    'path' => $request->path(),
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
                abort(403, 'Akses tidak di izinkan');
            }
        }

        // Additional check: Prevent access to any role-specific resource files
        // if ($request->is('files/*')) {
        //     $pathParts = explode('/', $path);
        //     if (count($pathParts) >= 2 && $pathParts[0] === 'files') {
        //         $fileRole = strtolower($pathParts[1] ?? '');
        //         if ($fileRole && $fileRole !== $userRole) {
        //             abort(404, 'File tidak ditemukan');
        //         }
        //     }
        // }

        return $next($request);
    }
}
