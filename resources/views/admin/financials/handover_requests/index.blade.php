@extends('admin.layouts.app')

@section('title', __('Handover'))
@section('page-title', __('Handover'))

@section('breadcrumb')
    <span class="sep">/</span>
    <span class="current">{{ __('Handover') }}</span>
@endsection

@section('content')
@php
    $tabs = [
        'drivers'  => ['label' => __('Pending Handover'),   'sub' => number_format($totalDriverCash, 2) . ' JD', 'tint' => 'rgba(239, 68, 68, 0.12)',
                       'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'],
        'pending'  => ['label' => __('Pending Approvals'),  'sub' => $pendingRequests->count() . ' ' . __('requests'), 'tint' => 'rgba(245, 158, 11, 0.15)',
                       'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        'approved' => ['label' => __('Approved Handovers'), 'sub' => $approvedRequests->count() . ' ' . __('requests'), 'tint' => 'rgba(34, 197, 94, 0.1)',
                       'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
    ];
    $activeTab = array_key_exists(request('tab'), $tabs) ? request('tab') : 'drivers';
@endphp
    {{-- Page Header --}}
    <div class="page-hd">
        <div class="page-hd-left">
            <h1>{{ __('Handover') }}</h1>
            <p>{{ __('Track the cash drivers are holding, settle it manually, and approve driver handover requests.') }}</p>
        </div>
    </div>

    {{-- Session Messages --}}
    @if(session('success'))
        <div style="margin-top: 15px; padding: 12px 16px; background: rgba(34, 197, 94, 0.15); border: 1px solid #22c55e; border-radius: 8px; color: #4ade80; font-size: 0.88rem;">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="margin-top: 15px; padding: 12px 16px; background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; border-radius: 8px; color: #f87171; font-size: 0.88rem;">
            {{ session('error') }}
        </div>
    @endif

    {{-- Tab Navigation --}}
    <div style="margin-top: 18px;">
        <div style="display: flex; border-bottom: 2px solid var(--bdr); gap: 4px; flex-wrap: wrap;">
            @foreach($tabs as $key => $tab)
                <button type="button" class="handover-tab-btn" data-tab="{{ $key }}" onclick="switchTab('{{ $key }}')"
                    style="display: flex; align-items: center; gap: 10px; padding: 12px 20px; background: none; border: none; border-bottom: 2px solid transparent; margin-bottom: -2px; cursor: pointer; font-size: 0.88rem; font-weight: 600; color: var(--text-dim); transition: all 0.15s;">
                    <div style="display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px; background: {{ $tab['tint'] }};">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $tab['icon'] }}"/></svg>
                    </div>
                    <div style="text-align: left;">
                        <div>{{ $tab['label'] }}</div>
                        <div style="font-size: 0.75rem; color: var(--text-dim); line-height: 1.1;">{{ $tab['sub'] }}</div>
                    </div>
                </button>
            @endforeach
        </div>

        {{-- Tab: Pending Handover (cash held by drivers) --}}
        <div id="tab-drivers" class="handover-tab" style="margin-top: 18px;">
            <div class="table-card">
                <div style="padding: 16px; border-bottom: 1px solid var(--bdr); display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="font-size: 0.9rem; font-weight: 700; color: var(--text-sub); text-transform: uppercase; letter-spacing: 0.08em;">{{ __('Cash Held by Drivers') }}</h3>
                    <span style="font-size: 0.72rem; color: var(--text-dim);">{{ __('Settle collected cash to office') }}</span>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>{{ __('Driver Name') }}</th>
                                <th>{{ __('Pending Orders') }}</th>
                                <th>{{ __('Cash Held') }}</th>
                                <th style="width: 150px; text-align: center;">{{ __('Actions') }}</th>
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
                                        <span class="badge badge-pending">{{ $data['pending_orders_count'] }} {{ __('orders') }}</span>
                                    </td>
                                    <td>
                                        <strong style="color: var(--red-lt);">{{ number_format($data['balance'], 2) }} JD</strong>
                                    </td>
                                    <td>
                                        <div class="act-btns" style="justify-content: center;">
                                            @if($data['pending_orders_count'] > 0)
                                                <a href="{{ route('admin.financials.handover-requests.settle', $data['driver']) }}" class="btn-primary" style="padding: 6px 12px; font-size: 0.78rem; text-decoration: none; box-shadow: none;">
                                                    {{ __('Settle Cash') }}
                                                </a>
                                            @elseif($data['pending_handover_request'])
                                                <a href="{{ route('admin.financials.handover-requests.show', $data['pending_handover_request']) }}" class="btn-primary" style="padding: 6px 12px; font-size: 0.78rem; text-decoration: none; box-shadow: none;">
                                                    {{ __('Review Handover') }}
                                                </a>
                                            @else
                                                <span class="cell-sub" style="font-style: italic;">{{ __('Settled') }}</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="text-align: center; color: var(--text-dim); padding: 40px 20px;">
                                        {{ __('All drivers have fully settled their cash collections.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Tab: Pending Approvals --}}
        <div id="tab-pending" class="handover-tab" style="margin-top: 18px;">
            <div class="table-card">
                <div style="padding: 16px; border-bottom: 1px solid var(--bdr); display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="font-size: 0.9rem; font-weight: 700; color: var(--text-sub); text-transform: uppercase; letter-spacing: 0.08em;">{{ __('Pending Handover Requests') }}</h3>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>{{ __('Driver Name') }}</th>
                                <th>{{ __('Submitted At') }}</th>
                                <th>{{ __('Driver Notes') }}</th>
                                <th style="width: 150px; text-align: center;">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingRequests as $request)
                                <tr>
                                    <td>
                                        <div class="cell-main">{{ $request->driver->name }}</div>
                                        <div class="cell-sub">{{ $request->driver->phone }}</div>
                                    </td>
                                    <td>
                                        <div class="cell-main">{{ $request->created_at->format('Y-m-d H:i') }}</div>
                                        <div class="cell-sub">{{ $request->created_at->diffForHumans() }}</div>
                                    </td>
                                    <td>
                                        <span class="cell-sub" style="font-style: italic;">{{ $request->notes ?: __('No notes provided') }}</span>
                                    </td>
                                    <td>
                                        <div class="act-btns" style="justify-content: center;">
                                            <a href="{{ route('admin.financials.handover-requests.show', $request) }}" class="btn-primary" style="padding: 6px 12px; font-size: 0.78rem; text-decoration: none; box-shadow: none;">
                                                {{ __('Review & Approve') }}
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="text-align: center; color: var(--text-dim); padding: 40px 20px;">
                                        {{ __('No pending checkout approvals. All driver handovers are up to date!') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Tab: Approved Handovers --}}
        <div id="tab-approved" class="handover-tab" style="margin-top: 18px;">
            <div class="table-card">
                <div style="padding: 16px; border-bottom: 1px solid var(--bdr); display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="font-size: 0.9rem; font-weight: 700; color: var(--text-sub); text-transform: uppercase; letter-spacing: 0.08em;">{{ __('Approved Handover History') }}</h3>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>{{ __('Driver Name') }}</th>
                                <th>{{ __('Submitted At') }}</th>
                                <th>{{ __('Approved By') }}</th>
                                <th>{{ __('Approved At') }}</th>
                                <th style="width: 120px; text-align: center;">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($approvedRequests as $request)
                                <tr>
                                    <td>
                                        <div class="cell-main">{{ $request->driver->name }}</div>
                                        <div class="cell-sub">{{ $request->driver->phone }}</div>
                                    </td>
                                    <td>
                                        <div class="cell-main">{{ $request->created_at->format('Y-m-d H:i') }}</div>
                                        <div class="cell-sub">{{ $request->created_at->diffForHumans() }}</div>
                                    </td>
                                    <td>
                                        <div class="cell-main">{{ $request->approver?->name ?? __('System') }}</div>
                                    </td>
                                    <td>
                                        <div class="cell-main">{{ $request->approved_at?->format('Y-m-d H:i') ?? '-' }}</div>
                                        <div class="cell-sub">{{ $request->approved_at?->diffForHumans() }}</div>
                                    </td>
                                    <td>
                                        <div class="act-btns" style="justify-content: center;">
                                            <a href="{{ route('admin.financials.handover-requests.show', $request) }}" class="btn-secondary" style="padding: 6px 12px; font-size: 0.78rem; text-decoration: none; display: flex; align-items: center; gap: 4px;">
                                                {{ __('View Details') }}
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align: center; color: var(--text-dim); padding: 40px 20px;">
                                        {{ __('No approved handovers found in history.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Tab Switching Script --}}
    <script>
        function switchTab(tab) {
            document.querySelectorAll('.handover-tab').forEach(function (panel) {
                panel.style.display = panel.id === 'tab-' + tab ? 'block' : 'none';
            });
            document.querySelectorAll('.handover-tab-btn').forEach(function (btn) {
                var active = btn.dataset.tab === tab;
                btn.style.borderBottomColor = active ? '#f59e0b' : 'transparent';
                btn.style.color = active ? '#f59e0b' : 'var(--text-dim)';
            });
        }
        switchTab(@json($activeTab));
    </script>
@endsection
