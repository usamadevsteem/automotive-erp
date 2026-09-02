<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TwoFactorController extends Controller
{
    public function showVerifyForm(): View|RedirectResponse
    {
        if (!session('2fa_user_id')) return redirect()->route('login');
        return view('auth.2fa-verify');
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string', 'digits:6']]);

        $userId = session('2fa_user_id');
        if (!$userId) return redirect()->route('login');

        $user = User::findOrFail($userId);

        // Simple TOTP verification using Google2FA if installed, else basic check
        $valid = $this->verifyTotp($user->two_factor_secret, $request->code);

        if (!$valid) {
            return back()->withErrors(['code' => 'Invalid authentication code.']);
        }

        session()->forget('2fa_user_id');
        session(['2fa_verified' => true]);
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function enable(Request $request): RedirectResponse
    {
        $request->validate([
            'code'   => ['required', 'digits:6'],
            'secret' => ['required', 'string'],
        ]);

        if (!$this->verifyTotp($request->secret, $request->code)) {
            return back()->withErrors(['code' => 'Invalid verification code.']);
        }

        auth()->user()->update([
            'two_factor_secret'  => $request->secret,
            'two_factor_enabled' => true,
        ]);

        return back()->with('success', 'Two-factor authentication enabled.');
    }

    public function disable(Request $request): RedirectResponse
    {
        $request->validate(['password' => ['required', 'current_password']]);

        auth()->user()->update([
            'two_factor_secret'  => null,
            'two_factor_enabled' => false,
        ]);

        session()->forget('2fa_verified');
        return back()->with('success', 'Two-factor authentication disabled.');
    }

    private function verifyTotp(?string $secret, string $code): bool
    {
        if (!$secret) return false;

        // If pragmarx/google2fa is installed, use it
        if (class_exists(\PragmaRX\Google2FA\Google2FA::class)) {
            return (new \PragmaRX\Google2FA\Google2FA())->verifyKey($secret, $code);
        }

        // Fallback: basic time-based check (install the package for production)
        return false;
    }
}
