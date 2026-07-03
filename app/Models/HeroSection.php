<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class HeroSection extends Model
{
    use HasTranslations;

    protected $fillable = [
        'badge',
        'title',
        'subtitle',
        'image_path',
    ];

    protected function casts(): array
    {
        return [
            'badge' => 'array',
            'title' => 'array',
            'subtitle' => 'array',
        ];
    }

    public static function instance(): self
    {
        return static::query()->firstOrCreate(['id' => 1]);
    }
}
