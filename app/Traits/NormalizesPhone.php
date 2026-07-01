<?php

namespace App\Traits;

trait NormalizesPhone
{
    /**
     * Build candidate phone strings from a single free-text input so a user can be
     * found regardless of whether they typed +{cc}{number}, {cc}{number}, or {number},
     * and regardless of how the number is stored in the DB (with/without leading 0, cc, etc).
     */
    protected function phoneCandidates(string $input): array
    {
        $trimmed = trim($input);
        $digits  = preg_replace('/\D/', '', $trimmed);

        $candidates = array_filter([
            $trimmed,
            $digits,
            $digits !== '' ? '0' . ltrim($digits, '0') : null,
        ], fn ($v) => $v !== null && $v !== '');

        // Strip 1-3 leading digits (possible country code lengths) to derive local-format candidates.
        for ($strip = 1; $strip <= 3; $strip++) {
            if (\strlen($digits) > $strip + 7) {
                $local        = substr($digits, $strip);
                $candidates[] = $local;
                $candidates[] = '0' . $local;
            }
        }

        return array_values(array_unique(array_filter($candidates)));
    }
}
