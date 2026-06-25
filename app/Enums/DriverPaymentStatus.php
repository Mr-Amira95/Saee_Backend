<?php

namespace App\Enums;

enum DriverPaymentStatus: string
{
    case Draft    = 'draft';
    case Approved = 'approved';
    case Paid     = 'paid';
}
