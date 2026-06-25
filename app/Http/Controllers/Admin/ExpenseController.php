<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ExpenseCategory;
use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Services\ExpenseService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    public function __construct(private ExpenseService $service) {}

    public function index(Request $request)
    {
        $expenses = Expense::with('recordedBy')
            ->when($request->category, fn($q, $c) => $q->where('category', $c))
            ->when($request->from, fn($q, $d) => $q->whereDate('payment_date', '>=', $d))
            ->when($request->to, fn($q, $d) => $q->whereDate('payment_date', '<=', $d))
            ->latest('payment_date')
            ->paginate(20)
            ->withQueryString();

        $categories = ExpenseCategory::cases();

        return view('admin.expenses.index', compact('expenses', 'categories'));
    }

    public function create()
    {
        $categories = ExpenseCategory::cases();
        return view('admin.expenses.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category'         => ['required', Rule::enum(ExpenseCategory::class)],
            'amount'           => 'required|numeric|min:0.01',
            'payment_date'     => 'required|date',
            'payment_method'   => ['required', Rule::in(['bank_transfer', 'cash', 'cliq', 'cheque'])],
            'description'      => 'required|string|max:500',
            'vendor'           => 'nullable|string|max:255',
            'reference_number' => 'nullable|string|max:100',
            'receipt'          => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        if ($request->hasFile('receipt')) {
            $data['receipt_path'] = $request->file('receipt')->store('expense-receipts', 'public');
        }

        $this->service->createExpense($data, auth()->user());

        return redirect()->route('admin.expenses.index')
            ->with('success', 'Expense recorded successfully.');
    }

    public function show(Expense $expense)
    {
        $expense->load('recordedBy', 'approvedBy');
        return view('admin.expenses.show', compact('expense'));
    }

    public function approve(Expense $expense)
    {
        $this->service->approveExpense($expense, auth()->user());

        return back()->with('success', 'Expense approved.');
    }

    public function destroy(Expense $expense)
    {
        abort_if($expense->approved_at !== null, 403, 'Approved expenses cannot be deleted.');

        $expense->delete();

        return redirect()->route('admin.expenses.index')
            ->with('success', 'Expense deleted.');
    }
}
