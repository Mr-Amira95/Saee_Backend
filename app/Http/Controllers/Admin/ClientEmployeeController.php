<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\UserInvitationMail;
use App\Models\ClientEmployee;
use App\Models\ClientProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ClientEmployeeController extends Controller
{
    public function create(ClientProfile $client)
    {
        $permissions = \App\Models\Permission::where('scope', 'client')
            ->orderBy('group')->orderBy('display_name')->get()
            ->groupBy('group');
        return view('admin.users.clients.employees.create', compact('client', 'permissions'));
    }

    public function store(Request $request, ClientProfile $client)
    {
        $data = $request->validate([
            'name'               => 'required|string|max:255',
            'email'              => 'required|email|unique:users,email',
            'phone'              => 'nullable|string|max:20',
            'phone_country_code' => 'nullable|string|max:10',
            'job_title'          => 'nullable|string|max:100',
            'permissions'        => 'nullable|array',
            'permissions.*'      => 'integer|exists:permissions,id',
        ]);

        $user = null;
        DB::transaction(function () use ($data, $client, &$user) {
            $user = User::create([
                'name'               => $data['name'],
                'email'              => $data['email'],
                'password'           => Hash::make(Str::random(40)),
                'phone'              => $data['phone'] ?? null,
                'phone_country_code' => $data['phone_country_code'] ?? '+962',
                'role'               => 'client_employee',
                'status'             => 'pending',
            ]);

            ClientEmployee::create([
                'user_id'           => $user->id,
                'client_profile_id' => $client->id,
                'job_title'         => $data['job_title'] ?? null,
                'status'            => 'active',
            ]);

            $now = now();
            foreach ($data['permissions'] ?? [] as $permId) {
                DB::table('client_employee_permission_user')->insertOrIgnore([
                    'employee_user_id'  => $user->id,
                    'permission_id'     => (int) $permId,
                    'client_profile_id' => $client->id,
                    'granted_by'        => auth()->id(),
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ]);
            }
        });

        $token = Password::createToken($user);
        Mail::to($user->email)->send(new UserInvitationMail($user, $token));

        return back()->with('success', "Employee account created. An invitation email has been sent to {$user->email}.");
    }

    public function updateStatus(ClientProfile $client, ClientEmployee $employee)
    {
        $newStatus = $employee->status === 'active' ? 'suspended' : 'active';
        $employee->update(['status' => $newStatus]);

        $label = $newStatus === 'active' ? 'activated' : 'suspended';
        return back()->with('success', "Employee has been {$label}.");
    }

    public function destroy(ClientProfile $client, ClientEmployee $employee)
    {
        DB::transaction(function () use ($employee) {
            DB::table('client_employee_permission_user')
                ->where('employee_user_id', $employee->user_id)
                ->where('client_profile_id', $employee->client_profile_id)
                ->delete();

            $employee->user?->delete();
            $employee->delete();
        });

        return back()->with('success', 'Employee removed successfully.');
    }
}
