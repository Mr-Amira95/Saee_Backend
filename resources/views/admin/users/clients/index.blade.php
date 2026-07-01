@extends('admin.layouts.app')

@section('title', 'Clients')

@section('page-title', 'Clients')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <span>/</span>
    <span>Clients</span>
@endsection

@section('head')
<style>
.bank-modal-overlay {
    position: fixed;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(10px);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: fadeIn 0.25s ease-out;
}
.bank-modal-card {
    background: rgba(25, 25, 30, 0.85);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 16px;
    width: 95%;
    max-width: 500px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.5);
    animation: slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    overflow: hidden;
    color: var(--text);
}
.bank-modal-hd {
    padding: 16px 20px;
    border-bottom: 1px solid rgba(255,255,255,0.08);
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.bank-modal-title {
    margin: 0;
    font-size: 1rem;
    font-weight: 700;
    color: var(--text);
}
.bank-modal-close {
    background: none;
    border: none;
    color: var(--text-dim);
    font-size: 1.5rem;
    cursor: pointer;
    line-height: 1;
    padding: 0;
    transition: color 0.15s;
}
.bank-modal-close:hover {
    color: var(--red);
}
.bank-modal-body {
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 14px;
}
.bank-detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 10px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.04);
}
.bank-detail-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}
.bank-detail-label {
    font-size: 0.82rem;
    color: var(--text-dim);
    font-weight: 500;
}
.bank-detail-val-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
    max-width: 70%;
}
.bank-detail-val {
    font-size: 0.88rem;
    color: var(--text);
    font-weight: 600;
    text-align: right;
    word-break: break-all;
}
.font-mono {
    font-family: var(--font-mono, monospace);
    letter-spacing: 0.02em;
}
.btn-copy {
    padding: 3px 8px;
    border-radius: 4px;
    background: rgba(220, 38, 38, 0.1);
    color: var(--red);
    border: 1px solid rgba(220, 38, 38, 0.2);
    font-size: 0.72rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s, border-color 0.15s;
}
.btn-copy:hover {
    background: rgba(220, 38, 38, 0.2);
    border-color: rgba(220, 38, 38, 0.3);
}
.act-btns .act-bank:hover {
    background: rgba(34, 197, 94, 0.12);
    color: #4ade80;
    border-color: rgba(34, 197, 94, 0.25);
}
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
@keyframes slideUp {
    from { transform: translateY(20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

/* ─── Light Mode Overrides for Bank Details Modal ─── */
html.light-theme .bank-modal-card {
    background: #ffffff !important;
    border-color: #cbd5e1 !important;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1) !important;
    color: #0f172a !important;
}
html.light-theme .bank-modal-hd {
    border-bottom-color: #e2e8f0 !important;
}
html.light-theme .bank-modal-title {
    color: #0f172a !important;
}
html.light-theme .bank-detail-item {
    border-bottom-color: #f1f5f9 !important;
}
html.light-theme .bank-detail-label {
    color: #64748b !important;
}
html.light-theme .bank-detail-val {
    color: #0f172a !important;
}
html.light-theme #modal-notes {
    background: #f8fafc !important;
    border: 1px solid #e2e8f0 !important;
    color: #334155 !important;
}
</style>
@endsection

@section('content')
{{-- Stats --}}
<div class="mini-stats">
    <div class="mini-stat">
        <div class="ms-val">{{ \App\Models\ClientProfile::count() }}</div>
        <div class="ms-lbl">Total Clients</div>
    </div>
    <div class="mini-stat">
        <div class="ms-val">{{ \App\Models\ClientProfile::where('status','active')->count() }}</div>
        <div class="ms-lbl">Active</div>
    </div>
    <div class="mini-stat">
        <div class="ms-val">{{ \App\Models\ClientProfile::where('status','pending_verification')->count() }}</div>
        <div class="ms-lbl">Pending</div>
    </div>
    <div class="mini-stat">
        <div class="ms-val">{{ \App\Models\ClientProfile::where('status','suspended')->count() }}</div>
        <div class="ms-lbl">Suspended</div>
    </div>
</div>

{{-- Filter bar --}}
<div class="filter-bar" style="display:flex; justify-content:space-between; align-items:center; gap:16px;">
    <form method="GET" action="{{ route('admin.clients.index') }}" class="filter-form" style="margin:0; flex:1; max-width:320px;" id="search-form">
        <input
            class="filter-search"
            type="text"
            name="search"
            id="search-input"
            value="{{ request('search') }}"
            placeholder="Search clients..."
            style="width:100%;"
        >
    </form>
    <a href="{{ route('admin.clients.create') }}" class="btn-primary">+ Add Client</a>
</div>

{{-- Table --}}
@if($q->count())
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Company</th>
                <th>Contact Information</th>
                <th>Address</th>
                <th>Financials</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($q as $client)
            <tr>
                <td>
                    <div class="cell-name">
                        @if($client->logo_path)
                            <img src="{{ Storage::disk('public')->url($client->logo_path) }}" alt="Logo" style="width:36px; height:36px; object-fit:contain; border-radius:8px; background:var(--in-bg,#1a1a24); padding:2px; border:1px solid var(--bdr,rgba(255,255,255,0.08)); flex-shrink:0;">
                        @else
                            <div class="cell-avatar">{{ strtoupper(substr($client->company_name, 0, 2)) }}</div>
                        @endif
                        <div>
                            <div class="cell-main">
                                @if(app()->getLocale() === 'ar')
                                    {{ $client->company_name_ar ?: $client->company_name }}
                                @else
                                    {{ $client->company_name }}
                                @endif
                            </div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="cell-main" style="word-break:break-all;">{{ $client->masterUser->email ?? '—' }}</div>
                    <div class="cell-sub" style="margin-top:2px;">
                        @if($client->company_phone)
                            {{ $client->company_phone_country_code ? ($client->company_phone_country_code . ' ') : '' }}{{ $client->company_phone }}
                        @else
                            —
                        @endif
                    </div>
                </td>
                <td>
                    <div class="cell-main">
                        @if(app()->getLocale() === 'ar')
                            {{ $client->city?->name_ar ?: ($client->city?->name ?? '—') }}
                        @else
                            {{ $client->city?->name ?? '—' }}
                        @endif
                    </div>
                    <div class="cell-sub" style="margin-top:2px;">
                        @if(app()->getLocale() === 'ar')
                            {{ $client->area?->name_ar ?: ($client->area?->name ?? '—') }}
                        @else
                            {{ $client->area?->name ?? '—' }}
                        @endif
                    </div>
                </td>
                <td>
                    <div class="cell-main" style="font-weight:600;">
                        {{ number_format($client->credit_limit, 2) }} <span style="color:var(--red-lt,#ef4444);font-size:.75rem;">JD</span>
                    </div>
                    <div class="cell-sub" style="margin-top:2px;font-size:.76rem;">
                        Balance: {{ number_format($client->balance, 2) }} JD
                    </div>
                </td>
                <td>
                    <div class="act-btns">
                        @php
                            $bankData = $client->bankDetail ? [
                                'bank_name' => $client->bankDetail->bank_name ?: '',
                                'account_name' => $client->bankDetail->account_name ?: '',
                                'iban' => $client->bankDetail->iban ?: '',
                                'swift_code' => $client->bankDetail->swift_code ?: '',
                                'account_number' => $client->bankDetail->account_number ?: '',
                                'cliq_id' => $client->bankDetail->cliq_id ?: '',
                                'cliq_alias_type' => $client->bankDetail->cliq_alias_type ?: '',
                                'notes' => $client->bankDetail->notes ?: ''
                            ] : null;
                        @endphp
                        @if($bankData)
                            <button
                                class="act-btn act-bank"
                                title="Bank Details"
                                style="cursor:pointer;"
                                onclick="openBankModal({{ json_encode($bankData) }}, '{{ addslashes(app()->getLocale() === 'ar' ? ($client->company_name_ar ?: $client->company_name) : $client->company_name) }}')"
                            >
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                                </svg>
                            </button>
                        @else
                            <button
                                class="act-btn act-bank"
                                title="No Bank Details"
                                style="opacity: 0.3; cursor: not-allowed;"
                                disabled
                            >
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                                </svg>
                            </button>
                        @endif
                        <a href="{{ route('admin.clients.show', $client) }}" class="act-btn act-view" title="View">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </a>
                        <button
                            class="act-btn act-reset-pw"
                            title="Reset Password"
                            onclick="openResetPasswordModal('{{ route('admin.clients.reset-password', $client) }}', '{{ addslashes($client->masterUser->name ?? $client->company_name) }}')"
                        >
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </button>
                        <a href="{{ route('admin.clients.edit', $client) }}" class="act-btn act-edit" title="Edit">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                        <button
                            class="act-btn act-delete"
                            title="Delete"
                            onclick="confirmDelete('{{ route('admin.clients.destroy', $client) }}','{{ addslashes($client->company_name) }}')"
                        >
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
    <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
    <p>No clients found. <a href="{{ route('admin.clients.create') }}">Add the first client.</a></p>
</div>
@endif

{{-- Bank Details Modal --}}
<div id="bank-modal" class="bank-modal-overlay" style="display:none;" onclick="closeBankModal(event)">
    <div class="bank-modal-card" onclick="event.stopPropagation()">
        <div class="bank-modal-hd">
            <h3 class="bank-modal-title">Bank Details - <span id="modal-company-name"></span></h3>
            <button class="bank-modal-close" onclick="closeBankModal(null)">&times;</button>
        </div>
        <div class="bank-modal-body">
            <div class="bank-detail-item">
                <span class="bank-detail-label">Bank Name</span>
                <div class="bank-detail-val-wrap">
                    <span id="modal-bank-name" class="bank-detail-val">—</span>
                    <button class="btn-copy" onclick="copyText('modal-bank-name')">Copy</button>
                </div>
            </div>
            <div class="bank-detail-item">
                <span class="bank-detail-label">Account Holder</span>
                <div class="bank-detail-val-wrap">
                    <span id="modal-account-name" class="bank-detail-val">—</span>
                    <button class="btn-copy" onclick="copyText('modal-account-name')">Copy</button>
                </div>
            </div>
            <div class="bank-detail-item">
                <span class="bank-detail-label">IBAN</span>
                <div class="bank-detail-val-wrap">
                    <span id="modal-iban" class="bank-detail-val font-mono">—</span>
                    <button class="btn-copy" onclick="copyText('modal-iban')">Copy</button>
                </div>
            </div>
            <div class="bank-detail-item">
                <span class="bank-detail-label">SWIFT / BIC</span>
                <div class="bank-detail-val-wrap">
                    <span id="modal-swift-code" class="bank-detail-val font-mono">—</span>
                    <button class="btn-copy" onclick="copyText('modal-swift-code')">Copy</button>
                </div>
            </div>
            <div class="bank-detail-item">
                <span class="bank-detail-label">Account Number</span>
                <div class="bank-detail-val-wrap">
                    <span id="modal-account-number" class="bank-detail-val font-mono">—</span>
                    <button class="btn-copy" onclick="copyText('modal-account-number')">Copy</button>
                </div>
            </div>
            <div class="bank-detail-item" id="modal-cliq-row">
                <span class="bank-detail-label">CliQ ID <span id="modal-cliq-type" style="font-size:0.75rem;opacity:0.7;"></span></span>
                <div class="bank-detail-val-wrap">
                    <span id="modal-cliq-id" class="bank-detail-val">—</span>
                    <button class="btn-copy" onclick="copyText('modal-cliq-id')">Copy</button>
                </div>
            </div>
            <div class="bank-detail-item" id="modal-notes-row" style="flex-direction: column; align-items: flex-start; gap: 6px;">
                <span class="bank-detail-label">Notes</span>
                <div id="modal-notes" class="bank-detail-notes" style="width: 100%; max-height: 80px; overflow-y: auto; background: rgba(255,255,255,0.05); padding: 8px 10px; border-radius: 6px; font-size: 0.82rem; white-space: pre-wrap;">—</div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function openBankModal(data, companyName) {
    if (!data) return;
    
    document.getElementById('modal-company-name').textContent = companyName;
    document.getElementById('modal-bank-name').textContent = data.bank_name || '—';
    document.getElementById('modal-account-name').textContent = data.account_name || '—';
    document.getElementById('modal-iban').textContent = data.iban || '—';
    document.getElementById('modal-swift-code').textContent = data.swift_code || '—';
    document.getElementById('modal-account-number').textContent = data.account_number || '—';
    
    var cliqId = data.cliq_id;
    if (cliqId) {
        document.getElementById('modal-cliq-row').style.display = 'flex';
        document.getElementById('modal-cliq-id').textContent = cliqId;
        var type = data.cliq_alias_type;
        document.getElementById('modal-cliq-type').textContent = type ? '(' + (type === 'alias' ? 'Alias' : 'Phone') + ')' : '';
    } else {
        document.getElementById('modal-cliq-row').style.display = 'none';
    }
    
    var notes = data.notes;
    if (notes) {
        document.getElementById('modal-notes-row').style.display = 'flex';
        document.getElementById('modal-notes').textContent = notes;
    } else {
        document.getElementById('modal-notes-row').style.display = 'none';
    }

    document.getElementById('bank-modal').style.display = 'flex';
}

function closeBankModal(event) {
    if (!event || event.target === document.getElementById('bank-modal')) {
        document.getElementById('bank-modal').style.display = 'none';
    }
}

function copyText(elementId) {
    var text = document.getElementById(elementId).textContent;
    if (text === '—') return;
    
    navigator.clipboard.writeText(text).then(function() {
        showSuccessToast('Copied to clipboard: ' + text);
    }).catch(function() {
        var el = document.createElement('textarea');
        el.value = text;
        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);
        showSuccessToast('Copied to clipboard: ' + text);
    });
}

function showSuccessToast(message) {
    var container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.style.position = 'fixed';
        container.style.bottom = '20px';
        container.style.right = '20px';
        container.style.zIndex = '9999';
        container.style.display = 'flex';
        container.style.flexDirection = 'column';
        container.style.gap = '10px';
        document.body.appendChild(container);
    }
    
    var toast = document.createElement('div');
    toast.style.background = 'rgba(25, 25, 30, 0.85)';
    toast.style.border = '1px solid rgba(34, 197, 94, 0.3)';
    toast.style.color = '#4ade80';
    toast.style.padding = '12px 20px';
    toast.style.borderRadius = '10px';
    toast.style.fontSize = '0.85rem';
    toast.style.fontWeight = '600';
    toast.style.boxShadow = '0 10px 30px rgba(0,0,0,0.3)';
    toast.style.backdropFilter = 'blur(8px)';
    toast.style.animation = 'slideUp 0.3s ease-out';
    toast.style.display = 'flex';
    toast.style.alignItems = 'center';
    toast.style.gap = '8px';
    toast.innerHTML = '✨ ' + message;
    
    container.appendChild(toast);
    
    setTimeout(function() {
        toast.style.animation = 'fadeOut 0.3s ease-out forwards';
        setTimeout(function() {
            toast.remove();
        }, 300);
    }, 2500);
}
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
