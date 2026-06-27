@extends('client.layouts.app')
@section('title', __('Edit User'))
@section('page-title', __('Edit User'))

@section('content')

<div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
    <a href="{{ route('client.users.index') }}" style="color:var(--text-dim);text-decoration:none;display:inline-flex;align-items:center;gap:5px;font-size:.85rem;">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        {{ __('Team') }}
    </a>
    <span style="color:var(--text-dim);">/</span>
    <span style="font-size:.85rem;color:var(--text-sub);">{{ __('Edit User') }}</span>
</div>

<div style="max-width:560px;">
    <div class="card">
        <h2 style="font-size:1rem;font-weight:700;margin-bottom:20px;">{{ __('Edit Team Member') }}</h2>

        @if($errors->any())
        <div class="alert alert-danger" style="margin-bottom:16px;">
            <ul style="margin:0;padding-left:16px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('client.users.update', $employee->id) }}">
            @csrf
            @method('PUT')

            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:.8rem;color:var(--text-dim);margin-bottom:6px;">{{ __('Full Name') }} <span style="color:#f87171;">*</span></label>
                <input type="text" name="name" value="{{ old('name', $employee->user->name) }}" required class="form-control" placeholder="{{ __('e.g. Ahmed Ali') }}">
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:.8rem;color:var(--text-dim);margin-bottom:6px;">{{ __('Phone') }} <span style="color:#f87171;">*</span></label>
                <input type="text" name="phone" value="{{ old('phone', $employee->user->phone) }}" required class="form-control" placeholder="{{ __('e.g. 0791234567') }}">
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:.8rem;color:var(--text-dim);margin-bottom:6px;">{{ __('Email') }}</label>
                <input type="email" name="email" value="{{ old('email', $employee->user->email) }}" class="form-control" placeholder="{{ __('Optional') }}">
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:.8rem;color:var(--text-dim);margin-bottom:6px;">{{ __('Job Title') }}</label>
                <input type="text" name="job_title" value="{{ old('job_title', $employee->job_title) }}" class="form-control" placeholder="{{ __('e.g. Operations Manager') }}">
            </div>

            <div style="background:rgba(243,244,246,0.5);padding:14px;border-radius:6px;border:1px solid var(--bdr);margin-bottom:16px;">
                <h3 style="font-size:0.85rem;font-weight:700;margin-top:0;margin-bottom:4px;color:var(--text-main);">{{ __('Change Password') }}</h3>
                <p style="font-size:0.75rem;color:var(--text-dim);margin-top:0;margin-bottom:12px;">{{ __('Leave blank if you do not want to change the password.') }}</p>
                
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div>
                        <label style="display:block;font-size:.8rem;color:var(--text-dim);margin-bottom:6px;">{{ __('New Password') }}</label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••">
                    </div>
                    <div>
                        <label style="display:block;font-size:.8rem;color:var(--text-dim);margin-bottom:6px;">{{ __('Confirm New Password') }}</label>
                        <input type="password" name="password_confirmation" class="form-control" placeholder="••••••••">
                    </div>
                </div>
            </div>

            <div style="display:flex;gap:10px;margin-top:24px;">
                <button type="submit" class="btn-primary">{{ __('Save Changes') }}</button>
                <a href="{{ route('client.users.index') }}" class="btn-secondary" style="text-decoration:none;">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
</div>

@endsection
