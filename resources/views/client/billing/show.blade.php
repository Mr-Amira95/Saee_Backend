@extends('client.layouts.app')

@section('title', __('Invoice') . ' ' . $invoice->invoice_number)
@section('page-title', __('Invoice Detail'))

@section('content')
    {{-- Header --}}
    <div class="page-hd">
        <div class="page-hd-left">
            <h1 style="font-family:monospace;">{{ $invoice->invoice_number }}</h1>
            <p>{{ $invoice->clientProfile->company_name ?? $invoice->clientProfile->masterUser->name }}
               &nbsp;·&nbsp; {{ \Carbon\Carbon::parse($invoice->period_start)->format('d M Y') }} – {{ \Carbon\Carbon::parse($invoice->period_end)->format('d M Y') }}</p>
        </div>
        <div style="display:flex;gap:8px;align-items:center;">
            <a href="{{ route('client.billing.index') }}" class="btn-secondary">← {{ __('Back') }}</a>
        </div>
    </div>

    @php $sv = $invoice->status->value ?? $invoice->status; @endphp

    {{-- Stats --}}
    <div class="mini-stats" style="margin-bottom:18px; display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:14px;">
        <div class="mini-stat" style="background:var(--card); border:1px solid var(--bdr); border-radius:12px; padding:14px 16px; display:flex; align-items:center; gap:12px;">
            <div class="mini-stat-icon" style="background:rgba(99,102,241,.1); color:#818cf8; width:36px; height:36px; border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
            </div>
            <div>
                <div class="ms-val" style="font-size:1.15rem; font-weight:800; color:var(--text);">{{ $invoice->billable_orders }}</div>
                <div class="ms-lbl" style="font-size:.73rem; color:var(--text-dim);">{{ __('Billable Orders') }}</div>
            </div>
        </div>
        <div class="mini-stat" style="background:var(--card); border:1px solid var(--bdr); border-radius:12px; padding:14px 16px; display:flex; align-items:center; gap:12px;">
            <div class="mini-stat-icon" style="background:rgba(245,158,11,.1); color:#f59e0b; width:36px; height:36px; border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="ms-val" style="font-size:1.15rem; font-weight:800; color:var(--text);">{{ number_format($invoice->delivery_amount, 2) }} <span style="font-size:.75rem; font-weight:600; color:var(--text-sub);">JD</span></div>
                <div class="ms-lbl" style="font-size:.73rem; color:var(--text-dim);">{{ __('Delivery Fees') }}</div>
            </div>
        </div>
        @if($invoice->discount_amount > 0)
        <div class="mini-stat" style="background:var(--card); border:1px solid var(--bdr); border-radius:12px; padding:14px 16px; display:flex; align-items:center; gap:12px;">
            <div class="mini-stat-icon" style="background:rgba(239,68,68,.08); color:#f87171; width:36px; height:36px; border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M17 17h.01M6.5 17.5l11-11M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/></svg>
            </div>
            <div>
                <div class="ms-val" style="font-size:1.15rem; font-weight:800; color:#f87171;">-{{ number_format($invoice->discount_amount, 2) }} <span style="font-size:.75rem; font-weight:600;">JD</span></div>
                <div class="ms-lbl" style="font-size:.73rem; color:var(--text-dim);">{{ __('Discount') }}</div>
            </div>
        </div>
        @endif
        <div class="mini-stat" style="background:var(--card); border:1px solid var(--bdr); border-radius:12px; padding:14px 16px; display:flex; align-items:center; gap:12px;">
            <div class="mini-stat-icon" style="background:rgba(34,197,94,.1); color:#22c55e; width:36px; height:36px; border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138z"/></svg>
            </div>
            <div>
                <div class="ms-val" style="font-size:1.15rem; font-weight:800; color:#22c55e;">{{ number_format($invoice->net_amount, 2) }} <span style="font-size:.75rem; font-weight:600;">JD</span></div>
                <div class="ms-lbl" style="font-size:.73rem; color:var(--text-dim);">{{ __('Net Due') }}</div>
            </div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:20px;margin-bottom:20px;">
        {{-- Invoice info --}}
        <div class="card" style="padding:0;overflow:hidden;height:fit-content;">
            <div style="padding:16px;border-bottom:1px solid var(--bdr);">
                <h3 style="font-size:.9rem;font-weight:700;color:var(--text-sub);text-transform:uppercase;letter-spacing:.08em;">{{ __('Invoice Details') }}</h3>
            </div>
            <div style="padding:16px;">
                <div class="info-rows" style="display:flex; flex-direction:column; gap:10px;">
                    <div class="info-row" style="display:flex; justify-content:space-between;">
                        <span class="info-row-key" style="color:var(--text-dim); font-size:.8rem;">{{ __('Status') }}</span>
                        <span class="info-row-val">
                            @if($sv === 'draft')     <span class="badge badge-neutral">{{ __('Draft') }}</span>
                            @elseif($sv === 'issued') <span class="badge badge-pending">{{ __('Issued') }}</span>
                            @elseif($sv === 'paid')   <span class="badge badge-success">{{ __('Paid') }}</span>
                            @elseif($sv === 'overdue')<span class="badge badge-danger">{{ __('Overdue') }}</span>
                            @else                     <span class="badge">{{ __('Cancelled') }}</span>
                            @endif
                        </span>
                    </div>
                    <div class="info-row" style="display:flex; justify-content:space-between;">
                        <span class="info-row-key" style="color:var(--text-dim); font-size:.8rem;">{{ __('Due Date') }}</span>
                        <span class="info-row-val" style="font-weight:600; font-size:.85rem;">{{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') : '—' }}</span>
                    </div>
                    @if($invoice->payment_method)
                    <div class="info-row" style="display:flex; justify-content:space-between;">
                        <span class="info-row-key" style="color:var(--text-dim); font-size:.8rem;">{{ __('Payment Method') }}</span>
                        <span class="info-row-val" style="font-weight:600; font-size:.85rem;">{{ ucwords(str_replace('_', ' ', $invoice->payment_method)) }}</span>
                    </div>
                    @endif
                    @if($invoice->reference_number)
                    <div class="info-row" style="display:flex; justify-content:space-between;">
                        <span class="info-row-key" style="color:var(--text-dim); font-size:.8rem;">{{ __('Reference') }}</span>
                        <span class="info-row-val" style="font-family:monospace; font-size:.85rem;">{{ $invoice->reference_number }}</span>
                    </div>
                    @endif
                    @if($invoice->paid_at)
                    <div class="info-row" style="display:flex; justify-content:space-between;">
                        <span class="info-row-key" style="color:var(--text-dim); font-size:.8rem;">{{ __('Paid At') }}</span>
                        <span class="info-row-val" style="color:#22c55e; font-weight:600; font-size:.85rem;">{{ \Carbon\Carbon::parse($invoice->paid_at)->format('d M Y H:i') }}</span>
                    </div>
                    @endif
                    @if($invoice->notes)
                    <div class="info-row" style="display:block; border-top:1px solid var(--bdr); padding-top:10px; margin-top:5px;">
                        <span class="info-row-key" style="color:var(--text-dim); font-size:.8rem; display:block; margin-bottom:5px;">{{ __('Notes') }}</span>
                        <div style="font-size:.82rem;color:var(--text-sub); line-height:1.4;">{{ $invoice->notes }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Client info --}}
        <div class="card" style="padding:0;overflow:hidden;height:fit-content;">
            <div style="padding:16px;border-bottom:1px solid var(--bdr);">
                <h3 style="font-size:.9rem;font-weight:700;color:var(--text-sub);text-transform:uppercase;letter-spacing:.08em;">{{ __('Merchant Information') }}</h3>
            </div>
            <div style="padding:16px;">
                <div class="info-rows" style="display:flex; flex-direction:column; gap:10px;">
                    @php $c = $invoice->clientProfile @endphp
                    <div class="info-row" style="display:flex; justify-content:space-between;">
                        <span class="info-row-key" style="color:var(--text-dim); font-size:.8rem;">{{ __('Business Name') }}</span>
                        <span class="info-row-val" style="font-weight:600; font-size:.85rem;">{{ $c->company_name ?? '—' }}</span>
                    </div>
                    <div class="info-row" style="display:flex; justify-content:space-between;">
                        <span class="info-row-key" style="color:var(--text-dim); font-size:.8rem;">{{ __('Contact') }}</span>
                        <span class="info-row-val" style="font-weight:600; font-size:.85rem;">{{ $c->masterUser?->name ?? '—' }}</span>
                    </div>
                    <div class="info-row" style="display:flex; justify-content:space-between;">
                        <span class="info-row-key" style="color:var(--text-dim); font-size:.8rem;">{{ __('Phone') }}</span>
                        <span class="info-row-val" style="font-family:monospace; font-size:.85rem;">{{ $c->masterUser?->phone ?? '—' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Orders table --}}
    <div class="card" style="padding:0;overflow:hidden;">
        <div style="padding:16px;border-bottom:1px solid var(--bdr);display:flex;justify-content:space-between;align-items:center;">
            <h3 style="font-size:.9rem;font-weight:700;color:var(--text-sub);text-transform:uppercase;letter-spacing:.08em;">{{ __('Included Orders') }}</h3>
            <span style="font-size:.8rem;color:var(--text-dim);">{{ $invoice->orders->count() }} {{ __('orders') }}</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Order #') }}</th>
                        <th>{{ __('Recipient') }}</th>
                        <th>{{ __('Delivered') }}</th>
                        <th style="text-align:right;">{{ __('Delivery Fee') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoice->orders as $order)
                        <tr>
                            <td>
                                <div class="cell-main" style="font-family:monospace;font-size:.82rem;color:var(--red-lt);">{{ $order->order_number ?? '#'.$order->id }}</div>
                            </td>
                            <td>
                                <div class="cell-main">{{ $order->receiver?->receiver_name ?? '—' }}</div>
                                <div class="cell-sub" style="font-size: 0.72rem; color: var(--text-dim);">{{ $order->receiver?->receiver_phone ?? '—' }}</div>
                            </td>
                            <td>{{ $order->delivered_at ? \Carbon\Carbon::parse($order->delivered_at)->format('d M Y') : '—' }}</td>
                            <td style="text-align:right;font-weight:600;white-space:nowrap;">
                                {{ number_format($order->payment->client_delivery_amount ?? 0, 2) }} JD
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align:center;color:var(--text-dim);padding:30px;">
                                {{ __('No orders attached.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($invoice->orders->count())
                <tfoot>
                    <tr style="border-top:2px solid var(--bdr);">
                        <td colspan="3" style="text-align:right;font-weight:700;padding:12px 16px;">{{ __('Total') }}</td>
                        <td style="text-align:right;font-weight:800;font-size:1rem;padding:12px 16px;color:#22c55e;white-space:nowrap;">
                            {{ number_format($invoice->delivery_amount, 2) }} JD
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
@endsection
