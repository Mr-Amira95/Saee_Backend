<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;

class SiteSettingController extends Controller
{
    public function show(): JsonResponse
    {
        return response()->json([
            'siteName' => SiteSetting::getVal('site_name', 'SAEE Logistics'),
            'social' => [
                'facebook'  => SiteSetting::getVal('social_facebook', ''),
                'twitter'   => SiteSetting::getVal('social_twitter', ''),
                'instagram' => SiteSetting::getVal('social_instagram', ''),
                'linkedin'  => SiteSetting::getVal('social_linkedin', ''),
            ],
        ]);
    }
}
