<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle($request, Closure $next, $role)
    {
        if (!$request->user()) {

        return response()->json([
            'success' => false,
            'code' => 401,
            'message' => 'Unauthenticated'
        ], 401);
    }

    if ($request->user()->role !== $role) {

        return response()->json([
            'success' => false,
            'code' => 403,
            'message' => "Akses ditolak. Endpoint ini hanya untuk role '{$role}'."
        ], 403);
    }

    return $next($request);
    }
}
