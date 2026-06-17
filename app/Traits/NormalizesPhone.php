<?php

namespace App\Traits;

trait NormalizesPhone
{
    /**
     * Build candidate phone strings from the three mobile-app inputs so that
     * a user can be found regardless of how the number is stored in the DB.
     */
    protected function phoneCandidates(string $phoneNumber, ?string $countryCode, ?string $fullPhone): array
    {
        $phoneDigits   = preg_replace('/\D/', '', $phoneNumber);
        $countryDigits = preg_replace('/\D/', '', $countryCode ?? '');
        $fullDigits    = preg_replace('/\D/', '', $fullPhone ?? '');

        $candidates = [];

        if ($fullPhone) {
            $candidates[] = $fullPhone;
            $candidates[] = '0' . ltrim($fullDigits, '0');
        }

        if ($phoneDigits) {
            $candidates[] = $phoneDigits;
            $candidates[] = '0' . $phoneDigits;
        }

        if ($countryDigits && $phoneDigits) {
            $candidates[] = $countryDigits . $phoneDigits;
            $candidates[] = '+' . $countryDigits . $phoneDigits;
        }

        return array_unique(array_filter($candidates));
    }
}
