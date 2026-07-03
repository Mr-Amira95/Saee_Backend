<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Public\Concerns\FormatsMedia;
use App\Models\Industry;
use App\Models\IndustrySection;
use Illuminate\Http\JsonResponse;

class IndustriesController extends Controller
{
    use FormatsMedia;

    public function show(): JsonResponse
    {
        $section = IndustrySection::instance();
        $items = Industry::where('status', 'active')->orderBy('sort_order')->get();

        return response()->json([
            'badge' => $section->badge,
            'title' => $section->title,
            'subtitle' => $section->subtitle,
            'items' => $items->map(fn (Industry $industry) => [
                'icon' => $this->mediaUrl($industry->icon_path),
                'title' => $industry->title,
                'subtitle' => $industry->subtitle,
            ]),
        ]);
    }
}
