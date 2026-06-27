@extends('client.layouts.app')
@section('title', __('Financials'))
@section('page-title', __('Financials'))

@section('content')

<h1 style="font-size:1.35rem;font-weight:800;margin-bottom:16px;">{{ __('Financials') }}</h1>

<div class="tabs-container" style="display:flex;gap:8px;margin-bottom:20px;border-bottom:1px solid var(--bdr);padding-bottom:12px;">
    <a href="{{ route('client.financials.index') }}" style="font-size:.87rem;font-weight:600;color:{{ request()->routeIs('client.financials.index') ? 'var(--red-lt)' : 'var(--text-sub)' }};text-decoration:none;padding:6px 12px;border-radius:6px;background:{{ request()->routeIs('client.financials.index') ? 'rgba(220,38,38,.08)' : 'transparent' }};border:1px solid {{ request()->routeIs('client.financials.index') ? 'var(--bdr-red)' : 'transparent' }};transition:all .15s;">{{ __('Transactions') }}</a>
    <a href="{{ route('client.financials.invoices') }}" style="font-size:.87rem;font-weight:600;color:{{ request()->routeIs('client.financials.invoices') ? 'var(--red-lt)' : 'var(--text-sub)' }};text-decoration:none;padding:6px 12px;border-radius:6px;background:{{ request()->routeIs('client.financials.invoices') ? 'rgba(220,38,38,.08)' : 'transparent' }};border:1px solid {{ request()->routeIs('client.financials.invoices') ? 'var(--bdr-red)' : 'transparent' }};transition:all .15s;">{{ __('Invoices') }}</a>
</div>

{{-- Summary cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;margin-bottom:24px;">
    <div class="card">
        <div style="font-size:.74rem;color:var(--text-dim);text-transform:uppercase;letter-spacing:.1em;margin-bottom:10px;">{{ __('COD Collected') }}</div>
        <div style="font-size:1.8rem;font-weight:800;color:var(--text);">{{ number_format($codCollected, 2) }} <span style="font-size:.95rem;font-weight:600;color:var(--text-sub);">JD</span></div>
        <div style="font-size:.79rem;color:var(--text-dim);margin-top:6px;">{{ __('Cash on delivery received') }}</div>
    </div>
    <div class="card">
        <div style="font-size:.74rem;color:var(--text-dim);text-transform:uppercase;letter-spacing:.1em;margin-bottom:10px;">{{ __('Shipping Charges') }}</div>
        <div style="font-size:1.8rem;font-weight:800;color:#f87171;">{{ number_format($shippingCharges, 2) }} <span style="font-size:.95rem;font-weight:600;color:var(--text-sub);">JD</span></div>
        <div style="font-size:.79rem;color:var(--text-dim);margin-top:6px;">{{ __('Delivery fees deducted') }}</div>
    </div>
    <div class="card">
        <div style="font-size:.74rem;color:var(--text-dim);text-transform:uppercase;letter-spacing:.1em;margin-bottom:10px;">{{ __('Payouts Received') }}</div>
        <div style="font-size:1.8rem;font-weight:800;color:#4ade80;">{{ number_format($payoutsReceived, 2) }} <span style="font-size:.95rem;font-weight:600;color:var(--text-sub);">JD</span></div>
        <div style="font-size:.79rem;color:var(--text-dim);margin-top:6px;">{{ __('Transferred to you') }}</div>
    </div>
    <div class="card" style="background:linear-gradient(135deg,rgba(220,38,38,.12),rgba(220,38,38,.04));">
        <div style="font-size:.74rem;color:var(--text-dim);text-transform:uppercase;letter-spacing:.1em;margin-bottom:10px;">{{ __('Net Balance Due') }}</div>
        <div style="font-size:1.8rem;font-weight:800;color:{{ $netBalanceDue >= 0 ? '#4ade80' : '#f87171' }};">{{ number_format($netBalanceDue, 2) }} <span style="font-size:.95rem;font-weight:600;color:var(--text-sub);">JD</span></div>
        <div style="font-size:.79rem;color:var(--text-dim);margin-top:6px;">{{ __('Pending payout to you') }}</div>
    </div>
</div>

{{-- Filters --}}
<form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px;">
    <select name="type" class="form-control" style="width:auto;" onchange="this.form.submit()">
        <option value="">{{ __('All Types') }}</option>
        <option value="cod_collection"   {{ request('type') === 'cod_collection'   ? 'selected' : '' }}>{{ __('COD Collection') }}</option>
        <option value="delivery_collection" {{ request('type') === 'delivery_collection' ? 'selected' : '' }}>{{ __('Delivery Collection') }}</option>
        <option value="shipping_charge"  {{ request('type') === 'shipping_charge'  ? 'selected' : '' }}>{{ __('Shipping Charge') }}</option>
        <option value="client_payout"    {{ request('type') === 'client_payout'    ? 'selected' : '' }}>{{ __('Payout') }}</option>
    </select>
    <input type="date" name="from" class="form-control" style="width:auto;" value="{{ request('from') }}" placeholder="{{ __('From') }}">
    <input type="date" name="to"   class="form-control" style="width:auto;" value="{{ request('to') }}"   placeholder="{{ __('To') }}">
    <button type="submit" class="btn-secondary">{{ __('Filter') }}</button>
    @if(request()->hasAny(['type','from','to']))
        <a href="{{ route('client.financials.index') }}" class="btn-secondary">{{ __('Clear') }}</a>
    @endif
</form>

{{-- Transactions table --}}
<div class="card" style="padding:0;overflow:hidden;">
    @if($ledger->count())
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('Order') }}</th>
                    <th style="text-align:right;">{{ __('Amount') }}</th>
                    <th>{{ __('Ref') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ledger as $entry)
                <tr>
                    <td style="color:var(--text-dim);font-size:.82rem;white-space:nowrap;">{{ $entry->created_at->format('d M Y') }}</td>
                    <td>
                        <span class="badge {{ in_array($entry->type, ['cod_collection','delivery_collection']) ? 'badge-success' : ($entry->type === 'client_payout' ? 'badge-pending' : 'badge-neutral') }}" style="font-size:.72rem;">
                            {{ ucwords(str_replace('_', ' ', $entry->type)) }}
                        </span>
                    </td>
                    <td style="font-size:.83rem;color:var(--text-sub);">{{ optional($entry->order)->order_number ?? '—' }}</td>
                    <td style="font-weight:700;white-space:nowrap;text-align:right;color:{{ in_array($entry->type, ['shipping_charge']) ? '#f87171' : '#4ade80' }};">
                        {{ number_format($entry->amount, 2) }} JD
                    </td>
                    <td style="font-size:.8rem;color:var(--text-dim);">{{ $entry->reference_number ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div style="padding:48px;text-align:center;color:var(--text-dim);font-size:.87rem;">{{ __('No transactions yet.') }}</div>
    @endif
</div>

@if($ledger->hasPages())
<div style="margin-top:16px;">{{ $ledger->links() }}</div>
@endif

@endsection
