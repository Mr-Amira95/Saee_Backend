<?php

namespace App\Enums;

enum DeliveryShift: string
{
    case DoesntMatter = 'doesnt_matter';
    case Before12pm   = 'before_12pm';
    case After12pm    = 'after_12pm';

    public function label(): string
    {
        return match($this) {
            self::DoesntMatter => "Doesn't Matter",
            self::Before12pm   => 'Before 12 PM',
            self::After12pm    => 'After 12 PM',
        };
    }
}
