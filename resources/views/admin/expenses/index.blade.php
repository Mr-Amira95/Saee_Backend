@extends('admin.layouts.app')

@section('title', 'Expenses')
@section('page-title', 'Expenses')

@section('breadcrumb')
    <span class="sep">/</span>
    <span class="current">Expenses</span>
@endsection

@section('content')
    <div class="page-hd">
        <div class="page-hd-left">
            <h1>Expenses</h1>
            <p>Track operational expenses: rent, utilities, fuel, and other costs.</p>
        </div>
        <div class="page-hd-right">
            <a href="{{ route('admin.expenses.create') }}" class="btn-primary">+ Record Expense</a>
        </div>
    </div>

    {{-- Filter bar --}}
    <div class="filter-bar" style="flex-wrap:nowrap;gap:8px;">
        <div class="filter-search-wrap" style="min-width:160px;max-width:220px;flex-shrink:0;">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35"/>
            </svg>
            <input type="text" id="tableSearch" class="filter-input" placeholder="Search..." autocomplete="off">
        </div>

        <form action="{{ route('admin.expenses.index') }}" method="GET" style="display:flex;align-items:center;gap:8px;flex-wrap:nowrap;flex:1;">
            <select name="category" class="filter-select" style="flex-shrink:0;">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->value }}" {{ request('category') === $cat->value ? 'selected' : '' }}>
                        {{ $cat->label() }}
                    </option>
                @endforeach
            </select>

            <input type="date" name="from" class="filter-input" style="width:auto;min-width:130px;padding-left:12px;flex-shrink:0;" value="{{ request('from') }}">
            <input type="date" name="to"   class="filter-input" style="width:auto;min-width:130px;padding-left:12px;flex-shrink:0;" value="{{ request('to') }}">

            <button type="submit" class="btn-primary" style="box-shadow:none;padding:8px 16px;white-space:nowrap;flex-shrink:0;">Filter</button>
            <a href="{{ route('admin.expenses.index') }}" class="btn-secondary" style="padding:8px 16px;white-space:nowrap;flex-shrink:0;">Reset</a>
        </form>
    </div>

    {{-- Category totals (current filter period) --}}
    @if($totals->isNotEmpty())
    <div class="mini-stats" style="margin-bottom:18px;flex-wrap:wrap;">
        @foreach($totals as $t)
        <div class="mini-stat">
            <div>
                <div class="ms-val">{{ number_format($t->total, 2) }} JD</div>
                <div class="ms-lbl">{{ $t->category->label() }}</div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Table --}}
    <div class="table-card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Vendor</th>
                        <th>Amount</th>
                        <th style="width: 90px; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $exp)
                        <tr>
                            <td>
                                <div class="cell-main">{{ $exp->payment_date->format('d M Y') }}</div>
                                <div class="cell-sub">{{ ucfirst(str_replace('_', ' ', $exp->payment_method)) }}</div>
                            </td>
                            <td>
                                <span class="badge badge-pv">{{ $exp->category->label() }}</span>
                            </td>
                            <td>
                                <div class="cell-main">{{ Str::limit($exp->description, 50) }}</div>
                            </td>
                            <td>
                                <div class="cell-sub">{{ $exp->vendor ?? '—' }}</div>
                            </td>
                            <td>
                                <strong>{{ number_format($exp->amount, 2) }} JD</strong>
                            </td>
                            <td>
                                <div class="act-btns" style="justify-content: center;">
                                    <a href="{{ route('admin.expenses.show', $exp) }}" class="act-btn act-view" title="View">
                                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--text-dim); padding: 40px;">
                                No expenses found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($expenses->hasPages())
            <div class="pagination-wrap">
                <div class="pag-info">Showing {{ $expenses->firstItem() }}-{{ $expenses->lastItem() }} of {{ $expenses->total() }}</div>
                <div class="pag-links">{{ $expenses->links() }}</div>
            </div>
        @endif
    </div>
@endsection

@section('scripts')
<script>
document.getElementById('tableSearch').addEventListener('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('tbody tr').forEach(function (row) {
        row.style.display = !q || row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>
@endsection
