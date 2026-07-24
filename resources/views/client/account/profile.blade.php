@extends('client.layouts.app')
@section('title', 'Edit Profile')
@section('page-title', 'Edit Profile')

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

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
        <div class="form-group" style="margin-bottom:0;">
            <label class="form-label" for="email">{{ __('Email Address') }}</label>
            <input id="email" name="email" type="email" class="form-input {{ $errors->has('email') ? 'has-error' : '' }}"
                   placeholder="{{ __('you@example.com') }}" value="{{ old('email', $user->email) }}">
            @error('email') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group" style="margin-bottom:0;">
            <label class="form-label" for="phone">{{ __('Phone Number') }}</label>
            <input id="phone" name="phone" type="tel" class="form-input {{ $errors->has('phone') ? 'has-error' : '' }}"
                   placeholder="{{ __('07xxxxxxxx') }}" value="{{ old('phone', $user->phone) }}">
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
