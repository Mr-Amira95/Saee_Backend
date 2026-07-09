@extends('admin.layouts.app')

@section('title', 'Add Employee – '.$client->company_name)
@section('page-title', 'Add Employee')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <span>/</span>
    <a href="{{ route('admin.clients.index') }}">Clients</a>
    <span>/</span>
    <a href="{{ route('admin.clients.show', $client) }}">{{ $client->company_name }}</a>
    <span>/</span>
    <span>Add Employee</span>
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

/* ── Permission cards ── */
.perm-card {
    background: var(--card); border: 1px solid var(--bdr); border-radius: 14px;
    overflow: hidden; margin-bottom: 12px; backdrop-filter: blur(6px);
}
.perm-card-hd {
    display: flex; align-items: center; justify-content: space-between;
    padding: 12px 18px; border-bottom: 1px solid var(--bdr);
    background: rgba(255,255,255,.02);
}
.perm-card-title {
    display: flex; align-items: center; gap: 8px;
    font-size: .78rem; font-weight: 700; color: var(--text-sub);
    text-transform: uppercase; letter-spacing: .07em;
}
.perm-card-title .perm-icon {
    width: 26px; height: 26px; border-radius: 7px;
    display: flex; align-items: center; justify-content: center;
    background: rgba(220,38,38,.1); font-size: .85rem;
}
.perm-card-meta { font-size: .72rem; color: var(--text-dim); font-weight: 400; text-transform: none; letter-spacing: 0; margin-left: 4px; }
.perm-card-toggle {
    font-size: .75rem; color: var(--red); background: rgba(220,38,38,.07);
    border: 1px solid rgba(220,38,38,.18); cursor: pointer; padding: 4px 12px;
    border-radius: 6px; font-weight: 600; transition: background .15s;
}
.perm-card-toggle:hover { background: rgba(220,38,38,.15); }
.perm-card-body { padding: 14px 18px; }
.perm-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
@media (max-width:600px) { .perm-grid { grid-template-columns: 1fr; } }
.perm-item {
    display: flex; align-items: flex-start; gap: 10px;
    padding: 11px 14px; background: var(--in-bg);
    border: 1px solid var(--bdr); border-radius: 10px;
    cursor: pointer; transition: border-color .15s, background .15s; user-select: none;
}
.perm-item:hover { border-color: rgba(220,38,38,.3); background: rgba(220,38,38,.03); }
.perm-item.is-checked { border-color: rgba(220,38,38,.45); background: rgba(220,38,38,.07); }
.perm-item input[type=checkbox] { accent-color: var(--red); width: 15px; height: 15px; flex-shrink: 0; margin-top: 2px; }
.perm-item-lbl { font-size: .85rem; color: var(--text); line-height: 1.3; }
</style>
@endsection

@section('content')

    @if($errors->any())
    <div style="background:rgba(220,38,38,.1);border:1px solid rgba(220,38,38,.3);border-radius:10px;padding:14px 18px;margin-bottom:20px;">
        <ul style="margin:0;padding-left:18px;color:#fca5a5;font-size:.88rem;">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

