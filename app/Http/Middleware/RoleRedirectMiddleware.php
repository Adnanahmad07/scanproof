<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleRedirectMiddleware
{
    private array $except = [
        'logout',
        'r/',
        'track/',
        'scan/',
        'email/',
        'supervisor/set-password/',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();
        $currentPath = $request->path();

        // Allow excluded paths
        foreach ($this->except as $prefix) {
            if (str_starts_with($currentPath, $prefix)) {
                return $next($request);
            }
        }

        $rolePrefix = $user->role->value;

        if ($currentPath === 'dashboard' || $currentPath === '') {
            return redirect()->to($user->role->dashboardPath());
        }

        if (!str_starts_with($currentPath, $rolePrefix)) {
            return redirect()->to($user->role->dashboardPath());
        }

        return $next($request);
    }
}
