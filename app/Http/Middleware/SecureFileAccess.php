<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SecureFileAccess
{
    /**
     * Handle an incoming request for file access with role-based security
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            abort(404, 'File tidak ditemukan.');
        }

        $user = Auth::user();
        $userRole = strtolower($user->role ?? '');

        // Get the role from the route parameter
        $requestedRole = strtolower($request->route('role') ?? '');
        
        // Allowed roles
        $allowedRoles = ['admin', 'manajer', 'staff'];
        
        // Check if user's role is valid
        if (!in_array($userRole, $allowedRoles)) {
            abort(404, 'File tidak ditemukan.');
        }

        // Check if requested role matches user's role
        if ($userRole !== $requestedRole) {
            abort(404, 'File tidak ditemukan.');
        }

        // Additional security checks for file requests
        if ($request->has('filename')) {
            $filename = $request->get('filename') ?? $request->route('filename');
            
            // Prevent directory traversal attacks
            if (strpos($filename, '..') !== false || 
                strpos($filename, '/') !== false || 
                strpos($filename, '\\') !== false) {
                abort(404, 'File tidak ditemukan.');
            }
            
            // Ensure filename is not empty
            if (empty(trim($filename))) {
                abort(404, 'File tidak ditemukan.');
            }
        }

        return $next($request);
    }
}