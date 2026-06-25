@extends('client.layouts.app')
@section('title', 'Company Information')
@section('page-title', 'Company Information')

@section('content')

<div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
    <a href="{{ route('client.account.index') }}" class="btn-secondary" style="padding:7px 14px;font-size:.82rem;">← Back</a>
    <div>
        <h1 style="font-size:1.3rem;font-weight:800;">Company Information</h1>
        <p style="font-size:.82rem;color:var(--text-sub);">Business registration and contact details</p>
    </div>
</div>

@if(auth()->user()->role === 'client_employee')
<div class="flash flash-info" style="margin-bottom:16px;">Company information can only be edited by the account owner.</div>
@endif

<form method="POST" action="{{ route('client.account.company.update') }}">
@csrf

<div class="card" style="margin-bottom:16px;">
    <div style="font-size:.76rem;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:.1em;margin-bottom:18px;">Business Identity</div>

    <div class="form-group">
        <label class="form-label" for="company_name">Company Name *</label>
        <input id="company_name" name="company_name" type="text" class="form-input {{ $errors->has('company_name') ? 'has-error' : '' }}"
               placeholder="Your registered company name" value="{{ old('company_name', $profile->company_name) }}"
               {{ auth()->user()->role === 'client_employee' ? 'disabled' : '' }}>
        @error('company_name') <div class="form-error">{{ $message }}</div> @enderror
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
        <div class="form-group">
            <label class="form-label" for="commercial_register_number">Commercial Register No.</label>
            <input id="commercial_register_number" name="commercial_register_number" type="text" class="form-input {{ $errors->has('commercial_register_number') ? 'has-error' : '' }}"
                   placeholder="000000000" value="{{ old('commercial_register_number', $profile->commercial_register_number) }}"
                   style="font-family:monospace;"
                   {{ auth()->user()->role === 'client_employee' ? 'disabled' : '' }}>
            @error('commercial_register_number') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="vat_number">VAT Number</label>
            <input id="vat_number" name="vat_number" type="text" class="form-input {{ $errors->has('vat_number') ? 'has-error' : '' }}"
                   placeholder="JO000000000" value="{{ old('vat_number', $profile->vat_number) }}"
                   style="font-family:monospace;"
                   {{ auth()->user()->role === 'client_employee' ? 'disabled' : '' }}>
            @error('vat_number') <div class="form-error">{{ $message }}</div> @enderror
        </div>
    </div>
</div>

<div class="card" style="margin-bottom:16px;">
    <div style="font-size:.76rem;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:.1em;margin-bottom:18px;">Contact</div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
        <div class="form-group">
            <label class="form-label" for="email">Company Email</label>
            <input id="email" name="email" type="email" class="form-input {{ $errors->has('email') ? 'has-error' : '' }}"
                   placeholder="info@yourcompany.com" value="{{ old('email', $profile->email) }}"
                   {{ auth()->user()->role === 'client_employee' ? 'disabled' : '' }}>
            @error('email') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="company_phone">Company Phone</label>
            <input id="company_phone" name="company_phone" type="tel" class="form-input {{ $errors->has('company_phone') ? 'has-error' : '' }}"
                   placeholder="07xxxxxxxx" value="{{ old('company_phone', $profile->company_phone) }}"
                   {{ auth()->user()->role === 'client_employee' ? 'disabled' : '' }}>
            @error('company_phone') <div class="form-error">{{ $message }}</div> @enderror
        </div>
    </div>
</div>

<div class="card" style="margin-bottom:20px;">
    <div style="font-size:.76rem;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:.1em;margin-bottom:18px;">Location</div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
        <div class="form-group">
            <label class="form-label" for="city_id">City</label>
            <select id="city_id" name="city_id" class="form-select {{ $errors->has('city_id') ? 'has-error' : '' }}"
                    onchange="loadAreas(this.value)"
                    {{ auth()->user()->role === 'client_employee' ? 'disabled' : '' }}>
                <option value="">Select city…</option>
                @foreach($cities as $city)
                    <option value="{{ $city->id }}" {{ old('city_id', $profile->city_id) == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                @endforeach
            </select>
            @error('city_id') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="area_id">Area</label>
            <select id="area_id" name="area_id" class="form-select {{ $errors->has('area_id') ? 'has-error' : '' }}"
                    {{ auth()->user()->role === 'client_employee' ? 'disabled' : '' }}>
                <option value="">Select area…</option>
                @if($profile->area)
                    <option value="{{ $profile->area_id }}" selected>{{ $profile->area->name }}</option>
                @endif
            </select>
            @error('area_id') <div class="form-error">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="form-group" style="margin-bottom:0;">
        <label class="form-label" for="address_line1">Company Address Line</label>
        <textarea id="address_line1" name="address_line1" class="form-textarea {{ $errors->has('address_line1') ? 'has-error' : '' }}"
                  placeholder="Street, building, floor…"
                  {{ auth()->user()->role === 'client_employee' ? 'disabled' : '' }}>{{ old('address_line1', $profile->address_line1) }}</textarea>
        @error('address_line1') <div class="form-error">{{ $message }}</div> @enderror
    </div>
</div>

@if(auth()->user()->role !== 'client_employee')
<button type="submit" class="btn-primary" style="width:100%;justify-content:center;padding:13px 24px;font-size:.92rem;">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
    Save Company Information
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
    if (!cityId) { sel.innerHTML = '<option value="">Select area…</option>'; return; }
    sel.innerHTML = '<option value="">Loading…</option>';

    fetch(`{{ route('client.api.areas') }}?city_id=${cityId}`, {
        headers: { 'Accept': 'application/json' }
    }).then(r => r.json()).then(areas => {
        sel.innerHTML = '<option value="">Select area…</option>';
        areas.forEach(a => {
            const opt = document.createElement('option');
            opt.value = a.id; opt.textContent = a.name;
            if (preselect && String(a.id) === String(preselect)) opt.selected = true;
            sel.appendChild(opt);
        });
    }).catch(() => { sel.innerHTML = '<option value="">Error loading areas</option>'; });
}

if (preCity) loadAreas(preCity, preArea);
</script>
@endpush
