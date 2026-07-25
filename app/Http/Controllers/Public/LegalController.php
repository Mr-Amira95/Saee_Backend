<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class LegalController extends Controller
{
    /**
     * Legacy endpoint (consumed by the driver app) — returns a single-language string.
     * Language resolves from ?lang=ar, falling back to the Accept-Language header (see SetLocale), then English.
     */
    public function terms(Request $request): JsonResponse
    {
        return response()->json([
            'type'    => 'terms_and_conditions',
            'content' => $this->resolveSingle('terms_and_conditions', $request),
        ]);
    }

    public function privacy(Request $request): JsonResponse
    {
        return response()->json([
            'type'    => 'privacy_policy',
            'content' => $this->resolveSingle('privacy_policy', $request),
        ]);
    }

    /**
     * Bilingual endpoints — consumed by the marketing website.
     */
    public function termsBilingual(): JsonResponse
    {
        return response()->json([
            'type'    => 'terms_and_conditions',
            'content' => $this->resolveBilingual('terms_and_conditions'),
        ]);
    }

    public function privacyBilingual(): JsonResponse
    {
        return response()->json([
            'type'    => 'privacy_policy',
            'content' => $this->resolveBilingual('privacy_policy'),
        ]);
    }

    private function resolveSingle(string $key, Request $request): string
    {
        $value = SiteSetting::getVal($key, '');
        $lang  = $request->query('lang') ?: App::getLocale();
        $lang  = $lang === 'ar' ? 'ar' : 'en';

        if (is_array($value)) {
            return $value[$lang] ?? $value['en'] ?? '';
        }

        return (string) $value;
    }

    private function resolveBilingual(string $key): array
    {
        $value = SiteSetting::getVal($key, '');

        if (is_array($value)) {
            return ['en' => $value['en'] ?? '', 'ar' => $value['ar'] ?? ''];
        }

        // Legacy flat-string value stored before bilingual support was added.
        return ['en' => (string) $value, 'ar' => ''];
    }
}
