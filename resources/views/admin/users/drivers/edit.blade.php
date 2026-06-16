@extends('admin.layouts.app')

@section('title', 'Edit – '.($driver->user->name ?? 'Driver'))

@section('page-title', 'Edit Driver')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <span>/</span>
    <a href="{{ route('admin.drivers.index') }}">Drivers</a>
    <span>/</span>
    <a href="{{ route('admin.drivers.show', $driver) }}">{{ $driver->user->name ?? '—' }}</a>
    <span>/</span>
    <span>Edit</span>
@endsection

@section('head')
<style>
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
    border-radius: 10px; box-shadow: 0 8px 30px rgba(0,0,0,.35);
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
<form method="POST" action="{{ route('admin.drivers.update', $driver) }}" enctype="multipart/form-data" novalidate>
    @csrf
    @method('PUT')

    {{-- Account --}}
    <div class="form-section" style="position:relative;z-index:2;">
        <div class="form-section-title">Account Credentials</div>
        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label" for="name">Full Name <span class="req">*</span></label>
                <input class="form-input @error('name') is-error @enderror" id="name" type="text" name="name" value="{{ old('name', $driver->user->name) }}" required>
                @error('name')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="email">Email <span class="req">*</span></label>
                <input class="form-input @error('email') is-error @enderror" id="email" type="email" name="email" value="{{ old('email', $driver->user->email) }}" required>
                @error('email')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="password">New Password <span class="opt">(leave blank to keep)</span></label>
                <input class="form-input @error('password') is-error @enderror" id="password" type="password" name="password">
                @error('password')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="password_confirmation">Confirm New Password</label>
                <input class="form-input" id="password_confirmation" type="password" name="password_confirmation">
            </div>
            <div class="form-group" style="position:relative;">
                <label class="form-label">Phone <span class="opt">(optional)</span></label>
                <div class="phone-wrap">
                    <button type="button" class="phone-ext-btn" id="phoneExtBtn">
                        <span class="flag" id="phoneExtFlag">🇯🇴</span>
                        <span class="code" id="phoneExtCode">+962</span>
                        <span class="arrow">▼</span>
                    </button>
                    <input type="hidden" name="phone_country_code" id="phoneExtVal"
                           value="{{ old('phone_country_code', $driver->phone_country_code ?? '+962') }}">
                    <input type="text" name="phone" class="phone-input-field"
                           value="{{ old('phone', $driver->user->phone) }}" placeholder="7X XXX XXXX">
                </div>
                <div class="phone-dropdown" id="phoneExtDropdown">
                    <input type="text" class="phone-dd-search" placeholder="Search country or code…">
                    <div class="phone-dd-list" id="phoneExtList"></div>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label" for="user_status">Account Status</label>
                <select class="form-select" id="user_status" name="user_status">
                    <option value="active"    {{ old('user_status', $driver->user->status) === 'active'    ? 'selected' : '' }}>Active</option>
                    <option value="suspended" {{ old('user_status', $driver->user->status) === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    <option value="pending"   {{ old('user_status', $driver->user->status) === 'pending'   ? 'selected' : '' }}>Pending</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Identity & License --}}
    <div class="form-section">
        <div class="form-section-title">Identity &amp; License</div>
        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label" for="national_id">National ID <span class="req">*</span></label>
                <input class="form-input @error('national_id') is-error @enderror" id="national_id" type="text" name="national_id" value="{{ old('national_id', $driver->national_id) }}" required>
                @error('national_id')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="license_number">License Number <span class="req">*</span></label>
                <input class="form-input @error('license_number') is-error @enderror" id="license_number" type="text" name="license_number" value="{{ old('license_number', $driver->license_number) }}" required>
                @error('license_number')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="license_expiry_date">License Expiry <span class="req">*</span></label>
                <input class="form-input @error('license_expiry_date') is-error @enderror" id="license_expiry_date" type="date" name="license_expiry_date" value="{{ old('license_expiry_date', \Carbon\Carbon::parse($driver->license_expiry_date)->format('Y-m-d')) }}" required>
                @error('license_expiry_date')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="license_class">License Class</label>
                <select class="form-select" id="license_class" name="license_class">
                    <option value="">Select…</option>
                    <option value="light"      {{ old('license_class', $driver->license_class) === 'light'      ? 'selected' : '' }}>Light</option>
                    <option value="heavy"      {{ old('license_class', $driver->license_class) === 'heavy'      ? 'selected' : '' }}>Heavy</option>
                    <option value="motorcycle" {{ old('license_class', $driver->license_class) === 'motorcycle' ? 'selected' : '' }}>Motorcycle</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="license_attachment">License Attachment <span class="opt">(optional)</span></label>
                @if($driver->license_attachment)
                <div style="margin-bottom:6px;font-size:.82rem;color:var(--text-sub);">
                    Current: <a href="{{ Storage::disk('public')->url($driver->license_attachment) }}" target="_blank" style="color:var(--red);">↗ View file</a>
                </div>
                @endif
                <input class="form-input" id="license_attachment" type="file" name="license_attachment" style="padding:6px 8px;">
                @error('license_attachment')<span class="form-error">{{ $message }}</span>@enderror
            </div>
        </div>
    </div>

    {{-- Vehicle --}}
    <div class="form-section">
        <div class="form-section-title">Vehicle Information</div>
        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label" for="vehicle_type">Vehicle Type</label>
                <input class="form-input" id="vehicle_type" type="text" name="vehicle_type" value="{{ old('vehicle_type', $driver->vehicle_type) }}">
            </div>
            <div class="form-group">
                <label class="form-label" for="vehicle_plate">Plate Number</label>
                <input class="form-input @error('vehicle_plate') is-error @enderror" id="vehicle_plate" type="text" name="vehicle_plate" value="{{ old('vehicle_plate', $driver->vehicle_plate) }}">
                @error('vehicle_plate')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="car_license_expiry">Car License Expiry <span class="opt">(optional)</span></label>
                <input class="form-input @error('car_license_expiry') is-error @enderror" id="car_license_expiry" type="date" name="car_license_expiry" value="{{ old('car_license_expiry', $driver->car_license_expiry?->format('Y-m-d')) }}">
                @error('car_license_expiry')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="car_license_attachment">Car License Attachment <span class="opt">(optional)</span></label>
                @if($driver->car_license_attachment)
                <div style="margin-bottom:6px;font-size:.82rem;color:var(--text-sub);">
                    Current: <a href="{{ Storage::disk('public')->url($driver->car_license_attachment) }}" target="_blank" style="color:var(--red);">↗ View file</a>
                </div>
                @endif
                <input class="form-input" id="car_license_attachment" type="file" name="car_license_attachment" style="padding:6px 8px;">
                @error('car_license_attachment')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="is_available">Availability</label>
                <select class="form-select" id="is_available" name="is_available">
                    <option value="1" {{ old('is_available', $driver->is_available ? '1' : '0') === '1' ? 'selected' : '' }}>Available</option>
                    <option value="0" {{ old('is_available', $driver->is_available ? '1' : '0') === '0' ? 'selected' : '' }}>Busy / Unavailable</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Salary Configuration --}}
    @php
        $salaryConfig   = $driver->activeSalaryConfig;
        $perSalary      = $salaryConfig?->activePerSalaryConfig;
        $currentType    = old('salary_type', $salaryConfig?->salary_type?->value ?? '');
    @endphp
    <div class="form-section">
        <div class="form-section-title">Salary Configuration</div>
        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label" for="salary_type">Salary Type</label>
                <select class="form-select @error('salary_type') is-error @enderror" id="salary_type" name="salary_type"
                        onchange="updateSalaryFields()">
                    <option value="">— Not configured —</option>
                    <option value="per_salary" {{ $currentType === 'per_salary' ? 'selected' : '' }}>Per Salary (Employee)</option>
                    <option value="per_order"  {{ $currentType === 'per_order'  ? 'selected' : '' }}>Per Order</option>
                </select>
                @error('salary_type')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            @if($salaryConfig)
            <div class="form-group" style="display:flex;align-items:flex-end;padding-bottom:2px;">
                <span style="font-size:.78rem;color:var(--text-dim);">
                    Active since {{ $salaryConfig->effective_from->format('d M Y') }}
                </span>
            </div>
            @endif
        </div>

        {{-- Per Salary fields --}}
        <div id="per-salary-fields" style="display:none;">
            <div class="form-grid-2" style="margin-top:12px;">
                <div class="form-group">
                    <label class="form-label" for="basic_salary">Basic Salary <span class="req">*</span></label>
                    <input class="form-input @error('basic_salary') is-error @enderror" id="basic_salary" type="number"
                           name="basic_salary" value="{{ old('basic_salary', $perSalary?->basic_salary ?? '') }}"
                           step="0.01" min="0" placeholder="0.00">
                    @error('basic_salary')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="car_allowance">Car &amp; Gasoline Allowance <span class="req">*</span></label>
                    <input class="form-input @error('car_allowance') is-error @enderror" id="car_allowance" type="number"
                           name="car_allowance" value="{{ old('car_allowance', $perSalary?->car_allowance ?? '') }}"
                           step="0.01" min="0" placeholder="0.00">
                    @error('car_allowance')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="extra_order_threshold">Order Bonus Threshold <span class="req">*</span></label>
                    <input class="form-input @error('extra_order_threshold') is-error @enderror" id="extra_order_threshold" type="number"
                           name="extra_order_threshold" value="{{ old('extra_order_threshold', $perSalary?->extra_order_threshold ?? '') }}"
                           min="0" placeholder="e.g. 80">
                    <span style="font-size:.76rem;color:var(--text-dim);margin-top:4px;display:block;">Bonus kicks in for each order above this count per period</span>
                    @error('extra_order_threshold')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="extra_order_bonus">Bonus Per Extra Order <span class="req">*</span></label>
                    <input class="form-input @error('extra_order_bonus') is-error @enderror" id="extra_order_bonus" type="number"
                           name="extra_order_bonus" value="{{ old('extra_order_bonus', $perSalary?->extra_order_bonus ?? '') }}"
                           step="0.01" min="0" placeholder="0.00">
                    @error('extra_order_bonus')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        {{-- Per Order info --}}
        <div id="per-order-info" style="display:none;margin-top:12px;">
            <div style="display:flex;align-items:center;gap:10px;background:rgba(59,130,246,.07);border:1px solid rgba(59,130,246,.18);border-radius:10px;padding:12px 16px;">
                <svg width="16" height="16" fill="none" stroke="#60a5fa" stroke-width="1.8" viewBox="0 0 24 24" style="flex-shrink:0"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01"/></svg>
                <span style="font-size:.82rem;color:#93c5fd;">Per-order rates are set globally per city and apply to all per-order drivers automatically.</span>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <a href="{{ route('admin.drivers.show', $driver) }}" class="btn-secondary">Cancel</a>
        <button type="submit" class="btn-primary">Save Changes</button>
    </div>
