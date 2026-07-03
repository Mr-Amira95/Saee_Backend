<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Public\Concerns\FormatsMedia;
use App\Models\HeroSection;
use App\Models\HeroStat;
use Illuminate\Http\JsonResponse;

class HeroController extends Controller
{
    use FormatsMedia;

    public function show(): JsonResponse
    {
        $hero = HeroSection::instance();
        $stats = HeroStat::orderBy('sort_order')->get();

        return response()->json([
            'badge' => $hero->badge,
            'title' => $hero->title,
            'subtitle' => $hero->subtitle,
            'image' => $this->mediaUrl($hero->image_path),
            'stats' => $stats->map(fn (HeroStat $stat) => [
                'key' => $stat->key,
                'value' => $stat->value,
            ]),
        ]);
    }
}
