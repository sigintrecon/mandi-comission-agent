<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        foreach ($roles as $role) {
            if ($request->user()->role === $role) {
                return $next($request);
            }
        }

        // Check if user has higher role
        $roleHierarchy = [
            'super_admin' => 4,
            'admin' => 3,
            'client_manager' => 2,
            'viewer' => 1,
        ];

        $requiredLevel = $roleHierarchy[$roles[0]] ?? 0;
        $userLevel = $roleHierarchy[$request->user()->role] ?? 0;

        if ($userLevel >= $requiredLevel) {
            return $next($request);
        }

        abort(403, 'Unauthorized access.');
    }
}