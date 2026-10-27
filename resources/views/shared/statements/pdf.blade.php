<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ $statement->statement_number }}</title>
    <style>
        @page { margin: 22px 24px; }
        body { font-family: DejaVu Sans, sans-serif; color: #111827; font-size: 10px; }
        h1 { font-size: 17px; margin: 0 0 4px; }
        .muted { color: #6b7280; }
        .meta { width: 100%; margin-bottom: 12px; }
        .meta td { padding: 2px 0; vertical-align: top; }
        .status { display: inline-block; padding: 2px 8px; border-radius: 8px; font-size: 9px; font-weight: bold; text-transform: uppercase; }
        .status-exported { background: #dbeafe; color: #1d4ed8; }
        .status-paid { background: #dcfce7; color: #15803d; }
        .status-superseded { background: #e5e7eb; color: #4b5563; }
        table.orders { width: 100%; border-collapse: collapse; }
        table.orders th { background: #111827; color: #fff; font-size: 9px; text-align: left; padding: 5px 4px; }
        table.orders td { border-bottom: 1px solid #e5e7eb; padding: 4px; }
        table.orders tr:nth-child(even) td { background: #f9fafb; }
        .num { text-align: right; white-space: nowrap; }
        .neg { color: #b91c1c; }
        table.orders tfoot td { font-weight: bold; border-top: 2px solid #111827; background: #f3f4f6; }
        .summary { width: 45%; margin-left: auto; margin-top: 14px; border-collapse: collapse; }
        .summary td { padding: 4px 6px; }
        .summary tr.net td { font-size: 12px; font-weight: bold; border-top: 2px solid #111827; }
    </style>
</head>
<body>
    <table class="meta">
        <tr>
            <td>
                <h1>{{ __('Client Statement') }}</h1>
                <div><strong>{{ $statement->clientProfile?->company_name }}</strong></div>
                <div class="muted">{{ __('Period') }}: {{ $period }}</div>
            </td>
            <td style="text-align: right;">
                <div style="font-size: 13px; font-weight: bold;">{{ $statement->statement_number }}</div>
                <div class="muted">{{ __('Exported') }}: {{ $statement->created_at->format('Y-m-d H:i') }}</div>
                <div style="margin-top: 4px;"><span class="status status-{{ $statement->status }}">{{ __(ucfirst($statement->status)) }}</span></div>
                @if($statement->paid_at)
                    <div class="muted" style="margin-top: 2px;">{{ __('Paid at') }}: {{ $statement->paid_at->format('Y-m-d') }}@if($statement->payment_reference) · {{ $statement->payment_reference }}@endif</div>
                @endif
            </td>
        </tr>
    </table>

    <table class="orders">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('Order #') }}</th>
                <th>{{ __('Date') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Receiver') }}</th>
                <th>{{ __('City') }}</th>
                <th>{{ __('Type') }}</th>
                <th class="num">{{ __('COD') }}</th>
                <th class="num">{{ __('Customer Delivery') }}</th>
                <th class="num">{{ __('Saee Fee') }}</th>
                <th class="num">{{ __('Net') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $i => $order)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $order->order_number }}</td>
                    <td>{{ $service->completedAt($order)?->format('Y-m-d') }}</td>
                    <td>{{ __(ucfirst($order->status)) }}</td>
                    <td>{{ $order->receiver?->receiver_name }}<br><span class="muted">{{ $order->receiver?->receiver_phone }}</span></td>
                    <td>{{ $order->receiver?->city?->name }}</td>
                    <td>{{ strtoupper($order->payment?->payment_type ?? '') }}</td>
                    <td class="num">{{ number_format($order->pivot->cod_amount, 2) }}</td>
                    <td class="num">{{ number_format($order->pivot->customer_delivery_amount, 2) }}</td>
                    <td class="num neg">-{{ number_format($order->pivot->client_delivery_amount, 2) }}</td>
                    <td class="num {{ $order->pivot->net_amount < 0 ? 'neg' : '' }}">{{ number_format($order->pivot->net_amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7">{{ __('Total') }} ({{ $statement->orders_count }} {{ __('orders') }})</td>
                <td class="num">{{ number_format($statement->total_cod, 2) }}</td>
                <td class="num">{{ number_format($statement->total_customer_delivery, 2) }}</td>
                <td class="num neg">-{{ number_format($statement->total_client_delivery, 2) }}</td>
                <td class="num">{{ number_format($statement->net_amount, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <table class="summary">
        <tr><td>{{ __('COD') }}</td><td class="num">{{ number_format($statement->total_cod, 2) }} JD</td></tr>
        <tr><td>+ {{ __('Delivery Paid by Customer') }}</td><td class="num">{{ number_format($statement->total_customer_delivery, 2) }} JD</td></tr>
        <tr><td>− {{ __('Saee Delivery Fees') }}</td><td class="num neg">{{ number_format($statement->total_client_delivery, 2) }} JD</td></tr>
        <tr class="net"><td>{{ __('Net Due to Client') }}</td><td class="num {{ $statement->net_amount < 0 ? 'neg' : '' }}">{{ number_format($statement->net_amount, 2) }} JD</td></tr>
    </table>
</body>
</html>
