<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        if (!$user) return redirect()->route('login');

        if ($user->hasTwoFactorEnabled() && !session('2fa_verified')) {
            return redirect()->route('2fa.verify');
        }

        return $next($request);
    }
}
