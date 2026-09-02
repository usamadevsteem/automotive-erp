<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) return redirect()->route('dashboard');
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $this->checkRateLimit($request);

        // On the central Vercel domain there is no tenant in the request host.
        // Find the active user first, then establish that user's tenant context.
        $user = User::where('email', $request->email)
                    ->where('is_active', true)
                    ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            // A tenant is not available on the central login page, so there is
            // no safe tenant_id to write to login_logs for an unknown user.
            $this->incrementRateLimit($request);
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        $tenant = app()->bound('tenant') ? app('tenant') : $user->tenant;

        if (!$tenant) {
            throw ValidationException::withMessages([
                'email' => 'This account is not linked to a dealership.',
            ]);
        }

        // Establish tenant context for all tenant-scoped models and permissions.
        app()->instance('tenant', $tenant);
        setPermissionsTeamId($tenant->id);

        $this->logAttempt($request, $user->id, $tenant->id, 'success');
        $this->clearRateLimit($request);

        if ($user->hasTwoFactorEnabled()) {
            session(['2fa_user_id' => $user->id]);
            return redirect()->route('2fa.verify');
        }

        Auth::login($user, $request->boolean('remember'));
        $user->update(['last_login_at' => now()]);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    private function checkRateLimit(Request $request): void
    {
        $key = 'login_attempts:' . $request->ip();
        if (cache()->get($key, 0) >= 5) {
            throw ValidationException::withMessages([
                'email' => 'Too many login attempts. Please try again in a minute.',
            ]);
        }
    }

    private function incrementRateLimit(Request $request): void
    {
        $key = 'login_attempts:' . $request->ip();
        cache()->put($key, cache()->get($key, 0) + 1, 60);
    }

    private function clearRateLimit(Request $request): void
    {
        cache()->forget('login_attempts:' . $request->ip());
    }

    private function logAttempt(Request $request, ?int $userId, int $tenantId, string $status): void
    {
        LoginLog::create([
            'user_id'    => $userId,
            'tenant_id'  => $tenantId,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status'     => $status,
        ]);
    }
}
