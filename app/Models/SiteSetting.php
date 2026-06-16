<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Get setting value by key.
     */
    public static function getVal(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        if ($setting) {
            $value = $setting->value;
            // Decode JSON if applicable
            if (is_string($value) && (str_starts_with($value, '{') || str_starts_with($value, '['))) {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $decoded;
                }
            }
            return $value;
        }
        return $default;
    }

    /**
     * Set setting value by key.
     */
    public static function setVal(string $key, $value)
    {
        $valStr = is_array($value) || is_object($value) ? json_encode($value) : $value;
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $valStr]
        );
    }
}
