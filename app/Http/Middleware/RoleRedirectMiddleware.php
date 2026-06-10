<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleRedirectMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();
        $currentPath = $request->path();

        $rolePaths = [
            UserRole::Admin->value => 'admin',
            UserRole::Supervisor->value => 'supervisor',
            UserRole::Staff->value => 'staff',
            UserRole::Client->value => 'client',
        ];

        $rolePrefix = $rolePaths[$user->role->value] ?? 'dashboard';

        if ($currentPath === 'dashboard' || $currentPath === '') {
            return redirect()->to($user->role->dashboardPath());
        }

        if (!str_starts_with($currentPath, $rolePrefix) && !str_starts_with($currentPath, 'logout')) {
            return redirect()->to($user->role->dashboardPath());
        }

        return $next($request);
    }
}
