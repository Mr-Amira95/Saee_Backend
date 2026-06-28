<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetCode;
use App\Models\User;
use App\Services\WhatsAppService;
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
        if ($this->isAuthenticatedAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'phone'    => ['required', 'string'],
            'password' => ['required'],
        ]);

        $submitted  = trim($request->input('phone'));
        $digits     = preg_replace('/\D/', '', $submitted);
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

        if (! \in_array($user->role, ['admin', 'superadmin'])) {
            return back()
                ->withErrors(['phone' => 'You do not have admin access.'])
                ->withInput($request->only('phone'));
        }

        if ($user->status !== 'active') {
            return back()
                ->withErrors(['phone' => 'Your account has been suspended. Contact the superadmin.'])
                ->withInput($request->only('phone'));
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
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
        $request->validate(['phone' => ['required', 'string']]);

        $submitted  = trim($request->input('phone'));
        $digits     = preg_replace('/\D/', '', $submitted);
        $candidates = array_unique(array_filter([$submitted, $digits]));
        for ($strip = 1; $strip <= 3; $strip++) {
            if (\strlen($digits) > $strip + 7) {
                $local        = substr($digits, $strip);
                $candidates[] = $local;
                $candidates[] = '0' . $local;
            }
        }

        $user = User::whereIn('phone', $candidates)
            ->whereIn('role', ['admin', 'superadmin'])
            ->first();

        if (! $user || $user->status !== 'active') {
            return back()->withErrors(['phone' => 'No active admin account found with that phone number.']);
        }

        PasswordResetCode::where('user_id', $user->id)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->update(['expires_at' => now()]);

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        PasswordResetCode::create([
            'user_id'    => $user->id,
            'phone'      => $user->phone,
            'code_hash'  => Hash::make($code),
            'attempts'   => 0,
            'expires_at' => now()->addMinutes(5),
        ]);

        app(WhatsAppService::class)->sendTemplate('password_reset_otp', $user->phone ?? '', [
            'code' => $code,
        ]);

        $request->session()->put('admin_otp_user_id', $user->id);

        return redirect()->route('admin.forgot-password.verify-otp');
    }

    public function showVerifyOtp(Request $request): View|RedirectResponse
    {
        if ($this->isAuthenticatedAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if (! $request->session()->has('admin_otp_user_id')) {
            return redirect()->route('admin.forgot-password');
        }

        return view('admin.auth.verify-otp');
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string', 'size:6']]);

        $userId = $request->session()->get('admin_otp_user_id');

        if (! $userId) {
            return redirect()->route('admin.forgot-password')
                ->withErrors(['code' => 'Session expired. Please start again.']);
        }

        $resetCode = PasswordResetCode::where('user_id', $userId)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->orderByDesc('created_at')
            ->first();

        if (! $resetCode) {
            $request->session()->forget('admin_otp_user_id');
            return redirect()->route('admin.forgot-password')
                ->withErrors(['phone' => 'Code expired. Please request a new one.']);
        }

        if ($resetCode->attempts >= 5) {
            return back()->withErrors(['code' => 'Too many attempts. Please request a new code.']);
        }

        if (! Hash::check($request->input('code'), $resetCode->code_hash)) {
            $resetCode->increment('attempts');
            return back()->withErrors(['code' => 'Invalid code. Please try again.']);
        }

        $resetCode->update(['verified_at' => now(), 'used_at' => now()]);
        $request->session()->forget('admin_otp_user_id');

        $user  = User::find($userId);
        $token = Password::createToken($user);

        return redirect()->to(
            url('/set-password?token='.urlencode($token).'&email='.urlencode($user->email))
        );
    }

    private function isAuthenticatedAdmin(): bool
    {
        return Auth::check() && in_array(Auth::user()->role, ['admin', 'superadmin']);
    }
}
