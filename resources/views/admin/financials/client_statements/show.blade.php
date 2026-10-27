@extends('admin.layouts.app')

@section('title', $statement->statement_number)
@section('page-title', __('Client Statement'))

@section('breadcrumb')
    <span class="sep">/</span>
    <a href="{{ route('admin.financials.client-statements.index', ['tab' => 'statements']) }}">{{ __('Client Statements') }}</a>
    <span class="sep">/</span>
    <span class="current">{{ $statement->statement_number }}</span>
@endsection

@section('content')
@php
    $badges = [
        'exported'   => ['class' => 'badge-info',   'label' => __('Exported')],
        'paid'       => ['class' => 'badge-active', 'label' => __('Paid')],
        'superseded' => ['class' => 'badge-no',     'label' => __('Superseded')],
    ];
@endphp

    <div class="page-hd">
        <div class="page-hd-left">
            <h1>{{ $statement->statement_number }} <span class="badge {{ $badges[$statement->status]['class'] }}" style="vertical-align: middle;">{{ $badges[$statement->status]['label'] }}</span></h1>
            <p>
                {{ $statement->clientProfile?->company_name }} ·
                {{ app(\App\Services\ClientStatementService::class)->periodLabel($statement) }} ·
                {{ __('Exported') }} {{ $statement->created_at->format('Y-m-d H:i') }} {{ __('by') }} {{ $statement->exporter?->name ?? '—' }}
            </p>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('admin.financials.client-statements.download', [$statement, 'csv']) }}" class="btn-secondary" style="padding: 8px 16px;">{{ __('Download Excel') }}</a>
            <a href="{{ route('admin.financials.client-statements.download', [$statement, 'pdf']) }}" class="btn-secondary" style="padding: 8px 16px;">{{ __('Download PDF') }}</a>
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
    @if($statement->status === 'superseded')
        <div style="margin-bottom: 15px; padding: 12px 16px; background: rgba(100, 116, 139, 0.15); border: 1px solid #64748b; border-radius: 8px; color: var(--text-sub); font-size: 0.88rem;">
            {{ __('This statement was replaced by a newer export and can no longer be paid. It is kept for history only.') }}
        </div>
    @endif

    {{-- Totals --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 14px; margin-bottom: 18px;">
        @foreach([
            [__('Orders'), number_format($statement->orders_count), 'var(--text)'],
            [__('COD'), number_format($statement->total_cod, 2) . ' JD', 'var(--text)'],
            [__('Customer Delivery'), '+' . number_format($statement->total_customer_delivery, 2) . ' JD', '#60a5fa'],
            [__('Saee Delivery Fees'), '−' . number_format($statement->total_client_delivery, 2) . ' JD', 'var(--red-lt)'],
            [__('Net Due to Client'), number_format($statement->net_amount, 2) . ' JD', $statement->net_amount < 0 ? 'var(--red-lt)' : '#22c55e'],
        ] as [$label, $value, $color])
            <div class="table-card" style="padding: 14px 16px;">
                <div style="font-size: .72rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: .06em; margin-bottom: 4px;">{{ $label }}</div>
                <div style="font-size: 1.2rem; font-weight: 700; color: {{ $color }};">{{ $value }}</div>
            </div>
        @endforeach
    </div>

    {{-- Payment --}}
    @if($statement->status === 'paid')
        <div class="form-section" style="margin-bottom: 18px;">
            <div class="form-section-title">{{ __('Payment') }}</div>
            <div style="font-size: .88rem; color: var(--text-sub); line-height: 1.7;">
                {{ __('Paid at') }}: <strong>{{ $statement->paid_at?->format('Y-m-d H:i') }}</strong> · {{ __('by') }} {{ $statement->payer?->name ?? '—' }}<br>
                @if($statement->payment_reference){{ __('Reference') }}: <strong>{{ $statement->payment_reference }}</strong><br>@endif
                @if($statement->notes){{ __('Notes') }}: {{ $statement->notes }}@endif
            </div>
        </div>
    @elseif($statement->status === 'exported' && auth()->user()->hasAdminAction('finances.client_statements'))
        <form action="{{ route('admin.financials.client-statements.confirm-payment', $statement) }}" method="POST" class="form-section" style="margin-bottom: 18px;"
              onsubmit="return confirm(@js(__('Confirm that the client was paid? All orders in this statement will be marked as paid and cannot be exported again.')))">
            @csrf
            <div class="form-section-title">{{ __('Confirm Payment') }}</div>
            <div class="form-grid-2" style="margin-bottom: 16px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="payment_reference">{{ __('Payment Reference') }}</label>
                    <input type="text" name="payment_reference" id="payment_reference" class="form-input" maxlength="100" placeholder="{{ __('e.g. bank transfer #, CliQ reference') }}" value="{{ old('payment_reference') }}">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="notes">{{ __('Notes') }}</label>
                    <input type="text" name="notes" id="notes" class="form-input" maxlength="1000" placeholder="{{ __('Optional notes...') }}" value="{{ old('notes') }}">
                </div>
            </div>
            <button type="submit" class="btn-primary" style="background: linear-gradient(135deg, #16a34a, #22c55e); box-shadow: none; padding: 10px 22px;">
                {{ __('Confirm Payment') }} — {{ number_format($statement->net_amount, 2) }} JD
            </button>
        </form>
    @endif

    {{-- Orders --}}
    <div class="table-card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Order #') }}</th>
                        <th>{{ __('Receiver') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Order Status') }}</th>
                        <th>{{ __('Payment Type') }}</th>
                        <th>{{ __('COD') }}</th>
                        <th>{{ __('Customer Delivery') }}</th>
                        <th>{{ __('Saee Fee') }}</th>
                        <th>{{ __('Net') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                        <tr>
                            <td><a href="{{ route('admin.orders.show', $order) }}" target="_blank" style="color: var(--red-lt); font-weight: 700; text-decoration: none;">#{{ $order->order_number }}</a></td>
                            <td>
                                <div class="cell-main">{{ $order->receiver?->receiver_name }}</div>
                                <div class="cell-sub">{{ $order->receiver?->city?->name }}</div>
                            </td>
                            <td style="white-space: nowrap;">{{ ($order->delivered_at ?? $order->returned_at ?? $order->created_at)->format('Y-m-d') }}</td>
                            <td><span class="badge {{ $order->status === 'delivered' ? 'badge-active' : 'badge-suspended' }}">{{ __(ucfirst($order->status)) }}</span></td>
                            <td>{{ $order->payment?->payment_type === 'cod' ? __('COD') : __('Prepaid') }}</td>
                            <td style="white-space: nowrap;">{{ number_format($order->pivot->cod_amount, 2) }}</td>
                            <td style="white-space: nowrap;">{{ number_format($order->pivot->customer_delivery_amount, 2) }}</td>
                            <td style="white-space: nowrap; color: var(--red-lt);">−{{ number_format($order->pivot->client_delivery_amount, 2) }}</td>
                            <td style="white-space: nowrap;"><strong style="color: {{ $order->pivot->net_amount < 0 ? 'var(--red-lt)' : '#22c55e' }};">{{ number_format($order->pivot->net_amount, 2) }} JD</strong></td>
                        </tr>
                    @endforeach
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
@endsection
