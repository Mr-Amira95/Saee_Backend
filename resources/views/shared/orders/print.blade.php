<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Print Order Waybills') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            background: #ffffff;
            color: #0c1230;
            font-family: 'Outfit', sans-serif;
            margin: 0;
            padding: 0;
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
            transition: background 0.15s;
        }
        .no-print-btn:hover {
            background: #c94040;
        }

        .print-page {
            width: 210mm;
            height: 297mm;
            padding: 20mm 15mm;
            margin: 0 auto;
            position: relative;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .waybill-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0c1230;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .company-info h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            color: #e05454;
            letter-spacing: 0.5px;
        }
        .company-info p {
            margin: 2px 0 0 0;
            color: #64748b;
            font-size: 12px;
        }

        .waybill-title {
            text-align: right;
        }
        .waybill-title h2 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            color: #0c1230;
        }
        .waybill-title .order-no {
            font-size: 16px;
            font-weight: 700;
            color: #e05454;
            margin-top: 5px;
        }
        .waybill-title .order-date {
            font-size: 11px;
            color: #64748b;
            margin-top: 2px;
        }

        .barcode-wrap {
            text-align: center;
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .mock-barcode {
            font-family: monospace;
            font-size: 24px;
            letter-spacing: 8px;
            font-weight: bold;
            color: #000;
        }
        .barcode-text {
            font-size: 11px;
            color: #64748b;
            margin-top: 5px;
            letter-spacing: 1px;
        }

        .parties-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }
        .info-card {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 15px;
        }
        .info-card h3 {
            margin: 0 0 10px 0;
            font-size: 13px;
            font-weight: 600;
            color: #0c1230;
            text-transform: uppercase;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 5px;
        }
        .info-row {
            display: flex;
            margin-bottom: 6px;
        }
        .info-row:last-child {
            margin-bottom: 0;
        }
        .info-label {
            width: 100px;
            color: #64748b;
            font-weight: 500;
        }
        .info-value {
            flex: 1;
            font-weight: 500;
        }

        .shipment-details {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 25px;
        }
        .shipment-details h3 {
            margin: 0 0 12px 0;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 5px;
        }
        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .finance-summary {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            margin-left: auto;
            width: 320px;
            margin-bottom: 30px;
        }
        .finance-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 13px;
        }
        .finance-row:last-child {
            margin-bottom: 0;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            font-weight: 700;
            font-size: 15px;
        }
        .finance-val.highlight {
            color: #e05454;
        }

        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: auto;
            border-top: 1px dashed #cbd5e1;
            padding-top: 25px;
        }
        .sig-block {
            text-align: center;
        }
        .sig-line {
            border-bottom: 1px solid #94a3b8;
            height: 50px;
            margin-bottom: 8px;
        }
        .sig-label {
            font-size: 12px;
            color: #64748b;
            font-weight: 500;
        }

        @media print {
            .no-print-btn {
                display: none !important;
            }
            body {
                background: none;
            }
            .print-page {
                box-shadow: none;
                margin: 0;
                padding: 15mm 10mm;
                page-break-after: always;
                break-after: page;
                height: 100vh;
            }
            .print-page:last-child {
                page-break-after: avoid;
                break-after: avoid;
            }
        }

        /* ─── RTL Directional Overrides ──────────────────── */
        html[dir="rtl"] .waybill-title { text-align: left; }
        html[dir="rtl"] .company-info { text-align: right; flex-direction: row-reverse; }
        html[dir="rtl"] .finance-summary { margin-right: 0; margin-left: auto; }
    </style>