<form method="POST" action="{{ route('admin.clients.employees.store', $client) }}" novalidate>
    @csrf

    {{-- ── Account Credentials ── --}}
    <div class="form-section" style="position:relative;z-index:2;">
        <div class="form-section-title">Account Credentials</div>

        {{-- Company context banner --}}
        <div style="display:flex;align-items:center;gap:12px;background:rgba(220,38,38,.06);border:1px solid rgba(220,38,38,.15);border-radius:10px;padding:12px 16px;margin-bottom:20px;">
            <div style="width:36px;height:36px;border-radius:9px;background:linear-gradient(135deg,var(--red-dark),var(--red));display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;color:#fff;flex-shrink:0;">
                {{ strtoupper(substr($client->company_name, 0, 2)) }}
            </div>
            <div>
                <div style="font-size:.75rem;color:var(--text-dim);">Adding employee to</div>
                <div style="font-size:.92rem;font-weight:700;color:var(--text);">{{ $client->company_name }}</div>
            </div>
        </div>

        {{-- Invitation note --}}
        <div style="display:flex;align-items:center;gap:10px;background:rgba(59,130,246,.07);border:1px solid rgba(59,130,246,.18);border-radius:10px;padding:12px 16px;margin-bottom:20px;">
            <svg width="16" height="16" fill="none" stroke="#60a5fa" stroke-width="1.8" viewBox="0 0 24 24" style="flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            <span style="font-size:.82rem;color:#93c5fd;">A password setup email will be sent to the employee automatically after account creation.</span>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label" for="name">Full Name <span class="req">*</span></label>
                <input class="form-input @error('name') is-error @enderror" id="name" type="text" name="name"
                       value="{{ old('name') }}" placeholder="e.g. Sara Al-Mansouri" required>
                @error('name')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="username">Username <span class="req">*</span></label>
                <input class="form-input @error('username') is-error @enderror" id="username" type="text" name="username"
                       value="{{ old('username') }}" required>
                @error('username')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="email">Email Address <span class="opt">(required for email channel)</span></label>
                <input class="form-input @error('email') is-error @enderror" id="email" type="email" name="email"
                       value="{{ old('email') }}" placeholder="employee@company.com">
                @error('email')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Phone <span class="opt">(required for WhatsApp)</span></label>
                <div style="position:relative;">
                    <div class="phone-wrap">
                        <button type="button" class="phone-ext-btn" id="phoneExtBtn">
                            <span class="flag" id="phoneExtFlag">🇯🇴</span>
                            <span class="code" id="phoneExtCode">+962</span>
                            <span class="arrow">▼</span>
                        </button>
                        <input type="hidden" name="phone_country_code" id="phoneExtVal" value="{{ old('phone_country_code', '+962') }}">
                        <input type="text" name="phone" class="phone-input-field @error('phone') is-error @enderror"
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
                <label class="form-label" for="job_title">Job Title <span class="opt">(optional)</span></label>
                <input class="form-input @error('job_title') is-error @enderror" id="job_title" type="text" name="job_title"
                       value="{{ old('job_title') }}" placeholder="e.g. Logistics Manager, Dispatcher">
                @error('job_title')<span class="form-error">{{ $message }}</span>@enderror
            </div>
        </div>
    </div>

    {{-- ── Permissions ── --}}
    @if($permissions->isNotEmpty())
    <div class="form-section">
        <div class="form-section-title">
            Permissions
            <span class="opt" style="text-transform:none;font-size:.72rem;font-weight:400;margin-left:6px;">Choose what this employee can do</span>
        </div>

        @php
            $groupIcons = [
                'orders'          => '📦',
                'support'         => '🎧',
                'payout_invoices' => '🧾',
                'billing'         => '💳',
                'reports'         => '📊',
                'team'            => '👥',
                'account'         => '⚙️',
                'ai_assistant'    => '🤖',
            ];
        @endphp

        @foreach($permissions as $group => $perms)
        @php
            $groupKey  = 'perm_group_' . $loop->index;
            $icon      = $groupIcons[$group] ?? '🔑';
            $count     = $perms->count();
            $oldChecked = collect(old('permissions', []))->map(fn($v) => (int)$v);
            $checkedInGroup = $perms->filter(fn($p) => $oldChecked->contains($p->id))->count();
        @endphp
        <div class="perm-card" data-group="{{ $groupKey }}">
            <div class="perm-card-hd">
                <div class="perm-card-title">
                    <span class="perm-icon">{{ $icon }}</span>
                    {{ ucwords(str_replace('_', ' ', $group)) }}
                    <span class="perm-card-meta">{{ $count }} permission{{ $count !== 1 ? 's' : '' }}</span>
                </div>
                <button type="button" class="perm-card-toggle" onclick="togglePermGroup(this, '{{ $groupKey }}')"
                        data-all="{{ $checkedInGroup === $count && $count > 0 ? '1' : '0' }}">
                    {{ $checkedInGroup === $count && $count > 0 ? 'Deselect All' : 'Select All' }}
                </button>
            </div>
            <div class="perm-card-body">
                <div class="perm-grid">
                    @foreach($perms as $perm)
                    @php $isChecked = $oldChecked->contains($perm->id); @endphp
                    <label class="perm-item {{ $isChecked ? 'is-checked' : '' }}">
                        <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                               {{ $isChecked ? 'checked' : '' }}
                               onchange="onPermChange(this)">
                        <span class="perm-item-lbl">{{ $perm->display_name }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach

    </div>
    @endif

    <div class="form-actions" style="flex-wrap:wrap;gap:16px;">
        {{-- Invitation / OTP channel toggle --}}
        <label style="display:flex;align-items:center;gap:10px;cursor:pointer;margin-right:auto;user-select:none;">
            <span style="font-size:.85rem;color:var(--text-sub);white-space:nowrap;">Send invitation &amp; OTP via</span>
            <div style="position:relative;display:inline-flex;align-items:center;background:var(--in-bg);border:1px solid var(--bdr);border-radius:8px;padding:3px;gap:2px;" id="invChannelWrap">
                <input type="hidden" name="otp_channel" id="invChannelInput" value="whatsapp">
                <button type="button" id="btnWhatsapp"
                    onclick="setChannel('whatsapp')"
                    style="display:flex;align-items:center;gap:6px;padding:5px 12px;border:none;border-radius:6px;font-size:.8rem;font-weight:600;cursor:pointer;transition:background .2s,color .2s;background:#25D366;color:#fff;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    WhatsApp
                </button>
                <button type="button" id="btnEmail"
                    onclick="setChannel('email')"
                    style="display:flex;align-items:center;gap:6px;padding:5px 12px;border:none;border-radius:6px;font-size:.8rem;font-weight:600;cursor:pointer;transition:background .2s,color .2s;background:transparent;color:var(--text-sub);">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Email
                </button>
            </div>
        </label>

        <a href="{{ route('admin.clients.show', $client) }}" class="btn-secondary">Cancel</a>
        <button type="submit" class="btn-primary">Create Employee &amp; Send Invitation</button>
    </div>

    <script>
    function setChannel(ch) {
        document.getElementById('invChannelInput').value = ch;
        const wa = document.getElementById('btnWhatsapp');
        const em = document.getElementById('btnEmail');
        if (ch === 'whatsapp') {
            wa.style.background = '#25D366'; wa.style.color = '#fff';
            em.style.background = 'transparent'; em.style.color = 'var(--text-sub)';
        } else {
            em.style.background = 'var(--red)'; em.style.color = '#fff';
            wa.style.background = 'transparent'; wa.style.color = 'var(--text-sub)';
        }
    }
    </script>
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
    { flag:'🇩🇪', name:'Germany',         code:'+49'  },
    { flag:'🇫🇷', name:'France',          code:'+33'  },
    { flag:'🇦🇺', name:'Australia',       code:'+61'  },
    { flag:'🇸🇬', name:'Singapore',       code:'+65'  },
    { flag:'🇯🇵', name:'Japan',           code:'+81'  },
    { flag:'🇨🇳', name:'China',           code:'+86'  },
    { flag:'🇰🇷', name:'South Korea',     code:'+82'  },
    { flag:'🇷🇺', name:'Russia',          code:'+7'   },
    { flag:'🇿🇦', name:'South Africa',    code:'+27'  },
    { flag:'🇳🇬', name:'Nigeria',         code:'+234' },
    { flag:'🇧🇷', name:'Brazil',          code:'+55'  },
    { flag:'🇲🇽', name:'Mexico',          code:'+52'  },
];

