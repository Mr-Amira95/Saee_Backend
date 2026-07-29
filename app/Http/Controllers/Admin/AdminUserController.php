<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\UserInvitationMail;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;

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
        abort_unless($request->user()->hasAdminAction('admins.add'), 403);

        $data = $request->validate([
            'name'                => ['required', 'string', 'max:255', 'regex:/^[\p{L}\s]+$/u'],
            'username'            => ['required', 'string', 'max:50', 'regex:/^(?=.*[a-zA-Z])[a-zA-Z0-9]([a-zA-Z0-9_.-]*[a-zA-Z0-9])?$/', 'unique:users,username'],
            'email'               => ['required_if:otp_channel,email', 'nullable', 'email', 'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', 'unique:users,email'],
            'phone'               => ['required_if:otp_channel,whatsapp', 'nullable', 'string', 'max:20', 'regex:/^[0-9]{6,15}$/', 'unique:users,phone'],
            'phone_country_code'  => 'nullable|string|max:10',
            'otp_channel'         => ['required', Rule::in(['whatsapp', 'email'])],
            'password'            => ['nullable', 'string', 'confirmed', PasswordRule::min(8)->mixedCase()->symbols()],
            'permissions'         => 'nullable|array',
            'permissions.*'       => 'integer|exists:permissions,id',
        ], [
            'name.regex'      => __('The full name field must only contain letters and spaces.'),
            'username.regex'  => __('The username must start with a letter or number, contain at least one letter, and cannot end with a special character.'),
            'email.regex'     => __('The email must be a valid address in the format name@domain.com.'),
            'email.required_if' => __('The email field is required when the notification channel is set to email.'),
            'phone.regex'     => __('The phone field must contain 6 to 15 digits only.'),
            'phone.required_if' => __('The phone field is required when the notification channel is set to WhatsApp.'),
        ]);

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name'               => $data['name'],
                'username'           => $data['username'],
                'email'              => $data['email'],
                'password'           => Hash::make($data['password'] ?? Str::random(40)),
                'phone'              => $data['phone'] ?? null,
                'phone_country_code' => $data['phone_country_code'] ?? '+962',
                'otp_channel'        => $data['otp_channel'] ?? 'whatsapp',
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

        if (empty($data['password'])) {
            $channel = $data['otp_channel'] ?? 'whatsapp';
            $this->sendInvitation($user, $channel);

            $via = $channel === 'email' ? 'email' : 'WhatsApp';
            return redirect()->route('admin.admins.index')
                ->with('success', __('Admin account created. An invitation has been sent via :via.', ['via' => $via]));
        }

        return redirect()->route('admin.admins.index')
            ->with('success', __('Admin account created successfully.'));
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
        abort_unless($request->user()->hasAdminAction('admins.edit'), 403);

        $data = $request->validate([
            'name'                => ['required', 'string', 'max:255', 'regex:/^[\p{L}\s]+$/u'],
            'username'            => ['required','string','max:50','regex:/^(?=.*[a-zA-Z])[a-zA-Z0-9]([a-zA-Z0-9_.-]*[a-zA-Z0-9])?$/', Rule::unique('users','username')->ignore($admin->id)],
            'email'               => ['required_if:otp_channel,email', 'nullable', 'email', 'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', Rule::unique('users','email')->ignore($admin->id)],
            'phone'               => ['required_if:otp_channel,whatsapp', 'nullable', 'string', 'max:20', 'regex:/^[0-9]{6,15}$/', Rule::unique('users','phone')->ignore($admin->id)],
            'phone_country_code'  => 'nullable|string|max:10',
            'otp_channel'         => ['required', Rule::in(['whatsapp', 'email'])],
            'status'              => ['nullable', Rule::in(['active','suspended','pending'])],
            'permissions'         => 'nullable|array',
            'permissions.*'       => 'integer|exists:permissions,id',
        ], [
            'name.regex'      => __('The full name field must only contain letters and spaces.'),
            'username.regex'  => __('The username must start with a letter or number, contain at least one letter, and cannot end with a special character.'),
            'email.regex'     => __('The email must be a valid address in the format name@domain.com.'),
            'email.required_if' => __('The email field is required when the notification channel is set to email.'),
            'phone.regex'     => __('The phone field must contain 6 to 15 digits only.'),
            'phone.required_if' => __('The phone field is required when the notification channel is set to WhatsApp.'),
        ]);

        DB::transaction(function () use ($data, $admin) {
            $userUpdate = [
                'name'               => $data['name'],
                'username'           => $data['username'],
                'email'              => $data['email'],
                'phone'              => $data['phone'] ?? null,
                'phone_country_code' => $data['phone_country_code'] ?? $admin->phone_country_code,
                'otp_channel'        => $data['otp_channel'] ?? $admin->otp_channel,
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
            ->with('success', __('Admin updated successfully.'));
    }

    public function destroy(User $admin)
    {
        abort_unless(auth()->user()->hasAdminAction('admins.delete'), 403);

        DB::transaction(function () use ($admin) {
            DB::table('admin_permission_user')->where('admin_user_id', $admin->id)->delete();
            $admin->delete();
        });

        return redirect()->route('admin.admins.index')
            ->with('success', __('Admin deleted successfully.'));
    }

    public function resendInvitation(User $admin)
    {
        $this->sendInvitation($admin, $admin->otp_channel ?? 'whatsapp');
        return back()->with('success', __('Invitation sent to :name.', ['name' => $admin->name]));
    }

    public function resetPassword(Request $request, User $admin)
    {
        abort_unless($request->user()->hasAdminAction('admins.reset_password'), 403);

        $data = $request->validate([
            'password' => ['required', 'string', 'confirmed', PasswordRule::min(8)->mixedCase()->symbols()],
        ]);

        $admin->update(['password' => Hash::make($data['password'])]);

        return back()->with('success', __('Password reset for :name.', ['name' => $admin->name]));
    }

    private function sendInvitation(User $user, string $channel = 'whatsapp'): void
    {
        $token = Password::createToken($user);

        if ($channel === 'email' && $user->email) {
            Mail::to($user->email)->send(new UserInvitationMail($user, $token));
        } else {
            $queryTail = '?token='.urlencode($token).'&email='.urlencode($user->email ?? '');
            app(WhatsAppService::class)->sendTemplate('user_invitation', $user->phone ?? '', [
                'user_name' => $user->name,
            ], null, 'ar', [$queryTail]);
        }
    }
}
