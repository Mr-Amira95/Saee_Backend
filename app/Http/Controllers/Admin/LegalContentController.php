<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class LegalContentController extends Controller
{
    public function index()
    {
        $settings = [
            'terms_and_conditions' => SiteSetting::getVal('terms_and_conditions', ''),
            'privacy_policy'       => SiteSetting::getVal('privacy_policy', ''),
        ];

        return view('admin.settings.legal.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'terms_and_conditions' => 'nullable|string',
            'privacy_policy'       => 'nullable|string',
        ]);

        SiteSetting::setVal('terms_and_conditions', $request->input('terms_and_conditions', ''));
        SiteSetting::setVal('privacy_policy', $request->input('privacy_policy', ''));

        return redirect()->route('admin.settings.legal.index')
            ->with('success', 'Legal content saved successfully.');
    }
}
