<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\UserInvitationMail;
use App\Models\ClientEmployee;
use App\Models\ClientProfile;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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
            'username'           => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9_.-]+$/', 'unique:users,username'],
            'email'              => ['required_if:otp_channel,email', 'nullable', 'email', 'unique:users,email'],
            'phone'              => ['required_if:otp_channel,whatsapp', 'nullable', 'string', 'max:20', 'unique:users,phone'],
            'phone_country_code' => 'nullable|string|max:10',
            'otp_channel'        => ['required', Rule::in(['whatsapp', 'email'])],
            'job_title'          => 'nullable|string|max:100',
            'permissions'        => 'nullable|array',
            'permissions.*'      => 'integer|exists:permissions,id',
        ], [
            'username.regex'  => 'The username field must only contain letters, numbers, dashes, underscores, and dots.',
            'email.required_if' => 'The email field is required when the notification channel is set to email.',
            'phone.required_if' => 'The phone field is required when the notification channel is set to WhatsApp.',
        ]);

        $user = null;
        DB::transaction(function () use ($data, $client, &$user) {
            $user = User::create([
                'name'               => $data['name'],
                'username'           => $data['username'],
                'email'              => $data['email'],
                'password'           => Hash::make(Str::random(40)),
                'phone'              => $data['phone'] ?? null,
                'phone_country_code' => $data['phone_country_code'] ?? '+962',
                'otp_channel'        => $data['otp_channel'],
                'role'               => 'client_employee',
                'status'             => 'active',
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

        $token          = Password::createToken($user);
        $setPasswordUrl = url('/set-password?token='.urlencode($token).'&email='.urlencode($user->email ?? ''));

        if ($data['otp_channel'] === 'email') {
            Mail::to($user->email)->send(new UserInvitationMail($user, $token));
        } else {
            app(WhatsAppService::class)->sendTemplate('user_invitation', $user->phone ?? '', [
                'name' => $user->name,
                'link' => $setPasswordUrl,
            ]);
        }

        $via = $data['otp_channel'] === 'email' ? 'email' : 'WhatsApp';
        return back()->with('success', "Employee account created. An invitation has been sent via {$via}.");
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
