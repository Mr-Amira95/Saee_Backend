<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $q = User::whereIn('role', ['admin','superadmin'])
            ->when($request->search, fn($query, $s) =>
                $query->where(function ($sub) use ($s) {
                    $sub->where('name', 'like', "%$s%")
                        ->orWhere('email', 'like', "%$s%")
                        ->orWhere('phone', 'like', "%$s%");
                })
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.admins.index', compact('q'));
    }

    public function create()
    {
        $permissions = Permission::where('scope', 'admin')->orderBy('group')->orderBy('name')->get()->groupBy('group');
        return view('admin.users.admins.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                => 'required|string|max:255',
            'email'               => 'required|email|unique:users,email',
            'phone'               => 'nullable|string|max:20|unique:users,phone',
            'phone_country_code'  => 'nullable|string|max:10',
            'permissions'         => 'nullable|array',
            'permissions.*'       => 'integer|exists:permissions,id',
        ]);

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name'               => $data['name'],
                'email'              => $data['email'],
                'password'           => Hash::make(Str::random(40)),
                'phone'              => $data['phone'] ?? null,
                'phone_country_code' => $data['phone_country_code'] ?? '+962',
                'role'               => 'admin',
                'status'             => 'active',
            ]);

            if (!empty($data['permissions'])) {
                $pivotRows = array_map(fn($permId) => [
                    'admin_user_id' => $user->id,
                    'permission_id' => $permId,
                    'granted_by'    => auth()->id(),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ], $data['permissions']);

                DB::table('admin_permission_user')->insert($pivotRows);
            }

            return $user;
        });

        $this->sendInvitation($user);

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin account created successfully.');
    }


    public function edit(User $admin)
    {
        $allPermissions = Permission::where('scope', 'admin')->orderBy('group')->orderBy('name')->get()->groupBy('group');
        $grantedIds = DB::table('admin_permission_user')
            ->where('admin_user_id', $admin->id)
            ->pluck('permission_id')
            ->toArray();

        return view('admin.users.admins.edit', compact('admin', 'allPermissions', 'grantedIds'));
    }

    public function update(Request $request, User $admin)
    {
        $data = $request->validate([
            'name'                => 'required|string|max:255',
            'email'               => ['required','email', Rule::unique('users','email')->ignore($admin->id)],
            'phone'               => ['nullable','string','max:20', Rule::unique('users','phone')->ignore($admin->id)],
            'phone_country_code'  => 'nullable|string|max:10',
            'status'              => ['nullable', Rule::in(['active','suspended','pending'])],
            'permissions'         => 'nullable|array',
            'permissions.*'       => 'integer|exists:permissions,id',
        ]);

        DB::transaction(function () use ($data, $admin) {
            $userUpdate = [
                'name'               => $data['name'],
                'email'              => $data['email'],
                'phone'              => $data['phone'] ?? null,
                'phone_country_code' => $data['phone_country_code'] ?? $admin->phone_country_code,
                'status'             => $data['status'] ?? $admin->status,
            ];
            $admin->update($userUpdate);

            DB::table('admin_permission_user')->where('admin_user_id', $admin->id)->delete();

            if (!empty($data['permissions'])) {
                $pivotRows = array_map(fn($permId) => [
                    'admin_user_id' => $admin->id,
                    'permission_id' => $permId,
                    'granted_by'    => auth()->id(),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ], $data['permissions']);

                DB::table('admin_permission_user')->insert($pivotRows);
            }
        });

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin updated successfully.');
    }

    public function destroy(User $admin)
    {
        DB::transaction(function () use ($admin) {
            DB::table('admin_permission_user')->where('admin_user_id', $admin->id)->delete();
            $admin->delete();
        });

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin deleted successfully.');
    }

    public function resendInvitation(User $admin)
    {
        $this->sendInvitation($admin);
        return back()->with('success', "Invitation email resent to {$admin->email}.");
    }

    private function sendInvitation(User $user): void
    {
        $token = Password::createToken($user);
        Mail::to($user->email)->send(new \App\Mail\UserInvitationMail($user, $token));
    }
}
