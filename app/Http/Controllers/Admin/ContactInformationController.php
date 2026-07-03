<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactInformation;
use Illuminate\Http\Request;

class ContactInformationController extends Controller
{
    public function index()
    {
        $contact = ContactInformation::instance();

        return view('admin.cms.contact-information.index', compact('contact'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'email'                     => 'nullable|email|max:255',
            'phone'                     => 'nullable|string|max:50',
            'address_link'              => 'nullable|string|max:2048',
            'address_text.en'           => 'nullable|string|max:1000',
            'address_text.ar'           => 'nullable|string|max:1000',
            'working_hours_text.en'     => 'nullable|string|max:500',
            'working_hours_text.ar'     => 'nullable|string|max:500',
        ]);

        $contact = ContactInformation::instance();

        $contact->update([
            'email'               => $validated['email'] ?? null,
            'phone'               => $validated['phone'] ?? null,
            'address_link'        => $validated['address_link'] ?? null,
            'address_text'        => $validated['address_text'] ?? null,
            'working_hours_text'  => $validated['working_hours_text'] ?? null,
        ]);

        return redirect()->route('admin.cms.contact-information.index')
            ->with('success', 'Contact information updated successfully.');
    }
}