</head>
<body>

    <button onclick="window.print()" class="no-print-btn">{{ __('Print Now') }}</button>

    @foreach($orders as $order)
    <div class="print-page">
        <div>
            {{-- Header --}}
            <div class="waybill-header">
                <div class="company-info" style="display: flex; align-items: center; gap: 14px;">
                    <img src="{{ asset('saee_logo_light.png') }}" alt="Sa'ee Logistics" style="height: 52px; width: auto; object-fit: contain;">
                    <div>
                        <h1 style="margin: 0; font-size: 22px; font-weight: 800; color: #0c1230; letter-spacing: 0.5px; line-height: 1.1;">SA'EE LOGISTICS</h1>
                        <p style="margin: 2px 0 0 0; color: #64748b; font-size: 12px;">Reliable Delivery Solutions</p>
                        <p style="margin: 2px 0 0 0; color: #64748b; font-size: 12px;">Phone: +962 7 9000 0000 | support@saee.logistics</p>
                    </div>
                </div>
                <div class="waybill-title">
                    <h2>{{ __('DELIVERY WAYBILL') }}</h2>
                    <div class="order-no">#{{ $order->order_number }}</div>
                    <div class="order-date">{{ __('Date:') }} {{ $order->created_at?->format('Y-m-d H:i') }}</div>
                </div>
            </div>

            {{-- QR Code Wrap --}}
            <div class="barcode-wrap" style="text-align: center; padding: 15px; margin-bottom: 20px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px;">
                <div style="background: white; padding: 8px; border: 1px solid #cbd5e1; border-radius: 8px; display: inline-flex; margin-bottom: 5px;">
                    {!! QrCode::size(70)->generate($order->order_number) !!}
                </div>
                <div class="barcode-text" style="margin-top: 2px; font-weight: 600; font-family: monospace;">*{{ $order->order_number }}*</div>
            </div>

            {{-- Parties Grid --}}
            <div class="parties-grid">
                {{-- Sender --}}
                <div class="info-card">
                    <h3>{{ __('Sender (Client)') }}</h3>
                    <div class="info-row">
                        <div class="info-label">{{ __('Company:') }}</div>
                        <div class="info-value">{{ $order->clientProfile?->company_name ?? 'N/A' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">{{ __('Phone:') }}</div>
                        <div class="info-value">{{ $order->clientProfile?->company_phone ?? $order->clientProfile?->masterUser?->phone ?? 'N/A' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">{{ __('Batch No:') }}</div>
                        <div class="info-value">{{ $order->batch_number ?? 'N/A' }}</div>
                    </div>
                </div>

                {{-- Receiver --}}
                <div class="info-card">
                    <h3>{{ __('Receiver (Customer)') }}</h3>
                    <div class="info-row">
                        <div class="info-label">{{ __('Name:') }}</div>
                        <div class="info-value">{{ $order->receiver?->receiver_name }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">{{ __('Phone:') }}</div>
                        <div class="info-value">{{ $order->receiver?->receiver_phone }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">{{ __('Destination:') }}</div>
                        <div class="info-value">
                            {{ $order->receiver?->city?->name ?? 'N/A' }} - {{ $order->receiver?->area?->name ?? 'N/A' }}
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">{{ __('Address:') }}</div>
                        <div class="info-value">{{ $order->receiver?->address_text }}</div>
                    </div>
                </div>
            </div>

            {{-- Shipment Details --}}
            <div class="shipment-details">
                <h3>{{ __('Shipment Specifications') }}</h3>
                <div class="details-grid">
                    <div>
                        <div class="info-row">
                            <div class="info-label">{{ __('Description:') }}</div>
                            <div class="info-value">{{ $order->order_description ?? __('No description') }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">{{ __('Shift:') }}</div>
                            <div class="info-value">
                                @if($order->delivery_shift === 'before_12pm')
                                    {{ __('Before 12 PM (Morning)') }}
                                @elseif($order->delivery_shift === 'after_12pm')
                                    {{ __('After 12 PM (Evening)') }}
                                @else
                                    {{ __('Doesn\'t Matter') }}
                                @endif
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="info-row">
                            <div class="info-label">{{ __('Payment:') }}</div>
                            <div class="info-value" style="text-transform: uppercase; font-weight: bold;">
                                {{ $order->payment?->payment_type ?? 'N/A' }}
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">{{ __('Notes:') }}</div>
                            <div class="info-value">{{ $order->notes ?? __('None') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Finance Summary --}}
            <div class="finance-summary">
                <div class="finance-row">
                    <span>{{ __('Order Price:') }}</span>
                    <span>{{ number_format($order->payment?->order_amount ?? 0, 2) }} JOD</span>
                </div>
                <div class="finance-row">
                    <span>{{ __('Delivery Charge:') }}</span>
                    <span>{{ number_format($order->payment?->customer_delivery_amount ?? 0, 2) }} JOD</span>
                </div>
                <div class="finance-row">
                    <span>{{ __('Total Cash to Collect:') }}</span>
                    <span class="finance-val highlight">
                        @if(($order->payment?->payment_type ?? 'cod') === 'cod')
                            {{ number_format(($order->payment?->order_amount ?? 0) + ($order->payment?->customer_delivery_amount ?? 0), 2) }} JOD
                        @else
                            {{ number_format($order->payment?->customer_delivery_amount ?? 0, 2) }} JOD
                        @endif
                    </span>
                </div>
            </div>
        </div>

        {{-- Signatures --}}
        <div class="signatures">
            <div class="sig-block">
                <div class="sig-line"></div>
                <div class="sig-label">{{ __('Courier Signature & Date') }}</div>
            </div>
            <div class="sig-block">
                <div class="sig-line"></div>
                <div class="sig-label">{{ __('Receiver Signature & Date') }}</div>
            </div>
        </div>
    </div>
    @endforeach

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            // Auto trigger print window
            setTimeout(() => {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>
