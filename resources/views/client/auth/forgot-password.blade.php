@extends('client.layouts.auth')
@section('title', 'Reset Password')

@section('form')
<div class="card">
    <div class="card-logo">
        <img src="{{ asset('saee_logo_dark.png') }}" alt="Sa'ee" width="44" height="44" style="object-fit:contain;border-radius:10px;">
    </div>

    <span class="badge-portal"><span class="badge-dot"></span>{{ __('Password Reset') }}</span>

    @if($errors->any())
        <div class="alert alert-err">{{ $errors->first() }}</div>
    @endif
    @if(session('status'))
        <div class="alert alert-ok">{{ session('status') }}</div>
    @endif

    {{-- STEP 1: Enter phone --}}
    <div id="step1" style="{{ session('step') === 'verify' || session('step') === 'reset' ? 'display:none' : '' }}">
        <h1>{{ __('Reset Password') }}</h1>
        <p class="sub">{{ __('Enter your phone number to receive a reset code') }}</p>

        <form method="POST" action="{{ route('client.forgot-password.request') }}" id="form1" novalidate>
            @csrf

            <div class="field f1">
                <label class="field-label" for="fpPhoneLocal">{{ __('Phone Number') }}</label>
                <div class="field-wrap phone-wrap">
                    <button type="button" class="country-btn" id="fpCountryBtn" aria-label="{{ __('Select country code') }}">
                        <span class="country-flag" id="fpCountryFlag">🇯🇴</span>
                        <span class="country-dial" id="fpCountryDial">+962</span>
                        <svg class="country-chevron" width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div class="country-dropdown" id="fpCountryDropdown">
                        <div class="country-option active" data-dial="+962" data-flag="🇯🇴"><span class="opt-flag">🇯🇴</span><span class="opt-name">Jordan</span><span class="opt-dial">+962</span></div>
                        <div class="country-option" data-dial="+966" data-flag="🇸🇦"><span class="opt-flag">🇸🇦</span><span class="opt-name">Saudi Arabia</span><span class="opt-dial">+966</span></div>
                        <div class="country-option" data-dial="+971" data-flag="🇦🇪"><span class="opt-flag">🇦🇪</span><span class="opt-name">UAE</span><span class="opt-dial">+971</span></div>
                        <div class="country-option" data-dial="+965" data-flag="🇰🇼"><span class="opt-flag">🇰🇼</span><span class="opt-name">Kuwait</span><span class="opt-dial">+965</span></div>
                        <div class="country-option" data-dial="+974" data-flag="🇶🇦"><span class="opt-flag">🇶🇦</span><span class="opt-name">Qatar</span><span class="opt-dial">+974</span></div>
                        <div class="country-option" data-dial="+973" data-flag="🇧🇭"><span class="opt-flag">🇧🇭</span><span class="opt-name">Bahrain</span><span class="opt-dial">+973</span></div>
                        <div class="country-option" data-dial="+968" data-flag="🇴🇲"><span class="opt-flag">🇴🇲</span><span class="opt-name">Oman</span><span class="opt-dial">+968</span></div>
                        <div class="country-option" data-dial="+20" data-flag="🇪🇬"><span class="opt-flag">🇪🇬</span><span class="opt-name">Egypt</span><span class="opt-dial">+20</span></div>
                        <div class="country-option" data-dial="+970" data-flag="🇵🇸"><span class="opt-flag">🇵🇸</span><span class="opt-name">Palestine</span><span class="opt-dial">+970</span></div>
                        <div class="country-option" data-dial="+961" data-flag="🇱🇧"><span class="opt-flag">🇱🇧</span><span class="opt-name">Lebanon</span><span class="opt-dial">+961</span></div>
                        <div class="country-option" data-dial="+964" data-flag="🇮🇶"><span class="opt-flag">🇮🇶</span><span class="opt-name">Iraq</span><span class="opt-dial">+964</span></div>
                        <div class="country-option" data-dial="+963" data-flag="🇸🇾"><span class="opt-flag">🇸🇾</span><span class="opt-name">Syria</span><span class="opt-dial">+963</span></div>
                    </div>
                    <input id="fpPhoneLocal" type="tel" placeholder="{{ __('7xxxxxxxx') }}" autocomplete="tel" autofocus>
                    <input type="hidden" name="phone" id="fpPhoneHidden">
                </div>
            </div>

            <button type="submit" class="btn f2" id="btn1">
                <div class="spinner"></div>
                <span class="btn-text">{{ __('Send Reset Code') }}</span>
                <svg class="btn-arrow" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </button>
        </form>

        <p class="card-footer f3"><a href="{{ route('client.login') }}">{{ __('← Back to Sign In') }}</a></p>
    </div>

    {{-- STEP 2: Enter code --}}
    <div id="step2" style="{{ session('step') === 'verify' ? '' : 'display:none' }}">
        <h1>{{ __('Enter Code') }}</h1>
        <p class="sub">{{ __('Enter the 6-digit code sent to your phone') }}</p>

        @if(config('app.debug') && session('_fp_code_preview'))
            <div class="alert alert-ok" style="font-family:monospace;">DEV: code = {{ session('_fp_code_preview') }}</div>
        @endif

        <form method="POST" action="{{ route('client.forgot-password.verify') }}" id="form2" novalidate>
            @csrf
            <input type="hidden" name="phone" value="{{ session('_fp_phone') }}">

            <div class="field f1">
                <label class="field-label" for="code">{{ __('Verification Code') }}</label>
                <div class="field-wrap">
                    <svg class="field-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <input id="code" name="code" type="number" placeholder="000000" maxlength="6" autofocus style="letter-spacing:.3em;text-align:center;padding-left:44px;padding-right:12px;">
                </div>
            </div>

            <button type="submit" class="btn f2" id="btn2">
                <div class="spinner"></div>
                <span class="btn-text">{{ __('Verify Code') }}</span>
                <svg class="btn-arrow" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </button>
        </form>

        <p class="card-footer f3">
            <a href="{{ route('client.forgot-password') }}">{{ __('← Start over') }}</a>
        </p>
    </div>

    {{-- STEP 3: New password --}}
    <div id="step3" style="{{ session('step') === 'reset' ? '' : 'display:none' }}">
        <h1>{{ __('New Password') }}</h1>
        <p class="sub">{{ __('Choose a strong password for your account') }}</p>

        <form method="POST" action="{{ route('client.forgot-password.reset') }}" id="form3" novalidate>
            @csrf
            <input type="hidden" name="phone" value="{{ session('_fp_phone') }}">

            <div class="field f1">
                <label class="field-label" for="newpwd">{{ __('New Password') }}</label>
                <div class="field-wrap">
                    <svg class="field-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <input id="newpwd" name="password" type="password" placeholder="{{ __('Min 8 characters') }}" autocomplete="new-password">
                </div>
            </div>

            <div class="field f2">
                <label class="field-label" for="confirmpwd">{{ __('Confirm Password') }}</label>
                <div class="field-wrap">
                    <svg class="field-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <input id="confirmpwd" name="password_confirmation" type="password" placeholder="{{ __('Repeat password') }}" autocomplete="new-password">
                </div>
            </div>

            <button type="submit" class="btn f3" id="btn3">
                <div class="spinner"></div>
                <span class="btn-text">{{ __('Reset Password') }}</span>
                <svg class="btn-arrow" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </button>
        </form>

        <p class="card-footer" style="margin-top:22px;"><a href="{{ route('client.forgot-password') }}">{{ __('← Start over') }}</a></p>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Country code dropdown for forgot-password step 1
