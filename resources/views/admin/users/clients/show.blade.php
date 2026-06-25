@extends('admin.layouts.app')

@section('title', $client->company_name)
@section('page-title', $client->company_name)

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <span>/</span>
    <a href="{{ route('admin.clients.index') }}">Clients</a>
    <span>/</span>
    <span>{{ $client->company_name }}</span>
@endsection

@section('head')
<style>
.logo-thumb {
    width: 72px; height: 72px; object-fit: contain;
    background: var(--in-bg); border-radius: 16px;
    border: 1px solid var(--bdr); padding: 8px; flex-shrink: 0;
}
.expiry-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 10px; border-radius: 100px; font-size: .72rem; font-weight: 600;
}
.expiry-ok      { background: rgba(34,197,94,.12); color: #4ade80; }
.expiry-soon    { background: rgba(234,179,8,.12);  color: #fbbf24; }
.expiry-expired { background: rgba(220,38,38,.15);  color: #f87171; }

.att-list { display: flex; flex-direction: column; gap: 8px; margin-top: 4px; }
.att-item {
    display: flex; align-items: center; gap: 12px;
    padding: 11px 14px; background: var(--in-bg);
    border-radius: 10px; border: 1px solid var(--bdr); transition: border-color .15s;
}
.att-item:hover { border-color: rgba(220,38,38,.2); }
.att-icon { font-size: 1.3rem; flex-shrink: 0; }
.att-info { flex: 1; min-width: 0; }
.att-label { font-size: .87rem; font-weight: 600; color: var(--text); }
.att-meta  { font-size: .73rem; color: var(--text-dim); margin-top: 2px; }
.att-link  { font-size: .8rem; color: var(--red); text-decoration: none; font-weight: 600; white-space: nowrap; }
.att-link:hover { text-decoration: underline; }

.section-card { background: var(--card); border: 1px solid var(--bdr); border-radius: 14px; overflow: hidden; margin-bottom: 16px; backdrop-filter: blur(8px); }
.section-card-hd { padding: 14px 20px; border-bottom: 1px solid var(--bdr); display: flex; align-items: center; justify-content: space-between; }
.section-card-title { font-size: .72rem; font-weight: 700; color: var(--text-dim); letter-spacing: .09em; text-transform: uppercase; }
.section-card-body { padding: 18px 20px; }

/* ── Inline employee action buttons ── */
.btn-warn-sm   { padding:4px 10px;border-radius:5px;font-size:.74rem;font-weight:600;cursor:pointer;background:rgba(234,179,8,.1);color:#fbbf24;border:1px solid rgba(234,179,8,.2);transition:background .15s; }
.btn-warn-sm:hover   { background:rgba(234,179,8,.22); }
.btn-ok-sm     { padding:4px 10px;border-radius:5px;font-size:.74rem;font-weight:600;cursor:pointer;background:rgba(34,197,94,.1);color:#4ade80;border:1px solid rgba(34,197,94,.2);transition:background .15s; }
.btn-ok-sm:hover     { background:rgba(34,197,94,.2); }
.btn-danger-sm { padding:4px 10px;border-radius:5px;font-size:.74rem;font-weight:600;cursor:pointer;background:rgba(220,38,38,.1);color:#f87171;border:1px solid rgba(220,38,38,.2);transition:background .15s; }
.btn-danger-sm:hover { background:rgba(220,38,38,.2); }
</style>
@endsection

@section('content')

{{-- ── Profile Header ── --}}
<div class="profile-hd">

    @if($client->logo_path)
        <img src="{{ Storage::disk('public')->url($client->logo_path) }}" alt="Logo" class="logo-thumb">
    @else
        <div class="profile-avatar" style="background:linear-gradient(135deg,var(--red-dark),var(--red));">
            {{ strtoupper(substr($client->company_name, 0, 2)) }}
        </div>
    @endif

    <div style="flex:1;min-width:180px;">
        <h2 class="profile-name">{{ $client->company_name }}</h2>
        @if($client->company_name_ar)
            <div style="font-size:.85rem;color:var(--text-sub);margin-top:4px;" dir="rtl">{{ $client->company_name_ar }}</div>
        @endif
        <div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:10px;align-items:center;">
            @if($client->status === 'active')        <span class="badge-active">Active</span>
            @elseif($client->status === 'suspended') <span class="badge-suspended">Suspended</span>
            @else                                    <span class="badge-pv">Pending Verification</span>
            @endif

            @if($client->masterUser?->status === 'active')        <span class="badge-info">User Active</span>
            @elseif($client->masterUser?->status === 'suspended') <span class="badge-suspended">User Suspended</span>
            @else                                                  <span class="badge-pending">User Pending</span>
            @endif

            @if($client->expiry_date)
                @php
                    $diff  = now()->startOfDay()->diffInDays($client->expiry_date, false);
                    $ecls  = $diff < 0 ? 'expiry-expired' : ($diff <= 30 ? 'expiry-soon' : 'expiry-ok');
                    $elbl  = $diff < 0 ? 'Expired '.$client->expiry_date->format('d M Y')
                                       : ($diff === 0 ? 'Expires today'
                                                      : 'Expires '.$client->expiry_date->format('d M Y'));
                @endphp
                <span class="expiry-badge {{ $ecls }}">⏱ {{ $elbl }}</span>
            @endif
        </div>
    </div>

    <div class="profile-actions">
        <a href="{{ route('admin.clients.edit', $client) }}" class="btn-primary">Edit Client</a>
        <form method="POST" action="{{ route('admin.clients.resend-invitation', $client) }}" style="display:inline;">
            @csrf
            <button type="submit" class="btn-secondary" title="Resend invitation email">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Resend Invitation
            </button>
        </form>
        <button class="btn-danger"
            onclick="confirmDelete('{{ route('admin.clients.destroy', $client) }}','{{ addslashes($client->company_name) }}')">
            Delete
        </button>
    </div>
</div>

{{-- ── Info Grid ── --}}
<div class="info-grid">

    {{-- Master Account --}}
    <div class="info-card">
        <div class="info-card-title">Master Account</div>
        <div class="info-rows">
            <div class="info-row">
                <span class="info-row-key">Full Name</span>
                <span class="info-row-val">{{ $client->masterUser->name ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-row-key">Email</span>
                <span class="info-row-val" style="word-break:break-all;">{{ $client->masterUser->email ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-row-key">Phone</span>
                <span class="info-row-val">
                    @if($client->masterUser?->phone)
                        <span style="color:var(--text-dim);font-size:.8rem;margin-right:4px;">{{ $client->masterUser->phone_country_code ?? '' }}</span>{{ $client->masterUser->phone }}
                    @else —
                    @endif
                </span>
            </div>
            <div class="info-row">
                <span class="info-row-key">Account Status</span>
                <span class="info-row-val">
                    @if($client->masterUser?->status === 'active')        <span class="badge-active">Active</span>
                    @elseif($client->masterUser?->status === 'suspended') <span class="badge-suspended">Suspended</span>
                    @else <span class="badge-pending">Pending</span>
                    @endif
                </span>
            </div>
            <div class="info-row">
                <span class="info-row-key">Member Since</span>
                <span class="info-row-val">{{ $client->masterUser?->created_at?->format('d M Y') ?? '—' }}</span>
            </div>
            <div class="info-row" style="align-items:center;">
                <span class="info-row-key">Notifications</span>
                <span class="info-row-val" style="display:flex;align-items:center;gap:10px;">
                    <span id="admin-notif-label" style="font-size:.82rem;color:var(--text-dim);">
                        {{ $client->masterUser?->notifications_enabled ? 'Enabled' : 'Disabled' }}
                    </span>
                    @if($client->masterUser)
                    <label style="display:flex;align-items:center;cursor:pointer;" title="Toggle client notifications">
                        <input type="checkbox" id="admin-notif-toggle" style="display:none;" {{ $client->masterUser->notifications_enabled ? 'checked' : '' }}>
                        <div id="admin-notif-track" style="
                            width:40px;height:22px;border-radius:11px;
                            background:{{ $client->masterUser->notifications_enabled ? 'var(--red,#dc2626)' : '#4b5563' }};
                            position:relative;transition:background .2s;flex-shrink:0;
                        ">
                            <div id="admin-notif-thumb" style="
                                width:16px;height:16px;border-radius:50%;background:#fff;
                                position:absolute;top:3px;
                                left:{{ $client->masterUser->notifications_enabled ? '21px' : '3px' }};
                                transition:left .2s;box-shadow:0 1px 3px rgba(0,0,0,.3);
                            "></div>
                        </div>
                    </label>
                    @endif
                </span>
            </div>
        </div>
    </div>

    {{-- Company Details --}}
    <div class="info-card">
        <div class="info-card-title">Company Details</div>
        <div class="info-rows">
            <div class="info-row">
                <span class="info-row-key">CR Number</span>
                <span class="info-row-val">{{ $client->commercial_register_number ?: '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-row-key">VAT Number</span>
                <span class="info-row-val">{{ $client->vat_number ?: '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-row-key">Company Email</span>
                <span class="info-row-val" style="word-break:break-all;">{{ $client->email ?: '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-row-key">Client Status</span>
                <span class="info-row-val">
                    @if($client->status === 'active')        <span class="badge-active">Active</span>
                    @elseif($client->status === 'suspended') <span class="badge-suspended">Suspended</span>
                    @else                                    <span class="badge-pv">Pending Verification</span>
                    @endif
                </span>
            </div>
            <div class="info-row">
                <span class="info-row-key">Profile Created</span>
                <span class="info-row-val">{{ $client->created_at?->format('d M Y') ?? '—' }}</span>
            </div>
        </div>
    </div>

    {{-- Financial --}}
    <div class="info-card">
        <div class="info-card-title">Financial</div>
        <div class="info-rows">
            <div class="info-row">
                <span class="info-row-key">Credit Limit</span>
                <span class="info-row-val" style="font-weight:700;">
                    {{ number_format($client->credit_limit, 2) }}
                    <span style="color:var(--red-lt);font-size:.78rem;margin-left:3px;">JD</span>
                </span>
            </div>
            <div class="info-row">
                <span class="info-row-key">Balance</span>
                <span class="info-row-val" style="font-weight:700;">
                    {{ number_format($client->balance, 2) }}
                    <span style="color:var(--red-lt);font-size:.78rem;margin-left:3px;">JD</span>
                </span>
            </div>
            <div class="info-row">
                <span class="info-row-key">Expiry Date</span>
                <span class="info-row-val">
                    @if($client->expiry_date)
                        {{ $client->expiry_date->format('d M Y') }}
                        @if(isset($ecls))<span class="expiry-badge {{ $ecls }}" style="margin-left:6px;font-size:.68rem;">{{ $elbl }}</span>@endif
                    @else —
                    @endif
                </span>
            </div>
        </div>
    </div>

    {{-- Address --}}
    <div class="info-card">
        <div class="info-card-title">Address</div>
        <div class="info-rows">
            <div class="info-row">
                <span class="info-row-key">Address Line</span>
                <span class="info-row-val">{{ $client->address_line1 ?: '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-row-key">Governorate</span>
                <span class="info-row-val">{{ $client->city?->name ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-row-key">Area / District</span>
                <span class="info-row-val">{{ $client->area?->name ?? '—' }}</span>
            </div>
        </div>
    </div>

</div>

{{-- ── Banking Details ── --}}
@if($client->bankDetail)
<div class="section-card">
    <div class="section-card-hd">
        <span class="section-card-title">Banking Details</span>
        <a href="{{ route('admin.clients.edit', $client) }}" style="font-size:.78rem;color:var(--red);text-decoration:none;">Edit</a>
    </div>
    <div class="section-card-body">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0;">
            @php $bd = $client->bankDetail; @endphp

            @if($bd->bank_name)
            <div class="info-row" style="grid-column:1/-1;">
                <span class="info-row-key">Bank Name</span>
                <span class="info-row-val">{{ $bd->bank_name }}</span>
            </div>
            @endif

            @if($bd->account_name)
            <div class="info-row" style="grid-column:1/-1;">
                <span class="info-row-key">Account Holder</span>
                <span class="info-row-val">{{ $bd->account_name }}</span>
            </div>
            @endif

            @if($bd->iban)
            <div class="info-row" style="grid-column:1/-1;">
                <span class="info-row-key">IBAN</span>
                <span class="info-row-val" style="font-family:monospace;letter-spacing:.04em;word-break:break-all;">{{ $bd->iban }}</span>
            </div>
            @endif

            @if($bd->swift_code)
            <div class="info-row">
                <span class="info-row-key">SWIFT / BIC</span>
                <span class="info-row-val" style="font-family:monospace;">{{ $bd->swift_code }}</span>
            </div>
            @endif

            @if($bd->account_number)
            <div class="info-row">
                <span class="info-row-key">Account Number</span>
                <span class="info-row-val" style="font-family:monospace;">{{ $bd->account_number }}</span>
            </div>
            @endif

            @if($bd->cliq_id)
            <div class="info-row" style="grid-column:1/-1;">
                <span class="info-row-key">CliQ ID</span>
                <span class="info-row-val">
                    {{ $bd->cliq_id }}
                    @if($bd->cliq_alias_type)
                        <span style="font-size:.74rem;color:var(--text-dim);margin-left:6px;">({{ $bd->cliq_alias_type === 'alias' ? 'Alias' : 'Phone' }})</span>
                    @endif
                </span>
            </div>
            @endif

            @if($bd->notes)
            <div class="info-row" style="grid-column:1/-1;">
                <span class="info-row-key">Notes</span>
                <span class="info-row-val" style="white-space:pre-line;">{{ $bd->notes }}</span>
            </div>
            @endif
        </div>
    </div>
</div>
@endif

{{-- ── Attachments ── --}}
@if($client->attachments->count())
<div class="section-card">
    <div class="section-card-hd">
        <span class="section-card-title">Attachments</span>
        <span style="font-size:.75rem;color:var(--text-dim);">{{ $client->attachments->count() }} file{{ $client->attachments->count() !== 1 ? 's' : '' }}</span>
    </div>
    <div class="section-card-body">
        <div class="att-list">
            @foreach($client->attachments as $att)
            @php
                $mime = $att->mime_type ?? '';
                $icon = str_contains($mime, 'image') ? '🖼️' : (str_contains($mime, 'pdf') ? '📄' : '📎');
            @endphp
            <div class="att-item">
                <div class="att-icon">{{ $icon }}</div>
                <div class="att-info">
                    <div class="att-label">{{ $att->label }}</div>
                    <div class="att-meta">{{ $att->original_filename }} · {{ $att->formatted_size }}</div>
                </div>
                <a href="{{ $att->url }}" target="_blank" class="att-link">↗ View</a>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- ── Employees ── --}}
<div class="section-card">
    <div class="section-card-hd">
        <div style="display:flex;align-items:center;gap:10px;">
            <span class="section-card-title">Employees</span>
            @if($client->employees->count())
                <span style="font-size:.75rem;color:var(--text-dim);">{{ $client->employees->count() }} member{{ $client->employees->count() !== 1 ? 's' : '' }}</span>
            @endif
        </div>
        <a href="{{ route('admin.clients.employees.create', $client) }}" class="btn-primary" style="padding:6px 14px;font-size:.82rem;">
            + Add Employee
        </a>
    </div>

    @if($client->employees->count())
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Job Title</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th style="width:150px;text-align:center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($client->employees as $emp)
                <tr>
                    <td><span class="cell-main">{{ $emp->user->name ?? '—' }}</span></td>
                    <td><span style="font-size:.82rem;color:var(--text-dim);word-break:break-all;">{{ $emp->user->email ?? '—' }}</span></td>
                    <td>{{ $emp->job_title ?: '—' }}</td>
                    <td>
                        @if($emp->status === 'active') <span class="badge-active">Active</span>
                        @else <span class="badge-suspended">Suspended</span>
                        @endif
                    </td>
                    <td><span style="font-size:.82rem;color:var(--text-dim);">{{ $emp->created_at?->format('d M Y') ?? '—' }}</span></td>
                    <td>
                        <div style="display:flex;gap:6px;align-items:center;justify-content:center;">
                            <form method="POST" action="{{ route('admin.clients.employees.status', [$client, $emp]) }}" style="display:inline;">
                                @csrf @method('PATCH')
                                <button type="submit" class="{{ $emp->status === 'active' ? 'btn-warn-sm' : 'btn-ok-sm' }}">
                                    {{ $emp->status === 'active' ? 'Suspend' : 'Activate' }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.clients.employees.destroy', [$client, $emp]) }}" style="display:inline;"
                                  onsubmit="return confirm('Remove {{ addslashes($emp->user->name ?? 'this employee') }} from this company?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-danger-sm">Remove</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div style="padding:32px;text-align:center;color:var(--text-dim);font-size:.88rem;">
        No employees yet. Click <strong style="color:var(--text-sub);">+ Add Employee</strong> to add the first team member.
    </div>
    @endif
</div>

@section('scripts')
<script>
(function () {
    var toggle = document.getElementById('admin-notif-toggle');
    if (!toggle) return;

    var track = document.getElementById('admin-notif-track');
    var thumb = document.getElementById('admin-notif-thumb');
    var label = document.getElementById('admin-notif-label');

    track.parentElement.addEventListener('click', function () {
        var enabled = !toggle.checked;
        toggle.checked = enabled;

        track.style.background = enabled ? 'var(--red,#dc2626)' : '#4b5563';
        thumb.style.left       = enabled ? '21px' : '3px';
        label.textContent      = enabled ? 'Enabled' : 'Disabled';

        fetch('{{ route('admin.clients.toggle-notifications', $client) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
        }).catch(function () {
            toggle.checked = !enabled;
            track.style.background = !enabled ? 'var(--red,#dc2626)' : '#4b5563';
            thumb.style.left       = !enabled ? '21px' : '3px';
            label.textContent      = !enabled ? 'Enabled' : 'Disabled';
        });
    });
})();
</script>
@endsection

@endsection
