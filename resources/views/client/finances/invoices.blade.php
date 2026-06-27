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

    {{-- Statistics --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;margin-bottom:20px;animation:fu .5s both;">
        {{-- COD Collected --}}
        <div class="card" style="padding:18px 20px;">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
                <div style="width:32px;height:32px;border-radius:9px;background:rgba(34,197,94,.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="15" height="15" fill="none" stroke="#22c55e" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span style="font-size:.69rem;font-weight:700;color:var(--text-dim);letter-spacing:.09em;text-transform:uppercase;">{{ __('COD Collected') }}</span>
            </div>
            <div style="font-size:1.8rem;font-weight:800;color:#22c55e;line-height:1;margin-bottom:5px;">{{ number_format($codCollected, 2) }} <span style="font-size:1rem;font-weight:600;">JD</span></div>
            <div style="font-size:.72rem;color:var(--text-dim);">{{ __('Total cash on delivery collections') }}</div>
        </div>

        {{-- Customer Delivery --}}
        <div class="card" style="padding:18px 20px;">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
                <div style="width:32px;height:32px;border-radius:9px;background:rgba(96,165,250,.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="15" height="15" fill="none" stroke="#60a5fa" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <span style="font-size:.69rem;font-weight:700;color:var(--text-dim);letter-spacing:.09em;text-transform:uppercase;">{{ __('Customer Delivery') }}</span>
            </div>
            <div style="font-size:1.8rem;font-weight:800;color:#60a5fa;line-height:1;margin-bottom:5px;">{{ number_format($customerDelivery, 2) }} <span style="font-size:1rem;font-weight:600;">JD</span></div>
            <div style="font-size:.72rem;color:var(--text-dim);">{{ __('Total delivery paid by customer') }}</div>
        </div>

        {{-- Shipping Charges --}}
        <div class="card" style="padding:18px 20px;">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
                <div style="width:32px;height:32px;border-radius:9px;background:rgba(248,113,113,.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="15" height="15" fill="none" stroke="#f87171" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                </div>
                <span style="font-size:.69rem;font-weight:700;color:var(--text-dim);letter-spacing:.09em;text-transform:uppercase;">{{ __('Shipping Charges') }}</span>
            </div>
            <div style="font-size:1.8rem;font-weight:800;color:#f87171;line-height:1;margin-bottom:5px;">{{ number_format($shippingCharges, 2) }} <span style="font-size:1rem;font-weight:600;">JD</span></div>
            <div style="font-size:.72rem;color:var(--text-dim);">{{ __('Total shipping fees deducted') }}</div>
        </div>
    </div>

    {{-- Filter bar --}}
    <div class="filter-bar" style="margin-bottom: 20px;">
        <form action="{{ route('client.financials.invoices') }}" method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;width:100%;">
            <div class="filter-search-wrap">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}" class="filter-input" placeholder="{{ __('Search by invoice number…') }}">
            </div>
            
            <input type="date" name="from" value="{{ request('from') }}" class="filter-input" style="max-width:145px;" title="{{ __('From Date') }}">
            <input type="date" name="to" value="{{ request('to') }}" class="filter-input" style="max-width:145px;" title="{{ __('To Date') }}">

            <button type="submit" class="btn-primary" style="box-shadow:none; padding: 8px 16px;">{{ __('Filter') }}</button>
            @if(request()->anyFilled(['search', 'from', 'to']))
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
