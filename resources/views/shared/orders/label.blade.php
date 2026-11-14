<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Delivery Waybill') }}</title>
    {{--
        Thermal label (see WaybillExportService::renderLabel) — same content as
        pdf.blade.php, condensed for a 40 x 60 mm page. Pure black only: no
        colours, greys or background fills, which dither badly on thermal heads.
    --}}
    <style>
        * {
            box-sizing: border-box;
        }
        @page {
            margin: 1mm;
        }
        body {
            color: #000;
            font-family: DejaVu Sans, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 4pt;
            line-height: 1.1;
        }
        .label-page.with-break {
            page-break-after: always;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }
        td {
            padding: 0;
            vertical-align: top;
        }

        .hd td {
            vertical-align: middle;
            border-bottom: 0.6pt solid #000;
            padding-bottom: 0.3mm;
        }
        .hd img {
            width: 10mm;
        }
        .hd .order {
            text-align: right;
        }
        .order-no {
            font-size: 6pt;
            font-weight: bold;
        }
        .order-date {
            font-size: 4pt;
        }

        .qr-row td {
            padding-top: 0.3mm;
            padding-bottom: 0.3mm;
            border-bottom: 0.6pt solid #000;
        }
        .qr-row .qr {
            width: 9.5mm;
            text-align: center;
        }
        .fin td {
            padding: 0;
            white-space: nowrap;
        }
        .fin .val {
            text-align: right;
        }
        .fin .total td {
            border-top: 0.6pt solid #000;
            padding-top: 0.2mm;
            font-weight: bold;
            font-size: 5pt;
        }

        .sec {
            border-bottom: 0.6pt solid #000;
            padding: 0.2mm 0;
        }
        .sec-title {
            font-weight: bold;
            text-transform: uppercase;
        }
        .lbl {
            font-weight: bold;
        }

        .sig td {
            width: 50%;
            text-align: center;
            padding: 0 1mm;
        }
        .sig-line {
            border-bottom: 0.6pt solid #000;
            height: 1.8mm;
        }
        .sig-label {
            font-size: 3.2pt;
        }

        .disclaimer {
            margin-top: 0.4mm;
            font-size: 2.8pt;
            line-height: 1;
            text-align: justify;
        }
    </style>
</head>
<body>

@foreach($orders as $order)
    @php
        $orderAmount    = $order->payment?->order_amount ?? 0;
        $deliveryAmount = $order->payment?->customer_delivery_amount ?? 0;
        $toCollect      = ($order->payment?->payment_type ?? 'cod') === 'cod' ? $orderAmount + $deliveryAmount : $deliveryAmount;
    @endphp
    <div class="label-page @unless($loop->last) with-break @endif">

        <table class="hd">
            <tr>
                <td><img src="{{ public_path('pdf-assets/logo-black.png') }}" alt="Sa'ee Logistics"></td>
                <td class="order">
                    <div class="order-no">#{{ $order->order_number }}</div>
                    <div class="order-date">{{ $order->created_at?->format('Y-m-d H:i') }}</div>
                </td>
            </tr>
        </table>

        <table class="qr-row">
            <tr>
                <td class="qr">
                    {{-- dompdf ignores inline <svg>; an SVG data-URI image renders --}}
                    <img src="data:image/svg+xml;base64,{{ base64_encode(QrCode::format('svg')->size(120)->margin(0)->color(0, 0, 0)->generate($order->order_number)) }}" style="width: 8.5mm; height: 8.5mm;" alt="">
                </td>
                <td style="padding-left: 1mm;">
                    <table class="fin">
                        <tr>
                            <td class="lbl">{{ __('Payment:') }}</td>
                            <td class="val" style="text-transform: uppercase; font-weight: bold;">{{ $order->payment?->payment_type ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td>{{ __('Order Price:') }}</td>
                            <td class="val">{{ number_format($orderAmount, 2) }}</td>
                        </tr>
                        <tr>
                            <td>{{ __('Delivery Charge:') }}</td>
                            <td class="val">{{ number_format($deliveryAmount, 2) }}</td>
                        </tr>
                        <tr class="total">
                            <td colspan="2">{{ __('Total Cash to Collect:') }}<br>{{ number_format($toCollect, 2) }} JOD</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <div class="sec">
            <div><span class="sec-title">{{ __('Sender (Client)') }}:</span> {{ $order->clientProfile?->company_name ?? 'N/A' }} · {{ $order->clientProfile?->company_phone ?? $order->clientProfile?->masterUser?->phone ?? 'N/A' }}</div>
            <div><span class="lbl">{{ __('Batch No:') }}</span> {{ $order->batch_number ?? 'N/A' }}</div>
        </div>

        <div class="sec">
            <div class="sec-title">{{ __('Receiver (Customer)') }}:</div>
            <div style="font-weight: bold; font-size: 5pt;">{{ $order->receiver?->receiver_name }} · {{ $order->receiver?->receiver_phone }}</div>
            <div><span class="lbl">{{ __('Destination:') }}</span> {{ $order->receiver?->city?->name ?? 'N/A' }} - {{ $order->receiver?->area?->name ?? 'N/A' }}</div>
            <div><span class="lbl">{{ __('Address:') }}</span> {{ Str::limit($order->receiver?->address_text, 75) }}</div>
        </div>

        <div class="sec">
            <div><span class="lbl">{{ __('Description:') }}</span> {{ Str::limit($order->order_description ?? __('No description'), 60) }}</div>
            <div><span class="lbl">{{ __('Shift:') }}</span> {{ match ($order->delivery_shift) { 'before_12pm' => __('Before 12 PM (Morning)'), 'after_12pm' => __('After 12 PM (Evening)'), default => __("Doesn't Matter") } }}</div>
            <div><span class="lbl">{{ __('Notes:') }}</span> {{ Str::limit($order->notes ?? __('None'), 60) }}</div>
        </div>

        <table class="sig" style="margin-top: 0.4mm;">
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

        <div class="disclaimer">
            <strong>{{ __('Disclaimer:') }}</strong> {{ __("The sender agrees that Sa'ee Logistics Services' responsibility is limited to transportation and delivery services, and the company bears no responsibility for damage, shortage, or loss resulting from improper packaging, the nature of the shipment's contents, incorrect information provided by the sender, force majeure, or any cause beyond the company's control.") }}
        </div>
    </div>
@endforeach

</body>
</html>
