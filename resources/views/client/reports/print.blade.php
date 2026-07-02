<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Orders Report') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            background: #ffffff;
            color: #0c1230;
            font-family: 'Outfit', sans-serif;
            margin: 0;
            padding: 24px 30px;
            font-size: 13px;
            line-height: 1.4;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .no-print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #e05454;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(224, 84, 84, 0.3);
            z-index: 1000;
        }
        .no-print-btn:hover { background: #c94040; }

        @media screen {
            .waybill-header { margin-top: 46px; }
        }

        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 7px 8px; border-bottom: 1px solid #e2e8f0; text-align: left; font-size: 11.5px; }
        th { background: #f8fafc; font-weight: 700; text-transform: uppercase; font-size: 10px; color: #475569; letter-spacing: .04em; }
        tr:last-child td { border-bottom: none; }

        .summary-row { display: flex; gap: 20px; justify-content: flex-end; margin-top: 4px; }
        .summary-box { border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 16px; text-align: right; }
        .summary-box .label { font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 700; }
        .summary-box .value { font-size: 15px; font-weight: 800; color: #0c1230; margin-top: 2px; }

        @media print {
            .no-print-btn { display: none !important; }
            body { padding: 0 10mm; }
        }

        html[dir="rtl"] th, html[dir="rtl"] td { text-align: right; }
        html[dir="rtl"] .waybill-title { text-align: left; }
        html[dir="rtl"] .company-details { text-align: right; }
    </style>
</head>
<body>

    <button onclick="window.print()" class="no-print-btn">{{ __('Print / Save as PDF') }}</button>

    <div class="waybill-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #0c1230; padding-bottom: 15px; margin-bottom: 20px;">
        {{-- Sa'ee Details (Left side) --}}
        <div class="company-details" style="flex: 1; text-align: left;">
            <h1 style="margin: 0; font-size: 18px; font-weight: 800; color: #0c1230; letter-spacing: 0.5px; line-height: 1.1;">SA'EE LOGISTICS</h1>
            <p style="margin: 2px 0 0 0; color: #64748b; font-size: 11px; font-weight: 500;">Reliable Delivery Solutions</p>
            <p style="margin: 2px 0 0 0; color: #64748b; font-size: 11px; font-weight: 500;">Phone: +962 7 9080 0989</p>
            <p style="margin: 2px 0 0 0; color: #64748b; font-size: 11px; font-weight: 500;">support@saee.logistics</p>
        </div>

        {{-- Logo (Center) --}}
        <div class="logo-center" style="flex: 1; text-align: center;">
            <img src="{{ asset('saee_logo_light.png') }}" alt="Sa'ee Logistics" style="height: 75px; width: auto; object-fit: contain; display: inline-block;">
        </div>

        {{-- Report meta (Right side) --}}
        <div class="waybill-title" style="flex: 1; text-align: right;">
            <h2 style="margin: 0; font-size: 16px; font-weight: 700; color: #0c1230;">{{ __('ORDERS REPORT') }}</h2>
            <div class="status-label" style="font-size: 15px; font-weight: 800; color: #e05454; margin-top: 5px;">{{ $statusLabel }}</div>
            <div class="range" style="font-size: 11px; color: #64748b; margin-top: 2px;">{{ __('Duration:') }} {{ \Carbon\Carbon::parse($from)->format('d M Y') }} – {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</div>
            <div class="range" style="font-size: 11px; color: #64748b; margin-top: 2px;">{{ __('Number of orders:') }} {{ number_format($orders->count()) }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>{{ __('Order #') }}</th>
                <th>{{ __('Date') }}</th>
                <th>{{ __('Receiver') }}</th>
                <th>{{ __('City / Area') }}</th>
                <th>{{ __('Payment') }}</th>
                <th>{{ __('COD Amount') }}</th>
                <th>{{ __('Del. Fee') }}</th>
                <th>{{ __('Status') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($orders as $order)
                <tr>
                    <td>{{ $order->order_number }}</td>
                    <td>{{ $order->created_at?->format('d M Y') }}</td>
                    <td>{{ optional($order->receiver)->receiver_name }}</td>
                    <td>{{ optional(optional($order->receiver)->city)->name }} / {{ optional(optional($order->receiver)->area)->name }}</td>
                    <td>{{ strtoupper(optional($order->payment)->payment_type ?? '') }}</td>
                    <td>{{ number_format(optional($order->payment)->order_amount ?? 0, 2) }} JD</td>
                    <td>{{ number_format(optional($order->payment)->customer_delivery_amount ?? 0, 2) }} JD</td>
                    <td>{{ ucfirst($order->status) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center;padding:24px;color:#64748b;">{{ __('No orders found for this filter.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary-row">
        <div class="summary-box">
            <div class="label">{{ __('Total COD Value') }}</div>
            <div class="value">{{ number_format($totalCod, 2) }} JD</div>
        </div>
        <div class="summary-box">
            <div class="label">{{ __('Delivery Fees') }}</div>
            <div class="value">{{ number_format($totalDelivery, 2) }} JD</div>
        </div>
    </div>

</body>
</html>
