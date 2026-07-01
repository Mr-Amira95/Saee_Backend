@extends('admin.layouts.app')

@section('title', 'Add Admin')

@section('page-title', 'Add Admin')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <span>/</span>
    <a href="{{ route('admin.admins.index') }}">Admins</a>
    <span>/</span>
    <span>Add Admin</span>
@endsection

@section('head')
<style>
/* ── Phone extension dropdown ── */
.phone-wrap { display: flex; gap: 0; }
.phone-ext-btn {
    display: flex; align-items: center; gap: 6px;
    padding: 0 10px; min-width: 110px; height: 42px;
    background: var(--in-bg); border: 1px solid var(--bdr);
    border-right: none; border-radius: 8px 0 0 8px;
    color: var(--text); cursor: pointer; user-select: none;
    white-space: nowrap; font-size: .9rem; transition: border-color .2s;
}
.phone-ext-btn:hover { border-color: var(--red); }
.phone-ext-btn .flag { font-size: 1.1rem; }
.phone-ext-btn .code { font-weight: 600; color: var(--red); }
.phone-ext-btn .arrow { margin-left: auto; font-size: .65rem; color: var(--text-sub); }
.phone-input-field {
    flex: 1; padding: 0 12px; height: 42px;
    background: var(--in-bg); border: 1px solid var(--bdr);
    border-radius: 0 8px 8px 0; color: var(--text); font-size: .9rem;
    transition: border-color .2s;
}
.phone-input-field:focus { outline: none; border-color: var(--red); }
.phone-dropdown {
    position: absolute; z-index: 500; top: calc(100% + 4px); left: 0;
    width: 300px; background: var(--bg-2); border: 1px solid var(--bdr);
    border-radius: 10px; box-shadow: 0 8px 30px rgba(0,0,0,.6);
    display: none; overflow: hidden;
}
.phone-dropdown.open { display: block; }
.phone-dd-search {
    width: 100%; padding: 10px 12px; background: var(--bg);
    border: none; border-bottom: 1px solid var(--bdr);
    color: var(--text); font-size: .85rem; box-sizing: border-box;
}
.phone-dd-search:focus { outline: none; }
.phone-dd-list { max-height: 220px; overflow-y: auto; }
.phone-dd-item {
    display: flex; align-items: center; gap: 8px;
    padding: 8px 12px; cursor: pointer; font-size: .88rem; transition: background .15s;
}
.phone-dd-item:hover { background: rgba(220,38,38,.12); }
.phone-dd-item .dd-flag { font-size: 1.1rem; }
.phone-dd-item .dd-name { flex: 1; color: var(--text); }
.phone-dd-item .dd-code { color: var(--red); font-weight: 600; font-size: .8rem; }
</style>
@endsection

@section('content')
<form method="POST" action="{{ route('admin.admins.store') }}" novalidate>
    @csrf

    {{-- Account --}}
    <div class="form-section">
        <div class="form-section-title">Account Details</div>
        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label" for="name">Full Name <span class="req">*</span></label>
                <input class="form-input @error('name') is-error @enderror" id="name" type="text" name="name" value="{{ old('name') }}" required>
                @error('name')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="username">Username <span class="req">*</span></label>
                <input class="form-input @error('username') is-error @enderror" id="username" type="text" name="username" value="{{ old('username') }}" required>
                @error('username')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="email">Email <span class="req">*</span></label>
                <input class="form-input @error('email') is-error @enderror" id="email" type="email" name="email" value="{{ old('email') }}" required>
                @error('email')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="phone">Phone <span class="opt">(optional)</span></label>
                <div style="position:relative;">
                    <div class="phone-wrap">
                        <button type="button" class="phone-ext-btn" id="phoneExtBtn">
                            <span class="flag" id="phoneExtFlag">🇯🇴</span>
                            <span class="code" id="phoneExtCode">+962</span>
                            <span class="arrow">▼</span>
                        </button>
                        <input type="hidden" name="phone_country_code" id="phoneExtVal" value="{{ old('phone_country_code', '+962') }}">
                        <input type="text" name="phone" id="phone" class="phone-input-field @error('phone') is-error @enderror"
                               value="{{ old('phone') }}" placeholder="7X XXX XXXX">
                    </div>
                    <div class="phone-dropdown" id="phoneExtDropdown">
                        <input type="text" class="phone-dd-search" placeholder="Search country or code…">
                        <div class="phone-dd-list" id="phoneExtList"></div>
                    </div>
                </div>
                @error('phone')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="password">Password <span class="opt">(optional — leave blank to auto-generate &amp; send invitation)</span></label>
                <div style="position:relative;">
                    <input class="form-input @error('password') is-error @enderror" id="password" type="password" name="password" autocomplete="new-password" placeholder="Minimum 8 characters" style="width:100%;padding-right:40px;box-sizing:border-box;">
                    <button type="button" onclick="togglePwd('password','eyePwd')" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-sub);padding:4px;display:flex;">
                        <svg id="eyePwd" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
                @error('password')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="password_confirmation">Confirm Password <span class="opt">(optional)</span></label>
                <div style="position:relative;">
                    <input class="form-input" id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" placeholder="Repeat password" style="width:100%;padding-right:40px;box-sizing:border-box;">
                    <button type="button" onclick="togglePwd('password_confirmation','eyePwdConf')" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-sub);padding:4px;display:flex;">
                        <svg id="eyePwdConf" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Permissions --}}
    <div class="form-section">
        <div class="form-section-title">Permissions</div>
        <p style="font-size: .82rem; color: var(--text-sub); margin-bottom: 16px;">Grant specific permissions to this admin. Superadmins always have full access regardless.</p>
        <div class="perm-groups">
            @foreach($permissions as $group => $perms)
            <div>
                <div class="perm-group-title">{{ ucwords(str_replace('_', ' ', $group)) }}</div>
                <div class="perm-grid">
                    @foreach($perms as $perm)
                    <label class="perm-item">
                        <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                            {{ in_array($perm->id, old('permissions', [])) ? 'checked' : '' }}>
                        <div>
                            <div class="perm-name">{{ $perm->display_name }}</div>
                            @if($perm->description)
                                <div class="perm-desc">{{ $perm->description }}</div>
                            @endif
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="form-actions">
        <a href="{{ route('admin.admins.index') }}" class="btn-secondary">Cancel</a>
        <button type="submit" class="btn-primary">Create Admin</button>
    </div>
