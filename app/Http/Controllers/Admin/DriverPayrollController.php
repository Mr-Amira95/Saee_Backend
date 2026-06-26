<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DriverPaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\DriverPayment;
use App\Models\DriverProfile;
use App\Models\Order;
use App\Services\DriverPayrollService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DriverPayrollController extends Controller
{
    public function __construct(private DriverPayrollService $service) {}

    public function index(Request $request)
    {
        $payments = DriverPayment::with('driverProfile.user')
            ->when($request->driver_id, fn($q, $id) => $q->where('driver_profile_id', $id))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $drivers = DriverProfile::with('user')->orderBy('id')->get();

        return view('admin.payroll.index', compact('payments', 'drivers'));
    }

    public function create(DriverProfile $driver)
    {
        $driver->load('user');

        $periodStart = Carbon::now()->startOfMonth();
        $periodEnd   = Carbon::now()->endOfMonth();

        $existingThisMonth = DriverPayment::where('driver_profile_id', $driver->id)
            ->where('period_start', '<=', $periodEnd->toDateString())
            ->where('period_end',   '>=', $periodStart->toDateString())
            ->first();

        // Daily delivered order counts for the period (used by JS to auto-calculate extra orders)
        $dailyOrders = Order::where('driver_profile_id', $driver->id)
            ->where('status', 'delivered')
            ->whereBetween('delivered_at', [$periodStart->copy()->startOfDay(), $periodEnd->copy()->endOfDay()])
            ->selectRaw('DATE(delivered_at) as day, COUNT(*) as cnt')
            ->groupBy('day')
            ->pluck('cnt', 'day');

        return view('admin.payroll.create', compact(
            'driver', 'periodStart', 'periodEnd', 'dailyOrders', 'existingThisMonth'
        ));
    }

    public function store(Request $request, DriverProfile $driver)
    {
        $data = $request->validate([
            'period_start'       => 'required|date',
            'period_end'         => 'required|date|after_or_equal:period_start',
            'basic_salary'       => 'required|numeric|min:0',
            'car_allowance'      => 'required|numeric|min:0',
            'extra_orders_count' => 'nullable|integer|min:0',
            'extra_order_bonus'  => 'nullable|numeric|min:0',
            'deductions'         => 'nullable|numeric|min:0',
            'payment_method'     => ['required', Rule::in(['bank_transfer', 'cash', 'cliq'])],
            'reference_number'   => 'nullable|string|max:100',
            'notes'              => 'nullable|string',
        ]);

        $overlap = DriverPayment::where('driver_profile_id', $driver->id)
            ->where('period_start', '<=', $data['period_end'])
            ->where('period_end',   '>=', $data['period_start'])
            ->exists();

        if ($overlap) {
            return back()->withInput()->withErrors([
                'period_start' => 'A payroll record already exists for this driver that overlaps with the selected period.',
            ]);
        }

        $payment = $this->service->createPaymentDraft($driver, $data, auth()->user());

        return redirect()->route('admin.payroll.show', $payment)
            ->with('success', 'Payroll entry created.');
    }

    public function show(DriverPayment $payment)
    {
        $payment->load('driverProfile.user', 'recordedBy');
        return view('admin.payroll.show', compact('payment'));
    }

    public function pay(Request $request, DriverPayment $payment)
    {
        abort_if($payment->status === DriverPaymentStatus::Paid, 403, 'Already paid.');

        $data = $request->validate([
            'payment_method'   => ['required', Rule::in(['bank_transfer', 'cash', 'cliq'])],
            'reference_number' => 'nullable|string|max:100',
        ]);

        $this->service->recordPayment(
            $payment,
            $data['payment_method'],
            $data['reference_number'] ?? null,
            auth()->user()
        );

        return back()->with('success', 'Payment marked as paid.');
    }

    public function destroy(DriverPayment $payment)
    {
        abort_if($payment->status === DriverPaymentStatus::Paid, 403, 'Paid records cannot be deleted.');

        $payment->delete();

        return redirect()->route('admin.payroll.index')
            ->with('success', 'Payroll entry deleted.');
    }
}
