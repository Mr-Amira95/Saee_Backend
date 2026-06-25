@extends('client.layouts.app')
@section('title', 'Change Password')
@section('page-title', 'Change Password')

@section('content')

<div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
    <a href="{{ route('client.account.index') }}" class="btn-secondary" style="padding:7px 14px;font-size:.82rem;">{{ __('← Back') }}</a>
    <div>
        <h1 style="font-size:1.3rem;font-weight:800;">{{ __('Change Password') }}</h1>
        <p style="font-size:.82rem;color:var(--text-sub);">{{ __('Update your account password') }}</p>
    </div>
</div>

@if(session('success'))
<div class="flash flash-ok" style="margin-bottom:16px;">{{ session('success') }}</div>
@endif

<form method="POST" action="{{ route('client.account.password.update') }}">
@csrf

<div class="card" style="margin-bottom:20px;">
    <div style="font-size:.76rem;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:.1em;margin-bottom:18px;">{{ __('Password') }}</div>

    <div class="form-group">
        <label class="form-label" for="current_password">{{ __('Current Password *') }}</label>
        <input id="current_password" name="current_password" type="password"
               class="form-input {{ $errors->has('current_password') ? 'has-error' : '' }}"
               placeholder="{{ __('Enter your current password') }}" autocomplete="current-password" required>
        @error('current_password') <div class="form-error">{{ $message }}</div> @enderror
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
        <div class="form-group" style="margin-bottom:0;">
            <label class="form-label" for="password">{{ __('New Password *') }}</label>
            <input id="password" name="password" type="password"
                   class="form-input {{ $errors->has('password') ? 'has-error' : '' }}"
                   placeholder="{{ __('Min. 8 characters') }}" autocomplete="new-password" required>
            @error('password') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group" style="margin-bottom:0;">
            <label class="form-label" for="password_confirmation">{{ __('Confirm New Password *') }}</label>
            <input id="password_confirmation" name="password_confirmation" type="password"
                   class="form-input" placeholder="{{ __('Repeat new password') }}" autocomplete="new-password" required>
        </div>
    </div>
</div>

<button type="submit" class="btn-primary" style="width:100%;justify-content:center;padding:13px 24px;font-size:.92rem;">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
    {{ __('Update Password') }}
</button>

</form>

@endsection
