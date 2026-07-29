<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ExpenseCategory;
use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Services\ExpenseService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    public function __construct(private ExpenseService $service) {}

    public function index(Request $request)
    {
        $from = $this->parseFilterDate($request->from);
        $to   = $this->parseFilterDate($request->to);

        $expenses = Expense::with('recordedBy')
            ->when($request->category, fn($q, $c) => $q->where('category', $c))
            ->when($from, fn($q, $d) => $q->whereDate('payment_date', '>=', $d))
            ->when($to, fn($q, $d) => $q->whereDate('payment_date', '<=', $d))
            ->latest('payment_date')
            ->paginate(20)
            ->withQueryString();

        $categories = ExpenseCategory::cases();

        $totals = Expense::query()
            ->when($request->category, fn($q, $c) => $q->where('category', $c))
            ->when($from, fn($q, $d) => $q->whereDate('payment_date', '>=', $d))
            ->when($to, fn($q, $d) => $q->whereDate('payment_date', '<=', $d))
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->get();

        return view('admin.expenses.index', compact('expenses', 'categories', 'totals'));
    }

    private function parseFilterDate(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        try {
            return Carbon::createFromFormat('d-m-Y', $value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    public function create()
    {
        $categories = ExpenseCategory::cases();
        return view('admin.expenses.create', compact('categories'));
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->hasAdminAction('finances.expenses'), 403);

        $data = $request->validate([
            'category'         => ['required', Rule::enum(ExpenseCategory::class)],
            'amount'           => 'required|numeric|min:0.01',
            'payment_date'     => 'required|date',
            'payment_method'   => ['required', Rule::in(['bank_transfer', 'cash', 'cliq', 'cheque'])],
            'description'      => 'required|string|max:500',
            'vendor'           => 'nullable|string|max:255',
            'reference_number' => 'nullable|string|max:100',
            'receipt'          => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ], [
            'receipt.max'   => __('The receipt/attachment must not exceed 5MB.'),
            'receipt.mimes' => __('The receipt/attachment must be a JPG, PNG or PDF file.'),
        ]);

        if ($request->hasFile('receipt')) {
            $data['receipt_path'] = $request->file('receipt')->store('expense-receipts', 'public');
        }

        $this->service->createExpense($data, auth()->user());

        return redirect()->route('admin.expenses.index')
            ->with('success', __('Expense recorded.'));
    }

    public function show(Expense $expense)
    {
        $expense->load('recordedBy');
        return view('admin.expenses.show', compact('expense'));
    }

    public function destroy(Expense $expense)
    {
        abort_unless(auth()->user()->hasAdminAction('finances.expenses'), 403);

        $expense->delete();

        return redirect()->route('admin.expenses.index')
            ->with('success', __('Expense deleted.'));
    }
}
