<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class ShowcasePage extends Model
{
    use HasTranslations;

    protected $fillable = [
        'page_badge',
        'page_title',
        'page_subtitle',
        'section_badge',
        'section_title',
        'section_subtitle',
    ];

    protected function casts(): array
    {
        return [
            'page_badge' => 'array',
            'page_title' => 'array',
            'page_subtitle' => 'array',
            'section_badge' => 'array',
            'section_title' => 'array',
            'section_subtitle' => 'array',
        ];
    }

    public static function instance(): self
    {
        return static::query()->firstOrCreate(['id' => 1]);
    }
}
