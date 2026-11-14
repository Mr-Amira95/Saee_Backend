<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Delivery Waybill') }}</title>
    {{--
        Thermal label (see WaybillExportService::renderLabel) — the content of
        pdf.blade.php condensed for a 40 x 60 mm page (5 mm side, 1.5 mm top
        margins; the footer note is pinned 5 mm above the bottom edge). The
        signature lines stay on the A4 waybill only. Pure black and white only:
        no colours or greys, which dither badly on thermal heads.
    --}}
    <style>
        * {
            box-sizing: border-box;
        }
        /* Bottom margin = 5 mm gap + 8.5 mm reserved for the footer note */
        @page {
            margin: 1.5mm 5mm 13.5mm 5mm;
        }
        body {
            color: #000;
            font-family: DejaVu Sans, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 3.5pt;
            line-height: 1.05;
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

        .hd {
            text-align: center;
        }
        .hd img {
            display: block;
            width: 18mm;
            margin: 0 auto 0.3mm auto;
        }
        .order-no {
            margin-top: 0.7mm;
            font-size: 5.3pt;
            font-weight: bold;
        }
        .order-date {
            margin-bottom: 0.7mm;
            font-size: 3.5pt;
        }

        .qr-row {
            margin-top: 0.6mm;
        }
        .qr-row .qr {
            width: 8.5mm;
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
            font-size: 3.5pt;
            white-space: normal;
        }

        .sec {
            margin-top: 1mm;
        }
        .sec-title {
            background: #000;
            color: #fff;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            padding: 0.2mm 0;
            margin-bottom: 0.3mm;
        }
        .lbl {
            font-weight: bold;
        }

        /* Fixed in the bottom margin so it repeats on every label; text sits on
           the bottom of the box, 5 mm above the edge, however many lines it wraps to */
        .note {
            position: fixed;
            left: 0;
            right: 0;
            bottom: -8.5mm;
            height: 8.5mm;
        }
        .note td {
            height: 8.5mm;
            vertical-align: bottom;
            font-size: 2.2pt;
            line-height: 1;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="note">
    <table><tr><td>{{ __("The sender agrees that Sa'ee Logistics Services' responsibility is limited to transportation and delivery services, and the company bears no responsibility for damage, shortage, or loss resulting from improper packaging, the nature of the shipment's contents, incorrect information provided by the sender, force majeure, or any cause beyond the company's control.") }}</td></tr></table>
</div>

@foreach($orders as $order)
    @php
        $orderAmount    = $order->payment?->order_amount ?? 0;
        $deliveryAmount = $order->payment?->customer_delivery_amount ?? 0;
        $toCollect      = ($order->payment?->payment_type ?? 'cod') === 'cod' ? $orderAmount + $deliveryAmount : $deliveryAmount;
    @endphp
    <div class="label-page @unless($loop->last) with-break @endif">

        <div class="hd">
            <img src="{{ public_path('pdf-assets/logo-black.png') }}" alt="Sa'ee Logistics">
            <div class="order-no">#{{ $order->order_number }}</div>
            <div class="order-date">{{ $order->created_at?->format('Y-m-d H:i') }}</div>
        </div>

        <table class="qr-row">
            <tr>
                <td class="qr">
                    {{-- dompdf ignores inline <svg>; an SVG data-URI image renders --}}
                    <img src="data:image/svg+xml;base64,{{ base64_encode(QrCode::format('svg')->size(120)->margin(0)->color(0, 0, 0)->generate($order->order_number)) }}" style="width: 7.5mm; height: 7.5mm;" alt="">
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
                            <td style="font-size: 2.8pt; vertical-align: middle;">{{ __('Total Cash to Collect:') }}</td>
                            <td class="val" style="white-space: nowrap; vertical-align: middle;">{{ number_format($toCollect, 2) }} JOD</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <div class="sec">
            <div class="sec-title">{{ __('Sender Details') }}</div>
            <div>{{ $order->clientProfile?->company_name ?? 'N/A' }} · {{ $order->clientProfile?->company_phone ?? $order->clientProfile?->masterUser?->phone ?? 'N/A' }}</div>
        </div>

        <div class="sec">
            <div class="sec-title">{{ __('Customer Details') }}</div>
            <div style="font-weight: bold; font-size: 4.4pt;">{{ $order->receiver?->receiver_name }} · {{ $order->receiver?->receiver_phone }}</div>
            <div>{{ $order->receiver?->city?->name ?? 'N/A' }} - {{ $order->receiver?->area?->name ?? 'N/A' }}</div>
            @if($order->receiver?->address_text)
                <div>{{ Str::limit($order->receiver->address_text, 55) }}</div>
            @endif
        </div>

        {{-- Values only, no field titles; empty description / notes are skipped --}}
        @if($order->order_description || $order->notes)
            <div class="sec">
                <div class="sec-title">{{ __('Order Details') }}</div>
                @if($order->order_description)
                    <div>{{ Str::limit($order->order_description, 40) }}</div>
                @endif
                @if($order->notes)
                    <div>{{ Str::limit($order->notes, 40) }}</div>
                @endif
            </div>
        @endif
    </div>
@endforeach

</body>
</html>
