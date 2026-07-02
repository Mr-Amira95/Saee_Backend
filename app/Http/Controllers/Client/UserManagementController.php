<?php

namespace App\Http\Controllers\Client;

use App\Mail\UserInvitationMail;
use App\Models\ClientEmployee;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            $user = Auth::user();
            if (! $user->isClientMaster() && ! $user->hasClientPermission('team')) {
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
        $permissions = Permission::where('scope', 'client')->orderBy('display_name')->get();

        return view('client.users.create', compact('permissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9_.-]+$/', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'phone_country_code' => ['nullable', 'string', 'max:10'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'job_title' => ['nullable', 'string', 'max:100'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ], [
            'username.regex' => 'The username field must only contain letters, numbers, dashes, underscores, and dots.',
        ]);

        $profile = $this->getClientProfile();

        $user = User::create([
            'name' => $request->name,
            'username' => $data['username'],
            'email' => $request->email,
            'phone' => $request->phone,
            'phone_country_code' => $data['phone_country_code'] ?? '+962',
            'otp_channel' => Auth::user()->otp_channel ?? 'whatsapp',
            'password' => $data['password'] ?? Str::random(40),
            'role' => 'client_employee',
            'status' => 'active',
        ]);

        ClientEmployee::create([
            'user_id' => $user->id,
            'client_profile_id' => $profile->id,
            'job_title' => $request->job_title ?: null,
            'status' => 'active',
        ]);

        $this->syncPermissions($user->id, $profile->id, $data['permissions'] ?? []);

        $token = Password::createToken($user);
        Mail::to($user->email)->send(new UserInvitationMail($user, $token));

        return redirect()->route('client.users.index')
            ->with('success', __('User created successfully.'));
    }

    public function edit(int $id): View
    {
        $profile = $this->getClientProfile();
        $employee = $profile->employees()->with('user')->findOrFail($id);
        $permissions = Permission::where('scope', 'client')->orderBy('display_name')->get();
        $grantedPermissionIds = DB::table('client_employee_permission_user')
            ->where('employee_user_id', $employee->user_id)
            ->where('client_profile_id', $profile->id)
            ->pluck('permission_id');

        return view('client.users.edit', compact('employee', 'permissions', 'grantedPermissionIds'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $profile = $this->getClientProfile();
        $employee = $profile->employees()->with('user')->findOrFail($id);
        $user = $employee->user;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9_.-]+$/', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['required', 'string', 'max:20', Rule::unique('users', 'phone')->ignore($user->id)],
            'phone_country_code' => ['nullable', 'string', 'max:10'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'job_title' => ['nullable', 'string', 'max:100'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ], [
            'username.regex' => 'The username field must only contain letters, numbers, dashes, underscores, and dots.',
        ]);

        $user->name = $request->name;
        $user->username = $data['username'];
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->phone_country_code = $data['phone_country_code'] ?? '+962';
        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }
        $user->save();

        $employee->job_title = $request->job_title ?: null;
        $employee->save();

        $this->syncPermissions($user->id, $profile->id, $data['permissions'] ?? []);

        return redirect()->route('client.users.index')
            ->with('success', __('User updated successfully.'));
    }

    protected function syncPermissions(int $employeeUserId, int $clientProfileId, array $permissionIds): void
    {
        DB::transaction(function () use ($employeeUserId, $clientProfileId, $permissionIds) {
            DB::table('client_employee_permission_user')
                ->where('employee_user_id', $employeeUserId)
                ->where('client_profile_id', $clientProfileId)
                ->delete();

            $now = now();
            $rows = array_map(fn ($permId) => [
                'employee_user_id' => $employeeUserId,
                'permission_id' => (int) $permId,
                'client_profile_id' => $clientProfileId,
                'granted_by' => Auth::id(),
                'created_at' => $now,
                'updated_at' => $now,
            ], $permissionIds);

            if ($rows !== []) {
                DB::table('client_employee_permission_user')->insertOrIgnore($rows);
            }
        });
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
