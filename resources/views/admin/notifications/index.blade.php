@extends('admin.layouts.app')

@section('title', 'Notifications Management')
@section('page-title', 'Notifications Center')

@section('breadcrumb')
    <span class="sep">/</span>
    <span class="current">Notifications</span>
@endsection

@section('content')
    <div class="page-hd">
        <div class="page-hd-left">
            <h1>Notifications Center</h1>
            <p>Send pushes, dashboard alerts, and broadcast announcements to individual users or groups.</p>
        </div>
    </div>

    <div style="display: flex; flex-direction: column; gap: 20px;">

        {{-- 1. Create Broadcast Form --}}
        <div>
            <div class="form-section" style="padding: 20px;">
                <div class="form-section-title">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    Dispatch Alert
                </div>

                <form action="{{ route('admin.notifications.store') }}" method="POST" style="display: flex; flex-direction: column; gap: 14px;">
                    @csrf
                    
                    <div class="form-group">
                        <label class="form-label" for="title">Alert Title<span class="req">*</span></label>
                        <input type="text" id="title" name="title" class="form-input" required placeholder="E.g., System Update">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="message">Message Body<span class="req">*</span></label>
                        <textarea id="message" name="message" class="form-textarea" required placeholder="Describe the announcement or notification alert details..."></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="type">Alert Style / Level</label>
                        <select id="type" name="type" class="form-select">
                            <option value="info">Info (Blue)</option>
                            <option value="success">Success (Green)</option>
                            <option value="warning">Warning (Amber)</option>
                            <option value="danger">Danger (Red)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="target">Recipient Target</label>
                        <select id="target" name="target" class="form-select" onchange="toggleUserDropdown(this.value)">
                            <option value="all">Broadcast to All Users</option>
                            <option value="driver">Broadcast to Drivers Only</option>
                            <option value="client">Broadcast to Clients Only</option>
                            <option value="specific">Target Specific User</option>
                        </select>
                    </div>

                    <div class="form-group" id="specificUserWrap" style="display: none;">
                        <label class="form-label" for="user_id">Select User<span class="req">*</span></label>
                        <select id="user_id" name="user_id" class="form-select">
                            <option value="">-- Choose User --</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ ucfirst($u->role) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="link">Callback URL / Redirect Link <span class="opt">(Optional)</span></label>
                        <input type="text" id="link" name="link" class="form-input" placeholder="E.g., /admin/orders">
                    </div>

                    <button type="submit" class="btn-primary" style="justify-content: center; margin-top: 6px;">
                        Dispatch Notification
                    </button>
                </form>
            </div>
        </div>

        {{-- 2. Notifications Dispatch Logs --}}
        <div class="table-card" style="height: fit-content;">
            <div style="padding: 16px; border-bottom: 1px solid var(--bdr);">
                <h3 style="font-size: 0.9rem; font-weight: 700; color: var(--text-sub); text-transform: uppercase; letter-spacing: 0.08em;">Notification Dispatch Audit Trail</h3>
            </div>
            
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Alert Details</th>
                            <th>Target Group / User</th>
                            <th>Status / Style</th>
                            <th>FCM Push</th>
                            <th>Sent By</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($notifications as $n)
                            <tr>
                                <td>
                                    <strong style="font-size: .88rem; color: #fff;">{{ $n->title }}</strong>
                                    <div style="font-size: .78rem; color: var(--text-sub); margin-top: 4px; line-height: 1.4;">{{ $n->message }}</div>
                                    @if($n->link)
                                        <div style="font-size: .7rem; color: var(--red-lt); margin-top: 4px;">Link: {{ $n->link }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($n->user)
                                        <div class="cell-main">{{ $n->user->name }}</div>
                                        <div class="cell-sub">Role: {{ ucfirst($n->user->role) }}</div>
                                    @elseif($n->role)
                                        <span class="badge badge-info">{{ strtoupper($n->role) }}</span>
                                    @else
                                        <span class="badge badge-info">ALL</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-{{ $n->type === 'info' ? 'info' : ($n->type === 'success' ? 'active' : ($n->type === 'warning' ? 'pending' : 'suspended')) }}">
                                        {{ strtoupper($n->type) }}
                                    </span>
                                </td>
                                <td>
                                    @if($n->fcm_status === 'sent')
                                        <span class="badge badge-active" title="{{ $n->fcm_sent_count }} delivered">Sent ({{ $n->fcm_sent_count }})</span>
                                    @elseif($n->fcm_status === 'partial')
                                        <span class="badge badge-pending" title="{{ $n->fcm_sent_count }} sent / {{ $n->fcm_failed_count }} failed">Partial</span>
                                        <div style="font-size:.68rem;color:var(--text-dim);margin-top:3px;">{{ $n->fcm_sent_count }}✓ / {{ $n->fcm_failed_count }}✗</div>
                                        @if($n->fcm_error)
                                            <div style="font-size:.68rem;color:var(--red-lt);margin-top:2px;line-height:1.4;" title="{{ $n->fcm_error }}">{{ Str::limit($n->fcm_error, 60) }}</div>
                                        @endif
                                    @elseif($n->fcm_status === 'failed')
                                        <span class="badge badge-suspended">Failed</span>
                                        @if($n->fcm_error)
                                            <div style="font-size:.68rem;color:var(--red-lt);margin-top:3px;line-height:1.4;" title="{{ $n->fcm_error }}">{{ Str::limit($n->fcm_error, 60) }}</div>
                                        @endif
                                    @elseif($n->fcm_status === 'skipped')
                                        <span style="font-size:.72rem;color:var(--text-dim);">No tokens</span>
                                    @else
                                        <span style="font-size:.72rem;color:var(--text-dim);">—</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="cell-main">{{ $n->creator->name ?? 'System' }}</div>
                                </td>
                                <td>
                                    <div class="cell-main">{{ $n->created_at->format('d M Y') }}</div>
                                    <div class="cell-sub">{{ $n->created_at->diffForHumans() }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; color: var(--text-dim); padding: 40px;">
                                    No notifications have been dispatched yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($notifications->hasPages())
                <div class="pagination-wrap">
                    <div class="pag-info">Showing {{ $notifications->firstItem() }}-{{ $notifications->lastItem() }} of {{ $notifications->total() }} log entries</div>
                    <div class="pag-links">
                        {{ $notifications->links() }}
                    </div>
                </div>
            @endif
        </div>

    </div>
@endsection

@section('scripts')
<script>
    function toggleUserDropdown(val) {
        const wrap = document.getElementById('specificUserWrap');
        if (val === 'specific') {
            wrap.style.display = 'flex';
        } else {
            wrap.style.display = 'none';
        }
    }
</script>
@endsection
