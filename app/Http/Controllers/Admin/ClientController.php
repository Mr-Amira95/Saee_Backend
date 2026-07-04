<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\UserInvitationMail;
use App\Models\City;
use App\Models\ClientAttachment;
use App\Models\ClientProfile;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $q = ClientProfile::with('masterUser', 'city', 'area')
            ->when($request->search, function($query, $s) {
                $query->where(function($sub) use ($s) {
                    $sub->where('company_name', 'like', "%$s%")
                        ->orWhere('company_name_ar', 'like', "%$s%")
                        ->orWhere('email', 'like', "%$s%")
                        ->orWhere('company_phone', 'like', "%$s%")
                        ->orWhere('commercial_register_number', 'like', "%$s%")
                        ->orWhere('vat_number', 'like', "%$s%")
                        ->orWhereHas('masterUser', function($u) use ($s) {
                            $u->where('name', 'like', "%$s%")
                              ->orWhere('email', 'like', "%$s%")
                              ->orWhere('phone', 'like', "%$s%");
                        })
                        ->orWhereHas('city', function($c) use ($s) {
                            $c->where('name', 'like', "%$s%")
                              ->orWhere('name_ar', 'like', "%$s%");
                        })
                        ->orWhereHas('area', function($a) use ($s) {
                            $a->where('name', 'like', "%$s%")
                              ->orWhere('name_ar', 'like', "%$s%");
                        });
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.clients.index', compact('q'));
    }

    public function create()
    {
        $cities = City::orderBy('name')->get();
        return view('admin.users.clients.create', compact('cities'));
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->hasAdminAction('clients.add'), 403);

        $data = $request->validate([
            'name'                        => 'required|string|max:255',
            'username'                    => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9_.-]+$/', 'unique:users,username'],
            'email'                       => 'nullable|email|unique:users,email',
            'phone'                       => 'nullable|string|max:20|unique:users,phone',
            'phone_country_code'          => 'nullable|string|max:10',
            'otp_channel'                 => ['nullable', Rule::in(['whatsapp', 'email'])],
            'password'                    => 'nullable|string|min:8|confirmed',
            'company_name'                => 'required|string|max:255',
            'company_name_ar'             => 'nullable|string|max:255',
            'commercial_register_number'  => 'nullable|string|max:100',
            'vat_number'                  => 'nullable|string|max:50',
            'company_email'               => 'nullable|email|max:255',
            'company_phone'               => 'nullable|string|max:20',
            'company_phone_country_code'  => 'nullable|string|max:10',
            'address_line1'               => 'nullable|string|max:255',
            'city_id'                     => 'nullable|exists:cities,id',
            'area_id'                     => 'nullable|exists:areas,id',
            'credit_limit'                => 'nullable|numeric|min:0',
            'expiry_date'                 => 'nullable|date|after:today',
            'status'                      => ['nullable', Rule::in(['active','suspended','pending_verification'])],
            'require_national_id'         => 'nullable|boolean',
            'logo'                        => 'nullable|image|max:2048',
            'attachment_labels'           => 'nullable|array',
            'attachment_labels.*'         => 'nullable|string|max:255',
            'attachment_files'            => 'nullable|array',
            'attachment_files.*'          => 'nullable|file|max:10240',
            'delivery_prices'             => 'nullable|array',
            'delivery_prices.*'           => 'nullable|numeric|min:0',
            'bank_name'                   => 'nullable|string|max:255',
            'account_name'                => 'nullable|string|max:255',
            'account_number'              => 'nullable|string|max:30',
            'iban'                        => 'nullable|string|max:34',
            'swift_code'                  => 'nullable|string|max:11',
            'cliq_id'                     => 'nullable|string|max:50',
            'cliq_alias_type'             => ['nullable', Rule::in(['alias', 'phone'])],
            'bank_notes'                  => 'nullable|string|max:500',
        ], [
            'username.regex' => 'The username field must only contain letters, numbers, dashes, underscores, and dots.',
        ]);

        $user = DB::transaction(function () use ($data, $request) {
            $user = User::create([
                'name'               => $data['name'],
                'username'           => $data['username'],
                'email'              => $data['email'],
                'password'           => Hash::make($data['password'] ?? Str::random(40)),
                'phone'              => $data['phone'] ?? null,
                'phone_country_code' => $data['phone_country_code'] ?? '+962',
                'otp_channel'        => $data['otp_channel'] ?? 'whatsapp',
                'role'               => 'client_master',
                'status'             => 'active',
            ]);

            $logoPath = null;
            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('client-logos', 'public');
            }

            $client = ClientProfile::create([
                'master_user_id'               => $user->id,
                'company_name'                 => $data['company_name'],
                'company_name_ar'              => $data['company_name_ar'] ?? null,
                'commercial_register_number'   => $data['commercial_register_number'] ?? null,
                'vat_number'                   => $data['vat_number'] ?? null,
                'email'                        => $data['company_email'] ?? null,
                'company_phone'                => $data['company_phone'] ?? null,
                'company_phone_country_code'   => $data['company_phone_country_code'] ?? null,
                'logo_path'                    => $logoPath,
                'address_line1'                => $data['address_line1'] ?? null,
                'city_id'                      => $data['city_id'] ?? null,
                'area_id'                      => $data['area_id'] ?? null,
                'credit_limit'                 => $data['credit_limit'] ?? 0,
                'expiry_date'                  => $data['expiry_date'] ?? null,
                'status'                       => $data['status'] ?? 'active',
                'require_national_id'          => $request->boolean('require_national_id'),
            ]);

            $this->saveAttachments($request, $client, $user->id);

            $bankFields = array_filter([
                'bank_name'       => $data['bank_name'] ?? null,
                'account_name'    => $data['account_name'] ?? null,
                'account_number'  => $data['account_number'] ?? null,
                'iban'            => $data['iban'] ?? null,
                'swift_code'      => $data['swift_code'] ?? null,
                'cliq_id'         => $data['cliq_id'] ?? null,
                'cliq_alias_type' => $data['cliq_alias_type'] ?? null,
                'notes'           => $data['bank_notes'] ?? null,
            ]);
            if (!empty($bankFields)) {
                $client->bankDetail()->create($bankFields);
            }

            foreach ($data['delivery_prices'] ?? [] as $cityId => $price) {
                if ($price !== null && $price !== '') {
                    $client->deliveryPrices()->create([
                        'city_id'        => $cityId,
                        'delivery_price' => $price,
                    ]);
                }
            }

            return $user;
        });

        if (empty($data['password'])) {
            $channel = $data['otp_channel'] ?? 'whatsapp';
            $this->sendInvitation($user, $channel);

            $via = $channel === 'email' ? 'email' : 'WhatsApp';
            return redirect()->route('admin.clients.index')
                ->with('success', "Client account created. An invitation has been sent via {$via}.");
        }

        return redirect()->route('admin.clients.index')
            ->with('success', 'Client account created successfully.');
    }

    public function show(ClientProfile $client)
    {
        $client->load('masterUser', 'city', 'area', 'employees.user', 'attachments', 'bankDetail', 'deliveryPrices');
        return view('admin.users.clients.show', compact('client'));
    }

    public function edit(ClientProfile $client)
    {
        $client->load('masterUser', 'city', 'area', 'attachments', 'bankDetail');
        $cities = City::orderBy('name')->get();
        $existingPrices = $client->deliveryPrices()->pluck('delivery_price', 'city_id');
        return view('admin.users.clients.edit', compact('client', 'cities', 'existingPrices'));
    }

    public function update(Request $request, ClientProfile $client)
    {
        abort_unless($request->user()->hasAdminAction('clients.edit'), 403);

        $data = $request->validate([
            'name'                        => 'required|string|max:255',
            'username'                    => ['required','string','max:50','regex:/^[a-zA-Z0-9_.-]+$/', Rule::unique('users','username')->ignore($client->master_user_id)],
            'email'                       => ['nullable','email', Rule::unique('users','email')->ignore($client->master_user_id)],
            'phone'                       => ['nullable','string','max:20', Rule::unique('users','phone')->ignore($client->master_user_id)],
            'phone_country_code'          => 'nullable|string|max:10',
            'otp_channel'                 => ['nullable', Rule::in(['whatsapp', 'email'])],
            'company_name'                => 'required|string|max:255',
            'company_name_ar'             => 'nullable|string|max:255',
            'commercial_register_number'  => 'nullable|string|max:100',
            'vat_number'                  => 'nullable|string|max:50',
            'company_email'               => 'nullable|email|max:255',
            'company_phone'               => 'nullable|string|max:20',
            'company_phone_country_code'  => 'nullable|string|max:10',
            'address_line1'               => 'nullable|string|max:255',
            'city_id'                     => 'nullable|exists:cities,id',
            'area_id'                     => 'nullable|exists:areas,id',
            'credit_limit'                => 'nullable|numeric|min:0',
            'expiry_date'                 => 'nullable|date',
            'status'                      => ['nullable', Rule::in(['active','suspended','pending_verification'])],
            'require_national_id'         => 'nullable|boolean',
            'logo'                        => 'nullable|image|max:2048',
            'attachment_labels'           => 'nullable|array',
            'attachment_labels.*'         => 'nullable|string|max:255',
            'attachment_files'            => 'nullable|array',
            'attachment_files.*'          => 'nullable|file|max:10240',
            'delete_attachment_ids'       => 'nullable|array',
            'delete_attachment_ids.*'     => 'nullable|integer',
            'bank_name'                   => 'nullable|string|max:255',
            'account_name'                => 'nullable|string|max:255',
            'account_number'              => 'nullable|string|max:30',
            'iban'                        => 'nullable|string|max:34',
            'swift_code'                  => 'nullable|string|max:11',
            'cliq_id'                     => 'nullable|string|max:50',
            'cliq_alias_type'             => ['nullable', Rule::in(['alias', 'phone'])],
            'bank_notes'                  => 'nullable|string|max:500',
            'delivery_prices'             => 'nullable|array',
            'delivery_prices.*'           => 'nullable|numeric|min:0',
        ], [
            'username.regex' => 'The username field must only contain letters, numbers, dashes, underscores, and dots.',
        ]);

        DB::transaction(function () use ($data, $request, $client) {
            $client->masterUser->update([
                'name'               => $data['name'],
                'username'           => $data['username'],
                'email'              => $data['email'],
                'phone'              => $data['phone'] ?? null,
                'phone_country_code' => $data['phone_country_code'] ?? $client->masterUser->phone_country_code,
                'otp_channel'        => $data['otp_channel'] ?? $client->masterUser->otp_channel,
                'status'             => $data['user_status'] ?? $client->masterUser->status,
            ]);

            $logoPath = $client->logo_path;
            if ($request->hasFile('logo')) {
                if ($logoPath) Storage::disk('public')->delete($logoPath);
                $logoPath = $request->file('logo')->store('client-logos', 'public');
            }

            $client->update([
                'company_name'                => $data['company_name'],
                'company_name_ar'             => $data['company_name_ar'] ?? null,
                'commercial_register_number'  => $data['commercial_register_number'] ?? null,
                'vat_number'                  => $data['vat_number'] ?? null,
                'email'                       => $data['company_email'] ?? null,
                'company_phone'               => $data['company_phone'] ?? null,
                'company_phone_country_code'  => $data['company_phone_country_code'] ?? null,
                'logo_path'                   => $logoPath,
                'address_line1'               => $data['address_line1'] ?? null,
                'city_id'                     => $data['city_id'] ?? null,
                'area_id'                     => $data['area_id'] ?? null,
                'credit_limit'                => $data['credit_limit'] ?? $client->credit_limit,
                'expiry_date'                 => $data['expiry_date'] ?? null,
                'status'                      => $data['status'] ?? $client->status,
                'require_national_id'         => $request->boolean('require_national_id'),
            ]);

            // Sync custom delivery prices
            $client->deliveryPrices()->delete();
            foreach ($data['delivery_prices'] ?? [] as $cityId => $price) {
                if ($price !== null && $price !== '') {
                    $client->deliveryPrices()->create([
                        'city_id'        => $cityId,
                        'delivery_price' => $price,
                    ]);
                }
            }

            // Delete marked attachments
            if (!empty($data['delete_attachment_ids'])) {
                $toDelete = ClientAttachment::whereIn('id', $data['delete_attachment_ids'])
                    ->where('client_profile_id', $client->id)->get();
                foreach ($toDelete as $att) {
                    Storage::disk('public')->delete($att->file_path);
                    $att->delete();
                }
            }

            $client->bankDetail()->updateOrCreate([], [
                'bank_name'       => $data['bank_name'] ?? null,
                'account_name'    => $data['account_name'] ?? null,
                'account_number'  => $data['account_number'] ?? null,
                'iban'            => $data['iban'] ?? null,
                'swift_code'      => $data['swift_code'] ?? null,
                'cliq_id'         => $data['cliq_id'] ?? null,
                'cliq_alias_type' => $data['cliq_alias_type'] ?? null,
                'notes'           => $data['bank_notes'] ?? null,
            ]);

            $this->saveAttachments($request, $client, auth()->id());
        });

        return redirect()->route('admin.clients.show', $client)
            ->with('success', 'Client updated successfully.');
    }

    public function destroy(ClientProfile $client)
    {
        abort_unless(auth()->user()->hasAdminAction('clients.delete'), 403);

        DB::transaction(function () use ($client) {
            foreach ($client->attachments as $att) {
                Storage::disk('public')->delete($att->file_path);
            }
            if ($client->logo_path) Storage::disk('public')->delete($client->logo_path);
            $client->masterUser->delete();
            $client->delete();
        });

        return redirect()->route('admin.clients.index')
            ->with('success', 'Client deleted successfully.');
    }

    public function resendInvitation(ClientProfile $client)
    {
        $this->sendInvitation($client->masterUser, $client->masterUser->otp_channel ?? 'whatsapp');
        return back()->with('success', "Invitation resent to {$client->masterUser->name}.");
    }

    public function resetPassword(Request $request, ClientProfile $client)
    {
        abort_unless($request->user()->hasAdminAction('clients.reset_password'), 403);

        $data = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $client->masterUser->update(['password' => Hash::make($data['password'])]);

        return back()->with('success', "Password reset for {$client->masterUser->name}.");
    }

    public function toggleNotifications(ClientProfile $client)
    {
        $user = $client->masterUser;
        $user->notifications_enabled = ! $user->notifications_enabled;
        $user->save();

        return response()->json(['notifications_enabled' => $user->notifications_enabled]);
    }

    public function toggleStatus(ClientProfile $client)
    {
        $newStatus = $client->status === 'active' ? 'suspended' : 'active';
        $client->update(['status' => $newStatus]);

        $user = $client->masterUser;
        if ($user) {
            $user->update(['status' => $newStatus]);
        }
        return back()->with('success', 'Account status updated successfully.');
    }

    public function areas(Request $request)
    {
        $areas = \App\Models\Area::where('city_id', $request->city_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'name_ar']);

        return response()->json($areas);
    }

    private function saveAttachments(Request $request, ClientProfile $client, int $uploadedBy): void
    {
        $files  = $request->file('attachment_files', []);
        $labels = $request->input('attachment_labels', []);

        foreach ($files as $i => $file) {
            if (!$file || !$file->isValid()) continue;

            $path = $file->store("client-attachments/{$client->id}", 'public');

            ClientAttachment::create([
                'client_profile_id' => $client->id,
                'label'             => $labels[$i] ?? $file->getClientOriginalName(),
                'file_path'         => $path,
                'original_filename' => $file->getClientOriginalName(),
                'mime_type'         => $file->getMimeType(),
                'file_size'         => $file->getSize(),
                'uploaded_by'       => $uploadedBy,
            ]);
        }
    }

    private function sendInvitation(User $user, string $channel = 'whatsapp'): void
    {
        $token          = Password::createToken($user);
        $setPasswordUrl = url('/set-password?token='.urlencode($token).'&email='.urlencode($user->email ?? ''));

        if ($channel === 'email' && $user->email) {
            Mail::to($user->email)->send(new UserInvitationMail($user, $token));
        } else {
            app(WhatsAppService::class)->sendTemplate('user_invitation', $user->phone ?? '', [
                'name' => $user->name,
                'link' => $setPasswordUrl,
            ]);
        }
    }
}
