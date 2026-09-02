<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPlanLimits
{
    public function handle(Request $request, Closure $next, string $resource = ''): Response
    {
        if (!$request->isMethod('POST') || empty($resource)) {
            return $next($request);
        }

        $tenant = app('tenant');
        $plan   = $tenant->plan;

        $current = match ($resource) {
            'users'    => \App\Models\User::count(),
            'branches' => \App\Models\Branch::count(),
            'vehicles' => \App\Models\Vehicle::count(),
            default    => 0,
        };

        $limit = match ($resource) {
            'users'    => $plan->max_users,
            'branches' => $plan->max_branches,
            'vehicles' => $plan->max_vehicles,
            default    => PHP_INT_MAX,
        };

        if ($current >= $limit) {
            $msg = "Your plan allows a maximum of {$limit} {$resource}. Please upgrade your plan.";

            if ($request->expectsJson()) {
                return response()->json(['message' => $msg], 403);
            }

            return back()->with('error', $msg);
        }

        return $next($request);
    }
}
