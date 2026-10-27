@extends('admin.layouts.app')

@section('title', __('Client Statements'))
@section('page-title', __('Client Statements'))

@section('breadcrumb')
    <span class="sep">/</span>
    <span class="current">{{ __('Client Statements') }}</span>
@endsection

@section('content')
@php
    $paymentBadges = [
        'ready'    => ['class' => 'badge-pending', 'label' => __('Ready')],
        'exported' => ['class' => 'badge-info',    'label' => __('Exported')],
        'paid'     => ['class' => 'badge-active',  'label' => __('Paid')],
    ];
    $statementBadges = [
        'exported'   => ['class' => 'badge-info',   'label' => __('Exported')],
        'paid'       => ['class' => 'badge-active', 'label' => __('Paid')],
        'superseded' => ['class' => 'badge-no',     'label' => __('Superseded')],
    ];
    $canManage = auth()->user()->hasAdminAction('finances.client_statements');
    $tabUrl = fn ($t) => route('admin.financials.client-statements.index', array_merge(request()->except(['tab', 'page']), ['tab' => $t]));
@endphp

    <div class="page-hd">
        <div class="page-hd-left">
            <h1>{{ __('Client Statements') }}</h1>
            <p>{{ __('One report per client: COD + delivery paid by customer − Saee delivery fee. Export it, then confirm the payment once the client is paid.') }}</p>
        </div>
    </div>

    @if(session('success'))
        <div style="margin-bottom: 15px; padding: 12px 16px; background: rgba(34, 197, 94, 0.15); border: 1px solid #22c55e; border-radius: 8px; color: #4ade80; font-size: 0.88rem;">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div style="margin-bottom: 15px; padding: 12px 16px; background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; border-radius: 8px; color: #f87171; font-size: 0.88rem;">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- Filter bar --}}
    <div class="filter-bar">
        <form action="{{ route('admin.financials.client-statements.index') }}" method="GET" class="filter-form" style="flex-wrap: wrap;">
            <input type="hidden" name="tab" value="{{ $tab }}">

            <div class="filter-search-wrap">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ $filters['search'] }}" class="filter-input"
                       placeholder="{{ $tab === 'orders' ? __('Order #, receiver, phone, statement #…') : __('Statement #…') }}">
            </div>

            <select name="client_id" class="filter-select">
                <option value="">{{ __('All Clients') }}</option>
                @foreach($clients as $c)
                    <option value="{{ $c->id }}" {{ (string) $filters['client_id'] === (string) $c->id ? 'selected' : '' }}>{{ $c->company_name }}</option>
                @endforeach
            </select>

            <input type="date" name="from" value="{{ $filters['from'] }}" class="filter-input" style="max-width: 150px; flex: none;" title="{{ __('From Date') }}">
            <input type="date" name="to" value="{{ $filters['to'] }}" class="filter-input" style="max-width: 150px; flex: none;" title="{{ __('To Date') }}">

            @if($tab === 'orders')
                <select name="payment_status" class="filter-select">
                    <option value="">{{ __('All Payment Statuses') }}</option>
                    @foreach($paymentBadges as $value => $badge)
                        <option value="{{ $value }}" {{ $filters['payment_status'] === $value ? 'selected' : '' }}>{{ $badge['label'] }}</option>
                    @endforeach
                </select>
            @else
                <select name="statement_status" class="filter-select">
                    <option value="">{{ __('All Statuses') }}</option>
                    @foreach($statementBadges as $value => $badge)
                        <option value="{{ $value }}" {{ request('statement_status') === $value ? 'selected' : '' }}>{{ $badge['label'] }}</option>
                    @endforeach
                </select>
            @endif

            <button type="submit" class="btn-primary" style="box-shadow:none; padding: 8px 16px;">{{ __('Filter') }}</button>
            <a href="{{ route('admin.financials.client-statements.index', ['tab' => $tab]) }}" class="btn-secondary" style="padding: 8px 16px;">{{ __('Reset') }}</a>
        </form>
    </div>

    {{-- Totals for the current filters --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 14px; margin-bottom: 18px;">
        @foreach([
            [__('Orders'), number_format($totals['orders']), 'var(--text)'],
            [__('COD'), number_format($totals['cod'], 2) . ' JD', 'var(--text)'],
            [__('Delivery Paid by Customer'), '+' . number_format($totals['customer_delivery'], 2) . ' JD', '#60a5fa'],
            [__('Saee Delivery Fees'), '−' . number_format($totals['client_delivery'], 2) . ' JD', 'var(--red-lt)'],
            [__('Net Due to Client'), number_format($totals['net'], 2) . ' JD', $totals['net'] < 0 ? 'var(--red-lt)' : '#22c55e'],
        ] as [$label, $value, $color])
            <div class="table-card" style="padding: 14px 16px;">
                <div style="font-size: .72rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: .06em; margin-bottom: 4px;">{{ $label }}</div>
                <div style="font-size: 1.2rem; font-weight: 700; color: {{ $color }};">{{ $value }}</div>
            </div>
        @endforeach
    </div>

    {{-- Export --}}
    @if($canManage)
    <div class="table-card" style="padding: 14px 16px; margin-bottom: 18px; display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 240px; font-size: .84rem; color: var(--text-sub);">
            @if(! $filters['client_id'])
                {{ __('Select a client to export a statement. Each statement is for one client.') }}
            @elseif($filters['payment_status'] === 'paid')
                {{ __('Paid orders cannot be exported again.') }}
            @elseif(! $exportable || $exportable['orders'] === 0)
                {{ __('There are no orders to export for the selected filters.') }}
            @else
                {!! __('Export :count orders (net :net JD). Ready and already-exported orders in these filters are included; paid orders are not.', [
                    'count' => '<strong>' . $exportable['orders'] . '</strong>',
                    'net'   => '<strong>' . number_format($exportable['net'], 2) . '</strong>',
                ]) !!}
            @endif
        </div>

        @if($filters['client_id'] && $exportable && $exportable['orders'] > 0)
            <form action="{{ route('admin.financials.client-statements.export') }}" method="POST" style="display: flex; gap: 8px;"
                  onsubmit="return confirm(@js(__('Create a new statement and mark these orders as exported? Any earlier statement that contains them will be superseded.')))">
                @csrf
                @foreach(['client_id', 'from', 'to', 'payment_status', 'search'] as $key)
                    @if($filters[$key])
                        <input type="hidden" name="{{ $key }}" value="{{ $filters[$key] }}">
                    @endif
                @endforeach
                <button type="submit" name="format" value="csv" class="btn-primary" style="box-shadow: none; padding: 8px 16px;">{{ __('Export Excel') }}</button>
                <button type="submit" name="format" value="pdf" class="btn-secondary" style="padding: 8px 16px;">{{ __('Export PDF') }}</button>
            </form>
        @endif
    </div>
    @endif

    {{-- Tabs --}}
    <div style="display: flex; border-bottom: 2px solid var(--bdr); gap: 4px; margin-bottom: 18px;">
        @foreach(['orders' => __('Orders'), 'statements' => __('Statements')] as $key => $label)
            <a href="{{ $tabUrl($key) }}"
               style="padding: 10px 18px; border-bottom: 2px solid {{ $tab === $key ? '#f59e0b' : 'transparent' }}; margin-bottom: -2px; font-size: .88rem; font-weight: 600; color: {{ $tab === $key ? '#f59e0b' : 'var(--text-dim)' }}; text-decoration: none;">
                {{ $label }}
            </a>
        @endforeach
    </div>

    @if($tab === 'orders')
        <div class="table-card">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('Order #') }}</th>
                            <th>{{ __('Client') }}</th>
                            <th>{{ __('Receiver') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Order Status') }}</th>
                            <th>{{ __('COD') }}</th>
                            <th>{{ __('Customer Delivery') }}</th>
                            <th>{{ __('Saee Fee') }}</th>
                            <th>{{ __('Net') }}</th>
                            <th>{{ __('Payment Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            @php
                                $amounts = app(\App\Services\ClientStatementService::class)->amounts($order);
                                $ps = in_array($order->payment_status, ['exported', 'paid'], true) ? $order->payment_status : 'ready';
                            @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('admin.orders.show', $order) }}" target="_blank" style="color: var(--red-lt); font-weight: 700; text-decoration: none;">#{{ $order->order_number }}</a>
                                </td>
                                <td>{{ $order->clientProfile?->company_name }}</td>
                                <td>
                                    <div class="cell-main">{{ $order->receiver?->receiver_name }}</div>
                                    <div class="cell-sub">{{ $order->receiver?->receiver_phone }}</div>
                                </td>
                                <td style="white-space: nowrap;">{{ ($order->delivered_at ?? $order->returned_at ?? $order->created_at)->format('Y-m-d') }}</td>
                                <td><span class="badge {{ $order->status === 'delivered' ? 'badge-active' : 'badge-suspended' }}">{{ __(ucfirst($order->status)) }}</span></td>
                                <td style="white-space: nowrap;">{{ number_format($amounts['cod'], 2) }}</td>
                                <td style="white-space: nowrap;">{{ number_format($amounts['customer_delivery'], 2) }}</td>
                                <td style="white-space: nowrap; color: var(--red-lt);">−{{ number_format($amounts['client_delivery'], 2) }}</td>
                                <td style="white-space: nowrap;"><strong style="color: {{ $amounts['net'] < 0 ? 'var(--red-lt)' : '#22c55e' }};">{{ number_format($amounts['net'], 2) }} JD</strong></td>
                                <td>
                                    <span class="badge {{ $paymentBadges[$ps]['class'] }}">{{ $paymentBadges[$ps]['label'] }}</span>
                                    @if($order->clientStatement && $ps !== 'ready')
                                        <div class="cell-sub"><a href="{{ route('admin.financials.client-statements.show', $order->clientStatement) }}" style="color: inherit;">{{ $order->clientStatement->statement_number }}</a></div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" style="text-align: center; color: var(--text-dim); padding: 40px;">{{ __('No orders found for the selected filters.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($orders->hasPages())
                <div class="pagination-wrap">
                    <div class="pag-info">{{ __('Showing') }} {{ $orders->firstItem() }}-{{ $orders->lastItem() }} {{ __('of') }} {{ $orders->total() }}</div>
                    <div class="pag-links">{{ $orders->links() }}</div>
                </div>
            @endif
        </div>
    @else
        <div class="table-card">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('Statement #') }}</th>
                            <th>{{ __('Client') }}</th>
                            <th>{{ __('Period') }}</th>
                            <th>{{ __('Orders') }}</th>
                            <th>{{ __('Net Amount') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Exported') }}</th>
                            <th style="width: 90px; text-align: center;">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($statementsList as $st)
                            <tr>
                                <td><strong style="color: var(--red-lt);">{{ $st->statement_number }}</strong></td>
                                <td>{{ $st->clientProfile?->company_name }}</td>
                                <td style="white-space: nowrap;">{{ app(\App\Services\ClientStatementService::class)->periodLabel($st) }}</td>
                                <td>{{ $st->orders_count }}</td>
                                <td style="white-space: nowrap;"><strong>{{ number_format($st->net_amount, 2) }} JD</strong></td>
                                <td>
                                    <span class="badge {{ $statementBadges[$st->status]['class'] }}">{{ $statementBadges[$st->status]['label'] }}</span>
                                    @if($st->paid_at)
                                        <div class="cell-sub">{{ $st->paid_at->format('Y-m-d') }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="cell-main">{{ $st->created_at->format('Y-m-d H:i') }}</div>
                                    <div class="cell-sub">{{ $st->exporter?->name }}</div>
                                </td>
                                <td>
                                    <div class="act-btns" style="justify-content: center;">
                                        <a href="{{ route('admin.financials.client-statements.show', $st) }}" class="btn-secondary" style="padding: 6px 12px; font-size: .78rem; text-decoration: none;">{{ __('View') }}</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="text-align: center; color: var(--text-dim); padding: 40px;">{{ __('No statements found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($statementsList->hasPages())
                <div class="pagination-wrap">
                    <div class="pag-info">{{ __('Showing') }} {{ $statementsList->firstItem() }}-{{ $statementsList->lastItem() }} {{ __('of') }} {{ $statementsList->total() }}</div>
                    <div class="pag-links">{{ $statementsList->links() }}</div>
                </div>
            @endif
        </div>
    @endif
@endsection
