<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Anda harus login terlebih dahulu.');
        }

        $user = Auth::user();

        // Check if user is active
        if (!$user->isActive()) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Akun Anda tidak aktif. Silakan hubungi administrator.');
        }

        // Check if user has required role
        if (!empty($roles) && !in_array($user->role, $roles)) {
            // Redirect based on user's actual role
            switch ($user->role) {
                case 'admin':
                    return redirect()->route('admin.dashboard')->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
                case 'staff':
                    return redirect()->route('staff.dashboard')->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
                case 'client':
                    return redirect()->route('client.dashboard')->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
                default:
                    return redirect()->route('login')->with('error', 'Role tidak valid.');
            }
        }

        return $next($request);
    }
}
