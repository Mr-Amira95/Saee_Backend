<?php

namespace App\Http\Controllers\Client;

use App\Models\PasswordResetCode;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if ($this->isAuthenticatedClient()) {
            return redirect()->route('client.dashboard');
        }

        return view('client.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'phone'    => ['required'],
            'password' => ['required'],
        ]);

        $phone = preg_replace('/\s+/', '', $request->phone);

        $user = User::where('phone', $phone)
            ->orWhere('phone', ltrim($phone, '0'))
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return back()
                ->withErrors(['phone' => 'The phone number or password is incorrect.'])
                ->withInput($request->only('phone'));
        }

        if (! in_array($user->role, ['client_master', 'client_employee'])) {
            return back()
                ->withErrors(['phone' => 'You do not have client access.'])
                ->withInput($request->only('phone'));
        }

        if ($user->status !== 'active') {
            return back()
                ->withErrors(['phone' => 'Your account has been suspended. Please contact Saee support.'])
                ->withInput($request->only('phone'));
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->route('client.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('client.login');
    }

    public function showForgotPassword(): View|RedirectResponse
    {
        if ($this->isAuthenticatedClient()) {
            return redirect()->route('client.dashboard');
        }

        return view('client.auth.forgot-password');
    }

    public function requestCode(Request $request): RedirectResponse
    {
        $request->validate(['phone' => ['required']]);

        $phone = preg_replace('/\s+/', '', $request->phone);

        $user = User::where('phone', $phone)
            ->orWhere('phone', ltrim($phone, '0'))
            ->whereIn('role', ['client_master', 'client_employee'])
            ->first();

        if (! $user) {
            return back()
                ->withErrors(['phone' => 'No client account found with this phone number.'])
                ->withInput();
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        PasswordResetCode::updateOrCreate(
            ['user_id' => $user->id],
            [
                'phone'            => $user->phone,
                'code_hash'        => Hash::make($code),
                'reset_token_hash' => null,
                'attempts'         => 0,
                'verified_at'      => null,
                'used_at'          => null,
                'expires_at'       => now()->addMinutes(10),
                'reset_token_expires_at' => null,
            ]
        );

        // TODO: dispatch SMS job with $code to $user->phone
        // For now store in session for development
        session(['_fp_code_preview' => $code, '_fp_user_id' => $user->id, '_fp_phone' => $phone]);

        return back()->with('step', 'verify')->with('phone', $phone);
    }

    public function verifyCode(Request $request): RedirectResponse
    {
        $request->validate([
            'phone' => ['required'],
            'code'  => ['required', 'digits:6'],
        ]);

        $phone = preg_replace('/\s+/', '', $request->phone);

        $user = User::where('phone', $phone)
            ->orWhere('phone', ltrim($phone, '0'))
            ->whereIn('role', ['client_master', 'client_employee'])
            ->first();

        if (! $user) {
            return back()->withErrors(['code' => 'Invalid request.'])->withInput();
        }

        $resetCode = PasswordResetCode::where('user_id', $user->id)
            ->whereNull('verified_at')
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        if (! $resetCode || ! Hash::check($request->code, $resetCode->code_hash)) {
            return back()->withErrors(['code' => 'Invalid or expired code.'])->withInput();
        }

        $token = bin2hex(random_bytes(32));
        $resetCode->update([
            'verified_at'            => now(),
            'reset_token_hash'       => Hash::make($token),
            'reset_token_expires_at' => now()->addMinutes(15),
        ]);

        session(['_fp_reset_token' => $token, '_fp_user_id' => $user->id, '_fp_phone' => $phone]);

        return back()->with('step', 'reset')->with('phone', $phone);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'phone'                 => ['required'],
            'password'              => ['required', 'min:8', 'confirmed'],
            'password_confirmation' => ['required'],
        ]);

        $phone = preg_replace('/\s+/', '', $request->phone);
        $token = session('_fp_reset_token');
        $userId = session('_fp_user_id');

        $user = User::find($userId);

        if (! $user) {
            return back()->withErrors(['password' => 'Session expired. Please start over.']);
        }

        $resetCode = PasswordResetCode::where('user_id', $user->id)
            ->whereNotNull('verified_at')
            ->whereNull('used_at')
            ->where('reset_token_expires_at', '>', now())
            ->first();

        if (! $resetCode || ! Hash::check($token, $resetCode->reset_token_hash)) {
            return back()->withErrors(['password' => 'Session expired. Please start over.']);
        }

        $user->update(['password' => Hash::make($request->password)]);
        $resetCode->update(['used_at' => now()]);

        session()->forget(['_fp_reset_token', '_fp_user_id', '_fp_phone', '_fp_code_preview']);

        return redirect()->route('client.login')->with('status', 'Password reset successfully. Please sign in.');
    }

    private function isAuthenticatedClient(): bool
    {
        return Auth::check() && in_array(Auth::user()->role, ['client_master', 'client_employee']);
    }
}
