<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use App\Models\User;

class SetPasswordController extends Controller
{
    public function show(Request $request)
    {
        return view('auth.set-password', [
            'token' => $request->query('token'),
            'email' => $request->query('email'),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'status'   => 'active',
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('set-password.success');
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['token' => 'This invitation link is invalid or has expired. Please contact your administrator.']);
    }

    public function success()
    {
        return view('auth.set-password-success');
    }
}
