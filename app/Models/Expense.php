<?php

namespace App\Models;

use App\Enums\ExpenseCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category',
        'amount',
        'payment_date',
        'payment_method',
        'description',
        'vendor',
        'reference_number',
        'receipt_path',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'category'     => ExpenseCategory::class,
            'amount'       => 'decimal:2',
            'payment_date' => 'date',
        ];
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
