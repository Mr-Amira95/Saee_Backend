@extends('client.layouts.app')
@section('title', __('Invoices'))
@section('page-title', __('Invoices'))

@section('content')

<h1 style="font-size:1.35rem;font-weight:800;margin-bottom:16px;">{{ __('Invoices') }}</h1>

<div class="tabs-container" style="display:flex;gap:8px;margin-bottom:20px;border-bottom:1px solid var(--bdr);padding-bottom:12px;">
    <a href="{{ route('client.financials.index') }}" style="font-size:.87rem;font-weight:600;color:{{ request()->routeIs('client.financials.index') ? 'var(--red-lt)' : 'var(--text-sub)' }};text-decoration:none;padding:6px 12px;border-radius:6px;background:{{ request()->routeIs('client.financials.index') ? 'rgba(220,38,38,.08)' : 'transparent' }};border:1px solid {{ request()->routeIs('client.financials.index') ? 'var(--bdr-red)' : 'transparent' }};transition:all .15s;">{{ __('Transactions') }}</a>
    <a href="{{ route('client.financials.invoices') }}" style="font-size:.87rem;font-weight:600;color:{{ request()->routeIs('client.financials.invoices') ? 'var(--red-lt)' : 'var(--text-sub)' }};text-decoration:none;padding:6px 12px;border-radius:6px;background:{{ request()->routeIs('client.financials.invoices') ? 'rgba(220,38,38,.08)' : 'transparent' }};border:1px solid {{ request()->routeIs('client.financials.invoices') ? 'var(--bdr-red)' : 'transparent' }};transition:all .15s;">{{ __('Invoices') }}</a>
</div>

<div class="card" style="padding:0;overflow:hidden;">
    @if($invoices->count())
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('Invoice #') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Orders') }}</th>
                    <th style="text-align:right;">{{ __('COD Amount') }}</th>
                    <th style="text-align:right;">{{ __('Shipping') }}</th>
                    <th style="text-align:right;">{{ __('Net Amount') }}</th>
                    <th>{{ __('Status') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoices as $inv)
                <tr style="cursor:pointer;" onclick="window.location='{{ route('client.financials.invoices.show', $inv) }}'">
                    <td style="font-family:monospace;font-size:.83rem;color:var(--red-lt);">{{ $inv->invoice_number }}</td>
                    <td style="color:var(--text-dim);font-size:.82rem;white-space:nowrap;">{{ $inv->created_at->format('d M Y') }}</td>
                    <td style="font-size:.86rem;">{{ $inv->total_orders ?? '—' }}</td>
                    <td style="font-weight:600;white-space:nowrap;text-align:right;">{{ number_format($inv->cod_amount ?? 0, 2) }} JD</td>
                    <td style="font-size:.86rem;white-space:nowrap;text-align:right;">{{ number_format($inv->shipping_amount ?? 0, 2) }} JD</td>
                    <td style="font-weight:700;white-space:nowrap;text-align:right;">{{ number_format($inv->net_amount ?? 0, 2) }} JD</td>
                    <td>
                        @php
                            $sc = match($inv->status ?? 'unpaid') {
                                'paid'  => 'badge-success',
                                default => 'badge-pending',
                            };
                            $label = match($inv->status ?? 'unpaid') {
                                'paid'  => __('Paid'),
                                default => __('Unpaid'),
                            };
                        @endphp
                        <span class="badge {{ $sc }}" style="font-size:.72rem;">{{ $label }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div style="padding:48px;text-align:center;color:var(--text-dim);font-size:.87rem;">{{ __('No invoices issued yet.') }}</div>
    @endif
</div>

@if($invoices->hasPages())
<div style="margin-top:16px;">{{ $invoices->links() }}</div>
@endif

@endsection
