<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Delivery Waybill') }}</title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            color: #0c1230;
            font-family: DejaVu Sans, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 12px;
            line-height: 1.4;
        }

        .print-page {
            width: 100%;
            padding: 10mm 12mm;
        }
        .print-page.with-break {
            page-break-after: always;
        }

        table.layout {
            width: 100%;
            border-collapse: collapse;
        }

        .waybill-header td {
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
        .waybill-title {
            text-align: right;
        }
        .waybill-title h2 {
            margin: 0;
            font-size: 14px;
            font-weight: 700;
            color: #0c1230;
        }
        .waybill-title .order-no {
            font-size: 14px;
            font-weight: 700;
            color: #e05454;
            margin-top: 4px;
        }
        .waybill-title .order-date {
            font-size: 10px;
            color: #64748b;
            margin-top: 2px;
        }

        .header-spacer td {
            height: 16px;
        }

        .barcode-wrap {
            text-align: center;
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            padding: 8px;
            margin-bottom: 16px;
        }
        .barcode-text {
            font-size: 10px;
            color: #64748b;
            margin-top: 4px;
            font-weight: 600;
        }

        .parties-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 12px 0;
            margin-bottom: 16px;
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
            margin-bottom: 5px;
        }
        .info-row:last-child {
            margin-bottom: 0;
        }
        .info-label {
            color: #64748b;
            font-weight: 500;
            display: inline-block;
            width: 85px;
        }
        .info-value {
            font-weight: 500;
        }

        .shipment-details {
            border: 1px solid #e2e8f0;
            padding: 12px;
            margin-bottom: 16px;
        }
        .shipment-details h3 {
            margin: 0 0 10px 0;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
        }
        .details-grid {
            width: 100%;
        }
        .details-grid td {
            vertical-align: top;
            width: 50%;
        }

        .finance-summary {
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            padding: 12px;
            width: 60mm;
            margin-left: auto;
            margin-bottom: 20px;
        }
        .finance-row td {
            padding: 4px 0;
            font-size: 11px;
        }
        .finance-row.total td {
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
            font-weight: 700;
            font-size: 13px;
        }
        .finance-val.highlight {
            color: #e05454;
        }

        .signatures {
            width: 100%;
            border-top: 1px dashed #cbd5e1;
            padding-top: 18px;
            margin-top: 12px;
        }
        .signatures td {
            width: 50%;
            text-align: center;
            padding: 0 20px;
        }
        .sig-line {
            border-bottom: 1px solid #94a3b8;
            height: 36px;
            margin-bottom: 6px;
        }
        .sig-label {
            font-size: 10px;
            color: #64748b;
            font-weight: 500;
        }

        .disclaimer-note {
            margin-top: 12px;
            border-top: 1px solid #cbd5e1;
            padding-top: 8px;
            font-size: 9px;
            color: #64748b;
            text-align: justify;
        }
    </style>
</head>
<body>

