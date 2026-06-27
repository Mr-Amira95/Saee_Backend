@extends('client.layouts.app')
@section('title', __('Add User'))
@section('page-title', __('Add User'))

@section('content')

<div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
    <a href="{{ route('client.users.index') }}" style="color:var(--text-dim);text-decoration:none;display:inline-flex;align-items:center;gap:5px;font-size:.85rem;">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        {{ __('Team') }}
    </a>
    <span style="color:var(--text-dim);">/</span>
    <span style="font-size:.85rem;color:var(--text-sub);">{{ __('Add User') }}</span>
</div>

<div style="max-width:560px;">
    <div class="card">
        <h2 style="font-size:1rem;font-weight:700;margin-bottom:20px;">{{ __('New Team Member') }}</h2>

        @if($errors->any())
        <div class="alert alert-danger" style="margin-bottom:16px;">
            <ul style="margin:0;padding-left:16px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('client.users.store') }}">
            @csrf

            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:.8rem;color:var(--text-dim);margin-bottom:6px;">{{ __('Full Name') }} <span style="color:#f87171;">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required class="form-control" placeholder="{{ __('e.g. Ahmed Ali') }}">
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:.8rem;color:var(--text-dim);margin-bottom:6px;">{{ __('Phone') }} <span style="color:#f87171;">*</span></label>
                <input type="text" name="phone" value="{{ old('phone') }}" required class="form-control" placeholder="{{ __('e.g. 0791234567') }}">
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:.8rem;color:var(--text-dim);margin-bottom:6px;">{{ __('Email') }}</label>
                <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="{{ __('Optional') }}">
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:.8rem;color:var(--text-dim);margin-bottom:6px;">{{ __('Job Title') }}</label>
                <input type="text" name="job_title" value="{{ old('job_title') }}" class="form-control" placeholder="{{ __('e.g. Operations Manager') }}">
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px;">
                <div>
                    <label style="display:block;font-size:.8rem;color:var(--text-dim);margin-bottom:6px;">{{ __('Password') }} <span style="color:#f87171;">*</span></label>
                    <input type="password" name="password" required class="form-control" placeholder="••••••••">
                </div>
                <div>
                    <label style="display:block;font-size:.8rem;color:var(--text-dim);margin-bottom:6px;">{{ __('Confirm Password') }} <span style="color:#f87171;">*</span></label>
                    <input type="password" name="password_confirmation" required class="form-control" placeholder="••••••••">
                </div>
            </div>

            <div style="display:flex;gap:10px;margin-top:24px;">
                <button type="submit" class="btn-primary">{{ __('Create User') }}</button>
                <a href="{{ route('client.users.index') }}" class="btn-secondary" style="text-decoration:none;">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
</div>

@endsection
