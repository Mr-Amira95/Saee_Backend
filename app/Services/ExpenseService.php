<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\DriverPayment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ExpenseService
{
    public function createExpense(array $data, User $actor): Expense
    {
        return Expense::create([
            'category'         => $data['category'],
            'amount'           => $data['amount'],
            'payment_date'     => $data['payment_date'],
            'payment_method'   => $data['payment_method'],
            'description'      => $data['description'],
            'vendor'           => $data['vendor'] ?? null,
            'reference_number' => $data['reference_number'] ?? null,
            'receipt_path'     => $data['receipt_path'] ?? null,
            'recorded_by'      => $actor->id,
        ]);
    }

    public function approveExpense(Expense $expense, User $actor): Expense
    {
        $expense->update([
            'approved_by' => $actor->id,
            'approved_at' => now(),
        ]);

        return $expense->fresh();
    }

    /**
     * Aggregate expenses + driver payments for a given calendar month.
     * Returns totals by category without double-counting driver salaries.
     */
    public function getMonthlySummary(Carbon $month): array
    {
        $start = $month->copy()->startOfMonth();
        $end   = $month->copy()->endOfMonth();

        $expensesByCategory = Expense::whereBetween('payment_date', [$start, $end])
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category')
            ->toArray();

        $driverPayrollTotal = DriverPayment::where('status', 'paid')
            ->whereBetween('paid_at', [$start, $end])
            ->sum('net_amount');

        return [
            'expenses_by_category' => $expensesByCategory,
            'expenses_total'       => array_sum($expensesByCategory),
            'driver_payroll_total' => (float) $driverPayrollTotal,
            'grand_total'          => array_sum($expensesByCategory) + (float) $driverPayrollTotal,
            'month'                => $month->format('Y-m'),
        ];
    }
}
