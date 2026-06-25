<?php

namespace App\Enums;

enum DeliveryInvoiceStatus: string
{
    case Draft     = 'draft';
    case Issued    = 'issued';
    case Paid      = 'paid';
    case Overdue   = 'overdue';
    case Cancelled = 'cancelled';
}
