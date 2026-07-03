<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactFormSubmission extends Model
{
    protected $fillable = [
        'type',
        'name',
        'company',
        'monthly_volume',
        'email',
        'phone',
        'message',
        'status',
    ];
}
