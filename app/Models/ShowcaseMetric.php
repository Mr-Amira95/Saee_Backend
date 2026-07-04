<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class ShowcaseMetric extends Model
{
    use HasTranslations;

    protected $fillable = [
        'key',
        'value',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'key' => 'array',
            'value' => 'array',
            'sort_order' => 'integer',
        ];
    }
}
