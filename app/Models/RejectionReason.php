<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RejectionReason extends Model
{
    protected $fillable = ['reason', 'reason_ar', 'is_active'];
}
