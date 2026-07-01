<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetOtpMail;
use App\Models\PasswordResetCode;
use App\Models\User;
use App\Services\WhatsAppService;
use App\Traits\NormalizesPhone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    use NormalizesPhone;

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
            'login'    => ['required', 'string'],
            'password' => ['required'],
        ]);

        $submitted = trim($request->input('login'));

        $user = User::where('username', $submitted)->first()
            ?? User::whereIn('phone', $this->phoneCandidates($submitted))->first();

        if (! $user || ! Hash::check($request->input('password'), $user->password)) {
            return back()
                ->withErrors(['login' => 'The credentials you entered are incorrect.'])
                ->withInput($request->only('login'));
        }

        if (! \in_array($user->role, ['admin', 'superadmin', 'client_master', 'client_employee'])) {
            return back()
                ->withErrors(['login' => 'This portal is for admin and client users only. Please use the mobile app.'])
                ->withInput($request->only('login'));
        }

        if ($user->status !== 'active') {
            return back()
                ->withErrors(['login' => 'Your account has been suspended. Please contact support.'])
                ->withInput($request->only('login'));
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
        $request->validate(['login' => ['required', 'string']]);

        $submitted = trim($request->input('login'));

        $user = User::where('username', $submitted)->first()
            ?? User::whereIn('phone', $this->phoneCandidates($submitted))->first();

        if (! $user || ! in_array($user->role, ['admin', 'superadmin', 'client_master', 'client_employee'])) {
            return back()->withErrors(['login' => 'No account was found with that username or phone number.']);
        }

        if ($user->status !== 'active') {
            return back()->withErrors(['login' => 'This account is not active. Please contact support.']);
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

        // Admins always get WhatsApp; clients follow their configured otp_channel.
        $channel = \in_array($user->role, ['admin', 'superadmin']) ? 'whatsapp' : ($user->otp_channel ?? 'whatsapp');

        if ($channel === 'email') {
            Mail::to($user->email)->send(new PasswordResetOtpMail($user, $code));
        } else {
            app(WhatsAppService::class)->sendTemplate('password_reset_otp', $user->phone ?? '', [
                'code' => $code,
            ]);
        }

        $request->session()->put('otp_user_id', $user->id);
        $request->session()->put('otp_channel', $channel);

        return redirect()->route('portal.forgot-password.verify-otp');
    }

    public function showVerifyOtp(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }

        if (! $request->session()->has('otp_user_id')) {
            return redirect()->route('portal.forgot-password');
        }

        return view('portal.auth.verify-otp', [
            'channel' => $request->session()->get('otp_channel', 'whatsapp'),
        ]);
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string', 'size:6']]);

        $userId = $request->session()->get('otp_user_id');

        if (! $userId) {
            return redirect()->route('portal.forgot-password')
                ->withErrors(['code' => 'Session expired. Please start again.']);
        }

        $resetCode = PasswordResetCode::where('user_id', $userId)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->orderByDesc('created_at')
            ->first();

        if (! $resetCode) {
            $request->session()->forget(['otp_user_id', 'otp_channel']);
            return redirect()->route('portal.forgot-password')
                ->withErrors(['login' => 'Code expired. Please request a new one.']);
        }

        if ($resetCode->attempts >= 5) {
            return back()->withErrors(['code' => 'Too many attempts. Please request a new code.']);
        }

        if (! Hash::check($request->input('code'), $resetCode->code_hash)) {
            $resetCode->increment('attempts');
            return back()->withErrors(['code' => 'Invalid code. Please try again.']);
        }

        $resetCode->update(['verified_at' => now(), 'used_at' => now()]);
        $request->session()->forget(['otp_user_id', 'otp_channel']);

        $user  = User::find($userId);
        $token = Password::createToken($user);

        return redirect()->to(
            url('/set-password?token='.urlencode($token).'&email='.urlencode($user->email))
        );
    }

    private function redirectByRole(User $user): RedirectResponse
    {
        if (\in_array($user->role, ['admin', 'superadmin'])) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('client.dashboard');
    }
}
