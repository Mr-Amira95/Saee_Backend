@extends('admin.layouts.app')

@section('title', __('Client Delivery Billing'))
@section('page-title', __('Client Delivery Billing'))

@section('breadcrumb')
    <span class="sep">/</span>
    <span class="current">{{ __('Billing') }}</span>
@endsection

@section('content')
    <div class="page-hd">
        <div class="page-hd-left">
            <h1>{{ __('Client Delivery Billing') }}</h1>
            <p>{{ __('Generate and manage delivery fee invoices billed to clients.') }}</p>
        </div>
    </div>

    {{-- Filter bar --}}
    <div class="filter-bar">
        <form action="{{ route('admin.billing.index') }}" method="GET" class="filter-form">
            <select name="client_id" class="filter-select">
                <option value="">{{ __('All Clients') }}</option>
                @foreach($clients as $c)
                    <option value="{{ $c->id }}" {{ request('client_id') == $c->id ? 'selected' : '' }}>
                        {{ $c->company_name ?? $c->user->name }}
                    </option>
                @endforeach
            </select>

            <select name="status" class="filter-select">
                <option value="">{{ __('All Statuses') }}</option>
                <option value="draft"     {{ request('status') === 'draft'     ? 'selected' : '' }}>{{ __('Draft') }}</option>
                <option value="issued"    {{ request('status') === 'issued'    ? 'selected' : '' }}>{{ __('Issued') }}</option>
                <option value="paid"      {{ request('status') === 'paid'      ? 'selected' : '' }}>{{ __('Paid') }}</option>
                <option value="overdue"   {{ request('status') === 'overdue'   ? 'selected' : '' }}>{{ __('Overdue') }}</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
            </select>

            <button type="submit" class="btn-primary" style="box-shadow:none; padding: 8px 16px;">{{ __('Filter') }}</button>
            <a href="{{ route('admin.billing.index') }}" class="btn-secondary" style="padding: 8px 16px;">{{ __('Reset') }}</a>
        </form>

        <div style="margin-left: auto;">
            <div style="font-size:.8rem;color:var(--text-dim);">
                {{ __('Select a client below to generate a new invoice →') }}
            </div>
        </div>
    </div>

    {{-- Quick-create links per client --}}
    @if($clients->isNotEmpty() && auth()->user()->hasAdminAction('finances.client_billing'))
    <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:16px;">
        @foreach($clients as $c)
            <a href="{{ route('admin.billing.create', $c) }}"
               class="btn-secondary" style="padding:6px 14px;font-size:.82rem;">
                + {{ $c->company_name ?? $c->user->name }}
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
                        <th>{{ __('Invoice #') }}</th>
                        <th>{{ __('Client') }}</th>
                        <th>{{ __('Period') }}</th>
                        <th>{{ __('Billable Orders') }}</th>
                        <th>{{ __('Net Amount') }}</th>
                        <th>{{ __('Due Date') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th style="width: 90px; text-align: center;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                        <tr>
                            <td>
                                <div class="cell-main" style="font-family:monospace;font-size:.82rem;">{{ $inv->invoice_number }}</div>
                                <div class="cell-sub">{{ $inv->created_at->format('d M Y') }}</div>
                            </td>
                            <td>
                                <div class="cell-main">{{ $inv->clientProfile->company_name ?? $inv->clientProfile->user->name }}</div>
                            </td>
                            <td>
                                <div class="cell-main">{{ $inv->period_start->format('d M Y') }}</div>
                                <div class="cell-sub">{{ __('to') }} {{ $inv->period_end->format('d M Y') }}</div>
                            </td>
                            <td>{{ $inv->billable_orders }}</td>
                            <td>
                                <strong>{{ number_format($inv->net_amount, 2) }} JD</strong>
                            </td>
                            <td>
                                @if($inv->due_date)
                                    <div class="{{ $inv->status->value === 'overdue' ? 'cell-main' : '' }}"
                                         style="{{ $inv->status->value === 'overdue' ? 'color:#f87171;' : '' }}">
                                        {{ $inv->due_date->format('d M Y') }}
                                    </div>
                                @else
                                    <span style="color:var(--text-dim);">—</span>
                                @endif
                            </td>
                            <td>
                                @php $sv = $inv->status->value; @endphp
                                @if($sv === 'draft')
                                    <span class="badge badge-pending">{{ __('Draft') }}</span>
                                @elseif($sv === 'issued')
                                    <span class="badge badge-info">{{ __('Issued') }}</span>
                                @elseif($sv === 'paid')
                                    <span class="badge badge-active">{{ __('Paid') }}</span>
                                @elseif($sv === 'overdue')
                                    <span class="badge badge-suspended">{{ __('Overdue') }}</span>
                                @else
                                    <span class="badge">{{ __('Cancelled') }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="act-btns" style="justify-content: center;">
                                    <a href="{{ route('admin.billing.show', $inv) }}" class="act-btn act-view" title="{{ __('View') }}">
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
                                {{ __('No delivery invoices found.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
            <div class="pagination-wrap">
                <div class="pag-info">{{ __('Showing') }} {{ $invoices->firstItem() }}-{{ $invoices->lastItem() }} {{ __('of') }} {{ $invoices->total() }}</div>
                <div class="pag-links">{{ $invoices->links() }}</div>
            </div>
        @endif
    </div>
@endsection
