<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClientBankDetail;
use App\Models\ClientProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BankDetailController extends Controller
{
    /**
     * Return the company bank details.
     * Accessible to both client_master and client_employee.
     */
    public function show(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $clientProfile = $this->resolveClientProfile($user);

        if (! $clientProfile) {
            return $this->clientProfileNotFound();
        }

        $bankDetail = $clientProfile->bankDetail;

        return response()->json([
            'success' => true,
            'message' => 'Bank details retrieved successfully.',
            'data'    => $bankDetail ? $this->formatBankDetail($bankDetail) : null,
        ]);
    }

    /**
     * Create or update the company bank details.
     * Only client_master may do this.
     */
    public function update(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if (! $user->isClientMaster()) {
            return response()->json([
                'success' => false,
                'message' => 'Only the account owner can update bank details.',
            ], 403);
        }

        $clientProfile = $user->clientProfile;

        if (! $clientProfile) {
            return $this->clientProfileNotFound();
        }

        $data = $request->validate([
            'bank_name'       => ['required', 'string', 'max:255'],
            'account_name'    => ['required', 'string', 'max:255'],
            'iban'            => ['required', 'string', 'max:34'],
            'swift_code'      => ['nullable', 'string', 'max:11'],
            'account_number'  => ['nullable', 'string', 'max:50'],
            'cliq_alias_type' => ['nullable', Rule::in(['alias', 'number'])],
            'cliq_id'         => ['nullable', 'string', 'max:100'],
            'notes'           => ['nullable', 'string', 'max:1000'],
        ]);

        $bankDetail = ClientBankDetail::updateOrCreate(
            ['client_profile_id' => $clientProfile->id],
            $data
        );

        return response()->json([
            'success' => true,
            'message' => 'Bank details saved successfully.',
            'data'    => $this->formatBankDetail($bankDetail),
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

    private function formatBankDetail(ClientBankDetail $bankDetail): array
    {
        return [
            'id'              => $bankDetail->id,
            'bank_name'       => $bankDetail->bank_name,
            'account_name'    => $bankDetail->account_name,
            'account_number'  => $bankDetail->account_number,
            'iban'            => $bankDetail->iban,
            'swift_code'      => $bankDetail->swift_code,
            'cliq_alias_type' => $bankDetail->cliq_alias_type,
            'cliq_id'         => $bankDetail->cliq_id,
            'notes'           => $bankDetail->notes,
            'updated_at'      => $bankDetail->updated_at?->toDateTimeString(),
        ];
    }
}
