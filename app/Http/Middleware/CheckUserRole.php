<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckUserRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->get('auth_user');

        if (!$user || !isset($user['role'])) {
            return response()->json(['error' => 'Unauthorized - role not found'], 403);
        }

        // Admin puede hacer cualquier cosa
        if ($user['role'] === 'admin') {
            return $next($request);
        }

        // Chequeo normal
        if (!in_array($user['role'], $roles)) {
            return response()->json(['error' => 'Forbidden - insufficient role'], 403);
        }

        return $next($request);
    }
}
