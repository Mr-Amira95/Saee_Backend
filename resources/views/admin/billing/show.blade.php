@extends('admin.layouts.app')

@section('title', 'Invoice ' . $invoice->invoice_number)
@section('page-title', 'Invoice Detail')

@section('breadcrumb')
    <span class="sep">/</span>
    <a href="{{ route('admin.billing.index') }}">Billing</a>
    <span class="sep">/</span>
    <span class="current">{{ $invoice->invoice_number }}</span>
@endsection

@section('content')
    {{-- Header --}}
    <div class="page-hd">
        <div class="page-hd-left">
            <h1 style="font-family:monospace;">{{ $invoice->invoice_number }}</h1>
            <p>{{ $invoice->clientProfile->company_name ?? $invoice->clientProfile->masterUser->name }}
               &nbsp;·&nbsp; {{ $invoice->period_start->format('d M Y') }} – {{ $invoice->period_end->format('d M Y') }}</p>
        </div>
        <div style="display:flex;gap:8px;align-items:center;">
            @php $sv = $invoice->status->value; @endphp
            @if($sv === 'draft')
                <button type="button" class="btn-primary" onclick="document.getElementById('issue-form').style.display='block'">
                    Issue Invoice
                </button>
                <form method="POST" action="{{ route('admin.billing.destroy', $invoice) }}"
                      onsubmit="return confirm('Delete this draft invoice?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-secondary" style="color:#f87171;border-color:rgba(220,38,38,.3);">
                        Delete Draft
                    </button>
                </form>
            @elseif($sv === 'issued' || $sv === 'overdue')
                <button type="button" class="btn-primary" onclick="document.getElementById('pay-form').style.display='block'">
                    Record Payment
                </button>
            @endif
            <a href="{{ route('admin.billing.index') }}" class="btn-secondary">← Back</a>
        </div>
    </div>

    {{-- Issue form (shown on click) --}}
    @if($sv === 'draft')
    <div id="issue-form" style="display:none;" class="form-section">
        <form method="POST" action="{{ route('admin.billing.issue', $invoice) }}">
            @csrf
            <div class="form-section-title">Issue Invoice</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Due Date <span class="opt">(optional)</span></label>
                    <input type="date" name="due_date" class="form-input"
                           value="{{ now()->addDays(14)->format('Y-m-d') }}">
                </div>
            </div>
            <div class="form-actions" style="padding-top:0;">
                <button type="button" class="btn-secondary" onclick="document.getElementById('issue-form').style.display='none'">Cancel</button>
                <button type="submit" class="btn-primary">Confirm Issue</button>
            </div>
        </form>
    </div>
    @endif

    {{-- Pay form (shown on click) --}}
    @if($sv === 'issued' || $sv === 'overdue')
    <div id="pay-form" style="display:none;" class="form-section">
        <form method="POST" action="{{ route('admin.billing.pay', $invoice) }}">
            @csrf
            <div class="form-section-title">Record Payment</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Payment Method <span class="req">*</span></label>
                    <select name="payment_method" class="form-input" required>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="cash">Cash</option>
                        <option value="cliq">CliQ</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Reference Number</label>
                    <input type="text" name="reference_number" class="form-input" placeholder="Optional">
                </div>
            </div>
            <div class="form-actions" style="padding-top:0;">
                <button type="button" class="btn-secondary" onclick="document.getElementById('pay-form').style.display='none'">Cancel</button>
                <button type="submit" class="btn-primary">Confirm Payment</button>
            </div>
        </form>
    </div>
    @endif

    {{-- Stats --}}
    <div class="mini-stats" style="margin-bottom:18px;">
        <div class="mini-stat">
            <div class="mini-stat-icon" style="background:rgba(99,102,241,.1);color:#818cf8;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
            </div>
            <div>
                <div class="ms-val">{{ $invoice->billable_orders }}</div>
                <div class="ms-lbl">Billable Orders</div>
            </div>
        </div>
        <div class="mini-stat">
            <div class="mini-stat-icon" style="background:rgba(245,158,11,.1);color:#f59e0b;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="ms-val">{{ number_format($invoice->delivery_amount, 2) }} JD</div>
                <div class="ms-lbl">Delivery Fees</div>
            </div>
        </div>
        @if($invoice->discount_amount > 0)
        <div class="mini-stat">
            <div class="mini-stat-icon" style="background:rgba(239,68,68,.08);color:#f87171;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M17 17h.01M6.5 17.5l11-11M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/></svg>
            </div>
            <div>
                <div class="ms-val" style="color:#f87171;">-{{ number_format($invoice->discount_amount, 2) }} JD</div>
                <div class="ms-lbl">Discount</div>
            </div>
        </div>
        @endif
        <div class="mini-stat">
            <div class="mini-stat-icon" style="background:rgba(34,197,94,.1);color:#22c55e;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
            </div>
            <div>
                <div class="ms-val" style="color:#22c55e;">{{ number_format($invoice->net_amount, 2) }} JD</div>
                <div class="ms-lbl">Net Due</div>
            </div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
        {{-- Invoice info --}}
        <div class="table-card" style="height:fit-content;">
            <div style="padding:16px;border-bottom:1px solid var(--bdr);">
                <h3 style="font-size:.9rem;font-weight:700;color:var(--text-sub);text-transform:uppercase;letter-spacing:.08em;">Invoice Details</h3>
            </div>
            <div style="padding:16px;">
                <div class="info-rows">
                    <div class="info-row">
                        <span class="info-row-key">Status</span>
                        <span class="info-row-val">
                            @if($sv === 'draft')     <span class="badge badge-pending">Draft</span>
                            @elseif($sv === 'issued') <span class="badge badge-info">Issued</span>
                            @elseif($sv === 'paid')   <span class="badge badge-active">Paid</span>
                            @elseif($sv === 'overdue')<span class="badge badge-suspended">Overdue</span>
                            @else                     <span class="badge">Cancelled</span>
                            @endif
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-row-key">Due Date</span>
                        <span class="info-row-val">{{ $invoice->due_date?->format('d M Y') ?? '—' }}</span>
                    </div>
                    @if($invoice->payment_method)
                    <div class="info-row">
                        <span class="info-row-key">Payment Method</span>
                        <span class="info-row-val">{{ ucfirst(str_replace('_', ' ', $invoice->payment_method)) }}</span>
                    </div>
                    @endif
                    @if($invoice->reference_number)
                    <div class="info-row">
                        <span class="info-row-key">Reference</span>
                        <span class="info-row-val" style="font-family:monospace;">{{ $invoice->reference_number }}</span>
                    </div>
                    @endif
                    @if($invoice->paid_at)
                    <div class="info-row">
                        <span class="info-row-key">Paid At</span>
                        <span class="info-row-val" style="color:#22c55e;">{{ $invoice->paid_at->format('d M Y H:i') }}</span>
                    </div>
                    @endif
                    <div class="info-row">
                        <span class="info-row-key">Created By</span>
                        <span class="info-row-val">{{ $invoice->createdBy->name ?? '—' }}</span>
                    </div>
                    @if($invoice->notes)
                    <div class="info-row" style="display:block;">
                        <span class="info-row-key">Notes</span>
                        <div style="margin-top:6px;font-size:.85rem;color:var(--text-sub);">{{ $invoice->notes }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Client info --}}
        <div class="table-card" style="height:fit-content;">
            <div style="padding:16px;border-bottom:1px solid var(--bdr);">
                <h3 style="font-size:.9rem;font-weight:700;color:var(--text-sub);text-transform:uppercase;letter-spacing:.08em;">Client Information</h3>
            </div>
            <div style="padding:16px;">
                <div class="info-rows">
                    @php $c = $invoice->clientProfile @endphp
                    <div class="info-row">
                        <span class="info-row-key">Business Name</span>
                        <span class="info-row-val">{{ $c->company_name ?? '—' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-row-key">Contact</span>
                        <span class="info-row-val">{{ $c->masterUser?->name ?? '—' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-row-key">Phone</span>
                        <span class="info-row-val" style="font-family:monospace;">{{ $c->masterUser?->phone ?? '—' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Orders table --}}
    <div class="table-card">
        <div style="padding:16px;border-bottom:1px solid var(--bdr);display:flex;justify-content:space-between;align-items:center;">
            <h3 style="font-size:.9rem;font-weight:700;color:var(--text-sub);text-transform:uppercase;letter-spacing:.08em;">Included Orders</h3>
            <span style="font-size:.8rem;color:var(--text-dim);">{{ $invoice->orders->count() }} orders</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Recipient</th>
                        <th>Delivered</th>
                        <th style="text-align:right;">Delivery Fee</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoice->orders as $order)
                        <tr>
                            <td>
                                <div class="cell-main" style="font-family:monospace;font-size:.82rem;">{{ $order->order_number ?? '#'.$order->id }}</div>
                            </td>
                            <td>
                                <div class="cell-main">{{ $order->receiver?->receiver_name ?? '—' }}</div>
                                <div class="cell-sub">{{ $order->receiver?->receiver_phone ?? '—' }}</div>
                            </td>
                            <td>{{ $order->delivered_at?->format('d M Y') ?? '—' }}</td>
                            <td style="text-align:right;font-weight:600;">
                                {{ number_format($order->payment->client_delivery_amount ?? 0, 2) }} JD
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align:center;color:var(--text-dim);padding:30px;">
                                No orders attached.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($invoice->orders->count())
                <tfoot>
                    <tr style="border-top:2px solid var(--bdr);">
                        <td colspan="3" style="text-align:right;font-weight:700;padding:12px 16px;">Total</td>
                        <td style="text-align:right;font-weight:800;font-size:1rem;padding:12px 16px;color:#22c55e;">
                            {{ number_format($invoice->delivery_amount, 2) }} JD
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
@endsection
