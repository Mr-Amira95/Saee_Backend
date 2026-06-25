@extends('admin.layouts.app')

@section('title', 'Driver Payroll')
@section('page-title', 'Driver Payroll')

@section('breadcrumb')
    <span class="sep">/</span>
    <span class="current">Payroll</span>
@endsection

@section('content')
    <div class="page-hd">
        <div class="page-hd-left">
            <h1>Driver Payroll</h1>
            <p>Track and process monthly salary payments to drivers.</p>
        </div>
    </div>

    {{-- Filter bar --}}
    <div class="filter-bar">
        <form action="{{ route('admin.payroll.index') }}" method="GET" class="filter-form">
            <select name="driver_id" class="filter-select">
                <option value="">All Drivers</option>
                @foreach($drivers as $d)
                    <option value="{{ $d->id }}" {{ request('driver_id') == $d->id ? 'selected' : '' }}>
                        {{ $d->user->name }}
                    </option>
                @endforeach
            </select>

            <select name="status" class="filter-select">
                <option value="">All Statuses</option>
                <option value="draft"    {{ request('status') === 'draft'    ? 'selected' : '' }}>Draft</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="paid"     {{ request('status') === 'paid'     ? 'selected' : '' }}>Paid</option>
            </select>

            <button type="submit" class="btn-primary" style="box-shadow:none; padding: 8px 16px;">Filter</button>
            <a href="{{ route('admin.payroll.index') }}" class="btn-secondary" style="padding: 8px 16px;">Reset</a>
        </form>

        <div style="margin-left: auto;">
            <div style="font-size:.8rem;color:var(--text-dim);">
                Select a driver below to create a new payroll entry →
            </div>
        </div>
    </div>

    {{-- Quick-create links per driver --}}
    @if($drivers->isNotEmpty())
    <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:16px;">
        @foreach($drivers as $d)
            <a href="{{ route('admin.payroll.create', $d) }}"
               class="btn-secondary" style="padding:6px 14px;font-size:.82rem;">
                + {{ $d->user->name }}
            </a>
        @endforeach
    </div>
    @endif

    {{-- Table --}}
    <div class="table-card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Driver</th>
                        <th>Period</th>
                        <th>Basic Salary</th>
                        <th>Allowance</th>
                        <th>Bonus</th>
                        <th>Net Amount</th>
                        <th>Status</th>
                        <th style="width: 90px; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $p)
                        <tr>
                            <td>
                                <div class="cell-main">{{ $p->driverProfile->user->name }}</div>
                                <div class="cell-sub">{{ $p->driverProfile->user->phone }}</div>
                            </td>
                            <td>
                                <div class="cell-main">{{ $p->period_start->format('d M Y') }}</div>
                                <div class="cell-sub">to {{ $p->period_end->format('d M Y') }}</div>
                            </td>
                            <td>{{ number_format($p->basic_salary, 2) }} JD</td>
                            <td>{{ number_format($p->car_allowance, 2) }} JD</td>
                            <td>{{ number_format($p->extra_order_bonus * $p->extra_orders_count, 2) }} JD</td>
                            <td>
                                <strong style="color: #22c55e;">{{ number_format($p->net_amount, 2) }} JD</strong>
                            </td>
                            <td>
                                @if($p->status->value === 'draft')
                                    <span class="badge badge-pending">Draft</span>
                                @elseif($p->status->value === 'approved')
                                    <span class="badge badge-info">Approved</span>
                                @else
                                    <span class="badge badge-active">Paid</span>
                                @endif
                            </td>
                            <td>
                                <div class="act-btns" style="justify-content: center;">
                                    <a href="{{ route('admin.payroll.show', $p) }}" class="act-btn act-view" title="View">
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
                            <td colspan="8" style="text-align: center; color: var(--text-dim); padding: 40px;">
                                No payroll records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
            <div class="pagination-wrap">
                <div class="pag-info">Showing {{ $payments->firstItem() }}-{{ $payments->lastItem() }} of {{ $payments->total() }}</div>
                <div class="pag-links">{{ $payments->links() }}</div>
            </div>
        @endif
    </div>
@endsection
