<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ContactInformation;
use Illuminate\Http\JsonResponse;

class ContactInformationController extends Controller
{
    public function show(): JsonResponse
    {
        $info = ContactInformation::instance();

        return response()->json([
            'page' => [
                'badge' => $info->page_badge,
                'title' => $info->page_title,
                'subtitle' => $info->page_subtitle,
            ],
            'email' => $info->email,
            'phone' => $info->phone,
            'addressLink' => $info->address_link,
            'addressText' => $info->address_text,
            'workingHoursText' => $info->working_hours_text,
        ]);
    }
}
