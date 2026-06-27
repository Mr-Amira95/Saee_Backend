@extends('client.layouts.app')
@section('title', __('Add User'))
@section('page-title', __('Add User'))

@section('content')

<div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
    <a href="{{ route('client.users.index') }}" class="btn-secondary" style="padding:7px 14px;font-size:.82rem;">{{ __('← Back to Team') }}</a>
    <div>
        <h1 style="font-size:1.3rem;font-weight:800;">{{ __('Add Team Member') }}</h1>
        <p style="font-size:.82rem;color:var(--text-sub);">{{ __('Invite a new employee to access the client portal') }}</p>
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

<form method="POST" action="{{ route('client.users.store') }}">
    @csrf

    <div class="card" style="margin-bottom:20px; max-width:650px;">
        <div style="font-size:.76rem;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:.1em;margin-bottom:18px;">{{ __('Employee Information') }}</div>

        <div class="form-group">
            <label class="form-label" for="name">{{ __('Full Name *') }}</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required class="form-input {{ $errors->has('name') ? 'has-error' : '' }}" placeholder="{{ __('e.g. Ahmed Ali') }}">
            @error('name') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
            <div class="form-group">
                <label class="form-label" for="phone">{{ __('Phone Number *') }}</label>
                <input id="phone" type="text" name="phone" value="{{ old('phone') }}" required class="form-input {{ $errors->has('phone') ? 'has-error' : '' }}" placeholder="{{ __('e.g. 0791234567') }}">
                @error('phone') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="email">{{ __('Email Address *') }}</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required class="form-input {{ $errors->has('email') ? 'has-error' : '' }}" placeholder="{{ __('e.g. employee@company.com') }}">
                @error('email') <div class="form-error">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="job_title">{{ __('Job Title') }}</label>
            <input id="job_title" type="text" name="job_title" value="{{ old('job_title') }}" class="form-input {{ $errors->has('job_title') ? 'has-error' : '' }}" placeholder="{{ __('e.g. Operations Manager') }}">
            @error('job_title') <div class="form-error">{{ $message }}</div> @enderror
        </div>


    </div>

    <div style="display:flex;gap:10px;max-width:650px;">
        <button type="submit" class="btn-primary" style="flex:1;justify-content:center;">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            {{ __('Create & Invite User') }}
        </button>
        <a href="{{ route('client.users.index') }}" class="btn-secondary" style="text-decoration:none;">{{ __('Cancel') }}</a>
    </div>
</form>

@endsection
