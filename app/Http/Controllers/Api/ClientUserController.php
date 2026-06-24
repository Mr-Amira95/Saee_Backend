<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClientEmployee;
use App\Models\ClientProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ClientUserController extends Controller
{
    /**
     * List all employees in the authenticated client's company.
     * Accessible to both client_master and client_employee.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $clientProfile = $this->resolveClientProfile($user);

        if (! $clientProfile) {
            return $this->clientProfileNotFound();
        }

        $employees = ClientEmployee::with('user')
            ->where('client_profile_id', $clientProfile->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Users retrieved successfully.',
            'data'    => $employees->map(fn ($emp) => $this->formatEmployee($emp)),
        ]);
    }

    /**
     * Create a new employee under the authenticated client's company.
     * Only client_master may do this.
     */
    public function store(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if (! $user->isClientMaster()) {
            return response()->json([
                'success' => false,
                'message' => 'Only the account owner can add users.',
            ], 403);
        }

        $clientProfile = $user->clientProfile;

        if (! $clientProfile) {
            return $this->clientProfileNotFound();
        }

        $request->validate([
            'name'               => ['required', 'string', 'max:255'],
            'phone'              => ['required', 'string', 'max:20', 'unique:users,phone'],
            'phone_country_code' => ['nullable', 'string', 'max:10'],
            'email'              => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'password'           => ['required', 'string', 'min:8'],
            'job_title'          => ['nullable', 'string', 'max:100'],
        ]);

        $employee = null;

        DB::transaction(function () use ($request, $clientProfile, &$employee) {
            $newUser = User::create([
                'name'               => $request->input('name'),
                'phone'              => $request->input('phone'),
                'phone_country_code' => $request->input('phone_country_code', '+962'),
                'email'              => $request->input('email'),
                'password'           => Hash::make($request->input('password')),
                'role'               => 'client_employee',
                'status'             => 'active',
            ]);

            $employee = ClientEmployee::create([
                'user_id'           => $newUser->id,
                'client_profile_id' => $clientProfile->id,
                'job_title'         => $request->input('job_title'),
                'status'            => 'active',
            ]);

            $employee->setRelation('user', $newUser);
        });

        return response()->json([
            'success' => true,
            'message' => 'User created successfully.',
            'data'    => $this->formatEmployee($employee),
        ], 201);
    }

    /**
     * Update an employee's details.
     * Only client_master may do this, and only for employees in their own company.
     */
    public function update(Request $request, int $employeeId): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if (! $user->isClientMaster()) {
            return response()->json([
                'success' => false,
                'message' => 'Only the account owner can edit users.',
            ], 403);
        }

        $clientProfile = $user->clientProfile;

        if (! $clientProfile) {
            return $this->clientProfileNotFound();
        }

        $employee = ClientEmployee::with('user')
            ->where('id', $employeeId)
            ->where('client_profile_id', $clientProfile->id)
            ->first();

        if (! $employee) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        $request->validate([
            'name'               => ['sometimes', 'string', 'max:255'],
            'phone'              => ['sometimes', 'string', 'max:20', Rule::unique('users', 'phone')->ignore($employee->user_id)],
            'phone_country_code' => ['sometimes', 'nullable', 'string', 'max:10'],
            'email'              => ['sometimes', 'nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($employee->user_id)],
            'job_title'          => ['sometimes', 'nullable', 'string', 'max:100'],
            'status'             => ['sometimes', Rule::in(['active', 'suspended'])],
        ]);

        DB::transaction(function () use ($request, $employee) {
            $userFields = array_filter([
                'name'               => $request->input('name'),
                'phone'              => $request->input('phone'),
                'phone_country_code' => $request->input('phone_country_code'),
                'email'              => $request->input('email'),
            ], fn ($v) => $v !== null);

            if (! empty($userFields)) {
                $employee->user->update($userFields);
            }

            $employeeFields = array_filter([
                'job_title' => $request->input('job_title'),
                'status'    => $request->input('status'),
            ], fn ($v) => $v !== null);

            if (! empty($employeeFields)) {
                $employee->update($employeeFields);
            }
        });

        $employee->refresh()->load('user');

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully.',
            'data'    => $this->formatEmployee($employee),
        ]);
    }

    /**
     * Remove an employee from the company.
     * Only client_master may do this. Cannot delete their own account.
     */
    public function destroy(Request $request, int $employeeId): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if (! $user->isClientMaster()) {
            return response()->json([
                'success' => false,
                'message' => 'Only the account owner can remove users.',
            ], 403);
        }

        $clientProfile = $user->clientProfile;

        if (! $clientProfile) {
            return $this->clientProfileNotFound();
        }

        $employee = ClientEmployee::with('user')
            ->where('id', $employeeId)
            ->where('client_profile_id', $clientProfile->id)
            ->first();

        if (! $employee) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        if ($employee->user_id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot remove your own account.',
            ], 422);
        }

        DB::transaction(function () use ($employee) {
            $employee->user?->delete();
            $employee->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'User removed successfully.',
        ]);
    }

    private function resolveClientProfile(User $user): ?ClientProfile
    {
        if ($user->isClientMaster()) {
            return $user->clientProfile;
        }

        if ($user->isClientEmployee()) {
            return $user->clientEmployee?->clientProfile;
        }

        return null;
    }

    private function clientProfileNotFound(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Client profile not found.',
            'code'    => 'CLIENT_PROFILE_NOT_FOUND',
        ], 403);
    }

    private function formatEmployee(ClientEmployee $employee): array
    {
        return [
            'id'         => $employee->id,
            'user_id'    => $employee->user_id,
            'name'       => $employee->user?->name,
            'phone'      => $employee->user?->phone,
            'phone_country_code' => $employee->user?->phone_country_code,
            'email'      => $employee->user?->email,
            'job_title'  => $employee->job_title,
            'status'     => $employee->status,
            'user_status'=> $employee->user?->status,
            'created_at' => $employee->created_at?->toDateTimeString(),
        ];
    }
}
