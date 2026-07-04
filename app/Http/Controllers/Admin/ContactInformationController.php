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
            'page_badge.en'             => 'nullable|string|max:255',
            'page_badge.ar'             => 'nullable|string|max:255',
            'page_title.en'             => 'nullable|string|max:255',
            'page_title.ar'             => 'nullable|string|max:255',
            'page_subtitle.en'          => 'nullable|string|max:1000',
            'page_subtitle.ar'          => 'nullable|string|max:1000',
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
            'page_badge'          => $validated['page_badge'] ?? null,
            'page_title'          => $validated['page_title'] ?? null,
            'page_subtitle'       => $validated['page_subtitle'] ?? null,
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
