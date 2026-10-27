<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ $statement->statement_number }}</title>
    {{-- Header and footer follow the waybill design in shared/orders/pdf.blade.php --}}
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            color: #0c1230;
            font-family: DejaVu Sans, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 10px;
            line-height: 1.4;
        }

        @page {
            margin: 10mm 12mm 18mm 12mm;
        }

        table.layout {
            width: 100%;
            border-collapse: collapse;
        }

        /* ── Header ── */
        .doc-header td {
            vertical-align: top;
            border-bottom: 2px solid #0c1230;
            padding-bottom: 12px;
        }
        .company-details h1 {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
            color: #0c1230;
        }
        .company-details p {
            margin: 2px 0 0 0;
            color: #64748b;
            font-size: 10px;
        }
        .logo-center {
            text-align: center;
        }
        .logo-center img {
            height: 60px;
        }
        .doc-title {
            text-align: right;
        }
        .doc-title h2 {
            margin: 0;
            font-size: 14px;
            font-weight: 700;
            color: #0c1230;
        }
        .doc-title .doc-no {
            font-size: 14px;
            font-weight: 700;
            color: #e05454;
            margin-top: 4px;
        }
        .doc-title .doc-date {
            font-size: 10px;
            color: #64748b;
            margin-top: 2px;
        }
        .status {
            display: inline-block;
            margin-top: 4px;
            padding: 2px 8px;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .status-exported { background: #dbeafe; color: #1d4ed8; }
        .status-paid { background: #dcfce7; color: #15803d; }
        .status-superseded { background: #e2e8f0; color: #475569; }

        .header-spacer td {
            height: 14px;
        }

        /* ── Info cards ── */
        .parties-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 12px 0;
            margin: 0 -12px 14px -12px;
        }
        .info-card {
            border: 1px solid #e2e8f0;
            padding: 10px 12px;
            vertical-align: top;
            width: 50%;
        }
        .info-card h3 {
            margin: 0 0 8px 0;
            font-size: 11px;
            font-weight: 700;
            color: #0c1230;
            text-transform: uppercase;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
        }
        .info-row {
            margin-bottom: 4px;
        }
        .info-label {
            color: #64748b;
            display: inline-block;
            width: 95px;
        }

        /* ── Orders table ── */
        table.orders {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #e2e8f0;
        }
        table.orders th {
            background: #0c1230;
            color: #ffffff;
            font-size: 9px;
            font-weight: 700;
            text-align: left;
            text-transform: uppercase;
            padding: 6px 5px;
        }
        table.orders td {
            border-bottom: 1px solid #e2e8f0;
            padding: 5px;
            vertical-align: top;
        }
        table.orders tr.even td {
            background: #f8fafc;
        }
        table.orders tfoot td {
            font-weight: 700;
            background: #f1f5f9;
            border-top: 2px solid #0c1230;
        }
        .num,
        table.orders th.num {
            text-align: right;
            white-space: nowrap;
        }
        .muted {
            color: #64748b;
        }
        .neg {
            color: #e05454;
        }
        .order-no {
            font-weight: 700;
            white-space: nowrap;
        }
        .st-icon {
            font-weight: 700;
            padding-right: 3px;
        }
        .st-delivered { color: #16a34a; }
        .st-returned { color: #d97706; }
        .st-rejected { color: #e05454; }

        .legend {
            font-size: 9px;
            color: #64748b;
        }
        .legend span {
            padding-right: 12px;
        }

        /* ── Closing block: legend + note beside the totals ── */
        .closing {
            margin-top: 10px;
            page-break-inside: avoid;
        }
        .closing td {
            vertical-align: top;
        }
        .finance-summary {
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            padding: 12px;
            width: 100%;
        }
        .finance-row td {
            padding: 4px 0;
            font-size: 11px;
            white-space: nowrap;
        }
        .finance-row.total td {
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
            font-weight: 700;
            font-size: 13px;
        }

        /* ── Footer (repeated on every page) ── */
        .page-footer {
            position: fixed;
            bottom: -12mm;
            left: 0;
            right: 0;
            border-top: 1px solid #cbd5e1;
            padding-top: 6px;
            font-size: 9px;
            color: #64748b;
        }
        .page-footer td {
            vertical-align: top;
        }
        .page-number:after {
            content: counter(page);
        }

        .disclaimer-note {
            margin-top: 12px;
            border-top: 1px solid #cbd5e1;
            padding-top: 8px;
            font-size: 9px;
            color: #64748b;
        }
    </style>
</head>
<body>
@php
    $statusLabels = [
        'exported'   => __('Awaiting Payment'),
        'paid'       => __('Paid'),
        'superseded' => __('Superseded'),
    ];
    $statusIcons = [
        'delivered' => '✔',
        'returned'  => '↩',
        'rejected'  => '✘',
    ];
@endphp

    {{-- Footer is declared first so dompdf repeats it on every page --}}
    <table class="layout page-footer">
        <tr>
            <td>SA'EE LOGISTICS · +962 7 9080 0989 · support@saee.logistics</td>
            <td style="text-align: center;">{{ $statement->statement_number }}</td>
            <td style="text-align: right;">{{ __('Page') }} <span class="page-number"></span></td>
        </tr>
    </table>

    <table class="layout doc-header">
        <tr>
            <td class="company-details" style="width: 33%;">
                <h1>SA'EE LOGISTICS</h1>
                <p>{{ __('Reliable Delivery Solutions') }}</p>
                <p>{{ __('Phone:') }} <span>+962 7 9080 0989</span></p>
                <p>support@saee.logistics</p>
            </td>
            <td class="logo-center" style="width: 34%;">
                <img src="{{ public_path('pdf-assets/logo.png') }}" alt="Sa'ee Logistics">
            </td>
            <td class="doc-title" style="width: 33%;">
                <h2>{{ __('CLIENT STATEMENT') }}</h2>
                <div class="doc-no">{{ $statement->statement_number }}</div>
                <div class="doc-date">{{ __('Date:') }} <span>{{ $statement->created_at->format('Y-m-d H:i') }}</span></div>
                <div class="status status-{{ $statement->status }}">{{ $statusLabels[$statement->status] ?? $statement->status }}</div>
            </td>
        </tr>
    </table>

    <table class="layout header-spacer"><tr><td></td></tr></table>

    <table class="parties-grid">
        <tr>
            <td class="info-card">
                <h3>{{ __('Client') }}</h3>
                <div class="info-row">
                    <span class="info-label">{{ __('Company:') }}</span>
                    <span>{{ $statement->clientProfile?->company_name ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">{{ __('Phone:') }}</span>
                    <span>{{ $statement->clientProfile?->company_phone ?? $statement->clientProfile?->masterUser?->phone ?? 'N/A' }}</span>
                </div>
            </td>
            <td class="info-card">
                <h3>{{ __('Statement Details') }}</h3>
                <div class="info-row">
                    <span class="info-label">{{ __('Period') }}:</span>
                    <span>{{ $period }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">{{ __('Orders') }}:</span>
                    <span>{{ $statement->orders_count }}</span>
                </div>
                @if($statement->paid_at)
                    <div class="info-row">
                        <span class="info-label">{{ __('Paid at') }}:</span>
                        <span>{{ $statement->paid_at->format('Y-m-d') }}@if($statement->payment_reference) · {{ $statement->payment_reference }}@endif</span>
                    </div>
                @endif
            </td>
        </tr>
    </table>

    <table class="orders">
        <thead>
            <tr>
                <th style="width: 22px;">#</th>
                <th>{{ __('Order #') }}</th>
                <th>{{ __('Date') }}</th>
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
                <tr class="{{ $i % 2 ? 'even' : '' }}">
                    <td class="muted">{{ $i + 1 }}</td>
                    <td class="order-no"><span class="st-icon st-{{ $order->status }}">{{ $statusIcons[$order->status] ?? '' }}</span>{{ $order->order_number }}</td>
                    <td style="white-space: nowrap;">{{ $service->completedAt($order)?->format('Y-m-d') }}</td>
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
                <td colspan="6">{{ __('Total') }} ({{ $statement->orders_count }} {{ __('orders') }})</td>
                <td class="num">{{ number_format($statement->total_cod, 2) }}</td>
                <td class="num">{{ number_format($statement->total_customer_delivery, 2) }}</td>
                <td class="num neg">-{{ number_format($statement->total_client_delivery, 2) }}</td>
                <td class="num">{{ number_format($statement->net_amount, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <table class="layout closing">
        <tr>
            <td style="width: 60%; padding-right: 20px;">
                <div class="legend">
                    <span><span class="st-delivered">✔</span> {{ __('Delivered') }}</span>
                    <span><span class="st-returned">↩</span> {{ __('Returned') }}</span>
                    <span><span class="st-rejected">✘</span> {{ __('Rejected') }}</span>
                </div>
                <div class="disclaimer-note">
                    <strong>{{ __('Note:') }}</strong> {{ __('This statement lists the cash on delivery collected for the client, plus delivery fees paid by customers, minus Saee delivery fees. A negative net amount is due from the client to Saee.') }}
                </div>
            </td>
            <td style="width: 40%;">
                <table class="finance-summary">
                    <tr class="finance-row">
                        <td>{{ __('COD') }}</td>
                        <td style="text-align: right;">{{ number_format($statement->total_cod, 2) }} JOD</td>
                    </tr>
                    <tr class="finance-row">
                        <td>+ {{ __('Customer Delivery') }}</td>
                        <td style="text-align: right;">{{ number_format($statement->total_customer_delivery, 2) }} JOD</td>
                    </tr>
                    <tr class="finance-row">
                        <td>− {{ __('Saee Delivery Fees') }}</td>
                        <td style="text-align: right;" class="neg">{{ number_format($statement->total_client_delivery, 2) }} JOD</td>
                    </tr>
                    <tr class="finance-row total">
                        <td>{{ __('Net Due to Client') }}</td>
                        <td style="text-align: right;" class="{{ $statement->net_amount < 0 ? 'neg' : '' }}">{{ number_format($statement->net_amount, 2) }} JOD</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
