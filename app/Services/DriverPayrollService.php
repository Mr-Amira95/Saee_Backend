<?php

namespace App\Services;

use App\Enums\DriverPaymentStatus;
use App\Models\DriverPayment;
use App\Models\DriverProfile;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DriverPayrollService
{
    public function createPaymentDraft(
        DriverProfile $driver,
        array $data,
        User $actor
    ): DriverPayment {
        return DB::transaction(function () use ($driver, $data, $actor) {
            $periodStart = Carbon::parse($data['period_start'])->startOfDay();
            $periodEnd   = Carbon::parse($data['period_end'])->endOfDay();

            $orderCount = Order::where('driver_profile_id', $driver->id)
                ->where('status', 'delivered')
                ->whereBetween('delivered_at', [$periodStart, $periodEnd])
                ->count();

            $basicSalary      = (float) ($data['basic_salary'] ?? 0);
            $carAllowance     = (float) ($data['car_allowance'] ?? 0);
            $extraOrdersCount = (int)   ($data['extra_orders_count'] ?? 0);
            $extraOrderBonus  = (float) ($data['extra_order_bonus'] ?? 0);
            $deductions       = (float) ($data['deductions'] ?? 0);

            $gross = $basicSalary + $carAllowance + ($extraOrdersCount * $extraOrderBonus);
            $net   = max(0, $gross - $deductions);

            return DriverPayment::create([
                'driver_profile_id'  => $driver->id,
                'period_start'       => $data['period_start'],
                'period_end'         => $data['period_end'],
                'basic_salary'       => $basicSalary,
                'car_allowance'      => $carAllowance,
                'order_count'        => $orderCount,
                'extra_orders_count' => $extraOrdersCount,
                'extra_order_bonus'  => $extraOrderBonus,
                'gross_amount'       => $gross,
                'deductions'         => $deductions,
                'net_amount'         => $net,
                'payment_method'     => $data['payment_method'],
                'reference_number'   => $data['reference_number'] ?? null,
                'status'             => DriverPaymentStatus::Draft,
                'notes'              => $data['notes'] ?? null,
                'recorded_by'        => $actor->id,
            ]);
        });
    }

    public function recordPayment(
        DriverPayment $payment,
        string $paymentMethod,
        ?string $reference,
        User $actor
    ): DriverPayment {
        $payment->update([
            'status'           => DriverPaymentStatus::Paid,
            'payment_method'   => $paymentMethod,
            'reference_number' => $reference,
            'paid_at'          => now(),
        ]);

        return $payment->fresh();
    }
}
