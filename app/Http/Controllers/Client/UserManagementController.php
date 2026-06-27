<?php

namespace App\Http\Controllers\Client;

use App\Mail\UserInvitationMail;
use App\Models\ClientEmployee;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (! Auth::user()->isClientMaster()) {
                abort(403);
            }

            return $next($request);
        });
    }

    public function index(): View
    {
        $profile = $this->getClientProfile();
        $employees = $profile->employees()->with('user')->latest()->paginate(20);

        return view('client.users.index', compact('profile', 'employees'));
    }

    public function create(): View
    {
        return view('client.users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'job_title' => ['nullable', 'string', 'max:100'],
        ]);

        $profile = $this->getClientProfile();

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => $request->password ?: Str::random(40),
            'role' => 'client_employee',
            'status' => 'active',
        ]);

        ClientEmployee::create([
            'user_id' => $user->id,
            'client_profile_id' => $profile->id,
            'job_title' => $request->job_title ?: null,
            'status' => 'active',
        ]);

        $token = Password::createToken($user);
        Mail::to($user->email)->send(new UserInvitationMail($user, $token));

        return redirect()->route('client.users.index')
            ->with('success', __('User created successfully.'));
    }

    public function edit(int $id): View
    {
        $profile = $this->getClientProfile();
        $employee = $profile->employees()->with('user')->findOrFail($id);

        return view('client.users.edit', compact('employee'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $profile = $this->getClientProfile();
        $employee = $profile->employees()->with('user')->findOrFail($id);
        $user = $employee->user;

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['required', 'string', 'max:20', Rule::unique('users', 'phone')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'job_title' => ['nullable', 'string', 'max:100'],
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        if ($request->filled('password')) {
            $user->password = $request->password;
        }
        $user->save();

        $employee->job_title = $request->job_title ?: null;
        $employee->save();

        return redirect()->route('client.users.index')
            ->with('success', __('User updated successfully.'));
    }

    public function destroy(int $id): RedirectResponse
    {
        $profile = $this->getClientProfile();
        $employee = $profile->employees()->with('user')->findOrFail($id);

        $employee->user->delete();
        $employee->delete();

        return redirect()->route('client.users.index')
            ->with('success', __('User removed successfully.'));
    }
}
