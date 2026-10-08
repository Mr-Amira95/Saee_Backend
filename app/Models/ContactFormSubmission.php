<?php

namespace App\Models;

use App\Models\Concerns\BroadcastsRealtimeChanges;
use Illuminate\Database\Eloquent\Model;

class ContactFormSubmission extends Model
{
    use BroadcastsRealtimeChanges;

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
