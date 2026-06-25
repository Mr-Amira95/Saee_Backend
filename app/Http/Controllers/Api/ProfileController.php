<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\UserResource;
use App\Models\Attendance;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if ($user->trashed()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
                'code'    => 'UNAUTHENTICATED',
            ], 401);
        }

        if ($user->isDriver()) {
            return $this->driverProfile($user);
        }

        if ($user->isClientMaster() || $user->isClientEmployee()) {
            return $this->clientProfile($user);
        }

        return response()->json([
            'success' => false,
            'message' => 'Profile not available for this account type.',
            'code'    => 'UNSUPPORTED_ACCOUNT_TYPE',
        ], 403);
    }

    private function driverProfile($user): JsonResponse
    {
        $driver = $user->driverProfile;

        if (! $driver) {
            return response()->json([
                'success' => false,
                'message' => 'Driver profile not found',
                'code'    => 'PROFILE_NOT_FOUND',
            ], 404);
        }

        $todayAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', today())
            ->latest('check_in_at')
            ->first();

        $attendanceStatus = match (true) {
            $todayAttendance === null               => 'not_checked_in',
            $todayAttendance->check_out_at === null => 'checked_in',
            default                                 => 'checked_out',
        };

        $attachments = [];

        if ($driver->avatar_path) {
            $attachments[] = ['type' => 'avatar', 'path' => $driver->avatar_path, 'url' => $this->storageUrl($driver->avatar_path)];
        }
        if ($driver->license_attachment) {
            $attachments[] = ['type' => 'license', 'path' => $driver->license_attachment, 'url' => $this->storageUrl($driver->license_attachment)];
        }
        if ($driver->car_license_attachment) {
            $attachments[] = ['type' => 'car_license', 'path' => $driver->car_license_attachment, 'url' => $this->storageUrl($driver->car_license_attachment)];
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile retrieved successfully',
            'data'    => [
                'user'        => new UserResource($user),
                'driver'      => [
                    'id'                      => $driver->id,
                    'national_id'             => $driver->national_id,
                    'national_id_verified_at' => $driver->national_id_verified_at?->toDateTimeString(),
                    'license_number'          => $driver->license_number,
                    'license_expiry_date'     => $driver->license_expiry_date?->toDateString(),
                    'license_class'           => $driver->license_class,
                    'is_available'            => $driver->is_available,
                    'current_latitude'        => $driver->current_latitude,
                    'current_longitude'       => $driver->current_longitude,
                    'location_updated_at'     => $driver->location_updated_at?->toDateTimeString(),
                    'average_rating'          => $user->average_rating,
                    'total_orders'            => $user->driverOrders()->count(),
                ],
                'car'         => [
                    'vehicle_type'       => $driver->vehicle_type,
                    'vehicle_plate'      => $driver->vehicle_plate,
                    'car_license_expiry' => $driver->car_license_expiry?->toDateString(),
                ],
                'attachments' => $attachments,
                'attendance'  => [
                    'status'             => $attendanceStatus,
                    'check_in_at'        => $todayAttendance?->check_in_at?->toDateTimeString(),
                    'check_out_at'       => $todayAttendance?->check_out_at?->toDateTimeString(),
                    'check_in_location'  => $todayAttendance?->check_in_location,
                    'check_out_location' => $todayAttendance?->check_out_location,
                ],
            ],
        ]);
    }

    private function clientProfile($user): JsonResponse
    {
        $clientProfile = $user->isClientMaster()
            ? $user->clientProfile
            : $user->clientEmployee?->clientProfile;

        if (! $clientProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Client profile not found.',
                'code'    => 'PROFILE_NOT_FOUND',
            ], 404);
        }

        $logoUrl = $clientProfile->logo_path
            ? $this->storageUrl($clientProfile->logo_path)
            : null;

        $data = [
            'user'    => new UserResource($user),
            'company' => [
                'id'                              => $clientProfile->id,
                'company_name'                    => $clientProfile->company_name,
                'company_name_ar'                 => $clientProfile->company_name_ar,
                'email'                           => $clientProfile->email,
                'commercial_register_number'      => $clientProfile->commercial_register_number,
                'commercial_register_verified_at' => $clientProfile->commercial_register_verified_at?->toDateTimeString(),
                'vat_number'                      => $clientProfile->vat_number,
                'address_line1'                   => $clientProfile->address_line1,
                'city_id'                         => $clientProfile->city_id,
                'area_id'                         => $clientProfile->area_id,
                'logo_url'                        => $logoUrl,
                'status'                          => $clientProfile->status,
                'expiry_date'                     => $clientProfile->expiry_date?->toDateString(),
            ],
        ];

        if ($user->isClientEmployee()) {
            $employee    = $user->clientEmployee;
            $data['employee'] = [
                'id'        => $employee->id,
                'job_title' => $employee->job_title,
                'status'    => $employee->status,
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile retrieved successfully.',
            'data'    => $data,
        ]);
    }

    private function storageUrl(string $path): string
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('public');
        return $disk->url($path);
    }
}
