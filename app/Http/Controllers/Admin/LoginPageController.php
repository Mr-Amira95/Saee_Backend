<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class LoginPageController extends Controller
{
    public function index()
    {
        $settings = [
            'login_brand_headline' => SiteSetting::getVal('login_brand_headline', 'Your Business, Delivered.'),
            'login_brand_subtitle' => SiteSetting::getVal('login_brand_subtitle', "SA'EE LOGISTICS PORTAL"),
            'login_brand_points'   => SiteSetting::getVal('login_brand_points', ['Track Orders', 'Manage Shipments', 'Real-time Updates']),
        ];

        if (!is_array($settings['login_brand_points'])) {
            $settings['login_brand_points'] = ['Track Orders', 'Manage Shipments', 'Real-time Updates'];
        }

        return view('admin.cms.login-page.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'login_brand_headline' => 'nullable|string|max:500',
            'login_brand_subtitle' => 'nullable|string|max:255',
            'login_brand_points'   => 'nullable|array',
            'login_brand_points.*' => 'nullable|string|max:100',
        ]);

        SiteSetting::setVal('login_brand_headline', $request->input('login_brand_headline'));
        SiteSetting::setVal('login_brand_subtitle', $request->input('login_brand_subtitle'));

        $points = array_values(array_filter($request->input('login_brand_points', [])));
        SiteSetting::setVal('login_brand_points', $points);

        return redirect()->route('admin.cms.login-page.index')
            ->with('success', 'Login page content updated successfully.');
    }
}
