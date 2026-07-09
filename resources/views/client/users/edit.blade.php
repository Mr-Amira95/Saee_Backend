@extends('client.layouts.app')
@section('title', __('Edit User'))
@section('page-title', __('Edit User'))

@push('styles')
<style>
    .form-wrap { max-width: 100%; }
    .form-section {
        background: var(--card);
        border: 1px solid var(--bdr);
        border-radius: 14px;
        padding: 24px;
        margin-bottom: 18px;
        backdrop-filter: blur(8px);
    }
    .form-section-title {
        font-size: .76rem;
        font-weight: 700;
        color: var(--text-dim);
        text-transform: uppercase;
        letter-spacing: .1em;
        margin-bottom: 18px;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--bdr);
    }
    .form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    @media (max-width: 768px) { .form-grid-2 { grid-template-columns: 1fr; } }
    .form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 0; }

    /* ── Phone extension dropdown ── */
    .phone-wrap { display: flex; gap: 0; }
    .phone-ext-btn {
        display: flex; align-items: center; gap: 6px;
        padding: 0 10px; min-width: 105px; height: 42px;
        background: var(--in-bg); border: 1px solid var(--in-bdr);
        border-right: none; border-radius: 9px 0 0 9px;
        color: var(--text); cursor: pointer; user-select: none;
        white-space: nowrap; font-size: .88rem; transition: border-color .2s;
    }
    .phone-ext-btn:hover { border-color: rgba(220,38,38,.4); }
    .phone-ext-btn .flag { font-size: 1.05rem; }
    .phone-ext-btn .code { font-weight: 600; color: var(--red-lt); }
    .phone-ext-btn .arrow { margin-left: auto; font-size: .6rem; color: var(--text-sub); }
    .phone-input-field {
        flex: 1; padding: 0 13px; height: 42px;
        background: var(--in-bg); border: 1px solid var(--in-bdr);
        border-radius: 0 9px 9px 0; color: var(--text); font-size: .88rem;
        font-family: inherit; outline: none; transition: border-color .2s;
    }
    .phone-input-field:focus { border-color: rgba(220,38,38,.4); }
    .phone-dropdown {
        position: absolute; z-index: 500; top: calc(100% + 4px); left: 0;
        width: 280px; background: #0c1230; border: 1px solid var(--bdr);
        border-radius: 10px; box-shadow: 0 8px 30px rgba(0,0,0,.5);
        display: none; overflow: hidden;
    }
    .phone-dropdown.open { display: block; }
    .phone-dd-search {
        width: 100%; padding: 10px 12px; background: transparent;
        border: none; border-bottom: 1px solid var(--bdr);
        color: var(--text); font-size: .85rem; box-sizing: border-box; outline: none;
    }
    .phone-dd-list { max-height: 220px; overflow-y: auto; }
    .phone-dd-item {
        display: flex; align-items: center; gap: 8px;
        padding: 8px 12px; cursor: pointer; font-size: .85rem; transition: background .15s;
    }
    .phone-dd-item:hover { background: rgba(220,38,38,.12); }
    .phone-dd-item .dd-flag { font-size: 1.05rem; }
    .phone-dd-item .dd-name { flex: 1; color: var(--text); }
    .phone-dd-item .dd-code { color: var(--red-lt); font-weight: 600; font-size: .78rem; }

    /* ── Password visibility toggle ── */
    .pwd-field-wrap { position: relative; }
    .pwd-toggle-btn {
        position: absolute; right: 8px; top: 50%; transform: translateY(-50%);
        background: none; border: none; cursor: pointer; color: var(--text-sub);
        padding: 4px; display: flex;
    }
</style>
@endpush

@section('content')

<div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
    <a href="{{ route('client.users.index') }}" class="btn-secondary" style="padding:7px 14px;font-size:.82rem;">{{ __('← Back to Team') }}</a>
    <div>
        <h1 style="font-size:1.3rem;font-weight:800;">{{ __('Edit Team Member') }}</h1>
        <p style="font-size:.82rem;color:var(--text-sub);">{{ __('Modify employee account and credentials') }}</p>
    </div>
</div>

@if($errors->any())
<div class="flash flash-err" style="margin-bottom:16px;">
    <ul style="margin:0;padding-left:16px;">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="form-wrap">
