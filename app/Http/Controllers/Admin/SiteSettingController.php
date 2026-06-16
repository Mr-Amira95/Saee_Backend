<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class SiteSettingController extends Controller
{
    public function index()
    {
        $settings = [
            'site_name'        => SiteSetting::getVal('site_name', 'SAEE Logistics'),
            'site_email'       => SiteSetting::getVal('site_email', 'info@saee.com.jo'),
            'site_phone'       => SiteSetting::getVal('site_phone', '+962 6 123 4567'),
            'site_address'     => SiteSetting::getVal('site_address', 'Amman, Jordan'),
            'meta_title'       => SiteSetting::getVal('meta_title', 'SAEE Logistics - Premier Delivery Solutions'),
            'meta_description' => SiteSetting::getVal('meta_description', 'SAEE is a premier delivery and logistics network connecting drivers and clients across the country.'),
            'meta_keywords'    => SiteSetting::getVal('meta_keywords', 'delivery, logistics, shipping, Jordan, saee'),
            'social_facebook'  => SiteSetting::getVal('social_facebook', 'https://facebook.com'),
            'social_twitter'   => SiteSetting::getVal('social_twitter', 'https://twitter.com'),
            'social_instagram' => SiteSetting::getVal('social_instagram', 'https://instagram.com'),
            'social_linkedin'  => SiteSetting::getVal('social_linkedin', 'https://linkedin.com'),
        ];

        return view('admin.settings.site.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'site_name'        => 'nullable|string|max:255',
            'site_email'       => 'nullable|email|max:255',
            'site_phone'       => 'nullable|string|max:255',
            'site_address'     => 'nullable|string|max:500',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords'    => 'nullable|string',
            'social_facebook'  => 'nullable|url|max:255',
            'social_twitter'   => 'nullable|url|max:255',
            'social_instagram' => 'nullable|url|max:255',
            'social_linkedin'  => 'nullable|url|max:255',
        ]);

        foreach ($validated as $key => $value) {
            SiteSetting::setVal($key, $value);
        }

        return redirect()->route('admin.settings.site.index')
            ->with('success', 'Site settings updated successfully.');
    }
}
