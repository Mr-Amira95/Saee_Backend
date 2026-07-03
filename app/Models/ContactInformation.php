<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class ContactInformation extends Model
{
    use HasTranslations;

    protected $fillable = [
        'email',
        'phone',
        'address_link',
        'address_text',
        'working_hours_text',
    ];

    protected function casts(): array
    {
        return [
            'address_text' => 'array',
            'working_hours_text' => 'array',
        ];
    }

    public static function instance(): self
    {
        return static::query()->firstOrCreate(['id' => 1]);
    }
}
