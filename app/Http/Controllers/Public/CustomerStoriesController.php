<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\CustomerStorySection;
use App\Models\CustomerTestimonial;
use Illuminate\Http\JsonResponse;

class CustomerStoriesController extends Controller
{
    public function show(): JsonResponse
    {
        $section = CustomerStorySection::instance();
        $testimonials = CustomerTestimonial::where('status', 'active')->orderBy('sort_order')->get();

        return response()->json([
            'badge' => $section->badge,
            'title' => $section->title,
            'subtitle' => $section->subtitle,
            'testimonials' => $testimonials->map(fn (CustomerTestimonial $item) => [
                'feedback' => $item->feedback,
                'client' => $item->client,
            ]),
        ]);
    }
}
