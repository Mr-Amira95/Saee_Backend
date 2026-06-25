@extends('client.layouts.auth')
@section('title', 'Sign In')

@section('form')
<div class="card">
    <div class="card-logo">
        <img src="{{ asset('saee_logo_dark.png') }}" alt="Sa'ee" width="44" height="44" style="object-fit:contain;border-radius:10px;">
    </div>

    <span class="badge-portal"><span class="badge-dot"></span>{{ __('Client Portal') }}</span>
    <h1>{{ __('Welcome back') }}</h1>
    <p class="sub">{{ __('Sign in to manage your shipments') }}</p>

    @if($errors->any())
        <div class="alert alert-err">{{ $errors->first() }}</div>
    @endif
    @if(session('status'))
        <div class="alert alert-ok">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('client.login') }}" id="loginForm" novalidate>
        @csrf

        <div class="field f1">
            <label class="field-label" for="phone">{{ __('Phone Number') }}</label>
            <div class="field-wrap">
                <svg class="field-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
                <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" placeholder="{{ __('07xxxxxxxx') }}" autocomplete="tel" autofocus class="{{ $errors->has('phone') ? 'has-error' : '' }}">
            </div>
            @error('phone') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="field f2">
            <label class="field-label" for="password">{{ __('Password') }}</label>
            <div class="field-wrap">
                <svg class="field-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <input id="password" name="password" type="password" placeholder="{{ __('••••••••••') }}" autocomplete="current-password">
                <button type="button" class="pwd-btn" id="pwdToggle" aria-label="{{ __('Toggle password') }}">
                    <svg id="eyeIcon" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="extras f3">
            <span></span>
            <a href="{{ route('client.forgot-password') }}" class="forgot">{{ __('Forgot password?') }}</a>
        </div>

        <button type="submit" class="btn f4" id="submitBtn">
            <div class="spinner"></div>
            <span class="btn-text">{{ __('Sign In') }}</span>
            <svg class="btn-arrow" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
        </button>
    </form>

    <p class="card-footer f5">&copy; {{ date('Y') }} Sa'ee Logistic Services. {{ __('All rights reserved.') }}</p>
</div>
@endsection

@push('scripts')
<script>
const pwdInput  = document.getElementById('password');
const pwdToggle = document.getElementById('pwdToggle');
const eyeIcon   = document.getElementById('eyeIcon');
const eyeOpen   = `<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>`;
const eyeClosed = `<path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>`;
pwdToggle.addEventListener('click', () => {
    const isText = pwdInput.type === 'text';
    pwdInput.type = isText ? 'password' : 'text';
    eyeIcon.innerHTML = isText ? eyeOpen : eyeClosed;
});
document.getElementById('loginForm').addEventListener('submit', () => {
    document.getElementById('submitBtn').classList.add('loading');
});
</script>
@endpush