@foreach($orders as $order)
    <div class="print-page @unless($loop->last) with-break @endif">

        <table class="layout waybill-header">
            <tr>
                <td class="company-details" style="width: 33%;">
                    <h1>SA'EE LOGISTICS</h1>
                    <p>{{ __('Reliable Delivery Solutions') }}</p>
                    <p>{{ __('Phone:') }} +962 7 9080 0989</p>
                    <p>support@saee.logistics</p>
                </td>
                <td class="logo-center" style="width: 34%;">
                    <img src="{{ public_path('pdf-assets/logo.png') }}" alt="Sa'ee Logistics">
                </td>
                <td class="waybill-title" style="width: 33%;">
                    <h2>{{ __('DELIVERY WAYBILL') }}</h2>
                    <div class="order-no">#{{ $order->order_number }}</div>
                    <div class="order-date">{{ __('Date:') }} {{ $order->created_at?->format('Y-m-d H:i') }}</div>
                </td>
            </tr>
        </table>

        <table class="layout header-spacer"><tr><td></td></tr></table>

        <div class="barcode-wrap">
            {!! QrCode::size(70)->generate($order->order_number) !!}
            <div class="barcode-text">*{{ $order->order_number }}*</div>
        </div>

        <table class="parties-grid">
            <tr>
                <td class="info-card">
                    <h3>{{ __('Sender (Client)') }}</h3>
                    <div class="info-row">
                        <span class="info-label">{{ __('Company:') }}</span>
                        <span class="info-value">{{ $order->clientProfile?->company_name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">{{ __('Phone:') }}</span>
                        <span class="info-value">{{ $order->clientProfile?->company_phone ?? $order->clientProfile?->masterUser?->phone ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">{{ __('Batch No:') }}</span>
                        <span class="info-value">{{ $order->batch_number ?? 'N/A' }}</span>
                    </div>
                </td>
                <td class="info-card">
                    <h3>{{ __('Receiver (Customer)') }}</h3>
                    <div class="info-row">
                        <span class="info-label">{{ __('Name:') }}</span>
                        <span class="info-value">{{ $order->receiver?->receiver_name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">{{ __('Phone:') }}</span>
                        <span class="info-value">{{ $order->receiver?->receiver_phone }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">{{ __('Destination:') }}</span>
                        <span class="info-value">{{ $order->receiver?->city?->name ?? 'N/A' }} - {{ $order->receiver?->area?->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">{{ __('Address:') }}</span>
                        <span class="info-value">{{ $order->receiver?->address_text }}</span>
                    </div>
                </td>
            </tr>
        </table>

        <div class="shipment-details">
            <h3>{{ __('Shipment Specifications') }}</h3>
            <table class="details-grid">
                <tr>
                    <td>
                        <div class="info-row">
                            <span class="info-label">{{ __('Description:') }}</span>
                            <span class="info-value">{{ $order->order_description ?? __('No description') }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">{{ __('Shift:') }}</span>
                            <span class="info-value">
                                @if($order->delivery_shift === 'before_12pm')
                                    {{ __('Before 12 PM (Morning)') }}
                                @elseif($order->delivery_shift === 'after_12pm')
                                    {{ __('After 12 PM (Evening)') }}
                                @else
                                    {{ __("Doesn't Matter") }}
                                @endif
                            </span>
                        </div>
                    </td>
                    <td>
                        <div class="info-row">
                            <span class="info-label">{{ __('Payment:') }}</span>
                            <span class="info-value" style="text-transform: uppercase; font-weight: bold;">{{ $order->payment?->payment_type ?? 'N/A' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">{{ __('Notes:') }}</span>
                            <span class="info-value">{{ $order->notes ?? __('None') }}</span>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <table class="finance-summary">
            <tr class="finance-row">
                <td>{{ __('Order Price:') }}</td>
                <td style="text-align: right;">{{ number_format($order->payment?->order_amount ?? 0, 2) }} JOD</td>
            </tr>
            <tr class="finance-row">
                <td>{{ __('Delivery Charge:') }}</td>
                <td style="text-align: right;">{{ number_format($order->payment?->customer_delivery_amount ?? 0, 2) }} JOD</td>
            </tr>
            <tr class="finance-row total">
                <td>{{ __('Total Cash to Collect:') }}</td>
                <td class="finance-val highlight" style="text-align: right;">
                    @if(($order->payment?->payment_type ?? 'cod') === 'cod')
                        {{ number_format(($order->payment?->order_amount ?? 0) + ($order->payment?->customer_delivery_amount ?? 0), 2) }} JOD
                    @else
                        {{ number_format($order->payment?->customer_delivery_amount ?? 0, 2) }} JOD
                    @endif
                </td>
            </tr>
        </table>

        <table class="signatures">
            <tr>
                <td>
                    <div class="sig-line"></div>
                    <div class="sig-label">{{ __('Courier Signature & Date') }}</div>
                </td>
                <td>
                    <div class="sig-line"></div>
                    <div class="sig-label">{{ __('Receiver Signature & Date') }}</div>
                </td>
            </tr>
        </table>

        <div class="disclaimer-note">
            <strong>{{ __('Disclaimer:') }}</strong> {{ __("The sender agrees that Sa'ee Logistics Services' responsibility is limited to transportation and delivery services, and the company bears no responsibility for damage, shortage, or loss resulting from improper packaging, the nature of the shipment's contents, incorrect information provided by the sender, force majeure, or any cause beyond the company's control.") }}
        </div>
    </div>
@endforeach

</body>
</html>
