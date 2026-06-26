@extends('admin.layouts.app')

@section('title', 'Drivers')

@section('page-title', 'Drivers')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <span>/</span>
    <span>Drivers</span>
@endsection

@section('content')
<div class="mini-stats">
    <div class="mini-stat">
        <div class="ms-val">{{ \App\Models\DriverProfile::count() }}</div>
        <div class="ms-lbl">Total Drivers</div>
    </div>
    <div class="mini-stat">
        <div class="ms-val">{{ \App\Models\DriverProfile::where('is_available', true)->count() }}</div>
        <div class="ms-lbl">Available</div>
    </div>
    <div class="mini-stat">
        <div class="ms-val">{{ \App\Models\DriverProfile::where('is_available', false)->count() }}</div>
        <div class="ms-lbl">Busy</div>
    </div>
    <div class="mini-stat">
        <div class="ms-val">{{ \App\Models\DriverProfile::whereHas('user', fn($q) => $q->where('status','suspended'))->count() }}</div>
        <div class="ms-lbl">Suspended</div>
    </div>
</div>

<div class="filter-bar" style="display:flex; justify-content:space-between; align-items:center; gap:16px;">
    <form method="GET" action="{{ route('admin.drivers.index') }}" class="filter-form" style="margin:0; flex:1; max-width:320px;" id="search-form">
        <input
            class="filter-search"
            type="text"
            name="search"
            id="search-input"
            value="{{ request('search') }}"
            placeholder="Search name, plate, national ID…"
            style="width:100%;"
        >
    </form>
    <div style="display:flex; gap:8px; align-items:center;">
        <a href="{{ route('admin.drivers.live-map') }}" class="btn-secondary">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Live Map
        </a>
        <a href="{{ route('admin.drivers.create') }}" class="btn-primary">+ Add Driver</a>
    </div>
</div>

@if($q->count())
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Driver</th>
                <th>Vehicle</th>
                <th>License</th>
                <th>License Expiry</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($q as $driver)
            <tr>
                <td>
                    <div class="cell-name">
                        <div class="cell-avatar">{{ strtoupper(substr($driver->user->name ?? '?', 0, 2)) }}</div>
                        <div>
                            <div class="cell-main">{{ $driver->user->name ?? '—' }}</div>
                            <div class="cell-sub">{{ $driver->user->email ?? '' }}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="cell-main">{{ $driver->vehicle_plate ?: '—' }}</div>
                    <div class="cell-sub">{{ trim(($driver->vehicle_model ?? '').' '.($driver->vehicle_color ?? '')) ?: '' }}</div>
                </td>
                <td>{{ $driver->license_number }}</td>
                <td>
                    @php
                        $expiry = \Carbon\Carbon::parse($driver->license_expiry_date);
                        $isExpired = $expiry->isPast();
                        $isSoon = !$isExpired && $expiry->diffInDays(now()) <= 30;
                    @endphp
                    <span style="color: {{ $isExpired ? 'var(--red-lt)' : ($isSoon ? 'var(--warning)' : 'inherit') }}">
                        {{ $expiry->format('d M Y') }}
                        @if($isExpired) <small>(Expired)</small>
                        @elseif($isSoon) <small>(Soon)</small>
                        @endif
                    </span>
                </td>

                <td>
                    <div class="act-btns">
                        <a href="{{ route('admin.drivers.show', $driver) }}" class="act-btn act-view" title="View">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </a>
                        <button class="act-btn" title="Bank Details" style="color:#60a5fa;"
                            onclick="showBankDetails('{{ route('admin.drivers.bank-details', $driver) }}', '{{ addslashes($driver->user->name ?? 'Driver') }}')">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        </button>
                        <a href="{{ route('admin.drivers.edit', $driver) }}" class="act-btn act-edit" title="Edit">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                        <button class="act-btn act-delete" title="Delete"
                            onclick="confirmDelete('{{ route('admin.drivers.destroy', $driver) }}','{{ addslashes($driver->user->name ?? 'this driver') }}')">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
{{ $q->links() }}
@else
<div class="empty-state">
    <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
    <p>No drivers found. <a href="{{ route('admin.drivers.create') }}">Add the first driver.</a></p>
