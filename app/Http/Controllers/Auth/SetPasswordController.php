<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\SetPasswordTokenService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;

class SetPasswordController extends Controller
{
    public function __construct(private readonly SetPasswordTokenService $tokens) {}

    public function show(Request $request, string $token = null)
    {
        return view('auth.set-password', [
            'token' => $token ?? $request->query('token'),
            'email' => $request->query('email'),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'password' => [
                'required',
                'string',
                'confirmed',
                PasswordRule::min(8)->letters()->mixedCase()->symbols(),
            ],
        ]);

        $user = $this->tokens->resolve($request->string('token')->toString());

        if (! $user) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['token' => 'This invitation link is invalid or has expired. Please contact your administrator.']);
        }

        $user->forceFill([
            'password' => Hash::make($request->input('password')),
            'status'   => 'active',
        ])->save();

        $this->tokens->consume($request->string('token')->toString());

        event(new PasswordReset($user));

        return redirect()->route('set-password.success');
    }

    public function success()
    {
        return view('auth.set-password-success');
    }
}
