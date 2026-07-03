<?php

namespace App\Models\Concerns;

trait HasTranslations
{
    /**
     * Get a translated value for a JSON-translatable column, falling back to English.
     */
    public function trans(string $field, ?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        $value = $this->{$field};

        if (!is_array($value)) {
            return $value;
        }

        return $value[$locale] ?? $value['en'] ?? null;
    }
}
