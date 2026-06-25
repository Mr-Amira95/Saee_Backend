@extends('client.layouts.app')
@section('title', 'Edit Profile')
@section('page-title', 'Edit Profile')

@section('content')

<div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
    <a href="{{ route('client.account.index') }}" class="btn-secondary" style="padding:7px 14px;font-size:.82rem;">← Back</a>
    <div>
        <h1 style="font-size:1.3rem;font-weight:800;">Edit Profile</h1>
        <p style="font-size:.82rem;color:var(--text-sub);">Update your personal information</p>
    </div>
</div>

@if(session('success'))
<div class="flash flash-ok" style="margin-bottom:16px;">{{ session('success') }}</div>
@endif

<form method="POST" action="{{ route('client.account.profile.update') }}">
@csrf

<div class="card" style="margin-bottom:16px;">
    <div style="font-size:.76rem;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:.1em;margin-bottom:18px;">Personal Information</div>

    <div class="form-group">
        <label class="form-label" for="name">Full Name *</label>
        <input id="name" name="name" type="text" class="form-input {{ $errors->has('name') ? 'has-error' : '' }}"
               placeholder="Your full name" value="{{ old('name', $user->name) }}" required>
        @error('name') <div class="form-error">{{ $message }}</div> @enderror
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
        <div class="form-group" style="margin-bottom:0;">
            <label class="form-label" for="email">Email Address</label>
            <input id="email" name="email" type="email" class="form-input {{ $errors->has('email') ? 'has-error' : '' }}"
                   placeholder="you@example.com" value="{{ old('email', $user->email) }}">
            @error('email') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group" style="margin-bottom:0;">
            <label class="form-label" for="phone">Phone Number</label>
            <input id="phone" name="phone" type="tel" class="form-input {{ $errors->has('phone') ? 'has-error' : '' }}"
                   placeholder="07xxxxxxxx" value="{{ old('phone', $user->phone) }}">
            @error('phone') <div class="form-error">{{ $message }}</div> @enderror
        </div>
    </div>
</div>

<button type="submit" class="btn-primary" style="width:100%;justify-content:center;padding:13px 24px;font-size:.92rem;">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
    Save Changes
</button>

</form>

@endsection
