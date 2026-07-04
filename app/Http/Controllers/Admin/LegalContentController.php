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
            'terms_and_conditions' => $this->asBilingual(SiteSetting::getVal('terms_and_conditions', '')),
            'privacy_policy'       => $this->asBilingual(SiteSetting::getVal('privacy_policy', '')),
        ];

        return view('admin.settings.legal.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'terms_and_conditions.en' => 'nullable|string',
            'terms_and_conditions.ar' => 'nullable|string',
            'privacy_policy.en'       => 'nullable|string',
            'privacy_policy.ar'       => 'nullable|string',
        ]);

        SiteSetting::setVal('terms_and_conditions', [
            'en' => $request->input('terms_and_conditions.en', ''),
            'ar' => $request->input('terms_and_conditions.ar', ''),
        ]);

        SiteSetting::setVal('privacy_policy', [
            'en' => $request->input('privacy_policy.en', ''),
            'ar' => $request->input('privacy_policy.ar', ''),
        ]);

        return redirect()->route('admin.settings.legal.index')
            ->with('success', 'Legal content saved successfully.');
    }

    /**
     * Normalise a stored setting value to a ['en' => ..., 'ar' => ...] shape,
     * treating a pre-bilingual flat string as legacy English content.
     */
    private function asBilingual($value): array
    {
        if (is_array($value)) {
            return [
                'en' => $value['en'] ?? '',
                'ar' => $value['ar'] ?? '',
            ];
        }

        return ['en' => (string) $value, 'ar' => ''];
    }
}
