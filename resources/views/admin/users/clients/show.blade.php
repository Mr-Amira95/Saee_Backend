@extends('admin.layouts.app')

@section('title', app()->getLocale() === 'ar' ? ($client->company_name_ar ?: $client->company_name) : $client->company_name)
@section('page-title', app()->getLocale() === 'ar' ? ($client->company_name_ar ?: $client->company_name) : $client->company_name)

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
    <span>/</span>
    <a href="{{ route('admin.clients.index') }}">{{ __('Clients') }}</a>
    <span>/</span>
    <span>{{ app()->getLocale() === 'ar' ? ($client->company_name_ar ?: $client->company_name) : $client->company_name }}</span>
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
        <h2 class="profile-name">
            @if(app()->getLocale() === 'ar')
                {{ $client->company_name_ar ?: $client->company_name }}
            @else
                {{ $client->company_name }}
            @endif
        </h2>
        <div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:10px;align-items:center;">
            @if($client->status === 'active')        <span class="badge-active">{{ __('Active') }}</span>
            @elseif($client->status === 'suspended') <span class="badge-suspended">{{ __('Suspended') }}</span>
            @else                                    <span class="badge-pv">{{ __('Pending Verification') }}</span>
            @endif

            @if($client->expiry_date)
                @php
                    $diff  = now()->startOfDay()->diffInDays($client->expiry_date, false);
                    $ecls  = $diff < 0 ? 'expiry-expired' : ($diff <= 30 ? 'expiry-soon' : 'expiry-ok');
                    $elbl  = $diff < 0 ? __('Expired :date', ['date' => $client->expiry_date->format('d M Y')])
                                       : ($diff === 0 ? __('Expires today')
                                                      : __('Expires :date', ['date' => $client->expiry_date->format('d M Y')]));
                @endphp
                <span class="expiry-badge {{ $ecls }}">⏱ {{ $elbl }}</span>
            @endif
        </div>

        {{-- Row containing Shortcuts and Main Actions together --}}
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-top:14px; width:100%;">
            {{-- Shortcut Buttons --}}
            <div class="shortcut-buttons" style="display:flex;gap:8px;flex-wrap:wrap;">
                <a href="{{ route('admin.orders.index', ['client_profile_id' => $client->id]) }}" class="btn-secondary" style="font-size:.78rem;padding:6px 12px;display:inline-flex;align-items:center;gap:6px;">
                    📦 {{ __('Orders') }}
                </a>
                <a href="{{ route('admin.financials.invoices', ['client_id' => $client->id]) }}" class="btn-secondary" style="font-size:.78rem;padding:6px 12px;display:inline-flex;align-items:center;gap:6px;">
                    📄 {{ __('Invoices') }}
                </a>
                <a href="{{ route('admin.support.index', ['client_id' => $client->id]) }}" class="btn-secondary" style="font-size:.78rem;padding:6px 12px;display:inline-flex;align-items:center;gap:6px;">
                    🎫 {{ __('Support Tickets') }}
                </a>
                <a href="{{ route('admin.financials.payout-client', $client) }}" class="btn-secondary" style="font-size:.78rem;padding:6px 12px;display:inline-flex;align-items:center;gap:6px;">
                    💰 {{ __('Payout') }}
                </a>
            </div>

            {{-- Profile Actions (Edit, Status Toggle, Resend, Delete) --}}
            <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                @if(auth()->user()->hasAdminAction('clients.edit'))
                <a href="{{ route('admin.clients.edit', $client) }}" class="btn-primary" style="font-size:.78rem;padding:6px 12px;">{{ __('Edit Client') }}</a>
                @endif
                <form method="POST" action="{{ route('admin.clients.toggle-status', $client) }}" style="display:inline;">
                    @csrf
                    @method('PATCH')
                    @if($client->status === 'active')
                        <button type="submit" class="btn-secondary" title="{{ __('Suspend Client') }}" style="font-size:.78rem;padding:6px 12px;display:inline-flex;align-items:center;gap:4px;color:#fbbf24;border-color:rgba(234,179,8,.4);background:rgba(234,179,8,.1);">
                            ⏸ {{ __('Suspend Client') }}
                        </button>
                    @else
                        <button type="submit" class="btn-secondary" title="{{ __('Activate Client') }}" style="font-size:.78rem;padding:6px 12px;display:inline-flex;align-items:center;gap:4px;color:#4ade80;border-color:rgba(34,197,94,.4);background:rgba(34,197,94,.1);">
                            ▶ {{ __('Activate Client') }}
                        </button>
                    @endif
                </form>
                <form method="POST" action="{{ route('admin.clients.resend-invitation', $client) }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn-secondary" title="{{ __('Resend invitation email') }}" style="font-size:.78rem;padding:6px 12px;display:inline-flex;align-items:center;gap:4px;">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        {{ __('Resend Invitation') }}
                    </button>
                </form>
                @if(auth()->user()->hasAdminAction('clients.delete'))
                <button class="btn-danger" style="font-size:.78rem;padding:6px 12px;"
                    onclick="confirmDelete('{{ route('admin.clients.destroy', $client) }}','{{ addslashes($client->company_name) }}')">
                    {{ __('Delete') }}
                </button>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ── Info Grid ── --}}