<form method="POST" action="{{ route('client.users.update', $employee->id) }}">
    @csrf
    @method('PUT')

    <div class="form-section" style="position:relative;z-index:2;">
        <div class="form-section-title">{{ __('Employee Information') }}</div>

        <div class="form-grid-2" style="margin-bottom:16px;">
            <div class="form-group">
                <label class="form-label" for="name">{{ __('Full Name *') }}</label>
                <input id="name" type="text" name="name" value="{{ old('name', $employee->user->name) }}" required class="form-input {{ $errors->has('name') ? 'has-error' : '' }}" placeholder="{{ __('e.g. Ahmed Ali') }}">
                @error('name') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="username">{{ __('Username *') }}</label>
                <input id="username" type="text" name="username" value="{{ old('username', $employee->user->username) }}" required class="form-input {{ $errors->has('username') ? 'has-error' : '' }}" placeholder="{{ __('e.g. ahmed.ali') }}">
                @error('username') <div class="form-error">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="form-grid-2" style="margin-bottom:16px;">
            <div class="form-group">
                <label class="form-label" for="phone">{{ __('Phone Number') }} <span class="opt">({{ __('required for WhatsApp') }})</span></label>
                <div style="position:relative;">
                    <div class="phone-wrap">
                        <button type="button" class="phone-ext-btn" id="phoneExtBtn">
                            <span class="flag" id="phoneExtFlag">🇯🇴</span>
                            <span class="code" id="phoneExtCode">+962</span>
                            <span class="arrow">▼</span>
                        </button>
                        <input type="hidden" name="phone_country_code" id="phoneExtVal" value="{{ old('phone_country_code', $employee->user->phone_country_code ?? '+962') }}">
                        <input type="text" name="phone" id="phone" class="phone-input-field {{ $errors->has('phone') ? 'has-error' : '' }}" value="{{ old('phone', $employee->user->phone) }}" placeholder="{{ __('7X XXX XXXX') }}">
                    </div>
                    <div class="phone-dropdown" id="phoneExtDropdown">
                        <input type="text" class="phone-dd-search" placeholder="{{ __('Search country or code…') }}">
                        <div class="phone-dd-list" id="phoneExtList"></div>
                    </div>
                </div>
                @error('phone') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="email">{{ __('Email Address') }} <span class="opt">({{ __('required for email channel') }})</span></label>
                <input id="email" type="email" name="email" value="{{ old('email', $employee->user->email) }}" class="form-input {{ $errors->has('email') ? 'has-error' : '' }}" placeholder="{{ __('e.g. employee@company.com') }}">
                @error('email') <div class="form-error">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="form-group" style="margin-bottom:16px;">
            <label class="form-label">{{ __('Notification channel') }}</label>
            <div style="position:relative;display:inline-flex;align-items:center;background:var(--in-bg);border:1px solid var(--bdr);border-radius:8px;padding:3px;gap:2px;" id="otpChannelWrap">
                <input type="hidden" name="otp_channel" id="otpChannelInput" value="{{ old('otp_channel', $employee->user->otp_channel ?? 'whatsapp') }}">
                <button type="button" id="btnOtpWhatsapp"
                    onclick="setOtpChannel('whatsapp')"
                    style="display:flex;align-items:center;gap:6px;padding:5px 12px;border:none;border-radius:6px;font-size:.8rem;font-weight:600;cursor:pointer;transition:background .2s,color .2s;background:#25D366;color:#fff;">
                    {{ __('WhatsApp') }}
                </button>
                <button type="button" id="btnOtpEmail"
                    onclick="setOtpChannel('email')"
                    style="display:flex;align-items:center;gap:6px;padding:5px 12px;border:none;border-radius:6px;font-size:.8rem;font-weight:600;cursor:pointer;transition:background .2s,color .2s;background:transparent;color:var(--text-sub);">
                    {{ __('Email') }}
                </button>
            </div>
            @error('otp_channel') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-grid-2" style="margin-bottom:16px;">
            <div class="form-group">
                <label class="form-label" for="password">{{ __('Password') }} <span style="text-transform:none;font-weight:400;">({{ __('optional — leave blank to keep current password') }})</span></label>
                <div class="pwd-field-wrap">
                    <input id="password" type="password" name="password" autocomplete="new-password" class="form-input {{ $errors->has('password') ? 'has-error' : '' }}" placeholder="{{ __('Minimum 8 characters') }}" style="padding-right:40px;">
                    <button type="button" class="pwd-toggle-btn" onclick="togglePwd('password','eyePwd')">
                        <svg id="eyePwd" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
                @error('password') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="password_confirmation">{{ __('Confirm Password') }}</label>
                <div class="pwd-field-wrap">
                    <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" class="form-input" placeholder="{{ __('Repeat password') }}" style="padding-right:40px;">
                    <button type="button" class="pwd-toggle-btn" onclick="togglePwd('password_confirmation','eyePwdConf')">
                        <svg id="eyePwdConf" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="job_title">{{ __('Job Title') }}</label>
            <input id="job_title" type="text" name="job_title" value="{{ old('job_title', $employee->job_title) }}" class="form-input {{ $errors->has('job_title') ? 'has-error' : '' }}" placeholder="{{ __('e.g. Operations Manager') }}">
            @error('job_title') <div class="form-error">{{ $message }}</div> @enderror
        </div>
    </div>

    @include('client.users.partials.permissions', ['checkedIds' => collect(old('permissions', $grantedPermissionIds))->map(fn($v) => (int) $v)])

    <div style="display:flex;gap:10px;">
        <button type="submit" class="btn-primary" style="justify-content:center;">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            {{ __('Save Changes') }}
        </button>
        <a href="{{ route('client.users.index') }}" class="btn-secondary" style="text-decoration:none;">{{ __('Cancel') }}</a>
    </div>
