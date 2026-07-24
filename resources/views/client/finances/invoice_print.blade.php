<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Invoice') }} {{ $invoice->invoice_number }}</title>
    <style>
        @page { size: A4; margin: 14mm; }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            color: #0f172a;
            background: #fff;
            font-size: 11pt;
            line-height: 1.5;
        }

        .doc { max-width: 800px; margin: 0 auto; }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 14pt;
            margin-bottom: 20pt;
            border-bottom: 2px solid #cbd5e1;
        }
        .header img { height: 42px; width: auto; }
        .header .meta { text-align: right; }
        .header .meta h1 { font-size: 15pt; font-weight: 800; margin: 0 0 4pt; }
        .header .meta p { font-size: 9pt; color: #475569; margin: 2pt 0 0; }

        .badge { display: inline-block; padding: 2pt 8pt; border-radius: 100pt; font-size: 8pt; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; border: 1px solid currentColor; }
        .badge-paid { color: #15803d; background: #f0fdf4; }
        .badge-unpaid { color: #b45309; background: #fffbeb; }

        .stats { display: flex; gap: 10pt; margin-bottom: 18pt; }
        .stat { flex: 1; border: 1px solid #cbd5e1; border-radius: 6pt; padding: 8pt 10pt; }
        .stat .val { font-size: 12pt; font-weight: 800; }
        .stat .lbl { font-size: 7.5pt; color: #64748b; text-transform: uppercase; letter-spacing: .05em; margin-top: 2pt; }

        .info-grid { display: flex; gap: 16pt; margin-bottom: 18pt; }
        .info-box { flex: 1; border: 1px solid #cbd5e1; border-radius: 6pt; overflow: hidden; page-break-inside: avoid; }
        .info-box h3 { font-size: 8.5pt; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #475569; margin: 0; padding: 8pt 10pt; border-bottom: 1px solid #e2e8f0; background: #f8fafc; }
        .info-box .rows { padding: 8pt 10pt; }
        .info-row { display: flex; justify-content: space-between; gap: 10pt; padding: 3pt 0; font-size: 9.5pt; }
        .info-row .k { color: #64748b; }
        .info-row .v { font-weight: 600; text-align: right; }

        table { width: 100%; border-collapse: collapse; font-size: 9.5pt; margin-bottom: 16pt; }
        thead { display: table-header-group; }
        tfoot { display: table-footer-group; }
        th { background: #f1f5f9; color: #475569; text-align: left; font-size: 8pt; text-transform: uppercase; letter-spacing: .04em; padding: 7pt 8pt; border-bottom: 1px solid #cbd5e1; }
        html[dir="rtl"] th { text-align: right; }
        td { padding: 7pt 8pt; border-bottom: 1px solid #e2e8f0; }
        tr { page-break-inside: avoid; }
        .num { text-align: right; white-space: nowrap; }
        .total-row td { border-top: 2px solid #cbd5e1; border-bottom: none; font-weight: 800; }

        .footer { border-top: 1px solid #e2e8f0; padding-top: 10pt; text-align: center; font-size: 8pt; color: #94a3b8; }

        .no-print { text-align: center; margin: 16px 0; }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" style="padding:8px 18px;font-size:13px;cursor:pointer;">{{ __('Print Invoice') }}</button>
    </div>

    <div class="doc">
        <div class="header">
            <img src="{{ asset('saee_logo_light.png') }}" alt="Sa'ee Logistics">
            <div class="meta">
                <h1>{{ $invoice->invoice_number }}</h1>
                <p>{{ __('Date') }}: {{ $invoice->created_at->format('d M Y') }}</p>
                <p>
                    @if($invoice->status === 'paid')
                        <span class="badge badge-paid">{{ __('Paid') }}</span>
                    @else
                        <span class="badge badge-unpaid">{{ ucfirst($invoice->status) }}</span>
                    @endif
                </p>
            </div>
        </div>

        @php
            $totalCod = $orders->sum(fn($o) => (float) ($o->payment?->order_amount ?? 0));
            $totalCustDel = $orders->sum(fn($o) => $o->payment?->delivery_on_customer ? (float) ($o->payment?->customer_delivery_amount ?? 0) : 0);
            $totalNet = $totalCod + $totalCustDel;
            $c = $invoice->clientProfile;
        @endphp

        <div class="stats">
            <div class="stat">
                <div class="val">{{ $orders->count() }}</div>
                <div class="lbl">{{ __('Total Orders') }}</div>
            </div>
            <div class="stat">
                <div class="val">{{ number_format($totalCod, 2) }} JD</div>
                <div class="lbl">{{ __('COD Collected') }}</div>
            </div>
            <div class="stat">
                <div class="val">{{ number_format($totalCustDel, 2) }} JD</div>
                <div class="lbl">{{ __('Customer Delivery') }}</div>
            </div>
            <div class="stat">
                <div class="val">{{ number_format($totalNet, 2) }} JD</div>
                <div class="lbl">{{ __('Net Payout Paid') }}</div>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-box">
                <h3>{{ __('Payment Information') }}</h3>
                <div class="rows">
                    <div class="info-row"><span class="k">{{ __('Payment Method') }}</span><span class="v">{{ __('Direct Transfer / Payout Cash') }}</span></div>
                    <div class="info-row"><span class="k">{{ __('Reference Number') }}</span><span class="v">{{ $invoice->payoutLedgerEntry->reference_number ?? __('N/A') }}</span></div>
                    <div class="info-row"><span class="k">{{ __('Ledger Ref ID') }}</span><span class="v">#{{ $invoice->payout_ledger_entry_id }}</span></div>
                    <div class="info-row"><span class="k">{{ __('Recorded By') }}</span><span class="v">{{ $invoice->payoutLedgerEntry->recordedBy->name ?? __('System') }}</span></div>
                </div>
            </div>
            <div class="info-box">
                <h3>{{ __('Merchant Information') }}</h3>
                <div class="rows">
                    <div class="info-row"><span class="k">{{ __('Company Name') }}</span><span class="v">{{ $c->company_name }}</span></div>
                    <div class="info-row"><span class="k">{{ __('Merchant ID') }}</span><span class="v">#{{ $c->id }}</span></div>
                    <div class="info-row"><span class="k">{{ __('Email') }}</span><span class="v">{{ $c->email ?? __('N/A') }}</span></div>
                    <div class="info-row"><span class="k">{{ __('Address') }}</span><span class="v">{{ $c->address_line1 ?? __('N/A') }}, {{ $c->city->name ?? '' }}</span></div>
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>{{ __('Order Number') }}</th>
                    <th>{{ __('Recipient') }}</th>
                    <th class="num">{{ __('COD Amount') }}</th>
                    <th class="num">{{ __('Customer Delivery') }}</th>
                    <th class="num">{{ __('Net Payout') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $o)
                    @php
                        $codAmt  = (float) ($o->payment?->order_amount ?? 0);
                        $custDel = $o->payment?->delivery_on_customer ? (float) ($o->payment?->customer_delivery_amount ?? 0) : 0;
                        $net     = $codAmt + $custDel;
                    @endphp
                    <tr>
                        <td>#{{ $o->order_number }}</td>
                        <td>{{ $o->receiver?->receiver_name ?? '—' }}</td>
                        <td class="num">{{ number_format($codAmt, 2) }} JD</td>
                        <td class="num">{{ $custDel > 0 ? number_format($custDel, 2).' JD' : '—' }}</td>
                        <td class="num">{{ number_format($net, 2) }} JD</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center;color:#94a3b8;padding:20pt;">{{ __('No orders found linked to this invoice reference transaction.') }}</td>
                    </tr>
                @endforelse
            </tbody>
            @if($orders->count())
            <tfoot>
                <tr class="total-row">
                    <td colspan="2">{{ __('Total') }}</td>
                    <td class="num">{{ number_format($totalCod, 2) }} JD</td>
                    <td class="num">{{ $totalCustDel > 0 ? '+'.number_format($totalCustDel, 2).' JD' : '—' }}</td>
                    <td class="num">{{ number_format($totalNet, 2) }} JD</td>
                </tr>
            </tfoot>
            @endif
        </table>

        <div class="footer">
            <p>{{ __("Thank you for choosing Sa'ee Logistics. For any inquiries, please contact our support system.") }}</p>
        </div>
    </div>

    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
</body>
</html>
