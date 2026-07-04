<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class ContactInformation extends Model
{
    use HasTranslations;

    protected $fillable = [
        'page_badge',
        'page_title',
        'page_subtitle',
        'email',
        'phone',
        'address_link',
        'address_text',
        'working_hours_text',
    ];

    protected function casts(): array
    {
        return [
            'page_badge' => 'array',
            'page_title' => 'array',
            'page_subtitle' => 'array',
            'address_text' => 'array',
            'working_hours_text' => 'array',
        ];
    }

    public static function instance(): self
    {
        return static::query()->firstOrCreate(['id' => 1]);
    }
}
