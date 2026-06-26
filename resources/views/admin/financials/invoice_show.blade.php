@extends('admin.layouts.app')

@section('title', 'Invoice ' . $invoice->invoice_number)
@section('page-title', 'Invoice Detail')

@section('breadcrumb')
    <span class="sep">/</span>
    <a href="{{ route('admin.financials.invoices') }}">Invoices</a>
    <span class="sep">/</span>
    <span class="current">{{ $invoice->invoice_number }}</span>
@endsection

@section('head')
<style>
    .invoice-card {
        background: #0c1230;
        border: 1px solid var(--bdr);
        border-radius: 16px;
        padding: 40px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
        max-width: 800px;
        margin: 0 auto;
        color: #fff;
    }
    
    .inv-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        border-bottom: 2px solid rgba(255, 255, 255, 0.05);
        padding-bottom: 30px;
        margin-bottom: 30px;
    }

    .inv-logo {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .inv-logo-text {
        font-size: 1.25rem;
        font-weight: 800;
        letter-spacing: .12em;
        text-transform: uppercase;
        color: #fff;
    }

    .inv-meta {
        text-align: right;
    }

    .inv-meta h2 {
        font-size: 1.6rem;
        font-weight: 800;
        color: var(--red-lt);
        letter-spacing: -.03em;
        margin-bottom: 8px;
    }

    .inv-meta p {
        font-size: .83rem;
        color: var(--text-sub);
        margin-top: 3px;
    }

    .inv-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
        margin-bottom: 40px;
    }

    .inv-bill-title {
        font-size: .7rem;
        font-weight: 700;
        color: var(--text-dim);
        text-transform: uppercase;
        letter-spacing: .08em;
        margin-bottom: 12px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.04);
        padding-bottom: 6px;
    }

    .inv-bill-text {
        font-size: .86rem;
        color: var(--text-sub);
        line-height: 1.6;
    }

    .inv-bill-text strong {
        color: #fff;
        font-size: 1rem;
    }

    .inv-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 30px;
    }

    .inv-table th {
        background: rgba(255, 255, 255, 0.02);
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        padding: 12px;
        font-size: .75rem;
        font-weight: 700;
        color: var(--text-dim);
        text-transform: uppercase;
        letter-spacing: .06em;
    }

    .inv-table td {
        padding: 12px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.03);
        font-size: .83rem;
        color: var(--text-sub);
    }

    .inv-table tr:hover td {
        background: rgba(255, 255, 255, 0.01);
    }

    .inv-totals {
        width: 300px;
        margin-left: auto;
        margin-bottom: 30px;
    }

    .inv-total-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        font-size: .85rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.03);
    }

    .inv-total-row.grand {
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        border-bottom: none;
        padding-top: 12px;
        font-size: 1.15rem;
        font-weight: 800;
        color: #22c55e;
    }

    .inv-footer {
        border-top: 1px solid rgba(255, 255, 255, 0.05);
        padding-top: 20px;
        font-size: .78rem;
        color: var(--text-dim);
        text-align: center;
    }

    /* Print styling overrides */
    @media print {
        body {
            background: #fff !important;
            color: #000 !important;
        }
        .shell, .topbar, .sidebar, .page-hd, .breadcrumb, .btn-secondary, .logout-btn {
            display: none !important;
        }
        .main, .content {
            padding: 0 !important;
            overflow: visible !important;
            background: transparent !important;
        }
        .invoice-card {
            box-shadow: none !important;
            border: none !important;
            background: #fff !important;
            color: #000 !important;
            padding: 0 !important;
            max-width: 100% !important;
        }
        .inv-logo-text, .inv-bill-text strong, .inv-table td {
            color: #000 !important;
        }
        .inv-table th {
            background: #f1f5f9 !important;
            border-bottom: 2px solid #cbd5e1 !important;
            color: #475569 !important;
        }
        .inv-table td {
            border-bottom: 1px solid #e2e8f0 !important;
        }
        .inv-total-row {
            color: #000 !important;
            border-bottom: 1px solid #e2e8f0 !important;
        }
        .inv-total-row.grand {
            color: #16a34a !important;
            border-top: 2px solid #475569 !important;
        }
    }
</style>
@endsection

