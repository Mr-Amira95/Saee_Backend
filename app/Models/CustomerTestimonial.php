<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class CustomerTestimonial extends Model
{
    use HasTranslations;

    protected $fillable = [
        'feedback',
        'client',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'feedback' => 'array',
            'sort_order' => 'integer',
        ];
    }
}
