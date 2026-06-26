@extends('admin.layouts.app')

@section('title', 'Client Invoices')
@section('page-title', 'Client Invoices')

@section('breadcrumb')
    <span class="sep">/</span>
    <span class="current">Invoices</span>
@endsection

@section('content')
    <div class="page-hd">
        <div class="page-hd-left">
            <h1>Client Invoices</h1>
            <p>View payout histories, gross COD collections, delivery charge debits, and printable receipts.</p>
        </div>
    </div>

    {{-- Filter bar --}}
    <div class="filter-bar">
        <form action="{{ route('admin.financials.invoices') }}" method="GET" class="filter-form">
            <div class="filter-search-wrap">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}" class="filter-input" placeholder="Search by INV number or merchant name...">
            </div>
            
            <select name="client_id" class="filter-select">
                <option value="">All Clients</option>
                @foreach($clients as $c)
                    <option value="{{ $c->id }}" {{ request('client_id') == $c->id ? 'selected' : '' }}>
                        {{ $c->company_name }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="btn-primary" style="box-shadow:none; padding: 8px 16px;">Filter</button>
            <a href="{{ route('admin.financials.invoices') }}" class="btn-secondary" style="padding: 8px 16px;">Reset</a>
        </form>
    </div>

    {{-- Invoices Table --}}
    <div class="table-card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Invoice Number</th>
                        <th>Client / Merchant</th>
                        <th>Payout Date</th>
                        <th style="text-align: center;">Total Orders</th>
                        <th>Gross COD</th>
                        <th>Customer Delivery</th>
                        <th>Net Paid Payout</th>
                        <th style="width: 100px; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                        <tr>
                            <td>
                                <strong style="color: var(--red-lt);">{{ $inv->invoice_number }}</strong>
                            </td>
                            <td>
                                <div class="cell-main">{{ $inv->clientProfile->company_name }}</div>
                                <div class="cell-sub">ID: {{ $inv->clientProfile->id }}</div>
                            </td>
                            <td>
                                <div class="cell-main">{{ $inv->created_at->format('d M Y') }}</div>
                                <div class="cell-sub">{{ $inv->created_at->format('H:i') }}</div>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge badge-pv">{{ $inv->total_orders }} orders</span>
                            </td>
                            <td>{{ number_format($inv->cod_amount, 2) }} JD</td>
                            <td>
                                @if($inv->customer_delivery_amount > 0)
                                    <span style="color: #22c55e;">+{{ number_format($inv->customer_delivery_amount, 2) }} JD</span>
                                @else
                                    <span style="color: var(--text-dim);">—</span>
                                @endif
                            </td>
                            <td>
                                <strong style="color: #22c55e;">{{ number_format($inv->cod_amount + $inv->customer_delivery_amount, 2) }} JD</strong>
                            </td>
                            <td>
                                <div class="act-btns" style="justify-content: center;">
                                    <a href="{{ route('admin.financials.invoices.show', $inv) }}" class="act-btn act-view" title="View & Print Invoice">
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
                                No invoices have been created/processed yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($invoices->hasPages())
            <div class="pagination-wrap">
                <div class="pag-info">Showing {{ $invoices->firstItem() }}-{{ $invoices->lastItem() }} of {{ $invoices->total() }} invoices</div>
                <div class="pag-links">
                    {{ $invoices->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection
