<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActivePlan
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = app()->bound('tenant') ? app('tenant') : null;

        if (!$tenant) return redirect()->route('login');

        if ($tenant->isOnTrial()) return $next($request);

        if ($tenant->isSuspended()) {
            return response()->view('errors.suspended', compact('tenant'), 402);
        }

        if ($tenant->status === 'cancelled') {
            return response()->view('errors.cancelled', compact('tenant'), 402);
        }

        if ($tenant->plan_expires_at && $tenant->plan_expires_at->isPast()) {
            if ($tenant->plan_expires_at->diffInDays(now()) > 7) {
                return response()->view('errors.plan-expired', compact('tenant'), 402);
            }
        }

        return $next($request);
    }
}
