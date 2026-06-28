@extends('admin.layouts.app')

@section('title', 'Checkout Approvals')
@section('page-title', 'Checkout Approvals')

@section('breadcrumb')
    <a href="{{ route('admin.financials.index') }}">Finance Dashboard</a>
    <span class="sep">/</span>
    <span class="current">Checkout Approvals</span>
@endsection

@section('content')
    {{-- Page Header --}}
    <div class="page-hd">
        <div class="page-hd-left">
            <h1>Checkout Handover Approvals</h1>
            <p>Review and approve driver checkout requests, cash transfers, and order returns.</p>
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
        <div style="display: flex; border-bottom: 2px solid var(--bdr); gap: 4px;">
            <button id="tab-pending-btn" onclick="switchTab('pending')"
                style="display: flex; align-items: center; gap: 10px; padding: 12px 20px; background: none; border: none; border-bottom: 2px solid #f59e0b; margin-bottom: -2px; cursor: pointer; font-size: 0.88rem; font-weight: 600; color: #f59e0b; transition: all 0.15s;">
                <div style="display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px; background: rgba(245, 158, 11, 0.15);">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div style="text-align: left;">
                    <div>Pending Approvals</div>
                    <div style="font-size: 0.75rem; color: var(--text-dim); line-height: 1.1;">{{ $pendingRequests->count() }} requests</div>
                </div>
            </button>
            <button id="tab-approved-btn" onclick="switchTab('approved')"
                style="display: flex; align-items: center; gap: 10px; padding: 12px 20px; background: none; border: none; border-bottom: 2px solid transparent; margin-bottom: -2px; cursor: pointer; font-size: 0.88rem; font-weight: 600; color: var(--text-dim); transition: all 0.15s;">
                <div style="display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px; background: rgba(34, 197, 94, 0.1);">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div style="text-align: left;">
                    <div>Approved Handovers</div>
                    <div style="font-size: 0.75rem; color: var(--text-dim); line-height: 1.1;">{{ $approvedRequests->count() }} requests</div>
                </div>
            </button>
        </div>

        {{-- Tab: Pending Approvals --}}
        <div id="tab-pending" style="margin-top: 18px;">
            <div class="table-card">
                <div style="padding: 16px; border-bottom: 1px solid var(--bdr); display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="font-size: 0.9rem; font-weight: 700; color: var(--text-sub); text-transform: uppercase; letter-spacing: 0.08em;">Pending Handover Requests</h3>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Driver Name</th>
                                <th>Submitted At</th>
                                <th>Driver Notes</th>
                                <th style="width: 150px; text-align: center;">Actions</th>
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
                                        <span class="cell-sub" style="font-style: italic;">{{ $request->notes ?: 'No notes provided' }}</span>
                                    </td>
                                    <td>
                                        <div class="act-btns" style="justify-content: center;">
                                            <a href="{{ route('admin.financials.handover-requests.show', $request) }}" class="btn-primary" style="padding: 6px 12px; font-size: 0.78rem; text-decoration: none; box-shadow: none;">
                                                Review & Approve
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="text-align: center; color: var(--text-dim); padding: 40px 20px;">
                                        No pending checkout approvals. All driver handovers are up to date!
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Tab: Approved Handovers --}}
        <div id="tab-approved" style="margin-top: 18px; display: none;">
            <div class="table-card">
                <div style="padding: 16px; border-bottom: 1px solid var(--bdr); display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="font-size: 0.9rem; font-weight: 700; color: var(--text-sub); text-transform: uppercase; letter-spacing: 0.08em;">Approved Handover History</h3>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Driver Name</th>
                                <th>Submitted At</th>
                                <th>Approved By</th>
                                <th>Approved At</th>
                                <th style="width: 120px; text-align: center;">Actions</th>
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
                                        <div class="cell-main">{{ $request->approver?->name ?? 'System' }}</div>
                                    </td>
                                    <td>
                                        <div class="cell-main">{{ $request->approved_at?->format('Y-m-d H:i') ?? '-' }}</div>
                                        <div class="cell-sub">{{ $request->approved_at?->diffForHumans() }}</div>
                                    </td>
                                    <td>
                                        <div class="act-btns" style="justify-content: center;">
                                            <a href="{{ route('admin.financials.handover-requests.show', $request) }}" class="btn-secondary" style="padding: 6px 12px; font-size: 0.78rem; text-decoration: none; display: flex; align-items: center; gap: 4px;">
                                                View Details
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align: center; color: var(--text-dim); padding: 40px 20px;">
                                        No approved handovers found in history.
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
            const pendingBtn = document.getElementById('tab-pending-btn');
            const approvedBtn = document.getElementById('tab-approved-btn');
            const pendingDiv = document.getElementById('tab-pending');
            const approvedDiv = document.getElementById('tab-approved');

            if (tab === 'pending') {
                pendingBtn.style.borderBottomColor = '#f59e0b';
                pendingBtn.style.color = '#f59e0b';
                pendingBtn.querySelector('div').style.background = 'rgba(245, 158, 11, 0.15)';

                approvedBtn.style.borderBottomColor = 'transparent';
                approvedBtn.style.color = 'var(--text-dim)';
                approvedBtn.querySelector('div').style.background = 'rgba(34, 197, 94, 0.1)';

                pendingDiv.style.display = 'block';
                approvedDiv.style.display = 'none';
            } else {
                approvedBtn.style.borderBottomColor = '#f59e0b';
                approvedBtn.style.color = '#f59e0b';
                approvedBtn.querySelector('div').style.background = 'rgba(245, 158, 11, 0.15)';

                pendingBtn.style.borderBottomColor = 'transparent';
                pendingBtn.style.color = 'var(--text-dim)';
                pendingBtn.querySelector('div').style.background = 'rgba(34, 197, 94, 0.1)';

                pendingDiv.style.display = 'none';
                approvedDiv.style.display = 'block';
            }
        }
    </script>
@endsection
