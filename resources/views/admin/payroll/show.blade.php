@extends('admin.layouts.app')

@section('title', 'Payroll — ' . $payment->driverProfile->user->name)
@section('page-title', 'Payroll Detail')

@section('breadcrumb')
    <span class="sep">/</span>
    <a href="{{ route('admin.payroll.index') }}">Payroll</a>
    <span class="sep">/</span>
    <span class="current">{{ $payment->driverProfile->user->name }}</span>
@endsection

@section('content')
    {{-- Header --}}
    <div class="page-hd">
        <div class="page-hd-left">
            <h1>Payroll — {{ $payment->driverProfile->user->name }}</h1>
            <p>{{ $payment->period_start->format('d M Y') }} to {{ $payment->period_end->format('d M Y') }}</p>
        </div>
        <div style="display:flex;gap:8px;align-items:center;">
            @if($payment->status->value === 'draft')
                <form method="POST" action="{{ route('admin.payroll.approve', $payment) }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn-primary" style="background: linear-gradient(135deg, #1d4ed8, #3b82f6); box-shadow:none;">
                        ✓ Approve
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.payroll.destroy', $payment) }}" style="display:inline;"
                      onsubmit="return confirm('Delete this draft?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-secondary" style="color:#f87171;border-color:rgba(220,38,38,.3);">
                        Delete Draft
                    </button>
                </form>
            @elseif($payment->status->value === 'approved')
                <button type="button" class="btn-primary" onclick="document.getElementById('pay-form').style.display='block'">
                    Mark as Paid
                </button>
            @endif
            <a href="{{ route('admin.payroll.index') }}" class="btn-secondary">← Back</a>
        </div>
    </div>

    {{-- Pay form (shown on click) --}}
    @if($payment->status->value === 'approved')
    <div id="pay-form" style="display:none;" class="form-section" style="margin-bottom:20px;">
        <form method="POST" action="{{ route('admin.payroll.pay', $payment) }}">
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

    {{-- Status badge row --}}
    <div class="mini-stats" style="margin-bottom:18px;">
        <div class="mini-stat">
            <div class="mini-stat-icon" style="background: rgba(34,197,94,.12); color: #22c55e;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="ms-val" style="color:#22c55e;">{{ number_format($payment->net_amount, 2) }} JD</div>
                <div class="ms-lbl">Net Payment</div>
            </div>
        </div>
        <div class="mini-stat">
            <div class="mini-stat-icon" style="background: rgba(245,158,11,.12); color: #f59e0b;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <div>
                <div class="ms-val">{{ $payment->order_count }}</div>
                <div class="ms-lbl">Orders Delivered in Period</div>
            </div>
        </div>
        <div class="mini-stat">
            <div style="padding: 8px 16px;">
                @if($payment->status->value === 'draft')
                    <span class="badge badge-pending" style="font-size:.85rem;padding:6px 12px;">Draft</span>
                @elseif($payment->status->value === 'approved')
                    <span class="badge badge-info" style="font-size:.85rem;padding:6px 12px;">Approved</span>
                @else
                    <span class="badge badge-active" style="font-size:.85rem;padding:6px 12px;">Paid</span>
                @endif
            </div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
        {{-- Salary breakdown card --}}
        <div class="table-card" style="height:fit-content;">
            <div style="padding:16px;border-bottom:1px solid var(--bdr);">
                <h3 style="font-size:.9rem;font-weight:700;color:var(--text-sub);text-transform:uppercase;letter-spacing:.08em;">Salary Breakdown</h3>
            </div>
            <div style="padding:16px;">
                <div class="info-rows">
                    <div class="info-row">
                        <span class="info-row-key">Basic Salary</span>
                        <span class="info-row-val">{{ number_format($payment->basic_salary, 2) }} JD</span>
                    </div>
                    <div class="info-row">
                        <span class="info-row-key">Car & Gasoline Allowance</span>
                        <span class="info-row-val">{{ number_format($payment->car_allowance, 2) }} JD</span>
                    </div>
                    <div class="info-row">
                        <span class="info-row-key">Extra Orders Count</span>
                        <span class="info-row-val">{{ $payment->extra_orders_count }} orders</span>
                    </div>
                    <div class="info-row">
                        <span class="info-row-key">Bonus Per Extra Order</span>
                        <span class="info-row-val">{{ number_format($payment->extra_order_bonus, 2) }} JD</span>
                    </div>
                    <div class="info-row">
                        <span class="info-row-key">Total Bonus</span>
                        <span class="info-row-val">{{ number_format($payment->extra_order_bonus * $payment->extra_orders_count, 2) }} JD</span>
                    </div>
                    <div class="info-row" style="border-top:1px solid var(--bdr);padding-top:10px;margin-top:4px;">
                        <span class="info-row-key">Gross Amount</span>
                        <span class="info-row-val" style="font-weight:700;">{{ number_format($payment->gross_amount, 2) }} JD</span>
                    </div>
                    <div class="info-row">
                        <span class="info-row-key">Deductions</span>
                        <span class="info-row-val" style="color:#f87171;">-{{ number_format($payment->deductions, 2) }} JD</span>
                    </div>
                    <div class="info-row" style="border-top:1px solid var(--bdr);padding-top:10px;margin-top:4px;">
                        <span class="info-row-key" style="font-weight:700;">Net Payment</span>
                        <span class="info-row-val" style="font-size:1.1rem;font-weight:800;color:#22c55e;">{{ number_format($payment->net_amount, 2) }} JD</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Meta card --}}
        <div class="table-card" style="height:fit-content;">
            <div style="padding:16px;border-bottom:1px solid var(--bdr);">
                <h3 style="font-size:.9rem;font-weight:700;color:var(--text-sub);text-transform:uppercase;letter-spacing:.08em;">Payment Information</h3>
            </div>
            <div style="padding:16px;">
                <div class="info-rows">
                    <div class="info-row">
                        <span class="info-row-key">Payment Method</span>
                        <span class="info-row-val">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</span>
                    </div>
                    @if($payment->reference_number)
                    <div class="info-row">
                        <span class="info-row-key">Reference Number</span>
                        <span class="info-row-val" style="font-family:monospace;">{{ $payment->reference_number }}</span>
                    </div>
                    @endif
                    <div class="info-row">
                        <span class="info-row-key">Recorded By</span>
                        <span class="info-row-val">{{ $payment->recordedBy->name ?? '—' }}</span>
                    </div>
                    @if($payment->approvedBy)
                    <div class="info-row">
                        <span class="info-row-key">Approved By</span>
                        <span class="info-row-val">{{ $payment->approvedBy->name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-row-key">Approved At</span>
                        <span class="info-row-val">{{ $payment->approved_at?->format('d M Y H:i') }}</span>
                    </div>
                    @endif
                    @if($payment->paid_at)
                    <div class="info-row">
                        <span class="info-row-key">Paid At</span>
                        <span class="info-row-val" style="color:#22c55e;">{{ $payment->paid_at->format('d M Y H:i') }}</span>
                    </div>
                    @endif
                    @if($payment->notes)
                    <div class="info-row" style="display:block;">
                        <span class="info-row-key">Notes</span>
                        <div style="margin-top:6px;font-size:.85rem;color:var(--text-sub);line-height:1.5;">{{ $payment->notes }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
