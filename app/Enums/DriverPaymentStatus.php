<?php

namespace App\Enums;

enum DriverPaymentStatus: string
{
    case Draft = 'draft';
    case Paid  = 'paid';
}
