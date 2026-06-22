<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminProfile;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $q = User::whereIn('role', ['admin','superadmin'])
            ->with('adminProfile')
            ->when($request->search, fn($query, $s) =>
                $query->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%")
            )
            ->when($request->status, fn($query, $s) => $query->where('status', $s))
            ->when($request->role, fn($query, $r) => $query->where('role', $r))
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
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email',
            'password'       => 'required|string|min:8|confirmed',
            'phone'          => 'nullable|string|max:20|unique:users,phone',
            'department'     => 'nullable|string|max:100',
            'notes'          => 'nullable|string',
            'permissions'    => 'nullable|array',
            'permissions.*'  => 'integer|exists:permissions,id',
        ]);

        DB::transaction(function () use ($data, $request) {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
                'phone'    => $data['phone'] ?? null,
                'role'     => 'admin',
                'status'   => 'active',
            ]);

            AdminProfile::create([
                'user_id'    => $user->id,
                'department' => $data['department'] ?? null,
                'notes'      => $data['notes'] ?? null,
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
        });

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin account created successfully.');
    }

    public function show(User $admin)
    {
        $admin->load('adminProfile');
        $permissions = DB::table('admin_permission_user')
            ->join('permissions', 'permissions.id', '=', 'admin_permission_user.permission_id')
            ->where('admin_permission_user.admin_user_id', $admin->id)
            ->select('permissions.*', 'admin_permission_user.expires_at', 'admin_permission_user.granted_by')
            ->get()
            ->groupBy('group');

        return view('admin.users.admins.show', compact('admin', 'permissions'));
    }

    public function edit(User $admin)
    {
        $admin->load('adminProfile');
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
            'name'           => 'required|string|max:255',
            'email'          => ['required','email', Rule::unique('users','email')->ignore($admin->id)],
            'phone'          => ['nullable','string','max:20', Rule::unique('users','phone')->ignore($admin->id)],
            'password'       => 'nullable|string|min:8|confirmed',
            'department'     => 'nullable|string|max:100',
            'notes'          => 'nullable|string',
            'status'         => ['nullable', Rule::in(['active','suspended','pending'])],
            'permissions'    => 'nullable|array',
            'permissions.*'  => 'integer|exists:permissions,id',
        ]);

        DB::transaction(function () use ($data, $admin) {
            $userUpdate = [
                'name'   => $data['name'],
                'email'  => $data['email'],
                'phone'  => $data['phone'] ?? null,
                'status' => $data['status'] ?? $admin->status,
            ];
            if (!empty($data['password'])) {
                $userUpdate['password'] = Hash::make($data['password']);
            }
            $admin->update($userUpdate);

            $admin->adminProfile?->update([
                'department' => $data['department'] ?? null,
                'notes'      => $data['notes'] ?? null,
            ]);

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

        return redirect()->route('admin.admins.show', $admin)
            ->with('success', 'Admin updated successfully.');
    }

    public function destroy(User $admin)
    {
        DB::transaction(function () use ($admin) {
            DB::table('admin_permission_user')->where('admin_user_id', $admin->id)->delete();
            $admin->adminProfile?->delete();
            $admin->delete();
        });

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin deleted successfully.');
    }
}