</form>
</div>

@endsection

@push('scripts')
<script>
const CLIENT_USER_COUNTRIES = [
    { flag:'🇯🇴', name:'Jordan',         code:'+962' },
    { flag:'🇸🇦', name:'Saudi Arabia',    code:'+966' },
    { flag:'🇦🇪', name:'UAE',             code:'+971' },
    { flag:'🇰🇼', name:'Kuwait',          code:'+965' },
    { flag:'🇧🇭', name:'Bahrain',         code:'+973' },
    { flag:'🇶🇦', name:'Qatar',           code:'+974' },
    { flag:'🇴🇲', name:'Oman',            code:'+968' },
    { flag:'🇮🇶', name:'Iraq',            code:'+964' },
    { flag:'🇸🇾', name:'Syria',           code:'+963' },
    { flag:'🇱🇧', name:'Lebanon',         code:'+961' },
    { flag:'🇵🇸', name:'Palestine',       code:'+970' },
    { flag:'🇪🇬', name:'Egypt',           code:'+20'  },
    { flag:'🇱🇾', name:'Libya',           code:'+218' },
    { flag:'🇹🇳', name:'Tunisia',         code:'+216' },
    { flag:'🇩🇿', name:'Algeria',         code:'+213' },
    { flag:'🇲🇦', name:'Morocco',         code:'+212' },
    { flag:'🇸🇩', name:'Sudan',           code:'+249' },
    { flag:'🇾🇪', name:'Yemen',           code:'+967' },
    { flag:'🇹🇷', name:'Turkey',          code:'+90'  },
    { flag:'🇮🇳', name:'India',           code:'+91'  },
    { flag:'🇵🇰', name:'Pakistan',        code:'+92'  },
    { flag:'🇧🇩', name:'Bangladesh',      code:'+880' },
    { flag:'🇵🇭', name:'Philippines',     code:'+63'  },
    { flag:'🇮🇩', name:'Indonesia',       code:'+62'  },
    { flag:'🇬🇧', name:'United Kingdom',  code:'+44'  },
    { flag:'🇺🇸', name:'United States',   code:'+1'   },
    { flag:'🇨🇦', name:'Canada',          code:'+1'   },
    { flag:'🇩🇪', name:'Germany',         code:'+49'  },
    { flag:'🇫🇷', name:'France',          code:'+33'  },
    { flag:'🇮🇹', name:'Italy',           code:'+39'  },
    { flag:'🇪🇸', name:'Spain',           code:'+34'  },
    { flag:'🇳🇱', name:'Netherlands',     code:'+31'  },
    { flag:'🇸🇪', name:'Sweden',          code:'+46'  },
    { flag:'🇳🇴', name:'Norway',          code:'+47'  },
    { flag:'🇩🇰', name:'Denmark',         code:'+45'  },
    { flag:'🇨🇭', name:'Switzerland',     code:'+41'  },
    { flag:'🇦🇺', name:'Australia',       code:'+61'  },
    { flag:'🇳🇿', name:'New Zealand',     code:'+64'  },
    { flag:'🇸🇬', name:'Singapore',       code:'+65'  },
    { flag:'🇲🇾', name:'Malaysia',        code:'+60'  },
    { flag:'🇹🇭', name:'Thailand',        code:'+66'  },
    { flag:'🇯🇵', name:'Japan',           code:'+81'  },
    { flag:'🇨🇳', name:'China',           code:'+86'  },
    { flag:'🇰🇷', name:'South Korea',     code:'+82'  },
    { flag:'🇷🇺', name:'Russia',          code:'+7'   },
    { flag:'🇿🇦', name:'South Africa',    code:'+27'  },
    { flag:'🇳🇬', name:'Nigeria',         code:'+234' },
    { flag:'🇰🇪', name:'Kenya',           code:'+254' },
    { flag:'🇧🇷', name:'Brazil',          code:'+55'  },
    { flag:'🇲🇽', name:'Mexico',          code:'+52'  },
];

