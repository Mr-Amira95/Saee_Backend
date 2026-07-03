<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Public\Concerns\FormatsMedia;
use App\Models\WhySaeeReason;
use App\Models\WhySaeeSection;
use Illuminate\Http\JsonResponse;

class WhySaeeController extends Controller
{
    use FormatsMedia;

    public function show(): JsonResponse
    {
        $section = WhySaeeSection::instance();
        $reasons = WhySaeeReason::where('status', 'active')->orderBy('sort_order')->get();

        return response()->json([
            'badge' => $section->badge,
            'title' => $section->title,
            'subtitle' => $section->subtitle,
            'reasons' => $reasons->map(fn (WhySaeeReason $reason) => [
                'icon' => $this->mediaUrl($reason->icon_path),
                'title' => $reason->title,
                'subtitle' => $reason->subtitle,
            ]),
        ]);
    }
}
