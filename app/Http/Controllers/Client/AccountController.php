<?php

namespace App\Http\Controllers\Client;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(): View
    {
        $profile = $this->getClientProfile();
        $profile->load(['city', 'area', 'bankDetail', 'masterUser']);

        $user       = Auth::user();
        $bankDetail = $profile->bankDetail;
        $masterUser = $profile->masterUser;

        return view('client.account.index', compact('profile', 'user', 'bankDetail', 'masterUser'));
    }

    public function editProfile(): View
    {
        return view('client.account.profile', ['user' => Auth::user()]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $request->validate([
            'name'  => ['required', 'string', 'max:255', 'regex:/^[\p{L}\s]+$/u'],
            'email' => ['nullable', 'email', 'max:255', 'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9]{6,15}$/', Rule::unique('users', 'phone')->ignore($user->id)],
        ], [
            'name.regex' => __('The full name field must only contain letters and spaces.'),
            'email.regex' => __('The email must be a valid address in the format name@domain.com.'),
            'phone.regex' => __('The phone field must contain 6 to 15 digits only.'),
        ]);

        $user->name  = $request->name;
        $user->email = $request->email ?: null;
        $user->phone = $request->phone ?: null;
        $user->save();

        return redirect()->route('client.account.profile.edit')
            ->with('success', __('Profile updated successfully.'));
    }

    public function editPassword(): View
    {
        return view('client.account.change-password');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'string', 'confirmed', PasswordRule::min(8)->mixedCase()->symbols()],
        ]);

        $user = Auth::user();

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => __('Current password is incorrect.')]);
        }

        $user->password = $request->password;
        $user->save();

        return redirect()->route('client.account.password.edit')
            ->with('success', __('Password updated successfully.'));
    }

    public function toggleNotifications(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        $user->notifications_enabled = ! $user->notifications_enabled;
        $user->save();

        return response()->json(['notifications_enabled' => $user->notifications_enabled]);
    }
}
