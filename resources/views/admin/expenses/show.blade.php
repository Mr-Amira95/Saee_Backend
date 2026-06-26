@extends('admin.layouts.app')

@section('title', 'Expense — ' . $expense->category->label())
@section('page-title', 'Expense Detail')

@section('breadcrumb')
    <span class="sep">/</span>
    <a href="{{ route('admin.expenses.index') }}">Expenses</a>
    <span class="sep">/</span>
    <span class="current">{{ $expense->category->label() }}</span>
@endsection

@section('content')
    {{-- Header --}}
    <div class="page-hd">
        <div class="page-hd-left">
            <h1>{{ $expense->category->label() }}</h1>
            <p>{{ $expense->payment_date->format('d M Y') }}
               @if($expense->vendor) &nbsp;·&nbsp; {{ $expense->vendor }} @endif
            </p>
        </div>
        <div style="display:flex;gap:8px;align-items:center;">
            <form method="POST" action="{{ route('admin.expenses.destroy', $expense) }}" style="display:inline;"
                  onsubmit="return confirm('Delete this expense?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn-secondary" style="color:#f87171;border-color:rgba(220,38,38,.3);">
                    Delete
                </button>
            </form>
            <a href="{{ route('admin.expenses.index') }}" class="btn-secondary">← Back</a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="mini-stats" style="margin-bottom:18px;">
        <div class="mini-stat">
            <div class="mini-stat-icon" style="background:rgba(239,68,68,.08);color:#f87171;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="ms-val" style="color:#f87171;">{{ number_format($expense->amount, 2) }} JD</div>
                <div class="ms-lbl">Amount</div>
            </div>
        </div>
        <div class="mini-stat">
            <div style="padding:8px 16px;">
                <span class="badge badge-pv" style="font-size:.85rem;padding:6px 12px;">{{ $expense->category->label() }}</span>
            </div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
        {{-- Expense details --}}
        <div class="table-card" style="height:fit-content;">
            <div style="padding:16px;border-bottom:1px solid var(--bdr);">
                <h3 style="font-size:.9rem;font-weight:700;color:var(--text-sub);text-transform:uppercase;letter-spacing:.08em;">Expense Details</h3>
            </div>
            <div style="padding:16px;">
                <div class="info-rows">
                    <div class="info-row">
                        <span class="info-row-key">Category</span>
                        <span class="info-row-val">{{ $expense->category->label() }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-row-key">Amount</span>
                        <span class="info-row-val" style="font-weight:700;font-size:1.05rem;color:#f87171;">
                            {{ number_format($expense->amount, 2) }} JD
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-row-key">Payment Date</span>
                        <span class="info-row-val">{{ $expense->payment_date->format('d M Y') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-row-key">Payment Method</span>
                        <span class="info-row-val">{{ ucfirst(str_replace('_', ' ', $expense->payment_method)) }}</span>
                    </div>
                    @if($expense->vendor)
                    <div class="info-row">
                        <span class="info-row-key">Vendor / Payee</span>
                        <span class="info-row-val">{{ $expense->vendor }}</span>
                    </div>
                    @endif
                    @if($expense->reference_number)
                    <div class="info-row">
                        <span class="info-row-key">Reference</span>
                        <span class="info-row-val" style="font-family:monospace;">{{ $expense->reference_number }}</span>
                    </div>
                    @endif
                    <div class="info-row" style="display:block;">
                        <span class="info-row-key">Description</span>
                        <div style="margin-top:6px;font-size:.87rem;color:var(--text-sub);line-height:1.5;">{{ $expense->description }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:20px;">
            {{-- Approval info --}}
            <div class="table-card">
                <div style="padding:16px;border-bottom:1px solid var(--bdr);">
                    <h3 style="font-size:.9rem;font-weight:700;color:var(--text-sub);text-transform:uppercase;letter-spacing:.08em;">Audit</h3>
                </div>
                <div style="padding:16px;">
                    <div class="info-rows">
                        <div class="info-row">
                            <span class="info-row-key">Recorded By</span>
                            <span class="info-row-val">{{ $expense->recordedBy->name ?? '—' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-row-key">Recorded At</span>
                            <span class="info-row-val">{{ $expense->created_at->format('d M Y H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Receipt --}}
            @if($expense->receipt_path)
            <div class="table-card">
                <div style="padding:16px;border-bottom:1px solid var(--bdr);">
                    <h3 style="font-size:.9rem;font-weight:700;color:var(--text-sub);text-transform:uppercase;letter-spacing:.08em;">Receipt</h3>
                </div>
                <div style="padding:16px;">
                    @php $ext = pathinfo($expense->receipt_path, PATHINFO_EXTENSION); @endphp
                    @if(in_array(strtolower($ext), ['jpg','jpeg','png']))
                        <img src="{{ asset('storage/' . $expense->receipt_path) }}"
                             style="max-width:100%;border-radius:8px;border:1px solid var(--bdr);" alt="Receipt">
                    @else
                        <a href="{{ asset('storage/' . $expense->receipt_path) }}"
                           target="_blank" class="btn-secondary" style="display:inline-block;">
                            Download Receipt ({{ strtoupper($ext) }})
                        </a>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
@endsection
