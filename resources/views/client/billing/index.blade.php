@extends('client.layouts.app')
@section('title', __('Billing'))
@section('page-title', __('Billing'))

@section('content')

<h1 style="font-size:1.35rem;font-weight:800;margin-bottom:16px;">{{ __('Billing') }}</h1>


<div class="card" style="padding:0;overflow:hidden;">
    @if($invoices->count())
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('Invoice #') }}</th>
                    <th>{{ __('Period') }}</th>
                    <th style="text-align:center;">{{ __('Orders') }}</th>
                    <th style="text-align:right;">{{ __('Delivery Amount') }}</th>
                    <th style="text-align:right;">{{ __('Discount') }}</th>
                    <th style="text-align:right;">{{ __('Net Amount') }}</th>
                    <th>{{ __('Due Date') }}</th>
                    <th>{{ __('Status') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoices as $inv)
                @php
                    $sc = match($inv->status->value ?? $inv->status) {
                        'paid'      => 'badge-success',
                        'overdue'   => 'badge-danger',
                        'issued'    => 'badge-pending',
                        default     => 'badge-neutral',
                    };
                    $label = match($inv->status->value ?? $inv->status) {
                        'draft'     => __('Draft'),
                        'issued'    => __('Issued'),
                        'paid'      => __('Paid'),
                        'overdue'   => __('Overdue'),
                        'cancelled' => __('Cancelled'),
                        default     => ucfirst($inv->status->value ?? $inv->status),
                    };
                @endphp
                <tr style="cursor:pointer;" onclick="window.location='{{ route('client.billing.show', $inv) }}'">
                    <td style="font-family:monospace;font-size:.83rem;color:var(--red-lt);">{{ $inv->invoice_number }}</td>
                    <td style="font-size:.83rem;white-space:nowrap;color:var(--text-sub);">
                        {{ \Carbon\Carbon::parse($inv->period_start)->format('d M Y') }} — {{ \Carbon\Carbon::parse($inv->period_end)->format('d M Y') }}
                    </td>
                    <td style="text-align:center;font-size:.86rem;">{{ $inv->billable_orders }}</td>
                    <td style="font-weight:600;white-space:nowrap;text-align:right;">{{ number_format($inv->delivery_amount, 2) }} JD</td>
                    <td style="font-size:.86rem;white-space:nowrap;text-align:right;color:#4ade80;">
                        {{ $inv->discount_amount > 0 ? '- '.number_format($inv->discount_amount, 2).' JD' : '—' }}
                    </td>
                    <td style="font-weight:700;white-space:nowrap;text-align:right;">{{ number_format($inv->net_amount, 2) }} JD</td>
                    <td style="font-size:.82rem;color:var(--text-dim);white-space:nowrap;">
                        {{ $inv->due_date ? \Carbon\Carbon::parse($inv->due_date)->format('d M Y') : '—' }}
                    </td>
                    <td><span class="badge {{ $sc }}" style="font-size:.72rem;">{{ $label }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div style="padding:48px;text-align:center;color:var(--text-dim);font-size:.87rem;">{{ __('No billing invoices found.') }}</div>
    @endif
</div>

@if($invoices->hasPages())
<div style="margin-top:16px;">{{ $invoices->links() }}</div>
@endif

@endsection
