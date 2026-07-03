<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use HasTranslations;

    protected $fillable = [
        'question',
        'answer',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'question' => 'array',
            'answer' => 'array',
            'sort_order' => 'integer',
        ];
    }
}
