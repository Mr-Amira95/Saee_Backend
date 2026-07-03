<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class AboutPage extends Model
{
    use HasTranslations;

    protected $fillable = [
        'page_badge',
        'page_title',
        'page_subtitle',
        'image_path',
        'mission',
        'vision',
    ];

    protected function casts(): array
    {
        return [
            'page_badge' => 'array',
            'page_title' => 'array',
            'page_subtitle' => 'array',
            'mission' => 'array',
            'vision' => 'array',
        ];
    }

    public static function instance(): self
    {
        return static::query()->firstOrCreate(['id' => 1]);
    }
}
