<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if ($this->isAuthenticatedAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            if (! in_array($user->role, ['admin', 'superadmin'])) {
                Auth::logout();
                return back()
                    ->withErrors(['email' => 'You do not have admin access.'])
                    ->withInput($request->only('email'));
            }

            if ($user->status !== 'active') {
                Auth::logout();
                return back()
                    ->withErrors(['email' => 'Your account has been suspended. Contact the superadmin.'])
                    ->withInput($request->only('email'));
            }

            $request->session()->regenerate();

            return redirect()->route('admin.dashboard');
        }

        return back()
            ->withErrors(['email' => 'The credentials you entered are incorrect.'])
            ->withInput($request->only('email'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    public function showForgotPassword(): View|RedirectResponse
    {
        if ($this->isAuthenticatedAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::ResetLinkSent
            ? back()->with('status', 'Password reset link sent! Check your inbox.')
            : back()->withErrors(['email' => __($status)]);
    }

    private function isAuthenticatedAdmin(): bool
    {
        return Auth::check() && in_array(Auth::user()->role, ['admin', 'superadmin']);
    }
}