@section('content')
    {{-- Top Controls --}}
    <div class="page-hd" style="max-width: 800px; margin: 0 auto 18px;">
        <div class="page-hd-left">
            <h1>Invoice Detail</h1>
        </div>
        <div>
            <button onclick="window.print()" class="btn-primary" style="background: linear-gradient(135deg, #475569, #1e293b); box-shadow: none;">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Print Invoice
            </button>
            <a href="{{ route('admin.financials.invoices') }}" class="btn-secondary">Back to Invoices</a>
        </div>
    </div>

    {{-- Printable Card --}}
    <div class="invoice-card">
        {{-- Header --}}
        <div class="inv-header">
            <div class="inv-logo">
                <img src="{{ asset('saee_logo_light.png') }}" alt="Sa'ee Logistics" style="height: 48px; width: auto;">
            </div>
            
            <div class="inv-meta">
                <h2>{{ $invoice->invoice_number }}</h2>
                <p>Date: {{ $invoice->created_at->format('d M Y') }}</p>
                <p>Status: <span style="text-transform:uppercase; font-weight:700; color:#4ade80">{{ $invoice->status }}</span></p>
            </div>
        </div>

        {{-- Client details --}}
        <div class="inv-grid">
            <div>
                <div class="inv-bill-title">Billed To (Merchant)</div>
                <div class="inv-bill-text">
                    <strong>{{ $invoice->clientProfile->company_name }}</strong><br>
                    Merchant ID: #{{ $invoice->clientProfile->id }}<br>
                    Email: {{ $invoice->clientProfile->email ?? 'N/A' }}<br>
                    Address: {{ $invoice->clientProfile->address_line1 ?? 'N/A' }}, {{ $invoice->clientProfile->city->name ?? '' }}
                </div>
            </div>
            <div>
                <div class="inv-bill-title">Payment Information</div>
                <div class="inv-bill-text">
                    Method: Direct Transfer / Payout Cash<br>
                    Reference Number: {{ $invoice->payoutLedgerEntry->reference_number ?? 'N/A' }}<br>
                    Ledger Ref ID: #{{ $invoice->payout_ledger_entry_id }}<br>
                    Recorded By: {{ $invoice->payoutLedgerEntry->recordedBy->name ?? 'System' }}
                </div>
            </div>
        </div>

        {{-- Linked Orders breakdown --}}
        <div class="inv-bill-title">Order Breakdown List</div>
        <table class="inv-table">
            <thead>
                <tr>
                    <th>Order Number</th>
                    <th>Recipient</th>
                    <th>City / Area</th>
                    <th>Payment Type</th>
                    <th style="text-align: right;">COD Amount</th>
                    <th style="text-align: right;">Customer Delivery</th>
                    <th style="text-align: right;">Net Payout</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $o)
                    @php
                        $codAmt      = (float) ($o->payment?->order_amount ?? 0);
                        $custDel     = $o->payment?->delivery_on_customer ? (float) ($o->payment?->customer_delivery_amount ?? 0) : 0;
                        $net         = $codAmt + $custDel;
                    @endphp
                    <tr>
                        <td><strong>#{{ $o->order_number }}</strong></td>
                        <td>{{ $o->receiver?->receiver_name ?? '—' }}</td>
                        <td>{{ $o->city?->name ?? '—' }} / {{ $o->area?->name ?? '—' }}</td>
                        <td>{{ strtoupper($o->payment?->payment_type ?? '—') }}</td>
                        <td style="text-align: right;">{{ number_format($codAmt, 2) }} JD</td>
                        <td style="text-align: right;">{{ $custDel > 0 ? number_format($custDel, 2).' JD' : '—' }}</td>
                        <td style="text-align: right; font-weight: 600; color: #4ade80;">
                            {{ number_format($net, 2) }} JD
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--text-dim); padding: 20px;">
                            No orders found linked to this invoice reference transaction.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Totals Summary --}}
        <div class="inv-totals">
            <div class="inv-total-row">
                <span style="color: var(--text-dim)">COD Collected</span>
                <span>{{ number_format($invoice->cod_amount, 2) }} JD</span>
            </div>
            <div class="inv-total-row">
                <span style="color: var(--text-dim)">Customer Delivery</span>
                <span>+{{ number_format($invoice->shipping_amount, 2) }} JD</span>
            </div>
            <div class="inv-total-row grand">
                <span>Net Payout Paid</span>
                <span>{{ number_format($invoice->net_amount, 2) }} JD</span>
            </div>
        </div>

        {{-- Footer notes --}}
        <div class="inv-footer">
            <p>Thank you for choosing Sa'ee Logistics. For any inquiries, please contact our support system.</p>
            <p style="margin-top: 8px; font-size: 0.7rem; color: var(--text-dim)">Generated on {{ now()->toDateTimeString() }}</p>
        </div>
    </div>
@endsection
