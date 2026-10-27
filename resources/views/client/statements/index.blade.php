@extends('client.layouts.app')

@section('title', __('Statements'))
@section('page-title', __('Statements'))

@section('content')
@php
    $paymentBadges = [
        'ready'    => ['class' => 'badge-pending', 'label' => __('Pending Statement')],
        'exported' => ['class' => 'badge-info',    'label' => __('Exported')],
        'paid'     => ['class' => 'badge-success', 'label' => __('Paid')],
    ];
    $service = app(\App\Services\ClientStatementService::class);
@endphp

    <div class="page-hd">
        <div class="page-hd-left">
            <h1>{{ __('Statements') }}</h1>
            <p>{{ __('Your COD collections, delivery fees, and payment status for every finished order.') }}</p>
        </div>
    </div>

    {{-- Totals for the current filters --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin-bottom:20px;animation:fu .5s both;">
        @foreach([
            [__('COD Collected'), number_format($totals['cod'], 2), '#22c55e'],
            [__('Customer Delivery'), '+' . number_format($totals['customer_delivery'], 2), '#60a5fa'],
            [__('Saee Delivery Fees'), '−' . number_format($totals['client_delivery'], 2), '#f87171'],
            [__('Net Amount'), number_format($totals['net'], 2), $totals['net'] < 0 ? '#f87171' : '#22c55e'],
        ] as [$label, $value, $color])
            <div class="card" style="padding:18px 20px;">
                <div style="font-size:.69rem;font-weight:700;color:var(--text-dim);letter-spacing:.09em;text-transform:uppercase;margin-bottom:8px;">{{ $label }}</div>
                <div style="font-size:1.5rem;font-weight:800;color:{{ $color }};line-height:1;">{{ $value }} <span style="font-size:.9rem;font-weight:600;">JD</span></div>
            </div>
        @endforeach
    </div>

    {{-- Statements exported by Saee --}}
    <div class="card" style="padding:0;overflow:hidden;margin-bottom:20px;">
        <div style="padding:14px 18px;border-bottom:1px solid var(--bdr);font-size:.8rem;font-weight:700;color:var(--text-sub);text-transform:uppercase;letter-spacing:.08em;">{{ __('Statements') }}</div>
        @if($statementsList->count())
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('Statement #') }}</th>
                            <th>{{ __('Period') }}</th>
                            <th style="text-align:center;">{{ __('Orders') }}</th>
                            <th>{{ __('Net Amount') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th style="text-align:center;">{{ __('Download') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($statementsList as $st)
                            <tr>
                                <td>
                                    <strong style="color:var(--red-lt);">{{ $st->statement_number }}</strong>
                                    <div style="font-size:.72rem;color:var(--text-dim);">{{ $st->created_at->format('d M Y') }}</div>
                                </td>
                                <td style="white-space:nowrap;">{{ $service->periodLabel($st) }}</td>
                                <td style="text-align:center;">{{ $st->orders_count }}</td>
                                <td style="font-weight:700;white-space:nowrap;">{{ number_format($st->net_amount, 2) }} JD</td>
                                <td>
                                    @if($st->status === 'paid')
                                        <span class="badge badge-success">{{ __('Paid') }}</span>
                                        <div style="font-size:.72rem;color:var(--text-dim);">{{ $st->paid_at?->format('d M Y') }}</div>
                                    @else
                                        <span class="badge badge-info">{{ __('Awaiting Payment') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="display:flex;gap:6px;justify-content:center;">
                                        <a href="{{ route('client.statements.download', [$st, 'csv']) }}" class="btn-secondary" style="padding:5px 10px;font-size:.75rem;text-decoration:none;">{{ __('Excel') }}</a>
                                        <a href="{{ route('client.statements.download', [$st, 'pdf']) }}" class="btn-secondary" style="padding:5px 10px;font-size:.75rem;text-decoration:none;">{{ __('PDF') }}</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($statementsList->hasPages())
                <div style="padding:12px 16px;">{{ $statementsList->links() }}</div>
            @endif
        @else
            <div style="padding:32px;text-align:center;color:var(--text-dim);font-size:.87rem;">{{ __('No statements yet.') }}</div>
        @endif
    </div>

    {{-- Filter bar --}}
    <div class="filter-bar" style="margin-bottom: 20px;">
        <form action="{{ route('client.statements.index') }}" method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;width:100%;">
            <div class="filter-search-wrap">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ $filters['search'] }}" class="filter-input" placeholder="{{ __('Order #, receiver, phone, statement #…') }}">
            </div>

            <input type="date" name="from" value="{{ $filters['from'] }}" class="filter-input" style="max-width:145px;" title="{{ __('From Date') }}">
            <input type="date" name="to" value="{{ $filters['to'] }}" class="filter-input" style="max-width:145px;" title="{{ __('To Date') }}">

            <select name="payment_status" class="filter-input" style="max-width:190px;">
                <option value="">{{ __('All Payment Statuses') }}</option>
                @foreach($paymentBadges as $value => $badge)
                    <option value="{{ $value }}" {{ $filters['payment_status'] === $value ? 'selected' : '' }}>{{ $badge['label'] }}</option>
                @endforeach
            </select>

            <button type="submit" class="btn-primary" style="box-shadow:none; padding: 8px 16px;">{{ __('Filter') }}</button>
            @if(request()->anyFilled(['search', 'from', 'to', 'payment_status']))
                <a href="{{ route('client.statements.index') }}" class="btn-secondary" style="padding: 8px 16px; text-decoration:none;">{{ __('Reset') }}</a>
            @endif
        </form>
    </div>

    {{-- Orders --}}
    <div class="card" style="padding:0;overflow:hidden;">
        @if($orders->count())
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('Order #') }}</th>
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
                        @foreach($orders as $order)
                            @php
                                $amounts = $service->amounts($order);
                                $ps = in_array($order->payment_status, ['exported', 'paid'], true) ? $order->payment_status : 'ready';
                            @endphp
                            <tr style="cursor:pointer;" onclick="window.location='{{ route('client.orders.show', $order) }}'">
                                <td><strong style="color:var(--red-lt);">#{{ $order->order_number }}</strong></td>
                                <td>
                                    <div>{{ $order->receiver?->receiver_name }}</div>
                                    <div style="font-size:.72rem;color:var(--text-dim);">{{ $order->receiver?->city?->name }}</div>
                                </td>
                                <td style="white-space:nowrap;">{{ $service->completedAt($order)?->format('d M Y') }}</td>
                                <td><span class="badge {{ $order->status === 'delivered' ? 'badge-success' : 'badge-danger' }}">{{ __(ucfirst($order->status)) }}</span></td>
                                <td style="white-space:nowrap;">{{ number_format($amounts['cod'], 2) }}</td>
                                <td style="white-space:nowrap;">{{ number_format($amounts['customer_delivery'], 2) }}</td>
                                <td style="white-space:nowrap;color:#f87171;">−{{ number_format($amounts['client_delivery'], 2) }}</td>
                                <td style="white-space:nowrap;font-weight:700;color:{{ $amounts['net'] < 0 ? '#f87171' : '#22c55e' }};">{{ number_format($amounts['net'], 2) }} JD</td>
                                <td>
                                    <span class="badge {{ $paymentBadges[$ps]['class'] }}">{{ $paymentBadges[$ps]['label'] }}</span>
                                    @if($ps !== 'ready' && $order->clientStatement)
                                        <div style="font-size:.72rem;color:var(--text-dim);">{{ $order->clientStatement->statement_number }}</div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div style="padding:48px;text-align:center;color:var(--text-dim);font-size:.87rem;">{{ __('No orders found for the selected filters.') }}</div>
        @endif
    </div>

    @if($orders->hasPages())
        <div style="margin-top:16px;">{{ $orders->links() }}</div>
    @endif
@endsection