(function () {
    const btn      = document.getElementById('fpCountryBtn');
    const dropdown = document.getElementById('fpCountryDropdown');
    const flagEl   = document.getElementById('fpCountryFlag');
    const dialEl   = document.getElementById('fpCountryDial');
    if (!btn) return;

    btn.addEventListener('click', e => {
        e.stopPropagation();
        btn.classList.toggle('open');
        dropdown.classList.toggle('open');
    });
    document.addEventListener('click', () => {
        btn.classList.remove('open');
        dropdown.classList.remove('open');
    });
    dropdown.querySelectorAll('.country-option').forEach(opt => {
        opt.addEventListener('click', () => {
            dropdown.querySelectorAll('.country-option').forEach(o => o.classList.remove('active'));
            opt.classList.add('active');
            flagEl.textContent = opt.dataset.flag;
            dialEl.textContent = opt.dataset.dial;
            btn.classList.remove('open');
            dropdown.classList.remove('open');
        });
    });

    const form1 = document.getElementById('form1');
    if (form1) {
        form1.addEventListener('submit', () => {
            let local = document.getElementById('fpPhoneLocal').value.trim();
            if (local.startsWith('0')) local = local.slice(1);
            document.getElementById('fpPhoneHidden').value = dialEl.textContent + local;
        });
    }
})();

['form1','form2','form3'].forEach(id => {
    const form = document.getElementById(id);
    if (form) form.addEventListener('submit', () => {
        const btn = form.querySelector('button[type=submit]');
        if (btn) btn.classList.add('loading');
    });
});
</script>
@endpush
