<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Branch;
use App\Models\Business;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!$request->user()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Super admin has access to everything
        if ($request->user()->isSuperAdmin()) {
            return $next($request);
        }

        // Check if user has the required role
        if (!in_array($request->user()->role, $roles)) {
            return response()->json(['error' => 'Forbidden - Invalid role'], 403);
        }

        // For business-specific routes
        if ($request->route('business')) {
            $business = Business::find($request->route('business'));
            if (!$business) {
                return response()->json(['error' => 'Business not found'], 404);
            }

            if (!$request->user()->hasAccessToBusiness($business)) {
                return response()->json(['error' => 'Forbidden - No access to this business'], 403);
            }
        }

        // For branch-specific routes
        if ($request->route('branch')) {
            $branch = Branch::find($request->route('branch'));
            if (!$branch) {
                return response()->json(['error' => 'Branch not found'], 404);
            }

            if (!$request->user()->hasAccessToBranch($branch)) {
                return response()->json(['error' => 'Forbidden - No access to this branch'], 403);
            }
        }

        return $next($request);
    }
}
