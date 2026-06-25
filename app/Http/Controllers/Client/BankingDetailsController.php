<?php

namespace App\Http\Controllers\Client;

use App\Models\ClientBankDetail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BankingDetailsController extends Controller
{
    public function index(): View
    {
        $profile    = $this->getClientProfile();
        $bankDetail = $profile->bankDetail;

        return view('client.account.banking-details', compact('profile', 'bankDetail'));
    }

    public function save(Request $request): RedirectResponse
    {
        $profile = $this->getClientProfile();

        $data = $request->validate([
            'bank_name'       => ['required', 'string', 'max:255'],
            'account_name'    => ['required', 'string', 'max:255'],
            'iban'            => ['required', 'string', 'max:34'],
            'swift_code'      => ['nullable', 'string', 'max:11'],
            'account_number'  => ['nullable', 'string', 'max:50'],
            'cliq_alias_type' => ['nullable', 'in:alias,number'],
            'cliq_id'         => ['nullable', 'string', 'max:100'],
            'notes'           => ['nullable', 'string', 'max:1000'],
        ]);

        ClientBankDetail::updateOrCreate(
            ['client_profile_id' => $profile->id],
            $data
        );

        return back()->with('success', 'Banking details saved successfully.');
    }
}
