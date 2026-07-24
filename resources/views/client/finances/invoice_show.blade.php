@extends('client.layouts.app')

@section('title', __('Invoice') . ' ' . $invoice->invoice_number)
@section('page-title', __('Invoice Detail'))

@push('styles')
<style>
    .page-hd.invoice-hd { margin-bottom: 40px; }
</style>
@endpush

@section('content')
    {{-- Header --}}
    <div class="page-hd invoice-hd">
        <div class="page-hd-left">
            <h1 style="font-family:monospace;">{{ $invoice->invoice_number }}</h1>
            <p>{{ $invoice->clientProfile->company_name }} &nbsp;&middot;&nbsp; {{ $invoice->created_at->format('d M Y') }}</p>
        </div>
        <div style="display:flex;gap:8px;align-items:center;">
            <a href="{{ route('client.financials.invoices.print', $invoice) }}" target="_blank" class="btn-secondary">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                {{ __('Print Invoice') }}
            </a>
            <a href="{{ route('client.financials.invoices') }}" class="btn-secondary">&larr; {{ __('Back') }}</a>
        </div>
    </div>

    @php
        $totalCod = $orders->sum(fn($o) => (float) ($o->payment?->order_amount ?? 0));
        $totalCustDel = $orders->sum(fn($o) => $o->payment?->delivery_on_customer ? (float) ($o->payment?->customer_delivery_amount ?? 0) : 0);
        $totalNet = $totalCod + $totalCustDel;
    @endphp

    {{-- Stats --}}
    <div class="mini-stats" style="margin-bottom:18px; display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:14px;">
        <div class="mini-stat" style="background:var(--card); border:1px solid var(--bdr); border-radius:12px; padding:14px 16px; display:flex; align-items:center; gap:12px;">
            <div class="mini-stat-icon" style="background:rgba(148,163,184,.12); color:#94a3b8; width:36px; height:36px; border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0l-1.5 7.5a1 1 0 01-1 .5H6.5a1 1 0 01-1-.5L4 13m16 0H4"/></svg>
            </div>
            <div>
                <div style="font-size:1.15rem; font-weight:800; color:var(--text);">{{ $orders->count() }}</div>
                <div style="font-size:.73rem; color:var(--text-dim);">{{ __('Total Orders') }}</div>
            </div>
        </div>
        <div class="mini-stat" style="background:var(--card); border:1px solid var(--bdr); border-radius:12px; padding:14px 16px; display:flex; align-items:center; gap:12px;">
            <div class="mini-stat-icon" style="background:rgba(34,197,94,.1); color:#22c55e; width:36px; height:36px; border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div style="font-size:1.15rem; font-weight:800; color:var(--text);">{{ number_format($totalCod, 2) }} <span style="font-size:.75rem; font-weight:600; color:var(--text-sub);">JD</span></div>
                <div style="font-size:.73rem; color:var(--text-dim);">{{ __('COD Collected') }}</div>
            </div>
        </div>
        <div class="mini-stat" style="background:var(--card); border:1px solid var(--bdr); border-radius:12px; padding:14px 16px; display:flex; align-items:center; gap:12px;">
            <div class="mini-stat-icon" style="background:rgba(96,165,250,.1); color:#60a5fa; width:36px; height:36px; border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <div style="font-size:1.15rem; font-weight:800; color:var(--text);">{{ number_format($totalCustDel, 2) }} <span style="font-size:.75rem; font-weight:600; color:var(--text-sub);">JD</span></div>
                <div style="font-size:.73rem; color:var(--text-dim);">{{ __('Customer Delivery') }}</div>
            </div>
        </div>
        <div class="mini-stat" style="background:var(--card); border:1px solid var(--bdr); border-radius:12px; padding:14px 16px; display:flex; align-items:center; gap:12px;">
            <div class="mini-stat-icon" style="background:rgba(34,197,94,.1); color:#22c55e; width:36px; height:36px; border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138z"/></svg>
            </div>
            <div>
                <div style="font-size:1.15rem; font-weight:800; color:#22c55e;">{{ number_format($totalNet, 2) }} <span style="font-size:.75rem; font-weight:600;">JD</span></div>
                <div style="font-size:.73rem; color:var(--text-dim);">{{ __('Net Payout Paid') }}</div>
            </div>
        </div>
    </div>

    {{-- Info cards --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:20px;margin-bottom:20px;">
        {{-- Invoice / Payment info --}}
        <div class="card" style="padding:0;overflow:hidden;height:fit-content;">
            <div style="padding:16px;border-bottom:1px solid var(--bdr);">
                <h3 style="font-size:.9rem;font-weight:700;color:var(--text-sub);text-transform:uppercase;letter-spacing:.08em;">{{ __('Payment Information') }}</h3>
            </div>
            <div style="padding:16px;">
                <div class="info-rows" style="display:flex; flex-direction:column; gap:10px;">
                    <div class="info-row" style="display:flex; justify-content:space-between;">
                        <span class="info-row-key" style="color:var(--text-dim); font-size:.8rem;">{{ __('Status') }}</span>
                        <span class="info-row-val">
                            @if($invoice->status === 'paid')
                                <span class="badge badge-success">{{ __('Paid') }}</span>
                            @else
                                <span class="badge badge-pending">{{ ucfirst($invoice->status) }}</span>
                            @endif
                        </span>
                    </div>
                    <div class="info-row" style="display:flex; justify-content:space-between;">
                        <span class="info-row-key" style="color:var(--text-dim); font-size:.8rem;">{{ __('Date') }}</span>
                        <span class="info-row-val" style="font-weight:600; font-size:.85rem;">{{ $invoice->created_at->format('d M Y') }}</span>
                    </div>
                    <div class="info-row" style="display:flex; justify-content:space-between;">
                        <span class="info-row-key" style="color:var(--text-dim); font-size:.8rem;">{{ __('Payment Method') }}</span>
                        <span class="info-row-val" style="font-weight:600; font-size:.85rem;">{{ __('Direct Transfer / Payout Cash') }}</span>
                    </div>
                    <div class="info-row" style="display:flex; justify-content:space-between;">
                        <span class="info-row-key" style="color:var(--text-dim); font-size:.8rem;">{{ __('Reference Number') }}</span>
                        <span class="info-row-val" style="font-family:monospace; font-size:.85rem;">{{ $invoice->payoutLedgerEntry->reference_number ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row" style="display:flex; justify-content:space-between;">
                        <span class="info-row-key" style="color:var(--text-dim); font-size:.8rem;">{{ __('Ledger Ref ID') }}</span>
                        <span class="info-row-val" style="font-family:monospace; font-size:.85rem;">#{{ $invoice->payout_ledger_entry_id }}</span>
                    </div>
                    <div class="info-row" style="display:flex; justify-content:space-between;">
                        <span class="info-row-key" style="color:var(--text-dim); font-size:.8rem;">{{ __('Recorded By') }}</span>
                        <span class="info-row-val" style="font-weight:600; font-size:.85rem;">{{ $invoice->payoutLedgerEntry->recordedBy->name ?? __('System') }}</span>
                    </div>
                    @if($invoice->attachment_path)
                    <div class="info-row" style="display:block; border-top:1px solid var(--bdr); padding-top:10px; margin-top:5px;">
                        <a href="{{ asset('storage/' . $invoice->attachment_path) }}" target="_blank" class="btn-secondary" style="text-decoration:none; display:inline-flex; align-items:center; gap:6px; font-size:.8rem;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M5 21h14a2 2 0 002-2V8.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0014.586 2H5a2 2 0 00-2 2v15a2 2 0 002 2z"/></svg>
                            {{ __('View Attachment') }}
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Merchant info --}}
        <div class="card" style="padding:0;overflow:hidden;height:fit-content;">
            <div style="padding:16px;border-bottom:1px solid var(--bdr);">
                <h3 style="font-size:.9rem;font-weight:700;color:var(--text-sub);text-transform:uppercase;letter-spacing:.08em;">{{ __('Merchant Information') }}</h3>
            </div>
            <div style="padding:16px;">
                @php $c = $invoice->clientProfile; @endphp
                <div class="info-rows" style="display:flex; flex-direction:column; gap:10px;">
                    <div class="info-row" style="display:flex; justify-content:space-between;">
                        <span class="info-row-key" style="color:var(--text-dim); font-size:.8rem;">{{ __('Company Name') }}</span>
                        <span class="info-row-val" style="font-weight:600; font-size:.85rem;">{{ $c->company_name }}</span>
                    </div>
                    <div class="info-row" style="display:flex; justify-content:space-between;">
                        <span class="info-row-key" style="color:var(--text-dim); font-size:.8rem;">{{ __('Merchant ID') }}</span>
                        <span class="info-row-val" style="font-family:monospace; font-size:.85rem;">#{{ $c->id }}</span>
                    </div>
                    <div class="info-row" style="display:flex; justify-content:space-between;">
                        <span class="info-row-key" style="color:var(--text-dim); font-size:.8rem;">{{ __('Email') }}</span>
                        <span class="info-row-val" style="font-size:.85rem;">{{ $c->email ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row" style="display:block; border-top:1px solid var(--bdr); padding-top:10px; margin-top:5px;">
                        <span class="info-row-key" style="color:var(--text-dim); font-size:.8rem; display:block; margin-bottom:5px;">{{ __('Address') }}</span>
                        <div style="font-size:.85rem; color:var(--text-sub); line-height:1.4;">{{ $c->address_line1 ?? 'N/A' }}, {{ $c->city->name ?? '' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Orders table --}}
    <div class="card" style="padding:0;overflow:hidden;">
        <div style="padding:16px;border-bottom:1px solid var(--bdr);display:flex;justify-content:space-between;align-items:center;">
            <h3 style="font-size:.9rem;font-weight:700;color:var(--text-sub);text-transform:uppercase;letter-spacing:.08em;">{{ __('Order Breakdown') }}</h3>
            <span style="font-size:.8rem;color:var(--text-dim);">{{ $orders->count() }} {{ __('orders') }}</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Order Number') }}</th>
                        <th>{{ __('Recipient') }}</th>
                        <th style="text-align: right;">{{ __('COD Amount') }}</th>
                        <th style="text-align: right;">{{ __('Customer Delivery') }}</th>
                        <th style="text-align: right;">{{ __('Net Payout') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $o)
                        @php
                            $codAmt  = (float) ($o->payment?->order_amount ?? 0);
                            $custDel = $o->payment?->delivery_on_customer ? (float) ($o->payment?->customer_delivery_amount ?? 0) : 0;
                            $net     = $codAmt + $custDel;
                        @endphp
                        <tr>
                            <td><strong style="color: var(--red-lt);">#{{ $o->order_number }}</strong></td>
                            <td>
                                <div class="cell-main">{{ $o->receiver?->receiver_name ?? '—' }}</div>
                                <div class="cell-sub" style="font-size: 0.72rem; color: var(--text-dim);">{{ $o->receiver?->receiver_phone ?? '—' }}</div>
                            </td>
                            <td style="text-align: right; white-space:nowrap;">{{ number_format($codAmt, 2) }} JD</td>
                            <td style="text-align: right; white-space:nowrap;">{{ $custDel > 0 ? number_format($custDel, 2).' JD' : '—' }}</td>
                            <td style="text-align: right; font-weight: 700; white-space:nowrap; color: #22c55e;">
                                {{ number_format($net, 2) }} JD
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-dim); padding: 30px;">
                                {{ __('No orders found linked to this invoice reference transaction.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($orders->count())
                <tfoot>
                    <tr style="border-top:2px solid var(--bdr);">
                        <td colspan="2" style="text-align:right;font-weight:700;padding:12px 16px;">{{ __('Total') }}</td>
                        <td style="text-align:right;font-weight:700;padding:12px 16px;white-space:nowrap;">{{ number_format($totalCod, 2) }} JD</td>
                        <td style="text-align:right;font-weight:700;padding:12px 16px;white-space:nowrap;">{{ $totalCustDel > 0 ? '+'.number_format($totalCustDel, 2).' JD' : '—' }}</td>
                        <td style="text-align:right;font-weight:800;font-size:1rem;padding:12px 16px;color:#22c55e;white-space:nowrap;">{{ number_format($totalNet, 2) }} JD</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
@endsection
