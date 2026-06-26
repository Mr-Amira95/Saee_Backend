<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\UserInvitationMail;
use App\Models\DriverBankDetail;
use App\Models\DriverProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DriverController extends Controller
{
    public function index(Request $request)
    {
        $q = DriverProfile::with('user')
            ->when($request->search, fn($query, $s) =>
                $query->where(function ($sub) use ($s) {
                    $sub->whereHas('user', fn($u) =>
                        $u->where('name', 'like', "%$s%")
                          ->orWhere('email', 'like', "%$s%")
                          ->orWhere('phone', 'like', "%$s%")
                    )
                    ->orWhere('vehicle_plate', 'like', "%$s%")
                    ->orWhere('vehicle_model', 'like', "%$s%")
                    ->orWhere('vehicle_color', 'like', "%$s%")
                    ->orWhere('national_id', 'like', "%$s%")
                    ->orWhere('license_number', 'like', "%$s%");
                })
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.drivers.index', compact('q'));
    }

    public function create()
    {
        return view('admin.users.drivers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                   => 'required|string|max:255',
            'email'                  => 'required|email|unique:users,email',
            'phone'                  => 'nullable|string|max:20|unique:users,phone',
            'phone_country_code'     => 'nullable|string|max:10',
            'national_id'            => 'required|string|max:20|unique:driver_profiles,national_id',
            'license_number'         => 'required|string|max:50|unique:driver_profiles,license_number',
            'license_expiry_date'    => 'required|date',
            'license_attachment'     => 'nullable|image|max:10240',
            'vehicle_type'           => 'nullable|string|max:50',
            'vehicle_plate'          => 'nullable|string|max:20|unique:driver_profiles,vehicle_plate',
            'car_license_expiry'     => 'nullable|date',
            'car_license_attachment' => 'nullable|image|max:10240',
            'bank_name'              => 'nullable|string|max:100',
            'account_name'           => 'nullable|string|max:100',
            'account_number'         => 'nullable|string|max:30',
            'iban'                   => 'nullable|string|max:34',
            'swift_code'             => 'nullable|string|max:11',
            'cliq_id'                => 'nullable|string|max:50',
            'cliq_alias_type'        => 'nullable|in:alias,phone',
            'bank_notes'             => 'nullable|string',
        ]);

        $user = DB::transaction(function () use ($data, $request) {
            $user = User::create([
                'name'               => $data['name'],
                'email'              => $data['email'],
                'password'           => Hash::make(Str::random(40)),
                'phone'              => $data['phone'] ?? null,
                'phone_country_code' => $data['phone_country_code'] ?? '+962',
                'role'               => 'driver',
                'status'             => 'active',
            ]);

            $licenseAttachment = null;
            if ($request->hasFile('license_attachment')) {
                $licenseAttachment = $request->file('license_attachment')
                    ->store("driver-licenses/{$user->id}", 'public');
            }

            $carLicenseAttachment = null;
            if ($request->hasFile('car_license_attachment')) {
                $carLicenseAttachment = $request->file('car_license_attachment')
                    ->store("driver-car-licenses/{$user->id}", 'public');
            }

            $profile = DriverProfile::create([
                'user_id'                => $user->id,
                'national_id'            => $data['national_id'],
                'license_number'         => $data['license_number'],
                'license_expiry_date'    => $data['license_expiry_date'],
                'license_attachment'     => $licenseAttachment,
                'vehicle_type'           => $data['vehicle_type'] ?? null,
                'vehicle_plate'          => $data['vehicle_plate'] ?? null,
                'car_license_expiry'     => $data['car_license_expiry'] ?? null,
                'car_license_attachment' => $carLicenseAttachment,
            ]);

            $profile->bankDetail()->create([
                'bank_name'       => $data['bank_name'] ?? null,
                'account_name'    => $data['account_name'] ?? null,
                'account_number'  => $data['account_number'] ?? null,
                'iban'            => $data['iban'] ?? null,
                'swift_code'      => $data['swift_code'] ?? null,
                'cliq_id'         => $data['cliq_id'] ?? null,
                'cliq_alias_type' => $data['cliq_alias_type'] ?? null,
                'notes'           => $data['bank_notes'] ?? null,
            ]);

            return $user;
        });

        $this->sendInvitation($user);

        return redirect()->route('admin.drivers.index')
            ->with('success', "Driver account created. An invitation email has been sent to {$user->email}.");
    }

    public function show(DriverProfile $driver)
    {
        $driver->load('user', 'bankDetail');
        return view('admin.users.drivers.show', compact('driver'));
    }

    public function edit(DriverProfile $driver)
    {
        $driver->load('user', 'bankDetail');
        return view('admin.users.drivers.edit', compact('driver'));
    }

    public function update(Request $request, DriverProfile $driver)
    {
        $data = $request->validate([
            'name'                   => 'required|string|max:255',
            'email'                  => ['required','email', Rule::unique('users','email')->ignore($driver->user_id)],
            'phone'                  => ['nullable','string','max:20', Rule::unique('users','phone')->ignore($driver->user_id)],
            'phone_country_code'     => 'nullable|string|max:10',
            'national_id'            => ['required','string','max:20', Rule::unique('driver_profiles','national_id')->ignore($driver->id)],
            'license_number'         => ['required','string','max:50', Rule::unique('driver_profiles','license_number')->ignore($driver->id)],
            'license_expiry_date'    => 'required|date',
            'license_attachment'     => 'nullable|image|max:10240',
            'vehicle_type'           => 'nullable|string|max:50',
            'vehicle_plate'          => ['nullable','string','max:20', Rule::unique('driver_profiles','vehicle_plate')->ignore($driver->id)],
            'car_license_expiry'     => 'nullable|date',
            'car_license_attachment' => 'nullable|image|max:10240',
            'bank_name'              => 'nullable|string|max:100',
            'account_name'           => 'nullable|string|max:100',
            'account_number'         => 'nullable|string|max:30',
            'iban'                   => 'nullable|string|max:34',
            'swift_code'             => 'nullable|string|max:11',
            'cliq_id'                => 'nullable|string|max:50',
            'cliq_alias_type'        => 'nullable|in:alias,phone',
            'bank_notes'             => 'nullable|string',
        ]);

        DB::transaction(function () use ($data, $request, $driver) {
            $driver->user->update([
                'name'               => $data['name'],
                'email'              => $data['email'],
                'phone'              => $data['phone'] ?? null,
                'phone_country_code' => $data['phone_country_code'] ?? $driver->user->phone_country_code,
                'status'             => $driver->user->status,
            ]);

            $licenseAttachment = $driver->license_attachment;
            if ($request->hasFile('license_attachment')) {
                if ($licenseAttachment) Storage::disk('public')->delete($licenseAttachment);
                $licenseAttachment = $request->file('license_attachment')
                    ->store("driver-licenses/{$driver->user_id}", 'public');
            }

            $carLicenseAttachment = $driver->car_license_attachment;
            if ($request->hasFile('car_license_attachment')) {
                if ($carLicenseAttachment) Storage::disk('public')->delete($carLicenseAttachment);
                $carLicenseAttachment = $request->file('car_license_attachment')
                    ->store("driver-car-licenses/{$driver->user_id}", 'public');
            }

            $driver->update([
                'national_id'            => $data['national_id'],
                'license_number'         => $data['license_number'],
                'license_expiry_date'    => $data['license_expiry_date'],
                'license_attachment'     => $licenseAttachment,
                'vehicle_type'           => $data['vehicle_type'] ?? null,
                'vehicle_plate'          => $data['vehicle_plate'] ?? null,
                'car_license_expiry'     => $data['car_license_expiry'] ?? null,
                'car_license_attachment' => $carLicenseAttachment,
                'is_available'           => $driver->is_available,
            ]);

            $driver->bankDetail()->updateOrCreate(
                ['driver_profile_id' => $driver->id],
                [
                    'bank_name'       => $data['bank_name'] ?? null,
                    'account_name'    => $data['account_name'] ?? null,
                    'account_number'  => $data['account_number'] ?? null,
                    'iban'            => $data['iban'] ?? null,
                    'swift_code'      => $data['swift_code'] ?? null,
                    'cliq_id'         => $data['cliq_id'] ?? null,
                    'cliq_alias_type' => $data['cliq_alias_type'] ?? null,
                    'notes'           => $data['bank_notes'] ?? null,
                ]
            );

        });

        return redirect()->route('admin.drivers.show', $driver)
            ->with('success', 'Driver updated successfully.');
    }

    public function bankDetails(DriverProfile $driver)
    {
        return response()->json($driver->bankDetail);
    }

    public function destroy(DriverProfile $driver)
    {
        DB::transaction(function () use ($driver) {
            $driver->user->delete();
            $driver->delete();
        });

        return redirect()->route('admin.drivers.index')
            ->with('success', 'Driver deleted successfully.');
    }

    public function locationHistory(Request $request, DriverProfile $driver)
    {
        $driver->load('user');

        $from = $request->filled('from')
            ? Carbon::parse($request->input('from'))
            : now()->startOfDay();

        $to = $request->filled('to')
            ? Carbon::parse($request->input('to'))
            : now();

        $points = $driver->locationHistories()
            ->whereBetween('recorded_at', [$from, $to])
            ->orderBy('recorded_at')
            ->get(['latitude', 'longitude', 'recorded_at', 'speed', 'accuracy']);

        // Compute total distance in km using Haversine
        $distanceKm = 0;
        foreach ($points as $i => $point) {
            if ($i === 0) continue;
            $prev        = $points[$i - 1];
            $distanceKm += $this->haversineKm(
                (float) $prev->latitude,  (float) $prev->longitude,
                (float) $point->latitude, (float) $point->longitude
            );
        }

        return view('admin.users.drivers.location-history', compact('driver', 'points', 'from', 'to', 'distanceKm'));
    }

    public function liveMap()
    {
        $drivers = DriverProfile::with('user')
            ->whereNotNull('current_latitude')
            ->whereNotNull('current_longitude')
            ->get(['id', 'user_id', 'current_latitude', 'current_longitude', 'location_updated_at']);

        return view('admin.users.drivers.live-map', compact('drivers'));
    }

    public function resendInvitation(DriverProfile $driver)
    {
        $this->sendInvitation($driver->user);

        return back()->with('success', "Invitation email resent to {$driver->user->email}.");
    }

    public function toggleStatus(DriverProfile $driver)
    {
        $user = $driver->user;
        if ($user) {
            $user->status = $user->status === 'active' ? 'suspended' : 'active';
            $user->save();
        }
        return back()->with('success', 'Driver status updated successfully.');
    }

    private function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $R    = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a    = sin($dLat / 2) ** 2
              + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    private function sendInvitation(User $user): void
    {
        $token = Password::createToken($user);
        Mail::to($user->email)->send(new UserInvitationMail($user, $token));
    }
}
