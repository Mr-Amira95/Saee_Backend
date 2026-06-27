@extends('client.layouts.app')
@section('title', __('Invoice') . ' ' . $invoice->invoice_number)
@section('page-title', __('Invoice') . ' ' . $invoice->invoice_number)

@section('content')

@php
    $statusValue = $invoice->status->value ?? $invoice->status;
    $sc = match($statusValue) {
        'paid'      => 'badge-success',
        'overdue'   => 'badge-danger',
        'issued'    => 'badge-pending',
        default     => 'badge-neutral',
    };
    $label = match($statusValue) {
        'draft'     => __('Draft'),
        'issued'    => __('Issued'),
        'paid'      => __('Paid'),
        'overdue'   => __('Overdue'),
        'cancelled' => __('Cancelled'),
        default     => ucfirst($statusValue),
    };
@endphp

<div style="margin-bottom:16px;">
    <a href="{{ route('client.billing.index') }}" style="font-size:.84rem;color:var(--text-dim);text-decoration:none;">← {{ __('Back to Billing') }}</a>
</div>

{{-- Invoice header --}}
<div class="card" style="margin-bottom:20px;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;">
        <div>
            <div style="font-size:.74rem;color:var(--text-dim);text-transform:uppercase;letter-spacing:.1em;margin-bottom:4px;">{{ __('Invoice Number') }}</div>
            <div style="font-size:1.4rem;font-weight:800;font-family:monospace;color:var(--red-lt);">{{ $invoice->invoice_number }}</div>
        </div>
        <span class="badge {{ $sc }}" style="font-size:.85rem;padding:6px 14px;">{{ $label }}</span>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;margin-top:20px;">
        <div>
            <div style="font-size:.74rem;color:var(--text-dim);margin-bottom:4px;">{{ __('Period') }}</div>
            <div style="font-size:.9rem;font-weight:600;">
                {{ \Carbon\Carbon::parse($invoice->period_start)->format('d M Y') }} — {{ \Carbon\Carbon::parse($invoice->period_end)->format('d M Y') }}
            </div>
        </div>
        <div>
            <div style="font-size:.74rem;color:var(--text-dim);margin-bottom:4px;">{{ __('Total Orders') }}</div>
            <div style="font-size:.9rem;font-weight:600;">{{ $invoice->total_orders }}</div>
        </div>
        <div>
            <div style="font-size:.74rem;color:var(--text-dim);margin-bottom:4px;">{{ __('Billable Orders') }}</div>
            <div style="font-size:.9rem;font-weight:600;">{{ $invoice->billable_orders }}</div>
        </div>
        @if($invoice->due_date)
        <div>
            <div style="font-size:.74rem;color:var(--text-dim);margin-bottom:4px;">{{ __('Due Date') }}</div>
            <div style="font-size:.9rem;font-weight:600;">{{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}</div>
        </div>
        @endif
    </div>

    {{-- Amounts --}}
    <div style="border-top:1px solid var(--bdr);margin-top:20px;padding-top:16px;display:flex;flex-direction:column;gap:8px;max-width:320px;margin-left:auto;">
        <div style="display:flex;justify-content:space-between;font-size:.86rem;">
            <span style="color:var(--text-dim);">{{ __('Delivery Amount') }}</span>
            <span style="font-weight:600;">{{ number_format($invoice->delivery_amount, 2) }} JD</span>
        </div>
        @if($invoice->discount_amount > 0)
        <div style="display:flex;justify-content:space-between;font-size:.86rem;">
            <span style="color:var(--text-dim);">{{ __('Discount') }}</span>
            <span style="font-weight:600;color:#4ade80;">- {{ number_format($invoice->discount_amount, 2) }} JD</span>
        </div>
        @endif
        <div style="display:flex;justify-content:space-between;font-size:1rem;font-weight:800;border-top:1px solid var(--bdr);padding-top:8px;">
            <span>{{ __('Net Amount') }}</span>
            <span>{{ number_format($invoice->net_amount, 2) }} JD</span>
        </div>
    </div>

    {{-- Payment info (if paid) --}}
    @if($statusValue === 'paid' && $invoice->paid_at)
    <div style="border-top:1px solid var(--bdr);margin-top:16px;padding-top:14px;display:flex;gap:24px;flex-wrap:wrap;">
        <div>
            <div style="font-size:.74rem;color:var(--text-dim);margin-bottom:3px;">{{ __('Paid On') }}</div>
            <div style="font-size:.87rem;font-weight:600;">{{ \Carbon\Carbon::parse($invoice->paid_at)->format('d M Y') }}</div>
        </div>
        @if($invoice->payment_method)
        <div>
            <div style="font-size:.74rem;color:var(--text-dim);margin-bottom:3px;">{{ __('Payment Method') }}</div>
            <div style="font-size:.87rem;font-weight:600;">{{ ucwords(str_replace('_', ' ', $invoice->payment_method)) }}</div>
        </div>
        @endif
        @if($invoice->reference_number)
        <div>
            <div style="font-size:.74rem;color:var(--text-dim);margin-bottom:3px;">{{ __('Reference') }}</div>
            <div style="font-size:.87rem;font-family:monospace;">{{ $invoice->reference_number }}</div>
        </div>
        @endif
    </div>
    @endif

    @if($invoice->notes)
    <div style="border-top:1px solid var(--bdr);margin-top:14px;padding-top:12px;font-size:.84rem;color:var(--text-sub);">
        {{ $invoice->notes }}
    </div>
    @endif
</div>

{{-- Orders table --}}
@if($invoice->orders->count())
<h2 style="font-size:1rem;font-weight:700;margin-bottom:12px;">{{ __('Included Orders') }}</h2>
<div class="card" style="padding:0;overflow:hidden;">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('Order #') }}</th>
                    <th>{{ __('Receiver') }}</th>
                    <th>{{ __('City') }}</th>
                    <th style="text-align:right;">{{ __('Delivery Amount') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->orders as $order)
                <tr>
                    <td style="font-family:monospace;font-size:.83rem;color:var(--red-lt);">{{ $order->order_number }}</td>
                    <td style="font-size:.85rem;">{{ optional($order->receiver)->receiver_name ?? '—' }}</td>
                    <td style="font-size:.82rem;color:var(--text-dim);">{{ optional(optional($order->receiver)->city)->name ?? '—' }}</td>
                    <td style="font-weight:600;white-space:nowrap;text-align:right;">{{ number_format(optional($order->payment)->client_delivery_amount ?? 0, 2) }} JD</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection
