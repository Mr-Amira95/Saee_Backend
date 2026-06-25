@extends('client.layouts.app')
@section('title', 'Account')
@section('page-title', 'Account')

@section('content')

<h1 style="font-size:1.35rem;font-weight:800;margin-bottom:20px;">{{ __('Account') }}</h1>

{{-- Profile card --}}
<div class="card" style="margin-bottom:20px;">
    <div style="display:flex;align-items:center;gap:18px;flex-wrap:wrap;">
        <div style="width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,var(--red-dark),var(--red));display:flex;align-items:center;justify-content:center;font-size:1.5rem;font-weight:800;color:white;flex-shrink:0;">{{ strtoupper(substr($user->name,0,1)) }}</div>

        <div style="flex:1;min-width:180px;">
            <div style="font-size:1.05rem;font-weight:700;">{{ $user->name }}</div>
            <div style="font-size:.82rem;color:var(--text-sub);margin-top:2px;">{{ $user->phone }}</div>
            @if($user->email)
            <div style="font-size:.82rem;color:var(--text-sub);">{{ $user->email }}</div>
            @endif
            <div style="margin-top:6px;display:flex;gap:8px;flex-wrap:wrap;">
                @if($profile->company_name)
                <span class="badge badge-info" style="font-size:.72rem;">{{ $profile->company_name }}</span>
                @endif
                <span class="badge {{ $user->status === 'active' ? 'badge-success' : 'badge-neutral' }}" style="font-size:.72rem;">
                    <span class="badge-dot"></span>{{ $user->status === 'active' ? __('Active') : __('Inactive') }}
                </span>
                <span class="badge badge-neutral" style="font-size:.72rem;">{{ $user->role === 'client_master' ? __('Account Owner') : __('Employee') }}</span>
            </div>
        </div>

        @if($masterUser && $user->role === 'client_employee')
        <div style="text-align:right;min-width:160px;">
            <div style="font-size:.74rem;color:var(--text-dim);margin-bottom:4px;">{{ __('Account Owner') }}</div>
            <div style="font-size:.86rem;font-weight:600;">{{ $masterUser->name }}</div>
            <div style="font-size:.78rem;color:var(--text-sub);">{{ $masterUser->phone }}</div>
        </div>
        @endif

        <a href="{{ route('client.account.profile.edit') }}" class="btn-secondary" style="padding:7px 16px;font-size:.82rem;display:flex;align-items:center;gap:6px;flex-shrink:0;">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 012.828 2.828L11.828 15.828a2 2 0 01-1.415.586H9v-1.414A2 2 0 019.586 13z"/></svg>
            {{ __('Edit Profile') }}
        </a>
    </div>
</div>

{{-- Change Password --}}
<div class="card" style="margin-bottom:20px;">
    <div style="display:flex;align-items:center;justify-content:space-between;">
        <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:36px;height:36px;border-radius:9px;background:rgba(220,38,38,.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="17" height="17" fill="none" stroke="var(--red)" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <div>
                <div style="font-size:.95rem;font-weight:700;">{{ __('Password') }}</div>
                <div style="font-size:.76rem;color:var(--text-dim);">{{ __('Change your account password') }}</div>
            </div>
        </div>
        <a href="{{ route('client.account.password.edit') }}" class="btn-secondary" style="padding:7px 16px;font-size:.82rem;display:flex;align-items:center;gap:6px;">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 012.828 2.828L11.828 15.828a2 2 0 01-1.415.586H9v-1.414A2 2 0 019.586 13z"/></svg>
            {{ __('Change') }}
        </a>
    </div>
</div>

{{-- Notifications --}}
<div class="card" style="margin-bottom:20px;">
    <div style="display:flex;align-items:center;justify-content:space-between;">
        <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:36px;height:36px;border-radius:9px;background:rgba(220,38,38,.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="17" height="17" fill="none" stroke="var(--red)" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            </div>
            <div>
                <div style="font-size:.95rem;font-weight:700;">{{ __('Notifications') }}</div>
                <div style="font-size:.76rem;color:var(--text-dim);" id="notif-label">
                    {{ $user->notifications_enabled ? __('You are receiving notifications') : __('Notifications are disabled') }}
                </div>
            </div>
        </div>

        {{-- Toggle switch --}}
        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;user-select:none;" title="Toggle notifications">
            <input type="checkbox" id="notif-toggle" style="display:none;" {{ $user->notifications_enabled ? 'checked' : '' }}>
            <div id="notif-track" style="
                width:44px;height:24px;border-radius:12px;
                background:{{ $user->notifications_enabled ? 'var(--red,#dc2626)' : '#d1d5db' }};
                position:relative;transition:background .2s;flex-shrink:0;
            ">
                <div id="notif-thumb" style="
                    width:18px;height:18px;border-radius:50%;background:#fff;
                    position:absolute;top:3px;
                    left:{{ $user->notifications_enabled ? '23px' : '3px' }};
                    transition:left .2s;box-shadow:0 1px 3px rgba(0,0,0,.25);
                "></div>
            </div>
        </label>
    </div>
</div>

<script>
(function () {
    const checkbox = document.getElementById('notif-toggle');
    const track    = document.getElementById('notif-track');
    const thumb    = document.getElementById('notif-thumb');
    const label    = document.getElementById('notif-label');

    document.getElementById('notif-track').parentElement.addEventListener('click', function () {
        const enabled = !checkbox.checked;
        checkbox.checked = enabled;

        track.style.background = enabled ? 'var(--red,#dc2626)' : '#d1d5db';
        thumb.style.left       = enabled ? '23px' : '3px';
        label.textContent      = enabled ? '{{ __('You are receiving notifications') }}' : '{{ __('Notifications are disabled') }}';

        fetch('{{ route('client.account.notifications.toggle') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
        }).catch(function () {
            // revert on failure
            checkbox.checked = !enabled;
            track.style.background = !enabled ? 'var(--red,#dc2626)' : '#d1d5db';
            thumb.style.left       = !enabled ? '23px' : '3px';
            label.textContent      = !enabled ? '{{ __('You are receiving notifications') }}' : '{{ __('Notifications are disabled') }}';
        });
    });
})();
</script>

{{-- Banking Details --}}
<div class="card" style="margin-bottom:20px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
        <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:36px;height:36px;border-radius:9px;background:rgba(220,38,38,.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="17" height="17" fill="none" stroke="var(--red)" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            </div>
            <div>
                <div style="font-size:.95rem;font-weight:700;">{{ __('Banking Details') }}</div>
                <div style="font-size:.76rem;color:var(--text-dim);">{{ __('Bank account for settlements and payouts') }}</div>
            </div>
        </div>
        <a href="{{ route('client.account.banking') }}" class="btn-secondary" style="padding:7px 16px;font-size:.82rem;display:flex;align-items:center;gap:6px;">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 012.828 2.828L11.828 15.828a2 2 0 01-1.415.586H9v-1.414A2 2 0 019.586 13z"/></svg>
            {{ __('Edit') }}
        </a>
    </div>

    @if($bankDetail)
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:0;">

        @php
        $bankRows = [
            ['label' => __('Bank Name'),          'value' => $bankDetail->bank_name,     'mono' => false],
            ['label' => __('Account Holder'),     'value' => $bankDetail->account_name,  'mono' => false],
            ['label' => __('IBAN'),               'value' => $bankDetail->iban,          'mono' => true],
            ['label' => __('SWIFT / BIC'),        'value' => $bankDetail->swift_code,    'mono' => true],
            ['label' => __('Account Number'),     'value' => $bankDetail->account_number,'mono' => true],
            ['label' => __('CliQ Type'),          'value' => $bankDetail->cliq_alias_type ? ucfirst($bankDetail->cliq_alias_type) : null, 'mono' => false],
            ['label' => __('CliQ Alias / No.'),   'value' => $bankDetail->cliq_id,       'mono' => false],
        ];
        @endphp

        @foreach($bankRows as $row)
        <div style="padding:10px 0;border-bottom:1px solid var(--border-subtle,rgba(0,0,0,.06));display:flex;flex-direction:column;gap:3px;">
            <div style="font-size:.72rem;font-weight:600;color:var(--text-dim);text-transform:uppercase;letter-spacing:.08em;">{{ $row['label'] }}</div>
            <div style="font-size:.88rem;color:{{ $row['value'] ? 'var(--text)' : 'var(--text-dim)' }};{{ $row['mono'] ? 'font-family:monospace;' : '' }}">
                {{ $row['value'] ?? '—' }}
            </div>
        </div>
        @endforeach

        @if($bankDetail->notes)
        <div style="padding:10px 0;grid-column:1/-1;">
            <div style="font-size:.72rem;font-weight:600;color:var(--text-dim);text-transform:uppercase;letter-spacing:.08em;margin-bottom:3px;">{{ __('Notes') }}</div>
            <div style="font-size:.88rem;color:var(--text);">{{ $bankDetail->notes }}</div>
        </div>
        @endif

    </div>
    @else
    <div style="padding:24px 0;text-align:center;color:var(--text-dim);font-size:.88rem;">
        {{ __('No banking details on file.') }} <a href="{{ route('client.account.banking') }}" style="color:var(--red);text-decoration:none;font-weight:600;">{{ __('Add now →') }}</a>
    </div>
    @endif
</div>

{{-- Company Information --}}
<div class="card" style="margin-bottom:20px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
        <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:36px;height:36px;border-radius:9px;background:rgba(220,38,38,.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="17" height="17" fill="none" stroke="var(--red)" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <div>
                <div style="font-size:.95rem;font-weight:700;">{{ __('Company Information') }}</div>
                <div style="font-size:.76rem;color:var(--text-dim);">{{ __('Business registration and contact details') }}</div>
            </div>
        </div>
        @if($user->role !== 'client_employee')
        <a href="{{ route('client.account.company') }}" class="btn-secondary" style="padding:7px 16px;font-size:.82rem;display:flex;align-items:center;gap:6px;">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 012.828 2.828L11.828 15.828a2 2 0 01-1.415.586H9v-1.414A2 2 0 019.586 13z"/></svg>
            {{ __('Edit') }}
        </a>
        @endif
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:0;">

        @php
        $companyRows = [
            ['label' => __('Company Name'),         'value' => $profile->company_name,                   'mono' => false],
            ['label' => __('Commercial Reg. No.'),  'value' => $profile->commercial_register_number,     'mono' => true],
            ['label' => __('VAT Number'),           'value' => $profile->vat_number,                     'mono' => true],
            ['label' => __('Company Email'),        'value' => $profile->email,                          'mono' => false],
            ['label' => __('Company Phone'),        'value' => $profile->company_phone,                  'mono' => false],
            ['label' => __('City'),                 'value' => $profile->city?->name,                    'mono' => false],
            ['label' => __('Area'),                 'value' => $profile->area?->name,                    'mono' => false],
        ];
        @endphp

        @foreach($companyRows as $row)
        <div style="padding:10px 0;border-bottom:1px solid var(--border-subtle,rgba(0,0,0,.06));display:flex;flex-direction:column;gap:3px;">
            <div style="font-size:.72rem;font-weight:600;color:var(--text-dim);text-transform:uppercase;letter-spacing:.08em;">{{ $row['label'] }}</div>
            <div style="font-size:.88rem;color:{{ $row['value'] ? 'var(--text)' : 'var(--text-dim)' }};{{ $row['mono'] ? 'font-family:monospace;' : '' }}">
                {{ $row['value'] ?? '—' }}
            </div>
        </div>
        @endforeach

        @if($profile->address_line1)
        <div style="padding:10px 0;grid-column:1/-1;">
            <div style="font-size:.72rem;font-weight:600;color:var(--text-dim);text-transform:uppercase;letter-spacing:.08em;margin-bottom:3px;">{{ __('Address') }}</div>
            <div style="font-size:.88rem;color:var(--text);">{{ $profile->address_line1 }}</div>
        </div>
        @endif

    </div>
</div>

@endsection
