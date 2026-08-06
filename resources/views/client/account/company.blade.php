@extends('client.layouts.app')
@section('title', 'Company Information')
@section('page-title', 'Company Information')

@push('styles')
<style>
    .acct-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    @media (max-width: 600px) { .acct-grid-2 { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')

<div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
    <a href="{{ route('client.account.index') }}" class="btn-secondary" style="padding:7px 14px;font-size:.82rem;">{{ __('← Back') }}</a>
    <div>
        <h1 style="font-size:1.3rem;font-weight:800;">{{ __('Company Information') }}</h1>
        <p style="font-size:.82rem;color:var(--text-sub);">{{ __('Business registration and contact details') }}</p>
    </div>
</div>

@if(auth()->user()->role === 'client_employee')
<div class="flash flash-info" style="margin-bottom:16px;">{{ __('Company information can only be edited by the account owner.') }}</div>
@endif

<form method="POST" action="{{ route('client.account.company.update') }}" id="companyForm" novalidate>
@csrf

<div class="card" style="margin-bottom:16px;">
    <div style="font-size:.76rem;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:.1em;margin-bottom:18px;">{{ __('Business Identity') }}</div>

    <div class="form-group">
        <label class="form-label" for="company_name">{{ __('Company Name *') }}</label>
        <input id="company_name" name="company_name" type="text" class="form-input {{ $errors->has('company_name') ? 'has-error' : '' }}"
               placeholder="{{ __('Your registered company name') }}" value="{{ old('company_name', $profile->company_name) }}"
               {{ auth()->user()->role === 'client_employee' ? 'disabled' : '' }}>
        @error('company_name') <div class="form-error">{{ $message }}</div> @enderror
    </div>

    <div class="acct-grid-2">
        <div class="form-group">
            <label class="form-label" for="commercial_register_number">{{ __('Commercial Register No.') }}</label>
            <input id="commercial_register_number" name="commercial_register_number" type="text" inputmode="numeric" pattern="[0-9]*" title="{{ __('Numbers only') }}" class="form-input {{ $errors->has('commercial_register_number') ? 'has-error' : '' }}"
                   placeholder="000000000" value="{{ old('commercial_register_number', $profile->commercial_register_number) }}"
                   style="font-family:monospace;"
                   {{ auth()->user()->role === 'client_employee' ? 'disabled' : '' }}>
            @error('commercial_register_number') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="vat_number">{{ __('VAT Number') }}</label>
            <input id="vat_number" name="vat_number" type="text" inputmode="numeric" pattern="[0-9]*" title="{{ __('Numbers only') }}" class="form-input {{ $errors->has('vat_number') ? 'has-error' : '' }}"
                   placeholder="JO000000000" value="{{ old('vat_number', $profile->vat_number) }}"
                   style="font-family:monospace;"
                   {{ auth()->user()->role === 'client_employee' ? 'disabled' : '' }}>
            @error('vat_number') <div class="form-error">{{ $message }}</div> @enderror
        </div>
    </div>
</div>

<div class="card" style="margin-bottom:16px;">
    <div style="font-size:.76rem;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:.1em;margin-bottom:18px;">{{ __('Contact') }}</div>

    <div class="acct-grid-2">
        <div class="form-group">
            <label class="form-label" for="email">{{ __('Company Email') }}</label>
            <input id="email" name="email" type="email" class="form-input {{ $errors->has('email') ? 'has-error' : '' }}"
                   placeholder="{{ __('info@yourcompany.com') }}" value="{{ old('email', $profile->email) }}"
                   {{ auth()->user()->role === 'client_employee' ? 'disabled' : '' }}>
            @error('email') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="company_phone">{{ __('Company Phone') }}</label>
            <input id="company_phone" name="company_phone" type="tel" class="form-input {{ $errors->has('company_phone') ? 'has-error' : '' }}"
                   placeholder="{{ __('07xxxxxxxx') }}" value="{{ old('company_phone', $profile->company_phone) }}"
                   {{ auth()->user()->role === 'client_employee' ? 'disabled' : '' }}>
            @error('company_phone') <div class="form-error">{{ $message }}</div> @enderror
        </div>
    </div>
</div>

<div class="card" style="margin-bottom:20px;">
    <div style="font-size:.76rem;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:.1em;margin-bottom:18px;">{{ __('Location') }}</div>

    <div class="acct-grid-2">
        <div class="form-group">
            <label class="form-label" for="city_id">{{ __('City') }}</label>
            <select id="city_id" name="city_id" class="form-select {{ $errors->has('city_id') ? 'has-error' : '' }}"
                    onchange="loadAreas(this.value)"
                    {{ auth()->user()->role === 'client_employee' ? 'disabled' : '' }}>
                <option value="">{{ __('Select city…') }}</option>
                @foreach($cities as $city)
                    <option value="{{ $city->id }}" {{ old('city_id', $profile->city_id) == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                @endforeach
            </select>
            @error('city_id') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="area_id">{{ __('Area') }}</label>
            <select id="area_id" name="area_id" class="form-select {{ $errors->has('area_id') ? 'has-error' : '' }}"
                    {{ auth()->user()->role === 'client_employee' ? 'disabled' : '' }}>
                <option value="">{{ __('Select area…') }}</option>
                @if($profile->area)
                    <option value="{{ $profile->area_id }}" selected>{{ $profile->area->name }}</option>
                @endif
            </select>
            @error('area_id') <div class="form-error">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="form-group" style="margin-bottom:0;">
        <label class="form-label" for="address_line1">{{ __('Company Address Line') }}</label>
        <textarea id="address_line1" name="address_line1" class="form-textarea {{ $errors->has('address_line1') ? 'has-error' : '' }}"
                  placeholder="{{ __('Street, building, floor…') }}"
                  {{ auth()->user()->role === 'client_employee' ? 'disabled' : '' }}>{{ old('address_line1', $profile->address_line1) }}</textarea>
        @error('address_line1') <div class="form-error">{{ $message }}</div> @enderror
    </div>
</div>

@if(auth()->user()->role !== 'client_employee')
<button type="submit" class="btn-primary" style="width:100%;justify-content:center;padding:13px 24px;font-size:.92rem;">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
    {{ __('Save Company Information') }}
</button>
@endif

</form>

@endsection

@push('scripts')
<script>
const preCity = '{{ old('city_id', $profile->city_id) }}';
const preArea = '{{ old('area_id', $profile->area_id) }}';

function loadAreas(cityId, preselect) {
    const sel = document.getElementById('area_id');
    if (!cityId) { sel.innerHTML = '<option value="">{{ __('Select area…') }}</option>'; return; }
    sel.innerHTML = '<option value="">{{ __('Loading…') }}</option>';

    fetch(`{{ route('client.api.areas') }}?city_id=${cityId}`, {
        headers: { 'Accept': 'application/json' }
    }).then(r => r.json()).then(areas => {
        sel.innerHTML = '<option value="">{{ __('Select area…') }}</option>';
        areas.forEach(a => {
            const opt = document.createElement('option');
            opt.value = a.id; opt.textContent = a.name;
            if (preselect && String(a.id) === String(preselect)) opt.selected = true;
            sel.appendChild(opt);
        });
    }).catch(() => { sel.innerHTML = '<option value="">{{ __('Error loading areas') }}</option>'; });
}

if (preCity) loadAreas(preCity, preArea);

/* ── Company Form Validation ── */
(function() {
    var form = document.getElementById('companyForm');
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

    function isDigits(v) { return /^[0-9]+$/.test(v.trim()); }
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

    wireLiveValidation('commercial_register_number', isDigits, '{{ __('The commercial registration number must contain numbers only.') }}');
    wireLiveValidation('vat_number', isDigits, '{{ __('The VAT number must contain numbers only.') }}');
    wireLiveValidation('email', isEmail, '{{ __('Please enter a valid email address in the format name@domain.com.') }}');
    wireLiveValidation('company_phone', isValidPhone, '{{ __('Company phone must contain 6 to 15 digits only.') }}');

    form.addEventListener('submit', function(e) {
        form.querySelectorAll('.js-marked').forEach(function(el) { clearFieldError(el); });
        var first = null;

        [
            ['commercial_register_number', isDigits, '{{ __('The commercial registration number must contain numbers only.') }}'],
            ['vat_number', isDigits, '{{ __('The VAT number must contain numbers only.') }}'],
            ['email', isEmail, '{{ __('Please enter a valid email address in the format name@domain.com.') }}'],
            ['company_phone', isValidPhone, '{{ __('Company phone must contain 6 to 15 digits only.') }}'],
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