function initClientUserPhoneDropdown(btnId, flagId, codeId, valId, ddId, listId) {
    const btn      = document.getElementById(btnId);
    const flagEl   = document.getElementById(flagId);
    const codeEl   = document.getElementById(codeId);
    const valEl    = document.getElementById(valId);
    const dd       = document.getElementById(ddId);
    const listEl   = document.getElementById(listId);
    const searchEl = dd.querySelector('.phone-dd-search');

    function renderList(q) {
        q = (q || '').toLowerCase();
        listEl.innerHTML = '';
        CLIENT_USER_COUNTRIES
            .filter(c => !q || c.name.toLowerCase().includes(q) || c.code.includes(q))
            .forEach(c => {
                const item = document.createElement('div');
                item.className = 'phone-dd-item';
                item.innerHTML =
                    '<span class="dd-flag">' + c.flag + '</span>' +
                    '<span class="dd-name">' + c.name + '</span>' +
                    '<span class="dd-code">' + c.code + '</span>';
                item.addEventListener('click', function() {
                    flagEl.textContent = c.flag;
                    codeEl.textContent = c.code;
                    valEl.value = c.code;
                    dd.classList.remove('open');
                });
                listEl.appendChild(item);
            });
    }

    renderList('');
    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        dd.classList.toggle('open');
        if (dd.classList.contains('open')) searchEl.focus();
    });
    searchEl.addEventListener('input', function() { renderList(this.value); });
    document.addEventListener('click', function(e) {
        if (!dd.contains(e.target) && e.target !== btn) dd.classList.remove('open');
    });

    const initVal = valEl.value;
    if (initVal) {
        const match = CLIENT_USER_COUNTRIES.find(c => c.code === initVal);
        if (match) { flagEl.textContent = match.flag; codeEl.textContent = match.code; }
    }
}

function togglePwd(inputId, iconId) {
    const input = document.getElementById(inputId);
    const isText = input.type === 'text';
    input.type = isText ? 'password' : 'text';
    document.getElementById(iconId).style.opacity = isText ? '1' : '0.5';
}

function setOtpChannel(ch) {
    document.getElementById('otpChannelInput').value = ch;
    const wa = document.getElementById('btnOtpWhatsapp');
    const em = document.getElementById('btnOtpEmail');
    if (ch === 'whatsapp') {
        wa.style.background = '#25D366'; wa.style.color = '#fff';
        em.style.background = 'transparent'; em.style.color = 'var(--text-sub)';
    } else {
        em.style.background = '#dc2626'; em.style.color = '#fff';
        wa.style.background = 'transparent'; wa.style.color = 'var(--text-sub)';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    initClientUserPhoneDropdown('phoneExtBtn', 'phoneExtFlag', 'phoneExtCode', 'phoneExtVal', 'phoneExtDropdown', 'phoneExtList');
    setOtpChannel(document.getElementById('otpChannelInput').value);
});
</script>
@endpush
