<?php

namespace App\Enums;

enum DriverSalaryType: string
{
    case PerSalary = 'per_salary';
    case PerOrder  = 'per_order';
}