</form>
@endsection

@section('scripts')
<script>
const COUNTRIES = [
    { flag:'🇯🇴', name:'Jordan',               code:'+962' },
    { flag:'🇸🇦', name:'Saudi Arabia',          code:'+966' },
    { flag:'🇦🇪', name:'UAE',                   code:'+971' },
    { flag:'🇰🇼', name:'Kuwait',                code:'+965' },
    { flag:'🇧🇭', name:'Bahrain',               code:'+973' },
    { flag:'🇶🇦', name:'Qatar',                 code:'+974' },
    { flag:'🇴🇲', name:'Oman',                  code:'+968' },
    { flag:'🇮🇶', name:'Iraq',                  code:'+964' },
    { flag:'🇸🇾', name:'Syria',                 code:'+963' },
    { flag:'🇱🇧', name:'Lebanon',               code:'+961' },
    { flag:'🇵🇸', name:'Palestine',             code:'+970' },
    { flag:'🇪🇬', name:'Egypt',                 code:'+20'  },
    { flag:'🇱🇾', name:'Libya',                 code:'+218' },
    { flag:'🇹🇳', name:'Tunisia',               code:'+216' },
    { flag:'🇩🇿', name:'Algeria',               code:'+213' },
    { flag:'🇲🇦', name:'Morocco',               code:'+212' },
    { flag:'🇸🇩', name:'Sudan',                 code:'+249' },
    { flag:'🇾🇪', name:'Yemen',                 code:'+967' },
    { flag:'🇹🇷', name:'Turkey',                code:'+90'  },
    { flag:'🇮🇳', name:'India',                 code:'+91'  },
    { flag:'🇵🇰', name:'Pakistan',              code:'+92'  },
    { flag:'🇬🇧', name:'United Kingdom',        code:'+44'  },
    { flag:'🇺🇸', name:'United States',         code:'+1'   },
    { flag:'🇩🇪', name:'Germany',               code:'+49'  },
    { flag:'🇫🇷', name:'France',                code:'+33'  },
    { flag:'🇦🇺', name:'Australia',             code:'+61'  },
    { flag:'🇸🇬', name:'Singapore',             code:'+65'  },
    { flag:'🇯🇵', name:'Japan',                 code:'+81'  },
    { flag:'🇨🇳', name:'China',                 code:'+86'  },
    { flag:'🇷🇺', name:'Russia',                code:'+7'   },
    { flag:'🇿🇦', name:'South Africa',          code:'+27'  },
    { flag:'🇧🇷', name:'Brazil',                code:'+55'  },
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

function updateSalaryFields() {
    var type = document.getElementById('salary_type').value;
    document.getElementById('per-salary-fields').style.display = type === 'per_salary' ? 'block' : 'none';
    document.getElementById('per-order-info').style.display   = type === 'per_order'  ? 'block' : 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    initPhoneDropdown('phoneExtBtn','phoneExtFlag','phoneExtCode','phoneExtVal','phoneExtDropdown','phoneExtList');
    updateSalaryFields();
});

/* ── Driver Form Validation ── */
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

        req('name',                'Full name is required.');
        req('email',               'Email address is required.');
        req('national_id',         'National ID is required.');
        req('license_number',      'License number is required.');
        req('license_expiry_date', 'License expiry date is required.');

        var eEl = getField('email');
        if (eEl && eEl.value.trim() && !isEmail(eEl.value)) {
            showFieldError(eEl, 'Please enter a valid email address.');
            if (!first) first = eEl;
        }

        // Password confirmation
        var pw = getField('password'), pwc = getField('password_confirmation');
        if (pw && pwc && pw.value && pw.value !== pwc.value) {
            showFieldError(pwc, 'Password confirmation does not match.');
            if (!first) first = pwc;
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
