<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class AboutValue extends Model
{
    use HasTranslations;

    protected $fillable = [
        'text',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'text' => 'array',
            'sort_order' => 'integer',
        ];
    }
}
