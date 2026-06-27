@extends('client.layouts.app')

@section('title', __('Payout Invoices'))
@section('page-title', __('Payout Invoices'))

@section('content')
    <div class="page-hd">
        <div class="page-hd-left">
            <h1>{{ __('Payout Invoices') }}</h1>
            <p>{{ __('View your payout histories, gross COD collections, and printable receipts.') }}</p>
        </div>
    </div>

    {{-- Filter bar --}}
    <div class="filter-bar">
        <form action="{{ route('client.financials.invoices') }}" method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;width:100%;">
            <div class="filter-search-wrap">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}" class="filter-input" placeholder="{{ __('Search by invoice number…') }}">
            </div>
            
            <button type="submit" class="btn-primary" style="box-shadow:none; padding: 8px 16px;">{{ __('Filter') }}</button>
            @if(request()->filled('search'))
                <a href="{{ route('client.financials.invoices') }}" class="btn-secondary" style="padding: 8px 16px; text-decoration:none;">{{ __('Reset') }}</a>
            @endif
        </form>
    </div>

    {{-- Invoices Table --}}
    <div class="card" style="padding:0;overflow:hidden;">
        @if($invoices->count())
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Invoice Number') }}</th>
                        <th>{{ __('Payout Date') }}</th>
                        <th style="text-align: center;">{{ __('Total Orders') }}</th>
                        <th>{{ __('Gross COD') }}</th>
                        <th>{{ __('Customer Delivery') }}</th>
                        <th>{{ __('Net Paid Payout') }}</th>
                        <th style="width: 100px; text-align: center;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoices as $inv)
                        <tr style="cursor:pointer;" onclick="window.location='{{ route('client.financials.invoices.show', $inv) }}'">
                            <td>
                                <strong style="color: var(--red-lt);">{{ $inv->invoice_number }}</strong>
                            </td>
                            <td>
                                <div class="cell-main">{{ $inv->created_at->format('d M Y') }}</div>
                                <div class="cell-sub" style="font-size: 0.72rem; color: var(--text-dim);">{{ $inv->created_at->format('H:i') }}</div>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge badge-neutral" style="font-size:.72rem;">{{ $inv->total_orders }} {{ __('orders') }}</span>
                            </td>
                            <td style="font-weight:600;white-space:nowrap;">{{ number_format($inv->cod_amount, 2) }} JD</td>
                            <td style="white-space:nowrap;">
                                @if($inv->customer_delivery_amount > 0)
                                    <span style="color: #22c55e;">+{{ number_format($inv->customer_delivery_amount, 2) }} JD</span>
                                @else
                                    <span style="color: var(--text-dim);">—</span>
                                @endif
                            </td>
                            <td style="font-weight:700;white-space:nowrap;color:#22c55e;">
                                {{ number_format($inv->cod_amount + $inv->customer_delivery_amount, 2) }} JD
                            </td>
                            <td>
                                <div style="display:flex; justify-content: center;">
                                    <a href="{{ route('client.financials.invoices.show', $inv) }}" class="btn-secondary" style="padding: 5px 8px; font-size: 0.75rem; text-decoration:none; display: inline-flex; align-items: center; gap: 4px;" title="{{ __('View & Print Invoice') }}" onclick="event.stopPropagation();">
                                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        {{ __('View') }}
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
            <div style="padding:48px;text-align:center;color:var(--text-dim);font-size:.87rem;">{{ __('No payout invoices found.') }}</div>
        @endif
    </div>

    @if($invoices->hasPages())
        <div style="margin-top:16px;">{{ $invoices->links() }}</div>
    @endif
@endsection
