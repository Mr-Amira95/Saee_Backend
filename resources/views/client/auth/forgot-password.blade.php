@extends('client.layouts.auth')
@section('title', 'Reset Password')

@section('form')
<div class="card">
    <div class="card-logo">
        <img src="{{ asset('saee_logo_dark.png') }}" alt="Sa'ee" width="44" height="44" style="object-fit:contain;border-radius:10px;">
    </div>

    <span class="badge-portal"><span class="badge-dot"></span>Password Reset</span>

    @if($errors->any())
        <div class="alert alert-err">{{ $errors->first() }}</div>
    @endif
    @if(session('status'))
        <div class="alert alert-ok">{{ session('status') }}</div>
    @endif

    {{-- STEP 1: Enter phone --}}
    <div id="step1" style="{{ session('step') === 'verify' || session('step') === 'reset' ? 'display:none' : '' }}">
        <h1>Reset Password</h1>
        <p class="sub">Enter your phone number to receive a reset code</p>

        <form method="POST" action="{{ route('client.forgot-password.request') }}" id="form1" novalidate>
            @csrf

            <div class="field f1">
                <label class="field-label" for="phone1">Phone Number</label>
                <div class="field-wrap">
                    <svg class="field-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    <input id="phone1" name="phone" type="tel" value="{{ old('phone', session('_fp_phone')) }}" placeholder="07xxxxxxxx" autocomplete="tel" autofocus>
                </div>
            </div>

            <button type="submit" class="btn f2" id="btn1">
                <div class="spinner"></div>
                <span class="btn-text">Send Reset Code</span>
                <svg class="btn-arrow" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </button>
        </form>

        <p class="card-footer f3"><a href="{{ route('client.login') }}">← Back to Sign In</a></p>
    </div>

    {{-- STEP 2: Enter code --}}
    <div id="step2" style="{{ session('step') === 'verify' ? '' : 'display:none' }}">
        <h1>Enter Code</h1>
        <p class="sub">Enter the 6-digit code sent to your phone</p>

        @if(config('app.debug') && session('_fp_code_preview'))
            <div class="alert alert-ok" style="font-family:monospace;">DEV: code = {{ session('_fp_code_preview') }}</div>
        @endif

        <form method="POST" action="{{ route('client.forgot-password.verify') }}" id="form2" novalidate>
            @csrf
            <input type="hidden" name="phone" value="{{ session('_fp_phone') }}">

            <div class="field f1">
                <label class="field-label" for="code">Verification Code</label>
                <div class="field-wrap">
                    <svg class="field-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <input id="code" name="code" type="number" placeholder="000000" maxlength="6" autofocus style="letter-spacing:.3em;text-align:center;padding-left:44px;padding-right:12px;">
                </div>
            </div>

            <button type="submit" class="btn f2" id="btn2">
                <div class="spinner"></div>
                <span class="btn-text">Verify Code</span>
                <svg class="btn-arrow" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </button>
        </form>

        <p class="card-footer f3">
            <a href="{{ route('client.forgot-password') }}">← Start over</a>
        </p>
    </div>

    {{-- STEP 3: New password --}}
    <div id="step3" style="{{ session('step') === 'reset' ? '' : 'display:none' }}">
        <h1>New Password</h1>
        <p class="sub">Choose a strong password for your account</p>

        <form method="POST" action="{{ route('client.forgot-password.reset') }}" id="form3" novalidate>
            @csrf
            <input type="hidden" name="phone" value="{{ session('_fp_phone') }}">

            <div class="field f1">
                <label class="field-label" for="newpwd">New Password</label>
                <div class="field-wrap">
                    <svg class="field-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <input id="newpwd" name="password" type="password" placeholder="Min 8 characters" autocomplete="new-password">
                </div>
            </div>

            <div class="field f2">
                <label class="field-label" for="confirmpwd">Confirm Password</label>
                <div class="field-wrap">
                    <svg class="field-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <input id="confirmpwd" name="password_confirmation" type="password" placeholder="Repeat password" autocomplete="new-password">
                </div>
            </div>

            <button type="submit" class="btn f3" id="btn3">
                <div class="spinner"></div>
                <span class="btn-text">Reset Password</span>
                <svg class="btn-arrow" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </button>
        </form>

        <p class="card-footer" style="margin-top:22px;"><a href="{{ route('client.forgot-password') }}">← Start over</a></p>
    </div>
</div>
@endsection

@push('scripts')
<script>
['form1','form2','form3'].forEach(id => {
    const form = document.getElementById(id);
    if (form) form.addEventListener('submit', (e) => {
        const btn = form.querySelector('button[type=submit]');
        if (btn) btn.classList.add('loading');
    });
});
</script>
@endpush
