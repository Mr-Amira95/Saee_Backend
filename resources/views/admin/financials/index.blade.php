@extends('admin.layouts.app')

@section('title', 'Financial Settlements')
@section('page-title', 'Financial Settlements')

@section('breadcrumb')
    <span class="sep">/</span>
    <span class="current">Finance Dashboard</span>
@endsection

@section('content')
    {{-- Page Header --}}
    <div class="page-hd">
        <div class="page-hd-left">
            <h1>Financial Settlements</h1>
            <p>Audit cash collections, settle driver cash accounts, and run client COD payouts.</p>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="mini-stats">
        <div class="mini-stat">
            <div class="mini-stat-icon" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <div>
                <div class="ms-val" style="color: #fcd34d;">{{ number_format($totalDriverCash, 2) }} JD</div>
                <div class="ms-lbl">Total Cash Held by Drivers</div>
            </div>
        </div>
        <div class="mini-stat">
            <div class="mini-stat-icon" style="background: rgba(34, 197, 94, 0.15); color: #22c55e;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 8h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <div class="ms-val" style="color: #4ade80;">{{ number_format($totalClientPayoutsDue, 2) }} JD</div>
                <div class="ms-lbl">Total Pending Client Payouts</div>
            </div>
        </div>
    </div>

    {{-- Left & Right Columns for Driver Settle vs Client Payout --}}
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 18px;">
        
        {{-- 1. Driver Cash Balances Table Card --}}
        <div class="table-card" style="height: fit-content;">
            <div style="padding: 16px; border-bottom: 1px solid var(--bdr); display: flex; justify-content: space-between; align-items: center;">
                <h3 style="font-size: 0.9rem; font-weight: 700; color: var(--text-sub); text-transform: uppercase; letter-spacing: 0.08em;">Driver Cash Collections</h3>
                <span style="font-size: 0.72rem; color: var(--text-dim);">Settle collected cash to office</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Driver Name</th>
                            <th>Pending Orders</th>
                            <th>Cash Held</th>
                            <th style="width: 100px; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($driverBalances as $data)
                            <tr>
                                <td>
                                    <div class="cell-main">{{ $data['driver']->name }}</div>
                                    <div class="cell-sub">{{ $data['driver']->phone }}</div>
                                </td>
                                <td>
                                    <span class="badge badge-pending">{{ $data['pending_orders_count'] }} orders</span>
                                </td>
                                <td>
                                    <strong style="color: var(--red-lt);">{{ number_format($data['balance'], 2) }} JD</strong>
                                </td>
                                <td>
                                    <div class="act-btns" style="justify-content: center;">
                                        @if($data['pending_orders_count'] > 0)
                                            <a href="{{ route('admin.financials.settle-driver', $data['driver']) }}" class="btn-primary" style="padding: 6px 12px; font-size: 0.78rem; text-decoration: none; box-shadow: none;">
                                                Settle Cash
                                            </a>
                                        @else
                                            <span class="cell-sub" style="font-style: italic;">Settled</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align: center; color: var(--text-dim); padding: 30px;">
                                    All drivers have fully settled their cash collections.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- 2. Client Payout Balances Table Card --}}
        <div class="table-card" style="height: fit-content;">
            <div style="padding: 16px; border-bottom: 1px solid var(--bdr); display: flex; justify-content: space-between; align-items: center;">
                <h3 style="font-size: 0.9rem; font-weight: 700; color: var(--text-sub); text-transform: uppercase; letter-spacing: 0.08em;">Client Payout Accounts</h3>
                <span style="font-size: 0.72rem; color: var(--text-dim);">Transfer COD collections to Clients</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Client / Merchant</th>
                            <th>Pending COD</th>
                            <th>Net Balance Due</th>
                            <th style="width: 100px; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clientBalances as $data)
                            <tr>
                                <td>
                                    <div class="cell-main">{{ $data['client']->company_name }}</div>
                                    <div class="cell-sub">ID: {{ $data['client']->id }}</div>
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $data['pending_payout_count'] }} orders</span>
                                </td>
                                <td>
                                    <strong style="color: #22c55e;">{{ number_format($data['net_balance_due'], 2) }} JD</strong>
                                </td>
                                <td>
                                    <div class="act-btns" style="justify-content: center;">
                                        @if($data['pending_payout_count'] > 0)
                                            <a href="{{ route('admin.financials.payout-client', $data['client']) }}" class="btn-primary" style="padding: 6px 12px; font-size: 0.78rem; text-decoration: none; background: linear-gradient(135deg, #16a34a, #22c55e); box-shadow: none;">
                                                Payout Client
                                            </a>
                                        @else
                                            <span class="cell-sub" style="font-style: italic;">No pending payout</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align: center; color: var(--text-dim); padding: 30px;">
                                    No outstanding payouts due to clients.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection
