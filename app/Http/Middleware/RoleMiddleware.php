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

    // Let Spatie do the check internally
    if (!$user->hasRole($role)) {
        return response()->json([
            'message' => 'Unauthorized, role mismatch',
            'your_role' => $user->getRoleNames(),
            'required_role' => $role
        ], 403);
    }

    return $next($request);
}

}