</div>
@endif
{{-- Bank Details Modal --}}
<div id="bankModal" style="display:none;position:fixed;inset:0;z-index:1000;background:rgba(0,0,0,.6);backdrop-filter:blur(2px);align-items:center;justify-content:center;">
    <div style="background:var(--bg-2);border:1px solid var(--bdr);border-radius:14px;padding:28px;width:100%;max-width:520px;margin:16px;box-shadow:0 20px 60px rgba(0,0,0,.6);position:relative;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <div>
                <div style="font-size:.65rem;text-transform:uppercase;letter-spacing:.08em;color:var(--text-dim);margin-bottom:3px;">Bank Details</div>
                <div id="bankModalName" style="font-size:1rem;font-weight:700;color:var(--text);"></div>
            </div>
            <button onclick="closeBankModal()" style="background:none;border:none;color:var(--text-dim);cursor:pointer;font-size:1.2rem;padding:4px 8px;line-height:1;">✕</button>
        </div>
        <div id="bankModalBody">
            <div style="text-align:center;padding:24px 0;color:var(--text-dim);font-size:.85rem;">Loading…</div>
        </div>
        <div style="margin-top:18px;text-align:right;">
            <button onclick="closeBankModal()" class="btn-secondary" style="font-size:.82rem;padding:6px 16px;">Close</button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Bank details modal
    function showBankDetails(url, name) {
        document.getElementById('bankModalName').textContent = name;
        document.getElementById('bankModalBody').innerHTML = '<div style="text-align:center;padding:24px 0;color:var(--text-dim);font-size:.85rem;">Loading…</div>';
        var modal = document.getElementById('bankModal');
        modal.style.display = 'flex';

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) { return r.json(); })
            .then(function(d) {
                var rows = [
                    ['Bank Name',       d && d.bank_name      ? d.bank_name      : '—'],
                    ['Account Name',    d && d.account_name   ? d.account_name   : '—'],
                    ['Account Number',  d && d.account_number ? d.account_number : '—', true],
                    ['IBAN',            d && d.iban            ? d.iban           : '—', true],
                    ['SWIFT / BIC',     d && d.swift_code     ? d.swift_code     : '—', true],
                    ['CliQ ID',         d && d.cliq_id        ? d.cliq_id + (d.cliq_alias_type ? ' (' + (d.cliq_alias_type.charAt(0).toUpperCase() + d.cliq_alias_type.slice(1)) + ')' : '') : '—'],
                ];

                var hasAny = d && (d.bank_name || d.account_name || d.account_number || d.iban || d.swift_code || d.cliq_id);
                if (!hasAny) {
                    document.getElementById('bankModalBody').innerHTML =
                        '<div style="text-align:center;padding:20px 0;color:var(--text-dim);font-size:.82rem;">No bank details have been added for this driver.</div>';
                    return;
                }

                var html = '<div style="display:grid;gap:0;">';
                rows.forEach(function(row) {
                    html += '<div style="display:flex;justify-content:space-between;align-items:center;padding:9px 0;border-bottom:1px solid rgba(255,255,255,.04);">' +
                        '<span style="font-size:.75rem;color:var(--text-dim);text-transform:uppercase;letter-spacing:.05em;">' + row[0] + '</span>' +
                        '<span style="font-size:.88rem;color:var(--text);font-weight:500;' + (row[2] ? 'font-family:monospace;' : '') + '">' + row[1] + '</span>' +
                        '</div>';
                });
                html += '</div>';

                if (d && d.notes) {
                    html += '<div style="margin-top:14px;padding:10px 12px;background:var(--in-bg);border-radius:8px;">' +
                        '<div style="font-size:.68rem;text-transform:uppercase;color:var(--text-dim);margin-bottom:4px;">Notes</div>' +
                        '<div style="font-size:.82rem;color:var(--text-sub);">' + d.notes + '</div>' +
                        '</div>';
                }

                document.getElementById('bankModalBody').innerHTML = html;
            })
            .catch(function() {
                document.getElementById('bankModalBody').innerHTML =
                    '<div style="text-align:center;padding:20px 0;color:#f87171;font-size:.82rem;">Failed to load bank details.</div>';
            });
    }

    function closeBankModal() {
        document.getElementById('bankModal').style.display = 'none';
    }

    document.getElementById('bankModal').addEventListener('click', function(e) {
        if (e.target === this) closeBankModal();
    });

    // Real-time search script
    var searchInput = document.getElementById('search-input');
    if (searchInput) {
        var timeout = null;
        searchInput.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                document.getElementById('search-form').submit();
            }, 500);
        });
        // Keep focus at end of input
        searchInput.focus();
        var val = searchInput.value;
        searchInput.value = '';
        searchInput.value = val;
    }
</script>
@endsection