</form>
@endsection

@section('scripts')
<script>
/* ── Country phone data ── */
const COUNTRIES = [
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

/* ── Phone extension dropdown factory ── */
function initPhoneDropdown(btnId, flagId, codeId, valId, ddId, listId) {
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
        COUNTRIES
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

    // Match code on load
    const currentVal = valEl.value;
    if (currentVal) {
        const found = COUNTRIES.find(c => c.code === currentVal);
        if (found) {
            flagEl.textContent = found.flag;
            codeEl.textContent = found.code;
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    initPhoneDropdown('phoneExtBtn','phoneExtFlag','phoneExtCode','phoneExtVal','phoneExtDropdown','phoneExtList');
});

function togglePwd(inputId, iconId) {
    var input = document.getElementById(inputId);
    var isText = input.type === 'text';
    input.type = isText ? 'password' : 'text';
    document.getElementById(iconId).style.opacity = isText ? '1' : '0.5';
}

/* ── Admin Form Validation ── */
(function() {
    var form = document.querySelector('form');
    if (!form) return;

    function getField(n) {
        return document.getElementById(n) || form.querySelector('[name="' + n + '"]');
    }

    function showFieldError(el, msg) {
        var container = el.closest ? (el.closest('.form-group') || el.parentElement) : el.parentElement;
        el.classList.add('is-error', 'js-marked');
        if (container.querySelector('.js-err')) return;
        var s = document.createElement('span');
        s.className = 'form-error js-err';
        s.textContent = msg;
        container.appendChild(s);
    }

    function clearErrors() {
        form.querySelectorAll('.js-err').forEach(function(e) { e.remove(); });
        form.querySelectorAll('.js-marked').forEach(function(e) { e.classList.remove('is-error', 'js-marked'); });
    }

    function isEmail(v) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v.trim()); }

    form.addEventListener('submit', function(e) {
        clearErrors();
        var first = null;

        function req(name, msg) {
            var el = getField(name);
            if (!el || el.value.trim()) return;
            showFieldError(el, msg);
            if (!first) first = el;
        }

        req('name',     'Full name is required.');
        req('username', 'Username is required.');
        req('email',    'Email address is required.');

        var eEl = getField('email');
        if (eEl && eEl.value.trim() && !isEmail(eEl.value)) {
            showFieldError(eEl, 'Please enter a valid email address.');
            if (!first) first = eEl;
        }

        var pEl  = getField('password');
        var pcEl = getField('password_confirmation');
        if (pEl && pEl.value) {
            if (pEl.value.length < 8) {
                showFieldError(pEl, 'Password must be at least 8 characters.');
                if (!first) first = pEl;
            } else if (pEl.value !== pcEl.value) {
                showFieldError(pcEl, 'Passwords do not match.');
                if (!first) first = pcEl;
            }
        }

        if (first) {
            e.preventDefault();
            first.scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(function() { try { first.focus(); } catch(x) {} }, 350);
        }
    });
})();
</script>
@endsection
