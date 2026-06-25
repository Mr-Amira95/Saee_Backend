<?php

namespace App\Enums;

enum ExpenseCategory: string
{
    case EmployeeSalary     = 'employee_salary';
    case Rent               = 'rent';
    case Utilities          = 'utilities';
    case Fuel               = 'fuel';
    case VehicleMaintenance = 'vehicle_maintenance';
    case Insurance          = 'insurance';
    case Marketing          = 'marketing';
    case OfficeSupplies     = 'office_supplies';
    case Other              = 'other';

    public function label(): string
    {
        return match($this) {
            self::EmployeeSalary     => 'Employee Salary',
            self::Rent               => 'Rent',
            self::Utilities          => 'Utilities',
            self::Fuel               => 'Fuel',
            self::VehicleMaintenance => 'Vehicle Maintenance',
            self::Insurance          => 'Insurance',
            self::Marketing          => 'Marketing',
            self::OfficeSupplies     => 'Office Supplies',
            self::Other              => 'Other',
        };
    }
}
