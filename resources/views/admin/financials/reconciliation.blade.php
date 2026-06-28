@extends('admin.layouts.app')

@section('title', 'Financial Reconciliation')
@section('page-title', 'Financial Reconciliation')

@section('breadcrumb')
    <span class="sep">/</span>
    <span class="current">Reconciliation</span>
@endsection

@section('content')
    <div class="page-hd">
        <div class="page-hd-left">
            <h1>Financial Reconciliation</h1>
            <p>Perform automated audits on driver cash collections vs settlements to ensure zero cash loss.</p>
        </div>
    </div>

    {{-- Reconciliation Summary Stats --}}
    <div class="mini-stats">
        <div class="mini-stat">
            <div class="mini-stat-icon" style="background: rgba(59, 130, 246, 0.15); color: #3b82f6;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <div class="ms-val" style="color: #60a5fa;">{{ number_format($totalCollected, 2) }} JD</div>
                <div class="ms-lbl">Gross Driver Cash Collections</div>
            </div>
        </div>
        <div class="mini-stat">
            <div class="mini-stat-icon" style="background: rgba(34, 197, 94, 0.15); color: #22c55e;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div>
                <div class="ms-val" style="color: #4ade80;">{{ number_format($totalSettled, 2) }} JD</div>
                <div class="ms-lbl">Total Cash Settled to Company</div>
            </div>
        </div>
        <div class="mini-stat">
            <div class="mini-stat-icon" style="background: rgba(239, 68, 68, 0.15); color: #ef4444;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <div class="ms-val" style="color: {{ $netDiscrepancy > 0 ? 'var(--red-lt)' : '#4ade80' }};">
                    {{ number_format($netDiscrepancy, 2) }} JD
                </div>
                <div class="ms-lbl">Total Cash Remaining in Field</div>
            </div>
        </div>
    </div>

    {{-- Drivers Cash Discrepancy Audit Card --}}
    <div class="table-card" style="margin-top: 20px;">
        <div style="padding: 16px; border-bottom: 1px solid var(--bdr); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 0.9rem; font-weight: 700; color: var(--text-sub); text-transform: uppercase; letter-spacing: 0.08em;">Driver Ledger Balance Audit</h3>
            <span style="font-size: 0.72rem; color: var(--text-dim);">Verify driver cash holdings matches office logs</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Driver / Agent</th>
                        <th>Total Cash Collected</th>
                        <th>Total Cash Settled</th>
                        <th>Outstanding Cash Held</th>
                        <th style="text-align: center;">Audit Status</th>
                        <th style="width: 120px; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($drivers as $d)
                        <tr style="{{ $d['mismatch'] ? 'background: rgba(220,38,38,0.02);' : '' }}">
                            <td>
                                <div class="cell-main">{{ $d['driver']->name }}</div>
                                <div class="cell-sub">{{ $d['driver']->phone }}</div>
                            </td>
                            <td>{{ number_format($d['collected'], 2) }} JD</td>
                            <td>{{ number_format($d['settled'], 2) }} JD</td>
                            <td>
                                <strong style="color: {{ $d['cash_held'] > 0 ? 'var(--red-lt)' : '#4ade80' }}">
                                    {{ number_format($d['cash_held'], 2) }} JD
                                </strong>
                            </td>
                            <td style="text-align: center;">
                                @if($d['mismatch'])
                                    <span class="badge badge-suspended">
                                        <span class="badge-dot"></span> Discrepancy Alert
                                    </span>
                                @elseif($d['cash_held'] > 0)
                                    <span class="badge badge-pending">
                                        <span class="badge-dot"></span> Pending Settle
                                    </span>
                                @else
                                    <span class="badge badge-active">
                                        <span class="badge-dot"></span> Balanced
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="act-btns" style="justify-content: center;">
                                    <a href="{{ route('admin.drivers.show', $d['driver']->driverProfile) }}" class="btn-secondary" style="padding: 6px 12px; font-size: 0.75rem;">
                                        View Profile
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--text-dim); padding: 30px;">
                                No drivers are currently registered in the database.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
