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
            'commercial_register_number'  => ['nullable', 'string', 'max:100'],
            'vat_number'                  => ['nullable', 'string', 'max:50'],
            'email'                       => ['nullable', 'email', 'max:255'],
            'company_phone'               => ['nullable', 'string', 'max:20'],
            'city_id'                     => ['nullable', 'exists:cities,id'],
            'area_id'                     => ['nullable', 'exists:areas,id'],
            'address_line1'               => ['nullable', 'string', 'max:500'],
        ]);

        $profile->update($validated);

        return back()->with('success', 'Company information updated.');
    }
}