<div class="info-grid">

    {{-- Master Account --}}
    <div class="info-card">
        <div class="info-card-title">{{ __('Master Account') }}</div>
        <div class="info-rows">
            <div class="info-row">
                <span class="info-row-key">{{ __('Full Name') }}</span>
                <span class="info-row-val">{{ $client->masterUser->name ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-row-key">{{ __('Username') }}</span>
                <span class="info-row-val">{{ $client->masterUser->username ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-row-key">{{ __('Email') }}</span>
                <span class="info-row-val" style="word-break:break-all;">{{ $client->masterUser->email ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-row-key">{{ __('Phone') }}</span>
                <span class="info-row-val">
                    @if($client->masterUser?->phone)
                        <span style="color:var(--text-dim);font-size:.8rem;margin-right:4px;">{{ $client->masterUser->phone_country_code ?? '' }}</span>{{ $client->masterUser->phone }}
                    @else —
                    @endif
                </span>
            </div>
            <div class="info-row">
                <span class="info-row-key">{{ __('OTP / Notification Channel') }}</span>
                <span class="info-row-val">{{ $client->masterUser?->otp_channel === 'email' ? __('Email') : __('WhatsApp') }}</span>
            </div>
            <div class="info-row">
                <span class="info-row-key">{{ __('Account Status') }}</span>
                <span class="info-row-val" style="display:flex; align-items:center; gap:8px;">
                    @if($client->masterUser?->status === 'active')        <span class="badge-active">{{ __('Active') }}</span>
                    @elseif($client->masterUser?->status === 'suspended') <span class="badge-suspended">{{ __('Suspended') }}</span>
                    @else <span class="badge-pending">{{ __('Pending') }}</span>
                    @endif

                    <form method="POST" action="{{ route('admin.clients.toggle-status', $client) }}" style="display:inline;">
                        @csrf
                        @method('PATCH')
                        @if($client->masterUser?->status === 'active')
                            <button type="submit" class="btn-warn-sm" style="padding: 2px 6px; font-size: 0.7rem;">{{ __('Suspend') }}</button>
                        @else
                            <button type="submit" class="btn-ok-sm" style="padding: 2px 6px; font-size: 0.7rem;">{{ __('Activate') }}</button>
                        @endif
                    </form>
                </span>
            </div>
            <div class="info-row">
                <span class="info-row-key">{{ __('Member Since') }}</span>
                <span class="info-row-val">{{ $client->masterUser?->created_at?->format('d M Y') ?? '—' }}</span>
            </div>
        </div>
    </div>

    {{-- Company Details --}}
    <div class="info-card">
        <div class="info-card-title">{{ __('Company Details') }}</div>
        <div class="info-rows">
            <div class="info-row">
                <span class="info-row-key">{{ __('CR Number') }}</span>
                <span class="info-row-val">{{ $client->commercial_register_number ?: '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-row-key">{{ __('VAT Number') }}</span>
                <span class="info-row-val">{{ $client->vat_number ?: '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-row-key">{{ __('Company Email') }}</span>
                <span class="info-row-val" style="word-break:break-all;">{{ $client->email ?: '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-row-key">{{ __('Company Phone') }}</span>
                <span class="info-row-val">
                    @if($client->company_phone)
                        @if($client->company_phone_country_code)
                            <span style="color:var(--text-dim);font-size:.8rem;margin-right:4px;">{{ $client->company_phone_country_code }}</span>
                        @endif
                        {{ $client->company_phone }}
                    @else
                        —
                    @endif
                </span>
            </div>
            <div class="info-row">
                <span class="info-row-key">{{ __('Client Status') }}</span>
                <span class="info-row-val" style="display:flex; align-items:center; gap:8px;">
                    @if($client->status === 'active')        <span class="badge-active">{{ __('Active') }}</span>
                    @elseif($client->status === 'suspended') <span class="badge-suspended">{{ __('Suspended') }}</span>
                    @else                                    <span class="badge-pv">{{ __('Pending Verification') }}</span>
                    @endif

                    <form method="POST" action="{{ route('admin.clients.toggle-status', $client) }}" style="display:inline;">
                        @csrf
                        @method('PATCH')
                        @if($client->status === 'active')
                            <button type="submit" class="btn-warn-sm" style="padding: 2px 6px; font-size: 0.7rem;">{{ __('Suspend') }}</button>
                        @else
                            <button type="submit" class="btn-ok-sm" style="padding: 2px 6px; font-size: 0.7rem;">{{ __('Activate') }}</button>
                        @endif
                    </form>
                </span>
            </div>
            <div class="info-row">
                <span class="info-row-key">{{ __('Profile Created') }}</span>
                <span class="info-row-val">{{ $client->created_at?->format('d M Y') ?? '—' }}</span>
            </div>
        </div>
    </div>

    {{-- Financial --}}
    <div class="info-card">
        <div class="info-card-title">{{ __('Financial') }}</div>
        <div class="info-rows">
            <div class="info-row">
                <span class="info-row-key">{{ __('Credit Limit') }}</span>
                <span class="info-row-val" style="font-weight:700;">
                    {{ number_format($client->credit_limit, 2) }}
                    <span style="color:var(--red-lt);font-size:.78rem;margin-left:3px;">JD</span>
                </span>
            </div>
            <div class="info-row">
                <span class="info-row-key">{{ __('Balance') }}</span>
                <span class="info-row-val" style="font-weight:700;">
                    {{ number_format($client->balance, 2) }}
                    <span style="color:var(--red-lt);font-size:.78rem;margin-left:3px;">JD</span>
                </span>
            </div>
            <div class="info-row">
                <span class="info-row-key">{{ __('Expiry Date') }}</span>
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
        <div class="info-card-title">{{ __('Address') }}</div>
        <div class="info-rows">
            <div class="info-row">
                <span class="info-row-key">{{ __('Address Line') }}</span>
                <span class="info-row-val">{{ $client->address_line1 ?: '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-row-key">{{ __('Governorate') }}</span>
                <span class="info-row-val">
                    @if(app()->getLocale() === 'ar')
                        {{ $client->city?->name_ar ?: ($client->city?->name ?? '—') }}
                    @else
                        {{ $client->city?->name ?? '—' }}
                    @endif
                </span>
            </div>
            <div class="info-row">
                <span class="info-row-key">{{ __('Area / District') }}</span>
                <span class="info-row-val">
                    @if(app()->getLocale() === 'ar')
                        {{ $client->area?->name_ar ?: ($client->area?->name ?? '—') }}
                    @else
                        {{ $client->area?->name ?? '—' }}
                    @endif
                </span>
            </div>
        </div>
    </div>

</div>

{{-- ── Banking Details ── --}}
@if(auth()->user()->hasAdminAction('clients.bank_details'))
@if($client->bankDetail)
<div class="section-card">
    <div class="section-card-hd">
        <span class="section-card-title">{{ __('Banking Details') }}</span>
        @if(auth()->user()->hasAdminAction('clients.edit'))
        <a href="{{ route('admin.clients.edit', $client) }}" style="font-size:.78rem;color:var(--red);text-decoration:none;">{{ __('Edit') }}</a>
        @endif
    </div>
    <div class="section-card-body">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0;">
            @php $bd = $client->bankDetail; @endphp

            @if($bd->bank_name)
            <div class="info-row" style="grid-column:1/-1;">
                <span class="info-row-key">{{ __('Bank Name') }}</span>
                <span class="info-row-val">{{ $bd->bank_name }}</span>
            </div>
            @endif

            @if($bd->account_name)
            <div class="info-row" style="grid-column:1/-1;">
                <span class="info-row-key">{{ __('Account Holder') }}</span>
                <span class="info-row-val">{{ $bd->account_name }}</span>
            </div>
            @endif

            @if($bd->iban)
            <div class="info-row" style="grid-column:1/-1;">
                <span class="info-row-key">{{ __('IBAN') }}</span>
                <span class="info-row-val" style="font-family:monospace;letter-spacing:.04em;word-break:break-all;">{{ $bd->iban }}</span>
            </div>
            @endif

            @if($bd->swift_code)
            <div class="info-row" style="grid-column: 1 / -1;">
                <span class="info-row-key">{{ __('SWIFT / BIC') }}</span>
                <span class="info-row-val" style="font-family:monospace;">{{ $bd->swift_code }}</span>
            </div>
            @endif

            @if($bd->account_number)
            <div class="info-row" style="grid-column: 1 / -1;">
                <span class="info-row-key">{{ __('Account Number') }}</span>
                <span class="info-row-val" style="font-family:monospace;">{{ $bd->account_number }}</span>
            </div>
            @endif

            @if($bd->cliq_id)
            <div class="info-row" style="grid-column:1/-1;">
                <span class="info-row-key">{{ __('CliQ ID') }}</span>
                <span class="info-row-val">
                    {{ $bd->cliq_id }}
                    @if($bd->cliq_alias_type)
                        <span style="font-size:.74rem;color:var(--text-dim);margin-left:6px;">({{ $bd->cliq_alias_type === 'alias' ? __('Alias') : __('Phone') }})</span>
                    @endif
                </span>
            </div>
            @endif

            @if($bd->notes)
            <div class="info-row" style="grid-column:1/-1;">
                <span class="info-row-key">{{ __('Notes') }}</span>
                <span class="info-row-val" style="white-space:pre-line;">{{ $bd->notes }}</span>
            </div>
            @endif
        </div>
    </div>
</div>
@endif
@endif

{{-- ── Delivery Rates ── --}}
<div class="section-card">
    <div class="section-card-hd">
        <span class="section-card-title">{{ __('Delivery Rates per Governorate') }}</span>
        @if(auth()->user()->hasAdminAction('clients.edit'))
        <a href="{{ route('admin.clients.edit', $client) }}" style="font-size:.78rem;color:var(--red);text-decoration:none;">{{ __('Edit Rates') }}</a>
        @endif
    </div>
    <div class="section-card-body">
        <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(240px, 1fr));gap:12px;">
            @foreach(\App\Models\City::where('is_active', true)->orderBy('name')->get() as $city)
                @php
                    $customPrice = $client->deliveryPrices->where('city_id', $city->id)->first();
                @endphp
                <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 14px; background:var(--in-bg); border:1px solid var(--bdr); border-radius:10px;">
                    <span style="font-size:.85rem; font-weight:600; color:var(--text);">
                        @if(app()->getLocale() === 'ar')
                            {{ $city->name_ar ?: $city->name }}
                        @else
                            {{ $city->name }}
                        @endif
                    </span>
                    <span style="font-size:.85rem; font-weight:700; color:{{ $customPrice ? 'var(--red)' : 'var(--text-dim)' }}">
                        @if($customPrice)
                            {{ number_format($customPrice->delivery_price, 2) }} JD <span style="font-size:.7rem;font-weight:600;padding:2px 6px;border-radius:4px;background:rgba(220,38,38,0.1);color:var(--red);margin-left:4px;">{{ __('Custom') }}</span>
                        @else
                            {{ number_format($city->delivery_price, 2) }} JD <span style="font-size:.7rem;font-weight:500;color:var(--text-dim);margin-left:4px;">{{ __('Default') }}</span>
                        @endif
                    </span>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ── Attachments ── --}}
@if($client->attachments->count())
<div class="section-card">
    <div class="section-card-hd">
        <span class="section-card-title">{{ __('Attachments') }}</span>
        <span style="font-size:.75rem;color:var(--text-dim);">{{ $client->attachments->count() }} {{ __('file(s)') }}</span>
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
                <a href="{{ $att->url }}" target="_blank" class="att-link">↗ {{ __('View') }}</a>
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
            <span class="section-card-title">{{ __('Employees') }}</span>
            @if($client->employees->count())
                <span style="font-size:.75rem;color:var(--text-dim);">{{ $client->employees->count() }} {{ __('member(s)') }}</span>
            @endif
        </div>
        <a href="{{ route('admin.clients.employees.create', $client) }}" class="btn-primary" style="padding:6px 14px;font-size:.82rem;">
            + {{ __('Add Employee') }}
        </a>
    </div>

    @if($client->employees->count())
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Email') }}</th>
                    <th>{{ __('Job Title') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Joined') }}</th>
                    <th style="width:150px;text-align:center;">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($client->employees as $emp)
                <tr>
                    <td><span class="cell-main">{{ $emp->user->name ?? '—' }}</span></td>
                    <td><span style="font-size:.82rem;color:var(--text-dim);word-break:break-all;">{{ $emp->user->email ?? '—' }}</span></td>
                    <td>{{ $emp->job_title ?: '—' }}</td>
                    <td>
                        @if($emp->status === 'active') <span class="badge-active">{{ __('Active') }}</span>
                        @else <span class="badge-suspended">{{ __('Suspended') }}</span>
                        @endif
                    </td>
                    <td><span style="font-size:.82rem;color:var(--text-dim);">{{ $emp->created_at?->format('d M Y') ?? '—' }}</span></td>
                    <td>
                        <div style="display:flex;gap:6px;align-items:center;justify-content:center;">
                            <form method="POST" action="{{ route('admin.clients.employees.status', [$client, $emp]) }}" style="display:inline;">
                                @csrf @method('PATCH')
                                <button type="submit" class="{{ $emp->status === 'active' ? 'btn-warn-sm' : 'btn-ok-sm' }}">
                                    {{ $emp->status === 'active' ? __('Suspend') : __('Activate') }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.clients.employees.destroy', [$client, $emp]) }}" style="display:inline;"
                                  onsubmit="return confirm('{{ __('Remove :name from this company?', ['name' => addslashes($emp->user->name ?? __('this employee'))]) }}')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-danger-sm">{{ __('Remove') }}</button>
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
        {{ __('No employees yet. Click') }} <strong style="color:var(--text-sub);">+ {{ __('Add Employee') }}</strong> {{ __('to add the first team member.') }}
    </div>
    @endif
</div>



@endsection
