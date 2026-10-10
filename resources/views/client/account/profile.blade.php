@extends('client.layouts.app')
@section('title', 'Edit Profile')
@section('page-title', 'Edit Profile')

@push('styles')
<style>
    .acct-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    @media (max-width: 600px) { .acct-grid-2 { grid-template-columns: 1fr; } }

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
        flex: 1; min-width: 0; padding: 0 13px; height: 42px;
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
</style>
@endpush

@section('content')

<div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
    <a href="{{ route('client.account.index') }}" class="btn-secondary" style="padding:7px 14px;font-size:.82rem;">{{ __('← Back') }}</a>
    <div>
        <h1 style="font-size:1.3rem;font-weight:800;">{{ __('Edit Profile') }}</h1>
        <p style="font-size:.82rem;color:var(--text-sub);">{{ __('Update your personal information') }}</p>
    </div>
</div>

@if(session('success'))
<div class="flash flash-ok" style="margin-bottom:16px;">{{ session('success') }}</div>
@endif

<form method="POST" action="{{ route('client.account.profile.update') }}" id="profileForm" novalidate>
@csrf

<div class="card" style="margin-bottom:16px;">
    <div style="font-size:.76rem;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:.1em;margin-bottom:18px;">{{ __('Personal Information') }}</div>

    <div class="form-group">
        <label class="form-label" for="name">{{ __('Full Name *') }}</label>
        <input id="name" name="name" type="text" class="form-input {{ $errors->has('name') ? 'has-error' : '' }}"
               placeholder="{{ __('Your full name') }}" value="{{ old('name', $user->name) }}" required>
        @error('name') <div class="form-error">{{ $message }}</div> @enderror
    </div>

    <div class="acct-grid-2">
        <div class="form-group" style="margin-bottom:0;">
            <label class="form-label" for="email">{{ __('Email Address') }}</label>
            <input id="email" name="email" type="email" class="form-input {{ $errors->has('email') ? 'has-error' : '' }}"
                   placeholder="{{ __('you@example.com') }}" value="{{ old('email', $user->email) }}">
            @error('email') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group" style="margin-bottom:0;">
            <label class="form-label" for="phone">{{ __('Phone Number') }}</label>
            <div style="position:relative;">
                <div class="phone-wrap">
                    <button type="button" class="phone-ext-btn" id="phoneExtBtn">
                        <span class="flag" id="phoneExtFlag">🇯🇴</span>
                        <span class="code" id="phoneExtCode">+962</span>
                        <span class="arrow">▼</span>
                    </button>
                    <input type="hidden" name="phone_country_code" id="phoneExtVal" value="{{ old('phone_country_code', $user->phone_country_code ?? '+962') }}">
                    <input id="phone" name="phone" type="tel" class="phone-input-field {{ $errors->has('phone') ? 'has-error' : '' }}"
                           placeholder="{{ __('7X XXX XXXX') }}" value="{{ old('phone', $user->phone) }}">
                </div>
                <div class="phone-dropdown" id="phoneExtDropdown">
                    <input type="text" class="phone-dd-search" placeholder="{{ __('Search country or code…') }}">
                    <div class="phone-dd-list" id="phoneExtList"></div>
                </div>
            </div>
            @error('phone_country_code') <div class="form-error">{{ $message }}</div> @enderror
            @error('phone') <div class="form-error">{{ $message }}</div> @enderror
        </div>
    </div>
</div>

<button type="submit" class="btn-primary" style="width:100%;justify-content:center;padding:13px 24px;font-size:.92rem;">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
    {{ __('Save Changes') }}
</button>

</form>

@endsection

@push('scripts')
<script>
const PROFILE_COUNTRIES = [
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

(function() {
    const btn      = document.getElementById('phoneExtBtn');
    const flagEl   = document.getElementById('phoneExtFlag');
    const codeEl   = document.getElementById('phoneExtCode');
    const valEl    = document.getElementById('phoneExtVal');
    const dd       = document.getElementById('phoneExtDropdown');
    const listEl   = document.getElementById('phoneExtList');
    if (!btn || !dd) return;
    const searchEl = dd.querySelector('.phone-dd-search');

    function renderList(q) {
        q = (q || '').toLowerCase();
        listEl.innerHTML = '';
        PROFILE_COUNTRIES
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

    const match = PROFILE_COUNTRIES.find(c => c.code === valEl.value);
    if (match) { flagEl.textContent = match.flag; codeEl.textContent = match.code; }
})();

(function() {
    var form = document.getElementById('profileForm');
    if (!form) return;

    function getField(n) { return form.querySelector('[name="' + n + '"]'); }

    function showFieldError(el, msg) {
        var container = el.closest('.form-group') || el.parentElement;
        el.classList.add('has-error', 'js-marked');
        var err = document.createElement('div');
        err.className = 'form-error js-err';
        err.textContent = msg;
        container.appendChild(err);
    }

    function clearFieldError(el) {
        var container = el.closest('.form-group') || el.parentElement;
        el.classList.remove('has-error', 'js-marked');
        var err = container.querySelector('.js-err');
        if (err) err.remove();
    }

    function isValidName(v) { return /^[\p{L}\s]+$/u.test(v.trim()); }
    function isEmail(v) { return /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(v.trim()); }
    function isValidPhone(v) { return /^[0-9]{6,15}$/.test(v.trim()); }

    function wireLiveValidation(name, validator, msg) {
        var el = getField(name);
        if (!el) return;
        el.addEventListener('input', function() {
            clearFieldError(el);
            if (el.value.trim() && !validator(el.value)) showFieldError(el, msg);
        });
    }

    wireLiveValidation('name', isValidName, '{{ __('Full name must only contain letters and spaces (no numbers or special characters).') }}');
    wireLiveValidation('email', isEmail, '{{ __('Please enter a valid email address in the format name@domain.com.') }}');
    wireLiveValidation('phone', isValidPhone, '{{ __('Phone must contain 6 to 15 digits only.') }}');

    form.addEventListener('submit', function(e) {
        form.querySelectorAll('.js-marked').forEach(function(el) { clearFieldError(el); });
        var first = null;

        [
            ['name', isValidName, '{{ __('Full name must only contain letters and spaces (no numbers or special characters).') }}'],
            ['email', isEmail, '{{ __('Please enter a valid email address in the format name@domain.com.') }}'],
            ['phone', isValidPhone, '{{ __('Phone must contain 6 to 15 digits only.') }}'],
        ].forEach(function(rule) {
            var el = getField(rule[0]);
            if (el && el.value.trim() && !rule[1](el.value)) {
                showFieldError(el, rule[2]);
                if (!first) first = el;
            }
        });

        if (first) {
            e.preventDefault();
            first.scrollIntoView({ behavior: 'smooth', block: 'center' });
            first.focus();
        }
    });
})();
</script>
@endpush
