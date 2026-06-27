<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }

        return view('portal.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'phone'    => ['required', 'string'],
            'password' => ['required'],
        ]);

        $submitted = trim($request->input('phone'));
        $digits    = preg_replace('/\D/', '', $submitted);

        // Build candidate phone formats to match however the number is stored
        $candidates = array_unique(array_filter([$submitted, $digits]));
        for ($strip = 1; $strip <= 3; $strip++) {
            if (\strlen($digits) > $strip + 7) {
                $local        = substr($digits, $strip);
                $candidates[] = $local;
                $candidates[] = '0' . $local;
            }
        }

        $user = User::whereIn('phone', $candidates)->first();

        if (! $user || ! Hash::check($request->input('password'), $user->password)) {
            return back()
                ->withErrors(['phone' => 'The credentials you entered are incorrect.'])
                ->withInput($request->only('phone'));
        }

        if (! \in_array($user->role, ['admin', 'superadmin', 'client_master', 'client_employee'])) {
            return back()
                ->withErrors(['phone' => 'This portal is for admin and client users only. Please use the mobile app.'])
                ->withInput($request->only('phone'));
        }

        if ($user->status !== 'active') {
            return back()
                ->withErrors(['phone' => 'Your account has been suspended. Please contact support.'])
                ->withInput($request->only('phone'));
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return $this->redirectByRole($user);
    }

    public function showForgotPassword(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }

        return view('portal.auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::ResetLinkSent
            ? back()->with('status', 'A password reset link has been sent to your email address.')
            : back()->withErrors(['email' => __($status)]);
    }

    private function redirectByRole(User $user): RedirectResponse
    {
        if (\in_array($user->role, ['admin', 'superadmin'])) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('client.dashboard');
    }
}
