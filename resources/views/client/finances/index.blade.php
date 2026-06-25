@extends('client.layouts.app')
@section('title', 'Finances')
@section('page-title', 'Finances')

@push('styles')
<style>
    .fin-tabs { display:flex; gap:0; border-bottom:1px solid var(--bdr); margin-bottom:20px; }
    .fin-tab  { padding:10px 20px; font-size:.86rem; font-weight:600; color:var(--text-dim); cursor:pointer; border-bottom:2px solid transparent; transition:color .15s,border-color .15s; text-decoration:none; }
    .fin-tab:hover { color:var(--text-sub); }
    .fin-tab.active { color:var(--text); border-bottom-color:var(--red); }
    .fin-panel { display:none; }
    .fin-panel.active { display:block; }
    .ledger-type-credit { color:#4ade80; }
    .ledger-type-debit  { color:#f87171; }
</style>
@endpush

@section('content')

<h1 style="font-size:1.35rem;font-weight:800;margin-bottom:16px;">Finances</h1>

{{-- Balance card --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px;margin-bottom:24px;">
    <div class="card" style="background:linear-gradient(135deg,rgba(220,38,38,.12),rgba(220,38,38,.04));">
        <div style="font-size:.74rem;color:var(--text-dim);text-transform:uppercase;letter-spacing:.1em;margin-bottom:10px;">Wallet Balance</div>
        <div style="font-size:2rem;font-weight:800;color:{{ $balance >= 0 ? '#4ade80' : '#f87171' }};">{{ number_format($balance, 2) }} <span style="font-size:1rem;font-weight:600;color:var(--text-sub);">JD</span></div>
        <div style="font-size:.79rem;color:var(--text-dim);margin-top:6px;">Available to transfer</div>
    </div>
    <div class="card">
        <div style="font-size:.74rem;color:var(--text-dim);text-transform:uppercase;letter-spacing:.1em;margin-bottom:10px;">Credit Limit</div>
        <div style="font-size:2rem;font-weight:800;color:var(--text);">{{ number_format($creditLimit, 2) }} <span style="font-size:1rem;font-weight:600;color:var(--text-sub);">JD</span></div>
        <div style="font-size:.79rem;color:var(--text-dim);margin-top:6px;">Maximum outstanding balance</div>
    </div>
    <div class="card">
        <div style="font-size:.74rem;color:var(--text-dim);text-transform:uppercase;letter-spacing:.1em;margin-bottom:10px;">Total Invoiced</div>
        <div style="font-size:2rem;font-weight:800;color:var(--text);">{{ $invoices->total() }}</div>
        <div style="font-size:.79rem;color:var(--text-dim);margin-top:6px;">Invoices issued</div>
    </div>
</div>

{{-- Tabs --}}
<div class="fin-tabs">
    <a href="#ledger"   class="fin-tab active" onclick="switchTab('ledger',event)">Transactions</a>
    <a href="#invoices" class="fin-tab"         onclick="switchTab('invoices',event)">Invoices</a>
</div>

{{-- Ledger panel --}}
<div class="fin-panel active" id="panel-ledger">
    <div class="card" style="padding:0;overflow:hidden;">
        @if($ledger->count())
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Balance After</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ledger as $entry)
                    <tr>
                        <td style="color:var(--text-dim);font-size:.82rem;white-space:nowrap;">{{ $entry->created_at->format('d M Y') }}</td>
                        <td style="font-size:.86rem;">{{ $entry->description ?? ucfirst(str_replace('_',' ',$entry->entry_type)) }}</td>
                        <td>
                            <span class="badge {{ $entry->amount >= 0 ? 'badge-success' : 'badge-danger' }}" style="font-size:.72rem;">
                                {{ $entry->amount >= 0 ? 'Credit' : 'Debit' }}
                            </span>
                        </td>
                        <td class="{{ $entry->amount >= 0 ? 'ledger-type-credit' : 'ledger-type-debit' }}" style="font-weight:700;white-space:nowrap;">
                            {{ $entry->amount >= 0 ? '+' : '' }}{{ number_format($entry->amount, 2) }} JD
                        </td>
                        <td style="font-size:.85rem;white-space:nowrap;">{{ number_format($entry->balance_after ?? 0, 2) }} JD</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div style="padding:48px;text-align:center;color:var(--text-dim);font-size:.87rem;">No transactions yet.</div>
        @endif
    </div>
    @if($ledger->hasPages())
    <div style="margin-top:16px;">{{ $ledger->appends(['tab'=>'ledger'])->links() }}</div>
    @endif
</div>

{{-- Invoices panel --}}
<div class="fin-panel" id="panel-invoices">
    <div class="card" style="padding:0;overflow:hidden;">
        @if($invoices->count())
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Download</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoices as $inv)
                    <tr>
                        <td style="font-family:monospace;font-size:.83rem;color:var(--red-lt);">{{ $inv->invoice_number }}</td>
                        <td style="color:var(--text-dim);font-size:.82rem;white-space:nowrap;">{{ $inv->created_at->format('d M Y') }}</td>
                        <td style="font-weight:700;white-space:nowrap;">{{ number_format($inv->amount ?? 0, 2) }} JD</td>
                        <td>
                            @php
                                $sc = match($inv->status ?? 'issued') {
                                    'paid'   => 'badge-success',
                                    'void'   => 'badge-neutral',
                                    default  => 'badge-pending',
                                };
                            @endphp
                            <span class="badge {{ $sc }}" style="font-size:.72rem;">{{ ucfirst($inv->status ?? 'Issued') }}</span>
                        </td>
                        <td>
                            @if(!empty($inv->file_path))
                            <a href="{{ asset('storage/'.$inv->file_path) }}" target="_blank" class="btn-secondary" style="padding:4px 10px;font-size:.76rem;">
                                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                PDF
                            </a>
                            @else
                            <span style="font-size:.78rem;color:var(--text-dim);">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div style="padding:48px;text-align:center;color:var(--text-dim);font-size:.87rem;">No invoices issued yet.</div>
        @endif
    </div>
    @if($invoices->hasPages())
    <div style="margin-top:16px;">{{ $invoices->appends(['tab'=>'invoices'])->links() }}</div>
    @endif
</div>

@endsection

@push('scripts')
<script>
function switchTab(name, e) {
    e.preventDefault();
    document.querySelectorAll('.fin-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.fin-panel').forEach(p => p.classList.remove('active'));
    e.currentTarget.classList.add('active');
    document.getElementById('panel-' + name).classList.add('active');
}

// Activate tab from URL hash on load
const hash = location.hash.replace('#','');
if (hash === 'invoices') switchTab('invoices', {preventDefault:()=>{}, currentTarget: document.querySelector('.fin-tab:nth-child(2)')});
</script>
@endpush
