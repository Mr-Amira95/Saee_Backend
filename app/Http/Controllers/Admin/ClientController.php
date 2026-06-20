<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\UserInvitationMail;
use App\Models\City;
use App\Models\ClientAttachment;
use App\Models\ClientProfile;
use App\Models\User;
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
            ->when($request->search, fn($query, $s) =>
                $query->where('company_name', 'like', "%$s%")
                      ->orWhere('company_name_ar', 'like', "%$s%")
                      ->orWhereHas('masterUser', fn($u) => $u->where('email', 'like', "%$s%"))
            )
            ->when($request->status, fn($query, $s) => $query->where('status', $s))
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
        $data = $request->validate([
            'name'                        => 'required|string|max:255',
            'email'                       => 'required|email|unique:users,email',
            'phone'                       => 'nullable|string|max:20',
            'phone_country_code'          => 'nullable|string|max:10',
            'company_name'                => 'required|string|max:255',
            'company_name_ar'             => 'nullable|string|max:255',
            'commercial_register_number'  => 'nullable|string|max:100',
            'vat_number'                  => 'nullable|string|max:50',
            'company_email'               => 'nullable|email|max:255',
            'address_line1'               => 'nullable|string|max:255',
            'city_id'                     => 'nullable|exists:cities,id',
            'area_id'                     => 'nullable|exists:areas,id',
            'credit_limit'                => 'nullable|numeric|min:0',
            'expiry_date'                 => 'nullable|date|after:today',
            'status'                      => ['nullable', Rule::in(['active','suspended','pending_verification'])],
            'logo'                        => 'nullable|image|max:2048',
            'attachment_labels'           => 'nullable|array',
            'attachment_labels.*'         => 'nullable|string|max:255',
            'attachment_files'            => 'nullable|array',
            'attachment_files.*'          => 'nullable|file|max:10240',
            'delivery_prices'             => 'nullable|array',
            'delivery_prices.*'           => 'nullable|numeric|min:0',
        ]);

        $user = DB::transaction(function () use ($data, $request) {
            $user = User::create([
                'name'               => $data['name'],
                'email'              => $data['email'],
                'password'           => Hash::make(Str::random(40)),
                'phone'              => $data['phone'] ?? null,
                'phone_country_code' => $data['phone_country_code'] ?? '+962',
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
                'logo_path'                    => $logoPath,
                'address_line1'                => $data['address_line1'] ?? null,
                'city_id'                      => $data['city_id'] ?? null,
                'area_id'                      => $data['area_id'] ?? null,
                'credit_limit'                 => $data['credit_limit'] ?? 0,
                'expiry_date'                  => $data['expiry_date'] ?? null,
                'status'                       => $data['status'] ?? 'pending_verification',
            ]);

            $this->saveAttachments($request, $client, $user->id);

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

        $this->sendInvitation($user);

        return redirect()->route('admin.clients.index')
            ->with('success', "Client account created. An invitation email has been sent to {$user->email}.");
    }

    public function show(ClientProfile $client)
    {
        $client->load('masterUser', 'city', 'area', 'employees.user', 'attachments');
        return view('admin.users.clients.show', compact('client'));
    }

    public function edit(ClientProfile $client)
    {
        $client->load('masterUser', 'city', 'area', 'attachments');
        $cities = City::orderBy('name')->get();
        $existingPrices = $client->deliveryPrices()->pluck('delivery_price', 'city_id');
        return view('admin.users.clients.edit', compact('client', 'cities', 'existingPrices'));
    }

    public function update(Request $request, ClientProfile $client)
    {
        $data = $request->validate([
            'name'                        => 'required|string|max:255',
            'email'                       => ['required','email', Rule::unique('users','email')->ignore($client->master_user_id)],
            'phone'                       => 'nullable|string|max:20',
            'phone_country_code'          => 'nullable|string|max:10',
            'company_name'                => 'required|string|max:255',
            'company_name_ar'             => 'nullable|string|max:255',
            'commercial_register_number'  => 'nullable|string|max:100',
            'vat_number'                  => 'nullable|string|max:50',
            'company_email'               => 'nullable|email|max:255',
            'address_line1'               => 'nullable|string|max:255',
            'city_id'                     => 'nullable|exists:cities,id',
            'area_id'                     => 'nullable|exists:areas,id',
            'credit_limit'                => 'nullable|numeric|min:0',
            'expiry_date'                 => 'nullable|date',
            'status'                      => ['nullable', Rule::in(['active','suspended','pending_verification'])],
            'user_status'                 => ['nullable', Rule::in(['active','suspended','pending'])],
            'logo'                        => 'nullable|image|max:2048',
            'attachment_labels'           => 'nullable|array',
            'attachment_labels.*'         => 'nullable|string|max:255',
            'attachment_files'            => 'nullable|array',
            'attachment_files.*'          => 'nullable|file|max:10240',
            'delete_attachment_ids'       => 'nullable|array',
            'delete_attachment_ids.*'     => 'nullable|integer',
        ]);

        DB::transaction(function () use ($data, $request, $client) {
            $client->masterUser->update([
                'name'               => $data['name'],
                'email'              => $data['email'],
                'phone'              => $data['phone'] ?? null,
                'phone_country_code' => $data['phone_country_code'] ?? $client->masterUser->phone_country_code,
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
                'logo_path'                   => $logoPath,
                'address_line1'               => $data['address_line1'] ?? null,
                'city_id'                     => $data['city_id'] ?? null,
                'area_id'                     => $data['area_id'] ?? null,
                'credit_limit'                => $data['credit_limit'] ?? $client->credit_limit,
                'expiry_date'                 => $data['expiry_date'] ?? null,
                'status'                      => $data['status'] ?? $client->status,
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

            $this->saveAttachments($request, $client, auth()->id());
        });

        return redirect()->route('admin.clients.show', $client)
            ->with('success', 'Client updated successfully.');
    }

    public function destroy(ClientProfile $client)
    {
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
        $this->sendInvitation($client->masterUser);
        return back()->with('success', "Invitation email resent to {$client->masterUser->email}.");
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

    private function sendInvitation(User $user): void
    {
        $token = Password::createToken($user);
        Mail::to($user->email)->send(new UserInvitationMail($user, $token));
    }
}
