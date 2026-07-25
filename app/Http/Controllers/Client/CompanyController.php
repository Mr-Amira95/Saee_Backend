<?php

namespace App\Http\Controllers\Client;

use App\Models\Area;
use App\Models\City;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function index(): View
    {
        $profile = $this->getClientProfile();
        $profile->load(['city', 'area']);
        $cities = City::where('is_active', true)->orderBy('name')->get();

        return view('client.account.company', compact('profile', 'cities'));
    }

    public function update(Request $request): RedirectResponse
    {
        $profile = $this->getClientProfile();

        $validated = $request->validate([
            'company_name'                => ['required', 'string', 'max:255'],
            'commercial_register_number'  => ['nullable', 'string', 'max:100', 'regex:/^[0-9]+$/'],
            'vat_number'                  => ['nullable', 'string', 'max:50', 'regex:/^[0-9]+$/'],
            'email'                       => ['nullable', 'email', 'max:255', 'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'],
            'company_phone'               => ['nullable', 'string', 'max:20', 'regex:/^[0-9]{6,15}$/'],
            'city_id'                     => ['nullable', 'exists:cities,id'],
            'area_id'                     => ['nullable', 'exists:areas,id'],
            'address_line1'               => ['nullable', 'string', 'max:500'],
        ], [
            'commercial_register_number.regex' => 'The commercial registration number must contain numbers only.',
            'vat_number.regex' => 'The VAT number must contain numbers only.',
            'email.regex' => 'The email must be a valid address in the format name@domain.com.',
            'company_phone.regex' => 'The company phone must contain 6 to 15 digits only.',
        ]);

        $profile->update($validated);

        return back()->with('success', __('Company information updated.'));
    }
}
