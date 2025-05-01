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
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $role)
    {
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
                'data' => ['error' => 'User not authenticated']
            ], Response::HTTP_UNAUTHORIZED);
        }

        if ($request->user()->role !== $role) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
                'data' => [
                    'error' => 'You do not have permission to access this resource.',
                    'user_role' => $request->user()->role,
                    'required_role' => $role
                ]
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
} 