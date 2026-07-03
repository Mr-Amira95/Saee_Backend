<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasTranslations;

    protected $fillable = [
        'title',
        'subtitle',
        'icon',
        'status',
        'sort_order',
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
