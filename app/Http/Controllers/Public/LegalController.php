<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;

class LegalController extends Controller
{
    public function terms(): JsonResponse
    {
        return response()->json([
            'type'    => 'terms_and_conditions',
            'content' => SiteSetting::getVal('terms_and_conditions', ''),
        ]);
    }

    public function privacy(): JsonResponse
    {
        return response()->json([
            'type'    => 'privacy_policy',
            'content' => SiteSetting::getVal('privacy_policy', ''),
        ]);
    }
}