function initPhoneDropdown(btnId, flagId, codeId, valId, ddId, listId) {
    var btn      = document.getElementById(btnId);
    var flagEl   = document.getElementById(flagId);
    var codeEl   = document.getElementById(codeId);
    var valEl    = document.getElementById(valId);
    var dd       = document.getElementById(ddId);
    var listEl   = document.getElementById(listId);
    var searchEl = dd.querySelector('.phone-dd-search');
    var initial  = valEl.value || '+962';

    var match = COUNTRIES.find(function(c) { return c.code === initial; });
    if (match) { flagEl.textContent = match.flag; codeEl.textContent = match.code; }

    function renderList(q) {
        q = (q || '').toLowerCase();
        listEl.innerHTML = '';
        COUNTRIES.filter(function(c) { return !q || c.name.toLowerCase().includes(q) || c.code.includes(q); })
            .forEach(function(c) {
                var item = document.createElement('div');
                item.className = 'phone-dd-item';
                item.innerHTML = '<span class="dd-flag">' + c.flag + '</span><span class="dd-name">' + c.name + '</span><span class="dd-code">' + c.code + '</span>';
                item.addEventListener('click', function() {
                    flagEl.textContent = c.flag; codeEl.textContent = c.code; valEl.value = c.code;
                    dd.classList.remove('open');
                });
                listEl.appendChild(item);
            });
    }
    renderList('');
    btn.addEventListener('click', function(e) { e.stopPropagation(); dd.classList.toggle('open'); if (dd.classList.contains('open')) searchEl.focus(); });
    searchEl.addEventListener('input', function() { renderList(this.value); });
    document.addEventListener('click', function(e) { if (!dd.contains(e.target) && e.target !== btn) dd.classList.remove('open'); });
}

