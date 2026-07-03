<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class ShowcaseHowItWork extends Model
{
    use HasTranslations;

    protected $fillable = [
        'icon',
        'title',
        'subtitle',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'subtitle' => 'array',
            'sort_order' => 'integer',
        ];
    }
}
