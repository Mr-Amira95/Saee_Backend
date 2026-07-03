<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Public\Concerns\FormatsMedia;
use App\Models\FlowSection;
use App\Models\FlowStep;
use Illuminate\Http\JsonResponse;

class FlowController extends Controller
{
    use FormatsMedia;

    public function show(): JsonResponse
    {
        $section = FlowSection::instance();
        $steps = FlowStep::where('status', 'active')->orderBy('sort_order')->get();

        return response()->json([
            'badge' => $section->badge,
            'title' => $section->title,
            'subtitle' => $section->subtitle,
            'steps' => $steps->map(fn (FlowStep $step) => [
                'image' => $this->mediaUrl($step->image_path),
                'title' => $step->title,
                'subtitle' => $step->subtitle,
                'order' => $step->sort_order,
            ]),
        ]);
    }
}
