<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized, user not found',
                'your_role' => null,
                'required_role' => $role
            ], 401);
        }

        // Detect guard automatically from the request
        $guard = $request->route()->getPrefix() === 'api' ? 'api' : 'web';

        // Check role using the correct guard
        if (!$user->hasRole($role, $guard)) {
            return response()->json([
                'message' => 'Unauthorized, role mismatch',
                'your_role' => $user->getRoleNames(), // returns all roles of user
                'required_role' => $role
            ], 403);
        }

        return $next($request);
    }
}