/* ── Permission helpers ── */
function onPermChange(checkbox) {
    checkbox.closest('.perm-item').classList.toggle('is-checked', checkbox.checked);
    var card   = checkbox.closest('[data-group]');
    if (!card) return;
    var boxes  = card.querySelectorAll('input[type=checkbox]');
    var allOn  = Array.from(boxes).every(function(b) { return b.checked; });
    var btn    = card.querySelector('.perm-card-toggle');
    if (btn) { btn.textContent = allOn ? 'Deselect All' : 'Select All'; btn.dataset.all = allOn ? '1' : '0'; }
}

function togglePermGroup(btn, groupKey) {
    var card   = document.querySelector('[data-group="' + groupKey + '"]');
    if (!card) return;
    var boxes  = card.querySelectorAll('input[type=checkbox]');
    var allOn  = btn.dataset.all === '1';
    boxes.forEach(function(b) { b.checked = !allOn; b.closest('.perm-item').classList.toggle('is-checked', !allOn); });
    btn.dataset.all = allOn ? '0' : '1';
    btn.textContent = allOn ? 'Select All' : 'Deselect All';
}

/* ── Form validation ── */
(function() {
    var form = document.querySelector('form');
    if (!form) return;

    function getField(n) { return document.getElementById(n) || form.querySelector('[name="' + n + '"]'); }

    function showFieldError(el, msg) {
        var container = el.closest ? (el.closest('.form-group') || el.parentElement) : el.parentElement;
        el.classList.add('is-error', 'js-marked');
        if (container.querySelector('.js-err')) return;
        var s = document.createElement('span');
        s.className = 'form-error js-err'; s.textContent = msg;
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
            showFieldError(el, msg); if (!first) first = el;
        }
        req('name',     'Full name is required.');
        req('username', 'Username is required.');
        var eEl = getField('email');
        if (eEl && eEl.value.trim() && !isEmail(eEl.value)) {
            showFieldError(eEl, 'Please enter a valid email address.'); if (!first) first = eEl;
        }
        if (first) {
            e.preventDefault();
            first.scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(function() { try { first.focus(); } catch(x) {} }, 350);
        }
    });
})();

/* ── Init ── */
document.addEventListener('DOMContentLoaded', function() {
    initPhoneDropdown('phoneExtBtn','phoneExtFlag','phoneExtCode','phoneExtVal','phoneExtDropdown','phoneExtList');
});
</script>
@endsection
